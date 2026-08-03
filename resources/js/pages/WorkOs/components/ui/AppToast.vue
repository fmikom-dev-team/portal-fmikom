<script setup lang="ts">
import { toastState } from "../../composables/useWorkOs";
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-from-class="translate-y-2 opacity-0 scale-95"
            enter-active-class="transition-all duration-200 ease-out"
            leave-to-class="opacity-0 scale-95"
            leave-active-class="transition-opacity duration-150"
        >
            <div
                v-if="toastState.show"
                role="status"
                aria-live="polite"
                :class="[
                    'fixed bottom-5 right-5 z-[9999] flex items-center gap-3 px-4 py-3 rounded-2xl border text-[13px] font-semibold max-w-sm shadow-xl backdrop-blur-xl',
                    toastState.type === 'error'   ? 'bg-rose-50/95 dark:bg-zinc-900/95 border-rose-200 dark:border-rose-900/50 text-rose-800 dark:text-rose-200' :
                    toastState.type === 'warning' ? 'bg-amber-50/95 dark:bg-zinc-900/95 border-amber-200 dark:border-amber-900/50 text-amber-800 dark:text-amber-200' :
                    toastState.type === 'info'    ? 'bg-sky-50/95 dark:bg-zinc-900/95 border-sky-200 dark:border-sky-900/50 text-sky-800 dark:text-sky-200' :
                                                   'bg-white/95 dark:bg-zinc-900/95 border-slate-200/90 dark:border-zinc-800 text-slate-800 dark:text-zinc-100 shadow-slate-900/10',
                ]"
                style="font-family: var(--wos-font, system-ui, sans-serif)"
            >
                <div :class="[
                    'w-2 h-2 rounded-full shrink-0',
                    toastState.type === 'error'   ? 'bg-rose-500' :
                    toastState.type === 'warning' ? 'bg-amber-500' :
                    toastState.type === 'info'    ? 'bg-sky-500' :
                                                   'bg-emerald-500',
                ]" aria-hidden="true" />
                <span class="flex-1 leading-snug">{{ toastState.msg }}</span>
            </div>
        </Transition>
    </Teleport>
</template>
