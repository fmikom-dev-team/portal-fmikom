<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import Cropper from "cropperjs";
import "cropperjs/dist/cropper.css";
import {
	AlertTriangle,
	ArrowDown,
	ArrowUp,
	Check,
	CheckCircle2,
	ChevronDown,
	Crop,
	Edit2,
	Eye,
	EyeOff,
	Image as ImageIcon,
	Layers,
	MessageSquareQuote,
	Move,
	Palette,
	Plus,
	RefreshCw,
	RotateCcw,
	RotateCw,
	Save,
	Sparkles,
	Trash2,
	Upload,
	UserCheck,
	X,
	ZoomIn,
	ZoomOut,
} from "lucide-vue-next";
import { reactive, ref, computed, watch, nextTick, onUnmounted } from "vue";
import { showToast } from "@/composables/useGlobalToast";
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

const hasTestimonialError = computed(() => {
	if (!props.errors) return false;
	return Object.keys(props.errors).some(key => key === 'testimonials' || key.startsWith('testimonial_avatar_files'));
});

const testimonialError = computed(() => {
	if (!props.errors) return "";
	const key = Object.keys(props.errors).find(k => k === 'testimonials' || k.startsWith('testimonial_avatar_files'));
	return key ? props.errors[key] : "";
});

const form = reactive<Record<string, any>>({
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

	// Hero Section
	hero_title:
		props.settings.hero_title ||
		"Satu Portal untuk Semua Layanan FMIKOM",
	hero_subtitle:
		props.settings.hero_subtitle || "Sistem Informasi Terpadu",
	hero_description:
		props.settings.hero_description ||
		"Kelola administrasi, magang, alumni, dan portofolio dalam satu sistem terintegrasi. Dibangun untuk memberikan pengalaman terbaik bergaya modern.",
	hero_gallery: Array.isArray(props.settings.hero_gallery)
		? [...props.settings.hero_gallery]
		: [],
	remove_hero_gallery: [] as string[],

	// Partners
	partners: Array.isArray(props.settings.partners)
		? [...props.settings.partners]
		: [],
	remove_partners: [] as string[],

	// Testimonials Section Header
	testimonials_title:
		props.settings.testimonials_title || "Apa Kata Mereka",
	testimonials_subtitle:
		props.settings.testimonials_subtitle ||
		"Pengalaman dan testimoni nyata dari civitas akademika FMIKOM.",
	testimonials: (() => {
		if (props.settings.testimonials) {
			if (Array.isArray(props.settings.testimonials)) {
				return [...props.settings.testimonials];
			}
			try {
				const parsed = JSON.parse(props.settings.testimonials);
				return Array.isArray(parsed) ? parsed : defaultTestimonialsList;
			} catch {
				return defaultTestimonialsList;
			}
		}
		return defaultTestimonialsList;
	})() as any[],

	// Theme
	primary_color: props.settings.primary_color || "#2563EB",

	// Benefits Section
	benefits_title:
		props.settings.benefits_title ||
		"Kenapa Menggunakan Portal FMIKOM?",
	benefits_subtitle:
		props.settings.benefits_subtitle ||
		"Platform modern yang dirancang untuk efisiensi dan transparansi",
	benefit_1_title:
		props.settings.benefit_1_title || "Terintegrasi Penuh",
	benefit_1_desc:
		props.settings.benefit_1_desc ||
		"Semua modul saling terhubung mulai dari FAST, WIMS, hingga Tracer Study.",
	benefit_2_title:
		props.settings.benefit_2_title || "Akses Cepat & Mudah",
	benefit_2_desc:
		props.settings.benefit_2_desc ||
		"Antarmuka responsif dan ramah pengguna di semua perangkat mobile & desktop.",
	benefit_3_title:
		props.settings.benefit_3_title || "Keamanan Tinggi",
	benefit_3_desc:
		props.settings.benefit_3_desc ||
		"Sistem SSO dengan proteksi berlapis untuk menjaga keamanan data.",
});

watch(() => props.settings, (newSettings) => {
	if (!newSettings) return;
	form.show_navbar = newSettings.show_navbar !== "0";
	form.show_hero = newSettings.show_hero !== "0";
	form.show_features = newSettings.show_features !== "0";
	form.show_partners = newSettings.show_partners !== "0";
	form.show_benefits = newSettings.show_benefits !== "0";
	form.show_testimonials = newSettings.show_testimonials !== "0";
	form.show_events = newSettings.show_events !== "0";
	form.show_posts = newSettings.show_posts !== "0";
	form.show_showcase = newSettings.show_showcase !== "0";
	form.show_alumni = newSettings.show_alumni !== "0";

	form.hero_title = newSettings.hero_title || form.hero_title;
	form.hero_subtitle = newSettings.hero_subtitle || form.hero_subtitle;
	form.hero_description = newSettings.hero_description || form.hero_description;
	form.testimonials_title = newSettings.testimonials_title || form.testimonials_title;
	form.testimonials_subtitle = newSettings.testimonials_subtitle || form.testimonials_subtitle;
	form.primary_color = newSettings.primary_color || form.primary_color;
	form.benefits_title = newSettings.benefits_title || form.benefits_title;
	form.benefits_subtitle = newSettings.benefits_subtitle || form.benefits_subtitle;
	form.benefit_1_title = newSettings.benefit_1_title || "Terintegrasi Penuh";
	form.benefit_1_desc = newSettings.benefit_1_desc || "Semua modul saling terhubung mulai dari FAST, WIMS, hingga Tracer Study.";
	form.benefit_2_title = newSettings.benefit_2_title || "Akses Cepat & Mudah";
	form.benefit_2_desc = newSettings.benefit_2_desc || "Antarmuka responsif dan ramah pengguna di semua perangkat mobile & desktop.";
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

	if (newSettings.testimonials !== undefined && newSettings.testimonials !== null) {
		if (Array.isArray(newSettings.testimonials)) {
			form.testimonials = [...newSettings.testimonials];
		} else {
			try {
				const parsed = JSON.parse(newSettings.testimonials || "[]");
				form.testimonials = Array.isArray(parsed) ? [...parsed] : [];
			} catch {
				form.testimonials = [];
			}
		}
	}
}, { deep: true });

const newHeroFiles = ref<File[]>([]);
const newHeroFilePreviews = ref<string[]>([]);
const newPartnerFiles = ref<File[]>([]);
const newPartnerFilePreviews = ref<string[]>([]);

const heroGalleryInput = ref<HTMLInputElement | null>(null);
const partnerInput = ref<HTMLInputElement | null>(null);

const triggerToast = (message: string, type: "success" | "error" = "success") => {
	showToast(message, type);
};

