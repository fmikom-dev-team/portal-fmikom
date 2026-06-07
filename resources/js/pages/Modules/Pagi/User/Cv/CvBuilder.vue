<script setup lang="ts">
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import axios from "axios";
import {
	AlertCircle,
	ArrowLeft,
	Award,
	BookOpen,
	Briefcase,
	Check,
	ChevronDown,
	ChevronUp,
	Download,
	Eye,
	FileSignature,
	GraduationCap,
	Languages,
	LayoutGrid,
	Loader2,
	Plus,
	Redo2,
	RefreshCw,
	Save,
	Settings,
	SlidersHorizontal,
	Sparkles,
	ToggleLeft,
	ToggleRight,
	Trash2,
	Undo2,
	User,
	Users,
	ZoomIn,
	ZoomOut,
} from "lucide-vue-next";
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import Navbar from "../ui/Navbar.vue";
import AutocompleteInput from "./AutocompleteInput.vue";
import CvPreview from "./CvPreview.vue";
import MonthYearPicker from "./MonthYearPicker.vue";
import ThemeCustomizer from "./ThemeCustomizer.vue";

const SUGGESTIONS = {
	jobTitle: [
		"Desain Grafis",
		"Teknik Informatika",
		"Manajemen",
		"Akuntansi",
		"Hukum",
		"Kedokteran",
		"Frontend Developer",
		"Backend Developer",
		"Fullstack Developer",
		"UI/UX Designer",
		"Product Designer",
		"Data Analyst",
		"Data Scientist",
		"Mobile Developer",
		"Cyber Security Specialist",
		"Cloud Engineer",
		"System Administrator",
		"DevOps Engineer",
		"Project Manager",
		"Digital Marketer",
		"Content Writer",
		"Social Media Specialist",
		"Business Analyst",
		"Quality Assurance",
		"Network Engineer",
		"Game Developer",
		"Copywriter",
		"SEO Specialist",
		"Video Editor",
		"Animator",
	],
	degree: [
		"Sarjana (S.Kom)",
		"Sarjana (S.T)",
		"Sarjana (S.Ds)",
		"Sarjana (S.M)",
		"Sarjana (S.Ak)",
		"Diploma III (A.Md.Kom)",
		"Diploma III (A.Md)",
		"Magister (M.Kom)",
		"Magister (M.T)",
		"Magister (M.B.A)",
		"Doktor (Dr.)",
		"SMA",
		"SMK",
	],
	field: [
		"Teknik Informatika",
		"Sistem Informasi",
		"Desain Komunikasi Visual",
		"Manajemen",
		"Akuntansi",
		"Teknik Elektro",
		"Teknik Industri",
		"Ilmu Komunikasi",
		"Sastra Inggris",
		"Matematika",
		"Statistika",
		"Hukum",
		"Psikologi",
		"Hubungan Internasional",
	],
	position: [
		"Frontend Developer",
		"Backend Developer",
		"Fullstack Developer",
		"UI/UX Designer",
		"UI/UX Writer",
		"Product Manager",
		"Data Analyst",
		"Quality Assurance Engineer",
		"Cyber Security Consultant",
		"Social Media Coordinator",
		"Graphic Designer",
		"Video Editor",
		"Content Creator",
		"Project Leader",
		"Creative Director",
		"Software Engineer Intern",
		"Research Assistant",
		"Teaching Assistant",
		"Freelance Developer",
	],
	role: [
		"Ketua",
		"Wakil Ketua",
		"Sekretaris",
		"Bendahara",
		"Koordinator",
		"Anggota",
		"Penanggung Jawab",
		"Kepala Divisi",
		"Staff Ahli",
		"Relawan",
		"Moderator",
		"Project Manager",
		"Event Organizer",
	],
	issuer: [
		"Google",
		"Coursera",
		"Udemy",
		"Microsoft",
		"Amazon Web Services (AWS)",
		"IBM",
		"Cisco",
		"Oracle",
		"Red Hat",
		"Dicoding Indonesia",
		"Binar Academy",
		"Sanbercode",
		"HackerRank",
		"freeCodeCamp",
		"Project Management Institute (PMI)",
		"Scrum.org",
	],
	language: [
		"Indonesia",
		"Inggris",
		"Mandarin",
		"Jepang",
		"Korea",
		"Arab",
		"Jerman",
		"Prancis",
		"Spanyol",
		"Rusia",
		"Belanda",
		"Italia",
	],
	location: [
		"Banyumas, Indonesia",
		"Purwokerto, Indonesia",
		"Cilacap, Indonesia",
		"Purbalingga, Indonesia",
		"Kebumen, Indonesia",
		"Banjarnegara, Indonesia",
		"Brebes, Indonesia",
		"Tegal, Indonesia",
		"Pekalongan, Indonesia",
		"Semarang, Indonesia",
		"Surakarta, Indonesia",
		"Yogyakarta, Indonesia",
		"Jakarta, Indonesia",
		"Bandung, Indonesia",
		"Surabaya, Indonesia",
	],
};

const page = usePage();

const props = defineProps<{
	cv: any;
}>();

// Clone CV data for local manipulation
const cvData = ref({
	id: props.cv.id,
	title: props.cv.title || "Untitled CV",
	status: props.cv.status || "draft",
	template_id: props.cv.template_id || "ats-professional",
	personal_info: props.cv.personal_info ? { ...props.cv.personal_info } : {},
	education: props.cv.education ? [...props.cv.education] : [],
	experience: props.cv.experience ? [...props.cv.experience] : [],
	organizations: props.cv.organizations ? [...props.cv.organizations] : [],
	skills: props.cv.skills ? [...props.cv.skills] : [],
	certifications: props.cv.certifications ? [...props.cv.certifications] : [],
	trainings: props.cv.trainings ? [...props.cv.trainings] : [],
	achievements: props.cv.achievements ? [...props.cv.achievements] : [],
	languages: props.cv.languages ? [...props.cv.languages] : [],
	references: props.cv.references ? [...props.cv.references] : [],
	customization: props.cv.customization
		? { ...props.cv.customization }
		: {
				primary_color: "#1e3a8a",
				font_family: "GT Standard M",
				font_size: "11pt",
				line_height: "1.4",
				spacing: "normal",
				section_order: [
					"summary",
					"experience",
					"education",
					"organizations",
					"skills",
					"certifications",
					"trainings",
					"achievements",
					"languages",
					"references",
				],
				sections_visibility: {
					summary: true,
					experience: true,
					education: true,
					organizations: true,
					skills: true,
					certifications: true,
					trainings: true,
					achievements: true,
					languages: true,
					references: true,
				},
			},
});

// UI states
const activeTab = ref<"form" | "customizer">("form");
const activeAccordion = ref<string>("personal");
const zoom = ref(0.7);
const previewModeMobile = ref(false); // Mobile toggle between editor & preview
const previewContainer = ref<HTMLElement | null>(null);

const updateMobileZoom = () => {
	if (typeof window !== "undefined" && window.innerWidth < 768) {
		const containerWidth =
			previewContainer.value?.clientWidth || window.innerWidth;
		const targetWidth = containerWidth - 32; // padding
		const computedZoom = parseFloat((targetWidth / 793.7).toFixed(2));
		zoom.value = Math.min(0.9, Math.max(0.3, computedZoom));
	}
};

watch(previewModeMobile, (val) => {
	if (val) {
		setTimeout(updateMobileZoom, 60);
	}
});

// Save states
const isSaving = ref(false);
const saveStatus = ref<"saved" | "saving" | "error" | "modified">("saved");
const lastSavedTime = ref<string>("");

// Undo/Redo Stacks
const undoStack = ref<string[]>([]);
const redoStack = ref<string[]>([]);
let isApplyingHistory = false;

const pushStateToHistory = () => {
	if (isApplyingHistory) return;
	const state = JSON.stringify(cvData.value);

	// Only push if different from last element
	if (
		undoStack.value.length === 0 ||
		undoStack.value[undoStack.value.length - 1] !== state
	) {
		undoStack.value.push(state);
		// Limit stack size to 25
		if (undoStack.value.length > 25) {
			undoStack.value.shift();
		}
		redoStack.value = []; // Clear redo stack on new action
	}
};

