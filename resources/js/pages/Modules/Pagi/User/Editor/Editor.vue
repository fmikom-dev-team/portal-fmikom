<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import {
	AlertCircle,
	CheckCircle2,
	Image as ImageIcon,
	LayoutGrid,
	PlaySquare,
	Type,
	X,
} from "lucide-vue-next";
import { onMounted, onUnmounted, ref } from "vue";
import { sanitize } from "@/composables/useSanitize";
import { usePagiProgress } from "../../shared/composables/usePagiProgress";
import { useToast } from "../../shared/composables/useToast";
import Navbar from "../ui/Navbar.vue";
import Preview from "../ui/Preview.vue";
import EditorCanvas from "./EditorCanvas.vue";
import EditorPublishModal from "./EditorPublishModal.vue";
import EditorSidebar from "./EditorSidebar.vue";
import { useEditorCanvas } from "./useEditorCanvas";
import { useEditorCollaborators } from "./useEditorCollaborators";
import { idbClear, idbGet, useEditorDraft } from "./useEditorDraft";
import { useEditorFileUpload } from "./useEditorFileUpload";
import { useEditorTags } from "./useEditorTags";

const { trackInertiaForm } = usePagiProgress();

const props = defineProps<{
	editor?: any;
	mockImages?: any;
}>();

// Toast Notification System
const { toasts, addToast, removeToast } = useToast();

const form = useForm({
	title: "",
	content: [] as Array<any>,
	is_published: false,
	cover_image: null as File | string | null,
	tags: "",
	category: "",
	tools_used: "",
	description: "",
	visibility: "Everyone",
	collaborators: [] as string[],
});

// Autocomplete tags & query state variables
const {
	categoryInput,
	categoryTags,
	showCategoryDropdown,
	toolsInput,
	toolsTags,
	showToolsDropdown,
	filteredCategorySuggestions,
	filteredToolsSuggestions,
	addCategoryTag,
	removeCategoryTag,
	addToolTag,
	removeToolTag,
} = useEditorTags();

const {
	collaboratorInput,
	showCollaboratorDropdown,
	collaboratorSuggestions,
	isLoadingCollaborators,
	handleCollaboratorSearch,
	addCollaboratorChip,
	removeCollaboratorChip,
} = useEditorCollaborators(form);

const {
	globalSpacing,
	canvasBgColor,
	canvasTextColor,
	canvasBorderColor,
	activeSidebarTab,
	coverFit,
	stylePresets,
	updateGlobalSettingsBlock,
	spacingInPx,
	canvasContainer,
	containerWidth,
	getContainerWidth,
	updateContainerWidth,
} = useEditorCanvas(form);

const setCanvasContainer = (el: any) => {
	canvasContainer.value = el;
};

const contentOptions = [
	{ id: "image", label: "Image", icon: ImageIcon, color: "text-blue-500" },
	{ id: "text", label: "Text", icon: Type, color: "text-blue-500" },
	{
		id: "photo_grid",
		label: "Photo Grid",
		icon: LayoutGrid,
		color: "text-blue-500",
	},
	{
		id: "video_audio",
		label: "Video",
		icon: PlaySquare,
		color: "text-blue-500",
	},
];

const showPublishModal = ref(false);
const isPreviewMode = ref(false);

const {
	coverPreview,
	isCoverVideo,
	triggerFileInput,
	triggerFileInputForBlock,
} = useEditorFileUpload(form, addToast);

// Asset Link Modal State
const showAssetLinkModal = ref(false);
const assetNameInput = ref("");
const assetLinkInput = ref("");

const openAssetLinkModal = () => {
	assetNameInput.value = "";
	assetLinkInput.value = "";
	showAssetLinkModal.value = true;
};

const closeAssetLinkModal = () => {
	showAssetLinkModal.value = false;
};

const addAssetLinkBlock = () => {
	const name = assetNameInput.value.trim();
	let link = assetLinkInput.value.trim();

	if (!name) {
		addToast("Nama/label aset wajib diisi!", "error");
		return;
	}
	if (!link) {
		addToast("Tautan/URL aset wajib diisi!", "error");
		return;
	}

	if (!/^https?:\/\//i.test(link)) {
		link = `https://${link}`;
	}

	form.content.push({
		type: "asset",
		name,
		link,
	});

	closeAssetLinkModal();
	addToast("Aset tautan berhasil ditambahkan!", "success");
};

// Form Helper Functions
const deleteBlock = (index: number) => {
	form.content.splice(index, 1);
};

