<?php

namespace App\DTOs;

use Carbon\CarbonInterface;

final readonly class MonthlyRevenueData
{
    public function __construct(
        public string $month,
        public string $label,
        public string $total,
    ) {}

    public static function fromMonth(CarbonInterface $monthStart, string $total): self
    {
        return new self(
            month: $monthStart->format('Y-m'),
            label: $monthStart->format('M Y'),
            total: $total,
        );
    }
}
