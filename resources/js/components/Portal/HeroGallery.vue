<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Layers, X, Eye } from "lucide-vue-next";

const props = defineProps<{
    gallery: string[];
}>();

const activeCardIndex = ref(0);
const isAnimating = ref(false);
const swipeDirection = ref<"left" | "right" | null>(null);

const isPreviewOpen = ref(false);
const previewImage = ref("");

const openLightbox = (img: string) => {
    previewImage.value = img;
    isPreviewOpen.value = true;
};

const closeLightbox = () => {
    isPreviewOpen.value = false;
};

const optimizeImageUrl = (url: string, width = 800) => {
	if (!url) return "";
	if (url.includes("images.unsplash.com")) {
		if (url.includes("w=")) {
			return url.replace(/w=\d+/, `w=${width}`).replace(/q=\d+/, "q=80");
		}
		const separator = url.includes("?") ? "&" : "?";
		return `${url}${separator}w=${width}&q=80&auto=format&fit=crop`;
	}
	return url;
};

const cardStyles = computed(() => {
	const total = props.gallery.length;
	if (total === 0) return [];
	return props.gallery.map((_, i) => {
		const offset = (i - activeCardIndex.value + total) % total;
		const zIndex = total - offset;
		if (offset === 0) {
			// Front card
			return {
				zIndex,
				transform: "translateX(0) translateY(0) rotate(0deg) scale(1)",
				opacity: "1",
				transition: "all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)",
			};
		} else if (offset === 1) {
			return {
				zIndex,
				transform: "translateX(8px) translateY(10px) rotate(2deg) scale(0.96)",
				opacity: "1",
				transition: "all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)",
			};
		} else if (offset === 2) {
			return {
				zIndex,
				transform: "translateX(16px) translateY(20px) rotate(4deg) scale(0.92)",
				opacity: "0.8",
				transition: "all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)",
			};
		} else {
			return {
				zIndex: 0,
				transform: "translateX(24px) translateY(30px) scale(0.88)",
				opacity: "0",
				transition: "all 0.4s ease",
			};
		}
	});
});

const nextCard = () => {
	if (isAnimating.value || props.gallery.length <= 1) return;
	swipeDirection.value = "left";
	isAnimating.value = true;
	setTimeout(() => {
		activeCardIndex.value = (activeCardIndex.value + 1) % props.gallery.length;
		swipeDirection.value = null;
		setTimeout(() => {
			isAnimating.value = false;
		}, 50);
	}, 300);
};

const prevCard = () => {
	if (isAnimating.value || props.gallery.length <= 1) return;
	swipeDirection.value = "right";
	isAnimating.value = true;
	setTimeout(() => {
		activeCardIndex.value =
			(activeCardIndex.value - 1 + props.gallery.length) % props.gallery.length;
		swipeDirection.value = null;
		setTimeout(() => {
			isAnimating.value = false;
		}, 50);
	}, 300);
};

// Touch/swipe support
const touchStartX = ref(0);
const onTouchStart = (e: TouchEvent) => {
	touchStartX.value = e.touches[0].clientX;
};
const onTouchEnd = (e: TouchEvent) => {
	const diff = touchStartX.value - e.changedTouches[0].clientX;
	if (Math.abs(diff) > 50) {
		diff > 0 ? nextCard() : prevCard();
	}
};

let autoRotateInterval: ReturnType<typeof setInterval> | null = null;
onMounted(() => {
    autoRotateInterval = setInterval(() => {
        nextCard();
    }, 4000);
});

onUnmounted(() => {
    if (autoRotateInterval) {
        clearInterval(autoRotateInterval);
    }
});
</script>

