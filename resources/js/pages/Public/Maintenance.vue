<script setup lang="ts">
import { Head, usePage, router } from "@inertiajs/vue3";
import { ArrowDown, Globe, Mail, RefreshCw, LogOut } from "lucide-vue-next";
import { computed, onMounted, onUnmounted, ref } from "vue";

defineProps<{
	message: string;
}>();

const page = usePage();
const siteSettings = computed(() => (page.props as any).siteSettings || {});

const isMounted = ref(false);

// Text options for the rotating text badge
const texts = ["unugha", "pagi", "trace", "wims", "fast"];
const currentTextIndex = ref(0);
const containerWidth = ref<number | null>(null);
let rotationIntervalId: ReturnType<typeof setInterval> | null = null;

// Split text into characters with support for emoji graphemes
const splitIntoCharacters = (text: string): string[] => {
	if (typeof Intl !== "undefined" && "Segmenter" in Intl) {
		const segmenter = new Intl.Segmenter("en", { granularity: "grapheme" });
		return Array.from(segmenter.segment(text), ({ segment }) => segment);
	}
	return Array.from(text);
};

// Computed property for the characters of the current text
const currentCharacters = computed(() =>
	splitIntoCharacters(texts[currentTextIndex.value]),
);

// Helper to measure text width dynamically based on responsive classes
const measureTextWidth = (text: string): number => {
	if (typeof document === "undefined") return 0;

	const tempSpan = document.createElement("span");
	tempSpan.style.visibility = "hidden";
	tempSpan.style.position = "absolute";
	tempSpan.style.whiteSpace = "nowrap";

	// Apply exact font weights & sizes as the actual rendering target
	tempSpan.className = "text-4xl md:text-6xl font-black px-6";
	tempSpan.innerText = text;

	document.body.appendChild(tempSpan);
	const width = tempSpan.getBoundingClientRect().width;
	document.body.removeChild(tempSpan);

	return width;
};

// Handle window resizing to recalculate responsive width
const handleResize = () => {
	containerWidth.value = measureTextWidth(texts[currentTextIndex.value]);
};

onMounted(() => {
	setTimeout(() => {
		isMounted.value = true;
	}, 100);

	// Initial width calculation
	containerWidth.value = measureTextWidth(texts[0]);

	// Rotation interval (using 2.8s for clean staggered animation cycle)
	rotationIntervalId = setInterval(() => {
		const nextIndex = (currentTextIndex.value + 1) % texts.length;
		containerWidth.value = measureTextWidth(texts[nextIndex]);
		currentTextIndex.value = nextIndex;
	}, 2800);

	window.addEventListener("resize", handleResize);
});

onUnmounted(() => {
	if (rotationIntervalId) {
		clearInterval(rotationIntervalId);
	}
	window.removeEventListener("resize", handleResize);
});

// Dynamic values
const brandName = computed(
	() => siteSettings.value.brand_name || "Portal FMIKOM",
);
const brandLogo = computed(
	() => siteSettings.value.brand_logo || "/asset/apple-touch-icon.png",
);

// Dynamic hostname for the website detail
const websiteUrl = computed(() => {
	if (typeof window !== "undefined") {
		return window.location.hostname;
	}
	return "fmikom.suntree.my.id";
});

const refreshPage = () => {
	window.location.reload();
};

const handleLogout = () => {
	router.post("/logout");
};
</script>

