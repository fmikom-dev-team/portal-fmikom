<script setup lang="ts">
import { Link, router } from "@inertiajs/vue3";
import {
	BookOpen,
	ChevronDown,
	ChevronRight,
	Plus,
	Zap,
} from "lucide-vue-next";
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { Skeleton } from "@/components/ui/skeleton";
import CaseStudyTab from "./CaseStudyTab.vue";
import CollaboratedTab from "./CollaboratedTab.vue";
import CreatedTab from "./CreatedTab.vue";

const props = withDefaults(
	defineProps<{
		projects?: Array<{
			id: number;
			user_id: number;
			title: string;
			image: string;
			likes: number;
			views: number;
			content: any;
			created_at: string;
			is_verified?: boolean;
			is_published?: boolean;
			user?: any;
		}>;
		isOwnProfile: boolean;
		user: any;
		isLoading?: boolean;
		isStudent?: boolean;
	}>(),
	{
		isStudent: true,
	},
);

const emit = defineEmits<{
	(e: "open-project", project: any): void;
	(e: "clone-project", title: string): void;
	(e: "share-project", project: any): void;
	(e: "delete-project", id: number, title: string): void;
	(e: "report-project", project: any): void;
	(e: "open-add-work"): void;
	(e: "edit-quick-work", project: any): void;
	(
		e: "like-updated",
		data: { id: number; liked: boolean; count: number },
	): void;
}>();

const activeWorkFilter = ref<"Created" | "Case Study" | "Collaborated">(
	"Created",
);
const showCreateDropdown = ref(false);

const closeDropdownOnOutsideClick = () => {
	showCreateDropdown.value = false;
};

onMounted(() => {
	if (globalThis.window !== undefined) {
		globalThis.window.addEventListener("click", closeDropdownOnOutsideClick);
	}
});

onUnmounted(() => {
	if (globalThis.window !== undefined) {
		globalThis.window.removeEventListener("click", closeDropdownOnOutsideClick);
	}
});
const localProjects = ref<any[]>([]);

watch(
	() => props.projects,
	(newVal) => {
		if (newVal) {
			localProjects.value = [...newVal];
		}
	},
	{ immediate: true, deep: true },
);

const isQuickAddProject = (project: any) => {
	if (!project?.content || !Array.isArray(project.content)) return false;
	return !project.content.some(
		(b: any) => b && b.type !== "featured_details" && b.type !== "settings",
	);
};

const isGalleryItem = (project: any) => {
	if (!project?.content || !Array.isArray(project.content)) return false;
	return project.content.some((b: any) => b && b.type === "gallery_item");
};

const createdProjects = computed(() => {
	return localProjects.value.filter(
		(p) => !isGalleryItem(p) && p.user_id === props.user.id,
	);
});

const caseStudyProjects = computed(() => {
	return localProjects.value.filter(
		(p) =>
			!isQuickAddProject(p) && !isGalleryItem(p) && p.user_id === props.user.id,
	);
});

const collaboratedProjects = computed(() => {
	return localProjects.value.filter((p) => {
		if (isGalleryItem(p)) return false;
		const details =
			p.content?.find((b: any) => b && b.type === "featured_details") || {};
		const collaborators = details.collaborators || [];

		const hasCollaborators =
			Array.isArray(collaborators) && collaborators.length > 0;

		const cleanUserName = props.user.name.toLowerCase();
		const cleanUserHandle = (props.user.pagi_username || "")
			.toLowerCase()
			.replace(/^@/, "");

		const isUserCollaborator =
			Array.isArray(collaborators) &&
			collaborators.some((c) => {
				const cName = String(typeof c === "object" ? c.name || "" : c)
					.toLowerCase()
					.replace(/^@/, "");
				const cUserId = typeof c === "object" ? (c.user_id ?? c.id) : null;

				if (cUserId && Number(cUserId) === Number(props.user.id)) return true;

				return (
					cName === cleanUserName ||
					(cleanUserHandle && cName === cleanUserHandle)
				);
			});

		const isUserOwnerWithCollaborators =
			p.user_id === props.user.id && hasCollaborators;

		return isUserCollaborator || isUserOwnerWithCollaborators;
	});
});

