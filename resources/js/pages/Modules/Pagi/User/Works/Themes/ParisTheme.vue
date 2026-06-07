<script setup lang="ts">
import { Star } from "lucide-vue-next";
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
		"An editorial design collection showcasing thoughts, creations, and visual ideas.",
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
		<div class="max-w-4xl mx-auto px-6">
			<!-- Large Magazine Serif Layout -->
			<div class="text-center mt-8 mb-24 relative select-none">
				<div class="absolute top-0 right-4 animate-pulse opacity-85 no-print">
					<Star class="w-8 h-8 fill-current" :style="{ color: activePalette.accent }" />
				</div>
				
				<span class="text-[10px] tracking-widest font-black uppercase opacity-60">Creative Works</span>
				
				<h1 
					class="text-5xl sm:text-7xl font-light tracking-tight mt-4"
					style="font-family: 'Times New Roman', Times, serif;"
				>
					{{ titleText.split(' ')[0] }}<br />
					<span class="font-normal italic">{{ titleText.split(' ').slice(1).join(' ') || 'Showcase' }}</span>
				</h1>

				<div class="w-18 h-18 rounded-full overflow-hidden mx-auto my-8 border border-white/20 shrink-0 print-avatar">
					<img :src="userAvatar" :alt="props.profileUser.name" class="w-full h-full object-cover" />
				</div>

				<p class="text-base max-w-xl mx-auto italic opacity-95 leading-relaxed font-serif">
					"{{ bioText }}"
				</p>

				<!-- Skills and links footer -->
				<div class="flex flex-wrap justify-center gap-1.5 mt-8 max-w-lg mx-auto">
					<span 
						v-for="skill in props.profileUser.skills" 
						:key="skill"
						class="text-[10px] font-extrabold uppercase tracking-widest border-b pb-0.5"
						:style="{ borderColor: activePalette.accent }"
					>
						{{ skill }}
					</span>
				</div>
			</div>

			<!-- Masonry Selected Projects -->
			<div class="mb-24 print-section">
				<h3 
					class="text-2xl font-normal tracking-wide mb-8 border-b pb-2 text-center"
					style="font-family: 'Times New Roman', Times, serif;"
					:style="{ borderColor: activePalette.border || 'transparent' }"
				>
					Works Collection
				</h3>

				<!-- Mockup Mode -->
				<div v-if="isMockup" class="border-t pt-4 grid grid-cols-2 gap-6" :style="{ borderColor: activePalette.border || 'transparent' }">
					<div v-for="i in 2" :key="i" class="text-left space-y-1.5">
						<div class="aspect-square bg-stone-100/50 border" :style="{ borderColor: activePalette.border || 'transparent' }"></div>
						<h4 class="text-xs font-normal" style="font-family: 'Times New Roman', Times, serif;">Parisian Project Series {{ i }}</h4>
					</div>
				</div>

				<!-- Live Mode -->
				<div v-else>
					<div v-if="props.projects.length === 0" class="text-center py-12 italic opacity-60 text-xs">
						Belum ada karya yang diunggah.
					</div>

					<div v-else class="space-y-12">
						<div 
							v-for="(proj, idx) in props.projects" 
							:key="proj.id"
							@click="handleProjectClick(proj)"
							class="group cursor-pointer grid grid-cols-1 md:grid-cols-12 gap-8 border-b pb-8 transition-transform hover:-translate-y-0.5 duration-200 print-card"
							:style="{ borderColor: activePalette.border || 'transparent' }"
						>
							<div class="md:col-span-5 bg-stone-100 overflow-hidden aspect-video relative print-card-image">
								<img :src="proj.image" :alt="proj.title" class="w-full h-full object-cover group-hover:scale-101 transition-transform duration-300" />
							</div>
							
							<div class="md:col-span-7 flex flex-col justify-between text-left">
								<div class="space-y-2">
									<span class="text-[10px] font-mono tracking-widest opacity-60">INDEX No. 0{{ idx + 1 }}</span>
									<h4 
										class="text-xl font-medium tracking-tight leading-snug group-hover:underline"
										style="font-family: 'Times New Roman', Times, serif;"
									>
										{{ proj.title }}
									</h4>
									<p class="text-xs opacity-85 leading-relaxed font-sans">
										{{ proj.description }}
									</p>
								</div>
								
								<span class="text-[10px] font-bold tracking-wider uppercase border-b pb-0.5 w-fit mt-4 no-print" :style="{ borderColor: activePalette.text }">
									DISCOVER DETAILS
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Editorial Credentials list (only visible when not mockup) -->
			<div class="mb-16 print-section page-break-before-print" v-if="!isMockup && props.profileUser.certificates && props.profileUser.certificates.length > 0">
				<h3 
					class="text-2xl font-normal tracking-wide mb-8 border-b pb-2 text-center"
					style="font-family: 'Times New Roman', Times, serif;"
					:style="{ borderColor: activePalette.border || 'transparent' }"
				>
					Certifications
				</h3>

				<div class="grid grid-cols-1 gap-6 divide-y divide-stone-200 text-left">
					<div 
						v-for="cert in props.profileUser.certificates" 
						:key="cert.id"
						class="pt-6 flex items-start justify-between gap-4 print-card"
					>
						<div class="space-y-1">
							<h4 class="text-base font-medium font-serif leading-tight">{{ cert.title }}</h4>
							<p class="text-xs italic opacity-75">{{ cert.issuer }} — {{ cert.date }}</p>
						</div>
						<span class="text-[10px] font-bold tracking-wider opacity-60 shrink-0">[{{ cert.credentialId }}]</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
