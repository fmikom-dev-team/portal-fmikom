<script setup lang="ts">
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
		"Interface and logic programmer focusing on performant web architecture.",
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
		class="py-16 md:py-24 font-mono theme-section text-left"
		:style="{ backgroundColor: activePalette.bg, color: activePalette.text }"
	>
		<div class="max-w-5xl mx-auto px-6">
			<!-- Cyber grid header -->
			<div 
				class="mt-8 border p-6 sm:p-10 flex flex-col sm:flex-row items-center gap-6 justify-between relative overflow-hidden"
				:style="{ borderColor: activePalette.border || 'transparent', backgroundColor: activePalette.cardBg }"
			>
				<!-- Corner decoration -->
				<div class="absolute top-0 right-0 w-3 h-3 border-b border-l" :style="{ borderColor: activePalette.accent }"></div>
				<div class="absolute bottom-0 left-0 w-3 h-3 border-t border-r" :style="{ borderColor: activePalette.accent }"></div>

				<div class="space-y-4 text-center sm:text-left flex-1">
					<div class="flex flex-col gap-1 select-none">
						<span class="text-[10px] tracking-widest opacity-60 uppercase">SYSTEM USER DIRECTORY:</span>
						<h1 class="text-2xl sm:text-4xl font-black uppercase tracking-wider leading-none">
							{{ titleText }}
						</h1>
					</div>

					<p class="text-xs opacity-85 leading-relaxed max-w-xl">
						{{ bioText }}
					</p>

					<div class="flex flex-wrap gap-1.5 justify-center sm:justify-start pt-1">
						<span 
							v-for="skill in props.profileUser.skills" 
							:key="skill"
							class="px-2 py-0.5 rounded text-[10px] font-black font-mono"
							:class="activePalette.badge || 'border border-black text-black'"
						>
							[{{ skill.toUpperCase() }}]
						</span>
					</div>
				</div>

				<!-- Square Profile photo -->
				<div class="w-24 h-24 shrink-0 border-2 print-avatar" :style="{ borderColor: activePalette.border || 'transparent' }">
					<img :src="userAvatar" :alt="props.profileUser.name" class="w-full h-full object-cover grayscale" />
				</div>
			</div>

			<!-- Code Projects Grid -->
			<div class="mt-16 mb-16 print-section">
				<h3 class="text-sm font-black uppercase tracking-widest mb-6 text-left" :style="{ color: activePalette.accent }">
					// 01. CODE ARCHIVES
				</h3>

				<!-- Mockup Mode -->
				<div v-if="isMockup" class="border divide-y" :style="{ borderColor: activePalette.border || 'transparent' }">
					<div 
						v-for="i in 2" 
						:key="i" 
						class="p-4 flex justify-between items-center text-xs"
						:style="{ backgroundColor: activePalette.cardBg }"
					>
						<span>[0{{ i }}] WEB COMPONENT DATA ARCHIVE</span>
						<span :style="{ color: activePalette.accent }">VIEW_PRJ →</span>
					</div>
				</div>

				<!-- Live Mode -->
				<div v-else>
					<div v-if="props.projects.length === 0" class="text-center py-12 border border-dashed rounded italic opacity-60 text-xs">
						No projects uploaded.
					</div>

					<div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4 print-grid">
						<div 
							v-for="(proj, idx) in props.projects" 
							:key="proj.id"
							@click="handleProjectClick(proj)"
							class="group p-5 cursor-pointer border flex flex-col justify-between hover:bg-black/5 dark:hover:bg-white/5 transition-all duration-200 print-card"
							:style="{ borderColor: activePalette.border || 'transparent', backgroundColor: activePalette.cardBg }"
						>
							<div class="text-left">
								<div class="flex justify-between items-start text-[10px] opacity-60 font-black">
									<span>ID: {{ proj.id }}</span>
									<span>[{{ idx + 1 < 10 ? '0' + (idx + 1) : idx + 1 }}]</span>
								</div>
								<h4 class="text-sm font-black uppercase tracking-wide group-hover:underline mt-2">
									{{ proj.title }}
								</h4>
								<p class="text-[11px] mt-2 opacity-80 line-clamp-3">
									{{ proj.description }}
								</p>
							</div>
							<div class="flex justify-between items-center mt-6 text-[10px] font-black">
								<span>{{ proj.category.toUpperCase() }}</span>
								<span class="no-print" v-if="isInteractive" :style="{ color: activePalette.accent }">EXP_PRJ →</span>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Credentials Monospace list (only visible when not mockup) -->
			<div class="mb-16 print-section page-break-before-print" v-if="!isMockup && props.profileUser.certificates && props.profileUser.certificates.length > 0">
				<h3 class="text-sm font-black uppercase tracking-widest mb-6 text-left" :style="{ color: activePalette.accent }">
					// 02. VERIFIED CREDENTIALS
				</h3>

				<div class="border divide-y text-left" :style="{ borderColor: activePalette.border || 'transparent', divideColor: activePalette.border || 'transparent' }">
					<div 
						v-for="cert in props.profileUser.certificates" 
						:key="cert.id"
						class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs print-card"
						:style="{ backgroundColor: activePalette.cardBg }"
					>
						<div class="space-y-1">
							<h4 class="font-black uppercase text-sm">{{ cert.title }}</h4>
							<p class="opacity-75">{{ cert.issuer }} • {{ cert.date }}</p>
						</div>
						<div class="text-[10px] font-black tracking-wider text-slate-500 uppercase shrink-0">
							CRD: [{{ cert.credentialId }}]
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
