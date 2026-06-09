<?php

namespace App\DTOs;

final readonly class RevenueComparisonData
{
    public function __construct(
        public string $current,
        public string $previous,
        public float $changePercent,
    ) {}

    public static function fromAmounts(string $current, string $previous): self
    {
        $currentValue = (float) $current;
        $previousValue = (float) $previous;

        $changePercent = $previousValue > 0.0 ?
            round((($currentValue - $previousValue) / $previousValue) * 100, 2)
            : 0.0;

        return new self($current, $previous, $changePercent);
    }
}
