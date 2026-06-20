<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import {
	AlertTriangle,
	CheckCircle2,
	MessageSquare,
	Save,
	Settings as SettingsIcon,
	User,
	Camera,
} from "lucide-vue-next";
import { reactive, ref } from "vue";
import PortalAdminLayout from "@/layouts/PortalAdminLayout.vue";

const props = defineProps({
	settings: {
		type: Object,
		default: () => ({}),
	},
});

const form = reactive({
	posts_per_page: props.settings.posts_per_page || "10",
	allow_comments: props.settings.allow_comments !== "0" ? "1" : "0",
	moderate_comments: props.settings.moderate_comments !== "0" ? "1" : "0",
	author_name: props.settings.author_name || "",
	author_image_file: null as File | null,
});

const authorImagePreview = ref(props.settings.author_image || null);
const fileInput = ref<HTMLInputElement | null>(null);

const handleFileChange = (e: Event) => {
	const target = e.target as HTMLInputElement;
	const file = target.files?.[0];
	if (file) {
		form.author_image_file = file;
		authorImagePreview.value = URL.createObjectURL(file);
	}
};

const triggerFileInput = () => {
	fileInput.value?.click();
};

const isProcessing = ref(false);
const isSuccess = ref(false);

const submit = () => {
	isProcessing.value = true;
	// Inertia router.post supports multipart/form-data when File objects are present in data
	router.post("/portal-admin/settings", form, {
		preserveScroll: true,
		onSuccess: () => {
			isProcessing.value = false;
			isSuccess.value = true;
			form.author_image_file = null; // reset file input
			setTimeout(() => (isSuccess.value = false), 3000);
		},
		onError: () => {
			isProcessing.value = false;
		},
	});
};
</script>

