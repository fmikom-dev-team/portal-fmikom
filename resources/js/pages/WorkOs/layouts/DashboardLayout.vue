<script setup lang="ts">
import { ref } from "vue";
import AppHeader from "../components/ui/AppHeader.vue";
import AppSidebar from "../components/ui/AppSidebar.vue";
import AppToast from "../components/ui/AppToast.vue";

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

defineProps<{
	navGroups: NavGroup[];
	bottomNavItems?: NavItem[];
	activePage: string;
	activeLabel: string;
	pendingCount?: number;
}>();

const emit = defineEmits<(e: "navigate", page: string) => void>();

// Mobile sidebar open/close
const sidebarOpen = ref(false);

// Desktop sidebar collapsed (icon-only mode)
const sidebarCollapsed = ref(false);

function handleNavigate(id: string) {
	emit("navigate", id);
	// Don't close the sidebar if they just opened a category that has a sub-menu
	if (id !== "authentication" && id !== "authorization") {
		sidebarOpen.value = false;
	}
}
</script>

<template>
    <div
        class="wos-app h-screen w-screen flex overflow-hidden bg-[#f9fafb]"
        style="font-family: var(--wos-font)"
    >
        <!-- Mobile overlay -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            leave-active-class="transition-opacity duration-150"
            leave-to-class="opacity-0"
        >
            <div
                v-if="sidebarOpen"
                class="fixed inset-0 z-40 bg-black/30 backdrop-blur-[1px] md:hidden"
                @click="sidebarOpen = false"
                aria-hidden="true"
            />
        </Transition>

        <!-- Sidebar -->
        <AppSidebar
            :nav-groups="navGroups"
            :bottom-nav-items="bottomNavItems ?? []"
            :active-page="activePage"
            :sidebar-open="sidebarOpen"
            :collapsed="sidebarCollapsed"
            @navigate="handleNavigate"
            @close="sidebarOpen = false"
            @toggle-collapse="sidebarCollapsed = !sidebarCollapsed"
        />

        <!-- Main area (takes remaining width) -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <AppHeader
                :active-label="activeLabel"
                @toggle-sidebar="sidebarOpen = !sidebarOpen"
            />

            <main
                id="wos-main"
                class="flex-1 overflow-y-auto wos-scroll bg-[#f9fafb]"
                style="scroll-behavior: smooth"
            >
                <slot />
            </main>
        </div>

        <!-- Global toast -->
        <AppToast />
    </div>
</template>
