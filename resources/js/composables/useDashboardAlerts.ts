import { usePage } from '@inertiajs/vue3';
import { useEchoNotification } from '@laravel/echo-vue';
import { toast } from 'vue-sonner';
import type { User } from '@/types';

type DelayedOrdersAlertPayload = {
    level: 'warning' | 'error' | 'info' | 'success';
    message: string;
    delayedCount: number;
};

export function useDashboardAlerts(): void {
    const user = usePage<{ auth: { user: User } }>().props.auth.user;

    useEchoNotification<DelayedOrdersAlertPayload>(
        `App.Models.User.${user.id}`,
        (notification) => {
            toast[notification.level](notification.message);
        },
        'order.delayed.alert',
    );
}
