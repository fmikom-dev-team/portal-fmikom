<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import {
	CheckCircle2,
	Eye,
	Filter,
	Search,
	ShieldAlert,
} from "lucide-vue-next";
import { computed, ref } from "vue";
import ModerationModal from "@/components/Admin/ui/ModerationModal.vue";
import PagiAdminLayout from "@/layouts/PagiAdminLayout.vue";

interface ReportItem {
	id: number;
	workId: number | null;
	workTitle: string;
	author: string;
	authorHandle: string;
	reporter: string;
	reporterHandle: string;
	reason: string;
	description: string;
	status: "pending" | "reviewed" | "dismissed" | "actioned";
	time: string;
	thumbnail?: string;
}

const props = defineProps<{
	reports?: ReportItem[];
}>();

const computedReports = computed(() => props.reports ?? []);

// Search & filter states
const searchQuery = ref("");
const selectedStatus = ref("all");
const isLoading = ref(false);
const brokenImages = ref<Record<number | string, boolean>>({});

// Filtered reports
const filteredReports = computed(() => {
	return computedReports.value.filter((r) => {
		const matchSearch =
			r.workTitle.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
			r.author.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
			r.reporter.toLowerCase().includes(searchQuery.value.toLowerCase());
		const matchStatus =
			selectedStatus.value === "all" || r.status === selectedStatus.value;
		return matchSearch && matchStatus;
	});
});

// Badges styles
const statusConfig = {
	pending:
		"bg-blue-50 text-blue-600 dark:bg-blue-950/20 dark:text-blue-400 border-blue-100 dark:border-blue-900/30",
	reviewed:
		"bg-indigo-50 text-indigo-600 dark:bg-indigo-950/20 dark:text-indigo-400 border-indigo-100 dark:border-indigo-900/30",
	dismissed:
		"bg-zinc-50 text-zinc-500 dark:bg-zinc-900/20 dark:text-zinc-400 border-zinc-200 dark:border-zinc-800",
	actioned:
		"bg-emerald-50 text-emerald-600 dark:bg-emerald-950/20 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/30",
};

const statusLabel = {
	pending: "Menunggu",
	reviewed: "Ditinjau",
	dismissed: "Abaikan Laporan",
	actioned: "Ditindaklanjuti",
};

// Moderation Modal states
const activeReport = ref<any>(null);
const showModal = ref(false);

const handleReview = (report: any) => {
	activeReport.value = {
		id: report.workId,
		reportId: report.id,
		title: report.workTitle,
		author: report.author,
		authorHandle: report.authorHandle,
		thumbnail: report.thumbnail,
		reportReason: report.reason,
		reportDescription: report.description,
		reporterHandle: report.reporterHandle,
		time: report.time,
		userId: report.userId || 1,
	};
	showModal.value = true;
};
</script>

