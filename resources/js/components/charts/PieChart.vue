<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Pie } from 'vue-chartjs';
import { registerCharts } from '@/lib/chartjs';

registerCharts();

const props = defineProps<{
    labels: string[];
    values: number[];
    colors: string[];
}>();

const mounted = ref(false);
onMounted(() => (mounted.value = true));

const data = computed(() => ({
    labels: props.labels,
    datasets: [
        {
            data: props.values,
            backgroundColor: props.colors,
            borderColor: 'rgba(255, 255, 255, 0.6)',
            borderWidth: 2,
        },
    ],
}));

const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom' as const } },
};
</script>

<template>
    <div class="relative h-full w-full">
        <Pie v-if="mounted" :data="data" :options="options" />
    </div>
</template>
