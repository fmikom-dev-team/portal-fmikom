<script setup lang="ts">
import { ArrowRight, Award, Globe, MapPin } from "lucide-vue-next";
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
		"Crafting minimal interfaces and visual systems for modern digital products.",
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
		:style="{ backgroundColor: activePalette.bg, color: activePalette.text }"
	>
		<div class="max-w-5xl mx-auto px-6">
			<!-- Minimal Gold Header -->
			<div class="flex flex-col md:flex-row items-center justify-between gap-8 mt-8 border-b pb-12 mb-16" :style="{ borderColor: activePalette.border || 'transparent' }">
				<div class="text-center md:text-left space-y-3 flex-1">
					<div class="flex justify-center md:justify-start items-center gap-2">
						<span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider text-white" :style="{ background: activePalette.accent }">
							{{ props.profileUser.pagi_role || 'Creator' }}
						</span>
					</div>
					<h1 class="text-3xl sm:text-5xl font-black tracking-tight" style="font-family: Georgia, serif;">
						{{ titleText }}
					</h1>
					<p class="text-base max-w-xl opacity-90 leading-relaxed">
						{{ bioText }}
					</p>
					
					<!-- Location & Links -->
					<div class="flex flex-wrap gap-x-4 gap-y-1 text-xs font-semibold opacity-75 pt-2">
						<span class="flex items-center gap-1"><MapPin class="w-3.5 h-3.5" /> {{ props.profileUser.location || 'Banyumas, Indonesia' }}</span>
						<span class="flex items-center gap-1"><Globe class="w-3.5 h-3.5" /> {{ props.profileUser.pagi_username }}</span>
					</div>
				</div>

				<!-- Circle Profile Photo -->
				<div class="w-32 h-32 rounded-full overflow-hidden shrink-0 border border-slate-200 shadow-md print-avatar">
					<img :src="userAvatar" :alt="props.profileUser.name" class="w-full h-full object-cover" />
				</div>
			</div>

			<!-- Skills Badge Strip -->
			<div class="mb-16">
				<h3 class="text-xs uppercase font-black tracking-widest opacity-60 mb-4">Domain Skills</h3>
				<div class="flex flex-wrap gap-2">
					<span 
						v-for="skill in props.profileUser.skills" 
						:key="skill"
						class="px-4 py-1.5 rounded-full text-xs font-extrabold shadow-2xs"
						:style="{ backgroundColor: activePalette.cardBg, border: '1px solid ' + (activePalette.border || 'transparent') }"
					>
						{{ skill }}
					</span>
				</div>
			</div>

			<!-- Works masonry grid -->
			<div class="mb-20 print-section">
				<div class="flex justify-between items-end mb-8 border-b pb-2" :style="{ borderColor: activePalette.border || 'transparent' }">
					<h3 class="text-lg font-black uppercase tracking-widest opacity-80" style="font-family: Georgia, serif;">Selected Works</h3>
					<span v-if="!isMockup" class="text-xs font-mono opacity-65 no-print">Total: {{ props.projects.length }}</span>
				</div>

				<!-- Mockup Mode -->
				<div v-if="isMockup" class="grid grid-cols-2 gap-4 pt-4">
					<div 
						v-for="i in 2" 
						:key="i" 
						class="rounded-2xl p-4 border"
						:style="{ backgroundColor: activePalette.cardBg, borderColor: activePalette.accent + '22' }"
					>
						<div class="h-24 rounded-xl opacity-80" :style="{ background: activePalette.accent }"></div>
						<h4 class="text-xs font-bold font-serif mt-3">Visual Brand Interface {{ i }}</h4>
						<span class="text-[9px] font-mono opacity-50 uppercase">Case Study</span>
					</div>
				</div>

				<!-- Live Mode -->
				<div v-else>
					<div v-if="props.projects.length === 0" class="text-center py-12 italic opacity-60 text-xs">
						Belum ada karya yang diunggah.
					</div>

					<div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6 print-grid">
						<div 
							v-for="proj in props.projects" 
							:key="proj.id"
							@click="handleProjectClick(proj)"
							class="group bg-white rounded-2xl overflow-hidden cursor-pointer border hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between print-card"
							:style="{ backgroundColor: activePalette.cardBg, borderColor: activePalette.border || 'transparent' }"
						>
							<div class="aspect-video w-full overflow-hidden bg-stone-100 relative print-card-image">
								<img :src="proj.image" :alt="proj.title" class="w-full h-full object-cover group-hover:scale-102 transition-transform duration-300" />
							</div>
							<div class="p-6">
								<span class="text-[9px] font-black uppercase tracking-wider opacity-60">{{ proj.category }}</span>
								<h4 class="text-base font-bold group-hover:underline mt-1 leading-snug" style="font-family: Georgia, serif;">
									{{ proj.title }}
								</h4>
								<p class="text-xs mt-2 line-clamp-2 opacity-80 leading-relaxed font-sans">
									{{ proj.description }}
								</p>
								<div class="flex items-center gap-1 mt-4 text-[10px] font-black no-print" v-if="isInteractive" :style="{ color: activePalette.accent.includes('gradient') ? '#6366f1' : activePalette.text }">
									<span>Read case study</span>
									<ArrowRight class="w-3 h-3" />
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Certificates Section (only visible when not mockup) -->
			<div class="mb-16 print-section page-break-before-print" v-if="!isMockup && props.profileUser.certificates && props.profileUser.certificates.length > 0">
				<div class="flex justify-between items-end mb-8 border-b pb-2" :style="{ borderColor: activePalette.border || 'transparent' }">
					<h3 class="text-lg font-black uppercase tracking-widest opacity-80" style="font-family: Georgia, serif;">Certifications</h3>
				</div>

				<div class="space-y-4">
					<div 
						v-for="cert in props.profileUser.certificates" 
						:key="cert.id"
						class="p-5 rounded-2xl border flex items-center justify-between gap-4 print-card"
						:style="{ backgroundColor: activePalette.cardBg, borderColor: activePalette.border || 'transparent' }"
					>
						<div class="flex items-center gap-4">
							<div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0" :style="{ background: activePalette.border || 'rgba(0,0,0,0.05)' }">
								<Award class="w-5 h-5 opacity-80" />
							</div>
							<div class="text-left">
								<h4 class="text-sm font-bold font-serif leading-tight">{{ cert.title }}</h4>
								<p class="text-xs opacity-70 font-semibold">{{ cert.issuer }} • {{ cert.date }}</p>
							</div>
						</div>
						<span class="text-[9px] font-mono px-2 py-1 bg-black/5 dark:bg-white/5 rounded border border-black/10 dark:border-white/10 shrink-0">
							{{ cert.credentialId }}
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
