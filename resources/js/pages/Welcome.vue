<script setup lang="ts">
import { Head, Link, Deferred } from "@inertiajs/vue3";
import {
    ArrowRight,
    BookOpen,
    Briefcase,
    CheckCircle2,
    ChevronRight,
    FileText,
    GraduationCap,
    X,
    Megaphone,
} from "lucide-vue-next";
import {
    computed,
    defineAsyncComponent,
    onMounted,
    onUnmounted,
    ref,
} from "vue";
import PublicFooter from "@/components/Portal/PublicFooter.vue";
import PublicNavbar from "@/components/Portal/PublicNavbar.vue";
import LazyWrapper from "@/components/Portal/LazyWrapper.vue";
import { register } from "@/routes";

const HeroGallery = defineAsyncComponent(
    () => import("@/components/Portal/HeroGallery.vue"),
);
const MasonryGallery = defineAsyncComponent(
    () => import("@/components/Portal/MasonryGallery.vue"),
);
const Testimonials = defineAsyncComponent(
    () => import("@/components/Portal/Testimonials.vue"),
);
const AlumniMap = defineAsyncComponent(
    () => import("@/components/Portal/AlumniMap.vue"),
);

const optimizeImageUrl = (url: string, width = 800) => {
    if (!url) return "";
    if (url.includes("images.unsplash.com")) {
        if (url.includes("w=")) {
            return url.replace(/w=\d+/, `w=${width}`).replace(/q=\d+/, "q=80");
        }
        const separator = url.includes("?") ? "&" : "?";
        return `${url}${separator}w=${width}&q=80&auto=format&fit=crop`;
    }
    return url;
};

const getUnsplashSrcset = (url: string) => {
    if (!url || !url.includes("images.unsplash.com")) return undefined;
    const baseUrl = url.split("?")[0];
    return `${baseUrl}?auto=format&fit=crop&w=300&q=80 300w,
			${baseUrl}?auto=format&fit=crop&w=600&q=80 600w,
			${baseUrl}?auto=format&fit=crop&w=900&q=80 900w,
			${baseUrl}?auto=format&fit=crop&w=1200&q=80 1200w`;
};

const _templateComponents = [
    Head,
    Link,
    ArrowRight,
    CheckCircle2,
    ChevronRight,
    X,
    PublicFooter,
    PublicNavbar,
    register,
    Megaphone,
];

const props = withDefaults(
    defineProps<{
        canRegister?: boolean;
        // biome-ignore lint/suspicious/noExplicitAny: library config
        settings?: Record<string, any>;
        // biome-ignore lint/suspicious/noExplicitAny: library config
        latest_posts?: Array<any>;
        pinned_announcement?: any;
        alumni_data?: Array<any>;
        total_alumni?: number;
        alumni_stats?: any;
    }>(),
    {
        canRegister: true,
        settings: () => ({}),
        latest_posts: () => [],
        pinned_announcement: null,
        alumni_data: () => [],
        total_alumni: 0,
        alumni_stats: () => ({}),
    },
);

const isBannerDismissed = ref(false);

const dismissBanner = () => {
    if (props.pinned_announcement) {
        localStorage.setItem(
            "dismissed_announcement_id",
            String(props.pinned_announcement.id),
        );
    }
    isBannerDismissed.value = true;
};

const gallery = computed<string[]>(() => props.settings?.hero_gallery || []);

const isMobileMenuOpen = ref(false);
const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

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

// benefits array removed

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

const newsItems = [
    {
        title: "Peluncuran Modul PAGI untuk Mahasiswa",
        date: "12 Okt 2026",
        category: "Update Sistem",
        excerpt:
            "Modul portofolio mahasiswa resmi dirilis. Kini setiap mahasiswa dapat mencetak CV otomatis dan pamer karya langsung dari satu dashboard.",
        image: "https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&q=80&w=800",
    },
    {
        title: "Integrasi Industri: Rekrutmen Magang via WIMS",
        date: "08 Sep 2026",
        category: "Kerjasama",
        excerpt:
            "FMIKOM menggandeng 20+ perusahaan teknologi untuk membuka lowongan magang eksklusif yang bisa di-apply langsung.",
        image: "https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&q=80&w=800",
    },
    {
        title: "Standarisasi Tracer Study Alumni 2026",
        date: "15 Agu 2026",
        category: "Akademik",
        excerpt:
            "Survei tracer study kini lebih ringkas dan berhadiah! Masukkan data karirmu dan bantu prodi mencapai akreditasi Unggul.",
        image: "https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80&w=800",
    },
];

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

