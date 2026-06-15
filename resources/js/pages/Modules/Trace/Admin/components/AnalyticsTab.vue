<script setup lang="ts">
import axios from 'axios';
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
    RadialLinearScale,
} from 'chart.js';
import {
    Loader2,
    TrendingUp,
    BarChart3,
    PieChart as PieChartIcon,
    Download,
    Users,
    LayoutDashboard,
    Award,
} from 'lucide-vue-next';
import { ref, onMounted, computed } from 'vue';

import { Bar, Pie, Line, Radar } from 'vue-chartjs';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

import RespondentList from './RespondentList.vue';

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
    RadialLinearScale,
);

const props = defineProps({
    kuesionerId: {
        type: [Number, String],
        required: true,
    },
});

const loading = ref(true);
const exporting = ref(false);
const data = ref<any>(null);
const activeView = ref('overview');
const selectedSection = ref('all');

const fetchAnalytics = async () => {
    loading.value = true;

    try {
        const response = await axios.get(
            `/admin/quesionnaires/${props.kuesionerId}/analytics`,
        );
        data.value = response.data;
    } catch (error) {
        console.error('Gagal mengambil data analitik:', error);
    } finally {
        loading.value = false;
    }
};

const handleExport = async () => {
    exporting.value = true;

    try {
        const response = await axios.get(
            `/admin/quesionnaires/${props.kuesionerId}/export`,
            { responseType: 'blob' },
        );
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute(
            'download',
            `Export_Kuesioner_${props.kuesionerId}.csv`,
        );
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch (error) {
        console.error('Gagal mengekspor data:', error);
    } finally {
        exporting.value = false;
    }
};

onMounted(() => {
    fetchAnalytics();
});

const filteredSections = computed(() => {
    if (!data.value?.sections) {
return [];
}

    if (selectedSection.value === 'all') {
return data.value.sections;
}

    return data.value.sections.filter(
        (s: any) => s.id.toString() === selectedSection.value,
    );
});

const filteredCategories = computed(() => {
    if (!data.value?.categories) {
return [];
}

    if (selectedSection.value === 'all') {
return data.value.categories;
}

    return data.value.categories
        .map((cat: any) => ({
            ...cat,
            statistics: cat.statistics.filter(
                (stat: any) =>
                    stat.section_id?.toString() === selectedSection.value,
            ),
        }))
        .filter((cat: any) => cat.statistics.length > 0);
});

const getChartData = (stat: any) => {
    if (stat.analysis.distribution) {
        return {
            labels: stat.analysis.distribution.map((d: any) => d.label),
            datasets: [
                {
                    label: 'Jumlah Responden',
                    backgroundColor: [
                        '#3b82f6',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444',
                        '#8b5cf6',
                        '#ec4899',
                        '#6366f1',
                    ],
                    data: stat.analysis.distribution.map((d: any) => d.count),
                    borderRadius: 8,
                },
            ],
        };
    }

    return { labels: [], datasets: [] };
};

const getScaleBarData = (stat: any) => {
    if (stat.analysis.distribution) {
        const total = stat.analysis.total_responses || 1;
        return {
            labels: stat.analysis.distribution.map((d: any) => d.label),
            datasets: [
                {
                    label: 'Jumlah',
                    backgroundColor: stat.analysis.distribution.map((_: any, i: number) => {
                        const colors = ['#ef4444', '#f97316', '#eab308', '#22c55e', '#3b82f6', '#8b5cf6', '#ec4899', '#06b6d4', '#14b8a6', '#84cc16'];
                        return colors[i % colors.length];
                    }),
                    data: stat.analysis.distribution.map((d: any) => d.count),
                    borderRadius: 6,
                    barThickness: 28,
                },
            ],
        };
    }
    return { labels: [], datasets: [] };
};

const getTrendData = (stat: any) => {
    if (stat.analysis.trend && stat.analysis.trend.length > 0) {
        return {
            labels: stat.analysis.trend.map((t: any) => t.tahun),
            datasets: [
                {
                    label: 'Indeks Capaian',
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    data: stat.analysis.trend.map((t: any) => t.value),
                },
            ],
        };
    }

    return { labels: [], datasets: [] };
};

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom' as const,
            labels: {
                usePointStyle: true,
                padding: 20,
                font: { size: 10, weight: 'bold' as any },
            },
        },
    },
};

