<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import {
    AlertCircle,
    CheckCircle2,
    Info,
    TriangleAlert,
} from 'lucide-vue-next';

type FastFlash = {
    success?: string;
    error?: string;
    warning?: string;
    info?: string;
};

type PageProps = {
    fast_flash?: FastFlash;
};

const page = usePage<PageProps>();
const message = ref('');
const variant = ref<'success' | 'error' | 'warning' | 'info'>('success');
let timer: number | null = null;

const icon = computed(() => {
    if (variant.value === 'success') return CheckCircle2;
    if (variant.value === 'error') return AlertCircle;
    if (variant.value === 'warning') return TriangleAlert;
    return Info;
});

function clearTimer() {
    if (timer !== null) {
        window.clearTimeout(timer);
        timer = null;
    }
}

async function show(nextMessage: string, nextVariant: 'success' | 'error' | 'warning' | 'info') {
    clearTimer();
    message.value = '';
    await nextTick();
    variant.value = nextVariant;
    message.value = nextMessage;
    timer = window.setTimeout(() => {
        message.value = '';
        timer = null;
    }, 6000);
}

watch(
    () => page.props.fast_flash,
    (flash) => {
        if (!flash) return;
        if (typeof flash.success === 'string' && flash.success.trim()) {
            void show(flash.success, 'success');
            return;
        }
        if (typeof flash.error === 'string' && flash.error.trim()) {
            void show(flash.error, 'error');
            return;
        }
        if (typeof flash.warning === 'string' && flash.warning.trim()) {
            void show(flash.warning, 'warning');
            return;
        }
        if (typeof flash.info === 'string' && flash.info.trim()) {
            void show(flash.info, 'info');
        }
    },
    { immediate: true, deep: true },
);

onBeforeUnmount(() => {
    clearTimer();
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-3 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-3 opacity-0"
        >
            <div
                v-if="message"
                class="fixed top-5 left-1/2 z-[99999] w-[calc(100%-2rem)] max-w-sm -translate-x-1/2"
            >
                <div
                    class="pointer-events-auto flex items-center gap-2.5 rounded-xl border px-4 py-3 shadow-lg"
                    :class="{
                        'border-blue-200 bg-blue-50 text-blue-800': variant === 'success',
                        'border-red-200 bg-red-50 text-red-800': variant === 'error',
                        'border-amber-200 bg-amber-50 text-amber-800': variant === 'warning',
                        'border-sky-200 bg-sky-50 text-sky-800': variant === 'info',
                    }"
                    role="status"
                    aria-live="polite"
                >
                    <div
                        class="grid size-5 shrink-0 place-items-center"
                        :class="{
                            'text-blue-500': variant === 'success',
                            'text-red-500': variant === 'error',
                            'text-amber-600': variant === 'warning',
                            'text-sky-500': variant === 'info',
                        }"
                    >
                        <component :is="icon" class="size-5 shrink-0" />
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium">
                            {{ variant === 'success' ? 'Berhasil' : variant === 'error' ? 'Gagal' : variant === 'warning' ? 'Perhatian' : 'Info' }}
                        </p>
                        <p class="mt-1 text-sm leading-5">
                            {{ message }}
                        </p>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
