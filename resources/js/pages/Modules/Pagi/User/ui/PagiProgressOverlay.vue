<script setup lang="ts">
import { DownloadCloud, Loader2, UploadCloud } from "lucide-vue-next";
import { computed } from "vue";
import { usePagiProgress } from "../../shared/composables/usePagiProgress";
import Progress from "./Progress.vue";

const { show, title, percent, mode, statusText, isProcessing } =
	usePagiProgress();

const displayPercent = computed(() => Math.round(percent.value));
</script>

<template>
	<Teleport to="body">
		<Transition
			enter-active-class="transition duration-300 ease-out"
			enter-from-class="translate-y-6 opacity-0 scale-95"
			enter-to-class="translate-y-0 opacity-100 scale-100"
			leave-active-class="transition duration-200 ease-in"
			leave-from-class="translate-y-0 opacity-100 scale-100"
			leave-to-class="translate-y-6 opacity-0 scale-95"
		>
			<div 
				v-if="show" 
				class="fixed bottom-6 right-6 z-20020 bg-slate-900/95 dark:bg-zinc-950/95 border border-slate-800 dark:border-zinc-800/80 shadow-[0_12px_40px_rgba(0,0,0,0.5)] rounded-2xl p-4.5 w-80 backdrop-blur-md select-none flex flex-col gap-3"
			>
				<div class="flex items-center gap-3">
					<!-- Animated Icon Container -->
					<div 
						class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 shadow-inner"
						:class="[
							mode === 'download' 
								? 'bg-sky-500/10 text-sky-400' 
								: mode === 'publish'
									? 'bg-emerald-500/10 text-emerald-400'
									: 'bg-indigo-500/10 text-indigo-400'
						]"
					>
						<!-- Server Processing or general loading -->
						<Loader2 
							v-if="isProcessing" 
							class="animate-spin w-5 h-5" 
						/>
						<UploadCloud 
							v-else-if="mode === 'upload' || mode === 'publish'" 
							class="w-5 h-5 animate-pulse" 
						/>
						<DownloadCloud 
							v-else 
							class="w-5 h-5 animate-bounce" 
						/>
					</div>

					<!-- Texts -->
					<div class="flex-1 min-w-0 text-left">
						<h4 class="text-xs font-black text-white truncate leading-tight">
							{{ title }}
						</h4>
						<p 
							class="text-[9px] font-bold text-zinc-400 truncate mt-0.5"
							:class="{ 'animate-pulse text-indigo-300': isProcessing }"
						>
							{{ statusText }}
						</p>
					</div>

					<!-- Percentage -->
					<span 
						class="text-xs font-black tabular-nums"
						:class="[
							mode === 'download' 
								? 'text-sky-400' 
								: mode === 'publish'
									? 'text-emerald-400'
									: 'text-indigo-400'
						]"
					>
						{{ displayPercent }}%
					</span>
				</div>

				<!-- Progress Bar -->
				<Progress 
					:value="percent" 
					className="w-full h-1.5 bg-slate-800" 
					:indicatorClassName="
						mode === 'download' 
							? 'bg-sky-500' 
							: mode === 'publish'
								? 'bg-emerald-500'
								: 'bg-indigo-500'
					"
				/>
			</div>
		</Transition>
	</Teleport>
</template>
