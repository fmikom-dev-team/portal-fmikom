<script setup lang="ts">
import { Link, router, useForm } from "@inertiajs/vue3";
import { toast } from 'vue-sonner';
import EditorJsRenderer from '@/components/editor/EditorJsRenderer.vue';
import TraceAlumniLayout from "@/layouts/TraceAlumniLayout.vue";
import type { BreadcrumbItem } from "@/types";
import { TPageHeader, TStatusBadge } from '@/components/trace';
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import {
    MapPin,
    Clock,
    Briefcase,
    Building2,
    DollarSign,
    Bookmark,
    BookmarkCheck,
    CheckCircle2,
    XCircle,
    Send,
    ArrowLeft,
    Users,
    GraduationCap,
    FileText,
    FolderOpen,
    ExternalLink,
    Check,
    Image,
} from "lucide-vue-next";
import { ref, computed } from "vue";

interface Mitra {
    nama_perusahaan: string;
    logo_path: string | null;
    logo_url: string | null;
    alamat?: string;
    deskripsi?: string;
    website?: string;
}

interface Job {
    id: number;
    title: string;
    description: string;
    status: string;
    deadline: string;
    experience_level: string;
    location_type: string;
    tipe_kerja: string;
    salary_min: number | null;
    salary_max: number | null;
    is_salary_visible: boolean;
    applicants_count: number;
    category: { nama: string } | null;
    mitra: Mitra;
}

interface MyApplication {
    id: number;
    status: string;
    reviewer_note: string | null;
    reviewed_at: string | null;
}

const props = defineProps<{
    job: Job;
    hasApplied: boolean;
    isBookmarked: boolean;
    myApplication: MyApplication | null;
    myCvs: Array<{ id: number; title: string; template_id?: string; updated_at?: string }>;
    myWorks: Array<{ id: number; title: string; category?: string; cover_image?: string; updated_at?: string }>;
}>();

const applicationStatusConfig: Record<string, { label: string; class: string; bgClass: string }> = {
    applied: {
        label: 'Menunggu Review',
        class: 'text-[#EF9F27] dark:text-[#FAC775]',
        bgClass: 'bg-[#EF9F27]/10 dark:bg-[#EF9F27]/10',
    },
    reviewed: {
        label: 'Sedang Ditinjau',
        class: 'text-[#0C447C] dark:text-[#85B7EB]',
        bgClass: 'bg-[#0C447C]/10 dark:bg-[#0C447C]/20',
    },
    accepted: {
        label: 'Lamaran Diterima',
        class: 'text-emerald-700 dark:text-emerald-400',
        bgClass: 'bg-emerald-50 dark:bg-emerald-950/20',
    },
    rejected: {
        label: 'Lamaran Ditolak',
        class: 'text-red-700 dark:text-red-400',
        bgClass: 'bg-red-50 dark:bg-red-950/20',
    },
};

const breadcrumbItems: BreadcrumbItem[] = [
    { title: "Dashboard", href: "/trace" },
    { title: "Lowongan Kerja", href: "/trace/jobs" },
    { title: props.job.title, href: `/trace/jobs/${props.job.id}` },
];

const bookmarked = ref(props.isBookmarked);
const bookmarkLoading = ref(false);

/* ───── Apply Form ───── */
const selectedCvIds = ref<number[]>([]);

const applyForm = useForm({
    cover_letter: "",
    attached_cv_ids: [] as number[],
});

function toggleCv(id: number) {
    const idx = selectedCvIds.value.indexOf(id);
    if (idx >= 0) selectedCvIds.value.splice(idx, 1);
    else selectedCvIds.value.push(id);
}


function submitApplication() {
    applyForm.attached_cv_ids = selectedCvIds.value;
    applyForm.post(`/trace/jobs/${props.job.id}/apply`, {
        preserveScroll: true,
        onError: () => { toast.error('Gagal mengirim lamaran.'); },
    });
}

