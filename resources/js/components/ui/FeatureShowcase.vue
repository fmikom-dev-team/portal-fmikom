<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { ChevronDown } from "lucide-vue-next";
import { computed, ref } from "vue";

export interface TabMedia {
	value: string;
	label: string;
	src: string;
	alt?: string;
	title?: string;
	author?: string;
}

export interface ShowcaseStep {
	id: string;
	title: string;
	text: string;
}

const props = withDefaults(
	defineProps<{
		eyebrow?: string;
		title: string;
		description?: string;
		stats?: string[];
		steps?: ShowcaseStep[];
		tabs: TabMedia[];
		defaultTab?: string;
		panelMinHeight?: number;
		className?: string;
	}>(),
	{
		eyebrow: "Mahasiswa FMIKOM",
		description: "Jelajahi inovasi teknologi, desain antarmuka, dan aplikasi buatan mahasiswa yang telah dikurasi secara ketat.",
		stats: () => ["Karya Terverifikasi", "100% Produk Mahasiswa", "Siap Kolaborasi"],
		steps: () => [
			{
				id: "step-1",
				title: "Inovasi & Konsep Digital",
				text: "Setiap karya diawali dari riset empiris dan perancangan antarmuka yang presisi sesuai standar industri.",
			},
			{
				id: "step-2",
				title: "Implementasi Teknologi Modern",
				text: "Dibangun menggunakan stack teknologi terkini seperti Laravel, Vue 3, Tailwind CSS, dan AI automatization.",
			},
			{
				id: "step-3",
				title: "Siap Dipublikasikan & Kolaborasi",
				text: "Portofolio terpilih dikurasi langsung oleh fakultas untuk dipromosikan ke jaringan industri & alumni.",
			},
		],
		panelMinHeight: 720,
	},
);

const activeTab = ref(props.defaultTab ?? props.tabs[0]?.value ?? "tab-0");
const activeAccordion = ref<string | null>(null);
const brokenImages = ref<Record<string, boolean>>({});

const isPreviewOpen = ref(false);
const previewImageSrc = ref("");
const previewImageTitle = ref("");

const openImagePreview = (src: string, title?: string) => {
	if (!src) return;
	previewImageSrc.value = src;
	previewImageTitle.value = title || "Pratinjau Karya";
	isPreviewOpen.value = true;
};

const closeImagePreview = () => {
	isPreviewOpen.value = false;
};

const toggleAccordion = (id: string) => {
	activeAccordion.value = activeAccordion.value === id ? null : id;
};

const currentMedia = computed(() => {
	return (props.tabs.find((t) => t.value === activeTab.value) || props.tabs[0]) as any;
});

const currentSteps = computed(() => {
	if (currentMedia.value && currentMedia.value.steps && currentMedia.value.steps.length > 0) {
		return currentMedia.value.steps;
	}
	return props.steps;
});
</script>

