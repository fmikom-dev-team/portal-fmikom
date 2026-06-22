<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import type { MapMarker } from '@/types/trace';
import {
    ChevronLeft, ChevronRight, RotateCcw, Filter,
    Search, Eye, X, Crosshair,
} from 'lucide-vue-next';
import { ref, computed, watch, onUnmounted, toRaw } from 'vue';
import TraceAdminLayout from '@/layouts/TraceAdminLayout.vue';
import { useLeafletMap } from '@/composables/useLeafletMap';
import 'leaflet.markercluster';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';
import 'leaflet.heat';

// ─── COMPOSABLES ───────────────────────────────────────────
import {
    type ViewMode, type TematicField,
    CATEGORY_CONFIG, TEMATIC_PALETTES,
    getCategory, createMarkerIcon,
} from '@/composables/trace/useMapData';
import { useMapData } from '@/composables/trace/useMapData';
import { useMapLayers } from '@/composables/trace/useMapLayers';
import { useMapViewport } from '@/composables/trace/useMapViewport';
import { useMapRadius } from '@/composables/trace/useMapRadius';

// ─── SUBCOMPONENTS ─────────────────────────────────────────
import MapToolbar from './components/map/MapToolbar.vue';
import MapRadiusPanel from './components/map/MapRadiusPanel.vue';
import MapSidebar from './components/map/MapSidebar.vue';
import MapLegend from './components/map/MapLegend.vue';

// ─── LEAFLET MAP ───────────────────────────────────────────
const { mapContainer, map, isReady, isMapLoading, isDarkMap, toggleDarkMode } = useLeafletMap();
const rawMap = () => map.value ? toRaw(map.value) as L.Map : null;

// ─── COMPOSABLE INSTANCES ──────────────────────────────────
const { allAlumni, allMarkers, filterOptions, completionMeta, isLoading, lastUpdated, fetchMapData } = useMapData();
const { clearAllLayers, applyLayers } = useMapLayers();
const { viewportStats, isViewportUpdating, updateViewportStats, setupViewportTracking, cleanup: cleanupViewport } = useMapViewport();
const { radiusMode, radiusKm, radiusCenter, radiusAlumniCount, toggleRadiusMode, clearRadius, activateRadiusListener, updateRadiusSize } = useMapRadius();

// ─── LOCAL STATE ───────────────────────────────────────────
const breadcrumbs = [
    { title: 'WebGIS Alumni', href: '#' },
    { title: 'Peta Sebaran', href: '#' },
];

const viewMode = ref<ViewMode>('cluster');
const tematicField = ref<TematicField>('status');
const showSidebar = ref(true);
const showMarkers = ref(true);

const layerVisibility = ref({
    bekerja: true,
    wirausaha: true,
    studi: true,
    belum: true,
});

const searchQuery = ref('');
const searchFocused = ref(false);

const filters = ref({
    angkatan: '',
    program_studi: '',
    sektor: '',
});

// ─── COMPUTED ──────────────────────────────────────────────
const hasActiveFilters = computed(() =>
    filters.value.angkatan !== '' || filters.value.program_studi !== '' || filters.value.sektor !== ''
);

const visibleLayerCount = computed(() =>
    Object.values(layerVisibility.value).filter(Boolean).length
);

const searchResults = computed(() => {
    if (!searchQuery.value || searchQuery.value.length < 2) return [];
    const q = searchQuery.value.toLowerCase();
    return allAlumni.value.filter(a =>
        a.nama_lengkap?.toLowerCase().includes(q) ||
        a.nim?.toLowerCase().includes(q) ||
        a.instansi?.toLowerCase().includes(q) ||
        a.nama_kota?.toLowerCase().includes(q)
    ).slice(0, 8);
});

