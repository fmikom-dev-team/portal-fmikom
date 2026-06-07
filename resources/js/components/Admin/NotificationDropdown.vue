<script setup lang="ts">
import { router } from "@inertiajs/vue3";

interface NotificationItem {
	id: string;
	title: string;
	message: string;
	time: string;
	type: string;
	unread: boolean;
	href?: string;
}

const props = defineProps<{
	notifications: NotificationItem[];
	unreadCount: number;
}>();

const emit = defineEmits<{
	close: [];
	markRead: [id: string];
	markAllRead: [];
}>();

const getDotColor = (type: string) => {
	switch (type) {
		case "report":
			return "bg-rose-500";
		case "warning":
			return "bg-amber-500";
		case "takedown":
			return "bg-rose-400";
		case "comment":
			return "bg-blue-400";
		case "publish":
			return "bg-emerald-400";
		default:
			return "bg-slate-400";
	}
};

const handleNotifClick = (notif: NotificationItem) => {
	if (notif.unread) {
		emit("markRead", notif.id);
	}
	emit("close");
	router.visit(notif.href || "/pagi/admin");
};
</script>

<template>
    <div class="absolute right-0 top-full mt-2 w-[340px] rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 shadow-xl shadow-slate-200/60 dark:shadow-zinc-900 z-50 overflow-hidden">

        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3.5 border-b border-slate-100 dark:border-zinc-800">
            <div class="flex items-center gap-2">
                <h4 class="text-[13px] font-bold text-slate-800 dark:text-zinc-100">Notifikasi</h4>
                <span v-if="unreadCount > 0" class="flex h-4 items-center justify-center rounded-full bg-rose-500 px-1.5 text-[9px] font-black text-white">
                    {{ unreadCount }}
                </span>
            </div>
            <button
                v-if="unreadCount > 0"
                @click="emit('markAllRead')"
                class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 transition-colors"
            >
                Tandai semua dibaca
            </button>
        </div>

        <!-- Notification List -->
        <div class="max-h-[320px] overflow-y-auto divide-y divide-slate-50 dark:divide-zinc-800/50" style="scrollbar-width: thin;">
            <div v-if="notifications.length === 0" class="flex flex-col items-center justify-center py-8 px-4 text-center">
                <p class="text-[12px] text-slate-400 dark:text-zinc-500 font-medium">Tidak ada notifikasi baru</p>
            </div>
            <div
                v-else
                v-for="notif in notifications"
                :key="notif.id"
                @click="handleNotifClick(notif)"
                class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-zinc-800/40 transition-colors cursor-pointer"
                :class="{ 'bg-indigo-50/40 dark:bg-indigo-900/10': notif.unread }"
            >
                <div class="relative shrink-0 mt-0.5">
                    <div class="h-8 w-8 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                        <svg class="h-3.5 w-3.5 text-slate-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <div :class="['absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full border-2 border-white dark:border-zinc-900', getDotColor(notif.type)]" />
                </div>
                <div class="flex-1 min-w-0">
                    <p :class="['text-[12px] leading-tight truncate', !notif.unread ? 'font-medium text-slate-600 dark:text-zinc-400' : 'font-bold text-slate-800 dark:text-zinc-100']">
                        {{ notif.title }}
                    </p>
                    <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-0.5 line-clamp-2">{{ notif.message }}</p>
                    <p class="text-[10px] text-slate-400 dark:text-zinc-600 mt-0.5">{{ notif.time }}</p>
                </div>
                <div v-if="notif.unread" class="mt-2 h-1.5 w-1.5 rounded-full bg-indigo-500 shrink-0" />
            </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-slate-100 dark:border-zinc-800 px-4 py-3 text-center">
            <button
                @click="emit('close'); router.visit('/pagi/admin/moderation')"
                class="text-[12px] font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 transition-colors"
            >
                Lihat semua notifikasi
            </button>
        </div>
    </div>
</template>
