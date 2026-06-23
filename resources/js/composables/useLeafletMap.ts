import { ref, shallowRef, watch, onUnmounted, nextTick } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const TILE_URLS = {
    light: 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
    dark: 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',
};

export function useLeafletMap(options?: { center?: [number, number]; zoom?: number }) {
    const mapContainer = ref<HTMLElement | null>(null);
    const map = shallowRef<L.Map | null>(null);
    const isReady = ref(false);
    const isMapLoading = ref(true);
    const isDarkMap = ref(false);
    const currentZoom = ref(5);

    let tileLayer: L.TileLayer | null = null;
    let resizeObserver: ResizeObserver | null = null;
    const invalidateTimers: ReturnType<typeof setTimeout>[] = [];

    const createMap = (el: HTMLElement) => {
        // Already initialized
        if (map.value) return;

        // HMR safety: clean up leftover Leaflet instance
        if ((el as any)._leaflet_id) {
            try {
                const oldMap = (el as any)._leaflet;
                if (oldMap && typeof oldMap.remove === 'function') oldMap.remove();
            } catch (e) {}
            delete (el as any)._leaflet_id;
            // Clear child nodes left by old map
            while (el.firstChild) el.removeChild(el.firstChild);
        }

        isDarkMap.value = document.documentElement.classList.contains('dark');

        const leafletMap = L.map(el, {
            center: options?.center ?? [-2.5, 118],
            zoom: options?.zoom ?? 5,
            zoomControl: false,
            preferCanvas: true,
        });

        tileLayer = L.tileLayer(
            isDarkMap.value ? TILE_URLS.dark : TILE_URLS.light,
            { attribution: '&copy; <a href="https://carto.com">CARTO</a>', subdomains: 'abcd', maxZoom: 20 }
        ).addTo(leafletMap);

        L.control.zoom({ position: 'bottomright' }).addTo(leafletMap);

        leafletMap.on('zoomend', () => {
            currentZoom.value = leafletMap.getZoom();
        });

        map.value = leafletMap;

        leafletMap.whenReady(() => {
            leafletMap.invalidateSize();
            isReady.value = true;
            isMapLoading.value = false;
        });

        // Aggressive invalidateSize to handle CSS loading delays on hard refresh
        [50, 150, 400, 800, 1500, 3000].forEach(ms => {
            const t = setTimeout(() => {
                try {
                    leafletMap.invalidateSize();
                    // If map still not ready after 3s, force it
                    if (ms >= 1500 && !isReady.value) {
                        isReady.value = true;
                        isMapLoading.value = false;
                    }
                } catch(e) {}
            }, ms);
            invalidateTimers.push(t);
        });

        resizeObserver = new ResizeObserver(() => {
            try { leafletMap.invalidateSize(); } catch(e) {}
        });
        resizeObserver.observe(el);
    };

    // Watch the template ref — fires when DOM element becomes available
    // This works for BOTH Inertia navigation AND hard refresh
    watch(mapContainer, (el) => {
        if (el && !map.value) {
            // Small delay to ensure parent layout CSS is applied
            nextTick(() => {
                requestAnimationFrame(() => {
                    createMap(el);
                });
            });
            // Fallback: if rAF didn't fire (e.g., tab not visible)
            setTimeout(() => { if (!map.value && el) createMap(el); }, 200);
        }
    }, { immediate: true });

    const toggleDarkMode = () => {
        const m = map.value;
        if (!m || !tileLayer) return;
        isDarkMap.value = !isDarkMap.value;
        m.removeLayer(tileLayer);
        tileLayer = L.tileLayer(
            isDarkMap.value ? TILE_URLS.dark : TILE_URLS.light,
            { attribution: '&copy; <a href="https://carto.com">CARTO</a>', subdomains: 'abcd', maxZoom: 20 }
        ).addTo(m);
    };

    const destroy = () => {
        invalidateTimers.forEach(clearTimeout);
        invalidateTimers.length = 0;
        resizeObserver?.disconnect();
        resizeObserver = null;
        if (map.value) {
            try { map.value.remove(); } catch(e) {}
            map.value = null;
        }
        tileLayer = null;
        isReady.value = false;
        isMapLoading.value = true;
    };

    onUnmounted(destroy);

    return {
        mapContainer,
        map,
        isReady,
        isMapLoading,
        isDarkMap,
        currentZoom,
        toggleDarkMode,
    };
}
