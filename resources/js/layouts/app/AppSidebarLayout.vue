<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import {
	BookOpen,
	Globe,
	GraduationCap,
	Home,
	LayoutGrid,
	PenLine,
	Users,
} from "lucide-vue-next";
import { computed, ref } from "vue";
import AppContent from "@/components/AppContent.vue";
import AppShell from "@/components/AppShell.vue";
import AppSidebar from "@/components/AppSidebar.vue";
import AppSidebarHeader from "@/components/AppSidebarHeader.vue";
import { useCurrentUrl } from "@/composables/useCurrentUrl";
import { dashboard } from "@/routes";
import type { BreadcrumbItem } from "@/types";
import { useLoadingState } from "@/composables/useLoadingState";
import DashboardSkeleton from "@/components/skeletons/DashboardSkeleton.vue";
import TableSkeleton from "@/components/skeletons/TableSkeleton.vue";
import FormSkeleton from "@/components/skeletons/FormSkeleton.vue";
import PortfolioSkeleton from "@/components/skeletons/PortfolioSkeleton.vue";
import CVBuilderSkeleton from "@/components/skeletons/CVBuilderSkeleton.vue";
import ChatSkeleton from "@/components/skeletons/ChatSkeleton.vue";
import NewsSkeleton from "@/components/skeletons/NewsSkeleton.vue";
import UserProfileSkeleton from "@/components/skeletons/UserProfileSkeleton.vue";

type Props = {
	breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
	breadcrumbs: () => [],
});

const page = usePage();
const user = computed(
	() => page.props.auth?.user || ({} as Record<string, any>),
);
const { isCurrentUrl } = useCurrentUrl();

const { isLoading, loadingType } = useLoadingState();

const isPortalDashboard = computed(() => {
	return page.component === "Dashboard";
});

import { onMounted, onUnmounted, watch } from "vue";

onMounted(() => {
	document.documentElement.classList.add("app-sidebar-layout-active");
	document.body.classList.add("app-sidebar-layout-active");
});

watch(
	isPortalDashboard,
	(newValue) => {
		if (newValue) {
			document.documentElement.classList.add("portal-dashboard-mobile-bg");
			document.body.classList.add("portal-dashboard-mobile-bg");
		} else {
			document.documentElement.classList.remove("portal-dashboard-mobile-bg");
			document.body.classList.remove("portal-dashboard-mobile-bg");
		}
	},
	{ immediate: true }
);

onUnmounted(() => {
	document.documentElement.classList.remove("portal-dashboard-mobile-bg");
	document.body.classList.remove("portal-dashboard-mobile-bg");
	document.documentElement.classList.remove("app-sidebar-layout-active");
	document.body.classList.remove("app-sidebar-layout-active");
});

