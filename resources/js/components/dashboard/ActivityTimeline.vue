<script setup lang="ts">
import { Card } from '@/components/ui/card';
import { formatCurrency } from '@/lib/format';
import type { ActivityItem } from '@/types/dashboard';

defineProps<{ items: ActivityItem[] }>();
</script>

<template>
    <Card class="gap-3 p-5">
        <h2 class="text-sm font-medium text-muted-foreground">
            Aktywność na żywo
        </h2>
        <ul class="flex max-h-72 flex-col gap-2 overflow-y-auto">
            <li
                v-for="item in items"
                :key="item.id"
                class="flex items-center justify-between rounded-md px-2 py-1.5 text-sm odd:bg-muted/50"
            >
                <span>
                    <strong>{{ item.workerName }}</strong> - {{ item.type }}
                    <span class="text-muted-foreground"
                        >({{ item.status }})</span
                    >
                </span>
                <span class="font-medium tabular-nums">
                    {{ formatCurrency(item.amount) }}
                </span>
            </li>
            <li
                v-if="items.length === 0"
                class="px-2 py-1.5 text-sm text-muted-foreground"
            >
                Brak aktywności
            </li>
        </ul>
    </Card>
</template>
