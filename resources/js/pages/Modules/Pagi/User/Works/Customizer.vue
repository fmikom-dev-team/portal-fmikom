<script setup lang="ts">
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import {
	ArrowLeft,
	ArrowRight,
	Award,
	Check,
	ChevronRight,
	Copy,
	Edit,
	Globe,
	Image,
	Layout,
	Lock,
	MapPin,
	Plus,
	Settings,
	Share2,
	Sparkles,
	Star,
} from "lucide-vue-next";
import { computed, onMounted, ref } from "vue";

const props = defineProps<{
	theme: string;
	customWork: any;
	profileUser: any;
	projects: any[];
}>();

// Page state overrides
const customTitle = ref(
	props.customWork?.custom_title || props.profileUser.name,
);
const customBio = ref(
	props.customWork?.custom_bio ||
		props.profileUser.bio ||
		"Creative Product Designer and Web Developer.",
);
const selectedPaletteIndex = ref(props.customWork?.palette_index ?? 0);
const isPublished = ref(props.customWork?.is_published ?? true);

// Initialize selected projects list (defaults to showing all user's profile projects if new)
const selectedProjectIds = ref<number[]>(
	props.customWork?.selected_projects || props.projects.map((p) => p.id),
);

// Toggle project selection
const toggleProject = (id: number) => {
	const idx = selectedProjectIds.value.indexOf(id);
	if (idx > -1) {
		selectedProjectIds.value.splice(idx, 1);
	} else {
		selectedProjectIds.value.push(id);
	}
};

// Filter projects dynamically for the live mockup preview
const previewProjects = computed(() => {
	return props.projects.filter((p) => selectedProjectIds.value.includes(p.id));
});

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

