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

            <!-- 3 Columns Grid (Masonry style) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- Column 1 -->
                <div class="flex flex-col gap-6">
                    <template v-for="(item, idx) in col1" :key="item.id || idx">
                        <!-- Dark Card -->
                        <div
                            v-if="item.theme === 'dark'"
                            class="bg-[#18181b] text-white rounded-2xl p-8 flex flex-col justify-between shadow-xl ring-1 ring-white/10 transition-transform duration-300 hover:-translate-y-1 h-full"
                        >
                            <div class="mb-8">
                                <svg class="h-6 w-auto text-white/50 mb-6" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                                <p class="text-xl md:text-[1.35rem] font-medium leading-snug">"{{ item.quote }}"</p>
                            </div>
                            <div class="flex items-center gap-4 mt-auto">
                                <div class="h-11 w-11 rounded-full overflow-hidden shrink-0 border border-white/20 bg-slate-800 flex items-center justify-center">
                                    <img v-if="item.avatar" :src="item.avatar" :alt="item.name" class="h-full w-full object-cover" width="44" height="44" loading="lazy" decoding="async">
                                    <span v-else class="text-sm font-bold text-white">{{ item.name.charAt(0) }}</span>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold">{{ item.name }}</h4>
                                    <p class="text-xs text-gray-400">{{ item.role }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Light Card -->
                        <div
                            v-else
                            class="bg-white text-[#111827] rounded-2xl p-8 flex flex-col shadow-sm ring-1 ring-gray-200/60 transition-transform duration-300 hover:-translate-y-1"
                        >
                            <p class="text-base text-gray-600 mb-8 leading-relaxed">"{{ item.quote }}"</p>
                            <div class="flex items-center gap-4 mt-auto">
                                <div class="h-10 w-10 rounded-full overflow-hidden shrink-0 bg-gray-100 border border-gray-200 flex items-center justify-center">
                                    <img v-if="item.avatar" :src="item.avatar" :alt="item.name" class="h-full w-full object-cover" width="40" height="40" loading="lazy" decoding="async">
                                    <span v-else class="text-xs font-bold text-gray-700">{{ item.name.charAt(0) }}</span>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900">{{ item.name }}</h4>
                                    <p class="text-xs text-gray-500">{{ item.role }}</p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Column 2 -->
                <div class="flex flex-col gap-6">
                    <template v-for="(item, idx) in col2" :key="item.id || idx">
                        <!-- Dark Card -->
                        <div
                            v-if="item.theme === 'dark'"
                            class="bg-[#18181b] text-white rounded-2xl p-8 flex flex-col justify-between shadow-xl ring-1 ring-white/10 transition-transform duration-300 hover:-translate-y-1 h-full"
                        >
                            <div class="mb-8">
                                <svg class="h-6 w-auto text-white/50 mb-6" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                                <p class="text-xl md:text-[1.35rem] font-medium leading-snug">"{{ item.quote }}"</p>
                            </div>
                            <div class="flex items-center gap-4 mt-auto">
                                <div class="h-11 w-11 rounded-full overflow-hidden shrink-0 border border-white/20 bg-slate-800 flex items-center justify-center">
                                    <img v-if="item.avatar" :src="item.avatar" :alt="item.name" class="h-full w-full object-cover" width="44" height="44" loading="lazy" decoding="async">
                                    <span v-else class="text-sm font-bold text-white">{{ item.name.charAt(0) }}</span>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold">{{ item.name }}</h4>
                                    <p class="text-xs text-gray-400">{{ item.role }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Light Card -->
                        <div
                            v-else
                            class="bg-white text-[#111827] rounded-2xl p-8 flex flex-col shadow-sm ring-1 ring-gray-200/60 transition-transform duration-300 hover:-translate-y-1"
                        >
                            <p class="text-base text-gray-600 mb-8 leading-relaxed">"{{ item.quote }}"</p>
                            <div class="flex items-center gap-4 mt-auto">
                                <div class="h-10 w-10 rounded-full overflow-hidden shrink-0 bg-gray-100 border border-gray-200 flex items-center justify-center">
                                    <img v-if="item.avatar" :src="item.avatar" :alt="item.name" class="h-full w-full object-cover" width="40" height="40" loading="lazy" decoding="async">
                                    <span v-else class="text-xs font-bold text-gray-700">{{ item.name.charAt(0) }}</span>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900">{{ item.name }}</h4>
                                    <p class="text-xs text-gray-500">{{ item.role }}</p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Column 3 -->
                <div class="flex flex-col gap-6">
                    <template v-for="(item, idx) in col3" :key="item.id || idx">
                        <!-- Dark Card -->
                        <div
                            v-if="item.theme === 'dark'"
                            class="bg-[#18181b] text-white rounded-2xl p-8 flex flex-col justify-between shadow-xl ring-1 ring-white/10 transition-transform duration-300 hover:-translate-y-1 h-full"
                        >
                            <div class="mb-8">
                                <svg class="h-6 w-auto text-white/50 mb-6" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                                <p class="text-xl md:text-[1.35rem] font-medium leading-snug">"{{ item.quote }}"</p>
                            </div>
                            <div class="flex items-center gap-4 mt-auto">
                                <div class="h-11 w-11 rounded-full overflow-hidden shrink-0 border border-white/20 bg-slate-800 flex items-center justify-center">
                                    <img v-if="item.avatar" :src="item.avatar" :alt="item.name" class="h-full w-full object-cover" width="44" height="44" loading="lazy" decoding="async">
                                    <span v-else class="text-sm font-bold text-white">{{ item.name.charAt(0) }}</span>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold">{{ item.name }}</h4>
                                    <p class="text-xs text-gray-400">{{ item.role }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Light Card -->
                        <div
                            v-else
                            class="bg-white text-[#111827] rounded-2xl p-8 flex flex-col shadow-sm ring-1 ring-gray-200/60 transition-transform duration-300 hover:-translate-y-1"
                        >
                            <p class="text-base text-gray-600 mb-8 leading-relaxed">"{{ item.quote }}"</p>
                            <div class="flex items-center gap-4 mt-auto">
                                <div class="h-10 w-10 rounded-full overflow-hidden shrink-0 bg-gray-100 border border-gray-200 flex items-center justify-center">
                                    <img v-if="item.avatar" :src="item.avatar" :alt="item.name" class="h-full w-full object-cover" width="40" height="40" loading="lazy" decoding="async">
                                    <span v-else class="text-xs font-bold text-gray-700">{{ item.name.charAt(0) }}</span>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900">{{ item.name }}</h4>
                                    <p class="text-xs text-gray-500">{{ item.role }}</p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                
            </div>
        </div>
    </section>
</template>
