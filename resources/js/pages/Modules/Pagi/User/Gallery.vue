<script setup lang="ts">
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import axios from "axios";
import {
	ArrowLeftRight,
	Eye,
	Flag,
	Heart,
	Image as ImageIcon,
	ListFilter,
	Loader2,
	MapPin,
	MoreHorizontal,
	Search,
	Share2,
	SlidersHorizontal,
	X,
} from "lucide-vue-next";
import { computed, onMounted, ref, watch } from "vue";
import Footer from "./ui/Footer.vue";
import Navbar from "./ui/Navbar.vue";
import OptimizedImage from "./ui/OptimizedImage.vue";
import Preview from "./ui/Preview.vue";
import VideoLazy from "./ui/VideoLazy.vue";

const props = defineProps<{
	moduleName: string;
	roleName: string;
	galleryItems: Array<{
		id: string;
		portfolio_id: number;
		url: string;
		type: "image" | "video";
		title: string;
		is_manual: boolean;
		likes: number;
		views: number;
		comments_count: number;
		author: {
			id: number;
			name: string;
			avatar: string | null;
			pagi_username: string | null;
			prodi: string | null;
		};
		portfolio: any;
	}>;
	nextPageUrl?: string | null;
	currentPage?: number;
	lastPage?: number;
	total?: number;
	filters?: {
		search?: string;
		sort?: string;
	};
}>();

const page = usePage();
const searchQuery = ref(props.filters?.search || "");
const selectedSort = ref(props.filters?.sort || "Recommended");
const showSortDropdown = ref(false);
const showFilters = ref(true);

const localGalleryItems = ref<any[]>([]);
const isFetchingMore = ref(false);
const isPageLoading = ref(false);

const formatItems = (items: any[]) => {
	return (items || []).map((item) => ({
		...item,
		authorName: item.author?.name || "Mahasiswa FMIKOM",
		authorProdi: item.author?.prodi || "Teknik Informatika",
	}));
};

// Initialize items on mount
localGalleryItems.value = formatItems(props.galleryItems);

// Watch for prop changes to handle append or reset
watch(
	() => props.galleryItems,
	(newVal) => {
		const currentPg = props.currentPage ?? 1;
		if (currentPg === 1) {
			localGalleryItems.value = formatItems(newVal);
		} else {
			const existingIds = new Set(
				localGalleryItems.value.map((item) => item.id),
			);
			const formatted = formatItems(newVal);
			const uniqueNew = formatted.filter((item) => !existingIds.has(item.id));
			localGalleryItems.value.push(...uniqueNew);
		}
	},
	{ deep: true },
);

// Server-side Search & Sorting Request
const runServerQuery = () => {
	isPageLoading.value = true;
	router.get(
		"/pagi/gallery",
		{
			search: searchQuery.value,
			sort: selectedSort.value,
		},
		{
			preserveState: true,
			preserveScroll: true,
			only: [
				"galleryItems",
				"nextPageUrl",
				"currentPage",
				"lastPage",
				"total",
				"filters",
			],
			onFinish: () => {
				isPageLoading.value = false;
			},
		},
	);
};

// Debounce search input to avoid hitting database on every keystroke
let searchTimeout: any = null;
watch(searchQuery, () => {
	if (searchTimeout) clearTimeout(searchTimeout);
	searchTimeout = setTimeout(() => {
		runServerQuery();
	}, 400);
});

// Immediately filter when sort option changes
watch(selectedSort, () => {
	runServerQuery();
});

// Load next page
const hasMore = computed(() => !!props.nextPageUrl);
const loadMore = () => {
	if (!props.nextPageUrl || isFetchingMore.value) return;
	isFetchingMore.value = true;

	router.get(
		props.nextPageUrl,
		{},
		{
			preserveState: true,
			preserveScroll: true,
			only: ["galleryItems", "nextPageUrl", "currentPage", "lastPage", "total"],
			onFinish: () => {
				isFetchingMore.value = false;
			},
		},
	);
};

// Details Preview Modal State
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
			console.log("Failed to increment views on explore gallery:", e);
		}
	}
};

