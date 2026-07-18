<script setup>
import { Deferred, Link, usePage } from "@inertiajs/vue3";
import { ArrowRight, ChevronDown, Layers, Menu, X, Download } from "lucide-vue-next";
import { ref, computed } from "vue";
import { usePwaInstall } from "@/composables/usePwaInstall";

const page = usePage();
const siteSettings = computed(() => page.props.siteSettings || {});
import { dashboard, login, register } from "@/routes";

import { useAppearance } from "@/composables/useAppearance";
import { ThemeTogglerButton } from "@/components/animate-ui/components/buttons/theme-toggler";

const { appearance, resolvedAppearance, updateAppearance } = useAppearance();
const { isInstallable, installPwa } = usePwaInstall();

const activeTheme = computed({
	get: () => appearance.value === "system" ? resolvedAppearance.value : appearance.value,
	set: (val) => {
		updateAppearance(val);
	}
});

const isMobileMenuOpen = ref(false);
const openMobile = ref(null);
const activeMenu = ref(null); // controls which flyout is open
let closeTimer = null;

const getUnsplashSrcset = (url) => {
	if (!url || !url.includes("images.unsplash.com")) return undefined;
	const baseUrl = url.split("?")[0];
	return `${baseUrl}?auto=format&fit=crop&w=220&q=80 220w,
			${baseUrl}?auto=format&fit=crop&w=440&q=80 440w`;
};

const toggleMobileMenu = () => {
	isMobileMenuOpen.value = !isMobileMenuOpen.value;
};
const toggleMobileAccordion = (id) => {
	openMobile.value = openMobile.value === id ? null : id;
};

const openFlyout = (id) => {
	clearTimeout(closeTimer);
	activeMenu.value = id;
};
const closeFlyout = () => {
	closeTimer = setTimeout(() => {
		activeMenu.value = null;
	}, 120);
};

const getMenuHref = (menu) => {
	if (menu.url) return menu.url;
	if (menu.title === 'Agenda Event' || menu.title === 'Event') return '/event';
	if (menu.page) return `/halaman/${menu.page.slug}`;
	return "#";
};

