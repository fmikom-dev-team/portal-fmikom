<script setup lang="ts">
import { computed } from "vue";

const props = withDefaults(
	defineProps<{
		variant?:
			| "default"
			| "success"
			| "warning"
			| "error"
			| "info"
			| "pending"
			| "outline";
		size?: "xs" | "sm";
		dot?: boolean;
	}>(),
	{
		variant: "default",
		size: "sm",
	},
);

const styles: Record<string, string> = {
	default: "bg-[#f3f4f6] text-[#374151] border-[#e5e7eb]",
	success: "bg-[#d1fae5] text-[#065f46] border-[#a7f3d0]",
	warning: "bg-[#fef3c7] text-[#92400e] border-[#fde68a]",
	error: "bg-[#fee2e2] text-[#991b1b] border-[#fecaca]",
	info: "bg-[#dbeafe] text-[#1e40af] border-[#bfdbfe]",
	pending: "bg-[#fef3c7] text-[#92400e] border-[#fde68a]",
	outline: "bg-white text-[#6b7280] border-[#e5e7eb]",
};

const dotStyles: Record<string, string> = {
	default: "bg-[#9ca3af]",
	success: "bg-[#10b981]",
	warning: "bg-[#f59e0b]",
	error: "bg-[#ef4444]",
	info: "bg-[#3b82f6]",
	pending: "bg-[#f59e0b]",
	outline: "bg-[#9ca3af]",
};

const sizes: Record<string, string> = {
	xs: "h-4 px-1.5 text-[9px]",
	sm: "h-5 px-2 text-[10.5px]",
};
</script>

<template>
    <span
        :class="[
            'inline-flex items-center gap-1 font-semibold rounded-full border',
            styles[variant],
            sizes[size],
        ]"
        style="font-family: var(--wos-font)"
    >
        <span v-if="dot" :class="['w-1.5 h-1.5 rounded-full shrink-0', dotStyles[variant]]" aria-hidden="true" />
        <slot />
    </span>
</template>
