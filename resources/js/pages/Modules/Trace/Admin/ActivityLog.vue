<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import TraceAdminLayout from '@/layouts/TraceAdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    History,
    Search,
    Filter,
    X,
    User as UserIcon,
    Globe,
    Clock,
    ChevronLeft,
    ChevronRight,
    Activity,
    CalendarDays,
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
const showFilters = ref(false);

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
            label: action, bg: 'bg-violet-50', text: 'text-violet-700', border: 'border-violet-200',
            darkBg: 'dark:bg-violet-950/30', darkText: 'dark:text-violet-400', darkBorder: 'dark:border-violet-800',
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
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-1.5 text-sky-600 dark:text-sky-400 mb-1">
                        <History class="h-4 w-4" />
                        <span class="text-[10px] font-black uppercase tracking-widest">Audit Trail</span>
                    </div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-800 dark:text-white">Activity Log</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        Pantau semua aktivitas pengguna di sistem.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-xl bg-sky-50 dark:bg-sky-950/30 px-3 py-1.5 text-xs font-bold text-sky-700 dark:text-sky-400 border border-sky-200/50 dark:border-sky-800/30">
                        <Activity class="h-3.5 w-3.5" />
                        {{ logs.total }} Total Log
                    </span>
                </div>
            </div>

            <!-- Search & Filters -->
            <Card class="rounded-2xl border-slate-100 dark:border-slate-800 shadow-xs overflow-hidden">
                <CardContent class="p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <!-- Search -->
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                            <input
                                v-model="search"
                                @input="onSearchInput"
                                @keyup.enter="applyFilters"
                                type="text"
                                placeholder="Cari deskripsi aktivitas..."
                                class="w-full h-10 pl-10 pr-4 rounded-xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-700 placeholder-slate-400 focus:outline-none focus:border-sky-400 focus:ring-1 focus:ring-sky-200 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-200 dark:focus:border-sky-600 dark:focus:ring-sky-800 transition-colors"
                            />
                        </div>

                        <!-- Toggle Filters -->
                        <Button
                            variant="outline"
                            size="sm"
                            @click="showFilters = !showFilters"
                            class="rounded-xl border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 h-10 px-4 gap-2"
                        >
                            <Filter class="h-3.5 w-3.5" />
                            Filter
                            <span v-if="hasActiveFilters" class="flex h-4 w-4 items-center justify-center rounded-full bg-sky-600 text-[9px] font-bold text-white">!</span>
                        </Button>

                        <!-- Clear -->
                        <Button
                            v-if="hasActiveFilters"
                            variant="ghost"
                            size="sm"
                            @click="clearFilters"
                            class="rounded-xl text-red-500 hover:text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/20 h-10 px-4 gap-2"
                        >
                            <X class="h-3.5 w-3.5" />
                            Reset
                        </Button>
                    </div>

                    <!-- Filter Panel -->
                    <Transition
                        enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="opacity-0 -translate-y-2 max-h-0"
                        enter-to-class="opacity-100 translate-y-0 max-h-40"
                        leave-active-class="transition-all duration-150 ease-in"
                        leave-from-class="opacity-100 translate-y-0 max-h-40"
                        leave-to-class="opacity-0 -translate-y-2 max-h-0"
                    >
                        <div v-if="showFilters" class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800 overflow-hidden">
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <!-- Action Type -->
                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">Tipe Aksi</label>
                                    <select
                                        v-model="actionFilter"
                                        @change="applyFilters"
                                        class="w-full h-9 rounded-lg border border-slate-200 bg-white px-3 text-xs font-medium text-slate-700 focus:outline-none focus:border-sky-400 focus:ring-1 focus:ring-sky-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:focus:border-sky-600 appearance-none"
                                    >
                                        <option value="">Semua Aksi</option>
                                        <template v-for="(actions, group) in groupedActionTypes" :key="group">
                                            <optgroup :label="String(group).toUpperCase()">
                                                <option v-for="action in actions" :key="action" :value="action">{{ action }}</option>
                                            </optgroup>
                                        </template>
                                    </select>
                                </div>

                                <!-- Date From -->
                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">Dari Tanggal</label>
                                    <input
                                        v-model="dateFrom"
                                        @change="applyFilters"
                                        type="date"
                                        class="w-full h-9 rounded-lg border border-slate-200 bg-white px-3 text-xs font-medium text-slate-700 focus:outline-none focus:border-sky-400 focus:ring-1 focus:ring-sky-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:focus:border-sky-600"
                                    />
                                </div>

                                <!-- Date To -->
                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">Sampai Tanggal</label>
                                    <input
                                        v-model="dateTo"
                                        @change="applyFilters"
                                        type="date"
                                        class="w-full h-9 rounded-lg border border-slate-200 bg-white px-3 text-xs font-medium text-slate-700 focus:outline-none focus:border-sky-400 focus:ring-1 focus:ring-sky-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:focus:border-sky-600"
                                    />
                                </div>
                            </div>
                        </div>
                    </Transition>
                </CardContent>
            </Card>

            <!-- Activity Log Table -->
            <Card class="rounded-2xl border-slate-100 dark:border-slate-800 shadow-xs overflow-hidden">
                <CardContent class="p-0">
                    <div v-if="logs.data.length > 0" class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                                    <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400">Pengguna</th>
                                    <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400">Aksi</th>
                                    <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400 hidden sm:table-cell">Deskripsi</th>
                                    <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400 hidden lg:table-cell">IP Address</th>
                                    <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400 text-right">Waktu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                                <tr
                                    v-for="log in logs.data"
                                    :key="log.id"
                                    class="group transition-colors hover:bg-sky-50/30 dark:hover:bg-sky-950/10"
                                >
                                    <!-- User -->
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-2.5 min-w-[140px]">
                                            <template v-if="log.user">
                                                <div class="relative flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full ring-2 ring-slate-100 dark:ring-slate-700">
                                                    <img
                                                        :src="getAvatarUrl(log.user)"
                                                        :alt="log.user.name"
                                                        class="h-full w-full object-cover"
                                                    />
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-xs font-bold text-slate-800 dark:text-white truncate max-w-[120px]">
                                                        {{ log.user.name }}
                                                    </p>
                                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 truncate max-w-[120px]">
                                                        {{ log.user.email }}
                                                    </p>
                                                </div>
                                            </template>
                                            <template v-else>
                                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800">
                                                    <UserIcon class="h-3.5 w-3.5 text-slate-400" />
                                                </div>
                                                <span class="text-xs text-slate-400 italic">Sistem</span>
                                            </template>
                                        </div>
                                    </td>

                                    <!-- Action Badge -->
                                    <td class="px-5 py-3.5">
                                        <span
                                            class="inline-flex items-center rounded-lg border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider whitespace-nowrap"
                                            :class="[
                                                getActionBadge(log.action).bg,
                                                getActionBadge(log.action).text,
                                                getActionBadge(log.action).border,
                                                getActionBadge(log.action).darkBg,
                                                getActionBadge(log.action).darkText,
                                                getActionBadge(log.action).darkBorder,
                                            ]"
                                        >
                                            {{ log.action }}
                                        </span>
                                    </td>

                                    <!-- Description -->
                                    <td class="px-5 py-3.5 hidden sm:table-cell">
                                        <p class="text-xs font-medium text-slate-700 dark:text-slate-300 max-w-[300px] truncate">
                                            {{ log.description }}
                                        </p>
                                        <p
                                            v-if="log.properties && Object.keys(log.properties).length > 0"
                                            class="mt-0.5 text-[10px] text-slate-400 dark:text-slate-500 truncate max-w-[300px]"
                                        >
                                            {{ JSON.stringify(log.properties) }}
                                        </p>
                                    </td>

                                    <!-- IP -->
                                    <td class="px-5 py-3.5 hidden lg:table-cell">
                                        <div v-if="log.ip_address" class="flex items-center gap-1.5">
                                            <Globe class="h-3 w-3 text-slate-400" />
                                            <span class="text-xs font-mono text-slate-500 dark:text-slate-400">{{ log.ip_address }}</span>
                                        </div>
                                        <span v-else class="text-xs text-slate-300 dark:text-slate-600">—</span>
                                    </td>

                                    <!-- Timestamp -->
                                    <td class="px-5 py-3.5 text-right">
                                        <div class="flex flex-col items-end gap-0.5">
                                            <span class="text-xs font-bold text-slate-600 dark:text-slate-300">
                                                {{ formatRelative(log.created_at) }}
                                            </span>
                                            <span class="text-[10px] text-slate-400 dark:text-slate-500 flex items-center gap-1">
                                                <Clock class="h-2.5 w-2.5" />
                                                {{ formatDate(log.created_at) }} {{ formatTime(log.created_at) }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="flex flex-col items-center justify-center py-20 text-center">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 mb-4">
                            <FileText class="h-8 w-8 text-slate-300 dark:text-slate-600" />
                        </div>
                        <p class="text-sm font-bold text-slate-500 dark:text-slate-400 mb-1">Belum ada aktivitas</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 max-w-sm">
                            Aktivitas pengguna akan muncul di sini saat ada tindakan yang dilakukan di sistem.
                        </p>
                        <Button
                            v-if="hasActiveFilters"
                            variant="outline"
                            size="sm"
                            @click="clearFilters"
                            class="mt-4 rounded-xl border-slate-200 dark:border-slate-700"
                        >
                            <X class="h-3.5 w-3.5 mr-1.5" />
                            Reset Filter
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="logs.last_page > 1" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-400 dark:text-slate-500">
                    Menampilkan {{ logs.from }}–{{ logs.to }} dari {{ logs.total }} log
                    · Halaman {{ logs.current_page }} / {{ logs.last_page }}
                </p>
                <nav class="flex items-center gap-1">
                    <template v-for="(link, idx) in logs.links" :key="idx">
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
