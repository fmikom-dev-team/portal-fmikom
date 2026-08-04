<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const page = usePage();
const siteSettings = computed(() => (page.props as any).siteSettings || {});
</script>

<template>
    <div class="flex items-center min-w-0 max-w-full overflow-hidden">
        <!-- Dynamic Logo from WorkOS Settings with brand logo fallback -->
        <div 
            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl overflow-hidden transition-all duration-300 select-none bg-transparent border-0 p-0 shadow-none"
        >
            <img v-if="siteSettings.brand_logo" :src="siteSettings.brand_logo" class="h-full w-full object-contain" alt="Logo" />
            <img v-else src="/asset/brand-logo.webp" class="h-full w-full object-contain" alt="Logo" />
        </div>
        <!-- Typography -->
        <div class="ml-2.5 flex flex-col text-left group-data-[collapsible=icon]:hidden min-w-0 overflow-hidden">
            <span class="text-[14px] font-black tracking-tight text-slate-800 dark:text-white leading-tight truncate">
                {{ siteSettings.brand_name || 'Portal FMIKOM' }}
            </span>
            <span class="text-[9px] font-bold text-slate-400 dark:text-zinc-500 mt-0.5 uppercase tracking-tight leading-none truncate block max-w-[170px]">
                {{ siteSettings.brand_subtitle || 'Fakultas Matematika dan Ilmu Komputer' }}
            </span>
        </div>
    </div>
</template>
