<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { ExternalLink, FileText, LoaderCircle, RotateCcw, X, ZoomIn, ZoomOut } from 'lucide-vue-next';
import PdfViewer from '@/components/PdfViewer.vue';

type PreviewMode = 'html' | 'pdf';

const props = withDefaults(
    defineProps<{
        open: boolean;
        mode: PreviewMode;
        title: string;
        url: string | null;
        subtitle?: string | null;
        pdfFilename?: string;
        showThumbnails?: boolean;
        initialZoom?: number;
        htmlInitialZoom?: number;
        showHtmlZoomControls?: boolean;
        showOpenInNewTab?: boolean;
        htmlMinZoom?: number;
        htmlMaxZoom?: number;
        htmlZoomStep?: number;
    }>(),
    {
        subtitle: null,
        pdfFilename: 'Dokumen.pdf',
        showThumbnails: false,
        initialZoom: 100,
        htmlInitialZoom: 100,
        showHtmlZoomControls: false,
        showOpenInNewTab: false,
        htmlMinZoom: 50,
        htmlMaxZoom: 200,
        htmlZoomStep: 10,
    },
);

const emit = defineEmits<{
    close: [];
    'open-new-tab': [url: string];
}>();

const iframeLoad = ref(true);
const iframeError = ref(false);
const iframeZoom = ref(props.initialZoom);
const iframeVersion = ref(0);

const isHtmlMode = computed(() => props.mode === 'html');
const iframeFilename = computed(() => props.pdfFilename || props.title);
const iframeScale = computed(() => iframeZoom.value / 100);

function resetIframeState() {
    iframeLoad.value = true;
    iframeError.value = false;
    iframeVersion.value += 1;
}

function close() {
    emit('close');
}

function openNewTab() {
    if (!props.url) return;
    emit('open-new-tab', props.url);
}

function zoomIn() {
    iframeZoom.value = Math.min(
        props.htmlMaxZoom,
        iframeZoom.value + props.htmlZoomStep,
    );
}

function zoomOut() {
    iframeZoom.value = Math.max(
        props.htmlMinZoom,
        iframeZoom.value - props.htmlZoomStep,
    );
}

function resetZoom() {
    iframeZoom.value = props.initialZoom;
}

const htmlFrameStyle = computed(() => {
    if (!isHtmlMode.value) return {};

    const scale = iframeScale.value;

    return {
        transform: `scale(${scale})`,
        transformOrigin: 'top center',
        width: `${100 / scale}%`,
        maxWidth: `${100 / scale}%`,
    };
});

watch(
    () => [props.open, props.url, props.mode],
    () => {
        if (!props.open) return;
        iframeZoom.value = isHtmlMode.value
            ? props.initialZoom
            : props.initialZoom;
        resetIframeState();
    },
    { immediate: true },
);

function onKeydown(event: KeyboardEvent) {
    if (!props.open) return;
    if (event.key === 'Escape') {
        close();
    }
}

onMounted(() => {
    window.addEventListener('keydown', onKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <Transition name="fade">
        <div
            v-if="open"
            class="fixed inset-0 z-50 flex flex-col bg-black/70 backdrop-blur-sm"
            @click.self="close"
        >
            <div
                class="flex shrink-0 items-center justify-between gap-3 border-b border-white/10 bg-slate-950 px-4 py-3 text-white sm:px-5"
            >
                <div class="flex min-w-0 items-center gap-3">
                    <div
                        class="grid size-8 shrink-0 place-items-center rounded-lg bg-blue-600"
                    >
                        <FileText class="size-4 text-white" />
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-white">
                            {{ title }}
                        </p>
                        <p
                            v-if="subtitle"
                            class="truncate text-xs text-slate-400"
                        >
                            {{ subtitle }}
                        </p>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-1">
                    <template v-if="isHtmlMode && showHtmlZoomControls">
                        <button
                            type="button"
                            class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-800 hover:text-white disabled:cursor-not-allowed disabled:opacity-40"
                            :disabled="iframeZoom <= htmlMinZoom"
                            @click="zoomOut"
                        >
                            <ZoomOut class="size-4" />
                        </button>
                        <span
                            class="min-w-12 px-1 text-center text-xs text-slate-300"
                        >
                            {{ iframeZoom }}%
                        </span>
                        <button
                            type="button"
                            class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-800 hover:text-white disabled:cursor-not-allowed disabled:opacity-40"
                            :disabled="iframeZoom >= htmlMaxZoom"
                            @click="zoomIn"
                        >
                            <ZoomIn class="size-4" />
                        </button>
                        <button
                            type="button"
                            class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-800 hover:text-white"
                            @click="resetZoom"
                        >
                            <RotateCcw class="size-4" />
                        </button>
                    </template>

                    <button
                        v-if="showOpenInNewTab"
                        type="button"
                        class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-800 hover:text-white"
                        @click="openNewTab"
                    >
                        <ExternalLink class="size-4" />
                    </button>

                    <button
                        type="button"
                        class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-red-600 hover:text-white"
                        @click="close"
                    >
                        <X class="size-4" />
                    </button>
                </div>
            </div>

            <slot name="banner" />

            <div v-if="mode === 'html'" class="relative flex-1 overflow-hidden bg-slate-800">
                <div
                    v-if="iframeLoad"
                    class="absolute inset-0 z-10 flex items-center justify-center bg-slate-800"
                >
                    <div class="flex flex-col items-center gap-3">
                        <LoaderCircle class="size-10 animate-spin text-slate-300" />
                        <p class="text-sm text-slate-400">Memuat dokumen...</p>
                    </div>
                </div>

                <div
                    v-else-if="iframeError"
                    class="absolute inset-0 z-10 flex items-center justify-center bg-slate-800 p-6"
                >
                    <div class="max-w-sm rounded-2xl border border-slate-700 bg-slate-900 px-5 py-4 text-center text-sm text-slate-300 shadow-xl">
                        <p class="font-semibold text-white">Gagal memuat preview</p>
                        <p class="mt-1 text-slate-400">
                            Coba muat ulang atau buka dokumen di tab baru.
                        </p>
                        <div class="mt-4 flex items-center justify-center gap-2">
                            <button
                                type="button"
                                class="fast-btn fast-btn-outline rounded-xl border-slate-700 px-4 py-2 text-xs font-semibold text-slate-200"
                                @click="resetIframeState"
                            >
                                Muat ulang
                            </button>
                            <button
                                v-if="showOpenInNewTab"
                                type="button"
                                class="fast-btn fast-btn-primary rounded-xl px-4 py-2 text-xs font-semibold"
                                @click="openNewTab"
                            >
                                Buka tab baru
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex h-full items-start justify-center overflow-auto p-1.5 sm:p-4">
                    <div
                        class="min-h-full overflow-hidden rounded-none shadow-2xl sm:rounded-lg"
                        :style="htmlFrameStyle"
                    >
                        <iframe
                            v-if="url"
                            :key="iframeVersion"
                            :src="url"
                            class="h-[calc(100dvh-3.5rem)] w-full border-0 bg-white transition-opacity sm:rounded-lg"
                            :class="{ 'opacity-0': iframeLoad }"
                            @load="iframeLoad = false"
                            @error="
                                iframeLoad = false;
                                iframeError = true;
                            "
                        />
                    </div>
                </div>
            </div>

            <div v-else class="flex-1 min-h-0 bg-slate-900">
                <PdfViewer
                    v-if="url"
                    :src="url"
                    :filename="iframeFilename"
                    :show-thumbnails="showThumbnails"
                    :initial-zoom="initialZoom"
                    class="h-full w-full"
                />
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
