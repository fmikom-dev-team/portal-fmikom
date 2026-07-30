<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3";
import {
	ArrowDown,
	ArrowUp,
	CheckCircle2,
	Edit2,
	Eye,
	EyeOff,
	Move,
	Plus,
	Save,
	Sparkles,
	Trash2,
	Upload,
	UserCheck,
	X,
} from "lucide-vue-next";
import { reactive, ref, computed, watch } from "vue";
import PortalAdminLayout from "@/layouts/PortalAdminLayout.vue";

const props = defineProps({
	settings: {
		type: Object,
		default: () => ({}),
	},
	errors: {
		type: Object,
		default: () => ({}),
	},
});

const defaultTestimonialsList = [
	{
		id: "1",
		quote: "Sistem FAST benar-benar mengubah cara saya mengajukan persuratan. Dulu butuh 3 hari, sekarang hanya hitungan jam sudah disetujui Kaprodi!",
		name: "Andi Saputra",
		role: "Mahasiswa Semester 6",
		avatar: "https://api.dicebear.com/7.x/avataaars/svg?seed=a042581f4e29026024d",
		theme: "dark",
	},
	{
		id: "2",
		quote: "Sistem administrasi menjadi sangat transparan. Saya bisa melacak setiap proses dokumen dengan mudah tanpa harus bolak-balik ke tata usaha.",
		name: "Rizky Pratama",
		role: "Ketua BEM FMIKOM",
		avatar: "https://api.dicebear.com/7.x/avataaars/svg?seed=a042581f4e29026011d",
		theme: "light",
	},
	{
		id: "3",
		quote: "Sebagai dosen pembimbing, memantau logbook magang mahasiswa via WIMS sangat menghemat waktu. Semua terpusat, real-time, dan mudah diakses dari mana saja.",
		name: "Dr. Budi Santoso, M.Kom",
		role: "Dosen Pembimbing",
		avatar: "https://api.dicebear.com/7.x/avataaars/svg?seed=a042581f4e29026704d",
		theme: "light",
	},
	{
		id: "4",
		quote: "Pengajuan judul skripsi dan pencarian dosen pembimbing jadi lebih terstruktur berkat modul bimbingan akademik di portal ini.",
		name: "Dina Aulia",
		role: "Mahasiswa Tingkat Akhir",
		avatar: "https://api.dicebear.com/7.x/avataaars/svg?seed=a042581f4e29026715d",
		theme: "light",
	},
	{
		id: "5",
		quote: "Birokrasi kampus yang selama ini kompleks, kini dapat diselesaikan hanya dengan beberapa kali klik. Transformasi digital yang luar biasa.",
		name: "Prof. Herman",
		role: "Dekan FMIKOM",
		avatar: "https://api.dicebear.com/7.x/avataaars/svg?seed=a042581f4e29026725d",
		theme: "light",
	},
	{
		id: "6",
		quote: "Sangat mudah memonitor mahasiswa magang dari perusahaan kami. Form penilaian langsung tersedia online dan sistemnya sangat responsif.",
		name: "Anton Setiawan",
		role: "HR Director, TechNesia",
		avatar: "https://api.dicebear.com/7.x/avataaars/svg?seed=a04258a2462d826729d",
		theme: "light",
	},
	{
		id: "7",
		quote: "Saya mendapat pekerjaan pertama saya karena profil portofolio yang saya bangun dan tracer study terhubung langsung oleh mitra kerja sama fakultas FMIKOM.",
		name: "Siti Rahmawati",
		role: "Alumni Angkatan 2022",
		avatar: "https://api.dicebear.com/7.x/avataaars/svg?seed=a04258a2462d826712d",
		theme: "dark",
	},
];

const hasGalleryError = computed(() => {
	if (!props.errors) return false;
	return Object.keys(props.errors).some(key => key === 'hero_gallery_files' || key.startsWith('hero_gallery_files.'));
});

const galleryError = computed(() => {
	if (!props.errors) return "";
	const key = Object.keys(props.errors).find(k => k === 'hero_gallery_files' || k.startsWith('hero_gallery_files.'));
	return key ? props.errors[key] : "";
});

