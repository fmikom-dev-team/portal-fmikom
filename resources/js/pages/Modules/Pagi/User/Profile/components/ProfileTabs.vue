<script setup lang="ts">
import { MapPin, Pencil, Plus, Globe, Linkedin, Github, Twitter, Instagram } from "lucide-vue-next";

defineProps<{
	tabs: string[];
	activeTab: string;
	isLoading: boolean;
	isOwnProfile: boolean;
	user: any;
	socialLinks: Array<{ type: string; url: string; label: string }>;
}>();

const emit = defineEmits(["update:activeTab", "click-location", "click-socials"]);
</script>

<template>
	<nav id="profile_tabs_navigation" class="border-b border-slate-200 dark:border-slate-800 pb-3 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 sm:mb-8" aria-label="Profile tabs">
		<!-- Tabs list -->
		<div class="flex items-center gap-6 overflow-x-auto w-full sm:w-auto" style="scrollbar-width: none;">
			<button 
				v-for="tab in tabs" 
				:key="tab" 
				@click="emit('update:activeTab', tab)"
				:class="[
					'whitespace-nowrap text-sm font-semibold transition-all relative cursor-pointer outline-hidden',
					activeTab === tab 
						? 'text-slate-900 dark:text-white font-bold' 
						: 'text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'
				]"
			>
				{{ tab }}
				<div v-if="activeTab === tab" class="absolute -bottom-[13px] left-0 w-full h-0.5 bg-slate-900 dark:bg-white rounded-full"></div>
			</button>
		</div>

		<!-- Right Area: Location & Social Links -->
		<div v-if="isLoading" class="hidden lg:flex items-center gap-6 sm:shrink-0">
			<div class="h-4 w-32 bg-slate-200 dark:bg-slate-800 rounded animate-pulse"></div>
			<div class="w-px h-4 bg-slate-200 dark:bg-slate-800"></div>
			<div class="h-8 w-24 bg-slate-200 dark:bg-slate-800 rounded-full animate-pulse"></div>
		</div>
		<div v-else class="hidden lg:flex items-center justify-between w-full sm:w-auto gap-6 sm:shrink-0">
			<!-- Location indicator -->
			<div class="relative group flex justify-center">
				<div 
					@click="isOwnProfile ? emit('click-location') : null" 
					:class="[
						'flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-400 group/loc',
						isOwnProfile ? 'cursor-pointer hover:text-slate-900 dark:hover:text-white transition-colors' : ''
					]"
				>
					<MapPin class="w-3.5 h-3.5 text-slate-400" />
					<span>{{ user.location || 'Purwokerto, Indonesia' }}</span>
					<!-- Pencil Icon (Visible on hover if own profile with smooth transition slide) -->
					<Pencil 
						v-if="isOwnProfile" 
						class="h-3.5 text-slate-400 dark:text-slate-500 shrink-0 transition-all duration-300 ease-out translate-x-2 opacity-0 w-0 overflow-hidden group-hover/loc:w-3.5 group-hover/loc:ml-1.5 group-hover/loc:opacity-100 group-hover/loc:translate-x-0" 
					/>
				</div>
			</div>

			<div class="w-px h-4 bg-slate-200 dark:bg-slate-800 hidden sm:block"></div>

			<!-- Social connections -->
			<div class="flex items-center gap-3">
				<!-- Own Profile -->
				<div v-if="isOwnProfile">
					<!-- If no social links yet, show the '+ Add social links' button -->
					<button 
						v-if="socialLinks.length === 0"
						@click="emit('click-socials')" 
						class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-[#f1f5f9] hover:bg-[#e2e8f0] dark:bg-slate-800 dark:hover:bg-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 transition-colors shadow-3xs cursor-pointer"
					>
						<Plus class="w-3.5 h-3.5 text-slate-500" />
						<span>Add social links</span>
					</button>
					
					<!-- If social links exist, show the active icons, and show a '+' button next to them on hover -->
					<div v-else class="flex items-center gap-2 group/soc-edit">
						<a 
							v-for="link in socialLinks" 
							:key="link.type" 
							:href="link.url" 
							target="_blank" 
							rel="noopener noreferrer"
							class="w-8 h-8 rounded-full bg-[#f1f5f9] hover:bg-[#e2e8f0] dark:bg-slate-800 dark:hover:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-colors"
							:title="link.label"
						>
							<Globe v-if="link.type === 'website'" class="w-4 h-4" />
							<Linkedin v-else-if="link.type === 'linkedin'" class="w-4 h-4" />
							<Github v-else-if="link.type === 'github'" class="w-4 h-4" />
							<Twitter v-else-if="link.type === 'twitter'" class="w-4 h-4" />
							<Instagram v-else-if="link.type === 'instagram'" class="w-4 h-4" />
						</a>
						
						<!-- '+' Button to edit/add (transitions to opacity-100 on hover of group) -->
						<button 
							@click="emit('click-socials')" 
							class="w-8 h-8 rounded-full bg-slate-900 text-white dark:bg-white dark:text-slate-950 hover:bg-slate-800 dark:hover:bg-slate-100 flex items-center justify-center transition-all duration-200 cursor-pointer shadow-xs opacity-0 group-hover/soc-edit:opacity-100 scale-95 group-hover/soc-edit:scale-100"
							title="Add/Edit social links"
							aria-label="Add or edit social links"
						>
							<Plus class="w-4 h-4" />
						</button>
					</div>
				</div>

				<!-- Public View: Display social links dynamically as icons -->
				<div v-else-if="socialLinks.length > 0" class="flex items-center gap-2">
					<a 
						v-for="link in socialLinks" 
						:key="link.type" 
						:href="link.url" 
						target="_blank" 
						rel="noopener noreferrer"
						class="w-8 h-8 rounded-full bg-[#f1f5f9] hover:bg-[#e2e8f0] dark:bg-slate-800 dark:hover:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-colors"
						:title="link.label"
					>
						<Globe v-if="link.type === 'website'" class="w-4 h-4" />
						<Linkedin v-else-if="link.type === 'linkedin'" class="w-4 h-4" />
						<Github v-else-if="link.type === 'github'" class="w-4 h-4" />
						<Twitter v-else-if="link.type === 'twitter'" class="w-4 h-4" />
						<Instagram v-else-if="link.type === 'instagram'" class="w-4 h-4" />
					</a>
				</div>
			</div>
		</div>
	</nav>
</template>
