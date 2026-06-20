<script setup lang="ts">
import { Head, useForm, Link, router } from "@inertiajs/vue3";
import {
	Plus,
	Edit,
	Trash2,
	Folder,
	FileText,
	Pin,
	Search,
	X,
	Loader2,
	Download,
	File,
} from "lucide-vue-next";
import { ref, watch, computed } from "vue";
import PortalAdminLayout from "@/layouts/PortalAdminLayout.vue";

const props = defineProps({
	documents: {
		type: Object,
		default: () => ({ data: [] }),
	},
	categories: {
		type: Array as () => string[],
		default: () => [],
	},
	filters: {
		type: Object,
		default: () => ({ search: "", category: "" }),
	},
});

const isModalOpen = ref(false);
const editingDocument = ref<any>(null);
const localSearch = ref(props.filters.search || "");
const selectedCategory = ref(props.filters.category || "");

// watch filters to reload from server
watch([localSearch, selectedCategory], ([newSearch, newCat]) => {
	router.get(
		"/portal-admin/documents",
		{ search: newSearch, category: newCat },
		{ preserveState: true, replace: true }
	);
});

const form = useForm({
	_method: "POST",
	title: "",
	description: "",
	category: "",
	is_pinned: false,
	file: null as File | null,
});

const openModal = (document: any = null) => {
	editingDocument.value = document;
	if (document) {
		form.title = document.title;
		form.description = document.description || "";
		form.category = document.category || "";
		form.is_pinned = !!document.is_pinned;
		form.file = null;
		form._method = "PUT";
	} else {
		form.reset();
		form.file = null;
		form._method = "POST";
	}
	isModalOpen.value = true;
};

const closeModal = () => {
	isModalOpen.value = false;
	editingDocument.value = null;
	form.reset();
};

const handleFileChange = (e: any) => {
	const file = e.target.files[0];
	if (file) {
		form.file = file;
	}
};

const submit = () => {
	if (editingDocument.value) {
		// Laravel requires POST with _method = PUT for multipart/form-data updates
		form.post(`/portal-admin/documents/${editingDocument.value.id}`, {
			onSuccess: () => closeModal(),
		});
	} else {
		form.post("/portal-admin/documents", {
			onSuccess: () => closeModal(),
		});
	}
};

const deleteDocument = (id: number) => {
	if (confirm("Apakah Anda yakin ingin menghapus dokumen ini secara permanen dari server?")) {
		router.delete(`/portal-admin/documents/${id}`);
	}
};

const togglePin = (id: number) => {
	router.post(`/portal-admin/documents/${id}/toggle-pin`);
};

const formatSize = (bytes: number) => {
	if (!bytes) return "0 B";
	const k = 1024;
	const sizes = ["B", "KB", "MB", "GB"];
	const i = Math.floor(Math.log(bytes) / Math.log(k));
	return `${parseFloat((bytes / k ** i).toFixed(2))} ${sizes[i]}`;
};
</script>

