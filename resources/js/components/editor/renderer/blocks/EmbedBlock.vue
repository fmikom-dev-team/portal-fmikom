<script setup>
import { computed } from "vue";

const props = defineProps({ data: Object });

const aspectRatio = computed(() => {
	const h = props.data?.height || 315;
	const w = props.data?.width || 560;
	return `${((h / w) * 100).toFixed(2)}%`;
});
</script>

<template>
    <div v-if="data.embed" class="my-8">
        <div class="relative w-full overflow-hidden rounded-2xl shadow-sm bg-gray-900"
             :style="{ paddingBottom: aspectRatio }">
            <iframe
                :src="data.embed"
                :title="data.caption || 'Embedded content'"
                class="absolute inset-0 w-full h-full border-0"
                allowfullscreen
                loading="lazy"
            />
        </div>
        <p v-if="data.caption" class="text-center text-sm text-gray-400 mt-3 italic">
            {{ data.caption }}
        </p>
    </div>
    <div v-else-if="data.source" class="my-8">
        <a :href="data.source" target="_blank" rel="noopener noreferrer"
           class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 text-sm font-semibold">
            🔗 Buka sumber embed
        </a>
    </div>
</template>
