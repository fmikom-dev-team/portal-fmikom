<script setup lang="ts">
import { Head, Link, usePage } from "@inertiajs/vue3";
import { Edit3, Palette } from "lucide-vue-next";
import { computed, ref } from "vue";
import Footer from "../ui/Footer.vue";
import Navbar from "../ui/Navbar.vue";
import Preview from "../ui/Preview.vue";

interface ProfileUser {
	id: number;
	name: string;
	email: string;
	pagi_username: string;
	role_title: string;
	pagi_role: string;
	user_type: string;
	bio: string;
	location: string;
	website: string;
	twitter: string;
	linkedin: string;
	github: string;
	instagram: string;
	foto_path: string;
	banner_path: string;
	tanggal_lahir: string;
	skills: string[];
	timezone: string;
	timezone_extended: string;
	languages: string[];
	followers_count: number;
	following_count: number;
	certificates: Array<{
		id: number;
		title: string;
		issuer: string;
		date: string;
		credentialId: string;
		logo?: string;
	}>;
}

interface Project {
	id: number;
	user_id: number;
	title: string;
	image: string;
	content: any;
	created_at: string;
	likes: number;
	liked: boolean;
	comments: any[];
	views: number;
	is_published: boolean;
	tools_used: string[];
	description: string;
	category: string;
	tags: string[];
	resolved_collaborators: any[];
	user: {
		id: number;
		name: string;
		pagi_username: string;
		avatar: string;
		location: string;
	};
}

const props = defineProps<{
	moduleName: string;
	roleName: string;
	profileUser: ProfileUser;
	projects: Project[];
	isFollowing: boolean;
	selectedTheme: string;
	selectedPalette: number;
}>();

// Auth details to check if the current viewer is the owner
const page = usePage();
const isOwner = computed(() => {
	return (page.props as any).auth?.user?.id === props.profileUser.id;
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
	return themeComponents[props.selectedTheme] || SydneyTheme;
});

// Modal state for project details/case studies
const viewingProject = ref<Project | null>(null);

const openProjectModal = (proj: Project) => {
	viewingProject.value = proj;
};