const handleHeroUpload = (event: Event) => {
	const target = event.target as HTMLInputElement;
	if (target.files) {
		const files = Array.from(target.files);
		const allowedTypes = new Set(["image/png", "image/jpeg", "image/jpg", "image/webp"]);
		const maxSize = 25 * 1024 * 1024; 

		for (const file of files) {
			if (!allowedTypes.has(file.type)) {
				triggerToast(`Berkas "${file.name}" tidak didukung. Format harus berupa PNG, JPG, JPEG, atau WEBP.`, "error");
				target.value = "";
				return;
			}
			if (file.size > maxSize) {
				triggerToast(`Ukuran foto "${file.name}" melebihi batas maksimum 25MB.`, "error");
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
		const allowedTypes = new Set(["image/png", "image/jpeg", "image/jpg", "image/webp", "image/svg+xml"]);
		const maxSize = 15 * 1024 * 1024; 

		for (const file of files) {
			const isSvg = file.type === "image/svg+xml" || file.name.toLowerCase().endsWith(".svg");
			if (!allowedTypes.has(file.type) && !isSvg) {
				triggerToast(`Berkas logo "${file.name}" tidak didukung. Format harus berupa PNG, JPG, JPEG, WEBP, atau SVG.`, "error");
				target.value = "";
				return;
			}
			if (file.size > maxSize) {
				triggerToast(`Ukuran logo "${file.name}" melebihi batas maksimum 15MB.`, "error");
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
	if (widgetName === "testimonials") {
		activeTestimonialTab.value = "list";
		resetTestimonialItemForm();
	}
};

const closeEditModal = () => {
	editingWidget.value = null;
	resetTestimonialItemForm();
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

const confirmDisableModalOpen = ref(false);
const targetDisableKey = ref<string | null>(null);
const targetDisableLabel = ref<string>("");

const requestToggle = (key: string, label: string) => {
	if (form[key]) {
		targetDisableKey.value = key;
		targetDisableLabel.value = label;
		confirmDisableModalOpen.value = true;
	} else {
		form[key] = true;
	}
};

const confirmDisableSection = () => {
	if (targetDisableKey.value) {
		form[targetDisableKey.value] = false;
	}
	confirmDisableModalOpen.value = false;
	targetDisableKey.value = null;
};

const activeTestimonialTab = ref<"list" | "header">("list");
const isEditingTestimonialItem = ref(false);
const editingTestimonialIndex = ref<number | null>(null);
const avatarInput = ref<HTMLInputElement | null>(null);
const testimonialAvatarMode = ref<"upload" | "url">("upload");
const testimonialAvatarFileName = ref<string>("");
const testimonialAvatarFileSize = ref<string>("");
const testimonialAvatarPreview = ref<string>("");
const testimonialFormErrors = reactive({
	name: "",
	quote: "",
});

const presetRoles = [
	{
		category: "🎓 Mahasiswa & Organisasi",
		items: [
			"Mahasiswa",
			"Mahasiswa Semester 4",
			"Mahasiswa Semester 6",
			"Mahasiswa Tingkat Akhir",
			"Ketua BEM FMIKOM",
			"Ketua Himpunan Mahasiswa",
		]
	},
	{
		category: "👨‍🏫 Dosen & Pimpinan",
		items: [
			"Dosen Pembimbing",
			"Dosen Pengajar",
			"Kaprodi Sistem Informasi",
			"Kaprodi Teknik Informatika",
			"Dekan FMIKOM",
			"Wakil Dekan FMIKOM",
		]
	},
	{
		category: "💼 Alumni & Mitra Industri",
		items: [
			"Alumni Angkatan 2022",
			"Alumni Angkatan 2023",
			"Software Engineer (Alumni)",
			"HR Director / Mitra Industri",
			"CEO / Founder Mitra Bisnis",
		]
	}
];

const selectedPresetRole = ref<string>("");
const isCustomRole = ref<boolean>(false);

const onRolePresetChange = () => {
	if (selectedPresetRole.value === "custom") {
		isCustomRole.value = true;
		testimonialItemForm.role = "";
	} else if (selectedPresetRole.value) {
		isCustomRole.value = false;
		testimonialItemForm.role = selectedPresetRole.value;
	} else {
		isCustomRole.value = false;
		testimonialItemForm.role = "";
	}
};

const testimonialItemForm = reactive({
	name: "",
	role: "",
	quote: "",
	avatar: "",
	theme: "light" as "light" | "dark",
});

const pendingAvatarFiles = ref<Map<string, File>>(new Map());

const activeBlobUrls = ref<Set<string>>(new Set());

const revokeBlobUrl = (url: string) => {
	if (url && url.startsWith('blob:') && activeBlobUrls.value.has(url)) {
		URL.revokeObjectURL(url);
		activeBlobUrls.value.delete(url);
	}
};

const resetTestimonialItemForm = () => {
	testimonialItemForm.name = "";
	testimonialItemForm.role = "";
	testimonialItemForm.quote = "";
	testimonialItemForm.avatar = "";
	testimonialItemForm.theme = "light";
	testimonialAvatarFileName.value = "";
	testimonialAvatarFileSize.value = "";
	testimonialAvatarPreview.value = "";
	testimonialAvatarMode.value = "upload";
	testimonialFormErrors.name = "";
	testimonialFormErrors.quote = "";
	selectedPresetRole.value = "";
	isCustomRole.value = false;
	(testimonialItemForm as any)._pendingAvatarId = undefined;
	(testimonialItemForm as any)._pendingAvatarFile = undefined;
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
	testimonialItemForm.theme = item.theme === "dark" ? "dark" : "light";
	testimonialItemForm.avatar = item.avatar || "";
	testimonialAvatarPreview.value = item.avatar || item._previewUrl || "";
	testimonialAvatarFileName.value = "";
	testimonialAvatarFileSize.value = "";
	testimonialAvatarMode.value = (item.avatar && (item.avatar.startsWith("http://") || item.avatar.startsWith("https://"))) ? "url" : "upload";
	testimonialFormErrors.name = "";
	testimonialFormErrors.quote = "";

	// Check if role exists in preset list
	const allPresets = presetRoles.flatMap(p => p.items);
	if (item.role && allPresets.includes(item.role)) {
		selectedPresetRole.value = item.role;
		isCustomRole.value = false;
	} else if (item.role) {
		selectedPresetRole.value = "custom";
		isCustomRole.value = true;
	} else {
		selectedPresetRole.value = "";
		isCustomRole.value = false;
	}

	isEditingTestimonialItem.value = true;
	editingTestimonialIndex.value = index;
};

// Cropper State & Methods
const isCropperOpen = ref(false);
const cropperImageSrc = ref("");
const cropperImageRef = ref<HTMLImageElement | null>(null);
let cropperInstance: Cropper | null = null;
let currentCropRawFile: File | null = null;

const initCropper = () => {
	if (!cropperImageRef.value) return;
	if (cropperInstance) {
		cropperInstance.destroy();
	}
	cropperInstance = new Cropper(cropperImageRef.value, {
		aspectRatio: 1, // 1:1 circular/square avatar
		viewMode: 1,
		dragMode: "move",
		autoCropArea: 0.95,
		restore: false,
		guides: true,
		center: true,
		highlight: false,
		cropBoxMovable: true,
		cropBoxResizable: true,
		toggleDragModeOnDblclick: false,
		background: true,
	});
};

const destroyCropper = () => {
	if (cropperInstance) {
		cropperInstance.destroy();
		cropperInstance = null;
	}
};

const closeCropper = () => {
	destroyCropper();
	isCropperOpen.value = false;
	cropperImageSrc.value = "";
	currentCropRawFile = null;
};

const zoomInCropper = () => {
	cropperInstance?.zoom(0.1);
};

const zoomOutCropper = () => {
	cropperInstance?.zoom(-0.1);
};

const rotateLeftCropper = () => {
	cropperInstance?.rotate(-90);
};

const rotateRightCropper = () => {
	cropperInstance?.rotate(90);
};

const resetCropper = () => {
	cropperInstance?.reset();
};

onUnmounted(() => {
	destroyCropper();
});

const handleAvatarUpload = (event: Event) => {
	const target = event.target as HTMLInputElement;
	if (target.files && target.files[0]) {
		const file = target.files[0];
		const allowedTypes = ["image/png", "image/jpeg", "image/jpg", "image/webp"];
		const maxSize = 15 * 1024 * 1024; 

		if (!allowedTypes.includes(file.type)) {
			triggerToast(`Format gambar "${file.name}" tidak didukung. Harap gunakan format PNG, JPG, JPEG, atau WEBP.`, "error");
			target.value = "";
			return;
		}
		if (file.size > maxSize) {
			triggerToast(`Ukuran foto avatar "${file.name}" melebihi batas 15MB.`, "error");
			target.value = "";
			return;
		}

		currentCropRawFile = file;
		const reader = new FileReader();
		reader.onload = (e) => {
			if (e.target?.result) {
				cropperImageSrc.value = e.target.result as string;
				isCropperOpen.value = true;
				nextTick(() => {
					setTimeout(() => {
						initCropper();
					}, 150);
				});
			}
		};
		reader.readAsDataURL(file);
	}
	if (target) target.value = "";
};

const applyCroppedAvatar = () => {
	if (!cropperInstance || !currentCropRawFile) return;

	const canvas = cropperInstance.getCroppedCanvas({
		width: 500,
		height: 500,
		imageSmoothingEnabled: true,
		imageSmoothingQuality: "high",
	});

	if (canvas) {
		canvas.toBlob((blob) => {
			if (blob) {
				const rawName = currentCropRawFile?.name || "avatar.png";
				const cleanName = rawName.replace(/\.[^/.]+$/, "") + ".webp";
				const croppedFile = new File([blob], cleanName, {
					type: "image/webp",
				});

				const testimonialId = editingTestimonialIndex.value !== null && form.testimonials[editingTestimonialIndex.value]?.id
					? String(form.testimonials[editingTestimonialIndex.value].id)
					: 'item_' + Date.now();

				const blobUrl = URL.createObjectURL(croppedFile);
				activeBlobUrls.value.add(blobUrl);
				testimonialAvatarPreview.value = blobUrl;
				testimonialAvatarFileName.value = croppedFile.name;
				testimonialAvatarFileSize.value = (croppedFile.size / 1024).toFixed(1) + " KB";
				testimonialItemForm.avatar = ""; 

				pendingAvatarFiles.value.set(testimonialId, croppedFile);
				(testimonialItemForm as any)._pendingAvatarId = testimonialId;
				(testimonialItemForm as any)._pendingAvatarFile = croppedFile;

				triggerToast("Foto avatar berhasil dipotong dan disesuaikan.", "success");
				closeCropper();
			}
		}, "image/webp", 0.92);
	}
};

const removeSelectedAvatarFile = () => {
	testimonialAvatarPreview.value = "";
	testimonialAvatarFileName.value = "";
	testimonialAvatarFileSize.value = "";
	testimonialItemForm.avatar = "";
	const pendingId = (testimonialItemForm as any)._pendingAvatarId;
	if (pendingId) {
		pendingAvatarFiles.value.delete(pendingId);
	}
	(testimonialItemForm as any)._pendingAvatarId = undefined;
	(testimonialItemForm as any)._pendingAvatarFile = undefined;
};

const saveTestimonialItem = () => {
	testimonialFormErrors.name = "";
	testimonialFormErrors.quote = "";

	if (!testimonialItemForm.name.trim()) {
		testimonialFormErrors.name = "Nama pemberi testimoni wajib diisi.";
	}
	if (!testimonialItemForm.quote.trim()) {
		testimonialFormErrors.quote = "Teks kutipan testimoni wajib diisi.";
	}
	if (testimonialFormErrors.name || testimonialFormErrors.quote) {
		triggerToast("Mohon lengkapi nama dan kutipan testimoni.", "error");
		return;
	}

	const testimonialId = editingTestimonialIndex.value !== null && form.testimonials[editingTestimonialIndex.value]?.id
		? String(form.testimonials[editingTestimonialIndex.value].id)
		: 'item_' + Date.now();

	const pendingFile = (testimonialItemForm as any)._pendingAvatarFile as File | undefined;
	const previewUrl = testimonialAvatarPreview.value;

	let avatarValue = "";
	if (testimonialAvatarMode.value === "url" && testimonialItemForm.avatar.trim()) {
		avatarValue = testimonialItemForm.avatar.trim();
	} else if (!pendingFile && editingTestimonialIndex.value !== null && form.testimonials[editingTestimonialIndex.value]?.avatar) {
		avatarValue = form.testimonials[editingTestimonialIndex.value].avatar;
	}

	const newItem = {
		id: testimonialId,
		name: testimonialItemForm.name.trim(),
		role: testimonialItemForm.role.trim() || "Mahasiswa / Alumni",
		quote: testimonialItemForm.quote.trim(),
		avatar: avatarValue,
		_previewUrl: previewUrl,
		theme: testimonialItemForm.theme,
	};

	if (editingTestimonialIndex.value !== null) {
		form.testimonials[editingTestimonialIndex.value] = newItem;
	} else {
		form.testimonials.push(newItem);
	}

	if (pendingFile) {
		const pendingId = (testimonialItemForm as any)._pendingAvatarId as string | undefined;
		if (pendingId && pendingId !== testimonialId) {
			pendingAvatarFiles.value.delete(pendingId);
		}
		pendingAvatarFiles.value.set(testimonialId, pendingFile);
	}

	resetTestimonialItemForm();
	triggerToast("Item testimoni berhasil diperbarui di daftar.", "success");
};

const deleteTestimonialItem = (index: number) => {
	const item = form.testimonials[index];
	if (!item) return;
	if (item.id) {
		pendingAvatarFiles.value.delete(String(item.id));
	}
	if (item.avatar?.startsWith('blob:')) {
		revokeBlobUrl(item.avatar);
	}
	form.testimonials.splice(index, 1);
	if (editingTestimonialIndex.value === index) {
		resetTestimonialItemForm();
	}
	triggerToast("Testimoni dihapus dari daftar.", "success");
};

const moveTestimonialItem = (index: number, direction: "up" | "down") => {
	const newIndex = direction === "up" ? index - 1 : index + 1;
	if (newIndex < 0 || newIndex >= form.testimonials.length) return;
	const temp = form.testimonials[index];
	form.testimonials[index] = form.testimonials[newIndex];
	form.testimonials[newIndex] = temp;
};

const onAvatarImageError = (event: Event) => {
	const target = event.target as HTMLImageElement;
	if (target) {
		target.style.display = "none";
		const parent = target.parentElement;
		if (parent) {
			parent.classList.add("bg-gradient-to-br", "from-blue-600", "to-indigo-700", "text-white", "flex", "items-center", "justify-center", "font-black");
		}
	}
};

const submit = () => {
	if (isEditingTestimonialItem.value) {
		if (testimonialItemForm.name.trim() && testimonialItemForm.quote.trim()) {
			saveTestimonialItem();
		} else if (testimonialItemForm.name.trim() || testimonialItemForm.quote.trim()) {
			triggerToast("Mohon lengkapi Nama dan Teks Kutipan Testimoni sebelum menyimpan.", "error");
			return;
		}
	}

	const formData = new FormData();

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

	const cleanTestimonials = form.testimonials.map((t: any) => ({
		id: t.id,
		name: t.name,
		role: t.role,
		quote: t.quote,
		avatar: t.avatar && !t.avatar.startsWith('blob:') ? t.avatar : '',
		theme: t.theme || 'light',
	}));
	formData.append("testimonials", JSON.stringify(cleanTestimonials));

	formData.append("benefits_title", form.benefits_title);
	formData.append("benefits_subtitle", form.benefits_subtitle);
	formData.append("benefit_1_title", form.benefit_1_title);
	formData.append("benefit_1_desc", form.benefit_1_desc);
	formData.append("benefit_2_title", form.benefit_2_title);
	formData.append("benefit_2_desc", form.benefit_2_desc);
	formData.append("benefit_3_title", form.benefit_3_title);
	formData.append("benefit_3_desc", form.benefit_3_desc);

	newHeroFiles.value.forEach((f) => { formData.append("hero_gallery_files[]", f); });

	newPartnerFiles.value.forEach((f) => { formData.append("partner_files[]", f); });

	pendingAvatarFiles.value.forEach((file, testimonialId) => {
		formData.append(`testimonial_avatar_files[${testimonialId}]`, file);
	});

	form.remove_hero_gallery.forEach((url: string) => { formData.append("remove_hero_gallery[]", url); });
	form.remove_partners.forEach((url: string) => { formData.append("remove_partners[]", url); });

	formData.append("_method", "POST");

	isProcessing.value = true;
	router.post("/portal-admin/appearance", formData, {
		preserveScroll: true,
		forceFormData: true,
		onSuccess: (page: any) => {
			isSuccess.value = true;
			isProcessing.value = false;
			newHeroFiles.value = [];
			newHeroFilePreviews.value = [];
			newPartnerFiles.value = [];
			newPartnerFilePreviews.value = [];
			form.remove_hero_gallery = [];
			form.remove_partners = [];
			pendingAvatarFiles.value.clear();

			const freshSettings = page?.props?.settings || props.settings;
			if (freshSettings?.testimonials) {
				try {
					const parsed = typeof freshSettings.testimonials === 'string'
						? JSON.parse(freshSettings.testimonials)
						: freshSettings.testimonials;
					if (Array.isArray(parsed)) {
						form.testimonials = [...parsed];
					}
				} catch (e) {
					console.error("Failed to parse fresh testimonials", e);
				}
			}

			setTimeout(() => {
				activeBlobUrls.value.forEach(url => URL.revokeObjectURL(url));
				activeBlobUrls.value.clear();
			}, 300);

			closeEditModal();
			triggerToast("Pengaturan tata letak berhasil disimpan!", "success");
			setTimeout(() => (isSuccess.value = false), 3000);
		},
		onError: (errors) => {
			isProcessing.value = false;
			const firstError = Object.values(errors)[0] as string | undefined;
			triggerToast(firstError || "Terjadi kesalahan saat menyimpan pengaturan.", "error");
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
                type="button"
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
                            <button type="button" @click="toggleVisibility('show_hero')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300" title="Sembunyikan/Tampilkan">
                                <Eye v-if="form.show_hero" class="w-5 h-5"/>
                                <EyeOff v-else class="w-5 h-5 text-rose-500"/>
                            </button>
                            <div>
                                <h4 :class="['text-[14px] font-bold', form.show_hero ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 line-through']">Teks Utama & Call to Action</h4>
                                <p class="text-[12px] font-bold text-slate-500 mt-0.5">Gadget Hero Halaman Utama</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="openEditModal('hero')" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-[#2563EB] hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors border border-slate-200 dark:border-slate-600">
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
                                            <img :src="img" alt="Pratinjau galeri hero" class="w-full h-full object-cover">
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
                            <button type="button" @click="openEditModal('gallery')" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-[#2563EB] hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors border border-slate-200 dark:border-slate-600 shrink-0">
                                <Edit2 class="w-3.5 h-3.5"/>
                            </button>
                        </div>

                        <!-- Inline Gallery Previews (always visible after save) -->
                        <div v-if="form.hero_gallery.length > 0" class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Gambar Tersimpan ({{ form.hero_gallery.length }})</p>
                            <div class="flex flex-wrap gap-2">
                                <div v-for="img in form.hero_gallery" :key="img"
                                    class="relative group w-16 h-16 rounded-xl overflow-hidden border border-slate-200 shadow-sm">
                                    <img :src="img" alt="Pratinjau galeri" class="w-full h-full object-cover">
                                    <button type="button" @click="removeExistingGallery(img)"
                                        class="absolute inset-0 bg-red-500/80 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <X class="w-4 h-4"/>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Upload Button -->
                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700" :class="{'border-t-0 pt-0': form.hero_gallery.length === 0}">
                            <button type="button" @click="openEditModal('gallery')" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-[#2563EB] hover:bg-blue-600 text-white px-6 py-3 rounded-xl text-[12px] font-black transition-all shadow-lg shadow-blue-500/20 active:scale-95">
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
                                <button
                                    type="button"
                                    @click="requestToggle('show_events', 'Event & Agenda Timeline')"
                                    :class="[
                                        'relative inline-flex h-6.5 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500/20 shadow-xs',
                                        form.show_events ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-700'
                                    ]"
                                    title="Geser untuk Aktif/Nonaktifkan"
                                >
                                    <span :class="['pointer-events-none inline-block h-5.5 w-5.5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out', form.show_events ? 'translate-x-5.5' : 'translate-x-0']"></span>
                                </button>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 :class="['text-[14px] font-bold', form.show_events ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 line-through']">Event & Agenda Timeline</h4>
                                        <span :class="['px-2 py-0.5 rounded-full text-[10px] font-extrabold border', form.show_events ? 'bg-emerald-50 text-emerald-600 border-emerald-200/60 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-400']">
                                            {{ form.show_events ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>
                                    <p class="text-[12px] font-bold text-slate-500 mt-0.5">Otomatis dari Data Event FMIKOM</p>
                                </div>
                            </div>
                        </div>

                        <!-- WIDGET: SHOWCASE PORTOFOLIO -->
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl p-4 flex items-center justify-between shadow-sm transition-all hover:shadow-md hover:border-slate-300 dark:hover:border-slate-500">
                            <div class="flex items-center gap-4">
                                <button
                                    type="button"
                                    @click="requestToggle('show_showcase', 'Portofolio Showcase Mahasiswa')"
                                    :class="[
                                        'relative inline-flex h-6.5 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500/20 shadow-xs',
                                        form.show_showcase ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-700'
                                    ]"
                                    title="Geser untuk Aktif/Nonaktifkan"
                                >
                                    <span :class="['pointer-events-none inline-block h-5.5 w-5.5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out', form.show_showcase ? 'translate-x-5.5' : 'translate-x-0']"></span>
                                </button>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 :class="['text-[14px] font-bold', form.show_showcase ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 line-through']">Portofolio Showcase Mahasiswa</h4>
                                        <span :class="['px-2 py-0.5 rounded-full text-[10px] font-extrabold border', form.show_showcase ? 'bg-emerald-50 text-emerald-600 border-emerald-200/60 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-400']">
                                            {{ form.show_showcase ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>
                                    <p class="text-[12px] font-bold text-slate-500 mt-0.5">Pengaturan Karya Unggulan (Modul PAGI)</p>
                                </div>
                            </div>
                        </div>

                        <!-- WIDGET: PETA ALUMNI -->
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl p-4 flex items-center justify-between shadow-sm transition-all hover:shadow-md hover:border-slate-300 dark:hover:border-slate-500">
                            <div class="flex items-center gap-4">
                                <button
                                    type="button"
                                    @click="requestToggle('show_alumni', 'Peta Sebaran Alumni & Statistik')"
                                    :class="[
                                        'relative inline-flex h-6.5 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500/20 shadow-xs',
                                        form.show_alumni ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-700'
                                    ]"
                                    title="Geser untuk Aktif/Nonaktifkan"
                                >
                                    <span :class="['pointer-events-none inline-block h-5.5 w-5.5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out', form.show_alumni ? 'translate-x-5.5' : 'translate-x-0']"></span>
                                </button>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 :class="['text-[14px] font-bold', form.show_alumni ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 line-through']">Peta Sebaran Alumni & Statistik</h4>
                                        <span :class="['px-2 py-0.5 rounded-full text-[10px] font-extrabold border', form.show_alumni ? 'bg-emerald-50 text-emerald-600 border-emerald-200/60 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-400']">
                                            {{ form.show_alumni ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>
                                    <p class="text-[12px] font-bold text-slate-500 mt-0.5">Seksi Visual Peta & Statistik Tracer</p>
                                </div>
                            </div>
                        </div>

                        <!-- WIDGET: POSTS / BERITA -->
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl p-4 flex items-center justify-between shadow-sm transition-all hover:shadow-md hover:border-slate-300 dark:hover:border-slate-500">
                            <div class="flex items-center gap-4">
                                <button
                                    type="button"
                                    @click="requestToggle('show_features', 'Berita & Postingan')"
                                    :class="[
                                        'relative inline-flex h-6.5 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500/20 shadow-xs',
                                        form.show_features ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-700'
                                    ]"
                                    title="Geser untuk Aktif/Nonaktifkan"
                                >
                                    <span :class="['pointer-events-none inline-block h-5.5 w-5.5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out', form.show_features ? 'translate-x-5.5' : 'translate-x-0']"></span>
                                </button>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 :class="['text-[14px] font-bold', form.show_features ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 line-through']">Berita & Postingan</h4>
                                        <span :class="['px-2 py-0.5 rounded-full text-[10px] font-extrabold border', form.show_features ? 'bg-emerald-50 text-emerald-600 border-emerald-200/60 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-400']">
                                            {{ form.show_features ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>
                                    <p class="text-[12px] font-bold text-slate-500 mt-0.5">Otomatis dari Portal Admin Posts</p>
                                </div>
                            </div>
                        </div>

                        <!-- WIDGET: PARTNERS -->
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl p-4 shadow-sm transition-all hover:shadow-md hover:border-slate-300 dark:hover:border-slate-500">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <button
                                        type="button"
                                        @click="requestToggle('show_partners', 'Mitra & Partner')"
                                        :class="[
                                            'relative inline-flex h-6.5 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500/20 shadow-xs',
                                            form.show_partners ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-700'
                                        ]"
                                        title="Geser untuk Aktif/Nonaktifkan"
                                    >
                                        <span :class="['pointer-events-none inline-block h-5.5 w-5.5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out', form.show_partners ? 'translate-x-5.5' : 'translate-x-0']"></span>
                                    </button>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 :class="['text-[14px] font-bold', form.show_partners ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 line-through']">Mitra & Partner</h4>
                                            <span :class="['px-2 py-0.5 rounded-full text-[10px] font-extrabold border', form.show_partners ? 'bg-emerald-50 text-emerald-600 border-emerald-200/60 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-400']">
                                                {{ form.show_partners ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </div>
                                        <p class="text-[12px] font-bold text-slate-500 mt-0.5">
                                            {{ form.partners.length > 0 ? `${form.partners.length} logo mitra tersimpan` : 'Logo-logo mitra / kerjasama' }}
                                        </p>
                                    </div>
                                </div>
                                <button type="button" @click="openEditModal('partners')" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-[#2563EB] hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors border border-slate-200 dark:border-slate-600">
                                    <Edit2 class="w-3.5 h-3.5"/>
                                </button>
                            </div>

                            <!-- Inline Logo Previews -->
                            <div v-if="form.partners.length > 0" class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                                <div class="flex flex-wrap gap-2">
                                    <div v-for="logo in form.partners" :key="typeof logo === 'object' ? logo.logo : logo"
                                        class="relative group h-10 w-18 rounded-lg overflow-hidden border border-slate-100 bg-white flex items-center justify-center p-1 shadow-sm">
                                        <img :src="typeof logo === 'object' ? logo.logo : logo" alt="Logo partner" class="max-w-full max-h-full object-contain">
                                        <button type="button" @click="removeExistingPartner(typeof logo === 'object' ? logo.logo : logo)"
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
                                <button
                                    type="button"
                                    @click="requestToggle('show_benefits', 'Seksi Keunggulan')"
                                    :class="[
                                        'relative inline-flex h-6.5 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500/20 shadow-xs',
                                        form.show_benefits ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-700'
                                    ]"
                                    title="Geser untuk Aktif/Nonaktifkan"
                                >
                                    <span :class="['pointer-events-none inline-block h-5.5 w-5.5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out', form.show_benefits ? 'translate-x-5.5' : 'translate-x-0']"></span>
                                </button>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 :class="['text-[14px] font-bold', form.show_benefits ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 line-through']">Seksi Keunggulan</h4>
                                        <span :class="['px-2 py-0.5 rounded-full text-[10px] font-extrabold border', form.show_benefits ? 'bg-emerald-50 text-emerald-600 border-emerald-200/60 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-400']">
                                            {{ form.show_benefits ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>
                                    <p class="text-[12px] font-bold text-slate-500 mt-0.5">Gadget Teks & Ilustrasi</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="openEditModal('benefits')" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-[#2563EB] hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors border border-slate-200 dark:border-slate-600">
                                    <Edit2 class="w-3.5 h-3.5"/>
                                </button>
                            </div>
                        </div>

                        <!-- WIDGET: TESTIMONIALS (Apa Kata Mereka) -->
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl p-4 shadow-sm transition-all hover:shadow-md hover:border-slate-300 dark:hover:border-slate-500">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <button
                                        type="button"
                                        @click="requestToggle('show_testimonials', 'Apa Kata Mereka (Testimoni)')"
                                        :class="[
                                            'relative inline-flex h-6.5 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500/20 shadow-xs',
                                            form.show_testimonials ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-700'
                                        ]"
                                        title="Geser untuk Aktif/Nonaktifkan"
                                    >
                                        <span :class="['pointer-events-none inline-block h-5.5 w-5.5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out', form.show_testimonials ? 'translate-x-5.5' : 'translate-x-0']"></span>
                                    </button>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 :class="['text-[14px] font-bold', form.show_testimonials ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 line-through']">Apa Kata Mereka (Testimoni)</h4>
                                            <span :class="['px-2 py-0.5 rounded-full text-[10px] font-extrabold border', form.show_testimonials ? 'bg-emerald-50 text-emerald-600 border-emerald-200/60 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-400']">
                                                {{ form.show_testimonials ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </div>
                                        <p class="text-[12px] font-bold text-slate-500 mt-0.5">
                                            {{ form.testimonials && form.testimonials.length > 0 ? `${form.testimonials.length} testimoni civitas tersimpan` : 'Belum ada testimoni tersimpan' }}
                                        </p>
                                    </div>
                                </div>
                                <button type="button" @click="openEditModal('testimonials')" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-[#2563EB] hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors border border-slate-200 dark:border-slate-600" title="Kelola Testimoni">
                                    <Edit2 class="w-3.5 h-3.5"/>
                                </button>
                            </div>

                            <!-- Inline Testimonials Mini Cards Preview -->
                            <div v-if="form.testimonials && form.testimonials.length > 0" class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                    <div 
                                        v-for="(item, index) in form.testimonials" 
                                        :key="item.id || index"
                                        @click="openEditModal('testimonials'); startEditTestimonialItem(Number(index))"
                                        class="group relative bg-slate-50 hover:bg-blue-50/60 dark:bg-slate-900/60 dark:hover:bg-blue-950/30 border border-slate-200/80 hover:border-blue-300 dark:border-slate-700/80 dark:hover:border-blue-600 rounded-xl p-2.5 flex items-center gap-2.5 transition-all cursor-pointer shadow-2xs hover:shadow-xs"
                                        title="Klik untuk edit testimoni ini"
                                    >
                                        <!-- Avatar Circle -->
                                        <div class="w-8 h-8 rounded-full overflow-hidden shrink-0 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center shadow-2xs">
                                            <img 
                                                v-if="item.avatar || item._previewUrl" 
                                                :src="item._previewUrl || item.avatar" 
                                                :alt="item.name" 
                                                class="w-full h-full object-cover" 
                                                @error="onAvatarImageError" 
                                            />
                                            <div v-else class="w-full h-full bg-linear-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white font-black text-[11px]">
                                                {{ item.name ? item.name.charAt(0).toUpperCase() : '?' }}
                                            </div>
                                        </div>

                                        <!-- Info -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-1">
                                                <h6 class="text-[12px] font-bold text-slate-800 dark:text-slate-200 truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                                    {{ item.name || 'Tanpa Nama' }}
                                                </h6>
                                            </div>
                                            <p v-if="item.role" class="text-[10px] font-medium text-slate-400 dark:text-slate-500 truncate">
                                                {{ item.role }}
                                            </p>
                                            <p v-if="item.quote" class="text-[10px] text-slate-500 dark:text-slate-400 truncate italic">
                                                "{{ item.quote }}"
                                            </p>
                                        </div>
                                    </div>
                                </div>
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
                                <button type="button" @click="openEditModal('theme')" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-[#2563EB] hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors border border-slate-200 dark:border-slate-600">
                                    <Edit2 class="w-3.5 h-3.5"/>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- EDIT MODALS -->
        <div v-if="editingWidget" class="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-[100] flex items-center justify-center p-4">
            <div 
                class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full overflow-hidden border border-slate-200/80 dark:border-slate-700/80 flex flex-col max-h-[92vh] transition-all duration-300"
                :class="editingWidget === 'testimonials' ? 'max-w-2xl' : 'max-w-lg'"
            >
                
                <!-- Modal Header -->
                <div class="px-6 py-4.5 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/80 dark:bg-slate-800/80 backdrop-blur-xs">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center font-black">
                            <MessageSquareQuote v-if="editingWidget === 'testimonials'" class="w-5 h-5"/>
                            <Sparkles v-else-if="editingWidget === 'hero'" class="w-5 h-5"/>
                            <ImageIcon v-else-if="editingWidget === 'gallery'" class="w-5 h-5"/>
                            <Layers v-else-if="editingWidget === 'partners'" class="w-5 h-5"/>
                            <Palette v-else class="w-5 h-5"/>
                        </div>
                        <div>
                            <h3 class="text-[15px] font-black text-slate-800 dark:text-white leading-tight">
                                <span v-if="editingWidget === 'hero'">Teks Utama & Hero</span>
                                <span v-if="editingWidget === 'navbar'">Navigasi Header</span>
                                <span v-if="editingWidget === 'partners'">Mitra & Kerjasama</span>
                                <span v-if="editingWidget === 'gallery'">Galeri dan Event</span>
                                <span v-if="editingWidget === 'theme'">Warna Tema Utama</span>
                                <span v-if="editingWidget === 'benefits'">Seksi Keunggulan</span>
                                <span v-if="editingWidget === 'testimonials'">Pengaturan Apa Kata Mereka (Testimoni)</span>
                            </h3>
                            <p class="text-[11px] font-semibold text-slate-400">Sesuaikan konten dan tampilan gadget landing page.</p>
                        </div>
                    </div>
                    <button type="button" @click="closeEditModal" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 flex items-center justify-center transition-colors">
                        <X class="w-4 h-4"/>
                    </button>
                </div>

                <!-- TESTIMONIALS TAB SWITCHER (If editing testimonials) -->
                <div v-if="editingWidget === 'testimonials' && !isEditingTestimonialItem" class="px-6 pt-4 pb-0 bg-slate-50/40 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-700/60 flex gap-2">
                    <button 
                        type="button"
                        @click="activeTestimonialTab = 'list'"
                        :class="[
                            'px-4 py-2.5 rounded-t-xl text-[12px] font-bold transition-all border-b-2 flex items-center gap-2',
                            activeTestimonialTab === 'list'
                                ? 'border-[#2563EB] text-[#2563EB] bg-white dark:bg-slate-800 shadow-xs'
                                : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'
                        ]"
                    >
                        <MessageSquareQuote class="w-4 h-4" />
                        <span>Daftar Kartu Testimoni</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400">
                            {{ form.testimonials.length }}
                        </span>
                    </button>
                    <button 
                        type="button"
                        @click="activeTestimonialTab = 'header'"
                        :class="[
                            'px-4 py-2.5 rounded-t-xl text-[12px] font-bold transition-all border-b-2 flex items-center gap-2',
                            activeTestimonialTab === 'header'
                                ? 'border-[#2563EB] text-[#2563EB] bg-white dark:bg-slate-800 shadow-xs'
                                : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'
                        ]"
                    >
                        <Sparkles class="w-4 h-4" />
                        <span>Pengaturan Header Seksi</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto space-y-5 flex-1">
                    
                    <div v-if="editingWidget === 'hero'" class="space-y-5">
                        <div>
                            <label for="hero_subtitle_input" class="block text-[12px] font-bold text-slate-600 dark:text-slate-400 mb-2">Sub-Judul (Tagline)</label>
                            <input id="hero_subtitle_input" v-model="form.hero_subtitle" type="text" class="w-full bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-[13px] font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-[#2563EB] outline-none" />
                        </div>
                        <div>
                            <label for="hero_title_input" class="block text-[12px] font-bold text-slate-600 dark:text-slate-400 mb-2">Judul Utama</label>
                            <textarea id="hero_title_input" v-model="form.hero_title" rows="3" class="w-full bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-[13px] font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-[#2563EB] outline-none resize-none"></textarea>
                            <p class="text-[11px] text-slate-400 mt-1">Gunakan enter/baris baru untuk memisahkan teks.</p>
                        </div>
                        <div>
                            <label for="hero_desc_input" class="block text-[12px] font-bold text-slate-600 dark:text-slate-400 mb-2">Deskripsi Singkat</label>
                            <textarea id="hero_desc_input" v-model="form.hero_description" rows="3" class="w-full bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-[13px] font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-[#2563EB] outline-none resize-none"></textarea>
                        </div>
                    </div>

                    <div v-if="editingWidget === 'partners'" class="space-y-5">
                        <!-- Validation error alert if any -->
                        <div v-if="hasPartnerError" class="bg-rose-50/80 dark:bg-rose-950/20 border border-rose-200/80 dark:border-rose-800 rounded-2xl p-4 flex gap-3 shadow-sm">
                            <div class="text-rose-500 shrink-0 mt-0.5">
                                <AlertTriangle class="w-5 h-5" />
                            </div>
                            <div class="flex-1">
                                <h4 class="text-[13px] font-bold text-rose-800 dark:text-rose-300">Gagal Mengunggah Logo</h4>
                                <p class="text-[11px] font-bold text-rose-700 dark:text-rose-400 mt-1">
                                    {{ partnerError }}
                                </p>
                            </div>
                        </div>
                        <div>
                            <span class="block text-[12px] font-bold text-slate-600 dark:text-slate-400 mb-3">Logo Mitra & Kerjasama</span>
                            
                            <!-- Existing Logos -->
                            <div v-if="form.partners.length > 0" class="mb-4">
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Sudah Diunggah</p>
                                <div class="grid grid-cols-4 gap-2">
                                    <div v-for="img in form.partners" :key="typeof img === 'object' ? img.logo : img" class="relative group aspect-video rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 flex items-center justify-center p-2 shadow-xs">
                                        <img :src="typeof img === 'object' ? img.logo : img" alt="Logo mitra" class="max-w-full max-h-full object-contain" @error="onAvatarImageError" />
                                        <button type="button" @click="removeExistingPartner(typeof img === 'object' ? img.logo : img)" class="absolute top-1 right-1 bg-rose-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity shadow-sm">
                                            <X class="w-3 h-3"/>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- New Files Preview -->
                            <div v-if="newPartnerFilePreviews.length > 0" class="mb-4">
                                <p class="text-[11px] font-bold text-blue-500 uppercase tracking-wider mb-2">Baru Dipilih ({{ newPartnerFilePreviews.length }} file)</p>
                                <div class="grid grid-cols-4 gap-2">
                                    <div v-for="(preview, i) in newPartnerFilePreviews" :key="i" class="relative group aspect-video rounded-xl overflow-hidden border-2 border-blue-400 bg-white dark:bg-slate-900 flex items-center justify-center p-2 shadow-xs">
                                        <img :src="preview" alt="Partner logo preview" class="max-w-full max-h-full object-contain">
                                        <button type="button" @click="removeNewPartner(i)" class="absolute top-1 right-1 bg-rose-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity shadow-sm">
                                            <X class="w-3 h-3"/>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <input id="partner_input_file" aria-label="Unggah logo mitra baru" type="file" multiple accept="image/*" class="hidden" ref="partnerInput" @change="handlePartnerUpload">
                            <div 
                                @click="partnerInput?.click()" 
                                class="flex flex-col items-center justify-center border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-500 bg-slate-50/50 dark:bg-slate-900/30 hover:bg-blue-50/20 rounded-2xl p-5 cursor-pointer transition-all text-center group"
                            >
                                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 group-hover:bg-blue-50 dark:group-hover:bg-blue-950/30 group-hover:text-[#2563EB] transition-colors mb-2">
                                    <Upload class="w-5 h-5"/>
                                </div>
                                <span class="text-[12px] font-bold text-slate-700 dark:text-zinc-300">Klik untuk unggah logo mitra</span>
                                <span class="text-[10px] text-slate-400 dark:text-zinc-500 mt-1">Format: <strong>PNG, JPG, WEBP, SVG</strong> (Maks. 15MB)</span>
                            </div>
                        </div>
                    </div>

                    <!-- TESTIMONIALS TAB CONTENT -->
                    <div v-if="editingWidget === 'testimonials'" class="space-y-4">
                        
                        <!-- TAB 1: SECTION HEADER SETTINGS -->
                        <div v-if="activeTestimonialTab === 'header' && !isEditingTestimonialItem" class="space-y-4 bg-slate-50 dark:bg-slate-900/40 p-5 rounded-2xl border border-slate-200 dark:border-slate-700">
                            <div class="flex items-center gap-2 text-[#2563EB] font-bold text-[13px]">
                                <Sparkles class="w-4 h-4" />
                                <span>Teks Judul & Deskripsi Seksi</span>
                            </div>
                            <div>
                                <label for="testimonials_title_input" class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">Judul Seksi Utama</label>
                                <input id="testimonials_title_input" v-model="form.testimonials_title" type="text" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-[13px] font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-[#2563EB] outline-none transition-all" placeholder="Contoh: Apa Kata Mereka" />
                            </div>
                            <div>
                                <label for="testimonials_subtitle_input" class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">Sub-Judul / Deskripsi Seksi</label>
                                <textarea id="testimonials_subtitle_input" v-model="form.testimonials_subtitle" rows="3" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-[13px] font-medium text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-[#2563EB] outline-none resize-none transition-all" placeholder="Contoh: Pengalaman dan testimoni nyata dari mahasiswa, dosen, dan alumni."></textarea>
                            </div>
                        </div>

                        <!-- TAB 2: TESTIMONIAL ITEM INLINE FORM (ADD / EDIT) -->
                        <div v-if="isEditingTestimonialItem" class="bg-blue-50/40 dark:bg-blue-950/20 border-2 border-blue-200 dark:border-blue-800/80 rounded-2xl p-5 space-y-4 shadow-sm">
                            <div class="flex items-center justify-between border-b border-blue-100 dark:border-blue-900/40 pb-3">
                                <div class="flex items-center gap-2 text-blue-600 dark:text-blue-400 font-black text-[13px]">
                                    <UserCheck class="w-4.5 h-4.5"/>
                                    <span>{{ editingTestimonialIndex !== null ? 'Edit Testimoni' : 'Tambah Testimoni Baru' }}</span>
                                </div>
                                <button type="button" @click="resetTestimonialItemForm" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1">
                                    <X class="w-4 h-4"/>
                                </button>
                            </div>

                            <!-- Name & Role -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <label for="testimonial_name_input" class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Nama Pemberi Testimoni <span class="text-rose-500">*</span>
                                    </label>
                                    <input 
                                        id="testimonial_name_input"
                                        v-model="testimonialItemForm.name" 
                                        type="text" 
                                        :class="['w-full bg-white dark:bg-slate-800 border rounded-xl px-3.5 py-2.5 text-[12px] font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-[#2563EB] outline-none transition-all', testimonialFormErrors.name ? 'border-rose-400' : 'border-slate-200 dark:border-slate-700']" 
                                        placeholder="Contoh: Andi Saputra" 
                                    />
                                    <span v-if="testimonialFormErrors.name" class="text-[10px] font-bold text-rose-500 mt-0.5 block">{{ testimonialFormErrors.name }}</span>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label for="testimonial_preset_role_select" class="text-[11px] font-bold text-slate-700 dark:text-slate-300">
                                            Jabatan / Peran
                                        </label>
                                        <button 
                                            v-if="isCustomRole" 
                                            type="button" 
                                            @click="isCustomRole = false; selectedPresetRole = ''; testimonialItemForm.role = ''"
                                            class="text-[10px] font-bold text-[#2563EB] hover:underline"
                                        >
                                            ← Pilih dari Daftar
                                        </button>
                                    </div>

                                    <!-- Dropdown Selector if not custom -->
                                    <div v-if="!isCustomRole" class="relative">
                                        <select 
                                            id="testimonial_preset_role_select"
                                            v-model="selectedPresetRole" 
                                            @change="onRolePresetChange"
                                            class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-[12px] font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-[#2563EB] outline-none transition-all cursor-pointer appearance-none pr-9"
                                        >
                                            <option value="" disabled>-- Pilih Jabatan / Peran --</option>
                                            <optgroup v-for="grp in presetRoles" :key="grp.category" :label="grp.category">
                                                <option v-for="r in grp.items" :key="r" :value="r">{{ r }}</option>
                                            </optgroup>
                                            <option value="custom">✏️ + Tulis Jabatan Lainnya (Kustom)...</option>
                                        </select>
                                        <ChevronDown class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                                    </div>

                                    <!-- Custom Input if custom mode selected -->
                                    <div v-else>
                                        <input 
                                            id="testimonial_custom_role_input"
                                            aria-label="Ketik jabatan kustom"
                                            v-model="testimonialItemForm.role" 
                                            type="text" 
                                            class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-[12px] font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-[#2563EB] outline-none transition-all" 
                                            placeholder="Ketik jabatan kustom (contoh: CEO Startup)" 
                                            autofocus
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Quote -->
                            <div>
                                <label for="testimonial_quote_input" class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Teks Kutipan Testimoni <span class="text-rose-500">*</span>
                                </label>
                                <textarea 
                                    id="testimonial_quote_input"
                                    v-model="testimonialItemForm.quote" 
                                    rows="3" 
                                    :class="['w-full bg-white dark:bg-slate-800 border rounded-xl px-3.5 py-2.5 text-[12px] font-medium text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-[#2563EB] outline-none resize-none transition-all', testimonialFormErrors.quote ? 'border-rose-400' : 'border-slate-200 dark:border-slate-700']" 
                                    placeholder="Tuliskan pengalaman atau ulasan positif..."
                                ></textarea>
                                <span v-if="testimonialFormErrors.quote" class="text-[10px] font-bold text-rose-500 mt-0.5 block">{{ testimonialFormErrors.quote }}</span>
                            </div>

                            <!-- Theme Selector (Visual Cards) -->
                            <div>
                                <span class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">Tampilan Kartu Testimoni</span>
                                <div class="grid grid-cols-2 gap-3">
                                    <div 
                                        @click="testimonialItemForm.theme = 'light'" 
                                        :class="[
                                            'p-3 rounded-xl border-2 cursor-pointer transition-all flex items-center gap-3',
                                            testimonialItemForm.theme === 'light'
                                                ? 'border-[#2563EB] bg-white dark:bg-slate-800 shadow-sm'
                                                : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 opacity-60 hover:opacity-100'
                                        ]"
                                    >
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0">
                                            <div class="w-4 h-4 rounded-full bg-white border border-slate-300"></div>
                                        </div>
                                        <div>
                                            <h5 class="text-[11px] font-bold text-slate-800 dark:text-slate-200">Kartu Terang</h5>
                                            <p class="text-[9px] text-slate-400">Standar putih elegan</p>
                                        </div>
                                    </div>

                                    <div 
                                        @click="testimonialItemForm.theme = 'dark'" 
                                        :class="[
                                            'p-3 rounded-xl border-2 cursor-pointer transition-all flex items-center gap-3',
                                            testimonialItemForm.theme === 'dark'
                                                ? 'border-[#2563EB] bg-slate-900 text-white shadow-sm'
                                                : 'border-slate-200 dark:border-slate-700 bg-slate-900 text-white opacity-60 hover:opacity-100'
                                        ]"
                                    >
                                        <div class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center shrink-0">
                                            <div class="w-4 h-4 rounded-full bg-blue-500"></div>
                                        </div>
                                        <div>
                                            <h5 class="text-[11px] font-bold text-white">Kartu Gelap</h5>
                                            <p class="text-[9px] text-slate-400">Sorotan / Highlight</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Avatar Section -->
                            <div class="bg-white dark:bg-slate-800/80 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-700 space-y-3">
                                <div class="flex items-center justify-between">
                                    <label for="testimoni_avatar_url" class="text-[11px] font-bold text-slate-700 dark:text-slate-300">Foto Avatar Testimoni</label>
                                    <div class="flex items-center gap-2 text-[10px] font-bold">
                                        <button 
                                            type="button" 
                                            @click="testimonialAvatarMode = 'upload'"
                                            :class="testimonialAvatarMode === 'upload' ? 'text-[#2563EB] underline' : 'text-slate-400 hover:text-slate-600'"
                                        >
                                            Unggah File
                                        </button>
                                        <span class="text-slate-300">•</span>
                                        <button 
                                            type="button" 
                                            @click="testimonialAvatarMode = 'url'"
                                            :class="testimonialAvatarMode === 'url' ? 'text-[#2563EB] underline' : 'text-slate-400 hover:text-slate-600'"
                                        >
                                            Gunakan URL Web
                                        </button>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4">
                                    <!-- Avatar Preview Circle -->
                                    <div class="w-14 h-14 rounded-full overflow-hidden shrink-0 border-2 border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-900 flex items-center justify-center shadow-xs">
                                        <img 
                                            v-if="testimonialAvatarPreview || testimonialItemForm.avatar" 
                                            :src="testimonialAvatarPreview || testimonialItemForm.avatar" 
                                            alt="Avatar testimoni" 
                                            class="w-full h-full object-cover" 
                                            @error="onAvatarImageError" 
                                        />
                                        <div v-else class="w-full h-full bg-linear-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white font-black text-[14px]">
                                            {{ testimonialItemForm.name ? testimonialItemForm.name.charAt(0).toUpperCase() : '?' }}
                                        </div>
                                    </div>

                                    <!-- Upload Mode Dropzone -->
                                    <div v-if="testimonialAvatarMode === 'upload'" class="flex-1 min-w-0">
                                        <input id="avatar_file_input" aria-label="Unggah foto avatar" type="file" ref="avatarInput" accept="image/*" class="hidden" @change="handleAvatarUpload" />
                                        
                                        <div v-if="testimonialAvatarFileName" class="flex items-center justify-between p-2.5 bg-blue-50/60 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-xl">
                                            <div class="min-w-0 flex-1">
                                                <p class="text-[11px] font-bold text-blue-900 dark:text-blue-200 truncate">{{ testimonialAvatarFileName }}</p>
                                                <p class="text-[9px] text-blue-500 font-semibold">{{ testimonialAvatarFileSize }}</p>
                                            </div>
                                            <button @click="removeSelectedAvatarFile" type="button" class="text-rose-500 hover:text-rose-700 p-1 ml-2 shrink-0" title="Hapus berkas">
                                                <X class="w-4 h-4" />
                                            </button>
                                        </div>

                                        <div v-else class="flex items-center gap-2">
                                            <button 
                                                @click="avatarInput?.click()" 
                                                type="button" 
                                                class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl text-[11px] font-bold transition-all flex items-center gap-2 shadow-xs"
                                            >
                                                <Upload class="w-4 h-4 text-blue-500" />
                                                Pilih & Sesuaikan Foto
                                            </button>
                                            <span class="text-[10px] text-slate-400">PNG, JPG, WEBP (Auto-Crop 1:1)</span>
                                        </div>
                                    </div>

                                    <!-- URL Mode Input -->
                                    <div v-else class="flex-1 min-w-0">
                                        <input 
                                            id="testimoni_avatar_url"
                                            v-model="testimonialItemForm.avatar" 
                                            type="text" 
                                            class="w-full bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-[11px] font-medium text-slate-800 dark:text-slate-200 outline-none focus:ring-2 focus:ring-[#2563EB]" 
                                            placeholder="https://example.com/avatar.jpg" 
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-end gap-2.5 pt-2">
                                <button @click="resetTestimonialItemForm" type="button" class="px-4 py-2 text-[12px] font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
                                    Batal
                                </button>
                                <button @click="saveTestimonialItem" type="button" class="px-5 py-2 bg-[#2563EB] hover:bg-blue-600 text-white text-[12px] font-black rounded-xl shadow-md shadow-blue-500/20 transition-all active:scale-95">
                                    {{ editingTestimonialIndex !== null ? 'Perbarui Testimoni' : 'Tambahkan ke Daftar' }}
                                </button>
                            </div>
                        </div>

                        <!-- TAB 1: TESTIMONIALS CARDS LIST (If not editing item) -->
                        <div v-if="activeTestimonialTab === 'list' && !isEditingTestimonialItem" class="space-y-3">
                            <div class="flex items-center justify-between">
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                    Urutan & Kartu Aktif
                                </p>
                                <button 
                                    @click="startAddTestimonialItem" 
                                    type="button" 
                                    class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-[11px] font-black transition-all flex items-center gap-1.5 shadow-sm shadow-blue-500/20 active:scale-95"
                                >
                                    <Plus class="w-3.5 h-3.5" />
                                    Tambah Testimoni Baru
                                </button>
                            </div>

                            <div v-if="form.testimonials.length === 0" class="p-8 text-center border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-2xl text-slate-400 text-[12px]">
                                <MessageSquareQuote class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-600 mb-2" />
                                Belum ada testimoni. Klik <strong>"Tambah Testimoni Baru"</strong> untuk memulai.
                            </div>

                            <div class="space-y-2.5 max-h-95 overflow-y-auto pr-1">
                                <div
                                    v-for="(item, index) in form.testimonials"
                                    :key="item.id || index"
                                    class="bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700 rounded-2xl p-3.5 flex items-center justify-between gap-3.5 shadow-xs hover:border-blue-300 dark:hover:border-blue-600 transition-all group"
                                >
                                    <div class="flex items-center gap-3.5 flex-1 min-w-0">
                                        <!-- Avatar Circle with Initial Fallback -->
                                        <div class="w-11 h-11 rounded-full overflow-hidden shrink-0 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center shadow-xs">
                                            <img 
                                                v-if="item.avatar || item._previewUrl" 
                                                :src="item._previewUrl || item.avatar" 
                                                :alt="item.name" 
                                                class="w-full h-full object-cover" 
                                                @error="onAvatarImageError" 
                                            />
                                            <div v-else class="w-full h-full bg-linear-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white font-black text-[13px]">
                                                {{ item.name ? item.name.charAt(0).toUpperCase() : '?' }}
                                            </div>
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <h5 class="text-[13px] font-bold text-slate-800 dark:text-slate-200 truncate">{{ item.name }}</h5>
                                                <span v-if="item.theme === 'dark'" class="bg-slate-900 text-white text-[9px] font-black px-2 py-0.5 rounded-md shrink-0 shadow-2xs">Kartu Gelap</span>
                                                <span v-else class="bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 text-[9px] font-bold px-2 py-0.5 rounded-md shrink-0 border border-slate-200 dark:border-slate-700">Kartu Terang</span>
                                            </div>
                                            <p class="text-[11px] font-medium text-slate-400 truncate mt-0.5">{{ item.role }} — "{{ item.quote }}"</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1 shrink-0">
                                        <button type="button" @click="moveTestimonialItem(Number(index), 'up')" :disabled="index === 0" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-20 transition-colors" title="Geser ke Atas">
                                            <ArrowUp class="w-3.5 h-3.5" />
                                        </button>
                                        <button type="button" @click="moveTestimonialItem(Number(index), 'down')" :disabled="index === form.testimonials.length - 1" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-20 transition-colors" title="Geser ke Bawah">
                                            <ArrowDown class="w-3.5 h-3.5" />
                                        </button>
                                        <button type="button" @click="startEditTestimonialItem(Number(index))" class="p-1.5 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors" title="Edit Testimoni">
                                            <Edit2 class="w-3.5 h-3.5" />
                                        </button>
                                        <button type="button" @click="deleteTestimonialItem(Number(index))" class="p-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg transition-colors" title="Hapus Testimoni">
                                            <Trash2 class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div v-if="editingWidget === 'theme'" class="space-y-5">
                        <div>
                            <label for="theme_color_picker" class="block text-[12px] font-bold text-slate-600 dark:text-slate-400 mb-2">Pilih Warna Utama (Hex Code)</label>
                            <div class="flex items-center gap-3">
                                <input id="theme_color_picker" type="color" v-model="form.primary_color" class="h-10 w-14 rounded-xl cursor-pointer border-0 p-0 overflow-hidden" />
                                <input id="theme_color_text" aria-label="Kode hex warna tema" v-model="form.primary_color" type="text" class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-[13px] font-bold text-slate-800 dark:text-slate-200 w-32 focus:ring-2 focus:ring-[#2563EB] outline-none" />
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
                            <span class="block text-[12px] font-bold text-slate-600 dark:text-slate-400 mb-1">Gambar Galeri dan Event</span>
                            <p class="text-[11px] text-slate-400 mb-4">Upload gambar galeri dan event. Akan muncul sebagai card stack interaktif di bagian atas Landing Page. Bisa lebih dari satu gambar.</p>
                        </div>

                        <!-- Existing Images Grid -->
                        <div v-if="form.hero_gallery.length > 0">
                            <p class="text-[11px] font-black text-slate-400 uppercase tracking-wider mb-3">Gambar Tersimpan ({{ form.hero_gallery.length }})</p>
                            <div class="grid grid-cols-3 gap-3">
                                <div v-for="img in form.hero_gallery" :key="img" class="relative group aspect-square rounded-xl overflow-hidden border border-slate-200 shadow-sm">
                                    <img :src="img" alt="Pratinjau galeri tersimpan" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all flex items-center justify-center">
                                        <button type="button" @click="removeExistingGallery(img)" class="opacity-0 group-hover:opacity-100 bg-red-500 text-white rounded-full p-1.5 transition-opacity shadow-md">
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
                                    <img :src="preview" alt="Pratinjau gambar baru" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all flex items-center justify-center">
                                        <button type="button" @click="removeNewHero(i)" class="opacity-0 group-hover:opacity-100 bg-red-500 text-white rounded-full p-1.5 transition-opacity shadow-md">
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
                                <span class="text-[9px] text-slate-400 dark:text-zinc-600 mt-0.5">Mendukung format PNG, JPG, JPEG, WEBP (maks. 25MB)</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 flex flex-col sm:flex-row justify-end gap-2 sm:gap-3">
                    <button type="button" @click="closeEditModal" class="px-5 py-2.5 rounded-xl text-[13px] font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all order-2 sm:order-1 flex-1 sm:flex-none">
                        Batal
                    </button>
                    <button type="button" @click="submit" class="bg-[#2563EB] hover:bg-blue-600 text-white px-5 py-3 rounded-xl text-[13px] font-black shadow-lg shadow-blue-500/20 transition-all order-1 sm:order-2 flex-1 sm:flex-none active:scale-95">
                        Simpan Perubahan
                    </button>
                </div>

            </div>
        </div>

        <!-- CONFIRMATION MODAL DISABLING SECTION -->
        <div v-if="confirmDisableModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-2xl border border-slate-100 dark:border-slate-700 animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                        <AlertTriangle class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Nonaktifkan Seksi?</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Konfirmasi Perubahan Tampilan</p>
                    </div>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed mb-6">
                    Apakah Anda yakin ingin menyembunyikan seksi <strong class="text-slate-900 dark:text-white">"{{ targetDisableLabel }}"</strong> dari halaman utama publik? Pengunjung tidak akan dapat melihat seksi ini.
                </p>
                <div class="flex items-center justify-end gap-3">
                    <button @click="confirmDisableModalOpen = false" type="button" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        Batal
                    </button>
                    <button @click="confirmDisableSection" type="button" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-md shadow-rose-600/20 transition-all">
                        Ya, Sembunyikan Seksi
                    </button>
                </div>
            </div>
        </div>

        <!-- AVATAR CROPPER MODAL -->
        <div v-if="isCropperOpen" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md z-120 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-200 dark:border-slate-800 flex flex-col max-h-[92vh] animate-in fade-in zoom-in-95 duration-200">
                
                <!-- Header -->
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/80 dark:bg-slate-900/80">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
                            <Crop class="w-4 h-4" />
                        </div>
                        <div>
                            <h3 class="text-[14px] font-black text-slate-800 dark:text-white">Sesuaikan Foto Avatar</h3>
                            <p class="text-[10.5px] text-slate-400 font-medium">Potong foto dengan proporsi lingkaran 1:1 yang sempurna</p>
                        </div>
                    </div>
                    <button type="button" @click="closeCropper" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 flex items-center justify-center transition-colors">
                        <X class="w-4 h-4"/>
                    </button>
                </div>

                <!-- Cropper Arena -->
                <div class="p-5 flex-1 flex flex-col items-center justify-center bg-slate-950/90 overflow-hidden">
                    <div class="w-full h-80 max-h-[45vh] flex items-center justify-center overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/50 relative">
                        <img ref="cropperImageRef" :src="cropperImageSrc" class="max-w-full block" alt="Foto untuk dipotong" />
                    </div>

                    <!-- Toolbar Controls -->
                    <div class="flex items-center gap-2 mt-4 bg-slate-900/90 border border-slate-800 px-4 py-2 rounded-2xl shadow-lg">
                        <button type="button" @click="zoomInCropper" class="p-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800 transition-colors" title="Perbesar (Zoom In)">
                            <ZoomIn class="w-4 h-4" />
                        </button>
                        <button type="button" @click="zoomOutCropper" class="p-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800 transition-colors" title="Perkecil (Zoom Out)">
                            <ZoomOut class="w-4 h-4" />
                        </button>
                        <div class="w-px h-4 bg-slate-700 mx-1"></div>
                        <button type="button" @click="rotateLeftCropper" class="p-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800 transition-colors" title="Putar Kiri (-90°)">
                            <RotateCcw class="w-4 h-4" />
                        </button>
                        <button type="button" @click="rotateRightCropper" class="p-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800 transition-colors" title="Putar Kanan (+90°)">
                            <RotateCw class="w-4 h-4" />
                        </button>
                        <div class="w-px h-4 bg-slate-700 mx-1"></div>
                        <button type="button" @click="resetCropper" class="p-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800 transition-colors text-[11px] font-bold" title="Reset">
                            <RefreshCw class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/80 dark:bg-slate-900/80">
                    <span class="text-[11px] text-slate-400 font-medium">Resolusi output: 500 × 500 px (HD WebP)</span>
                    <div class="flex items-center gap-2.5">
                        <button type="button" @click="closeCropper" class="px-4 py-2 text-[12px] font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
                            Batal
                        </button>
                        <button type="button" @click="applyCroppedAvatar" class="px-5 py-2 bg-[#2563EB] hover:bg-blue-600 text-white text-[12px] font-black rounded-xl shadow-md shadow-blue-500/20 transition-all flex items-center gap-2 active:scale-95">
                            <Check class="w-4 h-4" />
                            Gunakan Foto Ini
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- HIDDEN FILE INPUTS (Global) -->
        <input id="hero_gallery_hidden_input" aria-label="Unggah berkas galeri hero" type="file" multiple accept="image/*" class="hidden" ref="heroGalleryInput" @change="handleHeroUpload">
        <input id="partner_hidden_input" aria-label="Unggah berkas logo mitra" type="file" multiple accept="image/*" class="hidden" ref="partnerInput" @change="handlePartnerUpload">

    </PortalAdminLayout>
</template>
