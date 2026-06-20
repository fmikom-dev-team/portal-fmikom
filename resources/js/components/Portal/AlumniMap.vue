<script setup lang="ts">
import { ref } from "vue";
import { X } from "lucide-vue-next";
import AlumniStats from "@/components/Portal/AlumniStats.vue";

const showAlumniPopup = ref(true);
</script>

<template>
    <!-- ALUMNI MAP TRACKING SECTION -->
    <section class="relative overflow-hidden bg-white py-24 sm:py-32 font-sans border-t border-gray-100">
        <!-- Subtle Background Elements -->
        <div class="absolute inset-0 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] bg-size-[16px_16px] opacity-30 pointer-events-none"></div>

        <div class="relative z-10 mx-auto max-w-360 px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center mb-24">
                
                <!-- Left Text Content -->
                <div class="lg:col-span-5 flex flex-col justify-center text-center lg:text-left z-20 hide-animate slide-right">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-50 border border-slate-200 text-slate-500 text-xs font-semibold w-fit mx-auto lg:mx-0 mb-8 shadow-sm">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        <span class="text-blue-500 font-bold">Terhubung</span> <span class="text-slate-300">•</span> 
                        <span class="text-blue-500 font-bold">Tumbuh</span> <span class="text-slate-300">•</span> 
                        <span class="text-blue-500 font-bold">Berdampak</span>
                    </div>
                    
                    <h2 class="text-4xl sm:text-5xl lg:text-[3.5rem] font-extrabold tracking-tight text-slate-900 mb-6 leading-[1.1]">
                        Lacak Alumni,<br>Bentuk Masa Depan<br>
                        <span class="text-transparent bg-clip-text bg-linear-to-r from-blue-600 to-blue-400">Bersama</span>
                    </h2>
                    
                    <p class="text-slate-500 text-lg sm:text-xl mb-10 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                        AlumniTrack membantu kampus terhubung dengan alumni, memetakan jejak karier, dan membangun jaringan yang lebih kuat di seluruh dunia.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row items-center gap-4 justify-center lg:justify-start">
                        <button class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-blue-600 text-white font-semibold shadow-lg shadow-blue-600/30 hover:bg-blue-700 hover:shadow-blue-600/40 transition-all flex items-center justify-center gap-2 group">
                            Jelajahi Peta Alumni
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </button>
                        <button class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-white text-slate-700 font-semibold border border-slate-200 hover:bg-slate-50 transition-all flex items-center justify-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                                <svg class="w-4 h-4 ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                            </div>
                            <div class="text-left">
                                <div class="text-sm">Lihat Video</div>
                                <div class="text-[10px] text-slate-400 font-normal -mt-0.5">Cara kerjanya</div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Right Map Visualization -->
                <div class="lg:col-span-7 relative min-h-[500px] flex items-center justify-center z-10 hide-animate slide-left">
                    <!-- Abstract Map Background - Using Indonesia Map -->
                    <div class="absolute inset-0 w-full h-full opacity-80 pointer-events-none flex items-center justify-center">
                        <img src="/images/indonesia-map.svg" alt="Map of Indonesia" class="w-full h-full object-contain" width="700" height="500" loading="lazy" decoding="async" style="filter: drop-shadow(0 4px 6px rgba(0,0,0,0.05));" />
                    </div>
                    
                    <!-- Floating Total Alumni Card -->
                    <div class="absolute top-0 right-1/4 sm:right-1/3 bg-white/80 backdrop-blur-md border border-slate-100 rounded-2xl p-4 shadow-xl shadow-slate-200/50 flex items-center gap-4 z-30 animate-[bounce_4s_infinite]">
                        <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-500 font-semibold mb-0.5">Total Alumni</p>
                            <div class="flex items-center gap-2">
                                <p class="text-lg font-black text-slate-900 leading-none">4.782</p>
                                <span class="text-[10px] font-bold text-green-500 bg-green-50 px-1.5 py-0.5 rounded flex items-center gap-0.5"><svg class="w-2 h-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7" /></svg> 12%</span>
                            </div>
                            <p class="text-[9px] text-slate-500 mt-1">dari tahun lalu</p>
                        </div>
                    </div>

                    <!-- Floating Profile Card (Interactive Pop-up) -->
                    <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-4">
                        <div v-if="showAlumniPopup" class="absolute right-0 sm:right-10 top-20 bg-white/90 backdrop-blur-xl border border-slate-100/50 rounded-3xl p-5 shadow-2xl shadow-blue-900/5 w-64 z-40 transition-transform duration-500 hover:-translate-y-2 group">
                            <button @click="showAlumniPopup = false" class="absolute top-3 right-3 text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-full p-1 transition-colors z-50" aria-label="Tutup detail alumni">
                                <X class="w-4 h-4" />
                            </button>
                            <div class="flex flex-col items-center text-center">
                                <!-- Profile Image inside a circular polygon/ring -->
                            <div class="relative w-20 h-20 mb-4">
                                <div class="absolute inset-0 rounded-full border-2 border-dashed border-blue-200 animate-[spin_10s_linear_infinite]"></div>
                                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=10" alt="Fauzan Rachman Avatar" class="absolute inset-1 rounded-full object-cover shadow-md" width="72" height="72" loading="lazy" decoding="async" />
                                <div class="absolute bottom-0 right-0 w-5 h-5 bg-green-500 border-2 border-white rounded-full"></div>
                            </div>
                            <h4 class="font-bold text-slate-900 text-base mb-1">Fauzan Rachman</h4>
                            <span class="bg-blue-50 text-blue-600 text-[10px] font-bold px-2 py-0.5 rounded-full mb-3">Alumni 2021</span>
                            
                            <div class="w-full flex items-center gap-2 text-left mb-2 px-2">
                                <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                <p class="text-[10px] text-slate-600 font-medium leading-tight">Software Engineer<br>at Gojek</p>
                            </div>
                            <div class="w-full flex items-center gap-2 text-left mb-4 px-2">
                                <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <p class="text-[10px] text-slate-600 font-medium">Jakarta, Indonesia</p>
                            </div>
                            
                            <button class="w-full py-2 bg-slate-50 hover:bg-blue-50 text-blue-600 text-xs font-bold rounded-lg transition-colors">Lihat Profil</button>
                        </div>
                        </div>
                    </transition>

                    <!-- Floating Avatars and Numbers on Map -->
                    <!-- Number bubble 1 -->
                    <div class="absolute top-[40%] left-[20%] z-20 animate-[bounce_5s_infinite]">
                        <div class="relative w-12 h-12 bg-blue-500/10 rounded-full flex items-center justify-center backdrop-blur-sm border border-blue-200">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-[10px] font-bold shadow-lg shadow-blue-500/40">243</div>
                        </div>
                    </div>
                    <!-- Number bubble 2 -->
                    <div class="absolute top-[60%] left-[55%] z-20 animate-[bounce_4s_infinite_reverse]">
                        <div class="relative w-14 h-14 bg-green-500/10 rounded-full flex items-center justify-center backdrop-blur-sm border border-green-200">
                            <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white text-[11px] font-bold shadow-lg shadow-green-500/40">532</div>
                        </div>
                    </div>
                    <!-- Number bubble 3 -->
                    <div class="absolute top-[75%] left-[80%] z-20 animate-[bounce_6s_infinite]">
                        <div class="relative w-10 h-10 bg-blue-600/10 rounded-full flex items-center justify-center backdrop-blur-sm border border-blue-300">
                            <div class="w-7 h-7 bg-blue-600 rounded-full flex items-center justify-center text-white text-[9px] font-bold shadow-lg shadow-blue-600/40">815</div>
                        </div>
                    </div>
                    <!-- Number bubble 4 -->
                    <div class="absolute top-[50%] left-[75%] z-20 animate-[bounce_4.5s_infinite]">
                        <div class="relative w-12 h-12 bg-purple-500/10 rounded-full flex items-center justify-center backdrop-blur-sm border border-purple-200">
                            <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center text-white text-[10px] font-bold shadow-lg shadow-purple-500/40">421</div>
                        </div>
                    </div>

                    <!-- Small Floating Avatars -->
                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=20" alt="Alumni avatar" class="absolute top-[30%] left-[30%] w-6 h-6 rounded-full border-2 border-white shadow-md z-20 hover:scale-125 transition-transform cursor-pointer" width="24" height="24" loading="lazy" decoding="async" />
                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=30" alt="Alumni avatar" class="absolute top-[25%] left-[55%] w-7 h-7 rounded-full border-2 border-white shadow-md z-20 hover:scale-125 transition-transform cursor-pointer" width="28" height="28" loading="lazy" decoding="async" />
                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=40" alt="Alumni avatar" class="absolute top-[45%] left-[65%] w-8 h-8 rounded-full border-2 border-white shadow-md z-20 hover:scale-125 transition-transform cursor-pointer" width="32" height="32" loading="lazy" decoding="async" />
                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=50" alt="Alumni avatar" class="absolute top-[35%] left-[45%] w-6 h-6 rounded-full border-2 border-white shadow-md z-20 hover:scale-125 transition-transform cursor-pointer" width="24" height="24" loading="lazy" decoding="async" />
                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=60" alt="Alumni avatar" class="absolute top-[65%] left-[60%] w-7 h-7 rounded-full border-2 border-white shadow-md z-20 hover:scale-125 transition-transform cursor-pointer" width="28" height="28" loading="lazy" decoding="async" />
                    
                    <!-- connecting faint lines -->
                    <svg class="absolute inset-0 w-full h-full pointer-events-none z-10" viewBox="0 0 100 100" preserveAspectRatio="none">
                        <path d="M 20,40 Q 40,20 55,25" fill="none" stroke="#cbd5e1" stroke-width="0.2" stroke-dasharray="1,1" />
                        <path d="M 55,25 Q 70,30 65,45" fill="none" stroke="#cbd5e1" stroke-width="0.2" stroke-dasharray="1,1" />
                        <path d="M 65,45 Q 60,60 55,60" fill="none" stroke="#cbd5e1" stroke-width="0.2" stroke-dasharray="1,1" />
                        <path d="M 55,60 Q 70,70 80,75" fill="none" stroke="#cbd5e1" stroke-width="0.2" stroke-dasharray="1,1" />
                    </svg>
                </div>
            </div>

            <!-- Trust / Partners Section -->
            <div class="text-center mb-10 hide-animate slide-up">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-6">Dipercaya oleh berbagai institusi dan perusahaan</p>
                <div class="flex flex-wrap justify-center gap-8 sm:gap-12 opacity-50 grayscale hover:grayscale-0 transition-all duration-500">
                    <!-- Generic placeholders for partner logos -->
                    <div class="h-8 flex items-center font-black text-xl text-slate-800 tracking-tighter"><span class="text-blue-600">GO</span>JEK</div>
                    <div class="h-8 flex items-center font-black text-xl text-slate-800 tracking-tighter">tokopedia</div>
                    <div class="h-8 flex items-center font-black text-xl text-slate-800 tracking-tighter">traveloka<span class="text-blue-400">.</span></div>
                    <div class="h-8 flex items-center font-black text-xl text-slate-800 tracking-tighter"><span class="text-orange-500">Shopee</span></div>
                    <div class="h-8 flex items-center font-black text-xl text-slate-800 tracking-tighter">Ruang<span class="text-blue-500">guru</span></div>
                </div>
            </div>

            <!-- 4 Stat Boxes -->
            <AlumniStats />

        </div>
    </section>
</template>
