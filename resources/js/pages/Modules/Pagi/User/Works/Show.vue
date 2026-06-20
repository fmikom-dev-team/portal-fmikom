<script setup lang="ts">
import { Head, Link, usePage } from "@inertiajs/vue3";
import { Edit3, Palette } from "lucide-vue-next";
import { computed } from "vue";
import Footer from "../ui/Footer.vue";
import Navbar from "../ui/Navbar.vue";
import UmumNavbar from "../ui/UmumNavbar.vue";
import WorkViewer from "./components/WorkViewer.vue";

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

const isMahasiswa = computed(() => {
	const role =
		props.roleName || (page.props as any).context?.active_role || "mahasiswa";
	return role.toLowerCase() === "mahasiswa";
});
</script>

<template>
	<Head>
		<title>{{ props.profileUser.name }} — Works</title>
	</Head>

	<div class="min-h-screen relative font-sans transition-colors duration-300">
		<Navbar v-if="isMahasiswa" />
		<UmumNavbar v-else :roleName="props.roleName" />

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
		<WorkViewer
			:profile-user="props.profileUser"
			:projects="props.projects"
			:selected-theme="props.selectedTheme"
			:selected-palette="props.selectedPalette"
		/>

		<Footer />
	</div>
</template>

<style scoped>
/* Inline rotation support scales */
.scale-102 {
	transform: scale(1.025) rotate(1.5deg);
}
</style>
