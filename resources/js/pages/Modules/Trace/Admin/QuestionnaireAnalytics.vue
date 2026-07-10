<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { toast } from 'vue-sonner';
import {
    Chart as ChartJS, Title, Tooltip, Legend, BarElement,
    CategoryScale, LinearScale, PointElement, LineElement,
    ArcElement, RadialLinearScale,
} from 'chart.js';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import {
    Loader2, TrendingUp, BarChart3, PieChart as PieChartIcon,
    Download, Users, LayoutDashboard, Award, ArrowLeft, AlertCircle, Copy, Check, FileText, RefreshCw,
} from 'lucide-vue-next';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Bar, Pie, Line, Radar } from 'vue-chartjs';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { TPageHeader } from '@/components/Trace';
import TraceAdminLayout from '@/layouts/TraceAdminLayout.vue';
import type { KuesionerSection } from '@/types/trace';
import RespondentList from './components/RespondentList.vue';

ChartJS.register(CategoryScale, LinearScale, BarElement, PointElement, LineElement, ArcElement, Title, Tooltip, Legend, RadialLinearScale, ChartDataLabels);

const props = defineProps({
    kuesioner: { type: Object, required: true },
    kuesionerId: { type: [Number, String], required: true },
});

const loading = ref(true);
const exporting = ref(false);
const exportingPdf = ref(false);
const data = ref<Record<string, unknown> | null>(null);
const activeView = ref('overview');
const selectedSection = ref('all');
const error = ref<string | null>(null);
const analyticsContent = ref<HTMLElement | null>(null);
const copiedId = ref<string | number | null>(null);

// ── Live Stats (auto-refresh) ─────────────────────────────────────────────
interface LiveStats {
    total_responses: number;
    today_responses: number;
    last_response_at: string | null;
    cache_status: 'cached' | 'fresh';
}

const liveStats = ref<LiveStats | null>(null);
const refreshingLive = ref(false);
let refreshInterval: ReturnType<typeof setInterval> | null = null;

async function fetchLiveStats() {
    try {
        refreshingLive.value = true;
        const response = await axios.get(`/trace/admin/questionnaires/${props.kuesionerId}/live-stats`);
        liveStats.value = response.data;
    } catch (e) {

        toast.error('Gagal memuat data analitik.');
    } finally {
        refreshingLive.value = false;
    }
}

async function hardRefresh() {
    await fetchLiveStats();
    await fetchAnalytics();
}

const fetchAnalytics = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get(`/trace/admin/questionnaires/${props.kuesionerId}/analytics`);
        data.value = response.data;
    } catch (err: unknown) {
        error.value = (err as Record<string, unknown>)?.response ? ((err as Record<string, { data?: { message?: string } }>).response?.data?.message || 'Gagal mengambil data analitik') : 'Gagal mengambil data analitik';
    } finally {
        loading.value = false;
    }
};

const handleExport = () => {
    exporting.value = true;
    try {
        window.open(`/trace/admin/questionnaires/${props.kuesionerId}/export`, '_blank');
    } catch (err) {

        toast.error('Gagal memuat data analitik.');
    } finally {
        setTimeout(() => { exporting.value = false; }, 1500);
    }
};

const copyChart = async (statId: string | number) => {
    const card = document.querySelector(`[data-chart-id="${statId}"]`) as HTMLElement;
    if (!card) return;

    try {
        const { default: html2canvas } = await import('html2canvas-pro');
        const canvas = await html2canvas(card, {
            scale: 2,
            backgroundColor: '#ffffff',
            useCORS: true,
            logging: false,
        });

        canvas.toBlob(async (blob: Blob | null) => {
            if (!blob) return;
            await navigator.clipboard.write([
                new ClipboardItem({ 'image/png': blob }),
            ]);
            copiedId.value = statId;
            setTimeout(() => { copiedId.value = null; }, 2000);
        }, 'image/png');
    } catch (err) {

        toast.error('Gagal memuat data analitik.');
    }
};

