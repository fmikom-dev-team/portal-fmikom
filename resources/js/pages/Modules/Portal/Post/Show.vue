<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import {
	Calendar,
	Clock,
	Facebook,
	Instagram,
	Lightbulb,
	Linkedin,
	Share2,
	ChevronRight,
	Home,
	Flame,
	Tag,
	Copy,
	Check,
	Send,
	MessageSquare,
	ArrowLeft,
	ArrowRight,
	ExternalLink,
	CalendarDays,
	GraduationCap,
	BookOpen,
	List,
	Eye,
	Sparkles,
	Bookmark,
	Info,
	MoreHorizontal,
	ArrowUp,
	Coffee,
	Sun,
	Moon,
	Sunrise,
	Folder,
	FileText,
	Users,
	Mail,
	Plus,
	X,
	Search,
} from "lucide-vue-next";
import { computed, onMounted, onUnmounted, ref } from "vue";
import { sanitizeRich } from "@/composables/useSanitize";
import BlockRenderer from "@/components/editor/renderer/BlockRenderer.vue";
import PublicFooter from "@/components/Portal/PublicFooter.vue";
import PublicNavbar from "@/components/Portal/PublicNavbar.vue";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { countWordsFromEditorJs } from "@/utils/editorJsRenderer.js";

const props = defineProps({
	post: Object,
	relatedPosts: {
		type: Array,
		default: () => [],
	},
	popularPosts: {
		type: Array,
		default: () => [],
	},
	categories: {
		type: Array,
		default: () => [],
	},
	previousPost: Object,
	nextPost: Object,
	settings: Object,
});

const formatDate = (dateString) => {
	if (!dateString) return "";
	return new Date(dateString).toLocaleDateString("id-ID", {
		day: "numeric",
		month: "short",
		year: "numeric",
	});
};

const formatShortDate = (dateString) => {
	if (!dateString) return "";
	return new Date(dateString).toLocaleDateString("id-ID", {
		day: "numeric",
		month: "short",
	});
};

// Greeting based on current local hour
const greetingText = computed(() => {
	const hour = new Date().getHours();
	if (hour >= 5 && hour < 11) return "Good morning!";
	if (hour >= 11 && hour < 15) return "Good afternoon!";
	if (hour >= 15 && hour < 18) return "Good evening!";
	return "Good night!";
});

const showCommentSuccess = ref(false);
const isShareModalOpen = ref(false);
const isCopied = ref(false);
const isBookmarked = ref(false);
const isTocExpanded = ref(true);
const isTocDrawerOpen = ref(false);
const showBackToTop = ref(false);

const toggleBookmark = () => {
	isBookmarked.value = !isBookmarked.value;
};

const scrollToComments = () => {
	const el = document.getElementById("comments-section");
	if (el) el.scrollIntoView({ behavior: "smooth" });
};

const scrollToTop = () => {
	window.scrollTo({ top: 0, behavior: "smooth" });
};

const commentForm = useForm({
	author_name: "",
	author_email: "",
	content: "",
});

const submitComment = () => {
	const cleanText = (val) => {
		if (!val) return "";
		return val.replace(/<[^>]*>?/gm, "").trim();
	};

	commentForm.author_name = cleanText(commentForm.author_name);
	commentForm.author_email = cleanText(commentForm.author_email);
	commentForm.content = cleanText(commentForm.content);

	const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
	if (!emailRegex.test(commentForm.author_email)) {
		commentForm.errors.author_email = "Format email tidak valid.";
		return;
	}

	commentForm.post(`/berita/${props.post.slug}/comments`, {
		preserveScroll: true,
		onSuccess: () => {
			commentForm.reset();
			showCommentSuccess.value = true;
			setTimeout(() => {
				showCommentSuccess.value = false;
			}, 5000);
		},
	});
};

const scrollProgress = ref(0);
const updateScrollProgress = () => {
	const winScroll =
		document.body.scrollTop || document.documentElement.scrollTop;
	const height =
		document.documentElement.scrollHeight -
		document.documentElement.clientHeight;
	scrollProgress.value = height > 0 ? (winScroll / height) * 100 : 0;
	showBackToTop.value = winScroll > 300;
};

// Detect if content is Editor.js JSON or legacy HTML
const parsedContent = computed(() => {
	const content = props.post.content || "";
	if (!content) return null;
	try {
		const parsed = JSON.parse(content);
		if (parsed.blocks && Array.isArray(parsed.blocks)) return parsed;
	} catch {}
	return null;
});

const isEditorJs = computed(() => parsedContent.value !== null);
const legacyHtml = computed(() =>
	isEditorJs.value ? "" : props.post.content || "",
);

const toc = computed(() => {
	if (parsedContent.value?.blocks) {
		return parsedContent.value.blocks
			.filter((b) => b.type === "header")
			.map((b) => {
				const text = b.data?.text || "";
				const cleanText = text.replace(/<[^>]*>?/gm, "");
				const id = cleanText
					.toLowerCase()
					.replace(/[^a-z0-9]+/g, "-")
					.replace(/(^-|-$)+/g, "");
				return { id, text: cleanText, level: b.data?.level || 2 };
			});
	}
	return [];
});

const activeTocId = ref("");
const updateActiveToc = () => {
	const headings = Array.from(
		document.querySelectorAll("h1[id], h2[id], h3[id]"),
	);
	const scrollPosition = window.scrollY + 150;
	let current = "";
	for (const heading of headings) {
		if (heading.offsetTop <= scrollPosition) current = heading.id;
		else break;
	}
	activeTocId.value = current;
};

const currentUrl = ref("");

onMounted(() => {
	currentUrl.value = window.location.href;
	window.addEventListener("scroll", updateScrollProgress);
	window.addEventListener("scroll", updateActiveToc);
});

onUnmounted(() => {
	window.removeEventListener("scroll", updateScrollProgress);
	window.removeEventListener("scroll", updateActiveToc);
});

const scrollToToc = (id) => {
	const el = document.getElementById(id);
	if (el) {
		window.scrollTo({ top: el.offsetTop - 100, behavior: "smooth" });
		isTocDrawerOpen.value = false;
	}
};

