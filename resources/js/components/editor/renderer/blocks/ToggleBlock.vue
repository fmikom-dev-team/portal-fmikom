<script setup>
import { ref } from "vue";
import { sanitize, sanitizeInline } from "@/composables/useSanitize";

const props = defineProps({ data: Object });
const open = ref(false);
</script>
<template>
    <div class="my-4 border border-gray-200 rounded-xl overflow-hidden">
        <button @click="open = !open"
                class="w-full flex items-center justify-between px-5 py-3.5 text-left bg-gray-50 hover:bg-gray-100 transition-colors group">
            <span class="font-semibold text-gray-800 text-[0.9375rem]" v-html="sanitizeInline(data.title || 'Toggle')" />
            <svg :class="['w-4 h-4 text-gray-400 transition-transform duration-200', open ? 'rotate-90' : '']"
                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </button>
        <Transition
            enter-active-class="transition-all duration-200 ease-out overflow-hidden"
            leave-active-class="transition-all duration-150 ease-in overflow-hidden"
            enter-from-class="opacity-0 max-h-0"
            enter-to-class="opacity-100 max-h-[1000px]"
            leave-from-class="opacity-100 max-h-[1000px]"
            leave-to-class="opacity-0 max-h-0">
            <div v-if="open" class="px-5 py-4 text-[0.9375rem] text-gray-600 leading-relaxed border-t border-gray-100"
                 v-html="sanitize(data.content || data.message || '')" />
        </Transition>
    </div>
</template>
