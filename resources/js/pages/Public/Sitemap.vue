<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import {
	ArrowRight,
	BookOpen,
	Briefcase,
	Calendar,
	ChevronRight,
	FileText,
	Globe,
	GraduationCap,
	Image,
	Info,
	Lock,
	MapPin,
	Megaphone,
	Scale,
	ShieldCheck,
	Video,
} from "lucide-vue-next";
import PublicFooter from "@/components/Portal/PublicFooter.vue";
import PublicNavbar from "@/components/Portal/PublicNavbar.vue";

defineProps<{
	categories: Record<
		string,
		Array<{ id: number; title: string; slug: string; category: string }>
	>;
	latestPosts: Array<{
		id: number;
		title: string;
		slug: string;
		created_at: string;
	}>;
}>();

// Map icons to categories
const getCategoryIcon = (category: string) => {
	switch (category.toLowerCase()) {
		case "profil":
			return Info;
		case "akademik":
			return GraduationCap;
		case "layanan":
			return Briefcase;
		case "berita & media":
			return Megaphone;
		default:
			return Globe;
	}
};

const getCategoryColor = (category: string) => {
	switch (category.toLowerCase()) {
		case "profil":
			return "text-blue-500 bg-blue-50 dark:bg-blue-900/20";
		case "akademik":
			return "text-emerald-500 bg-emerald-50 dark:bg-emerald-900/20";
		case "layanan":
			return "text-orange-500 bg-orange-50 dark:bg-orange-900/20";
		case "berita & media":
			return "text-violet-500 bg-violet-50 dark:bg-violet-900/20";
		default:
			return "text-slate-500 bg-slate-50 dark:bg-slate-900/20";
	}
};
</script>

