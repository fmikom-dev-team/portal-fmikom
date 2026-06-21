<script setup lang="ts">
import { Deferred, Head, Link, usePage } from "@inertiajs/vue3";
import axios from "axios";
import {
	AlertCircle,
	Briefcase,
	Building,
	CheckCircle2,
	ChevronDown,
	ChevronRight,
	Eye,
	FileText,
	Flag,
	Folder,
	GraduationCap,
	Heart,
	Image as ImageIcon,
	ListFilter,
	MessageSquare,
	MoreHorizontal,
	Search,
	Share2,
	SlidersHorizontal,
	User,
	Users,
	X,
} from "lucide-vue-next";
import { computed, defineAsyncComponent, onMounted, onUnmounted, ref, watch } from "vue";
import { Skeleton } from "@/components/ui/skeleton";
import OptimizedImage from "../ui/OptimizedImage.vue";

const Preview = defineAsyncComponent(() => import("../ui/Preview.vue"));

import UmumNavbar from "../ui/UmumNavbar.vue";
import VideoLazy from "../ui/VideoLazy.vue";

const isLoading = ref(false);

const props = defineProps<{
	moduleName: string;
	roleName: string;
	peopleYouMayKnow: Array<any>;
	feedProjects: Array<any>;
	followingFeedProjects?: Array<any>;
	stats: {
		total_portfolios: number;
		total_creators: number;
		total_views: number;
	};
}>();

const page = usePage();

const searchQuery = ref("");
const activeCategory = ref("All Works");
const selectedSort = ref("Recommended");
const showSortDropdown = ref(false);
const showSuggestions = ref(false);

const displayLimit = ref(20);
watch([searchQuery, activeCategory, selectedSort], () => {
	displayLimit.value = 20;
});
const visibleProjects = computed(() =>
	filteredProjects.value.slice(0, displayLimit.value),
);

const categories = [
	"All Works",
	"Following",
	"Graphic Design",
	"Photography",
	"Illustration",
	"3D Art",
	"UI/UX",
	"Motion",
	"Branding",
];

