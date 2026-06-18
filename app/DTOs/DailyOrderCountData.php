<?php

namespace App\DTOs;

use Carbon\CarbonInterface;

final readonly class DailyOrderCountData
{
    public function __construct(
        public string $date,
        public int $count,
    ) {}

    public static function fromDate(CarbonInterface $date, int $count): self
    {
        return new self(
            date: $date->toDateString(),
            count: $count,
        );
    }
}
