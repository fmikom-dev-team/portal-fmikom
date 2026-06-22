<script setup lang="ts">
import { router, usePage, Link } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
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
    ExternalLink,
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
const toast = ref<Notification | null>(null);
const toastTimeout = ref<ReturnType<typeof setTimeout> | null>(null);
const prevUnreadCount = ref(0);

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
    router.post(`/trace/notifications/${id}/mark-read`, {}, {
        preserveState: true,
        preserveScroll: true,
        only: ['recent_notifications', 'unread_notifications_count'],
    });
}

function markAllRead() {
    router.post('/trace/notifications/mark-all-read', {}, {
        preserveState: true,
        preserveScroll: true,
        only: ['recent_notifications', 'unread_notifications_count'],
    });
}

function dismissToast() {
    toast.value = null;
    if (toastTimeout.value) clearTimeout(toastTimeout.value);
}

function handleToastClick() {
    if (toast.value) {
        if (toast.value.unread) markRead(toast.value.id);
        dismissToast();
    }
}

// Watch for new notifications → show toast
watch(unreadCount, (newVal, oldVal) => {
    if (newVal > oldVal && notifications.value.length > 0) {
        const newest = notifications.value.find(n => n.unread);
        if (newest) {
            toast.value = newest;
            if (toastTimeout.value) clearTimeout(toastTimeout.value);
            toastTimeout.value = setTimeout(() => {
                toast.value = null;
            }, 5000);
        }
    }
    prevUnreadCount.value = newVal;
});

function handleClickOutside(e: MouseEvent) {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target as Node)) {
        open.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    prevUnreadCount.value = unreadCount.value;
});
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>

