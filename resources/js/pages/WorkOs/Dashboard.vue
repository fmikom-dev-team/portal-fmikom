<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3";
import { computed, defineAsyncComponent, onMounted, ref, watch } from "vue";
import DashboardLayout from "./layouts/DashboardLayout.vue";
import AuditLogsEvents from "./partials/AuditLogs/Events.vue";
import AuditLogsIndex from "./partials/AuditLogs/Index.vue";
import AuditLogsSecurity from "./partials/AuditLogs/SecurityLogs.vue";
import Authentication from "./partials/Authentication/Index.vue";
import Authorization from "./partials/Authorization/Index.vue";
const Overview = defineAsyncComponent(() => import("./partials/Dashboard/Overview.vue"));
import EmailsIndex from "./partials/Emails/Index.vue";
import NotificationsIndex from "./partials/Notifications/Index.vue";
import Organizations from "./partials/Organizations/Index.vue";
import OrganizationDetails from "./partials/Organizations/Show.vue";
import Placeholder from "./partials/Placeholder.vue";
const Radar = defineAsyncComponent(() => import("./partials/Radar/Overview.vue"));
import SystemSettings from "./partials/Settings/SystemSettings.vue";
import Users from "./partials/Users/Index.vue";
import UserDetails from "./partials/Users/Show.vue";

// ── Props ──────────────────────────────────────────────────────────
const props = defineProps<{
	users: any;
	roles: any[];
	permissions: any[];
	modules: any[];
	stats: Record<string, number>;
	pendingCount: number;
	radarConfig: any[];
	radarStats: any;
	radarDetections: any[];
	radarBlockedItems: any[];
	auditStats: any;
	auditRecentEvents: any[];
	smtpConfig?: any;
	emailLogs?: any[];
	webhookConfig?: any;
	webhookDeliveries?: any[];
	systemSettings?: any;
	notifications?: any[];
	unreadNotificationsCount?: number;
}>();

const usersArray = computed(() => {
	if (!props.users) return [];
	if (Array.isArray(props.users)) return props.users;
	if (props.users && Array.isArray(props.users.data)) return props.users.data;
	return [];
});

const notifications = computed(() => props.notifications || []);

const clearNotifications = () => {
	router.post("/workos/notifications/clear", {}, {
		preserveScroll: true,
	});
};

const markAllNotificationsAsRead = () => {
	router.post("/workos/notifications/mark-all-read", {}, {
		preserveScroll: true,
	});
};

const toggleNotificationRead = (n: any) => {
	router.post(`/workos/notifications/${n.id}/toggle-read`, {}, {
		preserveScroll: true,
	});
};

const unreadNotificationsCount = computed(() => {
	return props.unreadNotificationsCount ?? 0;
});

// ── State — URL-based navigation with sessionStorage fallback ──────
const SESSION_KEY = "workos_ui_state";

// Map page IDs → URL path segments (relative to /workos/)
const PAGE_TO_PATH: Record<string, string> = {
	overview: "",
	organizations: "organizations",
	"organizations.show": "organizations",
	users: "users",
	"users.show": "users",
	authentication: "authentication",
	authorization: "authorization",
	radar: "radar",
	"audit-logs": "audit-logs",
	emails: "emails",
	notifications: "notifications",
	settings: "settings",
};
function getPageFromUrl(): string {
	const path = location.pathname.replace(/^\/workos\/?/, "");
	if (!path) return "overview";

	const segments = path.split("/");
	const firstSegment = segments[0];

	if (firstSegment === "organizations") {
		return "organizations";
	}
	if (firstSegment === "users") {
		return "users";
	}
	if (firstSegment === "auth") {
		const sub = segments[1];
		return sub ? `auth.${sub}` : "authentication";
	}
	if (firstSegment === "authentication") {
		return "authentication";
	}
	if (firstSegment === "authz") {
		const sub = segments[1];
		return sub ? `authz.${sub}` : "authorization";
	}
	if (firstSegment === "authorization") {
		return "authorization";
	}
	if (firstSegment === "audit-logs") {
		const sub = segments[1];
		return sub ? `audit-logs.${sub}` : "audit-logs";
	}

	return firstSegment;
}

function loadState() {
	try {
		return JSON.parse(sessionStorage.getItem(SESSION_KEY) || "{}");
	} catch {
		return {};
	}
}

