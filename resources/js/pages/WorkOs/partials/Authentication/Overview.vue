<script setup lang="ts">
import type { ApexOptions } from "apexcharts";
import axios from "axios";
import {
	AlertTriangle,
	Loader2,
	RefreshCw,
	ShieldCheck,
	Trash2,
	TrendingUp,
	Users,
} from "lucide-vue-next";
import { computed, onMounted, onUnmounted, reactive, ref, watch } from "vue";
import VueApexCharts from "vue3-apexcharts";
import AppModal from "../../components/ui/AppModal.vue";
import MotionTabs from "@/components/ui/tabs/MotionTabs.vue";

// ─────────────────────────────────────────────────────────────────────────────
// State & Core API Loading
// ─────────────────────────────────────────────────────────────────────────────
const isLoading = ref(true);
const data = ref<any>(null);
const days = ref(7);
const interval = ref("daily");
const chartType = ref<"line" | "bar">("line");

// Track document dark mode class reactively for ApexCharts themes
const isDark = ref(document.documentElement.classList.contains("dark"));
let themeObserver: MutationObserver | null = null;

const loadAnalytics = async () => {
	if (!data.value) {
		isLoading.value = true;
	}
	try {
		const res = await axios.get("/workos/auth-platform/analytics", {
			params: { days: days.value, interval: interval.value },
		});
		data.value = res.data;
	} catch (err) {
		console.error("Failed to load authentication analytics", err);
	} finally {
		isLoading.value = false;
	}
};

let intervalId: any = null;



watch([days, interval], () => loadAnalytics());

// ─────────────────────────────────────────────────────────────────────────────
// Reactive Chart Configurations
// ─────────────────────────────────────────────────────────────────────────────
const loginTrendSeries = computed(() => {
	const trends = data.value?.trends ?? [];
	return [
		{ name: "Successful", data: trends.map((t: any) => t.successful) },
		{ name: "Failed", data: trends.map((t: any) => t.failed) },
	];
});

const loginTrendOptions = computed(
	(): ApexOptions => ({
		chart: {
			type: chartType.value as "line" | "bar",
			height: 240,
			toolbar: { show: false },
			animations: { enabled: true, speed: 350 },
			fontFamily: "var(--wos-font, Inter, sans-serif)",
			background: "transparent",
		},
		theme: {
			mode: isDark.value ? "dark" : "light",
		},
		colors: ["#3b82f6", "#ef4444"], // Vibrant Blue and Red
		stroke: {
			curve: "smooth" as const,
			width: chartType.value === "line" ? 2.5 : 0,
		},
		fill: {
			type: (chartType.value === "line" ? "gradient" : "solid") as "gradient" | "solid",
			gradient: {
				shade: isDark.value ? "dark" : "light",
				type: "vertical",
				shadeIntensity: 0.5,
				gradientToColors: ["#60a5fa", "#f87171"],
				opacityFrom: 0.35,
				opacityTo: 0,
			},
		},
		dataLabels: { enabled: false },
		xaxis: {
			categories: data.value?.trends?.map((t: any) => t.period) ?? [],
			labels: { style: { colors: isDark.value ? "#a1a1aa" : "#9ca3af", fontSize: "11px" } },
			axisBorder: { show: false },
			axisTicks: { show: false },
		},
		yaxis: {
			labels: { style: { colors: isDark.value ? "#a1a1aa" : "#9ca3af", fontSize: "11px" } },
		},
		grid: {
			borderColor: isDark.value ? "#27272a" : "#f3f4f6", // zinc-800 or gray-100
			strokeDashArray: 4,
		},
		legend: {
			position: "top" as const,
			fontSize: "12px",
			labels: { colors: isDark.value ? "#e4e4e7" : "#374151" },
		},
		tooltip: {
			theme: isDark.value ? "dark" : "light",
			x: { show: true },
		},
	}),
);

const providerSeries = computed(() =>
	Object.values(data.value?.providerStats ?? {}).map(Number),
);

