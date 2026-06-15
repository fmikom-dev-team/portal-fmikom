<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import L from 'leaflet';
import { MapPin, Users, Building2, Layers, GraduationCap, Briefcase, Home, ChevronUp, ChevronDown, RotateCcw, Filter, BookOpen } from 'lucide-vue-next';
import { onMounted, ref, onUnmounted, computed } from 'vue';
import TraceAdminLayout from '@/layouts/TraceAdminLayout.vue';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';
import 'leaflet.heat';

const breadcrumbs = [
    { title: 'WebGIS Alumni', href: '#' },
    { title: 'Peta Sebaran', href: '#' },
];

const mapContainer = ref<HTMLElement | null>(null);
let map: L.Map | null = null;
let markerCluster: any = null;
let heatLayer: any = null;

const isLoading = ref(false);
const showStats = ref(true);

const mapStats = ref({
    totalAlumni: 0,
    totalCities: 0,
    totalCompanies: 0,
    totalBekerja: 0,
    totalStudi: 0,
    totalBelumBekerja: 0,
});

const statsColumns = ref([
    { title: 'Top Wilayah', icon: MapPin, color: 'blue', data: [] as any[] },
    { title: 'Top Sektor', icon: Building2, color: 'emerald', data: [] as any[] },
    { title: 'Top Instansi', icon: Briefcase, color: 'purple', data: [] as any[] }
]);

const completionMeta = ref({
    total_alumni: 0,
    mapped_count: 0,
    completion_rate: 0,
    is_filter_active: false,
    filtered: { total: 0, mapped: 0, rate: 0 }
});
let cachedData: any[] = [];

const viewMode = ref('cluster'); // 'cluster' or 'heat'

const filters = ref({
    status_filter: 'semua',
    angkatan: '',
    program_studi: '',
    sektor: '',
});

const statusOptions = [
    { value: 'semua', label: 'Semua Status', icon: '🗺️' },
    { value: 'bekerja', label: 'Bekerja / Wirausaha', icon: '💼' },
    { value: 'lainnya', label: 'Belum Bekerja / Studi', icon: '🎓' },
];

const currentStatusLabel = computed(() =>
    statusOptions.find(o => o.value === filters.value.status_filter)?.label || 'Semua Status'
);

const angkatanOptions = ['2019', '2020', '2021', '2022', '2023', '2024'];
const prodiOptions = ['Informatika', 'Sistem Informasi', 'Matematika'];
const sektorOptions = [
    'Teknologi Informasi',
    'Pendidikan',
    'Keuangan & Perbankan',
    'Kesehatan',
    'Manufaktur',
    'Pemerintahan',
    'Perdagangan & E-Commerce',
    'Telekomunikasi',
    'Konstruksi & Properti',
    'Transportasi & Logistik',
    'Media & Kreatif',
    'Pertanian & Pangan',
    'Energi & Pertambangan',
    'Pariwisata & Hospitality',
    'Lainnya',
];

const allLegend = [
    { color: 'bg-blue-600', hex: '#2563eb', label: 'Bekerja / Wirausaha', desc: 'Titik lokasi perusahaan/tempat usaha', filter: 'bekerja' },
    { color: 'bg-purple-600', hex: '#9333ea', label: 'Lanjut Studi', desc: 'Titik lokasi universitas', filter: 'lainnya' },
    { color: 'bg-emerald-600', hex: '#059669', label: 'Belum Bekerja', desc: 'Titik lokasi rumah/domisili', filter: 'lainnya' },
];

const legend = computed(() => {
    if (filters.value.status_filter === 'bekerja') {
return allLegend.filter(l => l.filter === 'bekerja');
}

    if (filters.value.status_filter === 'lainnya') {
return allLegend.filter(l => l.filter === 'lainnya');
}

    return allLegend;
});

const initMap = () => {
    if (!mapContainer.value) {
return;
}

    map = L.map(mapContainer.value, {
        center: [-2.5, 118],
        zoom: 5,
        zoomControl: false,
    });

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; CARTO',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(map);

    L.control.zoom({ position: 'bottomright' }).addTo(map);

    markerCluster = (L as any).markerClusterGroup({
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true,
        iconCreateFunction: (cluster: any) => {
            const count = cluster.getChildCount();

            return L.divIcon({
                html: `<div class="cluster-icon">${count}</div>`,
                className: 'custom-cluster',
                iconSize: [36, 36],
            });
        }
    });

    fetchMapData();
};

