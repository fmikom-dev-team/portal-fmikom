<script setup lang="ts">
import { ref, watch } from "vue";
import { UploadCloud, X, Loader2 } from "lucide-vue-next";
import Modal from "../../ui/Modal.vue";
import Progress from "../../ui/Progress.vue";

const props = defineProps<{
	show: boolean;
	user: any;
	form: any;
}>();

const emit = defineEmits(["close", "submit", "warning", "toast", "trigger-crop"]);

const activeBannerTab = ref<"Upload" | "Presets">("Upload");
const selectedPresetIndex = ref<number | null>(null);
const bannerPreview = ref<string | null>(null);
const isDragging = ref(false);
const bannerInput = ref<HTMLInputElement | null>(null);

const originalFileName = ref("banner.jpg");
const originalFileType = ref("image/jpeg");

// Dummy fields for potential WebM/FFmpeg variables (kept to match layout checks)
const isConvertingVideo = ref(false);
const videoConvertProgress = ref(0);

const isVideoUrl = (url: string | null): boolean => {
	if (!url) return false;
	const cleanUrl = url.split("?")[0].split("#")[0];
	const ext = cleanUrl.split(".").pop()?.toLowerCase();
	return ["mp4", "webm", "ogg"].includes(ext || "");
};

// Preset Banner options
const presets = [
	{
		name: "Cyber Slate",
		svgMini: `
			<svg viewBox="0 0 320 41" width="100%" height="100%" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
				<rect width="100%" height="100%" fill="#0f172a"/>
				<defs>
					<pattern id="gridMini1" width="8" height="8" patternUnits="userSpaceOnUse">
						<path d="M 8 0 L 0 0 0 8" fill="none" stroke="rgba(51, 65, 85, 0.3)" stroke-width="0.5"/>
					</pattern>
					<radialGradient id="glowMini1" cx="80%" cy="20%" r="60%">
						<stop offset="0%" stop-color="#3b82f6" stop-opacity="0.25"/>
						<stop offset="100%" stop-color="#0f172a" stop-opacity="0"/>
					</radialGradient>
				</defs>
				<rect width="100%" height="100%" fill="url(#gridMini1)"/>
				<rect width="100%" height="100%" fill="url(#glowMini1)"/>
			</svg>
		`,
		svgFull: `
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3200 410" width="3200" height="410">
				<rect width="3200" height="410" fill="#0f172a"/>
				<defs>
					<pattern id="gridFull1" width="40" height="40" patternUnits="userSpaceOnUse">
						<path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(51, 65, 85, 0.3)" stroke-width="1"/>
					</pattern>
					<radialGradient id="glowFull1" cx="80%" cy="20%" r="60%">
						<stop offset="0%" stop-color="#3b82f6" stop-opacity="0.15"/>
						<stop offset="50%" stop-color="#8b5cf6" stop-opacity="0.05"/>
						<stop offset="100%" stop-color="#0f172a" stop-opacity="0"/>
					</radialGradient>
					<linearGradient id="lineGrad1" x1="0%" y1="0%" x2="100%" y2="0%">
						<stop offset="0%" stop-color="#3b82f6"/>
						<stop offset="50%" stop-color="#8b5cf6"/>
						<stop offset="100%" stop-color="#ec4899"/>
					</linearGradient>
				</defs>
				<rect width="3200" height="410" fill="url(#gridFull1)"/>
				<rect width="3200" height="410" fill="url(#glowFull1)"/>
				<circle cx="1600" cy="205" r="300" fill="none" stroke="rgba(99, 102, 241, 0.05)" stroke-width="1" stroke-dasharray="10 10"/>
				<circle cx="1600" cy="205" r="450" fill="none" stroke="rgba(99, 102, 241, 0.03)" stroke-width="2"/>
				<path d="M-100,300 L600,100 L1200,250 L1800,50 L2400,200 L3300,50" fill="none" stroke="url(#lineGrad1)" stroke-width="2" opacity="0.3"/>
			</svg>
		`
	},
	{
		name: "Aurora Flow",
		svgMini: `
			<svg viewBox="0 0 320 41" width="100%" height="100%" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
				<defs>
					<linearGradient id="auroraBgMini" x1="0%" y1="0%" x2="100%" y2="100%">
						<stop offset="0%" stop-color="#4f46e5"/>
						<stop offset="50%" stop-color="#7c3aed"/>
						<stop offset="100%" stop-color="#c084fc"/>
					</linearGradient>
					<radialGradient id="blobMini1" cx="20%" cy="30%" r="50%">
						<stop offset="0%" stop-color="#60a5fa" stop-opacity="0.6"/>
						<stop offset="100%" stop-color="#60a5fa" stop-opacity="0"/>
					</radialGradient>
					<radialGradient id="blobMini2" cx="80%" cy="70%" r="60%">
						<stop offset="0%" stop-color="#f472b6" stop-opacity="0.6"/>
						<stop offset="100%" stop-color="#f472b6" stop-opacity="0"/>
					</radialGradient>
				</defs>
				<rect width="100%" height="100%" fill="url(#auroraBgMini)"/>
				<rect width="100%" height="100%" fill="url(#blobMini1)"/>
				<rect width="100%" height="100%" fill="url(#blobMini2)"/>
			</svg>
		`,
		svgFull: `
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3200 410" width="3200" height="410">
				<defs>
					<linearGradient id="auroraBgFull" x1="0%" y1="0%" x2="100%" y2="100%">
						<stop offset="0%" stop-color="#4f46e5"/>
						<stop offset="50%" stop-color="#7c3aed"/>
						<stop offset="100%" stop-color="#c084fc"/>
					</linearGradient>
					<radialGradient id="blobFull1" cx="20%" cy="30%" r="50%">
						<stop offset="0%" stop-color="#60a5fa" stop-opacity="0.6"/>
						<stop offset="100%" stop-color="#60a5fa" stop-opacity="0"/>
					</radialGradient>
					<radialGradient id="blobFull2" cx="80%" cy="70%" r="60%">
						<stop offset="0%" stop-color="#f472b6" stop-opacity="0.6"/>
						<stop offset="100%" stop-color="#f472b6" stop-opacity="0"/>
					</radialGradient>
				</defs>
				<rect width="3200" height="410" fill="url(#auroraBgFull)"/>
				<rect width="3200" height="410" fill="url(#blobFull1)"/>
				<rect width="3200" height="410" fill="url(#blobFull2)"/>
				<path d="M 0 200 Q 800 50 1600 250 T 3200 150 L 3200 410 L 0 410 Z" fill="rgba(255, 255, 255, 0.05)"/>
				<path d="M 0 300 Q 600 150 1400 350 T 3200 250 L 3200 410 L 0 410 Z" fill="rgba(255, 255, 255, 0.03)"/>
			</svg>
		`
	},
	{
		name: "Midnight Mesh",
		svgMini: `
			<svg viewBox="0 0 320 41" width="100%" height="100%" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
				<rect width="100%" height="100%" fill="#030712"/>
				<defs>
					<radialGradient id="neonGlowMini" cx="50%" cy="50%" r="50%">
						<stop offset="0%" stop-color="#06b6d4" stop-opacity="0.2"/>
						<stop offset="100%" stop-color="#030712" stop-opacity="0"/>
					</radialGradient>
				</defs>
				<rect width="100%" height="100%" fill="url(#neonGlowMini)"/>
				<g stroke="rgba(6, 182, 212, 0.2)" stroke-width="0.3">
					<line x1="20" y1="10" x2="40" y2="25" />
					<line x1="40" y1="25" x2="60" y2="15" />
					<line x1="60" y1="15" x2="80" y2="30" />
				</g>
			</svg>
		`,
		svgFull: `
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3200 410" width="3200" height="410">
				<rect width="3200" height="410" fill="#030712"/>
				<defs>
					<radialGradient id="neonGlowFull" cx="50%" cy="50%" r="50%">
						<stop offset="0%" stop-color="#06b6d4" stop-opacity="0.15"/>
						<stop offset="100%" stop-color="#030712" stop-opacity="0"/>
					</radialGradient>
				</defs>
				<rect width="3200" height="410" fill="url(#neonGlowFull)"/>
				<g stroke="rgba(6, 182, 212, 0.15)" stroke-width="1">
					<line x1="200" y1="100" x2="400" y2="250" />
					<line x1="400" y1="250" x2="600" y2="150" />
					<line x1="600" y1="150" x2="800" y2="300" />
					<line x1="800" y1="300" x2="1000" y2="100" />
					<line x1="200" y1="100" x2="300" y2="50" />
					<line x1="400" y1="250" x2="450" y2="350" />
					<line x1="600" y1="150" x2="700" y2="80" />
					<line x1="800" y1="300" x2="900" y2="380" />
					<line x1="300" y1="50" x2="450" y2="350" stroke="rgba(139, 92, 246, 0.12)" />
				</g>
				<g fill="#22d3ee">
					<circle cx="200" cy="100" r="3" />
					<circle cx="400" cy="250" r="3" />
					<circle cx="600" cy="150" r="3" />
					<circle cx="800" cy="300" r="3" />
					<circle cx="1000" cy="100" r="3" />
				</g>
			</svg>
		`
	},
	{
		name: "Monochrome Tech",
		svgMini: `
			<svg viewBox="0 0 320 41" width="100%" height="100%" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
				<rect width="100%" height="100%" fill="#f8fafc"/>
				<defs>
					<pattern id="dotGridMini" width="4" height="4" patternUnits="userSpaceOnUse">
						<circle cx="0.5" cy="0.5" r="0.25" fill="#cbd5e1" />
					</pattern>
				</defs>
				<rect width="100%" height="100%" fill="url(#dotGridMini)"/>
			</svg>
		`,
		svgFull: `
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3200 410" width="3200" height="410">
				<rect width="3200" height="410" fill="#f8fafc"/>
				<defs>
					<pattern id="dotGridFull" width="30" height="30" patternUnits="userSpaceOnUse">
						<circle cx="3" cy="3" r="1.5" fill="#e2e8f0" />
					</pattern>
				</defs>
				<rect width="3200" height="410" fill="url(#dotGridFull)"/>
				<line x1="0" y1="80" x2="3200" y2="80" stroke="#f1f5f9" stroke-width="2"/>
				<line x1="0" y1="330" x2="3200" y2="330" stroke="#f1f5f9" stroke-width="2"/>
			</svg>
		`
	},
	{
		name: "Academic Gold",
		svgMini: `
			<svg viewBox="0 0 320 41" width="100%" height="100%" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
				<defs>
					<linearGradient id="royalNavyMini" x1="0%" y1="0%" x2="100%" y2="0%">
						<stop offset="0%" stop-color="#0f172a"/>
						<stop offset="100%" stop-color="#1e293b"/>
					</linearGradient>
				</defs>
				<rect width="100%" height="100%" fill="url(#royalNavyMini)"/>
			</svg>
		`,
		svgFull: `
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3200 410" width="3200" height="410">
				<defs>
					<linearGradient id="royalNavyFull" x1="0%" y1="0%" x2="100%" y2="0%">
						<stop offset="0%" stop-color="#0f172a"/>
						<stop offset="60%" stop-color="#1e293b"/>
						<stop offset="100%" stop-color="#0f172a"/>
					</linearGradient>
					<linearGradient id="goldGradFull" x1="0%" y1="0%" x2="100%" y2="100%">
						<stop offset="0%" stop-color="#fbbf24"/>
						<stop offset="50%" stop-color="#f59e0b"/>
						<stop offset="100%" stop-color="#b45309"/>
					</linearGradient>
				</defs>
				<rect width="3200" height="410" fill="url(#royalNavyFull)"/>
				<path d="M 2400 410 C 2700 180, 2800 280, 3200 100" fill="none" stroke="url(#goldGradFull)" stroke-width="3" opacity="0.85"/>
			</svg>
		`
	}
];

