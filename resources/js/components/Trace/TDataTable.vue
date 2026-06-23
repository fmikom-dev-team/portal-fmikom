<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { ref, computed, watch } from 'vue';
import { cn } from '@/lib/utils';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Skeleton } from '@/components/ui/skeleton';
import { Inbox } from 'lucide-vue-next';

/* ───── Types ───── */
export interface Column {
    key: string;
    label: string;
    sortable?: boolean;
    class?: string;
    headerClass?: string;
}

interface Props {
    columns: Column[];
    data: any[];
    loading?: boolean;
    selectable?: boolean;
    rowKey?: string;
    striped?: boolean;
    hoverable?: boolean;
    compact?: boolean;
    class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    selectable: false,
    rowKey: 'id',
    striped: false,
    hoverable: true,
    compact: false,
});

const emit = defineEmits<{
    'row-click': [row: any];
    'selection-change': [keys: any[]];
}>();

/* ───── Selection ───── */
const selectedRows = ref<Set<any>>(new Set());

const isAllSelected = computed(() => {
    if (props.data.length === 0) return false;
    return props.data.every((row) => selectedRows.value.has(row[props.rowKey]));
});

const isIndeterminate = computed(() => {
    if (selectedRows.value.size === 0) return false;
    return !isAllSelected.value;
});

function toggleSelectAll(checked: boolean | 'indeterminate') {
    if (checked === true) {
        props.data.forEach((row) => selectedRows.value.add(row[props.rowKey]));
    } else {
        selectedRows.value.clear();
    }
    emit('selection-change', Array.from(selectedRows.value));
}

function toggleRow(rowKey: any, checked: boolean | 'indeterminate') {
    if (checked === true) {
        selectedRows.value.add(rowKey);
    } else {
        selectedRows.value.delete(rowKey);
    }
    emit('selection-change', Array.from(selectedRows.value));
}

function clearSelection() {
    selectedRows.value.clear();
    emit('selection-change', []);
}

// Clear selection when data changes
watch(
    () => props.data,
    () => {
        selectedRows.value.clear();
    },
);

defineExpose({ selectedRows, clearSelection });

/* ───── Styles ───── */
const thBase = 'px-5 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400';
const tdBase = computed(() =>
    cn('px-5 text-sm text-slate-700 dark:text-slate-300', props.compact ? 'py-2.5' : 'py-4'),
);
</script>

<template>
    <Card :class="cn('rounded-2xl border-slate-100 dark:border-slate-800 shadow-xs overflow-hidden', props.class)">
        <CardContent class="p-0">
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                            <!-- Select-all checkbox -->
                            <th
                                v-if="selectable"
                                :class="cn(thBase, 'w-12 text-center')"
                            >
                                <Checkbox
                                    :checked="isAllSelected ? true : isIndeterminate ? 'indeterminate' : false"
                                    @update:checked="toggleSelectAll"
                                    class="mx-auto"
                                />
                            </th>

                            <!-- Column headers -->
                            <th
                                v-for="col in columns"
                                :key="col.key"
                                :class="cn(thBase, col.class, col.headerClass)"
                            >
                                {{ col.label }}
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                        <!-- Loading skeleton rows -->
                        <template v-if="loading">
                            <tr
                                v-for="n in 3"
                                :key="`skeleton-${n}`"
                                class="animate-pulse"
                            >
                                <td
                                    v-if="selectable"
                                    :class="cn(tdBase, 'w-12 text-center')"
                                >
                                    <Skeleton class="h-4 w-4 rounded mx-auto" />
                                </td>
                                <td
                                    v-for="col in columns"
                                    :key="`skeleton-${n}-${col.key}`"
                                    :class="cn(tdBase, col.class)"
                                >
                                    <Skeleton class="h-4 w-3/4 rounded" />
                                </td>
                            </tr>
                        </template>

                        <!-- Data rows -->
                        <template v-else-if="data.length > 0">
                            <tr
                                v-for="row in data"
                                :key="row[rowKey]"
                                :class="cn(
                                    'group transition-colors',
                                    hoverable && 'hover:bg-[#85B7EB]/8 dark:hover:bg-[#85B7EB]/5 cursor-pointer',
                                    striped && 'even:bg-slate-50/50 dark:even:bg-slate-800/20',
                                )"
                                @click="emit('row-click', row)"
                            >
                                <!-- Row checkbox -->
                                <td
                                    v-if="selectable"
                                    :class="cn(tdBase, 'w-12 text-center')"
                                    @click.stop
                                >
                                    <Checkbox
                                        :checked="selectedRows.has(row[rowKey])"
                                        @update:checked="(v: boolean | 'indeterminate') => toggleRow(row[rowKey], v)"
                                        class="mx-auto"
                                    />
                                </td>

                                <!-- Data cells -->
                                <td
                                    v-for="col in columns"
                                    :key="col.key"
                                    :class="cn(tdBase, col.class)"
                                >
                                    <slot
                                        :name="`cell-${col.key}`"
                                        :row="row"
                                        :value="row[col.key]"
                                    >
                                        {{ row[col.key] }}
                                    </slot>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Empty state -->
            <div
                v-if="!loading && data.length === 0"
                class="flex flex-col items-center justify-center py-16 text-center"
            >
                <slot name="empty">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 mb-4">
                        <Inbox class="h-8 w-8 text-slate-300 dark:text-slate-600" />
                    </div>
                    <p
                        class="text-sm font-bold text-slate-500 dark:text-slate-400 mb-1"
                        style="font-family: 'Poppins', sans-serif;"
                    >
                        Tidak ada data
                    </p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 max-w-sm">
                        Data yang Anda cari tidak ditemukan.
                    </p>
                </slot>
            </div>
        </CardContent>
    </Card>
</template>
