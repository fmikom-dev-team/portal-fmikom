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
const userInitial = computed(() =>
	(authUser.value?.name ?? "A").charAt(0).toUpperCase(),
);
const userName = computed(() => authUser.value?.name ?? "Admin");
const userEmail = computed(() => authUser.value?.email ?? "");

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
</script>

<template>
    <!--
      Sidebar:
      - Desktop: always visible, collapsible (212px ↔ 52px)
      - Mobile: slide-in drawer over content (212px)
    -->
    <aside
        :class="[
            'wos-sidebar flex flex-col bg-white border-r border-[#e5e7eb] shrink-0',
            'transition-all duration-300 ease-out will-change-transform',
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
            class="flex items-center border-b border-[#e5e7eb] shrink-0 overflow-hidden"
            :class="collapsed ? 'justify-center px-0' : 'gap-2.5 px-3.5'"
            style="height: 52px"
        >
            <!-- Logo mark -->
            <div
                class="w-[30px] h-[30px] rounded-[7px] flex items-center justify-center shrink-0 cursor-pointer"
                style="background-color: #2563EB"
                @click="collapsed ? emit('toggle-collapse') : null"
                :title="collapsed ? 'Expand sidebar' : ''"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="color: #B6FF00">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>

            <!-- Brand name + controls (hidden when collapsed) -->
            <template v-if="!collapsed">
                <span class="text-[15px] font-semibold text-[#111827] tracking-tight flex-1 truncate">WorkOS</span>

                <!-- Desktop collapse toggle -->
                <button
                    class="hidden md:flex items-center justify-center w-7 h-7 rounded text-[#9ca3af] hover:text-[#374151] hover:bg-[#f3f4f6] transition-colors shrink-0"
                    aria-label="Collapse sidebar"
                    @click="emit('toggle-collapse')"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </button>

                <!-- Mobile close -->
                <button
                    class="md:hidden flex items-center justify-center w-7 h-7 rounded text-[#9ca3af] hover:text-[#374151] transition-colors shrink-0"
                    @click="emit('close')"
                    aria-label="Close sidebar"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </template>
        </div>

        <!-- ── Scrollable navigation ── -->
        <nav
            class="flex-1 overflow-y-auto wos-scroll py-2 overflow-x-hidden"
            :class="collapsed ? 'px-2 space-y-1' : 'px-2.5 space-y-4'"
            role="navigation"
        >
            <!-- Collapsed: flat icon buttons only -->
            <template v-if="collapsed">
                <template v-for="group in navGroups" :key="group.label">
                    <div class="space-y-0.5">
                        <button
                            v-for="item in group.items"
                            :key="item.id"
                            :title="item.label"
                            :class="[
                                'relative w-full flex items-center justify-center h-9 rounded-md transition-colors duration-150',
                                isActive(item.id)
                                    ? 'bg-[#EFF6FF] text-[#2563EB]'
                                    : 'text-[#6b7280] hover:bg-[#f3f4f6] hover:text-[#374151]',
                            ]"
                            @click="emit('navigate', item.id)"
                        >
                            <svg class="w-[18px] h-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                            </svg>
                            <!-- Badge -->
                            <span
                                v-if="item.badge && item.badge > 0"
                                class="absolute top-0.5 right-0.5 w-3.5 h-3.5 rounded-full bg-[#f59e0b] text-white text-[8px] font-bold flex items-center justify-center"
                            >
                                {{ item.badge > 9 ? '9+' : item.badge }}
                            </span>
                        </button>
                    </div>
                </template>
            </template>

            <!-- Expanded: full nav groups with labels -->
            <template v-else>
                
                <!-- Desktop: Always show main nav -->
                <div class="hidden md:block">
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
                </div>

                <!-- Mobile: Show auth sub-menu OR main nav -->
                <div class="md:hidden">
                    <template v-if="isAuthMenu">
                        <div class="px-2 mb-4">
                            <button @click="forceMainMenu = true" class="flex items-center text-[13px] font-medium text-gray-500 hover:text-gray-900 mb-4 transition-colors">
                                <ChevronLeft class="w-4 h-4 mr-1" /> Back
                            </button>
                            <h2 class="text-[14px] font-semibold text-gray-900 mb-2 px-1">Authentication</h2>
                        </div>
                        
                        <div class="space-y-0.5 mb-6 px-1">
                            <button
                                v-for="tab in authTabs" :key="tab.id"
                                @click="emit('navigate', tab.id); emit('close')"
                                :class="[
                                    'w-full text-left px-3 py-1.5 rounded-md text-[13px] font-medium transition-colors',
                                    (props.activePage === tab.id || (props.activePage === 'authentication' && tab.id === 'auth.analytics')) ? 'bg-[#EFF6FF] text-[#2563EB]' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
                                ]"
                            >
                                {{ tab.label }}
                            </button>
                        </div>

                        <h3 class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2 px-4">Related</h3>
                        <div class="space-y-0.5 px-1">
                            <button
                                v-for="tab in authRelated" :key="tab.id"
                                @click="emit('navigate', tab.id); emit('close')"
                                class="w-full text-left px-3 py-1.5 rounded-md text-[13px] font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors"
                            >
                                {{ tab.label }}
                            </button>
                        </div>
                    </template>
                    <template v-else-if="isAuthzMenu">
                        <div class="px-2 mb-4">
                            <button @click="forceMainMenu = true" class="flex items-center text-[13px] font-medium text-gray-500 hover:text-gray-900 mb-4 transition-colors">
                                <ChevronLeft class="w-4 h-4 mr-1" /> Back
                            </button>
                            <h2 class="text-[14px] font-semibold text-gray-900 mb-2 px-1">Authorization</h2>
                        </div>
                        
                        <div class="space-y-0.5 mb-6 px-1">
                            <button
                                v-for="tab in authzTabs" :key="tab.id"
                                @click="emit('navigate', tab.id); emit('close')"
                                :class="[
                                    'w-full text-left px-3 py-1.5 rounded-md text-[13px] font-medium transition-colors',
                                    (props.activePage === tab.id || (props.activePage === 'authorization' && tab.id === 'authz.roles')) ? 'bg-[#EFF6FF] text-[#2563EB]' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
                                ]"
                            >
                                {{ tab.label }}
                            </button>
                        </div>

                        <h3 class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2 px-4">Related</h3>
                        <div class="space-y-0.5 px-1">
                            <button
                                v-for="tab in authzRelated" :key="tab.id"
                                @click="emit('navigate', tab.id); emit('close')"
                                class="w-full text-left px-3 py-1.5 rounded-md text-[13px] font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors"
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

            </template>
        </nav>

        <!-- ── Bottom pinned: Notifications + Settings ── -->
        <div
            class="shrink-0 border-t border-[#e5e7eb]"
            :class="collapsed ? 'px-2 py-1 space-y-0.5' : 'px-2.5 py-1'"
        >
            <!-- Collapsed icon buttons -->
            <template v-if="collapsed">
                <button
                    v-for="item in bottomNavItems"
                    :key="item.id"
                    :title="item.label"
                    :class="[
                        'w-full flex items-center justify-center h-9 rounded-md transition-colors',
                        isActive(item.id)
                            ? 'bg-[#EFF6FF] text-[#2563EB]'
                            : 'text-[#6b7280] hover:bg-[#f3f4f6] hover:text-[#374151]',
                    ]"
                    @click="emit('navigate', item.id)"
                >
                    <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                    </svg>
                </button>
                <Link
                    href="/dashboard"
                    title="Portal Modules"
                    class="w-full flex items-center justify-center h-9 rounded-md transition-colors text-[#6b7280] hover:bg-[#f3f4f6] hover:text-[#374151]"
                >
                    <LayoutGrid class="w-[18px] h-[18px] shrink-0" />
                </Link>
            </template>
            <!-- Expanded full items -->
            <template v-else>
                <ul class="space-y-0.5">
                    <SidebarItem
                        v-for="item in bottomNavItems"
                        :key="item.id"
                        :id="item.id"
                        :label="item.label"
                        :icon="item.icon"
                        :active="isActive(item.id)"
                        @navigate="emit('navigate', $event)"
                    />
                    <li>
                        <Link
                            href="/dashboard"
                            class="relative w-full flex items-center gap-2.5 px-3 py-[7px] rounded-md text-[13.5px] transition-colors duration-150 select-none group text-[#374151] hover:bg-[#f3f4f6] hover:text-[#111827] font-normal"
                        >
                            <LayoutGrid class="w-[18px] h-[18px] shrink-0 text-[#6b7280] group-hover:text-[#374151]" />
                            <span class="flex-1 text-left truncate leading-none py-px">Portal Modules</span>
                        </Link>
                    </li>
                </ul>
            </template>
        </div>

        <!-- ── User footer ── -->
        <div class="shrink-0 border-t border-[#e5e7eb] p-2">
            <button
                class="w-full flex items-center rounded-md hover:bg-[#f9fafb] transition-colors text-left group"
                :class="collapsed ? 'justify-center p-1' : 'gap-2.5 px-2.5 py-2'"
                aria-label="User menu"
            >
                <div
                    class="w-7 h-7 rounded-full flex items-center justify-center text-white text-[11px] font-bold shrink-0 overflow-hidden"
                    style="background-color: #2563EB"
                    aria-hidden="true"
                >
                    <img v-if="authUser?.avatar" :src="authUser.avatar" :alt="userName" class="w-full h-full object-cover" />
                    <span v-else>{{ userInitial }}</span>
                </div>
                <template v-if="!collapsed">
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-medium text-[#111827] truncate leading-snug">{{ userName }}</p>
                        <p class="text-[11.5px] text-[#9ca3af] truncate leading-snug">{{ userEmail }}</p>
                    </div>
                    <svg
                        class="w-3.5 h-3.5 text-[#d1d5db] group-hover:text-[#9ca3af] transition-colors shrink-0"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                    </svg>
                </template>
            </button>
        </div>
    </aside>
</template>
