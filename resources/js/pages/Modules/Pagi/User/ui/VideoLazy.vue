<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";

const props = withDefaults(
	defineProps<{
		src: string;
		poster?: string | null;
		autoplay?: boolean;
		loop?: boolean;
		muted?: boolean;
		playsinline?: boolean;
		className?: string;
		controls?: boolean;
	}>(),
	{
		poster: null,
		autoplay: true,
		loop: true,
		muted: true,
		playsinline: true,
		className: "w-full h-full object-cover",
		controls: false,
	},
);

const videoRef = ref<HTMLVideoElement | null>(null);
const isIntersecting = ref(false);
const isLoaded = ref(false);
const isVideoReady = ref(false);

let observer: IntersectionObserver | null = null;

onMounted(() => {
	if (typeof window === "undefined" || !("IntersectionObserver" in window)) {
		isLoaded.value = true;
		isIntersecting.value = true;
		return;
	}

	observer = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					isIntersecting.value = true;
					isLoaded.value = true;
				} else {
					isIntersecting.value = false;
					if (videoRef.value) {
						videoRef.value.pause();
					}
				}
			});
		},
		{
			rootMargin: "200px",
			threshold: 0.01,
		},
	);

	if (videoRef.value) {
		observer.observe(videoRef.value);
	}
});

onUnmounted(() => {
	if (observer) {
		observer.disconnect();
	}
});

// Watch isLoaded to trigger browser video load when source elements are appended
watch(isLoaded, async (newVal) => {
	if (newVal && videoRef.value) {
		await nextTick();
		videoRef.value.load();
		if (props.autoplay && isIntersecting.value) {
			videoRef.value.play().catch(() => {});
		}
	}
});

watch(isIntersecting, (newVal) => {
	if (videoRef.value && isLoaded.value) {
		if (newVal && props.autoplay) {
			videoRef.value.play().catch(() => {});
		} else {
			videoRef.value.pause();
		}
	}
});

const wrapperHeightClass = computed(() => {
	if (!isVideoReady.value) {
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
	<div :class="['relative overflow-hidden w-full', wrapperHeightClass]">
		<!-- Modern Skeleton Shimmer Placeholder -->
		<div
			v-if="!isVideoReady"
			class="absolute inset-0 bg-gradient-to-r from-slate-100 via-slate-200/60 to-slate-100 dark:from-zinc-900 dark:via-zinc-800/60 dark:to-zinc-900 animate-shimmer z-10"
			style="background-size: 200% 100%;"
		></div>

		<video
			ref="videoRef"
			:poster="poster || undefined"
			:loop="loop"
			:muted="muted"
			:playsinline="playsinline"
			:controls="controls"
			@loadeddata="isVideoReady = true"
			:class="[className, 'transition-opacity duration-500', isVideoReady ? 'opacity-100 relative z-5' : 'opacity-0 absolute inset-0']"
			preload="none"
		>
			<source v-if="isLoaded" :src="src" type="video/mp4" />
			<source v-if="isLoaded" :src="src" type="video/webm" />
		</video>
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
