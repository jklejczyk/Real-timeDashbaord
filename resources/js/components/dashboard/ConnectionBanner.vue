<script setup lang="ts">
import { computed } from 'vue';
import type { ConnectionStatus } from
        '@laravel/echo-vue';

const props = defineProps<{ status:
        ConnectionStatus }>();

const message = computed(() => {
    switch (props.status) {
        case 'connecting':
        case 'reconnecting':
            return 'Łączenie na żywo…';
        case 'failed':
            return 'Nie udało się połączyć na żywo.';
        default:
            return 'Utracono połączenie na żywo - dane mogą być nieaktualne.';
    }
});
</script>

<template>
    <div
        v-if="status !== 'connected'"
        class="flex items-center gap-2 rounded-md border border-amber-300 bg-amber-50 px-4 py-2 text-sm
         text-amber-900 dark:border-amber-700 dark:bg-amber-950 dark:text-amber-200"
    >
          <span class="size-2 animate-pulse rounded-full bg-amber-500" />
        {{ message }}
    </div>
</template>
