<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import { ChevronLeft, LayoutGrid } from "lucide-vue-next";
import { computed, ref, watch } from "vue";
import SidebarGroup from "./SidebarGroup.vue";
import SidebarItem from "./SidebarItem.vue";

interface NavItem {
	id: string;
	label: string;
	icon: string;
	badge?: number;
	hasSubmenu?: boolean;
}

interface NavGroup {
	label?: string;
	items: NavItem[];
}

const props = defineProps<{
	navGroups: NavGroup[];
	bottomNavItems: NavItem[];
	activePage: string;
	sidebarOpen: boolean;
	collapsed: boolean;
}>();

const emit = defineEmits<{
	(e: "navigate", id: string): void;
	(e: "close"): void;
	(e: "toggle-collapse"): void;
}>();

const page = usePage();
const authUser = computed(() => (page.props as any).auth?.user);
const siteSettings = computed(() => (page.props as any).siteSettings || {});
const userInitial = computed(() =>
	(authUser.value?.name ?? "A").charAt(0).toUpperCase(),
);
const userName = computed(() => authUser.value?.name ?? "Admin");
const userEmail = computed(() => authUser.value?.email ?? "");

// Floating/sliding sidebar hover highlight effect
const hoverStyle = ref({ top: "0px", height: "0px", opacity: 0 });
const hoverStyleBottom = ref({ top: "0px", height: "0px", opacity: 0 });

const handleMouseEnter = (e: MouseEvent) => {
	const el = e.currentTarget as HTMLElement;
	const container = el.closest('nav');
	if (container) {
		const elRect = el.getBoundingClientRect();
		const containerRect = container.getBoundingClientRect();
		hoverStyle.value = {
			top: `${elRect.top - containerRect.top + container.scrollTop}px`,
			height: `${elRect.height}px`,
			opacity: 1,
		};
	}
};

const handleMouseLeave = () => {
	hoverStyle.value.opacity = 0;
};

const handleMouseEnterBottom = (e: MouseEvent) => {
	const el = e.currentTarget as HTMLElement;
	const container = el.parentElement?.closest('.relative');
	if (container) {
		const elRect = el.getBoundingClientRect();
		const containerRect = container.getBoundingClientRect();
		hoverStyleBottom.value = {
			top: `${elRect.top - containerRect.top}px`,
			height: `${elRect.height}px`,
			opacity: 1,
		};
	}
};

const handleMouseLeaveBottom = () => {
	hoverStyleBottom.value.opacity = 0;
};

function isActive(id: string): boolean {
	if (id === props.activePage) return true;
	if (id === "organizations" && props.activePage.startsWith("organizations"))
		return true;
	if (id === "users" && props.activePage.startsWith("users")) return true;
	if (id === "authentication") {
		const authMenus = [
			"auth.analytics",
			"auth.methods",
			"auth.providers",
			"auth.features",
			"auth.sessions",
			"auth.mfa",
			"auth.passkeys",
			"auth.sso",
		];
		return authMenus.includes(props.activePage);
	}
	if (id === "authorization") {
		const authzMenus = [
			"authz.overview",
			"authz.roles",
			"authz.permissions",
			"authz.assignments",
			"authz.policies",
			"authz.groups",
			"authz.access-control",
		];
		return authzMenus.includes(props.activePage);
	}
	return false;
}

const forceMainMenu = ref(false);

const isAuthMenu = computed(() => {
	if (forceMainMenu.value) return false;
	const authMenus = [
		"authentication",
		"auth.analytics",
		"auth.methods",
		"auth.providers",
		"auth.features",
		"auth.sessions",
		"auth.mfa",
		"auth.passkeys",
		"auth.sso",
	];
	return authMenus.includes(props.activePage);
});

const isAuthzMenu = computed(() => {
	if (forceMainMenu.value) return false;
	const authzMenus = [
		"authorization",
		"authz.overview",
		"authz.roles",
		"authz.permissions",
		"authz.assignments",
		"authz.policies",
		"authz.groups",
		"authz.access-control",
	];
	return authzMenus.includes(props.activePage);
});

