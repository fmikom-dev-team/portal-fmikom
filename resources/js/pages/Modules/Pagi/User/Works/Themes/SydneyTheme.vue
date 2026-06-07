<script setup lang="ts">
import { Award, MapPin } from "lucide-vue-next";
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
		`${props.profileUser.name} is a product designer, brand developer, and creative director shaping modern digital experiences.`,
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

const userInitials = computed(() => {
	const name = titleText.value;
	return name
		.split(" ")
		.map((n: string) => n[0])
		.join("")
		.substring(0, 2)
		.toUpperCase();
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
		:style="{ backgroundColor: activePalette.bg, color: activePalette.text }"
	>
		<div class="max-w-6xl mx-auto px-6">
			<!-- Hero Introduction -->
			<div class="flex flex-col items-center text-center mt-8 mb-16">
				<div class="w-24 h-24 rounded-full border-4 border-white/20 overflow-hidden flex items-center justify-center bg-zinc-800 shrink-0 shadow-lg print-avatar">
					<img v-if="props.profileUser.foto_path || props.profileUser.avatar" :src="userAvatar" alt="Avatar" class="w-full h-full object-cover" />
					<span v-else class="text-3xl font-black text-white">{{ userInitials }}</span>
				</div>
				
				<h1 class="text-4xl sm:text-6xl font-black tracking-tight mt-6 font-serif">
					{{ titleText }}
				</h1>
				<p class="text-xs uppercase tracking-widest font-extrabold mt-2 opacity-80" :style="{ color: activePalette.accent }">
					{{ props.profileUser.pagi_role || props.profileUser.role_title || 'Creative Creator' }}
				</p>

				<h2 class="text-xl sm:text-3xl font-bold leading-snug mt-8 max-w-3xl font-serif">
					{{ bioText }}
				</h2>

				<!-- Location -->
				<div class="flex items-center gap-1.5 mt-6 text-sm font-semibold opacity-85">
					<MapPin class="w-4 h-4" />
					<span>{{ props.profileUser.location || 'Banyumas, Indonesia' }}</span>
				</div>
			</div>

			<!-- Rotating Marquee Ticker -->
			<div 
				class="w-full py-3 overflow-hidden text-[10px] sm:text-xs font-black tracking-widest uppercase rotate-1 scale-102 rounded-xl mb-24 relative select-none shadow-md flex justify-center print-marquee animate-ticker-slow"
				:style="{ backgroundColor: activePalette.cardBg, color: activePalette.accent }"
			>
				<span class="whitespace-nowrap">
					{{ props.profileUser.skills.join(' • ') || 'Design • Code • Innovation • Creativity • Experience' }}
				</span>
			</div>

			<!-- Works Grid -->
			<div class="mb-24 print-section">
				<h3 class="text-2xl font-black uppercase tracking-wider mb-8 border-b pb-3" :style="{ borderColor: activePalette.border || 'transparent' }">
					Featured Works
				</h3>
				
				<!-- Mockup Mode -->
				<div v-if="isMockup" class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4">
					<div 
						v-for="i in 2" 
						:key="i"
						class="rounded-2xl overflow-hidden border"
						:style="{ backgroundColor: activePalette.cardBg, borderColor: activePalette.accent + '22' }"
					>
						<div class="aspect-video w-full bg-slate-800/40 relative">
							<div class="absolute inset-0 flex items-center justify-center text-[10px] opacity-45">PROJECT SHAPE</div>
						</div>
						<div class="p-5">
							<h4 class="text-sm font-bold font-serif leading-snug">Selected Project Title {{ i }}</h4>
							<p class="text-[10px] opacity-75 mt-1">Brief summary of the creative approach and execution.</p>
						</div>
					</div>
				</div>

				<!-- Live Mode -->
				<div v-else>
					<div v-if="props.projects.length === 0" class="text-center py-12 opacity-75 italic text-sm">
						Belum ada karya yang diunggah.
					</div>

					<div v-else class="grid grid-cols-1 md:grid-cols-2 gap-8 print-grid">
						<div 
							v-for="proj in props.projects" 
							:key="proj.id"
							@click="handleProjectClick(proj)"
							class="group rounded-3xl overflow-hidden cursor-pointer shadow-sm hover:shadow-xl transition-all duration-300 border flex flex-col justify-between print-card"
							:style="{ backgroundColor: activePalette.cardBg, borderColor: activePalette.border || 'transparent' }"
						>
							<div class="aspect-video w-full overflow-hidden bg-slate-900 relative print-card-image">
								<img :src="proj.image" :alt="proj.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
								<div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center no-print" v-if="isInteractive">
									<span class="px-5 py-2 rounded-full bg-white text-black text-xs font-bold shadow-md transform translate-y-2 group-hover:translate-y-0 transition-transform">
										View Case Study
									</span>
								</div>
							</div>
							<div class="p-6">
								<h4 class="text-lg font-bold group-hover:underline font-serif leading-snug">
									{{ proj.title }}
								</h4>
								<p class="text-xs mt-2 line-clamp-2 opacity-80 leading-relaxed">
									{{ proj.description }}
								</p>
								<div class="flex flex-wrap gap-1.5 mt-4">
									<span 
										v-for="t in proj.tags.slice(0, 3)" 
										:key="t"
										class="px-2 py-0.5 rounded text-[10px] font-semibold"
										:class="activePalette.badge || 'bg-white/10 text-white'"
									>
										{{ t }}
									</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Certificates & Credentials (only visible when not mockup) -->
			<div class="mb-16 print-section page-break-before-print" v-if="!isMockup && props.profileUser.certificates && props.profileUser.certificates.length > 0">
				<h3 class="text-2xl font-black uppercase tracking-wider mb-8 border-b pb-3" :style="{ borderColor: activePalette.border || 'transparent' }">
					Certificates
				</h3>

				<div class="grid grid-cols-1 md:grid-cols-2 gap-6 print-grid">
					<div 
						v-for="cert in props.profileUser.certificates" 
						:key="cert.id"
						class="p-6 rounded-2xl border flex items-start gap-4 transition-transform hover:-translate-y-1 duration-200 print-card"
						:style="{ backgroundColor: activePalette.cardBg, borderColor: activePalette.border || 'transparent' }"
					>
						<div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center shrink-0">
							<Award class="w-6 h-6" :style="{ color: activePalette.accent }" />
						</div>
						<div class="space-y-1">
							<h4 class="text-sm font-bold leading-snug font-serif">{{ cert.title }}</h4>
							<p class="text-xs opacity-75 font-semibold">{{ cert.issuer }} • {{ cert.date }}</p>
							<p class="text-[10px] opacity-60 font-mono">Credential: {{ cert.credentialId }}</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<style scoped>
.scale-102 {
	transform: scale(1.025) rotate(1.5deg);
}
</style>
