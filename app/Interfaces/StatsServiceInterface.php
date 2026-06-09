<?php

namespace App\Interfaces;

use App\DTOs\OrderStatusCountsData;
use App\DTOs\RevenueComparisonData;
use App\DTOs\WorkerStatData;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

interface StatsServiceInterface
{
    public function ordersByStatus(): OrderStatusCountsData;

    public function revenueForPeriod(CarbonInterface $start, CarbonInterface $end): string;

    public function revenueComparison(CarbonInterface $start, CarbonInterface $end): RevenueComparisonData;

    /**
     * @return Collection<int, WorkerStatData>
     */
    public function topWorkers(int $limit = 5): Collection;
}
