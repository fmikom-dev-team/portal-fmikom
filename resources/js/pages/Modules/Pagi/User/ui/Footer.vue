<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const page = usePage();
const siteSettings = computed(() => (page.props as any).siteSettings || {});
const currentYear = computed(() => new Date().getFullYear());
</script>

<template>
    <footer class="w-full bg-[#fcfdfd] dark:bg-zinc-950 border-t border-slate-200/60 dark:border-zinc-900/60 pt-16 pb-32 md:pb-12 mt-20 transition-all select-none">
        <div class="mx-auto max-w-[1200px] px-6 lg:px-8 space-y-16">
            
            <!-- ─── 2. FOOTER MIDDLE COLUMNS ──────────────────────────────────────── -->
            <div class="grid grid-cols-1 gap-10 md:grid-cols-4 pt-4 text-left">
                
                <!-- Col 1: Brand details -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <!-- PG Logo Minimalist -->
                        <div class="h-8 w-8 rounded-xl bg-slate-900 dark:bg-slate-50 flex items-center justify-center shadow-sm">
                            <span class="text-[14px] font-black text-white dark:text-slate-950 tracking-tight">P</span>
                        </div>
                        <div>
                            <span class="text-[13px] font-black text-slate-800 dark:text-zinc-100 uppercase tracking-wider">PAGI</span>
                            <span class="text-[9px] block font-black text-slate-400 dark:text-zinc-500 tracking-widest leading-none">{{ siteSettings.brand_name || 'FMIKOM' }}</span>
                        </div>
                    </div>
                    <p class="text-[11.5px] leading-relaxed text-slate-450 dark:text-zinc-400 font-medium">
                        Portal portofolio & galeri showcase karya kreatif terbesar mahasiswa Fakultas Ilmu Komputer.
                    </p>
                </div>

                <!-- Col 2: Kategori (Behance-like Company links) -->
                <div class="space-y-3">
                    <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-450 dark:text-zinc-500">Program</h4>
                    <ul class="space-y-2.5">
                        <li>
                            <Link href="/pagi" class="text-[12px] font-semibold text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                                Jelajah Karya
                            </Link>
                        </li>
                        <li>
                            <Link href="/pagi/people" class="text-[12px] font-semibold text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                                Direktori Mahasiswa
                            </Link>
                        </li>
                        <li>
                            <Link href="/pagi/profile" class="text-[12px] font-semibold text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white transition-colors flex items-center gap-1">
                                Partner Program 
                                <svg class="w-2.5 h-2.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" />
                                </svg>
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Col 3: Socials links with inline arrows -->
                <div class="space-y-3">
                    <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-450 dark:text-zinc-500">Tautan Resmi</h4>
                    <ul class="space-y-2.5">
                        <li>
                            <a href="https://fmikom.suntree.my.id" target="_blank" class="text-[12px] font-semibold text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white transition-colors flex items-center gap-1">
                                Website FMIKOM 
                                <svg class="w-2.5 h-2.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" />
                                </svg>
                            </a>
                        </li>
                        <li>
                            <a href="https://github.com" target="_blank" class="text-[12px] font-semibold text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white transition-colors flex items-center gap-1">
                                GitHub 
                                <svg class="w-2.5 h-2.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" />
                                </svg>
                            </a>
                        </li>
                        <li>
                            <a href="https://linkedin.com" target="_blank" class="text-[12px] font-semibold text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white transition-colors flex items-center gap-1">
                                LinkedIn 
                                <svg class="w-2.5 h-2.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" />
                                </svg>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Col 4: Newsletter pill-shaped input -->
                <div class="space-y-3">
                    <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-450 dark:text-zinc-500">Newsletter</h4>
                    <p class="text-[11.5px] leading-relaxed text-slate-450 dark:text-zinc-400 font-medium">
                        Dapatkan kompilasi karya terbaik mahasiswa dan pembaruan rekrutmen terbaru.
                    </p>
                    
                    <!-- Sleek pill-shaped nested input -->
                    <div class="relative w-full h-[46px] rounded-full border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 flex items-center pl-4 pr-1">
                        <span class="text-slate-400 dark:text-zinc-650 text-[13px] font-semibold select-none pr-1.5">@</span>
                        <input 
                            type="email" 
                            placeholder="Masukkan email Anda..." 
                            class="flex-1 bg-transparent border-none outline-none text-[12px] font-semibold text-slate-800 dark:text-white placeholder-slate-400/80 w-full"
                        />
                        <!-- Nested circular arrow button -->
                        <button class="w-[38px] h-[38px] rounded-full bg-slate-950 dark:bg-slate-50 text-white dark:text-slate-950 flex items-center justify-center hover:bg-slate-800 dark:hover:bg-slate-200 transition-colors shadow-sm" aria-label="Subscribe to newsletter">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ─── 3. BOTTOM COPYRIGHT & LOGOS BAR ────────────────────────────────── -->
            <div class="pt-8 border-t border-slate-100 dark:border-zinc-900 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-left">
                    <p class="text-[11px] font-semibold text-slate-400 dark:text-zinc-500">
                        &copy; {{ currentYear }} PAGI {{ siteSettings.brand_name || 'FMIKOM' }} • Hak Cipta Dilindungi • Made with Faculty of Computer Science
                    </p>
                </div>
            </div>
            
        </div>
    </footer>
</template>