function toggleBookmark() {
    bookmarkLoading.value = true;
    router.post(
        `/trace/jobs/${props.job.id}/bookmark`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                bookmarked.value = !bookmarked.value;
            },
            onError: () => {
                toast.error('Gagal memperbarui bookmark.');
            },
            onFinish: () => {
                bookmarkLoading.value = false;
            },
        },
    );
}

function formatSalary(value: number): string {
    if (value >= 1_000_000) return `${(value / 1_000_000).toFixed(value % 1_000_000 === 0 ? 0 : 1)} jt`;
    if (value >= 1_000) return `${(value / 1_000).toFixed(0)} rb`;
    return value.toLocaleString("id-ID");
}

const salaryDisplay = computed(() => {
    const j = props.job;
    if (!j.is_salary_visible) return null;
    if (j.salary_min && j.salary_max) return `Rp ${formatSalary(j.salary_min)} – ${formatSalary(j.salary_max)}`;
    if (j.salary_min) return `Dari Rp ${formatSalary(j.salary_min)}`;
    if (j.salary_max) return `Hingga Rp ${formatSalary(j.salary_max)}`;
    return null;
});

function formatDeadline(dateStr: string): string {
    const d = new Date(dateStr);
    return d.toLocaleDateString("id-ID", { day: "numeric", month: "long", year: "numeric" });
}

const isDeadlinePassed = computed(() => {
    if (!props.job.deadline) return false;
    return new Date(props.job.deadline) < new Date();
});

const experienceLabelMap: Record<string, string> = {
    fresh_graduate: "Fresh Graduate",
    junior: "Junior",
    mid_level: "Mid-Level",
    senior: "Senior",
    internship: "Internship",
};

const locationLabelMap: Record<string, string> = {
    onsite: "On-site",
    remote: "Remote",
    hybrid: "Hybrid",
};

const tipeKerjaLabelMap: Record<string, string> = {
    full_time: "Full-time",
    part_time: "Part-time",
    magang: "Magang",
    freelance: "Freelance",
};
</script>