const wordCount = computed(() => {
	if (parsedContent.value) return countWordsFromEditorJs(parsedContent.value);
	return (props.post.content || "").split(/\s+/).filter(Boolean).length;
});

const readingTime = computed(() => {
	const minutes = Math.ceil(wordCount.value / 225);
	return `${Math.max(1, minutes)} min`;
});

const copyLink = () => {
	if (navigator.clipboard) {
		navigator.clipboard.writeText(currentUrl.value);
		isCopied.value = true;
		setTimeout(() => (isCopied.value = false), 2500);
	}
};

const shareTo = (platform) => {
	const url = currentUrl.value;
	const text = props.post.title;
	let shareUrl = "";
	if (platform === "facebook")
		shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
	if (platform === "twitter")
		shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(text)}`;
	if (platform === "linkedin")
		shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`;
	if (platform === "whatsapp")
		shareUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(text + " " + url)}`;
	if (platform === "telegram")
		shareUrl = `https://t.me/share/url?url=${encodeURIComponent(url)}&text=${encodeURIComponent(text)}`;
	if (shareUrl) window.open(shareUrl, "_blank");
};

const getAvatarUrl = (user) => {
	if (!user?.foto_path) return null;
	return user.foto_path.startsWith("http")
		? user.foto_path
		: `/storage/${user.foto_path}`;
};
</script>

