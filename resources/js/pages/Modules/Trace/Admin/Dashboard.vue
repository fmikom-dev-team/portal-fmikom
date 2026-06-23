<script setup lang="ts">
import { Deferred, Link } from '@inertiajs/vue3';
import {
    Chart as ChartJS,
    ArcElement,
    Tooltip as ChartTooltip,
    Legend as ChartLegend,
} from 'chart.js';
import { Doughnut } from 'vue-chartjs';
import { 
    Users, 
    Briefcase, 
    ClipboardCheck, 
    Clock, 
    BookOpen,
    ChevronRight,
    ArrowRight,
    MapPin,
    Building2,
    Database,
    LayoutDashboard,
    TrendingUp,
    GraduationCap,
    CalendarDays,
    Plus,
    AlertCircle,
    ExternalLink,
    MapPinned,
} from 'lucide-vue-next';
import { TPageHeader, TStatCard } from '@/components/Trace';
import { Skeleton } from '@/components/ui/skeleton';
import TraceAdminLayout from '@/layouts/TraceAdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import type { AngkatanItem } from '@/types/trace';
import AlumniGrowthChart from './components/AlumniGrowthChart.vue';
import { computed } from 'vue';

ChartJS.register(ArcElement, ChartTooltip, ChartLegend);

interface CareerItem {
    label: string;
    value: number;
    color: string;
}

interface RankItem {
    name: string;
    count: number;
}

interface CompletenessItem {
    label: string;
    value: number;
    total: number;
    rate: number;
}

interface Props {
    stats?: {
        totalAlumni: Record<string, unknown>;
        employmentRate: Record<string, unknown>;
        studiLanjut: Record<string, unknown>;
        kuesionerStats: {
            total_kuesioners: number;
            total_responses: number;
            response_rate: number;
        };
        careerBreakdown: CareerItem[];
        topSektors: RankItem[];
        topCities: RankItem[];
        dataCompleteness: CompletenessItem[];
        recentActivities: Array<{
            id: number;
            action: string;
            description: string;
            user_name: string;
            ip_address: string | null;
            created_at: string;
        }>;
        prodiDistribution: {
            labels: string[];
            counts: number[];
        };
        total_alumni_raw: number;
        activeJobs: number;
        activeEvents: number;
        pendingJobs: number;
        upcomingEvents: Array<{
            id: number;
            title: string;
            event_date: string;
            event_time_start: string | null;
            location: string | null;
            max_participants: number | null;
            registrations_count: number;
        }>;
    } | null;
    alumniGrowthData?: AngkatanItem[];
}
 
const props = defineProps<Props>();
 
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/trace/admin' },
];

// --- Donut Chart: Status Karir ---
const careerChartData = computed(() => {
    if (!props.stats) return null;
    const items = props.stats.careerBreakdown.filter(i => i.value > 0);
    return {
        labels: items.map(i => i.label),
        datasets: [{
            data: items.map(i => i.value),
            backgroundColor: items.map(i => i.color),
            borderColor: '#ffffff',
            borderWidth: 3,
            hoverBorderWidth: 0,
            hoverOffset: 6,
        }]
    };
});

const careerTotal = computed(() => 
    props.stats?.careerBreakdown.reduce((a, b) => a + b.value, 0) ?? 0
);

// --- Donut Chart: Sebaran Prodi ---
const prodiColors = ['#6366f1', '#8b5cf6', '#a78bfa', '#c4b5fd', '#818cf8', '#4f46e5'];

const prodiChartData = computed(() => {
    if (!props.stats) return null;
    return {
        labels: props.stats.prodiDistribution.labels,
        datasets: [{
            data: props.stats.prodiDistribution.counts,
            backgroundColor: prodiColors.slice(0, props.stats.prodiDistribution.labels.length),
            borderColor: '#ffffff',
            borderWidth: 3,
            hoverBorderWidth: 0,
            hoverOffset: 6,
        }]
    };
});

const donutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '65%',
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: 'rgba(15, 23, 42, 0.9)',
            padding: 10,
            titleFont: { size: 12, weight: 'bold' as const },
            bodyFont: { size: 12 },
            cornerRadius: 10,
            displayColors: true,
            boxPadding: 4,
            callbacks: {
                label: (ctx: Record<string, unknown>) => {
                    const total = ctx.dataset.data.reduce((a: number, b: number) => a + b, 0);
                    const pct = total > 0 ? Math.round((ctx.parsed / total) * 100) : 0;
                    return ` ${ctx.label}: ${ctx.parsed} (${pct}%)`;
                }
            }
        },
    },
};
 
const formatTimeAgo = (dateStr: string) => {
    try {
        const date = new Date(dateStr);
        const now = new Date();
        const diffMs = now.getTime() - date.getTime();
        const diffMins = Math.floor(diffMs / 60000);
        if (diffMins < 60) return `${diffMins} menit lalu`;
        const diffHrs = Math.floor(diffMins / 60);
        if (diffHrs < 24) return `${diffHrs} jam lalu`;
        const diffDays = Math.floor(diffHrs / 24);
        return `${diffDays} hari lalu`;
    } catch (e) {
        return dateStr;
    }
};

const maxRank = (items: RankItem[]) => Math.max(...items.map(i => i.count), 1);

const getRateColor = (rate: number) => {
    if (rate >= 70) return 'bg-emerald-500';
    if (rate >= 40) return 'bg-amber-400';
    return 'bg-red-400';
};

const getRateTextColor = (rate: number) => {
    if (rate >= 70) return 'text-emerald-600';
    if (rate >= 40) return 'text-amber-600';
    return 'text-red-500';
};

// Format raw action string to human-readable label
const formatAction = (action: string): string => {
    const map: Record<string, string> = {
        'job.created': 'Lowongan Baru',
        'job.updated': 'Lowongan Diperbarui',
        'job.deleted': 'Lowongan Dihapus',
        'job.approved': 'Lowongan Disetujui',
        'job.rejected': 'Lowongan Ditolak',
        'event.created': 'Event Baru',
        'event.updated': 'Event Diperbarui',
        'event.deleted': 'Event Dihapus',
        'kuesioner.created': 'Kuesioner Baru',
        'kuesioner.updated': 'Kuesioner Diperbarui',
        'kuesioner.deleted': 'Kuesioner Dihapus',
        'career.created': 'Karir Ditambah',
        'career.updated': 'Karir Diperbarui',
        'career.deleted': 'Karir Dihapus',
        'profile.updated': 'Profil Diperbarui',
        'auth.login': 'Login',
        'auth.logout': 'Logout',
        'applicant.status_updated': 'Status Pelamar',
    };
    return map[action] ?? action.replace(/[._]/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
};

const getActionColor = (action: string): string => {
    if (action.startsWith('auth')) return 'bg-slate-100 text-slate-500 dark:bg-zinc-800 dark:text-zinc-400';
    if (action.startsWith('job')) return 'bg-violet-50 text-violet-600 dark:bg-violet-950/30 dark:text-violet-400';
    if (action.startsWith('event')) return 'bg-sky-50 text-sky-600 dark:bg-sky-950/30 dark:text-sky-400';
    if (action.startsWith('kuesioner')) return 'bg-amber-50 text-amber-600 dark:bg-amber-950/30 dark:text-amber-400';
    if (action.startsWith('applicant')) return 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/30 dark:text-emerald-400';
    if (action.startsWith('career')) return 'bg-rose-50 text-rose-600 dark:bg-rose-950/30 dark:text-rose-400';
    if (action.startsWith('profile')) return 'bg-cyan-50 text-cyan-600 dark:bg-cyan-950/30 dark:text-cyan-400';
    return 'bg-slate-100 text-slate-500 dark:bg-zinc-800 dark:text-zinc-400';
};

const formatEventDate = (dateStr: string) => {
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    } catch { return dateStr; }
};

const formatEventDay = (dateStr: string) => {
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', { weekday: 'short' });
    } catch { return ''; }
};

const formatEventDayNum = (dateStr: string) => {
    try { return new Date(dateStr).getDate().toString(); } catch { return ''; }
};

