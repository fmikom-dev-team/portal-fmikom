<script setup lang="ts">
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { AlertCircle, CheckCircle2, X } from "lucide-vue-next";
import { computed, defineAsyncComponent, onMounted, ref, watch } from "vue";
import Navbar from "../ui/Navbar.vue";
import UmumNavbar from "../ui/UmumNavbar.vue";

const WorkTab = defineAsyncComponent(() => import("./WorkTab.vue"));
const EducationalTab = defineAsyncComponent(
	() => import("./EducationalTab.vue"),
);
const GalleryTab = defineAsyncComponent(() => import("./GalleryTab.vue"));
const SertifikatTab = defineAsyncComponent(() => import("./SertifikatTab.vue"));
const AboutTab = defineAsyncComponent(() => import("./AboutTab.vue"));

import { useToast } from "../../shared/composables/useToast";

const PagiShareModal = defineAsyncComponent(
	() => import("../ui/PagiShareModal.vue"),
);
const Preview = defineAsyncComponent(() => import("../ui/Preview.vue"));
const AddWorkModal = defineAsyncComponent(
	() => import("./components/AddWorkModal.vue"),
);
const CropImageModal = defineAsyncComponent(
	() => import("./components/CropImageModal.vue"),
);
const EditAvatarModal = defineAsyncComponent(
	() => import("./components/EditAvatarModal.vue"),
);
const EditBannerModal = defineAsyncComponent(
	() => import("./components/EditBannerModal.vue"),
);
const EditBioModal = defineAsyncComponent(
	() => import("./components/EditBioModal.vue"),
);
const EditDetailsModal = defineAsyncComponent(
	() => import("./components/EditDetailsModal.vue"),
);
const EditLocationModal = defineAsyncComponent(
	() => import("./components/EditLocationModal.vue"),
);
const EditSocialsModal = defineAsyncComponent(
	() => import("./components/EditSocialsModal.vue"),
);
const EditUsernameModal = defineAsyncComponent(
	() => import("./components/EditUsernameModal.vue"),
);

import ProfileHeader from "./components/ProfileHeader.vue";
import ProfileTabs from "./components/ProfileTabs.vue";

const RelationsModal = defineAsyncComponent(
	() => import("./components/RelationsModal.vue"),
);
const WarningModal = defineAsyncComponent(
	() => import("./components/WarningModal.vue"),
);

import { useProfileFollow } from "./composables/useProfileFollow";
// Composables
import { useProfileForm } from "./composables/useProfileForm";
import { useProfileProjects } from "./composables/useProfileProjects";
import { useProfileTabs } from "./composables/useProfileTabs";

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

const tabs = ["Work", "Educational", "Gallery", "Certificates", "About"];
const page = usePage();
const user = computed(
	() =>
		props.profileUser ||
		page.props.auth?.user || {
			name: "User",
			email: "",
			role_title: "",
			bio: "",
			location: "",
			foto_path: "",
			banner_path: "",
			website: "",
			linkedin: "",
			github: "",
			twitter: "",
			instagram: "",
			tanggal_lahir: "",
		},
);

const isOwnProfile = computed(() => {
	if (!page.props.auth?.user) return false;
	return page.props.auth.user.id === user.value.id;
});

const isMahasiswa = computed(() => {
	const role =
		props.roleName || (page.props as any).context?.active_role || "mahasiswa";
	return role.toLowerCase() === "mahasiswa";
});

const displayRoleName = computed(() => {
	const role =
		props.roleName ||
		(page.props as any).context?.active_role ||
		(page.props.roleName as string) ||
		"Mahasiswa";
	const r = role.toLowerCase();
	if (r === "mahasiswa") return "Mahasiswa";
	if (r === "super-admin" || r === "super_admin") return "Super Admin";
	if (r === "dosen") return "Dosen";
	if (r === "alumni") return "Alumni";
	if (r === "mitra") return "Mitra Perusahaan";
	if (r === "guest") return "Tamu";
	return role.charAt(0).toUpperCase() + role.slice(1);
});

// Toast Notification System
const { toasts, addToast, removeToast } = useToast();

// Warning modal states
const showWarningModal = ref(false);
const warningTitle = ref("");
const warningMessage = ref("");

