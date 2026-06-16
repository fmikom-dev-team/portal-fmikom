<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import L from 'leaflet';
import {
    Layers, ChevronLeft, ChevronRight, RotateCcw, Filter,
    Download, Search, Sun, Moon, Eye, X, Crosshair
} from 'lucide-vue-next';
import { ref, computed, watch, onUnmounted, toRaw } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import TraceAdminLayout from '@/layouts/TraceAdminLayout.vue';
import { useLeafletMap } from '@/composables/useLeafletMap';
import 'leaflet.markercluster';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';
import 'leaflet.heat';

// ─── TYPES ─────────────────────────────────────────────────
interface AlumniData {
    profil_alumni_id: number;
    nama_lengkap: string;
    nim: string;
    angkatan: string;
    program_studi: string;
    instansi: string;
    detail: string;
    sektor_industri: string;
    nama_kota: string;
    latitude: string;
    longitude: string;
    tipe_lokasi: string;
}

interface MarkerEntry {
    marker: L.Marker;
    category: 'bekerja' | 'wirausaha' | 'studi' | 'belum';
    data: AlumniData;
    latlng: L.LatLng;
}

type ViewMode = 'cluster' | 'heat' | 'choropleth' | 'tematic';
type TematicField = 'status' | 'sektor' | 'angkatan' | 'prodi';

// ─── COMPOSABLE ────────────────────────────────────────────
const { mapContainer, map, isReady, isMapLoading, isDarkMap, currentZoom, toggleDarkMode } = useLeafletMap();
// Always use rawMap() to access the Leaflet instance (avoids Vue proxy issues)
const rawMap = () => map.value ? toRaw(map.value) as L.Map : null;

// ─── STATE ─────────────────────────────────────────────────
const breadcrumbs = [
    { title: 'WebGIS Alumni', href: '#' },
    { title: 'Peta Sebaran', href: '#' },
];

// View
const viewMode = ref<ViewMode>('cluster');
const tematicField = ref<TematicField>('status');
const showSidebar = ref(true);
const showMarkers = ref(true);
const isLoading = ref(false);

// Layer visibility
const layerVisibility = ref({
    bekerja: true,
    wirausaha: true,
    studi: true,
    belum: true,
});

// Search
const searchQuery = ref('');
const searchFocused = ref(false);

// Filters
const filters = ref({
    angkatan: '',
    program_studi: '',
    sektor: '',
});

// Data
const allAlumni = ref<AlumniData[]>([]);
const allMarkers: MarkerEntry[] = [];
const filterOptions = ref({ angkatan: [] as string[], prodi: [] as string[], sektor: [] as string[] });

// Completion
const completionMeta = ref({
    total_alumni: 0, mapped_count: 0, completion_rate: 0,
    is_filter_active: false,
    filtered: { total: 0, mapped: 0, rate: 0 },
});

// Viewport-aware stats
const viewportStats = ref({
    total: 0, bekerja: 0, wirausaha: 0, studi: 0, belum: 0,
    sektorData: [] as { name: string; count: number }[],
    cityData: [] as { name: string; count: number }[],
    instansiData: [] as { name: string; count: number }[],
    angkatanData: [] as { angkatan: string; count: number }[],
});

const lastUpdated = ref('');
const isViewportUpdating = ref(false);

// Radius search
const radiusMode = ref(false);
const radiusKm = ref(10);
const radiusCenter = ref<L.LatLng | null>(null);
const radiusAlumniCount = ref(0);
let radiusCircle: L.Circle | null = null;
let radiusMarker: L.Marker | null = null;

// Layers
let markerCluster: any = null;
let heatLayer: any = null;
let choroplethLayer: any = null;
let geoJsonCache: any = null;
let moveEndTimer: ReturnType<typeof setTimeout> | null = null;

// ─── MARKER COLORS ─────────────────────────────────────────
const CATEGORY_CONFIG: Record<string, { color: string; bg: string; label: string; icon: string }> = {
    bekerja:   { color: '#3b82f6', bg: 'bg-blue-500',   label: 'Bekerja',         icon: '💼' },
    wirausaha: { color: '#10b981', bg: 'bg-emerald-500', label: 'Wirausaha',       icon: '🏢' },
    studi:     { color: '#8b5cf6', bg: 'bg-violet-500',  label: 'Lanjut Studi',    icon: '🎓' },
    belum:     { color: '#f59e0b', bg: 'bg-amber-500',   label: 'Belum Bekerja',   icon: '🔍' },
};

const TEMATIC_PALETTES: Record<string, string[]> = {
    status:   ['#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#ef4444', '#06b6d4', '#f97316', '#ec4899'],
    sektor:   ['#0C447C', '#1a6bb5', '#3a8fd4', '#85B7EB', '#EF9F27', '#10b981', '#ef4444', '#8b5cf6', '#f97316', '#06b6d4'],
    angkatan: ['#0C447C', '#1a6bb5', '#3a8fd4', '#85B7EB', '#c5dcf5', '#EF9F27', '#f59e0b', '#f97316'],
    prodi:    ['#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#ef4444', '#06b6d4', '#ec4899', '#84cc16'],
};