const selectPreset = (index: number) => {
	selectedPresetIndex.value = index;
	const preset = presets[index];
	
	const svgString = preset.svgFull;
	const img = new Image();
	img.onload = () => {
		const canvas = document.createElement("canvas");
		canvas.width = 3200;
		canvas.height = 410;
		const ctx = canvas.getContext("2d");
		if (ctx) {
			ctx.drawImage(img, 0, 0, 3200, 410);
			canvas.toBlob((blob) => {
				if (blob) {
					const presetFile = new File([blob], `preset-${preset.name.toLowerCase().replace(/\s+/g, "-")}.png`, {
						type: "image/png"
					});
					props.form.banner = presetFile;
					bannerPreview.value = URL.createObjectURL(presetFile);
				}
			}, "image/png");
		}
	};
	img.src = "data:image/svg+xml;charset=utf-8," + encodeURIComponent(svgString);
};

const triggerBannerUpload = () => {
	bannerInput.value?.click();
};

const handleBannerFile = (file: File) => {
	// Mime-type verification
	const validTypes = [
		"image/jpeg", "image/png", "image/webp", "image/gif",
		"video/mp4", "video/webm", "video/ogg"
	];
	if (!validTypes.includes(file.type)) {
		emit("warning",
			"Invalid File Format",
			"Please upload a valid image (png, jpg, webp, gif) or video (mp4, webm, ogg)."
		);
		return;
	}

	originalFileName.value = file.name;
	originalFileType.value = file.type;

	// For videos: assign file directly and update preview with duration check
	if (file.type.startsWith("video/")) {
		const video = document.createElement("video");
		video.preload = "metadata";
		video.onloadedmetadata = () => {
			window.URL.revokeObjectURL(video.src);
			if (video.duration > 60.5) {
				emit("warning",
					"Video Terlalu Lama",
					"Durasi video maksimal adalah 1 menit (60 detik) demi menjaga performa server."
				);
			} else {
				selectedPresetIndex.value = null;
				props.form.banner = file;
				originalFileName.value = file.name;
				originalFileType.value = file.type;
				bannerPreview.value = URL.createObjectURL(file); // immediate preview
				emit("toast", "Video loaded successfully!", "success");
			}
		};
		video.src = URL.createObjectURL(file);
		return;
	}

	// For animated GIFs, upload directly (no crop)
	if (file.type === "image/gif") {
		props.form.banner = file;
		selectedPresetIndex.value = null;
		bannerPreview.value = URL.createObjectURL(file);
		emit("toast", "GIF loaded successfully!", "success");
		return;
	}

	// For normal images: emit crop event
	const reader = new FileReader();
	reader.onload = (event) => {
		if (event.target?.result) {
			emit("trigger-crop", {
				src: event.target.result as string,
				name: file.name,
				type: file.type
			});
		}
	};
	reader.readAsDataURL(file);
};

