<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import {
	BookOpen,
	Globe,
	GraduationCap,
	LayoutGrid,
	Users,
} from "lucide-vue-next";
import { computed } from "vue";
import AppLogo from "@/components/AppLogo.vue";
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
import { useCurrentUrl } from "@/composables/useCurrentUrl";
import { dashboard } from "@/routes";

const page = usePage();
const user = computed(
	() => page.props.auth?.user || ({} as Record<string, any>),
);
const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <Sidebar collapsible="icon">
        <!-- Brand Header (Nexus layout style) with Hover Logo Reveal Toggle interaction -->
        <SidebarHeader class="flex flex-row items-center justify-between p-5 group-data-[collapsible=icon]:px-0 group-data-[collapsible=icon]:py-4 group-data-[collapsible=icon]:justify-center border-b border-slate-50 dark:border-zinc-900 group/header relative">
            <Link :href="dashboard()" class="flex items-center justify-center shrink-0 transition-all duration-300 group-data-[collapsible=icon]:group-hover/header:opacity-0 group-data-[collapsible=icon]:group-hover/header:scale-75">
                <AppLogo />
            </Link>
            <!-- Toggle Sidebar button - absolute centered when collapsed, fades in beautifully on hovering the logo brand area -->
            <SidebarTrigger class="sidebar-trigger-btn shrink-0 bg-transparent text-slate-400 hover:text-indigo-600 rounded-lg p-1 w-7 h-7 hover:bg-slate-50 dark:hover:bg-zinc-900 border-none shadow-none transition-all duration-305 group-data-[collapsible=icon]:absolute group-data-[collapsible=icon]:inset-0 group-data-[collapsible=icon]:m-auto group-data-[collapsible=icon]:opacity-0 group-data-[collapsible=icon]:pointer-events-none group-data-[collapsible=icon]:group-hover/header:opacity-100 group-data-[collapsible=icon]:group-hover/header:pointer-events-auto" />
        </SidebarHeader>

        <!-- Sidebar Navigation Sections (Nexus General/Tools/Support layout) -->
        <SidebarContent class="px-2 py-4 group-data-[collapsible=icon]:p-0 group-data-[collapsible=icon]:gap-0">
            <!-- GENERAL SECTION -->
            <SidebarGroup class="px-3 py-2 group-data-[collapsible=icon]:px-0 group-data-[collapsible=icon]:py-0 group-data-[collapsible=icon]:w-full group-data-[collapsible=icon]:flex group-data-[collapsible=icon]:flex-col group-data-[collapsible=icon]:items-center">
                <SidebarGroupLabel class="px-2 text-[10px] font-extrabold uppercase tracking-[0.08em] text-slate-400/80 dark:text-zinc-500 mb-2 group-data-[collapsible=icon]:hidden select-none">
                    General
                </SidebarGroupLabel>
                <SidebarMenu class="space-y-1 group-data-[collapsible=icon]:items-center group-data-[collapsible=icon]:w-full">
                    <SidebarMenuItem class="group-data-[collapsible=icon]:w-full group-data-[collapsible=icon]:flex group-data-[collapsible=icon]:justify-center">
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(dashboard())"
                            tooltip="Dashboard"
                            class="h-10 rounded-xl transition-all duration-150"
                            :class="isCurrentUrl(dashboard()) 
                              ? 'font-bold text-slate-900 bg-slate-105 dark:bg-slate-800 dark:text-slate-100 shadow-sm border border-slate-200/40 dark:border-slate-700/25' 
                              : 'font-medium text-slate-500 hover:text-slate-900 hover:bg-slate-50 dark:text-zinc-400 dark:hover:text-white dark:hover:bg-zinc-900'"
                        >
                            <Link :href="dashboard()">
                                <LayoutGrid class="h-[18px] w-[18px] shrink-0 transition-colors" 
                                            :class="isCurrentUrl(dashboard()) ? 'text-slate-900 dark:text-slate-100' : 'text-slate-400 group-hover:text-slate-650'" />
                                <span class="text-[13.5px] group-data-[collapsible=icon]:hidden select-none">Dashboard</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>

            <!-- MANAGEMENT SECTION (only for super_admin or admin) -->
            <SidebarGroup v-if="user.is_admin || user.is_super_admin" class="px-3 py-2 mt-4 group-data-[collapsible=icon]:px-0 group-data-[collapsible=icon]:py-0 group-data-[collapsible=icon]:mt-0 group-data-[collapsible=icon]:w-full group-data-[collapsible=icon]:flex group-data-[collapsible=icon]:flex-col group-data-[collapsible=icon]:items-center">
                <SidebarGroupLabel class="px-2 text-[10px] font-extrabold uppercase tracking-[0.08em] text-slate-400/80 dark:text-zinc-500 mb-2 group-data-[collapsible=icon]:hidden select-none">
                    Management
                </SidebarGroupLabel>
                <SidebarMenu class="space-y-1 group-data-[collapsible=icon]:items-center group-data-[collapsible=icon]:w-full">
                    <SidebarMenuItem class="group-data-[collapsible=icon]:w-full group-data-[collapsible=icon]:flex group-data-[collapsible=icon]:justify-center">
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl('/portal-admin')"
                            tooltip="Portal Admin (Web)"
                            class="h-10 rounded-xl transition-all duration-150"
                            :class="isCurrentUrl('/portal-admin') 
                              ? 'font-bold text-slate-900 bg-slate-105 dark:bg-slate-800 dark:text-slate-100 shadow-sm border border-slate-200/40 dark:border-slate-700/25' 
                              : 'font-medium text-slate-500 hover:text-slate-900 hover:bg-slate-50 dark:text-zinc-400 dark:hover:text-white dark:hover:bg-zinc-900'"
                        >
                            <Link href="/portal-admin">
                                <Globe class="h-[18px] w-[18px] shrink-0 transition-colors" 
                                       :class="isCurrentUrl('/portal-admin') ? 'text-slate-900 dark:text-slate-100' : 'text-slate-400 group-hover:text-slate-650'" />
                                <span class="text-[13.5px] group-data-[collapsible=icon]:hidden select-none">Portal Admin (Web)</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <SidebarMenuItem class="group-data-[collapsible=icon]:w-full group-data-[collapsible=icon]:flex group-data-[collapsible=icon]:justify-center">
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl('/workos')"
                            tooltip="Manajemen Role User"
                            class="h-10 rounded-xl transition-all duration-150"
                            :class="isCurrentUrl('/workos') 
                              ? 'font-bold text-slate-900 bg-slate-105 dark:bg-slate-800 dark:text-slate-100 shadow-sm border border-slate-200/40 dark:border-slate-700/25' 
                              : 'font-medium text-slate-500 hover:text-slate-900 hover:bg-slate-50 dark:text-zinc-400 dark:hover:text-white dark:hover:bg-zinc-900'"
                        >
                            <Link href="/workos">
                                <Users class="h-[18px] w-[18px] shrink-0 transition-colors" 
                                       :class="isCurrentUrl('/workos') ? 'text-slate-900 dark:text-slate-100' : 'text-slate-400 group-hover:text-slate-650'" />
                                <span class="text-[13.5px] group-data-[collapsible=icon]:hidden select-none">Manajemen Role User</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>

            <!-- EXTERNAL SERVICES SECTION -->
            <SidebarGroup class="px-3 py-2 mt-4 group-data-[collapsible=icon]:px-0 group-data-[collapsible=icon]:py-0 group-data-[collapsible=icon]:mt-0 group-data-[collapsible=icon]:w-full group-data-[collapsible=icon]:flex group-data-[collapsible=icon]:flex-col group-data-[collapsible=icon]:items-center">
                <SidebarGroupLabel class="px-2 text-[10px] font-extrabold uppercase tracking-[0.08em] text-slate-400/80 dark:text-zinc-500 mb-2 group-data-[collapsible=icon]:hidden select-none">
                    Layanan UNUGHA
                </SidebarGroupLabel>
                <SidebarMenu class="space-y-1 group-data-[collapsible=icon]:items-center group-data-[collapsible=icon]:w-full">
                    <SidebarMenuItem class="group-data-[collapsible=icon]:w-full group-data-[collapsible=icon]:flex group-data-[collapsible=icon]:justify-center">
                        <SidebarMenuButton
                            as-child
                            tooltip="Siakad UNUGHA"
                            class="h-10 rounded-xl transition-all duration-200 font-medium text-slate-500 hover:text-slate-900 hover:bg-slate-50 dark:text-zinc-400 dark:hover:text-white dark:hover:bg-zinc-900"
                        >
                            <a href="https://siakad.unugha.ac.id" target="_blank" rel="noopener noreferrer">
                                <GraduationCap class="h-[18px] w-[18px] shrink-0 text-slate-400" />
                                <span class="text-[13.5px] group-data-[collapsible=icon]:hidden select-none">Siakad UNUGHA</span>
                            </a>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <SidebarMenuItem class="group-data-[collapsible=icon]:w-full group-data-[collapsible=icon]:flex group-data-[collapsible=icon]:justify-center">
                        <SidebarMenuButton
                            as-child
                            tooltip="SINTA BIMA"
                            class="h-10 rounded-xl transition-all duration-200 font-medium text-slate-500 hover:text-slate-900 hover:bg-slate-50 dark:text-zinc-400 dark:hover:text-white dark:hover:bg-zinc-900"
                        >
                            <a href="https://bima.kemdikbud.go.id/" target="_blank" rel="noopener noreferrer">
                                <BookOpen class="h-[18px] w-[18px] shrink-0 text-slate-400" />
                                <span class="text-[13.5px] group-data-[collapsible=icon]:hidden select-none">SINTA BIMA</span>
                            </a>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <SidebarMenuItem class="group-data-[collapsible=icon]:w-full group-data-[collapsible=icon]:flex group-data-[collapsible=icon]:justify-center">
                        <SidebarMenuButton
                            as-child
                            tooltip="Web Utama UNUGHA"
                            class="h-10 rounded-xl transition-all duration-200 font-medium text-slate-500 hover:text-slate-900 hover:bg-slate-50 dark:text-zinc-400 dark:hover:text-white dark:hover:bg-zinc-900"
                        >
                            <a href="https://unugha.ac.id" target="_blank" rel="noopener noreferrer">
                                <Globe class="h-[18px] w-[18px] shrink-0 text-slate-400" />
                                <span class="text-[13.5px] group-data-[collapsible=icon]:hidden select-none">Web Utama UNUGHA</span>
                            </a>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>
        </SidebarContent>

        <!-- Sidebar Footer (Nexus layout style with department card & back to public button) -->
        <SidebarFooter class="px-5 py-4 border-t border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-950">
            <!-- Small Copyright -->
            <p class="text-[10px] text-slate-400/80 dark:text-zinc-500 font-semibold text-center mt-4">
                &copy; 2026 Portal FMIKOM
            </p>
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
