<script setup lang="ts">
import { Head, useForm, router } from "@inertiajs/vue3";
import {
    Plus,
    Briefcase,
    MapPin,
    Calendar,
    DollarSign,
    Edit,
    Trash2,
    CheckCircle,
    Building2,
    TrendingUp,
    GraduationCap,
    Info,
    BookOpen,
} from "lucide-vue-next";
import { ref } from "vue";
import { toast } from "vue-sonner";
import TraceAlumniLayout from "@/layouts/TraceAlumniLayout.vue";
import type { BreadcrumbItem } from "@/types";
import CareerFormModal from "./components/CareerFormModal.vue";
import CareerStatsCard from "./components/CareerStatsCard.vue";
import CareerTimelineCard from "./components/CareerTimelineCard.vue";

interface Career {
    id: number;
    status: string;
    nama_perusahaan?: string;
    jabatan?: string;
    sektor_industri?: string;
    alamat_perusahaan?: string;
    provinsi?: { id: number; name: string };
    kota?: { id: number; name: string };
    gaji_min?: number;
    gaji_max?: number;
    nama_universitas?: string;
    program_studi_lanjutan?: string;
    jenjang_pendidikan?: string;
    sumber_biaya?: string;
    alamat_universitas?: string;
    tanggal_mulai?: string;
    tanggal_selesai?: string;
    is_current: boolean;
    created_at: string;
    updated_at: string;
}

interface Stats {
    totalCareers: number;
    totalCompanies: number;
    currentStatus: string;
    yearsOfExperience: string;
}

const props = defineProps<{
    roleName: string;
    currentCareer?: Career | null;
    careerHistory: Career[];
    educationHistory: Career[];
    stats: Stats;
    provinces: any[];
    cities: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: "Dashboard", href: "/trace" },
    { title: "Riwayat Pekerjaan", href: "/trace/career" },
];

const showModal = ref(false);
const editingCareer = ref<Career | null>(null);
const showDeleteConfirm = ref(false);
const deletingCareer = ref<Career | null>(null);

const openAddModal = () => {
    editingCareer.value = null;
    showModal.value = true;
};

const openEditModal = (career: Career) => {
    editingCareer.value = career;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingCareer.value = null;
};

const confirmDelete = (career: Career) => {
    deletingCareer.value = career;
    showDeleteConfirm.value = true;
};

