<?php

namespace App\Interfaces;

use App\DTOs\DailyOrderCountData;
use App\DTOs\DashboardFilters;
use App\DTOs\MonthlyRevenueData;
use App\DTOs\OrderStatusCountsData;
use App\DTOs\RevenueComparisonData;
use App\DTOs\WorkerStatData;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

interface StatsServiceInterface
{
    public function ordersByStatus(?DashboardFilters $filters = null): OrderStatusCountsData;

    public function revenueForPeriod(CarbonInterface $start, CarbonInterface $end): string;

    public function revenueComparison(CarbonInterface $start, CarbonInterface $end): RevenueComparisonData;

    /**
     * @return Collection<int, WorkerStatData>
     */
    public function topWorkers(int $limit = 5): Collection;

    /**
     * @return Collection<int, MonthlyRevenueData>
     */
    public function revenuePerMonth(int $months = 6, ?DashboardFilters $filters = null): Collection;

    /**
     * @return Collection<int, DailyOrderCountData>
     */
    public function ordersTrendDaily(DashboardFilters $filters): Collection;
}
