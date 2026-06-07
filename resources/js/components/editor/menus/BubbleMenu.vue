<script setup>
import tippy from "tippy.js";
import { onBeforeUnmount, onMounted, ref } from "vue";
import "tippy.js/dist/tippy.css";
import "tippy.js/animations/shift-away.css";
import {
	Bold,
	Code,
	Highlighter,
	Italic,
	Link as LinkIcon,
	Strikethrough,
	Type,
	Underline as UnderlineIcon,
} from "lucide-vue-next";
import FloatingLinkEditor from "./FloatingLinkEditor.vue";

const props = defineProps({ editor: Object });

const menuRef = ref(null);
const linkEditorRef = ref(null);
let tippyInstance = null;

/* ── position menu on selection ── */
const updateMenu = () => {
	if (!props.editor || !tippyInstance) return;

	const { view, state } = props.editor;
	const { selection } = state;
	const isImage = props.editor.isActive("image");
	const isTable = props.editor.isActive("table");
	const hasText = !selection.empty && !isImage && !isTable;

	if (!hasText) {
		tippyInstance.hide();
		return;
	}

	const from = Math.min(...selection.ranges.map((r) => r.$from.pos));
	const to = Math.max(...selection.ranges.map((r) => r.$to.pos));
	const start = view.coordsAtPos(from);
	const end = view.coordsAtPos(to);

	tippyInstance.setProps({
		getReferenceClientRect: () => ({
			width: end.left - start.left,
			height: start.bottom - start.top,
			top: start.top,
			bottom: start.bottom,
			left: start.left,
			right: end.right,
		}),
	});
	tippyInstance.show();
};

onMounted(() => {
	tippyInstance = tippy(document.body, {
		content: menuRef.value,
		interactive: true,
		trigger: "manual",
		placement: "top",
		animation: "shift-away",
		appendTo: () => document.body,
	});
	props.editor.on("selectionUpdate", updateMenu);
	props.editor.on("update", updateMenu);
});

onBeforeUnmount(() => {
	props.editor?.off("selectionUpdate", updateMenu);
	props.editor?.off("update", updateMenu);
	tippyInstance?.destroy();
});

/* ── link ── */
const openLinkEditor = () => linkEditorRef.value?.show();

/* ── heading cycle ── */
const HEADING_LABELS = { 0: "P", 1: "H1", 2: "H2", 3: "H3" };
const currentHeadingLevel = () => {
	for (let l = 1; l <= 3; l++) {
		if (props.editor.isActive("heading", { level: l })) return l;
	}
	return 0;
};
const cycleHeading = () => {
	const next = (currentHeadingLevel() + 1) % 4;
	if (next === 0) props.editor.chain().focus().setParagraph().run();
	else props.editor.chain().focus().setHeading({ level: next }).run();
};
</script>

