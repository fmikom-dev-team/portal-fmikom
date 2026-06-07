<script setup>
import { Color } from "@tiptap/extension-color";
import { Highlight } from "@tiptap/extension-highlight";
import { Image } from "@tiptap/extension-image";
import { Link } from "@tiptap/extension-link";
import { Placeholder } from "@tiptap/extension-placeholder";
import { TaskItem } from "@tiptap/extension-task-item";
import { TaskList } from "@tiptap/extension-task-list";
import { TextAlign } from "@tiptap/extension-text-align";
import { TextStyle } from "@tiptap/extension-text-style";
import { Underline } from "@tiptap/extension-underline";
import { Youtube } from "@tiptap/extension-youtube";
import { StarterKit } from "@tiptap/starter-kit";
import { EditorContent, useEditor } from "@tiptap/vue-3";
import {
	AlignCenter,
	AlignLeft,
	AlignRight,
	Bold,
	CheckSquare,
	Download,
	Heading1,
	Heading2,
	Heading3,
	Highlighter,
	Image as ImageIcon,
	Italic,
	Link as LinkIcon,
	List,
	ListOrdered,
	MoreVertical,
	Palette,
	PlusCircle,
	Quote,
	Redo,
	Strikethrough,
	Type,
	Underline as UnderlineIcon,
	Undo,
	Video,
} from "lucide-vue-next";
import { DownloadButton } from "./Extensions/DownloadButton";
// Custom Extensions
import { FontSize } from "./Extensions/FontSize";

const props = defineProps({
	modelValue: String,
	placeholder: {
		type: String,
		default: "Tuliskan konten berita di sini...",
	},
});

const emit = defineEmits(["update:modelValue", "upload-image"]);

const editor = useEditor({
	content: props.modelValue,
	extensions: [
		StarterKit.configure({
			blockquote: {
				HTMLAttributes: {
					class:
						"border-l-4 border-blue-500 pl-6 italic text-slate-600 my-8 text-xl",
				},
			},
		}),
		Underline,
		Link.configure({
			openOnClick: false,
			HTMLAttributes: {
				class: "text-blue-600 underline cursor-pointer",
			},
		}),
		Image.configure({
			inline: true,
			allowBase64: true,
			HTMLAttributes: {
				class: "rounded-lg max-w-full h-auto mx-auto block my-4",
			},
		}),
		Youtube.configure({
			width: 840,
			height: 480,
			HTMLAttributes: {
				class: "rounded-xl overflow-hidden aspect-video w-full my-6",
			},
		}),
		TextAlign.configure({
			types: ["heading", "paragraph", "image", "video"],
		}),
		Placeholder.configure({
			placeholder: props.placeholder,
		}),
		TextStyle,
		FontSize,
		Color,
		Highlight.configure({ multicolor: true }),
		TaskList,
		TaskItem.configure({
			nested: true,
		}),
		DownloadButton,
	],
	editorProps: {
		attributes: {
			class:
				"prose prose-lg max-w-none focus:outline-none min-h-[600px] px-8 py-8 selection:bg-blue-100",
		},
	},
	onUpdate: ({ editor }) => {
		emit("update:modelValue", editor.getHTML());
	},
});

const addImage = () => {
	const input = document.createElement("input");
	input.type = "file";
	input.accept = "image/*";
	input.onchange = (e) => {
		const file = e.target.files[0];
		if (file) {
			emit("upload-image", file, (url) => {
				editor.value.chain().focus().setImage({ src: url }).run();
			});
		}
	};
	input.click();
};

const addVideo = () => {
	const url = window.prompt("Masukkan URL YouTube");
	if (url) {
		editor.value.chain().focus().setYoutubeVideo({ src: url }).run();
	}
};

const setLink = () => {
	const previousUrl = editor.value.getAttributes("link").href;
	const url = window.prompt("URL", previousUrl);

	if (url === null) return;
	if (url === "") {
		editor.value.chain().focus().extendMarkRange("link").unsetLink().run();
		return;
	}

	editor.value
		.chain()
		.focus()
		.extendMarkRange("link")
		.setLink({ href: url })
		.run();
};

const addDownloadButton = () => {
	const url = window.prompt("URL File");
	const text = window.prompt("Teks Tombol", "Download File");
	if (url && text) {
		editor.value.chain().focus().setDownloadButton({ href: url, text }).run();
	}
};

