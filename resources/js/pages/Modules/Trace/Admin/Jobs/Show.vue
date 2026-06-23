<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import EditorJsRenderer from '@/components/editor/EditorJsRenderer.vue';
import {
    ArrowLeft,
    Briefcase,
    Calendar,
    MapPin,
    DollarSign,
    Users,
    FileText,
    Check,
    X,
    Trash2,
    Building2,
    GraduationCap,
    Clock,
    Eye,
    Shield,
    ChevronDown,
    FolderOpen,
    Pencil,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import TraceAdminLayout from '@/layouts/TraceAdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import type { PaginationLinks } from '@/types/trace';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { TPageHeader, TStatusBadge, TEmptyState, TConfirmDialog } from '@/components/Trace';

interface Alumni {
    id: number;
    user: {
        id: number;
        name: string;
        email: string;
        pagi_username?: string;
        pagi_works?: Array<{ id: number; [key: string]: unknown }>;
        pagi_cvs?: Array<{ id: number; [key: string]: unknown }>;
    };
}

interface Applicant {
    id: number;
    status: 'applied' | 'reviewed' | 'accepted' | 'rejected';
    cover_letter: string | null;
    attached_cv_ids: number[] | null;
    attached_portfolio_ids: number[] | null;
    applied_at: string;
    created_at: string;
    alumni: Alumni;
}

interface Job {
    id: number;
    title: string;
    description: string;
    status: 'draft' | 'pending_review' | 'published' | 'rejected' | 'closed';
    deadline: string;
    experience_level: string;
    location_type: string;
    location_city: string | null;
    tipe_kerja: string;
    salary_min: number | null;
    salary_max: number | null;
    is_salary_visible: boolean;
    category: { nama: string } | null;
    mitra: { nama_perusahaan: string; logo_path: string | null; logo_url: string | null } | null;
    user: { name: string } | null;
    created_at: string;
}

const props = defineProps<{
    job: Job;
    applicants: {
        data: Applicant[];
        links: PaginationLinks;
        meta?: { total: number };
        total?: number;
    };
    isOwner: boolean;
}>();

const applicantList = computed(() => props.applicants.data ?? []);
const applicantTotal = computed(() => props.applicants.meta?.total ?? props.applicants.total ?? props.applicants.data?.length ?? 0);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/trace/admin' },
    { title: 'Lowongan', href: '/trace/admin/jobs' },
    { title: props.job.title, href: `/trace/admin/jobs/${props.job.id}` },
];

/* ───── Label maps ───── */
const experienceLabelMap: Record<string, string> = {
    fresh_graduate: 'Fresh Graduate', junior: 'Junior', mid_level: 'Mid Level', senior: 'Senior', internship: 'Internship',
};
const locationLabelMap: Record<string, string> = { onsite: 'On-site', remote: 'Remote', hybrid: 'Hybrid' };
const tipeKerjaLabelMap: Record<string, string> = { full_time: 'Full-time', part_time: 'Part-time', magang: 'Magang', freelance: 'Freelance' };

/* ───── Applicant status label map ───── */
const applicantLabelMap: Record<string, string> = {
    applied: 'Menunggu',
    reviewed: 'Ditinjau',
    accepted: 'Diterima',
    rejected: 'Ditolak',
};

