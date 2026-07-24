<script setup lang="ts">
interface Tab {
	id: string;
	label: string;
	badge?: number;
}

defineProps<{
	tabs: Tab[];
	modelValue: string;
}>();

const emit = defineEmits<(e: "update:modelValue", id: string) => void>();
</script>

<template>
    <div
        class="flex items-end border-b border-[#e5e7eb] dark:border-zinc-800 overflow-x-auto wos-scroll"
        role="tablist"
        style="font-family: var(--wos-font)"
    >
        <button
            v-for="tab in tabs"
            :key="tab.id"
            :id="`tab-${tab.id}`"
            role="tab"
            :aria-selected="modelValue === tab.id"
            :aria-controls="`tabpanel-${tab.id}`"
            :class="[
                'flex items-center gap-1.5 px-1 pb-3 mr-6 text-sm font-medium border-b-2 -mb-px transition-all duration-150 whitespace-nowrap shrink-0',
                modelValue === tab.id
                    ? 'border-[#2563EB] text-[#111827] dark:text-zinc-50'
                    : 'border-transparent text-[#6b7280] dark:text-zinc-500 hover:text-[#374151] dark:hover:text-zinc-300 hover:border-[#d1d5db] dark:border-zinc-700 dark:hover:border-zinc-600',
            ]"
            @click="emit('update:modelValue', tab.id)"
        >
            {{ tab.label }}
            <span
                v-if="tab.badge !== undefined && tab.badge > 0"
                class="flex items-center justify-center h-4 min-w-[16px] px-1 rounded-full text-[9.5px] font-bold bg-[#fef3c7] dark:bg-amber-900/40 text-[#92400e] dark:text-amber-400"
                aria-label="`${tab.badge} items`"
            >
                {{ tab.badge }}
            </span>
        </button>
    </div>
</template>
