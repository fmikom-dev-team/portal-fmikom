<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Bell,
    CheckCheck,
    FileClock,
    Users,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { formatIndonesianDateLabel } from '@/lib/date';
import WimsDosenLayout from '@/layouts/Modules/Wims/Dosen/Layout.vue';
import wimsRoutes from '@/routes/wims';

defineOptions({
    layout: WimsDosenLayout,
});

type SummaryProps = {
    total_students?: number;
    upcoming_students?: number;
    active_students?: number;
    completed_students?: number;
    needs_attention?: number;
    not_present?: number;
    active_warnings?: number;
    not_evaluated?: number;
};

type ObjectiveSummary = {
    final_report_uploaded?: boolean;
    evaluation_status?: string | null;
    evaluation_total_score?: number | null;
};

type StudentItem = {
    id: number;
    pendaftaran_id?: number | null;
    name?: string | null;
    nim?: string | null;
    company?: string | null;
    period_start?: string | null;
    period_end?: string | null;
    status_pendaftaran?: string | null;
    dashboard_phase?: 'assigned' | 'upcoming' | 'active' | 'completed' | null;
    attendance_status?: string | null;
    check_in_time?: string | null;
    check_out_time?: string | null;
    logbook_status?: string | null;
    has_logbook?: boolean;
    latest_logbook_date?: string | null;
    latest_logbook_activity?: string | null;
    today_date?: string | null;
    objective_summary?: ObjectiveSummary | null;
};

type WarningItem = {
    student_id?: number | null;
    pendaftaran_id?: number | null;
    name?: string | null;
    nim?: string | null;
    company?: string | null;
    attendance_missing_days?: number;
    logbook_missing_days?: number;
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
    summary: SummaryProps;
    students: StudentItem[];
    warnings: WarningItem[];
}>();

const isNotificationOpen = ref(false);
const notificationMenuRef = ref<HTMLElement | null>(null);

const summaryCards = computed(() => [
    {
        label: 'Aktif',
        value: props.summary.active_students ?? 0,
        caption: 'Mahasiswa yang sedang menjalani kegiatan PKL.',
        tone: 'text-sky-700',
        cardClass: 'border-blue-100/80 bg-blue-50/45',
        iconClass: 'border-blue-100 bg-blue-100 text-blue-600',
        icon: Users,
    },
    {
        label: 'Selesai',
        value: props.summary.completed_students ?? 0,
        caption: 'Mahasiswa yang sudah selesai dan siap ditinjau nilainya.',
        tone: 'text-violet-700',
        cardClass: 'border-violet-100/80 bg-violet-50/45',
        iconClass: 'border-violet-100 bg-violet-100 text-violet-600',
        icon: CheckCheck,
    },
    {
        label: 'Perlu Monitoring',
        value: props.summary.active_warnings ?? 0,
        caption: 'Kasus presensi atau logbook yang perlu diperiksa dosen.',
        tone: 'text-amber-700',
        cardClass: 'border-amber-100/80 bg-amber-50/45',
        iconClass: 'border-amber-100 bg-amber-100 text-amber-600',
        icon: AlertTriangle,
    },
    {
        label: 'Belum Dinilai',
        value: props.summary.not_evaluated ?? 0,
        caption: 'Mahasiswa selesai yang penilaian dosennya belum final.',
        tone: 'text-slate-700',
        cardClass: 'border-slate-200 bg-slate-50/80',
        iconClass: 'border-slate-200 bg-white text-slate-500',
        icon: FileClock,
    },
]);

const activeStudents = computed(() =>
    props.students.filter((student) => student.dashboard_phase === 'active'),
);

const completedStudents = computed(() =>
    props.students.filter((student) => student.dashboard_phase === 'completed'),
);

const warningPreview = computed(() => props.warnings.slice(0, 3));

const attendanceBoard = computed(() =>
    activeStudents.value.filter((student) =>
        ['hadir', 'terlambat', 'izin', 'sakit', 'alfa'].includes(student.attendance_status ?? ''),
    ).slice(0, 4),
);

const logbookBoard = computed(() =>
    activeStudents.value.filter((student) =>
        student.has_logbook || ['belum_isi', 'revisi', 'menunggu_review'].includes(student.logbook_status ?? ''),
    ).slice(0, 4),
);

const previewStudents = (students: StudentItem[]) => students.slice(0, 3);

