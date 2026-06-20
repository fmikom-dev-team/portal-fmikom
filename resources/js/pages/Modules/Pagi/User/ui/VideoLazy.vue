<script setup lang="ts">
import { Play } from "lucide-vue-next";
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
const isPlaying = ref(false);
const isPlayBlocked = ref(false);

let observer: IntersectionObserver | null = null;

const checkDeviceCapability = () => {
	let coresOk = true;
	let memoryOk = true;

	if (typeof navigator !== "undefined") {
		if (navigator.hardwareConcurrency !== undefined) {
			coresOk = navigator.hardwareConcurrency >= 4;
		}
		if ((navigator as any).deviceMemory !== undefined) {
			memoryOk = (navigator as any).deviceMemory >= 4;
		}
	}
	return coresOk && memoryOk;
};

const checkNetworkCapability = () => {
	let netOk = true;
	if (typeof navigator !== "undefined" && (navigator as any).connection) {
		const conn = (navigator as any).connection;
		if (conn.saveData === true) {
			netOk = false;
		}
		if (conn.effectiveType) {
			const slowTypes = ["slow-2g", "2g", "3g"];
			if (slowTypes.includes(conn.effectiveType)) {
				netOk = false;
			}
		}
	}
	return netOk;
};

const isAutoPlayAllowed = computed(() => {
	if (!props.autoplay) return false;

	// Only apply hardware and network checks on mobile viewports (< 768px)
	const isMobile = typeof window !== "undefined" && window.innerWidth < 768;
	if (!isMobile) return true;

	return checkDeviceCapability() && checkNetworkCapability();
});

const attemptPlay = () => {
	if (videoRef.value) {
		videoRef.value
			.play()
			.then(() => {
				isPlaying.value = true;
				isPlayBlocked.value = false;
			})
			.catch((err) => {
				if (err.name !== "AbortError") {
					console.warn("Autoplay blocked or failed:", err);
				}
				isPlaying.value = false;
				isPlayBlocked.value = true;
			});
	}
};

const togglePlay = () => {
	if (!videoRef.value) return;
	if (isPlaying.value) {
		videoRef.value.pause();
		isPlaying.value = false;
	} else {
		attemptPlay();
	}
};

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
						isPlaying.value = false;
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

const hasCalledLoad = ref(false);

// Unified watcher to coordinate loading and playing without race conditions
watch([isLoaded, isIntersecting], async ([loaded, intersecting]) => {
	if (!videoRef.value) return;

	if (loaded && intersecting) {
		if (!hasCalledLoad.value) {
			hasCalledLoad.value = true;
			await nextTick();
			if (!isIntersecting.value || !videoRef.value) return;
			videoRef.value.load();
		}
		if (isAutoPlayAllowed.value && isIntersecting.value) {
			attemptPlay();
		} else {
			isPlayBlocked.value = true;
		}
	} else if (!intersecting) {
		videoRef.value.pause();
		isPlaying.value = false;
	}
});

// Reset load state if the source URL changes dynamically
watch(
	() => props.src,
	async () => {
		hasCalledLoad.value = false;
		if (isLoaded.value && isIntersecting.value && videoRef.value) {
			await nextTick();
			videoRef.value.load();
			if (isAutoPlayAllowed.value) {
				attemptPlay();
			}
		}
	},
);

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
			@play="isPlaying = true"
			@pause="isPlaying = false"
			@playing="isPlaying = true"
			@click="controls ? null : togglePlay()"
			:class="[className, 'transition-opacity duration-500 cursor-pointer', isVideoReady ? 'opacity-100 relative z-5' : 'opacity-0 absolute inset-0']"
			preload="none"
		>
			<source v-if="isLoaded" :src="src" type="video/mp4" />
			<source v-if="isLoaded" :src="src" type="video/webm" />
		</video>

		<!-- Modern Play Button Overlay (Glassmorphism design) -->
		<div 
			v-if="isVideoReady && !isPlaying"
			class="absolute inset-0 flex items-center justify-center bg-black/20 dark:bg-black/40 z-20 cursor-pointer transition-all duration-350 hover:bg-black/30"
			@click.stop="togglePlay"
		>
			<button 
				class="w-14 h-14 rounded-full flex items-center justify-center bg-white/25 dark:bg-zinc-900/30 backdrop-blur-md border border-white/35 dark:border-zinc-800/50 shadow-[0_8px_32px_rgba(0,0,0,0.3)] text-white hover:scale-110 active:scale-95 transition-all duration-300 group"
				aria-label="Play video"
			>
				<Play class="w-6 h-6 fill-white text-white ml-0.5 group-hover:text-indigo-400 transition-colors" />
			</button>
		</div>
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
