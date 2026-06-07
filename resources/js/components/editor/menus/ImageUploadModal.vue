<script setup>
import axios from "axios";
import {
	Check,
	File,
	FileText,
	Image as ImageIcon,
	Link as LinkIcon,
	Loader2,
	Upload,
	X,
} from "lucide-vue-next";
import { nextTick, onBeforeUnmount, onMounted, ref } from "vue";

const props = defineProps({ editor: Object });
const emit = defineEmits(["close", "upload-image"]);

const isOpen = ref(false);
const isDragging = ref(false);
const activeTab = ref("upload"); // 'upload' | 'url'
const urlValue = ref("");
const uploadError = ref("");
const isUploading = ref(false);
const previewFile = ref(null);
const previewUrl = ref("");
const dropRef = ref(null);

const open = () => {
	resetState();
	isOpen.value = true;
};

const close = () => {
	isOpen.value = false;
	emit("close");
};

const resetState = () => {
	isDragging.value = false;
	activeTab.value = "upload";
	urlValue.value = "";
	uploadError.value = "";
	isUploading.value = false;
	previewFile.value = null;
	previewUrl.value = "";
};

/* ── Drag & Drop ── */
const onDragOver = (e) => {
	e.preventDefault();
	isDragging.value = true;
};
const onDragLeave = () => {
	isDragging.value = false;
};
const onDrop = (e) => {
	e.preventDefault();
	isDragging.value = false;
	const file = e.dataTransfer?.files?.[0];
	if (file?.type.startsWith("image/")) setPreview(file);
	else uploadError.value = "Hanya file gambar yang diperbolehkan.";
};

const onFileInput = (e) => {
	const file = e.target.files?.[0];
	if (file) setPreview(file);
};

const setPreview = (file) => {
	uploadError.value = "";
	previewFile.value = file;
	previewUrl.value = URL.createObjectURL(file);
};

/* ── Insert ── */
const insertFromUrl = () => {
	const url = urlValue.value.trim();
	if (!url) {
		uploadError.value = "URL gambar diperlukan.";
		return;
	}
	props.editor.chain().focus().setImage({ src: url }).run();
	close();
};

const insertFromFile = () => {
	if (!previewFile.value) return;
	isUploading.value = true;
	emit("upload-image", previewFile.value, (url) => {
		props.editor.chain().focus().setImage({ src: url }).run();
		isUploading.value = false;
		close();
	});
};

const clearPreview = () => {
	previewFile.value = null;
	previewUrl.value = "";
	uploadError.value = "";
};

defineExpose({ open, close });

const handleKey = (e) => {
	if (e.key === "Escape") close();
};
onMounted(() => document.addEventListener("keydown", handleKey));
onBeforeUnmount(() => document.removeEventListener("keydown", handleKey));
</script>

