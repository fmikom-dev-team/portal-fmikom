<script setup>
import { router } from "@inertiajs/vue3";
import {
	Activity,
	AlertTriangle,
	ArrowUpRight,
	Download,
	Search,
	ShieldAlert,
	Trash2,
	Users,
} from "lucide-vue-next";
import { computed } from "vue";
import { toast } from "../../composables/useWorkOs";

const props = defineProps({
	stats: {
		type: Object,
		default: () => ({
			total_events: 0,
			active_users: 0,
			failed_actions: 0,
			security_incidents: 0,
			api_requests: 0,
			suspicious_activity: 0,
		}),
	},
	recent_events: {
		type: Array,
		default: () => [],
	},
});

const emit = defineEmits(["navigate"]);

// Formatting helper
const formatNumber = (num) => new Intl.NumberFormat("en-US").format(num);
const formatDate = (date) =>
	new Date(date).toLocaleString("en-US", {
		month: "short",
		day: "numeric",
		hour: "2-digit",
		minute: "2-digit",
	});

const resetAuditLogs = () => {
	if (
		confirm(
			"Apakah Anda yakin ingin mereset semua data Audit Logs? Tindakan ini akan menghapus semua aktivitas, insiden keamanan, dan log API secara permanen.",
		)
	) {
		router.post(
			"/workos/audit-logs/clear",
			{},
			{
				onSuccess: () => {
					toast("Audit logs successfully cleared", "success");
				},
				onError: () => {
					toast("Failed to clear audit logs", "error");
				},
			},
		);
	}
};
</script>

