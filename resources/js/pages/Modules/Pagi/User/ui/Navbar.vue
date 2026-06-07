<script setup lang="ts">
import { Link, router, usePage } from "@inertiajs/vue3";
import {
	BarChart2,
	Bell,
	CheckCircle,
	ChevronDown,
	ChevronRight,
	ChevronUp,
	Circle,
	ExternalLink,
	FileText,
	Folder,
	Image,
	LayoutGrid,
	LogOut,
	Menu,
	MessageSquare,
	Plus,
	TrendingUp,
	User as UserIcon,
	Users,
	X,
} from "lucide-vue-next";
import { computed, onMounted, onUnmounted, ref, watch } from "vue";

const props = defineProps<{
	roleName?: string;
}>();

const page = usePage();

// Real-time unread messages count
const unreadMessagesCount = ref(Number(page.props.unread_messages_count || 0));

watch(
	() => page.props.unread_messages_count,
	(newVal) => {
		unreadMessagesCount.value = Number(newVal || 0);
	},
);

let userChannel: any = null;
const user = computed(
	() => page.props.auth?.user || { name: "User", email: "", foto_path: null },
);

import AdminActionModal from "./AdminActionModal.vue";

const activeAdminNotification = ref<any>(null);
const showAdminModal = ref(false);

const isCompletenessExpanded = ref(false);
const showCompleteness = ref(true);

const completenessItems = computed(() => {
	const items = [
		{ name: "Creator Avatar", completed: !!user.value.foto_path, weight: 20 },
		{
			name: "Username PAGI",
			completed: !!user.value.pagi_username,
			weight: 15,
		},
		{
			name: "Creative Role Title",
			completed: !!user.value.role_title,
			weight: 10,
		},
		{ name: "Professional Bio", completed: !!user.value.bio, weight: 10 },
		{ name: "Location", completed: !!user.value.location, weight: 10 },
		{
			name: "Social Links",
			completed: !!(
				user.value.website ||
				user.value.linkedin ||
				user.value.github ||
				user.value.twitter
			),
			weight: 15,
		},
		{
			name: "Upload a Project",
			completed: !!user.value.has_uploaded_project,
			weight: 20,
		},
	];
	return items;
});

const completenessPercentage = computed(() => {
	return completenessItems.value.reduce((acc, item) => {
		return acc + (item.completed ? item.weight : 0);
	}, 0);
});

const handleCompleteSettingsClick = () => {
	const firstIncomplete = completenessItems.value.find(
		(item) => !item.completed,
	);
	if (!firstIncomplete) return;

	if (firstIncomplete.name === "Creator Avatar") {
		router.visit("/pagi/profile?edit=avatar");
	} else if (firstIncomplete.name === "Username PAGI") {
		router.visit("/pagi/profile?edit=username");
	} else if (
		firstIncomplete.name === "Creative Role Title" ||
		firstIncomplete.name === "Professional Bio"
	) {
		router.visit("/pagi/profile?edit=bio");
	} else if (firstIncomplete.name === "Location") {
		router.visit("/pagi/profile?edit=location");
	} else if (firstIncomplete.name === "Social Links") {
		router.visit("/pagi/profile?edit=socials");
	} else if (firstIncomplete.name === "Upload a Project") {
		router.visit("/pagi/profile?edit=project");
	}
};

const computedRoleName = computed(() => {
	const r =
		props.roleName ||
		(page.props as any).context?.active_role ||
		(page.props.roleName as string) ||
		"Mahasiswa";
	if (r.toLowerCase() === "mahasiswa") return "Mahasiswa";
	if (r.toLowerCase() === "super-admin") return "Super Admin";
	return r.charAt(0).toUpperCase() + r.slice(1);
});

const isMobileMenuOpen = ref(false);
const isExploreOpen = ref(false);
let exploreTimeout: ReturnType<typeof setTimeout> | null = null;

const openExplore = () => {
	if (exploreTimeout) {
		clearTimeout(exploreTimeout);
	}
	isExploreOpen.value = true;
};
const closeExplore = () => {
	exploreTimeout = setTimeout(() => {
		isExploreOpen.value = false;
	}, 150);
};

const exploreItems = [
	{
		icon: Folder,
		label: "Projects",
		desc: "Telusuri portofolio proyek kreatif",
		href: "/pagi",
		color: "text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800",
	},
	{
		icon: Users,
		label: "People",
		desc: "Temukan kreator & talent terbaik",
		href: "/pagi/people",
		color: "text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800",
	},
	{
		icon: BarChart2,
		label: "Analytics",
		desc: "Pantau performa proyek Anda",
		href: "#",
		color: "text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800",
	},
	{
		icon: TrendingUp,
		label: "Trending",
		desc: "Karya yang sedang populer hari ini",
		href: "#",
		color: "text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800",
	},
	{
		icon: LayoutGrid,
		label: "Collections",
		desc: "Kumpulan karya pilihan editor",
		href: "#",
		color: "text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800",
	},
];

// Interactive Realtime Hover State Management
const isNotifOpen = ref(false);
const isMsgOpen = ref(false);
const isProfileModalOpen = ref(false);
const isNotifPanelOpen = ref(false);
const notifActiveTab = ref("all");

const notifTabs = [
	{ key: "all", label: "Semua" },
	{ key: "follow", label: "Diikuti" },
	{ key: "comment", label: "Komentar" },
	{ key: "like", label: "Suka" },
];

// ── Real Notifications from Inertia props ──────────────────────────────────
const rawNotifs = ref<any[]>([]);

// Initialize from server-side props
const syncFromProps = () => {
	rawNotifs.value = ((page.props.recent_notifications as any[]) ?? []).map(
		(n: any) => ({ ...n }),
	);
};
syncFromProps();

watch(
	() => page.props.recent_notifications,
	() => syncFromProps(),
	{ deep: true },
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
			return n.type === notifActiveTab.value;
		});

	const today = toGroup(
		rawNotifs.value.filter((n) => new Date(n.created_at) >= startOfToday),
	);
	const week = toGroup(
		rawNotifs.value.filter(
			(n) =>
				new Date(n.created_at) >= weekAgo &&
				new Date(n.created_at) < startOfToday,
		),
	);
	const month = toGroup(
		rawNotifs.value.filter(
			(n) =>
				new Date(n.created_at) >= monthAgo && new Date(n.created_at) < weekAgo,
		),
	);
	const older = toGroup(
		rawNotifs.value.filter((n) => new Date(n.created_at) < monthAgo),
	);

	const groups = [];
	if (today.length) groups.push({ group: "Hari Ini", items: today });
	if (week.length) groups.push({ group: "Minggu Ini", items: week });
	if (month.length) groups.push({ group: "Bulan Ini", items: month });
	if (older.length) groups.push({ group: "Sebelumnya", items: older });
	return groups;
});

