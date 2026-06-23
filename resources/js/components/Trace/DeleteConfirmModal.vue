<script setup lang="ts">
import { X, AlertTriangle } from 'lucide-vue-next';

defineProps<{
    show: boolean;
    title?: string;
    message?: string;
    confirmText?: string;
    cancelText?: string;
    processing?: boolean;
    variant?: 'danger' | 'warning';
}>();

const emit = defineEmits<{
    confirm: [];
    cancel: [];
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
                v-if="show"
                class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
                @click.self="emit('cancel')"
            >
                <div class="w-full max-w-md rounded-2xl bg-gray-800 border border-white/10 shadow-2xl p-6">
                    <!-- Header -->
                    <div class="flex items-start gap-4">
                        <div
                            class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-full"
                            :class="variant === 'warning' ? 'bg-amber-500/20' : 'bg-red-500/20'"
                        >
                            <AlertTriangle
                                class="w-5 h-5"
                                :class="variant === 'warning' ? 'text-amber-400' : 'text-red-400'"
                            />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-semibold text-white">
                                {{ title || 'Konfirmasi' }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-400">
                                {{ message || 'Apakah Anda yakin ingin melanjutkan?' }}
                            </p>
                        </div>
                        <button
                            @click="emit('cancel')"
                            class="text-gray-400 hover:text-white transition-colors"
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
                            class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-white rounded-lg border border-white/10 hover:bg-white/5 transition-colors disabled:opacity-50"
                        >
                            {{ cancelText || 'Batal' }}
                        </button>
                        <button
                            @click="emit('confirm')"
                            :disabled="processing"
                            class="px-4 py-2 text-sm font-medium rounded-lg transition-colors disabled:opacity-50"
                            :class="
                                variant === 'warning'
                                    ? 'bg-amber-600 hover:bg-amber-700 text-white'
                                    : 'bg-red-600 hover:bg-red-700 text-white'
                            "
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
            </div>
        </Transition>
    </Teleport>
</template>
