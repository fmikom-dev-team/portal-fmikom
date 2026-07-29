<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3";
import {
	Archive,
	CheckCircle,
	Eye,
	Search,
	ShieldCheck,
} from "lucide-vue-next";
import { computed, ref } from "vue";
import ModerationModal from "@/components/Admin/ui/ModerationModal.vue";
import PagiAdminLayout from "@/layouts/PagiAdminLayout.vue";

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
	stage?: string;
	status: string;
	time: string;
	thumbnail?: string;
	category?: string;
	sourceType?: "ai_flag" | "user_report";
}

const props = defineProps<{
	reportsList?: ReportItem[];
	summary?: {
		resolved?: number;
		takedown?: number;
	};
}>();

const searchQuery = ref("");
const selectedItem = ref<any>(null);
const isModalOpen = ref(false);

const allReportsList = computed(() => props.reportsList ?? []);

// Filter resolved / archived items only
const archivedReports = computed(() => {
	return allReportsList.value.filter((r) => {
		const s = (r.status || "").toLowerCase();
		return (
			["actioned", "reviewed", "dismissed", "resolved", "archive"].includes(
				s,
			) || r.stage === "archive"
		);
	});
});

const filteredReports = computed(() => {
	let list = archivedReports.value;

	if (searchQuery.value.trim()) {
		const q = searchQuery.value.toLowerCase().trim();
		list = list.filter(
			(r) =>
				r.workTitle.toLowerCase().includes(q) ||
				r.author.toLowerCase().includes(q) ||
				r.reporter.toLowerCase().includes(q) ||
				r.reason.toLowerCase().includes(q) ||
				r.description.toLowerCase().includes(q),
		);
	}

	return list;
});

const handleViewDetail = (r: ReportItem) => {
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
		status: r.status,
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

const getStatusBadge = (status: string) => {
	switch (status.toLowerCase()) {
		case "actioned":
		case "reviewed":
		case "resolved":
			return {
				label: "✓ Telah Ditindak",
				class:
					"bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300",
			};
		case "dismissed":
			return {
				label: "⚪ Dikesampingkan",
				class:
					"bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-400",
			};
		default:
			return {
				label: status,
				class:
					"bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-400",
			};
	}
};
</script>

<template>
    <PagiAdminLayout>
        <Head title="Arsip Moderasi - PAGI Admin" />

        <div class="space-y-6 pb-12">
            <!-- Header Banner -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-6 rounded-2xl shadow-sm">
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Arsip Laporan Selesai</h1>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-black bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">
                            <ShieldCheck class="w-3.5 h-3.5" /> Permanen Archive
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
                        Riwayat seluruh pengaduan laporan moderasi yang telah selesai diproses & dikesampingkan.
                    </p>
                </div>
                <div class="flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-zinc-400 bg-slate-50 dark:bg-zinc-800/80 px-4 py-2 rounded-xl border border-slate-200/80 dark:border-zinc-700">
                    <Archive class="w-4 h-4 text-emerald-500" /> Total Arsip: <span class="font-black text-slate-900 dark:text-white">{{ archivedReports.length }}</span>
                </div>
            </div>

            <!-- Main Content Table Card -->
            <div class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden">
                <!-- Toolbar Header -->
                <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">
                            Tampilan:
                        </span>
                        <span class="px-3 py-1 rounded-lg text-xs font-black bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-900/50 shadow-2xs">
                            Arsip Laporan Selesai ({{ archivedReports.length }})
                        </span>
                    </div>

                    <!-- Search Input -->
                    <div class="relative w-full sm:w-72">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 dark:text-zinc-500" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari karya, pelapor, atau alasan..."
                            class="w-full pl-9 pr-4 py-1.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        />
                    </div>
                </div>

                <!-- Archived Reports Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/50">
                                <th class="py-3.5 px-6 text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Karya Dilaporkan</th>
                                <th class="py-3.5 px-6 text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Pelapor</th>
                                <th class="py-3.5 px-6 text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Alasan Laporan</th>
                                <th class="py-3.5 px-6 text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Status Akhir</th>
                                <th class="py-3.5 px-6 text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider text-right">Waktu Selesai</th>
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

                                <!-- Pelapor -->
                                <td class="py-4 px-6">
                                    <div>
                                        <p class="font-bold text-slate-800 dark:text-zinc-200">{{ r.reporter }}</p>
                                        <p class="text-[10.5px] text-slate-400 dark:text-zinc-500">{{ r.reporterHandle }}</p>
                                    </div>
                                </td>

                                <!-- Alasan -->
                                <td class="py-4 px-6">
                                    <span class="inline-block px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-[11px] font-bold">
                                        {{ r.reason }}
                                    </span>
                                    <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-1 line-clamp-1 max-w-xs">{{ r.description }}</p>
                                </td>

                                <!-- Status -->
                                <td class="py-4 px-6 whitespace-nowrap">
                                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold', getStatusBadge(r.status).class]">
                                        {{ getStatusBadge(r.status).label }}
                                    </span>
                                </td>

                                <!-- Waktu -->
                                <td class="py-4 px-6 text-slate-500 dark:text-zinc-400 whitespace-nowrap text-right">
                                    {{ r.time }}
                                </td>
                            </tr>

                            <tr v-if="filteredReports.length === 0">
                                <td colspan="6" class="py-12 text-center text-slate-400 dark:text-zinc-500">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <CheckCircle class="w-8 h-8 text-emerald-500/50" />
                                        <p class="font-bold text-slate-600 dark:text-zinc-400">Belum ada laporan di Arsip Moderasi.</p>
                                        <p class="text-xs text-slate-400 dark:text-zinc-500">Laporan yang telah selesai diproses akan otomatis tersimpan di sini.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Moderation Detail Modal -->
        <ModerationModal
            :show="isModalOpen"
            :item="selectedItem"
            @close="isModalOpen = false"
        />
    </PagiAdminLayout>
</template>
