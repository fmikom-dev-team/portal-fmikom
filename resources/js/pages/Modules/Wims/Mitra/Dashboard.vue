<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Bell,
    ClipboardList,
    FileClock,
    Users,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { formatIndonesianDateLabel } from '@/lib/date';
import WimsMitraLayout from '@/layouts/Modules/Wims/Mitra/Layout.vue';

defineOptions({
    layout: WimsMitraLayout,
});

type MentorProps = {
    name?: string | null;
    email?: string | null;
    phone?: string | null;
    jabatan?: string | null;
    company_city?: string | null;
};

type SummaryProps = {
    total_students?: number;
    upcoming_students?: number;
    active_students?: number;
    completed_students?: number;
    not_present_today?: number;
    needs_review?: number;
    active_warnings?: number;
    not_evaluated?: number;
    pending_absence_requests?: number;
};

type ObjectiveSummary = {
    expected_workdays?: number;
    attendance_total?: number;
    attendance_late?: number;
    attendance_rate?: number;
    logbook_total?: number;
    logbook_approved?: number;
    logbook_revision?: number;
    logbook_pending?: number;
    logbook_rate?: number;
    final_report_uploaded?: boolean;
    mentor_evaluation_submitted?: boolean;
    evaluation_status?: string | null;
    evaluation_total_score?: number | null;
};

type StudentItem = {
    registration_id: number;
    student_id?: number | null;
    name?: string | null;
    nim?: string | null;
    company?: {
        id?: number | null;
        name?: string | null;
    } | null;
    period_start?: string | null;
    period_end?: string | null;
    status_pendaftaran?: string | null;
    dashboard_phase?: 'assigned' | 'upcoming' | 'active' | 'completed' | null;
    attendance_status?: string | null;
    check_in_time?: string | null;
    check_out_time?: string | null;
    latest_logbook_id?: number | null;
    logbook_status?: string | null;
    latest_logbook_date?: string | null;
    latest_logbook_sort_date?: string | null;
    latest_logbook_activity?: string | null;
    latest_logbook_competency?: string | null;
    latest_logbook_note?: string | null;
    objective_summary?: ObjectiveSummary | null;
};

type AttendanceBoardItem = {
    registration_id: number;
    name?: string | null;
    nim?: string | null;
    attendance_status?: string | null;
    check_in_time?: string | null;
    check_out_time?: string | null;
};

type PendingAbsenceRequestItem = {
    id: number;
    name?: string | null;
    nim?: string | null;
    company?: {
        id?: number | null;
        name?: string | null;
    } | null;
    jenis?: string | null;
    alasan?: string | null;
    tanggal_label?: string | null;
};

type WarningItem = {
    student_id?: number | null;
    pendaftaran_id?: number | null;
    name?: string | null;
    nim?: string | null;
    company?: {
        id?: number | null;
        name?: string | null;
    } | null;
    attendance_missing_days?: number;
    logbook_missing_days?: number;
    missing_types?: string[];
    last_monitoring_date?: string | null;
};

type DashboardNotificationItem = {
    id: string;
    title: string;
    description: string;
    actionLabel?: string | null;
    tone: 'amber' | 'blue' | 'rose' | 'slate' | 'emerald';
    targetType?: 'section' | 'monitoring-index' | 'assessment-index';
    target?: string;
    status?: 'aktif' | 'selesai';
};

const props = defineProps<{
    mentor: MentorProps;
    summary: SummaryProps;
    students: StudentItem[];
    attendanceBoard: AttendanceBoardItem[];
    reviewBoard: StudentItem[];
    pendingAbsenceRequests: PendingAbsenceRequestItem[];
    warnings: WarningItem[];
}>();

const search = ref('');
const isNotificationOpen = ref(false);
const notificationMenuRef = ref<HTMLElement | null>(null);
const activeFilter = ref<
    'semua' | 'perlu_review' | 'butuh_revisi' | 'alfa' | 'belum_dinilai'
>('semua');
const absenceReviewForm = useForm({
    catatan_mitra: '',
});

