<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3";
import { ArrowLeft, Check, Sparkles } from "lucide-vue-next";
import Navbar from "../../ui/Navbar.vue";

const templatesList = [
	{
		id: "ats-professional",
		title: "ATS Professional",
		description:
			"Layout satu kolom standar industri yang dioptimalkan untuk kelulusan ATS. Ideal untuk melamar ke perusahaan multinasional dan BUMN.",
		category: "ATS Friendly",
		accentColor: "bg-blue-600",
		badge: "Paling Populer",
		svgLayout: "ats",
	},
	{
		id: "modern-sidebar",
		title: "Modern Sidebar",
		description:
			"Layout dua kolom yang memisahkan informasi kontak dan keahlian di sisi kiri, menyisakan ruang luas untuk riwayat kerja di kanan.",
		category: "Modern & Creative",
		accentColor: "bg-sky-500",
		badge: "Rekomendasi",
		svgLayout: "sidebar",
	},
	{
		id: "executive",
		title: "Executive Classic",
		description:
			"Desain elegan berpusat pada kepemimpinan, menonjolkan ringkasan eksekutif dan rekam jejak profesional tingkat senior.",
		category: "Formal",
		accentColor: "bg-zinc-850",
		badge: "Premium",
		svgLayout: "executive",
	},
	{
		id: "creative-minimal",
		title: "Creative Minimalist",
		description:
			"Kombinasi garis minimalis dengan sentuhan warna artistik. Cocok untuk industri kreatif, desainer, dan bidang seni.",
		category: "Minimalis",
		accentColor: "bg-purple-650",
		badge: "Trend",
		svgLayout: "creative",
	},
	{
		id: "student-resume",
		title: "Student Resume",
		description:
			"Didesain khusus untuk mahasiswa atau lulusan baru, menekankan pendidikan, organisasi kemahasiswaan, proyek akademik, dan sertifikasi.",
		category: "Akademik",
		accentColor: "bg-emerald-500",
		badge: "Khusus Mahasiswa",
		svgLayout: "student",
	},
	{
		id: "custom",
		title: "Custom Bebas",
		description:
			"Pilih sendiri section apa saja yang ingin kamu tampilkan. Fleksibel 100% — cocok untuk kebutuhan unik yang tidak sesuai template lain.",
		category: "Kustom",
		accentColor: "bg-indigo-600",
		badge: "Bebas Atur",
		svgLayout: "custom",
	},
];

const selectTemplate = (templateId: string) => {
	router.post("/pagi/cv", {
		template_id: templateId,
		title: `CV Baru - ${templateId
			.split("-")
			.map((w) => w.charAt(0).toUpperCase() + w.slice(1))
			.join(" ")}`,
	});
};
</script>

