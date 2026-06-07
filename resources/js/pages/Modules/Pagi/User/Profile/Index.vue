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
import EditAvatarModal from "./components/EditAvatarModal.vue";
import EditBannerModal from "./components/EditBannerModal.vue";
import RelationsModal from "./components/RelationsModal.vue";
import ShareProfileModal from "./components/ShareProfileModal.vue";


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

// Cropping references
const isCropperOpen = ref(false);
const cropperImageSrc = ref("");
const cropperAspectRatio = ref<number | string>(3200 / 410);
let originalFileName = "banner.jpg";
const originalFileType = ref("image/jpeg");

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
	showAvatarModal.value = true;
};

const openBannerModal = () => {
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

const generateShareToken = () => {
	const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	let result = "";
	for (let i = 0; i < 12; i++) {
		result += chars.charAt(Math.floor(Math.random() * chars.length));
	}
	return btoa(result).replace(/=/g, "").substring(0, 14);
};

const shareProfile = () => {
	const username = user.value.pagi_username;
	const baseUrl = username 
		? `${window.location.origin}/pagi/${username}`
		: `${window.location.origin}/pagi/profile/${user.value.id}`;
	const token = generateShareToken();
	activeShareUrl.value = `${baseUrl}?pagi_share=${token}`;
	showProfileShareModal.value = true;
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

const openRelationsModal = (type: 'followers' | 'following') => {
	relationsModalType.value = type;
	showRelationsModal.value = true;
};

const updateFollowingCount = (following: boolean) => {
	if (props.profileUser) {
		if (following) {
			props.profileUser.following_count = (props.profileUser.following_count ?? 0) + 1;
		} else {
			props.profileUser.following_count = Math.max(0, (props.profileUser.following_count ?? 1) - 1);
		}
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




const handleCropSave = (croppedFile: File) => {
	form.banner = croppedFile;
	closeCropper();
};

const handleTriggerCrop = (data: { src: string, name: string, type: string }) => {
	cropperImageSrc.value = data.src;
	originalFileName = data.name;
	originalFileType.value = data.type;
	isCropperOpen.value = true;
};

const closeCropper = () => {
	isCropperOpen.value = false;
	cropperImageSrc.value = "";
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

		<!-- MODALS SECTION		<!-- 1. Avatar Uploader Modal -->
		<EditAvatarModal :show="showAvatarModal" :user="user" :form="form" @close="showAvatarModal = false" @submit="submitAvatar" @warning="triggerWarning" />

		<!-- 2. Banner/Featured Media Modal -->
		<EditBannerModal :show="showBannerModal" :user="user" :form="form" @close="showBannerModal = false" @submit="submitBanner" @warning="triggerWarning" @toast="addToast" @trigger-crop="handleTriggerCrop" />

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
		<RelationsModal :show="showRelationsModal" :type="relationsModalType" :userId="user.id" :isOwnProfile="isOwnProfile" @close="showRelationsModal = false" @toast="addToast" @following-count-changed="updateFollowingCount" />

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
		<ShareProfileModal :show="showProfileShareModal" :user="user" :displayRoleName="displayRoleName" :activeShareUrl="activeShareUrl" @close="showProfileShareModal = false" @toast="addToast" />
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
