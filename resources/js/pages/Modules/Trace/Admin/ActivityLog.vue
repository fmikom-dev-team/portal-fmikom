<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import TraceAdminLayout from '@/layouts/TraceAdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { TPageHeader, TDataTable, TFilterBar, TPagination, TEmptyState } from '@/components/trace';
import {
    History,
    User as UserIcon,
    Globe,
    Clock,
    Activity,
    FileText,
} from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';

interface UserData {
    id: number;
    name: string;
    email: string;
    avatar?: string | null;
}

interface LogEntry {
    id: number;
    user_id: number | null;
    action: string;
    subject_type: string | null;
    subject_id: number | null;
    description: string;
    properties: Record<string, any> | null;
    ip_address: string | null;
    created_at: string;
    user: UserData | null;
}

interface PaginatedLogs {
    data: LogEntry[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
    from: number | null;
    to: number | null;
}

const props = defineProps<{
    logs: PaginatedLogs;
    filters: {
        action?: string;
        user_id?: string;
        search?: string;
        date_from?: string;
        date_to?: string;
    };
    actionTypes: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/trace/admin' },
    { title: 'Activity Log', href: '/trace/admin/activity-log' },
];

// Filter state
const search = ref(props.filters.search ?? '');
const actionFilter = ref(props.filters.action ?? '');
const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');


const hasActiveFilters = computed(() => {
    return !!actionFilter.value || !!dateFrom.value || !!dateTo.value || !!search.value;
});

const applyFilters = () => {
    const params: Record<string, string> = {};
    if (search.value) params.search = search.value;
    if (actionFilter.value) params.action = actionFilter.value;
    if (dateFrom.value) params.date_from = dateFrom.value;
    if (dateTo.value) params.date_to = dateTo.value;

    router.get('/trace/admin/activity-log', params, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    search.value = '';
    actionFilter.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    router.get('/trace/admin/activity-log', {}, { preserveState: true, preserveScroll: true });
};

const onFilterBarChange = (filters: Record<string, string>) => {
    search.value = filters.search ?? '';
    actionFilter.value = filters.action ?? '';
    applyFilters();
};

let searchTimeout: ReturnType<typeof setTimeout> | null = null;
const onSearchInput = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 400);
};

// Action badge config
type BadgeConfig = { label: string; bg: string; text: string; border: string; darkBg: string; darkText: string; darkBorder: string };

