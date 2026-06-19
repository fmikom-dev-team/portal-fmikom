<script setup lang="ts">
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { LoaderCircle, MapPinned, Search } from 'lucide-vue-next';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

import 'leaflet/dist/leaflet.css';

type SearchResult = {
    display_name: string;
    lat: string;
    lon: string;
    address?: Record<string, string | undefined>;
};

const props = defineProps<{
    latitude?: number | null;
    longitude?: number | null;
    address?: string | null;
}>();

const emit = defineEmits<{
    (e: 'update:latitude', value: number | null): void;
    (e: 'update:longitude', value: number | null): void;
    (e: 'update:address', value: string): void;
}>();

const mapElement = ref<HTMLElement | null>(null);
const search = ref(props.address ?? '');
const results = ref<SearchResult[]>([]);
const isSearching = ref(false);
const error = ref('');
const DEFAULT_CENTER: [number, number] = [-7.6998, 109.0187];
const DEFAULT_ZOOM = 6;

let leaflet: typeof import('leaflet') | null = null;
let map: import('leaflet').Map | null = null;
let marker: import('leaflet').Marker | null = null;
let markerIconInstance: import('leaflet').DivIcon | null = null;
let resizeObserver: ResizeObserver | null = null;
let searchController: AbortController | null = null;
let reverseGeocodeController: AbortController | null = null;

const ADDRESS_PRIORITY = [
    'road',
    'pedestrian',
    'hamlet',
    'village',
    'suburb',
    'city_district',
    'town',
    'city',
    'municipality',
    'county',
    'state_district',
    'state',
    'postcode',
    'country',
] as const;

const normalizeAddressSegment = (segment: string) => {
    const normalized = segment
        .replace(/\s+/g, ' ')
        .replace(/^Kabupaten\s+/i, '')
        .replace(/^Kota\s+/i, '')
        .trim();

    return normalized
        .replace(/\bCentral Java\b/gi, 'Jawa Tengah')
        .replace(/\bSpecial Region of Yogyakarta\b/gi, 'DI Yogyakarta');
};

const formatAddress = (
    displayName?: string | null,
    address?: Record<string, string | undefined> | null,
) => {
    if (address && Object.keys(address).length > 0) {
        const segments = ADDRESS_PRIORITY
            .map((key) => address[key])
            .filter((value): value is string => Boolean(value))
            .map(normalizeAddressSegment)
            .filter(Boolean);

        const uniqueSegments = segments.filter(
            (segment, index) =>
                segments.findIndex(
                    (candidate) =>
                        candidate.toLocaleLowerCase('id-ID') === segment.toLocaleLowerCase('id-ID'),
                ) === index,
        );

        if (uniqueSegments.length > 0) {
            return uniqueSegments.join(', ');
        }
    }

    return normalizeAddressSegment(displayName ?? '');
};

const clearMarker = () => {
    if (!map || !marker) {
        return;
    }

    map.removeLayer(marker);
    marker = null;
};

const reverseGeocode = async (latitude: number, longitude: number) => {
    reverseGeocodeController?.abort();
    reverseGeocodeController = new AbortController();

    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=jsonv2&accept-language=id,en&lat=${latitude}&lon=${longitude}`,
            {
                signal: reverseGeocodeController.signal,
                headers: {
                    Accept: 'application/json',
                },
            },
        );

        if (!response.ok) {
            throw new Error('Gagal membaca alamat dari titik peta.');
        }

        const payload = await response.json();
        const address = formatAddress(payload.display_name, payload.address);

        if (address) {
            error.value = '';
            results.value = [];
            emit('update:address', address);
            search.value = address;
        }
    } catch (err) {
        if (err instanceof DOMException && err.name === 'AbortError') {
            return;
        }

        error.value = err instanceof Error ? err.message : 'Terjadi kesalahan saat membaca alamat.';
    } finally {
        reverseGeocodeController = null;
    }
};

const setMarker = (latitude: number, longitude: number) => {
    if (!leaflet || !map) {
        return;
    }

    if (!marker) {
        marker = leaflet.marker([latitude, longitude], {
            draggable: true,
            icon: markerIconInstance ?? undefined,
        }).addTo(map);

        marker.on('dragend', () => {
            const next = marker?.getLatLng();

            if (!next) {
                return;
            }

            const latitude = Number(next.lat.toFixed(8));
            const longitude = Number(next.lng.toFixed(8));

            emit('update:latitude', latitude);
            emit('update:longitude', longitude);
            reverseGeocode(latitude, longitude);
        });
    } else {
        marker.setLatLng([latitude, longitude]);
    }

    map.setView([latitude, longitude], 16);
};

const syncMapSize = () => {
    if (!map) {
        return;
    }

    requestAnimationFrame(() => {
        map?.invalidateSize();
    });
};

const searchLocation = async () => {
    const query = search.value.trim();

    if (!query) {
        results.value = [];
        error.value = '';
        return;
    }

    search.value = query;
    isSearching.value = true;
    error.value = '';
    searchController?.abort();
    searchController = new AbortController();

    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/search?format=jsonv2&addressdetails=1&accept-language=id,en&limit=5&q=${encodeURIComponent(query)}`,
            {
                signal: searchController.signal,
                headers: {
                    Accept: 'application/json',
                },
            },
        );

        if (!response.ok) {
            throw new Error('Gagal mencari lokasi dari Nominatim.');
        }

        results.value = await response.json();

        if (results.value.length === 0) {
            error.value = 'Alamat tidak ditemukan. Coba gunakan kata kunci yang lebih spesifik.';
        }
    } catch (err) {
        if (err instanceof DOMException && err.name === 'AbortError') {
            return;
        }

        error.value = err instanceof Error ? err.message : 'Terjadi kesalahan saat mencari lokasi.';
    } finally {
        isSearching.value = false;
        searchController = null;
    }
};