const undo = () => {
	if (undoStack.value.length > 1) {
		isApplyingHistory = true;
		const currentState = undoStack.value.pop();
		if (currentState) redoStack.value.push(currentState);

		const prevState = undoStack.value[undoStack.value.length - 1];
		if (prevState) {
			cvData.value = JSON.parse(prevState);
			saveStatus.value = "modified";
		}
		isApplyingHistory = false;
	}
};

const redo = () => {
	if (redoStack.value.length > 0) {
		isApplyingHistory = true;
		const nextState = redoStack.value.pop();
		if (nextState) {
			undoStack.value.push(nextState);
			cvData.value = JSON.parse(nextState);
			saveStatus.value = "modified";
		}
		isApplyingHistory = false;
	}
};

// Deep watch changes for autosave
watch(
	cvData,
	() => {
		saveStatus.value = "modified";
		pushStateToHistory();
	},
	{ deep: true },
);

// Initial history load
onMounted(() => {
	pushStateToHistory();
	updateMobileZoom();

	if (typeof window !== "undefined") {
		window.addEventListener("resize", updateMobileZoom);
	}

	// Auto-save interval (10 seconds)
	const interval = setInterval(() => {
		if (saveStatus.value === "modified") {
			autoSave();
		}
	}, 10000);

	onBeforeUnmount(() => {
		clearInterval(interval);
		if (typeof window !== "undefined") {
			window.removeEventListener("resize", updateMobileZoom);
		}
	});
});

const toggleAccordion = (section: string) => {
	activeAccordion.value = activeAccordion.value === section ? "" : section;
};

// Repeater Add/Remove helpers
const addEducation = () => {
	cvData.value.education.push({
		id: Date.now(),
		school: "",
		degree: "",
		field: "",
		start_date: "",
		end_date: "",
		description: "",
	});
};

const removeEducation = (index: number) => {
	cvData.value.education.splice(index, 1);
};

const addExperience = () => {
	cvData.value.experience.push({
		id: Date.now(),
		company: "",
		position: "",
		start_date: "",
		end_date: "",
		description: "",
	});
};

const removeExperience = (index: number) => {
	cvData.value.experience.splice(index, 1);
};

const addOrganization = () => {
	cvData.value.organizations.push({
		id: Date.now(),
		name: "",
		role: "",
		start_date: "",
		end_date: "",
		description: "",
	});
};

const removeOrganization = (index: number) => {
	cvData.value.organizations.splice(index, 1);
};

const addSkill = () => {
	cvData.value.skills.push({
		id: Date.now(),
		name: "",
		level: 80,
	});
};

const removeSkill = (index: number) => {
	cvData.value.skills.splice(index, 1);
};

const addCertification = () => {
	cvData.value.certifications.push({
		id: Date.now(),
		name: "",
		issuer: "",
		date: "",
		credential_id: "",
		credential_url: "",
	});
};

const removeCertification = (index: number) => {
	cvData.value.certifications.splice(index, 1);
};

const addTraining = () => {
	cvData.value.trainings.push({
		id: Date.now(),
		name: "",
		provider: "",
		date: "",
		description: "",
	});
};

const removeTraining = (index: number) => {
	cvData.value.trainings.splice(index, 1);
};

const addAchievement = () => {
	cvData.value.achievements.push({
		id: Date.now(),
		title: "",
		date: "",
		description: "",
	});
};

const removeAchievement = (index: number) => {
	cvData.value.achievements.splice(index, 1);
};

const addLanguage = () => {
	cvData.value.languages.push({
		id: Date.now(),
		name: "",
		proficiency: "Professional working proficiency",
	});
};

const removeLanguage = (index: number) => {
	cvData.value.languages.splice(index, 1);
};

const addReference = () => {
	cvData.value.references.push({
		id: Date.now(),
		name: "",
		position: "",
		company: "",
		contact: "",
	});
};

const removeReference = (index: number) => {
	cvData.value.references.splice(index, 1);
};

const templateSupportsPhoto = computed(() => {
	return ["modern-sidebar", "student-resume"].includes(
		cvData.value.template_id,
	);
});

// ─── Template Section Definitions ───────────────────────────────────────────
// Each template only shows relevant form sections to reduce clutter
const TEMPLATE_SECTIONS: Record<string, string[]> = {
	"ats-professional": [
		"personal",
		"experience",
		"education",
		"skills",
		"certifications",
	],
	"modern-sidebar": [
		"personal",
		"experience",
		"education",
		"skills",
		"languages",
	],
	executive: [
		"personal",
		"experience",
		"education",
		"organizations",
		"achievements",
		"references",
	],
	"creative-minimal": [
		"personal",
		"education",
		"skills",
		"certifications",
		"trainings",
		"achievements",
	],
	"student-resume": [
		"personal",
		"education",
		"organizations",
		"skills",
		"certifications",
		"trainings",
		"achievements",
	],
	custom: [
		"personal",
		"experience",
		"education",
		"organizations",
		"skills",
		"certifications",
		"trainings",
		"achievements",
		"languages",
		"references",
	],
};

// All possible optional sections (excluding 'personal' which is always shown)
const ALL_OPTIONAL_SECTIONS = [
	{ key: "experience", label: "Pengalaman Kerja", icon: "Briefcase" },
	{ key: "education", label: "Pendidikan", icon: "GraduationCap" },
	{ key: "organizations", label: "Organisasi", icon: "Users" },
	{ key: "skills", label: "Keahlian", icon: "Award" },
	{ key: "certifications", label: "Sertifikasi", icon: "BookOpen" },
	{ key: "trainings", label: "Pelatihan & Kursus", icon: "Sparkles" },
	{ key: "achievements", label: "Prestasi", icon: "Award" },
	{ key: "languages", label: "Bahasa", icon: "Languages" },
	{ key: "references", label: "Referensi Kerja", icon: "Users" },
];

// For Custom template: user-toggled sections (stored in customization)
const customActiveSections = computed({
	get: () => {
		const stored = cvData.value.customization?.custom_sections;
		if (Array.isArray(stored)) return stored;
		return ["experience", "education", "skills"]; // default custom sections
	},
	set: (val: string[]) => {
		if (!cvData.value.customization) cvData.value.customization = {};
		cvData.value.customization.custom_sections = val;
	},
});

const toggleCustomSection = (key: string) => {
	const current = [...customActiveSections.value];
	const idx = current.indexOf(key);
	if (idx === -1) current.push(key);
	else current.splice(idx, 1);
	customActiveSections.value = current;
};

// Active sections based on current template
const activeSections = computed(() => {
	const t = cvData.value.template_id;
	if (t === "custom") {
		return ["personal", ...customActiveSections.value];
	}
	return TEMPLATE_SECTIONS[t] ?? TEMPLATE_SECTIONS["ats-professional"];
});

const hasSection = (key: string) => activeSections.value.includes(key);

/**
 * Determines whether the CV has enough data to be considered complete / "Siap Guna".
 * Rules vary per template:
 * - Templates with experience: name, email, phone, summary + (1 edu OR 1 exp)
 * - Templates without experience (creative/student): name, email, phone, summary + 1 edu
 */
const isComplete = computed(() => {
	const pi = cvData.value.personal_info;
	const hasBasic =
		pi?.name?.trim() &&
		pi?.email?.trim() &&
		pi?.phone?.trim() &&
		pi?.summary?.trim();

	const secs = activeSections.value;
	const needsExp = secs.includes("experience");
	const hasEdCount = cvData.value.education?.length > 0;
	const hasExpCount = cvData.value.experience?.length > 0;

	const hasSectionData = needsExp ? hasEdCount || hasExpCount : hasEdCount;

	return !!(hasBasic && hasSectionData);
});

// Auto-sync status based on completeness
watch(
	isComplete,
	(complete) => {
		cvData.value.status = complete ? "published" : "draft";
	},
	{ immediate: true },
);

// Toast System
const showToast = ref(false);
const toastMessage = ref("");
const toastType = ref<"success" | "error" | "info">("success");
let toastTimeout: any = null;

const triggerToast = (
	message: string,
	type: "success" | "error" | "info" = "success",
) => {
	toastMessage.value = message;
	toastType.value = type;
	showToast.value = true;
	if (toastTimeout) clearTimeout(toastTimeout);
	toastTimeout = setTimeout(() => {
		showToast.value = false;
	}, 3000);
};

