import { ref } from 'vue';
import { useEchoPresence } from '@laravel/echo-vue';
import type { DashboardViewer } from '@/types/dashboard';

export function useDashboardPresence() {
    const viewers = ref<DashboardViewer[]>([]);

    const { channel } = useEchoPresence('dashboard');

    channel()
        .here((users: DashboardViewer[]) => {
            viewers.value = users;
        })
        .joining((user: DashboardViewer) => {
            if (!viewers.value.some((v) => v.id === user.id)) {
                viewers.value.push(user);
            }
        })
        .leaving((user: DashboardViewer) => {
            viewers.value = viewers.value.filter((v) => v.id !== user.id);
        });

    return { viewers };
}
