<script setup>
import { sanitize, sanitizeInline } from "@/composables/useSanitize";

defineProps({ data: Object });
</script>
<template>
    <div class="my-10 border border-amber-100 bg-amber-50/50 rounded-[2rem] p-8 flex gap-5">
        <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        <div class="min-w-0">
            <h5 v-if="data.title" class="font-black text-amber-900 text-sm uppercase tracking-widest mb-2" v-html="sanitizeInline(data.title)"></h5>
            <p class="text-amber-800/80 text-[15px] leading-relaxed m-0" v-html="sanitize(data.message)" />
        </div>
    </div>
</template>