// Init activePage from URL path first, then sessionStorage
const _urlPage = getPageFromUrl();
const _s = loadState();

let initialPage = _urlPage;
if (_urlPage === "users") {
	if (_s.page === "users.show" && _s.userId) {
		initialPage = "users.show";
	}
} else if (_urlPage === "organizations") {
	if (_s.page === "organizations.show" && _s.orgId) {
		initialPage = "organizations.show";
	}
}

const activePage = ref<string>(initialPage);
const selectedUser = ref<any>(null);
const selectedOrganization = ref<any>(null);
const searchQuery = ref("");
const isPageLoading = ref(false);

// Verification modal states
const showVerifyModal = ref(false);
const verifyData = ref<any>(null);
const isLoadingVerifyData = ref(false);
const showApproveConfirm = ref(false);
const showRejectReasonInput = ref(false);
const rejectReason = ref("");
const isActionProcessing = ref(false);

function handleViewRegistration(requestId: string) {
	showVerifyModal.value = true;
	isLoadingVerifyData.value = true;
	showApproveConfirm.value = false;
	showRejectReasonInput.value = false;
	rejectReason.value = "";
	verifyData.value = null;

	fetch(`/workos/users/registration-requests/${requestId}`)
		.then((res) => res.json())
		.then((data) => {
			verifyData.value = data;
			isLoadingVerifyData.value = false;
		})
		.catch((err) => {
			console.error("Gagal memuat data verifikasi", err);
			isLoadingVerifyData.value = false;
			showVerifyModal.value = false;
		});
}

function executeApprove() {
	if (!verifyData.value) return;
	isActionProcessing.value = true;
	router.post(`/workos/users/${verifyData.value.user_id}/approve`, {}, {
		onSuccess: () => {
			showVerifyModal.value = false;
			showApproveConfirm.value = false;
			isActionProcessing.value = false;
			router.reload();
		},
		onError: (err) => {
			console.error("Gagal menyetujui user", err);
			isActionProcessing.value = false;
		}
	});
}

function executeReject() {
	if (!verifyData.value) return;
	isActionProcessing.value = true;
	router.post(`/workos/users/${verifyData.value.user_id}/reject`, {
		reason: rejectReason.value
	}, {
		onSuccess: () => {
			showVerifyModal.value = false;
			showRejectReasonInput.value = false;
			isActionProcessing.value = false;
			rejectReason.value = "";
			router.reload();
		},
		onError: (err) => {
			console.error("Gagal menolak user", err);
			isActionProcessing.value = false;
		}
	});
}

// Map activePage ID → required props to be loaded dynamically
const PAGE_REQUIRED_PROPS: Record<string, string[]> = {
	overview: ["stats"],
	organizations: ["modules"],
	"organizations.show": ["modules", "roles", "users"],
	users: ["users"],
	"users.show": ["users", "roles", "modules"],
	authorization: ["roles", "permissions"],
	radar: ["radarConfig", "radarStats", "radarDetections", "radarBlockedItems"],
	"audit-logs": ["auditStats", "auditRecentEvents"],
	emails: ["smtpConfig"],
	notifications: ["notifications"],
	settings: ["systemSettings"],
};

const loadedProps = ref<Record<string, boolean>>({});

function isPropEmpty(propName: string): boolean {
	const val = (props as any)[propName];
	if (val === undefined || val === null) return true;
	if (Array.isArray(val) && val.length === 0) return true;
	if (typeof val === "object" && Object.keys(val).length === 0) return true;
	return false;
}

function getThreatSeverityType(severity: string): string {
	if (severity === "high") return "error";
	if (severity === "medium") return "warning";
	return "info";
}

