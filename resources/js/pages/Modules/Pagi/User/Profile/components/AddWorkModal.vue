<script setup lang="ts">
import axios from "axios";
import {
	Camera,
	ChevronDown,
	ChevronUp,
	ExternalLink,
	Info,
	Loader2,
	UploadCloud,
	X,
} from "lucide-vue-next";
import { computed, onMounted, ref, watch } from "vue";
import { usePagiProgress } from "../../../shared/composables/usePagiProgress";
import Modal from "../../ui/Modal.vue";

const { trackUpload } = usePagiProgress();

const props = defineProps<{
	show: boolean;
	isEditingQuickWork: boolean;
	editingQuickWorkId: number | null;
	editingProject: any | null;
	user: any;
}>();

const emit = defineEmits(["close", "success", "warning", "toast"]);

// Form State
const addWorkForm = ref({
	title: "",
	cover_image: null as File | null,
	skills: [] as string[],
	tools: [] as string[],
	completed_work_link: "",
	collaborators: [] as string[],
	client: "",
	start_date: "",
	end_date: "",
	industry: "",
	original_work_confirmed: true,
});

// UI states
const isSubmittingWork = ref(false);
const showMoreDetails = ref(false);
const coverFit = ref<"cover" | "contain">("cover");
const coverFile = ref<File | null>(null);
const coverPreviewUrl = ref<string | null>(null);
const zoomLevel = ref(1.0);
const cropSaved = ref(false);
const imageQualityWarning = ref(false);

const addWorkSkillsInput = ref("");
const addWorkSkillsTags = ref<string[]>([]);
const addWorkToolsInput = ref("");
const addWorkToolsTags = ref<string[]>([]);
const addWorkCollaboratorsInput = ref("");
const addWorkCollaboratorsTags = ref<string[]>([]);

const collaboratorsSuggestions = ref<any[]>([]);
const isLoadingCollaborators = ref(false);
const showCollaboratorsDropdown = ref(false);

// Skill & Tool presets
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

const toolsSuggestions = [
	{ name: "Figma", category: "design" },
	{ name: "Figma Make", category: "design" },
	{ name: "FigJam", category: "design" },
	{ name: "VS Code", category: "dev" },
	{ name: "Python", category: "math" },
	{ name: "GitHub", category: "dev" },
	{ name: "PostgreSQL", category: "database" },
	{ name: "MySQL", category: "database" },
	{ name: "MATLAB", category: "math" },
	{ name: "R Language", category: "math" },
	{ name: "SPSS", category: "math" },
	{ name: "Android Studio", category: "dev" },
	{ name: "Docker", category: "dev" },
	{ name: "Laravel", category: "dev" },
	{ name: "Notion", category: "comms" },
	{ name: "Tableau", category: "math" },
	{ name: "Excel", category: "math" },
	{ name: "Jupyter Notebook", category: "math" },
	{ name: "LaTeX", category: "math" },
];

const getToolSlug = (name: string) => {
	return name
		.toLowerCase()
		.replace(/[^a-z0-9]+/g, "-")
		.replace(/(^-|-$)/g, "");
};

const isVideoFile = (file: File | null) => {
	if (!file) return false;
	return file.type.startsWith("video/");
};

const isVideoUrl = (url: string | null): boolean => {
	if (!url) return false;
	const cleanUrl = url.split("?")[0].split("#")[0];
	const ext = cleanUrl.split(".").pop()?.toLowerCase();
	return (
		["mp4", "webm", "mov", "avi", "mkv", "3gp"].includes(ext || "") ||
		url.startsWith("data:video/")
	);
};

// Autocomplete computations
const filteredSkillsSuggestions = computed(() => {
	if (!addWorkSkillsInput.value.trim()) return [];
	const q = addWorkSkillsInput.value.toLowerCase().trim();
	return FMIKOM_SKILL_SUGGESTIONS.filter(
		(s) => s.toLowerCase().includes(q) && !addWorkSkillsTags.value.includes(s),
	);
});

const filteredToolsSuggestions = computed(() => {
	if (!addWorkToolsInput.value.trim()) return [];
	const q = addWorkToolsInput.value.toLowerCase().trim();
	return toolsSuggestions.filter(
		(t) =>
			t.name.toLowerCase().includes(q) &&
			!addWorkToolsTags.value.includes(t.name),
	);
});

// Autocomplete Handlers
const addSkillTag = () => {
	const val = addWorkSkillsInput.value.trim().replace(/,/g, "");
	if (
		val &&
		addWorkSkillsTags.value.length < 3 &&
		!addWorkSkillsTags.value.includes(val)
	) {
		addWorkSkillsTags.value.push(val);
	}
	addWorkSkillsInput.value = "";
};