const getActionBadge = (action: string): BadgeConfig => {
    const prefix = action.split('.')[0];
    const configs: Record<string, BadgeConfig> = {
        auth: {
            label: action, bg: 'bg-slate-100', text: 'text-slate-600', border: 'border-slate-200',
            darkBg: 'dark:bg-slate-800', darkText: 'dark:text-slate-400', darkBorder: 'dark:border-slate-700',
        },
        job: {
            label: action, bg: 'bg-[#0C447C]/10', text: 'text-[#0C447C]', border: 'border-[#85B7EB]',
            darkBg: 'dark:bg-[#0C447C]/20', darkText: 'dark:text-[#85B7EB]', darkBorder: 'dark:border-[#0C447C]',
        },
        event: {
            label: action, bg: 'bg-sky-50', text: 'text-sky-700', border: 'border-sky-200',
            darkBg: 'dark:bg-sky-950/30', darkText: 'dark:text-sky-400', darkBorder: 'dark:border-sky-800',
        },
        kuesioner: {
            label: action, bg: 'bg-amber-50', text: 'text-amber-700', border: 'border-amber-200',
            darkBg: 'dark:bg-amber-950/30', darkText: 'dark:text-amber-400', darkBorder: 'dark:border-amber-800',
        },
        applicant: {
            label: action, bg: 'bg-emerald-50', text: 'text-emerald-700', border: 'border-emerald-200',
            darkBg: 'dark:bg-emerald-950/30', darkText: 'dark:text-emerald-400', darkBorder: 'dark:border-emerald-800',
        },
        profile: {
            label: action, bg: 'bg-rose-50', text: 'text-rose-700', border: 'border-rose-200',
            darkBg: 'dark:bg-rose-950/30', darkText: 'dark:text-rose-400', darkBorder: 'dark:border-rose-800',
        },
    };
    return configs[prefix] ?? {
        label: action, bg: 'bg-gray-50', text: 'text-gray-600', border: 'border-gray-200',
        darkBg: 'dark:bg-gray-800', darkText: 'dark:text-gray-400', darkBorder: 'dark:border-gray-700',
    };
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

const formatTime = (dateStr: string) => {
    try {
        return new Date(dateStr).toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch {
        return '';
    }
};

const formatRelative = (dateStr: string) => {
    try {
        const now = new Date();
        const date = new Date(dateStr);
        const diffMs = now.getTime() - date.getTime();
        const diffMin = Math.floor(diffMs / 60000);
        const diffHrs = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMin < 1) return 'Baru saja';
        if (diffMin < 60) return `${diffMin} menit lalu`;
        if (diffHrs < 24) return `${diffHrs} jam lalu`;
        if (diffDays < 7) return `${diffDays} hari lalu`;
        return formatDate(dateStr);
    } catch {
        return dateStr;
    }
};

const getInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const getAvatarUrl = (user: UserData) => {
    if (user.avatar) return user.avatar;
    return `https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(user.name)}&backgroundColor=0369a1&textColor=ffffff`;
};

// Group action types for the filter dropdown
const groupedActionTypes = computed(() => {
    const groups: Record<string, string[]> = {};
    props.actionTypes.forEach((action) => {
        const prefix = action.split('.')[0];
        if (!groups[prefix]) groups[prefix] = [];
        groups[prefix].push(action);
    });
    return groups;
});
</script>

<template>
    <TraceAdminLayout title="Activity Log" :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-7xl space-y-6">
            <TPageHeader
                title="Activity Log"
                description="Riwayat seluruh aktivitas pengguna dalam sistem."
                :icon="History"
            >
                <template #actions>
                    <span class="inline-flex items-center gap-1.5 rounded-xl bg-[#0C447C]/10 dark:bg-[#85B7EB]/10 px-3 py-1.5 text-xs font-bold text-[#0C447C] dark:text-[#85B7EB] border border-[#0C447C]/20 dark:border-[#85B7EB]/20">
                        <Activity class="h-3.5 w-3.5" />
                        {{ logs.total }} Total Log
                    </span>
                </template>
            </TPageHeader>

            <TFilterBar
                search-placeholder="Cari deskripsi aktivitas..."
                :filters="[
                    {
                        key: 'action',
                        label: 'Aksi',
                        options: actionTypes.map(a => ({ value: a, label: a })),
                    },
                ]"
                :model-value="{ search, action: actionFilter }"
                @filter-change="onFilterBarChange"
            />

            <!-- Date Range Filters -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1 sm:flex-none">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">Dari Tanggal</label>
                    <input v-model="dateFrom" @change="applyFilters" type="date" class="w-full sm:w-[180px] h-9 rounded-xl border border-slate-200 bg-slate-50/50 px-3 text-sm font-medium text-slate-700 focus:outline-none focus:border-[#0C447C] focus:ring-1 focus:ring-[#85B7EB]/30 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-300" />
                </div>
                <div class="flex-1 sm:flex-none">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">Sampai Tanggal</label>
                    <input v-model="dateTo" @change="applyFilters" type="date" class="w-full sm:w-[180px] h-9 rounded-xl border border-slate-200 bg-slate-50/50 px-3 text-sm font-medium text-slate-700 focus:outline-none focus:border-[#0C447C] focus:ring-1 focus:ring-[#85B7EB]/30 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-300" />
                </div>
            </div>

            <TDataTable
                :columns="[
                    { key: 'user', label: 'Pengguna' },
                    { key: 'action', label: 'Aksi' },
                    { key: 'description', label: 'Deskripsi', class: 'hidden sm:table-cell', headerClass: 'hidden sm:table-cell' },
                    { key: 'ip_address', label: 'IP Address', class: 'hidden lg:table-cell', headerClass: 'hidden lg:table-cell' },
                    { key: 'created_at', label: 'Waktu', class: 'text-right', headerClass: 'text-right' },
                ]"
                :data="logs.data"
            >
                <template #cell-user="{ row }">
                    <div class="flex items-center gap-2.5 min-w-[140px]">
                        <template v-if="row.user">
                            <div class="relative flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full ring-2 ring-slate-100 dark:ring-slate-700">
                                <img :src="getAvatarUrl(row.user)" :alt="row.user.name" class="h-full w-full object-cover" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-800 dark:text-white truncate max-w-[120px]">{{ row.user.name }}</p>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 truncate max-w-[120px]">{{ row.user.email }}</p>
                            </div>
                        </template>
                        <template v-else>
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800">
                                <UserIcon class="h-3.5 w-3.5 text-slate-400" />
                            </div>
                            <span class="text-xs text-slate-400 italic">Sistem</span>
                        </template>
                    </div>
                </template>
                <template #cell-action="{ row }">
                    <span
                        class="inline-flex items-center rounded-lg border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider whitespace-nowrap"
                        :class="[
                            getActionBadge(row.action).bg,
                            getActionBadge(row.action).text,
                            getActionBadge(row.action).border,
                            getActionBadge(row.action).darkBg,
                            getActionBadge(row.action).darkText,
                            getActionBadge(row.action).darkBorder,
                        ]"
                    >
                        {{ row.action }}
                    </span>
                </template>
                <template #cell-description="{ row }">
                    <p class="text-xs font-medium text-slate-700 dark:text-slate-300 max-w-[300px] truncate">{{ row.description }}</p>
                    <p v-if="row.properties && Object.keys(row.properties).length > 0" class="mt-0.5 text-[10px] text-slate-400 dark:text-slate-500 truncate max-w-[300px]">
                        {{ JSON.stringify(row.properties) }}
                    </p>
                </template>
                <template #cell-ip_address="{ row }">
                    <div v-if="row.ip_address" class="flex items-center gap-1.5">
                        <Globe class="h-3 w-3 text-slate-400" />
                        <span class="text-xs font-mono text-slate-500 dark:text-slate-400">{{ row.ip_address }}</span>
                    </div>
                    <span v-else class="text-xs text-slate-300 dark:text-slate-600">—</span>
                </template>
                <template #cell-created_at="{ row }">
                    <div class="flex flex-col items-end gap-0.5">
                        <span class="text-xs font-bold text-slate-600 dark:text-slate-300">{{ formatRelative(row.created_at) }}</span>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 flex items-center gap-1">
                            <Clock class="h-2.5 w-2.5" />
                            {{ formatDate(row.created_at) }} {{ formatTime(row.created_at) }}
                        </span>
                    </div>
                </template>
                <template #empty>
                    <TEmptyState
                        :icon="FileText"
                        :title="hasActiveFilters ? 'Tidak ada log ditemukan' : 'Belum ada aktivitas'"
                        :description="hasActiveFilters ? 'Coba ubah filter pencarian Anda.' : 'Aktivitas pengguna akan muncul di sini saat ada tindakan yang dilakukan di sistem.'"
                        :action-label="hasActiveFilters ? 'Reset Filter' : undefined"
                        @action="clearFilters"
                    />
                </template>
            </TDataTable>

            <TPagination :links="logs.links" />
        </div>
    </TraceAdminLayout>
</template>
