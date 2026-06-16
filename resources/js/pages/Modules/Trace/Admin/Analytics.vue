<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    ArcElement,
} from 'chart.js';
import {
    Users,
    TrendingUp,
    Briefcase,
    BookOpen,
    BarChart3,
    Activity,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Bar, Pie, Line } from 'vue-chartjs';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import { TPageHeader, TStatCard } from '@/components/trace';
import TraceAdminLayout from '@/layouts/TraceAdminLayout.vue';
import type { BreadcrumbItem } from '@/types';

ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    PointElement,
    LineElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
);


const props = defineProps({
    totalAlumni: Number,
    employmentRate: Number,
    studyRate: Number,
    responseRate: Number,
    careerStatus: Array,
    alumniPerAngkatan: Array,
    topSektors: Array,
    prodiDistribution: Array,
});

const selectedProdi = ref('all');

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Analitik',
        href: '/trace/admin/analytics',
    },
];

const filteredProdiData = computed(() => {
    if (selectedProdi.value === 'all' || !props.prodiDistribution) {
        return props.prodiDistribution || [];
    }

    return props.prodiDistribution.filter(
        (p: any) => p.program_studi === selectedProdi.value
    );
});

const careerStatusChart = computed(() => ({
    labels: props.careerStatus?.map((c: any) => c.label) || [],
    datasets: [
        {
            label: 'Jumlah Alumni',
            backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444'],
            data: props.careerStatus?.map((c: any) => c.value) || [],
            borderRadius: 8,
        },
    ],
}));

const alumniPerAngkatanChart = computed(() => ({
    labels: props.alumniPerAngkatan?.map((a: any) => a.angkatan) || [],
    datasets: [
        {
            label: 'Jumlah Alumni',
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            fill: true,
            tension: 0.4,
            data: props.alumniPerAngkatan?.map((a: any) => a.total) || [],
        },
    ],
}));

const topSektorsChart = computed(() => ({
    labels: props.topSektors?.map((s: any) => s.sektor_industri) || [],
    datasets: [
        {
            label: 'Jumlah Alumni',
            backgroundColor: [
                '#3b82f6',
                '#10b981',
                '#f59e0b',
                '#ef4444',
                '#8b5cf6',
                '#ec4899',
                '#06b6d4',
            ],
            data: props.topSektors?.map((s: any) => s.total) || [],
            borderRadius: 8,
        },
    ],
}));

const prodiDistributionChart = computed(() => ({
    labels: props.prodiDistribution?.map((p: any) => p.program_studi) || [],
    datasets: [
        {
            label: 'Jumlah Alumni',
            backgroundColor: [
                '#3b82f6',
                '#10b981',
                '#f59e0b',
                '#ef4444',
                '#8b5cf6',
            ],
            data: props.prodiDistribution?.map((p: any) => p.total) || [],
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom' as const,
            labels: {
                usePointStyle: true,
                padding: 15,
                font: { size: 11, weight: 'bold' as any },
            },
        },
    },
};

const lineChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
    },
    scales: {
        y: {
            beginAtZero: true,
        },
    },
};
</script>

<template>
    <Head title="Analitik" />

    <TraceAdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Page Header -->
            <TPageHeader
                title="Analitik"
                description="Ikhtisar data alumni dan tren karir"
                :icon="Activity"
            />

            <!-- KPI Summary Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <TStatCard
                    label="Total Alumni"
                    :value="totalAlumni"
                    :icon="Users"
                    color="primary"
                    trend-label="Jumlah total alumni terdaftar"
                />
                <TStatCard
                    label="Tingkat Kerja"
                    :value="`${employmentRate}%`"
                    :icon="Briefcase"
                    color="emerald"
                    trend-label="Alumni yang bekerja/wirausaha"
                />
                <TStatCard
                    label="Lanjut Studi"
                    :value="`${studyRate}%`"
                    :icon="BookOpen"
                    color="accent"
                    trend-label="Alumni yang melanjutkan studi"
                />
                <TStatCard
                    label="Response Rate"
                    :value="`${responseRate}%`"
                    :icon="BarChart3"
                    color="primary"
                    trend-label="Tingkat respons kuesioner"
                />
            </div>

            <!-- Charts Grid -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Distribusi Status Karir -->
                <Card class="overflow-hidden border border-slate-100 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <CardHeader class="border-b pb-3 bg-slate-50/50 dark:bg-slate-900/50">
                        <CardTitle class="text-sm font-bold flex items-center gap-2">
                            <Briefcase class="h-4 w-4 text-[#0C447C] dark:text-[#85B7EB]" />
                            Distribusi Status Karir Terkini
                        </CardTitle>
                        <CardDescription class="text-xs">
                            Kategori: Status Karir Alumni
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="pt-6 h-[350px] flex items-center">
                        <Pie
                            :data="careerStatusChart"
                            :options="chartOptions"
                            v-if="careerStatus && careerStatus.length"
                        />
                    </CardContent>
                </Card>

                <!-- Alumni per Angkatan -->
                <Card class="overflow-hidden border border-slate-100 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <CardHeader class="border-b pb-3 bg-slate-50/50 dark:bg-slate-900/50">
                        <CardTitle class="text-sm font-bold flex items-center gap-2">
                            <TrendingUp class="h-4 w-4 text-[#0C447C] dark:text-[#85B7EB]" />
                            Perkembangan Alumni per Angkatan
                        </CardTitle>
                        <CardDescription class="text-xs">
                            Kategori: Distribusi Temporal
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="pt-6 h-[350px] flex items-center">
                        <Line
                            :data="alumniPerAngkatanChart"
                            :options="lineChartOptions"
                            v-if="alumniPerAngkatan && alumniPerAngkatan.length"
                        />
                    </CardContent>
                </Card>
            </div>

            <!-- Top Sektor Industri -->
            <Card class="overflow-hidden border border-slate-100 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <CardHeader class="border-b pb-3 bg-slate-50/50 dark:bg-slate-900/50">
                    <CardTitle class="text-sm font-bold flex items-center gap-2">
                        <Briefcase class="h-4 w-4 text-[#EF9F27] dark:text-[#FAC775]" />
                        7 Sektor Industri Terbesar
                    </CardTitle>
                    <CardDescription class="text-xs">
                        Kategori: Distribusi Industri
                    </CardDescription>
                </CardHeader>
                <CardContent class="pt-6 h-[350px] flex items-center">
                    <Bar
                        :data="topSektorsChart"
                        :options="chartOptions"
                        v-if="topSektors && topSektors.length"
                    />
                </CardContent>
            </Card>

            <!-- Program Studi Distribution -->
            <Card class="overflow-hidden border border-slate-100 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <CardHeader class="border-b pb-3 bg-slate-50/50 dark:bg-slate-900/50">
                    <CardTitle class="text-sm font-bold flex items-center gap-2">
                        <Users class="h-4 w-4 text-[#0C447C] dark:text-[#85B7EB]" />
                        Distribusi Alumni per Program Studi
                    </CardTitle>
                    <CardDescription class="text-xs">
                        Kategori: Distribusi Program
                    </CardDescription>
                </CardHeader>
                <CardContent class="pt-6 h-[400px] flex items-center">
                    <Bar
                        :data="prodiDistributionChart"
                        :options="chartOptions"
                        v-if="prodiDistribution && prodiDistribution.length"
                    />
                </CardContent>
            </Card>
        </div>
    </TraceAdminLayout>
</template>
