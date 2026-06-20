<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        src: string;
        filename?: string;
        showThumbnails?: boolean;
        initialZoom?: number;
    }>(),
    {
        filename: 'Dokumen.pdf',
        showThumbnails: false,
        initialZoom: 100,
    },
);

const frameStyle = computed(() => {
    const zoom = Math.max(25, Math.min(200, props.initialZoom));
    const scale = zoom / 100;

    return {
        transform: `scale(${scale})`,
        transformOrigin: 'top center',
        width: `${100 / scale}%`,
        maxWidth: `${100 / scale}%`,
    };
});
</script>

<template>
    <div class="flex h-full min-h-0 flex-col bg-slate-900 text-slate-100">
        <div class="flex shrink-0 items-center justify-between border-b border-white/10 px-4 py-2 text-xs text-slate-400">
            <span class="truncate font-medium text-slate-200">{{ filename }}</span>
            <span v-if="showThumbnails" class="ml-3 shrink-0 rounded-full bg-white/5 px-2 py-0.5">
                Thumbnails disabled
            </span>
        </div>

        <div class="min-h-0 flex-1 overflow-auto bg-slate-800 p-1.5 sm:p-4">
            <div class="mx-auto min-h-full overflow-hidden rounded-none shadow-2xl sm:rounded-lg" :style="frameStyle">
                <iframe
                    :src="src"
                    class="h-[calc(100dvh-7rem)] w-full border-0 bg-white transition-opacity sm:rounded-lg"
                    title="PDF Preview"
                />
            </div>
        </div>
    </div>
</template>
