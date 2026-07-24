<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
import {
	computed,
	defineAsyncComponent,
	onMounted,
	onUnmounted,
	ref,
} from "vue";
import ActivityList from "@/components/Admin/ActivityList.vue";

const ChartCard = defineAsyncComponent(
	() => import("@/components/Admin/ChartCard.vue"),
);
const ModerationSummary = defineAsyncComponent(
	() => import("@/components/Admin/ModerationSummary.vue"),
);

import ModerationTable from "@/components/Admin/ModerationTable.vue";
import StatsCard from "@/components/Admin/StatsCard.vue";
import ModerationModal from "@/components/Admin/ui/ModerationModal.vue";
import MotionTabs from "@/components/ui/tabs/MotionTabs.vue";
import PagiAdminLayout from "@/layouts/PagiAdminLayout.vue";

// === Types ===
interface StatsChange {
	value: string;
	trend: "up" | "down" | "neutral";
}

interface StatsData {
	mahasiswaAktif: number;
	karyaPublish: number;
	laporanMasuk: number;
	warningAktif: number;
	karyaDitinjau: number;
	changes?: {
		mahasiswaAktif: StatsChange;
		karyaPublish: StatsChange;
		laporanMasuk: StatsChange;
		warningAktif: StatsChange;
		karyaDitinjau: StatsChange;
	};
}

interface ChartDataShape {
	categories: string[];
	karya: number[];
	laporan: number[];
	warnings: number[];
}

// === Props from Inertia controller ===
const props = defineProps<{
	stats?: StatsData;
	moderationSummary?: {
		total: number;
		pending: number;
		warning: number;
		takedown: number;
		rejected: number;
		safe: number;
	};
	recentActivities?: Array<{
		id: number | string;
		type: "report" | "warning" | "takedown" | "comment" | "publish";
		title: string;
		description: string;
		actor: string;
		time: string;
	}>;
	moderationItems?: Array<{
		id: number;
		title: string;
		author: string;
		authorHandle: string;
		type: "Laporan" | "Karya Baru" | "Komentar";
		reportedBy?: string;
		time: string;
		status: "active" | "warning" | "hidden" | "removed" | "pending";
		thumbnail?: string;
	}>;
	popularWorks?: Array<{
		id: number;
		rank: number;
		title: string;
		author: string;
		views: number;
		thumbnail?: string;
	}>;
	chartData?: ChartDataShape;
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user || { name: "Admin" });

// === Loading states ===
// isLoading = true saat halaman pertama kali dimuat (skeleton tampil)
const isLoading = ref(true);
const isLoadingChart = ref(false);

// === Reactive data (starts with props, updated by polling) ===
const liveStats = ref<StatsData | null>(props.stats ?? null);
const liveModSummary = ref(props.moderationSummary ?? null);
const liveChartData = ref<ChartDataShape | null>(props.chartData ?? null);

// Setelah komponen mount, set isLoading ke false (data dari SSR sudah ada)
onMounted(() => {
	// Jika data sudah ada dari Inertia SSR, langsung tampilkan
	if (props.stats) {
		isLoading.value = false;
	} else {
		// Jika tidak ada data SSR, fetch manual
		fetchStats().then(() => {
			isLoading.value = false;
		});
	}

	// Polling setiap 30 detik
	pollingInterval = setInterval(() => {
		fetchStats();
	}, 30_000);
});

let pollingInterval: ReturnType<typeof setInterval> | null = null;

onUnmounted(() => {
	if (pollingInterval) clearInterval(pollingInterval);
});

// === Fetch stats dari JSON API ===
const lastUpdated = ref<string | null>(null);

async function fetchStats() {
	try {
		const res = await fetch("/pagi/admin/api/stats", {
			headers: {
				Accept: "application/json",
				"X-Requested-With": "XMLHttpRequest",
			},
			credentials: "same-origin",
		});
		if (!res.ok) return;
		const data = await res.json();
		if (data.stats) liveStats.value = data.stats;
		if (data.moderationSummary) liveModSummary.value = data.moderationSummary;
		lastUpdated.value = new Date().toLocaleTimeString("id-ID", {
			hour: "2-digit",
			minute: "2-digit",
		});
	} catch {
		// Silently fail — tetap tampilkan data lama
	}
}

