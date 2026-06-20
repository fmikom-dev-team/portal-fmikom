<script setup lang="ts">
// resources/js/pages/Modules/Fast/Mahasiswa/Dashboard.vue
import FastLayout from '@/layouts/Modules/Fast/FastLayout.vue';
import DocumentPreviewModal from '@/components/DocumentPreviewModal.vue';
import LetterFlowCard from '@/components/Modules/Fast/LetterFlowCard.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';
import {
    FileText,
    CheckCircle2,
    XCircle,
    Plus,
    Download,
    Eye,
    AlertCircle,
    X,
    ZoomIn,
    ZoomOut,
    RotateCcw as ResetZoom,
    ExternalLink,
    RefreshCw,
    Info,
    FilePlus2,
    History,
    UploadCloud,
    Paperclip,
    Search,
    Calendar,
} from 'lucide-vue-next';
import {
    approvalRoleLabel,
    isProgressStepActive,
    isProgressStepFilled,
    getProgressPercent,
    getProgressStepIndex,
    progressSteps,
} from '@/lib/fastProgress';

type Summary = {
    total: number;
    diproses: number;
    selesai: number;
    ditolak: number;
    dibatalkan: number;
};
type NoteItem = {
    catatan: string;
    oleh?: string | null;
    created_at?: string | null;
};
type LatestSubmission = {
    id: number;
    reference: string;
    jenisSurat: string;
    jenisSuratId?: number | null;
    approvalRole?: {
        id?: number | null;
        nama?: string | null;
        slug?: string | null;
    } | null;
    approval_role_slug?: string | null;
    requiresFinalApproval?: boolean;
    status: string;
    keperluan: string;
    hasPdf: boolean;
    rejectionReason?: string | null;
    revisionReason?: string | null;
    rejectedByRole?: string | null;
    needsRevision?: boolean;
    revisionCount?: number;
    notes?: NoteItem[];
    timeline?: {
        action: string;
        label: string;
        description?: string | null;
        created_at?: string | null;
    }[];
    submittedAt?: string | null;
    neededAt?: string | null;
};
type FieldOption = { label: string; value: string };
type FieldConfig = {
    name: string;
    label: string;
    type: string;
    required: boolean;
    placeholder: string;
    options: FieldOption[];
    help?: string;
    repeatable?: boolean;
    add_label?: string;
    item_label?: string;
    sumber_data?: 'data_pemohon' | 'data_kampus' | 'data_sistem';
    editable_role?: 'mahasiswa' | 'admin' | 'sistem';
    mode_form_pemohon?: 'editable' | 'readonly' | 'hidden';
};
type JenisSuratOption = {
    id: number;
    categoryId?: number | null;
    nama: string;
    slug?: string | null;
    deskripsi?: string | null;
    fieldConfig?: FieldConfig[];
};
type SuratCategoryOption = {
    id: number;
    nama: string;
    slug: string;
    deskripsi?: string | null;
};
type PageProps = {
    auth: { user: { name?: string } };
    flash?: { success?: string };
};

const props = defineProps<{
    summary: Summary;
    latest: LatestSubmission[];
    categories: SuratCategoryOption[];
    jenisSurats: JenisSuratOption[];
    userType: {
        value?: string | null;
        label?: string | null;
    };
    userProfile: {
        name?: string | null;
        identifierLabel?: string | null;
        identifierValue?: string | null;
    };
    endpoints: { submission: string; jenisSuratBase: string; basePath: string };
}>();

const page = usePage<PageProps>();
const safeSummary = computed<Summary>(() => ({
    total: props.summary?.total ?? 0,
    diproses: props.summary?.diproses ?? 0,
    selesai: props.summary?.selesai ?? 0,
    ditolak: props.summary?.ditolak ?? 0,
    dibatalkan: props.summary?.dibatalkan ?? 0,
}));
const summary = safeSummary;
const safeLatest = computed<LatestSubmission[]>(() => props.latest ?? []);
const safeJenisSurats = computed<JenisSuratOption[]>(() => props.jenisSurats ?? []);
const safeEndpoints = computed(
    () =>
        props.endpoints ?? {
            submission: '',
            jenisSuratBase: '/jenis-surat',
            basePath: '/mahasiswa',
        },
);
const showFormModal = ref(false);
const selectedJenis = ref<JenisSuratOption | null>(null);
const formStep = ref<'form' | 'preview'>('form');
const toastMessage = ref('');
const expandedReasonId = ref<number | null>(null);
const expandedNotesId = ref<number | null>(null);
let toastTimeoutId: number | null = null;
const applicantFieldRefs = ref<Record<string, HTMLElement | null>>({});

const visibleJenis = computed(() => safeJenisSurats.value.slice(0, 5));
const hasMoreJenis = computed(
    () => safeJenisSurats.value.length > visibleJenis.value.length,
);
const latestVisible = computed(() => safeLatest.value.slice(0, 5));

type FieldValue = string | boolean | string[] | number | null;

const submitForm = useForm<{
    jenis_surat_id: string;
    keperluan: string;
    tanggal_kebutuhan: string;
    field_data: Record<string, FieldValue>;
    lampiran: File[];
}>({
    jenis_surat_id: '',
    keperluan: '',
    tanggal_kebutuhan: '',
    field_data: {},
    lampiran: [],
});

function initFormData(jenis: JenisSuratOption) {
    submitForm.jenis_surat_id = String(jenis.id);
    submitForm.keperluan = '';
    submitForm.tanggal_kebutuhan = '';
    submitForm.lampiran = [];
    const fieldValues: Record<string, FieldValue> = {};
    for (const f of jenis.fieldConfig ?? []) {
        if (f.type === 'checkbox') fieldValues[f.name] = false;
        else if (['checkbox-group', 'multiselect'].includes(f.type))
            fieldValues[f.name] = [];
        else fieldValues[f.name] = '';
    }
    submitForm.field_data = fieldValues;
}
function isApplicantFieldVisible(field: FieldConfig) {
    return (field.mode_form_pemohon ?? 'editable') !== 'hidden';
}
function isApplicantFieldReadonly(field: FieldConfig) {
    return (field.mode_form_pemohon ?? 'editable') === 'readonly';
}

function setApplicantFieldRef(name: string, el: any) {
    applicantFieldRefs.value[name] = el instanceof HTMLElement ? el : null;
}

