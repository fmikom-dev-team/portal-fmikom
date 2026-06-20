<script setup lang="ts">
defineProps<{
	processing?: boolean;
	disableSave?: boolean;
}>();

const emit = defineEmits<{
	(e: "continue"): void;
	(e: "save-draft"): void;
	(e: "preview"): void;
}>();
</script>

<template>
	<div class="absolute bottom-0 left-0 right-0 p-6 bg-white dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 flex flex-col gap-2 z-40">
		<button
			type="button"
			@click="emit('continue')"
			class="w-full rounded-xl bg-emerald-600 dark:bg-emerald-500 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-colors cursor-pointer"
		>
			Continue
		</button>
		<button
			type="button"
			@click="emit('save-draft')"
			:disabled="disableSave || processing"
			class="w-full rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
		>
			Save as Draft
		</button>
		<button
			type="button"
			@click="emit('preview')"
			class="w-full rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300 transition-colors cursor-pointer"
		>
			View Preview
		</button>
	</div>
</template>
