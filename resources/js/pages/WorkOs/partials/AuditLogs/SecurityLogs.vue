<script setup>
import { router } from "@inertiajs/vue3";
import axios from "axios";
import {
	AlertCircle,
	AlertOctagon,
	AlertTriangle,
	ArrowUpRight,
	Bug,
	Check,
	CheckCircle2,
	ChevronRight,
	Clock,
	Copy,
	Database,
	ExternalLink,
	Eye,
	FileCode,
	Filter,
	Globe,
	Info,
	Layers,
	Play,
	Radio,
	RefreshCw,
	Search,
	Server,
	ShieldAlert,
	ShieldCheck,
	Terminal,
	Trash2,
	User,
	Wrench,
	X,
	XCircle,
} from "lucide-vue-next";
import { onBeforeUnmount, onMounted, ref, watch } from "vue";

// State
const incidents = ref({ data: [], current_page: 1, last_page: 1, total: 0 });
const stats = ref({
	total_incidents: 0,
	critical_errors: 0,
	open_issues: 0,
	resolved_issues: 0,
	today_count: 0,
});
const search = ref("");
const selectedSeverity = ref("all");
const selectedStatus = ref("all");
const isDetailModalOpen = ref(false);
const selectedIncident = ref(null);
const isInitialLoading = ref(true);
const isManualRefreshing = ref(false);
const autoRefresh = ref(true);
const copiedTrace = ref(false);
const copiedReport = ref(false);
const isUpdatingStatus = ref(false);
let refreshTimer = null;

// Modern Confirmation Modal State
const confirmModal = ref({
	isOpen: false,
	title: "",
	message: "",
	confirmText: "",
	type: "danger",
	isProcessing: false,
	action: null,
});

// Severity tabs
const severityTabs = [
	{ id: "all", label: "Semua Log" },
	{ id: "critical", label: "🔴 Kritis (500)" },
	{ id: "high", label: "🟠 Tinggi" },
	{ id: "warning", label: "🟡 Peringatan" },
];

const debounce = (fn, delay) => {
	let timeoutId;
	return (...args) => {
		clearTimeout(timeoutId);
		timeoutId = setTimeout(() => fn(...args), delay);
	};
};

const fetchIncidents = async (page = 1) => {
	try {
		const response = await axios.get("/workos/audit-logs/security", {
			params: {
				search: search.value,
				severity: selectedSeverity.value,
				status: selectedStatus.value,
				page: page,
			},
		});

		incidents.value = response.data.incidents || { data: [], current_page: 1, last_page: 1, total: 0 };
		if (response.data.stats) {
			stats.value = response.data.stats;
		}

		// If detail modal is open, keep selected incident in sync
		if (selectedIncident.value) {
			const updated = incidents.value.data.find(
				(i) => i.id === selectedIncident.value.id,
			);
			if (updated) {
				selectedIncident.value = updated;
			}
		}
	} catch (e) {
		console.error("Gagal mengambil log insiden:", e);
	} finally {
		isInitialLoading.value = false;
		isManualRefreshing.value = false;
	}
};

const manualRefresh = async () => {
	isManualRefreshing.value = true;
	await fetchIncidents(incidents.value.current_page || 1);
};

const debouncedFetch = debounce(() => fetchIncidents(1), 300);

watch(search, () => {
	debouncedFetch();
});

watch([selectedSeverity, selectedStatus], () => {
	fetchIncidents(1);
});

// Setup Silent Live Real-time Polling
const startLivePolling = () => {
	stopLivePolling();
	refreshTimer = setInterval(() => {
		if (autoRefresh.value && document.visibilityState === "visible") {
			fetchIncidents(incidents.value.current_page || 1);
		}
	}, 4000); // 4 seconds silent poll
};

const stopLivePolling = () => {
	if (refreshTimer) {
		clearInterval(refreshTimer);
		refreshTimer = null;
	}
};

onMounted(() => {
	fetchIncidents(1);
	startLivePolling();
});

onBeforeUnmount(() => {
	stopLivePolling();
});

// Helper formatters
const formatDate = (date) => {
	if (!date) return "-";
	return new Date(date).toLocaleString("id-ID", {
		day: "2-digit",
		month: "short",
		year: "numeric",
		hour: "2-digit",
		minute: "2-digit",
		second: "2-digit",
	});
};

const formatRelativeTime = (date) => {
	if (!date) return "-";
	const now = new Date();
	const then = new Date(date);
	const diffSeconds = Math.floor((now - then) / 1000);

	if (diffSeconds < 5) return "Baru saja";
	if (diffSeconds < 60) return `${diffSeconds} dtk lalu`;
	const diffMinutes = Math.floor(diffSeconds / 60);
	if (diffMinutes < 60) return `${diffMinutes} mnt lalu`;
	const diffHours = Math.floor(diffMinutes / 60);
	if (diffHours < 24) return `${diffHours} jam lalu`;
	const diffDays = Math.floor(diffHours / 24);
	return `${diffDays} hr lalu`;
};

const openDetail = (incident) => {
	selectedIncident.value = incident;
	isDetailModalOpen.value = true;
	copiedTrace.value = false;
	copiedReport.value = false;
};

// Update status (open, investigating, resolved)
const updateStatus = async (status) => {
	if (!selectedIncident.value) return;
	isUpdatingStatus.value = true;
	try {
		await axios.patch(
			`/workos/audit-logs/security/${selectedIncident.value.id}/status`,
			{
				status: status,
			},
		);
		selectedIncident.value.mitigation_status = status;
		fetchIncidents(incidents.value.current_page);
	} catch (err) {
		console.error("Gagal memperbarui status insiden:", err);
	} finally {
		isUpdatingStatus.value = false;
	}
};