// Color palettes per theme
interface Palette {
	name: string;
	bg: string;
	text: string;
	accent?: string;
	cardBg?: string;
	border?: string;
	badge?: string;
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
					border: "#6b2031",
					badge: "bg-red-950/40 text-red-200",
				},
				{
					name: "Midnight",
					bg: "#ECE6DB",
					text: "#1E1E1E",
					accent: "#E53E3E",
					cardBg: "#E3DCD1",
					border: "#d5cbb8",
					badge: "bg-stone-200 text-stone-850",
				},
				{
					name: "Noon",
					bg: "#F7F4A5",
					text: "#2C1B4D",
					accent: "#2C1B4D",
					cardBg: "#EAE68C",
					border: "#d7d268",
					badge: "bg-yellow-100 text-yellow-900",
				},
				{
					name: "Ocean",
					bg: "#B3C1CD",
					text: "#1D2D44",
					accent: "#E53E3E",
					cardBg: "#9EB0BE",
					border: "#8fa2b1",
					badge: "bg-slate-200 text-slate-800",
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
					border: "#EBE3D5",
					badge: "bg-amber-100 text-amber-800",
				},
				{
					name: "Lavender",
					bg: "#F3E8FF",
					text: "#5B21B6",
					accent: "linear-gradient(135deg, #ec4899, #f43f5e)",
					cardBg: "#E9D5FF",
					border: "#D8B4FE",
					badge: "bg-purple-200 text-purple-900",
				},
				{
					name: "Forest",
					bg: "#45543C",
					text: "#D1FAE5",
					accent: "linear-gradient(135deg, #f59e0b, #eab308)",
					cardBg: "#374330",
					border: "#2E3B28",
					badge: "bg-emerald-950/40 text-emerald-200",
				},
				{
					name: "Obsidian",
					bg: "#121212",
					text: "#E5E7EB",
					accent: "linear-gradient(135deg, #06b6d4, #3b82f6)",
					cardBg: "#1F1F1F",
					border: "#2E2E2E",
					badge: "bg-zinc-800 text-zinc-300",
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
					border: "rgba(255,255,255,0.6)",
					badge: "bg-white/50 text-pink-900",
				},
				{
					name: "Cyberpunk",
					bg: "#0F172A",
					text: "#10B981",
					accent: "rgba(255,255,255,0.05)",
					cardBg: "#1E293B",
					border: "#334155",
					badge: "bg-slate-800 text-emerald-400",
				},
				{
					name: "Mint",
					bg: "#D1FAE5",
					text: "#065F46",
					accent: "rgba(6,95,70,0.08)",
					cardBg: "#A7F3D0",
					border: "#6EE7B7",
					badge: "bg-emerald-200/60 text-emerald-900",
				},
				{
					name: "Rose",
					bg: "#FCE7F3",
					text: "#9D174D",
					accent: "rgba(157,23,77,0.08)",
					cardBg: "#FBCFE8",
					border: "#F9A8D4",
					badge: "bg-pink-200/60 text-pink-900",
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
					border: "#F1F5F9",
					badge: "bg-indigo-50 text-indigo-700",
				},
				{
					name: "Soho",
					bg: "#FFF5F5",
					text: "#9B2C2C",
					accent: "linear-gradient(135deg, #f97316, #ec4899)",
					cardBg: "#FEE2E2",
					border: "#FCA5A5",
					badge: "bg-red-100 text-red-800",
				},
				{
					name: "Fog",
					bg: "#F1F5F9",
					text: "#334155",
					accent: "linear-gradient(135deg, #64748b, #0f172a)",
					cardBg: "#E2E8F0",
					border: "#CBD5E1",
					badge: "bg-slate-200 text-slate-800",
				},
				{
					name: "Thames",
					bg: "#0B132B",
					text: "#E2E8F0",
					accent: "linear-gradient(135deg, #3a0ca3, #7209b7)",
					cardBg: "#1C2541",
					border: "#3A0CA3",
					badge: "bg-indigo-950/40 text-indigo-300",
				},
			];
		case "tokyo":
			return [
				{
					name: "Shibuya",
					bg: "#FAF9F6",
					text: "#111111",
					accent: "#E53E3E",
					cardBg: "#FFFFFF",
					border: "#111111",
					badge: "border border-black bg-transparent text-black",
				},
				{
					name: "Akihabara",
					bg: "#111111",
					text: "#FAF9F6",
					accent: "#38BDF8",
					cardBg: "#1A1A1A",
					border: "#333333",
					badge: "border border-zinc-700 bg-transparent text-zinc-300",
				},
				{
					name: "Kyoto",
					bg: "#FDF0D5",
					text: "#003049",
					accent: "#C1121F",
					cardBg: "#FFFDF9",
					border: "#003049",
					badge: "border border-sky-900 bg-transparent text-sky-950",
				},
				{
					name: "Ginza",
					bg: "#0D1B2A",
					text: "#E0E1DD",
					accent: "#F59E0B",
					cardBg: "#1B263B",
					border: "#415A77",
					badge: "border border-slate-650 bg-transparent text-slate-300",
				},
			];
		case "paris":
			return [
				{
					name: "Louvre",
					bg: "#FBFBFA",
					text: "#1E2522",
					accent: "#FF5E3A",
					cardBg: "#FAF9F5",
					border: "#EAE6DF",
					badge: "bg-[#F3EFE9] text-[#554C3E]",
				},
				{
					name: "Seine",
					bg: "#F3FDE8",
					text: "#2C3E20",
					accent: "#E83E8C",
					cardBg: "#E8F5E9",
					border: "#C8E6C9",
					badge: "bg-emerald-100 text-emerald-800",
				},
				{
					name: "Orsay",
					bg: "#E8F1F2",
					text: "#132E32",
					accent: "#8A2BE2",
					cardBg: "#D1E8E2",
					border: "#A7D7C5",
					badge: "bg-[#a9ded9] text-teal-950",
				},
				{
					name: "Bastille",
					bg: "#1C1C1C",
					text: "#ECEBE4",
					accent: "#FFD700",
					cardBg: "#2A2A2A",
					border: "#3A3A3A",
					badge: "bg-zinc-800 text-yellow-450",
				},
			];
		case "new-york":
			return [
				{
					name: "Neon Cyan",
					bg: "#0B0F19",
					text: "#F3F4F6",
					accent: "#06B6D4",
					cardBg: "rgba(17, 24, 39, 0.7)",
					border: "rgba(6, 182, 212, 0.15)",
					badge: "bg-cyan-950/40 text-cyan-250 border border-cyan-500/20",
				},
				{
					name: "Interstellar",
					bg: "#090714",
					text: "#F3F4F6",
					accent: "#8B5CF6",
					cardBg: "rgba(21, 16, 36, 0.7)",
					border: "rgba(139, 92, 246, 0.15)",
					badge: "bg-purple-950/40 text-purple-250 border border-purple-500/20",
				},
				{
					name: "Aurora",
					bg: "#050B0D",
					text: "#F3F4F6",
					accent: "#10B981",
					cardBg: "rgba(12, 28, 30, 0.7)",
					border: "rgba(16, 185, 129, 0.15)",
					badge:
						"bg-emerald-950/40 text-emerald-250 border border-emerald-500/20",
				},
				{
					name: "Dark Apple",
					bg: "#0A0A0A",
					text: "#F3F4F6",
					accent: "#F3F4F6",
					cardBg: "rgba(20, 20, 20, 0.7)",
					border: "rgba(255, 255, 255, 0.15)",
					badge: "bg-zinc-900 text-zinc-100 border border-zinc-750",
				},
			];
		default:
			return [];
	}
});