const filteredNotifGroups = computed(() => notifGroups.value);
const totalUnread = computed(
	() => rawNotifs.value.filter((n) => n.unread).length,
);

const toggleNotifPanel = () => {
	isNotifPanelOpen.value = !isNotifPanelOpen.value;
};

const handleNotifClick = (notif: any) => {
	if (notif.unread) {
		// Optimistic update
		notif.unread = false;
		// Persist to server
		fetch(`/pagi/notifications/${notif.id}/mark-read`, {
			method: "POST",
			headers: {
				"X-CSRF-TOKEN": (document.querySelector("meta[name=csrf-token]") as any)
					?.content,
				Accept: "application/json",
			},
		}).catch(() => {
			notif.unread = true;
		});
	}
	isNotifPanelOpen.value = false;

	const isActionable =
		notif.type === "admin_takedown" ||
		notif.type === "admin_warning" ||
		notif.type === "admin_action";
	if (isActionable) {
		activeAdminNotification.value = notif;
		showAdminModal.value = true;
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
				"X-CSRF-TOKEN": (document.querySelector("meta[name=csrf-token]") as any)
					?.content,
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
	rawNotifs.value.forEach((n) => {
		n.unread = false;
	});
	fetch("/pagi/notifications/mark-all-read", {
		method: "POST",
		headers: {
			"X-CSRF-TOKEN": (document.querySelector("meta[name=csrf-token]") as any)
				?.content,
			Accept: "application/json",
		},
	});
};

// Push a new real-time notification to the top of the list
const pushRealtimeNotif = (notification: any) => {
	// Support both direct payload and nested .data payload from standard Laravel BroadcastNotificationCreated
	const payload = notification?.data ? notification.data : notification;

	const mappedNotif = {
		id: notification?.id ?? String(Date.now()),
		type: payload?.type ?? "system",
		title: payload?.title ?? "PAGI",
		message: payload?.message ?? "",
		avatar: payload?.avatar ?? null,
		href: payload?.href ?? "/pagi",
		unread: true,
		time: "baru saja",
		created_at: new Date().toISOString(),
		extra: payload,
	};

	rawNotifs.value.unshift(mappedNotif);

	const isActionable =
		mappedNotif.type === "admin_takedown" ||
		mappedNotif.type === "admin_warning" ||
		mappedNotif.type === "admin_action";
	if (isActionable) {
		activeAdminNotification.value = mappedNotif;
		showAdminModal.value = true;
	}
};

let notifTimeout: ReturnType<typeof setTimeout> | null = null;
let msgTimeout: ReturnType<typeof setTimeout> | null = null;
let profileTimeout: ReturnType<typeof setTimeout> | null = null;

const enterNotif = () => {
	if (notifTimeout) clearTimeout(notifTimeout);
	isNotifOpen.value = true;
	isMsgOpen.value = false;
	isProfileModalOpen.value = false;
};
const leaveNotif = () => {
	notifTimeout = setTimeout(() => {
		isNotifOpen.value = false;
	}, 150);
};
const enterMsg = () => {
	if (msgTimeout) clearTimeout(msgTimeout);
	isMsgOpen.value = true;
	isNotifOpen.value = false;
	isProfileModalOpen.value = false;
};
const leaveMsg = () => {
	msgTimeout = setTimeout(() => {
		isMsgOpen.value = false;
	}, 150);
};
const enterProfile = () => {
	if (profileTimeout) clearTimeout(profileTimeout);
	isProfileModalOpen.value = true;
	isNotifOpen.value = false;
	isMsgOpen.value = false;
};
const leaveProfile = () => {
	profileTimeout = setTimeout(() => {
		isProfileModalOpen.value = false;
	}, 150);
};
const handleLogout = () => {
	router.post("/logout");
};
const closeProfileModal = () => {
	isProfileModalOpen.value = false;
};

// ── Subscribe to user-specific channel for real-time updates ──────────────────
function subscribeUserChannel(userId: number | string) {
	if (!userId || !window.Echo) return;
	const channelName = `App.Models.User.${userId}`;
	try {
		window.Echo.leave(channelName);
	} catch (_) {}
	userChannel = window.Echo.private(channelName)
		.listen(".unread.count.updated", (data: any) => {
			unreadMessagesCount.value = Number(data.unread_messages_count);
		})
		// Real-time push notifications
		.listen(".pagi.notification", (data: any) => {
			pushRealtimeNotif(data);
		})
		.notification((data: any) => {
			// Laravel broadcast notifications
			pushRealtimeNotif(data);
		});
}

const handleUnreadCountUpdate = (e: Event) => {
	unreadMessagesCount.value = Number((e as CustomEvent).detail || 0);
};

onMounted(() => {
	window.addEventListener("click", closeProfileModal);
	window.addEventListener(
		"pagi:unread_messages_count",
		handleUnreadCountUpdate,
	);

	const userId = page.props.auth?.user?.id;
	if (!userId) return;

	// Echo may not be ready immediately — retry up to 5 times with 500ms delay
	let attempts = 0;
	const trySubscribe = () => {
		if (window.Echo) {
			subscribeUserChannel(userId);
		} else if (attempts < 10) {
			attempts++;
			setTimeout(trySubscribe, 500);
		}
	};
	trySubscribe();
});

watch(
	() => page.props.auth?.user?.id,
	(newUserId, oldUserId) => {
		if (!window.Echo) return;
		if (oldUserId) {
			try {
				window.Echo.leave(`App.Models.User.${oldUserId}`);
			} catch (_) {}
		}
		if (newUserId) {
			subscribeUserChannel(newUserId);
		}
	},
);

onUnmounted(() => {
	window.removeEventListener("click", closeProfileModal);
	window.removeEventListener(
		"pagi:unread_messages_count",
		handleUnreadCountUpdate,
	);

	const uid = page.props.auth?.user?.id;
	if (uid && window.Echo) {
		try {
			window.Echo.leave(`App.Models.User.${uid}`);
		} catch (_) {}
	}
});

// Mock messages array removed — replaced by real message data from server
</script>
<template>
	<div class="sticky top-0 z-50 select-none">
		<header class="border-b border-slate-200/80 dark:border-zinc-800 bg-white/95 dark:bg-zinc-950/95 backdrop-blur-md" :class="{ 'hidden md:block': !$page.props.auth?.user }">
			<div class="mx-auto flex h-14 max-w-[1400px] items-center justify-between px-4 gap-4 lg:px-6 w-full">
				<!-- Left: Logo + Module Name (desktop) + Profile (mobile) + Nav -->
				<div class="flex items-center gap-4 shrink-0">
					<!-- Logo + Module Name (desktop only) -->
					<Link href="/pagi" class="hidden md:flex items-center gap-2.5 shrink-0 group">
						<div class="h-8 w-8 rounded-xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center shadow-md group-hover:shadow-indigo-200 dark:group-hover:shadow-indigo-900 transition-all duration-300">
							<span class="text-white text-xs font-black tracking-tight">P</span>
						</div>
						<div class="flex flex-col">
							<span class="text-sm font-black text-slate-900 dark:text-zinc-100 leading-none tracking-tight group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">PAGI</span>
							<span class="text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-widest leading-none mt-0.5">Portal Akademik</span>
						</div>
					</Link>

					<!-- Divider (desktop only) -->
					<div v-if="$page.props.auth?.user" class="hidden md:block h-6 w-px bg-slate-200 dark:bg-zinc-800"></div>

					<!-- Profile Info Group (MOBILE ONLY) -->
					<div v-if="$page.props.auth?.user" class="md:hidden relative shrink-0" @click.stop="isProfileModalOpen = !isProfileModalOpen">
						<div
							class="flex items-center gap-2.5 cursor-pointer hover:opacity-90 transition-all duration-300 group"
						>
							<div class="h-9 w-9 rounded-xl border border-slate-200 dark:border-zinc-800 bg-slate-100 dark:bg-slate-800 overflow-hidden flex items-center justify-center shrink-0 ring-offset-white dark:ring-offset-zinc-950 group-hover:ring-2 ring-indigo-500/50 transition-all duration-300 shadow-xs">
								<img v-if="user.foto_path" :src="'/storage/' + user.foto_path" :alt="user.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-350" />
								<img v-else-if="user.avatar" :src="user.avatar" :alt="user.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-350" />
								<span v-else class="text-slate-700 dark:text-slate-200 text-xs font-black">{{ user.name.charAt(0) }}</span>
							</div>
							<div class="flex flex-col text-left">
								<span class="text-xs font-black text-slate-800 dark:text-zinc-200 leading-none tracking-tight group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ user.name }}</span>
								<span class="text-[9px] font-extrabold text-slate-400 dark:text-zinc-500 uppercase tracking-widest mt-0.5">{{ computedRoleName }}</span>
							</div>
							<ChevronDown class="h-3 w-3 text-slate-400 ml-0.5 shrink-0 group-hover:text-slate-600 transition-colors" />
						</div>

						<!-- ULTRA-PREMIUM PROFILE MODAL PREVIEW CARD (left-aligned) -->
						<Transition
							enter-active-class="transition ease-out duration-250"
							enter-from-class="opacity-0 translate-y-2 scale-95"
							enter-to-class="opacity-100 translate-y-0 scale-100"
							leave-active-class="transition ease-in duration-200"
							leave-from-class="opacity-100 translate-y-0 scale-100"
							leave-to-class="opacity-0 translate-y-2 scale-95"
						>
							<div v-show="isProfileModalOpen" class="absolute left-0 mt-2.5 w-72 rounded-2xl border border-slate-200/80 dark:border-zinc-800 bg-white/95 dark:bg-zinc-950/95 backdrop-blur-xl shadow-[0_24px_50px_-12px_rgba(0,0,0,0.18)] p-4 select-none z-50">
								<!-- Premium Cover Banner -->
								<div class="h-20 w-full relative rounded-xl overflow-hidden bg-gradient-to-r from-[#6366f1] via-[#a855f7] to-[#ec4899] bg-cover bg-center shadow-xs">
									<template v-if="user.banner_path && user.banner_path !== 'null'">
										<video
											v-if="user.banner_path.endsWith('.mp4') || user.banner_path.endsWith('.webm') || user.banner_path.endsWith('.ogg')"
											:src="user.banner_path.startsWith('http') ? user.banner_path : (user.banner_path.startsWith('/storage') ? user.banner_path : '/storage/' + user.banner_path)"
											class="w-full h-full object-cover"
											autoplay
											loop
											muted
											playsinline
										></video>
										<img v-else :src="user.banner_path.startsWith('http') ? user.banner_path : (user.banner_path.startsWith('/storage') ? user.banner_path : '/storage/' + user.banner_path)" class="w-full h-full object-cover" />
									</template>
									<template v-else>
										<div class="absolute inset-0 opacity-90"></div>
										<div class="absolute -top-6 -left-6 w-16 h-16 rounded-full bg-cyan-300/30 blur-md"></div>
										<div class="absolute -bottom-6 -right-6 w-16 h-16 rounded-full bg-yellow-300/20 blur-md"></div>
									</template>
								</div>

								<!-- Profile Info Area -->
								<div class="relative z-10 flex flex-col items-center -mt-10 mb-3 px-2">
									<div class="h-20 w-20 rounded-full border-[3px] border-white dark:border-zinc-950 bg-slate-100 dark:bg-slate-800 overflow-hidden flex items-center justify-center shadow-md mb-2 relative z-20">
										<img v-if="user.foto_path" :src="'/storage/' + user.foto_path" :alt="user.name" class="w-full h-full object-cover" />
										<img v-else-if="user.avatar" :src="user.avatar" :alt="user.name" class="w-full h-full object-cover" />
										<span v-else class="text-slate-700 dark:text-slate-200 text-xl font-bold">{{ user.name.charAt(0) }}</span>
									</div>
									<h3 class="text-base font-black text-slate-800 dark:text-zinc-150 text-center truncate w-full px-2 tracking-tight leading-snug">{{ user.name }}</h3>
									<p class="text-xs text-slate-500 dark:text-zinc-400 font-semibold text-center mt-0.5 truncate w-full px-2 leading-none">@{{ user.pagi_username || 'username' }}</p>
								</div>

								<!-- Stats Grid -->
								<div class="w-full grid grid-cols-3 rounded-2xl border border-slate-100/85 dark:border-zinc-900 bg-slate-50/50 dark:bg-zinc-900/35 py-3.5 mb-4 mt-3 select-none">
									<div class="text-center border-r border-slate-100/85 dark:border-zinc-900">
										<span class="block text-xl font-extrabold text-slate-900 dark:text-zinc-100">{{ user.works_count ?? 0 }}</span>
										<span class="block text-[9px] font-extrabold text-slate-500 dark:text-zinc-400 uppercase tracking-wider mt-1.5">Karya</span>
									</div>
									<div class="text-center border-r border-slate-100/85 dark:border-zinc-900">
										<span class="block text-xl font-extrabold text-slate-900 dark:text-zinc-100">{{ user.certificates_count ?? 2 }}</span>
										<span class="block text-[9px] font-extrabold text-slate-500 dark:text-zinc-400 uppercase tracking-wider mt-1.5">Sertifikat</span>
									</div>
									<div class="text-center">
										<span class="block text-xl font-extrabold text-slate-900 dark:text-zinc-100">{{ user.followers_count ?? (user.metadata?.followers?.length ?? 0) }}</span>
										<span class="block text-[9px] font-extrabold text-slate-500 dark:text-zinc-400 uppercase tracking-wider mt-1.5">Pengikut</span>
									</div>
								</div>

								<!-- Action Menu Items -->
								<div class="space-y-1.5">
									<Link href="/pagi/profile" class="w-full flex items-center justify-center gap-2 rounded-xl bg-slate-950 hover:bg-indigo-600 dark:bg-slate-50 dark:text-slate-950 dark:hover:bg-indigo-500 dark:hover:text-white py-2.5 text-xs font-black text-white transition-all shadow-md active:scale-97 cursor-pointer">
										<UserIcon class="h-3.5 w-3.5" /> Lihat Profil PAGI
									</Link>

									<Link href="/pagi/cv" class="w-full flex items-center justify-center gap-2 rounded-xl border border-slate-200/80 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-900 py-2.5 text-xs font-bold text-slate-700 dark:text-zinc-300 transition-all shadow-xs active:scale-97 cursor-pointer">
										<FileText class="h-3.5 w-3.5 text-slate-450" /> CV Builder
									</Link>

									<Link href="/dashboard" class="w-full flex items-center justify-center gap-2 rounded-xl border border-slate-200/80 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-900 py-2.5 text-xs font-bold text-slate-700 dark:text-zinc-300 transition-all shadow-xs active:scale-97 cursor-pointer">
										<ExternalLink class="h-3.5 w-3.5 text-slate-450" /> Kembali ke Portal
									</Link>

									<button @click="handleLogout" class="w-full flex items-center justify-center gap-2 rounded-xl border border-red-200/60 dark:border-red-950/40 hover:bg-red-50 dark:hover:bg-red-950/20 py-2.5 text-xs font-bold text-red-600 dark:text-red-400 transition-all active:scale-97 cursor-pointer">
										<LogOut class="h-3.5 w-3.5" /> Keluar (Logout)
									</button>
								</div>
							</div>
						</Transition>
					</div>
					<!-- Login Icon/Link for Guests (MOBILE ONLY) -->
					<Link v-else href="/login" class="md:hidden text-slate-600 dark:text-zinc-350 p-2 border border-slate-200 dark:border-zinc-800 rounded-xl flex items-center justify-center cursor-pointer" title="Log In">
						<UserIcon class="h-4.5 w-4.5" />
					</Link>

					<!-- Base Navigation (Desktop only) -->
					<nav v-if="$page.props.auth?.user" class="hidden md:flex items-center gap-0.5">
						<!-- Explore = halaman utama dashboard -->
						<Link
							href="/pagi"
							class="px-3 py-1.5 text-sm font-semibold transition-colors border-b-2"
							:class="[ $page.url === '/pagi' ? 'border-slate-900 text-slate-900 dark:border-slate-100 dark:text-white' : 'border-transparent text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100' ]"
						>
							Explore
						</Link>

						<Link
							href="/pagi/gallery"
							class="px-3 py-1.5 text-sm font-semibold transition-colors border-b-2"
							:class="[ $page.url.startsWith('/pagi/gallery') ? 'border-slate-900 text-slate-900 dark:border-slate-100 dark:text-white' : 'border-transparent text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100' ]"
						>
							Gallery
						</Link>

						<Link
							href="/pagi/people"
							class="px-3 py-1.5 text-sm font-semibold transition-colors border-b-2"
							:class="[ $page.url.startsWith('/pagi/people') ? 'border-slate-900 text-slate-900 dark:border-slate-100 dark:text-white' : 'border-transparent text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100' ]"
						>
							People
						</Link>

						<Link
							v-if="false"
							href="/pagi/works"
							class="px-3 py-1.5 text-sm font-semibold transition-colors border-b-2"
							:class="[ $page.url.startsWith('/pagi/works') ? 'border-slate-900 text-slate-900 dark:border-slate-100 dark:text-white' : 'border-transparent text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100' ]"
						>
							Works
						</Link>

						<Link
							href="/pagi/cv"
							class="px-3 py-1.5 text-sm font-semibold transition-colors border-b-2"
							:class="[ $page.url.startsWith('/pagi/cv') ? 'border-slate-900 text-slate-900 dark:border-slate-100 dark:text-white' : 'border-transparent text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100' ]"
						>
							CV
						</Link>
					</nav>
				</div>

				<!-- Right Actions Area -->
				<div class="flex items-center gap-3 ml-auto">
					<!-- Share Work button -->
					<Link v-if="$page.props.auth?.user" href="/pagi/editor" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-slate-950 text-white hover:bg-indigo-600 p-2 sm:px-4 sm:py-2 text-xs font-bold transition-all shadow-md active:scale-95">
						<Plus class="h-3.5 w-3.5 shrink-0" /> <span class="hidden sm:inline">Share Work</span>
					</Link>

					<!-- Message Icon (desktop only) -->
					<Link v-if="$page.props.auth?.user" href="/pagi/messages" class="hidden md:flex relative p-2 rounded-xl border border-slate-200 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-900 text-slate-600 dark:text-zinc-350 transition-colors items-center justify-center">
						<MessageSquare class="h-4.5 w-4.5" />
						<span v-if="unreadMessagesCount > 0" class="absolute -top-1 -right-1 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-indigo-600 px-1 text-[9px] font-black text-white ring-2 ring-white dark:ring-zinc-950">
							{{ unreadMessagesCount }}
						</span>
					</Link>

					<!-- Notification Bell (click to open panel) -->
					<button v-if="$page.props.auth?.user" @click="toggleNotifPanel" class="relative p-2 rounded-xl border border-slate-200 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-900 text-slate-600 dark:text-zinc-350 transition-colors">
						<Bell class="h-4.5 w-4.5" />
						<span v-if="totalUnread > 0" class="absolute -top-1 -right-1 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-red-500 px-1 text-[9px] font-black text-white ring-2 ring-white dark:ring-zinc-950">
							{{ totalUnread }}
						</span>
					</button>

					<!-- Profile (DESKTOP ONLY — shown on right side) -->
					<div v-if="$page.props.auth?.user" class="hidden md:block relative shrink-0" @mouseenter="enterProfile" @mouseleave="leaveProfile" @click.stop="isProfileModalOpen = !isProfileModalOpen">
						<div
							class="flex items-center gap-2.5 cursor-pointer hover:opacity-90 transition-all duration-300 group"
						>
							<div class="h-9 w-9 rounded-xl border border-slate-200 dark:border-zinc-800 bg-slate-100 dark:bg-slate-800 overflow-hidden flex items-center justify-center shrink-0 ring-offset-white dark:ring-offset-zinc-950 group-hover:ring-2 ring-indigo-500/50 transition-all duration-300 shadow-xs">
								<img v-if="user.foto_path" :src="'/storage/' + user.foto_path" :alt="user.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-350" />
								<img v-else-if="user.avatar" :src="user.avatar" :alt="user.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-350" />
								<span v-else class="text-slate-700 dark:text-slate-200 text-xs font-black">{{ user.name.charAt(0) }}</span>
							</div>
							<div class="flex flex-col text-left">
								<span class="text-xs font-black text-slate-800 dark:text-zinc-200 leading-none tracking-tight group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ user.name }}</span>
								<span class="text-[9px] font-extrabold text-slate-400 dark:text-zinc-500 uppercase tracking-widest mt-0.5">{{ computedRoleName }}</span>
							</div>
							<ChevronDown class="h-3 w-3 text-slate-400 ml-0.5 shrink-0 group-hover:text-slate-600 transition-colors" />
						</div>

						<!-- Profile Dropdown Card (right-aligned for desktop) -->
						<Transition
							enter-active-class="transition ease-out duration-250"
							enter-from-class="opacity-0 translate-y-2 scale-95"
							enter-to-class="opacity-100 translate-y-0 scale-100"
							leave-active-class="transition ease-in duration-200"
							leave-from-class="opacity-100 translate-y-0 scale-100"
							leave-to-class="opacity-0 translate-y-2 scale-95"
						>
							<div v-show="isProfileModalOpen" class="absolute right-0 mt-2.5 w-72 rounded-2xl border border-slate-200/80 dark:border-zinc-800 bg-white/95 dark:bg-zinc-950/95 backdrop-blur-xl shadow-[0_24px_50px_-12px_rgba(0,0,0,0.18)] p-4 select-none z-50">
								<!-- Premium Cover Banner -->
								<div class="h-20 w-full relative rounded-xl overflow-hidden bg-gradient-to-r from-[#6366f1] via-[#a855f7] to-[#ec4899] bg-cover bg-center shadow-xs">
									<template v-if="user.banner_path && user.banner_path !== 'null'">
										<video
											v-if="user.banner_path.endsWith('.mp4') || user.banner_path.endsWith('.webm') || user.banner_path.endsWith('.ogg')"
											:src="user.banner_path.startsWith('http') ? user.banner_path : (user.banner_path.startsWith('/storage') ? user.banner_path : '/storage/' + user.banner_path)"
											class="w-full h-full object-cover"
											autoplay
											loop
											muted
											playsinline
										></video>
										<img v-else :src="user.banner_path.startsWith('http') ? user.banner_path : (user.banner_path.startsWith('/storage') ? user.banner_path : '/storage/' + user.banner_path)" class="w-full h-full object-cover" />
									</template>
									<template v-else>
										<div class="absolute inset-0 opacity-90"></div>
										<div class="absolute -top-6 -left-6 w-16 h-16 rounded-full bg-cyan-300/30 blur-md"></div>
										<div class="absolute -bottom-6 -right-6 w-16 h-16 rounded-full bg-yellow-300/20 blur-md"></div>
									</template>
								</div>
								<!-- Profile Info Area -->
								<div class="relative z-10 flex flex-col items-center -mt-10 mb-3 px-2">
									<div class="h-20 w-20 rounded-full border-[3px] border-white dark:border-zinc-950 bg-slate-100 dark:bg-slate-800 overflow-hidden flex items-center justify-center shadow-md mb-2 relative z-20">
										<img v-if="user.foto_path" :src="'/storage/' + user.foto_path" :alt="user.name" class="w-full h-full object-cover" />
										<img v-else-if="user.avatar" :src="user.avatar" :alt="user.name" class="w-full h-full object-cover" />
										<span v-else class="text-slate-700 dark:text-slate-200 text-xl font-bold">{{ user.name.charAt(0) }}</span>
									</div>
									<h3 class="text-base font-black text-slate-800 dark:text-zinc-150 text-center truncate w-full px-2 tracking-tight leading-snug">{{ user.name }}</h3>
									<p class="text-xs text-slate-500 dark:text-zinc-400 font-semibold text-center mt-0.5 truncate w-full px-2 leading-none">@{{ user.pagi_username || 'username' }}</p>
								</div>
								<!-- Stats Grid -->
								<div class="w-full grid grid-cols-3 rounded-2xl border border-slate-100/85 dark:border-zinc-900 bg-slate-50/50 dark:bg-zinc-900/35 py-3.5 mb-4 mt-3 select-none">
									<div class="text-center border-r border-slate-100/85 dark:border-zinc-900">
										<span class="block text-xl font-extrabold text-slate-900 dark:text-zinc-100">{{ user.works_count ?? 0 }}</span>
										<span class="block text-[9px] font-extrabold text-slate-500 dark:text-zinc-400 uppercase tracking-wider mt-1.5">Karya</span>
									</div>
									<div class="text-center border-r border-slate-100/85 dark:border-zinc-900">
										<span class="block text-xl font-extrabold text-slate-900 dark:text-zinc-100">{{ user.certificates_count ?? 2 }}</span>
										<span class="block text-[9px] font-extrabold text-slate-500 dark:text-zinc-400 uppercase tracking-wider mt-1.5">Sertifikat</span>
									</div>
									<div class="text-center">
										<span class="block text-xl font-extrabold text-slate-900 dark:text-zinc-100">{{ user.followers_count ?? (user.metadata?.followers?.length ?? 0) }}</span>
										<span class="block text-[9px] font-extrabold text-slate-500 dark:text-zinc-400 uppercase tracking-wider mt-1.5">Pengikut</span>
									</div>
								</div>
								<!-- Action Menu Items -->
								<div class="space-y-1.5">
									<Link href="/pagi/profile" class="w-full flex items-center justify-center gap-2 rounded-xl bg-slate-950 hover:bg-indigo-600 dark:bg-slate-50 dark:text-slate-950 dark:hover:bg-indigo-500 dark:hover:text-white py-2.5 text-xs font-black text-white transition-all shadow-md active:scale-97 cursor-pointer">
										<UserIcon class="h-3.5 w-3.5" /> Lihat Profil PAGI
									</Link>
									<Link href="/pagi/cv" class="w-full flex items-center justify-center gap-2 rounded-xl border border-slate-200/80 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-900 py-2.5 text-xs font-bold text-slate-700 dark:text-zinc-300 transition-all shadow-xs active:scale-97 cursor-pointer">
										<FileText class="h-3.5 w-3.5 text-slate-450" /> CV Builder
									</Link>
									<Link href="/dashboard" class="w-full flex items-center justify-center gap-2 rounded-xl border border-slate-200/80 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-900 py-2.5 text-xs font-bold text-slate-700 dark:text-zinc-300 transition-all shadow-xs active:scale-97 cursor-pointer">
										<ExternalLink class="h-3.5 w-3.5 text-slate-450" /> Kembali ke Portal
									</Link>
									<button @click="handleLogout" class="w-full flex items-center justify-center gap-2 rounded-xl border border-red-200/60 dark:border-red-950/40 hover:bg-red-50 dark:hover:bg-red-950/20 py-2.5 text-xs font-bold text-red-600 dark:text-red-400 transition-all active:scale-97 cursor-pointer">
										<LogOut class="h-3.5 w-3.5" /> Keluar (Logout)
									</button>
								</div>
							</div>
						</Transition>
					</div>
					<!-- Log In Button for Guests (DESKTOP ONLY) -->
					<Link 
						v-else 
						href="/login" 
						class="hidden md:inline-flex items-center justify-center rounded-xl bg-indigo-600 hover:bg-indigo-700 px-5 py-2 text-xs font-black text-white transition-all shadow-md active:scale-95 cursor-pointer"
					>
						Log In
					</Link>
				</div>
			</div>
		</header>

		<!-- Modern Premium Bottom Navbar for Mobile Features (Behance/Instagram style) -->
		<div v-if="$page.props.auth?.user && !$page.url.startsWith('/pagi/messages')" class="fixed bottom-0 inset-x-0 h-16 bg-white/85 dark:bg-zinc-950/85 backdrop-blur-xl border-t border-slate-200/80 dark:border-zinc-850 flex items-center justify-around px-4 z-50 md:hidden shadow-[0_-4px_24px_rgba(0,0,0,0.04)] select-none" style="padding-bottom: env(safe-area-inset-bottom, 0px);">
			<!-- 1. Explore (main dashboard) -->
			<Link href="/pagi" class="flex flex-col items-center gap-1 transition-colors"
				:class="[ $page.url === '/pagi' ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-500 hover:text-slate-900 dark:text-zinc-450 dark:hover:text-zinc-200' ]">
				<LayoutGrid class="w-5 h-5 transition-transform active:scale-90" />
				<span class="text-[9px] font-extrabold tracking-tight uppercase">Explore</span>
			</Link>

			<!-- 2. Gallery -->
			<Link href="/pagi/gallery" class="flex flex-col items-center gap-1 transition-colors"
				:class="[ $page.url.startsWith('/pagi/gallery') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-500 hover:text-slate-900 dark:text-zinc-450 dark:hover:text-zinc-200' ]">
				<Image class="w-5 h-5 transition-transform active:scale-90" />
				<span class="text-[9px] font-extrabold tracking-tight uppercase">Gallery</span>
			</Link>

			<!-- 3. Share Project (FAB in the middle!) -->
			<Link href="/pagi/editor" class="flex flex-col items-center justify-center -mt-5 shrink-0 transition-transform active:scale-95">
				<div class="h-11 w-11 rounded-full bg-slate-950 dark:bg-white text-white dark:text-zinc-950 flex items-center justify-center shadow-lg border-2 border-white dark:border-zinc-900 hover:bg-indigo-600 dark:hover:bg-indigo-500">
					<Plus class="w-5 h-5 font-black" />
				</div>
				<span class="text-[8px] font-extrabold tracking-tight uppercase mt-0.5 text-slate-550 dark:text-zinc-400">Share</span>
			</Link>

			<!-- 2. People -->
			<Link href="/pagi/people" class="flex flex-col items-center gap-1 transition-colors"
				:class="[ $page.url.startsWith('/pagi/people') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-500 hover:text-slate-900 dark:text-zinc-450 dark:hover:text-zinc-200' ]">
				<Users class="w-5 h-5 transition-transform active:scale-90" />
				<span class="text-[9px] font-extrabold tracking-tight uppercase">People</span>
			</Link>

			<!-- 4. Works -->
			<Link v-if="false" href="/pagi/works" class="flex flex-col items-center gap-1 transition-colors"
				:class="[ $page.url.startsWith('/pagi/works') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-500 hover:text-slate-900 dark:text-zinc-450 dark:hover:text-zinc-200' ]">
				<Folder class="w-5 h-5 transition-transform active:scale-90" />
				<span class="text-[9px] font-extrabold tracking-tight uppercase">Works</span>
			</Link>

			<!-- 5. Message -->
			<Link href="/pagi/messages" class="flex flex-col items-center gap-1 transition-colors"
				:class="[ $page.url.startsWith('/pagi/messages') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-500 hover:text-slate-900 dark:text-zinc-450 dark:hover:text-zinc-200' ]">
				<div class="relative">
					<MessageSquare class="w-5 h-5 transition-transform active:scale-90" />
					<span v-if="unreadMessagesCount > 0" class="absolute -top-1.5 -right-1.5 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-indigo-600 px-1 text-[8px] font-black text-white ring-2 ring-white dark:ring-zinc-950">
						{{ unreadMessagesCount }}
					</span>
				</div>
				<span class="text-[9px] font-extrabold tracking-tight uppercase">Pesan</span>
			</Link>
		</div>
	</div>

	<!-- ===== NOTIFICATION SLIDE-OVER PANEL ===== -->
	<!-- Backdrop -->
	<Transition
		enter-active-class="transition ease-out duration-300"
		enter-from-class="opacity-0"
		enter-to-class="opacity-100"
		leave-active-class="transition ease-in duration-200"
		leave-from-class="opacity-100"
		leave-to-class="opacity-0"
	>
		<div v-if="isNotifPanelOpen" @click="isNotifPanelOpen = false"
			class="fixed inset-0 bg-black/30 backdrop-blur-[2px] z-[998]">
		</div>
	</Transition>

	<!-- Slide Panel -->
	<Transition
		enter-active-class="transition ease-out duration-300"
		enter-from-class="translate-x-full opacity-0"
		enter-to-class="translate-x-0 opacity-100"
		leave-active-class="transition ease-in duration-200"
		leave-from-class="translate-x-0 opacity-100"
		leave-to-class="translate-x-full opacity-0"
	>
		<div v-if="isNotifPanelOpen"
			class="fixed top-0 right-0 h-full w-full sm:w-[400px] bg-white dark:bg-zinc-950 shadow-2xl z-[999] flex flex-col"
		>
			<!-- Panel Header -->
			<div class="flex items-center justify-between px-5 pt-5 pb-3 border-b border-slate-100 dark:border-zinc-800 shrink-0">
				<div class="flex items-center gap-2.5">
					<h2 class="text-lg font-black text-slate-900 dark:text-zinc-100 tracking-tight">Notifikasi</h2>
					<span v-if="totalUnread > 0" class="inline-flex items-center px-2 py-0.5 rounded-full bg-red-500 text-white text-[10px] font-black">{{ totalUnread }}</span>
				</div>
				<button v-if="totalUnread > 0" @click="markAllNotifsAsRead" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">Tandai semua dibaca</button>
				<button @click="isNotifPanelOpen = false" class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-500 dark:text-zinc-400 transition-colors">
					<X class="h-5 w-5" />
				</button>
			</div>

			<!-- Category Tabs -->
			<div class="flex items-center gap-2 px-4 py-3 overflow-x-auto shrink-0" style="scrollbar-width:none;">
				<button
					v-for="tab in notifTabs" :key="tab.key"
					@click="notifActiveTab = tab.key"
					:class="[
						'shrink-0 px-3.5 py-1.5 rounded-full text-xs font-bold transition-all',
						notifActiveTab === tab.key
							? 'bg-slate-900 dark:bg-zinc-100 text-white dark:text-zinc-950 shadow-sm'
							: 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-300 hover:bg-slate-200 dark:hover:bg-zinc-700'
					]"
				>
					{{ tab.label }}
				</button>
			</div>

			<!-- Notification List (scrollable) -->
			<div class="flex-1 overflow-y-auto px-3 pb-8" style="scrollbar-width:thin;">
				<div v-if="filteredNotifGroups.length === 0" class="flex flex-col items-center justify-center py-16 text-slate-400 dark:text-zinc-500 gap-3">
					<Bell class="h-10 w-10 opacity-30" />
					<p class="text-sm font-semibold">Tidak ada notifikasi</p>
				</div>

				<div v-for="group in filteredNotifGroups" :key="group.group" class="mt-4">
					<!-- Group Label -->
					<h3 class="text-sm font-black text-slate-800 dark:text-zinc-200 px-2 mb-2">{{ group.group }}</h3>

					<!-- Items -->
					<div v-for="notif in group.items" :key="notif.id"
						@click="handleNotifClick(notif)"
						:class="['flex items-start gap-3 px-3 py-3 rounded-2xl transition-colors cursor-pointer hover:border-slate-200 dark:hover:border-zinc-800', notif.unread ? 'bg-indigo-50/60 dark:bg-indigo-950/20' : 'hover:bg-slate-50 dark:hover:bg-zinc-900']"
					>
						<!-- Avatar -->
						<div class="relative shrink-0">
							<img v-if="notif.avatar" :src="notif.avatar" class="w-11 h-11 rounded-full object-cover border-2 border-white dark:border-zinc-900 shadow-sm" />
							<div v-else class="w-11 h-11 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center shadow-sm">
								<Bell class="w-5 h-5 text-white" />
							</div>
							<span v-if="notif.unread" class="absolute -top-0.5 -right-0.5 w-3 h-3 bg-red-500 rounded-full border-2 border-white dark:border-zinc-950"></span>
						</div>

						<!-- Text -->
						<div class="flex-1 min-w-0">
							<p class="text-sm text-slate-800 dark:text-zinc-200 leading-snug">
								<span class="font-bold text-slate-900 dark:text-white">{{ notif.title }}</span>
								{{ ' ' + notif.message }}
							</p>
							<div class="flex items-center gap-3 mt-1.5 flex-wrap">
								<span class="text-[11px] text-slate-400 dark:text-zinc-555 font-medium block">{{ notif.time }}</span>

								<!-- Follback Button -->
								<button
									v-if="notif.type === 'follow' && notif.sender_id"
									@click.stop="toggleFollback(notif)"
									:disabled="FollbackInProgress[notif.sender_id]"
									class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[9px] sm:text-[10px] font-black uppercase tracking-wider rounded-lg transition-all active:scale-97 cursor-pointer"
									:class="isFollowingBack(notif.sender_id)
										? 'bg-slate-100 dark:bg-zinc-800 text-slate-500 dark:text-zinc-400 hover:bg-slate-200 dark:hover:bg-zinc-700'
										: 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-xs'"
								>
									{{ FollbackInProgress[notif.sender_id] ? '...' : (isFollowingBack(notif.sender_id) ? 'Mengikuti' : 'Follback') }}
								</button>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>
	</Transition>

	<!-- FLOATING COMPLETENESS PROGRESS CARD (DESKTOP) -->
	<div 
		v-if="user && user.user_type === 'mahasiswa' && completenessPercentage < 100 && showCompleteness"
		class="hidden md:block fixed bottom-0 right-6 z-40 bg-white dark:bg-zinc-900 border-t border-x border-slate-200/80 dark:border-zinc-800 shadow-[0_-4px_25px_rgba(0,0,0,0.06)] rounded-t-[20px] transition-all duration-300 overflow-hidden w-64 select-none"
		:class="isCompletenessExpanded ? 'h-auto max-h-[400px]' : 'h-14'"
	>
		<!-- Card Header (Toggles expanded state) -->
		<button 
			@click="isCompletenessExpanded = !isCompletenessExpanded"
			class="w-full h-14 px-4 flex items-center justify-between text-left outline-hidden cursor-pointer border-none bg-transparent"
		>
			<div class="flex items-center gap-3">
				<!-- Progress Ring -->
				<div class="relative w-8 h-8 flex items-center justify-center shrink-0">
					<svg class="w-full h-full transform -rotate-90">
						<circle cx="16" cy="16" r="13" stroke="currentColor" class="text-slate-100 dark:text-zinc-800" stroke-width="2.5" fill="transparent" />
						<circle cx="16" cy="16" r="13" stroke="currentColor" class="text-slate-900 dark:text-white" stroke-width="2.5" fill="transparent"
							:stroke-dasharray="2 * Math.PI * 13"
							:stroke-dashoffset="2 * Math.PI * 13 * (1 - completenessPercentage / 100)" />
					</svg>
					<span class="absolute text-[8px] font-bold text-slate-900 dark:text-white">{{ completenessPercentage }}%</span>
				</div>
				<span class="text-xs font-semibold text-slate-800 dark:text-slate-200">Complete profile</span>
			</div>

			<ChevronUp v-if="!isCompletenessExpanded" class="w-4 h-4 text-slate-400" />
			<ChevronDown v-else class="w-4 h-4 text-slate-400" />
		</button>

		<!-- Checklist details (Shows when expanded) -->
		<div class="px-4 pb-5 border-t border-slate-100 dark:border-zinc-800 pt-4 space-y-3.5 bg-slate-50/50 dark:bg-zinc-950/20">
			<p class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider">Completeness Checklist</p>
			<div class="space-y-2.5">
				<div 
					v-for="item in completenessItems" 
					:key="item.name" 
					class="flex items-center justify-between text-xs"
				>
					<span class="flex items-center gap-2" :class="item.completed ? 'text-slate-500 dark:text-slate-400 line-through' : 'text-slate-800 dark:text-slate-200 font-bold'">
						<CheckCircle v-if="item.completed" class="w-4 h-4 text-emerald-500 shrink-0" />
						<Circle v-else class="w-4 h-4 text-slate-400 dark:text-zinc-550 shrink-0" />
						<span>{{ item.name }}</span>
					</span>
					<span class="text-[10px] text-slate-400 font-extrabold">+{{ item.weight }}%</span>
				</div>
			</div>

			<!-- Quick action -->
			<button 
				@click="handleCompleteSettingsClick"
				class="w-full flex items-center justify-center gap-1 py-2 mt-4 bg-slate-900 dark:bg-white text-white dark:text-slate-950 font-black text-xs rounded-xl shadow-xs hover:bg-slate-800 dark:hover:bg-slate-100 cursor-pointer border-none"
			>
				<span>Complete Settings</span>
				<ChevronRight class="w-3.5 h-3.5" />
			</button>
		</div>
	</div>

	<!-- FLOATING COMPLETENESS PROGRESS CARD (MOBILE) -->
	<div 
		v-if="user && user.user_type === 'mahasiswa' && completenessPercentage < 100 && showCompleteness"
		class="md:hidden fixed bottom-20 left-1/2 -translate-x-1/2 z-40 bg-white/95 dark:bg-zinc-900/95 border border-slate-200/80 dark:border-zinc-800 shadow-xl transition-all duration-300 select-none backdrop-blur-xs flex items-center justify-between"
		:class="isCompletenessExpanded ? 'w-[90vw] rounded-2xl flex-col p-4 gap-4' : 'rounded-full px-4 py-2 w-auto gap-3.5 h-11'"
	>
		<!-- If closed, show as a simple pill with close button -->
		<template v-if="!isCompletenessExpanded">
			<div @click="isCompletenessExpanded = true" class="flex items-center gap-2.5 cursor-pointer">
				<!-- Progress Ring -->
				<div class="relative w-7 h-7 flex items-center justify-center shrink-0">
					<svg class="w-full h-full transform -rotate-90">
						<circle cx="14" cy="14" r="11" stroke="currentColor" class="text-slate-100 dark:text-zinc-800" stroke-width="2.2" fill="transparent" />
						<circle cx="14" cy="14" r="11" stroke="currentColor" class="text-slate-900 dark:text-white" stroke-width="2.2" fill="transparent"
							:stroke-dasharray="2 * Math.PI * 11"
							:stroke-dashoffset="2 * Math.PI * 11 * (1 - completenessPercentage / 100)" />
					</svg>
					<span class="absolute text-[8px] font-black text-slate-900 dark:text-white">{{ completenessPercentage }}%</span>
				</div>
				<span class="text-[11px] font-bold text-slate-800 dark:text-slate-200">Complete profile</span>
			</div>
			<button @click.stop="showCompleteness = false" class="text-slate-450 hover:text-slate-600 p-0.5 bg-transparent border-none cursor-pointer flex items-center justify-center">
				<X class="w-3.5 h-3.5" />
			</button>
		</template>

		<!-- If expanded, show the full checklist card -->
		<template v-else>
			<div class="w-full flex items-center justify-between pb-2.5 border-b border-slate-100 dark:border-zinc-800">
				<div class="flex items-center gap-2.5">
					<!-- Progress Ring -->
					<div class="relative w-7 h-7 flex items-center justify-center shrink-0">
						<svg class="w-full h-full transform -rotate-90">
							<circle cx="14" cy="14" r="11" stroke="currentColor" class="text-slate-100 dark:text-zinc-800" stroke-width="2.2" fill="transparent" />
							<circle cx="14" cy="14" r="11" stroke="currentColor" class="text-slate-900 dark:text-white" stroke-width="2.2" fill="transparent"
								:stroke-dasharray="2 * Math.PI * 11"
								:stroke-dashoffset="2 * Math.PI * 11 * (1 - completenessPercentage / 100)" />
						</svg>
						<span class="absolute text-[8px] font-black text-slate-900 dark:text-white">{{ completenessPercentage }}%</span>
					</div>
					<span class="text-[11px] font-bold text-slate-800 dark:text-slate-200">Complete profile</span>
				</div>
				<div class="flex items-center gap-2">
					<button @click="isCompletenessExpanded = false" class="text-xs text-slate-400 hover:text-slate-600 font-bold bg-transparent border-none cursor-pointer">Minimize</button>
					<button @click="showCompleteness = false" class="text-slate-450 hover:text-red-500 p-0.5 bg-transparent border-none cursor-pointer flex items-center justify-center">
						<X class="w-4 h-4" />
					</button>
				</div>
			</div>

			<!-- Checklist details -->
			<div class="w-full space-y-2.5">
				<div 
					v-for="item in completenessItems" 
					:key="item.name" 
					class="flex items-center justify-between text-xs"
				>
					<span class="flex items-center gap-2" :class="item.completed ? 'text-slate-500 dark:text-slate-400 line-through' : 'text-slate-800 dark:text-slate-200 font-bold'">
						<CheckCircle v-if="item.completed" class="w-4 h-4 text-emerald-500 shrink-0" />
						<Circle v-else class="w-4 h-4 text-slate-400 dark:text-zinc-550 shrink-0" />
						<span>{{ item.name }}</span>
					</span>
					<span class="text-[10px] text-slate-400 font-extrabold">+{{ item.weight }}%</span>
				</div>
			</div>

			<!-- Quick action -->
			<button 
				@click="handleCompleteSettingsClick"
				class="w-full flex items-center justify-center gap-1 py-2 bg-slate-900 dark:bg-white text-white dark:text-slate-950 font-black text-xs rounded-xl shadow-xs hover:bg-slate-800 dark:hover:bg-slate-100 cursor-pointer border-none"
			>
				<span>Complete Settings</span>
				<ChevronRight class="w-3.5 h-3.5" />
			</button>
		</template>
	</div>

	<!-- ADMIN ACTION MODAL -->
	<AdminActionModal 
		v-if="showAdminModal" 
		:show="showAdminModal" 
		:notification="activeAdminNotification" 
		@close="showAdminModal = false" 
	/>
</template>
