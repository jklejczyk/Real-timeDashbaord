<?php

namespace App\Console\Commands;

use App\Enums\OrderStatusEnum;
use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\Worker;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

#[Signature('order:simulate')]
#[Description('Tworzy losowe zlecenie i broadcastuje je na dashboard')]
class SimulateOrder extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $worker = Worker::query()->inRandomOrder()->first() ?? Worker::factory()->create();

        $order = Order::factory()->for($worker)->create([
            'status' => OrderStatusEnum::Completed,
            'created_at' => now(),
            'completed_at' => now(),
        ]);

        Cache::flush();

        OrderCreated::dispatch($order);

        $this->info("Zlecenie #{$order->id} ({$order->amount} PLN) → {$worker->name}");

        return self::SUCCESS;
    }
}
