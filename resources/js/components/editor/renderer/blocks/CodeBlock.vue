<script setup>
defineProps({ data: Object });
</script>
<template>
    <div class="my-6 rounded-[14px] overflow-hidden shadow-sm border border-gray-100">
        <div class="bg-[#0f172a] px-5 pt-4 pb-1 flex items-center gap-1.5">
            <span v-if="data.language" class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">{{ data.language }}</span>
        </div>
        <pre class="bg-[#0f172a] text-[#e2e8f0] px-5 pt-2 pb-5 overflow-x-auto text-[0.875rem] leading-[1.75] m-0 rounded-none"><code>{{ data.code }}</code></pre>
    </div>
</template>