const hasPartnerError = computed(() => {
	if (!props.errors) return false;
	return Object.keys(props.errors).some(key => key === 'partner_files' || key.startsWith('partner_files.'));
});

const partnerError = computed(() => {
	if (!props.errors) return "";
	const key = Object.keys(props.errors).find(k => k === 'partner_files' || k.startsWith('partner_files.'));
	return key ? props.errors[key] : "";
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
	show_testimonials: props.settings.show_testimonials !== "0",
	show_events: props.settings.show_events !== "0",
	show_posts: props.settings.show_posts !== "0",
	show_showcase: props.settings.show_showcase !== "0",
	show_alumni: props.settings.show_alumni !== "0",

	// Content
	hero_title:
		props.settings.hero_title || "Satu Portal untuk \nSemua Layanan \nFMIKOM",
	hero_subtitle: props.settings.hero_subtitle || "Sistem Informasi Terpadu",
	hero_description:
		props.settings.hero_description ||
		"Kelola administrasi, magang, alumni, dan portofolio dalam satu sistem terintegrasi.",
	primary_color: props.settings.primary_color || "#2563eb",

	testimonials_title: props.settings.testimonials_title || "Apa Kata Mereka",
	testimonials_subtitle: props.settings.testimonials_subtitle || "Pengalaman & Testimoni Civitas Akademika FMIKOM",
	testimonials: (() => {
		const v = props.settings.testimonials;
		if (Array.isArray(v)) return [...v];
		try {
			const parsed = JSON.parse(v || "[]");
			return Array.isArray(parsed) && parsed.length > 0 ? parsed : [...defaultTestimonialsList];
		} catch {
			return [...defaultTestimonialsList];
		}
	})(),

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

watch(() => props.settings, (newSettings) => {
	if (!newSettings) return;

	form.hero_title = newSettings.hero_title || "Satu Portal untuk \nSemua Layanan \nFMIKOM";
	form.hero_subtitle = newSettings.hero_subtitle || "Sistem Informasi Terpadu";
	form.hero_description = newSettings.hero_description || "Kelola administrasi, magang, alumni, dan portofolio dalam satu sistem terintegrasi.";
	form.show_navbar = newSettings.show_navbar !== "0";
	form.show_hero = newSettings.show_hero !== "0";
	form.show_features = newSettings.show_features !== "0";
	form.show_partners = newSettings.show_partners !== "0";
	form.show_benefits = newSettings.show_benefits !== "0";
	form.show_testimonials = newSettings.show_testimonials !== "0";
	form.testimonials_title = newSettings.testimonials_title || "Apa Kata Mereka";
	form.testimonials_subtitle = newSettings.testimonials_subtitle || "Pengalaman & Testimoni Civitas Akademika FMIKOM";
	form.primary_color = newSettings.primary_color || "#2563eb";

	form.benefits_title = newSettings.benefits_title || "Mengapa Memilih Portal FMIKOM?";
	form.benefits_subtitle = newSettings.benefits_subtitle || "Platform digital terpadu yang dirancang khusus untuk kebutuhan civitas akademika FMIKOM.";
	form.benefit_1_title = newSettings.benefit_1_title || "Akses Mudah";
	form.benefit_1_desc = newSettings.benefit_1_desc || "Satu platform untuk semua layanan akademik dan administratif.";
	form.benefit_2_title = newSettings.benefit_2_title || "Data Real-Time";
	form.benefit_2_desc = newSettings.benefit_2_desc || "Informasi selalu terkini dan akurat langsung dari sumbernya.";
	form.benefit_3_title = newSettings.benefit_3_title || "Keamanan Tinggi";
	form.benefit_3_desc = newSettings.benefit_3_desc || "Sistem SSO dengan proteksi berlapis untuk menjaga keamanan data.";

	if (Array.isArray(newSettings.hero_gallery)) {
		form.hero_gallery = [...newSettings.hero_gallery];
	} else {
		try {
			form.hero_gallery = JSON.parse(newSettings.hero_gallery || "[]");
		} catch {
			form.hero_gallery = [];
		}
	}

	if (Array.isArray(newSettings.partners)) {
		form.partners = [...newSettings.partners];
	} else {
		try {
			form.partners = JSON.parse(newSettings.partners || "[]");
		} catch {
			form.partners = [];
		}
	}
}, { deep: true });

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
		const allowedTypes = ["image/png", "image/jpeg", "image/jpg", "image/webp"];
		const maxSize = 5 * 1024 * 1024; // 5MB

		for (const file of files) {
			if (!allowedTypes.includes(file.type)) {
				alert(`File "${file.name}" tidak diizinkan. Hanya mendukung format PNG, JPG, JPEG, dan WEBP.`);
				target.value = "";
				return;
			}
			if (file.size > maxSize) {
				alert(`File "${file.name}" melebihi ukuran maksimum 5MB.`);
				target.value = "";
				return;
			}
		}

		newHeroFiles.value.push(...files);
		files.forEach((f) => { newHeroFilePreviews.value.push(URL.createObjectURL(f)); });
		target.value = "";
	}
};

