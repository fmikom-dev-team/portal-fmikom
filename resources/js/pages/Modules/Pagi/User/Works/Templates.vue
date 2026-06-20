<script setup lang="ts">
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { ArrowRight, Check, Star } from "lucide-vue-next";
import { computed, ref } from "vue";
import Footer from "../ui/Footer.vue";
import Navbar from "../ui/Navbar.vue";
import UmumNavbar from "../ui/UmumNavbar.vue";

const page = usePage();

const isMahasiswa = computed(() => {
	const role = (page.props as any).context?.active_role || "mahasiswa";
	return role.toLowerCase() === "mahasiswa";
});

// Access authenticated user info for personalized template previews
const userName = computed(
	() => (page.props as any).auth?.user?.name || "Jenny Lee",
);
const userPagiUsername = computed(
	() => (page.props as any).auth?.user?.pagi_username || "jennylee",
);
const userAvatar = computed(
	() => (page.props as any).auth?.user?.avatar || null,
);
const userInitials = computed(() => {
	const name = userName.value;
	return name
		.split(" ")
		.map((n: string) => n[0])
		.join("")
		.substring(0, 2)
		.toUpperCase();
});

const currentTheme = computed(
	() => (page.props as any).auth?.user?.metadata?.pagi_work_theme || null,
);
const currentPaletteIndex = computed(
	() => (page.props as any).auth?.user?.metadata?.pagi_work_palette_index ?? 0,
);

const isApplying = ref<string | null>(null);

const applyTheme = (themeId: string | null, themeName: string) => {
	isApplying.value = themeId || "standard";
	const paletteIndex = themeId ? (selectedPalettes.value[themeName] ?? 0) : 0;
	router.post(
		"/pagi/works/select",
		{
			theme: themeId,
			palette_index: paletteIndex,
		},
		{
			onFinish: () => {
				isApplying.value = null;
			},
		},
	);
};

// Mockup data for 6 premium templates
interface Palette {
	bg: string;
	text: string;
	accent?: string;
	secondaryBg?: string;
	secondaryText?: string;
	cardBg?: string;
}

interface TemplateTheme {
	id: string;
	name: string;
	tags: string[];
	palettes: Palette[];
}

