<script setup lang="ts">
import { Camera, Loader2, UploadCloud, X } from "lucide-vue-next";
import { ref, watch } from "vue";
import Modal from "../../ui/Modal.vue";
import Progress from "../../ui/Progress.vue";

const props = defineProps<{
	show: boolean;
	user: any;
	form: any;
}>();

const emit = defineEmits(["close", "submit", "warning"]);

const photoPreview = ref<string | null>(null);
const fotoInput = ref<HTMLInputElement | null>(null);

const triggerFotoUpload = () => {
	fotoInput.value?.click();
};

const handleFotoChange = (e: Event) => {
	const file = (e.target as HTMLInputElement).files?.[0];
	if (!file) return;

	// Validate size (max 2MB)
	if (file.size > 2 * 1024 * 1024) {
		emit(
			"warning",
			"File Terlalu Besar",
			"Ukuran maksimal foto profil adalah 2MB.",
		);
		return;
	}

	props.form.foto = file;
	props.form.remove_foto = false;
	props.form.avatar_url = "";

	const reader = new FileReader();
	reader.onload = (evt) => {
		photoPreview.value = evt.target?.result as string;
	};
	reader.readAsDataURL(file);
};

const removeProfilePhoto = () => {
	photoPreview.value = null;
	props.form.foto = null;
	props.form.avatar_url = "";
	props.form.remove_foto = true;
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
	return `${parseFloat((bytes / k ** i).toFixed(dm))} ${sizes[i]}`;
};

watch(
	() => props.show,
	(newVal) => {
		if (newVal) {
			photoPreview.value = props.user.foto_path
				? props.user.foto_path.startsWith("http")
					? props.user.foto_path
					: `/storage/${props.user.foto_path}`
				: null;
			props.form.foto = null;
			props.form.remove_foto = false;
		}
	},
);
</script>

<template>
	<Modal :show="show" title="Update Profile Avatar" maxWidth="sm" @close="emit('close')" :preventClose="form.processing">
		<div class="relative flex flex-col items-center gap-6 p-4">
			<!-- Uploading Overlay -->
			<Transition
				enter-active-class="transition duration-300 ease-out"
				enter-from-class="opacity-0 scale-95"
				enter-to-class="opacity-100 scale-100"
				leave-active-class="transition duration-200 ease-in"
				leave-from-class="opacity-100 scale-100"
				leave-to-class="opacity-0 scale-95"
			>
				<div v-if="form.processing && form.progress" class="absolute inset-0 bg-white/95 dark:bg-slate-900/95 z-50 flex flex-col items-center justify-center p-4 rounded-[20px] text-center space-y-4">
					<div class="w-12 h-12 rounded-full bg-indigo-50 dark:bg-indigo-950/30 flex items-center justify-center border border-indigo-100 dark:border-indigo-900 animate-bounce">
						<UploadCloud class="w-6 h-6 text-indigo-500" />
					</div>
					<div class="space-y-1">
						<h4 class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200">Uploading Avatar</h4>
						<p class="text-[10px] text-slate-500 dark:text-slate-400 font-bold animate-pulse">{{ getUploadStatusMessage(form.progress.percentage) }}</p>
						<p class="text-[9px] text-slate-455 dark:text-slate-500 font-semibold">{{ formatBytes((form.progress as any).loaded || 0) }} / {{ formatBytes((form.progress as any).total || 0) }}</p>
					</div>
					
					<Progress :value="form.progress.percentage" className="w-full max-w-[200px]" />
					
					<div class="text-[11px] font-black text-indigo-600 dark:text-indigo-455">
						{{ form.progress.percentage }}%
					</div>
				</div>
			</Transition>

			<div class="relative group select-none shrink-0 mx-auto">
				<div 
					@click="triggerFotoUpload"
					class="w-24 h-24 rounded-full border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800 flex items-center justify-center overflow-hidden cursor-pointer relative hover:ring-2 hover:ring-indigo-500/30 transition-all shadow-xs"
				>
					<img 
						v-if="photoPreview" 
						:src="photoPreview" 
						class="w-full h-full object-cover" 
						alt="Profile avatar preview"
					/>
					<div v-else class="text-2xl font-black text-slate-400 dark:text-slate-505">
						{{ user.name.charAt(0) }}
					</div>
					
					<div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
						<Camera class="w-5 h-5 text-white" />
					</div>
				</div>

				<!-- Photo Uploader Input -->
				<input 
					type="file" 
					ref="fotoInput" 
					class="hidden" 
					accept="image/*" 
					@change="handleFotoChange"
				/>
			</div>

			<div class="text-center space-y-1">
				<p class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider">Creator Avatar</p>
				<p class="text-[10px] text-slate-500 dark:text-slate-400 leading-relaxed max-w-md font-semibold">
					Click the circle to upload a new profile photo. Recommended: 400x400px (max 2MB).
				</p>
				<button 
					v-if="photoPreview" 
					type="button" 
					@click="removeProfilePhoto"
					class="text-[10px] font-black text-red-600 hover:underline flex items-center gap-1 mx-auto mt-2 cursor-pointer border-none bg-transparent"
				>
					<X class="w-3.5 h-3.5" /> Remove Avatar
				</button>
			</div>
		</div>
		<template #footer>
			<button 
				type="button"
				@click="!form.processing && emit('close')"
				:disabled="form.processing"
				class="px-4 py-2 border border-slate-200 dark:border-slate-800 text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl cursor-pointer"
				:class="form.processing ? 'opacity-50 cursor-not-allowed' : ''"
			>
				Cancel
			</button>
			<button 
				type="button"
				@click="emit('submit')"
				:disabled="form.processing"
				class="px-5 py-2 bg-slate-900 dark:bg-white text-white dark:text-slate-950 text-xs font-black uppercase tracking-wider hover:bg-slate-800 dark:hover:bg-slate-100 rounded-xl cursor-pointer flex items-center gap-2"
			>
				<Loader2 v-if="form.processing" class="w-3.5 h-3.5 animate-spin" />
				Save avatar
			</button>
		</template>
	</Modal>
</template>