watch(
	() => props.activePage,
	() => {
		forceMainMenu.value = false;
	},
);

const authTabs = [
	{ id: "auth.analytics", label: "Analytics" },
	{ id: "auth.methods", label: "Methods" },
	{ id: "auth.providers", label: "Providers" },
	{ id: "auth.features", label: "Features" },
	{ id: "auth.sessions", label: "Sessions" },
	{ id: "auth.mfa", label: "MFA (TOTP)" },
	{ id: "auth.passkeys", label: "Passkeys" },
	{ id: "auth.sso", label: "Add-ons" },
];

const authRelated = [
	{ id: "authorization", label: "Authorization" },
	{ id: "branding", label: "Branding" },
	{ id: "domains", label: "Domains" },
	{ id: "redirects", label: "Redirects" },
];

const authzTabs = [
	{ id: "authz.overview", label: "Overview" },
	{ id: "authz.roles", label: "Roles" },
	{ id: "authz.permissions", label: "Permissions" },
	{ id: "authz.assignments", label: "Role Assignments" },
	{ id: "authz.policies", label: "Policies" },
	{ id: "authz.groups", label: "Groups" },
	{ id: "authz.access-control", label: "Access Control" },
];

const authzRelated = [{ id: "audit-logs", label: "Audit Logs" }];

import { useAppearance } from "@/composables/useAppearance";
import { ThemeTogglerButton } from "@/components/animate-ui/components/buttons/theme-toggler";

const { appearance, resolvedAppearance, updateAppearance } = useAppearance();

const isDark = computed(() => resolvedAppearance.value === "dark");

const activeTheme = computed({
	get: () => appearance.value === "system" ? resolvedAppearance.value : appearance.value,
	set: (val) => {
		updateAppearance(val);
	}
});
</script>