const fetchMapData = async () => {
    isLoading.value = true;

    try {
        const response = await axios.get('/trace/admin/map/data', { params: filters.value });
        const alumniData = response.data.data;
        cachedData = alumniData;

        if (response.data.meta) {
completionMeta.value = response.data.meta;
}

        // Reset
        markerCluster.clearLayers();

        if (heatLayer) {
map?.removeLayer(heatLayer);
}

        let bekerja = 0, studi = 0, belumBekerja = 0;

        const cityMap = new Map();
        const sectorMap = new Map();
        const companyMap = new Map();
        const heatPoints: any[] = [];
        const statusMap = new Map();
        const univMap = new Map();
        const studyProdiMap = new Map();

        alumniData.forEach((alumni: any) => {
            if (alumni.latitude && alumni.longitude) {
                const jitterLat = (Math.random() - 0.5) * 0.015;
                const jitterLng = (Math.random() - 0.5) * 0.015;
                const finalLat = parseFloat(alumni.latitude) + jitterLat;
                const finalLng = parseFloat(alumni.longitude) + jitterLng;

                let hexColor = '#2563eb';
                let badgeColor = 'text-blue-600 bg-blue-50';

                if (alumni.tipe_lokasi === 'Lanjut Studi') {
                    hexColor = '#9333ea';
                    badgeColor = 'text-purple-600 bg-purple-50';
                    studi++;
                } else if (alumni.tipe_lokasi === 'Belum Bekerja') {
                    hexColor = '#059669';
                    badgeColor = 'text-emerald-600 bg-emerald-50';
                    belumBekerja++;
                } else {
                    bekerja++;
                }

                const marker = L.marker([finalLat, finalLng], {
                    icon: L.divIcon({
                        className: 'custom-div-icon',
                        html: `<div style="background:${hexColor}" class="map-pin flex items-center justify-center w-7 h-7 rounded-full border-2 border-white shadow-lg"><div class="w-2 h-2 rounded-full bg-white"></div></div>`,
                        iconSize: [28, 28],
                        iconAnchor: [14, 14]
                    })
                });

                const isBekerja = alumni.tipe_lokasi === 'Bekerja';
                const isStudi = alumni.tipe_lokasi === 'Lanjut Studi';
                const isBelum = alumni.tipe_lokasi === 'Belum Bekerja';

                const instansiLabel = isBekerja ? 'Perusahaan' : isStudi ? 'Universitas' : 'Alamat Domisili';
                const detailLabel = isBekerja ? 'Jabatan' : isStudi ? 'Program Studi' : 'Status';
                const instansiValue = alumni.instansi || (isBelum ? '(Belum diisi)' : '-');

                marker.bindPopup(`
                    <div style="min-width:220px">
                        <div class="popup-badge ${badgeColor}">${alumni.tipe_lokasi}</div>
                        <h4 class="popup-name">${alumni.nama_lengkap || 'Alumni'}</h4>
                        <div class="popup-info-row">
                            <span class="popup-label">Prodi</span>
                            <span class="popup-value">${alumni.program_studi || '-'}</span>
                        </div>
                        <div class="popup-info-row">
                            <span class="popup-label">Angkatan</span>
                            <span class="popup-value">${alumni.angkatan || '-'}</span>
                        </div>
                        <div class="popup-divider"></div>
                        <div class="popup-info-row">
                            <span class="popup-label">${instansiLabel}</span>
                            <span class="popup-value">${instansiValue}</span>
                        </div>
                        <div class="popup-info-row">
                            <span class="popup-label">${detailLabel}</span>
                            <span class="popup-value">${alumni.detail || '-'}</span>
                        </div>
                        <div class="popup-divider"></div>
                        <a href="/admin/alumni/${alumni.profil_alumni_id}" style="display:block;text-align:center;background:#2563eb;color:white;border-radius:8px;padding:6px 12px;font-size:11px;font-weight:700;text-decoration:none;margin-top:4px;">👤 Lihat Profil Lengkap</a>
                    </div>
                `, { className: 'custom-popup', maxWidth: 270 });

                markerCluster.addLayer(marker);
                heatPoints.push([finalLat, finalLng, 0.5]);

                if (alumni.tipe_lokasi) {
                    statusMap.set(alumni.tipe_lokasi, (statusMap.get(alumni.tipe_lokasi) || 0) + 1);
                }

                if (isBekerja) {
                    if (alumni.nama_kota) {
cityMap.set(alumni.nama_kota, (cityMap.get(alumni.nama_kota) || 0) + 1);
}

                    if (alumni.sektor_industri) {
sectorMap.set(alumni.sektor_industri, (sectorMap.get(alumni.sektor_industri) || 0) + 1);
}

                    if (alumni.instansi) {
companyMap.set(alumni.instansi, (companyMap.get(alumni.instansi) || 0) + 1);
}
                } else if (isStudi) {
                    if (alumni.instansi) {
univMap.set(alumni.instansi, (univMap.get(alumni.instansi) || 0) + 1);
}

                    if (alumni.detail) {
studyProdiMap.set(alumni.detail, (studyProdiMap.get(alumni.detail) || 0) + 1);
}
                }
            }
        });

        mapStats.value.totalAlumni = alumniData.length;
        mapStats.value.totalCities = cityMap.size;
        mapStats.value.totalCompanies = companyMap.size;
        mapStats.value.totalBekerja = bekerja;
        mapStats.value.totalStudi = studi;
        mapStats.value.totalBelumBekerja = belumBekerja;
        
        const sortMap = (m: Map<any, any>) => Array.from(m.entries()).map(([name, count]) => ({ name, count })).sort((a, b) => b.count - a.count).slice(0, 5);
        
        if (filters.value.status_filter === 'bekerja') {
            statsColumns.value = [
                { title: 'Top Wilayah', icon: MapPin, color: 'blue', data: sortMap(cityMap) },
                { title: 'Top Sektor', icon: Building2, color: 'emerald', data: sortMap(sectorMap) },
                { title: 'Top Instansi', icon: Briefcase, color: 'purple', data: sortMap(companyMap) }
            ];
        } else if (filters.value.status_filter === 'lainnya') {
            statsColumns.value = [
                { title: 'Sebaran Status', icon: Users, color: 'blue', data: sortMap(statusMap) },
                { title: 'Top Universitas', icon: GraduationCap, color: 'emerald', data: sortMap(univMap) },
                { title: 'Top Studi Lanjut', icon: BookOpen, color: 'purple', data: sortMap(studyProdiMap) }
            ];
        } else {
            const mixedInstansi = new Map();
            companyMap.forEach((v, k) => mixedInstansi.set(k, (mixedInstansi.get(k) || 0) + v));
            univMap.forEach((v, k) => mixedInstansi.set(k, (mixedInstansi.get(k) || 0) + v));
            
            statsColumns.value = [
                { title: 'Sebaran Status', icon: Users, color: 'blue', data: sortMap(statusMap) },
                { title: 'Top Wilayah (Bekerja)', icon: MapPin, color: 'emerald', data: sortMap(cityMap) },
                { title: 'Top Instansi/Kampus', icon: Building2, color: 'purple', data: sortMap(mixedInstansi) }
            ];
        }

        lastUpdated.value = formatTime(new Date());

        if (map) {
            if (viewMode.value === 'cluster') {
                map.addLayer(markerCluster);
            } else {
                heatLayer = (L as any).heatLayer(heatPoints, { radius: 25, blur: 15, maxZoom: 10 }).addTo(map);
            }

            if (alumniData.length > 0) {
                const group = (L as any).featureGroup(markerCluster.getLayers());

                try {
 map.fitBounds(group.getBounds().pad(0.2)); 
} catch (e) {}
            }
        }
    } catch (error) {
        console.error('Error fetching map data:', error);
    } finally {
        isLoading.value = false;
    }
};

