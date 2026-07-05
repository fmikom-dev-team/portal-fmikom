<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";
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

type GeocodeAddress = Record<string, string | undefined>;

const props = withDefaults(
    defineProps<{
        provinceId?: number | null;
        cityId?: number | null;
        latitude?: number | null;
        longitude?: number | null;
        address?: string | null;
        provinces: Province[];
        cities: City[];
        label?: string;
        mapLabel?: string;
        provinceLabel?: string;
        cityLabel?: string;
        showMap?: boolean;
    }>(),
    {
        showMap: true,
        mapLabel: "Tandai Lokasi di Peta",
        provinceLabel: "Provinsi",
        cityLabel: "Kota/Kabupaten",
    },
);

const emit = defineEmits<{
    "update:provinceId": [value: number | null];
    "update:cityId": [value: number | null];
    "update:latitude": [value: number | null];
    "update:longitude": [value: number | null];
    "update:address": [value: string];
}>();

const ADDRESS_PRIORITY = [
    "road",
    "pedestrian",
    "hamlet",
    "village",
    "suburb",
    "city_district",
    "town",
    "city",
    "municipality",
    "county",
    "state_district",
    "state",
    "postcode",
    "country",
] as const;

const mapContainer = ref<HTMLDivElement | null>(null);
const isLocating = ref(false);
const isReadingAddress = ref(false);
const locationError = ref<string | null>(null);

let mapInstance: L.Map | null = null;
let marker: L.Marker | null = null;
let reverseGeocodeController: AbortController | null = null;

const markerIcon = L.divIcon({
    className: "custom-div-icon",
    html: '<div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-green-600 shadow-lg shadow-green-500/50"><div class="h-2.5 w-2.5 rounded-full bg-white animate-pulse"></div></div>',
    iconSize: [32, 32],
    iconAnchor: [16, 16],
});

const filteredCities = computed(() => {
    if (!props.provinceId) return [];
    return props.cities.filter((city) => city.provinsi_id === props.provinceId);
});

function onProvinceChange(event: Event) {
    const value = (event.target as HTMLSelectElement).value;
    emit("update:provinceId", value ? Number(value) : null);
    emit("update:cityId", null);
}

function onCityChange(event: Event) {
    const value = (event.target as HTMLSelectElement).value;
    emit("update:cityId", value ? Number(value) : null);
}

function normalizeName(value?: string | null) {
    return (value || "")
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/[.,]/g, "")
        .replace(/\s+/g, " ")
        .trim()
        .toLocaleUpperCase("id-ID");
}

function stripRegionPrefix(value: string) {
    return normalizeName(value).replace(/^(KOTA|KABUPATEN|PROVINSI)\s+/i, "");
}

function uniqueStrings(values: Array<string | undefined>) {
    return values
        .filter((value): value is string => Boolean(value?.trim()))
        .filter(
            (value, index, array) =>
                array.findIndex(
                    (candidate) =>
                        normalizeName(candidate) === normalizeName(value),
                ) === index,
        );
}

function formatAddress(displayName?: string | null, address?: GeocodeAddress) {
    if (!address) return displayName || "";

    return (
        uniqueStrings(ADDRESS_PRIORITY.map((key) => address[key])).join(", ") ||
        displayName ||
        ""
    );
}

function findProvince(address: GeocodeAddress) {
    const candidates = uniqueStrings([
        address.state,
        address.region,
        address.province,
        address.state_district,
    ]);

    return props.provinces.find((province) =>
        candidates.some(
            (candidate) =>
                normalizeName(province.name) === normalizeName(candidate) ||
                stripRegionPrefix(province.name) === stripRegionPrefix(candidate),
        ),
    );
}

function cityCandidates(address: GeocodeAddress) {
    const cityLike = uniqueStrings([
        address.city,
        address.town,
        address.municipality,
    ]);
    const regencyLike = uniqueStrings([address.county, address.state_district]);
    const generic = uniqueStrings([
        address.city_district,
        address.suburb,
        ...cityLike,
        ...regencyLike,
    ]);

    return uniqueStrings([
        ...cityLike.flatMap((value) => [value, `KOTA ${value}`]),
        ...regencyLike.flatMap((value) => [value, `KABUPATEN ${value}`]),
        ...generic,
    ]);
}

function findCity(address: GeocodeAddress, provinceId?: number | null) {
    const candidates = cityCandidates(address);
    const cities = provinceId
        ? props.cities.filter((city) => city.provinsi_id === provinceId)
        : props.cities;

    return (
        cities.find((city) =>
            candidates.some(
                (candidate) => normalizeName(city.name) === normalizeName(candidate),
            ),
        ) ||
        cities.find((city) =>
            candidates.some(
                (candidate) =>
                    stripRegionPrefix(city.name) === stripRegionPrefix(candidate),
            ),
        )
    );
}

