<script setup lang="ts">
import { router, usePage, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import TraceAdminLayout from '@/layouts/TraceAdminLayout.vue';
import TraceAlumniLayout from '@/layouts/TraceAlumniLayout.vue';
import TraceMitraLayout from '@/layouts/TraceMitraLayout.vue';
import {
    TPageHeader,
    TPagination,
} from '@/components/Trace';
import {
    Bell,
    BellOff,
    Briefcase,
    CalendarCheck,
    CalendarDays,
    CalendarPlus,
    CheckCircle2,
    XCircle,
    Eye,
    Clock,
    Filter,
    CheckCheck,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface Notification {
    id: string;
    type: string;
    title: string;
    message: string;
    href: string;
    unread: boolean;
    time: string;
    date: string;
    created_at: string;
}

interface PaginatedNotifications {
    data: Notification[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    notifications: PaginatedNotifications;
    filter: string;
    role: string;
}>();

const page = usePage();

const layout = computed(() => {
    const adminRoles = ['super-admin', 'admin', 'admin-universitas', 'admin-akademik', 'prodi'];
    if (adminRoles.includes(props.role)) return TraceAdminLayout;
    if (props.role === 'mitra') return TraceMitraLayout;
    return TraceAlumniLayout;
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/trace' },
    { title: 'Notifikasi', href: '/trace/notifications' },
];

const activeFilter = ref(props.filter);

function setFilter(filter: string) {
    activeFilter.value = filter;
    router.get('/trace/notifications', {
        filter: filter === 'all' ? undefined : filter,
    }, { preserveState: true, replace: true });
}

function getIcon(type: string) {
    switch (type) {
        case 'job_application': return Briefcase;
        case 'application_status': return CheckCircle2;
        case 'event_registration': return CalendarCheck;
        case 'new_job': return Briefcase;
        case 'new_event': return CalendarPlus;
        case 'job_approved': return CheckCircle2;
        case 'job_rejected': return XCircle;
        case 'job_review': return Eye;
        default: return Bell;
    }
}

function getIconColor(type: string) {
    switch (type) {
        case 'job_application': return 'text-[#0C447C] bg-[#0C447C]/10 dark:bg-[#85B7EB]/15 dark:text-[#85B7EB]';
        case 'application_status': return 'text-[#0C447C] bg-[#85B7EB]/20 dark:bg-[#85B7EB]/15 dark:text-[#85B7EB]';
        case 'event_registration': return 'text-[#0C447C] bg-[#85B7EB]/20 dark:bg-[#85B7EB]/15 dark:text-[#85B7EB]';
        case 'new_job': return 'text-[#0C447C] bg-[#0C447C]/10 dark:bg-[#85B7EB]/15 dark:text-[#85B7EB]';
        case 'new_event': return 'text-[#0C447C] bg-[#85B7EB]/20 dark:bg-[#85B7EB]/15 dark:text-[#85B7EB]';
        case 'job_approved': return 'text-[#0C447C] bg-[#85B7EB]/20 dark:bg-[#85B7EB]/15 dark:text-[#85B7EB]';
        case 'job_rejected': return 'text-red-500 bg-red-50 dark:bg-red-950/30 dark:text-red-400';
        case 'job_review': return 'text-[#EF9F27] bg-amber-50 dark:bg-amber-950/30 dark:text-[#FAC775]';
        default: return 'text-slate-500 bg-slate-100 dark:bg-zinc-800';
    }
}

function markRead(id: string) {
    router.post(`/trace/notifications/${id}/mark-read`, {}, { preserveState: true, preserveScroll: true });
}

function markAllRead() {
    router.post('/trace/notifications/mark-all-read', {}, { preserveState: true, preserveScroll: true });
}

function handleClick(notif: Notification) {
    if (notif.unread) markRead(notif.id);
}

// Group notifications by date
const groupedNotifications = computed(() => {
    const groups: Record<string, Notification[]> = {};
    props.notifications.data.forEach(n => {
        const key = n.date;
        if (!groups[key]) groups[key] = [];
        groups[key].push(n);
    });
    return groups;
});

const unreadCount = computed(() => {
    return (page.props.unread_notifications_count as number) ?? 0;
});
</script>

<template>
    <component :is="layout" title="Notifikasi" :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-3xl space-y-6">
            <!-- Header -->
            <TPageHeader
                title="Notifikasi"
                :description="`${notifications.total} notifikasi`"
                :icon="Bell"
            >
                <template #actions>
                    <button
                        v-if="unreadCount > 0"
                        @click="markAllRead"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-[#0C447C]/10 px-3 py-1.5 text-[12px] font-semibold text-[#0C447C] transition-colors hover:bg-[#0C447C]/20 dark:bg-[#85B7EB]/15 dark:text-[#85B7EB] dark:hover:bg-[#85B7EB]/25"
                    >
                        <CheckCheck class="h-3.5 w-3.5" />
                        Tandai Semua Dibaca
                    </button>
                </template>
            </TPageHeader>

            <!-- Filter Tabs -->
            <div class="flex items-center gap-1 rounded-xl bg-slate-100/80 dark:bg-zinc-800/80 p-1">
                <button
                    @click="setFilter('all')"
                    class="flex-1 rounded-lg px-4 py-2 text-[13px] font-semibold transition-all"
                    :class="activeFilter === 'all'
                        ? 'bg-white text-[#0C447C] shadow-sm dark:bg-zinc-700 dark:text-[#85B7EB]'
                        : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                >
                    Semua
                </button>
                <button
                    @click="setFilter('unread')"
                    class="flex-1 rounded-lg px-4 py-2 text-[13px] font-semibold transition-all flex items-center justify-center gap-1.5"
                    :class="activeFilter === 'unread'
                        ? 'bg-white text-[#0C447C] shadow-sm dark:bg-zinc-700 dark:text-[#85B7EB]'
                        : 'text-slate-500 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                >
                    Belum Dibaca
                    <span
                        v-if="unreadCount > 0"
                        class="flex h-5 min-w-5 items-center justify-center rounded-full bg-[#0C447C] px-1 text-[9px] font-black text-white dark:bg-[#85B7EB] dark:text-zinc-900"
                    >
                        {{ unreadCount }}
                    </span>
                </button>
            </div>

            <!-- Notification List -->
            <div v-if="notifications.data.length > 0" class="space-y-4">
                <div v-for="(items, date) in groupedNotifications" :key="date">
                    <!-- Date Header -->
                    <div class="sticky top-0 z-10 flex items-center gap-3 py-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">
                            {{ date }}
                        </span>
                        <div class="flex-1 h-px bg-slate-100 dark:bg-zinc-800" />
                    </div>

                    <!-- Items -->
                    <div class="space-y-1.5">
                        <div
                            v-for="notif in items"
                            :key="notif.id"
                            class="flex items-start gap-4 rounded-2xl border border-transparent px-5 py-4 transition-all group"
                            :class="notif.unread
                                ? 'bg-white border-slate-100 shadow-sm dark:bg-zinc-800/60 dark:border-zinc-700'
                                : 'hover:bg-slate-50 dark:hover:bg-zinc-800/30'"
                        >
                            <!-- Icon -->
                            <div class="shrink-0 mt-0.5">
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-xl"
                                    :class="getIconColor(notif.type)"
                                >
                                    <component :is="getIcon(notif.type)" class="h-5 w-5" />
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-3">
                                    <p
                                        class="text-sm leading-tight"
                                        :class="notif.unread
                                            ? 'font-bold text-slate-800 dark:text-white'
                                            : 'font-medium text-slate-600 dark:text-zinc-400'"
                                    >
                                        {{ notif.title }}
                                    </p>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="text-[11px] text-slate-400 dark:text-zinc-500 whitespace-nowrap flex items-center gap-1">
                                            <Clock class="h-3 w-3" />
                                            {{ notif.time }}
                                        </span>
                                        <!-- Mark read button (hover) -->
                                        <button
                                            v-if="notif.unread"
                                            @click.stop="markRead(notif.id)"
                                            class="opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap text-[10px] font-semibold text-[#0C447C] dark:text-[#85B7EB] hover:text-[#0a3968] dark:hover:text-white bg-slate-100 dark:bg-zinc-700 hover:bg-slate-200 dark:hover:bg-zinc-600 rounded-md px-2 py-0.5"
                                        >
                                            Tandai dibaca
                                        </button>
                                        <div v-if="notif.unread" class="h-2.5 w-2.5 rounded-full bg-[#0C447C] dark:bg-[#85B7EB] animate-pulse group-hover:opacity-0 transition-opacity" />
                                    </div>
                                </div>
                                <p class="text-[13px] text-slate-500 dark:text-zinc-400 mt-1 leading-relaxed">
                                    {{ notif.message }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="flex flex-col items-center justify-center py-20 text-center">
                <div class="relative mb-6">
                    <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-gradient-to-br from-[#0C447C]/10 to-[#85B7EB]/20 dark:from-[#85B7EB]/10 dark:to-[#0C447C]/20">
                        <BellOff class="h-9 w-9 text-[#0C447C]/30 dark:text-[#85B7EB]/30" />
                    </div>
                    <div class="absolute -bottom-1 -right-1 h-7 w-7 rounded-full bg-white dark:bg-zinc-900 border-2 border-slate-100 dark:border-zinc-800 flex items-center justify-center">
                        <CheckCircle2 class="h-4 w-4 text-[#0C447C]/40 dark:text-[#85B7EB]/40" />
                    </div>
                </div>
                <h3 class="text-lg font-bold text-slate-600 dark:text-zinc-300 mb-2">
                    {{ activeFilter === 'unread' ? 'Semua sudah dibaca!' : 'Belum ada notifikasi' }}
                </h3>
                <p class="text-sm text-slate-400 dark:text-zinc-500 max-w-sm leading-relaxed">
                    {{ activeFilter === 'unread'
                        ? 'Tidak ada notifikasi yang belum dibaca. Anda sudah up to date!'
                        : 'Kami akan memberitahu Anda saat ada lowongan baru, event, atau update penting lainnya.'
                    }}
                </p>
                <button
                    v-if="activeFilter === 'unread'"
                    @click="setFilter('all')"
                    class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-[#0C447C] px-4 py-2 text-[13px] font-semibold text-white hover:bg-[#0a3968] transition-colors"
                >
                    Lihat Semua Notifikasi
                </button>
            </div>

            <!-- Pagination -->
            <TPagination v-if="notifications.data.length > 0" :links="notifications.links" />
        </div>
    </component>
</template>
