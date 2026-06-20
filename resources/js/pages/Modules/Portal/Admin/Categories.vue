<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import {
	Edit,
	Loader2,
	MoreHorizontal,
	Plus,
	Trash2,
	X,
} from "lucide-vue-next";
import { ref, watch } from "vue";
import PortalAdminLayout from "@/layouts/PortalAdminLayout.vue";

const props = defineProps({
	categories: {
		type: Array as () => any[],
		default: () => [],
	},
});

const isModalOpen = ref(false);
const editingCategory = ref<any>(null);

const form = useForm({
	name: "",
	slug: "",
	description: "",
});

watch(
	() => form.name,
	(newName) => {
		if (!editingCategory.value) {
			form.slug = newName
				.toLowerCase()
				.replace(/[^\w\s-]/g, "")
				.replace(/[\s_-]+/g, "-")
				.replace(/^-+|-+$/g, "");
		}
	},
);

const openModal = (category: any = null) => {
	editingCategory.value = category;
	if (category) {
		form.name = category.name;
		form.slug = category.slug;
		form.description = category.description || "";
	} else {
		form.reset();
	}
	isModalOpen.value = true;
};

const closeModal = () => {
	isModalOpen.value = false;
	editingCategory.value = null;
	form.reset();
};

const submit = () => {
	if (editingCategory.value) {
		form.put(`/portal-admin/categories/${editingCategory.value.id}`, {
			onSuccess: () => closeModal(),
		});
	} else {
		form.post("/portal-admin/categories", {
			onSuccess: () => closeModal(),
		});
	}
};

const deleteCategory = (id: number) => {
	if (confirm("Apakah Anda yakin ingin menghapus kategori ini?")) {
		form.delete(`/portal-admin/categories/${id}`);
	}
};
</script>

<template>
    <PortalAdminLayout title="Kelola Kategori">
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-[20px] sm:text-[24px] font-black text-slate-900 dark:text-white tracking-tight">Kategori Berita</h2>
                <p class="text-[13px] font-bold text-slate-500 mt-1">Organisir postingan Anda ke dalam berbagai kategori.</p>
            </div>
            <button @click="openModal()" class="bg-[#2563EB] hover:bg-blue-600 text-white px-6 py-3 rounded-[14px] text-[13px] font-black shadow-lg shadow-blue-500/20 transition-all flex items-center justify-center gap-2 w-full sm:w-auto active:scale-95 shrink-0">
                <Plus class="w-4 h-4" />
                Tambah Kategori
            </button>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-[1.25rem] shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <caption class="sr-only">Daftar Kategori</caption>
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700">
                            <th class="py-4 px-4 sm:px-6 text-[12px] font-black tracking-wider text-slate-400 uppercase">Informasi</th>
                            <th class="hidden sm:table-cell py-4 px-6 text-[12px] font-black tracking-wider text-slate-400 uppercase">Slug</th>
                            <th class="hidden md:table-cell py-4 px-6 text-[12px] font-black tracking-wider text-slate-400 uppercase">Jumlah Post</th>
                            <th class="py-4 px-4 sm:px-6 text-[12px] font-black tracking-wider text-slate-400 uppercase text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="category in categories" :key="category.id" class="border-b border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-900/30 transition-colors">
                            <td class="py-4 px-4 sm:px-6">
                                <p class="text-[14px] font-bold text-slate-800 dark:text-slate-200">{{ category.name }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <p class="sm:hidden text-[11px] font-bold text-blue-500">{{ category.posts_count || 0 }} Post</p>
                                    <p v-if="category.description" class="text-[12px] text-slate-400 truncate max-w-xs">{{ category.description }}</p>
                                </div>
                            </td>
                            <td class="hidden sm:table-cell py-4 px-6">
                                <code class="text-[12px] font-bold bg-slate-100 dark:bg-slate-700 px-2 py-1 rounded text-slate-600 dark:text-slate-300">{{ category.slug }}</code>
                            </td>
                            <td class="hidden md:table-cell py-4 px-6">
                                <span class="bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-[11px] font-black px-2.5 py-1 rounded-full">
                                    {{ category.posts_count || 0 }} Post
                                </span>
                            </td>
                            <td class="py-4 px-4 sm:px-6">
                                <div class="flex justify-end gap-1 sm:gap-2">
                                    <button @click="openModal(category)" class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors">
                                        <Edit class="w-4 h-4" />
                                    </button>
                                    <button @click="deleteCategory(category.id)" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="categories.length === 0">
                            <td colspan="4" class="py-16 text-center">
                                <p class="text-slate-500 text-[14px] font-bold">Belum ada kategori</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="closeModal"></div>
            <div class="relative bg-white dark:bg-slate-800 rounded-[2rem] w-full max-w-md shadow-2xl overflow-hidden border border-slate-100 dark:border-slate-700">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="text-[16px] font-black text-slate-900 dark:text-white uppercase tracking-wider">
                        {{ editingCategory ? 'Edit Kategori' : 'Tambah Kategori Baru' }}
                    </h3>
                    <button @click="closeModal" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-5">
                    <div class="space-y-2">
                        <label class="text-[12px] font-black text-slate-400 uppercase tracking-widest">Nama Kategori</label>
                        <input v-model="form.name" type="text" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-xl text-[14px] font-bold p-3.5 focus:ring-2 focus:ring-blue-500 transition-all" placeholder="E.g. Pengumuman" required />
                        <p v-if="form.errors.name" class="text-red-500 text-[11px] font-bold">{{ form.errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[12px] font-black text-slate-400 uppercase tracking-widest">Slug (URL)</label>
                        <input v-model="form.slug" type="text" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-xl text-[14px] font-bold p-3.5 focus:ring-2 focus:ring-blue-500 transition-all" placeholder="e-g-pengumuman" required />
                        <p v-if="form.errors.slug" class="text-red-500 text-[11px] font-bold">{{ form.errors.slug }}</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[12px] font-black text-slate-400 uppercase tracking-widest">Deskripsi (Opsional)</label>
                        <textarea v-model="form.description" rows="3" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-xl text-[14px] font-bold p-3.5 focus:ring-2 focus:ring-blue-500 transition-all resize-none" placeholder="Deskripsi singkat..."></textarea>
                    </div>
                    
                    <div class="pt-4 flex flex-col sm:flex-row gap-2 sm:gap-3">
                        <button type="button" @click="closeModal" class="px-4 py-3 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-[13px] font-bold hover:bg-slate-200 transition-all sm:flex-1 order-2 sm:order-1">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-3 bg-[#2563EB] text-white rounded-xl text-[13px] font-black shadow-lg shadow-blue-500/20 hover:bg-blue-600 disabled:opacity-50 transition-all flex items-center justify-center gap-2 sm:flex-1 order-1 sm:order-2 active:scale-95">
                            <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PortalAdminLayout>
</template>