const handleAddClick = (type: string) => {
	if (type === "text") {
		form.content.push({
			type: "text",
			value: "",
			initialValue: "",
			align: "left",
		});
	} else {
		triggerFileInput(type);
	}
};

const saveAsDraft = () => {
	form.is_published = false;
	submitForm();
};

const publishProject = () => {
	form.is_published = true;
	submitForm();
};

const stripHtml = (html: string) => {
	if (!html) return "";
	return html.replace(/<[^>]*>/g, "");
};

const submitForm = () => {
	// Sync chip tags to form fields
	form.category = categoryTags.value.join(", ");
	form.tools_used = toolsTags.value.join(", ");

	// Total files size validation
	let totalBytes = 0;
	if (form.cover_image && form.cover_image instanceof File) {
		totalBytes += form.cover_image.size;
	}
	for (const block of form.content) {
		if (block.file && block.file instanceof File) {
			totalBytes += block.file.size;
		}
		if (block.files && Array.isArray(block.files)) {
			for (const file of block.files) {
				if (file instanceof File) {
					totalBytes += file.size;
				}
			}
		}
	}

	const maxTotalLimit = 100 * 1024 * 1024; // 100MB
	if (totalBytes > maxTotalLimit) {
		const totalMB = (totalBytes / (1024 * 1024)).toFixed(1);
		addToast(
			`Total ukuran berkas (${totalMB}MB) melebihi batas maksimal 100MB! Silakan kurangi ukuran berkas Anda.`,
			"error",
		);
		return;
	}

	// Required fields validation when publishing
	if (form.is_published) {
		if (!form.title?.trim()) {
			addToast("Judul karya wajib diisi sebelum dipublikasikan!", "error");
			return;
		}
		if (!form.cover_image && !props.editor?.cover_image) {
			addToast(
				"Foto/video sampul karya wajib diunggah sebelum dipublikasikan!",
				"error",
			);
			return;
		}
		if (!form.description?.trim()) {
			addToast("Deskripsi proyek wajib diisi sebelum dipublikasikan!", "error");
			return;
		}
		if (categoryTags.value.length === 0) {
			addToast("Pilih minimal 1 kategori sebelum dipublikasikan!", "error");
			return;
		}
		if (toolsTags.value.length === 0) {
			addToast("Pilih minimal 1 tools sebelum dipublikasikan!", "error");
			return;
		}
		if (!form.tags?.trim()) {
			addToast("Tags proyek wajib diisi sebelum dipublikasikan!", "error");
			return;
		}
		if (!form.visibility) {
			addToast(
				"Visibility proyek wajib diisi sebelum dipublikasikan!",
				"error",
			);
			return;
		}
	}

	// Sanitize form inputs for security (XSS prevention)
	form.title = stripHtml(form.title);
	form.description = stripHtml(form.description);
	form.category = stripHtml(form.category);
	form.tools_used = stripHtml(form.tools_used);
	form.tags = stripHtml(form.tags);
	form.visibility = stripHtml(form.visibility);

	// Sanitize content blocks
	form.content.forEach((block) => {
		if (block) {
			if (block.type === "text" && block.value) {
				block.value = sanitize(block.value);
			}
			if (block.name) {
				block.name = stripHtml(block.name);
			}
			if (block.link) {
				block.link = stripHtml(block.link);
			}
		}
	});

	const url = props.editor ? `/pagi/editor/${props.editor.id}` : "/pagi/editor";

	form
		.transform((data) => ({
			...data,
			collaborators: JSON.stringify(data.collaborators),
		}))
		.post(url, {
			preserveScroll: true,
			onSuccess: () => {
				showPublishModal.value = false;
				if (globalThis.window !== undefined) {
					globalThis.localStorage.removeItem("pagi_work_draft");
					idbClear(); // Clear stored files from IndexedDB after successful save
				}
				addToast("Karya berhasil disimpan!", "success");
			},
			onError: (errors) => {
				const firstError = Object.values(errors)[0];
				addToast(
					firstError ||
						"Gagal menyimpan karya. Silakan periksa kembali formulir Anda.",
					"error",
				);
			},
		});
};

const { saveDraft } = useEditorDraft(
	form,
	globalSpacing,
	canvasBgColor,
	canvasTextColor,
	!!props.editor,
);

