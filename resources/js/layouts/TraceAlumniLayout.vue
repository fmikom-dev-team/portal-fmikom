<script setup lang="ts">
import type { PageProps } from "@inertiajs/core";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import {
    ArrowLeft,
    Bell,
    BookOpen,
    Briefcase,
    ChevronDown,
    GraduationCap,
    LayoutDashboard,
    LogOut,
    Settings,
    User,
    Users,
} from "lucide-vue-next";
import { computed, onMounted, onUnmounted, ref } from "vue";
import AppShell from "@/components/AppShell.vue";
import AppContent from "@/components/AppContent.vue";
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarTrigger,
} from "@/components/ui/sidebar";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
    DropdownMenuSeparator,
    DropdownMenuGroup,
    DropdownMenuItem,
} from "@/components/ui/dropdown-menu";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { useInitials } from "@/composables/useInitials";
import type { BreadcrumbItem } from "@/types";
import Breadcrumbs from "@/components/Breadcrumbs.vue";

interface TracePageProps extends PageProps {
    auth: { user: any };
    notifications_count?: number;
}

const props = withDefaults(
    defineProps<{
        roleName: string;
        title?: string;
        breadcrumbs?: BreadcrumbItem[];
        moduleName?: string;
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage<TracePageProps>();
const user = computed(() => page.props.auth?.user);
const firstName = computed(() => user.value?.name?.split(" ")[0] ?? "Alumni");
const { getInitials } = useInitials();

const isPageLoading = ref(false);
let loadingTimeout: ReturnType<typeof setTimeout> | null = null;

onMounted(() => {
    const startOff = router.on("start", () => {
        loadingTimeout = setTimeout(() => {
            isPageLoading.value = true;
        }, 150);
    });
    const finishOff = router.on("finish", () => {
        if (loadingTimeout) clearTimeout(loadingTimeout);
        isPageLoading.value = false;
    });
    onUnmounted(() => {
        startOff();
        finishOff();
    });
});

const navItems = [
    {
        label: "Dashboard",
        href: "/trace",
        icon: LayoutDashboard,
        match: (u: string) => u === "/trace",
    },
    {
        label: "Profile",
        href: "/trace/profile-alumni",
        icon: User,
        match: (u: string) => u.startsWith("/trace/profile"),
    },
    {
        label: "Riwayat Pekerjaan",
        href: "/trace/karir",
        icon: Briefcase,
        match: (u: string) => u.startsWith("/trace/karir"),
    },
    {
        label: "Kuesioner",
        href: "/trace/kuesioner",
        icon: BookOpen,
        match: (u: string) => u.startsWith("/trace/kuesioner"),
    },
    {
        label: "Lowongan",
        href: "/trace/direktori",
        icon: Users,
        match: (u: string) => u.startsWith("/trace/direktori"),
    },
];
</script>

<template>
    <Head :title="title ? `${title} — TRACE Alumni` : 'TRACE — Alumni'" />

    <AppShell variant="sidebar">
        <Sidebar collapsible="icon">
            <!-- Sidebar Header -->
            <SidebarHeader
                class="flex flex-row items-center justify-between p-4 group-data-[collapsible=icon]:px-0 group-data-[collapsible=icon]:py-4 group-data-[collapsible=icon]:justify-center border-b border-slate-100 dark:border-zinc-800/60 group/header relative"
            >
                <Link
                    href="/trace"
                    class="flex items-center gap-2.5 shrink-0 transition-all duration-300 group-data-[collapsible=icon]:group-hover/header:opacity-0 group-data-[collapsible=icon]:group-hover/header:scale-75"
                >
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-green-500 to-emerald-700 shadow-sm shadow-green-500/25"
                    >
                        <GraduationCap class="h-4 w-4 text-white" />
                    </div>
                    <div
                        class="flex flex-col text-left leading-none group-data-[collapsible=icon]:hidden"
                    >
                        <span
                            class="text-[13px] font-black tracking-wide text-slate-900 dark:text-white"
                            >TRACE</span
                        >
                        <span
                            class="text-[9px] font-semibold uppercase tracking-widest text-green-600 dark:text-green-400 mt-0.5"
                            >Alumni Portal</span
                        >
                    </div>
                </Link>
                <SidebarTrigger
                    class="shrink-0 bg-transparent text-slate-400 hover:text-green-600 rounded-lg p-1 w-7 h-7 hover:bg-slate-50 dark:hover:bg-zinc-800 border-none shadow-none transition-all duration-200 group-data-[collapsible=icon]:absolute group-data-[collapsible=icon]:inset-0 group-data-[collapsible=icon]:m-auto group-data-[collapsible=icon]:opacity-0 group-data-[collapsible=icon]:pointer-events-none group-data-[collapsible=icon]:group-hover/header:opacity-100 group-data-[collapsible=icon]:group-hover/header:pointer-events-auto"
                />
            </SidebarHeader>

            <!-- Navigation -->
            <SidebarContent
                class="px-2 py-3 group-data-[collapsible=icon]:px-1 group-data-[collapsible=icon]:py-2"
            >
                <SidebarGroup class="group-data-[collapsible=icon]:px-0">
                    <SidebarGroupLabel
                        class="px-2 text-[10px] font-bold uppercase tracking-[0.1em] text-slate-400/70 dark:text-zinc-500 mb-1 group-data-[collapsible=icon]:hidden select-none"
                    >
                        Menu Utama
                    </SidebarGroupLabel>
                    <SidebarMenu class="space-y-0.5">
                        <SidebarMenuItem
                            v-for="item in navItems"
                            :key="item.href"
                        >
                            <SidebarMenuButton
                                as-child
                                :is-active="item.match($page.url)"
                                :tooltip="item.label"
                                class="h-9 rounded-lg transition-all duration-150"
                                :class="
                                    item.match($page.url)
                                        ? 'font-semibold text-green-700 bg-green-50 dark:bg-green-950/50 dark:text-green-400 border border-green-100 dark:border-green-900/40'
                                        : 'font-medium text-slate-500 hover:text-slate-900 hover:bg-slate-50/80 dark:text-zinc-400 dark:hover:text-white dark:hover:bg-zinc-800/60'
                                "
                            >
                                <Link
                                    :href="item.href"
                                    class="flex items-center gap-2.5 px-2"
                                >
                                    <component
                                        :is="item.icon"
                                        class="h-[17px] w-[17px] shrink-0"
                                        :class="
                                            item.match($page.url)
                                                ? 'text-green-600 dark:text-green-400'
                                                : 'text-slate-400 dark:text-zinc-500'
                                        "
                                    />
                                    <span
                                        class="text-[13px] group-data-[collapsible=icon]:hidden select-none"
                                    >
                                        {{ item.label }}
                                    </span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroup>
            </SidebarContent>

            <!-- Sidebar Footer -->
            <SidebarFooter
                class="px-3 py-3 border-t border-slate-100 dark:border-zinc-800/60"
            >
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            tooltip="Kembali ke Portal"
                            class="h-9 rounded-lg transition-all duration-200 font-medium text-slate-400 hover:text-slate-700 hover:bg-slate-50 dark:text-zinc-500 dark:hover:text-zinc-200 dark:hover:bg-zinc-800/60"
                        >
                            <Link
                                href="/dashboard"
                                class="flex items-center gap-2.5 px-2"
                            >
                                <ArrowLeft class="h-4.25 w-4.25 shrink-0" />
                                <span
                                    class="text-[13px] group-data-[collapsible=icon]:hidden select-none"
                                >
                                    Kembali ke Portal
                                </span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
                <p
                    class="text-[10px] text-slate-300 dark:text-zinc-600 text-center mt-3 group-data-[collapsible=icon]:hidden select-none"
                >
                    &copy; {{ new Date().getFullYear() }} Portal FMIKOM
                </p>
            </SidebarFooter>
        </Sidebar>

        <!-- Main Content -->
        <AppContent
            variant="sidebar"
            class="overflow-x-hidden pb-20 md:pb-0 relative z-[1] flex flex-col min-h-screen"
        >
            <!-- Top Header -->
            <header
                class="sticky top-0 z-20 flex h-13 shrink-0 items-center justify-between border-b border-slate-100 bg-white/95 backdrop-blur-sm px-4 dark:border-slate-800 dark:bg-slate-900/95 sm:px-5"
            >
                <!-- Left: Breadcrumbs -->
                <div class="flex items-center gap-2 min-w-0">
                    <template v-if="breadcrumbs && breadcrumbs.length > 0">
                        <Breadcrumbs :breadcrumbs="breadcrumbs" />
                    </template>
                    <template v-else>
                        <span
                            class="rounded-md bg-green-50 px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-green-700 dark:bg-green-950/50 dark:text-green-400 shrink-0"
                        >
                            TRACE
                        </span>
                        <span
                            class="text-slate-300 dark:text-slate-700 shrink-0"
                            >/</span
                        >
                        <span
                            class="text-[13px] font-medium text-slate-500 dark:text-slate-400 truncate"
                        >
                            {{ title || "Alumni" }}
                        </span>
                    </template>
                </div>

                <!-- Right: Notifications + Profile -->
                <div class="flex items-center gap-1.5 shrink-0">
                    <!-- Notifications -->
                    <button
                        class="relative flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                        aria-label="Notifikasi"
                    >
                        <Bell class="h-4 w-4" />
                        <span
                            v-if="(page.props.notifications_count ?? 0) > 0"
                            class="absolute right-1.5 top-1.5 h-1.5 w-1.5 rounded-full bg-green-500"
                        />
                    </button>

                    <!-- Profile Dropdown -->
                    <DropdownMenu>
                        <DropdownMenuTrigger
                            class="flex items-center gap-2 py-1 pl-1.5 pr-2.5 rounded-lg outline-none hover:bg-slate-50 dark:hover:bg-zinc-800/60 border border-transparent hover:border-slate-100 dark:hover:border-zinc-700/50 transition-all duration-200 group"
                        >
                            <Avatar
                                class="h-7 w-7 overflow-hidden rounded-full ring-1 ring-green-200/60 dark:ring-green-800/40"
                            >
                                <AvatarImage
                                    v-if="user?.avatar"
                                    :src="user.avatar"
                                    :alt="user.name"
                                    class="object-cover"
                                />
                                <AvatarFallback
                                    class="rounded-full bg-green-100 dark:bg-green-900/60 text-green-700 dark:text-green-300 text-[10px] font-bold"
                                >
                                    {{ getInitials(user?.name ?? "A") }}
                                </AvatarFallback>
                            </Avatar>
                            <div
                                class="hidden flex-col text-left leading-none sm:flex"
                            >
                                <span
                                    class="text-[12px] font-semibold text-slate-800 dark:text-zinc-200 truncate max-w-[100px]"
                                >
                                    {{ firstName }}
                                </span>
                                <span
                                    class="text-[9px] font-semibold text-green-600 dark:text-green-400 mt-0.5 uppercase tracking-wider"
                                >
                                    {{ roleName }}
                                </span>
                            </div>
                            <ChevronDown
                                class="hidden h-3 w-3 text-slate-400 dark:text-zinc-500 sm:block"
                            />
                        </DropdownMenuTrigger>
                        <DropdownMenuContent
                            class="w-52 rounded-xl border border-slate-100 p-1.5 shadow-lg dark:border-zinc-800 dark:bg-zinc-900"
                            align="end"
                            :side-offset="6"
                        >
                            <!-- User info -->
                            <div class="px-3 py-2 mb-1">
                                <p
                                    class="text-[9px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500"
                                >
                                    Masuk sebagai
                                </p>
                                <p
                                    class="mt-0.5 truncate text-[12px] font-semibold text-slate-800 dark:text-white"
                                >
                                    {{ user?.name }}
                                </p>
                                <p
                                    class="truncate text-[11px] text-slate-400 dark:text-zinc-500"
                                >
                                    {{ user?.email }}
                                </p>
                            </div>
                            <DropdownMenuSeparator class="mx-1 mb-1" />
                            <DropdownMenuGroup>
                                <DropdownMenuItem :as-child="true">
                                    <Link
                                        class="flex w-full cursor-pointer items-center gap-2 rounded-lg px-3 py-2 text-[12px] font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white transition-colors"
                                        href="/settings/profile"
                                    >
                                        <Settings class="h-3.5 w-3.5" />
                                        Pengaturan
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem :as-child="true">
                                    <Link
                                        class="flex w-full cursor-pointer items-center gap-2 rounded-lg px-3 py-2 text-[12px] font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white transition-colors"
                                        href="/dashboard"
                                    >
                                        <LayoutDashboard class="h-3.5 w-3.5" />
                                        Portal Utama
                                    </Link>
                                </DropdownMenuItem>
                            </DropdownMenuGroup>
                            <DropdownMenuSeparator class="mx-1 my-1" />
                            <DropdownMenuItem :as-child="true">
                                <Link
                                    class="flex w-full cursor-pointer items-center gap-2 rounded-lg px-3 py-2 text-[12px] font-semibold text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 transition-colors"
                                    href="/logout"
                                    method="post"
                                    as="button"
                                >
                                    <LogOut class="h-3.5 w-3.5" />
                                    Keluar
                                </Link>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-4 sm:p-5 lg:p-7">
                <!-- Loading skeleton -->
                <div v-if="isPageLoading" class="animate-pulse space-y-4">
                    <div
                        class="h-7 w-48 rounded-lg bg-slate-200 dark:bg-slate-800"
                    />
                    <div
                        class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4"
                    >
                        <div
                            v-for="i in 4"
                            :key="i"
                            class="h-24 rounded-xl bg-slate-200 dark:bg-slate-800"
                        />
                    </div>
                    <div
                        class="h-56 rounded-xl bg-slate-200 dark:bg-slate-800"
                    />
                </div>
                <slot v-else />
            </main>

            <!-- Footer -->
            <footer
                class="shrink-0 border-t border-slate-100 px-5 py-2.5 dark:border-slate-800"
            >
                <p
                    class="text-center text-[11px] text-slate-300 dark:text-slate-700"
                >
                    TRACE — Tracking & Record Assessment System · FMIKOM ·
                    {{ new Date().getFullYear() }}
                </p>
            </footer>
        </AppContent>
    </AppShell>
</template>

<style scoped>
::-webkit-scrollbar {
    width: 4px;
    height: 4px;
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
</style>
