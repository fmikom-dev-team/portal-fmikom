<script setup>
import { sanitize } from "@/composables/useSanitize";

defineProps({ data: Object });
</script>
<template>
    <p class="text-[1.125rem] leading-[1.8] text-slate-600 my-6 first:mt-0 last:mb-0 font-normal"
       v-html="sanitize(data.text || '&nbsp;')" />
</template>
