<script setup lang="ts">
import { ref } from "vue";
import MotionTabs from "@/components/ui/tabs/MotionTabs.vue";
import Attributes from "./Attributes.vue";
import AuditLogs from "./AuditLogs.vue";
import Features from "./Features.vue";
import Invitations from "./Invitations.vue";
import Roles from "./Roles.vue";
import Settings from "./Settings.vue";
import Users from "./Users.vue";

const props = defineProps<{
	organization?: any;
	roles?: any[];
	users?: any[];
}>();

const emit = defineEmits<(e: "back") => void>();

const activeTab = ref("features");

const tabs = [
	{ id: "features", label: "Features" },
	{ id: "users", label: "Users" },
	{ id: "invites", label: "Invites" },
	{ id: "roles", label: "Roles" },
	{ id: "attributes", label: "Attributes" },
	{ id: "audit-logs", label: "Audit Logs" },
	{ id: "settings", label: "Settings" },
];
</script>

<template>
    <div class="px-4 sm:px-8 pt-6 pb-12 w-full max-w-[1200px]" style="font-family: var(--wos-font)">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm mb-5">
            <a href="#" class="text-[#2563EB] dark:text-blue-400 hover:underline" @click.prevent="emit('back')">Organizations</a>
            <span class="text-[#d1d5db] dark:text-zinc-700">/</span>
            <span class="text-[#6b7280] dark:text-zinc-500">Organization details</span>
        </div>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row items-start gap-4 mb-6">
            <!-- Logo -->
            <div class="w-12 h-12 rounded-xl bg-[#f3f4f6] dark:bg-zinc-800 border border-[#e5e7eb] dark:border-zinc-700 flex items-center justify-center text-lg font-semibold text-[#111827] dark:text-zinc-100 shrink-0 uppercase overflow-hidden">
                <img v-if="organization?.logo_path" :src="organization.logo_path" alt="Organization Logo" class="w-full h-full object-cover" />
                <span v-else>{{ organization?.name ? organization.name.substring(0, 2) : 'TO' }}</span>
            </div>
            
            <div class="flex-1 w-full">
                <div class="flex flex-wrap items-center gap-3 mb-1.5">
                    <h1 class="text-2xl font-semibold text-[#111827] dark:text-zinc-50 tracking-tight leading-none">{{ organization?.name || 'Test Organization' }}</h1>
                </div>
                <div class="flex flex-wrap items-center gap-2 mt-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono text-[#4b5563] dark:text-zinc-400 bg-[#f3f4f6] dark:bg-zinc-800">
                        {{ organization?.code || 'org_01KMQ8PKS7Q4B5TTQDHS2R4QMW' }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-sm text-[#4b5563] dark:text-zinc-400">
                        <svg class="w-3.5 h-3.5 text-[#10b981]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ organization?.domain || 'example.com' }}
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Tabs -->
        <MotionTabs
            v-model="activeTab"
            variant="underline"
            :tabs="tabs"
            container-class="mb-6"
        />

        <!-- Tab Content -->
        <div class="w-full">
            <Features v-if="activeTab === 'features'" :organization="organization" />
            <Users v-else-if="activeTab === 'users'" :users="users" :organization="organization" :roles="roles" />
            <Invitations v-else-if="activeTab === 'invites'" :organization="organization" />
            <Roles v-else-if="activeTab === 'roles'" :roles="roles" :organization="organization" />
            <Attributes v-else-if="activeTab === 'attributes'" :organization="organization" />
            <AuditLogs v-else-if="activeTab === 'audit-logs'" :organization="organization" />
            <Settings v-else-if="activeTab === 'settings'" :organization="organization" />
        </div>
    </div>
</template>