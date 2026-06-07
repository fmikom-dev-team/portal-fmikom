<script setup lang="ts">
import axios from "axios";
import {
	Eye,
	Heart,
	Image as ImageIcon,
	MoreHorizontal,
	Pencil,
	Plus,
	Share2,
	Trash2,
	UploadCloud,
	X,
} from "lucide-vue-next";
import { computed, onMounted, onUnmounted, ref } from "vue";
import Modal from "../ui/Modal.vue";
import OptimizedImage from "../ui/OptimizedImage.vue";
import VideoLazy from "../ui/VideoLazy.vue";

const props = defineProps<{
	projects?: Array<{
		id: number;
		title: string;
		image: string;
		likes: number;
		views: number;
		content: any;
		created_at: string;
		is_verified?: boolean;
		comments?: any[];
	}>;
	isOwnProfile: boolean;
	isLoading?: boolean;
}>();

const emit = defineEmits<{
	(e: "open-project", project: any): void;
	(e: "delete-project", id: number, title: string): void;
	(e: "gallery-item-added", project: any): void;
	(e: "gallery-item-updated", project: any): void;
	(e: "share-project", project: any): void;
}>();

// Form & Upload States
const showUploadModal = ref(false);
const formTitle = ref("");
const formDescription = ref("");
const selectedFile = ref<File | null>(null);
const filePreview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const isUploading = ref(false);
const uploadProgress = ref(0);
const dragActive = ref(false);

const isManualItem = (project: any) => {
	if (!project?.content || !Array.isArray(project.content)) return false;
	return project.content.some((b: any) => b && b.type === "gallery_item");
};

const isVideoBlock = (block: any) => {
	if (!block) return false;
	if (block.mimeType) return block.mimeType.startsWith("video");
	if (block.file?.type) return block.file.type.startsWith("video");
	if (block.name) {
		const ext = block.name.split(".").pop()?.toLowerCase();
		return ["mp4", "webm", "mov", "avi", "mkv", "3gp"].includes(ext || "");
	}
	if (block.file_path) {
		const ext = block.file_path.split(".").pop()?.toLowerCase();
		return ["mp4", "webm", "mov", "avi", "mkv", "3gp"].includes(ext || "");
	}
	if (block.preview) {
		const ext = block.preview.split("?")[0].split(".").pop()?.toLowerCase();
		return ["mp4", "webm", "mov", "avi", "mkv", "3gp"].includes(ext || "");
	}
	return false;
};

const DEFAULT_PLACEHOLDER =
	"https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop";

const hiddenGalleryItems = ref<string[]>([]);

onMounted(() => {
	try {
		hiddenGalleryItems.value = JSON.parse(
			localStorage.getItem("pagi_hidden_gallery_items") || "[]",
		);
	} catch (e) {
		console.warn("Failed to parse hidden gallery items:", e);
		hiddenGalleryItems.value = [];
	}
});