const providerOptions = computed(
	(): ApexOptions => ({
		chart: {
			type: "donut" as const,
			height: 200,
			toolbar: { show: false },
			fontFamily: "var(--wos-font, Inter, sans-serif)",
			background: "transparent",
		},
		theme: {
			mode: isDark.value ? "dark" : "light",
		},
		labels: Object.keys(data.value?.providerStats ?? {}),
		colors: ["#3b82f6", "#06b6d4", "#f59e0b", "#10b981"],
		dataLabels: { enabled: false },
		legend: {
			position: "bottom" as const,
			fontSize: "12px",
			labels: { colors: isDark.value ? "#e4e4e7" : "#374151" },
		},
		plotOptions: { pie: { donut: { size: "65%" } } },
		tooltip: {
			theme: isDark.value ? "dark" : "light",
			y: { formatter: (v: number) => `${v} logins` },
		},
	}),
);

const riskSeries = computed(() => [
	data.value?.riskDistribution?.safe ?? 0,
	data.value?.riskDistribution?.medium ?? 0,
	data.value?.riskDistribution?.high ?? 0,
]);

const riskOptions = computed(
	(): ApexOptions => ({
		chart: {
			type: "donut" as const,
			height: 200,
			fontFamily: "var(--wos-font, Inter, sans-serif)",
			toolbar: { show: false },
			background: "transparent",
		},
		theme: {
			mode: isDark.value ? "dark" : "light",
		},
		labels: ["Safe", "Medium", "High"],
		colors: ["#10b981", "#f59e0b", "#ef4444"],
		dataLabels: { enabled: false },
		legend: {
			position: "bottom" as const,
			fontSize: "11px",
			labels: { colors: isDark.value ? "#e4e4e7" : "#374151" },
		},
		plotOptions: { pie: { donut: { size: "65%" } } },
		tooltip: {
			theme: isDark.value ? "dark" : "light",
		},
	}),
);

// ─────────────────────────────────────────────────────────────────────────────
// Interactive Drill-Down Table Logic
// ─────────────────────────────────────────────────────────────────────────────
const selectedDetailTab = ref<"active_sessions" | "new_users" | "failed_logins">("active_sessions");

const detailTabs = computed(() => [
	{ id: "active_sessions", label: "Active Sessions", badge: data.value?.activeSessions ?? 0 },
	{ id: "new_users", label: "New Users", badge: data.value?.newUsers ?? 0 },
	{ id: "failed_logins", label: "Failed Logins", badge: data.value?.failedAttempts ?? 0, badgeClass: selectedDetailTab.value === 'failed_logins' ? 'bg-red-50 dark:bg-zinc-650 text-red-600 dark:text-red-400' : 'bg-red-50/50 dark:bg-zinc-800 text-red-500/70 dark:text-red-400/70' },
]);

const scrollToDetails = () => {
	setTimeout(() => {
		const el = document.getElementById("analytics-drill-down");
		const container = el?.closest(".overflow-y-auto");
		if (el && container) {
			const elRect = el.getBoundingClientRect();
			const containerRect = container.getBoundingClientRect();
			const relativeTop = elRect.top - containerRect.top + container.scrollTop;
			container.scrollTo({ top: relativeTop - 16, behavior: "smooth" });
		}
	}, 100);
};

const selectTabAndScroll = (tab: any) => {
	selectedDetailTab.value = tab;
	scrollToDetails();
};

onMounted(() => {
	loadAnalytics();
	intervalId = setInterval(loadAnalytics, 15000);

	// Observe dark class changes on <html> tag to adjust chart text/grid colors live
	themeObserver = new MutationObserver(() => {
		isDark.value = document.documentElement.classList.contains("dark");
	});
	themeObserver.observe(document.documentElement, {
		attributes: true,
		attributeFilter: ["class"],
	});
});

onUnmounted(() => {
	if (intervalId) clearInterval(intervalId);
	if (themeObserver) themeObserver.disconnect();
});

// ─────────────────────────────────────────────────────────────────────────────
// Modal & Delete/Revoke Handlers
// ─────────────────────────────────────────────────────────────────────────────
const confirmModal = reactive({
	show: false,
	title: "",
	description: "",
	message: "",
	confirmText: "",
	confirmBgClass: "bg-red-600 hover:bg-red-700",
	onConfirm: async () => {},
	isLoading: false,
});

interface ConfirmOptions {
	title: string;
	description?: string;
	message: string;
	confirmText?: string;
	confirmBgClass?: string;
	onConfirm: () => void | Promise<void>;
}