const deleteCareer = () => {
    if (!deletingCareer.value) return;
    router.delete(`/trace/career/${deletingCareer.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success("Riwayat karir berhasil dihapus");
            showDeleteConfirm.value = false;
            deletingCareer.value = null;
        },
        onError: () => {
            toast.error("Gagal menghapus riwayat karir");
        },
    });
};

const setAsCurrent = (career: Career) => {
    router.post(
        `/trace/career/${career.id}/set-current`,
        {},
        {
            preserveScroll: true,
            onSuccess: () =>
                toast.success("Status karir saat ini berhasil diperbarui"),
            onError: () => toast.error("Gagal memperbarui status karir"),
        },
    );
};
</script>

<template>
    <TraceAlumniLayout
        title="Riwayat Pekerjaan"
        :breadcrumbs="breadcrumbs"
        :role-name="roleName"
    >
        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold text-slate-900 dark:text-white"
                    >
                        Riwayat Pekerjaan
                    </h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Kelola riwayat karir dan pekerjaan Anda
                    </p>
                </div>
                <button
                    @click="openAddModal"
                    class="flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-green-700 dark:bg-green-600 dark:hover:bg-green-700 shadow-sm shadow-green-500/20"
                >
                    <Plus class="h-4 w-4" />
                    <span>Tambah Riwayat</span>
                </button>
            </div>

            <!-- Stats -->
            <CareerStatsCard :stats="stats" />

            <!-- Current Career -->
            <div
                v-if="currentCareer"
                class="rounded-2xl border border-slate-100 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900 shadow-xs"
            >
                <div class="mb-5 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <h2
                            class="text-base font-bold text-slate-900 dark:text-white"
                        >
                            Status Saat Ini
                        </h2>
                        <span
                            class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40"
                        >
                            <CheckCircle class="h-3 w-3" />
                            Aktif
                        </span>
                    </div>
                    <button
                        @click="openEditModal(currentCareer)"
                        class="flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition-colors hover:bg-slate-50 dark:border-zinc-700 dark:text-slate-300 dark:hover:bg-zinc-800"
                    >
                        <Edit class="h-3.5 w-3.5" />
                        Edit
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- Bekerja / Wirausaha -->
                    <template
                        v-if="
                            ['bekerja', 'wirausaha'].includes(
                                currentCareer.status,
                            )
                        "
                    >
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-green-50 dark:bg-green-950/30"
                                >
                                    <Building2
                                        class="h-4.5 w-4.5 text-green-600 dark:text-green-400"
                                    />
                                </div>
                                <div>
                                    <p
                                        class="text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        Perusahaan
                                    </p>
                                    <p
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{
                                            currentCareer.nama_perusahaan || "-"
                                        }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-950/30"
                                >
                                    <Briefcase
                                        class="h-4.5 w-4.5 text-emerald-600 dark:text-emerald-400"
                                    />
                                </div>
                                <div>
                                    <p
                                        class="text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        Jabatan
                                    </p>
                                    <p
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ currentCareer.jabatan || "-" }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-teal-50 dark:bg-teal-950/30"
                                >
                                    <TrendingUp
                                        class="h-4.5 w-4.5 text-teal-600 dark:text-teal-400"
                                    />
                                </div>
                                <div>
                                    <p
                                        class="text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        Sektor Industri
                                    </p>
                                    <p
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{
                                            currentCareer.sektor_industri || "-"
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-50 dark:bg-zinc-800"
                                >
                                    <MapPin
                                        class="h-4.5 w-4.5 text-slate-500 dark:text-slate-400"
                                    />
                                </div>
                                <div>
                                    <p
                                        class="text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        Lokasi
                                    </p>
                                    <p
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ currentCareer.kota?.name || ""
                                        }}{{
                                            currentCareer.provinsi?.name
                                                ? ", " +
                                                  currentCareer.provinsi.name
                                                : ""
                                        }}
                                    </p>
                                    <p
                                        v-if="currentCareer.alamat_perusahaan"
                                        class="text-xs text-slate-400 mt-0.5"
                                    >
                                        {{ currentCareer.alamat_perusahaan }}
                                    </p>
                                </div>
                            </div>
                            <div
                                v-if="currentCareer.tanggal_mulai"
                                class="flex items-start gap-3"
                            >
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-50 dark:bg-zinc-800"
                                >
                                    <Calendar
                                        class="h-4.5 w-4.5 text-slate-500 dark:text-slate-400"
                                    />
                                </div>
                                <div>
                                    <p
                                        class="text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        Periode
                                    </p>
                                    <p
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{
                                            new Date(
                                                currentCareer.tanggal_mulai,
                                            ).toLocaleDateString("id-ID", {
                                                year: "numeric",
                                                month: "long",
                                            })
                                        }}
                                        —
                                        {{
                                            currentCareer.tanggal_selesai
                                                ? new Date(
                                                      currentCareer.tanggal_selesai,
                                                  ).toLocaleDateString(
                                                      "id-ID",
                                                      {
                                                          year: "numeric",
                                                          month: "long",
                                                      },
                                                  )
                                                : "Sekarang"
                                        }}
                                    </p>
                                </div>
                            </div>
                            <div
                                v-if="
                                    currentCareer.gaji_min ||
                                    currentCareer.gaji_max
                                "
                                class="flex items-start gap-3"
                            >
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-50 dark:bg-zinc-800"
                                >
                                    <DollarSign
                                        class="h-4.5 w-4.5 text-slate-500 dark:text-slate-400"
                                    />
                                </div>
                                <div>
                                    <p
                                        class="text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        Gaji
                                    </p>
                                    <p
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        Rp
                                        {{
                                            (
                                                currentCareer.gaji_min || 0
                                            ).toLocaleString("id-ID")
                                        }}
                                        {{
                                            currentCareer.gaji_max
                                                ? " — Rp " +
                                                  currentCareer.gaji_max.toLocaleString(
                                                      "id-ID",
                                                  )
                                                : ""
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Lanjut Studi -->
                    <template
                        v-else-if="currentCareer.status === 'lanjut_studi'"
                    >
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-green-50 dark:bg-green-950/30"
                                >
                                    <GraduationCap
                                        class="h-4.5 w-4.5 text-green-600 dark:text-green-400"
                                    />
                                </div>
                                <div>
                                    <p
                                        class="text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        Perguruan Tinggi
                                    </p>
                                    <p
                                        class="font-bold text-slate-900 dark:text-white"
                                    >
                                        {{
                                            currentCareer.nama_universitas ||
                                            "-"
                                        }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-950/30"
                                >
                                    <BookOpen
                                        class="h-4.5 w-4.5 text-emerald-600 dark:text-emerald-400"
                                    />
                                </div>
                                <div>
                                    <p
                                        class="text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        Program Studi & Jenjang
                                    </p>
                                    <p
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ currentCareer.jenjang_pendidikan }} —
                                        {{
                                            currentCareer.program_studi_lanjutan ||
                                            "-"
                                        }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-50 dark:bg-zinc-800"
                                >
                                    <DollarSign
                                        class="h-4.5 w-4.5 text-slate-500 dark:text-slate-400"
                                    />
                                </div>
                                <div>
                                    <p
                                        class="text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        Sumber Biaya
                                    </p>
                                    <p
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ currentCareer.sumber_biaya || "-" }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-50 dark:bg-zinc-800"
                                >
                                    <MapPin
                                        class="h-4.5 w-4.5 text-slate-500 dark:text-slate-400"
                                    />
                                </div>
                                <div>
                                    <p
                                        class="text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        Lokasi Kampus
                                    </p>
                                    <p
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ currentCareer.kota?.name || ""
                                        }}{{
                                            currentCareer.provinsi?.name
                                                ? ", " +
                                                  currentCareer.provinsi.name
                                                : ""
                                        }}
                                    </p>
                                </div>
                            </div>
                            <div
                                v-if="currentCareer.tanggal_mulai"
                                class="flex items-start gap-3"
                            >
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-50 dark:bg-zinc-800"
                                >
                                    <Calendar
                                        class="h-4.5 w-4.5 text-slate-500 dark:text-slate-400"
                                    />
                                </div>
                                <div>
                                    <p
                                        class="text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        Periode
                                    </p>
                                    <p
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{
                                            new Date(
                                                currentCareer.tanggal_mulai,
                                            ).toLocaleDateString("id-ID", {
                                                year: "numeric",
                                                month: "long",
                                            })
                                        }}
                                        —
                                        {{
                                            currentCareer.tanggal_selesai
                                                ? new Date(
                                                      currentCareer.tanggal_selesai,
                                                  ).toLocaleDateString(
                                                      "id-ID",
                                                      {
                                                          year: "numeric",
                                                          month: "long",
                                                      },
                                                  )
                                                : "Sekarang"
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Mencari Kerja -->
                    <template v-else>
                        <div
                            class="md:col-span-2 flex flex-col items-center justify-center py-8 text-center"
                        >
                            <div
                                class="h-12 w-12 rounded-2xl bg-amber-50 dark:bg-amber-950/30 flex items-center justify-center text-amber-500 dark:text-amber-400 mb-3"
                            >
                                <Info class="h-6 w-6" />
                            </div>
                            <h3
                                class="font-bold text-slate-800 dark:text-white"
                            >
                                Sedang Mencari Kerja / Belum Bekerja
                            </h3>
                            <p
                                v-if="currentCareer.tanggal_mulai"
                                class="text-xs text-slate-400 mt-1"
                            >
                                Sejak:
                                {{
                                    new Date(
                                        currentCareer.tanggal_mulai,
                                    ).toLocaleDateString("id-ID", {
                                        year: "numeric",
                                        month: "long",
                                    })
                                }}
                            </p>
                        </div>
                    </template>
                </div>
            </div>

            <!-- No Current Career -->
            <div
                v-else
                class="rounded-2xl border border-dashed border-slate-200 bg-white p-10 text-center dark:border-zinc-700 dark:bg-zinc-900"
            >
                <div
                    class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 dark:bg-zinc-800"
                >
                    <Briefcase
                        class="h-7 w-7 text-slate-300 dark:text-zinc-600"
                    />
                </div>
                <h3
                    class="mb-1 text-base font-bold text-slate-900 dark:text-white"
                >
                    Belum Ada Status Karir
                </h3>
                <p class="mb-5 text-sm text-slate-400">
                    Tambahkan status karir Anda saat ini untuk melengkapi profil
                </p>
                <button
                    @click="openAddModal"
                    class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-green-700 shadow-sm shadow-green-500/20"
                >
                    <Plus class="h-4 w-4" />
                    Tambah Status Karir
                </button>
            </div>

            <!-- Career & Education Timelines -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <CareerTimelineCard
                    title="Riwayat Pekerjaan & Wirausaha"
                    :careers="careerHistory"
                    @edit="openEditModal"
                    @delete="confirmDelete"
                    @set-current="setAsCurrent"
                />
                <CareerTimelineCard
                    title="Riwayat Pendidikan Lanjut"
                    :careers="educationHistory"
                    @edit="openEditModal"
                    @delete="confirmDelete"
                    @set-current="setAsCurrent"
                />
            </div>
        </div>

        <!-- Career Form Modal -->
        <CareerFormModal
            v-model:show="showModal"
            :career="editingCareer"
            :provinces="provinces"
            :cities="cities"
            @close="closeModal"
        />

        <!-- Delete Confirmation Modal -->
        <Teleport to="body">
            <div
                v-if="showDeleteConfirm"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                @click.self="showDeleteConfirm = false"
            >
                <div
                    class="w-full max-w-md rounded-2xl bg-white p-6 dark:bg-zinc-900 shadow-xl"
                >
                    <h3
                        class="mb-3 text-base font-bold text-slate-900 dark:text-white"
                    >
                        Hapus Riwayat Karir?
                    </h3>
                    <p class="mb-6 text-sm text-slate-500 dark:text-slate-400">
                        Apakah Anda yakin ingin menghapus riwayat karir di
                        <strong class="text-slate-700 dark:text-slate-200">{{
                            deletingCareer?.nama_perusahaan
                        }}</strong
                        >? Tindakan ini tidak dapat dibatalkan.
                    </p>
                    <div class="flex gap-3">
                        <button
                            @click="showDeleteConfirm = false"
                            class="flex-1 rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50 dark:border-zinc-700 dark:text-slate-300 dark:hover:bg-zinc-800"
                        >
                            Batal
                        </button>
                        <button
                            @click="deleteCareer"
                            class="flex-1 rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-red-600"
                        >
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </TraceAlumniLayout>
</template>