<template>
    <!-- Interactive Stacked Card Gallery -->
    <div v-if="gallery.length > 0" class="relative w-full max-w-[560px] mx-auto select-none">
        <!-- Card Stack Container -->
        <div
            class="relative aspect-square cursor-grab active:cursor-grabbing"
            @touchstart="onTouchStart"
            @touchend="onTouchEnd"
        >
            <div
                v-for="(img, i) in gallery"
                :key="img"
                class="absolute inset-0 rounded-2xl overflow-hidden shadow-xl border-4 border-white ring-1 ring-black/5"
                :style="{
                    ...cardStyles[i],
                    ...(swipeDirection === 'left' && (i - activeCardIndex + gallery.length) % gallery.length === 0 ? { transform: 'translateX(-120%) rotate(-15deg)', opacity: '0' } : {}),
                    ...(swipeDirection === 'right' && (i - activeCardIndex + gallery.length) % gallery.length === 0 ? { transform: 'translateX(120%) rotate(15deg)', opacity: '0' } : {}),
                }"
            >
                <img 
                    :src="optimizeImageUrl(img, 800)" 
                    :alt="'Gallery ' + (i + 1)" 
                    class="w-full h-full object-cover" 
                    draggable="false" 
                    width="560" 
                    height="385"
                    :loading="i === activeCardIndex ? 'eager' : 'lazy'"
                    :fetchpriority="i === activeCardIndex ? 'high' : 'low'"
                    decoding="async"
                >
                <!-- Gradient overlay at bottom -->
                <div class="absolute inset-x-0 bottom-0 h-20 bg-linear-to-t from-black/30 to-transparent"></div>
                <!-- Card number badge -->
                <div class="absolute top-3 right-3 bg-black/40 backdrop-blur-sm text-white text-[11px] font-bold px-2.5 py-1 rounded-full">
                    {{ i + 1 }} / {{ gallery.length }}
                </div>
                <!-- Click-to-preview overlay on the active card -->
                <div 
                    v-if="(i - activeCardIndex + gallery.length) % gallery.length === 0"
                    @click="openLightbox(img)"
                    class="absolute inset-0 bg-black/0 hover:bg-black/40 transition-all duration-300 flex flex-col items-center justify-center cursor-pointer group/overlay"
                >
                    <div class="w-12 h-12 rounded-full bg-white/20 hover:bg-white/40 backdrop-blur-md flex items-center justify-center opacity-0 group-hover/overlay:opacity-100 scale-90 group-hover/overlay:scale-100 transition-all duration-300 shadow-lg border border-white/20">
                        <Eye class="w-5 h-5 text-white" />
                    </div>
                    <span class="text-white text-[11px] font-black mt-2 opacity-0 group-hover/overlay:opacity-100 transition-all duration-300 drop-shadow-md">
                        Klik untuk perbesar
                    </span>
                </div>
            </div>
        </div>

        <!-- Navigation Arrows -->
        <div class="flex items-center justify-between mt-4 px-2">
            <button
                @click="prevCard"
                class="group flex items-center justify-center w-11 h-11 rounded-full bg-white shadow-md border border-gray-200 hover:border-[#2563eb] hover:bg-[#2563eb] transition-all duration-200 disabled:opacity-40"
                :disabled="gallery.length <= 1"
                aria-label="Previous gallery image"
            >
                <svg class="w-4 h-4 text-gray-700 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <!-- Dot Indicators -->
            <div class="flex items-center gap-0.5">
                <button
                    v-for="(_, i) in gallery"
                    :key="i"
                    @click="activeCardIndex = i"
                    class="group flex items-center justify-center w-11 h-11 rounded-full transition-all cursor-pointer"
                    :aria-label="'Go to gallery slide ' + (i + 1)"
                >
                    <span
                        class="rounded-full transition-all duration-300"
                        :class="activeCardIndex === i ? 'w-6 h-2.5 bg-[#2563eb]' : 'w-2.5 h-2.5 bg-gray-300 group-hover:bg-gray-400'"
                    ></span>
                </button>
            </div>

            <button
                @click="nextCard"
                class="group flex items-center justify-center w-11 h-11 rounded-full bg-white shadow-md border border-gray-200 hover:border-[#2563eb] hover:bg-[#2563eb] transition-all duration-200 disabled:opacity-40"
                :disabled="gallery.length <= 1"
                aria-label="Next gallery image"
            >
                <svg class="w-4 h-4 text-gray-700 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        <!-- Swipe Hint -->
        <p class="text-center text-[11px] text-gray-400 font-medium mt-2">Geser atau klik panah untuk melihat karya berikutnya</p>
    </div>

    <!-- Fallback Graphic if no gallery images -->
    <div v-else class="relative z-10 rotate-1 transform rounded-2xl bg-white p-2 shadow-2xl ring-1 ring-gray-900/5 transition-all duration-700 hover:scale-105 hover:rotate-0 hover:shadow-[0_20px_50px_rgba(37,99,235,0.15)] md:p-4">
        <div class="flex aspect-square w-full items-center justify-center rounded-xl border border-gray-100 bg-gray-50 shadow-inner flex-col gap-4 text-gray-400">
            <Layers class="w-16 h-16 opacity-50" />
            <span class="text-sm font-bold opacity-50">Upload Galeri Karya di Portal Admin</span>
            <span class="text-xs opacity-40">Tata Letak → Hero Section → Galeri</span>
        </div>
    </div>

    <!-- Lightbox Modal -->
    <Teleport to="body">
        <div 
            v-if="isPreviewOpen" 
            class="fixed inset-0 bg-slate-950/90 backdrop-blur-md z-[999] flex items-center justify-center p-4 transition-all duration-300 cursor-zoom-out"
            @click="closeLightbox"
        >
            <button 
                @click="closeLightbox" 
                class="absolute top-6 right-6 text-white/80 hover:text-white p-2.5 rounded-full bg-white/10 hover:bg-white/20 transition-all duration-200 cursor-pointer shadow-lg hover:scale-110"
                aria-label="Close preview"
            >
                <X class="w-6 h-6" />
            </button>
            <div class="relative max-w-4xl max-h-[85vh] flex items-center justify-center cursor-default" @click.stop>
                <img 
                    :src="optimizeImageUrl(previewImage, 1200)" 
                    alt="Preview Gambar" 
                    class="max-w-full max-h-[80vh] rounded-2xl object-contain shadow-2xl border-4 border-white dark:border-slate-800"
                >
                <p class="absolute -bottom-8 left-1/2 -translate-x-1/2 text-white/90 text-xs font-bold tracking-widest uppercase">
                    {{ activeCardIndex + 1 }} dari {{ gallery.length }}
                </p>
            </div>
        </div>
    </Teleport>
</template>
