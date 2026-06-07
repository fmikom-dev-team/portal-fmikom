<template>
    <div class="editorjs-raw-html my-6 w-full" v-html="sanitize(data.html)"></div>
</template>

<script setup>
import { sanitize } from "@/composables/useSanitize";

defineProps({
	data: {
		type: Object,
		required: true,
		// Expected shape: { html: '...' }
	},
});
</script>
