<script setup lang="ts">
import { computed } from "vue";
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
    <div class="flex flex-col md:flex-row h-full w-full">
        <!-- Secondary Sidebar (Desktop Only) -->
        <div class="hidden md:flex w-[240px] shrink-0 border-r border-gray-200 bg-[#f9fafb] p-4 flex-col h-full overflow-y-auto wos-scroll">
            <h2 class="text-base font-semibold text-gray-900 mb-4 px-3 hidden md:block">Authorization</h2>
            
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

            <h3 class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2 px-3">Related</h3>
            <div class="space-y-0.5">
                <button
                    v-for="tab in relatedTabs" :key="tab.id"
                    @click="emit('navigate', tab.id)"
                    class="w-full text-left px-3 py-1.5 rounded-md text-[13px] font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors"
                >
                    {{ tab.label }}
                </button>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-8 lg:p-12 wos-scroll bg-white">
            <Overview v-if="currentTab === 'overview'" :stats="stats" :roles="roles ?? []" :permissions="permissions ?? []" :users="users ?? []" @navigate="handleNavigation" />
            <Roles v-else-if="currentTab === 'roles'" :roles="roles ?? []" :permissions="permissions ?? []" @navigate="handleNavigation" />
            <Permissions v-else-if="currentTab === 'permissions'" :permissions="permissions ?? []" @navigate="handleNavigation" />
            <RoleAssignments v-else-if="currentTab === 'assignments'" :users="users ?? []" :roles="roles ?? []" :modules="modules ?? []" @navigate="handleNavigation" />
            <AccessControl v-else-if="currentTab === 'access-control'" :roles="roles ?? []" :permissions="permissions ?? []" @navigate="handleNavigation" />
            <Audit v-else-if="currentTab === 'audit'" @navigate="handleNavigation" />
            
            <div v-else class="text-center py-20">
                <h3 class="text-lg font-medium text-gray-900">Coming Soon</h3>
                <p class="text-gray-500 mt-2">The {{ currentTab }} section is under development.</p>
            </div>
        </div>
    </div>
</template>
