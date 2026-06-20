<script setup lang="ts">
import {
	ArrowUpDown,
	ChevronDown,
	Edit2,
	LayoutGrid,
	Link2,
	Paperclip,
	PlusCircle,
	X,
} from "lucide-vue-next";
import { ref, watch } from "vue";
import { sanitize } from "@/composables/useSanitize";
import {
	getJustifiedLayout as computeLayout,
	loadImageAspectRatios as loadRatios,
	normalizeSrc,
} from "@/utils/justifiedLayout";
import OptimizedImage from "../ui/OptimizedImage.vue";
import VideoLazy from "../ui/VideoLazy.vue";
import PagiTiptapEditor from "./PagiTiptapEditor.vue";

const props = defineProps<{
	form: any;
	canvasBgColor: string;
	canvasTextColor: string;
	spacingInPx: number;
	containerWidth: number;
	getContainerWidth: (isFullWidth: boolean) => number;
	contentOptions: any[];
	isPreviewMode: boolean;
	canvasContainerRef: (el: any) => void;
}>();

const emit = defineEmits<{
	(e: "add-block", type: string): void;
	(e: "trigger-block-file-input", index: number): void;
	(e: "delete-block", index: number): void;
}>();

const activeEditMenu = ref<number | null>(null);
const editingGridIndex = ref<number | null>(null);
const focusedTextIndex = ref<number | null>(null);

// flickr justified layout engine aspect ratios ref
const aspectRatios = ref<Record<string, number>>({});

const handleImageLoad = (src: string, event: Event) => {
	const img = event.target as HTMLImageElement;
	if (img.naturalWidth && img.naturalHeight) {
		aspectRatios.value = {
			...aspectRatios.value,
			[normalizeSrc(src)]: img.naturalWidth / img.naturalHeight,
		};
	}
};

const getAspectRatio = (src: string) => {
	return aspectRatios.value[normalizeSrc(src)] || 1.5;
};

const loadImageAspectRatios = (previews: string[]) => {
	loadRatios(previews, aspectRatios);
};

const getJustifiedLayout = (
	previews: string[],
	containerWidth: number,
	targetHeight: number,
	gap: number,
) => {
	return computeLayout(
		previews,
		containerWidth,
		targetHeight,
		gap,
		getAspectRatio,
	);
};

const getBlockContainerWidth = (block: any) => {
	let width = props.getContainerWidth(block.isFullWidth);
	if (block.hasPadding) {
		const isMobile =
			typeof window !== "undefined" ? window.innerWidth < 768 : false;
		width -= isMobile ? 64 : 128;
	}
	return width;
};

const removeImageFromGrid = (gridIndex: number, src: string) => {
	const block = props.form.content[gridIndex];
	if (!block) return;
	const idx = block.previews.indexOf(src);
	if (idx !== -1) {
		block.previews.splice(idx, 1);
		if (block.files) block.files.splice(idx, 1);
		if (block.fileKeys) block.fileKeys.splice(idx, 1);
		if (block.aspectRatios) block.aspectRatios.splice(idx, 1);
	}
};

// watch content to load aspect ratios reactively
watch(
	() => props.form.content,
	(newContent) => {
		if (!newContent) return;
		newContent.forEach((block: any) => {
			if (block.type === "photo_grid" && block.previews) {
				if (block.aspectRatios && Array.isArray(block.aspectRatios)) {
					block.previews.forEach((src: string, idx: number) => {
						const ar = block.aspectRatios[idx];
						if (ar) {
							aspectRatios.value[normalizeSrc(src)] = ar;
						}
					});
				}
				loadImageAspectRatios(block.previews);
			}
		});
	},
	{ deep: true, immediate: true },
);

