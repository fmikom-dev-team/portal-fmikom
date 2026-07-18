<script setup lang="ts">
import type { ApexOptions } from "apexcharts";
import { computed, ref } from "vue";

// ─── Props: Real data from parent (DB) ───────────────────────────────────────
interface ChartDataShape {
	categories: string[];
	karya: number[];
	laporan: number[];
	warnings: number[];
}

const props = defineProps<{
	title: string;
	loading?: boolean;
	chartData?: ChartDataShape; // Real data from DB
	loadingChart?: boolean; // Spinner while fetching new range
}>();

const emit = defineEmits<{
	rangeChange: [range: "7d" | "30d" | "90d"];
}>();

const selectedRange = ref<"7d" | "30d" | "90d">("7d");

const onRangeChange = (range: "7d" | "30d" | "90d") => {
	selectedRange.value = range;
	emit("rangeChange", range);
};

// Fallback to empty arrays if data not loaded yet
const data = computed<ChartDataShape>(
	() =>
		props.chartData ?? {
			categories: [],
			karya: [],
			laporan: [],
			warnings: [],
		},
);

const isDark = computed(() =>
	document.documentElement.classList.contains("dark"),
);

const gridColor = computed(() => (isDark.value ? "#27272a" : "#f1f5f9"));
const labelColor = "#94a3b8";

const chartOptions = computed<ApexOptions>(() => ({
	chart: {
		type: "area",
		height: 230,
		toolbar: { show: false },
		animations: { enabled: true, speed: 450, easing: "easeinout" },
		background: "transparent",
		fontFamily: "Inter, sans-serif",
	},
	dataLabels: { enabled: false },
	stroke: {
		curve: "smooth",
		width: 2.5,
	},
	fill: {
		type: "gradient",
		gradient: {
			type: "vertical",
			shadeIntensity: 1,
			opacityFrom: 0.28,
			opacityTo: 0.02,
			stops: [0, 85, 100],
		},
	},
	colors: ["#6366f1", "#f59e0b", "#ef4444"],
	legend: {
		show: true,
		position: "top",
		horizontalAlign: "left",
		labels: { colors: labelColor },
		markers: { size: 5 },
		itemMargin: { horizontal: 14 },
		fontSize: "11px",
		fontWeight: 600,
	},
	xaxis: {
		categories: data.value.categories,
		labels: {
			style: { colors: labelColor, fontSize: "10px", fontWeight: 500 },
			rotate: -15,
			rotateAlways: false,
			hideOverlappingLabels: true,
		},
		axisBorder: { show: false },
		axisTicks: { show: false },
	},
	yaxis: {
		min: 0,
		forceNiceScale: true,
		labels: {
			style: { colors: labelColor, fontSize: "11px" },
			formatter: (val: number) => Math.round(val).toString(),
		},
	},
	grid: {
		borderColor: gridColor.value,
		strokeDashArray: 4,
		xaxis: { lines: { show: false } },
		padding: { left: 0, right: 0 },
	},
	tooltip: {
		theme: "light",
		x: { show: true },
		style: { fontSize: "12px", fontFamily: "Inter, sans-serif" },
		y: {
			formatter: (val: number) => `${val.toString()} item`,
		},
	},
	noData: {
		text: "Belum ada data untuk periode ini",
		align: "center",
		verticalAlign: "middle",
		style: { fontSize: "13px", color: "#94a3b8" },
	},
}));

const series = computed(() => [
	{ name: "Karya Dibuat", data: data.value.karya },
	{ name: "Laporan Masuk", data: data.value.laporan },
	{ name: "Peringatan", data: data.value.warnings },
]);

const rangeLabels = {
	"7d": "7 Hari",
	"30d": "30 Hari",
	"90d": "3 Bulan",
} as const;
</script>

<template>
    <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 p-5">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
            <div class="flex items-center gap-2">
                <h3 class="text-[13px] font-bold text-slate-800 dark:text-zinc-100">{{ title }}</h3>
                <!-- Realtime pulse indicator -->
                <span v-if="!loading && !loadingChart" class="flex items-center gap-1 text-[10px] font-semibold text-emerald-500">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse" />
                    Live
                </span>
            </div>
            <div class="flex items-center rounded-lg border border-slate-200 dark:border-zinc-700 overflow-hidden shrink-0">
                <button
                    v-for="range in (['7d', '30d', '90d'] as const)"
                    :key="range"
                    @click="onRangeChange(range)"
                    :class="[
                        'px-3 py-1.5 text-[11px] font-bold transition-colors',
                        selectedRange === range
                            ? 'bg-indigo-600 text-white'
                            : 'text-slate-500 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800 bg-white dark:bg-zinc-900'
                    ]"
                >
                    {{ rangeLabels[range] }}
                </button>
            </div>
        </div>

        <!-- Skeleton / Loading state -->
        <div v-if="loading || loadingChart" class="h-[230px] flex flex-col gap-3 pt-2">
            <!-- Legend skeleton -->
            <div class="flex gap-5">
                <div v-for="i in 3" :key="i" class="flex items-center gap-1.5">
                    <div class="h-3 w-3 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
                    <div class="h-2.5 w-16 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
                </div>
            </div>
            <!-- Chart skeleton bars -->
            <div class="flex-1 flex items-end gap-1 px-2 pb-2">
                <div
                    v-for="i in 12"
                    :key="i"
                    :style="{ height: (20 + Math.random() * 60) + '%' }"
                    class="flex-1 rounded-t-lg bg-slate-100 dark:bg-zinc-800 animate-shimmer"
                />
            </div>
            <!-- X-axis skeleton -->
            <div class="flex justify-between px-2">
                <div v-for="i in 5" :key="i" class="h-2 w-10 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
            </div>
        </div>

        <!-- Empty state: no data returned at all -->
        <div
            v-else-if="data.karya.length === 0"
            class="h-[230px] flex flex-col items-center justify-center text-center gap-2"
        >
            <div class="h-12 w-12 rounded-2xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                </svg>
            </div>
            <p class="text-[13px] font-semibold text-slate-500 dark:text-zinc-400">Belum ada data aktivitas</p>
            <p class="text-[11px] text-slate-400 dark:text-zinc-600">Data akan muncul setelah ada aktivitas di sistem</p>
        </div>

        <!-- Real Chart -->
        <VueApexCharts
            v-else
            type="area"
            height="230"
            :options="chartOptions"
            :series="series"
        />
    </div>
</template>
