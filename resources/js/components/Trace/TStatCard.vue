<script setup lang="ts">
import type { Component, HTMLAttributes } from 'vue';
import { computed } from 'vue';
import { cn } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import { ArrowUp, ArrowDown } from 'lucide-vue-next';

interface Props {
    label: string;
    value: string | number;
    icon?: Component;
    trend?: string;
    trendLabel?: string;
    trendUp?: boolean;
    subText?: string;
    color?: 'primary' | 'accent' | 'emerald' | 'rose' | 'slate' | 'violet' | 'blue' | 'green' | 'purple';
    loading?: boolean;
    class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
    color: 'primary',
    loading: false,
});

// Auto-detect trend direction from trend string if trendUp is not explicitly set
const isTrendUp = computed(() => {
    if (props.trendUp !== undefined) return props.trendUp;
    if (!props.trend) return false;
    // Detect positive trend from string like "+5.2%", "+100%"
    return props.trend.trim().startsWith('+');
});

const iconCircleClasses = computed(() => {
    const colorMap: Record<NonNullable<Props['color']>, string> = {
        primary: 'bg-[#0C447C]/10 text-[#0C447C] dark:bg-[#85B7EB]/15 dark:text-[#85B7EB]',
        accent: 'bg-[#EF9F27]/10 text-[#EF9F27] dark:bg-[#FAC775]/15 dark:text-[#FAC775]',
        emerald: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400',
        green: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400',
        rose: 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400',
        slate: 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
        violet: 'bg-violet-50 text-violet-600 dark:bg-violet-950/40 dark:text-violet-400',
        purple: 'bg-violet-50 text-violet-600 dark:bg-violet-950/40 dark:text-violet-400',
        blue: 'bg-[#0C447C]/10 text-[#0C447C] dark:bg-[#85B7EB]/15 dark:text-[#85B7EB]',
    };
    return colorMap[props.color ?? 'primary'];
});
</script>

<template>
    <Card
        :class="cn(
            'overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition-shadow duration-200 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900',
            props.class,
        )"
    >
        <CardContent class="p-5">
            <!-- Loading state -->
            <template v-if="loading">
                <div class="flex items-start gap-4">
                    <Skeleton class="h-11 w-11 shrink-0 rounded-xl" />
                    <div class="flex-1 space-y-2.5">
                        <Skeleton class="h-3 w-20 rounded" />
                        <Skeleton class="h-7 w-28 rounded" />
                        <Skeleton class="h-4 w-24 rounded" />
                    </div>
                </div>
            </template>

            <!-- Content state -->
            <template v-else>
                <div class="flex items-start gap-4">
                    <!-- Icon circle -->
                    <div
                        v-if="icon"
                        :class="cn(
                            'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition-colors',
                            iconCircleClasses,
                        )"
                    >
                        <component :is="icon" class="h-5 w-5" />
                    </div>

                    <!-- Text content -->
                    <div class="min-w-0 flex-1">
                        <!-- Label -->
                        <p
                            class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-zinc-500"
                            style="font-family: 'Poppins', sans-serif"
                        >
                            {{ label }}
                        </p>

                        <!-- Value + Trend -->
                        <div class="mt-1.5 flex items-end gap-2.5">
                            <h2
                                class="text-3xl font-black leading-none tracking-tight text-slate-900 dark:text-white"
                                style="font-family: 'Inter', sans-serif"
                            >
                                {{ value }}
                            </h2>

                            <!-- Trend badge -->
                            <span
                                v-if="trend"
                                class="mb-0.5 inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 text-[11px] font-bold leading-none"
                                :class="isTrendUp
                                    ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400'
                                    : 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400'
                                "
                            >
                                <ArrowUp v-if="isTrendUp" class="h-3 w-3" />
                                <ArrowDown v-else class="h-3 w-3" />
                                {{ trend }}
                            </span>
                        </div>

                        <!-- Trend label / Sub text -->
                        <p
                            v-if="trendLabel || subText"
                            class="mt-1 text-[11px] font-medium text-slate-400 dark:text-zinc-500"
                        >
                            {{ trendLabel || subText }}
                        </p>
                    </div>
                </div>
            </template>
        </CardContent>
    </Card>
</template>
