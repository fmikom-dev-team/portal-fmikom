<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import {
    Building2,
    CalendarDays,
    ClipboardPenLine,
    LoaderCircle,
    MapPinned,
    CheckCircle2,
    Clock3,
    XCircle,
    RefreshCcw,
    Briefcase,
    Timer,
    ClipboardList,
    IdCard,
    History,
    ChevronDown,
    ChevronUp,
    Circle,
    FileText,
    Download,
    Upload,
} from 'lucide-vue-next';
import StudentLayout from '@/layouts/Modules/Wims/Mahasiswa/Layout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import registrationRoutes from '@/routes/wims/registration';
import { toast } from '@/pages/WorkOs/composables/useWorkOs';

defineOptions({ layout: StudentLayout });

type RegistrationItem = {
    id: number;
    status?: string | null;
    tanggal_mulai?: string | null;
    tanggal_selesai?: string | null;
    tanggal_mulai_label?: string | null;
    tanggal_selesai_label?: string | null;
    application_note?: string | null;
    revision_note?: string | null;
    proposal_attachment?: {
        exists?: boolean;
        name?: string | null;
        uploaded_at?: string | null;
    } | null;
    company?: {
        proposal?: {
            name?: string | null;
            address?: string | null;
        } | null;
        final?: {
            id?: number | null;
            name?: string | null;
        } | null;
    } | null;
    submitted_at?: string | null;
    updated_at?: string | null;
};
type PageState = { can_submit?: boolean; is_revision?: boolean; is_locked?: boolean };
type PeriodOption = {
    id?: number | string | null;
    label?: string | null;
    period_label?: string | null;
    status_label?: string | null;
    is_active?: boolean | null;
};

type TemplateCard = {
    id?: number | string | null;
    title?: string | null;
    description?: string | null;
    original_name?: string | null;
    mime_type?: string | null;
    updated_at?: string | null;
    download_url?: string | null;
} | null;

type FormDefaults = {
    tanggal_mulai?: string | null;
    tanggal_selesai?: string | null;
    perusahaan_diminati_nama?: string | null;
    perusahaan_diminati_alamat?: string | null;
    catatan_pengajuan?: string | null;
};
type PageProps = {
    flash?: { success?: string; error?: string | null };
    errors?: Record<string, string | undefined>;
};

const props = defineProps<{
    registration?: RegistrationItem | null;
    pageState: PageState;
    formDefaults: FormDefaults;
    periods?: PeriodOption[];
    selected_period_id?: number | string | null;
    proposal_template?: TemplateCard;
}>();

const page = usePage<PageProps>();

const form = useForm({
    tanggal_mulai: props.formDefaults.tanggal_mulai ?? '',
    tanggal_selesai: props.formDefaults.tanggal_selesai ?? '',
    perusahaan_diminati_nama: props.formDefaults.perusahaan_diminati_nama ?? '',
    perusahaan_diminati_alamat: props.formDefaults.perusahaan_diminati_alamat ?? '',
    catatan_pengajuan: props.formDefaults.catatan_pengajuan ?? '',
    proposal_pkl: null as File | null,
});

const flash        = computed(() => page.props.flash ?? {});
const pageErrors   = computed(() => page.props.errors ?? {});
const registration = computed(() => props.registration ?? null);
const proposalAttachment = computed(() => registration.value?.proposal_attachment ?? null);
const proposalExistingFlag = computed(() => proposalAttachment.value?.exists ? '1' : '');
const proposalInputRef = ref<HTMLInputElement | null>(null);
const isLocked     = computed(() => Boolean(props.pageState.is_locked));
const isRevision   = computed(() => Boolean(props.pageState.is_revision));
const canSubmit    = computed(() => Boolean(props.pageState.can_submit));
const localSuccess = ref<string | null>(flash.value.success ?? null);
const localError = ref<string | null>(flash.value.error ?? null);
const globalError  = computed(() =>
    pageErrors.value.registration
    || localError.value
);

const proposalCompanyLabel = (registrationItem?: RegistrationItem | null) =>
    registrationItem?.company?.proposal?.name ?? '-';

const finalCompanyLabel = (registrationItem?: RegistrationItem | null) =>
    registrationItem?.company?.final?.name ?? '-';

const companyAddressLabel = (registrationItem?: RegistrationItem | null) =>
    registrationItem?.company?.proposal?.address ?? '-';

// -- UI toggles ----------------------------------------------
const showChecklist  = ref(true);
const showLog        = ref(true);
const showCardPreview = ref(false);

