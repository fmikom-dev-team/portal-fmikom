<script setup>
import { CharacterCount } from "@tiptap/extension-character-count";
import { CodeBlockLowlight } from "@tiptap/extension-code-block-lowlight";
import { Color } from "@tiptap/extension-color";
import { Dropcursor } from "@tiptap/extension-dropcursor";
import { Focus } from "@tiptap/extension-focus";
import { FontFamily } from "@tiptap/extension-font-family";
import { Gapcursor } from "@tiptap/extension-gapcursor";
import { Highlight } from "@tiptap/extension-highlight";
import { Link } from "@tiptap/extension-link";
import { Placeholder } from "@tiptap/extension-placeholder";
import { Subscript } from "@tiptap/extension-subscript";
import { Superscript } from "@tiptap/extension-superscript";
import { Table } from "@tiptap/extension-table";
import { TableCell } from "@tiptap/extension-table-cell";
import { TableHeader } from "@tiptap/extension-table-header";
import { TableRow } from "@tiptap/extension-table-row";
import { TaskItem } from "@tiptap/extension-task-item";
import { TaskList } from "@tiptap/extension-task-list";
import { TextAlign } from "@tiptap/extension-text-align";
import { TextStyle } from "@tiptap/extension-text-style";
import { Typography } from "@tiptap/extension-typography";
import { Underline } from "@tiptap/extension-underline";
import { Youtube } from "@tiptap/extension-youtube";
// Extensions
import { StarterKit } from "@tiptap/starter-kit";
import { EditorContent, useEditor } from "@tiptap/vue-3";
import { all, createLowlight } from "lowlight";
import GlobalDragHandle from "tiptap-extension-global-drag-handle";
import ImageResize from "tiptap-extension-resize-image";
import { onMounted, onUnmounted, ref } from "vue";

// Custom extensions & components
import { Callout } from "./extensions/Callout.js";
import BubbleMenu from "./menus/BubbleMenu.vue";
import FloatingMenu from "./menus/FloatingMenu.vue";
import ImageBubbleMenu from "./menus/ImageBubbleMenu.vue";
import ImageUploadModal from "./menus/ImageUploadModal.vue";
import TableBubbleMenu from "./menus/TableBubbleMenu.vue";
import YoutubeModal from "./menus/YoutubeModal.vue";
import { SlashCommandExtension } from "./slash-command/SlashCommandExtension.js";
import suggestion from "./slash-command/suggestion.js";

const lowlight = createLowlight(all);

/* ── props / emits ── */
const props = defineProps({
	modelValue: { type: String, default: "" },
	placeholder: {
		type: String,
		default: "Mulai menulis… ketik '/' untuk perintah",
	},
});
const emit = defineEmits([
	"update:modelValue",
	"upload-image",
	"update:wordCount",
	"update:charCount",
]);

/* ── modal refs ── */
const imageModalRef = ref(null);
const youtubeModalRef = ref(null);

/* ── global event handlers ── */
const onOpenImageModal = () => imageModalRef.value?.open();
const onOpenYoutubeModal = () => youtubeModalRef.value?.open();

// Legacy upload-image event (from paste/drop, still fine)
const onGlobalImageUpload = (e) => {
	if (e.detail?.file) {
		emit("upload-image", e.detail.file, (url) => {
			editor.value?.chain().focus().setImage({ src: url }).run();
		});
	} else {
		// Open the modern modal instead of prompt
		imageModalRef.value?.open();
	}
};

onMounted(() => {
	window.addEventListener("tiptap-open-image-modal", onOpenImageModal);
	window.addEventListener("tiptap-open-youtube-modal", onOpenYoutubeModal);
	window.addEventListener("tiptap-upload-image", onGlobalImageUpload);
});
onUnmounted(() => {
	window.removeEventListener("tiptap-open-image-modal", onOpenImageModal);
	window.removeEventListener("tiptap-open-youtube-modal", onOpenYoutubeModal);
	window.removeEventListener("tiptap-upload-image", onGlobalImageUpload);
});

/* ── handle image upload from modal ── */
const handleImageUpload = (file, callback) => {
	emit("upload-image", file, callback);
};