const summaryCards = computed(() => [
    {
        label: 'Mahasiswa Aktif',
        value: props.summary.active_students ?? 0,
        caption: 'Sedang PKL.',
        tone: 'text-sky-700',
        cardClass:
            'border-blue-100/80 bg-blue-50/40 dark:bg-blue-500/8 dark:border-blue-500/20',
        iconClass:
            'border-blue-100 bg-blue-100 text-blue-600 dark:bg-blue-500/15 dark:border-blue-500/20 dark:text-blue-400',
        icon: Users,
    },
    {
        label: 'Siap Dinilai',
        value: props.summary.completed_students ?? 0,
        caption: 'Laporan akhir sudah masuk.',
        tone: 'text-emerald-700',
        cardClass:
            'border-emerald-100/80 bg-emerald-50/38 dark:bg-emerald-500/8 dark:border-emerald-500/20',
        iconClass:
            'border-emerald-100 bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:border-emerald-500/20 dark:text-emerald-400',
        icon: FileClock,
    },
    {
        label: 'Perlu Tindak Lanjut',
        value: props.summary.active_warnings ?? 0,
        caption: 'Perlu dicek.',
        tone: 'text-amber-700',
        cardClass:
            'border-amber-100/80 bg-amber-50/42 dark:bg-amber-500/8 dark:border-amber-500/20',
        iconClass:
            'border-amber-100 bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:border-amber-500/20 dark:text-amber-400',
        icon: AlertTriangle,
    },
    {
        label: 'Belum Dinilai',
        value: props.summary.not_evaluated ?? 0,
        caption: 'Penilaian belum masuk.',
        tone: 'text-slate-700 dark:text-slate-200',
        cardClass: 'border-slate-200 bg-slate-50/65 dark:bg-slate-800/40',
        iconClass:
            'border-slate-200 bg-white text-slate-500 dark:bg-slate-700/50 dark:border-slate-700 dark:text-slate-300',
        icon: FileClock,
    },
    {
        label: 'Pengajuan Menunggu',
        value: props.summary.pending_absence_requests ?? 0,
        caption: 'Menunggu keputusan.',
        tone: 'text-orange-700',
        cardClass:
            'border-orange-100/80 bg-orange-50/40 dark:bg-orange-500/8 dark:border-orange-500/20',
        iconClass:
            'border-orange-100 bg-white text-orange-600 dark:bg-orange-500/15 dark:border-orange-500/20 dark:text-orange-400',
        icon: ClipboardList,
    },
]);

const matchesSearch = (student: StudentItem) => {
    const keyword = search.value.trim().toLowerCase();

    if (keyword === '') {
        return true;
    }

    return (
        (student.name ?? '').toLowerCase().includes(keyword) ||
        (student.nim ?? '').toLowerCase().includes(keyword) ||
        (student.company?.name ?? '').toLowerCase().includes(keyword)
    );
};

const matchesSectionFilter = (
    student: StudentItem,
    section: 'active' | 'completed',
) => {
    if (section === 'active') {
        if (activeFilter.value === 'perlu_review') {
            return (
                student.attendance_status === 'alfa' ||
                student.logbook_status === 'menunggu_review' ||
                student.logbook_status === 'revisi'
            );
        }

        if (activeFilter.value === 'butuh_revisi') {
            return student.logbook_status === 'revisi';
        }

        if (activeFilter.value === 'alfa') {
            return student.attendance_status === 'alfa';
        }

        return true;
    }

    if (activeFilter.value === 'belum_dinilai') {
        return student.objective_summary?.evaluation_status !== 'submitted';
    }

    return true;
};

const sectionDefinitions: Array<{
    key: 'active' | 'completed';
    title: string;
    description: string;
    empty: string;
}> = [
    {
        key: 'active',
        title: 'Mahasiswa Aktif',
        description:
            'Ringkasan mahasiswa aktif.',
        empty: 'Tidak ada mahasiswa aktif pada filter saat ini.',
    },
    {
        key: 'completed',
        title: 'Siap Dinilai',
        description:
            'Ringkasan mahasiswa siap dinilai.',
        empty: 'Belum ada mahasiswa siap dinilai.',
    },
];

const studentSections = computed(() =>
    sectionDefinitions.map((section) => ({
        ...section,
        students: props.students.filter(
            (student) =>
                student.dashboard_phase === section.key &&
                matchesSearch(student) &&
                matchesSectionFilter(student, section.key),
        ),
    })),
);

const attendanceLabel = (status?: string | null) => {
    if (status === 'belum_mulai') return 'Belum Mulai';
    if (status === 'terlambat') return 'Terlambat';
    if (status === 'izin') return 'Izin';
    if (status === 'sakit') return 'Sakit';
    if (status === 'alfa') return 'Alfa';
    return 'Hadir';
};

const attendanceClass = (status?: string | null) => {
    if (status === 'belum_mulai')
        return 'border-slate-200 bg-slate-50 text-slate-600 dark:bg-slate-800/50 dark:text-slate-300';
    if (status === 'terlambat')
        return 'border-amber-200 bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300';
    if (status === 'izin')
        return 'border-blue-200 bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300';
    if (status === 'sakit')
        return 'border-violet-200 bg-violet-50 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300';
    if (status === 'alfa')
        return 'border-rose-200 bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300';
    return 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300';
};

const logbookLabel = (status?: string | null) => {
    if (status === 'belum_mulai') return 'Belum Mulai';
    if (status === 'disetujui') return 'Disetujui';
    if (status === 'revisi') return 'Revisi';
    if (status === 'belum_isi') return 'Belum Isi';
    return 'Menunggu Review';
};

const logbookClass = (status?: string | null) => {
    if (status === 'belum_mulai')
        return 'border-slate-200 bg-slate-50 text-slate-600 dark:bg-slate-800/50 dark:text-slate-300';
    if (status === 'disetujui')
        return 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300';
    if (status === 'revisi')
        return 'border-rose-200 bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300';
    if (status === 'belum_isi')
        return 'border-slate-200 bg-slate-50 text-slate-600 dark:bg-slate-800/50 dark:text-slate-300';
    return 'border-amber-200 bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300';
};

