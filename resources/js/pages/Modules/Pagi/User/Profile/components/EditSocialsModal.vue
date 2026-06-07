<script setup lang="ts">
import {
	Github,
	Globe,
	Instagram,
	Linkedin,
	Loader2,
	Twitter,
} from "lucide-vue-next";
import Modal from "../../ui/Modal.vue";

defineProps<{
	show: boolean;
	form: any;
}>();

const emit = defineEmits(["close", "submit"]);
</script>

<template>
	<Modal :show="show" title="Update Social Connections" maxWidth="lg" @close="emit('close')">
		<form @submit.prevent="emit('submit')" class="space-y-6">
			<div class="space-y-4 text-left">
				<div class="flex items-center gap-3">
					<div class="w-10 h-10 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 flex items-center justify-center shrink-0 shadow-2xs">
						<Globe class="w-4 h-4 text-slate-500" />
					</div>
					<input 
						v-model="form.website" 
						type="url" 
						placeholder="https://myportfolio.com"
						class="flex-1 h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 transition-all shadow-2xs"
					/>
				</div>

				<div class="flex items-center gap-3">
					<div class="w-10 h-10 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 flex items-center justify-center shrink-0 shadow-2xs">
						<Linkedin class="w-4 h-4 text-slate-500" />
					</div>
					<input 
						v-model="form.linkedin" 
						type="text" 
						placeholder="linkedin-username"
						class="flex-1 h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 focus:border-slate-800 dark:focus:ring-slate-200 transition-all shadow-2xs"
					/>
				</div>

				<div class="flex items-center gap-3">
					<div class="w-10 h-10 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 flex items-center justify-center shrink-0 shadow-2xs">
						<Github class="w-4 h-4 text-slate-500" />
					</div>
					<input 
						v-model="form.github" 
						type="text" 
						placeholder="github-username"
						class="flex-1 h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 focus:border-slate-800 dark:focus:ring-slate-200 transition-all shadow-2xs"
					/>
				</div>

				<div class="flex items-center gap-3">
					<div class="w-10 h-10 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 flex items-center justify-center shrink-0 shadow-2xs">
						<Twitter class="w-4 h-4 text-slate-500" />
					</div>
					<input 
						v-model="form.twitter" 
						type="text" 
						placeholder="twitter-username"
						class="flex-1 h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 focus:border-slate-800 dark:focus:ring-slate-200 transition-all shadow-2xs"
					/>
				</div>
				
				<div class="flex items-center gap-3">
					<div class="w-10 h-10 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 flex items-center justify-center shrink-0 shadow-2xs">
						<Instagram class="w-4 h-4 text-slate-500" />
					</div>
					<input 
						v-model="form.instagram" 
						type="text" 
						placeholder="instagram-username"
						class="flex-1 h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 focus:border-slate-800 dark:focus:ring-slate-200 transition-all shadow-2xs"
					/>
				</div>
			</div>

			<!-- Actions -->
			<div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
				<button 
					type="button"
					@click="emit('close')"
					class="h-11 px-6 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors shadow-xs cursor-pointer"
				>
					Cancel
				</button>
				<button 
					type="submit"
					:disabled="form.processing"
					class="h-11 px-6 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-950 text-xs font-black uppercase tracking-wider hover:bg-slate-800 dark:hover:bg-slate-100 disabled:opacity-50 transition-all shadow-xs flex items-center gap-2 cursor-pointer"
				>
					<Loader2 v-if="form.processing" class="w-3.5 h-3.5 animate-spin" />
					Save changes
				</button>
			</div>
		</form>
	</Modal>
</template>