const addSkillSuggestion = (skill: string) => {
	if (
		addWorkSkillsTags.value.length < 3 &&
		!addWorkSkillsTags.value.includes(skill)
	) {
		addWorkSkillsTags.value.push(skill);
	}
	addWorkSkillsInput.value = "";
};

const removeSkillTag = (idx: number) => {
	addWorkSkillsTags.value.splice(idx, 1);
};

const addToolTag = (toolName: string) => {
	const val = toolName.trim();
	if (
		val &&
		addWorkToolsTags.value.length < 3 &&
		!addWorkToolsTags.value.includes(val)
	) {
		addWorkToolsTags.value.push(val);
	}
	addWorkToolsInput.value = "";
};

const removeToolTag = (idx: number) => {
	addWorkToolsTags.value.splice(idx, 1);
};

let searchTimeout: any = null;
const handleCollaboratorSearch = () => {
	if (searchTimeout) clearTimeout(searchTimeout);
	const q = addWorkCollaboratorsInput.value.trim();
	if (q.length < 1) {
		collaboratorsSuggestions.value = [];
		showCollaboratorsDropdown.value = false;
		return;
	}
	showCollaboratorsDropdown.value = true;
	isLoadingCollaborators.value = true;
	searchTimeout = setTimeout(async () => {
		try {
			const res = await axios.get(
				`/pagi/users/search?q=${encodeURIComponent(q)}`,
			);
			collaboratorsSuggestions.value = res.data || [];
		} catch (e) {
			console.error(e);
		} finally {
			isLoadingCollaborators.value = false;
		}
	}, 300);
};

const addCollaboratorSuggestion = (username: string) => {
	if (
		addWorkCollaboratorsTags.value.length < 3 &&
		!addWorkCollaboratorsTags.value.includes(username)
	) {
		addWorkCollaboratorsTags.value.push(username);
	}
	addWorkCollaboratorsInput.value = "";
	collaboratorsSuggestions.value = [];
	showCollaboratorsDropdown.value = false;
};

const addCollaboratorTag = () => {
	const val = addWorkCollaboratorsInput.value.trim();
	if (
		val &&
		addWorkCollaboratorsTags.value.length < 3 &&
		!addWorkCollaboratorsTags.value.includes(val)
	) {
		addWorkCollaboratorsTags.value.push(val);
	}
	addWorkCollaboratorsInput.value = "";
	collaboratorsSuggestions.value = [];
	showCollaboratorsDropdown.value = false;
};

const removeCollaboratorTag = (idx: number) => {
	addWorkCollaboratorsTags.value.splice(idx, 1);
};

// Cover Image Selection & Verification
const onCoverFileSelected = (e: Event) => {
	const input = e.target as HTMLInputElement;
	if (input.files?.[0]) {
		const file = input.files[0];

		const allowedImageTypes = [
			"image/png",
			"image/jpeg",
			"image/jpg",
			"image/gif",
			"image/webp",
		];
		const allowedVideoTypes = [
			"video/mp4",
			"video/webm",
			"video/ogg",
			"video/quicktime",
		];
		const fileType = file.type.toLowerCase();
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
			emit(
				"warning",
				"Format File Dilarang",
				"Format file ini tidak didukung demi alasan keamanan.",
			);
			input.value = "";
			return;
		}

		if (
			!allowedImageTypes.includes(fileType) &&
			!allowedVideoTypes.includes(fileType)
		) {
			emit(
				"warning",
				"Format File Tidak Valid",
				"Hanya berkas gambar (PNG, JPG, WEBP, GIF) atau video (MP4, WEBM) yang diperbolehkan.",
			);
			input.value = "";
			return;
		}

		if (fileType.startsWith("video/")) {
			const video = document.createElement("video");
			video.preload = "metadata";
			video.onloadedmetadata = () => {
				window.URL.revokeObjectURL(video.src);
				if (video.duration > 60.5) {
					emit(
						"warning",
						"Durasi Video Terlalu Lama",
						"Durasi video maksimal adalah 1 menit (60 detik) demi menjaga performa server.",
					);
					input.value = "";
					coverFile.value = null;
					coverPreviewUrl.value = null;
					cropSaved.value = false;
				} else {
					coverFile.value = file;
					zoomLevel.value = 1.0;
					coverPreviewUrl.value = URL.createObjectURL(file);
					cropSaved.value = true;
					imageQualityWarning.value = false;
				}
			};
			video.src = URL.createObjectURL(file);
		} else {
			coverFile.value = file;
			zoomLevel.value = 1.0;
			if (coverPreviewUrl.value?.startsWith("blob:")) {
				URL.revokeObjectURL(coverPreviewUrl.value);
			}
			coverPreviewUrl.value = URL.createObjectURL(file);
			cropSaved.value = false;

			const img = new Image();
			img.onload = () => {
				if (img.naturalWidth < 1600 || img.naturalHeight < 1200) {
					imageQualityWarning.value = true;
				} else {
					imageQualityWarning.value = false;
				}
			};
			img.src = coverPreviewUrl.value;
		}
	}
};