<template>
    <PortalAdminLayout title="Kelola Dokumen & Arsip">
        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                    Dokumen & Arsip
                    <span class="text-xs font-bold text-slate-400">({{ documents.total || 0 }})</span>
                </h2>
                <p class="text-[12px] font-bold text-slate-500 mt-1">
                    Unggah dan kelola berkas resmi, pengumuman, panduan, atau formulir kampus.
                </p>
            </div>
            
            <button @click="openModal()" class="bg-[#2563EB] hover:bg-blue-600 text-white px-4 py-2.5 rounded-xl text-xs font-bold shadow-sm transition-all flex items-center justify-center gap-2 active:scale-95 shrink-0 select-none cursor-pointer">
                <Plus class="w-4 h-4" />
                Unggah Dokumen
            </button>
        </div>

        <!-- Toolbar: Search & Filter -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 border border-slate-150 dark:border-slate-800 shadow-sm mb-4 flex flex-col sm:flex-row gap-3 items-center justify-between">
            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                <!-- Category buttons -->
                <button 
                    @click="selectedCategory = ''"
                    :class="[
                        selectedCategory === '' 
                            ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-bold' 
                            : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 font-semibold',
                        'px-3 py-1.5 rounded-xl text-xs transition-all whitespace-nowrap cursor-pointer'
                    ]"
                >
                    Semua
                </button>
                <button 
                    v-for="cat in categories" 
                    :key="cat"
                    @click="selectedCategory = cat"
                    :class="[
                        selectedCategory === cat 
                            ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-bold' 
                            : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 font-semibold',
                        'px-3 py-1.5 rounded-xl text-xs transition-all whitespace-nowrap cursor-pointer'
                    ]"
                >
                    {{ cat }}
                </button>
            </div>

            <!-- Compact Search -->
            <div class="relative w-full sm:w-[260px] shrink-0">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <Search class="w-3.5 h-3.5" />
                </span>
                <input 
                    v-model="localSearch"
                    type="text" 
                    placeholder="Telusuri dokumen..."
                    class="w-full pl-9 pr-3 py-2 rounded-xl text-xs font-semibold bg-slate-50 dark:bg-slate-900 border border-slate-150 dark:border-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-all placeholder-slate-400 text-slate-700 dark:text-white"
                />
            </div>
        </div>

        <!-- List Layout -->
        <div class="space-y-2">
            <div 
                v-for="doc in documents.data" 
                :key="doc.id" 
                class="group bg-white dark:bg-slate-800 rounded-xl p-3 border border-slate-150 dark:border-slate-800 hover:border-slate-350 dark:hover:border-slate-700 hover:shadow-sm transition-all flex items-center gap-4"
            >
                <!-- File Icon -->
                <div class="shrink-0">
                    <div class="w-12 h-12 rounded-lg bg-blue-50 dark:bg-blue-900/10 text-blue-600 dark:text-blue-450 flex items-center justify-center border border-blue-100/35">
                        <FileText class="w-6 h-6" />
                    </div>
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white truncate leading-snug group-hover:text-blue-600 transition-colors">
                            {{ doc.title }}
                        </h3>
                        <span v-if="doc.is_pinned" class="bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 text-[9px] font-black px-1.5 py-0.5 rounded flex items-center gap-0.5 shrink-0 border border-amber-100 dark:border-amber-900/35">
                            <Pin class="w-2.5 h-2.5" />
                            Penting
                        </span>
                    </div>
                    <p v-if="doc.description" class="text-[11px] text-slate-400 dark:text-slate-500 truncate mb-1">{{ doc.description }}</p>

                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[10.5px] text-slate-400 dark:text-slate-500 font-medium">
                        <span v-if="doc.category" class="bg-slate-100 dark:bg-slate-900/80 px-1.5 py-0.5 rounded text-[10px] text-slate-500 dark:text-slate-405 font-bold">
                            {{ doc.category }}
                        </span>
                        <span v-if="doc.category">•</span>
                        <span class="truncate max-w-[150px]" :title="doc.file_name">{{ doc.file_name }}</span>
                        <span>•</span>
                        <span>{{ formatSize(doc.file_size) }}</span>
                        <span>•</span>
                        <span class="flex items-center gap-1">
                            <Download class="w-3 h-3" /> {{ doc.download_count }} unduhan
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-1 shrink-0">
                    <button 
                        @click="togglePin(doc.id)"
                        class="w-8 h-8 flex items-center justify-center rounded-lg transition-all"
                        :class="[
                            doc.is_pinned 
                                ? 'text-amber-500 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30' 
                                : 'text-slate-400 hover:text-slate-655 hover:bg-slate-100 dark:hover:bg-slate-700'
                        ]"
                        title="Pasang Pengumuman"
                    >
                        <Pin class="w-4 h-4" />
                    </button>
                    <button 
                        @click="openModal(doc)"
                        class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-all"
                        title="Edit Dokumen"
                    >
                        <Edit class="w-4 h-4" />
                    </button>
                    <button 
                        @click="deleteDocument(doc.id)"
                        class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-all"
                        title="Hapus Dokumen"
                    >
                        <Trash2 class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div 
                v-if="documents.data.length === 0" 
                class="py-16 text-center bg-white dark:bg-slate-800 rounded-xl border border-dashed border-slate-200 dark:border-slate-700"
            >
                <Folder class="w-8 h-8 text-slate-300 mx-auto mb-2 opacity-55" />
                <p class="text-slate-800 dark:text-white text-xs font-bold">Tidak ada dokumen ditemukan</p>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="documents.links && documents.links.length > 3" class="mt-6 flex items-center justify-between border-t border-slate-150 dark:border-slate-700 px-4 py-3 sm:px-6 bg-white dark:bg-slate-800 rounded-xl shadow-sm">
            <div class="flex flex-1 justify-between sm:hidden">
                <Link :href="documents.prev_page_url || '#'" :class="[!documents.prev_page_url ? 'opacity-50 pointer-events-none' : '', 'relative inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-medium text-slate-750 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-250']">Previous</Link>
                <Link :href="documents.next_page_url || '#'" :class="[!documents.next_page_url ? 'opacity-50 pointer-events-none' : '', 'relative ml-3 inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-medium text-slate-750 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-250']">Next</Link>
            </div>
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Showing
                        <span class="font-bold">{{ documents.from || 0 }}</span>
                        to
                        <span class="font-bold">{{ documents.to || 0 }}</span>
                        of
                        <span class="font-bold">{{ documents.total || 0 }}</span>
                        results
                    </p>
                </div>
                <div>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm bg-white dark:bg-slate-800" aria-label="Pagination">
                        <Link
                            v-for="(link, i) in documents.links"
                            :key="i"
                            :href="link.url || '#'"
                            :class="[
                                link.active ? 'z-10 bg-blue-600 text-white' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700',
                                !link.url ? 'opacity-50 pointer-events-none' : '',
                                'relative inline-flex items-center px-3 py-2 text-xs font-bold ring-1 ring-inset ring-slate-200 dark:ring-slate-700 focus:z-20 transition-all'
                            ]"
                            v-html="link.label"
                        />
                    </nav>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="closeModal"></div>
            <div class="relative bg-white dark:bg-slate-800 rounded-[2rem] w-full max-w-md shadow-2xl overflow-hidden border border-slate-100 dark:border-slate-700 animate-in zoom-in-95 duration-200">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="text-[14px] font-black text-slate-900 dark:text-white uppercase tracking-wider">
                        {{ editingDocument ? 'Edit Dokumen' : 'Unggah Dokumen Baru' }}
                    </h3>
                    <button @click="closeModal" class="text-slate-400 hover:text-slate-650 transition-colors cursor-pointer">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Nama / Judul Dokumen</label>
                        <input v-model="form.title" type="text" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-xl text-[13px] font-bold p-3.5 focus:ring-2 focus:ring-blue-500 transition-all text-slate-850 dark:text-slate-200" placeholder="E.g. Panduan KRS Mahasiswa 2026" required />
                        <p v-if="form.errors.title" class="text-red-500 text-[10px] font-bold">{{ form.errors.title }}</p>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Kategori</label>
                        <select v-model="form.category" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-xl text-[13px] font-bold p-3.5 focus:ring-2 focus:ring-blue-500 transition-all text-slate-850 dark:text-slate-200">
                            <option value="">Pilih Kategori</option>
                            <option value="Pengumuman">Pengumuman</option>
                            <option value="Formulir">Formulir</option>
                            <option value="Panduan">Panduan</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                        <p v-if="form.errors.category" class="text-red-500 text-[10px] font-bold">{{ form.errors.category }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Deskripsi Singkat (Opsional)</label>
                        <textarea v-model="form.description" rows="3" class="w-full bg-slate-50 dark:bg-slate-900 border-none rounded-xl text-[13px] font-bold p-3.5 focus:ring-2 focus:ring-blue-500 transition-all resize-none text-slate-850 dark:text-slate-200" placeholder="Deskripsi isi file..."></textarea>
                        <p v-if="form.errors.description" class="text-red-500 text-[10px] font-bold">{{ form.errors.description }}</p>
                    </div>

                    <!-- Pinned Toggle -->
                    <div class="flex items-center gap-3 py-1">
                        <input type="checkbox" id="is_pinned" v-model="form.is_pinned" class="rounded border-slate-200 dark:border-slate-700 text-[#2563EB] focus:ring-[#2563EB]" />
                        <label for="is_pinned" class="text-[11px] font-bold text-slate-605 dark:text-slate-400 select-none cursor-pointer">Sematkan sebagai Berkas Penting (Tampil di Atas)</label>
                    </div>

                    <!-- File input -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Berkas Dokumen</label>
                        
                        <div class="flex flex-col items-center justify-center w-full border-2 border-dashed border-slate-200 dark:border-slate-750 rounded-2xl p-6 bg-slate-50/50 dark:bg-slate-900/30 hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-all relative">
                            <input type="file" @change="handleFileChange" class="absolute inset-0 opacity-0 w-full h-full cursor-pointer" :required="!editingDocument" />
                            <File class="w-8 h-8 text-slate-400 mb-2" />
                            <span class="text-xs font-bold text-slate-655 dark:text-slate-400 text-center">
                                {{ form.file ? form.file.name : (editingDocument ? 'Pilih file baru untuk mengganti file lama' : 'Seret file atau klik untuk unggah') }}
                            </span>
                            <span class="text-[10px] text-slate-400 mt-1">PDF, DOCX, ZIP, XLS, DLL. (Max: 50MB)</span>
                        </div>
                        <p v-if="form.errors.file" class="text-red-500 text-[10px] font-bold mt-1">{{ form.errors.file }}</p>
                    </div>
                    
                    <div class="pt-4 flex flex-col sm:flex-row gap-2 sm:gap-3">
                        <button type="button" @click="closeModal" class="px-4 py-3 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-[12px] font-bold hover:bg-slate-200 transition-all sm:flex-1 order-2 sm:order-1 cursor-pointer">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-3 bg-[#2563EB] text-white rounded-xl text-[12px] font-black shadow-lg shadow-blue-500/20 hover:bg-blue-600 disabled:opacity-50 transition-all flex items-center justify-center gap-2 sm:flex-1 order-1 sm:order-2 active:scale-95 cursor-pointer">
                            <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                            Unggah
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PortalAdminLayout>
</template>
