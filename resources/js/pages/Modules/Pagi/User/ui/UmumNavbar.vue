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
	Settings,
	TrendingUp,
	User as UserIcon,
	Users,
	X,
} from "lucide-vue-next";
import {
	computed,
	defineAsyncComponent,
	nextTick,
	onMounted,
	onUnmounted,
	ref,
	watch,
} from "vue";

const PagiProgressOverlay = defineAsyncComponent(
	() => import("./PagiProgressOverlay.vue"),
);

import { useAppearance } from "@/composables/useAppearance";
import { ThemeTogglerButton } from "@/components/animate-ui/components/buttons/theme-toggler";

const { appearance, resolvedAppearance, updateAppearance } = useAppearance();

const activeTheme = computed({
	get: () => appearance.value === "system" ? resolvedAppearance.value : appearance.value,
	set: (val) => {
		updateAppearance(val);
	}
});

const props = defineProps<{
	roleName?: string;
}>();

const page = usePage();
const siteSettings = computed(() => (page.props as any).siteSettings || {});

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

const AdminActionModal = defineAsyncComponent(
	() => import("./AdminActionModal.vue"),
);

const activeAdminNotification = ref<any>(null);
const showAdminModal = ref(false);

const computedRoleName = computed(() => {
	const r =
		props.roleName ||
		(page.props as any).context?.active_role ||
		(page.props.roleName as string) ||
		"Mitra";
	if (r.toLowerCase() === "mahasiswa") return "Mahasiswa";
	if (r.toLowerCase() === "super-admin") return "Super Admin";
	return r.charAt(0).toUpperCase() + r.slice(1);
});

