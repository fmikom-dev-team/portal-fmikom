<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import {
	Camera,
	Github,
	Globe,
	Image as ImageIcon,
	Instagram,
	Linkedin,
	MapPin,
	Pencil,
	Plus,
	Settings,
	Share,
	Sparkles,
	Twitter,
	UploadCloud,
} from "lucide-vue-next";
import { computed } from "vue";
import OptimizedImage from "../../ui/OptimizedImage.vue";
import VideoLazy from "../../ui/VideoLazy.vue";
import ProfileStats from "./ProfileStats.vue";

const props = defineProps<{
	isLoading: boolean;
	isOwnProfile: boolean;
	isFollowing: boolean;
	isMessageEnabled: boolean;
	user: any;
	displayRoleName: string;
	displayOwnerRoleName: string;
	projectCount: number;
	totalLikes: number;
	dynamicFollowersCount: number;
	dynamicFollowingCount: number;
	socialLinks: Array<{ type: string; url: string; label: string }>;
}>();

const emit = defineEmits([
	"open-avatar-modal",
	"open-username-modal",
	"open-location-modal",
	"open-location-only-modal",
	"open-socials-modal",
	"open-banner-modal",
	"open-chat",
	"toggle-message-switch",
	"share-profile",
	"toggle-follow",
	"select-work-tab",
	"open-relations-modal",
]);

const isVideoUrl = (url: string | null): boolean => {
	if (!url) return false;
	const cleanUrl = url.split("?")[0].split("#")[0];
	const ext = cleanUrl.split(".").pop()?.toLowerCase();
	return (
		["mp4", "webm", "mov", "avi", "mkv", "3gp"].includes(ext || "") ||
		url.startsWith("data:video/")
	);
};
</script>

