<script setup lang="ts">
import { ArrowRight, Award, Briefcase, MapPin } from "lucide-vue-next";
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
		"Creative developer creating highly interactive frontend applications and websites.",
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
		<div class="max-w-6xl mx-auto px-6">
			<div class="grid grid-cols-1 md:grid-cols-12 gap-12 mt-8 print-london-layout">
				<!-- Sticky Left Sidebar -->
				<div class="md:col-span-4 space-y-6 md:sticky md:top-24 h-fit print-london-sidebar">
					<div 
						class="rounded-3xl p-6 border flex flex-col items-center text-center space-y-4 shadow-sm"
						:style="{ backgroundColor: activePalette.cardBg, borderColor: activePalette.border || 'transparent' }"
					>
						<div class="w-24 h-24 rounded-full overflow-hidden shrink-0 border shadow-xs print-avatar">
							<img :src="userAvatar" :alt="props.profileUser.name" class="w-full h-full object-cover" />
						</div>
						
						<div class="space-y-1">
							<h2 class="text-xl font-extrabold tracking-tight" style="font-family: Georgia, serif;">
								{{ titleText }}
							</h2>
							<p class="text-[10px] opacity-75 font-mono">@{{ props.profileUser.pagi_username }}</p>
						</div>

						<!-- Available for Work indicator -->
						<div class="inline-flex items-center gap-1.5 bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider">
							<span class="w-2 h-2 rounded-full bg-emerald-500"></span>
							<span>Available for Work</span>
						</div>

						<p class="text-xs opacity-80 leading-relaxed font-sans">
							{{ bioText }}
						</p>

						<!-- Info Details -->
						<div class="w-full pt-4 border-t space-y-2 text-left text-xs opacity-75 font-semibold" :style="{ borderColor: activePalette.border || 'transparent' }">
							<div class="flex items-center gap-2"><MapPin class="w-4 h-4" /> {{ props.profileUser.location || 'Banyumas, Indonesia' }}</div>
							<div class="flex items-center gap-2"><Briefcase class="w-4 h-4" /> {{ props.profileUser.role_title }}</div>
						</div>
					</div>

					<!-- Skills Block -->
					<div 
						class="rounded-3xl p-6 border text-left"
						:style="{ backgroundColor: activePalette.cardBg, borderColor: activePalette.border || 'transparent' }"
					>
						<h3 class="text-xs uppercase font-black tracking-widest opacity-60 mb-3">Core Skills</h3>
						<div class="flex flex-wrap gap-1.5">
							<span 
								v-for="skill in props.profileUser.skills" 
								:key="skill"
								class="px-2.5 py-1 rounded-lg text-xs font-bold"
								:class="activePalette.badge || 'bg-slate-100 text-slate-800'"
							>
								{{ skill }}
							</span>
						</div>
					</div>
				</div>

				<!-- Scrollable Right Content Area -->
				<div class="md:col-span-8 space-y-12 print-london-main">
					<!-- Projects Showcase -->
					<div class="print-section">
						<h3 class="text-lg font-black uppercase tracking-widest border-b pb-3 mb-6 text-left" :style="{ borderColor: activePalette.border || 'transparent', fontFamily: 'Georgia, serif' }">
							Case Studies
						</h3>

						<!-- Mockup Mode -->
						<div v-if="isMockup" class="space-y-4">
							<div 
								v-for="i in 2" 
								:key="i" 
								class="rounded-2xl overflow-hidden border grid grid-cols-3"
								:style="{ backgroundColor: activePalette.cardBg, borderColor: activePalette.border || 'transparent' }"
							>
								<div class="col-span-1" :style="{ background: activePalette.accent }"></div>
								<div class="col-span-2 p-4">
									<h4 class="text-xs font-bold font-serif">London Project System {{ i }}</h4>
									<p class="text-[9px] opacity-75 mt-1">Typographic layouts and interaction elements.</p>
								</div>
							</div>
						</div>

						<!-- Live Mode -->
						<div v-else>
							<div v-if="props.projects.length === 0" class="text-center py-12 italic opacity-60 text-xs">
								Belum ada karya yang diunggah.
							</div>

							<div v-else class="space-y-8">
								<div 
									v-for="proj in props.projects" 
									:key="proj.id"
									@click="handleProjectClick(proj)"
									class="group rounded-3xl overflow-hidden cursor-pointer shadow-xs hover:shadow-md border grid grid-cols-1 sm:grid-cols-5 transition-transform hover:-translate-y-1 duration-200 print-card"
									:style="{ backgroundColor: activePalette.cardBg, borderColor: activePalette.border || 'transparent' }"
								>
									<div class="sm:col-span-2 overflow-hidden bg-slate-900 aspect-video sm:aspect-auto print-card-image">
										<img :src="proj.image" :alt="proj.title" class="w-full h-full object-cover group-hover:scale-102 transition-transform duration-300" />
									</div>
									<div class="sm:col-span-3 p-6 flex flex-col justify-between text-left">
										<div>
											<span class="text-[9px] font-black uppercase tracking-widest opacity-60">{{ proj.category }}</span>
											<h4 class="text-lg font-bold group-hover:underline leading-snug mt-1" style="font-family: Georgia, serif;">
												{{ proj.title }}
											</h4>
											<p class="text-xs mt-2 line-clamp-3 opacity-85 leading-relaxed font-sans">
												{{ proj.description }}
											</p>
										</div>
										<div class="flex items-center gap-1.5 mt-4 text-xs font-bold no-print" v-if="isInteractive" :style="{ color: activePalette.accent }">
											<span>Explore project</span>
											<ArrowRight class="w-3.5 h-3.5" />
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Certifications Showcase (only visible when not mockup) -->
					<div class="print-section page-break-before-print" v-if="!isMockup && props.profileUser.certificates && props.profileUser.certificates.length > 0">
						<h3 class="text-lg font-black uppercase tracking-widest border-b pb-3 mb-6 text-left" :style="{ borderColor: activePalette.border || 'transparent', fontFamily: 'Georgia, serif' }">
							Certificates
						</h3>

						<div class="grid grid-cols-1 gap-4">
							<div 
								v-for="cert in props.profileUser.certificates" 
								:key="cert.id"
								class="p-5 rounded-2xl border flex items-center justify-between print-card"
								:style="{ backgroundColor: activePalette.cardBg, borderColor: activePalette.border || 'transparent' }"
							>
								<div class="text-left">
									<h4 class="text-sm font-bold font-serif leading-tight">{{ cert.title }}</h4>
									<p class="text-xs opacity-75 font-semibold">{{ cert.issuer }} • {{ cert.date }}</p>
								</div>
								<span class="text-xs font-bold text-indigo-500 shrink-0">ID: {{ cert.credentialId }}</span>
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
</template>
