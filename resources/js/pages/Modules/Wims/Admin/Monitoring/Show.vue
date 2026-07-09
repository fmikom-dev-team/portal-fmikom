<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Clock3,
    Download,
    FileCheck2,
    ClipboardList,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import WimsAdminLayout from '@/layouts/Modules/Wims/Admin/Layout.vue';
import { formatIndonesianDateLabel, formatIndonesianDateTime, formatIndonesianTime } from '@/lib/date';

defineOptions({
    layout: WimsAdminLayout,
});

type StudentProps = {
    id?: number | null;
    name?: string | null;
    nim?: string | null;
    company?: {
        id?: number | null;
        name?: string | null;
    } | null;
    pendaftaran_id?: number | null;
    status_pendaftaran?: string | null;
    is_ready_for_assessment?: boolean;
    period_start?: string | null;
    period_end?: string | null;
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

type LogbookHistoryItem = {
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
        total_tepat_waktu?: number;
        total_terlambat?: number;
        total_izin?: number;
        total_sakit?: number;
        total_alfa?: number;
    };
    logbook?: {
        total?: number;
        total_disetujui?: number;
        total_revisi?: number;
        total_pending?: number;
    };
};

type AssessmentProps = {
    status_key?: string | null;
    status_label?: string | null;
    is_complete?: boolean;
    latest_submitted_at?: string | null;
    dosen?: {
        score?: number | null;
        status_label?: string | null;
    };
    mitra?: {
        score?: number | null;
        status_label?: string | null;
    };
};

type ActivitySectionProps = {
    date?: string | null;
    attendance?: {
        check_in?: string | null;
        check_out?: string | null;
        check_in_photo_url?: string | null;
        check_out_photo_url?: string | null;
        status?: string | null;
    } | null;
    logbook?: {
        tanggal_label?: string | null;
        aktivitas?: string | null;
        kompetensi?: string | null;
        status?: string | null;
        catatan_mitra?: string | null;
        reviewed_by_name?: string | null;
        reviewed_at?: string | null;
    } | null;
};

const props = defineProps<{
    student: StudentProps;
    today: ActivitySectionProps;
    selected: ActivitySectionProps;
    history: {
        attendance?: AttendanceHistoryItem[];
        logbook?: LogbookHistoryItem[];
    };
    assessment?: AssessmentProps;
    summary?: SummaryProps;
}>();

const attendanceHistory = computed(() => props.history.attendance ?? []);
const logbookHistory = computed(() => props.history.logbook ?? []);
const summary = computed(() => props.summary ?? {});
const assessment = computed(() => props.assessment ?? {});

const periodLabel = computed(() => {
    if (props.student.period_start && props.student.period_end) {
        return `${formatIndonesianDateLabel(props.student.period_start)} - ${formatIndonesianDateLabel(props.student.period_end)}`;
    }

    return formatIndonesianDateLabel(props.student.period_start || props.student.period_end);
});

const attendanceDownloadUrl = computed(() => {
    if (!props.student.pendaftaran_id) {
        return '#';
    }

    return `/wims/admin/monitoring/${props.student.pendaftaran_id}/download/absensi`;
});

const logbookDownloadUrl = computed(() => {
    if (!props.student.pendaftaran_id) {
        return '#';
    }

    return `/wims/admin/monitoring/${props.student.pendaftaran_id}/download/logbook`;
});

const attendanceClass = (value?: string | null) => {
    if (value === 'hadir' || value === 'tepat_waktu') return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    if (value === 'terlambat') return 'border-amber-200 bg-amber-50 text-amber-700';
    if (value === 'izin') return 'border-sky-200 bg-sky-50 text-sky-700';
    if (value === 'sakit') return 'border-violet-200 bg-violet-50 text-violet-700';
    if (value === 'alfa') return 'border-rose-200 bg-rose-50 text-rose-700';
    if (value === 'hari_libur') return 'border-blue-200 bg-blue-50 text-blue-700';
    if (value === 'bukan_hari_kerja') return 'border-zinc-200 bg-zinc-50 text-zinc-700';
    return 'border-zinc-200 bg-zinc-50 text-zinc-600';
};