function isFieldMissing(field: FieldConfig, value: FieldValue): boolean {
    if (field.type === 'checkbox') {
        return value !== true;
    }

    if (['checkbox-group', 'multiselect'].includes(field.type)) {
        return !Array.isArray(value) || value.length === 0;
    }

    if (Array.isArray(value)) {
        return value.length === 0;
    }

    return value === null || value === undefined || String(value).trim() === '';
}

function scrollToApplicantField(fieldKey: string) {
    nextTick(() => {
        const selector = `[data-field-key="${fieldKey}"]`;
        const wrapper =
            applicantFieldRefs.value[fieldKey] ??
            (document.querySelector(selector) as HTMLElement | null);

        if (!wrapper) return;

        wrapper.scrollIntoView({ behavior: 'smooth', block: 'center' });

        const focusable = wrapper.querySelector(
            'input:not([disabled]), textarea:not([disabled]), select:not([disabled]), button:not([disabled])',
        ) as HTMLElement | null;

        focusable?.focus();
    });
}

function scrollToFirstFieldError(errors: Record<string, string>) {
    const keys = Object.keys(errors);
    if (keys.length === 0) return;

    const preferredOrder = ['keperluan', 'tanggal_kebutuhan', 'lampiran'];
    const firstMatch =
        preferredOrder.find((key) => keys.includes(key)) ??
        keys.find((key) => key.startsWith('field_data.')) ??
        keys[0];

    if (firstMatch) {
        scrollToApplicantField(firstMatch);
    }
}

function validateRequiredFields(): string | null {
    submitForm.clearErrors();

    if (!String(submitForm.keperluan).trim()) {
        submitForm.setError('keperluan', 'Wajib diisi.');
        return 'keperluan';
    }

    if (!String(submitForm.tanggal_kebutuhan).trim()) {
        submitForm.setError('tanggal_kebutuhan', 'Wajib diisi.');
        return 'tanggal_kebutuhan';
    }

    for (const field of selectedJenis.value?.fieldConfig ?? []) {
        if (
            !isApplicantFieldVisible(field) ||
            isApplicantFieldReadonly(field) ||
            !field.required
        ) {
            continue;
        }

        const value = submitForm.field_data[field.name] as FieldValue;
        if (isFieldMissing(field, value)) {
            submitForm.setError(`field_data.${field.name}`, 'Wajib diisi.');
            return `field_data.${field.name}`;
        }
    }

    return null;
}

function fieldDisplayValue(field: FieldConfig): string {
    const val = submitForm.field_data[field.name];
    if (val === null || val === undefined || val === '') return '-';
    if (Array.isArray(val)) {
        if (val.length === 0) return '-';
        const labels = val.map(
            (v) => field.options.find((o) => o.value === v)?.label ?? String(v),
        );
        return labels.join(', ');
    }
    if (field.type === 'checkbox') return val ? 'Ya' : 'Tidak';
    if (field.options.length > 0)
        return field.options.find((o) => o.value === val)?.label ?? String(val);
    return String(val);
}

function goToPreview() {
    const firstInvalid = validateRequiredFields();
    if (firstInvalid) {
        formStep.value = 'form';
        scrollToApplicantField(firstInvalid);
        return;
    }

    formStep.value = 'preview';
}
function backToForm() {
    formStep.value = 'form';
}

// -- Lampiran ----------------------------------------------------------------
const fileInput = ref<HTMLInputElement | null>(null);

function triggerFilePick() {
    fileInput.value?.click();
}

function onFilesPicked(event: Event) {
    const input = event.target as HTMLInputElement;
    if (!input.files) return;
    submitForm.lampiran = [...submitForm.lampiran, ...Array.from(input.files)];
    input.value = '';
}

function removeFile(index: number) {
    submitForm.lampiran = submitForm.lampiran.filter((_, i) => i !== index);
}

function fileSize(bytes: number): string {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}
// -- End Lampiran -------------------------------------------------------------

function doSubmit() {
    const firstInvalid = validateRequiredFields();
    if (firstInvalid) {
        formStep.value = 'form';
        scrollToApplicantField(firstInvalid);
        return;
    }

    submitForm.post(safeEndpoints.value.submission, {
        forceFormData: true,
        onSuccess: () => closeForm(),
        onError: (errors) => {
            scrollToFirstFieldError(errors as Record<string, string>);
        },
    });
}

// -- Viewer state -------------------------------------------------------------
const viewerOpen = ref(false);
const viewerUrl = ref<string | null>(null);
const viewerTitle = ref('');
const viewerMode = ref<'html' | 'pdf'>('html');
const viewerNomor = ref<string | null>(null);
const iframeZoom = ref(100);
const iframeLoad = ref(true);
const iframeError = ref(false);

function openViewer(item: LatestSubmission, mode: 'preview' | 'pdf') {
    iframeZoom.value = 100;
    iframeLoad.value = true;
    iframeError.value = false;

    if (mode === 'pdf' && item.hasPdf) {
        viewerUrl.value = `/documents/surat/${item.id}/pdf`;
        viewerMode.value = 'pdf';
    } else {
        viewerUrl.value = `/documents/surat/${item.id}/generated-document`;
        viewerMode.value = 'html';
    }
    viewerTitle.value = `${item.jenisSurat} - ${item.reference}`;
    viewerNomor.value = item.reference;
    viewerOpen.value = true;
}

function closeViewer() {
    viewerOpen.value = false;
    setTimeout(() => {
        viewerUrl.value = null;
    }, 200);
}

function openInNewTab() {
    if (viewerUrl.value) window.open(viewerUrl.value, '_blank');
}
// -- End Viewer ----------------------------------------------------------------

const firstName = computed(
    () => String(page.props.auth?.user?.name ?? 'Pengguna').split(' ')[0],
);
const roleSlug = computed(() =>
    String(props.userType.value ?? '').toLowerCase(),
);
const dashboardGreeting = computed(() => {
    return firstName.value;
});
const dashboardIntro = computed(() => {
    return 'Cek dan kelola pengajuan surat Anda di sini.';
});

type StatCard = {
    label: string;
    value: number;
    icon: unknown;
    iconWrap: string;
    accent: string;
    glow: string;
    summary: string;
};