const handleBannerChange = (e: Event) => {
	const file = (e.target as HTMLInputElement).files?.[0];
	if (file) {
		handleBannerFile(file);
	}
};

const handleBannerDrop = (e: DragEvent) => {
	isDragging.value = false;
	const file = e.dataTransfer?.files?.[0];
	if (file) {
		handleBannerFile(file);
	}
};

const clearBannerSelection = () => {
	props.form.banner = null;
	bannerPreview.value = null;
	selectedPresetIndex.value = null;
};

const getUploadStatusMessage = (progress: number) => {
	if (progress < 5) return "Memulai pengunggahan...";
	if (progress < 25) return "Mengompresi dan mengoptimalkan berkas...";
	if (progress < 50) return "Menyiapkan paket pengunggahan...";
	if (progress < 75) return "Mengunggah berkas ke server...";
	if (progress < 95) return "Menyimpan perubahan...";
	return "Selesai!";
};

const formatBytes = (bytes: number, decimals = 2) => {
	if (bytes === 0) return "0 Bytes";
	const k = 1024;
	const dm = decimals < 0 ? 0 : decimals;
	const sizes = ["Bytes", "KB", "MB", "GB"];
	const i = Math.floor(Math.log(bytes) / Math.log(k));
	return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + " " + sizes[i];
};

