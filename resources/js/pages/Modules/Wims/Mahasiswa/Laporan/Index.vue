<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import StudentLayout from '@/layouts/Modules/Wims/Mahasiswa/Layout.vue';
import wimsRoutes from '@/routes/wims';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Award,
    CheckCircle2,
    ClipboardCheck,
    Clock,
    Download,
    Eye,
    FileText,
    NotebookPen,
    Star,
    TrendingUp,
    Upload,
    User,
    X,
} from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';

defineOptions({
    layout: StudentLayout,
});

type PageState = 'not_registered' | 'waiting' | 'active' | 'completed';

type RegistrationProps = {
    status?: string | null;
    company?: {
        proposal?: {
            name?: string | null;
        } | null;
        final?: {
            id?: number | null;
            name?: string | null;
        } | null;
    } | null;
    lecturer?: {
        name?: string | null;
    } | null;
    mentor?: {
        name?: string | null;
    } | null;
    period_label?: string | null;
    submitted_at?: string | null;
    laporan_akhir?: {
        name?: string | null;
        view_url?: string | null;
        download_url?: string | null;
        uploaded_at?: string | null;
    } | null;
} | null;

type InternshipProps = {
    progress_percentage?: number | null;
    completed_days?: number | null;
    total_days?: number | null;
    remaining_days?: number | null;
    // Fitur 4: Statistik baru
    total_logbook_entries?: number | null;
    total_hadir?: number | null;
    total_izin?: number | null;
    total_sakit?: number | null;
};

type EvaluationProps = {
    status_key?: string | null;
    status_label?: string | null;
    is_complete?: boolean;
    finalized_at?: string | null;
    catatan_dosen?: string | null;
    dosen_score?: number | null;
    mitra_score?: number | null;
};

// Fitur 6: Tipe untuk riwayat aktivitas
type ActivityLog = {
    label: string;
    timestamp: string;
    type: 'upload' | 'penilaian' | 'status' | 'logbook';
};

type PageProps = {
    flash?: {
        success?: string;
    };
};

type AttendanceHistoryItem = {
    id: number | string;
    tanggal?: string | null;
    tanggal_label?: string | null;
    check_in?: string | null;
    check_out?: string | null;
    check_in_photo_url?: string | null;
    check_out_photo_url?: string | null;
    status?: string | null;
    keterangan?: string | null;
};

type LogbookHistoryPhoto = {
    id: number | string;
    url?: string | null;
};

type LogbookHistoryItem = {
    id: number | string;
    tanggal?: string | null;
    tanggal_label?: string | null;
    jam_mulai?: string | null;
    jam_selesai?: string | null;
    aktivitas?: string | null;
    kompetensi?: string | null;
    status?: string | null;
    catatan_mitra?: string | null;
    photos?: LogbookHistoryPhoto[];
};

const props = withDefaults(
    defineProps<{
        pageState: PageState;
        registration?: RegistrationProps;
        internship?: InternshipProps;
        evaluation?: EvaluationProps;
        history?: {
            attendance?: AttendanceHistoryItem[];
            logbook?: LogbookHistoryItem[];
        };
currentPeriodHistoryDownloadUrl?: string | null;
        currentPeriodLogbookDownloadUrl?: string | null;
        final_report_template?: {
            id?: number | string;
            title?: string | null;
            description?: string | null;
            original_name?: string | null;
            mime_type?: string | null;
            updated_at?: string | null;
            download_url?: string | null;
        } | null;
        activityLogs?: ActivityLog[];
        periods?: PeriodOption[];
        selected_period_id?: number | string | null;
    }>(),
    {
        registration: null,
        internship: () => ({
            progress_percentage: 0,
            completed_days: null,
            total_days: null,
            remaining_days: null,
            total_logbook_entries: null,
            total_hadir: null,
            total_izin: null,
            total_sakit: null,
        }),
        evaluation: () => ({
            status_key: 'not_assessed',
            status_label: 'Belum Dinilai',
            is_complete: false,
            finalized_at: null,
            catatan_dosen: null,
            dosen_score: null,
            mitra_score: null,
        }),
        history: () => ({
            attendance: [],
            logbook: [],
        }),
        currentPeriodHistoryDownloadUrl: null,
        currentPeriodLogbookDownloadUrl: null,
        activityLogs: () => [],
    },
);

const page = usePage<PageProps>();
const selectedPeriodId = computed(() => (props.selected_period_id != null ? String(props.selected_period_id) : ''));

const withSelectedPeriod = (href: string) => {
    if (!selectedPeriodId.value) {
        return href;
    }

    const url = new URL(href, window.location.origin);
    url.searchParams.set('pendaftaran', selectedPeriodId.value);
    return url.pathname + url.search + url.hash;
};

const reportForm = useForm({
    laporan_akhir: null as File | null,
});

// Fitur 3: State untuk drag & drop dan preview file
const isDragging = ref(false);
const filePreview = ref<{ name: string; size: string; type: string } | null>(null);

const registrationHref = computed(() => withSelectedPeriod(wimsRoutes.registration().url));
const progressPercentage = computed(() =>
    Number(props.internship.progress_percentage ?? 0),
);
const flash = computed(() => page.props.flash ?? {});
const canUploadFinalReport = computed(() => props.pageState === 'completed');
const hasCurrentPeriodHistoryDownload = computed(() =>
    Boolean(props.currentPeriodHistoryDownloadUrl),
);
const hasCurrentPeriodLogbookDownload = computed(() =>
    Boolean(props.currentPeriodLogbookDownloadUrl),
);
const attendanceHistory = computed(() => props.history?.attendance ?? []);
const logbookHistory = computed(() => props.history?.logbook ?? []);
const attendanceHistoryDialogOpen = ref(false);
const logbookHistoryDialogOpen = ref(false);
const evaluationStatusKey = computed(() => props.evaluation.status_key ?? 'not_assessed');

const statusLabel = computed(() => {
    if (props.registration?.status === 'selesai') return 'Selesai';
    if (props.registration?.status === 'aktif') return 'Aktif';
    if (props.registration?.status === 'approved') return 'Approved';
    if (props.registration?.status === 'revisi') return 'Revisi';
    if (props.registration?.status === 'rejected') return 'Rejected';
    if (props.registration?.status === 'pending') return 'Pending';
    return 'Belum Mengajukan';
});