const exportPDF = async () => {
    exportingPdf.value = true;
    try {
        const { default: jsPDF } = await import('jspdf');
        const { default: html2canvas } = await import('html2canvas-pro');

        const pdf = new jsPDF('p', 'mm', 'a4');
        const pageW = 210;
        const pageH = 297;
        const margin = 15;
        const contentW = pageW - margin * 2;
        let y = margin;

        // --- Header ---
        pdf.setFillColor(37, 99, 235);
        pdf.rect(0, 0, pageW, 38, 'F');
        pdf.setTextColor(255, 255, 255);
        pdf.setFontSize(18);
        pdf.setFont('helvetica', 'bold');
        pdf.text('LAPORAN ANALISIS KUESIONER', pageW / 2, 16, { align: 'center' });
        pdf.setFontSize(11);
        pdf.setFont('helvetica', 'normal');
        pdf.text(props.kuesioner.judul || 'Kuesioner', pageW / 2, 24, { align: 'center' });
        pdf.setFontSize(9);
        const now = new Date();
        pdf.text(`Diekspor: ${now.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })} • Total Responden: ${data.value?.total_responses || 0}`, pageW / 2, 32, { align: 'center' });

        y = 48;
        pdf.setTextColor(0, 0, 0);

        // --- Capture each chart card ---
        const cards = document.querySelectorAll('[data-chart-id]');

        for (const card of cards) {
            const el = card as HTMLElement;
            const canvas = await html2canvas(el, {
                scale: 2,
                backgroundColor: '#ffffff',
                useCORS: true,
                logging: false,
            });

            const imgData = canvas.toDataURL('image/png');
            const ratio = canvas.width / canvas.height;
            const imgW = contentW;
            const imgH = imgW / ratio;

            // Check if need new page
            if (y + imgH > pageH - margin) {
                pdf.addPage();
                y = margin;
            }

            pdf.addImage(imgData, 'PNG', margin, y, imgW, imgH);
            y += imgH + 8;
        }

        // --- Footer on last page ---
        pdf.setFontSize(8);
        pdf.setTextColor(150, 150, 150);
        pdf.text('Dokumen ini dibuat secara otomatis oleh Portal FMIKOM', pageW / 2, pageH - 8, { align: 'center' });

        // Add page numbers
        const totalPages = pdf.getNumberOfPages();
        for (let i = 1; i <= totalPages; i++) {
            pdf.setPage(i);
            pdf.setFontSize(8);
            pdf.setTextColor(150, 150, 150);
            pdf.text(`Halaman ${i} / ${totalPages}`, pageW - margin, pageH - 8, { align: 'right' });
        }

        const filename = `Laporan_${(props.kuesioner.judul || 'Kuesioner').replace(/[^a-zA-Z0-9]/g, '_')}_${now.toISOString().slice(0, 10)}.pdf`;
        pdf.save(filename);
    } catch (err) {

        toast.error('Gagal memuat data analitik.');
    } finally {
        exportingPdf.value = false;
    }
};

const goBack = () => { router.get('/trace/admin/questionnaires'); };

const breadcrumbs = [
    { title: 'Manajemen Kuesioner', href: '/trace/admin/questionnaires' },
    { title: 'Analitik', href: '#' },
];

onMounted(() => {
    fetchAnalytics();
    fetchLiveStats();
    refreshInterval = setInterval(fetchLiveStats, 30000); // 30 seconds
});

onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval);
});

const filteredSections = computed(() => {
    if (!data.value?.sections) return [];
    if (selectedSection.value === 'all') return data.value.sections;
    return data.value.sections.filter((s: KuesionerSection) => s.id.toString() === selectedSection.value);
});

const filteredCategories = computed(() => {
    if (!data.value?.categories) return [];
    if (selectedSection.value === 'all') return data.value.categories;
    return data.value.categories
        .map((cat: { name: string; statistics: Array<{ section_id?: number; [key: string]: unknown }> }) => ({ ...cat, statistics: cat.statistics.filter((stat: { section_id?: number; [key: string]: unknown }) => stat.section_id?.toString() === selectedSection.value) }))
        .filter((cat: { name: string; statistics: unknown[] }) => cat.statistics.length > 0);
});

const normalizeIndicatorLabels = (indicator: unknown): string[] => {
    if (!indicator) return [];

    const values = Array.isArray(indicator) ? indicator : [indicator];

    return values
        .map((item) => {
            if (typeof item === 'string') return item;
            if (item && typeof item === 'object') {
                const record = item as Record<string, unknown>;
                return (record.nama || record.label || record.kode || record.value || '') as string;
            }
            return '';
        })
        .filter(Boolean)
        .map((item) =>
            item
                .replace(/_/g, ' ')
                .replace(/\b\w/g, (char) => char.toUpperCase()),
        );
};