// Icon + color + background + description per slug
const iconMap = {
	"tentang-fmikom": {
		d: "M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
		tc: "text-blue-500",
		bg: "bg-blue-50/70",
		desc: "Profil & informasi umum",
	},
	sejarah: {
		d: "M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z",
		tc: "text-indigo-500",
		bg: "bg-indigo-50/70",
		desc: "Perjalanan & latar belakang",
	},
	"visi-misi": {
		d: "M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z",
		tc: "text-sky-500",
		bg: "bg-sky-50/70",
		desc: "Tujuan & arah strategis",
	},
	"program-studi": {
		d: "M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253",
		tc: "text-blue-600",
		bg: "bg-blue-50/70",
		desc: "Daftar jurusan & kurikulum",
	},
	"struktur-organisasi": {
		d: "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z",
		tc: "text-violet-500",
		bg: "bg-violet-50/70",
		desc: "Hierarki & divisi fakultas",
	},
	"dekan-kaprodi": {
		d: "M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z",
		tc: "text-purple-500",
		bg: "bg-purple-50/70",
		desc: "Pimpinan & kepala program",
	},
	"dosen-staff": {
		d: "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z",
		tc: "text-pink-500",
		bg: "bg-pink-50/70",
		desc: "Tenaga pengajar & kependidikan",
	},
	akreditasi: {
		d: "M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z",
		tc: "text-amber-500",
		bg: "bg-amber-50/70",
		desc: "Status & sertifikasi mutu",
	},
	fasilitas: {
		d: "M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4",
		tc: "text-green-500",
		bg: "bg-green-50/70",
		desc: "Sarana & prasarana kampus",
	},
	"kalender-akademik": {
		d: "M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z",
		tc: "text-emerald-500",
		bg: "bg-emerald-50/70",
		desc: "Jadwal & agenda akademik",
	},
	informatika: {
		d: "m18 16 4-4-4-4M6 8l-4 4 4 4M14.5 4l-5 16",
		tc: "text-blue-600 dark:text-blue-400",
		bg: "bg-blue-50/80 dark:bg-blue-950/30",
		desc: "S1 Teknik Informatika",
	},
	"sistem-informasi": {
		d: "M12 8c4.97 0 9-1.343 9-3S16.97 2 12 2 3 3.343 3 5s4.03 3 9 3M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5M3 12c0 1.66 4 3 9 3s9-1.34 9-3",
		tc: "text-indigo-600 dark:text-indigo-400",
		bg: "bg-indigo-50/80 dark:bg-indigo-950/30",
		desc: "S1 Sistem Informasi",
	},
	matematika: {
		d: "M9 4v16M17 4v16M3 4h18M14 20a3 3 0 0 1-3-3",
		tc: "text-emerald-600 dark:text-emerald-400",
		bg: "bg-emerald-50/80 dark:bg-emerald-950/30",
		desc: "S1 Matematika",
	},
	mbkm: {
		d: "M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
		tc: "text-teal-500",
		bg: "bg-teal-50/70",
		desc: "Merdeka belajar kampus merdeka",
	},
	magang: {
		d: "M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z",
		tc: "text-cyan-500",
		bg: "bg-cyan-50/70",
		desc: "Praktik kerja industri",
	},
	"pedoman-akademik": {
		d: "M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z",
		tc: "text-lime-600",
		bg: "bg-lime-50/70",
		desc: "Panduan & aturan akademik",
	},
	beasiswa: {
		d: "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
		tc: "text-yellow-500",
		bg: "bg-yellow-50/70",
		desc: "Program bantuan & pendanaan",
	},
	"download-dokumen": {
		d: "M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4",
		tc: "text-emerald-600",
		bg: "bg-emerald-50/70",
		desc: "Unduh form & dokumen resmi",
	},
	berita: {
		d: "M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 12h8",
		tc: "text-violet-500",
		bg: "bg-violet-50/70",
		desc: "Berita terbaru FMIKOM",
	},
	pengumuman: {
		d: "M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z",
		tc: "text-purple-500",
		bg: "bg-purple-50/70",
		desc: "Info & pengumuman resmi",
	},
	"agenda-event": {
		d: "M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z",
		tc: "text-fuchsia-500",
		bg: "bg-fuchsia-50/70",
		desc: "Kegiatan & event kampus",
	},
	galeri: {
		d: "M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z",
		tc: "text-pink-500",
		bg: "bg-pink-50/70",
		desc: "Foto & dokumentasi kegiatan",
	},
	video: {
		d: "M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.893L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z",
		tc: "text-red-500",
		bg: "bg-red-50/70",
		desc: "Video & konten multimedia",
	},
	"pengajuan-dokumen": {
		d: "M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z",
		tc: "text-orange-500",
		bg: "bg-orange-50/70",
		desc: "Ajukan surat & dokumen",
	},
	"konsultasi-akademik": {
		d: "M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z",
		tc: "text-amber-500",
		bg: "bg-amber-50/70",
		desc: "Tanya jawab dengan dosen",
	},
	faq: {
		d: "M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
		tc: "text-rose-500",
		bg: "bg-rose-50/70",
		desc: "Pertanyaan yang sering diajukan",
	},
	default: {
		d: "M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
		tc: "text-slate-400",
		bg: "bg-slate-50/70",
		desc: "",
	},
};
const getIcon = (child) => iconMap[child.page?.slug] || iconMap.default;

// Header accent per menu title
const accentColor = {
	Profil: "text-blue-600",
	Akademik: "text-emerald-600",
	"Berita & Media": "text-violet-600",
	Layanan: "text-orange-500",
};
const getAccent = (menu) => accentColor[menu.title] || "text-blue-600";
</script>