const toggleMode = (mode: string) => {
    viewMode.value = mode;
    fetchMapData();
};

const resetFilters = () => {
    filters.value = { status_filter: 'semua', angkatan: '', program_studi: '', sektor: '' };
    fetchMapData();
};

const hasActiveFilters = computed(() =>
    filters.value.status_filter !== 'semua' ||
    filters.value.angkatan !== '' ||
    filters.value.program_studi !== '' ||
    filters.value.sektor !== ''
);

const lastUpdated = ref<string>('');

const formatTime = (d: Date) =>
    d.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) +
    ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

const exportCSV = () => {
    if (!cachedData.length) {
return;
}

    const headers = ['Nama', 'NIM', 'Angkatan', 'Program Studi', 'Status', 'Instansi/Kampus/Domisili', 'Detail', 'Kota', 'Sektor', 'Latitude', 'Longitude'];
    const rows = cachedData.map((a: any) => [
        a.nama_lengkap || '',
        a.nim || '',
        a.angkatan || '',
        a.program_studi || '',
        a.tipe_lokasi || '',
        a.instansi || '',
        a.detail || '',
        a.nama_kota || '',
        a.sektor_industri || '',
        a.latitude || '',
        a.longitude || '',
    ]);
    const csvContent = [headers, ...rows].map(r => r.map((v: any) => `"${String(v).replace(/"/g, '""')}"`).join(',')).join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `sebaran-alumni-${filters.value.status_filter}-${new Date().toISOString().slice(0,10)}.csv`;
    a.click();
    URL.revokeObjectURL(url);
};

