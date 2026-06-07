<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import axios from "axios";
import {
	AlertCircle,
	CheckCircle2,
	ChevronLeft,
	Clock,
	Eye,
	Loader2,
	PanelRight,
	Save,
	Send,
	X,
} from "lucide-vue-next";
import { computed, onMounted, ref, watch } from "vue";
import EditorJsEditor from "@/components/editor/EditorJsEditor.vue";
import BlockRenderer from "@/components/editor/renderer/BlockRenderer.vue";
import EditorSidebar from "@/components/editor/sidebar/EditorSidebar.vue";

const props = defineProps({
	post: Object,
	categories: Array,
});

/* ── Parse tags ── */
let initialTags = [];
try {
	initialTags =
		typeof props.post.tags === "string"
			? JSON.parse(props.post.tags)
			: props.post.tags || [];
} catch {
	initialTags = [];
}

/* ── Form ── */
const form = useForm({
	_method: "PUT",
	title: props.post.title || "",
	slug: props.post.slug || "",
	content: props.post.content || "",
	excerpt: props.post.excerpt || "",
	category_id: props.post.category_id || null,
	tags: initialTags,
	status: props.post.status || "draft",
	published_at: props.post.published_at
		? new Date(props.post.published_at).toISOString().slice(0, 16)
		: "",
	meta_title: props.post.meta_title || "",
	meta_description: props.post.meta_description || "",
	thumbnail: null,
	thumbnail_url: props.post.thumbnail || null,
	thumbnail_preview: null,
	og_image: null,
	og_image_url: props.post.og_image || null,
	og_image_preview: null,
});

/* ── Autosave ── */
let autosaveTimer = null;
let initialLoad = true;
const autosaveState = ref("idle"); // idle | unsaved | saving | saved | error
const wordCount = ref(0);
const readTime = computed(() => Math.max(1, Math.ceil(wordCount.value / 200)));

const triggerAutosave = () => {
	if (initialLoad) return;
	autosaveState.value = "unsaved";
	clearTimeout(autosaveTimer);
	autosaveTimer = setTimeout(async () => {
		autosaveState.value = "saving";
		try {
			const fd = new FormData();
			fd.append("_method", "PUT");
			fd.append("title", form.title);
			fd.append("slug", form.slug);
			fd.append("content", form.content);
			fd.append("status", form.status);
			await axios.post(`/portal-admin/posts/${props.post.id}`, fd, {
				headers: { Accept: "application/json" },
			});
			autosaveState.value = "saved";
			setTimeout(() => {
				if (autosaveState.value === "saved") autosaveState.value = "idle";
			}, 3000);
		} catch {
			autosaveState.value = "error";
		}
	}, 2000);
};

watch(() => form.content, triggerAutosave);
watch(() => form.title, triggerAutosave);

onMounted(() =>
	setTimeout(() => {
		initialLoad = false;
	}, 800),
);

/* ── Uploads ── */
const handleImageUpload = async (file, callback) => {
	const fd = new FormData();
	fd.append("image", file);
	try {
		const res = await axios.post("/portal-admin/posts/upload-image", fd);
		callback(res.data.url);
	} catch {
		callback(null);
	}
};

const handleThumbnailUpdate = (file) => {
	if (file) {
		form.thumbnail = file;
		form.thumbnail_preview = URL.createObjectURL(file);
	} else {
		form.thumbnail = null;
		form.thumbnail_preview = null;
		form.thumbnail_url = null;
	}
};
const handleOgImageUpdate = (file) => {
	if (file) {
		form.og_image = file;
		form.og_image_preview = URL.createObjectURL(file);
	} else {
		form.og_image = null;
		form.og_image_preview = null;
		form.og_image_url = null;
	}
};

/* ── Submit ── */
const editorRef = ref(null);

