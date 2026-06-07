<script setup>
import {
	AlignCenter,
	AlignJustify,
	AlignLeft,
	AlignRight,
	Bold,
	Braces,
	Code,
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
	ListTodo,
	Minus,
	Palette,
	Quote,
	Redo,
	Strikethrough,
	Subscript,
	Superscript,
	Table as TableIcon,
	Type,
	Underline as UnderlineIcon,
	Undo,
	Video,
} from "lucide-vue-next";

const props = defineProps({
	editor: {
		type: Object,
		required: true,
	},
});

const emit = defineEmits(["upload-image"]);

const addImage = () => {
	const input = document.createElement("input");
	input.type = "file";
	input.accept = "image/*";
	input.onchange = (e) => {
		const file = e.target.files[0];
		if (file) {
			emit("upload-image", file);
		}
	};
	input.click();
};

const addVideo = () => {
	const url = window.prompt("Masukkan URL YouTube");
	if (url) {
		props.editor.chain().focus().setYoutubeVideo({ src: url }).run();
	}
};

const setLink = () => {
	const previousUrl = props.editor.getAttributes("link").href;
	const url = window.prompt("URL", previousUrl);

	if (url === null) return;
	if (url === "") {
		props.editor.chain().focus().extendMarkRange("link").unsetLink().run();
		return;
	}

	props.editor
		.chain()
		.focus()
		.extendMarkRange("link")
		.setLink({ href: url })
		.run();
};

const insertTable = () => {
	props.editor
		.chain()
		.focus()
		.insertTable({ rows: 3, cols: 3, withHeaderRow: true })
		.run();
};

const addHorizontalRule = () => {
	props.editor.chain().focus().setHorizontalRule().run();
};
</script>

