<script setup lang="ts">
import {
    Edit,
    Trash2,
    CheckCircle,
    Briefcase,
    Calendar,
    MapPin,
} from "lucide-vue-next";

interface Career {
    id: number;
    status: string;
    nama_perusahaan?: string;
    jabatan?: string;
    sektor_industri?: string;
    alamat_perusahaan?: string;
    nama_universitas?: string;
    program_studi_lanjutan?: string;
    jenjang_pendidikan?: string;
    provinsi?: { id: number; name: string };
    kota?: { id: number; name: string };
    tanggal_mulai?: string;
    tanggal_selesai?: string;
    is_current: boolean;
    created_at: string;
}

defineProps<{
    careers: Career[];
    title?: string;
}>();

const emit = defineEmits<{
    edit: [career: Career];
    delete: [career: Career];
    setCurrent: [career: Career];
}>();

const formatDate = (dateString?: string) => {
    if (!dateString) {
        return null;
    }

    return new Date(dateString).toLocaleDateString("id-ID", {
        year: "numeric",
        month: "long",
    });
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        bekerja: "Bekerja",
        wirausaha: "Wirausaha",
        mencari_kerja: "Tidak Bekerja",
        lanjut_studi: "Lanjut Studi",
    };

    return labels[status] || status;
};

const getCareerTitle = (career: Career) => {
    if (career.status === "mencari_kerja") {
        return "Tidak Bekerja";
    } else if (career.status === "lanjut_studi") {
        return career.nama_universitas || "Universitas";
    } else {
        return career.nama_perusahaan || "Perusahaan";
    }
};

const getCareerSubtitle = (career: Career) => {
    if (career.status === "mencari_kerja") {
        return "Sedang mencari pekerjaan";
    } else if (career.status === "lanjut_studi") {
        return career.program_studi_lanjutan || "Program Studi";
    } else {
        return career.jabatan || "Jabatan";
    }
};

const calculateDuration = (start?: string, end?: string) => {
    if (!start) {
        return null;
    }

    const startDate = new Date(start);
    const endDate = end ? new Date(end) : new Date();

    const months =
        (endDate.getFullYear() - startDate.getFullYear()) * 12 +
        (endDate.getMonth() - startDate.getMonth());

    const years = Math.floor(months / 12);
    const remainingMonths = months % 12;

    if (years > 0 && remainingMonths > 0) {
        return `${years} tahun ${remainingMonths} bulan`;
    } else if (years > 0) {
        return `${years} tahun`;
    } else if (remainingMonths > 0) {
        return `${remainingMonths} bulan`;
    }

    return "< 1 bulan";
};
</script>

<template>
    <div
        class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-slate-900"
    >
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                {{ title || "Riwayat Karir" }}
            </h2>
            <span
                class="rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-600 dark:bg-blue-900/30 dark:text-blue-400"
            >
                {{ careers.length }} Record
            </span>
        </div>

        <div v-if="careers.length > 0" class="space-y-4">
            <div
                v-for="(career, index) in careers"
                :key="career.id"
                class="group relative rounded-xl border border-gray-200 bg-gray-50/50 p-5 transition-all hover:border-blue-300 hover:bg-blue-50/30 dark:border-slate-800 dark:bg-slate-800/30 dark:hover:border-blue-700 dark:hover:bg-blue-900/10"
            >
                <!-- Timeline Line -->
                <div
                    v-if="index < careers.length - 1"
                    class="absolute left-[29px] top-[60px] h-[calc(100%+16px)] w-0.5 bg-gray-200 dark:bg-slate-700"
                />

                <div class="flex items-start gap-4">
                    <!-- Icon -->
                    <div
                        class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white shadow-sm dark:bg-slate-900"
                    >
                        <Briefcase
                            class="h-5 w-5 text-gray-600 dark:text-gray-400"
                        />
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div
                            class="mb-2 flex items-start justify-between gap-4"
                        >
                            <div class="flex-1">
                                <h3
                                    class="font-bold text-gray-900 dark:text-white"
                                >
                                    {{ getCareerTitle(career) }}
                                </h3>
                                <p
                                    class="text-sm text-gray-600 dark:text-gray-400"
                                >
                                    {{ getCareerSubtitle(career) }}
                                </p>
                                <p
                                    v-if="
                                        career.sektor_industri &&
                                        career.status !== 'mencari_kerja'
                                    "
                                    class="mt-1 text-xs text-gray-500"
                                >
                                    {{ career.sektor_industri }}
                                </p>
                                <p
                                    v-if="
                                        career.jenjang_pendidikan &&
                                        career.status === 'lanjut_studi'
                                    "
                                    class="mt-1 text-xs text-gray-500"
                                >
                                    {{ career.jenjang_pendidikan }}
                                </p>
                            </div>

                            <!-- Actions -->
                            <div
                                class="flex gap-2 opacity-0 transition-opacity group-hover:opacity-100"
                            >
                                <button
                                    v-if="!career.is_current"
                                    @click="emit('setCurrent', career)"
                                    class="rounded-lg border border-green-300 p-2 text-green-600 transition-colors hover:bg-green-50 dark:border-green-700 dark:text-green-400 dark:hover:bg-green-900/20"
                                    title="Set sebagai karir saat ini"
                                >
                                    <CheckCircle class="h-4 w-4" />
                                </button>
                                <button
                                    @click="emit('edit', career)"
                                    class="rounded-lg border border-gray-300 p-2 text-gray-600 transition-colors hover:bg-gray-50 dark:border-slate-700 dark:text-gray-400 dark:hover:bg-slate-800"
                                    title="Edit"
                                >
                                    <Edit class="h-4 w-4" />
                                </button>
                                <button
                                    @click="emit('delete', career)"
                                    class="rounded-lg border border-red-300 p-2 text-red-600 transition-colors hover:bg-red-50 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-900/20"
                                    title="Hapus"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <!-- Period & Duration -->
                        <div
                            v-if="career.tanggal_mulai"
                            class="mb-2 flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
                        >
                            <Calendar class="h-4 w-4" />
                            <span>
                                {{ formatDate(career.tanggal_mulai) }} -
                                {{
                                    career.tanggal_selesai
                                        ? formatDate(career.tanggal_selesai)
                                        : "Sekarang"
                                }}
                            </span>
                            <span class="text-xs text-gray-500">
                                ({{
                                    calculateDuration(
                                        career.tanggal_mulai,
                                        career.tanggal_selesai,
                                    )
                                }})
                            </span>
                        </div>

                        <!-- Location -->
                        <div
                            v-if="career.kota || career.provinsi"
                            class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
                        >
                            <MapPin class="h-4 w-4" />
                            <span>
                                {{ career.kota?.name || ""
                                }}{{
                                    career.provinsi?.name
                                        ? ", " + career.provinsi.name
                                        : ""
                                }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div
            v-else
            class="flex flex-col items-center justify-center py-12 text-center"
        >
            <div class="mb-3 rounded-full bg-gray-100 p-4 dark:bg-slate-800">
                <Briefcase class="h-8 w-8 text-gray-400" />
            </div>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Belum ada riwayat karir sebelumnya
            </p>
            <p class="mt-1 text-xs text-gray-500">
                Tambahkan riwayat pekerjaan Anda untuk melengkapi profil
            </p>
        </div>
    </div>
</template>