const triggerWarning = (title: string, message: string) => {
	warningTitle.value = title;
	warningMessage.value = message;
	showWarningModal.value = true;
};

// Instantiate Form Composable
const {
	form,
	showLocationModal,
	showLocationOnlyModal,
	showSocialLinksModal,
	showBioModal,
	showAvatarModal,
	showBannerModal,
	showUsernameModal,
	isCropperOpen,
	cropperImageSrc,
	cropperAspectRatio,
	originalFileName,
	originalFileType,
	initFormValues,
	submitLocation,
	submitLocationOnly,
	submitSocialLinks,
	submitBio,
	submitAvatar,
	submitBanner,
	submitUsername,
	updateSkills,
	updateBio,
	updateLocation,
	updateTimezone,
	updateLanguages,
	updateSocials,
	handleCropSave,
	handleTriggerCrop,
	closeCropper,
} = useProfileForm(user, props, addToast, triggerWarning);

// Instantiate Projects Composable
const {
	localProjects,
	projects: composableProjects,
	showAddWorkModal,
	showShareModal,
	newCreatedProject,
	activeProjectMenu,
	toggleProjectMenu,
	cloneProject,
	shareProject,
	getProjectShareUrl,
	deleteProject,
	viewingProject,
	activeProjectSettings,
	openProjectModal,
	closeProjectModal,
	editingQuickWorkId,
	isEditingQuickWork,
	editingProject,
	openAddWorkModal,
	openEditQuickWorkModal,
	handleQuickStoreSuccess,
	handleLikeUpdated,
	handleGalleryItemUpdated,
	totalViews,
	totalLikes,
	projectCount,
} = useProfileProjects(props, page, addToast, triggerWarning);

// Tab Navigation logic
const { activeTab } = useProfileTabs();

const selectWorkTab = () => {
	activeTab.value = "Work";
	const el = document.getElementById("profile_tabs_navigation");
	if (el) {
		el.scrollIntoView({ behavior: "smooth" });
	}
};

const certificates = ref<any[]>([...(props.profileUser?.certificates || [])]);
watch(
	() => props.profileUser?.certificates,
	(newVal) => {
		certificates.value = [...(newVal || [])];
	},
	{ deep: true },
);

const educations = ref<any[]>([...(props.profileUser?.educations || [])]);
watch(
	() => props.profileUser?.educations,
	(newVal) => {
		educations.value = [...(newVal || [])];
	},
	{ deep: true },
);

// Form opening handlers
const openLocationModal = () => {
	initFormValues();
	showLocationModal.value = true;
};

const openLocationOnlyModal = () => {
	initFormValues();
	showLocationOnlyModal.value = true;
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

const openAvatarModal = () => {
	showAvatarModal.value = true;
};

const openBannerModal = () => {
	showBannerModal.value = true;
};

const isLoading = ref(false);
const isMessageEnabled = ref(true);

const {
	followingState,
	isFollowLoading,
	showUnfollowModal,
	realFollowersCount,
	dynamicFollowersCount,
	toggleFollow,
	requestUnfollow,
	confirmUnfollow,
	cancelUnfollow,
} = useProfileFollow(props, user, isOwnProfile, page, addToast);

onMounted(() => {
	initFormValues();
	isMessageEnabled.value = user.value.metadata?.is_message_enabled ?? true;

	const urlParams = new URLSearchParams(globalThis.window.location.search);
	const projectId = urlParams.get("project") || urlParams.get("portfolio");
	if (projectId) {
		const proj = props.projects?.find(
			(p: any) => String(p.id) === String(projectId),
		);
		if (proj) {
			openProjectModal(proj);
		}
	}

	const editParam = urlParams.get("edit");
	if (editParam === "username") {
		openUsernameModal();
	} else if (editParam === "avatar") {
		openAvatarModal();
	} else if (editParam === "bio") {
		openBioModal();
	} else if (editParam === "location") {
		openLocationModal();
	} else if (editParam === "socials") {
		openSocialLinksModal();
	} else if (editParam === "project") {
		openAddWorkModal();
	}
});

// Message Switch toggle
const toggleMessageSwitch = (e: Event) => {
	e.stopPropagation();
	isMessageEnabled.value = !isMessageEnabled.value;
	router.post(
		"/pagi/profile/update",
		{
			is_message_enabled: isMessageEnabled.value,
		},
		{
			preserveScroll: true,
			onSuccess: () => {
				if (isMessageEnabled.value) {
					addToast("Direct messaging has been enabled.", "success");
				} else {
					addToast("Direct messaging has been disabled.", "info");
				}
			},
		},
	);
};

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
	const chars =
		"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	let result = "";
	for (let i = 0; i < 12; i++) {
		result += chars.charAt(Math.floor(Math.random() * chars.length));
	}
	return btoa(result).replaceAll("=", "").substring(0, 14);
};

