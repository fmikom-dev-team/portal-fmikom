<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import {
	AlertTriangle,
	Bell,
	Check,
	ChevronRight,
	ShieldAlert,
	Sparkles,
	UserPlus,
	X,
} from "lucide-vue-next";
import { computed, ref } from "vue";

export interface NotificationItem {
	id: string;
	type: string;
	title: string;
	message: string;
	avatar?: string | null;
	href?: string;
	unread: boolean;
	time: string;
	created_at: string;
	// biome-ignore lint/suspicious/noExplicitAny: custom metadata
	extra?: any;
}

const props = defineProps<{
	isOpen: boolean;
	notifications: NotificationItem[];
	unreadCount: number;
}>();

const emit = defineEmits<{
	close: [];
	markRead: [id: string];
	markAllRead: [];
}>();

const activeTab = ref("all");

const tabs = [
	{ key: "all", label: "Semua" },
	{ key: "report", label: "Laporan" },
	{ key: "moderation", label: "Moderasi" },
	{ key: "system", label: "Sistem" },
];

const getTabCount = (key: string) => {
	if (key === "all") return props.notifications.length;
	if (key === "report")
		return props.notifications.filter(
			(n) => n.type === "report" || n.type === "pagi_report",
		).length;
	if (key === "moderation")
		return props.notifications.filter((n) =>
			[
				"moderation",
				"takedown",
				"warning",
				"admin_action",
				"admin_warning",
				"admin_takedown",
			].includes(n.type),
		).length;
	if (key === "system")
		return props.notifications.filter(
			(n) =>
				![
					"report",
					"pagi_report",
					"moderation",
					"takedown",
					"warning",
					"admin_action",
					"admin_warning",
					"admin_takedown",
				].includes(n.type),
		).length;
	return 0;
};

const filteredNotifications = computed(() => {
	return props.notifications.filter((n) => {
		if (activeTab.value === "all") return true;
		if (activeTab.value === "report")
			return n.type === "report" || n.type === "pagi_report";
		if (activeTab.value === "moderation")
			return [
				"moderation",
				"takedown",
				"warning",
				"admin_action",
				"admin_warning",
				"admin_takedown",
			].includes(n.type);
		if (activeTab.value === "system")
			return ![
				"report",
				"pagi_report",
				"moderation",
				"takedown",
				"warning",
				"admin_action",
				"admin_warning",
				"admin_takedown",
			].includes(n.type);
		return true;
	});
});

// Group filtered notifications by relative date
const groupedNotifications = computed(() => {
	const now = new Date();
	const startOfToday = new Date(
		now.getFullYear(),
		now.getMonth(),
		now.getDate(),
	);
	const startOfWeek = new Date(startOfToday.getTime() - 7 * 86400000);

	const items = filteredNotifications.value;

	const today = items.filter((n) => {
		if (!n.created_at) return true;
		return new Date(n.created_at) >= startOfToday;
	});
	const week = items.filter((n) => {
		if (!n.created_at) return false;
		const d = new Date(n.created_at);
		return d >= startOfWeek && d < startOfToday;
	});
	const older = items.filter((n) => {
		if (!n.created_at) return false;
		return new Date(n.created_at) < startOfWeek;
	});

	const result = [];
	if (today.length) result.push({ group: "Hari Ini", items: today });
	if (week.length) result.push({ group: "Minggu Ini", items: week });
	if (older.length) result.push({ group: "Sebelumnya", items: older });

	return result;
});

const getTypeIcon = (type: string) => {
	switch (type) {
		case "report":
		case "pagi_report":
			return ShieldAlert;
		case "warning":
		case "admin_warning":
			return AlertTriangle;
		case "takedown":
		case "admin_takedown":
			return ShieldAlert;
		case "publish":
		case "work":
			return Sparkles;
		case "user":
		case "new_user":
			return UserPlus;
		default:
			return Bell;
	}
};

