<script setup lang="ts">
import type { PageProps } from "@inertiajs/core";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import {
    ArrowLeft,
    Bell,
    Briefcase,
    Building2,
    ChevronDown,
    LayoutDashboard,
    LogOut,
    Settings,
    Users,
    ClipboardList,
    FileText,
} from "lucide-vue-next";
import { computed, onMounted, onUnmounted, ref } from "vue";
import AppShell from "@/components/AppShell.vue";
import AppContent from "@/components/AppContent.vue";
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
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
    DropdownMenuLabel,
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

withDefaults(
    defineProps<{
        title?: string;
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    }
);

const page = usePage<TracePageProps>();
const user = computed(() => page.props.auth?.user);
const firstName = computed(() => user.value?.name?.split(" ")[0] ?? "Mitra");

const { getInitials } = useInitials();

const logout = () => router.post("/logout");

const isPageLoading = ref(false);
let loadingTimeout: ReturnType<typeof setTimeout> | null = null;

onMounted(() => {
    const startOff = router.on("start", () => {
        loadingTimeout = setTimeout(() => { isPageLoading.value = true; }, 150);
    });
    const finishOff = router.on("finish", () => {
        if (loadingTimeout) clearTimeout(loadingTimeout);
        isPageLoading.value = false;
    });
    onUnmounted(() => { startOff(); finishOff(); });
});

const navItems = [
    { label: "Dashboard",           href: "/trace/mitra",               icon: LayoutDashboard, match: (u: string) => u === "/trace/mitra" },
    { label: "Profil Perusahaan",   href: "/trace/mitra/profil",        icon: Building2,       match: (u: string) => u.startsWith("/trace/mitra/profil") },
    { label: "Lowongan Magang",     href: "/trace/mitra/lowongan",      icon: Briefcase,       match: (u: string) => u.startsWith("/trace/mitra/lowongan") },
    { label: "Pendaftar",           href: "/trace/mitra/pendaftar",     icon: Users,           match: (u: string) => u.startsWith("/trace/mitra/pendaftar") },
    { label: "Penilaian Peserta",   href: "/trace/mitra/penilaian",     icon: ClipboardList,   match: (u: string) => u.startsWith("/trace/mitra/penilaian") },
    { label: "Laporan",             href: "/trace/mitra/laporan",       icon: FileText,        match: (u: string) => u.startsWith("/trace/mitra/laporan") },
];
</script>

