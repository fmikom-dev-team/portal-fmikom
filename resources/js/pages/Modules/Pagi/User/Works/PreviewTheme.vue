<script setup lang="ts">
import { Head, Link, usePage } from "@inertiajs/vue3";
import { ArrowLeft, Check, Star } from "lucide-vue-next";
import { computed, ref } from "vue";

const props = defineProps<{
	theme: string;
	profileUser: any;
	projects: any[];
}>();

import BarcelonaTheme from "./Themes/BarcelonaTheme.vue";
import CapeTownTheme from "./Themes/CapeTownTheme.vue";
import LondonTheme from "./Themes/LondonTheme.vue";
import NewYorkTheme from "./Themes/NewYorkTheme.vue";
import ParisTheme from "./Themes/ParisTheme.vue";
import SydneyTheme from "./Themes/SydneyTheme.vue";
import TokyoTheme from "./Themes/TokyoTheme.vue";

const themeComponents: Record<string, any> = {
	sydney: SydneyTheme,
	"cape-town": CapeTownTheme,
	barcelona: BarcelonaTheme,
	london: LondonTheme,
	tokyo: TokyoTheme,
	paris: ParisTheme,
	"new-york": NewYorkTheme,
};

const themeComponent = computed(() => {
	return themeComponents[props.theme] || SydneyTheme;
});

// Palettes configuration corresponding to each theme
interface Palette {
	name: string;
	bg: string;
	text: string;
	accent?: string;
	cardBg?: string;
}

