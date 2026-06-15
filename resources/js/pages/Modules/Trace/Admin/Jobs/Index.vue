<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import TraceAdminLayout from '@/layouts/TraceAdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Briefcase,
    Plus,
    Users,
    Calendar,
    Eye,
    Check,
    X,
    Clock,
    Pencil,
    Search,
    SlidersHorizontal,
    XCircle,
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';

interface Mitra {
    nama_perusahaan: string;
}

interface User {
    name: string;
}

interface Category {
    id: number;
    name: string;
}

interface Job {
    id: number;
    title: string;
    status: 'draft' | 'pending_review' | 'published' | 'rejected' | 'closed';
    deadline: string;
    mitra: Mitra | null;
    user: User | null;
    applicants_count: number;
    created_at: string;
}

interface PaginatedJobs {
    data: Job[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
    total: number;
}

interface Stats {
    total: number;
    pending_review: number;
    published: number;
}

const props = defineProps<{
    jobs: PaginatedJobs;
    stats: Stats;
    categories: Category[];
    filters: {
        search?: string;
        status?: string;
        category_id?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/trace/admin' },
    { title: 'Lowongan', href: '/trace/admin/jobs' },
];

/* ───── Filter state ───── */
const searchQuery = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || 'all');
const categoryFilter = ref(props.filters?.category_id || 'all');
const showFilters = ref(false);

const hasActiveFilters = computed(() => {
    return searchQuery.value || (statusFilter.value && statusFilter.value !== 'all') || (categoryFilter.value && categoryFilter.value !== 'all');
});

const statusOptions = [
    { value: 'all', label: 'Semua Status' },
    { value: 'pending_review', label: 'Pending Review' },
    { value: 'published', label: 'Published' },
    { value: 'draft', label: 'Draft' },
    { value: 'rejected', label: 'Rejected' },
    { value: 'closed', label: 'Closed' },
];

function applyFilters() {
    router.get(
        '/trace/admin/jobs',
        {
            search: searchQuery.value || undefined,
            status: statusFilter.value === 'all' ? undefined : statusFilter.value || undefined,
            category_id: categoryFilter.value === 'all' ? undefined : categoryFilter.value || undefined,
        },
        { preserveState: true, replace: true },
    );
}

const debouncedSearch = useDebounceFn(() => {
    applyFilters();
}, 400);

watch([statusFilter, categoryFilter], () => {
    applyFilters();
});

function clearFilters() {
    searchQuery.value = '';
    statusFilter.value = 'all';
    categoryFilter.value = 'all';
    applyFilters();
}

/* ───── Status badges ───── */
const statusConfig: Record<string, { label: string; classes: string }> = {
    draft: {
        label: 'Draft',
        classes: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
    },
    pending_review: {
        label: 'Menunggu Review',
        classes: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950 dark:text-amber-400 dark:border-amber-800',
    },
    published: {
        label: 'Published',
        classes: 'bg-green-50 text-green-700 border-green-200 dark:bg-green-950 dark:text-green-400 dark:border-green-800',
    },
    rejected: {
        label: 'Ditolak',
        classes: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950 dark:text-red-400 dark:border-red-800',
    },
    closed: {
        label: 'Ditutup',
        classes: 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700',
    },
};

/* ───── Helpers ───── */
function formatDate(dateStr: string): string {
    if (!dateStr) return '-';
    try {
        return new Date(dateStr).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        });
    } catch {
        return dateStr;
    }
}

function getSource(job: Job): string {
    return job.mitra?.nama_perusahaan ?? 'Admin - FMIKOM';
}

/* ───── Actions ───── */
const processing = ref<number | null>(null);

function approveJob(id: number) {
    if (!confirm('Setujui lowongan ini?')) return;
    processing.value = id;
    router.put(`/trace/admin/jobs/${id}/approve`, {}, {
        preserveScroll: true,
        onFinish: () => { processing.value = null; },
    });
}

function rejectJob(id: number) {
    if (!confirm('Tolak lowongan ini?')) return;
    processing.value = id;
    router.put(`/trace/admin/jobs/${id}/reject`, {}, {
        preserveScroll: true,
        onFinish: () => { processing.value = null; },
    });
}
</script>

