<script setup lang="ts">
import { computed, watch } from 'vue';
import { AlertTriangle, Info, Loader2 } from 'lucide-vue-next';

interface Props {
    open: boolean;
    title?: string;
    description?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    variant?: 'danger' | 'warning' | 'info';
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Apakah Anda yakin?',
    description: 'Tindakan ini tidak dapat dibatalkan.',
    confirmLabel: 'Ya, Lanjutkan',
    cancelLabel: 'Batal',
    variant: 'danger',
    loading: false,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    'confirm': [];
    'cancel': [];
}>();

const variantIcon = computed(() => {
    return props.variant === 'info' ? Info : AlertTriangle;
});

const iconClasses = computed(() => {
    const map: Record<NonNullable<Props['variant']>, string> = {
        danger: 'bg-red-50 text-red-500 dark:bg-red-950/40 dark:text-red-400',
        warning: 'bg-amber-50 text-[#EF9F27] dark:bg-amber-950/40 dark:text-[#FAC775]',
        info: 'bg-[#0C447C]/10 text-[#0C447C] dark:bg-[#85B7EB]/15 dark:text-[#85B7EB]',
    };
    return map[props.variant ?? 'danger'];
});

const confirmClasses = computed(() => {
    const map: Record<NonNullable<Props['variant']>, string> = {
        danger: 'bg-red-600 text-white hover:bg-red-700 focus-visible:ring-red-600/20',
        warning: 'bg-[#EF9F27] text-white hover:bg-[#d88d1f] focus-visible:ring-[#EF9F27]/20',
        info: 'bg-[#0C447C] text-white hover:bg-[#0a3968] focus-visible:ring-[#0C447C]/20',
    };
    return map[props.variant ?? 'danger'];
});

function handleConfirm() {
    emit('confirm');
}

function handleCancel() {
    emit('update:open', false);
    emit('cancel');
}

// Lock body scroll when open
watch(() => props.open, (val) => {
    if (val) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});
</script>

<template>
    <Teleport to="body">
        <Transition name="dialog-fade">
            <div
                v-if="open"
                class="fixed inset-0 z-[100] flex items-center justify-center"
            >
                <!-- Overlay -->
                <div
                    class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                    @click="handleCancel"
                />

                <!-- Dialog -->
                <div
                    class="relative z-10 w-full max-w-[420px] mx-4 rounded-2xl border border-slate-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <!-- Body -->
                    <div class="p-6 pb-4">
                        <!-- Icon -->
                        <div class="flex justify-center sm:justify-start mb-4">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl"
                                :class="iconClasses"
                            >
                                <component :is="variantIcon" class="h-6 w-6" />
                            </div>
                        </div>

                        <h2
                            class="text-lg font-bold text-slate-900 dark:text-white mb-1.5"
                            style="font-family: 'Poppins', sans-serif"
                        >
                            {{ title }}
                        </h2>
                        <p
                            class="text-[13px] leading-relaxed text-slate-500 dark:text-zinc-400"
                            style="font-family: 'Inter', sans-serif"
                        >
                            {{ description }}
                        </p>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-2.5 border-t border-slate-100 dark:border-zinc-800 px-6 py-4">
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-[13px] font-semibold text-slate-700 transition-colors hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                            :disabled="loading"
                            @click="handleCancel"
                        >
                            {{ cancelLabel }}
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-1.5 rounded-xl px-4 py-2 text-[13px] font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 disabled:pointer-events-none disabled:opacity-50"
                            :class="confirmClasses"
                            :disabled="loading"
                            @click="handleConfirm"
                        >
                            <Loader2 v-if="loading" class="h-4 w-4 animate-spin" />
                            {{ confirmLabel }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.dialog-fade-enter-active,
.dialog-fade-leave-active {
    transition: opacity 0.15s ease;
}
.dialog-fade-enter-from,
.dialog-fade-leave-to {
    opacity: 0;
}
</style>
