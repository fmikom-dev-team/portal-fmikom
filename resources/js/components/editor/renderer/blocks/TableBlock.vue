<script setup>
import { sanitizeInline } from "@/composables/useSanitize";

defineProps({ data: Object });
</script>
<template>
    <div class="my-10 overflow-x-auto rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/50 bg-white">
        <table class="w-full border-collapse text-[14px]">
            <caption class="sr-only">Tabel Konten Artikel</caption>
            <thead v-if="data.withHeadings && data.content?.length">
                <tr class="bg-slate-50/50">
                    <th v-for="(cell, ci) in data.content[0]" :key="ci"
                        class="px-6 py-4 text-left font-black text-slate-900 border-b border-slate-100 whitespace-nowrap uppercase tracking-widest text-[11px]"
                        v-html="sanitizeInline(cell)" />
                </tr>
            </thead>
            <tbody>
                <tr v-for="(row, ri) in (data.withHeadings ? data.content.slice(1) : data.content)" :key="ri"
                    class="hover:bg-blue-50/30 transition-colors border-b border-slate-50 last:border-0 group">
                    <td v-for="(cell, ci) in row" :key="ci"
                        class="px-6 py-4 text-slate-600 align-top leading-relaxed group-hover:text-slate-900 transition-colors"
                        v-html="sanitizeInline(cell)" />
                </tr>
            </tbody>
        </table>
        <div v-if="!data.content?.length" class="p-10 text-center text-slate-400 text-sm italic">
            Data tabel tidak tersedia
        </div>
    </div>
</template>
