<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import axios from "axios";
import {
	AlignCenter,
	AlignLeft,
	AlignRight,
	ArrowLeftRight,
	ArrowUpDown,
	ChevronDown,
	ChevronLeft,
	Edit2,
	Image as ImageIcon,
	LayoutGrid,
	Link2,
	Link2Off,
	Palette,
	Paperclip,
	PlaySquare,
	Plus,
	PlusCircle,
	Sliders,
	Type,
	X,
} from "lucide-vue-next";
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { sanitize } from "@/composables/useSanitize";
import Navbar from "./ui/Navbar.vue";
import OptimizedImage from "./ui/OptimizedImage.vue";
import Preview from "./ui/Preview.vue";
import Progress from "./ui/Progress.vue";
import VideoLazy from "./ui/VideoLazy.vue";

const props = defineProps<{
	editor?: any;
	mockImages?: any;
}>();

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

// Category and Tools Autocomplete Lists with Branding Logos
const categorySuggestions = [
	{ name: "Web Development", slug: "html5" },
	{ name: "Mobile Development", slug: "android" },
	{ name: "UI/UX Design", slug: "figma" },
	{ name: "Graphic Design", slug: "photoshop" },
	{ name: "Game Development", slug: "unity" },
	{ name: "Machine Learning / AI", slug: "python" },
	{ name: "Cyber Security", slug: "linux" },
	{ name: "Database Design", slug: "mysql" },
];

const toolsSuggestions = [
	{ name: "Figma", category: "design" },
	{ name: "VS Code", category: "dev" },
	{ name: "Python", category: "dev" },
	{ name: "GitHub", category: "dev" },
	{ name: "PostgreSQL", category: "database" },
	{ name: "MySQL", category: "database" },
	{ name: "MATLAB", category: "math" },
	{ name: "Android Studio", category: "dev" },
	{ name: "Docker", category: "dev" },
	{ name: "Laravel", category: "dev" },
	{ name: "Notion", category: "comms" },
	{ name: "Photoshop", category: "design" },
	{ name: "Illustrator", category: "design" },
	{ name: "Premiere Pro", category: "design" },
];

const getToolSlug = (toolName: string): string => {
	const name = toolName.toLowerCase().trim();
	if (name === "figma") return "figma";
	if (name === "photoshop" || name === "adobe photoshop" || name === "ps")
		return "photoshop";
	if (name === "illustrator" || name === "adobe illustrator" || name === "ai")
		return "illustrator";
	if (
		name === "premiere" ||
		name === "premiere pro" ||
		name === "pr" ||
		name === "premierepro"
	)
		return "premiere";
	if (
		name === "vs code" ||
		name === "vscode" ||
		name === "visual studio code" ||
		name === "visual-studio-code"
	)
		return "visual-studio-code";
	if (name === "visual studio" || name === "vs") return "visual-studio";
	if (
		name === "vue" ||
		name === "vue.js" ||
		name === "vuejs" ||
		name === "vuedotjs"
	)
		return "vue";
	if (name === "react" || name === "reactjs" || name === "react.js")
		return "react";
	if (
		name === "tailwind" ||
		name === "tailwindcss" ||
		name === "tailwind css" ||
		name === "tailwind-css"
	)
		return "tailwind-css";
	if (name === "laravel") return "laravel";
	if (name === "php") return "php";
	if (name === "javascript" || name === "js") return "javascript";
	if (name === "html" || name === "html5") return "html5";
	if (name === "css" || name === "css3") return "css";
	if (name === "git") return "git";
	if (name === "github") return "github";
	if (name === "docker") return "docker";
	if (name === "postman") return "postman";
	if (name === "canva") return "canva";
	if (name === "trello") return "trello";
	if (name === "jira") return "jira";
	if (name === "sass" || name === "scss") return "sass";
	if (name === "nodejs" || name === "node" || name === "node.js")
		return "nodedotjs";
	if (name === "typescript" || name === "ts") return "typescript";
	if (name === "python") return "python";
	if (name === "mysql") return "mysql";
	if (name === "postgresql" || name === "postgres") return "postgresql";
	if (name === "mongodb" || name === "mongo") return "mongodb";
	if (name === "firebase") return "firebase";
	if (name === "flutter") return "flutter";
	if (name === "kotlin") return "kotlin";
	if (name === "swift") return "swift";
	if (name === "xd" || name === "adobe xd") return "adobe-xd";
	if (name === "indesign" || name === "adobe indesign") return "adobe-indesign";
	if (
		name === "after effects" ||
		name === "ae" ||
		name === "adobe after effects"
	)
		return "adobe-after-effects";

	return name
		.replaceAll(".js", "dotjs")
		.replaceAll(".net", "dotnet")
		.replaceAll(/[^a-z0-9]+/g, "-");
};

// Autocomplete tags & query state variables
const categoryInput = ref("");
const categoryTags = ref<string[]>([]);
const showCategoryDropdown = ref(false);

const toolsInput = ref("");
const toolsTags = ref<string[]>([]);
const showToolsDropdown = ref(false);

const collaboratorInput = ref("");
const showCollaboratorDropdown = ref(false);
const collaboratorSuggestions = ref<any[]>([]);
const isLoadingCollaborators = ref(false);
let searchTimeout: any = null;

const addCategoryTag = (catName: string) => {
	const clean = catName.trim();
	if (
		clean &&
		categoryTags.value.length < 3 &&
		!categoryTags.value.includes(clean)
	) {
		categoryTags.value.push(clean);
	}
	categoryInput.value = "";
	showCategoryDropdown.value = false;
};

const removeCategoryTag = (idx: number) => {
	categoryTags.value.splice(idx, 1);
};

const filteredCategorySuggestions = computed(() => {
	const q = categoryInput.value.toLowerCase().trim();
	if (!q)
		return categorySuggestions.filter(
			(c) => !categoryTags.value.includes(c.name),
		);
	return categorySuggestions.filter(
		(c) =>
			c.name.toLowerCase().includes(q) && !categoryTags.value.includes(c.name),
	);
});

const addToolTag = (toolName: string) => {
	const clean = toolName.trim();
	if (
		clean &&
		toolsTags.value.length < 10 &&
		!toolsTags.value.includes(clean)
	) {
		toolsTags.value.push(clean);
	}
	toolsInput.value = "";
	showToolsDropdown.value = false;
};

const removeToolTag = (idx: number) => {
	toolsTags.value.splice(idx, 1);
};

const filteredToolsSuggestions = computed(() => {
	const q = toolsInput.value.toLowerCase().trim();
	if (!q)
		return toolsSuggestions.filter((t) => !toolsTags.value.includes(t.name));
	return toolsSuggestions.filter(
		(t) =>
			t.name.toLowerCase().includes(q) && !toolsTags.value.includes(t.name),
	);
});

const handleCollaboratorSearch = () => {
	if (searchTimeout) clearTimeout(searchTimeout);
	const q = collaboratorInput.value.trim();
	if (q.length < 1) {
		collaboratorSuggestions.value = [];
		showCollaboratorDropdown.value = false;
		return;
	}
	showCollaboratorDropdown.value = true;
	isLoadingCollaborators.value = true;
	searchTimeout = setTimeout(async () => {
		try {
			const res = await axios.get(
				`/pagi/users/search?q=${encodeURIComponent(q)}`,
			);
			collaboratorSuggestions.value = res.data || [];
		} catch (e) {
			console.error(e);
		} finally {
			isLoadingCollaborators.value = false;
		}
	}, 300);
};

const addCollaboratorChip = (username: string) => {
	if (form.collaborators.length < 3 && !form.collaborators.includes(username)) {
		form.collaborators.push(username);
	}
	collaboratorInput.value = "";
	collaboratorSuggestions.value = [];
	showCollaboratorDropdown.value = false;
};

const removeCollaboratorChip = (idx: number) => {
	form.collaborators.splice(idx, 1);
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
		label: "Video & Audio",
		icon: PlaySquare,
		color: "text-blue-500",
	},
];

const showPublishModal = ref(false);
const coverPreview = ref<string | null>(null);
const isPreviewMode = ref(false);

const promptLink = () => {
	const url = globalThis.prompt("Enter URL:");
	if (url) {
		execCmd("createLink", url);
	}
};

const delayBlur = (callback: () => void) => {
	globalThis.setTimeout(callback, 200);
};

const isCoverVideo = computed(() => {
	if (!form.cover_image) return false;
	if (form.cover_image instanceof File) {
		return form.cover_image.type.startsWith("video/");
	}
	if (typeof form.cover_image === "string") {
		const ext = form.cover_image.split(".").pop()?.toLowerCase();
		return ["mp4", "webm", "ogg", "mov", "m4v", "3gp"].includes(ext || "");
	}
	return false;
});

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

	// Simple URL validation / formatting
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

const getUploadStatusMessage = (progress: number) => {
	if (progress < 5) return "Memulai pengunggahan...";
	if (progress < 25) return "Mengompresi dan mengoptimalkan gambar/video...";
	if (progress < 50) return "Menyiapkan data blok konten...";
	if (progress < 75) return "Mengunggah aset karya...";
	if (progress < 95) return "Menyimpan ke basis data...";
	return "Selesai!";
};

// Toast Notification System
const toasts = ref<Array<{ id: number; message: string; type: string }>>([]);
const addToast = (message: string, type = "success") => {
	const id = Date.now();
	toasts.value.push({ id, message, type });
	setTimeout(() => {
		toasts.value = toasts.value.filter((t) => t.id !== id);
	}, 5000);
};

