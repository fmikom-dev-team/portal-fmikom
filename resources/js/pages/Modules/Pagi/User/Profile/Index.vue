<script setup lang="ts">
import { Head, Link, usePage, router, useForm } from "@inertiajs/vue3";
import {
	BadgeCheck,
	ChevronDown,
	ChevronUp,
	ChevronRight,
	Eye,
	Globe,
	Heart,
	Info,
	MessageSquare,
	Plus,
	Share,
	X,
	MapPin,
	BarChart3,
	Sparkles,
	ExternalLink,
	Link2,
	Linkedin,
	Github,
	Twitter,
	Instagram,
	CheckCircle2,
	Circle,
	UploadCloud,
	Settings,
	Bell,
	Image as ImageIcon,
	Camera,
	Pencil,
	Briefcase,
	Loader2,
	ZoomIn,
	ZoomOut,
	RotateCcw,
	RotateCw,
	Paperclip,
} from "lucide-vue-next";
import { computed, ref, onMounted, onUnmounted, nextTick, watch, defineAsyncComponent } from "vue";
import QRCode from "qrcode";
import Navbar from "../ui/Navbar.vue";
import Footer from "../ui/Footer.vue";
import Modal from "../ui/Modal.vue";
import VideoLazy from "../ui/VideoLazy.vue";
import OptimizedImage from "../ui/OptimizedImage.vue";
import Progress from "../ui/Progress.vue";
import ShareWorkModal from "../ui/ShareWorkModal.vue";


const WorkTab = defineAsyncComponent(() => import("./WorkTab.vue"));
const GalleryTab = defineAsyncComponent(() => import("./GalleryTab.vue"));
const SertifikatTab = defineAsyncComponent(() => import("./SertifikatTab.vue"));
const AboutTab = defineAsyncComponent(() => import("./AboutTab.vue"));
import Preview from "../ui/Preview.vue";
import EditBioModal from "./components/EditBioModal.vue";
import EditDetailsModal from "./components/EditDetailsModal.vue";
import EditLocationModal from "./components/EditLocationModal.vue";
import EditSocialsModal from "./components/EditSocialsModal.vue";
import EditUsernameModal from "./components/EditUsernameModal.vue";
import CropImageModal from "./components/CropImageModal.vue";
import AddWorkModal from "./components/AddWorkModal.vue";
import ProfileHeader from "./components/ProfileHeader.vue";
import ProfileTabs from "./components/ProfileTabs.vue";


const props = defineProps<{
	moduleName: string;
	roleName: string;
	profileUser?: any;
	isFollowing?: boolean;
	projects?: Array<{
		id: number;
		title: string;
		image: string;
		likes: number;
		views: number;
		content: any;
		created_at: string;
		is_verified?: boolean;
		is_published?: boolean;
	}>;
}>();

const tabs = ["Work", "Gallery", "Certificates", "About"];
const page = usePage();
const user = computed(
	() => props.profileUser || page.props.auth?.user || { name: "User", email: "", role_title: "", bio: "", location: "", foto_path: "", banner_path: "", website: "", linkedin: "", github: "", twitter: "", instagram: "", tanggal_lahir: "" },
);

const isOwnProfile = computed(() => {
	if (!page.props.auth?.user) return false;
	return page.props.auth.user.id === user.value.id;
});

const displayRoleName = computed(() => {
	const role = props.roleName || (page.props as any).context?.active_role || (page.props.roleName as string) || 'Mahasiswa';
	const r = role.toLowerCase();
	if (r === 'mahasiswa') return 'Mahasiswa';
	if (r === 'super-admin' || r === 'super_admin') return 'Super Admin';
	if (r === 'dosen') return 'Dosen';
	if (r === 'alumni') return 'Alumni';
	if (r === 'mitra') return 'Mitra Perusahaan';
	if (r === 'guest') return 'Tamu';
	return role.charAt(0).toUpperCase() + role.slice(1);
});

// Reactive states backed by localStorage for full interactivity
const isLoading = ref(true);
const isFollowing = ref(false);
const isMessageEnabled = ref(true);
const activeWorkFilter = ref("Created"); // "Created" or "Collaborated"
const getInitialTab = () => {
	if (typeof window === 'undefined') return 'Work';
	const path = window.location.pathname;
	const segments = path.split('/').filter(Boolean);
	if (segments.length >= 3 && segments[0].toLowerCase() === 'pagi') {
		const tabSegment = segments[2].toLowerCase();
		if (tabSegment === 'gallery') return 'Gallery';
		if (tabSegment === 'certificates') return 'Certificates';
		if (tabSegment === 'sertifikat') return 'Certificates';
		if (tabSegment === 'about') return 'About';
		if (tabSegment === 'work') return 'Work';
	}
	const params = new URLSearchParams(window.location.search);
	const queryTab = params.get('tab');
	if (queryTab) {
		const qLower = queryTab.toLowerCase();
		if (qLower === 'sertifikat' || qLower === 'certificates') return 'Certificates';
		if (qLower === 'work') return 'Work';
		if (qLower === 'gallery') return 'Gallery';
		if (qLower === 'about') return 'About';
	}
	return 'Work';
};
const activeTab = ref(getInitialTab());

watch(activeTab, (newTab) => {
	if (typeof window !== 'undefined') {
		const path = window.location.pathname;
		const segments = path.split('/').filter(Boolean);
		if (segments.length >= 2 && segments[0].toLowerCase() === 'pagi') {
			const prefix = segments[0];
			const username = segments[1];
			const tabLower = newTab.toLowerCase();
			let newPathname = '';
			if (tabLower === 'work') {
				newPathname = `/${prefix}/${username}`;
			} else {
				newPathname = `/${prefix}/${username}/${tabLower}`;
			}
			const url = new URL(window.location.href);
			url.pathname = newPathname;
			url.searchParams.delete('tab');
			window.history.replaceState(null, '', url.toString());
		} else {
			const url = new URL(window.location.href);
			url.searchParams.set('tab', newTab);
			window.history.replaceState(null, '', url.toString());
		}
	}
});

const localProjects = ref<any[]>([...(props.projects || [])]);
watch(() => props.projects, (newVal) => {
	localProjects.value = [...(newVal || [])];
}, { deep: true });

const projects = computed(() => {
	return localProjects.value;
});

const handleLikeUpdated = (data: { id: number; liked: boolean; count: number }) => {
	const proj = localProjects.value?.find((p: any) => p.id === data.id);
	if (proj) {
		proj.liked = data.liked;
		proj.likes = data.count;
		// trigger deep array reactivity
		localProjects.value = [...localProjects.value];
	}
};

const handleGalleryItemUpdated = (updatedProject: any) => {
	const idx = localProjects.value.findIndex(p => p.id === updatedProject.id);
	if (idx !== -1) {
		const existing = localProjects.value[idx];
		localProjects.value[idx] = {
			...existing,
			title: updatedProject.title,
			content: updatedProject.content,
		};
		localProjects.value = [...localProjects.value];
	}
};

// Add Work Modal Reactive States
const showAddWorkModal = ref(false);
const showShareModal = ref(false);
const newCreatedProject = ref<any>(null);
const isSubmittingWork = ref(false);
const showMoreDetails = ref(false);
const coverFit = ref<'cover' | 'contain'>('cover');

const getProjectFit = (project: any) => {
	if (!project || !project.content || !Array.isArray(project.content)) return 'cover';
	const settings = project.content.find((b: any) => b && b.type === 'settings');
	if (settings && settings.coverFit) return settings.coverFit;
	const details = project.content.find((b: any) => b && b.type === 'featured_details');
	if (details && details.cover_fit) return details.cover_fit;
	return 'cover';
};

const isEditingQuickWork = ref(false);
const editingQuickWorkId = ref<number | null>(null);

const showLocationOnlyModal = ref(false);
const searchQuery = ref("");
const showDropdown = ref(false);
const cities = [
	"Banyumas, Jawa Tengah",
	"Purwokerto, Jawa Tengah",
	"Cilacap, Jawa Tengah",
	"Purbalingga, Jawa Tengah",
	"Banjarnegara, Jawa Tengah",
	"Semarang, Jawa Tengah",
	"Surakarta (Solo), Jawa Tengah",
	"Yogyakarta, DIY",
	"Sleman, DIY",
	"Bantul, DIY",
	"Jakarta Pusat, DKI Jakarta",
	"Jakarta Selatan, DKI Jakarta",
	"Jakarta Barat, DKI Jakarta",
	"Jakarta Timur, DKI Jakarta",
	"Jakarta Utara, DKI Jakarta",
	"Bandung, Jawa Barat",
	"Bogor, Jawa Barat",
	"Depok, Jawa Barat",
	"Tangerang, Banten",
	"Tangerang Selatan, Banten",
	"Bekasi, Jawa Barat",
	"Surabaya, Jawa Timur",
	"Malang, Jawa Timur",
	"Sidoarjo, Jawa Timur",
	"Gresik, Jawa Timur",
	"Medan, Sumatera Utara",
	"Palembang, Sumatera Selatan",
	"Pekanbaru, Riau",
	"Padang, Sumatera Barat",
	"Bandar Lampung, Lampung",
	"Banda Aceh, Aceh",
	"Jambi, Jambi",
	"Bengkulu, Bengkulu",
	"Pontianak, Kalimantan Barat",
	"Banjarmasin, Kalimantan Selatan",
	"Balikpapan, Kalimantan Timur",
	"Samarinda, Kalimantan Timur",
	"Makassar, Sulawesi Selatan",
	"Manado, Sulawesi Utara",
	"Denpasar, Bali",
	"Mataram, Nusa Tenggara Barat",
	"Kupang, Nusa Tenggara Timur",
	"Ambon, Maluku",
	"Jayapura, Papua"
];

const filteredCities = computed(() => {
	if (!searchQuery.value) return cities;
	const query = searchQuery.value.toLowerCase();
	return cities.filter(city => city.toLowerCase().includes(query));
});

const openLocationOnlyModal = () => {
	initFormValues();
	searchQuery.value = user.value.location || "";
	showLocationOnlyModal.value = true;
};

const selectCity = (city: string) => {
	form.location = city;
	searchQuery.value = city;
	showDropdown.value = false;
};

const submitLocationOnly = () => {
	if (searchQuery.value && !form.location) {
		form.location = searchQuery.value;
	}
	form.post("/pagi/profile/update", {
		preserveScroll: true,
		onSuccess: () => {
			initFormValues();
			showLocationOnlyModal.value = false;
			addToast("Location updated successfully!", "success");
		},
	});
};

const certificates = ref<any[]>([...(props.profileUser?.certificates || [])]);
watch(() => props.profileUser?.certificates, (newVal) => {
	certificates.value = [...(newVal || [])];
}, { deep: true });

// Warning modal states
const showWarningModal = ref(false);
const warningTitle = ref("");
const warningMessage = ref("");

const triggerWarning = (title: string, message: string) => {
	warningTitle.value = title;
	warningMessage.value = message;
	showWarningModal.value = true;
};

// Form state using Inertia useForm
const form = useForm({
	name: "",
	role_title: "",
	bio: "",
	location: "",
	website: "",
	twitter: "",
	linkedin: "",
	github: "",
	instagram: "",
	tanggal_lahir: "",
	banner: null as File | null,
	foto: null as File | null,
	avatar_url: "",
	remove_foto: false,
	skills: [] as string[],
	timezone: "" as string | null,
	timezone_extended: "" as string | null,
	languages: [] as Array<{ language: string, proficiency: string }>,
	pagi_username: "" as string,
});

// File inputs and cropping references
const bannerInput = ref<HTMLInputElement | null>(null);
const fotoInput = ref<HTMLInputElement | null>(null);

const isCropperOpen = ref(false);
const cropperImageSrc = ref("");
const cropperAspectRatio = ref<number | string>(3200 / 410);
let originalFileName = "banner.jpg";
const originalFileType = ref("image/jpeg");

// FFmpeg WASM state for client-side video conversion
const isConvertingVideo = ref(false);
const videoConvertProgress = ref(0);

const getUploadStatusMessage = (progress: number) => {
	if (progress < 5) return "Memulai pengunggahan...";
	if (progress < 25) return "Mengompresi dan mengoptimalkan berkas...";
	if (progress < 50) return "Menyiapkan paket pengunggahan...";
	if (progress < 75) return "Mengunggah berkas ke server...";
	if (progress < 95) return "Menyimpan perubahan...";
	return "Selesai!";
};


// Previews
const bannerPreview = ref<string | null>(null);
const photoPreview = ref<string | null>(null);
const selectedPresetIndex = ref<number | null>(null);

// Modal trigger handlers
const parseSkills = (skillsArray: any[]): Array<{ name: string, percentage: number }> => {
	if (!Array.isArray(skillsArray)) return [];
	return skillsArray.map(item => {
		if (typeof item === 'string') {
			const parts = item.split(':');
			if (parts.length === 2 && !isNaN(Number(parts[1]))) {
				return { name: parts[0], percentage: Number(parts[1]) };
			}
			return { name: item, percentage: 80 };
		}
		if (item && typeof item === 'object' && item.name) {
			return { name: item.name, percentage: Number(item.percentage) || 80 };
		}
		return { name: String(item), percentage: 80 };
	});
};

