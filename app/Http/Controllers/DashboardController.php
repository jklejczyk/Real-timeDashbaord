<?php

namespace App\Http\Controllers;

use App\DTOs\DashboardFilters;
use App\Enums\OrderTypeEnum;
use App\Http\Requests\DashboardFilterRequest;
use App\Interfaces\StatsServiceInterface;
use App\Models\Worker;
use Illuminate\Support\Facades\Date;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(DashboardFilterRequest $request, StatsServiceInterface $statsService): Response
    {
        $filters = DashboardFilters::fromRequest($request);
        $now = Date::now();

        return Inertia::render('Dashboard', [
            'filters' => [
                'startDate' => $filters->startDate->toDateString(),
                'endDate' => $filters->endDate->toDateString(),
                'workerId' => $filters->workerId,
                'type' => $filters->type?->value,
            ],
            'filterOptions' => [
                'workers' => Worker::query()
                    ->orderBy('name')
                    ->get(['id', 'name'])
                    ->map(fn (Worker $worker): array => [
                        'id' => $worker->id,
                        'name' => $worker->name,
                    ]),
                'types' => collect(OrderTypeEnum::cases())
                    ->map(fn (OrderTypeEnum $type): array => [
                        'value' => $type->value,
                        'label' => $type->name,
                    ]),
            ],
            'revenue' => [
                'today' => $statsService->revenueComparison($now->startOfDay(), $now->endOfDay()),
                'week' => $statsService->revenueComparison($now->startOfWeek(), $now->endOfWeek()),
                'month' => $statsService->revenueComparison($now->startOfMonth(), $now->endOfMonth()),
            ],
            'statusCounts' => $statsService->ordersByStatus($filters),
            'topWorkers' => $statsService->topWorkers(),
            'revenuePerMonth' => Inertia::defer(fn () => $statsService->revenuePerMonth(filters: $filters)),
            'ordersTrend' => Inertia::defer(fn () => $statsService->ordersTrendDaily($filters)),
        ]);
    }
}
