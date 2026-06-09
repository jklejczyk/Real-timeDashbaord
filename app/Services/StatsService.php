<?php

namespace App\Services;

use App\DTOs\OrderStatusCountsData;
use App\DTOs\RevenueComparisonData;
use App\DTOs\WorkerStatData;
use App\Enums\OrderStatusEnum;
use App\Interfaces\StatsServiceInterface;
use App\Models\Order;
use App\Models\Worker;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class StatsService implements StatsServiceInterface
{
    public function ordersByStatus(): OrderStatusCountsData
    {
        $counts = Order::query()->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return OrderStatusCountsData::fromCollection($counts);
    }

    public function revenueForPeriod(CarbonInterface $start, CarbonInterface $end): string
    {
        return (string) Order::query()->where('status', OrderStatusEnum::Completed)
            ->whereBetween('completed_at', [$start, $end])
            ->sum('amount');
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
}