// Form initialization
const initFormValues = () => {
	form.name = user.value.name || "";
	form.role_title = user.value.role_title || "";
	form.bio = user.value.bio || "";
	form.location = user.value.location || "";
	form.website = user.value.website || "";
	form.twitter = user.value.twitter || "";
	form.linkedin = user.value.linkedin || "";
	form.github = user.value.github || "";
	form.instagram = user.value.instagram || "";
	form.tanggal_lahir = user.value.tanggal_lahir || "";
	form.pagi_username = user.value.pagi_username || "";
	const s = user.value.skills || user.value.metadata?.skills || props.profileUser?.skills;
	form.skills = Array.isArray(s) ? parseSkills(s) : parseSkills(['Figma', 'UI/UX Design', 'Vue.js']);
	form.timezone = user.value.timezone || user.value.metadata?.timezone || props.profileUser?.timezone || "";
	form.timezone_extended = user.value.timezone_extended || user.value.timezoneExtended || user.value.metadata?.timezone_extended || user.value.metadata?.timezoneExtended || props.profileUser?.timezone_extended || props.profileUser?.timezoneExtended || "No extended hours";
	const l = user.value.languages || user.value.metadata?.languages || props.profileUser?.languages;
	form.languages = Array.isArray(l) ? l : [];
};

const formatBytes = (bytes: number, decimals = 2) => {
	if (bytes === 0) return "0 Bytes";
	const k = 1024;
	const dm = decimals < 0 ? 0 : decimals;
	const sizes = ["Bytes", "KB", "MB", "GB"];
	const i = Math.floor(Math.log(bytes) / Math.log(k));
	return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + " " + sizes[i];
};

// Direct-edit modal states
const showLocationModal = ref(false);
const showSocialLinksModal = ref(false);
const showBioModal = ref(false);
const showAvatarModal = ref(false);
const showBannerModal = ref(false);
const showUsernameModal = ref(false);

const usernameChangesCount = computed(() => user.value.metadata?.username_changes_count || 0);
const lastUsernameChangedAt = computed(() => user.value.metadata?.last_username_changed_at || null);

const canChangeUsername = computed(() => {
	// First time creation is always allowed if current username is null/empty
	if (!user.value.pagi_username) return { allowed: true };

	// If changes limit reached
	if (usernameChangesCount.value >= 3) {
		return { allowed: false, reason: "Batas perubahan username Anda telah habis (Maksimal 3 kali)." };
	}

	// If duration constraint is active
	if (lastUsernameChangedAt.value) {
		const lastDate = new Date(lastUsernameChangedAt.value);
		const diffTime = Math.abs(new Date().getTime() - lastDate.getTime());
		const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
		if (diffDays <= 30) {
			const daysLeft = 30 - Math.floor(diffTime / (1000 * 60 * 60 * 24));
			return { allowed: false, reason: `Anda baru saja mengubah username. Silakan tunggu ${daysLeft} hari lagi untuk mengubahnya kembali.` };
		}
	}

	return { allowed: true };
});

const usernameEditBlock = computed(() => {
	const currentUsernameClean = (user.value.pagi_username || '').toLowerCase().trim();
	const inputUsernameClean = (form.pagi_username || '').toLowerCase().trim();
	if (inputUsernameClean === currentUsernameClean) {
		return null;
	}
	const status = canChangeUsername.value;
	if (!status.allowed) {
		return status.reason;
	}
	return null;
});

const formatDateString = (dateStr) => {
	if (!dateStr) return '';
	const date = new Date(dateStr);
	return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
};

const activeBannerTab = ref<'Upload' | 'Presets'>('Upload');
const isDragging = ref(false);

// Modal trigger handlers
const openLocationModal = () => {
	initFormValues();
	showLocationModal.value = true;
};

const openSocialLinksModal = () => {
	initFormValues();
	showSocialLinksModal.value = true;
};

const openBioModal = () => {
	initFormValues();
	showBioModal.value = true;
};

const openUsernameModal = () => {
	initFormValues();
	showUsernameModal.value = true;
};

const submitUsername = () => {
	form.post("/pagi/profile/update", {
		preserveScroll: true,
		onSuccess: () => {
			initFormValues();
			showUsernameModal.value = false;
			addToast("Username berhasil disimpan!", "success");
		},
	});
};

const openAvatarModal = () => {
	photoPreview.value = user.value.foto_path 
		? (user.value.foto_path.startsWith('http') ? user.value.foto_path : '/storage/' + user.value.foto_path)
		: null;
	form.foto = null;
	form.remove_foto = false;
	showAvatarModal.value = true;
};

const isVideoUrl = (url: string | null): boolean => {
	if (!url) return false;
	const cleanUrl = url.split('?')[0].split('#')[0];
	const ext = cleanUrl.split('.').pop()?.toLowerCase();
	return ['mp4', 'webm', 'mov', 'avi', 'mkv', '3gp'].includes(ext || '') || url.startsWith('data:video/');
};

const openBannerModal = () => {
	const savedPath = user.value.banner_path;
	bannerPreview.value = savedPath ? '/storage/' + savedPath : null;
	// Detect if the saved banner is a video and restore file type accordingly
	if (savedPath && isVideoUrl(savedPath)) {
		originalFileType.value = 'video/mp4';
	} else {
		originalFileType.value = 'image/jpeg';
	}
	form.banner = null;
	selectedPresetIndex.value = null;
	activeBannerTab.value = 'Upload';
	showBannerModal.value = true;
};

// Form submission methods
const submitLocation = () => {
	form.post("/pagi/profile/update", {
		preserveScroll: true,
		onSuccess: () => {
			initFormValues();
			showLocationModal.value = false;
			addToast("Location updated successfully!", "success");
		},
	});
};

const submitSocialLinks = () => {
	form.post("/pagi/profile/update", {
		preserveScroll: true,
		onSuccess: () => {
			initFormValues();
			showSocialLinksModal.value = false;
			addToast("Social links updated successfully!", "success");
		},
	});
};

const submitBio = () => {
	form.post("/pagi/profile/update", {
		preserveScroll: true,
		onSuccess: () => {
			initFormValues();
			showBioModal.value = false;
			addToast("Biography updated successfully!", "success");
		},
	});
};

const submitAvatar = () => {
	form.post("/pagi/profile/update", {
		preserveScroll: true,
		forceFormData: true,
		onSuccess: () => {
			initFormValues();
			showAvatarModal.value = false;
			form.foto = null;
			form.remove_foto = false;
			addToast("Profile avatar updated successfully!", "success");
		},
	});
};

const submitBanner = () => {
	form.post("/pagi/profile/update", {
		preserveScroll: true,
		forceFormData: true,
		onSuccess: () => {
			initFormValues();
			showBannerModal.value = false;
			form.banner = null;
			addToast("Featured banner updated successfully!", "success");
		},
	});
};

// Toast Notification System
const toasts = ref<Array<{ id: number; message: string; type: string }>>([]);
const addToast = (message: string, type = "success") => {
	const id = Date.now();
	toasts.value.push({ id, message, type });
	setTimeout(() => {
		toasts.value = toasts.value.filter((t) => t.id !== id);
	}, 3000);
};

const handleOutsideClick = (e: MouseEvent) => {
	const target = e.target as HTMLElement;
	if (target && !target.closest("#location_search_container")) {
		showDropdown.value = false;
	}
};

onMounted(() => {
	initFormValues();
	// Load real follow state from server prop first, fallback to localStorage for own profile
	if (props.isFollowing !== undefined) {
		isFollowing.value = props.isFollowing;
	} else {
		isFollowing.value = localStorage.getItem(`follow_${user.value.id}`) === "true";
	}
	isMessageEnabled.value = user.value.metadata?.is_message_enabled !== false;
	document.addEventListener("click", handleOutsideClick);

	// Check if a specific project was linked to open directly (Instagram style)
	const urlParams = new URLSearchParams(window.location.search);
	const projectId = urlParams.get('project') || urlParams.get('portfolio');
	if (projectId) {
		const proj = props.projects?.find((p: any) => p.id == projectId);
		if (proj) {
			openProjectModal(proj);
		}
	}

	const editParam = urlParams.get('edit');
	if (editParam === 'username') {
		openUsernameModal();
	} else if (editParam === 'avatar') {
		openAvatarModal();
	} else if (editParam === 'bio') {
		openBioModal();
	} else if (editParam === 'location') {
		openLocationModal();
	} else if (editParam === 'socials') {
		openSocialLinksModal();
	} else if (editParam === 'project') {
		openAddWorkModal();
	}

	setTimeout(() => {
		isLoading.value = false;
	}, 600);
});

onUnmounted(() => {
	document.removeEventListener("click", handleOutsideClick);
});

// Follow toggle — calls real API, updates metadata + sends notification
const isFollowLoading = ref(false);
const toggleFollow = async () => {
	if (!page.props.auth?.user) {
		addToast("Silakan login terlebih dahulu untuk mengikuti creator.", "info");
		return;
	}
	if (isOwnProfile.value) return;
	if (isFollowLoading.value) return;

	isFollowLoading.value = true;
	const prevState = isFollowing.value;
	isFollowing.value = !isFollowing.value; // Optimistic

	try {
		const csrfToken = (document.querySelector('meta[name=csrf-token]') as HTMLMetaElement)?.content;
		const res = await fetch(`/pagi/users/${user.value.id}/follow`, {
			method: 'POST',
			headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
		});
		const data = await res.json();
		if (!res.ok) throw new Error(data.error || 'Failed');
		isFollowing.value = data.following;
		realFollowersCount.value = data.followers_count;
		localStorage.setItem(`follow_${user.value.id}`, String(data.following));
		if (data.following) {
			addToast(`Kamu sekarang mengikuti ${user.value.name}!`, "success");
		} else {
			addToast(`Kamu berhenti mengikuti ${user.value.name}.`, "info");
		}
	} catch (e) {
		isFollowing.value = prevState; // Revert on error
		addToast("Gagal memperbarui status follow. Coba lagi.", "error");
	} finally {
		isFollowLoading.value = false;
	}
};

// Message toggle switch handler
const toggleMessageSwitch = (e: Event) => {
	e.stopPropagation(); // Prevent message button click redirection
	isMessageEnabled.value = !isMessageEnabled.value;
	router.post("/pagi/profile/update", {
		is_message_enabled: isMessageEnabled.value,
	}, {
		preserveScroll: true,
		onSuccess: () => {
			if (isMessageEnabled.value) {
				addToast("Direct messaging has been enabled.", "success");
			} else {
				addToast("Direct messaging has been disabled.", "info");
			}
		}
	});
};

// Navigate to chat
const openChat = () => {
	if (!page.props.auth?.user) {
		addToast("Silakan login terlebih dahulu untuk mengirim pesan.", "info");
		return;
	}
	if (isOwnProfile.value) {
		router.visit("/pagi/messages");
	} else {
		router.visit(`/pagi/messages?chat=${user.value.id}`);
	}
};

const showProfileShareModal = ref(false);
const activeShareUrl = ref("");
const qrCanvas = ref<HTMLCanvasElement | null>(null);

const renderQr = async (url: string) => {
	await nextTick();
	if (!qrCanvas.value) return;
	try {
		await QRCode.toCanvas(qrCanvas.value, url, {
			width: 200,
			margin: 2,
			color: { dark: '#000000', light: '#ffffff' },
		});
	} catch (e) {
		console.error('QR generate error', e);
	}
};

const generateShareToken = () => {
	const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	let result = "";
	for (let i = 0; i < 12; i++) {
		result += chars.charAt(Math.floor(Math.random() * chars.length));
	}
	return btoa(result).replace(/=/g, "").substring(0, 14);
};

// Share profile URL
const shareProfile = async () => {
	const username = user.value.pagi_username;
	const baseUrl = username 
		? `${window.location.origin}/pagi/${username}`
		: `${window.location.origin}/pagi/profile/${user.value.id}`;
	const token = generateShareToken();
	activeShareUrl.value = `${baseUrl}?pagi_share=${token}`;
	showProfileShareModal.value = true;
	// Render QR after modal is mounted
	await nextTick();
	await renderQr(activeShareUrl.value);
};

const copyProfileLink = () => {
	navigator.clipboard.writeText(activeShareUrl.value).then(() => {
		addToast("Link profil disalin ke papan klip!", "success");
	}).catch(() => {
		addToast("Gagal menyalin tautan.", "error");
	});
};

const downloadQrCode = () => {
	if (qrCanvas.value) {
		const link = document.createElement("a");
		link.href = qrCanvas.value.toDataURL('image/png');
		link.download = `${user.value.pagi_username || 'pagi_user'}_qr.png`;
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
		addToast("QR Code berhasil diunduh!", "success");
	}
};

const activeProjectMenu = ref<number | null>(null);
const toggleProjectMenu = (id: number) => {
	activeProjectMenu.value = activeProjectMenu.value === id ? null : id;
};