// safe block check helpers
const isVideoBlock = (block: any) => {
	if (!block) return false;
	if (block.mimeType) return block.mimeType.startsWith("video");
	if (block.file?.type) return block.file.type.startsWith("video");
	if (block.name) {
		const ext = block.name.split(".").pop()?.toLowerCase();
		return ["mp4", "webm", "ogg", "mov", "m4v", "3gp"].includes(ext || "");
	}
	if (block.preview) {
		const ext = block.preview.split(".").pop()?.split("?")[0].toLowerCase();
		return ["mp4", "webm", "ogg", "mov", "m4v", "3gp"].includes(ext || "");
	}
	return false;
};

const isAudioBlock = (block: any) => {
	if (!block) return false;
	if (block.mimeType) return block.mimeType.startsWith("audio");
	if (block.file?.type) return block.file.type.startsWith("audio");
	if (block.name) {
		const ext = block.name.split(".").pop()?.toLowerCase();
		return ["mp3", "wav", "ogg", "aac", "flac", "m4a"].includes(ext || "");
	}
	if (block.preview) {
		const ext = block.preview.split(".").pop()?.split("?")[0].toLowerCase();
		return ["mp3", "wav", "ogg", "aac", "flac", "m4a"].includes(ext || "");
	}
	return false;
};
</script>

