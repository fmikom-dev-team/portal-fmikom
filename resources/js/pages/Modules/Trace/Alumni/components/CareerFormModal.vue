<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import L from "leaflet";
import { X, MapPin } from "lucide-vue-next";
import { watch, computed, ref, nextTick, onUnmounted } from "vue";
import { toast } from "vue-sonner";
import "leaflet/dist/leaflet.css";

interface Career {
    id: number;
    status: string;
    // Work/Business fields
    nama_perusahaan?: string;
    jabatan?: string;
    sektor_industri?: string;
    alamat_perusahaan?: string;
    gaji_min?: number;
    gaji_max?: number;
    // Study fields
    nama_universitas?: string;
    program_studi_lanjutan?: string;
    jenjang_pendidikan?: string;
    sumber_biaya?: string;
    alamat_universitas?: string;
    // Location fields
    provinsi_id?: number;
    kota_id?: number;
    latitude?: number;
    longitude?: number;
    // Date fields
    tanggal_mulai?: string;
    tanggal_selesai?: string;
    is_current: boolean;
}

const props = defineProps<{
    show: boolean;
    career?: Career | null;
    provinces: any[];
    cities: any[];
}>();

const emit = defineEmits<{
    "update:show": [value: boolean];
    close: [];
}>();

const form = useForm({
    status: "bekerja",
    // Work/Business fields
    nama_perusahaan: "",
    jabatan: "",
    sektor_industri: "",
    alamat_perusahaan: "",
    gaji_min: null as number | null,
    gaji_max: null as number | null,
    // Study fields
    nama_universitas: "",
    program_studi_lanjutan: "",
    jenjang_pendidikan: "",
    sumber_biaya: "",
    alamat_universitas: "",
    // Location fields
    provinsi_id: null as number | null,
    kota_id: null as number | null,
    latitude: null as number | null,
    longitude: null as number | null,
    // Date fields
    tanggal_mulai: "",
    tanggal_selesai: "",
    is_current: false,
});

// Map Picker
const mapContainer = ref<HTMLElement | null>(null);
let map: L.Map | null = null;
let marker: L.Marker | null = null;

const isEditing = computed(() => !!props.career);

const filteredCities = computed(() => {
    if (!form.provinsi_id) {
        return [];
    }

    return props.cities.filter((city) => city.provinsi_id === form.provinsi_id);
});

const destroyMap = () => {
    if (map) {
        map.remove();
        map = null;
        marker = null;
    }
};

