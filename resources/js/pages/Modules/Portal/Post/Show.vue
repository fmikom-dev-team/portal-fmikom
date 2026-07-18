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
	relatedPosts: Array,
	previousPost: Object,
	nextPost: Object,
	settings: Object,
});

const formatDate = (dateString) => {
	return new Date(dateString).toLocaleDateString("id-ID", {
		day: "numeric",
		month: "long",
		year: "numeric",
	});
};

const showCommentSuccess = ref(false);
const isShareModalOpen = ref(false);
const commentForm = useForm({
	author_name: "",
	author_email: "",
	content: "",
});

const submitComment = () => {
	// Strip HTML tags completely on the client-side to prevent script injections
	const cleanText = (val) => {
		if (!val) return "";
		return val.replace(/<[^>]*>?/gm, "").trim();
	};

	commentForm.author_name = cleanText(commentForm.author_name);
	commentForm.author_email = cleanText(commentForm.author_email);
	commentForm.content = cleanText(commentForm.content);

	// Strict email format validation
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
	scrollProgress.value = (winScroll / height) * 100;
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
	if (el) window.scrollTo({ top: el.offsetTop - 100, behavior: "smooth" });
};

const wordCount = computed(() => {
	if (parsedContent.value) return countWordsFromEditorJs(parsedContent.value);
	return (props.post.content || "").split(/\s+/).filter(Boolean).length;
});

const readingTime = computed(() => {
	const minutes = Math.ceil(wordCount.value / 225);
	return `${Math.max(1, minutes)} min read`;
});

