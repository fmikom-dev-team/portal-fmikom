<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import TraceAdminLayout from '@/layouts/TraceAdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { TPageHeader, TDataTable, TFilterBar, TStatusBadge, TPagination, TEmptyState, TConfirmDialog } from '@/components/trace';
import {
    CalendarDays,
    MapPin,
    Plus,
    Users,
    Eye,
    Pencil,
    Trash2,
    CalendarCheck,
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';

interface Event {
    id: number;
    title: string;
    event_date: string;
    location: string;
    status: 'draft' | 'published' | 'closed';
    registrations_count: number;
    max_participants: number | null;
}

interface PaginatedEvents {
    data: Event[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    events: PaginatedEvents;
    filters: {
        search?: string;
        status?: string;
        date_from?: string;
        date_to?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/trace/admin' },
    { title: 'Events', href: '/trace/admin/events' },
];

/* ───── Filter state ───── */
const searchQuery = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || 'all');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');
const confirmDeleteId = ref<number | null>(null);

const hasActiveFilters = computed(() => {
    return searchQuery.value || (statusFilter.value && statusFilter.value !== 'all') || dateFrom.value || dateTo.value;
});

const statusOptions = [
    { value: 'all', label: 'Semua Status' },
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
    { value: 'closed', label: 'Closed' },
];

function applyFilters() {
    router.get(
        '/trace/admin/events',
        {
            search: searchQuery.value || undefined,
            status: statusFilter.value === 'all' ? undefined : statusFilter.value || undefined,
            date_from: dateFrom.value || undefined,
            date_to: dateTo.value || undefined,
        },
        { preserveState: true, replace: true },
    );
}

const debouncedSearch = useDebounceFn(() => {
    applyFilters();
}, 400);

watch([statusFilter, dateFrom, dateTo], () => {
    applyFilters();
});

function clearFilters() {
    searchQuery.value = '';
    statusFilter.value = 'all';
    dateFrom.value = '';
    dateTo.value = '';
    applyFilters();
}

const onFilterBarChange = (filters: Record<string, string>) => {
    searchQuery.value = filters.search ?? '';
    statusFilter.value = filters.status ?? 'all';
    applyFilters();
};

/* ───── Delete event ───── */
function deleteEvent() {
    if (confirmDeleteId.value) {
        router.delete(`/trace/admin/events/${confirmDeleteId.value}`, {
            onFinish: () => { confirmDeleteId.value = null; },
        });
    }
}

const formatDate = (dateStr: string) => {
    try {
        return new Date(dateStr).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        });
    } catch {
        return dateStr;
    }
};
</script>