const heroSummaryCards = computed<StatCard[]>(() => {
    const summary = safeSummary.value;

    return [
        {
            label: 'Total Surat',
            value: summary.total,
            icon: FileText,
            iconWrap: 'bg-blue-50 text-blue-600 ring-blue-100',
            accent: 'from-blue-500 via-blue-400 to-cyan-400',
            glow: 'shadow-blue-500/15',
            summary: 'Seluruh pengajuan yang tercatat di sistem',
        },
        {
            label: 'Diproses',
            value: summary.diproses,
            icon: RefreshCw,
            iconWrap: 'bg-sky-50 text-sky-600 ring-sky-100',
            accent: 'from-sky-500 via-cyan-400 to-blue-400',
            glow: 'shadow-sky-500/15',
            summary: 'Masih menunggu validasi atau tindak lanjut',
        },
        {
            label: 'Selesai',
            value: summary.selesai,
            icon: CheckCircle2,
            iconWrap: 'bg-emerald-50 text-emerald-600 ring-emerald-100',
            accent: 'from-emerald-500 via-green-400 to-teal-400',
            glow: 'shadow-emerald-500/15',
            summary: 'Sudah selesai dan siap digunakan',
        },
        {
            label: 'Ditolak',
            value: summary.ditolak,
            icon: XCircle,
            iconWrap: 'bg-rose-50 text-rose-600 ring-rose-100',
            accent: 'from-rose-500 via-red-400 to-rose-400',
            glow: 'shadow-rose-500/15',
            summary: 'Perlu perhatian atau pengajuan ulang',
        },
    ];
});

const heroHighlights = computed(() => {
    const summary = safeSummary.value;
    const finishedRatio = summary.total > 0 ? Math.round((summary.selesai / summary.total) * 100) : 0;
    const runningRatio = summary.total > 0 ? Math.round((summary.diproses / summary.total) * 100) : 0;

    return [
        {
            label: 'Status sistem',
            value: 'Aktif',
            tone: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            pulse: true,
        },
        {
            label: 'Diperbarui',
            value: `${formatDate(todayString())}`,
            tone: 'bg-white/10 text-white border-white/15',
            pulse: false,
        },
        {
            label: 'Selesai',
            value: `${finishedRatio}%`,
            tone: 'bg-white/10 text-white border-white/15',
            pulse: false,
        },
        {
            label: 'Diproses',
            value: `${runningRatio}%`,
            tone: 'bg-white/10 text-white border-white/15',
            pulse: false,
        },
    ];
});

function formatDate(date?: string | null) {
    if (!date) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(date));
}

function statusLabel(status: string) {
    const map: Record<string, string> = {
        pending: 'Menunggu Validasi',
        revision_requested: 'Sedang Direvisi Admin',
        validated_admin: 'Diteruskan ke Approver',
        approved_kaprodi: 'Disetujui',
        approved_dekan: 'Disetujui',
        finished: 'Selesai',
        rejected_admin: 'Ditolak Admin',
        rejected_approver: 'Ditolak Pimpinan',
        cancelled: 'Dibatalkan',
    };
    return map[status] ?? 'Diproses';
}

function submissionStatusLabel(item: LatestSubmission) {
    if (item.status === 'revision_requested' && item.needsRevision) {
        return 'Perlu Revisi';
    }

    if (
        item.status === 'finished' ||
        (item.hasPdf &&
            ![
                'pending',
                'revision_requested',
                'rejected_admin',
                'rejected_approver',
                'cancelled',
            ].includes(item.status))
    ) {
        return 'Selesai';
    }

    return statusLabel(item.status);
}

function statusBadgeClass(status: string) {
    if (status === 'revision_requested') return 'bg-amber-50 text-amber-700';
    if (status === 'rejected_admin' || status === 'rejected_approver')
        return 'bg-red-50 text-red-700';
    if (status === 'pending') return 'bg-amber-50 text-amber-700';
    if (status === 'validated_admin') return 'bg-slate-100 text-slate-700';
    if (status === 'cancelled') return 'bg-slate-100 text-slate-600';
    return 'bg-emerald-50 text-emerald-700';
}

function statusTone(status: string) {
    if (status === 'finished') {
        return {
            card: 'border-emerald-200',
            iconWrap: 'bg-emerald-50 text-emerald-600',
            accent: 'bg-emerald-400',
            text: 'text-emerald-600',
            progress: 'bg-emerald-400',
        };
    }
    if (status === 'rejected_admin' || status === 'rejected_approver') {
        return {
            card: 'border-red-200',
            iconWrap: 'bg-red-50 text-red-600',
            accent: 'bg-red-400',
            text: 'text-red-600',
            progress: 'bg-red-400',
        };
    }
    if (status === 'revision_requested') {
        return {
            card: 'border-amber-200',
            iconWrap: 'bg-amber-50 text-amber-600',
            accent: 'bg-amber-400',
            text: 'text-amber-600',
            progress: 'bg-amber-400',
        };
    }
    if (status === 'validated_admin') {
        return {
            card: 'border-slate-200',
            iconWrap: 'bg-slate-100 text-slate-600',
            accent: 'bg-slate-400',
            text: 'text-slate-600',
            progress: 'bg-slate-400',
        };
    }
    if (status.startsWith('approved')) {
        return {
            card: 'border-emerald-200',
            iconWrap: 'bg-emerald-50 text-emerald-600',
            accent: 'bg-emerald-400',
            text: 'text-emerald-600',
            progress: 'bg-emerald-400',
        };
    }
    return {
        card: 'border-amber-200',
        iconWrap: 'bg-amber-50 text-amber-600',
        accent: 'bg-amber-400',
        text: 'text-amber-600',
        progress: 'bg-amber-400',
    };
}

function dashboardProgressLabel(item: LatestSubmission): string {
    const status = item.status;
    if (status === 'rejected_admin') return 'Ditolak Admin';
    if (status === 'rejected_approver') return 'Ditolak Pimpinan';
    if (status === 'revision_requested') return 'Perlu Revisi';
    if (status === 'cancelled') return 'Dibatalkan';
    if (status === 'approved_kaprodi' || status === 'approved_dekan') {
        return `Disetujui ${approvalRoleLabel(item)}`;
    }
    if (status === 'validated_admin') return 'Divalidasi Admin';
    if (status === 'finished') return 'Selesai';
    return 'Diajukan';
}

function dashboardProgressDescription(item: LatestSubmission): string {
    if (item.status === 'rejected_admin' || item.status === 'rejected_approver') {
        return 'Pengajuan berhenti pada tahap penolakan dan tidak melanjut ke proses berikutnya.';
    }
    if (item.status === 'revision_requested') {
        return 'Pengajuan masih menunggu perbaikan sebelum diproses kembali.';
    }
    if (item.status === 'finished') {
        return 'Seluruh tahap selesai dan dokumen akhir dapat diproses.';
    }
    const steps = progressSteps(item);
    const idx = getProgressStepIndex(item);
    const current = steps[idx]?.short ?? 'Diajukan';
    const next = steps[idx + 1]?.short;
    return next
        ? `Saat ini berada pada tahap ${current}. Tahap berikutnya: ${next}.`
        : `Saat ini berada pada tahap ${current}.`;
}