// ─── COMPUTED ───────────────────────────────────────────────
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

// ─── HELPERS ────────────────────────────────────────────────
const formatTime = (d: Date) =>
    d.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) +
    ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

const getCategory = (tipe: string): 'bekerja' | 'wirausaha' | 'studi' | 'belum' => {
    if (tipe === 'Bekerja') return 'bekerja';
    if (tipe === 'Wirausaha') return 'wirausaha';
    if (tipe === 'Lanjut Studi') return 'studi';
    return 'belum';
};

const createMarkerIcon = (color: string, size: number = 10) => {
    return L.divIcon({
        html: `<div style="width:${size}px;height:${size}px;background:${color};border:2px solid white;border-radius:50%;box-shadow:0 2px 6px ${color}66;"></div>`,
        className: 'custom-marker-icon',
        iconSize: [size, size],
        iconAnchor: [size / 2, size / 2],
    });
};

const createPopupContent = (a: AlumniData) => {
    const cat = getCategory(a.tipe_lokasi);
    const cfg = CATEGORY_CONFIG[cat];
    return `
        <div style="min-width:240px;font-family:system-ui,-apple-system,sans-serif;">
            <div style="display:inline-block;font-size:9px;font-weight:900;letter-spacing:0.08em;text-transform:uppercase;padding:3px 8px;border-radius:999px;margin-bottom:8px;background:${cfg.color}22;color:${cfg.color};">
                ${cfg.label}
            </div>
            <div style="font-size:14px;font-weight:900;color:#1e293b;margin-bottom:10px;line-height:1.3;">${a.nama_lengkap}</div>
            <div style="border-top:1px solid #f1f5f9;margin:8px 0;"></div>
            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                <span style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#94a3b8;">PRODI</span>
                <span style="font-size:11px;font-weight:700;color:#334155;text-align:right;">${a.program_studi || '-'}</span>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                <span style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#94a3b8;">ANGKATAN</span>
                <span style="font-size:11px;font-weight:700;color:#334155;">${a.angkatan || '-'}</span>
            </div>
            ${a.instansi ? `<div style="border-top:1px solid #f1f5f9;margin:8px 0;"></div>
            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                <span style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#94a3b8;">${cat === 'studi' ? 'UNIVERSITAS' : cat === 'belum' ? 'ALAMAT DOMISILI' : 'PERUSAHAAN'}</span>
                <span style="font-size:11px;font-weight:700;color:#334155;text-align:right;max-width:60%;">${a.instansi}</span>
            </div>` : ''}
            ${a.detail ? `<div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                <span style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#94a3b8;">STATUS</span>
                <span style="font-size:11px;font-weight:700;color:#334155;text-align:right;max-width:60%;">${a.detail}</span>
            </div>` : ''}
            ${a.nama_kota ? `<div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                <span style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#94a3b8;">KOTA</span>
                <span style="font-size:11px;font-weight:700;color:#334155;">${a.nama_kota}</span>
            </div>` : ''}
            <div style="border-top:1px solid #f1f5f9;margin:8px 0;"></div>
            <a href="/trace/admin/alumni/${a.profil_alumni_id}" target="_blank"
               style="display:block;text-align:center;background:${cfg.color};color:white;padding:8px 16px;border-radius:12px;font-size:11px;font-weight:800;text-decoration:none;">
                📋 Lihat Profil Lengkap
            </a>
        </div>`;
};