function openConfirm({
	title,
	description,
	message,
	confirmText,
	confirmBgClass,
	onConfirm,
}: ConfirmOptions) {
	confirmModal.title = title;
	confirmModal.description = description || "";
	confirmModal.message = message;
	confirmModal.confirmText = confirmText || "Yes, proceed";
	confirmModal.confirmBgClass = confirmBgClass || "bg-red-600 hover:bg-red-700";
	confirmModal.onConfirm = async () => {
		confirmModal.isLoading = true;
		try {
			await onConfirm();
			confirmModal.show = false;
		} finally {
			confirmModal.isLoading = false;
		}
	};
	confirmModal.show = true;
}

const revokeSession = async (id: string) => {
	openConfirm({
		title: "Revoke session",
		description: "This will terminate the user's active session.",
		message: "Are you sure you want to revoke this session?",
		confirmText: "Yes, revoke",
		onConfirm: async () => {
			try {
				await axios.delete(`/workos/auth-platform/sessions/${id}`);
				await loadAnalytics();
			} catch {
				alert("Failed to revoke session.");
			}
		},
	});
};

const deleteUser = async (id: string) => {
	openConfirm({
		title: "Delete user",
		description: "This action will permanently delete the user account.",
		message: "Are you sure you want to delete this user? This will completely remove the user account.",
		confirmText: "Yes, delete",
		onConfirm: async () => {
			try {
				await axios.delete(`/workos/auth-platform/analytics/users/${id}`);
				await loadAnalytics();
			} catch {
				alert("Failed to delete user.");
			}
		},
	});
};

const deleteFailedLogin = async (id: string) => {
	openConfirm({
		title: "Clear record",
		description: "This action will clear the failed login attempt record.",
		message: "Are you sure you want to clear this failed login attempt record?",
		confirmText: "Yes, clear",
		onConfirm: async () => {
			try {
				await axios.delete(`/workos/auth-platform/analytics/failed-logins/${id}`);
				await loadAnalytics();
			} catch {
				alert("Failed to delete record.");
			}
		},
	});
};

const clearAnalytics = () => {
	openConfirm({
		title: "Reset Analytics Data",
		description: "This will permanently clear all login attempts and active sessions.",
		message: "Are you sure you want to clear all authentication analytics data?",
		confirmText: "Yes, clear all",
		onConfirm: async () => {
			try {
				await axios.post("/workos/auth-platform/analytics/clear");
				await loadAnalytics();
			} catch {
				alert("Failed to clear analytics data.");
			}
		},
	});
};
</script>

