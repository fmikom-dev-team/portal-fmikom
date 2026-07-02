<script setup lang="ts">
import AdminLayout from '@/layouts/Modules/Fast/AdminLayout.vue';
import DocumentPreviewModal from '@/components/DocumentPreviewModal.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import {
    AlertCircle,
    AlertTriangle,
    ArrowLeft,
    CheckCircle,
    Clock,
    Copy,
    Download,
    Eye,
    FileText,
    Paperclip,
    RefreshCcw,
    ShieldCheck,
    X,
} from 'lucide-vue-next';

type Lampiran = { id: number; name: string; url?: string | null; type: string };

type TimelineItem = {
    id: number;
    label: string;
    note?: string | null;
    description?: string | null;
    acted_at?: string | null;
    created_at?: string | null;
    status?: string | null;
    action?: string | null;
    actor?: string | null;
    role?: string | null;
};

type ApprovalNote = {
    role?: string | null;
    status?: string | null;
    label?: string | null;
    note?: string | null;
    acted_at?: string | null;
    actor?: string | null;
};

type Surat = {
    id: number;
    type?: string | null;
    nomor_surat?: string | null;
    letter_mode?: string | null;
    letter_mode_label?: string | null;
    is_institution?: boolean;
    subject?: { name: string; nim?: string | null };
    jenis_surat: string;
    keperluan: string;
    isi_surat: Record<string, any>;
    lampiran: Lampiran[];
    tanggal_pengajuan: string | null;
    status: string;
    latest_rejection?: {
        role?: string | null;
        label: string;
        type: 'revision' | 'final_reject';
        note?: string | null;
        acted_at?: string | null;
    } | null;
    approval_timeline?: TimelineItem[];
    history_timeline?: TimelineItem[];
    approval_notes?: ApprovalNote[];
    can_approve: boolean;
    can_request_revision: boolean;
    can_final_reject: boolean;
    previewTemplateUrl: string | null;
    generatedDocumentUrl: string | null;
    pdfUrl?: string | null;
    canDownloadPdf?: boolean;
};

type PageProps = {
    flash?: { success?: string };
};

const props = withDefaults(
    defineProps<
        {
            role?: { name?: string | null; slug?: string | null };
            back_href?: string;
            back_label?: string;
        } & Partial<Surat>
    >(),
    {
        role: () => ({ name: 'Approval', slug: 'dekan' }),
        back_href: '',
        back_label: 'Riwayat Approval',
    },
);

const page = usePage<PageProps>();
const viewerOpen = ref(false);
const viewerUrl = ref<string | null>(null);
const viewerTitle = ref('');
const viewerType = ref<'html' | 'pdf'>('html');
const copiedNumber = ref(false);
const toastMessage = ref('');
const attachmentPreviewOpen = ref(false);
const activeAttachment = ref<Lampiran | null>(null);

const revisionModalOpen = ref(false);
const finalRejectModalOpen = ref(false);
const revisionForm = useForm({ reason: '' });
const finalRejectForm = useForm({ reason: '' });
const expandedTimelineNoteId = ref<number | null>(null);

const normalizedRole = computed(() =>
    String(props.role.slug ?? props.role.name ?? '')
        .toLowerCase()
        .includes('kaprodi')
        ? 'kaprodi'
        : 'dekan',
);
const basePath = computed(() => `/${normalizedRole.value}`);
const backHref = computed(() => props.back_href || `${basePath.value}/arsip`);
const backLabel = computed(() => props.back_label || 'Riwayat Approval');
const activeMenu = computed(() => {
    const href = backHref.value;
    if (href.includes('/antrian')) return 'approval.antrian';
    if (href.includes('/dashboard')) return 'approval.dashboard';
    return 'approval.arsip';
});
const isFinished = computed(() => props.status === 'finished');
const canDownloadPdf = computed(() => props.canDownloadPdf ?? isFinished.value);
const documentTitle = computed(() =>
    props.nomor_surat
        ? `${props.jenis_surat} - ${props.nomor_surat}`
        : props.jenis_surat,
);

watch(
    () => page.props.flash?.success,
    (message) => {
        if (typeof message === 'string' && message.length > 0) {
            toastMessage.value = message;
            window.setTimeout(() => {
                if (toastMessage.value === message) toastMessage.value = '';
            }, 2800);
        }
    },
    { immediate: true },
);

