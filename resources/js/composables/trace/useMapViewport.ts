import { ref } from 'vue';
import L from 'leaflet';
import { type MarkerEntry } from './useMapData';

export interface ViewportStats {
    total: number;
    bekerja: number;
    wirausaha: number;
    studi: number;
    belum: number;
    sektorData: { name: string; count: number }[];
    cityData: { name: string; count: number }[];
    instansiData: { name: string; count: number }[];
    angkatanData: { angkatan: string; count: number }[];
}

export function useMapViewport() {
    const viewportStats = ref<ViewportStats>({
        total: 0, bekerja: 0, wirausaha: 0, studi: 0, belum: 0,
        sektorData: [],
        cityData: [],
        instansiData: [],
        angkatanData: [],
    });

    const isViewportUpdating = ref(false);
    let moveEndTimer: ReturnType<typeof setTimeout> | null = null;

    const updateViewportStats = (
        rawMap: () => L.Map | null,
        allMarkers: MarkerEntry[],
        layerVisibility: Record<string, boolean>,
    ) => {
        if (!rawMap()) return;
        const bounds = rawMap()!.getBounds();

        const visible = allMarkers.filter(m =>
            layerVisibility[m.category] && bounds.contains(m.latlng)
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

    const setupViewportTracking = (
        rawMap: () => L.Map | null,
        allMarkers: MarkerEntry[],
        layerVisibility: Record<string, boolean>,
    ) => {
        if (!rawMap()) return;

        const debouncedUpdate = () => {
            if (moveEndTimer) clearTimeout(moveEndTimer);
            isViewportUpdating.value = true;
            moveEndTimer = setTimeout(() => {
                updateViewportStats(rawMap, allMarkers, layerVisibility);
                setTimeout(() => { isViewportUpdating.value = false; }, 600);
            }, 400);
        };

        rawMap()!.off('moveend', debouncedUpdate);
        rawMap()!.on('moveend', debouncedUpdate);
    };

    const cleanup = () => {
        if (moveEndTimer) clearTimeout(moveEndTimer);
    };

    return {
        viewportStats,
        isViewportUpdating,
        updateViewportStats,
        setupViewportTracking,
        cleanup,
    };
}
