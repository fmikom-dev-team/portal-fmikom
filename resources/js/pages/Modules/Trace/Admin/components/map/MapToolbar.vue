<script setup lang="ts">
import {
    Layers, Sun, Moon,
} from 'lucide-vue-next';
import { type ViewMode } from '@/composables/trace/useMapData';

defineProps<{
    viewMode: ViewMode;
    isDarkMap: boolean;
    showMarkers: boolean;
    tematicField: string;
}>();

const emit = defineEmits<{
    'update:viewMode': [mode: ViewMode];
    'update:tematicField': [field: string];
    toggleDarkMode: [];
    toggleMarkers: [];
}>();
</script>

<template>
    <!-- Header + Mode Toggle -->
    <div class="rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-xl px-4 py-3">
        <div class="flex items-center gap-2 mb-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#0C447C] shadow-md">
                <Layers class="h-4 w-4 text-white" />
            </div>
            <div class="flex-1">
                <p class="text-[10px] font-black tracking-widest text-slate-400 uppercase">WebGIS</p>
                <p class="text-sm font-black text-slate-800 dark:text-white leading-none">Peta Sebaran Alumni</p>
            </div>
            <button @click="emit('toggleDarkMode')" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Toggle light/dark map" aria-label="Ubah mode peta terang/gelap">
                <Moon v-if="!isDarkMap" class="h-4 w-4 text-slate-400" />
                <Sun v-else class="h-4 w-4 text-amber-400" />
            </button>
        </div>

        <!-- Mode Buttons -->
        <div class="flex rounded-xl bg-slate-100 dark:bg-slate-800 p-1 gap-0.5">
            <button v-for="mode in (['cluster', 'heat', 'choropleth', 'tematic'] as ViewMode[])" :key="mode"
                @click="emit('update:viewMode', mode)"
                :class="viewMode === mode ? 'bg-white dark:bg-slate-700 text-[#0C447C] shadow-sm' : 'text-slate-400 hover:text-slate-600'"
                class="flex-1 px-1.5 py-1.5 rounded-lg text-[8px] font-black transition-all uppercase tracking-wide"
            >{{ mode === 'cluster' ? 'TITIK' : mode === 'heat' ? 'HEAT' : mode === 'choropleth' ? 'WILAYAH' : 'TEMATIK' }}</button>
        </div>

        <!-- Tematic field selector -->
        <div v-if="viewMode === 'tematic'" class="mt-2">
            <select :value="tematicField" @change="emit('update:tematicField', ($event.target as HTMLSelectElement).value)" aria-label="Pilih field tematik" class="w-full h-8 rounded-lg bg-slate-50 dark:bg-slate-800 px-2 text-[10px] font-bold text-slate-700 dark:text-slate-300 outline-none border border-slate-200 dark:border-slate-700">
                <option value="status">Warna berdasarkan Status</option>
                <option value="sektor">Warna berdasarkan Sektor</option>
                <option value="angkatan">Warna berdasarkan Angkatan</option>
                <option value="prodi">Warna berdasarkan Prodi</option>
            </select>
        </div>

        <!-- Show/Hide Markers toggle (only heat & choropleth) -->
        <label v-if="viewMode === 'heat' || viewMode === 'choropleth'" @click.prevent="emit('toggleMarkers')"
            class="flex items-center gap-2 mt-2 px-2 py-1.5 rounded-lg cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
        >
            <div class="h-4 w-4 rounded border-2 flex items-center justify-center transition-all"
                :class="showMarkers ? 'bg-blue-600 border-blue-600' : 'border-slate-300 dark:border-slate-600'"
            >
                <svg v-if="showMarkers" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">Tampilkan titik alumni</span>
        </label>
    </div>
</template>
