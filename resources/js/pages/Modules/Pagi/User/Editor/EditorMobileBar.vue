<script setup lang="ts">
import {
	ArrowLeft,
	Eye,
	Image as ImageIcon,
	LayoutGrid,
	MoreVertical,
	Palette,
	Paperclip,
	PlaySquare,
	Plus,
	RotateCcw,
	Save,
	Send,
	Sliders,
	Trash2,
	Type,
	X,
} from "lucide-vue-next";
import { ref } from "vue";

const props = defineProps<{
	globalSpacing: number;
	canvasBgColor: string;
	canvasTextColor: string;
	stylePresets: Array<{ name: string; bg: string; text: string }>;
	contentOptions: Array<{ id: string; label: string; icon: any }>;
	processing?: boolean;
	disableSave?: boolean;
}>();

const emit = defineEmits<{
	(e: "update:globalSpacing", val: number): void;
	(e: "update:canvasBgColor", val: string): void;
	(e: "update:canvasTextColor", val: string): void;
	(e: "add-block", type: string): void;
	(e: "open-asset-modal"): void;
	(e: "show-publish-modal"): void;
	(e: "save-draft"): void;
	(e: "preview"): void;
	(e: "update-settings"): void;
	(e: "cancel"): void;
	(e: "discard-draft"): void;
}>();

const activeSheet = ref<"styles" | "actions" | null>(null);

const closeSheet = () => {
	activeSheet.value = null;
};

const handleAddBlock = (type: string) => {
	if (type === "asset") {
		emit("open-asset-modal");
	} else {
		emit("add-block", type);
	}
};

const selectPreset = (bg: string, text: string) => {
	emit("update:canvasBgColor", bg);
	emit("update:canvasTextColor", text);
	emit("update-settings");
};
</script>