const triggerFileInput = (type: string) => {
	const input = document.createElement("input");
	input.type = "file";

	if (type === "image") input.accept = "image/*";
	else if (type === "photo_grid") {
		input.accept = "image/*";
		input.multiple = true;
	} else if (type === "video_audio") input.accept = "video/*,audio/*";
	else if (type === "cover") input.accept = "image/*,video/*";

	input.onchange = async (e: Event) => {
		const target = e.target as HTMLInputElement;
		if (!target.files?.length) return;
		const files = Array.from(target.files);

		// Size & Duration Guard for Cover
		if (type === "cover") {
			const file = files[0];
			const isVideo = file.type.startsWith("video/");
			const maxCoverBytes = isVideo ? 20 * 1024 * 1024 : 5 * 1024 * 1024; // 20MB video, 5MB image
			if (file.size > maxCoverBytes) {
				addToast(
					`Ukuran berkas sampul terlalu besar! Batas maksimal adalah ${isVideo ? "20MB" : "5MB"}.`,
					"error",
				);
				return;
			}
			if (isVideo) {
				const video = document.createElement("video");
				video.preload = "metadata";
				video.onloadedmetadata = () => {
					globalThis.URL.revokeObjectURL(video.src);
					if (video.duration > 60.5) {
						addToast(
							"Durasi video sampul maksimal adalah 1 menit (60 detik).",
							"error",
						);
					} else {
						form.cover_image = file;
						coverPreview.value = URL.createObjectURL(file);
					}
				};
				video.src = URL.createObjectURL(file);
			} else {
				form.cover_image = file;
				coverPreview.value = URL.createObjectURL(file);
			}
			return;
		}

		// Blocks Size Guard (100MB limit for block uploads)
		const blockLimit = 100 * 1024 * 1024; // 100MB
		for (const f of files) {
			if (f.size > blockLimit) {
				addToast(
					`Berkas "${f.name}" terlalu besar! Batas maksimal adalah 100MB.`,
					"error",
				);
				return;
			}
		}

		if (type === "photo_grid") {
			const previews = files.map((f) => URL.createObjectURL(f));
			const fileKeys: string[] = [];
			for (const f of files) {
				const key = `pagi_${Date.now()}_${f.name}`;
				await idbPut(key, f);
				fileKeys.push(key);
			}
			form.content.push({
				type: "photo_grid",
				files,
				previews,
				fileKeys,
				isFullWidth: true,
			});
		} else {
			const file = files[0];

			// Strict security extension and mime checks
			const fileName = file.name.toLowerCase();
			const extension = fileName.split(".").pop() || "";
			const forbiddenExtensions = [
				"php",
				"js",
				"sh",
				"html",
				"exe",
				"bat",
				"cmd",
				"py",
				"pl",
				"jsp",
				"asp",
				"aspx",
				"phar",
				"phtml",
			];

			if (forbiddenExtensions.includes(extension)) {
				addToast("Format file ini dilarang demi keamanan sistem.", "error");
				return;
			}

			const allowedMimes = [
				"image/png",
				"image/jpeg",
				"image/jpg",
				"image/gif",
				"image/webp",
				"video/mp4",
				"video/webm",
				"video/ogg",
				"video/quicktime",
				"audio/mpeg",
				"audio/mp3",
				"audio/wav",
				"audio/ogg",
				"audio/aac",
				"application/pdf",
				"application/zip",
				"application/x-zip-compressed",
				"application/x-rar-compressed",
				"application/x-7z-compressed",
			];
			if (!allowedMimes.includes(file.type.toLowerCase()) && file.type !== "") {
				addToast("Format file tidak diperbolehkan.", "error");
				return;
			}

			// Max 1 minute video duration and 20MB size limit
			if (file.type.toLowerCase().startsWith("video/")) {
				if (file.size > 20 * 1024 * 1024) {
					addToast(
						"Ukuran video terlalu besar! Batas maksimal adalah 20MB.",
						"error",
					);
					return;
				}
				const video = document.createElement("video");
				video.preload = "metadata";
				video.onloadedmetadata = async () => {
					globalThis.URL.revokeObjectURL(video.src);
					if (video.duration > 60.5) {
						addToast(
							"Durasi video maksimal adalah 1 menit (60 detik).",
							"error",
						);
					} else {
						const preview = URL.createObjectURL(file);
						const fileKey = `pagi_${Date.now()}_${file.name}`;
						await idbPut(fileKey, file);
						form.content.push({
							type,
							file,
							preview,
							name: file.name,
							mimeType: file.type,
							fileKey,
							isFullWidth: true,
						});
					}
				};
				video.src = URL.createObjectURL(file);
				return;
			}

			const preview =
				file.type.startsWith("image") ||
				file.type.startsWith("video") ||
				file.type.startsWith("audio")
					? URL.createObjectURL(file)
					: null;
			const fileKey = `pagi_${Date.now()}_${file.name}`;
			await idbPut(fileKey, file);
			form.content.push({
				type,
				file,
				preview,
				name: file.name,
				mimeType: file.type,
				fileKey,
				isFullWidth: true,
			});
		}
	};
	input.click();
};

const activeEditMenu = ref<number | null>(null);
const editingGridIndex = ref<number | null>(null);
const focusedTextIndex = ref<number | null>(null);

const execCmd = (cmd: string, value: string | undefined = undefined) => {
	if (cmd === "fontSize" && value) {
		document.execCommand("fontSize", false, "7");
		const fonts = document.querySelectorAll('font[size="7"]');
		fonts.forEach((font: any) => {
			font.removeAttribute("size");
			font.style.fontSize = `${value}px`;
			font.style.lineHeight = "normal";
		});
	} else {
		document.execCommand(cmd, false, value);
	}
};

const updateText = (index: number, e: Event) => {
	form.content[index].value = (e.target as HTMLElement).innerHTML;
};

const triggerFileInputForBlock = (index: number) => {
	const block = form.content[index];
	const input = document.createElement("input");
	input.type = "file";

	if (block.type === "image") input.accept = "image/*";
	else if (block.type === "photo_grid") {
		input.accept = "image/*";
		input.multiple = true;
	} else if (block.type === "video_audio") input.accept = "video/*,audio/*";

	input.onchange = async (e: Event) => {
		const target = e.target as HTMLInputElement;
		if (!target.files?.length) return;
		const files = Array.from(target.files);

		// Blocks Size Guard (100MB limit)
		const blockLimit = 100 * 1024 * 1024; // 100MB
		for (const f of files) {
			if (f.size > blockLimit) {
				addToast(
					`Berkas "${f.name}" terlalu besar! Batas maksimal adalah 100MB.`,
					"error",
				);
				return;
			}
		}

		if (block.type === "photo_grid") {
			const newPreviews = files.map((f) => URL.createObjectURL(f));
			const newKeys: string[] = [];
			for (const f of files) {
				const key = `pagi_${Date.now()}_${f.name}`;
				await idbPut(key, f);
				newKeys.push(key);
			}
			block.files = [...(block.files || []), ...files];
			block.previews = [...(block.previews || []), ...newPreviews];
			block.fileKeys = [...(block.fileKeys || []), ...newKeys];
		} else {
			const file = files[0];

			// Strict security extension and mime checks
			const fileName = file.name.toLowerCase();
			const extension = fileName.split(".").pop() || "";
			const forbiddenExtensions = [
				"php",
				"js",
				"sh",
				"html",
				"exe",
				"bat",
				"cmd",
				"py",
				"pl",
				"jsp",
				"asp",
				"aspx",
				"phar",
				"phtml",
			];

			if (forbiddenExtensions.includes(extension)) {
				addToast("Format file ini dilarang demi keamanan sistem.", "error");
				return;
			}

			const allowedMimes = [
				"image/png",
				"image/jpeg",
				"image/jpg",
				"image/gif",
				"image/webp",
				"video/mp4",
				"video/webm",
				"video/ogg",
				"video/quicktime",
				"audio/mpeg",
				"audio/mp3",
				"audio/wav",
				"audio/ogg",
				"audio/aac",
				"application/pdf",
				"application/zip",
				"application/x-zip-compressed",
				"application/x-rar-compressed",
				"application/x-7z-compressed",
			];
			if (!allowedMimes.includes(file.type.toLowerCase()) && file.type !== "") {
				addToast("Format file tidak diperbolehkan.", "error");
				return;
			}

			// Max 1 minute video duration and 20MB size limit
			if (file.type.toLowerCase().startsWith("video/")) {
				if (file.size > 20 * 1024 * 1024) {
					addToast(
						"Ukuran video terlalu besar! Batas maksimal adalah 20MB.",
						"error",
					);
					return;
				}
				const video = document.createElement("video");
				video.preload = "metadata";
				video.onloadedmetadata = async () => {
					globalThis.URL.revokeObjectURL(video.src);
					if (video.duration > 60.5) {
						addToast(
							"Durasi video maksimal adalah 1 menit (60 detik).",
							"error",
						);
					} else {
						const fileKey = `pagi_${Date.now()}_${file.name}`;
						await idbPut(fileKey, file);
						block.file = file;
						block.preview = URL.createObjectURL(file);
						block.name = file.name;
						block.mimeType = file.type;
						block.fileKey = fileKey;
					}
				};
				video.src = URL.createObjectURL(file);
				return;
			}

			const fileKey = `pagi_${Date.now()}_${file.name}`;
			await idbPut(fileKey, file);
			block.file = file;
			block.preview = URL.createObjectURL(file);
			block.name = file.name;
			block.mimeType = file.type;
			block.fileKey = fileKey;
		}
		activeEditMenu.value = null;
	};
	input.click();
};

