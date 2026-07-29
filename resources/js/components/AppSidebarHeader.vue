<script setup lang="ts">
import { Link, router, usePage } from "@inertiajs/vue3";
import { ChevronDown, LogOut, Menu, Settings } from "lucide-vue-next";
import { computed, onMounted, onUnmounted, ref } from "vue";
import AppLogo from "@/components/AppLogo.vue";
import UserMenuContent from "@/components/UserMenuContent.vue";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import {
	DropdownMenu,
	DropdownMenuContent,
	DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { useInitials } from "@/composables/useInitials";
import { edit as editProfile } from "@/routes/profile";
import { dashboard } from "@/routes";
import { toUrl } from "@/lib/utils";
import { useAppearance } from "@/composables/useAppearance";
import { ThemeTogglerButton } from "@/components/animate-ui/components/buttons/theme-toggler";

const { appearance, resolvedAppearance, updateAppearance } = useAppearance();

const activeTheme = computed({
	get: () => appearance.value === "system" ? resolvedAppearance.value : appearance.value,
	set: (val) => {
		updateAppearance(val);
	}
});

const props = withDefaults(
	defineProps<{
		isScrolled?: boolean;
		sidebarCollapsed?: boolean;
		hideSidebar?: boolean;
	}>(),
	{
		isScrolled: false,
		sidebarCollapsed: false,
		hideSidebar: false,
	},
);

const emit = defineEmits<{
	(e: "toggleMobileSidebar"): void;
	(e: "toggleCollapse"): void;
}>();

const page = usePage();
const user = computed(
	() => page.props.auth?.user || { name: "User", email: "" },
);
const { getInitials } = useInitials();

const isPortalDashboard = computed(() => {
	return page.component === "Dashboard";
});

// Loading state for profile skeleton
const isLoading = ref(true);

// Scroll-aware for header
const isDesktopScrolled = ref(false);
const handleScroll = () => {
	isDesktopScrolled.value = globalThis.window.scrollY > 8;
};
onMounted(() => {
	globalThis.window.addEventListener("scroll", handleScroll, {
		passive: true,
	});
	setTimeout(() => {
		isLoading.value = false;
	}, 300);
});
onUnmounted(() =>
	globalThis.window.removeEventListener("scroll", handleScroll),
);

const isHeaderScrolled = computed(() => {
	return props.isScrolled || isDesktopScrolled.value;
});
</script>

<template>
    <header class="w-full shrink-0 sticky top-0 z-30">
        <!-- MOBILE HEADER: Blue gradient or flat #2563eb, sticky top with shadow on scroll -->
        <div 
            class="flex md:hidden w-full items-center justify-between px-4 pb-3 shrink-0 sticky top-0 left-0 right-0 transition-all duration-300 z-30"
            style="padding-top: calc(env(safe-area-inset-top) + 0.75rem); height: calc(68px + env(safe-area-inset-top));"
            :class="isPortalDashboard 
                ? [
                    'bg-[#2563eb] border-b border-blue-500/20',
                    isHeaderScrolled 
                        ? 'shadow-[0_12px_40px_rgba(0,0,0,0.32)] border-blue-500/10' 
                        : 'shadow-none'
                  ]
                : 'bg-linear-to-r from-[#1d4ed8] to-[#3B82F6] shadow-sm'"
        >
            <!-- Left Side Mobile: Hamburger (if admin) + App Logo & Title -->
            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                <button
                    v-if="!hideSidebar"
                    @click="emit('toggleMobileSidebar')"
                    class="flex items-center justify-center w-9 h-9 rounded-xl bg-white/15 text-white hover:bg-white/25 shrink-0 transition-colors cursor-pointer"
                    title="Buka Menu"
                >
                    <Menu class="w-5 h-5" />
                </button>

                <Link
                    :href="toUrl(dashboard())"
                    class="flex items-center gap-2 overflow-hidden min-w-0"
                >
                    <AppLogo class="text-white [&_span]:text-white! [&_span.text-slate-400]:text-blue-100!" />
                </Link>
            </div>

            <!-- Right Side Mobile: Theme Toggler & User Controls -->
            <div v-if="!isLoading" class="flex items-center gap-2.5 text-white shrink-0">
                <ThemeTogglerButton
                    v-model="activeTheme"
                    variant="ghost"
                    size="sm"
                    direction="ltr"
                    :modes="['light', 'dark']"
                    class="text-white hover:text-blue-100"
                />

                <Link
                    :href="editProfile().url"
                    class="hover:opacity-80 transition-opacity p-1.5 rounded-lg bg-white/10"
                    title="Pengaturan Profil"
                >
                    <Avatar class="h-[32px] w-[32px] overflow-hidden rounded-full ring-2 ring-white/30 shadow-xs">
                        <AvatarImage v-if="user.avatar" :src="user.avatar" :alt="user.name" class="object-cover" />
                        <AvatarFallback class="rounded-full bg-white/20 text-white text-xs font-bold">
                            {{ getInitials(user.name) }}
                        </AvatarFallback>
                    </Avatar>
                </Link>

                <button
                    @click="router.post('/logout')"
                    class="hover:opacity-80 transition-opacity p-1.5 rounded-lg bg-white/10"
                    title="Keluar"
                >
                    <LogOut class="w-[18px] h-[18px]" />
                </button>
            </div>
        </div>

        <!-- DESKTOP HEADER: Sticky top bar with glassmorphism backdrop blur -->
        <div
            class="hidden md:flex h-16 w-full items-center justify-between px-6 bg-white/90 dark:bg-zinc-950/90 border-b border-slate-100 dark:border-zinc-800/80 backdrop-blur-md transition-all duration-300 sticky top-0 z-30"
            :class="{ 'shadow-sm': isHeaderScrolled }"
        >
            <!-- Left Side Desktop: Logo & Brand Name ONLY if sidebar is hidden (Mahasiswa, Alumni, Mitra)! -->
            <div class="flex items-center gap-3 min-w-0 flex-1">
                <Link
                    v-if="hideSidebar"
                    :href="toUrl(dashboard())"
                    class="flex items-center gap-2.5 overflow-hidden min-w-0"
                >
                    <AppLogo />
                </Link>
            </div>

            <!-- Right Side Desktop: Theme Toggler & Profile Dropdown -->
            <div v-if="!isLoading" class="flex items-center gap-3 shrink-0">
                <!-- Theme Toggler -->
                <ThemeTogglerButton
                    v-model="activeTheme"
                    variant="ghost"
                    size="sm"
                    direction="ltr"
                    :modes="['light', 'dark']"
                />

                <!-- Profile Dropdown Menu -->
                <DropdownMenu>
                    <DropdownMenuTrigger
                        class="flex items-center gap-2.5 py-1 pl-1.5 pr-3 rounded-xl outline-hidden hover:bg-slate-100/80 dark:hover:bg-zinc-900 border border-transparent hover:border-slate-200/60 dark:hover:border-zinc-800 transition-all duration-200 group cursor-pointer"
                    >
                        <Avatar
                            class="h-[36px] w-[36px] overflow-hidden rounded-full ring-2 ring-slate-200/80 dark:ring-zinc-800 shadow-xs transition-all duration-300 group-hover:ring-indigo-500/30 dark:group-hover:ring-zinc-700"
                        >
                            <AvatarImage
                                v-if="user.avatar"
                                :src="user.avatar"
                                :alt="user.name"
                                class="object-cover"
                            />
                            <AvatarFallback
                                class="rounded-full bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 text-[10px] font-bold"
                            >
                                {{ getInitials(user.name) }}
                            </AvatarFallback>
                        </Avatar>
                        <div class="flex flex-col text-left leading-tight">
                            <span
                                class="text-xs font-bold text-slate-800 dark:text-zinc-200 tracking-tight uppercase truncate max-w-[160px]"
                            >{{ user.name }}</span>
                            <span
                                class="text-[10px] font-semibold text-slate-400 dark:text-zinc-500 mt-0.5 tracking-wide uppercase truncate max-w-[160px]"
                            >
                                {{ user.role_title || user.user_type || "User" }}
                            </span>
                        </div>
                        <ChevronDown
                            class="h-3.5 w-3.5 text-slate-400 dark:text-zinc-500 transition-transform duration-200 group-hover:translate-y-px"
                        />
                    </DropdownMenuTrigger>
                    <DropdownMenuContent
                        class="w-56 rounded-xl border border-slate-100 dark:border-zinc-800 p-2 shadow-xl bg-white dark:bg-zinc-900 z-50"
                        align="end"
                        :side-offset="8"
                    >
                        <UserMenuContent :user="user" />
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>
    </header>
</template>
