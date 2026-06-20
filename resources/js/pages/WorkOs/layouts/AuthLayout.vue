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
        class="min-h-screen bg-[#fafafa] flex flex-col justify-center py-12 sm:px-6 lg:px-8"
        style="font-family:'Inter',ui-sans-serif,system-ui,sans-serif"
    >
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="flex justify-center">
                <div class="w-12 h-12 rounded-xl bg-gray-900 flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>
            <h2 class="mt-6 text-center text-[24px] font-bold tracking-tight text-gray-900">
                <slot name="title">Sign in to your account</slot>
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                <slot name="subtitle">WorkOS Identity Platform</slot>
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow-sm border border-gray-100 sm:rounded-2xl sm:px-10">
                <slot />
            </div>
        </div>

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
                        'fixed bottom-5 right-5 z-[9999] flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border text-[13px] font-medium max-w-xs',
                        toastState.type === 'error'
                            ? 'bg-red-50 border-red-200 text-red-800'
                            : toastState.type === 'warning'
                                ? 'bg-amber-50 border-amber-200 text-amber-800'
                                : toastState.type === 'info'
                                    ? 'bg-blue-50 border-blue-200 text-blue-800'
                                    : 'bg-white border-gray-200 text-gray-900',
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