const templates = ref<TemplateTheme[]>([
	{
		id: "sydney",
		name: "Sydney",
		tags: ["FREE"],
		palettes: [
			{ bg: "#4A1521", text: "#FFFFFF", accent: "#FFE8E8", cardBg: "#330D14" },
			{ bg: "#ECE6DB", text: "#1E1E1E", accent: "#E53E3E", cardBg: "#E3DCD1" },
			{ bg: "#F7F4A5", text: "#2C1B4D", accent: "#2C1B4D", cardBg: "#EAE68C" },
			{ bg: "#B3C1CD", text: "#1D2D44", accent: "#E53E3E", cardBg: "#9EB0BE" },
		],
	},
	{
		id: "cape-town",
		name: "Cape Town",
		tags: ["NEW", "PRO"],
		palettes: [
			{
				bg: "#FAF7F2",
				text: "#967A50",
				accent: "linear-gradient(135deg, #6366f1, #a855f7)",
				cardBg: "#F3EEE3",
			},
			{
				bg: "#F3E8FF",
				text: "#5B21B6",
				accent: "linear-gradient(135deg, #ec4899, #f43f5e)",
				cardBg: "#E9D5FF",
			},
			{
				bg: "#45543C",
				text: "#D1FAE5",
				accent: "linear-gradient(135deg, #f59e0b, #eab308)",
				cardBg: "#374330",
			},
			{
				bg: "#121212",
				text: "#E5E7EB",
				accent: "linear-gradient(135deg, #06b6d4, #3b82f6)",
				cardBg: "#1F1F1F",
			},
		],
	},
	{
		id: "barcelona",
		name: "Barcelona",
		tags: ["PRO"],
		palettes: [
			{
				bg: "linear-gradient(135deg, #fbcfe8, #fca5a5, #fcd34d)",
				text: "#FFFFFF",
				accent: "rgba(0,0,0,0.15)",
				cardBg: "rgba(255,255,255,0.2)",
			},
			{
				bg: "#0F172A",
				text: "#10B981",
				accent: "rgba(255,255,255,0.05)",
				cardBg: "#1E293B",
			},
			{
				bg: "#D1FAE5",
				text: "#065F46",
				accent: "rgba(6,95,70,0.08)",
				cardBg: "#A7F3D0",
			},
			{
				bg: "#FCE7F3",
				text: "#9D174D",
				accent: "rgba(157,23,77,0.08)",
				cardBg: "#FBCFE8",
			},
		],
	},
	{
		id: "london",
		name: "London",
		tags: ["PRO"],
		palettes: [
			{
				bg: "#FFFFFF",
				text: "#0F172A",
				accent: "linear-gradient(135deg, #14b8a6, #4f46e5)",
				cardBg: "#F8FAFC",
			},
			{
				bg: "#FFF5F5",
				text: "#9B2C2C",
				accent: "linear-gradient(135deg, #f97316, #ec4899)",
				cardBg: "#FEE2E2",
			},
			{
				bg: "#F1F5F9",
				text: "#334155",
				accent: "linear-gradient(135deg, #64748b, #0f172a)",
				cardBg: "#E2E8F0",
			},
			{
				bg: "#0B132B",
				text: "#E2E8F0",
				accent: "linear-gradient(135deg, #3a0ca3, #7209b7)",
				cardBg: "#1C2541",
			},
		],
	},
	{
		id: "tokyo",
		name: "Tokyo",
		tags: ["PRO"],
		palettes: [
			{ bg: "#FAF9F6", text: "#111111", accent: "#E53E3E", cardBg: "#111111" },
			{ bg: "#111111", text: "#FAF9F6", accent: "#38BDF8", cardBg: "#FAF9F6" },
			{ bg: "#FDF0D5", text: "#003049", accent: "#C1121F", cardBg: "#003049" },
			{ bg: "#0D1B2A", text: "#E0E1DD", accent: "#F59E0B", cardBg: "#E0E1DD" },
		],
	},
	{
		id: "paris",
		name: "Paris",
		tags: ["PRO"],
		palettes: [
			{ bg: "#FBFBFA", text: "#1E2522", accent: "#FF5E3A" },
			{ bg: "#F3FDE8", text: "#2C3E20", accent: "#E83E8C" },
			{ bg: "#E8F1F2", text: "#132E32", accent: "#8A2BE2" },
			{ bg: "#1C1C1C", text: "#ECEBE4", accent: "#FFD700" },
		],
	},
	{
		id: "new-york",
		name: "New York",
		tags: ["NEW", "PRO"],
		palettes: [
			{
				bg: "#0B0F19",
				text: "#F3F4F6",
				accent: "#06B6D4",
				cardBg: "rgba(17, 24, 39, 0.7)",
			},
			{
				bg: "#090714",
				text: "#F3F4F6",
				accent: "#8B5CF6",
				cardBg: "rgba(21, 16, 36, 0.7)",
			},
			{
				bg: "#050B0D",
				text: "#F3F4F6",
				accent: "#10B981",
				cardBg: "rgba(12, 28, 30, 0.7)",
			},
			{
				bg: "#0A0A0A",
				text: "#F3F4F6",
				accent: "#F3F4F6",
				cardBg: "rgba(20, 20, 20, 0.7)",
			},
		],
	},
]);

// Keep track of active palette indices per template
const selectedPalettes = ref<Record<string, number>>({
	Sydney: currentTheme.value === "sydney" ? currentPaletteIndex.value : 0,
	"Cape Town":
		currentTheme.value === "cape-town" ? currentPaletteIndex.value : 0,
	Barcelona: currentTheme.value === "barcelona" ? currentPaletteIndex.value : 0,
	London: currentTheme.value === "london" ? currentPaletteIndex.value : 0,
	Tokyo: currentTheme.value === "tokyo" ? currentPaletteIndex.value : 0,
	Paris: currentTheme.value === "paris" ? currentPaletteIndex.value : 0,
	"New York": currentTheme.value === "new-york" ? currentPaletteIndex.value : 0,
});

