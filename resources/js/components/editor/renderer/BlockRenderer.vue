<script setup>
/**
 * BlockRenderer.vue — Vue 3 component-based renderer for Editor.js JSON output.
 * Each block type maps to its own sub-component for clean, styled rendering.
 */
import { computed } from "vue";
import AlertBlock from "./blocks/AlertBlock.vue";
import AttachmentBlock from "./blocks/AttachmentBlock.vue";
import ChecklistBlock from "./blocks/ChecklistBlock.vue";
import CodeBlock from "./blocks/CodeBlock.vue";
import DelimiterBlock from "./blocks/DelimiterBlock.vue";
import EmbedBlock from "./blocks/EmbedBlock.vue";
import HeaderBlock from "./blocks/HeaderBlock.vue";
import ImageBlock from "./blocks/ImageBlock.vue";
import ListBlock from "./blocks/ListBlock.vue";
// ── Block sub-components (inline for single-file architecture) ──
import ParagraphBlock from "./blocks/ParagraphBlock.vue";
import QuoteBlock from "./blocks/QuoteBlock.vue";
import RawBlock from "./blocks/RawBlock.vue";
import TableBlock from "./blocks/TableBlock.vue";
import ToggleBlock from "./blocks/ToggleBlock.vue";
import WarningBlock from "./blocks/WarningBlock.vue";

const props = defineProps({
	/** Editor.js output object or JSON string */
	data: { type: [Object, String], default: null },
	/** Additional wrapper CSS classes */
	class: { type: String, default: "" },
});

const BLOCK_MAP = {
	paragraph: ParagraphBlock,
	header: HeaderBlock,
	quote: QuoteBlock,
	list: ListBlock,
	checklist: ChecklistBlock,
	code: CodeBlock,
	delimiter: DelimiterBlock,
	image: ImageBlock,
	table: TableBlock,
	embed: EmbedBlock,
	attaches: AttachmentBlock,
	warning: WarningBlock,
	alert: AlertBlock,
	toggle: ToggleBlock,
	raw: RawBlock,
};

const parsedData = computed(() => {
	if (!props.data) return null;
	if (typeof props.data === "string") {
		try {
			return JSON.parse(props.data);
		} catch {
			return null;
		}
	}
	return props.data;
});

const blocks = computed(() => parsedData.value?.blocks ?? []);
</script>

<template>
    <div :class="['editorjs-renderer', props.class]">
        <template v-for="(block, i) in blocks" :key="block.id ?? i">
            <component
                :is="BLOCK_MAP[block.type]"
                v-if="BLOCK_MAP[block.type]"
                :data="block.data"
            />
            <!-- Fallback for unknown block types -->
            <div v-else class="my-3 px-3 py-2 bg-gray-50 border border-gray-200 rounded text-xs text-gray-400 font-mono">
                Unknown block: {{ block.type }}
            </div>
        </template>

        <div v-if="blocks.length === 0" class="text-slate-400 italic text-sm py-4">
            Konten tidak tersedia.
        </div>
    </div>
</template>

<style>

.editorjs-renderer {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    -webkit-font-smoothing: antialiased;
    color: #374151;
    line-height: 1.75;
    max-width: 100%;
}
.editorjs-renderer > * { margin-bottom: 0; }
.editorjs-renderer > * + * { margin-top: 0.25rem; }
</style>