const evaluationStatusLabel = (status?: string | null) => {
    if (status === 'submitted') return 'Sudah Dikirim';
    if (status === 'draft') return 'Draft';
    return 'Belum Dinilai';
};

const evaluationStatusClass = (status?: string | null) => {
    if (status === 'submitted')
        return 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300';
    if (status === 'draft')
        return 'border-amber-200 bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300';
    return 'border-slate-200 bg-slate-50 text-slate-600 dark:bg-slate-800/50 dark:text-slate-300';
};

const formatDateUi = (value?: string | null) => formatIndonesianDateLabel(value);

const formatTimeUi = (value?: string | null) => value?.trim() || '-';

const reportStatusLabel = (uploaded?: boolean) =>
    uploaded ? 'Sudah Diunggah' : 'Belum Diunggah';

const reportStatusClass = (uploaded?: boolean) =>
    uploaded
        ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
        : 'border-slate-200 bg-slate-50 text-slate-600 dark:bg-slate-800/50 dark:text-slate-300';

const canAssess = (student: StudentItem) =>
    student.dashboard_phase === 'completed';

const assessmentActionLabel = (student: StudentItem) => {
    if (student.objective_summary?.evaluation_status === 'submitted') {
        return 'Lihat Nilai';
    }

    if (student.objective_summary?.evaluation_status === 'draft') {
        return 'Lanjutkan Draft';
    }

    return 'Isi Nilai';
};

const periodLabel = (student: StudentItem) => {
    if (student.period_start && student.period_end) {
        return `${formatDateUi(student.period_start)} - ${formatDateUi(student.period_end)}`;
    }

    return formatDateUi(student.period_start || student.period_end);
};

const attendanceMeta = (student: StudentItem) => {
    return `Masuk: ${formatTimeUi(student.check_in_time)} / Pulang: ${formatTimeUi(student.check_out_time)}`;
};

const activeLogbookSummary = (student: StudentItem) => {
    return `${student.objective_summary?.logbook_total ?? 0} entri`;
};

const absenceKindLabel = (value?: string | null) => {
    if (value === 'sakit') {
        return 'Sakit';
    }

    return 'Izin';
};

const approveAbsenceRequest = (id: number) => {
    absenceReviewForm.post(`/wims/mitra/ketidakhadiran/${id}/approve`, {
        preserveScroll: true,
    });
};

const rejectAbsenceRequest = (id: number) => {
    absenceReviewForm.post(`/wims/mitra/ketidakhadiran/${id}/reject`, {
        preserveScroll: true,
    });
};

const openMonitoring = (student: StudentItem) => {
    if (!student.student_id) {
        return;
    }

    router.visit(`/wims/mitra/monitoring/${student.student_id}`);
};

const openMonitoringIndex = (status?: 'aktif' | 'selesai') => {
    const url = status
        ? `/wims/mitra/monitoring?status=${encodeURIComponent(status)}`
        : '/wims/mitra/monitoring';

    router.visit(url);
};

const openAssessment = (student: StudentItem) => {
    if (!student.registration_id) {
        return;
    }

    router.visit(
        `/wims/mitra/penilaian-mahasiswa/${student.registration_id}?from=dashboard`,
    );
};

const openWarningMonitoring = (warning: WarningItem) => {
    if (!warning.student_id) {
        return;
    }

    router.visit(`/wims/mitra/monitoring/${warning.student_id}`);
};

const warningDetails = (warning: WarningItem) => {
    const parts: string[] = [];

    if ((warning.attendance_missing_days ?? 0) >= 3) {
        parts.push(`Belum presensi ${warning.attendance_missing_days} hari`);
    }

    if ((warning.logbook_missing_days ?? 0) >= 3) {
        parts.push(`Belum logbook ${warning.logbook_missing_days} hari`);
    }

    return parts;
};

const previewStudents = (students: StudentItem[]) => students.slice(0, 3);

const remainingPreviewCount = (students: StudentItem[]) =>
    Math.max(students.length - 3, 0);

const notificationToneClass = (
    tone: DashboardNotificationItem['tone'],
) => {
    if (tone === 'rose') {
        return 'border-rose-200 bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300';
    }

    if (tone === 'blue') {
        return 'border-blue-200 bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300';
    }

    if (tone === 'amber') {
        return 'border-amber-200 bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300';
    }

    if (tone === 'emerald') {
        return 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300';
    }

    return 'border-slate-200 bg-slate-50 text-slate-700 dark:bg-slate-800/50 dark:text-slate-300';
};