<template>
    <TraceAdminLayout title="Events" :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-7xl space-y-6">
            <TPageHeader
                title="Events"
                description="Kelola event dan kegiatan alumni."
                :icon="CalendarCheck"
            >
                <template #actions>
                    <Button as-child class="bg-[#0C447C] hover:bg-[#0C447C]/90 text-white rounded-xl shadow-sm">
                        <Link href="/trace/admin/events/create" class="inline-flex items-center gap-2">
                            <Plus class="h-4 w-4" />
                            Buat Event
                        </Link>
                    </Button>
                </template>
            </TPageHeader>

            <TFilterBar
                search-placeholder="Cari event berdasarkan judul..."
                :filters="[
                    {
                        key: 'status',
                        label: 'Status',
                        options: [
                            { value: 'draft', label: 'Draft' },
                            { value: 'published', label: 'Published' },
                            { value: 'closed', label: 'Closed' },
                        ],
                    },
                ]"
                :model-value="{ search: searchQuery, status: statusFilter }"
                @filter-change="onFilterBarChange"
            />

            <!-- Date Range Filters -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1 sm:flex-none">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">Dari</label>
                    <input v-model="dateFrom" @change="applyFilters" type="date" class="w-full sm:w-[180px] h-9 rounded-xl border border-slate-200 bg-slate-50/50 px-3 text-sm font-medium text-slate-700 focus:outline-none focus:border-[#0C447C] focus:ring-1 focus:ring-[#85B7EB]/30 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-300" />
                </div>
                <div class="flex-1 sm:flex-none">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">Sampai</label>
                    <input v-model="dateTo" @change="applyFilters" type="date" class="w-full sm:w-[180px] h-9 rounded-xl border border-slate-200 bg-slate-50/50 px-3 text-sm font-medium text-slate-700 focus:outline-none focus:border-[#0C447C] focus:ring-1 focus:ring-[#85B7EB]/30 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-300" />
                </div>
            </div>

            <TDataTable
                :columns="[
                    { key: 'title', label: 'Event' },
                    { key: 'event_date', label: 'Tanggal' },
                    { key: 'location', label: 'Lokasi' },
                    { key: 'registrations_count', label: 'Pendaftar' },
                    { key: 'status', label: 'Status' },
                    { key: 'actions', label: 'Aksi', class: 'text-right', headerClass: 'text-right' },
                ]"
                :data="events.data"
            >
                <template #cell-title="{ row }">
                    <p class="text-sm font-bold text-slate-800 dark:text-white group-hover:text-[#0C447C] dark:group-hover:text-[#85B7EB] transition-colors">
                        {{ row.title }}
                    </p>
                </template>
                <template #cell-event_date="{ row }">
                    <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <CalendarDays class="h-3.5 w-3.5 text-slate-400" />
                        {{ formatDate(row.event_date) }}
                    </div>
                </template>
                <template #cell-location="{ row }">
                    <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <MapPin class="h-3.5 w-3.5 text-slate-400" />
                        <span class="truncate max-w-[150px]">{{ row.location }}</span>
                    </div>
                </template>
                <template #cell-registrations_count="{ row }">
                    <div class="flex items-center gap-1.5">
                        <Users class="h-3.5 w-3.5 text-slate-400" />
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ row.registrations_count }}</span>
                        <span v-if="row.max_participants" class="text-[10px] text-slate-400">/ {{ row.max_participants }}</span>
                    </div>
                </template>
                <template #cell-status="{ row }">
                    <TStatusBadge :status="row.status" />
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex items-center justify-end gap-1">
                        <Button as-child variant="ghost" size="sm" class="text-[#0C447C] hover:text-[#0C447C] hover:bg-[#85B7EB]/10 dark:text-[#85B7EB] dark:hover:bg-[#85B7EB]/10 rounded-lg">
                            <Link :href="`/trace/admin/events/${row.id}`" class="inline-flex items-center gap-1.5">
                                <Eye class="h-3.5 w-3.5" />
                                Detail
                            </Link>
                        </Button>
                        <Button as-child variant="ghost" size="sm" class="text-[#EF9F27] hover:text-[#EF9F27] hover:bg-[#EF9F27]/10 dark:text-[#FAC775] dark:hover:bg-[#EF9F27]/10 rounded-lg">
                            <Link :href="`/trace/admin/events/${row.id}/edit`" class="inline-flex items-center gap-1.5">
                                <Pencil class="h-3.5 w-3.5" />
                                Edit
                            </Link>
                        </Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            class="text-red-500 hover:text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/30 rounded-lg inline-flex items-center gap-1.5"
                            @click="confirmDeleteId = row.id"
                        >
                            <Trash2 class="h-3.5 w-3.5" />
                            Hapus
                        </Button>
                    </div>
                </template>
                <template #empty>
                    <TEmptyState
                        :icon="CalendarCheck"
                        :title="hasActiveFilters ? 'Tidak ada event ditemukan' : 'Belum ada event'"
                        :description="hasActiveFilters ? 'Coba ubah filter pencarian Anda.' : 'Buat event pertama untuk memulai.'"
                        :action-label="hasActiveFilters ? 'Hapus Filter' : 'Buat Event'"
                        :action-href="hasActiveFilters ? undefined : '/trace/admin/events/create'"
                        @action="clearFilters"
                    />
                </template>
            </TDataTable>

            <TPagination :links="events.links" />

            <TConfirmDialog
                :open="confirmDeleteId !== null"
                title="Hapus Event?"
                description="Event yang dihapus tidak dapat dikembalikan. Semua data pendaftaran terkait juga akan dihapus."
                confirm-label="Ya, Hapus"
                @update:open="confirmDeleteId = null"
                @confirm="deleteEvent"
            />
        </div>
    </TraceAdminLayout>
</template>