const getChartData = (stat: Record<string, unknown>) => {
    const analysis = stat.analysis as Record<string, unknown>;
    if (analysis.distribution) {
        const dist = analysis.distribution as Array<{ label: string; count: number }>;
        return {
            labels: dist.map((d: { label: string; count: number }) => d.label),
            datasets: [{ label: 'Jumlah Responden', backgroundColor: ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#6366f1'], data: dist.map((d: { label: string; count: number }) => d.count), borderRadius: 8 }],
        };
    }
    return { labels: [], datasets: [] };
};

const getScaleBarData = (stat: Record<string, unknown>) => {
    const analysis = stat.analysis as Record<string, unknown>;
    if (analysis.distribution) {
        const dist = analysis.distribution as Array<{ label: string; count: number }>;
        const colors = ['#ef4444','#f97316','#eab308','#22c55e','#3b82f6','#8b5cf6','#ec4899','#06b6d4','#14b8a6','#84cc16'];
        return {
            labels: dist.map((d: { label: string; count: number }) => d.label),
            datasets: [{ label: 'Jumlah', backgroundColor: dist.map((_: unknown, i: number) => colors[i % colors.length]), data: dist.map((d: { label: string; count: number }) => d.count), borderRadius: 6, barThickness: 28 }],
        };
    }
    return { labels: [], datasets: [] };
};

const chartOptions = {
    responsive: true, maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom' as const, labels: { usePointStyle: true, padding: 20, font: { size: 10, weight: 'bold' as const } } },
        datalabels: {
            color: '#fff',
            font: { weight: 'bold' as const, size: 12 },
            formatter: (value: number, ctx: Record<string, unknown>) => {
                const total = ctx.dataset.data.reduce((a: number, b: number) => a + b, 0);
                const pct = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                return +pct >= 5 ? `${pct}%` : '';
            },
        },
    },
};

const scaleBarOptions = {
    indexAxis: 'y' as const, responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false }, datalabels: { display: false } },
    scales: {
        x: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11, weight: 'bold' as const } }, grid: { display: false } },
        y: { ticks: { font: { size: 12, weight: 'bold' as const } }, grid: { display: false } },
    },
};

const getRadarOptions = (stat: Record<string, unknown>) => ({
    responsive: true, maintainAspectRatio: false,
    scales: { r: { angleLines: { display: true }, suggestedMin: 0, suggestedMax: (stat.analysis as Record<string, unknown>)?.scale_max || 5, ticks: { stepSize: 1, font: { size: 9 } }, pointLabels: { font: { size: 11, weight: 'bold' as const } } } },
    plugins: { legend: { position: 'bottom' as const }, datalabels: { display: false } },
});
</script>

