<script setup lang="ts">
import { Info } from "lucide-vue-next";
import Modal from "../../ui/Modal.vue";

defineProps<{
	show: boolean;
	title: string;
	message: string;
}>();

defineEmits<(e: "close") => void>();
</script>

<template>
	<!-- Premium Modern Warning Modal Alert -->
	<Modal :show="show" :title="title" maxWidth="sm" @close="$emit('close')">
		<div class="flex flex-col items-center gap-4 text-center p-4">
			<div class="w-12 h-12 rounded-full bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900 flex items-center justify-center text-amber-600 dark:text-amber-400">
				<Info class="w-6 h-6 animate-pulse" />
			</div>
			<h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Warning</h3>
			<p class="text-xs text-slate-500 dark:text-slate-400 font-semibold leading-relaxed">
				{{ message }}
			</p>
		</div>
		<template #footer>
			<button 
				type="button"
				@click="$emit('close')"
				class="w-full h-11 bg-slate-900 dark:bg-white text-white dark:text-slate-950 text-xs font-black uppercase tracking-wider rounded-xl cursor-pointer shadow-xs hover:bg-slate-800 dark:hover:bg-slate-100"
			>
				Acknowledge
			</button>
		</template>
	</Modal>
</template>