/* ───── Helpers ───── */
function formatDate(dateStr: string): string {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

function formatCurrency(val: number | null): string {
    if (val == null) return '-';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
}

const salaryDisplay = computed(() => {
    const j = props.job;
    if (j.salary_min && j.salary_max) return `${formatCurrency(j.salary_min)} – ${formatCurrency(j.salary_max)}`;
    if (j.salary_min) return `Dari ${formatCurrency(j.salary_min)}`;
    if (j.salary_max) return `Hingga ${formatCurrency(j.salary_max)}`;
    return 'Tidak ditentukan';
});

const isDeadlinePassed = computed(() => {
    if (!props.job.deadline) return false;
    return new Date(props.job.deadline) < new Date();
});

function getSource(): string {
    return props.job.mitra?.nama_perusahaan ?? 'Admin FMIKOM';
}

function truncate(str: string | null, max = 80): string {
    if (!str) return '-';
    return str.length > max ? str.slice(0, max) + '...' : str;
}

/* ───── Actions ───── */
const processing = ref(false);

const confirmDialog = ref({
    open: false,
    title: '',
    description: '',
    variant: 'danger' as 'danger' | 'warning' | 'info',
    action: '' as string,
});

function approveJob() {
    confirmDialog.value = {
        open: true,
        title: 'Setujui Lowongan',
        description: 'Setujui lowongan ini untuk dipublikasikan?',
        variant: 'info',
        action: 'approve',
    };
}

function rejectJob() {
    confirmDialog.value = {
        open: true,
        title: 'Tolak Lowongan',
        description: 'Tolak lowongan ini?',
        variant: 'danger',
        action: 'reject',
    };
}

function deleteJob() {
    confirmDialog.value = {
        open: true,
        title: 'Hapus Lowongan',
        description: 'Apakah Anda yakin ingin menghapus lowongan ini? Tindakan ini tidak dapat dibatalkan.',
        variant: 'danger',
        action: 'delete',
    };
}

function handleConfirm() {
    if (confirmDialog.value.action === 'approve') {
        processing.value = true;
        router.put(`/trace/admin/jobs/${props.job.id}/approve`, {}, {
            preserveScroll: true,
            onError: () => toast.error('Gagal menyetujui lowongan.'),
            onFinish: () => { processing.value = false; confirmDialog.value.open = false; },
        });
    } else if (confirmDialog.value.action === 'reject') {
        processing.value = true;
        router.put(`/trace/admin/jobs/${props.job.id}/reject`, {}, {
            preserveScroll: true,
            onError: () => toast.error('Gagal menolak lowongan.'),
            onFinish: () => { processing.value = false; confirmDialog.value.open = false; },
        });
    } else if (confirmDialog.value.action === 'delete') {
        router.delete(`/trace/admin/jobs/${props.job.id}`, {
            onError: () => toast.error('Gagal menghapus lowongan.'),
        });
        confirmDialog.value.open = false;
    }
}

const processingApplicant = ref<number | null>(null);
const expandedApplicant = ref<number | null>(null);

function toggleExpand(id: number) {
    expandedApplicant.value = expandedApplicant.value === id ? null : id;
}

function getAttachedCvs(applicant: Applicant) {
    const allCvs = applicant.alumni.user.pagi_cvs ?? [];
    if (!allCvs.length) return [];
    if (applicant.attached_cv_ids?.length) {
        return allCvs.filter((cv: { id: number; [key: string]: unknown }) => applicant.attached_cv_ids!.includes(cv.id));
    }
    return allCvs;
}



/* ───── Review Modal ───── */
const showReviewModal = ref(false);
const reviewTarget = ref<{ applicantId: number; status: 'accepted' | 'rejected' } | null>(null);
const reviewNote = ref('');

function openReviewModal(applicantId: number, status: 'accepted' | 'rejected') {
    reviewTarget.value = { applicantId, status };
    reviewNote.value = '';
    showReviewModal.value = true;
}

function confirmReview() {
    if (!reviewTarget.value) return;
    processingApplicant.value = reviewTarget.value.applicantId;
    router.put(
        `/trace/admin/jobs/${props.job.id}/applicants/${reviewTarget.value.applicantId}/status`,
        { status: reviewTarget.value.status, note: reviewNote.value || null },
        {
            preserveScroll: true,
            onError: () => toast.error('Gagal memperbarui status pelamar.'),
            onFinish: () => {
                processingApplicant.value = null;
                showReviewModal.value = false;
                reviewTarget.value = null;
            },
        },
    );
}
</script>

<template>
    <TraceAdminLayout title="Detail Lowongan" :breadcrumbs="breadcrumbs">
        <div class="mx-auto space-y-6">
            <!-- Back Link -->
            <Link
                href="/trace/admin/jobs"
                class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition-colors hover:text-[#0C447C] dark:text-slate-400 dark:hover:text-[#85B7EB]"
            >
                <ArrowLeft class="h-4 w-4" />
                Kembali ke Daftar Lowongan
            </Link>

            <!-- Pending Review Banner -->
            <Card v-if="job.status === 'pending_review'" class="rounded-2xl border-[#EF9F27]/30 bg-[#EF9F27]/5 dark:border-[#FAC775]/30 dark:bg-[#EF9F27]/5">
                <CardContent class="p-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-[#EF9F27] dark:text-[#FAC775]">
                                <Shield class="mr-1 inline h-4 w-4" />
                                Lowongan ini menunggu review
                            </h3>
                            <p class="mt-0.5 text-xs text-[#EF9F27]/80 dark:text-[#FAC775]/80">
                                Diajukan oleh {{ getSource() }}. Setujui untuk mempublikasikan atau tolak.
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button class="rounded-xl bg-[#0C447C] px-5 text-white hover:bg-[#0C447C]/90 gap-1.5" :disabled="processing" @click="approveJob">
                                <Check class="h-4 w-4" /> Approve
                            </Button>
                            <Button variant="outline" class="rounded-xl border-red-300 px-5 text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-400 gap-1.5" :disabled="processing" @click="rejectJob">
                                <X class="h-4 w-4" /> Reject
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- ═══════ Main Content (2 cols) ═══════ -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Job Header -->
                    <Card class="rounded-2xl border-slate-100 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <CardContent class="p-6">
                            <div class="flex items-start gap-4">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-slate-100 dark:bg-zinc-800">
                                    <img
                                        v-if="job.mitra?.logo_url"
                                        :src="job.mitra.logo_url"
                                        :alt="job.mitra.nama_perusahaan"
                                        class="h-12 w-12 rounded-lg object-contain"
                                    />
                                    <Building2 v-else class="h-6 w-6 text-slate-400 dark:text-zinc-500" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2.5 flex-wrap">
                                        <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ job.title }}</h1>
                                        <TStatusBadge :status="job.status" size="md" />
                                    </div>
                                    <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">
                                        {{ getSource() }}
                                        <span v-if="job.category" class="ml-1 text-slate-300 dark:text-zinc-600">·</span>
                                        <span v-if="job.category" class="ml-1">{{ job.category.nama }}</span>
                                    </p>

                                    <!-- Tags -->
                                    <div class="mt-3 flex flex-wrap gap-1.5">
                                        <span v-if="job.experience_level" class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-600 dark:bg-zinc-800 dark:text-zinc-400">
                                            <GraduationCap class="mr-1 inline h-3 w-3" />
                                            {{ experienceLabelMap[job.experience_level] ?? job.experience_level }}
                                        </span>
                                        <span v-if="job.location_type" class="inline-flex items-center rounded-lg bg-[#0C447C]/5 px-2.5 py-1 text-[11px] font-medium text-[#0C447C] dark:bg-[#85B7EB]/10 dark:text-[#85B7EB]">
                                            <MapPin class="mr-1 inline h-3 w-3" />
                                            {{ locationLabelMap[job.location_type] ?? job.location_type }}
                                            <span v-if="job.location_city" class="ml-0.5 opacity-70">· {{ job.location_city }}</span>
                                        </span>
                                        <span v-if="job.tipe_kerja" class="inline-flex items-center rounded-lg bg-emerald-50 px-2.5 py-1 text-[11px] font-medium text-emerald-600 dark:bg-emerald-950/30 dark:text-emerald-400">
                                            <Briefcase class="mr-1 inline h-3 w-3" />
                                            {{ tipeKerjaLabelMap[job.tipe_kerja] ?? job.tipe_kerja }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Meta Row -->
                            <div class="mt-4 flex flex-wrap items-center gap-4 border-t border-slate-100 pt-4 text-sm dark:border-zinc-800">
                                <span class="flex items-center gap-1.5 font-semibold text-emerald-600 dark:text-emerald-400">
                                    <DollarSign class="h-4 w-4" /> {{ salaryDisplay }}
                                    <span v-if="!job.is_salary_visible" class="text-xs text-slate-400">(Tersembunyi)</span>
                                </span>
                                <span v-if="job.deadline" class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
                                    <Clock class="h-4 w-4" /> Deadline: {{ formatDate(job.deadline) }}
                                    <TStatusBadge v-if="isDeadlinePassed" status="closed" label="Expired" size="sm" />
                                </span>
                                <span class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
                                    <Users class="h-4 w-4" /> {{ applicantTotal }} pelamar
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Description -->
                    <Card class="rounded-2xl border-slate-100 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <CardHeader class="pb-3">
                            <CardTitle class="text-base font-bold text-slate-900 dark:text-white">Deskripsi Pekerjaan</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <EditorJsRenderer :content="job.description" />
                        </CardContent>
                    </Card>

                    <!-- Applicants -->
                    <Card class="rounded-2xl border-slate-100 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <CardHeader class="pb-3">
                            <div class="flex items-center gap-2.5">
                                <Users class="h-5 w-5 text-[#0C447C] dark:text-[#85B7EB]" />
                                <CardTitle class="text-base font-bold text-slate-900 dark:text-white">
                                    Daftar Pelamar ({{ applicantTotal }})
                                </CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div v-if="applicantList.length > 0" class="space-y-3">
                                <div
                                    v-for="applicant in applicantList"
                                    :key="applicant.id"
                                    class="rounded-xl border border-slate-100 transition-all dark:border-zinc-800"
                                    :class="expandedApplicant === applicant.id ? 'bg-slate-50/50 dark:bg-zinc-800/20' : ''"
                                >
                                    <!-- Header Row -->
                                    <button
                                        type="button"
                                        class="flex w-full items-center gap-3 p-4 text-left"
                                        @click="toggleExpand(applicant.id)"
                                    >
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#0C447C]/10 text-sm font-bold text-[#0C447C] dark:bg-[#85B7EB]/10 dark:text-[#85B7EB]">
                                            {{ applicant.alumni.user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ applicant.alumni.user.name }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ applicant.alumni.user.email }}</p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <TStatusBadge
                                                :status="applicant.status"
                                                :label="applicantLabelMap[applicant.status] ?? applicant.status"
                                            />
                                            <ChevronDown class="h-4 w-4 text-slate-400 transition-transform" :class="expandedApplicant === applicant.id ? 'rotate-180' : ''" />
                                        </div>
                                    </button>

                                    <!-- Expanded Content -->
                                    <div v-if="expandedApplicant === applicant.id" class="border-t border-slate-100 p-4 dark:border-zinc-800">
                                        <div class="space-y-4">
                                            <p class="text-xs text-slate-400">
                                                <Clock class="mr-1 inline h-3 w-3" />
                                                Melamar pada {{ formatDate(applicant.applied_at || applicant.created_at) }}
                                            </p>

                                            <div v-if="applicant.cover_letter">
                                                <p class="mb-1.5 text-xs font-bold uppercase tracking-wider text-slate-400">Cover Letter</p>
                                                <div class="rounded-lg bg-white p-3 text-sm leading-relaxed text-slate-700 dark:bg-zinc-900 dark:text-slate-300" style="white-space: pre-line;">
                                                    {{ applicant.cover_letter }}
                                                </div>
                                            </div>

                                            <div v-if="getAttachedCvs(applicant).length > 0">
                                                <p class="mb-1.5 text-xs font-bold uppercase tracking-wider text-slate-400">
                                                    <FileText class="mr-1 inline h-3 w-3 text-emerald-500" /> CV Dilampirkan
                                                </p>
                                                <div class="flex flex-wrap gap-2">
                                                    <a v-for="cv in getAttachedCvs(applicant)" :key="cv.id" :href="`/pagi/cv/${cv.id}/shared`" target="_blank"
                                                       class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 transition-colors hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-950/20 dark:text-emerald-400">
                                                        <Eye class="h-3 w-3" /> {{ cv.title }}
                                                    </a>
                                                </div>
                                            </div>


                                            <p v-if="!applicant.cover_letter && getAttachedCvs(applicant).length === 0"
                                               class="text-xs italic text-slate-400">
                                                Tidak ada lampiran atau cover letter.
                                            </p>

                                            <div v-if="isOwner && applicant.status !== 'accepted' && applicant.status !== 'rejected'" class="flex items-center gap-2 border-t border-slate-100 pt-3 dark:border-zinc-800">
                                                <Button size="sm" class="rounded-lg bg-[#0C447C] hover:bg-[#0C447C]/90 text-white text-xs gap-1 px-4"
                                                        :disabled="processingApplicant === applicant.id" @click="openReviewModal(applicant.id, 'accepted')">
                                                    <Check class="h-3.5 w-3.5" /> Terima Pelamar
                                                </Button>
                                                <Button size="sm" variant="outline" class="rounded-lg border-red-200 text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-400 text-xs gap-1 px-4"
                                                        :disabled="processingApplicant === applicant.id" @click="openReviewModal(applicant.id, 'rejected')">
                                                    <X class="h-3.5 w-3.5" /> Tolak Pelamar
                                                </Button>
                                            </div>

                                            <!-- Status result with note -->
                                            <div v-else-if="applicant.reviewer_note || applicant.reviewed_at" class="border-t border-slate-100 pt-3 dark:border-zinc-800">
                                                <p v-if="applicant.reviewed_at" class="text-[11px] text-slate-400 mb-1">
                                                    Diputuskan pada {{ new Date(applicant.reviewed_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                                                </p>
                                                <div v-if="applicant.reviewer_note" class="rounded-lg bg-slate-50 p-3 text-sm text-slate-600 dark:bg-zinc-800/50 dark:text-slate-400" style="white-space: pre-line;">
                                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Catatan</p>
                                                    {{ applicant.reviewer_note }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty -->
                            <TEmptyState
                                v-else
                                :icon="Users"
                                title="Belum ada pelamar"
                                description="Pelamar akan muncul setelah mereka melamar lowongan ini."
                            />
                        </CardContent>
                    </Card>
                </div>

                <!-- ═══════ Sidebar (1 col) ═══════ -->
                <div class="space-y-4">
                    <!-- Info Card -->
                    <Card class="rounded-2xl border-slate-100 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <CardHeader class="pb-3">
                            <CardTitle class="text-sm font-bold text-slate-900 dark:text-white">Informasi Lowongan</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3.5">
                            <div class="flex items-start gap-2.5">
                                <Building2 class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Sumber</p>
                                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ getSource() }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <GraduationCap class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pengalaman</p>
                                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ experienceLabelMap[job.experience_level] ?? job.experience_level }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <MapPin class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Lokasi</p>
                                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                        {{ locationLabelMap[job.location_type] ?? job.location_type }}
                                        <span v-if="job.location_city" class="text-slate-500"> · {{ job.location_city }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <DollarSign class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Gaji</p>
                                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ salaryDisplay }}</p>
                                    <p v-if="!job.is_salary_visible" class="text-[11px] text-slate-400">(Tersembunyi dari alumni)</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <Calendar class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Deadline</p>
                                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ formatDate(job.deadline) }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <Calendar class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Dibuat pada</p>
                                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ formatDate(job.created_at) }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quick Nav -->
                    <Card class="rounded-2xl border-slate-100 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <CardContent class="space-y-2 p-4">
                            <Link :href="`/trace/admin/jobs/${job.id}/edit`"
                                  class="flex w-full items-center gap-2 rounded-xl bg-[#0C447C]/5 px-3 py-2.5 text-sm font-medium text-[#0C447C] transition-colors hover:bg-[#0C447C]/10 dark:bg-[#85B7EB]/10 dark:text-[#85B7EB] dark:hover:bg-[#85B7EB]/20">
                                <Pencil class="h-4 w-4" />
                                Edit Lowongan
                            </Link>
                            <Link href="/trace/admin/jobs"
                                  class="flex w-full items-center gap-2 rounded-xl bg-slate-50 px-3 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100 dark:bg-zinc-800 dark:text-slate-300 dark:hover:bg-zinc-700">
                                <Briefcase class="h-4 w-4 text-slate-400" />
                                Semua Lowongan
                            </Link>
                            <Link href="/trace/admin/jobs/create"
                                  class="flex w-full items-center gap-2 rounded-xl bg-slate-50 px-3 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100 dark:bg-zinc-800 dark:text-slate-300 dark:hover:bg-zinc-700">
                                <Building2 class="h-4 w-4 text-slate-400" />
                                Buat Lowongan Baru
                            </Link>
                        </CardContent>
                    </Card>

                    <!-- Danger Zone -->
                    <Card class="rounded-2xl border-red-100 shadow-sm dark:border-red-900/50">
                        <CardContent class="p-4">
                            <h3 class="text-sm font-bold text-red-700 dark:text-red-400">Zona Berbahaya</h3>
                            <p class="mt-1 text-xs text-red-500 dark:text-red-500/80">Hapus lowongan beserta semua data pelamar secara permanen.</p>
                            <Button
                                variant="outline"
                                class="mt-3 w-full justify-center rounded-xl border-red-300 text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-400 gap-1.5"
                                @click="deleteJob"
                            >
                                <Trash2 class="h-4 w-4" /> Hapus Lowongan
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
        <!-- Review Modal -->
        <Teleport to="body">
            <div v-if="showReviewModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showReviewModal = false"></div>
                <div class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-zinc-700 dark:bg-zinc-900">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">
                        {{ reviewTarget?.status === 'accepted' ? '✅ Terima Pelamar' : '❌ Tolak Pelamar' }}
                    </h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ reviewTarget?.status === 'accepted'
                            ? 'Tambahkan catatan untuk pelamar (opsional), misal info kontak HR atau jadwal interview.'
                            : 'Tambahkan alasan penolakan (opsional) agar pelamar memahami keputusan Anda.' }}
                    </p>

                    <textarea
                        v-model="reviewNote"
                        rows="4"
                        class="mt-4 w-full rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700 placeholder-slate-400 focus:border-[#0C447C] focus:outline-none focus:ring-2 focus:ring-[#0C447C]/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-slate-300"
                        :placeholder="reviewTarget?.status === 'accepted'
                            ? 'Contoh: Silakan hubungi HR kami di 08xx untuk jadwal interview...'
                            : 'Contoh: Maaf, kami mencari kandidat dengan pengalaman lebih di bidang...'"
                    ></textarea>

                    <div class="mt-4 flex items-center justify-end gap-2">
                        <Button variant="outline" class="rounded-lg px-4 text-sm" @click="showReviewModal = false">
                            Batal
                        </Button>
                        <Button
                            :class="reviewTarget?.status === 'accepted'
                                ? 'bg-[#0C447C] hover:bg-[#0C447C]/90 text-white'
                                : 'bg-red-600 hover:bg-red-700 text-white'"
                            class="rounded-lg px-5 text-sm font-semibold"
                            :disabled="processingApplicant !== null"
                            @click="confirmReview"
                        >
                            {{ reviewTarget?.status === 'accepted' ? '✓ Konfirmasi Terima' : '✕ Konfirmasi Tolak' }}
                        </Button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Confirm Dialog -->
        <TConfirmDialog
            :open="confirmDialog.open"
            :title="confirmDialog.title"
            :description="confirmDialog.description"
            :variant="confirmDialog.variant"
            :confirm-label="confirmDialog.action === 'approve' ? 'Ya, Setujui' : confirmDialog.action === 'reject' ? 'Ya, Tolak' : 'Ya, Hapus'"
            :loading="processing"
            @update:open="(v) => confirmDialog.open = v"
            @confirm="handleConfirm"
        />
    </TraceAdminLayout>
</template>
