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
import { computed, ref, watch } from "vue";
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
	new Date(date).toLocaleString("id-ID", {
		month: "short",
		day: "numeric",
		hour: "2-digit",
		minute: "2-digit",
	});

const isResetModalOpen = ref(false);
const searchQuery = ref("");
const selectedSeverity = ref("");
const selectedModule = ref("");

let searchTimeout = null;
function applyFilters() {
	router.visit("/workos/audit-logs", {
		method: "get",
		data: {
			search: searchQuery.value || undefined,
			severity: selectedSeverity.value || undefined,
			module: selectedModule.value || undefined,
		},
		preserveState: true,
		preserveScroll: true,
		only: ["auditRecentEvents"],
	});
}

watch(searchQuery, () => {
	clearTimeout(searchTimeout);
	searchTimeout = setTimeout(() => {
		applyFilters();
	}, 300);
});

watch([selectedSeverity, selectedModule], () => {
	applyFilters();
});


const resetAuditLogs = () => {
	isResetModalOpen.value = true;
};

const confirmReset = () => {
	isResetModalOpen.value = false;
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
};
</script>

<template>
    <div class="w-full px-4 sm:px-8 pt-6 pb-12 min-w-0 space-y-6 animate-fade-in" style="font-family: var(--wos-font)">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 dark:border-zinc-800 pb-5">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-zinc-100 tracking-tight">Audit Logs Overview</h1>
                <p class="text-[13px] text-gray-500 dark:text-zinc-400 mt-1">Real-time monitoring of all organization, user, and API activity across environments.</p>
            </div>
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 w-full sm:w-auto">
                <button 
                    @click="resetAuditLogs"
                    class="h-[32px] px-3.5 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/40 rounded-md text-[12.5px] font-semibold text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors shadow-sm flex items-center justify-center gap-2 cursor-pointer dark:shadow-none flex-1 sm:flex-none"
                >
                    <Trash2 class="w-4 h-4 text-red-500" />
                    Reset Data
                </button>
                <button class="h-[32px] px-3.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-md text-[12.5px] font-semibold text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors shadow-sm flex items-center justify-center gap-2 cursor-pointer dark:shadow-none flex-1 sm:flex-none">
                    <Download class="w-4 h-4 text-gray-400 dark:text-zinc-550" />
                    Export Report
                </button>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Events -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-5 shadow-sm dark:shadow-none">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-md bg-blue-50 dark:bg-blue-950/30 flex items-center justify-center">
                        <Activity class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                    </div>
                    <span class="text-[13px] font-medium text-gray-500 dark:text-zinc-400">Total Events</span>
                </div>
                <div class="flex items-end gap-2">
                    <span class="text-2xl font-bold text-gray-900 dark:text-zinc-100">{{ formatNumber(stats.total_events) }}</span>
                    <span class="text-[12px] font-medium text-emerald-600 dark:text-emerald-450 flex items-center mb-1">
                        <ArrowUpRight class="w-3 h-3 mr-0.5" /> 12%
                    </span>
                </div>
            </div>

            <!-- Active Users -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-5 shadow-sm dark:shadow-none">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-md bg-emerald-50 dark:bg-emerald-950/30 flex items-center justify-center">
                        <Users class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <span class="text-[13px] font-medium text-gray-500 dark:text-zinc-400">Active Users</span>
                </div>
                <div class="flex items-end gap-2">
                    <span class="text-2xl font-bold text-gray-900 dark:text-zinc-100">{{ formatNumber(stats.active_users) }}</span>
                    <span class="text-[12px] font-medium text-emerald-600 dark:text-emerald-450 flex items-center mb-1">
                        <ArrowUpRight class="w-3 h-3 mr-0.5" /> 4%
                    </span>
                </div>
            </div>

            <!-- Security Incidents -->
            <div @click="emit('navigate', 'audit-logs.security')" class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-5 shadow-sm cursor-pointer hover:border-rose-300 dark:hover:border-rose-700 transition-colors group dark:shadow-none">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-md bg-rose-50 dark:bg-rose-950/20 flex items-center justify-center group-hover:bg-rose-100 dark:group-hover:bg-rose-900/30 transition-colors">
                        <ShieldAlert class="w-4 h-4 text-rose-600 dark:text-rose-455" />
                    </div>
                    <span class="text-[13px] font-medium text-gray-500 dark:text-zinc-400">Security Incidents</span>
                </div>
                <div class="flex items-end gap-2">
                    <span class="text-2xl font-bold text-gray-900 dark:text-zinc-100">{{ formatNumber(stats.security_incidents) }}</span>
                    <span class="text-[12px] font-medium text-rose-600 dark:text-rose-400 flex items-center mb-1">
                        Requires Action &rarr;
                    </span>
                </div>
            </div>

            <!-- Failed Actions -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-5 shadow-sm dark:shadow-none">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-md bg-amber-50 dark:bg-amber-950/30 flex items-center justify-center">
                        <AlertTriangle class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                    </div>
                    <span class="text-[13px] font-medium text-gray-500 dark:text-zinc-400">Failed Actions</span>
                </div>
                <div class="flex items-end gap-2">
                    <span class="text-2xl font-bold text-gray-900 dark:text-zinc-100">{{ formatNumber(stats.failed_actions) }}</span>
                    <span class="text-[12px] font-medium text-gray-400 dark:text-zinc-550 mb-1">Last 24h</span>
                </div>
            </div>
        </div>

        <!-- Recent Events Table -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-sm mt-6 dark:shadow-none">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-zinc-800 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-[14px] font-semibold text-gray-900 dark:text-zinc-100">Recent Activity</h2>
                <div class="flex flex-row items-center gap-2 w-full sm:w-auto">
                    <!-- Module Filter -->
                    <select v-model="selectedModule" class="h-[32px] pl-3 pr-8 text-[12px] border border-gray-200 dark:border-zinc-800 rounded-md bg-gray-50 dark:bg-zinc-950 text-gray-700 dark:text-zinc-300 outline-none appearance-none cursor-pointer">
                        <option value="">All Modules</option>
                        <option value="portal">Portal Admin</option>
                        <option value="pagi">Pagi</option>
                        <option value="workos">WorkOS</option>
                        <option value="fast">Fast</option>
                        <option value="trace">Trace</option>
                        <option value="wims">WIMS</option>
                    </select>
                    <!-- Severity Filter -->
                    <select v-model="selectedSeverity" class="h-[32px] pl-3 pr-8 text-[12px] border border-gray-200 dark:border-zinc-800 rounded-md bg-gray-50 dark:bg-zinc-950 text-gray-700 dark:text-zinc-300 outline-none appearance-none cursor-pointer">
                        <option value="">All Severities</option>
                        <option value="info">Info</option>
                        <option value="warning">Warning</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                    <!-- Search Input -->
                    <div class="relative w-full sm:w-64">
                        <Search class="w-4 h-4 text-gray-400 dark:text-zinc-550 absolute left-3 top-1/2 -translate-y-1/2" />
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Search events..." 
                            class="pl-9 pr-4 py-1.5 text-[12px] border border-gray-200 dark:border-zinc-800 rounded-md bg-gray-50 dark:bg-zinc-950 focus:bg-white dark:focus:bg-zinc-900 focus:ring-1 focus:ring-blue-500 outline-none transition-colors w-full text-gray-900 dark:text-zinc-100 placeholder-gray-450 dark:placeholder-zinc-600"
                        />
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <caption class="sr-only">Tabel Log Aktivitas Terbaru</caption>
                    <thead class="bg-gray-50/50 dark:bg-zinc-800/30 border-b border-gray-200 dark:border-zinc-800 text-gray-500 dark:text-zinc-400">
                        <tr>
                            <th class="px-5 py-3 font-medium">Event</th>
                            <th class="px-5 py-3 font-medium">Actor</th>
                            <th class="px-5 py-3 font-medium">Severity</th>
                            <th class="px-5 py-3 font-medium">IP Address</th>
                            <th class="px-5 py-3 font-medium text-right">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                        <tr v-for="event in recent_events" :key="event.id" class="hover:bg-gray-50 dark:bg-zinc-900 dark:hover:bg-zinc-800/40 transition-colors">
                            <td class="px-5 py-3">
                                <div class="font-medium text-gray-900 dark:text-zinc-200">{{ event.event_type }}</div>
                                <div class="text-gray-500 dark:text-zinc-400 text-[12px] mt-0.5 truncate max-w-[240px]">{{ event.request_path || 'Background Job' }}</div>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded bg-gray-100 dark:bg-zinc-800 flex items-center justify-center text-gray-500 dark:text-zinc-300 font-semibold text-[10px]">
                                        {{ event.actor?.name?.substring(0, 2).toUpperCase() || 'SYS' }}
                                    </div>
                                    <span class="text-gray-700 dark:text-zinc-300">{{ event.actor?.email || 'System' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <span :class="[
                                    'inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium uppercase tracking-wider',
                                    event.severity === 'critical' || event.severity === 'high' ? 'bg-rose-100 dark:bg-rose-950/35 text-rose-700 dark:text-rose-455' : 
                                    event.severity === 'warning' ? 'bg-amber-100 dark:bg-amber-950/25 text-amber-700 dark:text-amber-400' : 'bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300'
                                ]">
                                    {{ event.severity }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-500 dark:text-zinc-400">{{ event.ip_address || '-' }}</td>
                            <td class="px-5 py-3 text-right text-gray-500 dark:text-zinc-400 whitespace-nowrap">{{ formatDate(event.created_at) }}</td>
                        </tr>
                        <tr v-if="!recent_events.length">
                            <td colspan="5" class="px-5 py-12 text-center text-gray-500 dark:text-zinc-400">
                                <Activity class="w-8 h-8 text-gray-300 dark:text-zinc-700 mx-auto mb-3" />
                                <p>No recent events recorded in the system.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-gray-200 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-800/30 rounded-b-lg text-right">
                <button @click="emit('navigate', 'audit-logs.events')" class="text-[13px] font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 bg-transparent border-0 cursor-pointer">View All Events &rarr;</button>
            </div>
        </div>

        <!-- Modern Reset Confirmation Dialog (Shadcn style popup) -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="isResetModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 dark:bg-black/75">
                    <div class="absolute inset-0 cursor-default" @click="isResetModalOpen = false"></div>
                    <div class="relative bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-2xl w-full max-w-md shadow-2xl p-6 overflow-hidden flex flex-col space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-red-50 dark:bg-red-950/30 flex items-center justify-center shrink-0">
                                <AlertTriangle class="w-5 h-5 text-red-650 dark:text-red-400" />
                            </div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-zinc-100">Reset Audit Logs?</h3>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-zinc-400 leading-relaxed">
                            Apakah Anda yakin ingin menghapus semua data Audit Logs? Tindakan ini akan menghapus semua aktivitas, insiden keamanan, dan log API secara permanen dari basis data dan tidak dapat dibatalkan.
                        </p>
                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-zinc-900">
                            <button 
                                @click="isResetModalOpen = false" 
                                type="button"
                                class="h-9 px-4 rounded-xl border border-slate-200 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-900 text-xs font-semibold text-gray-700 dark:text-zinc-300 transition-all cursor-pointer"
                            >
                                Batal
                            </button>
                            <button 
                                @click="confirmReset" 
                                type="button"
                                class="h-9 px-4 rounded-xl bg-red-600 hover:bg-red-700 dark:bg-red-750 dark:hover:bg-red-800 text-xs font-semibold text-white transition-all shadow-sm cursor-pointer"
                            >
                                Ya, Hapus Semua
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>