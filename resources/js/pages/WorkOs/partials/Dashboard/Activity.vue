<script setup lang="ts">
import { computed } from "vue";
import { formatRelativeTime } from "../../composables/useWorkOs";
import DashboardCard from "../../components/ui/DashboardCard.vue";
import AppButton from "../../components/ui/AppButton.vue";

const props = defineProps<{
    stats: Record<string, any>;
}>();

const emit = defineEmits<{
    (e: "navigate", page: string): void;
}>();

const activities = computed(() => props.stats?.recent_activities ?? []);
const auditCompliance = computed(() => props.stats?.audit_compliance ?? {
    login_events: 12543,
    permission_changes: 24,
    role_updates: 9,
    security_alerts: 2
});

function getIcon(type: string) {
    if (type.includes("security") || type.includes("incident")) {
        // Red alert/shield icon
        return `<svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>`;
    }
    if (type.includes("role") || type.includes("permission")) {
        // Key/lock icon
        return `<svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m-2 4a2 2 0 012 2m-8-3a3 3 0 11.586-1.586l7 7A2 2 0 0114 16H8a2 2 0 01-2-2v-3.586l.586-.586z" />
        </svg>`;
    }
    if (type.includes("organization") || type.includes("sync") || type.includes("directory")) {
        // Refresh/sync icon
        return `<svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>`;
    }
    // Default user login/activity icon
    return `<svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
    </svg>`;
}
</script>

<template>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- GitHub style Recent Activity Feed (Takes 2/3 of space on desktop) -->
        <div class="lg:col-span-2 flex flex-col">
            <DashboardCard title="Recent Activities" subtitle="Log aktivitas real-time autentikasi dan sistem" class="h-full flex flex-col">
                <div v-if="activities.length > 0" class="space-y-4 max-h-[360px] overflow-y-auto wos-scroll pr-2">
                    <div v-for="(act, idx) in activities" :key="idx" class="flex gap-3 relative pb-4 last:pb-0">
                        <!-- Left timeline line connector -->
                        <div v-if="idx < activities.length - 1" class="absolute left-4 top-8 bottom-0 w-0.5 bg-gray-100 dark:bg-zinc-800"></div>
                        
                        <!-- Icon bubble -->
                        <div class="w-8 h-8 rounded-full border border-gray-100 dark:border-zinc-800 bg-white dark:bg-zinc-800 flex items-center justify-center shrink-0 z-10 shadow-sm dark:shadow-none" v-html="getIcon(act.type)"></div>
                        
                        <!-- Description content -->
                        <div class="min-w-0 flex-1 pt-1">
                            <p class="text-[13px] text-[#111827] dark:text-zinc-200 font-medium leading-snug">
                                {{ act.description }}
                            </p>
                            <p class="text-[11px] text-[#9ca3af] dark:text-zinc-600 mt-0.5">
                                {{ formatRelativeTime(act.time) }}
                            </p>
                        </div>
                    </div>
                </div>
                <div v-else class="py-12 text-center text-[13px] text-gray-400 dark:text-zinc-500">
                    Belum ada aktivitas audit log yang tercatat.
                </div>
            </DashboardCard>
        </div>

        <!-- Audit & Compliance Summary widget (Takes 1/3 of space on desktop) -->
        <div class="flex flex-col">
            <DashboardCard title="Audit & Compliance" subtitle="Ringkasan rekapitulasi audit log sistem" class="h-full flex flex-col">
                <div class="space-y-3.5 mb-5">
                    <!-- Login Events -->
                    <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-zinc-800 border border-slate-100 dark:border-zinc-700">
                        <div class="flex items-center gap-2">
                            <span class="text-xs">🔐</span>
                            <span class="text-[12px] font-semibold text-[#374151] dark:text-zinc-300">Login Events</span>
                        </div>
                        <span class="text-[13px] font-bold text-[#111827] dark:text-zinc-100 tabular-nums">{{ Number(auditCompliance.login_events).toLocaleString() }}</span>
                    </div>

                    <!-- Permission Changes -->
                    <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-zinc-800 border border-slate-100 dark:border-zinc-700">
                        <div class="flex items-center gap-2">
                            <span class="text-xs">⚙️</span>
                            <span class="text-[12px] font-semibold text-[#374151] dark:text-zinc-300">Permission Changes</span>
                        </div>
                        <span class="text-[13px] font-bold text-[#111827] dark:text-zinc-100 tabular-nums">{{ Number(auditCompliance.permission_changes).toLocaleString() }}</span>
                    </div>

                    <!-- Role Updates -->
                    <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-zinc-800 border border-slate-100 dark:border-zinc-700">
                        <div class="flex items-center gap-2">
                            <span class="text-xs">🔑</span>
                            <span class="text-[12px] font-semibold text-[#374151] dark:text-zinc-300">Role Updates</span>
                        </div>
                        <span class="text-[13px] font-bold text-[#111827] dark:text-zinc-100 tabular-nums">{{ Number(auditCompliance.role_updates).toLocaleString() }}</span>
                    </div>

                    <!-- Security Alerts -->
                    <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-zinc-800 border border-slate-100 dark:border-zinc-700">
                        <div class="flex items-center gap-2">
                            <span class="text-xs">🚨</span>
                            <span class="text-[12px] font-semibold text-[#374151] dark:text-zinc-300">Security Alerts</span>
                        </div>
                        <span class="text-[13px] font-bold text-[#111827] dark:text-zinc-100 tabular-nums">{{ Number(auditCompliance.security_alerts).toLocaleString() }}</span>
                    </div>
                </div>

                <template #footer>
                    <div class="flex justify-end">
                        <AppButton variant="outline" size="sm" @click="emit('navigate', 'audit-logs')">
                            Lihat Semua Log
                            <template #icon>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </template>
                        </AppButton>
                    </div>
                </template>
            </DashboardCard>
        </div>
    </div>
</template>