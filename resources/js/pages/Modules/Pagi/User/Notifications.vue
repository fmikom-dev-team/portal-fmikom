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
import MotionTabs from "@/components/ui/tabs/MotionTabs.vue";
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

const notificationTabs = computed(() => [
	{ id: "all", label: "Semua" },
	{
		id: "unread",
		label: "Belum Dibaca",
		badge:
			computedUnreadCount.value > 0 ? computedUnreadCount.value : undefined,
		badgeClass:
			activeTab.value === "unread"
				? "bg-red-50 dark:bg-zinc-650 text-red-600 dark:text-red-400"
				: "bg-red-500 text-white",
	},
	{ id: "read", label: "Dibaca" },
]);

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

const isFollowingBack = (senderId: any) => {
	const following =
		page.props.auth?.user?.following ??
		page.props.auth?.user?.metadata?.following ??
		[];
	const sId = Number(senderId);
	return following.some((id: any) => Number(id) === sId);
};

const toggleFollback = async (notif: any) => {
	const senderId = notif.sender_id;
	if (!senderId) return;

	FollbackInProgress.value[senderId] = true;
	try {
		const res = await axios.post(`/pagi/users/${senderId}/follow`);
		let following =
			page.props.auth?.user?.following ??
			page.props.auth?.user?.metadata?.following ??
			[];
		following = [...following];
		const sId = Number(senderId);

		if (res.data.following) {
			if (!following.some((id: any) => Number(id) === sId)) {
				following.push(sId);
			}
		} else {
			following = following.filter((id: any) => Number(id) !== sId);
		}

		if (page.props.auth?.user) {
			if (!page.props.auth.user.metadata) {
				page.props.auth.user.metadata = {};
			}
			page.props.auth.user.metadata.following = following;
			page.props.auth.user.following = following;
		}
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
	<Head>
        <title>{{ moduleName + ' — Notifikasi' }}</title>
    </Head>

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

				<!-- Tab Filters Card with motion transition -->
				<MotionTabs
					v-model="activeTab"
					:tabs="notificationTabs"
					variant="pill"
					container-class="w-full flex rounded-2xl border border-slate-200/80 dark:border-zinc-800 bg-slate-100/70 dark:bg-zinc-900/60 p-1 gap-1"
					pill-class="bg-white dark:bg-zinc-800 rounded-xl shadow-xs border border-slate-200/50 dark:border-zinc-700/50"
					active-class="text-slate-900 dark:text-white font-black flex-1 py-2 text-[10px] sm:text-xs tracking-wider justify-center"
					inactive-class="text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-zinc-200 flex-1 py-2 text-[10px] sm:text-xs font-bold tracking-wider justify-center"
				/>

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
				<div v-for="group in filteredGroups" :key="group.group" class="space-y-2.5 sm:space-y-3.5">
					<!-- Group Label (Instagram Style) -->
					<h2 class="text-xs sm:text-[13px] font-extrabold text-slate-900 dark:text-zinc-100 px-1 pt-2">
						{{ group.group }}
					</h2>

					<!-- Items -->
					<div
						v-for="notif in group.items"
						:key="notif.id"
						@click="handleItemClick(notif)"
						class="w-full flex items-center justify-between gap-3.5 rounded-2xl border p-3 sm:p-3.5 transition-all duration-200 cursor-pointer group"
						:class="[
							notif.unread
								? 'bg-slate-100/90 dark:bg-zinc-900/90 border-slate-200/90 dark:border-zinc-800 shadow-3xs'
								: 'bg-white/80 dark:bg-zinc-950/60 border-slate-100/70 dark:border-zinc-900/50 hover:bg-slate-50 dark:hover:bg-zinc-900/60'
						]"
					>
						<!-- Left: Avatar + Text Content -->
						<div class="flex items-center gap-3.5 min-w-0 flex-1">
							<!-- Avatar with type badge -->
							<div class="relative shrink-0">
								<img v-if="notif.avatar" :src="notif.avatar" alt="Avatar" class="w-11 h-11 sm:w-12 sm:h-12 rounded-full object-cover border border-slate-200/80 dark:border-zinc-800 bg-slate-50 shadow-3xs" />
								<div v-else class="w-11 h-11 sm:w-12 sm:h-12 rounded-full flex items-center justify-center border shrink-0 shadow-3xs"
									:class="notif.type === 'system'
										? 'bg-indigo-50 border-indigo-100 dark:bg-indigo-950/30 dark:border-indigo-900 text-indigo-600 dark:text-indigo-400'
										: 'bg-slate-100 border-slate-200 dark:bg-zinc-850 dark:border-zinc-800 text-slate-500'"
								>
									<component :is="typeIcon(notif.type)" class="w-5 h-5" />
								</div>

								<!-- Type indicator badge -->
								<span
									class="absolute -bottom-0.5 -right-0.5 flex h-4.5 w-4.5 sm:h-5 sm:w-5 rounded-full items-center justify-center border-2 border-white dark:border-zinc-950 text-white shadow-xs"
									:class="typeBg(notif.type)"
								>
									<Heart v-if="notif.type === 'like'" class="w-2.5 h-2.5 fill-white" />
									<Users v-else-if="notif.type === 'follow'" class="w-2.5 h-2.5" />
									<MessageSquare v-else-if="notif.type === 'comment' || notif.type === 'reply'" class="w-2.5 h-2.5 fill-white" />
									<Bell v-else class="w-2.5 h-2.5" />
								</span>
							</div>

							<!-- Content text (Instagram style: bold username + text + inline timestamp) -->
							<div class="min-w-0 flex-1">
								<p class="text-xs sm:text-[13px] font-normal text-slate-800 dark:text-zinc-200 leading-snug">
									<span class="font-bold text-slate-950 dark:text-white hover:underline cursor-pointer">{{ notif.title }}</span>
									{{ ' ' + notif.message }}
									<span class="text-[11px] font-medium text-slate-400 dark:text-zinc-500 ml-1 whitespace-nowrap">
										{{ notif.time }}
									</span>
								</p>

								<!-- Unread indicator & Collaboration actions -->
								<div v-if="notif.unread || ((notif.type === 'collaboration_invite' || (notif.type === 'collaboration' && notif.is_invite !== false && !notif.message.includes('menerima'))) && notif.portfolio_id)" class="flex items-center gap-2 mt-1 flex-wrap">
									<span v-if="notif.unread" class="inline-block w-2 h-2 rounded-full bg-blue-600 shrink-0"></span>

									<!-- Collaboration invite buttons -->
									<div v-if="(notif.type === 'collaboration_invite' || (notif.type === 'collaboration' && notif.is_invite !== false && !notif.message.includes('menerima'))) && notif.portfolio_id" class="flex items-center gap-2 mt-1">
										<template v-if="!notif.collaboration_handled">
											<button
												@click.stop="handleCollaborationResponse(notif, 'accept')"
												:disabled="collaborationActionInProgress[notif.id] !== undefined && collaborationActionInProgress[notif.id] !== null"
												class="px-3 py-1 bg-indigo-600 hover:bg-indigo-500 text-white text-[10px] font-bold rounded-lg shadow-2xs transition-all active:scale-97 cursor-pointer border-none"
											>
												{{ collaborationActionInProgress[notif.id] === 'accept' ? 'Proses...' : 'Terima' }}
											</button>
											<button
												@click.stop="handleCollaborationResponse(notif, 'decline')"
												:disabled="collaborationActionInProgress[notif.id] !== undefined && collaborationActionInProgress[notif.id] !== null"
												class="px-3 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 text-[10px] font-bold rounded-lg transition-all active:scale-97 cursor-pointer border-none"
											>
												{{ collaborationActionInProgress[notif.id] === 'decline' ? 'Proses...' : 'Tolak' }}
											</button>
										</template>
										<span v-else class="text-xs font-semibold" :class="notif.collaboration_status === 'accept' ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500'">
											{{ notif.collaboration_status === 'accept' ? 'Undangan diterima' : 'Undangan ditolak' }}
										</span>
									</div>
								</div>
							</div>
						</div>

						<!-- Right: Instagram Action / Thumbnail -->
						<div class="flex items-center gap-2 shrink-0">
							<!-- Follow / Follback Button -->
							<button
								v-if="notif.type === 'follow' && notif.sender_id"
								@click.stop="toggleFollback(notif)"
								:disabled="FollbackInProgress[notif.sender_id]"
								class="inline-flex items-center justify-center px-3.5 py-1.5 text-xs font-bold rounded-xl transition-all active:scale-97 cursor-pointer shadow-3xs"
								:class="isFollowingBack(notif.sender_id)
									? 'bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 hover:bg-slate-200 dark:hover:bg-zinc-700'
									: 'bg-blue-600 hover:bg-blue-700 text-white shadow-xs'"
							>
								{{ FollbackInProgress[notif.sender_id] ? '...' : (isFollowingBack(notif.sender_id) ? 'Mengikuti' : 'Follback') }}
							</button>

							<!-- Work Thumbnail Preview (Instagram Style) -->
							<div v-else-if="notif.work_image" class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl overflow-hidden border border-slate-200/80 dark:border-zinc-800 bg-slate-100 dark:bg-zinc-900 shadow-3xs shrink-0">
								<img :src="notif.work_image" alt="Work thumbnail" class="w-full h-full object-cover" />
							</div>

							<!-- Delete button (visible on hover or focus) -->
							<button @click.stop="deleteNotif(notif.id)" title="Hapus" class="opacity-0 group-hover:opacity-100 p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/20 transition-all cursor-pointer">
								<X class="w-4 h-4" />
							</button>
						</div>
					</div>
				</div>

			</div>
		</main>

	</div>
</template>