<template>
    <Head :title="title ? `${title} — TRACE Mitra` : 'TRACE — Mitra Industri'" />

    <AppShell variant="sidebar">
        <!-- ═══════════════════════ SIDEBAR ═══════════════════════ -->
        <Sidebar collapsible="icon">
            <!-- Brand Header -->
            <SidebarHeader class="flex flex-row items-center justify-between p-5 group-data-[collapsible=icon]:px-0 group-data-[collapsible=icon]:py-4 group-data-[collapsible=icon]:justify-center border-b border-slate-50 dark:border-zinc-900 group/header relative">
                <Link href="/trace/mitra" class="flex items-center gap-2.5 shrink-0 transition-all duration-300 group-data-[collapsible=icon]:group-hover/header:opacity-0 group-data-[collapsible=icon]:group-hover/header:scale-75">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-700 shadow-md shadow-violet-500/20">
                        <Building2 class="h-4 w-4 text-white" />
                    </div>
                    <div class="flex flex-col text-left leading-none group-data-[collapsible=icon]:hidden">
                        <span class="text-[14px] font-black tracking-wide text-slate-900 dark:text-white">TRACE</span>
                        <span class="text-[9px] font-semibold uppercase tracking-widest text-violet-600 dark:text-violet-400 mt-0.5">Mitra Industri</span>
                    </div>
                </Link>
                <SidebarTrigger class="sidebar-trigger-btn shrink-0 bg-transparent text-slate-400 hover:text-violet-600 rounded-lg p-1 w-7 h-7 hover:bg-slate-50 dark:hover:bg-zinc-900 border-none shadow-none transition-all duration-305 group-data-[collapsible=icon]:absolute group-data-[collapsible=icon]:inset-0 group-data-[collapsible=icon]:m-auto group-data-[collapsible=icon]:opacity-0 group-data-[collapsible=icon]:pointer-events-none group-data-[collapsible=icon]:group-hover/header:opacity-100 group-data-[collapsible=icon]:group-hover/header:pointer-events-auto" />
            </SidebarHeader>

            <!-- Navigation Sections -->
            <SidebarContent class="px-2 py-4 group-data-[collapsible=icon]:p-0 group-data-[collapsible=icon]:gap-0">
                <SidebarGroup class="px-3 py-2 group-data-[collapsible=icon]:px-0 group-data-[collapsible=icon]:py-0 group-data-[collapsible=icon]:w-full group-data-[collapsible=icon]:flex group-data-[collapsible=icon]:flex-col group-data-[collapsible=icon]:items-center">
                    <SidebarGroupLabel class="px-2 text-[10px] font-extrabold uppercase tracking-[0.08em] text-slate-400/80 dark:text-zinc-500 mb-2 group-data-[collapsible=icon]:hidden select-none">
                        Menu Utama
                    </SidebarGroupLabel>
                    <SidebarMenu class="space-y-1 group-data-[collapsible=icon]:items-center group-data-[collapsible=icon]:w-full">
                        <SidebarMenuItem v-for="item in navItems" :key="item.href" class="group-data-[collapsible=icon]:w-full group-data-[collapsible=icon]:flex group-data-[collapsible=icon]:justify-center">
                            <SidebarMenuButton
                                as-child
                                :is-active="item.match($page.url)"
                                :tooltip="item.label"
                                class="h-10 rounded-xl transition-all duration-150"
                                :class="item.match($page.url) 
                                  ? 'font-bold text-violet-700 bg-violet-50 dark:bg-violet-950/40 dark:text-violet-400 shadow-sm border border-violet-200/40 dark:border-violet-800/25' 
                                  : 'font-medium text-slate-500 hover:text-slate-900 hover:bg-slate-50 dark:text-zinc-400 dark:hover:text-white dark:hover:bg-zinc-900'"
                            >
                                <Link :href="item.href">
                                    <component :is="item.icon" class="h-[18px] w-[18px] shrink-0 transition-colors" 
                                                :class="item.match($page.url) ? 'text-violet-700 dark:text-violet-400' : 'text-slate-400 group-hover:text-slate-650'" />
                                    <span class="text-[13.5px] group-data-[collapsible=icon]:hidden select-none">{{ item.label }}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroup>
            </SidebarContent>

            <!-- Sidebar Footer -->
            <SidebarFooter class="px-5 py-4 border-t border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-950">
                <!-- Back to Portal button -->
                <SidebarMenu>
                    <SidebarMenuItem class="group-data-[collapsible=icon]:w-full group-data-[collapsible=icon]:flex group-data-[collapsible=icon]:justify-center">
                        <SidebarMenuButton
                            as-child
                            tooltip="Kembali ke Portal"
                            class="h-10 rounded-xl transition-all duration-200 font-medium text-slate-500 hover:text-violet-700 hover:bg-violet-50/50 dark:text-zinc-400 dark:hover:text-violet-400 dark:hover:bg-violet-950/20"
                        >
                            <Link href="/dashboard">
                                <ArrowLeft class="h-[18px] w-[18px] shrink-0 text-slate-400" />
                                <span class="text-[13.5px] group-data-[collapsible=icon]:hidden select-none">Kembali ke Portal</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
                <p class="text-[10px] text-slate-400/80 dark:text-zinc-500 font-semibold text-center mt-4 group-data-[collapsible=icon]:hidden">
                    &copy; 2026 Portal FMIKOM
                </p>
            </SidebarFooter>
        </Sidebar>

        <!-- ═══════════════════════ MAIN CONTENT ═══════════════════════ -->
        <AppContent variant="sidebar" class="overflow-x-hidden pb-24 md:pb-0 relative z-[1]">
            <!-- Sticky Top Header -->
            <header class="sticky top-0 z-20 flex h-14 shrink-0 items-center justify-between border-b border-slate-100 bg-white px-4 dark:border-slate-800 dark:bg-slate-900 sm:px-6">
                <!-- Left: SidebarTrigger + Breadcrumbs -->
                <div class="flex items-center gap-3">
                    <SidebarTrigger class="-ml-2 h-9 w-9 text-slate-500 hover:text-violet-600 dark:text-slate-400 dark:hover:text-violet-400" />
                    
                    <div class="flex items-center gap-2">
                        <template v-if="breadcrumbs && breadcrumbs.length > 0">
                            <Breadcrumbs :breadcrumbs="breadcrumbs" />
                        </template>
                        <template v-else>
                            <span class="rounded-lg bg-violet-50 px-2.5 py-1 text-[11px] font-black uppercase tracking-wider text-violet-700 dark:bg-violet-950/40 dark:text-violet-400">
                                TRACE
                            </span>
                            <span class="text-slate-300 dark:text-slate-700">/</span>
                            <span class="text-[13px] font-semibold text-slate-500 dark:text-slate-400">
                                {{ title || 'Mitra Industri' }}
                            </span>
                        </template>
                    </div>
                </div>

                <!-- Right: Notifications & Profile Dropdown -->
                <div class="flex items-center gap-2">
                    <button class="relative flex h-9 w-9 items-center justify-center rounded-xl text-slate-500 transition-colors hover:bg-slate-100 hover:text-violet-600 dark:hover:bg-slate-800">
                        <Bell class="h-4 w-4" />
                        <span
                            v-if="(page.props.notifications_count ?? 0) > 0"
                            class="absolute right-1.5 top-1.5 flex h-2 w-2 items-center justify-center rounded-full bg-violet-500"
                        />
                    </button>

                    <!-- Profile Dropdown -->
                    <DropdownMenu>
                        <DropdownMenuTrigger class="flex items-center gap-2 py-1 pl-2 pr-3 rounded-xl outline-none hover:bg-slate-50 dark:hover:bg-zinc-900/60 border border-transparent hover:border-slate-100 dark:hover:border-zinc-850 transition-all duration-300 group">
                            <Avatar class="h-[32px] w-[32px] overflow-hidden rounded-full ring-2 ring-violet-100 dark:ring-violet-900 shadow-sm transition-all duration-300 group-hover:ring-violet-500/30">
                                <AvatarImage v-if="user?.avatar" :src="user.avatar" :alt="user.name" class="object-cover" />
                                <AvatarFallback class="rounded-full bg-violet-100 dark:bg-violet-900 text-violet-700 dark:text-violet-300 text-[10px] font-bold">
                                    {{ getInitials(user?.name ?? 'M') }}
                                </AvatarFallback>
                            </Avatar>
                            <div class="hidden flex-col text-left leading-none sm:flex">
                                <span class="text-xs font-bold text-slate-850 dark:text-zinc-200 tracking-tight truncate max-w-[120px]">{{ firstName }}</span>
                                <span class="text-[9px] font-semibold text-violet-600 dark:text-violet-400 mt-0.5 uppercase tracking-wider">Mitra Industri</span>
                            </div>
                            <ChevronDown class="hidden h-3.5 w-3.5 text-slate-400 dark:text-zinc-500 transition-transform duration-200 group-hover:translate-y-px sm:block" />
                        </DropdownMenuTrigger>
                        <DropdownMenuContent class="w-56 rounded-xl border border-gray-100 p-2 shadow-xl dark:border-zinc-800 dark:bg-zinc-900" align="end" :side-offset="8">
                            <div class="border-b border-slate-100 px-3 py-2 dark:border-slate-700">
                                <p class="text-[9px] font-black uppercase tracking-wider text-slate-400">Masuk sebagai</p>
                                <p class="mt-0.5 truncate text-xs font-black text-slate-800 dark:text-white">{{ user?.email }}</p>
                            </div>
                            <DropdownMenuGroup class="mt-1">
                                <DropdownMenuItem :as-child="true">
                                    <Link class="flex w-full cursor-pointer items-center px-3 py-2 text-xs font-medium text-slate-600 hover:text-violet-600 dark:text-slate-350 dark:hover:text-violet-400" href="/settings/profile">
                                        <Settings class="mr-2 h-4 w-4" />
                                        Settings
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem :as-child="true">
                                    <Link class="flex w-full cursor-pointer items-center px-3 py-2 text-xs font-medium text-slate-600 hover:text-violet-600 dark:text-slate-350 dark:hover:text-violet-400" href="/dashboard">
                                        <LayoutDashboard class="mr-2 h-4 w-4" />
                                        Portal Utama
                                    </Link>
                                </DropdownMenuItem>
                            </DropdownMenuGroup>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem :as-child="true">
                                <Link
                                    class="flex w-full cursor-pointer items-center px-3 py-2 text-xs font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20"
                                    href="/logout"
                                    method="post"
                                    as="button"
                                >
                                    <LogOut class="mr-2 h-4 w-4" />
                                    Log out
                                </Link>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 p-4 transition-colors duration-300 sm:p-6 lg:p-8">
                <div v-if="isPageLoading" class="animate-pulse space-y-5">
                    <div class="h-8 w-56 rounded-xl bg-slate-200 dark:bg-slate-800" />
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="i in 3" :key="i" class="h-28 rounded-2xl bg-slate-200 dark:bg-slate-800" />
                    </div>
                    <div class="h-64 rounded-2xl bg-slate-200 dark:bg-slate-800" />
                </div>
                <slot v-else />
            </main>

            <!-- Footer -->
            <footer class="shrink-0 border-t border-slate-100 px-6 py-3 dark:border-slate-800">
                <p class="text-center text-[11px] text-slate-400 dark:text-slate-600">
                    TRACE — Tracking & Record Assessment System · FMIKOM · {{ new Date().getFullYear() }}
                </p>
            </footer>
        </AppContent>
    </AppShell>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
::-webkit-scrollbar { width: 5px; height: 5px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }
.dark ::-webkit-scrollbar-thumb { background: #334155; }
</style>