// === Fetch chart data saat range berubah ===
async function onChartRangeChange(range: "7d" | "30d" | "90d") {
	isLoadingChart.value = true;
	try {
		const res = await fetch(`/pagi/admin/api/chart?range=${range}`, {
			headers: {
				Accept: "application/json",
				"X-Requested-With": "XMLHttpRequest",
			},
			credentials: "same-origin",
		});
		if (!res.ok) return;
		const data = await res.json();
		if (data.chartData) liveChartData.value = data.chartData;
	} catch {
		// Silently fail
	} finally {
		isLoadingChart.value = false;
	}
}

// === Computed values dari live data ===
const computedStats = computed(() => liveStats.value);
const modSummary = computed(
	() =>
		liveModSummary.value ?? {
			total: 0,
			pending: 0,
			warning: 0,
			takedown: 0,
			rejected: 0,
			safe: 0,
		},
);

// Moderation items & popular works hanya dari Inertia SSR (tidak di-poll — cukup untuk dashboard)
const allModerationItems = computed(() => props.moderationItems ?? []);
const computedPopularWorks = computed(() => props.popularWorks ?? []);
const computedRecentActivities = computed(() => props.recentActivities ?? []);

// === Stats Cards Definition (dari live data) ===
const statsCards = computed(() => {
	if (!computedStats.value) return [];
	const ch = computedStats.value.changes;
	return [
		{
			title: "Mahasiswa Aktif",
			value: computedStats.value.mahasiswaAktif,
			change: ch?.mahasiswaAktif.value ?? "–",
			trend: ch?.mahasiswaAktif.trend ?? ("neutral" as const),
			iconColor: "bg-indigo-500",
			icon: `<svg class="h-4.5 w-4.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>`,
		},
		{
			title: "Karya Publik",
			value: computedStats.value.karyaPublish,
			change: ch?.karyaPublish.value ?? "–",
			trend: ch?.karyaPublish.trend ?? ("neutral" as const),
			iconColor: "bg-emerald-500",
			icon: `<svg class="h-4.5 w-4.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 21l6.75-6.75 1.5 1.5L3 21zM16.5 3.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" /></svg>`,
		},
		{
			title: "Konten Dilaporkan",
			value: computedStats.value.laporanMasuk,
			change: ch?.laporanMasuk.value ?? "–",
			trend: ch?.laporanMasuk.trend ?? ("neutral" as const),
			iconColor: "bg-amber-500",
			icon: `<svg class="h-4.5 w-4.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z" /></svg>`,
		},
		{
			title: "Peringatan Aktif",
			value: computedStats.value.warningAktif,
			change: ch?.warningAktif.value ?? "–",
			trend: ch?.warningAktif.trend ?? ("neutral" as const),
			iconColor: "bg-rose-500",
			icon: `<svg class="h-4.5 w-4.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>`,
		},
		{
			title: "Karya di Tinjau",
			value: computedStats.value.karyaDitinjau,
			change: ch?.karyaDitinjau.value ?? "–",
			trend: "neutral" as const,
			iconColor: "bg-slate-500",
			icon: `<svg class="h-4.5 w-4.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>`,
		},
	];
});

// === Moderation tabs ===
const activeTab = ref<"all" | "report" | "new" | "comment">("all");

const moderationTabs = computed(() => [
	{
		id: "all",
		label: "Semua",
		badge: allModerationItems.value.length,
		badgeClass:
			activeTab.value === "all"
				? "bg-white/20 text-white"
				: "bg-slate-100 dark:bg-zinc-700 text-slate-500 dark:text-zinc-400",
	},
	{
		id: "report",
		label: "Laporan",
		badge: allModerationItems.value.filter((i) => i.type === "Laporan").length,
		badgeClass:
			activeTab.value === "report"
				? "bg-white/20 text-white"
				: "bg-slate-100 dark:bg-zinc-700 text-slate-500 dark:text-zinc-400",
	},
	{
		id: "new",
		label: "Karya Baru",
		badge: allModerationItems.value.filter((i) => i.type === "Karya Baru")
			.length,
		badgeClass:
			activeTab.value === "new"
				? "bg-white/20 text-white"
				: "bg-slate-100 dark:bg-zinc-700 text-slate-500 dark:text-zinc-400",
	},
	{
		id: "comment",
		label: "Komentar",
		badge: allModerationItems.value.filter((i) => i.type === "Komentar").length,
		badgeClass:
			activeTab.value === "comment"
				? "bg-white/20 text-white"
				: "bg-slate-100 dark:bg-zinc-700 text-slate-500 dark:text-zinc-400",
	},
]);

