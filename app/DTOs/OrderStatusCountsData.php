<?php

namespace App\DTOs;

use App\Enums\OrderStatusEnum;
use Illuminate\Support\Collection;

final readonly class OrderStatusCountsData
{
    public function __construct(
        public int $pending,
        public int $inProgress,
        public int $completed,
        public int $delayed,
    ) {}

    /**
     * @param  Collection<string, int|string>  $counts
     */
    public static function fromCollection(Collection $counts): self
    {
        return new self(
            pending: (int) $counts->get(OrderStatusEnum::Pending->value, 0),
            inProgress: (int) $counts->get(OrderStatusEnum::InProgress->value, 0),
            completed: (int) $counts->get(OrderStatusEnum::Completed->value, 0),
            delayed: (int) $counts->get(OrderStatusEnum::Delayed->value, 0),
        );
    }
}
