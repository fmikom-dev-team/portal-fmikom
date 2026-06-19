<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import StudentLayout from '@/layouts/Modules/Wims/Mahasiswa/Layout.vue';
import wimsRoutes from '@/routes/wims';
import {
    BarChart3,
    BriefcaseBusiness,
    CalendarDays,
    ClipboardList,
    Clock3,
    FileText,
    MapPin,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';


// Number roll animation function
const useNumberRoll = (targetValue: number, duration: number = 1000) => {
    const animatedValue = ref(0);
    
    onMounted(() => {
        const startValue = 0;
        const startTime = performance.now();
        
        const animate = (currentTime: number) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function (ease-out quart)
            const easeOut = 1 - Math.pow(1 - progress, 4);
            
            animatedValue.value = Math.floor(startValue + (targetValue - startValue) * easeOut);
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };
        
        requestAnimationFrame(animate);
    });
    
    return animatedValue;
};

defineOptions({
    layout: StudentLayout,
});

type UserProps = {
    name: string | null;
    avatar: string | null;
};

type AttendanceStatus = 'not_checked_in' | 'checked_in' | 'checked_out';

type AttendanceProps = {
    status: AttendanceStatus | null;
    current_time: string | null;
    location_status: string | null;
    check_in_time: string | null;
    check_out_time: string | null;
    is_late: boolean | null;
    can_check_in: boolean | null;
    can_check_out: boolean | null;
};

type InternshipProps = {
    progress_percentage: number | string | null;
    completed_days: number | string | null;
    total_days: number | string | null;
    remaining_days: number | string | null;
};

type AlertProps = {
    id?: string | number | null;
    message: string | null;
};

type HistoryProps = {
    id?: string | number | null;
    date: string | null;
    time: string | null;
    status: string | null;
    label: string | null;
};

type RegistrationProps = {
    status?: string | null;
    dashboard_state?:
        | 'not_registered'
        | 'waiting'
        | 'active'
        | 'completed'
        | null;
    company?: {
        proposal?: {
            name?: string | null;
        } | null;
        final?: {
            id?: number | null;
            name?: string | null;
        } | null;
    } | null;
    submitted_at?: string | null;
    period_label?: string | null;
};

type LatestLogbookProps = {
    date?: string | null;
    status?: string | null;
    activity?: string | null;
    reviewer_note?: string | null;
};

const props = withDefaults(
    defineProps<{
        user?: UserProps;
        attendance?: AttendanceProps;
        internship?: InternshipProps;
        registration?: RegistrationProps;
        alerts?: AlertProps[];
        latest_logbook?: LatestLogbookProps;
        history?: HistoryProps[];
    }>(),
    {
        user: () => ({
            name: null,
            avatar: null,
        }),
        attendance: () => ({
            status: 'not_checked_in',
            current_time: null,
            location_status: null,
            check_in_time: null,
            check_out_time: null,
            is_late: null,
            can_check_in: null,
            can_check_out: null,
        }),
        internship: () => ({
            progress_percentage: 0,
            completed_days: null,
            total_days: null,
            remaining_days: null,
        }),
        registration: () => ({
            status: null,
            dashboard_state: 'not_registered',
            submitted_at: null,
            period_label: null,
        }),
        alerts: () => [],
        latest_logbook: () => ({
            date: null,
            status: null,
            activity: null,
        }),
        history: () => [],
    },
);

const attendancePageHref = computed(() => wimsRoutes.attendance().url);
const logbookPageHref = computed(() => wimsRoutes.logbook().url);
const registrationPageHref = computed(() => wimsRoutes.registration().url);
const safeUserName = computed(() => props.user.name ?? 'Mahasiswa');
const attendanceStatus = computed<AttendanceStatus>(
    () => props.attendance.status ?? 'not_checked_in',
);
const canCheckIn = computed(() => props.attendance.can_check_in === true);
const canCheckOut = computed(() => props.attendance.can_check_out === true);
const isCheckedOut = computed(() => attendanceStatus.value === 'checked_out');
const isCheckedIn = computed(() => attendanceStatus.value === 'checked_in');
const isNotCheckedIn = computed(
    () => attendanceStatus.value === 'not_checked_in',
);
const progressPercentage = computed(() =>
    Number(props.internship.progress_percentage ?? 0),
);
const animatedProgress = useNumberRoll(progressPercentage.value, 1200);
const locationStatusLabel = computed(
    () => props.attendance.location_status ?? 'Status lokasi belum tersedia',
);
// Real-time clock
const currentTime = ref('');
const attendanceCurrentTime = computed(
    () => (currentTime.value || props.attendance.current_time) ?? 'Waktu belum tersedia',
);

onMounted(() => {
    const updateTime = () => {
        const now = new Date();
        currentTime.value = now.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
        });
    };
    updateTime();
    const interval = setInterval(updateTime, 1000);
    onUnmounted(() => clearInterval(interval));
});

