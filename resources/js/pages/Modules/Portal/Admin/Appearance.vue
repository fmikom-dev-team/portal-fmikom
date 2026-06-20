<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3";
import {
	CheckCircle2,
	Edit2,
	Eye,
	EyeOff,
	Move,
	Save,
	Upload,
	X,
} from "lucide-vue-next";
import { reactive, ref } from "vue";
import PortalAdminLayout from "@/layouts/PortalAdminLayout.vue";

const props = defineProps({
	settings: {
		type: Object,
		default: () => ({}),
	},
});

// Hero_gallery & partners are ALREADY parsed arrays (from web.php route)
// Other settings are raw strings from DB
const form = reactive({
	// Visibility toggles (stored as '1'/'0' strings in DB)
	show_navbar: props.settings.show_navbar !== "0",
	show_hero: props.settings.show_hero !== "0",
	show_features: props.settings.show_features !== "0",
	show_partners: props.settings.show_partners !== "0",
	show_benefits: props.settings.show_benefits !== "0",

	// Content
	hero_title:
		props.settings.hero_title || "Satu Portal untuk \nSemua Layanan \nFMIKOM",
	hero_subtitle: props.settings.hero_subtitle || "Sistem Informasi Terpadu",
	hero_description:
		props.settings.hero_description ||
		"Kelola administrasi, magang, alumni, dan portofolio dalam satu sistem terintegrasi.",
	primary_color: props.settings.primary_color || "#2563eb",

	// hero_gallery & partners come as JSON strings from DB via pluck()
	hero_gallery: (() => {
		const v = props.settings.hero_gallery;
		if (Array.isArray(v)) return [...v];
		try {
			return JSON.parse(v || "[]");
		} catch {
			return [];
		}
	})(),
	partners: (() => {
		const v = props.settings.partners;
		if (Array.isArray(v)) return [...v];
		try {
			return JSON.parse(v || "[]");
		} catch {
			return [];
		}
	})(),

	// Benefits section
	benefits_title:
		props.settings.benefits_title || "Mengapa Memilih Portal FMIKOM?",
	benefits_subtitle:
		props.settings.benefits_subtitle ||
		"Platform digital terpadu yang dirancang khusus untuk kebutuhan civitas akademika FMIKOM.",
	benefit_1_title: props.settings.benefit_1_title || "Akses Mudah",
	benefit_1_desc:
		props.settings.benefit_1_desc ||
		"Satu platform untuk semua layanan akademik dan administratif.",
	benefit_2_title: props.settings.benefit_2_title || "Data Real-Time",
	benefit_2_desc:
		props.settings.benefit_2_desc ||
		"Informasi selalu terkini dan akurat langsung dari sumbernya.",
	benefit_3_title: props.settings.benefit_3_title || "Keamanan Tinggi",
	benefit_3_desc:
		props.settings.benefit_3_desc ||
		"Sistem SSO dengan proteksi berlapis untuk menjaga keamanan data.",

	// Images to remove
	remove_hero_gallery: [] as string[],
	remove_partners: [] as string[],
});

// New files selected but not yet submitted
const newHeroFiles = ref<File[]>([]);
const newHeroFilePreviews = ref<string[]>([]);
const newPartnerFiles = ref<File[]>([]);
const newPartnerFilePreviews = ref<string[]>([]);

const heroGalleryInput = ref<HTMLInputElement | null>(null);
const partnerInput = ref<HTMLInputElement | null>(null);

const handleHeroUpload = (event: Event) => {
	const target = event.target as HTMLInputElement;
	if (target.files) {
		const files = Array.from(target.files);
		newHeroFiles.value.push(...files);
		files.forEach((f) => { newHeroFilePreviews.value.push(URL.createObjectURL(f)); });
	}
};

