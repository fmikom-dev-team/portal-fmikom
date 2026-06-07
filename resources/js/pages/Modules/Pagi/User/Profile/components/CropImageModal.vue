<script setup lang="ts">
import { ref, watch, nextTick, onUnmounted } from "vue";
import { ZoomIn, ZoomOut, RotateCcw, RotateCw } from "lucide-vue-next";
import Cropper from "cropperjs";
import "cropperjs/dist/cropper.css";

const props = defineProps<{
	show: boolean;
	imageSrc: string;
	initialAspectRatio: number | string;
	originalFileName: string;
	originalFileType: string;
}>();

const emit = defineEmits(["close", "save"]);

const cropperImageRef = ref<HTMLImageElement | null>(null);
const cropperAspectRatio = ref<number | string>(props.initialAspectRatio);
let cropperInstance: Cropper | null = null;

const changeAspectRatio = (ratio: number | string) => {
	cropperAspectRatio.value = ratio;
	if (!cropperInstance) return;
	if (ratio === "free") {
		cropperInstance.setAspectRatio(NaN);
	} else {
		cropperInstance.setAspectRatio(Number(ratio));
	}
};

const initCropper = () => {
	if (!cropperImageRef.value) return;
	if (cropperInstance) {
		cropperInstance.destroy();
	}
	const ratio = cropperAspectRatio.value === "free" ? NaN : Number(cropperAspectRatio.value);
	cropperInstance = new Cropper(cropperImageRef.value, {
		aspectRatio: ratio,
		viewMode: 1,
		dragMode: "move",
		autoCropArea: 1.0,
		restore: false,
		guides: true,
		center: true,
		highlight: false,
		cropBoxMovable: true,
		cropBoxResizable: true,
		toggleDragModeOnDblclick: false,
		background: true,
	});
};

const handleCropSave = () => {
	if (!cropperInstance) return;

	let cropWidth = 3200;
	let cropHeight = 410;
	
	if (cropperAspectRatio.value !== (3200 / 410)) {
		const data = cropperInstance.getData();
		cropWidth = Math.round(data.width);
		cropHeight = Math.round(data.height);
	}

	const canvas = cropperInstance.getCroppedCanvas({
		width: cropWidth,
		height: cropHeight,
		imageSmoothingEnabled: true,
		imageSmoothingQuality: "high",
	});

	if (canvas) {
		canvas.toBlob((blob) => {
			if (blob) {
				const croppedFile = new File([blob], props.originalFileName, {
					type: props.originalFileType,
				});
				emit("save", croppedFile);
			}
		}, props.originalFileType);
	}
};

const destroyCropper = () => {
	if (cropperInstance) {
		cropperInstance.destroy();
		cropperInstance = null;
	}
};

const zoomIn = () => {
	cropperInstance?.zoom(0.1);
};

const zoomOut = () => {
	cropperInstance?.zoom(-0.1);
};

const rotateLeft = () => {
	cropperInstance?.rotate(-90);
};

const rotateRight = () => {
	cropperInstance?.rotate(90);
};

watch(() => props.show, (newVal) => {
	if (newVal) {
		nextTick(() => {
			initCropper();
		});
	} else {
		destroyCropper();
	}
});

onUnmounted(() => {
	destroyCropper();
});
</script>