const isCopied = ref(false);
const copyLink = () => {
	navigator.clipboard.writeText(currentUrl.value);
	isCopied.value = true;
	setTimeout(() => (isCopied.value = false), 2000);
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

    <!-- Reading Progress Bar -->
    <div class="fixed top-[68px] left-0 w-full h-1 z-50 pointer-events-none">
        <div class="h-full bg-slate-900 transition-all duration-150 ease-out" :style="{ width: scrollProgress + '%' }"></div>
    </div>

    <div class="min-h-screen bg-white font-sans antialiased text-slate-800">
        <PublicNavbar />

        <main class="relative bg-white pt-24 pb-24">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Category Pill (Centered above main title) -->
                <div v-if="post.category?.name" class="text-center mb-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 transition-colors">
                        {{ post.category.name }}
                    </span>
                </div>

                <!-- Title (Centered) -->
                <div class="text-center max-w-3xl mx-auto mb-4">
                    <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-semibold text-slate-900 leading-tight tracking-tight text-pretty">
                        {{ post.title }}
                    </h1>
                </div>

                <!-- Excerpt / Subtitle (Centered) -->
                <div v-if="post.excerpt" class="text-center max-w-3xl mx-auto mb-6">
                    <p class="text-base sm:text-lg md:text-xl text-slate-500 leading-relaxed font-normal">
                        {{ post.excerpt }}
                    </p>
                </div>

                <!-- Meta Details Row (Centered & Inline) -->
                <div class="flex flex-wrap items-center justify-center gap-x-2 gap-y-1 text-sm text-slate-500 mb-12">
                    <div class="flex items-center gap-2">
                        <Avatar class="h-6 w-6 border border-slate-200">
                            <AvatarImage v-if="post.user?.foto_path" :src="getAvatarUrl(post.user)" :alt="post.user?.name" class="object-cover" />
                            <AvatarFallback>{{ (post.user?.name || 'Admin').charAt(0).toUpperCase() }}</AvatarFallback>
                        </Avatar>
                        <span class="font-semibold text-slate-900">{{ post.user?.name || 'Admin' }}</span>
                    </div>

                    <span>•</span>
                    <span>Published on {{ formatDate(post.published_at || post.created_at) }}</span>
                    <span>•</span>
                    <span class="flex items-center gap-1">
                        <Clock class="w-3.5 h-3.5 text-slate-400" />
                        {{ readingTime }}
                    </span>
                </div>

                <!-- Featured Image -->
                <div v-if="post.thumbnail" class="w-full max-w-4xl aspect-[16/9] sm:aspect-video rounded-xl overflow-hidden bg-slate-50 border border-slate-100 shadow-sm mb-16 mx-auto">
                    <img :src="post.thumbnail" :alt="post.title" class="w-full h-full object-cover">
                </div>

                <!-- Article Content (Prose) -->
                <article class="prose prose-slate md:prose-lg max-w-3xl mx-auto dark:prose-invert
                    prose-headings:font-bold prose-headings:tracking-tight prose-headings:text-slate-900
                    prose-p:text-slate-600 prose-p:leading-relaxed prose-p:my-6
                    prose-h2:text-2xl md:prose-h2:text-3xl prose-h2:mt-12 prose-h2:mb-6
                    prose-h3:text-xl md:prose-h3:text-2xl prose-h3:mt-8 prose-h3:mb-4
                    prose-a:text-slate-900 prose-a:underline hover:prose-a:text-blue-600
                    prose-img:rounded-xl prose-img:border prose-img:border-slate-100 prose-img:my-8
                    prose-blockquote:border-l-2 prose-blockquote:border-slate-300 prose-blockquote:pl-4 prose-blockquote:italic prose-blockquote:text-slate-600 prose-blockquote:font-normal prose-blockquote:my-6
                    prose-ul:my-6 prose-ul:list-disc prose-ul:pl-6 prose-li:my-2 prose-li:text-slate-600
                    prose-ol:my-6 prose-ol:list-decimal prose-ol:pl-6
                    prose-table:border-collapse prose-table:w-full prose-table:my-6
                    prose-th:text-slate-900 prose-th:font-semibold prose-th:border-b prose-th:border-slate-200 prose-th:pb-2 prose-th:text-left
                    prose-td:py-3 prose-td:border-b prose-td:border-slate-100 prose-td:text-slate-600
                ">
                    <BlockRenderer v-if="isEditorJs" :data="parsedContent" />
                    <div v-else v-html="sanitizeRich(legacyHtml)" />
                </article>

                <!-- Previous & Next Navigation Block -->
                <div v-if="previousPost || nextPost" class="max-w-3xl mx-auto mt-16 pt-8 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-8">
                    <!-- Previous Post Link -->
                    <div class="text-left">
                        <Link v-if="previousPost" :href="`/berita/${previousPost.slug}`" class="group block space-y-1">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5 group-hover:text-slate-900 transition-colors">
                                &larr; Sebelumnya
                            </span>
                            <span class="block text-sm font-bold text-slate-800 group-hover:text-blue-600 transition-colors line-clamp-2">
                                {{ previousPost.title }}
                            </span>
                        </Link>
                    </div>

                    <!-- Next Post Link -->
                    <div class="text-right">
                        <Link v-if="nextPost" :href="`/berita/${nextPost.slug}`" class="group block space-y-1">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center justify-end gap-1.5 group-hover:text-slate-900 transition-colors">
                                Selanjutnya &rarr;
                            </span>
                            <span class="block text-sm font-bold text-slate-800 group-hover:text-blue-600 transition-colors line-clamp-2">
                                {{ nextPost.title }}
                            </span>
                        </Link>
                    </div>
                </div>

                <!-- Social Media Share Section (Gambar 2 Style) -->
                <div class="max-w-3xl mx-auto mt-12 pt-8 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-center gap-6">
                    <!-- Share Actions -->
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <span class="text-sm font-bold text-slate-900">Share on Social Media</span>
                        <div class="flex items-center gap-3">
                            <!-- Instagram -->
                            <a href="https://www.instagram.com/" target="_blank" class="w-10 h-10 rounded-full border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:border-slate-400 hover:text-slate-900 transition-colors shadow-sm cursor-pointer" title="Share on Instagram">
                                <svg class="w-4.5 h-4.5 stroke-current fill-none" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                            </a>
                            <!-- X (Twitter) -->
                            <button @click="shareTo('twitter')" class="w-10 h-10 rounded-full border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:border-slate-400 hover:text-slate-900 transition-colors shadow-sm cursor-pointer" title="Share on X">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.008 4.15H5.078z"/></svg>
                            </button>
                            <!-- Facebook -->
                            <button @click="shareTo('facebook')" class="w-10 h-10 rounded-full border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:border-slate-400 hover:text-slate-900 transition-colors shadow-sm cursor-pointer" title="Share on Facebook">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M9 8H7v3h2v9h3v-9h3l.5-3H12V6c0-.88.39-1 1-1h2V2h-3c-2.9 0-5 1.55-5 4.5V8z"/></svg>
                            </button>
                            <!-- LinkedIn -->
                            <button @click="shareTo('linkedin')" class="w-10 h-10 rounded-full border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:border-slate-400 hover:text-slate-900 transition-colors shadow-sm cursor-pointer" title="Share on LinkedIn">
                                <svg class="w-4.5 h-4.5 fill-current" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                            </button>
                            <!-- Share connection / plus button to open modal (Gambar 1 style) -->
                            <button @click="isShareModalOpen = true" class="w-10 h-10 rounded-full border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:border-slate-400 hover:text-slate-900 transition-colors shadow-sm cursor-pointer" title="Share to other apps">
                                <svg class="w-4 h-4 stroke-current fill-none" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Comments Section (Ultra clean white layout) -->
                <section v-if="settings?.allow_comments !== '0'" class="max-w-3xl mx-auto mt-24 pt-12 border-t border-slate-100">
                    <h3 class="text-xl font-bold text-slate-900 mb-8 flex items-center gap-2">
                        <span>Komentar</span>
                        <span class="text-xs font-semibold px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full">
                            {{ post.comments?.length || 0 }}
                        </span>
                    </h3>
                    
                    <!-- Comment List -->
                    <div v-if="post.comments?.length" class="space-y-8 mb-12">
                        <div v-for="comment in post.comments" :key="comment.id" class="flex gap-4">
                            <Avatar class="h-8 w-8 border border-slate-200 shrink-0">
                                <AvatarImage :src="`https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(comment.author_name)}&backgroundColor=f1f5f9`" />
                                <AvatarFallback>{{ comment.author_name.charAt(0).toUpperCase() }}</AvatarFallback>
                            </Avatar>
                            <div class="space-y-1 flex-grow">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900 text-sm">{{ comment.author_name }}</span>
                                    <span class="text-slate-300 text-xs">•</span>
                                    <span class="text-xs text-slate-400">{{ formatDate(comment.created_at) }}</span>
                                </div>
                                <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line">
                                    {{ comment.content }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-sm text-slate-400 italic mb-8">
                        Belum ada komentar. Jadilah yang pertama memberikan tanggapan!
                    </div>

                    <!-- Comment Form -->
                    <div class="mt-12 space-y-6">
                        <h4 class="text-md font-bold text-slate-900">Tinggalkan Komentar</h4>
                        
                        <div v-if="showCommentSuccess" class="mb-4">
                            <Alert class="bg-white border-green-200 text-green-800 rounded-lg">
                                <Lightbulb class="h-4 w-4 text-green-600" />
                                <AlertTitle class="font-bold text-sm">Komentar Terkirim</AlertTitle>
                                <AlertDescription class="text-xs">
                                    Terima kasih! Komentar Anda berhasil ditambahkan.
                                </AlertDescription>
                            </Alert>
                        </div>

                        <form @submit.prevent="submitComment" class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <input v-model="commentForm.author_name" required placeholder="Nama Lengkap" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:border-slate-900 outline-none transition-all placeholder-slate-400">
                                <input type="email" v-model="commentForm.author_email" required placeholder="Alamat Email" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:border-slate-900 outline-none transition-all placeholder-slate-400">
                            </div>
                            
                            <textarea rows="3" v-model="commentForm.content" required placeholder="Tulis komentar Anda..." class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:border-slate-900 outline-none transition-all placeholder-slate-400 resize-none"></textarea>
                            
                            <div class="flex justify-end">
                                <button type="submit" :disabled="commentForm.processing" class="py-2 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold transition-all disabled:opacity-50 cursor-pointer">
                                    Kirim Komentar
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                <!-- Berita Lainnya Block (Modern Card Deck) -->
                <section v-if="relatedPosts?.length" class="max-w-4xl mx-auto mt-24 pt-12 border-t border-slate-100">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-xl font-bold text-slate-900">Kumpulan Berita Lainnya</h3>
                        <Link href="/berita" class="text-xs font-bold text-blue-600 hover:underline flex items-center gap-1">
                            Lihat Semua Berita &rarr;
                        </Link>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8 sm:gap-10">
                        <Link v-for="relPost in relatedPosts.slice(0, 3)" :key="relPost.id" :href="`/berita/${relPost.slug}`" class="group flex flex-col bg-white transition-all duration-300">
                            <!-- Thumbnail -->
                            <div class="w-full aspect-[16/11] rounded-xl overflow-hidden bg-slate-100 border border-slate-100/60 relative mb-4">
                                <img v-if="relPost.thumbnail" :src="relPost.thumbnail" :alt="relPost.title" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                <div v-else class="w-full h-full flex items-center justify-center bg-slate-100 text-slate-400">
                                    <svg class="w-12 h-12 text-slate-300" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 4c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2zm-8 12v-3l4-4 4.5 4.5L19 14v7H6z"/></svg>
                                </div>
                            </div>
                            
                            <!-- Card Body -->
                            <div class="flex-grow flex flex-col justify-between space-y-4">
                                <div class="space-y-3">
                                    <!-- Category Pill -->
                                    <div v-if="relPost.category?.name">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 transition-colors">
                                            {{ relPost.category.name }}
                                        </span>
                                    </div>

                                    <!-- Title -->
                                    <h4 class="text-[17px] sm:text-[19px] font-bold text-slate-900 leading-snug group-hover:text-blue-600 transition-colors line-clamp-2 tracking-tight text-pretty">
                                        {{ relPost.title }}
                                    </h4>

                                    <!-- Description -->
                                    <p v-if="relPost.excerpt" class="text-sm text-slate-500 line-clamp-2 leading-relaxed font-normal">
                                        {{ relPost.excerpt }}
                                    </p>
                                </div>
                                
                                <!-- Author Info Footer -->
                                <div class="flex items-center gap-3 pt-2">
                                    <Avatar class="h-9 w-9 border border-slate-200 shrink-0">
                                        <AvatarImage v-if="relPost.user?.foto_path" :src="getAvatarUrl(relPost.user)" :alt="relPost.user?.name" class="object-cover" />
                                        <AvatarFallback>{{ (relPost.user?.name || 'Admin').charAt(0).toUpperCase() }}</AvatarFallback>
                                    </Avatar>
                                    <div class="flex flex-col text-left">
                                        <span class="text-xs font-bold text-slate-800 leading-none mb-1">{{ relPost.user?.name || 'Admin' }}</span>
                                        <span class="text-[10px] text-slate-400 font-normal">{{ formatDate(relPost.published_at || relPost.created_at) }}</span>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>
                </section>

            </div>
        </main>

        <!-- Share Modal (Gambar 1 style) -->
        <div v-if="isShareModalOpen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4 animate-in fade-in duration-200" style="z-index: 9999;" @click.self="isShareModalOpen = false">
            <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl border border-slate-100 overflow-hidden relative animate-in fade-in zoom-in-95 duration-200">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-slate-100">
                    <span class="text-sm font-bold text-slate-800">Bagikan ke aplikasi lainnya</span>
                    <button @click="isShareModalOpen = false" class="text-xs text-slate-400 hover:text-slate-900 transition-colors font-medium flex items-center gap-1 cursor-pointer">
                        Close <span class="text-sm font-bold">&times;</span>
                    </button>
                </div>

                <!-- Modal Content (Thumbnail Preview) -->
                <div class="flex gap-4 p-6 bg-slate-50/50 border-b border-slate-100">
                    <img :src="post.thumbnail || 'https://deifkwefumgah.cloudfront.net/shadcnblocks/block/placeholder-1.svg'" :alt="post.title" class="w-16 h-16 rounded-xl object-cover border border-slate-100 shrink-0">
                    <div class="flex flex-col justify-center">
                        <h4 class="text-sm font-bold text-slate-800 leading-snug line-clamp-2">
                            {{ post.title }}
                        </h4>
                    </div>
                </div>

                <!-- Share Grid -->
                <div class="grid grid-cols-4 gap-y-6 gap-x-2 p-6">
                    <!-- Facebook -->
                    <a :href="`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(currentUrl)}`" target="_blank" class="group block text-center cursor-pointer">
                        <div class="w-12 h-12 rounded-full bg-slate-100 hover:bg-slate-200/80 flex items-center justify-center text-slate-600 group-hover:text-slate-900 transition-colors mx-auto">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M9 8H7v3h2v9h3v-9h3l.5-3H12V6c0-.88.39-1 1-1h2V2h-3c-2.9 0-5 1.55-5 4.5V8z"/></svg>
                        </div>
                        <span class="text-[11px] text-slate-500 font-medium mt-1.5 block">Facebook</span>
                    </a>

                    <!-- WhatsApp -->
                    <a :href="`https://api.whatsapp.com/send?text=${encodeURIComponent(post.title + ' ' + currentUrl)}`" target="_blank" class="group block text-center cursor-pointer">
                        <div class="w-12 h-12 rounded-full bg-slate-100 hover:bg-slate-200/80 flex items-center justify-center text-slate-600 group-hover:text-slate-900 transition-colors mx-auto">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12.004 2C6.48 2 2 6.48 2 12c0 2.17.7 4.19 1.89 5.83L2.5 22.5l4.87-1.39A9.97 9.97 0 0012 22c5.52 0 10-4.48 10-10S17.52 2 12.004 2zm5.72 13.9c-.24.67-1.18 1.25-1.63 1.34-.45.09-.9.18-2.92-.61-2.58-1-4.22-3.61-4.35-3.79-.13-.18-1.07-1.42-1.07-2.7 0-1.28.67-1.92.94-2.19.27-.27.59-.34.79-.34.2 0 .4 0 .58.01.19.01.44-.07.69.53.25.61.85 2.08.93 2.23.08.15.13.33.03.53-.1.2-.2.32-.39.53-.19.21-.4.47-.57.63-.19.18-.39.38-.17.76.22.38.98 1.62 2.1 2.62 1.44 1.28 2.64 1.67 3.02 1.86.38.19.61.16.83-.09.23-.25.96-1.12 1.22-1.5.26-.38.53-.32.89-.19.36.13 2.29 1.08 2.39 1.13.1.05.17.26.11.45z"/></svg>
                        </div>
                        <span class="text-[11px] text-slate-500 font-medium mt-1.5 block">WhatsApp</span>
                    </a>

                    <!-- X / Twitter -->
                    <a :href="`https://twitter.com/intent/tweet?url=${encodeURIComponent(currentUrl)}&text=${encodeURIComponent(post.title)}`" target="_blank" class="group block text-center cursor-pointer">
                        <div class="w-12 h-12 rounded-full bg-slate-100 hover:bg-slate-200/80 flex items-center justify-center text-slate-600 group-hover:text-slate-900 transition-colors mx-auto">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.008 4.15H5.078z"/></svg>
                        </div>
                        <span class="text-[11px] text-slate-500 font-medium mt-1.5 block">X / Twitter</span>
                    </a>

                    <!-- Telegram -->
                    <a :href="`https://t.me/share/url?url=${encodeURIComponent(currentUrl)}&text=${encodeURIComponent(post.title)}`" target="_blank" class="group block text-center cursor-pointer">
                        <div class="w-12 h-12 rounded-full bg-slate-100 hover:bg-slate-200/80 flex items-center justify-center text-slate-600 group-hover:text-slate-900 transition-colors mx-auto">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-1-.65-.35-1 .22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.08-.06-.19-.04-.27-.02-.12.02-1.96 1.25-5.54 3.69-.52.36-1 .53-1.42.52-.47-.01-1.37-.26-2.03-.48-.82-.27-1.47-.42-1.42-.88.03-.24.35-.49.97-.74 3.79-1.64 6.32-2.73 7.59-3.26 3.61-1.5 4.36-1.76 4.85-1.77.11 0 .35.03.5.15.13.12.17.29.18.47z"/></svg>
                        </div>
                        <span class="text-[11px] text-slate-500 font-medium mt-1.5 block">Telegram</span>
                    </a>

                    <!-- Pinterest -->
                    <a :href="`https://pinterest.com/pin/create/button/?url=${encodeURIComponent(currentUrl)}&description=${encodeURIComponent(post.title)}`" target="_blank" class="group block text-center cursor-pointer">
                        <div class="w-12 h-12 rounded-full bg-slate-100 hover:bg-slate-200/80 flex items-center justify-center text-slate-600 group-hover:text-slate-900 transition-colors mx-auto">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12c0 4.22 2.62 7.83 6.35 9.31-.09-.79-.17-2 .03-2.86.19-.78 1.2-5.17 1.2-5.17s-.31-.61-.31-1.5c0-1.41.82-2.46 1.83-2.46.86 0 1.28.65 1.28 1.43 0 .87-.55 2.17-.84 3.38-.24 1.01.5 1.83 1.49 1.83 1.79 0 3.17-1.89 3.17-4.62 0-2.42-1.73-4.11-4.22-4.11-2.87 0-4.56 2.16-4.56 4.39 0 .87.34 1.8 0.76 2.3.08.1.1.19.07.29-.08.33-.26 1.07-.3 1.21-.05.21-.17.25-.39.15-1.47-.68-2.39-2.83-2.39-4.56 0-3.71 2.7-7.12 7.78-7.12 4.09 0 7.27 2.91 7.27 6.81 0 4.06-2.56 7.33-6.11 7.33-1.19 0-2.32-.62-2.7-1.35l-.74 2.81c-.27 1.03-.99 2.32-1.48 3.12C10.86 21.88 11.42 22 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2z"/></svg>
                        </div>
                        <span class="text-[11px] text-slate-500 font-medium mt-1.5 block">Pinterest</span>
                    </a>

                    <!-- LinkedIn -->
                    <a :href="`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(currentUrl)}`" target="_blank" class="group block text-center cursor-pointer">
                        <div class="w-12 h-12 rounded-full bg-slate-100 hover:bg-slate-200/80 flex items-center justify-center text-slate-600 group-hover:text-slate-900 transition-colors mx-auto">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                        </div>
                        <span class="text-[11px] text-slate-500 font-medium mt-1.5 block">LinkedIn</span>
                    </a>

                    <!-- Line -->
                    <a :href="`https://social-plugins.line.me/lineit/share?url=${encodeURIComponent(currentUrl)}`" target="_blank" class="group block text-center cursor-pointer">
                        <div class="w-12 h-12 rounded-full bg-slate-100 hover:bg-slate-200/80 flex items-center justify-center text-slate-600 group-hover:text-slate-900 transition-colors mx-auto">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M24 10.3c0-4.7-5.4-8.5-12-8.5S0 5.6 0 10.3c0 4.2 4.3 7.7 10.1 8.4.4.1.9.3 1 .7.1.3.1.7.1 1.1s-.2 1.9-.3 2.1c-.1.2-.5 1 .9.7 1.4-.3 7.5-4.4 10.2-7.6 1.4-1.5 2-3.2 2-5.4zm-16.7.7H6v-3.7c0-.2.2-.4.4-.4h.4c.2 0 .4.2.4.4v2.9h1.1c.2 0 .4.2.4.4v.1c0 .2-.2.3-.4.3zm2.3-.4c0 .2-.2.4-.4.4h-.4c-.2 0-.4-.2-.4-.4v-2.9c0-.2.2-.4.4-.4h.4c.2 0 .4.2.4.4v2.9zm4.7.4h-.4c-.2 0-.3-.1-.4-.3l-1.9-2.7v2.6c0 .2-.2.4-.4.4h-.4c-.2 0-.4-.2-.4-.4v-3.7c0-.2.2-.4.4-.4h.4c.2 0 .3.1.4.3l1.9 2.7v-2.6c0-.2.2-.4.4-.4h.4c.2 0 .4.2.4.4v3.7c0 .2-.2.4-.4.4zm3.9-.4c0 .2-.2.4-.4.4h-1.9c-.2 0-.4-.2-.4-.4v-3.7c0-.2.2-.4.4-.4h1.9c.2 0 .4.2.4.4v.1c0 .2-.2.3-.4.3h-1.5v.9h1.3c.2 0 .4.2.4.4v.1c0 .2-.2.3-.4.3h-1.3v1h1.5c.2 0 .4.2.4.4v.1z"/></svg>
                        </div>
                        <span class="text-[11px] text-slate-500 font-medium mt-1.5 block">Line</span>
                    </a>

                    <!-- Email -->
                    <a :href="`mailto:?subject=${encodeURIComponent(post.title)}&body=${encodeURIComponent(currentUrl)}`" class="group block text-center cursor-pointer">
                        <div class="w-12 h-12 rounded-full bg-slate-100 hover:bg-slate-200/80 flex items-center justify-center text-slate-600 group-hover:text-slate-900 transition-colors mx-auto">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                        </div>
                        <span class="text-[11px] text-slate-500 font-medium mt-1.5 block">Email</span>
                    </a>
                </div>

                <!-- Modal Footer (Copy Link Section) -->
                <div class="px-6 pb-8 pt-4 border-t border-slate-100">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2.5 block">or copy link</span>
                    <div class="flex items-center bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3 gap-3">
                        <svg class="w-4 h-4 text-slate-400 shrink-0 stroke-current fill-none" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                        <input type="text" readonly :value="currentUrl" class="text-xs text-slate-500 bg-transparent flex-grow outline-none border-none pointer-events-none select-all truncate font-medium">
                        <button @click="copyLink" class="text-slate-400 hover:text-slate-900 cursor-pointer relative shrink-0" title="Copy Link">
                            <svg class="w-4.5 h-4.5 stroke-current fill-none" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            <span v-if="isCopied" class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-[10px] px-2 py-0.5 rounded shadow z-10 whitespace-nowrap">
                                Copied!
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <PublicFooter />
    </div>
</template>

<style>
/* Smooth scroll for TOC */
html {
    scroll-behavior: smooth;
}

/* Scrollbar styling for sticky sidebar */
.scrollbar-thin::-webkit-scrollbar {
    width: 4px;
}
.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}

.article-content {
    word-wrap: break-word;
}
</style>