// Restore org/user objects from props on mount (props are available at this point)
onMounted(() => {
	// Mark initially loaded non-empty props as loaded
	Object.keys(props).forEach((key) => {
		if (!isPropEmpty(key)) {
			loadedProps.value[key] = true;
		}
	});

	const s = loadState();
	if (s.orgId) {
		const org = props.modules.find((m: any) => m.id === s.orgId);
		if (org) {
			selectedOrganization.value = org;
		} else {
			activePage.value = "organizations";
			persistState("organizations", null, null);
		}
	}
	if (s.userId) {
		const user = usersArray.value.find((u: any) => u.id === s.userId);
		if (user) {
			selectedUser.value = user;
		} else {
			activePage.value = "users";
			persistState("users", null, null);
		}
	}

	// Trigger lazy loading of required props for the initial page load if not already loaded
	let pageKey = activePage.value;
	if (pageKey.startsWith("authz.")) {
		pageKey = "authorization";
	}
	const required = PAGE_REQUIRED_PROPS[pageKey] || [];
	const propsToLoad = required.filter(
		(p) => isPropEmpty(p) && !loadedProps.value[p],
	);
	if (propsToLoad.length > 0) {
		isPageLoading.value = true;
		router.reload({
			only: propsToLoad,
			onFinish: () => {
				isPageLoading.value = false;
				propsToLoad.forEach((p) => {
					if (!isPropEmpty(p)) {
						loadedProps.value[p] = true;
					}
				});
			}
		});
	}

	if (globalThis.window !== undefined && (globalThis as any).Broadcaster) {
		(globalThis as any).Broadcaster.private("radar.alerts").listen(
			"ThreatDetected",
			(e: any) => {
				router.reload({
					only: ["notifications", "unreadNotificationsCount"],
				});
			},
		);
	}
});

watch(
	() => props.modules,
	(newModules) => {
		if (selectedOrganization.value) {
			selectedOrganization.value =
				newModules.find((m: any) => m.id === selectedOrganization.value.id) ||
				null;
		}
	},
	{ deep: true },
);

watch(
	() => props.users,
	(newUsers) => {
		if (selectedUser.value) {
			const arr = Array.isArray(newUsers) ? newUsers : (newUsers?.data || []);
			selectedUser.value =
				arr.find((u: any) => u.id === selectedUser.value.id) || null;
		}
	},
	{ deep: true },
);

function persistState(
	page: string,
	orgId: number | null = null,
	userId: number | null = null,
) {
	try {
		sessionStorage.setItem(
			SESSION_KEY,
			JSON.stringify({ page, orgId, userId }),
		);
	} catch {}
}

function pushUrl(page: string) {
	const segment = PAGE_TO_PATH[page] ?? page.replace(".", "/");
	const newPath = segment ? `/workos/${segment}` : "/workos";
	if (location.pathname !== newPath) {
		history.pushState({ page }, "", newPath);
	}
}

function navigate(page: string) {
	isPageLoading.value = true;
	activePage.value = page;
	if (page !== "organizations.show") selectedOrganization.value = null;
	if (page !== "users.show") selectedUser.value = null;
	pushUrl(page);
	persistState(
		activePage.value,
		selectedOrganization.value?.id ?? null,
		selectedUser.value?.id ?? null,
	);

	// Lazy load required props for the new page
	let pageKey = page;
	if (page.startsWith("authz.")) {
		pageKey = "authorization";
	}
	const required = PAGE_REQUIRED_PROPS[pageKey] || [];
	const propsToLoad = required.filter(
		(p) => isPropEmpty(p) && !loadedProps.value[p],
	);

	const startTime = Date.now();
	const MIN_LOAD_TIME = 350; // minimum skeleton show time for smooth transition

	if (propsToLoad.length > 0) {
		router.reload({
			only: propsToLoad,
			onFinish: () => {
				const elapsedTime = Date.now() - startTime;
				const remainingTime = Math.max(0, MIN_LOAD_TIME - elapsedTime);
				setTimeout(() => {
					isPageLoading.value = false;
					propsToLoad.forEach((p) => {
						if (!isPropEmpty(p)) {
							loadedProps.value[p] = true;
						}
					});
				}, remainingTime);
			}
		});
	} else {
		setTimeout(() => {
			isPageLoading.value = false;
		}, MIN_LOAD_TIME);
	}

	const main = document.getElementById("wos-main");
	if (main) main.scrollTop = 0;
}

function openOrganization(org: any) {
	selectedOrganization.value = org;
	persistState("organizations.show", org?.id ?? null, null);
	navigate("organizations.show");
}

function openUser(user: any) {
	selectedUser.value = user;
	persistState("users.show", null, user?.id ?? null);
	navigate("users.show");
}

