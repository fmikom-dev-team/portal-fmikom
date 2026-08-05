<script setup lang="ts">
// biome-ignore-all lint/correctness/noUnusedImports: used in template
import { Link, router, usePage } from "@inertiajs/vue3";
import { useAppearance } from "@/composables/useAppearance";
import {
	ChevronDown,
	ChevronLeft,
	ChevronRight,
	ChevronsUpDown,
	FileText,
	FolderOpen,
	Image as ImageIcon,
	Layers,
	LayoutDashboard,
	LayoutGrid,
	LineChart,
	LogOut,
	Settings,
	ShieldAlert,
	Sparkles,
	Users,
	X,
} from "lucide-vue-next";
import { computed, ref, watch, type Component } from "vue";
import { ThemeTogglerButton } from "@/components/animate-ui/components/buttons/theme-toggler";

interface SubNavItem {
	label: string;
	href: string;
	active: boolean;
	badge?: number | string | null;
	badgeColor?: string;
}

interface NavItem {
	label: string;
	icon: Component;
	href?: string;
	active: boolean;
	badge?: number | string | null;
	badgeColor?: string;
	children?: SubNavItem[];
}

interface NavGroup {
	label: string;
	items: NavItem[];
}

defineProps<{
	collapsed: boolean;
	mobileOpen: boolean;
}>();

const emit = defineEmits<{
	"update:collapsed": [value: boolean];
	"update:mobileOpen": [value: boolean];
}>();

const page = usePage();
const { appearance, resolvedAppearance, updateAppearance } = useAppearance();

const siteSettings = computed(() => (page.props as any).siteSettings || {});
const pagiCounts = computed(() => (page.props as any).pagi_moderation_counts || null);

const authUser = computed(() => (page.props as any).auth?.user);
const userName = computed(() => authUser.value?.name || "User Admin");
const userEmail = computed(() => authUser.value?.email || "admin@fmikom.org");
const userInitial = computed(() => userName.value.charAt(0).toUpperCase());
const userMenuOpen = ref(false);

const logout = () => {
	router.post("/logout");
};

const activeTheme = computed({
	get: () => appearance.value === "system" ? resolvedAppearance.value : appearance.value,
	set: (val) => {
		updateAppearance(val);
	}
});

const isActive = (path: string) => page.url.startsWith(path);
const isExact = (path: string) => page.url === path;

const isModerationChildActive = computed(() => {
	return (
		isActive("/pagi/admin/reports") ||
		isActive("/pagi/admin/takedowns") ||
		isActive("/pagi/admin/warnings")
	);
});

const isModerationOpen = ref(isModerationChildActive.value);

watch(
	() => page.url,
	() => {
		isModerationOpen.value = isModerationChildActive.value;
	}
);