const attendanceLabel = (value?: string | null) => {
    if (value === 'hadir' || value === 'tepat_waktu') return 'Hadir';
    if (value === 'terlambat') return 'Terlambat';
    if (value === 'izin') return 'Izin';
    if (value === 'sakit') return 'Sakit';
    if (value === 'alfa') return 'Alfa';
    if (value === 'hari_libur') return 'Hari libur';
    if (value === 'bukan_hari_kerja') return 'Bukan hari kerja';
    if (!value) return 'Belum ada presensi';
    return value.replace(/_/g, ' ');
};

const logbookClass = (value?: string | null) => {
    if (value === 'disetujui' || value === 'approved') return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    if (value === 'revisi') return 'border-amber-200 bg-amber-50 text-amber-700';
    if (value === 'rejected') return 'border-rose-200 bg-rose-50 text-rose-700';
    return 'border-zinc-200 bg-zinc-50 text-zinc-600';
};

const logbookLabel = (value?: string | null) => {
    if (value === 'disetujui' || value === 'approved') return 'Disetujui';
    if (value === 'revisi') return 'Revisi';
    if (value === 'rejected') return 'Ditolak';
    if (!value) return 'Belum ada logbook';
    return value.replace(/_/g, ' ');
};

const assessmentBadgeClass = computed(() => {
    if (assessment.value.is_complete) {
        return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    }

    if (assessment.value.status_key === 'draft') {
        return 'border-amber-200 bg-amber-50 text-amber-700';
    }

    if (assessment.value.status_key === 'final_dosen' || assessment.value.status_key === 'final_mitra') {
        return 'border-blue-200 bg-blue-50 text-blue-700';
    }

    return 'border-zinc-200 bg-zinc-50 text-zinc-600';
});

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
</script>

