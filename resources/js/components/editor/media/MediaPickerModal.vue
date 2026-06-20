<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { Search, Loader2, X, Image as ImageIcon } from "lucide-vue-next";

const emit = defineEmits(["select", "close"]);

const media = ref([]);
const isLoading = ref(true);
const searchQuery = ref("");

const fetchMedia = async () => {
	isLoading.value = true;
	try {
		const response = await axios.get("/portal-admin/media", {
			headers: { Accept: "application/json" },
		});
		if (response.data.success) {
			media.value = response.data.media;
		}
	} catch (error) {
		console.error("Gagal memuat galeri:", error);
	} finally {
		isLoading.value = false;
	}
};

const formatSize = (bytes) => {
	if (!bytes) return "0 B";
	const k = 1024;
	const sizes = ["B", "KB", "MB", "GB"];
	const i = Math.floor(Math.log(bytes) / Math.log(k));
	return `${parseFloat((bytes / k ** i).toFixed(2))} ${sizes[i]}`;
};

const selectImage = (path) => {
	emit("select", path);
};

onMounted(() => {
	fetchMedia();
});
</script>

<template>
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-200">
        <div class="bg-white dark:bg-slate-800 rounded-3xl w-full max-w-4xl h-[80vh] flex flex-col shadow-2xl border border-slate-100 dark:border-slate-700 overflow-hidden relative animate-in zoom-in-95 duration-200">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-slate-100 dark:border-slate-700 shrink-0">
                <div>
                    <h3 class="text-base font-black text-slate-800 dark:text-white">Pilih dari Galeri Media</h3>
                    <p class="text-[11px] font-medium text-slate-400 mt-0.5">Pilih foto yang sudah pernah diunggah sebelumnya</p>
                </div>
                <button @click="$emit('close')" class="w-8 h-8 flex items-center justify-center hover:bg-slate-100 dark:hover:bg-slate-700 rounded-full text-slate-400 dark:text-slate-500 cursor-pointer">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Search and Action bar -->
            <div class="px-6 py-4 border-b border-slate-50 dark:border-slate-800 shrink-0 flex gap-4">
                <div class="relative flex-1 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-200 dark:border-slate-700 h-[38px] flex items-center px-3 focus-within:ring-1 focus-within:ring-slate-400 dark:focus-within:ring-slate-650 transition-all">
                    <Search class="w-4 h-4 text-slate-400 shrink-0" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari foto..."
                        class="w-full h-full bg-transparent border-none focus:outline-none focus:ring-0 text-[13px] font-medium text-slate-700 dark:text-slate-200 placeholder-slate-400 pl-2.5 outline-none"
                    />
                </div>
            </div>

            <!-- Media Grid Area -->
            <div class="flex-1 overflow-y-auto p-6 bg-slate-50/50 dark:bg-slate-900/20">
                <!-- Loading State -->
                <div v-if="isLoading" class="w-full h-full flex flex-col items-center justify-center gap-3 py-20 text-slate-400">
                    <Loader2 class="w-8 h-8 animate-spin text-blue-600" />
                    <span class="text-xs font-bold uppercase tracking-wider">Memuat Galeri...</span>
                </div>

                <!-- Empty State -->
                <div v-else-if="media.length === 0" class="w-full h-full flex flex-col items-center justify-center gap-3 py-20 text-slate-400">
                    <ImageIcon class="w-12 h-12 opacity-40" />
                    <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300">Belum Ada Media</h4>
                    <p class="text-xs text-slate-400">Media yang Anda upload di post otomatis akan tampil di sini.</p>
                </div>

                <!-- Media Grid -->
                <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    <div 
                        v-for="item in media.filter(m => m.filename.toLowerCase().includes(searchQuery.toLowerCase()))" 
                        :key="item.id" 
                        @click="selectImage(item.path)"
                        class="group cursor-pointer bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col justify-between hover:border-blue-500 hover:shadow-md transition-all"
                    >
                        <div class="aspect-square bg-slate-50 dark:bg-slate-900 overflow-hidden relative">
                            <img :src="item.path" :alt="item.filename" class="w-full h-full object-cover group-hover:scale-102 transition-transform duration-300" />
                            <div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <span class="bg-blue-600 text-white text-[10px] font-bold px-2.5 py-1 rounded-full shadow">Pilih</span>
                            </div>
                        </div>
                        <div class="p-3 border-t border-slate-50 dark:border-slate-700">
                            <p class="text-[11px] font-bold text-slate-700 dark:text-slate-200 truncate" :title="item.filename">
                                {{ item.filename }}
                            </p>
                            <p class="text-[9.5px] font-medium text-slate-400 mt-0.5">
                                {{ formatSize(item.size) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