const submit = async (status) => {
	if (editorRef.value) {
		try {
			const output = await editorRef.value.save();
			if (output) {
				form.content = JSON.stringify(output);
			} else {
				alert("Editor returned empty output.");
			}
		} catch (e) {
			console.error("Editor save failed:", e);
			alert(`Editor save error (submit): ${e.message || e}`);
		}
	} else {
		alert("Editor ref is null!");
	}

	form.status = status;
	form.post(`/portal-admin/posts/${props.post.id}`, { forceFormData: true });
};

/* ── Preview ── */
const previewMode = ref(false);
const settingsOpen = ref(false);

const openPreview = async () => {
	if (editorRef.value) {
		try {
			const output = await editorRef.value.save();
			if (output) {
				form.content = JSON.stringify(output);
			} else {
				alert("Editor returned empty output.");
			}
		} catch (e) {
			console.error("Editor save failed:", e);
			alert(`Editor save error (preview): ${e.message || e}`);
		}
	} else {
		alert("Editor ref is null!");
	}
	previewMode.value = true;
};

const previewData = computed(() => {
	if (!form.content) return null;
	try {
		const d =
			typeof form.content === "string"
				? JSON.parse(form.content)
				: form.content;
		return d?.blocks ? d : null;
	} catch {
		return null;
	}
});

const statusBadge = computed(() =>
	form.status === "published"
		? "bg-emerald-100 text-emerald-700"
		: "bg-amber-100 text-amber-700",
);
</script>

