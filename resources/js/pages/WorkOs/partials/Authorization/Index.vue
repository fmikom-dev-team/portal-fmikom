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
    <div class="w-full min-h-full p-4 sm:p-6 lg:p-8 bg-white dark:bg-zinc-900">
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
</template>