// Project dropdown actions
const cloneProject = (title: string) => {
	addToast(`Cloned project: "${title}" successfully!`, "success");
	activeProjectMenu.value = null;
};

const shareProject = (project: any) => {
	newCreatedProject.value = project;
	showShareModal.value = true;
};

const getProjectShareUrl = (project: any) => {
	if (!project) return "";
	const username = props.user?.pagi_username;
	const ownerId = props.user?.id;
	const baseUrl = username
		? `${window.location.origin}/pagi/${username}`
		: `${window.location.origin}/pagi/profile/${ownerId}`;
	return `${baseUrl}?project=${project.id}`;
};

const deleteProject = async (id: number, title: string) => {
	activeProjectMenu.value = null;
	const prevProjects = [...localProjects.value];
	localProjects.value = localProjects.value.filter(p => p.id !== id);
	addToast(`Project "${title}" has been deleted.`, "success");

	try {
		const csrfToken = (document.querySelector('meta[name=csrf-token]') as HTMLMetaElement)?.content;
		const res = await fetch(`/pagi/editor/${id}`, {
			method: 'DELETE',
			headers: { 
				'X-CSRF-TOKEN': csrfToken || '', 
				'Accept': 'application/json' 
			},
		});
		const data = await res.json();
		if (!res.ok) throw new Error(data.error || 'Failed');
	} catch (e) {
		localProjects.value = prevProjects;
		addToast("Gagal menghapus project. Coba lagi.", "error");
	}
};

// Dynamic Justified Grid Aspect Ratio Preloading & Canvas Resize Observers
const aspectRatios = ref<Record<string, number>>({});
const canvasContainer = ref<HTMLElement | null>(null);
const containerWidth = ref(1200);

const updateContainerWidth = () => {
	if (canvasContainer.value) {
		containerWidth.value = canvasContainer.value.clientWidth;
	}
};

let resizeObserver: ResizeObserver | null = null;

watch(canvasContainer, (newVal) => {
	if (newVal) {
		nextTick(() => {
			updateContainerWidth();
			if (typeof ResizeObserver !== "undefined") {
				if (resizeObserver) resizeObserver.disconnect();
				resizeObserver = new ResizeObserver(() => {
					updateContainerWidth();
				});
				resizeObserver.observe(newVal);
			}
		});
	} else {
		if (resizeObserver) {
			resizeObserver.disconnect();
			resizeObserver = null;
		}
	}
});

const normalizeSrc = (src: string) => {
	if (src.startsWith('http') || src.startsWith('blob:')) return src;
	return '/storage/' + src;
};

const handleImageLoad = (src: string, event: Event) => {
	const img = event.target as HTMLImageElement;
	if (img.naturalWidth && img.naturalHeight) {
		aspectRatios.value = {
			...aspectRatios.value,
			[normalizeSrc(src)]: img.naturalWidth / img.naturalHeight
		};
	}
};

const getAspectRatio = (src: string) => {
	return aspectRatios.value[normalizeSrc(src)] || 1.5; // Default landscape fallback
};

const loadImageAspectRatios = (filePaths: string[]) => {
	filePaths.forEach((src) => {
		const normalized = normalizeSrc(src);
		if (aspectRatios.value[normalized]) return;
		const img = new Image();
		img.onload = () => {
			if (img.naturalWidth && img.naturalHeight) {
				aspectRatios.value = {
					...aspectRatios.value,
					[normalized]: img.naturalWidth / img.naturalHeight
				};
			}
		};
		img.src = normalized;
	});
};

const isVideoBlock = (block: any) => {
	if (!block) return false;
	if (block.mimeType) return block.mimeType.startsWith('video');
	if (block.file && block.file.type) return block.file.type.startsWith('video');
	if (block.name) {
		const ext = block.name.split('.').pop()?.toLowerCase();
		return ['mp4', 'webm', 'ogg', 'mov', 'm4v', '3gp'].includes(ext || '');
	}
	if (block.file_path) {
		const ext = block.file_path.split('.').pop()?.split('?')[0].toLowerCase();
		return ['mp4', 'webm', 'ogg', 'mov', 'm4v', '3gp'].includes(ext || '');
	}
	return false;
};

const isAudioBlock = (block: any) => {
	if (!block) return false;
	if (block.mimeType) return block.mimeType.startsWith('audio');
	if (block.file && block.file.type) return block.file.type.startsWith('audio');
	if (block.name) {
		const ext = block.name.split('.').pop()?.toLowerCase();
		return ['mp3', 'wav', 'ogg', 'aac', 'flac', 'm4a'].includes(ext || '');
	}
	if (block.file_path) {
		const ext = block.file_path.split('.').pop()?.split('?')[0].toLowerCase();
		return ['mp3', 'wav', 'ogg', 'aac', 'flac', 'm4a'].includes(ext || '');
	}
	return false;
};

// flickr-justified-layout algorithm implementation
const getJustifiedLayout = (filePaths: string[], containerWidth: number, targetHeight: number, gap: number) => {
	const items = filePaths || [];
	if (items.length === 0) return [];
	
	const rows = [];
	let i = 0;
	const n = items.length;
	
	while (i < n) {
		let currentRow = [items[i]];
		let currentSum = getAspectRatio(items[i]);
		let j = i + 1;
		
		while (j < n) {
			const nextItem = items[j];
			const nextAr = getAspectRatio(nextItem);
			
			const totalGapsWidth = currentRow.length * gap;
			const availableWidth = containerWidth - totalGapsWidth;
			const newHeight = availableWidth / (currentSum + nextAr);
			
			const prevGapsWidth = (currentRow.length - 1) * gap;
			const prevHeight = (containerWidth - prevGapsWidth) / currentSum;
			
			if (prevHeight > targetHeight) {
				currentRow.push(nextItem);
				currentSum += nextAr;
				j++;
			} else {
				if (Math.abs(newHeight - targetHeight) < Math.abs(prevHeight - targetHeight)) {
					currentRow.push(nextItem);
					currentSum += nextAr;
					j++;
				} else {
					break;
				}
			}
		}
		
		const isLastRow = (j === n);
		const totalGapsWidth = (currentRow.length - 1) * gap;
		const availableWidth = containerWidth - totalGapsWidth;
		const calculatedHeight = availableWidth / currentSum;
		
		if (isLastRow) {
			const canStretch = currentRow.length >= 2 && calculatedHeight <= 900;
			const finalHeight = canStretch ? calculatedHeight : Math.min(calculatedHeight, targetHeight);
			rows.push({
				items: [...currentRow],
				height: finalHeight,
				isLast: !canStretch
			});
		} else {
			rows.push({
				items: [...currentRow],
				height: calculatedHeight,
				isLast: false
			});
		}
		
		i = j;
	}
	
	return rows;
};

// Compute dynamic container width based on element clientWidth
const getContainerWidth = (isFullWidth: boolean) => {
	if (isFullWidth) return containerWidth.value;
	return Math.min(containerWidth.value, 896) - 48;
};

const viewingProject = ref<any>(null);

watch(() => viewingProject.value?.content, (newContent) => {
	if (!newContent) return;
	newContent.forEach((block: any) => {
		if (block.type === "photo_grid" && block.file_paths) {
			loadImageAspectRatios(block.file_paths);
		}
	});
}, { deep: true, immediate: true });
const activeProjectSettings = computed(() => {
	if (!viewingProject.value || !viewingProject.value.content) {
		return { globalSpacing: 50, canvasBgColor: '', canvasTextColor: '' };
	}
	return viewingProject.value.content.find((b: any) => b.type === 'settings') || { globalSpacing: 50, canvasBgColor: '', canvasTextColor: '' };
});

const spacingInPxForView = computed(() => {
	const spacing = activeProjectSettings.value.globalSpacing !== undefined ? activeProjectSettings.value.globalSpacing : 50;
	return (spacing / 100) * 80;
});

const openProjectModal = (p: any) => {
	if (!page.props.auth?.user) {
		addToast("Anda belum login. Silakan login terlebih dahulu untuk melihat karya.", "info");
		return;
	}
	viewingProject.value = p;
	document.body.style.overflow = "hidden";
};
const closeProjectModal = () => {
	viewingProject.value = null;
	document.body.style.overflow = "auto";
};

const commentText = ref('');
const isSubmittingComment = ref(false);
const isLikingProject = ref(false);

const toggleLikeProject = async () => {
	if (!page.props.auth?.user) {
		addToast("Please log in to appreciate this project.", "info");
		return;
	}
	if (!viewingProject.value || isLikingProject.value) return;
	isLikingProject.value = true;
	try {
		const res = await axios.post(`/pagi/preview/${viewingProject.value.id}/like`);
		viewingProject.value.liked = res.data.liked;
		viewingProject.value.likes = res.data.likes;
		
		// Update likes in props.projects list too so it reflects in the tab
		const proj = props.projects?.find((p: any) => p.id === viewingProject.value.id);
		if (proj) {
			proj.liked = res.data.liked;
			proj.likes = res.data.likes;
		}

		if (res.data.liked) {
			addToast("Appreciated project!", "success");
		} else {
			addToast("Removed appreciation.", "info");
		}
	} catch (e) {
		addToast("Failed to update appreciation.", "error");
	} finally {
		isLikingProject.value = false;
	}
};

const submitComment = async () => {
	if (!commentText.value.trim() || !viewingProject.value) return;
	isSubmittingComment.value = true;
	try {
		const res = await axios.post(`/pagi/preview/${viewingProject.value.id}/comment`, {
			body: commentText.value
		});
		viewingProject.value.comments = res.data.comments;
		commentText.value = '';
		addToast("Comment posted successfully!", "success");
	} catch (e) {
		addToast("Failed to post comment. Please try again.", "error");
	} finally {
		isSubmittingComment.value = false;
	}
};

const showRelationsModal = ref(false);
const relationsModalType = ref<'followers' | 'following'>('followers');
const followersList = ref<any[]>([]);
const followingList = ref<any[]>([]);
const isLoadingRelations = ref(false);
const FollbackInProgress = ref<Record<number, boolean>>({});
const relationsSearchQuery = ref("");

const filteredRelations = computed(() => {
	const list = relationsModalType.value === 'followers' ? followersList.value : followingList.value;
	if (!relationsSearchQuery.value.trim()) return list;
	const q = relationsSearchQuery.value.toLowerCase().trim();
	return list.filter((item: any) => item.name?.toLowerCase().includes(q));
});

const isFollowingBack = (senderId: number) => {
	const following = page.props.auth?.user?.metadata?.following ?? [];
	return following.includes(senderId);
};

const openRelationsModal = async (type: 'followers' | 'following') => {
	relationsModalType.value = type;
	relationsSearchQuery.value = "";
	showRelationsModal.value = true;
	isLoadingRelations.value = true;
	try {
		const res = await axios.get(`/pagi/users/${user.value.id}/relations`);
		followersList.value = res.data.followers || [];
		followingList.value = res.data.following || [];
	} catch (e) {
		console.error("Failed to load relations:", e);
		addToast("Gagal memuat data relasi.", "error");
	} finally {
		isLoadingRelations.value = false;
	}
};

const toggleFollowRelation = async (rel: any) => {
	const senderId = rel.id;
	if (!senderId) return;

	FollbackInProgress.value[senderId] = true;
	try {
		const res = await axios.post(`/pagi/users/${senderId}/follow`);
		const following = page.props.auth.user.metadata?.following ?? [];
		if (res.data.following) {
			if (!following.includes(senderId)) following.push(senderId);
			addToast(`Mulai mengikuti ${rel.name}!`, "success");
		} else {
			const idx = following.indexOf(senderId);
			if (idx > -1) following.splice(idx, 1);
			addToast(`Berhenti mengikuti ${rel.name}.`, "info");
		}
		if (!page.props.auth.user.metadata) {
			page.props.auth.user.metadata = {};
		}
		page.props.auth.user.metadata.following = following;

		// Reactively update following count if modifying own relations
		if (isOwnProfile.value) {
			if (props.profileUser) {
				if (res.data.following) {
					props.profileUser.following_count = (props.profileUser.following_count ?? 0) + 1;
				} else {
					props.profileUser.following_count = Math.max(0, (props.profileUser.following_count ?? 1) - 1);
				}
			}
		}
	} catch (e) {
		console.error("Relation follow toggle failed:", e);
	} finally {
		FollbackInProgress.value[senderId] = false;
	}
};

const selectWorkTab = () => {
	activeTab.value = "Work";
	const el = document.getElementById("profile_tabs_navigation");
	if (el) {
		el.scrollIntoView({ behavior: "smooth" });
	}
};

const editingProject = ref<any | null>(null);

// Add Work Modal Helper Handlers
const openAddWorkModal = () => {
	editingProject.value = null;
	isEditingQuickWork.value = false;
	editingQuickWorkId.value = null;
	showAddWorkModal.value = true;
};

const openEditQuickWorkModal = (p: any) => {
	editingProject.value = p;
	isEditingQuickWork.value = true;
	editingQuickWorkId.value = p.id;
	showAddWorkModal.value = true;
};

