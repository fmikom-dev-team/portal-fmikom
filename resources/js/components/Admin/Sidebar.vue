<script setup lang="ts">
// biome-ignore-all lint/correctness/noUnusedImports: used in template
import { Link, usePage } from "@inertiajs/vue3";
import { useAppearance } from "@/composables/useAppearance";
import {
	AlertTriangle,
	BarChart3,
	ChevronLeft,
	ChevronRight,
	Eye,
	Flag,
	Globe,
	ImageIcon,
	Layers,
	LayoutDashboard,
	LineChart,
	LogOut,
	Moon,
	Settings,
	ShieldAlert,
	Sun,
	Users,
	X,
} from "lucide-vue-next";
import { computed } from "vue";
import { ThemeTogglerButton } from "@/components/animate-ui/components/buttons/theme-toggler";

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

const activeTheme = computed({
	get: () => appearance.value === "system" ? resolvedAppearance.value : appearance.value,
	set: (val) => {
		updateAppearance(val);
	}
});

const isActive = (path: string) => page.url.startsWith(path);
const isExact = (path: string) => page.url === path;

const navGroups = computed(() => [
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
		label: "Moderasi",
		items: [
			{
				label: "Tinjauan Konten",
				icon: Eye,
				href: "/pagi/admin/moderation",
				active: isActive("/pagi/admin/moderation"),
				badge: 12,
				badgeColor: "rose",
			},
			{
				label: "Laporan Masuk",
				icon: Flag,
				href: "/pagi/admin/reports",
				active: isActive("/pagi/admin/reports"),
				badge: 8,
				badgeColor: "amber",
			},
			{
				label: "Peringatan",
				icon: AlertTriangle,
				href: "/pagi/admin/warnings",
				active: isActive("/pagi/admin/warnings"),
			},
			{
				label: "Takedown",
				icon: ShieldAlert,
				href: "/pagi/admin/takedowns",
				active: isActive("/pagi/admin/takedowns"),
			},
		],
	},
	{
		label: "Konten",
		items: [
			{
				label: "Daftar Karya",
				icon: ImageIcon,
				href: "/pagi/admin/works",
				active: isActive("/pagi/admin/works"),
			},
			{
				label: "Tags & Kategori",
				icon: Globe,
				href: "/pagi/admin/tags",
				active: isActive("/pagi/admin/tags"),
			},
		],
	},
	{
		label: "Pengguna",
		items: [
			{
				label: "Daftar Pengguna",
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
				label: "Pengaturan",
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
};
</script>

<template>
    <!-- Sidebar Container -->
    <aside
        :class="[
            'fixed inset-y-0 left-0 z-40 flex flex-col bg-white dark:bg-zinc-950',
            'border-r border-slate-100 dark:border-zinc-800',
            'transition-all duration-300 ease-in-out overflow-hidden',
            collapsed ? 'w-[72px]' : 'w-[240px]',
            mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
        ]"
    >
        <!-- Logo Row -->
        <div class="flex items-center justify-between px-4 h-[64px] shrink-0 border-b border-slate-100 dark:border-zinc-800">
            <div class="flex items-center gap-2.5 overflow-hidden">
                <!-- Brand Mark -->
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-indigo-600 shadow-sm shadow-indigo-200 dark:shadow-none">
                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 21l6.75-6.75 1.5 1.5L3 21zM16.5 3.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                    </svg>
                </div>
                <div
                    class="overflow-hidden transition-all duration-300 ease-in-out"
                    :class="collapsed ? 'w-0 opacity-0' : 'w-auto opacity-100'"
                >
                    <span class="block whitespace-nowrap text-[15px] font-black tracking-tight text-slate-900 dark:text-white">KaryaKampus</span>
                    <span class="block whitespace-nowrap text-[10px] font-bold text-slate-400 tracking-widest uppercase leading-none">Admin</span>
                </div>
            </div>

            <!-- Desktop collapse toggle -->
            <button
                @click="emit('update:collapsed', !collapsed)"
                class="hidden lg:flex items-center justify-center h-7 w-7 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors shrink-0"
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
        <div class="flex-1 overflow-y-auto overflow-x-hidden py-3 space-y-0.5" style="scrollbar-width: thin;">
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
                <nav class="px-2.5 flex flex-col gap-0.5">
                    <Link
                        v-for="item in group.items"
                        :key="item.href"
                        :href="item.href"
                        :title="collapsed ? item.label : undefined"
                        :class="[
                            'flex items-center gap-3 h-10 rounded-xl text-[13px] font-semibold transition-all duration-200 group relative',
                            collapsed ? 'justify-center px-0' : 'px-3.5',
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
                            class="pointer-events-none absolute left-full ml-3 z-50 rounded-lg bg-zinc-900 dark:bg-zinc-700 text-white text-xs font-semibold px-2.5 py-1.5 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-150 shadow-lg"
                        >
                            {{ item.label }}
                            <span v-if="item.badge" class="ml-1.5 rounded-full bg-white/20 px-1.5 py-0.5 text-[9px] font-black">
                                {{ item.badge }}
                            </span>
                        </div>
                    </Link>
                </nav>
            </div>
        </div>

        <!-- Footer -->
        <div class="shrink-0 border-t border-slate-100 dark:border-zinc-800 p-3">
            <!-- Theme Toggle -->
            <div v-if="!collapsed" class="flex items-center justify-between px-1.5 mb-2.5">
                <span class="text-[12px] font-semibold text-slate-500 dark:text-zinc-400">Mode Tampilan</span>
                <ThemeTogglerButton
                    v-model="activeTheme"
                    variant="ghost"
                    size="default"
                    direction="ltr"
                    :modes="['light', 'dark']"
                />
            </div>
            <div v-else class="flex justify-center mb-2">
                <ThemeTogglerButton
                    v-model="activeTheme"
                    variant="ghost"
                    size="default"
                    direction="ltr"
                    :modes="['light', 'dark']"
                />
            </div>

            <!-- Back to portal SSO (clear module session) -->
            <Link
                href="/dashboard"
                :class="[
                    'flex items-center gap-2.5 rounded-xl h-9 text-[12px] font-bold text-slate-400 dark:text-zinc-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all',
                    collapsed ? 'justify-center px-0' : 'px-3',
                ]"
                :title="collapsed ? 'Portal SSO' : undefined"
            >
                <LogOut class="h-3.5 w-3.5 shrink-0" />
                <span v-if="!collapsed" class="truncate">Portal SSO</span>
            </Link>
        </div>
    </aside>
</template>
