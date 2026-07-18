<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import {
	FileIcon,
	Image as ImageIcon,
	Loader2,
	Search,
	Trash2,
	Upload,
	X,
} from "lucide-vue-next";
import { computed, ref } from "vue";
import PortalAdminLayout from "@/layouts/PortalAdminLayout.vue";

const props = defineProps({
	media: {
		type: Array as () => any[],
		default: () => [],
	},
});

const searchQuery = ref("");
const filteredMedia = computed(() => {
	if (!searchQuery.value) return props.media;
	return props.media.filter((item) =>
		item.filename.toLowerCase().includes(searchQuery.value.toLowerCase()),
	);
});

const form = useForm({
	files: null as any,
});

const handleFileUpload = (e: any) => {
	form.files = e.target.files;
	form.post("/portal-admin/media", {
		onSuccess: () => {
			form.reset();
		},
	});
};

import DeleteConfirmModal from "@/components/DeleteConfirmModal.vue";

const isDeleteModalOpen = ref(false);
const deleteId = ref<number | null>(null);

const deleteMedia = (id: number) => {
	deleteId.value = id;
	isDeleteModalOpen.value = true;
};

const handleDeleteConfirm = () => {
	if (deleteId.value !== null) {
		form.delete(`/portal-admin/media/${deleteId.value}`, {
			onSuccess: () => {
				isDeleteModalOpen.value = false;
				deleteId.value = null;
			}
		});
	}
};

const formatSize = (bytes: number) => {
	if (!bytes) return "0 B";
	const k = 1024;
	const sizes = ["B", "KB", "MB", "GB"];
	const i = Math.floor(Math.log(bytes) / Math.log(k));
	return `${parseFloat((bytes / k ** i).toFixed(1))} ${sizes[i]}`;
};

const isImage = (mime: string) => mime?.startsWith("image/");
</script>

<template>
    <PortalAdminLayout title="Media Library">
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-[20px] sm:text-[24px] font-black text-slate-900 dark:text-white tracking-tight">Pustaka Media</h2>
                <p class="text-[13px] font-bold text-slate-500 mt-1">Unggah dan kelola semua file aset Anda di sini.</p>
            </div>
            <label class="bg-[#2563EB] hover:bg-blue-600 text-white px-6 py-3 rounded-[14px] text-[13px] font-black shadow-lg shadow-blue-500/20 transition-all flex items-center justify-center gap-2 cursor-pointer w-full sm:w-auto active:scale-95 shrink-0">
                <Upload class="w-4 h-4" />
                Unggah File
                <input type="file" class="hidden" multiple @change="handleFileUpload" />
            </label>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-[1.25rem] shadow-sm border border-slate-100 dark:border-slate-700 p-6">
            <!-- Toolbar -->
            <div class="flex flex-col sm:flex-row items-center gap-4 mb-8">
                <div class="relative w-full sm:max-w-md">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <input v-model="searchQuery" type="text" placeholder="Cari file..." class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-xl pl-10 pr-4 py-3 text-[13px] font-bold focus:ring-2 focus:ring-blue-500 transition-all" />
                </div>
            </div>

            <!-- Grid -->
            <div v-if="filteredMedia.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-6">
                <div v-for="item in filteredMedia" :key="item.id" class="group relative bg-slate-50 dark:bg-slate-900 rounded-2xl overflow-hidden border border-slate-100 dark:border-slate-800 transition-all hover:shadow-xl hover:-translate-y-1">
                    <div class="aspect-square flex items-center justify-center overflow-hidden">
                        <img v-if="isImage(item.mime_type)" :src="item.path" :alt="item.filename" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
                        <FileIcon v-else class="w-12 h-12 text-slate-300" />
                    </div>
                    <div class="p-3 bg-white dark:bg-slate-800 border-t border-slate-100 dark:border-slate-800">
                        <p class="text-[11px] font-black text-slate-800 dark:text-slate-200 truncate" :title="item.filename">{{ item.filename }}</p>
                        <p class="text-[10px] font-bold text-slate-400 mt-0.5">{{ formatSize(item.size) }}</p>
                    </div>

                    <!-- Overlay Actions -->
                    <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                        <button @click="deleteMedia(item.id)" class="p-2 bg-white/20 backdrop-blur-md text-white rounded-full hover:bg-red-500 transition-colors shadow-lg">
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
            
            <div v-else class="py-20 text-center">
                <ImageIcon class="w-12 h-12 text-slate-200 mx-auto mb-4" />
                <p class="text-slate-500 text-[14px] font-bold">Tidak ada file media</p>
                <p class="text-slate-400 text-[12px] mt-1">Unggah beberapa file untuk melihatnya di sini.</p>
            </div>
        </div>
        <DeleteConfirmModal
            :show="isDeleteModalOpen"
            title="Hapus Media"
            message="Apakah Anda yakin ingin menghapus file media ini secara permanen dari server? File yang digunakan di postingan mungkin akan rusak."
            @confirm="handleDeleteConfirm"
            @cancel="isDeleteModalOpen = false"
        />
    </PortalAdminLayout>
</template>
