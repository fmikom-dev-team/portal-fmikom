<script setup lang="ts">
import { computed, ref } from "vue";
import AppModal from "../../components/ui/AppModal.vue";

const props = defineProps<{
	logs: any[];
}>();

const emit = defineEmits<(e: "clear-logs") => void>();

const searchQuery = ref("");
const statusFilter = ref("all");
const typeFilter = ref("all");
const selectedLog = ref<any>(null);
const showLogModal = ref(false);

const filteredLogs = computed(() => {
	return props.logs.filter((log) => {
		const matchesSearch =
			log.recipient.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
			log.subject.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
			log.id.toLowerCase().includes(searchQuery.value.toLowerCase());

		const matchesStatus =
			statusFilter.value === "all" || log.status === statusFilter.value;
		const matchesType =
			typeFilter.value === "all" || log.type === typeFilter.value;

		return matchesSearch && matchesStatus && matchesType;
	});
});

function openLogDetails(log: any) {
	selectedLog.value = log;
	showLogModal.value = true;
}
</script>

<template>
  <div class="space-y-4">
    <!-- Filters -->
    <div class="flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[240px] max-w-sm">
            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
                id="search_logs_input"
                v-model="searchQuery"
                type="text"
                placeholder="Search by recipient or subject..."
                class="w-full h-[32px] pl-9 pr-3 text-[12.5px] border border-gray-200 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors text-gray-900 bg-white"
            />
        </div>
        
        <select
            id="status_filter_select"
            v-model="statusFilter"
            class="h-[32px] px-2.5 text-[12.5px] border border-gray-200 rounded-md focus:outline-none focus:border-[#2563eb] bg-white text-gray-700"
        >
            <option value="all">All Statuses</option>
            <option value="Delivered">Delivered</option>
            <option value="Bounced">Bounced</option>
        </select>

        <select
            id="type_filter_select"
            v-model="typeFilter"
            class="h-[32px] px-2.5 text-[12.5px] border border-gray-200 rounded-md focus:outline-none focus:border-[#2563eb] bg-white text-gray-700"
        >
            <option value="all">All Types</option>
            <option value="Verification Email">Verification</option>
            <option value="Member Invitation">Invitation</option>
            <option value="Password Alert">Password Alert</option>
        </select>

        <button
            v-if="props.logs.length > 0"
            id="clear_logs_button"
            class="h-[32px] px-3 border border-red-200 rounded-md text-[12.5px] font-semibold text-red-600 bg-red-50 hover:bg-red-100 transition-colors shadow-sm ml-auto flex items-center gap-1.5"
            @click="emit('clear-logs')"
        >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Clear Logs
        </button>
    </div>

    <!-- Table -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap text-[13px]">
                <caption class="sr-only">Email Dispatch Logs</caption>
                <thead>
                    <tr class="bg-gray-50/75 border-b border-gray-200/80">
                        <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Recipient</th>
                        <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Subject</th>
                        <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Sent At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-if="filteredLogs.length === 0">
                        <td colspan="5" class="px-4 py-12 text-center text-gray-500 bg-white">
                            No email logs found.
                        </td>
                    </tr>
                    <tr
                        v-for="log in filteredLogs"
                        :key="log.id"
                        class="hover:bg-gray-50/50 transition-colors cursor-pointer"
                        @click="openLogDetails(log)"
                    >
                        <td class="px-4 py-3 font-semibold text-gray-900">{{ log.recipient }}</td>
                        <td class="px-4 py-3 text-gray-700 max-w-xs truncate">{{ log.subject }}</td>
                        <td class="px-4 py-3">
                            <span class="font-mono text-[10.5px] bg-slate-50 border border-slate-200 px-1.5 py-0.5 rounded text-gray-600">
                                {{ log.type }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span :class="['inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[11.5px] font-medium border', log.status === 'Delivered' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-red-50 border-red-200 text-red-700']">
                                <span :class="['w-1.5 h-1.5 rounded-full', log.status === 'Delivered' ? 'bg-emerald-500' : 'bg-red-500']" />
                                {{ log.status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ log.sentAt }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- LOGS DETAILS MODAL -->
    <AppModal
        :show="showLogModal"
        title="Email Log Payload"
        @close="showLogModal = false"
    >
        <template #description>
            Detailed diagnostics and raw rendered output for selected mail dispatch.
        </template>

        <div v-if="selectedLog" class="space-y-4 text-[13px]">
            <div class="grid grid-cols-2 gap-4 border-b border-gray-100 pb-3">
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Message ID</span>
                    <span class="font-mono text-[12px] text-gray-800">{{ selectedLog.id }}</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Sent Via</span>
                    <span class="font-semibold text-gray-800">{{ selectedLog.provider }}</span>
                </div>
            </div>

            <!-- Variables -->
            <div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1.5">Shared Variables Payload</span>
                <div class="bg-gray-50 border border-gray-150 p-2.5 rounded-lg font-mono text-[11.5px] text-gray-700 space-y-1">
                    <div v-for="(val, key) in selectedLog.variables" :key="key">
                        <span class="text-blue-600">{{ key }}</span>: "{{ val }}"
                    </div>
                </div>
            </div>

            <!-- Event Timeline -->
            <div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-2">Delivery Trace Timeline</span>
                <div class="space-y-2 border-l border-gray-200 pl-3.5 ml-1">
                    <div v-for="(evt, idx) in selectedLog.events" :key="idx" class="relative">
                        <span class="absolute -left-[19px] top-1 w-2.5 h-2.5 rounded-full border border-white bg-blue-500" />
                        <span class="text-[11px] text-gray-400 font-mono">{{ evt.time }}</span> &bull; 
                        <span class="text-[12.5px] text-gray-700">{{ evt.event }}</span>
                    </div>
                </div>
            </div>

            <!-- HTML rendering -->
            <div class="space-y-1.5">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Rendered HTML Output</span>
                <div class="border border-gray-200 rounded-lg overflow-hidden bg-white max-h-60 overflow-y-auto p-4 scale-98 origin-top">
                    <div v-html="selectedLog.body"></div>
                </div>
            </div>
        </div>

        <template #footer>
            <button
                class="h-[34px] px-5 rounded-md text-[13px] font-semibold text-gray-700 border border-gray-300 hover:bg-gray-50 transition-colors bg-white shadow-sm"
                @click="showLogModal = false"
            >
                Close
            </button>
        </template>
    </AppModal>
  </div>
</template>