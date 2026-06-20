<script setup lang="ts">
import { router, useForm } from "@inertiajs/vue3";
import { CheckCircle2, Eye, RefreshCw, Search, Trash2 } from "lucide-vue-next";
import { computed, ref } from "vue";
import PagiAdminLayout from "@/layouts/PagiAdminLayout.vue";

interface TakedownItem {
	id: number;
	title: string;
	author: string;
	authorHandle: string;
	category: string;
	status: "hidden" | "removed";
	reason: string;
	time: string;
	thumbnail?: string;
}

const props = defineProps<{
	takedowns?: TakedownItem[];
}>();

const computedTakedowns = computed(() => props.takedowns ?? []);

// Search query state
const searchQuery = ref("");
const isLoading = ref(false);
const brokenImages = ref<Record<number | string, boolean>>({});

// Filtered takedowns
const filteredTakedowns = computed(() => {
	return computedTakedowns.value.filter((t) => {
		const matchSearch =
			t.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
			t.author.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
			t.reason.toLowerCase().includes(searchQuery.value.toLowerCase());
		return matchSearch;
	});
});

const restoreContent = (item: TakedownItem) => {
	if (
		confirm(
			`Apakah Anda yakin ingin memulihkan karya "${item.title}" kembali aktif ke publik?`,
		)
	) {
		isLoading.value = true;
		router.post(
			`/pagi/admin/takedowns/${item.id}/restore`,
			{},
			{
				onSuccess: () => {
					isLoading.value = false;
				},
				onError: () => {
					isLoading.value = false;
				},
			},
		);
	}
};
</script>

<template>
    <PagiAdminLayout title="Konten Takedown">
        
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Konten Takedown</h1>
            <p class="mt-0.5 text-[13px] text-slate-400 dark:text-zinc-500">Kelola dan tinjau seluruh karya mahasiswa yang sedang diturunkan dari publik</p>
        </div>

        <!-- Search Bar -->
        <div class="mb-5 relative flex items-center h-10 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm px-3">
            <Search class="h-4 w-4 text-slate-400 shrink-0 mr-2.5" />
            <input
                v-model="searchQuery"
                type="text"
                placeholder="Cari judul karya, nama pembuat, atau alasan penurunan..."
                class="w-full bg-transparent text-[13px] font-medium text-slate-700 dark:text-zinc-200 placeholder-slate-400 focus:outline-none"
            />
        </div>

        <!-- Table / Cards list -->
        <div class="space-y-4">
            <!-- Mobile Cards list (block md:hidden) -->
            <div class="block md:hidden space-y-4">
                <div v-for="t in filteredTakedowns" :key="t.id" class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 p-4 space-y-3.5 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="h-12 w-16 shrink-0 rounded-xl overflow-hidden bg-slate-50 dark:bg-zinc-800 border border-slate-100 dark:border-zinc-800">
                            <img
                                v-if="t.thumbnail && !brokenImages[t.id]"
                                :src="t.thumbnail"
                                :alt="t.title"
                                @error="brokenImages[t.id] = true"
                                class="h-full w-full object-cover"
                            />
                            <div v-else class="h-full w-full flex items-center justify-center">
                                <svg class="h-4.5 w-4.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159" />
                                </svg>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-[9px] font-bold uppercase tracking-wider text-rose-500 bg-rose-50 dark:bg-rose-950/40 px-2 py-0.5 rounded-full">
                                {{ t.category }}
                            </span>
                            <h3 class="mt-1 text-[13px] font-bold text-slate-800 dark:text-zinc-100 leading-tight truncate">{{ t.title }}</h3>
                            <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-0.5">{{ t.author }} ({{ t.authorHandle }})</p>
                        </div>
                    </div>

                    <div class="p-2.5 rounded-xl bg-rose-50/30 dark:bg-rose-950/10 border border-rose-100/30 dark:border-rose-900/10 text-[11.5px] text-rose-700 dark:text-rose-400 leading-relaxed font-medium">
                        <strong>Alasan Takedown:</strong> "{{ t.reason }}"
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-50 dark:border-zinc-800/40 pt-3">
                        <span class="text-[10px] text-slate-400 dark:text-zinc-500">Diturunkan: {{ t.time }}</span>
                        
                        <button
                            @click="restoreContent(t)"
                            class="rounded-lg border border-emerald-200 dark:border-emerald-900/50 bg-emerald-50 dark:bg-emerald-950/20 px-3 py-1.5 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100/50 transition-colors flex items-center gap-1.5"
                        >
                            <RefreshCw class="h-3 w-3" /> Pulihkan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Desktop Table (hidden md:block) -->
            <div class="hidden md:block rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 overflow-hidden shadow-sm">
                <table class="w-full min-w-[750px]">
                    <caption class="sr-only">Daftar Konten Diturunkan</caption>
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-zinc-800">
                            <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Karya</th>
                            <th class="px-4 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Alasan Penurunan</th>
                            <th class="px-4 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Pembuat</th>
                            <th class="px-4 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Kategori</th>
                            <th class="px-4 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Waktu</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-zinc-800/50">
                        <tr v-for="t in filteredTakedowns" :key="t.id" class="hover:bg-slate-50 dark:hover:bg-zinc-800/20 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-14 shrink-0 rounded-xl overflow-hidden bg-slate-50 dark:bg-zinc-800 border border-slate-100 dark:border-zinc-800">
                                        <img
                                            v-if="t.thumbnail && !brokenImages[t.id]"
                                            :src="t.thumbnail"
                                            :alt="t.title"
                                            @error="brokenImages[t.id] = true"
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
                                            {{ t.title }}
                                        </p>
                                        <span class="inline-flex items-center rounded-full bg-rose-50 text-rose-600 dark:bg-rose-950/20 dark:text-rose-400 px-2 py-0.5 text-[8.5px] font-bold uppercase tracking-wider mt-1 border border-rose-100 dark:border-rose-900/30">
                                            Takedown
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="max-w-[280px]">
                                    <p class="text-[11.5px] text-slate-600 dark:text-zinc-300 font-medium leading-tight line-clamp-2" :title="t.reason">
                                        "{{ t.reason }}"
                                    </p>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-[12px] font-medium text-slate-700 dark:text-zinc-300">{{ t.author }}</p>
                                <p class="text-[10.5px] text-slate-400 dark:text-zinc-500">{{ t.authorHandle }}</p>
                            </td>
                            <td class="px-4 py-4 text-[11.5px] text-slate-600 dark:text-zinc-400">
                                {{ t.category }}
                            </td>
                            <td class="px-4 py-4 text-[11.5px] text-slate-400 dark:text-zinc-500">
                                {{ t.time }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button
                                    @click="restoreContent(t)"
                                    class="rounded-lg border border-emerald-200 dark:border-emerald-900/50 bg-emerald-50 dark:bg-emerald-950/20 px-3 py-1.5 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100/50 transition-all shadow-sm flex items-center gap-1 ml-auto"
                                >
                                    <RefreshCw class="h-3 w-3" /> Restore Content
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-if="filteredTakedowns.length === 0" class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 p-12 text-center">
                <div class="mx-auto h-12 w-12 rounded-full bg-slate-50 dark:bg-zinc-800 flex items-center justify-center mb-3">
                    <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 class="text-[14px] font-bold text-slate-800 dark:text-zinc-100">Tidak ada konten takedown</h3>
                <p class="text-[12px] text-slate-500 dark:text-zinc-400 mt-1">Belum ada karya yang disembunyikan atau di-takedown oleh admin.</p>
            </div>
        </div>

    </PagiAdminLayout>
</template>