<template>
    <div ref="dropdownRef" class="relative">
        <!-- Bell Button -->
        <button
            @click.stop="toggle"
            class="relative flex h-9 w-9 items-center justify-center rounded-xl text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
            aria-label="Notifikasi"
        >
            <Bell class="h-[18px] w-[18px]" />
            <span
                v-if="unreadCount > 0"
                class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[9px] font-black text-white ring-2 ring-white dark:ring-zinc-900 animate-pulse"
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
                role="menu"
                aria-label="Daftar notifikasi"
                class="absolute right-0 top-full mt-2 w-[360px] rounded-2xl bg-white border border-slate-100 shadow-xl shadow-slate-200/60 dark:bg-zinc-900 dark:border-zinc-800 dark:shadow-zinc-950 z-50 overflow-hidden"
            >
                <!-- Header -->
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <h4 class="text-[13px] font-bold text-slate-800 dark:text-white">Notifikasi</h4>
                        <span
                            v-if="unreadCount > 0"
                            class="flex h-5 items-center justify-center rounded-full bg-[#0C447C] px-1.5 text-[9px] font-black text-white"
                        >
                            {{ unreadCount }}
                        </span>
                    </div>
                    <button
                        v-if="unreadCount > 0"
                        @click="markAllRead"
                        aria-label="Tandai semua notifikasi dibaca"
                        class="text-[11px] font-semibold text-[#0C447C] hover:text-[#0a3968] dark:text-[#85B7EB] dark:hover:text-white transition-colors"
                    >
                        Tandai semua dibaca
                    </button>
                </div>

                <!-- Notification List -->
                <div class="max-h-[380px] overflow-y-auto divide-y divide-slate-50 dark:divide-zinc-800/50" style="scrollbar-width: thin;">
                    <!-- Empty State -->
                    <div v-if="notifications.length === 0" class="flex flex-col items-center justify-center py-12 px-6 text-center">
                        <div class="relative mb-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-[#0C447C]/10 to-[#85B7EB]/20 dark:from-[#85B7EB]/10 dark:to-[#0C447C]/20">
                                <BellOff class="h-7 w-7 text-[#0C447C]/40 dark:text-[#85B7EB]/40" />
                            </div>
                            <div class="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                                <CheckCircle2 class="h-3 w-3 text-slate-300 dark:text-zinc-600" />
                            </div>
                        </div>
                        <p class="text-sm font-semibold text-slate-500 dark:text-zinc-400 mb-1">Semua bersih!</p>
                        <p class="text-[11px] text-slate-400 dark:text-zinc-500 leading-relaxed">
                            Belum ada notifikasi baru.<br>
                            Kami akan memberitahu Anda saat ada update.
                        </p>
                    </div>

                    <!-- Items -->
                    <div
                        v-for="notif in notifications"
                        :key="notif.id"
                        class="flex items-start gap-3 px-4 py-3 transition-all group"
                        :class="[
                            notif.unread
                                ? 'bg-[#0C447C]/[0.03] dark:bg-[#85B7EB]/[0.05]'
                                : ''
                        ]"
                    >
                        <!-- Icon -->
                        <div class="shrink-0 mt-0.5">
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-xl transition-transform group-hover:scale-105"
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

                        <!-- Mark read CTA (hover) / Unread dot (default) -->
                        <div v-if="notif.unread" class="shrink-0 mt-1.5 relative">
                            <!-- Dot (visible by default, hidden on hover) -->
                            <div class="h-2 w-2 rounded-full bg-[#0C447C] dark:bg-[#85B7EB] animate-pulse group-hover:opacity-0 transition-opacity" />
                            <!-- Button (hidden by default, visible on hover) -->
                            <button
                                @click.stop.prevent="markRead(notif.id)"
                                class="absolute -top-1 -right-1 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap text-[10px] font-semibold text-[#0C447C] dark:text-[#85B7EB] hover:text-[#0a3968] dark:hover:text-white bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 rounded-md px-1.5 py-0.5"
                                title="Tandai sudah dibaca"
                                aria-label="Tandai sudah dibaca"
                            >
                                Dibaca
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer — View All -->
                <div v-if="notifications.length > 0" class="border-t border-slate-100 dark:border-zinc-800 px-4 py-2.5">
                    <Link
                        href="/trace/notifications"
                        class="flex items-center justify-center gap-1.5 text-[11px] font-semibold text-[#0C447C] hover:text-[#0a3968] dark:text-[#85B7EB] dark:hover:text-white transition-colors rounded-lg py-1"
                        @click="open = false"
                    >
                        Lihat Semua Notifikasi
                        <ExternalLink class="h-3 w-3" />
                    </Link>
                </div>
            </div>
        </Transition>
    </div>

    <!-- Toast Notification -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-2 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-2 scale-95"
        >
            <div
                v-if="toast && !open"
                class="fixed bottom-6 right-6 z-[200] w-[340px] rounded-2xl bg-white border border-slate-200 shadow-2xl shadow-slate-300/40 dark:bg-zinc-900 dark:border-zinc-700 dark:shadow-zinc-950/60 overflow-hidden cursor-pointer"
                @click="handleToastClick"
            >
                <!-- Progress bar -->
                <div class="h-0.5 bg-slate-100 dark:bg-zinc-800">
                    <div class="h-full bg-[#0C447C] dark:bg-[#85B7EB] toast-progress" />
                </div>

                <div class="flex items-start gap-3 p-4">
                    <!-- Icon -->
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl shrink-0"
                        :class="getIconColor(toast.type)"
                    >
                        <component :is="getIcon(toast.type)" class="h-5 w-5" />
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] font-bold text-[#0C447C] dark:text-[#85B7EB] uppercase tracking-wider mb-0.5">Notifikasi Baru</p>
                        <p class="text-[13px] font-bold text-slate-800 dark:text-white leading-tight">
                            {{ toast.title }}
                        </p>
                        <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5 line-clamp-2">
                            {{ toast.message }}
                        </p>
                    </div>

                    <!-- Dismiss -->
                    <button
                        @click.stop="dismissToast"
                        aria-label="Tutup"
                        class="shrink-0 mt-0.5 flex h-6 w-6 items-center justify-center rounded-lg text-slate-300 hover:text-slate-500 hover:bg-slate-100 dark:text-zinc-600 dark:hover:text-zinc-400 dark:hover:bg-zinc-800 transition-colors"
                    >
                        <XCircle class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
@keyframes toast-countdown {
    from { width: 100%; }
    to { width: 0%; }
}
.toast-progress {
    animation: toast-countdown 5s linear forwards;
}
</style>
