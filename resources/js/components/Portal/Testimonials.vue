<script setup lang="ts">
import { computed } from "vue";

export interface TestimonialItem {
    id?: string;
    name: string;
    role: string;
    quote: string;
    avatar?: string;
    theme?: "dark" | "light";
}

const props = withDefaults(
    defineProps<{
        title?: string;
        subtitle?: string;
        items?: TestimonialItem[];
    }>(),
    {
        title: "Apa Kata Mereka",
        subtitle: "",
        items: () => [],
    }
);

const defaultTestimonials: TestimonialItem[] = [
    {
        id: "1",
        quote: "Sistem FAST benar-benar mengubah cara saya mengajukan persuratan. Dulu butuh 3 hari, sekarang hanya hitungan jam sudah disetujui Kaprodi!",
        name: "Andi Saputra",
        role: "Mahasiswa Semester 6",
        avatar: "https://api.dicebear.com/7.x/avataaars/svg?seed=a042581f4e29026024d",
        theme: "dark",
    },
    {
        id: "2",
        quote: "Sistem administrasi menjadi sangat transparan. Saya bisa melacak setiap proses dokumen dengan mudah tanpa harus bolak-balik ke tata usaha.",
        name: "Rizky Pratama",
        role: "Ketua BEM FMIKOM",
        avatar: "https://api.dicebear.com/7.x/avataaars/svg?seed=a042581f4e29026011d",
        theme: "light",
    },
    {
        id: "3",
        quote: "Sebagai dosen pembimbing, memantau logbook magang mahasiswa via WIMS sangat menghemat waktu. Semua terpusat, real-time, dan mudah diakses dari mana saja.",
        name: "Dr. Budi Santoso, M.Kom",
        role: "Dosen Pembimbing",
        avatar: "https://api.dicebear.com/7.x/avataaars/svg?seed=a042581f4e29026704d",
        theme: "light",
    },
    {
        id: "4",
        quote: "Pengajuan judul skripsi dan pencarian dosen pembimbing jadi lebih terstruktur berkat modul bimbingan akademik di portal ini.",
        name: "Dina Aulia",
        role: "Mahasiswa Tingkat Akhir",
        avatar: "https://api.dicebear.com/7.x/avataaars/svg?seed=a042581f4e29026715d",
        theme: "light",
    },
    {
        id: "5",
        quote: "Birokrasi kampus yang selama ini kompleks, kini dapat diselesaikan hanya dengan beberapa kali klik. Transformasi digital yang luar biasa.",
        name: "Prof. Herman",
        role: "Dekan FMIKOM",
        avatar: "https://api.dicebear.com/7.x/avataaars/svg?seed=a042581f4e29026725d",
        theme: "light",
    },
    {
        id: "6",
        quote: "Sangat mudah memonitor mahasiswa magang dari perusahaan kami. Form penilaian langsung tersedia online dan sistemnya sangat responsif.",
        name: "Anton Setiawan",
        role: "HR Director, TechNesia",
        avatar: "https://api.dicebear.com/7.x/avataaars/svg?seed=a04258a2462d826729d",
        theme: "light",
    },
    {
        id: "7",
        quote: "Saya mendapat pekerjaan pertama saya karena profil portofolio yang saya bangun dan tracer study terhubung langsung oleh mitra kerja sama fakultas FMIKOM.",
        name: "Siti Rahmawati",
        role: "Alumni Angkatan 2022",
        avatar: "https://api.dicebear.com/7.x/avataaars/svg?seed=a04258a2462d826712d",
        theme: "dark",
    },
];

const activeItems = computed(() => {
    if (props.items && props.items.length > 0) {
        return props.items;
    }
    return defaultTestimonials;
});

const col1 = computed(() => activeItems.value.filter((_, i) => i % 3 === 0));
const col2 = computed(() => activeItems.value.filter((_, i) => i % 3 === 1));
const col3 = computed(() => activeItems.value.filter((_, i) => i % 3 === 2));
</script>

