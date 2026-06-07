<script setup lang="ts">
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import axios from "axios";
import {
	AlertCircle,
	ArrowLeft,
	BadgeCheck,
	Bell,
	Check,
	CheckCircle,
	ChevronRight,
	Circle,
	Heart,
	MessageSquare,
	Plus,
	Settings,
	Trash2,
	Users,
	X,
} from "lucide-vue-next";
import { computed, ref } from "vue";
import Footer from "./ui/Footer.vue";
import Navbar from "./ui/Navbar.vue";

const props = defineProps<{
	moduleName: string;
	roleName: string;
	notifGroups: Array<{ group: string; items: any[] }>;
	unreadCount: number;
}>();

const page = usePage();
const currentUser = computed(
	() => page.props.auth?.user || { name: "User", email: "", foto_path: null },
);

// Active Tab Filter
const activeTab = ref<"all" | "unread" | "read">("all");

// Local copy of notification groups (so we can mutate without server round-trip)
const groups = ref(
	props.notifGroups.map((g) => ({
		...g,
		items: g.items.map((n: any) => ({ ...n })),
	})),
);

// Flatten helper
const allItems = computed(() => groups.value.flatMap((g) => g.items));

const computedUnreadCount = computed(
	() => allItems.value.filter((n) => n.unread).length,
);

const filteredGroups = computed(() => {
	return groups.value
		.map((g) => ({
			...g,
			items: g.items.filter((n) => {
				if (activeTab.value === "unread") return n.unread;
				if (activeTab.value === "read") return !n.unread;
				return true;
			}),
		}))
		.filter((g) => g.items.length > 0);
});

// Interactions
const markAsRead = (notif: any) => {
	if (!notif.unread) return;
	notif.unread = false;
	axios.post(`/pagi/notifications/${notif.id}/mark-read`).catch(() => {
		notif.unread = true;
	});
};

const handleItemClick = (notif: any) => {
	markAsRead(notif);
	router.visit(notif.href || "/pagi");
};

const deleteNotif = (id: string) => {
	groups.value = groups.value
		.map((g) => ({
			...g,
			items: g.items.filter((n) => n.id !== id),
		}))
		.filter((g) => g.items.length > 0);
	axios.delete(`/pagi/notifications/${id}`).catch(() => {
		// Silently fail; page reload will restore from server
	});
};

const markAllAsRead = () => {
	for (const g of groups.value) {
		for (const n of g.items) {
			n.unread = false;
		}
	}
	axios.post("/pagi/notifications/mark-all-read");
};