// Prompt Delete single incident (Modern Modal)
const promptDeleteIncident = (id) => {
	confirmModal.value = {
		isOpen: true,
		title: "Hapus Catatan Log Insiden?",
		message:
			"Catatan diagnostik dan stack trace error ini akan dihapus secara permanen dari riwayat sistem.",
		confirmText: "Ya, Hapus Log",
		type: "danger",
		isProcessing: false,
		action: async () => {
			confirmModal.value.isProcessing = true;
			try {
				await axios.delete(`/workos/audit-logs/security/${id}`);
				if (selectedIncident.value?.id === id) {
					isDetailModalOpen.value = false;
					selectedIncident.value = null;
				}
				confirmModal.value.isOpen = false;
				fetchIncidents(incidents.value.current_page);
			} catch (err) {
				console.error("Gagal menghapus insiden:", err);
			} finally {
				confirmModal.value.isProcessing = false;
			}
		},
	};
};

// Prompt Clear logs (Modern Modal)
const promptClearLogs = (scope = "all") => {
	const isResolved = scope === "resolved";
	confirmModal.value = {
		isOpen: true,
		title: isResolved
			? "Bersihkan Log yang Sudah Selesai?"
			: "Bersihkan SEMUA Log Insiden & Error?",
		message: isResolved
			? "Semua catatan log insiden yang telah berstatus 'Selesai' akan dihapus permanen dari riwayat sistem."
			: "PERINGATAN: Seluruh log diagnostik, error 500, dan riwayat exception sistem akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.",
		confirmText: isResolved ? "Bersihkan Log Selesai" : "Hapus Semua Log",
		type: "danger",
		isProcessing: false,
		action: async () => {
			confirmModal.value.isProcessing = true;
			try {
				await axios.post("/workos/audit-logs/security/clear", { scope });
				confirmModal.value.isOpen = false;
				fetchIncidents(1);
			} catch (err) {
				console.error("Gagal membersihkan log:", err);
			} finally {
				confirmModal.value.isProcessing = false;
			}
		},
	};
};

const handleConfirmAction = () => {
	if (confirmModal.value.action) {
		confirmModal.value.action();
	}
};

// Copy Diagnostic Report
const copyDiagnosticReport = () => {
	if (!selectedIncident.value) return;
	const i = selectedIncident.value;
	const d = i.details || {};

	const report = `================================================
🚨 LAPORAN ERROR & DIAGNOSTIK SISTEM PORTAL
================================================
Tipe Insiden    : ${i.incident_type}
Severity        : ${i.severity?.toUpperCase()}
Status          : ${i.mitigation_status?.toUpperCase()}
Waktu Terdeteksi: ${formatDate(i.created_at)}
User Terdampak  : ${i.user?.email || "Guest / Unauthenticated"} (ID: ${i.user?.id || "-"})
Alamat IP       : ${i.ip_address}
URL Endpoint    : [${d.method || "GET"}] ${d.url || "-"}
File Lokasi     : ${d.file || "-"}:${d.line || "-"}

💡 DIAGNOSA MASALAH (ROOT CAUSE):
${d.root_cause || d.message || "Tidak ada diagnosa spesifik."}

🛠️ REKOMENDASI SOLUSI:
${d.suggested_fix || "Periksa log stack trace dan implementasi controller terkait."}

💻 PESAN EXCEPTION ASLI:
${d.message || "-"}

📂 STACK TRACE (TOP APP FRAMES):
${(d.stack_trace || [])
	.map((frame, idx) => `  #${idx + 1} ${frame.file}:${frame.line} -> ${frame.call}`)
	.join("\n")}
================================================`;

	navigator.clipboard.writeText(report);
	copiedReport.value = true;
	setTimeout(() => {
		copiedReport.value = false;
	}, 2500);
};

// Copy Stack trace only
const copyStackTrace = () => {
	if (!selectedIncident.value?.details?.stack_trace) return;
	const text = selectedIncident.value.details.stack_trace
		.map((f, i) => `#${i + 1} ${f.file}:${f.line} -> ${f.call}`)
		.join("\n");
	navigator.clipboard.writeText(text);
	copiedTrace.value = true;
	setTimeout(() => {
		copiedTrace.value = false;
	}, 2500);
};
</script>

