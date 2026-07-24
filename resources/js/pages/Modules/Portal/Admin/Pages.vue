<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import {
	ChevronDown,
	Edit,
	Eye,
	FileText,
	FolderOpen,
	Globe,
	Loader2,
	Plus,
	Trash2,
	X,
} from "lucide-vue-next";
import { computed, ref, watch } from "vue";
import PortalAdminLayout from "@/layouts/PortalAdminLayout.vue";
import DeleteConfirmModal from "@/components/DeleteConfirmModal.vue";

const props = defineProps({
	pages: { type: Array as () => any[], default: () => [] },
	categories: { type: Array as () => string[], default: () => [] },
});

const categoryMeta: Record<string, { label: string; color: string }> = {
	profil: { label: "Profil", color: "bg-blue-100 text-blue-700" },
	akademik: { label: "Akademik", color: "bg-emerald-100 text-emerald-700" },
	media: { label: "Berita & Media", color: "bg-violet-100 text-violet-700" },
	layanan: { label: "Layanan", color: "bg-orange-100 text-orange-700" },
	system: { label: "System", color: "bg-slate-100 text-slate-600" },
};

// Group pages by category
const grouped = computed(() => {
	const groups: Record<string, any[]> = {};
	for (const page of props.pages) {
		const cat = page.category || "system";
		if (!groups[cat]) groups[cat] = [];
		groups[cat].push(page);
	}
	return groups;
});

const isModalOpen = ref(false);
const editingPage = ref<any>(null);
const activeFolder = ref<string | null>(null);
const expandedFolders = ref<Record<string, boolean>>({});

const toggleFolder = (cat: string) => {
	expandedFolders.value[cat] = !expandedFolders.value[cat];
};

const form = useForm({
	title: "",
	slug: "",
	content: "",
	excerpt: "",
	meta_description: "",
	category: "",
	template: "default",
	is_published: true,
});

watch(
	() => form.title,
	(newTitle) => {
		if (!editingPage.value) {
			form.slug = newTitle
				.toLowerCase()
				.replace(/[^\w\s-]/g, "")
				.replace(/[\s_-]+/g, "-")
				.replace(/^-+|-+$/g, "");
		}
	},
);

const openModal = (page: any = null) => {
	editingPage.value = page;
	if (page) {
		form.title = page.title;
		form.slug = page.slug;
		form.content = page.content || "";
		form.excerpt = page.excerpt || "";
		form.meta_description = page.meta_description || "";
		form.category = page.category || "";
		form.template = page.template || "default";
		form.is_published = !!page.is_published;
	} else {
		form.reset();
		form.is_published = true;
		if (activeFolder.value) form.category = activeFolder.value;
	}
	isModalOpen.value = true;
};

const closeModal = () => {
	isModalOpen.value = false;
	editingPage.value = null;
	form.reset();
};

const submit = () => {
	if (editingPage.value) {
		form.put(`/portal-admin/pages/${editingPage.value.id}`, {
			onSuccess: () => closeModal(),
		});
	} else {
		form.post("/portal-admin/pages", {
			onSuccess: () => {
				if (form.category) {
					expandedFolders.value[form.category] = true;
				}
				closeModal();
			},
		});
	}
};

const isDeleteModalOpen = ref(false);
const deletePageId = ref<number | null>(null);

const deletePage = (id: number) => {
	deletePageId.value = id;
	isDeleteModalOpen.value = true;
};

const handleDeletePage = () => {
	if (deletePageId.value !== null) {
		form.delete(`/portal-admin/pages/${deletePageId.value}`, {
			onSuccess: () => {
				isDeleteModalOpen.value = false;
				deletePageId.value = null;
			},
		});
	}
};
</script>