const themePalettes = computed<Palette[]>(() => {
	switch (props.theme) {
		case "sydney":
			return [
				{
					name: "Dawn",
					bg: "#4A1521",
					text: "#FFFFFF",
					accent: "#FFE8E8",
					cardBg: "#330D14",
				},
				{
					name: "Midnight",
					bg: "#ECE6DB",
					text: "#1E1E1E",
					accent: "#E53E3E",
					cardBg: "#E3DCD1",
				},
				{
					name: "Noon",
					bg: "#F7F4A5",
					text: "#2C1B4D",
					accent: "#2C1B4D",
					cardBg: "#EAE68C",
				},
				{
					name: "Ocean",
					bg: "#B3C1CD",
					text: "#1D2D44",
					accent: "#E53E3E",
					cardBg: "#9EB0BE",
				},
			];
		case "cape-town":
			return [
				{
					name: "Savannah",
					bg: "#FAF7F2",
					text: "#967A50",
					accent: "linear-gradient(135deg, #6366f1, #a855f7)",
					cardBg: "#F3EEE3",
				},
				{
					name: "Lavender",
					bg: "#F3E8FF",
					text: "#5B21B6",
					accent: "linear-gradient(135deg, #ec4899, #f43f5e)",
					cardBg: "#E9D5FF",
				},
				{
					name: "Forest",
					bg: "#45543C",
					text: "#D1FAE5",
					accent: "linear-gradient(135deg, #f59e0b, #eab308)",
					cardBg: "#374330",
				},
				{
					name: "Obsidian",
					bg: "#121212",
					text: "#E5E7EB",
					accent: "linear-gradient(135deg, #06b6d4, #3b82f6)",
					cardBg: "#1F1F1F",
				},
			];
		case "barcelona":
			return [
				{
					name: "Sunset",
					bg: "linear-gradient(135deg, #fbcfe8, #fca5a5, #fcd34d)",
					text: "#FFFFFF",
					accent: "rgba(0,0,0,0.15)",
					cardBg: "rgba(255,255,255,0.3)",
				},
				{
					name: "Cyberpunk",
					bg: "#0F172A",
					text: "#10B981",
					accent: "rgba(255,255,255,0.05)",
					cardBg: "#1E293B",
				},
				{
					name: "Mint",
					bg: "#D1FAE5",
					text: "#065F46",
					accent: "rgba(6,95,70,0.08)",
					cardBg: "#A7F3D0",
				},
				{
					name: "Rose",
					bg: "#FCE7F3",
					text: "#9D174D",
					accent: "rgba(157,23,77,0.08)",
					cardBg: "#FBCFE8",
				},
			];
		case "london":
			return [
				{
					name: "Westminster",
					bg: "#FFFFFF",
					text: "#0F172A",
					accent: "linear-gradient(135deg, #14b8a6, #4f46e5)",
					cardBg: "#F8FAFC",
				},
				{
					name: "Soho",
					bg: "#FFF5F5",
					text: "#9B2C2C",
					accent: "linear-gradient(135deg, #f97316, #ec4899)",
					cardBg: "#FEE2E2",
				},
				{
					name: "Fog",
					bg: "#F1F5F9",
					text: "#334155",
					accent: "linear-gradient(135deg, #64748b, #0f172a)",
					cardBg: "#E2E8F0",
				},
				{
					name: "Thames",
					bg: "#0B132B",
					text: "#E2E8F0",
					accent: "linear-gradient(135deg, #3a0ca3, #7209b7)",
					cardBg: "#1C2541",
				},
			];
		case "tokyo":
			return [
				{
					name: "Shibuya",
					bg: "#FAF9F6",
					text: "#111111",
					accent: "#E53E3E",
					cardBg: "#111111",
				},
				{
					name: "Akihabara",
					bg: "#111111",
					text: "#FAF9F6",
					accent: "#38BDF8",
					cardBg: "#FAF9F6",
				},
				{
					name: "Kyoto",
					bg: "#FDF0D5",
					text: "#003049",
					accent: "#C1121F",
					cardBg: "#003049",
				},
				{
					name: "Ginza",
					bg: "#0D1B2A",
					text: "#E0E1DD",
					accent: "#F59E0B",
					cardBg: "#E0E1DD",
				},
			];
		case "paris":
			return [
				{ name: "Louvre", bg: "#FBFBFA", text: "#1E2522", accent: "#FF5E3A" },
				{ name: "Seine", bg: "#F3FDE8", text: "#2C3E20", accent: "#E83E8C" },
				{ name: "Orsay", bg: "#E8F1F2", text: "#132E32", accent: "#8A2BE2" },
				{ name: "Bastille", bg: "#1C1C1C", text: "#ECEBE4", accent: "#FFD700" },
			];
		case "new-york":
			return [
				{
					name: "Neon Cyan",
					bg: "#0B0F19",
					text: "#F3F4F6",
					accent: "#06B6D4",
					cardBg: "rgba(17, 24, 39, 0.7)",
				},
				{
					name: "Interstellar",
					bg: "#090714",
					text: "#F3F4F6",
					accent: "#8B5CF6",
					cardBg: "rgba(21, 16, 36, 0.7)",
				},
				{
					name: "Aurora",
					bg: "#050B0D",
					text: "#F3F4F6",
					accent: "#10B981",
					cardBg: "rgba(12, 28, 30, 0.7)",
				},
				{
					name: "Dark Apple",
					bg: "#0A0A0A",
					text: "#F3F4F6",
					accent: "#F3F4F6",
					cardBg: "rgba(20, 20, 20, 0.7)",
				},
			];
		default:
			return [];
	}
});

const selectedPaletteIndex = ref(0);

const activePalette = computed(() => {
	return (
		themePalettes.value[selectedPaletteIndex.value] || themePalettes.value[0]
	);
});

const themeTitle = computed(() => {
	const names: Record<string, string> = {
		sydney: "Sydney",
		"cape-town": "Cape Town",
		barcelona: "Barcelona",
		london: "London",
		tokyo: "Tokyo",
		paris: "Paris",
		"new-york": "New York",
	};
	return names[props.theme] || "Premium Layout";
});
</script>