const statusClasses = computed(() => {
    if (props.registration?.status === 'selesai')
        return 'border-violet-200 bg-violet-50 dark:bg-violet-500/15 text-violet-700 dark:text-violet-300';
    if (props.registration?.status === 'aktif')
        return 'border-sky-200 bg-sky-50 dark:bg-sky-500/15 text-sky-700 dark:text-sky-300';
    if (props.registration?.status === 'approved')
        return 'border-emerald-200 bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300';
    if (props.registration?.status === 'revisi')
        return 'border-amber-200 bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300';
    if (props.registration?.status === 'rejected')
        return 'border-rose-200 bg-rose-50 dark:bg-rose-500/15 text-rose-700 dark:text-rose-300';
    if (props.registration?.status === 'pending')
        return 'border-blue-200 bg-blue-50 dark:bg-blue-500/15 text-[#2563EB]';
    return 'border-wims-border bg-slate-50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400';
});

const evaluationStatusLabel = computed(() => {
    return props.evaluation.status_label ?? 'Belum Dinilai';
});

const evaluationStatusClasses = computed(() => {
    if (evaluationStatusKey.value === 'submitted')
        return 'border-emerald-200 bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300';
    if (['final_dosen', 'final_mitra'].includes(evaluationStatusKey.value))
        return 'border-blue-200 bg-blue-50 dark:bg-blue-500/15 text-[#2563EB]';
    if (evaluationStatusKey.value === 'draft')
        return 'border-amber-200 bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300';
    return 'border-wims-border bg-slate-50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400';
});

// --- FITUR 1: Timeline Steps --------------------------------------------------
const timelineSteps = computed(() => [
    {
        key: 'registered',
        label: 'Pendaftaran',
        desc: 'Pengajuan PKL dikirim',
        done: props.pageState !== 'not_registered',
        active: props.pageState === 'waiting',
    },
    {
        key: 'approved',
        label: 'Disetujui',
        desc: 'Penempatan dikonfirmasi',
        done: props.pageState === 'active' || props.pageState === 'completed',
        active: false,
    },
    {
        key: 'active',
        label: 'PKL Aktif',
        desc: 'Magang sedang berjalan',
        done: props.pageState === 'completed',
        active: props.pageState === 'active',
    },
    {
        key: 'laporan',
        label: 'Laporan Akhir',
        desc: 'Unggah dokumen laporan',
        done: props.pageState === 'completed' && Boolean(props.registration?.laporan_akhir),
        active: props.pageState === 'completed' && !props.registration?.laporan_akhir,
    },
    {
        key: 'penilaian',
        label: 'Penilaian',
        desc: 'Dosen dan mitra mengirim nilai',
        done: Boolean(props.evaluation?.is_complete),
        active: ['final_dosen', 'final_mitra', 'draft'].includes(evaluationStatusKey.value),
    },
]);

// --- FITUR 2: Checklist Kelengkapan -------------------------------------------
const checklistItems = computed(() => [
    {
        label: 'Dosen pembimbing sudah ditetapkan',
        done: isAssignmentConfirmed.value && Boolean(props.registration?.lecturer?.name),
    },
    {
        label: 'Pembimbing lapangan mitra sudah ditetapkan',
        done: isAssignmentConfirmed.value && Boolean(props.registration?.mentor?.name),
    },
    {
        label: 'Laporan akhir siap dinilai',
        done: Boolean(props.registration?.laporan_akhir),
    },
    {
        label: 'Dokumen laporan akhir sudah diunggah',
        done: Boolean(props.registration?.laporan_akhir),
    },
    {
        label: 'Penilaian dosen sudah dikirim',
        done: props.evaluation?.dosen_score !== null && props.evaluation?.dosen_score !== undefined,
    },
    {
        label: 'Penilaian mitra sudah dikirim',
        done: props.evaluation?.mitra_score !== null && props.evaluation?.mitra_score !== undefined,
    },
]);

const checklistDoneCount = computed(
    () => checklistItems.value.filter((i) => i.done).length,
);
const checklistPercent = computed(
    () => Math.round((checklistDoneCount.value / checklistItems.value.length) * 100),
);

const proposalCompanyLabel = computed(
    () => props.registration?.company?.proposal?.name ?? 'Belum ada usulan',
);
const currentCompanyLabel = computed(
    () =>
        props.registration?.company?.final?.name ??
        props.registration?.company?.proposal?.name ??
        'Belum tersedia',
);
const lecturerLabel = computed(
    () => props.registration?.lecturer?.name ?? 'Belum ditetapkan',
);
const mentorLabel = computed(
    () => props.registration?.mentor?.name ?? 'Belum ditetapkan',
);

const isAssignmentConfirmed = computed(() =>
    ['approved', 'aktif', 'selesai'].includes(props.registration?.status ?? ''),
);

// --- FITUR 5: Konversi Nilai ke Huruf -----------------------------------------
const getNilaiHuruf = (score: number | null | undefined) => {
    if (score === null || score === undefined) return null;
    if (score >= 85) return { huruf: 'A', label: 'Sangat Baik', color: 'text-emerald-600 dark:text-emerald-400', bg: 'bg-emerald-50 dark:bg-emerald-500/15', border: 'border-emerald-200' };
    if (score >= 75) return { huruf: 'B', label: 'Baik', color: 'text-blue-600 dark:text-blue-400', bg: 'bg-blue-50 dark:bg-blue-500/15', border: 'border-blue-200' };
    if (score >= 65) return { huruf: 'C', label: 'Cukup', color: 'text-amber-600 dark:text-amber-400', bg: 'bg-amber-50 dark:bg-amber-500/15', border: 'border-amber-200' };
    if (score >= 55) return { huruf: 'D', label: 'Kurang', color: 'text-orange-600 dark:text-orange-400', bg: 'bg-orange-50 dark:bg-orange-500/15', border: 'border-orange-200' };
    return { huruf: 'E', label: 'Tidak Lulus', color: 'text-rose-600 dark:text-rose-400', bg: 'bg-rose-50 dark:bg-rose-500/15', border: 'border-rose-200' };
};

const getNilaiBarColor = (score: number | null | undefined) => {
    if (!score) return 'bg-slate-300';
    if (score >= 85) return 'bg-emerald-500';
    if (score >= 75) return 'bg-blue-500';
    if (score >= 65) return 'bg-amber-500';
    if (score >= 55) return 'bg-orange-500';
    return 'bg-rose-500';
};

const hasDosenScore = computed(
    () => props.evaluation?.dosen_score !== null && props.evaluation?.dosen_score !== undefined,
);
const hasMitraScore = computed(
    () => props.evaluation?.mitra_score !== null && props.evaluation?.mitra_score !== undefined,
);
const hasAnyEvaluationScore = computed(
    () => hasDosenScore.value || hasMitraScore.value,
);

const nilaiHurufDosen = computed(() => getNilaiHuruf(props.evaluation?.dosen_score));
const nilaiHurufMitra = computed(() => getNilaiHuruf(props.evaluation?.mitra_score));
const nilaiBarWidthDosen = computed(() => Math.min(100, Number(props.evaluation?.dosen_score ?? 0)));
const nilaiBarWidthMitra = computed(() => Math.min(100, Number(props.evaluation?.mitra_score ?? 0)));
const nilaiBarColorDosen = computed(() => getNilaiBarColor(props.evaluation?.dosen_score));
const nilaiBarColorMitra = computed(() => getNilaiBarColor(props.evaluation?.mitra_score));

