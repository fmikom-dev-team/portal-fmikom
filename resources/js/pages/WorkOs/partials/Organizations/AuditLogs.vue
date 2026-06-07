<script setup lang="ts">
import axios from "axios";
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import AppModal from "../../components/ui/AppModal.vue";
import { toast } from "../../composables/useWorkOs";

const props = defineProps<{ organization?: any }>();

const showActionsMenu = ref(false);
const activePopover = ref<string | null>(null);
const isExporting = ref(false);
const showConfigureModal = ref(false);
const isLoading = ref(false);

const mockLogs = ref<any[]>([]);

async function fetchRealLogs() {
	if (!props.organization?.id) return;
	isLoading.value = true;
	try {
		const response = await axios.get("/workos/audit-logs/events", {
			params: {
				organization_id: props.organization.id,
			},
		});
		const fetched = response.data.events?.data || [];
		mockLogs.value = fetched.map((log: any) => ({
			id: log.id,
			action: log.event_type,
			actorName: log.actor?.name || "System",
			actorEmail: log.actor?.email || "system@fmikom.org",
			target: log.target_type
				? log.target_type.split("\\").pop()
				: log.metadata?.key_name ||
					log.metadata?.domain ||
					log.metadata?.provider ||
					"N/A",
			ipAddress: log.ip_address || "0.0.0.0",
			timestamp: log.created_at
				? new Date(log.created_at).toLocaleString()
				: "N/A",
		}));
	} catch (e) {
		console.error("Failed to load audit logs", e);
		toast("Failed to load audit logs", "error");
	} finally {
		isLoading.value = false;
	}
}

watch(
	() => props.organization?.id,
	() => {
		fetchRealLogs();
	},
	{ immediate: true },
);

const uniqueActions = computed(() => {
	const defaultActions = [
		"user.signed_in",
		"organization.updated",
		"role.assigned",
		"domain.verified",
		"member.invited",
		"api_key.created",
		"sso.connection.activated",
	];
	const foundActions = mockLogs.value.map((l) => l.action);
	return Array.from(new Set([...defaultActions, ...foundActions]));
});

// Filters State
const selectedActions = ref<string[]>([]);
const actorNameSearch = ref("");
const actorIdSearch = ref("");
const dateRange = ref("all");

// Configuration State
const configuredEvents = ref<Record<string, boolean>>({
	"user.signed_in": true,
	"organization.updated": true,
	"role.assigned": true,
	"domain.verified": true,
	"member.invited": true,
	"api_key.created": true,
	"sso.connection.activated": true,
});

// Modal configuration state copy
const tempConfiguredEvents = ref<Record<string, boolean>>({});

function togglePopover(name: string, e: Event) {
	e.stopPropagation();
	activePopover.value = activePopover.value === name ? null : name;
	showActionsMenu.value = false;
}

function toggleActionFilter(action: string) {
	const index = selectedActions.value.indexOf(action);
	if (index > -1) {
		selectedActions.value.splice(index, 1);
	} else {
		selectedActions.value.push(action);
	}
}

function clearActionFilter() {
	selectedActions.value = [];
}

function clearActorNameFilter() {
	actorNameSearch.value = "";
}

function clearActorIdFilter() {
	actorIdSearch.value = "";
}

function setDateRange(range: string) {
	dateRange.value = range;
	activePopover.value = null;
}

const activeFiltersCount = computed(() => {
	let count = 0;
	if (selectedActions.value.length > 0) count++;
	if (actorNameSearch.value) count++;
	if (actorIdSearch.value) count++;
	if (dateRange.value !== "all") count++;
	return count;
});

function clearAllFilters() {
	selectedActions.value = [];
	actorNameSearch.value = "";
	actorIdSearch.value = "";
	dateRange.value = "all";
	activePopover.value = null;
}

function matchesDateRange(timestamp: string, range: string): boolean {
	if (range === "all") return true;
	const now = new Date("2026-05-22T08:00:00"); // simulated current date
	const logDate = new Date(timestamp.replace(" ", "T"));
	const diffMs = now.getTime() - logDate.getTime();
	const diffHrs = diffMs / (1000 * 60 * 60);

	if (range === "24h" && diffHrs > 24) return false;
	if (range === "7d" && diffHrs > 24 * 7) return false;
	if (range === "30d" && diffHrs > 24 * 30) return false;
	return true;
}

// Compute logs to display
const displayedLogs = computed(() => {
	return mockLogs.value.filter((log) => {
		// Check if event is configured/enabled
		if (!configuredEvents.value[log.action]) {
			return false;
		}

		// Action filter
		if (
			selectedActions.value.length > 0 &&
			!selectedActions.value.includes(log.action)
		) {
			return false;
		}

		// Actor name filter
		if (
			actorNameSearch.value &&
			!log.actorName.toLowerCase().includes(actorNameSearch.value.toLowerCase())
		) {
			return false;
		}

		// Actor ID (email) filter
		if (
			actorIdSearch.value &&
			!log.actorEmail.toLowerCase().includes(actorIdSearch.value.toLowerCase())
		) {
			return false;
		}

		// Date range filter
		if (!matchesDateRange(log.timestamp, dateRange.value)) {
			return false;
		}

		return true;
	});
});

