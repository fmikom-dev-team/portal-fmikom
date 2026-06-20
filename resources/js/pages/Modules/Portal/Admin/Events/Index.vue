<script setup lang="ts">
import { router, useForm } from "@inertiajs/vue3";
import {
	AlertCircle,
	Calendar,
	CheckCircle2,
	Circle,
	Edit,
	Eye,
	ImageIcon,
	Link2,
	MapPin,
	Plus,
	Search,
	Sparkles,
	Trash2,
	X,
} from "lucide-vue-next";
import { computed, ref, watch } from "vue";
import PortalAdminLayout from "@/layouts/PortalAdminLayout.vue";

const props = defineProps({
	events: {
		type: Array as () => any[],
		default: () => [],
	},
	filters: {
		type: Object,
		default: () => ({ search: "" }),
	},
});

// Search Filter
const localSearch = ref(props.filters.search || "");
const statusFilter = ref("all");

const filteredEvents = computed(() => {
	return props.events.filter((event) => {
		// Status filter
		if (statusFilter.value !== "all" && event.status !== statusFilter.value) {
			return false;
		}
		// Search filter
		if (localSearch.value.trim() !== "") {
			const query = localSearch.value.toLowerCase();
			return (
				event.title?.toLowerCase().includes(query) ||
				event.description?.toLowerCase().includes(query) ||
				event.location?.toLowerCase().includes(query)
			);
		}
		return true;
	});
});

// Modal state
const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
	title: "",
	slug: "",
	description: "",
	location: "",
	start_time: "",
	end_time: "",
	registration_link: "",
	status: "draft",
	thumbnail: null as any,
});

// Thumbnail preview state
const thumbnailPreview = ref<string | null>(null);

const onFileChange = (e: Event) => {
	const file = (e.target as HTMLInputElement).files?.[0];
	if (file) {
		form.thumbnail = file;
		thumbnailPreview.value = URL.createObjectURL(file);
	}
};

// Auto generate slug from title
watch(
	() => form.title,
	(newTitle) => {
		if (!isEditing.value) {
			form.slug = newTitle
				.toLowerCase()
				.trim()
				.replace(/[^\w\s-]/g, "")
				.replace(/[\s_-]+/g, "-")
				.replace(/^-+|-+$/g, "");
		}
	},
);

const openCreateModal = () => {
	form.reset();
	isEditing.value = false;
	editingId.value = null;
	thumbnailPreview.value = null;

	// Default dates
	const now = new Date();
	now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
	const formatted = now.toISOString().slice(0, 16);
	form.start_time = formatted;
	form.end_time = formatted;

	isModalOpen.value = true;
};

const openEditModal = (event: any) => {
	isEditing.value = true;
	editingId.value = event.id;
	form.title = event.title;
	form.slug = event.slug;
	form.description = event.description;
	form.location = event.location || "";

	// Format timestamp string "2026-05-19 19:30:00" to ISO "2026-05-19T19:30"
	form.start_time = event.start_time
		? event.start_time.replace(" ", "T").slice(0, 16)
		: "";
	form.end_time = event.end_time
		? event.end_time.replace(" ", "T").slice(0, 16)
		: "";

	form.registration_link = event.registration_link || "";
	form.status = event.status;
	form.thumbnail = null;
	thumbnailPreview.value = event.thumbnail || null;

	isModalOpen.value = true;
};

const submitForm = () => {
	// We send multipart form data (necessary because of file upload)
	if (isEditing.value && editingId.value) {
		// Laravel doesn't support file upload inside PUT request natively, so we spoof it with POST + _method: 'PUT'
		form.post(`/portal-admin/events/${editingId.value}`, {
			headers: {
				"X-HTTP-Method-Override": "PUT",
			},
			onSuccess: () => closeModal(),
		});
	} else {
		form.post("/portal-admin/events", {
			onSuccess: () => closeModal(),
		});
	}
};

const deleteEvent = (id: number) => {
	if (confirm("Apakah Anda yakin ingin menghapus event ini?")) {
		router.delete(`/portal-admin/events/${id}`, {
			onSuccess: () => closeModal(),
		});
	}
};

const closeModal = () => {
	isModalOpen.value = false;
	form.reset();
	thumbnailPreview.value = null;
};

const getFormattedDate = (dateStr: string) => {
	if (!dateStr) return "";
	const date = new Date(dateStr);
	return date.toLocaleDateString("id-ID", {
		day: "numeric",
		month: "short",
		year: "numeric",
		hour: "2-digit",
		minute: "2-digit",
	});
};
</script>