<template>
    <div class="sticky top-0 z-20 flex flex-wrap items-center gap-0.5 sm:gap-1 p-2 bg-white border-b border-gray-100 shadow-sm overflow-x-auto no-scrollbar max-w-full">
        <!-- Undo/Redo -->
        <div class="flex items-center gap-1 pr-2 border-r border-gray-100 shrink-0">
            <button @click="editor.chain().focus().undo().run()" :disabled="!editor.can().undo()" class="p-1.5 hover:bg-gray-100 rounded-md disabled:opacity-30 transition-colors text-slate-600" title="Undo">
                <Undo class="w-4 h-4" />
            </button>
            <button @click="editor.chain().focus().redo().run()" :disabled="!editor.can().redo()" class="p-1.5 hover:bg-gray-100 rounded-md disabled:opacity-30 transition-colors text-slate-600" title="Redo">
                <Redo class="w-4 h-4" />
            </button>
        </div>
        
        <!-- Text Styles -->
        <div class="flex items-center gap-1 px-2 border-r border-gray-100 shrink-0">
            <button @click="editor.chain().focus().toggleBold().run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive('bold') }" class="p-1.5 hover:bg-blue-50 rounded-md transition-all text-slate-600" title="Bold">
                <Bold class="w-4 h-4" />
            </button>
            <button @click="editor.chain().focus().toggleItalic().run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive('italic') }" class="p-1.5 hover:bg-blue-50 rounded-md transition-all text-slate-600" title="Italic">
                <Italic class="w-4 h-4" />
            </button>
            <button @click="editor.chain().focus().toggleUnderline().run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive('underline') }" class="p-1.5 hover:bg-blue-50 rounded-md transition-all text-slate-600" title="Underline">
                <UnderlineIcon class="w-4 h-4" />
            </button>
            <button @click="editor.chain().focus().toggleStrike().run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive('strike') }" class="p-1.5 hover:bg-blue-50 rounded-md transition-all text-slate-600" title="Strikethrough">
                <Strikethrough class="w-4 h-4" />
            </button>
            <button @click="editor.chain().focus().toggleSubscript().run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive('subscript') }" class="p-1.5 hover:bg-blue-50 rounded-md transition-all text-slate-600" title="Subscript">
                <Subscript class="w-4 h-4" />
            </button>
            <button @click="editor.chain().focus().toggleSuperscript().run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive('superscript') }" class="p-1.5 hover:bg-blue-50 rounded-md transition-all text-slate-600" title="Superscript">
                <Superscript class="w-4 h-4" />
            </button>
            <button @click="editor.chain().focus().toggleCode().run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive('code') }" class="p-1.5 hover:bg-blue-50 rounded-md transition-all text-slate-600" title="Inline Code">
                <Code class="w-4 h-4" />
            </button>
            <button @click="editor.chain().focus().toggleHighlight().run()" :class="{ 'bg-yellow-100 text-yellow-600': editor.isActive('highlight') }" class="p-1.5 hover:bg-gray-100 rounded-md text-slate-600" title="Highlight">
                <Highlighter class="w-4 h-4" />
            </button>
        </div>

        <!-- Headings -->
        <div class="flex items-center gap-1 px-2 border-r border-gray-100 shrink-0">
            <button @click="editor.chain().focus().toggleHeading({ level: 1 }).run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive('heading', { level: 1 }) }" class="p-1.5 hover:bg-blue-50 rounded-md transition-all font-bold text-xs text-slate-600" title="Heading 1">
                H1
            </button>
            <button @click="editor.chain().focus().toggleHeading({ level: 2 }).run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive('heading', { level: 2 }) }" class="p-1.5 hover:bg-blue-50 rounded-md transition-all font-bold text-xs text-slate-600" title="Heading 2">
                H2
            </button>
            <button @click="editor.chain().focus().toggleHeading({ level: 3 }).run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive('heading', { level: 3 }) }" class="p-1.5 hover:bg-blue-50 rounded-md transition-all font-bold text-xs text-slate-600" title="Heading 3">
                H3
            </button>
        </div>

        <!-- Lists & Blocks -->
        <div class="flex items-center gap-1 px-2 border-r border-gray-100 shrink-0">
            <button @click="editor.chain().focus().toggleBulletList().run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive('bulletList') }" class="p-1.5 hover:bg-blue-50 rounded-md transition-all text-slate-600" title="Bullet List">
                <List class="w-4 h-4" />
            </button>
            <button @click="editor.chain().focus().toggleOrderedList().run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive('orderedList') }" class="p-1.5 hover:bg-blue-50 rounded-md transition-all text-slate-600" title="Ordered List">
                <ListOrdered class="w-4 h-4" />
            </button>
            <button @click="editor.chain().focus().toggleTaskList().run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive('taskList') }" class="p-1.5 hover:bg-blue-50 rounded-md transition-all text-slate-600" title="Task List">
                <ListTodo class="w-4 h-4" />
            </button>
            <button @click="editor.chain().focus().toggleBlockquote().run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive('blockquote') }" class="p-1.5 hover:bg-blue-50 rounded-md transition-all text-slate-600" title="Quote">
                <Quote class="w-4 h-4" />
            </button>
            <button @click="editor.chain().focus().toggleCodeBlock().run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive('codeBlock') }" class="p-1.5 hover:bg-blue-50 rounded-md transition-all text-slate-600" title="Code Block">
                <Braces class="w-4 h-4" />
            </button>
        </div>

        <!-- Alignment -->
        <div class="flex items-center gap-1 px-2 border-r border-gray-100 shrink-0">
            <button @click="editor.chain().focus().setTextAlign('left').run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive({ textAlign: 'left' }) }" class="p-1.5 hover:bg-blue-50 rounded-md transition-all text-slate-600" title="Align Left">
                <AlignLeft class="w-4 h-4" />
            </button>
            <button @click="editor.chain().focus().setTextAlign('center').run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive({ textAlign: 'center' }) }" class="p-1.5 hover:bg-blue-50 rounded-md transition-all text-slate-600" title="Align Center">
                <AlignCenter class="w-4 h-4" />
            </button>
            <button @click="editor.chain().focus().setTextAlign('right').run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive({ textAlign: 'right' }) }" class="p-1.5 hover:bg-blue-50 rounded-md transition-all text-slate-600" title="Align Right">
                <AlignRight class="w-4 h-4" />
            </button>
            <button @click="editor.chain().focus().setTextAlign('justify').run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive({ textAlign: 'justify' }) }" class="p-1.5 hover:bg-blue-50 rounded-md transition-all text-slate-600" title="Justify">
                <AlignJustify class="w-4 h-4" />
            </button>
        </div>

        <!-- Media & Elements -->
        <div class="flex items-center gap-1 px-2 shrink-0">
            <button @click="setLink" :class="{ 'bg-blue-50 text-blue-600': editor.isActive('link') }" class="p-1.5 hover:bg-blue-50 rounded-md transition-all text-slate-600" title="Insert Link">
                <LinkIcon class="w-4 h-4" />
            </button>
            <button @click="addImage" class="p-1.5 hover:bg-blue-50 rounded-md transition-all text-slate-600" title="Insert Image">
                <ImageIcon class="w-4 h-4" />
            </button>
            <button @click="addVideo" class="p-1.5 hover:bg-blue-50 rounded-md transition-all text-slate-600" title="Insert YouTube Video">
                <Video class="w-4 h-4" />
            </button>
            <button @click="insertTable" class="p-1.5 hover:bg-blue-50 rounded-md transition-all text-slate-600" title="Insert Table">
                <TableIcon class="w-4 h-4" />
            </button>
            <button @click="addHorizontalRule" class="p-1.5 hover:bg-blue-50 rounded-md transition-all text-slate-600" title="Horizontal Rule">
                <Minus class="w-4 h-4" />
            </button>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