async function showToast(message: string) {
    if (toastTimeoutId !== null) {
        window.clearTimeout(toastTimeoutId);
        toastTimeoutId = null;
    }
    toastMessage.value = '';
    await nextTick();
    toastMessage.value = message;
    toastTimeoutId = window.setTimeout(() => {
        toastMessage.value = '';
        toastTimeoutId = null;
    }, 3200);
}

watch(
    () => page.props.flash?.success,
    (message) => {
        if (typeof message === 'string' && message.length > 0)
            showToast(message);
    },
    { immediate: true },
);

function openForm(jenis: JenisSuratOption) {
    router.get(
        `${safeEndpoints.value.basePath}/ajukan`,
        { jenis: jenis.id },
        { preserveScroll: true },
    );
}

function rejectionHeadline(item: LatestSubmission) {
    if (item.needsRevision) {
        return `Sedang direvisi admin setelah catatan ${item.rejectedByRole === 'dekan' ? 'Dekan' : 'Kaprodi'}`;
    }

    return item.rejectedByRole === 'admin'
        ? 'Pengajuan ditolak admin'
        : 'Pengajuan ditolak pimpinan';
}
function closeForm() {
    showFormModal.value = false;
    selectedJenis.value = null;
    submitForm.reset();
    submitForm.clearErrors();
}
function toggleReason(id: number) {
    expandedReasonId.value = expandedReasonId.value === id ? null : id;
}
function toggleNotes(id: number) {
    expandedNotesId.value = expandedNotesId.value === id ? null : id;
}
function todayString() {
    return new Date().toISOString().slice(0, 10);
}

function fieldError(name: string): string | undefined {
    return (submitForm.errors as Record<string, string>)[`field_data.${name}`];
}
</script>

