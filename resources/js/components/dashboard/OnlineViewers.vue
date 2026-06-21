<script setup lang="ts">
import type { DashboardViewer } from '@/types/dashboard';

defineProps<{ viewers: DashboardViewer[] }>();

const initials = (name: string): string =>
    name
        .split(' ')
        .map((part) => part[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
</script>

<template>
    <div class="flex items-center gap-3">
        <span class="text-sm text-muted-foreground">
            {{ viewers.length }} online
        </span>
        <div class="flex -space-x-2">
            <span
                v-for="viewer in viewers"
                :key="viewer.id"
                :title="viewer.name"
                class="flex size-7 items-center justify-center rounded-full border-2 border-background bg-primary/10 text-xs font-semibold"
            >
                {{ initials(viewer.name) }}
            </span>
        </div>
    </div>
</template>
