<script setup lang="ts">
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import {
	Folder,
	ListFilter,
	MapPin,
	Search,
	SlidersHorizontal,
	User,
} from "lucide-vue-next";
import { computed, onMounted, ref, watch } from "vue";
import LazyWrapper from "@/components/Portal/LazyWrapper.vue";
import { Skeleton } from "@/components/ui/skeleton";
import { useLoadingState } from "@/composables/useLoadingState";
import Navbar from "./ui/Navbar.vue";
import OptimizedImage from "./ui/OptimizedImage.vue";
import SidebarPeople from "./ui/SidebarPeople.vue";
import UmumNavbar from "./ui/UmumNavbar.vue";
import VideoLazy from "./ui/VideoLazy.vue";

const { isLoading } = useLoadingState();

const props = defineProps<{
	moduleName: string;
	roleName: string;
	peopleYouMayKnow: Array<{
		id: number;
		name: string;
		email: string;
		pagi_username?: string | null;
		role: string;
		foto_path: string | null;
		banner_path?: string | null;
		prodi: string | null;
		covers?: string[];
		total_likes?: number;
		total_projects?: number;
		followers_count?: number;
		skills?: any[];
		location?: string | null;
	}>;
}>();
const page = usePage();

const isMahasiswa = computed(() => {
	const role =
		props.roleName || (page.props as any).context?.active_role || "mahasiswa";
	return role.toLowerCase() === "mahasiswa";
});

const user = computed(
	() => page.props.auth?.user || { name: "User", email: "" },
);
const selectedPerson = ref<any>(null);
const currentIndex = ref(0);
const selectPerson = (p: any, idx: number) => {
	selectedPerson.value = p;
	currentIndex.value = idx;
};
const navigate = (dir: number) => {
	const list = filteredPeople.value;
	if (list.length === 0) return;
	currentIndex.value = (currentIndex.value + dir + list.length) % list.length;
	selectedPerson.value = list[currentIndex.value];
};

const isVideoUrl = (url: string | null): boolean => {
	if (!url) return false;
	const ext = url.split(".").pop()?.toLowerCase();
	return ["mp4", "webm", "ogg", "mov"].includes(ext || "");
};

const formatNumber = (num: number) => {
	if (num >= 1000000) {
		return `${(num / 1000000).toFixed(1).replace(/\.0$/, "")}M`;
	}
	if (num >= 1000) {
		return `${(num / 1000).toFixed(1).replace(/\.0$/, "")}K`;
	}
	return num.toString();
};

// Title case for PAGI module only — only first letter of each word capitalized.
// Capitalizes after spaces and hyphens, but NOT after apostrophes (e.g. Ma'ruf not Ma'Ruf).
const toPagiTitleCase = (str: string): string => {
	if (!str) return str;
	return str
		.toLowerCase()
		.replace(/(?:^|\s|-)\S/g, (char) => char.toUpperCase());
};

const allPeople = computed(() => {
	return props.peopleYouMayKnow.map((p) => {
		const skillsArray = p.skills || [];
		const parsedSkills = skillsArray.map((s: any) =>
			typeof s === "string" ? s : s.name || "",
		);

		return {
			id: p.id,
			name: toPagiTitleCase(p.name),
			pagi_username: p.pagi_username ?? null,
			loc: p.location || "Indonesia",
			prodi: p.prodi ?? "Teknik Informatika",
			avatar:
				p.foto_path ??
				`https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(p.name)}&backgroundColor=2563eb&textColor=ffffff`,
			banner: p.banner_path ?? null,
			skills: parsedSkills,
			appr: formatNumber(p.total_likes || 0),
			fol: formatNumber(p.followers_count || 0),
			proj: p.total_projects || 0,
			imgs: p.covers || [],
			coverGrad: "from-blue-500 via-indigo-500 to-purple-600",
		};
	});
});

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

	// Add unique prodi suggestions matching search
	const matchedProdis = new Set<string>();
	(allPeople.value || []).forEach((p) => {
		if (p.prodi?.toLowerCase().includes(query)) {
			matchedProdis.add(p.prodi);
		}
	});
	Array.from(matchedProdis)
		.slice(0, 3)
		.forEach((prodi) => {
			results.push({ type: "title", text: prodi });
		});

	// Add unique name suggestions matching search
	const matchedNames = new Set<string>();
	(allPeople.value || []).forEach((p) => {
		if (p.name?.toLowerCase().includes(query)) {
			matchedNames.add(p.name);
		}
	});
	Array.from(matchedNames)
		.slice(0, 3)
		.forEach((name) => {
			results.push({ type: "author", text: name });
		});

	// Add general entry
	results.push({ type: "general", text: searchQuery.value });

	return results;
});