<template>
    <TraceAdminLayout :breadcrumbs="breadcrumbs">
    <div class="min-h-screen bg-slate-50/50 p-6 dark:bg-slate-950/50">
        <div class="mx-auto max-w-7xl">
            <!-- Header with Back Button -->
            <div class="mb-8">
                <TPageHeader :title="kuesioner.judul" :description="kuesioner.subtitle || 'Analisis Hasil Kuesioner'" :icon="BarChart3">
                    <template #actions>
                        <Button
                            variant="outline"
                            size="icon"
                            class="h-10 w-10 rounded-xl"
                            @click="goBack"
                        >
                            <ArrowLeft class="h-4 w-4" />
                        </Button>
                    </template>
                </TPageHeader>
            </div>

            <!-- Live Stats Bar -->
            <div v-if="liveStats" class="flex flex-wrap items-center gap-4 rounded-2xl bg-white p-4 border border-slate-100 shadow-sm dark:bg-zinc-900 dark:border-zinc-800 mb-4">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    </span>
                    <span class="text-xs font-bold text-slate-600 dark:text-zinc-300">Live</span>
                </div>
                <div class="flex items-center gap-1.5 text-xs">
                    <span class="font-black text-slate-800 dark:text-white text-lg">{{ liveStats.total_responses }}</span>
                    <span class="text-slate-400">total responden</span>
                </div>
                <div class="h-4 w-px bg-slate-200 dark:bg-zinc-700"></div>
                <div class="flex items-center gap-1.5 text-xs">
                    <span class="font-bold text-emerald-600">+{{ liveStats.today_responses }}</span>
                    <span class="text-slate-400">hari ini</span>
                </div>
                <div class="ml-auto flex items-center gap-2">
                    <span v-if="liveStats.cache_status === 'cached'" class="text-[10px] font-bold text-amber-500 bg-amber-50 dark:bg-amber-950/30 px-2 py-0.5 rounded-full">Cached</span>
                    <span v-else class="text-[10px] font-bold text-emerald-500 bg-emerald-50 dark:bg-emerald-950/30 px-2 py-0.5 rounded-full">Fresh</span>
                    <button
                        class="flex items-center gap-1.5 rounded-lg bg-[#0C447C]/10 px-2.5 py-1 text-[11px] font-bold text-[#0C447C] transition-all hover:bg-[#85B7EB]/20 dark:bg-[#0C447C]/20 dark:text-[#85B7EB] dark:hover:bg-[#0C447C]/30"
                        :disabled="refreshingLive"
                        @click="hardRefresh"
                    >
                        <RefreshCw class="h-3 w-3" :class="{ 'animate-spin': refreshingLive }" />
                        Refresh
                    </button>
                </div>
            </div>

            <!-- Analytics Content -->
            <div class="space-y-8">
                <!-- Action Bar & Filters -->
                <div
                    class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#0C447C] text-white shadow-lg shadow-[#0C447C]/20"
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
                                    class="rounded-lg border border-[#85B7EB] bg-[#0C447C]/10 font-bold text-[#0C447C] dark:border-[#0C447C] dark:bg-[#0C447C]/20 dark:text-[#85B7EB]"
                                >
                                    {{ data.total_responses }} Responden
                                </Badge>
                            </div>
                            <p
                                class="text-xs font-medium text-muted-foreground"
                            >
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
                            <Loader2
                                v-if="exporting"
                                class="h-4 w-4 animate-spin"
                            />
                            <Download v-else class="h-4 w-4 text-[#0C447C]" />
                            Export Excel
                        </Button>
                        <Button
                            variant="outline"
                            class="rounded-xl border-red-200 bg-white font-bold text-red-700 shadow-sm transition-all hover:bg-red-50 hover:shadow-md dark:border-red-900/50 dark:bg-slate-900 dark:text-red-400"
                            :disabled="exportingPdf"
                            @click="exportPDF"
                        >
                            <Loader2
                                v-if="exportingPdf"
                                class="h-4 w-4 animate-spin"
                            />
                            <FileText v-else class="h-4 w-4 text-red-600" />
                            Export PDF
                        </Button>
                    </div>
                </div>

                <div class="w-full space-y-6">
                    <div
                        class="flex items-center justify-between border-b pb-1"
                    >
                        <div class="flex h-auto gap-8 bg-transparent p-0">
                            <button
                                @click="activeView = 'overview'"
                                :class="[
                                    'relative h-10 rounded-none border-b-2 bg-transparent px-2 pb-2 text-sm font-bold transition-all',
                                    activeView === 'overview'
                                        ? 'border-[#0C447C] text-[#0C447C]'
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
                                        ? 'border-[#0C447C] text-[#0C447C]'
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
                                <SelectContent
                                    class="rounded-xl border-none shadow-xl"
                                >
                                    <SelectItem value="all"
                                        >Semua Halaman</SelectItem
                                    >
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
                            <Loader2
                                class="h-10 w-10 animate-spin text-[#0C447C]"
                            />
                            <p
                                class="mt-4 text-sm font-medium text-muted-foreground"
                            >
                                Menganalisis data...
                            </p>
                        </div>

                        <div
                            v-else-if="error"
                            class="flex flex-col items-center justify-center rounded-xl border border-red-200 bg-red-50/50 p-6 py-20 dark:border-red-900/50 dark:bg-red-950/20"
                        >
                            <p
                                class="text-sm font-medium text-red-700 dark:text-red-400"
                            >
                                {{ error }}
                            </p>
                        </div>

                        <template
                            v-else-if="data && filteredCategories.length > 0"
                        >
                            <!-- No Responses Warning -->
                            <div
                                v-if="!data.has_responses"
                                class="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50/50 p-4 dark:border-amber-900/50 dark:bg-amber-950/20"
                            >
                                <AlertCircle
                                    class="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-600 dark:text-amber-400"
                                />
                                <div class="text-sm">
                                    <p
                                        class="font-medium text-amber-900 dark:text-amber-100"
                                    >
                                        Belum ada responden yang mengisi
                                        kuesioner
                                    </p>
                                    <p
                                        class="mt-1 text-xs text-amber-700 dark:text-amber-300"
                                    >
                                        Struktur pertanyaan ditampilkan di
                                        bawah. Data analitik akan muncul setelah
                                        ada responden.
                                    </p>
                                </div>
                            </div>

                            <!-- Radar Chart for Competency -->
                            <Card
                                v-if="data.radar_data"
                                class="overflow-hidden border border-slate-100 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
                            >
                                <CardHeader class="pb-2">
                                    <div class="flex items-center gap-2">
                                        <Award class="h-4 w-4 text-[#0C447C]" />
                                        <CardTitle class="text-sm font-bold"
                                            >Analisis Kompetensi Lulusan
                                            (CPL)</CardTitle
                                        >
                                    </div>
                                    <CardDescription class="text-xs"
                                        >Profil kemampuan alumni berdasarkan
                                        standar kompetensi
                                        prodi</CardDescription
                                    >
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
                                        class="rounded-lg bg-[#0C447C] px-3 py-1 text-[10px] font-black tracking-widest text-white uppercase"
                                    >
                                        Kategori: {{ category.name }}
                                    </Badge>
                                    <div
                                        class="h-px flex-1 bg-slate-200 dark:bg-slate-800"
                                    ></div>
                                </div>

                                <div
                                    class="grid grid-cols-1 gap-6 lg:grid-cols-2"
                                >
                                    <Card
                                        v-for="stat in category.statistics"
                                        :key="stat.question_id"
                                        :data-chart-id="stat.question_id"
                                        class="group overflow-hidden border border-slate-100 shadow-sm transition-all hover:shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
                                    >
                                        <CardHeader
                                            class="border-b bg-slate-50/50 pb-5 dark:bg-slate-900/50"
                                        >
                                            <div class="flex flex-col gap-3">
                                                <div
                                                    class="flex flex-wrap items-center gap-2"
                                                >
                                                    <!-- Acuan & Indikator Badges -->
                                                    <template v-if="stat.acuan">
                                                        <template
                                                            v-if="
                                                                Array.isArray(
                                                                    stat.acuan,
                                                                )
                                                            "
                                                        >
                                                            <Badge
                                                                v-for="ref in stat.acuan"
                                                                :key="ref"
                                                                variant="outline"
                                                                class="rounded-lg border-slate-200 bg-white px-2 py-0.5 text-[9px] font-bold text-slate-500 uppercase dark:border-slate-700 dark:bg-slate-900"
                                                            >
                                                                {{ ref }}
                                                            </Badge>
                                                        </template>
                                                        <template v-else>
                                                            <Badge
                                                                variant="outline"
                                                                class="rounded-lg border-slate-200 bg-white px-2 py-0.5 text-[9px] font-bold text-slate-500 uppercase dark:border-slate-700 dark:bg-slate-900"
                                                            >
                                                                {{ stat.acuan }}
                                                            </Badge>
                                                        </template>
                                                    </template>

                                                    <Badge
                                                        v-for="indicator in normalizeIndicatorLabels(stat.indikator)"
                                                        :key="indicator"
                                                        class="rounded-lg border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[9px] font-bold text-emerald-700 uppercase hover:bg-emerald-100 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300"
                                                    >
                                                        {{ indicator }}
                                                    </Badge>
                                                </div>

                                                <div class="flex items-start justify-between gap-2">
                                                    <div>
                                                        <CardTitle
                                                            class="text-sm leading-snug font-black text-slate-800 dark:text-slate-100"
                                                        >
                                                            {{ stat.teks }}
                                                        </CardTitle>
                                                        <p class="mt-1 text-[10px] font-semibold text-slate-400">
                                                            {{ stat.analysis.total_responses || data?.total_responses || 0 }} responden
                                                        </p>
                                                    </div>
                                                    <button
                                                        v-if="stat.tipe !== 'text' && stat.tipe !== 'number'"
                                                        class="flex-shrink-0 rounded-lg p-1.5 transition-all"
                                                        :class="copiedId === stat.question_id
                                                            ? 'bg-green-50 text-green-600 dark:bg-green-950/40'
                                                            : 'text-slate-400 hover:bg-[#85B7EB]/10 hover:text-[#0C447C] dark:hover:bg-[#0C447C]/20 dark:hover:text-[#85B7EB]'"
                                                        :title="copiedId === stat.question_id ? 'Tersalin!' : 'Salin Diagram'"
                                                        @click="copyChart(stat.question_id)"
                                                    >
                                                        <Check v-if="copiedId === stat.question_id" class="h-4 w-4" />
                                                        <Copy v-else class="h-4 w-4" />
                                                    </button>
                                                </div>
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
                                                <div
                                                    v-if="stat.analysis.average !== undefined && stat.analysis.average !== null"
                                                    class="flex items-center justify-between rounded-2xl bg-emerald-50 px-4 py-3 dark:bg-emerald-950/20"
                                                >
                                                    <span class="text-[10px] font-black tracking-widest text-emerald-700 uppercase dark:text-emerald-300">Skor Rata-rata</span>
                                                    <span class="text-lg font-black text-emerald-700 dark:text-emerald-300">{{ stat.analysis.average }}</span>
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
                                                <div class="flex items-center justify-between rounded-2xl bg-gradient-to-r from-[#0C447C]/5 to-[#85B7EB]/10 p-5 dark:from-[#0C447C]/20 dark:to-[#85B7EB]/10">
                                                    <div>
                                                        <div class="text-[10px] font-black tracking-widest text-slate-400 uppercase">Skor Rata-rata</div>
                                                        <div class="mt-1 flex items-baseline gap-1">
                                                            <span class="text-3xl font-black tracking-tighter text-[#0C447C] dark:text-[#85B7EB]">{{ stat.analysis.average }}</span>
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
                                                    <span class="text-4xl font-black tracking-tighter text-[#0C447C] dark:text-[#85B7EB]">{{ stat.analysis.average }}</span>
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
                                                                <span class="text-xs font-black text-[#0C447C] dark:text-[#85B7EB]">{{ row.average }} / {{ stat.analysis.scale_max || 5 }}</span>
                                                            </div>
                                                            <div class="h-2 w-full rounded-full bg-slate-200 dark:bg-slate-700">
                                                                <div
                                                                    class="h-2 rounded-full bg-gradient-to-r from-[#0C447C] to-[#85B7EB] transition-all duration-500"
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
                                                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#85B7EB]/20 text-[9px] font-black text-[#0C447C] dark:bg-[#0C447C]/20 dark:text-[#85B7EB]">{{ ci + 1 }}</span>
                                                            <span class="text-[10px] font-medium text-slate-600 dark:text-slate-400">{{ col }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Identification / Text Questions -->
                                            <div
                                                v-if="
                                                    stat.analysis.recent_responses &&
                                                    stat.analysis.recent_responses.length > 0
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
                                                        {{ stat.analysis.total_responses }} Respon
                                                    </Badge>
                                                </div>

                                                <div
                                                    class="custom-scrollbar max-h-[220px] space-y-2 overflow-y-auto pr-2"
                                                >
                                                    <div
                                                        v-for="(resp, idx) in stat.analysis.recent_responses"
                                                        :key="idx"
                                                        class="rounded-xl bg-slate-50/80 p-3 text-xs font-medium text-slate-600 transition-colors hover:bg-[#85B7EB]/10 dark:bg-slate-800/40 dark:text-slate-300"
                                                    >
                                                        {{ resp }}
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Empty State -->
                                            <div
                                                v-if="
                                                    (!stat.analysis.distribution ||
                                                        stat.analysis.distribution.length === 0) &&
                                                    !stat.analysis.radar_data &&
                                                    stat.analysis.average === undefined &&
                                                    (!stat.analysis.recent_responses ||
                                                        stat.analysis.recent_responses.length === 0)
                                                "
                                                class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/30 p-8 text-center dark:border-slate-800 dark:bg-slate-800/10"
                                            >
                                                <p
                                                    class="text-xs font-medium text-slate-400"
                                                >
                                                    Belum ada respon untuk pertanyaan ini.
                                                </p>
                                            </div>
                                        </CardContent>
                                    </Card>
                                </div>
                            </div>
                        </template>

                        <div
                            v-else
                            class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-8 py-20 dark:border-slate-800 dark:bg-slate-950/50"
                        >
                            <p
                                class="text-sm font-medium text-slate-600 dark:text-slate-400"
                            >
                                Belum ada data analitik untuk kuesioner ini
                            </p>
                            <p
                                class="mt-2 text-xs text-slate-500 dark:text-slate-500"
                            >
                                Pastikan ada responden yang telah mengisi
                                kuesioner
                            </p>
                        </div>
                    </div>
                    <div
                        v-if="activeView === 'respondents'"
                        class="mt-0 animate-in duration-500 fade-in"
                    >
                        <RespondentList :kuesioner-id="kuesionerId" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    </TraceAdminLayout>
</template>

<style scoped>
.font-black {
    font-weight: 900;
}
</style>