const shareProfile = () => {
	const username = user.value.pagi_username;
	const baseUrl = username
		? `${globalThis.window.location.origin}/pagi/${username}`
		: `${globalThis.window.location.origin}/pagi/profile/${user.value.id}`;
	const token = generateShareToken();
	activeShareUrl.value = `${baseUrl}?pagi_share=${token}`;
	showProfileShareModal.value = true;
};

const showRelationsModal = ref(false);
const relationsModalType = ref<"followers" | "following">("followers");

const openRelationsModal = (type: "followers" | "following") => {
	relationsModalType.value = type;
	showRelationsModal.value = true;
};

const updateFollowingCount = (following: boolean) => {
	if (props.profileUser) {
		if (following) {
			props.profileUser.following_count =
				(props.profileUser.following_count ?? 0) + 1;
		} else {
			props.profileUser.following_count = Math.max(
				0,
				(props.profileUser.following_count ?? 1) - 1,
			);
		}
	}
};

// Followers & Following Count
const dynamicFollowingCount = computed(() => {
	return (
		props.profileUser?.following_count ??
		user.value.metadata?.following?.length ??
		0
	);
});

const displayOwnerRoleName = computed(() => {
	if (user.value.pagi_role) return user.value.pagi_role;
	if (user.value.role_title) return user.value.role_title;
	if (user.value.user_type) {
		const type = user.value.user_type.toLowerCase();
		if (type === "mahasiswa") return "Mahasiswa";
		if (type === "super_admin" || type === "super-admin") return "Super Admin";
		if (type === "dosen") return "Dosen";
		if (type === "alumni") return "Alumni";
		if (type === "mitra") return "Mitra Perusahaan";
		return (
			user.value.user_type.charAt(0).toUpperCase() +
			user.value.user_type.slice(1)
		);
	}
	return "Anggota PAGI";
});

const socialLinks = computed(() => {
	const links = [];
	if (user.value.website)
		links.push({
			type: "website",
			url: user.value.website,
			label: "Website",
		});
	if (user.value.linkedin)
		links.push({
			type: "linkedin",
			url: user.value.linkedin.startsWith("http")
				? user.value.linkedin
				: `https://linkedin.com/in/${user.value.linkedin}`,
			label: "LinkedIn",
		});
	if (user.value.github)
		links.push({
			type: "github",
			url: user.value.github.startsWith("http")
				? user.value.github
				: `https://github.com/${user.value.github}`,
			label: "GitHub",
		});
	if (user.value.twitter)
		links.push({
			type: "twitter",
			url: user.value.twitter.startsWith("http")
				? user.value.twitter
				: `https://twitter.com/${user.value.twitter}`,
			label: "Twitter",
		});
	if (user.value.instagram)
		links.push({
			type: "instagram",
			url: user.value.instagram.startsWith("http")
				? user.value.instagram
				: `https://instagram.com/${user.value.instagram}`,
			label: "Instagram",
		});
	return links;
});

// About Tab Details
const parseSkills = (
	skillsArray: any[],
): Array<{ name: string; percentage: number }> => {
	if (!Array.isArray(skillsArray)) return [];
	return skillsArray.map((item) => {
		if (typeof item === "string") {
			const parts = item.split(":");
			if (parts.length === 2 && !Number.isNaN(Number(parts[1]))) {
				return { name: parts[0], percentage: Number(parts[1]) };
			}
			return { name: item, percentage: 80 };
		}
		if (item && typeof item === "object" && item.name) {
			return {
				name: item.name,
				percentage: Number(item.percentage) || 80,
			};
		}
		return { name: String(item), percentage: 80 };
	});
};