const testimonials = [
    {
        quote: "Sistem FAST benar-benar mengubah cara saya mengajukan persuratan. Dulu butuh 3 hari, sekarang hanya hitungan jam sudah disetujui Kaprodi!",
        name: "Andi Saputra",
        role: "Mahasiswa Semester 6",
        avatar: "https://api.dicebear.com/7.x/avataaars/svg?seed=a042581f4e29026024d",
    },
    {
        quote: "Sebagai dosen pembimbing, memantau logbook magang mahasiswa via WIMS sangat menghemat waktu. Semua terpusat dan mudah diakses.",
        name: "Dr. Budi Santoso, M.Kom",
        role: "Dosen Pembimbing",
        avatar: "https://api.dicebear.com/7.x/avataaars/svg?seed=a042581f4e29026704d",
    },
    {
        quote: "Saya mendapat pekerjaan pertama saya karena profil portofolio yang saya bangun dilihat langsung oleh mitra FMIKOM.",
        name: "Siti Rahmawati",
        role: "Alumni Angkatan 2022",
        avatar: "https://api.dicebear.com/7.x/avataaars/svg?seed=a04258a2462d826712d",
    },
];

onMounted(() => {
    if (props.pinned_announcement) {
        const dismissedId = localStorage.getItem("dismissed_announcement_id");
        if (dismissedId === String(props.pinned_announcement.id)) {
            isBannerDismissed.value = true;
        }
    }

    setTimeout(() => {
        updateNewsScrollState();
    }, 300);
    window.addEventListener("resize", updateNewsScrollState);
});

onUnmounted(() => {
    window.removeEventListener("resize", updateNewsScrollState);
});

const formatDate = (dateString: string) => {
    if (!dateString) return "";
    return new Date(dateString).toLocaleDateString("en-GB", {
        day: "numeric",
        month: "long",
        year: "numeric",
    });
};

