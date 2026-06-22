<script setup lang="ts">
import { Crosshair } from 'lucide-vue-next';
import L from 'leaflet';

defineProps<{
    radiusMode: boolean;
    radiusKm: number;
    radiusCenter: L.LatLng | null;
    radiusAlumniCount: number;
}>();

const emit = defineEmits<{
    toggle: [];
    clear: [];
    updateSize: [km: number];
}>();
</script>

<template>
    <div class="rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-xl px-4 py-3">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-1.5">
                <Crosshair class="h-3.5 w-3.5" :class="radiusMode ? 'text-[#0C447C]' : 'text-slate-400'" />
                <span class="text-[9px] font-black uppercase tracking-widest" :class="radiusMode ? 'text-[#0C447C]' : 'text-slate-400'">Radius Search</span>
            </div>
            <button @click="emit('toggle')"
                class="px-2.5 py-1 rounded-lg text-[9px] font-black transition-all"
                :class="radiusMode ? 'bg-[#0C447C] text-white shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-slate-600'"
                aria-label="Aktifkan pencarian radius"
            >{{ radiusMode ? 'AKTIF ✕' : 'AKTIFKAN' }}</button>
        </div>

        <template v-if="radiusMode">
            <p class="text-[9px] text-slate-400 mb-2">📍 Klik di peta untuk menentukan titik pusat</p>
            <div class="flex gap-1">
                <button v-for="km in [5, 10, 25, 50]" :key="km"
                    @click="emit('updateSize', km)"
                    class="flex-1 py-1 rounded-lg text-[9px] font-black transition-all"
                    :class="radiusKm === km ? 'bg-[#0C447C] text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700'"
                >{{ km }} km</button>
            </div>
            <div v-if="radiusCenter" class="mt-2 flex items-center justify-between px-2 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                <span class="text-[10px] font-bold text-blue-700 dark:text-blue-300">{{ radiusAlumniCount }} alumni ditemukan</span>
                <button @click="emit('clear')" class="text-[9px] font-bold text-red-500 hover:text-red-600" aria-label="Hapus pencarian radius">Hapus</button>
            </div>
        </template>
    </div>
</template>