const getTypeIconStyle = (type: string) => {
	switch (type) {
		case "report":
		case "pagi_report":
			return "bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20";
		case "warning":
		case "admin_warning":
			return "bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20";
		case "takedown":
		case "admin_takedown":
			return "bg-rose-600/10 text-rose-600 dark:text-rose-400 border border-rose-600/20";
		case "publish":
		case "work":
			return "bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20";
		case "user":
		case "new_user":
			return "bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20";
		default:
			return "bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20";
	}
};

const getTypeBadgeStyle = (type: string) => {
	switch (type) {
		case "report":
		case "pagi_report":
			return "bg-rose-500 text-white";
		case "warning":
		case "admin_warning":
			return "bg-amber-500 text-white";
		case "takedown":
		case "admin_takedown":
			return "bg-rose-600 text-white";
		case "publish":
		case "work":
			return "bg-emerald-500 text-white";
		default:
			return "bg-indigo-500 text-white";
	}
};

// Extract message details (Action & Quote)
const formatNotificationMessage = (notif: NotificationItem) => {
	const msg = notif.message || "";
	let action = msg;
	let quote = "";

	const firstQuote = msg.indexOf('"');
	const lastQuote = msg.lastIndexOf('"');
	if (firstQuote !== -1 && lastQuote !== -1 && lastQuote > firstQuote) {
		action = msg.substring(0, firstQuote).trim();
		quote = msg.substring(firstQuote + 1, lastQuote);
	}

	return { action, quote };
};

const handleNotifClick = (notif: NotificationItem) => {
	if (notif.unread) {
		emit("markRead", notif.id);
	}
	emit("close");
	router.visit(notif.href || "/pagi/admin/moderation");
};
</script>

