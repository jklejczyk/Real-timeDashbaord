import { usePage } from '@inertiajs/vue3';
import { useEchoPresence } from '@laravel/echo-vue';
import { ref } from 'vue';
import type { User } from '@/types';
import type { DashboardViewer } from '@/types/dashboard';

type ViewingWhisper = { id: number; name: string; widget: string };

const WHISPER_THROTTLE_MS = 300;

export function useDashboardPresence() {
    const viewers = ref<DashboardViewer[]>([]);
    const viewingByUser = ref<
        Record<
            number,
            {
                name: string;
                widget: string;
            }
        >
    >({});

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
            delete viewingByUser.value[user.id];
        })
        .listenForWhisper('viewing', (e: ViewingWhisper) => {
            viewingByUser.value[e.id] = { name: e.name, widget: e.widget };
        });

    let lastWhisperAt = 0;
    const whisperViewing = (widget: string): void => {
        const now = performance.now();

        if (now - lastWhisperAt < WHISPER_THROTTLE_MS) {
            return;
        }

        lastWhisperAt = now;

        const user = usePage<{ auth: { user: User } }>().props.auth.user;
        channel().whisper('viewing', { id: user.id, name: user.name, widget });
    };

    return { viewers, viewingByUser, whisperViewing };
}