// Chart options — viewport-aware
const sektorChartOptions = computed(() => ({
    chart: { type: 'bar', height: 180, toolbar: { show: false }, sparkline: { enabled: false } },
    plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '60%' } },
    dataLabels: { enabled: false },
    xaxis: {
        categories: viewportStats.value.sektorData.slice(0, 6).map(s => s.name.length > 15 ? s.name.slice(0, 15) + '…' : s.name),
        labels: { style: { fontSize: '9px', colors: isDarkMap.value ? '#94a3b8' : '#64748b' } },
    },
    yaxis: { labels: { style: { fontSize: '9px', colors: isDarkMap.value ? '#94a3b8' : '#64748b' } } },
    colors: ['#0C447C'],
    grid: { borderColor: isDarkMap.value ? '#334155' : '#f1f5f9' },
    tooltip: { theme: isDarkMap.value ? 'dark' : 'light' },
}));

const sektorChartSeries = computed(() => [{
    name: 'Alumni',
    data: viewportStats.value.sektorData.slice(0, 6).map(s => s.count),
}]);

const statusChartOptions = computed(() => ({
    chart: { type: 'donut', height: 180 },
    labels: ['Bekerja', 'Wirausaha', 'Studi', 'Belum Bekerja'],
    colors: ['#3b82f6', '#10b981', '#8b5cf6', '#f59e0b'],
    legend: { position: 'bottom' as const, fontSize: '10px', labels: { colors: isDarkMap.value ? '#94a3b8' : '#64748b' } },
    dataLabels: { enabled: true, style: { fontSize: '10px' } },
    stroke: { width: 0 },
    tooltip: { theme: isDarkMap.value ? 'dark' : 'light' },
}));

const statusChartSeries = computed(() => [
    viewportStats.value.bekerja,
    viewportStats.value.wirausaha,
    viewportStats.value.studi,
    viewportStats.value.belum,
]);

// Tematic legend (dynamic based on field)
const tematicLegend = computed(() => {
    if (viewMode.value !== 'tematic') return [];
    const field = tematicField.value;
    const palette = TEMATIC_PALETTES[field];
    const countMap = new Map<string, number>();

    allMarkers.filter(m => layerVisibility.value[m.category]).forEach(m => {
        let key = '';
        if (field === 'status') key = m.data.tipe_lokasi || 'Lainnya';
        else if (field === 'sektor') key = m.data.sektor_industri || 'Tidak Diketahui';
        else if (field === 'angkatan') key = m.data.angkatan || '?';
        else if (field === 'prodi') key = m.data.program_studi || 'Tidak Diketahui';
        countMap.set(key, (countMap.get(key) || 0) + 1);
    });

    return Array.from(countMap.entries())
        .sort((a, b) => b[1] - a[1])
        .map(([name, count], i) => ({
            name,
            count,
            color: palette[i % palette.length],
        }));
});

// ─── COORDINATION WRAPPERS ─────────────────────────────────
const doApplyLayers = (heatPoints?: [number, number, number][]) =>
    applyLayers(rawMap, allMarkers, layerVisibility, viewMode, showMarkers, tematicField, heatPoints);

const doClearAllLayers = () => clearAllLayers(rawMap);

const doSetupViewportTracking = () =>
    setupViewportTracking(rawMap, allMarkers, layerVisibility.value);

const doUpdateViewportStats = () =>
    updateViewportStats(rawMap, allMarkers, layerVisibility.value);

const doFetchMapData = () =>
    fetchMapData(rawMap, filters, layerVisibility, {
        clearAllLayers: doClearAllLayers,
        applyLayers: doApplyLayers,
        setupViewportTracking: doSetupViewportTracking,
        updateViewportStats: doUpdateViewportStats,
    });

// ─── ACTIONS ───────────────────────────────────────────────
const setViewMode = (mode: ViewMode) => {
    viewMode.value = mode;
    showMarkers.value = mode !== 'heat';
    if (radiusMode.value) { radiusMode.value = false; clearRadius(rawMap); }
    allMarkers.forEach(m => {
        m.marker.setIcon(createMarkerIcon(CATEGORY_CONFIG[m.category].color));
    });
    doApplyLayers();
};

const toggleMarkers = () => {
    showMarkers.value = !showMarkers.value;
    doApplyLayers();
};