const handlePartnerUpload = (event: Event) => {
	const target = event.target as HTMLInputElement;
	if (target.files) {
		const files = Array.from(target.files);
		newPartnerFiles.value.push(...files);
		files.forEach((f) => { newPartnerFilePreviews.value.push(URL.createObjectURL(f)); });
	}
};

const removeExistingGallery = (url: string) => {
	form.remove_hero_gallery.push(url);
	form.hero_gallery = form.hero_gallery.filter((u: string) => u !== url);
};

const removeExistingPartner = (url: string) => {
	form.remove_partners.push(url);
	form.partners = form.partners.filter((u: string) => u !== url);
};

const removeNewHero = (index: number) => {
	newHeroFiles.value.splice(index, 1);
	newHeroFilePreviews.value.splice(index, 1);
};

const removeNewPartner = (index: number) => {
	newPartnerFiles.value.splice(index, 1);
	newPartnerFilePreviews.value.splice(index, 1);
};

const isSuccess = ref(false);
const isProcessing = ref(false);
const editingWidget = ref<string | null>(null);

const openEditModal = (widgetName: string) => {
	editingWidget.value = widgetName;
};

const closeEditModal = () => {
	editingWidget.value = null;
};

const toggleVisibility = (
	key:
		| "show_navbar"
		| "show_hero"
		| "show_features"
		| "show_partners"
		| "show_benefits",
) => {
	form[key] = !form[key];
};

const submit = () => {
	const formData = new FormData();

	// Append text/boolean fields
	formData.append("hero_title", form.hero_title);
	formData.append("hero_subtitle", form.hero_subtitle);
	formData.append("hero_description", form.hero_description);
	formData.append("primary_color", form.primary_color);
	formData.append("show_navbar", form.show_navbar ? "1" : "0");
	formData.append("show_hero", form.show_hero ? "1" : "0");
	formData.append("show_features", form.show_features ? "1" : "0");
	formData.append("show_partners", form.show_partners ? "1" : "0");
	formData.append("show_benefits", form.show_benefits ? "1" : "0");
	formData.append("benefits_title", form.benefits_title);
	formData.append("benefits_subtitle", form.benefits_subtitle);
	formData.append("benefit_1_title", form.benefit_1_title);
	formData.append("benefit_1_desc", form.benefit_1_desc);
	formData.append("benefit_2_title", form.benefit_2_title);
	formData.append("benefit_2_desc", form.benefit_2_desc);
	formData.append("benefit_3_title", form.benefit_3_title);
	formData.append("benefit_3_desc", form.benefit_3_desc);

	// Append new gallery files
	newHeroFiles.value.forEach((f) => { formData.append("hero_gallery_files[]", f); });

	// Append new partner files
	newPartnerFiles.value.forEach((f) => { formData.append("partner_files[]", f); });

	// Append removals
	form.remove_hero_gallery.forEach((url) => { formData.append("remove_hero_gallery[]", url); });
	form.remove_partners.forEach((url) => { formData.append("remove_partners[]", url); });

	formData.append("_method", "POST");

	isProcessing.value = true;
	router.post("/portal-admin/appearance", formData, {
		preserveScroll: true,
		forceFormData: true,
		onSuccess: () => {
			isSuccess.value = true;
			isProcessing.value = false;
			newHeroFiles.value = [];
			newHeroFilePreviews.value = [];
			newPartnerFiles.value = [];
			newPartnerFilePreviews.value = [];
			form.remove_hero_gallery = [];
			form.remove_partners = [];
			closeEditModal();
			setTimeout(() => (isSuccess.value = false), 3000);
		},
		onError: () => {
			isProcessing.value = false;
		},
	});
};
</script>