<template>
    <!-- APA KATA MEREKA (TESTIMONIALS) -->
    <section class="hide-animate relative overflow-hidden bg-linear-to-b from-white to-gray-50/80 py-24 fade-in">
        <!-- decorative background gradients -->
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,var(--tw-gradient-stops))] from-blue-50/30 via-transparent to-transparent pointer-events-none"></div>
        
        <div class="relative z-10 mx-auto max-w-7xl px-4 xl:px-0">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl lg:text-[3.25rem] font-extrabold tracking-tight text-[#111827] leading-tight max-w-3xl mx-auto">
                    {{ title || 'Apa Kata Mereka' }}
                </h2>
                <p v-if="subtitle" class="mt-4 text-slate-500 text-base md:text-lg max-w-2xl mx-auto font-normal">
                    {{ subtitle }}
                </p>
            </div>

            <!-- Dynamic Responsive Grid (1, 2, or 3+ Masonry Columns) -->
            <div :class="[
                'grid gap-6',
                activeItems.length === 1 ? 'grid-cols-1 max-w-xl mx-auto' :
                activeItems.length === 2 ? 'grid-cols-1 md:grid-cols-2 max-w-4xl mx-auto' :
                'grid-cols-1 md:grid-cols-2 lg:grid-cols-3'
            ]">
                
                <!-- Column 1 -->
                <div v-if="col1.length > 0" class="flex flex-col gap-6">
                    <template v-for="(item, idx) in col1" :key="item.id || idx">
                        <!-- Dark Card -->
                        <div
                            v-if="item.theme === 'dark'"
                            class="bg-[#0f172a] text-white rounded-3xl p-8 flex flex-col justify-between shadow-xl ring-1 ring-white/10 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl h-full relative overflow-hidden group"
                        >
                            <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-500/10 rounded-full blur-2xl pointer-events-none group-hover:bg-blue-500/20 transition-all"></div>
                            <div class="mb-6 relative z-10">
                                <svg class="h-6 w-auto text-blue-400 mb-5 opacity-80" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                                <p class="text-[1.05rem] md:text-[1.125rem] font-semibold text-slate-100 leading-relaxed">"{{ item.quote }}"</p>
                            </div>
                            <div class="flex items-center gap-3.5 mt-auto pt-6 border-t border-white/10 relative z-10">
                                <div class="h-11 w-11 rounded-full overflow-hidden shrink-0 border border-white/20 bg-slate-800 flex items-center justify-center shadow-xs">
                                    <img v-if="item.avatar" :src="item.avatar" :alt="item.name" class="h-full w-full object-cover" width="44" height="44" loading="lazy" decoding="async" @error="(e) => (e.target as HTMLElement).style.display = 'none'">
                                    <span v-else class="text-xs font-black text-white">{{ item.name.charAt(0) }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-[13.5px] font-bold text-white truncate">{{ item.name }}</h4>
                                    <p class="text-[11.5px] font-medium text-blue-300 truncate">{{ item.role }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Light Card -->
                        <div
                            v-else
                            class="bg-white text-[#111827] rounded-3xl p-8 flex flex-col justify-between shadow-sm ring-1 ring-slate-200/80 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl h-full relative overflow-hidden group"
                        >
                            <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-50/50 rounded-full blur-2xl pointer-events-none group-hover:bg-blue-100/50 transition-all"></div>
                            <div class="mb-6 relative z-10">
                                <svg class="h-6 w-auto text-blue-600 mb-5 opacity-80" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                                <p class="text-[1.05rem] md:text-[1.125rem] font-semibold text-slate-800 leading-relaxed">"{{ item.quote }}"</p>
                            </div>
                            <div class="flex items-center gap-3.5 mt-auto pt-6 border-t border-slate-100 relative z-10">
                                <div class="h-11 w-11 rounded-full overflow-hidden shrink-0 bg-blue-50 border border-blue-100 flex items-center justify-center shadow-xs">
                                    <img v-if="item.avatar" :src="item.avatar" :alt="item.name" class="h-full w-full object-cover" width="44" height="44" loading="lazy" decoding="async" @error="(e) => (e.target as HTMLElement).style.display = 'none'">
                                    <span v-else class="text-xs font-black text-blue-600">{{ item.name.charAt(0) }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-[13.5px] font-bold text-slate-900 truncate">{{ item.name }}</h4>
                                    <p class="text-[11.5px] font-medium text-blue-600 truncate">{{ item.role }}</p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Column 2 -->
                <div v-if="col2.length > 0" class="flex flex-col gap-6">
                    <template v-for="(item, idx) in col2" :key="item.id || idx">
                        <!-- Dark Card -->
                        <div
                            v-if="item.theme === 'dark'"
                            class="bg-[#0f172a] text-white rounded-3xl p-8 flex flex-col justify-between shadow-xl ring-1 ring-white/10 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl h-full relative overflow-hidden group"
                        >
                            <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-500/10 rounded-full blur-2xl pointer-events-none group-hover:bg-blue-500/20 transition-all"></div>
                            <div class="mb-6 relative z-10">
                                <svg class="h-6 w-auto text-blue-400 mb-5 opacity-80" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                                <p class="text-[1.05rem] md:text-[1.125rem] font-semibold text-slate-100 leading-relaxed">"{{ item.quote }}"</p>
                            </div>
                            <div class="flex items-center gap-3.5 mt-auto pt-6 border-t border-white/10 relative z-10">
                                <div class="h-11 w-11 rounded-full overflow-hidden shrink-0 border border-white/20 bg-slate-800 flex items-center justify-center shadow-xs">
                                    <img v-if="item.avatar" :src="item.avatar" :alt="item.name" class="h-full w-full object-cover" width="44" height="44" loading="lazy" decoding="async" @error="(e) => (e.target as HTMLElement).style.display = 'none'">
                                    <span v-else class="text-xs font-black text-white">{{ item.name.charAt(0) }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-[13.5px] font-bold text-white truncate">{{ item.name }}</h4>
                                    <p class="text-[11.5px] font-medium text-blue-300 truncate">{{ item.role }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Light Card -->
                        <div
                            v-else
                            class="bg-white text-[#111827] rounded-3xl p-8 flex flex-col justify-between shadow-sm ring-1 ring-slate-200/80 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl h-full relative overflow-hidden group"
                        >
                            <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-50/50 rounded-full blur-2xl pointer-events-none group-hover:bg-blue-100/50 transition-all"></div>
                            <div class="mb-6 relative z-10">
                                <svg class="h-6 w-auto text-blue-600 mb-5 opacity-80" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                                <p class="text-[1.05rem] md:text-[1.125rem] font-semibold text-slate-800 leading-relaxed">"{{ item.quote }}"</p>
                            </div>
                            <div class="flex items-center gap-3.5 mt-auto pt-6 border-t border-slate-100 relative z-10">
                                <div class="h-11 w-11 rounded-full overflow-hidden shrink-0 bg-blue-50 border border-blue-100 flex items-center justify-center shadow-xs">
                                    <img v-if="item.avatar" :src="item.avatar" :alt="item.name" class="h-full w-full object-cover" width="44" height="44" loading="lazy" decoding="async" @error="(e) => (e.target as HTMLElement).style.display = 'none'">
                                    <span v-else class="text-xs font-black text-blue-600">{{ item.name.charAt(0) }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-[13.5px] font-bold text-slate-900 truncate">{{ item.name }}</h4>
                                    <p class="text-[11.5px] font-medium text-blue-600 truncate">{{ item.role }}</p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Column 3 -->
                <div v-if="col3.length > 0" class="flex flex-col gap-6">
                    <template v-for="(item, idx) in col3" :key="item.id || idx">
                        <!-- Dark Card -->
                        <div
                            v-if="item.theme === 'dark'"
                            class="bg-[#0f172a] text-white rounded-3xl p-8 flex flex-col justify-between shadow-xl ring-1 ring-white/10 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl h-full relative overflow-hidden group"
                        >
                            <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-500/10 rounded-full blur-2xl pointer-events-none group-hover:bg-blue-500/20 transition-all"></div>
                            <div class="mb-6 relative z-10">
                                <svg class="h-6 w-auto text-blue-400 mb-5 opacity-80" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                                <p class="text-[1.05rem] md:text-[1.125rem] font-semibold text-slate-100 leading-relaxed">"{{ item.quote }}"</p>
                            </div>
                            <div class="flex items-center gap-3.5 mt-auto pt-6 border-t border-white/10 relative z-10">
                                <div class="h-11 w-11 rounded-full overflow-hidden shrink-0 border border-white/20 bg-slate-800 flex items-center justify-center shadow-xs">
                                    <img v-if="item.avatar" :src="item.avatar" :alt="item.name" class="h-full w-full object-cover" width="44" height="44" loading="lazy" decoding="async" @error="(e) => (e.target as HTMLElement).style.display = 'none'">
                                    <span v-else class="text-xs font-black text-white">{{ item.name.charAt(0) }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-[13.5px] font-bold text-white truncate">{{ item.name }}</h4>
                                    <p class="text-[11.5px] font-medium text-blue-300 truncate">{{ item.role }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Light Card -->
                        <div
                            v-else
                            class="bg-white text-[#111827] rounded-3xl p-8 flex flex-col justify-between shadow-sm ring-1 ring-slate-200/80 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl h-full relative overflow-hidden group"
                        >
                            <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-50/50 rounded-full blur-2xl pointer-events-none group-hover:bg-blue-100/50 transition-all"></div>
                            <div class="mb-6 relative z-10">
                                <svg class="h-6 w-auto text-blue-600 mb-5 opacity-80" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                                <p class="text-[1.05rem] md:text-[1.125rem] font-semibold text-slate-800 leading-relaxed">"{{ item.quote }}"</p>
                            </div>
                            <div class="flex items-center gap-3.5 mt-auto pt-6 border-t border-slate-100 relative z-10">
                                <div class="h-11 w-11 rounded-full overflow-hidden shrink-0 bg-blue-50 border border-blue-100 flex items-center justify-center shadow-xs">
                                    <img v-if="item.avatar" :src="item.avatar" :alt="item.name" class="h-full w-full object-cover" width="44" height="44" loading="lazy" decoding="async" @error="(e) => (e.target as HTMLElement).style.display = 'none'">
                                    <span v-else class="text-xs font-black text-blue-600">{{ item.name.charAt(0) }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-[13.5px] font-bold text-slate-900 truncate">{{ item.name }}</h4>
                                    <p class="text-[11.5px] font-medium text-blue-600 truncate">{{ item.role }}</p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                
            </div>
        </div>
    </section>
</template>