// ─── DATA FETCHING ──────────────────────────────────────────
const fetchMapData = async () => {
    const m = rawMap();
    if (!m) {
        console.warn('[Map] fetchMapData called but rawMap() is null');
        return;
    }
    isLoading.value = true;

    try {
        const params: any = {};
        if (filters.value.angkatan) params.angkatan = filters.value.angkatan;
        if (filters.value.program_studi) params.program_studi = filters.value.program_studi;
        if (filters.value.sektor) params.sektor = filters.value.sektor;

        console.log('[Map] Fetching data...');
        const { data: response } = await axios.get('/trace/admin/map/data', { params });
        const alumniData: AlumniData[] = response.data || [];
        console.log('[Map] Data received:', alumniData.length, 'alumni');

        completionMeta.value = response.meta || completionMeta.value;
        allAlumni.value = alumniData;

        // Extract filter options
        const angkatanSet = new Set<string>();
        const prodiSet = new Set<string>();
        const sektorSet = new Set<string>();
        alumniData.forEach(a => {
            if (a.angkatan) angkatanSet.add(a.angkatan);
            if (a.program_studi) prodiSet.add(a.program_studi);
            if (a.sektor_industri)
                sektorSet.add(a.sektor_industri);
        });
        filterOptions.value = {
            angkatan: Array.from(angkatanSet).sort(),
            prodi: Array.from(prodiSet).sort(),
            sektor: Array.from(sektorSet).sort(),
        };

        // Clear existing layers
        clearAllLayers();

        // Build markers
        allMarkers.length = 0;
        const heatPoints: [number, number, number][] = [];

        markerCluster = (L as any).markerClusterGroup({
            showCoverageOnHover: false,
            zoomToBoundsOnClick: true,
            maxClusterRadius: 50,
            iconCreateFunction: (cluster: any) => {
                const count = cluster.getChildCount();
                const size = count > 100 ? 44 : count > 50 ? 40 : count > 10 ? 36 : 30;
                return L.divIcon({
                    html: `<div class="cluster-icon" style="width:${size}px;height:${size}px;">${count}</div>`,
                    className: 'custom-cluster',
                    iconSize: [size, size],
                });
            },
        });

        alumniData.forEach((a) => {
            if (a.latitude == null || a.longitude == null || a.latitude === '' || a.longitude === '') return;
            const lat = parseFloat(String(a.latitude));
            const lng = parseFloat(String(a.longitude));
            if (isNaN(lat) || isNaN(lng) || lat === 0 || lng === 0) return;

            // Jitter ±0.01 (~1km)
            const jLat = lat + (Math.random() - 0.5) * 0.02;
            const jLng = lng + (Math.random() - 0.5) * 0.02;
            const latlng = L.latLng(jLat, jLng);

            const category = getCategory(a.tipe_lokasi);
            const cfg = CATEGORY_CONFIG[category];

            const marker = L.marker(latlng, {
                icon: createMarkerIcon(cfg.color),
            });
            marker.bindPopup(createPopupContent(a), {
                maxWidth: 300,
                className: 'custom-popup',
            });

            allMarkers.push({ marker, category, data: a, latlng });
            heatPoints.push([jLat, jLng, 1]);
        });

        console.log('[Map] Markers created:', allMarkers.length);

        // Apply layers based on mode
        await applyLayers(heatPoints);

        // Fit bounds
        if (allMarkers.length > 0 && rawMap()) {
            const visibleMarkers = allMarkers.filter(m => layerVisibility.value[m.category]);
            if (visibleMarkers.length > 0) {
                const group = L.featureGroup(visibleMarkers.map(m => m.marker));
                try { rawMap()!.fitBounds(group.getBounds().pad(0.2)); } catch (e) {}
            }
        }

        // Setup viewport tracking
        setupViewportTracking();
        updateViewportStats();

        lastUpdated.value = formatTime(new Date());
        console.log('[Map] Data loaded successfully');

    } catch (error) {
        console.error('[Map] Error fetching map data:', error);
    } finally {
        isLoading.value = false;
    }
};

// ─── LAYER MANAGEMENT ───────────────────────────────────────
const clearAllLayers = () => {
    if (!rawMap()) return;
    if (markerCluster) { try { rawMap()!.removeLayer(markerCluster); } catch (e) {} }
    if (heatLayer) { try { rawMap()!.removeLayer(heatLayer); } catch (e) {} heatLayer = null; }
    if (choroplethLayer) { try { rawMap()!.removeLayer(choroplethLayer); } catch (e) {} choroplethLayer = null; }
};

const applyLayers = async (heatPoints?: [number, number, number][]) => {
    if (!rawMap()) return;
    clearAllLayers();

    const visibleMarkers = allMarkers.filter(m => layerVisibility.value[m.category]);

    if (viewMode.value === 'cluster') {
        markerCluster.clearLayers();
        markerCluster.addLayers(visibleMarkers.map(m => m.marker));
        rawMap()!.addLayer(markerCluster);

    } else if (viewMode.value === 'heat') {
        const hp = heatPoints || visibleMarkers.map(m => [m.latlng.lat, m.latlng.lng, 1] as [number, number, number]);
        try {
            heatLayer = (L as any).heatLayer(hp, {
                radius: 25, blur: 15, maxZoom: 12,
                gradient: { 0.2: '#3b82f6', 0.4: '#22c55e', 0.6: '#eab308', 0.8: '#f97316', 1.0: '#ef4444' },
            }).addTo(rawMap()!);
        } catch (e) {
            console.warn('Heatmap render error:', e);
        }
        // Optionally show markers on top of heatmap
        if (showMarkers.value) {
            markerCluster.clearLayers();
            visibleMarkers.forEach(m => m.marker.setIcon(createMarkerIcon(CATEGORY_CONFIG[m.category].color, 6)));
            markerCluster.addLayers(visibleMarkers.map(m => m.marker));
            rawMap()!.addLayer(markerCluster);
        }

    } else if (viewMode.value === 'choropleth') {
        await loadChoropleth(visibleMarkers);
        // Optionally show markers (smaller) on top of choropleth
        if (showMarkers.value) {
            markerCluster.clearLayers();
            visibleMarkers.forEach(m => m.marker.setIcon(createMarkerIcon(CATEGORY_CONFIG[m.category].color, 6)));
            markerCluster.addLayers(visibleMarkers.map(m => m.marker));
            rawMap()!.addLayer(markerCluster);
        }

    } else if (viewMode.value === 'tematic') {
        // Color markers by tematic field
        const palette = TEMATIC_PALETTES[tematicField.value];
        const valueColorMap = new Map<string, string>();
        let colorIdx = 0;

        visibleMarkers.forEach(m => {
            let key = '';
            if (tematicField.value === 'status') key = m.data.tipe_lokasi || 'Lainnya';
            else if (tematicField.value === 'sektor') key = m.data.sektor_industri || 'Tidak Diketahui';
            else if (tematicField.value === 'angkatan') key = m.data.angkatan || '?';
            else if (tematicField.value === 'prodi') key = m.data.program_studi || 'Tidak Diketahui';

            if (!valueColorMap.has(key)) {
                valueColorMap.set(key, palette[colorIdx % palette.length]);
                colorIdx++;
            }
            m.marker.setIcon(createMarkerIcon(valueColorMap.get(key)!, 12));
        });

        markerCluster.clearLayers();
        markerCluster.addLayers(visibleMarkers.map(m => m.marker));
        rawMap()!.addLayer(markerCluster);
    }
};