<template>
	<main class="flex-1 overflow-y-auto p-0 transition-colors duration-300" :style="{ backgroundColor: canvasBgColor }">
		<div class="w-full border-none rounded-none min-h-[calc(100vh-64px)] flex flex-col items-center justify-start relative transition-colors duration-300" :style="{ backgroundColor: canvasBgColor, color: canvasTextColor }" @click="focusedTextIndex = null; activeEditMenu = null">

			<!-- Center Empty State centered vertically and horizontally -->
			<div v-if="form.content.filter((b: any) => b.type !== 'settings').length === 0" class="text-center w-full flex-1 flex flex-col items-center justify-center p-10 select-none">
				<h2 class="text-xl font-medium mb-10" :style="{ color: canvasTextColor }">Start building your project:</h2>
				<div class="flex flex-wrap justify-center gap-6">
					<button v-for="item in contentOptions" :key="'center-'+item.id" @click="emit('add-block', item.id)"
						class="flex flex-col items-center justify-center group w-24 gap-3 cursor-pointer">
						<div class="h-16 w-16 rounded-xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-center shadow-sm group-hover:shadow-md group-hover:border-slate-300 dark:group-hover:border-slate-700 transition-all duration-200">
							<component :is="item.icon" class="h-6 w-6 text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white transition-colors" stroke-width="1.5" />
						</div>
						<span class="text-xs font-semibold transition-colors" :style="{ color: canvasTextColor }">{{ item.label }}</span>
					</button>
				</div>
			</div>

			<!-- Full-Width Content Canvas -->
			<div v-else :ref="canvasContainerRef" class="w-full max-w-7xl border-x border-t border-slate-200 dark:border-slate-800 rounded-t-3xl min-h-[calc(100vh-64px)] mt-10 shadow-2xl flex flex-col overflow-hidden transition-colors duration-300" :style="{ backgroundColor: canvasBgColor }">

				<template v-for="(block, index) in form.content" :key="index">
					<div v-if="block.type !== 'settings'"
						 class="w-full relative group transition-all rounded-none"
						 :style="{ marginBottom: spacingInPx + 'px' }">

						<!-- Pencil Icon & Edit Menu Wrapper -->
						<div v-if="!isPreviewMode" class="absolute left-4 top-4 z-20 flex items-start opacity-0 group-hover:opacity-100 transition-opacity group/menu">
							<!-- Neutral Pencil Icon -->
							<button type="button" class="bg-slate-950/80 dark:bg-slate-50/80 hover:bg-slate-900 dark:hover:bg-white text-white dark:text-slate-950 p-2.5 rounded-xl border border-slate-800/20 shadow-md cursor-pointer transition-all">
								<Edit2 class="w-4 h-4" />
							</button>

							<!-- Dropdown Menu -->
							<div class="hidden group-hover/menu:block ml-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-lg py-1.5 w-48 text-sm">
								<button class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-colors">Reorder Project</button>

								<template v-if="block.type === 'image'">
									<button class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-355 transition-colors">Add Caption</button>
									<button @click.stop="block.isFullWidth = !block.isFullWidth" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-colors flex items-center justify-between">
										<span>Lebar Penuh</span>
										<span class="text-[10px] font-bold text-slate-400 dark:text-slate-550">{{ block.isFullWidth ? 'Ya' : 'Tidak' }}</span>
									</button>
									<button @click.stop="block.hasPadding = !block.hasPadding" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-colors flex items-center justify-between">
										<span>Bantalan Sisi</span>
										<span class="text-[10px] font-bold text-slate-400 dark:text-slate-550">{{ block.hasPadding ? 'Aktif' : 'Mati' }}</span>
									</button>
									<button @click.stop="emit('trigger-block-file-input', Number(index))" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-colors">Replace Image</button>
									<button @click.stop="emit('delete-block', Number(index))" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800/50 text-red-650 dark:text-red-400 font-medium transition-colors">Delete Image</button>
								</template>

								<template v-else-if="block.type === 'photo_grid'">
									<button @click.stop="editingGridIndex = Number(index)" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-colors">Edit Grid</button>
									<button @click.stop="block.isFullWidth = !block.isFullWidth" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-colors flex items-center justify-between">
										<span>Lebar Penuh</span>
										<span class="text-[10px] font-bold text-slate-400 dark:text-slate-550">{{ block.isFullWidth ? 'Ya' : 'Tidak' }}</span>
									</button>
									<button @click.stop="block.hasPadding = !block.hasPadding" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-colors flex items-center justify-between">
										<span>Bantalan Sisi</span>
										<span class="text-[10px] font-bold text-slate-400 dark:text-slate-550">{{ block.hasPadding ? 'Aktif' : 'Mati' }}</span>
									</button>
									<button @click.stop="block.hasGap = block.hasGap === false" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-colors flex items-center justify-between">
										<span>Jarak Grid (4px)</span>
										<span class="text-[10px] font-bold text-slate-400 dark:text-slate-550">{{ block.hasGap !== false ? 'Aktif' : 'Mati' }}</span>
									</button>
									<button @click.stop="emit('delete-block', Number(index))" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800/50 text-red-650 dark:text-red-400 font-medium transition-colors">Delete Grid</button>
								</template>

								<template v-else-if="block.type === 'video_audio'">
									<button class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-colors">Add Caption</button>
									<button @click.stop="block.isFullWidth = !block.isFullWidth" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-colors flex items-center justify-between">
										<span>Lebar Penuh</span>
										<span class="text-[10px] font-bold text-slate-400 dark:text-slate-550">{{ block.isFullWidth ? 'Ya' : 'Tidak' }}</span>
									</button>
									<button @click.stop="block.hasPadding = !block.hasPadding" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-colors flex items-center justify-between">
										<span>Bantalan Sisi</span>
										<span class="text-[10px] font-bold text-slate-400 dark:text-slate-550">{{ block.hasPadding ? 'Aktif' : 'Mati' }}</span>
									</button>
									<button @click.stop="emit('trigger-block-file-input', Number(index))" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-colors">Replace Media</button>
									<button @click.stop="emit('delete-block', Number(index))" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800/50 text-red-650 dark:text-red-400 font-medium transition-colors">Delete Media</button>
								</template>

								<template v-else-if="block.type === 'text'">
									<button @click.stop="emit('delete-block', Number(index))" class="w-full text-left px-4 py-2 hover:bg-gray-50 dark:hover:bg-slate-800/50 text-red-650 dark:text-red-400 font-medium transition-colors">Delete Text</button>
								</template>

								<template v-else-if="block.type === 'asset'">
									<button @click.stop="emit('delete-block', Number(index))" class="w-full text-left px-4 py-2 hover:bg-gray-50 dark:hover:bg-slate-800/50 text-red-650 dark:text-red-400 font-medium transition-colors">Delete Asset</button>
								</template>
							</div>
						</div>

						<!-- If type is Text -->
						<div v-if="block.type === 'text'"
							 :class="[
								 'w-full max-w-4xl mx-auto px-6 transition-all duration-200 rounded-xl relative group/text',
								 focusedTextIndex === Number(index)
									 ? 'border-2 border-blue-500 ring-4 ring-blue-500/10 p-4 bg-transparent shadow-sm'
									 : 'border border-dashed border-transparent hover:border-slate-355 dark:hover:border-slate-700 p-4'
							 ]"
							 @click.stop="focusedTextIndex = Number(index); activeEditMenu = null">

							<!-- Tiptap Editor Component -->
							<PagiTiptapEditor
								v-model="block.value"
								:canvas-text-color="canvasTextColor"
								:is-preview-mode="isPreviewMode"
								:is-focused="focusedTextIndex === Number(index)"
								@focus="focusedTextIndex = Number(index); activeEditMenu = null"
							/>
						</div>

						<!-- If type is Image -->
						<div v-else-if="block.type === 'image'" class="w-full flex flex-col items-center">
							<div class="w-full overflow-hidden select-none"
								 :class="[ 
									 block.isFullWidth ? 'max-w-none' : 'max-w-4xl px-6 rounded-xl',
									 block.hasPadding ? 'px-8 md:px-16' : ''
								 ]">
								<OptimizedImage v-if="block.preview" :src="normalizeSrc(block.preview)"
									class="w-full h-auto object-cover max-h-[85vh] select-none pointer-events-none rounded-none shadow-sm"
									alt="Uploaded content image" />
							</div>
							<p v-if="block.caption" class="text-xs font-semibold text-slate-500 mt-2 px-6 text-center leading-normal max-w-lg">{{ block.caption }}</p>
						</div>

						<!-- If type is Photo Grid justified using advanced layout -->
						<div v-else-if="block.type === 'photo_grid' && block.previews && block.previews.length > 0"
							 class="w-full flex flex-col items-center select-none">
							<div class="w-full flex flex-col overflow-hidden"
								 :class="[ 
									 block.isFullWidth ? 'max-w-none' : 'max-w-4xl px-6 rounded-xl',
									 block.hasPadding ? 'px-8 md:px-16' : ''
								 ]"
								 :style="{ gap: (block.hasGap !== false ? 4 : 0) + 'px' }">
								<div v-for="(row, rIdx) in getJustifiedLayout(block.previews, getBlockContainerWidth(block), 380, block.hasGap !== false ? 4 : 0)" :key="rIdx"
									 class="w-full flex justify-start animate-fade-in"
									 :style="{ gap: (block.hasGap !== false ? 4 : 0) + 'px' }">
									<div v-for="p in row.items" :key="p"
										 class="overflow-hidden bg-slate-50 dark:bg-slate-900 border border-slate-200/20"
										 :style="{
											 width: (row.height * getAspectRatio(p)) + 'px',
											 flexGrow: row.isLast ? 0 : getAspectRatio(p),
											 flexShrink: row.isLast ? 0 : 1,
											 flexBasis: 'auto',
											 height: row.height + 'px'
										 }">
										<img :src="normalizeSrc(p)"
											 class="w-full h-full object-cover select-none pointer-events-none border-none"
											 @load="handleImageLoad(p, $event)"
											 loading="lazy" alt="Grid image element" />
									</div>
								</div>
							</div>
						</div>

						<!-- If type is Video -->
						<div v-else-if="block.type === 'video_audio'" class="w-full flex flex-col items-center select-none">
							<div class="w-full overflow-hidden"
								 :class="[ 
									 block.isFullWidth ? 'max-w-none' : 'max-w-4xl px-6 rounded-xl',
									 block.hasPadding ? 'px-8 md:px-16' : ''
								 ]">
								<VideoLazy v-if="block.preview && isVideoBlock(block)" :src="normalizeSrc(block.preview)" :muted="true" class="w-full h-auto max-h-[85vh] object-cover rounded-none" />
							</div>
							<p v-if="block.caption" class="text-xs font-semibold text-slate-500 mt-2 px-6 text-center leading-normal max-w-lg">{{ block.caption }}</p>
						</div>

						<!-- If type is Attached Asset Link -->
						<div v-else-if="block.type === 'asset'" class="w-full px-6 max-w-4xl mx-auto select-none">
							<div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-5 flex items-center justify-between hover:shadow-md transition-shadow">
								<div class="flex items-center gap-3.5 overflow-hidden">
									<div class="h-10 w-10 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
										<Paperclip class="h-5 w-5" />
									</div>
									<div class="flex flex-col overflow-hidden">
										<span class="text-xs font-black text-slate-900 dark:text-white truncate">{{ block.name }}</span>
										<span class="text-[10px] text-slate-400 font-bold truncate mt-0.5">{{ block.link }}</span>
									</div>
								</div>
								<a :href="block.link" target="_blank" rel="noopener noreferrer"
								   class="h-9 px-4 rounded-xl bg-slate-100 dark:bg-slate-900 hover:bg-slate-200 dark:hover:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 transition-colors flex items-center justify-center gap-1.5 shrink-0 shadow-3xs">
									<span>Open Resource</span>
									<Link2 class="h-4 w-4" />
								</a>
							</div>
						</div>
					</div>
				</template>

				<!-- Add more content footer (Centered with margin) -->
				<div class="w-full max-w-4xl mx-auto px-6 border-t border-slate-150 dark:border-slate-800 mt-10 pt-10 pb-32 md:pb-16 flex flex-col items-center">
					<h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 mb-6 uppercase tracking-wider">Add more content</h3>
					<div class="flex flex-wrap justify-center gap-4">
						<button v-for="item in contentOptions" :key="'add-'+item.id" @click="emit('add-block', item.id)"
							class="flex flex-col items-center justify-center group w-20 gap-2 cursor-pointer bg-transparent border-none">
							<div class="h-12 w-12 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center shadow-sm group-hover:shadow-md group-hover:border-slate-300 dark:group-hover:border-slate-700 transition-all duration-200">
								<component :is="item.icon" class="h-5 w-5 text-slate-700 dark:text-slate-300 group-hover:text-slate-950 dark:group-hover:text-white transition-colors" stroke-width="1.5" />
							</div>
							<span class="text-[10px] font-semibold text-slate-550 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-slate-200 transition-colors">{{ item.label }}</span>
						</button>
					</div>
				</div>

			</div>
		</div>
	</main>

	<!-- EDIT GRID MODAL OVERLAY -->
	<div v-if="editingGridIndex !== null" class="fixed inset-0 z-[10005] bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4" @click.self="editingGridIndex = null">
		<!-- Modal Card Container -->
		<div class="w-full max-w-5xl h-[80vh] md:h-[85vh] bg-slate-100 dark:bg-slate-950 rounded-2xl shadow-2xl flex flex-col overflow-hidden border border-slate-200/50 dark:border-slate-800/50 transition-all duration-300">
			
			<!-- Modal Header -->
			<div class="h-16 border-b border-slate-200/60 dark:border-slate-800/60 flex items-center justify-between px-6 bg-white dark:bg-slate-900 shadow-3xs shrink-0">
				<h2 class="text-sm font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider">Edit Grid</h2>
				<div class="flex items-center gap-3">
					<!-- Sort / Custom Dropdown -->
					<button type="button" class="flex items-center gap-1.5 border border-slate-200 dark:border-slate-800 px-3.5 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 bg-white dark:bg-slate-950 transition-colors shadow-3xs cursor-pointer">
						<ArrowUpDown class="w-3.5 h-3.5 text-slate-500" />
						<span>Custom</span>
						<ChevronDown class="w-3.5 h-3.5 text-slate-400" />
					</button>
					<!-- Add Photos Dropdown -->
					<button type="button" @click="emit('trigger-block-file-input', editingGridIndex)" class="flex items-center gap-1.5 border border-slate-200 dark:border-slate-800 px-3.5 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 bg-white dark:bg-slate-950 transition-colors shadow-3xs cursor-pointer">
						<PlusCircle class="w-3.5 h-3.5 text-blue-500" />
						<span>Add Photos</span>
						<ChevronDown class="w-3.5 h-3.5 text-slate-400" />
					</button>

					<div class="h-4 w-px bg-slate-200 dark:bg-slate-800 mx-1"></div>

					<!-- Cancel / Done -->
					<button type="button" @click="editingGridIndex = null" class="px-4.5 py-1.5 border border-slate-200 dark:border-slate-800 text-[10px] font-black uppercase tracking-wider text-slate-700 dark:text-slate-355 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-full transition-colors cursor-pointer shadow-3xs bg-transparent">Cancel</button>
					<button type="button" @click="editingGridIndex = null" class="bg-blue-600 hover:bg-blue-700 text-white px-5.5 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider transition-colors shadow-xs cursor-pointer border-none">Done</button>
				</div>
			</div>

			<!-- Modal Body (Centered justified photo grid container) -->
			<div class="flex-1 overflow-y-auto p-6 flex flex-col items-center justify-start">
				<div class="w-full bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800/60 rounded-xl shadow-xs p-6 flex flex-col overflow-hidden min-h-[50vh]">
					<div v-if="form.content[editingGridIndex].previews && form.content[editingGridIndex].previews.length > 0"
						 class="w-full flex flex-col" :style="{ gap: (form.content[editingGridIndex].hasGap !== false ? 4 : 0) + 'px' }">
						<div v-for="(row, rIdx) in getJustifiedLayout(form.content[editingGridIndex].previews, 920, 320, form.content[editingGridIndex].hasGap !== false ? 4 : 0)" :key="rIdx"
							 class="w-full flex justify-start animate-fade-in"
							 :style="{ gap: (form.content[editingGridIndex].hasGap !== false ? 4 : 0) + 'px' }">
							<div v-for="p in row.items" :key="p"
								 class="relative overflow-hidden group/item transition-transform duration-300 hover:scale-[1.01] rounded-lg"
								 :style="{
									 width: (row.height * getAspectRatio(p)) + 'px',
									 flexGrow: row.isLast ? 0 : getAspectRatio(p),
									 flexShrink: row.isLast ? 0 : 1,
									 flexBasis: 'auto',
									 height: row.height + 'px'
								 }">
								<img :src="normalizeSrc(p)"
									 class="w-full h-full object-cover border-none shadow-none"
									 loading="lazy" alt="Grid item" />

								<!-- Absolute X close button on top right -->
								<button type="button" @click.stop="removeImageFromGrid(Number(editingGridIndex), p)"
										class="absolute top-2.5 right-2.5 w-6 h-6 rounded-full bg-slate-950/60 hover:bg-slate-950/90 text-white flex items-center justify-center backdrop-blur-xs transition-all shadow-md cursor-pointer border-none z-10">
									<X class="w-3.5 h-3.5" />
								</button>
							</div>
						</div>
					</div>
					<div v-else class="w-full py-20 flex flex-col items-center justify-center text-slate-400 dark:text-slate-650 flex-1">
						<!-- Empty state -->
						<LayoutGrid class="w-10 h-10 mb-2 opacity-50" />
						<p class="font-bold text-xs uppercase tracking-wider">No photos in this grid.</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
