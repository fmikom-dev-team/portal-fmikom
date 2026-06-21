<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const page = usePage();
const siteSettings = computed(() => page.props.siteSettings || {});
const currentYear = new Date().getFullYear();
</script>

<template>
    <footer class="bg-[#f8fafc] flex flex-col items-center mt-20 z-0 overflow-hidden">
        <div class="w-full mx-auto max-w-7xl px-6 lg:px-8 relative z-20 pt-24 pb-2">
            <div class="bg-white rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/80 p-10 lg:p-14">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-12 lg:gap-8">
                    <!-- Brand & Info -->
                    <div class="md:col-span-5 lg:col-span-5">
                        <Link href="/" class="flex items-center gap-2 mb-6">
                            <div 
                                class="flex h-8 w-8 items-center justify-center rounded-[8px] overflow-hidden shrink-0"
                                :class="siteSettings.brand_logo ? 'bg-transparent' : 'bg-black text-white shadow-sm'"
                            >
                                <img v-if="siteSettings.brand_logo" :src="siteSettings.brand_logo" class="h-full w-full object-contain" alt="" />
                                <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18" /></svg>
                            </div>
                            <span class="text-[17px] font-bold tracking-tight text-slate-900">{{ siteSettings.brand_name || 'Portal FMIKOM' }}</span>
                        </Link>
                        <p class="text-[13.5px] leading-[1.7] text-slate-700 mb-8 max-w-[280px] pr-4">
                            Portal informasi akademik, berita, dan layanan mahasiswa terintegrasi yang memudahkan akses informasi perkuliahan Anda.
                        </p>
                        
                        <!-- Social Icons -->
                        <div class="flex items-center gap-5">
                            <!-- X (Twitter) -->
                            <a href="https://x.com/fmikom_" target="_blank" aria-label="X (formerly Twitter)" class="text-slate-700 hover:text-black transition-colors">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" /></svg>
                            </a>
                            <!-- Instagram -->
                            <a href="https://instagram.com/" target="_blank" aria-label="Instagram" class="text-slate-700 hover:text-black transition-colors">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
                            </a>
                            <!-- LinkedIn -->
                            <a href="https://linkedin.com/" target="_blank" aria-label="LinkedIn" class="text-slate-700 hover:text-black transition-colors">
                                <svg class="h-[17px] w-[17px]" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" clip-rule="evenodd" /></svg>
                            </a>
                            <!-- GitHub -->
                            <a href="https://github.com/" target="_blank" aria-label="GitHub" class="text-slate-700 hover:text-black transition-colors">
                                <svg class="h-[18px] w-[18px]" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" /></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Links Grid -->
                    <div class="md:col-span-7 lg:col-span-7 grid grid-cols-3 gap-8">
                        <!-- Navigasi -->
                        <div>
                            <h3 class="text-[14px] font-bold text-slate-900 tracking-tight mb-5">Navigasi</h3>
                            <ul role="list" class="space-y-3.5">
                                <li><Link href="/" class="text-[13px] text-slate-700 hover:text-slate-900 transition-colors">Home</Link></li>
                                <li><Link href="/berita" class="text-[13px] text-slate-700 hover:text-slate-900 transition-colors">Berita</Link></li>
                                <li><Link href="/halaman/tentang-fmikom" class="text-[13px] text-slate-700 hover:text-slate-900 transition-colors">Tentang FMIKOM</Link></li>
                                <li><Link href="/halaman/visi-misi" class="text-[13px] text-slate-700 hover:text-slate-900 transition-colors">Visi & Misi</Link></li>
                            </ul>
                        </div>

                        <!-- Akademik -->
                        <div>
                            <h3 class="text-[14px] font-bold text-slate-900 tracking-tight mb-5">Akademik</h3>
                            <ul role="list" class="space-y-3.5">
                                <li><Link href="/halaman/kalender-akademik" class="text-[13px] text-slate-700 hover:text-slate-900 transition-colors">Kalender Akademik</Link></li>
                                <li><Link href="/halaman/mbkm" class="text-[13px] text-slate-700 hover:text-slate-900 transition-colors">MBKM</Link></li>
                                <li><Link href="/halaman/beasiswa" class="text-[13px] text-slate-700 hover:text-slate-900 transition-colors">Beasiswa</Link></li>
                                <li><Link href="/halaman/pedoman-akademik" class="text-[13px] text-slate-700 hover:text-slate-900 transition-colors">Pedoman Akademik</Link></li>
                            </ul>
                        </div>

                        <!-- Company/Layanan -->
                        <div>
                            <h3 class="text-[14px] font-bold text-slate-900 tracking-tight mb-5">Layanan</h3>
                            <ul role="list" class="space-y-3.5">
                                <li><Link href="/halaman/pengajuan-dokumen" class="text-[13px] text-slate-700 hover:text-slate-900 transition-colors">Surat Menyurat</Link></li>
                                <li><Link href="/halaman/konsultasi-akademik" class="text-[13px] text-slate-700 hover:text-slate-900 transition-colors">Konsultasi</Link></li>
                                <li><Link href="/halaman/faq" class="text-[13px] text-slate-700 hover:text-slate-900 transition-colors">FAQ</Link></li>
                                <li><Link href="#" class="text-[13px] text-slate-700 hover:text-slate-900 transition-colors">Kontak</Link></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Bottom Bar -->
                <div class="mt-14 pt-7 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-[13px] text-slate-700">
                        &copy; {{ currentYear }} {{ siteSettings.brand_name || 'Portal FMIKOM' }}. All rights reserved.
                    </p>
                    <div class="flex gap-5 text-[13px] text-slate-700">
                        <Link href="/privacy-policy" class="hover:text-slate-900 transition-colors underline decoration-slate-300 hover:decoration-slate-400 underline-offset-[3px]">Privacy Policy</Link>
                        <Link href="/terms-of-service" class="hover:text-slate-900 transition-colors underline decoration-slate-300 hover:decoration-slate-400 underline-offset-[3px]">Terms of Service</Link>
                        <Link href="/cookie-policy" class="hover:text-slate-900 transition-colors underline decoration-slate-300 hover:decoration-slate-400 underline-offset-[3px]">Cookie Policy</Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Giant Background Text with Fade Gradient -->
        <div class="relative w-full h-[120px] md:h-[180px] lg:h-[220px] overflow-hidden flex justify-center mt-2">
            <div class="absolute top-0 text-[30vw] md:text-[25vw] lg:text-[20rem] font-black text-slate-300/40 select-none pointer-events-none tracking-tighter leading-none whitespace-nowrap z-10"
                 aria-hidden="true"
                 style="-webkit-mask-image: linear-gradient(to bottom, rgba(0,0,0,1) 0%, rgba(0,0,0,0) 80%); mask-image: linear-gradient(to bottom, rgba(0,0,0,1) 0%, rgba(0,0,0,0) 80%);">
                {{ siteSettings.brand_name || 'FMIKOM' }}
            </div>
        </div>
    </footer>
</template>