// ─── CHOROPLETH ─────────────────────────────────────────────
const loadChoropleth = async (markers: MarkerEntry[]) => {
    if (!rawMap()) return;

    if (!geoJsonCache) {
        try {
            const resp = await axios.get('/geojson/indonesia-provinces.json');
            geoJsonCache = resp.data;
        } catch (e) {
            console.error('Failed to load GeoJSON:', e);
            return;
        }
    }

    // Count per province + kabupaten breakdown
    const provCountMap = new Map<string, number>();
    const provKabMap = new Map<string, Map<string, number>>();

    const getBBox = (coords: any): [number, number, number, number] => {
        let minLat = 90, maxLat = -90, minLng = 180, maxLng = -180;
        const flatten = (c: any) => {
            if (typeof c[0] === 'number') {
                minLng = Math.min(minLng, c[0]); maxLng = Math.max(maxLng, c[0]);
                minLat = Math.min(minLat, c[1]); maxLat = Math.max(maxLat, c[1]);
            } else { c.forEach(flatten); }
        };
        flatten(coords);
        return [minLat, maxLat, minLng, maxLng];
    };

    const features = geoJsonCache.features.map((f: any) => ({
        name: f.properties.PROVINSI || f.properties.Propinsi || f.properties.name || 'Unknown',
        bbox: getBBox(f.geometry.coordinates),
    }));

    markers.forEach(m => {
        const lat = m.latlng.lat;
        const lng = m.latlng.lng;
        for (const fb of features) {
            const [minLat, maxLat, minLng, maxLng] = fb.bbox;
            if (lat >= minLat && lat <= maxLat && lng >= minLng && lng <= maxLng) {
                provCountMap.set(fb.name, (provCountMap.get(fb.name) || 0) + 1);
                // Track kabupaten
                const kota = m.data.nama_kota || 'Tidak Diketahui';
                if (!provKabMap.has(fb.name)) provKabMap.set(fb.name, new Map());
                const kabMap = provKabMap.get(fb.name)!;
                kabMap.set(kota, (kabMap.get(kota) || 0) + 1);
                break;
            }
        }
    });

    const maxCount = Math.max(...Array.from(provCountMap.values()), 1);
    const getColor = (count: number) => {
        if (count === 0) return 'rgba(12,68,124,0.03)';
        const r = count / maxCount;
        if (r > 0.7) return '#0C447C';
        if (r > 0.5) return '#1a6bb5';
        if (r > 0.3) return '#3a8fd4';
        if (r > 0.1) return '#85B7EB';
        return '#c5dcf5';
    };

    const buildTooltip = (provName: string, total: number) => {
        const kabMap = provKabMap.get(provName);
        let kabHtml = '';
        if (kabMap && kabMap.size > 0) {
            const sorted = Array.from(kabMap.entries()).sort((a, b) => b[1] - a[1]);
            const top5 = sorted.slice(0, 5);
            kabHtml = '<div style="border-top:1px solid #e2e8f0;margin:6px 0 4px;"></div>';
            kabHtml += top5.map(([name, count]) =>
                `<div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:2px;">
                    <span style="font-size:10px;color:#475569;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:150px;">${name}</span>
                    <span style="font-size:10px;font-weight:900;color:#0C447C;">${count}</span>
                </div>`
            ).join('');
            if (sorted.length > 5) {
                kabHtml += `<div style="font-size:9px;color:#94a3b8;text-align:center;margin-top:2px;">+${sorted.length - 5} kota/kab lainnya</div>`;
            }
        }
        return `<div style="min-width:180px;">
            <div style="font-size:12px;font-weight:900;color:#0C447C;">${provName}</div>
            <div style="font-size:11px;font-weight:700;color:#64748b;">${total} Alumni</div>
            ${kabHtml}
        </div>`;
    };

    choroplethLayer = L.geoJSON(geoJsonCache, {
        style: (feature: any) => {
            const name = feature?.properties?.PROVINSI || feature?.properties?.Propinsi || '';
            const count = provCountMap.get(name) || 0;
            return {
                fillColor: getColor(count),
                weight: 1.5, opacity: 0.8, color: '#0C447C',
                fillOpacity: count > 0 ? 0.6 : 0.05,
            };
        },
        onEachFeature: (feature: any, layer: any) => {
            const name = feature?.properties?.PROVINSI || feature?.properties?.Propinsi || 'Unknown';
            const count = provCountMap.get(name) || 0;
            layer.bindTooltip(buildTooltip(name, count), {
                sticky: true,
                className: 'province-tooltip',
            });
            layer.on({
                mouseover: (e: any) => { e.target.setStyle({ weight: 3, fillOpacity: 0.8, color: '#EF9F27' }); },
                mouseout: (e: any) => { choroplethLayer?.resetStyle(e.target); },
            });
        },
    }).addTo(rawMap()!);
};

