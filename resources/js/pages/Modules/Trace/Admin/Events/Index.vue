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
    CalendarDays,
    MapPin,
    Plus,
    Users,
    Eye,
    Pencil,
    Trash2,
    CalendarCheck,
    Search,
    SlidersHorizontal,
    XCircle,
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
    { title: 'Events', href: '/admin/events' },
];

/* ───── Filter state ───── */
const searchQuery = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || 'all');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');
const showFilters = ref(false);

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
        '/admin/events',
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

/* ───── Delete event ───── */
function deleteEvent(id: number) {
    if (confirm('Yakin ingin menghapus event ini?')) {
        router.delete(`/admin/events/${id}`);
    }
}

/* ───── Status config ───── */
const statusConfig: Record<string, { label: string; classes: string }> = {
    draft: {
        label: 'Draft',
        classes: 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
    },
    published: {
        label: 'Published',
        classes: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/30 dark:text-emerald-400 dark:border-emerald-800',
    },
    closed: {
        label: 'Closed',
        classes: 'bg-red-50 text-red-600 border-red-200 dark:bg-red-950/30 dark:text-red-400 dark:border-red-800',
    },
};

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
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-1.5 text-sky-600 dark:text-sky-400 mb-1">
                        <CalendarCheck class="h-4 w-4" />
                        <span class="text-[10px] font-black uppercase tracking-widest">Manajemen Event</span>
                    </div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-800 dark:text-white">Events</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        Kelola event dan kegiatan alumni.
                    </p>
                </div>
                <Button as-child class="bg-sky-600 hover:bg-sky-700 text-white rounded-xl shadow-sm">
                    <Link href="/admin/events/create" class="inline-flex items-center gap-2">
                        <Plus class="h-4 w-4" />
                        Buat Event
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
                                    placeholder="Cari event berdasarkan judul..."
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

                            <div class="flex items-center gap-2">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap">Dari</span>
                                    <Input
                                        v-model="dateFrom"
                                        type="date"
                                        class="h-10 w-full sm:w-[160px] rounded-xl border-slate-200 bg-slate-50/50 text-sm dark:border-slate-700 dark:bg-slate-800/50"
                                    />
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap">Sampai</span>
                                    <Input
                                        v-model="dateTo"
                                        type="date"
                                        class="h-10 w-full sm:w-[160px] rounded-xl border-slate-200 bg-slate-50/50 text-sm dark:border-slate-700 dark:bg-slate-800/50"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Events Table -->
            <Card class="rounded-2xl border-slate-100 dark:border-slate-800 shadow-xs overflow-hidden">
                <CardContent class="p-0">
                    <div v-if="events.data.length > 0" class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                                    <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400">Event</th>
                                    <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400">Tanggal</th>
                                    <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400">Lokasi</th>
                                    <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400">Pendaftar</th>
                                    <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400">Status</th>
                                    <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                                <tr
                                    v-for="event in events.data"
                                    :key="event.id"
                                    class="group transition-colors hover:bg-sky-50/30 dark:hover:bg-sky-950/10"
                                >
                                    <td class="px-5 py-4">
                                        <p class="text-sm font-bold text-slate-800 dark:text-white group-hover:text-sky-700 dark:group-hover:text-sky-400 transition-colors">
                                            {{ event.title }}
                                        </p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                            <CalendarDays class="h-3.5 w-3.5 text-slate-400" />
                                            {{ formatDate(event.event_date) }}
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                            <MapPin class="h-3.5 w-3.5 text-slate-400" />
                                            <span class="truncate max-w-[150px]">{{ event.location }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-1.5">
                                            <Users class="h-3.5 w-3.5 text-slate-400" />
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                                {{ event.registrations_count }}
                                            </span>
                                            <span v-if="event.max_participants" class="text-[10px] text-slate-400">
                                                / {{ event.max_participants }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span
                                            class="inline-flex items-center rounded-lg border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider"
                                            :class="statusConfig[event.status]?.classes ?? statusConfig.draft.classes"
                                        >
                                            {{ statusConfig[event.status]?.label ?? event.status }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <Button as-child variant="ghost" size="sm" class="text-sky-600 hover:text-sky-700 hover:bg-sky-50 dark:text-sky-400 dark:hover:bg-sky-950/30 rounded-lg">
                                                <Link :href="`/admin/events/${event.id}`" class="inline-flex items-center gap-1.5">
                                                    <Eye class="h-3.5 w-3.5" />
                                                    Detail
                                                </Link>
                                            </Button>
                                            <Button as-child variant="ghost" size="sm" class="text-amber-600 hover:text-amber-700 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-950/30 rounded-lg">
                                                <Link :href="`/admin/events/${event.id}/edit`" class="inline-flex items-center gap-1.5">
                                                    <Pencil class="h-3.5 w-3.5" />
                                                    Edit
                                                </Link>
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                class="text-red-500 hover:text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/30 rounded-lg inline-flex items-center gap-1.5"
                                                @click="deleteEvent(event.id)"
                                            >
                                                <Trash2 class="h-3.5 w-3.5" />
                                                Hapus
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="flex flex-col items-center justify-center py-16 text-center">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 mb-4">
                            <CalendarCheck class="h-7 w-7 text-slate-300 dark:text-slate-600" />
                        </div>
                        <p class="text-sm font-bold text-slate-500 dark:text-slate-400 mb-1">
                            {{ hasActiveFilters ? 'Tidak ada event ditemukan' : 'Belum ada event' }}
                        </p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mb-4">
                            {{ hasActiveFilters ? 'Coba ubah filter pencarian Anda.' : 'Buat event pertama untuk memulai.' }}
                        </p>
                        <Button v-if="hasActiveFilters" size="sm" variant="outline" class="rounded-xl" @click="clearFilters">
                            <XCircle class="h-3.5 w-3.5 mr-1.5" />
                            Hapus Filter
                        </Button>
                        <Button v-else as-child size="sm" class="bg-sky-600 hover:bg-sky-700 text-white rounded-xl">
                            <Link href="/admin/events/create" class="inline-flex items-center gap-1.5">
                                <Plus class="h-3.5 w-3.5" />
                                Buat Event
                            </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="events.last_page > 1" class="flex items-center justify-between">
                <p class="text-xs text-slate-400 dark:text-slate-500">
                    Menampilkan halaman {{ events.current_page }} dari {{ events.last_page }}
                    ({{ events.total }} event)
                </p>
                <nav class="flex items-center gap-1">
                    <template v-for="(link, idx) in events.links" :key="idx">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2.5 text-xs font-bold transition-colors"
                            :class="link.active
                                ? 'bg-sky-600 text-white shadow-sm'
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
