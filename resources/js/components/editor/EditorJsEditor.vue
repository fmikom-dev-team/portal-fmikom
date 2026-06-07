<script setup>
import { Loader2 } from "lucide-vue-next";
import { onBeforeUnmount, onMounted, ref, shallowRef } from "vue";
import { createUploadService } from "./services/uploadService";
import { loadAdvancedTools } from "./tools/advancedTools";
import { loadBasicTools } from "./tools/basicTools";
import { loadInlineTools } from "./tools/inlineTools";
import { loadMediaTools } from "./tools/mediaTools";

import "./styles/editor-base.css";
import "./styles/editor-blocks.css";

const props = defineProps({
	modelValue: { type: [Object, String], default: null },
	placeholder: { type: String, default: "Mulai menulis artikel…" },
	uploadUrl: { type: String, default: "/portal-admin/posts/upload-image" },
	uploadFileUrl: { type: String, default: "/portal-admin/posts/upload-file" },
	readOnly: { type: Boolean, default: false },
	minHeight: { type: Number, default: 400 },
});

const emit = defineEmits([
	"update:modelValue",
	"change",
	"ready",
	"update:wordCount",
]);

const holderRef = ref(null);
const isLoading = ref(true);
const editorInstance = shallowRef(null);

// ── Editor Data ─────────────────────────────────────────────────────────
const parseData = () => {
	const v = props.modelValue;
	if (!v) return { blocks: [] };
	if (typeof v === "string") {
		try {
			return JSON.parse(v);
		} catch {
			return { blocks: [] };
		}
	}
	return v;
};

const countWords = (data) =>
	(data?.blocks ?? [])
		.map(
			(b) =>
				b.data?.text || b.data?.code || b.data?.message || b.data?.title || "",
		)
		.join(" ")
		.trim()
		.split(/\s+/)
		.filter(Boolean).length;

// ── Editor Init ──────────────────────────────────────────────────────────
onMounted(async () => {
	if (!holderRef.value) return;

	const { uploadByFile, uploadByUrl, uploadFile } = createUploadService(
		props.uploadUrl,
		props.uploadFileUrl,
	);

	const [
		basicTools,
		inlineTools,
		mediaTools,
		advancedTools,
		EditorJS,
		Undo,
		DragDrop,
	] = await Promise.all([
		loadBasicTools(),
		loadInlineTools(),
		loadMediaTools(uploadByFile, uploadByUrl, uploadFile),
		loadAdvancedTools(),
		import("@editorjs/editorjs").then((m) => m.default || m),
		import("editorjs-undo").then((m) => m.default || m).catch(() => null),
		import("editorjs-drag-drop").then((m) => m.default || m).catch(() => null),
	]);

	const tools = Object.fromEntries(
		Object.entries({
			...basicTools,
			...inlineTools,
			...mediaTools,
			...advancedTools,
		}).filter(([, config]) => typeof config?.class === "function"),
	);

	const inlineToolbar = [
		"bold",
		"italic",
		"link",
		"inlineCode",
		"marker",
		"underline",
		"strikethrough",
		"linkAutocomplete",
	].filter((name) => ["bold", "italic", "link"].includes(name) || tools[name]);

	const editor = new EditorJS({
		holder: holderRef.value,
		placeholder: props.placeholder,
		readOnly: props.readOnly,
		autofocus: false,
		data: parseData(),
		defaultBlock: "paragraph",
		inlineToolbar,
		tools,
		tunes: [],

		onReady: () => {
			if (Undo) {
				try {
					new Undo({ editor, maxLength: 100 });
				} catch (e) {
					console.warn("[EditorJS] Undo:", e.message);
				}
			}
			if (DragDrop) {
				try {
					new DragDrop(editor);
				} catch (e) {
					console.warn("[EditorJS] DragDrop:", e.message);
				}
			}
			isLoading.value = false;
			emit("ready");
		},

		onChange: async (api) => {
			try {
				const output = await api.saver.save();
				emit("update:modelValue", output);
				emit("change", output);
				emit("update:wordCount", countWords(output));
			} catch (e) {
				console.warn("[EditorJS] onChange save error:", e.message);
			}
		},
	});

	await editor.isReady;
	editorInstance.value = editor;
});

onBeforeUnmount(() => {
	try {
		editorInstance.value?.destroy();
		editorInstance.value = null;
	} catch (e) {
		console.warn("[EditorJS] destroy error:", e.message);
	}
});

// Public save
const save = async () => {
	if (!editorInstance.value) return null;
	try {
		return await editorInstance.value.save();
	} catch (e) {
		console.error("[EditorJS] save() failed:", e.message);
		return null;
	}
};

defineExpose({ save });
</script>

<template>
    <div class="editorjs-wrapper" :style="{ minHeight: minHeight + 'px' }">

        <!-- Loading -->
        <div v-if="isLoading" class="editorjs-loading">
            <Loader2 class="editorjs-spinner" :size="28" />
            <span>Memuat editor…</span>
        </div>

        <!-- EditorJS Content Area -->
        <div ref="holderRef" id="editorJs" class="editorjs-holder" />
    </div>
</template>
