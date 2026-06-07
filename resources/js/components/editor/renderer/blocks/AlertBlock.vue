<script setup>
import { computed } from "vue";
import { sanitize } from "@/composables/useSanitize";

const props = defineProps({ data: Object });

const styleMap = {
	info: {
		bg: "bg-blue-50",
		border: "border-blue-400",
		text: "text-blue-800",
		emoji: "ℹ️",
	},
	success: {
		bg: "bg-green-50",
		border: "border-green-400",
		text: "text-green-800",
		emoji: "✅",
	},
	warning: {
		bg: "bg-amber-50",
		border: "border-amber-400",
		text: "text-amber-800",
		emoji: "⚠️",
	},
	danger: {
		bg: "bg-red-50",
		border: "border-red-400",
		text: "text-red-800",
		emoji: "🚨",
	},
	primary: {
		bg: "bg-indigo-50",
		border: "border-indigo-400",
		text: "text-indigo-800",
		emoji: "📌",
	},
};
const style = computed(() => styleMap[props.data?.type] ?? styleMap.info);
</script>
<template>
    <div :class="['my-5 px-4 py-3.5 rounded-xl border-l-4 border', style.bg, style.border, style.text]">
        <span class="mr-1.5">{{ style.emoji }}</span>
        <span class="text-[0.9375rem] leading-relaxed" v-html="sanitize(data.message || data.text)" />
    </div>
</template>
