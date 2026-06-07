<script setup lang="ts">
import { computed, ref } from "vue";
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
    <div class="flex flex-col md:flex-row h-full w-full">
        <!-- Secondary Sidebar (Desktop Only) -->
        <div class="hidden md:flex w-[240px] shrink-0 border-r border-gray-200 bg-[#f9fafb] p-4 flex-col h-full overflow-y-auto wos-scroll">
            <h2 class="text-base font-semibold text-gray-900 mb-4 px-3 hidden md:block">Authentication</h2>
            
            <div class="space-y-0.5 mb-6">
                <button
                    v-for="tab in tabs" :key="tab.id"
                    @click="handleNavigation(tab.id)"
                    :class="[
                        'w-full text-left px-3 py-1.5 rounded-md text-[13px] font-medium transition-colors',
                        currentTab === tab.id ? 'bg-gray-200/60 text-gray-900' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
                    ]"
                >
                    {{ tab.label }}
                </button>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-8 lg:p-12 wos-scroll bg-white">
            <Overview v-if="currentTab === 'analytics'" @navigate="handleNavigation" />
            <LoginMethods v-else-if="currentTab === 'methods'" @navigate="handleNavigation" />
            <OAuthProviders v-else-if="currentTab === 'providers'" @navigate="handleNavigation" />
            <Features v-else-if="currentTab === 'features'" @navigate="handleNavigation" />
            <Sessions v-else-if="currentTab === 'sessions'" @navigate="handleNavigation" />
            <SSO v-else-if="currentTab === 'sso'" @navigate="handleNavigation" />
            
            <!-- Other components waiting to be built -->
            <div v-else class="text-center py-20">
                <h3 class="text-lg font-medium text-gray-900">Coming Soon</h3>
                <p class="text-gray-500 mt-2">The {{ currentTab }} section is under development.</p>
            </div>
        </div>
    </div>
</template>
