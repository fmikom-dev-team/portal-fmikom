<script setup>
import { Check, Play, X, Youtube } from "lucide-vue-next";
import { nextTick, onBeforeUnmount, onMounted, ref } from "vue";

const props = defineProps({ editor: Object });
const emit = defineEmits(["close"]);

const isOpen = ref(false);
const inputRef = ref(null);
const urlValue = ref("");
const error = ref("");

const open = () => {
	urlValue.value = "";
	error.value = "";
	isOpen.value = true;
	nextTick(() => inputRef.value?.focus());
};

const close = () => {
	isOpen.value = false;
	emit("close");
};

const apply = () => {
	const url = urlValue.value.trim();
	if (!url) {
		error.value = "URL YouTube diperlukan.";
		return;
	}

	const ytRegex =
		/^(https?:\/\/)?(www\.)?(youtube\.com\/(watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/;
	if (!ytRegex.test(url)) {
		error.value = "URL YouTube tidak valid. Contoh: https://youtu.be/abc123";
		return;
	}

	props.editor.chain().focus().setYoutubeVideo({ src: url }).run();
	close();
};

defineExpose({ open, close });

const handleKey = (e) => {
	if (e.key === "Escape") close();
};
onMounted(() => document.addEventListener("keydown", handleKey));
onBeforeUnmount(() => document.removeEventListener("keydown", handleKey));
</script>

<template>
    <!-- Backdrop -->
    <Transition name="fade">
        <div v-if="isOpen" class="fixed inset-0 z-[200] flex items-center justify-center p-4"
             @click.self="close">
            <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" @click="close" />

            <!-- Panel -->
            <div class="relative bg-white rounded-3xl shadow-[0_32px_64px_rgba(0,0,0,0.2)] p-8 w-full max-w-md z-10 transition-all">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-red-50 flex items-center justify-center">
                            <Youtube class="w-6 h-6 text-red-500" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Embed YouTube Video</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Tempel link video YouTube</p>
                        </div>
                    </div>
                    <button @click="close" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-gray-100 rounded-xl transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Input -->
                <div class="space-y-3">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">URL Video</label>
                    <div class="relative">
                        <Play class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <input
                            ref="inputRef"
                            v-model="urlValue"
                            type="url"
                            placeholder="https://youtu.be/xxxxxx atau https://youtube.com/watch?v=xxxxxx"
                            class="w-full pl-10 pr-4 py-3 text-sm bg-gray-50/80 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 placeholder:text-slate-400 text-slate-700 transition-all"
                            @keydown.enter.prevent="apply"
                        />
                    </div>
                    <p v-if="error" class="text-xs text-red-500 flex items-center gap-1.5">
                        <X class="w-3.5 h-3.5" /> {{ error }}
                    </p>
                    <p class="text-[11px] text-slate-400">
                        Contoh: <span class="font-mono text-slate-500">https://youtu.be/dQw4w9WgXcQ</span>
                    </p>
                </div>

                <!-- Buttons -->
                <div class="flex items-center justify-end gap-2 mt-6 pt-4 border-t border-gray-100">
                    <button @click="close"
                            class="px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-gray-100 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button @click="apply"
                            class="px-5 py-2.5 text-sm font-semibold bg-red-500 hover:bg-red-600 text-white rounded-xl transition-colors flex items-center gap-2 shadow-sm shadow-red-200">
                        <Youtube class="w-4 h-4" />
                        Sisipkan Video
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to       { opacity: 0; }
</style>
