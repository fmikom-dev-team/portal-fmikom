<script setup lang="ts">
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { X } from "lucide-vue-next";
import { computed, defineAsyncComponent, onMounted, ref, watch } from "vue";
import Navbar from "../ui/Navbar.vue";
import ShareWorkModal from "../ui/ShareWorkModal.vue";

const WorkTab = defineAsyncComponent(() => import("./WorkTab.vue"));
const GalleryTab = defineAsyncComponent(() => import("./GalleryTab.vue"));
const SertifikatTab = defineAsyncComponent(() => import("./SertifikatTab.vue"));
const AboutTab = defineAsyncComponent(() => import("./AboutTab.vue"));

import Preview from "../ui/Preview.vue";
import AddWorkModal from "./components/AddWorkModal.vue";
import CropImageModal from "./components/CropImageModal.vue";
import EditAvatarModal from "./components/EditAvatarModal.vue";
import EditBannerModal from "./components/EditBannerModal.vue";
import EditBioModal from "./components/EditBioModal.vue";
import EditDetailsModal from "./components/EditDetailsModal.vue";
import EditLocationModal from "./components/EditLocationModal.vue";
import EditSocialsModal from "./components/EditSocialsModal.vue";
import EditUsernameModal from "./components/EditUsernameModal.vue";
import ProfileHeader from "./components/ProfileHeader.vue";
import ProfileTabs from "./components/ProfileTabs.vue";
import RelationsModal from "./components/RelationsModal.vue";
import ShareProfileModal from "./components/ShareProfileModal.vue";
import WarningModal from "./components/WarningModal.vue";

// Composables
import { useProfileForm } from "./composables/useProfileForm";
import { useProfileProjects } from "./composables/useProfileProjects";

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
const toasts = ref<Array<{ id: number; message: string; type: string }>>([]);
const addToast = (message: string, type = "success") => {
    const id = Date.now();
    toasts.value.push({ id, message, type });
    setTimeout(() => {
        toasts.value = toasts.value.filter((t) => t.id !== id);
    }, 3000);
};

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
const getInitialTab = () => {
    if (typeof globalThis.window === "undefined") return "Work";
    const path = globalThis.window.location.pathname;
    const segments = path.split("/").filter(Boolean);
    if (segments.length >= 3 && segments[0].toLowerCase() === "pagi") {
        const tabSegment = segments[2].toLowerCase();
        if (tabSegment === "gallery") return "Gallery";
        if (tabSegment === "certificates") return "Certificates";
        if (tabSegment === "sertifikat") return "Certificates";
        if (tabSegment === "about") return "About";
        if (tabSegment === "work") return "Work";
    }
    const params = new URLSearchParams(globalThis.window.location.search);
    const queryTab = params.get("tab");
    if (queryTab) {
        const qLower = queryTab.toLowerCase();
        if (qLower === "sertifikat" || qLower === "certificates")
            return "Certificates";
        if (qLower === "work") return "Work";
        if (qLower === "gallery") return "Gallery";
        if (qLower === "about") return "About";
    }
    return "Work";
};
const activeTab = ref(getInitialTab());

watch(activeTab, (newTab) => {
    if (typeof globalThis.window !== "undefined") {
        const path = globalThis.window.location.pathname;
        const segments = path.split("/").filter(Boolean);
        if (segments.length >= 2 && segments[0].toLowerCase() === "pagi") {
            const prefix = segments[0];
            const username = segments[1];
            const tabLower = newTab.toLowerCase();
            let newPathname = "";
            if (tabLower === "work") {
                newPathname = `/${prefix}/${username}`;
            } else {
                newPathname = `/${prefix}/${username}/${tabLower}`;
            }
            const url = new URL(globalThis.window.location.href);
            url.pathname = newPathname;
            url.searchParams.delete("tab");
            globalThis.window.history.replaceState(null, "", url.toString());
        } else {
            const url = new URL(globalThis.window.location.href);
            url.searchParams.set("tab", newTab);
            globalThis.window.history.replaceState(null, "", url.toString());
        }
    }
});

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

const isLoading = ref(true);
const followingState = ref(false);
const isMessageEnabled = ref(true);

onMounted(() => {
    initFormValues();
    if (props.isFollowing !== undefined) {
        followingState.value = props.isFollowing;
    } else {
        followingState.value =
            localStorage.getItem(`follow_${user.value.id}`) === "true";
    }
    isMessageEnabled.value = user.value.metadata?.is_message_enabled ?? true;

    const urlParams = new URLSearchParams(globalThis.window.location.search);
    const projectId = urlParams.get("project") || urlParams.get("portfolio");
    if (projectId) {
        const proj = props.projects?.find((p: any) => p.id === projectId);
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

    setTimeout(() => {
        isLoading.value = false;
    }, 600);
});

// Follow toggle — calls real API
const isFollowLoading = ref(false);
const toggleFollow = async () => {
    if (!page.props.auth?.user) {
        addToast(
            "Silakan login terlebih dahulu untuk mengikuti creator.",
            "info",
        );
        return;
    }
    if (isOwnProfile.value) return;
    if (isFollowLoading.value) return;

    isFollowLoading.value = true;
    const prevState = followingState.value;
    followingState.value = !followingState.value;

    try {
        const csrfToken = (
            document.querySelector("meta[name=csrf-token]") as HTMLMetaElement
        )?.content;
        const res = await fetch(`/pagi/users/${user.value.id}/follow`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": csrfToken || "",
                Accept: "application/json",
                "Content-Type": "application/json",
            },
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || "Failed");
        followingState.value = data.following;
        realFollowersCount.value = data.followers_count;
        localStorage.setItem(`follow_${user.value.id}`, String(data.following));
        if (data.following) {
            addToast(`Kamu sekarang mengikuti ${user.value.name}!`, "success");
        } else {
            addToast(`Kamu berhenti mengikuti ${user.value.name}.`, "info");
        }
    } catch (e) {
        console.error(e);
        followingState.value = prevState;
        addToast("Gagal memperbarui status follow. Coba lagi.", "error");
    } finally {
        isFollowLoading.value = false;
    }
};

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

