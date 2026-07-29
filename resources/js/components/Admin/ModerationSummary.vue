<script setup lang="ts">
import { useAppearance } from "@/composables/useAppearance";
import type { ApexOptions } from "apexcharts";
import { computed } from "vue";
import VueApexCharts from "vue3-apexcharts";

const props = defineProps<{
	total: number;
	pending: number;
	warning: number;
	takedown: number;
	rejected: number;
	safe: number;
	loading?: boolean;
}>();

const { resolvedAppearance } = useAppearance();
const isDark = computed(() => resolvedAppearance.value === "dark");

// Guard: jika semua 0, tampilkan placeholder agar donut chart tidak error
const hasData = computed(
	() =>
		props.pending +
			props.warning +
			props.takedown +
			props.rejected +
			props.safe >
		0,
);

const series = computed(() =>
	hasData.value
		? [props.pending, props.warning, props.takedown, props.rejected, props.safe]
		: [1],
);

const valueColor = computed(() => (isDark.value ? "#ffffff" : "#0f172a"));
const labelColor = computed(() => (isDark.value ? "#a1a1aa" : "#64748b"));

// Warna-warna yang lebih kuat dan konsisten di light & dark mode
const chartColors = computed(() =>
	hasData.value
		? ["#f59e0b", "#f97316", "#ef4444", "#6b7280", "#10b981"]
		: [isDark.value ? "#3f3f46" : "#e2e8f0"],
);

const chartLabels = computed(() =>
	hasData.value
		? ["Menunggu Tinjauan", "Peringatan", "Takedown", "Ditolak", "Aman"]
		: ["Tidak Ada Data"],
);

const chartOptions = computed<ApexOptions>(() => ({
	chart: {
		type: "donut",
		animations: { enabled: true, speed: 600 },
		background: "transparent",
	},
	colors: chartColors.value,
	labels: chartLabels.value,
	legend: { show: false },
	dataLabels: { enabled: false },
	plotOptions: {
		pie: {
			donut: {
				size: "72%",
				labels: {
					show: true,
					total: {
						show: true,
						label: hasData.value ? "Total" : "",
						fontSize: "11px",
						fontWeight: 700,
						color: labelColor.value,
						// biome-ignore lint/suspicious/noExplicitAny: ApexCharts global type workaround
						formatter: (w: any) =>
							hasData.value
								? w.globals.seriesTotals
										.reduce((a: number, b: number) => a + b, 0)
										.toString()
								: "0",
					},
					value: {
						fontSize: "22px",
						fontWeight: 900,
						color: valueColor.value,
					},
				},
			},
		},
	},
	stroke: { width: 0 },
	tooltip: {
		theme: isDark.value ? "dark" : "light",
		style: { fontSize: "12px", fontFamily: "Inter, sans-serif" },
		enabled: hasData.value,
	},
}));

// Legend items dengan warna bg, text, dan progress bar yang solid untuk light & dark
const legendItems = computed(() => [
	{
		label: "Menunggu Tinjauan",
		value: props.pending,
		dotClass: "bg-amber-400",
		badgeClass: "bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 dark:border dark:border-amber-800/50",
		barClass: "bg-amber-400",
	},
	{
		label: "Peringatan",
		value: props.warning,
		dotClass: "bg-orange-400",
		badgeClass: "bg-orange-50 text-orange-700 dark:bg-orange-950/60 dark:text-orange-300 dark:border dark:border-orange-800/50",
		barClass: "bg-orange-400",
	},
	{
		label: "Takedown",
		value: props.takedown,
		dotClass: "bg-red-400",
		badgeClass: "bg-red-50 text-red-700 dark:bg-red-950/60 dark:text-red-300 dark:border dark:border-red-800/50",
		barClass: "bg-red-400",
	},
	{
		label: "Ditolak",
		value: props.rejected,
		dotClass: "bg-slate-400",
		badgeClass: "bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-300 dark:border dark:border-zinc-700/50",
		barClass: "bg-slate-400",
	},
	{
		label: "Aman",
		value: props.safe,
		dotClass: "bg-emerald-400",
		badgeClass: "bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border dark:border-emerald-800/50",
		barClass: "bg-emerald-400",
	},
]);

const safePct = (val: number) =>
	props.total > 0 ? Math.round((val / props.total) * 100) : 0;
</script>

<template>
    <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 p-5 h-full">
        <h3 class="text-[13px] font-bold text-slate-800 dark:text-zinc-100 mb-4">Ringkasan Moderasi</h3>

        <!-- ── Skeleton ────────────────────────────────────────────────────── -->
        <div v-if="loading" class="flex flex-col items-center gap-4">
            <!-- Donut skeleton -->
            <div class="relative h-36 w-36 flex items-center justify-center">
                <div class="absolute inset-0 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
                <div class="absolute inset-[22%] rounded-full bg-white dark:bg-zinc-900" />
            </div>
            <!-- Legend skeleton rows -->
            <div class="w-full space-y-3">
                <div v-for="i in 5" :key="i" class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="h-2 w-2 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
                            <div class="h-3 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer" :style="{ width: (60 + i * 10) + 'px' }" />
                        </div>
                        <div class="h-5 w-10 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
                    </div>
                    <div class="h-1 w-full rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
                </div>
            </div>
        </div>

        <template v-else>
            <!-- Donut Chart -->
            <div class="flex justify-center -my-2">
                <VueApexCharts
                    type="donut"
                    width="190"
                    :options="chartOptions"
                    :series="series"
                />
            </div>

            <!-- No data notice -->
            <p v-if="!hasData" class="text-center text-[11px] text-slate-400 dark:text-zinc-600 -mt-2 mb-3">
                Belum ada data moderasi
            </p>

            <!-- Legend Stats — dengan progress bar dan badge nilai -->
            <div class="mt-3 space-y-2.5">
                <div
                    v-for="item in legendItems"
                    :key="item.label"
                    class="space-y-1"
                >
                    <!-- Row: dot + label + badge nilai -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <div :class="['h-2 w-2 rounded-full shrink-0', item.dotClass]" />
                            <span class="text-[11.5px] font-medium text-slate-600 dark:text-zinc-400 truncate">{{ item.label }}</span>
                        </div>
                        <!-- Badge nilai: warna berbeda per kategori, solid di dark mode -->
                        <span :class="['shrink-0 text-[10px] font-black px-1.5 py-0.5 rounded-md', item.badgeClass]">
                            {{ item.value }} <span class="opacity-60 font-bold">({{ safePct(item.value) }}%)</span>
                        </span>
                    </div>

                    <!-- Progress bar -->
                    <div class="h-1 w-full rounded-full bg-slate-100 dark:bg-zinc-800 overflow-hidden">
                        <div
                            :class="['h-full rounded-full transition-all duration-500', item.barClass]"
                            :style="{ width: safePct(item.value) + '%' }"
                        />
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
