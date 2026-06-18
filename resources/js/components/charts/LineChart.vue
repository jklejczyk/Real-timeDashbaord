<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Line } from 'vue-chartjs';
import { registerCharts } from '@/lib/chartjs';

registerCharts();

const props = defineProps<{
    labels: string[];
    values: number[];
    label?: string;
}>();

const mounted = ref(false);
onMounted(() => (mounted.value = true));

const data = computed(() => ({
    labels: props.labels,
    datasets: [
        {
            label: props.label ?? '',
            data: props.values,
            borderColor: 'rgb(16, 185, 129)',
            backgroundColor: 'rgba(16, 185, 129, 0.15)',
            fill: true,
            tension: 0.3,
            pointRadius: 0,
            pointHoverRadius: 4,
            borderWidth: 2,
        },
    ],
}));

const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    interaction: { mode: 'index' as const, intersect: false },
    scales: {
        y: { beginAtZero: true, ticks: { precision: 0 } },
        x: { grid: { display: false } },
    },
};
</script>

<template>
    <div class="relative h-full w-full">
        <Line v-if="mounted" :data="data" :options="options" />
    </div>
</template>
