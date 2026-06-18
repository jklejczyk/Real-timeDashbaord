<script setup lang="ts">
import { computed } from 'vue';
import { Card } from '@/components/ui/card';
import { cn } from '@/lib/utils';

const props = defineProps<{
    title: string;
    value: string;
    caption?: string;
    change?: number | null;
}>();

const hasChange = computed(
    () => props.change !== null && props.change !== undefined,
);

const trendClass = computed(() => {
    if (!hasChange.value || props.change === 0) {
        return 'text-muted-foreground';
    }

    return (props.change as number) > 0
        ? 'text-emerald-600 dark:text-emerald-400'
        : 'text-red-600 dark:text-red-400';
});

const trendSymbol = computed(() => {
    if (!hasChange.value || props.change === 0) {
        return '–';
    }

    return (props.change as number) > 0 ? '▲' : '▼';
});
</script>

<template>
    <Card class="gap-2 p-5">
        <p class="text-sm font-medium text-muted-foreground">{{ title }}</p>
        <p class="text-2xl font-semibold tracking-tight tabular-nums">
            {{ value }}
        </p>

        <p
            v-if="hasChange"
            :class="cn('text-xs font-medium tabular-nums', trendClass)"
        >
            {{ trendSymbol }} {{ Math.abs(change as number).toFixed(1) }}%
            <span v-if="caption" class="text-muted-foreground">{{
                caption
            }}</span>
        </p>
        <p v-else-if="caption" class="text-xs text-muted-foreground">
            {{ caption }}
        </p>
    </Card>
</template>
