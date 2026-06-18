import { router } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';

type OrderCreatedPayload = {
    id: number;
    type: string;
    status: string;
    amount: string;
};

const LIVE_PROPS = [
    'revenue',
    'statusCounts',
    'topWorkers',
    'revenuePerMonth',
    'ordersTrend',
];
const RELOAD_DEBOUNCE_MS = 400;

export function useLiveDashboard(): void {
    let reloadTimer: ReturnType<typeof setTimeout> | undefined;

    const scheduleReload = (): void => {
        clearTimeout(reloadTimer);
        reloadTimer = setTimeout(() => {
            router.reload({ only: LIVE_PROPS });
        }, RELOAD_DEBOUNCE_MS);
    };

    useEcho<OrderCreatedPayload>('dashboard', '.order.created', (event) => {
        scheduleReload();
    });
}
