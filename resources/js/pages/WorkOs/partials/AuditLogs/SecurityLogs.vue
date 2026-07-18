<script setup>
import { router } from "@inertiajs/vue3";
import axios from "axios";
import {
	AlertTriangle,
	CheckCircle2,
	ChevronRight,
	Filter,
	Search,
	ShieldAlert,
	X,
	XCircle,
} from "lucide-vue-next";
import { ref, watch } from "vue";

const debounce = (fn, delay) => {
	let timeoutId;
	return (...args) => {
		clearTimeout(timeoutId);
		timeoutId = setTimeout(() => fn(...args), delay);
	};
};

const incidents = ref({ data: [], links: [] });
const search = ref("");
const isDetailModalOpen = ref(false);
const selectedIncident = ref(null);
const isLoading = ref(false);

const fetchIncidents = debounce(async (page = 1) => {
	isLoading.value = true;
	try {
		const response = await axios.get("/workos/audit-logs/security", {
			params: {
				search: search.value,
				page: page,
			},
		});
		incidents.value = response.data.incidents;
	} catch (e) {
		console.error(e);
	} finally {
		isLoading.value = false;
	}
}, 300);

watch(search, () => {
	fetchIncidents();
});

// Initial fetch
fetchIncidents();

const formatDate = (date) =>
	new Date(date).toLocaleString("en-US", {
		month: "short",
		day: "numeric",
		year: "numeric",
		hour: "2-digit",
		minute: "2-digit",
		second: "2-digit",
	});

const openDetail = (incident) => {
	selectedIncident.value = incident;
	isDetailModalOpen.value = true;
};
</script>