const skills = computed(() => {
	const val =
		user.value.skills ||
		user.value.metadata?.skills ||
		props.profileUser?.skills;
	return Array.isArray(val)
		? parseSkills(val)
		: parseSkills(["Figma", "UI/UX Design", "Vue.js"]);
});

const timezone = computed(() => {
	return (
		user.value.timezone ||
		user.value.metadata?.timezone ||
		props.profileUser?.timezone ||
		""
	);
});

const timezoneExtended = computed(() => {
	return (
		user.value.timezone_extended ||
		user.value.timezoneExtended ||
		user.value.metadata?.timezone_extended ||
		user.value.metadata?.timezoneExtended ||
		props.profileUser?.timezone_extended ||
		props.profileUser?.timezoneExtended ||
		"No extended hours"
	);
});

const languages = computed(() => {
	const langs =
		user.value.languages ||
		user.value.metadata?.languages ||
		props.profileUser?.languages;
	return Array.isArray(langs) ? langs : [];
});

const computedProfileImage = computed(() => {
	if (!user.value.foto_path) return "";
	if (user.value.foto_path.startsWith("http")) return user.value.foto_path;
	const origin =
		typeof globalThis.window === "undefined"
			? ""
			: globalThis.window.location.origin;
	return `${origin}/storage/${user.value.foto_path}`;
});

// JSON-LD Structured Data
const jsonLdString = computed(() => {
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
		name: user.value.name,
		jobTitle: user.value.role_title || displayRoleName.value,
		description: user.value.bio || "",
		image: computedProfileImage.value,
		url: user.value.pagi_username
			? `${typeof globalThis.window !== "undefined" ? globalThis.window.location.origin : ""}/pagi/${user.value.pagi_username}`
			: `${typeof globalThis.window !== "undefined" ? globalThis.window.location.origin : ""}/pagi/profile/${user.value.id}`,
		sameAs: sameAs,
	});
});

const headTitle = computed(() => {
	if (viewingProject.value) {
		return `${viewingProject.value.title} by ${user.value.name} — PAGI Portfolio`;
	}
	return `${user.value.name} — ${props.moduleName || "PAGI"} Profile`;
});

const headDescription = computed(() => {
	if (viewingProject.value) {
		if (viewingProject.value.description) {
			const cleanDesc = viewingProject.value.description.replace(
				/<[^>]*>/g,
				"",
			);
			return cleanDesc.length > 160
				? `${cleanDesc.slice(0, 157)}...`
				: cleanDesc;
		}
		return `Lihat karya "${viewingProject.value.title}" oleh ${user.value.name} di FMIKOM Portal.`;
	}
	return (
		user.value.bio ||
		"FMIKOM Portal profile page. Hubungkan, kolaborasi, dan eksplorasi karya kreatif mahasiswa."
	);
});

const headImage = computed(() => {
	if (viewingProject.value?.image) {
		return viewingProject.value.image;
	}
	if (!user.value.foto_path) return "/og-image.png";
	return user.value.foto_path.startsWith("http")
		? user.value.foto_path
		: `/storage/${user.value.foto_path}`;
});

const headType = computed(() => {
	return viewingProject.value ? "article" : "profile";
});

const headUrl = computed(() => {
	const base = user.value.pagi_username
		? `/pagi/${user.value.pagi_username}`
		: `/pagi/profile/${user.value.id}`;
	if (viewingProject.value) {
		return `${globalThis.window.location.origin}${base}?project=${viewingProject.value.id}`;
	}
	return `${globalThis.window.location.origin}${base}`;
});
</script>