<template>
    <div class="w-full px-3.5 sm:px-8 pt-4 sm:pt-6 pb-20 min-w-0 space-y-4 sm:space-y-6" style="font-family: var(--wos-font)">
        
        <!-- Header with Live Pulse & Quick Actions -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-gray-200 dark:border-zinc-800 pb-4 sm:pb-5">
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="text-lg sm:text-xl font-black text-gray-900 dark:text-zinc-100 tracking-tight flex items-center gap-2">
                        <Bug class="w-5 h-5 text-rose-600 dark:text-rose-500 shrink-0" />
                        Pusat Diagnostik Error & Insiden
                    </h1>
                    
                    <!-- Live Real-time Indicator -->
                    <div class="flex items-center gap-1.5 px-2.5 py-0.5 sm:py-1 rounded-full text-[10px] sm:text-[11px] font-bold tracking-wide border shadow-xs"
                        :class="autoRefresh ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800' : 'bg-gray-100 dark:bg-zinc-800 text-gray-500 dark:text-zinc-400 border-gray-200 dark:border-zinc-700'">
                        <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full"
                            :class="autoRefresh ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400'"></span>
                        {{ autoRefresh ? 'Live (4s)' : 'Live Pause' }}
                    </div>
                </div>
                <p class="text-[12px] sm:text-[13px] text-gray-500 dark:text-zinc-400 mt-1 max-w-2xl leading-relaxed">
                    Pemantauan otomatis & diagnosa pintar menangkap Error 500, Database SQL, Akses 403, CSRF 419, dan anomali server.
                </p>
            </div>
            
            <!-- Actions Toolbar (Responsive Grid on Mobile) -->
            <div class="grid grid-cols-2 sm:flex sm:items-center gap-2">
                <!-- Toggle Auto Refresh -->
                <button 
                    @click="autoRefresh = !autoRefresh" 
                    class="px-2.5 sm:px-3 py-1.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg text-[11px] sm:text-[12px] font-semibold text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-all flex items-center justify-center gap-1.5 shadow-xs cursor-pointer active:scale-95">
                    <Radio class="w-3.5 h-3.5" :class="autoRefresh ? 'text-emerald-500 animate-spin-slow' : 'text-gray-400'" />
                    <span class="truncate">{{ autoRefresh ? 'Pause Live' : 'Resume Live' }}</span>
                </button>

                <!-- Manual Refresh -->
                <button 
                    @click="manualRefresh" 
                    :disabled="isManualRefreshing"
                    class="px-2.5 sm:px-3 py-1.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg text-[11px] sm:text-[12px] font-semibold text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-all flex items-center justify-center gap-1.5 shadow-xs disabled:opacity-50 cursor-pointer active:scale-95">
                    <RefreshCw class="w-3.5 h-3.5" :class="{'animate-spin text-blue-500': isManualRefreshing}" />
                    <span>Segarkan</span>
                </button>

                <!-- Clear Resolved -->
                <button 
                    @click="promptClearLogs('resolved')" 
                    class="px-2.5 sm:px-3 py-1.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg text-[11px] sm:text-[12px] font-semibold text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-all flex items-center justify-center gap-1.5 shadow-xs cursor-pointer active:scale-95">
                    <CheckCircle2 class="w-3.5 h-3.5 text-emerald-500" />
                    <span class="truncate">Bersihkan Selesai</span>
                </button>

                <!-- Clear All -->
                <button 
                    @click="promptClearLogs('all')" 
                    class="px-2.5 sm:px-3 py-1.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 rounded-lg text-[11px] sm:text-[12px] font-semibold text-rose-700 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/40 transition-all flex items-center justify-center gap-1.5 shadow-xs cursor-pointer active:scale-95">
                    <Trash2 class="w-3.5 h-3.5" />
                    <span class="truncate">Bersihkan Semua</span>
                </button>
            </div>
        </div>

        <!-- Top Metrics Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4">
            <!-- Card 1: Critical 500 -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl p-3 sm:p-4 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] sm:text-[12px] font-semibold text-gray-500 dark:text-zinc-400">Error 500 & Kritis</span>
                    <span class="p-1 sm:p-1.5 bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 rounded-lg">
                        <AlertOctagon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                    </span>
                </div>
                <div class="mt-1.5 sm:mt-2 text-xl sm:text-2xl font-black text-rose-600 dark:text-rose-400 tracking-tight">
                    {{ stats.critical_errors || 0 }}
                </div>
                <div class="text-[10px] sm:text-[11px] text-gray-400 dark:text-zinc-500 mt-0.5">Tindakan segera</div>
            </div>

            <!-- Card 2: Open Issues -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl p-3 sm:p-4 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] sm:text-[12px] font-semibold text-gray-500 dark:text-zinc-400">Masalah Terbuka</span>
                    <span class="p-1 sm:p-1.5 bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 rounded-lg">
                        <AlertTriangle class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                    </span>
                </div>
                <div class="mt-1.5 sm:mt-2 text-xl sm:text-2xl font-black text-amber-600 dark:text-amber-400 tracking-tight">
                    {{ stats.open_issues || 0 }}
                </div>
                <div class="text-[10px] sm:text-[11px] text-gray-400 dark:text-zinc-500 mt-0.5">Belum diselesaikan</div>
            </div>

            <!-- Card 3: Resolved Issues -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl p-3 sm:p-4 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] sm:text-[12px] font-semibold text-gray-500 dark:text-zinc-400">Telah Diselesaikan</span>
                    <span class="p-1 sm:p-1.5 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-lg">
                        <ShieldCheck class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                    </span>
                </div>
                <div class="mt-1.5 sm:mt-2 text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">
                    {{ stats.resolved_issues || 0 }}
                </div>
                <div class="text-[10px] sm:text-[11px] text-gray-400 dark:text-zinc-500 mt-0.5">Berhasil ditangani</div>
            </div>

            <!-- Card 4: Today's Incidents -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl p-3 sm:p-4 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] sm:text-[12px] font-semibold text-gray-500 dark:text-zinc-400">Insiden Hari Ini</span>
                    <span class="p-1 sm:p-1.5 bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 rounded-lg">
                        <Clock class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                    </span>
                </div>
                <div class="mt-1.5 sm:mt-2 text-xl sm:text-2xl font-black text-gray-900 dark:text-zinc-100 tracking-tight">
                    {{ stats.today_count || 0 }}
                </div>
                <div class="text-[10px] sm:text-[11px] text-gray-400 dark:text-zinc-500 mt-0.5">Dari {{ stats.total_incidents || 0 }} total insiden</div>
            </div>
        </div>

        <!-- Filters & Search Toolbar -->
        <div class="flex flex-col lg:flex-row gap-3 items-stretch lg:items-center justify-between">
            <!-- Search -->
            <div class="relative flex-1 w-full lg:max-w-md">
                <Search class="w-4 h-4 text-gray-400 dark:text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2" />
                <input 
                    v-model="search" 
                    type="text" 
                    placeholder="Cari pesan error, file kode, URL, IP, atau user..." 
                    class="w-full pl-9 pr-4 py-2.5 text-[12px] sm:text-[13px] border border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-600 rounded-xl focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 outline-none transition-all shadow-xs">
            </div>

            <!-- Filter Tabs & Status Row (Clean Full Width Scrollable on Mobile) -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0 scrollbar-none w-full lg:w-auto">
                <!-- Severity Tabs -->
                <div class="flex items-center gap-1 bg-gray-100/90 dark:bg-zinc-950 p-1 rounded-xl border border-gray-200/80 dark:border-zinc-800 shrink-0">
                    <button 
                        v-for="tab in severityTabs" 
                        :key="tab.id"
                        @click="selectedSeverity = tab.id"
                        :class="[
                            'px-3 py-1.5 text-[11px] sm:text-[12px] font-bold rounded-lg transition-all cursor-pointer whitespace-nowrap active:scale-95',
                            selectedSeverity === tab.id 
                                ? 'bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 shadow-xs' 
                                : 'text-gray-500 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-zinc-200'
                        ]">
                        {{ tab.label }}
                    </button>
                </div>

                <!-- Status selector -->
                <select 
                    v-model="selectedStatus" 
                    class="px-3 py-2 text-[11px] sm:text-[12px] font-semibold bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-zinc-300 rounded-xl outline-none cursor-pointer shrink-0 shadow-xs">
                    <option value="all">Semua Status</option>
                    <option value="open">⚠️ Belum Selesai</option>
                    <option value="investigating">🔍 Investigasi</option>
                    <option value="resolved">✅ Terselesaikan</option>
                </select>
            </div>
        </div>

        <!-- MAIN INCIDENT LIST: RESPONSIVE DESKTOP TABLE & MOBILE CARD VIEW (No Flickering, Solid Container) -->
        <div class="w-full bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-xs overflow-hidden">
            
            <!-- 1. MOBILE CARD VIEW (Visible on mobile screens < 768px) -->
            <div class="block md:hidden divide-y divide-gray-100 dark:divide-zinc-800/80">
                <template v-if="incidents.data && incidents.data.length">
                    <div 
                        v-for="incident in incidents.data" 
                        :key="`mob-${incident.id}`"
                        @click="openDetail(incident)"
                        class="p-4 hover:bg-rose-50/40 dark:hover:bg-rose-950/15 transition-colors cursor-pointer space-y-2.5 active:bg-rose-50/60">
                        
                        <!-- Top: Severity + Status + Time -->
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <div class="flex items-center gap-1.5">
                                <span :class="[
                                    'px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider',
                                    incident.severity === 'critical' ? 'bg-rose-100 dark:bg-rose-950/50 text-rose-700 dark:text-rose-400' :
                                    incident.severity === 'high' ? 'bg-amber-100 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400' :
                                    'bg-blue-100 dark:bg-blue-950/50 text-blue-700 dark:text-blue-400'
                                ]">
                                    {{ incident.severity }}
                                </span>

                                <span :class="[
                                    'text-[10px] font-bold px-2 py-0.5 rounded capitalize flex items-center gap-1',
                                    incident.mitigation_status === 'resolved' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400' :
                                    incident.mitigation_status === 'investigating' ? 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400' :
                                    'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400'
                                ]">
                                    <span class="w-1.5 h-1.5 rounded-full"
                                        :class="incident.mitigation_status === 'resolved' ? 'bg-emerald-500' : (incident.mitigation_status === 'investigating' ? 'bg-blue-500' : 'bg-rose-500')"></span>
                                    {{ incident.mitigation_status === 'open' ? 'Belum Selesai' : (incident.mitigation_status === 'investigating' ? 'Investigasi' : 'Selesai') }}
                                </span>
                            </div>

                            <span class="text-[11px] font-medium text-gray-400 dark:text-zinc-500 font-mono">
                                {{ formatRelativeTime(incident.created_at) }}
                            </span>
                        </div>

                        <!-- Incident Title & Message -->
                        <div>
                            <div class="font-bold text-gray-900 dark:text-zinc-100 text-[13px] leading-snug">
                                {{ incident.incident_type }}
                            </div>
                            <div class="text-[12px] text-gray-500 dark:text-zinc-400 line-clamp-2 mt-0.5" v-if="incident.details?.root_cause || incident.details?.message">
                                {{ incident.details?.root_cause || incident.details?.message }}
                            </div>
                        </div>

                        <!-- Code File & User Info -->
                        <div class="flex items-center justify-between pt-1 text-[11px] text-gray-500 dark:text-zinc-400">
                            <div v-if="incident.details?.file" class="font-mono text-rose-600 dark:text-rose-400 font-semibold flex items-center gap-1 truncate max-w-[180px]">
                                <FileCode class="w-3.5 h-3.5 shrink-0" />
                                <span class="truncate">{{ incident.details.file.split('/').pop() }}:{{ incident.details.line }}</span>
                            </div>
                            <div v-else>-</div>

                            <div class="font-medium text-gray-700 dark:text-zinc-300 flex items-center gap-1">
                                <User class="w-3.5 h-3.5 text-gray-400 shrink-0" />
                                <span class="truncate max-w-[120px]">{{ incident.user?.name || incident.user?.email || 'Tamu' }}</span>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Mobile Empty State (Generous Padding & Fully Visible) -->
                <div v-else class="py-12 px-6 text-center text-gray-500 dark:text-zinc-400">
                    <ShieldCheck class="w-12 h-12 text-emerald-500 mx-auto mb-3" />
                    <h3 class="text-base font-black text-gray-900 dark:text-zinc-100">Semua Sistem Berjalan Normal</h3>
                    <p class="text-[12px] text-gray-400 mt-1 max-w-xs mx-auto">
                        Tidak ada insiden atau exception yang ditemukan pada filter ini.
                    </p>
                </div>
            </div>

            <!-- 2. DESKTOP TABLE VIEW (Visible on md+ screens) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-[13px] whitespace-nowrap">
                    <caption class="sr-only">Tabel Insiden & Diagnostik Error</caption>
                    <thead class="bg-gray-50 dark:bg-zinc-800/40 border-b border-gray-200 dark:border-zinc-800 text-gray-500 dark:text-zinc-400 sticky top-0 z-10">
                        <tr>
                            <th class="px-5 py-3.5 font-semibold">Waktu Terdeteksi</th>
                            <th class="px-5 py-3.5 font-semibold">Tipe Error & Diagnosa</th>
                            <th class="px-5 py-3.5 font-semibold">Lokasi Kode</th>
                            <th class="px-5 py-3.5 font-semibold">Pengguna / IP</th>
                            <th class="px-5 py-3.5 font-semibold">Status</th>
                            <th class="px-5 py-3.5 font-semibold">Tingkat Bahaya</th>
                            <th class="px-5 py-3.5 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/80">
                        <template v-if="incidents.data && incidents.data.length">
                            <tr 
                                v-for="incident in incidents.data" 
                                :key="incident.id" 
                                @click="openDetail(incident)" 
                                class="hover:bg-rose-50/40 dark:hover:bg-rose-950/15 transition-colors cursor-pointer group">
                                
                                <!-- Timestamp -->
                                <td class="px-5 py-3.5 text-gray-500 dark:text-zinc-400">
                                    <div class="font-semibold text-gray-900 dark:text-zinc-200 text-[12px]">
                                        {{ formatRelativeTime(incident.created_at) }}
                                    </div>
                                    <div class="font-mono text-[11px] text-gray-400 dark:text-zinc-500">
                                        {{ formatDate(incident.created_at) }}
                                    </div>
                                </td>

                                <!-- Incident Type & Message -->
                                <td class="px-5 py-3.5 max-w-md truncate">
                                    <div class="font-bold text-gray-900 dark:text-zinc-100 flex items-center gap-1.5 truncate">
                                        <AlertOctagon v-if="incident.severity === 'critical'" class="w-4 h-4 text-rose-600 shrink-0" />
                                        <AlertTriangle v-else-if="incident.severity === 'high'" class="w-4 h-4 text-amber-500 shrink-0" />
                                        <Info v-else class="w-4 h-4 text-blue-500 shrink-0" />
                                        
                                        <span class="truncate">{{ incident.incident_type }}</span>
                                    </div>
                                    <div class="text-[12px] text-gray-500 dark:text-zinc-400 truncate mt-0.5" v-if="incident.details?.root_cause || incident.details?.message">
                                        {{ incident.details?.root_cause || incident.details?.message }}
                                    </div>
                                </td>

                                <!-- File & Line Location -->
                                <td class="px-5 py-3.5">
                                    <div v-if="incident.details?.file" class="font-mono text-[12px] text-gray-700 dark:text-zinc-300 flex items-center gap-1.5">
                                        <FileCode class="w-3.5 h-3.5 text-gray-400 shrink-0" />
                                        <span class="truncate max-w-[200px]" :title="incident.details.file">{{ incident.details.file.split('/').pop() }}:{{ incident.details.line }}</span>
                                    </div>
                                    <span v-else class="text-gray-400 font-mono text-[12px]">-</span>
                                </td>

                                <!-- Actor / IP -->
                                <td class="px-5 py-3.5">
                                    <div class="text-gray-900 dark:text-zinc-200 font-medium text-[12px] flex items-center gap-1">
                                        <User class="w-3.5 h-3.5 text-gray-400" />
                                        {{ incident.user?.name || incident.user?.email || 'Tamu (Unauthenticated)' }}
                                    </div>
                                    <div class="font-mono text-[11px] text-gray-400 dark:text-zinc-500">
                                        {{ incident.ip_address || '127.0.0.1' }}
                                    </div>
                                </td>

                                <!-- Status Badge -->
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-1.5">
                                        <CheckCircle2 v-if="incident.mitigation_status === 'resolved'" class="w-4 h-4 text-emerald-500" />
                                        <Clock v-else-if="incident.mitigation_status === 'investigating'" class="w-4 h-4 text-blue-500" />
                                        <AlertCircle v-else class="w-4 h-4 text-rose-500 animate-pulse" />

                                        <span :class="[
                                            'text-[11px] font-bold px-2 py-0.5 rounded-md capitalize',
                                            incident.mitigation_status === 'resolved' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400' :
                                            incident.mitigation_status === 'investigating' ? 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400' :
                                            'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400'
                                        ]">
                                            {{ incident.mitigation_status === 'open' ? 'Belum Selesai' : (incident.mitigation_status === 'investigating' ? 'Investigasi' : 'Selesai') }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Severity Badge -->
                                <td class="px-5 py-3.5">
                                    <span :class="[
                                        'inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-black uppercase tracking-wider',
                                        incident.severity === 'critical' ? 'bg-rose-100 dark:bg-rose-950/50 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-900/60' :
                                        incident.severity === 'high' ? 'bg-amber-100 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-900/60' :
                                        'bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-900/60'
                                    ]">
                                        {{ incident.severity }}
                                    </span>
                                </td>

                                <!-- Action Quick Detail -->
                                <td class="px-5 py-3.5 text-right">
                                    <button 
                                        @click.stop="openDetail(incident)" 
                                        class="px-2.5 py-1 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 hover:text-rose-600 dark:hover:text-rose-400 hover:border-rose-200 rounded-md text-[12px] font-semibold transition-all inline-flex items-center gap-1 shadow-xs cursor-pointer active:scale-95">
                                        <Eye class="w-3.5 h-3.5" />
                                        Inspeksi
                                    </button>
                                </td>
                            </tr>
                        </template>

                        <!-- Empty State Desktop -->
                        <tr v-else>
                            <td colspan="7" class="px-5 py-16 text-center text-gray-500 dark:text-zinc-400">
                                <ShieldCheck class="w-12 h-12 text-emerald-500 mx-auto mb-3" />
                                <h3 class="text-base font-black text-gray-900 dark:text-zinc-100">Semua Sistem Berjalan Normal</h3>
                                <p class="text-[13px] text-gray-500 dark:text-zinc-400 mt-1 max-w-sm mx-auto">
                                    Tidak ada insiden atau exception yang belum terselesaikan pada filter yang dipilih.
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar (Solid & Stable) -->
            <div class="px-4 sm:px-5 py-3.5 border-t border-gray-200 dark:border-zinc-800 bg-gray-50/80 dark:bg-zinc-800/30 flex items-center justify-between text-[12px] sm:text-[13px] text-gray-500 dark:text-zinc-400">
                <div class="text-[11px] sm:text-[12px] font-medium">
                    <span class="hidden sm:inline">Menampilkan </span><span class="font-bold text-gray-900 dark:text-zinc-200">{{ incidents.data?.length || 0 }}</span> dari <span class="font-bold text-gray-900 dark:text-zinc-200">{{ incidents.total || 0 }}</span>
                </div>
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <button 
                        @click="fetchIncidents(incidents.current_page - 1)" 
                        :disabled="incidents.current_page <= 1"
                        class="px-2.5 sm:px-3 py-1 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg disabled:opacity-40 text-gray-700 dark:text-zinc-300 font-semibold text-[11px] sm:text-[12px] hover:bg-gray-50 dark:hover:bg-zinc-800 cursor-pointer active:scale-95">
                        Prev
                    </button>
                    <span class="text-[11px] sm:text-[12px] font-mono font-medium">{{ incidents.current_page || 1 }}/{{ incidents.last_page || 1 }}</span>
                    <button 
                        @click="fetchIncidents(incidents.current_page + 1)" 
                        :disabled="incidents.current_page >= incidents.last_page"
                        class="px-2.5 sm:px-3 py-1 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg disabled:opacity-40 text-gray-700 dark:text-zinc-300 font-semibold text-[11px] sm:text-[12px] hover:bg-gray-50 dark:hover:bg-zinc-800 cursor-pointer active:scale-95">
                        Next
                    </button>
                </div>
            </div>
        </div>

        <!-- SLIDE-OVER DETAIL INSPECTOR DRAWER -->
        <div v-if="isDetailModalOpen" class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
            <div class="absolute inset-0 overflow-hidden">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-gray-900/40 dark:bg-black/60 backdrop-blur-xs transition-opacity" @click="isDetailModalOpen = false"></div>
                
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-0 sm:pl-10">
                    <div class="pointer-events-auto w-screen max-w-full sm:max-w-3xl transform transition-transform bg-white dark:bg-zinc-900 shadow-2xl flex flex-col border-l border-gray-200 dark:border-zinc-800">
                        
                        <!-- Header Drawer -->
                        <div class="px-4 sm:px-6 py-3.5 sm:py-4.5 border-b border-gray-200 dark:border-zinc-800 flex items-center justify-between bg-gray-50 dark:bg-zinc-950">
                            <div class="flex items-center gap-2.5">
                                <span class="p-1.5 sm:p-2 bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 rounded-lg shrink-0">
                                    <Bug class="w-4 h-4 sm:w-5 sm:h-5" />
                                </span>
                                <div class="min-w-0">
                                    <h2 class="text-sm sm:text-base font-black text-gray-900 dark:text-zinc-100 truncate">
                                        Inspektor Diagnostik Insiden
                                    </h2>
                                    <p class="text-[10px] sm:text-[11px] text-gray-400 font-mono truncate">ID: {{ selectedIncident?.id }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-1.5 sm:gap-2">
                                <!-- 1-Click Copy Diagnostic Report -->
                                <button 
                                    @click="copyDiagnosticReport" 
                                    class="px-2.5 sm:px-3 py-1.5 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg text-[11px] sm:text-[12px] font-bold text-gray-700 dark:text-zinc-200 hover:bg-gray-50 dark:hover:bg-zinc-700 transition-all flex items-center gap-1.5 shadow-xs cursor-pointer active:scale-95">
                                    <Check v-if="copiedReport" class="w-3.5 h-3.5 text-emerald-500" />
                                    <Copy v-else class="w-3.5 h-3.5 text-gray-500" />
                                    <span class="hidden sm:inline">{{ copiedReport ? 'Laporan Tersalin!' : 'Salin Laporan Error' }}</span>
                                    <span class="sm:hidden">{{ copiedReport ? 'Tersalin' : 'Salin' }}</span>
                                </button>

                                <button 
                                    @click="isDetailModalOpen = false" 
                                    class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-zinc-200 rounded-lg hover:bg-gray-100 dark:hover:bg-zinc-800 cursor-pointer">
                                    <X class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                        
                        <!-- Body Drawer -->
                        <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4 sm:space-y-6" v-if="selectedIncident">
                            
                            <!-- Error Title & Badges -->
                            <div class="bg-gray-50 dark:bg-zinc-950/70 p-3.5 sm:p-4 rounded-xl border border-gray-200 dark:border-zinc-800">
                                <div class="flex items-center justify-between flex-wrap gap-2 mb-2">
                                    <span :class="[
                                        'px-2.5 py-0.5 rounded-md text-[10px] sm:text-[11px] font-black uppercase tracking-wider',
                                        selectedIncident.severity === 'critical' ? 'bg-rose-100 dark:bg-rose-950/50 text-rose-700 dark:text-rose-400' :
                                        selectedIncident.severity === 'high' ? 'bg-amber-100 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400' :
                                        'bg-blue-100 dark:bg-blue-950/50 text-blue-700 dark:text-blue-400'
                                    ]">
                                        {{ selectedIncident.severity }}
                                    </span>

                                    <div class="text-[11px] text-gray-500 dark:text-zinc-400 font-mono">
                                        {{ formatDate(selectedIncident.created_at) }}
                                    </div>
                                </div>
                                <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-zinc-100 break-words">
                                    {{ selectedIncident.incident_type }}
                                </h3>
                            </div>

                            <!-- 1. ROOT CAUSE SUMMARY (Penyebab Utama) -->
                            <div class="bg-rose-50/70 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/50 rounded-xl p-3.5 sm:p-4.5">
                                <div class="flex items-start gap-3">
                                    <div class="p-1.5 sm:p-2 bg-rose-500 text-white rounded-lg shrink-0 mt-0.5">
                                        <AlertOctagon class="w-4 h-4" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-[12px] sm:text-[13px] font-black text-rose-900 dark:text-rose-300 uppercase tracking-wider mb-1">
                                            Diagnosa Masalah (Penyebab Utama)
                                        </h4>
                                        <p class="text-[12px] sm:text-[13px] text-rose-950 dark:text-rose-200 leading-relaxed font-medium">
                                            {{ selectedIncident.details?.root_cause || selectedIncident.details?.message || 'Tidak ada detail diagnosa spesifik.' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. SUGGESTED FIX (Rekomendasi Solusi) -->
                            <div class="bg-emerald-50/70 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/50 rounded-xl p-3.5 sm:p-4.5">
                                <div class="flex items-start gap-3">
                                    <div class="p-1.5 sm:p-2 bg-emerald-600 text-white rounded-lg shrink-0 mt-0.5">
                                        <Wrench class="w-4 h-4" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-[12px] sm:text-[13px] font-black text-emerald-900 dark:text-emerald-300 uppercase tracking-wider mb-1">
                                            Rekomendasi Solusi & Tindakan
                                        </h4>
                                        <p class="text-[12px] sm:text-[13px] text-emerald-950 dark:text-emerald-200 leading-relaxed font-medium">
                                            {{ selectedIncident.details?.suggested_fix || 'Periksa penanganan try-catch atau konfigurasi sistem terkait.' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. CODE LOCATION & HTTP REQUEST CONTEXT -->
                            <div class="bg-gray-50 dark:bg-zinc-950 rounded-xl p-4 sm:p-5 border border-gray-200 dark:border-zinc-800 space-y-3 sm:space-y-4">
                                <h4 class="text-[11px] sm:text-[12px] font-black text-gray-900 dark:text-zinc-100 uppercase tracking-wider flex items-center gap-2">
                                    <Server class="w-4 h-4 text-blue-500" />
                                    Konteks Request & Lingkungan
                                </h4>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-[12px] sm:text-[13px]">
                                    <!-- File & Line -->
                                    <div class="sm:col-span-2 bg-white dark:bg-zinc-900 p-3 rounded-lg border border-gray-200/70 dark:border-zinc-800">
                                        <div class="text-[10px] sm:text-[11px] font-bold text-gray-400 dark:text-zinc-500 mb-0.5">File Lokasi Penyebab:</div>
                                        <div class="font-mono text-[11px] sm:text-[12px] text-rose-600 dark:text-rose-400 break-all font-semibold">
                                            {{ selectedIncident.details?.file || '-' }}:{{ selectedIncident.details?.line || '-' }}
                                        </div>
                                    </div>

                                    <!-- URL -->
                                    <div class="bg-white dark:bg-zinc-900 p-3 rounded-lg border border-gray-200/70 dark:border-zinc-800">
                                        <div class="text-[10px] sm:text-[11px] font-bold text-gray-400 dark:text-zinc-500 mb-0.5">Endpoint URL:</div>
                                        <div class="font-mono text-[11px] sm:text-[12px] text-gray-900 dark:text-zinc-100 break-all">
                                            <span class="px-1.5 py-0.5 bg-gray-100 dark:bg-zinc-800 rounded font-bold text-gray-700 dark:text-zinc-300 mr-1">
                                                {{ selectedIncident.details?.method || 'GET' }}
                                            </span>
                                            {{ selectedIncident.details?.url || '-' }}
                                        </div>
                                    </div>

                                    <!-- Route Name -->
                                    <div class="bg-white dark:bg-zinc-900 p-3 rounded-lg border border-gray-200/70 dark:border-zinc-800">
                                        <div class="text-[10px] sm:text-[11px] font-bold text-gray-400 dark:text-zinc-500 mb-0.5">Nama Rute:</div>
                                        <div class="font-mono text-[11px] sm:text-[12px] text-gray-900 dark:text-zinc-100">
                                            {{ selectedIncident.details?.route_name || '-' }}
                                        </div>
                                    </div>

                                    <!-- User -->
                                    <div class="bg-white dark:bg-zinc-900 p-3 rounded-lg border border-gray-200/70 dark:border-zinc-800">
                                        <div class="text-[10px] sm:text-[11px] font-bold text-gray-400 dark:text-zinc-500 mb-0.5">Pengguna Terdampak:</div>
                                        <div class="text-gray-900 dark:text-zinc-100 font-medium">
                                            {{ selectedIncident.user?.name || selectedIncident.user?.email || 'Tamu' }}
                                            <span v-if="selectedIncident.user?.id" class="text-gray-400 text-[10px] font-mono">(ID: {{ selectedIncident.user.id }})</span>
                                        </div>
                                    </div>

                                    <!-- IP -->
                                    <div class="bg-white dark:bg-zinc-900 p-3 rounded-lg border border-gray-200/70 dark:border-zinc-800">
                                        <div class="text-[10px] sm:text-[11px] font-bold text-gray-400 dark:text-zinc-500 mb-0.5">Alamat IP:</div>
                                        <div class="font-mono text-[11px] sm:text-[12px] text-gray-900 dark:text-zinc-100">
                                            {{ selectedIncident.ip_address || '-' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. RAW EXCEPTION MESSAGE -->
                            <div v-if="selectedIncident.details?.message" class="space-y-2">
                                <div class="text-[11px] sm:text-[12px] font-black text-gray-900 dark:text-zinc-100 uppercase tracking-wider flex items-center justify-between">
                                    <span>Pesan Exception Teknis</span>
                                    <span class="text-[10px] font-mono text-gray-400">{{ selectedIncident.details?.exception_class }}</span>
                                </div>
                                <div class="bg-zinc-900 text-rose-400 p-3.5 sm:p-4 rounded-xl font-mono text-[11px] sm:text-[12px] leading-relaxed overflow-x-auto border border-zinc-800">
                                    {{ selectedIncident.details.message }}
                                </div>
                            </div>

                            <!-- 5. APPLICATION STACK TRACE VIEWER -->
                            <div v-if="selectedIncident.details?.stack_trace && selectedIncident.details.stack_trace.length" class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] sm:text-[12px] font-black text-gray-900 dark:text-zinc-100 uppercase tracking-wider flex items-center gap-1.5">
                                        <Terminal class="w-4 h-4 text-emerald-500" />
                                        Cuplikan Stack Trace
                                    </span>
                                    <button 
                                        @click="copyStackTrace" 
                                        class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1 bg-transparent border-0 cursor-pointer">
                                        <Copy class="w-3 h-3" />
                                        {{ copiedTrace ? 'Tersalin!' : 'Salin Trace' }}
                                    </button>
                                </div>

                                <div class="bg-zinc-950 border border-zinc-800 rounded-xl overflow-hidden shadow-inner">
                                    <div class="divide-y divide-zinc-800/60 font-mono text-[11px] sm:text-[12px]">
                                        <div 
                                            v-for="(frame, idx) in selectedIncident.details.stack_trace" 
                                            :key="`st-${idx}`"
                                            :class="[
                                                'p-3 transition-colors flex items-start gap-2.5',
                                                frame.is_app ? 'bg-zinc-900/90 text-emerald-400' : 'text-zinc-400'
                                            ]">
                                            <span class="text-zinc-600 select-none font-bold text-[10px] w-5 shrink-0 text-right">#{{ idx + 1 }}</span>
                                            <div class="flex-1 min-w-0 break-all">
                                                <div :class="frame.is_app ? 'text-emerald-300 font-semibold' : 'text-zinc-400'">
                                                    {{ frame.call }}
                                                </div>
                                                <div class="text-[10px] sm:text-[11px] text-zinc-500 mt-0.5">
                                                    {{ frame.file }}:{{ frame.line }}
                                                </div>
                                            </div>
                                            <span v-if="frame.is_app" class="px-1.5 py-0.5 bg-emerald-950/80 border border-emerald-800/60 text-emerald-400 text-[9px] font-black rounded uppercase tracking-wider shrink-0">
                                                App
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 6. REQUEST PAYLOAD (IF ANY) -->
                            <div v-if="selectedIncident.details?.payload && Object.keys(selectedIncident.details.payload).length" class="space-y-2">
                                <div class="text-[11px] sm:text-[12px] font-black text-gray-900 dark:text-zinc-100 uppercase tracking-wider">
                                    Parameter Payload Request
                                </div>
                                <div class="bg-zinc-950 text-zinc-300 p-3.5 sm:p-4 rounded-xl font-mono text-[11px] sm:text-[12px] overflow-x-auto border border-zinc-800">
                                    <pre>{{ JSON.stringify(selectedIncident.details.payload, null, 2) }}</pre>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="p-3.5 sm:p-4.5 border-t border-gray-200 dark:border-zinc-800 bg-gray-50 dark:bg-zinc-950 flex items-center justify-between flex-wrap gap-2.5" v-if="selectedIncident">
                            <div class="flex items-center gap-1.5 sm:gap-2">
                                <span class="text-[11px] sm:text-[12px] font-medium text-gray-500 dark:text-zinc-400 hidden sm:inline">Status:</span>
                                <button 
                                    @click="updateStatus('resolved')" 
                                    :disabled="isUpdatingStatus || selectedIncident.mitigation_status === 'resolved'"
                                    class="px-2.5 sm:px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-lg text-[11px] sm:text-[12px] font-bold transition-all flex items-center gap-1.5 shadow-xs cursor-pointer active:scale-95">
                                    <CheckCircle2 class="w-3.5 h-3.5" />
                                    Tandai Selesai
                                </button>
                                <button 
                                    @click="updateStatus('investigating')" 
                                    :disabled="isUpdatingStatus || selectedIncident.mitigation_status === 'investigating'"
                                    class="px-2.5 sm:px-3 py-1.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white rounded-lg text-[11px] sm:text-[12px] font-bold transition-all flex items-center gap-1.5 shadow-xs cursor-pointer active:scale-95">
                                    <Clock class="w-3.5 h-3.5" />
                                    Investigasi
                                </button>
                            </div>

                            <button 
                                @click="promptDeleteIncident(selectedIncident.id)" 
                                class="px-2.5 sm:px-3 py-1.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 hover:bg-rose-100 text-rose-700 dark:text-rose-400 rounded-lg text-[11px] sm:text-[12px] font-bold transition-all flex items-center gap-1.5 cursor-pointer active:scale-95">
                                <Trash2 class="w-3.5 h-3.5" />
                                Hapus Log
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MODERN CONFIRMATION MODAL (Replaces window.confirm)                        -->
        <!-- ========================================================================= -->
        <div v-if="confirmModal.isOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                <!-- Backdrop with blur -->
                <div class="fixed inset-0 bg-gray-900/50 dark:bg-black/70 backdrop-blur-xs transition-opacity" @click="confirmModal.isOpen = false"></div>

                <!-- Modal Dialog Box -->
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-zinc-900 text-left shadow-2xl transition-all w-full max-w-md border border-gray-200 dark:border-zinc-800 p-6 sm:p-7 space-y-5 animate-scale-in">
                    
                    <!-- Icon + Title -->
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 rounded-xl shrink-0 border border-rose-200/60 dark:border-rose-900/50">
                            <Trash2 class="w-6 h-6" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-black text-gray-900 dark:text-zinc-100 leading-snug" id="modal-title">
                                {{ confirmModal.title }}
                            </h3>
                            <p class="text-[13px] text-gray-500 dark:text-zinc-400 mt-1.5 leading-relaxed font-medium">
                                {{ confirmModal.message }}
                            </p>
                        </div>
                    </div>

                    <!-- Buttons Actions -->
                    <div class="flex items-center justify-end gap-2.5 pt-2">
                        <button 
                            type="button" 
                            @click="confirmModal.isOpen = false"
                            :disabled="confirmModal.isProcessing"
                            class="px-4 py-2.5 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-700 rounded-xl text-[13px] font-bold transition-all shadow-xs cursor-pointer active:scale-95 disabled:opacity-50">
                            Batal
                        </button>
                        <button 
                            type="button" 
                            @click="handleConfirmAction"
                            :disabled="confirmModal.isProcessing"
                            class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 disabled:opacity-50 text-white rounded-xl text-[13px] font-bold transition-all shadow-md shadow-rose-500/20 flex items-center gap-2 cursor-pointer active:scale-95">
                            <RefreshCw v-if="confirmModal.isProcessing" class="w-4 h-4 animate-spin" />
                            <Trash2 v-else class="w-4 h-4" />
                            {{ confirmModal.isProcessing ? 'Menghapus...' : confirmModal.confirmText }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<style scoped>
@keyframes spinSlow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin-slow {
    animation: spinSlow 3s linear infinite;
}

@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
.animate-scale-in {
    animation: scaleIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.scrollbar-none::-webkit-scrollbar {
    display: none;
}
.scrollbar-none {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>