<template>
    <FastLayout
        title="Dashboard"
        active-menu="dashboard"
        :breadcrumbs="[{ label: 'Dashboard' }]"
    >
        <Head title="Dashboard - FAST" />

        <!-- Greeting -->
        <section class="relative mb-6 overflow-hidden rounded-[2rem] border border-slate-200/80 bg-[var(--primary)] text-white shadow-[0_18px_50px_rgba(15,23,42,0.14)]">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(255,255,255,0.16),_transparent_34%),radial-gradient(circle_at_80%_20%,_rgba(191,219,254,0.18),_transparent_24%),linear-gradient(135deg,_hsl(221_78%_45%),_hsl(221_78%_58%)_48%,_hsl(203_92%_68%))]" />
            <div class="absolute inset-0 opacity-[0.10] mix-blend-soft-light bg-[linear-gradient(rgba(255,255,255,0.95)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.95)_1px,transparent_1px)] bg-[size:28px_28px]" />
            <div class="absolute -left-16 top-0 h-56 w-56 rounded-full bg-white/14 blur-3xl" />
            <div class="absolute top-8 right-0 h-64 w-64 rounded-full bg-sky-200/14 blur-3xl" />
            <div class="absolute bottom-0 left-1/2 h-40 w-40 -translate-x-1/2 rounded-full bg-white/10 blur-3xl" />

            <div class="relative px-5 py-5 sm:px-6 sm:py-6 lg:px-8 lg:py-8">
                <div class="grid gap-6 lg:grid-cols-[minmax(0,1.25fr)_340px] lg:items-stretch">
                    <div class="flex min-w-0 flex-col justify-between gap-6">
                        <div class="max-w-3xl">
                            <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[11px] font-semibold tracking-[0.18em] text-white/90 uppercase shadow-sm backdrop-blur-sm transition-all duration-300 hover:scale-[1.02] hover:bg-white/15">
                                <span class="relative flex size-2">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-300 opacity-60" />
                                    <span class="relative inline-flex size-2 rounded-full bg-emerald-300" />
                                </span>
                                Dashboard Akademik
                            </div>
                            <p class="text-base font-medium tracking-tight text-white/85 sm:text-lg">
                                Selamat datang,
                                <span class="font-semibold text-white">{{ dashboardGreeting }}</span>
                            </p>
                            <h2 class="mt-2 max-w-3xl text-2xl font-semibold leading-tight text-white sm:text-3xl lg:text-4xl">
                                {{ dashboardIntro }}
                            </h2>
                            <p class="mt-3 max-w-2xl text-sm leading-relaxed text-white/75 sm:text-[15px]">
                                Semua status penting ditampilkan supaya Anda cepat tahu apa yang sedang berjalan.
                            </p>
                        </div>

                    </div>

                </div>
            </div>

        </section>

        <div class="-mt-1 mb-6 grid grid-cols-2 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="stat in heroSummaryCards"
                :key="stat.label"
                class="group relative overflow-hidden rounded-[1.5rem] border border-slate-200/80 bg-white/95 p-4 text-slate-900 shadow-[0_10px_30px_rgba(15,23,42,0.08)] transition-all duration-300 hover:-translate-y-1 hover:scale-[1.005] hover:shadow-[0_14px_28px_rgba(59,130,246,0.10)]"
                :class="stat.label === 'Total Surat' ? 'border-blue-200/80' : stat.label === 'Diproses' ? 'border-sky-200/80' : stat.label === 'Selesai' ? 'border-emerald-200/80' : 'border-rose-200/80'"
            >
                <div
                    class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r transition-all duration-300 group-hover:h-1.5"
                    :class="stat.accent"
                />
                <div class="absolute -right-8 -top-8 size-24 rounded-full opacity-0 blur-2xl transition-all duration-300 group-hover:opacity-100" :class="stat.glow" />

                <div class="relative flex h-full min-h-[148px] flex-col justify-between gap-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-[11px] font-semibold tracking-[0.22em] text-slate-400 uppercase">
                                {{ stat.label }}
                            </p>
                            <p class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-[2.15rem]">
                                {{ String(stat.value).padStart(2, '0') }}
                            </p>
                        </div>
                        <div
                            class="grid size-11 shrink-0 place-items-center rounded-2xl ring-1 transition-all duration-300 group-hover:scale-[1.03] group-hover:-rotate-1"
                            :class="stat.iconWrap"
                        >
                            <component :is="stat.icon" class="size-5" />
                        </div>
                    </div>

                    <p class="text-xs leading-relaxed text-slate-500">
                        {{ stat.summary }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Main grid -->
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
            <!-- Kiri: Pengajuan Terbaru -->
            <div class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-[0_1px_2px_rgba(15,23,42,0.03)]">
                <!-- Toolbar -->
                <div
                    class="flex items-start justify-between gap-3 border-b border-slate-100 bg-slate-50/40 px-5 py-4"
                >
                    <div class="min-w-0">
                        <h2 class="text-base font-semibold text-slate-900">
                            Pengajuan Terbaru
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ latestVisible.length }} pengajuan
                        </p>
                    </div>
                    <Link
                        :href="`${safeEndpoints.basePath}/history`"
                        class="fast-btn fast-btn-primary w-fit shrink-0 px-4 py-2 text-xs"
                    >
                        <History class="size-3.5" />
                        Lihat Semua
                    </Link>
                </div>

                <!-- Perlu Revisi banner -->
                <div
                    v-if="latestVisible.some((i) => i.needsRevision)"
                    class="mx-5 mt-4 rounded-2xl border border-amber-200 bg-amber-50/70 px-4 py-3"
                >
                    <div class="flex items-start gap-2">
                        <AlertCircle class="mt-0.5 size-4 shrink-0 text-amber-600" />
                        <div data-field-key="lampiran">
                            <p class="text-sm font-semibold text-amber-800">
                                Ada pengajuan yang perlu direvisi
                            </p>
                            <p class="mt-0.5 text-xs text-amber-700">
                                Tinjau catatan sebelum melanjutkan.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Pengajuan Cards -->
                <div
                    v-if="latest.length === 0"
                    class="px-5 py-14 text-center text-sm text-slate-400"
                >
                    <div
                        class="mx-auto mb-4 grid size-14 place-items-center rounded-2xl bg-slate-100 text-slate-300"
                    >
                        <FileText class="size-7" />
                    </div>
                    <p class="font-semibold text-slate-700">
                        Belum ada pengajuan surat
                    </p>
                    <p class="mt-1 text-xs text-slate-400">
                        Pengajuan terbaru akan muncul di sini setelah surat dibuat.
                    </p>
                </div>

                <div v-else class="space-y-3 px-5 py-4">
                    <article
                        v-for="(item, i) in latestVisible"
                        :key="item.id"
                        class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-[0_1px_2px_rgba(15,23,42,0.03)] transition-all hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-[0_8px_24px_rgba(15,23,42,0.05)]"
                        :class="statusTone(item.status).card"
                    >
                        <div class="flex flex-col gap-4 p-4 sm:p-5">
                            <div class="flex items-start gap-3 sm:gap-4">
                                <div
                                    class="grid size-12 shrink-0 place-items-center rounded-2xl"
                                    :class="statusTone(item.status).iconWrap"
                                >
                                    <FileText class="size-5" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="text-[10px] font-semibold tracking-[0.18em] text-slate-400 uppercase">
                                                {{ item.reference }}
                                            </p>
                                            <h3 class="mt-1 text-sm font-semibold text-slate-900 sm:text-base">
                                                {{ item.jenisSurat }}
                                            </h3>
                                        </div>
                                        <span
                                            class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-semibold"
                                            :class="statusBadgeClass(item.status)"
                                        >
                                            {{ submissionStatusLabel(item) }}
                                        </span>
                                    </div>
                                    <p
                                        v-if="item.keperluan"
                                        class="mt-2 max-w-3xl text-sm leading-relaxed text-slate-600"
                                    >
                                        {{ item.keperluan }}
                                    </p>
                                    <div
                                        class="mt-3 flex flex-wrap items-center gap-3 text-[11px] text-slate-400"
                                    >
                                        <span class="flex items-center gap-1">
                                            <History class="size-3.5" />
                                            {{ formatDate(item.submittedAt) }}
                                        </span>
                                        <span v-if="item.neededAt" class="flex items-center gap-1">
                                            <Calendar class="size-3.5" />
                                            Dibutuhkan {{ formatDate(item.neededAt) }}
                                        </span>
                                        <span
                                            v-if="item.timeline?.length"
                                            class="inline-flex items-center rounded-full bg-slate-100 px-2 py-1 font-medium text-slate-500"
                                        >
                                            {{ item.timeline.length }} aktivitas
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-[0_1px_2px_rgba(15,23,42,0.03)]"
                                :class="statusTone(item.status).card"
                            >
                                <div
                                    class="h-1.5 w-full"
                                    :class="statusTone(item.status).accent"
                                />

                                <div class="p-4">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="text-[11px] font-semibold tracking-[0.18em] text-slate-400 uppercase">
                                                    Progress Surat
                                                </p>
                                                <span
                                                    class="rounded-full px-2.5 py-1 text-[10px] font-semibold"
                                                    :class="statusBadgeClass(item.status)"
                                                >
                                                    {{ dashboardProgressLabel(item) }}
                                                </span>
                                            </div>
                                            <p class="mt-1 text-xs leading-relaxed text-slate-500">
                                                {{ dashboardProgressDescription(item) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <div class="mb-2 flex items-center justify-between gap-3 text-[10px] text-slate-400">
                                            <span>Progres proses</span>
                                            <span>
                                                {{ getProgressStepIndex(item) < 0 ? '0%' : Math.round(((getProgressStepIndex(item) + 1) / progressSteps(item).length) * 100) }}%
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mt-5 pb-1">
                                        <div
                                            class="relative mx-auto grid w-full max-w-[560px] justify-items-center gap-0"
                                            :class="
                                                progressSteps(item).length === 3
                                                    ? 'grid-cols-3'
                                                    : 'grid-cols-4'
                                            "
                                        >
                                            <div
                                                class="pointer-events-none absolute left-[18px] right-[18px] top-[18px] h-px overflow-hidden rounded-full bg-slate-200"
                                            >
                                                <span
                                                    class="absolute inset-y-0 left-0 rounded-full"
                                                    :class="statusTone(item.status).progress"
                                                    :style="{ width: `${getProgressPercent(item)}%` }"
                                                />
                                            </div>
                                            <div
                                                v-for="(step, stepIndex) in progressSteps(item)"
                                                :key="step.key"
                                                class="relative z-10 min-w-0 w-full px-1"
                                            >
                                                <div
                                                    class="mx-auto flex size-9 items-center justify-center rounded-full border-2 text-sm font-bold transition-all"
                                                    :aria-label="step.short"
                                                    :class="
                                                        isProgressStepFilled(item, stepIndex)
                                                            ? `${statusTone(item.status).progress} border-transparent text-white shadow-sm`
                                                            : isProgressStepActive(item, stepIndex)
                                                              ? `border-current bg-white ${statusTone(item.status).text} shadow-sm`
                                                              : 'border-slate-200 bg-white text-slate-400'
                                                    "
                                                >
                                                    <CheckCircle2
                                                        v-if="isProgressStepFilled(item, stepIndex)"
                                                        class="size-4"
                                                    />
                                                    <span
                                                        v-else
                                                        class="block size-1.5 rounded-full bg-current"
                                                    />
                                                </div>
                                                <p
                                                    class="mt-3 w-full text-center text-[10px] font-semibold leading-tight"
                                                    :class="
                                                        isProgressStepFilled(item, stepIndex)
                                                            ? 'text-slate-700'
                                                            : isProgressStepActive(item, stepIndex)
                                                              ? statusTone(item.status).text
                                                              : 'text-slate-400'
                                                    "
                                                >
                                                    {{ step.short }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="expandedNotesId === item.id && item.notes?.length"
                                class="rounded-2xl border border-sky-100 bg-sky-50/60 p-3"
                            >
                                <div class="mb-2 flex items-center gap-2">
                                    <Info class="size-4 text-sky-600" />
                                    <p class="text-xs font-semibold text-sky-800">
                                        Catatan
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    <div
                                        v-for="(note, nidx) in item.notes"
                                        :key="nidx"
                                        class="rounded-xl border border-sky-100 bg-white px-3 py-2 text-xs"
                                    >
                                        <div class="mb-1 flex items-center gap-1.5">
                                            <span class="font-semibold text-sky-800">
                                                {{ note.oleh ?? 'Admin' }}
                                            </span>
                                            <span class="text-[10px] text-sky-400">
                                                {{ note.created_at ? formatDate(note.created_at) : '' }}
                                            </span>
                                        </div>
                                        <p class="text-sky-700">
                                            {{ note.catatan }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="
                                    expandedReasonId === item.id &&
                                    (item.rejectionReason || item.revisionReason)
                                "
                                class="rounded-2xl border border-red-100 bg-red-50 px-3 py-2 text-xs text-red-700"
                            >
                                <span class="font-semibold">
                                    {{ item.needsRevision ? 'Catatan revisi:' : 'Alasan penolakan:' }}
                                </span>
                                {{
                                    item.needsRevision
                                        ? item.revisionReason
                                        : item.rejectionReason
                                }}
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <Link
                                        :href="`${safeEndpoints.basePath}/history/${item.id}`"
                                        title="Detail Surat"
                                        class="fast-btn fast-btn-outline px-3 py-1.5 text-[11px] font-medium text-slate-600"
                                    >
                                        <FileText class="size-3.5" /> Detail Surat
                                    </Link>
                                    <button
                                        v-if="item.hasPdf"
                                        type="button"
                                        title="Download PDF"
                                        class="fast-btn fast-btn-primary px-3 py-1.5 text-[10px] font-medium"
                                        @click="openViewer(item, 'pdf')"
                                    >
                                        <Download class="size-3" /> PDF
                                    </button>
                                    <button
                                        v-if="item.notes && item.notes.length"
                                        type="button"
                                        title="Catatan Dekan"
                                        class="fast-btn fast-btn-soft px-3 py-1.5 text-[11px] font-medium text-sky-700"
                                        @click="toggleNotes(item.id)"
                                    >
                                        <Info class="size-3.5" /> Catatan
                                    </button>
                                    <button
                                        v-if="item.rejectionReason || item.revisionReason"
                                        type="button"
                                        title="Catatan"
                                        class="fast-btn fast-btn-soft px-3 py-1.5 text-[11px] font-medium text-red-600"
                                        @click="toggleReason(item.id)"
                                    >
                                        <AlertCircle class="size-3.5" /> Detail
                                    </button>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            </div>

            <!-- Kanan: Ajukan -->
            <div class="space-y-4">
                <LetterFlowCard />

                <!-- Ajukan Surat Baru -->
                <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                    <h3
                        class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-900"
                    >
                        <FilePlus2 class="size-4 text-blue-500" /> Ajukan Surat
                    </h3>

                    <!-- Card grid -->
                    <div
                        v-if="visibleJenis.length === 0"
                        class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 px-4 py-6 text-center"
                    >
                        <FileText class="mx-auto mb-2 size-6 text-slate-300" />
                        <p class="text-xs font-medium text-slate-600">
                            Tidak ada jenis surat
                        </p>
                    </div>
                    <div v-else class="grid gap-2">
                        <button
                        v-for="jenis in visibleJenis"
                            :key="jenis.id"
                            type="button"
                            class="group relative rounded-2xl border border-slate-200 bg-white p-3 text-left transition-all hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md"
                            @click="openForm(jenis)"
                        >
                            <div class="flex items-start gap-2.5">
                                <div
                                    class="grid size-9 shrink-0 place-items-center rounded-xl border border-blue-100 bg-blue-50 text-blue-600"
                                >
                                    <FileText class="size-4" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold text-slate-900">
                                        {{ jenis.nama }}
                                    </p>
                                    <p
                                        v-if="jenis.deskripsi"
                                        class="mt-0.5 line-clamp-1 text-[10px] text-slate-400"
                                    >
                                        {{ jenis.deskripsi }}
                                    </p>
                                </div>
                                <Plus class="size-3.5 shrink-0 text-slate-300 transition-colors group-hover:text-blue-500" />
                            </div>
                        </button>
                    </div>

                    <Link
                        v-if="hasMoreJenis"
                        href="/mahasiswa/ajukan"
                        class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-blue-600 transition-colors hover:text-blue-700"
                    >
                        Selengkapnya
                        <ExternalLink class="size-3.5" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- Modal Ajukan Surat -->
        <Transition name="modal">
            <div
                v-if="showFormModal"
                class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 sm:items-center"
                @click.self="closeForm"
            >
                <form
                    class="relative z-10 w-full max-w-lg rounded-t-2xl bg-white pointer-events-auto sm:rounded-2xl"
                    @submit.prevent="doSubmit"
                >
                    <!-- Header -->
                    <div
                        class="flex items-center justify-between border-b border-slate-100 px-5 py-4"
                    >
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">
                                {{ selectedJenis?.nama }}
                            </h3>
                            <p class="text-xs text-slate-400">
                                {{
                                    formStep === 'form'
                                        ? 'Isi data pengajuan'
                                        : 'Tinjau sebelum mengirim'
                                }}
                            </p>
                        </div>
                        <button
                            type="button"
                            class="grid size-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-100"
                            @click="closeForm"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <!-- Step: Form -->
                    <div
                        v-if="formStep === 'form'"
                        class="max-h-[70vh] space-y-4 overflow-y-auto p-5"
                    >
                        <!-- Keperluan -->
                        <div data-field-key="keperluan">
                            <label
                                class="mb-1 block text-xs font-medium text-slate-700"
                            >
                                Keperluan <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                v-model="submitForm.keperluan"
                                rows="2"
                                placeholder="Jelaskan keperluan pengajuan surat..."
                                class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-blue-400 focus:bg-white"
                            ></textarea>
                            <p
                                v-if="submitForm.errors.keperluan"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ submitForm.errors.keperluan }}
                            </p>
                        </div>

                        <!-- Tanggal kebutuhan -->
                        <div data-field-key="tanggal_kebutuhan">
                            <label
                                class="mb-1 block text-xs font-medium text-slate-700"
                            >
                                Tanggal Dibutuhkan
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="submitForm.tanggal_kebutuhan"
                                type="date"
                                :min="todayString()"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-blue-400 focus:bg-white"
                            />
                            <p
                                v-if="submitForm.errors.tanggal_kebutuhan"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ submitForm.errors.tanggal_kebutuhan }}
                            </p>
                        </div>

                        <!-- Dynamic fields -->
                        <template
                            v-for="field in selectedJenis?.fieldConfig ?? []"
                            :key="field.name"
                        >
                            <div
                                v-if="isApplicantFieldVisible(field)"
                                :data-field-key="`field_data.${field.name}`"
                                :ref="(el) => setApplicantFieldRef(`field_data.${field.name}`, el)"
                            >
                                <label
                                    class="mb-1 block text-xs font-medium text-slate-700"
                                >
                                    {{ field.label }}
                                    <span
                                        v-if="field.required"
                                        class="text-red-500"
                                        >*</span
                                    >
                                </label>
                                <span
                                    v-if="isApplicantFieldReadonly(field)"
                                    class="mb-2 inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-semibold text-amber-700"
                                >
                                    Data oleh kampus
                                </span>
                                <!-- textarea -->
                                <textarea
                                    v-if="field.type === 'textarea'"
                                    v-model="
                                        (
                                            submitForm.field_data as Record<
                                                string,
                                                FieldValue
                                            >
                                        )[field.name] as string
                                    "
                                    :placeholder="field.placeholder"
                                    rows="3"
                                    :readonly="isApplicantFieldReadonly(field)"
                                    :class="[
                                        'w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-blue-400',
                                        isApplicantFieldReadonly(field) ? 'cursor-not-allowed bg-slate-100 text-slate-500' : '',
                                    ]"
                                ></textarea>
                                <!-- select -->
                                <select
                                    v-else-if="field.type === 'select'"
                                    v-model="
                                        (
                                            submitForm.field_data as Record<
                                                string,
                                                FieldValue
                                            >
                                        )[field.name]
                                    "
                                    :disabled="isApplicantFieldReadonly(field)"
                                    :class="[
                                        'w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none focus:border-blue-400',
                                        isApplicantFieldReadonly(field) ? 'cursor-not-allowed bg-slate-100 text-slate-500' : '',
                                    ]"
                                >
                                    <option value="">-- Pilih --</option>
                                    <option
                                        v-for="opt in field.options"
                                        :key="opt.value"
                                        :value="opt.value"
                                    >
                                        {{ opt.label }}
                                    </option>
                                </select>
                                <!-- radio -->
                                <div
                                    v-else-if="field.type === 'radio'"
                                    class="flex flex-wrap gap-3 pt-1"
                                >
                                    <label
                                        v-for="opt in field.options"
                                        :key="opt.value"
                                        class="flex cursor-pointer items-center gap-1.5 text-sm"
                                    >
                                        <input
                                            type="radio"
                                            :value="opt.value"
                                            v-model="
                                                (
                                                    submitForm.field_data as Record<
                                                        string,
                                                    FieldValue
                                                >
                                            )[field.name]
                                            "
                                            :disabled="isApplicantFieldReadonly(field)"
                                            class="text-blue-600"
                                        />
                                        {{ opt.label }}
                                    </label>
                                </div>
                                <!-- checkbox -->
                                <label
                                    v-else-if="field.type === 'checkbox'"
                                    class="flex cursor-pointer items-center gap-2 pt-1"
                                >
                                    <input
                                        type="checkbox"
                                        v-model="
                                            (
                                                submitForm.field_data as Record<
                                                    string,
                                                    FieldValue
                                                >
                                            )[field.name]
                                        "
                                        :disabled="isApplicantFieldReadonly(field)"
                                        class="rounded text-blue-600"
                                    />
                                    <span class="text-sm text-slate-700">{{
                                        field.placeholder || field.label
                                    }}</span>
                                </label>
                                <!-- checkbox-group / multiselect -->
                                <div
                                    v-else-if="
                                        [
                                            'checkbox-group',
                                            'multiselect',
                                        ].includes(field.type)
                                    "
                                    class="flex flex-wrap gap-3 pt-1"
                                >
                                    <label
                                        v-for="opt in field.options"
                                        :key="opt.value"
                                        class="flex cursor-pointer items-center gap-1.5 text-sm"
                                    >
                                        <input
                                            type="checkbox"
                                            :value="opt.value"
                                            v-model="
                                        (
                                            submitForm.field_data as Record<
                                                string,
                                                FieldValue
                                            >
                                        )[field.name] as string[]
                                            "
                                            :disabled="isApplicantFieldReadonly(field)"
                                            class="rounded text-blue-600"
                                        />
                                        {{ opt.label }}
                                    </label>
                                </div>
                                <!-- default: text / number / date / email -->
                                <input
                                    v-else
                                    v-model="
                                        (
                                            submitForm.field_data as Record<
                                                string,
                                                FieldValue
                                            >
                                        )[field.name] as string
                                    "
                                    :type="
                                        field.type === 'number'
                                            ? 'number'
                                            : field.type === 'date'
                                              ? 'date'
                                              : field.type === 'email'
                                                ? 'email'
                                                : 'text'
                                    "
                                    :placeholder="field.placeholder"
                                    :readonly="isApplicantFieldReadonly(field)"
                                    :disabled="isApplicantFieldReadonly(field)"
                                    :class="[
                                        'w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-blue-400 focus:bg-white',
                                        isApplicantFieldReadonly(field) ? 'cursor-not-allowed bg-slate-100 text-slate-500' : '',
                                    ]"
                                />
                                <p
                                    v-if="fieldError(field.name)"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ fieldError(field.name) }}
                                </p>
                            </div>
                        </template>

                        <!-- Lampiran -->
                        <div>
                            <label
                                class="mb-1 block text-xs font-medium text-slate-700"
                            >
                                Lampiran
                                <span
                                    class="text-[10px] font-normal text-slate-400"
                                    >(opsional)</span
                                >
                            </label>
                            <p class="mb-2 text-[10px] text-slate-400">
                                PDF, JPG, PNG, DOC - maks. 4 MB / berkas
                            </p>
                            <input
                                ref="fileInput"
                                type="file"
                                multiple
                                class="hidden"
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                @change="onFilesPicked"
                            />
                            <button
                                type="button"
                                class="flex w-full flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 py-4 text-slate-500 transition hover:border-blue-300 hover:bg-blue-50/40"
                                @click="triggerFilePick"
                            >
                                <UploadCloud class="size-5 text-slate-400" />
                                <span class="text-xs font-medium"
                                    >Klik untuk pilih berkas</span
                                >
                            </button>
                            <p
                                v-if="submitForm.errors.lampiran"
                                class="mt-1.5 text-xs text-red-500"
                            >
                                {{ submitForm.errors.lampiran }}
                            </p>
                            <ul
                                v-if="submitForm.lampiran.length"
                                class="mt-3 space-y-2"
                            >
                                <li
                                    v-for="(file, i) in submitForm.lampiran"
                                    :key="i"
                                    class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2"
                                >
                                    <Paperclip
                                        class="size-4 shrink-0 text-blue-600"
                                    />
                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="truncate text-xs font-medium text-slate-700"
                                        >
                                            {{ file.name }}
                                        </p>
                                        <p class="text-[10px] text-slate-400">
                                            {{ fileSize(file.size) }}
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        class="grid size-7 place-items-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-500"
                                        @click="removeFile(i)"
                                    >
                                        <X class="size-4" />
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Step: Preview -->
                    <div
                        v-else
                        class="max-h-[70vh] space-y-3 overflow-y-auto p-5"
                    >
                        <p class="text-xs text-slate-500">
                            Pastikan data di bawah sudah benar sebelum mengirim.
                        </p>
                        <div
                            class="divide-y divide-slate-100 rounded-xl border border-slate-200"
                        >
                            <div class="flex gap-3 px-4 py-2.5">
                                <span
                                    class="w-32 shrink-0 text-xs text-slate-400"
                                    >Jenis Surat</span
                                >
                                <span
                                    class="text-xs font-medium text-slate-900"
                                    >{{ selectedJenis?.nama }}</span
                                >
                            </div>
                            <div class="flex gap-3 px-4 py-2.5">
                                <span
                                    class="w-32 shrink-0 text-xs text-slate-400"
                                    >Keperluan</span
                                >
                                <span class="text-xs text-slate-900">{{
                                    submitForm.keperluan || '-'
                                }}</span>
                            </div>
                            <div class="flex gap-3 px-4 py-2.5">
                                <span
                                    class="w-32 shrink-0 text-xs text-slate-400"
                                    >Tgl Dibutuhkan</span
                                >
                                <span class="text-xs text-slate-900">{{
                                    submitForm.tanggal_kebutuhan || '-'
                                }}</span>
                            </div>
                            <template
                                v-for="field in selectedJenis?.fieldConfig ??
                                []"
                                :key="field.name"
                            >
                                <div class="flex gap-3 px-4 py-2.5">
                                    <span
                                        class="w-32 shrink-0 text-xs text-slate-400"
                                        >{{ field.label }}</span
                                    >
                                    <span class="text-xs text-slate-900">{{
                                        fieldDisplayValue(field)
                                    }}</span>
                                </div>
                            </template>
                            <div
                                v-if="submitForm.lampiran.length"
                                class="flex gap-3 px-4 py-2.5"
                            >
                                <span
                                    class="w-32 shrink-0 text-xs text-slate-400"
                                    >Lampiran</span
                                >
                                <ul class="flex-1 space-y-1">
                                    <li
                                        v-for="(file, i) in submitForm.lampiran"
                                        :key="i"
                                        class="flex items-center gap-1.5 text-xs text-slate-700"
                                    >
                                        <Paperclip
                                            class="size-3 text-blue-500"
                                        />
                                        {{ file.name }}
                                        <span class="text-slate-400"
                                            >({{ fileSize(file.size) }})</span
                                        >
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Footer tombol -->
                    <div class="flex gap-2 border-t border-slate-100 px-5 py-4">
                        <button
                            v-if="formStep === 'preview'"
                            type="button"
                            class="flex-1 rounded-xl border border-slate-200 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                            @click="backToForm"
                        >
                            Kembali Edit
                        </button>
                        <button
                            v-else
                            type="button"
                            class="fast-btn fast-btn-outline flex-1 rounded-xl py-2.5 text-sm font-medium text-slate-600"
                            @click="closeForm"
                        >
                            Batal
                        </button>

                        <button
                            v-if="formStep === 'form'"
                            type="button"
                            class="fast-btn fast-btn-primary flex-1 rounded-xl py-2.5 text-sm font-semibold"
                            @click="goToPreview"
                        >
                            Tinjau Pengajuan ?
                        </button>
                        <button
                            v-else
                            type="submit"
                            class="fast-btn fast-btn-primary flex-1 rounded-xl py-2.5 text-sm font-semibold"
                            :disabled="submitForm.processing"
                        >
                            {{
                                submitForm.processing
                                    ? 'Mengirim...'
                                    : 'Kirim Pengajuan'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </Transition>

        <!-- Toast -->
        <Transition name="toast">
            <div
                v-if="toastMessage"
                class="fixed top-5 left-1/2 z-50 w-[calc(100%-2rem)] max-w-sm -translate-x-1/2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-blue-800 shadow-lg"
            >
                {{ toastMessage }}
            </div>
        </Transition>

        <DocumentPreviewModal
            :open="viewerOpen"
            :mode="viewerMode"
            :title="viewerTitle"
            :subtitle="viewerNomor"
            :url="viewerUrl"
            :show-open-in-new-tab="true"
            :show-thumbnails="false"
            :initial-zoom="100"
            :show-html-zoom-controls="true"
            @close="closeViewer"
            @open-new-tab="openInNewTab"
        />
    </FastLayout>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.2s;
}
.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
.toast-enter-active,
.toast-leave-active {
    transition: all 0.2s;
}
.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateX(-50%) translateY(8px);
}
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
