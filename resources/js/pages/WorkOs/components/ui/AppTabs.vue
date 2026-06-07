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
        class="flex items-end border-b border-[#e5e7eb] overflow-x-auto wos-scroll"
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
                    ? 'border-[#2563EB] text-[#111827]'
                    : 'border-transparent text-[#6b7280] hover:text-[#374151] hover:border-[#d1d5db]',
            ]"
            @click="emit('update:modelValue', tab.id)"
        >
            {{ tab.label }}
            <span
                v-if="tab.badge !== undefined && tab.badge > 0"
                class="flex items-center justify-center h-4 min-w-[16px] px-1 rounded-full text-[9.5px] font-bold bg-[#fef3c7] text-[#92400e]"
                aria-label="`${tab.badge} items`"
            >
                {{ tab.badge }}
            </span>
        </button>
    </div>
</template>