<template>
	<!-- DESKTOP HEADER -->
	<div class="hidden lg:flex lg:flex-row justify-between items-start gap-10 mb-12 transition-all duration-300">
		<!-- Left Column: Avatar & Text Details (Public vs Owner layout) -->
		<div class="flex-1 w-full">
			<!-- Case A: Own Profile (Horizontal layout) -->
			<div v-if="isOwnProfile" class="flex flex-col sm:flex-row items-start gap-6 w-full">
				<!-- Circular Avatar with hover photo overlay -->
				<div v-if="isLoading" class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-slate-200 dark:bg-slate-800 animate-pulse shrink-0"></div>
				<div 
					v-else
					class="relative group select-none shrink-0" 
					:class="{ 'cursor-pointer': isOwnProfile }"
					@click="isOwnProfile ? emit('open-avatar-modal') : null"
				>
					<!-- Tooltip Bubble -->
					<div v-if="isOwnProfile" class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3.5 opacity-0 pointer-events-none group-hover:opacity-100 transition-all duration-200 translate-y-1 group-hover:translate-y-0 bg-slate-955 text-white text-[11px] font-black tracking-wide px-3.5 py-2 rounded-xl whitespace-nowrap shadow-lg z-30">
						Change photo
						<div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-955"></div>
					</div>

					<div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full border border-slate-200 dark:border-slate-800 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900 shadow-2xs flex items-center justify-center overflow-hidden transition-transform duration-300">
						<OptimizedImage v-if="user.foto_path" :src="user.foto_path.startsWith('http') ? user.foto_path : '/storage/' + user.foto_path" alt="Avatar" fetchpriority="high" className="w-full h-full object-cover" />
						<div v-else class="w-full h-full bg-indigo-50 dark:bg-indigo-950/40 flex items-center justify-center">
							<span class="text-xl sm:text-2xl font-bold text-indigo-500 dark:text-indigo-400">{{ user.name?.charAt(0) || 'U' }}</span>
						</div>
						
						<!-- Photo Edit Overlay (shows on hover) -->
						<div v-if="isOwnProfile" class="absolute inset-0 bg-black/40 rounded-full flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
							<Camera class="w-4 h-4 sm:w-5 sm:h-5 text-white" />
							<span class="text-[8px] sm:text-[9px] text-white font-bold uppercase tracking-wider mt-1">Update</span>
						</div>
					</div>
					<!-- Available Dot -->
					<div class="absolute bottom-0.5 right-0.5 w-3.5 h-3.5 sm:w-4 sm:h-4 bg-emerald-500 border-2 border-white dark:border-slate-900 rounded-full"></div>
				</div>

				<!-- Details Stack on the right of Avatar -->
				<div class="flex-1 min-w-0 flex flex-col items-start gap-3.5">
					<!-- Name & Role (read-only) -->
					<div class="space-y-1 w-full">
						<template v-if="isLoading">
							<div class="h-8 w-56 bg-slate-200 dark:bg-slate-800 rounded animate-pulse"></div>
							<div class="h-4 w-36 bg-slate-200 dark:bg-slate-800 rounded animate-pulse mt-2"></div>
							<div class="h-3.5 w-24 bg-slate-200 dark:bg-slate-800 rounded animate-pulse mt-1.5"></div>
						</template>
						<template v-else>
							<h1 class="text-2xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-none flex items-center gap-2">
								{{ user.name }}
								<img src="/premium.svg" class="w-5.5 h-5.5 sm:w-6.5 sm:h-6.5 shrink-0 select-none" title="Premium Account" alt="Verified Badge" />
							</h1>
							<p class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400">
								{{ displayRoleName }}
							</p>
							<!-- @username display -->
							<div class="flex items-center gap-1.5 mt-0.5">
								<div 
									v-if="user.pagi_username" 
									class="flex items-center gap-1.5"
									:class="{ 'group cursor-pointer select-none': isOwnProfile }"
									@click="isOwnProfile ? emit('open-username-modal') : null"
								>
									<span class="text-xs font-bold text-slate-500 dark:text-slate-400 tracking-tight group-hover:text-slate-700 dark:group-hover:text-slate-300 transition-colors">@{{ user.pagi_username }}</span>
									<Pencil 
										v-if="isOwnProfile" 
										class="w-3 h-3 text-slate-400 dark:text-slate-505 opacity-0 group-hover:opacity-100 transition-opacity duration-200" 
									/>
								</div>
								<button
									v-else-if="isOwnProfile"
									@click="emit('open-username-modal')"
									class="text-[10px] font-semibold text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors cursor-pointer bg-transparent border-none p-0 underline underline-offset-2"
								>
									+ Buat username
								</button>
							</div>
						</template>
					</div>

					<!-- Action Buttons Row (Message, Share) -->
					<div class="flex items-center gap-3.5 pt-1">
						<!-- Message Button with Toggle Switch inside -->
						<div v-if="isOwnProfile || isMessageEnabled" class="relative group/msg">
							<button 
								@click="emit('open-chat')" 
								class="h-[44px] px-6 rounded-full border border-slate-300 dark:border-slate-800 bg-white hover:bg-slate-50 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold text-xs tracking-wider uppercase transition-all duration-200 cursor-pointer flex items-center gap-3 shadow-2xs hover:border-slate-400 dark:hover:border-slate-700 active:scale-98"
							>
								<span>Message</span>
								<div 
									v-if="isOwnProfile"
									@click.stop="emit('toggle-message-switch')"
									class="w-9 h-5 rounded-full relative p-[2px] transition-colors duration-300 border cursor-pointer shrink-0 flex items-center"
									:class="isMessageEnabled ? 'bg-emerald-500 border-transparent shadow-xs' : 'bg-slate-200 dark:bg-slate-800 border-slate-300 dark:border-slate-700'"
								>
									<div 
										class="w-4 h-4 rounded-full bg-white shadow-sm transition-transform duration-300 transform"
										:class="isMessageEnabled ? 'translate-x-4' : 'translate-x-0'"
									></div>
								</div>
							</button>
						</div>

						<!-- Share Button -->
						<div class="relative group/share">
							<div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2.5 opacity-0 pointer-events-none group-hover/share:opacity-100 transition-all duration-200 translate-y-1 group-hover/share:translate-y-0 bg-slate-900 text-white dark:bg-white dark:text-slate-900 text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 rounded-xl whitespace-nowrap shadow-md z-30">
								Bagikan Profil
								<div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900 dark:border-t-white"></div>
							</div>

							<button 
								@click="emit('share-profile')"
								class="w-[44px] h-[44px] rounded-full border border-slate-300 dark:border-slate-800 bg-white hover:bg-slate-50 text-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 flex items-center justify-center transition-all duration-200 cursor-pointer shadow-2xs hover:border-slate-400 dark:hover:border-slate-700 active:scale-98"
								aria-label="Bagikan Profil"
							>
								<Share class="w-4 h-4" />
							</button>
						</div>

						<!-- Settings Button (Only for own profile) -->
						<div v-if="isOwnProfile" class="relative group/settings">
							<div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2.5 opacity-0 pointer-events-none group-hover/settings:opacity-100 transition-all duration-200 translate-y-1 group-hover/settings:translate-y-0 bg-slate-900 text-white dark:bg-white dark:text-slate-900 text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 rounded-xl whitespace-nowrap shadow-md z-30">
								Pengaturan
								<div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900 dark:border-t-white"></div>
							</div>

							<Link 
								href="/pagi/settings"
								class="w-[44px] h-[44px] rounded-full border border-slate-300 dark:border-slate-800 bg-white hover:bg-slate-50 text-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 flex items-center justify-center transition-all duration-200 cursor-pointer shadow-2xs hover:border-slate-400 dark:hover:border-slate-700 active:scale-98"
								aria-label="Pengaturan"
							>
								<Settings class="w-4 h-4" />
							</Link>
						</div>
					</div>

					<!-- Stats Grid Box -->
					<ProfileStats 
						:projectCount="projectCount"
						:totalLikes="totalLikes"
						:followersCount="dynamicFollowersCount"
						:followingCount="dynamicFollowingCount"
						:isLoading="isLoading"
						@click-work="emit('select-work-tab')"
						@click-followers="emit('open-relations-modal', 'followers')"
						@click-following="emit('open-relations-modal', 'following')"
						class="mt-4"
					/>
				</div>
			</div>

			<!-- Case B: Public Viewer Profile -->
			<div v-else class="flex flex-col items-start gap-5 w-full">
				<!-- Avatar -->
				<div v-if="isLoading" class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-slate-200 dark:bg-slate-800 animate-pulse shrink-0"></div>
				<div v-else class="w-20 h-20 sm:w-24 sm:h-24 rounded-full border border-slate-200 dark:border-slate-800 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900 shadow-xs flex items-center justify-center overflow-hidden shrink-0">
					<OptimizedImage v-if="user.foto_path" :src="user.foto_path.startsWith('http') ? user.foto_path : '/storage/' + user.foto_path" alt="Avatar" fetchpriority="high" className="w-full h-full object-cover" />
					<div v-else class="w-full h-full bg-indigo-50 dark:bg-indigo-950/40 flex items-center justify-center">
						<span class="text-xl sm:text-2xl font-bold text-indigo-500 dark:text-indigo-400">{{ user.name?.charAt(0) || 'U' }}</span>
					</div>
				</div>

				<!-- Name & Role -->
				<div class="space-y-1 w-full">
					<template v-if="isLoading">
						<div class="h-9 w-64 bg-slate-200 dark:bg-slate-800 rounded animate-pulse"></div>
						<div class="h-4.5 w-44 bg-slate-200 dark:bg-slate-800 rounded animate-pulse mt-2"></div>
					</template>
					<template v-else>
						<h1 class="text-3xl sm:text-[38px] font-bold text-slate-900 dark:text-white tracking-tight leading-none flex items-center gap-2">
							{{ user.name }}
							<img src="/premium.svg" class="w-5.5 h-5.5 sm:w-6.5 sm:h-6.5 shrink-0 select-none" title="Premium Account" alt="Verified Badge" />
						</h1>
						<p class="text-sm sm:text-base font-normal text-slate-550 dark:text-slate-400">
							{{ displayOwnerRoleName }}
						</p>
					</template>
				</div>

				<!-- Action Buttons -->
				<div class="flex items-center gap-2.5 w-full sm:w-auto pt-1">
					<button 
						v-if="isMessageEnabled"
						@click="emit('open-chat')" 
						class="h-[38px] px-5 rounded-full bg-slate-950 hover:bg-slate-800 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-50 text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer shadow-2xs active:scale-98 flex items-center justify-center"
					>
						Message
					</button>
					<button 
						@click="emit('toggle-follow')" 
						class="h-[38px] px-5 rounded-full border transition-all duration-200 font-bold text-xs uppercase tracking-wider cursor-pointer shadow-2xs active:scale-98 flex items-center justify-center"
						:class="isFollowing 
							? 'bg-indigo-600 border-transparent text-white hover:bg-indigo-700' 
							: 'border-slate-300 dark:border-slate-800 bg-white hover:bg-slate-50 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200'"
					>
						{{ isFollowing ? 'Following' : 'Follow' }}
					</button>
				</div>

				<!-- Stats Grid Box -->
				<ProfileStats 
					:projectCount="projectCount"
					:totalLikes="totalLikes"
					:followersCount="dynamicFollowersCount"
					:followingCount="dynamicFollowingCount"
					:isLoading="isLoading"
					@click-work="emit('select-work-tab')"
					@click-followers="emit('open-relations-modal', 'followers')"
					@click-following="emit('open-relations-modal', 'following')"
				/>

				<!-- Skill Expert Badge Tag -->
				<div v-if="isLoading" class="h-9 w-32 bg-slate-200 dark:bg-slate-800 rounded-2xl animate-pulse"></div>
				<div v-else class="inline-flex items-center gap-3 px-3 py-2.5 rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-[#f8f9fa] dark:bg-slate-900/50 select-none shadow-3xs">
					<!-- Icon Container -->
					<div class="w-8 h-8 rounded-xl bg-slate-950 dark:bg-white flex items-center justify-center shrink-0">
						<svg v-if="user.role_title && user.role_title.toLowerCase().includes('rive')" viewBox="0 0 24 24" class="w-4.5 h-4.5 fill-white dark:fill-slate-950" xmlns="http://www.w3.org/2000/svg">
							<rect x="5" y="11" width="3.5" height="8" rx="1.75" />
							<rect x="10.25" y="8" width="3.5" height="8" rx="1.75" />
							<rect x="15.5" y="5" width="3.5" height="8" rx="1.75" />
						</svg>
						<svg v-else-if="user.role_title && (user.role_title.toLowerCase().includes('figma') || user.role_title.toLowerCase().includes('design') || user.role_title.toLowerCase().includes('ui/ux'))" viewBox="0 0 24 24" class="w-4 h-4 text-white dark:text-slate-950 fill-current" xmlns="http://www.w3.org/2000/svg">
							<path d="M12 2C9.24 2 7 4.24 7 7c0 1.93 1.09 3.6 2.7 4.4L7 14c0 2.76 2.24 5 5 5s5-2.24 5-5l-2.7-2.6C15.91 10.6 17 8.93 17 7c0-2.76-2.24-5-5-5zm-2 5c0-1.1.9-2 2-2s2 .9 2 2-.9 2-2 2-2-.9-2-2z"/>
						</svg>
						<Sparkles v-else class="w-4 h-4 text-white dark:text-slate-950 fill-current" />
					</div>
					<!-- Expert Text -->
					<span class="text-sm font-semibold text-slate-800 dark:text-slate-200">
						{{ user.role_title && user.role_title.toLowerCase().includes('rive') ? 'Rive expert' : (user.role_title && user.role_title.toLowerCase().includes('figma') ? 'Figma expert' : 'Top Creator') }}
					</span>
				</div>
			</div>
		</div>

		<!-- Right Column: Featured Media Container -->
		<div v-if="isLoading" class="w-full lg:w-[620px] aspect-[16/10] rounded-[24px] bg-slate-200 dark:bg-slate-800 animate-pulse shrink-0"></div>
		<div 
			v-else
			class="w-full lg:w-[620px] shrink-0 group relative"
			:class="{ 'cursor-pointer': isOwnProfile }"
			@click="isOwnProfile ? emit('open-banner-modal') : null"
		>
			<!-- Tooltip Bubble -->
			<div v-if="isOwnProfile" class="absolute bottom-full right-4 mb-3.5 opacity-0 pointer-events-none group-hover:opacity-100 transition-all duration-200 translate-y-1 group-hover:translate-y-0 bg-slate-950 text-white text-[11px] font-black tracking-wide px-3.5 py-2 rounded-xl whitespace-nowrap shadow-lg z-30">
				Edit banner
				<div class="absolute top-full right-6 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-955"></div>
			</div>

			<!-- Hover Edit Pencil Button -->
			<div v-if="isOwnProfile" class="absolute top-4 right-4 z-20 w-9 h-9 rounded-full bg-slate-950/80 border border-slate-800 text-white flex items-center justify-center shadow-lg hover:bg-slate-900 transition-colors opacity-0 group-hover:opacity-100">
				<Pencil class="w-4 h-4" />
			</div>

			<!-- Display banner if uploaded -->
			<div 
				v-if="user.banner_path"
				class="w-full aspect-[16/10] rounded-[24px] relative overflow-hidden shadow-2xs border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900"
			>
				<VideoLazy 
					v-if="isVideoUrl(user.banner_path)"
					:src="'/storage/' + user.banner_path"
					className="w-full h-full object-cover"
				/>
				<OptimizedImage v-else :src="'/storage/' + user.banner_path" alt="Featured Banner" fetchpriority="high" className="w-full h-full object-cover" />

				<!-- Premium Hover Overlay (shows on hover) -->
				<div v-if="isOwnProfile" class="absolute inset-0 bg-black/60 z-10 flex flex-col items-center justify-center p-6 text-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
					<ImageIcon class="w-8 h-8 text-white mb-3" />
					<h3 class="text-sm font-bold text-white">Add featured media</h3>
					<p class="text-xs font-semibold text-white/95 mt-1">
						Drag and drop or <span class="underline font-bold">browse</span>
					</p>
					<p class="text-[10px] text-white/70 mt-4 max-w-[320px] leading-relaxed font-semibold">
						We recommend a video (mp4) or image (png, jpg, gif) in a 4:3, 5:4, 9:16, or 16:9 aspect ratio. Max 100MB.
					</p>
				</div>
			</div>

			<!-- Premium Add media Placeholder -->
			<div 
				v-else-if="isOwnProfile"
				class="w-full aspect-[16/10] rounded-[24px] border border-slate-200/60 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex flex-col items-center justify-center p-6 text-center shadow-2xs"
			>
				<ImageIcon class="w-8 h-8 text-slate-400 dark:text-slate-500 mb-3" />
				<h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">Add featured media</h3>
				<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1">
					Drag and drop or <span class="text-slate-700 dark:text-slate-200 underline font-bold hover:text-indigo-600 transition-colors">browse</span>
				</p>
				<p class="text-[10px] text-slate-400 dark:text-slate-500 mt-4 max-w-[320px] leading-relaxed font-semibold">
					We recommend a video (mp4) or image (png, jpg, gif) in a 4:3, 5:4, 9:16, or 16:9 aspect ratio. Max 100MB.
				</p>
			</div>

			<!-- Guest Cover Gradient Placeholder -->
			<div 
				v-else
				class="w-full aspect-[16/10] rounded-[24px] bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 flex flex-col items-center justify-center p-6 text-center shadow-2xs relative overflow-hidden"
			>
				<div class="absolute inset-0 opacity-20 pointer-events-none bg-[radial-gradient(rgba(255,255,255,0.25)_1px,transparent_1px)] [background-size:20px_20px]"></div>
				<Sparkles class="w-10 h-10 text-white/80 mb-3 animate-pulse" />
				<h3 class="text-sm font-black text-white tracking-wide">PAGI Creative Portfolio</h3>
				<p class="text-xs text-white/80 mt-1">Explore student projects and collaborations</p>
			</div>
		</div>
	</div>

	<!-- MOBILE HEADER -->
	<div class="lg:hidden flex flex-col w-full mb-6 relative select-none">
		<!-- Banner / Featured Media -->
		<div class="relative w-full aspect-[16/7] sm:aspect-[16/10] mb-2">
			<div v-if="isLoading" class="w-full h-full rounded-2xl bg-slate-200 dark:bg-slate-800 animate-pulse"></div>
			<div 
				v-else
				class="w-full h-full group rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 cursor-pointer shadow-xs"
				@click="isOwnProfile ? emit('open-banner-modal') : null"
			>
				<!-- Hover Edit Pencil Button -->
				<div v-if="isOwnProfile" class="absolute top-3 right-3 z-20 w-8 h-8 rounded-full bg-slate-950/80 border border-slate-800 text-white flex items-center justify-center shadow-lg">
					<Pencil class="w-3.5 h-3.5" />
				</div>

				<!-- Display banner if uploaded -->
				<template v-if="user.banner_path && user.banner_path !== 'null'">
					<VideoLazy 
						v-if="isVideoUrl(user.banner_path)"
						:src="user.banner_path.startsWith('http') ? user.banner_path : (user.banner_path.startsWith('/storage') ? user.banner_path : '/storage/' + user.banner_path)"
						className="w-full h-full object-cover"
					/>
					<OptimizedImage v-else :src="user.banner_path.startsWith('http') ? user.banner_path : (user.banner_path.startsWith('/storage') ? user.banner_path : '/storage/' + user.banner_path)" alt="Featured Banner" fetchpriority="high" className="w-full h-full object-cover" />
				</template>
				<!-- Placeholder for Owner -->
				<div v-else-if="isOwnProfile" class="w-full h-full flex flex-col items-center justify-center p-4 text-center">
					<ImageIcon class="w-6 h-6 text-slate-400 dark:text-slate-500 mb-2" />
					<h3 class="text-xs font-bold text-slate-800 dark:text-slate-200">Add featured media</h3>
				</div>
				<!-- Placeholder for Guest -->
				<div v-else class="w-full h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 flex flex-col items-center justify-center p-4 text-center">
					<Sparkles class="w-6 h-6 text-white/80 mb-2 animate-pulse" />
					<h3 class="text-xs font-black text-white tracking-wide">PAGI Creative Portfolio</h3>
				</div>
			</div>

			<!-- Circular Avatar Overlapping (bottom-left) -->
			<div v-if="isLoading" class="absolute -bottom-8 left-4 z-20 w-18 h-18 sm:w-20 sm:h-20 rounded-full border-[3px] border-white dark:border-slate-900 bg-slate-200 dark:bg-slate-800 animate-pulse"></div>
			<div 
				v-else
				class="absolute -bottom-8 left-4 z-20"
				@click.stop="isOwnProfile ? emit('open-avatar-modal') : null"
			>
				<div class="w-18 h-18 sm:w-20 sm:h-20 rounded-full border-[3px] border-white dark:border-slate-900 bg-slate-100 dark:bg-slate-800 overflow-hidden shadow-md flex items-center justify-center relative">
					<OptimizedImage v-if="user.foto_path" :src="user.foto_path.startsWith('http') ? user.foto_path : '/storage/' + user.foto_path" alt="Avatar" fetchpriority="high" className="w-full h-full object-cover" />
					<span v-else class="text-lg font-black text-indigo-500">{{ user.name?.charAt(0) || 'U' }}</span>
					
					<!-- Photo Edit Overlay -->
					<div v-if="isOwnProfile" class="absolute inset-0 bg-black/40 rounded-full flex flex-col items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
						<Camera class="w-4 h-4 text-white" />
					</div>
				</div>
				<!-- Online dot indicator -->
				<div class="absolute bottom-0.5 right-0.5 w-4 h-4 bg-emerald-500 border-2 border-white dark:border-slate-900 rounded-full"></div>
			</div>
		</div>

		<!-- Details Section -->
		<div class="mt-8 flex flex-col items-start w-full px-1">
			<!-- Name & Role -->
			<div class="space-y-1.5 w-full">
				<template v-if="isLoading">
					<div class="h-8 w-48 bg-slate-200 dark:bg-slate-800 rounded animate-pulse"></div>
					<div class="h-4 w-36 bg-slate-200 dark:bg-slate-800 rounded animate-pulse mt-2"></div>
					<div class="h-3.5 w-24 bg-slate-200 dark:bg-slate-800 rounded animate-pulse mt-1.5"></div>
				</template>
				<template v-else>
					<h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight leading-none flex items-center gap-2">
						{{ user.name }}
						<img src="/premium.svg" class="w-5.5 h-5.5 shrink-0" title="Premium Account" alt="Verified Badge" />
					</h2>
					<p class="text-sm font-semibold text-slate-550 dark:text-slate-400">
						{{ isOwnProfile ? displayRoleName : displayOwnerRoleName }}
					</p>
					<div class="flex items-center gap-1.5 text-xs">
						<div 
							v-if="user.pagi_username" 
							class="flex items-center gap-1.5"
							:class="{ 'group cursor-pointer select-none': isOwnProfile }"
							@click="isOwnProfile ? emit('open-username-modal') : null"
						>
							<span class="font-bold text-slate-500 dark:text-slate-400 group-hover:text-slate-700 dark:group-hover:text-slate-300 transition-colors">@{{ user.pagi_username }}</span>
							<Pencil 
								v-if="isOwnProfile" 
								class="w-3 h-3 text-slate-400 dark:text-slate-500 opacity-0 group-hover:opacity-100 transition-opacity duration-200" 
							/>
						</div>
						<button
							v-else-if="isOwnProfile"
							@click="emit('open-username-modal')"
							class="font-semibold text-slate-400 hover:text-indigo-600 underline underline-offset-2 cursor-pointer bg-transparent border-none p-0"
						>
							+ Buat username
						</button>
					</div>
				</template>
			</div>

			<!-- Action Row -->
			<div class="flex items-center gap-3 mt-4 w-full">
				<!-- Message Button -->
				<div v-if="isOwnProfile || isMessageEnabled" class="flex-1">
					<button 
						@click="emit('open-chat')" 
						class="w-full h-11 px-5 rounded-full border border-slate-200 dark:border-slate-800 bg-white hover:bg-slate-50 dark:bg-zinc-900 dark:hover:bg-zinc-800 text-slate-800 dark:text-zinc-250 font-black text-xs uppercase tracking-wider transition-all cursor-pointer flex items-center justify-center gap-3 shadow-2xs hover:border-slate-300 active:scale-97"
					>
						<span>Message</span>
						<div 
							v-if="isOwnProfile"
							@click.stop="emit('toggle-message-switch')"
							class="w-8 h-4.5 rounded-full relative p-[2px] transition-colors duration-300 border cursor-pointer shrink-0 flex items-center"
							:class="isMessageEnabled ? 'bg-emerald-500 border-transparent shadow-xs' : 'bg-slate-200 dark:bg-slate-800 border-slate-300 dark:border-slate-700'"
						>
							<div 
								class="w-3.5 h-3.5 rounded-full bg-white shadow-xs transition-transform duration-300 transform"
								:class="isMessageEnabled ? 'translate-x-3.5' : 'translate-x-0'"
							></div>
						</div>
					</button>
				</div>

				<!-- Follow/Following Button -->
				<div v-if="!isOwnProfile" class="flex-1">
					<button 
						@click="emit('toggle-follow')" 
						class="w-full h-11 px-5 rounded-full font-black text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer shadow-2xs active:scale-97 flex items-center justify-center"
						:class="isFollowing 
							? 'bg-indigo-600 text-white hover:bg-indigo-700 border-transparent' 
							: 'border border-slate-200 dark:border-slate-800 bg-white hover:bg-slate-50 dark:bg-zinc-900 dark:hover:bg-zinc-800 text-slate-800 dark:text-zinc-200 hover:border-slate-300'"
					>
						{{ isFollowing ? 'Following' : 'Follow' }}
					</button>
				</div>

				<!-- Share Button -->
				<button 
					@click="emit('share-profile')"
					class="w-11 h-11 rounded-full border border-slate-200 dark:border-slate-800 bg-white dark:bg-zinc-900 hover:bg-slate-50 dark:hover:bg-zinc-800 text-slate-600 dark:text-zinc-350 flex items-center justify-center shrink-0 cursor-pointer shadow-2xs active:scale-95 transition-all hover:border-slate-300"
					title="Share Profile"
					aria-label="Bagikan Profil"
				>
					<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" d="M4 12v8a2 2 0 002 2h12a2 2 0 002-2v-8m-4-6l-4-4L8 6m4-4v13" />
					</svg>
				</button>

				<!-- Settings Button -->
				<Link 
					v-if="isOwnProfile"
					href="/pagi/settings"
					class="w-11 h-11 rounded-full border border-slate-200 dark:border-slate-800 bg-white dark:bg-zinc-900 hover:bg-slate-50 dark:hover:bg-zinc-800 text-slate-600 dark:text-zinc-350 flex items-center justify-center shrink-0 cursor-pointer shadow-2xs active:scale-95 transition-all hover:border-slate-300"
					title="Pengaturan"
					aria-label="Pengaturan"
				>
					<Settings class="w-4.5 h-4.5" />
				</Link>
			</div>

			<!-- Location & Social Section -->
			<div v-if="isLoading" class="flex items-center gap-4 mt-4 pt-3.5 border-t border-slate-150 dark:border-slate-800/80 w-full">
				<div class="h-4 w-32 bg-slate-200 dark:bg-slate-800 rounded animate-pulse"></div>
				<div class="h-4 w-24 bg-slate-200 dark:bg-slate-800 rounded animate-pulse"></div>
			</div>
			<div v-else class="flex flex-wrap items-center gap-3 text-xs font-semibold text-slate-500 dark:text-slate-400 mt-4 pt-3.5 border-t border-slate-150 dark:border-slate-800/80 w-full">
				<div 
					@click="isOwnProfile ? emit('open-location-only-modal') : null"
					class="flex items-center gap-1.5"
					:class="{ 'cursor-pointer hover:text-slate-800': isOwnProfile }"
				>
					<MapPin class="w-3.5 h-3.5 text-slate-400" />
					<span>{{ user.location || 'Purwokerto, Indonesia' }}</span>
				</div>
				
				<template v-if="isOwnProfile ? socialLinks.length > 0 : (user.linkedin || user.github || user.website || user.twitter || user.instagram)">
					<div class="w-px h-3.5 bg-slate-200 dark:bg-slate-800"></div>

					<!-- Social connections -->
					<div class="flex items-center gap-3">
						<template v-if="isOwnProfile">
							<a v-for="link in socialLinks" :key="link.type" :href="link.url" target="_blank" class="text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-colors" :title="link.label">
								<Globe v-if="link.type === 'website'" class="w-4 h-4" />
								<Linkedin v-else-if="link.type === 'linkedin'" class="w-4 h-4" />
								<Github v-else-if="link.type === 'github'" class="w-4 h-4" />
								<Twitter v-else-if="link.type === 'twitter'" class="w-4 h-4" />
								<Instagram v-else-if="link.type === 'instagram'" class="w-4 h-4" />
							</a>
						</template>
						<template v-else>
							<a v-if="user.linkedin" :href="user.linkedin" target="_blank" class="text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-colors" title="LinkedIn">
								<Linkedin class="w-4 h-4" />
							</a>
							<a v-if="user.github" :href="user.github" target="_blank" class="text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-colors" title="GitHub">
								<Github class="w-4 h-4" />
							</a>
							<a v-if="user.website" :href="user.website" target="_blank" class="text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-colors" title="Website">
								<Globe class="w-4 h-4" />
							</a>
							<a v-if="user.twitter" :href="user.twitter" target="_blank" class="text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-colors" title="Twitter">
								<Twitter class="w-4 h-4" />
							</a>
							<a v-if="user.instagram" :href="user.instagram" target="_blank" class="text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-colors" title="Instagram">
								<Instagram class="w-4 h-4" />
							</a>
						</template>
					</div>
				</template>
			</div>
		</div>
	</div>
</template>