const searchSuggestions = computed(() => {
	const query = searchQuery.value.trim().toLowerCase();
	if (!query) return [];

	const results: Array<{ type: "title" | "author" | "general"; text: string }> =
		[];

	// Add unique title suggestions matching search
	const matchedTitles = new Set<string>();
	(props.feedProjects || []).forEach((p) => {
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
	(props.feedProjects || []).forEach((p) => {
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
	if (activeCategory.value === "Following") {
		return props.followingFeedProjects || [];
	}

	let list = props.feedProjects || [];

	// Search filter
	if (searchQuery.value.trim()) {
		const q = searchQuery.value.toLowerCase().trim();
		list = list.filter(
			(p) =>
				p.title?.toLowerCase().includes(q) ||
				p.author?.toLowerCase().includes(q) ||
				p.tags?.some((t: string) => t.toLowerCase().includes(q)),
		);
	}

	// Category filter
	if (activeCategory.value && activeCategory.value !== "All Works") {
		const catLower = activeCategory.value.toLowerCase();
		list = list.filter((p) => {
			if ((p as any).category) {
				return (p as any).category.toLowerCase() === catLower;
			}
			// Safe fallback keyword matching
			return (
				p.title?.toLowerCase().includes(catLower) ||
				p.tags?.some((t: string) => t.toLowerCase() === catLower)
			);
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
		(props.followingFeedProjects || []).length === 0,
);

const resetFilters = () => {
	searchQuery.value = "";
	activeCategory.value = "All Works";
	selectedSort.value = "Recommended";
};

const hideSuggestionsWithDelay = () => {
	setTimeout(() => {
		showSuggestions.value = false;
	}, 200);
};

// Toast system
const toasts = ref<Array<{ id: number; message: string; type: string }>>([]);
const addToast = (message: string, type = "success") => {
	const id = Date.now();
	toasts.value.push({ id, message, type });
	setTimeout(() => {
		toasts.value = toasts.value.filter((t) => t.id !== id);
	}, 3000);
};

// Three-dot menu
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

// Project preview
const viewingProject = ref<any>(null);
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
	viewingProject.value = p;
	document.body.style.overflow = "hidden";

	if (p.id) {
		try {
			const res = await axios.post(`/pagi/preview/${p.id}/view`);
			p.views = res.data.views;
			if (p.views_count !== undefined) {
				p.views_count = res.data.views;
			}
		} catch (e) {
			console.error("Failed to increment portfolio views", e);
		}
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

const handleLikeProject = async (p: any) => {
	if (!page.props.auth?.user) {
		addToast("Silakan login terlebih dahulu untuk menyukai postingan.", "info");
		return;
	}
	try {
		const wasLiked = p.liked;
		p.liked = !wasLiked;
		p.likes += p.liked ? 1 : -1;

		const res = await axios.post(`/pagi/preview/${p.id}/like`);
		p.liked = res.data.liked;
		p.likes = res.data.likes;
	} catch (e) {
		console.error("Failed to like project", e);
		addToast("Gagal menyukai postingan. Silakan coba lagi.", "error");
	}
};
</script>

<template>
	<Head>
        <title>{{ moduleName + ' — Dashboard' }}</title>
    </Head>

	<div class="min-h-screen bg-slate-50 dark:bg-slate-950 font-sans text-slate-900 dark:text-slate-100 selection:bg-slate-200 dark:selection:bg-slate-800">
		<UmumNavbar :roleName="props.roleName" />

		<!-- AMBIENT BACKGROUND GLOWS -->
		<div class="relative overflow-hidden w-full bg-slate-50/50 dark:bg-slate-950/50">
			<div class="pointer-events-none absolute -left-40 top-0 h-96 w-96 rounded-full bg-indigo-600/10 blur-[120px] dark:bg-indigo-550/5"></div>
			<div class="pointer-events-none absolute -right-20 top-20 h-80 w-80 rounded-full bg-purple-600/10 blur-[100px] dark:bg-purple-550/5"></div>

			<!-- SEARCH & FILTER HEADER -->
			<div class="mx-auto max-w-[1400px] px-4 pt-12 pb-8">
				<div class="mb-8 space-y-6">
					<div class="text-left">
						<h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight leading-none">Jelajahi Karya Mahasiswa</h1>
						<p class="text-xs text-slate-500 dark:text-zinc-400 font-semibold mt-2.5">Temukan ide kreatif dan portofolio terbaik yang dipublikasikan oleh mahasiswa FMIKOM.</p>
					</div>

					<!-- Search & Filter Bar -->
					<div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between">
						<!-- Left: Search input -->
						<div class="flex-1 relative rounded-full border border-slate-200/85 dark:border-zinc-800 bg-white dark:bg-zinc-900/40 hover:border-slate-300 dark:hover:border-zinc-700 flex items-center pl-3.5 pr-1.5 py-1.5 transition-all min-w-0">
							<Search class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-slate-400 dark:text-zinc-555 shrink-0" />
							<input
								type="text"
								v-model="searchQuery"
								@focus="showSuggestions = true"
								@blur="hideSuggestionsWithDelay"
								placeholder="Cari judul karya atau nama kreator..."
								aria-label="Cari karya atau kreator"
								class="flex-1 bg-transparent border-none outline-none text-xs font-semibold text-slate-800 dark:text-zinc-150 placeholder-slate-400 pl-2 py-1.5 w-0 min-w-0"
							/>

							<!-- Realtime suggestions dropdown -->
							<div v-show="showSuggestions && searchSuggestions.length > 0" class="absolute left-0 right-0 top-full mt-2 bg-white dark:bg-zinc-900 border border-slate-150 dark:border-zinc-800 rounded-2xl shadow-xl z-50 overflow-hidden p-1.5">
								<div class="px-3 py-1.5 text-[9px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-555 border-b border-slate-100 dark:border-zinc-800/80 mb-1">
									Pencarian Terkini & Saran
								</div>
								<button v-for="(sug, idx) in searchSuggestions" :key="idx"
									@mousedown="searchQuery = sug.text; showSuggestions = false"
									class="w-full text-left px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800/60 flex items-center gap-2.5 transition-colors border-none bg-transparent cursor-pointer"
								>
									<Folder v-if="sug.type === 'title'" class="h-3.5 w-3.5 text-indigo-500 shrink-0" />
									<User v-else-if="sug.type === 'author'" class="h-3.5 w-3.5 text-emerald-500 shrink-0" />
									<Search v-else class="h-3.5 w-3.5 text-slate-400 shrink-0" />
									<span class="truncate text-left">{{ sug.text }}</span>
									<span v-if="sug.type === 'title'" class="ml-auto text-[9px] font-bold uppercase tracking-wide text-indigo-450 bg-indigo-50 dark:bg-indigo-950/40 px-2 py-0.5 rounded-md">Judul</span>
									<span v-else-if="sug.type === 'author'" class="ml-auto text-[9px] font-bold uppercase tracking-wide text-emerald-450 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-md">Kreator</span>
								</button>
							</div>
						</div>

						<!-- Right: Sort options -->
						<div class="relative shrink-0 flex items-center gap-2">
							<button @click="showSortDropdown = !showSortDropdown" aria-label="Urutkan karya" class="inline-flex items-center gap-1.5 rounded-full border border-slate-200/85 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-900 bg-white dark:bg-zinc-955 px-4 py-2.5 text-xs font-bold text-slate-800 dark:text-zinc-200 shadow-xs transition-all cursor-pointer">
								<ListFilter class="h-3.5 w-3.5 text-slate-500" />
								<span>{{ selectedSort }}</span>
								<ChevronDown class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': showSortDropdown }" />
							</button>

							<!-- Sorting Options dropdown -->
							<div v-show="showSortDropdown" class="absolute right-0 top-full mt-2 w-48 rounded-2xl border border-slate-150 dark:border-zinc-850 bg-white dark:bg-zinc-900 shadow-lg p-1.5 z-50">
								<button v-for="sortOpt in ['Recommended', 'Most Popular', 'Most Viewed']" :key="sortOpt"
									@click="selectedSort = sortOpt; showSortDropdown = false"
									:class="['w-full text-left px-4 py-2 text-xs font-semibold rounded-xl transition-all border-none bg-transparent cursor-pointer',
										selectedSort === sortOpt ? 'bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-955 font-bold' : 'text-slate-600 hover:bg-slate-50 dark:text-zinc-400 dark:hover:bg-zinc-800/60 dark:hover:text-white']"
								>
									{{ sortOpt }}
								</button>
							</div>
						</div>
					</div>

					<!-- Category Tabs -->
					<div class="border-b border-slate-250 dark:border-slate-800/85 py-1">
						<div class="flex items-center gap-1.5 py-2 overflow-x-auto" style="scrollbar-width:none;">
							<button v-for="cat in categories" :key="cat" @click="activeCategory = cat"
								:class="['shrink-0 rounded-full px-4 py-1.5 text-xs font-bold transition-all whitespace-nowrap cursor-pointer border-none',
									activeCategory === cat ? 'bg-slate-900 dark:bg-slate-50 text-white dark:text-slate-950 shadow-sm' : 'text-slate-650 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-150 dark:hover:bg-slate-900 bg-transparent']">
								{{ cat }}
							</button>
						</div>
					</div>
				</div>

				<Deferred data="['feedProjects', 'followingFeedProjects', 'stats']">
					<template #fallback>
						<!-- SKELETON LOADER -->
						<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 select-none">
							<div v-for="n in 8" :key="n" class="flex flex-col gap-2">
								<Skeleton class="aspect-4/3 rounded-md w-full" />
								<div class="flex items-center gap-2">
									<Skeleton class="h-6 w-6 rounded-full shrink-0" />
									<div class="flex-1 space-y-1.5 min-w-0">
										<Skeleton class="h-3 w-3/4 rounded" />
										<Skeleton class="h-2 w-1/2 rounded" />
									</div>
								</div>
							</div>
						</div>
					</template>
					<template #default>

						<!-- FOLLOWING EMPTY STATE -->
						<div v-if="isFollowingEmpty" class="flex flex-col items-center justify-center py-20 text-center bg-white dark:bg-zinc-900 border border-slate-200/60 dark:border-zinc-800 rounded-3xl p-8 shadow-sm w-full">
					<div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center mb-5 shadow-lg shadow-indigo-500/20">
						<svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
							<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
						</svg>
					</div>
					<h3 class="text-base font-black text-slate-900 dark:text-white mb-2">Belum Ada Karya dari yang Kamu Ikuti</h3>
					<p class="text-xs text-slate-500 dark:text-zinc-400 font-semibold max-w-sm leading-relaxed mb-6">
						Ikuti kreator lain untuk melihat karya terbaru mereka langsung di sini. Temukan dan dukung karya teman-temanmu!
					</p>
					<div class="flex items-center gap-3">
						<Link
							href="/pagi/people"
							class="inline-flex items-center gap-2 h-9 px-5 rounded-full bg-slate-900 dark:bg-white hover:bg-slate-700 dark:hover:bg-slate-100 text-white dark:text-slate-900 text-xs font-bold transition-all shadow-sm active:scale-97"
						>
							<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
							Jelajahi Kreator
						</Link>
						<button @click="activeCategory = 'All Works'" class="h-9 px-5 rounded-full border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-750 dark:text-slate-200 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-850 transition-all active:scale-97 cursor-pointer">
							Lihat Semua Karya
						</button>
					</div>
				</div>

				<!-- Grid List Portofolio -->
				<div v-else-if="visibleProjects.length > 0" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
					<div v-for="(p, pIdx) in visibleProjects" :key="p.id" class="group cursor-pointer" @click="openProjectModal(p)">
						<div class="relative rounded-md overflow-hidden aspect-4/3 bg-slate-100 dark:bg-slate-900 border border-slate-200/50 dark:border-slate-800 mb-2">
							<VideoLazy v-if="isVideoUrl(p.image)" :src="p.image" :autoplay="true" :loop="true" :muted="true" :playsinline="true" className="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500" />
							<OptimizedImage v-else :src="p.image" :alt="p.title" :fetchpriority="pIdx < 8 ? 'high' : 'auto'" :loading="pIdx < 8 ? 'eager' : 'lazy'" className="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500" />

							<!-- Three-dot menu -->
							<div class="absolute top-3 right-3 z-20 opacity-0 group-hover:opacity-100 transition-opacity">
								<div class="relative">
									<button @click.stop="toggleMenu(p.id, $event)" aria-label="Opsi karya" class="w-7 h-7 rounded-full bg-black/60 backdrop-blur-md hover:bg-black/80 flex items-center justify-center transition-all shadow-md cursor-pointer border-none" title="Opsi">
										<MoreHorizontal class="h-3.5 w-3.5 text-white" />
									</button>
									<div v-if="openMenuId === p.id" class="absolute right-0 top-full mt-1.5 w-40 bg-white dark:bg-zinc-900 border border-slate-150 dark:border-zinc-800 rounded-xl shadow-xl z-50 overflow-hidden py-1">
										<button @click.stop="openShareModal(p, $event)" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-zinc-200 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors text-left border-none bg-transparent cursor-pointer">
											<Share2 class="h-3.5 w-3.5 text-indigo-500" /> Bagikan
										</button>
										<button v-if="!$page.props.auth?.user || $page.props.auth?.user?.id !== p.user?.id" @click.stop="openReportModal(p, $event)" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-955/20 transition-colors text-left border-none bg-transparent cursor-pointer" :class="{ 'opacity-50 cursor-not-allowed': p.reported_by_me }">
											<Flag class="h-3.5 w-3.5" /> {{ p.reported_by_me ? 'Sudah Dilaporkan' : 'Laporkan' }}
										</button>
									</div>
								</div>
							</div>

							<!-- Hover overlay stats -->
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

						<!-- Details -->
						<div class="flex items-center gap-2">
							<div v-if="getAcceptedCollaborators(p).length > 0" class="flex -space-x-2 shrink-0">
								<Link v-if="p.user" :href="p.user.pagi_username ? '/pagi/' + p.user.pagi_username : '/pagi/profile/' + p.user.id" class="relative z-10 shrink-0" @click.stop>
									<OptimizedImage :src="p.avatar" :alt="p.author" className="h-6 w-6 rounded-full object-cover border-2 border-white dark:border-slate-900 shadow-sm" />
								</Link>
								<OptimizedImage v-else :src="p.avatar" :alt="p.author" className="h-6 w-6 rounded-full object-cover shrink-0 border-2 border-white dark:border-slate-900 shadow-sm" />
								
								<template v-for="(collab, idx) in getAcceptedCollaborators(p).slice(0, 2)" :key="collab.id">
									<Link :href="collab.pagi_username ? '/pagi/' + collab.pagi_username : '/pagi/profile/' + collab.id" class="shrink-0" :style="{ zIndex: 9 - Number(idx) }" @click.stop>
										<img :src="collab.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(collab.name)}&background=random`" :alt="collab.name" :title="collab.name" class="h-6 w-6 rounded-full object-cover border-2 border-white dark:border-slate-900 shadow-sm" />
									</Link>
								</template>
								<div v-if="getAcceptedCollaborators(p).length > 2" class="h-6 w-6 rounded-full bg-slate-200 dark:bg-slate-800 border-2 border-white dark:border-slate-900 flex items-center justify-center text-[8px] font-black text-slate-600 dark:text-slate-400 shrink-0 z-0 shadow-sm">
									+{{ getAcceptedCollaborators(p).length - 2 }}
								</div>
							</div>
							<template v-else>
								<Link v-if="p.user" :href="p.user.pagi_username ? '/pagi/' + p.user.pagi_username : '/pagi/profile/' + p.user.id" class="shrink-0" @click.stop>
									<OptimizedImage :src="p.avatar" :alt="p.author" className="h-6 w-6 rounded-full object-cover border border-slate-200 dark:border-slate-800 hover:ring-2 hover:ring-indigo-500/50 transition-all" />
								</Link>
								<OptimizedImage v-else :src="p.avatar" :alt="p.author" className="h-6 w-6 rounded-full object-cover shrink-0 border border-slate-200 dark:border-slate-800" />
							</template>

							<div class="min-w-0 flex-1 text-left">
								<p class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate group-hover:text-slate-950 dark:group-hover:text-white transition-colors group-hover:underline underline-offset-2 decoration-slate-400 dark:decoration-slate-600">{{ p.title }}</p>
								<div class="flex items-center gap-1 mt-0.5 min-w-0">
									<Link v-if="p.user" :href="p.user.pagi_username ? '/pagi/' + p.user.pagi_username : '/pagi/profile/' + p.user.id" class="text-[10px] text-slate-500 dark:text-zinc-400 hover:text-indigo-500 dark:hover:text-indigo-400 hover:underline transition-colors truncate block" @click.stop>{{ p.author }}</Link>
									<p v-else class="text-[10px] text-slate-500 dark:text-zinc-400 truncate">{{ p.author }}</p>
									<span v-if="getAcceptedCollaborators(p).length > 0" class="shrink-0 px-1 py-0.25 rounded-xs bg-indigo-50 dark:bg-indigo-950/45 text-indigo-600 dark:text-indigo-400 text-[8px] font-black tracking-wider uppercase border border-indigo-100/10">Collab</span>
								</div>
							</div>
							<div class="flex items-center gap-1.5 text-[10px] text-slate-500 dark:text-zinc-400 shrink-0 select-none">
								<button @click.stop="handleLikeProject(p)" aria-label="Sukai karya" class="flex items-center gap-0.5 transition-all duration-200 focus:outline-none hover:scale-110 active:scale-90 cursor-pointer border-none bg-transparent" :class="p.liked ? 'text-red-500 dark:text-red-400 font-bold' : 'text-slate-500 dark:text-zinc-400 hover:text-red-500 dark:hover:text-red-455'">
									<Heart class="h-3 w-3 transition-colors" :class="{ 'fill-red-500 text-red-500 dark:text-red-400': p.liked }" /> 
									<span>{{ p.likes }}</span>
								</button>
								<span class="flex items-center gap-0.5"><Eye class="h-3 w-3" /> {{ p.views }}</span>
							</div>
						</div>
					</div>
				</div>

				<!-- Load more -->
				<div v-if="filteredProjects.length > displayLimit" class="mt-10 flex justify-center">
					<button @click="displayLimit += 20" class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-8 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-700 shadow-sm transition-all cursor-pointer">
						Load More Projects
					</button>
				</div>

						<!-- Empty State -->
						<div v-else class="flex flex-col items-center justify-center py-20 text-center bg-white dark:bg-zinc-900 border border-slate-200/60 dark:border-zinc-800 rounded-3xl p-8 shadow-xs w-full">
							<div class="h-16 w-16 rounded-full bg-slate-50 dark:bg-zinc-800 flex items-center justify-center text-slate-400 mb-4">
								<Search class="h-8 w-8 text-slate-400 dark:text-zinc-500" />
							</div>
							<h3 class="text-base font-black text-slate-900 dark:text-white">Tidak ada karya yang cocok</h3>
							<p class="text-xs text-slate-500 dark:text-zinc-400 font-semibold max-w-sm mt-2">Coba ubah kata kunci pencarian Anda atau atur ulang filter kategori.</p>
							<button @click="resetFilters" class="mt-5 px-5 py-2.5 rounded-full bg-indigo-650 text-white text-xs font-bold hover:bg-indigo-755 transition-all shadow-md cursor-pointer border-none">
								Atur Ulang Filter
							</button>
						</div>
					</template>
				</Deferred>
			</div>
		</div>



		<!-- PREVIEW PORTFOLIO MODAL -->
		<Preview 
			v-if="viewingProject" 
			:title="viewingProject.title" 
			:content="viewingProject.content" 
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
			@close="closeProjectModal" 
			@select-portfolio="viewingProject = $event"
		/>		<!-- TOAST ALERTS CONTAINER -->
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
					<div class="flex-1 text-xs font-semibold leading-relaxed pr-1 text-slate-800 dark:text-slate-250 text-left">
						{{ toast.message }}
					</div>
					<button @click="toasts = toasts.filter(t => t.id !== toast.id)" class="text-slate-450 hover:text-slate-650 dark:hover:text-white shrink-0 bg-transparent border-none cursor-pointer p-0.5 rounded-full hover:bg-slate-200/50 dark:hover:bg-zinc-800/50 transition-colors flex items-center justify-center">
						<X class="w-3.5 h-3.5" />
					</button>
				</div>
			</TransitionGroup>
		</div>

		<!-- SHARE MODAL -->
		<Teleport to="body">
			<div v-if="shareModalProject" class="fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center z-[9999] p-4" @click.self="shareModalProject = null">
				<div class="w-full max-w-sm bg-white dark:bg-zinc-900 rounded-2xl border border-slate-100 dark:border-zinc-800 shadow-2xl overflow-hidden">
					<div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-zinc-800">
						<div class="flex items-center gap-2">
							<Share2 class="h-4 w-4 text-indigo-550" />
							<h3 class="text-sm font-bold text-slate-800 dark:text-zinc-100">Bagikan Karya</h3>
						</div>
						<button @click="shareModalProject = null" class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 transition-colors">
							<X class="h-4 w-4" />
						</button>
					</div>
					<div class="p-5 space-y-4 text-left">
						<p class="text-xs text-slate-500 dark:text-zinc-400 font-semibold">Salin tautan di bawah untuk berbagi karya ini:</p>
						<div class="flex items-center gap-2">
							<input :value="getShareUrl(shareModalProject)" readonly class="flex-1 rounded-lg border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-zinc-250 outline-none truncate" />
							<button @click="copyShareLink(shareModalProject)" class="shrink-0 rounded-lg px-3 py-2 text-xs font-bold transition-all" :class="shareCopied ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400' : 'bg-indigo-650 text-white hover:bg-indigo-755'">
								{{ shareCopied ? 'Tersalin!' : 'Salin' }}
							</button>
						</div>
					</div>
				</div>
			</div>
		</Teleport>

		<!-- REPORT MODAL -->
		<Teleport to="body">
			<div v-if="reportModalProject" class="fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center z-[9999] p-4" @click.self="reportModalProject = null">
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
					<div class="p-5 space-y-4 text-left">
						<p class="text-xs text-slate-500 dark:text-zinc-400 font-semibold">Laporkan karya <strong class="text-slate-700 dark:text-zinc-200">"{{ reportModalProject.title }}"</strong> kepada admin untuk ditinjau.</p>
						<div>
							<label class="block text-[11px] font-bold text-slate-500 dark:text-zinc-400 mb-1.5 uppercase tracking-wide">Alasan Laporan</label>
							<select v-model="reportReason" class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-zinc-200 focus:ring-2 focus:ring-rose-500 outline-none">
								<option value="inappropriate_content">Konten Tidak Pantas</option>
								<option value="copyright_violation">Pelanggaran Hak Cipta</option>
								<option value="spam">Spam</option>
								<option value="harassment">Pelecehan</option>
								<option value="misinformation">Informasi Menyesatkan</option>
								<option value="other">Lainnya</option>
							</select>
						</div>
						<div>
							<label class="block text-[11px] font-bold text-slate-500 dark:text-zinc-400 mb-1.5 uppercase tracking-wide">Keterangan</label>
							<textarea v-model="reportDescription" rows="3" placeholder="Jelaskan alasan laporan Anda secara detail..." class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-xs font-medium text-slate-700 dark:text-zinc-200 placeholder-slate-400 focus:ring-2 focus:ring-rose-500 outline-none resize-none"></textarea>
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
	</div>
</template>