const remainingPreviewCount = (students: StudentItem[]) =>
    Math.max(students.length - 3, 0);

const remainingWarningCount = computed(() =>
    Math.max(props.warnings.length - warningPreview.value.length, 0),
);

const formatDateUi = (value?: string | null) => formatIndonesianDateLabel(value);

const attendanceLabel = (status?: string | null) => {
    if (status === 'belum_mulai') return 'Belum Mulai';
    if (status === 'terlambat') return 'Terlambat';
    if (status === 'izin') return 'Izin';
    if (status === 'sakit') return 'Sakit';
    if (status === 'alfa') return 'Alfa';
    return 'Hadir';
};

const attendanceClass = (status?: string | null) => {
    if (status === 'belum_mulai') return 'border-slate-200 bg-slate-50 text-slate-600';
    if (status === 'terlambat') return 'border-amber-200 bg-amber-50 text-amber-700';
    if (status === 'izin') return 'border-blue-200 bg-blue-50 text-blue-700';
    if (status === 'sakit') return 'border-violet-200 bg-violet-50 text-violet-700';
    if (status === 'alfa') return 'border-rose-200 bg-rose-50 text-rose-700';
    return 'border-emerald-200 bg-emerald-50 text-emerald-700';
};

const logbookLabel = (status?: string | null) => {
    if (status === 'belum_mulai') return 'Belum Mulai';
    if (status === 'disetujui') return 'Disetujui';
    if (status === 'revisi') return 'Revisi';
    if (status === 'belum_isi') return 'Belum Isi';
    return 'Menunggu Tinjauan Mitra';
};

const logbookClass = (status?: string | null) => {
    if (status === 'belum_mulai') return 'border-slate-200 bg-slate-50 text-slate-600';
    if (status === 'disetujui') return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    if (status === 'revisi') return 'border-rose-200 bg-rose-50 text-rose-700';
    if (status === 'belum_isi') return 'border-slate-200 bg-slate-50 text-slate-600';
    return 'border-amber-200 bg-amber-50 text-amber-700';
};

const evaluationStatusLabel = (status?: string | null) => {
    if (status === 'submitted') return 'Sudah Dikirim';
    if (status === 'draft') return 'Draft';
    return 'Belum Dinilai';
};

const evaluationStatusClass = (status?: string | null) => {
    if (status === 'submitted') return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    if (status === 'draft') return 'border-amber-200 bg-amber-50 text-amber-700';
    return 'border-slate-200 bg-slate-50 text-slate-600';
};

const reportStatusLabel = (uploaded?: boolean) =>
    uploaded ? 'Sudah Diunggah' : 'Belum Diunggah';

const reportStatusClass = (uploaded?: boolean) =>
    uploaded
        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
        : 'border-slate-200 bg-slate-50 text-slate-600';

const periodLabel = (student: StudentItem) => {
    if (student.period_start && student.period_end) {
        return `${formatDateUi(student.period_start)} - ${formatDateUi(student.period_end)}`;
    }

    return formatDateUi(student.period_start || student.period_end);
};

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

const warningBadges = (warning: WarningItem) => {
    const parts: Array<{ label: string; class: string }> = [];

    if ((warning.attendance_missing_days ?? 0) >= 3) {
        parts.push({
            label: `Belum presensi ${warning.attendance_missing_days} hari`,
            class: 'border-amber-200 bg-amber-50 text-amber-700',
        });
    }

    if ((warning.logbook_missing_days ?? 0) >= 3) {
        parts.push({
            label: `Belum logbook ${warning.logbook_missing_days} hari`,
            class: 'border-rose-200 bg-rose-50 text-rose-700',
        });
    }

    return parts;
};

const warningDetails = (warning: WarningItem) =>
    warningBadges(warning).map((badge) => badge.label);

const openMonitoring = (student: StudentItem) => {
    if (!student.id) {
        return;
    }

    router.visit(
        wimsRoutes.dosen.monitoring.show(student.id, {
            query: {
                pendaftaran: student.pendaftaran_id,
                mode: 'view',
            },
        }).url,
    );
};

const openAssessment = (student: StudentItem) => {
    if (!student.pendaftaran_id) {
        return;
    }

    router.visit(`/wims/dosen/penilaian-mahasiswa/${student.pendaftaran_id}?from=dashboard`);
};

