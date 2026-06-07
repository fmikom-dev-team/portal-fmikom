<script setup lang="ts">
defineProps<{
	title: string;
	value: string | number;
	change: string;
	trend: "up" | "down" | "neutral";
	icon: string;
	iconColor: string;
	loading?: boolean;
}>();
</script>

<template>
    <div
        class="group relative overflow-hidden rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 p-4 sm:p-5 transition-all duration-200 hover:shadow-md hover:shadow-slate-200/50 dark:hover:shadow-zinc-900 hover:-translate-y-0.5"
    >
        <!-- ── Skeleton Loading (modern shimmer) ──────────────────────────── -->
        <template v-if="loading">
            <div class="flex items-start justify-between">
                <div class="space-y-2.5 flex-1 mr-3">
                    <!-- Label skeleton -->
                    <div class="h-2.5 w-24 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
                    <!-- Value skeleton -->
                    <div class="h-8 w-16 rounded-lg bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
                    <!-- Change badge skeleton -->
                    <div class="flex items-center gap-1.5">
                        <div class="h-5 w-14 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
                        <div class="h-2.5 w-20 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
                    </div>
                </div>
                <!-- Icon skeleton -->
                <div class="h-10 w-10 rounded-xl bg-slate-100 dark:bg-zinc-800 animate-shimmer shrink-0" />
            </div>
        </template>

        <!-- ── Real Content ────────────────────────────────────────────────── -->
        <template v-else>
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <!-- Label -->
                    <p class="text-[10px] sm:text-[11px] font-bold uppercase tracking-widest text-slate-400 dark:text-zinc-500 truncate">
                        {{ title }}
                    </p>
                    <!-- Value -->
                    <p class="mt-1.5 text-[24px] sm:text-[28px] font-black text-slate-900 dark:text-white leading-none tracking-tight">
                        {{ typeof value === 'number' ? value.toLocaleString('id-ID') : value }}
                    </p>
                    <!-- Change badge -->
                    <div class="mt-2 flex items-center gap-1.5 flex-wrap">
                        <span
                            :class="[
                                'flex items-center gap-0.5 rounded-full px-1.5 py-0.5 text-[10px] font-bold whitespace-nowrap',
                                trend === 'up' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400' :
                                trend === 'down' ? 'bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400' :
                                'bg-slate-100 text-slate-500 dark:bg-zinc-800 dark:text-zinc-400'
                            ]"
                        >
                            <!-- Up arrow -->
                            <svg v-if="trend === 'up'" class="h-2.5 w-2.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15M4.5 4.5h15v15" />
                            </svg>
                            <!-- Down arrow -->
                            <svg v-else-if="trend === 'down'" class="h-2.5 w-2.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 4.5l-15 15M19.5 19.5h-15v-15" />
                            </svg>
                            <span class="truncate max-w-[80px]">{{ change }}</span>
                        </span>
                        <span v-if="trend !== 'neutral'" class="text-[10px] text-slate-400 dark:text-zinc-500 hidden sm:inline">bulan ini</span>
                    </div>
                </div>

                <!-- Icon Box -->
                <div
                    :class="[
                        'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-white shadow-sm transition-transform duration-200 group-hover:scale-110',
                        iconColor
                    ]"
                    v-html="icon"
                />
            </div>

            <!-- Bottom gradient accent on hover -->
            <div
                :class="[
                    'absolute bottom-0 left-0 right-0 h-0.5 opacity-0 group-hover:opacity-100 transition-opacity duration-300',
                    trend === 'up' ? 'bg-gradient-to-r from-transparent via-emerald-400 to-transparent' :
                    trend === 'down' ? 'bg-gradient-to-r from-transparent via-rose-400 to-transparent' :
                    'bg-gradient-to-r from-transparent via-indigo-400 to-transparent'
                ]"
            />
        </template>
    </div>
</template>
