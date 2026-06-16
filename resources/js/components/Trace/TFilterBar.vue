<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { ref, computed, watch } from 'vue';
import { cn } from '@/lib/utils';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Search, SlidersHorizontal, XCircle } from 'lucide-vue-next';

/* ───── Types ───── */
export interface FilterOption {
    value: string;
    label: string;
}

export interface FilterConfig {
    key: string;
    label: string;
    options: FilterOption[];
    defaultValue?: string;
}

interface Props {
    searchPlaceholder?: string;
    filters?: FilterConfig[];
    modelValue?: Record<string, string>;
    debounceMs?: number;
    class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
    searchPlaceholder: 'Cari...',
    debounceMs: 400,
});

const emit = defineEmits<{
    'update:modelValue': [value: Record<string, string>];
    'filter-change': [value: Record<string, string>];
}>();

/* ───── State ───── */
const showFilters = ref(false);
const searchQuery = ref(props.modelValue?.search ?? '');

// Build initial filter values from modelValue or defaults
const filterValues = ref<Record<string, string>>(
    (() => {
        const initial: Record<string, string> = {};
        props.filters?.forEach((f) => {
            initial[f.key] = props.modelValue?.[f.key] ?? f.defaultValue ?? 'all';
        });
        return initial;
    })(),
);

/* ───── Computed ───── */
const hasActiveFilters = computed(() => {
    if (searchQuery.value.trim()) return true;
    return Object.entries(filterValues.value).some(([, v]) => v !== 'all');
});

/* ───── Build & Emit ───── */
function buildFilterObject(): Record<string, string> {
    const result: Record<string, string> = {};
    if (searchQuery.value.trim()) {
        result.search = searchQuery.value.trim();
    }
    Object.entries(filterValues.value).forEach(([key, value]) => {
        if (value && value !== 'all') {
            result[key] = value;
        }
    });
    return result;
}

function emitFilters() {
    const obj = buildFilterObject();
    emit('update:modelValue', obj);
    emit('filter-change', obj);
}

/* ───── Trigger search ───── */
function triggerSearch() {
    emitFilters();
}

function onSearchKeydown(e: KeyboardEvent) {
    if (e.key === 'Enter') {
        triggerSearch();
    }
}

/* ───── Filter change (immediate) ───── */
function onFilterChange(key: string, value: string) {
    filterValues.value[key] = value;
    emitFilters();
}

/* ───── Clear all ───── */
function clearFilters() {
    searchQuery.value = '';
    props.filters?.forEach((f) => {
        filterValues.value[f.key] = f.defaultValue ?? 'all';
    });
    emitFilters();
}

/* ───── Sync with external modelValue changes ───── */
watch(
    () => props.modelValue,
    (newVal) => {
        if (newVal) {
            searchQuery.value = newVal.search ?? '';
            props.filters?.forEach((f) => {
                filterValues.value[f.key] = newVal[f.key] ?? f.defaultValue ?? 'all';
            });
        }
    },
    { deep: true },
);
</script>

<template>
    <Card :class="cn('rounded-2xl border-slate-100 dark:border-slate-800 shadow-xs', props.class)">
        <CardContent class="p-4">
            <div class="flex flex-col gap-3">
                <!-- Search Row -->
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="relative flex-1">
                        <Search
                            class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-slate-400 pointer-events-none"
                        />
                        <Input
                            v-model="searchQuery"
                            :placeholder="searchPlaceholder"
                            class="h-10 rounded-xl border-slate-200 bg-slate-50/50 pl-10 pr-10 text-sm dark:border-slate-700 dark:bg-slate-800/50"
                            @keydown="onSearchKeydown"
                        />
                        <button
                            type="button"
                            class="absolute top-1/2 right-2.5 -translate-y-1/2 flex h-6 items-center rounded-md bg-[#0C447C] px-2 text-[10px] font-bold text-white uppercase tracking-wider transition-all hover:bg-[#0C447C]/80 active:scale-95 dark:bg-[#85B7EB] dark:text-slate-900"
                            @click="triggerSearch"
                        >
                            Cari
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Mobile filter toggle -->
                        <Button
                            v-if="filters && filters.length > 0"
                            variant="outline"
                            size="sm"
                            class="h-10 rounded-xl border-slate-200 text-slate-600 dark:border-slate-700 dark:text-slate-400 sm:hidden"
                            @click="showFilters = !showFilters"
                        >
                            <SlidersHorizontal class="h-4 w-4 mr-1.5" />
                            Filter
                        </Button>

                        <!-- Clear filters -->
                        <Button
                            v-if="hasActiveFilters"
                            variant="ghost"
                            size="sm"
                            class="h-10 rounded-xl text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/20"
                            @click="clearFilters"
                        >
                            <XCircle class="h-4 w-4 mr-1.5" />
                            Hapus Filter
                        </Button>
                    </div>
                </div>

                <!-- Filter Dropdowns -->
                <div
                    v-if="filters && filters.length > 0"
                    class="flex flex-col gap-3 sm:flex-row sm:items-center"
                    :class="{ 'hidden sm:flex': !showFilters }"
                >
                    <Select
                        v-for="filter in filters"
                        :key="filter.key"
                        :model-value="filterValues[filter.key]"
                        @update:model-value="(v: string) => onFilterChange(filter.key, v)"
                    >
                        <SelectTrigger
                            class="h-10 w-full sm:w-[180px] rounded-xl border-slate-200 bg-slate-50/50 text-sm font-medium dark:border-slate-700 dark:bg-slate-800/50"
                        >
                            <SelectValue :placeholder="`Semua ${filter.label}`" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">
                                Semua {{ filter.label }}
                            </SelectItem>
                            <SelectItem
                                v-for="opt in filter.options"
                                :key="opt.value"
                                :value="opt.value"
                            >
                                {{ opt.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
