<script setup lang="ts">
import { computed } from 'vue';
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

interface Props {
    status: string;
    size?: 'sm' | 'md';
    variant?: 'solid' | 'outline';
    label?: string;
    class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
    size: 'sm',
    variant: 'outline',
});

/**
 * Status → label mapping (auto-resolved).
 * Custom label prop overrides this.
 */
const labelMap: Record<string, string> = {
    published: 'Published',
    draft: 'Draft',
    pending_review: 'Pending Review',
    pending: 'Pending',
    rejected: 'Rejected',
    closed: 'Closed',
    registered: 'Terdaftar',
    active: 'Aktif',
    inactive: 'Nonaktif',
    accepted: 'Diterima',
    cancelled: 'Dibatalkan',
    hadir: 'Hadir',
};

/**
 * Status → color palette mapping.
 * Each palette defines classes for outline and solid variants, plus a dot color.
 */
interface ColorPalette {
    outline: string;
    solid: string;
    dot: string;
}

const colorMap: Record<string, ColorPalette> = {
    // Emerald green — published, active, accepted, hadir
    emerald: {
        outline:
            'border-emerald-300 bg-emerald-50 text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-400',
        solid:
            'border-transparent bg-emerald-600 text-white dark:bg-emerald-500',
        dot: 'bg-emerald-500 dark:bg-emerald-400',
    },
    // Slate gray — draft
    slate: {
        outline:
            'border-slate-300 bg-slate-50 text-slate-600 dark:border-zinc-500/40 dark:bg-zinc-500/10 dark:text-zinc-400',
        solid:
            'border-transparent bg-slate-500 text-white dark:bg-zinc-500',
        dot: 'bg-slate-400 dark:bg-zinc-400',
    },
    // Amber — pending_review, pending
    amber: {
        outline:
            'border-[#EF9F27]/40 bg-[#EF9F27]/10 text-[#EF9F27] dark:border-[#FAC775]/40 dark:bg-[#EF9F27]/10 dark:text-[#FAC775]',
        solid:
            'border-transparent bg-[#EF9F27] text-white dark:bg-[#EF9F27]',
        dot: 'bg-[#EF9F27] dark:bg-[#FAC775]',
    },
    // Red — rejected, cancelled
    red: {
        outline:
            'border-red-300 bg-red-50 text-red-700 dark:border-red-500/40 dark:bg-red-500/10 dark:text-red-400',
        solid:
            'border-transparent bg-red-600 text-white dark:bg-red-500',
        dot: 'bg-red-500 dark:bg-red-400',
    },
    // Slate dark — closed, inactive
    slateDark: {
        outline:
            'border-slate-400 bg-slate-100 text-slate-700 dark:border-zinc-500/50 dark:bg-zinc-600/20 dark:text-zinc-300',
        solid:
            'border-transparent bg-slate-700 text-white dark:bg-zinc-600',
        dot: 'bg-slate-500 dark:bg-zinc-400',
    },
    // Primary blue — registered
    primary: {
        outline:
            'border-[#0C447C]/30 bg-[#0C447C]/10 text-[#0C447C] dark:border-[#85B7EB]/40 dark:bg-[#85B7EB]/10 dark:text-[#85B7EB]',
        solid:
            'border-transparent bg-[#0C447C] text-white dark:bg-[#85B7EB] dark:text-slate-900',
        dot: 'bg-[#0C447C] dark:bg-[#85B7EB]',
    },
};

/** Resolve status string → color palette key */
const statusToColor: Record<string, keyof typeof colorMap> = {
    published: 'emerald',
    active: 'emerald',
    accepted: 'emerald',
    hadir: 'emerald',
    draft: 'slate',
    pending_review: 'amber',
    pending: 'amber',
    rejected: 'red',
    cancelled: 'red',
    closed: 'slateDark',
    inactive: 'slateDark',
    registered: 'primary',
};

const resolvedLabel = computed(() => {
    if (props.label) return props.label;
    return labelMap[props.status] ?? props.status;
});

const palette = computed(() => {
    const key = statusToColor[props.status] ?? 'slate';
    return colorMap[key];
});

const variantClasses = computed(() => {
    return props.variant === 'solid' ? palette.value.solid : palette.value.outline;
});

const sizeClasses = computed(() => {
    return props.size === 'md'
        ? 'px-2.5 py-1 text-xs'
        : 'px-2 py-0.5 text-[10px]';
});

const dotSize = computed(() => {
    return props.size === 'md' ? 'h-1.5 w-1.5' : 'h-1 w-1';
});
</script>

<template>
    <span
        :class="
            cn(
                'inline-flex items-center gap-1.5 rounded-lg border font-bold uppercase tracking-wider',
                sizeClasses,
                variantClasses,
                props.class,
            )
        "
        style="font-family: 'Inter', sans-serif"
    >
        <!-- Dot indicator -->
        <span
            :class="cn('shrink-0 rounded-full', dotSize, palette.dot)"
            aria-hidden="true"
        />
        {{ resolvedLabel }}
    </span>
</template>
