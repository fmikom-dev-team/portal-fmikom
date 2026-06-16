<script setup lang="ts">
import { Deferred, Head, Link } from '@inertiajs/vue3';
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
    Sparkles,
    MapPin,
    Building2,
    Database,
    LayoutDashboard,
} from 'lucide-vue-next';
import { TPageHeader, TStatCard } from '@/components/trace';
import { Skeleton } from '@/components/ui/skeleton';
import TraceAdminLayout from '@/layouts/TraceAdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
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
        totalAlumni: any;
        employmentRate: any;
        studiLanjut: any;
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
    } | null;
    alumniGrowthData?: any;
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
                label: (ctx: any) => {
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
</script>
 
<template>
    <TraceAdminLayout title="Dashboard" :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 max-w-7xl mx-auto w-full pb-12">
            
            <!-- Welcome Header -->
            <TPageHeader
                title="Dashboard Tracer Study"
                description="Ringkasan analisis sebaran karir alumni dan keaktifan pengisian kuesioner."
                :icon="LayoutDashboard"
            />
 
            <Deferred data="stats">
                <template #fallback>
                    <div class="space-y-6">
                        <!-- KPI Card Skeletons -->
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div v-for="i in 4" :key="i" class="rounded-3xl border border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 space-y-3">
                                <div class="flex items-center justify-between">
                                    <Skeleton class="h-3 w-24 rounded-md" />
                                    <Skeleton class="h-8 w-8 rounded-xl" />
                                </div>
                                <Skeleton class="h-8 w-20 rounded-lg" />
                                <Skeleton class="h-3 w-32 rounded-md" />
                            </div>
                        </div>
                        <!-- Chart Skeletons -->
                        <div class="grid gap-4 lg:grid-cols-2">
                            <div class="rounded-3xl border border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 space-y-4">
                                <Skeleton class="h-5 w-40 rounded-lg" />
                                <Skeleton class="h-3 w-56 rounded-md" />
                                <Skeleton class="h-56 w-full rounded-2xl" />
                            </div>
                            <div class="rounded-3xl border border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 space-y-4">
                                <Skeleton class="h-5 w-40 rounded-lg" />
                                <Skeleton class="h-3 w-56 rounded-md" />
                                <Skeleton class="h-56 w-full rounded-2xl" />
                            </div>
                        </div>
                    </div>
                </template>

            <!-- ============ KPI CARDS ============ -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <template v-if="props.stats">
                    <TStatCard v-bind="props.stats.totalAlumni" />
                    <TStatCard v-bind="props.stats.employmentRate" color="emerald" />
                    <TStatCard v-bind="props.stats.studiLanjut" color="violet" />
                    
                    <TStatCard
                        label="Kuesioner Tracer"
                        :value="`${props.stats.kuesionerStats.response_rate}%`"
                        :icon="ClipboardCheck"
                        trend="Respon Rate"
                        :trend-up="true"
                        :trend-label="`${props.stats.kuesionerStats.total_kuesioners} Kuesioner · ${props.stats.kuesionerStats.total_responses} Respon`"
                        color="violet"
                    />
                </template>
            </div>

            <!-- ============ ROW 2: Donut Status Karir + Top Sektor ============ -->
            <div class="grid gap-4 lg:grid-cols-2" v-if="props.stats">
                <!-- Status Karir Donut -->
                <div class="rounded-3xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-xs">
                    <div class="flex items-center gap-2 mb-5">
                        <Briefcase class="h-4 w-4 text-blue-500" />
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-400">Status Karir Alumni</h3>
                    </div>
                    <div class="flex items-center gap-6">
                        <!-- Donut -->
                        <div class="relative h-48 w-48 flex-shrink-0">
                            <Doughnut v-if="careerChartData && careerTotal > 0" :data="careerChartData" :options="donutOptions" />
                            <div v-if="careerTotal > 0" class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                <span class="text-2xl font-black text-slate-800 dark:text-white">{{ careerTotal }}</span>
                                <span class="text-[9px] font-bold text-slate-400 uppercase">Total</span>
                            </div>
                            <div v-else class="h-full flex items-center justify-center text-slate-300 dark:text-slate-700">
                                <Users class="h-12 w-12" />
                            </div>
                        </div>
                        <!-- Legend -->
                        <div class="flex-1 space-y-3">
                            <div v-for="item in props.stats.careerBreakdown" :key="item.label" class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="h-3 w-3 rounded-full flex-shrink-0" :style="`background:${item.color}`"></div>
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-400">{{ item.label }}</span>
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
                <div class="rounded-3xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-xs">
                    <div class="flex items-center gap-2 mb-5">
                        <Building2 class="h-4 w-4 text-indigo-500" />
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-400">Top Sektor Industri</h3>
                    </div>
                    <div v-if="props.stats.topSektors.length > 0" class="space-y-3">
                        <div v-for="(item, i) in props.stats.topSektors" :key="item.name" class="flex items-center gap-3">
                            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-indigo-500 text-[9px] font-black text-white flex-shrink-0">
                                {{ i + 1 }}
                            </span>
                            <div class="flex-1 relative h-9">
                                <div class="absolute inset-0 rounded-xl bg-slate-50 dark:bg-slate-800/50 overflow-hidden">
                                    <div
                                        class="h-full rounded-xl bg-indigo-500/15 dark:bg-indigo-500/20 transition-all duration-500"
                                        :style="`width: ${Math.max(10, (item.count / maxRank(props.stats.topSektors)) * 100)}%`"
                                    ></div>
                                </div>
                                <div class="absolute inset-0 flex items-center justify-between px-3">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 truncate pr-2">{{ item.name }}</span>
                                    <span class="text-xs font-black text-indigo-600 dark:text-indigo-400 flex-shrink-0">{{ item.count }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex flex-col items-center justify-center py-12 text-slate-400">
                        <Building2 class="h-8 w-8 text-slate-300 dark:text-slate-700 mb-2" />
                        <p class="text-xs font-medium">Belum ada data sektor industri</p>
                    </div>
                </div>
            </div>

            <!-- ============ ROW 3: Chart + Top Kota + Keterisian ============ -->
            <div class="grid gap-4 lg:grid-cols-3" v-if="props.stats">
                <!-- Alumni Growth Chart -->
                <div class="lg:col-span-2">
                    <template v-if="props.alumniGrowthData">
                        <AlumniGrowthChart :data="props.alumniGrowthData" />
                    </template>
                    <template v-else>
                        <div class="h-[420px] w-full animate-pulse rounded-3xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 font-medium">
                            Loading chart data...
                        </div>
                    </template>
                </div>

                <!-- Right Column: Top Kota + Keterisian -->
                <div class="flex flex-col gap-4">
                    <!-- Top 5 Kota -->
                    <div class="rounded-3xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-xs">
                        <div class="flex items-center gap-2 mb-4">
                            <MapPin class="h-4 w-4 text-emerald-500" />
                            <h3 class="text-sm font-black uppercase tracking-widest text-slate-400">Top Kota Tujuan</h3>
                        </div>
                        <div v-if="props.stats.topCities.length > 0" class="space-y-2.5">
                            <div v-for="(city, i) in props.stats.topCities" :key="city.name" class="flex items-center gap-2.5">
                                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-emerald-500 text-[9px] font-black text-white flex-shrink-0">
                                    {{ i + 1 }}
                                </span>
                                <div class="flex-1 relative h-8">
                                    <div class="absolute inset-0 rounded-lg bg-slate-50 dark:bg-slate-800/50 overflow-hidden">
                                        <div
                                            class="h-full rounded-lg bg-emerald-500/15 dark:bg-emerald-500/20 transition-all duration-500"
                                            :style="`width: ${Math.max(10, (city.count / maxRank(props.stats.topCities)) * 100)}%`"
                                        ></div>
                                    </div>
                                    <div class="absolute inset-0 flex items-center justify-between px-3">
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 truncate pr-2">{{ city.name }}</span>
                                        <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 flex-shrink-0">{{ city.count }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-6 text-xs text-slate-400">Belum ada data lokasi</div>
                    </div>

                    <!-- Keterisian Data -->
                    <div class="rounded-3xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-xs">
                        <div class="flex items-center gap-2 mb-4">
                            <Database class="h-4 w-4 text-amber-500" />
                            <h3 class="text-sm font-black uppercase tracking-widest text-slate-400">Keterisian Data</h3>
                        </div>
                        <div class="space-y-3">
                            <div v-for="item in props.stats.dataCompleteness" :key="item.label">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400">{{ item.label }}</span>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[10px] font-medium text-slate-400">{{ item.value }}/{{ item.total }}</span>
                                        <span class="text-[10px] font-black" :class="getRateTextColor(item.rate)">{{ item.rate }}%</span>
                                    </div>
                                </div>
                                <div class="h-1.5 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-700" :class="getRateColor(item.rate)" :style="`width: ${item.rate}%`"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ ROW 4: Sebaran Prodi Donut + Respon Terbaru ============ -->
            <div class="grid gap-4 lg:grid-cols-3" v-if="props.stats">
                <!-- Sebaran Prodi Donut -->
                <div class="lg:col-span-2 rounded-3xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-xs">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-2">
                            <BookOpen class="h-4 w-4 text-purple-500" />
                            <h3 class="text-sm font-black uppercase tracking-widest text-slate-400">Sebaran Program Studi</h3>
                        </div>
                        <Link href="/trace/admin/alumni" class="text-[10px] font-black text-blue-600 hover:underline inline-flex items-center gap-0.5">
                            Lihat Alumni <ArrowRight class="h-3 w-3" />
                        </Link>
                    </div>
                    <div v-if="props.stats.prodiDistribution.labels.length" class="flex items-center gap-8">
                        <!-- Donut -->
                        <div class="relative h-52 w-52 flex-shrink-0">
                            <Doughnut v-if="prodiChartData" :data="prodiChartData" :options="donutOptions" />
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                <span class="text-2xl font-black text-slate-800 dark:text-white">{{ props.stats.total_alumni_raw }}</span>
                                <span class="text-[9px] font-bold text-slate-400 uppercase">Alumni</span>
                            </div>
                        </div>
                        <!-- Legend + Bars -->
                        <div class="flex-1 space-y-3">
                            <div v-for="(label, i) in props.stats.prodiDistribution.labels" :key="label">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <div class="h-2.5 w-2.5 rounded-full flex-shrink-0" :style="`background:${prodiColors[i]}`"></div>
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ label }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-black text-slate-800 dark:text-white">{{ props.stats.prodiDistribution.counts[i] }}</span>
                                        <span class="text-[10px] font-bold text-slate-400 w-10 text-right">
                                            {{ props.stats.total_alumni_raw > 0 ? Math.round((props.stats.prodiDistribution.counts[i] / props.stats.total_alumni_raw) * 100) : 0 }}%
                                        </span>
                                    </div>
                                </div>
                                <div class="h-2 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                    <div 
                                        class="h-full rounded-full transition-all duration-700"
                                        :style="`width: ${Math.max(5, (props.stats.prodiDistribution.counts[i] / (props.stats.total_alumni_raw || 1)) * 100)}%; background:${prodiColors[i]}`"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-10 text-slate-400 italic text-xs">
                        Belum ada data program studi.
                    </div>
                </div>

                <!-- Aktivitas Terbaru -->
                <div class="rounded-3xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-xs">
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
                            class="p-3 rounded-xl border border-slate-50 dark:border-slate-800/60 bg-slate-50/30 dark:bg-slate-800/20"
                        >
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="text-xs font-black text-slate-800 dark:text-white truncate max-w-[60%]">{{ act.user_name }}</h4>
                                <span class="text-[9px] font-bold text-slate-400">{{ formatTimeAgo(act.created_at) }}</span>
                            </div>
                            <p class="text-[10px] text-slate-600 dark:text-slate-400 truncate">{{ act.description }}</p>
                            <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-50 dark:border-slate-800/40">
                                <span
                                    class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded-md"
                                    :class="{
                                        'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400': act.action.startsWith('auth'),
                                        'bg-violet-50 text-violet-600 dark:bg-violet-950/30 dark:text-violet-400': act.action.startsWith('job'),
                                        'bg-sky-50 text-sky-600 dark:bg-sky-950/30 dark:text-sky-400': act.action.startsWith('event'),
                                        'bg-amber-50 text-amber-600 dark:bg-amber-950/30 dark:text-amber-400': act.action.startsWith('kuesioner'),
                                        'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/30 dark:text-emerald-400': act.action.startsWith('applicant'),
                                        'bg-rose-50 text-rose-600 dark:bg-rose-950/30 dark:text-rose-400': act.action.startsWith('career'),
                                        'bg-cyan-50 text-cyan-600 dark:bg-cyan-950/30 dark:text-cyan-400': act.action.startsWith('profile'),
                                    }"
                                >{{ act.action }}</span>
                                <span v-if="act.ip_address" class="text-[9px] text-slate-400">{{ act.ip_address }}</span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-10 border border-dashed border-slate-100 dark:border-slate-800 rounded-2xl">
                        <Users class="h-8 w-8 text-slate-300 dark:text-slate-700 mx-auto mb-2" />
                        <p class="text-[10px] text-slate-400">Belum ada aktivitas</p>
                    </div>
                </div>
            </div>

            </Deferred>
        </div>
    </TraceAdminLayout>
</template>