const closeProjectModal = () => {
	viewingProject.value = null;
	document.body.style.overflow = "auto";
};

const getAcceptedCollaborators = (item: any) => {
	const collabs = item.portfolio?.resolved_collaborators || [];
	return collabs.filter((c: any) => c.status === "accepted");
};

// Three-dot menu
const openMenuId = ref<string | null>(null);
const toggleMenu = (id: string, e: Event) => {
	e.stopPropagation();
	openMenuId.value = openMenuId.value === id ? null : id;
};
const closeMenu = () => {
	openMenuId.value = null;
};

// Share modal
const shareModalItem = ref<any>(null);
const shareCopied = ref(false);
const openShareModal = (item: any, e: Event) => {
	e.stopPropagation();
	closeMenu();
	shareModalItem.value = item;
	shareCopied.value = false;
};
const getShareUrl = (item: any) => {
	if (!item?.portfolio) return `${window.location.origin}/pagi/gallery`;
	const base = window.location.origin;
	const p = item.portfolio;
	return p.user?.pagi_username
		? `${base}/pagi/${p.user.pagi_username}?project=${p.id}`
		: `${base}/pagi/profile/${p.user?.id ?? ""}?project=${p.id}`;
};
const copyShareLink = (item: any) => {
	navigator.clipboard.writeText(getShareUrl(item)).then(() => {
		shareCopied.value = true;
		setTimeout(() => {
			shareCopied.value = false;
		}, 2000);
	});
};

// Report modal
const reportModalItem = ref<any>(null);
const reportReason = ref("other");
const reportDescription = ref("");
const reportSubmitting = ref(false);
const openReportModal = (item: any, e: Event) => {
	e.stopPropagation();
	closeMenu();
	reportModalItem.value = item;
	reportReason.value = "other";
	reportDescription.value = "";
};
const submitReport = async () => {
	if (!reportModalItem.value?.portfolio || !reportDescription.value.trim())
		return;
	reportSubmitting.value = true;
	try {
		await axios.post("/pagi/works/report", {
			work_id: reportModalItem.value.portfolio.id,
			reason: reportReason.value,
			description: reportDescription.value,
		});
		reportModalItem.value = null;
	} catch (err) {
		console.error("Report failed", err);
	} finally {
		reportSubmitting.value = false;
	}
};
</script>

