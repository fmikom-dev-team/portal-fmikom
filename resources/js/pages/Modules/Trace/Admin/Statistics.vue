<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    BarChart3, Users, Briefcase, GraduationCap, Clock, TrendingUp,
    CheckCircle2, Target, BookOpen, FileText, Search,
} from 'lucide-vue-next';
import { TPageHeader, TStatCard } from '@/components/trace';
import TraceAdminLayout from '@/layouts/TraceAdminLayout.vue';

interface ProdiStat {
    prodi: string;
    total: number;
    absorbed: number;
    rate: number;
}

interface WaitingProdi {
    prodi: string;
    avg_months: number;
    count: number;
    min_months: number;
    max_months: number;
}

interface WaitingDist {
    label: string;
    count: number;
}

interface KuesionerStat {
    id: number;
    judul: string;
    tahun: number;
    status: string;
    responses: number;
    response_rate: number;
}

const props = defineProps<{
    totalAlumni: number;
    absorptionRate: number;
    verticalRate: number;
    workingCount: number;
    wirausahaCount: number;
    studyCount: number;
    seekingCount: number;
    prodiStats: ProdiStat[];
    kuesionerStats: KuesionerStat[];
    avgWaitingTime: number;
    waitingTimePerProdi: WaitingProdi[];
    waitingDistribution: WaitingDist[];
}>();

const breadcrumbs = [
    { title: 'Laporan & Statistik', href: '/trace/admin/laporan' },
];

const statusCards = [
    { label: 'Bekerja', value: props.workingCount, icon: Briefcase, color: 'blue' },
    { label: 'Wirausaha', value: props.wirausahaCount, icon: TrendingUp, color: 'emerald' },
    { label: 'Lanjut Studi', value: props.studyCount, icon: GraduationCap, color: 'purple' },
    { label: 'Mencari Kerja', value: props.seekingCount, icon: Search, color: 'amber' },
];

const getWaitingColor = (months: number) => {
    if (months <= 3) return 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20';
    if (months <= 6) return 'text-blue-600 bg-blue-50 dark:bg-blue-900/20';
    if (months <= 12) return 'text-amber-600 bg-amber-50 dark:bg-amber-900/20';
    return 'text-red-600 bg-red-50 dark:bg-red-900/20';
};

const getWaitingBarColor = (months: number) => {
    if (months <= 3) return 'bg-emerald-500';
    if (months <= 6) return 'bg-blue-500';
    if (months <= 12) return 'bg-amber-500';
    return 'bg-red-500';
};

const getRateColor = (rate: number) => {
    if (rate >= 80) return 'text-emerald-600';
    if (rate >= 60) return 'text-blue-600';
    if (rate >= 40) return 'text-amber-600';
    return 'text-red-600';
};

const getRateBarColor = (rate: number) => {
    if (rate >= 80) return 'bg-emerald-500';
    if (rate >= 60) return 'bg-blue-500';
    if (rate >= 40) return 'bg-amber-500';
    return 'bg-red-500';
};

const totalWaitingCount = props.waitingDistribution.reduce((a, b) => a + b.count, 0);
const maxWaitingCount = Math.max(...props.waitingDistribution.map(d => d.count), 1);

const distColors = ['bg-emerald-500', 'bg-blue-500', 'bg-amber-500', 'bg-red-500'];
</script>