<template>
    <PagiAdminLayout title="Laporan Masuk">
        
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Laporan Masuk</h1>
            <p class="mt-0.5 text-[13px] text-slate-400 dark:text-zinc-500">Kelola dan tinjau laporan dugaan pelanggaran konten dari pengguna</p>
        </div>

        <!-- Filters / Search -->
        <div class="mb-5 grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="md:col-span-2 relative flex items-center h-10 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm px-3">
                <Search class="h-4 w-4 text-slate-400 shrink-0 mr-2.5" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari karya dilaporkan, nama pembuat, atau pelapor..."
                    class="w-full bg-transparent text-[13px] font-medium text-slate-700 dark:text-zinc-200 placeholder-slate-400 focus:outline-none"
                />
            </div>
            <div class="relative flex items-center h-10 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm px-2">
                <Filter class="h-3.5 w-3.5 text-slate-400 shrink-0 ml-1.5 mr-2" />
                <select v-model="selectedStatus" class="w-full bg-transparent text-[12px] font-semibold text-slate-600 dark:text-zinc-400 focus:outline-none">
                    <option value="all">Semua Status</option>
                    <option value="pending">Menunggu</option>
                    <option value="actioned">Ditindaklanjuti</option>
                    <option value="dismissed">Abaikan Laporan</option>
                </select>
            </div>
        </div>

        <!-- Table / Cards List -->
        <div class="space-y-4">
            <!-- Mobile list (block md:hidden) -->
            <div class="block md:hidden space-y-4">
                <div v-for="r in filteredReports" :key="r.id" class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 p-4 space-y-3 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="h-12 w-16 shrink-0 rounded-xl overflow-hidden bg-slate-50 dark:bg-zinc-800 border border-slate-100 dark:border-zinc-800">
                            <img
                                v-if="r.thumbnail && !brokenImages[r.id]"
                                :src="r.thumbnail"
                                :alt="r.workTitle"
                                @error="brokenImages[r.id] = true"
                                class="h-full w-full object-cover"
                            />
                            <div v-else class="h-full w-full flex items-center justify-center">
                                <svg class="h-4.5 w-4.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159" />
                                </svg>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-[9px] font-black bg-rose-50 text-rose-600 dark:bg-rose-950/40 px-2 py-0.5 rounded-full">
                                {{ r.reason }}
                            </span>
                            <h3 class="mt-1 text-[13px] font-bold text-slate-800 dark:text-zinc-100 truncate leading-tight">{{ r.workTitle }}</h3>
                            <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-0.5">Pembuat: {{ r.author }} · Pelapor: {{ r.reporter }}</p>
                        </div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-zinc-800/40 border border-slate-100 dark:border-zinc-800/80 text-[11.5px] text-slate-500 dark:text-zinc-400 italic">
                        "{{ r.description }}"
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-50 dark:border-zinc-800/40 pt-3">
                        <div class="flex items-center gap-1.5">
                            <span :class="['inline-flex items-center rounded-full border px-2 py-0.5 text-[9px] font-bold', statusConfig[r.status]]">
                                {{ statusLabel[r.status] }}
                            </span>
                            <span class="text-[10px] text-slate-400 dark:text-zinc-500">{{ r.time }}</span>
                        </div>
                        <button
                            v-if="r.status === 'pending'"
                            @click="handleReview(r)"
                            class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 text-[11px] font-bold transition-colors shadow-sm shadow-indigo-100"
                        >
                            Tinjau
                        </button>
                    </div>
                </div>
            </div>

            <!-- Desktop table (hidden md:block) -->
            <div class="hidden md:block rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 overflow-hidden shadow-sm">
                <table class="w-full min-w-[750px]">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-zinc-800">
                            <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Karya & Pembuat</th>
                            <th class="px-4 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Alasan</th>
                            <th class="px-4 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Pelapor</th>
                            <th class="px-4 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Status</th>
                            <th class="px-4 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Waktu</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-zinc-800/50">
                        <tr v-for="r in filteredReports" :key="r.id" class="hover:bg-slate-50 dark:hover:bg-zinc-800/20 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-14 shrink-0 rounded-xl overflow-hidden bg-slate-50 dark:bg-zinc-800 border border-slate-100 dark:border-zinc-800">
                                        <img
                                            v-if="r.thumbnail && !brokenImages[r.id]"
                                            :src="r.thumbnail"
                                            :alt="r.workTitle"
                                            @error="brokenImages[r.id] = true"
                                            class="h-full w-full object-cover"
                                        />
                                        <div v-else class="h-full w-full flex items-center justify-center">
                                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[12.5px] font-semibold text-slate-800 dark:text-zinc-100 truncate max-w-[200px]">
                                            {{ r.workTitle }}
                                        </p>
                                        <p class="text-[11px] text-slate-400 dark:text-zinc-500 truncate">
                                            oleh {{ r.author }} ({{ r.authorHandle }})
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="max-w-[240px]">
                                    <span class="inline-block text-[9.5px] font-bold text-rose-700 bg-rose-50 dark:bg-rose-950/20 dark:text-rose-400 px-2 py-0.5 rounded-full mb-1">
                                        {{ r.reason }}
                                    </span>
                                    <p class="text-[11.5px] text-slate-500 dark:text-zinc-400 truncate" :title="r.description">
                                        "{{ r.description }}"
                                    </p>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-[12px] font-medium text-slate-700 dark:text-zinc-300">{{ r.reporter }}</p>
                                <p class="text-[10.5px] text-slate-400 dark:text-zinc-500">{{ r.reporterHandle }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <span :class="['inline-flex items-center rounded-full border px-2 py-0.5 text-[9.5px] font-bold', statusConfig[r.status]]">
                                    {{ statusLabel[r.status] }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-[11px] text-slate-400 dark:text-zinc-500">
                                {{ r.time }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button
                                    v-if="r.status === 'pending'"
                                    @click="handleReview(r)"
                                    class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 text-[11px] font-bold transition-all shadow-sm"
                                >
                                    Tinjau
                                </button>
                                <span v-else class="text-[11px] font-bold text-slate-400 dark:text-zinc-600 flex items-center justify-end gap-1">
                                    Selesai
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-if="filteredReports.length === 0" class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 p-12 text-center">
                <div class="mx-auto h-12 w-12 rounded-full bg-slate-50 dark:bg-zinc-800 flex items-center justify-center mb-3">
                    <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                    </svg>
                </div>
                <h3 class="text-[14px] font-bold text-slate-800 dark:text-zinc-100">Laporan tidak ditemukan</h3>
                <p class="text-[12px] text-slate-500 dark:text-zinc-400 mt-1">Belum ada laporan yang cocok dengan filter aktif Anda.</p>
            </div>
        </div>

        <!-- Moderation Detail Modal -->
        <ModerationModal
            :show="showModal"
            :item="activeReport"
            :available-tabs="['takedown', 'warn', 'dismiss']"
            @close="showModal = false"
        />

    </PagiAdminLayout>
</template>