const saveCrop = () => {
	cropSaved.value = true;
};

const cancelCrop = () => {
	if (coverPreviewUrl.value?.startsWith("blob:")) {
		URL.revokeObjectURL(coverPreviewUrl.value);
	}
	coverFile.value = null;
	coverPreviewUrl.value = null;
	cropSaved.value = false;
	zoomLevel.value = 1.0;
	imageQualityWarning.value = false;
};

const quickStoreSubmit = async () => {
	if (!coverFile.value && !props.isEditingQuickWork) {
		emit(
			"warning",
			"Cover Diperlukan",
			"Silakan unggah foto cover atau video terlebih dahulu.",
		);
		return;
	}
	if (!cropSaved.value) {
		emit(
			"warning",
			"Penyesuaian Belum Disimpan",
			"Silakan klik tombol 'Save & Apply' untuk menyimpan penyesuaian cover.",
		);
		return;
	}
	if (!addWorkForm.value.title.trim()) {
		emit("warning", "Judul Karya Diperlukan", "Judul karya wajib diisi.");
		return;
	}
	if (addWorkSkillsTags.value.length < 1) {
		emit(
			"warning",
			"Keahlian (Skills) Diperlukan",
			"Silakan tambahkan minimal 1 keahlian (skills) yang digunakan.",
		);
		return;
	}
	if (addWorkToolsTags.value.length < 1) {
		emit(
			"warning",
			"Tools Diperlukan",
			"Silakan tambahkan minimal 1 tools yang digunakan.",
		);
		return;
	}

	isSubmittingWork.value = true;
	try {
		const formData = new FormData();
		formData.append("title", addWorkForm.value.title);
		if (coverFile.value) {
			formData.append("cover_image", coverFile.value);
		}
		formData.append("skills", JSON.stringify(addWorkSkillsTags.value));
		formData.append("tools", JSON.stringify(addWorkToolsTags.value));
		formData.append(
			"completed_work_link",
			addWorkForm.value.completed_work_link,
		);
		formData.append(
			"collaborators",
			JSON.stringify(addWorkCollaboratorsTags.value),
		);
		formData.append("client", addWorkForm.value.client);
		formData.append("start_date", addWorkForm.value.start_date);
		formData.append("end_date", addWorkForm.value.end_date);
		formData.append("industry", addWorkForm.value.industry);
		formData.append(
			"original_work_confirmed",
			addWorkForm.value.original_work_confirmed ? "true" : "false",
		);
		formData.append("cover_fit", coverFit.value);

		let res: any;
		if (props.isEditingQuickWork && props.editingQuickWorkId) {
			res = await trackUpload(
				(config) =>
					axios.post(
						`/pagi/editor/${props.editingQuickWorkId}/quick-update`,
						formData,
						{
							...config,
							headers: {
								...config.headers,
								"Content-Type": "multipart/form-data",
							},
						},
					),
				"Mengunggah Karya",
			);
		} else {
			res = await trackUpload(
				(config) =>
					axios.post("/pagi/editor/quick-store", formData, {
						...config,
						headers: {
							...config.headers,
							"Content-Type": "multipart/form-data",
						},
					}),
				"Mengunggah Karya",
			);
		}

		if (res.data.success) {
			emit("success", res.data.project);
		}
	} catch (e: any) {
		console.error(e);
		emit(
			"toast",
			e.response?.data?.message || "Gagal menyimpan karya. Coba lagi.",
			"error",
		);
	} finally {
		isSubmittingWork.value = false;
	}
};

const initializeWith = (project: any) => {
	const details =
		project.content?.find((b: any) => b && b.type === "featured_details") || {};
	addWorkForm.value = {
		title: project.title || "",
		cover_image: null,
		skills: details.skills || [],
		tools: details.tools || [],
		completed_work_link: details.completed_work_link || "",
		collaborators: details.collaborators || [],
		client: details.client || "",
		start_date: details.start_date || "",
		end_date: details.end_date || "",
		industry: details.industry || "",
		original_work_confirmed: details.original_work_confirmed !== false,
	};

	addWorkSkillsInput.value = "";
	addWorkSkillsTags.value = [...(details.skills || [])];
	addWorkToolsInput.value = "";
	addWorkToolsTags.value = [...(details.tools || [])];
	addWorkCollaboratorsInput.value = "";
	addWorkCollaboratorsTags.value = (details.collaborators || [])
		.map((c: any) =>
			typeof c === "object" && c !== null ? c.name || "" : String(c),
		)
		.filter(Boolean);

	if (coverPreviewUrl.value?.startsWith("blob:")) {
		URL.revokeObjectURL(coverPreviewUrl.value);
	}
	coverFile.value = null;
	coverPreviewUrl.value = project.image || null;
	zoomLevel.value = 1.0;
	cropSaved.value = true;
	coverFit.value = details.cover_fit || "cover";
	imageQualityWarning.value = false;
	showMoreDetails.value = !!(
		details.client ||
		details.start_date ||
		details.collaborators?.length
	);
};

