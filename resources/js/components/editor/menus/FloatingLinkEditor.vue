<script setup>
import {
	Check,
	ExternalLink,
	Link as LinkIcon,
	Trash2,
	X,
} from "lucide-vue-next";
import tippy from "tippy.js";
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from "vue";

const props = defineProps({ editor: Object });

const panelRef = ref(null);
const inputRef = ref(null);
const urlValue = ref("");
const isEditing = ref(false);
let tippyInstance = null;

/* ── helpers ── */
const currentHref = () => props.editor?.getAttributes("link").href ?? "";

const show = () => {
	if (!props.editor || !tippyInstance) return;
	urlValue.value = currentHref();
	isEditing.value = true;

	const { view, state } = props.editor;
	const { from, to } = state.selection;
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
	nextTick(() => inputRef.value?.focus());
};

const hide = () => {
	isEditing.value = false;
	tippyInstance?.hide();
};

const apply = () => {
	const url = urlValue.value.trim();
	if (!url) {
		props.editor.chain().focus().extendMarkRange("link").unsetLink().run();
	} else {
		const href = url.startsWith("http") ? url : `https://${url}`;
		props.editor
			.chain()
			.focus()
			.extendMarkRange("link")
			.setLink({ href })
			.run();
	}
	hide();
};

const removeLink = () => {
	props.editor.chain().focus().extendMarkRange("link").unsetLink().run();
	hide();
};

const openUrl = () => {
	const url = currentHref();
	if (url) window.open(url, "_blank", "noopener");
};

/* ── expose for BubbleMenu to call ── */
defineExpose({ show, hide });

/* ── tippy setup ── */
onMounted(() => {
	tippyInstance = tippy(document.body, {
		content: panelRef.value,
		interactive: true,
		trigger: "manual",
		placement: "bottom-start",
		animation: "shift-away",
		appendTo: () => document.body,
		onHide: () => {
			isEditing.value = false;
		},
	});
});

onBeforeUnmount(() => tippyInstance?.destroy());
</script>

<template>
    <div ref="panelRef"
         class="bg-white/95 backdrop-blur-xl border border-gray-200/60 shadow-[0_8px_30px_rgba(0,0,0,0.12)] rounded-2xl p-3 w-[360px]">

        <!-- URL row -->
        <div class="flex items-center gap-2 bg-gray-50/80 border border-gray-200/60 rounded-xl px-3 py-2">
            <LinkIcon class="w-4 h-4 text-slate-400 shrink-0" />
            <input
                ref="inputRef"
                v-model="urlValue"
                type="url"
                placeholder="Tempel atau ketik URL..."
                class="flex-1 text-sm bg-transparent outline-none text-slate-700 placeholder:text-slate-400 min-w-0"
                @keydown.enter.prevent="apply"
                @keydown.escape.prevent="hide"
            />
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between mt-2.5">
            <!-- Open in new tab (if existing link) -->
            <button
                v-if="currentHref()"
                @click="openUrl"
                class="flex items-center gap-1.5 text-xs text-slate-500 hover:text-blue-600 px-2 py-1 rounded-lg hover:bg-blue-50 transition-colors"
            >
                <ExternalLink class="w-3.5 h-3.5" />
                Buka
            </button>
            <span v-else class="flex-1" />

            <div class="flex items-center gap-1">
                <!-- Remove link -->
                <button
                    v-if="currentHref()"
                    @click="removeLink"
                    class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                    title="Hapus link"
                >
                    <Trash2 class="w-4 h-4" />
                </button>

                <!-- Cancel -->
                <button @click="hide" class="p-1.5 text-slate-400 hover:bg-gray-100 rounded-lg transition-colors" title="Batal">
                    <X class="w-4 h-4" />
                </button>

                <!-- Apply -->
                <button
                    @click="apply"
                    class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-colors"
                >
                    <Check class="w-3.5 h-3.5" />
                    Terapkan
                </button>
            </div>
        </div>
    </div>
</template>
