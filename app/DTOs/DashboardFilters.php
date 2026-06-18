<?php

namespace App\DTOs;

use App\Enums\OrderTypeEnum;
use App\Http\Requests\DashboardFilterRequest;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;

final readonly class DashboardFilters
{
    public function __construct(
        public CarbonImmutable $startDate,
        public CarbonImmutable $endDate,
        public ?int $workerId = null,
        public ?OrderTypeEnum $type = null,
    ) {}

    public static function fromRequest(DashboardFilterRequest $request): self
    {
        $anchor = Date::now();

        $start = $request->date('start_date') ?? $anchor->subDays(29);
        $end = $request->date('end_date') ?? $anchor;

        return new self(
            startDate: CarbonImmutable::parse($start)->startOfDay(),
            endDate: CarbonImmutable::parse($end)->endOfDay(),
            workerId: $request->integer('worker_id') ?: null,
            type: $request->enum('type', OrderTypeEnum::class),
        );
    }

    /**
     * Stable identity of this filter set, used to scope cache entries.
     */
    public function cacheKey(): string
    {
        return implode(':', [
            $this->startDate->toDateString(),
            $this->endDate->toDateString(),
            $this->workerId ?? 'all',
            $this->type !== null ? $this->type->value : 'all',
        ]);
    }
}
