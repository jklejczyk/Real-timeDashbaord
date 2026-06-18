<?php

use App\DTOs\DailyOrderCountData;
use App\DTOs\MonthlyRevenueData;
use App\DTOs\OrderStatusCountsData;
use App\DTOs\RevenueComparisonData;
use App\DTOs\WorkerStatData;
use Illuminate\Support\Collection;

/**
 * Guards against the Laravel 13 `cache.serializable_classes` allow-list silently
 * turning cached DTOs into __PHP_Incomplete_Class on the Redis store. The unit
 * suite runs on the array store, so this reproduces the RedisStore unserialize
 * behaviour directly from config instead of requiring a live Redis connection.
 */
test('every cached DTO survives the cache allowed_classes round-trip', function () {
    $allowed = config('cache.serializable_classes');

    $samples = [
        new RevenueComparisonData('100.00', '50.00', 100.0),
        new OrderStatusCountsData(1, 2, 3, 4),
        new Collection([new WorkerStatData(1, 'Ada', 7)]),
        new Collection([new MonthlyRevenueData('2026-06', 'Jun 2026', '1234.00')]),
        new Collection([new DailyOrderCountData('2026-06-18', 5)]),
    ];

    foreach ($samples as $sample) {
        $restored = unserialize(serialize($sample), ['allowed_classes' => $allowed]);

        expect($restored)->not->toBeInstanceOf(__PHP_Incomplete_Class::class);

        if ($restored instanceof Collection) {
            $restored->each(
                fn ($item) => expect($item)->not->toBeInstanceOf(__PHP_Incomplete_Class::class),
            );
        }
    }
});
