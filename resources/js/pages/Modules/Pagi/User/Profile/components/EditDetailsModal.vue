<script setup lang="ts">
import { Loader2, MapPin } from "lucide-vue-next";
import Modal from "../../ui/Modal.vue";

defineProps<{
	show: boolean;
	form: any;
}>();

const emit = defineEmits(["close", "submit"]);
</script>

<template>
	<Modal :show="show" title="Update Profile Details" maxWidth="md" @close="emit('close')">
		<form @submit.prevent="emit('submit')" class="space-y-6">
			<div>
				<label for="modal_name" class="block text-[11px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Name</label>
				<input 
					id="modal_name" 
					v-model="form.name" 
					type="text" 
					placeholder="Your name"
					class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-455 focus:outline-hidden focus:ring-1 focus:ring-slate-800 dark:focus:ring-slate-200 focus:border-slate-800 dark:focus:border-slate-200 transition-all shadow-2xs"
				/>
			</div>
			<div>
				<label for="modal_role_title" class="block text-[11px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Creative Role / Headline</label>
				<input 
					id="modal_role_title" 
					v-model="form.role_title" 
					type="text" 
					placeholder="e.g. Mahasiswa, UI/UX Designer"
					class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-455 focus:outline-hidden focus:ring-1 focus:ring-slate-800 dark:focus:ring-slate-200 focus:border-slate-800 dark:focus:border-slate-200 transition-all shadow-2xs"
				/>
			</div>
			<div>
				<label for="modal_location" class="block text-[11px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Location</label>
				<div class="relative flex items-center">
					<span class="absolute left-4 text-slate-400"><MapPin class="w-3.5 h-3.5" /></span>
					<input 
						id="modal_location" 
						v-model="form.location" 
						type="text" 
						placeholder="e.g. Purwokerto, Indonesia"
						class="w-full h-11 pl-10 pr-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-455 focus:outline-hidden focus:ring-1 focus:ring-slate-800 dark:focus:ring-slate-200 focus:border-slate-800 dark:focus:border-slate-200 transition-all shadow-2xs"
					/>
				</div>
			</div>
			<div>
				<label for="modal_tanggal_lahir" class="block text-[11px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Birth Date</label>
				<input 
					id="modal_tanggal_lahir" 
					v-model="form.tanggal_lahir" 
					type="date" 
					class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent text-xs font-semibold text-slate-800 dark:text-white focus:outline-hidden focus:ring-1 focus:ring-slate-800 dark:focus:ring-slate-200 focus:border-slate-800 dark:focus:border-slate-200 transition-all shadow-2xs"
				/>
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