// Progress bar animation
const progressAnimated = ref(false);
onMounted(() => {
    setTimeout(() => {
        progressAnimated.value = true;
    }, 100);
});

// Staggered animation for cards
const cardVisible = ref(false);
onMounted(() => {
    setTimeout(() => {
        cardVisible.value = true;
    }, 200);
});

// Remaining days animation
const remainingDays = computed(() =>
    Number(props.internship.remaining_days ?? 0),
);
const animatedRemainingDays = useNumberRoll(remainingDays.value, 1200);
const registrationCompanyLabel = computed(
    () =>
        props.registration?.company?.final?.name ??
        props.registration?.company?.proposal?.name ??
        'Belum ada',
);
const checkInTimeLabel = computed(
    () => props.attendance.check_in_time ?? 'Belum check-in',
);
const checkOutTimeLabel = computed(
    () => props.attendance.check_out_time ?? 'Belum check-out',
);
const historyItems = computed(() => props.history.slice(0, 3));
const latestAttendanceItem = computed(() => props.history[0] ?? null);

const historyStatusTone = (item: HistoryProps) => {
    const value = (item.label ?? item.status ?? '').toLowerCase();

    if (value.includes('alfa') || value.includes('tidak hadir')) {
        return {
            card: 'border-rose-200/60 bg-rose-50 dark:bg-rose-500/15',
            icon: 'bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400',
            text: 'text-rose-700',
        };
    }

    if (value.includes('izin')) {
        return {
            card: 'border-amber-200/60 bg-amber-50 dark:bg-amber-500/15',
            icon: 'bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400',
            text: 'text-amber-700',
        };
    }

    if (value.includes('sakit')) {
        return {
            card: 'border-violet-200/60 bg-violet-50 dark:bg-violet-500/15',
            icon: 'bg-violet-100 dark:bg-violet-500/20 text-violet-600 dark:text-violet-400',
            text: 'text-violet-700',
        };
    }

    if (value.includes('terlambat')) {
        return {
            card: 'border-amber-200/60 bg-amber-50 dark:bg-amber-500/15',
            icon: 'bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400',
            text: 'text-amber-700',
        };
    }

    return {
        card: 'border-emerald-200/60 bg-emerald-50 dark:bg-emerald-500/15',
        icon: 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400',
        text: 'text-emerald-700',
    };
};

const currentProgressDays = computed(
    () => `${props.internship.completed_days ?? 0}/${props.internship.total_days ?? 0} hari`,
);
const remainingDaysLabel = computed(() =>
    props.internship.remaining_days !== null &&
    props.internship.remaining_days !== undefined
        ? `${props.internship.remaining_days} hari tersisa`
        : 'Belum tersedia',
);

const dashboardState = computed<
    'not_registered' | 'waiting' | 'active' | 'completed'
>(() => {
    if (props.registration.dashboard_state) {
        return props.registration.dashboard_state;
    }

    if (props.registration.status === 'aktif') {
        return 'active';
    }

    if (props.registration.status === 'selesai') {
        return 'completed';
    }

    if (props.registration.status) {
        return 'waiting';
    }

    return 'not_registered';
});

const registrationStatusLabel = computed(() => {
    if (props.registration.status === 'approved') return 'Approved';
    if (props.registration.status === 'aktif') return 'Aktif';
    if (props.registration.status === 'selesai') return 'Selesai';
    if (props.registration.status === 'revisi') return 'Revisi';
    if (props.registration.status === 'rejected') return 'Rejected';
    if (props.registration.status === 'pending') return 'Pending';

    return 'Belum Mengajukan';
});

const registrationStatusClasses = computed(() => {
    if (props.registration.status === 'approved') {
        return 'border-emerald-200/60 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300';
    }

    if (props.registration.status === 'aktif') {
        return 'border-sky-200/60 dark:border-sky-500/30 bg-sky-50 dark:bg-sky-500/15 text-sky-700 dark:text-sky-300';
    }

    if (props.registration.status === 'selesai') {
        return 'border-violet-200/60 dark:border-violet-500/30 bg-violet-50 dark:bg-violet-500/15 text-violet-700 dark:text-violet-300';
    }

    if (props.registration.status === 'revisi') {
        return 'border-amber-200/60 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300';
    }

    if (props.registration.status === 'rejected') {
        return 'border-rose-200/60 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/15 text-rose-700 dark:text-rose-300';
    }

    if (props.registration.status === 'pending') {
        return 'border-blue-200/60 dark:border-blue-500/30 bg-blue-50 dark:bg-blue-500/15 text-blue-700 dark:text-blue-300';
    }

    return 'border-wims-border bg-slate-50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400';
});

const heroTitle = computed(() => {
    if (dashboardState.value === 'active') {
        return `Selamat datang, ${safeUserName.value}`;
    }

    if (dashboardState.value === 'completed') {
        return 'PKL / Magang Selesai';
    }

    if (dashboardState.value === 'waiting') {
        return 'Status Pendaftaran PKL / Magang';
    }

    return 'Mulai Proses PKL / Magang';
});

