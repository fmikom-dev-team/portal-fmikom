<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { X } from "lucide-vue-next";
import { watch, computed } from "vue";
import { toast } from "vue-sonner";
import EmploymentFields from "./EmploymentFields.vue";
import EducationFields from "./EducationFields.vue";
import LocationPicker from "@/components/Trace/LocationPicker.vue";
import type { Lokasi } from "@/types/trace";

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
    provinces: Lokasi[];
    cities: Array<Lokasi & { provinsi_id: number }>;
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

const isEditing = computed(() => !!props.career);

const needsCompanyInfo = computed(() => {
    return ["bekerja", "wirausaha"].includes(form.status);
});

const needsUniversityInfo = computed(() => {
    return form.status === "lanjut_studi";
});

const needsLocation = computed(() => {
    return ["bekerja", "wirausaha", "lanjut_studi"].includes(form.status);
});

const locationAddress = computed({
    get() {
        return form.status === "lanjut_studi"
            ? form.alamat_universitas
            : form.alamat_perusahaan;
    },
    set(value: string) {
        if (form.status === "lanjut_studi") {
            form.alamat_universitas = value;
            return;
        }

        form.alamat_perusahaan = value;
    },
});

const locationMapLabel = computed(() => {
    if (form.status === "lanjut_studi") {
        return "Tandai Lokasi Kampus di Peta";
    }

    return form.status === "wirausaha"
        ? "Tandai Lokasi Usaha di Peta"
        : "Tandai Lokasi Perusahaan di Peta";
});

const locationAddressLabel = computed(() => {
    if (form.status === "lanjut_studi") {
        return "Alamat Lengkap Kampus / Universitas";
    }

    return form.status === "wirausaha"
        ? "Alamat Lengkap Usaha"
        : "Alamat Lengkap Perusahaan";
});

const locationAddressPlaceholder = computed(() => {
    if (form.status === "lanjut_studi") {
        return "Alamat lengkap kampus/universitas";
    }

    return form.status === "wirausaha"
        ? "Alamat lengkap lokasi usaha"
        : "Alamat lengkap perusahaan";
});

const locationAddressError = computed(() => {
    return form.status === "lanjut_studi"
        ? form.errors.alamat_universitas
        : form.errors.alamat_perusahaan;
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

watch(
    () => form.is_current,
    (isCurrent) => {
        if (isCurrent) {
            form.tanggal_selesai = "";
        }
    },
);

watch(
    () => props.show,
    (isShown) => {
        if (isShown) {
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
                // Location fields
                form.provinsi_id = newCareer.provinsi_id || null;
                form.kota_id = newCareer.kota_id || null;
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
        }
    },
    { immediate: true },
);

const close = () => {
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
        onError: () => {
            toast.error("Gagal menyimpan riwayat karir");
        },
    });
};
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
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ form.errors.status }}
                        </p>
                    </div>

                    <!-- Company Info (only for bekerja/wirausaha) -->
                    <template v-if="needsCompanyInfo">
                        <EmploymentFields :form="form" />
                    </template>

                    <!-- University Info (only for lanjut_studi) -->
                    <template v-if="needsUniversityInfo">
                        <EducationFields :form="form" />
                    </template>

                    <!-- Location Picker (for bekerja/wirausaha/lanjut_studi) -->
                    <LocationPicker
                        v-if="needsLocation"
                        v-model:province-id="form.provinsi_id"
                        v-model:city-id="form.kota_id"
                        v-model:latitude="form.latitude"
                        v-model:longitude="form.longitude"
                        v-model:address="locationAddress"
                        :map-label="locationMapLabel"
                        :provinces="provinces"
                        :cities="cities"
                    />

                    <div v-if="needsLocation">
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            {{ locationAddressLabel }}
                        </label>
                        <textarea
                            v-model="locationAddress"
                            rows="3"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            :placeholder="locationAddressPlaceholder"
                        />
                        <p
                            v-if="locationAddressError"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ locationAddressError }}
                        </p>
                    </div>

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
                            <p
                                v-if="form.errors.tanggal_mulai"
                                class="mt-1 text-sm text-red-500"
                            >
                                {{ form.errors.tanggal_mulai }}
                            </p>
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
                                class="mt-1 flex items-center gap-1 text-sm text-red-500"
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
                                class="mt-1 text-sm text-red-500"
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
