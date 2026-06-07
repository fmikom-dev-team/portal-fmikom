<script setup>
import tippy from "tippy.js";
import { onBeforeUnmount, onMounted, ref, watch } from "vue";
import "tippy.js/dist/tippy.css";
import "tippy.js/animations/shift-away.css";
import {
	ArrowDownToLine,
	ArrowRightToLine,
	Columns,
	Minus,
	Plus,
	Rows,
	SplitSquareHorizontal,
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

	// Ensure we are inside a table
	const isTableActive = props.editor.isActive("table");

	if (!isTableActive) {
		tippyInstance.hide();
		return;
	}

	// Get the table node position
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
		placement: "bottom",
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
</script>

<template>
    <div ref="menuRef" class="bg-white/95 backdrop-blur-md border border-gray-200/60 shadow-[0_8px_30px_rgb(0,0,0,0.12)] rounded-xl p-1.5 flex items-center gap-1 text-slate-700">
        <button @click="editor.chain().focus().addRowAfter().run()" class="p-2 hover:bg-gray-100 rounded-lg transition-colors flex items-center gap-1.5 text-xs font-bold" title="Add Row Below">
            <ArrowDownToLine class="w-4 h-4 text-slate-500" /> Row
        </button>
        <button @click="editor.chain().focus().addColumnAfter().run()" class="p-2 hover:bg-gray-100 rounded-lg transition-colors flex items-center gap-1.5 text-xs font-bold" title="Add Column Right">
            <ArrowRightToLine class="w-4 h-4 text-slate-500" /> Col
        </button>
        <div class="w-px h-5 bg-gray-200 mx-1"></div>
        <button @click="editor.chain().focus().mergeCells().run()" :disabled="!editor.can().mergeCells()" class="p-2 hover:bg-gray-100 disabled:opacity-50 disabled:hover:bg-transparent rounded-lg transition-colors" title="Merge Cells">
            <SplitSquareHorizontal class="w-4 h-4 text-slate-500" />
        </button>
        <div class="w-px h-5 bg-gray-200 mx-1"></div>
        <button @click="editor.chain().focus().deleteRow().run()" class="p-2 hover:bg-red-50 text-red-500 rounded-lg transition-colors" title="Delete Row">
            <Rows class="w-4 h-4" />
        </button>
        <button @click="editor.chain().focus().deleteColumn().run()" class="p-2 hover:bg-red-50 text-red-500 rounded-lg transition-colors" title="Delete Column">
            <Columns class="w-4 h-4" />
        </button>
        <button @click="editor.chain().focus().deleteTable().run()" class="p-2 hover:bg-red-50 text-red-600 rounded-lg transition-colors" title="Delete Table">
            <Trash class="w-4 h-4" />
        </button>
    </div>
</template>