<template>
	<Head title="PAGI — Student Visual Gallery" />

	<div class="min-h-screen bg-slate-50 dark:bg-slate-950 font-sans text-slate-900 dark:text-slate-100 selection:bg-slate-200 dark:selection:bg-slate-800">
		<Navbar />

		<!-- HERO BANNER -->
		<div class="relative overflow-hidden bg-gradient-to-br from-[#030712] via-[#0b0f19] to-[#1e1b4b] border-b border-slate-900 py-16 px-6 text-center select-none">
			<!-- Background Dot Grid -->
			<div class="absolute inset-0 opacity-15 pointer-events-none bg-[radial-gradient(rgba(255,255,255,0.15)_1px,transparent_1px)] [background-size:20px_20px]"></div>
			
			<!-- Glowing Blurs -->
			<div class="absolute inset-0 pointer-events-none overflow-hidden">
				<div class="absolute -top-1/4 -left-1/4 h-[120%] w-[80%] rounded-full bg-indigo-500/10 blur-[130px] mix-blend-screen"></div>
				<div class="absolute -bottom-1/4 -right-1/4 h-[120%] w-[80%] rounded-full bg-purple-500/10 blur-[130px] mix-blend-screen"></div>
			</div>

			<div class="relative max-w-2xl mx-auto space-y-4">
				<h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-none">
					Student Visual Gallery
				</h1>
				<p class="text-xs sm:text-sm leading-relaxed text-slate-300 max-w-lg mx-auto font-medium">
					Temukan dan jelajahi karya seni, visual, desain grafis, fotografi, dan kreativitas mahasiswa Fakultas Ilmu Komputer.
				</p>
			</div>
		</div>

		<!-- FILTER & SEARCH SUBNAVBAR -->
		<div class="border-b border-slate-200/80 dark:border-zinc-800/80 bg-white dark:bg-zinc-950 py-3 select-none sticky top-14 z-40 shadow-2xs">
			<div class="mx-auto max-w-[1400px] px-4 flex items-center gap-3">
				<!-- Left: Filter indicator/toggle -->
				<button @click="showFilters = !showFilters" class="inline-flex items-center gap-1.5 rounded-full border border-slate-200/85 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-900 bg-white dark:bg-zinc-950 px-4 py-2.5 text-xs font-bold text-slate-800 dark:text-zinc-200 shadow-3xs transition-all shrink-0">
					<SlidersHorizontal class="h-3.5 w-3.5" />
					<span class="hidden sm:inline">Filter</span>
				</button>

				<!-- Center: Search Input -->
				<div class="flex-1 relative rounded-full border border-slate-200/85 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/40 hover:border-slate-300 dark:hover:border-zinc-700 flex items-center pl-3.5 pr-2 py-1.5 transition-all min-w-0 shadow-3xs">
					<Search class="h-4 w-4 text-slate-400 dark:text-zinc-550 shrink-0" />
					<input
						type="text"
						v-model="searchQuery"
						placeholder="Search gallery by title, student, or program..."
						class="flex-1 bg-transparent border-none outline-none text-xs font-semibold text-slate-800 dark:text-zinc-150 placeholder-slate-400 dark:placeholder-slate-550 pl-2 py-1 w-0 min-w-0"
					/>
					
					<!-- Search navigation links (desktop only) -->
					<div class="hidden md:flex items-center gap-0.5 bg-white dark:bg-zinc-900 rounded-full p-0.5 border border-slate-100/80 dark:border-zinc-800 shrink-0 shadow-3xs">
						<Link href="/pagi" class="px-4 py-1.5 rounded-full text-[11px] font-bold text-slate-500 hover:text-slate-800 dark:text-zinc-455 dark:hover:text-white transition-all">
							Projects
						</Link>
						<Link href="/pagi/gallery" class="px-4 py-1.5 rounded-full text-[11px] font-bold bg-slate-950 dark:bg-slate-50 text-white dark:text-slate-950 transition-all shadow-3xs">
							Gallery
						</Link>
						<Link href="/pagi/people" class="px-4 py-1.5 rounded-full text-[11px] font-bold text-slate-500 hover:text-slate-800 dark:text-zinc-455 dark:hover:text-white transition-all">
							People
						</Link>
					</div>
				</div>

				<!-- Right: Sorting Dropdown -->
				<div class="relative shrink-0">
					<button @click="showSortDropdown = !showSortDropdown" class="inline-flex items-center gap-1.5 rounded-full border border-slate-200/85 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-900 bg-white dark:bg-zinc-950 px-4 py-2.5 text-xs font-bold text-slate-800 dark:text-zinc-200 shadow-3xs transition-all">
						<ListFilter class="h-3.5 w-3.5 text-slate-500" />
						<span class="hidden sm:inline">{{ selectedSort }}</span>
						<svg class="hidden sm:block w-3 h-3 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': showSortDropdown }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
							<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
						</svg>
					</button>
					<div v-show="showSortDropdown" class="absolute right-0 top-full mt-2 w-48 rounded-2xl border border-slate-150 dark:border-zinc-850 bg-white dark:bg-zinc-900 shadow-xl p-1.5 z-50">
						<button v-for="sortOpt in ['Recommended', 'Most Popular', 'Most Viewed']" :key="sortOpt"
							@click="selectedSort = sortOpt; showSortDropdown = false"
							:class="['w-full text-left px-4 py-2.5 text-xs font-semibold rounded-xl transition-all border-none bg-transparent cursor-pointer',
								selectedSort === sortOpt ? 'bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-950 font-bold shadow-sm' : 'text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 dark:hover:text-white hover:text-slate-800']"
						>
							{{ sortOpt }}
						</button>
					</div>
				</div>
			</div>
		</div>

		<!-- MAIN CONTAINER -->
		<main class="mx-auto max-w-[1400px] px-4 py-8 pb-24 md:pb-12">
			<!-- Loading Skeletons -->
			<div v-if="isPageLoading" class="columns-2 sm:columns-2 md:columns-3 lg:columns-4 gap-2.5 space-y-2.5">
				<div 
					v-for="n in 12" 
					:key="n" 
					class="break-inside-avoid w-full rounded-2xl border border-slate-200/40 dark:border-zinc-800/60 bg-white dark:bg-zinc-900 shadow-3xs p-3 flex flex-col gap-3 mb-2.5"
				>
					<!-- Media placeholder -->
					<div 
						class="w-full rounded-xl bg-slate-200 dark:bg-zinc-850 animate-shimmer-fast"
						:style="{ height: `${Math.floor(Math.random() * 120) + 160}px` }"
					></div>
					<!-- Title placeholder -->
					<div class="h-4 w-3/4 rounded bg-slate-200 dark:bg-zinc-850 animate-shimmer-fast"></div>
					<!-- Author placeholder -->
					<div class="flex items-center gap-2 border-t border-slate-100 dark:border-zinc-800/80 pt-2.5">
						<div class="h-6 w-6 rounded-full bg-slate-200 dark:bg-zinc-850 animate-shimmer-fast shrink-0"></div>
						<div class="flex-1 space-y-1.5">
							<div class="h-2 w-2/3 rounded bg-slate-200 dark:bg-zinc-850 animate-shimmer-fast"></div>
							<div class="h-2 w-1/2 rounded bg-slate-200 dark:bg-zinc-850 animate-shimmer-fast"></div>
						</div>
						<div class="w-10 h-3 rounded bg-slate-200 dark:bg-zinc-850 animate-shimmer-fast shrink-0"></div>
					</div>
				</div>
			</div>

			<!-- Visual Masonry Grid -->
			<div v-else-if="localGalleryItems && localGalleryItems.length > 0" class="columns-2 sm:columns-2 md:columns-3 lg:columns-4 gap-2.5 space-y-2.5">
				<div 
					v-for="item in localGalleryItems" 
					:key="item.id" 
					class="break-inside-avoid relative overflow-hidden rounded-2xl border border-slate-200/50 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-2xs hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 group cursor-pointer"
					@click="openProjectModal(item.portfolio)"
				>
					<VideoLazy v-if="item.type === 'video'" :src="item.url" className="w-full h-auto object-cover rounded-2xl group-hover:scale-[1.015] transition-transform duration-500" />
					<OptimizedImage v-else :src="item.url" :alt="item.title" className="w-full h-auto object-cover group-hover:scale-[1.015] transition-transform duration-500" />
					
					<!-- Three-dot menu button at top right -->
					<div class="absolute top-3 right-3 z-20 opacity-0 group-hover:opacity-100 transition-opacity" @click.stop>
						<div class="relative">
							<button @click.stop="toggleMenu(item.id, $event)" class="w-7 h-7 rounded-full bg-black/60 backdrop-blur-md hover:bg-black/80 flex items-center justify-center transition-all shadow-md cursor-pointer border-none" title="Opsi">
								<MoreHorizontal class="h-3.5 w-3.5 text-white" />
							</button>
							<div v-if="openMenuId === item.id" class="absolute right-0 top-full mt-1.5 w-40 bg-white dark:bg-zinc-900 border border-slate-150 dark:border-zinc-800 rounded-xl shadow-xl z-50 overflow-hidden py-1">
								<button @click.stop="openShareModal(item, $event)" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-zinc-200 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors text-left border-none bg-transparent cursor-pointer">
									<Share2 class="h-3.5 w-3.5 text-indigo-500" /> Bagikan
								</button>
								<button v-if="!$page.props.auth?.user || $page.props.auth?.user?.id !== item.author?.id" @click.stop="openReportModal(item, $event)" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-955/20 transition-colors text-left border-none bg-transparent cursor-pointer">
									<Flag class="h-3.5 w-3.5" /> Laporkan
								</button>
							</div>
						</div>
					</div>

					<!-- Hover Overlay details -->
					<div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-col justify-end p-4 z-10">
						<div class="space-y-3">
							<span class="text-white text-sm font-extrabold line-clamp-2 leading-snug">{{ item.title }}</span>
							
							<!-- Author info -->
							<div class="flex items-center gap-2 border-t border-white/10 pt-2.5">
								<div v-if="getAcceptedCollaborators(item).length > 0" class="flex -space-x-2 shrink-0">
									<!-- Owner Avatar -->
									<div class="h-6 w-6 rounded-full overflow-hidden border border-white/20 shrink-0 relative z-10">
										<img v-if="item.author.avatar" :src="item.author.avatar" :alt="item.authorName" class="w-full h-full object-cover" />
										<div v-else class="w-full h-full bg-indigo-600 flex items-center justify-center text-[10px] text-white font-bold">{{ item.authorName.charAt(0) }}</div>
									</div>
									<!-- Collaborators Avatars -->
									<template v-for="(collab, idx) in getAcceptedCollaborators(item).slice(0, 2)" :key="collab.id">
										<div class="h-6 w-6 rounded-full overflow-hidden border border-white/20 shrink-0" :style="{ zIndex: 9 - Number(idx) }">
											<img :src="collab.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(collab.name)}&background=random`" :alt="collab.name" :title="collab.name" class="w-full h-full object-cover" />
										</div>
									</template>
									<!-- Overflow count -->
									<div v-if="getAcceptedCollaborators(item).length > 2" class="h-6 w-6 rounded-full bg-zinc-800 border border-white/20 flex items-center justify-center text-[8px] font-black text-white shrink-0 z-0 shadow-sm">
										+{{ getAcceptedCollaborators(item).length - 2 }}
									</div>
								</div>
								
								<!-- Single Owner Avatar -->
								<div v-else class="h-6 w-6 rounded-full overflow-hidden border border-white/20 shrink-0">
									<img v-if="item.author.avatar" :src="item.author.avatar" :alt="item.authorName" class="w-full h-full object-cover" />
									<div v-else class="w-full h-full bg-indigo-600 flex items-center justify-center text-[10px] text-white font-bold">{{ item.authorName.charAt(0) }}</div>
								</div>

								<div class="min-w-0 flex-1">
									<div class="flex items-center gap-1 min-w-0">
										<p class="text-[11px] font-bold text-white truncate leading-none">{{ item.authorName }}</p>
										<span v-if="getAcceptedCollaborators(item).length > 0" class="shrink-0 px-1 py-0.25 rounded-xs bg-indigo-500/25 text-indigo-300 text-[8px] font-black tracking-wider uppercase border border-indigo-500/30">Collab</span>
									</div>
									<p class="text-[9px] text-slate-300 dark:text-zinc-400 truncate leading-none mt-1">@{{ item.author.pagi_username || 'creator' }}</p>
								</div>
							</div>

							<!-- Stats -->
							<div class="flex items-center gap-3 text-[10px] font-bold text-white/80 select-none">
								<span class="flex items-center gap-1.5">
									<Heart class="w-3.5 h-3.5 text-rose-500 fill-rose-500 shrink-0" />
									{{ item.likes }}
								</span>
								<span class="flex items-center gap-1.5">
									<Eye class="w-3.5 h-3.5 text-slate-400 shrink-0" />
									{{ item.views }}
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Empty State -->
			<div v-else-if="!isPageLoading" class="border border-dashed border-slate-200 dark:border-zinc-800 rounded-3xl p-16 text-center bg-slate-50/50 dark:bg-zinc-900/10">
				<ImageIcon class="w-10 h-10 text-slate-400 mx-auto mb-4" />
				<h3 class="text-base font-bold text-slate-800 dark:text-zinc-200 mb-1">No visual items found</h3>
				<p class="text-xs text-slate-550 max-w-sm mx-auto leading-relaxed">Silakan coba cari kata kunci lain atau periksa kembali filter Anda.</p>
			</div>

			<!-- Load More Button -->
			<div v-if="hasMore" class="mt-12 text-center">
				<button 
					@click="loadMore" 
					:disabled="isFetchingMore"
					class="rounded-xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-8 py-3 text-xs font-bold text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-900 shadow-3xs transition-all cursor-pointer flex items-center justify-center gap-2 mx-auto disabled:opacity-60 disabled:cursor-not-allowed"
				>
					<Loader2 v-if="isFetchingMore" class="w-4 h-4 animate-spin text-slate-500" />
					<span>{{ isFetchingMore ? 'Loading More Visuals...' : 'Load More Visuals' }}</span>
				</button>
			</div>
		</main>

		<!-- PROJECT PREVIEW MODAL -->
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
		/>

		<Footer />
	</div>

	<!-- SHARE MODAL -->
	<Teleport to="body">
		<div v-if="shareModalItem" class="fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center z-[9999] p-4" @click.self="shareModalItem = null">
			<div class="w-full max-w-sm bg-white dark:bg-zinc-900 rounded-2xl border border-slate-100 dark:border-zinc-800 shadow-2xl overflow-hidden">
				<div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-zinc-800">
					<div class="flex items-center gap-2">
						<Share2 class="h-4 w-4 text-indigo-500" />
						<h3 class="text-sm font-bold text-slate-800 dark:text-zinc-100">Bagikan Karya</h3>
					</div>
					<button @click="shareModalItem = null" class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 transition-colors">
						<X class="h-4 w-4" />
					</button>
				</div>
				<div class="p-5 space-y-4">
					<p class="text-xs text-slate-500 dark:text-zinc-400">Salin tautan di bawah untuk berbagi karya ini:</p>
					<div class="flex items-center gap-2">
						<input :value="getShareUrl(shareModalItem)" readonly class="flex-1 rounded-lg border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 px-3 py-2 text-xs font-medium text-slate-700 dark:text-zinc-200 outline-none truncate" />
						<button @click="copyShareLink(shareModalItem)" class="shrink-0 rounded-lg px-3 py-2 text-xs font-bold transition-all" :class="shareCopied ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400' : 'bg-indigo-600 text-white hover:bg-indigo-700'">
							{{ shareCopied ? 'Tersalin!' : 'Salin' }}
						</button>
					</div>
				</div>
			</div>
		</div>
	</Teleport>

	<!-- REPORT MODAL -->
	<Teleport to="body">
		<div v-if="reportModalItem" class="fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center z-[9999] p-4" @click.self="reportModalItem = null">
			<div class="w-full max-w-md bg-white dark:bg-zinc-900 rounded-2xl border border-slate-100 dark:border-zinc-800 shadow-2xl overflow-hidden">
				<div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-zinc-800">
					<div class="flex items-center gap-2">
						<Flag class="h-4 w-4 text-rose-500" />
						<h3 class="text-sm font-bold text-slate-800 dark:text-zinc-100">Laporkan Karya</h3>
					</div>
					<button @click="reportModalItem = null" class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 transition-colors">
						<X class="h-4 w-4" />
					</button>
				</div>
				<div class="p-5 space-y-4">
					<p class="text-xs text-slate-500 dark:text-zinc-400">Laporkan karya <strong class="text-slate-700 dark:text-zinc-200">"{{ reportModalItem.title }}"</strong> kepada admin untuk ditinjau.</p>
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
						<button @click="reportModalItem = null" class="rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-4 py-2 text-xs font-bold text-slate-600 dark:text-zinc-400 hover:bg-slate-50 transition-colors">Batal</button>
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

<style scoped>
@keyframes shimmer-fast {
	0% {
		background-position: -200% 0;
	}
	100% {
		background-position: 200% 0;
	}
}
.animate-shimmer-fast {
	background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
	background-size: 200% 100%;
	animation: shimmer-fast 1.5s infinite linear;
}
.dark .animate-shimmer-fast {
	background: linear-gradient(90deg, #1f2937 25%, #374151 50%, #1f2937 75%);
	background-size: 200% 100%;
}
</style>