<template>
    <div class="w-full px-4 sm:px-8 pt-6 pb-12 min-w-0 space-y-6 animate-fade-in h-full flex flex-col" style="font-family: var(--wos-font)">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 dark:border-zinc-800 pb-5">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-zinc-100 tracking-tight flex items-center gap-2">
                    <ShieldAlert class="w-5 h-5 text-rose-600 dark:text-rose-455" /> Security Logs
                </h1>
                <p class="text-[13px] text-gray-500 dark:text-zinc-400 mt-1">Dedicated monitoring for anomalous, suspicious, and security-critical incidents.</p>
            </div>
            <div class="flex items-center gap-2">
                <button class="px-3 py-1.5 bg-rose-600 border border-transparent rounded-md text-[13px] font-medium text-white hover:bg-rose-700 transition-colors shadow-sm shadow-rose-500/20 cursor-pointer dark:shadow-none">
                    Configure Alerts
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-col sm:flex-row gap-3 items-center justify-between">
            <div class="relative flex-1 max-w-md w-full sm:w-auto">
                <Search class="w-4 h-4 text-gray-400 dark:text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2" />
                <input v-model="search" type="text" placeholder="Search IP, Incident Type, User..." class="w-full pl-9 pr-4 py-2 text-[13px] border border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-600 rounded-md focus:ring-1 focus:ring-rose-500 outline-none transition-colors shadow-sm dark:shadow-none">
            </div>
            
            <button class="px-3 py-2 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-md text-[13px] font-medium text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors shadow-sm flex items-center gap-2 cursor-pointer dark:shadow-none">
                <Filter class="w-4 h-4 text-gray-400 dark:text-zinc-500" />
                Filter Incidents
            </button>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-sm flex-1 flex flex-col overflow-hidden dark:shadow-none">
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-[13px] whitespace-nowrap">
                    <caption class="sr-only">Tabel Insiden Keamanan</caption>
                    <thead class="bg-gray-50/80 dark:bg-zinc-800/30 border-b border-gray-200 dark:border-zinc-800 text-gray-500 dark:text-zinc-400 sticky top-0 z-10">
                        <tr>
                            <th class="px-5 py-3 font-medium">Detected At</th>
                            <th class="px-5 py-3 font-medium">Incident Type</th>
                            <th class="px-5 py-3 font-medium">User/Actor</th>
                            <th class="px-5 py-3 font-medium">IP Address</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium">Severity</th>
                            <th class="px-5 py-3 font-medium w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                        <template v-if="isLoading">
                            <tr v-for="i in 5" :key="`sk-sec-${i}`">
                                <td class="px-5 py-3"><div class="h-4 wos-shimmer rounded w-32" /></td>
                                <td class="px-5 py-3"><div class="h-4 wos-shimmer rounded w-44" /></td>
                                <td class="px-5 py-3"><div class="h-4 wos-shimmer rounded w-36" /></td>
                                <td class="px-5 py-3"><div class="h-4 wos-shimmer rounded w-24" /></td>
                                <td class="px-5 py-3"><div class="h-4 wos-shimmer rounded w-28" /></td>
                                <td class="px-5 py-3"><div class="h-4 wos-shimmer rounded w-16" /></td>
                                <td class="px-5 py-3"><div class="h-4 wos-shimmer rounded w-4" /></td>
                            </tr>
                        </template>
                        <template v-else>
                            <tr v-for="incident in incidents.data" :key="incident.id" @click="openDetail(incident)" class="hover:bg-rose-50/30 dark:hover:bg-rose-950/10 transition-colors cursor-pointer group">
                                <td class="px-5 py-3 text-gray-500 dark:text-zinc-400 font-mono text-[12px]">{{ formatDate(incident.created_at) }}</td>
                                <td class="px-5 py-3">
                                    <div class="font-medium text-gray-900 dark:text-zinc-100 flex items-center gap-1.5">
                                        <AlertTriangle v-if="incident.severity === 'critical'" class="w-3.5 h-3.5 text-rose-600 dark:text-rose-400" />
                                        {{ incident.incident_type }}
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="text-gray-700 dark:text-zinc-300">{{ incident.user?.email || 'Unknown User' }}</span>
                                </td>
                                <td class="px-5 py-3 text-gray-500 dark:text-zinc-400 font-mono text-[12px]">{{ incident.ip_address || '-' }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <CheckCircle2 v-if="incident.mitigation_status === 'resolved'" class="w-4 h-4 text-emerald-500" />
                                        <XCircle v-else-if="incident.mitigation_status === 'auto_blocked'" class="w-4 h-4 text-amber-500" />
                                        <ShieldAlert v-else class="w-4 h-4 text-rose-500" />
                                        <span class="text-[12px] font-medium capitalize text-gray-600 dark:text-zinc-300">{{ incident.mitigation_status.replace('_', ' ') }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <span :class="[
                                        'inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium uppercase tracking-wider',
                                        incident.severity === 'critical' || incident.severity === 'high' ? 'bg-rose-100 dark:bg-rose-950/25 text-rose-700 dark:text-rose-400' : 'bg-amber-100 dark:bg-amber-950/25 text-amber-700 dark:text-amber-400'
                                    ]">
                                        {{ incident.severity }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <ChevronRight class="w-4 h-4 text-gray-300 dark:text-zinc-600 group-hover:text-gray-600 dark:group-hover:text-zinc-300 transition-colors" />
                                </td>
                            </tr>
                            <tr v-if="!incidents.data || !incidents.data.length">
                                <td colspan="7" class="px-5 py-16 text-center text-gray-500 dark:text-zinc-400">
                                    <ShieldAlert class="w-8 h-8 text-gray-300 dark:text-zinc-700 mx-auto mb-3" />
                                    <p class="text-[14px]">No security incidents detected.</p>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-3 border-t border-gray-200 dark:border-zinc-800 bg-gray-50 dark:bg-zinc-800/30 flex items-center justify-between text-[13px] text-gray-500 dark:text-zinc-400">
                <div>Showing {{ incidents.data?.length || 0 }} incidents</div>
                <div class="flex gap-1">
                    <button class="px-3 py-1 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded disabled:opacity-50 text-gray-700 dark:text-zinc-300 cursor-pointer">Prev</button>
                    <button class="px-3 py-1 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded disabled:opacity-50 text-gray-700 dark:text-zinc-300 cursor-pointer">Next</button>
                </div>
            </div>
        </div>

        <!-- Detail Modal Slide-over -->
        <div v-if="isDetailModalOpen" class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute inset-0 bg-gray-900/30 dark:bg-black/50 backdrop-blur-sm transition-opacity" @click="isDetailModalOpen = false"></div>
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <div class="pointer-events-auto w-screen max-w-2xl transform transition-transform bg-white dark:bg-zinc-900 shadow-2xl flex flex-col border-l border-gray-200 dark:border-zinc-800 dark:shadow-none">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-zinc-800 flex items-center justify-between bg-rose-50/30 dark:bg-rose-950/10">
                            <h2 class="text-base font-semibold text-gray-900 dark:text-zinc-100 flex items-center gap-2">
                                <ShieldAlert class="w-4 h-4 text-rose-600 dark:text-rose-455" /> Incident Report
                            </h2>
                            <button @click="isDetailModalOpen = false" class="text-gray-400 dark:text-zinc-500 hover:text-gray-500 dark:hover:text-zinc-300 bg-transparent border-0 cursor-pointer">
                                <X class="w-5 h-5" />
                            </button>
                        </div>
                        
                        <div class="flex-1 overflow-y-auto p-6" v-if="selectedIncident">
                            <div class="mb-8">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-zinc-100 mb-1">{{ selectedIncident.incident_type }}</h3>
                                <div class="text-[13px] text-gray-500 dark:text-zinc-400 font-mono">{{ formatDate(selectedIncident.created_at) }}</div>
                            </div>

                            <div class="bg-gray-50 dark:bg-zinc-950 rounded-lg p-5 border border-gray-200 dark:border-zinc-800 mb-8">
                                <h4 class="text-[12px] font-bold text-gray-900 dark:text-zinc-100 uppercase tracking-wider mb-4">Incident Context</h4>
                                <div class="grid grid-cols-2 gap-y-4">
                                    <div>
                                        <div class="text-[12px] text-gray-500 dark:text-zinc-400 mb-1">Actor</div>
                                        <div class="text-[13px] font-medium text-gray-900 dark:text-zinc-100">{{ selectedIncident.user?.email || 'Unknown' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[12px] text-gray-500 dark:text-zinc-400 mb-1">IP Source</div>
                                        <div class="text-[13px] font-mono font-medium text-gray-900 dark:text-zinc-100">{{ selectedIncident.ip_address }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[12px] text-gray-500 dark:text-zinc-400 mb-1">Severity</div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium uppercase tracking-wider bg-rose-100 dark:bg-rose-950/25 text-rose-700 dark:text-rose-400">
                                            {{ selectedIncident.severity }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-[12px] text-gray-500 dark:text-zinc-400 mb-1">Status</div>
                                        <div class="text-[13px] font-medium text-gray-900 dark:text-zinc-100 capitalize">{{ selectedIncident.mitigation_status.replace('_', ' ') }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-6">
                                <div class="text-[12px] font-medium text-gray-500 dark:text-zinc-400 uppercase tracking-wider mb-2">Technical Details</div>
                                <div class="bg-gray-900 dark:bg-zinc-950 rounded-lg p-4 font-mono text-[12px] text-rose-400 dark:text-rose-500 overflow-x-auto shadow-inner border border-transparent dark:border-zinc-800 dark:shadow-none">
                                    <pre>{{ JSON.stringify(selectedIncident.details || {}, null, 2) }}</pre>
                                </div>
                            </div>

                            <div class="pt-6 border-t border-gray-200 dark:border-zinc-800 flex items-center gap-3">
                                <button class="px-4 py-2 bg-rose-600 text-white rounded-md text-[13px] font-medium hover:bg-rose-700 shadow-sm transition-colors cursor-pointer border-0 dark:shadow-none">
                                    Block IP Address
                                </button>
                                <button class="px-4 py-2 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-zinc-300 rounded-md text-[13px] font-medium hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors shadow-sm cursor-pointer dark:shadow-none">
                                    Mark as Resolved
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>