// ─── VIEWPORT TRACKING ─────────────────────────────────────
const setupViewportTracking = () => {
    if (!rawMap()) return;
    rawMap()!.off('moveend', debouncedViewportUpdate);
    rawMap()!.on('moveend', debouncedViewportUpdate);
};

const debouncedViewportUpdate = () => {
    if (moveEndTimer) clearTimeout(moveEndTimer);
    isViewportUpdating.value = true;
    moveEndTimer = setTimeout(() => {
        updateViewportStats();
        setTimeout(() => { isViewportUpdating.value = false; }, 600);
    }, 400);
};

const updateViewportStats = () => {
    if (!rawMap()) return;
    const bounds = rawMap()!.getBounds();

    const visible = allMarkers.filter(m =>
        layerVisibility.value[m.category] && bounds.contains(m.latlng)
    );

    let bekerja = 0, wirausaha = 0, studi = 0, belum = 0;
    const sektorMap = new Map<string, number>();
    const cityMap = new Map<string, number>();
    const instansiMap = new Map<string, number>();
    const angkatanMap = new Map<string, number>();

    visible.forEach(m => {
        if (m.category === 'bekerja') bekerja++;
        else if (m.category === 'wirausaha') wirausaha++;
        else if (m.category === 'studi') studi++;
        else belum++;

        if (m.data.sektor_industri) sektorMap.set(m.data.sektor_industri, (sektorMap.get(m.data.sektor_industri) || 0) + 1);
        if (m.data.nama_kota) cityMap.set(m.data.nama_kota, (cityMap.get(m.data.nama_kota) || 0) + 1);
        if (m.data.instansi) instansiMap.set(m.data.instansi, (instansiMap.get(m.data.instansi) || 0) + 1);
        if (m.data.angkatan) angkatanMap.set(m.data.angkatan, (angkatanMap.get(m.data.angkatan) || 0) + 1);
    });

    const sortMap = (m: Map<string, number>) => Array.from(m.entries()).map(([name, count]) => ({ name, count })).sort((a, b) => b.count - a.count);

    viewportStats.value = {
        total: visible.length,
        bekerja, wirausaha, studi, belum,
        sektorData: sortMap(sektorMap),
        cityData: sortMap(cityMap),
        instansiData: sortMap(instansiMap),
        angkatanData: Array.from(angkatanMap.entries()).map(([angkatan, count]) => ({ angkatan, count })).sort((a, b) => a.angkatan.localeCompare(b.angkatan)),
    };
};

// ─── RADIUS SEARCH ──────────────────────────────────────────
const toggleRadiusMode = () => {
    radiusMode.value = !radiusMode.value;
    if (!radiusMode.value) clearRadius();
};

const clearRadius = () => {
    const m = rawMap();
    if (!m) return;
    if (radiusCircle) { m.removeLayer(radiusCircle); radiusCircle = null; }
    if (radiusMarker) { m.removeLayer(radiusMarker); radiusMarker = null; }
    radiusCenter.value = null;
    radiusAlumniCount.value = 0;
    m.off('click', onRadiusClick);
};

const activateRadiusListener = () => {
    const m = rawMap();
    if (!m) return;
    m.off('click', onRadiusClick);
    m.on('click', onRadiusClick);
};

const onRadiusClick = (e: L.LeafletMouseEvent) => {
    if (!radiusMode.value) return;
    placeRadius(e.latlng);
};