const filteredModerationItems = computed(() => {
	if (activeTab.value === "all") return allModerationItems.value;
	if (activeTab.value === "report")
		return allModerationItems.value.filter((i) => i.type === "Laporan");
	if (activeTab.value === "new")
		return allModerationItems.value.filter((i) => i.type === "Karya Baru");
	if (activeTab.value === "comment")
		return allModerationItems.value.filter((i) => i.type === "Komentar");
	return allModerationItems.value;
});

// === Moderation modal ===
const activeItem = ref<any>(null);
const showModal = ref(false);
const brokenImages = ref<Record<number | string, boolean>>({});

const handleReview = (id: number) => {
	const item = allModerationItems.value.find((i) => i.id === id);
	if (item) {
		activeItem.value = item;
		showModal.value = true;
	}
};
</script>

<template>
    <PagiAdminLayout title="Dashboard">

        <!-- Page Header -->
        <div class="mb-6 flex items-start justify-between gap-3 flex-wrap">
            <div>
                <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Dashboard</h1>
                <p class="mt-0.5 text-[13px] text-slate-400 dark:text-zinc-500 flex items-center gap-2 flex-wrap">
                    <span>Selamat datang kembali, <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ user.name?.split(' ')[0] }}</span>! 👋</span>
                    <span v-if="lastUpdated" class="text-[11px] text-slate-300 dark:text-zinc-600">
                        · Diperbarui {{ lastUpdated }}
                    </span>
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <!-- Sistem Aktif indicator -->
                <div class="hidden sm:flex items-center gap-1.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2">
                    <div class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse" />
                    <span class="text-[11px] font-bold text-slate-500 dark:text-zinc-400">Sistem Aktif</span>
                </div>
                <!-- Refresh manual button -->
                <button
                    @click="fetchStats"
                    class="hidden sm:flex items-center gap-1.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-[11px] font-bold text-slate-500 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors"
                    title="Refresh data"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh
                </button>
                <button class="rounded-xl bg-indigo-600 px-4 py-2 text-[12px] font-bold text-white hover:bg-indigo-700 transition-colors shadow-sm shadow-indigo-200 dark:shadow-none">
                    + Buat Laporan
                </button>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            <!-- Skeleton saat loading dan stats belum ada -->
            <template v-if="isLoading || !computedStats">
                <div
                    v-for="i in 5"
                    :key="i"
                    class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 p-4 sm:p-5"
                >
                    <div class="flex items-start justify-between">
                        <div class="space-y-2.5 flex-1 mr-3">
                            <div class="h-2.5 w-24 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
                            <div class="h-8 w-16 rounded-lg bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
                            <div class="h-5 w-20 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
                        </div>
                        <div class="h-10 w-10 rounded-xl bg-slate-100 dark:bg-zinc-800 animate-shimmer shrink-0" />
                    </div>
                </div>
            </template>
            <template v-else>
                <StatsCard
                    v-for="card in statsCards"
                    :key="card.title"
                    :title="card.title"
                    :value="card.value"
                    :change="card.change"
                    :trend="card.trend"
                    :icon="card.icon"
                    :icon-color="card.iconColor"
                    :loading="false"
                />
            </template>
        </div>

        <!-- Main Grid: Chart + Moderation Summary -->
        <div class="mb-5 grid grid-cols-1 gap-5 lg:grid-cols-3">
            <!-- Activity Chart (2/3 width) -->
            <div class="lg:col-span-2">
                <ChartCard
                    title="Statistik Aktivitas"
                    :loading="isLoading"
                    :loading-chart="isLoadingChart"
                    :chart-data="liveChartData ?? undefined"
                    @range-change="onChartRangeChange"
                />
            </div>

            <!-- Moderation Summary (1/3 width) -->
            <div>
                <ModerationSummary
                    :total="modSummary.total"
                    :pending="modSummary.pending"
                    :warning="modSummary.warning"
                    :takedown="modSummary.takedown"
                    :rejected="modSummary.rejected"
                    :safe="modSummary.safe"
                    :loading="isLoading"
                />
            </div>
        </div>

        <!-- Bottom Grid: Moderation Table + Right Column -->
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">

            <!-- Moderation Table (2/3 width on xl) -->
            <div class="xl:col-span-2 space-y-4">
                <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 overflow-hidden">

                    <!-- Header -->
                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-zinc-800">
                        <h3 class="text-[13px] font-bold text-slate-800 dark:text-zinc-100">Konten Menunggu Tinjauan</h3>
                        <button class="text-[12px] font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 transition-colors">
                            Lihat Semua
                        </button>
                    </div>

                    <!-- Tabs with motion transition -->
                    <MotionTabs
                        v-model="activeTab"
                        :tabs="moderationTabs"
                        variant="pill"
                        container-class="flex items-center gap-1 px-5 py-3 border-b border-slate-100 dark:border-zinc-800 overflow-x-auto bg-transparent rounded-none"
                        pill-class="bg-indigo-600 rounded-lg shadow-sm"
                        active-class="text-white font-semibold flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-[12px] whitespace-nowrap shrink-0"
                        inactive-class="text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-200 flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-[12px] whitespace-nowrap shrink-0"
                    />

                    <!-- Moderation Table -->
                    <ModerationTable
                        :items="filteredModerationItems"
                        :loading="isLoading"
                        @review="handleReview"
                    />
                </div>
            </div>

            <div class="space-y-5">

                <ActivityList
                    :activities="computedRecentActivities"
                    :loading="isLoading"
                />

                <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-zinc-800">
                        <h3 class="text-[13px] font-bold text-slate-800 dark:text-zinc-100">Karya Populer Minggu Ini</h3>
                        <button class="text-[12px] font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 transition-colors">
                            Lihat Semua
                        </button>
                    </div>

                    <!-- Skeleton -->
                    <div v-if="isLoading" class="divide-y divide-slate-50 dark:divide-zinc-800/50">
                        <div v-for="i in 5" :key="i" class="flex items-center gap-3.5 px-5 py-3.5">
                            <div class="h-4 w-4 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer shrink-0" />
                            <div class="h-9 w-9 rounded-xl bg-slate-100 dark:bg-zinc-800 animate-shimmer shrink-0" />
                            <div class="flex-1 space-y-1.5 min-w-0">
                                <div class="h-3 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer"
                                     :style="{ width: (50 + i * 8) + '%' }" />
                                <div class="h-2.5 w-1/3 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
                            </div>
                            <div class="h-4 w-10 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer shrink-0" />
                        </div>
                    </div>

                    <!-- Data -->
                    <div v-else-if="computedPopularWorks.length > 0" class="divide-y divide-slate-50 dark:divide-zinc-800/50">
                        <div
                            v-for="work in computedPopularWorks"
                            :key="work.id"
                            class="flex items-center gap-3.5 px-5 py-3.5 hover:bg-slate-50 dark:hover:bg-zinc-800/40 transition-colors group cursor-pointer"
                        >
                            <!-- Rank -->
                            <span
                                :class="[
                                    'shrink-0 text-[12px] font-black w-5 text-center',
                                    work.rank === 1 ? 'text-amber-500' :
                                    work.rank === 2 ? 'text-slate-400' :
                                    work.rank === 3 ? 'text-orange-400' :
                                    'text-slate-300 dark:text-zinc-600'
                                ]"
                            >
                                {{ work.rank }}
                            </span>

                            <!-- Thumbnail -->
                            <div class="h-9 w-9 shrink-0 rounded-xl bg-slate-100 dark:bg-zinc-800 overflow-hidden border border-slate-100 dark:border-zinc-700">
                                <img v-if="work.thumbnail" :src="work.thumbnail" :alt="work.title" class="h-full w-full object-cover" />
                                <div v-else class="h-full w-full flex items-center justify-center">
                                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <p class="text-[12.5px] font-semibold text-slate-800 dark:text-zinc-100 truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                    {{ work.title }}
                                </p>
                                <p class="text-[11px] text-slate-400 dark:text-zinc-500 truncate">{{ work.author }}</p>
                            </div>

                            <!-- Views -->
                            <div class="shrink-0 flex items-center gap-1 text-[11px] text-slate-400 dark:text-zinc-500">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="font-semibold">{{ work.views >= 1000 ? (work.views / 1000).toFixed(1) + 'K' : work.views }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div v-else class="flex flex-col items-center justify-center py-10 text-center">
                        <div class="h-12 w-12 rounded-2xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center mb-3">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909" />
                            </svg>
                        </div>
                        <p class="text-[13px] font-semibold text-slate-500 dark:text-zinc-400">Belum ada karya populer</p>
                        <p class="text-[11px] text-slate-400 dark:text-zinc-600 mt-1">Data akan muncul setelah ada karya yang dipublikasikan</p>
                    </div>
                </div>

            </div>
        </div>

        <!-- Moderation Detail Modal -->
        <ModerationModal
            :show="showModal"
            :item="activeItem"
            @close="showModal = false"
            @success="fetchStats"
        />

    </PagiAdminLayout>
</template>