<template>
	<!-- STICKY TOP CONTROL SUB-HEADER BAR (< lg) -->
	<div
		class="w-full shrink-0 lg:hidden bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border-b border-slate-200/80 dark:border-slate-800 px-3 py-2 flex items-center justify-between shadow-2xs select-none z-30"
	>
		<!-- Left: Cancel / Back Button -->
		<button
			type="button"
			@click="emit('cancel')"
			class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-zinc-200 text-xs font-bold transition-colors cursor-pointer border-none bg-transparent"
		>
			<ArrowLeft class="w-4 h-4" />
			<span>Kembali</span>
		</button>

		<!-- Center: Quick Tool Controls (Styles, Preview, Menu) -->
		<div class="flex items-center gap-1">
			<!-- Styles Button -->
			<button
				type="button"
				@click="activeSheet = 'styles'"
				class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 text-xs font-bold transition-colors cursor-pointer border-none bg-transparent"
			>
				<Palette class="w-4 h-4" />
				<span>Styles</span>
			</button>

			<!-- Preview Button -->
			<button
				type="button"
				@click="emit('preview')"
				class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-950/40 text-blue-600 dark:text-blue-400 text-xs font-bold transition-colors cursor-pointer border-none bg-transparent"
			>
				<Eye class="w-4 h-4" />
				<span>Preview</span>
			</button>

			<!-- Menu Button -->
			<button
				type="button"
				@click="activeSheet = 'actions'"
				class="p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-zinc-200 transition-colors cursor-pointer border-none bg-transparent"
			>
				<MoreVertical class="w-4 h-4" />
			</button>
		</div>

		<!-- Right: Primary Next / Publish Step Button -->
		<button
			type="button"
			@click="emit('show-publish-modal')"
			:disabled="disableSave || processing"
			class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs font-black tracking-wide uppercase transition-all shadow-sm active:scale-95 cursor-pointer border-none shrink-0"
		>
			<span>Lanjut</span>
			<Send class="w-3.5 h-3.5" />
		</button>
	</div>

	<!-- STICKY BOTTOM QUICK-ADD BLOCK BAR (< lg) -->
	<div
		class="fixed bottom-0 inset-x-0 z-40 lg:hidden bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border-t border-slate-200/80 dark:border-slate-800 px-2 py-2 flex items-center justify-around shadow-[0_-4px_24px_rgba(0,0,0,0.08)] select-none"
		style="padding-bottom: max(0.6rem, env(safe-area-inset-bottom));"
	>
		<!-- 1. Text Block -->
		<button
			type="button"
			@click="handleAddBlock('text')"
			class="flex flex-col items-center justify-center gap-0.5 flex-1 py-1 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition-colors cursor-pointer border-none bg-transparent active:scale-90"
		>
			<div class="h-8 w-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-2xs">
				<Type class="w-4 h-4" stroke-width="2" />
			</div>
			<span class="text-[9px] font-extrabold tracking-tight uppercase">Teks</span>
		</button>

		<!-- 2. Image Block -->
		<button
			type="button"
			@click="handleAddBlock('image')"
			class="flex flex-col items-center justify-center gap-0.5 flex-1 py-1 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition-colors cursor-pointer border-none bg-transparent active:scale-90"
		>
			<div class="h-8 w-8 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shadow-2xs">
				<ImageIcon class="w-4 h-4" stroke-width="2" />
			</div>
			<span class="text-[9px] font-extrabold tracking-tight uppercase">Gambar</span>
		</button>

		<!-- 3. Photo Grid Block -->
		<button
			type="button"
			@click="handleAddBlock('photo_grid')"
			class="flex flex-col items-center justify-center gap-0.5 flex-1 py-1 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition-colors cursor-pointer border-none bg-transparent active:scale-90"
		>
			<div class="h-8 w-8 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center shadow-2xs">
				<LayoutGrid class="w-4 h-4" stroke-width="2" />
			</div>
			<span class="text-[9px] font-extrabold tracking-tight uppercase">Grid</span>
		</button>

		<!-- 4. Video Block -->
		<button
			type="button"
			@click="handleAddBlock('video_audio')"
			class="flex flex-col items-center justify-center gap-0.5 flex-1 py-1 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition-colors cursor-pointer border-none bg-transparent active:scale-90"
		>
			<div class="h-8 w-8 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shadow-2xs">
				<PlaySquare class="w-4 h-4" stroke-width="2" />
			</div>
			<span class="text-[9px] font-extrabold tracking-tight uppercase">Video</span>
		</button>

		<!-- 5. Asset Link Block -->
		<button
			type="button"
			@click="handleAddBlock('asset')"
			class="flex flex-col items-center justify-center gap-0.5 flex-1 py-1 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition-colors cursor-pointer border-none bg-transparent active:scale-90"
		>
			<div class="h-8 w-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-2xs">
				<Paperclip class="w-4 h-4" stroke-width="2" />
			</div>
			<span class="text-[9px] font-extrabold tracking-tight uppercase">Aset</span>
		</button>
	</div>

	<!-- BOTTOM SHEET OVERLAY & DRAWERS -->
	<Transition
		enter-active-class="transition duration-200 ease-out"
		enter-from-class="opacity-0"
		enter-to-class="opacity-100"
		leave-active-class="transition duration-150 ease-in"
		leave-from-class="opacity-100"
		leave-to-class="opacity-0"
	>
		<div
			v-if="activeSheet !== null"
			class="fixed inset-0 z-[10000] bg-slate-950/60 backdrop-blur-xs lg:hidden"
			@click="closeSheet"
		></div>
	</Transition>

	<Transition
		enter-active-class="transition duration-300 ease-out transform"
		enter-from-class="translate-y-full"
		enter-to-class="translate-y-0"
		leave-active-class="transition duration-200 ease-in transform"
		leave-from-class="translate-y-0"
		leave-to-class="translate-y-full"
	>
		<div
			v-if="activeSheet !== null"
			class="fixed bottom-0 inset-x-0 z-[10001] bg-white dark:bg-slate-900 rounded-t-3xl border-t border-slate-200 dark:border-slate-800 shadow-2xl lg:hidden flex flex-col max-h-[85vh] overflow-hidden"
			style="padding-bottom: max(1.5rem, env(safe-area-inset-bottom));"
		>
			<!-- Top Drag Indicator Handle -->
			<div class="w-full flex items-center justify-center pt-3 pb-1 cursor-pointer" @click="closeSheet">
				<div class="w-12 h-1.5 rounded-full bg-slate-300 dark:bg-slate-700"></div>
			</div>

			<!-- SHEET CONTENT: STYLES -->
			<div v-if="activeSheet === 'styles'" class="p-5 flex flex-col overflow-y-auto space-y-5">
				<div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
					<div class="flex items-center gap-2">
						<Palette class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
						<h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">Pengaturan Gaya Canvas</h3>
					</div>
					<button type="button" @click="closeSheet" class="p-1.5 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 transition-colors border-none bg-transparent">
						<X class="w-5 h-5" />
					</button>
				</div>

				<!-- Dynamic Spacing Slider -->
				<div class="space-y-2">
					<div class="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-300">
						<span>Jarak Antar Blok (Spacing)</span>
						<span class="text-indigo-600 dark:text-indigo-400 font-mono">{{ props.globalSpacing }}px</span>
					</div>
					<input
						type="range"
						min="0"
						max="200"
						step="5"
						:value="props.globalSpacing"
						@input="emit('update:globalSpacing', Number(($event.target as HTMLInputElement).value)); emit('update-settings');"
						class="w-full h-2 bg-slate-200 dark:bg-slate-800 rounded-lg appearance-none cursor-pointer accent-indigo-600"
					/>
				</div>

				<!-- Style Presets -->
				<div class="space-y-2">
					<span class="text-xs font-bold text-slate-700 dark:text-slate-300 block">Preset Tema Warna Canvas</span>
					<div class="grid grid-cols-2 gap-2.5">
						<button
							v-for="preset in stylePresets"
							:key="'mob-preset-'+preset.name"
							@click="selectPreset(preset.bg, preset.text); closeSheet();"
							class="flex items-center gap-2.5 p-2.5 rounded-xl border transition-all cursor-pointer text-left shadow-3xs"
							:class="[
								props.canvasBgColor === preset.bg
									? 'border-indigo-600 ring-2 ring-indigo-500/20 bg-indigo-50/50 dark:bg-indigo-950/40'
									: 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 hover:bg-slate-50'
							]"
						>
							<div
								class="h-6 w-6 rounded-full border border-slate-300 dark:border-slate-700 shrink-0 shadow-2xs flex items-center justify-center font-bold text-[10px]"
								:style="{ backgroundColor: preset.bg, color: preset.text }"
							>
								Aa
							</div>
							<span class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">{{ preset.name }}</span>
						</button>
					</div>
				</div>
			</div>

			<!-- SHEET CONTENT: ACTIONS & MENU -->
			<div v-else-if="activeSheet === 'actions'" class="p-5 flex flex-col overflow-y-auto space-y-4">
				<div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
					<div class="flex items-center gap-2">
						<Sliders class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
						<h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">Menu Project Editor</h3>
					</div>
					<button type="button" @click="closeSheet" class="p-1.5 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 transition-colors border-none bg-transparent">
						<X class="w-5 h-5" />
					</button>
				</div>

				<div class="space-y-2">
					<button
						type="button"
						@click="emit('save-draft'); closeSheet();"
						class="w-full flex items-center justify-between p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-800 dark:text-slate-200 transition-colors cursor-pointer"
					>
						<div class="flex items-center gap-3">
							<Save class="w-4 h-4 text-emerald-500" />
							<span class="text-xs font-bold">Simpan Draf</span>
						</div>
					</button>

					<button
						type="button"
						@click="emit('discard-draft'); closeSheet();"
						class="w-full flex items-center justify-between p-3.5 rounded-2xl border border-red-200 dark:border-red-900/40 bg-red-50/50 dark:bg-red-950/20 hover:bg-red-100/60 dark:hover:bg-red-950/40 text-red-600 dark:text-red-400 transition-colors cursor-pointer"
					>
						<div class="flex items-center gap-3">
							<Trash2 class="w-4 h-4" />
							<span class="text-xs font-bold">Buang Draf Pekerjaan</span>
						</div>
					</button>

					<button
						type="button"
						@click="emit('cancel'); closeSheet();"
						class="w-full flex items-center justify-between p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 transition-colors cursor-pointer"
					>
						<div class="flex items-center gap-3">
							<RotateCcw class="w-4 h-4" />
							<span class="text-xs font-bold">Keluar Editor</span>
						</div>
					</button>
				</div>
			</div>
		</div>
	</Transition>
</template>
