<script setup lang="ts">
import { Deferred, Head, Link, usePage } from "@inertiajs/vue3";
import axios from "axios";
import {
	AlertCircle,
	CheckCircle2,
	ChevronRight,
	Eye,
	Flag,
	Folder,
	Heart,
	ListFilter,
	MoreHorizontal,
	Search,
	Share2,
	SlidersHorizontal,
	User,
	X,
} from "lucide-vue-next";
import {
	computed,
	defineAsyncComponent,
	onMounted,
	onUnmounted,
	ref,
	shallowRef,
	watch,
} from "vue";
import LazyWrapper from "@/components/Portal/LazyWrapper.vue";
import ShadcnSearch from "@/components/ui/ShadcnSearch.vue";
import { Skeleton } from "@/components/ui/skeleton";
import { formatStorageUrl, getInitialsAvatar } from "@/composables/useInitials";
import { useLoadingState } from "@/composables/useLoadingState";
import { showToast } from "@/composables/useGlobalToast";
import Navbar from "./ui/Navbar.vue";
import OptimizedImage from "./ui/OptimizedImage.vue";

const Preview = defineAsyncComponent(() => import("./ui/Preview.vue"));

import AvatarGroup, { type AvatarItem } from "@/components/ui/AvatarGroup.vue";
import PagiShareModal from "./ui/PagiShareModal.vue";
import VideoLazy from "./ui/VideoLazy.vue";

const { isLoading } = useLoadingState();

const props = defineProps<{
	moduleName: string;
	roleName: string;
	peopleYouMayKnow: Array<{
		id: number;
		name: string;
		email: string;
		role: string;
		foto_path: string | null;
		prodi: string | null;
	}>;
	feedProjects: Array<{
		id: number;
		title: string;
		image: string;
		author: string;
		avatar: string;
		likes: number;
		views: number;
	}>;
	followingFeedProjects?: Array<any>;
}>();

const localFeedProjects = shallowRef([...(props.feedProjects || [])]);
watch(
	() => props.feedProjects,
	(newVal) => {
		localFeedProjects.value = [...(newVal || [])];
	},
	{ deep: false },
);

const localFollowingFeedProjects = shallowRef([
	...(props.followingFeedProjects || []),
]);
watch(
	() => props.followingFeedProjects,
	(newVal) => {
		localFollowingFeedProjects.value = [...(newVal || [])];
	},
	{ deep: false },
);

const activeCategory = ref("For You");

const categories = [
	"For You",
	"Following",
	"Best of PAGI",
	"Graphic Design",
	"Photography",
	"Illustration",
	"3D Art",
	"UI/UX",
	"Motion",
	"Branding",
];

const searchQuery = ref("");
const selectedSort = ref("Recommended");
const showSortDropdown = ref(false);
const showFilters = ref(true);
const showSuggestions = ref(false);

const searchSuggestions = computed(() => {
	const query = searchQuery.value.trim().toLowerCase();
	if (!query) return [];

	const results: Array<{ type: "title" | "author" | "general"; text: string }> =
		[];

	// Add unique title suggestions matching search
	const matchedTitles = new Set<string>();
	localFeedProjects.value.forEach((p) => {
		if (p.title?.toLowerCase().includes(query)) {
			matchedTitles.add(p.title);
		}
	});
	Array.from(matchedTitles)
		.slice(0, 3)
		.forEach((title) => {
			results.push({ type: "title", text: title });
		});

	// Add unique author suggestions matching search
	const matchedAuthors = new Set<string>();
	localFeedProjects.value.forEach((p) => {
		if (p.author?.toLowerCase().includes(query)) {
			matchedAuthors.add(p.author);
		}
	});
	Array.from(matchedAuthors)
		.slice(0, 3)
		.forEach((author) => {
			results.push({ type: "author", text: author });
		});

	// Add general entry
	results.push({ type: "general", text: searchQuery.value });

	return results;
});

const filteredProjects = computed(() => {
	// "Following" tab: show only works from followed users
	if (activeCategory.value === "Following") {
		return localFollowingFeedProjects.value;
	}

	let list = localFeedProjects.value;

	// Search filter
	if (searchQuery.value.trim()) {
		const q = searchQuery.value.toLowerCase().trim();
		list = list.filter(
			(p) =>
				p.title?.toLowerCase().includes(q) ||
				p.author?.toLowerCase().includes(q),
		);
	}

	// Category filter
	if (
		activeCategory.value &&
		activeCategory.value !== "For You" &&
		activeCategory.value !== "Following" &&
		activeCategory.value !== "Best of PAGI"
	) {
		const catLower = activeCategory.value.toLowerCase();
		list = list.filter((p) => {
			if ((p as any).category) {
				return (p as any).category.toLowerCase() === catLower;
			}
			// Safe fallback keyword matching
			return p.title?.toLowerCase().includes(catLower) || p.id % 2 === 0;
		});
	}

	// Sort logic
	if (selectedSort.value === "Most Popular") {
		return [...list].sort((a, b) => (b.likes || 0) - (a.likes || 0));
	} else if (selectedSort.value === "Most Viewed") {
		return [...list].sort((a, b) => (b.views || 0) - (a.views || 0));
	}

	return list;
});

