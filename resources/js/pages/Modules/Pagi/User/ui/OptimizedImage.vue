<script setup lang="ts">
import { computed, onMounted, ref, watch } from "vue";

const props = withDefaults(
	defineProps<{
		src: string;
		alt: string;
		sizes?: string;
		fetchpriority?: "high" | "low" | "auto";
		loading?: "lazy" | "eager";
		className?: string;
		masonry?: boolean;
		isSensitive?: boolean;
	}>(),
	{
		sizes: "(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw",
		fetchpriority: "auto",
		loading: "lazy",
		className: "w-full h-full object-cover",
		masonry: false,
		isSensitive: false,
	},
);

const revealedSensitive = ref(false);

const toggleReveal = (e: MouseEvent) => {
	e.stopPropagation();
	revealedSensitive.value = !revealedSensitive.value;
};

// Determine if the component is being used as a small avatar or icon
const isSmall = computed(() => {
	const cn = props.className || "";
	return (
		cn.includes("rounded-full") || /\b(h|w)-(2|3|4|5|6|8|10|12|16)\b/.test(cn)
	);
});

const isBlobOrData = computed(
	() => props.src?.startsWith("blob:") || props.src?.startsWith("data:"),
);

const isLoaded = ref(isSmall.value || isBlobOrData.value);
const imgRef = ref<HTMLImageElement | null>(null);

const handleLoad = () => {
	isLoaded.value = true;
};

const checkLoaded = () => {
	if (imgRef.value?.complete) {
		isLoaded.value = true;
	}
};

onMounted(() => {
	checkLoaded();
});

watch(
	() => props.src,
	(newSrc) => {
		if (newSrc?.startsWith("blob:") || newSrc?.startsWith("data:")) {
			isLoaded.value = true;
		} else {
			isLoaded.value = isSmall.value;
			setTimeout(checkLoaded, 50);
		}
	},
	{ immediate: true },
);

// Generate high performance srcset for popular dynamic media CDNs
const srcset = computed(() => {
	if (!props.src) return undefined;

	// Case 1: Unsplash URL
	if (props.src.includes("images.unsplash.com")) {
		const baseUrl = props.src.split("?")[0];
		let arParam = "";
		let fit = "crop";
		try {
			const urlObj = new URL(props.src);
			const ar = urlObj.searchParams.get("ar");
			if (ar) arParam = `&ar=${ar}`;
			const f = urlObj.searchParams.get("fit");
			if (f) fit = f;
		} catch (e) {
			// Ignore invalid URLs
		}
		return `
			${baseUrl}?auto=format&fit=${fit}${arParam}&w=480&q=80 480w,
			${baseUrl}?auto=format&fit=${fit}${arParam}&w=768&q=80 768w,
			${baseUrl}?auto=format&fit=${fit}${arParam}&w=1200&q=80 1200w
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
	if (isHFull.value) {
		return "h-full w-full";
	}
	// Masonry mode: no min-height so images determine natural heights for Pinterest-style layouts
	if (props.masonry) {
		return "h-auto w-full";
	}
	return "h-auto w-full min-h-[180px] bg-slate-100 dark:bg-zinc-800/80 rounded-xl";
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
				'transition-all duration-300 z-10',
				isLoaded ? 'opacity-100' : 'opacity-0',
				isHFull ? 'absolute inset-0' : '',
				isSensitive && !revealedSensitive ? 'blur-xl scale-110 filter pointer-events-none' : '',
			]"
		/>

		<!-- SENSITIVE CONTENT BLUR OVERLAY -->
		<div
			v-if="isSensitive && !revealedSensitive"
			class="absolute inset-0 z-20 flex flex-col items-center justify-center p-4 bg-black/40 backdrop-blur-md text-white text-center select-none"
		>
			<div class="w-10 h-10 rounded-full bg-amber-500/20 border border-amber-400/40 flex items-center justify-center mb-2 shadow-lg animate-pulse">
				<svg class="w-5 h-5 text-amber-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.955 11.955 0 003 10c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286z" />
				</svg>
			</div>
			<p class="text-xs font-bold tracking-wide uppercase text-amber-300 mb-0.5">Konten Sensitif</p>
			<p class="text-[11px] text-slate-200/90 mb-3 max-w-[200px] leading-tight">Foto diburamkan oleh Sistem Moderasi AI Kampus.</p>
			<button
				type="button"
				@click="toggleReveal"
				class="px-3 py-1.5 rounded-full bg-white/20 hover:bg-white/30 border border-white/30 text-[11px] font-semibold transition-all active:scale-95 shadow-xs"
			>
				Lihat Foto
			</button>
		</div>

		<!-- Re-hide button when revealed -->
		<button
			v-if="isSensitive && revealedSensitive"
			type="button"
			@click="toggleReveal"
			class="absolute top-2 right-2 z-20 px-2 py-1 rounded-md bg-black/60 hover:bg-black/80 text-amber-300 border border-amber-400/30 text-[10px] font-medium transition-all"
			title="Sembunyikan kembali"
		>
			🛡️ Sembunyikan
		</button>
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
