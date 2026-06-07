<script setup lang="ts">
// Redesigned brand logo representing Nexus Portal FMIKOM
</script>

<template>
    <div class="flex items-center">
        <!-- Modern Geometric Rhombus Logo Icon with Indigo Gradient -->
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#6366f1] to-[#4f46e5] text-white shadow-lg shadow-indigo-100 dark:shadow-none hover:rotate-12 transition-transform duration-500 ease-out select-none">
            <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
            </svg>
        </div>
        <!-- Modern Premium Typography matching Gambar 1 -->
        <div class="ml-3 flex flex-col text-left group-data-[collapsible=icon]:hidden">
            <span class="text-[17px] font-extrabold tracking-tight text-slate-800 dark:text-white leading-none">
                FMIKOM<span class="text-indigo-600 dark:text-indigo-400 font-black text-base ml-[1px]">.</span>
            </span>
            <span class="text-[9px] font-extrabold text-slate-400 dark:text-zinc-500 mt-1 uppercase tracking-widest leading-none">Portal SSO</span>
        </div>
    </div>
</template>