const handleReorder = (newSubOrderIds: number[]) => {
	const newOrderMap = new Map(newSubOrderIds.map((id, index) => [id, index]));

	const subTabProjects = localProjects.value.filter((p) =>
		newOrderMap.has(p.id),
	);
	subTabProjects.sort(
		(a, b) => newOrderMap.get(a.id)! - newOrderMap.get(b.id)!,
	);

	let subTabIdx = 0;
	const updatedProjects = localProjects.value.map((p) => {
		if (newOrderMap.has(p.id)) {
			return subTabProjects[subTabIdx++];
		}
		return p;
	});

	localProjects.value = updatedProjects;

	const orderIds = updatedProjects.map((p) => p.id);
	router.post(
		"/pagi/profile/reorder-projects",
		{ order: orderIds },
		{
			preserveScroll: true,
			preserveState: true,
		},
	);
};
</script>

<template>
	<div class="space-y-6">
		<!-- Subheader filters and action buttons -->
		<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
			<!-- Created vs Case Study vs Collaborated Segmented Control Pill Container -->
			<div class="bg-[#f1f5f9] dark:bg-slate-900/50 p-1 rounded-2xl flex items-center gap-1 border border-slate-200/40 dark:border-slate-800 shadow-3xs">
				<button 
					@click.stop="activeWorkFilter = 'Created'"
					class="px-4 py-2 rounded-xl text-xs font-semibold transition-all cursor-pointer"
					:class="activeWorkFilter === 'Created' 
						? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs' 
						: 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'"
				>
					Created
				</button>

				<button 
					@click.stop="activeWorkFilter = 'Case Study'"
					class="px-4 py-2 rounded-xl text-xs font-semibold transition-all cursor-pointer"
					:class="activeWorkFilter === 'Case Study' 
						? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs' 
						: 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'"
				>
					Case Study
				</button>

				<button 
					@click.stop="activeWorkFilter = 'Collaborated'"
					class="px-4 py-2 rounded-xl text-xs font-semibold transition-all cursor-pointer"
					:class="activeWorkFilter === 'Collaborated' 
						? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs' 
						: 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'"
				>
					Collaborated
				</button>
			</div>

			<!-- Unified Action Button with Dropdown (Only for own profile) -->
			<div v-if="isOwnProfile && isStudent" class="relative sm:ml-auto" @click.stop>
				<div class="inline-flex items-center rounded-full bg-indigo-600 dark:bg-indigo-600 text-white shadow-sm overflow-hidden p-0.5 select-none">
					<button 
						v-if="activeWorkFilter === 'Case Study'"
						@click.prevent="router.visit('/pagi/editor')"
						class="inline-flex items-center gap-1.5 px-4 py-2 hover:bg-indigo-700 dark:hover:bg-indigo-500 text-white text-xs font-bold transition-all cursor-pointer border-none bg-transparent"
					>
						<Plus class="w-4 h-4 text-white" />
						<span>Create Case Study</span>
					</button>
					<button 
						v-else
						@click.prevent="emit('open-add-work')"
						class="inline-flex items-center gap-1.5 px-4 py-2 hover:bg-indigo-700 dark:hover:bg-indigo-500 text-white text-xs font-bold transition-all cursor-pointer border-none bg-transparent"
					>
						<Plus class="w-4 h-4 text-white" />
						<span>Create Work</span>
					</button>
					<button 
						@click.stop="showCreateDropdown = !showCreateDropdown" 
						class="px-2.5 py-2 hover:bg-indigo-700 dark:hover:bg-indigo-500 text-white transition-all cursor-pointer border-none bg-transparent border-l border-white/20"
						title="Options"
					>
						<ChevronDown class="w-3.5 h-3.5 text-white/90 transition-transform duration-200" :class="{ 'rotate-180': showCreateDropdown }" />
					</button>
				</div>

				<!-- Dropdown Menu -->
				<div 
					v-if="showCreateDropdown" 
					class="absolute left-0 sm:left-auto sm:right-0 mt-2 w-72 sm:w-80 max-w-[calc(100vw-2rem)] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl rounded-2xl p-2 z-50 space-y-1 select-none"
				>
					<!-- Option 1: Create Case Study -->
					<Link 
						href="/pagi/editor" 
						@click="showCreateDropdown = false"
						class="w-full p-3 flex items-start gap-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-colors group text-left no-underline"
					>
						<div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0 group-hover:scale-105 transition-transform mt-0.5">
							<BookOpen class="w-4.5 h-4.5" />
						</div>
						<div class="min-w-0 flex-1">
							<div class="flex items-center justify-between gap-2">
								<span class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors truncate">Create Case Study</span>
								<span class="px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 text-[9px] font-black uppercase tracking-wider shrink-0">Full Editor</span>
							</div>
							<p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-snug">Rich story with text, images, videos & custom layouts.</p>
						</div>
					</Link>

					<!-- Option 2: Quick Work / Shot -->
					<button 
						@click.prevent="emit('open-add-work'); showCreateDropdown = false"
						class="w-full p-3 flex items-start gap-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-colors group text-left bg-transparent border-none cursor-pointer"
					>
						<div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0 group-hover:scale-105 transition-transform mt-0.5">
							<Zap class="w-4.5 h-4.5" />
						</div>
						<div class="min-w-0 flex-1">
							<div class="flex items-center justify-between gap-2">
								<span class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors truncate">Quick Work / Shot</span>
								<span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 text-[9px] font-black uppercase tracking-wider shrink-0">Instant</span>
							</div>
							<p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-snug">Fast upload for single image or quick portfolio shot.</p>
						</div>
					</button>
				</div>
			</div>
		</div>

		<!-- Loading state / Skeletons -->
		<div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-1.5 sm:gap-2">
			<Skeleton 
				v-for="n in 8" 
				:key="n"
				class="aspect-[4/3] rounded-[6px]"
			/>
		</div>

		<!-- Empty State (No projects at all) -->
		<div 
			v-else-if="!projects || projects.length === 0" 
			class="relative w-full min-h-[400px] rounded-[8px] overflow-hidden flex items-center justify-center p-6 bg-transparent"
		>
			<!-- Background grid of 3 rounded columns -->
			<div class="absolute inset-0 grid grid-cols-1 md:grid-cols-3 gap-6 opacity-85 pointer-events-none">
				<div class="bg-slate-50/50 dark:bg-slate-900/10 border border-slate-200/30 dark:border-slate-800/50 rounded-lg h-full"></div>
				<div class="bg-slate-50/50 dark:bg-slate-900/10 border border-slate-200/30 dark:border-slate-800/50 rounded-lg h-full"></div>
				<div class="bg-slate-50/50 dark:bg-slate-900/10 border border-slate-200/30 dark:border-slate-800/50 rounded-lg h-full"></div>
			</div>

			<!-- Overlaid Content -->
			<div v-if="isOwnProfile && isStudent" class="relative z-10 max-w-md flex flex-col items-center text-center p-4">
				<h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight mb-2">Feature your work</h2>
				<p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-6 max-w-xs leading-relaxed">
					Share quick snapshots of what you've been working on.
				</p>
				
				<div class="flex items-center gap-3 w-full sm:w-auto">
					<button 
						@click.prevent="emit('open-add-work')" 
						class="flex-1 sm:flex-none rounded-full bg-[#18181b] hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 text-white px-6 py-2.5 text-xs font-semibold shadow-xs text-center transition-colors cursor-pointer border-none"
					>
						Add work
					</button>
					<Link 
						href="/pagi/people" 
						class="flex-1 sm:flex-none rounded-full border border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800 bg-white dark:bg-slate-900 px-6 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-300 transition-colors shadow-2xs text-center"
					>
						Get inspired
					</Link>
				</div>

				<Link 
					href="/pagi/editor" 
					class="text-xs font-semibold text-slate-555 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 mt-6 inline-flex items-center gap-1 transition-colors"
				>
					<span>Or, add a full case study</span>
					<ChevronRight class="w-3.5 h-3.5" />
				</Link>
			</div>
			
			<div v-else-if="isOwnProfile && !isStudent" class="relative z-10 max-w-sm flex flex-col items-center text-center p-4">
				<h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight mb-2">No Projects Yet</h2>
				<p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-6 max-w-xs leading-relaxed">
					Belum ada karya yang diunggah.
				</p>
				
				<div class="flex items-center justify-center gap-3 w-full sm:w-auto">
					<Link 
						href="/pagi" 
						class="flex-1 sm:flex-none rounded-full bg-[#18181b] hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 text-white px-6 py-2.5 text-xs font-semibold shadow-xs text-center transition-colors"
					>
						Explore Works
					</Link>
				</div>
			</div>
			
			<div v-else class="relative z-10 max-w-sm flex flex-col items-center text-center p-4">
				<h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight mb-2">No Projects Yet</h2>
				<p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-6 max-w-xs leading-relaxed">
					{{ user.name }} hasn't posted any projects or case studies yet.
				</p>
				
				<div class="flex items-center justify-center gap-3 w-full sm:w-auto">
					<Link 
						href="/pagi" 
						class="flex-1 sm:flex-none rounded-full bg-[#18181b] hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 text-white px-6 py-2.5 text-xs font-semibold shadow-xs text-center transition-colors"
					>
						Explore Works
					</Link>
				</div>
			</div>
		</div>

		<!-- Tab Contents -->
		<template v-else>
			<!-- Created Tab -->
			<div v-if="activeWorkFilter === 'Created'">
				<CreatedTab 
					v-show="createdProjects.length > 0"
					:projects="createdProjects"
					:isOwnProfile="isOwnProfile"
					:user="user"
					@open-project="emit('open-project', $event)"
					@edit-quick-work="emit('edit-quick-work', $event)"
					@share-project="emit('share-project', $event)"
					@delete-project="(id, title) => emit('delete-project', id, title)"
					@reorder="handleReorder"
					@like-updated="emit('like-updated', $event)"
				/>
				<div v-if="createdProjects.length === 0" class="text-center py-14 px-4 bg-slate-50/30 dark:bg-slate-900/10 border border-dashed border-slate-200 dark:border-slate-800 rounded-2xl flex flex-col items-center justify-center space-y-3">
					<p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Belum ada karya cepat (Created).</p>
					<button v-if="isOwnProfile && isStudent" @click.prevent="emit('open-add-work')" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs cursor-pointer border-none">
						<Zap class="w-3.5 h-3.5" />
						<span>Tambah Karya Cepat</span>
					</button>
				</div>
			</div>

			<!-- Case Study Tab -->
			<div v-if="activeWorkFilter === 'Case Study'">
				<CaseStudyTab 
					v-show="caseStudyProjects.length > 0"
					:projects="caseStudyProjects"
					:isOwnProfile="isOwnProfile"
					:user="user"
					@open-project="emit('open-project', $event)"
					@share-project="emit('share-project', $event)"
					@delete-project="(id, title) => emit('delete-project', id, title)"
					@reorder="handleReorder"
					@like-updated="emit('like-updated', $event)"
				/>
				<div v-if="caseStudyProjects.length === 0" class="text-center py-14 px-4 bg-slate-50/30 dark:bg-slate-900/10 border border-dashed border-slate-200 dark:border-slate-800 rounded-2xl flex flex-col items-center justify-center space-y-3">
					<p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Belum ada case study yang dipublikasikan.</p>
					<Link v-if="isOwnProfile && isStudent" href="/pagi/editor" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-xs no-underline">
						<BookOpen class="w-3.5 h-3.5 text-white" />
						<span>Buat Case Study Sekarang</span>
					</Link>
				</div>
			</div>

			<!-- Collaborated Tab -->
			<div v-if="activeWorkFilter === 'Collaborated'">
				<CollaboratedTab 
					v-show="collaboratedProjects.length > 0"
					:projects="collaboratedProjects"
					:isOwnProfile="isOwnProfile"
					:user="user"
					@open-project="emit('open-project', $event)"
					@edit-quick-work="emit('edit-quick-work', $event)"
					@share-project="emit('share-project', $event)"
					@delete-project="(id, title) => emit('delete-project', id, title)"
					@report-project="emit('report-project', $event)"
					@reorder="handleReorder"
					@like-updated="emit('like-updated', $event)"
				/>
				<div v-if="collaboratedProjects.length === 0" class="text-center py-16 bg-slate-50/30 dark:bg-slate-900/10 border border-dashed border-slate-200 dark:border-slate-800 rounded-xl">
					<p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Belum ada project kolaborasi.</p>
				</div>
			</div>
		</template>
	</div>
</template>