// Theme configurations
const themesPalette = {
	sydney: [
		{
			bg: "#4A1521",
			text: "#FFFFFF",
			accent: "#FFE8E8",
			cardBg: "#330D14",
			border: "#6b2031",
			badge: "bg-red-950/40 text-red-200",
		},
		{
			bg: "#ECE6DB",
			text: "#1E1E1E",
			accent: "#E53E3E",
			cardBg: "#E3DCD1",
			border: "#d5cbb8",
			badge: "bg-stone-200 text-stone-800",
		},
		{
			bg: "#F7F4A5",
			text: "#2C1B4D",
			accent: "#2C1B4D",
			cardBg: "#EAE68C",
			border: "#d7d268",
			badge: "bg-yellow-100 text-yellow-900",
		},
		{
			bg: "#B3C1CD",
			text: "#1D2D44",
			accent: "#E53E3E",
			cardBg: "#9EB0BE",
			border: "#8fa2b1",
			badge: "bg-slate-200 text-slate-800",
		},
	],
	"cape-town": [
		{
			bg: "#FAF7F2",
			text: "#967A50",
			accent: "linear-gradient(135deg, #6366f1, #a855f7)",
			cardBg: "#F3EEE3",
			border: "#EBE3D5",
			badge: "bg-amber-100 text-amber-800",
		},
		{
			bg: "#F3E8FF",
			text: "#5B21B6",
			accent: "linear-gradient(135deg, #ec4899, #f43f5e)",
			cardBg: "#E9D5FF",
			border: "#D8B4FE",
			badge: "bg-purple-200 text-purple-900",
		},
		{
			bg: "#45543C",
			text: "#D1FAE5",
			accent: "linear-gradient(135deg, #f59e0b, #eab308)",
			cardBg: "#374330",
			border: "#2E3B28",
			badge: "bg-emerald-950/40 text-emerald-200",
		},
		{
			bg: "#121212",
			text: "#E5E7EB",
			accent: "linear-gradient(135deg, #06b6d4, #3b82f6)",
			cardBg: "#1F1F1F",
			border: "#2E2E2E",
			badge: "bg-zinc-800 text-zinc-300",
		},
	],
	barcelona: [
		{
			bg: "linear-gradient(135deg, #fbcfe8, #fca5a5, #fcd34d)",
			text: "#2c1518",
			accent: "#ec4899",
			cardBg: "rgba(255,255,255,0.4)",
			border: "rgba(255,255,255,0.6)",
			badge: "bg-white/50 text-pink-900",
		},
		{
			bg: "#0F172A",
			text: "#E2E8F0",
			accent: "#10B981",
			cardBg: "#1E293B",
			border: "#334155",
			badge: "bg-slate-800 text-emerald-400",
		},
		{
			bg: "#D1FAE5",
			text: "#065F46",
			accent: "#059669",
			cardBg: "#A7F3D0",
			border: "#6EE7B7",
			badge: "bg-emerald-200/60 text-emerald-900",
		},
		{
			bg: "#FCE7F3",
			text: "#9D174D",
			accent: "#DB2777",
			cardBg: "#FBCFE8",
			border: "#F9A8D4",
			badge: "bg-pink-200/60 text-pink-900",
		},
	],
	london: [
		{
			bg: "#FFFFFF",
			text: "#0F172A",
			accent: "#4F46E5",
			cardBg: "#F8FAFC",
			border: "#F1F5F9",
			badge: "bg-indigo-50 text-indigo-700",
		},
		{
			bg: "#FFF5F5",
			text: "#9B2C2C",
			accent: "#E53E3E",
			cardBg: "#FEE2E2",
			border: "#FCA5A5",
			badge: "bg-red-100 text-red-800",
		},
		{
			bg: "#F1F5F9",
			text: "#334155",
			accent: "#0F172A",
			cardBg: "#E2E8F0",
			border: "#CBD5E1",
			badge: "bg-slate-200 text-slate-800",
		},
		{
			bg: "#0B132B",
			text: "#E2E8F0",
			accent: "#6366F1",
			cardBg: "#1C2541",
			border: "#3A0CA3",
			badge: "bg-indigo-950/40 text-indigo-300",
		},
	],
	tokyo: [
		{
			bg: "#FAF9F6",
			text: "#111111",
			accent: "#E53E3E",
			cardBg: "#FFFFFF",
			border: "#111111",
			badge: "border border-black bg-transparent text-black font-mono",
		},
		{
			bg: "#111111",
			text: "#FAF9F6",
			accent: "#38BDF8",
			cardBg: "#1A1A1A",
			border: "#333333",
			badge: "border border-zinc-700 bg-transparent text-zinc-300 font-mono",
		},
		{
			bg: "#FDF0D5",
			text: "#003049",
			accent: "#C1121F",
			cardBg: "#FFFDF9",
			border: "#003049",
			badge: "border border-sky-900 bg-transparent text-sky-950 font-mono",
		},
		{
			bg: "#0D1B2A",
			text: "#E0E1DD",
			accent: "#F59E0B",
			cardBg: "#1B263B",
			border: "#415A77",
			badge: "border border-slate-650 bg-transparent text-slate-300 font-mono",
		},
	],
	paris: [
		{
			bg: "#FBFBFA",
			text: "#1E2522",
			accent: "#FF5E3A",
			cardBg: "#FAF9F5",
			border: "#EAE6DF",
			badge: "bg-[#F3EFE9] text-[#554C3E]",
		},
		{
			bg: "#F3FDE8",
			text: "#2C3E20",
			accent: "#E83E8C",
			cardBg: "#E8F5E9",
			border: "#C8E6C9",
			badge: "bg-emerald-100 text-emerald-800",
		},
		{
			bg: "#E8F1F2",
			text: "#132E32",
			accent: "#8A2BE2",
			cardBg: "#D1E8E2",
			border: "#A7D7C5",
			badge: "bg-[#a9ded9] text-teal-950",
		},
		{
			bg: "#1C1C1C",
			text: "#ECEBE4",
			accent: "#FFD700",
			cardBg: "#2A2A2A",
			border: "#3A3A3A",
			badge: "bg-zinc-800 text-yellow-450",
		},
	],
	"new-york": [
		{
			bg: "#0B0F19",
			text: "#F3F4F6",
			accent: "#06B6D4",
			cardBg: "rgba(17, 24, 39, 0.7)",
			border: "rgba(6, 182, 212, 0.15)",
			badge: "bg-cyan-950/40 text-cyan-250 border border-cyan-500/20",
		},
		{
			bg: "#090714",
			text: "#F3F4F6",
			accent: "#8B5CF6",
			cardBg: "rgba(21, 16, 36, 0.7)",
			border: "rgba(139, 92, 246, 0.15)",
			badge: "bg-purple-950/40 text-purple-250 border border-purple-500/20",
		},
		{
			bg: "#050B0D",
			text: "#F3F4F6",
			accent: "#10B981",
			cardBg: "rgba(12, 28, 30, 0.7)",
			border: "rgba(16, 185, 129, 0.15)",
			badge: "bg-emerald-950/40 text-emerald-250 border border-emerald-500/20",
		},
		{
			bg: "#0A0A0A",
			text: "#F3F4F6",
			accent: "#F3F4F6",
			cardBg: "rgba(20, 20, 20, 0.7)",
			border: "rgba(255, 255, 255, 0.15)",
			badge: "bg-zinc-900 text-zinc-100 border border-zinc-750",
		},
	],
};

