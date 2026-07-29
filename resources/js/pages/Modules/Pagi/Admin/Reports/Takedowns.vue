<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3";
import {
	AlertTriangle,
	Archive,
	CheckCircle,
	Clock,
	Eye,
	RotateCcw,
	Scale,
	Search,
	ShieldAlert,
} from "lucide-vue-next";
import { computed, ref } from "vue";
import ConfirmDialogModal from "@/components/Admin/ui/ConfirmDialogModal.vue";
import ModerationModal from "@/components/Admin/ui/ModerationModal.vue";
import PagiAdminLayout from "@/layouts/PagiAdminLayout.vue";

interface TakedownItem {
	id: number;
	workId?: number;
	title: string;
	author: string;
	authorHandle: string;
	category: string;
	status: string;
	reason: string;
	appealReason?: string | null;
	appealTime?: string | null;
	stage?: "takedown" | "banding";
	time: string;
	thumbnail?: string;
	userId?: number;
}

const props = defineProps<{
	takedownsList?: TakedownItem[];
	summary?: {
		takedown?: number;
		appeals?: number;
		pending?: number;
		warning?: number;
		resolved?: number;
	};
}>();

const searchQuery = ref("");
const activeTab = ref<"takedown" | "banding">("takedown");
const selectedWorkId = ref<number | null>(null);
const isConfirmRestoreOpen = ref(false);
const isProcessing = ref(false);
const selectedItem = ref<any>(null);
const isModalOpen = ref(false);

const allTakedownsList = computed(() => props.takedownsList ?? []);

const activeList = computed(() => {
	return allTakedownsList.value.filter((t) => {
		const stage = t.stage || (t.appealReason ? "banding" : "takedown");
		return stage === activeTab.value;
	});
});

const filteredTakedowns = computed(() => {
	let list = activeList.value;

	if (searchQuery.value.trim()) {
		const q = searchQuery.value.toLowerCase().trim();
		list = list.filter(
			(t) =>
				t.title.toLowerCase().includes(q) ||
				t.author.toLowerCase().includes(q) ||
				t.category.toLowerCase().includes(q) ||
				t.reason.toLowerCase().includes(q) ||
				(t.appealReason && t.appealReason.toLowerCase().includes(q)),
		);
	}

	return list;
});

const promptRestoreWork = (workId: number) => {
	selectedWorkId.value = workId;
	isConfirmRestoreOpen.value = true;
};

const handleConfirmRestore = () => {
	if (!selectedWorkId.value) return;
	isProcessing.value = true;

	router.post(
		`/pagi/admin/content/work/${selectedWorkId.value}/restore`,
		{},
		{
			preserveScroll: true,
			onFinish: () => {
				isProcessing.value = false;
				isConfirmRestoreOpen.value = false;
				selectedWorkId.value = null;
			},
		},
	);
};

const handleReview = (t: TakedownItem) => {
	selectedItem.value = {
		id: t.id,
		workId: t.workId ?? t.id,
		title: t.title,
		author: t.author,
		authorHandle: t.authorHandle,
		type: "Karya Baru",
		time: t.time,
		status: t.status,
		thumbnail: t.thumbnail,
		userId: t.userId || 1,
		description: t.appealReason || t.reason,
		category: t.category,
		reportReason: t.reason,
		reportDescription: t.appealReason || t.reason,
	};
	isModalOpen.value = true;
};
</script>