<template>
	<Head>
        <title>Pemeliharaan Sistem - Under Maintenance</title>
    </Head>

	<section class="relative flex items-center justify-center overflow-hidden bg-slate-50 min-h-screen w-full text-slate-900 select-none">
		<!-- Animated background grid -->
		<div class="absolute inset-0 bg-[linear-gradient(to_right,#e2e8f0_1px,transparent_1px),linear-gradient(to_bottom,#e2e8f0_1px,transparent_1px)] bg-[size:24px_24px] pointer-events-none opacity-60"></div>
		
		<!-- Ambient lighting glow -->
		<div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-primary/5 rounded-full blur-[120px] pointer-events-none"></div>

		<div class="relative z-10 mx-auto max-w-5xl text-center px-6 py-12">
			<div class="transition-all duration-700 ease-out" :class="isMounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'">
				
				<!-- Brand Logo Container -->
				<div 
					class="mb-6 inline-block transition-all duration-700 ease-out delay-150 transform"
					:class="isMounted ? 'scale-100' : 'scale-0'"
				>
					<div class="mx-auto h-24 w-24 rounded-full border-4 border-white bg-white shadow-xl flex items-center justify-center overflow-hidden p-2 relative group hover:scale-105 transition-transform duration-300">
						<img :src="brandLogo" class="h-full w-full object-contain" :alt="brandName" />
					</div>
				</div>

				<!-- Heading with responsive rotating text badge -->
				<h1 
					class="mb-6 text-4xl font-extrabold text-slate-900 md:text-6xl tracking-tight transition-all duration-700 ease-out delay-300 flex flex-col sm:flex-row items-center justify-center gap-x-3 gap-y-2"
					:class="isMounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
				>
					<span>FMIKOM</span>
					<span 
						class="badge-container inline-flex overflow-hidden relative h-[1.35em] items-center justify-center bg-primary text-white py-0.5 rounded-2xl shadow-md transform hover:scale-[1.02] transition-transform duration-200"
						:style="{ width: containerWidth ? `${containerWidth}px` : 'auto' }"
					>
						<!-- Explicit out-in transition duration matching CSS animation times -->
						<Transition name="word" mode="out-in" :duration="{ enter: 700, leave: 450 }">
							<span :key="currentTextIndex" class="word-container font-black text-center w-full px-6 flex items-center justify-center">
								<span 
									v-for="(char, index) in currentCharacters" 
									:key="index"
									class="char-span inline-block"
									:style="{ animationDelay: `${index * 25}ms` }"
								>
									<template v-if="char === ' '">&nbsp;</template>
									<template v-else>{{ char }}</template>
								</span>
							</span>
						</Transition>
					</span>
				</h1>

				<!-- Subtitle / Message -->
				<p 
					class="mx-auto mb-8 max-w-2xl text-lg text-slate-650 md:text-xl leading-relaxed font-medium transition-all duration-700 ease-out delay-450"
					:class="isMounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
				>
					{{ message || "Kami sedang melakukan pemeliharaan server berkala untuk meningkatkan performa layanan. Silakan kembali beberapa saat lagi." }}
				</p>

				<!-- Buttons -->
				<div 
					class="mb-12 flex flex-wrap justify-center gap-4 transition-all duration-700 ease-out delay-600"
					:class="isMounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
				>
					<a 
						href="mailto:admin@fmikom.ac.id" 
						class="inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary/95 text-white font-extrabold text-xs tracking-wider uppercase px-6 py-3.5 rounded-xl transition-all active:scale-[0.98] shadow-md shadow-primary/10 cursor-pointer"
					>
						<Mail class="h-4 w-4" />
						Hubungi Admin
					</a>
					<button 
						@click="refreshPage"
						class="inline-flex items-center justify-center gap-2 border border-slate-200 hover:border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-extrabold text-xs tracking-wider uppercase px-6 py-3.5 rounded-xl transition-all active:scale-[0.98] shadow-xs cursor-pointer"
					>
						Segarkan Halaman
						<RefreshCw class="h-4 w-4 animate-spin-hover" />
					</button>
					<button 
						v-if="(page.props as any).auth?.user"
						@click="handleLogout"
						class="inline-flex items-center justify-center gap-2 border border-red-200 hover:border-red-300 bg-red-50 hover:bg-red-100 text-red-700 font-extrabold text-xs tracking-wider uppercase px-6 py-3.5 rounded-xl transition-all active:scale-[0.98] shadow-xs cursor-pointer"
					>
						Keluar / Log Out
						<LogOut class="h-4 w-4" />
					</button>
				</div>

				<!-- Social Links -->
				<div 
					class="flex justify-center gap-4 transition-all duration-700 ease-out delay-700"
					:class="isMounted ? 'opacity-100' : 'opacity-0'"
				>
					<a 
						:href="'https://' + websiteUrl"
						target="_blank"
						class="flex h-11 w-11 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 transition-all hover:bg-primary hover:text-white hover:border-primary hover:-translate-y-0.5 shadow-xs active:scale-95 cursor-pointer"
					>
						<Globe class="h-5 w-5" />
					</a>
					<a 
						href="mailto:admin@fmikom.ac.id"
						class="flex h-11 w-11 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 transition-all hover:bg-primary hover:text-white hover:border-primary hover:-translate-y-0.5 shadow-xs active:scale-95 cursor-pointer"
					>
						<Mail class="h-5 w-5" />
					</a>
				</div>

			</div>
		</div>

		<!-- Scroll / Return Indicator -->
		<div 
			class="absolute bottom-8 left-1/2 -translate-x-1/2 transform transition-all duration-1000 ease-out delay-1000 flex flex-col items-center gap-1.5"
			:class="isMounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
		>
			<span class="text-[9px] uppercase tracking-widest text-slate-400 font-extrabold animate-pulse">Kembali beberapa saat lagi</span>
			<ArrowDown class="h-4 w-4 text-slate-400 animate-bounce" />
		</div>
	</section>
</template>

<style scoped>
.text-slate-650 {
	color: #475569;
}

/* Parent badge container handles width animation smoothly */
.badge-container {
	transition: width 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
	will-change: width;
}

/* Word container holding the letters */
.word-container {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	white-space: nowrap;
}

/* Individual character span defaults */
.char-span {
	display: inline-block;
	transform: translateY(0);
	opacity: 1;
	will-change: transform, opacity;
}

/* Staggered Enter Animation: Sliding Text Reveal from Bottom */
.word-enter-active .char-span {
	animation: charIn 0.45s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}

/* Staggered Exit Animation: Sliding Text Out to Top */
.word-leave-active .char-span {
	animation: charOut 0.35s ease-in both;
}

@keyframes charIn {
	0% {
		transform: translateY(100%);
		opacity: 0;
	}
	100% {
		transform: translateY(0);
		opacity: 1;
	}
}

@keyframes charOut {
	0% {
		transform: translateY(0);
		opacity: 1;
	}
	100% {
		transform: translateY(-120%);
		opacity: 0;
	}
}

/* Custom micro-animation on hover for reload icon */
.animate-spin-hover:hover {
	transform: rotate(360deg);
	transition: transform 0.6s ease-in-out;
}
</style>