<template>
	<div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/25 dark:bg-slate-950/40 overflow-y-auto">
		<div class="bg-white dark:bg-slate-900 w-full max-w-2xl rounded-3xl shadow-2xl border border-slate-105 dark:border-slate-800 overflow-hidden flex flex-col">
			<!-- Modal Header -->
			<div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between text-left">
				<div>
					<h3 class="text-sm font-black uppercase tracking-wider text-slate-900 dark:text-white">Crop Profile Banner</h3>
					<p class="text-[10px] text-slate-500 dark:text-slate-400 font-bold mt-0.5">Drag and adjust banner to match wide aspect ratio (3200 x 410)</p>
				</div>
			</div>

			<!-- Cropper Arena -->
			<div class="p-6 bg-slate-55 dark:bg-slate-955 flex items-center justify-center min-h-[300px] max-h-[380px]">
				<div class="w-full max-h-[320px] overflow-hidden rounded-xl bg-slate-200 dark:bg-slate-800 relative">
					<img ref="cropperImageRef" :src="imageSrc" class="max-w-full block" alt="Source image for cropping" />
				</div>
			</div>

			<!-- Aspect Ratio Controls Selector -->
			<div class="px-6 py-2.5 bg-slate-55 dark:bg-slate-955/80 border-b border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-center gap-2">
				<span class="text-[10px] font-black uppercase text-slate-404 dark:text-slate-500 mr-1 select-none">Aspect Ratio:</span>
				<button 
					type="button" 
					@click="changeAspectRatio(3200/410)"
					class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase border transition-colors cursor-pointer"
					:class="cropperAspectRatio === (3200/410) ? 'bg-slate-900 text-white border-slate-900 dark:bg-white dark:text-slate-950 dark:border-white' : 'bg-white text-slate-600 border-slate-202 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700 dark:hover:bg-slate-700'"
				>
					Wide Banner
				</button>
				<button 
					type="button" 
					@click="changeAspectRatio(16/9)"
					class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase border transition-colors cursor-pointer"
					:class="cropperAspectRatio === (16/9) ? 'bg-slate-900 text-white border-slate-900 dark:bg-white dark:text-slate-950 dark:border-white' : 'bg-white text-slate-600 border-slate-202 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700 dark:hover:bg-slate-700'"
				>
					16:9
				</button>
				<button 
					type="button" 
					@click="changeAspectRatio(16/10)"
					class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase border transition-colors cursor-pointer"
					:class="cropperAspectRatio === (16/10) ? 'bg-slate-900 text-white border-slate-900 dark:bg-white dark:text-slate-950 dark:border-white' : 'bg-white text-slate-600 border-slate-202 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700 dark:hover:bg-slate-700'"
				>
					16:10
				</button>
				<button 
					type="button" 
					@click="changeAspectRatio(4/3)"
					class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase border transition-colors cursor-pointer"
					:class="cropperAspectRatio === (4/3) ? 'bg-slate-900 text-white border-slate-900 dark:bg-white dark:text-slate-950 dark:border-white' : 'bg-white text-slate-600 border-slate-202 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700 dark:hover:bg-slate-700'"
				>
					4:3
				</button>
				<button 
					type="button" 
					@click="changeAspectRatio(5/4)"
					class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase border transition-colors cursor-pointer"
					:class="cropperAspectRatio === (5/4) ? 'bg-slate-900 text-white border-slate-900 dark:bg-white dark:text-slate-950 dark:border-white' : 'bg-white text-slate-600 border-slate-202 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700 dark:hover:bg-slate-700'"
				>
					5:4
				</button>
				<button 
					type="button" 
					@click="changeAspectRatio(9/16)"
					class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase border transition-colors cursor-pointer"
					:class="cropperAspectRatio === (9/16) ? 'bg-slate-900 text-white border-slate-900 dark:bg-white dark:text-slate-950 dark:border-white' : 'bg-white text-slate-600 border-slate-202 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700 dark:hover:bg-slate-700'"
				>
					9:16
				</button>
				<button 
					type="button" 
					@click="changeAspectRatio('free')"
					class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase border transition-colors cursor-pointer"
					:class="cropperAspectRatio === 'free' ? 'bg-slate-900 text-white border-slate-900 dark:bg-white dark:text-slate-950 dark:border-white' : 'bg-white text-slate-600 border-slate-202 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700 dark:hover:bg-slate-700'"
				>
					Free
				</button>
			</div>

			<!-- Toolbars (Zoom, Rotate) -->
			<div class="px-6 py-3 bg-slate-55/50 dark:bg-slate-900/50 border-t border-b border-slate-100 dark:border-slate-800 flex items-center justify-center gap-3">
				<button 
					type="button" 
					@click="zoomIn"
					title="Zoom In"
					class="p-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-202 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-2xs cursor-pointer"
				>
					<ZoomIn class="w-4 h-4" />
				</button>
				<button 
					type="button" 
					@click="zoomOut"
					title="Zoom Out"
					class="p-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-202 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-2xs cursor-pointer"
				>
					<ZoomOut class="w-4 h-4" />
				</button>
				<div class="w-px h-5 bg-slate-200 dark:bg-slate-700 mx-1"></div>
				<button 
					type="button" 
					@click="rotateLeft"
					title="Rotate Left"
					class="p-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-202 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-2xs cursor-pointer"
				>
					<RotateCcw class="w-4 h-4" />
				</button>
				<button 
					type="button" 
					@click="rotateRight"
					title="Rotate Right"
					class="p-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-202 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-2xs cursor-pointer"
				>
					<RotateCw class="w-4 h-4" />
				</button>
			</div>

			<!-- Modal Actions -->
			<div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
				<button 
					type="button" 
					@click="emit('close')"
					class="px-4 py-2 border border-slate-202 dark:border-slate-800 text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl cursor-pointer"
				>
					Cancel
				</button>
				<button 
					type="button" 
					@click="handleCropSave"
					class="px-5 py-2 bg-slate-900 dark:bg-white text-white dark:text-slate-950 text-xs font-black uppercase tracking-wider hover:bg-slate-800 dark:hover:bg-slate-100 rounded-xl cursor-pointer"
				>
					Apply Crop
				</button>
			</div>
		</div>
	</div>
</template>
