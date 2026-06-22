<script setup lang="ts">
import { computed, watch, ref, nextTick, onUnmounted } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

interface Province {
    id: number;
    name: string;
}

interface City {
    id: number;
    name: string;
    provinsi_id: number;
}

const props = defineProps<{
    provinceId?: number | null;
    cityId?: number | null;
    latitude?: number | null;
    longitude?: number | null;
    provinces: Province[];
    cities: City[];
    label?: string;
    showMap?: boolean;
}>();

const emit = defineEmits<{
    'update:provinceId': [value: number | null];
    'update:cityId': [value: number | null];
    'update:latitude': [value: number | null];
    'update:longitude': [value: number | null];
}>();

const mapContainer = ref<HTMLDivElement | null>(null);
let mapInstance: L.Map | null = null;
let marker: L.Marker | null = null;

const filteredCities = computed(() => {
    if (!props.provinceId) return [];
    return props.cities.filter((c) => c.provinsi_id === props.provinceId);
});

function onProvinceChange(e: Event) {
    const val = (e.target as HTMLSelectElement).value;
    emit('update:provinceId', val ? Number(val) : null);
    emit('update:cityId', null);
}

function onCityChange(e: Event) {
    const val = (e.target as HTMLSelectElement).value;
    emit('update:cityId', val ? Number(val) : null);
}

function initMap() {
    if (!mapContainer.value || mapInstance) return;

    const lat = props.latitude || -6.2;
    const lng = props.longitude || 106.816;

    mapInstance = L.map(mapContainer.value, {
        center: [lat, lng],
        zoom: props.latitude ? 13 : 5,
        zoomControl: true,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(mapInstance);

    if (props.latitude && props.longitude) {
        marker = L.marker([props.latitude, props.longitude], { draggable: true }).addTo(mapInstance);
        marker.on('dragend', onMarkerDrag);
    }

    mapInstance.on('click', (e: L.LeafletMouseEvent) => {
        const { lat, lng } = e.latlng;
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], { draggable: true }).addTo(mapInstance!);
            marker.on('dragend', onMarkerDrag);
        }
        emit('update:latitude', lat);
        emit('update:longitude', lng);
    });
}

function onMarkerDrag() {
    if (!marker) return;
    const pos = marker.getLatLng();
    emit('update:latitude', pos.lat);
    emit('update:longitude', pos.lng);
}

function destroyMap() {
    if (mapInstance) {
        mapInstance.remove();
        mapInstance = null;
        marker = null;
    }
}

watch(
    () => props.showMap,
    (val) => {
        if (val) {
            nextTick(() => setTimeout(initMap, 100));
        } else {
            destroyMap();
        }
    }
);

onUnmounted(() => destroyMap());
</script>

<template>
    <div class="space-y-4">
        <label v-if="label" class="block text-sm font-medium text-gray-300">{{ label }}</label>

        <!-- Province Select -->
        <div>
            <label class="block text-xs font-medium text-gray-400 mb-1">Provinsi</label>
            <select
                :value="provinceId"
                @change="onProvinceChange"
                class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                aria-label="Pilih provinsi"
            >
                <option value="" class="bg-gray-800">Pilih Provinsi</option>
                <option
                    v-for="prov in provinces"
                    :key="prov.id"
                    :value="prov.id"
                    class="bg-gray-800"
                >
                    {{ prov.name }}
                </option>
            </select>
        </div>

        <!-- City Select -->
        <div>
            <label class="block text-xs font-medium text-gray-400 mb-1">Kota/Kabupaten</label>
            <select
                :value="cityId"
                @change="onCityChange"
                :disabled="!provinceId"
                class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 disabled:opacity-50"
                aria-label="Pilih kota/kabupaten"
            >
                <option value="" class="bg-gray-800">Pilih Kota/Kabupaten</option>
                <option
                    v-for="city in filteredCities"
                    :key="city.id"
                    :value="city.id"
                    class="bg-gray-800"
                >
                    {{ city.name }}
                </option>
            </select>
        </div>

        <!-- Map Picker -->
        <div v-if="showMap !== false">
            <label class="block text-xs font-medium text-gray-400 mb-1">
                Tandai Lokasi di Peta
            </label>
            <div
                ref="mapContainer"
                class="w-full h-48 rounded-lg border border-white/10 overflow-hidden"
            ></div>
            <p v-if="latitude && longitude" class="mt-1 text-xs text-gray-500">
                {{ latitude?.toFixed(6) }}, {{ longitude?.toFixed(6) }}
            </p>
        </div>
    </div>
</template>
