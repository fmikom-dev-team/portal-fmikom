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
	let extracted = [];
	if (parsedContent.value?.blocks) {
		extracted = parsedContent.value.blocks
			.filter((b) => b.type === "header")
			.map((b, idx) => {
				const text = b.data?.text || "";
				const cleanText = text.replace(/<[^>]*>?/gm, "").trim();
				const id =
					cleanText
						.toLowerCase()
						.replace(/[^a-z0-9]+/g, "-")
						.replace(/(^-|-$)+/g, "") || `heading-${idx}`;
				return { id, text: cleanText, level: b.data?.level || 2 };
			});
	} else if (props.post?.content) {
		const regex = /<h([2-4])[^>]*>(.*?)<\/h\1>/gi;
		let match = regex.exec(props.post.content);
		let idx = 0;
		while (match !== null) {
			const level = Number.parseInt(match[1]);
			const cleanText = match[2].replace(/<[^>]*>?/gm, "").trim();
			const id =
				cleanText
					.toLowerCase()
					.replace(/[^a-z0-9]+/g, "-")
					.replace(/(^-|-$)+/g, "") || `heading-${idx++}`;
			extracted.push({ id, text: cleanText, level });
			match = regex.exec(props.post.content);
		}
	}

	// If no custom headings found, provide smart section checkpoints
	if (extracted.length === 0) {
		const defaults = [
			{ id: "article-content", text: "Isi Berita & Informasi", level: 2 },
			{ id: "share-section", text: "Bagikan Berita", level: 2 },
		];
		if (props.settings?.allow_comments !== "0") {
			defaults.push({ id: "comments-section", text: "Komentar Pembaca", level: 2 });
		}
		if (props.relatedPosts && props.relatedPosts.length) {
			defaults.push({ id: "related-posts-section", text: "Berita Terkait Lainnya", level: 2 });
		}
		return defaults;
	}

	return extracted;
});