// --- FITUR 3: File Preview & Drag-and-Drop ------------------------------------
const formatFileSize = (bytes: number): string => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
};

const handleReportChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;
    reportForm.laporan_akhir = file;
    if (file) {
        filePreview.value = {
            name: file.name,
            size: formatFileSize(file.size),
            type: file.type || 'application/octet-stream',
        };
    } else {
        filePreview.value = null;
    }
};

const handleDrop = (event: DragEvent) => {
    isDragging.value = false;
    const file = event.dataTransfer?.files?.[0] ?? null;
    if (!file) return;
    const allowed = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];
    if (!allowed.includes(file.type)) return;
    reportForm.laporan_akhir = file;
    filePreview.value = {
        name: file.name,
        size: formatFileSize(file.size),
        type: file.type,
    };
};

const clearFilePreview = () => {
    reportForm.laporan_akhir = null;
    filePreview.value = null;
};

const submitReport = () => {
    if (!canUploadFinalReport.value || !reportForm.laporan_akhir) return;
    reportForm.post('/wims/laporan', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            reportForm.reset('laporan_akhir');
            filePreview.value = null;
        },
    });
};

const downloadCurrentPeriodAttendanceHistory = () => {
    if (!props.currentPeriodHistoryDownloadUrl) return;
    window.location.href = props.currentPeriodHistoryDownloadUrl;
};

const downloadCurrentPeriodLogbook = () => {
    if (!props.currentPeriodLogbookDownloadUrl) return;
    window.location.href = props.currentPeriodLogbookDownloadUrl;
};

const formatDateUi = (value?: string | null) => {
    if (!value) return '-';

    const parsed = new Date(value);

    if (Number.isNaN(parsed.getTime())) {
        return value;
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(parsed);
};

const formatTimeUi = (value?: string | null) => {
    if (!value) return '-';

    return value.slice(0, 5);
};

const attendanceStatusLabel = (value?: string | null) => {
    if (value === 'hadir' || value === 'tepat_waktu') return 'Hadir';
    if (value === 'terlambat') return 'Terlambat';
    if (value === 'izin') return 'Izin';
    if (value === 'sakit') return 'Sakit';
    if (value === 'alfa') return 'Alfa';

    return value ? value.replace(/_/g, ' ') : 'Belum ada data';
};

const attendanceStatusClass = (value?: string | null) => {
    if (value === 'hadir' || value === 'tepat_waktu') return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    if (value === 'terlambat') return 'border-amber-200 bg-amber-50 text-amber-700';
    if (value === 'izin') return 'border-blue-200 bg-blue-50 text-blue-700';
    if (value === 'sakit') return 'border-violet-200 bg-violet-50 text-violet-700';
    if (value === 'alfa') return 'border-rose-200 bg-rose-50 text-rose-700';

    return 'border-wims-border bg-slate-50 text-slate-600';
};

const logbookStatusLabel = (value?: string | null) => {
    if (value === 'approved' || value === 'disetujui') return 'Disetujui';
    if (value === 'revisi') return 'Revisi';
    if (value === 'rejected') return 'Ditolak';

    return 'Menunggu Review';
};

const logbookStatusClass = (value?: string | null) => {
    if (value === 'approved' || value === 'disetujui') return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    if (value === 'revisi') return 'border-amber-200 bg-amber-50 text-amber-700';
    if (value === 'rejected') return 'border-rose-200 bg-rose-50 text-rose-700';

    return 'border-blue-200 bg-blue-50 text-blue-700';
};
// --- NEW ELEMENT: PKL Completion Score ---------------------------------------
const completionScore = computed(() => {
    const logbookPercent = Math.min(100, Math.round(((props.internship?.total_logbook_entries ?? 0) / Math.max(1, props.internship?.completed_days ?? 1)) * 100));
    const attendancePercent = Math.min(100, Math.round(((props.internship?.total_hadir ?? 0) / Math.max(1, props.internship?.completed_days ?? 1)) * 100));
    const items = [
        { label: 'Progress', percent: progressPercentage.value, color: 'text-blue-500' },
        { label: 'Presensi', percent: attendancePercent, color: 'text-cyan-500' },
        { label: 'Logbook', percent: logbookPercent, color: 'text-violet-500' },
        { label: 'Laporan', percent: props.registration?.laporan_akhir ? 100 : 0, color: 'text-emerald-500' },
        {
            label: 'Penilaian',
            percent: props.evaluation?.is_complete
                ? 100
                : props.evaluation?.dosen_score !== null && props.evaluation?.dosen_score !== undefined
                  ? 50
                  : 0,
            color: 'text-amber-500',
        },
    ];
    const overall = Math.round(items.reduce((sum, i) => sum + i.percent, 0) / items.length);
    return { items, overall };
});
</script>

<template>
    <Head title="Laporan Akhir" />

    <div class="min-h-full">
        <!-- Animated background decorations -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-32 -right-32 h-[500px] w-[500px] rounded-full bg-gradient-to-br from-blue-400/[0.06] to-cyan-400/[0.04] blur-3xl animate-pulse dark:from-blue-500/[0.04] dark:to-cyan-400/[0.025]" style="animation-duration: 7s;" />
            <div class="absolute top-1/3 -left-16 h-[400px] w-[400px] rounded-full bg-gradient-to-tr from-indigo-400/[0.05] to-violet-400/[0.03] blur-3xl animate-pulse dark:from-blue-600/[0.03] dark:to-indigo-500/[0.02]" style="animation-duration: 10s; animation-delay: 3s;" />
            <div class="absolute -bottom-16 right-1/3 h-[350px] w-[350px] rounded-full bg-gradient-to-tl from-cyan-400/[0.04] to-blue-400/[0.03] blur-3xl animate-pulse dark:from-cyan-400/[0.025] dark:to-blue-500/[0.02]" style="animation-duration: 12s; animation-delay: 5s;" />
            <div class="absolute inset-0 opacity-[0.02] dark:opacity-[0.04]" style="background-image: radial-gradient(circle, currentColor 0.5px, transparent 0.5px); background-size: 24px 24px;" />
        </div>

        <div class="relative space-y-4 lg:space-y-5">

            <!-- Hero Section -->
            <section class="relative overflow-hidden rounded-2xl lg:rounded-3xl">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-600 via-blue-500 to-cyan-500 dark:from-[#1D3E8A] dark:via-[#1F5FCC] dark:to-[#0E7ACF]" />
                <div class="absolute inset-0 bg-gradient-to-t from-blue-700/30 via-transparent to-transparent dark:from-[#07152f]/55 dark:via-[#0f172a]/10 dark:to-transparent" />
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(255,255,255,0.18),_transparent_38%),radial-gradient(circle_at_bottom_right,_rgba(34,211,238,0.18),_transparent_32%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(255,255,255,0.08),_transparent_32%),radial-gradient(circle_at_bottom_right,_rgba(34,211,238,0.10),_transparent_28%)]" />
                <div class="absolute top-0 right-0 h-72 w-72 -translate-y-1/2 translate-x-1/4 rounded-full bg-white/[0.08] blur-3xl dark:bg-white/[0.04]" />
                <div class="absolute bottom-0 left-0 h-56 w-56 -translate-x-1/4 translate-y-1/3 rounded-full bg-blue-900/20 blur-3xl dark:bg-slate-950/35" />
                <div class="absolute top-6 right-8 hidden h-16 w-16 rounded-full border border-white/[0.08] dark:border-white/[0.05] lg:block" />
                <div class="absolute top-12 right-16 hidden h-28 w-28 rounded-full border border-white/[0.05] dark:border-white/[0.03] lg:block" />
                <div class="absolute inset-0 opacity-[0.04] dark:opacity-[0.03]" style="background-image: radial-gradient(circle, white 0.5px, transparent 0.5px); background-size: 20px 20px;" />

                <div class="relative px-5 py-6 sm:px-7 sm:py-7 lg:px-8 lg:py-8">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="max-w-2xl">
                            <h1 class="text-[20px] font-bold tracking-tight text-white sm:text-[24px] lg:text-[30px] leading-[1.15]">
                                Laporan Akhir
                            </h1>
                            <p class="mt-2 text-[13px] leading-relaxed text-white/78 dark:text-white/70 sm:text-sm">
                                {{
                                    props.pageState === 'completed'
                                        ? props.registration?.laporan_akhir
                                            ? 'Laporan akhir sudah diunggah. Pantau penilaian dari dosen dan mitra.'
                                            : 'Fase akhir PKL sudah dibuka. Unggah laporan akhir untuk memulai penilaian dari dosen dan mitra.'
                                        : props.pageState === 'active'
                                          ? 'Laporan akhir akan diproses setelah dokumen final diunggah.'
                                          : props.pageState === 'waiting'
                                            ? 'Modul laporan akhir akan aktif setelah penempatan PKL resmi berjalan.'
                                            : 'Mulai proses PKL terlebih dahulu agar modul laporan akhir dapat digunakan.'
                                }}
                            </p>
                        </div>

                        <div class="grid w-full grid-cols-2 gap-3 sm:flex sm:w-auto sm:items-center">
                            <div class="min-w-0 rounded-xl bg-white/10 px-4 py-3 ring-1 ring-white/15 backdrop-blur-md dark:bg-white/[0.07] dark:ring-white/10">
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-white/60">Status</p>
                                <p class="mt-1 text-sm font-bold text-white leading-none">{{ statusLabel }}</p>
                            </div>
                            <div class="min-w-0 rounded-xl bg-white/10 px-4 py-3 ring-1 ring-white/15 backdrop-blur-md dark:bg-white/[0.07] dark:ring-white/10">
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-white/60">Progress</p>
                                <p class="mt-1 text-lg font-bold tabular-nums text-white leading-none">{{ progressPercentage }}%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