const isFollowingEmpty = computed(
	() =>
		activeCategory.value === "Following" &&
		localFollowingFeedProjects.value.length === 0,
);

// Pagination / Load More display limit state
const displayLimit = ref(20);

// Reset display limit when query or filter changes
watch([searchQuery, activeCategory, selectedSort], () => {
	displayLimit.value = 20;
});

// Dynamic projects from database based on filtered list and display limit
const visibleProjects = computed(() =>
	filteredProjects.value.slice(0, displayLimit.value),
);
const featuredProjects = computed(() => visibleProjects.value.slice(0, 8));
const regularProjects = computed(() => visibleProjects.value.slice(8));

// Toast Notification System
const addToast = (message: string, type: any = "success") => {
	showToast(message, type);
};

// Three-dot menu state per card
const openMenuId = ref<number | null>(null);
const toggleMenu = (id: number, e: Event) => {
	e.stopPropagation();
	openMenuId.value = openMenuId.value === id ? null : id;
};
const closeMenu = () => {
	openMenuId.value = null;
};

// Share modal
const shareModalProject = ref<any>(null);
const shareCopied = ref(false);
const shareModalUser = computed(() => {
	if (!shareModalProject.value)
		return { name: "", foto_path: null, pagi_username: null };
	return {
		name: shareModalProject.value.user?.name || shareModalProject.value.author,
		foto_path:
			shareModalProject.value.user?.foto_path || shareModalProject.value.avatar,
		pagi_username: shareModalProject.value.user?.pagi_username,
	};
});
const openShareModal = (p: any, e: Event) => {
	e.stopPropagation();
	closeMenu();
	shareModalProject.value = p;
	shareCopied.value = false;
};
const getShareUrl = (p: any) => {
	if (!p) return "";
	const base = window.location.origin;
	return p.user?.pagi_username
		? `${base}/pagi/${p.user.pagi_username}?project=${p.id}`
		: `${base}/pagi/profile/${p.user?.id ?? ""}?project=${p.id}`;
};
const copyShareLink = (p: any) => {
	navigator.clipboard.writeText(getShareUrl(p)).then(() => {
		shareCopied.value = true;
		setTimeout(() => {
			shareCopied.value = false;
		}, 2000);
	});
};

// Report modal
const reportModalProject = ref<any>(null);
const reportReason = ref("other");
const reportDescription = ref("");
const reportSubmitting = ref(false);
const openReportModal = (p: any, e: Event) => {
	e.stopPropagation();
	closeMenu();
	if (p.reported_by_me) {
		addToast(
			"Anda sudah mengirim laporan untuk karya ini dan sedang dalam peninjauan admin.",
			"error",
		);
		return;
	}
	reportModalProject.value = p;
	reportReason.value = "other";
	reportDescription.value = "";
};
const submitReport = async () => {
	if (!reportModalProject.value || !reportDescription.value.trim()) return;
	reportSubmitting.value = true;
	try {
		await axios.post("/pagi/works/report", {
			work_id: reportModalProject.value.id,
			reason: reportReason.value,
			description: reportDescription.value,
		});
		reportModalProject.value = null;
		addToast(
			"Laporan berhasil dikirim. Admin akan meninjau karya ini.",
			"success",
		);
	} catch (err: any) {
		const msg =
			err?.response?.data?.message || "Gagal mengirim laporan. Coba lagi.";
		addToast(msg, "error");
	} finally {
		reportSubmitting.value = false;
	}
};

const getAcceptedCollaborators = (project: any) => {
	return (project.resolved_collaborators || []).filter(
		(c: any) => c.status === "accepted",
	);
};

const getCollaborationAvatarsForCard = (project: any): AvatarItem[] => {
	const accepted = (project.resolved_collaborators || []).filter(
		(c: any) => c.status === "accepted",
	);
	if (accepted.length === 0) return [];
	const list: AvatarItem[] = [];
	if (project.user || project.author) {
		list.push({
			id: project.user?.id,
			name: project.user?.name || project.author,
			pagi_username: project.user?.pagi_username,
			src: project.avatar || project.user?.avatar,
		});
	}
	for (const c of accepted) {
		if (!list.some((item) => item.id === c.id)) {
			list.push({
				id: c.id,
				name: c.name,
				pagi_username: c.pagi_username,
				src: formatStorageUrl(c.avatar || c.foto_path),
			});
		}
	}
	return list;
};

// Interactive Like Handler
const updateProjectInList = (id: number, updates: Partial<any>) => {
	const updateItem = (item: any) =>
		item.id === id ? { ...item, ...updates } : item;
	localFeedProjects.value = localFeedProjects.value.map(updateItem);
	localFollowingFeedProjects.value =
		localFollowingFeedProjects.value.map(updateItem);

	if (viewingProject.value && viewingProject.value.id === id) {
		viewingProject.value = { ...viewingProject.value, ...updates };
	}
};