// API calls
const autoSave = async () => {
	if (isSaving.value) return;
	isSaving.value = true;
	saveStatus.value = "saving";

	try {
		const res = await axios.put(`/pagi/cv/${cvData.value.id}`, cvData.value);
		if (res.data.success) {
			saveStatus.value = "saved";
			const date = new Date();
			lastSavedTime.value = date.toLocaleTimeString("id-ID", {
				hour: "2-digit",
				minute: "2-digit",
				second: "2-digit",
			});
		}
	} catch (e) {
		saveStatus.value = "error";
		console.error("Autosave CV failed:", e);
	} finally {
		isSaving.value = false;
	}
};

const manualSave = async () => {
	if (isSaving.value) return;
	isSaving.value = true;
	saveStatus.value = "saving";
	triggerToast("Sedang menyimpan...", "info");

	try {
		const res = await axios.put(`/pagi/cv/${cvData.value.id}`, cvData.value);
		if (res.data.success) {
			saveStatus.value = "saved";
			triggerToast("CV Berhasil Disimpan! Mengalihkan...", "success");
			// Redirect back to CV dashboard after short delay
			setTimeout(() => {
				window.location.href = "/pagi/cv";
			}, 900);
		} else {
			triggerToast("Gagal menyimpan CV.", "error");
		}
	} catch (e: any) {
		saveStatus.value = "error";
		console.error("Manual CV save failed:", e);
		const errorMsg =
			e.response?.data?.message ||
			"Gagal menyimpan CV. Silakan periksa input Anda.";
		triggerToast(errorMsg, "error");
	} finally {
		isSaving.value = false;
	}
};

const isSyncing = ref(false);
const syncFromProfile = async () => {
	if (isSyncing.value) return;
	isSyncing.value = true;
	triggerToast("Menyinkronkan data dari profil...", "info");

	try {
		const res = await axios.get("/pagi/cv/profile-data");
		const data = res.data;

		if (data) {
			// Merge personal info
			cvData.value.personal_info = {
				...cvData.value.personal_info,
				...data.personal_info,
			};

			// Sync skills, certificates, and languages if available
			if (data.skills && data.skills.length > 0) {
				cvData.value.skills = [...data.skills];
			}
			if (data.certifications && data.certifications.length > 0) {
				cvData.value.certifications = [...data.certifications];
			}
			if (data.languages && data.languages.length > 0) {
				cvData.value.languages = [...data.languages];
			}

			triggerToast("Data profil berhasil disinkronkan ke CV!", "success");
			saveStatus.value = "modified";
		} else {
			triggerToast("Gagal menyinkronkan data profil.", "error");
		}
	} catch (e) {
		console.error("Profile sync failed:", e);
		triggerToast("Gagal mengambil data profil.", "error");
	} finally {
		isSyncing.value = false;
	}
};

const photoInput = ref<HTMLInputElement | null>(null);
const isUploadingPhoto = ref(false);

const triggerPhotoUpload = () => {
	photoInput.value?.click();
};

const handlePhotoUpload = async (event: Event) => {
	const target = event.target as HTMLInputElement;
	if (target.files?.[0]) {
		const file = target.files[0];
		const formData = new FormData();
		formData.append("photo", file);

		isUploadingPhoto.value = true;
		try {
			const res = await axios.post(
				`/pagi/cv/${cvData.value.id}/upload-photo`,
				formData,
				{
					headers: {
						"Content-Type": "multipart/form-data",
					},
				},
			);
			if (res.data.success) {
				cvData.value.personal_info.foto_path = res.data.path;
				saveStatus.value = "modified";
			}
		} catch (e) {
			console.error("Failed to upload CV photo:", e);
			alert(
				"Gagal mengunggah foto. Pastikan format gambar sesuai dan ukuran maksimal 2MB.",
			);
		} finally {
			isUploadingPhoto.value = false;
		}
	}
};

const resetPhotoToDefault = () => {
	const mainUser = page.props.auth?.user as any;
	cvData.value.personal_info.foto_path = mainUser?.foto_path || "";
	saveStatus.value = "modified";
};

const getPhotoUrl = (path: string) => {
	if (!path) return "";
	if (path.startsWith("http") || path.startsWith("data:")) return path;
	if (path.startsWith("/storage")) return path;
	return `/storage/${path}`;
};

// Status is auto-computed from isComplete — no manual toggle needed.

const downloadPdf = async () => {
	if (!isComplete.value) {
		triggerToast(
			"CV belum lengkap. Isi nama, email, telepon, ringkasan, dan minimal 1 pendidikan atau pengalaman.",
			"error",
		);
		return;
	}
	triggerToast("Menyiapkan file PDF...", "info");
	// Save first so PDF reflects latest data
	await autoSave();

	// Native link to bypass Inertia interception
	const link = document.createElement("a");
	link.href = `/pagi/cv/${cvData.value.id}/download`;
	link.setAttribute(
		"download",
		`${cvData.value.title.toLowerCase().replace(/[^a-z0-9]+/g, "-")}.pdf`,
	);
	link.setAttribute("rel", "external");
	document.body.appendChild(link);
	link.click();
	document.body.removeChild(link);
};
</script>

