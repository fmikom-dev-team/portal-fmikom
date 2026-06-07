<script setup lang="ts">
import { Award, Eye, Heart, MessageSquare, Sparkles } from "lucide-vue-next";
import { computed } from "vue";

const props = withDefaults(
	defineProps<{
		profileUser: any;
		projects: any[];
		activePalette: {
			bg: string;
			text: string;
			accent: string;
			cardBg?: string;
			border?: string;
			badge?: string;
		};
		customTitle?: string;
		customBio?: string;
		isMockup?: boolean;
		isInteractive?: boolean;
	}>(),
	{
		customTitle: "",
		customBio: "",
		isMockup: false,
		isInteractive: false,
	},
);

const emit = defineEmits<(e: "openProject", project: any) => void>();

const titleText = computed(() => props.customTitle || props.profileUser.name);
const bioText = computed(
	() =>
		props.customBio ||
		props.profileUser.bio ||
		"Vibrant creative thinking and product design frameworks combined into visual aesthetics.",
);

const userAvatar = computed(() => {
	if (props.profileUser.foto_path) {
		return props.profileUser.foto_path.startsWith("http")
			? props.profileUser.foto_path
			: `/storage/${props.profileUser.foto_path}`;
	}
	return (
		"https://api.dicebear.com/7.x/initials/svg?seed=" +
		encodeURIComponent(props.profileUser.name) +
		"&backgroundColor=3b82f6,6366f1,8b5cf6,ec4899,f43f5e&backgroundType=gradientLinear&bold=true"
	);
});

const handleProjectClick = (proj: any) => {
	if (props.isInteractive) {
		emit("openProject", proj);
	}
};
</script>

<template>
	<div 
		class="py-16 md:py-24 theme-section text-left"
		:style="{ background: activePalette.bg, color: activePalette.text }"
	>
		<div class="max-w-6xl mx-auto px-6">
			<!-- Glassmorphic Header Card -->
			<div 
				class="mt-8 rounded-3xl p-8 md:p-12 border backdrop-blur-xl shadow-xl flex flex-col md:flex-row items-center gap-8 md:gap-12"
				:style="{ backgroundColor: activePalette.cardBg, borderColor: activePalette.border || 'transparent' }"
			>
				<div class="w-28 h-28 rounded-3xl overflow-hidden shrink-0 shadow-lg border border-white/40 flex items-center justify-center print-avatar">
					<img :src="userAvatar" :alt="props.profileUser.name" class="w-full h-full object-cover" />
				</div>
				
				<div class="flex-1 text-center md:text-left space-y-4">
					<div>
						<h1 class="text-3xl sm:text-5xl font-black uppercase tracking-tighter leading-none">
							{{ titleText }}
						</h1>
						<p class="text-sm font-extrabold tracking-widest uppercase opacity-75 mt-1.5 flex items-center justify-center md:justify-start gap-1">
							<Sparkles class="w-4 h-4 fill-current" />
							{{ props.profileUser.pagi_role || 'Design Innovator' }}
						</p>
					</div>

					<p class="text-base opacity-90 max-w-2xl leading-relaxed">
						{{ bioText }}
					</p>

					<!-- Skills Grid -->
					<div class="flex flex-wrap gap-1.5 justify-center md:justify-start">
						<span 
							v-for="skill in props.profileUser.skills" 
							:key="skill"
							class="px-2.5 py-0.5 rounded-full text-[9px] font-black tracking-wider border uppercase bg-white/15 backdrop-blur-sm"
							:style="{ borderColor: activePalette.border || 'transparent' }"
						>
							{{ skill }}
						</span>
					</div>
				</div>
			</div>

			<!-- Projects Grid -->
			<div class="mt-20 mb-20 print-section">
				<h3 class="text-xl font-black uppercase tracking-wider mb-8 flex items-center gap-2">
					<span>🎨 Projects Feed</span>
				</h3>

				<!-- Mockup Mode -->
				<div v-if="isMockup" class="grid grid-cols-2 gap-4">
					<div 
						v-for="i in 2" 
						:key="i" 
						class="rounded-2xl p-4 border backdrop-blur-md"
						:style="{ backgroundColor: activePalette.cardBg, borderColor: activePalette.border || 'transparent' }"
					>
						<div class="aspect-video bg-white/10 rounded-lg flex items-center justify-center text-[8px]">PROJECT COVER</div>
						<h4 class="text-xs font-bold mt-2">Vibrant System Design {{ i }}</h4>
					</div>
				</div>

				<!-- Live Mode -->
				<div v-else>
					<div v-if="props.projects.length === 0" class="text-center py-12 glassmorphic rounded-2xl italic opacity-60 text-xs">
						Belum ada karya yang diunggah.
					</div>

					<div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 print-grid">
						<div 
							v-for="proj in props.projects" 
							:key="proj.id"
							@click="handleProjectClick(proj)"
							class="group rounded-3xl overflow-hidden cursor-pointer shadow-md hover:shadow-xl transition-all duration-300 border backdrop-blur-lg flex flex-col justify-between print-card"
							:style="{ backgroundColor: activePalette.cardBg, borderColor: activePalette.border || 'transparent' }"
						>
							<div class="aspect-video w-full overflow-hidden relative print-card-image">
								<img :src="proj.image" :alt="proj.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
							</div>
							<div class="p-6 text-left">
								<span class="text-[9px] font-black uppercase tracking-widest opacity-60">{{ proj.category }}</span>
								<h4 class="text-base font-extrabold mt-1 group-hover:underline leading-snug">
									{{ proj.title }}
								</h4>
								<div class="flex items-center gap-3 mt-4 text-[10px] opacity-75 font-semibold no-print" v-if="isInteractive">
									<span class="flex items-center gap-0.5"><Heart class="w-3.5 h-3.5" /> {{ proj.likes }}</span>
									<span class="flex items-center gap-0.5"><MessageSquare class="w-3.5 h-3.5" /> {{ proj.comments.length }}</span>
									<span class="flex items-center gap-0.5"><Eye class="w-3.5 h-3.5" /> {{ proj.views }}</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Certificates Section (only visible when not mockup) -->
			<div class="mb-16 print-section page-break-before-print" v-if="!isMockup && props.profileUser.certificates && props.profileUser.certificates.length > 0">
				<h3 class="text-xl font-black uppercase tracking-wider mb-8">
					<span>🏆 Verified Achievements</span>
				</h3>

				<div class="grid grid-cols-1 md:grid-cols-2 gap-6 print-grid">
					<div 
						v-for="cert in props.profileUser.certificates" 
						:key="cert.id"
						class="p-6 rounded-2xl border backdrop-blur-md flex items-center justify-between print-card"
						:style="{ backgroundColor: activePalette.cardBg, borderColor: activePalette.border || 'transparent' }"
					>
						<div class="flex items-center gap-4 text-left">
							<div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
								<Award class="w-5 h-5 text-white" />
							</div>
							<div>
								<h4 class="text-sm font-extrabold leading-snug">{{ cert.title }}</h4>
								<p class="text-xs opacity-75">{{ cert.issuer }} • {{ cert.date }}</p>
							</div>
						</div>
						<div class="text-[9px] font-black tracking-wider uppercase bg-white/10 px-2 py-0.5 rounded shrink-0">
							VERIFIED
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