onMounted(() => initMap());
onUnmounted(() => map?.remove());
</script>

<template>
    <Head title="WebGIS Sebaran Alumni" />

    <TraceAdminLayout :breadcrumbs="breadcrumbs">
        <div class="relative flex h-[calc(100vh-64px)] overflow-hidden bg-slate-100 dark:bg-slate-950">

            <!-- ==================== FULLSCREEN MAP ==================== -->
            <div ref="mapContainer" class="absolute inset-0 z-0"></div>

            <!-- Loading Overlay -->
            <div v-if="isLoading" class="absolute inset-0 z-[2000] flex items-center justify-center bg-white/50 dark:bg-slate-900/50 backdrop-blur-sm">
                <div class="flex items-center gap-3 rounded-2xl bg-white/90 dark:bg-slate-900/90 px-6 py-4 shadow-2xl">
                    <div class="h-5 w-5 animate-spin rounded-full border-2 border-blue-600 border-t-transparent"></div>
                    <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Memuat data peta...</span>
                </div>
            </div>

            <!-- ==================== TOP LEFT: FILTER PANEL ==================== -->
            <div class="absolute left-4 top-4 z-[1000] w-72">
                <!-- Header bar -->
                <div class="flex items-center gap-2 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-xl px-4 py-3 mb-2">
                    <div class="flex items-center gap-2 flex-1">
                        <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-blue-600 shadow-md shadow-blue-500/30">
                            <Layers class="h-4 w-4 text-white" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black tracking-widest text-slate-400 uppercase">WebGIS</p>
                            <p class="text-sm font-black text-slate-800 dark:text-white leading-none">Peta Sebaran Alumni</p>
                        </div>
                    </div>
                    <!-- View Mode Toggle -->
                    <div class="flex rounded-xl bg-slate-100 dark:bg-slate-800 p-1 gap-0.5">
                        <button
                            @click="toggleMode('cluster')"
                            :class="viewMode === 'cluster' ? 'bg-white dark:bg-slate-700 text-blue-600 shadow-sm' : 'text-slate-400 hover:text-slate-600'"
                            class="px-2.5 py-1 rounded-lg text-[9px] font-black transition-all"
                        >TITIK</button>
                        <button
                            @click="toggleMode('heat')"
                            :class="viewMode === 'heat' ? 'bg-white dark:bg-slate-700 text-orange-600 shadow-sm' : 'text-slate-400 hover:text-slate-600'"
                            class="px-2.5 py-1 rounded-lg text-[9px] font-black transition-all"
                        >HEAT</button>
                    </div>
                </div>

                <!-- Filter Card -->
                <div class="rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-xl overflow-hidden">
                    <div class="px-4 pt-4 pb-1 flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <Filter class="h-3.5 w-3.5 text-slate-400" />
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Filter Data</span>
                        </div>
                        <button
                            v-if="hasActiveFilters"
                            @click="resetFilters"
                            class="flex items-center gap-1 text-[9px] font-bold text-red-400 hover:text-red-600 transition-colors"
                        >
                            <RotateCcw class="h-3 w-3" /> Reset
                        </button>
                    </div>
                    <div class="px-4 pb-4 pt-2 space-y-3">
                        <!-- Status Filter as Pill Tabs -->
                        <div>
                            <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 block">Status Alumni</label>
                            <div class="flex gap-1 flex-wrap">
                                <button
                                    v-for="opt in statusOptions"
                                    :key="opt.value"
                                    @click="filters.status_filter = opt.value; fetchMapData()"
                                    :class="filters.status_filter === opt.value
                                        ? 'bg-blue-600 text-white shadow-md shadow-blue-500/30'
                                        : 'bg-slate-100 dark:bg-slate-800 text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700'"
                                    class="px-2.5 py-1.5 rounded-xl text-[10px] font-bold transition-all"
                                >{{ opt.icon }} {{ opt.label }}</button>
                            </div>
                        </div>

                        <!-- Other Filters -->
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1 block">Angkatan</label>
                                <select
                                    v-model="filters.angkatan"
                                    @change="fetchMapData"
                                    class="w-full h-9 rounded-xl bg-slate-50 dark:bg-slate-800 px-2.5 text-[11px] font-bold text-slate-700 dark:text-slate-300 outline-none border border-slate-200 dark:border-slate-700 focus:border-blue-500"
                                >
                                    <option value="">Semua</option>
                                    <option v-for="opt in angkatanOptions" :key="opt" :value="opt">{{ opt }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1 block">Prodi</label>
                                <select
                                    v-model="filters.program_studi"
                                    @change="fetchMapData"
                                    class="w-full h-9 rounded-xl bg-slate-50 dark:bg-slate-800 px-2.5 text-[11px] font-bold text-slate-700 dark:text-slate-300 outline-none border border-slate-200 dark:border-slate-700 focus:border-blue-500"
                                >
                                    <option value="">Semua</option>
                                    <option v-for="opt in prodiOptions" :key="opt" :value="opt">{{ opt }}</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1 block">Sektor Industri</label>
                            <select
                                v-model="filters.sektor"
                                @change="fetchMapData"
                                class="w-full h-9 rounded-xl bg-slate-50 dark:bg-slate-800 px-2.5 text-[11px] font-bold text-slate-700 dark:text-slate-300 outline-none border border-slate-200 dark:border-slate-700 focus:border-blue-500"
                            >
                                <option value="">Semua Sektor</option>
                                <option v-for="opt in sektorOptions" :key="opt" :value="opt">{{ opt }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==================== TOP RIGHT: STAT PILLS ==================== -->
            <div class="absolute right-4 top-4 z-[1000] flex flex-col gap-2 items-end">
                <div class="flex gap-2">
                    <div class="flex items-center gap-2.5 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-xl px-4 py-3">
                        <div class="h-8 w-8 flex items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/30">
                            <Users class="h-4 w-4 text-blue-600" />
                        </div>
                        <div>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">
                                {{ completionMeta.is_filter_active ? 'Alumni Terfilter' : 'Total Alumni' }}
                            </p>
                            <p class="text-xl font-black text-slate-800 dark:text-white leading-none">
                                {{ completionMeta.is_filter_active ? completionMeta.filtered?.total : completionMeta.total_alumni }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-xl px-4 py-3">
                        <div class="h-8 w-8 flex items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/30">
                            <MapPin class="h-4 w-4 text-emerald-600" />
                        </div>
                        <div>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Kota</p>
                            <p class="text-xl font-black text-slate-800 dark:text-white leading-none">{{ mapStats.totalCities }}</p>
                        </div>
                    </div>
                </div>
                <!-- Distribution pills -->
                <div class="flex gap-2" v-if="mapStats.totalAlumni > 0">
                    <div class="flex items-center gap-1.5 rounded-xl bg-blue-600/90 backdrop-blur-sm px-3 py-1.5 shadow-lg shadow-blue-600/20">
                        <Briefcase class="h-3 w-3 text-white" />
                        <span class="text-[10px] font-black text-white">{{ mapStats.totalBekerja }} Bekerja</span>
                    </div>
                    <div class="flex items-center gap-1.5 rounded-xl bg-purple-600/90 backdrop-blur-sm px-3 py-1.5 shadow-lg shadow-purple-600/20">
                        <GraduationCap class="h-3 w-3 text-white" />
                        <span class="text-[10px] font-black text-white">{{ mapStats.totalStudi }} Studi</span>
                    </div>
                    <div class="flex items-center gap-1.5 rounded-xl bg-emerald-600/90 backdrop-blur-sm px-3 py-1.5 shadow-lg shadow-emerald-600/20">
                        <Home class="h-3 w-3 text-white" />
                        <span class="text-[10px] font-black text-white">{{ mapStats.totalBelumBekerja }} Belum</span>
                    </div>
                </div>

                <!-- Completion Rate Card -->
                <div v-if="completionMeta.total_alumni > 0" class="w-full rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-xl px-4 py-3">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider">
                            {{ completionMeta.is_filter_active ? 'Keterisian (Filter)' : 'Keterisian Peta' }}
                        </span>
                        <span 
                            class="text-[11px] font-black" 
                            :class="
                                (completionMeta.is_filter_active ? completionMeta.filtered?.rate : completionMeta.completion_rate) >= 70 
                                    ? 'text-emerald-600' 
                                    : (completionMeta.is_filter_active ? completionMeta.filtered?.rate : completionMeta.completion_rate) >= 40 
                                        ? 'text-amber-500' 
                                        : 'text-red-500'
                            "
                        >
                            {{ completionMeta.is_filter_active ? completionMeta.filtered?.rate : completionMeta.completion_rate }}%
                        </span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                        <div
                            class="h-full rounded-full transition-all duration-700"
                            :class="
                                (completionMeta.is_filter_active ? completionMeta.filtered?.rate : completionMeta.completion_rate) >= 70 
                                    ? 'bg-emerald-500' 
                                    : (completionMeta.is_filter_active ? completionMeta.filtered?.rate : completionMeta.completion_rate) >= 40 
                                        ? 'bg-amber-400' 
                                        : 'bg-red-400'
                            "
                            :style="`width: ${completionMeta.is_filter_active ? completionMeta.filtered?.rate : completionMeta.completion_rate}%`"
                        ></div>
                    </div>
                    <div class="flex flex-col gap-0.5 mt-1.5">
                        <p class="text-[9px] font-bold text-slate-500 leading-normal">
                            {{ completionMeta.is_filter_active ? completionMeta.filtered?.mapped : completionMeta.mapped_count }} dari {{ completionMeta.is_filter_active ? completionMeta.filtered?.total : completionMeta.total_alumni }} alumni terpetakan
                        </p>
                        <p v-if="completionMeta.is_filter_active" class="text-[8px] text-slate-400 dark:text-slate-500 leading-normal border-t border-slate-100 dark:border-slate-800/60 pt-1 mt-1">
                            🌐 Akumulasi Global: {{ completionMeta.mapped_count }} dari {{ completionMeta.total_alumni }} terpetakan ({{ completionMeta.completion_rate }}%)
                        </p>
                    </div>
                </div>

                <!-- Export CSV Button -->
                <button
                    @click="exportCSV"
                    :disabled="!cachedData.length"
                    class="flex items-center gap-2 rounded-xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-xl px-4 py-2.5 text-[11px] font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors disabled:opacity-40 disabled:cursor-not-allowed w-full justify-center border border-slate-200 dark:border-slate-700"
                >
                    📥 Ekspor Rekapitulasi CSV
                </button>
            </div>

            <!-- ==================== BOTTOM: LEGEND + STATS PANEL ==================== -->
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-[1000] w-[calc(100%-2rem)] max-w-5xl">
                <div class="rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-2xl overflow-hidden">

                    <!-- Toggle bar: Legenda + info klik -->
                    <button @click="showStats = !showStats" class="w-full flex items-center justify-between px-5 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-4 flex-wrap">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Informasi & Statistik</span>

                            <!-- Warna marker -->
                            <div class="flex items-center gap-3">
                                <div v-for="l in legend" :key="l.label" class="flex items-center gap-1.5">
                                    <div :class="l.color" class="h-3 w-3 rounded-full shadow-sm flex-shrink-0"></div>
                                    <span class="text-[10px] font-bold text-slate-500 whitespace-nowrap">{{ l.label }}</span>
                                </div>
                            </div>

                            <!-- Cluster icon explanation -->
                            <div class="flex items-center gap-1.5 pl-3 border-l border-slate-100 dark:border-slate-800">
                                <div class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600/85 border-2 border-white shadow text-white text-[9px] font-black">N</div>
                                <span class="text-[10px] text-slate-400 font-medium">= N alumni berdekatan (kluster)</span>
                            </div>

                            <!-- Interaction hints -->
                            <div class="flex items-center gap-3 pl-3 border-l border-slate-100 dark:border-slate-800">
                                <span class="text-[10px] text-slate-400">🖱️ Klik titik → detail alumni</span>
                                <span class="text-[10px] text-slate-400">🔍 Scroll → zoom peta</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 flex-shrink-0 ml-2">
                            <!-- Timestamp -->
                            <span v-if="lastUpdated" class="text-[9px] text-slate-300 dark:text-slate-600 font-medium whitespace-nowrap">
                                🕒 {{ lastUpdated }}
                            </span>
                            <ChevronUp v-if="showStats" class="h-4 w-4 text-slate-400" />
                            <ChevronDown v-else class="h-4 w-4 text-slate-400" />
                        </div>
                    </button>

                    <!-- Expandable Stats -->
                    <div v-show="showStats">
                        <div class="grid grid-cols-3 divide-x divide-slate-100 dark:divide-slate-800">
                            <!-- Dynamic Columns -->
                            <div v-for="(col, index) in statsColumns" :key="index" class="px-5 py-4">
                                <div class="flex items-center gap-2 mb-3">
                                    <component :is="col.icon" :class="`h-3.5 w-3.5 text-${col.color}-500`" />
                                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ col.title }}</h3>
                                </div>
                                <div class="space-y-2">
                                    <div v-if="col.data.length === 0" class="text-[11px] text-slate-400 italic">Belum ada data</div>
                                    <div v-for="(item, i) in col.data" :key="item.name" class="flex items-center gap-2">
                                        <span class="text-[10px] font-black text-slate-300 w-4">{{ i + 1 }}</span>
                                        <div class="flex-1 relative">
                                            <div :class="`h-6 rounded-lg bg-${col.color}-50 dark:bg-${col.color}-900/20 overflow-hidden`">
                                                <div :class="`h-full bg-${col.color}-100 dark:bg-${col.color}-900/40 rounded-lg transition-all`" :style="`width: ${Math.max(10, (item.count / (col.data[0]?.count || 1)) * 100)}%`"></div>
                                            </div>
                                            <div class="absolute inset-0 flex items-center justify-between px-2">
                                                <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300 truncate max-w-[80%]">{{ item.name }}</span>
                                                <span :class="`text-[11px] font-black text-${col.color}-600`">{{ item.count }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Catatan koordinat -->
                        <div class="px-5 py-2.5 border-t border-slate-100 dark:border-slate-800 bg-amber-50/60 dark:bg-amber-900/10">
                            <p class="text-[10px] text-amber-600 dark:text-amber-400 font-medium">
                                ⚠️ <strong>Catatan:</strong> Posisi titik pada peta sedikit digeser secara acak (<em>jitter ±~1km</em>) agar alumni di lokasi yang sama tidak menumpuk. Koordinat asli tersimpan di database.
                            </p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </TraceAdminLayout>
</template>

<style>
/* Leaflet Custom Styles */
.leaflet-container {
    background: #f1f5f9 !important;
}

.map-pin {
    transition: transform 0.15s ease;
}
.map-pin:hover {
    transform: scale(1.2);
}

/* Cluster Icon */
.custom-cluster {
    background: transparent !important;
    border: none !important;
}
.cluster-icon {
    width: 36px;
    height: 36px;
    background: rgba(37, 99, 235, 0.85);
    border: 3px solid white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 11px;
    font-weight: 900;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
    backdrop-filter: blur(4px);
}

/* Popup Styles */
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
.popup-badge {
    display: inline-block;
    font-size: 9px;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 3px 8px;
    border-radius: 999px;
    margin-bottom: 8px;
}
.popup-name {
    font-size: 14px;
    font-weight: 900;
    color: #1e293b;
    margin-bottom: 10px;
    line-height: 1.3;
}
.popup-divider {
    border-top: 1px solid #f1f5f9;
    margin: 8px 0;
}
.popup-info-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 4px;
}
.popup-label {
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #94a3b8;
    white-space: nowrap;
    padding-top: 1px;
}
.popup-value {
    font-size: 11px;
    font-weight: 700;
    color: #334155;
    text-align: right;
}
</style>
