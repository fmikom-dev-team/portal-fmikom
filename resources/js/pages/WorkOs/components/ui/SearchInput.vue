<script setup lang="ts">
defineProps<{
	modelValue?: string;
	placeholder?: string;
}>();

const emit = defineEmits<(e: "update:modelValue", value: string) => void>();
</script>

<template>
    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-gray-50 dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-lg text-[12px] text-gray-400 dark:text-zinc-500 hover:border-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-800 cursor-pointer transition-all w-44">
        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" />
        </svg>
        <input
            type="text"
            :value="modelValue"
            :placeholder="placeholder || 'Search…'"
            class="bg-transparent border-none focus:ring-0 p-0 text-[12px] text-gray-900 dark:text-zinc-100 placeholder:text-gray-400 w-full"
            @input="e => emit('update:modelValue', (e.target as HTMLInputElement).value)"
        />
        <kbd class="ml-auto text-[10px] bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded px-1 py-px text-gray-400 dark:text-zinc-500 font-sans">⌘K</kbd>
    </div>
</template>
