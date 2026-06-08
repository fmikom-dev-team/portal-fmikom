<script setup lang="ts">
import { computed, watch } from "vue";
import {
    BookOpen,
    Briefcase,
    Calendar,
    ChevronDown,
    Globe,
    GraduationCap,
    Info,
    Linkedin,
    Loader2,
    MapPin,
    Phone,
    Plus,
    Save,
    User,
    X,
} from "lucide-vue-next";
import MapPicker from "./MapPicker.vue";

const props = defineProps<{
    isOpen: boolean;
    form: any;
    provinsis: { id: number; name: string }[];
    kotas: { id: number; name: string; provinsi_id: number }[];
    programStudis: { id: number; nama: string; kode: string }[];
}>();

const emit = defineEmits<{
    (e: "close"): void;
    (e: "submit"): void;
}>();

const filteredCities = computed(() => {
    if (!props.form.provinsi_id) return [];
    return props.kotas.filter(
        (city) => city.provinsi_id === parseInt(props.form.provinsi_id),
    );
});

watch(
    () => props.form.provinsi_id,
    (newProv, oldProv) => {
        // Reset city ONLY if province has actually changed to a different value (and not on initial loading)
        if (oldProv !== undefined && newProv !== oldProv) {
            props.form.kota_id = "";
        }
    },
);

const updateLocation = (loc: { lat: number; lng: number }) => {
    props.form.latitude_rumah = loc.lat;
    props.form.longitude_rumah = loc.lng;
};
</script>

