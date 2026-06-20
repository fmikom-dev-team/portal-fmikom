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
import { computed, ref, watch } from "vue";
import EditorJsEditor from "@/components/editor/EditorJsEditor.vue";
import BlockRenderer from "@/components/editor/renderer/BlockRenderer.vue";
import EditorSidebar from "@/components/editor/sidebar/EditorSidebar.vue";

const props = defineProps({ categories: Array });

const form = useForm({
	title: "",
	slug: "",
	content: "", // stores Editor.js JSON string
	excerpt: "",
	category_id: null,
	tags: [],
	status: "draft",
	published_at: "",
	meta_title: "",
	meta_description: "",
	thumbnail: null,
	thumbnail_preview: null,
	og_image: null,
	og_image_preview: null,
});

/* ── Auto-slug from title ── */
watch(
	() => form.title,
	(val) => {
		if (!form.slug || form.status === "draft") {
			form.slug = val
				.toLowerCase()
				.replace(/[^\w\s-]/g, "")
				.replace(/[\s_-]+/g, "-")
				.replace(/(?:^-+)|(?:-+$)/g, "");
		}
	},
);

/* ── Autosave state ── */
let autosaveTimer = null;
const autosaveState = ref("idle"); // 'idle' | 'unsaved' | 'saving' | 'saved' | 'error'
const wordCount = ref(0);
const readTime = computed(() => Math.max(1, Math.ceil(wordCount.value / 200)));

const triggerAutosave = () => {
	if (form.status !== "draft") return;
	autosaveState.value = "unsaved";
	clearTimeout(autosaveTimer);
	autosaveTimer = setTimeout(() => {
		// On Create, we just reflect local state — actual save on submit
		autosaveState.value = "saved";
		setTimeout(() => {
			if (autosaveState.value === "saved") autosaveState.value = "idle";
		}, 3000);
	}, 2000);
};

watch(() => form.content, triggerAutosave);
watch(() => form.title, triggerAutosave);

/* ── Image upload handler ── */
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

/* ── Thumbnail / OG ── */
const handleThumbnailUpdate = (file) => {
	if (file) {
		form.thumbnail = file;
		form.thumbnail_preview = URL.createObjectURL(file);
	} else {
		form.thumbnail = null;
		form.thumbnail_preview = null;
	}
};
const handleOgImageUpdate = (file) => {
	if (file) {
		form.og_image = file;
		form.og_image_preview = URL.createObjectURL(file);
	} else {
		form.og_image = null;
		form.og_image_preview = null;
	}
};

/* ── Submit ── */
const editorRef = ref(null);