const filteredPeople = computed(() => {
	let list = allPeople.value || [];

	// Search filter
	if (searchQuery.value.trim()) {
		const q = searchQuery.value.toLowerCase().trim();
		list = list.filter(
			(p) =>
				p.name?.toLowerCase().includes(q) ||
				p.prodi?.toLowerCase().includes(q) ||
				p.loc?.toLowerCase().includes(q),
		);
	}

	// Sort logic
	if (selectedSort.value === "Most Followers") {
		return [...list].sort((a, b) => {
			const parseVal = (v: string) => {
				if (v.includes("M")) return Number.parseFloat(v) * 1000000;
				if (v.includes("K")) return Number.parseFloat(v) * 1000;
				return Number.parseFloat(v) || 0;
			};
			return parseVal(b.fol) - parseVal(a.fol);
		});
	} else if (selectedSort.value === "Newest") {
		return [...list].sort((a, b) => b.id - a.id);
	}

	return list;
});

// Pagination: show 32 at a time
const PAGE_SIZE = 32;
const visibleCount = ref(PAGE_SIZE);

// Reset visible count whenever filters/sort changes
watch(filteredPeople, () => {
	visibleCount.value = PAGE_SIZE;
});

const visiblePeople = computed(() =>
	filteredPeople.value.slice(0, visibleCount.value),
);
const hasMore = computed(
	() => filteredPeople.value.length > visibleCount.value,
);

const loadMore = () => {
	visibleCount.value += PAGE_SIZE;
};

const startChat = (partnerId: number) => {
	router.visit(`/pagi/messages?chat=${partnerId}`);
};

const handleBlur = () => {
	setTimeout(() => {
		showSuggestions.value = false;
	}, 200);
};
</script>