const placeRadius = (center: L.LatLng) => {
    const m = rawMap();
    if (!m) return;

    // Clear previous
    if (radiusCircle) m.removeLayer(radiusCircle);
    if (radiusMarker) m.removeLayer(radiusMarker);

    radiusCenter.value = center;
    const radiusMeters = radiusKm.value * 1000;

    radiusCircle = L.circle(center, {
        radius: radiusMeters,
        color: '#0C447C',
        weight: 2,
        fillColor: '#0C447C',
        fillOpacity: 0.08,
        dashArray: '6 4',
    }).addTo(m);

    radiusMarker = L.marker(center, {
        icon: L.divIcon({
            html: '<div style="width:12px;height:12px;background:#0C447C;border:3px solid white;border-radius:50%;box-shadow:0 2px 8px rgba(12,68,124,0.5);"></div>',
            className: 'radius-center-icon',
            iconSize: [12, 12],
            iconAnchor: [6, 6],
        }),
    }).addTo(m);

    // Count alumni in radius
    const count = allMarkers.filter(mk =>
        layerVisibility.value[mk.category] && center.distanceTo(mk.latlng) <= radiusMeters
    ).length;
    radiusAlumniCount.value = count;

    radiusMarker.bindPopup(
        `<div style="text-align:center;font-family:system-ui;padding:4px;">
            <div style="font-size:24px;font-weight:900;color:#0C447C;">${count}</div>
            <div style="font-size:10px;font-weight:700;color:#64748b;">alumni dalam radius ${radiusKm.value} km</div>
        </div>`,
        { className: 'custom-popup' }
    ).openPopup();
};

const updateRadiusSize = (km: number) => {
    radiusKm.value = km;
    if (radiusCenter.value) placeRadius(radiusCenter.value);
};

// ─── SEARCH ─────────────────────────────────────────────────
const highlightAlumni = (alumni: AlumniData) => {
    searchQuery.value = '';
    searchFocused.value = false;
    const entry = allMarkers.find(m => m.data.profil_alumni_id === alumni.profil_alumni_id);
    if (entry && rawMap()) {
        rawMap()!.flyTo(entry.latlng, 14, { duration: 1 });
        setTimeout(() => entry.marker.openPopup(), 1000);
    }
};

// ─── MODE/FILTER ACTIONS ────────────────────────────────────
const setViewMode = (mode: ViewMode) => {
    viewMode.value = mode;
    // Default: markers OFF for heat, ON for rest
    showMarkers.value = mode !== 'heat';
    // Exit radius mode when switching
    if (radiusMode.value) { radiusMode.value = false; clearRadius(); }
    // Reset marker icons to default size
    allMarkers.forEach(m => {
        m.marker.setIcon(createMarkerIcon(CATEGORY_CONFIG[m.category].color));
    });
    applyLayers();
};

const toggleMarkers = () => {
    showMarkers.value = !showMarkers.value;
    applyLayers();
};

const toggleLayer = (key: keyof typeof layerVisibility.value) => {
    layerVisibility.value[key] = !layerVisibility.value[key];
    applyLayers();
    updateViewportStats();
};

const resetFilters = () => {
    filters.value = { angkatan: '', program_studi: '', sektor: '' };
    fetchMapData();
};

const onFilterChange = () => fetchMapData();

// ─── EXPORT ─────────────────────────────────────────────────
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

const downloadFile = (content: string, filename: string, mime: string) => {
    const blob = new Blob([content], { type: `${mime};charset=utf-8;` });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url; a.download = filename; a.click();
    URL.revokeObjectURL(url);
};

// ─── WATCHERS ───────────────────────────────────────────────
watch(isReady, (ready) => { if (ready) fetchMapData(); });
watch(tematicField, () => { if (viewMode.value === 'tematic') applyLayers(); });

