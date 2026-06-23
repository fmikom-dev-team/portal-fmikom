<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import TraceAlumniLayout from "@/layouts/TraceAlumniLayout.vue";
import type { PageProps } from "@inertiajs/core";
import type { BreadcrumbItem } from "@/types";
import type { TraceUser } from "@/types/trace";
import {
    UserIcon,
    BriefcaseIcon,
    ClipboardListIcon,
    CheckCircle2,
    AlertCircle,
    ArrowRight,
    GraduationCap,
    Clock,
    CalendarCheck,
    FileText,
    Circle,
    LayoutDashboard,
    MapPin,
    ChevronLeft,
    ChevronRight,
    Briefcase,
    CalendarDays,
} from "lucide-vue-next";
import { computed, ref, onMounted, nextTick } from "vue";
import { TPageHeader, TStatCard, TSkeleton } from '@/components/trace';

interface CompletenessItem {
    label: string;
    done: boolean;
}

interface ProfileCompleteness {
    items: CompletenessItem[];
    percentage: number;
}

interface Stats {
    hasProfile: boolean;
    currentStatus: string;
    totalCareers: number;
}

interface UpcomingEvent {
    id: number;
    title: string;
    event_date: string;
    event_time_start: string | null;
    location: string | null;
    poster_path: string | null;
    is_registered: boolean;
}

interface RecentApplication {
    id: number;
    job_title: string;
    location: string | null;
    tipe_kerja: string | null;
    status: string;
    applied_at: string;
}

const props = withDefaults(defineProps<{
    moduleName: string;
    roleName: string;
    stats: Stats;
    hasFilledKuesioner: boolean;
    profileCompleteness: ProfileCompleteness;
    appliedJobsCount: number;
    recentApplications?: RecentApplication[];
    upcomingEventsCount: number;
    upcomingEvents?: UpcomingEvent[];
    pendingKuesionersCount: number;
    angkatan?: number | null;
    programStudi?: string | null;
}>(), {
    recentApplications: () => [],
    upcomingEvents: () => [],
    angkatan: null,
    programStudi: null,
});

const page = usePage<PageProps & { auth: { user: TraceUser } }>();
const user = computed(() => page.props.auth?.user);

const breadcrumbItems: BreadcrumbItem[] = [
    { title: "Dashboard", href: "/trace" },
];

const statusLabel: Record<string, string> = {
    bekerja: "Bekerja",
    wirausaha: "Wirausaha",
    lanjut_studi: "Lanjut Studi",
    mencari_kerja: "Mencari Kerja",
};

const statusIcon: Record<string, string> = {
    bekerja: "💼",
    wirausaha: "🚀",
    lanjut_studi: "🎓",
    mencari_kerja: "🔍",
};

const statusColor: Record<string, string> = {
    bekerja: "text-emerald-700 bg-emerald-50 dark:bg-emerald-950/30 dark:text-emerald-400",
    wirausaha: "text-violet-700 bg-violet-50 dark:bg-violet-950/30 dark:text-violet-400",
    lanjut_studi: "text-sky-700 bg-sky-50 dark:bg-sky-950/30 dark:text-sky-400",
    mencari_kerja: "text-amber-700 bg-amber-50 dark:bg-amber-950/30 dark:text-amber-400",
};

const appStatusColor: Record<string, string> = {
    pending: "text-amber-600 bg-amber-50 dark:bg-amber-950/30 dark:text-amber-400",
    accepted: "text-emerald-600 bg-emerald-50 dark:bg-emerald-950/30 dark:text-emerald-400",
    rejected: "text-rose-600 bg-rose-50 dark:bg-rose-950/30 dark:text-rose-400",
    reviewed: "text-sky-600 bg-sky-50 dark:bg-sky-950/30 dark:text-sky-400",
};

const appStatusLabel: Record<string, string> = {
    pending: "Menunggu",
    accepted: "Diterima",
    rejected: "Ditolak",
    reviewed: "Direview",
};

const completenessGradient = computed(() => {
    if (props.profileCompleteness.percentage >= 80) return "from-[#0C447C] to-[#85B7EB]";
    if (props.profileCompleteness.percentage >= 50) return "from-amber-500 to-orange-500";
    return "from-red-400 to-rose-500";
});

// Event carousel
const isReady = ref(false);
onMounted(() => {
    nextTick(() => { isReady.value = true; });
});

const carouselRef = ref<HTMLElement | null>(null);
const scrollCarousel = (dir: 'left' | 'right') => {
    if (!carouselRef.value) return;
    const amount = 280;
    carouselRef.value.scrollBy({ left: dir === 'left' ? -amount : amount, behavior: 'smooth' });
};