// ── Navigation — mirrors WorkOS sidebar exactly ────────────────────
const navGroups = [
	{
		// No label (top group)
		items: [
			{
				id: "overview",
				label: "Overview",
				icon: "M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z",
			},
			{
				id: "organizations",
				label: "Organizations",
				icon: "M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4",
			},
			{
				id: "users",
				label: "Users",
				badge: props.pendingCount || undefined,
				icon: "M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z",
			},
		],
	},
	{
		label: "Products",
		items: [
			{
				id: "authentication",
				label: "Authentication",
				icon: "M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z",
				hasSubmenu: true,
			},
			{
				id: "authorization",
				label: "Authorization",
				icon: "M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z",
				hasSubmenu: true,
			},
			{
				id: "radar",
				label: "Radar",
				icon: "M13 10V3L4 14h7v7l9-11h-7z",
			},
			{
				id: "audit-logs",
				label: "Audit Logs",
				icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2",
			},
		],
	},
	{
		label: "Developer",
		items: [
			{
				id: "emails",
				label: "Emails",
				icon: "M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z",
			},
		],
	},
];

// ── Bottom nav items (Notifications + Settings) pinned ─────────────
const bottomNavItems = computed(() => [
	{
		id: "notifications",
		label: "System Alerts",
		badge: unreadNotificationsCount.value || undefined,
		icon: "M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9",
	},
	{
		id: "settings",
		label: "Settings",
		icon: "M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z",
	},
]);

// ── Computed ────────────────────────────────────────────────────────
const allNavItems = computed(() => [
	...navGroups.flatMap((g) => g.items),
	...bottomNavItems.value,
]);

const activeLabel = computed(() => {
	if (activePage.value === "organizations.show") return "Organizations";
	if (activePage.value === "users.show") return "Users";
	const item = allNavItems.value.find(
		(i) =>
			i.id === activePage.value ||
			(activePage.value.startsWith("auth") && i.id === "authentication"),
	);
	return item?.label ?? activePage.value;
});
</script>

