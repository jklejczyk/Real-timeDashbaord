<?php

namespace App\Services;

use App\DTOs\DailyOrderCountData;
use App\DTOs\DashboardFilters;
use App\DTOs\MonthlyRevenueData;
use App\DTOs\OrderStatusCountsData;
use App\DTOs\RevenueComparisonData;
use App\DTOs\WorkerStatData;
use App\Enums\OrderStatusEnum;
use App\Interfaces\StatsServiceInterface;
use App\Models\Order;
use App\Models\Worker;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

class StatsService implements StatsServiceInterface
{
    public function ordersByStatus(?DashboardFilters $filters = null): OrderStatusCountsData
    {
        $query = Order::query();

        if ($filters !== null) {
            $query = $this->applyFilters($query, $filters)
                ->whereBetween('created_at', [$filters->startDate->startOfDay(), $filters->endDate->endOfDay()]);
        }

        $counts = $query->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return OrderStatusCountsData::fromCollection($counts);
    }

    public function revenueForPeriod(CarbonInterface $start, CarbonInterface $end): string
    {
        return $this->sumCompletedRevenue($start, $end, null);
    }

    public function revenueComparison(CarbonInterface $start, CarbonInterface $end): RevenueComparisonData
    {
        $current = $this->revenueForPeriod($start, $end);

        $length = (int) $start->diffInSeconds($end, true);
        $previousStart = $start->subSeconds($length);
        $previousEnd = $start;

        $previous = $this->revenueForPeriod($previousStart, $previousEnd);

        return RevenueComparisonData::fromAmounts($current, $previous);
    }

    /**
     * @return Collection<int, WorkerStatData>
     */
    public function topWorkers(int $limit = 5): Collection
    {
        return Worker::query()
            ->withCount('orders')
            ->orderByDesc('orders_count')
            ->limit($limit)
            ->get()
            ->map(fn (Worker $worker): WorkerStatData => WorkerStatData::fromWorker($worker));
    }

    /**
     * @return Collection<int, MonthlyRevenueData>
     */
    public function revenuePerMonth(int $months = 6, ?DashboardFilters $filters = null): Collection
    {
        $anchor = Date::now();

        return Collection::make(range($months - 1, 0))
            ->map(function (int $offset) use ($anchor, $filters): MonthlyRevenueData {
                $monthStart = $anchor->subMonths($offset)->startOfMonth();
                $monthEnd = $monthStart->endOfMonth();

                return MonthlyRevenueData::fromMonth(
                    $monthStart,
                    $this->sumCompletedRevenue($monthStart, $monthEnd, $filters),
                );
            })
            ->values();
    }

    /**
     * @return Collection<int, DailyOrderCountData>
     */
    public function ordersTrendDaily(DashboardFilters $filters): Collection
    {
        $counts = $this->applyFilters(Order::query(), $filters)
            ->whereBetween('created_at', [$filters->startDate->startOfDay(), $filters->endDate->endOfDay()])
            ->selectRaw('date(created_at) as day, count(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        /** @var Collection<int, DailyOrderCountData> $trend */
        $trend = Collection::make();

        $cursor = $filters->startDate->startOfDay();
        $last = $filters->endDate->startOfDay();

        while ($cursor->lessThanOrEqualTo($last)) {
            $day = $cursor->toDateString();
            $trend->push(DailyOrderCountData::fromDate($cursor, (int) $counts->get($day, 0)));
            $cursor = $cursor->addDay();
        }

        return $trend;
    }

    private function sumCompletedRevenue(CarbonInterface $start, CarbonInterface $end, ?DashboardFilters $filters): string
    {
        $query = Order::query()
            ->where('status', OrderStatusEnum::Completed)
            ->whereBetween('completed_at', [$start, $end]);

        if ($filters !== null) {
            $query = $this->applyFilters($query, $filters);
        }

        return (string) $query->sum('amount');
    }

    /**
     * @param  Builder<Order>  $query
     * @return Builder<Order>
     */
    private function applyFilters(Builder $query, DashboardFilters $filters): Builder
    {
        return $query
            ->when(
                $filters->workerId !== null,
                fn (Builder $q): Builder => $q->where('worker_id', $filters->workerId),
            )
            ->when(
                $filters->type !== null,
                fn (Builder $q): Builder => $q->where('type', $filters->type),
            );
    }
}