const galleryItems = computed(() => {
	if (!props.projects) return [];

	const items: any[] = [];

	props.projects.forEach((p) => {
		const projectUrls = new Set<string>();

		// 1. Check if it is a manual gallery item
		if (isManualItem(p)) {
			if (p.image) {
				items.push({
					id: `manual-${p.id}`,
					projectId: p.id,
					project: p,
					url: p.image,
					type: isVideoUrl(p.image) ? "video" : "image",
					title: p.title,
					isManual: true,
					likes: p.likes || 0,
					commentsCount: p.comments ? p.comments.length : 0,
					views: p.views || 0,
				});
			}
		} else {
			// It's a standard project or case study.
			// a. Add Cover Image/Video (exclude default unsplash placeholder)
			if (p.image && p.image !== DEFAULT_PLACEHOLDER) {
				projectUrls.add(p.image);
				items.push({
					id: `cover-${p.id}`,
					projectId: p.id,
					project: p,
					url: p.image,
					type: isVideoUrl(p.image) ? "video" : "image",
					title: p.title,
					isManual: false,
					likes: p.likes || 0,
					commentsCount: p.comments ? p.comments.length : 0,
					views: p.views || 0,
				});
			}

			// b. Extract from content blocks
			if (p.content && Array.isArray(p.content)) {
				p.content.forEach((block, bIdx) => {
					if (!block) return;

					if (block.type === "image" && block.preview) {
						if (!projectUrls.has(block.preview)) {
							projectUrls.add(block.preview);
							items.push({
								id: `block-image-${p.id}-${bIdx}`,
								projectId: p.id,
								project: p,
								url: block.preview,
								type: "image",
								title: p.title,
								isManual: false,
								likes: p.likes || 0,
								commentsCount: p.comments ? p.comments.length : 0,
								views: p.views || 0,
							});
						}
					} else if (
						block.type === "video_audio" &&
						block.preview &&
						isVideoBlock(block)
					) {
						if (!projectUrls.has(block.preview)) {
							projectUrls.add(block.preview);
							items.push({
								id: `block-video-${p.id}-${bIdx}`,
								projectId: p.id,
								project: p,
								url: block.preview,
								type: "video",
								title: p.title,
								isManual: false,
								likes: p.likes || 0,
								commentsCount: p.comments ? p.comments.length : 0,
								views: p.views || 0,
							});
						}
					} else if (
						block.type === "photo_grid" &&
						block.previews &&
						Array.isArray(block.previews)
					) {
						block.previews.forEach((previewUrl: any, gIdx: number) => {
							if (!previewUrl) return;
							if (!projectUrls.has(previewUrl)) {
								projectUrls.add(previewUrl);
								items.push({
									id: `block-grid-${p.id}-${bIdx}-${gIdx}`,
									projectId: p.id,
									project: p,
									url: previewUrl,
									type: "image",
									title: p.title,
									isManual: false,
									likes: p.likes || 0,
									commentsCount: p.comments ? p.comments.length : 0,
									views: p.views || 0,
								});
							}
						});
					}
				});
			}
		}
	});

	return items.filter((item) => !hiddenGalleryItems.value.includes(item.id));
});

const isVideoUrl = (url: string) => {
	if (!url) return false;
	const cleanUrl = url.split("?")[0]; // Strip cache busters
	return ["mp4", "webm", "mov", "avi", "mkv", "3gp"].includes(
		cleanUrl.split(".").pop()?.toLowerCase() || "",
	);
};

// File Handlers
const onDragOver = (e: DragEvent) => {
	e.preventDefault();
	dragActive.value = true;
};

const onDragLeave = () => {
	dragActive.value = false;
};

const onDrop = (e: DragEvent) => {
	e.preventDefault();
	dragActive.value = false;
	if (e.dataTransfer?.files?.[0]) {
		handleFileSelect(e.dataTransfer.files[0]);
	}
};

const onFileChange = (e: Event) => {
	const target = e.target as HTMLInputElement;
	if (target.files?.[0]) {
		handleFileSelect(target.files[0]);
	}
};

const handleFileSelect = (file: File) => {
	const isImage = file.type.startsWith("image/");
	const isVideo = file.type.startsWith("video/");
	if (!isImage && !isVideo) {
		alert("Hanya file gambar atau video yang diperbolehkan.");
		return;
	}
	const maxSize = isVideo ? 100 * 1024 * 1024 : 10 * 1024 * 1024; // 100MB for video, 10MB for image
	if (file.size > maxSize) {
		alert(`Ukuran file maksimal adalah ${isVideo ? "100MB" : "10MB"}.`);
		return;
	}
	selectedFile.value = file;
	filePreview.value = URL.createObjectURL(file);
};

const removeSelectedFile = () => {
	selectedFile.value = null;
	filePreview.value = null;
};

// Actions
const openUploadModal = () => {
	showUploadModal.value = true;
};

const closeUploadModal = () => {
	if (isUploading.value) return;
	showUploadModal.value = false;
	formTitle.value = "";
	formDescription.value = "";
	selectedFile.value = null;
	filePreview.value = null;
};

