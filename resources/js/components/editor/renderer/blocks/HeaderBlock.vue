<script setup>
import { computed } from "vue";
import { sanitizeInline } from "@/composables/useSanitize";

const props = defineProps({ data: Object });

const tag = computed(
	() => `h${Math.min(Math.max(props.data?.level ?? 2, 1), 6)}`,
);
const sizeMap = {
	1: "text-[2.5rem]",
	2: "text-[1.875rem]",
	3: "text-[1.4rem]",
	4: "text-[1.15rem]",
	5: "text-[1rem]",
	6: "text-[0.9rem]",
};
const classes = computed(
	() =>
		`font-black tracking-tight text-gray-900 leading-tight mt-10 mb-3 first:mt-0 scroll-mt-24 ${sizeMap[props.data?.level ?? 2]}`,
);

const id = computed(() => {
	const text = props.data?.text || "";
	// Strip HTML and create slug
	return text
		.replace(/<[^>]*>?/gm, "")
		.toLowerCase()
		.replace(/[^a-z0-9]+/g, "-")
		.replace(/(^-|-$)+/g, "");
});
</script>
<template>
    <component :is="tag" :id="id" :class="classes" v-html="sanitizeInline(data.text || '')" style="font-family:'Plus Jakarta Sans',system-ui,sans-serif" />
</template>