/* ── editor ── */
const editor = useEditor({
	content: props.modelValue,
	extensions: [
		StarterKit.configure({ codeBlock: false }),
		Typography,
		Placeholder.configure({ placeholder: props.placeholder }),
		Link.configure({
			openOnClick: false,
			HTMLAttributes: { rel: "noopener noreferrer" },
		}),
		ImageResize.configure({ inline: false, allowBase64: true }),
		Youtube.configure({ controls: true, nocookie: true }),
		TextAlign.configure({ types: ["heading", "paragraph", "image"] }),
		TextStyle,
		FontFamily,
		Color,
		Highlight.configure({ multicolor: true }),
		Underline,
		Subscript,
		Superscript,
		Dropcursor.configure({ color: "#3b82f6", width: 2 }),
		Gapcursor,
		TaskList,
		TaskItem.configure({ nested: true }),
		Table.configure({ resizable: true }),
		TableRow,
		TableHeader,
		TableCell,
		CodeBlockLowlight.configure({ lowlight }),
		CharacterCount,
		Focus.configure({ className: "has-focus", mode: "all" }),
		Callout,
		GlobalDragHandle.configure({
			dragHandleWidth: 24,
			scrollTreshold: 100,
		}),
		SlashCommandExtension.configure({ suggestion }),
	],

	editorProps: {
		attributes: {
			class: "focus:outline-none min-h-[500px] pb-40",
		},

		// Paste images directly
		handlePaste(_view, event) {
			const items =
				(event.clipboardData || event.originalEvent?.clipboardData)?.items ??
				[];
			for (const item of items) {
				if (item.type.startsWith("image/")) {
					const file = item.getAsFile();
					emit("upload-image", file, (url) => {
						editor.value?.chain().focus().setImage({ src: url }).run();
					});
					event.preventDefault();
					return true;
				}
			}
			return false;
		},

		// Drop images
		handleDrop(_view, event, _slice, moved) {
			if (!moved && event.dataTransfer?.files?.[0]) {
				const file = event.dataTransfer.files[0];
				if (file.type.startsWith("image/")) {
					emit("upload-image", file, (url) => {
						editor.value?.chain().focus().setImage({ src: url }).run();
					});
					event.preventDefault();
					return true;
				}
			}
			return false;
		},
	},

	onUpdate: ({ editor }) => {
		emit("update:modelValue", editor.getHTML());
		emit("update:wordCount", editor.storage.characterCount.words());
		emit("update:charCount", editor.storage.characterCount.characters());
	},
});
</script>

<template>
    <div v-if="editor" class="relative flex flex-col w-full h-full bg-white group/editor">

        <!-- Minimal undo/redo ghost bar -->
        <div class="absolute top-3 right-4 z-10 flex items-center gap-1 opacity-0 group-hover/editor:opacity-100 transition-all duration-300 pointer-events-none group-hover/editor:pointer-events-auto">
            <button
                @click="editor.chain().focus().undo().run()"
                :disabled="!editor.can().undo()"
                class="w-8 h-8 flex items-center justify-center bg-white/80 backdrop-blur-sm border border-gray-100 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-gray-50 disabled:opacity-25 transition-all shadow-sm"
                title="Undo (⌘Z)"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M3 7v6h6"/><path d="M21 17A9 9 0 0 0 3 13"/></svg>
            </button>
            <button
                @click="editor.chain().focus().redo().run()"
                :disabled="!editor.can().redo()"
                class="w-8 h-8 flex items-center justify-center bg-white/80 backdrop-blur-sm border border-gray-100 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-gray-50 disabled:opacity-25 transition-all shadow-sm"
                title="Redo (⌘⇧Z)"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 7v6h-6"/><path d="M3 17a9 9 0 0 1 18-4"/></svg>
            </button>
        </div>

        <!-- Writing area -->
        <div class="flex-1 overflow-y-auto w-full flex justify-center py-16">
            <div class="max-w-[680px] w-full px-4 sm:px-0 relative">
                <EditorContent :editor="editor" class="w-full" />

                <!-- Contextual menus -->
                <BubbleMenu       :editor="editor" />
                <TableBubbleMenu  :editor="editor" />
                <ImageBubbleMenu  :editor="editor" />
                <FloatingMenu     :editor="editor" />
            </div>
        </div>

        <!-- Modern Modals (NO window.prompt) -->
        <ImageUploadModal
            ref="imageModalRef"
            :editor="editor"
            @upload-image="handleImageUpload"
        />
        <YoutubeModal
            ref="youtubeModalRef"
            :editor="editor"
        />
    </div>
</template>

<style>
/* ── Google Fonts ── */
@import url('https://fonts.bunny.net/css?family=inter:400,400i,500,500i,600,700,800|plus-jakarta-sans:500,600,700,800&display=swap');

