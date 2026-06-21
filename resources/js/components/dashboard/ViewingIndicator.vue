<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    viewing: Record<number, { name: string; widget: string }>;
}>();

const entries = computed(() => Object.values(props.viewing));
</script>

<template>
    <div
        v-if="entries.length"
        class="flex flex-col gap-1 text-xs text-muted-foreground"
    >
        <span v-for="entry in entries" :key="entry.name">
            <strong>{{ entry.name }}</strong>
            ogląda: {{ entry.widget }}
        </span>
    </div>
</template>
