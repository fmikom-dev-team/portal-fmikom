<script setup lang="ts">
defineProps<{
	title: string;
	description?: string;
	icon?: string;
	compact?: boolean;
}>();
</script>

<template>
    <div
        :class="['flex flex-col items-center justify-center text-center', compact ? 'py-8 px-4' : 'py-14 px-8']"
        style="font-family: var(--wos-font)"
    >
        <div class="w-10 h-10 rounded-full bg-[#f3f4f6] dark:bg-zinc-800 border border-[#e5e7eb] dark:border-zinc-700 flex items-center justify-center mb-3.5">
            <svg v-if="icon" class="w-5 h-5 text-[#9ca3af] dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" :d="icon" />
            </svg>
            <svg v-else class="w-5 h-5 text-[#9ca3af] dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
        </div>
        <p class="text-[13.5px] font-semibold text-[#374151] dark:text-zinc-300">{{ title }}</p>
        <p v-if="description" class="text-[12px] text-[#9ca3af] dark:text-zinc-600 mt-1 max-w-xs leading-relaxed">{{ description }}</p>
        <div v-if="$slots.actions" class="mt-5">
            <slot name="actions" />
        </div>
    </div>
</template>
