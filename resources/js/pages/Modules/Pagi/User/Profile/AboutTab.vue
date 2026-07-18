<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import {
	Camera,
	Github,
	Globe,
	Instagram,
	Linkedin,
	Plus,
	Twitter,
	X,
} from "lucide-vue-next";
import { computed, onMounted, onUnmounted, ref } from "vue";
import OptimizedImage from "../ui/OptimizedImage.vue";
import { getInitialsAvatar } from "@/composables/useInitials";

const props = withDefaults(
	defineProps<{
		profileUser: any;
		user: any;
		isOwnProfile: boolean;
		isFollowing: boolean;
		isMessageEnabled: boolean;
		dynamicFollowersCount: number;
		dynamicFollowingCount?: number;
		skills: any[];
		timezone: string | null;
		timezoneExtended: string | null;
		languages: Array<{ language: string; proficiency: string }>;
		isLoading?: boolean;
		isStudent?: boolean;
	}>(),
	{
		isLoading: false,
		isStudent: true,
		dynamicFollowingCount: 0,
	},
);

const emit = defineEmits<{
	(e: "update-bio", bio: string): void;
	(e: "update-skills", skills: any[]): void;
	(e: "update-location", location: string): void;
	(e: "update-timezone", data: { timezone: string; extended: string }): void;
	(
		e: "update-languages",
		languages: Array<{ language: string; proficiency: string }>,
	): void;
	(
		e: "update-socials",
		socials: {
			website: string;
			linkedin: string;
			github: string;
			twitter: string;
			instagram: string;
		},
	): void;
	(e: "open-avatar-modal"): void;
	(e: "open-socials-modal"): void;
	(e: "toggle-follow"): void;
	(e: "open-chat"): void;
}>();

// Inline Bio Editor
const isEditingBio = ref(false);
const inlineBio = ref("");

const startEditBio = () => {
	inlineBio.value = props.user.bio || "";
	isEditingBio.value = true;
};

const submitInlineBio = () => {
	emit("update-bio", inlineBio.value);
	isEditingBio.value = false;
};

// Skill suggestion list (relevant to Mathematics and Computer Science - FMIKOM)
const FMIKOM_SKILL_SUGGESTIONS = [
	"Algoritma & Struktur Data",
	"Analisis Data (Data Analysis)",
	"Artificial Intelligence (AI)",
	"Basis Data (Database Systems)",
	"Calculus (Kalkulus)",
	"Cloud Computing",
	"Cyber Security",
	"Data Science",
	"Deep Learning",
	"Desain Grafis (Graphic Design)",
	"Figma UI/UX",
	"Flutter / React Native",
	"Game Development",
	"Git & GitHub",
	"HTML, CSS, JavaScript",
	"Jaringan Komputer (Computer Networks)",
	"Kriptografi (Cryptography)",
	"Laravel (PHP)",
	"Linear Algebra (Aljabar Linear)",
	"Linux Administration",
	"Machine Learning",
	"Matematika Diskrit (Discrete Mathematics)",
	"Mobile Development",
	"Pemrograman C / C++",
	"Pemrograman Java",
	"Pemrograman Python",
	"Pemrograman R / MATLAB",
	"Rekayasa Perangkat Lunak (Software Engineering)",
	"Statistika & Probabilitas",
	"System Administration",
	"Tailwind CSS / Bootstrap",
	"UI/UX Design",
	"Vue.js / React.js",
	"Web Development",
];

// Dynamic Skills Editor
const newSkill = ref("");
const newSkillPercentage = ref(80);
const showAddSkillInput = ref(false);
const showSkillSuggestions = ref(false);

const parseSkills = (
	skillsArray: any[],
): Array<{ name: string; percentage: number }> => {
	if (!Array.isArray(skillsArray)) return [];
	return skillsArray.map((item) => {
		if (typeof item === "string") {
			const parts = item.split(":");
			if (parts.length === 2 && !Number.isNaN(Number(parts[1]))) {
				return { name: parts[0], percentage: Number(parts[1]) };
			}
			return { name: item, percentage: 80 };
		}
		if (item && typeof item === "object" && item.name) {
			return { name: item.name, percentage: Number(item.percentage) || 80 };
		}
		return { name: String(item), percentage: 80 };
	});
};

const parsedSkills = computed(() => {
	return parseSkills(props.skills);
});

const filteredSkillSuggestions = computed(() => {
	const query = newSkill.value.trim().toLowerCase();
	const currentNames = new Set(
		parsedSkills.value.map((s) => s.name.toLowerCase()),
	);
	return FMIKOM_SKILL_SUGGESTIONS.filter((skill) => {
		const isMatch = skill.toLowerCase().includes(query);
		const isAlreadyAdded = currentNames.has(skill.toLowerCase());
		return isMatch && !isAlreadyAdded;
	});
});

const selectSkillSuggestion = (suggestion: string) => {
	newSkill.value = suggestion;
	showSkillSuggestions.value = false;
};

const addSkill = () => {
	if (!newSkill.value.trim()) return;
	const currentSkills = [...parsedSkills.value];
	const exists = currentSkills.some(
		(s) => s.name.toLowerCase() === newSkill.value.trim().toLowerCase(),
	);
	if (!exists) {
		currentSkills.push({
			name: newSkill.value.trim(),
			percentage: newSkillPercentage.value,
		});
		emit("update-skills", currentSkills);
	}
	newSkill.value = "";
	newSkillPercentage.value = 80;
	showAddSkillInput.value = false;
};

const removeSkill = (index: number) => {
	const currentSkills = [...parsedSkills.value];
	currentSkills.splice(index, 1);
	emit("update-skills", currentSkills);
};

// Inline details editors (one active section at a time)
const activeEditSection = ref<"location" | "timezone" | "languages" | null>(
	null,
);

const editLocation = ref("");
const editTimezone = ref("");
const editTimezoneExtended = ref("");
const editLanguages = ref<Array<{ language: string; proficiency: string }>>([]);

