<script setup lang="ts">
import { Link } from "@tiptap/extension-link";
import { Placeholder } from "@tiptap/extension-placeholder";
import { TextAlign } from "@tiptap/extension-text-align";
import { TextStyle } from "@tiptap/extension-text-style";
import { Underline } from "@tiptap/extension-underline";
import { StarterKit } from "@tiptap/starter-kit";
import { EditorContent, useEditor } from "@tiptap/vue-3";
import tippy from "tippy.js";
import "tippy.js/dist/tippy.css";
import "tippy.js/animations/shift-away.css";
import {
	AlignCenter,
	AlignLeft,
	AlignRight,
	Bold,
	Heading1,
	Heading2,
	Italic,
	Link2,
	Pilcrow,
	Underline as UnderlineIcon,
} from "lucide-vue-next";
import { onBeforeUnmount, onMounted, ref, watch } from "vue";

const props = defineProps<{
	modelValue: string;
	canvasTextColor?: string;
	isPreviewMode?: boolean;
	isFocused?: boolean;
}>();

const emit = defineEmits<{
	(e: "update:modelValue", value: string): void;
	(e: "focus"): void;
	(e: "blur"): void;
}>();

const menuRef = ref<HTMLElement | null>(null);
let tippyInstance: any = null;

const updateMenu = () => {
	if (
		!editor.value ||
		!tippyInstance ||
		props.isPreviewMode ||
		props.isFocused === false
	) {
		tippyInstance?.hide();
		return;
	}

	const { view, state } = editor.value;
	const { selection } = state;
	const hasText = !selection.empty;

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

const editor = useEditor({
	content: props.modelValue,
	editable: !props.isPreviewMode,
	extensions: [
		StarterKit.configure({
			heading: {
				levels: [1, 2],
			},
		}),
		Underline,
		Link.configure({
			openOnClick: false,
			HTMLAttributes: {
				rel: "noopener noreferrer",
				class:
					"underline text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 cursor-pointer transition-colors",
			},
		}),
		TextAlign.configure({
			types: ["heading", "paragraph"],
		}),
		TextStyle,
		Placeholder.configure({
			placeholder: "Mulai menulis cerita Anda di sini...",
		}),
	],
	onUpdate: ({ editor }) => {
		emit("update:modelValue", editor.getHTML());
	},
	onFocus: () => {
		emit("focus");
	},
	onBlur: () => {
		emit("blur");
		tippyInstance?.hide();
	},
	editorProps: {
		attributes: {
			class:
				"focus:outline-none text-base leading-relaxed tracking-wide min-h-[40px] select-text w-full cursor-text",
		},
	},
});

watch(
	() => props.modelValue,
	(newVal) => {
		if (!editor.value) return;
		const isSame = editor.value.getHTML() === newVal;
		if (!isSame) {
			editor.value.commands.setContent(newVal, false as any);
		}
	},
);

watch(
	() => props.isPreviewMode,
	(newVal) => {
		if (!editor.value) return;
		editor.value.setEditable(!newVal);
		if (newVal) {
			tippyInstance?.hide();
		}
	},
);

watch(
	() => props.isFocused,
	(newVal) => {
		if (newVal === false) {
			tippyInstance?.hide();
		} else {
			updateMenu();
		}
	},
);

const initTippy = (editorInstance: any) => {
	if (tippyInstance) return;
	tippyInstance = tippy(document.body, {
		content: menuRef.value!,
		interactive: true,
		trigger: "manual",
		placement: "top",
		animation: "shift-away",
		appendTo: () => document.body,
		zIndex: 9999,
	});
	editorInstance.on("selectionUpdate", updateMenu);
	editorInstance.on("update", updateMenu);
};

onMounted(() => {
	if (editor.value && menuRef.value) {
		initTippy(editor.value);
	} else {
		const unwatch = watch(
			() => editor.value,
			(newEditor) => {
				if (newEditor && menuRef.value) {
					initTippy(newEditor);
					unwatch();
				}
			},
		);
	}
});

onBeforeUnmount(() => {
	if (editor.value) {
		editor.value.off("selectionUpdate", updateMenu);
		editor.value.off("update", updateMenu);
	}
	tippyInstance?.destroy();
	editor.value?.destroy();
});

const toggleLink = () => {
	if (!editor.value) return;

	if (editor.value.isActive("link")) {
		editor.value.chain().focus().unsetLink().run();
		return;
	}

	const url = globalThis.prompt("Masukkan URL tautan:");
	if (url) {
		let formattedUrl = url.trim();
		if (!/^https?:\/\//i.test(formattedUrl)) {
			formattedUrl = `https://${formattedUrl}`;
		}
		editor.value.chain().focus().setLink({ href: formattedUrl }).run();
	}
};
</script>

<template>
	<div class="w-full relative">
		<!-- Dynamic Floating Bubble Menu (Hidden in DOM, mounted by Tippy) -->
		<div
			v-if="editor"
			v-show="props.isFocused"
			ref="menuRef"
			class="flex items-center gap-0.5 bg-slate-900 dark:bg-slate-950 text-white rounded-xl shadow-2xl border border-slate-800 dark:border-slate-800 p-1 backdrop-blur-md select-none overflow-x-auto max-w-[90vw]"
		>
			<!-- Text Styles -->
			<button
				type="button"
				@click="editor.chain().focus().toggleBold().run()"
				:class="[
					'p-1.5 rounded-lg transition-colors flex items-center justify-center cursor-pointer',
					editor.isActive('bold') ? 'bg-white/20 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white',
				]"
				title="Bold"
			>
				<Bold class="w-3.5 h-3.5" />
			</button>
			<button
				type="button"
				@click="editor.chain().focus().toggleItalic().run()"
				:class="[
					'p-1.5 rounded-lg transition-colors flex items-center justify-center cursor-pointer',
					editor.isActive('italic') ? 'bg-white/20 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white',
				]"
				title="Italic"
			>
				<Italic class="w-3.5 h-3.5" />
			</button>
			<button
				type="button"
				@click="editor.chain().focus().toggleUnderline().run()"
				:class="[
					'p-1.5 rounded-lg transition-colors flex items-center justify-center cursor-pointer',
					editor.isActive('underline') ? 'bg-white/20 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white',
				]"
				title="Underline"
			>
				<UnderlineIcon class="w-3.5 h-3.5" />
			</button>
			<button
				type="button"
				@click="toggleLink"
				:class="[
					'p-1.5 rounded-lg transition-colors flex items-center justify-center cursor-pointer',
					editor.isActive('link') ? 'bg-white/20 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white',
				]"
				title="Link"
			>
				<Link2 class="w-3.5 h-3.5" />
			</button>

			<div class="w-px h-4 bg-slate-800 mx-1 shrink-0"></div>

			<!-- Typography Formats -->
			<button
				type="button"
				@click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
				:class="[
					'p-1.5 rounded-lg transition-colors flex items-center justify-center cursor-pointer',
					editor.isActive('heading', { level: 1 }) ? 'bg-white/20 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white',
				]"
				title="Heading 1"
			>
				<Heading1 class="w-3.5 h-3.5" />
			</button>
			<button
				type="button"
				@click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
				:class="[
					'p-1.5 rounded-lg transition-colors flex items-center justify-center cursor-pointer',
					editor.isActive('heading', { level: 2 }) ? 'bg-white/20 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white',
				]"
				title="Heading 2"
			>
				<Heading2 class="w-3.5 h-3.5" />
			</button>
			<button
				type="button"
				@click="editor.chain().focus().setParagraph().run()"
				:class="[
					'p-1.5 rounded-lg transition-colors flex items-center justify-center cursor-pointer',
					editor.isActive('paragraph') ? 'bg-white/20 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white',
				]"
				title="Paragraph"
			>
				<Pilcrow class="w-3.5 h-3.5" />
			</button>

			<div class="w-px h-4 bg-slate-800 mx-1 shrink-0"></div>

			<!-- Alignment Controls -->
			<button
				type="button"
				@click="editor.chain().focus().setTextAlign('left').run()"
				:class="[
					'p-1.5 rounded-lg transition-colors flex items-center justify-center cursor-pointer',
					editor.isActive({ textAlign: 'left' }) ? 'bg-white/20 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white',
				]"
				title="Align Left"
			>
				<AlignLeft class="w-3.5 h-3.5" />
			</button>
			<button
				type="button"
				@click="editor.chain().focus().setTextAlign('center').run()"
				:class="[
					'p-1.5 rounded-lg transition-colors flex items-center justify-center cursor-pointer',
					editor.isActive({ textAlign: 'center' }) ? 'bg-white/20 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white',
				]"
				title="Align Center"
			>
				<AlignCenter class="w-3.5 h-3.5" />
			</button>
			<button
				type="button"
				@click="editor.chain().focus().setTextAlign('right').run()"
				:class="[
					'p-1.5 rounded-lg transition-colors flex items-center justify-center cursor-pointer',
					editor.isActive({ textAlign: 'right' }) ? 'bg-white/20 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white',
				]"
				title="Align Right"
			>
				<AlignRight class="w-3.5 h-3.5" />
			</button>
		</div>

		<!-- Tiptap Editor Content Area -->
		<EditorContent
			v-if="editor"
			:editor="editor"
			:style="{ color: props.canvasTextColor }"
			class="pagi-tiptap-editor-content animate-fade-in"
		/>
	</div>
