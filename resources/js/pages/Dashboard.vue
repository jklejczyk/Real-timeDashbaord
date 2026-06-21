<script setup lang="ts">
import { Deferred, Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import BarChart from '@/components/charts/BarChart.vue';
import LineChart from '@/components/charts/LineChart.vue';
import PieChart from '@/components/charts/PieChart.vue';
import ActivityTimeline from '@/components/dashboard/ActivityTimeline.vue';
import DashboardFilters from '@/components/dashboard/DashboardFilters.vue';
import OnlineViewers from '@/components/dashboard/OnlineViewers.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import { Card } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import { useActivityFeed } from '@/composables/useActivityFeed';
import { useLiveDashboard } from '@/composables/useLiveDashboard';
import { useDashboardPresence } from '@/composables/usePresence';
import { formatCurrency, formatDayLabel, formatNumber } from '@/lib/format';
import { dashboard } from '@/routes';
import type {
    ActivityItem,
    DailyOrderCount,
    DashboardFilterValues,
    MonthlyRevenue,
    OrderTypeOption,
    RevenueComparison,
    StatusCounts,
    WorkerOption,
    WorkerStat,
} from '@/types/dashboard';
import ViewingIndicator from '@/components/dashboard/ViewingIndicator.vue';

const props = defineProps<{
    filters: DashboardFilterValues;
    filterOptions: { workers: WorkerOption[]; types: OrderTypeOption[] };
    revenue: {
        today: RevenueComparison;
        week: RevenueComparison;
        month: RevenueComparison;
    };
    statusCounts: StatusCounts;
    topWorkers: WorkerStat[];
    revenuePerMonth?: MonthlyRevenue[];
    ordersTrend?: DailyOrderCount[];
    recentActivity: ActivityItem[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    },
});

useLiveDashboard();
const { items: activityItems } = useActivityFeed(props.recentActivity);
const { viewers, viewingByUser, whisperViewing } = useDashboardPresence();

const statusCards = computed(() => [
    { title: 'Oczekujące', value: props.statusCounts.pending },
    { title: 'W toku', value: props.statusCounts.inProgress },
    { title: 'Zakończone', value: props.statusCounts.completed },
    { title: 'Opóźnione', value: props.statusCounts.delayed },
]);

const statusChart = computed(() => ({
    labels: ['Oczekujące', 'W toku', 'Zakończone', 'Opóźnione'],
    values: [
        props.statusCounts.pending,
        props.statusCounts.inProgress,
        props.statusCounts.completed,
        props.statusCounts.delayed,
    ],
    colors: ['#f59e0b', '#3b82f6', '#10b981', '#ef4444'],
}));

const monthlyLabels = computed(() =>
    (props.revenuePerMonth ?? []).map((m) => m.label),
);
const monthlyValues = computed(() =>
    (props.revenuePerMonth ?? []).map((m) => Number(m.total)),
);

const trendLabels = computed(() =>
    (props.ordersTrend ?? []).map((d) => formatDayLabel(d.date)),
);
const trendValues = computed(() =>
    (props.ordersTrend ?? []).map((d) => d.count),
);
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <OnlineViewers :viewers="viewers" />
        <ViewingIndicator :viewing="viewingByUser" />
        <DashboardFilters
            :filters="filters"
            :workers="filterOptions.workers"
            :types="filterOptions.types"
        />

        <!-- Przychód: dziś / tydzień / miesiąc vs poprzedni okres -->
        <div class="grid gap-4 md:grid-cols-3">
            <StatCard
                title="Przychód dziś"
                :value="formatCurrency(revenue.today.current)"
                :change="revenue.today.changePercent"
                caption="vs wczoraj"
            />
            <StatCard
                title="Przychód w tym tygodniu"
                :value="formatCurrency(revenue.week.current)"
                :change="revenue.week.changePercent"
                caption="vs poprzedni tydzień"
            />
            <StatCard
                title="Przychód w tym miesiącu"
                :value="formatCurrency(revenue.month.current)"
                :change="revenue.month.changePercent"
                caption="vs poprzedni miesiąc"
            />
        </div>

        <!-- Liczniki statusów (reagują na filtry) -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard
                v-for="card in statusCards"
                :key="card.title"
                :title="card.title"
                :value="formatNumber(card.value)"
                caption="w wybranym okresie"
            />
        </div>

        <!-- Wykresy: słupkowy (przychód/miesiąc, deferred) + kołowy (statusy) -->
        <div class="grid gap-4 lg:grid-cols-3">
            <Card
                class="gap-3 p-5 lg:col-span-2"
                @mouseenter="whisperViewing('Przychód per miesiąc')"
            >
                <h2 class="text-sm font-medium text-muted-foreground">
                    Przychód per miesiąc
                </h2>
                <div class="h-72">
                    <Deferred data="revenuePerMonth">
                        <template #fallback>
                            <Skeleton class="h-full w-full" />
                        </template>
                        <BarChart
                            :labels="monthlyLabels"
                            :values="monthlyValues"
                        />
                    </Deferred>
                </div>
            </Card>

            <Card
                class="gap-3 p-5"
                @mouseenter="whisperViewing('Zlecenia per status')"
            >
                <h2 class="text-sm font-medium text-muted-foreground">
                    Zlecenia per status
                </h2>
                <div class="h-72">
                    <PieChart
                        :labels="statusChart.labels"
                        :values="statusChart.values"
                        :colors="statusChart.colors"
                    />
                </div>
            </Card>
        </div>

        <!-- Wykres liniowy (trend dzienny, deferred) + top pracownicy -->
        <div class="grid gap-4 lg:grid-cols-3">
            <Card class="gap-3 p-5 lg:col-span-2">
                <h2 class="text-sm font-medium text-muted-foreground">
                    Trend zleceń dziennie
                </h2>
                <div class="h-72">
                    <Deferred data="ordersTrend">
                        <template #fallback>
                            <Skeleton class="h-full w-full" />
                        </template>
                        <LineChart
                            :labels="trendLabels"
                            :values="trendValues"
                        />
                    </Deferred>
                </div>
            </Card>

            <Card class="gap-3 p-5">
                <h2 class="text-sm font-medium text-muted-foreground">
                    Top pracownicy
                </h2>
                <ol class="flex flex-col gap-2">
                    <li
                        v-for="(worker, index) in topWorkers"
                        :key="worker.workerId"
                        class="flex items-center justify-between rounded-md px-2 py-1.5 text-sm odd:bg-muted/50"
                    >
                        <span class="flex items-center gap-2">
                            <span
                                class="flex size-5 items-center justify-center rounded-full bg-primary/10 text-xs font-semibold"
                            >
                                {{ index + 1 }}
                            </span>
                            {{ worker.name }}
                        </span>
                        <span class="font-medium tabular-nums">
                            {{ formatNumber(worker.ordersCount) }}
                        </span>
                    </li>
                    <li
                        v-if="topWorkers.length === 0"
                        class="px-2 py-1.5 text-sm text-muted-foreground"
                    >
                        Brak danych
                    </li>
                </ol>
            </Card>

            <ActivityTimeline :items="activityItems" />
        </div>
    </div>
</template>
