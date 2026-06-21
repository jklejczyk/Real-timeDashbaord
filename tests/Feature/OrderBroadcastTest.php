<?php

use App\Enums\OrderStatusEnum;
use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\Worker;
use Illuminate\Support\Facades\Event;

test('the simulate command broadcasts OrderCreated', function () {
    Event::fake();

    $this->artisan('order:simulate')->assertSuccessful();

    Event::assertDispatched(
        OrderCreated::class, fn (OrderCreated $event): bool => $event->order->status === OrderStatusEnum::Completed,
    );
});

test('broadcasts a full order row in the live feed payload', function () {
    $worker = Worker::factory()->create(['name' => 'Jan']);
    $order = Order::factory()->for($worker)->create();

    $payload = (new OrderCreated($order))->broadcastWith();

    expect($payload)
        ->toHaveKeys(['id', 'workerName', 'type', 'status', 'amount', 'createdAt'])
        ->and($payload['workerName'])->toBe('Jan');
});