<template>
    <Head :title="`${cvData.title} — CV Builder`" />

    <div class="min-h-screen bg-slate-100 dark:bg-zinc-950 font-sans text-slate-800 dark:text-zinc-100 flex flex-col">
        <!-- Main Top Bar -->
        <header class="bg-white dark:bg-zinc-900 border-b border-slate-200/80 dark:border-zinc-900 px-3 xs:px-4 sm:px-6 py-2.5 sm:py-3 shrink-0 select-none z-50 flex items-center justify-between gap-2 sm:gap-4">
            <div class="flex items-center gap-2 sm:gap-4 min-w-0">
                <Link 
                    href="/pagi/cv"
                    class="p-1.5 sm:p-2 hover:bg-slate-50 dark:hover:bg-zinc-800 rounded-xl text-slate-500 hover:text-slate-800 dark:hover:text-white shrink-0 border border-slate-200/50 dark:border-zinc-800"
                >
                    <ArrowLeft class="w-4 h-4" />
                </Link>
                
                <!-- Editable CV Title -->
                <div class="space-y-0.5 min-w-0">
                    <input 
                        type="text" 
                        v-model="cvData.title"
                        class="bg-transparent border-none outline-none font-bold text-xs sm:text-sm text-slate-800 dark:text-white focus:ring-1 focus:ring-indigo-500 px-1 py-0.5 rounded truncate max-w-[100px] xs:max-w-[160px] sm:max-w-sm"
                    />
                    <div class="flex flex-wrap items-center gap-x-1 sm:gap-x-2 text-[9px] sm:text-[10px] text-slate-400">
                        <span class="capitalize hidden sm:inline">{{ cvData.template_id.replace('-', ' ') }}</span>
                        <span class="hidden sm:inline">•</span>
                        <!-- Auto Status Badge (read-only, driven by completeness) -->
                        <span
                            class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] sm:text-[9px] font-black tracking-wider uppercase shrink-0 select-none"
                            :style="{ backgroundColor: isComplete ? 'rgba(16, 185, 129, 0.12)' : 'rgba(245, 158, 11, 0.12)', color: isComplete ? '#10b981' : '#f59e0b' }"
                        >
                            {{ isComplete ? 'Siap' : 'Draf' }}
                        </span>
                        <span>•</span>
                        <!-- Save Indicator -->
                        <span v-if="saveStatus === 'saving'" class="flex items-center gap-0.5 text-indigo-500 font-semibold">
                            <Loader2 class="w-3 h-3 animate-spin shrink-0" /> <span class="hidden xs:inline">Menyimpan...</span><span class="xs:hidden">Proses...</span>
                        </span>
                        <span v-else-if="saveStatus === 'saved'" class="text-emerald-500 font-semibold flex items-center gap-0.5">
                            <Check class="w-3 h-3 shrink-0" /> <span class="hidden xs:inline">Tersimpan</span> {{ lastSavedTime ? lastSavedTime : '' }}
                        </span>
                        <span v-else-if="saveStatus === 'modified'" class="text-amber-500 font-semibold truncate max-w-[80px] sm:max-w-none">
                            Ada perubahan
                        </span>
                        <span v-else class="text-red-500 font-semibold">
                            Gagal
                        </span>
                    </div>
                </div>
            </div>

            <!-- Toolbar Action Buttons -->
            <div class="flex items-center gap-1.5 sm:gap-2">
                <!-- Undo / Redo (hidden on mobile) -->
                <div class="hidden md:flex items-center gap-1 bg-slate-50 dark:bg-zinc-950 p-1 rounded-xl border border-slate-200/50 dark:border-zinc-850">
                    <button 
                        @click="undo"
                        :disabled="undoStack.length <= 1"
                        class="p-1.5 rounded-lg text-slate-500 hover:text-slate-800 dark:hover:text-white disabled:opacity-30 disabled:cursor-not-allowed bg-transparent border-none cursor-pointer"
                        title="Urungkan"
                    >
                        <Undo2 class="w-4 h-4" />
                    </button>
                    <button 
                        @click="redo"
                        :disabled="redoStack.length === 0"
                        class="p-1.5 rounded-lg text-slate-500 hover:text-slate-800 dark:hover:text-white disabled:opacity-30 disabled:cursor-not-allowed bg-transparent border-none cursor-pointer"
                        title="Ulangi"
                    >
                        <Redo2 class="w-4 h-4" />
                    </button>
                </div>

                <!-- Preview Mode toggle (Mobile only) -->
                <button
                    @click="previewModeMobile = !previewModeMobile"
                    class="md:hidden flex items-center gap-1 px-2 py-2 rounded-xl text-xs font-bold border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shrink-0 cursor-pointer"
                >
                    <Eye class="w-3.5 h-3.5" />
                    <span>{{ previewModeMobile ? 'Form' : 'Pratinjau' }}</span>
                </button>

                <button 
                    @click="manualSave"
                    class="inline-flex items-center justify-center gap-1 rounded-xl border border-slate-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:bg-slate-50 px-2 sm:px-3 py-2 sm:py-2.5 text-xs font-bold text-slate-700 dark:text-zinc-300 shadow-3xs transition-all cursor-pointer shrink-0"
                >
                    <Save class="w-3.5 h-3.5 shrink-0" />
                    <span class="hidden xs:inline">Simpan</span>
                </button>

                <button 
                    @click="downloadPdf"
                    :disabled="!isComplete"
                    :title="!isComplete ? 'Lengkapi CV terlebih dahulu sebelum mengunduh' : 'Unduh sebagai PDF'"
                    class="inline-flex items-center justify-center gap-1 rounded-lg px-2 sm:px-3 py-2 sm:py-2.5 text-xs font-bold shadow-lg transition-all cursor-pointer shrink-0 disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="isComplete ? 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-indigo-600/10 hover:shadow-indigo-600/20' : 'bg-slate-200 dark:bg-zinc-800 text-slate-400 dark:text-zinc-500'"
                >
                    <Download class="w-3.5 h-3.5 shrink-0" />
                    <span class="hidden xs:inline">Unduh PDF</span>
                </button>
            </div>
        </header>

        <!-- Builder Workspace Container -->
        <div class="flex-1 flex min-h-0 relative">
            
            <!-- LEFT AREA: FORMS & CUSTOMIZER -->
            <div 
                class="w-full md:w-[40%] flex flex-col bg-white dark:bg-zinc-900 border-r border-slate-200/80 dark:border-zinc-900 min-h-0"
                v-show="!previewModeMobile"
            >
                <!-- Switcher Data / Customizer Design -->
                <div class="flex border-b border-slate-100 dark:border-zinc-850 p-2 shrink-0 bg-slate-50/50 dark:bg-zinc-900/40 select-none">
                    <button 
                        @click="activeTab = 'form'"
                        class="flex-1 py-2 rounded-xl text-xs font-extrabold transition-all border-none bg-transparent cursor-pointer flex items-center justify-center gap-2"
                        :class="activeTab === 'form' ? 'bg-white dark:bg-zinc-900 text-indigo-600 dark:text-indigo-400 shadow-2xs font-black border border-slate-200/50 dark:border-zinc-800' : 'text-slate-500 hover:text-slate-800 dark:hover:text-zinc-350'"
                    >
                        <FileSignature class="w-4 h-4" />
                        Isi Data CV
                    </button>
                    <button 
                        @click="activeTab = 'customizer'"
                        class="flex-1 py-2 rounded-xl text-xs font-extrabold transition-all border-none bg-transparent cursor-pointer flex items-center justify-center gap-2"
                        :class="activeTab === 'customizer' ? 'bg-white dark:bg-zinc-900 text-indigo-600 dark:text-indigo-400 shadow-2xs font-black border border-slate-200/50 dark:border-zinc-800' : 'text-slate-500 hover:text-slate-800 dark:hover:text-zinc-350'"
                    >
                        <SlidersHorizontal class="w-4 h-4" />
                        Desain & Tata Letak
                    </button>
                </div>

                <!-- Left sidebar contents (scrollable) -->
                <div class="flex-1 overflow-y-auto min-h-0">
                    
                    <!-- TAB 1: FORM SECTIONS -->
                    <div v-show="activeTab === 'form'" class="divide-y divide-slate-100 dark:divide-zinc-850">
                        
                        <!-- Completeness Banner -->
                        <div 
                            class="p-3 text-[11px] font-semibold flex items-start gap-2.5 border-b"
                            :class="isComplete 
                                ? 'bg-emerald-50/60 dark:bg-emerald-950/20 border-emerald-100 dark:border-emerald-900/30 text-emerald-700 dark:text-emerald-400' 
                                : 'bg-amber-50/60 dark:bg-amber-950/20 border-amber-100 dark:border-amber-900/30 text-amber-700 dark:text-amber-400'"
                        >
                            <Check v-if="isComplete" class="w-3.5 h-3.5 mt-0.5 shrink-0" />
                            <AlertCircle v-else class="w-3.5 h-3.5 mt-0.5 shrink-0" />
                            <span v-if="isComplete">CV sudah lengkap dan siap diunduh sebagai PDF.</span>
                            <span v-else>
                                Untuk mengunduh PDF, isi <strong>nama, email, no. telepon, ringkasan</strong>,
                                dan tambahkan minimal
                                <strong v-if="activeSections.includes('experience')">1 pendidikan atau pengalaman kerja</strong>
                                <strong v-else>1 data pendidikan</strong>.
                            </span>
                        </div>

                        <!-- CUSTOM TEMPLATE: Section Picker Panel -->
                        <div v-if="cvData.template_id === 'custom'" class="p-3 border-b border-slate-100 dark:border-zinc-850 bg-indigo-50/50 dark:bg-indigo-950/10">
                            <div class="flex items-center gap-2 mb-2.5">
                                <LayoutGrid class="w-3.5 h-3.5 text-indigo-500" />
                                <span class="text-[11px] font-black text-slate-700 dark:text-zinc-300">Pilih Section yang Ingin Ditampilkan</span>
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                <button
                                    v-for="sec in ALL_OPTIONAL_SECTIONS"
                                    :key="sec.key"
                                    @click="toggleCustomSection(sec.key)"
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-bold border transition-all cursor-pointer"
                                    :class="customActiveSections.includes(sec.key)
                                        ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm'
                                        : 'bg-white dark:bg-zinc-900 text-slate-500 dark:text-zinc-400 border-slate-200 dark:border-zinc-800 hover:border-indigo-400'"
                                >
                                    <Check v-if="customActiveSections.includes(sec.key)" class="w-2.5 h-2.5" />
                                    {{ sec.label }}
                                </button>
                            </div>
                        </div>

                        <!-- 1. PERSONAL INFORMATION ACCORDION -->

                        <div class="bg-white dark:bg-zinc-900">
                            <button 
                                @click="toggleAccordion('personal')"
                                class="w-full flex items-center justify-between p-4 font-bold text-xs hover:bg-slate-50/80 dark:hover:bg-zinc-850/30 text-left border-none bg-transparent cursor-pointer"
                            >
                                <span class="flex items-center gap-2 text-slate-800 dark:text-zinc-150 font-black">
                                    <User class="w-4.5 h-4.5 text-indigo-500 shrink-0" />
                                    1. Informasi Pribadi
                                </span>
                                <ChevronDown class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': activeAccordion === 'personal' }" />
                            </button>
                            
                            <div v-show="activeAccordion === 'personal'" class="p-4 pt-0 space-y-4 border-t border-slate-50 dark:border-zinc-850/50 pt-4">
                                <!-- Sync From Profile Button -->
                                <div class="flex justify-end pt-2">
                                    <button 
                                        type="button"
                                        @click="syncFromProfile"
                                        :disabled="isSyncing"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-55/70 hover:bg-indigo-100/80 dark:bg-indigo-950/45 dark:hover:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 text-xs font-bold transition-all disabled:opacity-50 border border-indigo-100/50 dark:border-indigo-900/30 cursor-pointer"
                                    >
                                        <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': isSyncing }" />
                                        <span>Sinkronisasi dari Profil</span>
                                    </button>
                                </div>

                                <!-- Profile Photo Upload Widget -->
                                <div v-if="templateSupportsPhoto" class="flex items-center gap-4 bg-slate-50/50 dark:bg-zinc-900/40 p-3 rounded-xl border border-slate-100 dark:border-zinc-850">
                                    <div class="relative w-16 h-16 rounded-full overflow-hidden border border-slate-200 dark:border-zinc-850 bg-slate-100 dark:bg-zinc-850 shrink-0">
                                        <div v-if="isUploadingPhoto" class="w-full h-full animate-pulse bg-slate-200 dark:bg-zinc-700 flex items-center justify-center">
                                            <Loader2 class="w-5 h-5 text-indigo-500 animate-spin" />
                                        </div>
                                        <img v-else-if="cvData.personal_info.foto_path" :src="getPhotoUrl(cvData.personal_info.foto_path)" class="w-full h-full object-cover" />
                                        <div v-else class="w-full h-full flex items-center justify-center text-slate-400 dark:text-zinc-500 font-bold text-lg">
                                            {{ (cvData.personal_info.name || 'U').charAt(0).toUpperCase() }}
                                        </div>
                                    </div>
                                    <div class="space-y-1.5">
                                        <div class="text-[11px] font-bold text-slate-700 dark:text-zinc-300">Foto Profil CV</div>
                                        <div class="flex flex-wrap gap-2">
                                            <input 
                                                type="file" 
                                                ref="photoInput" 
                                                @change="handlePhotoUpload" 
                                                accept="image/*" 
                                                class="hidden" 
                                            />
                                            <button 
                                                @click="triggerPhotoUpload" 
                                                :disabled="isUploadingPhoto"
                                                class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-550 text-white text-[10px] font-bold transition-all disabled:opacity-50 cursor-pointer border-none"
                                            >
                                                {{ isUploadingPhoto ? 'Mengunggah...' : 'Pilih Foto' }}
                                            </button>
                                            <button 
                                                @click="resetPhotoToDefault" 
                                                class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-850 text-slate-600 dark:text-zinc-300 text-[10px] font-bold transition-all bg-transparent cursor-pointer"
                                            >
                                                Reset ke Default
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <label class="text-[11px] font-bold text-slate-500">Nama Lengkap</label>
                                        <input type="text" v-model="cvData.personal_info.name" placeholder="cth. John Doe" disabled class="w-full bg-slate-100 dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold text-slate-400 dark:text-zinc-500 cursor-not-allowed select-none" />
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[11px] font-bold text-slate-500">Gelar / Bidang</label>
                                        <AutocompleteInput v-model="cvData.personal_info.job_title" :suggestions="SUGGESTIONS.jobTitle" placeholder="cth. Product Designer" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <label class="text-[11px] font-bold text-slate-500">Email</label>
                                        <input type="email" v-model="cvData.personal_info.email" placeholder="cth. mail@exam.com" disabled class="w-full bg-slate-100 dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold text-slate-400 dark:text-zinc-500 cursor-not-allowed select-none" />
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[11px] font-bold text-slate-500">No. Telepon</label>
                                        <input type="text" v-model="cvData.personal_info.phone" placeholder="cth. 081234..." class="w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[11px] font-bold text-slate-500">Lokasi / Alamat</label>
                                    <AutocompleteInput v-model="cvData.personal_info.location" :suggestions="SUGGESTIONS.location" placeholder="cth. Banyumas, Indonesia" />
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <label class="text-[11px] font-bold text-slate-500">Website / Portfolio</label>
                                        <input type="text" v-model="cvData.personal_info.website" placeholder="https://..." class="w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[11px] font-bold text-slate-500">LinkedIn</label>
                                        <input type="text" v-model="cvData.personal_info.linkedin" placeholder="linkedin.com/in/..." class="w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <label class="text-[11px] font-bold text-slate-500">GitHub</label>
                                        <input type="text" v-model="cvData.personal_info.github" placeholder="github.com/..." class="w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[11px] font-bold text-slate-500">Instagram</label>
                                        <input type="text" v-model="cvData.personal_info.instagram" placeholder="instagram.com/..." class="w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[11px] font-bold text-slate-500">Ringkasan Profesional / Profil</label>
                                    <textarea v-model="cvData.personal_info.summary" rows="4" placeholder="Tuliskan deskripsi singkat mengenai rekam jejak, keahlian, dan tujuan profesional Anda..." class="w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-indigo-500 resize-y"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- 2. EXPERIENCE REPEATER ACCORDION -->
                        <div v-if="hasSection('experience')" class="bg-white dark:bg-zinc-900">
                            <button 
                                @click="toggleAccordion('experience')"
                                class="w-full flex items-center justify-between p-4 font-bold text-xs hover:bg-slate-50/80 dark:hover:bg-zinc-850/30 text-left border-none bg-transparent cursor-pointer"
                            >
                                <span class="flex items-center gap-2 text-slate-800 dark:text-zinc-150 font-black">
                                    <Briefcase class="w-4.5 h-4.5 text-indigo-500 shrink-0" />
                                    2. Pengalaman Kerja
                                </span>
                                <ChevronDown class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': activeAccordion === 'experience' }" />
                            </button>
                            
                            <div v-show="activeAccordion === 'experience'" class="p-4 pt-0 space-y-4 border-t border-slate-50 dark:border-zinc-850/50">
                                <div v-for="(exp, index) in cvData.experience" :key="exp.id" class="border border-slate-200/60 dark:border-zinc-800 p-4 rounded-xl relative space-y-3 bg-slate-50/10 dark:bg-zinc-900/10">
                                    <!-- Delete single element -->
                                    <button 
                                        @click="removeExperience(index)"
                                        class="absolute top-3 right-3 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 p-1.5 rounded-lg border-none bg-transparent cursor-pointer"
                                    >
                                        <Trash2 class="w-3.5 h-3.5" />
                                    </button>
                                    
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-500">Nama Perusahaan / Organisasi</label>
                                            <input type="text" v-model="exp.company" placeholder="cth. GoTo" class="w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none" />
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-500">Jabatan / Posisi</label>
                                            <AutocompleteInput v-model="exp.position" :suggestions="SUGGESTIONS.position" placeholder="cth. Frontend Developer" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-500">Tanggal Mulai</label>
                                            <MonthYearPicker v-model="exp.start_date" placeholder="cth. Jan 2024" />
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-500">Tanggal Selesai</label>
                                            <MonthYearPicker v-model="exp.end_date" placeholder="cth. Sekarang" />
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[11px] font-bold text-slate-500">Deskripsi Pekerjaan</label>
                                        <textarea v-model="exp.description" rows="3" placeholder="- Mengembangkan antarmuka landing page menggunakan Vue.js..." class="w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none resize-y"></textarea>
                                    </div>
                                </div>

                                <button 
                                    @click="addExperience"
                                    class="w-full flex items-center justify-center gap-1.5 border border-dashed border-slate-300 dark:border-zinc-850 hover:bg-slate-50 dark:hover:bg-zinc-900 rounded-xl p-3 text-xs font-bold text-indigo-600 dark:text-indigo-400 transition-all cursor-pointer bg-transparent"
                                >
                                    <Plus class="w-4 h-4" />
                                    Tambah Pengalaman Kerja
                                </button>
                            </div>
                        </div>

                        <!-- 3. EDUCATION REPEATER ACCORDION -->
                        <div v-if="hasSection('education')" class="bg-white dark:bg-zinc-900">
                            <button 
                                @click="toggleAccordion('education')"
                                class="w-full flex items-center justify-between p-4 font-bold text-xs hover:bg-slate-50/80 dark:hover:bg-zinc-850/30 text-left border-none bg-transparent cursor-pointer"
                            >
                                <span class="flex items-center gap-2 text-slate-800 dark:text-zinc-150 font-black">
                                    <GraduationCap class="w-4.5 h-4.5 text-indigo-500 shrink-0" />
                                    3. Pendidikan
                                </span>
                                <ChevronDown class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': activeAccordion === 'education' }" />
                            </button>
                            
                            <div v-show="activeAccordion === 'education'" class="p-4 pt-0 space-y-4 border-t border-slate-50 dark:border-zinc-850/50">
                                <div v-for="(edu, index) in cvData.education" :key="edu.id" class="border border-slate-200/60 dark:border-zinc-800 p-4 rounded-xl relative space-y-3 bg-slate-50/10 dark:bg-zinc-900/10">
                                    <button 
                                        @click="removeEducation(index)"
                                        class="absolute top-3 right-3 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 p-1.5 rounded-lg border-none bg-transparent cursor-pointer"
                                    >
                                        <Trash2 class="w-3.5 h-3.5" />
                                    </button>

                                    <div class="space-y-1 pt-2">
                                        <label class="text-[11px] font-bold text-slate-500">Nama Universitas / Institusi</label>
                                        <input type="text" v-model="edu.school" placeholder="cth. Universitas FMIKOM" class="w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none" />
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-500">Gelar / Kualifikasi</label>
                                            <AutocompleteInput v-model="edu.degree" :suggestions="SUGGESTIONS.degree" placeholder="cth. Sarjana Komputer (S.Kom)" />
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-500">Program Studi / Bidang</label>
                                            <AutocompleteInput v-model="edu.field" :suggestions="SUGGESTIONS.field" placeholder="cth. Teknik Informatika" />
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-500">Tahun Mulai</label>
                                            <MonthYearPicker v-model="edu.start_date" placeholder="cth. 2021" />
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-500">Tahun Selesai (atau Estimasi)</label>
                                            <MonthYearPicker v-model="edu.end_date" placeholder="cth. 2025" />
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[11px] font-bold text-slate-500">Pencapaian / Catatan Tambahan</label>
                                        <textarea v-model="edu.description" rows="2" placeholder="cth. IPK: 3.85/4.00, Aktif di Senat Mahasiswa..." class="w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none resize-y"></textarea>
                                    </div>
                                </div>

                                <button 
                                    @click="addEducation"
                                    class="w-full flex items-center justify-center gap-1.5 border border-dashed border-slate-300 dark:border-zinc-850 hover:bg-slate-50 dark:hover:bg-zinc-900 rounded-xl p-3 text-xs font-bold text-indigo-600 dark:text-indigo-400 transition-all cursor-pointer bg-transparent"
                                >
                                    <Plus class="w-4 h-4" />
                                    Tambah Pendidikan
                                </button>
                            </div>
                        </div>

                        <!-- 4. ORGANIZATIONS REPEATER ACCORDION -->
                        <div v-if="hasSection('organizations')" class="bg-white dark:bg-zinc-900">
                            <button 
                                @click="toggleAccordion('organizations')"
                                class="w-full flex items-center justify-between p-4 font-bold text-xs hover:bg-slate-50/80 dark:hover:bg-zinc-850/30 text-left border-none bg-transparent cursor-pointer"
                            >
                                <span class="flex items-center gap-2 text-slate-800 dark:text-zinc-150 font-black">
                                    <Users class="w-4.5 h-4.5 text-indigo-500 shrink-0" />
                                    4. Organisasi & Kegiatan
                                </span>
                                <ChevronDown class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': activeAccordion === 'organizations' }" />
                            </button>
                            
                            <div v-show="activeAccordion === 'organizations'" class="p-4 pt-0 space-y-4 border-t border-slate-50 dark:border-zinc-850/50">
                                <div v-for="(org, index) in cvData.organizations" :key="org.id" class="border border-slate-200/60 dark:border-zinc-800 p-4 rounded-xl relative space-y-3 bg-slate-50/10 dark:bg-zinc-900/10">
                                    <button 
                                        @click="removeOrganization(index)"
                                        class="absolute top-3 right-3 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 p-1.5 rounded-lg border-none bg-transparent cursor-pointer"
                                    >
                                        <Trash2 class="w-3.5 h-3.5" />
                                    </button>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-500">Nama Organisasi</label>
                                            <input type="text" v-model="org.name" placeholder="cth. Himpunan Mahasiswa Komputer" class="w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200/60 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none" />
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-500">Peran / Jabatan</label>
                                            <AutocompleteInput v-model="org.role" :suggestions="SUGGESTIONS.role" placeholder="cth. Ketua Himpunan" />
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-500">Tanggal Mulai</label>
                                            <MonthYearPicker v-model="org.start_date" placeholder="cth. Jan 2023" />
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-500">Tanggal Selesai</label>
                                            <MonthYearPicker v-model="org.end_date" placeholder="cth. Des 2023" />
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[11px] font-bold text-slate-500">Tugas / Kontribusi</label>
                                        <textarea v-model="org.description" rows="2" placeholder="Menyusun program kerja, memimpin 30 anggota..." class="w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none resize-y"></textarea>
                                    </div>
                                </div>

                                <button 
                                    @click="addOrganization"
                                    class="w-full flex items-center justify-center gap-1.5 border border-dashed border-slate-300 dark:border-zinc-850 hover:bg-slate-50 dark:hover:bg-zinc-900 rounded-xl p-3 text-xs font-bold text-indigo-600 dark:text-indigo-400 transition-all cursor-pointer bg-transparent"
                                >
                                    <Plus class="w-4 h-4" />
                                    Tambah Kegiatan Organisasi
                                </button>
                            </div>
                        </div>

                        <!-- 5. SKILLS REPEATER ACCORDION -->
                        <div v-if="hasSection('skills')" class="bg-white dark:bg-zinc-900">
                            <button 
                                @click="toggleAccordion('skills')"
                                class="w-full flex items-center justify-between p-4 font-bold text-xs hover:bg-slate-50/80 dark:hover:bg-zinc-850/30 text-left border-none bg-transparent cursor-pointer"
                            >
                                <span class="flex items-center gap-2 text-slate-800 dark:text-zinc-150 font-black">
                                    <Award class="w-4.5 h-4.5 text-indigo-500 shrink-0" />
                                    5. Keahlian
                                </span>
                                <ChevronDown class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': activeAccordion === 'skills' }" />
                            </button>
                            
                            <div v-show="activeAccordion === 'skills'" class="p-4 pt-0 space-y-3 border-t border-slate-50 dark:border-zinc-850/50">
                                <div v-for="(sk, index) in cvData.skills" :key="sk.id" class="grid grid-cols-1 xs:grid-cols-[1fr_auto_auto] items-center gap-3 bg-slate-50/40 dark:bg-zinc-900/40 p-2.5 rounded-xl border border-slate-100 dark:border-zinc-850">
                                    <input type="text" v-model="sk.name" placeholder="Nama Keahlian" class="w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none" />
                                    
                                    <!-- Range Slider level 0-100 -->
                                    <div class="flex items-center gap-2 w-full xs:w-32 shrink-0 justify-between xs:justify-start">
                                        <input type="range" min="10" max="100" step="5" v-model.number="sk.level" class="w-full accent-indigo-600 dark:accent-indigo-400 cursor-pointer" />
                                        <span class="text-[10px] font-bold w-7 text-right shrink-0">{{ sk.level }}%</span>
                                    </div>

                                    <button 
                                        @click="removeSkill(index)"
                                        class="text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 p-1.5 rounded-lg border-none bg-transparent cursor-pointer justify-self-end xs:justify-self-auto"
                                    >
                                        <Trash2 class="w-3.5 h-3.5" />
                                    </button>
                                </div>

                                <button 
                                    @click="addSkill"
                                    class="w-full flex items-center justify-center gap-1.5 border border-dashed border-slate-300 dark:border-zinc-850 hover:bg-slate-50 dark:hover:bg-zinc-900 rounded-xl p-2.5 text-xs font-bold text-indigo-600 dark:text-indigo-400 transition-all cursor-pointer bg-transparent"
                                >
                                    <Plus class="w-3.5 h-3.5" />
                                    Tambah Keahlian
                                </button>
                            </div>
                        </div>

                        <!-- 6. CERTIFICATIONS ACCORDION -->
                        <div v-if="hasSection('certifications')" class="bg-white dark:bg-zinc-900">
                            <button 
                                @click="toggleAccordion('certifications')"
                                class="w-full flex items-center justify-between p-4 font-bold text-xs hover:bg-slate-50/80 dark:hover:bg-zinc-850/30 text-left border-none bg-transparent cursor-pointer"
                            >
                                <span class="flex items-center gap-2 text-slate-800 dark:text-zinc-150 font-black">
                                    <BookOpen class="w-4.5 h-4.5 text-indigo-500 shrink-0" />
                                    6. Sertifikasi
                                </span>
                                <ChevronDown class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': activeAccordion === 'certifications' }" />
                            </button>
                            
                            <div v-show="activeAccordion === 'certifications'" class="p-4 pt-0 space-y-4 border-t border-slate-50 dark:border-zinc-850/50">
                                <div v-for="(cert, index) in cvData.certifications" :key="cert.id" class="border border-slate-200/60 dark:border-zinc-800 p-4 rounded-xl relative space-y-3 bg-slate-50/10 dark:bg-zinc-900/10">
                                    <button 
                                        @click="removeCertification(index)"
                                        class="absolute top-3 right-3 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 p-1.5 rounded-lg border-none bg-transparent cursor-pointer"
                                    >
                                        <Trash2 class="w-3.5 h-3.5" />
                                    </button>

                                    <div class="space-y-1 pt-2">
                                        <label class="text-[11px] font-bold text-slate-500">Nama Sertifikasi / Lisensi</label>
                                        <input type="text" v-model="cert.name" placeholder="cth. Google Professional UX Certificate" class="w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none" />
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-500">Organisasi Penerbit</label>
                                            <AutocompleteInput v-model="cert.issuer" :suggestions="SUGGESTIONS.issuer" placeholder="cth. Coursera" />
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-500">Tanggal Diperoleh</label>
                                            <MonthYearPicker v-model="cert.date" placeholder="cth. Des 2025" />
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[11px] font-bold text-slate-500">ID Kredensial / No. Lisensi</label>
                                        <input type="text" v-model="cert.credential_id" placeholder="cth. G-18A8B..." class="w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none" />
                                    </div>
                                </div>

                                <button 
                                    @click="addCertification"
                                    class="w-full flex items-center justify-center gap-1.5 border border-dashed border-slate-300 dark:border-zinc-850 hover:bg-slate-50 dark:hover:bg-zinc-900 rounded-xl p-3 text-xs font-bold text-indigo-600 dark:text-indigo-400 transition-all cursor-pointer bg-transparent"
                                >
                                    <Plus class="w-4 h-4" />
                                    Tambah Sertifikasi
                                </button>
                            </div>
                        </div>

                        <!-- 7. TRAININGS ACCORDION -->
                        <div v-if="hasSection('trainings')" class="bg-white dark:bg-zinc-900">
                            <button 
                                @click="toggleAccordion('trainings')"
                                class="w-full flex items-center justify-between p-4 font-bold text-xs hover:bg-slate-50/80 dark:hover:bg-zinc-850/30 text-left border-none bg-transparent cursor-pointer"
                            >
                                <span class="flex items-center gap-2 text-slate-800 dark:text-zinc-150 font-black">
                                    <Sparkles class="w-4.5 h-4.5 text-indigo-500 shrink-0" />
                                    7. Pelatihan & Kursus
                                </span>
                                <ChevronDown class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': activeAccordion === 'trainings' }" />
                            </button>
                            
                            <div v-show="activeAccordion === 'trainings'" class="p-4 pt-0 space-y-4 border-t border-slate-50 dark:border-zinc-850/50">
                                <div v-for="(trn, index) in cvData.trainings" :key="trn.id" class="border border-slate-200/60 dark:border-zinc-800 p-4 rounded-xl relative space-y-3 bg-slate-50/10 dark:bg-zinc-900/10">
                                    <button 
                                        @click="removeTraining(index)"
                                        class="absolute top-3 right-3 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 p-1.5 rounded-lg border-none bg-transparent cursor-pointer"
                                    >
                                        <Trash2 class="w-3.5 h-3.5" />
                                    </button>

                                    <div class="space-y-1 pt-2">
                                        <label class="text-[11px] font-bold text-slate-500">Nama Pelatihan / Kursus</label>
                                        <input type="text" v-model="trn.name" placeholder="cth. Advanced Figma UI/UX Course" class="w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none" />
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-500">Penyelenggara / Provider</label>
                                            <input type="text" v-model="trn.provider" placeholder="cth. FMIKOM Academy" class="w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none" />
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-500">Tanggal / Waktu</label>
                                            <MonthYearPicker v-model="trn.date" placeholder="cth. Nov 2025" />
                                        </div>
                                    </div>
                                </div>

                                <button 
                                    @click="addTraining"
                                    class="w-full flex items-center justify-center gap-1.5 border border-dashed border-slate-300 dark:border-zinc-850 hover:bg-slate-50 dark:hover:bg-zinc-900 rounded-xl p-3 text-xs font-bold text-indigo-600 dark:text-indigo-400 transition-all cursor-pointer bg-transparent"
                                >
                                    <Plus class="w-4 h-4" />
                                    Tambah Pelatihan
                                </button>
                            </div>
                        </div>

                        <!-- 8. ACHIEVEMENTS ACCORDION -->
                        <div v-if="hasSection('achievements')" class="bg-white dark:bg-zinc-900">
                            <button 
                                @click="toggleAccordion('achievements')"
                                class="w-full flex items-center justify-between p-4 font-bold text-xs hover:bg-slate-50/80 dark:hover:bg-zinc-850/30 text-left border-none bg-transparent cursor-pointer"
                            >
                                <span class="flex items-center gap-2 text-slate-800 dark:text-zinc-150 font-black">
                                    <Award class="w-4.5 h-4.5 text-indigo-500 shrink-0" />
                                    8. Prestasi & Penghargaan
                                </span>
                                <ChevronDown class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': activeAccordion === 'achievements' }" />
                            </button>
                            
                            <div v-show="activeAccordion === 'achievements'" class="p-4 pt-0 space-y-4 border-t border-slate-50 dark:border-zinc-850/50">
                                <div v-for="(ach, index) in cvData.achievements" :key="ach.id" class="border border-slate-200/60 dark:border-zinc-800 p-4 rounded-xl relative space-y-3 bg-slate-50/10 dark:bg-zinc-900/10">
                                    <button 
                                        @click="removeAchievement(index)"
                                        class="absolute top-3 right-3 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 p-1.5 rounded-lg border-none bg-transparent cursor-pointer"
                                    >
                                        <Trash2 class="w-3.5 h-3.5" />
                                    </button>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-500">Nama Penghargaan / Prestasi</label>
                                            <input type="text" v-model="ach.title" placeholder="cth. Juara 1 UI/UX Competition" class="w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none" />
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-500">Tanggal / Tahun</label>
                                            <MonthYearPicker v-model="ach.date" placeholder="cth. Oktober 2025" />
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[11px] font-bold text-slate-500">Keterangan Singkat</label>
                                        <input type="text" v-model="ach.description" placeholder="cth. Diselenggarakan oleh Kemenristekdikti..." class="w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none" />
                                    </div>
                                </div>

                                <button 
                                    @click="addAchievement"
                                    class="w-full flex items-center justify-center gap-1.5 border border-dashed border-slate-300 dark:border-zinc-850 hover:bg-slate-50 dark:hover:bg-zinc-900 rounded-xl p-3 text-xs font-bold text-indigo-600 dark:text-indigo-400 transition-all cursor-pointer bg-transparent"
                                >
                                    <Plus class="w-4 h-4" />
                                    Tambah Prestasi
                                </button>
                            </div>
                        </div>

                        <!-- 9. LANGUAGES REPEATER ACCORDION -->
                        <div v-if="hasSection('languages')" class="bg-white dark:bg-zinc-900">
                            <button 
                                @click="toggleAccordion('languages')"
                                class="w-full flex items-center justify-between p-4 font-bold text-xs hover:bg-slate-50/80 dark:hover:bg-zinc-850/30 text-left border-none bg-transparent cursor-pointer"
                            >
                                <span class="flex items-center gap-2 text-slate-800 dark:text-zinc-150 font-black">
                                    <Languages class="w-4.5 h-4.5 text-indigo-500 shrink-0" />
                                    9. Bahasa
                                </span>
                                <ChevronDown class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': activeAccordion === 'languages' }" />
                            </button>
                            
                            <div v-show="activeAccordion === 'languages'" class="p-4 pt-0 space-y-3 border-t border-slate-50 dark:border-zinc-850/50">
                                <div v-for="(lang, index) in cvData.languages" :key="lang.id" class="grid grid-cols-1 xs:grid-cols-[1fr_auto_auto] items-center gap-3 bg-slate-50/40 dark:bg-zinc-900/40 p-2.5 rounded-xl border border-slate-100 dark:border-zinc-850">
                                    <AutocompleteInput class="w-full" v-model="lang.name" :suggestions="SUGGESTIONS.language" placeholder="Nama Bahasa (cth. Inggris)" />
                                    
                                    <select v-model="lang.proficiency" class="w-full xs:w-44 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none">
                                        <option value="Native or bilingual proficiency">Kemampuan Penutur Asli</option>
                                        <option value="Full professional proficiency">Kemampuan Profesional Penuh</option>
                                        <option value="Professional working proficiency">Kemampuan Kerja Profesional</option>
                                        <option value="Limited working proficiency">Kemampuan Kerja Terbatas</option>
                                        <option value="Elementary proficiency">Kemampuan Dasar</option>
                                    </select>

                                    <button 
                                        @click="removeLanguage(index)"
                                        class="text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 p-1.5 rounded-lg border-none bg-transparent cursor-pointer justify-self-end xs:justify-self-auto"
                                    >
                                        <Trash2 class="w-3.5 h-3.5" />
                                    </button>
                                </div>

                                <button 
                                    @click="addLanguage"
                                    class="w-full flex items-center justify-center gap-1.5 border border-dashed border-slate-300 dark:border-zinc-850 hover:bg-slate-50 dark:hover:bg-zinc-900 rounded-xl p-2.5 text-xs font-bold text-indigo-600 dark:text-indigo-400 transition-all cursor-pointer bg-transparent"
                                >
                                    <Plus class="w-3.5 h-3.5" />
                                    Tambah Bahasa
                                </button>
                            </div>
                        </div>

                        <!-- 10. REFERENCES REPEATER ACCORDION -->
                        <div v-if="hasSection('references')" class="bg-white dark:bg-zinc-900">
                            <button 
                                @click="toggleAccordion('references')"
                                class="w-full flex items-center justify-between p-4 font-bold text-xs hover:bg-slate-50/80 dark:hover:bg-zinc-850/30 text-left border-none bg-transparent cursor-pointer"
                            >
                                <span class="flex items-center gap-2 text-slate-800 dark:text-zinc-150 font-black">
                                    <Users class="w-4.5 h-4.5 text-indigo-500 shrink-0" />
                                    10. Referensi Kerja
                                </span>
                                <ChevronDown class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': activeAccordion === 'references' }" />
                            </button>
                            
                            <div v-show="activeAccordion === 'references'" class="p-4 pt-0 space-y-4 border-t border-slate-50 dark:border-zinc-850/50">
                                <div v-for="(refItem, index) in cvData.references" :key="refItem.id" class="border border-slate-200/60 dark:border-zinc-800 p-4 rounded-xl relative space-y-3 bg-slate-50/10 dark:bg-zinc-900/10">
                                    <button 
                                        @click="removeReference(index)"
                                        class="absolute top-3 right-3 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 p-1.5 rounded-lg border-none bg-transparent cursor-pointer"
                                    >
                                        <Trash2 class="w-3.5 h-3.5" />
                                    </button>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-500">Nama Pemberi Referensi</label>
                                            <input type="text" v-model="refItem.name" placeholder="cth. Dr. Maruf Muchlisin" class="w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none" />
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-500">Jabatan / Posisi</label>
                                            <input type="text" v-model="refItem.position" placeholder="cth. Dosen Pembimbing" class="w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-500">Nama Perusahaan / Kampus</label>
                                            <input type="text" v-model="refItem.company" placeholder="cth. Universitas FMIKOM" class="w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none" />
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-500">Info Kontak (Email/HP)</label>
                                            <input type="text" v-model="refItem.contact" placeholder="cth. maruf@fmikom.ac.id" class="w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none" />
                                        </div>
                                    </div>
                                </div>

                                <button 
                                    @click="addReference"
                                    class="w-full flex items-center justify-center gap-1.5 border border-dashed border-slate-300 dark:border-zinc-850 hover:bg-slate-50 dark:hover:bg-zinc-900 rounded-xl p-3 text-xs font-bold text-indigo-600 dark:text-indigo-400 transition-all cursor-pointer bg-transparent"
                                >
                                    <Plus class="w-4 h-4" />
                                    Tambah Referensi Kerja
                                </button>
                            </div>
                        </div>

                    </div>

                    <!-- TAB 2: THEME CUSTOMIZER DESIGN PANEL -->
                    <div v-show="activeTab === 'customizer'">
                        <ThemeCustomizer 
                            v-model:customization="cvData.customization"
                            :templateId="cvData.template_id"
                        />
                    </div>

                </div>
            </div>

            <!-- RIGHT AREA: STICKY A4 WORKSPACE CANVAS PREVIEW -->
            <div 
                ref="previewContainer"
                class="flex-1 bg-slate-200 dark:bg-zinc-900/20 overflow-x-hidden overflow-y-auto flex flex-col items-center justify-start p-4 sm:p-8 relative min-h-0 select-none"
                :class="previewModeMobile ? 'flex' : 'hidden md:flex'"
            >
                <!-- Canvas Floating Toolbar Controls (Zoom In/Out) -->
                <div class="sticky top-0 bg-white/95 dark:bg-zinc-900/95 backdrop-blur-xs px-4 py-2 rounded-xl shadow-md border border-slate-200/50 dark:border-zinc-800 flex items-center gap-3 z-30 mb-8 select-none">
                    <button 
                        @click="zoom = Math.max(0.4, zoom - 0.05)"
                        class="p-1 rounded bg-slate-50 hover:bg-slate-100 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-600 dark:text-zinc-300 border-none cursor-pointer"
                        title="Perkecil"
                    >
                        <ZoomOut class="w-4 h-4" />
                    </button>
                    
                    <span class="text-xs font-bold w-12 text-center text-slate-700 dark:text-zinc-300">
                        {{ Math.round(zoom * 100) }}%
                    </span>
                    
                    <button 
                        @click="zoom = Math.min(1.2, zoom + 0.05)"
                        class="p-1 rounded bg-slate-50 hover:bg-slate-100 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-600 dark:text-zinc-300 border-none cursor-pointer"
                        title="Perbesar"
                    >
                        <ZoomIn class="w-4 h-4" />
                    </button>
                </div>

                <!-- A4 Shadow Wrapped Sheet Container -->
                <div class="flex-1 flex justify-center w-full min-h-[300mm]">
                    <CvPreview 
                        :cv="cvData"
                        :zoom="zoom"
                    />
                </div>
            </div>

        </div>

        <!-- Ultra-Premium Toast Notification System -->
        <Transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showToast" class="fixed bottom-6 right-6 z-[9999] max-w-sm bg-slate-900/95 dark:bg-zinc-900/95 text-white backdrop-blur-md border border-slate-800 dark:border-zinc-800 py-2.5 px-4 rounded-xl shadow-2xl flex items-center gap-3">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0" :class="
                    toastType === 'success' ? 'bg-emerald-500/10 text-emerald-400' :
                    toastType === 'error' ? 'bg-rose-500/10 text-rose-400' :
                    'bg-indigo-500/10 text-indigo-400'
                ">
                    <Check v-if="toastType === 'success'" class="w-4 h-4" />
                    <AlertCircle v-else-if="toastType === 'error'" class="w-4 h-4" />
                    <Loader2 v-else class="w-4 h-4 animate-spin" />
                </div>
                <p class="text-[13px] font-semibold text-slate-100 pr-1 select-none">{{ toastMessage }}</p>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
/* Range Slider Custom Styling */
input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #4f46e5;
    cursor: pointer;
}
input[type="range"]::-moz-range-thumb {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #4f46e5;
    cursor: pointer;
    border: none;
}
</style>