onMounted(async () => {
	if (props.editor) {
		form.title = props.editor.title || "";
		form.category = props.editor.category || "";
		form.tools_used = props.editor.tools_used || "";
		form.description = props.editor.description || "";
		form.visibility = props.editor.visibility || "Everyone";
		if (props.editor.category) {
			categoryTags.value = props.editor.category
				.split(",")
				.map((c: string) => c.trim())
				.filter(Boolean);
		}
		if (props.editor.tools_used) {
			toolsTags.value = props.editor.tools_used
				.split(",")
				.map((t: string) => t.trim())
				.filter(Boolean);
		}
		if (props.editor.tags && Array.isArray(props.editor.tags)) {
			form.tags = props.editor.tags.map((t: any) => t.name).join(", ");
		}
		form.is_published = !!props.editor.is_published;

		if (props.editor.cover_image) {
			coverPreview.value = props.editor.cover_image.startsWith("http")
				? props.editor.cover_image
				: `/storage/${props.editor.cover_image}`;
		}

		if (props.editor.content && Array.isArray(props.editor.content)) {
			form.content.splice(
				0,
				form.content.length,
				...props.editor.content.filter(
					(b: any) => b && typeof b === "object" && b.type,
				),
			);

			// Parse collaborators
			const details = props.editor.content.find(
				(b: any) => b && b.type === "featured_details",
			);
			if (details?.collaborators) {
				form.collaborators = details.collaborators.map((c: any) =>
					typeof c === "object" ? c.name : c,
				);
			}
		}

		const settingsBlock = form.content.find((b) => b && b.type === "settings");
		if (settingsBlock) {
			globalSpacing.value = settingsBlock.globalSpacing ?? 50;
			canvasBgColor.value = settingsBlock.canvasBgColor || "#ffffff";
			canvasTextColor.value = settingsBlock.canvasTextColor || "#111827";
			canvasBorderColor.value = settingsBlock.canvasBorderColor || "#e2e8f0";
			coverFit.value = settingsBlock.coverFit || "cover";
		} else {
			updateGlobalSettingsBlock();
		}
		return;
	}

	if (globalThis.window !== undefined) {
		const saved = globalThis.localStorage.getItem("pagi_work_draft");
		if (saved) {
			try {
				const draft = JSON.parse(saved);
				if (draft.title) form.title = draft.title;
				if (draft.content && Array.isArray(draft.content)) {
					// Use splice to reactively populate Inertia's form array
					form.content.splice(
						0,
						form.content.length,
						...draft.content.filter(
							(b: any) => b && typeof b === "object" && b.type,
						),
					);

					// Restore files from IndexedDB for each block
					for (const block of form.content) {
						if (!block) continue;
						if (
							block.type === "photo_grid" &&
							block.fileKeys &&
							block.fileKeys.length > 0
						) {
							const restoredPreviews: string[] = [];
							const restoredFiles: File[] = [];
							for (const key of block.fileKeys) {
								const file = await idbGet(key);
								if (file) {
									restoredFiles.push(file);
									restoredPreviews.push(URL.createObjectURL(file));
								}
							}
							if (restoredPreviews.length > 0) {
								block.files = restoredFiles;
								block.previews = restoredPreviews;
							}
						} else if (block.fileKey) {
							const file = await idbGet(block.fileKey);
							if (file) {
								block.file = file;
								block.preview = URL.createObjectURL(file);
								block.mimeType = file.type;
							}
						}
					}
				}
				if (draft.canvasBgColor) canvasBgColor.value = draft.canvasBgColor;
				if (draft.canvasTextColor)
					canvasTextColor.value = draft.canvasTextColor;
				if (draft.globalSpacing) globalSpacing.value = draft.globalSpacing;
			} catch (e) {
				console.error("Error parsing draft:", e);
			}
		}
	}

	const settingsBlock = form.content.find((b) => b && b.type === "settings");
	if (settingsBlock) {
		globalSpacing.value = settingsBlock.globalSpacing ?? 50;
		canvasBgColor.value = settingsBlock.canvasBgColor || "#ffffff";
		canvasTextColor.value = settingsBlock.canvasTextColor || "#111827";
		canvasBorderColor.value = settingsBlock.canvasBorderColor || "#e2e8f0";
		coverFit.value = settingsBlock.coverFit || "cover";
	} else {
		updateGlobalSettingsBlock();
	}
});

const handleHttpError = (e: Event) => {
	const detail = (e as CustomEvent).detail;
	addToast(detail.message || "Terjadi kesalahan pada server.", "error");
};

let formTracker: any = null;

onMounted(() => {
	if (globalThis.window !== undefined) {
		globalThis.addEventListener("pagi-http-error", handleHttpError);
	}
	formTracker = trackInertiaForm(form, "Menyimpan Proyek");
});

