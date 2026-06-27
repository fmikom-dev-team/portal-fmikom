<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick, watch } from "vue";
import {
    AlertCircle,
    Edit3,
    Github,
    Globe,
    GraduationCap,
    Instagram,
    Linkedin,
    Mail,
    MapPin,
    Phone,
    Twitter,
    User,
} from "lucide-vue-next";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

const props = defineProps<{
    alumni: {
        user_id: number;
        name: string;
        email: string;
        nomor_induk: string;
        no_telepon: string | null;
        foto_path: string | null;
        banner_path: string | null;
        tahun_lulus: number | null;
        tanggal_lahir: string | null;
        program_studi_id: number | null;
        bio: string | null;
        website: string | null;
        linkedin: string | null;
        github: string | null;
        instagram: string | null;
        twitter: string | null;
        profil_id: number;
        angkatan: number | null;
        alamat_rumah: string | null;
        latitude_rumah: number | null;
        longitude_rumah: number | null;
        jenis_kelamin: "L" | "P" | null;
        nik: string | null;
        npwp: string | null;
        provinsi_id: number | null;
        kota_id: number | null;
        completeness_percentage: number;
    };
    programStudis: { id: number; nama: string; kode: string }[];
    provinsis: { id: number; name: string }[];
    kotas: { id: number; name: string; provinsi_id: number }[];
}>();

const emit = defineEmits<{
    (e: "edit"): void;
}>();

const mapContainerView = ref<HTMLElement | null>(null);
let mapView: L.Map | null = null;
let markerView: L.Marker | null = null;

const destroyMapView = () => {
    if (mapView) {
        mapView.remove();
        mapView = null;
        markerView = null;
    }
};

const initMapView = () => {
    if (!mapContainerView.value) return;
    if (mapView) return;

    const lat = props.alumni.latitude_rumah;
    const lng = props.alumni.longitude_rumah;

    if (lat === null || lng === null) return;

    mapView = L.map(mapContainerView.value, {
        center: [lat, lng],
        zoom: 13,
        zoomControl: false,
        dragging: false,
        touchZoom: false,
        scrollWheelZoom: false,
        doubleClickZoom: false,
        boxZoom: false,
    });

    L.tileLayer(
        "https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png",
        {
            attribution:
                '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: "abcd",
            maxZoom: 20,
        },
    ).addTo(mapView);

    const markerIcon = L.divIcon({
        className: "custom-div-icon",
        html: '<div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-green-600 shadow-lg shadow-green-500/50"><div class="h-2.5 w-2.5 rounded-full bg-white animate-pulse"></div></div>',
        iconSize: [32, 32],
        iconAnchor: [16, 16],
    });

    markerView = L.marker([lat, lng], {
        icon: markerIcon,
    }).addTo(mapView);
};

watch(
    () => [props.alumni.latitude_rumah, props.alumni.longitude_rumah],
    ([newLat, newLng]) => {
        if (newLat !== null && newLng !== null) {
            nextTick(() => {
                destroyMapView();
                initMapView();
            });
        } else {
            destroyMapView();
        }
    },
);

onMounted(() => {
    nextTick(() => {
        initMapView();
    });
});

onUnmounted(() => {
    destroyMapView();
});

const getProdiName = (id: number | null) => {
    if (!id) return "-";
    const prodi = props.programStudis.find((p) => p.id === id);
    return prodi ? prodi.nama : "-";
};

const getProvinsiName = (id: number | null) => {
    if (!id) return "-";
    const prov = props.provinsis.find((p) => p.id === id);
    return prov ? prov.name : "-";
};

const getKotaName = (id: number | null) => {
    if (!id) return "-";
    const city = props.kotas.find((c) => c.id === id);
    return city ? city.name : "-";
};

const getGenderLabel = (g: string | null) => {
    if (g === "L") return "Laki-laki";
    if (g === "P") return "Perempuan";
    return "-";
};
</script>

