<script setup>
import { sanitize } from "@/composables/useSanitize";

defineProps({ data: Object });
</script>
<template>
    <blockquote class="border-l-4 border-blue-600 bg-blue-50/40 py-6 px-8 my-10 rounded-r-2xl italic shadow-sm shadow-blue-500/5">
        <p class="text-[1.25rem] leading-relaxed text-slate-700 font-medium" v-html="sanitize(data.text)" />
        <cite v-if="data.caption" class="block mt-4 text-[13px] text-blue-600/60 not-italic font-bold uppercase tracking-widest">
            — {{ data.caption }}
        </cite>
    </blockquote>
</template>
