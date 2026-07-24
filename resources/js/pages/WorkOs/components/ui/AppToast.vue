<script setup lang="ts">
import { toastState } from "../../composables/useWorkOs";
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-from-class="translate-y-1 opacity-0"
            enter-active-class="transition-all duration-200 ease-out"
            leave-to-class="opacity-0"
            leave-active-class="transition-opacity duration-150"
        >
            <div
                v-if="toastState.show"
                role="status"
                aria-live="polite"
                :class="[
                    'fixed bottom-5 right-5 z-[9999] flex items-center gap-2.5 px-4 py-3 rounded-xl border text-[13px] font-medium max-w-sm shadow-lg',
                    toastState.type === 'error'   ? 'bg-white dark:bg-zinc-900 border-[#fecaca] text-[#991b1b]' :
                    toastState.type === 'warning' ? 'bg-white dark:bg-zinc-900 border-[#fde68a] text-[#92400e]' :
                    toastState.type === 'info'    ? 'bg-white dark:bg-zinc-900 border-[#bfdbfe] text-[#1e40af]' :
                                                   'bg-white dark:bg-zinc-900 border-[#e5e7eb] dark:border-zinc-800 text-[#111827] dark:text-zinc-100',
                ]"
                style="font-family: var(--wos-font)"
            >
                <div :class="[
                    'w-1.5 h-1.5 rounded-full shrink-0',
                    toastState.type === 'error'   ? 'bg-[#ef4444]' :
                    toastState.type === 'warning' ? 'bg-[#f59e0b]' :
                    toastState.type === 'info'    ? 'bg-[#3b82f6]' :
                                                   'bg-[#10b981]',
                ]" aria-hidden="true" />
                <span class="flex-1">{{ toastState.msg }}</span>
            </div>
        </Transition>
    </Teleport>
</template>
