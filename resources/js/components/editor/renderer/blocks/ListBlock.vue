<script setup>
import { computed } from "vue";
import { sanitizeInline } from "@/composables/useSanitize";

const props = defineProps({ data: Object });
const isOrdered = computed(() => props.data?.style === "ordered");

// Recursively render items (handles nested-list structure)
const renderItems = (items) => items;
</script>
<template>
    <ol v-if="isOrdered" class="my-5 pl-6 space-y-1 list-decimal">
        <template v-for="(item, i) in (data.items || [])" :key="i">
            <!-- Nested list format: {content, items} -->
            <li v-if="typeof item === 'object' && item.content !== undefined"
                class="text-[1.125rem] text-slate-600 leading-[1.8] pl-2 marker:text-blue-500">
                <span v-html="sanitizeInline(item.content)" />
                <ol v-if="item.items && item.items.length" class="mt-3 pl-6 space-y-2 list-decimal">
                    <li v-for="(sub, j) in item.items" :key="j"
                        class="text-[1.125rem] text-slate-600 leading-[1.8] pl-2 marker:text-blue-400"
                        v-html="sanitizeInline(typeof sub === 'object' ? sub.content : sub)" />
                </ol>
            </li>
            <!-- Flat string format -->
            <li v-else class="text-[1.125rem] text-slate-600 leading-[1.8] pl-2 marker:text-blue-500"
                v-html="sanitizeInline(item)" />
        </template>
    </ol>
    <ul v-else class="my-5 pl-6 space-y-1 list-disc">
        <template v-for="(item, i) in (data.items || [])" :key="i">
            <!-- Nested list format: {content, items} -->
            <li v-if="typeof item === 'object' && item.content !== undefined"
                class="text-[1.125rem] text-slate-600 leading-[1.8] pl-2 marker:text-blue-500">
                <span v-html="sanitizeInline(item.content)" />
                <ul v-if="item.items && item.items.length" class="mt-3 pl-6 space-y-2 list-disc">
                    <li v-for="(sub, j) in item.items" :key="j"
                        class="text-[1.125rem] text-slate-600 leading-[1.8] pl-2 marker:text-blue-400"
                        v-html="sanitizeInline(typeof sub === 'object' ? sub.content : sub)" />
                </ul>
            </li>
            <!-- Flat string format -->
            <li v-else class="text-[1.125rem] text-slate-600 leading-[1.8] pl-2 marker:text-blue-500"
                v-html="sanitizeInline(item)" />
        </template>
    </ul>
</template>