const handleQuickStoreSuccess = (project: any) => {
	if (isEditingQuickWork.value && editingQuickWorkId.value) {
		const idx = localProjects.value.findIndex(p => p.id === editingQuickWorkId.value);
		if (idx !== -1) {
			localProjects.value[idx] = project;
		}
		addToast("Karya berhasil diperbarui!", "success");
	} else {
		localProjects.value = [project, ...localProjects.value];
		newCreatedProject.value = project;
		setTimeout(() => {
			showShareModal.value = true;
		}, 300);
		addToast("Karya berhasil ditambahkan!", "success");
	}
	showAddWorkModal.value = false;
};


// Stats computations
const totalViews = computed(() => {
	return localProjects.value?.reduce((acc, p) => acc + (Number(p.views) || 0), 0) || 518;
});

const totalLikes = computed(() => {
	return localProjects.value?.reduce((acc, p) => {
		const likesVal = typeof p.likes === 'number' 
			? p.likes 
			: (Array.isArray(p.likes) 
				? p.likes.length 
				: (typeof p.likes === 'string' ? parseInt(p.likes, 10) || 0 : 0));
		return acc + likesVal;
	}, 0) || 0;
});

const projectCount = computed(() => {
	return localProjects.value?.length || 0;
});

// Dynamic Followers Count — driven by real server data
const realFollowersCount = ref<number>(
	props.profileUser?.followers_count ?? (user.value.metadata?.followers?.length ?? 0)
);
const dynamicFollowersCount = computed(() => {
	return realFollowersCount.value;
});

const dynamicFollowingCount = computed(() => {
	return props.profileUser?.following_count ?? (user.value.metadata?.following?.length ?? 0);
});

const displayOwnerRoleName = computed(() => {
	// Prioritas 1: role PAGI pemilik profil (dikirim dari controller untuk public profile)
	if (user.value.pagi_role) return user.value.pagi_role;
	// Prioritas 2: role_title sebagai creative headline (jika user mengisinya sendiri)
	if (user.value.role_title) return user.value.role_title;
	// Prioritas 3: fallback ke user_type
	if (user.value.user_type) {
		const type = user.value.user_type.toLowerCase();
		if (type === 'mahasiswa') return 'Mahasiswa';
		if (type === 'super_admin' || type === 'super-admin') return 'Super Admin';
		if (type === 'dosen') return 'Dosen';
		if (type === 'alumni') return 'Alumni';
		if (type === 'mitra') return 'Mitra Perusahaan';
		return user.value.user_type.charAt(0).toUpperCase() + user.value.user_type.slice(1);
	}
	return 'Anggota PAGI';
});

// Helper: Format social urls
const socialLinks = computed(() => {
	const links = [];
	if (user.value.website) links.push({ type: 'website', url: user.value.website, label: 'Website' });
	if (user.value.linkedin) links.push({ type: 'linkedin', url: user.value.linkedin.startsWith('http') ? user.value.linkedin : `https://linkedin.com/in/${user.value.linkedin}`, label: 'LinkedIn' });
	if (user.value.github) links.push({ type: 'github', url: user.value.github.startsWith('http') ? user.value.github : `https://github.com/${user.value.github}`, label: 'GitHub' });
	if (user.value.twitter) links.push({ type: 'twitter', url: user.value.twitter.startsWith('http') ? user.value.twitter : `https://twitter.com/${user.value.twitter}`, label: 'Twitter' });
	if (user.value.instagram) links.push({ type: 'instagram', url: user.value.instagram.startsWith('http') ? user.value.instagram : `https://instagram.com/${user.value.instagram}`, label: 'Instagram' });
	return links;
});

// Filtered projects
const filteredProjects = computed(() => {
	if (!localProjects.value) return [];
	return localProjects.value;
});

// Dynamic Skills tags
const skills = computed(() => {
	const val = user.value.skills || user.value.metadata?.skills || props.profileUser?.skills;
	return Array.isArray(val) ? parseSkills(val) : parseSkills(['Figma', 'UI/UX Design', 'Vue.js']);
});

const timezone = computed(() => {
	return user.value.timezone || user.value.metadata?.timezone || props.profileUser?.timezone || "";
});

const timezoneExtended = computed(() => {
	return user.value.timezone_extended || user.value.timezoneExtended || user.value.metadata?.timezone_extended || user.value.metadata?.timezoneExtended || props.profileUser?.timezone_extended || props.profileUser?.timezoneExtended || "No extended hours";
});

const languages = computed(() => {
	const langs = user.value.languages || user.value.metadata?.languages || props.profileUser?.languages;
	return Array.isArray(langs) ? langs : [];
});

const updateSkills = (newSkills: string[]) => {
	form.skills = newSkills;
	form.post("/pagi/profile/update", {
		preserveScroll: true,
		onSuccess: () => {
			initFormValues();
			addToast("Skills updated successfully!", "success");
		}
	});
};

const updateBio = (newBio: string) => {
	form.bio = newBio;
	form.post("/pagi/profile/update", {
		preserveScroll: true,
		onSuccess: () => {
			initFormValues();
			addToast("Biography updated successfully!", "success");
		}
	});
};

const updateLocation = (newLocation: string) => {
	form.location = newLocation;
	form.post("/pagi/profile/update", {
		preserveScroll: true,
		onSuccess: () => {
			initFormValues();
			addToast("Location updated successfully!", "success");
		}
	});
};

const updateTimezone = (data: { timezone: string, extended: string }) => {
	form.timezone = data.timezone;
	form.timezone_extended = data.extended;
	form.post("/pagi/profile/update", {
		preserveScroll: true,
		onSuccess: () => {
			initFormValues();
			addToast("Time zone updated successfully!", "success");
		}
	});
};

const updateLanguages = (newLanguages: Array<{ language: string, proficiency: string }>) => {
	form.languages = newLanguages;
	form.post("/pagi/profile/update", {
		preserveScroll: true,
		onSuccess: () => {
			initFormValues();
			addToast("Languages updated successfully!", "success");
		}
	});
};

const updateSocials = (socials: { website?: string, linkedin?: string, github?: string, twitter?: string, instagram?: string }) => {
	form.website = socials.website || "";
	form.linkedin = socials.linkedin || "";
	form.github = socials.github || "";
	form.twitter = socials.twitter || "";
	form.instagram = socials.instagram || "";
	form.post("/pagi/profile/update", {
		preserveScroll: true,
		onSuccess: () => {
			initFormValues();
			addToast("Social links updated successfully!", "success");
		}
	});
};

// CropperJS delegation handlers

const handleBannerDrop = (e: DragEvent) => {
	isDragging.value = false;
	const file = e.dataTransfer?.files?.[0];
	if (file) {
		processBannerFile(file);
	}
};

const handleBannerChange = (e: Event) => {
	const file = (e.target as HTMLInputElement).files?.[0];
	if (file) {
		processBannerFile(file);
	}
};

const processBannerFile = async (file: File) => {
	// Size limit check (100MB = 100 * 1024 * 1024 bytes)
	if (file.size > 100 * 1024 * 1024) {
		triggerWarning(
			"File Too Large",
			"The selected file size exceeds the 100MB limit. Please choose a smaller file."
		);
		return;
	}

	// Mime-type verification
	const validTypes = [
		"image/jpeg", "image/png", "image/webp", "image/gif",
		"video/mp4", "video/webm", "video/ogg"
	];
	if (!validTypes.includes(file.type)) {
		triggerWarning(
			"Invalid File Format",
			"Please upload a valid image (png, jpg, webp, gif) or video (mp4, webm, ogg)."
		);
		return;
	}

	originalFileName = file.name;
	originalFileType.value = file.type;

	// For videos: assign file directly and update preview with duration check
	if (file.type.startsWith("video/")) {
		const video = document.createElement('video');
		video.preload = 'metadata';
		video.onloadedmetadata = () => {
			window.URL.revokeObjectURL(video.src);
			if (video.duration > 60.5) {
				triggerWarning(
					"Video Terlalu Lama",
					"Durasi video maksimal adalah 1 menit (60 detik) demi menjaga performa server."
				);
			} else {
				selectedPresetIndex.value = null;
				form.banner = file;
				originalFileName = file.name;
				originalFileType.value = file.type;
				bannerPreview.value = URL.createObjectURL(file); // immediate preview
				addToast("Video loaded successfully!", "success");
			}
		};
		video.src = URL.createObjectURL(file);
		return;
	}

	// For animated GIFs, upload directly (no crop)
	if (file.type === "image/gif") {
		form.banner = file;
		selectedPresetIndex.value = null;
		bannerPreview.value = URL.createObjectURL(file);
		addToast("GIF loaded successfully!", "success");
		return;
	}

	const reader = new FileReader();
	reader.onload = (event) => {
		if (event.target?.result) {
			cropperImageSrc.value = event.target.result as string;
			isCropperOpen.value = true;
		}
	};
	reader.readAsDataURL(file);
};


const handleCropSave = (croppedFile: File) => {
	form.banner = croppedFile;
	selectedPresetIndex.value = null; // Reset preset since we used custom upload
	bannerPreview.value = URL.createObjectURL(croppedFile);
	closeCropper();
};

const closeCropper = () => {
	isCropperOpen.value = false;
	cropperImageSrc.value = "";
};

const handleFotoChange = (e: Event) => {
	const file = (e.target as HTMLInputElement).files?.[0];
	if (file) {
		form.foto = file;
		form.remove_foto = false;
		form.avatar_url = "";
		photoPreview.value = URL.createObjectURL(file);
	}
};

const removeProfilePhoto = () => {
	form.foto = null;
	form.avatar_url = "";
	form.remove_foto = true;
	photoPreview.value = null;
};

const triggerBannerUpload = () => {
	bannerInput.value?.click();
};

const triggerFotoUpload = () => {
	fotoInput.value?.click();
};

const clearBannerSelection = () => {
	form.banner = null;
	bannerPreview.value = null;
	selectedPresetIndex.value = null;
};

