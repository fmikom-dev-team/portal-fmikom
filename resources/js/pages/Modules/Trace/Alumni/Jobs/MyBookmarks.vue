<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
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
import { TPageHeader, TEmptyState, TPagination } from '@/components/Trace';
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
        onError: () => { toast.error('Gagal menghapus bookmark.'); },
    });
}
</script>

<template>
    <TraceAlumniLayout title="Lowongan Tersimpan">
        <div class="mx-auto space-y-6">
            <!-- Header -->
            <TPageHeader title="Lowongan Tersimpan" :description="`${bookmarks.data.length} lowongan disimpan`" :icon="Bookmark">
                <template #actions>
                    <Link href="/trace/jobs">
                        <Button variant="outline" class="rounded-xl text-sm border-[#0C447C]/20 text-[#0C447C] hover:bg-[#0C447C]/5 dark:border-[#85B7EB]/30 dark:text-[#85B7EB] dark:hover:bg-[#85B7EB]/10">
                            <Briefcase class="mr-1.5 h-4 w-4" />
                            Cari Lowongan
                        </Button>
                    </Link>
                </template>
            </TPageHeader>

            <!-- Bookmark List -->
            <div v-if="bookmarks.data.length" class="space-y-3">
                <Card
                    v-for="bm in bookmarks.data"
                    :key="bm.id"
                    class="group rounded-2xl border border-slate-200/60 shadow-sm transition-all duration-200 hover:border-[#85B7EB]/40 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-[#85B7EB]/30"
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
                                            class="text-[15px] font-bold text-slate-900 transition-colors hover:text-[#0C447C] dark:text-white dark:hover:text-[#85B7EB]"
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
                                            <span v-if="bm.job_listing.is_salary_visible && (bm.job_listing.salary_min || bm.job_listing.salary_max)" class="font-medium text-[#0C447C] dark:text-[#85B7EB]">
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
                                    class="inline-flex items-center gap-1 rounded-lg bg-[#0C447C]/5 px-3 py-1.5 text-xs font-medium text-[#0C447C] transition-colors hover:bg-[#0C447C]/10 dark:bg-[#85B7EB]/10 dark:text-[#85B7EB] dark:hover:bg-[#85B7EB]/20"
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
            <TEmptyState
                v-else
                :icon="Bookmark"
                title="Belum ada lowongan tersimpan"
                description="Simpan lowongan yang menarik agar mudah ditemukan nanti"
                action-label="Jelajahi Lowongan"
                action-href="/trace/jobs"
            />

            <!-- Pagination -->
            <TPagination v-if="bookmarks.links && bookmarks.links.length > 3" :links="bookmarks.links" />
        </div>
    </TraceAlumniLayout>
</template>
