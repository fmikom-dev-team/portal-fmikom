<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import TraceAdminLayout from '@/layouts/TraceAdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import {
    TPageHeader,
    TFilterBar,
    TDataTable,
    TStatusBadge,
    TPagination,
    TEmptyState,
    TConfirmDialog,
} from '@/components/trace';
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
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface Mitra {
    nama_perusahaan: string;
}

interface User {
    name: string;
}

interface Category {
    id: number;
    nama: string;
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

/* ───── Filter config for TFilterBar ───── */
const filterConfig = computed(() => [
    {
        key: 'status',
        label: 'Status',
        options: [
            { value: 'pending_review', label: 'Pending Review' },
            { value: 'published', label: 'Published' },
            { value: 'draft', label: 'Draft' },
            { value: 'rejected', label: 'Rejected' },
            { value: 'closed', label: 'Closed' },
        ],
    },
    {
        key: 'category_id',
        label: 'Kategori',
        options: props.categories.map(c => ({ value: String(c.id), label: c.nama })),
    },
]);

const currentFilters = computed(() => {
    const f: Record<string, string> = {};
    if (props.filters?.search) f.search = props.filters.search;
    if (props.filters?.status) f.status = props.filters.status;
    if (props.filters?.category_id) f.category_id = props.filters.category_id;
    return f;
});

function onFilterChange(filters: Record<string, string>) {
    router.get('/trace/admin/jobs', {
        search: filters.search || undefined,
        status: filters.status || undefined,
        category_id: filters.category_id || undefined,
    }, { preserveState: true, replace: true });
}

/* ───── Table columns ───── */
const tableColumns = [
    { key: 'title', label: 'Lowongan' },
    { key: 'source', label: 'Sumber' },
    { key: 'status', label: 'Status' },
    { key: 'applicants_count', label: 'Pelamar' },
    { key: 'created_at', label: 'Tanggal' },
    { key: 'actions', label: 'Aksi', class: 'text-right', headerClass: 'text-right' },
];

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

const confirmDialog = ref({
    open: false,
    title: '',
    description: '',
    jobId: null as number | null,
    action: '' as 'approve' | 'reject',
});

function approveJob(id: number) {
    confirmDialog.value = {
        open: true,
        title: 'Setujui Lowongan',
        description: 'Setujui lowongan ini untuk dipublikasikan?',
        jobId: id,
        action: 'approve',
    };
}

function rejectJob(id: number) {
    confirmDialog.value = {
        open: true,
        title: 'Tolak Lowongan',
        description: 'Tolak lowongan ini?',
        jobId: id,
        action: 'reject',
    };
}

function handleConfirm() {
    if (!confirmDialog.value.jobId) return;
    const id = confirmDialog.value.jobId;
    processing.value = id;
    const url = confirmDialog.value.action === 'approve'
        ? `/trace/admin/jobs/${id}/approve`
        : `/trace/admin/jobs/${id}/reject`;
    router.put(url, {}, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = null;
            confirmDialog.value.open = false;
        },
    });
}
</script>