<template>
    <TraceAdminLayout title="Manajemen Lowongan" :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-7xl space-y-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-1.5 text-violet-600 dark:text-violet-400 mb-1">
                        <Briefcase class="h-4 w-4" />
                        <span class="text-[10px] font-black uppercase tracking-widest">Manajemen Lowongan</span>
                    </div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-800 dark:text-white">Lowongan Kerja</h1>
                    <div class="flex items-center gap-2 mt-1.5">
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Total {{ stats.total }} lowongan
                        </p>
                        <span
                            v-if="stats.pending_review > 0"
                            class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-[11px] font-bold text-amber-700 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-400"
                        >
                            <Clock class="h-3 w-3" />
                            {{ stats.pending_review }} pending review
                        </span>
                    </div>
                </div>
                <Button as-child class="bg-violet-600 hover:bg-violet-700 text-white rounded-xl shadow-sm">
                    <Link href="/trace/admin/jobs/create" class="inline-flex items-center gap-2">
                        <Plus class="h-4 w-4" />
                        Buat Lowongan
                    </Link>
                </Button>
            </div>

            <!-- Search & Filters -->
            <Card class="rounded-2xl border-slate-100 dark:border-slate-800 shadow-xs">
                <CardContent class="p-4">
                    <div class="flex flex-col gap-3">
                        <!-- Search Row -->
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <div class="relative flex-1">
                                <Search class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                <Input
                                    v-model="searchQuery"
                                    placeholder="Cari lowongan atau nama perusahaan..."
                                    class="h-10 rounded-xl border-slate-200 bg-slate-50/50 pl-10 text-sm dark:border-slate-700 dark:bg-slate-800/50"
                                    @input="debouncedSearch"
                                />
                            </div>
                            <div class="flex items-center gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="h-10 rounded-xl border-slate-200 text-slate-600 dark:border-slate-700 dark:text-slate-400 sm:hidden"
                                    @click="showFilters = !showFilters"
                                >
                                    <SlidersHorizontal class="h-4 w-4 mr-1.5" />
                                    Filter
                                </Button>
                                <Button
                                    v-if="hasActiveFilters"
                                    variant="ghost"
                                    size="sm"
                                    class="h-10 rounded-xl text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/20"
                                    @click="clearFilters"
                                >
                                    <XCircle class="h-4 w-4 mr-1.5" />
                                    Hapus Filter
                                </Button>
                            </div>
                        </div>

                        <!-- Filter Dropdowns -->
                        <div
                            class="flex flex-col gap-3 sm:flex-row sm:items-center"
                            :class="{ 'hidden sm:flex': !showFilters }"
                        >
                            <Select v-model="statusFilter">
                                <SelectTrigger class="h-10 w-full sm:w-[180px] rounded-xl border-slate-200 bg-slate-50/50 text-sm font-medium dark:border-slate-700 dark:bg-slate-800/50">
                                    <SelectValue placeholder="Semua Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                                        {{ opt.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>

                            <Select v-model="categoryFilter">
                                <SelectTrigger class="h-10 w-full sm:w-[200px] rounded-xl border-slate-200 bg-slate-50/50 text-sm font-medium dark:border-slate-700 dark:bg-slate-800/50">
                                    <SelectValue placeholder="Semua Kategori" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Kategori</SelectItem>
                                    <SelectItem v-for="cat in categories" :key="cat.id" :value="String(cat.id)">
                                        {{ cat.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Jobs Table -->
            <Card class="rounded-2xl border-slate-100 dark:border-slate-800 shadow-xs overflow-hidden">
                <CardContent class="p-0">
                    <div v-if="jobs.data.length > 0" class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                                    <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400">Lowongan</th>
                                    <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400">Sumber</th>
                                    <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400">Status</th>
                                    <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400">Pelamar</th>
                                    <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400">Tanggal</th>
                                    <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                                <tr
                                    v-for="job in jobs.data"
                                    :key="job.id"
                                    class="group transition-colors hover:bg-violet-50/30 dark:hover:bg-violet-950/10"
                                >
                                    <!-- Lowongan -->
                                    <td class="px-5 py-4">
                                        <p class="text-sm font-bold text-slate-800 dark:text-white group-hover:text-violet-700 dark:group-hover:text-violet-400 transition-colors">
                                            {{ job.title }}
                                        </p>
                                        <div class="flex items-center gap-1.5 mt-0.5 text-xs text-slate-400 dark:text-slate-500">
                                            <Calendar class="h-3 w-3" />
                                            Deadline: {{ formatDate(job.deadline) }}
                                        </div>
                                    </td>

                                    <!-- Sumber -->
                                    <td class="px-5 py-4">
                                        <p class="text-sm text-slate-600 dark:text-slate-400">
                                            {{ getSource(job) }}
                                        </p>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-5 py-4">
                                        <span
                                            class="inline-flex items-center rounded-lg border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider"
                                            :class="statusConfig[job.status]?.classes ?? statusConfig.draft.classes"
                                        >
                                            {{ statusConfig[job.status]?.label ?? job.status }}
                                        </span>
                                    </td>

                                    <!-- Pelamar -->
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-1.5">
                                            <Users class="h-3.5 w-3.5 text-slate-400" />
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                                {{ job.applicants_count }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Tanggal -->
                                    <td class="px-5 py-4">
                                        <span class="text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                            {{ formatDate(job.created_at) }}
                                        </span>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <Button as-child variant="ghost" size="sm" class="text-violet-600 hover:text-violet-700 hover:bg-violet-50 dark:text-violet-400 dark:hover:bg-violet-950/30 rounded-lg">
                                                <Link :href="`/trace/admin/jobs/${job.id}`" class="inline-flex items-center gap-1.5">
                                                    <Eye class="h-3.5 w-3.5" />
                                                    Detail
                                                </Link>
                                            </Button>
                                            <Button as-child variant="ghost" size="sm" class="text-slate-600 hover:text-violet-700 hover:bg-violet-50 dark:text-slate-400 dark:hover:bg-violet-950/30 rounded-lg">
                                                <Link :href="`/trace/admin/jobs/${job.id}/edit`" class="inline-flex items-center gap-1.5">
                                                    <Pencil class="h-3.5 w-3.5" />
                                                    Edit
                                                </Link>
                                            </Button>

                                            <!-- Quick approve / reject for pending_review -->
                                            <template v-if="job.status === 'pending_review'">
                                                <Button
                                                    size="sm"
                                                    class="h-7 rounded-lg bg-green-600 hover:bg-green-700 text-white text-xs gap-1 px-2.5"
                                                    :disabled="processing === job.id"
                                                    @click="approveJob(job.id)"
                                                >
                                                    <Check class="h-3 w-3" />
                                                    Approve
                                                </Button>
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    class="h-7 rounded-lg border-red-200 text-red-600 hover:bg-red-50 hover:text-red-700 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-950 text-xs gap-1 px-2.5"
                                                    :disabled="processing === job.id"
                                                    @click="rejectJob(job.id)"
                                                >
                                                    <X class="h-3 w-3" />
                                                    Reject
                                                </Button>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="flex flex-col items-center justify-center py-16 text-center">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 mb-4">
                            <Briefcase class="h-7 w-7 text-slate-300 dark:text-slate-600" />
                        </div>
                        <p class="text-sm font-bold text-slate-500 dark:text-slate-400 mb-1">
                            {{ hasActiveFilters ? 'Tidak ada lowongan ditemukan' : 'Belum ada lowongan' }}
                        </p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mb-4">
                            {{ hasActiveFilters ? 'Coba ubah filter pencarian Anda.' : 'Buat lowongan pertama atau tunggu mitra mengirimkan lowongan.' }}
                        </p>
                        <Button v-if="hasActiveFilters" size="sm" variant="outline" class="rounded-xl" @click="clearFilters">
                            <XCircle class="h-3.5 w-3.5 mr-1.5" />
                            Hapus Filter
                        </Button>
                        <Button v-else as-child size="sm" class="bg-violet-600 hover:bg-violet-700 text-white rounded-xl">
                            <Link href="/trace/admin/jobs/create" class="inline-flex items-center gap-1.5">
                                <Plus class="h-3.5 w-3.5" />
                                Buat Lowongan
                            </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="jobs.last_page > 1" class="flex items-center justify-between">
                <p class="text-xs text-slate-400 dark:text-slate-500">
                    Menampilkan halaman {{ jobs.current_page }} dari {{ jobs.last_page }}
                    ({{ jobs.total }} lowongan)
                </p>
                <nav class="flex items-center gap-1">
                    <template v-for="(link, idx) in jobs.links" :key="idx">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2.5 text-xs font-bold transition-colors"
                            :class="link.active
                                ? 'bg-violet-600 text-white shadow-sm'
                                : 'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800'"
                            v-html="link.label"
                            preserve-scroll
                        />
                        <span
                            v-else
                            class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2.5 text-xs text-slate-300 dark:text-slate-600"
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </div>
        </div>
    </TraceAdminLayout>
</template>
