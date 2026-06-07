<script setup lang="ts">
import { computed } from "vue";

const props = withDefaults(
	defineProps<{
		variant?: "primary" | "secondary" | "outline" | "ghost" | "danger";
		size?: "sm" | "md" | "lg";
		disabled?: boolean;
		loading?: boolean;
		type?: "button" | "submit" | "reset";
		fullWidth?: boolean;
	}>(),
	{
		variant: "primary",
		size: "md",
		type: "button",
	},
);

const cls = computed(() => {
	const base =
		"inline-flex items-center justify-center gap-1.5 font-medium rounded-lg transition-all duration-150 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2563eb] disabled:opacity-50 disabled:cursor-not-allowed select-none";

	const variants: Record<string, string> = {
		primary:
			"bg-[#111827] text-white hover:bg-[#1f2937] active:bg-[#111827] shadow-sm",
		secondary:
			"bg-[#f3f4f6] text-[#111827] hover:bg-[#e5e7eb] active:bg-[#d1d5db]",
		outline:
			"bg-white text-[#374151] border border-[#d1d5db] hover:bg-[#f9fafb] hover:border-[#9ca3af] shadow-sm",
		ghost: "text-[#6b7280] hover:bg-[#f3f4f6] hover:text-[#111827]",
		danger:
			"bg-white text-[#dc2626] border border-[#fecaca] hover:bg-[#fef2f2] hover:border-[#f87171]",
	};

	const sizes: Record<string, string> = {
		sm: "h-7 px-3 text-[12px]",
		md: "h-8 px-3.5 text-[13px]",
		lg: "h-9 px-4 text-[13.5px]",
	};

	return [
		base,
		variants[props.variant],
		sizes[props.size],
		props.fullWidth ? "w-full" : "",
	].join(" ");
});
</script>

<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        :class="cls"
        style="font-family: var(--wos-font)"
    >
        <svg
            v-if="loading"
            class="w-3.5 h-3.5 animate-spin"
            fill="none" viewBox="0 0 24 24"
            aria-hidden="true"
        >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        <slot v-if="!loading" name="icon" />
        <slot />
    </button>
</template>