watch(() => props.show, (newVal) => {
	if (newVal) {
		bannerPreview.value = props.user.banner_path ? "/storage/" + props.user.banner_path : null;
		props.form.banner = null;
		selectedPresetIndex.value = null;
		activeBannerTab.value = "Upload";
	}
});

// React to crops saved in parent
watch(() => props.form.banner, (newVal) => {
	if (newVal && props.show) {
		bannerPreview.value = URL.createObjectURL(newVal);
		selectedPresetIndex.value = null;
	}
});
</script>

<template>
	<Modal :show="show" title="Update Cover / Featured Banner" maxWidth="2xl" @close="emit('close')" :preventClose="form.processing || isConvertingVideo">
		<div class="relative min-h-[300px]">
			<!-- Uploading Overlay -->
			<Transition
				enter-active-class="transition duration-300 ease-out"
				enter-from-class="opacity-0 scale-98"
				enter-to-class="opacity-100 scale-100"
				leave-active-class="transition duration-200 ease-in"
				leave-from-class="opacity-100 scale-100"
				leave-to-class="opacity-0 scale-98"
			>
				<div v-if="form.processing && form.progress" class="absolute inset-0 bg-white/95 dark:bg-slate-900/95 z-50 flex flex-col items-center justify-center p-8 rounded-[20px] text-center space-y-6">
					<div class="w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-950/30 flex items-center justify-center border border-indigo-100 dark:border-indigo-900/50 animate-bounce">
						<UploadCloud class="w-8 h-8 text-indigo-500" />
					</div>
					<div class="space-y-2 max-w-md w-full">
						<h4 class="text-sm font-black uppercase tracking-wider text-slate-800 dark:text-slate-200">Uploading Featured Media</h4>
						<p class="text-xs text-slate-500 dark:text-slate-400 font-bold animate-pulse">
							{{ getUploadStatusMessage(form.progress.percentage) }}
						</p>
						<p class="text-[10px] text-slate-455 dark:text-slate-500 font-semibold">
							{{ formatBytes((form.progress as any).loaded || 0) }} of {{ formatBytes((form.progress as any).total || 0) }}
						</p>
					</div>
					
					<Progress :value="form.progress.percentage" className="w-full max-w-md" />
					
					<div class="text-base font-black text-indigo-600 dark:text-indigo-455">
						{{ form.progress.percentage }}% Complete
					</div>
				</div>
			</Transition>

			<!-- Converting Video Overlay (Static Placeholder) -->
			<Transition
				enter-active-class="transition duration-300 ease-out"
				enter-from-class="opacity-0 scale-98"
				enter-to-class="opacity-100 scale-100"
				leave-active-class="transition duration-200 ease-in"
				leave-from-class="opacity-100 scale-100"
				leave-to-class="opacity-0 scale-98"
			>
				<div v-if="isConvertingVideo" class="absolute inset-0 bg-white/95 dark:bg-slate-900/95 z-50 flex flex-col items-center justify-center p-8 rounded-[20px] text-center space-y-6">
					<div class="w-16 h-16 rounded-2xl bg-violet-50 dark:bg-violet-950/30 flex items-center justify-center border border-violet-100 dark:border-violet-900/50">
						<svg class="w-8 h-8 text-violet-500 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
							<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
							<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
						</svg>
					</div>
					<div class="space-y-2 max-w-md w-full">
						<h4 class="text-sm font-black uppercase tracking-wider text-slate-800 dark:text-slate-200">Converting to WebM</h4>
						<p class="text-xs text-slate-550 dark:text-slate-400 font-semibold truncate max-w-xs mx-auto mb-1">
							{{ originalFileName }} → WebM
						</p>
						<p class="text-xs text-violet-650 dark:text-violet-455 font-bold animate-pulse">
							{{ videoConvertProgress < 5 ? "Menginisialisasi konverter..." : (videoConvertProgress < 50 ? "Memproses kompresi video..." : "Menyelesaikan pembuatan WebM...") }}
						</p>
					</div>

					<div class="w-full max-w-xs space-y-2">
						<Progress :value="videoConvertProgress" className="w-full h-1.5" indicatorClassName="bg-violet-500" />
						<div v-if="videoConvertProgress > 0" class="text-xs font-bold text-violet-650 dark:text-violet-455">
							{{ videoConvertProgress }}% Selesai
						</div>
					</div>
				</div>
			</Transition>

			<!-- Tab header -->
			<div class="flex border-b border-slate-200 dark:border-slate-800 mb-6">
				<button 
					type="button"
					@click="activeBannerTab = 'Upload'"
					class="flex-1 pb-3 text-xs font-black uppercase tracking-wider transition-all relative cursor-pointer outline-hidden border-none bg-transparent"
					:class="activeBannerTab === 'Upload' ? 'text-slate-900 dark:text-white font-black' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200'"
				>
					Upload
					<div v-if="activeBannerTab === 'Upload'" class="absolute bottom-0 left-0 w-full h-0.5 bg-slate-900 dark:bg-white rounded-full"></div>
				</button>
				<button 
					type="button"
					@click="activeBannerTab = 'Presets'"
					class="flex-1 pb-3 text-xs font-black uppercase tracking-wider transition-all relative cursor-pointer outline-hidden border-none bg-transparent"
					:class="activeBannerTab === 'Presets' ? 'text-slate-900 dark:text-white font-black' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200'"
				>
					Presets
					<div v-if="activeBannerTab === 'Presets'" class="absolute bottom-0 left-0 w-full h-0.5 bg-slate-900 dark:bg-white rounded-full"></div>
				</button>
			</div>

			<!-- Upload Tab -->
			<div v-if="activeBannerTab === 'Upload'" class="space-y-6">
				<div 
					@click="triggerBannerUpload"
					@dragover.prevent="isDragging = true"
					@dragleave.prevent="isDragging = false"
					@drop.prevent="handleBannerDrop"
					class="w-full min-h-[200px] border-2 border-dashed border-slate-300 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 rounded-2xl relative overflow-hidden flex flex-col items-center justify-center cursor-pointer group hover:border-indigo-400 dark:hover:border-indigo-600 transition-colors p-6 text-center"
					:class="isDragging ? 'border-indigo-500 bg-indigo-50/10' : ''"
				>
					<!-- Icon -->
					<div class="w-12 h-12 rounded-xl bg-white dark:bg-slate-900 flex items-center justify-center border border-slate-200 dark:border-slate-800 mb-3 shadow-2xs group-hover:scale-105 transition-transform">
						<UploadCloud class="w-5 h-5 text-indigo-500" />
					</div>
					
					<!-- Text -->
					<h3 class="text-xs font-black text-slate-800 dark:text-white">Upload a new featured media</h3>
					<p class="text-[11px] font-semibold text-slate-550 mt-1">
						Drag and drop or <span class="text-slate-700 dark:text-slate-200 underline font-bold hover:text-indigo-600">browse</span>
					</p>
					<p class="text-[10px] text-slate-400 dark:text-slate-500 mt-4 leading-relaxed font-semibold">
						We recommend a video (mp4) or image (png, jpg, gif) in a 4:3, 5:4, 9:16, or 16:9 aspect ratio. Max 100MB.
					</p>
					<input 
						type="file" 
						ref="bannerInput" 
						class="hidden" 
						accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/ogg" 
						@change="handleBannerChange"
					/>
				</div>
				
				<!-- Upload Preview (if banner is selected/cropped) -->
				<div v-if="bannerPreview" class="relative rounded-2xl overflow-hidden aspect-[16/10] border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950">
					<video 
						v-if="originalFileType.startsWith('video/') || isVideoUrl(bannerPreview)"
						:src="bannerPreview"
						autoplay 
						loop 
						muted 
						playsinline 
						class="w-full h-full object-cover"
					></video>
					<img v-else :src="bannerPreview" class="w-full h-full object-cover" alt="Banner preview" />
					
					<button 
						type="button" 
						@click="clearBannerSelection" 
						class="absolute top-3 right-3 w-8 h-8 rounded-full bg-slate-950/70 text-white flex items-center justify-center hover:bg-slate-900 transition-colors border-none cursor-pointer"
					>
						<X class="w-4 h-4" />
					</button>
				</div>
			</div>

			<!-- Presets Tab -->
			<div v-if="activeBannerTab === 'Presets'" class="space-y-4">
				<div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
					<div 
						v-for="(preset, idx) in presets" 
						:key="idx"
						@click="selectPreset(idx)"
						class="h-16 rounded-xl overflow-hidden border cursor-pointer relative group transition-all"
						:class="selectedPresetIndex === idx ? 'border-slate-900 dark:border-white ring-2 ring-indigo-500/20 scale-[1.02]' : 'border-slate-200 dark:border-slate-800/80 hover:border-slate-400 dark:hover:border-slate-700'"
					>
						<div class="absolute inset-0 w-full h-full [&>svg]:w-full [&>svg]:h-full [&>svg]:object-cover" v-html="preset.svgMini"></div>
						<div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
						<div class="absolute bottom-1.5 left-2.5 text-[9px] font-black text-white drop-shadow-xs select-none uppercase tracking-wider">
							{{ preset.name }}
						</div>
						
						<!-- Selected Checkbox Badge -->
						<div v-if="selectedPresetIndex === idx" class="absolute top-1.5 right-1.5 w-4.5 h-4.5 rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-950 flex items-center justify-center shadow-xs">
							<svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4">
								<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
							</svg>
						</div>
					</div>
				</div>
			</div>
		</div>

		<template #footer>
			<button 
				type="button"
				@click="!form.processing && !isConvertingVideo && emit('close')"
				:disabled="form.processing || isConvertingVideo"
				class="px-4 py-2 border border-slate-200 dark:border-slate-800 text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl cursor-pointer"
				:class="(form.processing || isConvertingVideo) ? 'opacity-50 cursor-not-allowed' : ''"
			>
				Cancel
			</button>
			<button 
				type="button"
				@click="emit('submit')"
				:disabled="form.processing || isConvertingVideo"
				class="px-5 py-2 bg-slate-900 dark:bg-white text-white dark:text-slate-950 text-xs font-black uppercase tracking-wider hover:bg-slate-800 dark:hover:bg-slate-100 rounded-xl cursor-pointer flex items-center gap-2"
			>
				<Loader2 v-if="form.processing" class="w-3.5 h-3.5 animate-spin" />
				<svg v-else-if="isConvertingVideo" class="w-3.5 h-3.5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
					<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
					<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
				</svg>
				{{ isConvertingVideo ? "Converting..." : "Save banner" }}
			</button>
		</template>
	</Modal>
</template>