const setFontSize = (size) => {
	if (size === "default") {
		editor.value.chain().focus().unsetFontSize().run();
	} else {
		editor.value.chain().focus().setFontSize(size).run();
	}
};
</script>

<template>
    <div v-if="editor" class="flex flex-col bg-white overflow-hidden relative border-t border-gray-100">
        <!-- Main Toolbar -->
        <div class="sticky top-0 z-20 flex flex-wrap items-center gap-0.5 sm:gap-1 p-1 sm:p-2 bg-white/95 backdrop-blur-md border-b border-gray-100 overflow-x-auto no-scrollbar max-w-full overflow-hidden">
            <!-- Undo/Redo -->
            <div class="flex items-center gap-1 pr-2 border-r border-gray-100 shrink-0">
                <button @click="editor.chain().focus().undo().run()" :disabled="!editor.can().undo()" class="p-2 hover:bg-gray-100 rounded-md disabled:opacity-30 transition-colors">
                    <Undo class="w-4 h-4" />
                </button>
                <button @click="editor.chain().focus().redo().run()" :disabled="!editor.can().redo()" class="p-2 hover:bg-gray-100 rounded-md disabled:opacity-30 transition-colors">
                    <Redo class="w-4 h-4" />
                </button>
            </div>
            
            <!-- Basic Formatting -->
            <div class="flex items-center gap-1 px-2 border-r border-gray-100 shrink-0">
                <button @click="editor.chain().focus().toggleBold().run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive('bold') }" class="p-2 hover:bg-blue-50 rounded-md transition-all" title="Bold">
                    <Bold class="w-4 h-4" />
                </button>
                <button @click="editor.chain().focus().toggleItalic().run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive('italic') }" class="p-2 hover:bg-blue-50 rounded-md transition-all" title="Italic">
                    <Italic class="w-4 h-4" />
                </button>
                <button @click="editor.chain().focus().toggleUnderline().run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive('underline') }" class="p-2 hover:bg-blue-50 rounded-md transition-all" title="Underline">
                    <UnderlineIcon class="w-4 h-4" />
                </button>
                <button @click="editor.chain().focus().toggleStrike().run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive('strike') }" class="p-2 hover:bg-blue-50 rounded-md transition-all" title="Strikethrough">
                    <Strikethrough class="w-4 h-4" />
                </button>
            </div>

            <!-- Font Size & Color -->
            <div class="flex items-center gap-1 px-2 border-r border-gray-100 shrink-0">
                <select @change="setFontSize($event.target.value)" class="text-xs border-none focus:ring-0 bg-transparent py-1 font-bold text-slate-600">
                    <option value="default">Size</option>
                    <option value="12px">12</option>
                    <option value="14px">14</option>
                    <option value="16px">16</option>
                    <option value="18px">18</option>
                    <option value="20px">20</option>
                    <option value="24px">24</option>
                    <option value="32px">32</option>
                </select>
                <div class="h-4 w-px bg-gray-100 mx-1"></div>
                <button @click="editor.chain().focus().setColor('#ef4444').run()" class="p-2 hover:bg-gray-100 rounded-md" title="Red Text">
                    <Palette class="w-4 h-4 text-red-500" />
                </button>
                <button @click="editor.chain().focus().toggleHighlight({ color: '#fef08a' }).run()" :class="{ 'bg-yellow-100': editor.isActive('highlight') }" class="p-2 hover:bg-gray-100 rounded-md" title="Highlight">
                    <Highlighter class="w-4 h-4 text-yellow-600" />
                </button>
            </div>

            <!-- Alignment -->
            <div class="flex items-center gap-1 px-2 border-r border-gray-100 shrink-0">
                <button @click="editor.chain().focus().setTextAlign('left').run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive({ textAlign: 'left' }) }" class="p-2 hover:bg-blue-50 rounded-md transition-all" title="Align Left">
                    <AlignLeft class="w-4 h-4" />
                </button>
                <button @click="editor.chain().focus().setTextAlign('center').run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive({ textAlign: 'center' }) }" class="p-2 hover:bg-blue-50 rounded-md transition-all" title="Align Center">
                    <AlignCenter class="w-4 h-4" />
                </button>
                <button @click="editor.chain().focus().setTextAlign('right').run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive({ textAlign: 'right' }) }" class="p-2 hover:bg-blue-50 rounded-md transition-all" title="Align Right">
                    <AlignRight class="w-4 h-4" />
                </button>
            </div>

            <!-- Headings & Blockquote -->
            <div class="flex items-center gap-1 px-2 border-r border-gray-100 shrink-0">
                <button @click="editor.chain().focus().toggleHeading({ level: 1 }).run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive('heading', { level: 1 }) }" class="p-2 hover:bg-blue-50 rounded-md transition-all font-bold text-xs" title="H1">
                    H1
                </button>
                <button @click="editor.chain().focus().toggleHeading({ level: 2 }).run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive('heading', { level: 2 }) }" class="p-2 hover:bg-blue-50 rounded-md transition-all font-bold text-xs" title="H2">
                    H2
                </button>
                <button @click="editor.chain().focus().toggleBlockquote().run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive('blockquote') }" class="p-2 hover:bg-blue-50 rounded-md transition-all" title="Quote">
                    <Quote class="w-4 h-4" />
                </button>
            </div>

            <!-- Media & Special -->
            <div class="flex items-center gap-1 px-2 shrink-0">
                <button @click="setLink" :class="{ 'bg-blue-50 text-blue-600': editor.isActive('link') }" class="p-2 hover:bg-blue-50 rounded-md transition-all" title="Insert Link">
                    <LinkIcon class="w-4 h-4" />
                </button>
                <button @click="addImage" class="p-2 hover:bg-blue-50 rounded-md transition-all" title="Insert Image">
                    <ImageIcon class="w-4 h-4" />
                </button>
                <button @click="addVideo" class="p-2 hover:bg-blue-50 rounded-md transition-all" title="Insert Video">
                    <Video class="w-4 h-4" />
                </button>
                <button @click="addDownloadButton" :class="{ 'bg-blue-50 text-blue-600': editor.isActive('downloadButton') }" class="p-2 hover:bg-blue-50 rounded-md transition-all" title="Download Button">
                    <Download class="w-4 h-4" />
                </button>
            </div>
        </div>

        <!-- Editor Content -->
        <div class="flex-1 overflow-y-auto bg-gray-50/10">
            <div class="max-w-4xl mx-auto min-h-full py-10">
                <EditorContent :editor="editor" />
            </div>
        </div>
    </div>