const openWarningMonitoring = (warning: WarningItem) => {
    if (!warning.student_id) {
        return;
    }

    router.visit(
        wimsRoutes.dosen.monitoring.show(warning.student_id, {
            query: {
                pendaftaran: warning.pendaftaran_id,
                mode: 'view',
            },
        }).url,
    );
};

const openMonitoringIndex = (status?: 'aktif' | 'selesai') => {
    const url = status
        ? wimsRoutes.dosen.monitoring.index({
            query: {
                status,
            },
        }).url
        : wimsRoutes.dosen.monitoring.index().url;

    router.visit(url);
};

const notificationToneClass = (
    tone: DashboardNotificationItem['tone'],
) => {
    if (tone === 'rose') {
        return 'border-rose-200 bg-rose-50 text-rose-700';
    }

    if (tone === 'blue') {
        return 'border-blue-200 bg-blue-50 text-blue-700';
    }

    if (tone === 'amber') {
        return 'border-amber-200 bg-amber-50 text-amber-700';
    }

    if (tone === 'emerald') {
        return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    }

    return 'border-slate-200 bg-slate-50 text-slate-700';
};

const notificationItems = computed<DashboardNotificationItem[]>(() => {
    const items: DashboardNotificationItem[] = [];
    const firstWarning = props.warnings[0];
    const alfaStudents = activeStudents.value.filter(
        (student) => student.attendance_status === 'alfa',
    );

    if (props.warnings.length > 0) {
        items.push({
            id: 'warnings',
            title: `${props.warnings.length} mahasiswa perlu monitoring`,
            description: firstWarning
                ? `${firstWarning.name || 'Mahasiswa'}${
                      warningDetails(firstWarning).length
                          ? `: ${warningDetails(firstWarning).join(', ')}.`
                          : ' perlu segera diperiksa.'
                  }`
                : 'Ada mahasiswa dengan keterlambatan presensi atau logbook.',
            actionLabel: 'Periksa',
            tone: 'rose',
            targetType: 'section',
            target: 'warning-section',
        });
    }

    if (alfaStudents.length > 0) {
        items.push({
            id: 'attendance-alfa',
            title: `${alfaStudents.length} mahasiswa alfa hari ini`,
            description: 'Cek board presensi hari ini untuk melihat mahasiswa yang belum hadir.',
            actionLabel: 'Lihat Presensi',
            tone: 'blue',
            targetType: 'section',
            target: 'attendance-board-section',
        });
    }

    if (logbookBoard.value.length > 0) {
        items.push({
            id: 'logbook-board',
            title: `${logbookBoard.value.length} logbook terbaru tersedia`,
            description: 'Pantau ringkasan logbook mahasiswa aktif dari board logbook terbaru.',
            actionLabel: 'Lihat Logbook',
            tone: 'amber',
            targetType: 'section',
            target: 'logbook-board-section',
        });
    }

    if ((props.summary.not_evaluated ?? 0) > 0) {
        items.push({
            id: 'assessment',
            title: `${props.summary.not_evaluated} mahasiswa selesai belum dinilai`,
            description: 'Lengkapi penilaian dosen agar proses akhir mahasiswa tidak tertunda.',
            actionLabel: 'Isi Nilai',
            tone: 'slate',
            targetType: 'assessment-index',
        });
    }

    if (items.length === 0) {
        items.push({
            id: 'all-clear',
            title: 'Tidak ada notifikasi saat ini',
            description: 'Monitoring dan penilaian mahasiswa bimbingan saat ini relatif aman.',
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
        router.visit('/wims/dosen/penilaian-mahasiswa');
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
    <Head title="Dashboard Dosen" />

    <div class="min-h-screen bg-wims-bg">
        <div class="mx-auto w-full max-w-[1320px] space-y-4 px-4 py-3 lg:space-y-5 sm:px-6 sm:py-6 lg:px-8 lg:py-8 xl:px-10">
            <header class="relative overflow-hidden rounded-2xl border border-wims-border/50 bg-wims-card/95 px-5 py-5 shadow-[0_1px_3px_rgba(0,0,0,0.04)] sm:px-6 sm:py-6">
                <div class="flex flex-col gap-3">
                    <div class="min-w-0 max-w-3xl">
                        <h1 class="text-[17px] font-bold tracking-tight text-slate-950 sm:text-[20px]">
                            Ringkasan Bimbingan Hari Ini
                        </h1>
                        <p class="mt-1.5 max-w-2xl text-[12px] leading-5 text-slate-600 sm:text-sm sm:leading-6">
                            Pantau progres mahasiswa bimbingan, presensi, logbook, laporan, dan penilaian dosen.
                        </p>
                    </div>
                </div>
            </header>

            <section class="grid grid-cols-2 gap-3 lg:gap-4 xl:grid-cols-4">
                <Card
                    v-for="card in summaryCards"
                    :key="card.label"
                    class="rounded-2xl border py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)] transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_22px_42px_-30px_rgba(15,23,42,0.22)]"
                    :class="card.cardClass"
                >
                    <CardContent class="px-4 py-4 sm:px-5 sm:py-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-500 sm:text-xs">{{ card.label }}</p>
                                <p class="mt-1.5 text-[24px] font-bold tracking-tight sm:text-3xl" :class="card.tone">
                                    {{ card.value }}
                                </p>
                            </div>
                            <div
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl border"
                                :class="card.iconClass"
                            >
                                <component :is="card.icon" class="size-4" />
                            </div>
                        </div>
                        <p class="mt-2 text-[11px] leading-5 text-slate-500 sm:text-xs">
                            {{ card.caption }}
                        </p>
                    </CardContent>
                </Card>
            </section>

            <section id="warning-section" class="space-y-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div class="max-w-3xl">
                        <h2 class="text-base font-bold text-wims-text">
                            Perlu Monitoring
                        </h2>
                        <p class="mt-1 text-sm leading-6 text-slate-600">
                            Mahasiswa yang melewati ambang keterlambatan presensi atau logbook dan perlu dipantau.
                        </p>
                    </div>
                    <Badge
                        variant="outline"
                        class="w-fit rounded-full border-amber-200 bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700"
                    >
                        {{ props.warnings.length }} kasus
                    </Badge>
                </div>

                <Card class="rounded-2xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)]">
                    <CardContent class="space-y-3 px-5 py-5 sm:px-6">
                        <button
                            v-for="warning in warningPreview"
                            :key="warning.pendaftaran_id ?? warning.student_id ?? warning.name ?? 'warning-item'"
                            type="button"
                            class="flex w-full items-start justify-between gap-3 rounded-xl border border-amber-200/80 bg-amber-50/75 px-3.5 py-2.5 text-left transition hover:border-amber-300 hover:bg-amber-50"
                            @click="openWarningMonitoring(warning)"
                        >
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-wims-text">
                                    {{ warning.name || 'Mahasiswa' }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ warning.nim || '-' }} &bull; {{ warning.company || 'Perusahaan belum tersedia' }}
                                </p>
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    <span
                                        v-for="badge in warningBadges(warning)"
                                        :key="badge.label"
                                        class="inline-flex max-w-full rounded-full border px-2.5 py-1 text-[11px] font-bold leading-4"
                                        :class="badge.class"
                                    >
                                        {{ badge.label }}
                                    </span>
                                </div>
                            </div>
                            <span class="rounded-full border border-amber-200 bg-white px-3 py-1 text-[11px] font-bold text-amber-700">
                                Periksa
                            </span>
                        </button>

                        <div
                            v-if="remainingWarningCount > 0"
                            class="flex flex-col gap-2 rounded-xl border border-wims-border bg-slate-50 px-4 py-3 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <p>
                                {{ remainingWarningCount }} kasus lainnya tersedia di halaman monitoring.
                            </p>
                            <Button
                                type="button"
                                variant="outline"
                                class="h-8 rounded-lg border-wims-border bg-white px-3 text-xs font-bold text-slate-700 hover:bg-slate-100"
                                @click="openMonitoringIndex('aktif')"
                            >
                                Lihat Monitoring
                            </Button>
                        </div>

                        <p
                            v-if="!props.warnings.length"
                            class="rounded-xl border border-wims-border bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-500"
                        >
                            Belum ada kasus monitoring yang perlu dipantau saat ini.
                        </p>
                    </CardContent>
                </Card>
            </section>

            <section class="grid items-start gap-4 xl:grid-cols-2">
                <Card
                    id="attendance-board-section"
                    class="self-start rounded-2xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)]"
                >
                    <CardContent class="space-y-4 px-5 py-5 sm:px-6">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-base font-bold text-wims-text">
                                    Board Presensi Hari Ini
                                </h2>
                                <p class="mt-1 text-sm leading-6 text-slate-600">
                                    Ringkasan mahasiswa aktif yang sudah hadir, terlambat, izin, atau belum presensi.
                                </p>
                            </div>
                            <Badge
                                variant="outline"
                                class="rounded-full border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700"
                            >
                                {{ attendanceBoard.length }} aktif
                            </Badge>
                        </div>

                        <div class="space-y-2.5">
                            <div
                                v-for="student in attendanceBoard"
                                :key="student.pendaftaran_id ?? student.id"
                                class="rounded-xl border border-wims-border bg-slate-50 px-3.5 py-3"
                            >
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-wims-text">
                                            {{ student.name || 'Mahasiswa' }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ student.nim || '-' }} &bull; {{ student.company || 'Perusahaan belum tersedia' }}
                                        </p>
                                        <div class="mt-2 grid gap-2 text-xs text-slate-600 sm:grid-cols-2">
                                            <p>Masuk: <span class="font-bold text-wims-text">{{ student.check_in_time || '-' }}</span></p>
                                            <p>Pulang: <span class="font-bold text-wims-text">{{ student.check_out_time || '-' }}</span></p>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-2 sm:items-end">
                                        <Badge
                                            variant="outline"
                                            class="w-fit rounded-full px-3 py-1 text-[11px] font-bold"
                                            :class="attendanceClass(student.attendance_status)"
                                        >
                                            {{ attendanceLabel(student.attendance_status) }}
                                        </Badge>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            class="h-8 rounded-lg border-wims-border bg-white px-3 text-xs font-bold text-slate-700 hover:bg-slate-50"
                                            @click="openMonitoring(student)"
                                        >
                                            Lihat Monitoring
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <p
                                v-if="!attendanceBoard.length"
                                class="rounded-xl border border-wims-border bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-500"
                            >
                                Belum ada mahasiswa aktif yang perlu dipantau pada board presensi hari ini.
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card
                    id="logbook-board-section"
                    class="self-start rounded-2xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)]"
                >
                    <CardContent class="space-y-4 px-5 py-5 sm:px-6">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-base font-bold text-wims-text">
                                    Pemantauan Logbook
                                </h2>
                                <p class="mt-1 text-sm leading-6 text-slate-600">
                                    Lihat status dan ringkasan logbook terbaru mahasiswa bimbingan.
                                </p>
                            </div>
                            <Badge
                                variant="outline"
                                class="rounded-full border-amber-200 bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700"
                            >
                                {{ logbookBoard.length }} dipantau
                            </Badge>
                        </div>

                        <div class="space-y-2.5">
                            <div
                                v-for="student in logbookBoard"
                                :key="student.pendaftaran_id ?? student.id"
                                class="rounded-xl border border-wims-border bg-slate-50 px-3.5 py-3"
                            >
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-wims-text">
                                            {{ student.name || 'Mahasiswa' }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ student.nim || '-' }} &bull; {{ student.latest_logbook_date ? formatDateUi(student.latest_logbook_date) : 'Tanggal logbook belum tersedia' }}
                                        </p>
                                        <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-700">
                                            {{ student.latest_logbook_activity || 'Belum ada ringkasan aktivitas logbook terbaru.' }}
                                        </p>
                                    </div>
                                    <div class="flex flex-col gap-2 sm:items-end">
                                        <Badge
                                            variant="outline"
                                            class="w-fit rounded-full px-3 py-1 text-[11px] font-bold"
                                            :class="logbookClass(student.logbook_status)"
                                        >
                                            {{ logbookLabel(student.logbook_status) }}
                                        </Badge>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            class="h-8 rounded-lg border-wims-border bg-white px-3 text-xs font-bold text-slate-700 hover:bg-slate-50"
                                            @click="openMonitoring(student)"
                                        >
                                            Lihat Monitoring
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <p
                                v-if="!logbookBoard.length"
                                class="rounded-xl border border-wims-border bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-500"
                            >
                                Belum ada logbook terbaru yang perlu dipantau saat ini.
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </section>

            <section class="space-y-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div class="max-w-3xl">
                        <h2 class="text-base font-bold text-wims-text">
                            Mahasiswa Aktif
                        </h2>
                        <p class="mt-1 text-sm leading-6 text-slate-600">
                            Preview mahasiswa yang sedang menjalani PKL pada periode aktif.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                        <Badge
                            variant="outline"
                            class="w-fit rounded-full border-wims-border bg-slate-50 px-3 py-1 text-xs font-bold text-slate-700"
                        >
                            {{ activeStudents.length }} mahasiswa
                        </Badge>
                        <p
                            v-if="activeStudents.length > 3"
                            class="text-[11px] text-slate-500"
                        >
                            Menampilkan 3 dari {{ activeStudents.length }} mahasiswa.
                        </p>
                        <Button
                            type="button"
                            variant="outline"
                            class="h-8 rounded-lg border-wims-border bg-wims-card px-3 text-xs font-bold text-slate-700 hover:bg-slate-50"
                            @click="openMonitoringIndex('aktif')"
                        >
                            Lihat Semua
                        </Button>
                    </div>
                </div>

                <Card
                    v-if="activeStudents.length"
                    class="rounded-xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)]"
                >
                    <CardContent class="space-y-3 px-4 py-4 sm:px-5">
                        <div
                            v-for="student in previewStudents(activeStudents)"
                            :key="student.pendaftaran_id ?? student.id"
                            class="rounded-xl border border-wims-border bg-slate-50 px-3.5 py-3"
                        >
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <div class="min-w-0 lg:flex-1">
                                    <p class="text-sm font-bold text-wims-text">
                                        {{ student.name || 'Mahasiswa' }}
                                    </p>
                                    <div class="mt-1 flex flex-col gap-1 text-xs text-slate-500 sm:flex-row sm:flex-wrap sm:items-center sm:gap-x-3 sm:gap-y-1">
                                        <span>{{ student.nim || '-' }}</span>
                                        <span class="hidden text-slate-300 sm:inline">&bull;</span>
                                        <span>{{ student.company || '-' }}</span>
                                        <span class="hidden text-slate-300 sm:inline">&bull;</span>
                                        <span>Periode {{ periodLabel(student) }}</span>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-end">
                                    <div class="flex flex-wrap gap-2 lg:justify-end">
                                        <div class="flex items-center gap-1.5 rounded-full border border-wims-border bg-wims-card px-2.5 py-1">
                                            <span class="text-[10px] font-medium text-slate-500">Presensi</span>
                                            <Badge
                                                variant="outline"
                                                class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                                                :class="attendanceClass(student.attendance_status)"
                                            >
                                                {{ attendanceLabel(student.attendance_status) }}
                                            </Badge>
                                        </div>
                                        <div class="flex items-center gap-1.5 rounded-full border border-wims-border bg-wims-card px-2.5 py-1">
                                            <span class="text-[10px] font-medium text-slate-500">Logbook</span>
                                            <Badge
                                                variant="outline"
                                                class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                                                :class="logbookClass(student.logbook_status)"
                                            >
                                                {{ logbookLabel(student.logbook_status) }}
                                            </Badge>
                                        </div>
                                        <div class="flex items-center gap-1.5 rounded-full border border-wims-border bg-wims-card px-2.5 py-1">
                                            <span class="text-[10px] font-medium text-slate-500">Laporan</span>
                                            <Badge
                                                variant="outline"
                                                class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                                                :class="reportStatusClass(student.objective_summary?.final_report_uploaded)"
                                            >
                                                {{ reportStatusLabel(student.objective_summary?.final_report_uploaded) }}
                                            </Badge>
                                        </div>
                                    </div>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="hidden h-9 rounded-lg border-wims-border bg-wims-card px-3 text-sm font-bold text-slate-700 hover:bg-slate-50 sm:inline-flex"
                                        @click="openMonitoring(student)"
                                    >
                                        Lihat Monitoring
                                    </Button>
                                </div>
                            </div>

                            <div class="mt-2 flex flex-col gap-2 border-t border-wims-border pt-2 sm:hidden">
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="h-9 rounded-lg border-wims-border bg-wims-card px-3 text-sm font-bold text-slate-700 hover:bg-slate-50"
                                    @click="openMonitoring(student)"
                                >
                                    Lihat Monitoring
                                </Button>
                            </div>
                        </div>

                        <div
                            v-if="remainingPreviewCount(activeStudents) > 0"
                            class="rounded-xl border border-wims-border bg-slate-50 px-4 py-3 text-sm text-slate-600"
                        >
                            {{ previewStudents(activeStudents).length }} dari {{ activeStudents.length }} mahasiswa ditampilkan.
                        </div>
                    </CardContent>
                </Card>

                <p
                    v-else
                    class="rounded-xl border border-wims-border bg-wims-card px-5 py-6 text-center text-sm text-slate-500"
                >
                    Tidak ada mahasiswa aktif saat ini.
                </p>
            </section>

            <section class="space-y-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div class="max-w-3xl">
                        <h2 class="text-base font-bold text-wims-text">
                            Mahasiswa Selesai
                        </h2>
                        <p class="mt-1 text-sm leading-6 text-slate-600">
                            Preview mahasiswa yang sudah selesai PKL dan siap untuk penilaian dosen.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                        <Badge
                            variant="outline"
                            class="w-fit rounded-full border-wims-border bg-slate-50 px-3 py-1 text-xs font-bold text-slate-700"
                        >
                            {{ completedStudents.length }} mahasiswa
                        </Badge>
                        <p
                            v-if="completedStudents.length > 3"
                            class="text-[11px] text-slate-500"
                        >
                            Menampilkan 3 dari {{ completedStudents.length }} mahasiswa.
                        </p>
                        <Button
                            type="button"
                            variant="outline"
                            class="h-8 rounded-lg border-wims-border bg-wims-card px-3 text-xs font-bold text-slate-700 hover:bg-slate-50"
                            @click="openMonitoringIndex('selesai')"
                        >
                            Lihat Semua
                        </Button>
                    </div>
                </div>

                <Card
                    v-if="completedStudents.length"
                    class="rounded-xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)]"
                >
                    <CardContent class="space-y-3 px-4 py-4 sm:px-5">
                        <div
                            v-for="student in previewStudents(completedStudents)"
                            :key="student.pendaftaran_id ?? student.id"
                            class="rounded-xl border border-wims-border bg-slate-50 px-3.5 py-3"
                        >
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <div class="min-w-0 lg:flex-1">
                                    <p class="text-sm font-bold text-wims-text">
                                        {{ student.name || 'Mahasiswa' }}
                                    </p>
                                    <div class="mt-1 flex flex-col gap-1 text-xs text-slate-500 sm:flex-row sm:flex-wrap sm:items-center sm:gap-x-3 sm:gap-y-1">
                                        <span>{{ student.nim || '-' }}</span>
                                        <span class="hidden text-slate-300 sm:inline">&bull;</span>
                                        <span>{{ student.company || '-' }}</span>
                                        <span class="hidden text-slate-300 sm:inline">&bull;</span>
                                        <span>Periode {{ periodLabel(student) }}</span>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-end">
                                    <div class="flex flex-wrap gap-2 lg:justify-end">
                                        <div class="flex items-center gap-1.5 rounded-full border border-wims-border bg-wims-card px-2.5 py-1">
                                            <span class="text-[10px] font-medium text-slate-500">Penilaian</span>
                                            <Badge
                                                variant="outline"
                                                class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                                                :class="evaluationStatusClass(student.objective_summary?.evaluation_status)"
                                            >
                                                {{ evaluationStatusLabel(student.objective_summary?.evaluation_status) }}
                                            </Badge>
                                        </div>
                                        <div class="flex items-center gap-1.5 rounded-full border border-wims-border bg-wims-card px-2.5 py-1">
                                            <span class="text-[10px] font-medium text-slate-500">Nilai</span>
                                            <span class="text-[11px] font-bold text-wims-text">
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
                                            class="h-9 rounded-lg border-wims-border bg-wims-card px-3 text-sm font-bold text-slate-700 hover:bg-slate-50"
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
                                </div>
                            </div>

                            <div class="mt-2 flex flex-col gap-2 border-t border-wims-border pt-2 sm:hidden">
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="h-9 rounded-lg border-wims-border bg-wims-card px-3 text-sm font-bold text-slate-700 hover:bg-slate-50"
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
                        </div>

                        <div
                            v-if="remainingPreviewCount(completedStudents) > 0"
                            class="rounded-xl border border-wims-border bg-slate-50 px-4 py-3 text-sm text-slate-600"
                        >
                            {{ previewStudents(completedStudents).length }} dari {{ completedStudents.length }} mahasiswa ditampilkan.
                        </div>
                    </CardContent>
                </Card>

                <p
                    v-else
                    class="rounded-xl border border-wims-border bg-wims-card px-5 py-6 text-center text-sm text-slate-500"
                >
                    Belum ada mahasiswa yang masuk fase selesai.
                </p>
            </section>
        </div>
    </div>
</template>