// Preset Banner options
const presets = [
	{
		name: "Cyber Slate",
		svgMini: `
			<svg viewBox="0 0 320 41" width="100%" height="100%" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
				<rect width="100%" height="100%" fill="#0f172a"/>
				<defs>
					<pattern id="gridMini1" width="8" height="8" patternUnits="userSpaceOnUse">
						<path d="M 8 0 L 0 0 0 8" fill="none" stroke="rgba(51, 65, 85, 0.3)" stroke-width="0.5"/>
					</pattern>
					<radialGradient id="glowMini1" cx="80%" cy="20%" r="60%">
						<stop offset="0%" stop-color="#3b82f6" stop-opacity="0.25"/>
						<stop offset="100%" stop-color="#0f172a" stop-opacity="0"/>
					</radialGradient>
				</defs>
				<rect width="100%" height="100%" fill="url(#gridMini1)"/>
				<rect width="100%" height="100%" fill="url(#glowMini1)"/>
			</svg>
		`,
		svgFull: `
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3200 410" width="3200" height="410">
				<rect width="3200" height="410" fill="#0f172a"/>
				<defs>
					<pattern id="gridFull1" width="40" height="40" patternUnits="userSpaceOnUse">
						<path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(51, 65, 85, 0.3)" stroke-width="1"/>
					</pattern>
					<radialGradient id="glowFull1" cx="80%" cy="20%" r="60%">
						<stop offset="0%" stop-color="#3b82f6" stop-opacity="0.15"/>
						<stop offset="50%" stop-color="#8b5cf6" stop-opacity="0.05"/>
						<stop offset="100%" stop-color="#0f172a" stop-opacity="0"/>
					</radialGradient>
					<linearGradient id="lineGrad1" x1="0%" y1="0%" x2="100%" y2="0%">
						<stop offset="0%" stop-color="#3b82f6"/>
						<stop offset="50%" stop-color="#8b5cf6"/>
						<stop offset="100%" stop-color="#ec4899"/>
					</linearGradient>
				</defs>
				<rect width="3200" height="410" fill="url(#gridFull1)"/>
				<rect width="3200" height="410" fill="url(#glowFull1)"/>
				<circle cx="1600" cy="205" r="300" fill="none" stroke="rgba(99, 102, 241, 0.05)" stroke-width="1" stroke-dasharray="10 10"/>
				<circle cx="1600" cy="205" r="450" fill="none" stroke="rgba(99, 102, 241, 0.03)" stroke-width="2"/>
				<path d="M-100,300 L600,100 L1200,250 L1800,50 L2400,200 L3300,50" fill="none" stroke="url(#lineGrad1)" stroke-width="2" opacity="0.3"/>
			</svg>
		`
	},
	{
		name: "Aurora Flow",
		svgMini: `
			<svg viewBox="0 0 320 41" width="100%" height="100%" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
				<defs>
					<linearGradient id="auroraBgMini" x1="0%" y1="0%" x2="100%" y2="100%">
						<stop offset="0%" stop-color="#4f46e5"/>
						<stop offset="50%" stop-color="#7c3aed"/>
						<stop offset="100%" stop-color="#c084fc"/>
					</linearGradient>
					<radialGradient id="blobMini1" cx="20%" cy="30%" r="50%">
						<stop offset="0%" stop-color="#60a5fa" stop-opacity="0.6"/>
						<stop offset="100%" stop-color="#60a5fa" stop-opacity="0"/>
					</radialGradient>
					<radialGradient id="blobMini2" cx="80%" cy="70%" r="60%">
						<stop offset="0%" stop-color="#f472b6" stop-opacity="0.6"/>
						<stop offset="100%" stop-color="#f472b6" stop-opacity="0"/>
					</radialGradient>
				</defs>
				<rect width="100%" height="100%" fill="url(#auroraBgMini)"/>
				<rect width="100%" height="100%" fill="url(#blobMini1)"/>
				<rect width="100%" height="100%" fill="url(#blobMini2)"/>
			</svg>
		`,
		svgFull: `
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3200 410" width="3200" height="410">
				<defs>
					<linearGradient id="auroraBgFull" x1="0%" y1="0%" x2="100%" y2="100%">
						<stop offset="0%" stop-color="#4f46e5"/>
						<stop offset="50%" stop-color="#7c3aed"/>
						<stop offset="100%" stop-color="#c084fc"/>
					</linearGradient>
					<radialGradient id="blobFull1" cx="20%" cy="30%" r="50%">
						<stop offset="0%" stop-color="#60a5fa" stop-opacity="0.6"/>
						<stop offset="100%" stop-color="#60a5fa" stop-opacity="0"/>
					</radialGradient>
					<radialGradient id="blobFull2" cx="80%" cy="70%" r="60%">
						<stop offset="0%" stop-color="#f472b6" stop-opacity="0.6"/>
						<stop offset="100%" stop-color="#f472b6" stop-opacity="0"/>
					</radialGradient>
				</defs>
				<rect width="3200" height="410" fill="url(#auroraBgFull)"/>
				<rect width="3200" height="410" fill="url(#blobFull1)"/>
				<rect width="3200" height="410" fill="url(#blobFull2)"/>
				<path d="M 0 200 Q 800 50 1600 250 T 3200 150 L 3200 410 L 0 410 Z" fill="rgba(255, 255, 255, 0.05)"/>
				<path d="M 0 300 Q 600 150 1400 350 T 3200 250 L 3200 410 L 0 410 Z" fill="rgba(255, 255, 255, 0.03)"/>
			</svg>
		`
	},
	{
		name: "Midnight Mesh",
		svgMini: `
			<svg viewBox="0 0 320 41" width="100%" height="100%" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
				<rect width="100%" height="100%" fill="#030712"/>
				<defs>
					<radialGradient id="neonGlowMini" cx="50%" cy="50%" r="50%">
						<stop offset="0%" stop-color="#06b6d4" stop-opacity="0.2"/>
						<stop offset="100%" stop-color="#030712" stop-opacity="0"/>
					</radialGradient>
				</defs>
				<rect width="100%" height="100%" fill="url(#neonGlowMini)"/>
				<g stroke="rgba(6, 182, 212, 0.2)" stroke-width="0.3">
					<line x1="20" y1="10" x2="40" y2="25" />
					<line x1="40" y1="25" x2="60" y2="15" />
					<line x1="60" y1="15" x2="80" y2="30" />
				</g>
			</svg>
		`,
		svgFull: `
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3200 410" width="3200" height="410">
				<rect width="3200" height="410" fill="#030712"/>
				<defs>
					<radialGradient id="neonGlowFull" cx="50%" cy="50%" r="50%">
						<stop offset="0%" stop-color="#06b6d4" stop-opacity="0.15"/>
						<stop offset="100%" stop-color="#030712" stop-opacity="0"/>
					</radialGradient>
				</defs>
				<rect width="3200" height="410" fill="url(#neonGlowFull)"/>
				<g stroke="rgba(6, 182, 212, 0.15)" stroke-width="1">
					<line x1="200" y1="100" x2="400" y2="250" />
					<line x1="400" y1="250" x2="600" y2="150" />
					<line x1="600" y1="150" x2="800" y2="300" />
					<line x1="800" y1="300" x2="1000" y2="100" />
					<line x1="200" y1="100" x2="300" y2="50" />
					<line x1="400" y1="250" x2="450" y2="350" />
					<line x1="600" y1="150" x2="700" y2="80" />
					<line x1="800" y1="300" x2="900" y2="380" />
					<line x1="300" y1="50" x2="450" y2="350" stroke="rgba(139, 92, 246, 0.12)" />
				</g>
				<g fill="#22d3ee">
					<circle cx="200" cy="100" r="3" />
					<circle cx="400" cy="250" r="3" />
					<circle cx="600" cy="150" r="3" />
					<circle cx="800" cy="300" r="3" />
					<circle cx="1000" cy="100" r="3" />
				</g>
			</svg>
		`
	},
	{
		name: "Monochrome Tech",
		svgMini: `
			<svg viewBox="0 0 320 41" width="100%" height="100%" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
				<rect width="100%" height="100%" fill="#f8fafc"/>
				<defs>
					<pattern id="dotGridMini" width="4" height="4" patternUnits="userSpaceOnUse">
						<circle cx="0.5" cy="0.5" r="0.25" fill="#cbd5e1" />
					</pattern>
				</defs>
				<rect width="100%" height="100%" fill="url(#dotGridMini)"/>
			</svg>
		`,
		svgFull: `
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3200 410" width="3200" height="410">
				<rect width="3200" height="410" fill="#f8fafc"/>
				<defs>
					<pattern id="dotGridFull" width="30" height="30" patternUnits="userSpaceOnUse">
						<circle cx="3" cy="3" r="1.5" fill="#e2e8f0" />
					</pattern>
				</defs>
				<rect width="3200" height="410" fill="url(#dotGridFull)"/>
				<line x1="0" y1="80" x2="3200" y2="80" stroke="#f1f5f9" stroke-width="2"/>
				<line x1="0" y1="330" x2="3200" y2="330" stroke="#f1f5f9" stroke-width="2"/>
			</svg>
		`
	},
	{
		name: "Academic Gold",
		svgMini: `
			<svg viewBox="0 0 320 41" width="100%" height="100%" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
				<defs>
					<linearGradient id="royalNavyMini" x1="0%" y1="0%" x2="100%" y2="0%">
						<stop offset="0%" stop-color="#0f172a"/>
						<stop offset="100%" stop-color="#1e293b"/>
					</linearGradient>
				</defs>
				<rect width="100%" height="100%" fill="url(#royalNavyMini)"/>
			</svg>
		`,
		svgFull: `
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3200 410" width="3200" height="410">
				<defs>
					<linearGradient id="royalNavyFull" x1="0%" y1="0%" x2="100%" y2="0%">
						<stop offset="0%" stop-color="#0f172a"/>
						<stop offset="60%" stop-color="#1e293b"/>
						<stop offset="100%" stop-color="#0f172a"/>
					</linearGradient>
					<linearGradient id="goldGradFull" x1="0%" y1="0%" x2="100%" y2="100%">
						<stop offset="0%" stop-color="#fbbf24"/>
						<stop offset="50%" stop-color="#f59e0b"/>
						<stop offset="100%" stop-color="#b45309"/>
					</linearGradient>
				</defs>
				<rect width="3200" height="410" fill="url(#royalNavyFull)"/>
				<path d="M 2400 410 C 2700 180, 2800 280, 3200 100" fill="none" stroke="url(#goldGradFull)" stroke-width="3" opacity="0.85"/>
			</svg>
		`
	}
];

const selectPreset = (index: number) => {
	selectedPresetIndex.value = index;
	const preset = presets[index];
	
	const svgString = preset.svgFull;
	const img = new Image();
	img.onload = () => {
		const canvas = document.createElement("canvas");
		canvas.width = 3200;
		canvas.height = 410;
		const ctx = canvas.getContext("2d");
		if (ctx) {
			ctx.drawImage(img, 0, 0, 3200, 410);
			canvas.toBlob((blob) => {
				if (blob) {
					const presetFile = new File([blob], `preset-${preset.name.toLowerCase().replace(/\s+/g, '-')}.png`, {
						type: "image/png"
					});
					form.banner = presetFile;
					bannerPreview.value = URL.createObjectURL(presetFile);
				}
			}, "image/png");
		}
	};
	img.src = "data:image/svg+xml;charset=utf-8," + encodeURIComponent(svgString);
};

const getToolSlug = (toolName: string): string => {
	const name = toolName.toLowerCase().trim();
	if (name === "figma") return "figma";
	if (name === "photoshop" || name === "adobe photoshop" || name === "ps") return "photoshop";
	if (name === "illustrator" || name === "adobe illustrator" || name === "ai") return "illustrator";
	if (name === "premiere" || name === "premiere pro" || name === "pr" || name === "premierepro") return "premiere";
	if (name === "vs code" || name === "vscode" || name === "visual studio code" || name === "visual-studio-code") return "visual-studio-code";
	if (name === "visual studio" || name === "vs") return "visual-studio";
	if (name === "vue" || name === "vue.js" || name === "vuejs" || name === "vuedotjs") return "vue";
	if (name === "react" || name === "reactjs" || name === "react.js") return "react";
	if (name === "tailwind" || name === "tailwindcss" || name === "tailwind css" || name === "tailwind-css") return "tailwind-css";
	if (name === "laravel") return "laravel";
	if (name === "php") return "php";
	if (name === "javascript" || name === "js") return "javascript";
	if (name === "html" || name === "html5") return "html5";
	if (name === "css" || name === "css3") return "css";
	if (name === "git") return "git";
	if (name === "github") return "github";
	if (name === "docker") return "docker";
	if (name === "postman") return "postman";
	if (name === "canva") return "canva";
	if (name === "trello") return "trello";
	if (name === "jira") return "jira";
	if (name === "sass" || name === "scss") return "sass";
	if (name === "nodejs" || name === "node" || name === "node.js") return "nodedotjs";
	if (name === "typescript" || name === "ts") return "typescript";
	if (name === "python") return "python";
	if (name === "mysql") return "mysql";
	if (name === "postgresql" || name === "postgres") return "postgresql";
	if (name === "mongodb" || name === "mongo") return "mongodb";
	if (name === "firebase") return "firebase";
	if (name === "flutter") return "flutter";
	if (name === "kotlin") return "kotlin";
	if (name === "swift") return "swift";
	if (name === "xd" || name === "adobe xd") return "adobe-xd";
	if (name === "indesign" || name === "adobe indesign") return "adobe-indesign";
	if (name === "after effects" || name === "ae" || name === "adobe after effects") return "adobe-after-effects";
	
	return name
		.replace(/\.js/g, "dotjs")
		.replace(/\.net/g, "dotnet")
		.replace(/[^a-z0-9]+/g, "-");
};

const jsonLdString = computed(() => {
	const origin = typeof window !== "undefined" ? window.location.origin : "";
	const sameAs = [
		user.value.website,
		user.value.linkedin ? `https://linkedin.com/in/${user.value.linkedin}` : "",
		user.value.github ? `https://github.com/${user.value.github}` : "",
		user.value.twitter ? `https://twitter.com/${user.value.twitter}` : "",
		user.value.instagram ? `https://instagram.com/${user.value.instagram}` : "",
	].filter(Boolean);

	return JSON.stringify({
		"@context": "https://schema.org",
		"@type": "Person",
		"name": user.value.name,
		"jobTitle": user.value.role_title || displayRoleName.value,
		"description": user.value.bio || "",
		"image": user.value.foto_path
			? (user.value.foto_path.startsWith("http")
				? user.value.foto_path
				: `${origin}/storage/${user.value.foto_path}`)
			: "",
		"url": user.value.pagi_username ? `${origin}/pagi/${user.value.pagi_username}` : `${origin}/pagi/profile/${user.value.id}`,
		"sameAs": sameAs,
	});
});

const headTitle = computed(() => {
	if (viewingProject.value) {
		return `${viewingProject.value.title} by ${user.value.name} — PAGI Portfolio`;
	}
	return `${user.value.name} — ${props.moduleName || 'PAGI'} Profile`;
});

const headDescription = computed(() => {
	if (viewingProject.value) {
		if (viewingProject.value.description) {
			const cleanDesc = viewingProject.value.description.replace(/<[^>]*>/g, '');
			return cleanDesc.length > 160 ? cleanDesc.slice(0, 157) + '...' : cleanDesc;
		}
		return `Lihat karya "${viewingProject.value.title}" oleh ${user.value.name} di FMIKOM Portal.`;
	}
	return user.value.bio || 'FMIKOM Portal profile page. Hubungkan, kolaborasi, dan eksplorasi karya kreatif mahasiswa.';
});

const headImage = computed(() => {
	if (viewingProject.value && viewingProject.value.image) {
		return viewingProject.value.image;
	}
	return user.value.foto_path 
		? (user.value.foto_path.startsWith('http') ? user.value.foto_path : '/storage/' + user.value.foto_path)
		: '/og-image.png';
});

const headType = computed(() => {
	return viewingProject.value ? 'article' : 'profile';
});

