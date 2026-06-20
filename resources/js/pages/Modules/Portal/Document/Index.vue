<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { Search, Download, FileText, Pin, ExternalLink } from "lucide-vue-next";
import { ref, watch } from "vue";
import PublicFooter from "@/components/Portal/PublicFooter.vue";
import PublicNavbar from "@/components/Portal/PublicNavbar.vue";

const props = defineProps({
	pinnedDocuments: {
		type: Array,
		default: () => [],
	},
	documents: {
		type: Object,
		default: () => ({ data: [] }),
	},
	categories: {
		type: Array,
		default: () => [],
	},
	filters: {
		type: Object,
		default: () => ({ search: "", category: "" }),
	},
});

const localSearch = ref(props.filters.search || "");
const selectedCategory = ref(props.filters.category || "");

// Watch for search and category changes to reload from server
watch([localSearch, selectedCategory], ([newSearch, newCat]) => {
	router.get(
		"/dokumen",
		{ search: newSearch, category: newCat },
		{ preserveState: true, replace: true }
	);
});

const formatSize = (bytes) => {
	if (!bytes) return "0 B";
	const k = 1024;
	const sizes = ["B", "KB", "MB", "GB"];
	const i = Math.floor(Math.log(bytes) / Math.log(k));
	return `${parseFloat((bytes / k ** i).toFixed(2))} ${sizes[i]}`;
};

const formatDate = (dateString) => {
	return new Date(dateString).toLocaleDateString("id-ID", {
		day: "numeric",
		month: "long",
		year: "numeric",
	});
};
</script>

