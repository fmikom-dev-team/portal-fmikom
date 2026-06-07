<script setup lang="ts">
import type { PageProps } from "@inertiajs/core";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { useDark } from "@vueuse/core";
import {
	Bell,
	Calendar,
	CalendarDays,
	ChevronDown,
	ChevronLeft,
	ChevronRight,
	FileText,
	Folder,
	Image as ImageIcon,
	LayoutGrid,
	List,
	Menu,
	MessageCircle,
	Moon,
	PenTool,
	Search,
	Settings,
	Sun,
	User,
	X,
} from "lucide-vue-next";
import { computed, onMounted, onUnmounted, ref } from "vue";

interface CustomPageProps extends PageProps {
	auth: {
		user: any;
	};
	pending_comments_count?: number;
}

const props = defineProps({
	title: {
		type: String,
		default: "Admin Landingpage Portal",
	},
});

const page = usePage<CustomPageProps>();
const user = computed(() => page.props.auth?.user);
const firstName = computed(() => user.value?.name || "Admin");

const isDark = useDark({
	selector: "html",
	attribute: "class",
	valueDark: "dark",
	valueLight: "",
});

// Desktop: collapsed (icon-only) vs expanded
const sidebarCollapsed = ref(false);
// Mobile: open/close drawer
const mobileOpen = ref(false);

const profileDropdownOpen = ref(false);
const logout = () => {
	router.post("/logout");
};

const searchQuery = ref("");
const handleSearch = () => {
	const url = page.url;
	let target = "/portal-admin/posts";
	if (url.startsWith("/portal-admin/events")) {
		target = "/portal-admin/events";
	} else if (url.startsWith("/portal-admin/pages")) {
		target = "/portal-admin/pages";
	} else if (url.startsWith("/portal-admin/academic-calendars")) {
		target = "/portal-admin/academic-calendars";
	}
	router.get(target, { search: searchQuery.value }, { preserveState: true });
};

const searchPlaceholder = computed(() => {
	const url = page.url;
	if (url.startsWith("/portal-admin/posts")) return "Cari postingan...";
	if (url.startsWith("/portal-admin/events")) return "Cari event...";
	if (url.startsWith("/portal-admin/pages")) return "Cari halaman...";
	if (url.startsWith("/portal-admin/academic-calendars"))
		return "Cari agenda...";
	return "Cari sesuatu...";
});

const isPageLoading = ref(false);
let loadingTimeout: any = null;
let startListener: any = null;
let finishListener: any = null;

onMounted(() => {
	startListener = router.on("start", () => {
		loadingTimeout = setTimeout(() => {
			isPageLoading.value = true;
		}, 150); // Show skeleton after 150ms
	});
	finishListener = router.on("finish", () => {
		clearTimeout(loadingTimeout);
		isPageLoading.value = false;
	});
});

onUnmounted(() => {
	if (startListener) startListener();
	if (finishListener) finishListener();
	if (loadingTimeout) clearTimeout(loadingTimeout);
});
</script>

