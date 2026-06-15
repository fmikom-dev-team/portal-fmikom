<script setup>
import { computed } from 'vue';

const props = defineProps({
    content: { type: [String, Object, null], default: null },
});

const blocks = computed(() => {
    if (!props.content) return [];
    let data = props.content;
    if (typeof data === 'string') {
        try {
            data = JSON.parse(data);
        } catch {
            // Plain text fallback
            return [{ type: 'paragraph', data: { text: data } }];
        }
    }
    return data?.blocks ?? [];
});
</script>

<template>
    <div class="editorjs-renderer prose prose-sm dark:prose-invert max-w-none" style="overflow-wrap: break-word; word-break: break-word;">
        <template v-for="(block, i) in blocks" :key="i">
            <!-- Paragraph -->
            <p v-if="block.type === 'paragraph'" v-html="block.data.text" />

            <!-- Header -->
            <h1 v-else-if="block.type === 'header' && block.data.level === 1" v-html="block.data.text" />
            <h2 v-else-if="block.type === 'header' && block.data.level === 2" v-html="block.data.text" />
            <h3 v-else-if="block.type === 'header' && block.data.level === 3" v-html="block.data.text" />
            <h4 v-else-if="block.type === 'header' && block.data.level === 4" v-html="block.data.text" />
            <h5 v-else-if="block.type === 'header' && block.data.level === 5" v-html="block.data.text" />
            <h6 v-else-if="block.type === 'header' && block.data.level === 6" v-html="block.data.text" />

            <!-- List (nested) -->
            <component
                v-else-if="block.type === 'list' || block.type === 'nestedList'"
                :is="block.data.style === 'ordered' ? 'ol' : 'ul'"
                class="list-inside"
                :class="block.data.style === 'ordered' ? 'list-decimal' : 'list-disc'"
            >
                <li v-for="(item, j) in block.data.items" :key="j">
                    <span v-html="typeof item === 'string' ? item : item.content" />
                </li>
            </component>

            <!-- Checklist -->
            <div v-else-if="block.type === 'checklist'" class="space-y-1">
                <label v-for="(item, j) in block.data.items" :key="j" class="flex items-center gap-2 text-sm">
                    <input type="checkbox" :checked="item.checked" disabled class="rounded" />
                    <span v-html="item.text" :class="{ 'line-through opacity-60': item.checked }" />
                </label>
            </div>

            <!-- Quote -->
            <blockquote v-else-if="block.type === 'quote'" class="border-l-4 border-violet-300 pl-4 italic dark:border-violet-700">
                <p v-html="block.data.text" />
                <cite v-if="block.data.caption" class="mt-1 block text-xs not-italic text-slate-400" v-html="block.data.caption" />
            </blockquote>

            <!-- Delimiter -->
            <hr v-else-if="block.type === 'delimiter'" class="my-4 border-slate-200 dark:border-zinc-700" />

            <!-- Code -->
            <pre v-else-if="block.type === 'code'" class="rounded-lg bg-slate-100 p-4 text-sm dark:bg-zinc-800"><code>{{ block.data.code }}</code></pre>

            <!-- Table -->
            <div v-else-if="block.type === 'table'" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead v-if="block.data.withHeadings && block.data.content.length > 0">
                        <tr>
                            <th v-for="(cell, c) in block.data.content[0]" :key="c" class="border-b border-slate-200 p-2 text-left font-semibold dark:border-zinc-700" v-html="cell" />
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(row, r) in block.data.withHeadings ? block.data.content.slice(1) : block.data.content" :key="r">
                            <td v-for="(cell, c) in row" :key="c" class="border-b border-slate-100 p-2 dark:border-zinc-800" v-html="cell" />
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Raw HTML -->
            <div v-else-if="block.type === 'raw'" v-html="block.data.html" />

            <!-- Fallback: unknown block -->
            <p v-else class="text-sm text-slate-400 italic">[{{ block.type }}]</p>
        </template>

        <!-- Empty state -->
        <p v-if="blocks.length === 0" class="text-sm text-slate-400 italic">Tidak ada deskripsi</p>
    </div>
</template>
