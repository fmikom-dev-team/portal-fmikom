<script setup lang="ts">
import { Download } from 'lucide-vue-next';
import VueApexCharts from 'vue3-apexcharts';
import { type ViewportStats } from '@/composables/trace/useMapViewport';
import { CATEGORY_CONFIG } from '@/composables/trace/useMapData';

defineProps<{
    viewportStats: ViewportStats;
    isViewportUpdating: boolean;
    completionMeta: {
        total_alumni: number;
        mapped_count: number;
        completion_rate: number;
    };
    statusChartOptions: Record<string, unknown>;
    statusChartSeries: number[];
    sektorChartOptions: Record<string, unknown>;
    sektorChartSeries: Record<string, unknown>[];
}>();

const emit = defineEmits<{
    exportCsv: [];
    exportGeoJson: [];
}>();
</script>

<template>
    <!-- Stat Cards Row -->
    <div class="flex gap-2">
        <div class="flex-1 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-xl px-4 py-3 text-center transition-all duration-300"
            :class="isViewportUpdating ? 'ring-2 ring-blue-400/50 scale-[1.02]' : ''"
        >
            <div class="flex items-center justify-center gap-1">
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Di Viewport</p>
                <div v-if="isViewportUpdating" class="h-1.5 w-1.5 rounded-full bg-blue-500 animate-ping"></div>
            </div>
            <p class="text-2xl font-black text-slate-800 dark:text-white leading-none mt-1">{{ viewportStats.total }}</p>
        </div>
        <div class="flex-1 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-xl px-4 py-3 text-center">
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Total Alumni</p>
            <p class="text-2xl font-black text-slate-800 dark:text-white leading-none mt-1">{{ completionMeta.total_alumni }}</p>
        </div>
    </div>

    <!-- Distribution Pills -->
    <div class="flex flex-wrap gap-1.5">
        <div v-for="(cfg, key) in CATEGORY_CONFIG" :key="key"
            class="flex items-center gap-1 rounded-lg px-2.5 py-1 shadow-sm"
            :style="{ background: cfg.color + 'dd', color: 'white' }"
        >
            <span class="text-[10px] font-black">{{ key === 'bekerja' ? viewportStats.bekerja : key === 'wirausaha' ? viewportStats.wirausaha : key === 'studi' ? viewportStats.studi : viewportStats.belum }}</span>
            <span class="text-[9px] font-bold opacity-90">{{ cfg.label }}</span>
        </div>
    </div>

    <!-- Completion Rate -->
    <div class="rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-xl px-4 py-3">
        <div class="flex items-center justify-between mb-1.5">
            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Keterisian Peta</span>
            <span class="text-sm font-black" :class="completionMeta.completion_rate >= 80 ? 'text-emerald-500' : completionMeta.completion_rate >= 50 ? 'text-amber-500' : 'text-red-500'">
                {{ completionMeta.completion_rate }}%
            </span>
        </div>
        <div class="h-2 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
            <div class="h-full rounded-full transition-all duration-700"
                :class="completionMeta.completion_rate >= 80 ? 'bg-emerald-500' : completionMeta.completion_rate >= 50 ? 'bg-amber-500' : 'bg-red-500'"
                :style="{ width: completionMeta.completion_rate + '%' }"></div>
        </div>
        <p class="text-[9px] text-slate-400 mt-1">{{ completionMeta.mapped_count }} dari {{ completionMeta.total_alumni }} alumni terpetakan</p>
    </div>

    <!-- Status Donut Chart -->
    <div class="rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-xl px-4 py-3" v-if="viewportStats.total > 0">
        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">📊 Distribusi Status (Viewport)</p>
        <VueApexCharts type="donut" height="180" :options="statusChartOptions" :series="statusChartSeries" />
    </div>

    <!-- Sektor Bar Chart -->
    <div class="rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-xl px-4 py-3" v-if="viewportStats.sektorData.length > 0">
        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">📊 Top Sektor Industri (Viewport)</p>
        <VueApexCharts type="bar" height="180" :options="sektorChartOptions" :series="sektorChartSeries" />
    </div>

    <!-- Top Rankings -->
    <div class="rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-xl px-4 py-3" v-if="viewportStats.cityData.length > 0">
        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">📍 Top Wilayah (Viewport)</p>
        <div class="space-y-1">
            <div v-for="(item, i) in viewportStats.cityData.slice(0, 5)" :key="item.name" class="flex items-center gap-2">
                <span class="text-[9px] font-black text-slate-300 w-3">{{ i + 1 }}</span>
                <div class="flex-1 relative h-5 rounded bg-blue-50 dark:bg-blue-900/20 overflow-hidden">
                    <div class="h-full bg-blue-100 dark:bg-blue-900/40 rounded transition-all" :style="{ width: Math.max(10, (item.count / (viewportStats.cityData[0]?.count || 1)) * 100) + '%' }"></div>
                    <div class="absolute inset-0 flex items-center justify-between px-2">
                        <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300 truncate">{{ item.name }}</span>
                        <span class="text-[10px] font-black text-blue-600">{{ item.count }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Angkatan Breakdown -->
    <div class="rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-xl px-4 py-3" v-if="viewportStats.angkatanData.length > 0">
        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">🎓 Sebaran Angkatan</p>
        <div class="flex flex-wrap gap-1.5">
            <div v-for="ab in viewportStats.angkatanData" :key="ab.angkatan"
                class="flex items-center gap-1 px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700"
            >
                <span class="text-[10px] font-bold text-slate-500">{{ ab.angkatan }}</span>
                <span class="text-[10px] font-black text-[#0C447C] dark:text-[#85B7EB]">{{ ab.count }}</span>
            </div>
        </div>
    </div>

    <!-- Export -->
    <div class="rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-xl px-4 py-3">
        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">📥 Ekspor Data (Viewport)</p>
        <div class="flex gap-2">
            <button @click="emit('exportCsv')" class="flex-1 flex items-center justify-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 px-3 py-2 text-white transition-colors" aria-label="Ekspor data CSV">
                <Download class="h-3 w-3" />
                <span class="text-[10px] font-black">.CSV</span>
            </button>
            <button @click="emit('exportGeoJson')" class="flex-1 flex items-center justify-center gap-1.5 rounded-xl bg-[#0C447C] hover:bg-[#0a3866] px-3 py-2 text-white transition-colors" aria-label="Ekspor data GeoJSON">
                <Download class="h-3 w-3" />
                <span class="text-[10px] font-black">.GeoJSON</span>
            </button>
        </div>
        <p class="text-[9px] text-slate-400 mt-1.5 text-center">Hanya mengekspor {{ viewportStats.total }} alumni di area viewport saat ini</p>
    </div>
</template>
