<script setup>
import { PlusCircle, X } from "lucide-vue-next";

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

const handleFileChange = (e) => {
	const file = e.target.files[0];
	if (file) {
		emit("update", file);
	}
};
</script>

<template>
    <div class="space-y-3">
        <div v-if="previewUrl" :class="['relative group rounded-2xl overflow-hidden border border-gray-100 shadow-sm', aspectRatio]">
            <img :src="previewUrl" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                <button @click="emit('remove')" class="p-2 bg-white/20 backdrop-blur-md text-white rounded-full hover:bg-red-500 transition-colors">
                    <X class="w-4 h-4" />
                </button>
            </div>
        </div>
        <label v-else :class="['flex flex-col items-center justify-center w-full border-2 border-dashed border-gray-100 rounded-2xl cursor-pointer hover:bg-gray-50 hover:border-blue-200 transition-all group', aspectRatio]">
            <div class="p-3 bg-gray-50 rounded-full mb-2 group-hover:bg-blue-50 transition-colors">
                <PlusCircle class="w-5 h-5 text-slate-400 group-hover:text-blue-500" />
            </div>
            <span class="text-[11px] font-bold text-slate-400 group-hover:text-blue-500 uppercase tracking-wider">{{ label }}</span>
            <input type="file" class="hidden" accept="image/*" @change="handleFileChange" />
        </label>
        <p v-if="recommendation" class="text-[10px] text-slate-400 text-center italic">{{ recommendation }}</p>
    </div>
</template>
