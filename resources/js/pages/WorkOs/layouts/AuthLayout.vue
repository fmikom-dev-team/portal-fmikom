<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import AppToast from "../components/ui/AppToast.vue";

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
                <div class="w-12 h-12 rounded-xl bg-gray-900 flex items-center justify-center shadow-lg dark:shadow-none">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>
            <h2 class="mt-6 text-center text-[24px] font-bold tracking-tight text-gray-900 dark:text-zinc-100">
                <slot name="title">Sign in to your account</slot>
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                <slot name="subtitle">WorkOS Identity Platform</slot>
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white dark:bg-zinc-900 py-8 px-4 shadow-sm border border-gray-100 dark:border-zinc-800 sm:rounded-2xl sm:px-10 dark:shadow-none">
                <slot />
            </div>
        </div>

        <AppToast />
    </div>
</template>