<template>
    <PortalAdminLayout title="Tata Letak">
        
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-[20px] sm:text-[24px] font-black text-slate-900 dark:text-white tracking-tight">Tata Letak (Layout)</h2>
                <p class="text-[13px] font-bold text-slate-500 mt-1">Tambahkan, hapus, dan edit gadget di landing page utama.</p>
            </div>
            <button 
                @click="submit" 
                :disabled="isProcessing"
                class="bg-[#2563EB] hover:bg-blue-600 disabled:opacity-50 text-white px-6 py-3 rounded-[14px] text-[13px] font-black shadow-lg shadow-blue-500/20 transition-all flex items-center justify-center gap-2 w-full sm:w-auto active:scale-95 shrink-0"
            >
                <CheckCircle2 v-if="isSuccess" class="w-4 h-4" />
                <Save v-else class="w-4 h-4"/> 
                {{ isProcessing ? 'Menyimpan...' : (isSuccess ? 'Tersimpan!' : 'Simpan Tata Letak') }}
            </button>
        </div>

        <!-- LAYOUT BUILDER CANVAS -->
        <div class="bg-white dark:bg-slate-800 rounded-[1.25rem] p-6 sm:p-10 shadow-sm border border-slate-100 dark:border-slate-700 min-h-[70vh]">
            
            <div class="max-w-4xl mx-auto flex flex-col gap-6">
                
                <!-- SECTION: NAVBAR -->
                <div class="bg-[#f8fafc] dark:bg-slate-900/50 rounded-2xl p-4 border border-dashed border-slate-300 dark:border-slate-700">
                    <div class="text-[11px] font-black tracking-widest text-slate-400 uppercase mb-3 ml-2">Header / Navigasi</div>
                    
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl p-4 flex items-center justify-between shadow-sm transition-all hover:shadow-md hover:border-slate-300 dark:hover:border-slate-500">
                        <div class="flex items-center gap-4">
                            <button @click="toggleVisibility('show_navbar')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300" title="Sembunyikan/Tampilkan">
                                <Eye v-if="form.show_navbar" class="w-5 h-5"/>
                                <EyeOff v-else class="w-5 h-5 text-rose-500"/>
                            </button>
                            <div>
                                <h4 :class="['text-[14px] font-bold', form.show_navbar ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 line-through']">Menu Navigasi Utama</h4>
                                <p class="text-[12px] font-bold text-slate-500 mt-0.5">Gadget HTML/Navigasi Header</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="openEditModal('navbar')" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-[#2563EB] hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors border border-slate-200 dark:border-slate-600">
                                <Edit2 class="w-3.5 h-3.5"/>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- SECTION: HERO -->
                <div class="bg-[#f8fafc] dark:bg-slate-900/50 rounded-2xl p-4 border border-dashed border-slate-300 dark:border-slate-700">
                    <div class="text-[11px] font-black tracking-widest text-slate-400 uppercase mb-3 ml-2">Hero Section (Atas)</div>
                    
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl p-4 flex items-center justify-between shadow-sm transition-all hover:shadow-md hover:border-slate-300 dark:hover:border-slate-500">
                        <div class="flex items-center gap-4">
                            <button @click="toggleVisibility('show_hero')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300" title="Sembunyikan/Tampilkan">
                                <Eye v-if="form.show_hero" class="w-5 h-5"/>
                                <EyeOff v-else class="w-5 h-5 text-rose-500"/>
                            </button>
                            <div>
                                <h4 :class="['text-[14px] font-bold', form.show_hero ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 line-through']">Teks Utama & Call to Action</h4>
                                <p class="text-[12px] font-bold text-slate-500 mt-0.5">Gadget Hero Halaman Utama</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="openEditModal('hero')" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-[#2563EB] hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors border border-slate-200 dark:border-slate-600">
                                <Edit2 class="w-3.5 h-3.5"/>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- SECTION: GALLERY (Karya Mahasiswa) -->
                <div class="bg-[#f8fafc] dark:bg-slate-900/50 rounded-2xl p-4 border border-dashed border-blue-300 dark:border-blue-700">
                    <div class="text-[11px] font-black tracking-widest text-blue-400 uppercase mb-3 ml-2 flex items-center gap-2">
                        <ImageIcon class="w-3.5 h-3.5"/>
                        Galeri Karya Mahasiswa (Hero)
                    </div>
                    
                    <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-600 rounded-xl p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-4 flex-1">
                                <!-- Preview Thumbnails Compact -->
                                <div class="flex -space-x-3 shrink-0">
                                    <template v-if="form.hero_gallery.length > 0">
                                        <div v-for="(img, i) in form.hero_gallery.slice(0, 4)" :key="i"
                                            class="w-12 h-12 rounded-xl overflow-hidden border-2 border-white shadow-md"
                                        >
                                            <img :src="img" alt="Gallery image preview" class="w-full h-full object-cover">
                                        </div>
                                        <div v-if="form.hero_gallery.length > 4" class="w-12 h-12 rounded-xl bg-blue-100 border-2 border-white shadow-md flex items-center justify-center">
                                            <span class="text-[11px] font-black text-blue-600">+{{ form.hero_gallery.length - 4 }}</span>
                                        </div>
                                    </template>
                                    <div v-else class="w-12 h-12 rounded-xl bg-slate-100 border-2 border-dashed border-slate-300 flex items-center justify-center">
                                        <ImageIcon class="w-5 h-5 text-slate-400"/>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-[14px] font-bold text-slate-800 dark:text-slate-200">Galeri Karya Mahasiswa</h4>
                                    <p class="text-[12px] font-bold text-slate-500 mt-0.5">
                                        {{ form.hero_gallery.length > 0 ? `${form.hero_gallery.length} gambar · Muncul sebagai card stack interaktif di halaman depan` : 'Belum ada gambar · Upload untuk menampilkan galeri di halaman depan' }}
                                    </p>
                                    <!-- New files pending -->
                                    <p v-if="newHeroFilePreviews.length > 0" class="text-[11px] font-bold text-blue-500 mt-1">
                                        ✓ {{ newHeroFilePreviews.length }} file baru siap diupload
                                    </p>
                                </div>
                            </div>
                            <button @click="openEditModal('gallery')" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-[#2563EB] hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors border border-slate-200 dark:border-slate-600 shrink-0">
                                <Edit2 class="w-3.5 h-3.5"/>
                            </button>
                        </div>

                        <!-- Inline Gallery Previews (always visible after save) -->
                        <div v-if="form.hero_gallery.length > 0" class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Gambar Tersimpan ({{ form.hero_gallery.length }})</p>
                            <div class="flex flex-wrap gap-2">
                                <div v-for="img in form.hero_gallery" :key="img"
                                    class="relative group w-16 h-16 rounded-xl overflow-hidden border border-slate-200 shadow-sm">
                                    <img :src="img" alt="Gallery image preview" class="w-full h-full object-cover">
                                    <button @click="removeExistingGallery(img)"
                                        class="absolute inset-0 bg-red-500/80 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <X class="w-4 h-4"/>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Upload Button -->
                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700" :class="{'border-t-0 pt-0': form.hero_gallery.length === 0}">
                            <button @click="openEditModal('gallery')" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-[#2563EB] hover:bg-blue-600 text-white px-6 py-3 rounded-xl text-[12px] font-black transition-all shadow-lg shadow-blue-500/20 active:scale-95">
                                <Upload class="w-3.5 h-3.5"/> {{ form.hero_gallery.length > 0 ? 'Kelola Galeri' : 'Upload Gambar Galeri' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- MAIN BODY (GRID) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <!-- LEFT/MAIN COLUMN -->
                    <div class="md:col-span-2 bg-[#f8fafc] dark:bg-slate-900/50 rounded-2xl p-4 border border-dashed border-slate-300 dark:border-slate-700 flex flex-col gap-4">
                        <div class="text-[11px] font-black tracking-widest text-slate-400 uppercase ml-2">Badan Halaman</div>
                        
                        <!-- WIDGET: POSTS / BERITA -->
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl p-4 flex items-center justify-between shadow-sm transition-all hover:shadow-md hover:border-slate-300 dark:hover:border-slate-500">
                            <div class="flex items-center gap-4">
                                <button @click="toggleVisibility('show_features')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300" title="Sembunyikan/Tampilkan">
                                    <Eye v-if="form.show_features" class="w-5 h-5"/>
                                    <EyeOff v-else class="w-5 h-5 text-rose-500"/>
                                </button>
                                <div>
                                    <h4 :class="['text-[14px] font-bold', form.show_features ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 line-through']">Berita & Postingan</h4>
                                    <p class="text-[12px] font-bold text-slate-500 mt-0.5">Otomatis dari Portal Admin Posts</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-700 flex items-center justify-center text-slate-400 cursor-not-allowed border border-slate-200 dark:border-slate-600">
                                    <Edit2 class="w-3.5 h-3.5"/>
                                </button>
                            </div>
                        </div>

                        <!-- WIDGET: PARTNERS -->
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl p-4 shadow-sm transition-all hover:shadow-md hover:border-slate-300 dark:hover:border-slate-500">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <button @click="toggleVisibility('show_partners')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300" title="Sembunyikan/Tampilkan">
                                        <Eye v-if="form.show_partners" class="w-5 h-5"/>
                                        <EyeOff v-else class="w-5 h-5 text-rose-500"/>
                                    </button>
                                    <div>
                                        <h4 :class="['text-[14px] font-bold', form.show_partners ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 line-through']">Mitra & Partner</h4>
                                        <p class="text-[12px] font-bold text-slate-500 mt-0.5">
                                            {{ form.partners.length > 0 ? `${form.partners.length} logo mitra tersimpan` : 'Logo-logo partner / kerjasama' }}
                                        </p>
                                    </div>
                                </div>
                                <button @click="openEditModal('partners')" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-[#2563EB] hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors border border-slate-200 dark:border-slate-600">
                                    <Edit2 class="w-3.5 h-3.5"/>
                                </button>
                            </div>

                            <!-- Inline Logo Previews -->
                            <div v-if="form.partners.length > 0" class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                                <div class="flex flex-wrap gap-2">
                                    <div v-for="logo in form.partners" :key="logo"
                                        class="relative group h-10 w-[72px] rounded-lg overflow-hidden border border-slate-100 bg-white flex items-center justify-center p-1 shadow-sm">
                                        <img :src="logo" alt="Partner logo" class="max-w-full max-h-full object-contain">
                                        <button @click="removeExistingPartner(logo)"
                                            class="absolute inset-0 bg-red-500/80 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                            <X class="w-3 h-3"/>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- WIDGET: BENEFITS -->
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl p-4 flex items-center justify-between shadow-sm transition-all hover:shadow-md hover:border-slate-300 dark:hover:border-slate-500">
                            <div class="flex items-center gap-4">
                                <button @click="toggleVisibility('show_benefits')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300" title="Sembunyikan/Tampilkan">
                                    <Eye v-if="form.show_benefits" class="w-5 h-5"/>
                                    <EyeOff v-else class="w-5 h-5 text-rose-500"/>
                                </button>
                                <div>
                                    <h4 :class="['text-[14px] font-bold', form.show_benefits ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 line-through']">Seksi Keunggulan</h4>
                                    <p class="text-[12px] font-bold text-slate-500 mt-0.5">Gadget Teks & Ilustrasi</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="openEditModal('benefits')" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-[#2563EB] hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors border border-slate-200 dark:border-slate-600">
                                    <Edit2 class="w-3.5 h-3.5"/>
                                </button>
                            </div>
                        </div>

                    </div>

                    <!-- RIGHT COLUMN (SIDEBAR EQUIVALENT / SETTINGS) -->
                    <div class="bg-[#f8fafc] dark:bg-slate-900/50 rounded-2xl p-4 border border-dashed border-slate-300 dark:border-slate-700 flex flex-col gap-4">
                        <div class="text-[11px] font-black tracking-widest text-slate-400 uppercase ml-2">Pengaturan Umum</div>
                        
                        <!-- WIDGET: THEME -->
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl p-4 flex items-center justify-between shadow-sm transition-all hover:shadow-md hover:border-slate-300 dark:hover:border-slate-500">
                            <div class="flex items-center gap-4">
                                <div class="w-5 h-5 flex items-center justify-center">
                                    <div class="w-4 h-4 rounded-full" :style="{ backgroundColor: form.primary_color }"></div>
                                </div>
                                <div>
                                    <h4 class="text-[14px] font-bold text-slate-800 dark:text-slate-200">Warna Tema</h4>
                                    <p class="text-[12px] font-bold text-slate-500 mt-0.5">Aksen Primary</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="openEditModal('theme')" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-[#2563EB] hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors border border-slate-200 dark:border-slate-600">
                                    <Edit2 class="w-3.5 h-3.5"/>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- EDIT MODALS -->
        <div v-if="editingWidget" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-100 dark:border-slate-700 flex flex-col max-h-[90vh]">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800">
                    <h3 class="text-[16px] font-black text-slate-800 dark:text-white">
                        Konfigurasi Gadget: 
                        <span v-if="editingWidget === 'hero'">Teks Utama & Hero</span>
                        <span v-if="editingWidget === 'navbar'">Navigasi Header</span>
                        <span v-if="editingWidget === 'partners'">Mitra & Kerjasama</span>
                        <span v-if="editingWidget === 'gallery'">Galeri Karya Mahasiswa</span>
                        <span v-if="editingWidget === 'theme'">Warna Tema Utama</span>
                        <span v-if="editingWidget === 'benefits'">Seksi Keunggulan</span>
                    </h3>
                    <button @click="closeEditModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <X class="w-5 h-5"/>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto">
                    
                    <div v-if="editingWidget === 'hero'" class="space-y-5">
                        <div>
                            <label class="block text-[12px] font-bold text-slate-600 dark:text-slate-400 mb-2">Sub-Judul (Tagline)</label>
                            <input v-model="form.hero_subtitle" type="text" class="w-full bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-[13px] font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-[#2563EB] outline-none" />
                        </div>
                        <div>
                            <label class="block text-[12px] font-bold text-slate-600 dark:text-slate-400 mb-2">Judul Utama</label>
                            <textarea v-model="form.hero_title" rows="3" class="w-full bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-[13px] font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-[#2563EB] outline-none resize-none"></textarea>
                            <p class="text-[11px] text-slate-400 mt-1">Gunakan enter/baris baru untuk memisahkan teks.</p>
                        </div>
                        <div>
                            <label class="block text-[12px] font-bold text-slate-600 dark:text-slate-400 mb-2">Deskripsi Singkat</label>
                            <textarea v-model="form.hero_description" rows="3" class="w-full bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-[13px] font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-[#2563EB] outline-none resize-none"></textarea>
                        </div>
                        <div class="border-t border-slate-100 dark:border-slate-700 pt-5">
                            <label class="block text-[12px] font-bold text-slate-600 dark:text-slate-400 mb-3">Galeri Karya Mahasiswa (Card Stacks)</label>
                            
                            <!-- Existing Uploaded Images -->
                            <div v-if="form.hero_gallery.length > 0" class="mb-4">
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Sudah Diunggah</p>
                                <div class="grid grid-cols-4 gap-2">
                                    <div v-for="img in form.hero_gallery" :key="img" class="relative group aspect-square rounded-lg overflow-hidden border border-slate-200">
                                        <img :src="img" alt="Gallery image preview" class="w-full h-full object-cover">
                                        <button @click="removeExistingGallery(img)" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <X class="w-3 h-3"/>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- New Files Preview -->
                            <div v-if="newHeroFilePreviews.length > 0" class="mb-4">
                                <p class="text-[11px] font-bold text-blue-500 uppercase tracking-wider mb-2">Baru Dipilih ({{ newHeroFilePreviews.length }} file)</p>
                                <div class="grid grid-cols-4 gap-2">
                                    <div v-for="(preview, i) in newHeroFilePreviews" :key="i" class="relative group aspect-square rounded-lg overflow-hidden border-2 border-blue-300">
                                        <img :src="preview" alt="Gallery image preview" class="w-full h-full object-cover">
                                        <button @click="removeNewHero(i)" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <X class="w-3 h-3"/>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <input type="file" multiple accept="image/*" class="hidden" ref="heroGalleryInput" @change="handleHeroUpload">
                            <button @click="heroGalleryInput?.click()" class="flex items-center gap-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-200 px-4 py-2.5 rounded-xl text-[12px] font-bold hover:bg-blue-50 hover:text-[#2563EB] transition-colors border border-slate-200 dark:border-slate-600">
                                <Upload class="w-4 h-4"/> Pilih Gambar
                            </button>
                            <p class="text-[11px] text-slate-400 mt-2">Klik Simpan Perubahan untuk mengupload gambar ke server.</p>
                        </div>
                    </div>

                    <div v-if="editingWidget === 'partners'" class="space-y-5">
                        <div>
                            <label class="block text-[12px] font-bold text-slate-600 dark:text-slate-400 mb-3">Logo Mitra & Kerjasama</label>
                            
                            <!-- Existing Logos -->
                            <div v-if="form.partners.length > 0" class="mb-4">
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Sudah Diunggah</p>
                                <div class="grid grid-cols-4 gap-2">
                                    <div v-for="img in form.partners" :key="img" class="relative group aspect-video rounded-lg overflow-hidden border border-slate-200 bg-white flex items-center justify-center p-2">
                                        <img :src="img" alt="Partner logo preview" class="max-w-full max-h-full object-contain">
                                        <button @click="removeExistingPartner(img)" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <X class="w-3 h-3"/>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- New Files Preview -->
                            <div v-if="newPartnerFilePreviews.length > 0" class="mb-4">
                                <p class="text-[11px] font-bold text-blue-500 uppercase tracking-wider mb-2">Baru Dipilih ({{ newPartnerFilePreviews.length }} file)</p>
                                <div class="grid grid-cols-4 gap-2">
                                    <div v-for="(preview, i) in newPartnerFilePreviews" :key="i" class="relative group aspect-video rounded-lg overflow-hidden border-2 border-blue-300 bg-white flex items-center justify-center p-2">
                                        <img :src="preview" alt="Partner logo preview" class="max-w-full max-h-full object-contain">
                                        <button @click="removeNewPartner(i)" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <X class="w-3 h-3"/>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <input type="file" multiple accept="image/*" class="hidden" ref="partnerInput" @change="handlePartnerUpload">
                            <button @click="partnerInput?.click()" class="flex items-center gap-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-200 px-4 py-2.5 rounded-xl text-[12px] font-bold hover:bg-blue-50 hover:text-[#2563EB] transition-colors border border-slate-200 dark:border-slate-600">
                                <Upload class="w-4 h-4"/> Pilih Logo Mitra
                            </button>
                            <p class="text-[11px] text-slate-400 mt-2">Klik Simpan Perubahan untuk mengupload logo ke server.</p>
                        </div>
                    </div>

                    <div v-if="editingWidget === 'theme'" class="space-y-5">
                        <div>
                            <label class="block text-[12px] font-bold text-slate-600 dark:text-slate-400 mb-2">Pilih Warna Utama (Hex Code)</label>
                            <div class="flex items-center gap-3">
                                <input type="color" v-model="form.primary_color" class="h-10 w-14 rounded-xl cursor-pointer border-0 p-0 overflow-hidden" />
                                <input v-model="form.primary_color" type="text" class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-[13px] font-bold text-slate-800 dark:text-slate-200 w-32 focus:ring-2 focus:ring-[#2563EB] outline-none" />
                            </div>
                        </div>
                    </div>

                    <div v-if="editingWidget === 'navbar'" class="flex flex-col items-center justify-center py-8 opacity-60">
                        <Move class="w-10 h-10 text-slate-300 mb-3"/>
                        <p class="text-[13px] font-bold text-slate-500 text-center">Pengaturan detail untuk gadget ini akan tersedia pada pembaruan berikutnya.</p>
                        <p class="text-[11px] text-slate-400 mt-1">Saat ini Anda hanya dapat mengubah status tampilkan/sembunyikan di menu sebelumnya.</p>
                    </div>

                    <!-- GALLERY MODAL -->
                    <div v-if="editingWidget === 'gallery'" class="space-y-5">
                        <div>
                            <label class="block text-[12px] font-bold text-slate-600 dark:text-slate-400 mb-1">Gambar Galeri Karya Mahasiswa</label>
                            <p class="text-[11px] text-slate-400 mb-4">Upload gambar karya mahasiswa. Akan muncul sebagai card stack interaktif di bagian atas Landing Page. Bisa lebih dari satu gambar.</p>
                        </div>

                        <!-- Existing Images Grid -->
                        <div v-if="form.hero_gallery.length > 0">
                            <p class="text-[11px] font-black text-slate-400 uppercase tracking-wider mb-3">Gambar Tersimpan ({{ form.hero_gallery.length }})</p>
                            <div class="grid grid-cols-3 gap-3">
                                <div v-for="img in form.hero_gallery" :key="img" class="relative group aspect-[4/3] rounded-xl overflow-hidden border border-slate-200 shadow-sm">
                                    <img :src="img" alt="Gallery image preview" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all flex items-center justify-center">
                                        <button @click="removeExistingGallery(img)" class="opacity-0 group-hover:opacity-100 bg-red-500 text-white rounded-full p-1.5 transition-opacity shadow-md">
                                            <X class="w-4 h-4"/>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- New Files Preview -->
                        <div v-if="newHeroFilePreviews.length > 0">
                            <p class="text-[11px] font-black text-blue-500 uppercase tracking-wider mb-3">Akan Diunggah ({{ newHeroFilePreviews.length }})</p>
                            <div class="grid grid-cols-3 gap-3">
                                <div v-for="(preview, i) in newHeroFilePreviews" :key="i" class="relative group aspect-[4/3] rounded-xl overflow-hidden border-2 border-blue-300 shadow-sm">
                                    <img :src="preview" alt="Gallery image preview" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all flex items-center justify-center">
                                        <button @click="removeNewHero(i)" class="opacity-0 group-hover:opacity-100 bg-red-500 text-white rounded-full p-1.5 transition-opacity shadow-md">
                                            <X class="w-4 h-4"/>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 flex flex-col sm:flex-row justify-end gap-2 sm:gap-3">
                    <button @click="closeEditModal" class="px-5 py-2.5 rounded-xl text-[13px] font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all order-2 sm:order-1 flex-1 sm:flex-none">
                        Batal
                    </button>
                    <button @click="submit" class="bg-[#2563EB] hover:bg-blue-600 text-white px-5 py-3 rounded-xl text-[13px] font-black shadow-lg shadow-blue-500/20 transition-all order-1 sm:order-2 flex-1 sm:flex-none active:scale-95">
                        Simpan Perubahan
                    </button>
                </div>

            </div>
        </div>

        <!-- HIDDEN FILE INPUTS (Global) -->
        <input type="file" multiple accept="image/*" class="hidden" ref="heroGalleryInput" @change="handleHeroUpload">
        <input type="file" multiple accept="image/*" class="hidden" ref="partnerInput" @change="handlePartnerUpload">

    </PortalAdminLayout>
</template>