const heroDescription = computed(() => {
    if (dashboardState.value === 'active') {
        return 'Pantau presensi, progres magang, dan aktivitas harian Anda dari satu halaman ringkas.';
    }

    if (dashboardState.value === 'completed') {
        return 'Periode PKL/magang telah ditutup. Riwayat kegiatan dan progres tetap tersimpan sebagai arsip akademik.';
    }

    if (dashboardState.value === 'waiting') {
        if (props.registration.status === 'revisi') {
            return 'Pendaftaran perlu diperbaiki sebelum diproses kembali oleh kampus.';
        }

        if (props.registration.status === 'approved') {
            return 'Pendaftaran sudah disetujui dan menunggu penempatan final dari kampus.';
        }

        if (props.registration.status === 'rejected') {
            return 'Pendaftaran sebelumnya belum dapat diproses. Silakan sesuaikan data lalu ajukan kembali.';
        }

        return 'Pendaftaran sedang ditinjau oleh kampus sebelum masuk ke tahap magang aktif.';
    }

    return 'Ajukan pendaftaran terlebih dahulu agar menu presensi, logbook, dan laporan akhir pada portal web dapat dibuka.';
});

const attendanceButtonText = computed(() =>
    isCheckedIn.value
        ? 'Check-out Sekarang'
        : isCheckedOut.value
          ? 'Sudah Check-out'
          : 'Presensi Sekarang',
);

const attendanceButtonDisabled = computed(
    () =>
        isCheckedOut.value ||
        (isCheckedIn.value && !canCheckOut.value) ||
        (isNotCheckedIn.value && !canCheckIn.value),
);

const activityStatusLabel = computed(() => {
    if (isNotCheckedIn.value) return 'Belum Presensi';
    if (isCheckedIn.value) return 'Sudah Check-in';
    return 'Sudah Presensi';
});

const activityStatusSubLabel = computed(() => {
    if (isNotCheckedIn.value) return 'Belum check-in';
    if (isCheckedIn.value) return 'Menunggu check-out';
    return 'Check-in dan check-out sudah lengkap';
});

const activityStatusBadgeLabel = computed(() => {
    if (isNotCheckedIn.value) return 'Belum check-in';
    if (isCheckedIn.value) {
        return props.attendance.check_in_time
            ? `Check-in ${props.attendance.check_in_time}`
            : 'Sudah check-in';
    }

    return props.attendance.check_out_time
        ? `Check-out ${props.attendance.check_out_time}`
        : 'Sudah check-out';
});

const activityStatusClasses = computed(() => {
    if (isNotCheckedIn.value) {
        return {
            card: 'border-orange-200/60 dark:border-orange-500/30 bg-orange-50 dark:bg-orange-500/15',
            icon: 'border-orange-200/60 bg-orange-100 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400',
            badge: 'border-orange-200/60 bg-orange-100 dark:bg-orange-500/20 text-orange-700',
            accent: 'bg-orange-500',
            subtitle: 'text-orange-700',
        };
    }

    if (isCheckedIn.value) {
        return {
            card: 'border-sky-200/60 bg-sky-50 dark:bg-sky-500/15',
            icon: 'border-sky-200/60 bg-sky-100 dark:bg-sky-500/20 text-sky-600 dark:text-sky-400',
            badge: 'border-sky-200/60 bg-sky-100 dark:bg-sky-500/20 text-sky-700',
            accent: 'bg-sky-500',
            subtitle: 'text-sky-700',
        };
    }

    return {
        card: 'border-emerald-200/60 bg-emerald-50 dark:bg-emerald-500/15',
        icon: 'border-emerald-200/60 bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400',
        badge: 'border-emerald-200/60 bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700',
        accent: 'bg-emerald-500',
        subtitle: 'text-emerald-700',
    };
});

const primaryCtaHref = computed(() => {
    if (dashboardState.value === 'active') {
        return attendancePageHref.value;
    }

    if (dashboardState.value === 'completed') {
        return wimsRoutes.laporan().url;
    }

    return registrationPageHref.value;
});

const primaryCtaLabel = computed(() => {
    if (dashboardState.value === 'active') {
        return attendanceButtonText.value;
    }

    if (dashboardState.value === 'completed') {
        return 'Buka Laporan Akhir';
    }

    if (props.registration.status === 'revisi') {
        return 'Perbaiki Pendaftaran';
    }

    if (dashboardState.value === 'waiting') {
        return 'Lihat Status Pendaftaran';
    }

    return 'Ajukan PKL / Magang';
});

