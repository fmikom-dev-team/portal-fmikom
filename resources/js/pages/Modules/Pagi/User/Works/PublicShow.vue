<script setup lang="ts">
import { Head, Link, usePage } from "@inertiajs/vue3";
import {
	ArrowLeft,
	ArrowRight,
	Award,
	Briefcase,
	Check,
	Edit3,
	ExternalLink,
	Eye,
	Github,
	Globe,
	Heart,
	Instagram,
	Linkedin,
	MapPin,
	Menu,
	MessageSquare,
	Palette,
	Printer,
	Sparkles,
	Star,
	Twitter,
} from "lucide-vue-next";
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
	profileUser: ProfileUser;
	projects: Project[];
	customWork: any;
	selectedTheme: string;
	selectedPalette: number;
	roleName: string;
}>();

// Auth details to check if the current viewer is the owner
const page = usePage();
const isOwner = computed(() => {
	return page.props.auth?.user?.id === props.profileUser.id;
});

// Custom title & bio computed from custom portfolio overrides
const customTitle = computed(
	() => props.customWork?.custom_title || props.profileUser.name,
);
const customBio = computed(
	() =>
		props.customWork?.custom_bio ||
		props.profileUser.bio ||
		"Creative Product Designer and Web Developer.",
);

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
			badge: "bg-stone-200 text-stone-850",
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

// Trigger print
const handlePrint = () => {
	window.print();
};
</script>

<template>
	<Head>
        <title>{{ customTitle + ' — Works' }}</title>
    </Head>

	<div class="min-h-screen relative font-sans transition-colors duration-300 flex flex-col">
		
		<!-- STANDALONE ACTION BAR (NO-PRINT) -->
		<div class="no-print bg-slate-900 text-white py-3 px-6 flex items-center justify-between shadow-md relative z-50">
			<div class="flex items-center gap-3">
				<Link href="/pagi" class="text-xs font-bold text-slate-300 hover:text-white flex items-center gap-1 cursor-pointer">
					<ArrowLeft class="w-3.5 h-3.5" /> Kembali ke Portal
				</Link>
				<span class="text-slate-600">|</span>
				<span class="text-xs font-semibold text-slate-400">Halaman Works Publik: @{{ props.profileUser.pagi_username }}</span>
			</div>
			
			<div class="flex items-center gap-2">
				<Link 
					v-if="isOwner" 
					:href="'/pagi/works/editor/' + props.selectedTheme"
					class="inline-flex items-center gap-1 bg-zinc-800 hover:bg-zinc-700 text-white rounded-lg px-3 py-1.5 text-xs font-bold transition-all cursor-pointer border border-zinc-700"
				>
					<Edit3 class="w-3.5 h-3.5" /> Ubah Layout Works
				</Link>
				<button 
					@click="handlePrint"
					class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg px-4 py-1.5 text-xs font-bold transition-all cursor-pointer border-none shadow-sm"
				>
					<Printer class="w-3.5 h-3.5" /> Cetak / Unduh PDF
				</button>
			</div>
		</div>

		<!-- Main Platform Navbar (hidden in print) -->
		<Navbar class="no-print" />

		<!-- THEME CONTAINER (this is what gets formatted & printed) -->
		<div class="flex-1 print-container">
			<!-- DYNAMIC LAYOUT THEME -->
			<component 
				:is="themeComponent" 
				:profile-user="props.profileUser" 
				:projects="props.projects" 
				:active-palette="activePalette" 
				:custom-title="customTitle"
				:custom-bio="customBio"
				:is-interactive="true"
				@open-project="openProjectModal" 
			/>
		</div>

		<!-- Footer (hidden in print) -->
		<Footer class="no-print" />

		<!-- Project Detail Modal (hidden in print) -->
		<Preview
			v-if="viewingProject"
			:title="viewingProject.title"
			:content="viewingProject.content"
			:cover-image="viewingProject.image"
			:portfolio="viewingProject"
			:description="viewingProject.description"
			:category="viewingProject.category"
			:tools-used="viewingProject.tools_used"
			:tags="viewingProject.tags"
			class="no-print"
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

/* Print CSS Stylesheets */
@media print {
	/* Hide elements that shouldn't appear on paper */
	.no-print,
	nav,
	header,
	footer,
	.navbar-container,
	.footer-container {
		display: none !important;
	}

	body, html, .min-h-screen, .print-container, .theme-section {
		background: #ffffff !important;
		color: #000000 !important;
		margin: 0 !important;
		padding: 0 !important;
		width: 100% !important;
	}

	/* Adjust text colors for readability */
	h1, h2, h3, h4, span, p, div, a {
		color: #000000 !important;
		text-shadow: none !important;
	}

	/* Ensure images print correctly and are scaled */
	img {
		max-width: 100% !important;
		page-break-inside: avoid !important;
	}

	/* Force printing background colors and graphics in browser settings */
	* {
		-webkit-print-color-adjust: exact !important;
		print-color-adjust: exact !important;
	}

	/* Layout structure for A4 print */
	.max-w-6xl, .max-w-5xl, .max-w-4xl {
		max-width: 100% !important;
		width: 100% !important;
		margin: 0 !important;
		padding: 20px !important;
	}

	/* Grid styling override to prevent single-column layout stretching */
	.print-grid {
		display: grid !important;
		grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
		gap: 1.5rem !important;
	}

	/* Prevent cards from breaking midway across pages */
	.print-card {
		page-break-inside: avoid !important;
		background: #fafafa !important;
		border: 1px solid #e5e7eb !important;
		box-shadow: none !important;
	}

	.print-card-image {
		page-break-inside: avoid !important;
	}

	/* Control page breaks logically */
	.print-section {
		page-break-inside: avoid !important;
		margin-bottom: 2rem !important;
	}

	.page-break-before-print {
		page-break-before: always !important;
	}

	/* Sydney theme ticker adjustment */
	.print-marquee {
		transform: none !important;
		margin-bottom: 2rem !important;
		background: #f3f4f6 !important;
		border: 1px solid #d1d5db !important;
	}

	/* London layout override for neat printing side-by-side */
	.print-london-layout {
		display: grid !important;
		grid-template-columns: repeat(12, minmax(0, 1fr)) !important;
		gap: 2rem !important;
	}

	.print-london-sidebar {
		grid-column: span 4 / span 4 !important;
	}

	.print-london-main {
		grid-column: span 8 / span 8 !important;
	}

	.print-avatar {
		border-color: #d1d5db !important;
		box-shadow: none !important;
	}
}
</style>
