import { computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { echo, useConnectionStatus } from '@laravel/echo-vue';

const RESYNC_PROPS = [
    'revenue',
    'statusCounts',
    'topWorkers',
    'revenuePerMonth',
    'ordersTrend',
    'recentActivity',
];

const RECONNECT_DELAY_MS = 3000;

export function useConnectionResilience() {
    const status = useConnectionStatus();
    const isOnline = computed(() => status.value === 'connected');

    let hadConnection = false;
    let lostConnection = false;

    watch(status, (current) => {
        if (current === 'connected') {
            if (lostConnection) {
                router.reload({ only:
                    RESYNC_PROPS });
                lostConnection = false;
            }

            hadConnection = true;
        } else if (current === 'failed') {
            if (hadConnection) {
                lostConnection = true;
            }

            setTimeout(() => echo().connect(), RECONNECT_DELAY_MS);
        } else if (hadConnection) {
            lostConnection = true;
        }
    });

    return { status, isOnline };
}
