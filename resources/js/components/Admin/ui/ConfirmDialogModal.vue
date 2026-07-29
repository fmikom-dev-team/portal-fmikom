<script setup lang="ts">
import { AlertTriangle, Info, Trash2, X } from "lucide-vue-next";

const props = withDefaults(
	defineProps<{
		isOpen: boolean;
		title?: string;
		message?: string;
		confirmText?: string;
		cancelText?: string;
		variant?: "danger" | "warning" | "info";
		isLoading?: boolean;
	}>(),
	{
		title: "Konfirmasi Tindakan",
		message: "Apakah Anda yakin ingin melanjutkan tindakan ini?",
		confirmText: "Ya, Lanjutkan",
		cancelText: "Batal",
		variant: "danger",
		isLoading: false,
	}
);

const emit = defineEmits<{
	close: [];
	confirm: [];
}>();
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isOpen"
                class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-900/60 dark:bg-black/80 backdrop-blur-md"
                @click.self="emit('close')"
            >
                <Transition
                    enter-active-class="transition duration-250 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-4"
                >
                    <div
                        v-if="isOpen"
                        class="w-full max-w-md bg-white dark:bg-zinc-900 rounded-3xl p-6 border border-slate-200/80 dark:border-zinc-800 shadow-2xl space-y-5 relative overflow-hidden"
                    >
                        <!-- Top Close Button -->
                        <button
                            @click="emit('close')"
                            class="absolute right-4 top-4 w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors"
                        >
                            <X class="w-4 h-4" />
                        </button>

                        <!-- Icon & Content Header -->
                        <div class="flex items-start gap-4">
                            <div
                                :class="[
                                    'w-12 h-12 rounded-2xl flex items-center justify-center shrink-0',
                                    variant === 'danger' ? 'bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400' : '',
                                    variant === 'warning' ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400' : '',
                                    variant === 'info' ? 'bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400' : '',
                                ]"
                            >
                                <Trash2 v-if="variant === 'danger'" class="w-6 h-6" />
                                <AlertTriangle v-else-if="variant === 'warning'" class="w-6 h-6" />
                                <Info v-else class="w-6 h-6" />
                            </div>

                            <div class="space-y-1 pr-6">
                                <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">
                                    {{ title }}
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-zinc-400 leading-relaxed">
                                    {{ message }}
                                </p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button
                                @click="emit('close')"
                                :disabled="isLoading"
                                class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-zinc-300 text-xs font-bold hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors disabled:opacity-50"
                            >
                                {{ cancelText }}
                            </button>
                            <button
                                @click="emit('confirm')"
                                :disabled="isLoading"
                                :class="[
                                    'px-5 py-2.5 rounded-xl text-xs font-bold text-white shadow-md transition-all flex items-center gap-2 disabled:opacity-50',
                                    variant === 'danger' ? 'bg-rose-600 hover:bg-rose-700 shadow-rose-200 dark:shadow-none' : '',
                                    variant === 'warning' ? 'bg-amber-600 hover:bg-amber-700 shadow-amber-200 dark:shadow-none' : '',
                                    variant === 'info' ? 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-200 dark:shadow-none' : '',
                                ]"
                            >
                                <svg v-if="isLoading" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ confirmText }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