const submit = async (status) => {
	// Pastikan mengambil data terbaru dari editor sebelum submit
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
	form.post("/portal-admin/posts", { forceFormData: true });
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
</script>

<template>
    <Head>
        <title>Buat Berita Baru</title>
    </Head>

    <div class="fixed inset-0 flex flex-col bg-white z-[60] font-sans antialiased overflow-hidden">

        <!-- ── Top Bar ── -->
        <header class="h-[56px] border-b border-gray-100 flex items-center justify-between px-4 sm:px-6 bg-white/95 backdrop-blur-sm shrink-0 z-50">
            <div class="flex items-center gap-3">
                <Link href="/portal-admin/posts"
                      class="w-8 h-8 flex items-center justify-center hover:bg-gray-100 rounded-lg text-slate-400 hover:text-slate-700 transition-colors">
                    <ChevronLeft class="w-5 h-5" />
                </Link>
                <div class="h-4 w-px bg-gray-200" />

                <!-- Title + autosave status -->
                <div class="flex flex-col gap-0.5">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-slate-800 truncate max-w-[200px] hidden sm:block">
                            {{ form.title || 'Draft Baru' }}
                        </span>
                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-[10px] font-bold uppercase tracking-wider">Draft</span>
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
                            <span class="text-red-500">Gagal menyimpan</span>
                        </template>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <!-- Stats -->
                <div class="hidden lg:flex items-center gap-3 mr-2 text-[11px] font-medium text-slate-400 border-r border-gray-100 pr-4">
                    <span>{{ wordCount }} kata</span>
                    <span>~{{ readTime }} mnt baca</span>
                </div>

                <!-- Preview -->
                <button @click="openPreview"
                        class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-gray-100 rounded-lg transition-colors">
                    <Eye class="w-4 h-4" /> Preview
                </button>

                <!-- Save draft -->
                <button @click="submit('draft')" :disabled="form.processing"
                        class="hidden md:flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-gray-100 rounded-lg transition-colors">
                    <Save class="w-4 h-4" /> Simpan
                </button>

                <!-- Publish -->
                <button @click="submit('published')" :disabled="form.processing"
                        class="flex items-center gap-1.5 px-4 py-1.5 text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white rounded-lg shadow-sm transition-all active:scale-95">
                    <Loader2 v-if="form.processing" class="w-3.5 h-3.5 animate-spin" />
                    <Send v-else class="w-3.5 h-3.5" />
                    Publish
                </button>

                <!-- Mobile sidebar toggle -->
                <button @click="settingsOpen = !settingsOpen" class="lg:hidden p-2 text-slate-500 hover:bg-gray-100 rounded-lg">
                    <PanelRight class="w-5 h-5" />
                </button>
            </div>
        </header>

        <!-- ── Main ── -->
        <main class="flex-1 flex overflow-hidden">

            <!-- Writing area -->
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

            <!-- Sidebar -->
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
                        <div v-if="form.thumbnail_preview" class="mb-8 aspect-[16/9] rounded-xl overflow-hidden">
                            <img :src="form.thumbnail_preview" alt="Pratinjau Thumbnail" class="w-full h-full object-cover" />
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

<style>
/* ── Shared preview & public render styles ── */
.editorjs-public h1, .editorjs-public h2, .editorjs-public h3, .editorjs-public h4 {
    font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
    font-weight: 800; letter-spacing: -0.03em; color: #111827; line-height: 1.2;
    margin-top: 2.5rem; margin-bottom: 0.75rem;
}
.editorjs-public h1 { font-size: 2.25rem; }
.editorjs-public h2 { font-size: 1.75rem; }
.editorjs-public h3 { font-size: 1.35rem; }
.editorjs-public p  { font-size: 1.0625rem; line-height: 1.8; color: #374151; margin-bottom: 1.25rem; }
.editorjs-public blockquote {
    border-left: 3px solid #111827;
    padding: 0.5rem 0 0.5rem 1.5rem;
    font-style: italic; font-size: 1.15rem; color: #4b5563; margin: 2rem 0;
}
.editorjs-public pre {
    background: #0f172a; color: #e2e8f0; border-radius: 12px;
    padding: 1.25rem 1.5rem; overflow-x: auto; margin: 1.75rem 0;
    font-family: ui-monospace, Menlo, monospace; font-size: 0.875rem; line-height: 1.7;
}
.editorjs-public code {
    font-family: ui-monospace, Menlo, monospace;
    font-size: 0.875em; background: #f1f5f9; border-radius: 4px; padding: 0.15em 0.4em;
}
.editorjs-public hr {
    border: none; text-align: center; margin: 2rem 0; font-size: 1.25rem;
    color: #d1d5db; letter-spacing: 0.5em;
}
.editorjs-public hr::before { content: '· · ·'; }
/* image */
.editorjs-public figure.editorjs-image { margin: 2rem 0; }
.editorjs-public figure.editorjs-image img {
    border-radius: 14px; width: 100%; height: auto;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08); display: block;
}
.editorjs-public figure.editorjs-image figcaption {
    text-align: center; font-size: 0.875rem; color: #6b7280; margin-top: 0.75rem;
}
/* table */
.editorjs-public .editorjs-table-wrapper { overflow-x: auto; margin: 2rem 0; }
.editorjs-public table { border-collapse: collapse; width: 100%; }
.editorjs-public table th, .editorjs-public table td {
    border: 1px solid #e5e7eb; padding: 0.65rem 1rem; font-size: 0.9rem; text-align: left;
}
.editorjs-public table th { background: #f9fafb; font-weight: 600; }
/* attachment */
.editorjs-public a.editorjs-attachment {
    display: flex; align-items: center; gap: 0.75rem;
    background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px;
    padding: 0.875rem 1.25rem; text-decoration: none; color: #374151;
    margin: 1.5rem 0; transition: background 0.15s;
}
.editorjs-public a.editorjs-attachment:hover { background: #eff6ff; border-color: #bfdbfe; }
/* warning */
.editorjs-public .editorjs-warning {
    background: #fffbeb; border-left: 4px solid #f59e0b; border-radius: 12px;
    padding: 1rem 1.25rem; margin: 1.5rem 0;
}
.editorjs-public .editorjs-warning strong { display: block; color: #92400e; margin-bottom: 0.25rem; }
.editorjs-public .editorjs-warning p { color: #78350f; margin: 0; }
/* checklist */
.editorjs-public .checklist { list-style: none !important; padding: 0; margin: 1.5rem 0; }
.editorjs-public .checklist-item {
    display: flex; align-items: flex-start; gap: 0.75rem; margin-bottom: 0.5rem;
}
.editorjs-public .checklist-box {
    width: 1.25rem; height: 1.25rem; border: 2px solid #d1d5db; border-radius: 5px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 0.75rem; flex-shrink: 0; margin-top: 0.1rem;
}
.editorjs-public .checklist-item.checked .checklist-box {
    background: #3b82f6; border-color: #3b82f6; color: white;
}
.editorjs-public .checklist-item.checked span:last-child {
    text-decoration: line-through; color: #9ca3af;
}
/* alert */
.editorjs-public .editorjs-alert {
    border-radius: 12px; padding: 0.875rem 1.25rem; margin: 1.5rem 0;
    border-left-width: 4px; border-left-style: solid;
}
.editorjs-public .editorjs-alert--info    { background: #eff6ff; border-color: #3b82f6; color: #1e40af; }
.editorjs-public .editorjs-alert--success { background: #f0fdf4; border-color: #22c55e; color: #15803d; }
.editorjs-public .editorjs-alert--warning { background: #fffbeb; border-color: #f59e0b; color: #92400e; }
.editorjs-public .editorjs-alert--danger  { background: #fef2f2; border-color: #ef4444; color: #991b1b; }
/* embed */
.editorjs-public .editorjs-embed { margin: 2rem 0; position: relative; }
.editorjs-public .editorjs-embed iframe {
    width: 100% !important; border-radius: 14px; display: block;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}
.editorjs-public .embed-caption {
    text-align: center; font-size: 0.875rem; color: #6b7280; margin-top: 0.5rem;
}
</style>
