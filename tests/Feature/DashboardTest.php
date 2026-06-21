<?php

use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\User;
use App\Models\Worker;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard renders eager stat props and defers the heavier charts', function () {
    $user = User::factory()->create();
    $worker = Worker::factory()->create();
    Order::factory(3)->for($worker)->completed()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('filters')
            ->has('filterOptions.workers')
            ->has('filterOptions.types', 4)
            ->has('revenue.today')
            ->has('revenue.week')
            ->has('revenue.month')
            ->has('statusCounts')
            ->has('topWorkers')
            ->missing('revenuePerMonth')
            ->missing('ordersTrend')
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->has('revenuePerMonth')
                ->has('ordersTrend')
            )
        );
});

test('dashboard reflects filter query parameters in its props', function () {
    $user = User::factory()->create();
    $worker = Worker::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard', [
            'worker_id' => $worker->id,
            'type' => 'repair',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
        ]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.workerId', $worker->id)
            ->where('filters.type', 'repair')
            ->where('filters.startDate', '2026-06-01')
            ->where('filters.endDate', '2026-06-30')
        );
});

test('dashboard rejects an invalid order type filter', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard', ['type' => 'not-a-real-type']))
        ->assertSessionHasErrors('type');
});