const toggleEditSection = (section: "location" | "timezone" | "languages") => {
	if (activeEditSection.value === section) {
		activeEditSection.value = null;
	} else {
		// Initialize editing values from props
		if (section === "location") {
			editLocation.value = props.user.location || "";
		} else if (section === "timezone") {
			editTimezone.value = props.timezone || "";
			editTimezoneExtended.value =
				props.timezoneExtended || "No extended hours";
		} else if (section === "languages") {
			editLanguages.value =
				props.languages && props.languages.length > 0
					? props.languages.map((l: any) => ({ ...l }))
					: [{ language: "", proficiency: "" }];
		}
		activeEditSection.value = section;
	}
};

const saveLocation = () => {
	emit("update-location", editLocation.value);
	activeEditSection.value = null;
};

const saveTimezone = () => {
	emit("update-timezone", {
		timezone: editTimezone.value,
		extended: editTimezoneExtended.value,
	});
	activeEditSection.value = null;
};

const saveLanguages = () => {
	const filtered = editLanguages.value.filter(
		(l) => l.language.trim() && l.proficiency.trim(),
	);
	emit("update-languages", filtered);
	activeEditSection.value = null;
};

// Autocomplete outside click handler
const handleSkillsOutsideClick = (e: MouseEvent) => {
	const target = e.target as HTMLElement;
	if (target && !target.closest("#skill_autocomplete_container")) {
		showSkillSuggestions.value = false;
	}
};

onMounted(() => {
	document.addEventListener("click", handleSkillsOutsideClick);
});

onUnmounted(() => {
	document.removeEventListener("click", handleSkillsOutsideClick);
});
</script>