const isScrolled = ref(false);
const handleScroll = (e: Event) => {
	const target = e.target as HTMLElement;
	isScrolled.value = target.scrollTop > 2;
};
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <!-- bg-transparent on AppContent and blue on wrapper enforced via scoped CSS below -->
        <AppContent variant="sidebar" class="overflow-x-hidden pb-24 md:pb-0 relative z-[1]">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" :is-scrolled="isScrolled" />
            <!-- White content card shows rounded-t and shadow ONLY when scrolled on mobile -->
            <div class="md:contents">
                <div 
                    @scroll="handleScroll"
                    class="bg-white md:rounded-none md:shadow-none mt-[68px] md:mt-0 min-h-[calc(100dvh-68px)] md:min-h-0 max-md:fixed max-md:top-[68px] max-md:bottom-0 max-md:left-0 max-md:right-0 max-md:mt-0 max-md:min-h-0 max-md:overflow-y-auto max-md:pb-24 max-md:z-40 transition-all duration-300 ease-out"
                    :class="[
                        isScrolled 
                            ? 'rounded-t-2xl border-t border-slate-200/80 dark:border-zinc-800 shadow-sm' 
                            : 'rounded-none shadow-none'
                    ]"
                >
                    <div 
                        class="hidden max-md:block sticky top-0 left-0 right-0 h-[8px] bg-gradient-to-b from-black/5 to-transparent pointer-events-none z-50 transition-opacity duration-300 ease-out rounded-t-2xl"
                        :class="isScrolled ? 'opacity-100' : 'opacity-0'"
                    ></div>
                    <template v-if="isLoading">
                        <div class="p-6 sm:p-8 lg:p-10 w-full animate-pulse">
                            <DashboardSkeleton v-if="loadingType === 'Dashboard'" />
                            <TableSkeleton v-else-if="loadingType === 'Table'" />
                            <FormSkeleton v-else-if="loadingType === 'Form'" />
                            <PortfolioSkeleton v-else-if="loadingType === 'Portfolio'" />
                            <CVBuilderSkeleton v-else-if="loadingType === 'CVBuilder'" />
                            <ChatSkeleton v-else-if="loadingType === 'Chat'" />
                            <NewsSkeleton v-else-if="loadingType === 'News'" />
                            <UserProfileSkeleton v-else-if="loadingType === 'UserProfile'" />
                            <FormSkeleton v-else />
                        </div>
                    </template>
                    <slot v-else />
                </div>
            </div>
        </AppContent>

        <!-- Bottom Navbar for Mobile Mode (Minimalist & Clean SaaS style) -->
        <nav class="fixed bottom-0 left-0 z-50 w-full h-[64px] bg-white/95 dark:bg-zinc-950/95 border-t border-slate-200/80 dark:border-zinc-900 rounded-t-2xl shadow-sm md:hidden flex items-center justify-around px-2 pb-safe backdrop-blur-md">
            <!-- Item 1: Beranda -->
            <Link
                :href="dashboard()"
                class="flex flex-col items-center justify-center gap-1 py-1 flex-1 transition-all duration-200"
                :class="isCurrentUrl(dashboard()) && !isCurrentUrl('/portal-admin') && !isCurrentUrl('/workos') ? 'text-slate-900 dark:text-white font-bold' : 'text-slate-400 hover:text-slate-600 font-medium'"
            >
                <Home class="h-[18px] w-[18px] stroke-[2]" />
                <span class="text-[9px] tracking-tight uppercase">Beranda</span>
            </Link>

            <!-- Item 2: Admin (super-admin/admin) atau Siakad (user biasa) -->
            <template v-if="user.is_admin || user.is_super_admin">
                <Link
                    href="/portal-admin"
                    class="flex flex-col items-center justify-center gap-1 py-1 flex-1 transition-all duration-200"
                    :class="isCurrentUrl('/portal-admin') ? 'text-slate-900 dark:text-white font-bold' : 'text-slate-400 hover:text-slate-600 font-medium'"
                >
                    <Globe class="h-[18px] w-[18px] stroke-[2]" />
                    <span class="text-[9px] tracking-tight uppercase">Portal</span>
                </Link>
            </template>
            <template v-else>
                <a
                    href="https://siakad.unugha.ac.id"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex flex-col items-center justify-center gap-1 py-1 flex-1 transition-all duration-200 text-slate-400 hover:text-slate-600 font-medium"
                >
                    <GraduationCap class="h-[18px] w-[18px] stroke-[2]" />
                    <span class="text-[9px] tracking-tight uppercase">Siakad</span>
                </a>
            </template>

            <!-- Item 3: Center Floating — Buat Berita (admin) / Portal Modules (user biasa) -->
            <div class="relative flex flex-col items-center justify-center w-16 flex-1 gap-1 py-1">
                <template v-if="user.is_admin || user.is_super_admin">
                    <!-- floating button (absolute, tidak mempengaruhi flow) -->
                    <Link
                        href="/portal-admin/posts/create"
                        class="absolute -top-7 flex items-center justify-center w-[48px] h-[48px] rounded-xl bg-slate-900 dark:bg-slate-50 text-white dark:text-slate-950 shadow-sm border border-slate-800 dark:border-slate-200 ring-[4px] ring-white dark:ring-zinc-950 transition-transform active:scale-95 z-20"
                    >
                        <PenLine class="h-[20px] w-[20px] stroke-[2]" />
                    </Link>
                    <!-- placeholder transparan setinggi icon agar label sejajar -->
                    <div class="h-[18px] w-[18px] opacity-0 shrink-0"></div>
                    <span class="text-[9px] font-bold text-slate-500">POSTING</span>
                </template>
                <template v-else>
                    <!-- floating button (absolute, tidak mempengaruhi flow) -->
                    <Link
                        :href="dashboard()"
                        class="absolute -top-7 flex items-center justify-center w-[48px] h-[48px] rounded-xl bg-slate-900 dark:bg-slate-50 text-white dark:text-slate-950 shadow-sm border border-slate-800 dark:border-slate-200 ring-[4px] ring-white dark:ring-zinc-950 transition-transform active:scale-95 z-20"
                    >
                        <LayoutGrid class="h-[20px] w-[20px] stroke-[2]" />
                    </Link>
                    <!-- placeholder transparan setinggi icon agar label sejajar -->
                    <div class="h-[18px] w-[18px] opacity-0 shrink-0"></div>
                    <span class="text-[9px] font-bold text-slate-500">PORTAL</span>
                </template>
            </div>

            <!-- Item 4: WorkOS (super-admin saja) / Bima (semua lainnya) -->
            <template v-if="user.is_super_admin">
                <Link
                    href="/workos"
                    class="flex flex-col items-center justify-center gap-1 py-1 flex-1 transition-all duration-200"
                    :class="isCurrentUrl('/workos') ? 'text-slate-900 dark:text-white font-bold' : 'text-slate-400 hover:text-slate-600 font-medium'"
                >
                    <Users class="h-[18px] w-[18px] stroke-[2]" />
                    <span class="text-[9px] tracking-tight uppercase">Manajemen</span>
                </Link>
            </template>
            <template v-else>
                <a
                    href="https://bima.kemdikbud.go.id/"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex flex-col items-center justify-center gap-1 py-1 flex-1 transition-all duration-200 text-slate-400 hover:text-slate-600 font-medium"
                >
                    <BookOpen class="h-[18px] w-[18px] stroke-[2]" />
                    <span class="text-[9px] tracking-tight uppercase">Bima</span>
                </a>
            </template>

            <!-- Item 5: SIAKAD (admin/super-admin) / Website (user biasa) -->
            <template v-if="user.is_admin || user.is_super_admin">
                <a
                    href="https://siakad.unugha.ac.id"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex flex-col items-center justify-center gap-1 py-1 flex-1 transition-all duration-200 text-slate-400 hover:text-slate-600 font-medium"
                >
                    <GraduationCap class="h-[18px] w-[18px] stroke-[2]" />
                    <span class="text-[9px] tracking-tight uppercase">SIAKAD</span>
                </a>
            </template>
            <template v-else>
                <a
                    href="https://unugha.ac.id"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex flex-col items-center justify-center gap-1 py-1 flex-1 transition-all duration-200 text-slate-400 hover:text-slate-600 font-medium"
                >
                    <Globe class="h-[18px] w-[18px] stroke-[2]" />
                    <span class="text-[9px] tracking-tight uppercase">Website</span>
                </a>
            </template>
        </nav>
    </AppShell>
</template>

<style>
/* Mobile: clean fixed slate background — stays fixed so rounded-t of content always shows against it */
@media (max-width: 767px) {
    html.app-sidebar-layout-active, body.app-sidebar-layout-active {
        overflow: hidden !important;
        height: 100% !important;
        background: #0f172a !important;
        background-attachment: fixed !important;
    }
    html.portal-dashboard-mobile-bg,
    body.portal-dashboard-mobile-bg {
        background: #2563eb !important;
    }
    /* Make sidebar containers transparent so body slate background shows through */
    [data-slot="sidebar-wrapper"] {
        background: transparent !important;
    }
    [data-slot="sidebar-inset"] {
        background-color: transparent !important;
    }
}
</style>