const scaleBarOptions = {
    indexAxis: 'y' as const,
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
    },
    scales: {
        x: {
            beginAtZero: true,
            ticks: { stepSize: 1, font: { size: 11, weight: 'bold' as any } },
            grid: { display: false },
        },
        y: {
            ticks: { font: { size: 12, weight: 'bold' as any } },
            grid: { display: false },
        },
    },
};

const lineChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true, max: 5 } },
};

const getRadarOptions = (stat: any) => ({
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        r: {
            angleLines: { display: true },
            suggestedMin: 0,
            suggestedMax: stat.analysis?.scale_max || 5,
            ticks: { stepSize: 1, font: { size: 9 } },
            pointLabels: { font: { size: 11, weight: 'bold' as any } },
        },
    },
    plugins: { legend: { position: 'bottom' as const } },
});
</script>

<template>
    <div class="space-y-8">
        <!-- Action Bar -->
        <div
            class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"
        >
            <div class="flex items-center gap-3">
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-500/20"
                >
                    <BarChart3 class="h-6 w-6" />
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-black tracking-tight">
                            Analisis Hasil
                        </h2>
                        <Badge
                            v-if="data"
                            variant="secondary"
                            class="rounded-lg border border-blue-200 bg-blue-50 font-bold text-blue-700 dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-400"
                        >
                            {{ data.total_responses }} Responden
                        </Badge>
                    </div>
                    <p class="text-xs font-medium text-muted-foreground">
                        Visualisasi data responden kuesioner
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <Button
                    variant="outline"
                    size="sm"
                    class="h-10 gap-2 rounded-xl bg-white font-bold dark:bg-slate-900"
                    :disabled="exporting"
                    @click="handleExport"
                >
                    <Loader2 v-if="exporting" class="h-4 w-4 animate-spin" />
                    <Download v-else class="h-4 w-4 text-blue-600" />
                    Export CSV
                </Button>
            </div>
        </div>

        <div class="w-full space-y-6">
            <div class="flex items-center justify-between border-b pb-1">
                <div class="flex h-auto gap-8 bg-transparent p-0">
                    <button
                        @click="activeView = 'overview'"
                        :class="[
                            'relative h-10 rounded-none border-b-2 bg-transparent px-2 pb-2 text-sm font-bold transition-all',
                            activeView === 'overview'
                                ? 'border-blue-600 text-blue-600'
                                : 'border-transparent text-muted-foreground hover:text-slate-600',
                        ]"
                    >
                        <div class="flex items-center gap-2">
                            <LayoutDashboard class="h-4 w-4" />
                            Ringkasan
                        </div>
                    </button>
                    <button
                        @click="activeView = 'respondents'"
                        :class="[
                            'relative h-10 rounded-none border-b-2 bg-transparent px-2 pb-2 text-sm font-bold transition-all',
                            activeView === 'respondents'
                                ? 'border-blue-600 text-blue-600'
                                : 'border-transparent text-muted-foreground hover:text-slate-600',
                        ]"
                    >
                        <div class="flex items-center gap-2">
                            <Users class="h-4 w-4" />
                            Daftar Responden
                        </div>
                    </button>
                </div>

                <div
                    v-if="activeView === 'overview'"
                    class="flex items-center gap-2"
                >
                    <Select v-model="selectedSection">
                        <SelectTrigger
                            class="h-9 w-[200px] rounded-xl border-none bg-slate-100 font-bold dark:bg-slate-800"
                        >
                            <SelectValue placeholder="Pilih Halaman" />
                        </SelectTrigger>
                        <SelectContent class="rounded-xl border-none shadow-xl">
                            <SelectItem value="all">Semua Halaman</SelectItem>
                            <SelectItem
                                v-for="s in data?.sections"
                                :key="s.id"
                                :value="s.id.toString()"
                            >
                                {{ s.title }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <div
                v-if="activeView === 'overview'"
                class="mt-0 animate-in space-y-8 duration-500 fade-in"
            >
                <div
                    v-if="loading"
                    class="flex flex-col items-center justify-center py-20"
                >
                    <Loader2 class="h-10 w-10 animate-spin text-blue-500" />
                    <p class="mt-4 text-sm font-medium text-muted-foreground">
                        Menganalisis data...
                    </p>
                </div>

                <template v-else-if="data">
                    <!-- Radar Chart for Competency -->
                    <Card
                        v-if="data.radar_data"
                        class="overflow-hidden border-none bg-white shadow-sm dark:bg-slate-900/40"
                    >
                        <CardHeader class="pb-2">
                            <div class="flex items-center gap-2">
                                <Award class="h-4 w-4 text-blue-500" />
                                <CardTitle class="text-sm font-bold"
                                    >Analisis Kompetensi Lulusan</CardTitle
                                >
                            </div>
                            <CardDescription class="text-xs">
                                Profil kemampuan alumni berdasarkan standar
                                kompetensi prodi
                            </CardDescription>
                        </CardHeader>
                        <CardContent
                            class="flex h-[400px] items-center justify-center p-6"
                        >
                            <Radar
                                :data="data.radar_data"
                                :options="getRadarOptions({ analysis: { scale_max: 5 } })"
                            />
                        </CardContent>
                    </Card>

                    <!-- Charts per Category -->
                    <div
                        v-for="category in filteredCategories"
                        :key="category.name"
                        class="space-y-6"
                    >
                        <div class="flex items-center gap-3 pt-4">
                            <Badge
                                class="rounded-lg bg-blue-600 px-3 py-1 text-[10px] font-black tracking-widest text-white uppercase"
                            >
                                Kategori: {{ category.name }}
                            </Badge>
                            <div
                                class="h-px flex-1 bg-slate-200 dark:bg-slate-800"
                            ></div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            <Card
                                v-for="stat in category.statistics"
                                :key="stat.question_id"
                                class="group overflow-hidden border-none shadow-sm transition-all hover:shadow-xl dark:bg-slate-900/40"
                            >
                                <CardHeader
                                    class="border-b bg-slate-50/50 pb-5 dark:bg-slate-900/50"
                                >
                                    <div class="flex flex-col gap-3">
                                        <!-- Acuan Badges -->
                                        <div
                                            v-if="
                                                stat.acuan &&
                                                stat.acuan.length > 0
                                            "
                                            class="flex flex-wrap items-center gap-2"
                                        >
                                            <Badge
                                                v-for="ref in stat.acuan"
                                                :key="ref"
                                                variant="outline"
                                                class="rounded-lg border-blue-200 bg-blue-50 px-2 py-0.5 text-[9px] font-bold text-blue-600 uppercase dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-400"
                                            >
                                                {{ ref }}
                                            </Badge>
                                        </div>

                                        <CardTitle
                                            class="text-sm leading-snug font-black text-slate-800 dark:text-slate-100"
                                        >
                                            {{ stat.teks }}
                                        </CardTitle>
                                    </div>
                                </CardHeader>

                                <CardContent class="pt-6">
                                    <!-- Choice Questions: Pie Chart (radio/checkbox/dropdown only) -->
                                    <div
                                        v-if="
                                            ['radio', 'dropdown', 'checkbox'].includes(stat.tipe) &&
                                            stat.analysis.distribution &&
                                            stat.analysis.distribution.length > 0
                                        "
                                        class="space-y-6"
                                    >
                                        <div class="h-[260px] w-full">
                                            <div
                                                class="mb-4 flex items-center gap-2 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                            >
                                                <PieChartIcon
                                                    class="h-3.5 w-3.5"
                                                />
                                                Distribusi Respon
                                            </div>
                                            <Pie
                                                :data="getChartData(stat)"
                                                :options="chartOptions"
                                            />
                                        </div>
                                    </div>

                                    <!-- Scale Linear: Bar Distribution + Average -->
                                    <div
                                        v-if="
                                            stat.tipe === 'scale' &&
                                            stat.analysis.distribution &&
                                            stat.analysis.distribution.length > 0
                                        "
                                        class="space-y-5"
                                    >
                                        <!-- Average Badge -->
                                        <div class="flex items-center justify-between rounded-2xl bg-gradient-to-r from-blue-50 to-indigo-50 p-5 dark:from-blue-950/30 dark:to-indigo-950/30">
                                            <div>
                                                <div class="text-[10px] font-black tracking-widest text-slate-400 uppercase">Skor Rata-rata</div>
                                                <div class="mt-1 flex items-baseline gap-1">
                                                    <span class="text-3xl font-black tracking-tighter text-blue-600 dark:text-blue-400">{{ stat.analysis.average }}</span>
                                                    <span class="text-xs font-bold text-slate-400">/ {{ stat.analysis.scale_max || 5 }}.0</span>
                                                </div>
                                                <div v-if="stat.analysis.scale_label_min || stat.analysis.scale_label_max" class="mt-1 text-[9px] font-medium text-slate-400">
                                                    {{ stat.analysis.scale_label_min || stat.analysis.scale_min }} → {{ stat.analysis.scale_label_max || stat.analysis.scale_max }}
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-[10px] font-bold text-slate-400">Total Respon</div>
                                                <div class="text-xl font-black text-slate-700 dark:text-slate-300">{{ stat.analysis.total_responses || 0 }}</div>
                                            </div>
                                        </div>

                                        <!-- Bar Chart Distribution -->
                                        <div>
                                            <div class="mb-3 flex items-center gap-2 text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                                <BarChart3 class="h-3.5 w-3.5" />
                                                Distribusi Jawaban
                                            </div>
                                            <div class="h-[200px] w-full">
                                                <Bar
                                                    :data="getScaleBarData(stat)"
                                                    :options="scaleBarOptions"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Scale with average only (no distribution) -->
                                    <div
                                        v-else-if="
                                            stat.tipe === 'scale' &&
                                            stat.analysis.average !== undefined &&
                                            stat.analysis.average !== null
                                        "
                                        class="flex flex-col items-center justify-center rounded-3xl bg-slate-50/50 p-6 dark:bg-slate-800/30"
                                    >
                                        <div class="mb-1 text-[10px] font-black tracking-widest text-slate-400 uppercase">Skor Rata-rata</div>
                                        <div class="flex items-baseline gap-1">
                                            <span class="text-4xl font-black tracking-tighter text-blue-600 dark:text-blue-400">{{ stat.analysis.average }}</span>
                                            <span class="text-xs font-bold text-slate-400">/ {{ stat.analysis.scale_max || 5 }}.0</span>
                                        </div>
                                    </div>

                                    <!-- Matrix: Radar + Row Averages Table -->
                                    <div
                                        v-if="stat.analysis.radar_data"
                                        class="space-y-6"
                                    >
                                        <div class="h-[300px] w-full">
                                            <div
                                                class="mb-4 flex items-center gap-2 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                            >
                                                <TrendingUp
                                                    class="h-3.5 w-3.5"
                                                />
                                                Analisis Profil (Radar)
                                            </div>
                                            <Radar
                                                :data="stat.analysis.radar_data"
                                                :options="getRadarOptions(stat)"
                                            />
                                        </div>

                                        <!-- Row Averages Table -->
                                        <div v-if="stat.analysis.row_averages && stat.analysis.row_averages.length > 0">
                                            <div class="mb-3 flex items-center gap-2 text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                                <BarChart3 class="h-3.5 w-3.5" />
                                                Detail Per Sub-Pertanyaan
                                            </div>
                                            <div class="space-y-2.5">
                                                <div
                                                    v-for="row in stat.analysis.row_averages"
                                                    :key="row.label"
                                                    class="rounded-xl bg-slate-50/80 p-3.5 dark:bg-slate-800/40"
                                                >
                                                    <div class="flex items-center justify-between mb-2">
                                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ row.label }}</span>
                                                        <span class="text-xs font-black text-blue-600 dark:text-blue-400">{{ row.average }} / {{ stat.analysis.scale_max || 5 }}</span>
                                                    </div>
                                                    <div class="h-2 w-full rounded-full bg-slate-200 dark:bg-slate-700">
                                                        <div
                                                            class="h-2 rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 transition-all duration-500"
                                                            :style="{ width: row.percent + '%' }"
                                                        ></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Column Scale Legend -->
                                        <div v-if="stat.analysis.columns && stat.analysis.columns.length > 0" class="rounded-xl bg-slate-50/80 p-4 dark:bg-slate-800/30">
                                            <div class="mb-2 text-[9px] font-black tracking-widest text-slate-400 uppercase">Keterangan Skala</div>
                                            <div class="flex flex-wrap gap-3">
                                                <div v-for="(col, ci) in stat.analysis.columns" :key="ci" class="flex items-center gap-1.5">
                                                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-100 text-[9px] font-black text-blue-700 dark:bg-blue-900/40 dark:text-blue-400">{{ ci + 1 }}</span>
                                                    <span class="text-[10px] font-medium text-slate-600 dark:text-slate-400">{{ col }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Text Questions -->
                                    <div
                                        v-if="
                                            stat.analysis.recent_responses &&
                                            stat.analysis.recent_responses
                                                .length > 0
                                        "
                                        class="space-y-4"
                                    >
                                        <div
                                            class="flex items-center justify-between border-b pb-2"
                                        >
                                            <div
                                                class="text-[10px] font-black tracking-widest text-slate-400 uppercase italic"
                                            >
                                                Jawaban Terbaru
                                            </div>
                                            <Badge
                                                variant="outline"
                                                class="rounded-full bg-slate-50 px-2 py-0 text-[10px] font-bold text-slate-500"
                                            >
                                                {{
                                                    stat.analysis
                                                        .total_responses
                                                }}
                                                Respon
                                            </Badge>
                                        </div>

                                        <div
                                            class="custom-scrollbar max-h-[220px] space-y-2 overflow-y-auto pr-2"
                                        >
                                            <div
                                                v-for="(resp, idx) in stat
                                                    .analysis.recent_responses"
                                                :key="idx"
                                                class="rounded-xl bg-slate-50/80 p-3 text-xs font-medium text-slate-600 transition-colors hover:bg-blue-50/50 dark:bg-slate-800/40 dark:text-slate-300"
                                            >
                                                {{ resp }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Empty State -->
                                    <div
                                        v-if="
                                            (!stat.analysis.distribution ||
                                                stat.analysis.distribution
                                                    .length === 0) &&
                                            !stat.analysis.radar_data &&
                                            stat.analysis.average ===
                                                undefined &&
                                            (!stat.analysis.recent_responses ||
                                                stat.analysis.recent_responses
                                                    .length === 0)
                                        "
                                        class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/30 p-8 text-center dark:border-slate-800 dark:bg-slate-800/10"
                                    >
                                        <p
                                            class="text-xs font-medium text-slate-400"
                                        >
                                            Belum ada respon untuk pertanyaan
                                            ini.
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </template>
            </div>

            <div
                v-else-if="activeView === 'respondents'"
                class="mt-0 animate-in duration-500 fade-in"
            >
                <RespondentList :kuesioner-id="kuesionerId" />
            </div>
        </div>
    </div>
</template>

<style scoped>
.font-black {
    font-weight: 900;
}
</style>