const selectResult = (result: SearchResult) => {
    const latitude = Number(result.lat);
    const longitude = Number(result.lon);
    const formattedAddress = formatAddress(result.display_name, result.address);

    emit('update:latitude', latitude);
    emit('update:longitude', longitude);
    emit('update:address', formattedAddress);
    search.value = formattedAddress;
    results.value = [];
    error.value = '';

    setMarker(latitude, longitude);
};

onMounted(async () => {
    const L = await import('leaflet');
    leaflet = L;

    markerIconInstance = L.divIcon({
        className: 'company-location-marker',
        html: '<span class="company-location-marker__pin"></span>',
        iconSize: [28, 40],
        iconAnchor: [14, 40],
        popupAnchor: [0, -32],
    });

    if (!mapElement.value) {
        return;
    }

    map = L.map(mapElement.value, {
        zoomControl: true,
    }).setView(
        [
            props.latitude ?? DEFAULT_CENTER[0],
            props.longitude ?? DEFAULT_CENTER[1],
        ],
        props.latitude && props.longitude ? 16 : DEFAULT_ZOOM,
    );

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    map.on('click', (event: import('leaflet').LeafletMouseEvent) => {
        const latitude = Number(event.latlng.lat.toFixed(8));
        const longitude = Number(event.latlng.lng.toFixed(8));

        emit('update:latitude', latitude);
        emit('update:longitude', longitude);
        results.value = [];
        setMarker(latitude, longitude);
        reverseGeocode(latitude, longitude);
    });

    if (props.latitude !== null && props.latitude !== undefined && props.longitude !== null && props.longitude !== undefined) {
        setMarker(props.latitude, props.longitude);
    }

    await nextTick();
    syncMapSize();

    if (mapElement.value && typeof ResizeObserver !== 'undefined') {
        resizeObserver = new ResizeObserver(() => {
            syncMapSize();
        });

        resizeObserver.observe(mapElement.value);
    }
});

onBeforeUnmount(() => {
    searchController?.abort();
    reverseGeocodeController?.abort();
    resizeObserver?.disconnect();
    resizeObserver = null;
    searchController = null;
    reverseGeocodeController = null;
    map?.remove();
    map = null;
    marker = null;
});

watch(
    () => [props.latitude, props.longitude] as const,
    ([latitude, longitude]) => {
        if (latitude === null || latitude === undefined || longitude === null || longitude === undefined) {
            clearMarker();
            map?.setView(DEFAULT_CENTER, DEFAULT_ZOOM);
            syncMapSize();
            return;
        }

        setMarker(latitude, longitude);
        syncMapSize();
    },
);

watch(
    () => props.address,
    (value) => {
        search.value = value ?? '';
        results.value = [];
        error.value = '';
    },
);
</script>

<template>
    <div class="space-y-4">
        <div class="flex flex-col gap-3 sm:flex-row">
            <div class="relative flex-1">
                <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <Input
                    v-model="search"
                    type="text"
                    placeholder="Cari alamat perusahaan..."
                    class="h-11 rounded-2xl border-slate-200 bg-white pl-10"
                    @keydown.enter.prevent="searchLocation"
                />
            </div>

            <Button
                type="button"
                class="h-11 rounded-2xl bg-[#1554D1] px-5 text-white hover:bg-[#1449b8]"
                :disabled="isSearching"
                @click="searchLocation"
            >
                <LoaderCircle v-if="isSearching" class="size-4 animate-spin" />
                <MapPinned v-else class="size-4" />
                Cari Lokasi
            </Button>
        </div>

        <Alert v-if="error" variant="destructive" class="border-rose-200 bg-rose-50 text-rose-700">
            <AlertTitle>Pencarian lokasi gagal</AlertTitle>
            <AlertDescription>{{ error }}</AlertDescription>
        </Alert>

        <div
            v-if="results.length"
            class="space-y-2 rounded-3xl border border-slate-200 bg-slate-50 p-3"
        >
            <button
                v-for="result in results"
                :key="`${result.lat}-${result.lon}`"
                type="button"
                class="w-full rounded-2xl bg-white px-4 py-3 text-left text-sm text-slate-600 ring-1 ring-slate-200 transition hover:text-slate-900"
                @click="selectResult(result)"
            >
                {{ formatAddress(result.display_name, result.address) }}
            </button>
        </div>

        <div
            ref="mapElement"
            class="h-64 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 sm:h-72"
        />

        <div class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-xs font-medium text-slate-500">Latitude</p>
                <p class="mt-2 text-sm font-medium text-slate-900">{{ latitude ?? '-' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-xs font-medium text-slate-500">Longitude</p>
                <p class="mt-2 text-sm font-medium text-slate-900">{{ longitude ?? '-' }}</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
:deep(.company-location-marker) {
    background: transparent;
    border: 0;
}

:deep(.company-location-marker__pin) {
    position: relative;
    display: block;
    width: 28px;
    height: 28px;
    border-radius: 9999px 9999px 9999px 0;
    transform: rotate(-45deg);
    background: linear-gradient(180deg, #2563eb 0%, #1554d1 100%);
    box-shadow:
        0 12px 24px -10px rgba(37, 99, 235, 0.6),
        0 4px 10px rgba(15, 23, 42, 0.18);
}

:deep(.company-location-marker__pin::before) {
    content: '';
    position: absolute;
    inset: 7px;
    border-radius: 9999px;
    background: #ffffff;
}
</style>