<template>
    <!-- Maintenance Bypass Banner for Admins -->
    <div v-if="siteSettings.maintenance_mode === '1'" class="bg-amber-600 text-white text-[11px] font-bold text-center py-2 px-4 flex items-center justify-center gap-1.5 z-[100] relative">
        <span>⚠️</span>
        <span>Mode Maintenance Aktif. Publik terblokir, Anda melihat halaman ini sebagai Administrator.</span>
    </div>

    <nav class="sticky top-0 z-50 w-full border-b border-gray-100/60 bg-white/80 backdrop-blur-xl transition-all duration-300 shadow-sm shadow-slate-100/50" style="padding-top: env(safe-area-inset-top);">
        <div class="mx-auto max-w-7xl px-4 xl:px-0">
            <div class="flex h-[68px] items-center justify-between">
                <!-- Logo -->
                <div class="flex shrink-0 items-center">
                    <Link href="/" class="group flex items-center gap-2.5">
                        <div v-if="siteSettings.brand_logo" class="h-8 w-8 flex items-center justify-center overflow-hidden bg-transparent">
                            <img :src="siteSettings.brand_logo" class="h-full w-full object-contain" alt="" width="32" height="32" loading="eager" decoding="async" />
                        </div>
                        <div v-else class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#2563eb] text-white shadow-md shadow-blue-200 transition-transform group-hover:scale-105">
                            <Layers class="h-4.5 w-4.5" />
                        </div>
                        <div class="flex flex-col">
                            <span class="text-md font-bold tracking-tight text-[#111827] leading-tight">
                                {{ siteSettings.brand_name || 'Portal FMIKOM' }}
                            </span>
                            <span class="text-[10px] font-medium text-slate-500 tracking-tight leading-none mt-0.5">
                                {{ siteSettings.brand_subtitle || 'Fakultas Matematika dan Ilmu Komputer' }}
                            </span>
                        </div>
                    </Link>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center gap-1">
                    <Deferred data="portal_menus">
                        <template #fallback>
                            <div class="flex items-center gap-5 px-3 py-2 animate-pulse">
                                <div class="h-4 w-14 bg-slate-200/70 rounded-md"></div>
                                <div class="h-4 w-18 bg-slate-200/70 rounded-md"></div>
                                <div class="h-4 w-14 bg-slate-200/70 rounded-md"></div>
                            </div>
                        </template>
                        <template #default>
                            <template v-for="menu in $page.props.portal_menus" :key="menu.id">
                        <!-- Mega Flyout Dropdown -->
                        <div v-if="menu.children && menu.children.length > 0"
                            class="relative"
                            @mouseenter="openFlyout(menu.id)"
                            @mouseleave="closeFlyout">
                            <button :class="['flex items-center gap-1 px-3 py-2 text-sm font-semibold rounded-lg transition-all', activeMenu === menu.id ? 'text-[#2563eb] bg-slate-50' : 'text-slate-600 hover:text-[#2563eb] hover:bg-slate-50']">
                                {{ menu.title }}
                                <ChevronDown :class="['w-3.5 h-3.5 transition-transform duration-200 opacity-60', activeMenu === menu.id ? 'rotate-180' : '']" />
                            </button>

                            <!-- Mega Panel — fixed, centered in viewport -->
                            <div
                                @mouseenter="openFlyout(menu.id)"
                                @mouseleave="closeFlyout"
                                :class="['fixed left-1/2 -translate-x-1/2 top-[68px] transition-all duration-200 ease-out z-200', activeMenu === menu.id ? 'opacity-100 visible pointer-events-auto translate-y-0' : 'opacity-0 invisible pointer-events-none -translate-y-1']">

                                <!-- ═══ SPECIAL: Berita & Media — with 1 featured article ═══ -->
                                <div v-if="menu.title === 'Berita & Media'"
                                    class="bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex w-[920px]">

                                    <!-- Left: Submenu links -->
                                    <div class="w-56 shrink-0 border-r border-slate-100 flex flex-col">
                                        <div class="px-5 pt-5 pb-3 border-b border-slate-100">
                                            <p class="text-xs font-black uppercase tracking-widest text-violet-600">Berita & Media</p>
                                        </div>
                                        <div class="py-2 flex flex-col flex-1">
                                            <a v-for="child in menu.children" :key="child.id"
                                                :href="getMenuHref(child)"
                                                class="group/item flex items-center gap-3 px-4 py-2.5 hover:bg-violet-50/60 transition-colors">
                                                <!-- Icon: soft borderless background -->
                                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" :class="getIcon(child).bg">
                                                    <svg :class="['w-4.5 h-4.5', getIcon(child).tc]" fill="none" stroke="currentColor" stroke-width="1.85" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" :d="getIcon(child).d" />
                                                    </svg>
                                                </div>
                                                <div class="flex flex-col min-w-0">
                                                    <span class="text-sm font-semibold text-slate-800 group-hover/item:text-violet-700 transition-colors leading-tight">{{ child.title }}</span>
                                                    <span v-if="getIcon(child).desc" class="text-[11px] text-slate-400 leading-tight truncate">{{ getIcon(child).desc }}</span>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="px-4 py-4 border-t border-slate-100">
                                            <a href="/berita" class="group/footer flex items-center gap-1.5 text-sm font-bold text-violet-600 hover:text-violet-800 transition-colors">
                                                View all <ArrowRight class="w-3.5 h-3.5 transition-transform group-hover/footer:translate-x-1" />
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Right: Featured article — like Laravel.com (no card, image fills height) -->
                                    <div class="flex-1 flex flex-col">
                                        <div class="px-6 pt-5 pb-3 border-b border-slate-100">
                                            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Featured article</p>
                                        </div>
                                        <Deferred data="featured_posts">
                                            <template #fallback>
                                                <div class="flex-1 flex items-center justify-center p-8 animate-pulse">
                                                    <div class="w-full flex gap-4">
                                                        <div class="w-[220px] h-28 bg-slate-200/70 rounded-lg"></div>
                                                        <div class="flex-1 space-y-2 py-1">
                                                            <div class="h-4 bg-slate-200/70 rounded w-1/4"></div>
                                                            <div class="h-4 bg-slate-200/70 rounded"></div>
                                                            <div class="h-4 bg-slate-200/70 rounded w-5/6"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                            <template #default>
                                                <template v-if="$page.props.featured_posts?.length">
                                                    <a v-for="post in $page.props.featured_posts" :key="post.slug"
                                                        :href="`/berita/${post.slug}`"
                                                        class="group/post flex flex-1 hover:bg-slate-50 transition-colors">
                                                <!-- Image — fills full height, fixed width, no card/border -->
