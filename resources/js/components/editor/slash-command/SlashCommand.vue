<script setup>
import {
	CheckSquare,
	Code,
	Heading1,
	Heading2,
	Heading3,
	Image as ImageIcon,
	List,
	ListOrdered,
	Minus,
	Quote,
	Table,
	Video,
} from "lucide-vue-next";

const props = defineProps({
	editor: {
		type: Object,
		required: true,
	},
});

const insertTable = () => {
	props.editor
		.chain()
		.focus()
		.insertTable({ rows: 3, cols: 3, withHeaderRow: true })
		.run();
};
const insertImage = () => {
	const input = document.createElement("input");
	input.type = "file";
	input.accept = "image/*";
	input.onchange = (e) => {
		const file = e.target.files[0];
		if (file) {
			// Need to emit event to parent
			// Since this is deeply nested, we can dispatch a custom event
			window.dispatchEvent(
				new CustomEvent("tiptap-upload-image", { detail: { file } }),
			);
		}
	};
	input.click();
};
</script>

<template>
    <div class="hidden" v-if="false"></div>
</template>