<template>
    <Head title="WorkOS – FMIKOM Portal">
        <title>WorkOS – FMIKOM Portal</title>
    </Head>

    <DashboardLayout
        :nav-groups="navGroups"
        :bottom-nav-items="bottomNavItems"
        :active-page="activePage"
        :active-label="activeLabel"
        :pending-count="pendingCount"
        :notifications="notifications"
        :unread-notifications-count="unreadNotificationsCount"
        @navigate="navigate"
        @search="searchQuery = $event"
        @view-registration="handleViewRegistration"
    >
        <Transition name="fade-slide" mode="out-in">
            <div :key="activePage + '_' + isPageLoading" class="w-full h-full">
                <!-- Shimmer Skeleton Loading Page -->
                <div v-if="isPageLoading" class="px-8 pt-8 pb-12 space-y-6 w-full max-w-[1200px]" style="font-family: var(--wos-font)">
                    <!-- Title Skeleton -->
                    <div class="space-y-2">
                        <div class="h-6 w-48 wos-shimmer rounded-md" />
                        <div class="h-3 w-80 wos-shimmer rounded-md mt-2" />
                    </div>

                    <!-- Overview Page Skeleton -->
                    <div v-if="activePage === 'overview'" class="space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <div v-for="i in 4" :key="i" class="bg-white dark:bg-zinc-900 rounded-xl p-5 border border-gray-100 dark:border-zinc-800 ring-1 ring-gray-900/4 dark:ring-white/5 space-y-3">
                                <div class="flex justify-between items-center">
                                    <div class="h-3 w-20 wos-shimmer rounded" />
                                    <div class="h-4 w-4 wos-shimmer rounded" />
                                </div>
                                <div class="h-8 w-16 wos-shimmer rounded mt-1" />
                                <div class="h-2.5 w-32 wos-shimmer rounded mt-2" />
                            </div>
                        </div>
                        <div class="bg-white dark:bg-zinc-900 rounded-xl p-6 border border-gray-100 dark:border-zinc-800 ring-1 ring-gray-900/4 dark:ring-white/5 space-y-4">
                            <div class="h-4 w-28 wos-shimmer rounded mb-4" />
                            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3.5">
                                <div v-for="i in 6" :key="i" class="h-20 wos-shimmer rounded-xl" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                            <div class="lg:col-span-2 bg-white dark:bg-zinc-900 rounded-xl p-6 border border-gray-100 dark:border-zinc-800 ring-1 ring-gray-900/4 dark:ring-white/5 space-y-4">
                                <div class="h-4 w-32 wos-shimmer rounded" />
                                <div v-for="i in 3" :key="i" class="flex gap-3">
                                    <div class="w-8 h-8 rounded-full wos-shimmer shrink-0" />
                                    <div class="flex-1 space-y-2 mt-1">
                                        <div class="h-3.5 w-full wos-shimmer rounded" />
                                        <div class="h-2.5 w-24 wos-shimmer rounded" />
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white dark:bg-zinc-900 rounded-xl p-6 border border-gray-100 dark:border-zinc-800 ring-1 ring-gray-900/4 dark:ring-white/5 space-y-4">
                                <div class="h-4 w-32 wos-shimmer rounded" />
                                <div v-for="i in 4" :key="i" class="h-10 wos-shimmer rounded-lg" />
                            </div>
                        </div>
                    </div>

                    <!-- 1. Table Pages Skeleton (Users, Organizations, Audit Logs) -->
                    <div v-else-if="['users', 'organizations', 'audit-logs', 'audit-logs.events', 'audit-logs.security'].includes(activePage)" class="space-y-6">
                        <!-- Toolbar skeleton -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex flex-wrap items-center gap-2">
                                <div class="h-9 w-64 wos-shimmer rounded-lg" />
                                <div class="h-9 w-28 wos-shimmer rounded-lg" />
                                <div class="h-9 w-24 wos-shimmer rounded-lg" />
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="h-9 w-32 wos-shimmer rounded-lg" />
                                <div class="h-9 w-28 wos-shimmer rounded-lg" />
                            </div>
                        </div>
                        <!-- Table skeleton -->
                        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-gray-100 dark:border-zinc-800 overflow-hidden shadow-sm dark:shadow-none">
                            <div class="bg-[#f9fafb] dark:bg-zinc-800/40 border-b border-gray-100 dark:border-zinc-800 px-6 py-4 flex items-center justify-between">
                                <div class="h-3.5 w-32 wos-shimmer rounded" />
                                <div class="h-3.5 w-24 wos-shimmer rounded hidden md:block" />
                                <div class="h-3.5 w-20 wos-shimmer rounded hidden md:block" />
                                <div class="h-3.5 w-28 wos-shimmer rounded" />
                            </div>
                            <div class="divide-y divide-gray-100 dark:divide-zinc-800">
                                <div v-for="i in 6" :key="i" class="px-6 py-4 flex items-center justify-between">
                                    <div class="flex items-center gap-3 w-1/3">
                                        <div class="w-9 h-9 rounded-full wos-shimmer shrink-0" />
                                        <div class="space-y-2 flex-1">
                                            <div class="h-3 w-40 wos-shimmer rounded" />
                                            <div class="h-2.5 w-24 wos-shimmer rounded" />
                                        </div>
                                    </div>
                                    <div class="h-3 w-28 wos-shimmer rounded hidden md:block" />
                                    <div class="h-3 w-20 wos-shimmer rounded hidden md:block" />
                                    <div class="h-6 w-20 wos-shimmer rounded-full" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Form / Configurations Skeleton (Emails, Settings, Authentication, Authorization) -->
                    <div v-else-if="['emails', 'settings', 'authentication', 'authorization'].includes(activePage) || activePage.startsWith('auth') || activePage.startsWith('audit-logs.')" class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                        <!-- Left Navigation Sidebar -->
                        <div class="space-y-1">
                            <div v-for="i in 4" :key="i" class="h-9 w-full wos-shimmer rounded-lg" />
                        </div>
                        <!-- Right Form Content -->
                        <div class="lg:col-span-3 bg-white dark:bg-zinc-900 rounded-xl border border-gray-100 dark:border-zinc-800 p-6 space-y-6">
                            <div class="space-y-2">
                                <div class="h-4 w-48 wos-shimmer rounded" />
                                <div class="h-3 w-72 wos-shimmer rounded" />
                            </div>
                            <div class="border-t border-gray-100 dark:border-zinc-800 pt-6 space-y-4">
                                <div v-for="i in 3" :key="i" class="space-y-2">
                                    <div class="h-3 w-32 wos-shimmer rounded" />
                                    <div class="h-9 w-full wos-shimmer rounded-lg" />
                                </div>
                                <div class="flex items-center justify-between py-3 border-t border-b border-gray-100 dark:border-zinc-800 mt-6">
                                    <div class="space-y-1.5">
                                        <div class="h-3 w-40 wos-shimmer rounded" />
                                        <div class="h-2.5 w-60 wos-shimmer rounded" />
                                    </div>
                                    <div class="h-6 w-10 wos-shimmer rounded-full" />
                                </div>
                                <div class="flex justify-end pt-4">
                                    <div class="h-9 w-24 wos-shimmer rounded-lg" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Detail Views Skeleton (users.show, organizations.show) -->
                    <div v-else-if="['users.show', 'organizations.show'].includes(activePage)" class="space-y-6">
                        <!-- Back button -->
                        <div class="h-8 w-24 wos-shimmer rounded-md" />
                        <!-- Header banner & Profile Card -->
                        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-gray-100 dark:border-zinc-800 overflow-hidden p-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-full wos-shimmer shrink-0" />
                                <div class="space-y-2">
                                    <div class="h-5 w-48 wos-shimmer rounded" />
                                    <div class="h-3.5 w-64 wos-shimmer rounded" />
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <div class="h-9 w-24 wos-shimmer rounded-lg" />
                                <div class="h-9 w-24 wos-shimmer rounded-lg" />
                            </div>
                        </div>
                        <!-- Details Grid -->
                        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-gray-100 dark:border-zinc-800 p-6 space-y-6">
                            <div class="h-4 w-32 wos-shimmer rounded" />
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-gray-100 dark:border-zinc-800">
                                <div v-for="i in 6" :key="i" class="space-y-2">
                                    <div class="h-3 w-24 wos-shimmer rounded" />
                                    <div class="h-4.5 w-40 wos-shimmer rounded" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Radar Page Skeleton -->
                    <div v-else-if="activePage === 'radar'" class="space-y-6">
                        <div class="flex justify-between items-center">
                            <div class="h-4 w-40 wos-shimmer rounded" />
                            <div class="h-6 w-28 wos-shimmer rounded-full" />
                        </div>
                        <!-- Radar Pulse Animation Placeholder -->
                        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-gray-100 dark:border-zinc-800 p-8 flex flex-col items-center justify-center min-h-[300px]">
                            <div class="w-36 h-36 rounded-full wos-shimmer flex items-center justify-center">
                                <div class="w-24 h-24 rounded-full bg-white dark:bg-zinc-900" />
                            </div>
                            <div class="h-4 w-48 wos-shimmer rounded mt-6" />
                        </div>
                        <!-- Radar bottom threat items -->
                        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-gray-100 dark:border-zinc-800 p-6 space-y-4">
                            <div class="h-4 w-32 wos-shimmer rounded" />
                            <div v-for="i in 3" :key="i" class="h-12 wos-shimmer rounded-lg" />
                        </div>
                    </div>

                    <!-- Fallback Skeleton -->
                    <div v-else class="space-y-4">
                        <div class="bg-white dark:bg-zinc-900 rounded-xl p-6 border border-gray-100 dark:border-zinc-800 ring-1 ring-gray-900/4 dark:ring-white/5 space-y-4">
                            <div class="h-4 w-full wos-shimmer rounded" v-for="i in 6" :key="i" />
                        </div>
                    </div>
                </div>

                <!-- Real View rendering when not loading -->
                <div v-else class="h-full w-full">
                    <!-- ── Content views ── -->
                    <Overview
                        v-if="activePage === 'overview'"
                        :stats="stats"
                        @navigate="navigate"
                        @open-detail="u => (selectedUser = u)"
                    />
                    <Organizations
                        v-else-if="activePage === 'organizations'"
                        :modules="modules"
                        :search-query="searchQuery"
                        @open-detail="openOrganization"
                    />
                    <OrganizationDetails
                        v-else-if="activePage === 'organizations.show'"
                        :organization="selectedOrganization"
                        :roles="roles"
                        :users="usersArray"
                        @back="navigate('organizations')"
                    />
                    <Users
                        v-else-if="activePage === 'users'"
                        :users="users"
                        :roles="roles"
                        :pending-count="pendingCount"
                        :search-query="searchQuery"
                        @open-detail="openUser"
                    />
                    <UserDetails
                        v-else-if="activePage === 'users.show'"
                        :user="selectedUser"
                        :roles="roles"
                        :modules="modules"
                        @back="navigate('users')"
                    />
                    <Authentication 
                        v-else-if="activePage === 'authentication' || ['auth.analytics', 'auth.methods', 'auth.providers', 'auth.features', 'auth.sessions', 'auth.sso'].includes(activePage)" 
                        :active-tab="activePage.startsWith('auth.') ? activePage.replace('auth.', '') : 'analytics'"
                        @navigate="navigate" 
                    />
                    <Authorization
                        v-else-if="activePage === 'authorization' || ['authz.overview', 'authz.roles', 'authz.permissions', 'authz.assignments', 'authz.access-control'].includes(activePage)"
                        :active-tab="activePage.startsWith('authz.') ? activePage.replace('authz.', '') : 'overview'"
                        :roles="roles"
                        :permissions="permissions"
                        :users="usersArray"
                        :modules="modules"
                        :stats="stats"
                        :search-query="searchQuery"
                        @navigate="navigate"
                    />
                    <Radar
                        v-else-if="activePage === 'radar'"
                        :radar-config="radarConfig"
                        :radar-stats="radarStats"
                        :radar-detections="radarDetections"
                        :radar-blocked-items="radarBlockedItems"
                    />
                    <AuditLogsIndex
                        v-else-if="activePage === 'audit-logs'"
                        :stats="auditStats"
                        :recent_events="auditRecentEvents"
                        @navigate="navigate"
                    />
                    <AuditLogsEvents
                        v-else-if="activePage === 'audit-logs.events'"
                        @navigate="navigate"
                    />
                    <AuditLogsSecurity
                        v-else-if="activePage === 'audit-logs.security'"
                        @navigate="navigate"
                    />
                    <EmailsIndex
                        v-else-if="activePage === 'emails'"
                        :smtp-config="smtpConfig"
                        :email-logs="emailLogs || []"
                    />
                    <NotificationsIndex
                        v-else-if="activePage === 'notifications'"
                        :notifications="notifications"
                        :webhook-config="webhookConfig"
                        :webhook-deliveries="webhookDeliveries || []"
                        @mark-all-read="markAllNotificationsAsRead"
                        @clear-feed="clearNotifications"
                        @toggle-read="toggleNotificationRead"
                    />
                    <SystemSettings
                        v-else-if="activePage === 'settings'"
                        :settings="systemSettings || {}"
                    />
                    <Placeholder v-else :title="activePage" />
                </div>
            </div>
        </Transition>

        <!-- Modern UI Verification Modal -->
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="showVerifyModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showVerifyModal = false"></div>

                <!-- Modal Content Card -->
                <div class="bg-white dark:bg-zinc-900 rounded-3xl shadow-xl ring-1 ring-slate-100 dark:ring-zinc-800 w-full max-w-lg p-6 sm:p-8 relative z-10 animate-in zoom-in-95 duration-200">
                    <!-- Close button -->
                    <button @click="showVerifyModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-650 dark:hover:text-zinc-200 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <div v-if="isLoadingVerifyData" class="flex flex-col items-center justify-center py-10 space-y-3">
                        <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                        <span class="text-xs text-slate-500 dark:text-zinc-400 font-medium">Memuat data pendaftar...</span>
                    </div>

                    <div v-else-if="verifyData" class="space-y-6">
                        <!-- Header -->
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400 mb-2 capitalize">
                                Calon {{ verifyData.role }}
                            </span>
                            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Verifikasi Pendaftaran Akun</h2>
                            <p class="text-xs text-slate-400 dark:text-zinc-500 mt-1">Silakan periksa kelayakan data sebelum memberikan keputusan.</p>
                        </div>

                        <!-- Data details grid -->
                        <div class="bg-slate-50 dark:bg-zinc-850/40 border border-slate-100 dark:border-zinc-800/80 rounded-2xl p-5 space-y-3 text-xs sm:text-sm">
                            <div class="grid grid-cols-3 gap-2">
                                <span class="text-slate-400">Nama Lengkap</span>
                                <span class="col-span-2 font-semibold text-slate-800 dark:text-zinc-250">: {{ verifyData.name }}</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <span class="text-slate-400">Alamat Email</span>
                                <span class="col-span-2 font-medium text-slate-800 dark:text-zinc-250">: {{ verifyData.email }}</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <span class="text-slate-400">No. Identitas (NIM/NIB)</span>
                                <span class="col-span-2 font-medium text-slate-800 dark:text-zinc-250">: {{ verifyData.identifier }}</span>
                            </div>
                            <template v-if="verifyData.extra && Object.keys(verifyData.extra).length > 0">
                                <div v-for="(val, key) in verifyData.extra" :key="key" class="grid grid-cols-3 gap-2">
                                    <span class="text-slate-400">{{ key }}</span>
                                    <span class="col-span-2 font-medium text-slate-800 dark:text-zinc-250">: {{ val }}</span>
                                </div>
                            </template>
                            <div class="grid grid-cols-3 gap-2">
                                <span class="text-slate-400">Status</span>
                                <span class="col-span-2 font-bold text-yellow-600 dark:text-yellow-400 capitalize">: {{ verifyData.status }}</span>
                            </div>
                        </div>

                        <!-- Sub-action: Confirmation of Approval -->
                        <div v-if="showApproveConfirm" class="bg-blue-50/50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/30 rounded-2xl p-4 text-xs space-y-3 animate-in slide-in-from-bottom-2 duration-200">
                            <p class="font-bold text-blue-800 dark:text-blue-400">Apakah Anda yakin menyetujui pendaftaran ini?</p>
                            <p class="text-slate-500 dark:text-zinc-450">Menyetujui pendaftaran akan langsung membuat tautan aktivasi unik dan mengirimkannya secara otomatis via email ke calon pengguna.</p>
                            <div class="flex gap-2">
                                <button
                                    @click="executeApprove"
                                    :disabled="isActionProcessing"
                                    class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-colors flex items-center gap-1"
                                >
                                    <span v-if="isActionProcessing" class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                    Ya, Setujui
                                </button>
                                <button
                                    @click="showApproveConfirm = false"
                                    class="px-3.5 py-1.5 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-zinc-350 rounded-lg font-semibold hover:bg-slate-50 dark:hover:bg-zinc-750 transition-colors"
                                >
                                    Batal
                                </button>
                            </div>
                        </div>

                        <!-- Sub-action: Reject input reason -->
                        <div v-else-if="showRejectReasonInput" class="bg-red-50/50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/30 rounded-2xl p-4 text-xs space-y-3 animate-in slide-in-from-bottom-2 duration-200">
                            <p class="font-bold text-red-800 dark:text-red-400">Alasan Penolakan (Opsional)</p>
                            <textarea
                                v-model="rejectReason"
                                placeholder="Tuliskan catatan alasan penolakan agar calon pendaftar mengetahui alasannya..."
                                class="w-full h-20 p-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-200 focus:outline-none focus:border-red-500 transition-colors text-xs resize-none"
                            ></textarea>
                            <div class="flex gap-2">
                                <button
                                    @click="executeReject"
                                    :disabled="isActionProcessing"
                                    class="px-3.5 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition-colors flex items-center gap-1"
                                >
                                    <span v-if="isActionProcessing" class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                    Tolak Pendaftaran
                                </button>
                                <button
                                    @click="showRejectReasonInput = false"
                                    class="px-3.5 py-1.5 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-zinc-350 rounded-lg font-semibold hover:bg-slate-50 dark:hover:bg-zinc-750 transition-colors"
                                >
                                    Batal
                                </button>
                            </div>
                        </div>

                        <!-- Main Actions -->
                        <div v-else class="flex gap-3 pt-2">
                            <button
                                @click="showApproveConfirm = true"
                                class="flex-1 bg-green-600 hover:bg-green-700 text-white rounded-xl h-11 text-xs sm:text-sm font-bold shadow-md transition-all flex items-center justify-center gap-1.5"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                Setujui Pendaftaran
                            </button>
                            <button
                                @click="showRejectReasonInput = true"
                                class="flex-1 bg-red-600 hover:bg-red-700 text-white rounded-xl h-11 text-xs sm:text-sm font-bold shadow-md transition-all flex items-center justify-center gap-1.5"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                Tolak
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </DashboardLayout>
</template>