<template>
    <Head title="PAGI — People">
        <title>PAGI — People</title>
    </Head>
    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 font-sans text-slate-900 dark:text-slate-100 selection:bg-slate-200 dark:selection:bg-slate-800">

        <Navbar v-if="isMahasiswa" />
        <UmumNavbar v-else :roleName="props.roleName" />

        <!-- HERO BANNER -->
        <div class="relative overflow-hidden bg-linear-to-br from-[#030712] via-[#0b0f19] to-[#1e1b4b] border-b border-slate-900 py-20 px-6 text-center select-none">
            
            <!-- Background Abstract Tech Lines & Dots Grid with lighter points -->
            <div class="absolute inset-0 opacity-15 pointer-events-none bg-[radial-gradient(rgba(255,255,255,0.15)_1px,transparent_1px)] bg-size-[20px_20px]"></div>
            
            <!-- Animated Glowing Mesh-style Blurs -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                <div class="absolute -top-1/4 -left-1/4 h-[120%] w-[80%] rounded-full bg-indigo-500/10 blur-[130px] mix-blend-screen animate-pulse duration-[8s]"></div>
                <div class="absolute -bottom-1/4 -right-1/4 h-[120%] w-[80%] rounded-full bg-purple-500/10 blur-[130px] mix-blend-screen animate-pulse duration-[6s]"></div>
            </div>

            <div class="relative max-w-2xl mx-auto space-y-5">
                <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-none drop-shadow-sm">
                    Build Your Academic & <br class="hidden sm:inline" />
                    Creative Network
                </h1>
                <p class="text-[12px] sm:text-xs leading-relaxed text-slate-300 max-w-lg mx-auto font-medium">
                    Hubungkan visi Anda dengan kreator, pengembang, dan desainer terbaik Fakultas Ilmu Komputer. Bangun relasi akademis dan temukan kolaborator untuk proyek inovatif Anda berikutnya.
                </p>
                <div class="pt-4 flex flex-wrap items-center justify-center gap-3">
                    <button class="rounded-full bg-white text-indigo-700 hover:text-indigo-850 hover:bg-slate-50 hover:shadow-lg hover:shadow-indigo-500/20 active:scale-98 px-7 py-3 text-[11px] font-extrabold transition-all shadow-md">
                        Mulai Berjejaring
                    </button>
                    <button class="rounded-full border border-white/30 bg-white/10 backdrop-blur-xs hover:bg-white/20 hover:border-white/40 active:scale-98 px-7 py-3 text-[11px] font-extrabold text-white transition-all shadow-sm">
                        Panduan Kolaborasi
                    </button>
                </div>
            </div>
        </div>


        <!-- BEHANCE-STYLE FILTER & SEARCH SUBNAVBAR -->
        <div class="border-b border-slate-200/80 dark:border-zinc-800/80 bg-white dark:bg-zinc-955 py-2.5 sm:py-3.5 select-none sticky top-14 z-40">
            <div class="mx-auto max-w-[1400px] px-3 sm:px-4 flex items-center gap-2 sm:gap-3">

                <!-- Left: Filter Toggle Button (icon-only on mobile) -->
                <button @click="showFilters = !showFilters" aria-label="Filter kategori" class="inline-flex items-center gap-1.5 rounded-full border border-slate-200/85 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-900 bg-white dark:bg-zinc-950 px-2.5 sm:px-5 py-2 text-xs font-bold text-slate-800 dark:text-zinc-200 shadow-xs transition-all shrink-0">
                    <SlidersHorizontal class="h-3.5 w-3.5" />
                    <span class="hidden sm:inline">Filter</span>
                </button>

                <!-- Center: Search Input -->
                <div class="flex-1 relative rounded-full border border-slate-200/85 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/40 hover:border-slate-300 dark:hover:border-zinc-700 flex items-center pl-3.5 pr-1.5 py-1 transition-all min-w-0">
                    <Search class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-slate-400 dark:text-zinc-500 shrink-0" />
                    <input
                        type="text"
                        v-model="searchQuery"
                        @focus="showSuggestions = true"
                        @blur="handleBlur"
                        placeholder="Search PAGI..."
                        aria-label="Cari kreator"
                        class="flex-1 bg-transparent border-none outline-none text-xs font-semibold text-slate-800 dark:text-zinc-150 placeholder-slate-400 pl-2 py-1.5 w-0 min-w-0"
                    />

                    <!-- REALTIME SEARCH SUGGESTIONS DROPDOWN -->
                    <div v-show="showSuggestions && searchSuggestions.length > 0" class="absolute left-0 right-0 top-full mt-2 bg-white dark:bg-zinc-900 border border-slate-150 dark:border-zinc-800 rounded-2xl shadow-xl z-50 overflow-hidden p-1.5">
                        <div class="px-3 py-1.5 text-[9px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-555 border-b border-slate-100 dark:border-zinc-800/80 mb-1">
                            Pencarian Terkini & Saran
                        </div>
                        <button v-for="(sug, idx) in searchSuggestions" :key="idx"
                            @mousedown="searchQuery = sug.text; showSuggestions = false"
                            class="w-full text-left px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800/60 flex items-center gap-2.5 transition-colors"
                        >
                            <Folder v-if="sug.type === 'title'" class="h-3.5 w-3.5 text-indigo-500 shrink-0" />
                            <User v-else-if="sug.type === 'author'" class="h-3.5 w-3.5 text-emerald-500 shrink-0" />
                            <Search v-else class="h-3.5 w-3.5 text-slate-400 shrink-0" />
                            <span class="truncate">{{ sug.text }}</span>
                            <span v-if="sug.type === 'title'" class="ml-auto text-[9px] font-bold uppercase tracking-wide text-indigo-455 bg-indigo-50 dark:bg-indigo-950/40 px-2 py-0.5 rounded-md">Prodi</span>
                            <span v-else-if="sug.type === 'author'" class="ml-auto text-[9px] font-bold uppercase tracking-wide text-emerald-455 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-md">Nama</span>
                        </button>
                    </div>

                    <!-- Search Internal Navigation Tabs (desktop only) -->
                    <div class="hidden md:flex items-center gap-0.5 bg-white dark:bg-zinc-900 rounded-full p-0.5 border border-slate-100 dark:border-zinc-800 shadow-xs shrink-0">
                        <Link href="/pagi" class="px-4 py-1.5 rounded-full text-[11px] font-bold text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-white transition-all">
                            Projects
                        </Link>
                        <Link href="/pagi/gallery" class="px-4 py-1.5 rounded-full text-[11px] font-bold text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-white transition-all">
                            Gallery
                        </Link>
                        <Link href="/pagi/people" class="px-4 py-1.5 rounded-full text-[11px] font-bold bg-slate-950 dark:bg-slate-50 text-white dark:text-slate-950 transition-all shadow-xs">
                            People
                        </Link>
                    </div>
                </div>

                <!-- Right: Sorting Dropdown (icon-only on mobile) -->
                <div class="relative shrink-0">
                    <button @click="showSortDropdown = !showSortDropdown" aria-label="Urutkan kreator" class="inline-flex items-center gap-1.5 rounded-full border border-slate-200/85 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-900 bg-white dark:bg-zinc-950 px-2.5 sm:px-5 py-2 text-xs font-bold text-slate-800 dark:text-zinc-200 shadow-xs transition-all">
                        <ListFilter class="h-3.5 w-3.5 text-slate-500" />
                        <span class="hidden sm:inline">{{ selectedSort }}</span>
                        <svg class="hidden sm:block w-3 h-3 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': showSortDropdown }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <!-- Sorting Options Dropdown Card -->
                    <div v-show="showSortDropdown" class="absolute right-0 top-full mt-2 w-48 rounded-2xl border border-slate-150 dark:border-zinc-850 bg-white dark:bg-zinc-900 shadow-lg p-1.5 z-50">
                        <button v-for="sortOpt in ['Recommended', 'Most Followers', 'Newest']" :key="sortOpt"
                            @click="selectedSort = sortOpt; showSortDropdown = false"
                            :class="['w-full text-left px-4 py-2 text-xs font-semibold rounded-xl transition-all',
                                selectedSort === sortOpt ? 'bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-950 font-bold' : 'text-slate-600 hover:bg-slate-50 dark:text-zinc-400 dark:hover:bg-zinc-800/60 dark:hover:text-white']"
                        >
                            {{ sortOpt }}
                        </button>
                    </div>
                </div>

            </div>
        </div>        <!-- GRID -->
        <main class="mx-auto max-w-[1400px] px-4 py-8 pb-24 md:pb-8">
            <!-- SKELETON LOADER -->
            <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 select-none">
                <div v-for="n in 8" :key="n" class="rounded-xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 overflow-hidden p-5 flex flex-col gap-4">
                    <div class="relative h-36 bg-slate-100 dark:bg-zinc-900/60 rounded-lg overflow-hidden flex items-center justify-center">
                        <Skeleton class="h-20 w-20 rounded-full border-4 border-white dark:border-zinc-900 absolute bottom-0 translate-y-1/2 shrink-0" />
                    </div>
                    <div class="pt-8 flex flex-col items-center gap-3">
                        <Skeleton class="h-4 w-32 rounded-full" />
                        <Skeleton class="h-3 w-24 rounded-full" />
                        <div class="flex gap-1.5 mt-2">
                            <Skeleton class="h-6 w-16 rounded-full" />
                            <Skeleton class="h-6 w-16 rounded-full" />
                        </div>
                        <div class="grid grid-cols-3 gap-4 w-full mt-4 border-t border-slate-100 dark:border-zinc-800 pt-4">
                            <div class="flex flex-col items-center gap-1.5">
                                <Skeleton class="h-4 w-8 rounded" />
                                <Skeleton class="h-2.5 w-10 rounded" />
                            </div>
                            <div class="flex flex-col items-center gap-1.5 border-x border-slate-100 dark:border-zinc-800 px-2">
                                <Skeleton class="h-4 w-8 rounded" />
                                <Skeleton class="h-2.5 w-10 rounded" />
                            </div>
                            <div class="flex flex-col items-center gap-1.5">
                                <Skeleton class="h-4 w-8 rounded" />
                                <Skeleton class="h-2.5 w-10 rounded" />
                            </div>
                        </div>
                        <Skeleton class="h-9 w-full rounded-lg mt-2" />
                    </div>
                </div>
            </div>

            <template v-else>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                    <LazyWrapper
                        v-for="p in visiblePeople"
                        :key="p.id"
                        placeholderClass="h-[280px]"
                    >
                        <div class="group rounded-xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-955 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-200 cursor-pointer"
                            @click="selectPerson(p, filteredPeople.indexOf(p))">

                        <!-- 3 images/videos horizontal strip -->
                        <div v-if="p.imgs && p.imgs.length > 0" class="relative grid grid-cols-3 gap-0.5 bg-slate-100 dark:bg-zinc-850 h-36">
                            <div v-for="(img, i) in p.imgs.slice(0, 3)" :key="i" class="overflow-hidden h-36 bg-slate-50 dark:bg-zinc-900">
                                <VideoLazy v-if="isVideoUrl(img)" :src="img" :autoplay="true" :loop="true" :muted="true" :playsinline="true" className="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500 pointer-events-none" />
                                <OptimizedImage v-else :src="img" className="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500" alt="Project thumbnail" />
                            </div>
                            <!-- Pad remaining columns if user has fewer than 3 works -->
                            <div v-for="i in Math.max(0, 3 - p.imgs.length)" :key="'pad-' + i" class="h-36 bg-linear-to-br from-slate-50 to-slate-100 dark:from-zinc-900/60 dark:to-zinc-850/60"></div>
                            <!-- Centered circular avatar overlapping bottom of strip -->
                            <div class="absolute left-1/2 -translate-x-1/2 bottom-0 translate-y-1/2 z-10">
                                <OptimizedImage :src="p.avatar"
                                    className="h-20 w-20 rounded-full border-4 border-white dark:border-zinc-900 object-cover shadow-lg"
                                    alt="Author avatar" />
                            </div>
                        </div>
                        <!-- Fallback if user has 0 works -->
                        <div v-else class="relative h-36 bg-linear-to-br from-indigo-500/20 via-purple-500/20 to-pink-500/20 dark:from-indigo-950/40 dark:via-purple-950/40 dark:to-pink-950/40">
                            <!-- Centered circular avatar overlapping bottom of strip -->
                            <div class="absolute left-1/2 -translate-x-1/2 bottom-0 translate-y-1/2 z-10">
                                <OptimizedImage :src="p.avatar"
                                    className="h-20 w-20 rounded-full border-4 border-white dark:border-zinc-900 object-cover shadow-lg"
                                    alt="Author avatar" />
                            </div>
                        </div>

                        <!-- Card body -->
                        <div class="px-5 pt-12 pb-5">
                            <!-- Name + location -->
                            <div class="text-center mb-3">
                                <div class="flex flex-col items-center justify-center gap-0.5 mb-1">
                                    <div class="flex items-center gap-1.5">
                                        <h3 class="text-sm font-bold text-slate-800 dark:text-zinc-150 leading-tight">{{ p.name }}</h3>
                                        <img src="/premium.svg" class="w-3.5 h-3.5 shrink-0 select-none" title="Akun Terverifikasi" alt="Verified Badge" />
                                    </div>
                                    <span v-if="p.pagi_username" class="text-[10px] font-bold text-slate-500 dark:text-slate-400">@{{ p.pagi_username }}</span>
                                </div>
                                <div class="flex items-center justify-center gap-1 text-xs text-slate-500">
                                    <MapPin class="h-3 w-3 shrink-0" /> {{ p.loc }}
                                </div>
                            </div>

                            <!-- Skills (limit to 2 tags, neat and tidy) -->
                            <div class="flex flex-wrap justify-center gap-1.5 mb-4 min-h-[26px]">
                                <span v-for="skill in p.skills.slice(0, 2)" :key="skill"
                                    class="rounded-full bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 px-2.5 py-1 text-[11px] font-bold tracking-tight">
                                    {{ skill }}
                                </span>
                            </div>

                            <!-- Stats -->
                            <div class="grid grid-cols-3 gap-2 mb-4">
                                <div class="text-center">
                                    <p class="text-sm font-black text-slate-800 dark:text-zinc-100">{{ p.appr }}</p>
                                    <p class="text-[10px] text-slate-400 dark:text-zinc-500">Likes</p>
                                </div>
                                <div class="text-center border-x border-slate-100 dark:border-zinc-800">
                                    <p class="text-sm font-black text-slate-800 dark:text-zinc-100">{{ p.fol }}</p>
                                    <p class="text-[10px] text-slate-400 dark:text-zinc-500">Followers</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm font-black text-slate-800 dark:text-zinc-100">{{ p.proj }}</p>
                                    <p class="text-[10px] text-slate-400 dark:text-zinc-500">Projects</p>
                                </div>
                            </div>

                            <!-- Message Button -->
                            <button v-if="p.id !== user.id" @click.stop="startChat(p.id)" class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 py-2 text-xs font-semibold text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-900 transition-colors">
                                Message {{ p.name.split(' ')[0] }}
                            </button>
                        </div>
                    </div>
                </LazyWrapper>
            </div>
                <!-- Load More — only appears when there are more than visibleCount people -->
                <div v-if="hasMore" class="mt-10 text-center">
                    <button @click="loadMore" class="rounded-xl border-2 border-slate-200 dark:border-zinc-800 px-8 py-3 text-sm font-bold text-slate-600 dark:text-zinc-400 hover:border-[#1769ff] hover:text-[#1769ff] transition-colors">
                        Load More <span class="opacity-50 text-xs ml-1">({{ filteredPeople.length - visibleCount }} lainnya)</span>
                    </button>
                </div>
            </template>
        </main>

        <!-- PEOPLE DETAIL SIDEBAR -->
        <SidebarPeople
            :person="selectedPerson"
            :current-index="currentIndex"
            :total="filteredPeople.length"
            @close="selectedPerson = null"
            @navigate="navigate"
        />

    </div>
</template>