function triggerExport() {
	showActionsMenu.value = false;
	isExporting.value = true;
	toast("Starting CSV export...", "success");

	setTimeout(() => {
		isExporting.value = false;
		toast("Audit logs exported successfully.", "success");
	}, 1200);
}

function openConfigureModal() {
	showActionsMenu.value = false;
	tempConfiguredEvents.value = { ...configuredEvents.value };
	showConfigureModal.value = true;
}

function saveEventConfiguration() {
	configuredEvents.value = { ...tempConfiguredEvents.value };
	showConfigureModal.value = false;
	toast("Audit log events configuration updated.", "success");
}

function closePopovers() {
	activePopover.value = null;
	showActionsMenu.value = false;
}

onMounted(() => {
	document.addEventListener("click", closePopovers);
});

onUnmounted(() => {
	document.removeEventListener("click", closePopovers);
});
</script>

<template>
  <div style="font-family: var(--wos-font)" class="space-y-4">
    <!-- Filter Toolbar -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
      <div class="flex flex-wrap items-center gap-2">
        <!-- Action Filter Button -->
        <div class="relative">
          <button 
            @click.stop="togglePopover('action', $event)"
            :class="['h-[32px] px-3 border rounded-md text-[12.5px] font-semibold transition-colors flex items-center gap-1.5', selectedActions.length > 0 ? 'bg-indigo-50/70 border-indigo-200 text-indigo-600 hover:bg-indigo-100 hover:text-indigo-700' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50']"
          >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Action
            <span v-if="selectedActions.length > 0" class="ml-1 bg-indigo-100 text-indigo-700 rounded-full px-1.5 py-0.2 text-[10px] font-bold">
              {{ selectedActions.length }}
            </span>
          </button>
          
          <!-- Action Popover -->
          <div 
            v-if="activePopover === 'action'" 
            @click.stop
            class="absolute left-0 top-full mt-1.5 w-60 bg-white border border-gray-200 rounded-lg shadow-lg ring-1 ring-black/4 z-50 p-3"
          >
            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2.5">Select Actions</div>
            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
              <label v-for="action in uniqueActions" :key="action" class="flex items-center gap-2 cursor-pointer py-0.5 hover:bg-gray-50/50 px-1 rounded transition-colors">
                <input 
                  type="checkbox" 
                  :checked="selectedActions.includes(action)"
                  @change="toggleActionFilter(action)"
                  class="w-3.5 h-3.5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                />
                <span class="font-mono text-[11px] bg-gray-50 border border-gray-150 px-1 py-0.5 rounded text-gray-700 hover:bg-gray-100 transition-colors">{{ action }}</span>
              </label>
            </div>
            <div class="border-t border-gray-100 mt-3 pt-2 flex justify-between">
              <button @click="clearActionFilter" class="text-[11px] font-semibold text-gray-500 hover:text-gray-700 transition-colors">Clear</button>
              <button @click="activePopover = null" class="text-[11px] font-semibold text-indigo-600 hover:text-indigo-700 transition-colors">Apply</button>
            </div>
          </div>
        </div>

        <!-- Actor Name Filter Button -->
        <div class="relative">
          <button 
            @click.stop="togglePopover('actorName', $event)"
            :class="['h-[32px] px-3 border rounded-md text-[12.5px] font-semibold transition-colors flex items-center gap-1.5', actorNameSearch ? 'bg-indigo-50/70 border-indigo-200 text-indigo-600 hover:bg-indigo-100 hover:text-indigo-700' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50']"
          >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Actor name
          </button>

          <!-- Actor Name Popover -->
          <div 
            v-if="activePopover === 'actorName'" 
            @click.stop
            class="absolute left-0 top-full mt-1.5 w-60 bg-white border border-gray-200 rounded-lg shadow-lg ring-1 ring-black/4 z-50 p-3"
          >
            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Search Actor Name</div>
            <input 
              v-model="actorNameSearch"
              type="text"
              placeholder="Type a name..."
              class="w-full h-8 px-2.5 text-[12.5px] border border-gray-200 rounded-md focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors text-gray-900 bg-white mb-2.5"
              @keydown.enter="activePopover = null"
            />
            <div class="flex justify-between border-t border-gray-100 pt-2">
              <button @click="clearActorNameFilter" class="text-[11px] font-semibold text-gray-500 hover:text-gray-700 transition-colors">Clear</button>
              <button @click="activePopover = null" class="text-[11px] font-semibold text-indigo-600 hover:text-indigo-700 transition-colors">Apply</button>
            </div>
          </div>
        </div>

        <!-- Actor ID Filter Button -->
        <div class="relative">
          <button 
            @click.stop="togglePopover('actorId', $event)"
            :class="['h-[32px] px-3 border rounded-md text-[12.5px] font-semibold transition-colors flex items-center gap-1.5', actorIdSearch ? 'bg-indigo-50/70 border-indigo-200 text-indigo-600 hover:bg-indigo-100 hover:text-indigo-700' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50']"
          >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Actor ID
          </button>

          <!-- Actor ID Popover -->
          <div 
            v-if="activePopover === 'actorId'" 
            @click.stop
            class="absolute left-0 top-full mt-1.5 w-60 bg-white border border-gray-200 rounded-lg shadow-lg ring-1 ring-black/4 z-50 p-3"
          >
            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Search Actor Email</div>
            <input 
              v-model="actorIdSearch"
              type="text"
              placeholder="Type an email..."
              class="w-full h-8 px-2.5 text-[12.5px] border border-gray-200 rounded-md focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors text-gray-900 bg-white mb-2.5"
              @keydown.enter="activePopover = null"
            />
            <div class="flex justify-between border-t border-gray-100 pt-2">
              <button @click="clearActorIdFilter" class="text-[11px] font-semibold text-gray-500 hover:text-gray-700 transition-colors">Clear</button>
              <button @click="activePopover = null" class="text-[11px] font-semibold text-indigo-600 hover:text-indigo-700 transition-colors">Apply</button>
            </div>
          </div>
        </div>

        <!-- Date Range Filter Button -->
        <div class="relative">
          <button 
            @click.stop="togglePopover('dateRange', $event)"
            :class="['h-[32px] px-3 border rounded-md text-[12.5px] font-semibold transition-colors flex items-center gap-1.5', dateRange !== 'all' ? 'bg-indigo-50/70 border-indigo-200 text-indigo-600 hover:bg-indigo-100 hover:text-indigo-700' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50']"
          >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Date range
          </button>

          <!-- Date Range Popover -->
          <div 
            v-if="activePopover === 'dateRange'" 
            @click.stop
            class="absolute left-0 top-full mt-1.5 w-44 bg-white border border-gray-200 rounded-lg shadow-lg ring-1 ring-black/4 z-50 py-1"
          >
            <button 
              @click="setDateRange('all')"
              :class="['w-full px-3.5 py-2 text-left text-[12.5px] transition-colors', dateRange === 'all' ? 'bg-indigo-50/50 text-indigo-600 font-semibold' : 'text-gray-700 hover:bg-gray-50']"
            >
              All Time
            </button>
            <button 
              @click="setDateRange('24h')"
              :class="['w-full px-3.5 py-2 text-left text-[12.5px] transition-colors', dateRange === '24h' ? 'bg-indigo-50/50 text-indigo-600 font-semibold' : 'text-gray-700 hover:bg-gray-50']"
            >
              Last 24 Hours
            </button>
            <button 
              @click="setDateRange('7d')"
              :class="['w-full px-3.5 py-2 text-left text-[12.5px] transition-colors', dateRange === '7d' ? 'bg-indigo-50/50 text-indigo-600 font-semibold' : 'text-gray-700 hover:bg-gray-50']"
            >
              Last 7 Days
            </button>
            <button 
              @click="setDateRange('30d')"
              :class="['w-full px-3.5 py-2 text-left text-[12.5px] transition-colors', dateRange === '30d' ? 'bg-indigo-50/50 text-indigo-600 font-semibold' : 'text-gray-700 hover:bg-gray-50']"
            >
              Last 30 Days
            </button>
          </div>
        </div>

        <!-- Clear All Filters Button (Only visible if active filters exist) -->
        <button 
          v-if="activeFiltersCount > 0"
          @click="clearAllFilters"
          class="h-[32px] px-2.5 text-[12.5px] font-medium text-gray-500 hover:text-indigo-600 transition-colors flex items-center"
        >
          Clear filters
        </button>
      </div>

      <!-- Actions Dropdown -->
      <div class="relative">
        <button
          class="h-[32px] px-3.5 bg-indigo-600 text-white rounded-md text-[12.5px] font-semibold hover:bg-indigo-700 transition-colors flex items-center gap-1.5 shadow-sm disabled:opacity-60"
          :disabled="isExporting"
          @click.stop="showActionsMenu = !showActionsMenu"
        >
          <span v-if="isExporting" class="flex items-center gap-1.5">
            <svg class="animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Exporting...
          </span>
          <span v-else class="flex items-center gap-1.5">
            Actions
            <svg class="w-3 h-3 transition-transform" :class="{'rotate-180': showActionsMenu}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
          </span>
        </button>
        
        <div v-if="showActionsMenu" @click.stop class="absolute right-0 top-full mt-1.5 w-48 bg-white border border-gray-200 rounded-lg shadow-lg ring-1 ring-black/4 z-50 p-1">
          <button @click="triggerExport" class="w-full px-3 py-2 text-[12.5px] text-gray-700 hover:bg-gray-50 rounded-md text-left transition-colors flex items-center gap-2 animate-fade-in">
            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Export CSV
          </button>
          <button @click="openConfigureModal" class="w-full px-3 py-2 text-[12.5px] text-gray-700 hover:bg-gray-50 rounded-md text-left transition-colors flex items-center gap-2 animate-fade-in">
            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Configure events
          </button>
        </div>
      </div>
    </div>

    <!-- Empty Configuration State -->
    <div 
      v-if="Object.values(configuredEvents).every(v => !v)"
      class="rounded-xl bg-white flex flex-col items-center justify-center py-16 gap-3 ring-1 ring-gray-900/4 shadow-(--wos-shadow-card)"
    >
        <svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
            <rect x="3" y="3" width="18" height="18" rx="2" stroke-width="1.5"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 9h.01M15 9h.01M9 15h6"/>
        </svg>
        <p class="text-[13px] font-medium text-gray-700">No Audit Log events configured</p>
        <button 
          @click="openConfigureModal"
          class="h-[32px] px-3.5 border border-gray-200 rounded-md text-[12.5px] font-medium text-gray-700 hover:bg-gray-50 transition-colors bg-white shadow-sm"
        >
            Configure Audit Log Events
        </button>
    </div>

    <!-- Logs Table -->
    <div v-else class="rounded-xl overflow-hidden bg-white ring-1 ring-gray-900/4 shadow-(--wos-shadow-card)">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap">
          <thead>
            <tr class="bg-gray-50/75 border-b border-gray-200/80">
              <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Event</th>
              <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Actor</th>
              <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Target</th>
              <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">IP Address</th>
              <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Timestamp</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-150">
            <tr v-if="displayedLogs.length === 0">
              <td colspan="5" class="px-4 py-12 text-center text-[13px] text-gray-500 bg-white">
                No audit logs found matching the selected filters.
              </td>
            </tr>
            <tr 
              v-for="log in displayedLogs" 
              :key="log.id" 
              class="hover:bg-gray-50/50 transition-colors"
            >
              <!-- Event / Action -->
              <td class="px-4 py-3.5 align-middle">
                <span class="font-mono text-[10.5px] font-semibold bg-gray-50 border border-gray-200 px-1.5 py-0.5 rounded text-gray-700">
                  {{ log.action }}
                </span>
              </td>
              <!-- Actor -->
              <td class="px-4 py-3.5 align-middle">
                <div>
                  <p class="text-[13px] font-semibold text-gray-900 leading-tight">{{ log.actorName }}</p>
                  <p class="text-[11.5px] text-gray-500 leading-tight mt-0.5">{{ log.actorEmail }}</p>
                </div>
              </td>
              <!-- Target -->
              <td class="px-4 py-3.5 align-middle">
                <span class="text-[12.5px] text-gray-600">{{ log.target }}</span>
              </td>
              <!-- IP Address -->
              <td class="px-4 py-3.5 align-middle">
                <span class="font-mono text-[12px] text-gray-500">{{ log.ipAddress }}</span>
              </td>
              <!-- Timestamp -->
              <td class="px-4 py-3.5 align-middle">
                <span class="text-[12.5px] text-gray-500">{{ log.timestamp }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Configure Events Modal -->
    <AppModal
      :show="showConfigureModal"
      title="Configure Audit Log Events"
      @close="showConfigureModal = false"
    >
      <template #description>
        Toggle which event types should be logged and tracked inside the audit trails.
      </template>

      <div class="space-y-3.5">
        <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1 border-b border-gray-100 pb-2">Logged Events</div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2">
          <label v-for="(val, key) in tempConfiguredEvents" :key="key" class="flex items-center gap-2.5 cursor-pointer py-1.5 hover:bg-gray-50 px-2 rounded-md transition-colors border border-transparent hover:border-gray-100">
            <input 
              v-model="tempConfiguredEvents[key]" 
              type="checkbox"
              class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
            />
            <span class="font-mono text-[11px] text-gray-700">{{ key }}</span>
          </label>
        </div>
      </div>

      <template #footer>
        <button
          class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-gray-700 border border-gray-200 hover:bg-gray-50 transition-colors shadow-sm bg-white"
          @click="showConfigureModal = false"
        >
          Cancel
        </button>
        <button
          class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-sm"
          @click="saveEventConfiguration"
        >
          Save changes
        </button>
      </template>
    </AppModal>
  </div>
</template>