<template>
    <!-- Bubble toolbar -->
    <div
        ref="menuRef"
        class="bg-slate-900/95 backdrop-blur-xl border border-white/10
               shadow-[0_8px_24px_rgba(0,0,0,0.25)] rounded-[16px]
               p-1 flex items-center gap-0.5 text-white select-none"
    >
        <!-- Heading cycle -->
        <button
            @click="cycleHeading"
            class="px-2.5 py-2 text-xs font-black hover:bg-white/10 rounded-lg transition-colors tracking-wide min-w-[32px] text-center"
            :class="currentHeadingLevel() > 0 ? 'text-blue-300 bg-white/10' : 'text-slate-300'"
            title="Heading"
        >
            {{ HEADING_LABELS[currentHeadingLevel()] }}
        </button>

        <div class="w-px h-4 bg-white/15 mx-0.5" />

        <!-- B / I / U / S -->
        <button @click="editor.chain().focus().toggleBold().run()"
                :class="editor.isActive('bold') ? 'bg-white/15 text-white' : 'text-slate-300'"
                class="p-2 hover:bg-white/10 rounded-lg transition-colors font-bold text-[13px] w-8 h-8 flex items-center justify-center"
                title="Bold (Ctrl+B)">B</button>

        <button @click="editor.chain().focus().toggleItalic().run()"
                :class="editor.isActive('italic') ? 'bg-white/15 text-white' : 'text-slate-300'"
                class="p-2 hover:bg-white/10 rounded-lg transition-colors italic text-[13px] w-8 h-8 flex items-center justify-center"
                title="Italic (Ctrl+I)">I</button>

        <button @click="editor.chain().focus().toggleUnderline().run()"
                :class="editor.isActive('underline') ? 'bg-white/15 text-white' : 'text-slate-300'"
                class="p-2 hover:bg-white/10 rounded-lg transition-colors underline text-[13px] w-8 h-8 flex items-center justify-center"
                title="Underline (Ctrl+U)">U</button>

        <button @click="editor.chain().focus().toggleStrike().run()"
                :class="editor.isActive('strike') ? 'bg-white/15 text-white' : 'text-slate-300'"
                class="p-2 hover:bg-white/10 rounded-lg transition-colors line-through text-[13px] w-8 h-8 flex items-center justify-center"
                title="Strikethrough">S</button>

        <div class="w-px h-4 bg-white/15 mx-0.5" />

        <!-- Highlight -->
        <button @click="editor.chain().focus().toggleHighlight().run()"
                :class="editor.isActive('highlight') ? 'bg-amber-500/30 text-amber-300' : 'text-slate-300'"
                class="p-2 hover:bg-white/10 rounded-lg transition-colors"
                title="Highlight">
            <Highlighter class="w-3.5 h-3.5" />
        </button>

        <!-- Color -->
        <label class="relative p-2 hover:bg-white/10 rounded-lg transition-colors cursor-pointer" title="Text Color">
            <Type class="w-3.5 h-3.5 text-slate-300" />
            <input
                type="color"
                class="absolute inset-0 opacity-0 w-full h-full cursor-pointer"
                :value="editor.getAttributes('textStyle').color || '#000000'"
                @input="e => editor.chain().focus().setColor(e.target.value).run()"
            />
        </label>

        <!-- Code -->
        <button @click="editor.chain().focus().toggleCode().run()"
                :class="editor.isActive('code') ? 'bg-white/15 text-white' : 'text-slate-300'"
                class="p-2 hover:bg-white/10 rounded-lg transition-colors"
                title="Inline Code">
            <Code class="w-3.5 h-3.5" />
        </button>

        <div class="w-px h-4 bg-white/15 mx-0.5" />

        <!-- Link — opens FloatingLinkEditor, NO prompt() -->
        <button @click="openLinkEditor"
                :class="editor.isActive('link') ? 'bg-blue-500/30 text-blue-300' : 'text-slate-300'"
                class="p-2 hover:bg-white/10 rounded-lg transition-colors"
                title="Link">
            <LinkIcon class="w-3.5 h-3.5" />
        </button>

        <!-- Font family -->
        <select
            @change="e => editor.chain().focus().setFontFamily(e.target.value).run()"
            class="bg-transparent border-0 text-slate-300 text-[11px] font-semibold
                   focus:ring-0 cursor-pointer px-1 py-1
                   hover:bg-white/10 rounded-lg transition-colors"
            title="Font"
        >
            <option value="" class="text-slate-900 bg-white">Default</option>
            <option value="Inter, sans-serif" class="text-slate-900 bg-white">Inter</option>
            <option value="'Plus Jakarta Sans', sans-serif" class="text-slate-900 bg-white">Jakarta</option>
            <option value="Georgia, serif" class="text-slate-900 bg-white">Serif</option>
            <option value="ui-monospace, monospace" class="text-slate-900 bg-white">Mono</option>
        </select>
    </div>

    <!-- Floating link editor (no prompt!) -->
    <FloatingLinkEditor ref="linkEditorRef" :editor="editor" />
</template>