const navGroups = computed<NavGroup[]>(() => [
	{
		label: "Utama",
		items: [
			{
				label: "Dashboard",
				icon: LayoutDashboard,
				href: "/pagi/admin",
				active: isExact("/pagi/admin") || isExact("/pagi/admin/"),
			},
			{
				label: "Statistik",
				icon: LineChart,
				href: "/pagi/admin/analytics",
				active: isActive("/pagi/admin/analytics"),
			},
		],
	},
	{
		label: "Showcase & Exhibition",
		items: [
			{
				label: "Galeri Karya Publik",
				icon: FolderOpen,
				href: "/pagi/admin/works",
				active: isActive("/pagi/admin/works"),
			},
			{
				label: "Karya Terbaik",
				icon: Sparkles,
				href: "/pagi/admin/showcase",
				active: isActive("/pagi/admin/showcase"),
			},
		],
	},
	{
		label: "Moderasi & Laporan",
		items: [
			{
				label: "Pusat Moderasi",
				icon: ShieldAlert,
				active: isModerationChildActive.value,
				badge: pagiCounts.value && pagiCounts.value.reports > 0 ? pagiCounts.value.reports : null,
				badgeColor: "rose",
				children: [
					{
						label: "Laporan & Moderasi",
						href: "/pagi/admin/reports",
						active: (isExact("/pagi/admin/reports") || isExact("/pagi/admin/reports/")) && !isActive("/pagi/admin/reports/archive"),
						badge: pagiCounts.value && pagiCounts.value.reports > 0 ? pagiCounts.value.reports : null,
						badgeColor: "rose",
					},
					{
						label: "Takedown",
						href: "/pagi/admin/takedowns",
						active: isActive("/pagi/admin/takedowns"),
						badge: pagiCounts.value?.takedowns && pagiCounts.value.takedowns > 0 ? pagiCounts.value.takedowns : null,
						badgeColor: "orange",
					},
					{
						label: "Akun Peringatan",
						href: "/pagi/admin/warnings",
						active: isActive("/pagi/admin/warnings"),
						badge: pagiCounts.value?.warnings && pagiCounts.value.warnings > 0 ? pagiCounts.value.warnings : null,
						badgeColor: "orange",
					},
					{
						label: "Arsip",
						href: "/pagi/admin/reports/archive",
						active: isActive("/pagi/admin/reports/archive"),
						badge: null,
						badgeColor: "emerald",
					},
				],
			},
			{
				label: "Kamus Kata Teks",
				icon: FileText,
				href: "/pagi/admin/text-dictionary",
				active: isActive("/pagi/admin/text-dictionary"),
			},
			{
				label: "Kamus Gambar Visual",
				icon: ImageIcon,
				href: "/pagi/admin/image-dictionary",
				active: isActive("/pagi/admin/image-dictionary"),
			},
		],
	},
	{
		label: "Pengelolaan",
		items: [
			{
				label: "Manajemen Pengguna",
				icon: Users,
				href: "/pagi/admin/users",
				active: isActive("/pagi/admin/users"),
			},
		],
	},
	{
		label: "Sistem",
		items: [
			{
				label: "Pengaturan Modul",
				icon: Settings,
				href: "/pagi/admin/settings",
				active: isActive("/pagi/admin/settings"),
			},
		],
	},
]);

const badgeClasses: Record<string, string> = {
	rose: "bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-400",
	amber: "bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400",
	orange: "bg-orange-100 text-orange-600 dark:bg-orange-900/40 dark:text-orange-400",
	slate: "bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400",
};
</script>

