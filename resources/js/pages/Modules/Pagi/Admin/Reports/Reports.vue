<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3";
import PagiAdminLayout from "@/layouts/PagiAdminLayout.vue";
import ModerationModal from "@/components/Admin/ui/ModerationModal.vue";
import ConfirmDialogModal from "@/components/Admin/ui/ConfirmDialogModal.vue";
import {
	AlertTriangle,
	Archive,
	CheckCircle,
	Clock,
	Eye,
	RotateCcw,
	Search,
	ShieldAlert,
	UserCheck,
} from "lucide-vue-next";
import { computed, ref } from "vue";

interface ReportItem {
	id: number;
	reportId?: number;
	workId?: number;
	workTitle: string;
	userId?: number;
	author: string;
	authorHandle: string;
	reporter: string;
	reporterHandle: string;
	reportsCount?: number;
	reason: string;
	description: string;
	priority?: "high" | "medium" | "low";
	stage?: "report" | "tinjauan" | "review" | "archive";
	daysLeft?: string | null;
	status: string;
	time: string;
	thumbnail?: string;
	category?: string;
	sourceType?: "ai_flag" | "user_report";
}

const props = defineProps<{
	reportsList?: ReportItem[];
	summary?: {
		report?: number;
		tinjauan?: number;
		review?: number;
		pending?: number;
		warning?: number;
		takedown?: number;
		resolved?: number;
	};
}>();

const searchQuery = ref("");
const activeTab = ref<"report" | "tinjauan" | "review">("report");
const selectedItem = ref<any>(null);
const isModalOpen = ref(false);
const isConfirmResetOpen = ref(false);
const isResetting = ref(false);

const reportsList = computed(() => props.reportsList ?? []);

const activeList = computed(() => {
	return reportsList.value.filter((r) => {
		const stage = r.stage || (r.status === "tinjauan" ? "tinjauan" : r.status === "review" ? "review" : "report");
		return stage === activeTab.value;
	});
});

const filteredReports = computed(() => {
	let list = activeList.value;

	if (searchQuery.value.trim()) {
		const q = searchQuery.value.toLowerCase().trim();
		list = list.filter(
			(r) =>
				r.workTitle.toLowerCase().includes(q) ||
				r.author.toLowerCase().includes(q) ||
				r.reporter.toLowerCase().includes(q) ||
				r.reason.toLowerCase().includes(q) ||
				r.description.toLowerCase().includes(q)
		);
	}

	return list;
});

const handleReview = (r: ReportItem) => {
	selectedItem.value = {
		id: r.workId || r.id,
		reportId: r.id,
		workId: r.workId || r.id,
		title: r.workTitle,
		author: r.author,
		authorHandle: r.authorHandle,
		type: "Laporan",
		reportedBy: r.reporter,
		reporterHandle: r.reporterHandle,
		time: r.time,
		status: r.status === "pending" ? "active" : r.status,
		thumbnail: r.thumbnail,
		userId: r.userId || 1,
		description: r.description,
		category: r.category || "Design & UI/UX",
		reportReason: r.reason,
		reportDescription: r.description,
		sourceType: r.sourceType,
	};
	isModalOpen.value = true;
};

const handleConfirmReset = () => {
	isResetting.value = true;
	router.post(
		"/pagi/admin/reports/reset",
		{},
		{
			preserveScroll: true,
			onFinish: () => {
				isResetting.value = false;
				isConfirmResetOpen.value = false;
			},
		}
	);
};

const getPriorityBadge = (priority?: string) => {
	switch (priority) {
		case "high":
			return { label: "🔴 Tinggi", class: "bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-900/60" };
		case "medium":
			return { label: "🟡 Sedang", class: "bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200/80 dark:border-amber-900/60" };
		default:
			return { label: "🔵 Rendah", class: "bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 border border-slate-200 dark:border-zinc-700" };
	}
};
</script>

