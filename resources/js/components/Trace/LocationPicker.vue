<script setup lang="ts">
import { computed, watch, ref, nextTick, onMounted, onUnmounted } from "vue";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

interface Province {
    id: number;
    name: string;
}

interface City {
    id: number;
    name: string;
    provinsi_id: number;
}

const props = withDefaults(
    defineProps<{
        provinceId?: number | null;
        cityId?: number | null;
        latitude?: number | null;
        longitude?: number | null;
        provinces: Province[];
        cities: City[];
        label?: string;
        showMap?: boolean;
    }>(),
    {
        showMap: true,
    },
);

const emit = defineEmits<{
    "update:provinceId": [value: number | null];
    "update:cityId": [value: number | null];
    "update:latitude": [value: number | null];
    "update:longitude": [value: number | null];
}>();

const mapContainer = ref<HTMLDivElement | null>(null);
let mapInstance: L.Map | null = null;
let marker: L.Marker | null = null;

// Geolocation state
const isLocating = ref(false);
const locationError = ref<string | null>(null);

const markerIcon = L.divIcon({
    className: "custom-div-icon",
    html: '<div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-green-600 shadow-lg shadow-green-500/50"><div class="h-2.5 w-2.5 rounded-full bg-white animate-pulse"></div></div>',
    iconSize: [32, 32],
    iconAnchor: [16, 16],
});

const filteredCities = computed(() => {
    if (!props.provinceId) return [];
    return props.cities.filter((c) => c.provinsi_id === props.provinceId);
});

function onProvinceChange(e: Event) {
    const val = (e.target as HTMLSelectElement).value;
    emit("update:provinceId", val ? Number(val) : null);
    emit("update:cityId", null);
}

function onCityChange(e: Event) {
    const val = (e.target as HTMLSelectElement).value;
    emit("update:cityId", val ? Number(val) : null);
}

function moveMarkerTo(lat: number, lng: number) {
    if (!mapInstance || !marker) return;
    marker.setLatLng([lat, lng]);
    mapInstance.setView([lat, lng], 16);
    emit("update:latitude", lat);
    emit("update:longitude", lng);
}

function useMyLocation() {
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
                    locationError.value =
                        "Akses lokasi ditolak. Izinkan akses lokasi di browser.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    locationError.value = "Informasi lokasi tidak tersedia.";
                    break;
                case error.TIMEOUT:
                    locationError.value =
                        "Waktu permintaan lokasi habis, coba lagi.";
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
}

function initMap() {
    if (!mapContainer.value || mapInstance) return;

    const lat = props.latitude || -6.2;
    const lng = props.longitude || 106.816;
    const isDark = document.documentElement.classList.contains("dark");

    mapInstance = L.map(mapContainer.value, {
        center: [lat, lng],
        zoom: props.latitude ? 13 : 5,
        zoomControl: true,
    });

    const tileUrl = isDark
        ? "https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png"
        : "https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png";

    L.tileLayer(tileUrl, {
        attribution: '&copy; <a href="https://carto.com">CARTO</a>',
        subdomains: "abcd",
        maxZoom: 20,
    }).addTo(mapInstance);

    marker = L.marker([lat, lng], {
        draggable: true,
        icon: markerIcon,
    }).addTo(mapInstance);

    marker.on("dragend", onMarkerDrag);

    mapInstance.on("click", (e: L.LeafletMouseEvent) => {
        const { lat, lng } = e.latlng;
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], {
                draggable: true,
                icon: markerIcon,
            }).addTo(mapInstance!);
            marker.on("dragend", onMarkerDrag);
        }
        emit("update:latitude", lat);
        emit("update:longitude", lng);
    });
}

function onMarkerDrag() {
    if (!marker) return;
    const pos = marker.getLatLng();
    emit("update:latitude", pos.lat);
    emit("update:longitude", pos.lng);
}

function destroyMap() {
    if (mapInstance) {
        mapInstance.remove();
        mapInstance = null;
        marker = null;
    }
}

// Init map on mount
onMounted(() => {
    if (props.showMap) {
        nextTick(() =>
            setTimeout(() => {
                initMap();
                if (mapInstance) {
                    setTimeout(() => mapInstance?.invalidateSize(), 300);
                }
            }, 500),
        );
    }
});

// Handle dynamic show/hide
watch(
    () => props.showMap,
    (val) => {
        if (val) {
            nextTick(() => setTimeout(initMap, 100));
        } else {
            destroyMap();
        }
    },
);

onUnmounted(() => destroyMap());

const selectClass =
    "w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500";
const optionClass =
    "bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100";
</script>

<template>
    <div class="space-y-4">
        <label
            v-if="label"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >{{ label }}</label
        >

        <!-- Province Select -->
        <div>
            <label
                class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1"
                >Provinsi</label
            >
            <select
                :value="provinceId"
                @change="onProvinceChange"
                :class="selectClass"
                aria-label="Pilih provinsi"
            >
                <option value="" :class="optionClass">Pilih Provinsi</option>
                <option
                    v-for="prov in provinces"
                    :key="prov.id"
                    :value="prov.id"
                    :class="optionClass"
                >
                    {{ prov.name }}
                </option>
            </select>
        </div>

        <!-- City Select -->
        <div>
            <label
                class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1"
                >Kota/Kabupaten</label
            >
            <select
                :value="cityId"
                @change="onCityChange"
                :disabled="!provinceId"
                :class="[selectClass, 'disabled:opacity-50']"
                aria-label="Pilih kota/kabupaten"
            >
                <option value="" :class="optionClass">
                    Pilih Kota/Kabupaten
                </option>
                <option
                    v-for="city in filteredCities"
                    :key="city.id"
                    :value="city.id"
                    :class="optionClass"
                >
                    {{ city.name }}
                </option>
            </select>
        </div>

        <!-- Map Picker -->
        <div v-if="showMap" class="relative">
            <label
                class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1"
                >Tandai Lokasi di Peta</label
            >
            <div class="relative">
                <div
                    ref="mapContainer"
                    class="w-full h-56 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm"
                ></div>

                <!-- Coordinate badge -->
                <div
                    class="absolute bottom-2 left-2 z-[400] bg-white/80 dark:bg-slate-900/80 px-2 py-1 rounded-md text-[10px] font-mono text-slate-500 dark:text-slate-400 backdrop-blur-sm"
                >
                    Lat: {{ latitude ? latitude.toFixed(5) : "–" }}, Lng:
                    {{ longitude ? longitude.toFixed(5) : "–" }}
                </div>

                <!-- "Lokasi Saya" button -->
                <div class="absolute top-2 right-2 z-[400]">
                    <button
                        type="button"
                        @click="useMyLocation"
                        :disabled="isLocating"
                        class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white/90 px-3 py-1.5 text-[11px] font-bold text-slate-700 shadow-md backdrop-blur-sm transition-all hover:bg-green-50 hover:border-green-400 hover:text-green-700 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed dark:bg-slate-900/90 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-green-950/40 dark:hover:text-green-400"
                    >
                        <svg
                            v-if="isLocating"
                            class="h-3.5 w-3.5 animate-spin text-green-600"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            />
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                            />
                        </svg>
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
                class="flex items-center gap-1.5 mt-1 text-[11px] font-medium text-rose-500"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-3.5 w-3.5 shrink-0"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                {{ locationError }}
            </p>
        </div>
    </div>
</template>

<style>
.custom-div-icon {
    background: transparent;
    border: none;
}
</style>
