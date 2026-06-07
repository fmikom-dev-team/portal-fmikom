<script setup lang="ts">
defineProps<{
	label: string;
	value: string | number;
	sublabel?: string;
	footerLabel?: string;
	icon?: string;
	iconColor?: string;
}>();
</script>

<template>
    <div
        class="bg-white rounded-xl p-5 ring-1 ring-gray-900/[0.04] hover:ring-gray-900/[0.08] transition-all cursor-default"
        style="font-family: var(--wos-font)"
    >
        <p class="text-[13px] font-semibold text-[#374151] mb-3">{{ label }}</p>
        <p class="text-[32px] font-bold text-[#111827] leading-none mb-1.5 tabular-nums">{{ value }}</p>
        <p v-if="sublabel" class="text-[12px] text-[#6b7280]">{{ sublabel }}</p>
        <div v-if="footerLabel" class="mt-4 pt-3 border-t border-[#f3f4f6]">
            <p class="text-[11.5px] text-[#6b7280]">{{ footerLabel }}</p>
        </div>
    </div>
</template>
