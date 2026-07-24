<script setup>
import { router } from "@inertiajs/vue3";
import axios from "axios";
import {
	Activity,
	ArrowRight,
	ChevronDown,
	ChevronRight,
	Clock,
	Download,
	Filter,
	Monitor,
	Search,
	ShieldAlert,
	X,
} from "lucide-vue-next";
import { ref, watch } from "vue";

const debounce = (fn, delay) => {
	let timeoutId;
	return (...args) => {
		clearTimeout(timeoutId);
		timeoutId = setTimeout(() => fn(...args), delay);
	};
};

const props = defineProps({
	filters: Object,
});

const events = ref({ data: [], links: [] });
const search = ref(props.filters?.search || "");
const selectedSeverity = ref(props.filters?.severity || "");
const isDetailModalOpen = ref(false);
const selectedEvent = ref(null);
const isLoading = ref(false);

const fetchEvents = debounce(async (page = 1) => {
	isLoading.value = true;
	try {
		const response = await axios.get("/workos/audit-logs/events", {
			params: {
				search: search.value,
				severity: selectedSeverity.value,
				page: page,
			},
		});
		events.value = response.data.events;
	} catch (e) {
		console.error(e);
	} finally {
		isLoading.value = false;
	}
}, 300);

watch([search, selectedSeverity], () => {
	fetchEvents();
});

// Initial fetch
fetchEvents();

const formatDate = (date) =>
	new Date(date).toLocaleString("en-US", {
		month: "short",
		day: "numeric",
		year: "numeric",
		hour: "2-digit",
		minute: "2-digit",
		second: "2-digit",
	});

const openDetail = (event) => {
	selectedEvent.value = event;
	isDetailModalOpen.value = true;
};
</script>