<template>
    <div class="space-y-6">
        <!-- ═══════════════════════ HEADER HERO CARD ═══════════════════════ -->
        <div
            class="relative bg-white dark:bg-slate-900 rounded-[24px] border border-slate-200/80 dark:border-slate-800 shadow-xs p-6 sm:p-8 overflow-hidden"
        >
            <div
                class="relative flex flex-col md:flex-row sm:items-center md:items-center justify-between gap-6"
            >
                <!-- User Info -->
                <div
                    class="flex flex-col sm:flex-row items-center gap-5 text-center sm:text-left"
                >
                    <!-- Avatar -->
                    <div
                        class="h-20 w-20 sm:h-24 sm:w-24 rounded-full border-2 border-white dark:border-slate-800 shadow-md overflow-hidden shrink-0 bg-green-50 dark:bg-slate-900 flex items-center justify-center"
                    >
                        <img
                            v-if="alumni.foto_path"
                            :src="
                                alumni.foto_path.startsWith('http')
                                    ? alumni.foto_path
                                    : `/storage/${alumni.foto_path}`
                            "
                            class="h-full w-full object-cover"
                            :alt="alumni.name"
                        />
                        <div
                            v-else
                            class="text-3xl font-black text-slate-700 dark:text-white"
                        >
                            {{
                                alumni.name
                                    ? alumni.name
                                          .split(" ")
                                          .map((n: string) => n[0])
                                          .slice(0, 2)
                                          .join("")
                                          .toUpperCase()
                                    : "A"
                            }}
                        </div>
                    </div>
                    <!-- Details -->
                    <div class="space-y-3 w-full sm:w-auto">
                        <h1
                            class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 dark:text-white uppercase"
                        >
                            {{ alumni.name }}
                        </h1>
                        <div
                            class="flex flex-col items-center sm:items-start gap-2.5"
                        >
                            <span
                                class="inline-flex items-center gap-1.5 font-bold text-slate-600 dark:text-white bg-slate-900 px-2.5 py-1 rounded-lg text-xs"
                            >
                                <GraduationCap class="w-3.5 h-3.5" />
                                {{ getProdiName(alumni.program_studi_id) }}
                            </span>

                            <div
                                class="grid grid-cols-3 gap-2 text-center sm:flex sm:flex-wrap sm:text-left sm:items-center sm:gap-x-3 sm:gap-y-1.5 text-xs text-slate-500 dark:text-slate-400 w-full sm:w-auto"
                            >
                                <div
                                    class="bg-slate-50 dark:bg-slate-800/40 rounded-xl p-2 sm:p-0 sm:bg-transparent sm:dark:bg-transparent flex flex-col sm:flex-row sm:gap-1"
                                >
                                    <span
                                        class="text-[9px] sm:text-xs font-bold text-slate-400 sm:text-slate-500 uppercase sm:normal-case"
                                        >NIM</span
                                    >
                                    <span
                                        class="font-extrabold text-slate-800 dark:text-slate-200 text-xs mt-0.5 sm:mt-0"
                                        >{{ alumni.nomor_induk || "-" }}</span
                                    >
                                </div>
                                <div
                                    class="bg-slate-50 dark:bg-slate-800/40 rounded-xl p-2 sm:p-0 sm:bg-transparent sm:dark:bg-transparent flex flex-col sm:flex-row sm:gap-1"
                                >
                                    <span
                                        class="text-[9px] sm:text-xs font-bold text-slate-400 sm:text-slate-500 uppercase sm:normal-case"
                                        >Angkatan</span
                                    >
                                    <span
                                        class="font-extrabold text-slate-800 dark:text-slate-200 text-xs mt-0.5 sm:mt-0"
                                        >{{ alumni.angkatan || "-" }}</span
                                    >
                                </div>
                                <div
                                    class="bg-slate-50 dark:bg-slate-800/40 rounded-xl p-2 sm:p-0 sm:bg-transparent sm:dark:bg-transparent flex flex-col sm:flex-row sm:gap-1"
                                >
                                    <span
                                        class="text-[9px] sm:text-xs font-bold text-slate-400 sm:text-slate-500 uppercase sm:normal-case"
                                        >Lulus</span
                                    >
                                    <span
                                        class="font-extrabold text-slate-800 dark:text-slate-200 text-xs mt-0.5 sm:mt-0"
                                        >{{ alumni.tahun_lulus || "-" }}</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action -->
                <div
                    class="w-full md:w-auto shrink-0 flex justify-center md:justify-end"
                >
                    <!-- Edit Profile Button -->
                    <button
                        @click="emit('edit')"
                        class="flex items-center justify-center gap-2 h-11 px-6 w-full sm:w-auto rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-950 text-sm font-bold transition-all shadow-xs cursor-pointer active:scale-95"
                    >
                        <Edit3 class="w-4 h-4" />
                        Edit Profil
                    </button>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════ DETAILED PROFILE INFO ═══════════════════════ -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Side: Personal & Academic Details (2 Cols on large screen) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Data Pribadi Card -->
                <div
                    class="bg-white dark:bg-slate-900 rounded-[20px] border border-slate-200/80 dark:border-slate-800 shadow-xs p-6"
                >
                    <div
                        class="flex items-center gap-2.5 pb-4 mb-6 border-b border-slate-100 dark:border-slate-850"
                    >
                        <div
                            class="p-2 rounded-xl bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400"
                        >
                            <User class="w-4 h-4" />
                        </div>
                        <h2
                            class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider"
                        >
                            Data Pribadi & Kontak
                        </h2>
                    </div>

                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4"
                    >
                        <div class="space-y-1">
                            <p
                                class="text-[10px] font-black text-slate-400 uppercase tracking-wider"
                            >
                                Nama Lengkap
                            </p>
                            <p
                                class="text-sm font-bold text-slate-800 dark:text-slate-200"
                            >
                                {{ alumni.name }}
                            </p>
                        </div>
                        <div class="space-y-1">
                            <p
                                class="text-[10px] font-black text-slate-400 uppercase tracking-wider"
                            >
                                Email (Akun)
                            </p>
                            <p
                                class="text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5"
                            >
                                <Mail class="w-3.5 h-3.5 text-slate-400" />
                                {{ alumni.email }}
                            </p>
                        </div>
                        <div class="space-y-1">
                            <p
                                class="text-[10px] font-black text-slate-400 uppercase tracking-wider"
                            >
                                Nomor Induk Mahasiswa (NIM)
                            </p>
                            <p
                                class="text-sm font-bold text-slate-800 dark:text-slate-200"
                            >
                                {{ alumni.nomor_induk || "-" }}
                            </p>
                        </div>
                        <div class="space-y-1">
                            <p
                                class="text-[10px] font-black text-slate-400 uppercase tracking-wider"
                            >
                                Nomor Induk Kependudukan (NIK)
                            </p>
                            <p
                                class="text-sm font-bold text-slate-800 dark:text-slate-200"
                            >
                                {{ alumni.nik || "-" }}
                            </p>
                        </div>
                        <div class="space-y-1">
                            <p
                                class="text-[10px] font-black text-slate-400 uppercase tracking-wider"
                            >
                                NPWP
                            </p>
                            <p
                                class="text-sm font-bold text-slate-800 dark:text-slate-200"
                            >
                                {{ alumni.npwp || "-" }}
                            </p>
                        </div>
                        <div class="space-y-1">
                            <p
                                class="text-[10px] font-black text-slate-400 uppercase tracking-wider"
                            >
                                Nomor HP / WhatsApp
                            </p>
                            <p
                                class="text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5"
                            >
                                <Phone class="w-3.5 h-3.5 text-slate-400" />
                                {{ alumni.no_telepon || "-" }}
                            </p>
                        </div>
                        <div class="space-y-1">
                            <p
                                class="text-[10px] font-black text-slate-400 uppercase tracking-wider"
                            >
                                Jenis Kelamin
                            </p>
                            <p
                                class="text-sm font-bold text-slate-800 dark:text-slate-200"
                            >
                                {{ getGenderLabel(alumni.jenis_kelamin) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Bio, Location & Media Sosial -->
            <div class="space-y-6">
                <!-- Media Sosial & Portofolio Card -->
                <div
                    class="bg-white dark:bg-slate-900 rounded-[20px] border border-slate-200/80 dark:border-slate-800 shadow-xs p-6"
                >
                    <div
                        class="flex items-center gap-2.5 pb-4 mb-6 border-b border-slate-100 dark:border-slate-850"
                    >
                        <div
                            class="p-2 rounded-xl bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400"
                        >
                            <Globe class="w-4 h-4" />
                        </div>
                        <h2
                            class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider"
                        >
                            Sosial Media & Bio
                        </h2>
                    </div>

                    <div class="space-y-4">
                        <!-- Bio -->
                        <div class="space-y-1">
                            <p
                                class="text-[10px] font-black text-slate-400 uppercase tracking-wider"
                            >
                                Bio
                            </p>
                            <p
                                class="text-xs font-semibold text-slate-600 dark:text-slate-350 leading-relaxed italic bg-slate-50/50 dark:bg-slate-950 p-3 rounded-xl border border-slate-100 dark:border-slate-850"
                            >
                                {{ alumni.bio || '"-"' }}
                            </p>
                        </div>

                        <!-- Links Grid -->
                        <div class="space-y-2.5 pt-2">
                            <!-- Website -->
                            <div
                                class="flex items-center justify-between text-xs"
                            >
                                <span
                                    class="text-slate-400 font-bold flex items-center gap-1.5"
                                >
                                    <Globe class="w-3.5 h-3.5" /> Website
                                </span>
                                <a
                                    v-if="alumni.website"
                                    :href="alumni.website"
                                    target="_blank"
                                    class="text-green-600 dark:text-green-400 hover:underline truncate max-w-[150px] font-bold"
                                    >{{
                                        alumni.website
                                            .replace("https://", "")
                                            .replace("http://", "")
                                    }}</a
                                >
                                <span
                                    v-else
                                    class="text-slate-400 font-semibold"
                                    >-</span
                                >
                            </div>
                            <!-- Linkedin -->
                            <div
                                class="flex items-center justify-between text-xs"
                            >
                                <span
                                    class="text-slate-400 font-bold flex items-center gap-1.5"
                                >
                                    <Linkedin class="w-3.5 h-3.5" /> LinkedIn
                                </span>
                                <a
                                    v-if="alumni.linkedin"
                                    :href="
                                        alumni.linkedin.startsWith('http')
                                            ? alumni.linkedin
                                            : `https://linkedin.com/in/${alumni.linkedin}`
                                    "
                                    target="_blank"
                                    class="text-green-600 dark:text-green-400 hover:underline truncate max-w-[150px] font-bold"
                                    >{{ alumni.linkedin }}</a
                                >
                                <span
                                    v-else
                                    class="text-slate-400 font-semibold"
                                    >-</span
                                >
                            </div>
                            <!-- Github -->
                            <div
                                class="flex items-center justify-between text-xs"
                            >
                                <span
                                    class="text-slate-400 font-bold flex items-center gap-1.5"
                                >
                                    <Github class="w-3.5 h-3.5" /> GitHub
                                </span>
                                <a
                                    v-if="alumni.github"
                                    :href="
                                        alumni.github.startsWith('http')
                                            ? alumni.github
                                            : `https://github.com/${alumni.github}`
                                    "
                                    target="_blank"
                                    class="text-green-600 dark:text-green-400 hover:underline truncate max-w-[150px] font-bold"
                                    >{{ alumni.github }}</a
                                >
                                <span
                                    v-else
                                    class="text-slate-400 font-semibold"
                                    >-</span
                                >
                            </div>
                            <!-- Instagram -->
                            <div
                                class="flex items-center justify-between text-xs"
                            >
                                <span
                                    class="text-slate-400 font-bold flex items-center gap-1.5"
                                >
                                    <Instagram class="w-3.5 h-3.5" /> Instagram
                                </span>
                                <a
                                    v-if="alumni.instagram"
                                    :href="
                                        alumni.instagram.startsWith('http')
                                            ? alumni.instagram
                                            : `https://instagram.com/${alumni.instagram}`
                                    "
                                    target="_blank"
                                    class="text-green-600 dark:text-green-400 hover:underline truncate max-w-[150px] font-bold"
                                    >{{ alumni.instagram }}</a
                                >
                                <span
                                    v-else
                                    class="text-slate-400 font-semibold"
                                    >-</span
                                >
                            </div>
                            <!-- Twitter -->
                            <div
                                class="flex items-center justify-between text-xs"
                            >
                                <span
                                    class="text-slate-400 font-bold flex items-center gap-1.5"
                                >
                                    <Twitter class="w-3.5 h-3.5" /> Twitter
                                </span>
                                <a
                                    v-if="alumni.twitter"
                                    :href="
                                        alumni.twitter.startsWith('http')
                                            ? alumni.twitter
                                            : `https://twitter.com/${alumni.twitter}`
                                    "
                                    target="_blank"
                                    class="text-green-600 dark:text-green-400 hover:underline truncate max-w-[150px] font-bold"
                                    >{{ alumni.twitter }}</a
                                >
                                <span
                                    v-else
                                    class="text-slate-400 font-semibold"
                                    >-</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════ DOMISILI & LOKASI PETA ═══════════════════════ -->
        <div
            class="bg-white dark:bg-slate-900 rounded-[20px] border border-slate-200/80 dark:border-slate-800 shadow-xs p-6"
        >
            <div
                class="flex items-center gap-2.5 pb-4 mb-6 border-b border-slate-100 dark:border-slate-850"
            >
                <div
                    class="p-2 rounded-xl bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400"
                >
                    <MapPin class="w-4 h-4" />
                </div>
                <h2
                    class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider"
                >
                    Lokasi Domisili
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Details -->
                <div class="md:col-span-1 space-y-4">
                    <div class="space-y-1">
                        <p
                            class="text-[10px] font-black text-slate-400 uppercase tracking-wider"
                        >
                            Provinsi
                        </p>
                        <p
                            class="text-sm font-bold text-slate-800 dark:text-slate-200"
                        >
                            {{ getProvinsiName(alumni.provinsi_id) }}
                        </p>
                    </div>
                    <div class="space-y-1">
                        <p
                            class="text-[10px] font-black text-slate-400 uppercase tracking-wider"
                        >
                            Kota / Kabupaten
                        </p>
                        <p
                            class="text-sm font-bold text-slate-800 dark:text-slate-200"
                        >
                            {{ getKotaName(alumni.kota_id) }}
                        </p>
                    </div>
                    <div class="space-y-1">
                        <p
                            class="text-[10px] font-black text-slate-400 uppercase tracking-wider"
                        >
                            Alamat Lengkap
                        </p>
                        <p
                            class="text-xs font-semibold text-slate-650 dark:text-slate-350 leading-relaxed"
                        >
                            {{ alumni.alamat_rumah || "-" }}
                        </p>
                    </div>
                </div>

                <!-- Map (Read-Only) -->
                <div class="md:col-span-2 space-y-2">
                    <template
                        v-if="
                            alumni.latitude_rumah !== null &&
                            alumni.longitude_rumah !== null
                        "
                    >
                        <div
                            ref="mapContainerView"
                            class="h-48 w-full rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-inner"
                            style="z-index: 10"
                        ></div>
                        <p
                            class="text-[10px] text-right font-mono text-slate-400"
                        >
                            Lat: {{ alumni.latitude_rumah.toFixed(5) }}, Lng:
                            {{ alumni.longitude_rumah.toFixed(5) }}
                        </p>
                    </template>
                    <div
                        v-else
                        class="flex flex-col items-center justify-center p-8 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 text-center h-48"
                    >
                        <AlertCircle
                            class="w-6 h-6 text-slate-400 mb-2 animate-bounce-subtle"
                        />
                        <p
                            class="text-xs font-bold text-slate-500 dark:text-slate-400"
                        >
                            Titik lokasi pada peta belum diatur.
                        </p>
                        <button
                            @click="emit('edit')"
                            class="text-[11px] text-green-600 dark:text-green-400 font-extrabold mt-1.5 hover:underline uppercase tracking-wider"
                        >
                            Atur Sekarang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