<template>
    <Head>
        <title>Sitemap & Direktori Situs - Portal FMIKOM</title>
        <meta name="description" content="Peta situs resmi Portal FMIKOM. Jelajahi semua layanan, dokumen, berita, pengumuman, dan profil lengkap secara mudah." />
    </Head>

    <div class="min-h-screen bg-[#f8fafc] dark:bg-slate-950 font-sans text-slate-800 dark:text-slate-200">
        <!-- Navbar -->
        <PublicNavbar />

        <!-- Main Wrapper -->
        <main class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 mt-6">
            <!-- Header Section -->
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="px-3 py-1 text-xs font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 rounded-full">
                    Sitemap & Index
                </span>
                <h1 class="text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-3 mb-4 sm:text-5xl">
                    Direktori Lengkap Portal FMIKOM
                </h1>
                <p class="text-lg text-slate-600 dark:text-slate-400">
                    Gunakan peta situs ini untuk menjelajahi seluruh modul, menu navigasi, profil fakultas, layanan dokumen mahasiswa, dan berita terintegrasi.
                </p>
            </div>

            <!-- Grid Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- Left Column: Categories and Legal Pages -->
                <div class="lg:col-span-8 space-y-8">
                    
                    <!-- Dynamic Categories from Database -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div 
                            v-for="(pages, categoryName) in categories" 
                            :key="categoryName"
                            class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-xs p-6 hover:shadow-md transition-all duration-300"
                        >
                            <div class="flex items-center gap-3.5 mb-5 pb-3 border-b border-slate-100 dark:border-slate-800">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" :class="getCategoryColor(String(categoryName))">
                                    <component :is="getCategoryIcon(String(categoryName))" class="w-5 h-5" />
                                </div>
                                <h2 class="text-lg font-bold text-slate-900 dark:text-white capitalize">
                                    {{ categoryName }}
                                </h2>
                            </div>

                            <ul class="space-y-3.5">
                                <li v-for="page in pages" :key="page.id" class="group">
                                    <Link 
                                        :href="'/halaman/' + page.slug"
                                        class="flex items-center justify-between text-[14.5px] text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors font-medium"
                                    >
                                        <span class="flex items-center gap-2">
                                            <ChevronRight class="w-3.5 h-3.5 text-slate-300 dark:text-slate-700 transition-transform group-hover:translate-x-0.5 group-hover:text-blue-500" />
                                            {{ page.title }}
                                        </span>
                                        <span class="text-[11px] text-slate-400 group-hover:text-blue-500 transition-colors">/halaman/{{ page.slug }}</span>
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Core Modules and Static Navigation Section -->
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-xs p-8">
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2.5">
                            <span class="flex h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                            Modul Utama & Halaman Publik
                        </h2>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                            <!-- FAST -->
                            <div class="p-4 bg-slate-50/50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 rounded-xl">
                                <div class="flex items-center gap-2.5 mb-2 text-slate-900 dark:text-white font-bold text-sm">
                                    <span class="px-2 py-0.5 text-[10px] font-black tracking-wider bg-indigo-500 text-white rounded uppercase">FAST</span>
                                    Administrasi Surat
                                </div>
                                <p class="text-xs text-slate-500 mb-3">Layanan persuratan akademik & non-akademik digital.</p>
                                <a href="/login" class="text-xs text-indigo-600 dark:text-indigo-400 font-bold inline-flex items-center gap-1 hover:underline">
                                    Akses FAST <ArrowRight class="w-3 h-3" />
                                </a>
                            </div>

                            <!-- WIMS -->
                            <div class="p-4 bg-slate-50/50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 rounded-xl">
                                <div class="flex items-center gap-2.5 mb-2 text-slate-900 dark:text-white font-bold text-sm">
                                    <span class="px-2 py-0.5 text-[10px] font-black tracking-wider bg-emerald-500 text-white rounded uppercase">WIMS</span>
                                    Sistem Magang
                                </div>
                                <p class="text-xs text-slate-500 mb-3">Manajemen pendaftaran, logbook, dan penilaian magang.</p>
                                <a href="/login" class="text-xs text-emerald-600 dark:text-emerald-400 font-bold inline-flex items-center gap-1 hover:underline">
                                    Akses WIMS <ArrowRight class="w-3 h-3" />
                                </a>
                            </div>

                            <!-- TRACE -->
                            <div class="p-4 bg-slate-50/50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 rounded-xl">
                                <div class="flex items-center gap-2.5 mb-2 text-slate-900 dark:text-white font-bold text-sm">
                                    <span class="px-2 py-0.5 text-[10px] font-black tracking-wider bg-rose-500 text-white rounded uppercase">TRACE</span>
                                    Tracer Study
                                </div>
                                <p class="text-xs text-slate-500 mb-3">Pelacakan karir dan aktivitas alumni untuk pengembangan mutu kurikulum.</p>
                                <a href="/login" class="text-xs text-rose-600 dark:text-rose-400 font-bold inline-flex items-center gap-1 hover:underline">
                                    Akses TRACE <ArrowRight class="w-3 h-3" />
                                </a>
                            </div>

                            <!-- PAGI -->
                            <div class="p-4 bg-slate-50/50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 rounded-xl">
                                <div class="flex items-center gap-2.5 mb-2 text-slate-900 dark:text-white font-bold text-sm">
                                    <span class="px-2 py-0.5 text-[10px] font-black tracking-wider bg-cyan-500 text-white rounded uppercase">PAGI</span>
                                    Portofolio Siswa
                                </div>
                                <p class="text-xs text-slate-500 mb-3">Wadah pameran karya, CV generator, dan prestasi.</p>
                                <a href="/login" class="text-xs text-cyan-600 dark:text-cyan-400 font-bold inline-flex items-center gap-1 hover:underline">
                                    Akses PAGI <ArrowRight class="w-3 h-3" />
                                </a>
                            </div>
                        </div>

                        <!-- General Pages Link List -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-8 pt-6 border-t border-slate-100 dark:border-slate-800">
                            <div>
                                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-3">Akses & Publik</h3>
                                <ul class="space-y-2 text-sm">
                                    <li><Link href="/" class="text-slate-600 dark:text-slate-400 hover:text-blue-500 font-medium">Beranda Utama (Landing Page)</Link></li>
                                    <li><Link href="/berita" class="text-slate-600 dark:text-slate-400 hover:text-blue-500 font-medium">Kabar Berita & Pengumuman</Link></li>
                                    <li><Link href="/dokumen" class="text-slate-600 dark:text-slate-400 hover:text-blue-500 font-medium">Dokumen & Unduhan Publik</Link></li>
                                </ul>
                            </div>

                            <div>
                                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-3">Halaman Hukum (Legal)</h3>
                                <ul class="space-y-2 text-sm">
                                    <li><Link href="/privacy-policy" class="text-slate-600 dark:text-slate-400 hover:text-blue-500 font-medium inline-flex items-center gap-1.5"><ShieldCheck class="w-4 h-4 text-emerald-500" /> Kebijakan Privasi</Link></li>
                                    <li><Link href="/terms-of-service" class="text-slate-600 dark:text-slate-400 hover:text-blue-500 font-medium inline-flex items-center gap-1.5"><Scale class="w-4 h-4 text-amber-500" /> Syarat & Ketentuan</Link></li>
                                    <li><Link href="/cookie-policy" class="text-slate-600 dark:text-slate-400 hover:text-blue-500 font-medium inline-flex items-center gap-1.5"><Lock class="w-4 h-4 text-blue-500" /> Kebijakan Kuki</Link></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Latest Posts / News Feed -->
                <div class="lg:col-span-4 space-y-6">
                    
                    <!-- Search Engine Bot Info Card -->
                    <div class="bg-linear-to-br from-blue-600 to-indigo-700 text-white rounded-2xl p-6 shadow-md shadow-blue-500/10">
                        <h3 class="font-bold text-lg mb-2">Google Indexing</h3>
                        <p class="text-xs text-blue-100 leading-relaxed mb-4">
                            Halaman sitemap XML otomatis diperbarui untuk bot perayap mesin pencari modern (Google, Bing, Yahoo).
                        </p>
                        <div class="flex items-center gap-2">
                            <a 
                                href="/sitemap.xml" 
                                target="_blank"
                                class="inline-flex items-center justify-center bg-white text-blue-700 text-xs font-bold px-3 py-2 rounded-lg shadow-sm hover:bg-blue-50 transition-colors"
                            >
                                <Globe class="w-3.5 h-3.5 mr-1" />
                                Lihat sitemap.xml
                            </a>
                        </div>
                    </div>

                    <!-- Latest Posts Directory -->
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-xs p-6">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4 pb-2 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                            <Megaphone class="w-4.5 h-4.5 text-violet-500" />
                            Artikel Berita Terbaru
                        </h2>

                        <div v-if="latestPosts.length === 0" class="text-sm text-slate-400 py-4 text-center">
                            Belum ada artikel berita yang dipublikasikan.
                        </div>
                        <ul v-else class="space-y-4">
                            <li v-for="post in latestPosts" :key="post.id" class="group">
                                <a 
                                    :href="'/berita/' + post.slug"
                                    class="block"
                                >
                                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors line-clamp-2 leading-snug">
                                        {{ post.title }}
                                    </h3>
                                    <span class="text-[10px] text-slate-400 mt-1 block">/berita/{{ post.slug }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </main>

        <!-- Footer -->
        <PublicFooter />
    </div>
</template>

<style scoped>
/* Added subtle hover zoom animation to the directory grids */
.shadow-xs:hover {
    transform: translateY(-2px);
}
</style>