<template>
    <div class="w-full px-4 sm:px-8 pt-6 pb-12 min-w-0 space-y-6 animate-fade-in h-full flex flex-col" style="font-family: var(--wos-font)">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 dark:border-zinc-800 pb-5">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-zinc-100 tracking-tight">Event Logs</h1>
                <p class="text-[13px] text-gray-500 dark:text-zinc-400 mt-1">Centralized, immutable record of all system activity and changes.</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-col sm:flex-row gap-3 items-center justify-between">
            <div class="flex flex-1 gap-3 w-full sm:w-auto">
                <!-- Search -->
                <div class="relative flex-1 max-w-md">
                    <Search class="w-4 h-4 text-gray-400 dark:text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2" />
                    <input v-model="search" type="text" placeholder="Search event type, IP address, user..." class="w-full pl-9 pr-4 py-2 text-[13px] border border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-600 rounded-md focus:ring-1 focus:ring-blue-500 outline-none transition-colors shadow-sm dark:shadow-none">
                </div>
                <!-- Severity Filter -->
                <div class="relative w-40">
                    <select v-model="selectedSeverity" class="w-full pl-3 pr-8 py-2 text-[13px] border border-gray-200 dark:border-zinc-800 rounded-md focus:ring-1 focus:ring-blue-500 outline-none appearance-none bg-white dark:bg-zinc-900 text-gray-900 dark:text-zinc-100 shadow-sm dark:shadow-none">
                        <option value="">All Severities</option>
                        <option value="info">Info</option>
                        <option value="warning">Warning</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                    <ChevronDown class="w-4 h-4 text-gray-400 dark:text-zinc-500 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                </div>
            </div>
            
            <button class="px-3 py-2 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-md text-[13px] font-medium text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors shadow-sm flex items-center gap-2 cursor-pointer dark:shadow-none">
                <Filter class="w-4 h-4 text-gray-400 dark:text-zinc-500" />
                Advanced Filters
            </button>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-sm flex-1 flex flex-col overflow-hidden dark:shadow-none">
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-[13px] whitespace-nowrap">
                    <caption class="sr-only">Tabel Log Aktivitas</caption>
                    <thead class="bg-gray-50/80 dark:bg-zinc-800/30 border-b border-gray-200 dark:border-zinc-800 text-gray-500 dark:text-zinc-400 sticky top-0 z-10">
                        <tr>
                            <th class="px-5 py-3 font-medium">Timestamp</th>
                            <th class="px-5 py-3 font-medium">Event Type</th>
                            <th class="px-5 py-3 font-medium">Actor</th>
                            <th class="px-5 py-3 font-medium">Target</th>
                            <th class="px-5 py-3 font-medium">IP Address</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                        <template v-if="isLoading">
                            <tr v-for="i in 5" :key="`sk-ev-${i}`">
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
                            <tr v-for="event in events.data" :key="event.id" @click="openDetail(event)" class="hover:bg-gray-50 dark:bg-zinc-900 dark:hover:bg-zinc-800/30 transition-colors cursor-pointer group">
                                <td class="px-5 py-3 text-gray-500 dark:text-zinc-400 font-mono text-[12px]">{{ formatDate(event.created_at) }}</td>
                                <td class="px-5 py-3">
                                    <div class="font-medium text-gray-900 dark:text-zinc-100">{{ event.event_type }}</div>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="text-gray-700 dark:text-zinc-300">{{ event.actor?.email || 'System' }}</span>
                                </td>
                                <td class="px-5 py-3 text-gray-500 dark:text-zinc-400 font-mono text-[12px]">
                                    {{ event.target_type ? event.target_type.split('\\').pop() : '-' }}
                                </td>
                                <td class="px-5 py-3 text-gray-500 dark:text-zinc-400">{{ event.ip_address || '-' }}</td>
                                <td class="px-5 py-3">
                                    <span :class="[
                                        'inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium uppercase tracking-wider',
                                        ['critical', 'high'].includes(event.severity) ? 'bg-rose-100 dark:bg-rose-950/35 text-rose-700 dark:text-rose-450' : 
                                        event.severity === 'warning' ? 'bg-amber-100 dark:bg-amber-950/25 text-amber-700 dark:text-amber-400' : 'bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300'
                                    ]">
                                        {{ event.severity }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <ChevronRight class="w-4 h-4 text-gray-300 dark:text-zinc-655 group-hover:text-gray-600 dark:group-hover:text-zinc-300 transition-colors" />
                                </td>
                            </tr>
                            <tr v-if="!events.data || !events.data.length">
                                <td colspan="7" class="px-5 py-16 text-center text-gray-500 dark:text-zinc-400">
                                    <Activity class="w-8 h-8 text-gray-300 dark:text-zinc-700 mx-auto mb-3" />
                                    <p class="text-[14px]">No events found matching your criteria.</p>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination (Mock for now, normally use events.links) -->
            <div class="px-5 py-3 border-t border-gray-200 dark:border-zinc-800 bg-gray-50 dark:bg-zinc-800/30 flex items-center justify-between text-[13px] text-gray-500 dark:text-zinc-400">
                <div>Showing {{ events.data?.length || 0 }} events</div>
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
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-zinc-800 flex items-center justify-between bg-gray-50/50 dark:bg-zinc-950/20">
                            <h2 class="text-base font-semibold text-gray-900 dark:text-zinc-100" id="slide-over-title">Event Details</h2>
                            <button @click="isDetailModalOpen = false" class="text-gray-400 dark:text-zinc-500 hover:text-gray-500 dark:hover:text-zinc-300 bg-transparent border-0 cursor-pointer">
                                <X class="w-5 h-5" />
                            </button>
                        </div>
                        
                        <div class="flex-1 overflow-y-auto p-6" v-if="selectedEvent">
                            <div class="mb-8">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-zinc-100">{{ selectedEvent.event_type }}</h3>
                                    <span :class="[
                                        'inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium uppercase tracking-wider',
                                        ['critical', 'high'].includes(selectedEvent.severity) ? 'bg-rose-100 dark:bg-rose-950/25 text-rose-700 dark:text-rose-400' : 
                                        selectedEvent.severity === 'warning' ? 'bg-amber-100 dark:bg-amber-950/25 text-amber-700 dark:text-amber-400' : 'bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300'
                                    ]">{{ selectedEvent.severity }}</span>
                                </div>
                                <div class="text-[13px] text-gray-500 dark:text-zinc-400 flex items-center gap-2">
                                    <Clock class="w-3.5 h-3.5" /> {{ formatDate(selectedEvent.created_at) }}
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-x-6 gap-y-6 mb-8">
                                <div>
                                    <div class="text-[12px] font-medium text-gray-500 dark:text-zinc-400 uppercase tracking-wider mb-1">Actor</div>
                                    <div class="text-[14px] text-gray-900 dark:text-zinc-100">{{ selectedEvent.actor?.email || 'System' }}</div>
                                </div>
                                <div>
                                    <div class="text-[12px] font-medium text-gray-500 dark:text-zinc-400 uppercase tracking-wider mb-1">IP Address</div>
                                    <div class="text-[14px] text-gray-900 dark:text-zinc-100 font-mono">{{ selectedEvent.ip_address || '-' }}</div>
                                </div>
                                <div class="col-span-2">
                                    <div class="text-[12px] font-medium text-gray-500 dark:text-zinc-400 uppercase tracking-wider mb-1">User Agent</div>
                                    <div class="text-[13px] text-gray-700 dark:text-zinc-300">{{ selectedEvent.user_agent || '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-[12px] font-medium text-gray-500 dark:text-zinc-400 uppercase tracking-wider mb-1">Target Type</div>
                                    <div class="text-[13px] text-gray-900 dark:text-zinc-100 font-mono">{{ selectedEvent.target_type || '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-[12px] font-medium text-gray-500 dark:text-zinc-400 uppercase tracking-wider mb-1">Target ID</div>
                                    <div class="text-[13px] text-gray-900 dark:text-zinc-100 font-mono">{{ selectedEvent.target_id || '-' }}</div>
                                </div>
                            </div>

                            <div class="mb-6">
                                <div class="text-[12px] font-medium text-gray-500 dark:text-zinc-400 uppercase tracking-wider mb-2">Request Context</div>
                                <div class="bg-gray-50 dark:bg-zinc-950 rounded-lg border border-gray-200 dark:border-zinc-800 p-4 font-mono text-[12px] text-gray-700 dark:text-zinc-300 space-y-2">
                                    <div><span class="text-gray-400 dark:text-zinc-500">Method:</span> {{ selectedEvent.request_method || '-' }}</div>
                                    <div><span class="text-gray-400 dark:text-zinc-500">Path:</span> {{ selectedEvent.request_path || '-' }}</div>
                                    <div><span class="text-gray-400 dark:text-zinc-500">Correlation ID:</span> {{ selectedEvent.correlation_id || '-' }}</div>
                                </div>
                            </div>

                            <div>
                                <div class="text-[12px] font-medium text-gray-500 dark:text-zinc-400 uppercase tracking-wider mb-2">Raw Metadata</div>
                                <div class="bg-gray-900 dark:bg-zinc-950 rounded-lg p-4 font-mono text-[12px] text-green-400 dark:text-emerald-500 overflow-x-auto shadow-inner border border-transparent dark:border-zinc-800 dark:shadow-none">
                                    <pre>{{ JSON.stringify(selectedEvent.metadata || {}, null, 2) }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>