<script setup lang="ts">
import { Link, router, usePage } from "@inertiajs/vue3";
import {
	Bell,
	Check,
	ChevronRight,
	Heart,
	MessageSquare,
	Settings,
	Users,
	X,
} from "lucide-vue-next";
import { computed, ref, watch } from "vue";

const props = defineProps<{
	isOpen: boolean;
	notifications: any[];
}>();

const emit = defineEmits<{
	(e: "update:isOpen", value: boolean): void;
	(e: "admin-action", notif: any): void;
}>();

const page = usePage();
const notifActiveTab = ref("all");

const notifTabs = [
	{ key: "all", label: "Semua" },
	{ key: "follow", label: "Diikuti" },
	{ key: "comment", label: "Komentar" },
	{ key: "like", label: "Suka" },
];

const totalUnread = computed(
	() => props.notifications.filter((n) => n.unread).length,
);

// Group by time period for the panel
const notifGroups = computed(() => {
	const now = new Date();
	const startOfToday = new Date(
		now.getFullYear(),
		now.getMonth(),
		now.getDate(),
	);
	const weekAgo = new Date(startOfToday.getTime() - 7 * 86400000);
	const monthAgo = new Date(startOfToday.getTime() - 30 * 86400000);

	const toGroup = (items: any[]) =>
		items.filter((n) => {
			if (notifActiveTab.value === "all") return true;
			if (notifActiveTab.value === "follow")
				return n.type === "follow" || n.type === "collaboration";
			if (notifActiveTab.value === "comment")
				return n.type === "comment" || n.type === "reply";
			return n.type === notifActiveTab.value;
		});

	const today = toGroup(
		props.notifications.filter((n) => new Date(n.created_at) >= startOfToday),
	);
	const week = toGroup(
		props.notifications.filter(
			(n) =>
				new Date(n.created_at) >= weekAgo &&
				new Date(n.created_at) < startOfToday,
		),
	);
	const month = toGroup(
		props.notifications.filter(
			(n) =>
				new Date(n.created_at) >= monthAgo && new Date(n.created_at) < weekAgo,
		),
	);
	const older = toGroup(
		props.notifications.filter((n) => new Date(n.created_at) < monthAgo),
	);

	const groups = [];
	if (today.length) groups.push({ group: "Hari Ini", items: today });
	if (week.length) groups.push({ group: "Minggu Ini", items: week });
	if (month.length) groups.push({ group: "Bulan Ini", items: month });
	if (older.length) groups.push({ group: "Sebelumnya", items: older });
	return groups;
});

const filteredNotifGroups = computed(() => notifGroups.value);

const handleNotifClick = (notif: any) => {
	if (notif.unread) {
		// Optimistic update
		notif.unread = false;
		// Persist to server
		fetch(`/pagi/notifications/${notif.id}/mark-read`, {
			method: "POST",
			headers: {
				"X-CSRF-TOKEN":
					(
						document.querySelector(
							"meta[name=csrf-token]",
						) as HTMLMetaElement | null
					)?.content || "",
				Accept: "application/json",
			},
		}).catch(() => {
			notif.unread = true;
		});
	}
	emit("update:isOpen", false);

	const isActionable =
		notif.type === "admin_takedown" ||
		notif.type === "admin_warning" ||
		notif.type === "admin_action";
	if (isActionable) {
		emit("admin-action", notif);
	} else {
		router.visit(notif.href || "/pagi");
	}
};

const FollbackInProgress = ref<Record<number, boolean>>({});

const isFollowingBack = (senderId: number) => {
	const following = page.props.auth?.user?.metadata?.following ?? [];
	return following.includes(senderId);
};

const toggleFollback = async (notif: any) => {
	const senderId = notif.sender_id;
	if (!senderId) return;

	FollbackInProgress.value[senderId] = true;
	try {
		const res = await fetch(`/pagi/users/${senderId}/follow`, {
			method: "POST",
			headers: {
				"X-CSRF-TOKEN":
					(
						document.querySelector(
							"meta[name=csrf-token]",
						) as HTMLMetaElement | null
					)?.content || "",
				Accept: "application/json",
				"Content-Type": "application/json",
			},
		});
		const data = await res.json();
		const following = page.props.auth.user.metadata?.following ?? [];
		if (data.following) {
			if (!following.includes(senderId)) following.push(senderId);
		} else {
			const idx = following.indexOf(senderId);
			if (idx > -1) following.splice(idx, 1);
		}
		if (!page.props.auth.user.metadata) {
			page.props.auth.user.metadata = {};
		}
		page.props.auth.user.metadata.following = following;
	} catch (e) {
		console.error("Follback failed:", e);
	} finally {
		FollbackInProgress.value[senderId] = false;
	}
};