<template>
    <Head :title="title" />

    <div class="min-h-screen bg-white dark:bg-slate-900 font-sans selection:bg-[#2563EB] selection:text-white transition-colors duration-300">

        <!-- ===================== SIDEBAR (Fixed) ===================== -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-40 flex flex-col bg-white dark:bg-slate-900 border-r border-slate-100 dark:border-slate-800 transition-all duration-300 overflow-hidden',
                sidebarCollapsed ? 'w-[72px]' : 'w-[260px]',
                mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
            ]"
        >
            <!-- Logo Row -->
            <div class="flex items-center justify-between px-4 h-[68px] shrink-0 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2 overflow-hidden min-w-0">
                    <div class="grid grid-cols-3 gap-0.5 w-[22px] h-[22px] shrink-0">
                        <div class="bg-[#2563EB] rounded-full w-[6px] h-[6px]"></div>
                        <div class="bg-[#2563EB] rounded-full w-[6px] h-[6px]"></div>
                        <div class="bg-indigo-300 rounded-full w-[6px] h-[6px]"></div>
                        <div class="bg-[#2563EB] rounded-full w-[6px] h-[6px]"></div>
                        <div class="bg-indigo-300 rounded-full w-[6px] h-[6px]"></div>
                        <div class="bg-indigo-300 rounded-full w-[6px] h-[6px]"></div>
                        <div class="bg-indigo-300 rounded-full w-[6px] h-[6px]"></div>
                        <div class="bg-indigo-300 rounded-full w-[6px] h-[6px]"></div>
                        <div class="bg-indigo-300 rounded-full w-[6px] h-[6px]"></div>
                    </div>
                    <span
                        class="font-black text-[17px] tracking-wide text-slate-900 dark:text-white whitespace-nowrap transition-all duration-200 origin-left"
                        :class="sidebarCollapsed ? 'opacity-0 scale-x-0 w-0' : 'opacity-100 scale-x-100 ml-1'"
                    >MESQUITE</span>
                </div>

                <!-- Desktop: Collapse Toggle -->
                <button
                    @click="sidebarCollapsed = !sidebarCollapsed"
                    class="hidden lg:flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-[#2563EB] hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors shrink-0"
                >
                    <ChevronLeft v-if="!sidebarCollapsed" class="w-4 h-4" />
                    <ChevronRight v-else class="w-4 h-4" />
                </button>

                <!-- Mobile: Close Button -->
                <button
                    @click="mobileOpen = false"
                    class="lg:hidden flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 shrink-0"
                >
                    <X class="w-4 h-4" />
                </button>
            </div>

            <!-- Scrollable Nav -->
            <div class="flex-1 overflow-y-auto overflow-x-hidden py-4">

                <!-- MENU UTAMA label -->
                <div
                    class="px-4 mb-2 overflow-hidden transition-all duration-200"
                    :class="sidebarCollapsed ? 'h-0 opacity-0 mb-0' : 'h-auto opacity-100'"
                >
                    <span class="text-[10px] font-black text-slate-400 tracking-widest uppercase">MENU UTAMA</span>
                </div>

                <nav class="px-3 flex flex-col gap-0.5">
                    <!-- Dashboard -->
                    <Link
                        href="/portal-admin"
                        :class="[
                            $page.url === '/portal-admin'
                                ? 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100 font-bold'
                                : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-800 dark:hover:text-slate-200 font-medium',
                            'flex items-center gap-3 rounded-xl text-[13px] transition-all duration-150 h-10 px-4',
                            sidebarCollapsed ? 'justify-center px-0' : 'px-4'
                        ]"
                        :title="sidebarCollapsed ? 'Dashboard' : undefined"
                    >
                        <LayoutGrid class="w-[18px] h-[18px] shrink-0" />
                        <span v-if="!sidebarCollapsed" class="truncate">Dashboard</span>
                    </Link>

                    <!-- Posts -->
                    <Link
                        href="/portal-admin/posts"
                        :class="[
                            $page.url.startsWith('/portal-admin/posts')
                                ? 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100 font-bold'
                                : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-800 dark:hover:text-slate-200 font-medium',
                            'flex items-center gap-3 rounded-xl text-[13px] transition-all duration-150 h-10 px-4',
                            sidebarCollapsed ? 'justify-center px-0' : 'px-4'
                        ]"
                        :title="sidebarCollapsed ? 'Posts' : undefined"
                    >
                        <FileText class="w-[18px] h-[18px] shrink-0" />
                        <span v-if="!sidebarCollapsed" class="truncate">Posts</span>
                    </Link>

                    <!-- Categories -->
                    <Link
                        href="/portal-admin/categories"
                        :class="[
                            $page.url.startsWith('/portal-admin/categories')
                                ? 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100 font-bold'
                                : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-800 dark:hover:text-slate-200 font-medium',
                            'flex items-center gap-3 rounded-xl text-[13px] transition-all duration-150 h-10 px-4',
                            sidebarCollapsed ? 'justify-center px-0' : 'px-4'
                        ]"
                        :title="sidebarCollapsed ? 'Categories' : undefined"
                    >
                        <Folder class="w-[18px] h-[18px] shrink-0" />
                        <span v-if="!sidebarCollapsed" class="truncate">Categories</span>
                    </Link>

                    <!-- Media -->
                    <Link
                        href="/portal-admin/media"
                        :class="[
                            $page.url.startsWith('/portal-admin/media')
                                ? 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100 font-bold'
                                : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-800 dark:hover:text-slate-200 font-medium',
                            'flex items-center gap-3 rounded-xl text-[13px] transition-all duration-150 h-10 px-4',
                            sidebarCollapsed ? 'justify-center px-0' : 'px-4'
                        ]"
                        :title="sidebarCollapsed ? 'Media' : undefined"
                    >
                        <ImageIcon class="w-[18px] h-[18px] shrink-0" />
                        <span v-if="!sidebarCollapsed" class="truncate">Media</span>
                    </Link>

                    <!-- Pages -->
                    <Link
                        href="/portal-admin/pages"
                        :class="[
                            $page.url.startsWith('/portal-admin/pages')
                                ? 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100 font-bold'
                                : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-800 dark:hover:text-slate-200 font-medium',
                            'flex items-center gap-3 rounded-xl text-[13px] transition-all duration-150 h-10 px-4',
                            sidebarCollapsed ? 'justify-center px-0' : 'px-4'
                        ]"
                        :title="sidebarCollapsed ? 'Pages' : undefined"
                    >
                        <PenTool class="w-[18px] h-[18px] shrink-0" />
                        <span v-if="!sidebarCollapsed" class="truncate">Pages</span>
                    </Link>

                    <!-- Comments -->
                    <Link
                        href="/portal-admin/comments"
                        :class="[
                            $page.url.startsWith('/portal-admin/comments')
                                ? 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100 font-bold'
                                : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-800 dark:hover:text-slate-200 font-medium',
                            'flex items-center gap-3 rounded-xl text-[13px] transition-all duration-150 h-10 px-4',
                            sidebarCollapsed ? 'justify-center px-0' : 'px-4'
                        ]"
                        :title="sidebarCollapsed ? 'Comments' : undefined"
                    >
                        <MessageCircle class="w-[18px] h-[18px] shrink-0" />
                        <span v-if="!sidebarCollapsed" class="truncate">Comments</span>
                        <span
                            v-if="!sidebarCollapsed && (page.props.pending_comments_count ?? 0) > 0"
                            class="ml-auto bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-800"
                        >{{ page.props.pending_comments_count }}</span>
                    </Link>

                    <!-- Academic Calendar -->
                    <Link
                        href="/portal-admin/academic-calendars"
                        :class="[
                            $page.url.startsWith('/portal-admin/academic-calendars')
                                ? 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100 font-bold'
                                : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-800 dark:hover:text-slate-200 font-medium',
                            'flex items-center gap-3 rounded-xl text-[13px] transition-all duration-150 h-10 px-4',
                            sidebarCollapsed ? 'justify-center px-0' : 'px-4'
                        ]"
                        :title="sidebarCollapsed ? 'Kalender Akademik' : undefined"
                    >
                        <CalendarDays class="w-[18px] h-[18px] shrink-0" />
                        <span v-if="!sidebarCollapsed" class="truncate">Kalender Akademik</span>
                    </Link>

                    <!-- Events -->
                    <Link
                        href="/portal-admin/events"
                        :class="[
                            $page.url.startsWith('/portal-admin/events')
                                ? 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100 font-bold'
                                : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-800 dark:hover:text-slate-200 font-medium',
                            'flex items-center gap-3 rounded-xl text-[13px] transition-all duration-150 h-10 px-4',
                            sidebarCollapsed ? 'justify-center px-0' : 'px-4'
                        ]"
                        :title="sidebarCollapsed ? 'Event / Kegiatan' : undefined"
                    >
                        <Calendar class="w-[18px] h-[18px] shrink-0" />
                        <span v-if="!sidebarCollapsed" class="truncate">Event & Kegiatan</span>
                    </Link>
                </nav>

                <!-- SYSTEM Section -->
                <div class="mt-5">
                    <div
                        class="px-4 mb-2 overflow-hidden transition-all duration-200"
                        :class="sidebarCollapsed ? 'h-0 opacity-0 mb-0' : 'h-auto opacity-100'"
                    >
                        <span class="text-[10px] font-black text-slate-400 tracking-widest uppercase">SYSTEM</span>
                    </div>

                    <nav class="px-3 flex flex-col gap-0.5">
                        <!-- Menu Navigation -->
                        <Link
                            href="/portal-admin/menus"
                            :class="[
                                $page.url.startsWith('/portal-admin/menus')
                                    ? 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100 font-bold'
                                    : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-800 dark:hover:text-slate-200 font-medium',
                                'flex items-center gap-3 rounded-xl text-[13px] transition-all duration-150 h-10 px-4',
                                sidebarCollapsed ? 'justify-center px-0' : 'px-4'
                            ]"
                            :title="sidebarCollapsed ? 'Menu Navigation' : undefined"
                        >
                            <List class="w-[18px] h-[18px] shrink-0" />
                            <span v-if="!sidebarCollapsed" class="truncate">Menu Navigation</span>
                        </Link>
                        <!-- Tata Letak -->
                        <Link
                            href="/portal-admin/appearance"
                            :class="[
                                $page.url.startsWith('/portal-admin/appearance')
                                    ? 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100 font-bold'
                                    : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-800 dark:hover:text-slate-200 font-medium',
                                'flex items-center gap-3 rounded-xl text-[13px] transition-all duration-150 h-10 px-4',
                                sidebarCollapsed ? 'justify-center px-0' : 'px-4'
                            ]"
                            :title="sidebarCollapsed ? 'Tata Letak' : undefined"
                        >
                            <LayoutGrid class="w-[18px] h-[18px] shrink-0" />
                            <span v-if="!sidebarCollapsed" class="truncate">Tata Letak</span>
                        </Link>

                        <!-- Settings -->
                        <Link
                            href="/portal-admin/settings"
                            :class="[
                                $page.url.startsWith('/portal-admin/settings')
                                    ? 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100 font-bold'
                                    : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-800 dark:hover:text-slate-200 font-medium',
                                'flex items-center gap-3 rounded-xl text-[13px] transition-all duration-150 h-10 px-4',
                                sidebarCollapsed ? 'justify-center px-0' : 'px-4'
                            ]"
                            :title="sidebarCollapsed ? 'Settings' : undefined"
                        >
                            <Settings class="w-[18px] h-[18px] shrink-0 opacity-70" />
                            <span v-if="!sidebarCollapsed" class="truncate">Settings</span>
                        </Link>

                        <!-- Profile -->
                        <Link
                            href="/settings/profile"
                            :class="[
                                $page.url.startsWith('/settings/profile')
                                    ? 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100 font-bold'
                                    : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-800 dark:hover:text-slate-200 font-medium',
                                'flex items-center gap-3 rounded-xl text-[13px] transition-all duration-150 h-10 px-4',
                                sidebarCollapsed ? 'justify-center px-0' : 'px-4'
                            ]"
                            :title="sidebarCollapsed ? 'Profile' : undefined"
                        >
                            <User class="w-[18px] h-[18px] shrink-0 opacity-70" />
                            <span v-if="!sidebarCollapsed" class="truncate">Profile</span>
                        </Link>
                    </nav>
                </div>
            </div>

            <!-- Footer: Theme Toggle + Back to Portal -->
            <div class="shrink-0 border-t border-slate-100 dark:border-slate-800 p-4">
                <!-- Expanded: full Light/Dark toggle -->
                <div v-if="!sidebarCollapsed" class="bg-slate-50 dark:bg-slate-800/80 p-1 rounded-[14px] flex border border-slate-100 dark:border-slate-700/50 mb-3">
                    <button
                        @click="isDark = false"
                        :class="[!isDark ? 'bg-white text-slate-800 shadow-sm border border-slate-100/50' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300', 'flex-1 flex items-center justify-center gap-1.5 py-2 rounded-[10px] text-[12px] font-bold transition-all']"
                    >
                        <Sun class="w-3.5 h-3.5" /> Light
                    </button>
                    <button
                        @click="isDark = true"
                        :class="[isDark ? 'bg-slate-700 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300', 'flex-1 flex items-center justify-center gap-1.5 py-2 rounded-[10px] text-[12px] font-bold transition-all']"
                    >
                        <Moon class="w-3.5 h-3.5" /> Dark
                    </button>
                </div>

                <!-- Collapsed: icon-only toggle -->
                <div v-else class="flex justify-center mb-3">
                    <button
                        @click="isDark = !isDark"
                        class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-slate-500 hover:text-[#2563EB] transition-colors"
                    >
                        <Sun v-if="isDark" class="w-4 h-4" />
                        <Moon v-else class="w-4 h-4" />
                    </button>
                </div>

                <Link
                    href="/dashboard"
                    class="w-full text-center text-slate-400 hover:text-[#2563EB] text-[12px] font-bold transition-colors block truncate"
                >
                    <span v-if="!sidebarCollapsed">← Back to User Portal</span>
                    <span v-else title="Back to User Portal" class="text-base">←</span>
                </Link>
            </div>
        </aside>

        <!-- Mobile Overlay -->
        <transition name="fade">
            <div
                v-if="mobileOpen"
                @click="mobileOpen = false"
                class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm z-30 lg:hidden"
            ></div>
        </transition>

        <!-- ===================== MAIN CONTENT ===================== -->
        <div
            class="flex flex-col min-h-screen transition-all duration-300"
            :class="sidebarCollapsed ? 'lg:ml-[72px]' : 'lg:ml-[260px]'"
        >
            <!-- Sticky Top Header -->
            <header class="sticky top-0 z-20 flex items-center justify-between h-[60px] sm:h-[68px] px-4 sm:px-6 bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 shrink-0">
                <!-- Left: Mobile toggle -->
                <div class="flex items-center gap-4">
                    <button
                        @click="mobileOpen = !mobileOpen"
                        class="lg:hidden flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-[#2563EB] transition-colors"
                    >
                        <Menu class="w-4 h-4" />
                    </button>
                </div>

                <!-- Right: Search + Avatar -->
                <div class="flex items-center gap-3">
                    <div class="relative bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 h-[38px] w-[200px] sm:w-[260px] hidden sm:flex items-center px-3 focus-within:ring-1 focus-within:ring-slate-400 dark:focus-within:ring-slate-700 focus-within:border-slate-400 dark:focus-within:border-slate-700 transition-all">
                        <Search class="w-4 h-4 text-slate-400 shrink-0" />
                        <input
                            v-model="searchQuery"
                            @keyup.enter="handleSearch"
                            :placeholder="searchPlaceholder"
                            class="w-full h-full bg-transparent border-none focus:outline-none focus:ring-0 text-[13px] font-medium text-slate-700 dark:text-slate-200 placeholder-slate-400 pl-2.5"
                        />
                    </div>

                    <!-- Profile Dropdown Component -->
                    <div class="relative">
                        <!-- Click outside backdrop listener -->
                        <div 
                            v-if="profileDropdownOpen" 
                            @click="profileDropdownOpen = false" 
                            class="fixed inset-0 z-40 bg-transparent"
                        ></div>

                        <button
                            @click="profileDropdownOpen = !profileDropdownOpen"
                            class="flex items-center gap-2 h-9 sm:h-10 px-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all relative z-50 text-left border border-transparent hover:border-slate-150 dark:hover:border-slate-700 select-none"
                        >
                            <div class="w-8 h-8 rounded-full overflow-hidden border border-slate-150 dark:border-slate-700 shadow-sm shrink-0 flex items-center justify-center bg-blue-600 text-white font-bold text-xs">
                                <img v-if="user?.avatar" :src="user.avatar" class="w-full h-full object-cover" :alt="firstName" />
                                <img v-else :src="`https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(firstName)}&backgroundColor=2563eb&textColor=ffffff`" class="w-full h-full object-cover" :alt="firstName" />
                            </div>
                            <div class="hidden sm:flex flex-col">
                                <span class="text-xs font-bold text-slate-800 dark:text-white leading-tight">{{ user?.name || 'Admin' }}</span>
                                <span class="text-[9.5px] text-slate-400 font-medium leading-none mt-0.5">{{ user?.user_type ? user.user_type.replace('_', ' ').replace(/\b\w/g, (l: string) => l.toUpperCase()) : 'Super Admin' }}</span>
                            </div>
                            <ChevronDown class="w-3.5 h-3.5 text-slate-400 hidden sm:block shrink-0" />
                        </button>
                        
                        <transition name="fade">
                            <div 
                                v-if="profileDropdownOpen" 
                                class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-xl py-2 z-50 transform origin-top-right transition-all"
                            >
                                <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800">
                                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-wider">Signed in as</p>
                                    <p class="text-xs font-black text-slate-800 dark:text-white truncate mt-0.5">{{ user?.email }}</p>
                                </div>

                                <Link 
                                    href="/settings/profile" 
                                    class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-650 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-900/50 hover:text-blue-600 transition-all"
                                >
                                    <User class="w-4 h-4 opacity-70" />
                                    Edit Profil
                                </Link>

                                <Link 
                                    href="/dashboard" 
                                    class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-650 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-900/50 hover:text-blue-600 transition-all"
                                >
                                    <LayoutGrid class="w-4 h-4 opacity-70" />
                                    Portal Utama
                                </Link>

                                <button 
                                    @click="logout"
                                    class="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 transition-all text-left border-t border-slate-50 dark:border-slate-800 mt-1"
                                >
                                    <X class="w-4 h-4 opacity-80" />
                                    Keluar (Logout)
                                </button>
                            </div>
                        </transition>
                    </div>
                </div>
            </header>

            <!-- Page Slot -->
            <main class="flex-1 bg-slate-50 dark:bg-slate-950 p-6 sm:p-8 lg:p-10 transition-colors duration-300">
                <div v-if="isPageLoading" class="animate-pulse space-y-6">
                    <!-- Header skeleton -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="h-8 w-48 bg-slate-200 dark:bg-slate-800 rounded-xl"></div>
                        <div class="h-9 w-32 bg-slate-200 dark:bg-slate-800 rounded-xl"></div>
                    </div>
                    <!-- Toolbar skeleton -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-100 dark:border-slate-800 shadow-sm flex flex-col md:flex-row gap-3 items-center justify-between">
                        <div class="flex gap-2 w-full">
                            <div class="h-8 w-24 bg-slate-100 dark:bg-slate-900 rounded-xl"></div>
                            <div class="h-8 w-24 bg-slate-100 dark:bg-slate-900 rounded-xl"></div>
                            <div class="h-8 w-24 bg-slate-100 dark:bg-slate-900 rounded-xl"></div>
                        </div>
                        <div class="h-8 w-full md:w-[260px] bg-slate-100 dark:bg-slate-900 rounded-xl"></div>
                    </div>
                    <!-- List / Content skeleton -->
                    <div class="space-y-3">
                        <div v-for="i in 5" :key="i" class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-100 dark:border-slate-800 flex items-center gap-4">
                            <div class="w-14 h-14 bg-slate-200 dark:bg-slate-700/30 rounded-lg"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-4 w-1/3 bg-slate-200 dark:bg-slate-700/30 rounded-md"></div>
                                <div class="h-3 w-1/4 bg-slate-100 dark:bg-slate-700/20 rounded-md"></div>
                            </div>
                            <div class="h-8 w-16 bg-slate-100 dark:bg-slate-700/20 rounded-lg"></div>
                        </div>
                    </div>
                </div>
                <slot v-else />
            </main>
        </div>

    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

::-webkit-scrollbar {
    width: 5px;
    height: 5px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 4px;
}
.dark ::-webkit-scrollbar-thumb {
    background: #334155;
}
::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
.dark ::-webkit-scrollbar-thumb:hover {
    background: #475569;
}
</style>