<template>
    <Head>
        <title>Pilih Template CV — PAGI</title>
    </Head>

    <div class="min-h-screen bg-slate-50 dark:bg-zinc-950 font-sans text-slate-900 dark:text-zinc-100">
        <Navbar />

        <main class="max-w-6xl mx-auto px-6 py-10">
            <!-- Back Button & Nav -->
            <div class="mb-8">
                <Link
                    href="/pagi/cv"
                    class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 dark:text-zinc-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
                >
                    <ArrowLeft class="w-4 h-4" />
                    Kembali ke Dashboard
                </Link>
            </div>

            <!-- Page Intro Title -->
            <div class="text-center max-w-2xl mx-auto space-y-4 mb-14">
                <h1 class="text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight leading-tight">
                    Pilih Template CV Anda
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-zinc-400">
                    Setiap template telah dirancang dengan memperhatikan estetika visual premium dan keterbacaan yang optimal bagi perekrut.
                </p>
            </div>

            <!-- Templates Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <div 
                    v-for="tpl in templatesList" 
                    :key="tpl.id" 
                    class="bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800/80 hover:border-indigo-500/50 dark:hover:border-indigo-500/50 rounded-xl shadow-xs overflow-hidden transition-all duration-300 flex flex-col group"
                >
                    <!-- Template Preview Graphics -->
                    <div class="aspect-[4/3] bg-slate-100 dark:bg-zinc-950 p-6 flex items-center justify-center border-b border-slate-100 dark:border-zinc-800 relative overflow-hidden group-hover:bg-slate-50/50 dark:group-hover:bg-zinc-900/40 transition-colors">
                        <!-- Badge on preview -->
                        <span class="absolute top-4 right-4 px-2.5 py-0.5 rounded-full text-[9px] font-extrabold bg-slate-900 text-white dark:bg-white dark:text-slate-900 uppercase tracking-wider select-none z-10">
                            {{ tpl.badge }}
                        </span>

                        <!-- DYNAMIC SVG PREVIEW GRAPHICS -->
                        <div class="w-44 h-56 bg-white dark:bg-zinc-900 rounded-lg shadow-lg border border-slate-200/50 dark:border-zinc-800 p-4 overflow-hidden relative select-none transform group-hover:scale-[1.03] transition-transform duration-300">
                            
                            <!-- ATS PROFESSIONAL PREVIEW -->
                            <div v-if="tpl.svgLayout === 'ats'" class="space-y-3">
                                <!-- Header lines -->
                                <div class="space-y-1">
                                    <div class="w-1/2 h-2.5 bg-blue-600 rounded"></div>
                                    <div class="w-1/3 h-1.5 bg-slate-300 dark:bg-zinc-700 rounded"></div>
                                    <div class="w-3/4 h-1 bg-slate-200 dark:bg-zinc-800 rounded"></div>
                                </div>
                                <div class="h-0.5 bg-blue-600/30"></div>
                                <!-- Section 1 -->
                                <div class="space-y-1.5">
                                    <div class="w-1/4 h-2 bg-slate-400 dark:bg-zinc-650 rounded"></div>
                                    <div class="space-y-1">
                                        <div class="w-full h-1 bg-slate-200 dark:bg-zinc-800 rounded"></div>
                                        <div class="w-5/6 h-1 bg-slate-200 dark:bg-zinc-800 rounded"></div>
                                    </div>
                                </div>
                                <!-- Section 2 -->
                                <div class="space-y-1.5">
                                    <div class="w-1/4 h-2 bg-slate-400 dark:bg-zinc-650 rounded"></div>
                                    <div class="space-y-1">
                                        <div class="flex justify-between">
                                            <div class="w-1/3 h-1.5 bg-slate-300 dark:bg-zinc-700 rounded"></div>
                                            <div class="w-1/6 h-1.5 bg-slate-200 dark:bg-zinc-800 rounded"></div>
                                        </div>
                                        <div class="w-full h-1 bg-slate-200 dark:bg-zinc-800 rounded"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- MODERN SIDEBAR PREVIEW -->
                            <div v-else-if="tpl.svgLayout === 'sidebar'" class="h-full flex gap-3">
                                <!-- Left side -->
                                <div class="w-1/3 border-r border-slate-200 dark:border-zinc-800 pr-2 space-y-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-zinc-800 mx-auto"></div>
                                    <div class="space-y-1">
                                        <div class="w-full h-1.5 bg-sky-500 rounded"></div>
                                        <div class="w-3/4 h-1 bg-slate-200 dark:bg-zinc-800 rounded mx-auto"></div>
                                    </div>
                                    <div class="space-y-1.5">
                                        <div class="w-5/6 h-1.5 bg-slate-300 dark:bg-zinc-750 rounded"></div>
                                        <div class="w-full h-1 bg-slate-200 dark:bg-zinc-800 rounded"></div>
                                        <div class="w-full h-1 bg-slate-200 dark:bg-zinc-800 rounded"></div>
                                    </div>
                                </div>
                                <!-- Right side -->
                                <div class="flex-1 space-y-3">
                                    <div class="space-y-1.5">
                                        <div class="w-1/2 h-2 bg-sky-500 rounded"></div>
                                        <div class="w-full h-1.5 bg-slate-300 dark:bg-zinc-750 rounded"></div>
                                        <div class="w-5/6 h-1 bg-slate-200 dark:bg-zinc-800 rounded"></div>
                                    </div>
                                    <div class="space-y-1.5">
                                        <div class="w-1/2 h-2 bg-sky-500 rounded"></div>
                                        <div class="w-full h-1.5 bg-slate-300 dark:bg-zinc-750 rounded"></div>
                                        <div class="w-5/6 h-1 bg-slate-200 dark:bg-zinc-800 rounded"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- EXECUTIVE PREVIEW -->
                            <div v-else-if="tpl.svgLayout === 'executive'" class="space-y-3 text-center">
                                <div class="space-y-1.5 pt-2">
                                    <div class="w-2/3 h-3 bg-zinc-850 rounded mx-auto"></div>
                                    <div class="w-1/2 h-1.5 bg-slate-300 dark:bg-zinc-700 rounded mx-auto italic"></div>
                                    <div class="w-5/6 h-1 bg-slate-200 dark:bg-zinc-800 rounded mx-auto"></div>
                                </div>
                                <div class="h-px bg-zinc-400 dark:bg-zinc-750"></div>
                                <div class="space-y-2 text-left pt-1">
                                    <div class="w-1/4 h-2 bg-zinc-700 dark:bg-zinc-500 rounded"></div>
                                    <div class="space-y-1">
                                        <div class="w-full h-1.5 bg-slate-200 dark:bg-zinc-750 rounded"></div>
                                        <div class="w-11/12 h-1 bg-slate-200 dark:bg-zinc-800 rounded"></div>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="w-full h-1.5 bg-slate-200 dark:bg-zinc-750 rounded"></div>
                                        <div class="w-11/12 h-1 bg-slate-200 dark:bg-zinc-800 rounded"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- CREATIVE MINIMAL PREVIEW -->
                            <div v-else-if="tpl.svgLayout === 'creative'" class="space-y-3.5">
                                <div class="flex items-center gap-2 pt-2">
                                    <div class="w-6 h-6 rounded bg-purple-650 text-white text-[10px] font-black flex items-center justify-center shrink-0">CV</div>
                                    <div class="space-y-1">
                                        <div class="w-20 h-2 bg-purple-650 rounded"></div>
                                        <div class="w-12 h-1.5 bg-slate-300 dark:bg-zinc-700 rounded"></div>
                                    </div>
                                </div>
                                <div class="w-5 h-0.5 bg-purple-600"></div>
                                <div class="space-y-2">
                                    <div class="w-1/3 h-2 bg-slate-400 dark:bg-zinc-650 rounded"></div>
                                    <div class="space-y-1">
                                        <div class="w-full h-1 bg-slate-200 dark:bg-zinc-800 rounded"></div>
                                        <div class="w-full h-1 bg-slate-200 dark:bg-zinc-800 rounded"></div>
                                        <div class="w-5/6 h-1 bg-slate-200 dark:bg-zinc-800 rounded"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- STUDENT PREVIEW -->
                            <div v-else-if="tpl.svgLayout === 'student'" class="space-y-3">
                                <div class="bg-emerald-50 dark:bg-emerald-950/20 border-l-4 border-emerald-500 p-2 space-y-1">
                                    <div class="w-2/3 h-2.5 bg-emerald-500 rounded"></div>
                                    <div class="w-1/2 h-1.5 bg-slate-400 dark:bg-zinc-600 rounded"></div>
                                </div>
                                <div class="space-y-2">
                                    <div class="w-1/3 h-2 bg-emerald-500 rounded"></div>
                                    <div class="space-y-1">
                                        <div class="w-full h-1.5 bg-slate-300 dark:bg-zinc-700 rounded"></div>
                                        <div class="w-11/12 h-1 bg-slate-200 dark:bg-zinc-800 rounded"></div>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="w-full h-1.5 bg-slate-300 dark:bg-zinc-700 rounded"></div>
                                        <div class="w-11/12 h-1 bg-slate-200 dark:bg-zinc-800 rounded"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- CUSTOM PREVIEW -->
                            <div v-else-if="tpl.svgLayout === 'custom'" class="space-y-2.5">
                                <!-- Header -->
                                <div class="space-y-1 border-b border-indigo-200/60 dark:border-indigo-900/40 pb-2">
                                    <div class="w-1/2 h-2.5 bg-indigo-600 rounded"></div>
                                    <div class="w-1/3 h-1.5 bg-slate-300 dark:bg-zinc-600 rounded"></div>
                                </div>
                                <!-- Chip grid representing toggleable sections -->
                                <div class="text-[6px] font-bold text-slate-400 uppercase tracking-wider">Pilih Section:</div>
                                <div class="flex flex-wrap gap-1">
                                    <span class="px-1.5 py-0.5 rounded bg-indigo-600 text-white text-[6px] font-bold">Pengalaman</span>
                                    <span class="px-1.5 py-0.5 rounded bg-indigo-600 text-white text-[6px] font-bold">Pendidikan</span>
                                    <span class="px-1.5 py-0.5 rounded border border-slate-300 dark:border-zinc-700 text-slate-400 text-[6px] font-bold">Organisasi</span>
                                    <span class="px-1.5 py-0.5 rounded bg-indigo-600 text-white text-[6px] font-bold">Keahlian</span>
                                    <span class="px-1.5 py-0.5 rounded border border-slate-300 dark:border-zinc-700 text-slate-400 text-[6px] font-bold">Bahasa</span>
                                    <span class="px-1.5 py-0.5 rounded bg-indigo-600 text-white text-[6px] font-bold">Sertifikasi</span>
                                    <span class="px-1.5 py-0.5 rounded border border-slate-300 dark:border-zinc-700 text-slate-400 text-[6px] font-bold">Referensi</span>
                                </div>
                                <div class="space-y-1 mt-1">
                                    <div class="w-full h-1 bg-slate-200 dark:bg-zinc-800 rounded"></div>
                                    <div class="w-5/6 h-1 bg-slate-200 dark:bg-zinc-800 rounded"></div>
                                </div>
                            </div>

                        </div>
                    </div>                    <!-- Template Info -->
                    <div class="p-4 flex-1 flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            <span class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">
                                {{ tpl.category }}
                            </span>
                            <h3 class="font-bold text-sm text-slate-800 dark:text-zinc-150 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                {{ tpl.title }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-zinc-400 leading-relaxed">
                                {{ tpl.description }}
                            </p>
                        </div>

                        <!-- Action Button -->
                        <button
                            @click="selectTemplate(tpl.id)"
                            class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white dark:bg-zinc-800 dark:hover:bg-zinc-700 py-2 text-xs font-bold transition-all shadow-3xs cursor-pointer"
                        >
                            <Check class="w-3.5 h-3.5" />
                            Gunakan Template
                        </button>
                    </div>
                </div>
            </div>
        </main>

    </div>
</template>
