<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import {
    Bell,
    Briefcase,
    CalendarCheck,
    CalendarDays,
    CheckCircle2,
    XCircle,
    Eye,
    Clock,
} from 'lucide-vue-next';

interface Notification {
    id: string;
    type: string;
    title: string;
    message: string;
    href: string;
    icon: string;
    unread: boolean;
    time: string;
}

const page = usePage();
const open = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

const notifications = computed<Notification[]>(() => {
    return (page.props.recent_notifications as Notification[]) ?? [];
});

const unreadCount = computed(() => {
    return (page.props.unread_notifications_count as number) ?? 0;
});

function toggle() {
    open.value = !open.value;
}

function getIcon(type: string) {
    switch (type) {
        case 'job_application': return Briefcase;
        case 'application_status': return CheckCircle2;
        case 'event_registration': return CalendarCheck;
        case 'new_job': return Briefcase;
        case 'new_event': return CalendarDays;
        case 'job_approved': return CheckCircle2;
        case 'job_rejected': return XCircle;
        case 'job_review': return Eye;
        default: return Bell;
    }
}

function getIconColor(type: string) {
    switch (type) {
        case 'job_application': return 'text-violet-500 bg-violet-50 dark:bg-violet-950/30';
        case 'application_status': return 'text-emerald-500 bg-emerald-50 dark:bg-emerald-950/30';
        case 'event_registration': return 'text-sky-500 bg-sky-50 dark:bg-sky-950/30';
        case 'new_job': return 'text-violet-500 bg-violet-50 dark:bg-violet-950/30';
        case 'new_event': return 'text-sky-500 bg-sky-50 dark:bg-sky-950/30';
        case 'job_approved': return 'text-emerald-500 bg-emerald-50 dark:bg-emerald-950/30';
        case 'job_rejected': return 'text-red-500 bg-red-50 dark:bg-red-950/30';
        case 'job_review': return 'text-amber-500 bg-amber-50 dark:bg-amber-950/30';
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
    open.value = false;
    router.visit(notif.href || '/trace');
}

function handleClickOutside(e: MouseEvent) {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target as Node)) {
        open.value = false;
    }
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>

<template>
    <div ref="dropdownRef" class="relative">
        <!-- Bell Button -->
        <button
            @click.stop="toggle"
            class="relative flex h-9 w-9 items-center justify-center rounded-xl text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
        >
            <Bell class="h-[18px] w-[18px]" />
            <span
                v-if="unreadCount > 0"
                class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[9px] font-black text-white ring-2 ring-white dark:ring-zinc-900"
            >
                {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 scale-95 -translate-y-1"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 -translate-y-1"
        >
            <div
                v-if="open"
                class="absolute right-0 top-full mt-2 w-[360px] rounded-2xl bg-white border border-slate-100 shadow-xl shadow-slate-200/60 dark:bg-zinc-900 dark:border-zinc-800 dark:shadow-zinc-950 z-50 overflow-hidden"
            >
                <!-- Header -->
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <h4 class="text-[13px] font-bold text-slate-800 dark:text-white">Notifikasi</h4>
                        <span
                            v-if="unreadCount > 0"
                            class="flex h-5 items-center justify-center rounded-full bg-red-500 px-1.5 text-[9px] font-black text-white"
                        >
                            {{ unreadCount }}
                        </span>
                    </div>
                    <button
                        v-if="unreadCount > 0"
                        @click="markAllRead"
                        class="text-[11px] font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 transition-colors"
                    >
                        Tandai semua dibaca
                    </button>
                </div>

                <!-- Notification List -->
                <div class="max-h-[380px] overflow-y-auto divide-y divide-slate-50 dark:divide-zinc-800/50" style="scrollbar-width: thin;">
                    <!-- Empty -->
                    <div v-if="notifications.length === 0" class="flex flex-col items-center justify-center py-10 px-4 text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 dark:bg-zinc-800 mb-3">
                            <Bell class="h-5 w-5 text-slate-300 dark:text-zinc-600" />
                        </div>
                        <p class="text-xs font-semibold text-slate-400 dark:text-zinc-500">Belum ada notifikasi</p>
                    </div>

                    <!-- Items -->
                    <div
                        v-for="notif in notifications"
                        :key="notif.id"
                        @click="handleClick(notif)"
                        class="flex items-start gap-3 px-4 py-3 transition-colors cursor-pointer hover:bg-slate-50 dark:hover:bg-zinc-800/40"
                        :class="{ 'bg-emerald-50/40 dark:bg-emerald-950/10': notif.unread }"
                    >
                        <!-- Icon -->
                        <div class="shrink-0 mt-0.5">
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-xl"
                                :class="getIconColor(notif.type)"
                            >
                                <component :is="getIcon(notif.type)" class="h-4 w-4" />
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p
                                class="text-[12px] leading-tight"
                                :class="notif.unread
                                    ? 'font-bold text-slate-800 dark:text-white'
                                    : 'font-medium text-slate-600 dark:text-zinc-400'"
                            >
                                {{ notif.title }}
                            </p>
                            <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-0.5 line-clamp-2">
                                {{ notif.message }}
                            </p>
                            <p class="text-[10px] text-slate-300 dark:text-zinc-600 mt-1 flex items-center gap-1">
                                <Clock class="h-2.5 w-2.5" />
                                {{ notif.time }}
                            </p>
                        </div>

                        <!-- Unread dot -->
                        <div v-if="notif.unread" class="mt-3 h-2 w-2 rounded-full bg-emerald-500 shrink-0" />
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>