const currentRoleSlug = computed(() => {
	const r =
		props.roleName ||
		(page.props as any).context?.active_role ||
		(page.props.roleName as string) ||
		"mitra";
	return r.toLowerCase();
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

// Interactive Realtime Hover State Management
const isNotifOpen = ref(false);
const isMsgOpen = ref(false);
const isProfileModalOpen = ref(false);
const isNotifPanelOpen = ref(false);
const notifActiveTab = ref("all");

const notifTabs = [
	{ key: "all", label: "Semua" },
	{ key: "system", label: "Sistem" },
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
	if (isProfileModalOpen.value) {
		isProfileModalOpen.value = false;
	}
};

// ── Subscribe to user-specific channel for real-time updates ──────────────────
function subscribeUserChannel(userId: number | string) {
	if (!userId || !window.Broadcaster) return;
	const channelName = `App.Models.User.${userId}`;
	try {
		window.Broadcaster.leave(channelName);
	} catch (_) {}
	userChannel = window.Broadcaster.private(channelName)
		.listen(".unread.count.updated", (data: any) => {
			unreadMessagesCount.value = Number(data.unread_messages_count);
		})
		.listen(".pagi.notification", (data: any) => {
			pushRealtimeNotif(data);
		})
		.notification((data: any) => {
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

	let attempts = 0;
	const trySubscribe = () => {
		if (window.Broadcaster) {
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
		if (!window.Broadcaster) return;
		if (oldUserId) {
			try {
				window.Broadcaster.leave(`App.Models.User.${oldUserId}`);
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
	if (uid && window.Broadcaster) {
		try {
			window.Broadcaster.leave(`App.Models.User.${uid}`);
		} catch (_) {}
	}
});
</script>

<template>
	<div class="sticky top-0 z-50 select-none">
		<header class="border-b border-slate-200/80 dark:border-zinc-800 bg-white/95 dark:bg-zinc-950/95 backdrop-blur-md" :class="{ 'hidden md:block': !$page.props.auth?.user }">
			<div class="mx-auto flex h-14 max-w-[1400px] items-center justify-between px-4 gap-4 lg:px-6 w-full">
				<!-- Left: Logo + Module Name -->
				<div class="flex items-center gap-4 shrink-0">
					<Link href="/pagi" class="hidden md:flex items-center gap-2.5 shrink-0 group">
						<div 
							class="h-8 w-8 rounded-xl flex items-center justify-center overflow-hidden transition-all duration-300"
							:class="siteSettings.brand_logo ? 'bg-transparent border border-slate-200 dark:border-zinc-800' : 'bg-gradient-to-br from-indigo-650 to-purple-650 shadow-md group-hover:shadow-indigo-200 dark:group-hover:shadow-indigo-900'"
						>
							<img v-if="siteSettings.brand_logo" :src="siteSettings.brand_logo" class="h-full w-full object-contain" alt="Logo" />
							<span v-else class="text-white text-xs font-black tracking-tight">P</span>
						</div>
						<div class="flex flex-col">
							<span class="text-sm font-black text-slate-900 dark:text-zinc-100 leading-none tracking-tight group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">PAGI</span>
							<span class="text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-widest leading-none mt-0.5">FMIKOM UNUGHA</span>
						</div>
					</Link>

					<!-- Divider -->
					<div v-if="$page.props.auth?.user" class="hidden md:block h-6 w-px bg-slate-200 dark:bg-zinc-800"></div>

					<!-- Profile Info Group (MOBILE ONLY) -->
					<div v-if="$page.props.auth?.user" class="md:hidden relative shrink-0" @click.stop="isProfileModalOpen = !isProfileModalOpen">
						<div class="flex items-center gap-2.5 cursor-pointer hover:opacity-90 transition-all duration-300 group">
							<div class="h-9 w-9 rounded-xl border border-slate-200 dark:border-zinc-800 bg-slate-100 dark:bg-slate-800 overflow-hidden flex items-center justify-center shrink-0 ring-offset-white dark:ring-offset-zinc-950 group-hover:ring-2 ring-indigo-500/50 transition-all duration-300 shadow-xs">
								<img v-if="user.foto_path" :src="'/storage/' + user.foto_path" :alt="user.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-355" />
								<img v-else-if="user.avatar" :src="user.avatar" :alt="user.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-355" />
								<span v-else class="text-slate-700 dark:text-slate-200 text-xs font-black">{{ user.name.charAt(0) }}</span>
							</div>
							<div class="flex flex-col text-left">
								<span class="text-xs font-black text-slate-800 dark:text-zinc-200 leading-none tracking-tight group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors uppercase">{{ user.name }}</span>
								<span class="text-[9px] font-extrabold text-slate-500 dark:text-zinc-400 uppercase tracking-widest mt-0.5">{{ computedRoleName }}</span>
							</div>
							<ChevronDown class="h-3 w-3 text-slate-400 ml-0.5 shrink-0 group-hover:text-slate-600 transition-colors" />
						</div>

						<!-- Dropdown Card Mobile -->
						<Transition
							enter-active-class="transition ease-out duration-250"
							enter-from-class="opacity-0 translate-y-2 scale-95"
							enter-to-class="opacity-100 translate-y-0 scale-100"
							leave-active-class="transition ease-in duration-200"
							leave-from-class="opacity-100 translate-y-0 scale-100"
							leave-to-class="opacity-0 translate-y-2 scale-95"
						>
							<div v-show="isProfileModalOpen" class="absolute left-0 mt-2.5 w-64 rounded-2xl border border-slate-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-950 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.12)] select-none z-50 overflow-hidden">
								<div class="py-1">
									<!-- Profile Header -->
									<div class="flex items-center gap-3 px-4 py-3 border-b border-slate-100 dark:border-zinc-800">
										<div class="h-10 w-10 rounded-full border border-slate-200 dark:border-zinc-800 bg-slate-100 dark:bg-slate-800 overflow-hidden flex items-center justify-center shrink-0">
											<img v-if="user.foto_path" :src="'/storage/' + user.foto_path" :alt="user.name" class="w-full h-full object-cover" />
											<img v-else-if="user.avatar" :src="user.avatar" :alt="user.name" class="w-full h-full object-cover" />
											<span v-else class="text-slate-700 dark:text-slate-200 text-xs font-black">{{ user.name.charAt(0) }}</span>
										</div>
										<div class="flex flex-col text-left min-w-0">
											<span class="text-xs font-bold text-slate-800 dark:text-zinc-200 truncate uppercase tracking-wide leading-tight">{{ user.name }}</span>
											<span class="text-[10px] font-medium text-slate-400 dark:text-zinc-500 truncate leading-none mt-0.5">{{ user.email }}</span>
										</div>
									</div>

									<!-- Profile Link -->
									<Link href="/pagi/profile" class="w-full flex items-center gap-3 px-4 py-3 text-xs font-bold text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800/60 transition-colors cursor-pointer border-b border-slate-100 dark:border-zinc-800 bg-transparent text-left decoration-none">
										<UserIcon class="h-4 w-4 text-slate-455 dark:text-zinc-500 shrink-0" />
										<span>Lihat Profil PAGI</span>
									</Link>

									<!-- Settings Link -->
									<Link href="/pagi/settings" class="w-full flex items-center gap-3 px-4 py-3 text-xs font-bold text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800/60 transition-colors cursor-pointer border-b border-slate-100 dark:border-zinc-800 bg-transparent text-left decoration-none">
										<Settings class="h-4 w-4 text-slate-455 dark:text-zinc-500 shrink-0" />
										<span>Settings</span>
									</Link>

									<!-- Kembali ke Portal Link -->
									<Link href="/dashboard" class="w-full flex items-center gap-3 px-4 py-3 text-xs font-bold text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800/60 transition-colors cursor-pointer border-b border-slate-100 dark:border-zinc-800 bg-transparent text-left decoration-none">
										<ExternalLink class="h-4 w-4 text-slate-455 dark:text-zinc-500 shrink-0" />
										<span>Kembali ke Portal Utama</span>
									</Link>

									<!-- Log out Button -->
									<button @click="handleLogout" class="w-full flex items-center gap-3 px-4 py-3 text-xs font-bold text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800/60 transition-colors cursor-pointer bg-transparent text-left border-none">
										<LogOut class="h-4 w-4 text-slate-455 dark:text-zinc-500 shrink-0" />
										<span>Log out</span>
									</button>
								</div>
							</div>
						</Transition>
					</div>

					<!-- Base Navigation (Desktop only) -->
					<nav v-if="$page.props.auth?.user" class="hidden md:flex items-center gap-0.5 relative">
						<Link href="/pagi" class="px-3 py-1.5 text-sm font-semibold transition-colors" :class="[ $page.url === '/pagi' ? 'text-slate-900 dark:text-white active-nav-btn' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100' ]">
							Explore
						</Link>

						<Link href="/pagi/gallery" class="px-3 py-1.5 text-sm font-semibold transition-colors" :class="[ $page.url.startsWith('/pagi/gallery') ? 'text-slate-900 dark:text-white active-nav-btn' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100' ]">
							Gallery
						</Link>

						<Link href="/pagi/people" class="px-3 py-1.5 text-sm font-semibold transition-colors" :class="[ $page.url.startsWith('/pagi/people') ? 'text-slate-900 dark:text-white active-nav-btn' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100' ]">
							People
						</Link>

						<Link v-if="currentRoleSlug === 'alumni'" href="/pagi/cv" class="px-3 py-1.5 text-sm font-semibold transition-colors" :class="[ $page.url.startsWith('/pagi/cv') ? 'text-slate-900 dark:text-white active-nav-btn' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100' ]">
							CV
						</Link>
					</nav>
				</div>

				<!-- Right Actions Area -->
				<div class="flex items-center gap-3 ml-auto">
					<!-- Message Icon -->
					<Link v-if="$page.props.auth?.user" href="/pagi/messages" class="hidden md:flex relative p-2 rounded-xl border border-slate-200 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-900 text-slate-600 dark:text-zinc-350 transition-colors items-center justify-center" aria-label="Pesan">
						<MessageSquare class="h-4.5 w-4.5" />
						<span v-if="unreadMessagesCount > 0" class="absolute -top-1 -right-1 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-indigo-650 px-1 text-[9px] font-black text-white ring-2 ring-white dark:ring-zinc-950">
							{{ unreadMessagesCount }}
						</span>
					</Link>

					<!-- Notification Bell -->
					<button v-if="$page.props.auth?.user" @click="toggleNotifPanel" class="relative p-2 rounded-xl border border-slate-200 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-900 text-slate-600 dark:text-zinc-355 transition-colors" aria-label="Notifikasi">
						<Bell class="h-4.5 w-4.5" />
						<span v-if="totalUnread > 0" class="absolute -top-1 -right-1 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-red-500 px-1 text-[9px] font-black text-white ring-2 ring-white dark:ring-zinc-950">
							{{ totalUnread }}
						</span>
					</button>

					<!-- Theme Toggler -->
					<div class="flex items-center shrink-0">
						<ThemeTogglerButton
							v-model="activeTheme"
							variant="ghost"
							size="sm"
							direction="ltr"
							:modes="['light', 'dark']"
						/>
					</div>

					<!-- Profile Card Dropdown Desktop -->
					<div v-if="$page.props.auth?.user" class="hidden md:block relative shrink-0" @click.stop="isProfileModalOpen = !isProfileModalOpen">
						<div class="flex items-center gap-2.5 cursor-pointer hover:opacity-90 transition-all duration-300 group">
							<div class="h-9 w-9 rounded-xl border border-slate-200 dark:border-zinc-800 bg-slate-100 dark:bg-slate-800 overflow-hidden flex items-center justify-center shrink-0 ring-offset-white dark:ring-offset-zinc-900 group-hover:ring-2 ring-indigo-500/50 transition-all duration-300 shadow-xs">
								<img v-if="user.foto_path" :src="'/storage/' + user.foto_path" :alt="user.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-355" />
								<img v-else-if="user.avatar" :src="user.avatar" :alt="user.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-355" />
								<span v-else class="text-slate-700 dark:text-slate-200 text-xs font-black">{{ user.name.charAt(0) }}</span>
							</div>
							<div class="flex flex-col text-left">
								<span class="text-xs font-black text-slate-800 dark:text-zinc-200 leading-none tracking-tight group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors uppercase">{{ user.name }}</span>
								<span class="text-[9px] font-extrabold text-slate-500 dark:text-zinc-400 uppercase tracking-widest mt-0.5">{{ computedRoleName }}</span>
							</div>
							<ChevronDown class="h-3 w-3 text-slate-400 ml-0.5 shrink-0 group-hover:text-slate-600 transition-colors" />
						</div>

						<Transition
							enter-active-class="transition ease-out duration-250"
							enter-from-class="opacity-0 translate-y-2 scale-95"
							enter-to-class="opacity-100 translate-y-0 scale-100"
							leave-active-class="transition ease-in duration-200"
							leave-from-class="opacity-100 translate-y-0 scale-100"
							leave-to-class="opacity-0 translate-y-2 scale-95"
						>
							<div v-show="isProfileModalOpen" class="absolute right-0 mt-2.5 w-64 rounded-2xl border border-slate-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-950 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.12)] select-none z-50 overflow-hidden">
								<div class="py-1">
									<!-- Profile Header -->
									<div class="flex items-center gap-3 px-4 py-3 border-b border-slate-100 dark:border-zinc-800">
										<div class="h-10 w-10 rounded-full border border-slate-200 dark:border-zinc-800 bg-slate-100 dark:bg-slate-800 overflow-hidden flex items-center justify-center shrink-0">
											<img v-if="user.foto_path" :src="'/storage/' + user.foto_path" :alt="user.name" class="w-full h-full object-cover" />
											<img v-else-if="user.avatar" :src="user.avatar" :alt="user.name" class="w-full h-full object-cover" />
											<span v-else class="text-slate-700 dark:text-slate-200 text-xs font-black">{{ user.name.charAt(0) }}</span>
										</div>
										<div class="flex flex-col text-left min-w-0">
											<span class="text-xs font-bold text-slate-800 dark:text-zinc-200 truncate uppercase tracking-wide leading-tight">{{ user.name }}</span>
											<span class="text-[10px] font-medium text-slate-400 dark:text-zinc-555 truncate leading-none mt-0.5">{{ user.email }}</span>
										</div>
									</div>

									<!-- Profile Link -->
									<Link href="/pagi/profile" class="w-full flex items-center gap-3 px-4 py-3 text-xs font-bold text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800/60 transition-colors cursor-pointer border-b border-slate-100 dark:border-zinc-800 bg-transparent text-left decoration-none">
										<UserIcon class="h-4 w-4 text-slate-455 dark:text-zinc-500 shrink-0" />
										<span>Lihat Profil PAGI</span>
									</Link>

									<!-- Settings Link -->
									<Link href="/pagi/settings" class="w-full flex items-center gap-3 px-4 py-3 text-xs font-bold text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800/60 transition-colors cursor-pointer border-b border-slate-100 dark:border-zinc-800 bg-transparent text-left decoration-none">
										<Settings class="h-4 w-4 text-slate-455 dark:text-zinc-500 shrink-0" />
										<span>Settings</span>
									</Link>

									<!-- Kembali ke Portal Link -->
									<Link href="/dashboard" class="w-full flex items-center gap-3 px-4 py-3 text-xs font-bold text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800/60 transition-colors cursor-pointer border-b border-slate-100 dark:border-zinc-800 bg-transparent text-left decoration-none">
										<ExternalLink class="h-4 w-4 text-slate-455 dark:text-zinc-500 shrink-0" />
										<span>Kembali ke Portal Utama</span>
									</Link>

									<!-- Log out Button -->
									<button @click="handleLogout" class="w-full flex items-center gap-3 px-4 py-3 text-xs font-bold text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800/60 transition-colors cursor-pointer bg-transparent text-left border-none">
										<LogOut class="h-4 w-4 text-slate-455 dark:text-zinc-500 shrink-0" />
										<span>Log out</span>
									</button>
								</div>
							</div>
						</Transition>
					</div>
				</div>
			</div>
		</header>

		<!-- Bottom Navbar Mobile -->
		<div v-if="$page.props.auth?.user && !$page.url.startsWith('/pagi/messages')" class="fixed bottom-0 inset-x-0 h-16 bg-white/85 dark:bg-zinc-950/85 backdrop-blur-xl border-t border-slate-200/80 dark:border-zinc-850 flex items-center justify-between px-2 z-50 md:hidden shadow-[0_-4px_24px_rgba(0,0,0,0.04)] select-none" style="padding-bottom: env(safe-area-inset-bottom, 0px);">
			<Link href="/pagi" class="flex flex-col items-center justify-center gap-1 transition-colors flex-1" :class="[ $page.url === '/pagi' ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-500 hover:text-slate-900 dark:text-zinc-450 dark:hover:text-zinc-200' ]">
				<LayoutGrid class="w-5 h-5 transition-transform active:scale-90" />
				<span class="text-[9px] font-extrabold tracking-tight uppercase">Explore</span>
			</Link>

			<Link href="/pagi/gallery" class="flex flex-col items-center justify-center gap-1 transition-colors flex-1" :class="[ $page.url.startsWith('/pagi/gallery') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-500 hover:text-slate-900 dark:text-zinc-450 dark:hover:text-zinc-200' ]">
				<Image class="w-5 h-5 transition-transform active:scale-90" />
				<span class="text-[9px] font-extrabold tracking-tight uppercase">Gallery</span>
			</Link>

			<Link href="/pagi/people" class="flex flex-col items-center justify-center gap-1 transition-colors flex-1" :class="[ $page.url.startsWith('/pagi/people') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-500 hover:text-slate-900 dark:text-zinc-450 dark:hover:text-zinc-200' ]">
				<Users class="w-5 h-5 transition-transform active:scale-90" />
				<span class="text-[9px] font-extrabold tracking-tight uppercase">People</span>
			</Link>

			<Link v-if="currentRoleSlug === 'alumni'" href="/pagi/cv" class="flex flex-col items-center justify-center gap-1 transition-colors flex-1" :class="[ $page.url.startsWith('/pagi/cv') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-500 hover:text-slate-900 dark:text-zinc-450 dark:hover:text-zinc-200' ]">
				<FileText class="w-5 h-5 transition-transform active:scale-90" />
				<span class="text-[9px] font-extrabold tracking-tight uppercase">CV</span>
			</Link>
			<!-- Fallback placeholder if not alumni to keep centering layout -->
			<div v-else class="flex-1"></div>

			<Link href="/pagi/messages" class="flex flex-col items-center justify-center gap-1 transition-colors flex-1" :class="[ $page.url.startsWith('/pagi/messages') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-500 hover:text-slate-900 dark:text-zinc-450 dark:hover:text-zinc-200' ]">
				<div class="relative flex flex-col items-center justify-center">
					<MessageSquare class="w-5 h-5 transition-transform active:scale-90" />
					<span v-if="unreadMessagesCount > 0" class="absolute -top-1.5 -right-1.5 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-indigo-600 px-1 text-[8px] font-black text-white ring-2 ring-white dark:ring-zinc-950">
						{{ unreadMessagesCount }}
					</span>
				</div>
				<span class="text-[9px] font-extrabold tracking-tight uppercase">Pesan</span>
			</Link>
		</div>
	</div>

	<!-- ===== NOTIFICATION PANEL ===== -->
	<Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
		<div v-if="isNotifPanelOpen" @click="isNotifPanelOpen = false" class="fixed inset-0 bg-black/30 backdrop-blur-[2px] z-[998]"></div>
	</Transition>

	<Transition enter-active-class="transition ease-out duration-300" enter-from-class="translate-x-full opacity-0" enter-to-class="translate-x-0 opacity-100" leave-active-class="transition ease-in duration-200" leave-from-class="translate-x-0 opacity-100" leave-to-class="translate-x-full opacity-0">
		<div v-if="isNotifPanelOpen" class="fixed top-0 right-0 h-full w-full sm:w-[400px] bg-white dark:bg-zinc-950 shadow-2xl z-[999] flex flex-col">
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

			<div class="flex-1 overflow-y-auto px-3 pb-8 mt-2" style="scrollbar-width:thin;">
				<div v-if="filteredNotifGroups.length === 0" class="flex flex-col items-center justify-center py-16 text-slate-400 dark:text-zinc-555 gap-3">
					<Bell class="h-10 w-10 opacity-30" />
					<p class="text-sm font-semibold">Tidak ada notifikasi</p>
				</div>

				<div v-for="group in filteredNotifGroups" :key="group.group" class="mt-4">
					<h3 class="text-sm font-black text-slate-800 dark:text-zinc-200 px-2 mb-2">{{ group.group }}</h3>

					<div v-for="notif in group.items" :key="notif.id" @click="handleNotifClick(notif)" class="flex items-start gap-3 px-3 py-3 rounded-2xl transition-colors cursor-pointer hover:bg-slate-50 dark:hover:bg-zinc-900">
						<div class="relative shrink-0">
							<img v-if="notif.avatar" :src="notif.avatar" alt="Avatar" class="w-11 h-11 rounded-full object-cover border-2 border-white dark:border-zinc-900 shadow-sm" />
							<div v-else class="w-11 h-11 rounded-full bg-gradient-to-br from-indigo-450 to-purple-550 flex items-center justify-center shadow-sm">
								<Bell class="w-5 h-5 text-white" />
							</div>
							<span v-if="notif.unread" class="absolute -top-0.5 -right-0.5 w-3 h-3 bg-red-500 rounded-full border-2 border-white dark:border-zinc-950"></span>
						</div>

						<div class="flex-1 min-w-0">
							<p class="text-sm text-slate-800 dark:text-zinc-200 leading-snug">
								<span class="font-bold text-slate-900 dark:text-white">{{ notif.title }}</span>
								{{ ' ' + notif.message }}
							</p>
							<span class="text-[11px] text-slate-400 dark:text-zinc-555 font-medium block mt-1.5">{{ notif.time }}</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</Transition>

	<!-- ADMIN ACTION MODAL -->
	<AdminActionModal v-if="showAdminModal" :show="showAdminModal" :notification="activeAdminNotification" @close="showAdminModal = false" />

	<!-- Global Progress System Overlay -->
	<PagiProgressOverlay />
</template>

<style scoped>
.active-nav-btn {
	position: relative;
}
.active-nav-btn::after {
	content: "";
	position: absolute;
	bottom: -6px;
	left: 12px;
	right: 12px;
	height: 2px;
	background-color: currentColor;
	border-radius: 9999px;
}
</style>