const notificationItems = computed<DashboardNotificationItem[]>(() => {
    const items: DashboardNotificationItem[] = [];
    const firstWarning = props.warnings[0];

    if (props.pendingAbsenceRequests.length > 0) {
        items.push({
            id: 'pending-absence',
            title: `${props.pendingAbsenceRequests.length} pengajuan izin menunggu`,
            description:
                'Segera proses izin/sakit yang menunggu.',
            actionLabel: 'Tinjau',
            tone: 'amber',
            targetType: 'section',
            target: 'pending-absence-section',
        });
    }

    if (props.warnings.length > 0) {
        items.push({
            id: 'warnings',
            title: `${props.warnings.length} mahasiswa perlu tindak lanjut`,
            description: firstWarning
                ? `${firstWarning.name || 'Mahasiswa'}${
                      warningDetails(firstWarning).length
                          ? `: ${warningDetails(firstWarning).join(', ')}.`
                          : ' perlu dicek.'
                  }`
                : 'Ada mahasiswa yang perlu dicek.',
            actionLabel: 'Periksa',
            tone: 'rose',
            targetType: 'section',
            target: 'warning-section',
        });
    }

    if ((props.summary.not_present_today ?? 0) > 0) {
        items.push({
            id: 'attendance',
            title: `${props.summary.not_present_today} mahasiswa belum presensi`,
            description:
                'Cek presensi hari ini.',
            actionLabel: 'Lihat Presensi',
            tone: 'blue',
            targetType: 'section',
            target: 'attendance-board-section',
        });
    }

    if (props.reviewBoard.length > 0) {
        items.push({
            id: 'review-board',
            title: `${props.reviewBoard.length} logbook menunggu review`,
            description:
                'Utamakan logbook yang antre review.',
            actionLabel: 'Review',
            tone: 'amber',
            targetType: 'section',
            target: 'review-board-section',
        });
    }

    if ((props.summary.not_evaluated ?? 0) > 0) {
        items.push({
            id: 'assessment',
            title: `${props.summary.not_evaluated} mahasiswa siap dinilai belum diberi nilai`,
            description:
                'Lengkapi penilaian akhir.',
            actionLabel: 'Isi Nilai',
            tone: 'slate',
            targetType: 'assessment-index',
        });
    }

    if (items.length === 0) {
        items.push({
            id: 'all-clear',
            title: 'Tidak ada notifikasi prioritas',
            description:
                'Operasional harian aman.',
            tone: 'emerald',
        });
    }

    return items;
});

const actionableNotificationCount = computed(
    () => notificationItems.value.filter((item) => item.actionLabel).length,
);

const toggleNotifications = () => {
    isNotificationOpen.value = !isNotificationOpen.value;
};

const closeNotifications = () => {
    isNotificationOpen.value = false;
};

const scrollToSection = (target?: string) => {
    if (!target || typeof document === 'undefined') {
        return;
    }

    document.getElementById(target)?.scrollIntoView({
        behavior: 'smooth',
        block: 'start',
    });
};

const handleNotificationAction = (item: DashboardNotificationItem) => {
    closeNotifications();

    if (item.targetType === 'section') {
        scrollToSection(item.target);
        return;
    }

    if (item.targetType === 'assessment-index') {
        router.visit('/wims/mitra/penilaian-mahasiswa');
        return;
    }

    if (item.targetType === 'monitoring-index') {
        openMonitoringIndex(item.status);
    }
};

const handleClickOutsideNotification = (event: MouseEvent) => {
    const target = event.target;

    if (!(target instanceof Node)) {
        return;
    }

    if (!notificationMenuRef.value?.contains(target)) {
        closeNotifications();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutsideNotification);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutsideNotification);
});
</script>