<!-- Flash -->
            <div v-if="flash.success" class="flex items-start gap-3 rounded-xl border border-emerald-200/60 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-500/10 px-4 py-3">
                <CheckCircle2 class="mt-0.5 size-4 shrink-0 text-emerald-500 dark:text-emerald-400" />
                <div>
                    <p class="text-sm font-bold text-emerald-800 dark:text-emerald-300">Berhasil</p>
                    <p class="mt-0.5 text-xs leading-relaxed text-emerald-700 dark:text-emerald-400">{{ flash.success }}</p>
                </div>
            </div>
            <div v-if="props.final_report_template" class="overflow-hidden rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-blue-200/40 dark:border-blue-500/20 shadow-[0_1px_3px_rgba(0,0,0,0.04)]">
                <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div class="flex min-w-0 items-start gap-3">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400">
                            <FileText class="size-5" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-wims-text">
                                Template Laporan Akhir
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                Sudah punya template laporan akhir?
                            </p>
                        </div>
                    </div>
                    <a
                        v-if="props.final_report_template.download_url"
                        :href="props.final_report_template.download_url"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-blue-200/60 bg-wims-card px-4 py-2.5 text-sm font-semibold text-blue-600 dark:text-blue-400 dark:border-blue-500/30 transition hover:bg-blue-50 dark:hover:bg-blue-500/10 sm:w-auto"
                    >
                        <Download class="size-4" />
                        Download
                    </a>
                </div>
            </div>

            <!-- Timeline Status PKL -->
            <div class="overflow-hidden rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-blue-200/40 dark:border-blue-500/20 shadow-[0_1px_3px_rgba(0,0,0,0.04)]">
                <div class="border-b border-wims-border/50 px-5 py-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-violet-50 dark:bg-violet-500/15 text-violet-600 dark:text-violet-400">
                            <TrendingUp class="size-4" />
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Alur Proses</p>
                            <p class="text-sm font-bold text-wims-text">Timeline Status PKL</p>
                        </div>
                    </div>
                </div>
                <div class="px-5 py-5 sm:px-6">
                    <div class="relative overflow-x-auto">
                        <div class="flex min-w-max flex-row items-start justify-between gap-0 sm:min-w-0">
                            <template v-for="(step, idx) in timelineSteps" :key="step.key">
                                <div class="flex flex-col items-center gap-2" style="min-width:80px;">
                                    <div
                                        class="flex size-10 shrink-0 items-center justify-center rounded-full border-2 transition-all duration-300"
                                        :class="
                                            step.done
                                                ? 'border-emerald-500 bg-emerald-500 text-white shadow-md shadow-emerald-500/30'
                                                : step.active
                                                  ? 'border-blue-500 bg-wims-card text-blue-600 dark:text-blue-400 shadow-[0_0_0_3px_rgba(59,130,246,0.15)]'
                                                  : 'border-wims-border/60 bg-wims-card text-slate-400 dark:text-slate-500'
                                        "
                                    >
                                        <CheckCircle2 v-if="step.done" class="size-5" />
                                        <span v-else class="text-xs font-bold">{{ idx + 1 }}</span>
                                    </div>
                                    <div class="text-center">
                                        <p
                                            class="text-xs font-bold"
                                            :class="step.done ? 'text-emerald-700 dark:text-emerald-300' : step.active ? 'text-wims-text' : 'text-slate-400 dark:text-slate-500'"
                                        >
                                            {{ step.label }}
                                        </p>
                                        <p class="mt-0.5 text-[10px] leading-4 text-slate-400 dark:text-slate-500">{{ step.desc }}</p>
                                    </div>
                                </div>
                                <div
                                    v-if="idx < timelineSteps.length - 1"
                                    class="mt-5 h-px flex-1 min-w-[20px]"
                                    :class="timelineSteps[idx + 1].done || timelineSteps[idx + 1].active ? 'bg-emerald-300 dark:bg-emerald-500/40' : 'bg-slate-200 dark:bg-slate-700/50'"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NEW ELEMENT: PKL Completion Score -->
            <div v-if="props.pageState === 'active' || props.pageState === 'completed'" class="overflow-hidden rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-blue-200/40 dark:border-blue-500/20 shadow-[0_1px_3px_rgba(0,0,0,0.04)]">
                <div class="px-5 py-5 sm:px-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 text-white shadow-md shadow-blue-500/20">
                            <Award class="size-4" />
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Skor Keseluruhan</p>
                            <p class="text-sm font-bold text-wims-text">PKL Completion Score</p>
                        </div>
                        <span class="ml-auto text-2xl font-bold tabular-nums" :class="completionScore.overall >= 75 ? 'text-emerald-600 dark:text-emerald-400' : completionScore.overall >= 50 ? 'text-blue-600 dark:text-blue-400' : 'text-slate-600 dark:text-slate-400'">
                            {{ completionScore.overall }}%
                        </span>
                    </div>
                    <div class="h-2 rounded-full bg-slate-100 dark:bg-slate-700/50 overflow-hidden mb-4">
                        <div
                            class="h-2 rounded-full bg-gradient-to-r from-blue-500 to-cyan-500 transition-all duration-700 ease-out"
                            :style="{ width: `${completionScore.overall}%` }"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-2 md:grid-cols-5">
                        <div v-for="item in completionScore.items" :key="item.label" class="text-center rounded-xl border border-wims-border/50 bg-slate-50/80 dark:bg-slate-800/30 px-2 py-2.5">
                            <p class="text-lg font-bold tabular-nums" :class="item.color">{{ item.percent }}%</p>
                            <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">{{ item.label }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main grid -->
            <div class="grid gap-4 xl:grid-cols-[minmax(0,1.12fr)_minmax(320px,0.88fr)] xl:gap-5">
                <div class="min-w-0 space-y-4">

                    <!-- not_registered -->
                    <div
                        v-if="props.pageState === 'not_registered'"
                        class="overflow-hidden rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-blue-200/40 dark:border-blue-500/20 shadow-[0_1px_3px_rgba(0,0,0,0.04)]"
                    >
                        <div class="space-y-4 px-5 py-5 sm:px-6">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Akses Laporan</p>
                                <h2 class="mt-2 text-xl font-bold tracking-tight text-wims-text sm:text-2xl">
                                    PKL Belum Dimulai
                                </h2>
                                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">
                                    Mulai dari pengajuan PKL/magang. Setelah kampus mengaktifkan penempatanmu, modul laporan akhir akan menyesuaikan secara otomatis.
                                </p>
                            </div>
                            <Link :href="registrationHref" class="block">
                                <button class="h-11 w-full rounded-xl bg-gradient-to-r from-blue-600 to-blue-500 text-sm font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:shadow-blue-500/30 active:scale-[0.98]">
                                    Ajukan Pendaftaran PKL / Magang
                                </button>
                            </Link>
                        </div>
                    </div>

                    <!-- waiting -->
                    <div
                        v-else-if="props.pageState === 'waiting'"
                        class="overflow-hidden rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-blue-200/40 dark:border-blue-500/20 shadow-[0_1px_3px_rgba(0,0,0,0.04)]"
                    >
                        <div class="space-y-4 px-5 py-5 sm:px-6">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Status Pendaftaran</p>
                                    <h2 class="mt-2 text-sm font-bold tracking-tight text-wims-text">
                                        {{ statusLabel }}
                                    </h2>
                                </div>
                                <span class="inline-flex w-fit items-center rounded-full border px-3 py-1 text-xs font-bold" :class="statusClasses">
                                    {{ statusLabel }}
                                </span>
                            </div>
                            <div class="grid gap-2.5 sm:grid-cols-2">
                                <div class="rounded-xl border border-wims-border/50 bg-slate-50/80 dark:bg-slate-800/30 px-4 py-3">
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Usulan Perusahaan</p>
                                    <p class="mt-1.5 text-sm font-bold text-wims-text">
                                        {{ proposalCompanyLabel }}
                                    </p>
                                </div>
                                <div class="rounded-xl border border-wims-border/50 bg-slate-50/80 dark:bg-slate-800/30 px-4 py-3">
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Periode PKL</p>
                                    <p class="mt-1.5 text-sm font-bold text-wims-text">
                                        {{ props.registration?.period_label || 'Belum tersedia' }}
                                    </p>
                                </div>
                            </div>
                            <p class="text-sm leading-6 text-slate-600 dark:text-slate-400">
                                Modul laporan akhir belum dibuka karena penempatanmu masih menunggu keputusan kampus atau revisi data.
                            </p>
                            <Link :href="registrationHref" class="block">
                                <button class="h-11 w-full rounded-xl bg-gradient-to-r from-blue-600 to-blue-500 text-sm font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:shadow-blue-500/30 active:scale-[0.98]">
                                    Lihat Detail Pendaftaran
                                </button>
                            </Link>
                        </div>
                    </div>

                    <!-- active -->
                    <div
                        v-else-if="props.pageState === 'active'"
                        class="overflow-hidden rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-blue-200/40 dark:border-blue-500/20 shadow-[0_1px_3px_rgba(0,0,0,0.04)]"
                    >
                        <div class="space-y-4 px-5 py-5 sm:px-6">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Fase Berjalan</p>
                                    <h2 class="mt-2 text-sm font-bold tracking-tight text-wims-text">
                                        PKL Sedang Aktif
                                    </h2>
                                </div>
                                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-bold" :class="statusClasses">
                                    {{ statusLabel }}
                                </span>
                            </div>
                            <p class="text-sm leading-6 text-slate-600 dark:text-slate-400">
                                Selama PKL masih aktif, fokus utama tetap pada presensi dan logbook harian. Laporan akhir menjadi tahap berikutnya setelah kampus menutup periode magangmu.
                            </p>
                            <div class="rounded-xl border border-wims-border/50 bg-slate-50/80 dark:bg-slate-800/30 px-4 py-3.5">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Progres Periode</p>
                                        <p class="mt-1.5 text-sm font-bold text-wims-text">
                                            {{ props.internship?.completed_days ?? 0 }}/{{ props.internship?.total_days ?? 0 }} hari
                                        </p>
                                    </div>
                                    <span class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ progressPercentage }}%</span>
                                </div>
                                <div class="mt-3 h-2 rounded-full bg-slate-200 dark:bg-slate-700/50">
                                    <div
                                        class="h-2 rounded-full bg-gradient-to-r from-blue-600 to-blue-400 transition-all duration-700 ease-out"
                                        :style="{ width: `${progressPercentage}%` }"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- completed - Drag & Drop File -->
                    <div
                        v-else
                        class="overflow-hidden rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-blue-200/40 dark:border-blue-500/20 shadow-[0_1px_3px_rgba(0,0,0,0.04)]"
                    >
                        <div class="space-y-4 px-5 py-5 sm:px-6">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Fase Penutupan</p>
                                    <h2 class="mt-2 text-sm font-bold tracking-tight text-wims-text">
                                        Laporan dan Penilaian Akhir
                                    </h2>
                                </div>
                                <span class="inline-flex w-fit items-center rounded-full border px-3 py-1 text-xs font-bold" :class="statusClasses">
                                    {{ statusLabel }}
                                </span>
                            </div>

                            <div class="grid gap-2.5 sm:grid-cols-2">
                                <div class="rounded-xl border border-wims-border/50 bg-slate-50/80 dark:bg-slate-800/30 px-4 py-3">
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Perusahaan Final</p>
                                    <p class="mt-1.5 break-words text-sm font-bold text-wims-text">
                                        {{ currentCompanyLabel }}
                                    </p>
                                </div>
                                <div class="rounded-xl border border-wims-border/50 bg-slate-50/80 dark:bg-slate-800/30 px-4 py-3">
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Periode PKL</p>
                                    <p class="mt-1.5 break-words text-sm font-bold text-wims-text">
                                        {{ props.registration?.period_label || 'Belum tersedia' }}
                                    </p>
                                </div>
                            </div>



                            <!-- File Upload Section -->
                            <div class="rounded-xl border border-wims-border/50 bg-slate-50/80 dark:bg-slate-800/30 px-4 py-4">
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Dokumen Laporan Akhir</p>

                                <!-- Sudah ada file tersimpan -->
                                <div
                                    v-if="props.registration?.laporan_akhir"
                                    class="mt-3 rounded-xl border border-wims-border/50 bg-wims-card px-4 py-3.5"
                                >
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                        <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                                            <FileText class="size-4" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="break-all text-sm font-bold leading-5 text-wims-text">
                                                {{ props.registration.laporan_akhir.name || 'Dokumen tersimpan' }}
                                            </p>
                                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                                {{ props.registration.laporan_akhir.uploaded_at || 'Waktu upload belum tersedia' }}
                                            </p>
                                        </div>
                                        <div class="flex w-full flex-col gap-2 sm:ml-auto sm:w-auto sm:flex-row sm:items-center">
                                            <a
                                                v-if="props.registration.laporan_akhir.view_url"
                                                :href="props.registration.laporan_akhir.view_url"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl border border-wims-border/60 bg-wims-card px-3 py-2 text-center text-xs font-bold text-slate-700 transition hover:border-blue-300 hover:text-blue-600 dark:text-slate-300 sm:w-auto"
                                            >
                                                Lihat Dokumen
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Drag & Drop Zone -->
                                <div
                                    class="mt-3 rounded-xl border-2 border-dashed transition-all duration-200"
                                    :class="
                                        isDragging
                                            ? 'border-blue-500 bg-blue-50 dark:bg-blue-500/10'
                                            : filePreview
                                              ? 'border-emerald-300 bg-emerald-50 dark:bg-emerald-500/10'
                                              : 'border-wims-border/60 bg-wims-card hover:border-blue-300/60'
                                    "
                                    @dragover.prevent="isDragging = true"
                                    @dragleave="isDragging = false"
                                    @drop.prevent="handleDrop"
                                >
                                    <!-- File selected preview -->
                                    <div v-if="filePreview" class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center">
                                        <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                                            <FileText class="size-4" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="break-all text-sm font-bold leading-5 text-wims-text">{{ filePreview.name }}</p>
                                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                                {{ filePreview.size }} &middot; {{ filePreview.type.includes('pdf') ? 'PDF' : 'Word Document' }}
                                            </p>
                                        </div>
                                        <button
                                            type="button"
                                            class="flex size-7 shrink-0 self-end items-center justify-center rounded-full border border-wims-border/60 bg-wims-card text-slate-400 transition hover:border-rose-300 hover:bg-rose-50 hover:text-rose-500 dark:hover:bg-rose-500/10 sm:self-center"
                                            @click.stop="clearFilePreview"
                                        >
                                            <X class="size-3.5" />
                                        </button>
                                    </div>

                                    <!-- Empty drop zone -->
                                    <label v-else class="flex flex-col items-center gap-2 px-4 py-6 cursor-pointer">
                                        <div class="flex size-11 items-center justify-center rounded-xl bg-slate-50/80 dark:bg-slate-800/40 border border-wims-border/50 text-slate-400 dark:text-slate-500">
                                            <Upload class="size-5" />
                                        </div>
                                        <div class="text-center">
                                            <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                                <span class="font-bold text-blue-600 dark:text-blue-400">Klik untuk unggah</span> atau seret file ke sini
                                            </p>
                                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">PDF, DOC, DOCX &middot; Maks. 10 MB</p>
                                        </div>
                                        <input
                                            type="file"
                                            accept=".pdf,.doc,.docx"
                                            class="sr-only"
                                            @change="handleReportChange"
                                        />
                                    </label>
                                </div>

                                <InputError :message="reportForm.errors.laporan_akhir" class="mt-2" />

                                <button
                                    type="button"
                                    class="mt-3 h-11 w-full rounded-xl text-sm font-bold text-white transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
                                    :class="!canUploadFinalReport || reportForm.processing || !reportForm.laporan_akhir
                                        ? 'bg-slate-300 dark:bg-slate-700 text-slate-500 dark:text-slate-400'
                                        : 'bg-gradient-to-r from-blue-600 to-blue-500 shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30'"
                                    :disabled="!canUploadFinalReport || reportForm.processing || !reportForm.laporan_akhir"
                                    @click="submitReport"
                                >
                                    <span class="flex items-center justify-center gap-2">
                                        <Upload class="size-4" />
                                        {{
                                            reportForm.processing
                                                ? 'Mengunggah...'
                                                : props.registration?.laporan_akhir
                                                  ? 'Unggah Ulang Laporan Akhir'
                                                  : 'Unggah Laporan Akhir'
                                        }}
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Dokumen & Rekap PKL -->
                    <div
                        v-if="hasCurrentPeriodHistoryDownload || hasCurrentPeriodLogbookDownload"
                        class="overflow-hidden rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-blue-200/40 dark:border-blue-500/20 shadow-[0_1px_3px_rgba(0,0,0,0.04)]"
                    >
                        <div class="border-b border-wims-border/50 px-5 py-4 sm:px-6">
                            <div class="flex items-center gap-3">
                                <div class="flex size-9 items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-500/15 text-blue-600 dark:text-blue-400">
                                    <Download class="size-4" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Dokumen PKL</p>
                                    <p class="text-sm font-bold text-wims-text">Dokumen & Rekap PKL</p>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3 px-5 py-4 sm:px-6">
                            <div v-if="hasCurrentPeriodHistoryDownload || attendanceHistory.length" class="rounded-xl border border-wims-border/50 bg-slate-50/80 dark:bg-slate-800/30 px-4 py-3.5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold text-wims-text">Riwayat Presensi</p>

                                    </div>
                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="14" cy="9" r="6" />
                                            <polyline points="14 6 14 9 16.5 10.5" />
                                            <circle cx="7" cy="17" r="3" />
                                            <path d="M5 22a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2" />
                                        </svg>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    class="mt-3 h-9 w-full rounded-xl border border-wims-border/60 bg-wims-card text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:hover:bg-slate-700/30"
                                    @click="attendanceHistoryDialogOpen = true"
                                >
                                    <span class="flex items-center justify-center gap-2">
                                        <Eye class="size-3.5" />
                                        Lihat Semua Riwayat Presensi
                                    </span>
                                </button>
                                <button
                                    v-if="hasCurrentPeriodHistoryDownload"
                                    type="button"
                                    class="mt-3 h-9 w-full rounded-xl border border-blue-200/60 bg-wims-card text-sm font-bold text-blue-600 dark:text-blue-400 dark:border-blue-500/30 transition hover:bg-blue-50 dark:hover:bg-blue-500/10"
                                    @click="downloadCurrentPeriodAttendanceHistory"
                                >
                                    <span class="flex items-center justify-center gap-2">
                                        <Download class="size-3.5" />
                                        Unduh Riwayat Presensi
                                    </span>
                                </button>
                            </div>

                            <div v-if="hasCurrentPeriodLogbookDownload || logbookHistory.length" class="rounded-xl border border-wims-border/50 bg-slate-50/80 dark:bg-slate-800/30 px-4 py-3.5">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold text-wims-text">Logbook PKL</p>

                                    </div>
                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-violet-50 dark:bg-violet-500/15 text-violet-600 dark:text-violet-400">
                                        <NotebookPen class="size-4" />
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    class="mt-3 h-9 w-full rounded-xl border border-wims-border/60 bg-wims-card text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:hover:bg-slate-700/30"
                                    @click="logbookHistoryDialogOpen = true"
                                >
                                    <span class="flex items-center justify-center gap-2">
                                        <Eye class="size-3.5" />
                                        Lihat Semua Riwayat Logbook
                                    </span>
                                </button>
                                <button
                                    v-if="hasCurrentPeriodLogbookDownload"
                                    type="button"
                                    class="mt-3 h-9 w-full rounded-xl border border-blue-200/60 bg-wims-card text-sm font-bold text-blue-600 dark:text-blue-400 dark:border-blue-500/30 transition hover:bg-blue-50 dark:hover:bg-blue-500/10"
                                    @click="downloadCurrentPeriodLogbook"
                                >
                                    <span class="flex items-center justify-center gap-2">
                                        <Download class="size-3.5" />
                                        Unduh Logbook
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>


                <Dialog v-model:open="attendanceHistoryDialogOpen">
                    <DialogContent class="w-full max-w-[calc(100vw-2rem)] rounded-2xl border border-wims-border bg-wims-card sm:max-w-4xl">
                        <DialogHeader>
                            <DialogTitle class="text-wims-text">Riwayat Presensi</DialogTitle>
                            <DialogDescription class="text-slate-600">Seluruh riwayat presensi mahasiswa selama periode PKL terakhir.</DialogDescription>
                        </DialogHeader>

                        <div class="max-h-[70vh] overflow-y-auto rounded-xl border border-wims-border">
                            <div v-if="attendanceHistory.length" class="space-y-3 p-3 md:hidden">
                                <div
                                    v-for="item in attendanceHistory"
                                    :key="`attendance-mobile-${item.id}`"
                                    class="rounded-xl border border-wims-border bg-slate-50 px-4 py-3.5"
                                >
                                    <div class="flex flex-col gap-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold text-wims-text">{{ item.tanggal_label || formatDateUi(item.tanggal) }}</p>
                                                <p class="mt-1 text-xs leading-5 text-slate-500">{{ item.keterangan || 'Tanpa catatan tambahan' }}</p>
                                            </div>
                                            <span class="inline-flex rounded-full border px-3 py-1 text-[11px] font-bold" :class="attendanceStatusClass(item.status)">
                                                {{ attendanceStatusLabel(item.status) }}
                                            </span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div class="rounded-lg border border-wims-border bg-white px-3 py-2.5">
                                                <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Masuk</p>
                                                <p class="mt-1 text-sm font-bold text-wims-text">{{ formatTimeUi(item.check_in) }}</p>
                                            </div>
                                            <div class="rounded-lg border border-wims-border bg-white px-3 py-2.5">
                                                <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Pulang</p>
                                                <p class="mt-1 text-sm font-bold text-wims-text">{{ formatTimeUi(item.check_out) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="attendanceHistory.length" class="hidden overflow-x-auto md:block">
                                <table class="min-w-full border-collapse">
                                    <thead class="bg-slate-50/70">
                                        <tr class="border-b border-wims-border">
                                            <th class="px-4 py-3 text-left text-[12px] font-bold uppercase tracking-[0.06em] text-slate-500">Tanggal</th>
                                            <th class="px-4 py-3 text-left text-[12px] font-bold uppercase tracking-[0.06em] text-slate-500">Status</th>
                                            <th class="px-4 py-3 text-left text-[12px] font-bold uppercase tracking-[0.06em] text-slate-500">Masuk</th>
                                            <th class="px-4 py-3 text-left text-[12px] font-bold uppercase tracking-[0.06em] text-slate-500">Pulang</th>
                                            <th class="px-4 py-3 text-left text-[12px] font-bold uppercase tracking-[0.06em] text-slate-500">Catatan</th>
                                            <th class="px-4 py-3 text-left text-[12px] font-bold uppercase tracking-[0.06em] text-slate-500">Bukti</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in attendanceHistory" :key="`attendance-${item.id}`" class="border-b border-wims-border last:border-b-0">
                                            <td class="px-4 py-3 text-sm font-medium text-wims-text">{{ item.tanggal_label || formatDateUi(item.tanggal) }}</td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex rounded-full border px-3 py-1 text-[11px] font-bold" :class="attendanceStatusClass(item.status)">
                                                    {{ attendanceStatusLabel(item.status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-slate-600">{{ formatTimeUi(item.check_in) }}</td>
                                            <td class="px-4 py-3 text-sm text-slate-600">{{ formatTimeUi(item.check_out) }}</td>
                                            <td class="px-4 py-3 text-sm text-slate-500">{{ item.keterangan || '-' }}</td>
                                            <td class="px-4 py-3 text-sm text-slate-500">
                                                <div class="flex flex-wrap gap-2">
                                                    <a v-if="item.check_in_photo_url" :href="item.check_in_photo_url" target="_blank" rel="noreferrer" class="text-xs font-bold text-blue-600 hover:text-blue-700">Foto masuk</a>
                                                    <a v-if="item.check_out_photo_url" :href="item.check_out_photo_url" target="_blank" rel="noreferrer" class="text-xs font-bold text-blue-600 hover:text-blue-700">Foto pulang</a>
                                                    <span v-if="!item.check_in_photo_url && !item.check_out_photo_url" class="text-slate-400">-</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div v-if="!attendanceHistory.length" class="px-4 py-6 text-sm text-slate-500">
                                Belum ada riwayat presensi yang dapat ditampilkan.
                            </div>
                        </div>

                        <DialogFooter>
                            <button type="button" class="inline-flex h-9 items-center justify-center rounded-xl border border-wims-border bg-wims-card px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50" @click="attendanceHistoryDialogOpen = false">
                                Tutup
                            </button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

                <Dialog v-model:open="logbookHistoryDialogOpen">
                    <DialogContent class="w-full max-w-[calc(100vw-2rem)] rounded-2xl border border-wims-border bg-wims-card sm:max-w-4xl">
                        <DialogHeader>
                            <DialogTitle class="text-wims-text">Riwayat Logbook</DialogTitle>
                            <DialogDescription class="text-slate-600">Seluruh riwayat logbook mahasiswa selama periode PKL terakhir.</DialogDescription>
                        </DialogHeader>

                        <div class="max-h-[70vh] space-y-3 overflow-y-auto pr-1">
                            <div
                                v-for="item in logbookHistory"
                                :key="`logbook-${item.id}`"
                                class="rounded-xl border border-wims-border bg-slate-50 px-4 py-3.5"
                            >
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold text-wims-text">{{ item.tanggal_label || formatDateUi(item.tanggal) }}</p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ formatTimeUi(item.jam_mulai) }} - {{ formatTimeUi(item.jam_selesai) }}
                                        </p>
                                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ item.aktivitas || '-' }}</p>
                                        <p v-if="item.kompetensi" class="mt-2 text-xs leading-5 text-slate-500">Kompetensi: {{ item.kompetensi }}</p>
                                        <p v-if="item.catatan_mitra" class="mt-2 text-xs leading-5 text-slate-500">Catatan mitra: {{ item.catatan_mitra }}</p>
                                        <div v-if="(item.photos ?? []).length" class="mt-3 flex flex-wrap gap-2">
                                            <a
                                                v-for="photo in item.photos"
                                                :key="`logbook-photo-${photo.id}`"
                                                :href="photo.url || undefined"
                                                target="_blank"
                                                rel="noreferrer"
                                                class="text-xs font-bold text-blue-600 hover:text-blue-700"
                                            >
                                                Lihat foto
                                            </a>
                                        </div>
                                    </div>
                                    <span class="inline-flex w-fit rounded-full border px-3 py-1 text-[11px] font-bold" :class="logbookStatusClass(item.status)">
                                        {{ logbookStatusLabel(item.status) }}
                                    </span>
                                </div>
                            </div>

                            <div v-if="!logbookHistory.length" class="rounded-xl border border-dashed border-wims-border bg-slate-50 px-4 py-4 text-sm text-slate-500">
                                Belum ada riwayat logbook yang dapat ditampilkan.
                            </div>
                        </div>

                        <DialogFooter>
                            <button type="button" class="inline-flex h-9 items-center justify-center rounded-xl border border-wims-border bg-wims-card px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50" @click="logbookHistoryDialogOpen = false">
                                Tutup
                            </button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

                    </div>

                <!-- Right Column -->
                <div class="min-w-0 space-y-4">
                    <!-- Checklist Kelengkapan -->
                    <div class="overflow-hidden rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-blue-200/40 dark:border-blue-500/20 shadow-[0_1px_3px_rgba(0,0,0,0.04)]">
                        <div class="border-b border-wims-border/50 px-5 py-4 sm:px-6">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-9 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400">
                                        <ClipboardCheck class="size-4" />
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Persyaratan</p>
                                        <p class="text-sm font-bold text-wims-text">Checklist Kelengkapan</p>
                                    </div>
                                </div>
                                <span class="text-sm font-bold text-wims-text">
                                    {{ checklistDoneCount }}/{{ checklistItems.length }}
                                </span>
                            </div>
                        </div>
                        <div class="px-5 py-4 sm:px-6">
                            <div class="mb-3">
                                <div class="mb-1.5 flex items-center justify-between gap-2">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Kelengkapan laporan</p>
                                    <span class="text-xs font-bold" :class="checklistPercent === 100 ? 'text-emerald-600 dark:text-emerald-400' : 'text-wims-text'">
                                        {{ checklistPercent }}%
                                    </span>
                                </div>
                                <div class="h-2 rounded-full bg-slate-100 dark:bg-slate-700/50">
                                    <div
                                        class="h-2 rounded-full transition-all duration-700 ease-out"
                                        :class="checklistPercent === 100 ? 'bg-emerald-500' : 'bg-gradient-to-r from-amber-400 to-amber-500'"
                                        :style="{ width: `${checklistPercent}%` }"
                                    />
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <div
                                    v-for="item in checklistItems"
                                    :key="item.label"
                                    class="flex items-center gap-3 rounded-xl border px-3 py-2.5 transition"
                                    :class="item.done ? 'border-emerald-200/60 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-500/10' : 'border-wims-border/50 bg-slate-50/80 dark:bg-slate-800/30'"
                                >
                                    <div
                                        class="flex size-5 shrink-0 items-center justify-center rounded-full"
                                        :class="item.done ? 'bg-emerald-500 text-white' : 'border border-slate-300 dark:border-slate-600 bg-wims-card'"
                                    >
                                        <CheckCircle2 v-if="item.done" class="size-3" />
                                        <Clock v-else class="size-2.5 text-slate-400 dark:text-slate-500" />
                                    </div>
                                    <p
                                        class="min-w-0 break-words text-sm"
                                        :class="item.done ? 'font-bold text-emerald-800 dark:text-emerald-300' : 'text-slate-600 dark:text-slate-400'"
                                    >
                                        {{ item.label }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

