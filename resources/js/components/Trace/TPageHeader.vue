<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';
import type { Component } from 'vue';

interface Props {
    title: string;
    description?: string;
    icon?: Component;
    class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();
</script>

<template>
    <div
        :class="
            cn(
                'flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between',
                props.class,
            )
        "
    >
        <div class="flex items-center gap-3">
            <div
                v-if="icon"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#0C447C]/10 transition-colors duration-200 hover:bg-[#0C447C]/20 dark:bg-[#85B7EB]/10 dark:hover:bg-[#85B7EB]/20"
            >
                <component
                    :is="icon"
                    class="h-5 w-5 text-[#0C447C] transition-transform duration-200 group-hover:scale-110 dark:text-[#85B7EB]"
                />
            </div>
            <div>
                <h1
                    class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white"
                    style="font-family: 'Poppins', sans-serif"
                >
                    {{ title }}
                </h1>
                <p
                    v-if="description"
                    class="mt-0.5 text-sm text-slate-500 dark:text-zinc-400"
                    style="font-family: 'Inter', sans-serif"
                >
                    {{ description }}
                </p>
            </div>
        </div>
        <div class="mt-3 flex items-center gap-2 sm:mt-0">
            <slot name="actions" />
        </div>
    </div>
</template>