<template>
    <Head title="Dashboard Mitra" />

    <div class="min-h-screen bg-wims-bg">
        <div class="mx-auto w-full max-w-[1320px] space-y-4 px-4 py-3 lg:space-y-5 sm:px-6 sm:py-6 lg:px-8 lg:py-8 xl:px-10">
            <header class="relative overflow-hidden rounded-2xl border border-wims-border/50 bg-wims-card/95 px-4 py-4 shadow-[0_1px_3px_rgba(0,0,0,0.04)] sm:px-6 sm:py-6">
                <div class="flex flex-col gap-2.5 sm:gap-3">
                    <div class="min-w-0 max-w-3xl">
                        <h1 class="text-[18px] font-bold tracking-tight text-slate-950 dark:text-slate-100 sm:text-[22px] lg:text-[28px]">
                            Ringkasan Operasional Hari Ini
                        </h1>
                        <p class="mt-1.5 max-w-2xl text-[12px] leading-5 text-slate-600 dark:text-slate-400 sm:text-[13px]">
                            Pantau PKL, presensi, logbook, dan tindak lanjut hari ini.
                        </p>
                    </div>
                </div>
            </header>

            <section class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5 lg:gap-4">
                <Card
                    v-for="card in summaryCards"
                    :key="card.label"
                    class="overflow-hidden rounded-2xl border py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_20px_42px_-30px_rgba(15,23,42,0.25)]"
                    :class="card.cardClass"
                >
                    <CardContent class="px-4 py-4 sm:px-5">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-[10px] font-medium uppercase tracking-[0.08em] text-slate-500 dark:text-slate-400">
                                {{ card.label }}
                            </p>
                            <div
                                class="flex size-9 shrink-0 items-center justify-center rounded-xl border"
                                :class="card.iconClass"
                            >
                                <component :is="card.icon" class="size-4" />
                            </div>
                        </div>
                        <p class="mt-1.5 text-[22px] font-bold tracking-tight sm:text-3xl" :class="card.tone">
                            {{ card.value }}
                        </p>
                        <p class="mt-2 text-[11px] leading-5 text-slate-600 dark:text-slate-400">
                            {{ card.caption }}
                        </p>
                    </CardContent>
                </Card>
            </section>

            <section id="warning-section" class="space-y-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-3xl">
                        <h2 class="text-[14px] font-bold text-wims-text">
                            Perlu Tindak Lanjut
                        </h2>
                        <p class="mt-1 text-[11px] leading-5 text-slate-600 dark:text-slate-400">
                            Mahasiswa aktif yang perlu dicek.
                        </p>
                    </div>
                    <Badge
                        variant="outline"
                        class="w-fit rounded-full border-amber-200 bg-amber-50 px-3 py-1 text-[11px] font-bold text-amber-700 dark:bg-amber-500/15 dark:text-amber-300"
                    >
                        {{ props.warnings.length }} kasus
                    </Badge>
                </div>

                <Card class="rounded-2xl border border-amber-100 bg-wims-card py-0 shadow-[0_20px_44px_-32px_rgba(245,158,11,0.45)] dark:border-amber-500/20">
                    <CardContent class="space-y-4 px-4 py-4 sm:px-5 sm:py-5">
                        <button
                            v-for="warning in props.warnings"
                            :key="warning.pendaftaran_id ?? warning.student_id ?? warning.name ?? 'warning-item'"
                            type="button"
                            class="flex w-full flex-col gap-3 rounded-xl border border-amber-200/80 bg-amber-50/85 px-3.5 py-3 text-left transition-all duration-300 hover:border-amber-300 hover:bg-amber-50 lg:flex-row lg:items-center lg:justify-between dark:bg-amber-500/10"
                            @click="openWarningMonitoring(warning)"
                        >
                            <div class="min-w-0">
                                <p class="text-[12px] font-bold text-wims-text">
                                    {{ warning.name || 'Mahasiswa' }}
                                </p>
                                <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                                    {{ warning.nim || '-' }} • {{ warning.company?.name || 'Perusahaan belum tersedia' }}
                                </p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span
                                        v-for="detail in warningDetails(warning)"
                                        :key="detail"
                                        class="rounded-full border border-amber-200 bg-white px-2.5 py-1 text-[11px] font-medium text-amber-800 dark:bg-amber-500/10 dark:text-amber-200"
                                    >
                                        {{ detail }}
                                    </span>
                                </div>
                            </div>

                            <span class="rounded-full border border-amber-200 bg-white px-3 py-1 text-[11px] font-bold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
                                Periksa
                            </span>
                        </button>

                        <p
                            v-if="!props.warnings.length"
                            class="rounded-xl border border-wims-border bg-slate-50 px-4 py-3 text-[11px] text-slate-500 dark:bg-slate-800/50 dark:text-slate-400"
                        >
                            Belum ada data monitoring.
                        </p>
                    </CardContent>
                </Card>
            </section>

            <section class="grid items-start gap-4 md:grid-cols-2 xl:grid-cols-3">
                <Card
                    id="pending-absence-section"
                    class="self-start rounded-2xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)]"
                >
                    <CardContent class="space-y-4 px-4 py-4 sm:px-5 sm:py-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-[14px] font-bold text-wims-text">
                                    Pengajuan Ketidakhadiran
                                </h2>
                                <p class="mt-1 text-[11px] leading-5 text-slate-600 dark:text-slate-400">
                                    Izin dan sakit yang menunggu keputusan.
                                </p>
                            </div>
                            <Badge
                                variant="outline"
                                class="rounded-full border-orange-200 bg-orange-50 px-3 py-1 text-[11px] font-bold text-orange-700 dark:bg-orange-500/15 dark:text-orange-300"
                            >
                                {{ props.pendingAbsenceRequests.length }} menunggu
                            </Badge>
                        </div>

                        <div class="space-y-2.5">
                            <div
                                v-for="item in props.pendingAbsenceRequests"
                                :key="item.id"
                                class="rounded-xl border border-orange-200/80 bg-orange-50/70 px-3.5 py-3 transition-all duration-300 hover:border-orange-300 dark:bg-orange-500/10"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-[12px] font-bold text-wims-text">
                                            {{ item.name || 'Mahasiswa' }}
                                        </p>
                                        <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">
                                            {{ item.nim || '-' }} • {{ item.company?.name || 'Perusahaan belum tersedia' }}
                                        </p>
                                    </div>
                                    <Badge
                                        variant="outline"
                                        class="rounded-full border-orange-200 bg-white px-2.5 py-0.5 text-[11px] font-bold text-orange-700 dark:bg-orange-500/10 dark:text-orange-300"
                                    >
                                        {{ absenceKindLabel(item.jenis) }}
                                    </Badge>
                                </div>

                                <p class="mt-2 text-[11px] font-medium text-orange-700 dark:text-orange-300">
                                    {{ formatDateUi(item.tanggal_label) }}
                                </p>
                                <p class="mt-1.5 text-[11px] leading-5 text-slate-700 dark:text-slate-300">
                                    {{ item.alasan || '-' }}
                                </p>

                                <div class="mt-2.5 grid gap-2 sm:grid-cols-2">
                                    <button
                                        type="button"
                                        class="h-9 rounded-lg bg-emerald-600 px-3 text-[13px] font-bold text-white transition hover:bg-emerald-700 disabled:bg-slate-300 disabled:text-slate-600"
                                        :disabled="absenceReviewForm.processing"
                                        @click="approveAbsenceRequest(item.id)"
                                    >
                                        Setujui
                                    </button>
                                    <button
                                        type="button"
                                        class="h-9 rounded-lg bg-rose-600 px-3 text-[13px] font-bold text-white transition hover:bg-rose-700 disabled:bg-slate-300 disabled:text-slate-600"
                                        :disabled="absenceReviewForm.processing"
                                        @click="rejectAbsenceRequest(item.id)"
                                    >
                                        Tolak
                                    </button>
                                </div>
                            </div>

                            <p
                                v-if="!props.pendingAbsenceRequests.length"
                                class="rounded-xl border border-wims-border bg-slate-50 px-4 py-3 text-[11px] leading-5 text-slate-500 dark:bg-slate-800/50 dark:text-slate-400"
                            >
                                Tidak ada pengajuan izin atau sakit yang menunggu persetujuan saat ini.
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card
                    id="attendance-board-section"
                    class="self-start rounded-xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)]"
                >
                    <CardContent class="space-y-3 px-4 py-4 sm:px-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-[14px] font-bold text-wims-text">
                                    Presensi Hari Ini
                                </h2>
                                <p class="mt-1 text-[11px] leading-5 text-slate-600 dark:text-slate-400">
                                    Ringkasan presensi harian.
                                </p>
                            </div>
                            <Badge
                                variant="outline"
                                class="rounded-full border-blue-200 bg-blue-50 px-3 py-1 text-[11px] font-bold text-blue-700 dark:bg-blue-500/15 dark:text-blue-300"
                            >
                                {{ props.attendanceBoard.length }} aktif
                            </Badge>
                        </div>

                        <div v-if="props.attendanceBoard.length" class="space-y-2.5">
                                    <div
                                        v-for="student in props.attendanceBoard"
                                        :key="student.registration_id"
                                        class="rounded-xl border border-wims-border bg-slate-50 px-3.5 py-3 transition-all duration-300 hover:bg-white dark:bg-slate-800/50 dark:hover:bg-slate-800/60"
                                    >
                                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                            <div class="min-w-0">
                                                <p class="text-[12px] font-bold text-wims-text">
                                                    {{ student.name || 'Mahasiswa' }}
                                                </p>
                                                <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">{{ student.nim || '-' }}</p>
                                            </div>
                                            <Badge
                                                variant="outline"
                                                class="w-fit rounded-full px-3 py-1 text-[11px] font-bold"
                                                :class="attendanceClass(student.attendance_status)"
                                            >
                                                {{ attendanceLabel(student.attendance_status) }}
                                            </Badge>
                                        </div>
                                        <div class="mt-2 grid gap-2 text-[11px] text-slate-600 dark:text-slate-400 sm:grid-cols-2">
                                            <p>Masuk: <span class="font-bold text-wims-text">{{ formatTimeUi(student.check_in_time) }}</span></p>
                                            <p>Pulang: <span class="font-bold text-wims-text">{{ formatTimeUi(student.check_out_time) }}</span></p>
                                        </div>
                                    </div>
                        </div>
                        <p
                            v-else
                            class="rounded-xl border border-wims-border bg-slate-50 px-4 py-3 text-[11px] leading-5 text-slate-500 dark:bg-slate-800/50 dark:text-slate-400"
                        >
                            Belum ada data presensi.
                        </p>
                    </CardContent>
                </Card>

                <Card
                    id="review-board-section"
                    class="self-start rounded-xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)]"
                >
                    <CardContent class="space-y-3 px-4 py-4 sm:px-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-[14px] font-bold text-wims-text">
                                    Logbook Terbaru
                                </h2>
                                <p class="mt-1 text-[11px] leading-5 text-slate-600 dark:text-slate-400">
                                    Entri yang perlu review.
                                </p>
                            </div>
                            <Badge
                                variant="outline"
                                class="rounded-full border-amber-200 bg-amber-50 px-3 py-1 text-[11px] font-bold text-amber-700 dark:bg-amber-500/15 dark:text-amber-300"
                            >
                                {{ props.reviewBoard.length }} antrean
                            </Badge>
                        </div>

                        <div class="space-y-2.5">
                            <div
                                v-for="student in props.reviewBoard"
                                :key="student.registration_id"
                                class="rounded-xl border border-amber-200/80 bg-amber-50/70 px-3.5 py-3 transition-all duration-300 hover:border-amber-300 dark:bg-amber-500/10"
                            >
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="min-w-0">
                                            <p class="text-[12px] font-bold text-wims-text">
                                                {{ student.name || 'Mahasiswa' }}
                                            </p>
                                            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                                                {{ student.nim || '-' }} • {{ student.company?.name || 'Perusahaan belum tersedia' }}
                                            </p>
                                        </div>
                                        <Badge
                                            variant="outline"
                                            class="w-fit rounded-full px-3 py-1 text-[11px] font-bold"
                                            :class="logbookClass(student.logbook_status)"
                                        >
                                            {{ logbookLabel(student.logbook_status) }}
                                        </Badge>
                                    </div>
                                    <p class="mt-2 line-clamp-2 text-[11px] leading-5 text-slate-700 dark:text-slate-300">
                                        {{ student.latest_logbook_activity || 'Belum ada ringkasan aktivitas logbook terakhir.' }}
                                    </p>
                                    <div class="mt-2.5 flex justify-end">
                                        <Button
                                            type="button"
                                            class="h-9 rounded-lg bg-[#0F62FE] px-3 text-[13px] font-bold text-white hover:bg-[#0050E6]"
                                            @click="openMonitoring(student)"
                                        >
                                            Lihat Monitoring
                                        </Button>
                                    </div>
                            </div>

                            <p
                                v-if="!props.reviewBoard.length"
                                class="rounded-xl border border-wims-border bg-slate-50 px-4 py-3 text-[11px] leading-5 text-slate-500 dark:bg-slate-800/50 dark:text-slate-400"
                            >
                                Belum ada logbook terbaru.
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </section>

            <section
                v-for="section in studentSections"
                :key="section.key"
                class="space-y-4"
            >
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div class="max-w-3xl">
                        <h2 class="text-[14px] font-bold text-wims-text">
                            {{ section.title }}
                        </h2>
                        <p class="mt-1 text-[11px] leading-5 text-slate-600 dark:text-slate-400">
                            {{ section.description }}
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                        <Badge
                            variant="outline"
                            class="w-fit rounded-full border-wims-border bg-slate-50 px-3 py-1 text-[11px] font-bold text-slate-700 dark:bg-slate-800/50 dark:text-slate-300"
                        >
                            {{ section.students.length }} mahasiswa
                        </Badge>
                        <p
                            v-if="section.students.length > 3"
                            class="text-[11px] text-slate-500 dark:text-slate-400"
                        >
                            3 dari {{ section.students.length }} ditampilkan.
                        </p>
                        <Button
                            type="button"
                            variant="outline"
                            class="h-9 rounded-lg border-wims-border bg-wims-card px-3 text-[13px] font-bold text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-700/30"
                            @click="openMonitoringIndex(section.key === 'active' ? 'aktif' : 'selesai')"
                        >
                            Lihat Semua
                        </Button>
                    </div>
                </div>

                <Card
                    v-if="section.students.length"
                    class="rounded-xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)]"
                >
                    <CardContent class="space-y-3 px-4 py-4 sm:px-5">
                        <div class="space-y-3">
                            <div
                                v-for="student in previewStudents(section.students)"
                                :key="student.registration_id"
                                class="rounded-xl border border-wims-border bg-slate-50 px-3.5 py-3 transition-all duration-300 hover:border-slate-300 hover:bg-white dark:bg-slate-800/50 dark:hover:bg-slate-800/60"
                            >
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                    <div class="min-w-0 lg:flex-1">
                                        <p class="text-[12px] font-bold text-wims-text">
                                            {{ student.name || 'Mahasiswa' }}
                                        </p>
                                        <div class="mt-1 flex flex-col gap-1 text-[11px] text-slate-500 dark:text-slate-400 sm:flex-row sm:flex-wrap sm:items-center sm:gap-x-3 sm:gap-y-1">
                                            <span>{{ student.nim || '-' }}</span>
                                            <span class="hidden text-slate-300 sm:inline">•</span>
                                            <span>{{ student.company?.name || '-' }}</span>
                                            <span class="hidden text-slate-300 sm:inline">•</span>
                                            <span>Periode {{ periodLabel(student) }}</span>
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-2 lg:min-w-0 lg:flex-row lg:items-center lg:justify-end">
                                        <template v-if="section.key === 'active'">
                                            <div class="flex flex-wrap gap-2 lg:justify-end">
                                                <div class="flex items-center gap-1.5 rounded-full border border-wims-border bg-wims-card px-2.5 py-1">
                                                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Presensi</span>
                                                    <Badge
                                                        variant="outline"
                                                        class="rounded-full px-2 py-0.5 text-[11px] font-bold"
                                                        :class="attendanceClass(student.attendance_status)"
                                                    >
                                                        {{ attendanceLabel(student.attendance_status) }}
                                                    </Badge>
                                                </div>
                                                <div class="flex items-center gap-1.5 rounded-full border border-wims-border bg-wims-card px-2.5 py-1">
                                                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Logbook</span>
                                                    <Badge
                                                        variant="outline"
                                                        class="rounded-full px-2 py-0.5 text-[11px] font-bold"
                                                        :class="logbookClass(student.logbook_status)"
                                                    >
                                                        {{ logbookLabel(student.logbook_status) }}
                                                    </Badge>
                                                </div>
                                                <div class="flex items-center gap-1.5 rounded-full border border-wims-border bg-wims-card px-2.5 py-1">
                                                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Laporan</span>
                                                    <Badge
                                                        variant="outline"
                                                        class="rounded-full px-2 py-0.5 text-[11px] font-bold"
                                                        :class="reportStatusClass(student.objective_summary?.final_report_uploaded)"
                                                    >
                                                        {{ reportStatusLabel(student.objective_summary?.final_report_uploaded) }}
                                                    </Badge>
                                                </div>
                                            </div>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                class="hidden h-9 rounded-lg border-wims-border bg-wims-card px-3 text-[13px] font-bold text-slate-700 hover:bg-slate-50 sm:inline-flex dark:text-slate-300 dark:hover:bg-slate-700/30"
                                                @click="openMonitoring(student)"
                                            >
                                                Lihat Monitoring
                                            </Button>
                                        </template>

                                        <template v-else>
                                            <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap lg:justify-end">
                                                <div class="flex min-w-0 items-center justify-between gap-2 rounded-xl border border-wims-border bg-wims-card px-3 py-2 sm:justify-start sm:gap-1.5 sm:rounded-full sm:px-2.5 sm:py-1">
                                                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Penilaian</span>
                                                    <Badge
                                                        variant="outline"
                                                        class="rounded-full px-2 py-0.5 text-[11px] font-bold"
                                                        :class="evaluationStatusClass(student.objective_summary?.evaluation_status)"
                                                    >
                                                        {{ evaluationStatusLabel(student.objective_summary?.evaluation_status) }}
                                                    </Badge>
                                                </div>
                                                <div class="flex min-w-0 items-center justify-between gap-2 rounded-xl border border-wims-border bg-wims-card px-3 py-2 sm:justify-start sm:gap-1.5 sm:rounded-full sm:px-2.5 sm:py-1">
                                                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Nilai</span>
                                                    <span class="text-[12px] font-bold text-wims-text">
                                                        {{
                                                            student.objective_summary?.evaluation_total_score !== null &&
                                                            student.objective_summary?.evaluation_total_score !== undefined
                                                                ? Number(student.objective_summary.evaluation_total_score).toFixed(2)
                                                                : '-'
                                                        }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="hidden sm:flex sm:items-center sm:gap-2">
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    class="h-9 rounded-lg border-wims-border bg-wims-card px-3 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-700/30"
                                                    @click="openMonitoring(student)"
                                                >
                                                    Lihat Monitoring
                                                </Button>
                                                <Button
                                                    v-if="canAssess(student)"
                                                    type="button"
                                                    class="h-9 rounded-lg bg-[#0F62FE] px-3 text-sm font-bold text-white hover:bg-[#0050E6]"
                                                    @click="openAssessment(student)"
                                                >
                                                    {{ assessmentActionLabel(student) }}
                                                </Button>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div class="mt-3 grid grid-cols-2 gap-2 border-t border-wims-border pt-3 sm:hidden">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="h-9 w-full rounded-lg border-wims-border bg-wims-card px-3 text-[12px] font-bold text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-700/30"
                                        @click="openMonitoring(student)"
                                    >
                                        Lihat Monitoring
                                    </Button>
                                    <Button
                                        v-if="section.key === 'completed' && canAssess(student)"
                                        type="button"
                                        class="h-9 w-full rounded-lg bg-[#0F62FE] px-3 text-[12px] font-bold text-white hover:bg-[#0050E6]"
                                        @click="openAssessment(student)"
                                    >
                                        {{ assessmentActionLabel(student) }}
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="remainingPreviewCount(section.students) > 0"
                            class="rounded-xl border border-wims-border bg-slate-50 px-4 py-3 text-sm text-slate-600 dark:bg-slate-800/50 dark:text-slate-400"
                        >
                            {{ previewStudents(section.students).length }} dari {{ section.students.length }} ditampilkan.
                        </div>
                    </CardContent>
                </Card>

                <p
                    v-else
                    class="rounded-xl border border-wims-border bg-wims-card px-4 py-5 text-center text-[11px] text-slate-500 dark:text-slate-400"
                >
                    {{ section.empty }}
                </p>
            </section>
        </div>
    </div>
</template>