// Interactive Like Handler
const handleLikeProject = async (p: any) => {
	const page = usePage();
	if (!page.props.auth?.user) {
		addToast("Silakan login terlebih dahulu untuk menyukai postingan.", "info");
		return;
	}

	try {
		// Optimistically toggle status
		const newLiked = !p.liked;
		const newLikes = p.likes + (newLiked ? 1 : -1);
		updateProjectInList(p.id, { liked: newLiked, likes: newLikes });

		const res = await axios.post(`/pagi/preview/${p.id}/like`);

		// Re-sync with actual response values
		updateProjectInList(p.id, { liked: res.data.liked, likes: res.data.likes });
	} catch (e) {
		console.error("Failed to like project", e);
		addToast("Gagal menyukai postingan. Silakan coba lagi.", "error");
		// Rollback on failure
		updateProjectInList(p.id, { liked: p.liked, likes: p.likes });
	}
};

const viewingProject = ref<any>(null);
const isLoadingModal = ref(false);

const activeProjectSettings = computed(() => {
	if (!viewingProject.value?.content) {
		return {
			globalSpacing: 50,
			canvasBgColor: "#ffffff",
			canvasTextColor: "#111827",
			canvasBorderColor: "#e2e8f0",
		};
	}
	const settings = (viewingProject.value.content || []).find(
		(b: any) => b.type === "settings",
	);
	return {
		globalSpacing:
			settings?.globalSpacing !== undefined ? settings.globalSpacing : 50,
		canvasBgColor: settings?.canvasBgColor || "#ffffff",
		canvasTextColor: settings?.canvasTextColor || "#111827",
		canvasBorderColor: settings?.canvasBorderColor || "#e2e8f0",
	};
});

const openProjectModal = async (p: any) => {
	// Open modal immediately with basic card data (title, image, author, etc.)
	viewingProject.value = { ...p };
	document.body.style.overflow = "hidden";

	if (!p.id) return;

	// Lazy-load heavy data (content blocks + comments) only when modal opens.
	// This data is NOT included in the initial page payload to keep it small.
	if (p.content === undefined || p.comments === undefined) {
		isLoadingModal.value = true;
		try {
			const [detailRes] = await Promise.all([
				axios.get(`/pagi/preview/${p.id}/data`),
				axios
					.post(`/pagi/preview/${p.id}/view`)
					.then((res) => {
						updateProjectInList(p.id, {
							views: res.data.views,
							views_count: res.data.views,
						});
					})
					.catch(() => {}),
			]);
			// Merge full data into the viewing project and lists
			updateProjectInList(p.id, detailRes.data);
		} catch (e) {
			console.error("Failed to load project detail", e);
		} finally {
			isLoadingModal.value = false;
		}
	} else {
		// Content already fetched (re-open) — just track the view
		axios
			.post(`/pagi/preview/${p.id}/view`)
			.then((res) => {
				updateProjectInList(p.id, {
					views: res.data.views,
					views_count: res.data.views,
				});
			})
			.catch(() => {});
	}
};

const closeProjectModal = () => {
	viewingProject.value = null;
	document.body.style.overflow = "";
};

onUnmounted(() => {
	document.body.style.overflow = "";
});

const isVideoUrl = (url: string) => {
	if (!url) return false;
	const cleanUrl = url.split("?")[0].split("#")[0];
	const ext = cleanUrl.split(".").pop()?.toLowerCase();
	return ["mp4", "webm", "ogg", "mov", "m4v", "3gp"].includes(ext || "");
};

const handleSearchBlur = () => {
	setTimeout(() => {
		showSuggestions.value = false;
	}, 200);
};
</script>