const toggleLayer = (key: keyof typeof layerVisibility.value) => {
    layerVisibility.value[key] = !layerVisibility.value[key];
    doApplyLayers();
    doUpdateViewportStats();
};

const resetFilters = () => {
    filters.value = { angkatan: '', program_studi: '', sektor: '' };
    doFetchMapData();
};

const onFilterChange = () => doFetchMapData();

const highlightAlumni = (alumni: MapMarker) => {
    searchQuery.value = '';
    searchFocused.value = false;
    const entry = allMarkers.find(m => m.data.profil_alumni_id === alumni.profil_alumni_id);
    if (entry && rawMap()) {
        rawMap()!.flyTo(entry.latlng, 14, { duration: 1 });
        setTimeout(() => entry.marker.openPopup(), 1000);
    }
};

const handleRadiusToggle = () => {
    toggleRadiusMode(rawMap);
    activateRadiusListener(rawMap, allMarkers, layerVisibility.value);
};

const handleRadiusUpdateSize = (km: number) => {
    updateRadiusSize(rawMap, km, allMarkers, layerVisibility.value);
};

const handleRadiusClear = () => clearRadius(rawMap);

// ─── EXPORT ────────────────────────────────────────────────
const downloadFile = (content: string, filename: string, mime: string) => {
    const blob = new Blob([content], { type: `${mime};charset=utf-8;` });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url; a.download = filename; a.click();
    URL.revokeObjectURL(url);
};

const exportCSV = () => {
    if (!rawMap()) return;
    const bounds = rawMap()!.getBounds();
    const visible = allMarkers.filter(m => layerVisibility.value[m.category] && bounds.contains(m.latlng));

    const headers = ['Nama', 'NIM', 'Angkatan', 'Program Studi', 'Status', 'Instansi', 'Jabatan/Detail', 'Kota', 'Sektor'];
    const rows = visible.map(m => [
        m.data.nama_lengkap, m.data.nim, m.data.angkatan, m.data.program_studi,
        m.data.tipe_lokasi, m.data.instansi, m.data.detail, m.data.nama_kota, m.data.sektor_industri,
    ]);
    const bom = '\uFEFF';
    const csv = bom + [headers, ...rows].map(r => r.map(v => `"${String(v || '').replace(/"/g, '""')}"`).join(',')).join('\n');
    downloadFile(csv, `alumni-sebaran-${new Date().toISOString().slice(0, 10)}.csv`, 'text/csv');
};

const exportGeoJSON = () => {
    if (!rawMap()) return;
    const bounds = rawMap()!.getBounds();
    const visible = allMarkers.filter(m => layerVisibility.value[m.category] && bounds.contains(m.latlng));

    const geojson = {
        type: 'FeatureCollection',
        features: visible.map(m => ({
            type: 'Feature',
            geometry: { type: 'Point', coordinates: [m.latlng.lng, m.latlng.lat] },
            properties: {
                nama: m.data.nama_lengkap, nim: m.data.nim, angkatan: m.data.angkatan,
                prodi: m.data.program_studi, status: m.data.tipe_lokasi,
                instansi: m.data.instansi, kota: m.data.nama_kota, sektor: m.data.sektor_industri,
            },
        })),
    };
    downloadFile(JSON.stringify(geojson, null, 2), `alumni-sebaran-${new Date().toISOString().slice(0, 10)}.geojson`, 'application/json');
};

// ─── WATCHERS ──────────────────────────────────────────────
watch(isReady, (ready) => { if (ready) doFetchMapData(); });
watch(tematicField, () => { if (viewMode.value === 'tematic') doApplyLayers(); });

onUnmounted(() => {
    cleanupViewport();
    clearRadius(rawMap);
});
</script>