const handlePartnerUpload = (event: Event) => {
	const target = event.target as HTMLInputElement;
	if (target.files) {
		const files = Array.from(target.files);
		const allowedTypes = ["image/png", "image/jpeg", "image/jpg", "image/webp", "image/svg+xml"];
		const maxSize = 5 * 1024 * 1024; // 5MB

		for (const file of files) {
			if (!allowedTypes.includes(file.type)) {
				alert(`File "${file.name}" tidak diizinkan. Hanya mendukung format PNG, JPG, JPEG, WEBP, dan SVG.`);
				target.value = "";
				return;
			}
			if (file.size > maxSize) {
				alert(`File "${file.name}" melebihi ukuran maksimum 5MB.`);
				target.value = "";
				return;
			}
		}

		newPartnerFiles.value.push(...files);
		files.forEach((f) => { newPartnerFilePreviews.value.push(URL.createObjectURL(f)); });
		target.value = "";
	}
};

const removeExistingGallery = (url: string) => {
	form.remove_hero_gallery.push(url);
	form.hero_gallery = form.hero_gallery.filter((u: string) => u !== url);
};

const removeExistingPartner = (url: string) => {
	form.remove_partners.push(url);
	form.partners = form.partners.filter((u: any) => {
		const uUrl = typeof u === 'object' ? u.logo : u;
		return uUrl !== url;
	});
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
		| "show_benefits"
		| "show_testimonials"
		| "show_events"
		| "show_posts"
		| "show_showcase"
		| "show_alumni",
) => {
	form[key] = !form[key];
};

// Testimonial Item Form State & Helper Functions
const isEditingTestimonialItem = ref(false);
const editingTestimonialIndex = ref<number | null>(null);
const avatarInput = ref<HTMLInputElement | null>(null);
const testimonialItemForm = reactive({
	name: "",
	role: "",
	quote: "",
	avatar: "",
	theme: "light" as "light" | "dark",
});

const resetTestimonialItemForm = () => {
	testimonialItemForm.name = "";
	testimonialItemForm.role = "";
	testimonialItemForm.quote = "";
	testimonialItemForm.avatar = "";
	testimonialItemForm.theme = "light";
	isEditingTestimonialItem.value = false;
	editingTestimonialIndex.value = null;
};

const startAddTestimonialItem = () => {
	resetTestimonialItemForm();
	isEditingTestimonialItem.value = true;
	editingTestimonialIndex.value = null;
};

const startEditTestimonialItem = (index: number) => {
	const item = form.testimonials[index];
	if (!item) return;
	testimonialItemForm.name = item.name || "";
	testimonialItemForm.role = item.role || "";
	testimonialItemForm.quote = item.quote || "";
	testimonialItemForm.avatar = item.avatar || "";
	testimonialItemForm.theme = item.theme === "dark" ? "dark" : "light";
	isEditingTestimonialItem.value = true;
	editingTestimonialIndex.value = index;
};

