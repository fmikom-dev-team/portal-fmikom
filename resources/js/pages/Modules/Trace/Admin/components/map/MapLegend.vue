<script setup lang="ts">
import { Filter } from 'lucide-vue-next';
import { type ViewMode } from '@/composables/trace/useMapData';
import { CATEGORY_CONFIG } from '@/composables/trace/useMapData';

defineProps<{
    viewMode: ViewMode;
    hasActiveFilters: boolean;
    tematicLegend: { name: string; count: number; color: string }[];
    lastUpdated: string;
}>();
</script>

<template>
    <div class="absolute bottom-0 left-0 right-0 z-[1000]">
        <div class="rounded-t-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-xl mx-4 mb-0">

            <!-- Legend Row -->
            <div class="flex items-center justify-between px-5 py-2.5 flex-wrap gap-2">
                <div class="flex items-center gap-4 flex-wrap">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Legenda</span>

                    <!-- Active filter indicator -->
                    <div v-if="hasActiveFilters" class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800">
                        <Filter class="h-2.5 w-2.5 text-amber-600" />
                        <span class="text-[9px] font-black text-amber-600 uppercase tracking-wider">Filter Aktif</span>
                    </div>

                    <!-- Marker legend (cluster/tematic) -->
                    <template v-if="viewMode === 'cluster'">
                        <div v-for="(cfg, key) in CATEGORY_CONFIG" :key="key" class="flex items-center gap-1">
                            <div class="h-2.5 w-2.5 rounded-full" :style="{ background: cfg.color }"></div>
                            <span class="text-[9px] font-bold text-slate-500">{{ cfg.label }}</span>
                        </div>
                    </template>

                    <!-- Tematic legend -->
                    <template v-if="viewMode === 'tematic' && tematicLegend.length > 0">
                        <div v-for="item in tematicLegend.slice(0, 8)" :key="item.name" class="flex items-center gap-1">
                            <div class="h-2.5 w-2.5 rounded-full" :style="{ background: item.color }"></div>
                            <span class="text-[9px] font-bold text-slate-500">{{ item.name }} ({{ item.count }})</span>
                        </div>
                    </template>

                    <!-- Heat gradient -->
                    <div v-if="viewMode === 'heat'" class="flex items-center gap-1.5">
                        <span class="text-[9px] font-bold text-slate-400">Rendah</span>
                        <div class="h-2.5 w-20 rounded-full" style="background: linear-gradient(to right, #3b82f6, #22c55e, #eab308, #f97316, #ef4444);"></div>
                        <span class="text-[9px] font-bold text-slate-400">Tinggi</span>
                    </div>

                    <!-- Choropleth gradient -->
                    <div v-if="viewMode === 'choropleth'" class="flex items-center gap-1.5">
                        <span class="text-[9px] font-bold text-slate-400">Sedikit</span>
                        <div class="h-2.5 w-20 rounded-full" style="background: linear-gradient(to right, #c5dcf5, #85B7EB, #3a8fd4, #1a6bb5, #0C447C);"></div>
                        <span class="text-[9px] font-bold text-slate-400">Banyak</span>
                    </div>
                </div>

                <div class="flex items-center gap-3 text-[9px] text-slate-400">
                    <span>⚠️ Titik di-jitter ±1km</span>
                    <span>📋 Sumber: Profil & Karir Alumni</span>
                    <span v-if="lastUpdated">● {{ lastUpdated }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