<template>
    <div class="w-full px-4 sm:px-8 pt-6 pb-12 min-w-0 space-y-6 animate-fade-in" style="font-family: var(--wos-font)">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-5">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 tracking-tight">Audit Logs Overview</h1>
                <p class="text-[13px] text-gray-500 mt-1">Real-time monitoring of all organization, user, and API activity across environments.</p>
            </div>
            <div class="flex items-center gap-2">
                <button 
                    @click="resetAuditLogs"
                    class="h-[32px] px-3.5 bg-red-50 border border-red-200 rounded-md text-[12.5px] font-semibold text-red-600 hover:bg-red-100 transition-colors shadow-sm flex items-center gap-2"
                >
                    <Trash2 class="w-4 h-4 text-red-500" />
                    Reset Data
                </button>
                <button class="h-[32px] px-3.5 bg-white border border-gray-200 rounded-md text-[12.5px] font-semibold text-gray-700 hover:bg-gray-50 transition-colors shadow-sm flex items-center gap-2">
                    <Download class="w-4 h-4 text-gray-400" />
                    Export Report
                </button>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Events -->
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-md bg-blue-50 flex items-center justify-center">
                        <Activity class="w-4 h-4 text-blue-600" />
                    </div>
                    <span class="text-[13px] font-medium text-gray-500">Total Events</span>
                </div>
                <div class="flex items-end gap-2">
                    <span class="text-2xl font-bold text-gray-900">{{ formatNumber(stats.total_events) }}</span>
                    <span class="text-[12px] font-medium text-emerald-600 flex items-center mb-1">
                        <ArrowUpRight class="w-3 h-3 mr-0.5" /> 12%
                    </span>
                </div>
            </div>

            <!-- Active Users -->
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-md bg-emerald-50 flex items-center justify-center">
                        <Users class="w-4 h-4 text-emerald-600" />
                    </div>
                    <span class="text-[13px] font-medium text-gray-500">Active Users</span>
                </div>
                <div class="flex items-end gap-2">
                    <span class="text-2xl font-bold text-gray-900">{{ formatNumber(stats.active_users) }}</span>
                    <span class="text-[12px] font-medium text-emerald-600 flex items-center mb-1">
                        <ArrowUpRight class="w-3 h-3 mr-0.5" /> 4%
                    </span>
                </div>
            </div>

            <!-- Security Incidents -->
            <div @click="emit('navigate', 'audit-logs.security')" class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm cursor-pointer hover:border-rose-300 transition-colors group">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-md bg-rose-50 flex items-center justify-center group-hover:bg-rose-100 transition-colors">
                        <ShieldAlert class="w-4 h-4 text-rose-600" />
                    </div>
                    <span class="text-[13px] font-medium text-gray-500">Security Incidents</span>
                </div>
                <div class="flex items-end gap-2">
                    <span class="text-2xl font-bold text-gray-900">{{ formatNumber(stats.security_incidents) }}</span>
                    <span class="text-[12px] font-medium text-rose-600 flex items-center mb-1">
                        Requires Action &rarr;
                    </span>
                </div>
            </div>

            <!-- Failed Actions -->
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-md bg-amber-50 flex items-center justify-center">
                        <AlertTriangle class="w-4 h-4 text-amber-600" />
                    </div>
                    <span class="text-[13px] font-medium text-gray-500">Failed Actions</span>
                </div>
                <div class="flex items-end gap-2">
                    <span class="text-2xl font-bold text-gray-900">{{ formatNumber(stats.failed_actions) }}</span>
                    <span class="text-[12px] font-medium text-gray-400 mb-1">Last 24h</span>
                </div>
            </div>
        </div>

        <!-- Recent Events Table -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm mt-6">
            <div class="px-5 py-4 border-b border-gray-200 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-[14px] font-semibold text-gray-900">Recent Activity</h2>
                <div class="relative w-full sm:w-64">
                    <Search class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                    <input type="text" placeholder="Search events..." class="pl-9 pr-4 py-1.5 text-[13px] border border-gray-200 rounded-md bg-gray-50 focus:bg-white focus:ring-1 focus:ring-blue-500 outline-none transition-colors w-full">
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <caption class="sr-only">Tabel Log Aktivitas Terbaru</caption>
                    <thead class="bg-gray-50/50 border-b border-gray-200 text-gray-500">
                        <tr>
                            <th class="px-5 py-3 font-medium">Event</th>
                            <th class="px-5 py-3 font-medium">Actor</th>
                            <th class="px-5 py-3 font-medium">Severity</th>
                            <th class="px-5 py-3 font-medium">IP Address</th>
                            <th class="px-5 py-3 font-medium text-right">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="event in recent_events" :key="event.id" class="hover:bg-gray-50 transition-colors cursor-pointer">
                            <td class="px-5 py-3">
                                <div class="font-medium text-gray-900">{{ event.event_type }}</div>
                                <div class="text-gray-500 text-[12px] mt-0.5 truncate max-w-[200px]">{{ event.request_path || 'Background Job' }}</div>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded bg-gray-100 flex items-center justify-center text-gray-500 font-semibold text-[10px]">
                                        {{ event.actor?.name?.substring(0, 2).toUpperCase() || 'SYS' }}
                                    </div>
                                    <span class="text-gray-700">{{ event.actor?.email || 'System' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <span :class="[
                                    'inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium uppercase tracking-wider',
                                    event.severity === 'critical' || event.severity === 'high' ? 'bg-rose-100 text-rose-700' : 
                                    event.severity === 'warning' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600'
                                ]">
                                    {{ event.severity }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-500">{{ event.ip_address || '-' }}</td>
                            <td class="px-5 py-3 text-right text-gray-500 whitespace-nowrap">{{ formatDate(event.created_at) }}</td>
                        </tr>
                        <tr v-if="!recent_events.length">
                            <td colspan="5" class="px-5 py-12 text-center text-gray-500">
                                <Activity class="w-8 h-8 text-gray-300 mx-auto mb-3" />
                                <p>No recent events recorded in the system.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-gray-200 bg-gray-50/50 rounded-b-lg text-right">
                <button @click="emit('navigate', 'audit-logs.events')" class="text-[13px] font-medium text-blue-600 hover:text-blue-700">View All Events &rarr;</button>
            </div>
        </div>
    </div>
</template>