<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import { toastState } from "../composables/useWorkOs";

defineProps<{
	title?: string;
}>();
</script>

<template>
    <Head>
        <title>{{ title ? `${title} – WorkOS` : 'WorkOS' }}</title>
    </Head>
    
    <div 
        class="min-h-screen bg-white dark:bg-zinc-900"
        style="font-family:'Inter',ui-sans-serif,system-ui,sans-serif"
    >
        <slot />

        <!-- ═══════════════ TOAST ═══════════════ -->
        <Teleport to="body">
            <Transition
                enter-from-class="translate-y-2 opacity-0"
                enter-active-class="transition-all duration-200 ease-out"
                leave-to-class="opacity-0"
                leave-active-class="transition-opacity duration-150"
            >
                <div
                    v-if="toastState.show"
                    :class="[
                        'fixed bottom-5 right-5 z-9999 flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border text-[13px] font-medium max-w-xs',
                        toastState.type === 'error'
                            ? 'bg-red-50 border-red-200 text-red-800'
                            : toastState.type === 'warning'
                                ? 'bg-amber-50 border-amber-200 text-amber-800'
                                : toastState.type === 'info'
                                    ? 'bg-blue-50 border-blue-200 text-blue-800'
                                    : 'bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-700 text-gray-900 dark:text-zinc-100',
                    ]"
                >
                    <div
                        :class="['w-1.5 h-1.5 rounded-full shrink-0',
                            toastState.type === 'error' ? 'bg-red-500'
                            : toastState.type === 'warning' ? 'bg-amber-500'
                            : toastState.type === 'info' ? 'bg-blue-500'
                            : 'bg-emerald-500']"
                    />
                    {{ toastState.msg }}
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
<style>
/* Premium card shadow — multi-layer elevation (no hard border) */
.wos-card,
[class*="ring-1"][class*="ring-gray-900"] {
    box-shadow:
        0 0 0 1px rgba(0, 0, 0, 0.04),
        0 1px 3px rgba(0, 0, 0, 0.04),
        0 4px 12px rgba(0, 0, 0, 0.04),
        0 16px 40px rgba(0, 0, 0, 0.05);
}
</style>