<template>
    <TraceAlumniLayout
        :title="job.title"
        :breadcrumbs="breadcrumbItems"
        role-name="Alumni"
    >
        <div class="mx-auto space-y-6">
            <!-- Header -->
            <TPageHeader :title="job.title" :description="job.mitra?.nama_perusahaan" :icon="Briefcase">
                <template #actions>
                    <Link
                        href="/trace/jobs"
                        class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition-colors hover:text-[#0C447C] dark:text-slate-400 dark:hover:text-[#85B7EB]"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Kembali ke Lowongan
                    </Link>
                </template>
            </TPageHeader>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Main Content (2 cols) -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Job Header Card -->
                    <Card class="rounded-2xl border border-slate-200/60 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <CardContent class="p-6">
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
                                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                                        {{ job.title }}
                                    </h1>
                                    <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">
                                        {{ job.mitra?.nama_perusahaan }}
                                    </p>

                                    <!-- Tags -->
                                    <div class="mt-3 flex flex-wrap gap-1.5">
                                        <Badge
                                            v-if="job.experience_level"
                                            variant="secondary"
                                            class="rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-600 dark:bg-zinc-800 dark:text-zinc-400"
                                        >
                                            <GraduationCap class="mr-1 inline h-3 w-3" />
                                            {{ experienceLabelMap[job.experience_level] ?? job.experience_level }}
                                        </Badge>
                                        <Badge
                                            v-if="job.location_type"
                                            variant="secondary"
                                            class="rounded-lg bg-[#0C447C]/10 px-2.5 py-1 text-[11px] font-medium text-[#0C447C] dark:bg-[#0C447C]/10 dark:text-[#85B7EB]"
                                        >
                                            <MapPin class="mr-1 inline h-3 w-3" />
                                            {{ locationLabelMap[job.location_type] ?? job.location_type }}
                                        </Badge>
                                        <Badge
                                            v-if="job.tipe_kerja"
                                            variant="secondary"
                                            class="rounded-lg bg-[#0C447C]/5 px-2.5 py-1 text-[11px] font-medium text-[#0C447C] dark:bg-[#85B7EB]/10 dark:text-[#85B7EB]"
                                        >
                                            <Briefcase class="mr-1 inline h-3 w-3" />
                                            {{ tipeKerjaLabelMap[job.tipe_kerja] ?? job.tipe_kerja }}
                                        </Badge>
                                        <Badge
                                            v-if="job.category?.nama"
                                            variant="secondary"
                                            class="rounded-lg bg-[#0C447C]/10 px-2.5 py-1 text-[11px] font-medium text-[#0C447C] dark:bg-[#0C447C]/10 dark:text-[#85B7EB]"
                                        >
                                            {{ job.category.nama }}
                                        </Badge>
                                    </div>
                                </div>

                                <!-- Bookmark Button -->
                                <button
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border transition-all duration-200"
                                    :class="bookmarked
                                        ? 'border-[#0C447C]/30 bg-[#0C447C]/5 text-[#0C447C] dark:border-[#85B7EB]/40 dark:bg-[#85B7EB]/10 dark:text-[#85B7EB]'
                                        : 'border-slate-200 bg-white text-slate-400 hover:border-[#0C447C]/30 hover:text-[#0C447C] dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-[#85B7EB]/40 dark:hover:text-[#85B7EB]'"
                                    :disabled="bookmarkLoading"
                                    @click="toggleBookmark"
                                >
                                    <BookmarkCheck v-if="bookmarked" class="h-5 w-5" />
                                    <Bookmark v-else class="h-5 w-5" />
                                </button>
                            </div>

                            <!-- Meta Row -->
                            <div class="mt-4 flex flex-wrap items-center gap-4 border-t border-slate-100 pt-4 text-sm dark:border-zinc-800">
                                <span v-if="salaryDisplay" class="flex items-center gap-1.5 font-semibold text-[#0C447C] dark:text-[#85B7EB]">
                                    <DollarSign class="h-4 w-4" />
                                    {{ salaryDisplay }}
                                </span>
                                <span v-if="job.deadline" class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
                                    <Clock class="h-4 w-4" />
                                    Deadline: {{ formatDeadline(job.deadline) }}
                                    <TStatusBadge
                                        v-if="isDeadlinePassed"
                                        status="closed"
                                        label="Expired"
                                        size="sm"
                                        class="ml-1"
                                    />
                                </span>
                                <span class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
                                    <Users class="h-4 w-4" />
                                    {{ job.applicants_count }} pelamar
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Description -->
                    <Card class="rounded-2xl border border-slate-200/60 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <CardHeader class="pb-3">
                            <CardTitle class="text-base font-bold text-slate-900 dark:text-white">
                                Deskripsi Pekerjaan
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <EditorJsRenderer :content="job.description" />
                        </CardContent>
                    </Card>

                    <!-- Apply Form / Applied Status -->
                    <Card
                        v-if="!isDeadlinePassed"
                        class="rounded-2xl border border-slate-200/60 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
                    >
                        <CardHeader class="pb-3">
                            <CardTitle class="text-base font-bold text-slate-900 dark:text-white">
                                {{ hasApplied ? 'Status Lamaran' : 'Lamar Pekerjaan Ini' }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <!-- Already Applied -->
                            <div v-if="hasApplied" class="space-y-3">
                                <div
                                    class="flex items-center gap-3 rounded-xl p-4"
                                    :class="applicationStatusConfig[myApplication?.status ?? 'applied']?.bgClass ?? 'bg-slate-50 dark:bg-zinc-800/20'"
                                >
                                    <CheckCircle2
                                        v-if="myApplication?.status === 'accepted'"
                                        class="h-6 w-6 shrink-0 text-emerald-600 dark:text-emerald-400"
                                    />
                                    <component
                                        v-else
                                        :is="myApplication?.status === 'rejected' ? XCircle : Clock"
                                        class="h-6 w-6 shrink-0"
                                        :class="myApplication?.status === 'rejected'
                                            ? 'text-red-500 dark:text-red-400'
                                            : 'text-[#EF9F27] dark:text-[#FAC775]'"
                                    />
                                    <div>
                                        <p
                                            class="text-sm font-bold"
                                            :class="applicationStatusConfig[myApplication?.status ?? 'applied']?.class ?? 'text-slate-700 dark:text-slate-300'"
                                        >
                                            {{ applicationStatusConfig[myApplication?.status ?? 'applied']?.label ?? 'Sudah Melamar' }}
                                        </p>
                                        <p class="mt-0.5 text-xs text-slate-500/70 dark:text-slate-400/70">
                                            Lamaran Anda telah terkirim. Pantau status di halaman Lamaran Saya.
                                        </p>
                                    </div>
                                </div>

                                <!-- Reviewer Note -->
                                <div
                                    v-if="myApplication?.reviewer_note"
                                    class="rounded-xl border p-3.5"
                                    :class="myApplication.status === 'accepted'
                                        ? 'border-green-100 bg-green-50/50 dark:border-green-900/30 dark:bg-green-950/10'
                                        : myApplication.status === 'rejected'
                                            ? 'border-red-100 bg-red-50/50 dark:border-red-900/30 dark:bg-red-950/10'
                                            : 'border-slate-100 bg-slate-50/50 dark:border-zinc-800 dark:bg-zinc-800/30'"
                                >
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1.5">
                                        Catatan dari Perusahaan
                                    </p>
                                    <p v-if="myApplication.reviewed_at" class="text-[11px] text-slate-400 dark:text-slate-500 mb-2">
                                        {{ new Date(myApplication.reviewed_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                                    </p>
                                    <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-400" style="white-space: pre-line;">
                                        {{ myApplication.reviewer_note }}
                                    </p>
                                </div>
                            </div>

                            <!-- Apply Form -->
                            <form v-else @submit.prevent="submitApplication" class="space-y-5">
                                <!-- Cover Letter -->
                                <div>
                                    <label
                                        for="cover_letter"
                                        class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        Surat Pengantar (Cover Letter)
                                    </label>
                                    <Textarea
                                        id="cover_letter"
                                        v-model="applyForm.cover_letter"
                                        placeholder="Tuliskan mengapa Anda tertarik dan cocok untuk posisi ini..."
                                        rows="5"
                                        class="rounded-xl border-slate-200 text-sm dark:border-zinc-700"
                                    />
                                    <p
                                        v-if="applyForm.errors.cover_letter"
                                        class="mt-1 text-xs text-red-500"
                                    >
                                        {{ applyForm.errors.cover_letter }}
                                    </p>
                                </div>

                                <!-- CV Selection -->
                                <div>
                                    <p class="mb-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                                        <FileText class="mr-1 inline h-4 w-4 text-[#0C447C] dark:text-[#85B7EB]" />
                                        Lampirkan CV dari PaGI
                                    </p>
                                    <div v-if="myCvs.length > 0" class="space-y-2">
                                        <button
                                            v-for="cv in myCvs"
                                            :key="cv.id"
                                            type="button"
                                            class="flex w-full items-center gap-3 rounded-xl border-2 p-3 text-left transition-all duration-150"
                                            :class="selectedCvIds.includes(cv.id)
                                                ? 'border-[#0C447C] bg-[#0C447C]/5 dark:border-[#85B7EB] dark:bg-[#85B7EB]/10'
                                                : 'border-slate-100 bg-white hover:border-slate-200 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700'"
                                            @click="toggleCv(cv.id)"
                                        >
                                            <div
                                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-colors"
                                                :class="selectedCvIds.includes(cv.id)
                                                    ? 'bg-[#0C447C] text-white dark:bg-[#85B7EB] dark:text-slate-900'
                                                    : 'bg-slate-100 text-slate-400 dark:bg-zinc-800'"
                                            >
                                                <Check v-if="selectedCvIds.includes(cv.id)" class="h-4 w-4" />
                                                <FileText v-else class="h-4 w-4" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ cv.title }}</p>
                                                <p v-if="cv.template_id" class="text-[11px] text-slate-400">Template: {{ cv.template_id }}</p>
                                            </div>
                                        </button>
                                    </div>
                                    <div v-else class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-4 text-center dark:border-zinc-700 dark:bg-zinc-800/30">
                                        <FileText class="mx-auto mb-2 h-6 w-6 text-slate-300 dark:text-zinc-600" />
                                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Belum ada CV</p>
                                        <Link
                                            href="/pagi/cv"
                                            class="mt-1.5 inline-flex items-center gap-1 text-xs font-semibold text-[#0C447C] hover:underline dark:text-[#85B7EB]"
                                        >
                                            <ExternalLink class="h-3 w-3" />
                                            Buat CV di PaGI
                                        </Link>
                                    </div>
                                </div>



                                <Button
                                    type="submit"
                                    class="h-10 w-full rounded-xl bg-[#0C447C] px-6 text-sm font-semibold text-white shadow-sm shadow-[#0C447C]/20 hover:bg-[#0C447C]/90 dark:bg-[#85B7EB] dark:text-slate-900 dark:hover:bg-[#85B7EB]/90"
                                    :disabled="applyForm.processing"
                                >
                                    <Send class="mr-2 h-4 w-4" />
                                    {{ applyForm.processing ? 'Mengirim...' : 'Kirim Lamaran' }}
                                </Button>
                            </form>
                        </CardContent>
                    </Card>

                    <!-- Deadline Passed Notice -->
                    <Card
                        v-else
                        class="rounded-2xl border-red-200 bg-red-50/50 shadow-sm dark:border-red-900 dark:bg-red-950/10"
                    >
                        <CardContent class="flex items-center gap-3 p-5">
                            <Clock class="h-5 w-5 shrink-0 text-red-500" />
                            <p class="text-sm font-medium text-red-600 dark:text-red-400">
                                Lowongan ini telah melewati batas waktu pendaftaran.
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar (1 col) -->
                <div class="space-y-4">
                    <!-- Company Info -->
                    <Card class="rounded-2xl border border-slate-200/60 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <CardHeader class="pb-3">
                            <CardTitle class="text-sm font-bold text-slate-900 dark:text-white">
                                Tentang Perusahaan
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 dark:bg-zinc-800">
                                    <img
                                        v-if="job.mitra?.logo_url"
                                        :src="job.mitra.logo_url"
                                        :alt="job.mitra.nama_perusahaan"
                                        class="h-9 w-9 rounded-lg object-contain"
                                    />
                                    <Building2 v-else class="h-5 w-5 text-slate-400 dark:text-zinc-500" />
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">
                                        {{ job.mitra?.nama_perusahaan }}
                                    </p>
                                </div>
                            </div>

                            <p
                                v-if="job.mitra?.deskripsi"
                                class="text-xs leading-relaxed text-slate-500 dark:text-slate-400"
                            >
                                {{ job.mitra.deskripsi }}
                            </p>

                            <div v-if="job.mitra?.alamat" class="flex items-start gap-2 text-xs text-slate-500 dark:text-slate-400">
                                <MapPin class="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-400" />
                                <span>{{ job.mitra.alamat }}</span>
                            </div>

                            <a
                                v-if="job.mitra?.website"
                                :href="job.mitra.website"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-[#0C447C] hover:underline dark:text-[#85B7EB]"
                            >
                                Kunjungi Website →
                            </a>
                        </CardContent>
                    </Card>

                    <!-- Quick Actions -->
                    <Card class="rounded-2xl border border-slate-200/60 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <CardContent class="space-y-2 p-4">
                            <Link
                                href="/trace/jobs/my-applications"
                                class="flex w-full items-center gap-2 rounded-xl bg-slate-50 px-3 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100 dark:bg-zinc-800 dark:text-slate-300 dark:hover:bg-zinc-700"
                            >
                                <FileText class="h-4 w-4 text-slate-400" />
                                Lamaran Saya
                            </Link>
                            <Link
                                href="/trace/jobs"
                                class="flex w-full items-center gap-2 rounded-xl bg-slate-50 px-3 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100 dark:bg-zinc-800 dark:text-slate-300 dark:hover:bg-zinc-700"
                            >
                                <Briefcase class="h-4 w-4 text-slate-400" />
                                Semua Lowongan
                            </Link>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </TraceAlumniLayout>
</template>
