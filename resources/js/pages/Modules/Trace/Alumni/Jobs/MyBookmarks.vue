<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
    Bookmark,
    Briefcase,
    Building2,
    Calendar,
    MapPin,
    Trash2,
    ExternalLink,
    BookmarkX,
} from 'lucide-vue-next';
import TraceAlumniLayout from '@/layouts/TraceAlumniLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
} from '@/components/ui/card';

interface JobListing {
    id: number;
    title: string;
    deadline: string | null;
    experience_level: string;
    location_type: string;
    tipe_kerja: string;
    salary_min: number | null;
    salary_max: number | null;
    is_salary_visible: boolean;
    category: { nama: string } | null;
    mitra: { nama_perusahaan: string; logo_path: string | null } | null;
}

interface BookmarkItem {
    id: number;
    job_id: number;
    created_at: string;
    job_listing: JobListing | null;
}

interface PaginatedBookmarks {
    data: BookmarkItem[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

const props = defineProps<{
    bookmarks: PaginatedBookmarks;
}>();

const experienceLabels: Record<string, string> = {
    fresh_graduate: 'Fresh Graduate',
    junior: 'Junior',
    mid_level: 'Mid Level',
    senior: 'Senior',
    internship: 'Internship',
};

const tipeKerjaLabels: Record<string, string> = {
    full_time: 'Full Time',
    part_time: 'Part Time',
    magang: 'Magang',
    freelance: 'Freelance',
};

const locationLabels: Record<string, string> = {
    onsite: 'Onsite',
    remote: 'Remote',
    hybrid: 'Hybrid',
};

function formatSalary(min: number | null, max: number | null): string {
    if (!min && !max) return '-';
    const fmt = (n: number) => new Intl.NumberFormat('id-ID').format(n);
    if (min && max) return `Rp ${fmt(min)} - ${fmt(max)}`;
    if (min) return `Mulai Rp ${fmt(min)}`;
    return `Hingga Rp ${fmt(max!)}`;
}

function formatDate(d: string): string {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

function removeBookmark(jobId: number) {
    router.post(`/trace/jobs/${jobId}/bookmark`, {}, {
        preserveScroll: true,
    });
}
</script>

<template>
    <TraceAlumniLayout title="Lowongan Tersimpan">
        <!-- Header -->
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                    <Bookmark class="mr-2 inline h-6 w-6 text-emerald-600" />
                    Lowongan Tersimpan
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ bookmarks.data.length }} lowongan disimpan
                </p>
            </div>
            <Link href="/trace/jobs">
                <Button variant="outline" class="rounded-xl text-sm">
                    <Briefcase class="mr-1.5 h-4 w-4" />
                    Cari Lowongan
                </Button>
            </Link>
        </div>

        <!-- Bookmark List -->
        <div v-if="bookmarks.data.length" class="space-y-3">
            <Card
                v-for="bm in bookmarks.data"
                :key="bm.id"
                class="group rounded-2xl border-slate-100 shadow-sm transition-all duration-200 hover:border-emerald-200 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-emerald-800"
            >
                <CardContent class="p-5">
                    <div v-if="bm.job_listing" class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <!-- Job Info -->
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 dark:bg-zinc-800">
                                    <Building2 v-if="bm.job_listing.mitra" class="h-4.5 w-4.5 text-slate-400 dark:text-zinc-500" />
                                    <Briefcase v-else class="h-4.5 w-4.5 text-slate-400 dark:text-zinc-500" />
                                </div>
                                <div class="min-w-0">
                                    <Link
                                        :href="`/trace/jobs/${bm.job_listing.id}`"
                                        class="text-[15px] font-bold text-slate-900 transition-colors hover:text-emerald-700 dark:text-white dark:hover:text-emerald-400"
                                    >
                                        {{ bm.job_listing.title }}
                                    </Link>
                                    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                                        {{ bm.job_listing.mitra?.nama_perusahaan ?? 'FMIKOM' }}
                                    </p>