const realFollowersCount = ref<number>(
    props.profileUser?.followers_count ??
        user.value.metadata?.followers?.length ??
        0,
);
const dynamicFollowersCount = computed(() => {
    return realFollowersCount.value;
});

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
        if (type === "super_admin" || type === "super-admin")
            return "Super Admin";
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
        user.value.linkedin
            ? `https://linkedin.com/in/${user.value.linkedin}`
            : "",
        user.value.github ? `https://github.com/${user.value.github}` : "",
        user.value.twitter ? `https://twitter.com/${user.value.twitter}` : "",
        user.value.instagram
            ? `https://instagram.com/${user.value.instagram}`
            : "",
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

    <div
        class="min-h-screen bg-slate-100 dark:bg-slate-950 font-sans text-slate-900 dark:text-slate-100 selection:bg-slate-900 selection:text-white dark:selection:bg-white dark:selection:text-slate-900"
        style="font-family: &quot;Inter&quot;, system-ui, sans-serif"
    >
        <Navbar :roleName="displayRoleName" />
        <!-- Outer page padding to create the floating layer gap ("ngambang" effect) -->
        <div
            class="mx-auto max-w-[1480px] px-1.5 sm:px-5 lg:px-6 pt-3 sm:pt-5 pb-0"
        >
            <!-- Inner Floating Card Layer ("Layout Lapisan") -->
            <main
                class="bg-white dark:bg-slate-900 border-x border-t border-slate-200/60 dark:border-slate-800 rounded-t-[24px] rounded-b-none pt-5 pb-32 px-3.5 sm:p-8 md:p-10 shadow-[0_15px_45px_-10px_rgba(15,23,42,0.08)] dark:shadow-[0_20px_50px_-15px_rgba(0,0,0,0.6)] relative"
            >
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
                    <GalleryTab
                        v-else-if="activeTab === 'Gallery'"
                        :projects="composableProjects"
                        :isOwnProfile="isOwnProfile"
                        :isLoading="isLoading"
                        @open-project="openProjectModal"
                        @delete-project="deleteProject"
                        @gallery-item-added="
                            (item) => localProjects.unshift(item)
                        "
                        @gallery-item-updated="handleGalleryItemUpdated"
                        @share-project="shareProject"
                    />
                    <SertifikatTab
                        v-else-if="activeTab === 'Certificates'"
                        :isOwnProfile="isOwnProfile"
                        :certificates="certificates"
                        :isLoading="isLoading"
                        @add-toast="addToast"
                        @update-certificates="(list) => (certificates = list)"
                    />
                    <AboutTab
                        v-else-if="activeTab === 'About'"
                        :profileUser="profileUser"
                        :user="user"
                        :isOwnProfile="isOwnProfile"
                        :isFollowing="followingState"
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
        <div
            class="fixed top-6 right-6 z-10010 flex flex-col gap-3.5 max-w-xs pointer-events-none"
        >
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
                            : 'bg-slate-100/90 border-slate-200 text-slate-800 dark:bg-slate-900/95 dark:border-slate-800 dark:text-slate-100',
                    ]"
                >
                    <div class="flex-1 text-xs font-bold leading-relaxed pr-2">
                        {{ toast.message }}
                    </div>
                    <button
                        @click="
                            toasts = toasts.filter((t) => t.id !== toast.id)
                        "
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 shrink-0"
                    >
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
        <ShareWorkModal
            :show="showShareModal"
            :project="newCreatedProject"
            :user="user"
            :shareUrl="getProjectShareUrl(newCreatedProject)"
            @close="showShareModal = false"
            @shareToFeed="addToast('Karya dibagikan ke umpan Anda!', 'success')"
        />

        <!-- 11. Profile Share Modal (Bagikan Profil Keren) -->
        <ShareProfileModal
            :show="showProfileShareModal"
            :user="user"
            :displayRoleName="displayRoleName"
            :activeShareUrl="activeShareUrl"
            @close="showProfileShareModal = false"
            @toast="addToast"
        />
    </div>
</template>

<style scoped>
/* Custom animations & deep styles */
.editor-content :deep(h1) {
    font-size: 2.25rem;
    font-weight: 800;
    line-height: 1.2;
    margin: 1rem 0;
}
.editor-content :deep(h2) {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1.3;
    margin: 0.875rem 0;
}
.editor-content :deep(p) {
    margin: 0.75rem 0;
    font-size: 1.125rem;
    line-height: 1.8;
}
.editor-content :deep(blockquote) {
    border-left: 4px solid #e2e8f0;
    padding-left: 1rem;
    color: #64748b;
    font-style: italic;
    margin: 1rem 0;
}
.editor-content :deep(a) {
    color: inherit;
    text-decoration: underline;
    text-decoration-color: #64748b;
}
.editor-content :deep(ul) {
    list-style-type: disc;
    padding-left: 1.5rem;
    margin: 0.75rem 0;
}
.editor-content :deep(ol) {
    list-style-type: decimal;
    padding-left: 1.5rem;
    margin: 0.75rem 0;
}

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
</style>
