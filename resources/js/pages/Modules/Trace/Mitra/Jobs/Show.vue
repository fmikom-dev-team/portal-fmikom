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
    Eye,
    GraduationCap,
    Clock,
    Building2,
    Send,
    Pencil,
    ChevronDown,
    FolderOpen,
    ExternalLink,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import TraceMitraLayout from '@/layouts/TraceMitraLayout.vue';
import { TPageHeader, TStatusBadge, TEmptyState } from '@/components/Trace';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types';

interface PagiWork { id: number; [key: string]: unknown; }
interface PagiCv { id: number; [key: string]: unknown; }

interface Alumni {
    id: number;
    user: {
        id: number;
        name: string;
        email: string;
        pagi_username?: string;
        pagi_works?: PagiWork[];
        pagi_cvs?: PagiCv[];
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

interface Category { id: number; nama: string; }

interface Job {
    id: number;
    title: string;
    description: string;
    status: 'draft' | 'pending_review' | 'published' | 'rejected' | 'closed';
    rejection_reason: string | null;
    deadline: string;
    experience_level: string;
    location_type: string;
    location_city: string | null;
    tipe_kerja: string;
    salary_min: number | null;
    salary_max: number | null;
    is_salary_visible: boolean;
    category: Category | null;
    mitra: { nama_perusahaan: string; logo_path: string | null; logo_url: string | null } | null;
}

const props = defineProps<{
    job: Job;
    applicants: Applicant[];
    categories?: Category[];
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/trace/open-job/dashboard' },
    { title: 'Lowongan', href: '/trace/open-job/jobs-listings' },
    { title: props.job.title, href: `/trace/open-job/jobs-listings/${props.job.id}` },
];

/* ───── Status configs ───── */
const jobStatusConfig: Record<string, { label: string; color: string; bg: string }> = {
    draft:          { label: 'Draft',            color: 'text-slate-600 dark:text-slate-300',  bg: 'bg-slate-100 dark:bg-zinc-800' },
    pending_review: { label: 'Menunggu Review',  color: 'text-amber-700 dark:text-amber-400',  bg: 'bg-amber-50 dark:bg-amber-950/30' },
    published:      { label: 'Aktif',            color: 'text-emerald-700 dark:text-emerald-400', bg: 'bg-emerald-50 dark:bg-emerald-950/30' },
    rejected:       { label: 'Ditolak',          color: 'text-red-700 dark:text-red-400',      bg: 'bg-red-50 dark:bg-red-950/30' },
    closed:         { label: 'Ditutup',          color: 'text-slate-500 dark:text-slate-400',  bg: 'bg-slate-50 dark:bg-zinc-800/50' },
};

const applicantStatusConfig: Record<string, { label: string; class: string }> = {
    applied:  { label: 'Menunggu', class: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950 dark:text-amber-400 dark:border-amber-800' },
    reviewed: { label: 'Ditinjau', class: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950 dark:text-blue-400 dark:border-blue-800' },
    accepted: { label: 'Diterima', class: 'bg-green-50 text-green-700 border-green-200 dark:bg-green-950 dark:text-green-400 dark:border-green-800' },
    rejected: { label: 'Ditolak',  class: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950 dark:text-red-400 dark:border-red-800' },
};

const experienceLabelMap: Record<string, string> = {
    fresh_graduate: 'Fresh Graduate', junior: 'Junior', mid_level: 'Mid Level', senior: 'Senior', internship: 'Internship',
};
const locationLabelMap: Record<string, string> = { onsite: 'On-site', remote: 'Remote', hybrid: 'Hybrid' };
const tipeKerjaLabelMap: Record<string, string> = { full_time: 'Full-time', part_time: 'Part-time', magang: 'Magang', freelance: 'Freelance' };

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

function truncate(str: string | null, max = 80): string {
    if (!str) return '-';
    return str.length > max ? str.slice(0, max) + '...' : str;
}

/* ───── Actions ───── */
const processing = ref<number | null>(null);
const expandedApplicant = ref<number | null>(null);

function toggleExpand(id: number) {
    expandedApplicant.value = expandedApplicant.value === id ? null : id;
}

function getAttachedCvs(applicant: Applicant) {
    const allCvs = applicant.alumni.user.pagi_cvs ?? [];
    if (!allCvs.length) return [];
    if (applicant.attached_cv_ids?.length) {
        return allCvs.filter((cv: PagiCv) => applicant.attached_cv_ids!.includes(cv.id));
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
    processing.value = reviewTarget.value.applicantId;
    router.put(
        `/trace/open-job/jobs-listings/${props.job.id}/applicants/${reviewTarget.value.applicantId}/status`,
        { status: reviewTarget.value.status, note: reviewNote.value || null },
        {
            preserveScroll: true,
            onError: () => { toast.error('Gagal memperbarui status pelamar.'); },
            onFinish: () => {
                processing.value = null;
                showReviewModal.value = false;
                reviewTarget.value = null;
            },
        },
    );
}

function toggleJobStatus() {
    const toastCallbacks = {
        onError: () => { toast.error('Gagal memperbarui status lowongan.'); },
    };
    if (props.job.status === 'draft' || props.job.status === 'rejected') {
        router.put(`/trace/open-job/jobs-listings/${props.job.id}/submit-review`, {}, { preserveScroll: true, ...toastCallbacks });
    } else if (props.job.status === 'published') {
        router.put(`/trace/open-job/jobs-listings/${props.job.id}`, { status: 'closed' }, { preserveScroll: true, ...toastCallbacks });
    }
}
</script>

<template>
    <TraceMitraLayout title="Detail Lowongan" :breadcrumbs="breadcrumbItems">
        <div class="mx-auto space-y-6">
            <TPageHeader :title="job.title" :description="job.category?.nama" :icon="Briefcase">
                <template #actions>
                    <Link
                        href="/trace/open-job/jobs-listings"
                        class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition-colors hover:text-[#0C447C] dark:text-slate-400 dark:hover:text-[#85B7EB]"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Kembali
                    </Link>
                </template>
            </TPageHeader>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- ═══════ Main Content (2 cols) ═══════ -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Job Header -->
                    <Card class="rounded-2xl border-slate-100 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <CardContent class="p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-4">
                                    <!-- Company Logo -->
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
                                            <TStatusBadge :status="job.status" :label="jobStatusConfig[job.status]?.label" size="md" />
                                        </div>
                                        <p v-if="job.category" class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                            {{ job.category.nama }}
                                        </p>

                                        <!-- Tags -->
                                        <div class="mt-3 flex flex-wrap gap-1.5">
                                            <Badge v-if="job.experience_level" variant="secondary" class="rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-600 dark:bg-zinc-800 dark:text-zinc-400">
                                                <GraduationCap class="mr-1 inline h-3 w-3" />
                                                {{ experienceLabelMap[job.experience_level] ?? job.experience_level }}
                                            </Badge>
                                            <Badge v-if="job.location_type" variant="secondary" class="rounded-lg bg-blue-50 px-2.5 py-1 text-[11px] font-medium text-blue-600 dark:bg-blue-950/30 dark:text-blue-400">
                                                <MapPin class="mr-1 inline h-3 w-3" />
                                                {{ locationLabelMap[job.location_type] ?? job.location_type }}
                                                <span v-if="job.location_city" class="ml-0.5 opacity-70">· {{ job.location_city }}</span>
                                            </Badge>
                                            <Badge v-if="job.tipe_kerja" variant="secondary" class="rounded-lg bg-emerald-50 px-2.5 py-1 text-[11px] font-medium text-emerald-600 dark:bg-emerald-950/30 dark:text-emerald-400">
                                                <Briefcase class="mr-1 inline h-3 w-3" />
                                                {{ tipeKerjaLabelMap[job.tipe_kerja] ?? job.tipe_kerja }}
                                            </Badge>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Rejection Reason -->
                            <div
                                v-if="job.status === 'rejected' && job.rejection_reason"
                                class="mt-4 flex items-start gap-2.5 rounded-xl border border-red-200 bg-red-50/70 p-3.5 dark:border-red-900/40 dark:bg-red-950/20"
                            >
                                <X class="mt-0.5 h-4 w-4 shrink-0 text-red-500 dark:text-red-400" />
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-red-600 dark:text-red-400 mb-1">Alasan Penolakan</p>
                                    <p class="text-sm leading-relaxed text-red-700 dark:text-red-300" style="white-space: pre-line;">{{ job.rejection_reason }}</p>
                                </div>
                            </div>

                            <!-- Meta Row -->
                            <div class="mt-4 flex flex-wrap items-center gap-4 border-t border-slate-100 pt-4 text-sm dark:border-zinc-800">
                                <span class="flex items-center gap-1.5 font-semibold text-emerald-600 dark:text-emerald-400">
                                    <DollarSign class="h-4 w-4" />
                                    {{ salaryDisplay }}
                                    <span v-if="!job.is_salary_visible" class="text-xs text-slate-400">(Tersembunyi)</span>
                                </span>
                                <span v-if="job.deadline" class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
                                    <Clock class="h-4 w-4" />
                                    Deadline: {{ formatDate(job.deadline) }}
                                    <Badge v-if="isDeadlinePassed" variant="destructive" class="ml-1 rounded-md px-1.5 py-0 text-[10px]">Expired</Badge>
                                </span>
                                <span class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
                                    <Users class="h-4 w-4" />
                                    {{ applicants.length }} pelamar
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Description -->
                    <Card class="rounded-2xl border-slate-100 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <CardHeader class="pb-3">
                            <CardTitle class="text-base font-bold text-slate-900 dark:text-white">
                                Deskripsi Pekerjaan
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <EditorJsRenderer :content="job.description" />
                        </CardContent>
                    </Card>

                    <!-- ═══════ Applicants ═══════ -->
                    <Card class="rounded-2xl border-slate-100 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <CardHeader class="pb-3">
                            <div class="flex items-center gap-2.5">
                                <Users class="h-5 w-5 text-violet-600 dark:text-violet-400" />
                                <CardTitle class="text-base font-bold text-slate-900 dark:text-white">
                                    Daftar Pelamar ({{ applicants.length }})
                                </CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div v-if="applicants.length > 0" class="space-y-3">
                                <div
                                    v-for="applicant in applicants"
                                    :key="applicant.id"
                                    class="rounded-xl border border-slate-100 transition-all dark:border-zinc-800"
                                    :class="expandedApplicant === applicant.id ? 'bg-slate-50/50 dark:bg-zinc-800/20' : ''"
                                >
                                    <!-- Header Row (clickable) -->
                                    <button
                                        type="button"
                                        class="flex w-full items-center gap-3 p-4 text-left"
                                        @click="toggleExpand(applicant.id)"
                                    >
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-violet-100 text-sm font-bold text-violet-600 dark:bg-violet-950/30 dark:text-violet-400">
                                            {{ applicant.alumni.user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ applicant.alumni.user.name }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ applicant.alumni.user.email }}</p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <TStatusBadge :status="applicant.status" :label="applicantStatusConfig[applicant.status]?.label" size="sm" />
                                            <ChevronDown
                                                class="h-4 w-4 text-slate-400 transition-transform"
                                                :class="expandedApplicant === applicant.id ? 'rotate-180' : ''"
                                            />
                                        </div>
                                    </button>

                                    <!-- Expanded Content -->
                                    <div v-if="expandedApplicant === applicant.id" class="border-t border-slate-100 p-4 dark:border-zinc-800">
                                        <div class="space-y-4">
                                            <!-- Applied Date -->
                                            <p class="text-xs text-slate-400">
                                                <Clock class="mr-1 inline h-3 w-3" />
                                                Melamar pada {{ formatDate(applicant.applied_at || applicant.created_at) }}
                                            </p>

                                            <!-- Cover Letter -->
                                            <div v-if="applicant.cover_letter">
                                                <p class="mb-1.5 text-xs font-bold uppercase tracking-wider text-slate-400">Cover Letter</p>
                                                <div class="rounded-lg bg-white p-3 text-sm leading-relaxed text-slate-700 dark:bg-zinc-900 dark:text-slate-300" style="white-space: pre-line;">
                                                    {{ applicant.cover_letter }}
                                                </div>
                                            </div>

                                            <!-- Attached CVs -->
                                            <div v-if="getAttachedCvs(applicant).length > 0">
                                                <p class="mb-1.5 text-xs font-bold uppercase tracking-wider text-slate-400">
                                                    <FileText class="mr-1 inline h-3 w-3 text-emerald-500" /> CV Dilampirkan
                                                </p>
                                                <div class="flex flex-wrap gap-2">
                                                    <a
                                                        v-for="cv in getAttachedCvs(applicant)"
                                                        :key="cv.id"
                                                        :href="`/pagi/cv/${cv.id}/shared`"
                                                        target="_blank"
                                                        class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 transition-colors hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-950/20 dark:text-emerald-400"
                                                    >
                                                        <Eye class="h-3 w-3" /> {{ cv.title }}
                                                    </a>
                                                </div>
                                            </div>


                                            <!-- No attachments note -->
                                            <p
                                                v-if="!applicant.cover_letter && getAttachedCvs(applicant).length === 0"
                                                class="text-xs italic text-slate-400"
                                            >
                                                Tidak ada lampiran atau cover letter.
                                            </p>

                                            <!-- Actions -->
                                            <div v-if="applicant.status !== 'accepted' && applicant.status !== 'rejected'" class="flex items-center gap-2 border-t border-slate-100 pt-3 dark:border-zinc-800">
                                                <Button
                                                    size="sm"
                                                    class="rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs gap-1 px-4"
                                                    :disabled="processing === applicant.id"
                                                    @click="openReviewModal(applicant.id, 'accepted')"
                                                >
                                                    <Check class="h-3.5 w-3.5" /> Terima Pelamar
                                                </Button>
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    class="rounded-lg border-red-200 text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-400 text-xs gap-1 px-4"
                                                    :disabled="processing === applicant.id"
                                                    @click="openReviewModal(applicant.id, 'rejected')"
                                                >
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
                    <!-- Actions Card -->
                    <Card class="rounded-2xl border-slate-100 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <CardHeader class="pb-3">
                            <CardTitle class="text-sm font-bold text-slate-900 dark:text-white">Aksi</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <Button
                                v-if="job.status === 'draft' || job.status === 'rejected'"
                                class="w-full justify-center rounded-xl bg-violet-600 text-sm text-white hover:bg-violet-700"
                                @click="toggleJobStatus"
                            >
                                <Send class="mr-1.5 h-4 w-4" />
                                Ajukan Review
                            </Button>
                            <Button
                                v-else-if="job.status === 'published'"
                                variant="outline"
                                class="w-full justify-center rounded-xl border-red-200 text-sm text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-400"
                                @click="toggleJobStatus"
                            >
                                <X class="mr-1.5 h-4 w-4" />
                                Tutup Lowongan
                            </Button>
                            <div
                                v-else-if="job.status === 'pending_review'"
                                class="rounded-xl bg-amber-50 p-3 text-center text-xs font-medium text-amber-700 dark:bg-amber-950/30 dark:text-amber-400"
                            >
                                <Clock class="mr-1 inline h-3.5 w-3.5" />
                                Menunggu approval admin...
                            </div>

                            <Link :href="`/trace/open-job/jobs-listings/${job.id}/edit`" class="block">
                                <Button variant="outline" class="w-full justify-center rounded-xl text-sm">
                                    <Pencil class="mr-1.5 h-4 w-4" />
                                    Edit Lowongan
                                </Button>
                            </Link>
                        </CardContent>
                    </Card>

                    <!-- Detail Info Card -->
                    <Card class="rounded-2xl border-slate-100 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <CardHeader class="pb-3">
                            <CardTitle class="text-sm font-bold text-slate-900 dark:text-white">Informasi Lowongan</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3.5">
                            <div class="flex items-start gap-2.5">
                                <GraduationCap class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pengalaman</p>
                                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                        {{ experienceLabelMap[job.experience_level] ?? job.experience_level }}
                                    </p>
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
                                <Briefcase class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Tipe Kerja</p>
                                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                        {{ tipeKerjaLabelMap[job.tipe_kerja] ?? job.tipe_kerja }}
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
                                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                        {{ formatDate(job.deadline) }}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quick Nav -->
                    <Card class="rounded-2xl border-slate-100 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <CardContent class="space-y-2 p-4">
                            <Link
                                href="/trace/open-job/jobs-listings"
                                class="flex w-full items-center gap-2 rounded-xl bg-slate-50 px-3 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100 dark:bg-zinc-800 dark:text-slate-300 dark:hover:bg-zinc-700"
                            >
                                <Briefcase class="h-4 w-4 text-slate-400" />
                                Semua Lowongan
                            </Link>
                            <Link
                                href="/trace/open-job/jobs-listings/create"
                                class="flex w-full items-center gap-2 rounded-xl bg-slate-50 px-3 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100 dark:bg-zinc-800 dark:text-slate-300 dark:hover:bg-zinc-700"
                            >
                                <Building2 class="h-4 w-4 text-slate-400" />
                                Buat Lowongan Baru
                            </Link>
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
                        class="mt-4 w-full rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-slate-300"
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
                                ? 'bg-emerald-600 hover:bg-emerald-700 text-white'
                                : 'bg-red-600 hover:bg-red-700 text-white'"
                            class="rounded-lg px-5 text-sm font-semibold"
                            :disabled="processing !== null"
                            @click="confirmReview"
                        >
                            {{ reviewTarget?.status === 'accepted' ? '✓ Konfirmasi Terima' : '✕ Konfirmasi Tolak' }}
                        </Button>
                    </div>
                </div>
            </div>
        </Teleport>
    </TraceMitraLayout>
</template>