const hiddenFields = new Set(['created_by', 'jenis_surat_id', 'jenis_surat', 'keperluan']);

const detailRows = computed(() =>
    Object.entries(props.isi_surat ?? {})
        .filter(([key]) => !hiddenFields.has(String(key)))
        .map(([key, value]) => ({
            key: String(key),
            label: humanizeKey(String(key)),
            value: formatDisplayValue(value),
        }))
        .filter((row) => row.value !== '-'),
);

const recipientValue = computed(() => {
    const payload = props.isi_surat ?? {};
    return formatDisplayValue(payload.kepada_yth ?? payload.tujuan ?? '-');
});
const subjectValue = computed(() => {
    const payload = props.isi_surat ?? {};
    return formatDisplayValue(payload.perihal ?? props.keperluan ?? '-');
});
const isInstitutionLetter = computed(
    () => !!props.is_institution || props.letter_mode === 'institution',
);
const subjectPayloadIdentifier = computed(() => {
    const payload = props.isi_surat ?? {};

    return formatDisplayValue(
        payload.nomor_induk_pemohon ??
            payload.nim_pemohon ??
            payload.nomor_induk_mahasiswa ??
            payload.nim_mahasiswa ??
            payload.nomor_induk ??
            payload.nim ??
            '-',
    );
});
const identityLabel = computed(() =>
    isInstitutionLetter.value
        ? 'Mode Surat'
        : 'Pemohon',
);
const identityNumberLabel = computed(() =>
    isInstitutionLetter.value
        ? 'Keterangan'
        : 'NIM / NIP',
);
const subjectName = computed(() =>
    isInstitutionLetter.value
        ? (props.letter_mode_label || 'Surat Institusi')
        : props.subject?.name || '-',
);
const subjectNim = computed(() =>
    isInstitutionLetter.value
        ? 'Diterbitkan atas nama kampus, fakultas, atau unit'
        : props.subject?.nim || subjectPayloadIdentifier.value || '-',
);

const processTimeline = computed(() => {
    const approval = props.approval_timeline ?? [];
    if (approval.length > 0) {
        return approval.map((entry) => ({
            id: entry.id,
            label: entry.label,
            note: entry.note ?? null,
            timestamp: entry.acted_at ?? null,
        }));
    }

    return (props.history_timeline ?? []).map((entry) => ({
        id: entry.id,
        label: entry.label,
        note: entry.description ?? null,
        timestamp: entry.created_at ?? null,
    }));
});

const statusLabel: Record<string, string> = {
    pending: 'Menunggu Validasi',
    validated_admin: 'Divalidasi Admin',
    revision_requested: 'Menunggu Revisi Admin',
    approved_kaprodi: 'Disetujui Kaprodi',
    approved_dekan: 'Disetujui Dekan',
    finished: 'Selesai',
    rejected_admin: 'Ditolak Admin',
    rejected_approver: props.latest_rejection?.label ?? 'Ditolak Pimpinan',
};

const statusColor: Record<string, string> = {
    pending: 'bg-amber-50 text-amber-700 border-amber-200',
    validated_admin: 'bg-amber-50 text-amber-700 border-amber-200',
    revision_requested: 'bg-amber-50 text-amber-700 border-amber-200',
    approved_kaprodi: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    approved_dekan: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    finished: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    rejected_admin: 'bg-red-50 text-red-700 border-red-200',
    rejected_approver: 'bg-red-50 text-red-700 border-red-200',
};

function filled(value: unknown): boolean {
    return value !== null && value !== undefined && String(value).trim() !== '';
}

function humanizeKey(key: string): string {
    return key
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase());
}

function formatDisplayValue(value: unknown): string {
    if (value === null || value === undefined || value === '') return '-';
    if (Array.isArray(value)) {
        const items = value
            .map((item) => formatDisplayValue(item))
            .filter((item) => item !== '-');
        return items.length > 0 ? items.join(', ') : '-';
    }
    if (typeof value === 'object') {
        const candidate = value as Record<string, unknown>;
        return (
            String(candidate.name ?? candidate.label ?? candidate.value ?? '') ||
            '-'
        );
    }
    return String(value);
}

function formatDate(iso: string | null): string {
    if (!iso) return '-';

    return (
        new Date(iso).toLocaleString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }) + ' WIB'
    );
}