<template>
    <!--
      Sidebar:
      - Desktop: always visible, collapsible (212px ↔ 52px)
      - Mobile: slide-in drawer over content (212px)
    -->
    <aside
        :class="[
            'wos-sidebar flex flex-col bg-white dark:bg-zinc-900 border-r border-[#e5e7eb] dark:border-zinc-800 shrink-0',
            'transition-all duration-300 ease-out will-change-[width,transform]',
            // Mobile: fixed drawer
            'fixed inset-y-0 left-0 z-50 md:relative md:translate-x-0',
            sidebarOpen ? 'translate-x-0 shadow-xl' : '-translate-x-full',
        ]"
        :style="{
            width: collapsed ? '56px' : '224px',
            fontFamily: 'var(--wos-font)'
        }"
        aria-label="Main navigation"
    >
        <!-- ── Brand / Logo ── -->
        <div
            class="flex items-center border-b border-[#e5e7eb] dark:border-zinc-800 shrink-0 overflow-hidden transition-all duration-300 pl-[13px] pr-3"
            style="height: 52px"
        >
            <!-- Logo mark -->
            <div
                class="w-[30px] h-[30px] rounded-[7px] flex items-center justify-center shrink-0 cursor-pointer overflow-hidden transition-all duration-300"
                :style="{ backgroundColor: siteSettings.brand_logo ? 'transparent' : '#2563EB' }"
                @click="collapsed ? emit('toggle-collapse') : null"
                :title="collapsed ? 'Expand sidebar' : ''"
            >
                <img v-if="siteSettings.brand_logo" :src="siteSettings.brand_logo" alt="Brand Logo" class="w-full h-full object-contain" />
                <svg v-else class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="color: #B6FF00">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>

            <!-- Brand name + controls (transitioned) -->
            <div
                class="flex-1 flex items-center justify-between min-w-0 transition-all duration-300 overflow-hidden"
                :class="collapsed ? 'opacity-0 max-w-0 ml-0 pointer-events-none' : 'opacity-100 max-w-[180px] ml-2.5'"
            >
                <span class="text-[15px] font-semibold text-[#111827] dark:text-zinc-100 tracking-tight flex-1 truncate">
                    {{ siteSettings.brand_name || 'WorkOS' }}
                </span>

                <!-- Desktop collapse toggle -->
                <button
                    class="hidden md:flex items-center justify-center w-7 h-7 rounded text-[#9ca3af] dark:text-zinc-500 hover:text-[#374151] dark:hover:text-zinc-200 hover:bg-[#f3f4f6] dark:hover:bg-zinc-800 transition-colors shrink-0"
                    aria-label="Collapse sidebar"
                    @click="emit('toggle-collapse')"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </button>

                <!-- Mobile close -->
                <button
                    class="md:hidden flex items-center justify-center w-7 h-7 rounded text-[#9ca3af] hover:text-[#374151] dark:hover:text-zinc-200 dark:text-zinc-200 transition-colors shrink-0"
                    @click="emit('close')"
                    aria-label="Close sidebar"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- ── Scrollable navigation ── -->
        <nav
            class="flex-1 overflow-y-auto wos-scroll py-2 overflow-x-hidden transition-all duration-300 px-2 relative"
            :class="collapsed ? 'space-y-2' : 'space-y-4'"
            role="navigation"
            @mouseleave="handleMouseLeave"
        >
            <!-- Floating Highlighter (smooth animated hover) -->
            <div
                class="wos-sidebar-highlighter absolute left-2 right-2 rounded-md transition-all duration-200 ease-out pointer-events-none z-0"
                :style="{
                    top: hoverStyle.top,
                    height: hoverStyle.height,
                    opacity: hoverStyle.opacity,
                }"
            ></div>

            <!-- Desktop: Unified Sidebar list (Smooth collapsed/expanded state) -->
            <div 
                class="hidden md:block transition-all duration-300 relative z-10"
                :class="collapsed ? 'space-y-2' : 'space-y-4'"
            >
                <div v-for="(group, gi) in navGroups" :key="gi" class="transition-all duration-300">
                    <SidebarGroup :label="group.label" :collapsed="collapsed">
                        <SidebarItem
                            v-for="item in group.items"
                            :key="item.id"
                            :id="item.id"
                            :label="item.label"
                            :icon="item.icon"
                            :badge="item.badge"
                            :has-submenu="item.hasSubmenu"
                            :active="isActive(item.id)"
                            :collapsed="collapsed"
                            @navigate="emit('navigate', $event)"
                            @mouseenter="handleMouseEnter"
                        />
                    </SidebarGroup>
                </div>
            </div>

            <!-- Mobile: Show auth sub-menu OR main nav -->
            <div class="md:hidden">
                <template v-if="isAuthMenu">
                    <div class="px-2 mb-4">
                        <button @click="forceMainMenu = true" class="flex items-center text-[13px] font-medium text-gray-500 hover:text-gray-900 dark:text-zinc-100 mb-4 transition-colors">
                            <ChevronLeft class="w-4 h-4 mr-1" /> Back
                        </button>
                        <h2 class="text-[14px] font-semibold text-gray-900 dark:text-zinc-100 mb-2 px-1">Authentication</h2>
                    </div>
                    
                    <div class="space-y-0.5 mb-6 px-1">
                        <button
                            v-for="tab in authTabs" :key="tab.id"
                            @click="emit('navigate', tab.id); emit('close')"
                            :class="[
                                'w-full text-left px-3 py-1.5 rounded-md text-[13px] font-medium transition-colors',
                                (props.activePage === tab.id || (props.activePage === 'authentication' && tab.id === 'auth.analytics')) ? 'bg-[#EFF6FF] dark:bg-blue-500/15 text-[#2563EB] dark:text-blue-400' : 'text-gray-600 dark:text-zinc-400 hover:bg-gray-100 dark:hover:bg-zinc-800 hover:text-gray-900 dark:hover:text-zinc-100'
                            ]"
                        >
                            {{ tab.label }}
                        </button>
                    </div>

                    <h3 class="text-[11px] font-semibold text-gray-400 dark:text-zinc-500 uppercase tracking-wider mb-2 px-4">Related</h3>
                    <div class="space-y-0.5 px-1">
                        <button
                            v-for="tab in authRelated" :key="tab.id"
                            @click="emit('navigate', tab.id); emit('close')"
                            class="w-full text-left px-3 py-1.5 rounded-md text-[13px] font-medium text-gray-600 dark:text-zinc-400 hover:bg-gray-100 dark:hover:bg-zinc-800 hover:text-gray-900 dark:hover:text-zinc-100 transition-colors"
                        >
                            {{ tab.label }}
                        </button>
                    </div>
                </template>
                <template v-else-if="isAuthzMenu">
                    <div class="px-2 mb-4">
                        <button @click="forceMainMenu = true" class="flex items-center text-[13px] font-medium text-gray-500 hover:text-gray-900 dark:text-zinc-100 mb-4 transition-colors">
                            <ChevronLeft class="w-4 h-4 mr-1" /> Back
                        </button>
                        <h2 class="text-[14px] font-semibold text-gray-900 dark:text-zinc-100 mb-2 px-1">Authorization</h2>
                    </div>
                    
                    <div class="space-y-0.5 mb-6 px-1">
                        <button
                            v-for="tab in authzTabs" :key="tab.id"
                            @click="emit('navigate', tab.id); emit('close')"
                            :class="[
                                'w-full text-left px-3 py-1.5 rounded-md text-[13px] font-medium transition-colors',
                                (props.activePage === tab.id || (props.activePage === 'authorization' && tab.id === 'authz.roles')) ? 'bg-[#EFF6FF] dark:bg-blue-500/15 text-[#2563EB] dark:text-blue-400' : 'text-gray-600 dark:text-zinc-400 hover:bg-gray-100 dark:hover:bg-zinc-800 hover:text-gray-900 dark:hover:text-zinc-100'
                            ]"
                        >
                            {{ tab.label }}
                        </button>
                    </div>

                    <h3 class="text-[11px] font-semibold text-gray-400 dark:text-zinc-500 uppercase tracking-wider mb-2 px-4">Related</h3>
                    <div class="space-y-0.5 px-1">
                        <button
                            v-for="tab in authzRelated" :key="tab.id"
                            @click="emit('navigate', tab.id); emit('close')"
                            class="w-full text-left px-3 py-1.5 rounded-md text-[13px] font-medium text-gray-600 dark:text-zinc-400 hover:bg-gray-100 dark:hover:bg-zinc-800 hover:text-gray-900 dark:hover:text-zinc-100 transition-colors"
                        >
                            {{ tab.label }}
                        </button>
                    </div>
                </template>
                <template v-else>
                    <div v-for="(group, gi) in navGroups" :key="gi">
                        <SidebarGroup :label="group.label">
                            <SidebarItem
                                v-for="item in group.items"
                                :key="item.id"
                                :id="item.id"
                                :label="item.label"
                                :icon="item.icon"
                                :badge="item.badge"
                                :has-submenu="item.hasSubmenu"
                                :active="isActive(item.id)"
                                @navigate="emit('navigate', $event)"
                            />
                        </SidebarGroup>
                    </div>
                </template>
            </div>
        </nav>

        <!-- ── Bottom pinned: Notifications + Settings ── -->
        <div
            class="shrink-0 border-t border-[#e5e7eb] dark:border-zinc-800 transition-all duration-300 px-2 py-1 relative"
            @mouseleave="handleMouseLeaveBottom"
        >
            <!-- Floating Highlighter (smooth animated hover) -->
            <div
                class="wos-sidebar-highlighter absolute left-2 right-2 rounded-md transition-all duration-200 ease-out pointer-events-none z-0"
                :style="{
                    top: hoverStyleBottom.top,
                    height: hoverStyleBottom.height,
                    opacity: hoverStyleBottom.opacity,
                }"
            ></div>

            <ul class="space-y-0.5 relative z-10" role="list">
                <SidebarItem
                    v-for="item in bottomNavItems"
                    :key="item.id"
                    :id="item.id"
                    :label="item.label"
                    :icon="item.icon"
                    :badge="item.badge"
                    :active="isActive(item.id)"
                    :collapsed="collapsed"
                    @navigate="emit('navigate', $event)"
                    @mouseenter="handleMouseEnterBottom"
                />
                <li>
                    <Link
                        href="/dashboard"
                        class="relative w-full flex items-center rounded-md transition-all duration-300 select-none group text-[#374151] dark:text-zinc-300 hover:text-[#111827] dark:hover:text-zinc-100 font-normal h-9 pl-[11px] pr-2 z-10"
                        @mouseenter="handleMouseEnterBottom"
                    >
                        <LayoutGrid class="w-[18px] h-[18px] shrink-0 text-[#6b7280] dark:text-zinc-500 group-hover:text-[#374151] dark:group-hover:text-zinc-300" />
                        <span
                            class="transition-all duration-300 ease-in-out text-left truncate leading-none py-px overflow-hidden"
                            :class="collapsed ? 'opacity-0 max-w-0 ml-0 pointer-events-none' : 'opacity-100 max-w-[180px] ml-2.5'"
                        >Portal Modules</span>
                    </Link>
                </li>
            </ul>
        </div>

        <!-- Theme Toggle -->
        <div class="shrink-0 border-t border-[#e5e7eb] dark:border-zinc-800 px-4 py-2.5">
            <!-- Expanded: Theme Toggle with text -->
            <div v-if="!collapsed" class="flex items-center justify-between">
                <span class="text-[12px] font-semibold text-gray-500 dark:text-zinc-400">Mode Tampilan</span>
                <ThemeTogglerButton
                    v-model="activeTheme"
                    variant="ghost"
                    size="default"
                    direction="ltr"
                    :modes="['light', 'dark']"
                />
            </div>
            <!-- Collapsed: Centered icon only -->
            <div v-else class="flex justify-center">
                <ThemeTogglerButton
                    v-model="activeTheme"
                    variant="ghost"
                    size="default"
                    direction="ltr"
                    :modes="['light', 'dark']"
                />
            </div>
        </div>

        <!-- ── User footer ── -->
        <div class="shrink-0 border-t border-[#e5e7eb] dark:border-zinc-800 p-2">
            <button
                class="w-full flex items-center rounded-md hover:bg-[#f9fafb] dark:hover:bg-zinc-800 dark:bg-zinc-900 transition-all duration-300 text-left group pl-[6px] pr-2 py-2"
                aria-label="User menu"
            >
                <div
                    class="w-7 h-7 rounded-full flex items-center justify-center text-white text-[11px] font-bold shrink-0 overflow-hidden animate-none"
                    style="background-color: #2563EB"
                    aria-hidden="true"
                >
                    <img v-if="authUser?.avatar" :src="authUser.avatar" :alt="userName" class="w-full h-full object-cover animate-none" />
                    <span v-else>{{ userInitial }}</span>
                </div>
                <div
                    class="flex-1 min-w-0 transition-all duration-300 flex items-center justify-between overflow-hidden"
                    :class="collapsed ? 'opacity-0 max-w-0 ml-0 pointer-events-none' : 'opacity-100 max-w-[180px] ml-2.5'"
                >
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-medium text-[#111827] dark:text-zinc-100 truncate leading-snug">{{ userName }}</p>
                        <p class="text-[11.5px] text-[#9ca3af] dark:text-zinc-500 truncate leading-snug">{{ userEmail }}</p>
                    </div>
                    <svg
                        class="w-3.5 h-3.5 text-[#d1d5db] group-hover:text-[#9ca3af] dark:text-zinc-500 transition-colors shrink-0 ml-1.5"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                    </svg>
                </div>
            </button>
        </div>
    </aside>
</template>
