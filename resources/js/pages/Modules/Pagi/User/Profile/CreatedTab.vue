<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import axios from "axios";
import { Eye, Heart, Pencil, X } from "lucide-vue-next";
import { ref } from "vue";
import OptimizedImage from "../ui/OptimizedImage.vue";
import VideoLazy from "../ui/VideoLazy.vue";

const props = defineProps<{
	projects: any[];
	isOwnProfile: boolean;
	user: any;
}>();

const emit = defineEmits<{
	(e: "open-project", project: any): void;
	(e: "edit-quick-work", project: any): void;
	(e: "share-project", project: any): void;
	(e: "delete-project", id: number, title: string): void;
	(e: "reorder", orderIds: number[]): void;
	(
		e: "like-updated",
		data: { id: number; liked: boolean; count: number },
	): void;
}>();

const page = usePage();
const currentUserId = (page.props.auth as any)?.user?.id;

const activeProjectMenu = ref<number | null>(null);
const draggedIndex = ref<number | null>(null);
// local reactive like state: { [portfolioId]: { liked: boolean, count: number } }
const likeState = ref<Record<number, { liked: boolean; count: number }>>({});

const getLiked = (p: any) => likeState.value[p.id]?.liked ?? p.liked ?? false;
const getLikeCount = (p: any) => likeState.value[p.id]?.count ?? p.likes ?? 0;

const toggleLike = async (e: Event, p: any) => {
	e.stopPropagation();
	const prev = getLiked(p);
	const prevCount = getLikeCount(p);
	const nextLiked = !prev;
	const nextCount = prev ? prevCount - 1 : prevCount + 1;

	// Optimistic update
	likeState.value[p.id] = { liked: nextLiked, count: nextCount };
	p.liked = nextLiked;
	p.likes = nextCount;
	emit("like-updated", { id: p.id, liked: nextLiked, count: nextCount });

	try {
		const res = await axios.post(`/pagi/preview/${p.id}/like`);
		likeState.value[p.id] = { liked: res.data.liked, count: res.data.likes };
		p.liked = res.data.liked;
		p.likes = res.data.likes;
		emit("like-updated", {
			id: p.id,
			liked: res.data.liked,
			count: res.data.likes,
		});
	} catch {
		// Revert on failure
		likeState.value[p.id] = { liked: prev, count: prevCount };
		p.liked = prev;
		p.likes = prevCount;
		emit("like-updated", { id: p.id, liked: prev, count: prevCount });
	}
};

const toggleProjectMenu = (id: number) => {
	activeProjectMenu.value = activeProjectMenu.value === id ? null : id;
};
const closeProjectMenu = () => {
	activeProjectMenu.value = null;
};

const isVideoUrl = (url: string) => {
	if (!url) return false;
	return ["mp4", "webm", "mov", "avi", "mkv", "3gp"].includes(
		url.split(".").pop()?.toLowerCase() || "",
	);
};

const getCoverFit = (project: any) => {
	if (!project?.content || !Array.isArray(project.content)) return "cover";
	const details = project.content.find(
		(b: any) => b?.type === "featured_details",
	);
	return details?.cover_fit || "cover";
};

/** Show project creator + accepted collaborators. Creator is always first. Max 5 + overflow. */
const getCollaborationData = (project: any) => {
	const accepted = (project.resolved_collaborators || []).filter(
		(c: any) => c.status === "accepted",
	);
	if (accepted.length === 0) return null; // no badge without at least 1 accepted

	// Build list: owner first, then accepted collabs (skip duplicate)
	const all: any[] = [];
	if (project.user) {
		all.push({
			id: project.user.id,
			name: project.user.name,
			pagi_username: project.user.pagi_username,
			avatar: project.user.avatar,
		});
	}
	for (const c of accepted) {
		if (!all.some((p: any) => p.id === c.id)) all.push(c);
	}

	const MAX = 5;
	return {
		visible: all.slice(0, MAX),
		overflow: Math.max(0, all.length - MAX),
	};
};

const profileHref = (collab: any) =>
	collab.pagi_username
		? `/pagi/${collab.pagi_username}`
		: `/pagi/profile/${collab.id}`;