const submitGalleryItem = async () => {
	if (!selectedFile.value) return;
	isUploading.value = true;
	uploadProgress.value = 0;

	const formData = new FormData();
	formData.append("cover_image", selectedFile.value);
	formData.append("title", formTitle.value);
	formData.append("description", formDescription.value);

	try {
		const res = await axios.post("/pagi/gallery/store", formData, {
			headers: {
				"Content-Type": "multipart/form-data",
			},
			onUploadProgress: (progressEvent) => {
				if (progressEvent.total) {
					uploadProgress.value = Math.round(
						(progressEvent.loaded * 100) / progressEvent.total,
					);
				}
			},
		});

		if (res.data.success) {
			emit("gallery-item-added", res.data.project);
			closeUploadModal();
		}
	} catch (err: any) {
		alert(err.response?.data?.message || "Gagal mengunggah item ke galeri.");
	} finally {
		isUploading.value = false;
		uploadProgress.value = 0;
	}
};

// Dropdown active menu state
const activeMenuId = ref<string | null>(null);
const toggleMenu = (itemId: string) => {
	activeMenuId.value = activeMenuId.value === itemId ? null : itemId;
};

// Edit manual gallery item states
const showEditModal = ref(false);
const editingItem = ref<any>(null);
const editTitle = ref("");
const editDescription = ref("");
const isSavingEdit = ref(false);

const handleShare = (item: any) => {
	activeMenuId.value = null;
	emit("share-project", item.project);
};

const handleEdit = (item: any) => {
	activeMenuId.value = null;
	editingItem.value = item;
	editTitle.value = item.project.title || "";
	const descBlock = item.project.content?.find(
		(b: any) => b && b.type === "gallery_item",
	);
	editDescription.value = descBlock?.description || "";
	showEditModal.value = true;
};

const submitGalleryItemEdit = async () => {
	if (!editingItem.value) return;
	isSavingEdit.value = true;
	try {
		const res = await axios.post(
			`/pagi/editor/${editingItem.value.projectId}/quick-update`,
			{
				title: editTitle.value,
				description: editDescription.value,
			},
		);
		if (res.data.success) {
			// Update locally
			editingItem.value.project.title = editTitle.value;
			const descBlock = editingItem.value.project.content?.find(
				(b: any) => b && b.type === "gallery_item",
			);
			if (descBlock) {
				descBlock.description = editDescription.value;
			}
			emit("gallery-item-updated", editingItem.value.project);
			showEditModal.value = false;
		}
	} catch (err: any) {
		alert(err.response?.data?.message || "Gagal mengedit item galeri.");
	} finally {
		isSavingEdit.value = false;
	}
};

const handleDeleteItem = (item: any) => {
	activeMenuId.value = null;
	if (item.isManual) {
		if (
			confirm(`Apakah Anda yakin ingin menghapus "${item.title}" dari galeri?`)
		) {
			emit("delete-project", item.projectId, item.title);
		}
	} else if (
		confirm(`Apakah Anda yakin ingin menyembunyikan visual ini dari galeri?`)
	) {
		// For automatic items, hide locally/persistently in localStorage
		hiddenGalleryItems.value.push(item.id);
		localStorage.setItem(
			"pagi_hidden_gallery_items",
			JSON.stringify(hiddenGalleryItems.value),
		);
	}
};

// Document click listener to close dropdowns when clicking outside
const closeAllMenus = (e: MouseEvent) => {
	const target = e.target as HTMLElement;
	if (target && !target.closest(".dropdown-trigger-container")) {
		activeMenuId.value = null;
	}
};

onMounted(() => {
	document.addEventListener("click", closeAllMenus);
});

onUnmounted(() => {
	document.removeEventListener("click", closeAllMenus);
});
</script>

