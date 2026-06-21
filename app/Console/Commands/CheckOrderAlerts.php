<?php

namespace App\Console\Commands;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use App\Notifications\DelayedOrdersAlert;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

#[Signature('orders:check-alerts')]
#[Description('Sprawdza próg opóźnionych zleceń i wysyła alert broadcast do userów')]
class CheckOrderAlerts extends Command
{
    private const DELAYED_THRESHOLD = 5;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $delayedCount = Order::query()->where('status', OrderStatusEnum::Delayed)->count();

        if ($delayedCount < self::DELAYED_THRESHOLD) {
            $this->info("Opóźnione: {$delayedCount} < próg ".self::DELAYED_THRESHOLD.' — brak alertu.');

            return self::SUCCESS;
        }

        Notification::send(User::all(), new DelayedOrdersAlert($delayedCount, self::DELAYED_THRESHOLD));

        $this->warn("Alert wysłany: {$delayedCount} opóźnionych (próg ".self::DELAYED_THRESHOLD.').');

        return self::SUCCESS;
    }
}