<template>
	<div class="grid grid-cols-1 md:grid-cols-12 gap-8 md:gap-10 max-w-6xl mx-auto">
		<!-- Left Column (md:col-span-7) -->
		<div class="md:col-span-7 space-y-6">
			<!-- Header: Avatar + Meet + Followers + Bio -->
			<!-- Header: Avatar + Meet + Followers + Bio -->
			<div class="flex flex-col items-start w-full">
				<template v-if="isLoading">
					<!-- Avatar + Meet Name Skeleton -->
					<div class="flex items-center gap-5 mb-5 w-full">
						<div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-slate-200 dark:bg-slate-800 animate-pulse shrink-0"></div>
						<div class="h-8 w-56 bg-slate-200 dark:bg-slate-800 rounded animate-pulse"></div>
					</div>
					<!-- Follower Row Skeleton -->
					<div class="h-4 w-44 bg-slate-200 dark:bg-slate-800 rounded animate-pulse mb-3"></div>
					<!-- Followers Counts Skeleton -->
					<div class="h-4 w-32 bg-slate-200 dark:bg-slate-800 rounded animate-pulse mb-6"></div>
					<!-- Bio Block Skeleton -->
					<div class="w-full space-y-2 mb-8">
						<div class="h-4 w-full bg-slate-200 dark:bg-slate-800 rounded animate-pulse"></div>
						<div class="h-4 w-5/6 bg-slate-200 dark:bg-slate-800 rounded animate-pulse"></div>
						<div class="h-4 w-2/3 bg-slate-200 dark:bg-slate-800 rounded animate-pulse"></div>
					</div>
				</template>
				<template v-else>
					<div class="flex items-center gap-5 mb-5 w-full">
						<!-- Avatar -->
						<div 
							class="w-20 h-20 sm:w-24 sm:h-24 rounded-full border border-slate-200 dark:border-slate-800 bg-linear-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900 shadow-3xs flex items-center justify-center overflow-hidden shrink-0 relative group"
							:class="{ 'cursor-pointer': isOwnProfile }"
							@click="isOwnProfile ? emit('open-avatar-modal') : null"
						>
							<OptimizedImage v-if="user.foto_path" :src="user.foto_path.startsWith('http') ? user.foto_path : '/storage/' + user.foto_path" className="w-full h-full object-cover" alt="User avatar" />
							<div v-else class="w-full h-full bg-indigo-50 dark:bg-indigo-950/40 flex items-center justify-center text-xl sm:text-2xl font-bold text-indigo-500">
								{{ user.name.charAt(0) }}
							</div>
							<!-- Photo Edit Overlay -->
							<div v-if="isOwnProfile" class="absolute inset-0 bg-black/40 rounded-full flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
								<Camera class="w-4 h-4 text-white" />
								<span class="text-[8px] text-white font-bold uppercase tracking-wider mt-1">Update</span>
							</div>
						</div>
						<!-- Name -->
						<h2 class="text-2xl sm:text-[32px] font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2 leading-tight">
							Meet {{ user.name }}
							<img src="/premium.svg" class="w-5 h-5 sm:w-6 sm:h-6 shrink-0 select-none" title="Premium Account" alt="Verified Badge" />
						</h2>
					</div>

					<!-- Follower Row -->
					<div v-if="profileUser?.followed_by_user" class="flex items-center gap-2 mb-3 text-xs text-slate-550 dark:text-slate-400 font-semibold">
						<Link :href="profileUser.followed_by_user.pagi_username ? '/pagi/' + profileUser.followed_by_user.pagi_username : '/pagi/profile/' + profileUser.followed_by_user.id">
							<img 
								:src="profileUser.followed_by_user.foto_path || getInitialsAvatar(profileUser.followed_by_user.name)" 
								class="w-5 h-5 rounded-full border border-slate-200 dark:border-slate-800 object-cover cursor-pointer" 
								alt="Follower Avatar" 
							/>
						</Link>
						<span>
							Followed by 
							<Link 
								:href="profileUser.followed_by_user.pagi_username ? '/pagi/' + profileUser.followed_by_user.pagi_username : '/pagi/profile/' + profileUser.followed_by_user.id" 
								class="hover:text-indigo-600 font-bold transition-colors cursor-pointer"
							>
								{{ profileUser.followed_by_user.name }}
							</Link>
						</span>
					</div>

					<!-- Following/Followers Count -->
					<div class="text-xs text-slate-505 dark:text-slate-400 font-bold mb-6 flex gap-3">
						<span><strong>{{ dynamicFollowingCount }}</strong> following</span>
						<span><strong>{{ dynamicFollowersCount }}</strong> follower{{ dynamicFollowersCount !== 1 ? 's' : '' }}</span>
					</div>

					<!-- Actions for public visitors -->
					<div v-if="!isOwnProfile" class="flex items-center gap-3 mb-6">
						<button 
							v-if="isMessageEnabled"
							@click="emit('open-chat')" 
							class="h-[36px] px-6 rounded-full bg-[#18181b] hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 text-white font-semibold text-xs transition-all duration-200 cursor-pointer shadow-3xs active:scale-98 flex items-center justify-center"
						>
							Message
						</button>
						<button 
							@click="emit('toggle-follow')" 
							class="h-[36px] px-6 rounded-full border transition-all duration-200 font-semibold text-xs cursor-pointer shadow-3xs active:scale-98 flex items-center justify-center"
							:class="isFollowing 
								? 'bg-indigo-600 text-white border-transparent hover:bg-indigo-700' 
								: 'border-slate-200 hover:border-slate-300 dark:border-slate-800 dark:hover:border-slate-755 bg-white hover:bg-slate-50 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-800 hover:text-slate-900 dark:text-slate-200 dark:hover:text-white'"
						>
							{{ isFollowing ? 'Following' : 'Follow' }}
						</button>
					</div>

					<!-- Bio Block (Inline Borderless Edit) -->
					<div class="w-full mb-8 relative">
						<div v-if="isEditingBio" class="space-y-2">
							<textarea 
								v-model="inlineBio"
								maxlength="400"
								placeholder="Write a descriptive bio..."
								class="w-full p-0 bg-transparent border-0 outline-hidden focus:outline-hidden focus:ring-0 focus:border-0 resize-y text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-semibold"
								rows="3"
								autoFocus
							></textarea>
							<div class="flex items-center justify-between text-[11px] font-semibold text-slate-400">
								<span v-if="inlineBio.length === 0" class="text-red-500">bio cannot be empty</span>
								<span v-else></span>
								<span>{{ inlineBio.length }}/400</span>
							</div>
							<div class="flex items-center gap-2 mt-2">
								<button 
									@click="isEditingBio = false"
									class="px-3.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 text-[10px] font-black uppercase tracking-wider text-slate-600 dark:text-slate-355 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer"
								>
									Cancel
								</button>
								<button 
									@click="submitInlineBio"
									:disabled="inlineBio.length === 0"
									class="px-3.5 py-1.5 rounded-lg bg-slate-900 dark:bg-white text-white dark:text-slate-950 text-[10px] font-black uppercase tracking-wider hover:bg-slate-800 dark:hover:bg-slate-100 disabled:opacity-50 transition-all cursor-pointer"
								>
									Save
								</button>
							</div>
						</div>
						<div v-else>
							<p 
								v-if="user.bio" 
								@click="isOwnProfile ? startEditBio() : null"
								class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-semibold whitespace-pre-line"
								:class="isOwnProfile ? 'cursor-pointer hover:text-slate-900 dark:hover:text-white' : ''"
							>
								{{ user.bio }}
							</p>
							<button 
								v-if="isOwnProfile && !user.bio" 
								@click="startEditBio"
								class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-555 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 mt-2 hover:underline cursor-pointer"
							>
								<Plus class="w-3.5 h-3.5 text-slate-400" />
								<span>+ Add a descriptive bio</span>
							</button>
						</div>
					</div>
				</template>

				<!-- Divider Line & Skills Section -->
				<template v-if="isStudent">
					<hr class="w-full border-slate-200/60 dark:border-slate-800/80 mb-6" />

					<!-- Dynamic Skills Section (Percentage Bars) -->
					<div class="w-full space-y-5">
					<div class="flex items-center justify-between">
						<h3 class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider">Skills</h3>
						<button 
							v-if="isOwnProfile && !showAddSkillInput" 
							@click="showAddSkillInput = true; showSkillSuggestions = true" 
							class="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 cursor-pointer"
						>
							<Plus class="w-3 h-3" />
							<span>Add Skill</span>
						</button>
					</div>

					<!-- Add Skill Form with Autocomplete + Slider -->
					<div v-if="showAddSkillInput" id="skill_autocomplete_container" class="space-y-3 p-4 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-2xl">
						<!-- Search/type field -->
						<div class="relative">
							<input 
								v-model="newSkill" 
								type="text" 
								placeholder="Search or type a skill..." 
								@focus="showSkillSuggestions = true"
								@keydown.enter.prevent="filteredSkillSuggestions.length > 0 ? selectSkillSuggestion(filteredSkillSuggestions[0]) : addSkill()"
								@keydown.escape="showSkillSuggestions = false; showAddSkillInput = false; newSkill = ''"
								class="w-full h-9 px-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 transition-all shadow-2xs"
								autoFocus
							/>
							<!-- Suggestions Dropdown -->
							<div 
								v-if="showSkillSuggestions && filteredSkillSuggestions.length > 0"
								class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl z-50 max-h-48 overflow-y-auto"
							>
								<button
									v-for="suggestion in filteredSkillSuggestions.slice(0, 8)"
									:key="suggestion"
									@click.stop="selectSkillSuggestion(suggestion)"
									class="w-full text-left px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors cursor-pointer first:rounded-t-xl last:rounded-b-xl"
								>
									{{ suggestion }}
								</button>
							</div>
						</div>

						<!-- Proficiency Slider -->
						<div class="space-y-1.5">
							<div class="flex items-center justify-between text-[10px] font-bold text-slate-500 dark:text-slate-400">
								<span>Proficiency Level</span>
								<span class="text-indigo-600 dark:text-indigo-400 font-black">{{ newSkillPercentage }}%</span>
							</div>
							<input 
								v-model="newSkillPercentage"
								type="range" 
								min="10" 
								max="100" 
								step="5"
								class="w-full h-1.5 rounded-full appearance-none bg-slate-200 dark:bg-slate-700 cursor-pointer [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-indigo-600 [&::-webkit-slider-thumb]:cursor-pointer [&::-webkit-slider-thumb]:shadow-md accent-indigo-600"
							/>
							<div class="flex justify-between text-[9px] text-slate-400 font-semibold">
								<span>Beginner</span>
								<span>Intermediate</span>
								<span>Expert</span>
							</div>
						</div>

						<!-- Bar preview -->
						<div class="h-1.5 w-full bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
							<div 
								class="h-full rounded-full bg-linear-to-r from-indigo-500 to-indigo-600 transition-all duration-300"
								:style="{ width: newSkillPercentage + '%' }"
							></div>
						</div>

						<!-- Actions -->
						<div class="flex items-center gap-2 pt-1">
							<button 
								@click="showAddSkillInput = false; newSkill = ''; newSkillPercentage = 80" 
								class="px-3.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
							>
								Cancel
							</button>
							<button 
								@click="addSkill"
								:disabled="!newSkill.trim()"
								class="px-3.5 py-1.5 rounded-lg bg-slate-900 dark:bg-white text-white dark:text-slate-950 text-[10px] font-bold uppercase tracking-wider hover:bg-slate-800 dark:hover:bg-slate-100 disabled:opacity-50 transition-all cursor-pointer"
							>
								Add Skill
							</button>
						</div>
					</div>

					<!-- Skills as horizontal percentage bars -->
					<div class="space-y-3.5">
						<template v-if="isLoading">
							<div v-for="n in 3" :key="n" class="space-y-2">
								<div class="flex justify-between">
									<div class="h-3.5 w-24 bg-slate-200 dark:bg-slate-800 rounded animate-pulse"></div>
									<div class="h-3.5 w-8 bg-slate-200 dark:bg-slate-800 rounded animate-pulse"></div>
								</div>
								<div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
									<div class="h-full bg-slate-200 dark:bg-slate-800 animate-pulse rounded-full" style="width: 100%"></div>
								</div>
							</div>
						</template>
						<template v-else>
							<div 
								v-for="(skill, index) in parsedSkills" 
								:key="skill.name + index" 
								class="space-y-1.5 group/skill-bar"
							>
								<div class="flex items-center justify-between">
									<span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ skill.name }}</span>
									<div class="flex items-center gap-2">
										<span class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400">{{ skill.percentage }}%</span>
										<button 
											v-if="isOwnProfile" 
											@click="removeSkill(index)" 
											class="opacity-0 group-hover/skill-bar:opacity-100 text-slate-300 hover:text-red-500 transition-all duration-200 cursor-pointer"
										>
											<X class="w-3 h-3" />
										</button>
									</div>
								</div>
								<!-- Percentage bar -->
								<div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
									<div 
										class="h-full rounded-full transition-all duration-700 ease-out"
										:style="{ 
											width: skill.percentage + '%',
											background: skill.percentage >= 80 
												? 'linear-gradient(90deg, #6366f1, #8b5cf6)' 
												: skill.percentage >= 50 
													? 'linear-gradient(90deg, #3b82f6, #6366f1)' 
													: 'linear-gradient(90deg, #64748b, #3b82f6)'
										}"
									></div>
								</div>
							</div>
							<div v-if="parsedSkills.length === 0" class="text-xs text-slate-400 italic">
								No skills listed yet.
							</div>
						</template>
					</div>
				</div>
			</template>
			</div>
		</div>

		<!-- Right Column: Details Card (md:col-span-5) -->
		<div class="md:col-span-5 space-y-6">
			<div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-3xl p-6 shadow-3xs">
				<!-- Details Section -->
				<div>
					<span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest block mb-4">Details</span>
					
					<div class="space-y-5">
						<template v-if="isLoading">
							<!-- Location skeleton -->
							<div class="flex items-center gap-2">
								<div class="h-4 w-4 bg-slate-205 dark:bg-slate-800 rounded animate-pulse shrink-0"></div>
								<div class="h-4 w-36 bg-slate-200 dark:bg-slate-800 rounded animate-pulse"></div>
							</div>
							<!-- Timezone skeleton -->
							<div class="flex items-center gap-2">
								<div class="h-4 w-4 bg-slate-205 dark:bg-slate-800 rounded animate-pulse shrink-0"></div>
								<div class="h-4 w-48 bg-slate-200 dark:bg-slate-800 rounded animate-pulse"></div>
							</div>
							<!-- Languages skeleton -->
							<div class="flex items-center gap-2">
								<div class="h-4 w-4 bg-slate-205 dark:bg-slate-800 rounded animate-pulse shrink-0"></div>
								<div class="h-4 w-28 bg-slate-200 dark:bg-slate-800 rounded animate-pulse"></div>
							</div>
							<!-- Socials skeleton -->
							<div class="flex items-center gap-3">
								<div v-for="n in 4" :key="n" class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-800 animate-pulse"></div>
							</div>
						</template>
						<template v-else>
							<!-- Location Section -->
							<div class="space-y-2">
								<div v-if="!user.location && isOwnProfile" class="flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200">
									<Plus class="w-3.5 h-3.5 text-slate-400 shrink-0" />
									<button @click="toggleEditSection('location')" class="hover:underline cursor-pointer">Add location</button>
								</div>
								<div 
									v-else-if="user.location"
									@click="isOwnProfile ? toggleEditSection('location') : null"
									class="flex items-center justify-between group/loc-row"
									:class="isOwnProfile ? 'cursor-pointer hover:text-slate-900 dark:hover:text-white' : ''"
								>
									<div class="flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300">
										<svg fill="none" focusable="false" height="16" role="img" stroke-width="1" viewBox="0 0 24 24" width="16" class="text-slate-400 shrink-0">
											<path d="M11.3106 7.45302L11.5 6.5H12.5L12.6894 7.45302C12.9261 8.6434 13.8566 9.57394 15.047 9.81056L16 10V11L15.047 11.1894C13.8566 11.4261 12.9261 12.3566 12.6894 13.547L12.5 14.5H11.5L11.3106 13.547C11.0739 12.3566 10.1434 11.4261 8.95302 11.1894L8 11V10L8.95302 9.81056C10.1434 9.57394 11.0739 8.6434 11.3106 7.45302Z" fill="currentColor"></path>
											<path fill-rule="evenodd" clip-rule="evenodd" d="M22 10C22 18 12 24 12 24C12 24 2 18 2 10C2 4.47715 6.47715 0 12 0C17.5228 0 22 4.47715 22 10ZM20.5 10C20.5 13.3892 18.3569 16.5218 15.9393 18.9393C14.7571 20.1216 13.5694 21.0724 12.6755 21.7279C12.4236 21.9126 12.1962 22.0731 12 22.2079C11.8039 22.0731 11.5764 21.9126 11.3245 21.7279C10.4306 21.0724 9.2429 20.1216 8.06066 18.9393C5.64308 16.5218 3.5 13.3892 3.5 10C3.5 5.30558 7.30558 1.5 12 1.5C16.6944 1.5 20.5 5.30558 20.5 10Z" fill="currentColor"></path>
										</svg>
										<span>{{ user.location }}</span>
									</div>
									<button v-if="isOwnProfile" class="text-slate-400 hover:text-slate-700 dark:text-slate-500 dark:hover:text-slate-300 transition-colors cursor-pointer">
										<svg fill="none" focusable="false" height="14" role="img" stroke-width="1" viewBox="0 0 24 24" width="14">
											<path fill-rule="evenodd" clip-rule="evenodd" d="M14.3837 3.05554C14.3285 3.11073 14.2747 3.16444 14.2225 3.21691L1.5 15.9394V22.5H8.06066L20.4918 10.0689C20.5002 10.0605 20.5088 10.0519 20.5178 10.0429L21.3531 9.2076C21.8674 8.69362 22.2182 8.34308 22.4491 7.96853C23.1934 6.76173 23.1934 5.23834 22.4491 4.03154C22.2182 3.65699 21.8674 3.30646 21.3531 2.79248L21.2076 2.64691C20.6936 2.13264 20.343 1.78188 19.9685 1.55089C18.7617 0.806605 17.2383 0.806605 16.0315 1.55089C15.657 1.78189 15.3064 2.13263 14.7924 2.64691L14.3837 3.05554ZM16.8189 2.8276C17.543 2.38103 18.457 2.38103 19.1811 2.8276C19.3862 2.95408 19.605 3.16566 20.2197 3.78037C20.8344 4.39507 21.046 4.61386 21.1724 4.81894C21.619 5.54302 21.619 6.45705 21.1724 7.18113C21.046 7.38621 20.8344 7.605 20.2197 8.2197L19.4697 8.96971C19.4674 8.972 19.4651 8.97425 19.4629 8.97645C19.3826 9.05676 19.359 9.08004 19.3408 9.09658C18.864 9.52995 18.136 9.52995 17.6592 9.09658C17.6405 9.07958 17.6161 9.05547 17.5303 8.96971L15.5303 6.96971C14.9423 6.38164 14.7857 6.21106 14.7084 6.05506C14.5351 5.70534 14.5351 5.29474 14.7084 4.94502C14.7857 4.78901 14.9423 4.61843 15.5303 4.03037L15.7803 3.78036C16.395 3.16566 16.6138 2.95408 16.8189 2.8276ZM17.6515 10.7879L7.43934 21H3V16.5607L13.2125 6.34822C13.2536 6.47514 13.3043 6.59977 13.3644 6.72108C13.5662 7.12829 13.9229 7.48445 14.3837 7.94453L16.4822 10.0429C16.5496 10.1103 16.6014 10.1622 16.6503 10.2066C16.9479 10.4771 17.2901 10.6709 17.6515 10.7879Z" fill="currentColor"></path>
										</svg>
									</button>
								</div>
								<!-- Location Dropdown Form -->
								<transition enter-active-class="transition duration-200 ease-out" enter-from-class="transform -translate-y-2 opacity-0" enter-to-class="transform translate-y-0 opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="transform translate-y-0 opacity-100" leave-to-class="transform -translate-y-2 opacity-0">
									<div v-if="activeEditSection === 'location'" class="p-4 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-2xl space-y-3 mt-1.5">
										<input 
											v-model="editLocation" 
											type="text" 
											placeholder="Enter location (e.g. Central Jakarta, Indonesia)" 
											class="w-full h-9 px-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800"
										/>
										<div class="flex items-center gap-2">
											<button @click="activeEditSection = null" class="px-3.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-355 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">Cancel</button>
											<button @click="saveLocation" class="px-3.5 py-1.5 rounded-lg bg-slate-900 dark:bg-white text-white dark:text-slate-950 text-[10px] font-bold uppercase tracking-wider hover:bg-slate-800 dark:hover:bg-slate-100 transition-colors cursor-pointer">Save</button>
										</div>
									</div>
								</transition>
							</div>

							<!-- Timezone Section -->
							<div class="space-y-2">
								<div v-if="!timezone && isOwnProfile" class="flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-450 dark:hover:text-slate-200">
									<Plus class="w-3.5 h-3.5 text-slate-400 shrink-0" />
									<button @click="toggleEditSection('timezone')" class="hover:underline cursor-pointer">Add timezone</button>
								</div>
								<div 
									v-else-if="timezone"
									@click="isOwnProfile ? toggleEditSection('timezone') : null"
									class="flex items-center justify-between group/tz-row"
									:class="isOwnProfile ? 'cursor-pointer hover:text-slate-900 dark:hover:text-white' : ''"
								>
									<div class="flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300">
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" height="16" width="16" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 shrink-0">
											<circle cx="12" cy="12" r="10" />
											<polyline points="12 6 12 12 16 14" />
										</svg>
										<span>{{ timezone }} <template v-if="timezoneExtended && timezoneExtended !== 'No extended hours'">• {{ timezoneExtended }}</template></span>
									</div>
									<button v-if="isOwnProfile" class="text-slate-400 hover:text-slate-700 dark:text-slate-500 dark:hover:text-slate-300 transition-colors cursor-pointer">
										<svg fill="none" focusable="false" height="14" role="img" stroke-width="1.2" viewBox="0 0 24 24" width="14">
											<path fill-rule="evenodd" clip-rule="evenodd" d="M14.3837 3.05554C14.3285 3.11073 14.2747 3.16444 14.2225 3.21691L1.5 15.9394V22.5H8.06066L20.4918 10.0689C20.5002 10.0605 20.5088 10.0519 20.5178 10.0429L21.3531 9.2076C21.8674 8.69362 22.2182 8.34308 22.4491 7.96853C23.1934 6.76173 23.1934 5.23834 22.4491 4.03154C22.2182 3.65699 21.8674 3.30646 21.3531 2.79248L21.2076 2.64691C20.6936 2.13264 20.343 1.78188 19.9685 1.55089C18.7617 0.806605 17.2383 0.806605 16.0315 1.55089C15.657 1.78189 15.3064 2.13263 14.7924 2.64691L14.3837 3.05554ZM16.8189 2.8276C17.543 2.38103 18.457 2.38103 19.1811 2.8276C19.3862 2.95408 19.605 3.16566 20.2197 3.78037C20.8344 4.39507 21.046 4.61386 21.1724 4.81894C21.619 5.54302 21.619 6.45705 21.1724 7.18113C21.046 7.38621 20.8344 7.605 20.2197 8.2197L19.4697 8.96971C19.4674 8.972 19.4651 8.97425 19.4629 8.97645C19.3826 9.05676 19.359 9.08004 19.3408 9.09658C18.864 9.52995 18.136 9.52995 17.6592 9.09658C17.6405 9.07958 17.6161 9.05547 17.5303 8.96971L15.5303 6.96971C14.9423 6.38164 14.7857 6.21106 14.7084 6.05506C14.5351 5.70534 14.5351 5.29474 14.7084 4.94502C14.7857 4.78901 14.9423 4.61843 15.5303 4.03037L15.7803 3.78036C16.395 3.16566 16.6138 2.95408 16.8189 2.8276ZM17.6515 10.7879L7.43934 21H3V16.5607L13.2125 6.34822C13.2536 6.47514 13.3043 6.59977 13.3644 6.72108C13.5662 7.12829 13.9229 7.48445 14.3837 7.94453L16.4822 10.0429C16.5496 10.1103 16.6014 10.1622 16.6503 10.2066C16.9479 10.4771 17.2901 10.6709 17.6515 10.7879Z" fill="currentColor"></path>
										</svg>
									</button>
								</div>
								<!-- Timezone Dropdown Form -->
								<transition enter-active-class="transition duration-200 ease-out" enter-from-class="transform -translate-y-2 opacity-0" enter-to-class="transform translate-y-0 opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="transform translate-y-0 opacity-100" leave-to-class="transform -translate-y-2 opacity-0">
									<div v-if="activeEditSection === 'timezone'" class="p-4 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-2xl space-y-4 text-left mt-1.5">
										<div class="space-y-1.5">
											<label for="timezone-select" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Time Zone</label>
											<select id="timezone-select" v-model="editTimezone" class="w-full h-9 px-3 pr-8 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%236b7280%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%2F%3E%3C%2Fsvg%3E')] bg-position-[right_10px_center] bg-size-[16px_16px] bg-no-repeat cursor-pointer">
												<option value="">Select Time Zone</option>
												<option value="(GMT+7) Western Indonesian Time (WIB)">(GMT+7) Western Indonesian Time (WIB)</option>
												<option value="(GMT+8) Central Indonesian Time (WITA)">(GMT+8) Central Indonesian Time (WITA)</option>
												<option value="(GMT+9) Eastern Indonesian Time (WIT)">(GMT+9) Eastern Indonesian Time (WIT)</option>
												<option value="(GMT-8) Pacific Standard Time (PST)">(GMT-8) Pacific Standard Time (PST)</option>
												<option value="(GMT-5) Eastern Standard Time (EST)">(GMT-5) Eastern Standard Time (EST)</option>
												<option value="(GMT-6) Central Standard Time (CST)">(GMT-6) Central Standard Time (CST)</option>
												<option value="(GMT-7) Mountain Standard Time (MST)">(GMT-7) Mountain Standard Time (MST)</option>
												<option value="(GMT-0) Greenwich Mean Time (GMT)">(GMT-0) Greenwich Mean Time (GMT)</option>
												<option value="(GMT+8) China Standard Time (CST)">(GMT+8) China Standard Time (CST)</option>
												<option value="(GMT+9) Japan Standard Time (JST)">(GMT+9) Japan Standard Time (JST)</option>
											</select>
										</div>
										<div class="space-y-1.5">
											<label for="extended-hours-select" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Extended Hours</label>
											<select id="extended-hours-select" v-model="editTimezoneExtended" class="w-full h-9 px-3 pr-8 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%236b7280%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%2F%3E%3C%2Fsvg%3E')] bg-position-[right_10px_center] bg-size-[16px_16px] bg-no-repeat cursor-pointer">
												<option value="No extended hours">No extended hours</option>
												<option value="1 Hour">1 Hour</option>
												<option value="2 Hours">2 Hours</option>
												<option value="3 Hours">3 Hours</option>
												<option value="4 Hours">4 Hours</option>
												<option value="5 Hours">5 Hours</option>
											</select>
										</div>
										<div class="text-[10px] text-slate-450 dark:text-slate-500 leading-normal font-semibold">
											Your time zone will not exclude you from jobs; it helps us find you better matches.
										</div>
										<div class="flex items-center gap-2">
											<button @click="activeEditSection = null" class="px-3.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-355 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">Cancel</button>
											<button @click="saveTimezone" class="px-3.5 py-1.5 rounded-lg bg-slate-900 dark:bg-white text-white dark:text-slate-950 text-[10px] font-bold uppercase tracking-wider hover:bg-slate-800 dark:hover:bg-slate-100 transition-colors cursor-pointer">Save</button>
										</div>
									</div>
								</transition>
							</div>

							<!-- Languages Section -->
							<div class="space-y-2">
								<div v-if="(!languages || languages.length === 0) && isOwnProfile" class="flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200">
									<Plus class="w-3.5 h-3.5 text-slate-400 shrink-0" />
									<button @click="toggleEditSection('languages')" class="hover:underline cursor-pointer">Add languages</button>
								</div>
								<div 
									v-else-if="languages && languages.length > 0"
									@click="isOwnProfile ? toggleEditSection('languages') : null"
									class="flex items-center justify-between group/lang-row"
									:class="isOwnProfile ? 'cursor-pointer hover:text-slate-900 dark:hover:text-white' : ''"
								>
									<div class="flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300">
										<Globe class="w-3.5 h-3.5 text-slate-400 shrink-0" />
										<span>{{ languages.map(l => `${l.language} (${l.proficiency})`).join(', ') }}</span>
									</div>
									<button v-if="isOwnProfile" class="text-slate-400 hover:text-slate-700 dark:text-slate-500 dark:hover:text-slate-300 transition-colors cursor-pointer">
										<svg fill="none" focusable="false" height="14" role="img" stroke-width="1.2" viewBox="0 0 24 24" width="14">
											<path fill-rule="evenodd" clip-rule="evenodd" d="M14.3837 3.05554C14.3285 3.11073 14.2747 3.16444 14.2225 3.21691L1.5 15.9394V22.5H8.06066L20.4918 10.0689C20.5002 10.0605 20.5088 10.0519 20.5178 10.0429L21.3531 9.2076C21.8674 8.69362 22.2182 8.34308 22.4491 7.96853C23.1934 6.76173 23.1934 5.23834 22.4491 4.03154C22.2182 3.65699 21.8674 3.30646 21.3531 2.79248L21.2076 2.64691C20.6936 2.13264 20.343 1.78188 19.9685 1.55089C18.7617 0.806605 17.2383 0.806605 16.0315 1.55089C15.657 1.78189 15.3064 2.13263 14.7924 2.64691L14.3837 3.05554ZM16.8189 2.8276C17.543 2.38103 18.457 2.38103 19.1811 2.8276C19.3862 2.95408 19.605 3.16566 20.2197 3.78037C20.8344 4.39507 21.046 4.61386 21.1724 4.81894C21.619 5.54302 21.619 6.45705 21.1724 7.18113C21.046 7.38621 20.8344 7.605 20.2197 8.2197L19.4697 8.96971C19.4674 8.972 19.4651 8.97425 19.4629 8.97645C19.3826 9.05676 19.359 9.08004 19.3408 9.09658C18.864 9.52995 18.136 9.52995 17.6592 9.09658C17.6405 9.07958 17.6161 9.05547 17.5303 8.96971L15.5303 6.96971C14.9423 6.38164 14.7857 6.21106 14.7084 6.05506C14.5351 5.70534 14.5351 5.29474 14.7084 4.94502C14.7857 4.78901 14.9423 4.61843 15.5303 4.03037L15.7803 3.78036C16.395 3.16566 16.6138 2.95408 16.8189 2.8276ZM17.6515 10.7879L7.43934 21H3V16.5607L13.2125 6.34822C13.2536 6.47514 13.3043 6.59977 13.3644 6.72108C13.5662 7.12829 13.9229 7.48445 14.3837 7.94453L16.4822 10.0429C16.5496 10.1103 16.6014 10.1622 16.6503 10.2066C16.9479 10.4771 17.2901 10.6709 17.6515 10.7879Z" fill="currentColor"></path>
										</svg>
									</button>
								</div>
								<!-- Languages Dropdown Form -->
								<transition enter-active-class="transition duration-200 ease-out" enter-from-class="transform -translate-y-2 opacity-0" enter-to-class="transform translate-y-0 opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="transform translate-y-0 opacity-100" leave-to-class="transform -translate-y-2 opacity-0">
									<div v-if="activeEditSection === 'languages'" class="p-4 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-2xl space-y-4 text-left mt-1.5">
										<div v-for="(item, idx) in editLanguages" :key="idx" class="flex gap-2 items-center border-b border-slate-200/50 dark:border-slate-800/50 pb-3 last:border-b-0 last:pb-0">
											<div class="flex-1 space-y-2">
												<select v-model="item.language" class="w-full h-9 px-3 pr-8 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%236b7280%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%2F%3E%3C%2Fsvg%3E')] bg-position-[right_10px_center] bg-size-[16px_16px] bg-no-repeat cursor-pointer">
													<option value="">Select language</option>
													<option value="Indonesian">Indonesian</option>
													<option value="English">English</option>
													<option value="Javanese">Javanese</option>
													<option value="Sundanese">Sundanese</option>
													<option value="Mandarin">Mandarin</option>
													<option value="Japanese">Japanese</option>
													<option value="Spanish">Spanish</option>
													<option value="German">German</option>
													<option value="French">French</option>
													<option value="Arabic">Arabic</option>
												</select>
												<select v-model="item.proficiency" class="w-full h-9 px-3 pr-8 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%236b7280%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%2F%3E%3C%2Fsvg%3E')] bg-position-[right_10px_center] bg-size-[16px_16px] bg-no-repeat cursor-pointer">
													<option value="">Select proficiency</option>
													<option value="Native">Native</option>
													<option value="Fluent">Fluent</option>
													<option value="Conversational">Conversational</option>
													<option value="Basic">Basic</option>
												</select>
											</div>
											<button @click="editLanguages.splice(idx, 1)" class="p-2 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-400 hover:text-red-500 hover:border-red-200 transition-colors shrink-0 cursor-pointer">
												<X class="w-4 h-4" />
											</button>
										</div>
										<button @click="editLanguages.push({ language: '', proficiency: '' })" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 cursor-pointer">
											<Plus class="w-3.5 h-3.5" />
											<span>Add another language</span>
										</button>
										<div class="flex items-center gap-2 mt-2">
											<button @click="activeEditSection = null" class="px-3.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-355 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">Cancel</button>
											<button @click="saveLanguages" class="px-3.5 py-1.5 rounded-lg bg-slate-900 dark:bg-white text-white dark:text-slate-950 text-[10px] font-bold uppercase tracking-wider hover:bg-slate-800 dark:hover:bg-slate-100 transition-colors cursor-pointer">Save</button>
										</div>
									</div>
								</transition>
							</div>

							<!-- Social Links Section — now opens modal popup -->
							<div class="space-y-2">
								<div v-if="isOwnProfile && !user.website && !user.linkedin && !user.github && !user.twitter && !user.instagram" class="flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200">
									<Plus class="w-3.5 h-3.5 text-slate-400 shrink-0" />
									<button @click="emit('open-socials-modal')" class="hover:underline cursor-pointer">Add social links</button>
								</div>
								<div 
									v-else-if="user.website || user.linkedin || user.github || user.twitter || user.instagram"
									class="flex items-center justify-between group/social-row"
									:class="isOwnProfile ? 'cursor-pointer' : ''"
									@click="isOwnProfile ? emit('open-socials-modal') : null"
								>
									<div class="flex items-center gap-3">
										<a v-if="user.linkedin" :href="user.linkedin.startsWith('http') ? user.linkedin : 'https://' + user.linkedin" target="_blank" @click.stop class="text-slate-500 hover:text-[#0077b5] transition-colors"><Linkedin class="w-4 h-4" /></a>
										<a v-if="user.github" :href="user.github.startsWith('http') ? user.github : 'https://' + user.github" target="_blank" @click.stop class="text-slate-500 hover:text-black dark:hover:text-white transition-colors"><Github class="w-4 h-4" /></a>
										<a v-if="user.instagram" :href="user.instagram.startsWith('http') ? user.instagram : 'https://' + user.instagram" target="_blank" @click.stop class="text-slate-500 hover:text-[#e1306c] transition-colors"><Instagram class="w-4 h-4" /></a>
										<a v-if="user.twitter" :href="user.twitter.startsWith('http') ? user.twitter : 'https://' + user.twitter" target="_blank" @click.stop class="text-slate-500 hover:text-[#1da1f2] transition-colors"><Twitter class="w-4 h-4" /></a>
										<a v-if="user.website" :href="user.website.startsWith('http') ? user.website : 'https://' + user.website" target="_blank" @click.stop class="text-slate-500 hover:text-indigo-500 transition-colors"><Globe class="w-4 h-4" /></a>
									</div>
									<button v-if="isOwnProfile" @click.stop="emit('open-socials-modal')" class="text-slate-400 hover:text-slate-700 dark:text-slate-500 dark:hover:text-slate-300 transition-colors cursor-pointer">
										<svg fill="none" focusable="false" height="14" role="img" stroke-width="1.2" viewBox="0 0 24 24" width="14">
											<path fill-rule="evenodd" clip-rule="evenodd" d="M14.3837 3.05554C14.3285 3.11073 14.2747 3.16444 14.2225 3.21691L1.5 15.9394V22.5H8.06066L20.4918 10.0689C20.5002 10.0605 20.5088 10.0519 20.5178 10.0429L21.3531 9.2076C21.8674 8.69362 22.2182 8.34308 22.4491 7.96853C23.1934 6.76173 23.1934 5.23834 22.4491 4.03154C22.2182 3.65699 21.8674 3.30646 21.3531 2.79248L21.2076 2.64691C20.6936 2.13264 20.343 1.78188 19.9685 1.55089C18.7617 0.806605 17.2383 0.806605 16.0315 1.55089C15.657 1.78189 15.3064 2.13263 14.7924 2.64691L14.3837 3.05554ZM16.8189 2.8276C17.543 2.38103 18.457 2.38103 19.1811 2.8276C19.3862 2.95408 19.605 3.16566 20.2197 3.78037C20.8344 4.39507 21.046 4.61386 21.1724 4.81894C21.619 5.54302 21.619 6.45705 21.1724 7.18113C21.046 7.38621 20.8344 7.605 20.2197 8.2197L19.4697 8.96971C19.4674 8.972 19.4651 8.97425 19.4629 8.97645C19.3826 9.05676 19.359 9.08004 19.3408 9.09658C18.864 9.52995 18.136 9.52995 17.6592 9.09658C17.6405 9.07958 17.6161 9.05547 17.5303 8.96971L15.5303 6.96971C14.9423 6.38164 14.7857 6.21106 14.7084 6.05506C14.5351 5.70534 14.5351 5.29474 14.7084 4.94502C14.7857 4.78901 14.9423 4.61843 15.5303 4.03037L15.7803 3.78036C16.395 3.16566 16.6138 2.95408 16.8189 2.8276ZM17.6515 10.7879L7.43934 21H3V16.5607L13.2125 6.34822C13.2536 6.47514 13.3043 6.59977 13.3644 6.72108C13.5662 7.12829 13.9229 7.48445 14.3837 7.94453L16.4822 10.0429C16.5496 10.1103 16.6014 10.1622 16.6503 10.2066C16.9479 10.4771 17.2901 10.6709 17.6515 10.7879Z" fill="currentColor"></path>
										</svg>
									</button>
								</div>
							</div>
						</template>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