<template>
    <Head :title="post.title">
        <meta name="description" :content="post.meta_description || post.excerpt || post.title">
        <title>{{ post.title }} – Portal FMIKOM</title>
    </Head>

    <!-- Top Reading Progress Bar -->
    <div class="fixed top-0 left-0 w-full h-1 z-50 pointer-events-none bg-transparent">
        <div class="h-full bg-blue-600 dark:bg-blue-500 transition-all duration-150 ease-out" :style="{ width: scrollProgress + '%' }"></div>
    </div>

    <!-- Main Outer Wrapper -->
    <div class="min-h-screen bg-[#fdfcff] dark:bg-slate-950 font-sans antialiased text-[#08102b] dark:text-slate-100 transition-colors">
        <PublicNavbar />

        <!-- LEFT OUTLINE NAVIGATION RAIL (Docked on Left for Plus UI feel) -->
        <aside class="hidden 2xl:flex fixed left-4 top-28 flex-col items-center justify-between py-5 px-2 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs z-30 space-y-4">
            <div class="flex flex-col items-center gap-2">
                <!-- Home -->
                <Link
                    href="/"
                    class="size-10 rounded-xl flex items-center justify-center text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                    title="Beranda"
                >
                    <Home class="w-5 h-5 stroke-[1.75]" />
                </Link>

                <!-- Berita (Active indicator) -->
                <Link
                    href="/berita"
                    class="size-10 rounded-xl flex items-center justify-center text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/60 relative cursor-pointer"
                    title="Kumpulan Berita"
                >
                    <span class="size-1.5 rounded-full bg-blue-600 absolute left-1 top-1/2 -translate-y-1/2"></span>
                    <Folder class="w-5 h-5 stroke-[1.75]" />
                </Link>

                <!-- Kalender -->
                <Link
                    href="/halaman/kalender-akademik"
                    class="size-10 rounded-xl flex items-center justify-center text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                    title="Kalender Akademik"
                >
                    <Calendar class="w-5 h-5 stroke-[1.75]" />
                </Link>

                <!-- Event Timeline -->
                <Link
                    href="/event"
                    class="size-10 rounded-xl flex items-center justify-center text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                    title="Agenda & Event"
                >
                    <Sparkles class="w-5 h-5 stroke-[1.75]" />
                </Link>

                <!-- Civitas & Mahasiswa -->
                <Link
                    href="/halaman/profil-fakultas"
                    class="size-10 rounded-xl flex items-center justify-center text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                    title="Profil Fakultas"
                >
                    <Users class="w-5 h-5 stroke-[1.75]" />
                </Link>

                <!-- Layanan Mahasiswa FAST -->
                <Link
                    href="/modules/fast"
                    class="size-10 rounded-xl flex items-center justify-center text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                    title="Layanan FAST"
                >
                    <Mail class="w-5 h-5 stroke-[1.75]" />
                </Link>
            </div>

            <!-- Bottom Plus Action -->
            <button
                @click="isShareModalOpen = true"
                class="size-10 rounded-xl flex items-center justify-center text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                title="Bagikan Halaman Ini"
            >
                <Plus class="w-5 h-5 stroke-[1.75]" />
            </button>
        </aside>

        <!-- Main Page Container -->
        <main class="relative pt-24 pb-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- 2-Column Grid (Main Article 68% + Sticky Sidebar 32%) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">
                    
                    <!-- ============================================== -->
                    <!-- LEFT COLUMN: ARTICLE CONTENT (lg:col-span-8)   -->
                    <!-- ============================================== -->
                    <div class="lg:col-span-8 space-y-6">

                        <!-- Main Article Card -->
                        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 md:p-10 border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-6">
                            
                            <!-- 1. Header / Greeting / Breadcrumb Block -->
                            <div class="space-y-3">
                                <!-- Greeting Pill (Plus UI Good Afternoon Style) -->
                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-xl text-xs font-semibold bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700 text-slate-700 dark:text-slate-300 shadow-2xs">
                                    <Coffee class="w-3.5 h-3.5 text-amber-500 stroke-[1.75]" />
                                    <span>{{ greetingText }}</span>
                                </div>

                                <!-- Breadcrumb: Home / Documentation -->
                                <nav class="flex items-center gap-1.5 text-xs font-medium text-slate-400 dark:text-slate-500">
                                    <Link href="/" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                        Home
                                    </Link>
                                    <span class="text-slate-300 dark:text-slate-600">/</span>
                                    <Link :href="post.category ? `/berita?category=${post.category.slug || post.category.id}` : '/berita'" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                        {{ post.category?.name || 'Berita' }}
                                    </Link>
                                </nav>
                            </div>

                            <!-- 2. Post Title (Bold Plus UI H1 Font) -->
                            <h1 class="text-2xl sm:text-3xl lg:text-[34px] font-extrabold text-[#08102b] dark:text-white leading-[1.25] tracking-tight text-pretty">
                                {{ post.title }}
                            </h1>

                            <!-- 3. Subtitle / Lead Excerpt -->
                            <p v-if="post.excerpt" class="text-sm sm:text-base text-slate-500 dark:text-slate-400 font-normal leading-relaxed">
                                {{ post.excerpt }}
                            </p>

                            <!-- 4. Author Row & Action Icons (Plus UI Outline Style) -->
                            <div class="space-y-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                                <div class="flex items-center justify-between gap-4">
                                    <!-- Author Avatar & Name -->
                                    <div class="flex items-center gap-2.5">
                                        <Avatar class="size-7 border border-slate-200 dark:border-slate-700">
                                            <AvatarImage v-if="post.user?.foto_path" :src="getAvatarUrl(post.user)" :alt="post.user?.name" class="object-cover" />
                                            <AvatarFallback class="font-bold text-xs bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                                                {{ (post.user?.name || 'A').charAt(0).toUpperCase() }}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">
                                            <span>Published by </span>
                                            <span class="font-bold text-[#08102b] dark:text-white">{{ post.user?.name || 'Humas FMIKOM' }}</span>
                                        </div>
                                    </div>

                                    <!-- Right Outline Action Icons (Bookmark, Comment, Share) -->
                                    <div class="flex items-center gap-1.5 sm:gap-2">
                                        <!-- Bookmark Icon -->
                                        <button
                                            @click="toggleBookmark"
                                            :class="[
                                                'size-8 rounded-lg flex items-center justify-center transition-colors cursor-pointer',
                                                isBookmarked ? 'text-blue-600 bg-blue-50 dark:bg-blue-950/50' : 'text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800'
                                            ]"
                                            title="Simpan Berita"
                                        >
                                            <Bookmark class="w-4 h-4 stroke-[1.75]" :class="{ 'fill-current': isBookmarked }" />
                                        </button>

                                        <!-- Comment Icon -->
                                        <button
                                            @click="scrollToComments"
                                            class="size-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                                            title="Lihat Komentar"
                                        >
                                            <MessageSquare class="w-4 h-4 stroke-[1.75]" />
                                        </button>

                                        <!-- Share Icon -->
                                        <button
                                            @click="isShareModalOpen = true"
                                            class="size-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                                            title="Bagikan"
                                        >
                                            <Send class="w-4 h-4 stroke-[1.75]" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Secondary Meta Date Line -->
                                <div class="text-[11px] sm:text-xs text-slate-400 dark:text-slate-500 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                    <span>Published: {{ formatDate(post.published_at || post.created_at) }}</span>
                                    <span>·</span>
                                    <span>Estimated read time: {{ readingTime }}</span>
                                </div>
                            </div>

                            <!-- 5. Callout / Notice Box (Plus UI Blue Outline Alert Box) -->
                            <div class="rounded-2xl border border-blue-200/90 dark:border-blue-900/80 bg-blue-50/40 dark:bg-blue-950/20 p-4 sm:p-4.5 flex items-start gap-3 text-xs leading-relaxed text-slate-700 dark:text-slate-300">
                                <div class="size-6 rounded-full bg-blue-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                                    <Info class="w-3.5 h-3.5 stroke-[2.5]" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="font-bold text-blue-900 dark:text-blue-300">Informasi Resmi FMIKOM UNUGHA:</span>
                                    <p class="mt-0.5 text-slate-600 dark:text-slate-400">
                                        Seluruh informasi yang dipublikasikan telah diverifikasi oleh Tim Humas & Akademik Fakultas Manajemen dan Ilmu Komputer UNUGHA Cilacap.
                                    </p>
                                </div>
                            </div>

                            <!-- 6. In-Article Table of Contents Toggle (Plus UI Style) -->
                            <div v-if="toc.length > 0" class="border-y border-slate-100 dark:border-slate-800 py-3">
                                <div class="flex items-center justify-between cursor-pointer select-none" @click="isTocExpanded = !isTocExpanded">
                                    <span class="text-xs font-bold text-[#08102b] dark:text-white flex items-center gap-2">
                                        <List class="w-4 h-4 text-slate-400 stroke-[1.75]" />
                                        <span>Table of Contents</span>
                                    </span>
                                    <button class="text-xs font-semibold text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                        {{ isTocExpanded ? 'Hide' : 'Show all' }}
                                    </button>
                                </div>

                                <!-- Collapsible ToC List -->
                                <div v-if="isTocExpanded" class="mt-3 pt-3 border-t border-slate-50 dark:border-slate-800/60">
                                    <ol class="space-y-2 text-xs text-slate-600 dark:text-slate-400 list-decimal list-inside">
                                        <li v-for="(item, idx) in toc" :key="item.id" class="leading-relaxed">
                                            <button
                                                @click="scrollToToc(item.id)"
                                                :class="[
                                                    'hover:text-blue-600 dark:hover:text-blue-400 transition-colors cursor-pointer text-left',
                                                    activeTocId === item.id ? 'font-bold text-blue-600 dark:text-blue-400' : ''
                                                ]"
                                            >
                                                {{ item.text }}
                                            </button>
                                        </li>
                                    </ol>
                                </div>
                            </div>

                            <!-- 7. Hero Featured Image -->
                            <div v-if="post.thumbnail" class="w-full aspect-[16/9] rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-800 shadow-2xs relative">
                                <img :src="post.thumbnail" :alt="post.title" class="w-full h-full object-cover">
                            </div>

                            <!-- 8. Article Body (Prose Tipografi Tajam Plus UI) -->
                            <article class="prose prose-slate md:prose-lg max-w-none dark:prose-invert
                                prose-headings:font-bold prose-headings:tracking-tight prose-headings:text-[#08102b] dark:prose-headings:text-white
                                prose-p:text-slate-600 dark:prose-p:text-slate-300 prose-p:leading-relaxed prose-p:my-5
                                prose-h2:text-2xl md:prose-h2:text-[28px] prose-h2:mt-10 prose-h2:mb-4
                                prose-h3:text-lg md:prose-h3:text-xl prose-h3:mt-8 prose-h3:mb-3
                                prose-a:text-blue-600 dark:prose-a:text-blue-400 prose-a:underline hover:prose-a:text-blue-700
                                prose-img:rounded-2xl prose-img:border prose-img:border-slate-200 dark:prose-img:border-slate-700 prose-img:my-8 prose-img:shadow-2xs
                                prose-blockquote:border-l-4 prose-blockquote:border-blue-600 dark:prose-blockquote:border-blue-500 prose-blockquote:bg-blue-50/50 dark:prose-blockquote:bg-blue-950/30 prose-blockquote:p-4 prose-blockquote:rounded-r-xl prose-blockquote:italic prose-blockquote:text-slate-700 dark:prose-blockquote:text-slate-200 prose-blockquote:my-6
                                prose-ul:my-5 prose-ul:list-disc prose-ul:pl-6 prose-li:my-1.5 prose-li:text-slate-600 dark:prose-li:text-slate-300
                                prose-ol:my-5 prose-ol:list-decimal prose-ol:pl-6
                                prose-table:border-collapse prose-table:w-full prose-table:my-6
                                prose-th:text-[#08102b] dark:prose-th:text-white prose-th:font-semibold prose-th:border-b prose-th:border-slate-200 dark:prose-th:border-slate-700 prose-th:pb-2 prose-th:text-left
                                prose-td:py-3 prose-td:border-b prose-td:border-slate-100 dark:prose-td:border-slate-800 prose-td:text-slate-600 dark:prose-td:text-slate-300
                            ">
                                <BlockRenderer v-if="isEditorJs" :data="parsedContent" />
                                <div v-else v-html="sanitizeRich(legacyHtml)" />
                            </article>

                            <!-- 9. Share Pill Buttons Bar -->
                            <div class="pt-6 border-t border-slate-100 dark:border-slate-800 space-y-3">
                                <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">
                                    Bagikan Berita Ini:
                                </span>
                                <div class="flex flex-wrap items-center gap-2">
                                    <!-- WhatsApp -->
                                    <button
                                        @click="shareTo('whatsapp')"
                                        class="px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-500 hover:bg-emerald-600 text-white flex items-center gap-2 transition-transform active:scale-95 shadow-2xs cursor-pointer"
                                    >
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.004 2C6.48 2 2 6.48 2 12c0 2.17.7 4.19 1.89 5.83L2.5 22.5l4.87-1.39A9.97 9.97 0 0012 22c5.52 0 10-4.48 10-10S17.52 2 12.004 2zm5.72 13.9c-.24.67-1.18 1.25-1.63 1.34-.45.09-.9.18-2.92-.61-2.58-1-4.22-3.61-4.35-3.79-.13-.18-1.07-1.42-1.07-2.7 0-1.28.67-1.92.94-2.19.27-.27.59-.34.79-.34.2 0 .4 0 .58.01.19.01.44-.07.69.53.25.61.85 2.08.93 2.23.08.15.13.33.03.53-.1.2-.2.32-.39.53-.19.21-.4.47-.57.63-.19.18-.39.38-.17.76.22.38.98 1.62 2.1 2.62 1.44 1.28 2.64 1.67 3.02 1.86.38.19.61.16.83-.09.23-.25.96-1.12 1.22-1.5.26-.38.53-.32.89-.19.36.13 2.29 1.08 2.39 1.13.1.05.17.26.11.45z"/></svg>
                                        <span>WhatsApp</span>
                                    </button>

                                    <!-- X / Twitter -->
                                    <button
                                        @click="shareTo('twitter')"
                                        class="px-3.5 py-2 rounded-xl text-xs font-bold bg-[#08102b] hover:bg-black text-white flex items-center gap-2 transition-transform active:scale-95 shadow-2xs cursor-pointer"
                                    >
                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.008 4.15H5.078z"/></svg>
                                        <span>X (Twitter)</span>
                                    </button>

                                    <!-- Facebook -->
                                    <button
                                        @click="shareTo('facebook')"
                                        class="px-3.5 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white flex items-center gap-2 transition-transform active:scale-95 shadow-2xs cursor-pointer"
                                    >
                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M9 8H7v3h2v9h3v-9h3l.5-3H12V6c0-.88.39-1 1-1h2V2h-3c-2.9 0-5 1.55-5 4.5V8z"/></svg>
                                        <span>Facebook</span>
                                    </button>

                                    <!-- Telegram -->
                                    <button
                                        @click="shareTo('telegram')"
                                        class="px-3.5 py-2 rounded-xl text-xs font-bold bg-sky-500 hover:bg-sky-600 text-white flex items-center gap-2 transition-transform active:scale-95 shadow-2xs cursor-pointer"
                                    >
                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-1-.65-.35-1 .22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.08-.06-.19-.04-.27-.02-.12.02-1.96 1.25-5.54 3.69-.52.36-1 .53-1.42.52-.47-.01-1.37-.26-2.03-.48-.82-.27-1.47-.42-1.42-.88.03-.24.35-.49.97-.74 3.79-1.64 6.32-2.73 7.59-3.26 3.61-1.5 4.36-1.76 4.85-1.77.11 0 .35.03.5.15.13.12.17.29.18.47z"/></svg>
                                        <span>Telegram</span>
                                    </button>

                                    <!-- Copy Link Pill -->
                                    <button
                                        @click="copyLink"
                                        class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-[#08102b] dark:text-white flex items-center gap-1.5 transition-colors cursor-pointer"
                                    >
                                        <Check v-if="isCopied" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                                        <Copy v-else class="w-3.5 h-3.5 text-slate-400" />
                                        <span>{{ isCopied ? 'Tersalin!' : 'Salin Link' }}</span>
                                    </button>
                                </div>
                            </div>

                            <!-- 10. Author Profile Box (Plus UI Editorial Credibility) -->
                            <div class="p-5 rounded-2xl bg-slate-50/80 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-800 flex items-start sm:items-center gap-4">
                                <Avatar class="size-12 border border-slate-200 dark:border-slate-700 shrink-0">
                                    <AvatarImage v-if="post.user?.foto_path" :src="getAvatarUrl(post.user)" :alt="post.user?.name" class="object-cover" />
                                    <AvatarFallback class="font-bold text-base bg-blue-600 text-white">
                                        {{ (post.user?.name || 'FMIKOM').charAt(0).toUpperCase() }}
                                    </AvatarFallback>
                                </Avatar>
                                <div class="space-y-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-bold text-[#08102b] dark:text-white">
                                            {{ post.user?.name || 'Tim Publikasi & Humas FMIKOM' }}
                                        </h4>
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300">
                                            Penulis
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                        Fakultas Manajemen dan Ilmu Komputer – Universitas Nahdlatul Ulama Al Ghazali (UNUGHA) Cilacap.
                                    </p>
                                </div>
                            </div>

                            <!-- 11. Previous & Next Article Navigation Cards -->
                            <div v-if="previousPost || nextPost" class="pt-6 border-t border-slate-100 dark:border-slate-800 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Previous -->
                                <Link
                                    v-if="previousPost"
                                    :href="`/berita/${previousPost.slug}`"
                                    class="group p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-[#fdfcff] dark:bg-slate-800/40 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all flex flex-col justify-between"
                                >
                                    <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider flex items-center gap-1 group-hover:text-blue-600 transition-colors mb-2">
                                        <ArrowLeft class="w-3.5 h-3.5" /> Sebelumnya
                                    </span>
                                    <span class="text-xs sm:text-sm font-bold text-[#08102b] dark:text-slate-200 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors line-clamp-2">
                                        {{ previousPost.title }}
                                    </span>
                                </Link>
                                <div v-else class="hidden sm:block"></div>

                                <!-- Next -->
                                <Link
                                    v-if="nextPost"
                                    :href="`/berita/${nextPost.slug}`"
                                    class="group p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-[#fdfcff] dark:bg-slate-800/40 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all flex flex-col justify-between text-right"
                                >
                                    <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider flex items-center justify-end gap-1 group-hover:text-blue-600 transition-colors mb-2">
                                        Selanjutnya <ArrowRight class="w-3.5 h-3.5" />
                                    </span>
                                    <span class="text-xs sm:text-sm font-bold text-[#08102b] dark:text-slate-200 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors line-clamp-2">
                                        {{ nextPost.title }}
                                    </span>
                                </Link>
                            </div>

                        </div>

                        <!-- 12. Comments Section -->
                        <section id="comments-section" v-if="settings?.allow_comments !== '0'" class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-base font-bold text-[#08102b] dark:text-white flex items-center gap-2">
                                    <MessageSquare class="w-4 h-4 text-blue-600 stroke-[1.75]" />
                                    <span>Komentar</span>
                                </h3>
                                <span class="text-xs font-bold px-2.5 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-full">
                                    {{ post.comments?.length || 0 }}
                                </span>
                            </div>

                            <!-- Comment List -->
                            <div v-if="post.comments?.length" class="space-y-4">
                                <div
                                    v-for="comment in post.comments"
                                    :key="comment.id"
                                    class="p-4 rounded-2xl bg-slate-50/60 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-800 flex gap-3.5"
                                >
                                    <Avatar class="size-8 border border-slate-200 dark:border-slate-700 shrink-0">
                                        <AvatarImage :src="`https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(comment.author_name)}&backgroundColor=f1f5f9`" />
                                        <AvatarFallback>{{ comment.author_name.charAt(0).toUpperCase() }}</AvatarFallback>
                                    </Avatar>
                                    <div class="space-y-1 flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="font-bold text-[#08102b] dark:text-white text-xs sm:text-sm">{{ comment.author_name }}</span>
                                            <span class="text-[11px] text-slate-400 dark:text-slate-500">{{ formatDate(comment.created_at) }}</span>
                                        </div>
                                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line">
                                            {{ comment.content }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-xs text-slate-400 dark:text-slate-500 italic p-4 rounded-2xl bg-slate-50/40 dark:bg-slate-800/20 text-center">
                                Belum ada komentar. Jadilah yang pertama memberikan tanggapan!
                            </div>

                            <!-- Comment Form -->
                            <div class="space-y-4 pt-2">
                                <h4 class="text-sm font-bold text-[#08102b] dark:text-white">Tulis Komentar</h4>

                                <div v-if="showCommentSuccess" class="mb-4">
                                    <Alert class="bg-emerald-50 dark:bg-emerald-950/50 border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 rounded-xl">
                                        <Lightbulb class="h-4 w-4 text-emerald-600 stroke-[1.75]" />
                                        <AlertTitle class="font-bold text-sm">Komentar Terkirim</AlertTitle>
                                        <AlertDescription class="text-xs">
                                            Terima kasih! Komentar Anda berhasil dikirimkan.
                                        </AlertDescription>
                                    </Alert>
                                </div>

                                <form @submit.prevent="submitComment" class="space-y-3">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <input
                                            v-model="commentForm.author_name"
                                            required
                                            placeholder="Nama Lengkap *"
                                            class="w-full px-3.5 py-2.5 bg-[#fdfcff] dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all placeholder:text-slate-400"
                                        />
                                        <input
                                            type="email"
                                            v-model="commentForm.author_email"
                                            required
                                            placeholder="Alamat Email *"
                                            class="w-full px-3.5 py-2.5 bg-[#fdfcff] dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all placeholder:text-slate-400"
                                        />
                                    </div>

                                    <textarea
                                        rows="3"
                                        v-model="commentForm.content"
                                        required
                                        placeholder="Tuliskan komentar atau tanggapan Anda di sini..."
                                        class="w-full px-3.5 py-2.5 bg-[#fdfcff] dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all placeholder:text-slate-400 resize-none"
                                    ></textarea>

                                    <div class="flex justify-end">
                                        <button
                                            type="submit"
                                            :disabled="commentForm.processing"
                                            class="h-10 px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all disabled:opacity-50 cursor-pointer flex items-center gap-2 shadow-xs"
                                        >
                                            <Send class="w-3.5 h-3.5 stroke-[1.75]" />
                                            <span>Post Comment</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </section>

                    </div>

                    <!-- ============================================== -->
                    <!-- RIGHT COLUMN: STICKY SIDEBAR (lg:col-span-4)   -->
                    <!-- ============================================== -->
                    <aside class="lg:col-span-4 space-y-6">

                        <!-- WIDGET 1: Popular Posts (Plus UI Design Match) -->
                        <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 sm:p-6 border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                                <h3 class="text-sm font-bold text-[#08102b] dark:text-white tracking-tight flex items-center gap-2">
                                    <span>Popular Posts</span>
                                    <MoreHorizontal class="w-4 h-4 text-slate-400 stroke-[1.75]" />
                                </h3>
                                <div class="grid grid-cols-2 gap-0.5 text-slate-300 dark:text-slate-600">
                                    <span class="size-1 rounded-full bg-current"></span>
                                    <span class="size-1 rounded-full bg-current"></span>
                                    <span class="size-1 rounded-full bg-current"></span>
                                    <span class="size-1 rounded-full bg-current"></span>
                                </div>
                            </div>

                            <div v-if="popularPosts && popularPosts.length" class="space-y-4">
                                <!-- Top Featured Popular Post (#1 Card with Abstract Cover / Image) -->
                                <Link
                                    v-if="popularPosts[0]"
                                    :href="`/berita/${popularPosts[0].slug}`"
                                    class="group block relative rounded-2xl overflow-hidden border border-slate-100 dark:border-slate-800 shadow-2xs"
                                >
                                    <div class="aspect-[16/10] w-full bg-gradient-to-br from-pink-500 via-rose-500 to-indigo-600 overflow-hidden relative flex flex-col justify-between p-4">
                                        <!-- Background Image if exists -->
                                        <img
                                            v-if="popularPosts[0].thumbnail"
                                            :src="popularPosts[0].thumbnail"
                                            :alt="popularPosts[0].title"
                                            class="absolute inset-0 w-full h-full object-cover mix-blend-overlay opacity-50 transition-transform duration-500 group-hover:scale-105"
                                        />
                                        
                                        <!-- Top row: Category tag & bookmark icon -->
                                        <div class="flex items-center justify-between z-10">
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-white/20 text-white backdrop-blur-xs uppercase">
                                                {{ popularPosts[0].category?.name || 'Featured' }}
                                            </span>
                                            <div class="size-7 rounded-lg bg-black/20 backdrop-blur-xs flex items-center justify-center text-white">
                                                <Bookmark class="w-3.5 h-3.5 stroke-[2]" />
                                            </div>
                                        </div>

                                        <!-- Title overlay -->
                                        <div class="z-10 space-y-2 mt-4">
                                            <h4 class="text-sm sm:text-base font-bold text-white leading-snug line-clamp-2 drop-shadow-xs">
                                                {{ popularPosts[0].title }}
                                            </h4>
                                            <!-- Author pill with verified badge -->
                                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-black/40 backdrop-blur-md text-[11px] font-semibold text-white">
                                                <Avatar class="size-4 border border-white/40">
                                                    <AvatarFallback class="text-[8px] bg-blue-600 text-white">
                                                        {{ (popularPosts[0].user?.name || 'H').charAt(0) }}
                                                    </AvatarFallback>
                                                </Avatar>
                                                <span>{{ popularPosts[0].user?.name || 'Humas FMIKOM' }}</span>
                                                <span class="size-3 rounded-full bg-blue-500 text-white text-[8px] flex items-center justify-center">✓</span>
                                            </div>
                                        </div>
                                    </div>
                                </Link>

                                <!-- Numbered List Underneath (#1, #2, #3, #4) -->
                                <div class="space-y-3.5 divide-y divide-slate-100 dark:divide-slate-800">
                                    <div
                                        v-for="(popPost, idx) in popularPosts.slice(1)"
                                        :key="popPost.id"
                                        class="pt-3.5 first:pt-0"
                                    >
                                        <Link :href="`/berita/${popPost.slug}`" class="group block space-y-1">
                                            <!-- Date and category meta line: "Oct 27 — in Documentation" -->
                                            <div class="text-[11px] text-slate-400 dark:text-slate-500 font-medium flex items-center gap-1">
                                                <span>{{ formatShortDate(popPost.published_at || popPost.created_at) }}</span>
                                                <span>— in</span>
                                                <span class="text-slate-600 dark:text-slate-400">{{ popPost.category?.name || 'Berita' }}</span>
                                            </div>

                                            <!-- Numbered Title: "#1 Title" -->
                                            <h5 class="text-xs sm:text-sm font-bold text-[#08102b] dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors line-clamp-2 leading-snug">
                                                <span class="text-slate-400 font-semibold mr-0.5">#{{ idx + 1 }}</span>
                                                {{ popPost.title }}
                                            </h5>

                                            <!-- Excerpt snippet -->
                                            <p v-if="popPost.excerpt" class="text-[11px] text-slate-400 dark:text-slate-500 line-clamp-2 leading-relaxed">
                                                {{ popPost.excerpt }}
                                            </p>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-xs text-slate-400 italic py-2">
                                Belum ada berita populer lainnya.
                            </div>
                        </div>

                        <!-- WIDGET 2: Labels (Plus UI Design Match) -->
                        <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 sm:p-6 border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                                <h3 class="text-sm font-bold text-[#08102b] dark:text-white tracking-tight flex items-center gap-2">
                                    <span>Labels</span>
                                    <MoreHorizontal class="w-4 h-4 text-slate-400 stroke-[1.75]" />
                                </h3>
                                <div class="grid grid-cols-2 gap-0.5 text-slate-300 dark:text-slate-600">
                                    <span class="size-1 rounded-full bg-current"></span>
                                    <span class="size-1 rounded-full bg-current"></span>
                                    <span class="size-1 rounded-full bg-current"></span>
                                    <span class="size-1 rounded-full bg-current"></span>
                                </div>
                            </div>

                            <div v-if="categories && categories.length" class="flex flex-wrap gap-2">
                                <Link
                                    v-for="cat in categories"
                                    :key="cat.id"
                                    :href="`/berita?category=${cat.slug || cat.id}`"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100/80 dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-blue-950/50 text-[#08102b] dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400 border border-slate-200/60 dark:border-slate-700/60 transition-colors group"
                                >
                                    <span>{{ cat.name }}</span>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">({{ cat.posts_count || 0 }})</span>
                                </Link>
                            </div>
                            <div v-else class="text-xs text-slate-400 italic py-2">
                                Belum ada kategori terdaftar.
                            </div>

                            <Link href="/berita" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1 pt-1">
                                <span>Show more</span>
                                <ChevronRight class="w-3.5 h-3.5 stroke-[2]" />
                            </Link>
                        </div>

                    </aside>

                </div>

                <!-- 3. Bottom Full-Width Section: Related Posts -->
                <section v-if="relatedPosts && relatedPosts.length" class="pt-10 border-t border-slate-200/80 dark:border-slate-800 space-y-6">
                    <div class="flex items-center justify-between">
                        <div class="space-y-0.5">
                            <span class="text-[11px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider block">
                                Rekomendasi
                            </span>
                            <h3 class="text-lg sm:text-xl font-bold text-[#08102b] dark:text-white tracking-tight">
                                Berita Terkait Lainnya
                            </h3>
                        </div>
                        <Link href="/berita" class="text-xs font-bold text-blue-600 hover:underline flex items-center gap-1 shrink-0">
                            <span>Lihat Semua</span>
                            <ArrowRight class="w-3.5 h-3.5 stroke-[2]" />
                        </Link>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                        <Link
                            v-for="relPost in relatedPosts.slice(0, 3)"
                            :key="relPost.id"
                            :href="`/berita/${relPost.slug}`"
                            class="group flex flex-col bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 overflow-hidden shadow-2xs hover:shadow-md transition-all duration-300"
                        >
                            <!-- Thumbnail -->
                            <div class="w-full aspect-[16/10] bg-slate-100 dark:bg-slate-800 overflow-hidden relative">
                                <img
                                    v-if="relPost.thumbnail"
                                    :src="relPost.thumbnail"
                                    :alt="relPost.title"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-slate-300 dark:text-slate-600">
                                    <BookOpen class="w-10 h-10 stroke-[1.5]" />
                                </div>
                                <span v-if="relPost.category?.name" class="absolute top-3 left-3 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-white/90 dark:bg-slate-900/90 text-[#08102b] dark:text-slate-200 backdrop-blur-xs shadow-2xs">
                                    {{ relPost.category.name }}
                                </span>
                            </div>

                            <!-- Body -->
                            <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between space-y-3">
                                <div class="space-y-1.5">
                                    <span class="text-[11px] font-medium text-slate-400 dark:text-slate-500 block">
                                        {{ formatDate(relPost.published_at || relPost.created_at) }}
                                    </span>
                                    <h4 class="text-xs sm:text-sm font-bold text-[#08102b] dark:text-white leading-snug group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors line-clamp-2">
                                        {{ relPost.title }}
                                    </h4>
                                </div>

                                <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                                    <Avatar class="size-5 border border-slate-200 dark:border-slate-700">
                                        <AvatarImage v-if="relPost.user?.foto_path" :src="getAvatarUrl(relPost.user)" :alt="relPost.user?.name" class="object-cover" />
                                        <AvatarFallback class="text-[8px]">{{ (relPost.user?.name || 'A').charAt(0) }}</AvatarFallback>
                                    </Avatar>
                                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-300 truncate">
                                        {{ relPost.user?.name || 'Humas FMIKOM' }}
                                    </span>
                                </div>
                            </div>
                        </Link>
                    </div>
                </section>

            </div>
        </main>

        <!-- FLOATING TABLE OF CONTENTS FLYOUT BUTTON (Right Edge Button in Screenshot 1) -->
        <button
            v-if="toc.length > 0"
            @click="isTocDrawerOpen = true"
            class="fixed right-4 top-1/2 -translate-y-1/2 size-10 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-md text-slate-700 dark:text-slate-200 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/50 flex items-center justify-center z-40 transition-transform active:scale-95 cursor-pointer"
            title="Buka Table of Contents"
        >
            <List class="w-4 h-4 stroke-[2]" />
        </button>

        <!-- TABLE OF CONTENTS SLIDEOUT DRAWER (Screenshot 2 Match) -->
        <div v-if="isTocDrawerOpen" class="fixed inset-0 bg-slate-950/40 backdrop-blur-xs z-50 flex justify-end animate-in fade-in duration-200" @click.self="isTocDrawerOpen = false">
            <div class="w-full max-w-xs sm:max-w-sm bg-white dark:bg-slate-900 h-full border-l border-slate-200 dark:border-slate-800 shadow-2xl p-6 flex flex-col justify-between overflow-y-auto animate-in slide-in-from-right duration-300">
                <div class="space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-sm font-bold text-[#08102b] dark:text-white">Table of contents</span>
                        <button @click="isTocDrawerOpen = false" class="text-xs font-bold text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center gap-1 cursor-pointer">
                            <span>Close</span>
                            <X class="w-3.5 h-3.5" />
                        </button>
                    </div>

                    <ol class="space-y-3 text-xs text-slate-600 dark:text-slate-300 list-decimal list-inside">
                        <li v-for="item in toc" :key="item.id">
                            <button
                                @click="scrollToToc(item.id)"
                                :class="[
                                    'text-left font-medium hover:text-blue-600 dark:hover:text-blue-400 transition-colors cursor-pointer',
                                    activeTocId === item.id ? 'font-bold text-blue-600 dark:text-blue-400' : ''
                                ]"
                            >
                                {{ item.text }}
                            </button>
                        </li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- FLOATING BACK TO TOP BUTTON (Bottom Right Outline Circle in Plus UI) -->
        <button
            v-if="showBackToTop"
            @click="scrollToTop"
            class="fixed bottom-6 right-6 size-10 rounded-full bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-md text-slate-600 dark:text-slate-300 hover:text-blue-600 hover:border-blue-300 transition-all flex items-center justify-center z-40 cursor-pointer"
            title="Kembali ke Atas"
        >
            <ArrowUp class="w-4 h-4 stroke-[2]" />
        </button>

        <!-- Share Modal (Plus UI Style) -->
        <div v-if="isShareModalOpen" class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-[9999] flex items-center justify-center p-4 animate-in fade-in duration-200" style="z-index: 9999;" @click.self="isShareModalOpen = false">
            <div class="bg-white dark:bg-slate-900 rounded-3xl w-full max-w-md shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden relative animate-in fade-in zoom-in-95 duration-200">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-sm font-bold text-[#08102b] dark:text-white">Bagikan Berita Ini</span>
                    <button @click="isShareModalOpen = false" class="text-xs text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors font-bold cursor-pointer">
                        &times;
                    </button>
                </div>

                <!-- Modal Content Preview -->
                <div class="flex gap-3.5 p-4 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                    <img v-if="post.thumbnail" :src="post.thumbnail" :alt="post.title" class="w-14 h-14 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shrink-0">
                    <div class="flex flex-col justify-center min-w-0">
                        <span v-if="post.category?.name" class="text-[10px] font-bold text-blue-600 dark:text-blue-400 block uppercase">
                            {{ post.category.name }}
                        </span>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 leading-snug line-clamp-2">
                            {{ post.title }}
                        </h4>
                    </div>
                </div>

                <!-- Share Grid -->
                <div class="grid grid-cols-4 gap-4 p-5">
                    <button @click="shareTo('whatsapp'); isShareModalOpen = false" class="group block text-center cursor-pointer">
                        <div class="size-11 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 hover:bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto transition-colors border border-emerald-200/60 dark:border-emerald-800/60">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12.004 2C6.48 2 2 6.48 2 12c0 2.17.7 4.19 1.89 5.83L2.5 22.5l4.87-1.39A9.97 9.97 0 0012 22c5.52 0 10-4.48 10-10S17.52 2 12.004 2zm5.72 13.9c-.24.67-1.18 1.25-1.63 1.34-.45.09-.9.18-2.92-.61-2.58-1-4.22-3.61-4.35-3.79-.13-.18-1.07-1.42-1.07-2.7 0-1.28.67-1.92.94-2.19.27-.27.59-.34.79-.34.2 0 .4 0 .58.01.19.01.44-.07.69.53.25.61.85 2.08.93 2.23.08.15.13.33.03.53-.1.2-.2.32-.39.53-.19.21-.4.47-.57.63-.19.18-.39.38-.17.76.22.38.98 1.62 2.1 2.62 1.44 1.28 2.64 1.67 3.02 1.86.38.19.61.16.83-.09.23-.25.96-1.12 1.22-1.5.26-.38.53-.32.89-.19.36.13 2.29 1.08 2.39 1.13.1.05.17.26.11.45z"/></svg>
                        </div>
                        <span class="text-[10px] text-slate-600 dark:text-slate-400 font-semibold mt-1.5 block">WhatsApp</span>
                    </button>

                    <button @click="shareTo('twitter'); isShareModalOpen = false" class="group block text-center cursor-pointer">
                        <div class="size-11 rounded-2xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-[#08102b] dark:text-white flex items-center justify-center mx-auto transition-colors border border-slate-200 dark:border-slate-700">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.008 4.15H5.078z"/></svg>
                        </div>
                        <span class="text-[10px] text-slate-600 dark:text-slate-400 font-semibold mt-1.5 block">X (Twitter)</span>
                    </button>

                    <button @click="shareTo('facebook'); isShareModalOpen = false" class="group block text-center cursor-pointer">
                        <div class="size-11 rounded-2xl bg-blue-50 dark:bg-blue-950/50 hover:bg-blue-100 text-blue-600 flex items-center justify-center mx-auto transition-colors border border-blue-200/60 dark:border-blue-800/60">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M9 8H7v3h2v9h3v-9h3l.5-3H12V6c0-.88.39-1 1-1h2V2h-3c-2.9 0-5 1.55-5 4.5V8z"/></svg>
                        </div>
                        <span class="text-[10px] text-slate-600 dark:text-slate-400 font-semibold mt-1.5 block">Facebook</span>
                    </button>

                    <button @click="shareTo('telegram'); isShareModalOpen = false" class="group block text-center cursor-pointer">
                        <div class="size-11 rounded-2xl bg-sky-50 dark:bg-sky-950/50 hover:bg-sky-100 text-sky-600 flex items-center justify-center mx-auto transition-colors border border-sky-200/60 dark:border-sky-800/60">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-1-.65-.35-1 .22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.08-.06-.19-.04-.27-.02-.12.02-1.96 1.25-5.54 3.69-.52.36-1 .53-1.42.52-.47-.01-1.37-.26-2.03-.48-.82-.27-1.47-.42-1.42-.88.03-.24.35-.49.97-.74 3.79-1.64 6.32-2.73 7.59-3.26 3.61-1.5 4.36-1.76 4.85-1.77.11 0 .35.03.5.15.13.12.17.29.18.47z"/></svg>
                        </div>
                        <span class="text-[10px] text-slate-600 dark:text-slate-400 font-semibold mt-1.5 block">Telegram</span>
                    </button>
                </div>

                <!-- Copy Link Bar -->
                <div class="px-5 pb-6">
                    <div class="flex items-center bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 gap-2.5">
                        <input type="text" readonly :value="currentUrl" class="text-xs text-slate-600 dark:text-slate-300 bg-transparent flex-1 outline-none border-none select-all truncate font-mono">
                        <button @click="copyLink" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-700 text-[#08102b] dark:text-white text-xs font-bold hover:bg-slate-200 dark:hover:bg-slate-600 cursor-pointer shrink-0 transition-colors">
                            {{ isCopied ? 'Tersalin!' : 'Salin' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <PublicFooter />
    </div>
</template>

<style>
html {
    scroll-behavior: smooth;
}

.scrollbar-thin::-webkit-scrollbar {
    width: 4px;
}
.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
.dark .scrollbar-thin::-webkit-scrollbar-thumb {
    background: #334155;
}
</style>