<template>
    <Transition name="modal-fade">
        <div v-if="isOpen" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" @click="close" />

            <div class="relative bg-white rounded-3xl shadow-[0_32px_64px_rgba(0,0,0,0.2)] p-0 w-full max-w-lg z-10 overflow-hidden">

                <!-- Header -->
                <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 flex items-center justify-center">
                            <ImageIcon class="w-5 h-5 text-blue-500" />
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Sisipkan Gambar</h3>
                    </div>
                    <button @click="close" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-gray-100 rounded-xl transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Tabs -->
                <div class="flex border-b border-gray-100">
                    <button
                        @click="activeTab = 'upload'"
                        :class="activeTab === 'upload' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                        class="flex-1 py-3 text-sm font-semibold border-b-2 transition-colors flex items-center justify-center gap-2"
                    >
                        <Upload class="w-4 h-4" /> Upload File
                    </button>
                    <button
                        @click="activeTab = 'url'"
                        :class="activeTab === 'url' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                        class="flex-1 py-3 text-sm font-semibold border-b-2 transition-colors flex items-center justify-center gap-2"
                    >
                        <LinkIcon class="w-4 h-4" /> URL Gambar
                    </button>
                </div>

                <!-- Upload tab -->
                <div v-if="activeTab === 'upload'" class="p-6">
                    <!-- Preview state -->
                    <div v-if="previewUrl" class="space-y-4">
                        <div class="relative aspect-video rounded-2xl overflow-hidden border border-gray-100 shadow-sm group">
                            <img :src="previewUrl" class="w-full h-full object-contain bg-gray-50" />
                            <button @click="clearPreview" class="absolute top-2 right-2 w-8 h-8 bg-black/50 hover:bg-red-500 text-white rounded-full flex items-center justify-center transition-colors">
                                <X class="w-4 h-4" />
                            </button>
                        </div>
                        <p class="text-xs text-slate-500 text-center truncate">{{ previewFile?.name }}</p>
                    </div>

                    <!-- Dropzone -->
                    <div v-else
                         ref="dropRef"
                         @dragover="onDragOver"
                         @dragleave="onDragLeave"
                         @drop="onDrop"
                         :class="isDragging ? 'border-blue-400 bg-blue-50/60 scale-[1.01]' : 'border-gray-200 hover:border-blue-300 hover:bg-blue-50/40'"
                         class="border-2 border-dashed rounded-2xl p-8 text-center transition-all duration-200 cursor-pointer"
                    >
                        <label class="cursor-pointer flex flex-col items-center gap-3">
                            <div :class="isDragging ? 'bg-blue-100' : 'bg-gray-50'"
                                 class="w-14 h-14 rounded-2xl flex items-center justify-center transition-colors">
                                <Upload :class="isDragging ? 'text-blue-500' : 'text-slate-400'" class="w-7 h-7" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-700">
                                    {{ isDragging ? 'Lepaskan di sini' : 'Seret & lepas gambar' }}
                                </p>
                                <p class="text-xs text-slate-400 mt-0.5">atau <span class="text-blue-500 font-semibold underline">klik untuk pilih file</span></p>
                            </div>
                            <p class="text-[11px] text-slate-400">PNG, JPG, GIF, WebP — maks. 5 MB</p>
                            <input type="file" class="hidden" accept="image/*" @change="onFileInput" />
                        </label>
                    </div>
                    <p v-if="uploadError" class="text-xs text-red-500 mt-2 flex items-center gap-1.5">
                        <X class="w-3.5 h-3.5" /> {{ uploadError }}
                    </p>
                </div>

                <!-- URL tab -->
                <div v-else class="p-6 space-y-3">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">URL Gambar</label>
                    <input
                        v-model="urlValue"
                        type="url"
                        placeholder="https://example.com/gambar.jpg"
                        class="w-full px-4 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 text-slate-700 placeholder:text-slate-400"
                        @keydown.enter.prevent="insertFromUrl"
                    />
                    <p v-if="uploadError" class="text-xs text-red-500">{{ uploadError }}</p>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end gap-2 px-6 pb-6 pt-2">
                    <button @click="close" class="px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-gray-100 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button
                        v-if="activeTab === 'url'"
                        @click="insertFromUrl"
                        class="px-5 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors flex items-center gap-2 shadow-sm"
                    >
                        <Check class="w-4 h-4" /> Sisipkan
                    </button>
                    <button
                        v-else
                        @click="insertFromFile"
                        :disabled="!previewFile || isUploading"
                        class="px-5 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed text-white rounded-xl transition-colors flex items-center gap-2 shadow-sm"
                    >
                        <Loader2 v-if="isUploading" class="w-4 h-4 animate-spin" />
                        <Check v-else class="w-4 h-4" />
                        {{ isUploading ? 'Mengunggah...' : 'Sisipkan Gambar' }}
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.modal-fade-enter-active { transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1); }
.modal-fade-leave-active { transition: all 0.15s ease; }
.modal-fade-enter-from  { opacity: 0; transform: scale(0.95) translateY(8px); }
.modal-fade-leave-to    { opacity: 0; transform: scale(0.96); }
</style>