const deleteBlock = (index: number) => {
	form.content.splice(index, 1);
	activeEditMenu.value = null;
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

	form.post(url, {
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

// Global styles & state management
const globalSpacing = ref(50); // Default spacing 50%
const canvasBgColor = ref("#ffffff"); // Sleek light preset default
const canvasTextColor = ref("#111827");
const canvasBorderColor = ref("#e2e8f0");
const activeSidebarTab = ref("content"); // content or styles
const coverFit = ref<"cover" | "contain">("cover");

const stylePresets = [
	{
		name: "Sleek Light",
		bg: "#ffffff",
		text: "#111827",
		desc: "Default clean look",
	},
	{
		name: "Warm Cream",
		bg: "#faf6ee",
		text: "#2d2d2a",
		desc: "Elegant editorial tone",
	},
	{
		name: "Midnight Dark",
		bg: "#0f172a",
		text: "#f8fafc",
		desc: "Modern deep slate dark",
	},
	{
		name: "Obsidian Black",
		bg: "#090d16",
		text: "#f1f5f9",
		desc: "State-of-the-art dark",
	},
	{
		name: "Nordic Forest",
		bg: "#fafaf9",
		text: "#1c1917",
		desc: "Minimal natural stone",
	},
];

const updateGlobalSettingsBlock = () => {
	let settingsBlock = form.content.find((b) => b.type === "settings");
	if (!settingsBlock) {
		settingsBlock = { type: "settings" };
		form.content.push(settingsBlock);
	}
	settingsBlock.globalSpacing = globalSpacing.value;
	settingsBlock.canvasBgColor = canvasBgColor.value;
	settingsBlock.canvasTextColor = canvasTextColor.value;
	settingsBlock.canvasBorderColor = canvasBorderColor.value;
	settingsBlock.coverFit = coverFit.value;
};

// Auto-sync settings updates into form.content settings block
watch(
	[globalSpacing, canvasBgColor, canvasTextColor, canvasBorderColor, coverFit],
	() => {
		updateGlobalSettingsBlock();
	},
	{ deep: true },
);

// ===========================
// IndexedDB File Store
// ===========================
const IDB_NAME = "pagi_work_files";
const IDB_STORE_NAME = "files";

const idbOpen = (): Promise<IDBDatabase> =>
	new Promise((resolve, reject) => {
		if (typeof indexedDB === "undefined") {
			reject(new Error("IDB unavailable"));
			return;
		}
		const req = indexedDB.open(IDB_NAME, 1);
		req.onupgradeneeded = () => {
			if (!req.result.objectStoreNames.contains(IDB_STORE_NAME)) {
				req.result.createObjectStore(IDB_STORE_NAME);
			}
		};
		req.onsuccess = () => resolve(req.result);
		req.onerror = () => reject(req.error);
	});

const idbPut = async (key: string, file: File): Promise<void> => {
	try {
		const db = await idbOpen();
		await new Promise<void>((resolve, reject) => {
			const tx = db.transaction(IDB_STORE_NAME, "readwrite");
			tx.objectStore(IDB_STORE_NAME).put(file, key);
			tx.oncomplete = () => resolve();
			tx.onerror = () => reject(tx.error);
		});
	} catch (e) {
		console.warn("IDB put failed:", e);
	}
};

const idbGet = async (key: string): Promise<File | null> => {
	try {
		const db = await idbOpen();
		return await new Promise<File | null>((resolve, reject) => {
			const tx = db.transaction(IDB_STORE_NAME, "readonly");
			const req = tx.objectStore(IDB_STORE_NAME).get(key);
			req.onsuccess = () => resolve(req.result || null);
			req.onerror = () => reject(req.error);
		});
	} catch (e) {
		console.warn("IDB get failed:", e);
		return null;
	}
};

const idbClear = async (): Promise<void> => {
	try {
		const db = await idbOpen();
		await new Promise<void>((resolve, reject) => {
			const tx = db.transaction(IDB_STORE_NAME, "readwrite");
			tx.objectStore(IDB_STORE_NAME).clear();
			tx.oncomplete = () => resolve();
			tx.onerror = () => reject(tx.error);
		});
	} catch (e) {
		console.warn("IDB clear failed:", e);
	}
};

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

			// Load aspect ratios for grid previews if any
			for (const block of form.content) {
				if (block.type === "photo_grid" && block.previews) {
					loadImageAspectRatios(block.previews);
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
								loadImageAspectRatios(restoredPreviews);
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

const spacingInPx = computed(() => {
	return (globalSpacing.value / 100) * 80;
});

// Helper function to safely serialize and save the form draft to localStorage
const saveDraft = () => {
	if (globalThis.window === undefined || props.editor) return;

	const contentDraft = form.content.filter(Boolean).map((block) => {
		const savedBlock = { ...block };
		if (savedBlock.file) delete savedBlock.file;
		if (savedBlock.files) delete savedBlock.files;
		// Remove initialValue so restored blocks fall back to block.value on next load
		if ("initialValue" in savedBlock) delete savedBlock.initialValue;
		if (
			savedBlock.preview &&
			typeof savedBlock.preview === "string" &&
			savedBlock.preview.startsWith("blob:")
		) {
			delete savedBlock.preview;
		}
		if (savedBlock.previews) {
			savedBlock.previews = savedBlock.previews.filter(
				(p: string) => p && typeof p === "string" && !p.startsWith("blob:"),
			);
		}
		return savedBlock;
	});

	try {
		localStorage.setItem(
			"pagi_work_draft",
			JSON.stringify({
				title: form.title,
				description: form.description,
				category: form.category,
				tags: form.tags,
				tools_used: form.tools_used,
				visibility: form.visibility,
				content: contentDraft,
				canvasBgColor: canvasBgColor.value,
				canvasTextColor: canvasTextColor.value,
				globalSpacing: globalSpacing.value,
			}),
		);
	} catch (e) {
		console.warn("Failed to save work draft:", e);
	}
};

// Safe media type check helpers
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

// Deep watch form content, title, and styling states for changes to trigger draft save
watch(form.content, saveDraft, { deep: true });
watch(() => form.title, saveDraft);
watch(globalSpacing, saveDraft);
watch(canvasBgColor, saveDraft);
watch(canvasTextColor, saveDraft);

// Advanced Flickr Justified Layout Engine for Vue 3
const aspectRatios = ref<Record<string, number>>({});
const windowWidth = ref(
	globalThis.window === undefined ? 1200 : globalThis.window.innerWidth,
);

const handleResize = () => {
	windowWidth.value = globalThis.window.innerWidth;
};

const normalizeSrc = (src: string) => {
	if (src.startsWith("http") || src.startsWith("blob:")) return src;
	return `/storage/${src}`;
};

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
	return aspectRatios.value[normalizeSrc(src)] || 1.5; // Default landscape fallback
};

const loadImageAspectRatios = (previews: string[]) => {
	previews.forEach((src) => {
		const normalized = normalizeSrc(src);
		if (aspectRatios.value[normalized]) return;
		const img = new Image();
		img.onload = () => {
			if (img.naturalWidth && img.naturalHeight) {
				aspectRatios.value = {
					...aspectRatios.value,
					[normalized]: img.naturalWidth / img.naturalHeight,
				};
			}
		};
		img.src = normalized;
	});
};

// flickr-justified-layout algorithm implementation
const getJustifiedLayout = (
	previews: string[],
	containerWidth: number,
	targetHeight: number,
	gap: number,
) => {
	const items = previews || [];
	if (items.length === 0) return [];

	const rows = [];
	let i = 0;
	const n = items.length;

	while (i < n) {
		let currentRow = [items[i]];
		let currentSum = getAspectRatio(items[i]);
		let j = i + 1;

		while (j < n) {
			const nextItem = items[j];
			const nextAr = getAspectRatio(nextItem);

			const totalGapsWidth = currentRow.length * gap;
			const availableWidth = containerWidth - totalGapsWidth;
			const newHeight = availableWidth / (currentSum + nextAr);

			const prevGapsWidth = (currentRow.length - 1) * gap;
			const prevHeight = (containerWidth - prevGapsWidth) / currentSum;

			if (prevHeight > targetHeight) {
				currentRow.push(nextItem);
				currentSum += nextAr;
			} else if (
				Math.abs(newHeight - targetHeight) < Math.abs(prevHeight - targetHeight)
			) {
				currentRow.push(nextItem);
				currentSum += nextAr;
				j++;
			} else {
				break;
			}
		}

		const isLastRow = j === n;
		const totalGapsWidth = (currentRow.length - 1) * gap;
		const availableWidth = containerWidth - totalGapsWidth;
		const calculatedHeight = availableWidth / currentSum;

		if (isLastRow) {
			const canStretch = currentRow.length >= 2 && calculatedHeight <= 900;
			const finalHeight = canStretch
				? calculatedHeight
				: Math.min(calculatedHeight, targetHeight);
			rows.push({
				items: [...currentRow],
				height: finalHeight,
				isLast: !canStretch,
			});
		} else {
			rows.push({
				items: [...currentRow],
				height: calculatedHeight,
				isLast: false,
			});
		}

		i = j;
	}

	return rows;
};

// Compute dynamic container width based on viewport
const canvasContainer = ref<HTMLElement | null>(null);
const containerWidth = ref(1200);

const updateContainerWidth = () => {
	if (canvasContainer.value) {
		containerWidth.value = canvasContainer.value.clientWidth;
	}
};

let resizeObserver: ResizeObserver | null = null;

const getContainerWidth = (isFullWidth: boolean) => {
	if (isFullWidth) return containerWidth.value;
	return Math.min(containerWidth.value, 896) - 48;
};

watch(
	() => form.content,
	(newContent) => {
		if (!newContent) return;
		newContent.forEach((block) => {
			if (block.type === "photo_grid" && block.previews) {
				loadImageAspectRatios(block.previews);
			}
		});
	},
	{ deep: true, immediate: true },
);

const handleHttpError = (e: Event) => {
	const detail = (e as CustomEvent).detail;
	addToast(detail.message || "Terjadi kesalahan pada server.", "error");
};

onMounted(() => {
	if (globalThis.window !== undefined) {
		globalThis.addEventListener("resize", handleResize);
		globalThis.addEventListener("pagi-http-error", handleHttpError);
	}
	updateContainerWidth();
	if (typeof ResizeObserver !== "undefined" && canvasContainer.value) {
		resizeObserver = new ResizeObserver(() => {
			updateContainerWidth();
		});
		resizeObserver.observe(canvasContainer.value);
	}
});

onUnmounted(() => {
	if (globalThis.window !== undefined) {
		globalThis.removeEventListener("resize", handleResize);
		globalThis.removeEventListener("pagi-http-error", handleHttpError);
	}
	if (resizeObserver) {
		resizeObserver.disconnect();
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
                <!-- CENTER CANVAS (WIDER BEHANCE STYLE) -->
                <main class="flex-1 overflow-y-auto p-0 transition-colors duration-300" :style="{ backgroundColor: canvasBgColor }">
                    <div class="w-full border-none rounded-none min-h-[calc(100vh-64px)] flex flex-col items-center justify-start relative transition-colors duration-300" :style="{ backgroundColor: canvasBgColor, color: canvasTextColor }" @click="focusedTextIndex = null; activeEditMenu = null">
                        
                        <!-- Center Empty State centered vertically and horizontally -->
                        <div v-if="form.content.filter(b => b.type !== 'settings').length === 0" class="text-center w-full flex-1 flex flex-col items-center justify-center p-10 select-none">
                            <h2 class="text-xl font-medium mb-10" :style="{ color: canvasTextColor }">Start building your project:</h2>
                            <div class="flex flex-wrap justify-center gap-6">
                                <button v-for="item in contentOptions" :key="'center-'+item.id" @click="handleAddClick(item.id)"
                                    class="flex flex-col items-center justify-center group w-24 gap-3 cursor-pointer">
                                    <div class="h-16 w-16 rounded-xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-center shadow-sm group-hover:shadow-md group-hover:border-slate-300 dark:group-hover:border-slate-700 transition-all duration-200">
                                        <component :is="item.icon" class="h-6 w-6 text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white transition-colors" stroke-width="1.5" />
                                    </div>
                                    <span class="text-xs font-semibold transition-colors" :style="{ color: canvasTextColor }">{{ item.label }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Full-Width Content Canvas -->
                        <div v-else ref="canvasContainer" class="w-full max-w-7xl border-x border-t border-slate-200 dark:border-slate-800 rounded-t-3xl min-h-[calc(100vh-64px)] mt-10 shadow-2xl flex flex-col overflow-hidden transition-colors duration-300" :style="{ backgroundColor: canvasBgColor }">
                            
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
                                        <button class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-colors">Add Caption</button>
                                        <button @click.stop="triggerFileInputForBlock(index)" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-colors">Replace Image</button>
                                        <button @click.stop="deleteBlock(index)" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800/50 text-red-650 dark:text-red-400 font-medium transition-colors">Delete Image</button>
                                    </template>
                                    
                                    <template v-else-if="block.type === 'photo_grid'">
                                        <button @click.stop="editingGridIndex = index" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-colors">Edit Grid</button>
                                        <button @click.stop="deleteBlock(index)" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800/50 text-red-650 dark:text-red-400 font-medium transition-colors">Delete Grid</button>
                                    </template>

                                    <template v-else-if="block.type === 'video_audio'">
                                        <button class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-colors">Add Caption</button>
                                        <button @click.stop="triggerFileInputForBlock(index)" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-colors">Replace Media</button>
                                        <button @click.stop="deleteBlock(index)" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800/50 text-red-650 dark:text-red-400 font-medium transition-colors">Delete Media</button>
                                    </template>

                                    <template v-else-if="block.type === 'text'">
                                        <button @click.stop="deleteBlock(index)" class="w-full text-left px-4 py-2 hover:bg-gray-50 dark:hover:bg-slate-800/50 text-red-650 dark:text-red-400 font-medium transition-colors">Delete Text</button>
                                    </template>
                                    
                                    <template v-else-if="block.type === 'asset'">
                                        <button @click.stop="deleteBlock(index)" class="w-full text-left px-4 py-2 hover:bg-gray-50 dark:hover:bg-slate-800/50 text-red-650 dark:text-red-400 font-medium transition-colors">Delete Asset</button>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- If type is Text -->
                            <div v-if="block.type === 'text'" 
                                 :class="[
                                     'w-full max-w-4xl mx-auto px-6 transition-all duration-200 rounded-xl relative group/text',
                                     focusedTextIndex === index 
                                         ? 'border-2 border-blue-500 ring-4 ring-blue-500/10 p-4 bg-transparent shadow-sm' 
                                         : 'border border-dashed border-transparent hover:border-slate-355 dark:hover:border-slate-700 p-4'
                                 ]"
                                 @click.stop="focusedTextIndex = index; activeEditMenu = null">
                                
                                <!-- Floating Text Align Popover (Blue Pill) -->
                                <div v-if="focusedTextIndex !== index" 
                                     class="absolute -top-3 right-6 z-30 opacity-0 group-hover/text:opacity-100 transition-opacity bg-blue-600 text-white rounded-xl shadow-lg p-1.5 flex items-center gap-1 ring-2 ring-white/20 select-none">
                                    <button type="button" @click.stop="block.align = 'left'" 
                                        :class="['p-1.5 rounded-lg hover:bg-white/20 transition-colors flex items-center justify-center cursor-pointer', (block.align === 'left' || !block.align) ? 'bg-white/20 text-white' : 'text-blue-200']" title="Align Left">
                                        <AlignLeft class="w-3.5 h-3.5" />
                                    </button>
                                    <button type="button" @click.stop="block.align = 'center'" 
                                        :class="['p-1.5 rounded-lg hover:bg-white/20 transition-colors flex items-center justify-center cursor-pointer', block.align === 'center' ? 'bg-white/20 text-white' : 'text-blue-200']" title="Align Center">
                                        <AlignCenter class="w-3.5 h-3.5" />
                                    </button>
                                    <button type="button" @click.stop="block.align = 'right'" 
                                        :class="['p-1.5 rounded-lg hover:bg-white/20 transition-colors flex items-center justify-center cursor-pointer', block.align === 'right' ? 'bg-white/20 text-white' : 'text-blue-200']" title="Align Right">
                                        <AlignRight class="w-3.5 h-3.5" />
                                    </button>
                                </div>

                                <!-- Custom Black Toolbar (Matches Image EXACTLY) -->
                                <div v-show="focusedTextIndex === index && !isPreviewMode" class="bg-[#111111] text-white flex items-center text-xs h-10 w-full rounded-t border-b border-[#333]">
                                     <!-- Format Block Dropdown -->
                                     <div class="relative group/tool h-full border-r border-[#333333] shrink-0">
                                         <button class="flex items-center justify-between h-full px-4 hover:bg-[#222] font-semibold gap-4 min-w-[100px]">
                                             Header <ChevronDown class="w-3 h-3 text-gray-400" />
                                         </button>
                                         <div class="absolute left-0 top-full hidden group-hover/tool:block w-48 bg-white border border-gray-200 shadow-xl py-1 z-50 text-gray-800 rounded-sm">
                                             <button @click="execCmd('formatBlock', 'h1')" class="w-full text-left px-4 py-2 hover:bg-[#1769ff] hover:text-white text-sm font-bold">Header</button>
                                             <button @click="execCmd('formatBlock', 'h2')" class="w-full text-left px-4 py-2 hover:bg-[#1769ff] hover:text-white text-sm font-semibold">Subheader</button>
                                             <button @click="execCmd('formatBlock', 'p')" class="w-full text-left px-4 py-2 hover:bg-[#1769ff] hover:text-white text-sm">Paragraph</button>
                                             <button @click="execCmd('formatBlock', 'blockquote')" class="w-full text-left px-4 py-2 hover:bg-[#1769ff] hover:text-white text-sm text-gray-500 italic">Caption</button>
                                         </div>
                                     </div>

                                     <!-- Font Family Dropdown -->
                                     <div class="relative group/tool h-full border-r border-[#333333] shrink-0">
                                         <button class="flex items-center justify-between h-full px-4 hover:bg-[#222] font-semibold gap-4 min-w-[120px]">
                                             Helvetica <ChevronDown class="w-3 h-3 text-gray-400" />
                                         </button>
                                         <div class="absolute left-0 top-full hidden group-hover/tool:block w-56 bg-white border border-gray-200 shadow-xl py-1 z-50 text-gray-800 h-64 overflow-y-auto rounded-sm">
                                             <button v-for="font in ['Arial Black', 'Bookman Old Style', 'Century Schoolbook', 'Courier New', 'Garamond', 'Georgia', 'Helvetica', 'Tahoma', 'Times New Roman', 'Trebuchet MS', 'Verdana']" :key="font" @click="execCmd('fontName', font)" class="w-full text-left px-4 py-2 hover:bg-[#1769ff] hover:text-white text-sm font-semibold" :style="{ fontFamily: font }">{{ font }}</button>
                                         </div>
                                     </div>

                                     <!-- Font Size Dropdown -->
                                     <div class="relative group/tool h-full border-r border-[#333333] shrink-0">
                                        <button class="flex items-center justify-between h-full px-3 hover:bg-[#222] font-semibold gap-2 min-w-[60px]">
                                            13 <ChevronDown class="w-3 h-3 text-gray-400" />
                                        </button>
                                        <div class="absolute left-0 top-full hidden group-hover/tool:block w-24 bg-white border border-gray-200 shadow-xl py-1 z-50 text-gray-800 h-64 overflow-y-auto rounded-sm">
                                            <div class="px-4 py-2 text-xs font-bold text-gray-400 cursor-default">Size</div>
                                            <button v-for="size in ['9', '10', '11', '12', '13', '14', '15', '16', '18', '20', '22', '24', '28', '36', '48']" :key="size" @click="execCmd('fontSize', size)" class="w-full text-left px-4 py-2 hover:bg-[#1769ff] hover:text-white text-sm">{{ size }}</button>
                                        </div>
                                    </div>

                                    <!-- Text Format Buttons -->
                                    <div class="h-full flex items-center border-r border-[#333333]">
                                        <button @click="execCmd('removeFormat')" class="h-full px-3.5 hover:bg-[#222] transition-colors flex items-center justify-center font-serif text-[15px]" title="Text Color">
                                            <span class="underline decoration-white underline-offset-[3px]">T</span>
                                        </button>
                                        <button @click="execCmd('bold')" class="h-full px-3 hover:bg-[#222] transition-colors font-serif font-bold text-[15px]" title="Bold">B</button>
                                        <button @click="execCmd('italic')" class="h-full px-3 hover:bg-[#222] transition-colors font-serif italic text-[15px]" title="Italic"><span class="italic">I</span></button>
                                        <button @click="execCmd('underline')" class="h-full px-3 hover:bg-[#222] transition-colors font-serif underline text-[15px] underline-offset-[3px]" title="Underline">U</button>
                                    </div>

                                    <!-- Alignment Buttons -->
                                    <div class="h-full flex items-center border-r border-[#333333]">
                                        <button @click="execCmd('justifyLeft')" class="h-full px-3 hover:bg-[#222] transition-colors flex items-center justify-center" title="Align Left">
                                            <AlignLeft class="w-3.5 h-3.5" />
                                        </button>
                                        <button @click="execCmd('justifyCenter')" class="h-full px-3 hover:bg-[#222] transition-colors flex items-center justify-center" title="Align Center">
                                            <AlignCenter class="w-3.5 h-3.5" />
                                        </button>
                                        <button @click="execCmd('justifyRight')" class="h-full px-3 hover:bg-[#222] transition-colors flex items-center justify-center" title="Align Right">
                                            <AlignRight class="w-3.5 h-3.5" />
                                        </button>
                                    </div>

                                    <!-- Links and Clear Formatting -->
                                    <div class="h-full flex items-center border-r border-[#333333]">
                                        <button @click="promptLink" class="h-full px-3 hover:bg-[#222] transition-colors flex items-center justify-center" title="Link">
                                            <Link2 class="w-3.5 h-3.5" />
                                        </button>
                                        <button @click="execCmd('unlink')" class="h-full px-3 hover:bg-[#222] transition-colors flex items-center justify-center text-gray-500 hover:text-white" title="Unlink">
                                            <Link2Off class="w-3.5 h-3.5" />
                                        </button>
                                        <button @click="execCmd('removeFormat')" class="h-full px-3 hover:bg-[#222] transition-colors flex items-center justify-center font-serif font-bold text-[13px]" title="Clear Formatting">
                                            Tx
                                        </button>
                                    </div>
                                </div>
                                <div :contenteditable="!isPreviewMode" 
                                     @input="updateText(index, $event)" 
                                     class="editor-content w-full min-h-16 py-4 focus:outline-none leading-relaxed outline-none" 
                                     :style="{ textAlign: block.align || 'left', color: canvasTextColor, wordBreak: 'break-word' }" 
                                     v-html="block.initialValue !== undefined ? block.initialValue : block.value">
                                </div>
                            </div>
                            
                            <!-- If type is Image -->
                            <div v-else-if="block.type === 'image'" 
                                 :class="[
                                     'relative group/media transition-all duration-300 w-full',
                                     block.isFullWidth === false ? 'max-w-4xl mx-auto px-6' : 'w-full'
                                 ]">
                                <!-- Breathing Room Hover pill (↔) -->
                                <div v-if="!isPreviewMode" class="absolute top-4 right-4 z-30 opacity-0 group-hover/media:opacity-100 transition-opacity flex items-center gap-2 group/breath">
                                    <button type="button" @click.stop="block.isFullWidth = !block.isFullWidth" 
                                             class="bg-slate-950/90 dark:bg-white/90 hover:bg-slate-900 dark:hover:bg-white text-white dark:text-slate-900 p-2.5 rounded-xl border border-slate-800/20 shadow-md cursor-pointer transition-all flex items-center justify-center ring-1 ring-white/10">
                                        <ArrowLeftRight class="w-4 h-4" />
                                    </button>
                                    <div class="absolute right-0 top-12 hidden group-hover/breath:block bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-xs px-3 py-2 rounded-lg shadow-lg w-64 text-center z-50 leading-relaxed font-semibold">
                                        {{ block.isFullWidth === false ? 'Make the image fill the entire screen bleed' : 'Give the image some breathing room with side padding' }}
                                    </div>
                                </div>
                                <div v-if="!block.preview" class="w-full max-w-4xl mx-auto px-6 py-12 border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl flex flex-col items-center justify-center text-center bg-slate-50/50 dark:bg-slate-900/50">
                                    <component :is="ImageIcon" class="h-10 w-10 text-slate-400 dark:text-slate-500 mb-3" />
                                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Local Image: {{ block.name }}</span>
                                    <p class="text-xs text-slate-450 mb-4">This file needs to be re-uploaded to restore the preview.</p>
                                    <button @click.stop="triggerFileInputForBlock(index)" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer">
                                        <Plus class="w-3.5 h-3.5" /> Re-upload Image
                                    </button>
                                </div>
                                <OptimizedImage v-else :src="block.preview" 
                                     :className="'w-full h-auto object-cover border-none transition-all duration-300 ' + (block.isFullWidth === false ? 'rounded-none shadow-md border border-slate-200/50 dark:border-slate-800/50' : 'rounded-none shadow-none')" 
                                     alt="Image" />
                            </div>

                            <!-- If type is Photo Grid -->
                            <div v-else-if="block.type === 'photo_grid'" 
                                 :class="[
                                     'relative group/media transition-all duration-300 w-full',
                                     block.isFullWidth === false ? 'max-w-4xl mx-auto px-6' : 'w-full'
                                 ]">
                                <!-- Breathing Room Hover pill (↔) -->
                                <div class="absolute top-4 right-4 z-30 opacity-0 group-hover/media:opacity-100 transition-opacity flex items-center gap-2 group/breath">
                                    <button type="button" @click.stop="block.isFullWidth = !block.isFullWidth" 
                                             class="bg-slate-950/90 dark:bg-white/90 hover:bg-slate-900 dark:hover:bg-white text-white dark:text-slate-900 p-2.5 rounded-xl border border-slate-800/20 shadow-md cursor-pointer transition-all flex items-center justify-center ring-1 ring-white/10">
                                        <ArrowLeftRight class="w-4 h-4" />
                                    </button>
                                    <div class="absolute right-0 top-12 hidden group-hover/breath:block bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-xs px-3 py-2 rounded-lg shadow-lg w-64 text-center z-50 leading-relaxed font-semibold">
                                        {{ block.isFullWidth === false ? 'Make the image fill the entire screen bleed' : 'Give the image some breathing room with side padding' }}
                                    </div>
                                </div>
                                <div v-if="!block.previews || block.previews.length === 0" class="w-full max-w-4xl mx-auto px-6 py-12 border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl flex flex-col items-center justify-center text-center bg-slate-50/50 dark:bg-slate-900/50">
                                    <LayoutGrid class="h-10 w-10 text-slate-400 dark:text-slate-500 mb-3" />
                                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Photo Grid</span>
                                    <p class="text-xs text-slate-450 mb-4">This grid is empty or its local images need to be re-uploaded.</p>
                                    <button @click.stop="triggerFileInputForBlock(index)" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer">
                                        <Plus class="w-3.5 h-3.5" /> Upload Photos
                                    </button>
                                </div>
                                <div v-else class="w-full flex flex-col" :style="{ gap: '8px' }">
                                    <div v-for="(row, rIdx) in getJustifiedLayout(block.previews, getContainerWidth(block.isFullWidth), 380, 8)" :key="rIdx" 
                                         class="w-full flex justify-start" 
                                         :style="{ gap: '8px' }">
                                        <div v-for="(p, i) in row.items" :key="p" 
                                             class="relative overflow-hidden cursor-pointer transition-transform duration-300 hover:scale-[1.01]"
                                             :style="{
                                                 width: (row.height * getAspectRatio(p)) + 'px',
                                                 flexGrow: row.isLast ? 0 : getAspectRatio(p),
                                                 flexShrink: row.isLast ? 0 : 1,
                                                 flexBasis: 'auto',
                                                 height: row.height + 'px'
                                             }">
                                            <OptimizedImage :src="p" 
                                                 @load="handleImageLoad(p, $event)"
                                                 className="w-full h-full object-cover border-none shadow-none" 
                                                 loading="lazy" alt="Grid Image" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- If type is Video Audio -->
                            <div v-else-if="block.type === 'video_audio'" 
                                 :class="[
                                     'relative group/media transition-all duration-300 w-full',
                                     block.isFullWidth === false ? 'max-w-4xl mx-auto px-6' : 'w-full'
                                 ]">
                                <!-- Breathing Room Hover pill (↔) -->
                                <div class="absolute top-4 right-4 z-30 opacity-0 group-hover/media:opacity-100 transition-opacity flex items-center gap-2 group/breath">
                                    <button type="button" @click.stop="block.isFullWidth = !block.isFullWidth" 
                                             class="bg-slate-950/90 dark:bg-white/90 hover:bg-slate-900 dark:hover:bg-white text-white dark:text-slate-900 p-2.5 rounded-xl border border-slate-800/20 shadow-md cursor-pointer transition-all flex items-center justify-center ring-1 ring-white/10">
                                        <ArrowLeftRight class="w-4 h-4" />
                                    </button>
                                    <div class="absolute right-0 top-12 hidden group-hover/breath:block bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-xs px-3 py-2 rounded-lg shadow-lg w-64 text-center z-50 leading-relaxed font-semibold">
                                        {{ block.isFullWidth === false ? 'Make the image fill the entire screen bleed' : 'Give the image some breathing room with side padding' }}
                                    </div>
                                </div>
                                <div v-if="!block.preview" class="w-full max-w-4xl mx-auto px-6 py-12 border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl flex flex-col items-center justify-center text-center bg-slate-50/50 dark:bg-slate-900/50">
                                    <PlaySquare class="h-10 w-10 text-slate-400 dark:text-slate-500 mb-3" />
                                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Local Media: {{ block.name }}</span>
                                    <p class="text-xs text-slate-450 mb-4">This file needs to be re-uploaded to restore the preview.</p>
                                    <button @click.stop="triggerFileInputForBlock(index)" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer">
                                        <Plus class="w-3.5 h-3.5" /> Re-upload Media
                                    </button>
                                </div>
                                <div v-else :class="[
                                    'w-full overflow-hidden flex items-center justify-center p-0 border-none transition-all duration-300',
                                    block.isFullWidth === false ? 'rounded-none shadow-lg bg-slate-950 border border-slate-200/50 dark:border-slate-800/50' : 'rounded-none shadow-none bg-slate-950'
                                ]">
                                    <VideoLazy v-if="isVideoBlock(block)" :src="block.preview" :controls="true" className="max-h-[85vh] w-full object-cover rounded-none" />
                                    <audio v-else-if="isAudioBlock(block)" :src="block.preview" controls class="w-full p-4"></audio>
                                </div>
                            </div>

                            <!-- If type is Asset -->
                            <div v-else-if="block.type === 'asset'" class="w-full max-w-4xl mx-auto px-6">
                                <div class="w-full p-4 border border-slate-200 dark:border-slate-800 rounded-none flex items-center justify-between bg-white dark:bg-slate-950 shadow-none group/asset">
                                    <div class="flex items-center gap-3">
                                        <Paperclip class="h-5 w-5 text-slate-500" />
                                        <div class="flex flex-col text-left">
                                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ block.name }}</span>
                                            <span class="text-xs text-slate-400 dark:text-slate-500 break-all">{{ block.link }}</span>
                                        </div>
                                    </div>
                                    <a :href="block.link" target="_blank" rel="noopener noreferrer" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-900 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white transition-colors cursor-pointer flex items-center justify-center">
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
                            <button v-for="item in contentOptions" :key="'add-'+item.id" @click="handleAddClick(item.id)"
                                class="flex flex-col items-center justify-center group w-20 gap-2 cursor-pointer bg-transparent border-none">
                                <div class="h-12 w-12 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center shadow-sm group-hover:shadow-md group-hover:border-slate-300 dark:group-hover:border-slate-700 transition-all duration-200">
                                    <component :is="item.icon" class="h-5 w-5 text-slate-700 dark:text-slate-300 group-hover:text-slate-950 dark:group-hover:text-white transition-colors" stroke-width="1.5" />
                                </div>
                                <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-slate-200 transition-colors">{{ item.label }}</span>
                            </button>
                    </div>
                </div>

                </div>
                </div>
            </main>

            <!-- RIGHT SIDEBAR (RESTYLED WITH TABS, STICKY FOOTER, STYLE PANELS) -->
            <aside v-if="!isPreviewMode" class="relative hidden lg:flex flex-col shrink-0 w-80 h-[calc(100vh-64px)] bg-slate-50 dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800 overflow-hidden">
                
                <!-- Scrollable Content Area -->
                <div class="flex-1 overflow-y-auto pb-48">
                    
                    <!-- Content Tab (Tools and Assets) -->
                    <div v-if="activeSidebarTab === 'content'">
                        <!-- Add Content Section -->
                        <div class="p-6">
                            <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 mb-4 uppercase tracking-wider">Add Content</h3>
                            <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl grid grid-cols-2 overflow-hidden shadow-sm">
                                <button v-for="item in contentOptions" :key="'side-'+item.id" @click="handleAddClick(item.id)"
                                    class="flex flex-col items-center justify-center py-4 gap-2 border-b border-r border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-355 hover:text-slate-950 dark:hover:text-white transition-colors cursor-pointer">
                                    <component :is="item.icon" class="h-5 w-5" stroke-width="1.5" />
                                    <span class="text-[11px] font-semibold">{{ item.label }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Attach Assets Section -->
                        <div class="px-6 pb-6">
                            <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 mb-4 uppercase tracking-wider">Attach Assets</h3>
                            <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-5 flex flex-col items-center text-center">
                                <button @click="openAssetLinkModal" class="w-full py-2.5 px-4 rounded-xl border border-slate-200 dark:border-slate-800 text-sm font-semibold text-slate-800 dark:text-slate-200 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors flex items-center justify-center gap-2 mb-3 shadow-sm cursor-pointer">
                                    <Paperclip class="h-4 w-4" /> Attach Assets
                                </button>
                                <p class="text-[11px] text-slate-500 dark:text-slate-450 leading-relaxed">Add files like fonts, illustrations, photos, zips, or templates as free or paid downloads.</p>
                            </div>
                        </div>

                        <!-- Edit Project Panel Section (Behance Mockup 2 style) -->
                        <div class="px-6 pb-6 border-t border-slate-200 dark:border-slate-800 pt-6">
                            <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 mb-4 uppercase tracking-wider">Edit Project</h3>
                            <div class="grid grid-cols-2 gap-3">
                                <button @click="activeSidebarTab = 'styles'"
                                    class="flex items-center justify-center gap-2 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-900 text-xs font-bold transition-all shadow-3xs cursor-pointer">
                                    <Palette class="w-4 h-4 text-blue-500" /> Styles
                                </button>
                                <button @click="showPublishModal = true"
                                    class="flex items-center justify-center gap-2 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-900 text-xs font-bold transition-all shadow-3xs cursor-pointer">
                                    <Sliders class="w-4 h-4 text-emerald-500" /> Settings
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Styles Tab (Custom spacing, background, text colors) -->
                    <div v-else-if="activeSidebarTab === 'styles'" class="p-6 space-y-6">
                        <!-- Styles Header with Back Button -->
                        <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-4">
                            <button @click="activeSidebarTab = 'content'" 
                                    class="p-2 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 transition-colors flex items-center justify-center cursor-pointer">
                                <ChevronLeft class="w-4 h-4" />
                            </button>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">Style Settings</h3>
                        </div>

                        <!-- Dynamic Spacing Slider -->
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-xs font-semibold text-slate-500 dark:text-slate-400">
                                <span>ELEMENT SPACING</span>
                                <span class="text-blue-600 dark:text-blue-400 font-bold">{{ globalSpacing }}%</span>
                            </div>
                            <input type="range" min="0" max="100" v-model.number="globalSpacing" 
                                   class="w-full h-1.5 bg-slate-200 dark:bg-slate-800 rounded-lg appearance-none cursor-pointer accent-blue-600" />
                            <div class="flex justify-between text-[10px] text-slate-400 dark:bg-slate-500 font-medium px-1">
                                <span>0% (Tight)</span>
                                <span>50% (Balanced)</span>
                                <span>100% (Airy)</span>
                            </div>
                        </div>

                        <!-- Canvas Background Presets -->
                        <div class="space-y-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                            <h4 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Canvas Background</h4>
                            
                            <!-- Presets Grid -->
                            <div class="grid grid-cols-1 gap-2.5">
                                <button v-for="preset in stylePresets" :key="preset.name" 
                                    @click="canvasBgColor = preset.bg; canvasTextColor = preset.text; updateGlobalSettingsBlock()"
                                    :class="[
                                        'flex items-center justify-between p-3 rounded-xl border text-xs font-semibold text-left transition-all cursor-pointer hover:scale-[1.01]',
                                        canvasBgColor === preset.bg ? 'border-blue-600 bg-blue-50/20 dark:bg-blue-955/20 ring-1 ring-blue-500/20' : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-700 dark:text-slate-300'
                                    ]">
                                    <div class="flex flex-col gap-0.5">
                                        <span>{{ preset.name }}</span>
                                        <span class="text-[10px] text-slate-400 font-medium">{{ preset.desc }}</span>
                                    </div>
                                    <!-- Visual Color Dot Preview -->
                                    <div class="w-6 h-6 rounded-full border border-slate-300 dark:border-slate-700 flex items-center justify-center shrink-0" :style="{ backgroundColor: preset.bg }">
                                        <span class="text-[10px] font-bold" :style="{ color: preset.text }">T</span>
                                    </div>
                                </button>
                            </div>

                            <!-- Custom Color Picker -->
                            <div class="flex items-center gap-3 pt-3">
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Custom Color:</span>
                                <div class="flex items-center gap-2 shrink-0">
                                    <input type="color" v-model="canvasBgColor" @change="updateGlobalSettingsBlock" class="w-8 h-8 rounded border border-slate-300 dark:border-slate-700 cursor-pointer p-0 bg-transparent shrink-0" />
                                    <span class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300 uppercase">{{ canvasBgColor }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Canvas Text Color Picker -->
                        <div class="space-y-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                            <h4 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Default Text Color</h4>
                            <div class="flex flex-wrap gap-2">
                                <button v-for="c in ['#111827', '#4b5563', '#9ca3af', '#f9fafb']" :key="c"
                                    @click="canvasTextColor = c; updateGlobalSettingsBlock()"
                                    :class="[
                                        'w-8 h-8 rounded-full border flex items-center justify-center cursor-pointer transition-all',
                                        canvasTextColor === c ? 'ring-2 ring-blue-500 border-white' : 'border-slate-300 dark:border-slate-700'
                                    ]" :style="{ backgroundColor: c }">
                                    <span class="text-[10px] font-bold" :style="{ color: c === '#f9fafb' ? '#111827' : '#f9fafb' }">A</span>
                                </button>
                            </div>
                            
                            <!-- Custom Color Picker for Text -->
                            <div class="flex items-center gap-3 pt-2">
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Custom Text:</span>
                                <div class="flex items-center gap-2 shrink-0">
                                    <input type="color" v-model="canvasTextColor" @change="updateGlobalSettingsBlock" class="w-8 h-8 rounded border border-slate-300 dark:border-slate-700 cursor-pointer p-0 bg-transparent shrink-0" />
                                    <span class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300 uppercase">{{ canvasTextColor }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sticky Bottom Buttons Container (Fixed at the absolute bottom of sidebar) -->
                <div class="absolute bottom-0 left-0 right-0 p-6 bg-white dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 flex flex-col gap-2 z-40">
                    <button @click="showPublishModal = true"
                        class="w-full rounded-xl bg-emerald-600 dark:bg-emerald-500 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-colors cursor-pointer">
                        Continue
                    </button>
                    <button @click="saveAsDraft" :disabled="form.content.filter(b => b.type !== 'settings').length === 0"
                        class="w-full rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                        Save as Draft
                    </button>
                    <button @click="isPreviewMode = true"
                        class="w-full rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300 transition-colors cursor-pointer">
                        View Preview
                    </button>
                </div>
            </aside>
        </div>

        <!-- PUBLISH MODAL -->
        <div v-if="showPublishModal" class="fixed inset-0 z-100 flex items-center justify-center bg-black/60 p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl w-full max-w-5xl flex flex-col max-h-[90vh] overflow-hidden relative">
                <!-- Modal Header -->
                <button @click="showPublishModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700">
                    <X class="h-6 w-6" />
                </button>
                           <div class="flex-1 flex flex-col md:flex-row gap-10 overflow-hidden" style="max-height: calc(90vh - 120px);">
                    <!-- Left: Cover (Sticky) -->
                    <div class="w-full md:w-2/5 p-10 pb-4 md:pr-0 flex flex-col gap-4 shrink-0 md:sticky md:top-0">
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">Project Cover <span class="text-slate-400 font-normal">(required)</span></span>
                        <div class="aspect-4/3 w-full border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 flex flex-col items-center justify-center rounded-xl relative overflow-hidden group">
                            <!-- Blurred cinematic backdrop (only in contain mode) -->
                            <img 
                                v-if="coverPreview && coverFit === 'contain' && !isCoverVideo"
                                :src="coverPreview" 
                                class="absolute inset-0 w-full h-full object-cover blur-xl opacity-40 scale-110 pointer-events-none select-none"
                                alt="Backdrop blur shadow"
                            />
                            
                            <video 
                                v-if="coverPreview && isCoverVideo" 
                                :src="coverPreview" 
                                class="relative z-10 w-full h-full object-cover"
                                :class="coverFit === 'contain' ? 'max-w-full max-h-full object-contain' : 'absolute inset-0 w-full h-full object-cover'"
                                autoplay 
                                loop 
                                muted 
                                playsinline
                            >
                                <track kind="captions" />
                            </video>
                            <img 
                                v-else-if="coverPreview" 
                                :src="coverPreview" 
                                class="relative z-10"
                                :class="coverFit === 'contain' ? 'max-w-full max-h-full object-contain' : 'absolute inset-0 w-full h-full object-cover'" 
                                alt="Project cover preview"
                            />
                            
                            <div v-if="!coverPreview" class="relative z-10 flex flex-col items-center text-center p-4">
                                <button @click="triggerFileInput('cover')" class="bg-slate-950 dark:bg-slate-50 text-white dark:text-slate-950 px-5 py-2.5 rounded-xl font-semibold text-xs hover:bg-slate-800 dark:hover:bg-slate-200 transition-colors mb-2 shadow-sm">
                                    Upload Image/Video
                                </button>
                                <p class="text-xs text-slate-550 max-w-[200px] bg-white/90 dark:bg-slate-900/90 px-2.5 py-1 rounded-lg">Maksimal ukuran video adalah 20MB & durasi 1 menit.</p>
                            </div>
                            
                            <!-- Ubah Cover Button overlay if uploaded -->
                            <button 
                                v-else 
                                type="button"
                                @click="triggerFileInput('cover')" 
                                class="absolute bottom-3 right-3 z-25 bg-slate-950/80 hover:bg-slate-950 dark:bg-white/80 dark:hover:bg-white text-white dark:text-slate-950 px-3.5 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider shadow-md transition-all cursor-pointer border-none"
                            >
                                Ubah Cover
                            </button>
                        </div>

                        <!-- Cover Fit Selector Switch -->
                        <div v-if="coverPreview" class="flex flex-col gap-2 mt-1 animate-fade-in">
                            <span class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">Tampilan Cover (Fit Mode)</span>
                            <div class="bg-slate-100 dark:bg-slate-950 p-1 rounded-xl flex items-center gap-1 border border-slate-200/40 dark:border-slate-800">
                                <button 
                                    type="button"
                                    @click="coverFit = 'cover'"
                                    class="flex-1 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer border-none bg-transparent"
                                    :class="coverFit === 'cover' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                                >
                                    Penuh / Potong (Crop)
                                </button>
                                <button 
                                    type="button"
                                    @click="coverFit = 'contain'"
                                    class="flex-1 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer border-none bg-transparent"
                                    :class="coverFit === 'contain' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                                >
                                    Pas / Utuh (Contain)
                                </button>
                            </div>
                        </div>
                    </div>
 
                    <!-- Right: Info (Scrollable) -->
                    <div class="w-full md:w-3/5 p-10 pt-4 md:pt-10 md:pl-0 overflow-y-auto flex flex-col gap-6" style="max-height: calc(90vh - 120px);">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-2">Project Information</h3>
                        
                        <div class="flex flex-col gap-1.5">
                            <label for="editor-pub-title" class="text-sm font-semibold text-slate-800 dark:text-slate-200">Title <span class="text-slate-400 font-normal">(required)</span></label>
                            <input id="editor-pub-title" v-model="form.title" type="text" placeholder="Give your project a title" class="w-full rounded-xl border border-slate-200 dark:border-slate-800 px-3 py-2 text-sm focus:border-slate-800 dark:focus:border-slate-200 focus:ring-1 focus:ring-slate-800 dark:focus:ring-slate-200 outline-none bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 placeholder-slate-400" />
                        </div>
 
                        <div class="flex flex-col gap-1.5">
                            <label for="editor-pub-description" class="text-sm font-semibold text-slate-800 dark:text-slate-200">Project Description <span class="text-slate-400 font-normal">(required)</span></label>
                            <textarea id="editor-pub-description" v-model="form.description" rows="3" placeholder="Briefly describe what this project is about..." class="w-full rounded-xl border border-slate-200 dark:border-slate-800 px-3 py-2 text-sm focus:border-slate-800 dark:focus:border-slate-200 focus:ring-1 focus:ring-slate-800 dark:focus:ring-slate-200 outline-none bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 placeholder-slate-400 resize-none font-medium"></textarea>
                        </div>
 
                        <div class="flex flex-col gap-1.5">
                            <label for="editor-pub-tags" class="text-sm font-semibold text-slate-800 dark:text-slate-200">Tags <span class="text-slate-400 font-normal">(required, limit of 10)</span></label>
                            <input id="editor-pub-tags" v-model="form.tags" type="text" placeholder="Add up to 10 keywords separated by comma (e.g. website, app, ui)" class="w-full rounded-xl border border-slate-200 dark:border-slate-800 px-3 py-2 text-sm focus:border-slate-800 dark:focus:border-slate-200 focus:ring-1 focus:ring-slate-800 dark:focus:ring-slate-200 outline-none bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 placeholder-slate-400" />
                        </div>
 
                        <!-- Category Chip Autocomplete Selector -->
                        <div class="flex flex-col gap-1.5 relative">
                            <label for="editor-pub-category" class="text-sm font-semibold text-slate-800 dark:text-slate-200">Category <span class="text-slate-400 font-normal">(required, limit of 3)</span></label>
                            <div class="w-full min-h-[44px] p-1.5 flex flex-wrap items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 shadow-xs">
                                <span v-for="(tag, idx) in categoryTags" :key="idx" class="h-7 px-3 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 inline-flex items-center gap-1.5 border border-slate-200/40 dark:border-slate-700/40 shadow-3xs">
									<img :src="`https://cdn.jsdelivr.net/gh/glincker/thesvg@main/public/icons/${getToolSlug(tag)}/default.svg`" 
										 class="w-3.5 h-3.5 object-contain" 
										 alt=""
										 @error="(e) => (e.target as HTMLImageElement).style.display = 'none'" />
                                    <span>{{ tag }}</span>
                                    <X class="w-3.5 h-3.5 text-slate-400 hover:text-slate-700 cursor-pointer shrink-0" @click="removeCategoryTag(idx)" />
                                </span>
                                <input id="editor-pub-category" v-model="categoryInput" type="text" :disabled="categoryTags.length >= 3" placeholder="Add category..." @focus="showCategoryDropdown = true" @blur="delayBlur(() => { showCategoryDropdown = false })" @keydown.enter.prevent="addCategoryTag(categoryInput)" class="flex-1 h-7 px-2 bg-transparent text-xs font-semibold focus:outline-none border-none min-w-[80px] dark:text-white" />
                            </div>
                            <div v-if="showCategoryDropdown && filteredCategorySuggestions.length > 0 && categoryTags.length < 3" class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl rounded-xl py-1 z-150 max-h-40 overflow-y-auto">
                                <button v-for="cat in filteredCategorySuggestions" :key="cat.name" type="button" @mousedown="addCategoryTag(cat.name)" class="w-full h-9 px-3 flex items-center gap-2.5 hover:bg-slate-50 dark:hover:bg-slate-800 text-left text-xs font-bold text-slate-700 dark:text-slate-200 border-none bg-transparent cursor-pointer">
									<img :src="`https://cdn.jsdelivr.net/gh/glincker/thesvg@main/public/icons/${cat.slug}/default.svg`" 
										 class="w-4 h-4 object-contain" 
										 alt=""
										 @error="(e) => (e.target as HTMLImageElement).style.display = 'none'" />
                                    <span>{{ cat.name }}</span>
                                </button>
                            </div>
                        </div>
 
                        <!-- Tools Chip Autocomplete Selector -->
                        <div class="flex flex-col gap-1.5 relative">
                            <label for="editor-pub-tools" class="text-sm font-semibold text-slate-800 dark:text-slate-200">Tools Used <span class="text-slate-400 font-normal">(required)</span></label>
                            <div class="w-full min-h-[44px] p-1.5 flex flex-wrap items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 shadow-xs">
                                <span v-for="(tag, idx) in toolsTags" :key="idx" class="h-7 px-3 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 inline-flex items-center gap-1.5 border border-slate-200/40 dark:border-slate-700/40 shadow-3xs">
									<img :src="`https://cdn.jsdelivr.net/gh/glincker/thesvg@main/public/icons/${getToolSlug(tag)}/default.svg`" 
										 class="w-3.5 h-3.5 object-contain" 
										 alt=""
										 @error="(e) => (e.target as HTMLImageElement).style.display = 'none'" />
                                    <span>{{ tag }}</span>
                                    <X class="w-3.5 h-3.5 text-slate-400 hover:text-slate-700 cursor-pointer shrink-0" @click="removeToolTag(idx)" />
                                </span>
                                <input id="editor-pub-tools" v-model="toolsInput" type="text" :disabled="toolsTags.length >= 10" placeholder="Add tools..." @focus="showToolsDropdown = true" @blur="delayBlur(() => { showToolsDropdown = false })" @keydown.enter.prevent="addToolTag(toolsInput)" class="flex-1 h-7 px-2 bg-transparent text-xs font-semibold focus:outline-none border-none min-w-[80px] dark:text-white" />
                            </div>
                            <div v-if="showToolsDropdown && filteredToolsSuggestions.length > 0 && toolsTags.length < 10" class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl rounded-xl py-1 z-150 max-h-40 overflow-y-auto">
                                <button v-for="tool in filteredToolsSuggestions" :key="tool.name" type="button" @mousedown="addToolTag(tool.name)" class="w-full h-9 px-3 flex items-center gap-2.5 hover:bg-slate-50 dark:hover:bg-slate-800 text-left text-xs font-bold text-slate-700 dark:text-slate-200 border-none bg-transparent cursor-pointer">
									<img :src="`https://cdn.jsdelivr.net/gh/glincker/thesvg@main/public/icons/${getToolSlug(tool.name)}/default.svg`" 
										 class="w-4 h-4 object-contain" 
										 alt=""
										 @error="(e) => (e.target as HTMLImageElement).style.display = 'none'" />
                                    <span>{{ tool.name }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Collaborators selector field -->
                        <div class="flex flex-col gap-1.5 relative">
                            <div class="flex justify-between">
                                <label for="editor-pub-collab" class="text-sm font-semibold text-slate-800 dark:text-slate-200">Collaborators <span class="text-slate-400 font-normal">(optional, limit of 3)</span></label>
                                <span class="text-xs text-slate-500 font-semibold">{{ form.collaborators.length }}/3</span>
                            </div>
                            <div class="w-full min-h-[44px] p-1.5 flex flex-wrap items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 shadow-xs">
                                <span v-for="(tag, idx) in form.collaborators" :key="idx" class="h-7 px-3 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 inline-flex items-center gap-1.5 border border-slate-200/40 dark:border-slate-700/40 shadow-3xs">
                                    <span>{{ tag }}</span>
                                    <X class="w-3.5 h-3.5 text-slate-400 hover:text-slate-700 cursor-pointer shrink-0" @click="removeCollaboratorChip(idx)" />
                                </span>
                                <input id="editor-pub-collab" v-model="collaboratorInput" type="text" :disabled="form.collaborators.length >= 3" placeholder="Search collaborator..." @input="handleCollaboratorSearch" @focus="showCollaboratorDropdown = true" @blur="delayBlur(() => { showCollaboratorDropdown = false })" class="flex-1 h-7 px-2 bg-transparent text-xs font-semibold focus:outline-none border-none min-w-[80px] dark:text-white" />
                            </div>
                            <div v-if="showCollaboratorDropdown && collaboratorSuggestions.length > 0 && form.collaborators.length < 3" class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl rounded-xl py-1 z-150 max-h-40 overflow-y-auto animate-fade-in">
                                <button v-for="u in collaboratorSuggestions" :key="u.id" type="button" @mousedown="addCollaboratorChip(u.name)" class="w-full h-11 px-3 flex items-center gap-3 hover:bg-slate-50 dark:hover:bg-slate-800 text-left text-xs font-bold text-slate-700 dark:text-slate-200 border-none bg-transparent cursor-pointer">
                                    <img :src="u.foto_path ? (u.foto_path.startsWith('http') ? u.foto_path : '/storage/' + u.foto_path) : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(u.name)" class="w-6 h-6 rounded-full object-cover" alt="Collaborator avatar" />
                                    <div class="flex flex-col">
                                        <span>{{ u.name }}</span>
                                        <span class="text-[10px] text-slate-400 font-normal">@{{ u.pagi_username || u.email }}</span>
                                    </div>
                                </button>
                            </div>
                        </div>
 
                        <div class="flex flex-col gap-1.5 w-1/2">
                            <label for="editor-pub-visibility" class="text-sm font-semibold text-slate-800 dark:text-slate-200">Visibility <span class="text-slate-400 font-normal">(required)</span></label>
                            <select id="editor-pub-visibility" v-model="form.visibility" class="w-full rounded-xl border border-slate-200 dark:border-slate-800 px-3 py-2 text-sm focus:border-slate-800 dark:focus:border-slate-200 focus:ring-1 focus:ring-slate-800 dark:focus:ring-slate-200 outline-none bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">
                                <option>Everyone</option>
                                <option>Private</option>
                            </select>
                            <p class="text-xs text-slate-550 mt-1">
                                {{ form.visibility === 'Private' ? 'Hanya dapat dilihat oleh Anda' : 'Dapat diakses dan ditemukan oleh semua orang' }}
                            </p>
                        </div>
                    </div></div>

                <!-- Modal Footer -->
                <div class="border-t border-slate-200 dark:border-slate-800 p-6 flex justify-end gap-3 bg-slate-50 dark:bg-slate-950">
                    <button :disabled="form.processing" @click="showPublishModal = false" class="px-5 py-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-900 rounded-xl transition-colors disabled:opacity-50">
                        Cancel
                    </button>
                    <button :disabled="form.processing" @click="saveAsDraft" class="px-5 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl transition-colors disabled:opacity-50">
                        <span v-if="form.processing && !form.is_published">Saving...</span>
                        <span v-else>Save as Draft</span>
                    </button>
                    <button :disabled="form.processing" @click="publishProject" class="px-5 py-2 text-sm font-semibold text-white bg-[#1769ff] hover:bg-[#1255cc] rounded-xl transition-colors shadow-sm disabled:opacity-50 flex items-center justify-center min-w-[90px]">
                        <span v-if="form.processing && form.is_published" class="inline-block w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2"></span>
                        <span v-if="form.processing && form.is_published">Publishing...</span>
                        <span v-else>Publish</span>
                    </button>
                </div>
            </div>
        </div>

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
        <!-- EDIT GRID MODAL -->
        <div v-if="editingGridIndex !== null" class="fixed inset-0 z-110 bg-white dark:bg-slate-900 flex flex-col">
            <!-- Modal Header -->
            <div class="h-16 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-8 bg-white dark:bg-slate-900 shadow-xs shrink-0">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Edit Grid</h2>
                <div class="flex items-center gap-3">
                    <!-- Sort / Custom Dropdown -->
                    <button type="button" class="flex items-center gap-1.5 border border-slate-200 dark:border-slate-800 px-4 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 bg-white dark:bg-slate-950 transition-colors shadow-3xs cursor-pointer">
                        <ArrowUpDown class="w-3.5 h-3.5 text-slate-500" />
                        <span>Custom</span>
                        <ChevronDown class="w-3.5 h-3.5 text-slate-400" />
                    </button>
                    <!-- Add Photos Dropdown -->
                    <button type="button" @click="triggerFileInputForBlock(editingGridIndex)" class="flex items-center gap-1.5 border border-slate-200 dark:border-slate-800 px-4 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 bg-white dark:bg-slate-950 transition-colors shadow-3xs cursor-pointer">
                        <PlusCircle class="w-3.5 h-3.5 text-blue-500" />
                        <span>Add Photos</span>
                        <ChevronDown class="w-3.5 h-3.5 text-slate-400" />
                    </button>
                    
                    <div class="h-4 w-px bg-slate-200 dark:bg-slate-800 mx-1"></div>
 
                    <!-- Cancel / Done -->
                    <button type="button" @click="editingGridIndex = null" class="px-5 py-2 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-700 dark:text-slate-355 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-full transition-colors cursor-pointer shadow-3xs">Cancel</button>
                    <button type="button" @click="editingGridIndex = null" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-full text-xs font-bold transition-colors shadow-xs cursor-pointer">Done</button>
                </div>
            </div>
            <!-- Modal Body (Justified photo grid layout preview with deletes) -->
            <div class="flex-1 overflow-y-auto p-10 bg-slate-50 dark:bg-slate-950 flex flex-col items-center">
                <div class="w-full max-w-7xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-t-3xl min-h-screen shadow-2xl p-8 flex flex-col overflow-hidden">
                    <div v-if="form.content[editingGridIndex].previews && form.content[editingGridIndex].previews.length > 0" 
                         class="w-full flex flex-col" :style="{ gap: '8px' }">
                        <div v-for="(row, rIdx) in getJustifiedLayout(form.content[editingGridIndex].previews, 1200, 380, 8)" :key="rIdx" 
                             class="w-full flex justify-start animate-fade-in" 
                             :style="{ gap: '8px' }">
                            <div v-for="(p, i) in row.items" :key="p" 
                                 class="relative overflow-hidden group/item transition-transform duration-300 hover:scale-[1.01]"
                                 :style="{
                                     width: (row.height * getAspectRatio(p)) + 'px',
                                     flexGrow: row.isLast ? 0 : getAspectRatio(p),
                                     flexShrink: row.isLast ? 0 : 1,
                                     flexBasis: 'auto',
                                     height: row.height + 'px'
                                 }">
                                <img :src="p" 
                                     class="w-full h-full object-cover border-none shadow-none" 
                                     loading="lazy" alt="Grid item" />
                                     
                                <!-- Absolute X close button on top right -->
                                <button type="button" @click.stop="const idx = form.content[editingGridIndex].previews.indexOf(p); if (idx !== -1) { form.content[editingGridIndex].previews.splice(idx, 1); form.content[editingGridIndex].files.splice(idx, 1); }" 
                                        class="absolute top-3 right-3 w-8 h-8 rounded-full bg-slate-950/50 hover:bg-slate-950/80 text-white flex items-center justify-center backdrop-blur-xs transition-all shadow-md cursor-pointer border-none z-10">
                                    <X class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="w-full py-20 flex flex-col items-center justify-center text-slate-400 dark:text-slate-650">
                        <!-- Empty state -->
                        <LayoutGrid class="w-12 h-12 mb-2 opacity-50" />
                        <p class="font-semibold text-sm">No photos in this grid.</p>
                    </div>
                </div>
            </div>
        </div>

    </template>

    <!-- TOAST ALERTS CONTAINER -->
        <div class="fixed top-6 right-6 z-150 flex flex-col gap-3.5 max-w-xs pointer-events-none">
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
                    class="p-4 rounded-xl border flex items-start gap-3 shadow-xl backdrop-blur-md pointer-events-auto select-none"
                    :class="[
                        toast.type === 'success' 
                            ? 'bg-slate-900/90 border-slate-800 text-white dark:bg-white/95 dark:border-slate-200 dark:text-slate-900' 
                            : 'bg-red-650 border-red-750 text-white dark:bg-red-600 dark:border-red-750'
                    ]"
                >
                    <div class="flex-1 text-xs font-bold leading-relaxed pr-2">
                        {{ toast.message }}
                    </div>
                    <button @click="toasts = toasts.filter(t => t.id !== toast.id)" class="text-white hover:opacity-80 shrink-0 bg-transparent border-none cursor-pointer">
                        <X class="w-3.5 h-3.5" />
                    </button>
                </div>
            </TransitionGroup>
        </div>

        <!-- GLOBAL UPLOAD PROGRESS OVERLAY -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="form.processing && form.progress" class="fixed inset-0 z-10000 bg-slate-950/85 backdrop-blur-md flex flex-col items-center justify-center p-8 select-none">
                <div class="w-full max-w-xs space-y-4">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-550 text-center">Mengunggah Karya</h3>
                    <Progress :value="form.progress.percentage" className="w-full h-2 bg-slate-800" indicatorClassName="bg-indigo-500" />
                    <div class="flex justify-between items-center text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        <span class="animate-pulse">{{ getUploadStatusMessage(form.progress.percentage ?? 0) }}</span>
                        <span>{{ form.progress.percentage }}%</span>
                    </div>
                </div>
            </div>
        </Transition>
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