const extractText = (content: string) => {
    if (!content) return "";
    try {
        const parsed = JSON.parse(content);
        if (parsed?.blocks && Array.isArray(parsed.blocks)) {
            const textContent = parsed.blocks
                .filter(
                    // biome-ignore lint/suspicious/noExplicitAny: parsing block type
                    (b: any) =>
                        b.type === "paragraph" ||
                        b.type === "header" ||
                        b.type === "list",
                )
                // biome-ignore lint/suspicious/noExplicitAny: parsing block structure
                .map((b: any) => {
                    if (b.type === "list" && b.data?.items) {
                        return (
                            b.data.items
                                // biome-ignore lint/suspicious/noExplicitAny: parsing item inside list block
                                .map((item: any) =>
                                    typeof item === "string"
                                        ? item
                                        : item.content || "",
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

// biome-ignore lint/suspicious/noExplicitAny: post object shape
const getPostExcerpt = (post: any, limit: number) => {
    if (post.excerpt) return post.excerpt;
    if (post.meta_description) return post.meta_description;
    const text = extractText(post.content);
    if (!text) return "";
    if (text.length <= limit) return text;
    return `${text.substring(0, limit)}...`;
};

const toTitleCase = (str: string) => {
    if (!str) return "";
    return str
        .toLowerCase()
        .split(" ")
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(" ");
};

// --- Carousel State & Logic for News Section ---
const newsScrollContainer = ref<HTMLDivElement | null>(null);
const canScrollPrev = ref(false);
const canScrollNext = ref(true);

const updateNewsScrollState = () => {
    if (!newsScrollContainer.value) return;
    requestAnimationFrame(() => {
        if (!newsScrollContainer.value) return;
        const { scrollLeft, scrollWidth, clientWidth } =
            newsScrollContainer.value;
        canScrollPrev.value = scrollLeft > 10;
        canScrollNext.value = scrollLeft + clientWidth < scrollWidth - 10;
    });
};

const scrollNews = (direction: "prev" | "next") => {
    if (!newsScrollContainer.value) return;
    const firstItem = newsScrollContainer.value.querySelector(".snap-start");
    const cardWidth = firstItem ? firstItem.getBoundingClientRect().width : 452;
    const gap = 24;
    const scrollAmount =
        direction === "next" ? cardWidth + gap : -(cardWidth + gap);
    newsScrollContainer.value.scrollBy({
        left: scrollAmount,
        behavior: "smooth",
    });
};

const currentYear = new Date().getFullYear();
</script>

<template>
    <Head>
        <title>
            {{ settings.brand_name || "Portal FMIKOM" }} -
            {{ settings.hero_subtitle || "Sistem Informasi Terpadu" }}
        </title>
        <meta
            name="description"
            :content="
                settings.brand_description ||
                'Sistem informasi terpadu untuk civitas akademika FMIKOM.'
            "
        />
    </Head>

    <div
        class="min-h-screen overflow-clip bg-[#ffffff] font-sans text-[#111827] selection:bg-[#b6ff00] selection:text-gray-900"
    >
        <!-- ANNOUNCEMENT BANNER -->
        <div
            v-if="pinned_announcement && !isBannerDismissed"
            class="relative isolate flex items-center gap-x-6 overflow-hidden bg-[#2563eb] px-6 py-2.5 pr-12 sm:px-3.5 sm:pr-3.5 sm:before:flex-1 text-white border-b border-blue-700 shadow-md transition-all duration-300 font-sans"
        >
            <!-- Backdrop Glows -->
            <div
                class="absolute top-1/2 left-[max(-7rem,calc(50%-52rem))] -z-10 -translate-y-1/2 transform-gpu blur-2xl"
                aria-hidden="true"
            >
                <div
                    class="aspect-577/310 w-144.25 bg-linear-to-r from-[#60a5fa] to-[#a78bfa] opacity-30"
                    style="
                        clip-path: polygon(
                            74.8% 41.9%,
                            97.2% 73.2%,
                            100% 34.9%,
                            92.5% 0.4%,
                            87.5% 0%,
                            75% 28.6%,
                            58.5% 54.6%,
                            50.1% 56.8%,
                            46.9% 44%,
                            48.3% 17.4%,
                            24.7% 53.9%,
                            0% 27.9%,
                            11.9% 74.2%,
                            24.9% 54.1%,
                            68.6% 100%,
                            74.8% 41.9%
                        );
                    "
                ></div>
            </div>
            <div
                class="absolute top-1/2 left-[max(45rem,calc(50%+8rem))] -z-10 -translate-y-1/2 transform-gpu blur-2xl"
                aria-hidden="true"
            >
                <div
                    class="aspect-577/310 w-144.25 bg-linear-to-r from-[#60a5fa] to-[#a78bfa] opacity-30"
                    style="
                        clip-path: polygon(
                            74.8% 41.9%,
                            97.2% 73.2%,
                            100% 34.9%,
                            92.5% 0.4%,
                            87.5% 0%,
                            75% 28.6%,
                            58.5% 54.6%,
                            50.1% 56.8%,
                            46.9% 44%,
                            48.3% 17.4%,
                            24.7% 53.9%,
                            0% 27.9%,
                            11.9% 74.2%,
                            24.9% 54.1%,
                            68.6% 100%,
                            74.8% 41.9%
                        );
                    "
                ></div>
            </div>

            <!-- Banner Content -->
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 max-w-full">
                <p class="text-sm leading-6 text-white font-sans">
                    <strong class="font-semibold text-white">{{
                        pinned_announcement.category || "Penting"
                    }}</strong>
                    <svg
                        viewBox="0 0 2 2"
                        class="mx-2 inline w-1.5 h-1.5 fill-current text-white/80"
                        aria-hidden="true"
                    >
                        <circle cx="1" cy="1" r="1" />
                    </svg>
                    {{ pinned_announcement.title }}
                </p>
                <a
                    v-if="pinned_announcement.file_path"
                    :href="`/dokumen/download/${pinned_announcement.id}`"
                    class="flex-none rounded-md bg-white px-3.5 py-1 text-sm font-semibold text-blue-600 shadow-xs hover:bg-blue-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white active:scale-95 transition-all duration-200"
                >
                    Download
                </a>
                <a
                    v-else
                    href="/dokumen"
                    class="flex-none rounded-md bg-white px-3.5 py-1 text-sm font-semibold text-blue-600 shadow-xs hover:bg-blue-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white active:scale-95 transition-all duration-200"
                >
                    Lihat Detail
                </a>
            </div>

            <!-- Close Button -->
            <div
                class="absolute right-3 top-1/2 -translate-y-1/2 sm:relative sm:top-auto sm:-translate-y-0 sm:right-auto flex sm:flex-1 sm:justify-end"
            >
                <button
                    type="button"
                    @click="dismissBanner"
                    class="-m-3 p-3 focus-visible:-outline-offset-4 text-white hover:text-blue-100 transition-colors cursor-pointer"
                >
                    <span class="sr-only">Dismiss</span>
                    <X class="w-5 h-5" aria-hidden="true" />
                </button>
            </div>
        </div>

        <!-- NAVBAR -->
        <PublicNavbar v-if="settings?.show_navbar !== '0'" />

        <main id="main-content">
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
                            class="mx-auto max-w-2xl text-center lg:mx-0 lg:text-left"
                        >
                            <div
                                class="mb-6 inline-flex items-center rounded-full border border-gray-200 bg-white/50 px-3 py-1 text-sm font-semibold text-[#2563eb] shadow-sm backdrop-blur-sm"
                            >
                                <span
                                    class="mr-2 flex h-2 w-2 rounded-full bg-[#b6ff00]"
                                ></span>
                                {{
                                    settings?.hero_subtitle ||
                                    "Sistem Informasi Terpadu"
                                }}
                            </div>
                            <h1
                                class="text-4xl leading-tight font-extrabold tracking-tight text-[#111827] drop-shadow-sm sm:text-5xl lg:text-6xl whitespace-pre-line"
                            >
                                {{
                                    settings?.hero_title ||
                                    "Satu Portal untuk \nSemua Layanan \nFMIKOM"
                                }}
                            </h1>
                            <p
                                class="mx-auto mt-6 max-w-xl text-lg leading-relaxed text-slate-600 sm:text-xl lg:mx-0"
                            >
                                {{
                                    settings?.hero_description ||
                                    "Kelola administrasi, magang, alumni, dan portofolio dalam satu sistem terintegrasi. Dibangun untuk memberikan pengalaman terbaik bergaya modern."
                                }}
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
                                class="mt-10 flex items-center justify-center gap-4 text-sm font-medium text-[#4b5563] lg:justify-start"
                            >
                                <div class="flex items-center gap-1.5">
                                    <CheckCircle2
                                        class="h-4 w-4 rounded-full bg-gray-900 text-[#b6ff00]"
                                    />
                                    Mudah
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <CheckCircle2
                                        class="h-4 w-4 rounded-full bg-gray-900 text-[#b6ff00]"
                                    />
                                    Cepat
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <CheckCircle2
                                        class="h-4 w-4 rounded-full bg-gray-900 text-[#b6ff00]"
                                    />
                                    Terpusat
                                </div>
                            </div>
                        </div>

                        <!-- Hero Gallery Card Stack -->
                        <div
                            class="relative mx-auto w-full max-w-[500px] lg:max-w-none flex items-center justify-center min-h-[400px]"
                        >
                            <HeroGallery :gallery="gallery" />
                        </div>
                    </div>
                </div>
            </section>

            <!-- DYNAMIC POSTS SECTION -->
            <section
                v-if="settings?.show_features !== '0'"
                id="news"
                class="bg-white py-20 lg:py-28 overflow-hidden"
            >
                <div class="mx-auto max-w-[1216px] px-4">
                    <!-- Header with navigation arrows on the right -->
                    <div
                        class="mb-10 flex flex-col justify-between md:mb-14 md:flex-row md:items-end"
                    >
                        <div class="max-w-2xl">
                            <h2
                                class="text-4xl lg:text-5xl font-bold text-[#111827] mb-4 tracking-tight"
                            >
                                Info Terbaru FMIKOM
                            </h2>
                            <p
                                class="text-[17px] text-[#4b5563] leading-relaxed"
                            >
                                Sorotan terbaru mengenai kegiatan akademik,
                                prestasi, dan pembaruan sistem di lingkungan
                                fakultas.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Wrap dynamic posts loading in Deferred -->
                <Deferred data="latest_posts">
                    <template #fallback>
                        <div class="w-full">
                            <div
                                class="flex gap-6 overflow-x-auto hide-scrollbar px-4 md:pl-[max(1rem,calc((100vw-1216px)/2+1rem))] md:pr-[max(1rem,calc((100vw-1216px)/2+1rem))]"
                            >
                                <div
                                    v-for="i in 3"
                                    :key="i"
                                    class="shrink-0 w-[85vw] sm:w-[420px] md:w-[452px] flex flex-col justify-between animate-pulse"
                                >
                                    <div>
                                        <div
                                            class="relative w-full aspect-16/10 rounded-xl bg-slate-100 border border-slate-200/60 shadow-xs mb-5"
                                        ></div>
                                        <div
                                            class="h-4 bg-slate-100 rounded w-1/3 mb-3"
                                        ></div>
                                        <div
                                            class="h-6 bg-slate-100 rounded w-3/4 mb-3"
                                        ></div>
                                        <div
                                            class="h-4 bg-slate-100 rounded w-5/6 mb-2"
                                        ></div>
                                        <div
                                            class="h-4 bg-slate-100 rounded w-2/3 mb-6"
                                        ></div>
                                    </div>
                                    <div
                                        class="h-4 bg-slate-100 rounded w-1/4 mt-2"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template #default>
                        <div
                            v-if="latest_posts.length === 0"
                            class="mx-auto max-w-[1216px] px-4 py-12 text-center text-gray-500 font-medium"
                        >
                            Belum ada postingan yang dipublikasikan.
                        </div>
                        <div v-else class="w-full">
                            <!-- Navigation Buttons (rendered dynamically if we have posts) -->
                            <div
                                class="mx-auto max-w-[1216px] px-4 flex justify-end gap-2.5 -mt-20 mb-10 relative z-20"
                            >
                                <button
                                    @click="scrollNews('prev')"
                                    :disabled="!canScrollPrev"
                                    class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:text-gray-900 disabled:opacity-40 disabled:pointer-events-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                    title="Sebelumnya"
                                    aria-label="Sebelumnya"
                                >
                                    <ArrowRight class="h-5 w-5 rotate-180" />
                                </button>
                                <button
                                    @click="scrollNews('next')"
                                    :disabled="!canScrollNext"
                                    class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:text-gray-900 disabled:opacity-40 disabled:pointer-events-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                    title="Selanjutnya"
                                    aria-label="Selanjutnya"
                                >
                                    <ArrowRight class="h-5 w-5" />
                                </button>
                            </div>

                            <div
                                ref="newsScrollContainer"
                                @scroll="updateNewsScrollState"
                                class="flex gap-6 overflow-x-auto snap-x snap-mandatory scroll-smooth hide-scrollbar px-4 md:pl-[max(1rem,calc((100vw-1216px)/2+1rem))] md:pr-[max(1rem,calc((100vw-1216px)/2+1rem))]"
                            >
                                <div
                                    v-for="(post, index) in latest_posts"
                                    :key="post.id"
                                    v-memo="[post.id]"
                                    class="snap-start shrink-0 w-[85vw] sm:w-[420px] md:w-[452px] group cursor-pointer flex flex-col justify-between"
                                    @click="
                                        $inertia.visit('/berita/' + post.slug)
                                    "
                                >
                                    <div>
                                        <div
                                            class="relative w-full aspect-16/10 rounded-xl overflow-hidden bg-slate-50 border border-slate-100 shadow-xs mb-5"
                                        >
                                            <div
                                                class="w-full h-full origin-bottom transition duration-300 group-hover:scale-102"
                                            >
                                                <img
                                                    v-if="post.thumbnail"
                                                    :src="
                                                        optimizeImageUrl(
                                                            post.thumbnail,
                                                            600,
                                                        )
                                                    "
                                                    :srcset="
                                                        getUnsplashSrcset(
                                                            post.thumbnail,
                                                        )
                                                    "
                                                    sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 452px"
                                                    :alt="post.title"
                                                    class="w-full h-full object-cover object-center"
                                                    width="452"
                                                    height="283"
                                                    loading="lazy"
                                                    decoding="async"
                                                />
                                                <div
                                                    v-else
                                                    class="w-full h-full flex items-center justify-center text-slate-300"
                                                >
                                                    <FileText
                                                        class="w-16 h-16"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <span
                                            class="text-sm font-medium text-slate-600 mb-3 flex items-center gap-1.5"
                                        >
                                            {{
                                                toTitleCase(
                                                    post.user?.name || "Admin",
                                                )
                                            }}
                                            <span class="text-slate-300"
                                                >&bull;</span
                                            >
                                            {{
                                                formatDate(
                                                    post.published_at ||
                                                        post.created_at,
                                                )
                                            }}
                                        </span>
                                        <h4
                                            class="text-xl md:text-2xl font-semibold text-slate-900 leading-tight mb-3 group-hover:text-blue-600 transition-colors line-clamp-3 tracking-tight"
                                        >
                                            {{ post.title }}
                                        </h4>
                                        <p
                                            v-if="getPostExcerpt(post, 120)"
                                            class="text-[15px] text-slate-600 leading-relaxed mb-6 line-clamp-2"
                                        >
                                            {{ getPostExcerpt(post, 120) }}
                                        </p>
                                    </div>
                                    <div
                                        class="flex items-center text-sm font-semibold text-blue-600 mt-2"
                                    >
                                        Baca selengkapnya
                                        <ArrowRight
                                            class="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </Deferred>
            </section>

            <!-- PARTNERS SECTION -->
            <section
                v-if="
                    settings?.show_partners !== '0' &&
                    settings?.partners?.length > 0
                "
                class="border-t border-gray-100 bg-gray-50/30 pt-12 pb-24 overflow-hidden"
                v-once
            >
                <div class="mx-auto max-w-7xl px-4 xl:px-0 mb-8">
                    <p
                        class="text-center text-xs font-bold tracking-widest text-slate-600 uppercase"
                    >
                        Telah Bekerja Sama Dengan
                    </p>
                </div>

                <!-- Infinite Marquee Slider -->
                <div
                    class="relative flex w-full flex-col justify-center overflow-hidden border-y border-gray-200"
                >
                    <!-- Left/Right Gradient Masks for smooth fade -->
                    <div
                        class="pointer-events-none absolute inset-y-0 left-0 z-10 w-24 bg-linear-to-r from-[#f8fafc] to-transparent sm:w-40"
                    ></div>
                    <div
                        class="pointer-events-none absolute inset-y-0 right-0 z-10 w-24 bg-linear-to-l from-[#f8fafc] to-transparent sm:w-40"
                    ></div>

                    <div class="flex w-max animate-marquee">
                        <!-- First Set -->
                        <div class="flex shrink-0 items-center">
                            <div
                                v-for="(partner, i) in settings.partners"
                                :key="'p1-' + i"
                                class="group flex h-20 w-40 sm:h-24 sm:w-56 items-center justify-center border-r border-gray-200 transition-all hover:bg-gray-100/50"
                            >
                                <img
                                    :src="
                                        typeof partner === 'object'
                                            ? partner.logo
                                            : partner
                                    "
                                    :alt="
                                        typeof partner === 'object' &&
                                        partner.name
                                            ? partner.name
                                            : 'Partner Logo'
                                    "
                                    class="h-10 w-28 sm:h-12 sm:w-40 object-contain grayscale opacity-60 transition-all duration-300 group-hover:scale-110 group-hover:grayscale-0 group-hover:opacity-100"
                                    width="160"
                                    height="48"
                                    loading="lazy"
                                    decoding="async"
                                />
                            </div>
                        </div>
                        <!-- Second Set (Duplicate) -->
                        <div class="flex shrink-0 items-center">
                            <div
                                v-for="(partner, i) in settings.partners"
                                :key="'p2-' + i"
                                class="group flex h-20 w-40 sm:h-24 sm:w-56 items-center justify-center border-r border-gray-200 transition-all hover:bg-gray-100/50"
                            >
                                <img
                                    :src="
                                        typeof partner === 'object'
                                            ? partner.logo
                                            : partner
                                    "
                                    :alt="
                                        typeof partner === 'object' &&
                                        partner.name
                                            ? partner.name
                                            : 'Partner Logo'
                                    "
                                    class="h-10 w-28 sm:h-12 sm:w-40 object-contain grayscale opacity-60 transition-all duration-300 group-hover:scale-110 group-hover:grayscale-0 group-hover:opacity-100"
                                    width="160"
                                    height="48"
                                    loading="lazy"
                                    decoding="async"
                                />
                            </div>
                        </div>
                        <!-- Third Set (Duplicate for wide screens) -->
                        <div class="flex shrink-0 items-center">
                            <div
                                v-for="(partner, i) in settings.partners"
                                :key="'p3-' + i"
                                class="group flex h-20 w-40 sm:h-24 sm:w-56 items-center justify-center border-r border-gray-200 transition-all hover:bg-gray-100/50"
                            >
                                <img
                                    :src="
                                        typeof partner === 'object'
                                            ? partner.logo
                                            : partner
                                    "
                                    :alt="
                                        typeof partner === 'object' &&
                                        partner.name
                                            ? partner.name
                                            : 'Partner Logo'
                                    "
                                    class="h-10 w-28 sm:h-12 sm:w-40 object-contain grayscale opacity-60 transition-all duration-300 group-hover:scale-110 group-hover:grayscale-0 group-hover:opacity-100"
                                    width="160"
                                    height="48"
                                    loading="lazy"
                                    decoding="async"
                                />
                            </div>
                            <!-- Benefits section removed -->
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
                        <h3
                            class="text-3xl font-extrabold text-white sm:text-4xl"
                        >
                            Sistem Multi-Role Dinamis
                        </h3>
                        <p class="mt-4 text-lg text-gray-400">
                            Setiap pangguna memiliki ruang kerja (dashboard)
                            khusus sesuai peran dan wewenang.
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
            <LazyWrapper>
                <Testimonials />
            </LazyWrapper>

            <!-- SHOWCASE GALLERY (TAILWIND STYLE MASONRY) -->
            <LazyWrapper placeholderClass="h-96">
                <MasonryGallery />
            </LazyWrapper>

            <!-- ALUMNI MAP TRACKING SECTION -->
            <LazyWrapper>
                <AlumniMap
                    :alumni-data="alumni_data"
                    :total-alumni="total_alumni"
                    :alumni-stats="alumni_stats"
                />
            </LazyWrapper>
        </main>

        <!-- FOOTER -->
        <PublicFooter />

        <!-- Bottom Progressive Blur Overlay -->
        <div
            class="progressive-blur pointer-events-none fixed bottom-0 left-0 right-0 z-40 h-[140px] select-none"
        >
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div>
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

/* Hide scrollbar for Chrome, Safari and Opera */
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.hide-scrollbar {
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none; /* Firefox */
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
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-50%);
    }
}
.animate-marquee {
    animation: marquee 30s linear infinite;
}
.animate-marquee:hover {
    animation-play-state: paused;
}

/* Scroll animations disabled for stability; content renders immediately */
.hide-animate,
.hide-animate.slide-up,
.hide-animate.slide-left,
.hide-animate.slide-right,
.hide-animate.scale-in,
.show-animate {
    opacity: 1 !important;
    transform: none !important;
    transition: none !important;
    will-change: auto;
}

/* Progressive Blur Layers with Bottom-to-Top Masking */
.progressive-blur div {
    position: absolute;
    inset: 0;
}
.progressive-blur div:nth-child(1) {
    backdrop-filter: blur(1px);
    mask-image: linear-gradient(
        to top,
        rgba(0, 0, 0, 1) 0%,
        rgba(0, 0, 0, 0) 20%
    );
    -webkit-mask-image: linear-gradient(
        to top,
        rgba(0, 0, 0, 1) 0%,
        rgba(0, 0, 0, 0) 20%
    );
}
.progressive-blur div:nth-child(2) {
    backdrop-filter: blur(2px);
    mask-image: linear-gradient(
        to top,
        rgba(0, 0, 0, 1) 0%,
        rgba(0, 0, 0, 0) 40%
    );
    -webkit-mask-image: linear-gradient(
        to top,
        rgba(0, 0, 0, 1) 0%,
        rgba(0, 0, 0, 0) 40%
    );
}
.progressive-blur div:nth-child(3) {
    backdrop-filter: blur(4px);
    mask-image: linear-gradient(
        to top,
        rgba(0, 0, 0, 1) 0%,
        rgba(0, 0, 0, 0) 60%
    );
    -webkit-mask-image: linear-gradient(
        to top,
        rgba(0, 0, 0, 1) 0%,
        rgba(0, 0, 0, 0) 60%
    );
}
.progressive-blur div:nth-child(4) {
    backdrop-filter: blur(8px);
    mask-image: linear-gradient(
        to top,
        rgba(0, 0, 0, 1) 0%,
        rgba(0, 0, 0, 0) 80%
    );
    -webkit-mask-image: linear-gradient(
        to top,
        rgba(0, 0, 0, 1) 0%,
        rgba(0, 0, 0, 0) 80%
    );
}
.progressive-blur div:nth-child(5) {
    backdrop-filter: blur(16px);
    mask-image: linear-gradient(
        to top,
        rgba(0, 0, 0, 1) 0%,
        rgba(0, 0, 0, 0) 100%
    );
    -webkit-mask-image: linear-gradient(
        to top,
        rgba(0, 0, 0, 1) 0%,
        rgba(0, 0, 0, 0) 100%
    );
}

/* Override and disable scroll animations on mobile viewports */
@media (max-width: 639px) {
    .hide-animate {
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
    }
}

/* Data Flow Animation (Electric Flow Vertical) */
@keyframes data-flow-up {
    0% {
        transform: translateZ(-30px) scale(0);
        opacity: 0;
    }
    20% {
        opacity: 1;
        transform: translateZ(20px) scale(1);
    }
    80% {
        opacity: 1;
        transform: translateZ(140px) scale(1.5);
    }
    100% {
        transform: translateZ(180px) scale(0);
        opacity: 0;
    }
}
.animate-data-flow-up {
    animation: data-flow-up 2.5s cubic-bezier(0.3, 0, 0.2, 1) infinite;
}

/* Horizontal Data Beams */
@keyframes beam-flow {
    0% {
        transform: translateX(-100%);
        opacity: 0;
    }
    30% {
        opacity: 1;
    }
    70% {
        opacity: 1;
    }
    100% {
        transform: translateX(100%);
        opacity: 0;
    }
}
.animate-beam-flow {
    animation: beam-flow 1.5s linear infinite;
}

@keyframes beam-flow-reverse {
    0% {
        transform: translateX(100%);
        opacity: 0;
    }
    30% {
        opacity: 1;
    }
    70% {
        opacity: 1;
    }
    100% {
        transform: translateX(-100%);
        opacity: 0;
    }
}
.animate-beam-flow-reverse {
    animation: beam-flow-reverse 1.5s linear infinite;
}

@keyframes beam-flow-y {
    0% {
        transform: translateY(100%);
        opacity: 0;
    }
    30% {
        opacity: 1;
    }
    70% {
        opacity: 1;
    }
    100% {
        transform: translateY(-100%);
        opacity: 0;
    }
}
.animate-beam-flow-y {
    animation: beam-flow-y 1.5s linear infinite;
}
</style>