<template>
	<div class="space-y-6">
		<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
			<div>
				<h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Gallery</h3>
				<p class="text-xs text-slate-500 mt-0.5">Koleksi gambar, visual, dan karya dari project Anda.</p>
			</div>

			<!-- Buat Galeri Button -->
			<button 
				v-if="isOwnProfile"
				@click.prevent="openUploadModal"
				class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full bg-[#18181b] hover:bg-zinc-800 dark:bg-white dark:hover:bg-zinc-100 text-white dark:text-zinc-950 text-xs font-semibold transition-all duration-300 shadow-sm hover:scale-[1.02] active:scale-[0.98] cursor-pointer border-none shrink-0 w-full sm:w-auto justify-center"
			>
				<Plus class="w-3.5 h-3.5" />
				<span>Buat Galeri</span>
			</button>
		</div>

		<!-- If loading, show skeletons -->
		<div v-if="isLoading" class="columns-2 sm:columns-2 md:columns-3 lg:columns-4 gap-4 space-y-4">
			<div 
				v-for="n in 8" 
				:key="n" 
				class="break-inside-avoid w-full rounded-xl bg-slate-200 dark:bg-slate-800 animate-pulse"
				:style="{ height: `${Math.floor(Math.random() * 200) + 180}px` }"
			></div>
		</div>

		<!-- Pinterest Masonry Layout -->
		<div v-else-if="galleryItems && galleryItems.length > 0" class="columns-2 sm:columns-2 md:columns-3 lg:columns-4 gap-1.5 space-y-1.5">
			<div 
				v-for="item in galleryItems" 
				:key="item.id" 
				class="break-inside-avoid relative overflow-hidden rounded-xl border border-slate-200/50 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 cursor-pointer shadow-2xs hover:shadow-md transition-all duration-300 group" 
				@click="emit('open-project', item.project)"
			>
				<VideoLazy v-if="item.type === 'video'" :src="item.url" className="w-full h-auto object-cover rounded-xl group-hover:scale-[1.02] transition-transform duration-500" />
				<OptimizedImage v-else :src="item.url" :alt="item.title" className="w-full h-auto object-cover group-hover:scale-[1.02] transition-transform duration-500" />
				
				<!-- Pinterest style gradient overlay -->
				<div class="absolute inset-0 bg-linear-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-col justify-between p-4 z-10">
					
					<!-- Top Action (Options Dropdown Menu) -->
					<div class="flex justify-end relative dropdown-trigger-container">
						<button 
							v-if="isOwnProfile"
							@click.stop="toggleMenu(item.id)"
							class="w-7 h-7 rounded-full bg-black/60 hover:bg-black/80 backdrop-blur-md flex items-center justify-center text-white hover:scale-105 active:scale-95 transition-all duration-200 border-none cursor-pointer"
							title="Menu Pilihan"
						>
							<MoreHorizontal class="w-4 h-4" />
						</button>

						<!-- Dropdown popup -->
						<div 
							v-if="activeMenuId === item.id" 
							class="absolute top-8 right-0 w-36 bg-white dark:bg-slate-905 border border-slate-200/80 dark:border-slate-800 shadow-xl rounded-xl py-1 z-40 overflow-hidden text-left"
						>
							<button 
								@click.stop="handleShare(item)"
								class="w-full flex items-center gap-2 px-3 py-2 text-[11px] font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer text-left border-none bg-transparent"
							>
								<Share2 class="w-3.5 h-3.5 text-slate-550" />
								<span>Share link</span>
							</button>

							<button 
								v-if="item.isManual"
								@click.stop="handleEdit(item)"
								class="w-full flex items-center gap-2 px-3 py-2 text-[11px] font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer text-left border-none bg-transparent"
							>
								<Pencil class="w-3.5 h-3.5 text-slate-550" />
								<span>Edit info</span>
							</button>

							<button 
								@click.stop="handleDeleteItem(item)"
								class="w-full flex items-center gap-2 px-3 py-2 text-[11px] font-bold text-red-650 hover:bg-red-50 dark:hover:bg-red-955/20 border-t border-slate-100 dark:border-slate-800 mt-1 pt-2 transition-colors cursor-pointer text-left bg-transparent"
							>
								<Trash2 class="w-3.5 h-3.5 text-red-550" />
								<span>{{ item.isManual ? 'Hapus' : 'Sembunyikan' }}</span>
							</button>
						</div>
					</div>

					<!-- Bottom title and stats -->
					<div class="space-y-1">
						<span class="text-white text-xs font-extrabold line-clamp-1 leading-normal">{{ item.title }}</span>
						<div class="flex items-center gap-3 text-[10px] font-bold text-white/80">
							<!-- Likes -->
							<span class="flex items-center gap-1">
								<Heart class="w-3.5 h-3.5 text-rose-500 fill-rose-500" />
								{{ item.likes }}
							</span>
							<!-- Views -->
							<span class="flex items-center gap-1">
								<Eye class="w-3.5 h-3.5 text-slate-400" />
								{{ item.views }}
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Empty State -->
		<div v-else class="border border-dashed border-slate-200 dark:border-slate-800 rounded-3xl p-12 text-center bg-slate-50/50 dark:bg-slate-900/10">
			<ImageIcon class="w-8 h-8 text-slate-400 mx-auto mb-3" />
			<h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-1">Gallery is empty</h3>
			<p class="text-xs text-slate-500 max-w-xs mx-auto leading-relaxed">Add projects with cover images or upload visuals directly to populate your gallery.</p>
		</div>

		<!-- Buat Galeri Modal -->
		<Modal :show="showUploadModal" title="Buat Galeri Visual" max-width="lg" :prevent-close="isUploading" @close="closeUploadModal">
			<form @submit.prevent="submitGalleryItem" class="space-y-4">
				
				<!-- Drag & Drop Zone -->
				<div 
					@dragover="onDragOver"
					@dragleave="onDragLeave"
					@drop="onDrop"
					:class="[
						'border-2 border-dashed rounded-2xl p-6 text-center cursor-pointer transition-all duration-300 relative overflow-hidden flex flex-col items-center justify-center min-h-[220px]',
						dragActive ? 'border-blue-500 bg-blue-50/30 dark:bg-blue-950/10' : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 bg-slate-50/30 dark:bg-slate-900/20'
					]"
					@click="fileInput?.click()"
				>
					<input 
						type="file" 
						ref="fileInput" 
						class="hidden" 
						accept="image/*,video/*"
						@change="onFileChange"
					/>

					<div v-if="!filePreview" class="space-y-2 flex flex-col items-center">
						<div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-1">
							<UploadCloud class="w-6 h-6" />
						</div>
						<p class="text-xs font-black text-slate-700 dark:text-slate-300">
							Tarik & lepas file di sini, atau <span class="text-blue-600 dark:text-blue-400 hover:underline">pilih file</span>
						</p>
						<p class="text-[10px] text-slate-450 dark:text-slate-500">Mendukung Gambar (Maks. 10MB) & Video (Maks. 60 Detik / 100MB)</p>
					</div>

					<div v-else class="relative w-full h-full min-h-[180px] flex items-center justify-center rounded-xl overflow-hidden group">
						<video v-if="selectedFile && selectedFile.type.startsWith('video/')" :src="filePreview" class="max-h-[200px] w-auto object-contain rounded-lg shadow-sm" controls>
							<track kind="captions" />
						</video>
						<img v-else :src="filePreview" alt="Visual preview" class="max-h-[200px] w-auto object-contain rounded-lg shadow-sm" />
						<div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center">
							<button 
								type="button" 
								@click.stop="removeSelectedFile"
								class="px-3 py-1.5 rounded-full bg-red-600 hover:bg-red-500 text-white text-[11px] font-bold shadow-md cursor-pointer border-none flex items-center gap-1"
							>
								<X class="w-3.5 h-3.5" /> Ganti File
							</button>
						</div>
					</div>
				</div>

				<!-- Title Input -->
				<div class="space-y-1 text-left">
					<label for="gallery-add-title" class="text-[11px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Judul Visual</label>
					<input 
						id="gallery-add-title"
						type="text" 
						v-model="formTitle" 
						placeholder="Beri judul visual gallery Anda..." 
						maxlength="255"
						required
						:disabled="isUploading"
						class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm text-slate-800 dark:text-slate-100 placeholder-slate-450 dark:placeholder-slate-550 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none"
					/>
				</div>

				<!-- Description Input -->
				<div class="space-y-1 text-left">
					<label for="gallery-add-desc" class="text-[11px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Deskripsi (Opsional)</label>
					<textarea 
						id="gallery-add-desc"
						v-model="formDescription" 
						placeholder="Tulis deskripsi singkat mengenai visual ini..." 
						rows="3"
						maxlength="2000"
						:disabled="isUploading"
						class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm text-slate-800 dark:text-slate-100 placeholder-slate-450 dark:placeholder-slate-550 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none resize-none"
					></textarea>
				</div>

				<!-- Progress Bar -->
				<div v-if="isUploading" class="space-y-2">
					<div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
						<div class="bg-blue-600 h-full transition-all duration-300" :style="{ width: `${uploadProgress}%` }"></div>
					</div>
					<p class="text-[11px] font-black text-center text-blue-600">Mengunggah... {{ uploadProgress }}%</p>
				</div>

				<!-- Modal Actions / Footer -->
				<div class="flex items-center justify-end gap-3 pt-2">
					<button 
						type="button" 
						@click="closeUploadModal" 
						:disabled="isUploading"
						class="px-5 py-2.5 rounded-full border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold shadow-none transition-colors cursor-pointer bg-white dark:bg-slate-900"
					>
						Batal
					</button>
					<button 
						type="submit" 
						:disabled="isUploading || !selectedFile || !formTitle.trim()"
						class="px-6 py-2.5 rounded-full bg-blue-600 hover:bg-blue-500 disabled:bg-slate-200 dark:disabled:bg-slate-800 disabled:text-slate-450 dark:disabled:text-slate-600 disabled:cursor-not-allowed text-white text-xs font-semibold shadow-xs transition-colors cursor-pointer border-none"
					>
						Simpan ke Galeri
					</button>
				</div>

			</form>
		</Modal>

		<!-- Edit Galeri Item Modal -->
		<Modal :show="showEditModal" title="Edit Informasi Galeri" max-width="md" :prevent-close="isSavingEdit" @close="showEditModal = false">
			<form @submit.prevent="submitGalleryItemEdit" class="space-y-4">
				<div class="space-y-1.5">
					<label for="gallery-edit-title" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Judul Visual</label>
					<input 
						id="gallery-edit-title"
						v-model="editTitle" 
						type="text" 
						required
						placeholder="Judul visual galeri..." 
						class="w-full h-9 px-3 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs"
					/>
				</div>
				<div class="space-y-1.5">
					<label for="gallery-edit-desc" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Deskripsi / Cerita</label>
					<textarea 
						id="gallery-edit-desc"
						v-model="editDescription" 
						rows="3"
						placeholder="Berikan penjelasan singkat mengenai visual ini..." 
						class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs resize-none"
					></textarea>
				</div>
				
				<div class="flex items-center justify-end gap-2 pt-2">
					<button 
						type="button"
						@click="showEditModal = false" 
						class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer bg-white dark:bg-slate-900"
						:disabled="isSavingEdit"
					>
						Batal
					</button>
					<button 
						type="submit"
						class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 disabled:bg-slate-200 dark:disabled:bg-slate-800 disabled:text-slate-450 dark:disabled:text-slate-600 disabled:cursor-not-allowed text-white text-xs font-semibold shadow-xs transition-colors cursor-pointer border-none"
						:disabled="isSavingEdit"
					>
						{{ isSavingEdit ? 'Menyimpan...' : 'Simpan Perubahan' }}
					</button>
				</div>
			</form>
		</Modal>

	</div>
</template>
