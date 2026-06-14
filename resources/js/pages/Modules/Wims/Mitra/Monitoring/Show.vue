<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft, LoaderCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import WimsMitraLayout from '@/layouts/Modules/Wims/Mitra/Layout.vue';
import { formatIndonesianDateLabel, formatIndonesianDateTime, formatIndonesianTime } from '@/lib/date';

defineOptions({
    layout: WimsMitraLayout,
});

type StudentProps = {
    id?: number | null;
    name?: string | null;
    nim?: string | null;
    company?: string | null;
    pendaftaran_id?: number | null;
    status_pendaftaran?: string | null;
    is_ready_for_assessment?: boolean;
    period_start?: string | null;
    period_end?: string | null;
};

type AttendanceProps = {
    check_in?: string | null;
    check_out?: string | null;
    check_in_photo_url?: string | null;
    check_out_photo_url?: string | null;
    status?: string | null;
    note?: string | null;
};

type LogbookProps = {
    id?: number | null;
    tanggal?: string | null;
    tanggal_label?: string | null;
    aktivitas?: string | null;
    kompetensi?: string | null;
    status?: string | null;
    catatan_mitra?: string | null;
    reviewed_by_name?: string | null;
    reviewed_at?: string | null;
};

type ImageItem = {
    id?: number | null;
    url?: string | null;
};

