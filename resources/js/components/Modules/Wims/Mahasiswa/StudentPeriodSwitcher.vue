<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { CalendarDays, ChevronDown } from 'lucide-vue-next';

import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

type PeriodOption = {
    id?: number | string | null;
    label?: string | null;
    period_label?: string | null;
    status_label?: string | null;
    is_active?: boolean | null;
};

const props = withDefaults(
    defineProps<{
        periods?: PeriodOption[];
        selectedPeriodId?: number | string | null;
        label?: string;
        helper?: string;
    }>(),
    {
        periods: () => [],
        selectedPeriodId: null,
        label: 'Periode',
        helper: 'Pilih periode yang ingin dibuka.',
    },
);

const periodOptions = computed(() =>
    (props.periods ?? [])
        .map((period) => {
            const value = period.id != null ? String(period.id) : '';
            const label =
                period.label ??
                period.period_label ??
                `Periode ${value || '-'}`;
            const meta = period.status_label ?? (period.is_active ? 'Aktif' : null);

            return {
                value,
                label,
                meta,
            };
        })
        .filter((period) => period.value.length > 0),
);

const selectedValue = computed(() =>
    props.selectedPeriodId != null ? String(props.selectedPeriodId) : '',
);

const selectedPeriod = computed(
    () =>
        periodOptions.value.find((period) => period.value === selectedValue.value) ??
        periodOptions.value[0] ??
        null,
);

const hasMultiplePeriods = computed(() => periodOptions.value.length > 1);
const selectedPeriodBadge = computed(() =>
    selectedPeriod.value?.meta === 'Aktif' ? 'Aktif' : null,
);

const switchPeriod = (value: string) => {
    if (!value || value === selectedValue.value) {
        return;
    }

    const url = new URL(window.location.href);
    const query = Object.fromEntries(url.searchParams.entries());

    router.get(
        url.pathname,
        {
            ...query,
            pendaftaran: value,
        },
        {
            preserveScroll: true,
            preserveState: false,
            replace: true,
        },
    );
};
</script>

<template>
    <div
        v-if="hasMultiplePeriods"
        class="rounded-xl border border-slate-200/80 bg-white/80 px-3.5 py-3 shadow-[0_10px_28px_-24px_rgba(15,23,42,0.18)] backdrop-blur-md dark:border-slate-700/70 dark:bg-slate-900/60"
    >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 items-center gap-3">
                <div class="flex size-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                    <CalendarDays class="size-4" />
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">
                        {{ label }}
                    </p>
                    <div class="flex min-w-0 items-center gap-2">
                        <p class="truncate text-sm font-semibold text-wims-text dark:text-white">
                            {{ selectedPeriod?.label ?? helper }}
                        </p>
                        <span
                            v-if="selectedPeriodBadge"
                            class="inline-flex shrink-0 items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 ring-1 ring-emerald-200/80 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-500/30"
                        >
                            {{ selectedPeriodBadge }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:min-w-[240px] sm:justify-end">
                <Select
                    :model-value="selectedValue"
                    :disabled="!hasMultiplePeriods"
                    @update:model-value="switchPeriod"
                >
                    <SelectTrigger class="h-9 w-full rounded-lg border-slate-200/80 bg-white/90 text-sm shadow-none dark:border-slate-700/70 dark:bg-slate-800/90 sm:w-[240px]">
                        <SelectValue :placeholder="helper" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="period in periodOptions"
                            :key="period.value"
                            :value="period.value"
                        >
                            <span class="flex items-center justify-between gap-2">
                                <span class="truncate">{{ period.label }}</span>
                                <span
                                    v-if="period.value === selectedValue"
                                    class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 ring-1 ring-emerald-200/80 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-500/30"
                                >
                                    Aktif
                                </span>
                                <span
                                    v-else-if="period.meta"
                                    class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400"
                                >
                                    {{ period.meta }}
                                </span>
                            </span>
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>
    </div>
</template>
