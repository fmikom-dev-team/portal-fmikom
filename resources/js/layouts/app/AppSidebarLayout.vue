<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
import { computed, ref, onMounted, onUnmounted, watch } from "vue";
import AppSidebar from "@/components/AppSidebar.vue";
import AppSidebarHeader from "@/components/AppSidebarHeader.vue";
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

const userRole = computed(() => {
	if (!user.value) return "";
	return String(user.value.user_type || user.value.role || "").toLowerCase();
});

const isSuperAdminOrAdmin = computed(() => {
	if (!user.value) return false;
	if (user.value.is_super_admin || user.value.is_admin) return true;
	return ["super_admin", "super-admin", "admin"].includes(userRole.value);
});

// Hide sidebar for Mahasiswa, Alumni, and Mitra
const isStudentAlumniMitra = computed(() => {
	if (isSuperAdminOrAdmin.value) return false;
	return ["mahasiswa", "alumni", "mitra"].includes(userRole.value);
});

const isPortalDashboard = computed(() => {
	return page.component === "Dashboard";
});

// Mobile background styling class on body
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

// Sidebar states
const sidebarCollapsed = ref(false);
const mobileOpen = ref(false);

const { isLoading, loadingType } = useLoadingState();

const isScrolled = ref(false);
const handleScroll = (e: Event) => {
	const target = e.target as HTMLElement;
	isScrolled.value = target.scrollTop > 2;
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 max-md:bg-transparent dark:bg-zinc-950 font-sans text-slate-900 dark:text-zinc-100 antialiased flex flex-col transition-colors duration-300">
        <!-- Render Sidebar only for Admin / Dosen / Staff / Super Admin -->
        <AppSidebar
            v-if="!isStudentAlumniMitra"
            v-model:collapsed="sidebarCollapsed"
            v-model:mobileOpen="mobileOpen"
        />

        <!-- Main Content Area with Dynamic Left Margin -->
        <div
            class="flex-1 flex flex-col min-w-0 transition-all duration-300 relative"
            :class="[
                isStudentAlumniMitra 
                    ? 'ml-0 w-full' 
                    : (sidebarCollapsed ? 'lg:ml-[72px]' : 'lg:ml-[260px]')
            ]"
        >
            <!-- STICKY TOP NAVBAR -->
            <AppSidebarHeader
                :is-scrolled="isScrolled"
                :sidebar-collapsed="sidebarCollapsed"
                :hide-sidebar="isStudentAlumniMitra"
                @toggle-mobile-sidebar="mobileOpen = !mobileOpen"
                @toggle-collapse="sidebarCollapsed = !sidebarCollapsed"
            />

            <!-- MAIN CONTENT AREA WITH DYNAMIC SCROLL ROUNDED SHEET EFFECT ON MOBILE -->
            <main class="flex-1 overflow-x-hidden relative z-[1] pb-24 md:pb-0">
                <div class="md:contents">
                    <div 
                        @scroll="handleScroll"
                        class="bg-white dark:bg-zinc-950 md:rounded-none md:shadow-none mt-[68px] md:mt-0 min-h-[calc(100dvh-68px)] md:min-h-0 max-md:fixed max-md:top-[68px] max-md:bottom-0 max-md:left-0 max-md:right-0 max-md:mt-0 max-md:min-h-0 max-md:overflow-y-auto max-md:pb-24 max-md:z-40 transition-all duration-300 ease-out"
                        style="top: calc(68px + env(safe-area-inset-top));"
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
                            <div class="p-4 sm:p-6 lg:p-8 w-full animate-pulse">
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
                        <Transition v-else name="page-slide" mode="out-in">
                            <div :key="$page.url" class="grow w-full">
                                <slot />
                            </div>
                        </Transition>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<style>
/* Mobile: clean fixed slate/blue background - stays fixed so rounded-t content card slides up when scrolled ("efek melengkung saat scroll") */
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
}
</style>
