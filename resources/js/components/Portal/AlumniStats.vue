<script setup lang="ts">
// Static component representing the 4 stat boxes at the bottom of the map section
</script>

<template>
    <!-- 4 Stat Boxes -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 hide-animate slide-up" style="transition-delay: 200ms">
        <!-- Box 1 -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-xl shadow-slate-200/20 flex items-center gap-4 group hover:-translate-y-1 transition-transform">
            <div class="w-14 h-14 shrink-0 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
            </div>
            <div>
                <h4 class="text-2xl font-black text-slate-900 mb-0.5">4.782+</h4>
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
                <h4 class="text-2xl font-black text-slate-900 mb-0.5">34</h4>
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
                <h4 class="text-2xl font-black text-slate-900 mb-0.5">1.256+</h4>
                <p class="text-xs font-bold text-slate-700">Perusahaan</p>
                <p class="text-[10px] text-slate-400">Tempat alumni berkarya</p>
            </div>
        </div>

        <!-- Box 4 -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-xl shadow-slate-200/20 flex items-center gap-4 group hover:-translate-y-1 transition-transform">
            <div class="w-14 h-14 shrink-0 rounded-2xl bg-orange-50 flex items-center justify-center text-orange-600 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
            </div>
            <div>
                <h4 class="text-2xl font-black text-slate-900 mb-0.5">312</h4>
                <p class="text-xs font-bold text-slate-700">Alumni Luar Negeri</p>
                <p class="text-[10px] text-slate-400">Tersebar di 24 negara</p>
            </div>
        </div>
    </div>
</template>