<template>
    <TraceAdminLayout title="Manajemen Lowongan" :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-7xl space-y-6">
            <!-- Header -->
            <TPageHeader
                title="Lowongan Kerja"
                :description="`Total ${stats.total} lowongan`"
                :icon="Briefcase"
            >
                <template #actions>
                    <span
                        v-if="stats.pending_review > 0"
                        class="inline-flex items-center gap-1 rounded-full border border-[#EF9F27]/30 bg-[#EF9F27]/10 px-2.5 py-0.5 text-[11px] font-bold text-[#EF9F27] dark:border-[#FAC775]/30 dark:text-[#FAC775]"
                    >
                        <Clock class="h-3 w-3" />
                        {{ stats.pending_review }} pending review
                    </span>
                    <Button as-child class="bg-[#0C447C] hover:bg-[#0C447C]/90 text-white rounded-xl shadow-sm">
                        <Link href="/trace/admin/jobs/create" class="inline-flex items-center gap-2">
                            <Plus class="h-4 w-4" />
                            Buat Lowongan
                        </Link>
                    </Button>
                </template>
            </TPageHeader>

            <!-- Filter Bar -->
            <TFilterBar
                search-placeholder="Cari lowongan atau nama perusahaan..."
                :filters="filterConfig"
                :model-value="currentFilters"
                @filter-change="onFilterChange"
            />

            <!-- Jobs Table -->
            <TDataTable
                :columns="tableColumns"
                :data="jobs.data"
                :hoverable="true"
            >
                <template #cell-title="{ row }">
                    <p class="text-sm font-bold text-slate-800 dark:text-white group-hover:text-[#0C447C] dark:group-hover:text-[#85B7EB] transition-colors">
                        {{ row.title }}
                    </p>
                    <div class="flex items-center gap-1.5 mt-0.5 text-xs text-slate-400 dark:text-slate-500">
                        <Calendar class="h-3 w-3" />
                        Deadline: {{ formatDate(row.deadline) }}
                    </div>
                </template>

                <template #cell-source="{ row }">
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        {{ getSource(row) }}
                    </p>
                </template>

                <template #cell-status="{ row }">
                    <TStatusBadge :status="row.status" />
                </template>

                <template #cell-applicants_count="{ row }">
                    <div class="flex items-center gap-1.5">
                        <Users class="h-3.5 w-3.5 text-slate-400" />
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                            {{ row.applicants_count }}
                        </span>
                    </div>
                </template>

                <template #cell-created_at="{ row }">
                    <span class="text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">
                        {{ formatDate(row.created_at) }}
                    </span>
                </template>

                <template #cell-actions="{ row }">
                    <div class="flex items-center justify-end gap-1.5">
                        <Button as-child variant="ghost" size="sm" class="text-[#0C447C] hover:text-[#0C447C]/80 hover:bg-[#0C447C]/5 dark:text-[#85B7EB] dark:hover:bg-[#85B7EB]/10 rounded-lg">
                            <Link :href="`/trace/admin/jobs/${row.id}`" class="inline-flex items-center gap-1.5">
                                <Eye class="h-3.5 w-3.5" />
                                Detail
                            </Link>
                        </Button>
                        <Button as-child variant="ghost" size="sm" class="text-slate-600 hover:text-[#0C447C] hover:bg-[#0C447C]/5 dark:text-slate-400 dark:hover:bg-[#85B7EB]/10 rounded-lg">
                            <Link :href="`/trace/admin/jobs/${row.id}/edit`" class="inline-flex items-center gap-1.5">
                                <Pencil class="h-3.5 w-3.5" />
                                Edit
                            </Link>
                        </Button>

                        <!-- Quick approve / reject for pending_review -->
                        <template v-if="row.status === 'pending_review'">
                            <Button
                                size="sm"
                                class="h-7 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs gap-1 px-2.5"
                                :disabled="processing === row.id"
                                @click="approveJob(row.id)"
                            >
                                <Check class="h-3 w-3" />
                                Approve
                            </Button>
                            <Button
                                size="sm"
                                variant="outline"
                                class="h-7 rounded-lg border-red-200 text-red-600 hover:bg-red-50 hover:text-red-700 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-950 text-xs gap-1 px-2.5"
                                :disabled="processing === row.id"
                                @click="rejectJob(row.id)"
                            >
                                <X class="h-3 w-3" />
                                Reject
                            </Button>
                        </template>
                    </div>
                </template>

                <template #empty>
                    <TEmptyState
                        :icon="Briefcase"
                        title="Belum ada lowongan"
                        description="Buat lowongan pertama atau tunggu mitra mengirimkan lowongan."
                        action-label="Buat Lowongan"
                        action-href="/trace/admin/jobs/create"
                    />
                </template>
            </TDataTable>

            <!-- Pagination -->
            <TPagination :links="jobs.links" />
        </div>

        <!-- Confirm Dialog -->
        <TConfirmDialog
            :open="confirmDialog.open"
            :title="confirmDialog.title"
            :description="confirmDialog.description"
            :variant="confirmDialog.action === 'approve' ? 'info' : 'danger'"
            :confirm-label="confirmDialog.action === 'approve' ? 'Ya, Setujui' : 'Ya, Tolak'"
            :loading="processing !== null"
            @update:open="(v) => confirmDialog.open = v"
            @confirm="handleConfirm"
        />
    </TraceAdminLayout>
</template>
