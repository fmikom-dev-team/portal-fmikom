<script setup lang="ts">
import { X, AlertTriangle, Info } from 'lucide-vue-next';

defineProps<{
    show: boolean;
    title?: string;
    message?: string;
    confirmText?: string;
    cancelText?: string;
    processing?: boolean;
    variant?: 'danger' | 'warning' | 'info';
}>();

const emit = defineEmits<{
    confirm: [];
    cancel: [];
}>();
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
                @click.self="emit('cancel')"
            >
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="scale-95 translate-y-4 opacity-0"
                    enter-to-class="scale-100 translate-y-0 opacity-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="scale-100 translate-y-0 opacity-100"
                    leave-to-class="scale-95 translate-y-4 opacity-0"
                >
                    <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-2xl p-6 overflow-hidden">
                        <!-- Header -->
                        <div class="flex items-start gap-4">
                            <div
                                class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-full"
                                :class="{
                                    'bg-red-50 dark:bg-red-950/30': variant === 'danger' || !variant,
                                    'bg-amber-50 dark:bg-amber-950/30': variant === 'warning',
                                    'bg-blue-50 dark:bg-blue-950/30': variant === 'info',
                                }"
                            >
                                <AlertTriangle
                                    v-if="variant === 'danger' || variant === 'warning' || !variant"
                                    class="w-5 h-5"
                                    :class="variant === 'warning' ? 'text-amber-550 dark:text-amber-400' : 'text-red-500 dark:text-red-400'"
                                />
                                <Info
                                    v-else-if="variant === 'info'"
                                    class="w-5 h-5 text-blue-500 dark:text-blue-400"
                                />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-[16px] font-bold text-slate-800 dark:text-white leading-tight">
                                    {{ title || 'Konfirmasi Hapus' }}
                                </h3>
                                <p class="mt-2 text-[13px] text-slate-500 dark:text-slate-400 leading-relaxed font-medium">
                                    {{ message || 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.' }}
                                </p>
                            </div>
                            <button
                                @click="emit('cancel')"
                                class="text-slate-400 hover:text-slate-650 dark:hover:text-slate-200 transition-colors duration-150"
                                aria-label="Tutup"
                            >
                                <X class="w-5 h-5" />
                            </button>
                        </div>

                        <!-- Actions -->
                        <div class="mt-6 flex items-center justify-end gap-3">
                            <button
                                @click="emit('cancel')"
                                :disabled="processing"
                                class="px-4 py-2.5 text-[12px] font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded-xl border border-slate-200 dark:border-slate-600 transition-colors disabled:opacity-50"
                            >
                                {{ cancelText || 'Batal' }}
                            </button>
                            <button
                                @click="emit('confirm')"
                                :disabled="processing"
                                class="px-4 py-2.5 text-[12px] font-bold rounded-xl transition-all duration-155 disabled:opacity-50 flex items-center gap-2 active:scale-95"
                                :class="{
                                    'bg-red-600 hover:bg-red-700 text-white shadow-lg shadow-red-500/10': variant === 'danger' || !variant,
                                    'bg-amber-600 hover:bg-amber-700 text-white shadow-lg shadow-amber-500/10': variant === 'warning',
                                    'bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-500/10': variant === 'info',
                                }"
                            >
                                <span v-if="processing" class="flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    Memproses...
                                </span>
                                <span v-else>{{ confirmText || 'Hapus' }}</span>
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
