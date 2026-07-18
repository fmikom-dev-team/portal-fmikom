<script setup lang="ts">
import { computed } from "vue";
import MotionTabs from "@/components/ui/tabs/MotionTabs.vue";
import AccessControl from "./AccessControl.vue";
import Audit from "./Audit.vue";
import Overview from "./Overview.vue";
import Permissions from "./Permissions.vue";
import RoleAssignments from "./RoleAssignments.vue";
import Roles from "./Roles.vue";

const props = defineProps<{
	activeTab?: string;
	roles?: any[];
	permissions?: any[];
	users?: any[];
	modules?: any[];
	stats?: any;
	searchQuery?: string;
}>();

const currentTab = computed(() =>
	props.activeTab && props.activeTab !== "auth.roles"
		? props.activeTab
		: "overview",
);

const tabs = [
	{ id: "overview", label: "Overview" },
	{ id: "roles", label: "Roles" },
	{ id: "permissions", label: "Permissions" },
	{ id: "assignments", label: "Role Assignments" },
	{ id: "access-control", label: "Access Control" },
];

const relatedTabs = [{ id: "audit-logs", label: "Audit" }];

const emit = defineEmits(["navigate"]);

const handleNavigation = (dest: string) => {
	const internalTabs = [
		"overview",
		"roles",
		"permissions",
		"assignments",
		"access-control",
	];
	if (internalTabs.includes(dest)) {
		emit("navigate", `authz.${dest}`);
	} else {
		emit("navigate", dest);
	}
};
</script>

<template>
    <div class="flex flex-col md:flex-row h-full w-full overflow-hidden">
        <!-- Secondary Sidebar (Desktop Only) -->
        <div class="hidden md:flex w-[240px] shrink-0 border-r border-gray-200 dark:border-zinc-700 bg-[#f9fafb] dark:bg-zinc-900 p-4 flex-col h-full overflow-y-auto wos-scroll">
            <h2 class="text-base font-semibold text-gray-900 dark:text-zinc-100 mb-4 px-3 hidden md:block">Authorization</h2>
            
            <MotionTabs
                :model-value="currentTab"
                :tabs="tabs"
                orientation="vertical"
                variant="sidebar"
                container-class="mb-6"
                @update:model-value="handleNavigation"
            />

            <h3 class="text-[11px] font-semibold text-gray-400 dark:text-zinc-500 uppercase tracking-wider mb-2 px-3">Related</h3>
            <div class="space-y-0.5">
                <button
                    v-for="tab in relatedTabs" :key="tab.id"
                    @click="emit('navigate', tab.id)"
                    class="w-full text-left px-3 py-1.5 rounded-md text-[13px] font-medium text-gray-600 dark:text-zinc-400 hover:bg-gray-100 dark:hover:bg-zinc-800 dark:bg-zinc-800 hover:text-gray-900 transition-colors border-0 cursor-pointer bg-transparent"
                >
                    {{ tab.label }}
                </button>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 overflow-y-auto h-full p-4 sm:p-8 lg:p-12 wos-scroll bg-white dark:bg-zinc-900">
            <Overview v-if="currentTab === 'overview'" :stats="stats" :roles="roles ?? []" :permissions="permissions ?? []" :users="users ?? []" @navigate="handleNavigation" />
            <Roles v-else-if="currentTab === 'roles'" :roles="roles ?? []" :permissions="permissions ?? []" :search-query="searchQuery" @navigate="handleNavigation" />
            <Permissions v-else-if="currentTab === 'permissions'" :permissions="permissions ?? []" :search-query="searchQuery" @navigate="handleNavigation" />
            <RoleAssignments v-else-if="currentTab === 'assignments'" :users="users ?? []" :roles="roles ?? []" :modules="modules ?? []" :search-query="searchQuery" @navigate="handleNavigation" />
            <AccessControl v-else-if="currentTab === 'access-control'" :roles="roles ?? []" :permissions="permissions ?? []" :search-query="searchQuery" @navigate="handleNavigation" />
            <Audit v-else-if="currentTab === 'audit'" @navigate="handleNavigation" />
            
            <div v-else class="text-center py-20">
                <h3 class="text-lg font-medium text-gray-900 dark:text-zinc-100">Coming Soon</h3>
                <p class="text-gray-500 dark:text-zinc-400 mt-2">The {{ currentTab }} section is under development.</p>
            </div>
        </div>
    </div>
</template>