<template>
    <Head title="Detail Monitoring Mahasiswa" />

    <div class="mx-auto w-full max-w-[1320px] space-y-5 px-4 py-4 sm:px-6 sm:py-6 lg:space-y-6 lg:px-8 lg:py-8">
        <Card class="rounded-xl border border-zinc-200 bg-white py-0 shadow-none">
            <CardContent class="px-5 py-5">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">
                            Detail Monitoring Mahasiswa
                        </p>
                        <h1 class="mt-1 text-[28px] font-bold tracking-tight text-slate-950">
                            {{ student.name || '-' }}
                        </h1>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-bold text-slate-600">
                                {{ student.nim || 'NIM belum tersedia' }}
                            </span>
                            <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-bold text-slate-600">
                                {{ student.company?.name || 'Belum ada perusahaan final' }}
                            </span>
                            <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-bold text-slate-600">
                                {{ periodLabel || '-' }}
                            </span>
                            <Badge class="rounded-full border px-3 py-1 text-xs font-bold shadow-none" :class="assessmentBadgeClass">
                                {{ assessment.status_label || 'Belum Dinilai' }}
                            </Badge>
                        </div>
                        <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-600">
                            Ringkasan monitoring untuk memeriksa presensi, logbook, dan hasil penilaian mahasiswa pada periode PKL yang dipilih.
                        </p>
                    </div>

                    <div class="flex w-full flex-col items-stretch gap-3 self-start sm:w-auto sm:items-end">
                        <Button as-child variant="outline" class="h-9 w-full rounded-xl border border-zinc-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-none hover:bg-zinc-50 sm:w-auto">
                            <Link href="/wims/admin/monitoring">
                                <ArrowLeft class="mr-2 size-4" />
                                Kembali
                            </Link>
                        </Button>
                        <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
                            <Button as-child class="h-9 w-full rounded-xl bg-gradient-to-r from-blue-600 to-blue-500 px-4 text-sm font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:shadow-blue-500/30 active:scale-[0.98] dark:from-[#214FAF] dark:to-[#0F6FBE] dark:shadow-[0_14px_34px_-18px_rgba(8,15,30,0.84)] dark:hover:shadow-[0_18px_38px_-18px_rgba(8,15,30,0.92)] sm:w-auto">
                                <a :href="attendanceDownloadUrl">
                                    <Download class="mr-2 size-4" />
                                    Unduh Absensi
                                </a>
                            </Button>
                            <Button as-child class="h-9 w-full rounded-xl bg-gradient-to-r from-blue-600 to-blue-500 px-4 text-sm font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:shadow-blue-500/30 active:scale-[0.98] dark:from-[#214FAF] dark:to-[#0F6FBE] dark:shadow-[0_14px_34px_-18px_rgba(8,15,30,0.84)] dark:hover:shadow-[0_18px_38px_-18px_rgba(8,15,30,0.92)] sm:w-auto">
                                <a :href="logbookDownloadUrl">
                                    <Download class="mr-2 size-4" />
                                    Unduh Logbook
                                </a>
                            </Button>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <Card class="rounded-xl border border-zinc-200 bg-white py-0 shadow-none">
                <CardContent class="px-5 py-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold text-slate-500">Presensi</p>
                            <p class="mt-2 text-[22px] font-bold tracking-tight text-blue-600">
                                {{ summary.attendance?.total ?? 0 }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500">Total riwayat presensi</p>
                        </div>
                        <div class="flex size-9 items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 text-zinc-600">
                            <Clock3 class="size-4" />
                        </div>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">
                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-emerald-700">Hadir {{ summary.attendance?.total_tepat_waktu ?? 0 }}</span>
                        <span class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-amber-700">Terlambat {{ summary.attendance?.total_terlambat ?? 0 }}</span>
                        <span class="rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-blue-700">Izin {{ summary.attendance?.total_izin ?? 0 }}</span>
                        <span class="rounded-full border border-violet-200 bg-violet-50 px-2.5 py-1 text-violet-700">Sakit {{ summary.attendance?.total_sakit ?? 0 }}</span>
                        <span class="rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-rose-700">Alfa {{ summary.attendance?.total_alfa ?? 0 }}</span>
                    </div>
                </CardContent>
            </Card>

            <Card class="rounded-xl border border-zinc-200 bg-white py-0 shadow-none">
                <CardContent class="px-5 py-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold text-slate-500">Logbook</p>
                            <p class="mt-2 text-[22px] font-bold tracking-tight text-emerald-700">
                                {{ summary.logbook?.total ?? 0 }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500">Total entri logbook</p>
                        </div>
                        <div class="flex size-9 items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 text-zinc-600">
                            <ClipboardList class="size-4" />
                        </div>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">
                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-emerald-700">Disetujui {{ summary.logbook?.total_disetujui ?? 0 }}</span>
                        <span class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-amber-700">Revisi {{ summary.logbook?.total_revisi ?? 0 }}</span>
                        <span class="rounded-full border border-zinc-200 bg-zinc-50 px-2.5 py-1 text-zinc-700">Pending {{ summary.logbook?.total_pending ?? 0 }}</span>
                    </div>
                </CardContent>
            </Card>

            <Card class="rounded-xl border border-zinc-200 bg-white py-0 shadow-none md:col-span-2 xl:col-span-1">
                <CardContent class="px-5 py-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold text-slate-500">Penilaian</p>
                            <p class="mt-2 text-[22px] font-bold tracking-tight text-slate-950">
                                {{ assessment.status_label || 'Belum Dinilai' }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ assessment.is_complete ? 'Nilai dosen dan mitra sudah lengkap.' : 'Nilai masih berjalan atau belum lengkap.' }}
                            </p>
                        </div>
                        <div class="flex size-9 items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 text-zinc-600">
                            <FileCheck2 class="size-4" />
                        </div>
                    </div>
                    <div class="mt-3 space-y-2 text-sm text-slate-700">
                        <p class="font-semibold text-slate-900">Dosen: <span class="font-bold">{{ assessment.dosen?.score ?? '-' }}</span></p>
                        <p class="font-semibold text-slate-900">Mitra: <span class="font-bold">{{ assessment.mitra?.score ?? '-' }}</span></p>
                        <p class="text-xs text-slate-500">
                            {{ assessment.latest_submitted_at ? `Diperbarui ${formatDateTime(assessment.latest_submitted_at)}` : 'Belum ada pengiriman penilaian.' }}
                        </p>
                    </div>
                </CardContent>
            </Card>
        </section>

        <div class="grid gap-5 xl:grid-cols-2">
            <Card class="rounded-xl border border-zinc-200 bg-white py-0 shadow-none">
                <CardHeader class="border-b border-zinc-200 px-5 py-4">
                    <CardTitle class="text-[15px] font-bold text-slate-950">Riwayat Presensi</CardTitle>
                    <CardDescription class="mt-1 text-sm leading-6 text-slate-600">
                        Seluruh jejak presensi mahasiswa pada periode yang dipilih.
                    </CardDescription>
                </CardHeader>
                <CardContent class="max-h-[620px] overflow-auto px-5 py-5">
                    <div class="space-y-3">
                        <div v-for="item in attendanceHistory" :key="item.id" class="rounded-xl border border-zinc-200 bg-zinc-50/50 px-4 py-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-sm font-bold text-slate-950">{{ item.tanggal_label || '-' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Masuk {{ formatTime(item.check_in) || '-' }} • Keluar {{ formatTime(item.check_out) || '-' }}
                                    </p>
                                </div>
                                <Badge class="rounded-full border px-3 py-1 text-xs font-bold shadow-none" :class="attendanceClass(item.status)">
                                    {{ attendanceLabel(item.status) }}
                                </Badge>
                            </div>
                            <p class="mt-3 text-sm text-slate-600">
                                {{ item.keterangan || 'Tidak ada keterangan tambahan.' }}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="rounded-xl border border-zinc-200 bg-white py-0 shadow-none">
                <CardHeader class="border-b border-zinc-200 px-5 py-4">
                    <CardTitle class="text-[15px] font-bold text-slate-950">Riwayat Logbook</CardTitle>
                    <CardDescription class="mt-1 text-sm leading-6 text-slate-600">
                        Entri logbook, status review, dan catatan pembimbing.
                    </CardDescription>
                </CardHeader>
                <CardContent class="max-h-[620px] overflow-auto px-5 py-5">
                    <div class="space-y-3">
                        <div v-for="item in logbookHistory" :key="item.id" class="rounded-xl border border-zinc-200 bg-zinc-50/50 px-4 py-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-sm font-bold text-slate-950">{{ item.tanggal_label || '-' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Direview {{ item.reviewed_by_name || 'belum ada reviewer' }}
                                        <span v-if="item.reviewed_at">• {{ formatDateTime(item.reviewed_at) }}</span>
                                    </p>
                                </div>
                                <Badge class="rounded-full border px-3 py-1 text-xs font-bold shadow-none" :class="logbookClass(item.status)">
                                    {{ logbookLabel(item.status) }}
                                </Badge>
                            </div>
                            <div class="mt-3 space-y-2 text-sm text-slate-600">
                                <p class="line-clamp-3"><span class="font-semibold text-slate-900">Aktivitas:</span> {{ item.aktivitas || '-' }}</p>
                                <p class="line-clamp-3"><span class="font-semibold text-slate-900">Kompetensi:</span> {{ item.kompetensi || '-' }}</p>
                                <p class="line-clamp-3"><span class="font-semibold text-slate-900">Catatan:</span> {{ item.catatan_mitra || '-' }}</p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>