<template>
    <PortalAdminLayout title="Manajemen Event">
        <!-- Header -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="bg-indigo-50 text-indigo-600 dark:bg-indigo-950/20 dark:text-indigo-400 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider flex items-center gap-1">
                        <Sparkles class="w-3 h-3 animate-pulse" /> Kegiatan Kampus
                    </span>
                </div>
                <h2 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Manajemen Event & Acara</h2>
                <p class="text-xs md:text-sm font-medium text-slate-500 mt-1 dark:text-slate-400">Kelola informasi workshop, seminar, webinar, kompetisi, dan acara fakultas.</p>
            </div>
            
            <button 
                @click="openCreateModal"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-2xl text-[13px] font-bold shadow-lg shadow-blue-500/20 transition-all flex items-center justify-center gap-2 w-full md:w-auto active:scale-95 shrink-0 select-none"
            >
                <Plus class="w-4 h-4" />
                Buat Event
            </button>
        </div>

        <!-- Filter, Tabs & Search Panel -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-4 md:p-6 border border-slate-100 dark:border-slate-800 shadow-sm mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
            <!-- Tabs (Filtering) -->
            <div class="flex items-center gap-1 bg-slate-50 dark:bg-slate-900 p-1 rounded-2xl border border-slate-100 dark:border-slate-800 w-full md:w-auto overflow-x-auto scrollbar-none">
                <button 
                    @click="statusFilter = 'all'"
                    :class="[statusFilter === 'all' ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-white shadow-sm border border-slate-100/50 dark:border-slate-700' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200', 'px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap']"
                >
                    Semua Event
                </button>
                <button 
                    @click="statusFilter = 'published'"
                    :class="[statusFilter === 'published' ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-white shadow-sm border border-slate-100/50 dark:border-slate-700' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200', 'px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap']"
                >
                    Dipublikasikan
                </button>
                <button 
                    @click="statusFilter = 'draft'"
                    :class="[statusFilter === 'draft' ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-white shadow-sm border border-slate-100/50 dark:border-slate-700' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200', 'px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap']"
                >
                    Draft
                </button>
            </div>

            <!-- Search input -->
            <div class="relative w-full md:w-[320px]">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                    <Search class="w-4 h-4" />
                </span>
                <input 
                    v-model="localSearch"
                    type="text" 
                    placeholder="Cari judul event atau lokasi..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-2xl text-xs font-semibold bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-600 transition-all placeholder-slate-400 text-slate-700 dark:text-white"
                />
            </div>
        </div>

        <!-- Events Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div 
                v-for="event in filteredEvents" 
                :key="event.id" 
                class="group bg-white dark:bg-slate-800 rounded-3xl overflow-hidden border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-xl hover:border-blue-100 dark:hover:border-blue-900/50 transition-all duration-300 flex flex-col justify-between"
            >
                <div>
                    <!-- Thumbnail preview with overlays -->
                    <div class="relative h-44 overflow-hidden bg-slate-50 dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 flex items-center justify-center">
                        <img 
                            v-if="event.thumbnail" 
                            :src="event.thumbnail" 
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" 
                            :alt="event.title"
                        >
                        <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-300 dark:text-slate-600">
                            <ImageIcon class="w-12 h-12 opacity-30 mb-2" />
                            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">No Cover Image</span>
                        </div>

                        <!-- Status badge on thumbnail -->
                        <div class="absolute top-4 left-4">
                            <span 
                                :class="[
                                    event.status === 'published' 
                                        ? 'bg-emerald-500 text-white shadow-sm' 
                                        : 'bg-amber-500 text-white shadow-sm', 
                                    'text-[9px] font-black px-2.5 py-1 rounded-lg uppercase tracking-wider'
                                ]"
                            >
                                {{ event.status === 'published' ? 'Publik' : 'Draft' }}
                            </span>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="p-5 space-y-3">
                        <h3 class="text-base font-black text-slate-800 dark:text-white leading-snug group-hover:text-blue-600 transition-colors line-clamp-2">
                            {{ event.title }}
                        </h3>

                        <div class="space-y-1.5 text-xs text-slate-500 dark:text-slate-400 font-semibold">
                            <!-- Time -->
                            <div class="flex items-center gap-2">
                                <Calendar class="w-4 h-4 text-slate-400 shrink-0" />
                                <span>{{ getFormattedDate(event.start_time) }}</span>
                            </div>
                            <!-- Location -->
                            <div class="flex items-center gap-2" v-if="event.location">
                                <MapPin class="w-4 h-4 text-slate-400 shrink-0" />
                                <span class="truncate">{{ event.location }}</span>
                            </div>
                            <!-- Registration link badge -->
                            <div class="flex items-center gap-2" v-if="event.registration_link">
                                <Link2 class="w-4 h-4 text-slate-400 shrink-0" />
                                <a :href="event.registration_link" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline truncate">Link Registrasi</a>
                            </div>
                        </div>

                        <p class="text-xs text-slate-400 dark:text-slate-500 line-clamp-3 font-medium leading-relaxed mt-2 pt-2 border-t border-slate-50 dark:border-slate-700">
                            {{ event.description }}
                        </p>
                    </div>
                </div>

                <!-- Footer actions -->
                <div class="px-5 py-4 border-t border-slate-50 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/10">
                    <span class="text-[10px] font-bold text-slate-400">ID: #{{ event.id }}</span>
                    
                    <div class="flex items-center gap-1">
                        <button 
                            @click="openEditModal(event)" 
                            class="w-8 h-8 flex items-center justify-center text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-xl transition-all"
                            title="Edit"
                        >
                            <Edit class="w-4 h-4" />
                        </button>
                        <button 
                            @click="deleteEvent(event.id)" 
                            class="w-8 h-8 flex items-center justify-center text-slate-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-xl transition-all"
                            title="Hapus"
                        >
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div 
                v-if="filteredEvents.length === 0" 
                class="col-span-full py-20 text-center bg-white dark:bg-slate-800 rounded-3xl border border-dashed border-slate-200 dark:border-slate-700"
            >
                <div class="w-16 h-16 bg-slate-50 dark:bg-slate-900 rounded-full flex items-center justify-center mx-auto mb-4">
                    <Calendar class="w-8 h-8 text-slate-400 opacity-60" />
                </div>
                <p class="text-slate-900 dark:text-white text-base font-black">Tidak ada event ditemukan</p>
                <p class="text-slate-500 text-xs mt-1 font-bold">Coba ubah kata kunci atau buat event baru.</p>
            </div>
        </div>

        <!-- Custom Dialog Modal (Glassmorphism Overlay) -->
        <transition name="fade">
            <div 
                v-if="isModalOpen" 
                class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-all overflow-y-auto"
                @click.self="closeModal"
            >
                <div class="bg-white dark:bg-slate-800 rounded-[28px] border border-slate-100 dark:border-slate-700 shadow-2xl w-full max-w-xl my-8 overflow-hidden transform transition-all duration-300 scale-100">
                    <!-- Modal Header -->
                    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600">
                                <Calendar class="w-5 h-5" />
                            </span>
                            <h3 class="text-lg font-black text-slate-800 dark:text-white">
                                {{ isEditing ? 'Edit Informasi Event' : 'Buat Event Baru' }}
                            </h3>
                        </div>
                        <button @click="closeModal" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center text-slate-400 transition-colors">
                            <X class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Modal Body Form -->
                    <form @submit.prevent="submitForm" class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                        <!-- Cover Image Upload -->
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-400 tracking-wider mb-1.5">Gambar Cover / Poster</label>
                            <div class="flex items-center gap-4">
                                <div class="w-24 h-24 rounded-2xl overflow-hidden bg-slate-50 dark:bg-slate-900 border border-slate-150 dark:border-slate-700 flex items-center justify-center shrink-0">
                                    <img v-if="thumbnailPreview" :src="thumbnailPreview" alt="Pratinjau poster event" class="w-full h-full object-cover">
                                    <ImageIcon v-else class="w-8 h-8 text-slate-300" />
                                </div>
                                <div class="space-y-1">
                                    <input 
                                        type="file" 
                                        accept="image/*"
                                        @change="onFileChange" 
                                        id="thumbnail-upload"
                                        class="hidden"
                                    />
                                    <label 
                                        for="thumbnail-upload"
                                        class="cursor-pointer bg-slate-50 dark:bg-slate-900 hover:bg-slate-100 border border-slate-150 dark:border-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition-all inline-block text-slate-700 dark:text-white"
                                    >
                                        Pilih File Gambar
                                    </label>
                                    <p class="text-[10px] text-slate-450 font-medium">Format: JPEG, PNG, WEBP. Maks: 5MB.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Title -->
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-400 tracking-wider mb-1.5">Nama / Judul Event</label>
                            <input 
                                v-model="form.title" 
                                type="text" 
                                required
                                placeholder="Contoh: Seminar Nasional AI & Generative Coding"
                                class="w-full px-4 py-3 rounded-2xl text-xs font-semibold bg-slate-50 dark:bg-slate-900 border border-slate-150 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-white"
                            />
                        </div>

                        <!-- Slug -->
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-400 tracking-wider mb-1.5">Slug (URL)</label>
                            <input 
                                v-model="form.slug" 
                                type="text" 
                                required
                                placeholder="seminar-nasional-ai"
                                class="w-full px-4 py-3 rounded-2xl text-xs font-semibold bg-slate-50 dark:bg-slate-900 border border-slate-150 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-white"
                            />
                        </div>

                        <!-- Location & Registration link -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black uppercase text-slate-400 tracking-wider mb-1.5">Lokasi Pelaksanaan</label>
                                <input 
                                    v-model="form.location" 
                                    type="text" 
                                    placeholder="Contoh: Auditorium Utama / Zoom"
                                    class="w-full px-4 py-3 rounded-2xl text-xs font-semibold bg-slate-50 dark:bg-slate-900 border border-slate-150 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-white"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase text-slate-400 tracking-wider mb-1.5">Link Pendaftaran (URL)</label>
                                <input 
                                    v-model="form.registration_link" 
                                    type="url" 
                                    placeholder="https://..."
                                    class="w-full px-4 py-3 rounded-2xl text-xs font-semibold bg-slate-50 dark:bg-slate-900 border border-slate-150 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-white"
                                />
                            </div>
                        </div>

                        <!-- Timestamps -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black uppercase text-slate-400 tracking-wider mb-1.5">Waktu Mulai</label>
                                <input 
                                    v-model="form.start_time" 
                                    type="datetime-local" 
                                    required
                                    class="w-full px-4 py-3 rounded-2xl text-xs font-semibold bg-slate-50 dark:bg-slate-900 border border-slate-150 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-white"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase text-slate-400 tracking-wider mb-1.5">Waktu Selesai (Opsional)</label>
                                <input 
                                    v-model="form.end_time" 
                                    type="datetime-local" 
                                    class="w-full px-4 py-3 rounded-2xl text-xs font-semibold bg-slate-50 dark:bg-slate-900 border border-slate-150 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-white"
                                />
                            </div>
                        </div>

                        <!-- Status & Publish controls -->
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-400 tracking-wider mb-1.5">Status Publikasi</label>
                            <div class="flex items-center gap-4">
                                <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700 dark:text-slate-300">
                                    <input 
                                        type="radio" 
                                        v-model="form.status" 
                                        value="draft" 
                                        class="w-4 h-4 text-blue-600"
                                    />
                                    Simpan sebagai Draft
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700 dark:text-slate-300">
                                    <input 
                                        type="radio" 
                                        v-model="form.status" 
                                        value="published" 
                                        class="w-4 h-4 text-blue-600"
                                    />
                                    Terbitkan Langsung
                                </label>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-400 tracking-wider mb-1.5">Deskripsi Event / Konten</label>
                            <textarea 
                                v-model="form.description" 
                                rows="4"
                                required
                                placeholder="Jelaskan detail jalannya acara, pembicara, syarat pendaftaran, dll..."
                                class="w-full px-4 py-3 rounded-2xl text-xs font-semibold bg-slate-50 dark:bg-slate-900 border border-slate-150 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-white"
                            ></textarea>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-between gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                            <div>
                                <button 
                                    v-if="isEditing" 
                                    type="button" 
                                    @click="deleteEvent(editingId!)"
                                    class="px-4 py-3 text-xs font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-2xl transition-all"
                                >
                                    Hapus Event
                                </button>
                            </div>
                            <div class="flex items-center gap-3">
                                <button 
                                    type="button" 
                                    @click="closeModal" 
                                    class="px-4 py-3 text-xs font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-900 rounded-2xl transition-all"
                                >
                                    Batal
                                </button>
                                <button 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-2xl text-xs font-bold shadow-lg shadow-blue-500/20 transition-all active:scale-95 disabled:opacity-50"
                                >
                                    {{ isEditing ? 'Simpan Event' : 'Buat Event' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </PortalAdminLayout>
</template>

<style scoped>
.scrollbar-none::-webkit-scrollbar {
    display: none;
}
.scrollbar-none {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