<template>
    <PagiAdminLayout>
        <Head title="Takedown & Banding - Moderasi PAGI" />

        <div class="space-y-6 pb-12">
            <!-- Header Banner -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-6 rounded-2xl shadow-sm">
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Takedown & Permohonan Banding</h1>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-black bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300">
                            <Archive class="w-3.5 h-3.5" /> Moderasi Akses
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
                        Daftar karya mahasiswa yang diturunkan dari galeri publik beserta permohonan banding yang masuk.
                    </p>
                </div>
            </div>

            <!-- 2 Main KPI Cards (TAKEDOWN & BANDING) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- KPI 1: TAKEDOWN -->
                <button
                    type="button"
                    @click="activeTab = 'takedown'"
                    :class="[
                        'bg-white dark:bg-zinc-900 p-5 rounded-2xl border text-left transition-all duration-200 shadow-sm flex items-center justify-between cursor-pointer hover:-translate-y-0.5',
                        activeTab === 'takedown'
                            ? 'border-rose-500 ring-2 ring-rose-500/20 bg-rose-50/20 dark:bg-rose-950/20'
                            : 'border-slate-200/80 dark:border-zinc-800 hover:border-rose-300 dark:hover:border-rose-900/60'
                    ]"
                >
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] font-black text-rose-600 dark:text-rose-400 uppercase tracking-widest">1. TAKEDOWN</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[9.5px] font-bold bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300">Disembunyikan</span>
                        </div>
                        <p class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ summary?.takedown ?? 0 }}</p>
                        <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5">Karya diturunkan dari galeri & dikunci dari edit</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/50 flex items-center justify-center text-rose-600 dark:text-rose-400 shrink-0 border border-rose-100 dark:border-rose-900/50">
                        <Archive class="w-6 h-6" />
                    </div>
                </button>

                <!-- KPI 2: BANDING -->
                <button
                    type="button"
                    @click="activeTab = 'banding'"
                    :class="[
                        'bg-white dark:bg-zinc-900 p-5 rounded-2xl border text-left transition-all duration-200 shadow-sm flex items-center justify-between cursor-pointer hover:-translate-y-0.5',
                        activeTab === 'banding'
                            ? 'border-amber-500 ring-2 ring-amber-500/20 bg-amber-50/20 dark:bg-amber-950/20'
                            : 'border-slate-200/80 dark:border-zinc-800 hover:border-amber-300 dark:hover:border-amber-900/60'
                    ]"
                >
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-widest">2. BANDING</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[9.5px] font-bold bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300">Aduan Keberatan</span>
                        </div>
                        <p class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ summary?.appeals ?? 0 }}</p>
                        <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5">Permohonan banding dari mahasiswa menunggu keputusan</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/50 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0 border border-amber-100 dark:border-amber-900/50">
                        <Scale class="w-6 h-6" />
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
                                activeTab === 'takedown' ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200/80 dark:border-rose-900/50' :
                                'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200/80 dark:border-amber-900/50'
                            ]"
                        >
                            {{ activeTab === 'takedown' ? '1. KONTEN TAKEDOWN (' + (summary?.takedown ?? 0) + ')' : '2. PERMOHONAN BANDING (' + (summary?.appeals ?? 0) + ')' }}
                        </span>
                    </div>

                    <!-- Search Input -->
                    <div class="relative w-full sm:w-72">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 dark:text-zinc-500" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari karya, pembuat, atau alasan..."
                            class="w-full pl-9 pr-4 py-1.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-rose-500"
                        />
                    </div>
                </div>

                <!-- Takedowns / Banding Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/50">
                                <th class="py-3.5 px-6 text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Karya Diturunkan</th>
                                <th class="py-3.5 px-6 text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Alasan Penurunan</th>
                                <th class="py-3.5 px-6 text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Pembuat</th>
                                <th v-if="activeTab === 'banding'" class="py-3.5 px-6 text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Alasan Banding</th>
                                <th class="py-3.5 px-6 text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Kategori</th>
                                <th class="py-3.5 px-6 text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Waktu</th>
                                <th class="py-3.5 px-6 text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60 text-xs">
                            <tr
                                v-for="t in filteredTakedowns"
                                :key="t.id"
                                class="hover:bg-slate-50/80 dark:hover:bg-zinc-800/40 transition-colors"
                            >
                                <!-- Karya -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-zinc-800 overflow-hidden shrink-0 border border-slate-200/60 dark:border-zinc-700">
                                            <img v-if="t.thumbnail" :src="t.thumbnail" alt="Cover" class="w-full h-full object-cover" />
                                            <div v-else class="w-full h-full flex items-center justify-center text-slate-400 font-bold text-xs">
                                                {{ t.title.charAt(0) }}
                                            </div>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 dark:text-white line-clamp-1">{{ t.title }}</p>
                                            <p class="text-[10.5px] text-slate-400 dark:text-zinc-500">ID Karya: #{{ t.workId ?? t.id }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Alasan Penurunan -->
                                <td class="py-4 px-6">
                                    <span class="inline-block px-2.5 py-1 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 text-[11px] font-bold">
                                        {{ t.reason }}
                                    </span>
                                </td>

                                <!-- Pembuat -->
                                <td class="py-4 px-6">
                                    <div>
                                        <p class="font-bold text-slate-800 dark:text-zinc-200">{{ t.author }}</p>
                                        <p class="text-[10.5px] text-slate-400 dark:text-zinc-500">{{ t.authorHandle }}</p>
                                    </div>
                                </td>

                                <!-- Alasan Banding (If Banding Tab) -->
                                <td v-if="activeTab === 'banding'" class="py-4 px-6">
                                    <div class="max-w-xs">
                                        <p class="text-xs font-semibold text-slate-800 dark:text-zinc-200 line-clamp-2">{{ t.appealReason || 'Mahasiswa mengajukan permohonan pemulihan karya.' }}</p>
                                        <p class="text-[10px] text-amber-600 dark:text-amber-400 mt-0.5 font-bold">{{ t.appealTime || 'baru saja' }}</p>
                                    </div>
                                </td>

                                <!-- Kategori -->
                                <td class="py-4 px-6">
                                    <span class="inline-block px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 text-[11px] font-bold">
                                        {{ t.category }}
                                    </span>
                                </td>

                                <!-- Waktu -->
                                <td class="py-4 px-6 text-slate-500 dark:text-zinc-400 whitespace-nowrap">
                                    {{ t.time }}
                                </td>

                                <!-- Aksi -->
                                <td class="py-4 px-6 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            v-if="activeTab === 'takedown'"
                                            @click="handleReview(t)"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 text-xs font-bold border border-slate-200/80 dark:border-zinc-700 transition-all cursor-pointer"
                                        >
                                            <Eye class="w-3.5 h-3.5" /> Lihat Detail
                                        </button>
                                        <button
                                            v-if="activeTab === 'takedown'"
                                            @click="promptRestoreWork(t.workId ?? t.id)"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs cursor-pointer"
                                        >
                                            <RotateCcw class="w-3.5 h-3.5" /> Pulihkan
                                        </button>
                                        <button
                                            v-if="activeTab === 'banding'"
                                            @click="handleReview(t)"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition-all shadow-amber-500/20 shadow-xs cursor-pointer"
                                        >
                                            <Scale class="w-3.5 h-3.5" /> Tinjau Banding
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="filteredTakedowns.length === 0">
                                <td :colspan="activeTab === 'banding' ? 7 : 6" class="py-12 text-center text-slate-400 dark:text-zinc-500">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <CheckCircle class="w-8 h-8 text-emerald-500/50" />
                                        <p class="font-bold text-slate-600 dark:text-zinc-400">Tidak ada item pada tab {{ activeTab.toUpperCase() }}.</p>
                                        <p class="text-xs text-slate-400 dark:text-zinc-500">Seluruh permohonan pada kategori ini telah selesai ditinjau.</p>
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

        <!-- Confirm Restore Modal -->
        <ConfirmDialogModal
            :is-open="isConfirmRestoreOpen"
            title="Pulihkan Karya ke Publik?"
            message="Apakah Anda yakin ingin memulihkan karya ini ke Galeri Publik? Karya akan langsung dapat dilihat kembali oleh mahasiswa lain."
            confirm-text="Pulihkan Karya"
            variant="info"
            :is-loading="isProcessing"
            @close="isConfirmRestoreOpen = false"
            @confirm="handleConfirmRestore"
        />
    </PagiAdminLayout>
</template>
