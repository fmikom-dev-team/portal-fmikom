<script setup lang="ts">
import { router, useForm } from "@inertiajs/vue3";
import {
	AlertCircle,
	CheckCircle2,
	Eye,
	Filter,
	Search,
	ShieldAlert,
} from "lucide-vue-next";
import { computed, ref } from "vue";
import PagiAdminLayout from "@/layouts/PagiAdminLayout.vue";

interface WorkItem {
	id: number;
	title: string;
	author: string;
	authorHandle: string;
	views: number;
	category: string;
	status: "active" | "warning" | "hidden" | "removed" | "review";
	isPublished: boolean;
	time: string;
	thumbnail?: string;
}

const props = defineProps<{
	works?: WorkItem[];
}>();

const computedWorks = computed(() => props.works ?? []);

// Search and Filter states
const searchQuery = ref("");
const selectedCategory = ref("all");
const selectedStatus = ref("all");
const isLoading = ref(false);
const brokenImages = ref<Record<number | string, boolean>>({});

// Categories from items
const categories = computed(() => {
	const list = new Set(computedWorks.value.map((w) => w.category));
	return ["all", ...Array.from(list)];
});

// Filtered works
const filteredWorks = computed(() => {
	return computedWorks.value.filter((w) => {
		const matchSearch =
			w.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
			w.author.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
			w.authorHandle.toLowerCase().includes(searchQuery.value.toLowerCase());
		const matchCategory =
			selectedCategory.value === "all" || w.category === selectedCategory.value;
		const matchStatus =
			selectedStatus.value === "all" || w.status === selectedStatus.value;
		return matchSearch && matchCategory && matchStatus;
	});
});

// Status Badge Config
const statusConfig = {
	active:
		"bg-emerald-50 text-emerald-600 dark:bg-emerald-950/20 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/30",
	warning:
		"bg-amber-50 text-amber-600 dark:bg-amber-950/20 dark:text-amber-400 border-amber-100 dark:border-amber-900/30",
	hidden:
		"bg-zinc-50 text-zinc-500 dark:bg-zinc-900/20 dark:text-zinc-400 border-zinc-200 dark:border-zinc-800",
	removed:
		"bg-rose-50 text-rose-600 dark:bg-rose-950/20 dark:text-rose-400 border-rose-100 dark:border-rose-900/30",
	review:
		"bg-indigo-50 text-indigo-600 dark:bg-indigo-950/20 dark:text-indigo-400 border-indigo-100 dark:border-indigo-900/30",
};

const statusLabel = {
	active: "Aktif",
	warning: "Peringatan",
	hidden: "Draft/Sembunyi",
	removed: "Dihapus",
	review: "Dalam Tinjauan",
};

// Quick Moderation Modal states
const activeWork = ref<WorkItem | null>(null);
const showModModal = ref(false);
const moderationReason = ref("");
const moderationAction = ref<"hide" | "remove" | "active">("hide");

const openModeration = (work: WorkItem) => {
	activeWork.value = work;
	moderationAction.value = work.status === "active" ? "hide" : "active";
	moderationReason.value = "";
	showModModal.value = true;
};

const submitQuickModeration = () => {
	if (!activeWork.value) return;
	isLoading.value = true;

	router.post(
		`/pagi/admin/content/work/${activeWork.value.id}/moderate`,
		{
			action: moderationAction.value,
			reason:
				moderationReason.value ||
				(moderationAction.value === "active"
					? "Dipulihkan kembali oleh admin."
					: "Diturunkan dalam tinjauan cepat."),
		},
		{
			onSuccess: () => {
				showModModal.value = false;
				activeWork.value = null;
				isLoading.value = false;
			},
			onError: () => {
				isLoading.value = false;
			},
		},
	);
};
</script>