<template>
    <Head :title="`Edit: ${form.title}`" />

    <div class="fixed inset-0 flex flex-col bg-white z-[60] font-sans antialiased overflow-hidden">

        <!-- ── Top Bar ── -->
        <header class="h-[56px] border-b border-gray-100 flex items-center justify-between px-4 sm:px-6 bg-white/95 backdrop-blur-sm shrink-0 z-50">
            <div class="flex items-center gap-3">
                <Link href="/portal-admin/posts"
                      class="w-8 h-8 flex items-center justify-center hover:bg-gray-100 rounded-lg text-slate-400 hover:text-slate-700 transition-colors">
                    <ChevronLeft class="w-5 h-5" />
                </Link>
                <div class="h-4 w-px bg-gray-200" />
                <div class="flex flex-col gap-0.5">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-slate-800 truncate max-w-[200px] hidden sm:block">
                            {{ form.title || 'Edit Berita' }}
                        </span>
                        <span :class="['px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider', statusBadge]">
                            {{ form.status }}
                        </span>
                    </div>
                    <!-- Autosave indicator -->
                    <div class="flex items-center gap-1 text-[11px] font-medium h-3">
                        <template v-if="autosaveState === 'saving'">
                            <Loader2 class="w-3 h-3 animate-spin text-blue-500" />
                            <span class="text-blue-500">Menyimpan...</span>
                        </template>
                        <template v-else-if="autosaveState === 'saved'">
                            <CheckCircle2 class="w-3 h-3 text-emerald-500" />
                            <span class="text-emerald-500">Tersimpan</span>
                        </template>
                        <template v-else-if="autosaveState === 'unsaved'">
                            <Clock class="w-3 h-3 text-amber-400" />
                            <span class="text-amber-400">Belum disimpan</span>
                        </template>
                        <template v-else-if="autosaveState === 'error'">
                            <AlertCircle class="w-3 h-3 text-red-500" />
                            <span class="text-red-500">Autosave gagal</span>
                        </template>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <div class="hidden lg:flex items-center gap-3 mr-2 text-[11px] font-medium text-slate-400 border-r border-gray-100 pr-4">
                    <span>{{ wordCount }} kata</span>
                    <span>~{{ readTime }} mnt baca</span>
                </div>
                <button @click="openPreview"
                        class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-gray-100 rounded-lg transition-colors">
                    <Eye class="w-4 h-4" /> Preview
                </button>
                <button @click="submit('draft')" :disabled="form.processing"
                        class="hidden md:flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-gray-100 rounded-lg transition-colors">
                    <Save class="w-4 h-4" /> Simpan
                </button>
                <button @click="submit('published')" :disabled="form.processing"
                        class="flex items-center gap-1.5 px-4 py-1.5 text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white rounded-lg shadow-sm transition-all active:scale-95">
                    <Loader2 v-if="form.processing" class="w-3.5 h-3.5 animate-spin" />
                    <Send v-else class="w-3.5 h-3.5" />
                    Perbarui
                </button>
                <button @click="settingsOpen = !settingsOpen" class="lg:hidden p-2 text-slate-500 hover:bg-gray-100 rounded-lg">
                    <PanelRight class="w-5 h-5" />
                </button>
            </div>
        </header>

        <!-- ── Main ── -->
        <main class="flex-1 flex overflow-hidden">
            <div class="flex-1 overflow-y-auto bg-white">
                <div class="max-w-[720px] w-full mx-auto px-4 sm:px-6">

                    <!-- Title -->
                    <div class="pt-14 pb-4">
                        <textarea
                            v-model="form.title"
                            rows="1"
                            placeholder="Judul Berita..."
                            @input="e => { e.target.style.height = 'auto'; e.target.style.height = e.target.scrollHeight + 'px' }"
                            class="w-full text-[2.5rem] font-black border-none focus:ring-0 bg-transparent placeholder:text-gray-200 text-gray-900 resize-none leading-tight overflow-hidden p-0 tracking-tight"
                            style="font-family:'Plus Jakarta Sans',system-ui,sans-serif;"
                        />
                        <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
                    </div>

                    <!-- Editor.js -->
                    <EditorJsEditor
                        ref="editorRef"
                        v-model="form.content"
                        @update:wordCount="wordCount = $event"
                        @upload-image="handleImageUpload"
                        class="pb-20"
                    />
                    <p v-if="form.errors.content" class="text-red-500 text-xs mt-1 px-2">{{ form.errors.content }}</p>
                </div>
            </div>

            <EditorSidebar
                :form="form"
                :categories="categories"
                :is-open="settingsOpen"
                @close="settingsOpen = false"
                @update:thumbnail="handleThumbnailUpdate"
                @update:ogImage="handleOgImageUpdate"
            />
        </main>

        <!-- ── Preview Modal ── -->
        <Transition name="preview">
            <div v-if="previewMode" class="fixed inset-0 z-[100] bg-white flex flex-col overflow-hidden">
                <header class="h-14 border-b border-gray-100 flex items-center justify-between px-6 shrink-0">
                    <span class="text-sm font-bold text-slate-800">Preview</span>
                    <button @click="previewMode = false"
                            class="w-8 h-8 flex items-center justify-center hover:bg-gray-100 rounded-full text-slate-400">
                        <X class="w-5 h-5" />
                    </button>
                </header>
                <div class="flex-1 overflow-y-auto p-6 sm:p-12 bg-slate-50">
                    <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-8 sm:p-14">
                        <h1 class="text-4xl font-black text-slate-900 mb-6 leading-tight tracking-tight"
                            style="font-family:'Plus Jakarta Sans',system-ui,sans-serif">
                            {{ form.title || 'Tanpa Judul' }}
                        </h1>
                        <div v-if="form.thumbnail_preview || form.thumbnail_url" class="mb-8 aspect-[16/9] rounded-xl overflow-hidden">
                            <img :src="form.thumbnail_preview || form.thumbnail_url" class="w-full h-full object-cover" />
                        </div>
                        <BlockRenderer
                            v-if="previewData"
                            :data="previewData"
                            class="article-content"
                        />
                        <p v-else class="text-slate-400 italic text-sm py-4">Belum ada konten...</p>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.preview-enter-active { transition: all 0.25s cubic-bezier(0.16,1,0.3,1); }
.preview-leave-active { transition: all 0.15s ease; }
.preview-enter-from  { opacity: 0; transform: translateY(8px); }
.preview-leave-to    { opacity: 0; }
textarea { outline: none !important; }
</style>
