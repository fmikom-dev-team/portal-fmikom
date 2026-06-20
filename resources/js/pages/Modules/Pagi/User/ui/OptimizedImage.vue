<script setup lang="ts">
import { computed, onMounted, ref } from "vue";

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

// Determine if the component is being used as a small avatar or icon
const isSmall = computed(() => {
	const cn = props.className || "";
	return (
		cn.includes("rounded-full") || /\b(h|w)-(2|3|4|5|6|8|10|12|16)\b/.test(cn)
	);
});

const isLoaded = ref(isSmall.value);
const imgRef = ref<HTMLImageElement | null>(null);

const handleLoad = () => {
	isLoaded.value = true;
};

onMounted(() => {
	if (imgRef.value?.complete) {
		isLoaded.value = true;
	}
});

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

// Determine constant layout constraints based on className to prevent Vue-driven reflows
const isHFull = computed(() => {
	const cn = props.className || "";
	return (
		cn.includes("h-full") ||
		cn.includes("max-h-full") ||
		cn.includes("absolute inset-0")
	);
});

const wrapperClass = computed(() => {
	if (isSmall.value) {
		return "inline-flex h-fit w-fit";
	}
	return isHFull.value
		? "h-full w-full"
		: "h-auto w-full min-h-[180px] bg-slate-100 dark:bg-zinc-800/80 rounded-xl";
});
</script>

<template>
	<div :class="['relative overflow-hidden flex items-center justify-center', wrapperClass]">
		<!-- Skeleton Shimmer Placeholder (only for non-small images) -->
		<div
			v-if="!isLoaded && !isSmall"
			class="absolute inset-0 bg-gradient-to-r from-slate-100 via-slate-200/60 to-slate-100 dark:from-zinc-900 dark:via-zinc-800/60 dark:to-zinc-900 animate-shimmer z-0"
			style="background-size: 200% 100%;"
		></div>
		
		<img
			ref="imgRef"
			:src="src"
			:alt="alt"
			:srcset="srcset"
			:sizes="srcset ? sizes : undefined"
			:loading="fetchpriority === 'high' ? 'eager' : loading"
			:fetchpriority="fetchpriority"
			decoding="async"
			@load="handleLoad"
			:class="[
				className,
				'transition-opacity duration-300 z-10',
				isLoaded ? 'opacity-100' : 'opacity-0',
				isHFull ? 'absolute inset-0' : '',
			]"
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
