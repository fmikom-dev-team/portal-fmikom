<script setup>
import tippy from "tippy.js";
import { onBeforeUnmount, onMounted, ref } from "vue";
import "tippy.js/dist/tippy.css";
import "tippy.js/animations/shift-away.css";
import {
	AlignCenter,
	AlignLeft,
	AlignRight,
	Image as ImageIcon,
	Maximize,
	Minimize,
	Trash,
} from "lucide-vue-next";

const props = defineProps({
	editor: Object,
});

const menuRef = ref(null);
let tippyInstance = null;

const updateMenu = () => {
	if (!props.editor || !tippyInstance) return;

	const { view, state } = props.editor;
	const { selection } = state;

	const isImageActive = props.editor.isActive("image");

	if (!isImageActive || selection.empty) {
		tippyInstance.hide();
		return;
	}

	const { ranges } = selection;
	const from = Math.min(...ranges.map((range) => range.$from.pos));
	const to = Math.max(...ranges.map((range) => range.$to.pos));

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
	if (props.editor) {
		props.editor.off("selectionUpdate", updateMenu);
		props.editor.off("update", updateMenu);
	}
	if (tippyInstance) {
		tippyInstance.destroy();
	}
});

const deleteImage = () => {
	props.editor.chain().focus().deleteSelection().run();
};

const replaceImage = () => {
	window.dispatchEvent(new CustomEvent("tiptap-upload-image"));
};
</script>

<template>
    <div ref="menuRef" class="bg-white/95 backdrop-blur-md border border-gray-200/60 shadow-[0_8px_30px_rgb(0,0,0,0.12)] rounded-xl p-1 flex items-center gap-1 text-slate-700">
        <button @click="editor.chain().focus().setTextAlign('left').run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive({ textAlign: 'left' }) }" class="p-2 hover:bg-gray-100 rounded-lg transition-colors" title="Align Left">
            <AlignLeft class="w-4 h-4" />
        </button>
        <button @click="editor.chain().focus().setTextAlign('center').run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive({ textAlign: 'center' }) }" class="p-2 hover:bg-gray-100 rounded-lg transition-colors" title="Align Center">
            <AlignCenter class="w-4 h-4" />
        </button>
        <button @click="editor.chain().focus().setTextAlign('right').run()" :class="{ 'bg-blue-50 text-blue-600': editor.isActive({ textAlign: 'right' }) }" class="p-2 hover:bg-gray-100 rounded-lg transition-colors" title="Align Right">
            <AlignRight class="w-4 h-4" />
        </button>
        <div class="w-px h-5 bg-gray-200 mx-1"></div>
        <button @click="replaceImage" class="p-2 hover:bg-gray-100 rounded-lg transition-colors" title="Replace Image">
            <ImageIcon class="w-4 h-4 text-slate-500" />
        </button>
        <div class="w-px h-5 bg-gray-200 mx-1"></div>
        <button @click="deleteImage" class="p-2 hover:bg-red-50 text-red-500 rounded-lg transition-colors" title="Delete Image">
            <Trash class="w-4 h-4" />
        </button>
    </div>
</template>