const headUrl = computed(() => {
	const base = user.value.pagi_username ? `/pagi/${user.value.pagi_username}` : `/pagi/profile/${user.value.id}`;
	if (viewingProject.value) {
		return `${window.location.origin}${base}?project=${viewingProject.value.id}`;
	}
	return `${window.location.origin}${base}`;
});
</script>


<template>
	<Head :title="headTitle">
		<!-- Meta Tags -->
		<meta name="description" :content="headDescription" />
		
		<!-- Open Graph / Facebook -->
		<meta property="og:type" :content="headType" />
		<meta property="og:title" :content="headTitle" />
		<meta property="og:description" :content="headDescription" />
		<meta property="og:image" :content="headImage" />
		<meta property="og:url" :content="headUrl" />

		<!-- Twitter -->
		<meta name="twitter:card" content="summary_large_image" />
		<meta name="twitter:title" :content="headTitle" />
		<meta name="twitter:description" :content="headDescription" />
		<meta name="twitter:image" :content="headImage" />

		<link rel="canonical" :href="headUrl" />

		<!-- Structured JSON-LD Data -->
		<script type="application/ld+json" v-html="jsonLdString"></script>
	</Head>

	<div class="min-h-screen bg-slate-100 dark:bg-slate-950 font-sans text-slate-900 dark:text-slate-100 selection:bg-slate-900 selection:text-white dark:selection:bg-white dark:selection:text-slate-900" style="font-family:'Inter',system-ui,sans-serif;">
		
		<Navbar :roleName="displayRoleName" />
		<!-- Outer page padding to create the floating layer gap ("ngambang" effect) -->
		<div class="mx-auto max-w-[1480px] px-1.5 sm:px-5 lg:px-6 pt-3 sm:pt-5 pb-0">
					<!-- Inner Floating Card Layer ("Layout Lapisan") -->
			<main class="bg-white dark:bg-slate-900 border-x border-t border-slate-200/60 dark:border-slate-800 rounded-t-[24px] rounded-b-none pt-5 pb-32 px-3.5 sm:p-8 md:p-10 shadow-[0_15px_45px_-10px_rgba(15,23,42,0.08)] dark:shadow-[0_20px_50px_-15px_rgba(0,0,0,0.6)] relative">
				<ProfileHeader
					:isLoading="isLoading"
					:isOwnProfile="isOwnProfile"
					:isFollowing="isFollowing"
					:isMessageEnabled="isMessageEnabled"
					:user="user"
					:displayRoleName="displayRoleName"
					:displayOwnerRoleName="displayOwnerRoleName"
					:projectCount="projectCount"
					:totalLikes="totalLikes"
					:dynamicFollowersCount="dynamicFollowersCount"
					:dynamicFollowingCount="dynamicFollowingCount"
					:socialLinks="socialLinks"
					:presets="presets"
					@open-avatar-modal="openAvatarModal"
					@open-username-modal="openUsernameModal"
					@open-location-modal="openLocationModal"
					@open-location-only-modal="openLocationOnlyModal"
					@open-socials-modal="openSocialLinksModal"
					@open-banner-modal="openBannerModal"
					@open-chat="openChat"
					@toggle-message-switch="toggleMessageSwitch"
					@share-profile="shareProfile"
					@toggle-follow="toggleFollow"
					@select-work-tab="selectWorkTab"
					@open-relations-modal="openRelationsModal"
				/>

				<!-- SECTION 2: TABS NAVIGATION AND LOCATION BAR -->
				<ProfileTabs
					:tabs="tabs"
					v-model:activeTab="activeTab"
					:isLoading="isLoading"
					:isOwnProfile="isOwnProfile"
					:user="user"
					:socialLinks="socialLinks"
					@click-location="openLocationOnlyModal"
					@click-socials="openSocialLinksModal"
				/>

				<!-- SECTION 3: TABS CONTENT -->
				<div class="transition-all duration-300">
					<WorkTab 
						v-if="activeTab === 'Work'"
						:projects="projects"
						:isOwnProfile="isOwnProfile"
						:user="user"
						:isLoading="isLoading"
						@open-project="openProjectModal"
						@clone-project="cloneProject"
						@share-project="shareProject"
						@delete-project="deleteProject"
						@open-add-work="openAddWorkModal"
						@edit-quick-work="openEditQuickWorkModal"
						@like-updated="handleLikeUpdated"
					/>
					<GalleryTab 
						v-else-if="activeTab === 'Gallery'"
						:projects="projects"
						:isOwnProfile="isOwnProfile"
						:isLoading="isLoading"
						@open-project="openProjectModal"
						@delete-project="deleteProject"
						@gallery-item-added="(item) => localProjects.unshift(item)"
						@gallery-item-updated="handleGalleryItemUpdated"
						@share-project="shareProject"
					/>
					<SertifikatTab 
						v-else-if="activeTab === 'Certificates'"
						:isOwnProfile="isOwnProfile"
						:certificates="certificates"
						:isLoading="isLoading"
						@add-toast="addToast"
						@update-certificates="(list) => certificates = list"
					/>
					<AboutTab 
						v-else-if="activeTab === 'About'"
						:profileUser="profileUser"
						:user="user"
						:isOwnProfile="isOwnProfile"
						:isFollowing="isFollowing"
						:isMessageEnabled="isMessageEnabled"
						:dynamicFollowersCount="dynamicFollowersCount"
						:skills="skills"
						:timezone="timezone"
						:timezoneExtended="timezoneExtended"
						:languages="languages"
						:isLoading="isLoading"
						@update-bio="updateBio"
						@update-skills="updateSkills"
						@update-location="updateLocation"
						@update-timezone="updateTimezone"
						@update-languages="updateLanguages"
						@update-socials="updateSocials"
						@open-avatar-modal="openAvatarModal"
						@open-socials-modal="openSocialLinksModal"
						@toggle-follow="toggleFollow"
						@open-chat="openChat"
					/>
				</div>
			</main>
		</div>

		<!-- TOAST ALERTS CONTAINER -->
		<div class="fixed top-6 right-6 z-[10010] flex flex-col gap-3.5 max-w-xs pointer-events-none">
			<TransitionGroup 
				enter-active-class="transform transition duration-300 ease-out"
				enter-from-class="translate-y-2 opacity-0 scale-95"
				enter-to-class="translate-y-0 opacity-100 scale-100"
				leave-active-class="transition duration-200 ease-in"
				leave-from-class="opacity-100 scale-100"
				leave-to-class="opacity-0 scale-95"
			>
				<div 
					v-for="toast in toasts" 
					:key="toast.id"
					class="p-4 rounded-xl border flex items-start gap-3 shadow-xl backdrop-blur-md pointer-events-auto select-none"
					:class="[
						toast.type === 'success' 
							? 'bg-slate-900/90 border-slate-800 text-white dark:bg-white/95 dark:border-slate-200 dark:text-slate-900' 
							: 'bg-slate-100/90 border-slate-200 text-slate-800 dark:bg-slate-900/95 dark:border-slate-800 dark:text-slate-100'
					]"
				>
					<div class="flex-1 text-xs font-bold leading-relaxed pr-2">
						{{ toast.message }}
					</div>
					<button @click="toasts = toasts.filter(t => t.id !== toast.id)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 shrink-0">
						<X class="w-3.5 h-3.5" />
					</button>
				</div>
			</TransitionGroup>
		</div>


		<Preview
			v-if="viewingProject"
			:title="viewingProject.title"
			:content="viewingProject.content"
			:cover-image="viewingProject.image"
			:portfolio="viewingProject"
			:canvas-bg-color="activeProjectSettings.canvasBgColor || '#ffffff'"
			:canvas-text-color="activeProjectSettings.canvasTextColor || '#111827'"
			:canvas-border-color="activeProjectSettings.canvasBorderColor || '#e2e8f0'"
			:global-spacing="activeProjectSettings.globalSpacing || 50"
			:description="viewingProject.description"
			:category="viewingProject.category"
			:tools-used="viewingProject.tools_used"
			:tags="viewingProject.tags"
			@close="closeProjectModal"
			@select-portfolio="viewingProject = $event"
		/>

		<!-- MODALS SECTION -->
		<!-- 1. Avatar Uploader Modal -->
		<Modal :show="showAvatarModal" title="Update Profile Avatar" maxWidth="sm" @close="showAvatarModal = false" :preventClose="form.processing">
			<div class="relative flex flex-col items-center gap-6 p-4">
				<!-- Uploading Overlay -->
				<Transition
					enter-active-class="transition duration-300 ease-out"
					enter-from-class="opacity-0 scale-95"
					enter-to-class="opacity-100 scale-100"
					leave-active-class="transition duration-200 ease-in"
					leave-from-class="opacity-100 scale-100"
					leave-to-class="opacity-0 scale-95"
				>
					<div v-if="form.processing && form.progress" class="absolute inset-0 bg-white/95 dark:bg-slate-900/95 z-50 flex flex-col items-center justify-center p-4 rounded-[20px] text-center space-y-4">
						<div class="w-12 h-12 rounded-full bg-indigo-50 dark:bg-indigo-950/30 flex items-center justify-center border border-indigo-100 dark:border-indigo-900 animate-bounce">
							<UploadCloud class="w-6 h-6 text-indigo-500" />
						</div>
						<div class="space-y-1">
							<h4 class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200">Uploading Avatar</h4>
							<p class="text-[10px] text-slate-500 dark:text-slate-400 font-bold animate-pulse">{{ getUploadStatusMessage(form.progress.percentage) }}</p>
							<p class="text-[9px] text-slate-450 dark:text-slate-500 font-semibold">{{ formatBytes((form.progress as any).loaded || 0) }} / {{ formatBytes((form.progress as any).total || 0) }}</p>
						</div>
						
						<Progress :value="form.progress.percentage" className="w-full max-w-[200px]" />
						
						<div class="text-[11px] font-black text-indigo-600 dark:text-indigo-455">
							{{ form.progress.percentage }}%
						</div>
					</div>
				</Transition>

				<div class="relative group select-none shrink-0 mx-auto">
					<div 
						@click="triggerFotoUpload"
						class="w-24 h-24 rounded-full border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800 flex items-center justify-center overflow-hidden cursor-pointer relative hover:ring-2 hover:ring-indigo-500/30 transition-all shadow-xs"
					>
						<img 
							v-if="photoPreview" 
							:src="photoPreview" 
							class="w-full h-full object-cover" 
							alt="Profile avatar preview"
						/>
						<div v-else class="text-2xl font-black text-slate-400 dark:text-slate-500">
							{{ user.name.charAt(0) }}
						</div>
						
						<div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
							<Camera class="w-5 h-5 text-white" />
						</div>
					</div>

					<!-- Photo Uploader Input -->
					<input 
						type="file" 
						ref="fotoInput" 
						class="hidden" 
						accept="image/*" 
						@change="handleFotoChange"
					/>
				</div>

				<div class="text-center space-y-1">
					<p class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider">Creator Avatar</p>
					<p class="text-[10px] text-slate-500 dark:text-slate-400 leading-relaxed max-w-md font-semibold">
						Click the circle to upload a new profile photo. Recommended: 400x400px (max 2MB).
					</p>
					<button 
						v-if="photoPreview" 
						type="button" 
						@click="removeProfilePhoto"
						class="text-[10px] font-black text-red-600 hover:underline flex items-center gap-1 mx-auto mt-2"
					>
						<X class="w-3.5 h-3.5" /> Remove Avatar
					</button>
				</div>
			</div>
			<template #footer>
				<button 
					type="button"
					@click="!form.processing && (showAvatarModal = false)"
					:disabled="form.processing"
					class="px-4 py-2 border border-slate-200 dark:border-slate-800 text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl cursor-pointer"
					:class="form.processing ? 'opacity-50 cursor-not-allowed' : ''"
				>
					Cancel
				</button>
				<button 
					type="button"
					@click="submitAvatar"
					:disabled="form.processing"
					class="px-5 py-2 bg-slate-900 dark:bg-white text-white dark:text-slate-950 text-xs font-black uppercase tracking-wider hover:bg-slate-800 dark:hover:bg-slate-100 rounded-xl cursor-pointer flex items-center gap-2"
				>
					<Loader2 v-if="form.processing" class="w-3.5 h-3.5 animate-spin" />
					Save avatar
				</button>
			</template>
		</Modal>

		<!-- 2. Banner/Featured Media Modal -->
		<Modal :show="showBannerModal" title="Update Cover / Featured Banner" maxWidth="2xl" @close="showBannerModal = false" :preventClose="form.processing || isConvertingVideo">
			<div class="relative min-h-[300px]">
				<!-- Uploading Overlay -->
				<Transition
					enter-active-class="transition duration-300 ease-out"
					enter-from-class="opacity-0 scale-98"
					enter-to-class="opacity-100 scale-100"
					leave-active-class="transition duration-200 ease-in"
					leave-from-class="opacity-100 scale-100"
					leave-to-class="opacity-0 scale-98"
				>
					<div v-if="form.processing && form.progress" class="absolute inset-0 bg-white/95 dark:bg-slate-900/95 z-50 flex flex-col items-center justify-center p-8 rounded-[20px] text-center space-y-6">
						<div class="w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-950/30 flex items-center justify-center border border-indigo-100 dark:border-indigo-900/50 animate-bounce">
							<UploadCloud class="w-8 h-8 text-indigo-500" />
						</div>
						<div class="space-y-2 max-w-md w-full">
							<h4 class="text-sm font-black uppercase tracking-wider text-slate-800 dark:text-slate-200">Uploading Featured Media</h4>
							<p class="text-xs text-slate-500 dark:text-slate-400 font-bold animate-pulse">
								{{ getUploadStatusMessage(form.progress.percentage) }}
							</p>
							<p class="text-[10px] text-slate-450 dark:text-slate-500 font-semibold">
								{{ formatBytes((form.progress as any).loaded || 0) }} of {{ formatBytes((form.progress as any).total || 0) }}
							</p>
						</div>
						
						<Progress :value="form.progress.percentage" className="w-full max-w-md" />
						
						<div class="text-base font-black text-indigo-600 dark:text-indigo-455">
							{{ form.progress.percentage }}% Complete
						</div>
					</div>
				</Transition>

			<!-- Converting Video Overlay (FFmpeg WASM) -->
			<Transition
				enter-active-class="transition duration-300 ease-out"
				enter-from-class="opacity-0 scale-98"
				enter-to-class="opacity-100 scale-100"
				leave-active-class="transition duration-200 ease-in"
				leave-from-class="opacity-100 scale-100"
				leave-to-class="opacity-0 scale-98"
			>
				<div v-if="isConvertingVideo" class="absolute inset-0 bg-white/95 dark:bg-slate-900/95 z-50 flex flex-col items-center justify-center p-8 rounded-[20px] text-center space-y-6">
					<div class="w-16 h-16 rounded-2xl bg-violet-50 dark:bg-violet-950/30 flex items-center justify-center border border-violet-100 dark:border-violet-900/50">
						<svg class="w-8 h-8 text-violet-500 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
							<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
							<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
						</svg>
					</div>
					<div class="space-y-2 max-w-md w-full">
						<h4 class="text-sm font-black uppercase tracking-wider text-slate-800 dark:text-slate-200">Converting to WebM</h4>
						<p class="text-xs text-slate-550 dark:text-slate-400 font-semibold truncate max-w-xs mx-auto mb-1">
							{{ originalFileName }} → WebM
						</p>
						<p class="text-xs text-violet-650 dark:text-violet-455 font-bold animate-pulse">
							{{ videoConvertProgress < 5 ? "Menginisialisasi konverter..." : (videoConvertProgress < 50 ? "Memproses kompresi video..." : "Menyelesaikan pembuatan WebM...") }}
						</p>
					</div>

					<div class="w-full max-w-xs space-y-2">
						<Progress :value="videoConvertProgress" className="w-full h-1.5" indicatorClassName="bg-violet-500" />
						<div v-if="videoConvertProgress > 0" class="text-xs font-bold text-violet-650 dark:text-violet-455">
							{{ videoConvertProgress }}% Selesai
						</div>
					</div>
				</div>
			</Transition>


				<!-- Tab header -->
				<div class="flex border-b border-slate-200 dark:border-slate-800 mb-6">
					<button 
						type="button"
						@click="activeBannerTab = 'Upload'"
						class="flex-1 pb-3 text-xs font-black uppercase tracking-wider transition-all relative cursor-pointer outline-hidden"
						:class="activeBannerTab === 'Upload' ? 'text-slate-900 dark:text-white' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200'"
					>
						Upload
						<div v-if="activeBannerTab === 'Upload'" class="absolute bottom-0 left-0 w-full h-0.5 bg-slate-900 dark:bg-white rounded-full"></div>
					</button>
					<button 
						type="button"
						@click="activeBannerTab = 'Presets'"
						class="flex-1 pb-3 text-xs font-black uppercase tracking-wider transition-all relative cursor-pointer outline-hidden"
						:class="activeBannerTab === 'Presets' ? 'text-slate-900 dark:text-white' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200'"
					>
						Presets
						<div v-if="activeBannerTab === 'Presets'" class="absolute bottom-0 left-0 w-full h-0.5 bg-slate-900 dark:bg-white rounded-full"></div>
					</button>
				</div>

				<!-- Upload Tab -->
				<div v-if="activeBannerTab === 'Upload'" class="space-y-6">
					<div 
						@click="triggerBannerUpload"
						@dragover.prevent="isDragging = true"
						@dragleave.prevent="isDragging = false"
						@drop.prevent="handleBannerDrop"
						class="w-full min-h-[200px] border-2 border-dashed border-slate-300 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 rounded-2xl relative overflow-hidden flex flex-col items-center justify-center cursor-pointer group hover:border-indigo-400 dark:hover:border-indigo-600 transition-colors p-6 text-center"
						:class="isDragging ? 'border-indigo-500 bg-indigo-50/10' : ''"
					>
						<!-- Icon -->
						<div class="w-12 h-12 rounded-xl bg-white dark:bg-slate-900 flex items-center justify-center border border-slate-200 dark:border-slate-800 mb-3 shadow-2xs group-hover:scale-105 transition-transform">
							<UploadCloud class="w-5 h-5 text-indigo-500" />
						</div>
						
						<!-- Text -->
						<h3 class="text-xs font-black text-slate-800 dark:text-white">Upload a new featured media</h3>
						<p class="text-[11px] font-semibold text-slate-500 mt-1">
							Drag and drop or <span class="text-slate-700 dark:text-slate-200 underline font-bold hover:text-indigo-600">browse</span>
						</p>
						<p class="text-[10px] text-slate-400 dark:text-slate-500 mt-4 leading-relaxed font-semibold">
							We recommend a video (mp4) or image (png, jpg, gif) in a 4:3, 5:4, 9:16, or 16:9 aspect ratio. Max 100MB.
						</p>
						<input 
							type="file" 
							ref="bannerInput" 
							class="hidden" 
							accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/ogg" 
							@change="handleBannerChange"
						/>
					</div>
					
					<!-- Upload Preview (if banner is selected/cropped) -->
					<div v-if="bannerPreview" class="relative rounded-2xl overflow-hidden aspect-[16/10] border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950">
						<video 
							v-if="originalFileType.startsWith('video/') || isVideoUrl(bannerPreview)"
							:src="bannerPreview"
							autoplay 
							loop 
							muted 
							playsinline 
							class="w-full h-full object-cover"
						></video>
						<img v-else :src="bannerPreview" class="w-full h-full object-cover" />
						
						<button 
							type="button" 
							@click="clearBannerSelection" 
							class="absolute top-3 right-3 w-8 h-8 rounded-full bg-slate-950/70 text-white flex items-center justify-center hover:bg-slate-900 transition-colors"
						>
							<X class="w-4 h-4" />
						</button>
					</div>
				</div>

				<!-- Presets Tab -->
				<div v-if="activeBannerTab === 'Presets'" class="space-y-4">
					<div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
						<div 
							v-for="(preset, idx) in presets" 
							:key="idx"
							@click="selectPreset(idx)"
							class="h-16 rounded-xl overflow-hidden border cursor-pointer relative group transition-all"
							:class="selectedPresetIndex === idx ? 'border-slate-900 dark:border-white ring-2 ring-indigo-500/20 scale-[1.02]' : 'border-slate-200 dark:border-slate-800/80 hover:border-slate-400 dark:hover:border-slate-700'"
						>
							<div class="absolute inset-0 w-full h-full [&>svg]:w-full [&>svg]:h-full [&>svg]:object-cover" v-html="preset.svgMini"></div>
							<div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
							<div class="absolute bottom-1.5 left-2.5 text-[9px] font-black text-white drop-shadow-xs select-none uppercase tracking-wider">
								{{ preset.name }}
							</div>
							
							<!-- Selected Checkbox Badge -->
							<div v-if="selectedPresetIndex === idx" class="absolute top-1.5 right-1.5 w-4.5 h-4.5 rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-950 flex items-center justify-center shadow-xs">
								<svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4">
									<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
								</svg>
							</div>
						</div>
					</div>
				</div>
			</div>

			<template #footer>
				<button 
					type="button"
					@click="!form.processing && !isConvertingVideo && (showBannerModal = false)"
					:disabled="form.processing || isConvertingVideo"
					class="px-4 py-2 border border-slate-200 dark:border-slate-800 text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl cursor-pointer"
					:class="(form.processing || isConvertingVideo) ? 'opacity-50 cursor-not-allowed' : ''"
				>
					Cancel
				</button>
				<button 
					type="button"
					@click="submitBanner"
					:disabled="form.processing || isConvertingVideo"
					class="px-5 py-2 bg-slate-900 dark:bg-white text-white dark:text-slate-950 text-xs font-black uppercase tracking-wider hover:bg-slate-800 dark:hover:bg-slate-100 rounded-xl cursor-pointer flex items-center gap-2"
				>
					<Loader2 v-if="form.processing" class="w-3.5 h-3.5 animate-spin" />
					<svg v-else-if="isConvertingVideo" class="w-3.5 h-3.5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
						<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
						<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
					</svg>
					{{ isConvertingVideo ? 'Converting...' : 'Save banner' }}
				</button>
			</template>
		</Modal>

		<EditDetailsModal :show="showLocationModal" :form="form" @close="showLocationModal = false" @submit="submitLocation" />

		<EditLocationModal :show="showLocationOnlyModal" :form="form" :userLocation="user.location" @close="showLocationOnlyModal = false" @submit="submitLocationOnly" />

		<EditSocialsModal :show="showSocialLinksModal" :form="form" @close="showSocialLinksModal = false" @submit="submitSocialLinks" />
		<EditBioModal :show="showBioModal" :form="form" @close="showBioModal = false" @submit="submitBio" />
		<EditUsernameModal :show="showUsernameModal" :form="form" :user="user" @close="showUsernameModal = false" @submit="submitUsername" />

		<CropImageModal :show="isCropperOpen" :imageSrc="cropperImageSrc" :initialAspectRatio="cropperAspectRatio" :originalFileName="originalFileName" :originalFileType="originalFileType" @close="closeCropper" @save="handleCropSave" />

		<!-- Premium Modern Warning Modal Alert -->
		<Modal :show="showWarningModal" :title="warningTitle" maxWidth="sm" @close="showWarningModal = false">
			<div class="flex flex-col items-center gap-4 text-center p-4">
				<div class="w-12 h-12 rounded-full bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900 flex items-center justify-center text-amber-600 dark:text-amber-400">
					<Info class="w-6 h-6 animate-pulse" />
				</div>
				<h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Warning</h3>
				<p class="text-xs text-slate-500 dark:text-slate-400 font-semibold leading-relaxed">
					{{ warningMessage }}
				</p>
			</div>
			<template #footer>
				<button 
					type="button"
					@click="showWarningModal = false"
					class="w-full h-11 bg-slate-900 dark:bg-white text-white dark:text-slate-950 text-xs font-black uppercase tracking-wider rounded-xl cursor-pointer shadow-xs hover:bg-slate-800 dark:hover:bg-slate-100"
				>
					Acknowledge
				</button>
			</template>
		</Modal>

		<!-- 8. Relations (Followers/Following) Modal -->
		<Modal :show="showRelationsModal" :title="relationsModalType === 'followers' ? 'Pengikut' : 'Mengikuti'" maxWidth="md" @close="showRelationsModal = false">
			<div class="space-y-4 my-2">
				<!-- Search box to filter users dynamically -->
				<div class="relative">
					<input 
						v-model="relationsSearchQuery" 
						type="text" 
						:placeholder="relationsModalType === 'followers' ? 'Cari pengikut...' : 'Cari akun yang diikuti...'"
						class="w-full h-11 pl-10 pr-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 transition-all shadow-2xs"
					/>
					<svg class="absolute left-3.5 top-3.5 w-4 h-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
						<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
					</svg>
				</div>

				<!-- Loader -->
				<div v-if="isLoadingRelations" class="flex flex-col items-center justify-center py-12 gap-3">
					<Loader2 class="w-8 h-8 text-indigo-600 dark:text-indigo-400 animate-spin" />
					<span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Memuat data...</span>
				</div>

				<!-- Content List -->
				<div v-else class="max-h-[380px] overflow-y-auto pr-1 space-y-3 scrollbar-thin scrollbar-thumb-slate-200">
					<div v-if="filteredRelations.length === 0" class="flex flex-col items-center justify-center py-12 text-center text-slate-400 dark:text-slate-500 gap-3">
						<svg class="w-12 h-12 text-slate-300 dark:text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
							<path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
						</svg>
						<p class="text-xs font-bold uppercase tracking-wider">
							{{ relationsModalType === 'followers' ? 'Tidak ada pengikut yang cocok' : 'Tidak ada akun diikuti yang cocok' }}
						</p>
					</div>

					<div 
						v-else 
						v-for="rel in filteredRelations" 
						:key="rel.id" 
						class="flex items-center justify-between gap-3 p-2.5 rounded-2xl border border-slate-105 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-all duration-200"
					>
						<!-- User profile block -->
						<div class="flex items-center gap-3 min-w-0 flex-1">
							<Link 
								:href="rel.pagi_username ? '/pagi/' + rel.pagi_username : '/pagi/profile/' + rel.id" 
								@click="showRelationsModal = false"
								class="w-10 h-10 rounded-full border border-slate-200 dark:border-slate-800 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900 overflow-hidden flex items-center justify-center shrink-0 shadow-3xs cursor-pointer hover:scale-102 transition-transform"
							>
								<OptimizedImage v-if="rel.foto_path" :src="rel.foto_path.startsWith('http') ? rel.foto_path : '/storage/' + rel.foto_path" alt="Avatar" className="w-full h-full object-cover" />
								<div v-else class="w-full h-full bg-indigo-50 dark:bg-indigo-950/40 flex items-center justify-center">
									<span class="text-xs font-bold text-indigo-500 dark:text-indigo-400">{{ rel.name.charAt(0).toUpperCase() }}</span>
								</div>
							</Link>

							<div class="min-w-0 flex-1">
								<div class="flex items-center gap-1.5">
									<Link 
										:href="rel.pagi_username ? '/pagi/' + rel.pagi_username : '/pagi/profile/' + rel.id" 
										@click="showRelationsModal = false"
										class="text-xs font-black text-slate-800 dark:text-white truncate hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
									>
										{{ rel.name }}
									</Link>
									<img src="/premium.svg" class="w-3.5 h-3.5 shrink-0 select-none" title="Premium Account" alt="Verified Badge" />
								</div>
								<p class="text-[10px] font-semibold text-slate-400 dark:text-slate-505 truncate mt-0.5">
									{{ rel.role_title || 'Top Creator' }}
								</p>
							</div>
						</div>

						<!-- Action Button (Follow / Follback) - only show if NOT the logged in user themselves -->
						<div v-if="page.props.auth?.user && page.props.auth.user.id !== rel.id" class="shrink-0">
							<button 
								@click="toggleFollowRelation(rel)" 
								:disabled="FollbackInProgress[rel.id]"
								class="h-8 px-4 rounded-full font-black text-[10px] uppercase tracking-wider cursor-pointer shadow-3xs active:scale-95 transition-all duration-200 flex items-center justify-center gap-1 disabled:opacity-50"
								:class="isFollowingBack(rel.id)
									? 'bg-slate-200 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-300/40 dark:border-slate-700/40' 
									: 'bg-indigo-600 hover:bg-indigo-700 text-white'"
							>
								<Loader2 v-if="FollbackInProgress[rel.id]" class="w-3 h-3 animate-spin mr-1" />
								<span>{{ isFollowingBack(rel.id) ? 'Mengikuti' : 'Ikuti' }}</span>
							</button>
						</div>
					</div>
				</div>
			</div>
		</Modal>

		<!-- 9. Feature Work (Quick Add Work) Modal -->
		<AddWorkModal :show="showAddWorkModal" :isEditingQuickWork="isEditingQuickWork" :editingQuickWorkId="editingQuickWorkId" :editingProject="editingProject" :user="user" @close="showAddWorkModal = false" @success="handleQuickStoreSuccess" @warning="triggerWarning" @toast="addToast" />

		<!-- 10. Success Share Modal ("Share your work") — standalone component -->
		<ShareWorkModal
			:show="showShareModal"
			:project="newCreatedProject"
			:user="user"
			:shareUrl="getProjectShareUrl(newCreatedProject)"
			@close="showShareModal = false"
			@shareToFeed="addToast('Karya dibagikan ke umpan Anda!', 'success')"
		/>

		<!-- 11. Profile Share Modal (Bagikan Profil Keren) -->
		<Modal :show="showProfileShareModal" maxWidth="sm" @close="showProfileShareModal = false">
			<div class="relative p-3 text-center flex flex-col items-center overflow-hidden">
				<!-- Colorful Gradient Glow Backdrop Aura -->
				<div v-once class="absolute -top-14 left-1/2 -translate-x-1/2 w-64 h-32 bg-gradient-to-r from-indigo-200/40 via-purple-300/30 to-pink-300/40 dark:from-indigo-500/10 dark:via-purple-600/10 dark:to-pink-600/10 rounded-full blur-2xl pointer-events-none"></div>

				<!-- Elegant Close Button -->
				<button 
					type="button"
					@click="showProfileShareModal = false" 
					class="absolute top-2 right-2 w-8 h-8 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 flex items-center justify-center cursor-pointer bg-transparent border-none transition-colors z-10"
					aria-label="Close modal"
				>
					<X class="w-5 h-5" />
				</button>

				<!-- Glassmorphic Virtual Business Card Preview -->
				<div class="w-full rounded-2xl border border-slate-200/60 dark:border-slate-800/80 bg-white/70 dark:bg-slate-900/40 backdrop-blur-md flex flex-col items-center p-5 text-center mb-6 shadow-3xs z-5 relative overflow-hidden">


					<!-- Avatar -->
					<div class="w-18 h-18 rounded-full border-4 border-white dark:border-slate-900 bg-slate-100 dark:bg-slate-800 overflow-hidden flex items-center justify-center shrink-0 mt-3 relative z-10 shadow-xs">
						<OptimizedImage v-if="user.foto_path" :src="user.foto_path.startsWith('http') ? user.foto_path : '/storage/' + user.foto_path" alt="Avatar" className="w-full h-full object-cover" />
						<span v-else class="text-indigo-600 dark:text-indigo-400 font-extrabold text-lg">{{ user.name.charAt(0).toUpperCase() }}</span>
					</div>

					<!-- User Info -->
					<div class="mt-3 relative z-10">
						<h4 class="text-sm font-black text-slate-800 dark:text-white flex items-center justify-center gap-1">
							{{ user.name }}
							<img src="/premium.svg" class="w-4 h-4 select-none shrink-0" alt="Verified" />
						</h4>
						<p class="text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mt-0.5">{{ displayRoleName }}</p>
						<p v-if="user.pagi_username" class="text-[11px] font-bold text-slate-500 dark:text-slate-400 mt-1">@{{ user.pagi_username }}</p>
					</div>

					<!-- QR Code rendered on canvas (no external API) -->
					<div class="mt-5 p-3 bg-white rounded-2xl border border-slate-150 shadow-sm shrink-0 flex items-center justify-center">
						<canvas ref="qrCanvas" class="w-[160px] h-[160px] select-none rounded-lg" />
					</div>
					<p class="text-[8px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-2">Pindai untuk melihat profil</p>
				</div>

				<h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight mb-1 z-5">Bagikan Profil Kreatif</h3>
				<p class="text-xs font-semibold text-slate-400 dark:text-slate-500 max-w-[260px] leading-relaxed mb-6 z-5">
					Ajak komunitas Anda menjelajahi karya, keahlian, dan studi kasus Anda.
				</p>

				<!-- Quick Share Options Grid -->
				<div class="grid grid-cols-5 gap-3 w-full z-5 px-2">
					<!-- Salin Tautan -->
					<button 
						type="button"
						@click="copyProfileLink"
						class="flex flex-col items-center gap-1.5 bg-transparent border-none cursor-pointer group"
					>
						<div class="w-10 h-10 rounded-full border border-slate-200 dark:border-slate-800 bg-white hover:bg-slate-50 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-355 flex items-center justify-center shadow-3xs group-hover:scale-105 transition-all">
							<Link2 class="w-4 h-4" />
						</div>
						<span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">Salin</span>
					</button>

					<!-- WhatsApp -->
					<a 
						:href="'https://api.whatsapp.com/send?text=' + encodeURIComponent('Lihat portofolio kreatif saya di FMIKOM Portal: ' + activeShareUrl)"
						target="_blank"
						@click="showProfileShareModal = false"
						class="flex flex-col items-center gap-1.5 no-underline group"
					>
						<div class="w-10 h-10 rounded-full border border-emerald-200/50 bg-[#e8f5e9]/50 hover:bg-[#c8e6c9]/50 dark:border-emerald-900/30 dark:bg-[#1b5e20]/20 dark:hover:bg-[#1b5e20]/40 text-emerald-600 dark:text-emerald-450 flex items-center justify-center shadow-3xs group-hover:scale-105 transition-all">
							<svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
								<path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.003 5.324 5.328 0 11.896 0c3.181.001 6.173 1.24 8.424 3.492 2.25 2.253 3.487 5.244 3.487 8.423 0 6.578-5.323 11.902-11.89 11.902-2.003 0-3.974-.505-5.724-1.468L0 24zm6.54-5.3c1.666.989 3.32 1.488 5.304 1.488 5.485 0 9.948-4.468 9.95-9.95.002-2.656-1.03-5.153-2.906-7.03C17.068 1.332 14.576.3 11.92.3c-5.485 0-9.95 4.467-9.953 9.95 0 1.996.505 3.655 1.5 5.313L2.3 21.7l6.297-1.652z" />
							</svg>
						</div>
						<span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">WhatsApp</span>
					</a>

					<!-- Telegram -->
					<a 
						:href="'https://t.me/share/url?url=' + encodeURIComponent(activeShareUrl) + '&text=' + encodeURIComponent('Lihat portofolio kreatif saya di FMIKOM Portal')"
						target="_blank"
						@click="showProfileShareModal = false"
						class="flex flex-col items-center gap-1.5 no-underline group"
					>
						<div class="w-10 h-10 rounded-full border border-sky-200/50 bg-[#e1f5fe]/50 hover:bg-[#b3e5fc]/50 dark:border-sky-900/30 dark:bg-[#01579b]/20 dark:hover:bg-[#01579b]/40 text-sky-600 dark:text-sky-450 flex items-center justify-center shadow-3xs group-hover:scale-105 transition-all">
							<svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
								<path d="M9.417 15.181l-.397 5.584c.568 0 .814-.244 1.109-.537l2.663-2.545 5.518 4.041c1.012.564 1.725.267 1.998-.931l3.622-16.972.001-.001c.321-1.496-.541-2.081-1.527-1.714l-21.29 8.151c-1.453.564-1.431 1.374-.247 1.741l5.475 1.71 12.74-7.977c.599-.396 1.147-.183.699.213z" />
							</svg>
						</div>
						<span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">Telegram</span>
					</a>

					<!-- LinkedIn -->
					<a 
						:href="'https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent(activeShareUrl)"
						target="_blank"
						@click="showProfileShareModal = false"
						class="flex flex-col items-center gap-1.5 no-underline group"
					>
						<div class="w-10 h-10 rounded-full border border-blue-200/50 bg-[#e8f4fd]/50 hover:bg-[#d0e8fc]/50 dark:border-blue-900/30 dark:bg-[#0b3c5d]/20 dark:hover:bg-[#0b3c5d]/40 text-[#0077b5] dark:text-[#32b0f5] flex items-center justify-center shadow-3xs group-hover:scale-105 transition-all">
							<Linkedin class="w-4 h-4" />
						</div>
						<span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">LinkedIn</span>
					</a>

					<!-- Unduh QR -->
					<button 
						type="button"
						@click="downloadQrCode"
						class="flex flex-col items-center gap-1.5 bg-transparent border-none cursor-pointer group"
					>
						<div class="w-10 h-10 rounded-full border border-slate-200 dark:border-slate-800 bg-white hover:bg-slate-50 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shadow-3xs group-hover:scale-105 transition-all">
							<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
							</svg>
						</div>
						<span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">QR Code</span>
					</button>
				</div>
			</div>
		</Modal>
	</div>
