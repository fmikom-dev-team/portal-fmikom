<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import {
	ArrowRight,
	BookOpen,
	Briefcase,
	CheckCircle2,
	ChevronRight,
	Database,
	FileText,
	GraduationCap,
	Layers,
	MonitorSmartphone,
	Users,
	X,
	Zap,
} from "lucide-vue-next";
import { computed, onMounted, ref } from "vue";
import PublicFooter from "@/components/Portal/PublicFooter.vue";
import PublicNavbar from "@/components/Portal/PublicNavbar.vue";
import { register } from "@/routes";

const _templateComponents = [
	Head,
	Link,
	ArrowRight,
	CheckCircle2,
	ChevronRight,
	Layers,
	X,
	PublicFooter,
	PublicNavbar,
	register,
];

const props = withDefaults(
	defineProps<{
		canRegister?: boolean;
		// biome-ignore lint/suspicious/noExplicitAny: library config
		settings?: Record<string, any>;
		// biome-ignore lint/suspicious/noExplicitAny: library config
		latest_posts?: Array<any>;
	}>(),
	{
		canRegister: true,
		settings: () => ({}),
		latest_posts: () => [],
	},
);

// ─── Hero Gallery Card Stack ────────────────────────────────
const activeCardIndex = ref(0);
const isAnimating = ref(false);
const swipeDirection = ref<"left" | "right" | null>(null);

// biome-ignore lint/correctness/noUnusedVariables: template
const showAlumniPopup = ref(true);

const gallery = computed<string[]>(() => props.settings?.hero_gallery || []);

// biome-ignore lint/correctness/noUnusedVariables: template
const getCardStyle = (i: number) => {
	const total = gallery.value.length;
	if (total === 0) return {};
	const offset = (i - activeCardIndex.value + total) % total;
	const zIndex = total - offset;
	if (offset === 0) {
		// Front card
		return {
			zIndex,
			transform: "translateX(0) translateY(0) rotate(0deg) scale(1)",
			opacity: "1",
			transition: "all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)",
		};
	} else if (offset === 1) {
		return {
			zIndex,
			transform: "translateX(8px) translateY(10px) rotate(2deg) scale(0.96)",
			opacity: "1",
			transition: "all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)",
		};
	} else if (offset === 2) {
		return {
			zIndex,
			transform: "translateX(16px) translateY(20px) rotate(4deg) scale(0.92)",
			opacity: "0.8",
			transition: "all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)",
		};
	} else {
		return {
			zIndex: 0,
			transform: "translateX(24px) translateY(30px) scale(0.88)",
			opacity: "0",
			transition: "all 0.4s ease",
		};
	}
};

const nextCard = () => {
	if (isAnimating.value || gallery.value.length <= 1) return;
	swipeDirection.value = "left";
	isAnimating.value = true;
	setTimeout(() => {
		activeCardIndex.value = (activeCardIndex.value + 1) % gallery.value.length;
		swipeDirection.value = null;
		setTimeout(() => {
			isAnimating.value = false;
		}, 50);
	}, 300);
};

const prevCard = () => {
	if (isAnimating.value || gallery.value.length <= 1) return;
	swipeDirection.value = "right";
	isAnimating.value = true;
	setTimeout(() => {
		activeCardIndex.value =
			(activeCardIndex.value - 1 + gallery.value.length) % gallery.value.length;
		swipeDirection.value = null;
		setTimeout(() => {
			isAnimating.value = false;
		}, 50);
	}, 300);
};

// Touch/swipe support
const touchStartX = ref(0);
// biome-ignore lint/correctness/noUnusedVariables: template
const onTouchStart = (e: TouchEvent) => {
	touchStartX.value = e.touches[0].clientX;
};
// biome-ignore lint/correctness/noUnusedVariables: template
const onTouchEnd = (e: TouchEvent) => {
	const diff = touchStartX.value - e.changedTouches[0].clientX;
	if (Math.abs(diff) > 50) {
		diff > 0 ? nextCard() : prevCard();
	}
};

