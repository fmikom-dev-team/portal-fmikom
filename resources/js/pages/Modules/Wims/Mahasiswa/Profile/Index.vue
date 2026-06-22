<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    PencilLine,
    Save,
    Copy,
    Check,
    Building2,
    GraduationCap,
    ChevronRight,
    FileText,
    Clock,
    Phone,
    Mail,
    BookOpen,
    TrendingUp,
    Zap,
    CircleCheck,
    AlertCircle,
    CalendarDays,
    Globe,
    Linkedin,
    SquarePen,
} from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import StudentLayout from '@/layouts/Modules/Wims/Mahasiswa/Layout.vue';
import { formatIndonesianDateTime } from '@/lib/date';

defineOptions({
    layout: StudentLayout,
});

type ProfileProps = {
    name?: string | null;
    email?: string | null;
    nim_nip?: string | null;
    nomor_induk?: string | null;
    phone?: string | null;
    tanggal_lahir?: string | null;
    tanggal_lahir_label?: string | null;
    bio?: string | null;
    website?: string | null;
    linkedin?: string | null;
    photo_url?: string | null;
    program_studi?: string | null;
    program_studi_code?: string | null;
    fakultas?: string | null;
    role?: string | null;
    status_approval?: string | null;
    is_active?: boolean | null;
};

type RegistrationProps = {
    status?: string | null;
    company?: {
        proposal?: {
            name?: string | null;
        } | null;
        final?: {
            id?: number | null;
            name?: string | null;
        } | null;
    } | null;
    lecturer?: {
        name?: string | null;
    } | null;
    mentor?: {
        name?: string | null;
    } | null;
    period_label?: string | null;
    submitted_at?: string | null;
};

const props = defineProps<{
    profile: ProfileProps;
    registration?: RegistrationProps | null;
}>();

// --- EXISTING LOGIC -----------------------------------------------------------

const profileInitial = computed(() => props.profile.name?.charAt(0) || 'M');
const selectedFileName = ref<string | null>(null);
const photoInputRef = ref<HTMLInputElement | null>(null);
const photoPreviewUrl = ref<string | null>(null);

const profileForm = useForm<{
    no_telepon: string;
    tanggal_lahir: string;
    bio: string;
    website: string;
    linkedin: string;
    foto_profil: File | null;
    remove_photo: boolean;
}>({
    no_telepon: props.profile.phone ?? '',
    tanggal_lahir: props.profile.tanggal_lahir ?? '',
    bio: props.profile.bio ?? '',
    website: props.profile.website ?? '',
    linkedin: props.profile.linkedin ?? '',
    foto_profil: null,
    remove_photo: false,
});

watch(
    () => props.profile.phone,
    (phone) => {
        profileForm.no_telepon = phone ?? '';
    },
);

watch(
    () => props.profile.tanggal_lahir,
    (tanggalLahir) => {
        profileForm.tanggal_lahir = tanggalLahir ?? '';
    },
);

watch(
    () => props.profile.bio,
    (bio) => {
        profileForm.bio = bio ?? '';
    },
);

watch(
    () => props.profile.website,
    (website) => {
        profileForm.website = website ?? '';
    },
);

watch(
    () => props.profile.linkedin,
    (linkedin) => {
        profileForm.linkedin = linkedin ?? '';
    },
);

watch(
    () => profileForm.foto_profil,
    (file, previousFile) => {
        if (photoPreviewUrl.value) {
            URL.revokeObjectURL(photoPreviewUrl.value);
            photoPreviewUrl.value = null;
        }
        if (file) {
            photoPreviewUrl.value = URL.createObjectURL(file);
        }
        if (!file && previousFile && photoInputRef.value) {
            photoInputRef.value.value = '';
        }
    },
);

onBeforeUnmount(() => {
    if (photoPreviewUrl.value) {
        URL.revokeObjectURL(photoPreviewUrl.value);
    }
});

const displayedPhotoUrl = computed(() => photoPreviewUrl.value || props.profile.photo_url || null);

const handlePhotoChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;
    profileForm.remove_photo = false;
    profileForm.foto_profil = file;
    selectedFileName.value = file?.name ?? null;
};

const openPhotoPicker = () => {
    photoInputRef.value?.click();
};

const submitProfileUpdate = () => {
    profileForm.post('/wims/profil', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            profileForm.reset('foto_profil', 'remove_photo');
            selectedFileName.value = null;
            if (photoInputRef.value) {
                photoInputRef.value.value = '';
            }
        },
    });
};

const removePhoto = () => {
    profileForm.remove_photo = true;
    profileForm.foto_profil = null;
    selectedFileName.value = null;
    submitProfileUpdate();
};