</template>

<style scoped>
/* Custom animations & deep styles */
.editor-content :deep(h1) { font-size: 2.25rem; font-weight: 800; line-height: 1.2; margin: 1rem 0; }
.editor-content :deep(h2) { font-size: 1.5rem; font-weight: 700; line-height: 1.3; margin: 0.875rem 0; }
.editor-content :deep(p) { margin: 0.75rem 0; font-size: 1.125rem; line-height: 1.8; }
.editor-content :deep(blockquote) { border-left: 4px solid #e2e8f0; padding-left: 1rem; color: #64748b; font-style: italic; margin: 1rem 0; }
.editor-content :deep(a) { color: inherit; text-decoration: underline; text-decoration-color: #64748b; }
.editor-content :deep(ul) { list-style-type: disc; padding-left: 1.5rem; margin: 0.75rem 0; }
.editor-content :deep(ol) { list-style-type: decimal; padding-left: 1.5rem; margin: 0.75rem 0; }

/* Upload Progress Bar Animations */
@keyframes progressbar-stripes {
  from { background-position: 1rem 0; }
  to { background-position: 0 0; }
}

.progress-bar-animated-stripes {
  background-image: linear-gradient(
    45deg,
    rgba(255, 255, 255, 0.15) 25%,
    transparent 25%,
    transparent 50%,
    rgba(255, 255, 255, 0.15) 50%,
    rgba(255, 255, 255, 0.15) 75%,
    transparent 75%,
    transparent
  );
  background-size: 1rem 1rem;
  animation: progressbar-stripes 1s linear infinite;
}

@keyframes shimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

.animate-shimmer {
  animation: shimmer 2s infinite linear;
}
</style>