onUnmounted(() => {
	if (globalThis.window !== undefined) {
		globalThis.removeEventListener("pagi-http-error", handleHttpError);
	}
	if (formTracker) {
		formTracker.stopTracking();
	}
});
</script>

<template>
	<Head>
		<title>Work Editor</title>
	</Head>

	<div class="h-screen bg-slate-100 dark:bg-slate-950 font-sans flex flex-col overflow-hidden" style="font-family:'Inter',system-ui,sans-serif;">
		<Preview 
			v-if="isPreviewMode" 
			:title="form.title" 
			:content="form.content" 
			:cover-image="coverPreview" 
			:portfolio="props.editor"
			:canvas-bg-color="canvasBgColor"
			:canvas-text-color="canvasTextColor"
			:canvas-border-color="canvasBorderColor"
			:global-spacing="globalSpacing"
			:description="form.description"
			:category="form.category"
			:tools-used="form.tools_used"
			:tags="form.tags"
			@close="isPreviewMode = false" 
		/>

		<template v-else>
			<Navbar />

			<!-- MAIN LAYOUT -->
			<div class="flex flex-1 overflow-hidden">
				<EditorCanvas
					:form="form"
					:canvas-bg-color="canvasBgColor"
					:canvas-text-color="canvasTextColor"
					:spacing-in-px="spacingInPx"
					:container-width="containerWidth"
					:get-container-width="getContainerWidth"
					:content-options="contentOptions"
					:is-preview-mode="isPreviewMode"
					:canvas-container-ref="setCanvasContainer"
					@add-block="handleAddClick"
					@trigger-block-file-input="triggerFileInputForBlock"
					@delete-block="deleteBlock"
				/>

				<!-- RIGHT SIDEBAR (RESTYLED WITH TABS, STICKY FOOTER, STYLE PANELS) -->
				<EditorSidebar 
					v-if="!isPreviewMode" 
					v-model:activeSidebarTab="activeSidebarTab" 
					v-model:globalSpacing="globalSpacing" 
					v-model:canvasBgColor="canvasBgColor" 
					v-model:canvasTextColor="canvasTextColor" 
					:style-presets="stylePresets" 
					:content-options="contentOptions" 
					:processing="form.processing" 
					:disable-save="form.content.filter(b => b.type !== 'settings').length === 0" 
					@add-block="handleAddClick" 
					@open-asset-modal="openAssetLinkModal" 
					@show-publish-modal="showPublishModal = true" 
					@save-draft="saveAsDraft" 
					@preview="isPreviewMode = true" 
					@update-settings="updateGlobalSettingsBlock" 
				/>
			</div>

			<EditorPublishModal 
				v-if="showPublishModal" 
				:form="form" 
				:cover-preview="coverPreview" 
				:is-cover-video="isCoverVideo" 
				v-model:cover-fit="coverFit" 
				:category-tags="categoryTags" 
				v-model:category-input="categoryInput" 
				v-model:show-category-dropdown="showCategoryDropdown" 
				:filtered-category-suggestions="filteredCategorySuggestions" 
				:tools-tags="toolsTags" 
				v-model:tools-input="toolsInput" 
				v-model:show-tools-dropdown="showToolsDropdown" 
				:filtered-tools-suggestions="filteredToolsSuggestions" 
				v-model:collaborator-input="collaboratorInput" 
				v-model:show-collaborator-dropdown="showCollaboratorDropdown" 
				:collaborator-suggestions="collaboratorSuggestions" 
				:is-loading-collaborators="isLoadingCollaborators" 
				@close="showPublishModal = false" 
				@trigger-file-input="triggerFileInput" 
				@add-category-tag="addCategoryTag" 
				@remove-category-tag="removeCategoryTag" 
				@add-tool-tag="addToolTag" 
				@remove-tool-tag="removeToolTag" 
				@handle-collaborator-search="handleCollaboratorSearch" 
				@add-collaborator-chip="addCollaboratorChip" 
				@remove-collaborator-chip="removeCollaboratorChip" 
				@save-draft="saveAsDraft" 
				@publish="publishProject" 
			/>

			<!-- ADD ASSET LINK MODAL -->
			<div v-if="showAssetLinkModal" class="fixed inset-0 z-120 flex items-center justify-center bg-black/60 p-4 animate-fade-in">
				<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl w-full max-w-md p-6 flex flex-col relative">
					<!-- Modal Header -->
					<button @click="closeAssetLinkModal" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 cursor-pointer border-none bg-transparent">
						<X class="h-5 w-5" />
					</button>
					<h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">Attach Asset Link</h3>
					
					<div class="flex flex-col gap-4">
						<div class="flex flex-col gap-1.5">
							<label for="editor-asset-name" class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Asset Name / Label</label>
							<input id="editor-asset-name" v-model="assetNameInput" type="text" placeholder="e.g. Free Figma Template, Font Zip File" class="w-full rounded-xl border border-slate-200 dark:border-slate-800 px-3 py-2 text-sm focus:border-slate-800 dark:focus:border-slate-200 focus:ring-1 focus:ring-slate-800 dark:focus:ring-slate-200 outline-none bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 placeholder-slate-400" />
						</div>
						
						<div class="flex flex-col gap-1.5">
							<label for="editor-asset-url" class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Link / URL</label>
							<input id="editor-asset-url" v-model="assetLinkInput" type="text" placeholder="https://github.com/... or https://figma.com/..." class="w-full rounded-xl border border-slate-200 dark:border-slate-800 px-3 py-2 text-sm focus:border-slate-800 dark:focus:border-slate-200 focus:ring-1 focus:ring-slate-800 dark:focus:ring-slate-200 outline-none bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 placeholder-slate-400" />
						</div>
					</div>
					
					<div class="flex justify-end gap-3 mt-6">
						<button @click="closeAssetLinkModal" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-900 rounded-xl transition-colors cursor-pointer border-none bg-transparent">
							Cancel
						</button>
						<button @click="addAssetLinkBlock" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-750 rounded-xl transition-colors shadow-xs cursor-pointer border-none">
							Add Link
						</button>
					</div>
				</div>
			</div>
		</template>

		<!-- TOAST ALERTS CONTAINER -->
		<div class="fixed top-6 right-6 z-[10010] flex flex-col gap-3 max-w-xs pointer-events-none">
			<TransitionGroup 
				enter-active-class="transform transition duration-300 ease-out"
				enter-from-class="translate-y-2 opacity-0 scale-95"
				enter-to-class="translate-y-0 opacity-100 scale-100"
				leave-active-class="transition duration-200 ease-in"
				leave-from-class="opacity-100 scale-100"
				leave-to-class="opacity-0 scale-95"
			>
				<div 
					v-for="toast in toasts" 
					:key="toast.id"
					class="p-4 rounded-xl border border-slate-200/80 dark:border-slate-800/80 flex items-start gap-3.5 shadow-[0_12px_40px_rgba(0,0,0,0.08)] dark:shadow-[0_12px_40px_rgba(0,0,0,0.35)] bg-white/95 dark:bg-slate-900/95 border-l-4 pointer-events-auto select-none w-80 max-w-xs"
					:class="[
						toast.type === 'success' 
							? 'border-l-emerald-500' 
							: 'border-l-rose-500'
					]"
				>
					<div class="shrink-0 mt-0.5">
						<CheckCircle2 v-if="toast.type === 'success'" class="w-4 h-4 text-emerald-500" />
						<AlertCircle v-else class="w-4 h-4 text-rose-500" />
					</div>
					<div class="flex-1 text-xs font-semibold leading-relaxed pr-1 text-slate-800 dark:text-slate-250">
						{{ toast.message }}
					</div>
					<button @click="removeToast(toast.id)" class="text-slate-400 hover:text-slate-600 dark:text-zinc-550 dark:hover:text-white shrink-0 bg-transparent border-none cursor-pointer p-0.5 rounded-full hover:bg-slate-200/50 dark:hover:bg-zinc-800/50 transition-colors flex items-center justify-center">
						<X class="w-3.5 h-3.5" />
					</button>
				</div>
			</TransitionGroup>
		</div>

	</div>
</template>

<style>
.editor-content { outline: none; }
.editor-content h1 { font-size: 2.25rem; font-weight: 800; line-height: 1.2; margin: 1rem 0; }
.editor-content h2 { font-size: 1.5rem; font-weight: 700; line-height: 1.3; margin: 0.875rem 0; }
.editor-content p { margin: 0.75rem 0; }
.editor-content blockquote { border-left: 4px solid #e2e8f0; padding-left: 1rem; color: #64748b; font-style: italic; margin: 1rem 0; }
.editor-content a { color: inherit; text-decoration: underline; text-decoration-color: #64748b; }
.editor-content ul { list-style-type: disc; padding-left: 1.5rem; margin: 0.75rem 0; }
.editor-content ol { list-style-type: decimal; padding-left: 1.5rem; margin: 0.75rem 0; }
</style>