type HistoryAttendanceItem = {
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

type HistoryLogbookItem = {
    id: number;
    tanggal?: string | null;
    tanggal_label?: string | null;
    aktivitas?: string | null;
    kompetensi?: string | null;
    status?: string | null;
    catatan_mitra?: string | null;
    reviewed_by_name?: string | null;
    reviewed_at?: string | null;
};

type SummaryProps = {
    attendance?: {
        total?: number;
    };
    logbook?: {
        total?: number;
        total_disetujui?: number;
        total_revisi?: number;
        total_pending?: number;
    };
};

type AssessmentProps = {
    status?: string | null;
    status_label?: string | null;
    total_score?: number | null;
};

type PageProps = {
    flash?: {
        success?: string;
    };
    errors?: Record<string, string | undefined>;
};

type ActivitySectionProps = {
    date?: string | null;
    attendance?: AttendanceProps | null;
    logbook?: LogbookProps | null;
    images?: ImageItem[];
};

const props = defineProps<{
    student: StudentProps;
    today: ActivitySectionProps;
    selected: ActivitySectionProps;
    history: {
        attendance?: HistoryAttendanceItem[];
        logbook?: HistoryLogbookItem[];
    };
    assessment?: AssessmentProps;
    summary?: SummaryProps;
}>();

const page = usePage<PageProps>();
const flash = computed(() => page.props.flash ?? {});
const pageErrors = computed(() => page.props.errors ?? {});
const todayAttendance = computed(() => props.today.attendance ?? null);
const todayLogbook = computed(() => props.today.logbook ?? null);
const todayImages = computed(() => props.today.images ?? []);
const selectedAttendance = computed(() => props.selected.attendance ?? null);
const selectedLogbook = computed(() => props.selected.logbook ?? null);
const selectedImages = computed(() => props.selected.images ?? []);
const attendanceHistory = computed(() => props.history.attendance ?? []);
const logbookHistory = computed(() => props.history.logbook ?? []);
const attendanceHistoryPreview = computed(() => attendanceHistory.value.slice(0, 7));
const logbookHistoryPreview = computed(() => logbookHistory.value.slice(0, 3));
const summary = computed(() => props.summary ?? {});
const assessment = computed(() => props.assessment ?? {});
const isActiveRegistration = computed(
    () => props.student.status_pendaftaran === 'aktif' && props.student.is_ready_for_assessment !== true,
);
const isCompletedRegistration = computed(() => props.student.is_ready_for_assessment === true);
const attendanceHistoryDialogOpen = ref(false);
const logbookHistoryDialogOpen = ref(false);

const reviewForm = useForm({
    status: todayLogbook.value?.status === 'revisi' ? 'revisi' : 'disetujui',
    catatan_mitra: todayLogbook.value?.catatan_mitra ?? '',
});

const isTodayReviewReadonly = computed(
    () => todayLogbook.value?.status === 'disetujui' || todayLogbook.value?.status === 'revisi',
);
const canReviewTodayLogbook = computed(() => Boolean(todayLogbook.value?.id) && !isTodayReviewReadonly.value);

const assessmentActionLabel = computed(() => {
    if (assessment.value.status === 'submitted') return 'Lihat Nilai';
    if (assessment.value.status === 'draft') return 'Lanjutkan Draft';
    return 'Isi Nilai';
});

const assessmentStatusClass = computed(() => {
    if (assessment.value.status === 'submitted') {
        return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    }

    if (assessment.value.status === 'draft') {
        return 'border-amber-200 bg-amber-50 text-amber-700';
    }

    return 'border-slate-200 bg-slate-50 text-slate-600';
});

const formatDateUi = (value?: string | null) => {
    return formatIndonesianDateLabel(value);
};

const formatTime = (value?: string | null) => {
    return formatIndonesianTime(value);
};

const formatDateTime = (value?: string | null) => {
    return formatIndonesianDateTime(value, {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const periodLabel = computed(() => {
    if (props.student.period_start && props.student.period_end) {
        return `${formatDateUi(props.student.period_start)} - ${formatDateUi(props.student.period_end)}`;
    }

    return formatDateUi(props.student.period_start || props.student.period_end);
});

const statusPendaftaranLabel = computed(() => {
    if (props.student.status_pendaftaran === 'selesai') return 'Selesai';
    if (props.student.status_pendaftaran === 'aktif') return 'Aktif';
    if (props.student.status_pendaftaran === 'diterima') return 'Diterima';
    return props.student.status_pendaftaran ? props.student.status_pendaftaran : '-';
});

const todayDateLabel = computed(() => formatDateUi(props.today.date));
const selectedDateLabel = computed(() => formatDateUi(props.selected.date));
const selectedDateInput = computed(() => props.selected.date ?? props.student.period_start ?? props.today.date ?? '');
const attendancePreviewLabel = computed(() =>
    attendanceHistory.value.length > 7 ? 'Menampilkan 7 data terbaru.' : null,
);
const logbookPreviewLabel = computed(() =>
    logbookHistory.value.length > 3 ? 'Menampilkan 3 logbook terbaru.' : null,
);

function getAttendanceLabel(status?: string | null) {
    if (status === 'izin') return 'Izin';
    if (status === 'sakit') return 'Sakit';
    if (status === 'alfa') return 'Alfa';
    if (status === 'terlambat') return 'Terlambat';
    if (status === 'hari_libur') return 'Hari libur';
    if (status === 'bukan_hari_kerja') return 'Bukan hari kerja';
    if (!status) return 'Belum ada';
    return 'Tepat Waktu';
}

function getAttendanceClass(status?: string | null) {
    if (status === 'izin') return 'border-blue-200 bg-blue-50 text-blue-700';
    if (status === 'sakit') return 'border-violet-200 bg-violet-50 text-violet-700';
    if (status === 'alfa') return 'border-rose-200 bg-rose-50 text-rose-700';
    if (status === 'terlambat') return 'border-amber-200 bg-amber-50 text-amber-700';
    if (status === 'hari_libur') return 'border-sky-200 bg-sky-50 text-sky-700';
    if (status === 'bukan_hari_kerja') return 'border-slate-200 bg-slate-100 text-slate-600';
    if (!status) return 'border-slate-200 bg-slate-50 text-slate-600';
    return 'border-emerald-200 bg-emerald-50 text-emerald-700';
}

function getLogbookLabel(status?: string | null) {
    if (status === 'disetujui') return 'Disetujui';
    if (status === 'revisi') return 'Revisi';
    if (!status) return 'Belum ada';
    return 'Menunggu review';
}

function getLogbookClass(status?: string | null) {
    if (status === 'disetujui') return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    if (status === 'revisi') return 'border-rose-200 bg-rose-50 text-rose-700';
    if (!status) return 'border-slate-200 bg-slate-50 text-slate-600';
    return 'border-amber-200 bg-amber-50 text-amber-700';
}

const goBack = () => router.visit('/wims/mitra/dashboard');

const submitReview = () => {
    if (!todayLogbook.value?.id) {
        return;
    }

    reviewForm.post(`/wims/mitra/logbook/${todayLogbook.value.id}/review`, {
        preserveScroll: true,
    });
};

const openAssessment = () => {
    if (!props.student.pendaftaran_id) {
        return;
    }

    router.visit(`/wims/mitra/penilaian-mahasiswa/${props.student.pendaftaran_id}?from=monitoring`);
};

const openSelectedDate = (value: string) => {
    if (!props.student.id || !/^\d{4}-\d{2}-\d{2}$/.test(value)) {
        return;
    }

    router.visit(`/wims/mitra/monitoring/${props.student.id}?date=${encodeURIComponent(value)}`);
};
</script>

<template>
    <Head title="Monitoring Mitra" />

    <div class="min-h-screen bg-wims-bg">
        <div class="mx-auto flex w-full max-w-[1320px] flex-col gap-4 px-4 py-3 lg:gap-5 sm:px-6 sm:py-6 lg:px-8 lg:py-8 xl:px-10">
            <section class="relative overflow-hidden rounded-2xl border border-wims-border/50 bg-wims-card/95 px-5 py-5 shadow-[0_1px_3px_rgba(0,0,0,0.04)] sm:px-6 sm:py-6">
                <div class="flex flex-col gap-2.5 sm:gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-3xl">
                        <h1 class="text-[17px] font-bold tracking-tight text-wims-text sm:text-[20px]">Ringkasan Mahasiswa</h1>
                        <p class="mt-1.5 text-[12px] leading-5 text-slate-600 sm:text-sm sm:leading-6">Pantau presensi, logbook, dan tindak lanjut mahasiswa PKL secara ringkas.</p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-9 w-fit self-start items-center gap-1.5 rounded-lg border border-wims-border bg-wims-card px-3 text-[11px] font-bold text-slate-700 transition hover:bg-slate-50 sm:px-3.5 sm:text-xs lg:self-auto"
                        @click="goBack"
                    >
                        <ArrowLeft class="size-3.5" />
                        Kembali ke Dashboard
                    </button>
                </div>
            </section>

            <section>
                <Card class="rounded-2xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)]">
                    <CardContent class="px-4 py-4 sm:px-5 sm:py-5">
                        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-slate-400">Mahasiswa</p>
                                <p class="mt-2 text-base font-bold text-wims-text">{{ props.student.name || '-' }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ props.student.nim || '-' }}</p>
                            </div>

                            <div class="grid flex-1 gap-3 xl:grid-cols-3 xl:items-stretch">
                                <div class="rounded-xl border border-wims-border bg-slate-50 px-4 py-3 h-full">
                                    <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Perusahaan</p>
                                    <p class="mt-1 text-sm font-bold leading-6 text-wims-text">{{ props.student.company || '-' }}</p>
                                </div>
                                <div class="rounded-xl border border-wims-border bg-slate-50 px-4 py-3 h-full">
                                    <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Periode PKL</p>
                                    <p class="mt-1 text-sm font-bold leading-6 text-wims-text">{{ periodLabel }}</p>
                                </div>
                                <div class="min-w-0 rounded-xl border border-wims-border bg-slate-50 px-4 py-3 h-full">
                                    <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Status PKL</p>
                                    <div class="mt-2 flex flex-col items-start gap-2.5">
                                        <Badge variant="outline" class="rounded-full px-3 py-1 text-[11px] font-bold border-slate-200 bg-white text-slate-700">
                                            {{ statusPendaftaranLabel }}
                                        </Badge>
                                        <template v-if="isActiveRegistration">
                                            <div class="flex flex-wrap gap-2">
                                                <Badge variant="outline" class="w-fit rounded-full px-3 py-1 text-[11px] font-bold" :class="getAttendanceClass(todayAttendance?.status)">
                                                    Presensi: {{ getAttendanceLabel(todayAttendance?.status) }}
                                                </Badge>
                                                <Badge variant="outline" class="w-fit rounded-full px-3 py-1 text-[11px] font-bold" :class="getLogbookClass(todayLogbook?.status)">
                                                    Logbook: {{ getLogbookLabel(todayLogbook?.status) }}
                                                </Badge>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </section>

            <section v-if="isCompletedRegistration">
                <Card class="rounded-2xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)]">
                    <CardHeader class="px-4 pt-4 pb-3 sm:px-5 sm:pt-5">
                        <CardTitle class="text-base text-wims-text">Penilaian Mahasiswa</CardTitle>
                        <CardDescription class="mt-1 text-sm text-slate-600">
                            Penilaian mitra tersedia setelah mahasiswa menyelesaikan PKL.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3 px-4 pb-4 sm:px-5 sm:pb-5">
                        <div v-if="isCompletedRegistration" class="rounded-xl border border-blue-100 bg-blue-50/60 px-4 py-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex flex-wrap items-center gap-2">
                                    <Badge variant="outline" class="rounded-full px-3 py-1 text-[11px] font-bold" :class="assessmentStatusClass">
                                        {{ assessment.status_label || 'Belum Dinilai' }}
                                    </Badge>
                                    <p v-if="assessment.total_score !== null && assessment.total_score !== undefined" class="text-xs text-slate-600">
                                        Nilai Mitra {{ Number(assessment.total_score).toFixed(2) }}/100
                                    </p>
                                </div>
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="h-9 rounded-lg border-[#0F62FE]/20 bg-white px-3.5 text-[13px] font-bold text-[#0F62FE] hover:bg-blue-100 sm:h-10 sm:px-4 sm:text-sm"
                                    @click="openAssessment"
                                >
                                    {{ assessmentActionLabel }}
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </section>

            <Alert v-if="flash.success" class="border-emerald-200 bg-emerald-50 text-emerald-700">
                <AlertTitle>Berhasil</AlertTitle>
                <AlertDescription>{{ flash.success }}</AlertDescription>
            </Alert>

            <Alert v-if="pageErrors.review" class="border-rose-200 bg-rose-50 text-rose-700">
                <AlertTitle>Review Belum Tersimpan</AlertTitle>
                <AlertDescription>{{ pageErrors.review }}</AlertDescription>
            </Alert>

            <section class="space-y-4 sm:space-y-5">
                <Card v-if="isActiveRegistration" class="rounded-2xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)]">
                    <CardHeader class="px-4 pt-4 pb-3 sm:px-5 sm:pt-5">
                        <CardTitle class="text-base text-wims-text">Monitoring Hari Ini</CardTitle>
                        <CardDescription class="mt-1 text-sm text-slate-600">
                            Presensi dan logbook mahasiswa untuk {{ todayDateLabel }}.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4 px-5 pb-5 sm:px-6 sm:pb-6">
                        <div class="grid gap-4 xl:grid-cols-2">
                            <div class="rounded-xl border border-wims-border bg-slate-50 px-4 py-4">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="text-sm font-bold text-wims-text">Presensi Hari Ini</p>
                                    <Badge variant="outline" class="w-fit rounded-full px-3 py-1 text-[11px] font-bold" :class="getAttendanceClass(todayAttendance?.status)">
                                        {{ getAttendanceLabel(todayAttendance?.status) }}
                                    </Badge>
                                </div>
                                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                                    <div class="rounded-lg border border-wims-border bg-white px-3 py-3">
                                        <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Masuk</p>
                                        <p class="mt-1 text-sm font-bold text-wims-text">{{ formatTime(todayAttendance?.check_in) }}</p>
                                    </div>
                                    <div class="rounded-lg border border-wims-border bg-white px-3 py-3">
                                        <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Pulang</p>
                                        <p class="mt-1 text-sm font-bold text-wims-text">{{ formatTime(todayAttendance?.check_out) }}</p>
                                    </div>
                                    <div class="rounded-lg border border-wims-border bg-white px-3 py-3 sm:col-span-3 xl:col-span-1">
                                        <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Catatan</p>
                                        <p class="mt-1 text-sm text-slate-600">{{ todayAttendance?.note || '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-xl border border-wims-border bg-slate-50 px-4 py-4">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="text-sm font-bold text-wims-text">Logbook Hari Ini</p>
                                    <Badge variant="outline" class="w-fit rounded-full px-3 py-1 text-[11px] font-bold" :class="getLogbookClass(todayLogbook?.status)">
                                        {{ getLogbookLabel(todayLogbook?.status) }}
                                    </Badge>
                                </div>
                                <div v-if="todayLogbook?.id" class="mt-3 space-y-3">
                                    <div>
                                        <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Aktivitas</p>
                                        <p class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-600">{{ todayLogbook?.aktivitas || '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Kompetensi</p>
                                        <p class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-600">{{ todayLogbook?.kompetensi || '-' }}</p>
                                    </div>
                                    <div v-if="todayLogbook?.catatan_mitra">
                                        <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Catatan Mitra</p>
                                        <p class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-600">{{ todayLogbook?.catatan_mitra }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Lampiran</p>
                                        <div v-if="todayImages.length" class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                            <div v-for="image in todayImages" :key="image.id ?? image.url ?? 'image'" class="overflow-hidden rounded-xl border border-wims-border bg-white">
                                                <img :src="image.url ?? undefined" alt="Lampiran logbook hari ini" class="aspect-square h-full w-full object-cover" />
                                            </div>
                                        </div>
                                        <p v-else class="mt-1 text-sm text-slate-500">Belum ada lampiran untuk logbook hari ini.</p>
                                    </div>
                                </div>
                                <p v-else class="mt-3 rounded-lg border border-dashed border-wims-border bg-white px-3 py-3 text-sm text-slate-500">
                                    Mahasiswa belum mengisi logbook hari ini.
                                </p>
                            </div>
                        </div>

                        <div v-if="todayLogbook?.id && canReviewTodayLogbook" class="rounded-xl border border-wims-border bg-white px-4 py-4">
                            <div class="space-y-4">
                                <div class="flex items-center gap-2">
                                    <UserCheck class="size-4 text-[#1554D1]" />
                                    <p class="text-sm font-bold text-wims-text">Review Mitra</p>
                                </div>
                                <label class="block space-y-2">
                                    <span class="text-sm font-medium text-slate-700">Status Review</span>
                                    <select v-model="reviewForm.status" class="h-10 w-full rounded-xl border border-wims-border bg-white px-3.5 text-[13px] text-wims-text transition outline-none focus:border-[#1554D1] focus:ring-2 focus:ring-[#1554D1]/15 sm:h-11 sm:px-4 sm:text-sm">
                                        <option value="disetujui">Disetujui</option>
                                        <option value="revisi">Revisi</option>
                                    </select>
                                </label>

                                <label class="block space-y-2">
                                    <span class="text-sm font-medium text-slate-700">Catatan Pembimbing Mitra</span>
                                    <textarea
                                        v-model="reviewForm.catatan_mitra"
                                        rows="5"
                                        class="min-h-[120px] w-full rounded-xl border border-wims-border bg-white px-4 py-3 text-sm leading-6 text-wims-text transition outline-none focus:border-[#1554D1] focus:ring-2 focus:ring-[#1554D1]/15"
                                        placeholder="Tulis validasi aktivitas atau arahan revisi untuk mahasiswa."
                                    />
                                    <p v-if="reviewForm.errors.catatan_mitra" class="text-xs text-rose-600">
                                        {{ reviewForm.errors.catatan_mitra }}
                                    </p>
                                </label>

                                <div class="flex justify-end pt-1">
                                    <Button
                                        type="button"
                                        class="h-10 w-full rounded-xl bg-[#1554D1] px-3.5 text-[13px] font-bold text-white hover:bg-[#1147b4] disabled:bg-slate-300 disabled:text-slate-600 sm:h-11 sm:w-auto sm:px-4 sm:text-sm"
                                        :disabled="reviewForm.processing"
                                        @click="submitReview"
                                    >
                                        <LoaderCircle v-if="reviewForm.processing" class="size-4 animate-spin" />
                                        <span v-else>Simpan Review</span>
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="todayLogbook?.id" class="rounded-xl border border-wims-border bg-white px-4 py-4">
                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <UserCheck class="size-4 text-[#1554D1]" />
                                    <p class="text-sm font-bold text-wims-text">Review Mitra</p>
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div class="rounded-lg border border-wims-border bg-slate-50 px-4 py-3">
                                        <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Status Review</p>
                                        <Badge variant="outline" class="mt-2 rounded-full px-3 py-1 text-[11px] font-bold" :class="getLogbookClass(todayLogbook?.status)">
                                            {{ getLogbookLabel(todayLogbook?.status) }}
                                        </Badge>
                                    </div>
                                    <div class="rounded-lg border border-wims-border bg-slate-50 px-4 py-3">
                                        <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Direview oleh</p>
                                        <p class="mt-2 text-sm font-medium text-wims-text">{{ todayLogbook?.reviewed_by_name || '-' }}</p>
                                    </div>
                                    <div class="rounded-lg border border-wims-border bg-slate-50 px-4 py-3 sm:col-span-2">
                                        <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Waktu review</p>
                                        <p class="mt-2 text-sm font-medium text-wims-text">{{ todayLogbook?.reviewed_at ? formatDateTime(todayLogbook.reviewed_at) : '-' }}</p>
                                    </div>
                                    <div class="rounded-lg border border-wims-border bg-slate-50 px-4 py-3 sm:col-span-2">
                                        <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Catatan Mitra</p>
                                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ todayLogbook?.catatan_mitra || 'Tidak ada catatan mitra.' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="rounded-2xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)]">
                    <CardHeader class="px-4 pt-4 pb-3 sm:px-5 sm:pt-5">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <CardTitle class="text-base text-wims-text">Lihat Aktivitas Mahasiswa</CardTitle>
                                <CardDescription class="mt-1 text-sm text-slate-600">
                                    Pilih tanggal dalam rentang PKL untuk melihat presensi dan logbook mahasiswa.
                                </CardDescription>
                            </div>
                            <div class="w-full max-w-xs">
                                <label class="block space-y-2">
                                    <span class="text-sm font-medium text-slate-700">Pilih tanggal</span>
                                    <input
                                        :value="selectedDateInput"
                                        type="date"
                                        class="h-10 w-full rounded-xl border border-wims-border bg-white px-3.5 text-[13px] text-wims-text transition outline-none focus:border-[#1554D1] focus:ring-2 focus:ring-[#1554D1]/15 sm:h-11 sm:px-4 sm:text-sm"
                                        :min="props.student.period_start || undefined"
                                        :max="props.student.period_end || undefined"
                                        @change="openSelectedDate(($event.target as HTMLInputElement).value)"
                                    />
                                </label>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4 px-5 pb-5 sm:px-6 sm:pb-6">
                        <div class="grid items-start gap-4 xl:grid-cols-2">
                            <div class="rounded-xl border border-wims-border bg-slate-50 px-4 py-4">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="text-sm font-bold text-wims-text">Presensi</p>
                                    <div class="flex items-center gap-2">
                                        <p class="text-xs text-slate-500">{{ selectedDateLabel }}</p>
                                        <Badge v-if="selectedAttendance?.status" variant="outline" class="rounded-full px-3 py-1 text-[11px] font-bold" :class="getAttendanceClass(selectedAttendance?.status)">
                                            {{ getAttendanceLabel(selectedAttendance?.status) }}
                                        </Badge>
                                    </div>
                                </div>
                                <div v-if="selectedAttendance?.status" class="mt-3 grid gap-3 sm:grid-cols-3">
                                    <div class="rounded-lg border border-wims-border bg-white px-3 py-3">
                                        <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Masuk</p>
                                        <p class="mt-1 text-sm font-bold text-wims-text">{{ formatTime(selectedAttendance?.check_in) }}</p>
                                    </div>
                                    <div class="rounded-lg border border-wims-border bg-white px-3 py-3">
                                        <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Pulang</p>
                                        <p class="mt-1 text-sm font-bold text-wims-text">{{ formatTime(selectedAttendance?.check_out) }}</p>
                                    </div>
                                    <div class="rounded-lg border border-wims-border bg-white px-3 py-3 sm:col-span-3">
                                        <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Catatan</p>
                                        <p :class="selectedAttendance?.note ? 'text-slate-600' : 'text-slate-400'" class="mt-1 text-sm break-words">{{ selectedAttendance?.note || '-' }}</p>
                                    </div>
                                </div>
                                <p v-else class="mt-3 rounded-lg border border-dashed border-wims-border bg-white px-3 py-3 text-sm text-slate-500">
                                    Tidak ada data presensi pada tanggal ini.
                                </p>
                            </div>

                            <div class="rounded-xl border border-wims-border bg-slate-50 px-4 py-4">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="text-sm font-bold text-wims-text">Logbook</p>
                                    <div class="flex items-center gap-2">
                                        <p class="text-xs text-slate-500">{{ selectedDateLabel }}</p>
                                        <Badge v-if="selectedLogbook?.id" variant="outline" class="rounded-full px-3 py-1 text-[11px] font-bold" :class="getLogbookClass(selectedLogbook?.status)">
                                            {{ getLogbookLabel(selectedLogbook?.status) }}
                                        </Badge>
                                    </div>
                                </div>
                                <div v-if="selectedLogbook?.id" class="mt-3 space-y-3">
                                    <div>
                                        <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Aktivitas</p>
                                        <p class="mt-1 break-words whitespace-pre-line text-sm leading-6 text-slate-600">{{ selectedLogbook?.aktivitas || '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Kompetensi</p>
                                        <p class="mt-1 break-words whitespace-pre-line text-sm leading-6 text-slate-600">{{ selectedLogbook?.kompetensi || '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Catatan Mitra</p>
                                        <p class="mt-1 break-words whitespace-pre-line text-sm leading-6 text-slate-600">{{ selectedLogbook?.catatan_mitra || 'Tidak ada catatan mitra.' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Lampiran</p>
                                        <div v-if="selectedImages.length" class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                            <div v-for="image in selectedImages" :key="`selected-${image.id ?? image.url ?? 'image'}`" class="overflow-hidden rounded-xl border border-wims-border bg-white">
                                                <img :src="image.url ?? undefined" alt="Lampiran logbook pada tanggal yang dipilih" class="aspect-square h-full w-full object-cover" />
                                            </div>
                                        </div>
                                        <p v-else class="mt-1 text-sm text-slate-500">Belum ada lampiran pada tanggal ini.</p>
                                    </div>
                                </div>
                                <p v-else class="mt-3 rounded-lg border border-dashed border-wims-border bg-white px-3 py-3 text-sm text-slate-500">
                                    Tidak ada logbook pada tanggal ini.
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </section>

            <section class="space-y-4">
                <div>
                    <h2 class="text-base font-bold text-wims-text">Riwayat</h2>
                    <p class="mt-1 text-sm text-slate-600">Riwayat presensi dan logbook ditampilkan ringkas untuk pemantauan cepat.</p>
                </div>

                <div class="grid items-start gap-5 xl:grid-cols-2">
                    <Card class="rounded-2xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)]">
                        <CardHeader class="px-4 pt-4 pb-3 sm:px-5 sm:pt-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <CardTitle class="text-base text-wims-text">Riwayat Presensi</CardTitle>
                                    <CardDescription class="mt-1 text-sm text-slate-600">
                                        Riwayat presensi harian mahasiswa selama periode PKL.
                                    </CardDescription>
                                    <p v-if="attendancePreviewLabel" class="mt-1 text-xs text-slate-400">
                                        {{ attendancePreviewLabel }}
                                    </p>
                                </div>
                                <Button
                                    v-if="attendanceHistory.length > 7"
                                    type="button"
                                    variant="outline"
                                    class="h-8 rounded-lg border-wims-border bg-wims-card px-3 text-xs font-bold text-slate-700 hover:bg-slate-50"
                                    @click="attendanceHistoryDialogOpen = true"
                                >
                                    Lihat Semua
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent class="px-0 pb-4 sm:pb-5">
                            <div v-if="attendanceHistoryPreview.length" class="space-y-3 md:hidden">
                                <div
                                    v-for="item in attendanceHistoryPreview"
                                    :key="`attendance-mobile-${item.id}`"
                                    class="rounded-xl border border-wims-border bg-slate-50 px-4 py-3.5"
                                >
                                    <div class="flex flex-col gap-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold text-wims-text">
                                                    {{ formatDateUi(item.tanggal_label || item.tanggal) }}
                                                </p>
                                                <p
                                                    :class="item.keterangan ? 'text-slate-500' : 'text-slate-400'"
                                                    class="mt-1 text-xs leading-5 break-words"
                                                >
                                                    {{ item.keterangan || 'Tanpa catatan' }}
                                                </p>
                                            </div>
                                            <Badge
                                                variant="outline"
                                                class="w-fit rounded-full px-3 py-1 text-[11px] font-bold"
                                                :class="getAttendanceClass(item.status)"
                                            >
                                                {{ getAttendanceLabel(item.status) }}
                                            </Badge>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div class="rounded-lg border border-wims-border bg-white px-3 py-2.5">
                                                <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Masuk</p>
                                                <p :class="item.check_in ? 'text-wims-text' : 'text-slate-400'" class="mt-1 text-sm font-bold">
                                                    {{ formatTime(item.check_in) }}
                                                </p>
                                            </div>
                                            <div class="rounded-lg border border-wims-border bg-white px-3 py-2.5">
                                                <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Pulang</p>
                                                <p :class="item.check_out ? 'text-wims-text' : 'text-slate-400'" class="mt-1 text-sm font-bold">
                                                    {{ formatTime(item.check_out) }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="rounded-lg border border-wims-border bg-white px-3 py-2.5">
                                            <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Bukti</p>
                                            <div class="mt-1.5 flex flex-wrap gap-3">
                                                <a v-if="item.check_in_photo_url" :href="item.check_in_photo_url" target="_blank" rel="noreferrer" class="text-xs font-bold text-blue-600 hover:text-blue-700">
                                                    Foto masuk
                                                </a>
                                                <a v-if="item.check_out_photo_url" :href="item.check_out_photo_url" target="_blank" rel="noreferrer" class="text-xs font-bold text-blue-600 hover:text-blue-700">
                                                    Foto pulang
                                                </a>
                                                <span v-if="!item.check_in_photo_url && !item.check_out_photo_url" class="text-xs text-slate-400">Tidak ada bukti</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="attendanceHistoryPreview.length" class="hidden overflow-x-auto md:block">
                                <table class="min-w-full border-collapse">
                                    <thead class="bg-slate-50/70">
                                        <tr class="border-y border-wims-border">
                                            <th class="px-4 py-3 text-left text-[12px] font-bold uppercase tracking-[0.06em] text-slate-500">Tanggal</th>
                                            <th class="px-4 py-3 text-left text-[12px] font-bold uppercase tracking-[0.06em] text-slate-500">Status</th>
                                            <th class="px-4 py-3 text-left text-[12px] font-bold uppercase tracking-[0.06em] text-slate-500">Masuk</th>
                                            <th class="px-4 py-3 text-left text-[12px] font-bold uppercase tracking-[0.06em] text-slate-500">Pulang</th>
                                            <th class="px-4 py-3 text-left text-[12px] font-bold uppercase tracking-[0.06em] text-slate-500">Bukti</th>
                                            <th class="px-4 py-3 text-left text-[12px] font-bold uppercase tracking-[0.06em] text-slate-500">Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in attendanceHistoryPreview" :key="item.id" class="border-b border-wims-border">
                                            <td class="px-4 py-3 text-sm font-medium text-wims-text">{{ formatDateUi(item.tanggal_label || item.tanggal) }}</td>
                                            <td class="px-4 py-3">
                                                <Badge variant="outline" class="rounded-full px-3 py-1 text-[11px] font-bold" :class="getAttendanceClass(item.status)">
                                                    {{ getAttendanceLabel(item.status) }}
                                                </Badge>
                                            </td>
                                            <td :class="item.check_in ? 'text-slate-600' : 'text-slate-400'" class="px-4 py-3 text-sm">{{ formatTime(item.check_in) }}</td>
                                            <td :class="item.check_out ? 'text-slate-600' : 'text-slate-400'" class="px-4 py-3 text-sm">{{ formatTime(item.check_out) }}</td>
                                            <td class="px-4 py-3 text-sm text-slate-500">
                                                <div class="flex flex-wrap gap-2">
                                                    <a v-if="item.check_in_photo_url" :href="item.check_in_photo_url" target="_blank" rel="noreferrer" class="text-xs font-bold text-blue-600 hover:text-blue-700">
                                                        Foto masuk
                                                    </a>
                                                    <a v-if="item.check_out_photo_url" :href="item.check_out_photo_url" target="_blank" rel="noreferrer" class="text-xs font-bold text-blue-600 hover:text-blue-700">
                                                        Foto pulang
                                                    </a>
                                                    <span v-if="!item.check_in_photo_url && !item.check_out_photo_url" class="text-slate-400">-</span>
                                                </div>
                                            </td>
                                            <td :class="item.keterangan ? 'text-slate-500' : 'text-slate-400'" class="px-4 py-3 text-sm break-words">{{ item.keterangan || '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p v-else class="mx-4 rounded-xl border border-dashed border-wims-border bg-slate-50 px-4 py-4 text-sm text-slate-500 sm:mx-5">
                                Belum ada riwayat presensi yang dapat ditampilkan.
                            </p>
                        </CardContent>
                    </Card>

                    <Card class="rounded-2xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)]">
                        <CardHeader class="px-4 pt-4 pb-3 sm:px-5 sm:pt-5">
                            <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <CardTitle class="text-base text-wims-text">Riwayat Logbook</CardTitle>
                                    <CardDescription class="mt-1 text-sm text-slate-600">
                                        Riwayat logbook terbaru mahasiswa selama periode PKL.
                                    </CardDescription>
                                    <p v-if="logbookPreviewLabel" class="mt-1 text-xs text-slate-400">
                                        {{ logbookPreviewLabel }}
                                    </p>
                                </div>
                                <Button
                                    v-if="logbookHistory.length"
                                    type="button"
                                    variant="outline"
                                    class="h-8 self-start rounded-lg border-wims-border bg-wims-card px-3 text-xs font-bold text-slate-700 hover:bg-slate-50"
                                    @click="logbookHistoryDialogOpen = true"
                                >
                                    Lihat Semua Logbook
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-3 px-4 pb-4 sm:px-5 sm:pb-5">
                            <div
                                v-for="item in logbookHistoryPreview"
                                :key="`preview-logbook-${item.id}`"
                                class="rounded-xl border border-wims-border bg-slate-50 px-4 py-3.5"
                            >
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold text-wims-text">{{ formatDateUi(item.tanggal_label || item.tanggal) }}</p>
                                        <p class="mt-1.5 break-words whitespace-pre-line text-sm leading-6 text-slate-600">{{ item.aktivitas || '-' }}</p>
                                        <p v-if="item.catatan_mitra" class="mt-2 break-words text-xs leading-5 text-slate-500">
                                            Catatan mitra: {{ item.catatan_mitra }}
                                        </p>
                                    </div>
                                    <Badge variant="outline" class="w-fit rounded-full px-3 py-1 text-[11px] font-bold" :class="getLogbookClass(item.status)">
                                        {{ getLogbookLabel(item.status) }}
                                    </Badge>
                                </div>
                            </div>
                            <p v-if="!logbookHistory.length" class="rounded-xl border border-dashed border-wims-border bg-slate-50 px-4 py-4 text-sm text-slate-500">
                                Belum ada riwayat logbook yang dapat ditampilkan.
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </section>
        </div>

        <Dialog v-model:open="attendanceHistoryDialogOpen">
            <DialogContent class="w-full max-w-[calc(100vw-2rem)] sm:max-w-4xl rounded-2xl border border-wims-border bg-wims-card">
                <DialogHeader>
                    <DialogTitle class="text-wims-text">Riwayat Presensi</DialogTitle>
                    <DialogDescription class="text-slate-600">Seluruh riwayat presensi harian mahasiswa selama periode PKL.</DialogDescription>
                </DialogHeader>

                <div class="max-h-[70vh] overflow-y-auto rounded-xl border border-wims-border">
                    <div v-if="attendanceHistory.length" class="space-y-3 md:hidden">
                        <div
                            v-for="item in attendanceHistory"
                            :key="`modal-attendance-mobile-${item.id}`"
                            class="rounded-xl border border-wims-border bg-slate-50 px-4 py-3.5"
                        >
                            <div class="flex flex-col gap-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-wims-text">{{ formatDateUi(item.tanggal_label || item.tanggal) }}</p>
                                        <p :class="item.keterangan ? 'text-slate-500' : 'text-slate-400'" class="mt-1 text-xs leading-5 break-words">
                                            {{ item.keterangan || 'Tanpa catatan' }}
                                        </p>
                                    </div>
                                    <Badge variant="outline" class="w-fit rounded-full px-3 py-1 text-[11px] font-bold" :class="getAttendanceClass(item.status)">
                                        {{ getAttendanceLabel(item.status) }}
                                    </Badge>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="rounded-lg border border-wims-border bg-white px-3 py-2.5">
                                        <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Masuk</p>
                                        <p class="mt-1 text-sm font-bold text-wims-text">{{ formatTime(item.check_in) }}</p>
                                    </div>
                                    <div class="rounded-lg border border-wims-border bg-white px-3 py-2.5">
                                        <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Pulang</p>
                                        <p class="mt-1 text-sm font-bold text-wims-text">{{ formatTime(item.check_out) }}</p>
                                    </div>
                                </div>
                                <div class="rounded-lg border border-wims-border bg-white px-3 py-2.5">
                                    <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Bukti</p>
                                    <div class="mt-1.5 flex flex-wrap gap-3">
                                        <a v-if="item.check_in_photo_url" :href="item.check_in_photo_url" target="_blank" rel="noreferrer" class="text-xs font-bold text-blue-600 hover:text-blue-700">
                                            Foto masuk
                                        </a>
                                        <a v-if="item.check_out_photo_url" :href="item.check_out_photo_url" target="_blank" rel="noreferrer" class="text-xs font-bold text-blue-600 hover:text-blue-700">
                                            Foto pulang
                                        </a>
                                        <span v-if="!item.check_in_photo_url && !item.check_out_photo_url" class="text-xs text-slate-400">Tidak ada bukti</span>
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
                                    <th class="px-4 py-3 text-left text-[12px] font-bold uppercase tracking-[0.06em] text-slate-500">Bukti</th>
                                    <th class="px-4 py-3 text-left text-[12px] font-bold uppercase tracking-[0.06em] text-slate-500">Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in attendanceHistory" :key="`modal-attendance-${item.id}`" class="border-b border-wims-border last:border-b-0">
                                    <td class="px-4 py-3 text-sm font-medium text-wims-text">{{ formatDateUi(item.tanggal_label || item.tanggal) }}</td>
                                    <td class="px-4 py-3">
                                        <Badge variant="outline" class="rounded-full px-3 py-1 text-[11px] font-bold" :class="getAttendanceClass(item.status)">
                                            {{ getAttendanceLabel(item.status) }}
                                        </Badge>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-600">{{ formatTime(item.check_in) }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-600">{{ formatTime(item.check_out) }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-500">
                                        <div class="flex flex-wrap gap-2">
                                            <a v-if="item.check_in_photo_url" :href="item.check_in_photo_url" target="_blank" rel="noreferrer" class="text-xs font-bold text-blue-600 hover:text-blue-700">
                                                Foto masuk
                                            </a>
                                            <a v-if="item.check_out_photo_url" :href="item.check_out_photo_url" target="_blank" rel="noreferrer" class="text-xs font-bold text-blue-600 hover:text-blue-700">
                                                Foto pulang
                                            </a>
                                            <span v-if="!item.check_in_photo_url && !item.check_out_photo_url" class="text-slate-400">-</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-500">{{ item.keterangan || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="px-4 py-6 text-sm text-slate-500">Belum ada riwayat presensi yang dapat ditampilkan.</div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" class="border-wims-border bg-wims-card text-slate-700 hover:bg-slate-50" @click="attendanceHistoryDialogOpen = false">Tutup</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="logbookHistoryDialogOpen">
            <DialogContent class="w-full max-w-[calc(100vw-2rem)] sm:max-w-4xl rounded-2xl border border-wims-border bg-wims-card">
                <DialogHeader>
                    <DialogTitle class="text-wims-text">Riwayat Logbook</DialogTitle>
                    <DialogDescription class="text-slate-600">Seluruh riwayat logbook mahasiswa selama periode PKL.</DialogDescription>
                </DialogHeader>

                <div class="max-h-[70vh] space-y-3 overflow-y-auto pr-1">
                    <div
                        v-for="item in logbookHistory"
                        :key="`modal-logbook-${item.id}`"
                        class="rounded-xl border border-wims-border bg-slate-50 px-4 py-3.5"
                    >
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-wims-text">{{ formatDateUi(item.tanggal_label || item.tanggal) }}</p>
                                <p class="mt-1.5 whitespace-pre-line text-sm leading-6 text-slate-600">{{ item.aktivitas || '-' }}</p>
                                <p v-if="item.kompetensi" class="mt-2 text-xs leading-5 text-slate-500">Kompetensi: {{ item.kompetensi }}</p>
                                <p v-if="item.catatan_mitra" class="mt-2 text-xs leading-5 text-slate-500">Catatan mitra: {{ item.catatan_mitra }}</p>
                                <p v-if="item.reviewed_at" class="mt-2 text-xs text-slate-500">
                                    Direview {{ formatDateTime(item.reviewed_at) }}<span v-if="item.reviewed_by_name"> oleh {{ item.reviewed_by_name }}</span>
                                </p>
                            </div>
                            <Badge variant="outline" class="w-fit rounded-full px-3 py-1 text-[11px] font-bold" :class="getLogbookClass(item.status)">
                                {{ getLogbookLabel(item.status) }}
                            </Badge>
                        </div>
                    </div>

                    <div v-if="!logbookHistory.length" class="rounded-xl border border-dashed border-wims-border bg-slate-50 px-4 py-4 text-sm text-slate-500">
                        Belum ada riwayat logbook yang dapat ditampilkan.
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" class="border-wims-border bg-wims-card text-slate-700 hover:bg-slate-50" @click="logbookHistoryDialogOpen = false">Tutup</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>