</template>

<style>
.prose h1 { font-size: 3rem; font-weight: 900; margin-bottom: 1.5rem; margin-top: 3rem; color: #0f172a; line-height: 1; }
.prose h2 { font-size: 1.875rem; font-weight: 700; margin-bottom: 1rem; margin-top: 2rem; color: #1e293b; line-height: 1.25; }
.prose h3 { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.75rem; margin-top: 1.5rem; color: #1e293b; }
.prose p { margin-bottom: 1.5rem; line-height: 1.625; color: #334155; font-size: 1.125rem; }
.prose blockquote { border-left-width: 4px; border-left-color: #3b82f6; padding-left: 1.5rem; font-style: italic; color: #475569; margin-top: 2rem; margin-bottom: 2rem; font-size: 1.25rem; }
.prose ul { list-style-type: disc; margin-left: 1.5rem; margin-bottom: 1.5rem; }
.prose ol { list-style-type: decimal; margin-left: 1.5rem; margin-bottom: 1.5rem; }
.prose a { color: #2563eb; text-decoration: underline; font-weight: 500; }
.prose img { border-radius: 1rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); margin: 2rem auto; display: block; max-width: 100%; height: auto; }
.prose iframe { border-radius: 1rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); margin: 2rem auto; display: block; width: 100%; aspect-ratio: 16/9; }

.tiptap p.is-editor-empty:first-child::before {
  content: attr(data-placeholder);
  float: left;
  color: #cbd5e1;
  pointer-events: none;
  height: 0;
}

.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* Custom Download Button Styling */
.btn-download {
    display: inline-flex !important;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem !important;
    background-color: #2563eb !important;
    color: white !important;
    border-radius: 0.75rem !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2) !important;
    transition: all 0.2s !important;
    margin: 1rem 0 !important;
}
.btn-download:hover {
    background-color: #1d4ed8 !important;
    transform: translateY(-2px);
    box-shadow: 0 20px 25px -5px rgba(37, 99, 235, 0.3) !important;
}
</style>