const onDragStart = (index: number) => {
	if (!props.isOwnProfile) return;
	draggedIndex.value = index;
};
const onDragOver = (e: DragEvent) => e.preventDefault();
const onDrop = (index: number) => {
	if (!props.isOwnProfile || draggedIndex.value === null) return;
	const items = [...props.projects];
	const [item] = items.splice(draggedIndex.value, 1);
	items.splice(index, 0, item);
	draggedIndex.value = null;
	emit(
		"reorder",
		items.map((p) => p.id),
	);
};
</script>

<template>
	<div @click="closeProjectMenu" class="space-y-6">
		<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-1.5 sm:gap-2">
			<div
				v-for="(p, idx) in projects"
				:key="p.id"
				class="group relative cursor-pointer aspect-[4/3] rounded-[6px] overflow-hidden border border-slate-200/80 dark:border-slate-800 bg-slate-100 dark:bg-slate-900 shadow-2xs hover:shadow-md transition-all duration-300"
				:class="{ 'opacity-50': draggedIndex === idx }"
				:draggable="isOwnProfile"
				@dragstart="onDragStart(idx)"
				@dragover.prevent="onDragOver"
				@drop="onDrop(idx)"
				@click.stop="emit('open-project', p)"
			>
				<!-- Card media -->
				<div 
					class="absolute inset-0 z-0 flex items-center justify-center overflow-hidden transition-colors duration-300"
					:class="getCoverFit(p) === 'contain' ? 'bg-slate-950' : 'bg-slate-50 dark:bg-slate-900'"
				>
					<template v-if="getCoverFit(p) === 'contain'">
						<VideoLazy v-if="isVideoUrl(p.image)" :src="p.image" :autoplay="true" :loop="true" :muted="true" :playsinline="true" className="absolute inset-0 h-full w-full object-cover blur-xl opacity-40 scale-110 pointer-events-none" />
						<OptimizedImage v-else :src="p.image" :alt="p.title" :fetchpriority="idx < 8 ? 'high' : 'auto'" :loading="idx < 8 ? 'eager' : 'lazy'" className="absolute inset-0 h-full w-full object-cover blur-xl opacity-40 scale-110 pointer-events-none" />
					</template>
					<VideoLazy v-if="isVideoUrl(p.image)" :src="p.image" :autoplay="true" :loop="true" :muted="true" :playsinline="true" :className="getCoverFit(p) === 'contain' ? 'max-w-full max-h-full object-contain z-10 relative transition-transform duration-500 group-hover:scale-102' : 'h-full w-full object-cover z-10 relative transition-transform duration-500 group-hover:scale-105'" />
					<OptimizedImage v-else :src="p.image" :alt="p.title" :fetchpriority="idx < 8 ? 'high' : 'auto'" :loading="idx < 8 ? 'eager' : 'lazy'" :className="getCoverFit(p) === 'contain' ? 'max-w-full max-h-full object-contain z-10 relative transition-transform duration-500 group-hover:scale-102' : 'h-full w-full object-cover z-10 relative transition-transform duration-500 group-hover:scale-105'" />
				</div>

				<!-- Top-left badges: Draft + Collaboration stacked -->
				<div class="absolute top-3 left-3 z-10 flex flex-col items-start gap-1.5">
					<div v-if="p.is_published === false" class="px-2 py-0.5 rounded-md bg-zinc-950/85 backdrop-blur-xs text-white text-[9px] font-black uppercase tracking-wider shadow-md border border-zinc-800">
						Draft
					</div>
					<!-- Collaboration badge with clickable avatars -->
					<div v-if="getCollaborationData(p)" class="flex items-center gap-1.5 bg-black/60 backdrop-blur-sm px-2 py-1 rounded-full border border-white/10 select-none">
						<div class="flex items-center">
							<template v-for="(collab, ci) in getCollaborationData(p)!.visible" :key="collab.id">
								<Link
									:href="profileHref(collab)"
									class="relative w-4 h-4 rounded-full overflow-hidden border border-white/30 bg-slate-700 shrink-0 flex items-center justify-center hover:scale-110 transition-transform"
									:style="ci > 0 ? 'margin-left: -5px;' : ''"
									:title="collab.pagi_username || collab.name"
									@click.stop
								>
									<img v-if="collab.avatar" :src="collab.avatar" :alt="collab.name" class="w-full h-full object-cover" />
									<span v-else class="text-[7px] font-black text-white leading-none">{{ (collab.name || '?').charAt(0).toUpperCase() }}</span>
								</Link>
							</template>
							<div v-if="getCollaborationData(p)!.overflow > 0" class="relative w-4 h-4 rounded-full bg-indigo-600 border border-white/30 shrink-0 flex items-center justify-center text-white font-black leading-none" style="margin-left: -5px; font-size: 6px;">
								+{{ getCollaborationData(p)!.overflow }}
							</div>
						</div>
						<span class="text-[9px] font-bold text-white/90 leading-none tracking-wide">Collaboration</span>
					</div>
				</div>

				<!-- Hover Actions Pill -->
				<div v-if="isOwnProfile" class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity z-20 flex items-center bg-white/95 dark:bg-slate-900/95 backdrop-blur-xs border border-slate-200 dark:border-slate-800 rounded-full p-1 shadow-md gap-0.5 select-none">
					<button @click.stop="toggleProjectMenu(p.id)" class="w-7 h-7 rounded-full flex items-center justify-center text-slate-600 dark:text-slate-355 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer transition-colors border-none bg-transparent" title="Options">
						<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6 10a2 2 0 110 4 2 2 0 010-4zm6 0a2 2 0 110 4 2 2 0 010-4zm6 0a2 2 0 110 4 2 2 0 010-4z" /></svg>
					</button>
					<div class="w-7 h-7 rounded-full flex items-center justify-center text-slate-600 dark:text-slate-355 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-grab active:cursor-grabbing transition-colors" title="Drag to reorder">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9h.01M12 9h.01M16 9h.01M8 15h.01M12 15h.01M16 15h.01" /></svg>
					</div>
					<!-- Dropdown -->
					<div v-if="activeProjectMenu === p.id" class="absolute top-full right-0 mt-2 w-48 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl rounded-2xl py-1.5 z-30 overflow-hidden">
						<button @click.stop="emit('edit-quick-work', p); closeProjectMenu()" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer text-left border-none bg-transparent">
							<Pencil class="w-3.5 h-3.5 text-slate-500" /><span>Edit details</span>
						</button>
						<button @click.stop="emit('share-project', p); closeProjectMenu()" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer text-left border-none bg-transparent">
							<svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.684 10.742L12.02 12.2a2.003 2.003 0 11-.868 1.983L7.816 12.72a2.003 2.003 0 110-1.44l3.336-1.464a2.003 2.003 0 11.868 1.983L8.684 10.742z" /></svg>
							<span>Share link</span>
						</button>
						<button @click.stop="emit('delete-project', p.id, p.title); closeProjectMenu()" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-red-650 hover:bg-red-50 dark:hover:bg-red-950/20 border-t border-slate-100 dark:border-slate-800 mt-1 pt-2.5 transition-colors cursor-pointer text-left bg-transparent">
							<X class="w-3.5 h-3.5 text-red-500" /><span>Remove from profile</span>
						</button>
					</div>
				</div>

				<!-- Hover Overlay: Gradient & Details with interactive Like -->
				<div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-col justify-end p-4 z-10 select-none">
					<h3 class="text-white text-xs sm:text-sm font-bold tracking-wide line-clamp-2 leading-snug drop-shadow-md mb-2 pointer-events-none">{{ p.title }}</h3>
					<div class="flex items-center gap-3 text-[10px] text-white/90 font-medium">
						<!-- Clickable Like button -->
						<button
							@click.stop="toggleLike($event, p)"
							class="flex items-center gap-1 cursor-pointer border-none bg-transparent p-0 group/like transition-colors"
							:class="getLiked(p) ? 'text-red-400' : 'text-white/90 hover:text-red-400'"
							:title="getLiked(p) ? 'Unlike' : 'Like'"
						>
							<Heart
								class="w-3.5 h-3.5 transition-all duration-200"
								:class="getLiked(p) ? 'fill-red-400 stroke-red-400 scale-110' : 'fill-white/20 stroke-white group-hover/like:fill-red-400 group-hover/like:stroke-red-400'"
							/>
							<span>{{ getLikeCount(p) }}</span>
						</button>
						<span class="flex items-center gap-1 pointer-events-none"><Eye class="w-3.5 h-3.5 stroke-[2]" /> {{ p.views }}</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