const latestLogbookLabel = computed(() => {
    if (dashboardState.value !== 'active' && !props.latest_logbook.date) {
        if (dashboardState.value === 'completed') return 'Periode selesai';
        if (dashboardState.value === 'waiting') return 'Menunggu periode aktif';
        return 'Belum tersedia';
    }

    if (props.latest_logbook.status === 'disetujui') return 'Disetujui';
    if (props.latest_logbook.status === 'revisi')
        return 'Perlu revisi mitra';
    if (props.latest_logbook.status === 'pending')
        return 'Menunggu review mitra';

    const dailyLogbookAlert = props.alerts.find(
        (alert) => alert.id === 'daily-logbook',
    );
    if (dailyLogbookAlert) return 'Belum diisi hari ini';

    if (props.latest_logbook.date) return 'Sudah terisi';

    return 'Belum tersedia';
});

const latestLogbookMeta = computed(() => {
    if (props.latest_logbook.date) {
        return props.latest_logbook.date;
    }

    if (dashboardState.value === 'active') {
        return 'Belum ada entri logbook terbaru';
    }

    if (dashboardState.value === 'waiting') {
        return 'Logbook dibuka saat periode aktif dimulai';
    }

    if (dashboardState.value === 'completed') {
        return 'Periode PKL sudah selesai';
    }

    return heroDescription.value;
});

const latestLogbookClasses = computed(() => {
    if (latestLogbookLabel.value === 'Disetujui') {
        return 'border-emerald-200 bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:border-emerald-800/50 dark:bg-emerald-900/20 dark:text-emerald-300';
    }

    if (latestLogbookLabel.value === 'Perlu revisi mitra') {
        return 'border-rose-200 bg-rose-50 dark:bg-rose-500/15 text-rose-700 dark:border-rose-800/50 dark:bg-rose-900/20 dark:text-rose-300';
    }

    if (latestLogbookLabel.value === 'Menunggu review mitra') {
        return 'border-amber-200 bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:border-amber-800/50 dark:bg-amber-900/20 dark:text-amber-300';
    }

    if (latestLogbookLabel.value === 'Belum diisi hari ini') {
        return 'border-wims-border bg-slate-50 dark:bg-slate-800/50 text-slate-700 dark:text-slate-300 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-300';
    }

    return 'border-wims-border bg-slate-50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-400';
});

const showHeroActions = computed(() => dashboardState.value === 'active');
</script>