<div class="relative w-[220px] shrink-0 overflow-hidden bg-slate-100">
    <div class="absolute inset-0 bg-linear-to-t from-black/10 to-transparent opacity-0 group-hover/post:opacity-100 transition-opacity duration-300"></div>
                                                    <img v-if="post.thumbnail" :src="post.thumbnail" :srcset="getUnsplashSrcset(post.thumbnail)" sizes="220px" :alt="post.title"
                                                         class="w-full h-full object-cover group-hover/post:scale-[1.03] transition-transform duration-700 ease-out" width="220" height="140" loading="lazy" decoding="async" />
                                                    <div v-else class="w-full h-full flex items-center justify-center bg-linear-to-br from-violet-50 to-indigo-100">
                                                        <svg class="w-10 h-10 text-violet-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                </div>
                                                <!-- Text: date, title, excerpt, read more -->
                                                <div class="flex-1 flex flex-col justify-between px-10 py-8">
                                                    <div>
                                                        <p class="text-xs font-semibold text-slate-400 mb-2">{{ post.published_at }}</p>
                                                        <p class="text-[20px] font-bold tracking-tight text-slate-900 group-hover/post:text-violet-700 leading-snug line-clamp-3 transition-colors mb-2">{{ post.title }}</p>
                                                        <p v-if="post.excerpt" class="text-sm text-slate-500 line-clamp-3 leading-relaxed">{{ post.excerpt }}</p>
                                                    </div>
                                                    <div class="flex items-center gap-1 text-sm font-bold text-violet-600 group-hover/post:text-violet-800 transition-colors">
                                                        Read more <ArrowRight class="w-3.5 h-3.5 transition-transform group-hover/post:translate-x-1" />
                                                    </div>
                                                </div>
                                            </a>
                                        </template>
                                                <div v-else class="flex-1 flex items-center justify-center text-slate-400 text-sm">
                                                    Belum ada artikel
                                                </div>
                                            </template>
                                        </Deferred>
                                    </div>
                                </div>

                                <!-- ═══ STANDARD: other menus ═══ -->
                                <div v-else class="bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden"
                                     :style="{ minWidth: menu.children.length > 6 ? '920px' : (menu.children.length > 3 ? '620px' : '340px') }">

                                    <!-- Panel header -->
                                    <div class="px-6 pt-5 pb-4 border-b border-slate-100">
                                        <p :class="['text-xs font-black uppercase tracking-widest', getAccent(menu)]">{{ menu.title }}</p>
                                    </div>

                                    <!-- Items grid —  inner p-4 so hover doesn't touch panel edge -->
                                    <div class="p-4 flex gap-x-3">
                                        <template v-if="menu.children.length > 3">
                                            <div v-for="colIndex in Math.ceil(menu.children.length / 3)" :key="colIndex" 
                                                 class="flex flex-col flex-1"
                                                 :class="{'border-r border-slate-100 pr-3': colIndex < Math.ceil(menu.children.length / 3), 'pl-3': colIndex > 1}">
                                                <a v-for="child in menu.children.slice((colIndex - 1) * 3, colIndex * 3)" :key="child.id"
                                                    :href="getMenuHref(child)"
                                                    class="group/item flex items-start gap-4 px-3 py-3 rounded-xl hover:bg-slate-50 transition-colors">
                                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 mt-0.5" :class="getIcon(child).bg">
                                                        <svg :class="['w-5 h-5', getIcon(child).tc]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" :d="getIcon(child).d" />
                                                        </svg>
                                                    </div>
                                                    <div class="flex flex-col min-w-0">
                                                        <span class="text-[14.5px] font-semibold text-slate-900 group-hover/item:text-blue-600 leading-snug transition-colors">{{ child.title }}</span>
                                                        <span v-if="getIcon(child).desc" class="text-[12.5px] text-slate-500 leading-snug mt-0.5">{{ getIcon(child).desc }}</span>
                                                    </div>
                                                </a>
                                            </div>
                                        </template>

                                        <!-- Single column (≤3 items) -->
                                        <template v-else>
                                            <div class="flex flex-col w-full">
                                                <a v-for="child in menu.children" :key="child.id"
                                                    :href="getMenuHref(child)"
                                                    class="group/item flex items-start gap-4 px-3 py-3 rounded-xl hover:bg-slate-50 transition-colors">
                                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 mt-0.5" :class="getIcon(child).bg">
                                                        <svg :class="['w-5 h-5', getIcon(child).tc]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" :d="getIcon(child).d" />
                                                        </svg>
                                                    </div>
                                                    <div class="flex flex-col min-w-0">
                                                        <span class="text-[14.5px] font-semibold text-slate-900 group-hover/item:text-blue-600 leading-snug transition-colors">{{ child.title }}</span>
                                                        <span v-if="getIcon(child).desc" class="text-[12.5px] text-slate-500 leading-snug mt-0.5">{{ getIcon(child).desc }}</span>
                                                    </div>
                                                </a>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Single link -->
                        <a v-else :href="getMenuHref(menu)" class="px-3 py-2 text-sm font-semibold text-slate-600 hover:text-[#2563eb] rounded-lg hover:bg-slate-50 transition-all">
                            {{ menu.title }}
                        </a>
                    </template>
                </template>
            </Deferred>
        </div>

                <!-- Right CTA -->
                <div class="hidden md:flex items-center gap-2">
                    <!-- PWA Install Button (Desktop) -->
                    <button 
                        v-if="isInstallable" 
                        @click="installPwa"
                        class="inline-flex h-9 items-center justify-center gap-1.5 rounded-xl border border-blue-200 bg-blue-50/50 hover:bg-blue-50 dark:bg-blue-950/20 dark:border-blue-900/30 px-3.5 text-xs font-bold text-blue-600 dark:text-blue-400 shadow-xs transition-all hover:scale-[1.02] cursor-pointer"
                    >
                        <Download class="w-3.5 h-3.5" />
                        Install App
                    </button>

                    <template v-if="$page.props.auth.user">
                        <Link :href="dashboard()"
                            class="inline-flex h-9 items-center justify-center rounded-xl border border-gray-200 bg-white/80 px-4 text-sm font-semibold text-[#111827] shadow-sm transition-all hover:bg-gray-50 hover:text-[#2563eb]">
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="login()"
                            class="inline-flex h-9 items-center justify-center rounded-xl px-4 text-sm font-semibold text-[#111827] transition-all hover:bg-slate-100">
                            Log in
                        </Link>
                        <Link :href="register()"
                            class="inline-flex h-9 items-center justify-center rounded-xl bg-[#2563eb] px-4 text-sm font-semibold text-white shadow-md shadow-blue-200 transition-all hover:bg-blue-700 active:scale-95">
                            Register
                        </Link>
                    </template>
                </div>

                <!-- Mobile hamburger -->
                <button @click="toggleMobileMenu" class="flex items-center justify-center md:hidden rounded-xl p-2 text-slate-500 hover:bg-slate-100 transition-colors" aria-label="Toggle mobile menu">
                    <Menu v-if="!isMobileMenuOpen" class="h-5 w-5" />
                    <X v-else class="h-5 w-5" />
                </button>
            </div>
        </div>

        <!-- Mobile Drawer -->
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-3"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-3"
        >
            <div v-show="isMobileMenuOpen" class="md:hidden border-t border-slate-100 bg-white/95 backdrop-blur-xl">
                <div class="px-4 pt-2 pb-5 space-y-1">
                    <a href="/#home" class="flex items-center px-3 py-2.5 text-sm font-semibold text-slate-700 rounded-xl hover:bg-slate-50 transition-colors" @click="isMobileMenuOpen = false">Home</a>

                    <Deferred data="portal_menus">
                        <template #fallback>
                            <div class="px-3 py-4 space-y-4 animate-pulse">
                                <div class="h-4 bg-slate-200/70 rounded w-1/3"></div>
                                <div class="h-4 bg-slate-200/70 rounded w-1/2"></div>
                                <div class="h-4 bg-slate-200/70 rounded w-1/4"></div>
                            </div>
                        </template>
                        <template #default>
                            <template v-for="menu in $page.props.portal_menus" :key="menu.id">
                        <!-- Accordion with children -->
                        <div v-if="menu.children && menu.children.length > 0">
                            <button
                                @click="toggleMobileAccordion(menu.id)"
                                class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-semibold text-slate-700 rounded-xl hover:bg-slate-50 transition-colors">
                                {{ menu.title }}
                                <ChevronDown :class="['w-4 h-4 text-slate-400 transition-transform duration-200', openMobile === menu.id ? 'rotate-180' : '']" />
                            </button>
                            <transition
                                enter-active-class="transition-all duration-200 ease-out overflow-hidden"
                                enter-from-class="max-h-0 opacity-0"
                                enter-to-class="max-h-[500px] opacity-100"
                                leave-active-class="transition-all duration-150 ease-in overflow-hidden"
                                leave-from-class="max-h-[500px] opacity-100"
                                leave-to-class="max-h-0 opacity-0"
                            >
                                <div v-show="openMobile === menu.id" class="mt-1 ml-3 pl-3 border-l-2 border-blue-100 flex flex-col gap-0.5">
                                    <a v-for="child in menu.children" :key="child.id"
                                        :href="getMenuHref(child)"
                                        class="flex items-center gap-2.5 px-3 py-2 text-sm font-medium text-slate-600 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors"
                                        @click="isMobileMenuOpen = false">
                                        <div class="w-6 h-6 rounded-md flex items-center justify-center shrink-0" :class="getIcon(child).bg">
                                            <svg class="w-3.5 h-3.5" :class="getIcon(child).tc" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" :d="getIcon(child).d" />
                                            </svg>
                                        </div>
                                        {{ child.title }}
                                    </a>
                                </div>
                            </transition>
                        </div>

                        <!-- Single link -->
                        <a v-else :href="getMenuHref(menu)"
                            class="flex items-center px-3 py-2.5 text-sm font-semibold text-slate-700 rounded-xl hover:bg-slate-50 transition-colors"
                            @click="isMobileMenuOpen = false">
                            {{ menu.title }}
                        </a>
                    </template>
                </template>
            </Deferred>

                    <!-- PWA Premium Install Banner (Mobile) -->
                    <div v-if="isInstallable" class="px-3 pt-4 pb-2 border-t border-slate-100">
                        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#064e3b] via-[#022c22] to-[#0a0f1d] border border-emerald-500/20 p-5 shadow-lg flex flex-col gap-4">
                            <!-- Glow effects -->
                            <div class="absolute -right-10 -top-10 w-32 h-32 rounded-full bg-emerald-500/10 blur-2xl pointer-events-none"></div>
                            <div class="absolute -left-10 -bottom-10 w-24 h-24 rounded-full bg-teal-500/5 blur-xl pointer-events-none"></div>

                            <div class="flex items-start gap-3">
                                <!-- Circular Icon -->
                                <div class="flex-shrink-0 w-8 h-8 rounded-full border border-emerald-500/30 flex items-center justify-center text-emerald-400 bg-emerald-500/10">
                                    <Download class="w-4 h-4" />
                                </div>
                                
                                <!-- Texts -->
                                <div class="space-y-1">
                                    <h4 class="text-sm font-bold text-white tracking-tight leading-tight">
                                        Download Aplikasi Portal
                                    </h4>
                                    <p class="text-[11.5px] text-emerald-300/80 leading-relaxed font-normal">
                                        Akses cepat, stabil, & fullscreen ke seluruh fitur FMIKOM.
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Action button -->
                            <button 
                                @click="installPwa"
                                class="w-full py-2.5 px-4 bg-white hover:bg-emerald-50 text-emerald-950 font-bold text-xs rounded-xl shadow-xs transition-all hover:scale-[1.01] active:scale-95 cursor-pointer text-center"
                            >
                                Unduh Sekarang
                            </button>
                        </div>
                    </div>

                    <!-- Auth actions -->
                    <div class="pt-3 mt-3 border-t border-slate-100 flex flex-col gap-2">
                        <template v-if="$page.props.auth.user">
                            <Link :href="dashboard()"
                                class="flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-[#111827]"
                                @click="isMobileMenuOpen = false">Dashboard</Link>
                        </template>
                        <template v-else>
                            <Link :href="login()"
                                class="flex w-full items-center justify-center rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-[#111827]"
                                @click="isMobileMenuOpen = false">Log in</Link>
                            <Link :href="register()"
                                class="flex w-full items-center justify-center rounded-xl bg-[#2563eb] px-4 py-2.5 text-sm font-semibold text-white shadow-md"
                                @click="isMobileMenuOpen = false">Register</Link>
                        </template>
                    </div>
                </div>
            </div>
        </transition>
    </nav>
</template>
