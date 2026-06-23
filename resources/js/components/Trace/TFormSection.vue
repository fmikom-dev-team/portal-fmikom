<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

interface Props {
    title: string;
    description?: string;
    class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();
</script>

<template>
    <div
        :class="
            cn(
                'overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-shadow duration-200 dark:border-zinc-700/60 dark:bg-zinc-800/80',
                props.class,
            )
        "
    >
        <!-- Header -->
        <div class="px-6 py-5">
            <h2
                class="text-base font-semibold text-slate-900 dark:text-white"
                style="font-family: 'Poppins', sans-serif"
            >
                {{ title }}
            </h2>
            <p
                v-if="description"
                class="mt-1 text-sm leading-relaxed text-slate-500 dark:text-zinc-400"
                style="font-family: 'Inter', sans-serif"
            >
                {{ description }}
            </p>
        </div>

        <!-- Separator -->
        <div class="border-t border-slate-200 dark:border-zinc-700/60" />

        <!-- Content -->
        <div class="px-6 py-5">
            <slot />
        </div>
    </div>
</template>