const saveTestimonialItem = () => {
	if (!testimonialItemForm.name.trim() || !testimonialItemForm.quote.trim()) {
		alert("Mohon isi Nama dan Teks Kutipan Testimoni.");
		return;
	}

	const newItem = {
		id: editingTestimonialIndex.value !== null && form.testimonials[editingTestimonialIndex.value]?.id
			? form.testimonials[editingTestimonialIndex.value].id
			: String(Date.now()),
		name: testimonialItemForm.name.trim(),
		role: testimonialItemForm.role.trim() || "Mahasiswa / Alumni",
		quote: testimonialItemForm.quote.trim(),
		avatar: testimonialItemForm.avatar.trim() || `https://api.dicebear.com/7.x/avataaars/svg?seed=${encodeURIComponent(testimonialItemForm.name.trim())}`,
		theme: testimonialItemForm.theme,
	};

	if (editingTestimonialIndex.value !== null) {
		form.testimonials[editingTestimonialIndex.value] = newItem;
	} else {
		form.testimonials.push(newItem);
	}

	resetTestimonialItemForm();
};

const deleteTestimonialItem = (index: number) => {
	if (confirm(`Apakah Anda yakin ingin menghapus testimoni dari "${form.testimonials[index]?.name}"?`)) {
		form.testimonials.splice(index, 1);
		if (editingTestimonialIndex.value === index) {
			resetTestimonialItemForm();
		}
	}
};

const moveTestimonialItem = (index: number, direction: "up" | "down") => {
	const newIndex = direction === "up" ? index - 1 : index + 1;
	if (newIndex < 0 || newIndex >= form.testimonials.length) return;
	const temp = form.testimonials[index];
	form.testimonials[index] = form.testimonials[newIndex];
	form.testimonials[newIndex] = temp;
};