function openPreviewDocument() {
    const sourceUrl = props.generatedDocumentUrl ?? props.previewTemplateUrl;
    if (!sourceUrl) return;

    const isPdfDocument = sourceUrl.endsWith('/pdf');
    viewerUrl.value = isPdfDocument ? `${sourceUrl}?refresh=1` : sourceUrl;
    viewerTitle.value = documentTitle.value;
    viewerType.value = isPdfDocument ? 'pdf' : 'html';
    viewerOpen.value = true;
}

function openDownloadPdf() {
    const url = props.pdfUrl || `/documents/surat/${props.id}/pdf?refresh=1`;
    const link = document.createElement('a');
    link.href = url;
    link.download = `${documentTitle.value}.pdf`;
    link.rel = 'noopener';
    document.body.appendChild(link);
    link.click();
    link.remove();
}

function goBack() {
    router.visit(backHref.value);
}

function closeViewer() {
    viewerOpen.value = false;
    setTimeout(() => {
        viewerUrl.value = null;
    }, 200);
}

function approveSurat() {
    router.post(`${basePath.value}/surat/${props.id}/approve`, {}, { onSuccess: () => {} });
}

function openRevisionModal() {
    revisionModalOpen.value = true;
    revisionForm.reset();
}

function closeRevisionModal() {
    revisionModalOpen.value = false;
}

function submitRevision() {
    revisionForm.post(`${basePath.value}/surat/${props.id}/reject`, {
        onSuccess: () => closeRevisionModal(),
    });
}

function openFinalRejectModal() {
    finalRejectModalOpen.value = true;
    finalRejectForm.reset();
}

function closeFinalRejectModal() {
    finalRejectModalOpen.value = false;
}

function submitFinalReject() {
    finalRejectForm.post(`${basePath.value}/surat/${props.id}/final-reject`, {
        onSuccess: () => closeFinalRejectModal(),
    });
}

function attachmentUrl(id: number) {
    return `${basePath.value}/lampiran/${id}/preview`;
}

function downloadFile(url: string, filename: string) {
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    link.rel = 'noopener';
    document.body.appendChild(link);
    link.click();
    link.remove();
}

function openAttachmentPreview(file: Lampiran) {
    const url = file.url ?? attachmentUrl(file.id);

    if (isPdfAttachment(file) && url) {
        window.open(url, '_blank', 'noopener,noreferrer');
        return;
    }

    if (isWordAttachment(file) && url) {
        downloadFile(url, file.name || 'lampiran.docx');
        return;
    }

    activeAttachment.value = {
        ...file,
        url,
    };
    attachmentPreviewOpen.value = true;
}

function closeAttachmentPreview() {
    attachmentPreviewOpen.value = false;
    activeAttachment.value = null;
}

function isImageAttachment(file?: Lampiran | null) {
    if (!file) return false;
    return (
        (file.type ?? '').toLowerCase().startsWith('image/') ||
        ['.jpg', '.jpeg', '.png', '.gif', '.webp'].some((ext) =>
            file.name.toLowerCase().endsWith(ext),
        )
    );
}

function isPdfAttachment(file?: Lampiran | null) {
    if (!file) return false;
    return (
        (file.type ?? '').toLowerCase().includes('pdf') ||
        file.name.toLowerCase().endsWith('.pdf')
    );
}

function isWordAttachment(file?: Lampiran | null) {
    if (!file) return false;

    const type = (file.type ?? '').toLowerCase();
    const name = file.name.toLowerCase();

    return (
        type.includes('msword') ||
        type.includes('wordprocessingml.document') ||
        name.endsWith('.doc') ||
        name.endsWith('.docx')
    );
}

async function copyNomorSurat() {
    if (!props.nomor_surat) return;

    try {
        await navigator.clipboard.writeText(props.nomor_surat);
        copiedNumber.value = true;
        window.setTimeout(() => {
            copiedNumber.value = false;
        }, 1600);
    } catch {
        copiedNumber.value = false;
    }
}

function toggleTimelineNote(id: number) {
    expandedTimelineNoteId.value =
        expandedTimelineNoteId.value === id ? null : id;
}

function timelineStepState(index: number, timestamp?: string | null): 'done' | 'current' | 'pending' {
    const lastIndex = processTimeline.value.length - 1;

    if (index === lastIndex && !isFinished.value) {
        return 'current';
    }

    if (!timestamp) {
        return index === lastIndex ? 'current' : 'pending';
    }

    return 'done';
}