<template>
    <Head title="WebGIS Sebaran Alumni" />

    <TraceAdminLayout :breadcrumbs="breadcrumbs">
        <template #fullscreen>
        <div class="relative flex h-[calc(100vh-64px)] overflow-hidden bg-slate-100 dark:bg-slate-950">

            <!-- ==================== FULLSCREEN MAP ==================== -->
            <div ref="mapContainer" class="absolute inset-0"></div>

            <!-- Map Init Loading -->
            <div v-if="isMapLoading" class="absolute inset-0 z-[2000] flex items-center justify-center bg-slate-950">
                <div class="flex flex-col items-center gap-4">
                    <div class="h-10 w-10 animate-spin rounded-full border-3 border-blue-500 border-t-transparent"></div>
                    <span class="text-sm font-bold text-slate-400">Memuat peta...</span>
                </div>
            </div>

            <!-- Data Loading Overlay -->
            <div v-else-if="isLoading" class="absolute inset-0 z-[2000] flex items-center justify-center bg-white/40 dark:bg-slate-900/40 backdrop-blur-sm">
                <div class="flex items-center gap-3 rounded-2xl bg-white/90 dark:bg-slate-900/90 px-6 py-4 shadow-2xl">
                    <div class="h-5 w-5 animate-spin rounded-full border-2 border-blue-600 border-t-transparent"></div>
                    <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Memuat data alumni...</span>
                </div>
            </div>

            <!-- ==================== LEFT PANEL ==================== -->
            <div class="absolute left-4 top-4 z-[1000] w-72 flex flex-col gap-2 max-h-[calc(100vh-130px)] overflow-y-auto scrollbar-thin pb-4">

                <!-- Toolbar (Header + Mode Toggle) -->
                <MapToolbar
                    :viewMode="viewMode"
                    :isDarkMap="isDarkMap"
                    :showMarkers="showMarkers"
                    :tematicField="tematicField"
                    @update:viewMode="setViewMode"
                    @update:tematicField="(f) => tematicField = f as TematicField"
                    @toggleDarkMode="toggleDarkMode"
                    @toggleMarkers="toggleMarkers"
                />

                <!-- Search -->
                <div class="rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-xl overflow-hidden">
                    <div class="flex items-center gap-2 px-3 py-2.5">
                        <Search class="h-3.5 w-3.5 text-slate-400 flex-shrink-0" />
                        <input
                            v-model="searchQuery"
                            @focus="searchFocused = true"
                            @blur="setTimeout(() => searchFocused = false, 200)"
                            placeholder="Cari nama, NIM, perusahaan..."
                            aria-label="Cari alumni di peta"
                            class="w-full bg-transparent text-[11px] font-medium text-slate-700 dark:text-slate-300 outline-none placeholder-slate-400"
                        />
                        <button v-if="searchQuery" @click="searchQuery = ''" class="text-slate-400 hover:text-slate-600" aria-label="Hapus pencarian">
                            <X class="h-3 w-3" />
                        </button>
                    </div>
                    <!-- Search Results Dropdown -->
                    <div v-if="searchFocused && searchResults.length > 0" class="border-t border-slate-100 dark:border-slate-800 max-h-60 overflow-y-auto">
                        <button v-for="r in searchResults" :key="r.profil_alumni_id"
                            @mousedown.prevent="highlightAlumni(r)"
                            class="w-full px-3 py-2 flex items-center gap-2 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-left"
                        >
                            <div class="h-6 w-6 rounded-full flex-shrink-0 flex items-center justify-center text-[9px]"
                                :style="{ background: CATEGORY_CONFIG[getCategory(r.tipe_lokasi)].color + '22', color: CATEGORY_CONFIG[getCategory(r.tipe_lokasi)].color }">
                                {{ CATEGORY_CONFIG[getCategory(r.tipe_lokasi)].icon }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[11px] font-bold text-slate-700 dark:text-slate-300 truncate">{{ r.nama_lengkap }}</p>
                                <p class="text-[9px] text-slate-400 truncate">{{ r.nim }} · {{ r.angkatan }} · {{ r.program_studi }}</p>
                            </div>
                            <Crosshair class="h-3 w-3 text-slate-300 flex-shrink-0" />
                        </button>
                    </div>
                </div>

                <!-- Radius Search -->
                <MapRadiusPanel
                    :radiusMode="radiusMode"
                    :radiusKm="radiusKm"
                    :radiusCenter="radiusCenter"
                    :radiusAlumniCount="radiusAlumniCount"
                    @toggle="handleRadiusToggle"
                    @clear="handleRadiusClear"
                    @updateSize="handleRadiusUpdateSize"
                />

                <!-- Layer Checkboxes -->
                <div class="rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-xl px-4 py-3">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-1.5">
                            <Eye class="h-3.5 w-3.5 text-slate-400" />
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Layer Data</span>
                        </div>
                        <span class="text-[9px] font-bold text-slate-400">{{ visibleLayerCount }}/4</span>
                    </div>
                    <div class="space-y-1">
                        <label v-for="(cfg, key) in CATEGORY_CONFIG" :key="key"
                            @click="toggleLayer(key as keyof typeof layerVisibility)"
                            class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg cursor-pointer transition-colors"
                            :class="layerVisibility[key as keyof typeof layerVisibility] ? 'hover:bg-slate-50 dark:hover:bg-slate-800' : 'opacity-40 hover:opacity-60'"
                        >
                            <div class="h-4 w-4 rounded border-2 flex items-center justify-center transition-all"
                                :style="layerVisibility[key as keyof typeof layerVisibility] ? { background: cfg.color, borderColor: cfg.color } : {}"
                                :class="!layerVisibility[key as keyof typeof layerVisibility] ? 'border-slate-300 dark:border-slate-600' : ''"
                            >
                                <svg v-if="layerVisibility[key as keyof typeof layerVisibility]" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300 flex-1">{{ cfg.label }}</span>
                            <span class="text-[10px] font-black" :style="{ color: cfg.color }">
                                {{ key === 'bekerja' ? viewportStats.bekerja : key === 'wirausaha' ? viewportStats.wirausaha : key === 'studi' ? viewportStats.studi : viewportStats.belum }}
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Filters -->
                <div class="rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-xl">
                    <div class="px-4 pt-3 pb-1 flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <Filter class="h-3.5 w-3.5 text-slate-400" />
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Filter</span>
                        </div>
                        <button v-if="hasActiveFilters" @click="resetFilters" class="flex items-center gap-1 text-[9px] font-bold text-red-500 hover:text-red-600" aria-label="Reset semua filter">
                            <RotateCcw class="h-2.5 w-2.5" /> Reset
                        </button>
                    </div>
                    <div class="p-3 pt-1 space-y-2">
                        <div>
                            <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-0.5 block">Angkatan</label>
                            <select v-model="filters.angkatan" @change="onFilterChange" aria-label="Filter angkatan" class="w-full h-8 rounded-lg bg-slate-50 dark:bg-slate-800 px-2 text-[11px] font-bold text-slate-700 dark:text-slate-300 outline-none border border-slate-200 dark:border-slate-700 focus:border-blue-500">
                                <option value="">Semua</option>
                                <option v-for="opt in filterOptions.angkatan" :key="opt" :value="opt">{{ opt }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-0.5 block">Program Studi</label>
                            <select v-model="filters.program_studi" @change="onFilterChange" aria-label="Filter program studi" class="w-full h-8 rounded-lg bg-slate-50 dark:bg-slate-800 px-2 text-[11px] font-bold text-slate-700 dark:text-slate-300 outline-none border border-slate-200 dark:border-slate-700 focus:border-blue-500">
                                <option value="">Semua</option>
                                <option v-for="opt in filterOptions.prodi" :key="opt" :value="opt">{{ opt }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-0.5 block">Sektor Industri</label>
                            <select v-model="filters.sektor" @change="onFilterChange" aria-label="Filter sektor industri" class="w-full h-8 rounded-lg bg-slate-50 dark:bg-slate-800 px-2 text-[11px] font-bold text-slate-700 dark:text-slate-300 outline-none border border-slate-200 dark:border-slate-700 focus:border-blue-500">
                                <option value="">Semua Sektor</option>
                                <option v-for="opt in filterOptions.sektor" :key="opt" :value="opt">{{ opt }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==================== RIGHT SIDEBAR ==================== -->
            <transition name="slide-right">
                <div v-show="showSidebar" class="absolute right-4 top-4 bottom-20 z-[1000] w-80 flex flex-col gap-2 overflow-y-auto scrollbar-thin">
                    <MapSidebar
                        :viewportStats="viewportStats"
                        :isViewportUpdating="isViewportUpdating"
                        :completionMeta="completionMeta"
                        :statusChartOptions="statusChartOptions"
                        :statusChartSeries="statusChartSeries"
                        :sektorChartOptions="sektorChartOptions"
                        :sektorChartSeries="sektorChartSeries"
                        @exportCsv="exportCSV"
                        @exportGeoJson="exportGeoJSON"
                    />
                </div>
            </transition>

            <!-- Sidebar Toggle -->
            <button @click="showSidebar = !showSidebar"
                class="absolute right-4 top-1/2 -translate-y-1/2 z-[1001] flex h-8 w-5 items-center justify-center rounded-l-lg bg-white/90 dark:bg-slate-900/90 shadow-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                :class="showSidebar ? 'right-[340px]' : 'right-4'"
                aria-label="Buka/tutup sidebar"
            >
                <ChevronRight v-if="!showSidebar" class="h-3.5 w-3.5 text-slate-500" />
                <ChevronLeft v-else class="h-3.5 w-3.5 text-slate-500" />
            </button>

            <!-- ==================== BOTTOM LEGEND BAR ==================== -->
            <MapLegend
                :viewMode="viewMode"
                :hasActiveFilters="hasActiveFilters"
                :tematicLegend="tematicLegend"
                :lastUpdated="lastUpdated"
            />

        </div>
        </template>
    </TraceAdminLayout>
</template>

<style>
/* ─── LEAFLET BASE ─────────────────────────────── */
.leaflet-container {
    background: #f1f5f9 !important;
    width: 100% !important;
    height: 100% !important;
    font-family: system-ui, -apple-system, sans-serif;
}
:root.dark .leaflet-container {
    background: #0f172a !important;
}

/* ─── CUSTOM MARKER ────────────────────────────── */
.custom-marker-icon {
    background: transparent !important;
    border: none !important;
}

/* ─── CLUSTER ICON ─────────────────────────────── */
.custom-cluster {
    background: transparent !important;
    border: none !important;
}
.cluster-icon {
    background: rgba(12, 68, 124, 0.85);
    border: 3px solid white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 11px;
    font-weight: 900;
    box-shadow: 0 4px 12px rgba(12, 68, 124, 0.4);
}

/* ─── POPUP ────────────────────────────────────── */
.custom-popup .leaflet-popup-content-wrapper {
    border-radius: 1.25rem;
    padding: 0;
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    border: 1px solid rgba(0,0,0,0.06);
    overflow: hidden;
}
.custom-popup .leaflet-popup-content {
    margin: 0;
    padding: 16px;
}
.custom-popup .leaflet-popup-tip-container {
    display: none;
}

/* ─── PROVINCE TOOLTIP ─────────────────────────── */
.province-tooltip {
    background: rgba(255,255,255,0.96) !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 12px !important;
    padding: 8px 14px !important;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important;
}
.province-tooltip::before {
    display: none !important;
}

/* ─── SCROLLBAR ────────────────────────────────── */
.scrollbar-thin::-webkit-scrollbar {
    width: 4px;
}
.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.3);
    border-radius: 999px;
}

/* ─── SIDEBAR TRANSITION ──────────────────────── */
.slide-right-enter-active,
.slide-right-leave-active {
    transition: transform 0.3s ease, opacity 0.3s ease;
}
.slide-right-enter-from,
.slide-right-leave-to {
    transform: translateX(20px);
    opacity: 0;
}
</style>
