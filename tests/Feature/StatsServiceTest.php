<?php

use App\DTOs\DashboardFilters;
use App\Enums\OrderTypeEnum;
use App\Models\Order;
use App\Models\Worker;
use App\Services\CachedStatsService;
use App\Services\StatsService;
use Carbon\CarbonImmutable;

test('ordersByStatus counts orders grouped by status', function () {
    $worker = Worker::factory()->create();

    Order::factory(3)->for($worker)->completed()->create();
    Order::factory(2)->for($worker)->delayed()->create();
    Order::factory(1)->for($worker)->create();

    $counts = app(StatsService::class)->ordersByStatus();

    expect($counts->completed)->toBe(3)
        ->and($counts->delayed)->toBe(2)
        ->and($counts->pending)->toBe(1)
        ->and($counts->inProgress)->toBe(0);
});

test('revenueForPeriod sums only completed orders within the date range', function () {
    $worker = Worker::factory()->create();

    Order::factory()->for($worker)->completed()->create([
        'amount' => 100.00,
        'completed_at' => CarbonImmutable::parse('2026-06-05 10:00'),
    ]);
    Order::factory()->for($worker)->completed()->create([
        'amount' => 250.50,
        'completed_at' => CarbonImmutable::parse('2026-06-06 12:00'),
    ]);

    Order::factory()->for($worker)->completed()->create([
        'amount' => 999.00,
        'completed_at' => CarbonImmutable::parse('2026-05-01 10:00'),
    ]);

    Order::factory()->for($worker)->create([
        'amount' => 500.00,
    ]);

    $revenue =
        app(StatsService::class)->revenueForPeriod(
            CarbonImmutable::parse('2026-06-01'),
            CarbonImmutable::parse('2026-06-30'),
        );

    expect($revenue)->toBe('350.50');
});

test('revenueComparison computes percentage change vs the previous equal-length period', function () {
    $worker = Worker::factory()->create();

    Order::factory()->for($worker)->completed()->create([
        'amount' => 200.00,
        'completed_at' => CarbonImmutable::parse('2026-05-15 10:00'),
    ]);

    Order::factory()->for($worker)->completed()->create([
        'amount' => 300.00,
        'completed_at' => CarbonImmutable::parse('2026-06-15 10:00'),
    ]);

    $comparison = app(StatsService::class)->revenueComparison(
        CarbonImmutable::parse('2026-06-01'),
        CarbonImmutable::parse('2026-06-30'),
    );

    expect($comparison->current)->toBe('300.00')
        ->and($comparison->previous)->toBe('200.00')
        ->and($comparison->changePercent)->toBe(50.0);
});

test('revenueComparison returns 0 percent change when the previous period had no revenue', function () {
    $worker = Worker::factory()->create();

    Order::factory()->for($worker)->completed()->create([
        'amount' => 300.00,
        'completed_at' => CarbonImmutable::parse('2026-06-15 10:00'),
    ]);

    $comparison = app(StatsService::class)->revenueComparison(
        CarbonImmutable::parse('2026-06-01'),
        CarbonImmutable::parse('2026-06-30'),
    );

    expect($comparison->changePercent)->toBe(0.0);
});

test('topWorkers returns workers ranked by order count, limited', function () {
    $busy = Worker::factory()->create(['name' => 'Busy Bee']);
    $medium = Worker::factory()->create(['name' => 'Medium Mike']);
    $idle = Worker::factory()->create(['name' => 'Idle Ian']);

    Order::factory(5)->for($busy)->create();
    Order::factory(3)->for($medium)->create();
    Order::factory(1)->for($idle)->create();

    $top = app(StatsService::class)->topWorkers(limit: 2);

    expect($top)->toHaveCount(2)->and($top->first()->name)->toBe('Busy Bee')
        ->and($top->first()->ordersCount)->toBe(5)
        ->and($top->last()->name)->toBe('Medium Mike')
        ->and($top->last()->ordersCount)->toBe(3);
});

test('CachedStatsService caches ordersByStatus so the second call hits no database', function () {
    $worker = Worker::factory()->create();
    Order::factory(3)->for($worker)->completed()
        ->create();

    $service = app(CachedStatsService::class);

    $first = $service->ordersByStatus();

    $queries = 0;
    DB::listen(function () use (&$queries): void {
        $queries++;
    });

    $second = $service->ordersByStatus();

    expect($queries)->toBe(0)
        ->and($second->completed)->toBe(3)
        ->and($second->completed)->toBe($first->completed);
});

