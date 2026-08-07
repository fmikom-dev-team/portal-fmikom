<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { ArrowRight, Sparkles } from "lucide-vue-next";

defineProps<{
	status?: {
		isVisible: boolean;
		hasUsername: boolean;
		hasPublishedWork: boolean;
		reason: string;
	};
}>();
</script>

<template>
	<div 
		v-if="status && !status.isVisible && status.reason" 
		class="relative overflow-hidden rounded-2xl border border-amber-500/30 dark:border-amber-500/20 bg-linear-to-r from-amber-50/90 via-orange-50/70 to-indigo-50/90 dark:from-amber-950/40 dark:via-zinc-900 dark:to-indigo-950/40 p-4 sm:p-5 shadow-xs transition-all select-none mb-6"
	>
		<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
			<div class="flex items-start gap-3.5 min-w-0">
				<div class="h-10 w-10 rounded-xl bg-amber-500/10 dark:bg-amber-400/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 mt-0.5 sm:mt-0">
					<Sparkles class="h-5 w-5" />
				</div>
				<div class="flex flex-col min-w-0">
					<h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-amber-100 flex items-center gap-1.5">
						Profil Anda Belum Tampil di Direktori People
					</h4>
					<p class="text-[11px] sm:text-xs text-slate-600 dark:text-zinc-300 mt-0.5 leading-relaxed">
						{{ status.reason }}
					</p>
				</div>
			</div>
			<div class="shrink-0 w-full sm:w-auto">
				<Link 
					v-if="!status.hasUsername" 
					href="/pagi/profile" 
					class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 text-xs font-bold transition-all shadow-xs w-full sm:w-auto"
				>
					Lengkapi Username <ArrowRight class="h-3.5 w-3.5" />
				</Link>
			</div>
		</div>
	</div>
</template>