const clearAll = () => {
	groups.value = [];
	axios.delete("/pagi/notifications/clear-all");
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
		const res = await axios.post(`/pagi/users/${senderId}/follow`);
		const following = page.props.auth.user.metadata?.following ?? [];
		if (res.data.following) {
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

// Icon per type
const typeIcon = (type: string) => {
	if (type === "like") return Heart;
	if (type === "follow" || type === "collaboration") return Users;
	if (type === "comment" || type === "reply") return MessageSquare;
	return Bell;
};

const typeBg = (type: string) => {
	if (type === "like") return "bg-pink-500";
	if (type === "follow") return "bg-blue-500";
	if (type === "collaboration") return "bg-indigo-600";
	if (type === "comment" || type === "reply") return "bg-emerald-500";
	return "bg-indigo-500";
};

const collaborationActionInProgress = ref<
	Record<string, "accept" | "decline" | null>
>({});

const handleCollaborationResponse = async (
	notif: any,
	action: "accept" | "decline",
) => {
	collaborationActionInProgress.value[notif.id] = action;
	try {
		await axios.post(
			`/pagi/editor/${notif.portfolio_id}/collaboration/${action}`,
		);
		markAsRead(notif);
		deleteNotif(notif.id);
	} catch (e) {
		console.error(`Collaboration ${action} failed:`, e);
	} finally {
		collaborationActionInProgress.value[notif.id] = null;
	}
};
</script>


<template>
	<Head :title="moduleName + ' — Notifikasi'" />

	<div class="min-h-screen bg-slate-50 dark:bg-zinc-950 font-sans text-slate-900 dark:text-zinc-100 flex flex-col">
		<Navbar />

		<!-- Main Notifications Space -->
		<main class="flex-1 w-full max-w-[700px] mx-auto px-3 sm:px-4 py-5 sm:py-8 pb-24 sm:pb-8 select-none">
			<div class="w-full flex flex-col gap-4 sm:gap-6">

				<!-- Header Actions Banner -->
				<div class="flex flex-col gap-3 border-b border-slate-200/80 dark:border-zinc-800 pb-4">
					<div class="flex items-start justify-between gap-2">
						<div>
							<h1 class="text-lg sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
								<Bell class="w-5 h-5 sm:w-6 sm:h-6 text-indigo-600 dark:text-indigo-400 shrink-0" />
								Pusat Notifikasi
							</h1>
							<p class="text-[10px] sm:text-xs text-slate-450 dark:text-zinc-500 font-semibold mt-1">
								{{ computedUnreadCount > 0 ? `${computedUnreadCount} notifikasi belum dibaca` : 'Semua notifikasi sudah dibaca' }}
							</p>
						</div>

						<!-- Action Buttons -->
						<div class="flex items-center gap-1.5 shrink-0 mt-0.5">
							<button
								@click="markAllAsRead"
								v-if="computedUnreadCount > 0"
								class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl border border-slate-200/85 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-[10px] sm:text-xs font-bold text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-850 shadow-2xs transition-all active:scale-97 cursor-pointer"
							>
								<Check class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
								<span class="hidden sm:inline">Tandai Dibaca</span>
								<span class="sm:hidden">Dibaca</span>
							</button>
							<button
								@click="clearAll"
								v-if="groups.length > 0"
								class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl border border-red-200/50 dark:border-red-950/20 hover:bg-red-50 dark:hover:bg-red-950/10 text-[10px] sm:text-xs font-bold text-red-600 dark:text-red-400 transition-all active:scale-97 cursor-pointer"
							>
								<Trash2 class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
								<span class="hidden sm:inline">Hapus Semua</span>
								<span class="sm:hidden">Hapus</span>
							</button>
						</div>
					</div>
				</div>

				<!-- Tab Filters Card -->
				<div class="w-full flex rounded-2xl border border-slate-200/80 dark:border-zinc-800 bg-white/95 dark:bg-zinc-900/95 shadow-2xs p-1 gap-1">
					<button
						v-for="tab in (['all', 'unread', 'read'] as const)"
						:key="tab"
						@click="activeTab = tab"
						class="flex-1 py-2 text-[10px] sm:text-xs font-bold rounded-xl uppercase tracking-wider transition-all cursor-pointer"
						:class="[
							activeTab === tab
								? 'bg-slate-950 text-white dark:bg-white dark:text-zinc-950 shadow-2xs font-black'
								: 'text-slate-500 hover:text-slate-900 hover:bg-slate-50 dark:hover:bg-zinc-850 dark:text-zinc-450 dark:hover:text-zinc-200'
						]"
					>
						{{ tab === 'all' ? 'Semua' : tab === 'unread' ? 'Belum Dibaca' : 'Dibaca' }}
								<span
									v-if="tab === 'unread' && computedUnreadCount > 0"
									class="ml-1 px-1.5 py-0.5 rounded-full text-[8px] bg-red-500 text-white font-bold leading-none"
								>
									{{ computedUnreadCount }}
						</span>
					</button>
				</div>

				<!-- Empty State -->
				<div
					v-if="filteredGroups.length === 0"
					class="flex flex-col items-center justify-center py-14 px-4 bg-white dark:bg-zinc-900/40 rounded-2xl border border-slate-200/80 dark:border-zinc-800 text-center"
				>
					<Bell class="w-10 h-10 text-slate-300 dark:text-zinc-700 mb-3" />
					<h3 class="text-sm font-bold text-slate-800 dark:text-zinc-200">Tidak ada notifikasi</h3>
					<p class="text-xs text-slate-450 dark:text-zinc-550 mt-1">Notifikasi yang relevan dengan portofolio Anda akan tampil di sini.</p>
				</div>

				<!-- Grouped Notification Stream -->
				<div v-for="group in filteredGroups" :key="group.group" class="space-y-2 sm:space-y-3">
					<!-- Group Label -->
					<h2 class="text-xs font-black text-slate-500 dark:text-zinc-500 uppercase tracking-widest px-1">
						{{ group.group }}
					</h2>

					<!-- Items -->
					<div
						v-for="notif in group.items"
						:key="notif.id"
						@click="handleItemClick(notif)"
						class="w-full flex items-center justify-between gap-3 rounded-2xl border p-3 sm:p-4 transition-all duration-200 cursor-pointer hover:border-indigo-300 dark:hover:border-zinc-700"
						:class="[
							notif.unread
								? 'bg-white dark:bg-zinc-900 border-indigo-200/80 dark:border-indigo-950/60 shadow-xs'
								: 'bg-white/70 dark:bg-zinc-900/30 border-slate-200/60 dark:border-zinc-800/80 hover:bg-white dark:hover:bg-zinc-900'
						]"
					>
						<!-- Left: Avatar + Content -->
						<div class="flex items-center gap-3 min-w-0 flex-1">
							<!-- Avatar with type badge -->
							<div class="relative shrink-0">
								<img v-if="notif.avatar" :src="notif.avatar" class="w-10 h-10 sm:w-11 sm:h-11 rounded-full object-cover border border-slate-100 dark:border-zinc-800 bg-slate-50" />
								<div v-else class="w-10 h-10 sm:w-11 sm:h-11 rounded-full flex items-center justify-center border shrink-0"
									:class="notif.type === 'system'
										? 'bg-indigo-50 border-indigo-100 dark:bg-indigo-950/30 dark:border-indigo-900 text-indigo-600 dark:text-indigo-400'
										: 'bg-slate-100 border-slate-200 dark:bg-zinc-850 dark:border-zinc-800 text-slate-500'"
								>
									<component :is="typeIcon(notif.type)" class="w-5 h-5" />
								</div>

								<!-- Type indicator badge -->
								<span
									class="absolute -bottom-0.5 -right-0.5 flex h-4 w-4 sm:h-5 sm:w-5 rounded-full items-center justify-center border-2 border-white dark:border-zinc-900 text-white shadow-xs"
									:class="typeBg(notif.type)"
								>
									<Heart v-if="notif.type === 'like'" class="w-2 h-2 fill-white" />
									<Users v-else-if="notif.type === 'follow'" class="w-2 h-2" />
									<MessageSquare v-else-if="notif.type === 'comment' || notif.type === 'reply'" class="w-2 h-2" />
									<Bell v-else class="w-2 h-2" />
								</span>
							</div>

							<!-- Content -->
							<div class="min-w-0 flex-1">
								<p class="text-xs sm:text-[13px] font-semibold text-slate-800 dark:text-zinc-200 leading-snug line-clamp-2">
									<span class="font-black text-slate-900 dark:text-white">{{ notif.title }}</span>
									{{ ' ' + notif.message }}
								</p>
								<div class="flex items-center gap-3 mt-1.5 flex-wrap">
									<span class="text-[9px] sm:text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wide">
										{{ notif.time }}
									</span>
									<span v-if="notif.unread" class="w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0"></span>

									<!-- Follback Button -->
									<button
										v-if="notif.type === 'follow' && notif.sender_id"
										@click.stop="toggleFollback(notif)"
										:disabled="FollbackInProgress[notif.sender_id]"
										class="inline-flex items-center gap-1 px-3 py-1 text-[9px] sm:text-[10px] font-black uppercase tracking-wider rounded-lg transition-all active:scale-97 cursor-pointer"
										:class="isFollowingBack(notif.sender_id)
											? 'bg-slate-100 dark:bg-zinc-800 text-slate-500 dark:text-zinc-400 hover:bg-slate-200 dark:hover:bg-zinc-700'
											: 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-xs'"
									>
										{{ FollbackInProgress[notif.sender_id] ? 'Proses...' : (isFollowingBack(notif.sender_id) ? 'Mengikuti' : 'Follback') }}
									</button>

									<!-- Collaboration invite buttons -->
									<div v-if="notif.type === 'collaboration' && notif.portfolio_id" class="flex items-center gap-2 mt-2">
										<button
											@click.stop="handleCollaborationResponse(notif, 'accept')"
											:disabled="collaborationActionInProgress[notif.id] !== undefined && collaborationActionInProgress[notif.id] !== null"
											class="px-3 py-1 bg-indigo-600 hover:bg-indigo-500 text-white text-[9px] sm:text-[10px] font-black uppercase tracking-wider rounded-lg shadow-xs transition-all active:scale-97 cursor-pointer border-none"
										>
											{{ collaborationActionInProgress[notif.id] === 'accept' ? 'Proses...' : 'Terima' }}
										</button>
										<button
											@click.stop="handleCollaborationResponse(notif, 'decline')"
											:disabled="collaborationActionInProgress[notif.id] !== undefined && collaborationActionInProgress[notif.id] !== null"
											class="px-3 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 text-[9px] sm:text-[10px] font-black uppercase tracking-wider rounded-lg transition-all active:scale-97 cursor-pointer border-none"
										>
											{{ collaborationActionInProgress[notif.id] === 'decline' ? 'Proses...' : 'Tolak' }}
										</button>
									</div>
								</div>
							</div>
						</div>

						<!-- Right: Actions -->
						<div class="flex items-center gap-0.5 shrink-0">
							<Link :href="notif.href || '/pagi'" @click.stop class="p-1.5 rounded-lg text-slate-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all">
								<ChevronRight class="w-4 h-4" />
							</Link>
							<button @click.stop="deleteNotif(notif.id)" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/20 transition-all cursor-pointer">
								<X class="w-4 h-4" />
							</button>
						</div>
					</div>
				</div>

			</div>
		</main>

		<Footer class="mt-auto hidden sm:block" />
	</div>
</template>