const markAllNotifsAsRead = () => {
	props.notifications.forEach((n) => {
		n.unread = false;
	});
	fetch("/pagi/notifications/mark-all-read", {
		method: "POST",
		headers: {
			"X-CSRF-TOKEN":
				(
					document.querySelector(
						"meta[name=csrf-token]",
					) as HTMLMetaElement | null
				)?.content || "",
			Accept: "application/json",
		},
	});
};

const animateItems = ref(false);

watch(
	() => props.isOpen,
	(newVal) => {
		if (newVal) {
			globalThis.setTimeout(() => {
				animateItems.value = true;
			}, 100);
		} else {
			animateItems.value = false;
		}
	},
	{ immediate: true },
);

// Tab Counts Computeds
const countAll = computed(() => props.notifications.length);
const countFollow = computed(
	() =>
		props.notifications.filter(
			(n) => n.type === "follow" || n.type === "collaboration",
		).length,
);
const countComment = computed(
	() =>
		props.notifications.filter(
			(n) => n.type === "comment" || n.type === "reply",
		).length,
);
const countLike = computed(
	() => props.notifications.filter((n) => n.type === "like").length,
);

const getTabCount = (tabKey: string) => {
	if (tabKey === "all") return countAll.value;
	if (tabKey === "follow") return countFollow.value;
	if (tabKey === "comment") return countComment.value;
	if (tabKey === "like") return countLike.value;
	return 0;
};

// Icon per type
const typeIcon = (type: string) => {
	if (type === "like") return Heart;
	if (type === "follow" || type === "collaboration") return Users;
	if (type === "comment" || type === "reply") return MessageSquare;
	return Bell;
};

// Date formatter to "Friday 3:12 PM"
const formatAbsoluteTime = (dateStr: string) => {
	if (!dateStr) return "";
	const d = new Date(dateStr);
	if (Number.isNaN(d.getTime())) return "";
	const days = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
	const dayName = days[d.getDay()];
	let hours = d.getHours();
	const minutes = d.getMinutes().toString().padStart(2, "0");
	const ampm = hours >= 12 ? "PM" : "AM";
	hours = hours % 12;
	hours = hours ? hours : 12;
	return `${dayName} ${hours}:${minutes} ${ampm}`;
};

// Helper to extract actions and quote details from notification message
const formatNotificationMessage = (notif: any) => {
	const msg = notif.message || "";
	let action = msg;
	let target = "";
	let quote = "";

	// Extract quote if present
	const firstQuote = msg.indexOf('"');
	const lastQuote = msg.lastIndexOf('"');
	if (firstQuote !== -1 && lastQuote !== -1 && lastQuote > firstQuote) {
		const prefix = msg.substring(0, firstQuote).trim().replace(/:$/, "");
		const inside = msg.substring(firstQuote + 1, lastQuote);
		if (notif.type === "comment" || notif.type === "reply") {
			action =
				prefix ||
				(notif.type === "reply"
					? "membalas komentar Anda"
					: "mengomentari karya Anda");
			quote = inside;
		} else if (notif.type === "like") {
			action = prefix || "menyukai karya Anda";
			target = inside;
		} else {
			action = prefix;
			quote = inside;
		}
	} else {
		// Clean up follow message text
		if (notif.type === "follow") {
			action = "mulai mengikuti Anda";
		}
	}

	return { action, target, quote };
};
</script>