<template>
    <Head title="Dashboard Mahasiswa" />

    <div class="min-h-full">
        <!-- Animated background decorations -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-32 -right-32 h-[500px] w-[500px] rounded-full bg-gradient-to-br from-blue-400/[0.06] to-cyan-400/[0.04] blur-3xl animate-pulse dark:from-blue-500/[0.04] dark:to-cyan-400/[0.025]" style="animation-duration: 7s;" />
            <div class="absolute top-1/3 -left-16 h-[400px] w-[400px] rounded-full bg-gradient-to-tr from-indigo-400/[0.05] to-violet-400/[0.03] blur-3xl animate-pulse dark:from-blue-600/[0.03] dark:to-indigo-500/[0.02]" style="animation-duration: 10s; animation-delay: 3s;" />
            <div class="absolute -bottom-16 right-1/3 h-[350px] w-[350px] rounded-full bg-gradient-to-tl from-cyan-400/[0.04] to-blue-400/[0.03] blur-3xl animate-pulse dark:from-cyan-400/[0.025] dark:to-blue-500/[0.02]" style="animation-duration: 12s; animation-delay: 5s;" />
            <!-- Subtle dot grid -->
            <div class="absolute inset-0 opacity-[0.02] dark:opacity-[0.04]" style="background-image: radial-gradient(circle, currentColor 0.5px, transparent 0.5px); background-size: 24px 24px;" />
        </div>

        <div class="relative space-y-5 lg:space-y-7">
            <!-- Hero Section -->
            <div
                class="relative overflow-hidden rounded-2xl lg:rounded-3xl transition-all duration-700"
                :class="cardVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
            >
                <!-- Hero gradient background -->
                <div class="absolute inset-0 bg-gradient-to-br from-blue-600 via-blue-500 to-cyan-500 dark:from-[#1D3E8A] dark:via-[#1F5FCC] dark:to-[#0E7ACF]" />
                <div class="absolute inset-0 bg-gradient-to-t from-blue-700/30 via-transparent to-transparent dark:from-[#07152f]/55 dark:via-[#0f172a]/10 dark:to-transparent" />
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(255,255,255,0.18),_transparent_38%),radial-gradient(circle_at_bottom_right,_rgba(34,211,238,0.18),_transparent_32%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(255,255,255,0.08),_transparent_32%),radial-gradient(circle_at_bottom_right,_rgba(34,211,238,0.10),_transparent_28%)]" />
                <!-- Decorative shapes -->
                <div class="absolute top-0 right-0 h-72 w-72 -translate-y-1/2 translate-x-1/4 rounded-full bg-white/[0.08] blur-3xl dark:bg-white/[0.04]" />
                <div class="absolute bottom-0 left-0 h-56 w-56 -translate-x-1/4 translate-y-1/3 rounded-full bg-blue-900/20 blur-3xl dark:bg-slate-950/35" />
                <div class="absolute top-8 right-10 hidden h-20 w-20 rounded-full border border-white/[0.08] dark:border-white/[0.05] lg:block" />
                <div class="absolute top-16 right-20 hidden h-36 w-36 rounded-full border border-white/[0.05] dark:border-white/[0.03] lg:block" />
                <!-- Grid pattern -->
                <div class="absolute inset-0 opacity-[0.04] dark:opacity-[0.03]" style="background-image: radial-gradient(circle, white 0.5px, transparent 0.5px); background-size: 20px 20px;" />

                <div class="relative px-5 py-7 sm:px-8 sm:py-9 lg:px-10 lg:py-11">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0 max-w-2xl">
                            <!-- Status pills -->
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3.5 py-1.5 text-[11px] font-bold text-white/90 ring-1 ring-white/20 backdrop-blur-md dark:bg-white/[0.10] dark:text-white/85 dark:ring-white/15">
                                    <span class="relative flex h-1.5 w-1.5">
                                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75" />
                                        <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-400" />
                                    </span>
                                    {{ registrationStatusLabel }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1.5 text-[11px] font-semibold text-white/80 ring-1 ring-white/15 backdrop-blur-md dark:bg-white/[0.07] dark:text-white/72 dark:ring-white/10">
                                    <Clock3 class="size-3" />
                                    {{ attendanceCurrentTime }}
                                </span>
                            </div>

                            <!-- Greeting -->
                            <h1 class="mt-4 text-[22px] font-bold tracking-tight text-white sm:text-[28px] lg:text-[38px] leading-[1.15]">
                                {{ heroTitle }}
                            </h1>
                            <p class="mt-2.5 text-[13px] leading-relaxed text-white/78 dark:text-white/70 sm:text-sm lg:max-w-xl lg:text-base">
                                {{ heroDescription }}
                            </p>
                        </div>

                        <!-- CTA Buttons -->
                        <div v-if="showHeroActions" class="flex flex-col gap-2.5 sm:flex-row lg:flex-col lg:min-w-[200px]">
                            <Link :href="primaryCtaHref" class="group flex-1">
                                <Button
                                    type="button"
                                    class="relative h-12 w-full overflow-hidden rounded-xl bg-white px-5 text-[13px] font-bold text-blue-700 shadow-lg shadow-blue-900/20 transition-all duration-300 hover:scale-[1.02] hover:shadow-xl hover:shadow-blue-900/30 active:scale-95 dark:bg-white/95 dark:text-[#1847a6] dark:shadow-[0_12px_30px_-16px_rgba(8,15,30,0.7)] dark:hover:shadow-[0_16px_34px_-16px_rgba(8,15,30,0.85)]"
                                    :disabled="attendanceButtonDisabled"
                                >
                                    <span class="relative z-10 flex items-center justify-center gap-2">
                                        <Clock3 class="size-4" />
                                        {{ primaryCtaLabel }}
                                    </span>
                                    <div class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-blue-100/60 to-transparent transition-transform duration-700 group-hover:translate-x-full" />
                                </Button>
                            </Link>
                            <Link :href="logbookPageHref" class="group flex-1">
                                <Button
                                    type="button"
                                    class="h-12 w-full rounded-xl border-2 border-white/25 bg-white/10 px-5 text-[13px] font-bold text-white backdrop-blur-sm transition-all duration-300 hover:scale-[1.02] hover:border-white/40 hover:bg-white/20 active:scale-95 dark:border-white/15 dark:bg-slate-900/12 dark:text-white/92 dark:hover:border-white/24 dark:hover:bg-white/[0.12]"
                                >
                                    <span class="flex items-center justify-center gap-2">
                                        <ClipboardList class="size-4" />
                                        Isi Logbook
                                    </span>
                                </Button>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4 lg:gap-4" :class="cardVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'" style="transition: all 0.6s ease-out 0.15s;">
                <!-- Progress Card -->
                <div class="group relative overflow-hidden rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-wims-border/50 shadow-[0_1px_3px_rgba(0,0,0,0.04)] transition-all duration-300 hover:shadow-[0_8px_24px_-8px_rgba(59,130,246,0.12)] hover:-translate-y-0.5">
                    <div class="absolute top-0 inset-x-0 h-[3px] bg-gradient-to-r from-blue-500 to-blue-400 rounded-t-2xl" />
                    <div class="p-4 lg:p-5">
                        <div class="flex items-center justify-between">
                            <div class="flex size-9 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-500/15 text-blue-600 dark:text-blue-400 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                                <BarChart3 class="size-4" />
                            </div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Progress</p>
                        </div>
                        <p class="mt-3 text-2xl font-bold tracking-tight text-wims-text lg:text-3xl">{{ animatedProgress }}<span class="text-base text-slate-400 dark:text-slate-500">%</span></p>
                        <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700/50">
                            <div class="h-full rounded-full bg-gradient-to-r from-blue-600 to-blue-400 transition-all duration-1000 ease-out" :style="{ width: progressAnimated ? `${progressPercentage}%` : '0%' }" />
                        </div>
                        <p class="mt-2 text-[11px] font-medium text-slate-500 dark:text-slate-400">{{ currentProgressDays }}</p>
                    </div>
                </div>

                <!-- Remaining Days -->
                <div class="group relative overflow-hidden rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-wims-border/50 shadow-[0_1px_3px_rgba(0,0,0,0.04)] transition-all duration-300 hover:shadow-[0_8px_24px_-8px_rgba(6,182,212,0.12)] hover:-translate-y-0.5">
                    <div class="absolute top-0 inset-x-0 h-[3px] bg-gradient-to-r from-cyan-500 to-teal-400 rounded-t-2xl" />
                    <div class="p-4 lg:p-5">
                        <div class="flex items-center justify-between">
                            <div class="flex size-9 items-center justify-center rounded-lg bg-cyan-50 dark:bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                                <CalendarDays class="size-4" />
                            </div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Sisa Hari</p>
                        </div>
                        <p class="mt-3 text-2xl font-bold tracking-tight text-wims-text lg:text-3xl">{{ animatedRemainingDays }}</p>
                        <p class="mt-2 text-[11px] font-medium text-slate-500 dark:text-slate-400">{{ remainingDaysLabel }}</p>
                    </div>
                </div>

                <!-- Attendance Status -->
                <div
                    class="group relative overflow-hidden rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-wims-border/50 shadow-[0_1px_3px_rgba(0,0,0,0.04)] transition-all duration-300 hover:-translate-y-0.5"
                    :class="isNotCheckedIn ? 'hover:shadow-[0_8px_24px_-8px_rgba(249,115,22,0.12)]' : isCheckedIn ? 'hover:shadow-[0_8px_24px_-8px_rgba(14,165,233,0.12)]' : 'hover:shadow-[0_8px_24px_-8px_rgba(16,185,129,0.12)]'"
                >
                    <div class="absolute top-0 inset-x-0 h-[3px] rounded-t-2xl" :class="isNotCheckedIn ? 'bg-gradient-to-r from-orange-500 to-amber-400' : isCheckedIn ? 'bg-gradient-to-r from-sky-500 to-blue-400' : 'bg-gradient-to-r from-emerald-500 to-teal-400'" />
                    <div class="p-4 lg:p-5">
                        <div class="flex items-center justify-between">
                            <div class="relative flex size-9 items-center justify-center rounded-lg transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3" :class="activityStatusClasses.icon">
                                <Clock3 class="size-4" />
                                <span v-if="isCheckedIn" class="absolute -top-0.5 -right-0.5 flex h-2.5 w-2.5">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-sky-400 opacity-75" />
                                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-sky-500" />
                                </span>
                            </div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Presensi</p>
                        </div>
                        <p class="mt-3 text-sm font-bold tracking-tight text-wims-text lg:text-base leading-tight">{{ activityStatusLabel }}</p>
                        <Badge variant="outline" class="mt-2 rounded-full border px-2 py-0.5 text-[10px] font-semibold" :class="activityStatusClasses.badge">
                            {{ activityStatusBadgeLabel }}
                        </Badge>
                    </div>
                </div>

                <!-- Location -->
                <div class="group relative overflow-hidden rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-wims-border/50 shadow-[0_1px_3px_rgba(0,0,0,0.04)] transition-all duration-300 hover:shadow-[0_8px_24px_-8px_rgba(16,185,129,0.12)] hover:-translate-y-0.5">
                    <div class="absolute top-0 inset-x-0 h-[3px] bg-gradient-to-r from-emerald-500 to-green-400 rounded-t-2xl" />
                    <div class="p-4 lg:p-5">
                        <div class="flex items-center justify-between">
                            <div class="flex size-9 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                                <MapPin class="size-4" />
                            </div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Lokasi</p>
                        </div>
                        <p class="mt-3 text-sm font-bold tracking-tight text-wims-text lg:text-base leading-tight line-clamp-2">{{ locationStatusLabel }}</p>
                        <p class="mt-2 text-[11px] font-medium text-slate-500 dark:text-slate-400">{{ checkOutTimeLabel }}</p>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid gap-4 xl:grid-cols-[1.35fr_1fr]" :class="cardVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'" style="transition: all 0.6s ease-out 0.3s;">
                <!-- Left Column -->
                <div class="space-y-4">
                    <!-- Status Magang Card -->
                    <div class="rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-wims-border/50 shadow-[0_1px_3px_rgba(0,0,0,0.04)] transition-all duration-300 hover:shadow-[0_8px_24px_-8px_rgba(0,0,0,0.06)]">
                        <div class="p-5 sm:p-6">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-10 items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-500/15 text-blue-600 dark:text-blue-400">
                                        <BriefcaseBusiness class="size-5" />
                                    </div>
                                    <div>
                                        <p class="text-[15px] font-bold text-wims-text">Status Magang</p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Ringkasan progres dan periode</p>
                                    </div>
                                </div>
                                <Badge variant="outline" class="w-fit rounded-full border px-3 py-1 text-[11px] font-bold" :class="registrationStatusClasses">
                                    {{ registrationStatusLabel }}
                                </Badge>
                            </div>

                            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div class="rounded-xl border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-4 py-3 transition-colors hover:bg-slate-100/80 dark:hover:bg-slate-700/40">
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Perusahaan</p>
                                    <p class="mt-1.5 text-[13px] font-bold leading-5 text-wims-text break-words sm:leading-tight">{{ registrationCompanyLabel }}</p>
                                </div>
                                <div class="rounded-xl border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-4 py-3 transition-colors hover:bg-slate-100/80 dark:hover:bg-slate-700/40">
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Periode</p>
                                    <p class="mt-1.5 text-[13px] font-bold leading-5 text-wims-text break-words sm:leading-tight">{{ props.registration.period_label || 'Belum ditentukan' }}</p>
                                </div>
                                <div class="rounded-xl border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-4 py-3 transition-colors hover:bg-slate-100/80 dark:hover:bg-slate-700/40">
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Progress</p>
                                    <p class="mt-1.5 text-[13px] font-bold leading-5 text-wims-text break-words">{{ currentProgressDays }}</p>
                                </div>
                                <div class="rounded-xl border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-4 py-3 transition-colors hover:bg-slate-100/80 dark:hover:bg-slate-700/40">
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Pengajuan</p>
                                    <p class="mt-1.5 text-[13px] font-bold leading-5 text-wims-text break-words sm:leading-tight">{{ props.registration.submitted_at || 'Belum ada' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Aktivitas Terbaru Card -->
                    <div class="rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-wims-border/50 shadow-[0_1px_3px_rgba(0,0,0,0.04)] transition-all duration-300 hover:shadow-[0_8px_24px_-8px_rgba(0,0,0,0.06)]">
                        <div class="p-5 sm:p-6">
                            <div class="flex items-center gap-3">
                                <div class="flex size-10 items-center justify-center rounded-xl bg-violet-50 dark:bg-violet-500/15 text-violet-600 dark:text-violet-400">
                                    <ClipboardList class="size-5" />
                                </div>
                                <div>
                                    <p class="text-[15px] font-bold text-wims-text">Aktivitas Terbaru</p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Presensi, logbook, dan pengajuan</p>
                                </div>
                            </div>

                            <div class="mt-5 space-y-3">
                                <!-- Latest attendance -->
                                <div class="rounded-xl border px-4 py-3.5 transition-colors" :class="latestAttendanceItem ? historyStatusTone(latestAttendanceItem).card : 'border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40'">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Presensi terakhir</p>
                                            <p class="mt-1 text-[13px] font-bold text-wims-text">{{ latestAttendanceItem?.date || 'Belum ada riwayat' }}</p>
                                        </div>
                                        <Clock3 class="size-4 flex-shrink-0" :class="latestAttendanceItem ? historyStatusTone(latestAttendanceItem).text : 'text-slate-400'" />
                                    </div>
                                    <p class="mt-2 text-[12px] font-semibold" :class="latestAttendanceItem ? historyStatusTone(latestAttendanceItem).text : 'text-slate-500 dark:text-slate-400'">
                                        {{ latestAttendanceItem ? `${latestAttendanceItem.time ? latestAttendanceItem.time + ' WIB - ' : ''}${latestAttendanceItem.label ?? latestAttendanceItem.status}` : 'Tidak ada aktivitas' }}
                                    </p>
                                </div>

                                <!-- Latest logbook -->
                                <div class="rounded-xl border px-4 py-3.5 transition-colors" :class="latestLogbookClasses">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Logbook terakhir</p>
                                            <p class="mt-1 text-[13px] font-bold leading-5 text-wims-text break-words">{{ latestLogbookMeta }}</p>
                                        </div>
                                        <ClipboardList class="size-4 flex-shrink-0 text-slate-400" />
                                    </div>
                                    <p class="mt-2 text-[12px] font-semibold leading-5 break-words">{{ latestLogbookLabel }}</p>
                                </div>

                                <!-- Registration status -->
                                <div class="rounded-xl border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-4 py-3.5 transition-colors hover:bg-slate-100/80 dark:hover:bg-slate-700/40">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Status pengajuan</p>
                                            <p class="mt-1 text-[13px] font-bold text-wims-text">{{ registrationStatusLabel }}</p>
                                        </div>
                                        <FileText class="size-4 flex-shrink-0 text-slate-400" />
                                    </div>
                                    <p class="mt-2 text-[12px] font-medium text-slate-500 dark:text-slate-400">{{ props.registration.submitted_at || 'Belum ada data pengajuan' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                    <!-- Progress & Presensi Card -->
                    <div class="rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-wims-border/50 shadow-[0_1px_3px_rgba(0,0,0,0.04)] transition-all duration-300 hover:shadow-[0_8px_24px_-8px_rgba(0,0,0,0.06)]">
                        <div class="p-5 sm:p-6">
                            <div class="flex items-center gap-3">
                                <div class="flex size-10 items-center justify-center rounded-xl bg-cyan-50 dark:bg-cyan-500/15 text-cyan-600 dark:text-cyan-400">
                                    <CalendarDays class="size-5" />
                                </div>
                                <div>
                                    <p class="text-[15px] font-bold text-wims-text">Progress & Presensi</p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Fokus aktivitas harian</p>
                                </div>
                            </div>

                            <div class="mt-5 space-y-3">
                                <!-- Progress bar section -->
                                <div class="rounded-xl border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 p-4">
                                    <div class="flex items-center justify-between">
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Progres magang</p>
                                        <span class="text-[11px] font-bold text-blue-600 dark:text-blue-400">{{ progressPercentage }}%</span>
                                    </div>
                                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700/50">
                                        <div class="h-full rounded-full bg-gradient-to-r from-blue-600 to-cyan-500 transition-all duration-1000 ease-out shadow-[0_0_8px_rgba(59,130,246,0.3)]" :style="{ width: `${progressPercentage}%` }" />
                                    </div>
                                    <p class="mt-2 text-[11px] font-medium text-slate-500 dark:text-slate-400">{{ remainingDaysLabel }}</p>
                                </div>

                                <!-- Check-in/out times -->
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div class="rounded-xl border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-4 py-3 transition-colors hover:bg-slate-100/80 dark:hover:bg-slate-700/40">
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Jam masuk</p>
                                        <p class="mt-1.5 text-[13px] font-bold text-wims-text">{{ checkInTimeLabel }}</p>
                                    </div>
                                    <div class="rounded-xl border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-4 py-3 transition-colors hover:bg-slate-100/80 dark:hover:bg-slate-700/40">
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Jam keluar</p>
                                        <p class="mt-1.5 text-[13px] font-bold text-wims-text">{{ checkOutTimeLabel }}</p>
                                    </div>
                                </div>

                                <!-- Activity status -->
                                <div class="flex flex-col items-start gap-2 rounded-xl border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                                    <Badge variant="outline" class="rounded-full border px-2.5 py-0.5 text-[10px] font-bold" :class="activityStatusClasses.badge">
                                        {{ activityStatusLabel }}
                                    </Badge>
                                    <span class="min-w-0 text-[11px] font-semibold leading-5 text-slate-500 dark:text-slate-400 sm:max-w-[220px] sm:text-right">{{ locationStatusLabel }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Riwayat Presensi Card -->
                    <div class="rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-wims-border/50 shadow-[0_1px_3px_rgba(0,0,0,0.04)] transition-all duration-300 hover:shadow-[0_8px_24px_-8px_rgba(0,0,0,0.06)]">
                        <div class="p-5 sm:p-6">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-10 items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400">
                                        <CalendarDays class="size-5" />
                                    </div>
                                    <p class="text-[15px] font-bold text-wims-text">Riwayat Presensi</p>
                                </div>
                            </div>

                            <div v-if="historyItems.length" class="mt-4 space-y-2.5">
                                <div v-for="item in historyItems" :key="String(item.id ?? item.date ?? item.time)" class="flex flex-col items-start gap-2 rounded-xl border px-4 py-3 transition-colors sm:flex-row sm:items-center sm:justify-between" :class="historyStatusTone(item).card">
                                    <div class="min-w-0">
                                        <p class="text-[13px] font-bold text-wims-text">{{ item.date || 'Tanggal tidak tersedia' }}</p>
                                        <p class="mt-0.5 text-[11px] font-medium text-slate-500 dark:text-slate-400">{{ item.time ? `${item.time} WIB` : 'Waktu belum tersedia' }}</p>
                                    </div>
                                    <Badge variant="outline" class="flex-shrink-0 rounded-full border px-2.5 py-0.5 text-[10px] font-bold" :class="historyStatusTone(item).text">
                                        {{ item.label ?? item.status }}
                                    </Badge>
                                </div>
                            </div>
                            <div v-else class="mt-5 flex flex-col items-center rounded-xl border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-4 py-8 text-center">
                                <div class="flex size-11 items-center justify-center rounded-full bg-slate-200/80 dark:bg-slate-700/50 mb-2.5">
                                    <CalendarDays class="size-5 text-slate-400 dark:text-slate-500" />
                                </div>
                                <p class="text-[12px] font-semibold text-slate-600 dark:text-slate-400">Belum ada riwayat</p>
                                <p class="mt-0.5 text-[10px] text-slate-400 dark:text-slate-500">Mulai presensi untuk melihat riwayat</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
