<script setup lang="ts">
import { onMounted, onUnmounted, ref } from "vue";

const props = withDefaults(
	defineProps<{
		placeholderClass?: string;
	}>(),
	{
		placeholderClass: "h-80",
	},
);

const isVisible = ref(false);
const containerRef = ref<HTMLElement | null>(null);

// Shared Global Observer to minimize memory and CPU overhead
let sharedObserver: IntersectionObserver | null = null;
const callbacksMap = new WeakMap<Element, () => void>();

function getSharedObserver() {
	if (!sharedObserver) {
		sharedObserver = new IntersectionObserver(
			(entries) => {
				for (const entry of entries) {
					if (entry.isIntersecting) {
						const cb = callbacksMap.get(entry.target);
						if (cb) {
							cb();
							sharedObserver?.unobserve(entry.target);
							callbacksMap.delete(entry.target);
						}
					}
				}
			},
			{
				rootMargin: "300px", // Preload 300px before entering viewport
			},
		);
	}
	return sharedObserver;
}

onMounted(() => {
	if (containerRef.value) {
		const observer = getSharedObserver();
		callbacksMap.set(containerRef.value, () => {
			isVisible.value = true;
		});
		observer.observe(containerRef.value);
	}
});

onUnmounted(() => {
	if (containerRef.value) {
		sharedObserver?.unobserve(containerRef.value);
		callbacksMap.delete(containerRef.value);
	}
});
</script>

<template>
    <div ref="containerRef" class="w-full">
        <slot v-if="isVisible"></slot>
        <div v-else :class="['w-full bg-slate-50/50 dark:bg-zinc-900/10 rounded-2xl animate-pulse border border-slate-100 dark:border-zinc-800/40', placeholderClass]"></div>
    </div>
</template>
