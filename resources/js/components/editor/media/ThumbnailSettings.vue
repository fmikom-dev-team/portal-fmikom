<script setup>
import { PlusCircle, X, Maximize2, Crop } from "lucide-vue-next";
import { ref } from "vue";

const props = defineProps({
	previewUrl: {
		type: String,
		default: null,
	},
	label: {
		type: String,
		default: "Unggah Gambar",
	},
	aspectRatio: {
		type: String,
		default: "aspect-[16/9]",
	},
	recommendation: {
		type: String,
		default: "",
	},
});

const emit = defineEmits(["update", "remove"]);

const fitMode = ref("cover"); // 'cover' | 'contain'

const toggleFitMode = () => {
	fitMode.value = fitMode.value === "cover" ? "contain" : "cover";
};

const handleFileChange = (e) => {
	const file = e.target.files[0];
	if (file) {
		emit("update", file);
	}
};
</script>

<template>
    <div class="space-y-3">
        <div v-if="previewUrl" :class="['relative group rounded-2xl overflow-hidden border border-slate-100 dark:border-slate-700 shadow-sm bg-slate-900/10', aspectRatio]">
            <img 
                :src="previewUrl" 
                alt="Thumbnail preview" 
                :class="['w-full h-full transition-transform duration-500 group-hover:scale-105', fitMode === 'cover' ? 'object-cover' : 'object-contain']" 
            />
            
            <!-- Controls Overlay -->
            <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 p-2">
                <button 
                    type="button"
                    @click="toggleFitMode" 
                    :title="fitMode === 'cover' ? 'Tampilkan Mode Full (Original)' : 'Tampilkan Mode Crop (Presisi)'"
                    class="px-3 py-1.5 bg-white/90 dark:bg-slate-800/90 backdrop-blur-md text-slate-800 dark:text-white rounded-xl hover:bg-white text-[10px] font-black transition-all shadow-md flex items-center gap-1.5"
                >
                    <Maximize2 v-if="fitMode === 'cover'" class="w-3.5 h-3.5 text-blue-500" />
                    <Crop v-else class="w-3.5 h-3.5 text-emerald-500" />
                    {{ fitMode === 'cover' ? 'Full' : 'Crop' }}
                </button>
                <button 
                    type="button"
                    @click="emit('remove')" 
                    title="Hapus Gambar"
                    class="p-2 bg-red-600/90 text-white rounded-xl hover:bg-red-600 transition-colors shadow-md"
                >
                    <X class="w-3.5 h-3.5" />
                </button>
            </div>
        </div>
        <label v-else :class="['flex flex-col items-center justify-center w-full border-2 border-dashed border-gray-200 dark:border-slate-700 rounded-2xl cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-800/50 hover:border-blue-300 transition-all group', aspectRatio]">
            <div class="p-3 bg-gray-50 dark:bg-slate-800 rounded-full mb-2 group-hover:bg-blue-50 dark:group-hover:bg-blue-950/40 transition-colors">
                <PlusCircle class="w-5 h-5 text-slate-400 group-hover:text-blue-500" />
            </div>
            <span class="text-[11px] font-bold text-slate-400 group-hover:text-blue-500 uppercase tracking-wider">{{ label }}</span>
            <input type="file" class="hidden" accept="image/*" @change="handleFileChange" />
        </label>
        <div class="flex items-center justify-between gap-2">
            <p v-if="recommendation" class="text-[10px] text-slate-400 italic">{{ recommendation }}</p>
            <span v-if="previewUrl" class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Fit: {{ fitMode === 'cover' ? 'Crop' : 'Full' }}</span>
        </div>
    </div>
</template>
