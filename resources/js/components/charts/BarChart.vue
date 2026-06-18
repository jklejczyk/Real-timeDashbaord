<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Bar } from 'vue-chartjs';
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
            backgroundColor: 'rgba(59, 130, 246, 0.65)',
            hoverBackgroundColor: 'rgba(59, 130, 246, 0.9)',
            borderRadius: 6,
            maxBarThickness: 48,
        },
    ],
}));

const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: { beginAtZero: true, ticks: { precision: 0 } },
        x: { grid: { display: false } },
    },
};
</script>

<template>
    <div class="relative h-full w-full">
        <Bar v-if="mounted" :data="data" :options="options" />
    </div>
</template>
