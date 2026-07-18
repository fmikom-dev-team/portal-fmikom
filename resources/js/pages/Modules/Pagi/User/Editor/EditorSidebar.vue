<script setup lang="ts">
import { ChevronLeft, Palette, Paperclip, Sliders } from "lucide-vue-next";
import EditorToolbar from "./EditorToolbar.vue";

defineProps<{
	activeSidebarTab: string;
	globalSpacing: number;
	canvasBgColor: string;
	canvasTextColor: string;
	stylePresets: any[];
	contentOptions: any[];
	processing?: boolean;
	disableSave?: boolean;
}>();

const emit = defineEmits<{
	(e: "update:activeSidebarTab", val: string): void;
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

const selectPreset = (bg: string, text: string) => {
	emit("update:canvasBgColor", bg);
	emit("update:canvasTextColor", text);
	emit("update-settings");
};

const updateText = (c: string) => {
	emit("update:canvasTextColor", c);
	emit("update-settings");
};
</script>

<template>
	<aside class="relative hidden lg:flex flex-col shrink-0 w-80 h-[calc(100vh-64px)] bg-slate-50 dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800 overflow-hidden">
		<!-- Scrollable Content Area -->
		<div class="flex-1 overflow-y-auto pb-48">
			<!-- Content Tab (Tools and Assets) -->
			<div v-if="activeSidebarTab === 'content'">
				<!-- Add Content Section -->
				<div class="p-6">
					<h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 mb-4 uppercase tracking-wider">Add Content</h3>
					<div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl grid grid-cols-2 overflow-hidden shadow-sm">
						<button v-for="item in contentOptions" :key="'side-'+item.id" @click="emit('add-block', item.id)"
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
						<button @click="emit('open-asset-modal')" class="w-full py-2.5 px-4 rounded-xl border border-slate-200 dark:border-slate-800 text-sm font-semibold text-slate-800 dark:text-slate-200 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors flex items-center justify-center gap-2 mb-3 shadow-sm cursor-pointer">
							<Paperclip class="h-4 w-4" /> Attach Assets
						</button>
						<p class="text-[11px] text-slate-500 dark:text-slate-450 leading-relaxed">Add files like fonts, illustrations, photos, zips, or templates as free or paid downloads.</p>
					</div>
				</div>

				<!-- Edit Project Panel Section (Behance Mockup 2 style) -->
				<div class="px-6 pb-6 border-t border-slate-200 dark:border-slate-800 pt-6">
					<h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 mb-4 uppercase tracking-wider">Edit Project</h3>
					<div class="grid grid-cols-2 gap-3">
						<button @click="emit('update:activeSidebarTab', 'styles')"
							class="flex items-center justify-center gap-2 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-900 text-xs font-bold transition-all shadow-3xs cursor-pointer">
							<Palette class="w-4 h-4 text-blue-500" /> Styles
						</button>
						<button @click="emit('show-publish-modal')"
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
					<button @click="emit('update:activeSidebarTab', 'content')"
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
					<input type="range" min="0" max="100" :value="globalSpacing" @input="emit('update:globalSpacing', Number(($event.target as HTMLInputElement).value))"
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
							@click="selectPreset(preset.bg, preset.text)"
							:class="[
								'flex items-center justify-between p-3 rounded-xl border text-xs font-semibold text-left transition-all cursor-pointer hover:scale-[1.01]',
								canvasBgColor === preset.bg ? 'border-blue-600 bg-blue-50/20 dark:bg-blue-950/20 ring-1 ring-blue-500/20' : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-700 dark:text-slate-300'
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
							<input type="color" :value="canvasBgColor" @change="emit('update:canvasBgColor', ($event.target as HTMLInputElement).value); emit('update-settings')" class="w-8 h-8 rounded border border-slate-300 dark:border-slate-700 cursor-pointer p-0 bg-transparent shrink-0" />
							<span class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300 uppercase">{{ canvasBgColor }}</span>
						</div>
					</div>
				</div>

				<!-- Canvas Text Color Picker -->
				<div class="space-y-3 pt-4 border-t border-slate-200 dark:border-slate-800">
					<h4 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Default Text Color</h4>
					<div class="flex flex-wrap gap-2">
						<button v-for="c in ['#111827', '#4b5563', '#9ca3af', '#f9fafb']" :key="c"
							@click="updateText(c)"
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
							<input type="color" :value="canvasTextColor" @change="emit('update:canvasTextColor', ($event.target as HTMLInputElement).value); emit('update-settings')" class="w-8 h-8 rounded border border-slate-300 dark:border-slate-700 cursor-pointer p-0 bg-transparent shrink-0" />
							<span class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300 uppercase">{{ canvasTextColor }}</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Sticky Bottom Buttons Container -->
		<EditorToolbar
			:processing="processing"
			:disable-save="disableSave"
			@continue="emit('show-publish-modal')"
			@save-draft="emit('save-draft')"
			@preview="emit('preview')"
			@cancel="emit('cancel')"
			@discard-draft="emit('discard-draft')"
		/>
	</aside>
</template>