const formatEventMonth = (dateStr: string) => {
    try {
        return new Date(dateStr).toLocaleDateString('id-ID', { month: 'short' }).toUpperCase();
    } catch { return ''; }
};
</script>
 
<template>
    <TraceAdminLayout title="Dashboard" :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 max-w-7xl mx-auto w-full pb-12">
            
            <!-- Welcome Header -->
            <TPageHeader
                title="Dashboard Tracer Study"
                description="Pantau sebaran karir, respons kuesioner, dan aktivitas alumni dalam satu halaman."
                :icon="LayoutDashboard"
            />
 
            <Deferred data="stats">
                <template #fallback>
                    <div class="space-y-6">
                        <!-- KPI Card Skeletons -->
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div v-for="i in 4" :key="i" class="rounded-2xl border border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5">
                                <div class="flex items-start gap-4">
                                    <Skeleton class="h-11 w-11 shrink-0 rounded-xl" />
                                    <div class="flex-1 space-y-2.5">
                                        <Skeleton class="h-3 w-20 rounded" />
                                        <Skeleton class="h-7 w-28 rounded" />
                                        <Skeleton class="h-3 w-32 rounded" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Chart Skeletons (Row 2) -->
                        <div class="grid gap-4 md:grid-cols-2">
                            <div v-for="i in 2" :key="i" class="rounded-3xl border border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 space-y-4">
                                <Skeleton class="h-5 w-40 rounded-lg" />
                                <Skeleton class="h-48 w-full rounded-2xl" />
                            </div>
                        </div>
                        <!-- Row 3 Skeletons -->
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <div class="md:col-span-2 lg:col-span-2 rounded-3xl border border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 space-y-4">
                                <Skeleton class="h-5 w-40 rounded-lg" />
                                <Skeleton class="h-72 w-full rounded-2xl" />
                            </div>
                            <div class="space-y-4">
                                <div class="rounded-3xl border border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 space-y-3">
                                    <Skeleton class="h-5 w-32 rounded-lg" />
                                    <Skeleton v-for="i in 5" :key="i" class="h-8 w-full rounded-lg" />
                                </div>
                                <div class="rounded-3xl border border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 space-y-3">
                                    <Skeleton class="h-5 w-32 rounded-lg" />
                                    <Skeleton v-for="i in 4" :key="i" class="h-6 w-full rounded-lg" />
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

            <!-- ============ KPI CARDS ============ -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <template v-if="props.stats">
                    <TStatCard 
                        v-bind="props.stats.totalAlumni" 
                        :icon="Users"
                        :sub-text="props.stats.totalAlumni.subLabel"
                    />
                    <TStatCard 
                        v-bind="props.stats.employmentRate" 
                        :icon="TrendingUp"
                        color="emerald"
                        :sub-text="props.stats.employmentRate.subLabel"
                    />
                    <TStatCard 
                        v-bind="props.stats.studiLanjut" 
                        :icon="GraduationCap"
                        color="violet"
                        :sub-text="props.stats.studiLanjut.subLabel"
                    />
                    
                    <TStatCard
                        label="Kuesioner Tracer"
                        :value="`${props.stats.kuesionerStats.response_rate}%`"
                        :icon="ClipboardCheck"
                        trend="Tingkat Respons"
                        :trend-up="true"
                        :trend-label="`${props.stats.kuesionerStats.total_kuesioners} Kuesioner · ${props.stats.kuesionerStats.total_responses} Respon`"
                        color="violet"
                    />
                </template>
            </div>

            <!-- ============ QUICK ACTIONS + LIVE STATUS ============ -->
            <div v-if="props.stats" class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Quick Action: Lowongan Aktif -->
                <Link href="/trace/admin/jobs" class="group flex items-center gap-4 rounded-2xl border border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4 shadow-xs transition-all hover:shadow-md hover:border-violet-200 dark:hover:border-violet-800">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-950/40 dark:text-violet-400 transition-transform group-hover:scale-110">
                        <Briefcase class="h-5 w-5" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-2xl font-black text-slate-900 dark:text-white leading-none">{{ props.stats.activeJobs }}</p>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500 mt-0.5">Lowongan Aktif</p>
                    </div>
                    <ChevronRight class="h-4 w-4 text-slate-300 dark:text-zinc-600 group-hover:text-violet-500 transition-colors" />
                </Link>

                <!-- Quick Action: Event Aktif -->
                <Link href="/trace/admin/events" class="group flex items-center gap-4 rounded-2xl border border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4 shadow-xs transition-all hover:shadow-md hover:border-sky-200 dark:hover:border-sky-800">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600 dark:bg-sky-950/40 dark:text-sky-400 transition-transform group-hover:scale-110">
                        <CalendarDays class="h-5 w-5" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-2xl font-black text-slate-900 dark:text-white leading-none">{{ props.stats.activeEvents }}</p>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500 mt-0.5">Event Aktif</p>
                    </div>
                    <ChevronRight class="h-4 w-4 text-slate-300 dark:text-zinc-600 group-hover:text-sky-500 transition-colors" />
                </Link>

                <!-- Quick Action: Review Lowongan -->
                <Link href="/trace/admin/jobs?status=pending" class="group flex items-center gap-4 rounded-2xl border border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4 shadow-xs transition-all hover:shadow-md hover:border-amber-200 dark:hover:border-amber-800">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 transition-transform group-hover:scale-110">
                        <AlertCircle class="h-5 w-5" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-2xl font-black text-slate-900 dark:text-white leading-none">{{ props.stats.pendingJobs }}</p>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500 mt-0.5">Menunggu Persetujuan</p>
                    </div>
                    <ChevronRight class="h-4 w-4 text-slate-300 dark:text-zinc-600 group-hover:text-amber-500 transition-colors" />
                </Link>

                <!-- Quick Action: Peta Alumni -->
                <Link href="/trace/admin/map" class="group flex items-center gap-4 rounded-2xl border border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4 shadow-xs transition-all hover:shadow-md hover:border-emerald-200 dark:hover:border-emerald-800">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 transition-transform group-hover:scale-110">
                        <MapPinned class="h-5 w-5" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-black text-slate-900 dark:text-white leading-none">WebGIS</p>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500 mt-0.5">Sebaran Alumni</p>
                    </div>
                    <ChevronRight class="h-4 w-4 text-slate-300 dark:text-zinc-600 group-hover:text-emerald-500 transition-colors" />
                </Link>
            </div>

            <!-- ============ ROW 2: Donut Status Karir + Top Sektor ============ -->
            <div class="grid gap-4 md:grid-cols-2" v-if="props.stats">
                <!-- Status Karir Donut -->
                <div class="rounded-3xl border border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-xs">
                    <div class="flex items-center gap-2 mb-5">
                        <Briefcase class="h-4 w-4 text-blue-500" />
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-400">Status Karir Alumni</h3>
                    </div>
                    <div class="flex flex-col items-center gap-6 sm:flex-row sm:items-center">
                        <!-- Donut -->
                        <div class="relative h-40 w-40 flex-shrink-0 sm:h-48 sm:w-48">
                            <Doughnut v-if="careerChartData && careerTotal > 0" :data="careerChartData" :options="donutOptions" />
                            <div v-if="careerTotal > 0" class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                <span class="text-2xl font-black text-slate-800 dark:text-white">{{ careerTotal }}</span>
                                <span class="text-[9px] font-bold text-slate-400 uppercase">Total</span>
                            </div>
                            <div v-else class="h-full flex items-center justify-center text-slate-300 dark:text-zinc-700">
                                <Users class="h-12 w-12" />
                            </div>
                        </div>
                        <!-- Legend -->
                        <div class="flex-1 space-y-3 w-full">
                            <div v-for="item in props.stats.careerBreakdown" :key="item.label" class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="h-3 w-3 rounded-full flex-shrink-0" :style="`background:${item.color}`"></div>
                                    <span class="text-xs font-bold text-slate-600 dark:text-zinc-400">{{ item.label }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-black text-slate-800 dark:text-white">{{ item.value }}</span>
                                    <span class="text-[10px] font-bold text-slate-400 w-10 text-right">
                                        {{ careerTotal > 0 ? Math.round((item.value / careerTotal) * 100) : 0 }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top 5 Sektor Industri -->
                <div class="rounded-3xl border border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-xs">
                    <div class="flex items-center gap-2 mb-5">
                        <Building2 class="h-4 w-4 text-indigo-500" />
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-400">Sektor Industri Teratas</h3>
                    </div>
                    <div v-if="props.stats.topSektors.length > 0" class="space-y-3">
                        <div v-for="(item, i) in props.stats.topSektors" :key="item.name" class="flex items-center gap-3">
                            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-indigo-500 text-[9px] font-black text-white flex-shrink-0">
                                {{ i + 1 }}
                            </span>
                            <div class="flex-1 relative h-9">
                                <div class="absolute inset-0 rounded-xl bg-slate-50 dark:bg-zinc-800/50 overflow-hidden">
                                    <div
                                        class="h-full rounded-xl bg-indigo-500/15 dark:bg-indigo-500/20 transition-all duration-500"
                                        :style="`width: ${Math.max(10, (item.count / maxRank(props.stats.topSektors)) * 100)}%`"
                                    ></div>
                                </div>
                                <div class="absolute inset-0 flex items-center justify-between px-3">
                                    <span class="text-xs font-bold text-slate-700 dark:text-zinc-300 truncate pr-2">{{ item.name }}</span>
                                    <span class="text-xs font-black text-indigo-600 dark:text-indigo-400 flex-shrink-0">{{ item.count }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex flex-col items-center justify-center py-12 text-slate-400">
                        <Building2 class="h-8 w-8 text-slate-300 dark:text-zinc-700 mb-2" />
                        <p class="text-xs font-medium">Data sektor industri belum tersedia</p>
                    </div>
                </div>
            </div>

            <!-- ============ ROW 3: Chart + Top Kota + Keterisian ============ -->
            <div class="space-y-4" v-if="props.stats">
                <!-- Alumni Growth Chart — full width -->
                <div class="min-w-0 overflow-hidden">
                    <template v-if="props.alumniGrowthData">
                        <AlumniGrowthChart :data="props.alumniGrowthData" />
                    </template>
                    <template v-else>
                        <div class="h-[420px] w-full animate-pulse rounded-3xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center text-slate-400 font-medium">
                            Memuat data grafik...
                        </div>
                    </template>
                </div>

                <!-- Top Kota + Keterisian — side by side -->
                <div class="grid gap-4 md:grid-cols-2">
                    <!-- Top 5 Kota -->
                    <div class="rounded-3xl border border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-xs">
                        <div class="flex items-center gap-2 mb-4">
                            <MapPin class="h-4 w-4 text-emerald-500" />
                            <h3 class="text-sm font-black uppercase tracking-widest text-slate-400">Kota Tujuan Teratas</h3>
                        </div>
                        <div v-if="props.stats.topCities.length > 0" class="space-y-2.5">
                            <div v-for="(city, i) in props.stats.topCities" :key="city.name" class="flex items-center gap-2.5">
                                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-emerald-500 text-[9px] font-black text-white flex-shrink-0">
                                    {{ i + 1 }}
                                </span>
                                <div class="flex-1 relative h-8">
                                    <div class="absolute inset-0 rounded-lg bg-slate-50 dark:bg-zinc-800/50 overflow-hidden">
                                        <div
                                            class="h-full rounded-lg bg-emerald-500/15 dark:bg-emerald-500/20 transition-all duration-500"
                                            :style="`width: ${Math.max(10, (city.count / maxRank(props.stats.topCities)) * 100)}%`"
                                        ></div>
                                    </div>
                                    <div class="absolute inset-0 flex items-center justify-between px-3">
                                        <span class="text-xs font-bold text-slate-700 dark:text-zinc-300 truncate pr-2">{{ city.name }}</span>
                                        <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 flex-shrink-0">{{ city.count }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-6 text-xs text-slate-400">Data lokasi belum tersedia</div>
                    </div>

                    <!-- Keterisian Data -->
                    <div class="rounded-3xl border border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-xs">
                        <div class="flex items-center gap-2 mb-4">
                            <Database class="h-4 w-4 text-amber-500" />
                            <h3 class="text-sm font-black uppercase tracking-widest text-slate-400">Kelengkapan Data</h3>
                        </div>
                        <div class="space-y-3">
                            <div v-for="item in props.stats.dataCompleteness" :key="item.label">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[11px] font-bold text-slate-600 dark:text-zinc-400">{{ item.label }}</span>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[10px] font-medium text-slate-400">{{ item.value }}/{{ item.total }}</span>
                                        <span class="text-[10px] font-black" :class="getRateTextColor(item.rate)">{{ item.rate }}%</span>
                                    </div>
                                </div>
                                <div class="h-1.5 w-full rounded-full bg-slate-100 dark:bg-zinc-800 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-700" :class="getRateColor(item.rate)" :style="`width: ${item.rate}%`"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ ROW 4: Sebaran Prodi Donut + Aktivitas Terbaru ============ -->
            <div class="grid gap-4 md:grid-cols-2" v-if="props.stats">
                <!-- Sebaran Prodi Donut -->
                <div class="rounded-3xl border border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-xs">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-2">
                            <BookOpen class="h-4 w-4 text-purple-500" />
                            <h3 class="text-sm font-black uppercase tracking-widest text-slate-400">Sebaran Program Studi</h3>
                        </div>
                        <Link href="/trace/admin/alumni" class="text-[10px] font-black text-blue-600 hover:underline inline-flex items-center gap-0.5">
                            Selengkapnya <ArrowRight class="h-3 w-3" />
                        </Link>
                    </div>
                    <div v-if="props.stats.prodiDistribution.labels.length" class="flex flex-col items-center gap-6 sm:flex-row sm:items-center sm:gap-8">
                        <!-- Donut -->
                        <div class="relative h-40 w-40 flex-shrink-0 sm:h-52 sm:w-52">
                            <Doughnut v-if="prodiChartData" :data="prodiChartData" :options="donutOptions" />
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                <span class="text-2xl font-black text-slate-800 dark:text-white">{{ props.stats.total_alumni_raw }}</span>
                                <span class="text-[9px] font-bold text-slate-400 uppercase">Alumni</span>
                            </div>
                        </div>
                        <!-- Legend + Bars -->
                        <div class="flex-1 space-y-3 w-full">
                            <div v-for="(label, i) in props.stats.prodiDistribution.labels" :key="label">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <div class="h-2.5 w-2.5 rounded-full flex-shrink-0" :style="`background:${prodiColors[i]}`"></div>
                                        <span class="text-xs font-bold text-slate-700 dark:text-zinc-300">{{ label }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-black text-slate-800 dark:text-white">{{ props.stats.prodiDistribution.counts[i] }}</span>
                                        <span class="text-[10px] font-bold text-slate-400 w-10 text-right">
                                            {{ props.stats.total_alumni_raw > 0 ? Math.round((props.stats.prodiDistribution.counts[i] / props.stats.total_alumni_raw) * 100) : 0 }}%
                                        </span>
                                    </div>
                                </div>
                                <div class="h-2 w-full rounded-full bg-slate-100 dark:bg-zinc-800 overflow-hidden">
                                    <div 
                                        class="h-full rounded-full transition-all duration-700"
                                        :style="`width: ${Math.max(5, (props.stats.prodiDistribution.counts[i] / (props.stats.total_alumni_raw || 1)) * 100)}%; background:${prodiColors[i]}`"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-10 text-slate-400 italic text-xs">
                        Data program studi belum tersedia.
                    </div>
                </div>

                <!-- Aktivitas Terbaru -->
                <div class="rounded-3xl border border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-xs">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <Clock class="h-4 w-4 text-violet-500" />
                            <h3 class="text-sm font-black uppercase tracking-widest text-slate-400">Aktivitas Terbaru</h3>
                        </div>
                        <Link href="/trace/admin/activity-log" class="text-[10px] font-bold text-violet-600 hover:underline inline-flex items-center gap-0.5">
                            Lihat Semua <ChevronRight class="h-3 w-3" />
                        </Link>
                    </div>
                    <div v-if="props.stats.recentActivities.length" class="space-y-2 max-h-[360px] overflow-y-auto pr-1 scrollbar-thin">
                        <div
                            v-for="act in props.stats.recentActivities"
                            :key="act.id"
                            class="p-3 rounded-xl border border-slate-50 dark:border-zinc-800/60 bg-slate-50/30 dark:bg-zinc-800/20"
                        >
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="text-xs font-black text-slate-800 dark:text-white truncate flex-1 mr-2">{{ act.user_name }}</h4>
                                <span class="text-[9px] font-bold text-slate-400 whitespace-nowrap">{{ formatTimeAgo(act.created_at) }}</span>
                            </div>
                            <p class="text-[10px] text-slate-600 dark:text-zinc-400 line-clamp-2">{{ act.description }}</p>
                            <div class="mt-2 pt-2 border-t border-slate-50 dark:border-zinc-800/40">
                                <span
                                    class="text-[9px] font-bold px-1.5 py-0.5 rounded-md"
                                    :class="getActionColor(act.action)"
                                >{{ formatAction(act.action) }}</span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-10 border border-dashed border-slate-100 dark:border-zinc-800 rounded-2xl">
                        <Users class="h-8 w-8 text-slate-300 dark:text-zinc-700 mx-auto mb-2" />
                        <p class="text-[10px] text-slate-400">Belum ada aktivitas tercatat</p>
                    </div>
                </div>
            </div>

            <!-- ============ ROW 5: Upcoming Events ============ -->
            <div v-if="props.stats && props.stats.upcomingEvents && props.stats.upcomingEvents.length > 0" class="rounded-3xl border border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-xs">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-2">
                        <CalendarDays class="h-4 w-4 text-sky-500" />
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-400">Event Mendatang</h3>
                    </div>
                    <Link href="/trace/admin/events" class="text-[10px] font-bold text-sky-600 hover:underline inline-flex items-center gap-0.5">
                        Semua Event <ChevronRight class="h-3 w-3" />
                    </Link>
                </div>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                    <Link
                        v-for="event in props.stats.upcomingEvents"
                        :key="event.id"
                        :href="`/trace/admin/events/${event.id}`"
                        class="group flex items-center gap-3 rounded-2xl border border-slate-100 dark:border-zinc-800 p-3 transition-all hover:shadow-md hover:border-sky-200 dark:hover:border-sky-800"
                    >
                        <!-- Date Card -->
                        <div class="flex h-14 w-14 flex-col items-center justify-center rounded-xl bg-sky-50 dark:bg-sky-950/30 shrink-0 transition-transform group-hover:scale-105">
                            <span class="text-lg font-black leading-none text-sky-700 dark:text-sky-300">{{ formatEventDayNum(event.event_date) }}</span>
                            <span class="text-[9px] font-bold text-sky-500 dark:text-sky-400 uppercase">{{ formatEventMonth(event.event_date) }}</span>
                        </div>
                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <p class="text-[12px] font-bold text-slate-800 dark:text-white truncate leading-tight">{{ event.title }}</p>
                            <p v-if="event.location" class="text-[10px] text-slate-400 dark:text-zinc-500 truncate mt-0.5 flex items-center gap-0.5">
                                <MapPin class="h-2.5 w-2.5 shrink-0" />
                                {{ event.location }}
                            </p>
                            <div class="flex items-center gap-2 mt-1">
                                <span v-if="event.event_time_start" class="text-[9px] font-medium text-slate-400 dark:text-zinc-500 flex items-center gap-0.5">
                                    <Clock class="h-2.5 w-2.5" />
                                    {{ event.event_time_start?.substring(0, 5) }}
                                </span>
                                <span v-if="event.max_participants" class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-sky-50 text-sky-600 dark:bg-sky-950/30 dark:text-sky-400">
                                    {{ event.registrations_count }}/{{ event.max_participants }}
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

            </Deferred>
        </div>
    </TraceAdminLayout>
</template>
