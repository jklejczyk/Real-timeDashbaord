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

test('OrderCreated broadcasts a thin payload on the private dashboard channel', function () {
    $order = Order::factory()->for(Worker::factory())->create([
        'status' => OrderStatusEnum::Completed,
    ]);

    $event = new OrderCreated($order);

    expect($event->broadcastOn()[0]->name)->toBe('private-dashboard')
        ->and($event->broadcastAs())->toBe('order.created')
        ->and($event->broadcastWith())->toBe([
            'id' => $order->id,
            'type' => $order->type->value,
            'status' => $order->status->value,
            'amount' => $order->amount,
        ]);
});
