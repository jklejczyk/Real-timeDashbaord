<?php

namespace App\DTOs;

use App\Models\Worker;

final readonly class WorkerStatData
{
    public function __construct(
        public int $workerId,
        public string $name,
        public int $ordersCount,
    ) {}

    public static function fromWorker(Worker $worker): self
    {
        return new self(
            workerId: $worker->id,
            name: $worker->name,
            ordersCount: (int) $worker->getAttribute('orders_count'),
        );
    }
}
