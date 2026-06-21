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

#[Signature('demo:simulate {--interval=3 : Sekundy między zdarzeniami}')]
#[Description('Symuluje ruch na żywo: co kilka sekund dodaje zlecenie i broadcastuje je na dashboard')]
class DemoSimulate extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $interval = max(1, (int) $this->option('interval'));

        $this->info("Symulacja live co {$interval}s — otwórz /dashboard i patrz. Ctrl+C aby zatrzymać.");
        $this->newLine();

        $running = true;
        $this->trap([SIGINT, SIGTERM], function () use (&$running): void {
            $running = false;
        });

        $tick = 0;

        while ($running) {
            $tick++;
            $order = $this->createRandomOrder();

            Cache::flush();
            OrderCreated::dispatch($order);

            $this->line(sprintf(
                '[%3d] #%-5d %-11s %-12s %9s PLN  → %s',
                $tick,
                $order->id,
                $order->status->value,
                $order->type->value,
                $order->amount,
                $order->worker->name,
            ));

            if ($order->status === OrderStatusEnum::Delayed) {
                $this->call('orders:check-alerts');
            }

            sleep($interval);
        }

        $this->newLine();
        $this->info("Zatrzymano po {$tick} zdarzeniach.");

        return self::SUCCESS;
    }

    private function createRandomOrder(): Order
    {
        $worker = Worker::query()->where('is_active', true)->inRandomOrder()->first()
            ?? Worker::factory()->create();

        $factory = match (fake()->numberBetween(1, 10)) {
            1, 2 => Order::factory()->for($worker)->recent()->delayed(),
            3 => Order::factory()->for($worker)->recent()->inProgress(),
            default => Order::factory()->for($worker)->recent()->completed(),
        };

        return $factory->create();
    }
}
