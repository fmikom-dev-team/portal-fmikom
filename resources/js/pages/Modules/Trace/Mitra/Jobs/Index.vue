<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Briefcase, Plus, Users, Calendar, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import TraceMitraLayout from '@/layouts/TraceMitraLayout.vue';
import { TPageHeader, TStatusBadge, TEmptyState } from '@/components/trace';
import { Button } from '@/components/ui/button';

interface Job {
    id: number;
    title: string;
    description: string;
    status: 'draft' | 'pending_review' | 'published' | 'rejected' | 'closed';
    rejection_reason: string | null;
    deadline: string;
    experience_level: string;
    location_type: string;
    tipe_kerja: string;
    salary_min: number | null;
    salary_max: number | null;
    is_salary_visible: boolean;
    applicants_count: number;
    category: { nama: string } | null;
}

interface PaginatedJobs {
    data: Job[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
}

const props = defineProps<{
    jobs: PaginatedJobs;
}>();

const statusConfig: Record<string, { label: string; class: string }> = {
    draft: {
        label: 'Draft',
        class: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
    },
    pending_review: {
        label: 'Menunggu Review',
        class: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950 dark:text-amber-400 dark:border-amber-800',
    },
    published: {
        label: 'Aktif',
        class: 'bg-green-50 text-green-700 border-green-200 dark:bg-green-950 dark:text-green-400 dark:border-green-800',
    },
    rejected: {
        label: 'Ditolak',
        class: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950 dark:text-red-400 dark:border-red-800',
    },
    closed: {
        label: 'Ditutup',
        class: 'bg-slate-50 text-slate-600 border-slate-300 dark:bg-slate-900 dark:text-slate-400 dark:border-slate-700',
    },
};

function formatDate(dateStr: string): string {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}
</script>

<template>
    <TraceMitraLayout title="Lowongan Kerja">
        <TPageHeader title="Lowongan Saya" description="Kelola lowongan kerja perusahaan Anda" :icon="Briefcase" class="mb-6">
            <template #actions>
                <Link href="/trace/open-job/jobs-listings/create">
                    <Button class="gap-2 rounded-xl bg-[#0C447C] hover:bg-[#0C447C]/90 text-white shadow-md shadow-[#0C447C]/20">
                        <Plus class="h-4 w-4" />
                        Buat Lowongan Baru
                    </Button>
                </Link>
            </template>
        </TPageHeader>

        <!-- Job List -->
        <div v-if="jobs.data.length > 0" class="space-y-3">
            <Link
                v-for="job in jobs.data"
                :key="job.id"
                :href="`/trace/open-job/jobs-listings/${job.id}`"
                class="block"
            >
                <div class="rounded-2xl border border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-sm transition-all duration-200 hover:shadow-md hover:border-[#0C447C]/30 dark:hover:border-[#85B7EB]/30 cursor-pointer">
                    <div class="p-4 sm:p-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <!-- Left: Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2.5 flex-wrap">
                                    <h3 class="text-base font-semibold text-slate-900 dark:text-white truncate">
                                        {{ job.title }}
                                    </h3>
                                    <TStatusBadge :status="job.status" :label="statusConfig[job.status]?.label" size="sm" />
                                </div>

                                <!-- Rejection Reason -->
                                <p
                                    v-if="job.status === 'rejected' && job.rejection_reason"
                                    class="mt-1.5 text-xs text-red-500 dark:text-red-400 italic"
                                >
                                    <span class="font-semibold not-italic">Alasan:</span> {{ job.rejection_reason }}
                                </p>

                                <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-slate-500 dark:text-slate-400">
                                    <span v-if="job.category" class="flex items-center gap-1">
                                        <Briefcase class="h-3.5 w-3.5" />
                                        {{ job.category.nama }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <Calendar class="h-3.5 w-3.5" />
                                        Deadline: {{ formatDate(job.deadline) }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <Users class="h-3.5 w-3.5" />
                                        {{ job.applicants_count }} pelamar
                                    </span>
                                </div>
                            </div>

                            <!-- Right: Arrow -->
                            <ChevronRight class="hidden sm:block h-5 w-5 text-slate-300 dark:text-slate-600 shrink-0" />
                        </div>
                    </div>
                </div>
            </Link>
        </div>

        <TEmptyState
            v-else
            :icon="Briefcase"
            title="Belum ada lowongan"
            description="Buat lowongan kerja pertama Anda untuk mulai menerima lamaran dari alumni."
            actionLabel="Buat Lowongan Baru"
            actionHref="/trace/open-job/jobs-listings/create"
        />

        <!-- Pagination -->
        <div v-if="jobs.last_page > 1" class="mt-6 flex items-center justify-center gap-1">
            <template v-for="(link, idx) in jobs.links" :key="idx">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    class="inline-flex h-9 min-w-[36px] items-center justify-center rounded-lg border px-3 text-sm font-medium transition-colors"
                    :class="link.active
                        ? 'bg-violet-600 text-white border-violet-600 shadow-sm'
                        : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 dark:bg-slate-900 dark:text-slate-300 dark:border-slate-700 dark:hover:bg-slate-800'"
                    v-html="link.label"
                />
                <span
                    v-else
                    class="inline-flex h-9 min-w-[36px] items-center justify-center rounded-lg border border-slate-100 bg-slate-50 px-3 text-sm text-slate-400 dark:bg-slate-900 dark:border-slate-800 dark:text-slate-600 cursor-not-allowed"
                    v-html="link.label"
                />
            </template>
        </div>
    </TraceMitraLayout>
</template>
