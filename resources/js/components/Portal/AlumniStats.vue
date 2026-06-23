<script setup lang="ts">
import { computed } from "vue";

const props = withDefaults(
    defineProps<{
        stats?: {
            alumni?: number;
            provinsi?: number;
            perusahaan?: number;
            luarNegeri?: number;
        };
    }>(),
    {
        stats: () => ({
            alumni: 0,
            provinsi: 0,
            perusahaan: 0,
            luarNegeri: 0,
        }),
    }
);

const displayAlumni = computed(() => (props.stats?.alumni ?? 0).toLocaleString("id-ID"));
const displayProvinsi = computed(() => (props.stats?.provinsi ?? 0).toLocaleString("id-ID"));
const displayPerusahaan = computed(() => (props.stats?.perusahaan ?? 0).toLocaleString("id-ID"));
</script>

<template>
    <!-- 3 Stat Boxes -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 hide-animate slide-up" style="transition-delay: 200ms">
        <!-- Box 1 -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-xl shadow-slate-200/20 flex items-center gap-4 group hover:-translate-y-1 transition-transform">
            <div class="w-14 h-14 shrink-0 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
            </div>
            <div>
                <h4 class="text-2xl font-black text-slate-900 mb-0.5">{{ displayAlumni }}</h4>
                <p class="text-xs font-bold text-slate-700">Alumni Terdaftar</p>
                <p class="text-[10px] text-slate-400">Bergabung dari berbagai angkatan</p>
            </div>
        </div>
        
        <!-- Box 2 -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-xl shadow-slate-200/20 flex items-center gap-4 group hover:-translate-y-1 transition-transform">
            <div class="w-14 h-14 shrink-0 rounded-2xl bg-green-50 flex items-center justify-center text-green-600 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            </div>
            <div>
                <h4 class="text-2xl font-black text-slate-900 mb-0.5">{{ displayProvinsi }}</h4>
                <p class="text-xs font-bold text-slate-700">Provinsi</p>
                <p class="text-[10px] text-slate-400">Alumni tersebar di seluruh Indonesia</p>
            </div>
        </div>

        <!-- Box 3 -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-xl shadow-slate-200/20 flex items-center gap-4 group hover:-translate-y-1 transition-transform">
            <div class="w-14 h-14 shrink-0 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
            </div>
            <div>
                <h4 class="text-2xl font-black text-slate-900 mb-0.5">{{ displayPerusahaan }}</h4>
                <p class="text-xs font-bold text-slate-700">Perusahaan</p>
                <p class="text-[10px] text-slate-400">Tempat alumni berkarya</p>
            </div>
        </div>
    </div>
</template>