</template>

<style>
/* ── Scoped/Component Tiptap Styles ── */
.pagi-tiptap-editor-content .ProseMirror {
	outline: none;
	width: 100%;
}

.pagi-tiptap-editor-content .ProseMirror p.is-editor-empty:first-child::before {
	content: attr(data-placeholder);
	float: left;
	height: 0;
	pointer-events: none;
	color: #94a3b8; /* tailwind slate-400 */
	font-weight: 400;
}

.pagi-tiptap-editor-content .ProseMirror > * + * {
	margin-top: 0.75em;
}

.pagi-tiptap-editor-content .ProseMirror h1 {
	font-size: 2.25rem;
	font-weight: 800;
	line-height: 1.2;
	margin: 1rem 0;
}

.pagi-tiptap-editor-content .ProseMirror h2 {
	font-size: 1.5rem;
	font-weight: 700;
	line-height: 1.3;
	margin: 0.875rem 0;
}

.pagi-tiptap-editor-content .ProseMirror blockquote {
	border-left: 4px solid #cbd5e1;
	padding-left: 1rem;
	color: #64748b;
	font-style: italic;
	margin: 1rem 0;
}

.pagi-tiptap-editor-content .ProseMirror ul {
	list-style-type: disc;
	padding-left: 1.5rem;
	margin: 0.75rem 0;
}

.pagi-tiptap-editor-content .ProseMirror ol {
	list-style-type: decimal;
	padding-left: 1.5rem;
	margin: 0.75rem 0;
}
</style>