<template>
    <Head title="Laporan & Statistik" />

    <TraceAdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 max-w-7xl mx-auto w-full">

            <!-- Header -->
            <TPageHeader
                title="Laporan & Statistik IKU"
                description="Metrik kinerja dan capaian akreditasi tracer study."
                :icon="BarChart3"
            />

            <!-- ============ IKU CARDS ============ -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <TStatCard
                    label="Total Alumni"
                    :value="totalAlumni"
                    :icon="Users"
                    color="slate"
                />
                <TStatCard
                    label="IKU Penyerapan"
                    :value="`${absorptionRate}%`"
                    :icon="CheckCircle2"
                    color="emerald"
                />
                <TStatCard
                    label="Keselarasan Vertikal"
                    :value="`${verticalRate}%`"
                    :icon="Target"
                    color="primary"
                />
                <TStatCard
                    label="Waktu Tunggu Kerja"
                    :value="`${avgWaitingTime} bulan`"
                    :icon="Clock"
                    color="accent"
                    trend-label="Rata-rata dari lulus ke pekerjaan pertama"
                />
            </div>

            <!-- ============ STATUS KARIR ============ -->
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="mb-4 text-sm font-black uppercase tracking-widest text-slate-400">Sebaran Status Karir</h2>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div
                        v-for="card in statusCards"
                        :key="card.label"
                        class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-800/30"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg"
                            :class="`bg-${card.color}-100 dark:bg-${card.color}-900/30`"
                        >
                            <component :is="card.icon" class="h-5 w-5" :class="`text-${card.color}-600`" />
                        </div>
                        <div>
                            <p class="text-xl font-black text-slate-800 dark:text-white">{{ card.value }}</p>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ card.label }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ WAKTU TUNGGU KERJA ============ -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <!-- Distribusi Waktu Tunggu -->
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="mb-4 flex items-center gap-2">
                        <Clock class="h-4 w-4 text-amber-500" />
                        <h2 class="text-sm font-black uppercase tracking-widest text-slate-400">Distribusi Waktu Tunggu</h2>
                    </div>
                    <div class="space-y-3">
                        <div v-for="(item, i) in waitingDistribution" :key="item.label" class="flex items-center gap-3">
                            <span class="w-24 text-xs font-bold text-slate-600 dark:text-slate-400">{{ item.label }}</span>
                            <div class="flex-1 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 overflow-hidden relative">
                                <div
                                    class="h-full rounded-lg transition-all duration-500"
                                    :class="distColors[i]"
                                    :style="`width: ${Math.max(2, (item.count / maxWaitingCount) * 100)}%`"
                                ></div>
                                <span class="absolute inset-0 flex items-center pl-3 text-xs font-black"
                                    :class="item.count > maxWaitingCount * 0.3 ? 'text-white' : 'text-slate-600 dark:text-slate-400'"
                                >
                                    {{ item.count }} alumni
                                    <span v-if="totalWaitingCount > 0" class="ml-1 font-medium opacity-70">
                                        ({{ Math.round((item.count / totalWaitingCount) * 100) }}%)
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Waktu Tunggu per Prodi -->
                <div class="rounded-2xl border border-slate-200/60 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-4 flex items-center gap-2">
                        <BookOpen class="h-4 w-4 text-blue-500" />
                        <h2 class="text-sm font-black uppercase tracking-widest text-slate-400">Waktu Tunggu per Prodi</h2>
                    </div>
                    <div v-if="waitingTimePerProdi.length > 0" class="space-y-4">
                        <div
                            v-for="prodi in waitingTimePerProdi"
                            :key="prodi.prodi"
                            class="rounded-xl border border-slate-100 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-800/30"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ prodi.prodi }}</span>
                                <span
                                    class="rounded-full px-3 py-1 text-xs font-black"
                                    :class="getWaitingColor(prodi.avg_months)"
                                >
                                    {{ prodi.avg_months }} bulan
                                </span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-slate-200 dark:bg-slate-700 overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all duration-500"
                                    :class="getWaitingBarColor(prodi.avg_months)"
                                    :style="`width: ${Math.min(100, (prodi.avg_months / 18) * 100)}%`"
                                ></div>
                            </div>
                            <div class="mt-2 flex gap-4 text-[10px] text-slate-400">
                                <span>{{ prodi.count }} alumni</span>
                                <span>Min: {{ prodi.min_months }} bln</span>
                                <span>Max: {{ prodi.max_months }} bln</span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex flex-col items-center justify-center py-8 text-center text-slate-400">
                        <Clock class="mb-2 h-8 w-8 text-slate-300 dark:text-slate-700" />
                        <p class="text-sm font-medium">Belum ada data waktu tunggu</p>
                    </div>
                </div>
            </div>

            <!-- ============ PENYERAPAN PER PRODI ============ -->
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="mb-4 flex items-center gap-2">
                    <BarChart3 class="h-4 w-4 text-emerald-500" />
                    <h2 class="text-sm font-black uppercase tracking-widest text-slate-400">Penyerapan per Program Studi</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800">
                                <th class="py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Program Studi</th>
                                <th class="py-3 text-center text-[10px] font-bold uppercase tracking-widest text-slate-400">Total Alumni</th>
                                <th class="py-3 text-center text-[10px] font-bold uppercase tracking-widest text-slate-400">Terserap</th>
                                <th class="py-3 text-center text-[10px] font-bold uppercase tracking-widest text-slate-400">Rate</th>
                                <th class="py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400 min-w-[200px]">Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="prodi in prodiStats"
                                :key="prodi.prodi"
                                class="border-b border-slate-50 dark:border-slate-800/50"
                            >
                                <td class="py-3 font-bold text-slate-700 dark:text-slate-300">{{ prodi.prodi }}</td>
                                <td class="py-3 text-center font-bold text-slate-500">{{ prodi.total }}</td>
                                <td class="py-3 text-center font-black text-slate-800 dark:text-white">{{ prodi.absorbed }}</td>
                                <td class="py-3 text-center">
                                    <span class="font-black" :class="getRateColor(prodi.rate)">{{ prodi.rate }}%</span>
                                </td>
                                <td class="py-3">
                                    <div class="h-2.5 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                        <div
                                            class="h-full rounded-full transition-all duration-700"
                                            :class="getRateBarColor(prodi.rate)"
                                            :style="`width: ${prodi.rate}%`"
                                        ></div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ============ KUESIONER STATS ============ -->
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="mb-4 flex items-center gap-2">
                    <FileText class="h-4 w-4 text-purple-500" />
                    <h2 class="text-sm font-black uppercase tracking-widest text-slate-400">Statistik Kuesioner</h2>
                </div>
                <div v-if="kuesionerStats.length > 0" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800">
                                <th class="py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Kuesioner</th>
                                <th class="py-3 text-center text-[10px] font-bold uppercase tracking-widest text-slate-400">Tahun</th>
                                <th class="py-3 text-center text-[10px] font-bold uppercase tracking-widest text-slate-400">Status</th>
                                <th class="py-3 text-center text-[10px] font-bold uppercase tracking-widest text-slate-400">Responden</th>
                                <th class="py-3 text-center text-[10px] font-bold uppercase tracking-widest text-slate-400">Response Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="k in kuesionerStats"
                                :key="k.id"
                                class="border-b border-slate-50 dark:border-slate-800/50"
                            >
                                <td class="py-3 font-bold text-slate-700 dark:text-slate-300">{{ k.judul }}</td>
                                <td class="py-3 text-center font-medium text-slate-500">{{ k.tahun }}</td>
                                <td class="py-3 text-center">
                                    <span
                                        class="rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase"
                                        :class="k.status === 'published'
                                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400'
                                            : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'"
                                    >
                                        {{ k.status === 'published' ? 'Aktif' : 'Draft' }}
                                    </span>
                                </td>
                                <td class="py-3 text-center font-black text-slate-800 dark:text-white">{{ k.responses }}</td>
                                <td class="py-3 text-center">
                                    <span class="font-black" :class="getRateColor(k.response_rate)">{{ k.response_rate }}%</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="flex flex-col items-center justify-center py-8 text-center text-slate-400">
                    <FileText class="mb-2 h-8 w-8 text-slate-300 dark:text-slate-700" />
                    <p class="text-sm font-medium">Belum ada kuesioner</p>
                </div>
            </div>

            <!-- Catatan -->
            <div class="rounded-xl border border-amber-200/50 bg-amber-50/60 px-5 py-3 dark:border-amber-900/30 dark:bg-amber-950/20">
                <p class="text-[11px] text-amber-700 dark:text-amber-400">
                    <strong>Catatan:</strong> Waktu tunggu kerja dihitung dari perkiraan wisuda (Juli tahun lulus) hingga tanggal mulai pekerjaan pertama.
                    Keselarasan vertikal menggunakan deteksi sektor industri terkait TI/Informatika.
                </p>
            </div>
        </div>
    </TraceAdminLayout>
</template>