function timelineDotClasses(state: 'done' | 'current' | 'pending'): string {
    if (state === 'current') {
        return 'bg-amber-500 text-white shadow-sm ring-4 ring-amber-100';
    }

    if (state === 'pending') {
        return 'bg-slate-100 text-slate-400 ring-1 ring-slate-200';
    }

    return 'bg-blue-600 text-white shadow-sm ring-4 ring-blue-100';
}

function timelineCardClasses(state: 'done' | 'current' | 'pending'): string {
    if (state === 'current') {
        return 'border-amber-200 bg-amber-50/80';
    }

    if (state === 'pending') {
        return 'border-slate-200 bg-slate-50/80 opacity-80';
    }

    return 'border-slate-200 bg-slate-50';
}
</script>

<template>
    <AdminLayout
        :title="jenis_surat"
        subtitle=""
        title-class="text-lg font-bold tracking-tight text-slate-900 md:text-xl"
        :active-menu="activeMenu"
        :breadcrumbs="[
            { label: backLabel, href: backHref },
            { label: jenis_surat },
        ]"
    >
        <Head :title="jenis_surat" />

        <div class="mx-auto max-w-6xl space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <button
                    type="button"
                    class="fast-btn fast-btn-outline items-center gap-2 px-4 py-2 text-sm font-medium"
                    @click="goBack"
                >
                    <ArrowLeft class="size-4" />
                    Kembali
                </button>

            </div>

            <section class="grid gap-4 lg:grid-cols-[minmax(0,1.55fr)_minmax(320px,0.95fr)]">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_1px_2px_rgba(15,23,42,0.04)]">
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="grid size-10 place-items-center rounded-2xl bg-sky-50 text-sky-600 ring-1 ring-sky-100">
                                <FileText class="size-5" />
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-900">
                                    Data Surat
                                </h2>
                                <p class="text-sm text-slate-500">
                                    Ringkasan metadata surat.
                                </p>
                            </div>
                        </div>

                        <span
                            class="inline-flex shrink-0 rounded-full border px-3 py-1 text-xs font-semibold"
                            :class="statusColor[status] || 'border-slate-200 bg-white text-slate-700'"
                        >
                            {{ statusLabel[status] || status }}
                        </span>
                    </div>

                    <div class="mt-2 divide-y divide-slate-100 rounded-2xl border border-slate-200 bg-white">
                        <div
                            v-if="!isInstitutionLetter"
                            class="grid gap-2 px-4 py-3 text-sm md:grid-cols-[180px_minmax(0,1fr)] md:gap-4"
                        >
                            <p class="text-slate-500">{{ identityLabel }}</p>
                            <p class="min-w-0 break-words font-medium leading-6 text-slate-900">
                                {{ subjectName }}
                            </p>
                        </div>

                        <div
                            v-if="!isInstitutionLetter"
                            class="grid gap-2 px-4 py-3 text-sm md:grid-cols-[180px_minmax(0,1fr)] md:gap-4"
                        >
                            <p class="text-slate-500">{{ identityNumberLabel }}</p>
                            <p class="min-w-0 break-words font-mono font-medium leading-6 text-slate-900">
                                {{ subjectNim }}
                            </p>
                        </div>

                        <div class="grid gap-2 px-4 py-3 text-sm md:grid-cols-[180px_minmax(0,1fr)] md:gap-4">
                            <p class="text-slate-500">Jenis Surat</p>
                            <p class="min-w-0 break-words font-medium leading-6 text-slate-900">
                                {{ jenis_surat || '-' }}
                            </p>
                        </div>

                        <div class="grid gap-2 px-4 py-3 text-sm md:grid-cols-[180px_minmax(0,1fr)] md:gap-4">
                            <p class="text-slate-500">Tanggal Pengajuan</p>
                            <p class="min-w-0 break-words font-medium leading-6 text-slate-900">
                                {{ formatDate(tanggal_pengajuan) }}
                            </p>
                        </div>

                        <div class="grid gap-2 px-4 py-3 text-sm md:grid-cols-[180px_minmax(0,1fr)] md:gap-4">
                            <p class="text-slate-500">Nomor Surat</p>
                            <div class="min-w-0">
                                <button
                                    v-if="nomor_surat"
                                    type="button"
                                    class="inline-flex max-w-full items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-left text-sm text-slate-700 transition hover:border-slate-300 hover:bg-slate-100"
                                    @click="copyNomorSurat"
                                >
                                    <span class="min-w-0 break-words font-mono">
                                        {{ nomor_surat }}
                                    </span>
                                    <Copy class="size-3.5 shrink-0 text-slate-400" />
                                </button>
                                <p v-else class="font-medium leading-6 text-slate-900">
                                    -
                                </p>
                                <p v-if="copiedNumber" class="mt-1 text-xs text-emerald-600">
                                    Nomor surat disalin.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-2 px-4 py-3 text-sm md:grid-cols-[180px_minmax(0,1fr)] md:gap-4">
                            <p class="text-slate-500">Keperluan</p>
                            <p class="min-w-0 break-words font-medium leading-6 text-slate-900">
                                {{ keperluan || '-' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_1px_2px_rgba(15,23,42,0.04)]">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="grid size-10 place-items-center rounded-2xl bg-sky-50 text-sky-600 ring-1 ring-sky-100">
                            <Download class="size-5" />
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">
                                Dokumen
                            </h2>
                            <p class="text-sm text-slate-500">
                                Akses preview dan unduhan dokumen surat.
                            </p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4">
                        <div
                            class="flex items-start gap-3"
                        >
                            <ShieldCheck class="mt-0.5 size-5 shrink-0 text-emerald-600" />
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-emerald-800">
                                    {{
                                        isFinished
                                            ? 'Dokumen Siap'
                                            : 'Dokumen Belum Siap'
                                    }}
                                </p>
                                <p class="mt-1 text-xs leading-5 text-emerald-700">
                                    {{
                                        isFinished
                                            ? 'Surat sudah divalidasi. Dokumen PDF dapat dibuka dan diunduh.'
                                            : 'Surat masih diproses. Dokumen PDF belum dapat dibuka dan diunduh.'
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3">
                        <button
                            type="button"
                            :disabled="!isFinished"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50"
                            :class="
                                !isFinished
                                    ? 'cursor-not-allowed border-dashed bg-slate-50 text-slate-400 opacity-50 hover:bg-slate-50'
                                    : ''
                            "
                            @click="openPreviewDocument"
                        >
                            <Eye class="size-4 text-slate-500" />
                            Preview Dokumen
                        </button>

                        <button
                            v-if="canDownloadPdf"
                            type="button"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700"
                            @click="openDownloadPdf"
                        >
                            <Download class="size-4" />
                            Download PDF
                        </button>

                        <div
                            v-else
                            class="flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"
                        >
                            <Clock class="size-4 shrink-0" />
                            PDF belum siap diunduh.
                        </div>
                    </div>

                    <div
                        v-if="lampiran.length > 0"
                        class="mt-5"
                    >
                        <h3 class="mb-3 text-sm font-semibold text-slate-900">
                            Lampiran
                        </h3>
                        <div class="space-y-2">
                            <button
                                v-for="file in lampiran"
                                :key="file.id"
                                type="button"
                                class="fast-btn fast-btn-soft flex w-full items-center justify-between gap-3 px-4 py-3 text-left"
                                @click="openAttachmentPreview(file)"
                            >
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-slate-800">
                                        {{ file.name }}
                                    </p>
                                    <p class="text-xs text-slate-400">
                                        {{ file.type || 'File pendukung' }}
                                    </p>
                                </div>
                                <span class="text-xs font-semibold text-blue-600">
                                    Lihat lampiran
                                </span>
                            </button>
                        </div>
                    </div>

                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-4 shadow-[0_1px_2px_rgba(15,23,42,0.04)] sm:p-6">
                <div class="mb-5 flex items-center gap-3">
                    <div class="grid size-10 place-items-center rounded-2xl bg-sky-50 text-sky-600 ring-1 ring-sky-100">
                        <ShieldCheck class="size-5" />
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">
                            Riwayat Persetujuan
                        </h2>
                        <p class="text-sm text-slate-500">
                            Alur proses surat dari validasi sampai keputusan akhir.
                        </p>
                    </div>
                </div>

                <div v-if="processTimeline.length > 0" class="space-y-3 sm:space-y-4">
                    <div
                        v-for="(entry, index) in processTimeline"
                        :key="entry.id"
                        class="grid grid-cols-[24px_minmax(0,1fr)] gap-3 md:grid-cols-[24px_minmax(0,1fr)_190px] md:items-start md:gap-4"
                    >
                        <div class="relative flex items-start justify-center">
                            <span
                                v-if="index !== processTimeline.length - 1"
                                class="absolute left-1/2 top-6 h-full w-px -translate-x-1/2 bg-blue-200"
                            />
                            <span
                                class="relative z-10 mt-0.5 grid size-6 place-items-center rounded-full"
                                :class="timelineDotClasses(timelineStepState(index, entry.timestamp))"
                            >
                                <CheckCircle
                                    v-if="timelineStepState(index, entry.timestamp) === 'done'"
                                    class="size-3.5"
                                />
                                <Clock
                                    v-else-if="timelineStepState(index, entry.timestamp) === 'current'"
                                    class="size-3.5"
                                />
                                <span
                                    v-else
                                    class="size-2.5 rounded-full bg-current"
                                />
                            </span>
                        </div>

                        <div
                            class="min-w-0 rounded-2xl border p-4"
                            :class="timelineCardClasses(timelineStepState(index, entry.timestamp))"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <p class="break-words text-sm font-semibold text-slate-900">
                                    {{ entry.label }}
                                </p>
                                <button
                                    v-if="entry.note"
                                    type="button"
                                class="fast-btn fast-btn-danger shrink-0 px-2 py-1 text-xs"
                                    :aria-expanded="expandedTimelineNoteId === entry.id"
                                    :aria-controls="`timeline-note-${entry.id}`"
                                    @click="toggleTimelineNote(entry.id)"
                                >
                                    <AlertCircle class="size-3.5" />
                                    Catatan
                                </button>
                            </div>
                            <Transition name="fade">
                                <div
                                    v-if="entry.note && expandedTimelineNoteId === entry.id"
                                    :id="`timeline-note-${entry.id}`"
                                    class="mt-3 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm leading-6 text-red-700"
                                >
                                    {{ entry.note }}
                                </div>
                            </Transition>
                            <p class="mt-3 text-xs font-medium text-slate-500 md:hidden">
                                {{
                                    timelineStepState(index, entry.timestamp) === 'pending'
                                        ? 'Menunggu'
                                        : formatDate(entry.timestamp)
                                }}
                            </p>
                        </div>

                        <div class="hidden pt-1 text-xs text-slate-500 md:block md:text-right">
                            {{ formatDate(entry.timestamp) }}
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-sm text-slate-500"
                >
                    Riwayat belum tersedia.
                </div>
            </section>
        </div>

        <Transition name="fade">
            <div
                v-if="revisionModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
                @click.self="closeRevisionModal"
            >
                <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="grid size-10 shrink-0 place-items-center rounded-xl bg-amber-50">
                            <AlertTriangle class="size-5 text-amber-500" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">
                                Kembalikan untuk Revisi
                            </h3>
                            <p class="text-xs text-slate-400">
                                Berikan catatan agar admin tahu apa yang perlu diperbaiki pada surat ini.
                            </p>
                        </div>
                        <button
                            type="button"
                            class="ml-auto rounded-lg p-1 text-slate-400 hover:bg-slate-100"
                            @click="closeRevisionModal"
                        >
                            <X class="size-4" />
                        </button>
                    </div>

                    <textarea
                        v-model="revisionForm.reason"
                        rows="4"
                        placeholder="Jelaskan alasan revisi..."
                        class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100"
                    />

                    <p v-if="revisionForm.errors.reason" class="mt-1 text-xs text-red-500">
                        {{ revisionForm.errors.reason }}
                    </p>

                    <div class="mt-4 flex justify-end gap-2">
                        <button
                            type="button"
                            class="fast-btn fast-btn-outline rounded-xl px-4 py-2 text-sm font-medium"
                            @click="closeRevisionModal"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            class="fast-btn rounded-xl border border-amber-500 bg-amber-500 px-4 py-2 text-sm text-white hover:bg-amber-600"
                            :disabled="revisionForm.processing || !revisionForm.reason.trim()"
                            @click="submitRevision"
                        >
                            {{
                                revisionForm.processing
                                    ? 'Memproses...'
                                    : 'Kembalikan'
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <Transition name="fade">
            <div
                v-if="finalRejectModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
                @click.self="closeFinalRejectModal"
            >
                <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="grid size-10 shrink-0 place-items-center rounded-xl bg-red-50">
                            <AlertTriangle class="size-5 text-red-500" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">
                                Tolak Final
                            </h3>
                            <p class="text-xs text-slate-400">
                                Penolakan ini bersifat permanen dan akan menutup proses surat.
                            </p>
                        </div>
                        <button
                            type="button"
                            class="ml-auto rounded-lg p-1 text-slate-400 hover:bg-slate-100"
                            @click="closeFinalRejectModal"
                        >
                            <X class="size-4" />
                        </button>
                    </div>

                    <textarea
                        v-model="finalRejectForm.reason"
                        rows="4"
                        placeholder="Jelaskan alasan penolakan final..."
                        class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100"
                    />

                    <p v-if="finalRejectForm.errors.reason" class="mt-1 text-xs text-red-500">
                        {{ finalRejectForm.errors.reason }}
                    </p>

                    <div class="mt-4 flex justify-end gap-2">
                        <button
                            type="button"
                            class="fast-btn fast-btn-outline rounded-xl px-4 py-2 text-sm font-medium"
                            @click="closeFinalRejectModal"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            class="fast-btn fast-btn-danger rounded-xl px-4 py-2 text-sm"
                            :disabled="finalRejectForm.processing || !finalRejectForm.reason.trim()"
                            @click="submitFinalReject"
                        >
                            {{
                                finalRejectForm.processing
                                    ? 'Memproses...'
                                    : 'Tolak Final'
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <DocumentPreviewModal
            :open="viewerOpen"
            :mode="viewerType"
            :title="viewerTitle"
            :url="viewerUrl"
            :show-html-zoom-controls="true"
            :show-thumbnails="false"
            :initial-zoom="100"
            @close="closeViewer"
        />

        <Dialog
            :open="attachmentPreviewOpen"
            @update:open="(v) => (v ? null : closeAttachmentPreview())"
        >
            <DialogContent
                class="flex max-h-[90vh] w-[min(860px,calc(100vw-2rem))] flex-col overflow-hidden rounded-2xl border-0 bg-white p-0"
                :show-close-button="false"
            >
                <div class="border-b border-slate-100 px-6 py-4">
                    <DialogHeader class="text-left">
                        <DialogTitle class="text-lg font-semibold text-slate-900">
                            Preview Lampiran
                        </DialogTitle>
                        <DialogDescription class="text-sm text-slate-400">
                            {{ activeAttachment?.name }}
                        </DialogDescription>
                    </DialogHeader>
                </div>
                <div class="min-h-0 flex-1 overflow-y-auto bg-slate-50 p-4">
                    <div
                        v-if="activeAttachment && isImageAttachment(activeAttachment)"
                        class="flex justify-center"
                    >
                        <img
                            :src="activeAttachment.url"
                            :alt="activeAttachment.name"
                            class="max-h-[65vh] rounded-xl border border-slate-200 object-contain shadow-sm"
                        />
                    </div>
                    <div
                        v-else-if="activeAttachment && isPdfAttachment(activeAttachment)"
                        class="overflow-hidden rounded-xl border border-slate-200 shadow-sm"
                    >
                        <iframe
                            :src="activeAttachment.url"
                            class="h-[65vh] w-full"
                            title="Preview PDF"
                        />
                    </div>
                    <div
                        v-else-if="activeAttachment?.url"
                        class="overflow-hidden rounded-xl border border-slate-200 shadow-sm"
                    >
                        <iframe
                            :src="activeAttachment.url"
                            class="h-[65vh] w-full"
                            title="Preview Lampiran"
                        />
                    </div>
                    <div
                        v-else
                        class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-500"
                    >
                        Preview lampiran tidak tersedia.
                    </div>
                </div>
                <div class="flex justify-end border-t border-slate-100 px-6 py-4">
                    <Button variant="ghost" @click="closeAttachmentPreview">Tutup</Button>
                </div>
            </DialogContent>
        </Dialog>

        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-3 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-3 opacity-0"
        >
            <div
                v-if="toastMessage"
                class="fixed top-5 left-1/2 z-50 w-[calc(100%-2rem)] max-w-sm -translate-x-1/2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-blue-800 shadow-lg"
            >
                <div class="flex items-center gap-2.5">
                    <CheckCircle class="size-5 shrink-0 text-blue-500" />
                    <p class="text-sm font-medium">{{ toastMessage }}</p>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>
