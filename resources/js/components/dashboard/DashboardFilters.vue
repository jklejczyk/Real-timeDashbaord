<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';
import type {
    DashboardFilterValues,
    OrderTypeOption,
    WorkerOption,
} from '@/types/dashboard';

const props = defineProps<{
    filters: DashboardFilterValues;
    workers: WorkerOption[];
    types: OrderTypeOption[];
}>();

const form = reactive({
    start_date: props.filters.startDate,
    end_date: props.filters.endDate,
    worker_id:
        props.filters.workerId !== null ? String(props.filters.workerId) : '',
    type: props.filters.type ?? '',
});

const selectClass =
    'border-input h-9 w-full rounded-md border bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 dark:bg-input/30';

function apply(): void {
    router.get(
        dashboard.url(),
        {
            start_date: form.start_date || undefined,
            end_date: form.end_date || undefined,
            worker_id: form.worker_id || undefined,
            type: form.type || undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function reset(): void {
    form.start_date = '';
    form.end_date = '';
    form.worker_id = '';
    form.type = '';

    router.get(dashboard.url(), {}, { preserveScroll: true });
}
</script>

<template>
    <Card class="p-4">
        <form
            class="grid grid-cols-1 items-end gap-3 sm:grid-cols-2 lg:grid-cols-5"
            @submit.prevent="apply"
        >
            <div class="grid gap-1.5">
                <Label for="start_date">Od</Label>
                <Input id="start_date" v-model="form.start_date" type="date" />
            </div>

            <div class="grid gap-1.5">
                <Label for="end_date">Do</Label>
                <Input id="end_date" v-model="form.end_date" type="date" />
            </div>

            <div class="grid gap-1.5">
                <Label for="worker_id">Pracownik</Label>
                <select
                    id="worker_id"
                    v-model="form.worker_id"
                    :class="selectClass"
                    @change="apply"
                >
                    <option value="">Wszyscy</option>
                    <option
                        v-for="worker in workers"
                        :key="worker.id"
                        :value="String(worker.id)"
                    >
                        {{ worker.name }}
                    </option>
                </select>
            </div>

            <div class="grid gap-1.5">
                <Label for="type">Typ zlecenia</Label>
                <select
                    id="type"
                    v-model="form.type"
                    :class="selectClass"
                    @change="apply"
                >
                    <option value="">Wszystkie</option>
                    <option
                        v-for="option in types"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </div>

            <div class="flex gap-2">
                <button
                    type="submit"
                    class="h-9 flex-1 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground shadow-xs transition hover:bg-primary/90"
                >
                    Zastosuj
                </button>
                <button
                    type="button"
                    class="h-9 rounded-md border border-input px-4 text-sm font-medium transition hover:bg-accent"
                    @click="reset"
                >
                    Reset
                </button>
            </div>
        </form>
    </Card>
</template>