const resetForm = () => {
	addWorkForm.value = {
		title: "",
		cover_image: null,
		skills: [],
		tools: [],
		completed_work_link: "",
		collaborators: [],
		client: "",
		start_date: "",
		end_date: "",
		industry: "",
		original_work_confirmed: true,
	};
	addWorkSkillsInput.value = "";
	addWorkSkillsTags.value = [];
	addWorkToolsInput.value = "";
	addWorkToolsTags.value = [];
	addWorkCollaboratorsInput.value = "";
	addWorkCollaboratorsTags.value = [];
	if (coverPreviewUrl.value?.startsWith("blob:")) {
		URL.revokeObjectURL(coverPreviewUrl.value);
	}
	coverFile.value = null;
	coverPreviewUrl.value = null;
	zoomLevel.value = 1.0;
	cropSaved.value = false;
	coverFit.value = "cover";
	imageQualityWarning.value = false;
};

watch(
	() => props.show,
	(newVal) => {
		if (newVal) {
			if (props.isEditingQuickWork && props.editingProject) {
				initializeWith(props.editingProject);
			} else {
				resetForm();
			}
		}
	},
);
</script>

<template>
	<Modal :show="show" :title="isEditingQuickWork ? 'Edit featured work on profile' : 'Feature work on profile'" maxWidth="3xl" @close="emit('close')">
		<div class="relative grid grid-cols-1 lg:grid-cols-12 gap-8 my-2 text-left">
			<!-- Left Column: Cover photo (col-span-6) - Stationary -->
			<div class="lg:col-span-6 flex flex-col gap-4 lg:sticky lg:top-0 self-start">
				<h3 class="text-xs font-black text-slate-404 dark:text-slate-505 uppercase tracking-wider flex items-center">
					Cover photo
					<span class="text-rose-500 ml-1 text-xs font-bold leading-none select-none">*</span>
				</h3>

				<!-- Uploader Card Container -->
				<div 
					class="relative w-full aspect-[16/10] rounded-2xl border border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30 overflow-hidden select-none"
					:class="!coverPreviewUrl ? 'flex flex-col items-center justify-center p-6 text-center border-dashed' : 'border-solid'"
				>
					<!-- Case A: No Media selected -->
					<div v-if="!coverPreviewUrl" class="flex flex-col items-center gap-3">
						<div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 shadow-3xs mb-2">
							<UploadCloud class="w-6 h-6" />
						</div>
						<label class="px-5 py-2.5 rounded-full bg-white hover:bg-slate-50 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-755 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-150 transition-colors shadow-2xs cursor-pointer">
							Upload
							<input type="file" accept="image/*,video/*" class="hidden" @change="onCoverFileSelected" />
						</label>
						<p class="text-[10px] font-semibold text-slate-404 dark:text-slate-505 leading-relaxed max-w-[220px]">
							Media at least 1600 x 1200 (4:3 aspect ratio) in PNG, JPG, GIF, or MP4/WebM formats work best
						</p>
					</div>

					<!-- Case B & C: Media selected -->
					<div v-else class="absolute inset-0 w-full h-full bg-slate-950">
						<!-- Preview Area -->
						<div class="w-full h-full overflow-hidden flex items-center justify-center relative">
							<!-- Blurred cinematic backdrop (only in contain mode) -->
							<template v-if="coverFit === 'contain'">
								<video 
									v-if="isVideoFile(coverFile) || isVideoUrl(coverPreviewUrl)"
									:src="coverPreviewUrl" 
									muted 
									autoplay 
									loop 
									playsinline
									class="absolute inset-0 w-full h-full object-cover blur-xl opacity-40 scale-110 pointer-events-none"
								></video>
								<img 
									v-else
									:src="coverPreviewUrl" 
									alt="Latar Belakang Cover"
									class="absolute inset-0 w-full h-full object-cover blur-xl opacity-40 scale-110 pointer-events-none select-none"
								/>
							</template>

							<video 
								v-if="isVideoFile(coverFile) || isVideoUrl(coverPreviewUrl)"
								:src="coverPreviewUrl" 
								muted 
								autoplay 
								loop 
								playsinline
								class="relative z-10"
								:class="coverFit === 'contain' ? 'max-w-full max-h-full object-contain' : 'w-full h-full object-cover'"
							></video>
							<img 
								v-else
								:src="coverPreviewUrl" 
								alt="Pratinjau Cover"
								class="relative z-10"
								:class="coverFit === 'contain' ? 'max-w-full max-h-full object-contain' : 'w-full h-full object-cover'"
								:style="{ transform: coverFit === 'contain' ? 'none' : 'scale(' + zoomLevel + ')' }"
							/>
						</div>
						
						<!-- Absolute overlay for quick update (Only visible if crop is saved) -->
						<label v-if="cropSaved" class="absolute bottom-3 right-3 z-30 px-4.5 py-2 rounded-full bg-slate-900 dark:bg-white hover:bg-slate-800 dark:hover:bg-slate-100 text-[10px] font-black text-white dark:text-slate-950 uppercase tracking-wider transition-colors shadow-lg cursor-pointer border border-slate-800 dark:border-white">
							Ubah Media
							<input type="file" accept="image/*,video/*" class="hidden" @change="onCoverFileSelected" />
						</label>
					</div>
				</div>

				<!-- Cover Fit Mode Option Toggle -->
				<div v-if="coverPreviewUrl" class="flex flex-col gap-2 mt-2">
					<label class="text-[10px] font-black text-slate-404 dark:text-slate-550 uppercase tracking-wider">Tampilan Cover (Fit Mode)</label>
					<div class="bg-slate-100 dark:bg-slate-950 p-1 rounded-xl flex items-center gap-1 border border-slate-200/40 dark:border-slate-800">
						<button 
							type="button"
							@click="coverFit = 'cover'; if (!isVideoFile(coverFile)) cropSaved = false;"
							class="flex-1 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer border-none bg-transparent"
							:class="coverFit === 'cover' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
						>
							Penuh / Potong (Crop)
						</button>
						<button 
							type="button"
							@click="coverFit = 'contain'; cropSaved = true;"
							class="flex-1 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer border-none bg-transparent"
							:class="coverFit === 'contain' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
						>
							Pas / Utuh (Contain)
						</button>
					</div>
				</div>

				<!-- Crop adjustments panel -->
				<div v-if="coverPreviewUrl && !cropSaved" class="bg-slate-55 dark:bg-slate-900/40 p-4 rounded-2xl flex flex-col gap-3.5 border border-slate-200/60 dark:border-slate-800/80 shadow-3xs mt-2">
					<!-- Slider zoom -->
					<div v-show="coverFit !== 'contain'" class="flex items-center gap-3">
						<Camera class="w-4 h-4 text-slate-400 shrink-0" />
						<input 
							v-model="zoomLevel" 
							type="range" 
							min="1.0" 
							max="2.5" 
							step="0.05" 
							class="flex-1 h-1 bg-slate-200 dark:bg-slate-800 rounded-lg appearance-none cursor-pointer accent-slate-900 dark:accent-white" 
						/>
						<Camera class="w-5.5 h-5.5 text-slate-400 shrink-0" />
					</div>
					<div v-show="coverFit === 'contain'" class="text-center py-1 text-[10px] font-bold text-slate-400 dark:text-slate-505 uppercase tracking-wider">
						Dalam mode Pas (Contain), seluruh gambar dimuat tanpa dipotong
					</div>
					<!-- Action confirm crop buttons -->
					<div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-800/60 pt-3">
						<button type="button" @click="cancelCrop" class="text-[10px] font-black text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 uppercase tracking-wider cursor-pointer bg-transparent border-none">Cancel</button>
						<button type="button" @click="saveCrop" class="px-4 py-1.5 rounded-full bg-[#18181b] hover:bg-zinc-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-zinc-900 font-bold text-xs shadow-sm transition-colors cursor-pointer border-none">Save & Apply</button>
					</div>
				</div>

				<!-- Image quality warning card -->
				<div v-if="coverPreviewUrl && imageQualityWarning" class="flex gap-3 p-3.5 rounded-2xl border border-amber-250/60 dark:border-amber-900/60 bg-amber-50/50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-300 leading-normal text-[11px] font-semibold">
					<Info class="w-4.5 h-4.5 shrink-0 text-amber-600 dark:text-amber-400" />
					<p>
						The selected image is low quality. Images at least 1600 x 1200 (4:3 aspect ratio) in PNG, JPG, or GIF formats work best.
					</p>
				</div>
			</div>

			<!-- Right Column: Fields (col-span-6) - Scrollable -->
			<div class="lg:col-span-6 flex flex-col gap-5 lg:overflow-y-auto lg:max-h-[68vh] px-2 -mx-2 scrollbar-thin">
				<!-- Project title input -->
				<div class="flex flex-col gap-1.5">
					<label class="text-[10px] font-black text-slate-404 dark:text-slate-505 uppercase tracking-wider flex items-center">
						Project Title
						<span class="text-rose-500 ml-1 text-xs font-bold leading-none select-none">*</span>
					</label>
					<input 
						v-model="addWorkForm.title" 
						type="text" 
						placeholder="e.g. CGI Product Visualization or UI/UX Design"
						class="w-full h-11 px-4 rounded-xl border border-slate-205 dark:border-slate-800 bg-transparent text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 transition-all shadow-2xs"
					/>
				</div>

				<!-- Original work confirm -->
				<label class="flex items-center gap-2.5 select-none cursor-pointer">
					<input 
						v-model="addWorkForm.original_work_confirmed" 
						type="checkbox" 
						class="w-4.5 h-4.5 rounded-sm border-slate-300 text-indigo-600 focus:ring-indigo-500" 
					/>
					<span class="text-xs font-bold text-slate-700 dark:text-slate-200 flex items-center gap-1">
						I confirm this is my original work
						<Info class="w-3.5 h-3.5 text-slate-400 cursor-help" title="Tandai jika karya ini benar-benar buatan Anda sendiri." />
					</span>
				</label>

				<!-- Skills tag list input -->
				<div class="flex flex-col gap-1.5 relative">
					<div class="flex justify-between items-center">
						<label class="text-[10px] font-black text-slate-404 dark:text-slate-505 uppercase tracking-wider flex items-center">
							Skills
							<span class="text-rose-500 ml-1 text-xs font-bold leading-none select-none">*</span>
						</label>
						<span class="text-[10px] font-bold text-slate-404 dark:text-slate-505">{{ addWorkSkillsTags.length }}/3</span>
					</div>
					<div class="w-full min-h-11 p-1.5 flex flex-wrap items-center gap-2 rounded-xl border border-slate-205 dark:border-slate-800 bg-transparent shadow-2xs focus-within:ring-1 focus-within:ring-slate-800">
						<!-- Tags list -->
						<span v-for="(tag, idx) in addWorkSkillsTags" :key="idx" class="h-7 px-3 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 inline-flex items-center gap-1.5 border border-slate-200/40 dark:border-slate-700/40 shadow-3xs">
							{{ tag }}
							<X class="w-3.5 h-3.5 text-slate-400 hover:text-slate-700 dark:hover:text-white cursor-pointer shrink-0" @click="removeSkillTag(idx)" />
						</span>
						<!-- Input element -->
						<input 
							v-model="addWorkSkillsInput" 
							type="text" 
							:disabled="addWorkSkillsTags.length >= 3"
							placeholder="Add skills used (Wajib diisi, minimal 1)"
							@keydown.enter.prevent="addSkillTag"
							@keydown.comma.prevent="addSkillTag"
							class="flex-1 h-8 px-2 bg-transparent text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-455 focus:outline-hidden disabled:opacity-50 border-none min-w-[100px]"
						/>
					</div>
					<!-- Skills Autocomplete Suggestions Dropdown -->
					<div v-if="filteredSkillsSuggestions.length > 0 && addWorkSkillsTags.length < 3" class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl rounded-2xl py-1.5 z-40 overflow-hidden max-h-[160px] overflow-y-auto">
						<button 
							type="button" 
							@click="addSkillSuggestion(skill)"
							v-for="skill in filteredSkillsSuggestions" 
							:key="skill"
							class="w-full h-10 px-4 flex items-center hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-left text-xs font-bold text-slate-700 dark:text-slate-200 cursor-pointer border-none bg-transparent"
						>
							<span>{{ skill }}</span>
						</button>
					</div>
				</div>

				<!-- Tools tag list input -->
				<div class="flex flex-col gap-1.5 relative">
					<div class="flex justify-between items-center">
						<label class="text-[10px] font-black text-slate-404 dark:text-slate-505 uppercase tracking-wider flex items-center">
							Tools
							<span class="text-rose-500 ml-1 text-xs font-bold leading-none select-none">*</span>
						</label>
						<span class="text-[10px] font-bold text-slate-404 dark:text-slate-505">{{ addWorkToolsTags.length }}/3</span>
					</div>
					<div class="w-full min-h-11 p-1.5 flex flex-wrap items-center gap-2 rounded-xl border border-slate-205 dark:border-slate-800 bg-transparent shadow-2xs focus-within:ring-1 focus-within:ring-slate-800">
						<!-- Tags list -->
						<span v-for="(tag, idx) in addWorkToolsTags" :key="idx" class="h-7 px-3 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 inline-flex items-center gap-1.5 border border-slate-200/40 dark:border-slate-700/40 shadow-3xs">
							<img :src="`https://cdn.jsdelivr.net/gh/glincker/thesvg@main/public/icons/${getToolSlug(tag)}/default.svg`" 
								 class="w-3.5 h-3.5 object-contain" 
								 alt=""
								 @error="(e) => (e.target as HTMLImageElement).style.display = 'none'" />
							<span>{{ tag }}</span>
							<X class="w-3.5 h-3.5 text-slate-400 hover:text-slate-700 dark:hover:text-white cursor-pointer shrink-0" @click="removeToolTag(idx)" />
						</span>
						<!-- Input element -->
						<input 
							v-model="addWorkToolsInput" 
							type="text" 
							:disabled="addWorkToolsTags.length >= 3"
							placeholder="Add tools used (Wajib diisi, minimal 1)"
							@keydown.enter.prevent="addToolTag(addWorkToolsInput)"
							class="flex-1 h-8 px-2 bg-transparent text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden disabled:opacity-50 border-none min-w-[100px]"
						/>
					</div>

					<!-- Autocomplete Suggestions Dropdown -->
					<div v-if="filteredToolsSuggestions.length > 0 && addWorkToolsTags.length < 3" class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl rounded-2xl py-1.5 z-40 overflow-hidden max-h-[160px] overflow-y-auto">
						<button 
							type="button" 
							@click="addToolTag(tool.name)"
							v-for="tool in filteredToolsSuggestions" 
							:key="tool.name"
							class="w-full h-10 px-4 flex items-center gap-3 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-left text-xs font-bold text-slate-700 dark:text-slate-200 cursor-pointer border-none bg-transparent"
						>
							<img :src="`https://cdn.jsdelivr.net/gh/glincker/thesvg@main/public/icons/${getToolSlug(tool.name)}/default.svg`" 
								 class="w-4.5 h-4.5 object-contain" 
								 alt=""
								 @error="(e) => (e.target as HTMLImageElement).style.display = 'none'" />
							<span>{{ tool.name }}</span>
						</button>
					</div>
				</div>

				<!-- Completed work link input -->
				<div class="flex flex-col gap-1.5">
					<label class="text-[10px] font-black text-slate-404 dark:text-slate-505 uppercase tracking-wider">Completed work link</label>
					<div class="relative">
						<input 
							v-model="addWorkForm.completed_work_link" 
							type="url" 
							placeholder="Enter a link to the live project or deliverable"
							class="w-full h-11 pl-10 pr-4 rounded-xl border border-slate-205 dark:border-slate-800 bg-transparent text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 transition-all shadow-2xs"
						/>
						<ExternalLink class="absolute left-3.5 top-3.5 w-4 h-4 text-slate-400" />
					</div>
					<span class="text-[10px] font-bold text-slate-404 dark:text-slate-550">Optional</span>
				</div>

				<!-- Collapsible Row for more details -->
				<div class="border-t border-slate-200/60 dark:border-slate-800/80 pt-4 flex flex-col">
					<button 
						type="button"
						@click="showMoreDetails = !showMoreDetails"
						class="flex justify-between items-center text-xs font-bold text-slate-700 dark:text-slate-200 cursor-pointer border-none bg-transparent hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors uppercase tracking-wider"
					>
						<span>Include more details for better visibility</span>
						<ChevronDown v-if="!showMoreDetails" class="w-4.5 h-4.5 text-slate-400" />
						<ChevronUp v-else class="w-4.5 h-4.5 text-slate-400" />
					</button>

					<div v-show="showMoreDetails" class="mt-5 space-y-4 pb-4">
						<!-- Collaborators input -->
						<div class="flex flex-col gap-1.5 relative">
							<div class="flex justify-between items-center">
								<label class="text-[10px] font-black text-slate-404 dark:text-slate-550 uppercase tracking-wider">Collaborators</label>
								<span class="text-[10px] font-bold text-slate-404 dark:text-slate-550">{{ addWorkCollaboratorsTags.length }}/3</span>
							</div>
							<div class="w-full min-h-11 p-1.5 flex flex-wrap items-center gap-2 rounded-xl border border-slate-205 dark:border-slate-800 bg-transparent shadow-2xs focus-within:ring-1 focus-within:ring-slate-800">
								<!-- Tags list -->
								<span v-for="(tag, idx) in addWorkCollaboratorsTags" :key="idx" class="h-7 px-3 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 inline-flex items-center gap-1.5 border border-slate-200/40 dark:border-slate-700/40 shadow-3xs">
									{{ tag }}
									<X class="w-3.5 h-3.5 text-slate-400 hover:text-slate-700 dark:hover:text-white cursor-pointer shrink-0" @click="removeCollaboratorTag(idx)" />
								</span>
								<!-- Input element -->
								<input 
									v-model="addWorkCollaboratorsInput" 
									type="text" 
									:disabled="addWorkCollaboratorsTags.length >= 3"
									placeholder="Who else worked on this project?"
									@keydown.enter.prevent="addCollaboratorTag"
									@input="handleCollaboratorSearch"
									class="flex-1 h-8 px-2 bg-transparent text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden disabled:opacity-50 border-none min-w-[100px]"
								/>
							</div>
							
							<!-- Autocomplete Suggestions Dropdown for Collaborators -->
							<div v-if="showCollaboratorsDropdown && collaboratorsSuggestions.length > 0 && addWorkCollaboratorsTags.length < 3" class="absolute bottom-full left-0 right-0 mb-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl rounded-2xl py-1.5 z-40 overflow-hidden max-h-[160px] overflow-y-auto">
								<button 
									type="button"
									@click="addCollaboratorSuggestion(u.name)"
									v-for="u in collaboratorsSuggestions" 
									:key="u.id"
									class="w-full px-4 py-2 flex items-center gap-3 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-left text-xs font-semibold text-slate-700 dark:text-slate-200 cursor-pointer border-none bg-transparent"
								>
									<div class="w-7 h-7 rounded-full border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800 overflow-hidden flex items-center justify-center shrink-0">
										<img v-if="u.foto_path" :src="u.foto_path" alt="Avatar" class="w-full h-full object-cover" />
										<span v-else class="text-slate-800 dark:text-slate-200 font-bold text-[10px]">{{ u.name.charAt(0).toUpperCase() }}</span>
									</div>
									<div class="min-w-0 flex-1">
										<p class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate leading-none mb-0.5">{{ u.name }}</p>
										<p class="text-[9px] text-slate-400 dark:text-slate-505 truncate leading-none">{{ u.role_title }}</p>
									</div>
								</button>
							</div>
							<span class="text-[10px] font-bold text-slate-404 dark:text-slate-550">Optional</span>
						</div>

						<!-- Client(s) input -->
						<div class="flex flex-col gap-1.5">
							<label class="text-[10px] font-black text-slate-404 dark:text-slate-550 uppercase tracking-wider">Client(s)</label>
							<input 
								v-model="addWorkForm.client" 
								type="text" 
								placeholder="Who hired you to complete this project?"
								class="w-full h-11 px-4 rounded-xl border border-slate-205 dark:border-slate-800 bg-transparent text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 transition-all shadow-2xs"
							/>
							<span class="text-[10px] font-bold text-slate-404 dark:text-slate-550">Optional</span>
						</div>

						<!-- Project Timeline -->
						<div class="grid grid-cols-2 gap-4">
							<div class="flex flex-col gap-1.5">
								<label class="text-[10px] font-black text-slate-404 dark:text-slate-555 uppercase tracking-wider">Start date</label>
								<input 
									v-model="addWorkForm.start_date" 
									type="date" 
									class="w-full h-11 px-4 rounded-xl border border-slate-205 dark:border-slate-800 bg-transparent text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 transition-all shadow-2xs"
								/>
							</div>
							<div class="flex flex-col gap-1.5">
								<label class="text-[10px] font-black text-slate-404 dark:text-slate-555 uppercase tracking-wider">End date</label>
								<input 
									v-model="addWorkForm.end_date" 
									type="date" 
									class="w-full h-11 px-4 rounded-xl border border-slate-205 dark:border-slate-800 bg-transparent text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 transition-all shadow-2xs"
								/>
							</div>
						</div>

						<!-- Industry select dropdown -->
						<div class="flex flex-col gap-1.5">
							<label class="text-[10px] font-black text-slate-404 dark:text-slate-550 uppercase tracking-wider">Industry</label>
							<select 
								v-model="addWorkForm.industry" 
								class="w-full h-11 px-4 rounded-xl border border-slate-205 dark:border-slate-800 bg-transparent text-xs font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-1 focus:ring-slate-800 transition-all shadow-2xs"
							>
								<option value="" class="dark:bg-slate-900">Select an industry</option>
								<option value="Design & Creative" class="dark:bg-slate-900">Design & Creative</option>
								<option value="Tech & Software" class="dark:bg-slate-900">Tech & Software</option>
								<option value="Education & Training" class="dark:bg-slate-900">Education & Training</option>
								<option value="Arts & Entertainment" class="dark:bg-slate-900">Arts & Entertainment</option>
								<option value="Marketing & Business" class="dark:bg-slate-900">Marketing & Business</option>
								<option value="Other" class="dark:bg-slate-900">Other</option>
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>

		<template #footer>
			<div class="w-full flex items-center justify-between">
				<button 
					type="button" 
					@click="emit('close')" 
					class="px-5 py-2.5 rounded-full border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-355 cursor-pointer bg-transparent"
				>
					Cancel
				</button>
				<button 
					type="button" 
					@click="quickStoreSubmit" 
					:disabled="isSubmittingWork || !cropSaved || !addWorkForm.title || !coverPreviewUrl || addWorkSkillsTags.length < 1 || addWorkToolsTags.length < 1"
					class="inline-flex items-center gap-2 px-6 h-11 bg-slate-950 dark:bg-white text-white dark:text-slate-950 font-black text-xs uppercase tracking-wider rounded-xl cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-800 dark:hover:bg-slate-100 shadow-md transition-all border-none"
				>
					<Loader2 v-if="isSubmittingWork" class="w-3.5 h-3.5 animate-spin" />
					<span>{{ isEditingQuickWork ? 'Save changes' : 'Add to profile' }}</span>
				</button>
			</div>
		</template>
	</Modal>
</template>