<template>
    <PortalAdminLayout title="Pengaturan">

        <!-- Page Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-[19px] font-black text-slate-900 dark:text-white">Pengaturan Sistem</h2>
                <p class="text-[13px] font-medium text-slate-500 mt-1">Konfigurasi umum untuk portal admin FMIKOM.</p>
            </div>
            <button
                @click="submit"
                :disabled="isProcessing"
                class="inline-flex items-center gap-2 bg-[#2563EB] hover:bg-blue-700 disabled:opacity-50 text-white px-5 py-2.5 rounded-xl text-[13px] font-bold shadow-md shadow-blue-500/20 transition-all"
            >
                <CheckCircle2 v-if="isSuccess" class="w-4 h-4" />
                <Save v-else class="w-4 h-4" />
                {{ isProcessing ? 'Menyimpan...' : isSuccess ? 'Tersimpan!' : 'Simpan Pengaturan' }}
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- LEFT: Main Settings -->
            <div class="lg:col-span-2 space-y-5">

                <!-- Profil Publik Postingan -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800">
                        <div class="w-8 h-8 bg-blue-50 dark:bg-blue-500/10 rounded-lg flex items-center justify-center text-[#2563EB]">
                            <User class="w-4 h-4" />
                        </div>
                        <div>
                            <h3 class="text-[14px] font-black text-slate-800 dark:text-white">Profil Publik Postingan</h3>
                            <p class="text-[11px] font-medium text-slate-400">Pengaturan author berita & artikel</p>
                        </div>
                    </div>
                    <div class="p-6 space-y-6">
                        <!-- Nama Author -->
                        <div>
                            <label class="block text-[12px] font-bold text-slate-600 dark:text-slate-400 mb-2">Nama Author</label>
                            <input
                                v-model="form.author_name"
                                type="text"
                                placeholder="Contoh: Admin FMIKOM"
                                class="w-full bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-[13px] font-medium text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-[#2563EB] focus:border-transparent outline-none transition-all"
                            />
                        </div>

                        <!-- Foto Profile -->
                        <div>
                            <label class="block text-[12px] font-bold text-slate-600 dark:text-slate-400 mb-3">Foto Profile Author</label>
                            <div class="flex items-center gap-5">
                                <!-- Avatar Preview Container -->
                                <div class="relative group w-20 h-20 rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 flex items-center justify-center shrink-0 shadow-sm transition-all">
                                    <img v-if="authorImagePreview" :src="authorImagePreview" class="w-full h-full object-cover" alt="Author Avatar" />
                                    <div v-else class="text-slate-450 dark:text-slate-500">
                                        <User class="w-8 h-8 opacity-60" />
                                    </div>
                                    
                                    <!-- Hover Overlay -->
                                    <button 
                                        type="button" 
                                        @click="triggerFileInput"
                                        class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer text-white"
                                    >
                                        <Camera class="w-5 h-5" />
                                    </button>
                                </div>

                                <!-- Upload Button and Help text -->
                                <div class="space-y-1.5">
                                    <input
                                        ref="fileInput"
                                        type="file"
                                        accept="image/*"
                                        class="hidden"
                                        @change="handleFileChange"
                                    />
                                    <button
                                        type="button"
                                        @click="triggerFileInput"
                                        class="px-4 py-2 border border-slate-200 dark:border-slate-750 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded-xl text-[12px] font-bold text-slate-700 dark:text-slate-350 bg-white dark:bg-slate-800 shadow-sm transition-colors cursor-pointer"
                                    >
                                        Pilih Foto
                                    </button>
                                    <p class="text-[10.5px] font-medium text-slate-400 leading-normal">
                                        Format JPG, PNG, atau WebP. Ukuran maks 2MB.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content Settings -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800">
                        <div class="w-8 h-8 bg-violet-50 dark:bg-violet-500/10 rounded-lg flex items-center justify-center text-violet-500">
                            <MessageSquare class="w-4 h-4" />
                        </div>
                        <div>
                            <h3 class="text-[14px] font-black text-slate-800 dark:text-white">Konten & Komentar</h3>
                            <p class="text-[11px] font-medium text-slate-400">Pengaturan posting dan moderasi</p>
                        </div>
                    </div>
                    <div class="p-6 space-y-5">
                        <div>
                            <label class="block text-[12px] font-bold text-slate-600 dark:text-slate-400 mb-2">Postingan Per Halaman</label>
                            <input
                                v-model="form.posts_per_page"
                                type="number"
                                min="1"
                                max="100"
                                class="w-32 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-[13px] font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-[#2563EB] focus:border-transparent outline-none transition-all"
                            />
                        </div>

                        <!-- Toggle: Allow Comments -->
                        <div class="flex items-center justify-between py-3 border-t border-slate-100 dark:border-slate-700">
                            <div>
                                <p class="text-[13px] font-bold text-slate-800 dark:text-slate-200">Izinkan Komentar</p>
                                <p class="text-[11px] font-medium text-slate-400 mt-0.5">Aktifkan fitur komentar di semua postingan</p>
                            </div>
                            <button
                                type="button"
                                @click="form.allow_comments = form.allow_comments === '1' ? '0' : '1'"
                                :class="[
                                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200',
                                    form.allow_comments === '1' ? 'bg-[#2563EB]' : 'bg-slate-200 dark:bg-slate-700'
                                ]"
                            >
                                <span :class="['inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200', form.allow_comments === '1' ? 'translate-x-6' : 'translate-x-1']"></span>
                            </button>
                        </div>

                        <!-- Toggle: Moderate Comments -->
                        <div class="flex items-center justify-between py-3 border-t border-slate-100 dark:border-slate-700">
                            <div>
                                <p class="text-[13px] font-bold text-slate-800 dark:text-slate-200">Moderasi Komentar</p>
                                <p class="text-[11px] font-medium text-slate-400 mt-0.5">Komentar baru perlu persetujuan admin sebelum tampil</p>
                            </div>
                            <button
                                type="button"
                                @click="form.moderate_comments = form.moderate_comments === '1' ? '0' : '1'"
                                :class="[
                                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200',
                                    form.moderate_comments === '1' ? 'bg-[#2563EB]' : 'bg-slate-200 dark:bg-slate-700'
                                ]"
                            >
                                <span :class="['inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200', form.moderate_comments === '1' ? 'translate-x-6' : 'translate-x-1']"></span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT: System Settings -->
            <div class="space-y-5">

                <!-- Info Card -->
                <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 rounded-2xl p-5">
                    <div class="flex items-start gap-3">
                        <SettingsIcon class="w-5 h-5 text-[#2563EB] mt-0.5 shrink-0" />
                        <div>
                            <p class="text-[13px] font-black text-blue-800 dark:text-blue-300">Tips</p>
                            <p class="text-[11px] font-medium text-blue-700 dark:text-blue-400 mt-1">
                                Pengaturan ini berdampak langsung pada postingan berita dan artikel. Selalu simpan setelah perubahan.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </PortalAdminLayout>
</template>
