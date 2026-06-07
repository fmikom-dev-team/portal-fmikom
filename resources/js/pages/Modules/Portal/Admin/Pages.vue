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

const deletePage = (id: number) => {
	if (confirm("Hapus halaman ini?")) form.delete(`/portal-admin/pages/${id}`);
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

        <!-- Edit/Create Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="closeModal"></div>
            <div class="relative bg-white dark:bg-slate-900 rounded-2xl w-full max-w-2xl shadow-2xl overflow-y-auto max-h-[90vh] border border-slate-100 dark:border-slate-800">
                <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between sticky top-0 bg-white dark:bg-slate-900 z-10">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white">{{ editingPage ? 'Edit Halaman' : 'Buat Halaman Baru' }}</h3>
                    <button @click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"><X class="w-5 h-5" /></button>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Judul Halaman</label>
                            <input v-model="form.title" type="text" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 focus:ring-blue-600" required />
                            <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Slug (URL)</label>
                            <input v-model="form.slug" type="text" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 focus:ring-blue-600 font-mono text-sm" required />
                            <p v-if="form.errors.slug" class="text-red-500 text-xs mt-1">{{ form.errors.slug }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Kategori / Folder</label>
                            <select v-model="form.category" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 focus:ring-blue-600">
                                <option value="">-- Pilih Kategori --</option>
                                <option v-for="cat in categories" :key="cat" :value="cat">{{ categoryMeta[cat]?.label || cat }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Template</label>
                            <select v-model="form.template" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 focus:ring-blue-600">
                                <option value="default">Default (Centered)</option>
                                <option value="sidebar">Dengan Sidebar</option>
                                <option value="full-width">Full Width</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Ringkasan (Excerpt)</label>
                        <textarea v-model="form.excerpt" rows="2" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 focus:ring-blue-600 resize-none" placeholder="Ringkasan singkat halaman ini..."></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Meta Description (SEO)</label>
                        <input v-model="form.meta_description" type="text" maxlength="255" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 focus:ring-blue-600" placeholder="Max 255 karakter untuk SEO" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Konten</label>
                        <textarea v-model="form.content" rows="10" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 focus:ring-blue-600 resize-none font-mono text-sm" placeholder="Konten halaman (HTML atau teks)..."></textarea>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" v-model="form.is_published" id="is_published" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                        <label for="is_published" class="text-sm font-semibold text-slate-700 dark:text-slate-300">Publikasikan halaman</label>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="closeModal" class="flex-1 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-200 transition-all">Batal</button>
                        <button type="submit" :disabled="form.processing" class="flex-1 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 disabled:opacity-50 transition-all flex items-center justify-center gap-2 active:scale-95">
                            <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PortalAdminLayout>
</template>