const registrationStatusLabel = computed(() => {
    const status = props.registration?.status;
    if (status === 'approved') return 'Approved';
    if (status === 'aktif') return 'Aktif';
    if (status === 'selesai') return 'Selesai';
    if (status === 'revisi') return 'Revisi';
    if (status === 'rejected') return 'Rejected';
    if (status === 'pending') return 'Pending';
    return 'Belum Mendaftar';
});

const registrationStatusClass = computed(() => {
    const status = props.registration?.status;
    if (status === 'approved') return 'border-emerald-200 bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300';
    if (status === 'aktif') return 'border-sky-200 bg-sky-50 dark:bg-sky-500/15 text-sky-700 dark:text-sky-300';
    if (status === 'selesai') return 'border-violet-200 bg-violet-50 dark:bg-violet-500/15 text-violet-700 dark:text-violet-300';
    if (status === 'revisi') return 'border-amber-200 bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300';
    if (status === 'rejected') return 'border-rose-200 bg-rose-50 dark:bg-rose-500/15 text-rose-700 dark:text-rose-300';
    if (status === 'pending') return 'border-blue-200 bg-blue-50 dark:bg-blue-500/15 text-[#2563EB]';
    return 'border-wims-border bg-slate-50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400';
});

const registrationCompanyLabel = computed(
    () => props.registration?.company?.final?.name ?? 'Belum ditetapkan kampus',
);
const registrationLecturerLabel = computed(
    () => props.registration?.lecturer?.name ?? 'Belum ditetapkan',
);
const registrationMentorLabel = computed(
    () => props.registration?.mentor?.name ?? 'Belum ditetapkan',
);

// --- FITUR 1: COPY TO CLIPBOARD -----------------------------------------------

const copiedField = ref<string | null>(null);

const copyToClipboard = async (value: string | null | undefined, field: string) => {
    if (!value) return;
    try {
        await navigator.clipboard.writeText(value);
        copiedField.value = field;
        setTimeout(() => { copiedField.value = null; }, 2000);
    } catch { /* silent */ }
};

// --- FITUR 2: PROFILE COMPLETION SCORE ----------------------------------------

const completionItems = computed(() => [
    { label: 'Nama', filled: !!props.profile.name },
    { label: 'Email', filled: !!props.profile.email },
    { label: 'NIM', filled: !!(props.profile.nim_nip || props.profile.nomor_induk) },
    { label: 'Program Studi', filled: !!props.profile.program_studi },
    { label: 'Nomor Telepon', filled: !!props.profile.phone },
    { label: 'Tanggal Lahir', filled: !!props.profile.tanggal_lahir },
    { label: 'Bio Singkat', filled: !!props.profile.bio },
    { label: 'Website', filled: !!props.profile.website },
    { label: 'LinkedIn', filled: !!props.profile.linkedin },
    { label: 'Foto Profil', filled: !!props.profile.photo_url },
]);

const completionScore = computed(() => {
    const filled = completionItems.value.filter((i) => i.filled).length;
    return Math.round((filled / completionItems.value.length) * 100);
});

const completionBarColor = computed(() => {
    if (completionScore.value >= 80) return 'bg-emerald-500';
    if (completionScore.value >= 50) return 'bg-amber-400';
    return 'bg-rose-400';
});

const completionTextColor = computed(() => {
    if (completionScore.value >= 80) return 'text-emerald-700 dark:text-emerald-300';
    if (completionScore.value >= 50) return 'text-amber-700 dark:text-amber-300';
    return 'text-rose-600 dark:text-rose-400';
});

// --- FITUR 3: INTERNSHIP PROGRESS TRACKER -------------------------------------

const internshipStages = [
    { key: 'draft', label: 'Pengajuan' },
    { key: 'pending', label: 'Review Kampus' },
    { key: 'approved', label: 'Disetujui' },
    { key: 'aktif', label: 'Aktif' },
    { key: 'selesai', label: 'Selesai' },
];

const currentStageIndex = computed(() => {
    const status = props.registration?.status;
    if (status === 'selesai') return 4;
    if (status === 'aktif') return 3;
    if (status === 'approved') return 2;
    if (status === 'pending' || status === 'revisi') return 1;
    if (status) return 0;
    return -1;
});

// --- FITUR 4: SUBMISSION TIMELINE ---------------------------------------------