const handleAvatarUpload = (event: Event) => {
	const target = event.target as HTMLInputElement;
	if (target.files && target.files[0]) {
		const file = target.files[0];
		const reader = new FileReader();
		reader.onload = (e) => {
			if (e.target?.result) {
				testimonialItemForm.avatar = e.target.result as string;
			}
		};
		reader.readAsDataURL(file);
	}
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
	formData.append("show_testimonials", form.show_testimonials ? "1" : "0");
	formData.append("show_events", form.show_events ? "1" : "0");
	formData.append("show_posts", form.show_posts ? "1" : "0");
	formData.append("show_showcase", form.show_showcase ? "1" : "0");
	formData.append("show_alumni", form.show_alumni ? "1" : "0");
	formData.append("testimonials_title", form.testimonials_title);
	formData.append("testimonials_subtitle", form.testimonials_subtitle);
	formData.append("testimonials", JSON.stringify(form.testimonials));
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

                <!-- SECTION: GALLERY (Galeri dan Event) -->
                <div class="bg-[#f8fafc] dark:bg-slate-900/50 rounded-2xl p-4 border border-dashed border-blue-300 dark:border-blue-700">
                    <div class="text-[11px] font-black tracking-widest text-blue-400 uppercase mb-3 ml-2 flex items-center gap-2">
                        <ImageIcon class="w-3.5 h-3.5"/>
                        Galeri dan Event (Hero)
                    </div>
                    
                    <div class="bg-white dark:bg-slate-800 border border-blue-100 dark:border-slate-600 rounded-xl p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex flex-col sm:flex-row items-start gap-4 flex-1">
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
                                <div class="flex-1">
                                    <h4 class="text-[14px] font-bold text-slate-800 dark:text-slate-200">Galeri dan Event</h4>
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
                        
                        <!-- WIDGET: EVENT TIMELINE -->
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl p-4 flex items-center justify-between shadow-sm transition-all hover:shadow-md hover:border-slate-300 dark:hover:border-slate-500">
                            <div class="flex items-center gap-4">
                                <button @click="toggleVisibility('show_events')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300" title="Sembunyikan/Tampilkan">
                                    <Eye v-if="form.show_events" class="w-5 h-5"/>
                                    <EyeOff v-else class="w-5 h-5 text-rose-500"/>
                                </button>
                                <div>
                                    <h4 :class="['text-[14px] font-bold', form.show_events ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 line-through']">Event & Agenda Timeline</h4>
                                    <p class="text-[12px] font-bold text-slate-500 mt-0.5">Otomatis dari Data Event FMIKOM</p>
                                </div>
                            </div>
                        </div>

                        <!-- WIDGET: SHOWCASE PORTOFOLIO -->
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl p-4 flex items-center justify-between shadow-sm transition-all hover:shadow-md hover:border-slate-300 dark:hover:border-slate-500">
                            <div class="flex items-center gap-4">
                                <button @click="toggleVisibility('show_showcase')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300" title="Sembunyikan/Tampilkan">
                                    <Eye v-if="form.show_showcase" class="w-5 h-5"/>
                                    <EyeOff v-else class="w-5 h-5 text-rose-500"/>
                                </button>
                                <div>
                                    <h4 :class="['text-[14px] font-bold', form.show_showcase ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 line-through']">Portofolio Showcase Mahasiswa</h4>
                                    <p class="text-[12px] font-bold text-slate-500 mt-0.5">Pengaturan Karya Unggulan (Modul PAGI)</p>
                                </div>
                            </div>
                        </div>

                        <!-- WIDGET: PETA ALUMNI -->
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl p-4 flex items-center justify-between shadow-sm transition-all hover:shadow-md hover:border-slate-300 dark:hover:border-slate-500">
                            <div class="flex items-center gap-4">
                                <button @click="toggleVisibility('show_alumni')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300" title="Sembunyikan/Tampilkan">
                                    <Eye v-if="form.show_alumni" class="w-5 h-5"/>
                                    <EyeOff v-else class="w-5 h-5 text-rose-500"/>
                                </button>
                                <div>
                                    <h4 :class="['text-[14px] font-bold', form.show_alumni ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 line-through']">Peta Sebaran Alumni & Statistik</h4>
                                    <p class="text-[12px] font-bold text-slate-500 mt-0.5">Seksi Visual Peta & Statistik Tracer</p>
                                </div>
                            </div>
                        </div>

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
                                    <div v-for="logo in form.partners" :key="typeof logo === 'object' ? logo.logo : logo"
                                        class="relative group h-10 w-[72px] rounded-lg overflow-hidden border border-slate-100 bg-white flex items-center justify-center p-1 shadow-sm">
                                        <img :src="typeof logo === 'object' ? logo.logo : logo" alt="Partner logo" class="max-w-full max-h-full object-contain">
                                        <button @click="removeExistingPartner(typeof logo === 'object' ? logo.logo : logo)"
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

                        <!-- WIDGET: TESTIMONIALS (Apa Kata Mereka) -->
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl p-4 flex items-center justify-between shadow-sm transition-all hover:shadow-md hover:border-slate-300 dark:hover:border-slate-500">
                            <div class="flex items-center gap-4">
                                <button @click="toggleVisibility('show_testimonials')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300" title="Sembunyikan/Tampilkan">
                                    <Eye v-if="form.show_testimonials" class="w-5 h-5"/>
                                    <EyeOff v-else class="w-5 h-5 text-rose-500"/>
                                </button>
                                <div>
                                    <h4 :class="['text-[14px] font-bold', form.show_testimonials ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 line-through']">Apa Kata Mereka (Testimoni)</h4>
                                    <p class="text-[12px] font-bold text-slate-500 mt-0.5">Gadget Testimoni Civitas Akademika & Alumni</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="openEditModal('testimonials')" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-[#2563EB] hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors border border-slate-200 dark:border-slate-600">
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
                        <span v-if="editingWidget === 'gallery'">Galeri dan Event</span>
                        <span v-if="editingWidget === 'theme'">Warna Tema Utama</span>
                        <span v-if="editingWidget === 'benefits'">Seksi Keunggulan</span>
                        <span v-if="editingWidget === 'testimonials'">Apa Kata Mereka (Testimoni)</span>
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
                    </div>

                    <div v-if="editingWidget === 'partners'" class="space-y-5">
                        <!-- Validation error alert if any -->
                        <div v-if="hasPartnerError" class="bg-rose-50/80 dark:bg-rose-950/20 border border-rose-200/80 dark:border-rose-800 rounded-2xl p-4 flex gap-3 shadow-sm">
                            <div class="text-rose-500 shrink-0 mt-0.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-[13px] font-bold text-rose-800 dark:text-rose-300">Gagal Mengunggah Logo</h4>
                                <p class="text-[11px] font-bold text-rose-700 dark:text-rose-400 mt-1">
                                    {{ partnerError }}
                                </p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[12px] font-bold text-slate-600 dark:text-slate-400 mb-3">Logo Mitra & Kerjasama</label>
                            
                            <!-- Existing Logos -->
                            <div v-if="form.partners.length > 0" class="mb-4">
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Sudah Diunggah</p>
                                <div class="grid grid-cols-4 gap-2">
                                    <div v-for="img in form.partners" :key="typeof img === 'object' ? img.logo : img" class="relative group aspect-video rounded-lg overflow-hidden border border-slate-200 bg-white flex items-center justify-center p-2">
                                        <img :src="typeof img === 'object' ? img.logo : img" alt="Partner logo preview" class="max-w-full max-h-full object-contain">
                                        <button @click="removeExistingPartner(typeof img === 'object' ? img.logo : img)" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
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
                            <div 
                                @click="partnerInput?.click()" 
                                class="flex flex-col items-center justify-center border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-indigo-500 dark:hover:border-indigo-500 bg-slate-50/50 dark:bg-slate-900/30 hover:bg-indigo-50/10 rounded-xl p-5 cursor-pointer transition-all text-center group"
                            >
                                <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center text-slate-500 group-hover:bg-indigo-50 dark:group-hover:bg-indigo-950/30 group-hover:text-indigo-600 transition-colors mb-2">
                                    <Upload class="w-4 h-4"/>
                                </div>
                                <span class="text-[12px] font-bold text-slate-700 dark:text-zinc-300">Klik untuk unggah logo mitra</span>
                                <span class="text-[10px] text-slate-400 dark:text-zinc-500 mt-1">Rekomendasi rasio: <strong>Lanskap (16:9 / 4:3)</strong>, resolusi <strong>600x300 px</strong></span>
                                <span class="text-[9px] text-slate-400 dark:text-zinc-600 mt-0.5">Mendukung format PNG, JPG, JPEG, WEBP, SVG (maks. 5MB)</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="editingWidget === 'testimonials'" class="space-y-6">
                        <!-- HEADER SETTINGS -->
                        <div class="grid grid-cols-1 gap-4 p-4 bg-slate-50 dark:bg-slate-900/40 rounded-xl border border-slate-200 dark:border-slate-700">
                            <h4 class="text-[13px] font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                <Sparkles class="w-4 h-4 text-blue-500"/>
                                Pengaturan Header Seksi
                            </h4>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Judul Seksi Utama</label>
                                <input v-model="form.testimonials_title" type="text" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-[13px] font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-[#2563EB] outline-none" placeholder="Contoh: Apa Kata Mereka" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Sub-Judul / Deskripsi</label>
                                <textarea v-model="form.testimonials_subtitle" rows="2" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-[13px] font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-[#2563EB] outline-none resize-none" placeholder="Contoh: Pengalaman dan testimoni nyata dari mahasiswa, dosen, dan alumni."></textarea>
                            </div>
                        </div>

                        <!-- TESTIMONIAL ITEM FORM (ADD / EDIT INLINE PANEL) -->
                        <div v-if="isEditingTestimonialItem" class="p-4 bg-blue-50/60 dark:bg-blue-950/20 border-2 border-blue-200 dark:border-blue-800 rounded-xl space-y-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <h4 class="text-[13px] font-black text-blue-700 dark:text-blue-300 flex items-center gap-2">
                                    <UserCheck class="w-4 h-4"/>
                                    {{ editingTestimonialIndex !== null ? 'Edit Testimoni' : 'Tambah Testimoni Baru' }}
                                </h4>
                                <button @click="resetTestimonialItemForm" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                    <X class="w-4 h-4"/>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Pemberi Testimoni *</label>
                                    <input v-model="testimonialItemForm.name" type="text" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-[12px] font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-[#2563EB] outline-none" placeholder="Contoh: Andi Saputra" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Jabatan / Peran *</label>
                                    <input v-model="testimonialItemForm.role" type="text" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-[12px] font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-[#2563EB] outline-none" placeholder="Contoh: Mahasiswa Semester 6" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Gaya Kartu (Tampilan)</label>
                                <div class="flex items-center gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer text-[12px] font-bold text-slate-700 dark:text-slate-300">
                                        <input type="radio" v-model="testimonialItemForm.theme" value="light" class="text-blue-600 focus:ring-blue-500" />
                                        <span>Kartu Terang (Standard)</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer text-[12px] font-bold text-slate-700 dark:text-slate-300">
                                        <input type="radio" v-model="testimonialItemForm.theme" value="dark" class="text-blue-600 focus:ring-blue-500" />
                                        <span class="bg-slate-900 text-white px-2 py-0.5 rounded text-[10px]">Kartu Gelap (Highlight)</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Teks Kutipan Testimoni *</label>
                                <textarea v-model="testimonialItemForm.quote" rows="3" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-[12px] font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-[#2563EB] outline-none resize-none" placeholder="Tuliskan pengalaman atau pendapat..."></textarea>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Foto Avatar (Upload atau URL)</label>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full overflow-hidden shrink-0 border border-slate-200 bg-slate-100 flex items-center justify-center">
                                        <img v-if="testimonialItemForm.avatar" :src="testimonialItemForm.avatar" class="w-full h-full object-cover" />
                                        <UserCheck v-else class="w-5 h-5 text-slate-400" />
                                    </div>
                                    <input v-model="testimonialItemForm.avatar" type="text" class="flex-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-[11px] font-medium text-slate-800 dark:text-slate-200 outline-none" placeholder="URL Foto Avatar (https://...)" />
                                    <input type="file" ref="avatarInput" accept="image/*" class="hidden" @change="handleAvatarUpload" />
                                    <button @click="avatarInput?.click()" type="button" class="px-3 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-[11px] font-bold rounded-lg transition-colors flex items-center gap-1 shrink-0">
                                        <Upload class="w-3.5 h-3.5" />
                                        Unggah
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2 border-t border-blue-200 dark:border-blue-800">
                                <button @click="resetTestimonialItemForm" type="button" class="px-3 py-1.5 text-[12px] font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
                                    Batal
                                </button>
                                <button @click="saveTestimonialItem" type="button" class="px-4 py-1.5 bg-[#2563EB] hover:bg-blue-600 text-white text-[12px] font-bold rounded-lg shadow-sm transition-all">
                                    {{ editingTestimonialIndex !== null ? 'Perbarui Testimoni' : 'Tambah Testimoni' }}
                                </button>
                            </div>
                        </div>

                        <!-- TESTIMONIALS LIST (CRUD MANAGER) -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-[13px] font-bold text-slate-800 dark:text-slate-200">
                                    Daftar Testimoni ({{ form.testimonials.length }} Kartu)
                                </h4>
                                <button v-if="!isEditingTestimonialItem" @click="startAddTestimonialItem" type="button" class="px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 text-[#2563EB] dark:text-blue-400 border border-blue-200 dark:border-blue-800 rounded-lg text-[12px] font-bold transition-all flex items-center gap-1.5">
                                    <Plus class="w-3.5 h-3.5" />
                                    Tambah Testimoni Baru
                                </button>
                            </div>

                            <div v-if="form.testimonials.length === 0" class="p-6 text-center border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl text-slate-400 text-[12px]">
                                Belum ada testimoni. Klik <strong>"Tambah Testimoni Baru"</strong> untuk menambahkan.
                            </div>

                            <div class="space-y-2 max-h-[300px] overflow-y-auto pr-1">
                                <div
                                    v-for="(item, index) in form.testimonials"
                                    :key="item.id || index"
                                    class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-3 flex items-center justify-between gap-3 shadow-xs hover:border-slate-300 dark:hover:border-slate-600 transition-all"
                                >
                                    <div class="flex items-center gap-3 flex-1 min-w-0">
                                        <div class="w-9 h-9 rounded-full overflow-hidden shrink-0 bg-slate-100 border border-slate-200 flex items-center justify-center">
                                            <img v-if="item.avatar" :src="item.avatar" :alt="item.name" class="w-full h-full object-cover" />
                                            <span v-else class="text-xs font-bold text-slate-600">{{ item.name?.charAt(0) }}</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <h5 class="text-[13px] font-bold text-slate-800 dark:text-slate-200 truncate">{{ item.name }}</h5>
                                                <span v-if="item.theme === 'dark'" class="bg-slate-900 text-white text-[9px] font-bold px-1.5 py-0.5 rounded shrink-0">Kartu Gelap</span>
                                                <span v-else class="bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 text-[9px] font-bold px-1.5 py-0.5 rounded shrink-0 border border-slate-200 dark:border-slate-700">Kartu Terang</span>
                                            </div>
                                            <p class="text-[11px] font-medium text-slate-400 truncate">{{ item.role }} — "{{ item.quote }}"</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1 shrink-0">
                                        <button @click="moveTestimonialItem(index, 'up')" :disabled="index === 0" class="p-1 text-slate-400 hover:text-slate-600 disabled:opacity-30">
                                            <ArrowUp class="w-3.5 h-3.5" />
                                        </button>
                                        <button @click="moveTestimonialItem(index, 'down')" :disabled="index === form.testimonials.length - 1" class="p-1 text-slate-400 hover:text-slate-600 disabled:opacity-30">
                                            <ArrowDown class="w-3.5 h-3.5" />
                                        </button>
                                        <button @click="startEditTestimonialItem(index)" class="p-1.5 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-md transition-colors" title="Edit Testimoni">
                                            <Edit2 class="w-3.5 h-3.5" />
                                        </button>
                                        <button @click="deleteTestimonialItem(index)" class="p-1.5 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-md transition-colors" title="Hapus Testimoni">
                                            <Trash2 class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
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
                        <!-- Validation error alert if any -->
                        <div v-if="hasGalleryError" class="bg-rose-50/80 dark:bg-rose-950/20 border border-rose-200/80 dark:border-rose-800 rounded-2xl p-4 flex gap-3 shadow-sm">
                            <div class="text-rose-500 shrink-0 mt-0.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-[13px] font-bold text-rose-800 dark:text-rose-300">Gagal Mengunggah Gambar</h4>
                                <p class="text-[11px] font-bold text-rose-700 dark:text-rose-400 mt-1">
                                    {{ galleryError }}
                                </p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[12px] font-bold text-slate-600 dark:text-slate-400 mb-1">Gambar Galeri dan Event</label>
                            <p class="text-[11px] text-slate-400 mb-4">Upload gambar galeri dan event. Akan muncul sebagai card stack interaktif di bagian atas Landing Page. Bisa lebih dari satu gambar.</p>
                        </div>

                        <!-- Existing Images Grid -->
                        <div v-if="form.hero_gallery.length > 0">
                            <p class="text-[11px] font-black text-slate-400 uppercase tracking-wider mb-3">Gambar Tersimpan ({{ form.hero_gallery.length }})</p>
                            <div class="grid grid-cols-3 gap-3">
                                <div v-for="img in form.hero_gallery" :key="img" class="relative group aspect-square rounded-xl overflow-hidden border border-slate-200 shadow-sm">
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
                                <div v-for="(preview, i) in newHeroFilePreviews" :key="i" class="relative group aspect-square rounded-xl overflow-hidden border-2 border-blue-300 shadow-sm">
                                    <img :src="preview" alt="Gallery image preview" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all flex items-center justify-center">
                                        <button @click="removeNewHero(i)" class="opacity-0 group-hover:opacity-100 bg-red-500 text-white rounded-full p-1.5 transition-opacity shadow-md">
                                            <X class="w-4 h-4"/>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Click Area -->
                        <div class="border-t border-slate-100 dark:border-slate-700 pt-5">
                            <div 
                                @click="heroGalleryInput?.click()" 
                                class="flex flex-col items-center justify-center border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-indigo-500 dark:hover:border-indigo-500 bg-slate-50/50 dark:bg-slate-900/30 hover:bg-indigo-50/10 rounded-xl p-5 cursor-pointer transition-all text-center group"
                            >
                                <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center text-slate-500 group-hover:bg-indigo-50 dark:group-hover:bg-indigo-950/30 group-hover:text-indigo-600 transition-colors mb-2">
                                    <Upload class="w-4 h-4"/>
                                </div>
                                <span class="text-[12px] font-bold text-slate-700 dark:text-zinc-300">Klik untuk unggah gambar</span>
                                <span class="text-[10px] text-slate-400 dark:text-zinc-500 mt-1">Rekomendasi rasio: <strong>1:1 (Persegi)</strong>, resolusi <strong>800x800 px</strong></span>
                                <span class="text-[9px] text-slate-400 dark:text-zinc-600 mt-0.5">Mendukung format PNG, JPG, JPEG, WEBP (maks. 5MB)</span>
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
