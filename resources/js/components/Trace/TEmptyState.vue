<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';
import type { Component } from 'vue';

interface Props {
    icon?: Component;
    title?: string;
    description?: string;
    actionLabel?: string;
    actionHref?: string;
    class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Belum ada data',
});

defineEmits<{
    action: [];
}>();
</script>

<template>
    <div
        :class="
            cn(
                'flex items-center justify-center py-16',
                props.class,
            )
        "
    >
        <div class="t-empty-state-enter flex max-w-sm flex-col items-center text-center">
            <!-- Icon circle -->
            <div
                class="mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-[#85B7EB]/10 ring-1 ring-[#85B7EB]/20 transition-all duration-300 dark:bg-[#85B7EB]/10 dark:ring-[#85B7EB]/15"
            >
                <component
                    :is="icon"
                    v-if="icon"
                    class="h-7 w-7 text-[#0C447C] dark:text-[#85B7EB]"
                />
                <svg
                    v-else
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-7 w-7 text-[#0C447C] dark:text-[#85B7EB]"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                    />
                </svg>
            </div>

            <!-- Title -->
            <h3
                class="text-base font-medium text-slate-800 dark:text-zinc-200"
                style="font-family: 'Poppins', sans-serif"
            >
                {{ title }}
            </h3>

            <!-- Description -->
            <p
                v-if="description"
                class="mt-1.5 text-sm leading-relaxed text-slate-500 dark:text-zinc-400"
                style="font-family: 'Inter', sans-serif"
            >
                {{ description }}
            </p>

            <!-- Action button -->
            <a
                v-if="actionLabel && actionHref"
                :href="actionHref"
                class="mt-5 inline-flex items-center gap-2 rounded-lg bg-[#0C447C] px-4 py-2 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-[#0C447C]/90 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#0C447C]/50 focus:ring-offset-2 dark:bg-[#85B7EB] dark:text-slate-900 dark:hover:bg-[#85B7EB]/90 dark:focus:ring-[#85B7EB]/50 dark:focus:ring-offset-zinc-900"
                style="font-family: 'Inter', sans-serif"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                {{ actionLabel }}
            </a>
            <button
                v-else-if="actionLabel"
                type="button"
                class="mt-5 inline-flex items-center gap-2 rounded-lg bg-[#0C447C] px-4 py-2 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-[#0C447C]/90 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#0C447C]/50 focus:ring-offset-2 dark:bg-[#85B7EB] dark:text-slate-900 dark:hover:bg-[#85B7EB]/90 dark:focus:ring-[#85B7EB]/50 dark:focus:ring-offset-zinc-900"
                style="font-family: 'Inter', sans-serif"
                @click="$emit('action')"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                {{ actionLabel }}
            </button>
        </div>
    </div>
</template>

<style scoped>
@keyframes t-empty-fade-in {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.t-empty-state-enter {
    animation: t-empty-fade-in 0.4s ease-out both;
}
</style>
