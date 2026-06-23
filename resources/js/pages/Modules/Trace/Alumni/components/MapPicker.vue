<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, nextTick } from "vue";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

const props = withDefaults(
    defineProps<{
        latitude: number | null;
        longitude: number | null;
        height?: string;
    }>(),
    {
        height: "280px",
    },
);

const emit = defineEmits<{
    (e: "update:location", data: { lat: number; lng: number }): void;
}>();

const mapContainer = ref<HTMLElement | null>(null);
let map: L.Map | null = null;
let marker: L.Marker | null = null;

// Geolocation state
const isLocating = ref(false);
const locationError = ref<string | null>(null);

const defaultLat = -6.2088; // Jakarta default
const defaultLng = 106.8456;

const destroyMap = () => {
    if (map) {
        map.remove();
        map = null;
        marker = null;
    }
};

const markerIcon = L.divIcon({
    className: "custom-div-icon",
    html: '<div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-green-600 shadow-lg shadow-green-500/50"><div class="h-2.5 w-2.5 rounded-full bg-white animate-pulse"></div></div>',
    iconSize: [32, 32],
    iconAnchor: [16, 16],
});

const moveMarkerTo = (lat: number, lng: number) => {
    if (!map || !marker) return;
    marker.setLatLng([lat, lng]);
    map.setView([lat, lng], 16);
    emit("update:location", { lat, lng });
};

// "Gunakan Lokasi Saya" handler
const useMyLocation = () => {
    if (!navigator.geolocation) {
        locationError.value = "Browser tidak mendukung fitur lokasi.";
        return;
    }

    isLocating.value = true;
    locationError.value = null;

    navigator.geolocation.getCurrentPosition(
        (position) => {
            const { latitude, longitude } = position.coords;
            moveMarkerTo(latitude, longitude);
            isLocating.value = false;
        },
        (error) => {
            isLocating.value = false;
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    locationError.value = "Akses lokasi ditolak. Izinkan akses lokasi di browser.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    locationError.value = "Informasi lokasi tidak tersedia.";
                    break;
                case error.TIMEOUT:
                    locationError.value = "Waktu permintaan lokasi habis, coba lagi.";
                    break;
                default:
                    locationError.value = "Gagal mendapatkan lokasi.";
            }
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0,
        },
    );
};

const initMap = () => {
    if (!mapContainer.value) return;
    if (map) return;

    const lat = props.latitude || defaultLat;
    const lng = props.longitude || defaultLng;

    map = L.map(mapContainer.value, {
        center: [lat, lng],
        zoom: 13,
        zoomControl: true,
    });

    L.tileLayer("https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png", {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: "abcd",
        maxZoom: 20,
    }).addTo(map);

    marker = L.marker([lat, lng], {
        draggable: true,
        icon: markerIcon,
    }).addTo(map);

    marker.on("dragend", () => {
        if (marker) {
            const position = marker.getLatLng();
            emit("update:location", { lat: position.lat, lng: position.lng });
        }
    });

    map.on("click", (e) => {
        if (marker) {
            marker.setLatLng(e.latlng);
            emit("update:location", { lat: e.latlng.lat, lng: e.latlng.lng });
        }
    });

    // If initial location was null, emit default coordinates
    if (props.latitude === null || props.longitude === null) {
        emit("update:location", { lat, lng });
    }
};

// Re-center map if coordinates change externally
watch(
    () => [props.latitude, props.longitude],
    ([newLat, newLng]) => {
        if (map && marker && newLat !== null && newLng !== null) {
            const currentCenter = marker.getLatLng();
            if (currentCenter.lat !== newLat || currentCenter.lng !== newLng) {
                marker.setLatLng([newLat, newLng]);
                map.setView([newLat, newLng], map.getZoom());
            }
        }
    },
);

onMounted(() => {
    nextTick(() => {
        initMap();
    });
});

onUnmounted(() => {
    destroyMap();
});
</script>

<template>
    <div class="relative w-full space-y-2">
        <!-- Map Container -->
        <div class="relative w-full">
            <div
                ref="mapContainer"
                class="w-full rounded-2xl border border-slate-200/80 shadow-sm dark:border-slate-800"
                :style="{ height: height }"
            ></div>

            <!-- Coordinate badge -->
            <div
                class="absolute bottom-2 left-2 z-[400] bg-white/80 dark:bg-slate-900/80 px-2 py-1 rounded-md text-[10px] font-mono text-slate-500 dark:text-slate-400 backdrop-blur-xs"
            >
                Lat: {{ latitude ? latitude.toFixed(5) : "–" }}, Lng:
                {{ longitude ? longitude.toFixed(5) : "–" }}
            </div>

            <!-- "Gunakan Lokasi Saya" button — overlaid top-right inside map -->
            <div class="absolute top-2 right-2 z-[400]">
                <button
                    type="button"
                    @click="useMyLocation"
                    :disabled="isLocating"
                    class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white/90 px-3 py-1.5 text-[11px] font-bold text-slate-700 shadow-md backdrop-blur-sm transition-all hover:bg-green-50 hover:border-green-400 hover:text-green-700 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed dark:bg-slate-900/90 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-green-950/40 dark:hover:text-green-400"
                >
                    <!-- Spinner saat loading -->
                    <svg
                        v-if="isLocating"
                        class="h-3.5 w-3.5 animate-spin text-green-600"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    <!-- Icon lokasi saat idle -->
                    <svg
                        v-else
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-3.5 w-3.5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <circle cx="12" cy="12" r="3" />
                        <path d="M12 2v3M12 19v3M2 12h3M19 12h3" />
                        <path d="M12 8a4 4 0 100 8 4 4 0 000-8z" />
                    </svg>
                    {{ isLocating ? "Mendeteksi..." : "Lokasi Saya" }}
                </button>
            </div>
        </div>

        <!-- Error message -->
        <p
            v-if="locationError"
            class="flex items-center gap-1.5 text-[11px] font-medium text-rose-500"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" /><line x1="12" y1="8" x2="12" y2="12" /><line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
            {{ locationError }}
        </p>
    </div>
</template>

<style>
/* Leaflet marker container overrides */
.leaflet-container {
    font-family: inherit;
}
.custom-div-icon {
    background: transparent;
    border: none;
}
</style>
