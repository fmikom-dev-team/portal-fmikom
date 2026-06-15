<script setup lang="ts">
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    PointElement,
    LineElement,
    BarElement,
    CategoryScale,
    LinearScale,
    Filler,
} from 'chart.js';
import { TrendingUp, Filter } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Bar } from 'vue-chartjs';

ChartJS.register(
    Title, Tooltip, Legend, PointElement, LineElement, BarElement,
    CategoryScale, LinearScale, Filler,
);

interface GrowthData {
    labels: (string | number)[];
    totals: number[];
    perProdi: Record<string, number[]>;
    prodiList: string[];
    angkatanList: (string | number)[];
}

const props = defineProps<{ data: GrowthData }>();

const selectedProdi = ref<string>('semua');
const selectedAngkatan = ref<string>('semua');

const barColors = ['#6366f1', '#8b5cf6', '#a78bfa', '#c4b5fd', '#818cf8', '#4f46e5'];

const filteredLabels = computed(() => {
    if (selectedAngkatan.value === 'semua') {
        return props.data.labels.map(String);
    }
    return [String(selectedAngkatan.value)];
});

const filteredData = computed(() => {
    const allLabels = props.data.labels.map(String);

    if (selectedAngkatan.value !== 'semua') {
        const idx = allLabels.indexOf(String(selectedAngkatan.value));
        if (idx === -1) return [];

        if (selectedProdi.value === 'semua') {
            // Show all prodi for selected angkatan as grouped bars
            return props.data.prodiList.map((prodi, pi) => ({
                label: prodi,
                data: [props.data.perProdi[prodi][idx]],
                backgroundColor: barColors[pi % barColors.length],
                borderRadius: 8,
                borderSkipped: false as const,
            }));
        } else {
            return [{
                label: selectedProdi.value,
                data: [props.data.perProdi[selectedProdi.value]?.[idx] ?? 0],
                backgroundColor: barColors[0],
                borderRadius: 8,
                borderSkipped: false as const,
            }];
        }
    }

    // All angkatan
    if (selectedProdi.value === 'semua') {
        return [{
            label: 'Semua Prodi',
            data: props.data.totals,
            backgroundColor: 'rgba(99, 102, 241, 0.8)',
            hoverBackgroundColor: 'rgba(99, 102, 241, 1)',
            borderRadius: 8,
            borderSkipped: false as const,
            barPercentage: 0.6,
            categoryPercentage: 0.7,
        }];
    } else {
        const prodiData = props.data.perProdi[selectedProdi.value] ?? [];
        return [{
            label: selectedProdi.value,
            data: prodiData,
            backgroundColor: barColors[props.data.prodiList.indexOf(selectedProdi.value) % barColors.length],
            hoverBackgroundColor: barColors[props.data.prodiList.indexOf(selectedProdi.value) % barColors.length],
            borderRadius: 8,
            borderSkipped: false as const,
            barPercentage: 0.6,
            categoryPercentage: 0.7,
        }];
    }
});

const processedData = computed(() => ({
    labels: filteredLabels.value,
    datasets: filteredData.value,
}));

const totalShown = computed(() =>
    filteredData.value.reduce((sum, ds) => sum + ds.data.reduce((a: number, b: number) => a + b, 0), 0)
);

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: 'rgba(15, 23, 42, 0.9)',
            padding: 10,
            titleFont: { size: 12, weight: 'bold' as const },
            bodyFont: { size: 12 },
            cornerRadius: 10,
            displayColors: true,
            boxPadding: 4,
            callbacks: {
                title: (items: any) => `Angkatan ${items[0].label}`,
                label: (ctx: any) => ` ${ctx.dataset.label}: ${ctx.parsed.y} Alumni`,
            }
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: { display: true, color: 'rgba(0, 0, 0, 0.04)' },
            ticks: { stepSize: 1, font: { size: 11, weight: '600' as const }, color: '#94a3b8', padding: 8 },
            border: { display: false },
        },
        x: {
            grid: { display: false },
            ticks: { font: { size: 11, weight: '700' as const }, color: '#64748b', padding: 4 },
            border: { display: false },
        },
    },
    interaction: { intersect: false, mode: 'index' as const },
};
</script>

<template>
    <div class="overflow-hidden rounded-3xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 pt-6 pb-1">
            <div class="flex items-center gap-2">
                <div class="rounded-lg bg-indigo-500/10 p-2">
                    <TrendingUp class="h-4 w-4 text-indigo-600" />
                </div>
                <div>
                    <h3 class="text-base font-black text-slate-800 dark:text-white">Alumni per Angkatan</h3>
                    <p class="text-[10px] font-bold text-slate-400">
                        Menampilkan {{ totalShown }} alumni
                        <span v-if="selectedProdi !== 'semua'" class="text-indigo-500">• {{ selectedProdi }}</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex items-center gap-3 px-6 py-3">
            <div class="flex items-center gap-1.5">
                <Filter class="h-3 w-3 text-slate-400" />
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Filter</span>
            </div>
            <select
                v-model="selectedProdi"
                class="h-8 rounded-lg bg-slate-50 dark:bg-slate-800 px-3 text-[11px] font-bold text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 outline-none focus:border-indigo-500 transition-colors"
            >
                <option value="semua">Semua Prodi</option>
                <option v-for="prodi in props.data.prodiList" :key="prodi" :value="prodi">{{ prodi }}</option>
            </select>
            <select
                v-model="selectedAngkatan"
                class="h-8 rounded-lg bg-slate-50 dark:bg-slate-800 px-3 text-[11px] font-bold text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 outline-none focus:border-indigo-500 transition-colors"
            >
                <option value="semua">Semua Angkatan</option>
                <option v-for="a in props.data.angkatanList" :key="a" :value="a">{{ a }}</option>
            </select>
        </div>

        <!-- Chart -->
        <div class="px-6 pb-6">
            <div class="h-[280px] w-full">
                <Bar :data="processedData" :options="chartOptions" />
            </div>
        </div>

        <!-- Legend (when showing per-prodi for single angkatan) -->
        <div v-if="selectedAngkatan !== 'semua' && selectedProdi === 'semua'" class="px-6 pb-4 flex flex-wrap gap-3 border-t border-slate-50 dark:border-slate-800 pt-3">
            <div v-for="(prodi, i) in props.data.prodiList" :key="prodi" class="flex items-center gap-1.5">
                <div class="h-2.5 w-2.5 rounded-sm" :style="`background:${barColors[i % barColors.length]}`"></div>
                <span class="text-[10px] font-bold text-slate-500">{{ prodi }}</span>
            </div>
        </div>
    </div>
</template>