<template>
    <!-- Sidebar Container -->
    <aside
        :class="[
            'fixed inset-y-0 left-0 z-40 flex flex-col bg-white dark:bg-zinc-950',
            'border-r border-slate-100 dark:border-zinc-800',
            'transition-all duration-300 ease-in-out',
            collapsed ? 'w-[72px] overflow-visible' : 'w-[240px] overflow-hidden',
            mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
        ]"
    >
        <!-- Logo Row -->
        <div
            class="flex items-center h-[64px] shrink-0 border-b border-slate-100 dark:border-zinc-800 transition-all duration-300 relative"
            :class="collapsed ? 'justify-center px-0' : 'justify-between px-4'"
        >
            <div class="flex items-center overflow-hidden transition-all duration-300" :class="collapsed ? 'justify-center w-full' : 'gap-2.5'">
                <!-- Brand Mark -->
                <div 
                    class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 overflow-hidden transition-all duration-200"
                    :class="siteSettings.brand_logo ? 'bg-transparent border-0 p-0 shadow-none' : 'bg-indigo-600 shadow-sm shadow-indigo-200 dark:shadow-none'"
                >
                    <img v-if="siteSettings.brand_logo" :src="siteSettings.brand_logo" alt="Brand Logo" class="w-full h-full object-contain" />
                    <svg v-else class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 21l6.75-6.75 1.5 1.5L3 21zM16.5 3.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                    </svg>
                </div>
                <div
                    class="overflow-hidden transition-all duration-300 ease-in-out"
                    :class="collapsed ? 'w-0 opacity-0' : 'w-auto opacity-100'"
                >
                    <span class="block whitespace-nowrap text-[14px] font-black tracking-tight text-slate-900 dark:text-white truncate">
                        {{ siteSettings.brand_name || 'Portal FMIKOM' }}
                    </span>
                    <span class="block whitespace-nowrap text-[9px] font-bold text-slate-400 tracking-widest uppercase leading-none truncate mt-0.5">
                        {{ siteSettings.brand_subtitle || 'PAGI Admin' }}
                    </span>
                </div>
            </div>

            <!-- Desktop collapse toggle -->
            <button
                @click="emit('update:collapsed', !collapsed)"
                :class="[
                    'hidden lg:flex items-center justify-center h-7 w-7 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors shrink-0',
                    collapsed ? 'absolute -right-3.5 top-4.5 bg-white dark:bg-zinc-950 border border-slate-100 dark:border-zinc-800 shadow-md z-50' : ''
                ]"
            >
                <ChevronLeft v-if="!collapsed" class="h-3.5 w-3.5" />
                <ChevronRight v-else class="h-3.5 w-3.5" />
            </button>

            <!-- Mobile close -->
            <button
                @click="emit('update:mobileOpen', false)"
                class="lg:hidden flex items-center justify-center h-7 w-7 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 shrink-0"
            >
                <X class="h-3.5 w-3.5" />
            </button>
        </div>

        <!-- Scrollable Navigation -->
        <div 
            class="flex-1 overflow-y-auto py-3 space-y-0.5" 
            :class="collapsed ? 'overflow-x-visible' : 'overflow-x-hidden'"
            style="scrollbar-width: thin;"
        >
            <div
                v-for="group in navGroups"
                :key="group.label"
                class="mb-1"
            >
                <!-- Group Label -->
                <div
                    class="px-4 mb-1.5 overflow-hidden transition-all duration-200"
                    :class="collapsed ? 'h-0 opacity-0 mb-0 mt-3' : 'h-auto opacity-100'"
                >
                    <span class="text-[10px] font-black text-slate-400 dark:text-zinc-500 tracking-widest uppercase">
                        {{ group.label }}
                    </span>
                </div>

                <!-- Divider when collapsed -->
                <div
                    v-if="collapsed"
                    class="mx-3 my-2 h-px bg-slate-100 dark:bg-zinc-800"
                />

                <!-- Nav Items -->
                <nav :class="collapsed ? 'px-0 flex flex-col items-center gap-1.5' : 'px-2.5 flex flex-col gap-0.5'">
                    <template v-for="item in group.items" :key="item.label">
                        <!-- Case 1: Item with Sub-menu Children -->
                        <div v-if="item.children && item.children.length > 0" :class="collapsed ? 'w-full flex justify-center' : 'w-full'">
                            <!-- Parent Trigger Button (Expanded View) -->
                            <button
                                v-if="!collapsed"
                                type="button"
                                @click="isModerationOpen = !isModerationOpen"
                                :class="[
                                    'flex items-center justify-between w-full h-10 px-3.5 rounded-xl text-[13px] font-semibold transition-all duration-200 group',
                                    item.active
                                        ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 font-bold'
                                        : 'text-slate-500 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 hover:text-slate-800 dark:hover:text-zinc-100',
                                ]"
                            >
                                <div class="flex items-center gap-3 min-w-0">
                                    <component
                                        :is="item.icon"
                                        :class="['h-[17px] w-[17px] shrink-0', item.active ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-zinc-500 group-hover:text-slate-600 dark:group-hover:text-zinc-200']"
                                    />
                                    <span class="truncate">{{ item.label }}</span>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0 ml-1">
                                    <span
                                        v-if="item.badge"
                                        :class="[
                                            'rounded-full px-1.5 py-0.5 text-[9.5px] font-black leading-none',
                                            item.badgeColor ? badgeClasses[item.badgeColor] : 'bg-slate-100 text-slate-500'
                                        ]"
                                    >
                                        {{ item.badge }}
                                    </span>
                                    <ChevronDown
                                        :class="['h-3.5 w-3.5 text-slate-400 transition-transform duration-200', isModerationOpen ? 'rotate-180' : '']"
                                    />
                                </div>
                            </button>

                            <!-- Parent Trigger Link (Collapsed View) -->
                            <Link
                                v-else
                                :href="item.children[0].href"
                                :class="[
                                    'flex items-center justify-center w-10 h-10 rounded-xl text-[13px] font-semibold transition-all duration-200 group relative',
                                    item.active
                                        ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200 dark:shadow-none'
                                        : 'text-slate-500 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 hover:text-slate-800 dark:hover:text-zinc-100',
                                ]"
                            >
                                <component
                                    :is="item.icon"
                                    :class="['h-[17px] w-[17px] shrink-0', item.active ? 'text-white' : 'text-slate-400 dark:text-zinc-500 group-hover:text-slate-600 dark:group-hover:text-zinc-200']"
                                />
                                <div
                                    class="pointer-events-none fixed left-[84px] z-[99999] rounded-xl bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-xs font-bold px-3 py-1.5 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-all duration-200 shadow-2xl flex items-center gap-1.5 transform translate-x-1 group-hover:translate-x-0"
                                >
                                    <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-900 dark:bg-slate-100 rotate-45"></div>
                                    <span class="relative z-10">{{ item.label }}</span>
                                </div>
                            </Link>

                            <!-- Sub-items List (Expanded View Only) -->
                            <div v-if="!collapsed && isModerationOpen" class="mt-0.5 ml-3 pl-3 border-l border-slate-200/60 dark:border-zinc-800 space-y-0.5 animate-fade-in">
                                <Link
                                    v-for="sub in item.children"
                                    :key="sub.href"
                                    :href="sub.href"
                                    :class="[
                                        'flex items-center justify-between h-8 px-2.5 rounded-lg text-[12px] font-semibold transition-all duration-150',
                                        sub.active
                                            ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200 dark:shadow-none font-bold'
                                            : 'text-slate-500 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 hover:text-slate-800 dark:hover:text-zinc-100',
                                    ]"
                                >
                                    <span class="truncate">{{ sub.label }}</span>
                                    <span
                                        v-if="sub.badge"
                                        :class="[
                                            'shrink-0 rounded-full px-1.5 py-0.5 text-[9px] font-black leading-none',
                                            sub.active ? 'bg-white/25 text-white' : (sub.badgeColor ? badgeClasses[sub.badgeColor] : 'bg-slate-100 text-slate-500')
                                        ]"
                                    >
                                        {{ sub.badge }}
                                    </span>
                                </Link>
                            </div>
                        </div>

                        <!-- Case 2: Standard Nav Item -->
                        <Link
                            v-else
                            :href="item.href || '#'"
                            :class="[
                                'flex items-center gap-3 h-10 rounded-xl text-[13px] font-semibold transition-all duration-200 group relative',
                                collapsed ? 'w-10 h-10 justify-center px-0' : 'w-full px-3.5',
                                item.active
                                    ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200 dark:shadow-none'
                                    : 'text-slate-500 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 hover:text-slate-800 dark:hover:text-zinc-100',
                            ]"
                        >
                            <component
                                :is="item.icon"
                                :class="['h-[17px] w-[17px] shrink-0', item.active ? 'text-white' : 'text-slate-400 dark:text-zinc-500 group-hover:text-slate-600 dark:group-hover:text-zinc-200']"
                            />
                            <span
                                v-if="!collapsed"
                                class="flex-1 truncate"
                            >{{ item.label }}</span>

                            <!-- Badge -->
                            <span
                                v-if="!collapsed && item.badge"
                                :class="[
                                    'ml-auto shrink-0 rounded-full px-1.5 py-0.5 text-[10px] font-black leading-none',
                                    item.active ? 'bg-white/20 text-white' : (item.badgeColor ? badgeClasses[item.badgeColor] : 'bg-slate-100 text-slate-500')
                                ]"
                            >
                                {{ item.badge }}
                            </span>

                            <!-- Tooltip for collapsed -->
                            <div
                                v-if="collapsed"
                                class="pointer-events-none fixed left-[84px] z-[99999] rounded-xl bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-xs font-bold px-3 py-1.5 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-all duration-200 shadow-2xl flex items-center gap-1.5 transform translate-x-1 group-hover:translate-x-0"
                            >
                                <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-900 dark:bg-slate-100 rotate-45"></div>
                                <span class="relative z-10">{{ item.label }}</span>
                                <span v-if="item.badge" class="ml-1 px-1.5 py-0.5 rounded-full bg-indigo-500 text-white text-[9.5px] font-black">
                                    {{ item.badge }}
                                </span>
                            </div>
                        </Link>
                    </template>
                </nav>
            </div>
        </div>

        <!-- ── Modern WorkOS-Style Sidebar Footer ── -->
        <!-- Theme Toggle Row -->
        <div class="shrink-0 border-t border-slate-100 dark:border-zinc-800 px-3.5 py-2.5">
            <div v-if="!collapsed" class="flex items-center justify-between">
                <span class="text-[12px] font-semibold text-slate-500 dark:text-zinc-400">Mode Tampilan</span>
                <ThemeTogglerButton
                    v-model="activeTheme"
                    variant="ghost"
                    size="default"
                    direction="ltr"
                    :modes="['light', 'dark']"
                />
            </div>
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

        <!-- User Profile Card Row (Matching WorkOS Gambar 3) -->
        <div class="relative shrink-0 border-t border-slate-100 dark:border-zinc-800 p-2">
            <button
                type="button"
                @click="userMenuOpen = !userMenuOpen"
                :class="[
                    'flex items-center rounded-xl hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all duration-200 text-left group w-full',
                    collapsed ? 'w-10 h-10 mx-auto justify-center px-0' : 'pl-1.5 pr-2 py-1.5'
                ]"
                aria-label="User menu"
            >
                <div
                    class="w-8 h-8 rounded-full flex items-center justify-center text-white text-[11px] font-bold shrink-0 overflow-hidden bg-[#2563EB]"
                    aria-hidden="true"
                >
                    <img v-if="authUser?.foto_path || authUser?.avatar" :src="authUser.foto_path || authUser.avatar" :alt="userName" class="w-full h-full object-cover" />
                    <span v-else>{{ userInitial }}</span>
                </div>

                <div
                    v-if="!collapsed"
                    class="flex-1 min-w-0 ml-2.5 flex items-center justify-between overflow-hidden"
                >
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-bold text-slate-900 dark:text-zinc-100 truncate leading-snug">{{ userName }}</p>
                        <p class="text-[11.5px] text-slate-400 dark:text-zinc-500 truncate leading-snug">{{ userEmail }}</p>
                    </div>
                    <ChevronsUpDown class="w-3.5 h-3.5 text-slate-300 group-hover:text-slate-500 dark:text-zinc-500 transition-colors shrink-0 ml-1.5" />
                </div>
            </button>

            <!-- Floating Tooltip when Collapsed -->
            <div 
                v-if="collapsed" 
                class="pointer-events-none fixed left-[84px] z-[99999] rounded-xl bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-xs font-bold px-3 py-1.5 whitespace-nowrap opacity-0 hover:opacity-100 transition-all duration-200 shadow-2xl flex items-center gap-1.5 transform translate-x-1"
            >
                <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-900 dark:bg-slate-100 rotate-45"></div>
                <span class="relative z-10">{{ userName }}</span>
            </div>

            <!-- User Dropdown Popover Menu -->
            <transition enter-active-class="transition duration-100 ease-out" enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100" leave-active-class="transition duration-75 ease-in" leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
                <div
                    v-if="userMenuOpen"
                    class="absolute bottom-full left-2 right-2 mb-2 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl shadow-xl p-1.5 z-50 text-xs font-medium space-y-0.5"
                >
                    <div class="px-2.5 py-2 border-b border-slate-100 dark:border-zinc-800 mb-1">
                        <p class="text-[12px] font-bold text-slate-900 dark:text-white truncate">{{ userName }}</p>
                        <p class="text-[10.5px] text-slate-400 dark:text-zinc-400 truncate">{{ userEmail }}</p>
                    </div>

                    <Link
                        href="/dashboard"
                        class="flex items-center gap-2 px-2.5 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-700 dark:text-zinc-200 transition-colors"
                        @click="userMenuOpen = false"
                    >
                        <LayoutGrid class="w-3.5 h-3.5 text-blue-600" />
                        <span>Portal SSO</span>
                    </Link>

                    <button
                        type="button"
                        @click="logout"
                        class="w-full flex items-center gap-2 px-2.5 py-2 rounded-xl hover:bg-red-50 dark:hover:bg-red-950/40 text-red-600 dark:text-red-400 transition-colors text-left"
                    >
                        <LogOut class="w-3.5 h-3.5" />
                        <span>Keluar (Logout)</span>
                    </button>
                </div>
            </transition>
        </div>
    </aside>
</template>