<template>
    <section :class="['w-full bg-white dark:bg-slate-950 text-slate-900 dark:text-white transition-colors duration-300 my-4', className]">
        <div class="container mx-auto grid max-w-7xl grid-cols-1 gap-10 px-6 py-12 md:grid-cols-12 md:py-16 lg:gap-14">
            <!-- Left column (Natural flow spacing matching 21st.dev) -->
            <div class="md:col-span-6 flex flex-col justify-start">
                <!-- Eyebrow Badge -->
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold w-fit mb-6">
                    {{ eyebrow }}
                </div>

                <!-- Title -->
                <h2 class="text-balance text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold leading-[0.98] tracking-tight text-slate-900 dark:text-white">
                    {{ title }}
                </h2>

                <!-- Description -->
                <p v-if="description" class="mt-6 text-sm md:text-base leading-relaxed text-slate-500 dark:text-slate-400 max-w-xl font-normal">
                    {{ description }}
                </p>

                <!-- Stats chips -->
                <div v-if="stats.length > 0" class="mt-6 flex flex-wrap gap-2">
                    <span
                        v-for="(s, i) in stats"
                        :key="i"
                        class="inline-flex items-center text-xs font-medium px-3 py-1 rounded-md bg-slate-100 dark:bg-slate-800/80 border border-slate-200/60 dark:border-slate-700/60 text-slate-700 dark:text-slate-300"
                    >
                        {{ s }}
                    </span>
                </div>

                <!-- Accordion Steps -->
                <div class="mt-8 max-w-xl">
                    <div
                        v-for="step in currentSteps"
                        :key="step.id"
                        class="border-b border-slate-200 dark:border-slate-800 py-3 transition-colors"
                    >
                        <button
                            type="button"
                            @click="toggleAccordion(step.id)"
                            class="w-full py-2 flex items-center justify-between text-left text-base font-semibold text-slate-900 dark:text-white hover:text-slate-600 dark:hover:text-slate-300 transition-colors"
                        >
                            <span>{{ step.title }}</span>
                            <ChevronDown
                                :class="['h-4 w-4 text-slate-400 transition-transform duration-200 shrink-0', activeAccordion === step.id ? 'rotate-180 text-slate-900 dark:text-white' : '']"
                            />
                        </button>
                        <div
                            v-show="activeAccordion === step.id"
                            class="pb-3 text-sm leading-relaxed text-slate-500 dark:text-slate-400 font-normal transition-all space-y-2"
                        >
                            <p>{{ step.text }}</p>
                            <!-- Creator Avatar & Name if step 3 -->
                            <div v-if="step.id === 'step-3' && (step.authorName || currentMedia?.author)" class="flex items-center gap-2.5 pt-2 mt-2 border-t border-slate-100 dark:border-slate-800">
                                <div class="h-6 w-6 rounded-full overflow-hidden bg-slate-200 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 flex items-center justify-center shrink-0">
                                    <img v-if="step.authorAvatar || currentMedia?.authorAvatar" :src="step.authorAvatar || currentMedia?.authorAvatar" :alt="step.authorName || currentMedia?.author" class="h-full w-full object-cover" />
                                    <span v-else class="text-[10px] font-bold text-slate-600 dark:text-slate-300">
                                        {{ (step.authorName || currentMedia?.author || 'M').charAt(0).toUpperCase() }}
                                    </span>
                                </div>
                                <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">
                                    {{ step.authorName || currentMedia?.author }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CTAs (Follows accordion naturally with mt-8) -->
                <div class="mt-8 flex flex-wrap gap-3">
                    <Link
                        href="/pagi/login"
                        class="inline-flex items-center justify-center rounded-xl bg-slate-900 dark:bg-white px-6 py-2.5 text-sm font-semibold text-white dark:text-slate-900 shadow-xs hover:bg-slate-800 dark:hover:bg-slate-100 transition-all cursor-pointer"
                    >
                        Get started
                    </Link>
                    <Link
                        href="/pagi/works"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent px-6 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-900 transition-all cursor-pointer"
                    >
                        Jelajahi semua karya
                    </Link>
                </div>
            </div>

            <!-- Right column (Clean Showcase Media Container with rounded-2xl matching 21st.dev reference) -->
            <div class="md:col-span-6 flex items-center">
                <div
                    class="relative w-full h-[380px] sm:h-[480px] md:h-[718px] lg:h-[718px] rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-slate-100 dark:bg-slate-900 overflow-hidden shadow-md flex flex-col justify-between"
                >
                    <!-- Media Showcase Container (Clickable for HD Lightbox Preview) -->
                    <div class="relative w-full h-full flex-1 overflow-hidden group">
                        <template v-for="t in tabs" :key="t.value">
                            <div
                                v-show="activeTab === t.value"
                                class="absolute inset-0 w-full h-full transition-opacity duration-300 flex items-center justify-center bg-slate-900/40 dark:bg-slate-950/60 cursor-zoom-in overflow-hidden p-0"
                                @click="openImagePreview(t.src, t.title || t.label)"
                                title="Klik untuk Pratinjau Gambar Kualitas Tinggi HD"
                            >
                                <template v-if="t.src && !brokenImages[t.value]">
                                    <!-- Ambient Blurred Background for natural poster framing -->
                                    <img
                                        :src="t.src"
                                        alt=""
                                        aria-hidden="true"
                                        class="absolute inset-0 w-full h-full object-cover blur-2xl opacity-40 scale-110 pointer-events-none select-none"
                                    />
                                    <!-- Foreground Fitted Image (Full edge-to-edge cover) -->
                                    <img
                                        :src="t.src"
                                        :alt="t.alt || t.label"
                                        @error="brokenImages[t.value] = true"
                                        class="relative z-10 w-full h-full object-cover object-top rounded-2xl shadow-xl transition-transform duration-500 group-hover:scale-[1.02]"
                                    />
                                </template>
                                <div v-else class="w-full h-full flex flex-col items-center justify-center p-6 text-center bg-slate-100 dark:bg-slate-900">
                                    <div class="w-14 h-14 rounded-2xl bg-slate-200/70 dark:bg-slate-800 flex items-center justify-center mb-3">
                                        <svg class="w-7 h-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159" />
                                        </svg>
                                    </div>
                                    <h4 class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ t.title || t.label }}</h4>
                                </div>

                                <!-- HD Zoom Hint Badge on Hover -->
                                <div class="absolute top-3 right-3 z-20 opacity-0 group-hover:opacity-100 transition-opacity bg-slate-900/80 text-white text-[11px] font-semibold px-3 py-1 rounded-full backdrop-blur-md border border-slate-700/60 shadow-md flex items-center gap-1.5 pointer-events-none">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
                                    </svg>
                                    Pratinjau HD
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Glassmorphic Pill Tab Controls -->
                    <div class="pointer-events-auto absolute inset-x-0 bottom-3 md:bottom-4 z-20 flex w-full justify-center px-3">
                        <div class="inline-flex max-w-full overflow-x-auto no-scrollbar gap-1.5 rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white/85 dark:bg-slate-900/85 p-1.5 backdrop-blur-md shadow-sm">
                            <button
                                v-for="t in tabs"
                                :key="t.value"
                                type="button"
                                @click="activeTab = t.value"
                                :class="[
                                    'rounded-xl px-3.5 sm:px-4 py-1.5 text-xs font-semibold transition-all duration-200 cursor-pointer whitespace-nowrap shrink-0',
                                    activeTab === t.value
                                        ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-xs font-bold'
                                        : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                                ]"
                            >
                                {{ t.label }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- High-Resolution Image Preview Lightbox Modal -->
        <Teleport to="body">
            <div
                v-if="isPreviewOpen"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-md p-4 md:p-8"
                @click.self="closeImagePreview"
            >
                <div class="relative max-w-5xl w-full max-h-[92vh] flex flex-col items-center justify-center">
                    <button
                        type="button"
                        @click="closeImagePreview"
                        class="absolute -top-10 right-0 md:top-2 md:right-2 z-10 p-2.5 rounded-full bg-white/10 hover:bg-white/20 text-white backdrop-blur-md transition-all cursor-pointer border border-white/20"
                        title="Tutup Pratinjau"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <img
                        :src="previewImageSrc"
                        :alt="previewImageTitle"
                        class="max-h-[84vh] w-auto max-w-full object-contain rounded-2xl shadow-2xl border border-white/10"
                    />
                    <p class="mt-3 text-xs md:text-sm font-semibold text-slate-200 tracking-wide bg-slate-900/90 px-4 py-1.5 rounded-full border border-slate-700/60 shadow-lg">
                        {{ previewImageTitle }} (Kualitas Tinggi HD)
                    </p>
                </div>
            </div>
        </Teleport>
    </section>
</template>
