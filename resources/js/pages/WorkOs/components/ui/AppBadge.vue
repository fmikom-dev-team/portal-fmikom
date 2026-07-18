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
	default: "bg-[#f3f4f6] dark:bg-zinc-800 text-[#374151] dark:text-zinc-200 border-[#e5e7eb] dark:border-zinc-700",
	success: "bg-[#d1fae5] dark:bg-emerald-950/30 text-[#065f46] dark:text-emerald-400 border-[#a7f3d0] dark:border-emerald-900/40",
	warning: "bg-[#fef3c7] dark:bg-amber-950/30 text-[#92400e] dark:text-amber-400 border-[#fde68a] dark:border-amber-900/40",
	error: "bg-[#fee2e2] dark:bg-red-950/30 text-[#991b1b] dark:text-red-400 border-[#fecaca] dark:border-red-900/40",
	info: "bg-[#dbeafe] dark:bg-blue-950/30 text-[#1e40af] dark:text-blue-400 border-[#bfdbfe] dark:border-blue-900/40",
	pending: "bg-[#fef3c7] dark:bg-amber-950/30 text-[#92400e] dark:text-amber-400 border-[#fde68a] dark:border-amber-900/40",
	outline: "bg-white dark:bg-zinc-900 text-[#6b7280] dark:text-zinc-400 border-[#e5e7eb] dark:border-zinc-700",
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
