<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import ModerationTable from "@/components/Admin/ModerationTable.vue";
import ModerationModal from "@/components/Admin/ui/ModerationModal.vue";
import PagiAdminLayout from "@/layouts/PagiAdminLayout.vue";

const props = defineProps<{
	items?: Array<{
		id: number;
		title: string;
		author: string;
		authorHandle: string;
		type: "Laporan" | "Karya Baru" | "Komentar";
		reportedBy?: string;
		time: string;
		status: "active" | "warning" | "hidden" | "removed" | "pending";
		thumbnail?: string;
		userId: number;
		description: string;
		category: string;
		reportReason: string;
		reportDescription: string;
		reporterHandle: string;
	}>;
	summary?: {
		pending: number;
		warning: number;
		takedown: number;
		resolved: number;
	};
}>();

const activeTab = ref<"all" | "report" | "new" | "comment">("all");
const isLoading = ref(false);
const isResetting = ref(false);

const resetQueue = () => {
	if (
		confirm(
			"Apakah Anda yakin ingin me-reset antrean moderasi dan peringatan ke data awal demo? Semua laporan dan warnings yang dibuat manual akan dihapus.",
		)
	) {
		isResetting.value = true;
		router.post(
			"/pagi/admin/reset-moderation",
			{},
			{
				onSuccess: () => {
					isResetting.value = false;
				},
				onError: () => {
					isResetting.value = false;
				},
			},
		);
	}
};

const computedItems = computed(() => props.items ?? []);

const computedSummary = computed(
	() =>
		props.summary ?? {
			pending: 0,
			warning: 0,
			takedown: 0,
			resolved: 0,
		},
);

const activeItem = ref<any>(null);
const showModal = ref(false);

const handleReview = (id: number) => {
	const item = computedItems.value.find((i) => i.id === id);
	if (item) {
		activeItem.value = item;
		showModal.value = true;
	}
};
</script>

<template>
    <PagiAdminLayout title="Moderasi">
        <div class="mb-6 flex items-start justify-between gap-3 flex-wrap">
            <div>
                <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Moderasi Konten</h1>
                <p class="mt-0.5 text-[13px] text-slate-400 dark:text-zinc-500">Tinjau dan kelola konten yang memerlukan perhatian</p>
            </div>
            <button
                @click="resetQueue"
                :disabled="isResetting"
                class="rounded-xl border border-rose-200 dark:border-rose-900/50 bg-rose-50 dark:bg-rose-950/20 px-4 py-2 text-[12px] font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-100/50 transition-colors shrink-0 flex items-center gap-1.5 disabled:opacity-50"
            >
                <svg v-if="isResetting" class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                Reset Antrean
            </button>
        </div>

        <!-- Summary Badges -->
            <div class="mb-5 grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 px-4 py-3">
                <p class="text-[10px] font-black uppercase tracking-widest text-amber-500">Menunggu</p>
                <p class="mt-1 text-2xl font-black text-amber-700 dark:text-amber-400">{{ computedSummary.pending }}</p>
            </div>
            <div class="rounded-xl bg-orange-50 dark:bg-orange-900/20 border border-orange-100 dark:border-orange-800 px-4 py-3">
                <p class="text-[10px] font-black uppercase tracking-widest text-orange-500">Peringatan</p>
                <p class="mt-1 text-2xl font-black text-orange-700 dark:text-orange-400">{{ computedSummary.warning }}</p>
            </div>
            <div class="rounded-xl bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-800 px-4 py-3">
                <p class="text-[10px] font-black uppercase tracking-widest text-rose-500">Takedown</p>
                <p class="mt-1 text-2xl font-black text-rose-700 dark:text-rose-400">{{ computedSummary.takedown }}</p>
            </div>
            <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 px-4 py-3">
                <p class="text-[10px] font-black uppercase tracking-widest text-emerald-500">Diselesaikan</p>
                <p class="mt-1 text-2xl font-black text-emerald-700 dark:text-emerald-400">{{ computedSummary.resolved }}</p>
            </div>
        </div>

        <!-- Tabs + Table -->
        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-zinc-800">
                <h3 class="text-[13px] font-bold text-slate-800 dark:text-zinc-100">Semua Konten</h3>
                <div class="flex items-center gap-2">
                    <select class="rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-1.5 text-[12px] font-medium text-slate-600 dark:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option>Semua Status</option>
                        <option>Menunggu</option>
                        <option>Peringatan</option>
                        <option>Takedown</option>
                        <option>Aman</option>
                    </select>
                </div>
            </div>
            <ModerationTable
                :items="computedItems"
                :loading="isLoading"
                @review="handleReview"
            />
        </div>

        <ModerationModal
            :show="showModal"
            :item="activeItem"
            :available-tabs="['takedown', 'warn', 'dismiss']"
            @close="showModal = false"
        />

    </PagiAdminLayout>
</template>