<template>
    <PagiAdminLayout title="Manajemen Karya">
        
        <!-- Header -->
        <div class="mb-6 flex items-start justify-between gap-3 flex-wrap">
            <div>
                <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Manajemen Karya</h1>
                <p class="mt-0.5 text-[13px] text-slate-400 dark:text-zinc-500">Kelola semua karya dan portofolio yang telah diunggah oleh mahasiswa</p>
            </div>
        </div>

        <!-- Filters / Search Panel -->
        <div class="mb-5 grid grid-cols-1 md:grid-cols-4 gap-3">
            <!-- Search input -->
            <div class="md:col-span-2 relative flex items-center h-10 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm px-3 focus-within:ring-2 focus-within:ring-indigo-500/20 transition-all">
                <Search class="h-4 w-4 text-slate-400 shrink-0 mr-2.5" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari judul karya atau nama pembuat..."
                    class="w-full bg-transparent text-[13px] font-medium text-slate-700 dark:text-zinc-200 placeholder-slate-400 focus:outline-none"
                />
            </div>
            <!-- Category filter -->
            <div class="relative flex items-center h-10 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm px-2">
                <Filter class="h-3.5 w-3.5 text-slate-400 shrink-0 ml-1.5 mr-2" />
                <select v-model="selectedCategory" class="w-full bg-transparent text-[12px] font-semibold text-slate-600 dark:text-zinc-400 focus:outline-none">
                    <option value="all">Semua Kategori</option>
                    <option v-for="cat in categories.filter(c => c !== 'all')" :key="cat" :value="cat">
                        {{ cat }}
                    </option>
                </select>
            </div>
            <!-- Status filter -->
            <div class="relative flex items-center h-10 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm px-2">
                <ShieldAlert class="h-3.5 w-3.5 text-slate-400 shrink-0 ml-1.5 mr-2" />
                <select v-model="selectedStatus" class="w-full bg-transparent text-[12px] font-semibold text-slate-600 dark:text-zinc-400 focus:outline-none">
                    <option value="all">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="review">Dalam Tinjauan</option>
                    <option value="warning">Peringatan</option>
                    <option value="hidden">Sembunyi/Draft</option>
                </select>
            </div>
        </div>

        <!-- Skeletons (modern shimmer) -->
        <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="i in 6" :key="i" class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 p-4 space-y-3">
                <div class="h-40 rounded-xl bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
                <div class="h-4 w-3/4 rounded bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
                <div class="h-3 w-1/2 rounded bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
                <div class="flex items-center justify-between pt-2">
                    <div class="h-5 w-16 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
                    <div class="h-7 w-16 rounded-lg bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
                </div>
            </div>
        </div>

        <template v-else>
            <!-- ── Mobile Card View (block md:hidden) ── -->
            <div class="block md:hidden space-y-4">
                <div 
                    v-for="work in filteredWorks" 
                    :key="work.id"
                    class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 p-4 flex flex-col gap-3.5 shadow-sm"
                >
                    <div class="flex items-start gap-3">
                        <!-- Thumbnail -->
                        <div class="h-14 w-20 shrink-0 rounded-xl overflow-hidden bg-slate-50 dark:bg-zinc-800 border border-slate-100 dark:border-zinc-800">
                            <img 
                                v-if="work.thumbnail && !brokenImages[work.id]" 
                                :src="work.thumbnail" 
                                :alt="work.title"
                                @error="brokenImages[work.id] = true"
                                class="h-full w-full object-cover" 
                            />
                            <div v-else class="h-full w-full flex items-center justify-center">
                                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159" />
                                </svg>
                            </div>
                        </div>
                        <!-- Info -->
                        <div class="min-w-0 flex-1">
                            <span class="text-[9px] font-bold uppercase tracking-wider text-indigo-500 bg-indigo-50 dark:bg-indigo-950/40 px-2 py-0.5 rounded-full">
                                {{ work.category }}
                            </span>
                            <h3 class="mt-1 text-[13px] font-bold text-slate-800 dark:text-zinc-100 leading-tight truncate">
                                {{ work.title }}
                            </h3>
                            <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-0.5">
                                {{ work.author }} ({{ work.authorHandle }})
                            </p>
                        </div>
                    </div>

                    <!-- Meta info & Actions -->
                    <div class="flex items-center justify-between border-t border-slate-50 dark:border-zinc-800/50 pt-3 flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <span :class="['inline-flex items-center rounded-full border px-2 py-0.5 text-[9px] font-bold', statusConfig[work.status]]">
                                {{ statusLabel[work.status] }}
                            </span>
                            <span class="text-[10px] text-slate-400 dark:text-zinc-500 flex items-center gap-1">
                                <Eye class="h-3 w-3" /> {{ work.views }}
                            </span>
                        </div>
                        
                        <div class="flex gap-1.5 ml-auto">
                            <!-- View button redirects to public portfolio profile -->
                            <a 
                                :href="`/pagi/works/v/${work.authorHandle.replace('@', '')}`"
                                target="_blank"
                                class="rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-1.5 text-slate-500 dark:text-zinc-400 hover:text-slate-700 transition-colors"
                                title="Lihat Publik"
                            >
                                <Eye class="h-3.5 w-3.5" />
                            </a>
                            <button
                                @click="openModeration(work)"
                                class="rounded-lg border px-3 py-1 text-[11px] font-bold transition-all"
                                :class="work.status === 'active' 
                                    ? 'border-rose-100 bg-rose-50 text-rose-600 hover:bg-rose-100' 
                                    : 'border-emerald-100 bg-emerald-50 text-emerald-600 hover:bg-emerald-100'"
                            >
                                {{ work.status === 'active' ? 'Takedown' : 'Pulihkan' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Desktop Table View (hidden md:block) ── -->
            <div class="hidden md:block rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 overflow-hidden shadow-sm">
                <table class="w-full min-w-[700px]">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-zinc-800">
                            <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Karya</th>
                            <th class="px-4 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Kategori</th>
                            <th class="px-4 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Status</th>
                            <th class="px-4 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Views</th>
                            <th class="px-4 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Dibuat</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-zinc-800/50">
                        <tr v-for="work in filteredWorks" :key="work.id" class="hover:bg-slate-50 dark:hover:bg-zinc-800/20 transition-colors">
                            <!-- Title + Author -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-14 shrink-0 rounded-xl overflow-hidden bg-slate-50 dark:bg-zinc-800 border border-slate-100 dark:border-zinc-800">
                                        <img 
                                            v-if="work.thumbnail && !brokenImages[work.id]" 
                                            :src="work.thumbnail" 
                                            :alt="work.title"
                                            @error="brokenImages[work.id] = true"
                                            class="h-full w-full object-cover" 
                                        />
                                        <div v-else class="h-full w-full flex items-center justify-center">
                                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[12.5px] font-semibold text-slate-800 dark:text-zinc-100 truncate max-w-[240px]">
                                            {{ work.title }}
                                        </p>
                                        <p class="text-[11px] text-slate-400 dark:text-zinc-500 truncate">
                                            oleh {{ work.author }} ({{ work.authorHandle }})
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Category -->
                            <td class="px-4 py-4">
                                <span class="text-[11.5px] font-medium text-slate-600 dark:text-zinc-400 bg-slate-50 dark:bg-zinc-800/50 px-2 py-0.5 rounded-lg border border-slate-100 dark:border-zinc-800">
                                    {{ work.category }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-4">
                                <span :class="['inline-flex items-center rounded-full border px-2 py-0.5 text-[9.5px] font-bold', statusConfig[work.status]]">
                                    {{ statusLabel[work.status] }}
                                </span>
                            </td>

                            <!-- Views -->
                            <td class="px-4 py-4">
                                <span class="text-[12px] font-medium text-slate-600 dark:text-zinc-400">
                                    {{ work.views.toLocaleString('id-ID') }}
                                </span>
                            </td>

                            <!-- Date Published -->
                            <td class="px-4 py-4">
                                <span class="text-[11px] text-slate-400 dark:text-zinc-500">
                                    {{ work.time }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a 
                                        :href="`/pagi/works/v/${work.authorHandle.replace('@', '')}`"
                                        target="_blank"
                                        class="rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-2 py-1.5 text-[11px] font-bold text-slate-600 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors flex items-center gap-1 shrink-0"
                                    >
                                        <Eye class="h-3 w-3" /> Lihat Publik
                                    </a>
                                    <button
                                        @click="openModeration(work)"
                                        class="rounded-lg border px-3 py-1.5 text-[11px] font-bold transition-all shrink-0"
                                        :class="work.status === 'active' 
                                            ? 'border-rose-100 bg-rose-50 text-rose-600 hover:bg-rose-100 dark:border-rose-900/30 dark:bg-rose-950/20 dark:text-rose-400' 
                                            : 'border-emerald-100 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:border-emerald-900/30 dark:bg-emerald-950/20 dark:text-emerald-400'"
                                    >
                                        {{ work.status === "active" ? "Takedown" : "Pulihkan" }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-if="filteredWorks.length === 0" class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 p-12 text-center">
                <div class="mx-auto h-12 w-12 rounded-full bg-slate-50 dark:bg-zinc-800 flex items-center justify-center mb-3">
                    <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h3 class="text-[14px] font-bold text-slate-800 dark:text-zinc-100">Karya tidak ditemukan</h3>
                <p class="text-[12px] text-slate-500 dark:text-zinc-400 mt-1">Gunakan kata kunci pencarian atau filter status yang berbeda.</p>
            </div>
        </template>

        <!-- Quick Moderation Modal -->
        <Teleport to="body">
            <div v-if="showModModal && activeWork" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-all">
                <div class="w-full max-w-md bg-white dark:bg-zinc-900 rounded-2xl border border-slate-100 dark:border-zinc-800 shadow-xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 dark:border-zinc-800 flex justify-between items-center">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-zinc-100">
                            {{ moderationAction === 'hide' ? 'Konfirmasi Takedown Konten' : 'Pulihkan Konten' }}
                        </h3>
                        <button @click="showModModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300">
                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="p-5 space-y-4 text-left">
                        <p class="text-[12px] text-slate-500 dark:text-zinc-400 leading-relaxed">
                            Anda akan mengubah status karya <strong class="text-slate-700 dark:text-zinc-200">"{{ activeWork.title }}"</strong> oleh {{ activeWork.author }}.
                        </p>

                        <!-- Action Choice if Takedown -->
                        <div v-if="moderationAction !== 'active'" class="space-y-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 dark:text-zinc-400 mb-1">Aksi Takedown</label>
                                <select v-model="moderationAction" class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-[12px] font-semibold text-slate-700 dark:text-zinc-300 focus:outline-none">
                                    <option value="hide">Sembunyikan (Menjadi Draft)</option>
                                    <option value="remove">Hapus Permanen</option>
                                </select>
                            </div>
                        </div>

                        <!-- Reason description -->
                        <div class="space-y-1.5">
                            <label class="block text-[11px] font-bold text-slate-500 dark:text-zinc-400">Catatan/Alasan Keputusan</label>
                            <textarea
                                v-model="moderationReason"
                                rows="3"
                                placeholder="Tulis alasan keputusan moderasi..."
                                class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-[12px] font-medium text-slate-700 dark:text-zinc-300 placeholder-slate-400 focus:outline-none resize-none"
                            />
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-2 px-5 py-3 border-t border-slate-100 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-800/40">
                        <button @click="showModModal = false" class="rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-4 py-2 text-[12px] font-bold text-slate-600 dark:text-zinc-400 hover:bg-slate-50 transition-colors">
                            Batal
                        </button>
                        <button
                            @click="submitQuickModeration"
                            class="rounded-xl px-4 py-2 text-[12px] font-bold text-white transition-colors shadow-sm"
                            :class="moderationAction === 'active' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'"
                        >
                            Terapkan
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

    </PagiAdminLayout>
</template>