<template>
    <PortalAdminLayout title="Kelola Halaman">
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Halaman Portal</h2>
                <p class="text-sm font-medium text-slate-500 mt-1">Kelola semua halaman statis — dikelompokkan per folder kategori.</p>
            </div>
            <button @click="openModal()" class="inline-flex items-center gap-2 bg-[#2563EB] hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-blue-500/20 transition-all active:scale-95 shrink-0">
                <Plus class="w-4 h-4" /> Buat Halaman
            </button>
        </div>

        <!-- Category Folders -->
        <div class="grid grid-cols-1 gap-8">
            <div v-for="(pagesInCat, cat) in grouped" :key="cat" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                <!-- Folder Header -->
                <div class="flex items-center justify-between px-6 py-4 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-700 cursor-pointer select-none" @click="toggleFolder(cat as string)">
                    <div class="flex items-center gap-3">
                        <FolderOpen class="w-5 h-5 text-slate-400" />
                        <h3 class="font-bold text-slate-800 dark:text-white text-base">{{ categoryMeta[cat]?.label || cat }}</h3>
                        <span :class="['text-xs font-bold px-2 py-0.5 rounded-full', categoryMeta[cat]?.color || 'bg-slate-100 text-slate-500']">{{ pagesInCat.length }} halaman</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <button @click.stop="activeFolder = cat as string; openModal()" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 bg-blue-50 dark:bg-blue-950/40 px-2.5 py-1.5 rounded-lg border border-blue-100/50 dark:border-blue-900/30 transition-all hover:scale-105 active:scale-95">
                            <Plus class="w-3.5 h-3.5" /> Tambah
                        </button>
                        <ChevronDown class="w-4 h-4 text-slate-400 transition-transform duration-300 shrink-0" :class="expandedFolders[cat] ? 'rotate-180' : ''" />
                    </div>
                </div>

                <!-- Pages List -->
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="transform opacity-0 -translate-y-2 scale-95"
                    enter-to-class="transform opacity-100 translate-y-0 scale-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="transform opacity-100 translate-y-0 scale-100"
                    leave-to-class="transform opacity-0 -translate-y-2 scale-95"
                >
                    <div v-show="expandedFolders[cat]" class="divide-y divide-slate-100 dark:divide-slate-800">
                        <div v-for="p in pagesInCat" :key="p.id" class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <FileText class="w-5 h-5 text-slate-300 shrink-0" />
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-800 dark:text-white text-sm truncate">{{ p.title }}</p>
                                    <a :href="`/halaman/${p.slug}`" target="_blank" class="text-xs text-slate-400 hover:text-blue-500 font-mono">/halaman/{{ p.slug }}</a>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0 ml-4">
                                <span :class="[p.is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500', 'text-xs font-bold px-2 py-0.5 rounded-full hidden sm:block']">
                                    {{ p.is_published ? 'Aktif' : 'Draft' }}
                                </span>
                                <a :href="`/halaman/${p.slug}`" target="_blank" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Preview">
                                    <Eye class="w-4 h-4" />
                                </a>
                                <button @click="openModal(p)" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors">
                                    <Edit class="w-4 h-4" />
                                </button>
                                <button @click="deletePage(p.id)" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>

        <!-- ============ MODERN EDIT/CREATE MODAL ============ -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeModal"></div>

                <!-- Modal Panel -->
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="translate-y-full sm:translate-y-0 sm:scale-95 opacity-0"
                    enter-to-class="translate-y-0 sm:scale-100 opacity-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="translate-y-0 sm:scale-100 opacity-100"
                    leave-to-class="translate-y-full sm:translate-y-0 sm:scale-95 opacity-0"
                    appear
                >
                    <div class="relative bg-white dark:bg-slate-900 w-full sm:max-w-2xl rounded-t-3xl sm:rounded-2xl shadow-2xl border-0 sm:border border-slate-200 dark:border-slate-700 overflow-hidden flex flex-col max-h-[95dvh]">

                        <!-- Modal Header -->
                        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 dark:border-slate-800 shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/40 flex items-center justify-center">
                                    <FileText class="w-4.5 h-4.5 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div>
                                    <h3 class="text-base font-black text-slate-900 dark:text-white leading-none">
                                        {{ editingPage ? 'Edit Halaman' : 'Buat Halaman Baru' }}
                                    </h3>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ editingPage ? 'Perbarui informasi halaman ini' : 'Isi detail untuk halaman baru' }}</p>
                                </div>
                            </div>
                            <button @click="closeModal" class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                                <X class="w-4 h-4" />
                            </button>
                        </div>

                        <!-- Modal Body (Scrollable) -->
                        <form @submit.prevent="submit" class="overflow-y-auto flex-1">
                            <div class="px-6 py-5 space-y-5">

                                <!-- Judul + Slug Row -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                                            Judul Halaman <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input
                                                v-model="form.title"
                                                type="text"
                                                placeholder="Mis: Tentang Kami"
                                                required
                                                class="w-full px-4 py-2.5 rounded-xl border text-sm font-medium transition-all outline-none"
                                                :class="form.errors.title
                                                    ? 'border-red-400 dark:border-red-500 bg-red-50 dark:bg-red-950/20 text-red-700 placeholder-red-300 focus:ring-2 focus:ring-red-400/30'
                                                    : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20'"
                                            />
                                        </div>
                                        <p v-if="form.errors.title" class="text-red-500 text-xs flex items-center gap-1">
                                            <span class="inline-block w-1 h-1 rounded-full bg-red-500"></span>{{ form.errors.title }}
                                        </p>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                                            Slug (URL) <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative flex items-center">
                                            <span class="absolute left-3 text-xs text-slate-400 font-mono select-none">/</span>
                                            <input
                                                v-model="form.slug"
                                                type="text"
                                                placeholder="tentang-kami"
                                                required
                                                class="w-full pl-6 pr-4 py-2.5 rounded-xl border text-sm font-mono transition-all outline-none"
                                                :class="form.errors.slug
                                                    ? 'border-red-400 dark:border-red-500 bg-red-50 dark:bg-red-950/20 text-red-700 placeholder-red-300 focus:ring-2 focus:ring-red-400/30'
                                                    : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20'"
                                            />
                                        </div>
                                        <p v-if="form.errors.slug" class="text-red-500 text-xs flex items-center gap-1">
                                            <span class="inline-block w-1 h-1 rounded-full bg-red-500"></span>{{ form.errors.slug }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Kategori + Template Row -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Kategori / Folder</label>
                                        <div class="relative">
                                            <select
                                                v-model="form.category"
                                                class="w-full appearance-none px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-800 dark:text-slate-100 transition-all outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 pr-9"
                                            >
                                                <option value="">— Pilih Kategori —</option>
                                                <option v-for="cat in categories" :key="cat" :value="cat">{{ categoryMeta[cat]?.label || cat }}</option>
                                            </select>
                                            <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                                        </div>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Template Layout</label>
                                        <div class="relative">
                                            <select
                                                v-model="form.template"
                                                class="w-full appearance-none px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-800 dark:text-slate-100 transition-all outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 pr-9"
                                            >
                                                <option value="default">Default (Centered)</option>
                                                <option value="sidebar">Dengan Sidebar</option>
                                                <option value="full-width">Full Width</option>
                                            </select>
                                            <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Excerpt -->
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Ringkasan (Excerpt)</label>
                                    <textarea
                                        v-model="form.excerpt"
                                        rows="2"
                                        placeholder="Ringkasan singkat halaman ini yang ditampilkan di listing..."
                                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 transition-all outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 resize-none"
                                    ></textarea>
                                </div>

                                <!-- Meta Description with character counter -->
                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Meta Description (SEO)</label>
                                        <span class="text-xs font-mono" :class="(form.meta_description?.length || 0) > 255 ? 'text-red-500' : (form.meta_description?.length || 0) > 200 ? 'text-amber-500' : 'text-slate-400'">
                                            {{ form.meta_description?.length || 0 }}/255
                                        </span>
                                    </div>
                                    <input
                                        v-model="form.meta_description"
                                        type="text"
                                        maxlength="255"
                                        placeholder="Deskripsi singkat untuk mesin pencari (maks. 255 karakter)..."
                                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 transition-all outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                    />
                                    <!-- SEO Strength Bar -->
                                    <div class="h-1 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                        <div
                                            class="h-full rounded-full transition-all duration-300"
                                            :class="(form.meta_description?.length || 0) >= 120 && (form.meta_description?.length || 0) <= 200
                                                ? 'bg-emerald-400'
                                                : (form.meta_description?.length || 0) > 0
                                                ? 'bg-amber-400'
                                                : 'bg-transparent'"
                                            :style="{ width: `${Math.min(((form.meta_description?.length || 0) / 255) * 100, 100)}%` }"
                                        ></div>
                                    </div>
                                    <p class="text-[11px] text-slate-400">Ideal: 120–200 karakter. Ditampilkan di Google Search.</p>
                                </div>

                                <!-- Content Textarea -->
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Konten Halaman</label>
                                    <textarea
                                        v-model="form.content"
                                        rows="10"
                                        placeholder="Konten halaman dalam format HTML atau teks biasa..."
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono text-slate-800 dark:text-slate-100 placeholder-slate-400 transition-all outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 resize-y min-h-[180px]"
                                    ></textarea>
                                </div>

                                <!-- Publikasikan Toggle -->
                                <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-700">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center" :class="form.is_published ? 'bg-emerald-100 dark:bg-emerald-900/40' : 'bg-slate-200 dark:bg-slate-700'">
                                            <Globe class="w-4 h-4" :class="form.is_published ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400'" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-800 dark:text-white">Publikasikan Halaman</p>
                                            <p class="text-xs text-slate-400">{{ form.is_published ? 'Halaman ini akan tampil ke publik' : 'Tersimpan sebagai draft (tidak publik)' }}</p>
                                        </div>
                                    </div>
                                    <!-- Toggle Switch -->
                                    <button
                                        type="button"
                                        @click="form.is_published = !form.is_published"
                                        class="relative inline-flex w-11 h-6 shrink-0 rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none cursor-pointer"
                                        :class="form.is_published ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-600'"
                                        :aria-pressed="form.is_published"
                                    >
                                        <span
                                            class="pointer-events-none inline-block w-5 h-5 transform rounded-full bg-white shadow-lg ring-0 transition-transform duration-200"
                                            :class="form.is_published ? 'translate-x-5' : 'translate-x-0'"
                                        ></span>
                                    </button>
                                </div>
                            </div>

                            <!-- Modal Footer (sticky) -->
                            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 flex gap-3 shrink-0">
                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="flex-1 sm:flex-none sm:w-32 py-2.5 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-all active:scale-95"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed shadow-lg shadow-blue-500/20"
                                >
                                    <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                                    <span>{{ form.processing ? 'Menyimpan...' : (editingPage ? 'Simpan Perubahan' : 'Buat Halaman') }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </Transition>
            </div>
        </Transition>

        <DeleteConfirmModal
            :show="isDeleteModalOpen"
            title="Hapus Halaman"
            message="Apakah Anda yakin ingin menghapus halaman ini? Halaman yang dihapus tidak dapat dipulihkan dan link menu yang mengarah ke halaman ini akan rusak."
            @confirm="handleDeletePage"
            @cancel="isDeleteModalOpen = false"
        />
    </PortalAdminLayout>
</template>

