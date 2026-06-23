<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

interface Props {
    variant?: 'card' | 'text' | 'circle' | 'chart' | 'stat-card' | 'list-item';
    width?: string;
    height?: string;
    count?: number;
    class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'text',
    count: 1,
});
</script>

<template>
    <template v-for="i in count" :key="i">
        <!-- text -->
        <div
            v-if="variant === 'text'"
            :class="cn('t-skeleton h-3 rounded-md', props.class)"
            :style="{ width: width ?? '100%', height }"
        />

        <!-- circle -->
        <div
            v-else-if="variant === 'circle'"
            :class="cn('t-skeleton rounded-full shrink-0', props.class)"
            :style="{ width: width ?? '48px', height: height ?? width ?? '48px' }"
        />

        <!-- chart -->
        <div
            v-else-if="variant === 'chart'"
            :class="cn('t-skeleton rounded-2xl', props.class)"
            :style="{ width: width ?? '100%', height: height ?? '12rem' }"
        />

        <!-- stat-card: mimics TStatCard layout -->
        <div
            v-else-if="variant === 'stat-card'"
            :class="cn('rounded-2xl border border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5', props.class)"
        >
            <div class="flex items-start gap-4">
                <div class="t-skeleton h-11 w-11 shrink-0 rounded-xl" />
                <div class="flex-1 space-y-2.5">
                    <div class="t-skeleton h-3 w-20 rounded" />
                    <div class="t-skeleton h-7 w-28 rounded" />
                    <div class="t-skeleton h-3 w-32 rounded" />
                </div>
            </div>
        </div>

        <!-- list-item -->
        <div
            v-else-if="variant === 'list-item'"
            :class="cn('flex items-center gap-3 py-2', props.class)"
        >
            <div class="t-skeleton h-9 w-9 rounded-full shrink-0" />
            <div class="flex-1 space-y-2">
                <div class="t-skeleton h-3 w-3/4 rounded" />
                <div class="t-skeleton h-2.5 w-1/2 rounded" />
            </div>
        </div>

        <!-- card (generic) -->
        <div
            v-else
            :class="cn('t-skeleton rounded-2xl', props.class)"
            :style="{ width: width ?? '100%', height: height ?? '120px' }"
        />
    </template>
</template>

<style scoped>
.t-skeleton {
    background: linear-gradient(
        90deg,
        rgba(148, 163, 184, 0.08) 25%,
        rgba(148, 163, 184, 0.15) 50%,
        rgba(148, 163, 184, 0.08) 75%
    );
    background-size: 200% 100%;
    animation: t-skeleton-shimmer 1.5s ease-in-out infinite;
}

@keyframes t-skeleton-shimmer {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

.dark .t-skeleton {
    background: linear-gradient(
        90deg,
        rgba(255, 255, 255, 0.03) 25%,
        rgba(255, 255, 255, 0.08) 50%,
        rgba(255, 255, 255, 0.03) 75%
    );
    background-size: 200% 100%;
}
</style>