<template>
	<!-- Backdrop Overlay -->
	<Transition
		enter-active-class="transition duration-[200ms] ease-out"
		enter-from-class="opacity-0"
		enter-to-class="opacity-100"
		leave-active-class="transition duration-[150ms] ease-in"
		leave-from-class="opacity-100"
		leave-to-class="opacity-0"
	>
		<div v-if="isOpen" @click="emit('update:isOpen', false)" class="fixed inset-0 bg-black/30 backdrop-blur-sm z-[9998]" />
	</Transition>

	<!-- Right-Side Fixed Slide Panel Drawer (Floating Inset Variant per Mockup) -->
	<Transition name="drawer-transition">
		<div v-if="isOpen" class="fixed top-4 right-4 bottom-4 left-4 sm:left-auto sm:w-[400px] md:w-[450px] bg-white/95 dark:bg-zinc-950/95 border border-slate-200/80 dark:border-zinc-800/85 rounded-3xl shadow-[0_10px_50px_-12px_rgba(0,0,0,0.15)] dark:shadow-[0_20px_50px_rgba(0,0,0,0.4)] backdrop-blur-md z-[9999] flex flex-col overflow-hidden text-slate-900 dark:text-zinc-100 font-sans">
			
			<!-- Drawer Sticky Header -->
			<div class="px-6 pt-7 pb-4 border-b border-slate-100/80 dark:border-zinc-900/80 flex flex-col gap-4 shrink-0">
				<div class="flex items-center justify-between">
					<div class="flex items-center gap-2">
						<h2 class="text-sm font-black text-slate-800 dark:text-zinc-200 uppercase tracking-widest leading-none mt-0.5">Notifikasi</h2>
						<span v-if="totalUnread > 0" class="px-2 py-0.5 rounded-full bg-blue-600/10 text-blue-650 dark:text-blue-400 text-[9px] font-black leading-none">
							{{ totalUnread }} baru
						</span>
					</div>
					<div class="flex items-center gap-2">
						<button
							v-if="totalUnread > 0"
							@click="markAllNotifsAsRead"
							title="Tandai semua dibaca"
							class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-900 text-slate-500 hover:text-slate-850 dark:text-zinc-400 dark:hover:text-zinc-200 transition-colors cursor-pointer border-none bg-transparent"
						>
							<Check class="w-4 h-4" />
						</button>
						<Link
							href="/pagi/profile?edit=username"
							title="Pengaturan"
							class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-900 text-slate-500 hover:text-slate-850 dark:text-zinc-400 dark:hover:text-zinc-200 transition-colors cursor-pointer"
						>
							<Settings class="w-4 h-4" />
						</Link>
						<button @click="emit('update:isOpen', false)" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-900 text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200 transition-colors cursor-pointer border-none bg-transparent">
							<X class="w-4 h-4" />
						</button>
					</div>
				</div>
			</div>

			<!-- Drawer Sticky Tabs (Mockup-style shared pill container) -->
			<div class="px-6 py-3 border-b border-slate-100/80 dark:border-zinc-900/80 shrink-0">
				<div class="bg-slate-100/70 dark:bg-zinc-900/60 p-1 rounded-xl flex gap-1 items-center overflow-x-auto" style="scrollbar-width: none;">
					<button
						v-for="tab in notifTabs"
						:key="tab.key"
						@click="notifActiveTab = tab.key"
						class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer border-none bg-transparent shrink-0"
						:class="notifActiveTab === tab.key
							? 'bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-100 shadow-xs'
							: 'text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-zinc-200 hover:bg-white/40 dark:hover:bg-zinc-800/20'"
					>
						<span>{{ tab.label }}</span>
						<span
							class="text-[9px] font-extrabold px-1.5 py-0.5 rounded-md transition-colors"
							:class="notifActiveTab === tab.key
								? 'bg-slate-100 dark:bg-zinc-700 text-slate-650 dark:text-zinc-300'
								: 'bg-slate-200/50 dark:bg-zinc-800/50 text-slate-500 dark:text-zinc-400'"
						>
							{{ getTabCount(tab.key) }}
						</span>
					</button>
				</div>
			</div>

			<!-- Scrollable Notification List -->
			<div class="flex-1 overflow-y-auto px-6 py-2 custom-scrollbar" style="scrollbar-width: thin;">
				
				<!-- Empty State -->
				<div
					v-if="filteredNotifGroups.length === 0"
					class="flex flex-col items-center justify-center py-20 px-4 text-center gap-3.5"
				>
					<div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-zinc-900 flex items-center justify-center text-slate-400 dark:text-zinc-650">
						<Bell class="w-5 h-5" />
					</div>
					<div>
						<h3 class="text-xs font-bold text-slate-800 dark:text-zinc-300 uppercase tracking-widest">Tidak ada notifikasi</h3>
						<p class="text-[10px] text-slate-455 dark:text-zinc-550 mt-1 max-w-[240px] mx-auto font-medium leading-relaxed">Update terbaru dari portal kreatif Anda akan muncul di sini.</p>
					</div>
				</div>

				<!-- Groups -->
				<div v-for="group in filteredNotifGroups" :key="group.group" class="py-3">
					<!-- Group Label -->
					<h3 class="text-[9px] font-black text-slate-400 dark:text-zinc-555 uppercase tracking-widest mb-1">
						{{ group.group }}
					</h3>

					<!-- Items -->
					<div
						v-for="(notif, idx) in group.items"
						:key="notif.id"
						:style="{ transitionDelay: `${idx * 20}ms` }"
						class="w-full flex items-start gap-3.5 py-4 border-b border-dashed border-slate-100 dark:border-zinc-800/80 last:border-b-0 transition-all duration-150 ease-out item-stagger"
						:class="[
							animateItems ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-2'
						]"
					>
						<!-- Left: Avatar (Clean, no overlap indicators per mockup) -->
						<div class="relative shrink-0">
							<img v-if="notif.avatar" :src="notif.avatar" alt="Avatar" class="w-9 h-9 rounded-full object-cover border border-slate-100 dark:border-zinc-800 bg-slate-50 shadow-3xs" />
							<div v-else class="w-9 h-9 rounded-full flex items-center justify-center bg-slate-100 border border-slate-200 dark:bg-zinc-900 dark:border-zinc-800 text-slate-500 shrink-0">
								<component :is="typeIcon(notif.type)" class="w-4 h-4" />
							</div>
						</div>

						<!-- Middle: Content and metadata column -->
						<div class="min-w-0 flex-1">
							<!-- Name + Action + Target & Relative Time Row -->
							<div class="flex items-start justify-between gap-3">
								<p class="text-xs text-slate-600 dark:text-zinc-350 font-medium leading-snug">
									<span class="font-bold text-slate-900 dark:text-white">{{ notif.title }}</span>
									{{ ' ' + formatNotificationMessage(notif).action }}
									<span v-if="formatNotificationMessage(notif).target" class="font-bold text-slate-900 dark:text-white"> {{ formatNotificationMessage(notif).target }}</span>
								</p>
								<div class="flex items-center gap-1.5 shrink-0 mt-0.5">
									<span class="text-[10px] text-slate-400 dark:text-zinc-500 font-bold shrink-0">{{ notif.time }}</span>
									<span v-if="notif.unread" class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0" title="Belum dibaca"></span>
								</div>
							</div>

							<!-- Absolute Time underneath -->
							<p class="text-[10px] text-slate-400 dark:text-zinc-555 mt-0.5 font-bold">
								{{ formatAbsoluteTime(notif.created_at) }}
							</p>

							<!-- Quote / Message Content Block -->
							<div v-if="formatNotificationMessage(notif).quote" class="mt-2.5 bg-slate-50/80 dark:bg-zinc-900/50 border border-slate-100 dark:border-zinc-800/60 p-3 rounded-xl text-xs text-slate-600 dark:text-zinc-350 font-medium leading-relaxed shadow-3xs">
								{{ formatNotificationMessage(notif).quote }}
							</div>

							<!-- Follback Button -->
							<div v-if="notif.type === 'follow' && notif.sender_id" class="mt-2.5">
								<button
									@click.stop="toggleFollback(notif)"
									:disabled="FollbackInProgress[notif.sender_id]"
									class="inline-flex items-center px-3 py-1 bg-white dark:bg-zinc-900 hover:bg-slate-50 dark:hover:bg-zinc-800 text-slate-800 dark:text-zinc-200 text-[10px] font-bold uppercase tracking-wider rounded-lg border border-slate-200 dark:border-zinc-800 transition-all active:scale-97 cursor-pointer"
								>
									{{ FollbackInProgress[notif.sender_id] ? 'Proses...' : (isFollowingBack(notif.sender_id) ? 'Following' : 'Follback') }}
								</button>
							</div>
						</div>

						<!-- Right hover chevron -->
						<div class="flex items-center shrink-0 mt-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
							<ChevronRight class="w-3.5 h-3.5 text-slate-450" />
						</div>

					</div>
				</div>

			</div>

		</div>
	</Transition>
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

.item-stagger {
	transition: opacity 300ms cubic-bezier(0.16, 1, 0.3, 1), transform 300ms cubic-bezier(0.16, 1, 0.3, 1);
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