const isMobileMenuOpen = ref(false);
// biome-ignore lint/correctness/noUnusedVariables: template
const toggleMobileMenu = () => {
	isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

// biome-ignore lint/correctness/noUnusedVariables: template
const features = [
	{
		title: "FAST",
		subtitle: "Administrasi Surat",
		description:
			"Layanan administrasi persuratan akademik dan non-akademik secara digital, cepat, dan terlacak.",
		icon: FileText,
		bgClass:
			"bg-linear-to-br from-[#6366f1] to-[#4f46e5] shadow-indigo-500/30 hover:shadow-indigo-500/50",
	},
	{
		title: "WIMS",
		subtitle: "Sistem Magang",
		description:
			"Kelola pendaftaran, logbook, hingga penilaian magang mahasiswa terintegrasi dengan mitra.",
		icon: Briefcase,
		bgClass:
			"bg-linear-to-br from-[#10b981] to-[#059669] shadow-emerald-500/30 hover:shadow-emerald-500/50",
	},
	{
		title: "TRACE",
		subtitle: "Tracer Study Alumni",
		description:
			"Pelacakan karir dan aktivitas alumni untuk pengembangan mutu kurikulum program studi.",
		icon: GraduationCap,
		bgClass:
			"bg-linear-to-br from-[#fb7185] to-[#e11d48] shadow-rose-500/30 hover:shadow-rose-500/50",
	},
	{
		title: "PAGI",
		subtitle: "Portfolio Mahasiswa",
		description:
			"Wadah publikasi karya, sertifikasi, dan pencapaian akademik mahasiswa secara terpusat.",
		icon: BookOpen,
		bgClass:
			"bg-linear-to-br from-[#22d3ee] to-[#0891b2] shadow-cyan-500/30 hover:shadow-cyan-500/50",
	},
];

// biome-ignore lint/correctness/noUnusedVariables: template
const benefits = [
	{
		title: "Cepat & Terintegrasi",
		description:
			"Satu ekosistem untuk semua layanan akademik, menghilangkan proses manual berulang.",
		icon: Zap,
	},
	{
		title: "Akses Multi Role",
		description:
			"Dashboard khusus dan hak akses tepat sasaran Mahasiswa, Dosen, Alumni, dan Mitra.",
		icon: Users,
	},
	{
		title: "Data Terpusat",
		description:
			"Satu sumber kebenaran, meningkatkan keamanan data dan memudahkan pelaporan.",
		icon: Database,
	},
	{
		title: "Modern & Responsive",
		description:
			"Desain SaaS modern yang cepat diakses dari perangkat mobile maupun desktop.",
		icon: MonitorSmartphone,
	},
];

// biome-ignore lint/correctness/noUnusedVariables: template
const userTypes = [
	{
		title: "Mahasiswa",
		description:
			"Akses penuh ke layanan administrasi, sistem magang, dan bangun portofolio akademik untuk karir masa depan.",
	},
	{
		title: "Dosen",
		description:
			"Fasilitas manajemen persetujuan dokumen, bimbingan, serta monitoring progres magang mahasiswa.",
	},
	{
		title: "Alumni",
		description:
			"Terhubung dengan almamater, isi tracer study, dan dapatkan update karir serta jejaring komunitas.",
	},
	{
		title: "Mitra",
		description:
			"Kolaborasi industri yang mudah, akses rekrutmen magang, logbook, dan form penilaian langsung.",
	},
];

// biome-ignore lint/correctness/noUnusedVariables: template
const newsItems = [
	{
		title: "Peluncuran Modul PAGI untuk Mahasiswa",
		date: "12 Okt 2026",
		category: "Update Sistem",
		excerpt:
			"Modul portofolio mahasiswa resmi dirilis. Kini setiap mahasiswa dapat mencetak CV otomatis dan pamer karya langsung dari satu dashboard.",
		image:
			"https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&q=80&w=800",
	},
	{
		title: "Integrasi Industri: Rekrutmen Magang via WIMS",
		date: "08 Sep 2026",
		category: "Kerjasama",
		excerpt:
			"FMIKOM menggandeng 20+ perusahaan teknologi untuk membuka lowongan magang eksklusif yang bisa di-apply langsung.",
		image:
			"https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&q=80&w=800",
	},
	{
		title: "Standarisasi Tracer Study Alumni 2026",
		date: "15 Agu 2026",
		category: "Akademik",
		excerpt:
			"Survei tracer study kini lebih ringkas dan berhadiah! Masukkan data karirmu dan bantu prodi mencapai akreditasi Unggul.",
		image:
			"https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80&w=800",
	},
];

// biome-ignore lint/correctness/noUnusedVariables: template
const partners = [
	{
		name: "Mitra 1",
		url: "https://placehold.co/200x60/f9fafb/9ca3af?text=Mitra+1",
	},
	{
		name: "Mitra 2",
		url: "https://placehold.co/200x60/f9fafb/9ca3af?text=Mitra+2",
	},
	{
		name: "Mitra 3",
		url: "https://placehold.co/200x60/f9fafb/9ca3af?text=Mitra+3",
	},
	{
		name: "Mitra 4",
		url: "https://placehold.co/200x60/f9fafb/9ca3af?text=Mitra+4",
	},
	{
		name: "Mitra 5",
		url: "https://placehold.co/200x60/f9fafb/9ca3af?text=Mitra+5",
	},
	{
		name: "Mitra 6",
		url: "https://placehold.co/200x60/f9fafb/9ca3af?text=Mitra+6",
	},
];

// biome-ignore lint/correctness/noUnusedVariables: template
const testimonials = [
	{
		quote:
			"Sistem FAST benar-benar mengubah cara saya mengajukan persuratan. Dulu butuh 3 hari, sekarang hanya hitungan jam sudah disetujui Kaprodi!",
		name: "Andi Saputra",
		role: "Mahasiswa Semester 6",
		avatar:
			"https://api.dicebear.com/7.x/avataaars/svg?seed=a042581f4e29026024d",
	},
	{
		quote:
			"Sebagai dosen pembimbing, memantau logbook magang mahasiswa via WIMS sangat menghemat waktu. Semua terpusat dan mudah diakses.",
		name: "Dr. Budi Santoso, M.Kom",
		role: "Dosen Pembimbing",
		avatar:
			"https://api.dicebear.com/7.x/avataaars/svg?seed=a042581f4e29026704d",
	},
	{
		quote:
			"Saya mendapat pekerjaan pertama saya karena profil portofolio yang saya bangun dilihat langsung oleh mitra FMIKOM.",
		name: "Siti Rahmawati",
		role: "Alumni Angkatan 2022",
		avatar:
			"https://api.dicebear.com/7.x/avataaars/svg?seed=a04258a2462d826712d",
	},
];

onMounted(() => {
	// Config untuk Intersection Observer (Animasi saat scroll)
	const observerOptions = {
		root: null,
		rootMargin: "0px 0px -50px 0px", // Memunculkan animasi sedikit sebelum elemen benar-benar masuk viewport
		threshold: 0.15,
	};

	const observer = new IntersectionObserver((entries, observer) => {
		for (const entry of entries) {
			if (entry.isIntersecting) {
				entry.target.classList.add("show-animate");
				observer.unobserve(entry.target); // Lepas observer setelah animasi jalan (supaya tidak berulang saat scroll naik turun)
			}
		}
	}, observerOptions);

	for (const el of document.querySelectorAll(".hide-animate")) {
		observer.observe(el);
	}

	setInterval(() => {
		nextCard();
	}, 4000);
});

// biome-ignore lint/correctness/noUnusedVariables: template
const formatDate = (dateString: string) => {
	if (!dateString) return "";
	return new Date(dateString).toLocaleDateString("en-GB", {
		day: "numeric",
		month: "long",
		year: "numeric",
	});
};

// biome-ignore lint/correctness/noUnusedVariables: template
const extractText = (content: string) => {
	if (!content) return "";
	try {
		const parsed = JSON.parse(content);
		if (parsed?.blocks && Array.isArray(parsed.blocks)) {
			const textContent = parsed.blocks
				.filter(
					// biome-ignore lint/suspicious/noExplicitAny: parsing block type
					(b: any) =>
						b.type === "paragraph" || b.type === "header" || b.type === "list",
				)
				// biome-ignore lint/suspicious/noExplicitAny: parsing block structure
				.map((b: any) => {
					if (b.type === "list" && b.data?.items) {
						return (
							b.data.items
								// biome-ignore lint/suspicious/noExplicitAny: parsing item inside list block
								.map((item: any) =>
									typeof item === "string" ? item : item.content || "",
								)
								.join(" ")
						);
					}
					return b.data?.text || "";
				})
				.join(" ");
			return textContent.replace(/<[^>]*>?/gm, "").trim();
		}
	} catch (error) {
		console.warn("Failed to parse JSON content inside extractText:", error);
	}
	return content.replace(/<[^>]*>?/gm, "").trim();
};

// biome-ignore lint/correctness/noUnusedVariables: template
const currentYear = new Date().getFullYear();
</script>

<template>
    <Head>
        <title>Welcome to Portal FMIKOM</title>
    </Head>

    <div
        class="min-h-screen overflow-clip bg-[#ffffff] font-sans text-[#111827] selection:bg-[#b6ff00] selection:text-gray-900"
    >
        <!-- NAVBAR -->
        <PublicNavbar v-if="settings?.show_navbar !== '0'" />

        <!-- HERO SECTION -->
        <section
            v-if="settings?.show_hero !== '0'"
            id="home"
            class="relative z-0 overflow-hidden bg-white pt-16 pb-16 sm:pt-24 sm:pb-24 lg:pt-32 lg:pb-32"
        >
            <div
                class="pointer-events-none absolute top-0 right-0 h-full w-full bg-linear-to-br from-white/90 via-white/70 to-blue-50/70"
            ></div>
            <!-- Grid Background Overlay -->
            <div
                class="bg-grid-pattern pointer-events-none absolute inset-0"
                style="
                    -webkit-mask-image: linear-gradient(
                        to bottom,
                        black 0%,
                        transparent 80%
                    );
                    mask-image: linear-gradient(
                        to bottom,
                        black 0%,
                        transparent 80%
                    );
                "
            ></div>
            <div
                class="animate-pulse-slow pointer-events-none absolute -top-40 right-20 h-[500px] w-[500px] rounded-full bg-blue-100/40 opacity-60 blur-3xl"
            ></div>
            <div
                class="animate-pulse-slow pointer-events-none absolute bottom-0 left-10 h-[300px] w-[300px] rounded-full bg-[#b6ff00]/20 opacity-50 blur-3xl delay-700"
            ></div>

            <div class="relative z-10 mx-auto max-w-7xl px-4 xl:px-0">
                <div
                    class="grid grid-cols-1 items-center gap-16 lg:grid-cols-2 lg:gap-8"
                >
                    <!-- Text Content -->
                    <div
                        class="hide-animate slide-left mx-auto max-w-2xl text-center lg:mx-0 lg:text-left"
                    >
                        <div
                            class="mb-6 inline-flex items-center rounded-full border border-gray-200 bg-white/50 px-3 py-1 text-sm font-semibold text-[#2563eb] shadow-sm backdrop-blur-sm"
                        >
                            <span
                                class="mr-2 flex h-2 w-2 rounded-full bg-[#b6ff00]"
                            ></span>
                            {{ settings?.hero_subtitle || 'Sistem Informasi Terpadu' }}
                        </div>
                        <h1
                            class="text-4xl leading-tight font-extrabold tracking-tight text-[#111827] drop-shadow-sm sm:text-5xl lg:text-6xl whitespace-pre-line"
                        >
                            {{ settings?.hero_title || 'Satu Portal untuk \nSemua Layanan \nFMIKOM' }}
                        </h1>
                        <p
                            class="mx-auto mt-6 max-w-xl text-lg leading-relaxed text-[#6b7280] sm:text-xl lg:mx-0"
                        >
                            {{ settings?.hero_description || 'Kelola administrasi, magang, alumni, dan portofolio dalam satu sistem terintegrasi. Dibangun untuk memberikan pengalaman terbaik bergaya modern.' }}
                        </p>
                        <div
                            class="mt-10 flex flex-col justify-center gap-4 sm:flex-row lg:justify-start"
                        >
                            <Link
                                :href="register()"
                                class="inline-flex h-12 items-center justify-center rounded-xl bg-[#2563eb] px-8 text-base font-semibold text-white shadow-lg transition-all hover:-translate-y-1 hover:bg-blue-700 hover:shadow-xl hover:shadow-blue-600/30 focus:ring-4 focus:ring-[#b6ff00]/50 focus:outline-none"
                            >
                                Mulai Sekarang
                                <ArrowRight class="ml-2 h-5 w-5" />
                            </Link>
                            <a
                                href="#modules"
                                class="inline-flex h-12 items-center justify-center rounded-xl border-2 border-gray-200 bg-white px-8 text-base font-semibold text-[#111827] shadow-sm transition-all hover:-translate-y-1 hover:border-[#2563eb] hover:text-[#2563eb] focus:ring-4 focus:ring-blue-500/20 focus:outline-none"
                            >
                                Pelajari Lebih Lanjut
                            </a>
                        </div>
                        <div
                            class="mt-10 flex items-center justify-center gap-4 text-sm font-medium text-[#6b7280] lg:justify-start"
                        >
                            <div
                                class="hide-animate scale-in flex items-center gap-1.5"
                                style="transition-delay: 300ms"
                            >
                                <CheckCircle2
                                    class="h-4 w-4 rounded-full bg-gray-900 text-[#b6ff00]"
                                />
                                Mudah
                            </div>
                            <div
                                class="hide-animate scale-in flex items-center gap-1.5"
                                style="transition-delay: 450ms"
                            >
                                <CheckCircle2
                                    class="h-4 w-4 rounded-full bg-gray-900 text-[#b6ff00]"
                                />
                                Cepat
                            </div>
                            <div
                                class="hide-animate scale-in flex items-center gap-1.5"
                                style="transition-delay: 600ms"
                            >
                                <CheckCircle2
                                    class="h-4 w-4 rounded-full bg-gray-900 text-[#b6ff00]"
                                />
                                Terpusat
                            </div>
                        </div>
                    </div>

                    <!-- Hero Gallery Card Stack -->
                    <div
                        class="hide-animate slide-right relative mx-auto w-full max-w-[500px] lg:max-w-none flex items-center justify-center min-h-[400px]"
                        style="transition-delay: 200ms"
                    >
                        <!-- Interactive Stacked Card Gallery -->
                        <div v-if="gallery.length > 0" class="relative w-full max-w-[560px] mx-auto select-none">
                            <!-- Card Stack Container -->
                            <div
                                class="relative aspect-16/11 cursor-grab active:cursor-grabbing"
                                @touchstart="onTouchStart"
                                @touchend="onTouchEnd"
                            >
                                <div
                                    v-for="(img, i) in gallery"
                                    :key="img"
                                    class="absolute inset-0 rounded-2xl overflow-hidden shadow-xl border-4 border-white ring-1 ring-black/5"
                                    :style="{
                                        ...getCardStyle(i),
                                        ...(swipeDirection === 'left' && (i - activeCardIndex + gallery.length) % gallery.length === 0 ? { transform: 'translateX(-120%) rotate(-15deg)', opacity: '0' } : {}),
                                        ...(swipeDirection === 'right' && (i - activeCardIndex + gallery.length) % gallery.length === 0 ? { transform: 'translateX(120%) rotate(15deg)', opacity: '0' } : {}),
                                    }"
                                >
                                    <img :src="img" :alt="'Gallery ' + (i + 1)" class="w-full h-full object-cover" draggable="false">
                                    <!-- Gradient overlay at bottom -->
                                    <div class="absolute inset-x-0 bottom-0 h-20 bg-linear-to-t from-black/30 to-transparent"></div>
                                    <!-- Card number badge -->
                                    <div class="absolute top-3 right-3 bg-black/40 backdrop-blur-sm text-white text-[11px] font-bold px-2.5 py-1 rounded-full">
                                        {{ i + 1 }} / {{ gallery.length }}
                                    </div>
                                </div>
                            </div>

                            <!-- Navigation Arrows -->
                            <div class="flex items-center justify-between mt-4 px-2">
                                <button
                                    @click="prevCard"
                                    class="group flex items-center justify-center w-10 h-10 rounded-full bg-white shadow-md border border-gray-200 hover:border-[#2563eb] hover:bg-[#2563eb] transition-all duration-200 disabled:opacity-40"
                                    :disabled="gallery.length <= 1"
                                >
                                    <svg class="w-4 h-4 text-gray-700 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                                </button>

                                <!-- Dot Indicators -->
                                <div class="flex items-center gap-1.5">
                                    <button
                                        v-for="(_, i) in gallery"
                                        :key="i"
                                        @click="activeCardIndex = i"
                                        class="rounded-full transition-all duration-300"
                                        :class="activeCardIndex === i ? 'w-6 h-2.5 bg-[#2563eb]' : 'w-2.5 h-2.5 bg-gray-300 hover:bg-gray-400'"
                                    ></button>
                                </div>

                                <button
                                    @click="nextCard"
                                    class="group flex items-center justify-center w-10 h-10 rounded-full bg-white shadow-md border border-gray-200 hover:border-[#2563eb] hover:bg-[#2563eb] transition-all duration-200 disabled:opacity-40"
                                    :disabled="gallery.length <= 1"
                                >
                                    <svg class="w-4 h-4 text-gray-700 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>

                            <!-- Swipe Hint -->
                            <p class="text-center text-[11px] text-gray-400 font-medium mt-2">Geser atau klik panah untuk melihat karya berikutnya</p>
                        </div>

                        <!-- Fallback Graphic if no gallery images -->
                        <div v-else class="relative z-10 rotate-1 transform rounded-2xl bg-white p-2 shadow-2xl ring-1 ring-gray-900/5 transition-all duration-700 hover:scale-105 hover:rotate-0 hover:shadow-[0_20px_50px_rgba(37,99,235,0.15)] md:p-4">
                            <div class="flex h-[350px] w-full items-center justify-center rounded-xl border border-gray-100 bg-gray-50 shadow-inner flex-col gap-4 text-gray-400">
                                <Layers class="w-16 h-16 opacity-50" />
                                <span class="text-sm font-bold opacity-50">Upload Galeri Karya di Portal Admin</span>
                                <span class="text-xs opacity-40">Tata Letak → Hero Section → Galeri</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- DYNAMIC POSTS SECTION -->
        <section v-if="settings?.show_features !== '0'" id="news" class="bg-white py-20 lg:py-28">
            <div class="mx-auto max-w-7xl px-4 xl:px-0">
                <!-- Centered Header -->
                <div class="hide-animate slide-up mb-16 text-center max-w-3xl mx-auto">
                    <h2 class="text-4xl lg:text-5xl font-bold text-[#111827] mb-6 tracking-tight">Info Terbaru FMIKOM</h2>
                    <p class="text-[17px] text-[#6b7280] leading-relaxed">
                        Sorotan terbaru mengenai kegiatan akademik, prestasi, dan pembaruan sistem di lingkungan fakultas.
                    </p>
                </div>

                <div v-if="latest_posts.length === 0" class="py-12 text-center text-gray-500 font-medium">
                    Belum ada postingan yang dipublikasikan.
                </div>
                
                <div v-else class="flex flex-col gap-12 lg:gap-16">
                    <!-- Featured Post (First Post) -->
                    <div v-if="latest_posts[0]" class="hide-animate slide-up group grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center cursor-pointer" @click="$inertia.visit('/berita/' + latest_posts[0].slug)">
                        <div class="relative w-full aspect-16/10 lg:aspect-4/3 rounded-4xl overflow-hidden bg-slate-100">
                            <img v-if="latest_posts[0].thumbnail" :src="latest_posts[0].thumbnail" :alt="latest_posts[0].title" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
                            <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                                <FileText class="w-16 h-16" />
                            </div>
                        </div>
                        <div class="flex flex-col justify-center px-2 lg:px-6">
                            <span class="text-[15px] font-semibold text-slate-500 mb-4">{{ formatDate(latest_posts[0].published_at || latest_posts[0].created_at) }}</span>
                            <h3 class="text-3xl lg:text-[42px] font-bold text-slate-900 leading-tight mb-5 group-hover:text-blue-600 transition-colors line-clamp-3 tracking-tight">
                                {{ latest_posts[0].title }}
                            </h3>
                            <p class="text-[17px] text-slate-500 leading-relaxed mb-8 line-clamp-3">
                                {{ latest_posts[0].meta_description || extractText(latest_posts[0].content).substring(0, 200) + '...' }}
                            </p>
                            <div class="flex items-center gap-3">
                                <img :src="`https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(latest_posts[0].user?.name || 'Admin')}&backgroundColor=f1f5f9&textColor=0f172a`" :alt="latest_posts[0].user?.name || 'Admin'" class="w-10 h-10 rounded-full border border-slate-200">
                                <span class="font-bold text-slate-900">{{ latest_posts[0].user?.name || 'Admin' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Grid Posts -->
                    <div v-if="latest_posts.length > 1" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-10">
                        <div v-for="(post, index) in latest_posts.slice(1, 5)" :key="post.id" class="hide-animate slide-up group cursor-pointer flex flex-col" :style="{ transitionDelay: `${index * 150}ms` }" @click="$inertia.visit('/berita/' + post.slug)">
                            <div class="relative w-full aspect-4/3 rounded-3xl overflow-hidden bg-slate-100 mb-6">
                                <img v-if="post.thumbnail" :src="post.thumbnail" :alt="post.title" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
                                <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                                    <FileText class="w-10 h-10" />
                                </div>
                            </div>
                            <h4 class="text-[19px] font-bold text-slate-900 leading-snug mb-3 group-hover:text-blue-600 transition-colors line-clamp-2 tracking-tight">
                                {{ post.title }}
                            </h4>
                            <p class="text-[15px] text-slate-500 leading-relaxed mb-6 line-clamp-2 flex-1">
                                {{ post.meta_description || extractText(post.content).substring(0, 100) + '...' }}
                            </p>
                            <div class="flex items-center gap-3 mt-auto">
                                <img :src="`https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(post.user?.name || 'Admin')}&backgroundColor=f1f5f9&textColor=0f172a`" :alt="post.user?.name || 'Admin'" class="w-8 h-8 rounded-full border border-slate-200">
                                <span class="text-[14px] font-bold text-slate-700">{{ post.user?.name || 'Admin' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- PARTNERS SECTION -->
        <section v-if="settings?.show_partners !== '0' && settings?.partners?.length > 0" class="border-t border-gray-100 bg-gray-50/30 py-10 overflow-hidden">
            <div class="mx-auto max-w-7xl px-4 xl:px-0 mb-8">
                <p class="text-center text-xs font-bold tracking-widest text-gray-400 uppercase">Telah Bekerja Sama Dengan</p>
            </div>
            
            <!-- Infinite Marquee Slider -->
            <div class="relative flex w-full flex-col justify-center overflow-hidden border-y border-gray-200">
                <!-- Left/Right Gradient Masks for smooth fade -->
                <div class="pointer-events-none absolute inset-y-0 left-0 z-10 w-24 bg-linear-to-r from-[#f8fafc] to-transparent sm:w-40"></div>
                <div class="pointer-events-none absolute inset-y-0 right-0 z-10 w-24 bg-linear-to-l from-[#f8fafc] to-transparent sm:w-40"></div>
                
                <div class="flex w-max animate-marquee">
                    <!-- First Set -->
                    <div class="flex shrink-0 items-center">
                        <div v-for="(partner, i) in settings.partners" :key="'p1-' + i" class="group flex h-20 w-40 sm:h-24 sm:w-56 items-center justify-center border-r border-gray-200 transition-all hover:bg-gray-100/50">
                            <img :src="typeof partner === 'object' ? partner.logo : partner" :alt="typeof partner === 'object' && partner.name ? partner.name : 'Partner Logo'" class="h-10 w-28 sm:h-12 sm:w-40 object-contain grayscale opacity-60 transition-all duration-300 group-hover:scale-110 group-hover:grayscale-0 group-hover:opacity-100">
                        </div>
                    </div>
                    <!-- Second Set (Duplicate) -->
                    <div class="flex shrink-0 items-center">
                        <div v-for="(partner, i) in settings.partners" :key="'p2-' + i" class="group flex h-20 w-40 sm:h-24 sm:w-56 items-center justify-center border-r border-gray-200 transition-all hover:bg-gray-100/50">
                            <img :src="typeof partner === 'object' ? partner.logo : partner" :alt="typeof partner === 'object' && partner.name ? partner.name : 'Partner Logo'" class="h-10 w-28 sm:h-12 sm:w-40 object-contain grayscale opacity-60 transition-all duration-300 group-hover:scale-110 group-hover:grayscale-0 group-hover:opacity-100">
                        </div>
                    </div>
                    <!-- Third Set (Duplicate for wide screens) -->
                    <div class="flex shrink-0 items-center">
                        <div v-for="(partner, i) in settings.partners" :key="'p3-' + i" class="group flex h-20 w-40 sm:h-24 sm:w-56 items-center justify-center border-r border-gray-200 transition-all hover:bg-gray-100/50">
                            <img :src="typeof partner === 'object' ? partner.logo : partner" :alt="typeof partner === 'object' && partner.name ? partner.name : 'Partner Logo'" class="h-10 w-28 sm:h-12 sm:w-40 object-contain grayscale opacity-60 transition-all duration-300 group-hover:scale-110 group-hover:grayscale-0 group-hover:opacity-100">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- BENEFITS SECTION -->
        <section v-if="settings?.show_benefits !== '0'" id="benefits" class="bg-white py-20 lg:py-28">
            <div class="mx-auto max-w-7xl px-4 xl:px-0">
                <div
                    class="grid grid-cols-1 items-center gap-16 lg:grid-cols-2"
                >
                    <div class="hide-animate slide-right">
                        <h2
                            class="mb-3 text-sm font-bold tracking-wide text-[#2563eb] uppercase"
                        >
                            Keunggulan
                        </h2>
                        <h3
                            class="mb-6 text-3xl leading-tight font-extrabold text-[#111827] sm:text-4xl"
                        >
                            {{ settings?.benefits_title || 'Mengapa Memilih Portal FMIKOM?' }}
                        </h3>
                        <p class="mb-8 text-lg leading-relaxed text-[#6b7280]">
                            {{ settings?.benefits_subtitle || 'Kami membawa standarisasi aplikasi industri (SaaS) ke lingkungan kampus. Transparan, terpusat, dan mudah diakses.' }}
                        </p>
                        <div
                            class="group relative hidden h-104 w-full transition-all duration-1500 lg:block"
                            style="perspective: 1200px"
                        >

                            <!-- 3D Isometric Container -->
                            <div
                                class="absolute top-[55%] left-1/2 flex h-64 w-64 -translate-x-1/2 -translate-y-1/2 transform-[rotateX(60deg)_rotateZ(-45deg)_scale(0.85)] items-center justify-center transition-all duration-1500 ease-out transform-3d group-hover:transform-[rotateX(60deg)_rotateZ(-35deg)_scale(0.95)]"
                            >
                                <!-- Layer 1 (Bottom) -->
                                <div
                                    class="absolute flex h-48 w-48 transform-[translateZ(0px)] items-center justify-center rounded-4xl border border-slate-300/60 bg-white/20 font-mono shadow-xl backdrop-blur-sm transition-all duration-1500 ease-out group-hover:transform-[translateZ(-20px)]"
                                >
                                    <Database class="h-12 w-12 text-slate-300 transition-all duration-1500 group-hover:scale-110 group-hover:text-slate-400" />
                                    <span
                                        class="absolute right-4 bottom-4 text-xs font-bold tracking-widest text-slate-400"
                                        >DATA</span
                                    >
                                </div>

                                <!-- Layer 2 (Middle) -->
                                <div
                                    class="absolute flex h-48 w-48 transform-[translateZ(40px)] items-center justify-center rounded-4xl border-[1.5px] border-[#2563eb]/30 bg-[#2563eb]/5 font-mono shadow-[0_20px_40px_rgba(37,99,235,0.1)] backdrop-blur-md transition-all duration-1500 ease-out group-hover:transform-[translateZ(60px)] group-hover:bg-[#2563eb]/10"
                                >
                                    <Layers class="h-14 w-14 text-[#2563eb]/40 transition-all duration-1500 group-hover:scale-110 group-hover:text-[#2563eb]/60" />
                                    <span
                                        class="absolute right-4 bottom-4 text-xs font-bold tracking-widest text-[#2563eb]/60"
                                        >SISTEM</span
                                    >
                                </div>

                                <!-- Layer 3 (Top) -->
                                <div
                                    class="absolute flex h-48 w-48 transform-[translateZ(80px)] items-center justify-center rounded-4xl border border-gray-700/80 bg-[#111827]/70 font-mono shadow-[0_30px_50px_rgba(17,24,39,0.3)] transition-all duration-1500 ease-out group-hover:transform-[translateZ(140px)] group-hover:border-[#b6ff00]/60 group-hover:shadow-[0_40px_60px_rgba(182,255,0,0.2)] group-hover:bg-[#111827]/90 overflow-hidden"
                                >
                                    <!-- ELECTRICITY BORDER (Spinning Glow) -->
                                    <div class="absolute inset-0 z-0 opacity-0 transition-opacity duration-1000 group-hover:opacity-100">
                                        <div class="absolute top-0 right-1/2 h-full w-[200%] origin-right animate-[spin_3s_linear_infinite] bg-linear-to-b from-transparent via-[#b6ff00] to-transparent opacity-70 blur-xs"></div>
                                    </div>
                                    <!-- Inner overlay to maintain dark core while keeping the edge glow -->
                                    <div class="absolute inset-[2px] z-10 flex cursor-pointer items-center justify-center rounded-4xl bg-[#111827]/90 backdrop-blur-2xl transition-all duration-1500 group-hover:bg-[#111827]">
                                        <!-- A glowing spark representing the Core/Engine -->
                                        <Zap
                                            class="h-16 w-16 text-white transition-all duration-1500 group-hover:scale-110 group-hover:text-[#b6ff00] group-hover:drop-shadow-[0_0_15px_rgba(182,255,0,0.5)]"
                                        />
                                        <span
                                            class="absolute right-4 bottom-4 text-xs font-bold tracking-widest text-[#b6ff00]/80"
                                            >PORTAL</span
                                        >
                                    </div>
                                </div>

                                <!-- ELECTRIC/DATA FLOW ANIMATIONS (Center energy) -->
                                <!-- We create multiple particles moving up through the Z axis. Without z-index they sort correctly in 3D -->
                                <div class="absolute opacity-0 transition-opacity duration-1000 group-hover:opacity-100 transform-3d">
                                    <div class="pointer-events-none absolute h-6 w-1.5 rounded-full bg-[#b6ff00] blur-[1px] shadow-[0_0_20px_#b6ff00] animate-data-flow-up"></div>
                                    <div class="pointer-events-none absolute -ml-3 mt-4 h-3 w-3 rounded-full bg-[#2563eb] blur-[1px] shadow-[0_0_20px_#2563eb] animate-data-flow-up" style="animation-delay: 0.7s;"></div>
                                    <div class="pointer-events-none absolute ml-4 -mt-2 h-2 w-2 rounded-full bg-[#10b981] blur-[1px] shadow-[0_0_20px_#10b981] animate-data-flow-up" style="animation-delay: 1.3s;"></div>
                                    <div class="pointer-events-none absolute ml-2 mt-3 h-5 w-1.5 rounded-full bg-[#b6ff00] blur-[1px] shadow-[0_0_15px_#b6ff00] animate-data-flow-up" style="animation-delay: 2s;"></div>
                                </div>

                                <!-- Flowing connecting lines (Beams) from satellites to center -->
                                <div class="absolute opacity-0 transition-opacity duration-1000 group-hover:opacity-100 transform-3d">
                                    <!-- Beam Left (FAST) -->
                                    <div class="pointer-events-none absolute inset-y-0 -left-14 my-auto h-[3px] w-20 overflow-hidden rounded-full border-l border-indigo-500/30 transform-[translateZ(100px)]">
                                        <div class="h-full w-full animate-beam-flow bg-linear-to-r from-transparent via-indigo-500 to-transparent blur-[1px]"></div>
                                    </div>
                                    <!-- Beam Right (WIMS) -->
                                    <div class="pointer-events-none absolute inset-y-0 -right-14 my-auto h-[3px] w-20 overflow-hidden rounded-full border-r border-pink-500/30 transform-[translateZ(60px)]">
                                        <div class="h-full w-full animate-beam-flow-reverse bg-linear-to-l from-transparent via-pink-500 to-transparent blur-[1px]" style="animation-delay: 0.3s;"></div>
                                    </div>
                                    <!-- Beam Bottom (PAGI) -->
                                    <div class="pointer-events-none absolute inset-x-0 -bottom-14 mx-auto h-20 w-[3px] overflow-hidden rounded-full border-b border-amber-500/30 transform-[translateZ(20px)]">
                                        <div class="h-full w-full animate-beam-flow-y bg-linear-to-t from-transparent via-amber-500 to-transparent blur-[1px]" style="animation-delay: 0.6s;"></div>
                                    </div>
                                </div>

                                <!-- Satellite Nodes floating around -->

                                <!-- Satellite node: FAST (Left) -->
                                <div
                                    class="absolute inset-y-0 -left-22 my-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-indigo-200 bg-white font-mono shadow-xl transition-all duration-1500 ease-out transform-[translateZ(0px)] group-hover:transform-[translateZ(100px)]"
                                >
                                    <span
                                        class="text-[10px] font-black tracking-widest text-indigo-500"
                                        >FAST</span
                                    >
                                </div>

                                <!-- Satellite node: WIMS (Right) -->
                                <div
                                    class="absolute inset-y-0 -right-22 my-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-pink-200 bg-white font-mono shadow-xl transition-all duration-1500 ease-out transform-[translateZ(0px)] group-hover:transform-[translateZ(60px)]"
                                >
                                    <span
                                        class="text-[10px] font-black tracking-widest text-pink-500"
                                        >WIMS</span
                                    >
                                </div>

                                <!-- Satellite node: PAGI (Bottom) -->
                                <div
                                    class="absolute inset-x-0 -bottom-22 mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-amber-200 bg-white font-mono shadow-xl transition-all duration-1500 ease-out transform-[translateZ(0px)] group-hover:transform-[translateZ(20px)]"
                                >
                                    <span
                                        class="text-[10px] font-black tracking-widest text-amber-500"
                                        >PAGI</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
                        <!-- Dynamic benefit items from settings -->
                        <div
                            v-for="(benefit, index) in [
                                { title: settings?.benefit_1_title || 'Akses Mudah', desc: settings?.benefit_1_desc || 'Satu platform untuk semua layanan akademik dan administratif.', icon: Zap },
                                { title: settings?.benefit_2_title || 'Data Real-Time', desc: settings?.benefit_2_desc || 'Informasi selalu terkini dan akurat langsung dari sumbernya.', icon: Database },
                                { title: settings?.benefit_3_title || 'Keamanan Tinggi', desc: settings?.benefit_3_desc || 'Sistem SSO dengan proteksi berlapis menjaga keamanan data.', icon: Users },
                                { title: 'Modern & Responsif', desc: 'Desain SaaS modern yang cepat diakses dari perangkat apa pun.', icon: MonitorSmartphone },
                            ]"
                            :key="index"
                            class="hide-animate slide-left group flex flex-col gap-4"
                            :style="{ transitionDelay: `${index * 150}ms` }"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl border border-blue-100 bg-blue-50/50 text-[#2563eb] transition-all duration-300 group-hover:-translate-y-1 group-hover:bg-[#2563eb] group-hover:text-white group-hover:shadow-md"
                            >
                                <component :is="benefit.icon" class="h-6 w-6" />
                            </div>
                            <div>
                                <h4 class="mb-2 text-lg font-bold text-[#111827]">{{ benefit.title }}</h4>
                                <p class="text-sm leading-relaxed text-[#6b7280]">{{ benefit.desc }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- USER TYPES SECTION -->
        <section
            id="about"
            class="relative z-0 overflow-hidden border-t border-gray-800 bg-gray-900 py-20 lg:py-28"
        >
            <!-- Background glow -->
            <div
                class="pointer-events-none absolute inset-0 bg-linear-to-b from-[#111827] to-gray-900"
            ></div>
            <!-- Grid Background Overlay -->
            <div
                class="bg-grid-pattern-dark pointer-events-none absolute inset-0"
                style="
                    -webkit-mask-image: linear-gradient(
                        to bottom,
                        transparent,
                        black 30%,
                        transparent 90%
                    );
                    mask-image: linear-gradient(
                        to bottom,
                        transparent,
                        black 30%,
                        transparent 90%
                    );
                "
            ></div>
            <div
                class="pointer-events-none absolute top-0 right-1/4 h-[400px] w-[400px] rounded-full bg-[#2563eb]/10 opacity-40 blur-[100px]"
            ></div>

            <div class="relative z-10 mx-auto max-w-7xl px-4 xl:px-0">
                <div
                    class="hide-animate slide-up mx-auto mb-16 max-w-3xl text-center"
                >
                    <h2
                        class="mb-3 text-sm font-bold tracking-wide text-[#b6ff00] uppercase"
                    >
                        Peran & Akses
                    </h2>
                    <h3 class="text-3xl font-extrabold text-white sm:text-4xl">
                        Sistem Multi-Role Dinamis
                    </h3>
                    <p class="mt-4 text-lg text-gray-400">
                        Setiap pangguna memiliki ruang kerja (dashboard) khusus
                        sesuai peran dan wewenang.
                    </p>
                </div>

                <div
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4 lg:gap-6"
                >
                    <div
                        v-for="(user, index) in userTypes"
                        :key="index"
                        class="hide-animate scale-in group cursor-pointer rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur-sm transition-all duration-300 hover:-translate-y-2 hover:border-[#b6ff00]/30 hover:bg-white/10 hover:shadow-[0_10px_30px_rgba(182,255,0,0.05)]"
                        :style="{ transitionDelay: `${index * 150}ms` }"
                    >
                        <h4
                            class="mb-3 flex items-center justify-between text-xl font-bold text-white transition-colors group-hover:text-[#b6ff00]"
                        >
                            {{ user.title }}
                            <ChevronRight
                                class="h-5 w-5 transform text-[#b6ff00] transition-transform group-hover:translate-x-1"
                            />
                        </h4>
                        <p class="text-sm leading-relaxed text-gray-400">
                            {{ user.description }}
                        </p>
                    </div>
                </div>
            </div>
        </section>


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
                                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=a042581f4e29026024d" alt="Andi Saputra" class="h-full w-full object-cover">
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
                                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=a042581f4e29026011d" alt="Rizky Pratama" class="h-full w-full object-cover">
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
                                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=a042581f4e29026704d" alt="Dr. Budi Santoso" class="h-full w-full object-cover">
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
                                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=a042581f4e29026715d" alt="Dina Aulia" class="h-full w-full object-cover">
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
                                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=a042581f4e29026725d" alt="Prof. Herman" class="h-full w-full object-cover">
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
                                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=a04258a2462d826729d" alt="Anton Setiawan" class="h-full w-full object-cover">
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
                                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=a04258a2462d826712d" alt="Siti Rahmawati" class="h-full w-full object-cover">
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

        <!-- SHOWCASE GALLERY (TAILWIND STYLE MASONRY) -->
        <section class="hide-animate relative overflow-hidden bg-gray-50 py-24 sm:py-32 fade-in border-t border-gray-100">
            <div class="relative z-10 mx-auto max-w-360 px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16 lg:mb-20 hide-animate slide-up">
                    <h2 class="text-4xl md:text-5xl lg:text-[4rem] font-extrabold tracking-tight text-slate-900 leading-tight mb-6">
                        Platform pilihan untuk skala besar.
                    </h2>
                    <p class="text-lg md:text-xl text-slate-500 max-w-3xl mx-auto font-medium">
                        Adaptif, super cepat, dan dipercaya oleh seluruh civitas akademika untuk menunjang produktivitas.
                    </p>
                </div>

                <!-- Tailwind CSS Columns for Masonry -->
                <div class="columns-2 sm:columns-3 lg:columns-4 xl:columns-5 gap-4 lg:gap-6 space-y-4 lg:space-y-6 relative">
                    
                    <!-- Item 1 -->
                    <div class="break-inside-avoid relative group rounded-2xl overflow-hidden bg-white shadow-sm ring-1 ring-gray-900/5 hide-animate slide-up">
                        <img src="https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=600&q=80" class="w-full h-auto transform transition duration-700 group-hover:scale-105" alt="Showcase" />
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors duration-300"></div>
                    </div>

                    <!-- Item 2 -->
                    <div class="break-inside-avoid relative group rounded-2xl overflow-hidden bg-white shadow-sm ring-1 ring-gray-900/5 hide-animate slide-up" style="transition-delay: 100ms">
                        <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=600&h=800&q=80" class="w-full h-auto transform transition duration-700 group-hover:scale-105" alt="Showcase" />
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors duration-300"></div>
                    </div>

                    <!-- Item 3 -->
                    <div class="break-inside-avoid relative group rounded-2xl overflow-hidden bg-white shadow-sm ring-1 ring-gray-900/5 hide-animate slide-up" style="transition-delay: 200ms">
                        <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=600&h=400&q=80" class="w-full h-auto transform transition duration-700 group-hover:scale-105" alt="Showcase" />
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors duration-300"></div>
                    </div>

                    <!-- Item 4 -->
                    <div class="break-inside-avoid relative group rounded-2xl overflow-hidden bg-white shadow-sm ring-1 ring-gray-900/5 hide-animate slide-up" style="transition-delay: 300ms">
                        <img src="https://images.unsplash.com/photo-1557672172-298e090bd0f1?auto=format&fit=crop&w=600&h=900&q=80" class="w-full h-auto transform transition duration-700 group-hover:scale-105" alt="Showcase" />
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors duration-300"></div>
                    </div>

                    <!-- Item 5 -->
                    <div class="break-inside-avoid relative group rounded-2xl overflow-hidden bg-white shadow-sm ring-1 ring-gray-900/5 hide-animate slide-up" style="transition-delay: 150ms">
                        <img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=600&h=700&q=80" class="w-full h-auto transform transition duration-700 group-hover:scale-105" alt="Showcase" />
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors duration-300"></div>
                    </div>

                    <!-- Item 6 -->
                    <div class="break-inside-avoid relative group rounded-2xl overflow-hidden bg-white shadow-sm ring-1 ring-gray-900/5 hide-animate slide-up" style="transition-delay: 250ms">
                        <img src="https://images.unsplash.com/photo-1611162617474-5b21e879e113?auto=format&fit=crop&w=600&h=500&q=80" class="w-full h-auto transform transition duration-700 group-hover:scale-105" alt="Showcase" />
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors duration-300"></div>
                    </div>

                    <!-- Item 7 -->
                    <div class="break-inside-avoid relative group rounded-2xl overflow-hidden bg-white shadow-sm ring-1 ring-gray-900/5 hide-animate slide-up" style="transition-delay: 50ms">
                        <img src="https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=600&h=1000&q=80" class="w-full h-auto transform transition duration-700 group-hover:scale-105" alt="Showcase" />
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors duration-300"></div>
                    </div>

                    <!-- Item 8 -->
                    <div class="break-inside-avoid relative group rounded-2xl overflow-hidden bg-white shadow-sm ring-1 ring-gray-900/5 hide-animate slide-up" style="transition-delay: 100ms">
                        <img src="https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?auto=format&fit=crop&w=600&h=600&q=80" class="w-full h-auto transform transition duration-700 group-hover:scale-105" alt="Showcase" />
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors duration-300"></div>
                    </div>

                    <!-- Item 9 -->
                    <div class="break-inside-avoid relative group rounded-2xl overflow-hidden bg-white shadow-sm ring-1 ring-gray-900/5 hide-animate slide-up" style="transition-delay: 200ms">
                        <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=600&h=850&q=80" class="w-full h-auto transform transition duration-700 group-hover:scale-105" alt="Showcase" />
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors duration-300"></div>
                    </div>

                    <!-- Item 10 -->
                    <div class="break-inside-avoid relative group rounded-2xl overflow-hidden bg-white shadow-sm ring-1 ring-gray-900/5 hide-animate slide-up" style="transition-delay: 300ms">
                        <img src="https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=600&h=400&q=80" class="w-full h-auto transform transition duration-700 group-hover:scale-105" alt="Showcase" />
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors duration-300"></div>
                    </div>

                    <!-- Item 11 -->
                    <div class="break-inside-avoid relative group rounded-2xl overflow-hidden bg-white shadow-sm ring-1 ring-gray-900/5 hide-animate slide-up" style="transition-delay: 150ms">
                        <img src="https://images.unsplash.com/photo-1550684848-fac1c5b4e853?auto=format&fit=crop&w=600&h=700&q=80" class="w-full h-auto transform transition duration-700 group-hover:scale-105" alt="Showcase" />
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors duration-300"></div>
                    </div>

                    <!-- Item 12 -->
                    <div class="break-inside-avoid relative group rounded-2xl overflow-hidden bg-white shadow-sm ring-1 ring-gray-900/5 hide-animate slide-up" style="transition-delay: 250ms">
                        <img src="https://images.unsplash.com/photo-1478147424044-caebd12467b7?auto=format&fit=crop&w=600&h=500&q=80" class="w-full h-auto transform transition duration-700 group-hover:scale-105" alt="Showcase" />
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors duration-300"></div>
                    </div>
                    
                    <!-- Item 13 -->
                    <div class="break-inside-avoid relative group rounded-2xl overflow-hidden bg-white shadow-sm ring-1 ring-gray-900/5 hide-animate slide-up" style="transition-delay: 50ms">
                        <img src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=600&h=900&q=80" class="w-full h-auto transform transition duration-700 group-hover:scale-105" alt="Showcase" />
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors duration-300"></div>
                    </div>
                    
                    <!-- Item 14 -->
                    <div class="break-inside-avoid relative group rounded-2xl overflow-hidden bg-white shadow-sm ring-1 ring-gray-900/5 hide-animate slide-up" style="transition-delay: 100ms">
                        <img src="https://images.unsplash.com/photo-1581291518633-83b4ebd1d83e?auto=format&fit=crop&w=600&h=600&q=80" class="w-full h-auto transform transition duration-700 group-hover:scale-105" alt="Showcase" />
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors duration-300"></div>
                    </div>
                    
                    <!-- Item 15 -->
                    <div class="break-inside-avoid relative group rounded-2xl overflow-hidden bg-white shadow-sm ring-1 ring-gray-900/5 hide-animate slide-up" style="transition-delay: 200ms">
                        <img src="https://images.unsplash.com/photo-1555421689-491a97ff2040?auto=format&fit=crop&w=600&h=500&q=80" class="w-full h-auto transform transition duration-700 group-hover:scale-105" alt="Showcase" />
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors duration-300"></div>
                    </div>

                    <!-- Gradient Overlay at bottom for soft fade -->
                    <div class="absolute inset-x-0 bottom-0 h-64 bg-linear-to-t from-gray-50 via-gray-50/80 to-transparent pointer-events-none z-10"></div>
                </div>

                <div class="relative z-20 mt-8 flex justify-center hide-animate slide-up">
                    <button class="rounded-full bg-[#111827] px-8 py-3.5 text-sm font-semibold text-white shadow-xl hover:bg-gray-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black transition-all hover:-translate-y-1">
                        Jelajahi Lebih Banyak Modul
                    </button>
                </div>
            </div>
        </section>

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
                            <img src="/images/indonesia-map.svg" alt="Map of Indonesia" class="w-full h-full object-contain" style="filter: drop-shadow(0 4px 6px rgba(0,0,0,0.05));" />
                        </div>
                        
                        <!-- Floating Total Alumni Card -->
                        <div class="absolute top-0 right-1/4 sm:right-1/3 bg-white/80 backdrop-blur-md border border-slate-100 rounded-2xl p-4 shadow-xl shadow-slate-200/50 flex items-center gap-4 z-30 animate-[bounce_4s_infinite]">
                            <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 font-semibold mb-0.5">Total Alumni</p>
                                <div class="flex items-center gap-2">
                                    <p class="text-lg font-black text-slate-900 leading-none">4.782</p>
                                    <span class="text-[10px] font-bold text-green-500 bg-green-50 px-1.5 py-0.5 rounded flex items-center gap-0.5"><svg class="w-2 h-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7" /></svg> 12%</span>
                                </div>
                                <p class="text-[9px] text-slate-400 mt-1">dari tahun lalu</p>
                            </div>
                        </div>

                        <!-- Floating Profile Card (Interactive Pop-up) -->
                        <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-4">
                            <div v-if="showAlumniPopup" class="absolute right-0 sm:right-10 top-20 bg-white/90 backdrop-blur-xl border border-slate-100/50 rounded-3xl p-5 shadow-2xl shadow-blue-900/5 w-64 z-40 transition-transform duration-500 hover:-translate-y-2 group">
                                <button @click="showAlumniPopup = false" class="absolute top-3 right-3 text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-full p-1 transition-colors z-50">
                                    <X class="w-4 h-4" />
                                </button>
                                <div class="flex flex-col items-center text-center">
                                    <!-- Profile Image inside a circular polygon/ring -->
                                <div class="relative w-20 h-20 mb-4">
                                    <div class="absolute inset-0 rounded-full border-2 border-dashed border-blue-200 animate-[spin_10s_linear_infinite]"></div>
                                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=10" alt="Fauzan Rachman Avatar" class="absolute inset-1 rounded-full object-cover shadow-md" />
                                    <div class="absolute bottom-0 right-0 w-5 h-5 bg-green-500 border-2 border-white rounded-full"></div>
                                </div>
                                <h4 class="font-bold text-slate-900 text-base mb-1">Fauzan Rachman</h4>
                                <span class="bg-blue-50 text-blue-600 text-[10px] font-bold px-2 py-0.5 rounded-full mb-3">Alumni 2021</span>
                                
                                <div class="w-full flex items-center gap-2 text-left mb-2 px-2">
                                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    <p class="text-[10px] text-slate-500 font-medium leading-tight">Software Engineer<br>at Gojek</p>
                                </div>
                                <div class="w-full flex items-center gap-2 text-left mb-4 px-2">
                                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <p class="text-[10px] text-slate-500 font-medium">Jakarta, Indonesia</p>
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
                        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=20" alt="Alumni avatar" class="absolute top-[30%] left-[30%] w-6 h-6 rounded-full border-2 border-white shadow-md z-20 hover:scale-125 transition-transform cursor-pointer" />
                        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=30" alt="Alumni avatar" class="absolute top-[25%] left-[55%] w-7 h-7 rounded-full border-2 border-white shadow-md z-20 hover:scale-125 transition-transform cursor-pointer" />
                        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=40" alt="Alumni avatar" class="absolute top-[45%] left-[65%] w-8 h-8 rounded-full border-2 border-white shadow-md z-20 hover:scale-125 transition-transform cursor-pointer" />
                        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=50" alt="Alumni avatar" class="absolute top-[35%] left-[45%] w-6 h-6 rounded-full border-2 border-white shadow-md z-20 hover:scale-125 transition-transform cursor-pointer" />
                        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=60" alt="Alumni avatar" class="absolute top-[65%] left-[60%] w-7 h-7 rounded-full border-2 border-white shadow-md z-20 hover:scale-125 transition-transform cursor-pointer" />
                        
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

            </div>
        </section>

        <!-- FOOTER -->
        <PublicFooter />
    </div>


</template>
<style>
.bg-grid-pattern {
    background-size: 40px 40px;
    background-image:
        linear-gradient(to right, rgba(0, 0, 0, 0.035) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(0, 0, 0, 0.035) 1px, transparent 1px);
}
.bg-grid-pattern-dark {
    background-size: 40px 40px;
    background-image:
        linear-gradient(
            to right,
            rgba(255, 255, 255, 0.045) 1px,
            transparent 1px
        ),
        linear-gradient(
            to bottom,
            rgba(255, 255, 255, 0.045) 1px,
            transparent 1px
        );
}

/* Smooth scrolling */
html {
    scroll-behavior: smooth;
}

/* Animations that are running indefinitely */
@keyframes bounce-slow {
    0%,
    100% {
        transform: translateY(-5%);
        animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
    }
    50% {
        transform: translateY(0);
        animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
    }
}
.animate-bounce-slow {
    animation: bounce-slow 4s infinite;
}

@keyframes pulse-slow {
    0%,
    100% {
        opacity: 0.4;
        transform: scale(1);
    }
    50% {
        opacity: 0.6;
        transform: scale(1.05);
    }
}
.animate-pulse-slow {
    animation: pulse-slow 5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Marquee Animation */
@keyframes marquee {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
.animate-marquee {
    animation: marquee 30s linear infinite;
}
.animate-marquee:hover {
    animation-play-state: paused;
}

/* Base starting styles for elements waiting to be revealed */
.hide-animate {
    opacity: 0;
    will-change: transform, opacity;
}

/* Slide Up */
.hide-animate.slide-up {
    transform: translateY(40px);
}
/* Slide Left (moving leftwards from right) */
.hide-animate.slide-left {
    transform: translateX(40px);
}
/* Slide Right (moving rightwards from left) */
.hide-animate.slide-right {
    transform: translateX(-40px);
}
.hide-animate.scale-in {
    transform: scale(0.9);
}

/* Once class 'show-animate' is added by JS intersection observer */
.show-animate {
    opacity: 1 !important;
    transform: translate(0, 0) scale(1) !important;
    transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1) !important;
}

/* Data Flow Animation (Electric Flow Vertical) */
@keyframes data-flow-up {
    0% { transform: translateZ(-30px) scale(0); opacity: 0; }
    20% { opacity: 1; transform: translateZ(20px) scale(1); }
    80% { opacity: 1; transform: translateZ(140px) scale(1.5); }
    100% { transform: translateZ(180px) scale(0); opacity: 0; }
}
.animate-data-flow-up {
    animation: data-flow-up 2.5s cubic-bezier(0.3, 0, 0.2, 1) infinite;
}

/* Horizontal Data Beams */
@keyframes beam-flow {
    0% { transform: translateX(-100%); opacity: 0; }
    30% { opacity: 1; }
    70% { opacity: 1; }
    100% { transform: translateX(100%); opacity: 0; }
}
.animate-beam-flow {
    animation: beam-flow 1.5s linear infinite;
}

@keyframes beam-flow-reverse {
    0% { transform: translateX(100%); opacity: 0; }
    30% { opacity: 1; }
    70% { opacity: 1; }
    100% { transform: translateX(-100%); opacity: 0; }
}
.animate-beam-flow-reverse {
    animation: beam-flow-reverse 1.5s linear infinite;
}

@keyframes beam-flow-y {
    0% { transform: translateY(100%); opacity: 0; }
    30% { opacity: 1; }
    70% { opacity: 1; }
    100% { transform: translateY(-100%); opacity: 0; }
}
.animate-beam-flow-y {
    animation: beam-flow-y 1.5s linear infinite;
}
</style>
