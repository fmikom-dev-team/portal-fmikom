<script setup lang="ts">
import { Link, router } from "@inertiajs/vue3";
import {
	AlertCircle,
	ArrowRight,
	CheckCircle,
	ChevronRight,
	Circle,
	FileEdit,
	Image,
	Sparkles,
	X,
} from "lucide-vue-next";
import { computed } from "vue";

const props = defineProps<{
	show: boolean;
	completenessPercentage: number;
	completenessItems: Array<{
		name: string;
		completed: boolean;
		weight: number;
	}>;
}>();

const emit = defineEmits<{
	(e: "close"): void;
	(e: "completeSettings"): void;
}>();

const isFullyComplete = computed(() => props.completenessPercentage >= 100);

const incompleteItems = computed(() =>
	props.completenessItems.filter((i) => !i.completed),
);

const handleNavigateCaseStudy = () => {
	if (!isFullyComplete.value) return;
	emit("close");
	router.visit("/pagi/editor");
};

const handleNavigateShowcase = () => {
	if (!isFullyComplete.value) return;
	emit("close");
	router.visit("/pagi/editor?type=showcase");
};
</script>

<template>
	<Teleport to="body">
		<Transition
			enter-active-class="transition-all duration-300 ease-out"
			enter-from-class="opacity-0"
			enter-to-class="opacity-100"
			leave-active-class="transition-all duration-200 ease-in"
			leave-from-class="opacity-100"
			leave-to-class="opacity-0"
		>
			<div
				v-if="show"
				class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-950/60 backdrop-blur-sm p-0 sm:p-4"
				@click.self="emit('close')"
			>
				<Transition
					enter-active-class="transition-all duration-300 ease-out"
					enter-from-class="opacity-0 translate-y-8 sm:scale-95"
					enter-to-class="opacity-100 translate-y-0 sm:scale-100"
					leave-active-class="transition-all duration-200 ease-in"
					leave-from-class="opacity-100 translate-y-0 sm:scale-100"
					leave-to-class="opacity-0 translate-y-8 sm:scale-95"
				>
					<div
						class="w-full max-w-lg bg-white dark:bg-zinc-900 rounded-t-3xl sm:rounded-3xl border border-slate-200/80 dark:border-zinc-800 shadow-2xl overflow-hidden select-none"
					>
						<!-- Modal Header -->
						<div
							class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-zinc-800/80 bg-slate-50/50 dark:bg-zinc-950/40"
						>
							<div class="flex items-center gap-2.5">
								<div
									class="h-9 w-9 rounded-xl bg-indigo-600/10 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center"
								>
									<Sparkles class="w-5 h-5" />
								</div>
								<div>
									<h3 class="text-base font-extrabold text-slate-900 dark:text-white leading-tight">
										Publish New Work
									</h3>
									<p class="text-xs font-semibold text-slate-500 dark:text-zinc-400">
										Pilih format karya yang ingin dipublikasikan
									</p>
								</div>
							</div>
							<button
								@click="emit('close')"
								class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-white rounded-full transition-colors cursor-pointer border-none bg-transparent"
								aria-label="Tutup Modal"
							>
								<X class="w-5 h-5" />
							</button>
						</div>

						<div class="p-6 space-y-5">
							<!-- Profile Completeness Warning (If < 100%) -->
							<div
								v-if="!isFullyComplete"
								class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-900/50 space-y-3"
							>
								<div class="flex items-start gap-3">
									<AlertCircle class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
									<div class="space-y-1">
										<h4 class="text-xs font-extrabold text-amber-900 dark:text-amber-200">
											Lengkapi Profil Anda ({{ completenessPercentage }}% Selesai)
										</h4>
										<p class="text-[11px] text-amber-700 dark:text-amber-300/90 leading-relaxed font-medium">
											Untuk mempublikasikan karya di PAGI, profil Mahasiswa wajib 100% lengkap agar portofolio Anda terverifikasi dengan baik.
										</p>
									</div>
								</div>

								<!-- Missing checklist pill list -->
								<div class="flex flex-wrap gap-1.5 pt-1">
									<span
										v-for="item in incompleteItems"
										:key="item.name"
										class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-100/80 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200 text-[10px] font-bold"
									>
										<Circle class="w-3 h-3 text-amber-500" />
										{{ item.name }} (+{{ item.weight }}%)
									</span>
								</div>

								<button
									@click="emit('completeSettings'); emit('close')"
									class="w-full flex items-center justify-center gap-2 py-2.5 px-4 bg-amber-600 hover:bg-amber-700 text-white font-black text-xs rounded-xl shadow-xs transition-all cursor-pointer border-none mt-2 active:scale-98"
								>
									<span>Lengkapi Profil Sekarang</span>
									<ArrowRight class="w-4 h-4" />
								</button>
							</div>

							<!-- Publication Options Grid -->
							<div class="space-y-3">
								<!-- Option 1: Create Case Study -->
								<button
									@click="handleNavigateCaseStudy"
									:disabled="!isFullyComplete"
									class="w-full group p-4 rounded-2xl border transition-all text-left flex items-center gap-4 cursor-pointer relative overflow-hidden"
									:class="
										isFullyComplete
											? 'border-slate-200 dark:border-zinc-800 bg-slate-50/60 dark:bg-zinc-800/40 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/30 hover:border-indigo-300 dark:hover:border-indigo-700 shadow-xs'
											: 'border-slate-200/60 dark:border-zinc-800/40 bg-slate-100/50 dark:bg-zinc-900/30 opacity-60 cursor-not-allowed'
									"
								>
									<div
										class="h-12 w-12 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-300 group-hover:scale-105"
										:class="
											isFullyComplete
												? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20'
												: 'bg-slate-300 dark:bg-zinc-700 text-slate-500 dark:text-zinc-400'
										"
									>
										<FileEdit class="w-6 h-6" />
									</div>

									<div class="flex-1 min-w-0">
										<div class="flex items-center gap-2">
											<span class="text-sm font-black text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
												Create Case Study
											</span>
											<span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300">
												Rekomendasi
											</span>
										</div>
										<p class="text-xs text-slate-500 dark:text-zinc-400 font-medium mt-0.5 line-clamp-2">
											Tulis studi kasus portofolio lengkap dengan dokumentasi, proses desain, dan gambar visual.
										</p>
									</div>

									<ChevronRight
										class="w-5 h-5 text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 group-hover:translate-x-1 transition-all shrink-0"
									/>
								</button>

								<!-- Option 2: Quick Showcase -->
								<button
									@click="handleNavigateShowcase"
									:disabled="!isFullyComplete"
									class="w-full group p-4 rounded-2xl border transition-all text-left flex items-center gap-4 cursor-pointer relative overflow-hidden"
									:class="
										isFullyComplete
											? 'border-slate-200 dark:border-zinc-800 bg-slate-50/60 dark:bg-zinc-800/40 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/30 hover:border-indigo-300 dark:hover:border-indigo-700 shadow-xs'
											: 'border-slate-200/60 dark:border-zinc-800/40 bg-slate-100/50 dark:bg-zinc-900/30 opacity-60 cursor-not-allowed'
									"
								>
									<div
										class="h-12 w-12 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-300 group-hover:scale-105"
										:class="
											isFullyComplete
												? 'bg-slate-900 dark:bg-zinc-100 text-white dark:text-slate-950 shadow-md'
												: 'bg-slate-300 dark:bg-zinc-700 text-slate-500 dark:text-zinc-400'
										"
									>
										<Image class="w-6 h-6" />
									</div>

									<div class="flex-1 min-w-0">
										<span class="text-sm font-black text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
											Quick Project Showcase
										</span>
										<p class="text-xs text-slate-500 dark:text-zinc-400 font-medium mt-0.5 line-clamp-2">
											Unggah cepat cuplikan visual atau karya desain tunggal ke Galeri PAGI.
										</p>
									</div>

									<ChevronRight
										class="w-5 h-5 text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 group-hover:translate-x-1 transition-all shrink-0"
									/>
								</button>
							</div>
						</div>
					</div>
				</Transition>
			</div>
		</Transition>
	</Teleport>
</template>