const formattedToc = computed(() => {
	let l1Index = 0;
	let l2Index = 0;
	let l3Index = 0;
	const romanNumerals = [
		"i",
		"ii",
		"iii",
		"iv",
		"v",
		"vi",
		"vii",
		"viii",
		"ix",
		"x",
		"xi",
		"xii",
		"xiii",
		"xiv",
		"xv",
	];

	return toc.value.map((item) => {
		if (item.level <= 2) {
			l1Index++;
			l2Index = 0;
			l3Index = 0;
			return {
				...item,
				prefix: `${l1Index}.`,
				depth: 1,
			};
		}
		if (item.level === 3) {
			l2Index++;
			l3Index = 0;
			const roman = romanNumerals[l2Index - 1] || `${l2Index}.`;
			return {
				...item,
				prefix: `${roman}.`,
				depth: 2,
			};
		}
		// level >= 4
		l3Index++;
		const roman = romanNumerals[l3Index - 1] || `${l3Index}.`;
		return {
			...item,
			prefix: `${roman}.`,
			depth: 3,
		};
	});
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

    <!-- Main Outer Canvas with Vertical Borders (Sample Gambar 1 Style) -->
    <div class="min-h-screen bg-slate-50/60 dark:bg-slate-950 font-sans antialiased text-slate-900 dark:text-slate-100 transition-colors">
        <PublicNavbar />

        <!-- Framed Container with Left & Right Subtle Fading Gradient Borders -->
        <div class="max-w-7xl mx-auto min-h-screen bg-white dark:bg-slate-900 relative">
            <!-- Left Subtle Fading Border -->
            <div class="absolute left-0 top-0 bottom-0 w-px bg-gradient-to-b from-transparent via-slate-200/80 dark:via-slate-800/80 to-transparent pointer-events-none"></div>
            <!-- Right Subtle Fading Border -->
            <div class="absolute right-0 top-0 bottom-0 w-px bg-gradient-to-b from-transparent via-slate-200/80 dark:via-slate-800/80 to-transparent pointer-events-none"></div>
            
            <main class="px-6 sm:px-10 lg:px-14 pt-28 pb-20">

                <!-- 1. Breadcrumbs (Gambar 1: Home > Components > Blog) -->
                <nav class="flex items-center gap-2 text-xs font-medium text-slate-400 dark:text-slate-500 mb-6">
                    <Link href="/" class="hover:text-slate-900 dark:hover:text-white transition-colors flex items-center gap-1">
                        <Home class="w-3.5 h-3.5 stroke-[1.75]" />
                        <span>Home</span>
                    </Link>
                    <ChevronRight class="w-3.5 h-3.5 text-slate-300 dark:text-slate-600" />
                    <Link href="/berita" class="hover:text-slate-900 dark:hover:text-white transition-colors">
                        Berita
                    </Link>
                    <template v-if="post.category">
                        <ChevronRight class="w-3.5 h-3.5 text-slate-300 dark:text-slate-600" />
                        <Link :href="`/berita?category=${post.category.slug || post.category.id}`" class="text-slate-700 dark:text-slate-300 font-semibold hover:text-blue-600 transition-colors">
                            {{ post.category.name }}
                        </Link>
                    </template>
                </nav>

                <!-- 2. Big Bold Article Headline (Gambar 1) -->
                <h1 class="text-3xl sm:text-4xl lg:text-[46px] font-extrabold text-slate-900 dark:text-white tracking-tight leading-[1.15] text-pretty max-w-4xl" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif;">
                    {{ post.title }}
                </h1>

                <!-- 3. Subtitle / Lead Excerpt (Optional) -->
                <p v-if="post.excerpt" class="text-base sm:text-lg text-slate-600 dark:text-slate-400 font-normal leading-relaxed mt-4 max-w-3xl">
                    {{ post.excerpt }}
                </p>

                <!-- 4. Author Meta Bar (Gambar 1: Avatar + John Doe on September 23, 2024) -->
                <div class="flex items-center justify-between flex-wrap gap-4 mt-6 mb-8">
                    <div class="flex items-center gap-2.5">
                        <Avatar class="size-7.5 border border-slate-200 dark:border-slate-700 shrink-0">
                            <AvatarImage v-if="post.user?.foto_path" :src="getAvatarUrl(post.user)" :alt="post.user?.name" class="object-cover" />
                            <AvatarFallback class="font-bold text-xs bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                                {{ (post.user?.name || 'A').charAt(0).toUpperCase() }}
                            </AvatarFallback>
                        </Avatar>
                        <div class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 flex items-center flex-wrap gap-x-1.5">
                            <span class="font-semibold text-slate-900 dark:text-white">{{ post.user?.name || 'Humas FMIKOM' }}</span>
                            <span class="text-slate-400">on {{ formatDate(post.published_at || post.created_at) }}</span>
                        </div>
                    </div>

                    <!-- Right Bookmark & Share Trigger -->
                    <div class="flex items-center gap-1">
                        <button
                            @click="toggleBookmark"
                            :class="[
                                'size-8 rounded-lg flex items-center justify-center transition-colors cursor-pointer',
                                isBookmarked ? 'text-blue-600 bg-blue-50 dark:bg-blue-950/50' : 'text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800'
                            ]"
                            title="Simpan Berita"
                        >
                            <Bookmark class="w-4 h-4 stroke-[1.75]" :class="{ 'fill-current text-blue-600': isBookmarked }" />
                        </button>
                        <button
                            @click="isShareModalOpen = true"
                            class="size-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                            title="Bagikan"
                        >
                            <Share2 class="w-4 h-4 stroke-[1.75]" />
                        </button>
                    </div>
                </div>

                <!-- 5. 2-Column Grid (Gambar 1: Left has Featured Image + Prose, Right has Sticky Sidebar) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-start">
                    
                    <!-- ============================================== -->
                    <!-- LEFT COLUMN: FEATURED IMAGE + ARTICLE BODY (lg:col-span-8) -->
                    <!-- ============================================== -->
                    <div class="lg:col-span-8 space-y-8">

                        <!-- Featured Thumbnail (Clean Subtle Rounded Box) -->
                        <div v-if="post.thumbnail" class="w-full aspect-[16/10] rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-800 shadow-2xs">
                            <img :src="post.thumbnail" :alt="post.title" class="w-full h-full object-cover">
                        </div>

                        <!-- Article Body (Clean, Sharp Editorial Prose) -->
                        <div id="article-content" class="prose prose-slate md:prose-lg max-w-none dark:prose-invert
                            prose-headings:font-bold prose-headings:tracking-tight prose-headings:text-slate-900 dark:prose-headings:text-white
                            prose-p:text-[#2f3337] dark:prose-p:text-slate-300 prose-p:leading-[1.8em] prose-p:my-5 prose-p:text-[15px] sm:prose-p:text-[16px]
                            prose-h2:text-2xl md:prose-h2:text-[26px] prose-h2:mt-10 prose-h2:mb-4
                            prose-h3:text-lg md:prose-h3:text-xl prose-h3:mt-8 prose-h3:mb-3
                            prose-a:text-blue-600 dark:prose-a:text-blue-400 prose-a:underline hover:prose-a:text-blue-700
                            prose-img:rounded-lg prose-img:border prose-img:border-slate-200 dark:prose-img:border-slate-700 prose-img:my-8 prose-img:shadow-2xs
                            prose-blockquote:border-l-4 prose-blockquote:border-blue-600 dark:prose-blockquote:border-blue-500 prose-blockquote:bg-blue-50/40 dark:prose-blockquote:bg-blue-950/20 prose-blockquote:p-4 prose-blockquote:rounded-r-lg prose-blockquote:italic prose-blockquote:text-slate-700 dark:prose-blockquote:text-slate-200 prose-blockquote:my-6
                            prose-ul:my-5 prose-ul:list-disc prose-ul:pl-6 prose-li:my-1.5 prose-li:text-slate-600 dark:prose-li:text-slate-300
                            prose-ol:my-5 prose-ol:list-decimal prose-ol:pl-6
                            prose-table:border-collapse prose-table:w-full prose-table:my-6
                            prose-th:text-slate-900 dark:prose-th:text-white prose-th:font-semibold prose-th:border-b prose-th:border-slate-200 dark:prose-th:border-slate-700 prose-th:pb-2 prose-th:text-left
                            prose-td:py-3 prose-td:border-b prose-td:border-slate-100 dark:prose-td:border-slate-800 prose-td:text-slate-600 dark:prose-td:text-slate-300
                        ">
                            <BlockRenderer v-if="isEditorJs" :data="parsedContent" />
                            <div v-else v-html="sanitizeRich(legacyHtml)" />
                        </div>

                        <!-- Share Pill Buttons -->
                        <div id="share-section" class="pt-6 border-t border-slate-100 dark:border-slate-800 space-y-3">
                            <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">
                                Bagikan Berita Ini:
                            </span>
                            <div class="flex flex-wrap items-center gap-2">
                                <button
                                    @click="shareTo('whatsapp')"
                                    class="px-3.5 py-2 rounded-xl text-xs font-bold bg-[#128C7E] hover:bg-[#0e7064] text-white flex items-center gap-2 transition-transform active:scale-95 shadow-2xs cursor-pointer"
                                >
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.004 2C6.48 2 2 6.48 2 12c0 2.17.7 4.19 1.89 5.83L2.5 22.5l4.87-1.39A9.97 9.97 0 0012 22c5.52 0 10-4.48 10-10S17.52 2 12.004 2zm5.72 13.9c-.24.67-1.18 1.25-1.63 1.34-.45.09-.9.18-2.92-.61-2.58-1-4.22-3.61-4.35-3.79-.13-.18-1.07-1.42-1.07-2.7 0-1.28.67-1.92.94-2.19.27-.27.59-.34.79-.34.2 0 .4 0 .58.01.19.01.44-.07.69.53.25.61.85 2.08.93 2.23.08.15.13.33.03.53-.1.2-.2.32-.39.53-.19.21-.4.47-.57.63-.19.18-.39.38-.17.76.22.38.98 1.62 2.1 2.62 1.44 1.28 2.64 1.67 3.02 1.86.38.19.61.16.83-.09.23-.25.96-1.12 1.22-1.5.26-.38.53-.32.89-.19.36.13 2.29 1.08 2.39 1.13.1.05.17.26.11.45z"/></svg>
                                    <span>WhatsApp</span>
                                </button>
                                <button
                                    @click="shareTo('twitter')"
                                    class="px-3.5 py-2 rounded-xl text-xs font-bold bg-[#08102b] hover:bg-black text-white flex items-center gap-2 transition-transform active:scale-95 shadow-2xs cursor-pointer"
                                >
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.008 4.15H5.078z"/></svg>
                                    <span>X (Twitter)</span>
                                </button>
                                <button
                                    @click="shareTo('facebook')"
                                    class="px-3.5 py-2 rounded-xl text-xs font-bold bg-[#1877F2] hover:bg-[#1464cc] text-white flex items-center gap-2 transition-transform active:scale-95 shadow-2xs cursor-pointer"
                                >
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M9 8H7v3h2v9h3v-9h3l.5-3H12V6c0-.88.39-1 1-1h2V2h-3c-2.9 0-5 1.55-5 4.5V8z"/></svg>
                                    <span>Facebook</span>
                                </button>
                                <button
                                    @click="shareTo('telegram')"
                                    class="px-3.5 py-2 rounded-xl text-xs font-bold bg-[#229ED9] hover:bg-[#1c85b8] text-white flex items-center gap-2 transition-transform active:scale-95 shadow-2xs cursor-pointer"
                                >
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-1-.65-.35-1 .22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.08-.06-.19-.04-.27-.02-.12.02-1.96 1.25-5.54 3.69-.52.36-1 .53-1.42.52-.47-.01-1.37-.26-2.03-.48-.82-.27-1.47-.42-1.42-.88.03-.24.35-.49.97-.74 3.79-1.64 6.32-2.73 7.59-3.26 3.61-1.5 4.36-1.76 4.85-1.77.11 0 .35.03.5.15.13.12.17.29.18.47z"/></svg>
                                    <span>Telegram</span>
                                </button>
                                <button
                                    @click="copyLink"
                                    class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-white flex items-center gap-1.5 transition-colors cursor-pointer"
                                >
                                    <Check v-if="isCopied" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                                    <Copy v-else class="w-3.5 h-3.5 text-slate-400" />
                                    <span>{{ isCopied ? 'Tersalin!' : 'Salin Link' }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Author Profile Card -->
                        <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-800 flex items-start sm:items-center gap-4">
                            <Avatar class="size-12 border border-slate-200 dark:border-slate-700 shrink-0">
                                <AvatarImage v-if="post.user?.foto_path" :src="getAvatarUrl(post.user)" :alt="post.user?.name" class="object-cover" />
                                <AvatarFallback class="font-bold text-base bg-blue-600 text-white">
                                    {{ (post.user?.name || 'FMIKOM').charAt(0).toUpperCase() }}
                                </AvatarFallback>
                            </Avatar>
                            <div class="space-y-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">
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

                        <!-- Previous & Next Navigation -->
                        <div v-if="previousPost || nextPost" class="pt-6 border-t border-slate-100 dark:border-slate-800 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <Link
                                v-if="previousPost"
                                :href="`/berita/${previousPost.slug}`"
                                class="group p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all flex flex-col justify-between"
                            >
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1 group-hover:text-blue-600 transition-colors mb-1.5">
                                    <ArrowLeft class="w-3.5 h-3.5" /> Sebelumnya
                                </span>
                                <span class="text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-200 group-hover:text-blue-600 transition-colors line-clamp-2">
                                    {{ previousPost.title }}
                                </span>
                            </Link>
                            <div v-else class="hidden sm:block"></div>

                            <Link
                                v-if="nextPost"
                                :href="`/berita/${nextPost.slug}`"
                                class="group p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all flex flex-col justify-between text-right"
                            >
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider flex items-center justify-end gap-1 group-hover:text-blue-600 transition-colors mb-1.5">
                                    Selanjutnya <ArrowRight class="w-3.5 h-3.5" />
                                </span>
                                <span class="text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-200 group-hover:text-blue-600 transition-colors line-clamp-2">
                                    {{ nextPost.title }}
                                </span>
                            </Link>
                        </div>

                        <!-- Comments Section -->
                        <section id="comments-section" v-if="settings?.allow_comments !== '0'" class="pt-8 border-t border-slate-100 dark:border-slate-800 space-y-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <MessageSquare class="w-4 h-4 text-blue-600 stroke-[1.75]" />
                                    <span>Komentar ({{ post.comments?.length || 0 }})</span>
                                </h3>
                            </div>

                            <!-- Comment List -->
                            <div v-if="post.comments?.length" class="space-y-3.5">
                                <div
                                    v-for="comment in post.comments"
                                    :key="comment.id"
                                    class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/70 dark:border-slate-800 flex gap-3.5"
                                >
                                    <Avatar class="size-8 border border-slate-200 dark:border-slate-700 shrink-0">
                                        <AvatarImage :src="`https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(comment.author_name)}&backgroundColor=f1f5f9`" />
                                        <AvatarFallback>{{ comment.author_name.charAt(0).toUpperCase() }}</AvatarFallback>
                                    </Avatar>
                                    <div class="space-y-1 flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm">{{ comment.author_name }}</span>
                                            <span class="text-[11px] text-slate-400">{{ formatDate(comment.created_at) }}</span>
                                        </div>
                                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line">
                                            {{ comment.content }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-xs text-slate-400 dark:text-slate-500 italic p-4 rounded-xl bg-slate-50 dark:bg-slate-800/20 text-center">
                                Belum ada komentar. Jadilah yang pertama memberikan tanggapan!
                            </div>

                            <!-- Comment Form -->
                            <div class="space-y-4 pt-2">
                                <h4 class="text-sm font-bold text-slate-900 dark:text-white">Tulis Komentar</h4>

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
                                            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all placeholder:text-slate-400"
                                        />
                                        <input
                                            type="email"
                                            v-model="commentForm.author_email"
                                            required
                                            placeholder="Alamat Email *"
                                            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all placeholder:text-slate-400"
                                        />
                                    </div>

                                    <textarea
                                        rows="3"
                                        v-model="commentForm.content"
                                        required
                                        placeholder="Tuliskan komentar atau tanggapan Anda di sini..."
                                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all placeholder:text-slate-400 resize-none"
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
                    <!-- RIGHT COLUMN: MINIMALIST SIDEBAR (lg:col-span-4) -->
                    <!-- ============================================== -->
                    <aside class="lg:col-span-4 space-y-8 sticky top-28">

                        <!-- WIDGET 1: ON THIS PAGE (Gambar 1 Match) -->
                        <div v-if="toc.length > 0" class="space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                                ON THIS PAGE
                            </h4>
                            <nav class="space-y-2.5 text-xs text-slate-600 dark:text-slate-400">
                                <button
                                    v-for="item in toc"
                                    :key="item.id"
                                    @click="scrollToToc(item.id)"
                                    :class="[
                                        'block text-left transition-colors cursor-pointer w-full leading-relaxed',
                                        activeTocId === item.id ? 'font-bold text-blue-600 dark:text-blue-400' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                                    ]"
                                >
                                    {{ item.text }}
                                </button>
                            </nav>
                        </div>

                        <!-- WIDGET 2: Share this article (Gambar 1 Circular Outline Icons) -->
                        <div class="space-y-3 pt-6 border-t border-slate-100 dark:border-slate-800">
                            <h4 class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                Share this article:
                            </h4>
                            <div class="flex items-center gap-2.5">
                                <!-- Facebook -->
                                <button
                                    @click="shareTo('facebook')"
                                    class="size-8 rounded-full border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-blue-600 hover:border-blue-400 transition-colors cursor-pointer"
                                    title="Facebook"
                                >
                                    <Facebook class="w-3.5 h-3.5" />
                                </button>
                                <!-- LinkedIn -->
                                <button
                                    @click="shareTo('linkedin')"
                                    class="size-8 rounded-full border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-blue-600 hover:border-blue-400 transition-colors cursor-pointer"
                                    title="LinkedIn"
                                >
                                    <Linkedin class="w-3.5 h-3.5" />
                                </button>
                                <!-- X / Twitter -->
                                <button
                                    @click="shareTo('twitter')"
                                    class="size-8 rounded-full border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-black dark:hover:text-white hover:border-slate-400 transition-colors cursor-pointer"
                                    title="X / Twitter"
                                >
                                    <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.008 4.15H5.078z"/></svg>
                                </button>
                                <!-- WhatsApp -->
                                <button
                                    @click="shareTo('whatsapp')"
                                    class="size-8 rounded-full border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-emerald-600 hover:border-emerald-400 transition-colors cursor-pointer"
                                    title="WhatsApp"
                                >
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.004 2C6.48 2 2 6.48 2 12c0 2.17.7 4.19 1.89 5.83L2.5 22.5l4.87-1.39A9.97 9.97 0 0012 22c5.52 0 10-4.48 10-10S17.52 2 12.004 2zm5.72 13.9c-.24.67-1.18 1.25-1.63 1.34-.45.09-.9.18-2.92-.61-2.58-1-4.22-3.61-4.35-3.79-.13-.18-1.07-1.42-1.07-2.7 0-1.28.67-1.92.94-2.19.27-.27.59-.34.79-.34.2 0 .4 0 .58.01.19.01.44-.07.69.53.25.61.85 2.08.93 2.23.08.15.13.33.03.53-.1.2-.2.32-.39.53-.19.21-.4.47-.57.63-.19.18-.39.38-.17.76.22.38.98 1.62 2.1 2.62 1.44 1.28 2.64 1.67 3.02 1.86.38.19.61.16.83-.09.23-.25.96-1.12 1.22-1.5.26-.38.53-.32.89-.19.36.13 2.29 1.08 2.39 1.13.1.05.17.26.11.45z"/></svg>
                                </button>
                                <!-- Copy Link -->
                                <button
                                    @click="copyLink"
                                    class="size-8 rounded-full border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-blue-600 hover:border-blue-400 transition-colors cursor-pointer"
                                    title="Salin Link"
                                >
                                    <Check v-if="isCopied" class="w-3.5 h-3.5 text-emerald-600" />
                                    <Copy v-else class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>

                        <!-- WIDGET 3: Popular Posts (Clean List) -->
                        <div v-if="popularPosts && popularPosts.length" class="space-y-4 pt-6 border-t border-slate-100 dark:border-slate-800">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                                Popular Posts
                            </h4>
                            <div class="space-y-3.5">
                                <Link
                                    v-for="(popPost, idx) in popularPosts.slice(0, 4)"
                                    :key="popPost.id"
                                    :href="`/berita/${popPost.slug}`"
                                    class="group block space-y-1"
                                >
                                    <div class="text-[11px] text-slate-400 font-medium flex items-center gap-1">
                                        <span>{{ formatShortDate(popPost.published_at || popPost.created_at) }}</span>
                                        <span>— in</span>
                                        <span class="text-slate-600 dark:text-slate-400 font-semibold">{{ popPost.category?.name || 'Berita' }}</span>
                                    </div>
                                    <h5 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors line-clamp-2 leading-snug">
                                        <span class="text-slate-400 font-semibold mr-0.5">#{{ idx + 1 }}</span>
                                        {{ popPost.title }}
                                    </h5>
                                </Link>
                            </div>
                        </div>

                        <!-- WIDGET 4: Categories (Clean Pills) -->
                        <div v-if="categories && categories.length" class="space-y-3 pt-6 border-t border-slate-100 dark:border-slate-800">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                                Categories
                            </h4>
                            <div class="flex flex-wrap gap-1.5">
                                <Link
                                    v-for="cat in categories"
                                    :key="cat.id"
                                    :href="`/berita?category=${cat.slug || cat.id}`"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-blue-950/50 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                                >
                                    <span>{{ cat.name }}</span>
                                    <span class="text-[10px] text-slate-400 font-normal">({{ cat.posts_count || 0 }})</span>
                                </Link>
                            </div>
                        </div>

                    </aside>

                </div>

                <!-- 7. Bottom Section: Related Posts -->
                <section id="related-posts-section" v-if="relatedPosts && relatedPosts.length" class="mt-16 pt-10 border-t border-slate-200 dark:border-slate-800 space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">
                            Berita Terkait Lainnya
                        </h3>
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
                            class="group flex flex-col rounded-xl overflow-hidden border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all duration-200"
                        >
                            <div class="w-full aspect-[16/10] bg-slate-100 dark:bg-slate-800 overflow-hidden relative">
                                <img
                                    v-if="relPost.thumbnail"
                                    :src="relPost.thumbnail"
                                    :alt="relPost.title"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-slate-300 dark:text-slate-600">
                                    <BookOpen class="w-8 h-8 stroke-[1.5]" />
                                </div>
                            </div>
                            <div class="p-4 flex-1 flex flex-col justify-between space-y-2.5">
                                <div class="space-y-1">
                                    <span class="text-[11px] font-medium text-slate-400 block">
                                        {{ formatDate(relPost.published_at || relPost.created_at) }}
                                    </span>
                                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white leading-snug group-hover:text-blue-600 transition-colors line-clamp-2">
                                        {{ relPost.title }}
                                    </h4>
                                </div>
                            </div>
                        </Link>
                    </div>
                </section>

            </main>
        </div>

        <!-- FLOATING MODERN TABLE OF CONTENTS TRIGGER TAB (User SVG) -->
        <button
            @click="isTocDrawerOpen = true"
            class="fixed right-0 top-36 z-40 bg-white dark:bg-slate-900 border-l border-y border-slate-200/90 dark:border-slate-800/90 rounded-l-full py-3 pl-3.5 pr-2.5 shadow-[-4px_6px_20px_rgba(0,0,0,0.08)] hover:shadow-[-6px_8px_25px_rgba(0,0,0,0.14)] hover:-translate-x-1 transition-all duration-300 group cursor-pointer flex items-center justify-center"
            title="Daftar Isi (Table of Contents)"
        >
            <div class="relative flex items-center justify-center">
                <!-- Light Blue Circle Notification Dot at top-left with Slow Blinking Animation -->
                <span class="absolute -top-1.5 -left-1.5 size-3 rounded-full bg-[#80b3ff] dark:bg-blue-400 ring-2 ring-white dark:ring-slate-900 shadow-2xs animate-dot-slow-blink"></span>
                
                <!-- Exact User Provided SVG Icon -->
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    class="h-5 w-5 text-slate-800 dark:text-slate-100 group-hover:text-blue-600 transition-colors"
                >
                    <g transform="translate(3.61 2.75)">
                        <line
                            x1="11.9858"
                            x2="4.7658"
                            y1="12.9463"
                            y2="12.9463"
                            stroke="currentColor"
                            stroke-linecap="round"
                        />

                        <line
                            x1="11.9858"
                            x2="4.7658"
                            y1="9.1865"
                            y2="9.1865"
                            stroke="currentColor"
                            stroke-linecap="round"
                        />

                        <line
                            x1="7.521"
                            x2="4.766"
                            y1="5.4272"
                            y2="5.4272"
                            stroke="currentColor"
                            stroke-linecap="round"
                        />

                        <path
                            d="M0 9.25C0 16.187 2.098 18.5 8.391 18.5S16.782 16.187 16.782 9.25 14.685 0 8.391 0 0 2.313 0 9.25Z"
                            stroke="currentColor"
                            stroke-linejoin="round"
                        />
                    </g>
                </svg>
            </div>
        </button>

        <!-- TABLE OF CONTENTS SLIDE-OVER DRAWER (Sample Screenshot 2) -->
        <Transition
            enter-active-class="transition-opacity duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isTocDrawerOpen"
                @click="isTocDrawerOpen = false"
                class="fixed inset-0 bg-slate-950/30 backdrop-blur-2xs z-[9990]"
            ></div>
        </Transition>

        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition-transform duration-200 ease-in"
            leave-from-class="translate-x-0"
            leave-to-class="translate-x-full"
        >
            <aside
                v-if="isTocDrawerOpen"
                class="fixed top-0 right-0 h-full w-80 sm:w-[360px] bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800 shadow-2xl z-[9995] flex flex-col font-toc"
            >
                <!-- Drawer Header (Screenshot 2: Table of contents  Close X) -->
                <div class="px-6 py-4.5 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-[15px] font-semibold text-slate-800 dark:text-white tracking-tight">
                        Table of contents
                    </h3>
                    <button
                        @click="isTocDrawerOpen = false"
                        class="flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white font-medium transition-colors cursor-pointer py-1 px-1.5 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800"
                        title="Tutup"
                    >
                        <span>Close</span>
                        <X class="w-3.5 h-3.5 stroke-[2]" />
                    </button>
                </div>

                <!-- Drawer Headings List (Screenshot 2: 1. Main, i. Sub, i. Subsub) -->
                <div class="flex-1 overflow-y-auto px-6 py-5 space-y-2.5 scrollbar-thin text-[14px]">
                    <div v-for="item in formattedToc" :key="item.id">
                        <button
                            @click="scrollToToc(item.id)"
                            :class="[
                                'text-left w-full transition-colors cursor-pointer leading-relaxed block py-1',
                                item.depth === 1 ? 'font-semibold text-slate-900 dark:text-white pt-2 first:pt-0' : '',
                                item.depth === 2 ? 'pl-6 text-slate-700 dark:text-slate-300 font-normal' : '',
                                item.depth === 3 ? 'pl-12 text-slate-600 dark:text-slate-400 font-normal text-xs' : '',
                                activeTocId === item.id ? 'text-blue-600 dark:text-blue-400 font-bold' : 'hover:text-blue-600 dark:hover:text-blue-400'
                            ]"
                        >
                            <span :class="[
                                'mr-1.5',
                                item.depth === 1 ? 'font-semibold text-slate-900 dark:text-white' : 'font-normal text-slate-500 dark:text-slate-400'
                            ]">{{ item.prefix }}</span>
                            <span>{{ item.text }}</span>
                        </button>
                    </div>
                </div>
            </aside>
        </Transition>

        <!-- FLOATING BACK TO TOP BUTTON WITH CIRCULAR PROGRESS RING (Sample Screenshot 1) -->
        <button
            v-if="showBackToTop"
            @click="scrollToTop"
            class="fixed bottom-8 right-6 size-11 rounded-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-md text-slate-700 dark:text-slate-200 hover:text-blue-600 transition-all flex items-center justify-center z-40 cursor-pointer hover:scale-105 group"
            title="Kembali ke Atas"
        >
            <!-- Progress indicator dot -->
            <span class="absolute -top-0.5 size-1.5 rounded-full bg-blue-500"></span>
            <svg class="w-4 h-4 stroke-[2.2] text-slate-700 dark:text-slate-200 group-hover:text-blue-600 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="18 15 12 9 6 15"></polyline>
            </svg>
        </button>

        <!-- Share Modal -->
        <div v-if="isShareModalOpen" class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-[9999] flex items-center justify-center p-4 animate-in fade-in duration-200" style="z-index: 9999;" @click.self="isShareModalOpen = false">
            <div class="bg-white dark:bg-slate-900 rounded-2xl w-full max-w-md shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden relative animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-sm font-bold text-slate-900 dark:text-white">Bagikan Berita Ini</span>
                    <button @click="isShareModalOpen = false" class="text-xs text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors font-bold cursor-pointer">
                        &times;
                    </button>
                </div>

                <!-- Social Grid in Modal -->
                <div class="p-5 grid grid-cols-4 gap-3 text-center">
                    <button @click="shareTo('whatsapp')" class="group cursor-pointer">
                        <div class="size-11 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 hover:bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto transition-colors border border-emerald-200/60 dark:border-emerald-800/60">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.004 2C6.48 2 2 6.48 2 12c0 2.17.7 4.19 1.89 5.83L2.5 22.5l4.87-1.39A9.97 9.97 0 0012 22c5.52 0 10-4.48 10-10S17.52 2 12.004 2zm5.72 13.9c-.24.67-1.18 1.25-1.63 1.34-.45.09-.9.18-2.92-.61-2.58-1-4.22-3.61-4.35-3.79-.13-.18-1.07-1.42-1.07-2.7 0-1.28.67-1.92.94-2.19.27-.27.59-.34.79-.34.2 0 .4 0 .58.01.19.01.44-.07.69.53.25.61.85 2.08.93 2.23.08.15.13.33.03.53-.1.2-.2.32-.39.53-.19.21-.4.47-.57.63-.19.18-.39.38-.17.76.22.38.98 1.62 2.1 2.62 1.44 1.28 2.64 1.67 3.02 1.86.38.19.61.16.83-.09.23-.25.96-1.12 1.22-1.5.26-.38.53-.32.89-.19.36.13 2.29 1.08 2.39 1.13.1.05.17.26.11.45z"/></svg>
                        </div>
                        <span class="text-[10px] text-slate-600 dark:text-slate-400 font-semibold mt-1.5 block">WhatsApp</span>
                    </button>
                    <button @click="shareTo('twitter')" class="group cursor-pointer">
                        <div class="size-11 rounded-2xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-900 dark:text-white flex items-center justify-center mx-auto transition-colors border border-slate-200 dark:border-slate-700">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.008 4.15H5.078z"/></svg>
                        </div>
                        <span class="text-[10px] text-slate-600 dark:text-slate-400 font-semibold mt-1.5 block">X</span>
                    </button>
                    <button @click="shareTo('facebook')" class="group cursor-pointer">
                        <div class="size-11 rounded-2xl bg-blue-50 dark:bg-blue-950/50 hover:bg-blue-100 text-blue-600 flex items-center justify-center mx-auto transition-colors border border-blue-200/60 dark:border-blue-800/60">
                            <Facebook class="w-4 h-4" />
                        </div>
                        <span class="text-[10px] text-slate-600 dark:text-slate-400 font-semibold mt-1.5 block">Facebook</span>
                    </button>
                    <button @click="shareTo('telegram')" class="group cursor-pointer">
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
@import url('https://fonts.googleapis.com/css2?family=Google+Sans+Text:wght@400;500;600;700&family=Google+Sans:wght@400;500;700&display=swap');

html {
    scroll-behavior: smooth;
}

.font-toc {
    font-family: 'Google Sans Text', 'Google Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}

@keyframes slow-blink {
    0%, 100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.25;
        transform: scale(0.85);
    }
}

.animate-dot-slow-blink {
    animation: slow-blink 2.2s ease-in-out infinite;
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
