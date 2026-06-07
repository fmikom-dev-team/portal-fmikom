<script setup>
import { sanitizeInline } from "@/composables/useSanitize";

defineProps({ data: Object });
</script>
<template>
    <ul class="my-5 space-y-2 list-none pl-0">
        <li v-for="(item, i) in (data.items || [])" :key="i"
            class="flex items-start gap-3">
            <span :class="[
                'mt-0.5 flex-shrink-0 w-[18px] h-[18px] rounded-[5px] border-2 flex items-center justify-center text-xs transition-colors',
                item.checked ? 'bg-blue-500 border-blue-500 text-white' : 'border-gray-300 bg-white'
            ]">
                <svg v-if="item.checked" xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </span>
            <span :class="['text-[1.0625rem] leading-relaxed', item.checked ? 'line-through text-gray-400' : 'text-slate-700']"
                  v-html="sanitizeInline(item.text)" />
        </li>
    </ul>
</template>