<template>
	<Teleport to="body">
		<!-- Backdrop Overlay -->
		<Transition
			enter-active-class="transition duration-200 ease-out"
			enter-from-class="opacity-0"
			enter-to-class="opacity-100"
			leave-active-class="transition duration-150 ease-in"
			leave-from-class="opacity-100"
			leave-to-class="opacity-0"
		>
			<div
				v-if="isOpen"
				@click="emit('close')"
				class="fixed inset-0 bg-black/30 backdrop-blur-xs z-[9998]"
			/>
		</Transition>

		<!-- Right-Side Fixed Slide Panel Drawer (PAGI User Style) -->
		<Transition name="drawer-transition">
			<div
				v-if="isOpen"
				id="notification-drawer-content"
				@click.stop
				class="fixed inset-0 sm:top-4 sm:right-4 sm:bottom-4 sm:left-auto w-full h-full sm:h-auto sm:w-[400px] md:w-[440px] bg-white dark:bg-zinc-950 sm:bg-white/95 sm:dark:bg-zinc-950/95 border-0 sm:border border-slate-200/80 dark:border-zinc-800/85 rounded-none sm:rounded-3xl shadow-none sm:shadow-[0_10px_50px_-12px_rgba(0,0,0,0.2)] dark:sm:shadow-[0_20px_50px_rgba(0,0,0,0.5)] backdrop-blur-md z-[9999] flex flex-col overflow-hidden text-slate-900 dark:text-zinc-100 font-sans pt-safe pb-safe"
			>
				<!-- Drawer Header -->
				<div class="px-5 sm:px-6 pt-5 pb-4 border-b border-slate-100 dark:border-zinc-900 flex items-center justify-between shrink-0">
					<div class="flex items-center gap-2">
						<button
							@click="emit('close')"
							class="sm:hidden p-1.5 -ml-2 rounded-full text-slate-800 dark:text-zinc-100 hover:bg-slate-100 dark:hover:bg-zinc-900 transition-colors border-none bg-transparent cursor-pointer"
						>
							<ChevronRight class="w-6 h-6 rotate-180" />
						</button>
						<h2 class="text-base sm:text-sm font-extrabold sm:font-black text-slate-900 dark:text-zinc-100 tracking-tight uppercase sm:tracking-widest leading-none">
							Notifikasi
						</h2>
						<span v-if="unreadCount > 0" class="px-2 py-0.5 rounded-full bg-rose-500/10 text-rose-600 dark:text-rose-400 text-[10px] font-black leading-none">
							{{ unreadCount }} baru
						</span>
					</div>
					<div class="flex items-center gap-1.5">
						<button
							v-if="unreadCount > 0"
							@click="emit('markAllRead')"
							title="Tandai semua dibaca"
							class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-900 text-indigo-600 dark:text-indigo-400 transition-colors cursor-pointer border-none bg-transparent"
						>
							<Check class="w-4 h-4" />
						</button>
						<button
							@click="emit('close')"
							title="Tutup"
							class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-900 text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200 transition-colors cursor-pointer border-none bg-transparent"
						>
							<X class="w-4 h-4" />
						</button>
					</div>
				</div>

				<!-- Filter Tabs (Pill style container like Pagi User) -->
				<div class="px-5 py-3 border-b border-slate-100 dark:border-zinc-900 shrink-0 bg-slate-50/60 dark:bg-zinc-900/40">
					<div class="bg-slate-200/50 dark:bg-zinc-900 p-1 rounded-xl flex gap-1 items-center overflow-x-auto" style="scrollbar-width: none;">
						<button
							v-for="tab in tabs"
							:key="tab.key"
							@click.stop.prevent="activeTab = tab.key"
							class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer border-none bg-transparent shrink-0"
							:class="activeTab === tab.key
								? 'bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 shadow-xs'
								: 'text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-zinc-200'"
						>
							<span>{{ tab.label }}</span>
							<span
								class="text-[9px] font-extrabold px-1.5 py-0.5 rounded-md"
								:class="activeTab === tab.key
									? 'bg-slate-100 dark:bg-zinc-700 text-slate-700 dark:text-zinc-200'
									: 'bg-slate-300/40 dark:bg-zinc-800/50 text-slate-500 dark:text-zinc-400'"
							>
								{{ getTabCount(tab.key) }}
							</span>
						</button>
					</div>
				</div>

				<!-- Notification Stream List -->
				<div class="flex-1 overflow-y-auto px-4 sm:px-5 py-3 custom-scrollbar" style="scrollbar-width: thin;">
					<!-- Empty State -->
					<div v-if="groupedNotifications.length === 0" class="flex flex-col items-center justify-center py-16 px-4 text-center gap-2">
						<div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-zinc-900 flex items-center justify-center text-slate-400 dark:text-zinc-500">
							<Bell class="w-5 h-5" />
						</div>
						<p class="text-xs font-bold text-slate-800 dark:text-zinc-300 uppercase tracking-wider">Tidak ada notifikasi</p>
						<p class="text-[11px] text-slate-400 dark:text-zinc-500 max-w-[220px]">Semua laporan dan aktivitas admin terbaru akan muncul di sini.</p>
					</div>

					<!-- Notification Groups -->
					<div v-for="group in groupedNotifications" :key="group.group" class="py-1">
						<h4 class="text-[9px] font-black text-slate-400 dark:text-zinc-500 uppercase tracking-widest px-2 mb-1.5">
							{{ group.group }}
						</h4>

						<div
							v-for="notif in group.items"
							:key="notif.id"
							@click="handleNotifClick(notif)"
							class="w-full flex items-start gap-3 p-3.5 my-1.5 rounded-2xl transition-all duration-200 cursor-pointer group select-none border"
							:class="notif.unread
								? 'bg-indigo-50/60 dark:bg-indigo-950/20 border-indigo-100 dark:border-indigo-900/40 shadow-xs'
								: 'bg-white dark:bg-zinc-950 border-slate-100 dark:border-zinc-900/60 hover:bg-slate-50 dark:hover:bg-zinc-900/50'"
						>
							<!-- Avatar or Icon Badge -->
							<div class="relative shrink-0 mt-0.5">
								<img
									v-if="notif.avatar"
									:src="notif.avatar"
									alt="Avatar"
									class="w-10 h-10 rounded-full object-cover border border-slate-200 dark:border-zinc-800 bg-slate-100"
								/>
								<div
									v-else
									class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 font-bold"
									:class="getTypeIconStyle(notif.type)"
								>
									<component :is="getTypeIcon(notif.type)" class="w-5 h-5" />
								</div>
								<!-- Overlay icon if avatar is present -->
								<div
									v-if="notif.avatar"
									class="absolute -bottom-0.5 -right-0.5 w-4.5 h-4.5 rounded-full flex items-center justify-center ring-2 ring-white dark:ring-zinc-950 text-[10px]"
									:class="getTypeBadgeStyle(notif.type)"
								>
									<component :is="getTypeIcon(notif.type)" class="w-2.5 h-2.5" />
								</div>
							</div>

							<!-- Content area -->
							<div class="min-w-0 flex-1">
								<div class="flex items-start justify-between gap-1.5">
									<p class="text-xs text-slate-700 dark:text-zinc-300 font-medium leading-snug">
										<span class="font-bold text-slate-900 dark:text-white">{{ notif.title }}</span>
										<span class="text-slate-600 dark:text-zinc-400"> {{ formatNotificationMessage(notif).action }}</span>
									</p>
									<div class="flex items-center gap-1 shrink-0 mt-0.5">
										<span class="text-[10px] text-slate-400 dark:text-zinc-500 font-semibold">{{ notif.time }}</span>
										<span v-if="notif.unread" class="w-2 h-2 rounded-full bg-rose-500 dark:bg-rose-400 shrink-0" title="Belum dibaca" />
									</div>
								</div>

								<!-- Detailed quote or reason box -->
								<div
									v-if="formatNotificationMessage(notif).quote"
									class="mt-2 bg-slate-100/70 dark:bg-zinc-900/70 border border-slate-200/60 dark:border-zinc-800/80 p-2.5 rounded-xl text-xs text-slate-700 dark:text-zinc-300 font-medium leading-relaxed"
								>
									"{{ formatNotificationMessage(notif).quote }}"
								</div>
							</div>

							<!-- Hover Chevron -->
							<div class="flex items-center shrink-0 mt-1 opacity-0 group-hover:opacity-100 transition-opacity">
								<ChevronRight class="w-4 h-4 text-slate-400 dark:text-zinc-500" />
							</div>
						</div>
					</div>
				</div>

				<!-- Footer -->
				<div class="border-t border-slate-100 dark:border-zinc-900 p-4 shrink-0 bg-slate-50/50 dark:bg-zinc-950 text-center">
					<button
						@click="emit('close'); router.visit('/pagi/admin/moderation')"
						class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-zinc-900 dark:hover:bg-zinc-800 text-xs font-extrabold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors border-none cursor-pointer"
					>
						Lihat semua notifikasi moderasi
					</button>
				</div>
			</div>
		</Transition>
	</Teleport>
</template>

<style scoped>
.drawer-transition-enter-active {
	transition: transform 280ms cubic-bezier(0.16, 1, 0.3, 1);
}
.drawer-transition-leave-active {
	transition: transform 200ms cubic-bezier(0.16, 1, 0.3, 1);
}
.drawer-transition-enter-from,
.drawer-transition-leave-to {
	transform: translateX(calc(100% + 2rem));
}
.drawer-transition-enter-to,
.drawer-transition-leave-from {
	transform: translateX(0);
}

.custom-scrollbar::-webkit-scrollbar {
	width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
	background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
	background: #e2e8f0;
	border-radius: 9999px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
	background: #27272a;
}
</style>
