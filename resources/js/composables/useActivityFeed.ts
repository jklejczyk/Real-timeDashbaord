import { ref } from 'vue';
import { useEcho } from '@laravel/echo-vue';
import type { ActivityItem } from '@/types/dashboard';

const MAX_ITEMS = 50;

export function useActivityFeed(initial: ActivityItem[]) {
    const items = ref<ActivityItem[]>([...initial]);

    const addItem = (item: ActivityItem): void => {
        if (items.value.some((existing) => existing.id === item.id)) {
            return;
        }

        items.value.unshift(item);

        if (items.value.length > MAX_ITEMS) {
            items.value.splice(MAX_ITEMS);
        }
    };

    useEcho<ActivityItem>('dashboard', '.order.created', (event) => {
        addItem(event);
    });

    return { items };
}