const initMap = () => {
    nextTick(() => {
        if (!mapContainer.value) {
            return;
        }

        if (map) {
            return;
        }

        const defaultLat = form.latitude || -6.2088;
        const defaultLng = form.longitude || 106.8456;

        map = L.map(mapContainer.value, {
            center: [defaultLat, defaultLng],
            zoom: 13,
            zoomControl: true,
        });

        L.tileLayer(
            "https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png",
            {
                attribution: "&copy; CARTO",
                subdomains: "abcd",
                maxZoom: 20,
            },
        ).addTo(map);

        const iconColor = form.status === "lanjut_studi" ? "purple" : "blue";
        marker = L.marker([defaultLat, defaultLng], {
            draggable: true,
            icon: L.divIcon({
                className: "custom-div-icon",
                html: `<div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-${iconColor}-600 shadow-lg shadow-${iconColor}-500/50"><div class="h-2 w-2 rounded-full bg-white"></div></div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 16],
            }),
        }).addTo(map);

        marker.on("dragend", () => {
            const position = marker!.getLatLng();
            form.latitude = position.lat;
            form.longitude = position.lng;
        });

        map.on("click", (e) => {
            marker!.setLatLng(e.latlng);
            form.latitude = e.latlng.lat;
            form.longitude = e.latlng.lng;
        });

        if (!form.latitude || !form.longitude) {
            form.latitude = defaultLat;
            form.longitude = defaultLng;
        }
    });
};

watch(
    () => form.is_current,
    (isCurrent) => {
        if (isCurrent) {
            form.tanggal_selesai = "";
        }
    },
);

// Only reset kota when provinsi changes manually (not during initial load)
let isInitialLoad = true;
watch(
    () => form.provinsi_id,
    () => {
        if (!isInitialLoad) {
            form.kota_id = null;
        }

        isInitialLoad = false;
    },
);

watch(
    () => form.status,
    (newStatus) => {
        destroyMap();

        if (["bekerja", "wirausaha", "lanjut_studi"].includes(newStatus)) {
            initMap();
        }
    },
    { immediate: true },
);

watch(
    () => props.show,
    (isShown) => {
        if (isShown) {
            isInitialLoad = true;
            if (props.career) {
                const newCareer = props.career;
                form.status = newCareer.status;
                // Work/Business fields
                form.nama_perusahaan = newCareer.nama_perusahaan || "";
                form.jabatan = newCareer.jabatan || "";
                form.sektor_industri = newCareer.sektor_industri || "";
                form.alamat_perusahaan = newCareer.alamat_perusahaan || "";
                form.gaji_min = newCareer.gaji_min || null;
                form.gaji_max = newCareer.gaji_max || null;
                // Study fields
                form.nama_universitas = newCareer.nama_universitas || "";
                form.program_studi_lanjutan =
                    newCareer.program_studi_lanjutan || "";
                form.jenjang_pendidikan = newCareer.jenjang_pendidikan || "";
                form.sumber_biaya = newCareer.sumber_biaya || "";
                form.alamat_universitas = newCareer.alamat_universitas || "";
                // Location fields - set provinsi first, then kota
                form.provinsi_id = newCareer.provinsi_id || null;
                nextTick(() => {
                    form.kota_id = newCareer.kota_id || null;
                });
                form.latitude = newCareer.latitude || null;
                form.longitude = newCareer.longitude || null;
                // Date fields
                form.tanggal_mulai = newCareer.tanggal_mulai
                    ? newCareer.tanggal_mulai.substring(0, 10)
                    : "";
                form.tanggal_selesai = newCareer.tanggal_selesai
                    ? newCareer.tanggal_selesai.substring(0, 10)
                    : "";
                form.is_current = !!newCareer.is_current;
            } else {
                form.reset();
            }

            if (
                ["bekerja", "wirausaha", "lanjut_studi"].includes(form.status)
            ) {
                setTimeout(() => initMap(), 100);
            }
        } else {
            destroyMap();
        }
    },
    { immediate: true },
);

onUnmounted(() => {
    destroyMap();
});

const close = () => {
    destroyMap();
    emit("update:show", false);
    emit("close");
    form.reset();
    form.clearErrors();
};

const submit = () => {
    // Clean up data before submit based on status
    if (form.status === "mencari_kerja") {
        // Clear all work and university fields for mencari_kerja
        form.nama_perusahaan = "";
        form.jabatan = "";
        form.sektor_industri = "";
        form.alamat_perusahaan = "";
        form.gaji_min = null;
        form.gaji_max = null;
        form.nama_universitas = "";
        form.program_studi_lanjutan = "";
        form.jenjang_pendidikan = "";
        form.sumber_biaya = "";
        form.alamat_universitas = "";
    } else if (form.status === "lanjut_studi") {
        // Clear work fields for lanjut_studi
        form.nama_perusahaan = "";
        form.jabatan = "";
        form.sektor_industri = "";
        form.alamat_perusahaan = "";
        form.gaji_min = null;
        form.gaji_max = null;
    } else if (["bekerja", "wirausaha"].includes(form.status)) {
        // Clear university fields for bekerja/wirausaha
        form.nama_universitas = "";
        form.program_studi_lanjutan = "";
        form.jenjang_pendidikan = "";
        form.sumber_biaya = "";
        form.alamat_universitas = "";
    }

    // Force clear tanggal_selesai if is_current is true
    if (form.is_current) {
        form.tanggal_selesai = "";
    }

    const url = isEditing.value
        ? `/trace/career/${props.career!.id}`
        : "/trace/career";

    const method = isEditing.value ? "put" : "post";

    form[method](url, {
        preserveScroll: true,
        onSuccess: () => {
            close();
        },
        onError: (errors) => {
            console.error("Validation errors:", errors);
            toast.error("Gagal menyimpan riwayat karir");
        },
    });
};

const needsCompanyInfo = computed(() => {
    return ["bekerja", "wirausaha"].includes(form.status);
});

const needsUniversityInfo = computed(() => {
    return form.status === "lanjut_studi";
});

// Date validation: tanggal_selesai cannot be before tanggal_mulai
const dateError = computed(() => {
    if (!form.is_current && form.tanggal_mulai && form.tanggal_selesai) {
        const mulai = new Date(form.tanggal_mulai);
        const selesai = new Date(form.tanggal_selesai);

        if (selesai < mulai) {
            return "Tanggal selesai tidak boleh lebih awal dari tanggal mulai.";
        }
    }

    return null;
});

// Salary validation: gaji_max cannot be less than gaji_min
const salaryError = computed(() => {
    if (
        form.gaji_min !== null &&
        form.gaji_max !== null &&
        form.gaji_max < form.gaji_min
    ) {
        return "Gaji maksimum tidak boleh lebih kecil dari gaji minimum.";
    }

    return null;
});
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="close"
        >
            <div
                class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-xl bg-white dark:bg-slate-900"
            >
                <!-- Header -->
                <div
                    class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        {{
                            isEditing
                                ? "Edit Riwayat Karir"
                                : "Tambah Riwayat Karir"
                        }}
                    </h2>
                    <button
                        @click="close"
                        class="rounded-lg p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-slate-800 dark:hover:text-gray-300"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Status -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.status"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            required
                        >
                            <option value="bekerja">
                                Bekerja (Full-time/Part-time)
                            </option>
                            <option value="wirausaha">Wirausaha</option>
                            <option value="mencari_kerja">Tidak Bekerja</option>
                            <option value="lanjut_studi">Lanjut Studi</option>
                        </select>
                        <p
                            v-if="form.errors.status"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.status }}
                        </p>
                    </div>

                    <!-- Company Info (only for bekerja/wirausaha) -->
                    <template v-if="needsCompanyInfo">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Nama Perusahaan -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Nama Perusahaan
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.nama_perusahaan"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    required
                                />
                                <p
                                    v-if="form.errors.nama_perusahaan"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.nama_perusahaan }}
                                </p>
                            </div>

                            <!-- Jabatan -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Jabatan <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.jabatan"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    required
                                />
                                <p
                                    v-if="form.errors.jabatan"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.jabatan }}
                                </p>
                            </div>
                        </div>

                        <!-- Sektor Industri -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Sektor Industri
                            </label>
                            <select
                                v-model="form.sektor_industri"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            >
                                <option value="" disabled>Pilih Sektor Industri</option>
                                <option value="Teknologi Informasi">Teknologi Informasi</option>
                                <option value="Pendidikan">Pendidikan</option>
                                <option value="Keuangan & Perbankan">Keuangan & Perbankan</option>
                                <option value="Kesehatan">Kesehatan</option>
                                <option value="Manufaktur">Manufaktur</option>
                                <option value="Pemerintahan">Pemerintahan</option>
                                <option value="Perdagangan & E-Commerce">Perdagangan & E-Commerce</option>
                                <option value="Telekomunikasi">Telekomunikasi</option>
                                <option value="Konstruksi & Properti">Konstruksi & Properti</option>
                                <option value="Transportasi & Logistik">Transportasi & Logistik</option>
                                <option value="Media & Kreatif">Media & Kreatif</option>
                                <option value="Pertanian & Pangan">Pertanian & Pangan</option>
                                <option value="Energi & Pertambangan">Energi & Pertambangan</option>
                                <option value="Pariwisata & Hospitality">Pariwisata & Hospitality</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                            <p
                                v-if="form.errors.sektor_industri"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.sektor_industri }}
                            </p>
                        </div>

                        <!-- Location -->
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Provinsi -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Provinsi
                                </label>
                                <select
                                    v-model="form.provinsi_id"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                >
                                    <option :value="null">
                                        Pilih Provinsi
                                    </option>
                                    <option
                                        v-for="prov in provinces"
                                        :key="prov.id"
                                        :value="prov.id"
                                    >
                                        {{ prov.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Kota -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Kota/Kabupaten
                                </label>
                                <select
                                    v-model="form.kota_id"
                                    :disabled="!form.provinsi_id"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 disabled:bg-gray-100 disabled:cursor-not-allowed dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:disabled:bg-slate-900"
                                >
                                    <option :value="null">Pilih Kota</option>
                                    <option
                                        v-for="city in filteredCities"
                                        :key="city.id"
                                        :value="city.id"
                                    >
                                        {{ city.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Alamat -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Alamat Lengkap Perusahaan/Usaha
                            </label>
                            <textarea
                                v-model="form.alamat_perusahaan"
                                rows="3"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                placeholder="Alamat lengkap perusahaan/usaha"
                            />
                        </div>

                        <!-- Map Picker -->
                        <div>
                            <label
                                class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                <MapPin class="h-4 w-4 text-blue-500" /> Lokasi
                                Perusahaan/Usaha pada Peta (Klik / Geser Pin)
                            </label>
                            <div
                                ref="mapContainer"
                                class="h-64 w-full rounded-lg border border-gray-200 shadow-sm dark:border-slate-800"
                                style="z-index: 10"
                            ></div>
                            <div
                                class="mt-2 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400"
                            >
                                <span
                                    >Pilih lokasi perusahaan/usaha untuk
                                    pemetaan sebaran alumni (WebGIS).</span
                                >
                                <span class="font-mono"
                                    >Lat:
                                    {{
                                        form.latitude
                                            ? Number(form.latitude).toFixed(5)
                                            : "-"
                                    }}, Lng:
                                    {{
                                        form.longitude
                                            ? Number(form.longitude).toFixed(5)
                                            : "-"
                                    }}</span
                                >
                            </div>
                        </div>

                        <!-- Gaji Range -->
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Gaji Minimum (Rp)
                                </label>
                                <input
                                    v-model.number="form.gaji_min"
                                    type="number"
                                    min="0"
                                    step="100000"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    placeholder="0"
                                />
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Gaji Maximum (Rp)
                                </label>
                                <input
                                    v-model.number="form.gaji_max"
                                    type="number"
                                    min="0"
                                    step="100000"
                                    :class="[
                                        'w-full rounded-lg border px-4 py-2.5 text-gray-900 focus:ring-2 dark:bg-slate-800 dark:text-white',
                                        salaryError
                                            ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
                                            : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500/20 dark:border-slate-700',
                                    ]"
                                    placeholder="0"
                                />
                                <p
                                    v-if="salaryError"
                                    class="mt-1 flex items-center gap-1 text-sm text-red-600"
                                >
                                    <svg
                                        class="h-4 w-4 shrink-0"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    {{ salaryError }}
                                </p>
                                <p
                                    v-else-if="form.errors.gaji_max"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.gaji_max }}
                                </p>
                            </div>
                        </div>
                    </template>

                    <!-- University Info (only for lanjut_studi) -->
                    <template v-if="needsUniversityInfo">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Nama Universitas -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Nama Perguruan Tinggi / Universitas
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.nama_universitas"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    placeholder="Contoh: Universitas Indonesia"
                                    required
                                />
                                <p
                                    v-if="form.errors.nama_universitas"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.nama_universitas }}
                                </p>
                            </div>

                            <!-- Program Studi Lanjutan -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Program Studi Lanjutan
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.program_studi_lanjutan"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    placeholder="Contoh: Magister Teknik Informatika"
                                    required
                                />
                                <p
                                    v-if="form.errors.program_studi_lanjutan"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.program_studi_lanjutan }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Jenjang Pendidikan -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Jenjang Pendidikan
                                    <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.jenjang_pendidikan"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    required
                                >
                                    <option value="">Pilih Jenjang</option>
                                    <option value="S2">S2 (Magister)</option>
                                    <option value="S3">S3 (Doktor)</option>
                                    <option value="Profesi">Profesi</option>
                                    <option value="Spesialis">Spesialis</option>
                                </select>
                                <p
                                    v-if="form.errors.jenjang_pendidikan"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.jenjang_pendidikan }}
                                </p>
                            </div>

                            <!-- Sumber Biaya -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Sumber Biaya Studi
                                </label>
                                <select
                                    v-model="form.sumber_biaya"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                >
                                    <option value="">Pilih Sumber Biaya</option>
                                    <option value="Biaya Sendiri">
                                        Biaya Sendiri
                                    </option>
                                    <option value="Beasiswa Pemerintah">
                                        Beasiswa Pemerintah
                                    </option>
                                    <option value="Beasiswa Swasta">
                                        Beasiswa Swasta
                                    </option>
                                    <option value="Beasiswa Kampus">
                                        Beasiswa Kampus
                                    </option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                                <p
                                    v-if="form.errors.sumber_biaya"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.sumber_biaya }}
                                </p>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Provinsi -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Provinsi Kampus
                                </label>
                                <select
                                    v-model="form.provinsi_id"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                >
                                    <option :value="null">
                                        Pilih Provinsi
                                    </option>
                                    <option
                                        v-for="prov in provinces"
                                        :key="prov.id"
                                        :value="prov.id"
                                    >
                                        {{ prov.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Kota -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Kota/Kabupaten Kampus
                                </label>
                                <select
                                    v-model="form.kota_id"
                                    :disabled="!form.provinsi_id"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 disabled:bg-gray-100 disabled:cursor-not-allowed dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:disabled:bg-slate-900"
                                >
                                    <option :value="null">Pilih Kota</option>
                                    <option
                                        v-for="city in filteredCities"
                                        :key="city.id"
                                        :value="city.id"
                                    >
                                        {{ city.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Alamat Universitas -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Alamat Lengkap Kampus / Universitas
                            </label>
                            <textarea
                                v-model="form.alamat_universitas"
                                rows="3"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                placeholder="Alamat lengkap kampus/universitas"
                            />
                        </div>

                        <!-- Map Picker -->
                        <div>
                            <label
                                class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                <MapPin class="h-4 w-4 text-purple-500" />
                                Lokasi Kampus pada Peta (Klik / Geser Pin)
                            </label>
                            <div
                                ref="mapContainer"
                                class="h-64 w-full rounded-lg border border-gray-200 shadow-sm dark:border-slate-800"
                                style="z-index: 10"
                            ></div>
                            <div
                                class="mt-2 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400"
                            >
                                <span
                                    >Pilih lokasi kampus untuk pemetaan sebaran
                                    alumni (WebGIS).</span
                                >
                                <span class="font-mono"
                                    >Lat:
                                    {{
                                        form.latitude
                                            ? Number(form.latitude).toFixed(5)
                                            : "-"
                                    }}, Lng:
                                    {{
                                        form.longitude
                                            ? Number(form.longitude).toFixed(5)
                                            : "-"
                                    }}</span
                                >
                            </div>
                        </div>
                    </template>

                    <!-- Period -->
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Tanggal Mulai
                            </label>
                            <input
                                v-model="form.tanggal_mulai"
                                type="date"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Tanggal Selesai
                            </label>
                            <input
                                v-model="form.tanggal_selesai"
                                type="date"
                                :disabled="form.is_current"
                                :min="form.tanggal_mulai || undefined"
                                :class="[
                                    'w-full rounded-lg border px-4 py-2.5 text-gray-900 focus:ring-2 dark:bg-slate-800 dark:text-white',
                                    dateError
                                        ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
                                        : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500/20 dark:border-slate-700',
                                    form.is_current
                                        ? 'bg-gray-100 cursor-not-allowed dark:bg-slate-900'
                                        : '',
                                ]"
                            />
                            <p
                                v-if="dateError"
                                class="mt-1 flex items-center gap-1 text-sm text-red-600"
                            >
                                <svg
                                    class="h-4 w-4 shrink-0"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                {{ dateError }}
                            </p>
                            <p
                                v-else-if="form.errors.tanggal_selesai"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.tanggal_selesai }}
                            </p>
                        </div>
                    </div>

                    <!-- Is Current -->
                    <div class="flex items-center gap-3">
                        <input
                            v-model="form.is_current"
                            type="checkbox"
                            id="is_current"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500/20"
                        />
                        <label
                            for="is_current"
                            class="text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Ini adalah pekerjaan/status saat ini
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 pt-4">
                        <button
                            type="button"
                            @click="close"
                            class="flex-1 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-slate-700 dark:text-gray-300 dark:hover:bg-slate-800"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="
                                form.processing || !!dateError || !!salaryError
                            "
                            class="flex-1 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-blue-500 dark:hover:bg-blue-600"
                        >
                            {{
                                form.processing
                                    ? "Menyimpan..."
                                    : isEditing
                                      ? "Perbarui"
                                      : "Simpan"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>
