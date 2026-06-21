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
		propsToLoad.forEach((p) => {
			loadedProps.value[p] = true;
		});
		router.reload({
			only: propsToLoad,
			onFinish: () => {
				isPageLoading.value = false;
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
		propsToLoad.forEach((p) => {
			loadedProps.value[p] = true;
		});
		router.reload({
			only: propsToLoad,
			onFinish: () => {
				const elapsedTime = Date.now() - startTime;
				const remainingTime = Math.max(0, MIN_LOAD_TIME - elapsedTime);
				setTimeout(() => {
					isPageLoading.value = false;
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
		label: "Notifications",
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
        @navigate="navigate"
        @search="searchQuery = $event"
    >
        <Transition name="fade-slide" mode="out-in">
            <div :key="activePage + '_' + isPageLoading" class="w-full">
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
                            <div v-for="i in 4" :key="i" class="bg-white rounded-xl p-5 border border-gray-100 ring-1 ring-gray-900/4 space-y-3">
                                <div class="flex justify-between items-center">
                                    <div class="h-3 w-20 wos-shimmer rounded" />
                                    <div class="h-4 w-4 wos-shimmer rounded" />
                                </div>
                                <div class="h-8 w-16 wos-shimmer rounded mt-1" />
                                <div class="h-2.5 w-32 wos-shimmer rounded mt-2" />
                            </div>
                        </div>
                        <div class="bg-white rounded-xl p-6 border border-gray-100 ring-1 ring-gray-900/4 space-y-4">
                            <div class="h-4 w-28 wos-shimmer rounded mb-4" />
                            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3.5">
                                <div v-for="i in 6" :key="i" class="h-20 wos-shimmer rounded-xl" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                            <div class="lg:col-span-2 bg-white rounded-xl p-6 border border-gray-100 ring-1 ring-gray-900/4 space-y-4">
                                <div class="h-4 w-32 wos-shimmer rounded" />
                                <div v-for="i in 3" :key="i" class="flex gap-3">
                                    <div class="w-8 h-8 rounded-full wos-shimmer shrink-0" />
                                    <div class="flex-1 space-y-2 mt-1">
                                        <div class="h-3.5 w-full wos-shimmer rounded" />
                                        <div class="h-2.5 w-24 wos-shimmer rounded" />
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white rounded-xl p-6 border border-gray-100 ring-1 ring-gray-900/4 space-y-4">
                                <div class="h-4 w-32 wos-shimmer rounded" />
                                <div v-for="i in 4" :key="i" class="h-10 wos-shimmer rounded-lg" />
                            </div>
                        </div>
                    </div>

                    <!-- General Data/Table/Form Page Skeleton -->
                    <div v-else-if="['organizations', 'organizations.show', 'users', 'users.show', 'audit-logs', 'emails', 'settings', 'authentication', 'authorization', 'radar', 'notifications'].includes(activePage) || activePage.startsWith('auth') || activePage.startsWith('audit-logs.')" class="space-y-4">
                        <div class="flex justify-between items-center">
                            <div class="h-[34px] w-64 wos-shimmer rounded-md" />
                            <div class="h-[34px] w-28 wos-shimmer rounded-md" />
                        </div>
                        <div class="bg-white rounded-xl overflow-hidden ring-1 ring-gray-900/4">
                            <div class="bg-gray-50 border-b border-gray-100 px-6 py-3 flex gap-4">
                                <div class="h-3 w-24 wos-shimmer rounded" v-for="i in 4" :key="i" />
                            </div>
                            <div class="p-6 space-y-4">
                                <div v-for="i in 5" :key="i" class="flex gap-4 items-center">
                                    <div class="h-4 w-full wos-shimmer rounded" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fallback Skeleton -->
                    <div v-else class="space-y-4">
                        <div class="bg-white rounded-xl p-6 border border-gray-100 ring-1 ring-gray-900/4 space-y-4">
                            <div class="h-4 w-full wos-shimmer rounded" v-for="i in 6" :key="i" />
                        </div>
                    </div>
                </div>

                <!-- Real View rendering when not loading -->
                <div v-else>
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
                    />
                    <NotificationsIndex
                        v-else-if="activePage === 'notifications'"
                        :notifications="notifications"
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
    </DashboardLayout>
</template>