<template>
	<Head>
		<title>PAGI — Explore</title>
	</Head>

    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 font-sans text-slate-900 dark:text-slate-100 selection:bg-slate-200 dark:selection:bg-slate-800 overflow-x-hidden">

        <Navbar />

        <!-- BEHANCE-STYLE FILTER & SEARCH SUBNAVBAR -->
        <div class="border-b border-slate-200/80 dark:border-zinc-800/80 bg-white dark:bg-zinc-950 py-2.5 sm:py-3.5 select-none sticky top-14 z-40">
            <div class="mx-auto max-w-[1400px] px-3 sm:px-4 flex items-center gap-2 sm:gap-3">

                <!-- Left: Filter Toggle Button (icon-only on mobile) -->
                <button @click="showFilters = !showFilters" aria-label="Filter kategori" class="inline-flex items-center gap-1.5 rounded-full border border-slate-200/85 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-900 bg-white dark:bg-zinc-900 px-2.5 sm:px-5 py-2 text-xs font-bold text-slate-800 dark:text-zinc-200 shadow-xs transition-all shrink-0">
                    <SlidersHorizontal class="h-3.5 w-3.5" />
                    <span class="hidden sm:inline">Filter</span>
                </button>

                <!-- Center: Search Input & Navigation Tabs -->
                <div class="flex-1 flex items-center gap-2 min-w-0">
                    <ShadcnSearch 
                        v-model="searchQuery"
                        endpoint="/pagi/instant-search"
                        placeholder="Search PAGI..."
                        widthClass="flex-1"
                    />

                    <!-- Search Internal Navigation Tabs (desktop only) -->
                    <div class="hidden md:flex items-center gap-0.5 bg-white dark:bg-zinc-900 rounded-full p-0.5 border border-slate-100 dark:border-zinc-800 shadow-xs shrink-0">
                        <Link href="/pagi" class="px-4 py-1.5 rounded-full text-[11px] font-bold bg-slate-950 dark:bg-slate-50 text-white dark:text-slate-950 transition-all shadow-xs">
                            Projects
                        </Link>
                        <Link href="/pagi/gallery" class="px-4 py-1.5 rounded-full text-[11px] font-bold text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-white transition-all">
                            Gallery
                        </Link>
                        <Link href="/pagi/people" class="px-4 py-1.5 rounded-full text-[11px] font-bold text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-white transition-all">
                            People
                        </Link>
                    </div>
                </div>


                <!-- Right: Sorting Dropdown (icon-only on mobile) -->
                <div class="relative shrink-0">
                    <button @click="showSortDropdown = !showSortDropdown" aria-label="Urutkan karya" class="inline-flex items-center gap-1.5 rounded-full border border-slate-200/85 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-900 bg-white dark:bg-zinc-950 px-2.5 sm:px-5 py-2 text-xs font-bold text-slate-800 dark:text-zinc-200 shadow-xs transition-all">
                        <ListFilter class="h-3.5 w-3.5 text-slate-500" />
                        <span class="hidden sm:inline">{{ selectedSort }}</span>
                        <svg class="hidden sm:block w-3 h-3 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': showSortDropdown }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <!-- Sorting Options Dropdown Card -->
                    <div v-show="showSortDropdown" class="absolute right-0 top-full mt-2 w-48 rounded-2xl border border-slate-150 dark:border-zinc-850 bg-white dark:bg-zinc-900 shadow-lg p-1.5 z-50">
                        <button v-for="sortOpt in ['Recommended', 'Most Popular', 'Most Viewed']" :key="sortOpt"
                            @click="selectedSort = sortOpt; showSortDropdown = false"
                            :class="['w-full text-left px-4 py-2 text-xs font-semibold rounded-xl transition-all border-none cursor-pointer',
                                selectedSort === sortOpt ? 'bg-indigo-600 text-white font-bold' : 'text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800/60 dark:hover:text-white']"
                        >
                            {{ sortOpt }}
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- CATEGORY TABS -->
        <div v-show="showFilters" class="sticky top-[108px] sm:top-[116px] md:top-[120px] z-30 border-b border-slate-200/80 dark:border-zinc-800/80 bg-white/95 dark:bg-zinc-950/95 backdrop-blur-md shadow-2xs transition-all duration-200">
            <div class="mx-auto max-w-[1400px] px-3 sm:px-4">
                <div class="flex items-center gap-1.5 sm:gap-2 py-2 sm:py-3 overflow-x-auto" style="scrollbar-width:none;">
                    <button v-for="cat in categories" :key="cat" @click="activeCategory = cat"
                        :class="['shrink-0 rounded-xl px-3 sm:px-4 py-1.5 text-xs sm:text-sm font-semibold transition-all whitespace-nowrap cursor-pointer border-none',
                            activeCategory === cat ? 'bg-indigo-600 dark:bg-indigo-500 text-white shadow-xs' : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-900']">
                        {{ cat }}
                    </button>
                    <button aria-label="Next categories" class="shrink-0 p-2 rounded-xl bg-slate-100 dark:bg-zinc-900 text-slate-500 dark:text-zinc-400 hover:bg-slate-200 dark:hover:bg-zinc-800 transition-colors border-none cursor-pointer">
                        <ChevronRight class="h-3.5 w-3.5" />
                    </button>
                </div>
            </div>
        </div>

        <!-- MAIN -->
        <main class="mx-auto max-w-[1400px] px-3 sm:px-4 pt-4 sm:pt-8 pb-24 md:pb-16 transition-all duration-300">
            <Deferred :data="['feedProjects', 'followingFeedProjects']">
                <template #fallback>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 mb-8 select-none">
                        <div v-for="n in 8" :key="n" class="flex flex-col gap-2 p-1.5">
                            <Skeleton class="aspect-4/3 rounded-md w-full" />
                            <div class="flex items-center gap-2">
                                <Skeleton class="h-6 w-6 rounded-full shrink-0" />
                                <div class="flex-1 space-y-1.5 min-w-0">
                                    <Skeleton class="h-3 w-3/4 rounded" />
                                    <Skeleton class="h-2 w-1/2 rounded" />
                                </div>
                                <Skeleton class="h-3.5 w-6 rounded shrink-0" />
                            </div>
                        </div>
                    </div>
                </template>
                <template #default>
                    <!-- FOLLOWING EMPTY STATE -->
            <div v-if="isFollowingEmpty" class="flex flex-col items-center justify-center py-24 text-center">
                <div class="w-20 h-20 rounded-2xl bg-linear-to-br from-indigo-500 to-purple-600 flex items-center justify-center mb-6 shadow-lg shadow-indigo-500/30">
                    <svg class="w-9 h-9 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Belum Ada Karya dari yang Kamu Ikuti</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm leading-relaxed mb-8">
                    Ikuti kreator lain untuk melihat karya terbaru mereka langsung di sini. Temukan dan dukung karya teman-temanmu!
                </p>
                <div class="flex items-center gap-3">
                    <Link
                        href="/pagi/people"
                        class="inline-flex items-center gap-2 h-10 px-6 rounded-full bg-slate-900 dark:bg-white hover:bg-slate-700 dark:hover:bg-slate-100 text-white dark:text-slate-900 text-sm font-bold transition-all shadow-sm active:scale-97"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        Jelajahi Kreator
                    </Link>
                    <button @click="activeCategory = 'For You'" class="h-10 px-6 rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition-all active:scale-97">
                        Lihat Semua Karya
                    </button>
                </div>
            </div>

            <!-- Featured Grid -->
            <template v-else>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 mb-8">

                <div v-for="(p, pIdx) in featuredProjects" :key="p.id" class="group" @click.self="closeMenu">
                    <div @click="openProjectModal(p)" class="relative rounded-md overflow-hidden aspect-4/3 bg-slate-100 dark:bg-slate-900 border border-slate-200/50 dark:border-slate-800 mb-2 cursor-pointer">
                        <VideoLazy v-if="isVideoUrl(p.image)" :src="p.image" :autoplay="true" :loop="true" :muted="true" :playsinline="true" className="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500" />
                        <!-- First 2 images get fetchpriority=high (LCP candidates above the fold) -->
                        <OptimizedImage v-else :src="p.image" :alt="p.title" :is-sensitive="Boolean(p.status === 'review' || p.status === 'hidden')" :fetchpriority="'high'" :loading="'eager'" className="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500" />
                        
                        <!-- Three-dot menu button at top right (Always visible on mobile/touch, hover on desktop) -->
                        <div class="absolute top-2.5 right-2.5 z-20 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity">
                            <div class="relative">
                                <button @click.stop="toggleMenu(p.id, $event)" aria-label="Opsi karya" class="w-6.5 h-6.5 rounded-full bg-black/40 backdrop-blur-md hover:bg-black/75 border border-white/20 flex items-center justify-center transition-all shadow-sm active:scale-95 cursor-pointer border-none" title="Opsi">
                                    <MoreHorizontal class="h-3.5 w-3.5 text-white" />
                                </button>
                                <!-- Dropdown -->
                                <div v-if="openMenuId === p.id" class="absolute right-0 top-full mt-1.5 w-40 bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md border border-slate-200/80 dark:border-zinc-800 rounded-2xl shadow-2xl z-50 overflow-hidden py-1.5">
                                    <button @click.stop="openShareModal(p, $event)" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-zinc-200 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors text-left border-none bg-transparent cursor-pointer">
                                        <Share2 class="h-3.5 w-3.5 text-indigo-500" /> Bagikan
                                    </button>
                                    <button v-if="!$page.props.auth?.user || $page.props.auth?.user?.id !== p.user?.id" @click.stop="openReportModal(p, $event)" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-colors text-left border-none bg-transparent cursor-pointer" :class="{ 'opacity-50 cursor-not-allowed': p.reported_by_me }">
                                        <Flag class="h-3.5 w-3.5" /> {{ p.reported_by_me ? 'Sudah Dilaporkan' : 'Laporkan' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Hover overlay -->
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all duration-300 flex items-end">
                            <div class="w-full p-3 opacity-0 group-hover:opacity-100 translate-y-1 group-hover:translate-y-0 transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3 text-white text-xs">
                                        <button @click.stop="handleLikeProject(p)" aria-label="Sukai karya" class="flex items-center gap-1 focus:outline-none transition-all duration-200 hover:scale-110 active:scale-95 cursor-pointer text-white" :class="{ 'text-red-500 hover:text-red-400': p.liked, 'hover:text-red-500': !p.liked }">
                                            <Heart class="h-3.5 w-3.5 transition-colors" :class="{ 'fill-red-500 text-red-500': p.liked, 'text-white': !p.liked }" />
                                            <span>{{ p.likes }}</span>
                                        </button>
                                        <span class="flex items-center gap-1"><Eye class="h-3.5 w-3.5" /> {{ p.views }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <div v-if="getAcceptedCollaborators(p).length > 0" class="shrink-0">
                            <AvatarGroup :avatars="getCollaborationAvatarsForCard(p)" :size="24" :overlap="8" :maxVisible="3" />
                        </div>
                        <template v-else>
                            <Link v-if="p.user" :href="p.user.pagi_username ? '/pagi/' + p.user.pagi_username : '/pagi/profile/' + p.user.id" class="shrink-0">
                                <OptimizedImage :src="p.avatar" :alt="p.author" className="h-6 w-6 rounded-full object-cover border border-slate-200 dark:border-slate-800 hover:ring-2 hover:ring-indigo-500/50 transition-all" />
                            </Link>
                            <OptimizedImage v-else :src="p.avatar" :alt="p.author" className="h-6 w-6 rounded-full object-cover shrink-0 border border-slate-200 dark:border-slate-800" />
                        </template>
                        
                        <div class="min-w-0 flex-1">
                            <p @click="openProjectModal(p)" class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate group-hover:text-slate-950 dark:group-hover:text-white transition-colors group-hover:underline underline-offset-2 decoration-slate-400 dark:decoration-slate-600 cursor-pointer">{{ p.title }}</p>
                            <div class="flex items-center gap-1 mt-0.5 min-w-0">
                                <Link v-if="p.user" :href="p.user.pagi_username ? '/pagi/' + p.user.pagi_username : '/pagi/profile/' + p.user.id" class="text-[10px] text-slate-500 dark:text-zinc-400 hover:text-indigo-500 dark:hover:text-indigo-400 hover:underline transition-colors truncate block">{{ p.author }}</Link>
                                <p v-else class="text-[10px] text-slate-500 dark:text-zinc-400 truncate">{{ p.author }}</p>
                                <span v-if="getAcceptedCollaborators(p).length > 0" class="shrink-0 px-1 py-0.25 rounded-xs bg-indigo-50 dark:bg-indigo-950/45 text-indigo-600 dark:text-indigo-400 text-[8px] font-black tracking-wider uppercase border border-indigo-100/10">Collab</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 text-[10px] text-slate-500 dark:text-zinc-400 shrink-0 select-none pt-0.5">
                            <button @click.stop="handleLikeProject(p)" aria-label="Sukai karya" class="flex items-center gap-0.5 transition-all duration-200 focus:outline-none hover:scale-110 active:scale-90 cursor-pointer" :class="p.liked ? 'text-red-500 dark:text-red-400 font-bold' : 'text-slate-500 dark:text-zinc-400 hover:text-red-500 dark:hover:text-red-455'">
                                <Heart class="h-3 w-3 transition-colors" :class="{ 'fill-red-500 text-red-500 dark:text-red-400': p.liked }" /> 
                                <span>{{ p.likes }}</span>
                            </button>
                            <span class="flex items-center gap-0.5"><Eye class="h-3 w-3" /> {{ p.views }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feed Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                <LazyWrapper
                    v-for="p in regularProjects"
                    :key="p.id"
                    placeholderClass="h-64 mb-2"
                >
                    <div class="group" @click.self="closeMenu">
                    <div @click="openProjectModal(p)" class="relative rounded-md overflow-hidden aspect-4/3 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 mb-2 cursor-pointer">
                        <VideoLazy v-if="isVideoUrl(p.image)" :src="p.image" :autoplay="true" :loop="true" :muted="true" :playsinline="true" className="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500" />
                        <OptimizedImage v-else :src="p.image" :alt="p.title" :is-sensitive="Boolean(p.status === 'review' || p.status === 'hidden')" className="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500" />
                        
                        <!-- Three-dot menu button at top right -->
                        <div class="absolute top-2.5 right-2.5 z-20 opacity-0 group-hover:opacity-100 transition-opacity">
                            <div class="relative">
                                <button @click.stop="toggleMenu(p.id, $event)" aria-label="Opsi karya" class="w-6.5 h-6.5 rounded-full bg-black/40 backdrop-blur-md hover:bg-black/75 border border-white/20 flex items-center justify-center transition-all shadow-sm active:scale-95 cursor-pointer border-none" title="Opsi">
                                    <MoreHorizontal class="h-3.5 w-3.5 text-white" />
                                </button>
                                <!-- Dropdown -->
                                <div v-if="openMenuId === p.id" class="absolute right-0 top-full mt-1.5 w-40 bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md border border-slate-200/80 dark:border-zinc-800 rounded-2xl shadow-2xl z-50 overflow-hidden py-1.5">
                                    <button @click.stop="openShareModal(p, $event)" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-zinc-200 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors text-left border-none bg-transparent cursor-pointer">
                                        <Share2 class="h-3.5 w-3.5 text-indigo-500" /> Bagikan
                                    </button>
                                    <button v-if="!$page.props.auth?.user || $page.props.auth?.user?.id !== p.user?.id" @click.stop="openReportModal(p, $event)" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-colors text-left border-none bg-transparent cursor-pointer" :class="{ 'opacity-50 cursor-not-allowed': p.reported_by_me }">
                                        <Flag class="h-3.5 w-3.5" /> {{ p.reported_by_me ? 'Sudah Dilaporkan' : 'Laporkan' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Hover overlay -->
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/35 transition-all duration-300 flex items-end p-3 opacity-0 group-hover:opacity-100">
                            <div class="flex items-center gap-3 text-white text-xs">
                                <button @click.stop="handleLikeProject(p)" aria-label="Sukai karya" class="flex items-center gap-1 focus:outline-none transition-all duration-200 hover:scale-110 active:scale-95 cursor-pointer text-white" :class="{ 'text-red-500 hover:text-red-400': p.liked, 'hover:text-red-500': !p.liked }">
                                    <Heart class="h-3.5 w-3.5 transition-colors" :class="{ 'fill-red-500 text-red-500': p.liked, 'text-white': !p.liked }" />
                                    <span>{{ p.likes }}</span>
                                </button>
                                <span class="flex items-center gap-1"><Eye class="h-3.5 w-3.5" /> {{ p.views }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <div v-if="getAcceptedCollaborators(p).length > 0" class="flex -space-x-2 shrink-0">
                            <Link v-if="p.user" :href="p.user.pagi_username ? '/pagi/' + p.user.pagi_username : '/pagi/profile/' + p.user.id" class="relative z-10 shrink-0">
                                <OptimizedImage :src="p.avatar" :alt="p.author" className="h-6 w-6 rounded-full object-cover border-2 border-white dark:border-slate-900 shadow-sm" />
                            </Link>
                            <OptimizedImage v-else :src="p.avatar" :alt="p.author" className="h-6 w-6 rounded-full object-cover shrink-0 border-2 border-white dark:border-slate-900 shadow-sm" />
                            
                            <template v-for="(collab, idx) in getAcceptedCollaborators(p).slice(0, 2)" :key="collab.id">
                                <Link :href="collab.pagi_username ? '/pagi/' + collab.pagi_username : '/pagi/profile/' + collab.id" class="shrink-0" :style="{ zIndex: 9 - Number(idx) }">
                                    <img :src="collab.avatar || getInitialsAvatar(collab.name)" :alt="collab.name" :title="collab.name" class="h-6 w-6 rounded-full object-cover border-2 border-white dark:border-slate-900 shadow-sm" />
                                </Link>
                            </template>
                            <div v-if="getAcceptedCollaborators(p).length > 2" class="h-6 w-6 rounded-full bg-slate-200 dark:bg-slate-800 border-2 border-white dark:border-slate-900 flex items-center justify-center text-[8px] font-black text-slate-600 dark:text-slate-400 shrink-0 z-0 shadow-sm">
                                +{{ getAcceptedCollaborators(p).length - 2 }}
                            </div>
                        </div>
                        <template v-else>
                            <Link v-if="p.user" :href="p.user.pagi_username ? '/pagi/' + p.user.pagi_username : '/pagi/profile/' + p.user.id" class="shrink-0">
                                <OptimizedImage :src="p.avatar" :alt="p.author" className="h-6 w-6 rounded-full object-cover border border-slate-200 dark:border-slate-800 hover:ring-2 hover:ring-indigo-500/50 transition-all" />
                            </Link>
                            <OptimizedImage v-else :src="p.avatar" :alt="p.author" className="h-6 w-6 rounded-full object-cover shrink-0 border border-slate-200 dark:border-slate-800" />
                        </template>
                        
                        <div class="min-w-0 flex-1">
                            <p @click="openProjectModal(p)" class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate group-hover:text-slate-950 dark:group-hover:text-white transition-colors group-hover:underline underline-offset-2 decoration-slate-400 dark:decoration-slate-600 cursor-pointer">{{ p.title }}</p>
                            <div class="flex items-center gap-1 mt-0.5 min-w-0">
                                <Link v-if="p.user" :href="p.user.pagi_username ? '/pagi/' + p.user.pagi_username : '/pagi/profile/' + p.user.id" class="text-[10px] text-slate-500 dark:text-zinc-400 hover:text-indigo-500 dark:hover:text-indigo-400 hover:underline transition-colors truncate block">{{ p.author }}</Link>
                                <p v-else class="text-[10px] text-slate-500 dark:text-zinc-400 truncate">{{ p.author }}</p>
                                <span v-if="getAcceptedCollaborators(p).length > 0" class="shrink-0 px-1 py-0.25 rounded-xs bg-indigo-50 dark:bg-indigo-950/45 text-indigo-600 dark:text-indigo-400 text-[8px] font-black tracking-wider uppercase border border-indigo-100/10">Collab</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 text-[10px] text-slate-500 dark:text-zinc-400 shrink-0 select-none pt-0.5">
                            <button @click.stop="handleLikeProject(p)" aria-label="Sukai karya" class="flex items-center gap-0.5 transition-all duration-200 focus:outline-none hover:scale-110 active:scale-90 cursor-pointer" :class="p.liked ? 'text-red-500 dark:text-red-400 font-bold' : 'text-slate-500 dark:text-zinc-400 hover:text-red-500 dark:hover:text-red-455'">
                                <Heart class="h-3 w-3 transition-colors" :class="{ 'fill-red-500 text-red-500 dark:text-red-400': p.liked }" /> 
                                <span>{{ p.likes }}</span>
                            </button>
                            <span class="flex items-center gap-0.5"><Eye class="h-3.5 w-3.5" /> {{ p.views }}</span>
                        </div>
                    </div>
                </div>
            </LazyWrapper>
            </div>

            <!-- Load more -->
            <div v-if="filteredProjects.length > displayLimit" class="mt-10 flex justify-center">
                <button @click="displayLimit += 20" class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-8 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-700 shadow-sm transition-all cursor-pointer">
                    Load More Projects
                </button>
            </div>
            </template>
                </template>
            </Deferred>
        </main>

        <Preview 
            v-if="viewingProject" 
            :title="viewingProject.title" 
            :content="viewingProject.content ?? []" 
            :cover-image="viewingProject.image" 
            :portfolio="viewingProject"
            :canvas-bg-color="activeProjectSettings.canvasBgColor"
            :canvas-text-color="activeProjectSettings.canvasTextColor"
            :canvas-border-color="activeProjectSettings.canvasBorderColor"
            :global-spacing="activeProjectSettings.globalSpacing"
            :description="viewingProject.description"
            :category="viewingProject.category"
            :tools-used="viewingProject.tools_used"
            :tags="viewingProject.tags"
            :is-loading="isLoadingModal"
            @close="closeProjectModal" 
            @select-portfolio="openProjectModal($event)"
        />
    </div>

    <!-- SHARE MODAL (Modern QR Code Share Component) -->
    <PagiShareModal
        v-if="shareModalProject"
        :show="!!shareModalProject"
        :user="shareModalUser"
        :project="shareModalProject"
        :activeShareUrl="getShareUrl(shareModalProject)"
        @close="shareModalProject = null"
        @toast="addToast"
    />

    <!-- REPORT MODAL -->
    <Teleport to="body">
        <div v-if="reportModalProject" class="fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center z-9999 p-4" @click.self="reportModalProject = null">
            <div class="w-full max-w-md bg-white dark:bg-zinc-900 rounded-2xl border border-slate-100 dark:border-zinc-800 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <Flag class="h-4 w-4 text-rose-500" />
                        <h3 class="text-sm font-bold text-slate-800 dark:text-zinc-100">Laporkan Karya</h3>
                    </div>
                    <button @click="reportModalProject = null" class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 transition-colors">
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <div class="p-5 space-y-4">
                    <p class="text-xs text-slate-500 dark:text-zinc-400">Laporkan karya <strong class="text-slate-700 dark:text-zinc-200">"{{ reportModalProject.title }}"</strong> kepada admin untuk ditinjau.</p>
                    <div>
                        <label for="report-reason" class="block text-[11px] font-bold text-slate-500 dark:text-zinc-400 mb-1.5 uppercase tracking-wide">Alasan Laporan</label>
                        <select id="report-reason" v-model="reportReason" class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-zinc-200 focus:ring-2 focus:ring-rose-500 outline-none">
                            <option value="inappropriate_content">Konten Tidak Pantas</option>
                            <option value="copyright_violation">Pelanggaran Hak Cipta</option>
                            <option value="spam">Spam</option>
                            <option value="harassment">Pelecehan</option>
                            <option value="misinformation">Informasi Menyesatkan</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label for="report-description" class="block text-[11px] font-bold text-slate-500 dark:text-zinc-400 mb-1.5 uppercase tracking-wide">Keterangan</label>
                        <textarea id="report-description" v-model="reportDescription" rows="3" placeholder="Jelaskan alasan laporan Anda secara detail..." class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-xs font-medium text-slate-700 dark:text-zinc-200 placeholder-slate-400 focus:ring-2 focus:ring-rose-500 outline-none resize-none"></textarea>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-1">
                        <button @click="reportModalProject = null" class="rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-4 py-2 text-xs font-bold text-slate-600 dark:text-zinc-400 hover:bg-slate-50 transition-colors">Batal</button>
                        <button @click="submitReport" :disabled="reportSubmitting || !reportDescription.trim()" class="rounded-xl bg-rose-600 hover:bg-rose-700 disabled:opacity-50 px-4 py-2 text-xs font-bold text-white transition-colors flex items-center gap-1.5">
                            <span v-if="reportSubmitting" class="animate-spin inline-block w-3 h-3 border-2 border-white/40 border-t-white rounded-full"></span>
                            {{ reportSubmitting ? 'Mengirim...' : 'Kirim Laporan' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
