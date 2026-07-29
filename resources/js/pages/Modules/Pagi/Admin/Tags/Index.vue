<script setup lang="ts">
import { computed, ref } from "vue";
import PagiAdminLayout from "@/layouts/PagiAdminLayout.vue";
import { Plus, Search, Tag, Eye, Trash2 } from "lucide-vue-next";

const props = defineProps<{
	tags?: Array<{
		id: number;
		name: string;
		slug: string;
		color: string;
		usage_count: number;
		is_active: boolean;
	}>;
}>();

const searchQuery = ref("");

const allTags = computed(() => props.tags ?? []);

const filteredTags = computed(() => {
	const q = searchQuery.value.toLowerCase();
	return allTags.value.filter(
		(t) => t.name.toLowerCase().includes(q) || t.slug.toLowerCase().includes(q)
	);
});
</script>

<template>
    <PagiAdminLayout title="Tags & Kategori">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Tags & Kategori</h1>
                <p class="mt-0.5 text-[13px] text-slate-400 dark:text-zinc-500 font-medium">
                    Kelola tag pencarian dan kategori karya mahasiswa di modul PAGI
                </p>
            </div>
            
            <!-- Button placeholder (Aesthetic only) -->
            <button class="flex items-center gap-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 px-4 py-2.5 text-[12px] font-bold text-white transition-all shadow-sm shadow-indigo-100 dark:shadow-none shrink-0 cursor-not-allowed opacity-80">
                <Plus class="w-4 h-4" />
                Tambah Tag
            </button>
        </div>

        <!-- Filter Toolbar -->
        <div class="mb-5 flex items-center justify-between gap-4 bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 rounded-2xl p-4">
            <div class="relative w-full sm:w-[320px]">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari tag atau slug..."
                    class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 pl-9 pr-4 py-2 text-[12px] font-medium text-slate-700 dark:text-zinc-300 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                />
            </div>
            <div class="text-[11px] text-slate-400 dark:text-zinc-500 font-bold">
                {{ filteredTags.length }} tag terdaftar
            </div>
        </div>

        <!-- Tags Grid Layout -->
        <div v-if="filteredTags.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div 
                v-for="tag in filteredTags" 
                :key="tag.id"
                class="bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 rounded-2xl p-4.5 flex flex-col justify-between hover:shadow-lg hover:shadow-slate-100/50 dark:hover:shadow-none transition-all duration-300 group"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div 
                            class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                            :style="{ backgroundColor: tag.color + '15' }"
                        >
                            <Tag class="w-4 h-4" :style="{ color: tag.color }" />
                        </div>
                        <div>
                            <h3 class="text-[13.5px] font-bold text-slate-800 dark:text-zinc-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-450 transition-colors">
                                {{ tag.name }}
                            </h3>
                            <p class="text-[11px] text-slate-400 dark:text-zinc-500 font-mono mt-0.5">
                                #{{ tag.slug }}
                            </p>
                        </div>
                    </div>

                    <!-- Active Status Badge -->
                    <span 
                        class="inline-flex items-center rounded-full px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-wider"
                        :class="tag.is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-zinc-800 dark:text-zinc-400'"
                    >
                        {{ tag.is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                <div class="mt-5 pt-3.5 border-t border-slate-50 dark:border-zinc-800/60 flex items-center justify-between text-[12px]">
                    <div class="flex items-baseline gap-1 text-slate-550 dark:text-zinc-400">
                        <span class="text-[14px] font-black text-slate-800 dark:text-zinc-100">{{ tag.usage_count }}</span>
                        <span class="text-[10px] font-bold text-slate-400">karya</span>
                    </div>

                    <!-- Fake/Aesthetic management buttons -->
                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        <button class="p-1.5 rounded-lg border border-slate-100 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-800 text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 transition-all cursor-not-allowed">
                            <Eye class="w-3.5 h-3.5" />
                        </button>
                        <button class="p-1.5 rounded-lg border border-red-100 dark:border-red-950 bg-red-50/50 dark:bg-red-950/10 hover:bg-red-55/70 text-red-400 hover:text-red-600 transition-all cursor-not-allowed">
                            <Trash2 class="w-3.5 h-3.5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 p-12 text-center animate-fade-in">
            <div class="mx-auto h-12 w-12 rounded-full bg-slate-50 dark:bg-zinc-800 flex items-center justify-center mb-3">
                <Tag class="h-6 w-6 text-slate-400" />
            </div>
            <h3 class="text-[14px] font-bold text-slate-800 dark:text-zinc-100">Tidak ada tag ditemukan</h3>
            <p class="text-[13px] text-slate-500 dark:text-zinc-400 mt-1">Coba masukkan kata pencarian lain.</p>
        </div>
    </PagiAdminLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