const activePalette = computed(() => {
	return (
		themePalettes.value[selectedPaletteIndex.value] || themePalettes.value[0]
	);
});

// Sidebar active editing tab
const activeTab = ref<"style" | "content" | "share" | null>("style");

// Action button logic
const isSaving = ref(false);
const saveWork = () => {
	isSaving.value = true;
	router.post(
		"/pagi/works/save",
		{
			theme: props.theme,
			palette_index: selectedPaletteIndex.value,
			custom_title: customTitle.value,
			custom_bio: customBio.value,
			selected_projects: selectedProjectIds.value,
			is_published: isPublished.value,
		},
		{
			onFinish: () => {
				isSaving.value = false;
			},
		},
	);
};

// Share URL copying helper
const showShareCopied = ref(false);
const copyShareUrl = () => {
	const url = `${window.location.origin}/pagi/works/v/${props.profileUser.pagi_username}`;
	navigator.clipboard.writeText(url).then(() => {
		showShareCopied.value = true;
		setTimeout(() => {
			showShareCopied.value = false;
		}, 3000);
	});
};
</script>

<template>
	<Head>
        <title>Customize Works Theme</title>
    </Head>

	<div class="h-screen flex flex-col bg-slate-50 dark:bg-zinc-950 font-sans text-slate-900 dark:text-slate-100 overflow-hidden">
		
		<!-- TOP BAR -->
		<header class="h-16 flex items-center justify-between px-6 border-b border-slate-200 dark:border-zinc-900 bg-white dark:bg-zinc-900 shrink-0 z-30 shadow-xs">
			<Link href="/pagi/works" class="flex items-center gap-2 text-sm font-extrabold text-slate-700 hover:text-slate-900 dark:text-zinc-350 dark:hover:text-white transition-colors cursor-pointer">
				<ArrowLeft class="w-4 h-4" />
				<span>Templates</span>
				<span class="text-slate-300 dark:text-zinc-700">/</span>
				<span class="text-slate-900 dark:text-white">Customize</span>
			</Link>

			<div class="flex items-center gap-3">
				<!-- Draft/Publish Toggle -->
				<label class="flex items-center gap-2 cursor-pointer select-none">
					<input type="checkbox" v-model="isPublished" class="sr-only peer" />
					<div class="relative w-9 h-5 bg-slate-200 dark:bg-zinc-800 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
					<span class="text-xs font-bold text-slate-600 dark:text-zinc-400">
						{{ isPublished ? 'Public' : 'Draft' }}
					</span>
				</label>

				<button 
					@click="saveWork"
					:disabled="isSaving"
					class="inline-flex items-center bg-indigo-600 hover:bg-indigo-600 text-white px-5 py-2 rounded-xl text-xs font-extrabold shadow-md transition-all active:scale-97 cursor-pointer border-none"
				>
					{{ isSaving ? 'Publishing...' : 'Publish Works' }}
				</button>
			</div>
		</header>

		<!-- WORKSPACE GRID -->
		<div class="flex-1 flex overflow-hidden">
			
			<!-- LEFT/CENTER AREA: LIVE CUSTOM PORTFOLIO PREVIEW -->
			<div class="flex-1 bg-slate-100 dark:bg-zinc-950 p-6 flex items-center justify-center overflow-hidden">
				<div 
					class="w-full h-full max-w-4xl rounded-3xl border border-slate-200/80 dark:border-zinc-800/80 shadow-2xl overflow-y-auto overflow-x-hidden transition-all duration-300 relative select-none"
					:style="{ backgroundColor: activePalette.bg, color: activePalette.text }"
				>
					
					<!-- DYNAMIC LAYOUT TEMPLATE PREVIEW -->
					<component 
						:is="themeComponent" 
						:profile-user="props.profileUser" 
						:projects="previewProjects" 
						:active-palette="activePalette" 
						:custom-title="customTitle"
						:custom-bio="customBio"
						:is-interactive="false"
					/>

				</div>
			</div>

			<!-- RIGHT SIDEBAR PANEL: CUSTOMIZATION PANEL (SCREENSHOT 2) -->
			<div class="w-80 bg-white dark:bg-zinc-900 border-l border-slate-200 dark:border-zinc-800 p-6 flex flex-col justify-between shrink-0 overflow-y-auto z-20">
				<div class="space-y-6">
					
					<!-- Customization Section -->
					<div>
						<div class="flex items-center justify-between mb-4">
							<h3 class="text-sm font-black uppercase tracking-wider text-slate-800 dark:text-zinc-200">
								Customization
							</h3>
							<span class="px-2 py-0.5 rounded text-[8px] font-black uppercase bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300">
								PRO
							</span>
						</div>

						<!-- Accordions list -->
						<div class="space-y-2.5 text-left">
							<!-- 1. Fonts, Colors, Swatches -->
							<div class="border border-slate-100 dark:border-zinc-800 rounded-xl overflow-hidden">
								<button 
									@click="activeTab = activeTab === 'style' ? null : 'style'"
									class="w-full flex items-center justify-between p-3.5 bg-slate-50/50 hover:bg-slate-50 dark:bg-zinc-950/20 dark:hover:bg-zinc-950/50 border-none outline-none text-xs font-bold text-slate-700 dark:text-zinc-300 cursor-pointer"
								>
									<span class="flex items-center gap-2">
										<Palette class="w-4 h-4 text-slate-400" /> Color Variations
									</span>
									<ChevronRight class="w-4 h-4 transform transition-transform" :class="{'rotate-90': activeTab === 'style'}" />
								</button>
								<div v-show="activeTab === 'style'" class="p-3 bg-white dark:bg-zinc-900 border-t border-slate-100 dark:border-zinc-800 space-y-2">
									<div class="grid grid-cols-4 gap-2">
										<button 
											v-for="(palette, idx) in themePalettes" 
											:key="idx"
											@click="selectedPaletteIndex = idx"
											class="h-9 rounded-lg border flex items-center justify-center p-0 overflow-hidden cursor-pointer active:scale-95 transition-transform"
											:style="{ background: palette.bg, borderColor: selectedPaletteIndex === idx ? '#4F46E5' : 'transparent' }"
											:title="palette.name"
										>
											<Check v-if="selectedPaletteIndex === idx" class="w-3.5 h-3.5 text-white bg-slate-900/50 rounded-full p-0.5" />
										</button>
									</div>
									<span class="text-[10px] text-slate-400 block pt-1 text-center font-bold">Palette: {{ themePalettes[selectedPaletteIndex]?.name }}</span>
								</div>
							</div>

							<!-- 2. Logo Upload -->
							<div class="border border-slate-100 dark:border-zinc-800 rounded-xl overflow-hidden opacity-60">
								<div class="w-full flex items-center justify-between p-3.5 bg-slate-50/50 dark:bg-zinc-950/20 text-xs font-bold text-slate-400 dark:text-zinc-550 select-none">
									<span class="flex items-center gap-2">
										<Image class="w-4 h-4" /> Works Logo
									</span>
									<Lock class="w-3.5 h-3.5" />
								</div>
							</div>

							<!-- 3. Custom Domain -->
							<div class="border border-slate-100 dark:border-zinc-800 rounded-xl overflow-hidden opacity-60">
								<div class="w-full flex items-center justify-between p-3.5 bg-slate-50/50 dark:bg-zinc-950/20 text-xs font-bold text-slate-400 dark:text-zinc-550 select-none">
									<span class="flex items-center gap-2">
										<Globe class="w-4 h-4" /> Custom Domain
									</span>
									<Lock class="w-3.5 h-3.5" />
								</div>
							</div>
						</div>
					</div>

					<!-- Works content options -->
					<div>
						<h3 class="text-sm font-black uppercase tracking-wider text-slate-800 dark:text-zinc-200 mb-4 text-left">
							Works
						</h3>

						<div class="space-y-2.5 text-left">
							<!-- 1. Edit Content Form Toggle -->
							<div class="border border-slate-100 dark:border-zinc-800 rounded-xl overflow-hidden">
								<button 
									@click="activeTab = activeTab === 'content' ? null : 'content'"
									class="w-full flex items-center justify-between p-3.5 bg-slate-50/50 hover:bg-slate-50 dark:bg-zinc-950/20 dark:hover:bg-zinc-950/50 border-none outline-none text-xs font-bold text-slate-700 dark:text-zinc-300 cursor-pointer"
								>
									<span class="flex items-center gap-2">
										<Edit class="w-4 h-4 text-slate-400" /> Edit Content
									</span>
									<ChevronRight class="w-4 h-4 transform transition-transform" :class="{'rotate-90': activeTab === 'content'}" />
								</button>
								<div v-show="activeTab === 'content'" class="p-4 bg-white dark:bg-zinc-900 border-t border-slate-100 dark:border-zinc-800 space-y-4">
									<div class="space-y-1">
										<label class="text-[10px] font-black uppercase text-slate-400 block">Custom Title</label>
										<input 
											type="text" 
											v-model="customTitle"
											class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-950 text-slate-800 dark:text-white outline-none focus:border-indigo-500"
										/>
									</div>
									<div class="space-y-1">
										<label class="text-[10px] font-black uppercase text-slate-400 block">Custom Bio</label>
										<textarea 
											v-model="customBio"
											rows="3"
											class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-950 text-slate-800 dark:text-white outline-none focus:border-indigo-500 resize-none"
										></textarea>
									</div>

									<!-- Projects List selection checkboxes -->
									<div class="space-y-2 pt-2 border-t border-slate-100 dark:border-zinc-800">
										<label class="text-[10px] font-black uppercase text-slate-400 block">Featured Projects</label>
										<div class="max-h-36 overflow-y-auto space-y-1.5 pr-1">
											<div 
												v-for="p in props.projects" 
												:key="p.id"
												@click="toggleProject(p.id)"
												class="flex items-center gap-2 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-zinc-950 cursor-pointer"
											>
												<div 
													class="w-4 h-4 rounded border flex items-center justify-center shrink-0"
													:class="selectedProjectIds.includes(p.id) ? 'bg-indigo-600 border-transparent text-white' : 'border-slate-300 dark:border-zinc-700'"
												>
													<Check v-if="selectedProjectIds.includes(p.id)" class="w-2.5 h-2.5" />
												</div>
												<span class="text-[10px] font-bold truncate">{{ p.title }}</span>
											</div>
										</div>
									</div>
								</div>
							</div>

							<!-- 2. Templates and themes link -->
							<Link 
								href="/pagi/works"
								class="w-full flex items-center justify-between p-3.5 bg-slate-50/50 hover:bg-slate-50 dark:bg-zinc-950/20 dark:hover:bg-zinc-950/50 border border-slate-100 dark:border-zinc-800 rounded-xl text-xs font-bold text-slate-700 dark:text-zinc-300 cursor-pointer"
							>
								<span class="flex items-center gap-2">
									<Layout class="w-4 h-4 text-slate-400" /> Templates and Themes
								</span>
								<ChevronRight class="w-4 h-4 text-slate-300" />
							</Link>

							<!-- 3. Share copy route -->
							<div class="border border-slate-100 dark:border-zinc-800 rounded-xl overflow-hidden">
								<button 
									@click="activeTab = activeTab === 'share' ? null : 'share'"
									class="w-full flex items-center justify-between p-3.5 bg-slate-50/50 hover:bg-slate-50 dark:bg-zinc-950/20 dark:hover:bg-zinc-950/50 border-none outline-none text-xs font-bold text-slate-700 dark:text-zinc-300 cursor-pointer"
								>
									<span class="flex items-center gap-2">
										<Share2 class="w-4 h-4 text-slate-400" /> Share Links
									</span>
									<ChevronRight class="w-4 h-4 transform transition-transform" :class="{'rotate-90': activeTab === 'share'}" />
								</button>
								<div v-show="activeTab === 'share'" class="p-3.5 bg-white dark:bg-zinc-900 border-t border-slate-100 dark:border-zinc-800 space-y-3">
									<p class="text-[10px] text-slate-400 leading-relaxed">
										Copy the public link to share your custom works layout page with companies or contacts:
									</p>
									<div class="flex gap-2">
										<button 
											@click="copyShareUrl"
											class="flex-1 flex items-center justify-center gap-1.5 py-2 border rounded-xl text-xs font-extrabold cursor-pointer active:scale-95 transition-all bg-slate-50 hover:bg-slate-100 dark:bg-zinc-950 dark:hover:bg-zinc-850"
										>
											<Copy class="w-3.5 h-3.5" />
											{{ showShareCopied ? 'Copied!' : 'Copy Link' }}
										</button>
									</div>
								</div>
							</div>
						</div>
					</div>

				</div>

				<!-- Save Options footer area -->
				<div class="pt-6 border-t border-slate-100 dark:border-zinc-800 text-left">
					<p class="text-[10px] text-slate-400 leading-relaxed">
						PAGI Custom Works. Simpan perubahan untuk menerbitkan layout karya Anda secara publik.
					</p>
				</div>
			</div>

		</div>
	</div>
</template>

<style scoped>
/* Swatch visual scales */
.scale-102 {
	transform: scale(1.02) rotate(1deg);
}
</style>
