<script setup lang="ts">
import axios from "axios";
import {
	AlertCircle,
	Check,
	Filter,
	Info,
	Loader2,
	RefreshCw,
	Search,
} from "lucide-vue-next";
import { onMounted, ref } from "vue";

// ─────────────────────────────────────────────────────────────────────────────
const logs = ref<any[]>([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const isLoading = ref(true);
const filters = ref({ event: "", severity: "", days: 7 });
const searchInput = ref("");
let searchTimer: ReturnType<typeof setTimeout>;

const SEVERITY_STYLE: Record<string, string> = {
	info: "bg-blue-50 text-blue-700 border-blue-100",
	warning: "bg-yellow-50 text-yellow-700 border-yellow-100",
	critical: "bg-red-50 text-red-700 border-red-100",
};

const EVENT_ICONS: Record<string, string> = {
	"auth.login.success": "✓",
	"auth.login.failed": "✗",
	"auth.mfa.enabled": "🔒",
	"auth.session.revoked": "⊘",
	"auth.provider.toggled": "⚙",
	"auth.setting.changed": "⚙",
};

// ─────────────────────────────────────────────────────────────────────────────
const loadLogs = async (page = 1) => {
	isLoading.value = true;
	try {
		const res = await axios.get("/workos/auth-platform/audit-logs", {
			params: {
				event: filters.value.event || undefined,
				severity: filters.value.severity || undefined,
				days: filters.value.days,
				page,
			},
		});
		logs.value = res.data.logs.data;
		pagination.value = {
			current_page: res.data.logs.current_page,
			last_page: res.data.logs.last_page,
			total: res.data.logs.total,
		};
	} catch {
		// silent
	} finally {
		isLoading.value = false;
	}
};

onMounted(() => loadLogs());

const onSearchInput = () => {
	clearTimeout(searchTimer);
	searchTimer = setTimeout(() => {
		filters.value.event = searchInput.value;
		loadLogs();
	}, 400);
};

const timeAgo = (iso: string) => {
	const diff = (Date.now() - new Date(iso).getTime()) / 1000;
	if (diff < 60) return `${Math.floor(diff)}s ago`;
	if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
	if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
	return new Date(iso).toLocaleDateString();
};
</script>

<template>
    <div class="space-y-5 animate-fade-in max-w-[900px]">

        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-200 dark:border-zinc-700 pb-5">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-zinc-100">Audit Logs</h1>
                <p class="text-[14px] text-gray-500 dark:text-zinc-400 mt-1">Immutable record of all authentication events.</p>
            </div>
            <button @click="loadLogs()" class="p-2 hover:bg-gray-100 dark:hover:bg-zinc-800 dark:bg-zinc-800 rounded-lg text-gray-500 dark:text-zinc-400">
                <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': isLoading }" />
            </button>
        </div>

        <!-- Filters -->
        <div class="flex items-center gap-3 flex-wrap">
            <div class="flex items-center gap-2 flex-1 min-w-48 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-lg px-3 py-1.5">
                <Search class="w-4 h-4 text-gray-400 dark:text-zinc-500 shrink-0" />
                <input v-model="searchInput" @input="onSearchInput" placeholder="Search events..." class="text-[13px] flex-1 outline-none placeholder-gray-400 dark:placeholder-zinc-500" />
            </div>
            <select v-model="filters.severity" @change="loadLogs()"
                class="text-[13px] border border-gray-200 dark:border-zinc-700 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-blue-300 text-gray-600 dark:text-zinc-400 bg-white dark:bg-zinc-900">
                <option value="">All severities</option>
                <option value="info">Info</option>
                <option value="warning">Warning</option>
                <option value="critical">Critical</option>
            </select>
            <select v-model="filters.days" @change="loadLogs()"
                class="text-[13px] border border-gray-200 dark:border-zinc-700 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-blue-300 text-gray-600 dark:text-zinc-400 bg-white dark:bg-zinc-900">
                <option :value="1">Last 24h</option>
                <option :value="7">Last 7 days</option>
                <option :value="30">Last 30 days</option>
                <option :value="90">Last 90 days</option>
            </select>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl shadow-sm overflow-hidden dark:shadow-none">
            <div v-if="!isLoading && logs.length === 0" class="text-center py-12 text-gray-500 dark:text-zinc-400 text-[14px]">
                No audit events found for the selected filters.
            </div>

            <table v-else class="w-full text-[13px]">
                <caption class="sr-only">Tabel Log Audit Otentikasi</caption>
                <thead class="bg-gray-50 dark:bg-zinc-900 border-b border-gray-100 dark:border-zinc-800">
                    <tr>
                        <th class="text-left px-4 py-2.5 font-medium text-gray-500 dark:text-zinc-400">Event</th>
                        <th class="text-left px-4 py-2.5 font-medium text-gray-500 dark:text-zinc-400">User</th>
                        <th class="text-left px-4 py-2.5 font-medium text-gray-500 dark:text-zinc-400">IP Address</th>
                        <th class="text-left px-4 py-2.5 font-medium text-gray-500 dark:text-zinc-400">Severity</th>
                        <th class="text-left px-4 py-2.5 font-medium text-gray-500 dark:text-zinc-400">When</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-zinc-800">
                    <template v-if="isLoading">
                        <tr v-for="i in 5" :key="`sk-aud-${i}`">
                            <td class="px-4 py-3"><div class="h-4 wos-shimmer rounded w-36" /></td>
                            <td class="px-4 py-3"><div class="h-4 wos-shimmer rounded w-24" /></td>
                            <td class="px-4 py-3"><div class="h-4 wos-shimmer rounded w-28" /></td>
                            <td class="px-4 py-3"><div class="h-4 wos-shimmer rounded w-16" /></td>
                            <td class="px-4 py-3"><div class="h-4 wos-shimmer rounded w-16" /></td>
                        </tr>
                    </template>
                    <template v-else>
                        <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-50 dark:hover:bg-zinc-800 dark:bg-zinc-900/50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-[12px]">{{ EVENT_ICONS[log.event] ?? '•' }}</span>
                                    <code class="text-gray-700 dark:text-zinc-300 font-mono text-[12px] bg-gray-100 dark:bg-zinc-800 px-1.5 py-0.5 rounded">{{ log.event }}</code>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span v-if="log.user" class="text-gray-700 dark:text-zinc-300">{{ log.user.name }}</span>
                                <span v-else class="text-gray-400 dark:text-zinc-500 text-[12px]">System</span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-zinc-400 font-mono text-[12px]">{{ log.ip_address ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span :class="['px-2 py-0.5 rounded-full border text-[11px] font-medium', SEVERITY_STYLE[log.severity]]">
                                    {{ log.severity }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-zinc-400">{{ timeAgo(log.occurred_at) }}</td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="px-4 py-3 border-t border-gray-100 dark:border-zinc-800 flex items-center justify-between text-[13px] text-gray-500 dark:text-zinc-400">
                <span>{{ pagination.total }} total events · Page {{ pagination.current_page }} / {{ pagination.last_page }}</span>
                <div class="flex gap-2">
                    <button @click="loadLogs(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
                        class="px-3 py-1 border border-gray-200 dark:border-zinc-700 rounded-md hover:bg-gray-50 dark:hover:bg-zinc-800 dark:bg-zinc-900 disabled:opacity-40">Previous</button>
                    <button @click="loadLogs(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page"
                        class="px-3 py-1 border border-gray-200 dark:border-zinc-700 rounded-md hover:bg-gray-50 dark:hover:bg-zinc-800 dark:bg-zinc-900 disabled:opacity-40">Next</button>
                </div>
            </div>
        </div>
    </div>
</template>