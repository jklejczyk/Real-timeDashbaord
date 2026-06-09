<?php

namespace App\Services;

use App\DTOs\OrderStatusCountsData;
use App\DTOs\RevenueComparisonData;
use App\DTOs\WorkerStatData;
use App\Interfaces\StatsServiceInterface;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Collection;

final readonly class CachedStatsService implements StatsServiceInterface
{
    private const int TTL_SECONDS = 60;

    public function __construct(
        private StatsServiceInterface $statsService,
        private Cache $cache,
    ) {}

    public function ordersByStatus(): OrderStatusCountsData
    {
        return $this->cache->remember(
            'stats:orders_by_status',
            self::TTL_SECONDS,
            fn (): OrderStatusCountsData => $this->statsService->ordersByStatus(),
        );
    }

    public function revenueForPeriod(CarbonInterface $start, CarbonInterface $end): string
    {
        return $this->cache->remember(
            "stats:revenue:{$start->timestamp}:{$end->timestamp}",
            self::TTL_SECONDS,
            fn (): string => $this->statsService->revenueForPeriod($start, $end),
        );
    }

    public function revenueComparison(CarbonInterface $start, CarbonInterface $end): RevenueComparisonData
    {
        return $this->cache->remember(
            "stats:revenue_comparison:{$start->timestamp}:{$end->timestamp}",
            self::TTL_SECONDS,
            fn (): RevenueComparisonData => $this->statsService->revenueComparison($start, $end),
        );
    }

    /**
     * @return Collection<int, WorkerStatData>
     */
    public function topWorkers(int $limit = 5): Collection
    {
        return $this->cache->remember(
            "stats:top_workers:{$limit}",
            self::TTL_SECONDS,
            fn (): Collection => $this->statsService->topWorkers($limit),
        );
    }
}