<template>
    <PagiAdminLayout>
        <Head title="Laporan & Moderasi - PAGI Admin" />

        <div class="space-y-6 pb-12">
            <!-- Header Banner -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-6 rounded-2xl shadow-sm">
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Laporan & Moderasi Konten</h1>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-black bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300">
                            <ShieldAlert class="w-3.5 h-3.5" /> Working Inbox
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
                        Pusat pengelolaan laporan aduan mahasiswa & verifikasi perbaikan karya secara realtime.
                    </p>
                </div>
                <button
                    @click="isConfirmResetOpen = true"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-200 text-xs font-semibold hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors cursor-pointer"
                >
                    <RotateCcw class="w-3.5 h-3.5" />
                    Riset Data Laporan
                </button>
            </div>

            <!-- 3 Main KPI Cards (Report, Tinjauan, Review) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- KPI 1: REPORT -->
                <button
                    type="button"
                    @click="activeTab = 'report'"
                    :class="[
                        'bg-white dark:bg-zinc-900 p-5 rounded-2xl border text-left transition-all duration-200 shadow-sm flex items-center justify-between cursor-pointer hover:-translate-y-0.5',
                        activeTab === 'report'
                            ? 'border-rose-500 ring-2 ring-rose-500/20 bg-rose-50/20 dark:bg-rose-950/20'
                            : 'border-slate-200/80 dark:border-zinc-800 hover:border-rose-300 dark:hover:border-rose-900/60'
                    ]"
                >
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] font-black text-rose-600 dark:text-rose-400 uppercase tracking-widest">1. REPORT</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[9.5px] font-bold bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300">Masuk</span>
                        </div>
                        <p class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ summary?.report ?? 0 }}</p>
                        <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5">Aduan baru menunggu tindakan awal</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/50 flex items-center justify-center text-rose-600 dark:text-rose-400 shrink-0 border border-rose-100 dark:border-rose-900/50">
                        <ShieldAlert class="w-6 h-6" />
                    </div>
                </button>

                <!-- KPI 2: TINJAUAN -->
                <button
                    type="button"
                    @click="activeTab = 'tinjauan'"
                    :class="[
                        'bg-white dark:bg-zinc-900 p-5 rounded-2xl border text-left transition-all duration-200 shadow-sm flex items-center justify-between cursor-pointer hover:-translate-y-0.5',
                        activeTab === 'tinjauan'
                            ? 'border-amber-500 ring-2 ring-amber-500/20 bg-amber-50/20 dark:bg-amber-950/20'
                            : 'border-slate-200/80 dark:border-zinc-800 hover:border-amber-300 dark:hover:border-amber-900/60'
                    ]"
                >
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-widest">2. TINJAUAN</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[9.5px] font-bold bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300">Grace 7 Hari</span>
                        </div>
                        <p class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ summary?.tinjauan ?? 0 }}</p>
                        <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5">Menunggu perbaikan dari pemilik karya</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/50 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0 border border-amber-100 dark:border-amber-900/50">
                        <Clock class="w-6 h-6" />
                    </div>
                </button>

                <!-- KPI 3: REVIEW -->
                <button
                    type="button"
                    @click="activeTab = 'review'"
                    :class="[
                        'bg-white dark:bg-zinc-900 p-5 rounded-2xl border text-left transition-all duration-200 shadow-sm flex items-center justify-between cursor-pointer hover:-translate-y-0.5',
                        activeTab === 'review'
                            ? 'border-indigo-500 ring-2 ring-indigo-500/20 bg-indigo-50/20 dark:bg-indigo-950/20'
                            : 'border-slate-200/80 dark:border-zinc-800 hover:border-indigo-300 dark:hover:border-indigo-900/60'
                    ]"
                >
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">3. REVIEW</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[9.5px] font-bold bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300">Verifikasi</span>
                        </div>
                        <p class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ summary?.review ?? 0 }}</p>
                        <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5">Karya sudah diedit & butuh verifikasi</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0 border border-indigo-100 dark:border-indigo-900/50">
                        <UserCheck class="w-6 h-6" />
                    </div>
                </button>
            </div>

            <!-- Main Content Table Card -->
            <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden">
                <!-- Toolbar Header -->
                <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">
                            Tab Aktif:
                        </span>
                        <span
                            :class="[
                                'px-3 py-1 rounded-lg text-xs font-black capitalize border shadow-2xs',
                                activeTab === 'report' ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200/80 dark:border-rose-900/50' :
                                activeTab === 'tinjauan' ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200/80 dark:border-amber-900/50' :
                                'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border-indigo-200/80 dark:border-indigo-900/50'
                            ]"
                        >
                            {{ activeTab === 'report' ? '1. REPORT (' + (summary?.report ?? 0) + ')' : activeTab === 'tinjauan' ? '2. TINJAUAN PERBAIKAN USER (' + (summary?.tinjauan ?? 0) + ')' : '3. REVIEW VERIFIKASI ADMIN (' + (summary?.review ?? 0) + ')' }}
                        </span>
                    </div>

                    <!-- Search Input -->
                    <div class="relative w-full sm:w-72">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 dark:text-zinc-500" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari karya, pelapor, atau alasan..."
                            class="w-full pl-9 pr-4 py-1.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                    </div>
                </div>

                <!-- Reports Table with New Columns (Prioritas & Jumlah Pelapor) -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/50">
                                <th class="py-3.5 px-6 text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Karya Dilaporkan</th>
                                <th class="py-3.5 px-6 text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Pelapor & Jumlah</th>
                                <th class="py-3.5 px-6 text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Alasan Laporan</th>
                                <th class="py-3.5 px-6 text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Prioritas</th>
                                <th class="py-3.5 px-6 text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Status & Sisa Waktu</th>
                                <th class="py-3.5 px-6 text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 text-xs">
                            <tr
                                v-for="r in filteredReports"
                                :key="r.id"
                                class="hover:bg-slate-50/80 dark:hover:bg-zinc-800/40 transition-colors"
                            >
                                <!-- Karya -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-zinc-800 overflow-hidden shrink-0 border border-slate-200/60 dark:border-zinc-700">
                                            <img v-if="r.thumbnail" :src="r.thumbnail" alt="Cover" class="w-full h-full object-cover" />
                                            <div v-else class="w-full h-full flex items-center justify-center text-slate-400 font-bold text-xs">
                                                {{ r.workTitle.charAt(0) }}
                                            </div>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 dark:text-white line-clamp-1">{{ r.workTitle }}</p>
                                            <p class="text-[11px] text-slate-500 dark:text-zinc-400">Pembuat: <span class="font-semibold text-slate-700 dark:text-zinc-300">{{ r.authorHandle }}</span></p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Pelapor & Jumlah Reports -->
                                <td class="py-4 px-6">
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <p class="font-bold text-slate-800 dark:text-zinc-200">{{ r.reporter }}</p>
                                            <span v-if="(r.reportsCount || 1) > 1" class="px-1.5 py-0.2 rounded-md bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 text-[10px] font-black">
                                                {{ r.reportsCount }} Aduan
                                            </span>
                                        </div>
                                        <p class="text-[10.5px] text-slate-400 dark:text-zinc-500">{{ r.reporterHandle }}</p>
                                    </div>
                                </td>

                                <!-- Alasan -->
                                <td class="py-4 px-6">
                                    <span class="inline-block px-2.5 py-1 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 text-[11px] font-bold">
                                        {{ r.reason }}
                                    </span>
                                    <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-1 line-clamp-1 max-w-xs">{{ r.description }}</p>
                                </td>

                                <!-- Prioritas -->
                                <td class="py-4 px-6 whitespace-nowrap">
                                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-black', getPriorityBadge(r.priority).class]">
                                        {{ getPriorityBadge(r.priority).label }}
                                    </span>
                                </td>

                                <!-- Status & Sisa Waktu Countdown -->
                                <td class="py-4 px-6 whitespace-nowrap">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="font-bold text-slate-700 dark:text-zinc-300 capitalize">
                                            {{ r.stage === 'tinjauan' ? '⚠️ Grace Period User' : r.stage === 'review' ? '🔵 Re-Review Admin' : '🔴 Laporan Baru' }}
                                        </span>
                                        <span v-if="r.daysLeft" class="text-[10.5px] font-extrabold text-amber-600 dark:text-amber-400 flex items-center gap-1">
                                            <Clock class="w-3 h-3" /> {{ r.daysLeft }}
                                        </span>
                                        <span v-else class="text-[10.5px] text-slate-400 dark:text-zinc-500">
                                            {{ r.time }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Aksi Kontekstual -->
                                <td class="py-4 px-6 text-right whitespace-nowrap">
                                    <button
                                        @click="handleReview(r)"
                                        :class="[
                                            'inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all shadow-xs cursor-pointer',
                                            activeTab === 'report'
                                                ? 'bg-rose-600 hover:bg-rose-700 text-white shadow-rose-500/20'
                                                : activeTab === 'tinjauan'
                                                ? 'bg-amber-500 hover:bg-amber-600 text-white shadow-amber-500/20'
                                                : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-indigo-500/20'
                                        ]"
                                    >
                                        <Eye class="w-3.5 h-3.5" />
                                        {{ activeTab === 'report' ? 'Tinjau Laporan' : activeTab === 'tinjauan' ? 'Cek Progres' : 'Verifikasi Hasil' }}
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="filteredReports.length === 0">
                                <td colspan="6" class="py-12 text-center text-slate-400 dark:text-zinc-500">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <CheckCircle class="w-8 h-8 text-emerald-500/50" />
                                        <p class="font-bold text-slate-600 dark:text-zinc-400">Tidak ada laporan pada tab {{ activeTab.toUpperCase() }}.</p>
                                        <p class="text-xs text-slate-400 dark:text-zinc-500">Semua laporan pada kategori ini telah selesai diproses.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Moderation Modal -->
        <ModerationModal
            :show="isModalOpen"
            :item="selectedItem"
            @close="isModalOpen = false"
            @success="router.reload({ preserveScroll: true })"
        />

        <!-- Reset Confirmation Dialog -->
        <ConfirmDialogModal
            :is-open="isConfirmResetOpen"
            title="Riset Data Laporan?"
            message="Apakah Anda yakin ingin meriset ulang data laporan komunitas? Tindakan ini akan mengembalikan data laporan ke kondisi demo awal."
            confirm-text="Riset Data Laporan"
            variant="warning"
            :is-loading="isResetting"
            @close="isConfirmResetOpen = false"
            @confirm="handleConfirmReset"
        />
    </PagiAdminLayout>
</template>
