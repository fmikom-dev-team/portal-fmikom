<script setup lang="ts">
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

// Guard: jika semua 0, tampilkan placeholder 1 agar donut chart tidak error
const hasData = computed(
	() =>
		props.pending +
			props.warning +
			props.takedown +
			props.rejected +
			props.safe >
		0,
);

const series = computed(
	() =>
		hasData.value
			? [
					props.pending,
					props.warning,
					props.takedown,
					props.rejected,
					props.safe,
				]
			: [1], // placeholder agar chart tidak error
);

const chartColors = computed(() =>
	hasData.value
		? ["#f59e0b", "#f97316", "#ef4444", "#6b7280", "#10b981"]
		: ["#e2e8f0"],
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
						color: "#94a3b8",
						// biome-ignore lint/suspicious/noExplicitAny: ApexCharts global type w
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
						color: "#0f172a",
					},
				},
			},
		},
	},
	stroke: { width: 0 },
	tooltip: {
		style: { fontSize: "12px", fontFamily: "Inter, sans-serif" },
		enabled: hasData.value,
	},
}));

// Legend items definition
const legendItems = computed(() => [
	{
		label: "Menunggu Tinjauan",
		value: props.pending,
		colorClass: "bg-amber-400",
	},
	{ label: "Peringatan", value: props.warning, colorClass: "bg-orange-400" },
	{ label: "Takedown", value: props.takedown, colorClass: "bg-red-400" },
	{ label: "Ditolak", value: props.rejected, colorClass: "bg-slate-400" },
	{ label: "Aman", value: props.safe, colorClass: "bg-emerald-400" },
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
            <div class="w-full space-y-2.5">
                <div v-for="i in 5" :key="i" class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
                        <div class="h-3 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer"
                             :style="{ width: (60 + i * 10) + 'px' }" />
                    </div>
                    <div class="h-3 w-10 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
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
            <p v-if="!hasData" class="text-center text-[11px] text-slate-400 dark:text-zinc-600 -mt-2 mb-2">
                Belum ada data moderasi
            </p>

            <!-- Legend Stats -->
            <div class="mt-3 space-y-2">
                <div
                    v-for="item in legendItems"
                    :key="item.label"
                    class="flex items-center justify-between py-1"
                >
                    <div class="flex items-center gap-2 min-w-0">
                        <div :class="['h-2 w-2 rounded-full shrink-0', item.colorClass]" />
                        <span class="text-[12px] font-medium text-slate-600 dark:text-zinc-400 truncate">{{ item.label }}</span>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="text-[12px] font-bold text-slate-800 dark:text-zinc-100">{{ item.value }}</span>
                        <span class="text-[10px] text-slate-400 dark:text-zinc-600">({{ safePct(item.value) }}%)</span>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