const activePalette = computed(() => {
	const themeKey = props.selectedTheme as keyof typeof themesPalette;
	const paletteList = themesPalette[themeKey] || themesPalette.sydney;
	return paletteList[props.selectedPalette] || paletteList[0];
});
</script>

<template>
	<Head>
		<title>{{ props.profileUser.name }} — Works</title>
	</Head>

	<div class="min-h-screen relative font-sans transition-colors duration-300">
		<Navbar />

		<!-- Floating Glassmorphic Owner Control Bar -->
		<div v-if="isOwner" class="fixed top-20 left-1/2 -translate-x-1/2 z-40 w-[90%] max-w-lg">
			<div class="flex items-center justify-between gap-4 px-5 py-2.5 bg-white/85 dark:bg-zinc-950/85 backdrop-blur-md rounded-full border border-slate-200/80 dark:border-zinc-800/80 shadow-lg">
				<div class="flex items-center gap-2">
					<div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
					<span class="text-[10px] font-black text-slate-800 dark:text-zinc-200 uppercase tracking-widest">Tema Aktif: {{ props.selectedTheme }}</span>
				</div>
				<div class="flex items-center gap-1.5">
					<Link 
						href="/pagi/works" 
						class="inline-flex items-center gap-1 bg-slate-900 hover:bg-slate-800 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-white rounded-full px-3.5 py-1.5 text-[10px] font-extrabold transition-all active:scale-95 border-none cursor-pointer"
					>
						<Palette class="w-3.5 h-3.5" /> Ganti Layout
					</Link>
					<a 
						:href="'/pagi/' + props.profileUser.pagi_username + '?edit=true'" 
						class="inline-flex items-center gap-1 bg-white hover:bg-slate-50 dark:bg-zinc-900 dark:hover:bg-zinc-850 text-slate-800 dark:text-zinc-200 border border-slate-200 dark:border-zinc-800 rounded-full px-3.5 py-1.5 text-[10px] font-extrabold transition-all active:scale-95 cursor-pointer"
					>
						<Edit3 class="w-3.5 h-3.5" /> Edit Data
					</a>
				</div>
			</div>
		</div>
		<!-- DYNAMIC LAYOUT THEME -->
		<component 
			:is="themeComponent" 
			:profile-user="props.profileUser" 
			:projects="props.projects" 
			:active-palette="activePalette" 
			:is-interactive="true"
			@open-project="openProjectModal" 
		/>

		<Footer />

		<!-- Project Detail Modal -->
		<Preview
			v-if="viewingProject"
			:title="viewingProject.title"
			:content="viewingProject.content"
			:cover-image="viewingProject.image"
			:portfolio="viewingProject"
			:description="viewingProject.description"
			:category="viewingProject.category"
			:tools-used="Array.isArray(viewingProject.tools_used) ? viewingProject.tools_used.join(', ') : (typeof viewingProject.tools_used === 'string' ? viewingProject.tools_used : '')"
			:tags="viewingProject.tags"
			@close="viewingProject = null"
			@select-portfolio="viewingProject = $event"
		/>
	</div>
</template>

<style scoped>
/* Inline rotation support scales */
.scale-102 {
	transform: scale(1.025) rotate(1.5deg);
}
</style>