<template>
    <div class="space-y-6 animate-fade-in max-w-[900px] relative">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-gray-200 dark:border-zinc-700 pb-5">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-zinc-100 tracking-tight">Analytics</h1>
                <p class="text-[14px] text-gray-500 dark:text-zinc-400 mt-1">Real-time authentication platform metrics.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <!-- Reset / Clear Data -->
                <button 
                    v-if="data"
                    @click="clearAnalytics" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-red-200 dark:border-red-900/40 hover:border-red-300 rounded-lg text-xs font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-950/20 hover:bg-red-100/80 dark:hover:bg-red-900/30 transition-all shadow-sm cursor-pointer dark:shadow-none"
                >
                    <Trash2 class="w-3.5 h-3.5" />
                    Reset / Clear Data
                </button>

                <!-- Date range -->
                <select v-model="days" class="text-[13px] border border-gray-200 dark:border-zinc-700 rounded-lg px-3 py-1.5 bg-white dark:bg-zinc-900 text-gray-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-blue-300">
                    <option :value="1">Last 24h</option>
                    <option :value="7">Last 7 days</option>
                    <option :value="30">Last 30 days</option>
                    <option :value="90">Last 90 days</option>
                </select>
                <!-- Interval -->
                <select v-model="interval" class="text-[13px] border border-gray-200 dark:border-zinc-700 rounded-lg px-3 py-1.5 bg-white dark:bg-zinc-900 text-gray-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-blue-300">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                </select>
                <button @click="loadAnalytics" class="p-2 hover:bg-gray-100 dark:bg-zinc-800 dark:hover:bg-zinc-800 rounded-lg text-gray-500 dark:text-zinc-400 cursor-pointer bg-transparent border-0">
                    <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': isLoading }" />
                </button>
            </div>
        </div>

        <!-- Loading View -->
        <div v-if="isLoading" class="space-y-5">
            <!-- KPI Cards Skeleton -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div v-for="i in 4" :key="i" class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl p-4 shadow-sm space-y-3 dark:shadow-none">
                    <div class="flex justify-between items-center">
                        <div class="h-3.5 wos-shimmer rounded w-20" />
                        <div class="w-4 h-4 rounded-full wos-shimmer" />
                    </div>
                    <div class="h-7 wos-shimmer rounded w-12" />
                    <div class="h-3 wos-shimmer rounded w-24" />
                </div>
            </div>
            <!-- Chart Skeleton -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl p-5 shadow-sm space-y-4 dark:shadow-none">
                <div class="flex justify-between items-center">
                    <div class="h-4 wos-shimmer rounded w-32" />
                    <div class="h-6 wos-shimmer rounded w-24" />
                </div>
                <div class="h-[240px] wos-shimmer rounded-xl w-full" />
            </div>
            <!-- Bottom Row Skeleton -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="i in 2" :key="i" class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl p-5 shadow-sm space-y-4 dark:shadow-none">
                    <div class="h-4 wos-shimmer rounded w-28" />
                    <div class="h-40 wos-shimmer rounded-xl w-full" />
                </div>
            </div>
        </div>

        <!-- Main Loaded View -->
        <template v-else-if="data">

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Active Users Card -->
                <div @click="selectTabAndScroll('active_sessions')" class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl p-4 shadow-sm cursor-pointer hover:shadow-md hover:border-blue-300 dark:hover:border-blue-700 transition-all duration-150 group dark:shadow-none">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-[12px] font-medium text-gray-500 dark:text-zinc-400 uppercase tracking-wide group-hover:text-blue-600 transition-colors">Active Users</p>
                        <Users class="w-4 h-4 text-blue-400 group-hover:scale-110 transition-transform" />
                    </div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-zinc-100">{{ data.activeSessions }}</p>
                    <p class="text-[12px] text-gray-400 dark:text-zinc-500 mt-1 flex items-center justify-between">
                        <span>in last {{ days }} day{{ days > 1 ? 's' : '' }}</span>
                        <span class="text-blue-500 font-medium text-[11px] opacity-0 group-hover:opacity-100 transition-opacity">View details →</span>
                    </p>
                </div>

                <!-- New Users Card -->
                <div @click="selectTabAndScroll('new_users')" class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl p-4 shadow-sm cursor-pointer hover:shadow-md hover:border-emerald-300 dark:hover:border-emerald-700 transition-all duration-150 group dark:shadow-none">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-[12px] font-medium text-gray-500 dark:text-zinc-400 uppercase tracking-wide group-hover:text-emerald-600 transition-colors">New Users</p>
                        <TrendingUp class="w-4 h-4 text-emerald-400 group-hover:scale-110 transition-transform" />
                    </div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-zinc-100">{{ data.newUsers }}</p>
                    <p class="text-[12px] text-gray-400 dark:text-zinc-500 mt-1 flex items-center justify-between">
                        <span>of {{ data.totalUsers }} total</span>
                        <span class="text-emerald-600 font-medium text-[11px] opacity-0 group-hover:opacity-100 transition-opacity">View details →</span>
                    </p>
                </div>

                <!-- MFA Adoption Card -->
                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl p-4 shadow-sm dark:shadow-none">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-[12px] font-medium text-gray-500 dark:text-zinc-400 uppercase tracking-wide">MFA Adoption</p>
                        <ShieldCheck class="w-4 h-4 text-purple-400" />
                    </div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-zinc-100">{{ data.mfaAdoption }}%</p>
                    <p class="text-[12px] text-gray-400 dark:text-zinc-500 mt-1">{{ data.mfaUsers }} users</p>
                </div>

                <!-- Failed Logins Card -->
                <div @click="selectTabAndScroll('failed_logins')" class="bg-white dark:bg-zinc-900 border border-red-100 dark:border-red-950/40 rounded-xl p-4 shadow-sm cursor-pointer hover:shadow-md hover:border-red-300 dark:hover:border-red-800 transition-all duration-150 group dark:shadow-none">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-[12px] font-medium text-red-400 uppercase tracking-wide group-hover:text-red-600 transition-colors">Failed Logins</p>
                        <AlertTriangle class="w-4 h-4 text-red-400 group-hover:scale-110 transition-transform" />
                    </div>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ data.failedAttempts }}</p>
                    <p class="text-[12px] text-gray-400 dark:text-zinc-500 mt-1 flex items-center justify-between">
                        <span>{{ data.successRate }}% success rate</span>
                        <span class="text-red-500 font-medium text-[11px] opacity-0 group-hover:opacity-100 transition-opacity">View details →</span>
                    </p>
                </div>
            </div>

            <!-- Login Trend Chart -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl p-5 shadow-sm dark:shadow-none">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-[14px] font-semibold text-gray-900 dark:text-zinc-100">Login Activity</h3>
                    <div class="flex items-center gap-1 bg-gray-100 dark:bg-zinc-800 rounded-lg p-0.5">
                        <button @click="chartType = 'line'" :class="['px-3 py-1 rounded-md text-[12px] font-medium transition-colors border-0 cursor-pointer', chartType === 'line' ? 'bg-white dark:bg-zinc-700 text-gray-900 dark:text-zinc-100 shadow-sm' : 'text-gray-500 dark:text-zinc-400 bg-transparent']">Line</button>
                        <button @click="chartType = 'bar'" :class="['px-3 py-1 rounded-md text-[12px] font-medium transition-colors border-0 cursor-pointer', chartType === 'bar' ? 'bg-white dark:bg-zinc-700 text-gray-900 dark:text-zinc-100 shadow-sm' : 'text-gray-500 dark:text-zinc-400 bg-transparent']">Bar</button>
                    </div>
                </div>
                <!-- Locked height container prevents ApexCharts height resize loop issues -->
                <div class="h-[240px] w-full overflow-hidden">
                    <VueApexCharts :type="chartType" :options="loginTrendOptions" :series="loginTrendSeries" height="240" />
                </div>
            </div>

            <!-- Bottom Row (OAuth & Session Risk) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Provider Distribution -->
                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl p-5 shadow-sm dark:shadow-none">
                    <h3 class="text-[14px] font-semibold text-gray-900 dark:text-zinc-200 mb-3">Provider Distribution</h3>
                    <div v-if="Object.keys(data.providerStats ?? {}).length === 0" class="text-center py-6 text-[13px] text-gray-400 dark:text-zinc-500">
                        No OAuth logins yet in this period.
                    </div>
                    <!-- Locked height container prevents ApexCharts height resize loop issues -->
                    <div v-else class="h-[200px] w-full overflow-hidden flex items-center justify-center">
                        <VueApexCharts type="donut" :options="providerOptions" :series="providerSeries" height="200" />
                    </div>
                </div>

                <!-- Session Risk Distribution -->
                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl p-5 shadow-sm dark:shadow-none">
                    <h3 class="text-[14px] font-semibold text-gray-900 dark:text-zinc-200 mb-3">Session Risk</h3>
                    <!-- Locked height container prevents ApexCharts height resize loop issues -->
                    <div class="h-[200px] w-full overflow-hidden flex items-center justify-center">
                        <VueApexCharts type="donut" :options="riskOptions" :series="riskSeries" height="200" />
                    </div>
                    <div class="mt-2 space-y-1.5 text-[12px]">
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-1.5 text-gray-700 dark:text-zinc-300"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span> Safe</span>
                            <span class="font-medium text-gray-700 dark:text-zinc-200">{{ data.riskDistribution?.safe ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-1.5 text-gray-700 dark:text-zinc-300"><span class="w-2.5 h-2.5 rounded-full bg-yellow-400 inline-block"></span> Medium</span>
                            <span class="font-medium text-gray-700 dark:text-zinc-200">{{ data.riskDistribution?.medium ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-1.5 text-gray-700 dark:text-zinc-300"><span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span> High</span>
                            <span class="text-red-600 dark:text-red-400 font-semibold">{{ data.riskDistribution?.high ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Drill-Down Real-Time Details -->
            <div id="analytics-drill-down" class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl p-5 shadow-sm mt-5 scroll-mt-6 dark:shadow-none">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-4 mb-4 gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-zinc-100">Real-Time Activity Details</h3>
                        <p class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">Drill-down view of authenticating users and attempts.</p>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-2 self-start lg:self-auto">
                        <MotionTabs
                            :model-value="selectedDetailTab"
                            :tabs="detailTabs"
                            variant="pill"
                            active-class="text-blue-600 dark:text-blue-400 font-semibold"
                            inactive-class="text-gray-500 dark:text-zinc-400 hover:text-gray-700 dark:hover:text-zinc-200"
                            @update:model-value="selectTabAndScroll"
                        />
                    </div>
                </div>

                <!-- 1. Active Sessions List -->
                <div v-if="selectedDetailTab === 'active_sessions'" class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-500 dark:text-zinc-400">
                        <caption class="sr-only">Tabel Sesi Aktif</caption>
                        <thead class="bg-gray-50/50 dark:bg-zinc-800/30 text-[10px] text-gray-400 dark:text-zinc-500 uppercase font-semibold border-b border-gray-100 dark:border-zinc-800">
                            <tr>
                                <th class="px-4 py-2.5">User</th>
                                <th class="px-4 py-2.5">IP Address</th>
                                <th class="px-4 py-2.5">Device/Browser</th>
                                <th class="px-4 py-2.5">Risk Level</th>
                                <th class="px-4 py-2.5">Last Active</th>
                                <th class="px-4 py-2.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-zinc-800 text-sm">
                            <tr v-for="s in data.activeSessionsList" :key="s.id" class="hover:bg-gray-50 dark:bg-zinc-900/50 dark:hover:bg-zinc-800/20">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900 dark:text-zinc-200">{{ s.user_name }}</div>
                                    <div class="text-xs text-gray-400 dark:text-zinc-500">{{ s.user_email }}</div>
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-600 dark:text-zinc-400">{{ s.ip_address }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-zinc-400 text-xs">{{ s.device }}</td>
                                <td class="px-4 py-3">
                                    <span :class="[
                                        'px-2 py-0.5 rounded text-[11px] font-medium border',
                                        s.risk_level === 'high' ? 'bg-red-50 dark:bg-red-950/20 border-red-100 dark:border-red-900/30 text-red-700 dark:text-red-400' :
                                        s.risk_level === 'medium' ? 'bg-amber-50 dark:bg-amber-950/20 border-amber-100 dark:border-amber-900/30 text-amber-700 dark:text-amber-400' :
                                        'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-100 dark:border-emerald-900/30 text-emerald-700 dark:text-emerald-400'
                                    ]">{{ s.risk_level.toUpperCase() }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-zinc-400 text-xs">{{ s.last_active }}</td>
                                <td class="px-4 py-3 text-right">
                                    <button @click="revokeSession(s.id)" class="inline-flex items-center gap-1 text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-950/20 px-2 py-1 rounded text-xs font-semibold transition-colors border border-transparent hover:border-red-100 dark:hover:border-red-950/30 cursor-pointer">
                                        <Trash2 class="w-3.5 h-3.5" />
                                        Revoke
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="data.activeSessionsList.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-gray-400 dark:text-zinc-500">No active sessions found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 2. New Users List -->
                <div v-else-if="selectedDetailTab === 'new_users'" class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-500 dark:text-zinc-400">
                        <caption class="sr-only">Tabel Pengguna Baru</caption>
                        <thead class="bg-gray-50/50 dark:bg-zinc-800/30 text-[10px] text-gray-400 dark:text-zinc-500 uppercase font-semibold border-b border-gray-100 dark:border-zinc-800">
                            <tr>
                                <th class="px-4 py-2.5">Name</th>
                                <th class="px-4 py-2.5">Email</th>
                                <th class="px-4 py-2.5">Joined</th>
                                <th class="px-4 py-2.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-zinc-800 text-sm">
                            <tr v-for="u in data.newUsersList" :key="u.id" class="hover:bg-gray-50 dark:bg-zinc-900/50 dark:hover:bg-zinc-800/20">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-zinc-200">{{ u.name }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-zinc-400">{{ u.email }}</td>
                                <td class="px-4 py-3 text-gray-500 dark:text-zinc-400 text-xs">{{ u.created_at }}</td>
                                <td class="px-4 py-3 text-right">
                                    <button
                                        @click="deleteUser(u.id)"
                                        :disabled="u.user_type === 'super_admin'"
                                        :title="u.user_type === 'super_admin' ? 'Akun Super Admin dilindungi' : ''"
                                        class="inline-flex items-center gap-1 text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-950/20 px-2 py-1 rounded text-xs font-semibold transition-colors border border-transparent hover:border-red-100 dark:hover:border-red-950/30 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-transparent disabled:hover:text-red-500 disabled:border-transparent cursor-pointer"
                                    >
                                        <Trash2 class="w-3.5 h-3.5" />
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="data.newUsersList.length === 0">
                                <td colspan="4" class="px-4 py-8 text-center text-gray-400 dark:text-zinc-500">No new users registered in this period.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 3. Failed Logins List -->
                <div v-else-if="selectedDetailTab === 'failed_logins'" class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-500 dark:text-zinc-400">
                        <caption class="sr-only">Tabel Gagal Login</caption>
                        <thead class="bg-gray-50/50 dark:bg-zinc-800/30 text-[10px] text-gray-400 dark:text-zinc-500 uppercase font-semibold border-b border-gray-100 dark:border-zinc-800">
                            <tr>
                                <th class="px-4 py-2.5">Target Email</th>
                                <th class="px-4 py-2.5">IP Address</th>
                                <th class="px-4 py-2.5">Provider</th>
                                <th class="px-4 py-2.5">Reason</th>
                                <th class="px-4 py-2.5">Attempted</th>
                                <th class="px-4 py-2.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-zinc-800 text-sm">
                            <tr v-for="l in data.failedLoginsList" :key="l.id" class="hover:bg-gray-50 dark:bg-zinc-900/50 dark:hover:bg-zinc-800/20">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-zinc-200">{{ l.email }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-600 dark:text-zinc-400">{{ l.ip_address }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-zinc-400 text-xs">
                                    <span class="bg-gray-100 dark:bg-zinc-950 text-gray-700 dark:text-zinc-300 px-2 py-0.5 rounded text-[11px] font-mono border border-gray-200 dark:border-zinc-700">{{ l.provider }}</span>
                                </td>
                                <td class="px-4 py-3 text-red-600 dark:text-red-400 text-xs font-medium">{{ l.reason }}</td>
                                <td class="px-4 py-3 text-gray-500 dark:text-zinc-400 text-xs">{{ l.time }}</td>
                                <td class="px-4 py-3 text-right">
                                    <button @click="deleteFailedLogin(l.id)" class="inline-flex items-center gap-1 text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-950/20 px-2 py-1 rounded text-xs font-semibold transition-colors border border-transparent hover:border-red-100 dark:hover:border-red-950/30 cursor-pointer">
                                        <Trash2 class="w-3.5 h-3.5" />
                                        Clear
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="data.failedLoginsList.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-gray-400 dark:text-zinc-500">No failed logins found in this period.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </template>

        <!-- GENERIC CONFIRMATION MODAL -->
        <AppModal :show="confirmModal.show" :title="confirmModal.title" :description="confirmModal.description" @close="confirmModal.show = false">
            <div class="py-2 text-[13.5px] text-[#4b5563] dark:text-zinc-400">
                {{ confirmModal.message }}
            </div>
            <template #footer>
                <button class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] dark:text-zinc-300 border border-[#d1d5db] dark:border-zinc-700 hover:bg-[#f3f4f6] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm cursor-pointer dark:shadow-none" @click="confirmModal.show = false">Cancel</button>
                <button
                    :disabled="confirmModal.isLoading"
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white transition-colors shadow-sm disabled:opacity-50 flex items-center gap-2 cursor-pointer border-0 dark:shadow-none"
                    :class="confirmModal.confirmBgClass"
                    @click="confirmModal.onConfirm"
                >
                    <svg v-if="confirmModal.isLoading" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    {{ confirmModal.isLoading ? 'Processing...' : confirmModal.confirmText }}
                </button>
            </template>
        </AppModal>
    </div>
</template>
