<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Order $order)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('dashboard'),
        ];
    }

    /**
     * @return array{id: int, workerName: string, type: string, status: string, amount: string, createdAt: string}
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->order->id,
            'workerName' => $this->order->worker->name,
            'type' => $this->order->type->value,
            'status' => $this->order->status->value,
            'amount' => $this->order->amount,
            'createdAt' => $this->order->created_at->toIso8601String(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.created';
    }
}