onUnmounted(() => {
    if (moveEndTimer) clearTimeout(moveEndTimer);
    clearRadius();
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

                <!-- Header + Mode Toggle -->
                <div class="rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-xl px-4 py-3">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#0C447C] shadow-md">
                            <Layers class="h-4 w-4 text-white" />
                        </div>
                        <div class="flex-1">
                            <p class="text-[10px] font-black tracking-widest text-slate-400 uppercase">WebGIS</p>
                            <p class="text-sm font-black text-slate-800 dark:text-white leading-none">Peta Sebaran Alumni</p>
                        </div>
                        <button @click="toggleDarkMode" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Toggle light/dark map">
                            <Moon v-if="!isDarkMap" class="h-4 w-4 text-slate-400" />
                            <Sun v-else class="h-4 w-4 text-amber-400" />
                        </button>
                    </div>

                    <!-- Mode Buttons -->
                    <div class="flex rounded-xl bg-slate-100 dark:bg-slate-800 p-1 gap-0.5">
                        <button v-for="mode in (['cluster', 'heat', 'choropleth', 'tematic'] as ViewMode[])" :key="mode"
                            @click="setViewMode(mode)"
                            :class="viewMode === mode ? 'bg-white dark:bg-slate-700 text-[#0C447C] shadow-sm' : 'text-slate-400 hover:text-slate-600'"
                            class="flex-1 px-1.5 py-1.5 rounded-lg text-[8px] font-black transition-all uppercase tracking-wide"
                        >{{ mode === 'cluster' ? 'TITIK' : mode === 'heat' ? 'HEAT' : mode === 'choropleth' ? 'WILAYAH' : 'TEMATIK' }}</button>
                    </div>

                    <!-- Tematic field selector -->
                    <div v-if="viewMode === 'tematic'" class="mt-2">
                        <select v-model="tematicField" class="w-full h-8 rounded-lg bg-slate-50 dark:bg-slate-800 px-2 text-[10px] font-bold text-slate-700 dark:text-slate-300 outline-none border border-slate-200 dark:border-slate-700">
                            <option value="status">Warna berdasarkan Status</option>
                            <option value="sektor">Warna berdasarkan Sektor</option>
                            <option value="angkatan">Warna berdasarkan Angkatan</option>
                            <option value="prodi">Warna berdasarkan Prodi</option>
                        </select>
                    </div>

                    <!-- Show/Hide Markers toggle (only heat & choropleth) -->
                    <label v-if="viewMode === 'heat' || viewMode === 'choropleth'" @click.prevent="toggleMarkers"
                        class="flex items-center gap-2 mt-2 px-2 py-1.5 rounded-lg cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                    >
                        <div class="h-4 w-4 rounded border-2 flex items-center justify-center transition-all"
                            :class="showMarkers ? 'bg-blue-600 border-blue-600' : 'border-slate-300 dark:border-slate-600'"
                        >
                            <svg v-if="showMarkers" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">Tampilkan titik alumni</span>
                    </label>
                </div>

                <!-- Search -->
                <div class="rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-xl overflow-hidden">
                    <div class="flex items-center gap-2 px-3 py-2.5">
                        <Search class="h-3.5 w-3.5 text-slate-400 flex-shrink-0" />
                        <input
                            v-model="searchQuery"
                            @focus="searchFocused = true"
                            @blur="setTimeout(() => searchFocused = false, 200)"
                            placeholder="Cari nama, NIM, perusahaan..."
                            class="w-full bg-transparent text-[11px] font-medium text-slate-700 dark:text-slate-300 outline-none placeholder-slate-400"
                        />
                        <button v-if="searchQuery" @click="searchQuery = ''" class="text-slate-400 hover:text-slate-600">
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
                <div class="rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-xl px-4 py-3">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-1.5">
                            <Crosshair class="h-3.5 w-3.5" :class="radiusMode ? 'text-[#0C447C]' : 'text-slate-400'" />
                            <span class="text-[9px] font-black uppercase tracking-widest" :class="radiusMode ? 'text-[#0C447C]' : 'text-slate-400'">Radius Search</span>
                        </div>
                        <button @click="toggleRadiusMode(); activateRadiusListener()"
                            class="px-2.5 py-1 rounded-lg text-[9px] font-black transition-all"
                            :class="radiusMode ? 'bg-[#0C447C] text-white shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-slate-600'"
                        >{{ radiusMode ? 'AKTIF ✕' : 'AKTIFKAN' }}</button>
                    </div>

                    <template v-if="radiusMode">
                        <p class="text-[9px] text-slate-400 mb-2">📍 Klik di peta untuk menentukan titik pusat</p>
                        <div class="flex gap-1">
                            <button v-for="km in [5, 10, 25, 50]" :key="km"
                                @click="updateRadiusSize(km)"
                                class="flex-1 py-1 rounded-lg text-[9px] font-black transition-all"
                                :class="radiusKm === km ? 'bg-[#0C447C] text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700'"
                            >{{ km }} km</button>
                        </div>
                        <div v-if="radiusCenter" class="mt-2 flex items-center justify-between px-2 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                            <span class="text-[10px] font-bold text-blue-700 dark:text-blue-300">{{ radiusAlumniCount }} alumni ditemukan</span>
                            <button @click="clearRadius" class="text-[9px] font-bold text-red-500 hover:text-red-600">Hapus</button>
                        </div>
                    </template>
                </div>

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
                        <button v-if="hasActiveFilters" @click="resetFilters" class="flex items-center gap-1 text-[9px] font-bold text-red-500 hover:text-red-600">
                            <RotateCcw class="h-2.5 w-2.5" /> Reset
                        </button>
                    </div>
                    <div class="p-3 pt-1 space-y-2">
                        <div>
                            <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-0.5 block">Angkatan</label>
                            <select v-model="filters.angkatan" @change="onFilterChange" class="w-full h-8 rounded-lg bg-slate-50 dark:bg-slate-800 px-2 text-[11px] font-bold text-slate-700 dark:text-slate-300 outline-none border border-slate-200 dark:border-slate-700 focus:border-blue-500">
                                <option value="">Semua</option>
                                <option v-for="opt in filterOptions.angkatan" :key="opt" :value="opt">{{ opt }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-0.5 block">Program Studi</label>
                            <select v-model="filters.program_studi" @change="onFilterChange" class="w-full h-8 rounded-lg bg-slate-50 dark:bg-slate-800 px-2 text-[11px] font-bold text-slate-700 dark:text-slate-300 outline-none border border-slate-200 dark:border-slate-700 focus:border-blue-500">
                                <option value="">Semua</option>
                                <option v-for="opt in filterOptions.prodi" :key="opt" :value="opt">{{ opt }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-0.5 block">Sektor Industri</label>
                            <select v-model="filters.sektor" @change="onFilterChange" class="w-full h-8 rounded-lg bg-slate-50 dark:bg-slate-800 px-2 text-[11px] font-bold text-slate-700 dark:text-slate-300 outline-none border border-slate-200 dark:border-slate-700 focus:border-blue-500">
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
                            <button @click="exportCSV" class="flex-1 flex items-center justify-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 px-3 py-2 text-white transition-colors">
                                <Download class="h-3 w-3" />
                                <span class="text-[10px] font-black">.CSV</span>
                            </button>
                            <button @click="exportGeoJSON" class="flex-1 flex items-center justify-center gap-1.5 rounded-xl bg-[#0C447C] hover:bg-[#0a3866] px-3 py-2 text-white transition-colors">
                                <Download class="h-3 w-3" />
                                <span class="text-[10px] font-black">.GeoJSON</span>
                            </button>
                        </div>
                        <p class="text-[9px] text-slate-400 mt-1.5 text-center">Hanya mengekspor {{ viewportStats.total }} alumni di area viewport saat ini</p>
                    </div>
                </div>
            </transition>

            <!-- Sidebar Toggle -->
            <button @click="showSidebar = !showSidebar"
                class="absolute right-4 top-1/2 -translate-y-1/2 z-[1001] flex h-8 w-5 items-center justify-center rounded-l-lg bg-white/90 dark:bg-slate-900/90 shadow-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                :class="showSidebar ? 'right-[340px]' : 'right-4'"
            >
                <ChevronRight v-if="!showSidebar" class="h-3.5 w-3.5 text-slate-500" />
                <ChevronLeft v-else class="h-3.5 w-3.5 text-slate-500" />
            </button>

            <!-- ==================== BOTTOM LEGEND BAR ==================== -->
            <div class="absolute bottom-0 left-0 right-0 z-[1000]">
                <div class="rounded-t-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-xl mx-4 mb-0">

                    <!-- Legend Row -->
                    <div class="flex items-center justify-between px-5 py-2.5 flex-wrap gap-2">
                        <div class="flex items-center gap-4 flex-wrap">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Legenda</span>

                            <!-- Active filter indicator -->
                            <div v-if="hasActiveFilters" class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800">
                                <Filter class="h-2.5 w-2.5 text-amber-600" />
                                <span class="text-[9px] font-black text-amber-600 uppercase tracking-wider">Filter Aktif</span>
                            </div>

                            <!-- Marker legend (cluster/tematic) -->
                            <template v-if="viewMode === 'cluster'">
                                <div v-for="(cfg, key) in CATEGORY_CONFIG" :key="key" class="flex items-center gap-1">
                                    <div class="h-2.5 w-2.5 rounded-full" :style="{ background: cfg.color }"></div>
                                    <span class="text-[9px] font-bold text-slate-500">{{ cfg.label }}</span>
                                </div>
                            </template>

                            <!-- Tematic legend -->
                            <template v-if="viewMode === 'tematic' && tematicLegend.length > 0">
                                <div v-for="item in tematicLegend.slice(0, 8)" :key="item.name" class="flex items-center gap-1">
                                    <div class="h-2.5 w-2.5 rounded-full" :style="{ background: item.color }"></div>
                                    <span class="text-[9px] font-bold text-slate-500">{{ item.name }} ({{ item.count }})</span>
                                </div>
                            </template>

                            <!-- Heat gradient -->
                            <div v-if="viewMode === 'heat'" class="flex items-center gap-1.5">
                                <span class="text-[9px] font-bold text-slate-400">Rendah</span>
                                <div class="h-2.5 w-20 rounded-full" style="background: linear-gradient(to right, #3b82f6, #22c55e, #eab308, #f97316, #ef4444);"></div>
                                <span class="text-[9px] font-bold text-slate-400">Tinggi</span>
                            </div>

                            <!-- Choropleth gradient -->
                            <div v-if="viewMode === 'choropleth'" class="flex items-center gap-1.5">
                                <span class="text-[9px] font-bold text-slate-400">Sedikit</span>
                                <div class="h-2.5 w-20 rounded-full" style="background: linear-gradient(to right, #c5dcf5, #85B7EB, #3a8fd4, #1a6bb5, #0C447C);"></div>
                                <span class="text-[9px] font-bold text-slate-400">Banyak</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 text-[9px] text-slate-400">
                            <span>⚠️ Titik di-jitter ±1km</span>
                            <span>📋 Sumber: Profil & Karir Alumni</span>
                            <span v-if="lastUpdated">● {{ lastUpdated }}</span>
                        </div>
                    </div>
                </div>
            </div>

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