<template>
    <Transition name="fade">
        <div
            v-if="isOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md overflow-y-auto"
        >
            <!-- Modal Box -->
            <div
                class="relative bg-white dark:bg-slate-900 w-full max-w-4xl rounded-[28px] shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden flex flex-col my-8 max-h-[90vh]"
            >
                <!-- Modal Header -->
                <div
                    class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0 bg-slate-50/50 dark:bg-slate-950/20"
                >
                    <div>
                        <h3
                            class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-wider"
                        >
                            Perbarui Profil Karir
                        </h3>
                        <p
                            class="text-xs text-slate-400 dark:text-slate-500 font-semibold mt-0.5"
                        >
                            Lengkapi data profil dan domisili Anda untuk tracer
                            study
                        </p>
                    </div>
                    <button
                        @click="emit('close')"
                        class="p-2 rounded-xl hover:bg-slate-105 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 transition-all cursor-pointer active:scale-95"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <!-- Modal Body (Scrollable) -->
                <div class="flex-1 p-6 overflow-y-auto space-y-6">
                    <form
                        @submit.prevent="emit('submit')"
                        id="profileEditForm"
                        class="space-y-6"
                    >
                        <!-- ══════════════════ SECTION 1: DENTITAS & AKUN ══════════════════ -->
                        <div
                            class="bg-slate-50/50 dark:bg-slate-950/10 rounded-2xl border border-slate-100 dark:border-slate-850 p-5 space-y-4"
                        >
                            <div class="flex items-center gap-2 mb-2">
                                <User class="w-4 h-4 text-green-500" />
                                <span
                                    class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider"
                                    >Identitas & Kontak</span
                                >
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Nama Lengkap -->
                                <div class="space-y-1.5">
                                    <label
                                        class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                                        >Nama Lengkap</label
                                    >
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        required
                                        class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all uppercase"
                                    />
                                    <p
                                        v-if="form.errors.name"
                                        class="text-xs font-medium text-rose-500"
                                    >
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <!-- Email (Disabled) -->
                                <div class="space-y-1.5">
                                    <label
                                        class="block text-[11px] font-black text-black-500 dark:text-slate-400 uppercase tracking-wider"
                                        >Email (Akun)</label
                                    >
                                    <input
                                        :value="form.email"
                                        type="email"
                                        disabled
                                        class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-850 bg-slate-100 dark:bg-slate-950 text-sm font-semibold text-slate-400 dark:text-slate-500 cursor-not-allowed"
                                    />
                                    <p
                                        class="text-[9px] font-medium text-slate-400 dark:text-slate-500 mt-1 flex items-center gap-1"
                                    >
                                        <Info class="w-3 h-3" /> Untuk mengubah
                                        email utama, silakan akses halaman
                                        Settings akun.
                                    </p>
                                </div>

                                <!-- NIM -->
                                <div class="space-y-1.5">
                                    <label
                                        class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                                        >NIM (Nomor Induk Mahasiswa)</label
                                    >
                                    <input
                                        v-model="form.nomor_induk"
                                        type="text"
                                        required
                                        class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                    />
                                    <p
                                        v-if="form.errors.nomor_induk"
                                        class="text-xs font-medium text-rose-500"
                                    >
                                        {{ form.errors.nomor_induk }}
                                    </p>
                                </div>

                                <!-- NIK -->
                                <div class="space-y-1.5">
                                    <label
                                        class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                                        >NIK (16 Digit KTP)</label
                                    >
                                    <input
                                        v-model="form.nik"
                                        type="text"
                                        maxlength="16"
                                        placeholder="Masukkan 16 digit NIK"
                                        class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                    />
                                    <p
                                        v-if="form.errors.nik"
                                        class="text-xs font-medium text-rose-500"
                                    >
                                        {{ form.errors.nik }}
                                    </p>
                                </div>

                                <!-- NPWP -->
                                <div class="space-y-1.5">
                                    <label
                                        class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                                        >NPWP</label
                                    >
                                    <input
                                        v-model="form.npwp"
                                        type="text"
                                        placeholder="Masukkan NPWP jika ada"
                                        class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                    />
                                    <p
                                        v-if="form.errors.npwp"
                                        class="text-xs font-medium text-rose-500"
                                    >
                                        {{ form.errors.npwp }}
                                    </p>
                                </div>

                                <!-- No HP / WA -->
                                <div class="space-y-1.5">
                                    <label
                                        class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                                        >No. HP / WhatsApp</label
                                    >
                                    <input
                                        v-model="form.no_telepon"
                                        type="text"
                                        placeholder="Contoh: 08123456789"
                                        class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                    />
                                    <p
                                        v-if="form.errors.no_telepon"
                                        class="text-xs font-medium text-rose-500"
                                    >
                                        {{ form.errors.no_telepon }}
                                    </p>
                                </div>

                                <!-- Jenis Kelamin -->
                                <div class="space-y-1.5">
                                    <label
                                        class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                                        >Jenis Kelamin</label
                                    >
                                    <div class="relative">
                                        <select
                                            v-model="form.jenis_kelamin"
                                            class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all appearance-none cursor-pointer"
                                            style="color-scheme: light"
                                        >
                                            <option value="" disabled>
                                                Pilih jenis kelamin...
                                            </option>
                                            <option value="L">Laki-laki</option>
                                            <option value="P">Perempuan</option>
                                        </select>
                                        <div
                                            class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center"
                                        >
                                            <ChevronDown
                                                class="w-4 h-4 text-slate-400"
                                            />
                                        </div>
                                    </div>
                                    <p
                                        v-if="form.errors.jenis_kelamin"
                                        class="text-xs font-medium text-rose-500"
                                    >
                                        {{ form.errors.jenis_kelamin }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- ══════════════════ SECTION 2: AKADEMIK ══════════════════ -->
                        <div
                            class="bg-slate-50/50 dark:bg-slate-950/10 rounded-2xl border border-slate-100 dark:border-slate-850 p-5 space-y-4"
                        >
                            <div class="flex items-center gap-2 mb-2">
                                <GraduationCap class="w-4 h-4 text-green-500" />
                                <span
                                    class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider"
                                    >Informasi Akademik</span
                                >
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <!-- Program Studi -->
                                <div class="space-y-1.5 sm:col-span-1">
                                    <label
                                        class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                                        >Program Studi</label
                                    >
                                    <div class="relative">
                                        <select
                                            v-model="form.program_studi_id"
                                            class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all appearance-none cursor-pointer"
                                            style="color-scheme: light"
                                        >
                                            <option value="" disabled>
                                                Pilih program studi...
                                            </option>
                                            <option
                                                v-for="prodi in programStudis"
                                                :key="prodi.id"
                                                :value="prodi.id"
                                            >
                                                {{ prodi.nama }}
                                            </option>
                                        </select>
                                        <div
                                            class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center"
                                        >
                                            <ChevronDown
                                                class="w-4 h-4 text-slate-400"
                                            />
                                        </div>
                                    </div>
                                    <p
                                        v-if="form.errors.program_studi_id"
                                        class="text-xs font-medium text-rose-500"
                                    >
                                        {{ form.errors.program_studi_id }}
                                    </p>
                                </div>

                                <!-- Angkatan -->
                                <div class="space-y-1.5">
                                    <label
                                        class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                                        >Angkatan (Tahun)</label
                                    >
                                    <input
                                        v-model="form.angkatan"
                                        type="number"
                                        placeholder="2019"
                                        class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                    />
                                    <p
                                        v-if="form.errors.angkatan"
                                        class="text-xs font-medium text-rose-500"
                                    >
                                        {{ form.errors.angkatan }}
                                    </p>
                                </div>

                                <!-- Tahun Lulus -->
                                <div class="space-y-1.5">
                                    <label
                                        class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                                        >Tahun Lulus</label
                                    >
                                    <input
                                        v-model="form.tahun_lulus"
                                        type="number"
                                        placeholder="2023"
                                        class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                    />
                                    <p
                                        v-if="form.errors.tahun_lulus"
                                        class="text-xs font-medium text-rose-500"
                                    >
                                        {{ form.errors.tahun_lulus }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- ══════════════════ SECTION 3: BIO & MEDIA SOSIAL ══════════════════ -->
                        <div
                            class="bg-slate-50/50 dark:bg-slate-950/10 rounded-2xl border border-slate-100 dark:border-slate-850 p-5 space-y-4"
                        >
                            <div class="flex items-center gap-2 mb-2">
                                <Globe class="w-4 h-4 text-green-500" />
                                <span
                                    class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider"
                                    >Biodata & Media Sosial</span
                                >
                            </div>

                            <div class="space-y-4">
                                <!-- Biodata Singkat -->
                                <div class="space-y-1.5">
                                    <label
                                        class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                                        >Bio</label
                                    >
                                    <textarea
                                        v-model="form.bio"
                                        rows="3"
                                        placeholder=""
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all resize-none"
                                    ></textarea>
                                    <p
                                        v-if="form.errors.bio"
                                        class="text-xs font-medium text-rose-500"
                                    >
                                        {{ form.errors.bio }}
                                    </p>
                                </div>

                                <div
                                    class="grid grid-cols-1 sm:grid-cols-2 gap-4"
                                >
                                    <!-- Lokasi -->
                                    <div class="space-y-1.5">
                                        <label
                                            class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                                            >Lokasi / Domisili Kota</label
                                        >
                                        <input
                                            v-model="form.location"
                                            type="text"
                                            placeholder="Contoh: Jakarta Selatan, DKI Jakarta"
                                            class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                        />
                                        <p
                                            v-if="form.errors.location"
                                            class="text-xs font-medium text-rose-500"
                                        >
                                            {{ form.errors.location }}
                                        </p>
                                    </div>

                                    <!-- Website -->
                                    <div class="space-y-1.5">
                                        <label
                                            class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                                            >Website URL</label
                                        >
                                        <input
                                            v-model="form.website"
                                            type="url"
                                            placeholder="https://example.com"
                                            class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                        />
                                        <p
                                            v-if="form.errors.website"
                                            class="text-xs font-medium text-rose-500"
                                        >
                                            {{ form.errors.website }}
                                        </p>
                                    </div>

                                    <!-- LinkedIn -->
                                    <div class="space-y-1.5">
                                        <label
                                            class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                                            >LinkedIn URL / Username</label
                                        >
                                        <input
                                            v-model="form.linkedin"
                                            type="text"
                                            placeholder="Username LinkedIn"
                                            class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                        />
                                        <p
                                            v-if="form.errors.linkedin"
                                            class="text-xs font-medium text-rose-500"
                                        >
                                            {{ form.errors.linkedin }}
                                        </p>
                                    </div>

                                    <!-- GitHub -->
                                    <div class="space-y-1.5">
                                        <label
                                            class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                                            >GitHub Username</label
                                        >
                                        <input
                                            v-model="form.github"
                                            type="text"
                                            placeholder="Username Github"
                                            class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                        />
                                        <p
                                            v-if="form.errors.github"
                                            class="text-xs font-medium text-rose-500"
                                        >
                                            {{ form.errors.github }}
                                        </p>
                                    </div>

                                    <!-- Instagram -->
                                    <div class="space-y-1.5">
                                        <label
                                            class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                                            >Username Instagram</label
                                        >
                                        <input
                                            v-model="form.instagram"
                                            type="text"
                                            placeholder="Instagram Username"
                                            class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                        />
                                        <p
                                            v-if="form.errors.instagram"
                                            class="text-xs font-medium text-rose-500"
                                        >
                                            {{ form.errors.instagram }}
                                        </p>
                                    </div>

                                    <!-- Twitter -->
                                    <div class="space-y-1.5">
                                        <label
                                            class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                                            >Twitter Username</label
                                        >
                                        <input
                                            v-model="form.twitter"
                                            type="text"
                                            placeholder="Twitter Username"
                                            class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                        />
                                        <p
                                            v-if="form.errors.twitter"
                                            class="text-xs font-medium text-rose-500"
                                        >
                                            {{ form.errors.twitter }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ══════════════════ SECTION 4: ALAMAT & PETA ══════════════════ -->
                        <div
                            class="bg-slate-50/50 dark:bg-slate-950/10 rounded-2xl border border-slate-100 dark:border-slate-850 p-5 space-y-4"
                        >
                            <div class="flex items-center gap-2 mb-2">
                                <MapPin class="w-4 h-4 text-green-500" />
                                <span
                                    class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider"
                                    >Lokasi Domisili & Peta</span
                                >
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Provinsi -->
                                <div class="space-y-1.5">
                                    <label
                                        class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                                        >Provinsi Domisili</label
                                    >
                                    <div class="relative">
                                        <select
                                            v-model="form.provinsi_id"
                                            class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all appearance-none cursor-pointer"
                                            style="color-scheme: light"
                                        >
                                            <option value="">
                                                Pilih Provinsi...
                                            </option>
                                            <option
                                                v-for="prov in provinsis"
                                                :key="prov.id"
                                                :value="prov.id"
                                            >
                                                {{ prov.name }}
                                            </option>
                                        </select>
                                        <div
                                            class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center"
                                        >
                                            <ChevronDown
                                                class="w-4 h-4 text-slate-400"
                                            />
                                        </div>
                                    </div>
                                    <p
                                        v-if="form.errors.provinsi_id"
                                        class="text-xs font-medium text-rose-500"
                                    >
                                        {{ form.errors.provinsi_id }}
                                    </p>
                                </div>

                                <!-- Kota / Kabupaten -->
                                <div class="space-y-1.5">
                                    <label
                                        class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                                        >Kota / Kabupaten</label
                                    >
                                    <div class="relative">
                                        <select
                                            v-model="form.kota_id"
                                            :disabled="!form.provinsi_id"
                                            class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all appearance-none cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                            style="color-scheme: light"
                                        >
                                            <option value="">
                                                Pilih Kota/Kabupaten...
                                            </option>
                                            <option
                                                v-for="city in filteredCities"
                                                :key="city.id"
                                                :value="city.id"
                                            >
                                                {{ city.name }}
                                            </option>
                                        </select>
                                        <div
                                            class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center"
                                        >
                                            <ChevronDown
                                                class="w-4 h-4 text-slate-400"
                                            />
                                        </div>
                                    </div>
                                    <p
                                        v-if="form.errors.kota_id"
                                        class="text-xs font-medium text-rose-500"
                                    >
                                        {{ form.errors.kota_id }}
                                    </p>
                                </div>

                                <!-- Alamat Lengkap -->
                                <div class="space-y-1.5 sm:col-span-2">
                                    <label
                                        class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                                        >Alamat Lengkap Rumah / Domisili</label
                                    >
                                    <textarea
                                        v-model="form.alamat_rumah"
                                        rows="3"
                                        placeholder="Masukkan alamat lengkap RT/RW, kelurahan, kecamatan..."
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all resize-none"
                                    ></textarea>
                                    <p
                                        v-if="form.errors.alamat_rumah"
                                        class="text-xs font-medium text-rose-500"
                                    >
                                        {{ form.errors.alamat_rumah }}
                                    </p>
                                </div>

                                <!-- Map Picker Integration -->
                                <div class="space-y-2 sm:col-span-2">
                                    <label
                                        class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider flex items-center gap-1.5"
                                    >
                                        <MapPin
                                            class="w-3.5 h-3.5 text-green-500"
                                        />
                                        Lokasi pada Peta (Klik / Seret Pin)
                                    </label>

                                    <!-- Render MapPicker only when modal is open to ensure container element exists -->
                                    <MapPicker
                                        v-if="isOpen"
                                        :latitude="form.latitude_rumah"
                                        :longitude="form.longitude_rumah"
                                        @update:location="updateLocation"
                                    />
                                    <p
                                        class="text-[10px] text-slate-400 dark:text-slate-500 leading-normal"
                                    >
                                        Geser pin penanda di atas untuk
                                        menentukan koordinat tempat tinggal Anda
                                        untuk visualisasi peta alumni.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Modal Footer -->
                <div
                    class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20 flex items-center justify-end gap-3 shrink-0"
                >
                    <button
                        type="button"
                        @click="emit('close')"
                        class="h-11 px-5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-105 dark:hover:bg-slate-800 transition-all cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        form="profileEditForm"
                        :disabled="form.processing"
                        class="h-11 px-6 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-bold shadow-lg shadow-green-200/50 dark:shadow-none transition-all flex items-center gap-2 cursor-pointer active:scale-95 disabled:opacity-50"
                    >
                        <Loader2
                            v-if="form.processing"
                            class="w-4 h-4 animate-spin"
                        />
                        <Save v-else class="w-4 h-4" />
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition:
        opacity 0.2s ease,
        transform 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: scale(0.98);
}

select option {
    color: #0f172a !important;
    background-color: #ffffff !important;
}
</style>