const formatEventDate = (dateStr: string) => {
    try {
        return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
    } catch { return dateStr; }
};

const formatEventDay = (dateStr: string) => {
    try {
        return new Date(dateStr).toLocaleDateString('id-ID', { weekday: 'long' });
    } catch { return ''; }
};

const formatTimeAgo = (dateStr: string) => {
    try {
        const d = new Date(dateStr);
        const now = new Date();
        const diff = Math.floor((now.getTime() - d.getTime()) / 86400000);
        if (diff === 0) return 'Hari ini';
        if (diff === 1) return 'Kemarin';
        return `${diff} hari lalu`;
    } catch { return ''; }
};
</script>

<template>
    <TraceAlumniLayout
        title="Dashboard"
        :breadcrumbs="breadcrumbItems"
        :role-name="roleName"
        :module-name="moduleName"
    >
        <div class="mx-auto space-y-6">
            <!-- Page Header -->
            <TPageHeader
                title="Dashboard Alumni"
                description="Pantau progres karir, kelengkapan profil, dan informasi terbaru dari kampus."
                :icon="LayoutDashboard"
            />

            <!-- ============ WELCOME BANNER (with integrated completeness) ============ -->
            <div class="rounded-2xl bg-gradient-to-br from-[#0C447C] via-[#0C447C]/90 to-[#1a5a9e] p-5 sm:p-6 text-white shadow-lg shadow-[#0C447C]/15 relative overflow-hidden">
                <!-- Decorative -->
                <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-white/5"></div>
                <div class="absolute right-12 bottom-0 h-20 w-20 rounded-full bg-white/[0.03]"></div>
                <div class="absolute left-1/2 -bottom-10 h-24 w-24 rounded-full bg-white/[0.02]"></div>

                <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] font-bold text-[#85B7EB]/60 uppercase tracking-widest">Selamat datang kembali</p>
                        <h2 class="text-xl sm:text-2xl font-black mt-0.5">
                            Halo, {{ user?.name?.split(" ")[0] }}
                        </h2>
                        <div class="flex flex-wrap items-center gap-2 mt-1">
                            <span v-if="programStudi" class="inline-flex items-center gap-1 text-xs font-medium text-[#85B7EB]/80">
                                <GraduationCap class="h-3 w-3" /> {{ programStudi }}
                            </span>
                            <span v-if="angkatan" class="text-xs text-[#85B7EB]/50">• Angkatan {{ angkatan }}</span>
                        </div>
                        <!-- Inline CTA -->
                        <div class="mt-3 flex flex-wrap gap-2">
                            <Link
                                href="/trace/career"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 hover:bg-white/20 px-3.5 py-1.5 text-[11px] font-bold transition-all backdrop-blur-sm border border-white/10"
                            >
                                <Briefcase class="h-3 w-3" /> Karir
                            </Link>
                            <Link
                                href="/trace/profile-alumni"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 hover:bg-white/20 px-3.5 py-1.5 text-[11px] font-bold transition-all backdrop-blur-sm border border-white/10"
                            >
                                <UserIcon class="h-3 w-3" /> Profil
                            </Link>
                            <Link
                                href="/trace/kuesioner"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 hover:bg-white/20 px-3.5 py-1.5 text-[11px] font-bold transition-all backdrop-blur-sm border border-white/10"
                            >
                                <FileText class="h-3 w-3" /> Kuesioner
                            </Link>
                        </div>
                    </div>

                    <!-- Progress ring + status -->
                    <div class="flex items-center gap-3 shrink-0">
                        <div class="relative h-[72px] w-[72px]">
                            <svg class="h-[72px] w-[72px] -rotate-90" viewBox="0 0 72 72">
                                <circle cx="36" cy="36" r="30" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="5" />
                                <circle
                                    cx="36" cy="36" r="30" fill="none"
                                    :stroke="profileCompleteness.percentage >= 100 ? '#34d399' : '#85B7EB'"
                                    stroke-width="5" stroke-linecap="round"
                                    :stroke-dasharray="`${(profileCompleteness.percentage / 100) * 188.5} 188.5`"
                                    class="transition-all duration-1000 ease-out"
                                />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-base font-black leading-none">{{ profileCompleteness.percentage }}%</span>
                                <span class="text-[7px] font-bold text-[#85B7EB]/60 uppercase mt-0.5">Profil</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile completeness checklist (only when < 100%) -->
                <div v-if="profileCompleteness.percentage < 100" class="relative mt-4 pt-3 border-t border-white/10">
                    <div class="flex flex-wrap gap-1.5">
                        <div
                            v-for="item in profileCompleteness.items"
                            :key="item.label"
                            class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold transition-colors"
                            :class="item.done ? 'bg-emerald-500/20 text-emerald-200' : 'bg-white/8 text-white/50'"
                        >
                            <CheckCircle2 v-if="item.done" class="h-3 w-3 text-emerald-300" />
                            <Circle v-else class="h-3 w-3 text-white/30" />
                            {{ item.label }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ STATS ROW ============ -->
            <!-- Stats Skeleton -->
            <div v-if="!isReady" class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                <TSkeleton variant="stat-card" :count="4" />
            </div>
            <div v-else class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                <TStatCard
                    label="Lamaran Kerja"
                    :value="appliedJobsCount"
                    :icon="BriefcaseIcon"
                    color="primary"
                    trend-label="lowongan dilamar"
                />
                <TStatCard
                    label="Status Karir"
                    :value="statusLabel[stats.currentStatus] ?? 'N/A'"
                    :icon="Briefcase"
                    :color="stats.currentStatus === 'bekerja' ? 'emerald' : stats.currentStatus === 'wirausaha' ? 'violet' : 'accent'"
                />
                <TStatCard
                    label="Riwayat Karir"
                    :value="stats.totalCareers"
                    :icon="GraduationCap"
                    color="violet"
                    trend-label="riwayat karir tercatat"
                />
                <TStatCard
                    label="Kuesioner"
                    :value="pendingKuesionersCount > 0 ? `${pendingKuesionersCount} pending` : '✓ Selesai'"
                    :icon="FileText"
                    :color="pendingKuesionersCount > 0 ? 'accent' : 'emerald'"
                    :trend-label="pendingKuesionersCount > 0 ? 'menunggu diisi' : 'semua telah diisi'"
                />
            </div>

            <!-- ============ KUESIONER ALERT (only if pending) ============ -->
            <div
                v-if="pendingKuesionersCount > 0"
                class="flex items-center gap-3 rounded-xl border border-amber-200/60 bg-amber-50/50 p-3.5 dark:border-amber-800/30 dark:bg-amber-950/20"
            >
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/40">
                    <ClipboardListIcon class="h-3.5 w-3.5 text-amber-600 dark:text-amber-400" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-amber-800 dark:text-amber-300">
                        Ada {{ pendingKuesionersCount }} kuesioner yang menunggu respons Anda
                    </p>
                    <p class="text-[10px] text-amber-600/60 dark:text-amber-400/50">
                        Respons Anda membantu meningkatkan kualitas pendidikan
                    </p>
                </div>
                <Link
                    href="/trace/kuesioner"
                    class="shrink-0 inline-flex items-center gap-1 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-[10px] font-bold px-3 py-1.5 transition-colors"
                >
                    Isi <ArrowRight class="h-3 w-3" />
                </Link>
            </div>

            <!-- ============ RECENT APPLICATIONS (full width) ============ -->
            <!-- Recent Applications Skeleton -->
            <div v-if="!isReady" class="rounded-2xl border border-slate-100 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900 shadow-sm space-y-4">
                <div class="flex items-center gap-3">
                    <TSkeleton variant="circle" width="36px" height="36px" />
                    <div class="space-y-2">
                        <TSkeleton variant="text" width="120px" />
                        <TSkeleton variant="text" width="80px" height="10px" />
                    </div>
                </div>
                <div class="grid gap-2 sm:grid-cols-3">
                    <div v-for="i in 3" :key="i" class="rounded-xl border border-slate-50 dark:border-zinc-800/60 p-3 bg-slate-50/30 dark:bg-zinc-800/20 space-y-2">
                        <TSkeleton variant="text" width="80%" />
                        <TSkeleton variant="text" width="60%" height="10px" />
                    </div>
                </div>
            </div>
            <div v-else class="rounded-2xl border border-slate-100 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-950/40 dark:text-violet-400">
                            <BriefcaseIcon class="h-4 w-4" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Lamaran Terbaru</h3>
                            <p class="text-[10px] text-slate-400">{{ appliedJobsCount }} lamaran terkirim</p>
                        </div>
                    </div>
                    <Link
                        href="/trace/jobs"
                        class="text-[10px] font-bold text-violet-600 dark:text-violet-400 hover:underline flex items-center gap-0.5"
                    >
                        Semua <ArrowRight class="h-3 w-3" />
                    </Link>
                </div>

                <div v-if="recentApplications.length > 0" class="grid gap-2 sm:grid-cols-3">
                    <div
                        v-for="app in recentApplications"
                        :key="app.id"
                        class="flex items-center gap-3 rounded-xl border border-slate-50 dark:border-zinc-800/60 p-3 bg-slate-50/30 dark:bg-zinc-800/20"
                    >
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-800 dark:text-white truncate">{{ app.job_title }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span v-if="app.location" class="text-[10px] text-slate-400 flex items-center gap-0.5">
                                    <MapPin class="h-2.5 w-2.5" /> {{ app.location }}
                                </span>
                                <span v-if="app.tipe_kerja" class="text-[10px] text-slate-400">• {{ app.tipe_kerja }}</span>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-0.5 shrink-0">
                            <span
                                class="text-[9px] font-bold px-1.5 py-0.5 rounded-md"
                                :class="appStatusColor[app.status] ?? appStatusColor.pending"
                            >
                                {{ appStatusLabel[app.status] ?? app.status }}
                            </span>
                            <span class="text-[9px] text-slate-400">{{ formatTimeAgo(app.applied_at) }}</span>
                        </div>
                    </div>
                </div>
                <div v-else class="flex flex-col items-center justify-center py-8 text-slate-400">
                    <BriefcaseIcon class="h-8 w-8 text-slate-200 dark:text-zinc-700 mb-2" />
                    <p class="text-xs font-medium">Anda belum melamar lowongan</p>
                    <Link href="/trace/jobs" class="text-[10px] font-bold text-violet-600 dark:text-violet-400 hover:underline mt-1">
                        Jelajahi Lowongan →
                    </Link>
                </div>
            </div>

            <!-- ============ EVENT CAROUSEL ============ -->
            <!-- Event Carousel Skeleton -->
            <div v-if="!isReady && upcomingEvents.length > 0" class="rounded-2xl border border-slate-100 bg-white dark:border-zinc-800 dark:bg-zinc-900 shadow-sm p-5 space-y-4">
                <div class="flex items-center gap-3">
                    <TSkeleton variant="circle" width="36px" height="36px" />
                    <div class="space-y-2">
                        <TSkeleton variant="text" width="140px" />
                        <TSkeleton variant="text" width="100px" height="10px" />
                    </div>
                </div>
                <div class="flex gap-3 overflow-hidden">
                    <div v-for="i in 3" :key="i" class="flex-shrink-0 w-[260px] rounded-2xl border border-slate-100 dark:border-zinc-800 overflow-hidden">
                        <TSkeleton variant="card" height="112px" class="!rounded-none" />
                        <div class="p-3.5 space-y-2">
                            <TSkeleton variant="text" width="80%" />
                            <TSkeleton variant="text" width="60%" height="10px" />
                            <TSkeleton variant="text" width="50%" height="10px" />
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="isReady && upcomingEvents.length > 0" class="rounded-2xl border border-slate-100 bg-white dark:border-zinc-800 dark:bg-zinc-900 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-5 pt-5 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-50 text-sky-600 dark:bg-sky-950/40 dark:text-sky-400">
                            <CalendarDays class="h-4 w-4" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Event Mendatang</h3>
                            <p class="text-[10px] text-slate-400">{{ upcomingEvents.length }} event yang akan datang</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button
                            @click="scrollCarousel('left')"
                            class="h-7 w-7 flex items-center justify-center rounded-lg border border-slate-200 dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors"
                        >
                            <ChevronLeft class="h-3.5 w-3.5 text-slate-500 dark:text-zinc-400" />
                        </button>
                        <button
                            @click="scrollCarousel('right')"
                            class="h-7 w-7 flex items-center justify-center rounded-lg border border-slate-200 dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors"
                        >
                            <ChevronRight class="h-3.5 w-3.5 text-slate-500 dark:text-zinc-400" />
                        </button>
                    </div>
                </div>

                <!-- Carousel Track -->
                <div
                    ref="carouselRef"
                    class="flex gap-3 overflow-x-auto px-5 pb-5 snap-x snap-mandatory scrollbar-none"
                    style="-webkit-overflow-scrolling: touch;"
                >
                    <Link
                        v-for="event in upcomingEvents"
                        :key="event.id"
                        :href="`/trace/events/${event.id}`"
                        class="group relative flex-shrink-0 w-[260px] snap-start rounded-2xl border border-slate-100 dark:border-zinc-800 overflow-hidden transition-all hover:shadow-md hover:border-sky-200 dark:hover:border-sky-800"
                    >
                        <!-- Poster / Gradient BG -->
                        <div class="h-28 w-full bg-gradient-to-br from-sky-500 to-indigo-600 relative overflow-hidden">
                            <img
                                v-if="event.poster_path"
                                :src="`/storage/${event.poster_path}`"
                                :alt="event.title"
                                class="h-full w-full object-cover"
                            />
                            <!-- Registered badge -->
                            <span
                                v-if="event.is_registered"
                                class="absolute top-2 right-2 text-[9px] font-bold bg-emerald-500 text-white px-2 py-0.5 rounded-full"
                            >
                                ✓ Terdaftar
                            </span>
                        </div>
                        <!-- Info -->
                        <div class="p-3.5">
                            <p class="text-xs font-bold text-slate-800 dark:text-white truncate group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">
                                {{ event.title }}
                            </p>
                            <div class="flex items-center gap-3 mt-1.5">
                                <span class="text-[10px] text-slate-400 dark:text-zinc-500 flex items-center gap-0.5">
                                    <CalendarCheck class="h-3 w-3" />
                                    {{ formatEventDate(event.event_date) }}, {{ formatEventDay(event.event_date) }}
                                </span>
                            </div>
                            <div class="flex items-center gap-3 mt-1">
                                <span v-if="event.event_time_start" class="text-[10px] text-slate-400 dark:text-zinc-500 flex items-center gap-0.5">
                                    <Clock class="h-3 w-3" />
                                    {{ event.event_time_start?.substring(0, 5) }} WIB
                                </span>
                                <span v-if="event.location" class="text-[10px] text-slate-400 dark:text-zinc-500 flex items-center gap-0.5 truncate">
                                    <MapPin class="h-3 w-3 shrink-0" />
                                    {{ event.location }}
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- ============ QUICK ACTIONS (Conditional) ============ -->
            <div v-if="profileCompleteness.percentage < 100 || pendingKuesionersCount > 0">
                <h3 class="text-[10px] font-black text-slate-400 dark:text-zinc-500 uppercase tracking-widest mb-3">
                    Langkah Selanjutnya
                </h3>
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-if="profileCompleteness.percentage < 100"
                        href="/trace/profile-alumni"
                        class="group flex items-center gap-3 rounded-xl border border-slate-100 bg-white p-3.5 hover:border-[#85B7EB]/40 hover:shadow-sm transition-all dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-[#85B7EB]/30"
                    >
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#0C447C]/5 text-[#0C447C] dark:text-[#85B7EB] dark:bg-[#85B7EB]/10">
                            <UserIcon class="h-4 w-4" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-900 dark:text-white">Lengkapi Profil</p>
                            <p class="text-[10px] text-slate-400 truncate">Sisa {{ 100 - profileCompleteness.percentage }}% lagi</p>
                        </div>
                        <ArrowRight class="h-3.5 w-3.5 text-slate-300 dark:text-zinc-600 group-hover:text-[#0C447C] dark:group-hover:text-[#85B7EB] transition-colors shrink-0" />
                    </Link>
                    <Link
                        v-if="!stats.hasProfile || stats.totalCareers === 0"
                        href="/trace/career"
                        class="group flex items-center gap-3 rounded-xl border border-slate-100 bg-white p-3.5 hover:border-[#85B7EB]/40 hover:shadow-sm transition-all dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-[#85B7EB]/30"
                    >
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#0C447C]/5 text-[#0C447C] dark:text-[#85B7EB] dark:bg-[#85B7EB]/10">
                            <BriefcaseIcon class="h-4 w-4" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-900 dark:text-white">Tambah Riwayat Karir</p>
                            <p class="text-[10px] text-slate-400 truncate">Mulai catat perjalanan karir Anda</p>
                        </div>
                        <ArrowRight class="h-3.5 w-3.5 text-slate-300 dark:text-zinc-600 group-hover:text-[#0C447C] dark:group-hover:text-[#85B7EB] transition-colors shrink-0" />
                    </Link>
                    <Link
                        v-if="pendingKuesionersCount > 0"
                        href="/trace/kuesioner"
                        class="group flex items-center gap-3 rounded-xl border border-slate-100 bg-white p-3.5 hover:border-amber-200 hover:shadow-sm transition-all dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-amber-800"
                    >
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:text-amber-400 dark:bg-amber-950/30">
                            <ClipboardListIcon class="h-4 w-4" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-900 dark:text-white">Isi Kuesioner</p>
                            <p class="text-[10px] text-slate-400 truncate">{{ pendingKuesionersCount }} kuesioner tersedia</p>
                        </div>
                        <ArrowRight class="h-3.5 w-3.5 text-slate-300 dark:text-zinc-600 group-hover:text-amber-500 transition-colors shrink-0" />
                    </Link>
                </div>
            </div>
        </div>
    </TraceAlumniLayout>
</template>

<style scoped>
.scrollbar-none {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-none::-webkit-scrollbar {
    display: none;
}
</style>
