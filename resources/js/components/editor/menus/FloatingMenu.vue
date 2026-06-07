<script setup>
import tippy from "tippy.js";
import { onBeforeUnmount, onMounted, ref, watch } from "vue";
import "tippy.js/dist/tippy.css";
import "tippy.js/animations/shift-away.css";
import { Plus } from "lucide-vue-next";

const props = defineProps({
	editor: Object,
});

const menuRef = ref(null);
let tippyInstance = null;
const isVisible = ref(false);

const updateMenu = () => {
	if (!props.editor || !tippyInstance) return;

	const { view, state } = props.editor;
	const { selection } = state;
	const { $anchor, empty } = selection;

	const isRootDepth = $anchor.depth === 1;
	const isEmptyTextBlock =
		$anchor.parent.isTextblock && !$anchor.parent.textContent && empty;

	if (!isRootDepth || !isEmptyTextBlock) {
		tippyInstance.hide();
		isVisible.value = false;
		return;
	}

	// Find the DOM element of the current block
	const node = view.domAtPos($anchor.pos).node;
	if (!node || node.nodeType !== 1) {
		tippyInstance.hide();
		isVisible.value = false;
		return;
	}

	// Attach floating menu to the left of the block
	tippyInstance.setProps({
		getReferenceClientRect: () => {
			const rect = node.getBoundingClientRect();
			return {
				width: 0,
				height: rect.height,
				top: rect.top,
				bottom: rect.bottom,
				left: rect.left,
				right: rect.left,
			};
		},
	});

	tippyInstance.show();
	isVisible.value = true;
};

onMounted(() => {
	tippyInstance = tippy(document.body, {
		content: menuRef.value,
		interactive: true,
		trigger: "manual",
		placement: "left",
		animation: "shift-away",
		appendTo: () => document.body,
		offset: [0, 20],
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

const openSlashCommand = () => {
	// Insert slash to trigger suggestion
	props.editor.chain().focus().insertContent("/").run();
};
</script>

<template>
    <div ref="menuRef" class="flex items-center">
        <button 
            @click="openSlashCommand" 
            class="w-8 h-8 rounded-full bg-white border border-gray-100 shadow-sm flex items-center justify-center text-slate-400 hover:text-blue-500 hover:bg-blue-50 hover:border-blue-100 transition-all duration-200"
            title="Klik untuk tambah blok (/)"
        >
            <Plus class="w-5 h-5" />
        </button>
    </div>
</template>