const selectPalette = (themeName: string, idx: number) => {
	selectedPalettes.value[themeName] = idx;
};
</script>

<template>
	<Head>
		<title>Select a Template — Custom Works</title>
	</Head>

	<div class="min-h-screen bg-slate-50 dark:bg-slate-950 font-sans text-slate-900 dark:text-slate-100 selection:bg-slate-200 dark:selection:bg-slate-800">
		<Navbar v-if="isMahasiswa" />
		<UmumNavbar v-else />

		<!-- Header Banner -->
		<div class="max-w-7xl mx-auto px-6 pt-12 pb-6">
			<h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
				Select a template
			</h1>
			<p class="text-sm sm:text-base text-slate-500 dark:text-zinc-400 mt-2 font-medium">
				Explore and choose a theme. Don't worry, you can always switch it later.
			</p>
		</div>

		<!-- Templates Grid -->
		<div class="max-w-7xl mx-auto px-6 pb-24">
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
				
				<!-- Standard PAGI Layout Card -->
				<div 
					class="group flex flex-col bg-white dark:bg-zinc-900 rounded-3xl border border-slate-200/70 dark:border-zinc-800/80 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1"
					:class="{'ring-2 ring-indigo-500 border-indigo-500/50': currentTheme === null}"
				>
					<!-- Card Header / Visual Preview Container -->
					<div class="relative aspect-video w-full overflow-hidden bg-slate-105 dark:bg-zinc-950 flex items-center justify-center p-4 select-none border-b border-slate-100 dark:border-zinc-850">
						<!-- Active Badge if selected -->
						<div v-if="currentTheme === null" class="absolute top-3 left-3 flex items-center gap-1 z-35 font-extrabold text-[10px] tracking-wider uppercase select-none">
							<span class="px-2.5 py-1 rounded-md shadow-2xs font-black bg-indigo-600 text-white flex items-center gap-1">
								<Check class="w-3 h-3" /> ACTIVE
							</span>
						</div>
						
						<!-- MOCKUP STANDARD PROFILE -->
						<div class="w-full h-full rounded-2xl flex flex-col justify-between p-4 bg-slate-50 dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800">
							<div class="flex items-center gap-3">
								<div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-zinc-800 overflow-hidden flex items-center justify-center">
									<img v-if="userAvatar" :src="userAvatar" alt="Avatar" class="w-full h-full object-cover" />
									<span v-else class="text-xs font-black text-slate-700 dark:text-zinc-300">{{ userInitials }}</span>
								</div>
								<div>
									<h4 class="text-xs font-bold">{{ userName }}</h4>
									<p class="text-[8px] text-slate-400">@{{ userPagiUsername }}</p>
								</div>
							</div>
							<div class="space-y-1">
								<div class="h-2 w-full bg-slate-200 dark:bg-zinc-800 rounded"></div>
								<div class="h-2 w-3/4 bg-slate-200 dark:bg-zinc-800 rounded"></div>
							</div>
							<div class="flex gap-2">
								<div class="h-4 w-12 bg-slate-200 dark:bg-zinc-800 rounded-full"></div>
								<div class="h-4 w-12 bg-slate-200 dark:bg-zinc-800 rounded-full"></div>
							</div>
						</div>
					</div>
					
					<!-- Card Info & Footer -->
					<div class="p-6 flex-1 flex flex-col justify-between bg-white dark:bg-zinc-900">
						<p class="text-xs text-slate-400 dark:text-zinc-500 mb-4 leading-relaxed">
							Tampilan profil standar PAGI dengan grid feed karya, sertifikat, dan kolom tentang saya.
						</p>
						
						<div class="flex items-center justify-between mt-auto">
							<h3 class="text-base font-extrabold text-slate-800 dark:text-zinc-100 tracking-tight">
								Standard PAGI
							</h3>
							
							<button
								@click="applyTheme(null, 'Standard')"
								:disabled="isApplying !== null"
								class="inline-flex items-center gap-1 rounded-full px-4.5 py-2 text-xs font-bold transition-all shadow-sm cursor-pointer border-none"
								:class="[
									currentTheme === null 
										? 'bg-slate-100 dark:bg-zinc-800 text-slate-400 dark:text-zinc-500 cursor-not-allowed'
										: 'bg-indigo-600 hover:bg-indigo-550 text-white'
								]"
							>
								{{ currentTheme === null ? 'Active' : (isApplying === 'standard' ? 'Applying...' : 'Apply Layout') }}
								<Check v-if="currentTheme === null" class="w-3.5 h-3.5" />
								<ArrowRight v-else class="w-3.5 h-3.5" />
							</button>
						</div>
					</div>
				</div>
				
				<!-- Render each template card -->
				<div 
					v-for="tpl in templates" 
					:key="tpl.id" 
					class="group flex flex-col bg-white dark:bg-zinc-900 rounded-3xl border border-slate-200/70 dark:border-zinc-800/80 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1"
					:class="{'ring-2 ring-indigo-500 border-indigo-500/50': currentTheme === tpl.id}"
				>
					<!-- Card Header / Visual Preview Container -->
					<div class="relative aspect-video w-full overflow-hidden bg-slate-100 dark:bg-zinc-950 flex items-center justify-center p-4 select-none border-b border-slate-100 dark:border-zinc-850">
						<!-- Active Badge if selected -->
						<div v-if="currentTheme === tpl.id" class="absolute top-3 left-3 flex items-center gap-1 z-35 font-extrabold text-[10px] tracking-wider uppercase select-none">
							<span class="px-2.5 py-1 rounded-md shadow-2xs font-black bg-indigo-600 text-white flex items-center gap-1">
								<Check class="w-3 h-3" /> ACTIVE
							</span>
						</div>

						<!-- Tags Badges (Top-Right) -->
						<div class="absolute top-3 right-3 flex items-center gap-1 z-35 font-extrabold text-[10px] tracking-wider uppercase select-none">
							<span 
								v-for="tag in tpl.tags" 
								:key="tag" 
								:class="[
									'px-2.5 py-1 rounded-md shadow-2xs font-black',
									tag === 'FREE' ? 'bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300' : '',
									tag === 'NEW' ? 'bg-red-500 text-white' : '',
									tag === 'PRO' ? 'bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300' : ''
								]"
							>
								{{ tag }}
							</span>
						</div>

						<!-- MOCKUP MAPPINGS -->

						<!-- 1. SYDNEY MOCKUP -->
						<div 
							v-if="tpl.id === 'sydney'"
							class="w-full h-full rounded-2xl flex flex-col items-center justify-center p-4 transition-all duration-300 relative overflow-hidden"
							:style="{ backgroundColor: tpl.palettes[selectedPalettes[tpl.name]].bg, color: tpl.palettes[selectedPalettes[tpl.name]].text }"
						>
							<div class="w-10 h-10 rounded-full border-2 border-white/40 overflow-hidden flex items-center justify-center bg-zinc-800 shrink-0">
								<img v-if="userAvatar" :src="userAvatar" alt="Avatar" class="w-full h-full object-cover" />
								<span v-else class="text-xs font-black text-white">{{ userInitials }}</span>
							</div>
							<h3 class="text-[12px] font-bold text-center mt-3 leading-snug max-w-[200px]">
								{{ userName }} is a product designer, brand designer, and user researcher.
							</h3>
							
							<!-- Infinite Ticker/Marquee effect simulation -->
							<div 
								class="absolute bottom-4 left-0 right-0 py-1 flex overflow-hidden text-[7px] font-black tracking-widest uppercase select-none rotate-1 scale-105"
								:style="{ backgroundColor: tpl.palettes[selectedPalettes[tpl.name]].cardBg, color: tpl.palettes[selectedPalettes[tpl.name]].accent }"
							>
								<div class="whitespace-nowrap animate-marquee flex gap-4">
									<span>Crafting visual experiences • Design is intelligence • Innovation •</span>
								</div>
							</div>
						</div>

						<!-- 2. CAPE TOWN MOCKUP -->
						<div 
							v-else-if="tpl.id === 'cape-town'"
							class="w-full h-full rounded-2xl flex flex-col justify-between p-4 transition-all duration-300"
							:style="{ backgroundColor: tpl.palettes[selectedPalettes[tpl.name]].bg, color: tpl.palettes[selectedPalettes[tpl.name]].text }"
						>
							<!-- Header -->
							<div class="flex items-center gap-1.5">
								<div class="w-5 h-5 rounded-full overflow-hidden flex items-center justify-center bg-indigo-600 text-[8px] font-black text-white shrink-0">
									{{ userInitials[0] }}
								</div>
								<span class="text-[9px] font-black uppercase tracking-wider">{{ userName }}</span>
							</div>

							<!-- Center Text -->
							<h2 
								class="text-sm font-semibold tracking-tight text-center leading-snug my-2"
								style="font-family: Georgia, serif;"
							>
								Crafting visual experiences for modern brands
							</h2>

							<!-- Dynamic abstract dual gradient cards -->
							<div class="grid grid-cols-2 gap-2 mt-1">
								<div 
									class="h-8 rounded-lg opacity-85 shadow-2xs" 
									:style="{ background: tpl.palettes[selectedPalettes[tpl.name]].accent }"
								></div>
								<div 
									class="h-8 rounded-lg opacity-85 shadow-2xs"
									:style="{ background: tpl.palettes[selectedPalettes[tpl.name]].accent, filter: 'hue-rotate(45deg)' }"
								></div>
							</div>
						</div>

						<!-- 3. BARCELONA MOCKUP -->
						<div 
							v-else-if="tpl.id === 'barcelona'"
							class="w-full h-full rounded-2xl flex flex-col justify-between p-4 transition-all duration-300 relative overflow-hidden"
							:style="{ background: tpl.palettes[selectedPalettes[tpl.name]].bg }"
						>
							<!-- Name Marquee Background styling -->
							<div 
								class="absolute top-2 left-0 right-0 text-center text-[19px] font-black tracking-tighter opacity-15 whitespace-nowrap select-none uppercase font-mono"
								:style="{ color: tpl.palettes[selectedPalettes[tpl.name]].text }"
							>
								{{ userName }} • {{ userName }}
							</div>

							<div class="mt-4 flex flex-col gap-1.5 relative z-10">
								<!-- Role Pills -->
								<div class="flex flex-wrap gap-1 justify-center">
									<span 
										v-for="role in ['Designer', 'Brand', 'Researcher']" 
										:key="role" 
										class="px-2 py-0.5 rounded-full text-[7px] font-extrabold border"
										:style="{ borderColor: tpl.palettes[selectedPalettes[tpl.name]].text, color: tpl.palettes[selectedPalettes[tpl.name]].text, backgroundColor: tpl.palettes[selectedPalettes[tpl.name]].accent }"
									>
										{{ role.toUpperCase() }}
									</span>
								</div>

								<!-- Subheading -->
								<p 
									class="text-[9px] font-bold text-center mt-1.5"
									:style="{ color: tpl.palettes[selectedPalettes[tpl.name]].text }"
								>
									Crafting visual experiences for modern brands
								</p>
							</div>

							<div 
								class="w-full h-8 rounded-xl flex items-center justify-center text-[8px] font-black tracking-widest uppercase border border-white/10"
								:style="{ backgroundColor: tpl.palettes[selectedPalettes[tpl.name]].cardBg, color: tpl.palettes[selectedPalettes[tpl.name]].text }"
							>
								View Projects
							</div>
						</div>

						<!-- 4. LONDON MOCKUP -->
						<div 
							v-else-if="tpl.id === 'london'"
							class="w-full h-full rounded-2xl grid grid-cols-5 p-4 transition-all duration-300"
							:style="{ backgroundColor: tpl.palettes[selectedPalettes[tpl.name]].bg, color: tpl.palettes[selectedPalettes[tpl.name]].text }"
						>
							<div class="col-span-3 flex flex-col justify-center gap-1.5 pr-2">
								<h3 class="text-[12px] font-black tracking-tight leading-none" style="font-family: Georgia, serif;">
									{{ userName }}
								</h3>
								<p class="text-[7px] text-slate-500 font-semibold leading-tight">
									Motion Designer • Web Developer based in Indonesia.
								</p>
								<div class="flex gap-1 mt-1">
									<span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
									<span class="text-[6px] font-bold text-slate-400">Available for work</span>
								</div>
							</div>
							<div class="col-span-2 flex items-center justify-center">
								<!-- Left Right layout image placeholder -->
								<div 
									class="w-full h-16 rounded-xl opacity-90 shadow-2xs rotate-2"
									:style="{ background: tpl.palettes[selectedPalettes[tpl.name]].accent }"
								></div>
							</div>
						</div>

						<!-- 5. TOKYO MOCKUP -->
						<div 
							v-else-if="tpl.id === 'tokyo'"
							class="w-full h-full rounded-2xl flex flex-col justify-between p-4 transition-all duration-300 relative border overflow-hidden"
							:style="{ backgroundColor: tpl.palettes[selectedPalettes[tpl.name]].bg, color: tpl.palettes[selectedPalettes[tpl.name]].text, borderColor: tpl.palettes[selectedPalettes[tpl.name]].accent + '33' }"
						>
							<!-- Border grids -->
							<div class="absolute inset-0 grid grid-cols-3 grid-rows-3 pointer-events-none opacity-10">
								<div class="border-r border-b" :style="{ borderColor: tpl.palettes[selectedPalettes[tpl.name]].accent }"></div>
								<div class="border-r border-b" :style="{ borderColor: tpl.palettes[selectedPalettes[tpl.name]].accent }"></div>
								<div class="border-b" :style="{ borderColor: tpl.palettes[selectedPalettes[tpl.name]].accent }"></div>
								<div class="border-r border-b" :style="{ borderColor: tpl.palettes[selectedPalettes[tpl.name]].accent }"></div>
								<div class="border-r border-b" :style="{ borderColor: tpl.palettes[selectedPalettes[tpl.name]].accent }"></div>
								<div class="border-b" :style="{ borderColor: tpl.palettes[selectedPalettes[tpl.name]].accent }"></div>
							</div>

							<div class="flex justify-between items-start z-10">
								<span class="text-[8px] font-mono tracking-wider">INDEX: 01</span>
								<span class="text-[8px] font-mono">2026/06</span>
							</div>

							<!-- Bold graphic name text -->
							<h1 
								class="text-xl font-black tracking-widest text-center select-none uppercase z-10"
								:style="{ color: tpl.palettes[selectedPalettes[tpl.name]].text }"
							>
								{{ userInitials }}
							</h1>

							<div class="flex justify-between items-center z-10">
								<p class="text-[6px] font-mono tracking-tight uppercase leading-none max-w-[80px]">
									Creative coding & interaction.
								</p>
								<div class="w-3.5 h-3.5 flex items-center justify-center rounded-full bg-red-500 text-[6px] text-white">
									→
								</div>
							</div>
						</div>

						<!-- 6. PARIS MOCKUP -->
						<div 
							v-else-if="tpl.id === 'paris'"
							class="w-full h-full rounded-2xl flex flex-col justify-between p-4 transition-all duration-300 relative"
							:style="{ backgroundColor: tpl.palettes[selectedPalettes[tpl.name]].bg, color: tpl.palettes[selectedPalettes[tpl.name]].text }"
						>
							<!-- Star Icon (Sticker) in top right -->
							<div class="absolute top-4 right-4 z-10">
								<Star 
									class="w-6 h-6 fill-current animate-spin" 
									:style="{ color: tpl.palettes[selectedPalettes[tpl.name]].accent, animationDuration: '8s' }" 
								/>
							</div>

							<div>
								<span class="text-[7px] font-bold tracking-widest uppercase">Editorial Theme</span>
								<!-- Large Serif Typography split -->
								<h2 
									class="text-base font-medium tracking-tight mt-1 leading-tight max-w-[120px]"
									style="font-family: 'Times New Roman', Times, serif;"
								>
									{{ userName.split(' ')[0] }}<br />{{ userName.split(' ')[1] || 'Karya' }}
								</h2>
							</div>

							<div class="flex items-end justify-between">
								<p class="text-[6px] italic leading-tight max-w-[90px] opacity-75">
									A curated selection of thoughts, creations and visuals.
								</p>
								<span class="text-[7px] font-bold border-b pb-0.5" :style="{ borderColor: tpl.palettes[selectedPalettes[tpl.name]].text }">
									ENTER WORKS
								</span>
							</div>
						</div>

						<!-- 7. NEW YORK MOCKUP -->
						<div 
							v-else-if="tpl.id === 'new-york'"
							class="w-full h-full rounded-2xl flex flex-col justify-between p-4 transition-all duration-300 relative overflow-hidden text-left"
							:style="{ backgroundColor: tpl.palettes[selectedPalettes[tpl.name]].bg, color: tpl.palettes[selectedPalettes[tpl.name]].text }"
						>
							<!-- Background glows mockup -->
							<div class="absolute -top-10 -left-10 w-24 h-24 rounded-full blur-xl opacity-30 pointer-events-none" :style="{ background: tpl.palettes[selectedPalettes[tpl.name]].accent }"></div>
							
							<div class="flex justify-between items-center z-10">
								<span class="text-[7px] font-mono opacity-60">NEW YORK PRIME</span>
								<div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
							</div>

							<div class="space-y-1 z-10">
								<h3 class="text-[12px] font-extrabold leading-none text-white tracking-tight">
									{{ userName }}
								</h3>
								<p class="text-[6px] font-bold text-slate-400">
									SaaS Designer & Fullstack Engineer
								</p>
							</div>

							<div class="grid grid-cols-3 gap-1 z-10">
								<div class="h-5 rounded border border-white/5 bg-white/5"></div>
								<div class="h-5 rounded border border-white/5 bg-white/5"></div>
								<div class="h-5 rounded border border-white/5 bg-white/5"></div>
							</div>
						</div>

					</div>

					<!-- Card Info & Footer -->
					<div class="p-6 flex-1 flex flex-col justify-between bg-white dark:bg-zinc-900">
						<!-- Color Palettes Selector Row -->
						<div class="flex items-center gap-2 mb-4">
							<span class="text-[10px] font-extrabold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Variasi Warna:</span>
							<div class="flex items-center gap-1.5">
								<button 
									v-for="(palette, idx) in tpl.palettes" 
									:key="idx" 
									@click="selectPalette(tpl.name, idx)"
									class="w-5 h-5 rounded-full border border-slate-200/90 dark:border-zinc-800 flex items-center justify-center p-0 cursor-pointer overflow-hidden transition-transform duration-150 hover:scale-110 active:scale-95"
									:style="{ background: palette.bg }"
									:title="'Palette ' + (idx + 1)"
								>
									<!-- small check icon if selected -->
									<Check 
										v-if="selectedPalettes[tpl.name] === idx"
										class="w-2.5 h-2.5" 
										:class="palette.text === '#FFFFFF' || palette.text.startsWith('linear') ? 'text-white' : 'text-slate-800'"
									/>
								</button>
							</div>
						</div>

						<!-- Footer Row (Theme Name + Action Button) -->
						<div class="flex items-center justify-between mt-auto">
							<h3 class="text-base font-extrabold text-slate-800 dark:text-zinc-100 tracking-tight">
								{{ tpl.name }}
							</h3>
							
							<Link 
								:href="'/pagi/works/preview/' + tpl.id"
								class="inline-flex items-center gap-1.5 rounded-full px-5 py-2.5 text-xs font-bold transition-all shadow-sm cursor-pointer border-none bg-slate-900 hover:bg-slate-800 dark:bg-indigo-600 dark:hover:bg-indigo-600 text-white"
							>
								Browse theme
								<ArrowRight class="w-3.5 h-3.5" />
							</Link>
						</div>
					</div>

				</div>

			</div>
		</div>

		<Footer />
	</div>
</template>

<style scoped>
/* Simulation of a smooth sliding text marquee */
@keyframes marquee {
	0% { transform: translateX(10%); }
	50% { transform: translateX(-10%); }
	100% { transform: translateX(10%); }
}

.animate-marquee {
	animation: marquee 16s linear infinite;
}
</style>