test('revenueForPeriod uses period-specific cache keys', function () {
    $worker = Worker::factory()->create();

    Order::factory()->for($worker)->completed()->create([
        'amount' => 100.00,
        'completed_at' => CarbonImmutable::parse('2026-05-15'),
    ]);
    Order::factory()->for($worker)->completed()->create([
        'amount' => 200.00,
        'completed_at' => CarbonImmutable::parse('2026-06-15'),
    ]);

    $service = app(CachedStatsService::class);

    $may = $service->revenueForPeriod(
        CarbonImmutable::parse('2026-05-01'),
        CarbonImmutable::parse('2026-05-31'),
    );
    $june = $service->revenueForPeriod(
        CarbonImmutable::parse('2026-06-01'),
        CarbonImmutable::parse('2026-06-30'),
    );

    expect($may)->toBe('100.00')
        ->and($june)->toBe('200.00');
});

test('revenuePerMonth buckets completed revenue by calendar month', function () {
    $this->travelTo(CarbonImmutable::parse('2026-06-18 12:00'));
    $worker = Worker::factory()->create();

    Order::factory()->for($worker)->completed()->create([
        'amount' => 100.00,
        'completed_at' => CarbonImmutable::parse('2026-06-05'),
    ]);
    Order::factory()->for($worker)->completed()->create([
        'amount' => 50.00,
        'completed_at' => CarbonImmutable::parse('2026-06-15'),
    ]);
    Order::factory()->for($worker)->completed()->create([
        'amount' => 200.00,
        'completed_at' => CarbonImmutable::parse('2026-04-10'),
    ]);

    $perMonth = app(StatsService::class)->revenuePerMonth(months: 6);

    expect($perMonth)->toHaveCount(6)
        ->and($perMonth->first()->month)->toBe('2026-01')
        ->and($perMonth->last()->month)->toBe('2026-06')
        ->and($perMonth->last()->total)->toBe('150.00')
        ->and($perMonth->firstWhere('month', '2026-04')->total)->toBe('200.00')
        ->and($perMonth->firstWhere('month', '2026-05')->total)->toBe('0');
});

test('ordersTrendDaily counts orders per day and fills empty days with zero', function () {
    $worker = Worker::factory()->create();

    Order::factory(2)->for($worker)->create(['created_at' => CarbonImmutable::parse('2026-06-10 09:00')]);
    Order::factory()->for($worker)->create(['created_at' => CarbonImmutable::parse('2026-06-12 09:00')]);

    $trend = app(StatsService::class)->ordersTrendDaily(new DashboardFilters(
        startDate: CarbonImmutable::parse('2026-06-10')->startOfDay(),
        endDate: CarbonImmutable::parse('2026-06-12')->endOfDay(),
    ));

    expect($trend)->toHaveCount(3)
        ->and($trend->firstWhere('date', '2026-06-10')->count)->toBe(2)
        ->and($trend->firstWhere('date', '2026-06-11')->count)->toBe(0)
        ->and($trend->firstWhere('date', '2026-06-12')->count)->toBe(1);
});

test('ordersByStatus honours worker, type and date filters', function () {
    $alice = Worker::factory()->create();
    $bob = Worker::factory()->create();

    Order::factory()->for($alice)->completed()->create([
        'type' => OrderTypeEnum::Repair,
        'created_at' => CarbonImmutable::parse('2026-06-10'),
    ]);
    Order::factory()->for($alice)->delayed()->create([
        'type' => OrderTypeEnum::Repair,
        'created_at' => CarbonImmutable::parse('2026-06-11'),
    ]);
    Order::factory()->for($alice)->completed()->create([
        'type' => OrderTypeEnum::Installation,
        'created_at' => CarbonImmutable::parse('2026-06-10'),
    ]);
    Order::factory()->for($bob)->completed()->create([
        'type' => OrderTypeEnum::Repair,
        'created_at' => CarbonImmutable::parse('2026-06-10'),
    ]);
    Order::factory()->for($alice)->completed()->create([
        'type' => OrderTypeEnum::Repair,
        'created_at' => CarbonImmutable::parse('2026-05-01'),
    ]);

    $counts = app(StatsService::class)->ordersByStatus(new DashboardFilters(
        startDate: CarbonImmutable::parse('2026-06-01')->startOfDay(),
        endDate: CarbonImmutable::parse('2026-06-30')->endOfDay(),
        workerId: $alice->id,
        type: OrderTypeEnum::Repair,
    ));

    expect($counts->completed)->toBe(1)
        ->and($counts->delayed)->toBe(1)
        ->and($counts->pending)->toBe(0)
        ->and($counts->inProgress)->toBe(0);
});
