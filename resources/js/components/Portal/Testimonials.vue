<template>
    <!-- APA KATA MEREKA (TESTIMONIALS) -->
    <section class="hide-animate relative overflow-hidden bg-linear-to-b from-white to-gray-50/80 py-24 fade-in">
        <!-- decorative background gradients to match the FMIKOM modern aesthetic slightly -->
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,var(--tw-gradient-stops))] from-blue-50/30 via-transparent to-transparent pointer-events-none"></div>
        
        <div class="relative z-10 mx-auto max-w-7xl px-4 xl:px-0">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl lg:text-[3.25rem] font-extrabold tracking-tight text-[#111827] leading-tight max-w-3xl mx-auto">
                    Apa Kata Mereka
                </h2>
            </div>

            <!-- 3 Columns Grid (Masonry style) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- Column 1 -->
                <div class="flex flex-col gap-6">
                    <!-- Dark Card (Tall) -->
                    <div class="bg-[#18181b] text-white rounded-2xl p-8 flex flex-col justify-between shadow-xl ring-1 ring-white/10 transition-transform duration-300 hover:-translate-y-1">
                        <div class="mb-8">
                            <svg class="h-6 w-auto text-white/50 mb-6" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                            <p class="text-xl md:text-[1.35rem] font-medium leading-snug">"Sistem FAST benar-benar mengubah cara saya mengajukan persuratan. Dulu butuh 3 hari, sekarang hanya hitungan jam sudah disetujui Kaprodi!"</p>
                        </div>
                        <div class="flex items-center gap-4 mt-auto">
                            <div class="h-11 w-11 rounded-full overflow-hidden shrink-0 border border-white/20">
                                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=a042581f4e29026024d" alt="Andi Saputra" class="h-full w-full object-cover" width="44" height="44" loading="lazy" decoding="async">
                            </div>
                            <div>
                                <h4 class="text-sm font-bold">Andi Saputra</h4>
                                <p class="text-xs text-gray-400">Mahasiswa Semester 6</p>
                            </div>
                        </div>
                    </div>

                    <!-- Light Card (Short) -->
                    <div class="bg-white text-[#111827] rounded-2xl p-8 flex flex-col shadow-sm ring-1 ring-gray-200/60 transition-transform duration-300 hover:-translate-y-1">
                        <p class="text-base text-gray-600 mb-8 leading-relaxed">"Sistem administrasi menjadi sangat transparan. Saya bisa melacak setiap proses dokumen dengan mudah tanpa harus bolak-balik ke tata usaha."</p>
                        <div class="flex items-center gap-4 mt-auto">
                            <div class="h-10 w-10 rounded-full overflow-hidden shrink-0 bg-gray-100">
                                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=a042581f4e29026011d" alt="Rizky Pratama" class="h-full w-full object-cover" width="40" height="40" loading="lazy" decoding="async">
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-gray-900">Rizky Pratama</h4>
                                <p class="text-xs text-gray-500">Ketua BEM FMIKOM</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column 2 -->
                <div class="flex flex-col gap-6">
                    <!-- Light Card (Short) -->
                    <div class="bg-white text-[#111827] rounded-2xl p-8 flex flex-col shadow-sm ring-1 ring-gray-200/60 transition-transform duration-300 hover:-translate-y-1">
                        <p class="text-base text-gray-600 mb-8 leading-relaxed">"Sebagai dosen pembimbing, memantau logbook magang mahasiswa via WIMS sangat menghemat waktu. Semua terpusat, real-time, dan mudah diakses dari mana saja."</p>
                        <div class="flex items-center gap-4 mt-auto">
                            <div class="h-10 w-10 rounded-full overflow-hidden shrink-0 bg-gray-100">
                                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=a042581f4e29026704d" alt="Dr. Budi Santoso" class="h-full w-full object-cover" width="40" height="40" loading="lazy" decoding="async">
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-gray-900">Dr. Budi Santoso, M.Kom</h4>
                                <p class="text-xs text-gray-500">Dosen Pembimbing</p>
                            </div>
                        </div>
                    </div>

                    <!-- Light Card (Short) -->
                    <div class="bg-white text-[#111827] rounded-2xl p-8 flex flex-col shadow-sm ring-1 ring-gray-200/60 transition-transform duration-300 hover:-translate-y-1">
                        <p class="text-base text-gray-600 mb-8 leading-relaxed">"Pengajuan judul skripsi dan pencarian dosen pembimbing jadi lebih terstruktur berkat modul bimbingan akademik di portal ini."</p>
                        <div class="flex items-center gap-4 mt-auto">
                            <div class="h-10 w-10 rounded-full overflow-hidden shrink-0 bg-gray-100">
                                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=a042581f4e29026715d" alt="Dina Aulia" class="h-full w-full object-cover" width="40" height="40" loading="lazy" decoding="async">
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-gray-900">Dina Aulia</h4>
                                <p class="text-xs text-gray-500">Mahasiswa Tingkat Akhir</p>
                            </div>
                        </div>
                    </div>

                    <!-- Light Card (Short) -->
                    <div class="bg-white text-[#111827] rounded-2xl p-8 flex flex-col shadow-sm ring-1 ring-gray-200/60 transition-transform duration-300 hover:-translate-y-1">
                        <p class="text-base text-gray-600 mb-8 leading-relaxed">"Birokrasi kampus yang selama ini kompleks, kini dapat diselesaikan hanya dengan beberapa kali klik. Transformasi digital yang luar biasa."</p>
                        <div class="flex items-center gap-4 mt-auto">
                            <div class="h-10 w-10 rounded-full overflow-hidden shrink-0 bg-gray-100">
                                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=a042581f4e29026725d" alt="Prof. Herman" class="h-full w-full object-cover" width="40" height="40" loading="lazy" decoding="async">
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-gray-900">Prof. Herman</h4>
                                <p class="text-xs text-gray-500">Dekan FMIKOM</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column 3 -->
                <div class="flex flex-col gap-6">
                    <!-- Light Card (Short) -->
                    <div class="bg-white text-[#111827] rounded-2xl p-8 flex flex-col shadow-sm ring-1 ring-gray-200/60 transition-transform duration-300 hover:-translate-y-1">
                        <p class="text-base text-gray-600 mb-8 leading-relaxed">"Sangat mudah memonitor mahasiswa magang dari perusahaan kami. Form penilaian langsung tersedia online dan sistemnya sangat responsif."</p>
                        <div class="flex items-center gap-4 mt-auto">
                            <div class="h-10 w-10 rounded-full overflow-hidden shrink-0 bg-gray-100">
                                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=a04258a2462d826729d" alt="Anton Setiawan" class="h-full w-full object-cover" width="40" height="40" loading="lazy" decoding="async">
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-gray-900">Anton Setiawan</h4>
                                <p class="text-xs text-gray-500">HR Director, TechNesia</p>
                            </div>
                        </div>
                    </div>

                    <!-- Dark Card (Tall) -->
                    <div class="bg-[#18181b] text-white rounded-2xl p-8 flex flex-col justify-between shadow-xl ring-1 ring-white/10 transition-transform duration-300 hover:-translate-y-1 h-full">
                        <div class="mb-8">
                            <svg class="h-6 w-auto text-white/50 mb-6" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                            <p class="text-xl md:text-[1.35rem] font-medium leading-snug">"Saya mendapat pekerjaan pertama saya karena profil portofolio yang saya bangun dan tracer study terhubung langsung oleh mitra kerja sama fakultas FMIKOM."</p>
                        </div>
                        <div class="flex items-center gap-4 mt-auto">
                            <div class="h-11 w-11 rounded-full overflow-hidden shrink-0 border border-white/20">
                                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=a04258a2462d826712d" alt="Siti Rahmawati" class="h-full w-full object-cover" width="44" height="44" loading="lazy" decoding="async">
                            </div>
                            <div>
                                <h4 class="text-sm font-bold">Siti Rahmawati</h4>
                                <p class="text-xs text-gray-400">Alumni Angkatan 2022</p>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </section>
</template>
