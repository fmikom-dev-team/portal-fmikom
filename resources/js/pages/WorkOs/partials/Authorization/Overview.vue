<script setup lang="ts">
import {
	ArrowRight,
	CheckCircle2,
	FileText,
	Key,
	Shield,
	Users,
} from "lucide-vue-next";
import { computed } from "vue";

const props = defineProps<{
	stats?: any;
	roles: any[];
	permissions: any[];
	users: any[];
}>();

const emit = defineEmits<(e: "navigate", tab: string) => void>();

// Calculate total module role assignments from all users
const totalAssignments = computed(() => {
	return props.users.reduce(
		(acc, user) => acc + (user.module_roles?.length || 0),
		0,
	);
});
</script>

<template>
    <div class="space-y-8 animate-fade-in max-w-[1000px]" style="font-family: var(--wos-font)">
        <!-- Header -->
        <div>
            <h1 class="text-[22px] font-semibold text-gray-900 tracking-tight mb-1">Authorization Overview</h1>
            <p class="text-[13px] text-gray-500">Manage fine-grained access control, security policies, and user role assignments.</p>
        </div>

        <!-- System Status banner -->
        <div class="bg-emerald-50/50 border border-emerald-100 rounded-xl p-4 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-emerald-100/80 flex items-center justify-center text-emerald-600 shrink-0">
                <CheckCircle2 class="w-4.5 h-4.5" />
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[13px] font-semibold text-emerald-950">Authorization Engine Operational</p>
                <p class="text-[11.5px] text-emerald-700/90 mt-0.5">Role-Based Access Control (RBAC) and module policy validations are fully active.</p>
            </div>
            <span class="inline-flex items-center gap-1 bg-emerald-100/50 border border-emerald-200 text-emerald-700 px-2 py-0.5 rounded text-[10.5px] font-semibold">
                Active
            </span>
        </div>

        <!-- Metrics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Roles Card -->
            <div 
                @click="emit('navigate', 'roles')"
                class="bg-white border border-gray-200 hover:border-gray-300 hover:shadow-sm rounded-xl p-5 transition-all cursor-pointer group"
            >
                <div class="flex items-center justify-between">
                    <span class="text-[12px] font-semibold text-gray-400 uppercase tracking-wider">Roles</span>
                    <div class="p-2 rounded-lg bg-gray-50 group-hover:bg-gray-100 transition-colors">
                        <Shield class="w-4 h-4 text-gray-500" />
                    </div>
                </div>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-gray-950 tabular-nums leading-none">{{ roles.length }}</span>
                    <span class="text-[11.5px] text-gray-500 font-medium">defined</span>
                </div>
            </div>

            <!-- Permissions Card -->
            <div 
                @click="emit('navigate', 'permissions')"
                class="bg-white border border-gray-200 hover:border-gray-300 hover:shadow-sm rounded-xl p-5 transition-all cursor-pointer group"
            >
                <div class="flex items-center justify-between">
                    <span class="text-[12px] font-semibold text-gray-400 uppercase tracking-wider">Permissions</span>
                    <div class="p-2 rounded-lg bg-gray-50 group-hover:bg-gray-100 transition-colors">
                        <Key class="w-4 h-4 text-gray-500" />
                    </div>
                </div>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-gray-950 tabular-nums leading-none">{{ permissions.length }}</span>
                    <span class="text-[11.5px] text-gray-500 font-medium">active keys</span>
                </div>
            </div>

            <!-- Role Assignments Card -->
            <div 
                @click="emit('navigate', 'assignments')"
                class="bg-white border border-gray-200 hover:border-gray-300 hover:shadow-sm rounded-xl p-5 transition-all cursor-pointer group"
            >
                <div class="flex items-center justify-between">
                    <span class="text-[12px] font-semibold text-gray-400 uppercase tracking-wider">Assignments</span>
                    <div class="p-2 rounded-lg bg-gray-50 group-hover:bg-gray-100 transition-colors">
                        <Users class="w-4 h-4 text-gray-500" />
                    </div>
                </div>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-gray-950 tabular-nums leading-none">{{ totalAssignments }}</span>
                    <span class="text-[11.5px] text-gray-500 font-medium">user-role links</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Guides -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Quick Actions -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
                <h2 class="text-[14px] font-semibold text-gray-950">Quick Actions</h2>
                <div class="divide-y divide-gray-100">
                    <button 
                        @click="emit('navigate', 'roles')"
                        class="w-full flex items-center justify-between py-3 text-left group first:pt-0"
                    >
                        <div>
                            <p class="text-[13px] font-semibold text-gray-800 group-hover:text-gray-950 transition-colors">Configure System Roles</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">Define new global or module-specific roles and assign groups.</p>
                        </div>
                        <ArrowRight class="w-4 h-4 text-gray-400 group-hover:text-gray-600 group-hover:translate-x-1 transition-all" />
                    </button>

                    <button 
                        @click="emit('navigate', 'permissions')"
                        class="w-full flex items-center justify-between py-3 text-left group"
                    >
                        <div>
                            <p class="text-[13px] font-semibold text-gray-800 group-hover:text-gray-950 transition-colors">Manage Access Keys</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">Register granular API or action-level authorization permissions.</p>
                        </div>
                        <ArrowRight class="w-4 h-4 text-gray-400 group-hover:text-gray-600 group-hover:translate-x-1 transition-all" />
                    </button>

                    <button 
                        @click="emit('navigate', 'assignments')"
                        class="w-full flex items-center justify-between py-3 text-left group last:pb-0"
                    >
                        <div>
                            <p class="text-[13px] font-semibold text-gray-800 group-hover:text-gray-950 transition-colors">Assign User Module Roles</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">Map users to specific roles inside organizations or portal modules.</p>
                        </div>
                        <ArrowRight class="w-4 h-4 text-gray-400 group-hover:text-gray-600 group-hover:translate-x-1 transition-all" />
                    </button>
                </div>
            </div>

            <!-- RBAC Architecture Guide -->
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 space-y-4">
                <h2 class="text-[14px] font-semibold text-gray-950 flex items-center gap-2">
                    <FileText class="w-4 h-4 text-gray-500" />
                    <span>Access Control Flow</span>
                </h2>
                
                <div class="relative pl-6 space-y-4">
                    <!-- vertical connector line -->
                    <div class="absolute left-[7.5px] top-2 bottom-2 w-0.5 bg-gray-200" />

                    <!-- Step 1 -->
                    <div class="relative">
                        <div class="absolute -left-[23px] top-0.5 w-4.5 h-4.5 rounded-full bg-white border-2 border-gray-300 flex items-center justify-center">
                            <span class="text-[9px] font-bold text-gray-500">1</span>
                        </div>
                        <p class="text-[12.5px] font-semibold text-gray-800">Permissions</p>
                        <p class="text-[11px] text-gray-400 leading-normal mt-0.5">Granular action-level keys (e.g., <code>users:write</code>) representing discrete tasks.</p>
                    </div>

                    <!-- Step 2 -->
                    <div class="relative">
                        <div class="absolute -left-[23px] top-0.5 w-4.5 h-4.5 rounded-full bg-white border-2 border-gray-300 flex items-center justify-center">
                            <span class="text-[9px] font-bold text-gray-500">2</span>
                        </div>
                        <p class="text-[12.5px] font-semibold text-gray-800">Roles</p>
                        <p class="text-[11px] text-gray-400 leading-normal mt-0.5">Roles collect permissions into logical sets (e.g., <code>Administrator</code> or <code>Editor</code>).</p>
                    </div>

                    <!-- Step 3 -->
                    <div class="relative">
                        <div class="absolute -left-[23px] top-0.5 w-4.5 h-4.5 rounded-full bg-white border-2 border-gray-300 flex items-center justify-center">
                            <span class="text-[9px] font-bold text-gray-500">3</span>
                        </div>
                        <p class="text-[12.5px] font-semibold text-gray-800">Assignments</p>
                        <p class="text-[11px] text-gray-400 leading-normal mt-0.5">Users are assigned a role within a specific module or organization context.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