<template>
    <Head :title="headTitle">
        <title>{{ headTitle }}</title>
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
        <!-- <script type="application/ld+json" v-html="jsonLdString"></script> -->
        <component
            :is="'script'"
            type="application/ld+json"
            v-text="jsonLdString"
        />
    </Head>

	<div class="min-h-screen bg-slate-100 dark:bg-slate-950 font-sans text-slate-900 dark:text-slate-100 selection:bg-slate-900 selection:text-white dark:selection:bg-white dark:selection:text-slate-900" style="font-family:'Inter',system-ui,sans-serif;">
		
		<Navbar v-if="isMahasiswa" :roleName="displayRoleName" />
		<UmumNavbar v-else :roleName="displayRoleName" />
		<!-- Outer page padding to create the floating layer gap ("ngambang" effect) -->
		<div class="mx-auto max-w-[1480px] px-1.5 sm:px-5 lg:px-6 pt-3 sm:pt-5 pb-0">
					<!-- Inner Floating Card Layer ("Layout Lapisan") -->
			<main class="bg-white dark:bg-slate-900 border-x border-t border-slate-200/60 dark:border-slate-800 rounded-t-[24px] rounded-b-none pt-5 pb-32 px-3.5 sm:p-8 md:p-10 shadow-[0_15px_45px_-10px_rgba(15,23,42,0.08)] dark:shadow-[0_20px_50px_-15px_rgba(0,0,0,0.6)] relative">
				<ProfileHeader
					:isLoading="isLoading"
					:isOwnProfile="isOwnProfile"
					:isFollowing="followingState"
					:isMessageEnabled="isMessageEnabled"
					:user="user"
					:displayRoleName="displayRoleName"
					:displayOwnerRoleName="displayOwnerRoleName"
					:projectCount="projectCount"
					:totalLikes="totalLikes"
					:dynamicFollowersCount="dynamicFollowersCount"
					:dynamicFollowingCount="dynamicFollowingCount"
					:socialLinks="socialLinks"
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
					@request-unfollow="requestUnfollow"
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
				<Transition name="fade-slide" mode="out-in">
					<div :key="activeTab">
						<WorkTab 
							v-if="activeTab === 'Work'"
							:projects="composableProjects"
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
						<EducationalTab 
							v-else-if="activeTab === 'Educational'"
							:educations="educations"
							:isOwnProfile="isOwnProfile"
							:isLoading="isLoading"
							@add-toast="addToast"
							@update-educations="(list) => educations = list"
						/>
						<GalleryTab 
							v-else-if="activeTab === 'Gallery'"
							:projects="composableProjects"
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
							:isFollowing="followingState"
							:isMessageEnabled="isMessageEnabled"
							:dynamicFollowersCount="dynamicFollowersCount"
							:dynamicFollowingCount="dynamicFollowingCount"
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
				</Transition>
			</main>
		</div>

		<!-- TOAST ALERTS CONTAINER -->
		<div class="fixed top-6 right-6 z-[10010] flex flex-col gap-3 max-w-xs pointer-events-none">
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
					class="p-4 rounded-xl border border-slate-200/80 dark:border-slate-800/80 flex items-start gap-3.5 shadow-[0_12px_40px_rgba(0,0,0,0.08)] dark:shadow-[0_12px_40px_rgba(0,0,0,0.35)] bg-white/95 dark:bg-slate-900/95 border-l-4 pointer-events-auto select-none w-80 max-w-xs"
					:class="[
						toast.type === 'success' 
							? 'border-l-emerald-500' 
							: 'border-l-rose-500'
					]"
				>
					<div class="shrink-0 mt-0.5">
						<CheckCircle2 v-if="toast.type === 'success'" class="w-4 h-4 text-emerald-500" />
						<AlertCircle v-else class="w-4 h-4 text-rose-500" />
					</div>
					<div class="flex-1 text-xs font-semibold leading-relaxed pr-1 text-slate-800 dark:text-slate-250">
						{{ toast.message }}
					</div>
					<button @click="removeToast(toast.id)" class="text-slate-400 hover:text-slate-600 dark:text-zinc-550 dark:hover:text-white shrink-0 bg-transparent border-none cursor-pointer p-0.5 rounded-full hover:bg-slate-200/50 dark:hover:bg-zinc-800/50 transition-colors flex items-center justify-center">
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
            :canvas-text-color="
                activeProjectSettings.canvasTextColor || '#111827'
            "
            :canvas-border-color="
                activeProjectSettings.canvasBorderColor || '#e2e8f0'
            "
            :global-spacing="activeProjectSettings.globalSpacing || 50"
            :description="viewingProject.description"
            :category="viewingProject.category"
            :tools-used="viewingProject.tools_used"
            :tags="viewingProject.tags"
            @close="closeProjectModal"
            @select-portfolio="viewingProject = $event"
        />

        <!-- MODALS SECTION		<!-- 1. Avatar Uploader Modal -->
        <EditAvatarModal
            :show="showAvatarModal"
            :user="user"
            :form="form"
            @close="showAvatarModal = false"
            @submit="submitAvatar"
            @warning="triggerWarning"
        />

        <!-- 2. Banner/Featured Media Modal -->
        <EditBannerModal
            :show="showBannerModal"
            :user="user"
            :form="form"
            @close="showBannerModal = false"
            @submit="submitBanner"
            @warning="triggerWarning"
            @toast="addToast"
            @trigger-crop="handleTriggerCrop"
        />

        <EditDetailsModal
            :show="showLocationModal"
            :form="form"
            @close="showLocationModal = false"
            @submit="submitLocation"
        />

        <EditLocationModal
            :show="showLocationOnlyModal"
            :form="form"
            :userLocation="user.location"
            @close="showLocationOnlyModal = false"
            @submit="submitLocationOnly"
        />

        <EditSocialsModal
            :show="showSocialLinksModal"
            :form="form"
            @close="showSocialLinksModal = false"
            @submit="submitSocialLinks"
        />
        <EditBioModal
            :show="showBioModal"
            :form="form"
            @close="showBioModal = false"
            @submit="submitBio"
        />
        <EditUsernameModal
            :show="showUsernameModal"
            :form="form"
            :user="user"
            @close="showUsernameModal = false"
            @submit="submitUsername"
        />

        <CropImageModal
            :show="isCropperOpen"
            :imageSrc="cropperImageSrc"
            :initialAspectRatio="cropperAspectRatio"
            :originalFileName="originalFileName"
            :originalFileType="originalFileType"
            @close="closeCropper"
            @save="handleCropSave"
        />

        <!-- Premium Modern Warning Modal Alert -->
        <WarningModal
            :show="showWarningModal"
            :title="warningTitle"
            :message="warningMessage"
            @close="showWarningModal = false"
        />

        <!-- 8. Relations (Followers/Following) Modal -->
        <RelationsModal
            :show="showRelationsModal"
            :type="relationsModalType"
            :userId="user.id"
            :isOwnProfile="isOwnProfile"
            @close="showRelationsModal = false"
            @toast="addToast"
            @following-count-changed="updateFollowingCount"
        />

        <!-- 9. Feature Work (Quick Add Work) Modal -->
        <AddWorkModal
            :show="showAddWorkModal"
            :isEditingQuickWork="isEditingQuickWork"
            :editingQuickWorkId="editingQuickWorkId"
            :editingProject="editingProject"
            :user="user"
            @close="showAddWorkModal = false"
            @success="handleQuickStoreSuccess"
            @warning="triggerWarning"
            @toast="addToast"
        />

		<!-- 10. Success Share Modal ("Share your work") — standalone component -->
		<PagiShareModal
			:show="showShareModal"
			:project="newCreatedProject"
			:user="user"
			:activeShareUrl="getProjectShareUrl(newCreatedProject)"
			@close="showShareModal = false"
			@toast="addToast"
		/>

		<!-- 11. Profile Share Modal (Bagikan Profil Keren) -->
		<PagiShareModal
			:show="showProfileShareModal"
			:user="user"
			:displayRoleName="displayRoleName"
			:activeShareUrl="activeShareUrl"
			@close="showProfileShareModal = false"
			@toast="addToast"
		/>

		<!-- 12. Unfollow Confirmation Modal -->
		<Transition
			enter-active-class="transition duration-200 ease-out"
			enter-from-class="opacity-0"
			enter-to-class="opacity-100"
			leave-active-class="transition duration-150 ease-in"
			leave-from-class="opacity-100"
			leave-to-class="opacity-0"
		>
			<div
				v-if="showUnfollowModal"
				class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
				@click.self="cancelUnfollow"
			>
				<!-- Backdrop blur -->
				<div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

				<!-- Modal card -->
				<Transition
					enter-active-class="transition duration-200 ease-out"
					enter-from-class="opacity-0 scale-95 translate-y-2"
					enter-to-class="opacity-100 scale-100 translate-y-0"
					leave-active-class="transition duration-150 ease-in"
					leave-from-class="opacity-100 scale-100"
					leave-to-class="opacity-0 scale-95"
				>
					<div
						v-if="showUnfollowModal"
						class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200/80 dark:border-slate-800 w-full max-w-sm mx-auto overflow-hidden"
					>
						<!-- Accent top bar -->
						<div class="h-1 w-full bg-gradient-to-r from-red-500 via-rose-500 to-pink-500"></div>

						<div class="p-6">
							<!-- Avatar + icon -->
							<div class="flex items-center gap-4 mb-5">
								<div class="relative shrink-0">
									<div class="w-14 h-14 rounded-full border-2 border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 overflow-hidden">
										<img
											v-if="user.foto_path"
											:src="user.foto_path.startsWith('http') ? user.foto_path : '/storage/' + user.foto_path"
											alt="Avatar"
											class="w-full h-full object-cover"
										/>
										<div v-else class="w-full h-full flex items-center justify-center">
											<span class="text-lg font-bold text-indigo-500">{{ user.name?.charAt(0) || 'U' }}</span>
										</div>
									</div>
									<!-- Unfollow badge -->
									<div class="absolute -bottom-0.5 -right-0.5 w-5 h-5 rounded-full bg-red-500 border-2 border-white dark:border-slate-900 flex items-center justify-center">
										<svg class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
											<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
										</svg>
									</div>
								</div>
								<div class="min-w-0">
									<p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-0.5">Berhenti mengikuti</p>
									<p class="text-base font-bold text-slate-900 dark:text-white truncate">{{ user.name }}</p>
									<p class="text-xs text-slate-500 dark:text-slate-400 truncate">@{{ user.pagi_username || user.name }}</p>
								</div>
							</div>

							<!-- Message -->
							<p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed mb-6">
								Kamu tidak akan lagi melihat postingan atau update dari <strong class="text-slate-800 dark:text-slate-200 font-semibold">{{ user.name }}</strong> di timeline kamu.
							</p>

							<!-- Actions -->
							<div class="flex items-center gap-3">
								<button
									@click="cancelUnfollow"
									class="flex-1 h-10 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm font-semibold transition-all duration-150 cursor-pointer active:scale-98"
								>
									Batal
								</button>
								<button
									@click="confirmUnfollow"
									class="flex-1 h-10 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-bold transition-all duration-150 cursor-pointer active:scale-98 shadow-sm shadow-red-500/30"
								>
									Ya, Berhenti Ikuti
								</button>
							</div>
						</div>
					</div>
				</Transition>
			</div>
		</Transition>
	</div>