async function reverseGeocode(latitude: number, longitude: number) {
    reverseGeocodeController?.abort();
    const controller = new AbortController();
    reverseGeocodeController = controller;
    isReadingAddress.value = true;
    locationError.value = null;

    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=jsonv2&addressdetails=1&accept-language=id,en&lat=${latitude}&lon=${longitude}`,
            {
                signal: controller.signal,
                headers: { Accept: "application/json" },
            },
        );

        if (!response.ok) {
            throw new Error("Gagal membaca alamat dari titik peta.");
        }

        const payload = await response.json();
        const address = (payload.address || {}) as GeocodeAddress;
        const province = findProvince(address);
        const city = findCity(address, province?.id || props.provinceId);
        const formattedAddress = formatAddress(payload.display_name, address);

        if (province) {
            emit("update:provinceId", province.id);
        }

        if (city) {
            emit("update:cityId", city.id);
        }

        if (formattedAddress) {
            emit("update:address", formattedAddress);
        }
    } catch (error) {
        if (error instanceof DOMException && error.name === "AbortError") {
            return;
        }

        locationError.value =
            error instanceof Error
                ? error.message
                : "Gagal membaca alamat dari titik peta.";
    } finally {
        if (reverseGeocodeController === controller) {
            isReadingAddress.value = false;
            reverseGeocodeController = null;
        }
    }
}

function moveMarkerTo(latitude: number, longitude: number) {
    if (!mapInstance || !marker) return;

    marker.setLatLng([latitude, longitude]);
    mapInstance.setView([latitude, longitude], 16);
    emit("update:latitude", latitude);
    emit("update:longitude", longitude);
    reverseGeocode(latitude, longitude);
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

function onMapLocationSelected(latitude: number, longitude: number) {
    if (!marker || !mapInstance) return;

    marker.setLatLng([latitude, longitude]);
    emit("update:latitude", latitude);
    emit("update:longitude", longitude);
    reverseGeocode(latitude, longitude);
}

function onMarkerDrag() {
    if (!marker) return;

    const position = marker.getLatLng();
    emit("update:latitude", position.lat);
    emit("update:longitude", position.lng);
    reverseGeocode(position.lat, position.lng);
}

function initMap() {
    if (!mapContainer.value || mapInstance) return;

    const latitude = props.latitude || -6.2;
    const longitude = props.longitude || 106.816;
    const isDark = document.documentElement.classList.contains("dark");

    mapInstance = L.map(mapContainer.value, {
        center: [latitude, longitude],
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

    marker = L.marker([latitude, longitude], {
        draggable: true,
        icon: markerIcon,
    }).addTo(mapInstance);

    marker.on("dragend", onMarkerDrag);

    mapInstance.on("click", (event: L.LeafletMouseEvent) => {
        onMapLocationSelected(event.latlng.lat, event.latlng.lng);
    });
}

function destroyMap() {
    reverseGeocodeController?.abort();

    if (mapInstance) {
        mapInstance.remove();
        mapInstance = null;
        marker = null;
    }
}

onMounted(() => {
    if (props.showMap) {
        nextTick(() =>
            setTimeout(() => {
                initMap();
                setTimeout(() => mapInstance?.invalidateSize(), 300);
            }, 500),
        );
    }
});

watch(
    () => props.showMap,
    (showMap) => {
        if (showMap) {
            nextTick(() => setTimeout(initMap, 100));
        } else {
            destroyMap();
        }
    },
);

watch(
    () => [props.latitude, props.longitude] as const,
    ([latitude, longitude]) => {
        if (!mapInstance || !marker || latitude === null || longitude === null) {
            return;
        }

        if (latitude === undefined || longitude === undefined) return;

        const currentPosition = marker.getLatLng();
        if (
            currentPosition.lat !== latitude ||
            currentPosition.lng !== longitude
        ) {
            marker.setLatLng([latitude, longitude]);
            mapInstance.setView([latitude, longitude], mapInstance.getZoom());
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
        >
            {{ label }}
        </label>

        <div v-if="showMap" class="relative">
            <label
                class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400"
            >
                {{ mapLabel }}
            </label>
            <div class="relative">
                <div
                    ref="mapContainer"
                    class="h-56 w-full overflow-hidden rounded-2xl border border-gray-200 shadow-sm dark:border-gray-700"
                ></div>

                <div
                    class="absolute bottom-2 left-2 z-[400] rounded-md bg-white/80 px-2 py-1 font-mono text-[10px] text-slate-500 backdrop-blur-sm dark:bg-slate-900/80 dark:text-slate-400"
                >
                    Lat: {{ latitude ? latitude.toFixed(5) : "-" }}, Lng:
                    {{ longitude ? longitude.toFixed(5) : "-" }}
                </div>

                <div class="absolute right-2 top-2 z-[400]">
                    <button
                        type="button"
                        :disabled="isLocating"
                        class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white/90 px-3 py-1.5 text-[11px] font-bold text-slate-700 shadow-md backdrop-blur-sm transition-all hover:border-green-400 hover:bg-green-50 hover:text-green-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900/90 dark:text-slate-300 dark:hover:bg-green-950/40 dark:hover:text-green-400"
                        @click="useMyLocation"
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

            <p
                v-if="isReadingAddress"
                class="mt-1 text-[11px] font-medium text-emerald-600 dark:text-emerald-400"
            >
                Membaca alamat dari titik peta...
            </p>

            <p
                v-if="locationError"
                class="mt-1 flex items-center gap-1.5 text-[11px] font-medium text-rose-500"
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

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label
                    class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400"
                >
                    {{ provinceLabel }}
                </label>
                <select
                    :value="provinceId"
                    :class="selectClass"
                    aria-label="Pilih provinsi"
                    @change="onProvinceChange"
                >
                    <option value="" :class="optionClass">Pilih Provinsi</option>
                    <option
                        v-for="province in provinces"
                        :key="province.id"
                        :value="province.id"
                        :class="optionClass"
                    >
                        {{ province.name }}
                    </option>
                </select>
            </div>

            <div>
                <label
                    class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400"
                >
                    {{ cityLabel }}
                </label>
                <select
                    :value="cityId"
                    :disabled="!provinceId"
                    :class="[selectClass, 'disabled:opacity-50']"
                    aria-label="Pilih kota/kabupaten"
                    @change="onCityChange"
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
        </div>
    </div>
</template>

<style>
.custom-div-icon {
    background: transparent;
    border: none;
}
</style>
