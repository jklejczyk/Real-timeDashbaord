<?php

use App\Models\Order;
use App\Models\User;
use App\Notifications\DelayedOrdersAlert;
use Illuminate\Support\Facades\Notification;

test('sends a broadcast alert when delayed orders exceed the threshold', function () {
    Notification::fake();

    $user = User::factory()->create();

    Order::factory()->delayed()->count(6)->create();

    $this->artisan('orders:check-alerts')->assertSuccessful();

    Notification::assertSentTo(
        $user,
        DelayedOrdersAlert::class,
        function (DelayedOrdersAlert $notification, array $channels) {
            return in_array('broadcast', $channels, true);
        },
    );
});

test('does not send an alert when below the threshold', function () {
    Notification::fake();

    User::factory()->create();

    Order::factory()->delayed()->count(2)->create();

    $this->artisan('orders:check-alerts')->assertSuccessful();

    Notification::assertNothingSent();
});