</template>

<style scoped>
/* stylelint-disable selector-pseudo-class-no-unknown */
/* Custom animations & deep styles */
.editor-content :deep(h1) { font-size: 2.25rem; font-weight: 800; line-height: 1.2; margin: 1.5rem 0 1rem 0; }
.editor-content :deep(h2) { font-size: 1.5rem; font-weight: 700; line-height: 1.3; margin: 1.25rem 0 0.875rem 0; }
.editor-content :deep(p) { margin: 0.875rem 0; font-size: 1.125rem; line-height: 1.8; }
.editor-content :deep(blockquote) { border-left: 4px solid #e2e8f0; padding-left: 1rem; color: #64748b; font-style: italic; margin: 1rem 0; }
.editor-content :deep(a) { color: inherit; text-decoration: underline; text-decoration-color: #64748b; }
.editor-content :deep(ul) { list-style-type: disc; padding-left: 1.5rem; margin: 0.875rem 0; }
.editor-content :deep(ol) { list-style-type: decimal; padding-left: 1.5rem; margin: 0.875rem 0; }
/* stylelint-enable selector-pseudo-class-no-unknown */

/* Upload Progress Bar Animations */
@keyframes progressbar-stripes {
    from {
        background-position: 1rem 0;
    }
    to {
        background-position: 0 0;
    }
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
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(100%);
    }
}

.animate-shimmer {
    animation: shimmer 2s infinite linear;
}

/* Tab content transition animations */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: opacity 0.2s ease-out, transform 0.2s ease-out;
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(6px);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>