const formattedSubmittedAt = computed(() => {
    const raw = props.registration?.submitted_at;
    if (!raw) return null;
    try {
        return formatIndonesianDateTime(raw, {
            day: '2-digit',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch { return raw; }
});

const timelineEvents = computed(() => {
    const events: { label: string; date: string | null; icon: string; color: string }[] = [];
    const status = props.registration?.status;
    if (props.registration?.submitted_at) {
        events.push({ label: 'Pengajuan dikirim', date: formattedSubmittedAt.value, icon: 'FileText', color: 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/15 border-blue-200' });
    }
    if (status === 'approved' || status === 'aktif' || status === 'selesai') {
        events.push({ label: 'Pengajuan disetujui', date: props.registration?.period_label ?? '—', icon: 'CircleCheck', color: 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/15 border-emerald-200' });
    }
    if (status === 'revisi') {
        events.push({ label: 'Diminta revisi', date: '—', icon: 'AlertCircle', color: 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/15 border-amber-200' });
    }
    if (status === 'rejected') {
        events.push({ label: 'Pengajuan ditolak', date: '—', icon: 'AlertCircle', color: 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-500/15 border-rose-200' });
    }
    if (status === 'aktif' || status === 'selesai') {
        events.push({ label: 'Magang dimulai', date: props.registration?.period_label ?? '—', icon: 'TrendingUp', color: 'text-sky-600 dark:text-sky-400 bg-sky-50 dark:bg-sky-500/15 border-sky-200' });
    }
    if (status === 'selesai') {
        events.push({ label: 'Magang selesai', date: '—', icon: 'GraduationCap', color: 'text-violet-600 dark:text-violet-400 bg-violet-50 dark:bg-violet-500/15 border-violet-200' });
    }
    return events;
});

const quickActions = computed(() => {
    const status = props.registration?.status;
    const actions: {
        label: string;
        href: string;
        icon: 'building' | 'book' | 'file' | 'alert';
        desc: string;
        color: string;
    }[] = [];

    if (!status) {
        actions.push({
            label: 'Daftar Magang',
            href: '/wims/pendaftaran',
            icon: 'building',
            desc: 'Ajukan pendaftaran PKL baru.',
            color: 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/15 border-blue-200 hover:bg-blue-100 dark:hover:bg-blue-500/20',
        });
    }

    if (status === 'revisi') {
        actions.push({
            label: 'Perbaiki Pengajuan',
            href: '/wims/pendaftaran',
            icon: 'alert',
            desc: 'Buka ulang halaman pendaftaran untuk revisi.',
            color: 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/15 border-amber-200 hover:bg-amber-100 dark:hover:bg-amber-500/20',
        });
    }

    if (status === 'aktif' || status === 'selesai') {
        actions.push({
            label: 'Logbook Harian',
            href: '/wims/logbook',
            icon: 'book',
            desc: 'Lihat dan kelola catatan aktivitas magang.',
            color: 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/15 border-emerald-200 hover:bg-emerald-100 dark:hover:bg-emerald-500/20',
        });
        actions.push({
            label: 'Laporan Akhir',
            href: '/wims/laporan',
            icon: 'file',
            desc: 'Kelola dokumen laporan akhir magang.',
            color: 'text-violet-600 dark:text-violet-400 bg-violet-50 dark:bg-violet-500/15 border-violet-200 hover:bg-violet-100 dark:hover:bg-violet-500/20',
        });
    }

    if (status === 'approved' || status === 'pending' || status === 'aktif' || status === 'selesai') {
        actions.push({
            label: 'Status Magang',
            href: '/wims/pendaftaran',
            icon: 'building',
            desc: 'Tinjau perusahaan, periode, dan status pengajuan.',
            color: 'text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/50 border-wims-border hover:bg-slate-100 dark:hover:bg-slate-700/40',
        });
    }

    return actions;
});

// --- FITUR 5: DRAG & DROP PHOTO ----------------------------------------------

const isDraggingOver = ref(false);

const handleDrop = (event: DragEvent) => {
    event.preventDefault();
    isDraggingOver.value = false;
    const file = event.dataTransfer?.files?.[0];
    if (!file) return;
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) return;
    profileForm.foto_profil = file;
    selectedFileName.value = file.name;
};

const handleDragOver = (event: DragEvent) => {
    event.preventDefault();
    isDraggingOver.value = true;
};

const handleDragLeave = () => { isDraggingOver.value = false; };
</script>

<template>
    <Head title="Profil Mahasiswa" />

    <div class="min-h-full bg-wims-bg">
        <div class="space-y-5 lg:space-y-6">

            <!-- Page Header -->
            <section class="rounded-xl border border-wims-border bg-wims-card px-5 py-4 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)] sm:px-6 sm:py-5">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">WIMS Mahasiswa</p>
                <h1 class="mt-2 text-[24px] font-semibold tracking-tight text-slate-950 dark:text-white sm:text-[28px]">
                    Profil Mahasiswa
                </h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-400">
                    Kelola identitas dasar, kontak, dan ringkasan status magang Anda di WIMS.
                </p>
            </section>

            <!-- ----------------------------------------------------------------
                 FITUR 2 — PROFILE COMPLETION SCORE
                 Menampilkan persentase kelengkapan data profil mahasiswa
                 dengan progress bar berwarna dan checklist item.
            ----------------------------------------------------------------- -->
            <section class="rounded-xl border border-wims-border bg-wims-card px-5 py-4 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)] sm:px-6 sm:py-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-wims-text">Kelengkapan Profil</p>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                            Lengkapi data agar sistem WIMS dapat memproses pengajuan Anda.
                        </p>
                    </div>
                    <span class="text-2xl font-bold" :class="completionTextColor">
                        {{ completionScore }}%
                    </span>
                </div>
                <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700/50">
                    <div
                        class="h-full rounded-full transition-all duration-700"
                        :class="completionBarColor"
                        :style="{ width: completionScore + '%' }"
                    />
                </div>
                <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1.5">
                    <div
                        v-for="item in completionItems"
                        :key="item.label"
                        class="flex items-center gap-1.5 text-xs"
                        :class="item.filled ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400'"
                    >
                        <CircleCheck v-if="item.filled" class="size-3.5 shrink-0" />
                        <AlertCircle v-else class="size-3.5 shrink-0" />
                        {{ item.label }}
                    </div>
                </div>
            </section>

            <!-- Main Identity Card -->
            <div class="w-full">
                <Card class="rounded-xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)]">
                    <CardHeader class="border-b border-wims-border/80 px-5 pt-5 pb-4 sm:px-6 sm:pt-6">
                        <CardTitle class="text-xl text-slate-950 dark:text-white">Identitas Mahasiswa</CardTitle>
                        <CardDescription class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Data diri, akademik, kontak, dan status magang dalam satu halaman yang ringkas.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="px-5 pt-5 pb-5 sm:px-6 sm:pt-6 sm:pb-6">
                        <form class="space-y-6" @submit.prevent="submitProfileUpdate">

                            <!-- Photo + Name Row -->
                            <section class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="flex min-w-0 flex-1 flex-col gap-4 sm:flex-row sm:items-start">

                                    <!-- ----------------------------------------
                                         FITUR 6 — DRAG & DROP AVATAR
                                         Avatar bisa diklik ATAU di-drag-and-drop
                                         file foto langsung ke atasnya. Muncul
                                         overlay hint saat hover dan highlight
                                         border biru saat ada file yang di-drag.
                                    ----------------------------------------- -->
                                    <div
                                        class="relative flex size-[80px] shrink-0 cursor-pointer select-none items-center justify-center overflow-hidden rounded-full ring-1 transition-all duration-200"
                                        :class="[
                                            isDraggingOver
                                                ? 'scale-105 ring-2 ring-[#0F62FE] bg-blue-50 dark:bg-blue-500/15'
                                                : 'bg-slate-200 dark:bg-slate-700 ring-slate-200 text-slate-700 dark:text-slate-300',
                                        ]"
                                        @dragover="handleDragOver"
                                        @dragleave="handleDragLeave"
                                        @drop="handleDrop"
                                        @click="openPhotoPicker"
                                        title="Klik atau seret foto ke sini"
                                    >
                                        <img
                                            v-if="displayedPhotoUrl"
                                            :src="displayedPhotoUrl"
                                            :alt="profile.name || 'Foto profil mahasiswa'"
                                            class="h-full w-full object-cover"
                                        />
                                        <span v-else-if="!isDraggingOver" class="text-xl font-semibold">
                                            {{ profileInitial }}
                                        </span>
                                        <span v-else class="px-1 text-center text-[10px] font-medium leading-tight text-blue-600 dark:text-blue-400">
                                            Lepas di sini
                                        </span>
                                        <!-- hover overlay -->
                                        <div
                                            v-if="!isDraggingOver"
                                            class="absolute inset-0 flex items-center justify-center rounded-full bg-black/30 opacity-0 transition-opacity duration-200 hover:opacity-100"
                                        >
                                            <PencilLine class="size-5 text-white" />
                                        </div>
                                    </div>

                                    <div class="min-w-0 flex-1 space-y-3">
                                        <div class="space-y-2">
                                            <div>
                                                <p class="text-[22px] font-semibold tracking-tight text-slate-950 dark:text-white">
                                                    {{ profile.name || 'Mahasiswa' }}
                                                </p>

                                                <!-- ----------------------------
                                                     FITUR 1 — COPY EMAIL
                                                     Klik teks email untuk menyalin
                                                     ke clipboard; ikon berubah jadi
                                                     centang selama 2 detik.
                                                ----------------------------- -->
                                                <button
                                                    type="button"
                                                    class="group mt-1 flex items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400 transition-colors hover:text-slate-800"
                                                    @click="copyToClipboard(profile.email, 'email')"
                                                    :title="profile.email ? 'Salin ' + profile.email : ''"
                                                >
                                                    <Mail class="size-3.5 shrink-0" />
                                                    <span class="break-words">{{ profile.email || '-' }}</span>
                                                    <Check v-if="copiedField === 'email'" class="size-3.5 shrink-0 text-emerald-500" />
                                                    <Copy v-else class="size-3.5 shrink-0 opacity-0 transition-opacity group-hover:opacity-60" />
                                                </button>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                                                <span>{{ profile.role || 'Mahasiswa' }}</span>
                                                <Badge
                                                    variant="outline"
                                                    class="rounded-full border px-2.5 py-0.5 text-[11px] font-medium shadow-none"
                                                    :class="profile.is_active ? 'border-emerald-200 bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300' : 'border-wims-border bg-slate-50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400'"
                                                >
                                                    {{ profile.is_active ? 'Aktif' : 'Nonaktif' }}
                                                </Badge>
                                            </div>
                                        </div>

                                        <div class="space-y-2">
                                            <input
                                                ref="photoInputRef"
                                                id="foto_profil"
                                                type="file"
                                                accept=".jpg,.jpeg,.png,.webp"
                                                class="hidden"
                                                @change="handlePhotoChange"
                                            />
                                            <p v-if="selectedFileName" class="text-xs text-slate-500 dark:text-slate-400">
                                                File: {{ selectedFileName }}
                                            </p>
                                            <InputError :message="profileForm.errors.foto_profil" />
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2 lg:justify-end">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="h-8 rounded-lg border-wims-border bg-wims-card px-3 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/40 dark:bg-slate-700/50"
                                        :disabled="profileForm.processing"
                                        @click="openPhotoPicker"
                                    >
                                        <PencilLine class="size-4" />
                                        {{ profile.photo_url ? 'Ganti Foto' : 'Pilih Foto' }}
                                    </Button>
                                    <Button
                                        v-if="profile.photo_url || selectedFileName"
                                        type="button"
                                        variant="outline"
                                        class="h-8 rounded-lg border-rose-200 bg-wims-card px-3 text-xs font-medium text-rose-700 dark:text-rose-300 hover:bg-rose-50 dark:bg-rose-500/15 hover:text-rose-800"
                                        :disabled="profileForm.processing"
                                        @click="removePhoto"
                                    >
                                        Hapus Foto
                                    </Button>
                                </div>
                            </section>

                            <!-- Data Akademik + Kontak + Status Magang -->
                            <section class="grid gap-6 border-t border-wims-border pt-5 lg:grid-cols-[minmax(0,1.15fr)_minmax(280px,0.85fr)]">
                                <div class="space-y-6">
                                    <!-- Data Akademik -->
                                    <section class="space-y-3">
                                        <div>
                                            <h3 class="text-base font-semibold text-slate-950 dark:text-white">Data Akademik</h3>
                                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                                Identitas akademik utama yang dipakai di WIMS.
                                            </p>
                                        </div>
                                        <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
                                            <div>
                                                <dt class="text-sm text-slate-500 dark:text-slate-400">NIM</dt>
                                                <!-- ----------------------------
                                                     FITUR 1 — COPY NIM
                                                     Tombol ikon kecil di samping
                                                     NIM untuk menyalin ke clipboard.
                                                ----------------------------- -->
                                                <dd class="mt-1 flex items-center gap-2">
                                                    <span class="text-sm font-semibold text-wims-text">
                                                        {{ profile.nim_nip || profile.nomor_induk || '-' }}
                                                    </span>
                                                    <button
                                                        v-if="profile.nim_nip || profile.nomor_induk"
                                                        type="button"
                                                        class="text-slate-400 transition-colors hover:text-slate-700 dark:text-slate-300"
                                                        @click="copyToClipboard(profile.nim_nip || profile.nomor_induk, 'nim')"
                                                        title="Salin NIM"
                                                    >
                                                        <Check v-if="copiedField === 'nim'" class="size-3.5 text-emerald-500" />
                                                        <Copy v-else class="size-3.5" />
                                                    </button>
                                                </dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-slate-500 dark:text-slate-400">Program Studi</dt>
                                                <dd class="mt-1 text-sm font-semibold text-wims-text">{{ profile.program_studi || '-' }}</dd>
                                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ profile.program_studi_code || 'Kode belum tersedia' }}</p>
                                            </div>
                                            <div class="sm:col-span-2">
                                                <dt class="text-sm text-slate-500 dark:text-slate-400">Fakultas</dt>
                                                <dd class="mt-1 text-sm font-semibold text-wims-text">{{ profile.fakultas || '-' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-slate-500 dark:text-slate-400">Tanggal Lahir</dt>
                                                <dd class="mt-1 text-sm font-semibold text-wims-text">
                                                    {{ profile.tanggal_lahir_label || 'Belum diisi' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </section>

                                    <!-- Kontak -->
                                    <section class="space-y-3 border-t border-wims-border pt-5">
                                        <div>
                                            <h3 class="text-base font-semibold text-slate-950 dark:text-white">Kontak</h3>
                                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                                Lengkapi kanal komunikasi dan profil profesional agar data mahasiswa lebih siap dipakai lintas modul.
                                            </p>
                                        </div>
                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <div class="grid gap-2">
                                                <label for="no_telepon" class="flex items-center gap-1.5 text-sm text-slate-700 dark:text-slate-300">
                                                    <Phone class="size-3.5 text-slate-400" />
                                                    Nomor Telepon
                                                </label>
                                                <Input
                                                    id="no_telepon"
                                                    v-model="profileForm.no_telepon"
                                                    type="text"
                                                    placeholder="Contoh: 081234567890"
                                                    class="h-11 rounded-lg border-wims-border bg-wims-card"
                                                />
                                                <InputError :message="profileForm.errors.no_telepon" />
                                            </div>
                                            <div class="grid gap-2">
                                                <label for="tanggal_lahir" class="flex items-center gap-1.5 text-sm text-slate-700 dark:text-slate-300">
                                                    <CalendarDays class="size-3.5 text-slate-400" />
                                                    Tanggal Lahir
                                                </label>
                                                <Input
                                                    id="tanggal_lahir"
                                                    v-model="profileForm.tanggal_lahir"
                                                    type="date"
                                                    class="student-date-input h-11 rounded-lg border-wims-border bg-wims-card"
                                                />
                                                <InputError :message="profileForm.errors.tanggal_lahir" />
                                            </div>
                                            <div class="grid gap-2 sm:col-span-2">
                                                <label for="website" class="flex items-center gap-1.5 text-sm text-slate-700 dark:text-slate-300">
                                                    <Globe class="size-3.5 text-slate-400" />
                                                    Website
                                                </label>
                                                <Input
                                                    id="website"
                                                    v-model="profileForm.website"
                                                    type="url"
                                                    placeholder="https://portfolioanda.com"
                                                    class="h-11 rounded-lg border-wims-border bg-wims-card"
                                                />
                                                <InputError :message="profileForm.errors.website" />
                                            </div>
                                            <div class="grid gap-2 sm:col-span-2">
                                                <label for="linkedin" class="flex items-center gap-1.5 text-sm text-slate-700 dark:text-slate-300">
                                                    <Linkedin class="size-3.5 text-slate-400" />
                                                    LinkedIn
                                                </label>
                                                <Input
                                                    id="linkedin"
                                                    v-model="profileForm.linkedin"
                                                    type="url"
                                                    placeholder="https://linkedin.com/in/username"
                                                    class="h-11 rounded-lg border-wims-border bg-wims-card"
                                                />
                                                <InputError :message="profileForm.errors.linkedin" />
                                            </div>
                                            <div class="grid gap-2 sm:col-span-2">
                                                <label for="bio" class="flex items-center gap-1.5 text-sm text-slate-700 dark:text-slate-300">
                                                    <SquarePen class="size-3.5 text-slate-400" />
                                                    Bio Singkat
                                                </label>
                                                <textarea
                                                    id="bio"
                                                    v-model="profileForm.bio"
                                                    rows="4"
                                                    maxlength="1500"
                                                    class="min-h-28 w-full rounded-lg border border-wims-border bg-wims-card px-3 py-2.5 text-sm leading-6 text-wims-text transition-colors outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-400/20 dark:focus:ring-blue-400/10"
                                                    placeholder="Tulis ringkasan singkat tentang minat, keahlian, atau fokus akademik Anda."
                                                />
                                                <div class="flex items-center justify-between gap-3">
                                                    <InputError :message="profileForm.errors.bio" />
                                                    <span class="text-xs text-slate-400 dark:text-slate-500">
                                                        {{ profileForm.bio.length }}/1500
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="rounded-xl border border-wims-border/80 bg-slate-50/80 px-4 py-4 dark:bg-slate-800/40">
                                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Tampilan Profil Tambahan</p>
                                            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                                <div>
                                                    <p class="text-xs uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Website</p>
                                                    <p class="mt-1 text-sm font-medium text-wims-text break-all">
                                                        {{ profileForm.website || 'Belum diisi' }}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p class="text-xs uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">LinkedIn</p>
                                                    <p class="mt-1 text-sm font-medium text-wims-text break-all">
                                                        {{ profileForm.linkedin || 'Belum diisi' }}
                                                    </p>
                                                </div>
                                                <div class="sm:col-span-2">
                                                    <p class="text-xs uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Bio</p>
                                                    <p class="mt-1 text-sm leading-6 text-wims-text whitespace-pre-line">
                                                        {{ profileForm.bio || 'Belum ada bio singkat.' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                            <Button
                                                type="submit"
                                                class="h-11 rounded-lg bg-[#0F62FE] px-5 text-white hover:bg-[#0050E6]"
                                                :disabled="profileForm.processing || !profileForm.isDirty"
                                            >
                                                <Save class="size-4" />
                                                Simpan Perubahan
                                            </Button>
                                            <p v-if="profileForm.recentlySuccessful" class="text-sm text-emerald-700 dark:text-emerald-300">
                                                Profil WIMS berhasil diperbarui.
                                            </p>
                                        </div>
                                    </section>
                                </div>

                                <!-- Status Magang -->
                                <section class="space-y-3 border-t border-wims-border pt-5 lg:border-t-0 lg:border-l lg:pl-6 lg:pt-0">
                                    <div>
                                        <h3 class="text-base font-semibold text-slate-950 dark:text-white">Status Magang</h3>
                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                            Ringkasan singkat pengajuan atau penempatan PKL/magang terbaru di WIMS.
                                        </p>
                                    </div>
                                    <dl class="space-y-3">
                                        <div>
                                            <dt class="text-sm text-slate-500 dark:text-slate-400">Status</dt>
                                            <dd class="mt-2">
                                                <Badge
                                                    variant="outline"
                                                    class="rounded-full border px-2.5 py-0.5 text-[11px] font-medium shadow-none"
                                                    :class="registrationStatusClass"
                                                >
                                                    {{ registrationStatusLabel }}
                                                </Badge>
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-slate-500 dark:text-slate-400">Perusahaan</dt>
                                            <dd class="mt-1 text-sm font-semibold text-wims-text">{{ registrationCompanyLabel }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-slate-500 dark:text-slate-400">Dosen pembimbing</dt>
                                            <dd class="mt-1 text-sm font-semibold text-wims-text">{{ registrationLecturerLabel }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-slate-500 dark:text-slate-400">Pembimbing lapangan mitra</dt>
                                            <dd class="mt-1 text-sm font-semibold text-wims-text">{{ registrationMentorLabel }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-slate-500 dark:text-slate-400">Periode PKL</dt>
                                            <dd class="mt-1 text-sm font-semibold text-wims-text">{{ registration?.period_label || 'Belum tersedia' }}</dd>
                                        </div>
                                    </dl>
                                </section>
                            </section>
                        </form>
                    </CardContent>
                </Card>
            </div>

            <!-- ----------------------------------------------------------------
                 FITUR 3 — INTERNSHIP PROGRESS TRACKER
                 Stepper horizontal yang menunjukkan posisi mahasiswa
                 dalam alur PKL: Pengajuan ? Disetujui ? Aktif ? Selesai.
                 Ditampilkan hanya jika ada data registration.
            ----------------------------------------------------------------- -->
            <Card
                v-if="registration"
                class="rounded-xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)]"
            >
                <CardHeader class="border-b border-wims-border/80 px-5 pt-5 pb-4 sm:px-6 sm:pt-6">
                    <CardTitle class="text-base text-slate-950 dark:text-white">Progress Magang</CardTitle>
                    <CardDescription class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                        Tahapan alur proses PKL Anda di WIMS.
                    </CardDescription>
                </CardHeader>
                <CardContent class="px-5 py-5 sm:px-6">
                    <div class="relative flex items-start justify-between gap-2">
                        <!-- background connector -->
                        <div class="absolute top-4 left-4 right-4 h-0.5 bg-slate-100 dark:bg-slate-700/50" aria-hidden="true" />
                        <!-- filled progress -->
                        <div
                            class="absolute top-4 left-4 h-0.5 bg-[#0F62FE] transition-all duration-700"
                            :style="{
                                width: currentStageIndex >= 0
                                    ? `calc(${(currentStageIndex / (internshipStages.length - 1)) * 100}% - 2rem)`
                                    : '0%',
                            }"
                            aria-hidden="true"
                        />
                        <div
                            v-for="(stage, idx) in internshipStages"
                            :key="stage.key"
                            class="relative z-10 flex flex-1 flex-col items-center gap-2"
                        >
                            <div
                                class="flex size-8 items-center justify-center rounded-full border-2 transition-all duration-300"
                                :class="idx <= currentStageIndex
                                    ? 'border-[#0F62FE] bg-[#0F62FE] text-white'
                                    : 'border-wims-border bg-wims-card text-slate-400'"
                            >
                                <CircleCheck v-if="idx < currentStageIndex || (stage.key === 'selesai' && currentStageIndex === internshipStages.length - 1)" class="size-4" />
                                <span v-else class="text-[11px] font-bold">{{ idx + 1 }}</span>
                            </div>
                            <span
                                class="text-center text-[11px] font-medium leading-tight"
                                :class="idx <= currentStageIndex ? 'text-slate-800 dark:text-slate-100' : 'text-slate-400 dark:text-slate-500'"
                            >
                                {{ stage.label }}
                            </span>
                        </div>
                    </div>
                    <p v-if="currentStageIndex === -1" class="mt-4 text-center text-xs text-slate-500 dark:text-slate-400">
                        Belum ada pendaftaran magang aktif.
                    </p>
                </CardContent>
            </Card>

            <!-- Bottom Row: Timeline + Quick Actions -->
            <div class="grid gap-5 lg:grid-cols-2">

                <!-- ------------------------------------------------------------
                     FITUR 4 — RIWAYAT AKTIVITAS (TIMELINE)
                     Menampilkan kronologi event pengajuan magang secara
                     vertikal — dari submit hingga selesai atau ditolak.
                ------------------------------------------------------------- -->
                <Card class="rounded-xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)]">
                    <CardHeader class="border-b border-wims-border/80 px-5 pt-5 pb-4 sm:px-6 sm:pt-6">
                        <CardTitle class="flex items-center gap-2 text-base text-slate-950 dark:text-white">
                            <Clock class="size-4 text-slate-400" />
                            Riwayat Aktivitas
                        </CardTitle>
                        <CardDescription class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                            Kronologi proses pengajuan magang Anda.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="px-5 py-5 sm:px-6">
                        <div v-if="timelineEvents.length > 0" class="relative space-y-4 pl-5">
                            <div class="absolute top-1 bottom-0 left-[9px] w-px bg-slate-100 dark:bg-slate-700/50" aria-hidden="true" />
                            <div
                                v-for="(event, idx) in timelineEvents"
                                :key="idx"
                                class="relative flex items-start gap-3"
                            >
                                <div
                                    class="absolute -left-5 flex size-[18px] items-center justify-center rounded-full border"
                                    :class="event.color"
                                >
                                    <CircleCheck v-if="event.icon === 'CircleCheck'" class="size-3" />
                                    <AlertCircle v-else-if="event.icon === 'AlertCircle'" class="size-3" />
                                    <TrendingUp v-else-if="event.icon === 'TrendingUp'" class="size-3" />
                                    <GraduationCap v-else-if="event.icon === 'GraduationCap'" class="size-3" />
                                    <FileText v-else class="size-3" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ event.label }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ event.date }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="flex flex-col items-center justify-center py-6 text-center">
                            <Clock class="size-8 text-slate-200 dark:text-slate-600" />
                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Belum ada riwayat aktivitas.</p>
                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Mulai dengan mengajukan pendaftaran magang.</p>
                        </div>
                    </CardContent>
                </Card>

                <Card class="rounded-xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)]">
                    <CardHeader class="border-b border-wims-border/80 px-5 pt-5 pb-4 sm:px-6 sm:pt-6">
                        <CardTitle class="flex items-center gap-2 text-base text-slate-950 dark:text-white">
                            <Zap class="size-4 text-slate-400" />
                            Aksi Cepat
                        </CardTitle>
                        <CardDescription class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                            Pintasan ke halaman yang paling sering dipakai.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="px-5 py-5 sm:px-6">
                        <div class="grid gap-2">
                            <Link
                                v-for="action in quickActions"
                                :key="`${action.label}-${action.href}`"
                                :href="action.href"
                                class="flex items-center justify-between rounded-lg border px-3.5 py-3 transition-colors"
                                :class="action.color"
                            >
                                <div class="flex items-center gap-3">
                                    <Building2 v-if="action.icon === 'building'" class="size-4 shrink-0" />
                                    <BookOpen v-else-if="action.icon === 'book'" class="size-4 shrink-0" />
                                    <FileText v-else-if="action.icon === 'file'" class="size-4 shrink-0" />
                                    <AlertCircle v-else class="size-4 shrink-0" />
                                    <div>
                                        <p class="text-sm font-medium">{{ action.label }}</p>
                                        <p class="text-xs opacity-70">{{ action.desc }}</p>
                                    </div>
                                </div>
                                <ChevronRight class="size-4 shrink-0 opacity-50" />
                            </Link>
                        </div>
                    </CardContent>
                </Card>

            </div>
        </div>
    </div>
</template>
