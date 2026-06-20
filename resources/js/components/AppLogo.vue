<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const page = usePage();
const siteSettings = computed(() => (page.props as any).siteSettings || {});
</script>

<template>
    <div class="flex items-center">
        <!-- Dynamic Logo from WorkOS Settings with Rhombus fallback -->
        <div 
            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl overflow-hidden transition-all duration-300 select-none"
            :class="siteSettings.brand_logo ? 'bg-transparent border border-slate-200 dark:border-zinc-800' : 'bg-gradient-to-br from-[#6366f1] to-[#4f46e5] text-white shadow-lg shadow-indigo-100 dark:shadow-none hover:rotate-12'"
        >
            <img v-if="siteSettings.brand_logo" :src="siteSettings.brand_logo" class="h-full w-full object-contain" alt="Logo" />
            <svg v-else class="h-[18px] w-[18px] text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
            </svg>
        </div>
        <!-- Typography -->
        <div class="ml-3 flex flex-col text-left group-data-[collapsible=icon]:hidden">
            <span class="text-[17px] font-extrabold tracking-tight text-slate-800 dark:text-white leading-none">
                {{ siteSettings.brand_name || 'FMIKOM' }}<span class="text-indigo-600 dark:text-indigo-400 font-black text-base ml-[1px]">.</span>
            </span>
            <span class="text-[9px] font-extrabold text-slate-400 dark:text-zinc-500 mt-1.5 uppercase tracking-widest leading-none">FMIKOM UNUGHA</span>
        </div>
    </div>
</template>