<template>
	<Head>
        <title>{{ 'Preview Theme — ' + themeTitle }}</title>
    </Head>

	<div class="h-screen flex flex-col bg-slate-50 dark:bg-zinc-950 font-sans text-slate-900 dark:text-slate-100 overflow-hidden">
		
		<!-- TOP BAR / HEADER -->
		<header class="h-16 flex items-center justify-between px-6 border-b border-slate-200 dark:border-zinc-900 bg-white dark:bg-zinc-900 shrink-0 z-30 shadow-xs">
			<Link href="/pagi/works" class="flex items-center gap-2 text-sm font-extrabold text-slate-700 hover:text-slate-900 dark:text-zinc-350 dark:hover:text-white transition-colors cursor-pointer">
				<ArrowLeft class="w-4 h-4" />
				<span>Templates</span>
				<span class="text-slate-300 dark:text-zinc-700">/</span>
				<span class="text-slate-900 dark:text-white">{{ themeTitle }}</span>
			</Link>

			<Link 
				:href="'/pagi/works/editor/' + props.theme + '?palette=' + selectedPaletteIndex"
				class="inline-flex items-center bg-slate-950 text-white hover:bg-indigo-600 px-6 py-2.5 rounded-xl text-xs font-extrabold shadow-md transition-all active:scale-97 cursor-pointer"
			>
				Continue with template
			</Link>
		</header>

		<!-- MAIN CONTAINER (SPLIT LAYOUT) -->
		<div class="flex-1 flex overflow-hidden">
			
			<!-- LEFT SIDE: FULL SIZE LIVE THEME PREVIEW MOCKUP -->
			<div class="flex-1 bg-slate-100 dark:bg-zinc-950 p-8 flex items-center justify-center overflow-hidden">
				<div 
					class="w-full h-full max-w-4xl rounded-3xl border border-slate-200/80 dark:border-zinc-800/80 shadow-2xl overflow-y-auto overflow-x-hidden transition-all duration-300 relative select-none"
					:style="{ backgroundColor: activePalette.bg, color: activePalette.text }"
				>
					
					<!-- DYNAMIC LAYOUT TEMPLATE MOCKUP -->
					<component 
						:is="themeComponent" 
						:profile-user="props.profileUser" 
						:projects="[]" 
						:active-palette="activePalette" 
						:is-mockup="true"
						:is-interactive="false"
					/>

				</div>
			</div>

			<!-- RIGHT SIDE: PALETTES SWATCH SELECTOR (SCREENSHOT 1) -->
			<div class="w-72 bg-white dark:bg-zinc-900 border-l border-slate-200 dark:border-zinc-800 p-6 flex flex-col justify-between shrink-0 overflow-y-auto z-20">
				<div>
					<h3 class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-zinc-500 mb-6">
						Choose a Color Scheme
					</h3>

					<div class="space-y-4">
						<button 
							v-for="(palette, idx) in themePalettes" 
							:key="idx"
							@click="selectedPaletteIndex = idx"
							class="w-full flex items-center justify-between p-3.5 rounded-xl border text-left cursor-pointer transition-all active:scale-98 select-none bg-slate-50/50 hover:bg-slate-50 dark:bg-zinc-950/20 dark:hover:bg-zinc-950/50"
							:class="[
								selectedPaletteIndex === idx 
									? 'border-slate-900 dark:border-white ring-2 ring-slate-900/10 dark:ring-white/10' 
									: 'border-slate-200 dark:border-zinc-850'
							]"
						>
							<div class="space-y-1">
								<span class="text-xs font-bold text-slate-800 dark:text-zinc-200 block">{{ palette.name }}</span>
								<!-- Mini color swatches row -->
								<div class="flex gap-1 pt-1.5">
									<div class="w-3.5 h-3.5 rounded-full border border-black/5" :style="{ background: palette.bg }"></div>
									<div class="w-3.5 h-3.5 rounded-full border border-black/5" :style="{ background: palette.cardBg || palette.accent }"></div>
									<div class="w-3.5 h-3.5 rounded-full border border-black/5" :style="{ background: palette.text }"></div>
								</div>
							</div>

							<div 
								class="w-5 h-5 rounded-full border flex items-center justify-center shrink-0"
								:class="selectedPaletteIndex === idx ? 'bg-slate-900 dark:bg-white border-transparent' : 'border-slate-300 dark:border-zinc-700'"
							>
								<Check v-if="selectedPaletteIndex === idx" class="w-3 h-3 text-white dark:text-slate-900" />
							</div>
						</button>
					</div>
				</div>

				<div class="pt-6 border-t border-slate-100 dark:border-zinc-850">
					<p class="text-[10px] text-slate-400 dark:text-zinc-550 leading-relaxed">
						Click "Continue with template" to set up your portfolio page details, upload custom domains, and override your text details.
					</p>
				</div>
			</div>

		</div>
	</div>
</template>

<style scoped>
/* Inline rotation scale deco */
.scale-102 {
	transform: scale(1.02) rotate(1deg);
}
</style>
