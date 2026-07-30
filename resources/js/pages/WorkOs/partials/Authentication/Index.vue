<script setup lang="ts">
import { computed } from "vue";
import MotionTabs from "@/components/ui/tabs/MotionTabs.vue";
import Features from "./Features.vue";
import LoginMethods from "./LoginMethods.vue";
import OAuthProviders from "./OAuthProviders.vue";
import Overview from "./Overview.vue";
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
    <div class="h-full w-full p-4 sm:p-8 lg:p-12 overflow-y-auto wos-scroll bg-white dark:bg-zinc-900">
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
</template>
