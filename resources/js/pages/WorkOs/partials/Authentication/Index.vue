<script setup lang="ts">
import { computed } from "vue";
import MotionTabs from "@/components/ui/tabs/MotionTabs.vue";
import Audit from "./Audit.vue";
import Features from "./Features.vue";
import LoginMethods from "./LoginMethods.vue";
import MagicLinks from "./MagicLinks.vue";
import OAuthProviders from "./OAuthProviders.vue";
import Overview from "./Overview.vue";
import PasswordPolicies from "./PasswordPolicies.vue";
import Sessions from "./Sessions.vue";
import SSO from "./SSO.vue";

const props = defineProps<{
	activeTab?: string;
}>();

const currentTab = computed(() =>
	props.activeTab && props.activeTab !== "authentication"
		? props.activeTab
		: "analytics",
);

const tabs = [
	{ id: "analytics", label: "Analytics" },
	{ id: "methods", label: "Methods" },
	{ id: "providers", label: "Providers" },
	{ id: "features", label: "Features" },
	{ id: "sessions", label: "Sessions" },
];

const emit = defineEmits(["navigate"]);

const handleNavigation = (dest: string) => {
	const internalTabs = [
		"analytics",
		"methods",
		"providers",
		"features",
		"sessions",
		"sso",
	];
	if (internalTabs.includes(dest)) {
		emit("navigate", `auth.${dest}`);
	} else {
		emit("navigate", dest);
	}
};
</script>

<template>
    <div class="flex flex-col md:flex-row h-full w-full overflow-hidden">
        <!-- Secondary Sidebar (Desktop Only) -->
        <div class="hidden md:flex w-[240px] shrink-0 border-r border-gray-200 dark:border-zinc-800 bg-[#f9fafb] dark:bg-zinc-900 p-4 flex-col h-full overflow-y-auto wos-scroll">
            <h2 class="text-base font-semibold text-gray-900 dark:text-zinc-100 mb-4 px-3 hidden md:block">Authentication</h2>
            
            <MotionTabs
                :model-value="currentTab"
                :tabs="tabs"
                orientation="vertical"
                variant="sidebar"
                container-class="mb-6"
                @update:model-value="handleNavigation"
            />
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 overflow-y-auto h-full p-4 sm:p-8 lg:p-12 wos-scroll bg-white dark:bg-zinc-900">
            <Overview v-if="currentTab === 'analytics'" @navigate="handleNavigation" />
            <LoginMethods v-else-if="currentTab === 'methods'" @navigate="handleNavigation" />
            <OAuthProviders v-else-if="currentTab === 'providers'" @navigate="handleNavigation" />
            <Features v-else-if="currentTab === 'features'" @navigate="handleNavigation" />
            <Sessions v-else-if="currentTab === 'sessions'" @navigate="handleNavigation" />
            <SSO v-else-if="currentTab === 'sso'" @navigate="handleNavigation" />
            
            <!-- Other components waiting to be built -->
            <div v-else class="text-center py-20">
                <h3 class="text-lg font-medium text-gray-900 dark:text-zinc-100">Coming Soon</h3>
                <p class="text-gray-500 dark:text-zinc-500 mt-2">The {{ currentTab }} section is under development.</p>
            </div>
        </div>
    </div>
</template>
