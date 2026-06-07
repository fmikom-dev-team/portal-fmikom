<script setup lang="ts">
import { computed, ref } from "vue";

const props = withDefaults(
	defineProps<{
		src: string;
		alt: string;
		sizes?: string;
		fetchpriority?: "high" | "low" | "auto";
		loading?: "lazy" | "eager";
		className?: string;
	}>(),
	{
		sizes: "(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw",
		fetchpriority: "auto",
		loading: "lazy",
		className: "w-full h-full object-cover",
	},
);

const emit = defineEmits<(e: "load", event: Event) => void>();

const isLoaded = ref(false);

const handleLoad = (event: Event) => {
	isLoaded.value = true;
	emit("load", event);
};

// Generate high performance srcset for popular dynamic media CDNs
const srcset = computed(() => {
	if (!props.src) return undefined;

	// Case 1: Unsplash URL
	if (props.src.includes("images.unsplash.com")) {
		const baseUrl = props.src.split("?")[0];
		return `
			${baseUrl}?auto=format&fit=crop&w=480&q=80 480w,
			${baseUrl}?auto=format&fit=crop&w=768&q=80 768w,
			${baseUrl}?auto=format&fit=crop&w=1200&q=80 1200w
		`.trim();
	}

	// Case 2: Picsum Photos URL (e.g., https://picsum.photos/seed/a1/300/200)
	if (props.src.includes("picsum.photos")) {
		const baseParts = props.src.split("/");
		const seedIndex = baseParts.indexOf("seed");
		if (seedIndex !== -1 && baseParts.length >= seedIndex + 3) {
			const seedId = baseParts[seedIndex + 1];
			return `
				https://picsum.photos/seed/${seedId}/480/320 480w,
				https://picsum.photos/seed/${seedId}/768/512 768w,
				https://picsum.photos/seed/${seedId}/1200/800 1200w
			`.trim();
		}
	}

	return undefined;
});

const wrapperHeightClass = computed(() => {
	if (!isLoaded.value) {
		return "min-h-[220px] bg-slate-50 dark:bg-slate-900 rounded-xl";
	}
	if (
		props.className?.includes("h-full") ||
		props.className?.includes("max-h-full") ||
		props.className?.includes("absolute inset-0")
	) {
		return "h-full";
	}
	return "h-auto";
});
</script>

<template>
	<div :class="['relative overflow-hidden w-full flex items-center justify-center', wrapperHeightClass]">
		<!-- Modern Skeleton Shimmer Placeholder -->
		<div
			v-if="!isLoaded"
			class="absolute inset-0 bg-gradient-to-r from-slate-100 via-slate-200/60 to-slate-100 dark:from-zinc-900 dark:via-zinc-800/60 dark:to-zinc-900 animate-shimmer"
			style="background-size: 200% 100%;"
		></div>
		
		<img
			:src="src"
			:alt="alt"
			:srcset="srcset"
			:sizes="srcset ? sizes : undefined"
			:loading="fetchpriority === 'high' ? 'eager' : loading"
			:fetchpriority="fetchpriority"
			decoding="async"
			@load="handleLoad"
			:class="[className, 'transition-opacity duration-500', isLoaded ? 'opacity-100' : 'opacity-0 absolute inset-0']"
		/>
	</div>
</template>

<style scoped>
@keyframes shimmer {
	0% {
		background-position: -200% 0;
	}
	100% {
		background-position: 200% 0;
	}
}
.animate-shimmer {
	animation: shimmer 1.5s infinite linear;
}
</style>