// -- Status helpers -------------------------------------------
const statusLabel = (status?: string | null) => {
    const m: Record<string, string> = {
        approved: 'Approved', aktif: 'Aktif', selesai: 'Selesai',
        revisi: 'Revisi', rejected: 'Rejected', pending: 'Menunggu Review',
    };
    return m[status ?? ''] ?? 'Belum Mengajukan';
};
const statusConfig = (status?: string | null) => {
    const m: Record<string, { badge: string; dot: string; bar: string }> = {
        approved: { badge: 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/15 dark:text-emerald-400', dot: 'bg-emerald-500', bar: 'bg-emerald-500' },
        aktif:    { badge: 'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-500/30 dark:bg-sky-500/15 dark:text-sky-400',             dot: 'bg-sky-500',     bar: 'bg-sky-500' },
        selesai:  { badge: 'border-violet-200 bg-violet-50 text-violet-700 dark:border-violet-500/30 dark:bg-violet-500/15 dark:text-violet-400',    dot: 'bg-violet-500',  bar: 'bg-violet-500' },
        revisi:   { badge: 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/15 dark:text-amber-400',       dot: 'bg-amber-500',   bar: 'bg-amber-500' },
        rejected: { badge: 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/15 dark:text-rose-400',          dot: 'bg-rose-500',    bar: 'bg-rose-500' },
        pending:  { badge: 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/15 dark:text-blue-400',          dot: 'bg-blue-500',    bar: 'bg-blue-500' },
    };
    return m[status ?? ''] ?? { badge: 'border-slate-200 bg-slate-50 text-slate-500 dark:border-slate-600 dark:bg-slate-700/50 dark:text-slate-400', dot: 'bg-slate-400', bar: 'bg-slate-300' };
};

// -- Progress steps -------------------------------------------
const steps = computed(() => {
    const s = registration.value?.status;
    return [
        { key: 'draft',    label: 'Pengajuan',     done: !!s,                                                   active: !s },
        { key: 'pending',  label: 'Review Kampus', done: ['approved','aktif','selesai'].includes(s ?? ''),       active: s === 'pending' || s === 'revisi' },
        { key: 'approved', label: 'Disetujui',     done: ['aktif','selesai'].includes(s ?? ''),                  active: s === 'approved' },
        { key: 'aktif',    label: 'Aktif',         done: s === 'selesai',                                        active: s === 'aktif' },
        { key: 'selesai',  label: 'Selesai', done: s === 'selesai',                                        active: s === 'selesai' },
    ];
});

// -- FITUR 1: Countdown timer --------------------------------
const countdown = computed(() => {
    const start = registration.value?.tanggal_mulai ?? form.tanggal_mulai;
    const end   = registration.value?.tanggal_selesai ?? form.tanggal_selesai;
    if (!start || !end) return null;

    const now      = new Date();
    const startDate = new Date(start);
    const endDate   = new Date(end);
    const totalMs   = endDate.getTime() - startDate.getTime();
    const totalDays = Math.ceil(totalMs / 86400000);

    if (now < startDate) {
        const diffMs   = startDate.getTime() - now.getTime();
        const diffDays = Math.ceil(diffMs / 86400000);
        return { phase: 'belum', diffDays, totalDays, progress: 0 };
    }
    if (now <= endDate) {
        const elapsed  = now.getTime() - startDate.getTime();
        const remaining = endDate.getTime() - now.getTime();
        const diffDays  = Math.ceil(remaining / 86400000);
        const progress  = Math.round((elapsed / totalMs) * 100);
        return { phase: 'berjalan', diffDays, totalDays, progress };
    }
    return { phase: 'selesai', diffDays: 0, totalDays, progress: 100 };
});

// -- FITUR 2: Checklist kelengkapan --------------------------
const checklist = computed(() => [
    {
        id: 'proposal',
        label: 'Proposal PKL dilampirkan',
        done: Boolean(form.proposal_pkl || proposalExistingFlag.value),
        required: true,
    },
    {
        id: 'tanggal',
        label: 'Tanggal mulai & selesai diisi',
        done: Boolean(form.tanggal_mulai && form.tanggal_selesai),
        required: true,
    },
    {
        id: 'perusahaan',
        label: 'Nama perusahaan diisi',
        done: Boolean(form.perusahaan_diminati_nama),
        required: false,
    },
    {
        id: 'alamat',
        label: 'Alamat / kota perusahaan diisi',
        done: Boolean(form.perusahaan_diminati_alamat),
        required: false,
    },
    {
        id: 'catatan',
        label: 'Catatan pengajuan diisi',
        done: Boolean(form.catatan_pengajuan),
        required: false,
    },
]);
const checklistScore = computed(() => checklist.value.filter(c => c.done).length);
const checklistTotal = computed(() => checklist.value.length);

// -- FITUR 4: Activity log ------------------------------------
// Disusun dari data yang sudah ada di registration, murni computed
const activityLog = computed(() => {
    const logs: { icon: string; color: string; text: string; time: string }[] = [];
    const r = registration.value;
    if (!r) return logs;

    if (r.submitted_at) {
        logs.push({ icon: 'submit', color: 'blue',   text: 'Pendaftaran diajukan ke kampus', time: r.submitted_at });
    }
    if (r.status === 'revisi' && r.revision_note) {
        logs.push({ icon: 'revisi', color: 'amber',  text: 'Kampus meminta revisi data',     time: r.updated_at ?? r.submitted_at ?? '-' });
    }
    if (r.status === 'approved' || r.status === 'aktif' || r.status === 'selesai') {
        logs.push({ icon: 'approved', color: 'emerald', text: 'Pendaftaran disetujui kampus', time: r.updated_at ?? r.submitted_at ?? '-' });
    }
    if (r.status === 'aktif') {
        logs.push({ icon: 'aktif', color: 'sky', text: 'PKL/Magang sedang berjalan',         time: r.tanggal_mulai_label ?? '-' });
    }
    if (r.status === 'selesai') {
        logs.push({ icon: 'selesai', color: 'violet', text: 'PKL/Magang selesai',            time: r.tanggal_selesai_label ?? '-' });
    }
    if (r.status === 'rejected') {
        logs.push({ icon: 'rejected', color: 'rose', text: 'Pendaftaran ditolak kampus',     time: r.updated_at ?? r.submitted_at ?? '-' });
    }
    return logs.reverse();
});

// -- Form -----------------------------------------------------
const actionLabel = computed(() =>
    isRevision.value ? 'Kirim Ulang Pendaftaran' : 'Ajukan Pendaftaran',
);
const submitDisabled = computed(
    () => !canSubmit.value || !form.tanggal_mulai || !form.tanggal_selesai || (!form.proposal_pkl && !proposalExistingFlag.value) || form.processing,
);
const handleProposalFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement | null;
    form.proposal_pkl = target?.files?.[0] ?? null;
};

const clearProposalFile = () => {
    form.proposal_pkl = null;
    if (proposalInputRef.value) {
        proposalInputRef.value.value = '';
    }
};
const submit = () => {
    if (submitDisabled.value) return;
    localError.value = null;
    form.transform((data) => ({
        ...data,
        proposal_pkl_existing: proposalExistingFlag.value,
    }));
    form.post(registrationRoutes.store.url(), {
        preserveScroll: true,
        onSuccess: (pageResponse) => {
            const message = pageResponse.props.flash?.success
                ?? (isRevision.value
                    ? 'Perbaikan pendaftaran berhasil dikirim ulang dan menunggu review kampus.'
                    : 'Pendaftaran PKL/magang berhasil dikirim dan menunggu review kampus.');

            form.proposal_pkl = null;
            if (proposalInputRef.value) {
                proposalInputRef.value.value = '';
            }
            localSuccess.value = message;
        },
        onError: (errors) => {
            const message = page.props.flash?.error
                ?? page.props.errors?.registration
                ?? errors.tanggal_mulai
                ?? errors.tanggal_selesai
                ?? errors.perusahaan_diminati_nama
                ?? errors.perusahaan_diminati_alamat
                ?? errors.catatan_pengajuan
                ?? errors.proposal_pkl
                ?? 'Pendaftaran gagal dikirim. Periksa kembali data yang diisi.';

            localSuccess.value = null;
            localError.value = message;
            toast(message, 'error');
        },
    });
};

watch(
    () => flash.value.success,
    (message) => {
        localSuccess.value = message ?? null;
        if (message) {
            localError.value = null;
            toast(message, 'success');
        }
    },
    { immediate: true },
);

watch(
    () => flash.value.error,
    (message) => {
        localError.value = message ?? null;
        if (message) {
            localSuccess.value = null;
            toast(message, 'error');
        }
    },
    { immediate: true },
);
</script>

<template>
    <Head title="Pendaftaran PKL/Magang" />

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
                                Pendaftaran PKL / Magang
                            </h1>
                            <p class="mt-2 text-[13px] leading-relaxed text-white/78 dark:text-white/70 sm:text-sm">
                                Ajukan periode PKL/magang Anda. Perusahaan diminati bersifat opsional - keputusan final ditentukan oleh kampus.
                            </p>
                        </div>

                        <!-- Status Badge on Hero -->
                        <div v-if="registration" class="lg:min-w-[160px]">
                            <div class="rounded-xl bg-white/10 px-4 py-3 ring-1 ring-white/15 backdrop-blur-md dark:bg-white/[0.07] dark:ring-white/10">
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-white/60">Status saat ini</p>
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="inline-flex size-2 animate-pulse rounded-full" :class="statusConfig(registration.status).dot" />
                                    <span class="text-[13px] font-bold text-white">{{ statusLabel(registration.status) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
<!-- Progress Steps -->
            <div class="rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-wims-border/50 shadow-[0_1px_3px_rgba(0,0,0,0.04)] px-5 py-4 sm:px-6">
                <p class="mb-4 text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Alur Pendaftaran</p>
                <div class="flex items-center">
                    <template v-for="(step, i) in steps" :key="step.key">
                        <div class="flex min-w-0 flex-1 flex-col items-center gap-1.5">
                            <div
                                class="flex size-7 items-center justify-center rounded-full text-[11px] font-bold transition-all duration-300"
                                :class="step.done ? 'bg-blue-600 text-white shadow-md shadow-blue-500/30' : step.active ? 'border-2 border-blue-500 bg-wims-card text-blue-600 shadow-[0_0_0_1px_rgba(59,130,246,0.12)] dark:text-blue-400' : 'border border-slate-200 bg-white text-slate-400 shadow-[inset_0_1px_0_rgba(255,255,255,0.5)] dark:border-slate-600/80 dark:bg-slate-800/70 dark:text-slate-400 dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.03)]'"
                            >
                                <CheckCircle2 v-if="step.done" class="size-4" />
                                <span v-else>{{ i + 1 }}</span>
                            </div>
                            <span class="text-center text-[10px] font-semibold leading-tight" :class="step.done ? 'text-blue-600 dark:text-blue-400' : step.active ? 'text-wims-text' : 'text-slate-400 dark:text-slate-500'">
                                {{ step.label }}
                            </span>
                        </div>
                        <div v-if="i < steps.length - 1" class="mb-4 h-px flex-1 rounded-full transition-colors duration-500" :class="step.done ? 'bg-blue-500 dark:bg-blue-400' : 'bg-slate-200 dark:bg-slate-600/70'" />
                    </template>
                </div>
            </div>

            <!-- Flash Alerts -->
            <div v-if="localSuccess" class="flex items-start gap-3 rounded-xl border border-emerald-200/60 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-500/10 px-4 py-3">
                <CheckCircle2 class="mt-0.5 size-4 shrink-0 text-emerald-500 dark:text-emerald-400" />
                <div>
                    <p class="text-sm font-bold text-emerald-800 dark:text-emerald-300">Berhasil</p>
                    <p class="mt-0.5 text-xs leading-relaxed text-emerald-700 dark:text-emerald-400">{{ localSuccess }}</p>
                </div>
            </div>
            <div v-if="globalError" class="flex items-start gap-3 rounded-xl border border-rose-200/60 bg-rose-50 dark:border-rose-500/30 dark:bg-rose-500/10 px-4 py-3">
                <XCircle class="mt-0.5 size-4 shrink-0 text-rose-500 dark:text-rose-400" />
                <div>
                    <p class="text-sm font-bold text-rose-800 dark:text-rose-300">Perlu diperbaiki</p>
                    <p class="mt-0.5 text-xs leading-relaxed text-rose-700 dark:text-rose-400">{{ globalError }}</p>
                </div>
            </div>

            <!-- 2-Column -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-[280px_1fr] lg:gap-5">

                <!-- Sidebar -->
                <div class="space-y-4">
                    <!-- Status Card -->
                    <div class="relative overflow-hidden rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-wims-border/50 shadow-[0_1px_3px_rgba(0,0,0,0.04)]">
                        <div class="absolute top-0 inset-x-0 h-[3px]" :class="statusConfig(registration?.status).bar" />
                        <div class="p-5">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Status Pendaftaran</p>
                            <div class="mt-3 flex items-center gap-2.5">
                                <span class="inline-flex size-2 animate-pulse rounded-full" :class="statusConfig(registration?.status).dot" />
                                <Badge variant="outline" class="rounded-full px-2.5 py-0.5 text-xs font-bold" :class="statusConfig(registration?.status).badge">
                                    {{ statusLabel(registration?.status) }}
                                </Badge>
                            </div>
                            <div v-if="registration?.submitted_at" class="mt-3 border-t border-wims-border/50 pt-3">
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Diajukan pada</p>
                                <p class="mt-1 text-xs font-bold text-wims-text">{{ registration.submitted_at }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Countdown Timer -->
                    <div class="relative overflow-hidden rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-wims-border/50 shadow-[0_1px_3px_rgba(0,0,0,0.04)]">
                        <div class="absolute top-0 inset-x-0 h-[3px] bg-gradient-to-r from-blue-500 to-cyan-400" />
                        <div class="p-5">
                            <div class="mb-3 flex items-center gap-2.5">
                                <div class="flex size-8 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-500/15">
                                    <Timer class="size-4 text-blue-600 dark:text-blue-400" />
                                </div>
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Countdown PKL</p>
                            </div>
                            <template v-if="countdown">
                                <template v-if="countdown.phase === 'berjalan'">
                                    <div class="text-center">
                                        <p class="text-3xl font-bold tabular-nums text-blue-600 dark:text-blue-400">{{ countdown.diffDays }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">hari tersisa</p>
                                    </div>
                                    <div class="mt-3">
                                        <div class="mb-1 flex justify-between text-[10px] text-slate-400 dark:text-slate-500">
                                            <span>Progress</span>
                                            <span>{{ countdown.progress }}%</span>
                                        </div>
                                        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700/50">
                                            <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-cyan-400 transition-all duration-700" :style="{ width: countdown.progress + '%' }" />
                                        </div>
                                        <p class="mt-1.5 text-center text-[10px] text-slate-400 dark:text-slate-500">dari total {{ countdown.totalDays }} hari</p>
                                    </div>
                                </template>
                                <template v-else-if="countdown.phase === 'belum'">
                                    <div class="text-center">
                                        <p class="text-3xl font-bold tabular-nums text-amber-500 dark:text-amber-400">{{ countdown.diffDays }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">hari lagi dimulai</p>
                                    </div>
                                    <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700/50">
                                        <div class="h-full w-0 rounded-full bg-amber-400" />
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="flex flex-col items-center gap-1.5 py-1">
                                        <CheckCircle2 class="size-8 text-emerald-500 dark:text-emerald-400" />
                                        <p class="text-xs font-bold text-emerald-700 dark:text-emerald-300">Periode PKL Berakhir</p>
                                        <p class="text-[11px] text-slate-400 dark:text-slate-500">Total {{ countdown.totalDays }} hari</p>
                                    </div>
                                </template>
                            </template>
                            <template v-else>
                                <p class="text-center text-xs text-slate-400 dark:text-slate-500">Isi tanggal mulai & selesai untuk melihat countdown</p>
                            </template>
                        </div>
                    </div>

                    <!-- Perusahaan Final -->
                    <div class="rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-wims-border/50 shadow-[0_1px_3px_rgba(0,0,0,0.04)] p-5">
                        <div class="mb-2.5 flex items-center gap-2.5">
                            <div class="flex size-8 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-500/15">
                                <Briefcase class="size-4 text-blue-600 dark:text-blue-400" />
                            </div>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Perusahaan Final</p>
                        </div>
                        <p class="text-sm font-bold leading-snug text-wims-text">{{ registration?.company?.final?.name || '-' }}</p>
                        <p v-if="!registration?.company?.final?.name" class="mt-0.5 text-[11px] text-slate-400 dark:text-slate-500">Menunggu keputusan kampus</p>
                    </div>

                    <!-- Periode -->
                    <div class="rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-wims-border/50 shadow-[0_1px_3px_rgba(0,0,0,0.04)] p-5">
                        <div class="mb-3 flex items-center gap-2.5">
                            <div class="flex size-8 items-center justify-center rounded-lg bg-cyan-50 dark:bg-cyan-500/15">
                                <CalendarDays class="size-4 text-cyan-600 dark:text-cyan-400" />
                            </div>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Periode Diajukan</p>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between rounded-lg bg-slate-50/80 dark:bg-slate-800/40 px-3 py-2">
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Mulai</span>
                                <span class="text-xs font-bold text-wims-text">{{ registration?.tanggal_mulai_label || '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between rounded-lg bg-slate-50/80 dark:bg-slate-800/40 px-3 py-2">
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Selesai</span>
                                <span class="text-xs font-bold text-wims-text">{{ registration?.tanggal_selesai_label || '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Usulan -->
                    <div class="rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-wims-border/50 shadow-[0_1px_3px_rgba(0,0,0,0.04)] p-5">
                        <div class="mb-2.5 flex items-center gap-2.5">
                            <div class="flex size-8 items-center justify-center rounded-lg bg-violet-50 dark:bg-violet-500/15">
                                <Building2 class="size-4 text-violet-600 dark:text-violet-400" />
                            </div>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Usulan Mahasiswa</p>
                        </div>
                        <p class="text-xs font-bold text-wims-text">{{ registration?.company?.proposal?.name || 'Belum mengusulkan perusahaan' }}</p>
                        <p class="mt-1 text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">{{ registration?.company?.proposal?.address || 'Kampus akan menentukan perusahaan mitra yang sesuai.' }}</p>
                    </div>

                    <!-- Revision Note -->
                    <div v-if="registration?.revision_note" class="relative overflow-hidden rounded-2xl border border-amber-200/60 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-500/10 shadow-[0_1px_3px_rgba(0,0,0,0.04)]">
                        <div class="absolute top-0 inset-x-0 h-[3px] bg-amber-400" />
                        <div class="p-5">
                            <div class="mb-2.5 flex items-center gap-2.5">
                                <div class="flex size-8 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-500/20">
                                    <RefreshCcw class="size-4 text-amber-600 dark:text-amber-400" />
                                </div>
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">Catatan Revisi</p>
                            </div>
                            <p class="text-xs leading-relaxed text-amber-900 dark:text-amber-200">{{ registration.revision_note }}</p>
                        </div>
                    </div>
                    <!-- Activity Log -->
                    <div v-if="activityLog.length" class="overflow-hidden rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-wims-border/50 shadow-[0_1px_3px_rgba(0,0,0,0.04)]">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between px-5 py-3.5 transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/30"
                            @click="showLog = !showLog"
                        >
                            <div class="flex items-center gap-2.5">
                                <div class="flex size-8 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-500/15">
                                    <History class="size-4 text-blue-600 dark:text-blue-400" />
                                </div>
                                <span class="text-sm font-bold text-wims-text">Riwayat Aktivitas</span>
                                <span class="rounded-full bg-slate-100 dark:bg-slate-700/50 px-2 py-0.5 text-[11px] font-bold text-slate-600 dark:text-slate-400">
                                    {{ activityLog.length }}
                                </span>
                            </div>
                            <ChevronUp v-if="showLog" class="size-4 text-slate-400 dark:text-slate-500" />
                            <ChevronDown v-else class="size-4 text-slate-400 dark:text-slate-500" />
                        </button>
                        <div v-if="showLog" class="border-t border-wims-border/50 px-5 py-4">
                            <div class="relative space-y-0">
                                <div class="absolute top-2 bottom-2 left-[9px] w-px bg-wims-border/40" />
                                <div v-for="(log, i) in activityLog" :key="i" class="relative flex items-start gap-3 pb-4 last:pb-0">
                                    <div
                                        class="relative z-10 mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full"
                                        :class="{
                                            'bg-blue-100 dark:bg-blue-500/20': log.color === 'blue',
                                            'bg-emerald-100 dark:bg-emerald-500/20': log.color === 'emerald',
                                            'bg-amber-100 dark:bg-amber-500/20': log.color === 'amber',
                                            'bg-sky-100 dark:bg-sky-500/20': log.color === 'sky',
                                            'bg-violet-100 dark:bg-violet-500/20': log.color === 'violet',
                                            'bg-rose-100 dark:bg-rose-500/20': log.color === 'rose',
                                        }"
                                    >
                                        <div
                                            class="size-2 rounded-full"
                                            :class="{
                                                'bg-blue-500': log.color === 'blue',
                                                'bg-emerald-500': log.color === 'emerald',
                                                'bg-amber-500': log.color === 'amber',
                                                'bg-sky-500': log.color === 'sky',
                                                'bg-violet-500': log.color === 'violet',
                                                'bg-rose-500': log.color === 'rose',
                                            }"
                                        />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-semibold text-wims-text">{{ log.text }}</p>
                                        <p class="mt-0.5 text-[11px] text-slate-400 dark:text-slate-500">{{ log.time }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Column -->
                <div class="space-y-4">
                    <div v-if="props.proposal_template?.download_url" class="overflow-hidden rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-blue-200/40 dark:border-blue-500/20 shadow-[0_1px_3px_rgba(0,0,0,0.04)]">
                        <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div class="flex min-w-0 items-start gap-3">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400">
                                    <FileText class="size-5" />
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-wims-text">
                                        Template Proposal PKL
                                    </p>
                                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                        Sudah punya template proposal?
                                    </p>
                                </div>
                            </div>
                            <a
                                :href="props.proposal_template.download_url"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-blue-200/60 bg-wims-card px-4 py-2.5 text-sm font-semibold text-blue-600 dark:text-blue-400 dark:border-blue-500/30 transition hover:bg-blue-50 dark:hover:bg-blue-500/10 sm:w-auto"
                            >
                                <Download class="size-4" />
                                Download
                            </a>
                        </div>
                    </div>
                    <!-- Checklist -->
                    <div class="overflow-hidden rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-wims-border/50 shadow-[0_1px_3px_rgba(0,0,0,0.04)]">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between px-5 py-3.5 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors"
                            @click="showChecklist = !showChecklist"
                        >
                            <div class="flex items-center gap-2.5">
                                <div class="flex size-8 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-500/15">
                                    <ClipboardList class="size-4 text-blue-600 dark:text-blue-400" />
                                </div>
                                <span class="text-sm font-bold text-wims-text">Kelengkapan Form</span>
                                <span class="rounded-full px-2 py-0.5 text-[11px] font-bold" :class="checklistScore === checklistTotal ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400' : 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400'">
                                    {{ checklistScore }}/{{ checklistTotal }}
                                </span>
                            </div>
                            <ChevronUp v-if="showChecklist" class="size-4 text-slate-400 dark:text-slate-500" />
                            <ChevronDown v-else class="size-4 text-slate-400 dark:text-slate-500" />
                        </button>
                        <div v-if="showChecklist" class="border-t border-wims-border/50 px-5 py-3">
                            <div class="mb-3 h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700/50">
                                <div class="h-full rounded-full transition-all duration-700" :class="checklistScore === checklistTotal ? 'bg-emerald-500' : 'bg-blue-500'" :style="{ width: (checklistScore / checklistTotal * 100) + '%' }" />
                            </div>
                            <div class="space-y-2">
                                <div v-for="item in checklist" :key="item.id" class="flex items-center gap-3">
                                    <div class="flex size-5 shrink-0 items-center justify-center rounded-full transition-colors duration-300" :class="item.done ? 'bg-emerald-500' : 'border border-wims-border/60 bg-wims-card'">
                                        <svg v-if="item.done" class="size-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="text-xs leading-snug" :class="item.done ? 'text-wims-text font-medium' : 'text-slate-500 dark:text-slate-400'">
                                        {{ item.label }}
                                    </span>
                                    <span v-if="item.required && !item.done" class="ml-auto shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold text-rose-600 bg-rose-50 dark:bg-rose-500/15 dark:text-rose-400">Wajib</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Form Card -->
                    <div class="overflow-hidden rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-wims-border/50 shadow-[0_1px_3px_rgba(0,0,0,0.04)]">
                        <div class="flex items-center justify-between border-b border-wims-border/50 px-5 py-4 sm:px-6">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Form Pengajuan</p>
                                <h2 class="mt-0.5 text-[15px] font-bold text-wims-text">Data Pendaftaran</h2>
                            </div>
                            <Badge v-if="registration" variant="outline" class="rounded-full px-2.5 py-0.5 text-xs font-bold" :class="statusConfig(registration.status).badge">
                                {{ statusLabel(registration.status) }}
                            </Badge>
                        </div>

                        <form class="space-y-5 px-5 py-5 sm:px-6" @submit.prevent="submit">
                            <div v-if="isLocked" class="flex items-start gap-3 rounded-xl border border-blue-200/60 bg-blue-50 px-4 py-3 dark:border-blue-500/30 dark:bg-blue-500/10">
                                <Clock3 class="mt-0.5 size-4 shrink-0 text-blue-500 dark:text-blue-400" />
                                <p class="text-xs leading-relaxed text-blue-700 dark:text-blue-300">Pendaftaran sedang menunggu keputusan kampus atau sudah aktif. Form dikunci sampai status berubah atau ada permintaan revisi.</p>
                            </div>
                            <div v-else-if="isRevision" class="flex items-start gap-3 rounded-xl border border-amber-200/60 bg-amber-50 px-4 py-3 dark:border-amber-500/30 dark:bg-amber-500/10">
                                <RefreshCcw class="mt-0.5 size-4 shrink-0 text-amber-500 dark:text-amber-400" />
                                <p class="text-xs leading-relaxed text-amber-800 dark:text-amber-300">Pendaftaran dikembalikan untuk revisi. Perbarui data di bawah lalu kirim ulang ke kampus.</p>
                            </div>
                            <div v-if="registration?.revision_note" class="rounded-xl border border-amber-200/60 bg-amber-50/50 px-4 py-3 dark:border-amber-500/20 dark:bg-amber-500/5">
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">Catatan Revisi Kampus</p>
                                <p class="mt-2 text-xs leading-relaxed text-slate-700 dark:text-slate-300">{{ registration.revision_note }}</p>
                            </div>

                            <div class="flex items-center gap-2">
                                <div class="flex size-6 items-center justify-center rounded-md bg-blue-50 dark:bg-blue-500/15">
                                    <CalendarDays class="size-3.5 text-blue-600 dark:text-blue-400" />
                                </div>
                                <span class="text-xs font-bold text-wims-text">Periode PKL / Magang</span>
                                <div class="flex-1 border-t border-wims-border/40" />
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-1.5">
                                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300">Tanggal Mulai <span class="text-rose-400">*</span></label>
                                    <div class="relative">
                                        <CalendarDays class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
                                        <input v-model="form.tanggal_mulai" type="date" :disabled="isLocked" class="student-date-input h-10 w-full rounded-xl border border-wims-border/60 bg-wims-card pr-3 pl-10 text-sm text-wims-text outline-none transition-colors focus:border-blue-400 focus:ring-2 focus:ring-blue-400/20 dark:focus:ring-blue-400/10 disabled:cursor-not-allowed disabled:opacity-50" />
                                    </div>
                                    <p v-if="form.errors.tanggal_mulai" class="text-xs text-rose-500 dark:text-rose-400">{{ form.errors.tanggal_mulai }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300">Tanggal Selesai <span class="text-rose-400">*</span></label>
                                    <div class="relative">
                                        <CalendarDays class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
                                        <input v-model="form.tanggal_selesai" type="date" :disabled="isLocked" class="student-date-input h-10 w-full rounded-xl border border-wims-border/60 bg-wims-card pr-3 pl-10 text-sm text-wims-text outline-none transition-colors focus:border-blue-400 focus:ring-2 focus:ring-blue-400/20 dark:focus:ring-blue-400/10 disabled:cursor-not-allowed disabled:opacity-50" />
                                    </div>
                                    <p v-if="form.errors.tanggal_selesai" class="text-xs text-rose-500 dark:text-rose-400">{{ form.errors.tanggal_selesai }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <div class="flex size-6 items-center justify-center rounded-md bg-violet-50 dark:bg-violet-500/15">
                                    <Building2 class="size-3.5 text-violet-600 dark:text-violet-400" />
                                </div>
                                <span class="text-xs font-bold text-wims-text">Preferensi Perusahaan</span>
                                <div class="flex-1 border-t border-wims-border/40" />
                                <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-500">Opsional</span>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300">Nama Perusahaan</label>
                                <div class="relative">
                                    <Building2 class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
                                    <input v-model="form.perusahaan_diminati_nama" type="text" :disabled="isLocked" placeholder="Isi jika sudah memiliki usulan perusahaan" class="h-10 w-full rounded-xl border border-wims-border/60 bg-wims-card pr-3 pl-10 text-sm text-wims-text placeholder:text-slate-400 dark:placeholder:text-slate-500 outline-none transition-colors focus:border-blue-400 focus:ring-2 focus:ring-blue-400/20 dark:focus:ring-blue-400/10 disabled:cursor-not-allowed disabled:opacity-50" />
                                </div>
                                <p v-if="form.errors.perusahaan_diminati_nama" class="text-xs text-rose-500 dark:text-rose-400">{{ form.errors.perusahaan_diminati_nama }}</p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300">Alamat / Kota</label>
                                <div class="relative">
                                    <MapPinned class="pointer-events-none absolute top-3 left-3 size-4 text-slate-400 dark:text-slate-500" />
                                    <textarea v-model="form.perusahaan_diminati_alamat" :disabled="isLocked" rows="2" placeholder="Tambahkan alamat singkat atau kota perusahaan jika diperlukan" class="w-full rounded-xl border border-wims-border/60 bg-wims-card py-2.5 pr-3 pl-10 text-sm leading-relaxed text-wims-text placeholder:text-slate-400 dark:placeholder:text-slate-500 outline-none transition-colors focus:border-blue-400 focus:ring-2 focus:ring-blue-400/20 dark:focus:ring-blue-400/10 disabled:cursor-not-allowed disabled:opacity-50" />
                                </div>
                                <p v-if="form.errors.perusahaan_diminati_alamat" class="text-xs text-rose-500 dark:text-rose-400">{{ form.errors.perusahaan_diminati_alamat }}</p>
                            </div>

                            <div class="flex items-center gap-2">
                                <div class="flex size-6 items-center justify-center rounded-md bg-cyan-50 dark:bg-cyan-500/15">
                                    <ClipboardPenLine class="size-3.5 text-cyan-600 dark:text-cyan-400" />
                                </div>
                                <span class="text-xs font-bold text-wims-text">Catatan Tambahan</span>
                                <div class="flex-1 border-t border-wims-border/40" />
                                <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-500">Opsional</span>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300">Catatan untuk Kampus</label>
                                <textarea v-model="form.catatan_pengajuan" :disabled="isLocked" rows="3" placeholder="Contoh: alasan memilih perusahaan, kebutuhan bidang, atau catatan tambahan untuk kampus" class="w-full rounded-xl border border-wims-border/60 bg-wims-card px-4 py-2.5 text-sm leading-relaxed text-wims-text placeholder:text-slate-400 dark:placeholder:text-slate-500 outline-none transition-colors focus:border-blue-400 focus:ring-2 focus:ring-blue-400/20 dark:focus:ring-blue-400/10 disabled:cursor-not-allowed disabled:opacity-50" />
                                <p v-if="form.errors.catatan_pengajuan" class="text-xs text-rose-500 dark:text-rose-400">{{ form.errors.catatan_pengajuan }}</p>
                            </div>

                            <div class="rounded-xl border border-wims-border/50 bg-slate-50/80 px-4 py-4 dark:bg-slate-800/30">
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Dokumen Proposal PKL</p>

                                <div
                                    v-if="form.proposal_pkl || proposalAttachment?.name"
                                    class="mt-3 rounded-xl border border-wims-border/50 bg-wims-card px-4 py-3.5"
                                >
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                        <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
                                            <FileText class="size-4" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="break-all text-sm font-bold leading-5 text-wims-text">
                                                {{ form.proposal_pkl?.name || proposalAttachment?.name || 'Dokumen tersimpan' }}
                                            </p>
                                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                                {{ proposalAttachment?.uploaded_at || 'Waktu upload belum tersedia' }}
                                            </p>
                                        </div>
                                        <div class="flex w-full flex-col gap-2 sm:ml-auto sm:w-auto sm:flex-row sm:items-center">
                                            <Button type="button" variant="outline" class="h-9 rounded-xl border-blue-200 bg-white px-3.5 text-xs font-bold text-blue-600 shadow-sm transition-colors hover:bg-blue-50 dark:border-blue-500/30 dark:bg-slate-900/30 dark:text-blue-300 dark:hover:bg-blue-500/10" @click="proposalInputRef?.click()">
                                                <Upload class="mr-2 size-4" />
                                                {{ form.proposal_pkl ? 'Ganti File' : 'Pilih File' }}
                                            </Button>
                                            <Button v-if="form.proposal_pkl || proposalAttachment?.name" type="button" variant="ghost" class="h-9 rounded-xl px-3.5 text-xs font-semibold text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200" @click="clearProposalFile">
                                                Hapus File
                                            </Button>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="mt-3 rounded-xl border-2 border-dashed transition-all duration-200"
                                    :class="isLocked ? 'border-slate-200 bg-slate-100/60 dark:border-slate-700 dark:bg-slate-800/30' : 'border-blue-200/70 bg-white/70 hover:border-blue-300/70 dark:border-blue-500/25 dark:bg-slate-900/20'"
                                >
                                    <label class="flex cursor-pointer flex-col items-center gap-2 px-4 py-6 text-center" :class="isLocked ? 'cursor-not-allowed opacity-60' : ''">
                                        <div class="flex size-11 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-500/15">
                                            <Upload class="size-5 text-blue-600 dark:text-blue-400" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-wims-text">
                                                Klik untuk pilih file atau drop file ke sini
                                            </p>
                                            <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">PDF, DOC, DOCX maksimal 5 MB</p>
                                        </div>
                                        <input ref="proposalInputRef" type="file" accept=".pdf,.doc,.docx" class="hidden" :disabled="isLocked" @change="handleProposalFileChange" />
                                    </label>
                                </div>

                                <p v-if="form.errors.proposal_pkl" class="mt-2 text-xs text-rose-500 dark:text-rose-400">{{ form.errors.proposal_pkl }}</p>
                            </div>

                            <div class="pt-1">
                                <Button type="submit" class="h-11 w-full rounded-xl bg-gradient-to-r from-blue-600 to-blue-500 text-sm font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:shadow-blue-500/30 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50 disabled:shadow-none disabled:from-slate-300 disabled:to-slate-300 disabled:text-slate-500 dark:disabled:from-slate-700 dark:disabled:to-slate-700 dark:disabled:text-slate-400" :disabled="submitDisabled">
                                    <LoaderCircle v-if="form.processing" class="mr-2 size-4 animate-spin" />
                                    <span>{{ actionLabel }}</span>
                                </Button>
                                <p v-if="!canSubmit && !isLocked" class="mt-2 text-center text-[11px] text-slate-400 dark:text-slate-500">Lengkapi tanggal mulai dan selesai untuk mengajukan</p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