                                    <!-- Tags -->
                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        <Badge v-if="bm.job_listing.category" variant="outline" class="rounded-md text-[10px] font-medium">
                                            {{ bm.job_listing.category.nama }}
                                        </Badge>
                                        <Badge variant="outline" class="rounded-md text-[10px] font-medium text-blue-600 border-blue-200 dark:text-blue-400">
                                            {{ experienceLabels[bm.job_listing.experience_level] ?? bm.job_listing.experience_level }}
                                        </Badge>
                                        <Badge variant="outline" class="rounded-md text-[10px] font-medium">
                                            <MapPin class="mr-0.5 h-3 w-3" />
                                            {{ locationLabels[bm.job_listing.location_type] ?? bm.job_listing.location_type }}
                                        </Badge>
                                        <Badge variant="outline" class="rounded-md text-[10px] font-medium">
                                            {{ tipeKerjaLabels[bm.job_listing.tipe_kerja] ?? bm.job_listing.tipe_kerja }}
                                        </Badge>
                                    </div>

                                    <!-- Salary & Deadline -->
                                    <div class="mt-2 flex items-center gap-3 text-xs text-slate-400 dark:text-zinc-500">
                                        <span v-if="bm.job_listing.is_salary_visible && (bm.job_listing.salary_min || bm.job_listing.salary_max)" class="font-medium text-emerald-600 dark:text-emerald-400">
                                            {{ formatSalary(bm.job_listing.salary_min, bm.job_listing.salary_max) }}
                                        </span>
                                        <span v-if="bm.job_listing.deadline" class="flex items-center gap-1">
                                            <Calendar class="h-3 w-3" />
                                            Deadline: {{ formatDate(bm.job_listing.deadline) }}
                                        </span>
                                        <span class="text-slate-300 dark:text-zinc-700">·</span>
                                        <span>Disimpan {{ formatDate(bm.created_at) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex shrink-0 items-center gap-2">
                            <Link
                                :href="`/trace/jobs/${bm.job_listing.id}`"
                                class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 transition-colors hover:bg-emerald-100 dark:bg-emerald-950 dark:text-emerald-400 dark:hover:bg-emerald-900"
                            >
                                <ExternalLink class="h-3 w-3" />
                                Lihat
                            </Link>
                            <button
                                @click="removeBookmark(bm.job_listing.id)"
                                class="inline-flex items-center gap-1 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 transition-colors hover:bg-red-100 dark:bg-red-950 dark:text-red-400 dark:hover:bg-red-900"
                                title="Hapus dari bookmark"
                            >
                                <BookmarkX class="h-3 w-3" />
                                Hapus
                            </button>
                        </div>
                    </div>

                    <!-- Deleted job fallback -->
                    <div v-else class="flex items-center gap-3 text-sm text-slate-400">
                        <Trash2 class="h-4 w-4" />
                        <span>Lowongan ini sudah tidak tersedia</span>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Empty State -->
        <div v-else class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 py-16 dark:border-zinc-700">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-zinc-800">
                <Bookmark class="h-7 w-7 text-slate-400 dark:text-zinc-500" />
            </div>
            <h3 class="mt-4 text-base font-bold text-slate-700 dark:text-slate-300">Belum ada lowongan tersimpan</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Simpan lowongan yang menarik agar mudah ditemukan nanti</p>
            <Link href="/trace/jobs" class="mt-4">
                <Button class="rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm">
                    Jelajahi Lowongan
                </Button>
            </Link>
        </div>

        <!-- Pagination -->
        <div v-if="bookmarks.links && bookmarks.links.length > 3" class="mt-6 flex flex-wrap justify-center gap-1">
            <template v-for="link in bookmarks.links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    class="rounded-lg px-3 py-1.5 text-xs font-medium transition-colors"
                    :class="link.active
                        ? 'bg-emerald-600 text-white'
                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-zinc-800 dark:text-slate-400 dark:hover:bg-zinc-700'"
                    v-html="link.label"
                />
                <span
                    v-else
                    class="rounded-lg px-3 py-1.5 text-xs text-slate-300 dark:text-zinc-600"
                    v-html="link.label"
                />
            </template>
        </div>
    </TraceAlumniLayout>
</template>