<template>
    <Head>
        <title>Dokumen & Arsip - Portal FMIKOM</title>
    </Head>

    <div class="min-h-screen bg-slate-50/50 dark:bg-slate-900 font-sans antialiased text-slate-900 dark:text-slate-100 transition-colors duration-300">
        <!-- Navigation -->
        <PublicNavbar />

        <main class="py-16 lg:py-24">
            <div class="max-w-7xl mx-auto px-4">
                
                <!-- Header Section -->
                <div class="mb-16 text-center max-w-3xl mx-auto">
                    <h1 class="text-4xl lg:text-6xl font-black text-slate-900 dark:text-white mb-6 tracking-tight">Dokumen & Arsip Resmi</h1>
                    <p class="text-lg text-slate-500 dark:text-slate-400 leading-relaxed font-medium">Unduh dokumen resmi, pengumuman, panduan akademik, dan formulir perkuliahan FMIKOM secara aman.</p>
                </div>

                <!-- Pinned / Important Documents (If present) -->
                <div v-if="pinnedDocuments.length > 0" class="mb-12">
                    <h2 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <Pin class="w-3.5 h-3.5 text-amber-500 animate-bounce" />
                        Dokumen & Pengumuman Penting
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div 
                            v-for="doc in pinnedDocuments" 
                            :key="doc.id" 
                            class="relative overflow-hidden bg-gradient-to-r from-amber-500/10 to-orange-500/10 dark:from-amber-950/20 dark:to-orange-950/20 rounded-2xl p-5 border border-amber-250 dark:border-amber-900/50 shadow-sm hover:shadow-md transition-all flex items-start gap-4"
                        >
                            <div class="w-12 h-12 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-500/10">
                                <FileText class="w-6 h-6" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300 text-[10px] font-black px-2 py-0.5 rounded-full uppercase tracking-wider">
                                    {{ doc.category || 'PENTING' }}
                                </span>
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-200 mt-2 leading-snug">
                                    {{ doc.title }}
                                </h3>
                                <p v-if="doc.description" class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2 leading-relaxed">
                                    {{ doc.description }}
                                </p>
                                
                                <div class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-slate-450 dark:text-slate-500 font-medium">
                                    <span>{{ formatSize(doc.file_size) }}</span>
                                    <span>•</span>
                                    <span>{{ formatDate(doc.created_at) }}</span>
                                    <span>•</span>
                                    <span>{{ doc.download_count }} unduhan</span>
                                </div>
                            </div>
                            <a 
                                :href="`/dokumen/download/${doc.id}`"
                                class="bg-amber-500 hover:bg-amber-600 text-white self-center p-3 rounded-xl transition-all shadow-md shadow-amber-500/15 cursor-pointer active:scale-95 shrink-0"
                                title="Unduh Berkas"
                            >
                                <Download class="w-4 h-4" />
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Search & Filters -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-4 border border-slate-150 dark:border-slate-800 shadow-sm mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
                    <!-- Category Filters -->
                    <div class="flex flex-wrap items-center gap-1.5 w-full md:w-auto overflow-x-auto scrollbar-none">
                        <button 
                            @click="selectedCategory = ''"
                            :class="[
                                selectedCategory === '' 
                                    ? 'bg-blue-600 text-white font-bold shadow-lg shadow-blue-500/10' 
                                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 font-semibold bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800',
                                'px-4 py-2 rounded-xl text-xs transition-all whitespace-nowrap cursor-pointer'
                            ]"
                        >
                            Semua Dokumen
                        </button>
                        <button 
                            v-for="cat in categories" 
                            :key="cat"
                            @click="selectedCategory = cat"
                            :class="[
                                selectedCategory === cat 
                                    ? 'bg-blue-600 text-white font-bold shadow-lg shadow-blue-500/10' 
                                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 font-semibold bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800',
                                'px-4 py-2 rounded-xl text-xs transition-all whitespace-nowrap cursor-pointer'
                            ]"
                        >
                            {{ cat }}
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="relative w-full md:w-[320px] shrink-0">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <Search class="w-4 h-4" />
                        </span>
                        <input 
                            v-model="localSearch"
                            type="text" 
                            placeholder="Cari nama atau deskripsi dokumen..."
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl text-xs font-semibold bg-slate-50 dark:bg-slate-900 border border-slate-150 dark:border-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-all placeholder-slate-400 text-slate-700 dark:text-white"
                        />
                    </div>
                </div>

                <!-- Documents List -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-150 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div v-if="documents.data.length > 0" class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        <div 
                            v-for="doc in documents.data" 
                            :key="doc.id" 
                            class="group p-5 md:p-6 transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-900/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                        >
                            <!-- Info -->
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center border border-blue-100/35 shrink-0">
                                    <FileText class="w-6 h-6" />
                                </div>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-200 leading-snug group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                            {{ doc.title }}
                                        </h3>
                                        <span v-if="doc.category" class="bg-slate-100 dark:bg-slate-900 border border-slate-150 dark:border-slate-800 text-slate-600 dark:text-slate-400 text-[9.5px] font-black px-2 py-0.5 rounded-full uppercase tracking-wider">
                                            {{ doc.category }}
                                        </span>
                                    </div>
                                    <p v-if="doc.description" class="text-xs text-slate-450 dark:text-slate-400 leading-relaxed mb-2">{{ doc.description }}</p>
                                    
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-slate-400 dark:text-slate-500 font-medium">
                                        <span class="truncate max-w-[180px] text-slate-500 dark:text-slate-400" :title="doc.file_name">{{ doc.file_name }}</span>
                                        <span>•</span>
                                        <span>{{ formatSize(doc.file_size) }}</span>
                                        <span>•</span>
                                        <span>{{ formatDate(doc.created_at) }}</span>
                                        <span>•</span>
                                        <span>{{ doc.download_count }} unduhan</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Download button -->
                            <a 
                                :href="`/dokumen/download/${doc.id}`"
                                class="bg-blue-600 hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-500/10 text-white px-5 py-3 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 active:scale-95 cursor-pointer shrink-0 select-none sm:w-auto"
                            >
                                <Download class="w-4 h-4" />
                                Unduh Berkas
                            </a>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-24 px-4 bg-white dark:bg-slate-800">
                        <div class="w-16 h-16 bg-slate-50 dark:bg-slate-900 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 dark:text-slate-700">
                            <Search class="w-8 h-8" />
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1 tracking-tight">Tidak Ada Dokumen</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Tidak ada arsip dokumen yang sesuai dengan kata kunci pencarian Anda.</p>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="documents.links && documents.links.length > 3" class="mt-12 flex justify-center gap-2">
                    <template v-for="(link, k) in documents.links" :key="k">
                        <div v-if="link.url === null" class="px-4 py-2 text-sm font-bold text-slate-350 dark:text-slate-600 bg-white dark:bg-slate-800 border border-slate-50 dark:border-slate-800 rounded-xl cursor-default" v-html="link.label"></div>
                        <Link v-else :href="link.url" 
                            class="px-4 py-2 text-sm font-bold rounded-xl transition-all border"
                            :class="link.active ? 'bg-blue-600 text-white border-blue-600 shadow-lg shadow-blue-100' : 'bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-450 border-slate-100 dark:border-slate-800 hover:border-blue-600 dark:hover:border-blue-600 hover:text-blue-600 dark:hover:text-blue-400'">
                            <span v-html="link.label"></span>
                        </Link>
                    </template>
                </div>

            </div>
        </main>

        <!-- Footer -->
        <PublicFooter />
    </div>
</template>

<style scoped>
.scrollbar-none::-webkit-scrollbar {
    display: none;
}
.scrollbar-none {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