/* ── Base ── */
.ProseMirror {
    outline: none;
    line-height: 1.75;
    color: #374151;
    font-family: 'Inter', system-ui, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* Spacing between blocks */
.ProseMirror > * + * { margin-top: 1.1em; }
.ProseMirror > * + h1,
.ProseMirror > * + h2,
.ProseMirror > * + h3 { margin-top: 2em; }

/* ── Headings ── */
.ProseMirror h1,
.ProseMirror h2,
.ProseMirror h3 {
    font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: #111827;
    line-height: 1.2;
    margin-bottom: 0.35em;
}
.ProseMirror h1 { font-size: 2.5rem; }
.ProseMirror h2 { font-size: 1.875rem; }
.ProseMirror h3 { font-size: 1.4rem; }

/* ── Paragraph ── */
.ProseMirror p {
    font-size: 1.0625rem;
    letter-spacing: -0.012em;
    color: #374151;
}

/* ── Placeholder ── */
.ProseMirror p.is-editor-empty:first-child::before {
    content: attr(data-placeholder);
    float: left;
    height: 0;
    pointer-events: none;
    color: #d1d5db;
    font-weight: 400;
}

/* ── Blockquote ── */
.ProseMirror blockquote {
    border-left: 3px solid #111827;
    margin: 1.75rem 0;
    padding: 0.125rem 0 0.125rem 1.5rem;
    font-style: italic;
    font-size: 1.2rem;
    color: #4b5563;
    line-height: 1.65;
}

/* ── Callout ── */
.callout-block {
    display: flex;
    align-items: flex-start;
    gap: 0.875rem;
    padding: 1rem 1.25rem;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 12px;
    margin: 1.5rem 0;
    color: #0369a1;
    line-height: 1.6;
}
.callout-block::before {
    content: "💡";
    font-size: 1.35rem;
    flex-shrink: 0;
    margin-top: 0.05rem;
}

/* ── Task list ── */
ul[data-type="taskList"] { list-style: none; padding: 0; }
ul[data-type="taskList"] li {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
}
ul[data-type="taskList"] li > label {
    flex-shrink: 0;
    margin-top: 0.3rem;
    cursor: pointer;
    user-select: none;
}
ul[data-type="taskList"] li > label input[type="checkbox"] {
    width: 1rem;
    height: 1rem;
    border-radius: 4px;
    accent-color: #3b82f6;
    cursor: pointer;
}
ul[data-type="taskList"] li > div { flex: 1 1 auto; }
ul[data-type="taskList"] li[data-checked="true"] > div {
    text-decoration: line-through;
    color: #9ca3af;
}

/* ── Code ── */
.ProseMirror code {
    font-family: ui-monospace, 'Cascadia Code', Menlo, monospace;
    font-size: 0.875em;
    padding: 0.15em 0.4em;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    color: #e11d48;
}
.ProseMirror pre {
    background: #0f172a;
    color: #e2e8f0;
    font-family: ui-monospace, 'Cascadia Code', Menlo, monospace;
    font-size: 0.875rem;
    line-height: 1.7;
    padding: 1.25rem 1.5rem;
    border-radius: 14px;
    overflow-x: auto;
    margin: 2rem 0;
    box-shadow: 0 4px 24px rgba(0,0,0,0.15);
}
.ProseMirror pre code {
    background: none;
    border: none;
    padding: 0;
    color: inherit;
    font-size: inherit;
}

/* ── Images ── */
.ProseMirror img {
    max-width: 100%;
    height: auto;
    border-radius: 14px;
    margin: 1.75rem 0;
    box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    display: block;
    transition: box-shadow 0.2s, outline 0.15s;
}
.ProseMirror img.ProseMirror-selectednode {
    outline: 3px solid #3b82f6;
    outline-offset: 3px;
    box-shadow: 0 0 0 6px rgba(59,130,246,0.12);
}

/* ── Tables ── */
.ProseMirror table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    margin: 2rem 0;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.ProseMirror table th,
.ProseMirror table td {
    border-right: 1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
    padding: 0.65rem 1rem;
    vertical-align: top;
    background: #fff;
    position: relative;
    box-sizing: border-box;
}
.ProseMirror table th {
    background: #f9fafb;
    font-weight: 600;
    font-size: 0.8125rem;
    color: #374151;
    text-align: left;
}
.ProseMirror table tr:last-child td  { border-bottom: none; }
.ProseMirror table td:last-child,
.ProseMirror table th:last-child     { border-right: none; }
.ProseMirror table tr:hover td       { background: #f9fafb; }
.ProseMirror table .selectedCell::after {
    position: absolute; inset: 0; z-index: 2;
    content: ''; pointer-events: none;
    background: rgba(59,130,246,0.08);
}
.ProseMirror table .column-resize-handle {
    position: absolute;
    right: -2px; top: 0; bottom: 0;
    width: 4px;
    background: #3b82f6;
    pointer-events: none;
    z-index: 10;
}
.ProseMirror table p { margin: 0; font-size: 0.875rem; }

/* ── Lists ── */
.ProseMirror ul { list-style: disc; padding-left: 1.5rem; }
.ProseMirror ol { list-style: decimal; padding-left: 1.5rem; }
.ProseMirror li + li { margin-top: 0.35em; }

/* ── HR ── */
.ProseMirror hr {
    border: none;
    border-top: 1px solid #e5e7eb;
    margin: 2.5rem 0;
}

/* ── YouTube embeds ── */
.ProseMirror iframe {
    width: 100% !important;
    max-width: 100%;
    border-radius: 14px;
    overflow: hidden;
    margin: 1.75rem 0;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    display: block;
}

/* ── Drag handle ── */
.drag-handle {
    border-radius: 5px;
    transition: opacity 0.18s, background 0.18s;
    opacity: 0.4;
}
.drag-handle:hover {
    background: rgba(0,0,0,0.06);
    opacity: 1;
}

/* ── Focus/has-focus ── */
.has-focus { border-radius: 6px; }

/* ── Selection color ── */
.ProseMirror ::selection {
    background: rgba(59,130,246,0.18);
}
</style>
