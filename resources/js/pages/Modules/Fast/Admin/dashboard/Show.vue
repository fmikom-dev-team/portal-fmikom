<script setup lang="ts">
import AdminLayout from '@/layouts/Modules/Fast/AdminLayout.vue';
import DocumentPreviewModal from '@/components/DocumentPreviewModal.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useFastPermissions } from '@/composables/modules/fast/useFastPermissions';
import {
    AlertCircle,
    ArrowLeft,
    CheckCircle,
    Clock,
    Copy,
    Download,
    Eye,
    FileEdit,
    FileText,
    Paperclip,
    QrCode,
    ShieldCheck,
    X,
} from 'lucide-vue-next';

type Lampiran = { id: number; name: string; url: string; type: string };

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

type Surat = {
    id: number;
    type: string;
    nomor_surat?: string | null;
    letter_mode?: string | null;
    letter_mode_label?: string | null;
    is_institution?: boolean;
    subject?: { name: string; nim?: string | null };
    jenis_surat: string;
    keperluan: string;
    isi_surat: Record<string, any>;
    detail_data?: Record<string, any>;
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
    can_approve: boolean;
    can_edit: boolean;
    previewTemplateUrl: string | null;
    generatedDocumentUrl: string | null;
};

const props = defineProps<{ id: number } & Surat>();
const { can } = useFastPermissions();
const isInstitutionLetter = computed(
    () => !!props.is_institution || props.letter_mode === 'institution',
);
const subjectLabel = computed(() =>
    props.type === 'surat_keluar' ? 'Atas Nama' : 'Pemohon',
);
const subjectIdentityLabel = computed(() =>
    props.type === 'surat_keluar' ? 'Nomor Induk' : 'NIM / NIP',
);

const viewerOpen = ref(false);
const viewerUrl = ref<string | null>(null);
const viewerTitle = ref('');
const viewerType = ref<'html' | 'pdf'>('html');
const copiedNumber = ref(false);
const expandedTimelineNoteId = ref<number | null>(null);
const attachmentPreviewOpen = ref(false);
const activeAttachment = ref<Lampiran | null>(null);

const isFinished = computed(() => props.status === 'finished');
const documentTitle = computed(() =>
    props.nomor_surat
        ? `${props.jenis_surat} - ${props.nomor_surat}`
        : props.jenis_surat,
);

const completedAt = computed(() => {
    if (!isFinished.value) return null;

    const historyLatest = props.history_timeline?.[0]?.created_at ?? null;
    const approvalLatest =
        props.approval_timeline?.[props.approval_timeline.length - 1]
            ?.acted_at ?? null;

    return historyLatest ?? approvalLatest ?? props.tanggal_pengajuan;
});

const hiddenFields = new Set([
    'created_by',
    'jenis_surat_id',
    'jenis_surat',
    'keperluan',
]);

const detailSource = computed<Record<string, unknown>>(() => {
    const candidate = props.detail_data ?? props.isi_surat ?? {};

    if (Array.isArray(candidate)) {
        return {};
    }

    if (candidate && typeof candidate === 'object') {
        return candidate as Record<string, unknown>;
    }

    return {};
});

const detailRows = computed(() =>
    Object.entries(detailSource.value)
        .filter(([key]) => !hiddenFields.has(String(key)))
        .map(([key, value]) => ({
            key: String(key),
            label: key
                .replace(/_/g, ' ')
                .replace(/\b\w/g, (char) => char.toUpperCase()),
            value: formatDisplayValue(value),
        }))
        .filter((row) => row.value !== '-'),
);

const statusLabel: Record<string, string> = {
    pending: 'Menunggu Validasi',
    revision_requested: 'Menunggu Revisi Admin',
    validated_admin: 'Diteruskan untuk disetujui',
    approved_kaprodi: 'Disetujui Kaprodi',
    approved_dekan: 'Disetujui Dekan',
    finished: 'Selesai',
    rejected_admin: 'Ditolak Admin',
    rejected_approver: props.latest_rejection?.label ?? 'Ditolak Final',
};

const statusColor: Record<string, string> = {
    pending: 'bg-amber-50 text-amber-700 border-amber-200',
    revision_requested: 'bg-amber-50 text-amber-700 border-amber-200',
    validated_admin: 'bg-amber-50 text-amber-700 border-amber-200',
    approved_kaprodi: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    approved_dekan: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    finished: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    rejected_admin: 'bg-red-50 text-red-700 border-red-200',
    rejected_approver: 'bg-red-50 text-red-700 border-red-200',
};

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

function formatDateTime(iso: string | null): string {
    return formatDate(iso);
}

function formatDateOnly(iso: string | null): string {
    if (!iso) return '-';

    return new Date(iso).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}

function timelineBadgeClass(status?: string | null, action?: string | null): string {
    if (
        status === 'rejected_final' ||
        status === 'revision_requested' ||
        action === 'rejected' ||
        action === 'revised'
    ) {
        return 'border-red-200 bg-red-50 text-red-700';
    }

    if (status === 'approved' || action === 'approved' || action === 'validated') {
        return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    }

    if (status === 'note') {
        return 'border-slate-200 bg-slate-50 text-slate-700';
    }

    return 'border-amber-200 bg-amber-50 text-amber-700';
}

function openPreviewDocument() {
    const sourceUrl = props.generatedDocumentUrl;
    if (!sourceUrl) return;

    viewerUrl.value = sourceUrl;
    viewerTitle.value = documentTitle.value;
    viewerType.value = 'pdf';
    viewerOpen.value = true;
}

function openDownloadPdf() {
    const url = `/admin/surat/${props.id}/pdf`;
    const link = document.createElement('a');
    link.href = url;
    link.download = `${documentTitle.value}.pdf`;
    link.rel = 'noopener';
    document.body.appendChild(link);
    link.click();
    link.remove();
}

function openInNewTab() {
    if (viewerUrl.value) {
        window.open(viewerUrl.value, '_blank', 'noopener,noreferrer');
    }
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

function attachmentUrl(id: number) {
    return `/documents/public/lampiran/${id}/preview`;
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

function goBack() {
    if (window.history.length > 1) {
        window.history.back();
        return;
    }

    router.visit('/admin/archive');
}

function closeViewer() {
    viewerOpen.value = false;
    setTimeout(() => {
        viewerUrl.value = null;
    }, 200);
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
        active-menu="letters"
        :breadcrumbs="[
            { label: 'Arsip', href: '/admin/archive' },
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

            <section class="grid gap-4 lg:grid-cols-[minmax(0,1.55fr)_minmax(320px,0.9fr)]">
                <div class="space-y-4">
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
                                    Detail isi dan informasi yang tercantum pada surat.
                                </p>
                            </div>
                        </div>

                        <span
                            class="inline-flex shrink-0 items-center rounded-full border px-3 py-1 text-xs font-semibold"
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
                            <p class="text-slate-500">{{ subjectLabel }}</p>
                            <p class="min-w-0 break-words font-medium leading-6 text-slate-900">
                                {{ subject?.name || '-' }}
                            </p>
                        </div>

                        <div
                            v-if="!isInstitutionLetter"
                            class="grid gap-2 px-4 py-3 text-sm md:grid-cols-[180px_minmax(0,1fr)] md:gap-4"
                        >
                            <p class="text-slate-500">{{ subjectIdentityLabel }}</p>
                            <p class="min-w-0 break-words font-mono font-medium leading-6 text-slate-900">
                                {{ subject?.nim || '-' }}
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

                        <div
                            v-if="detailRows.length"
                            class="border-t border-slate-100 bg-slate-50/60"
                        >
                            <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-400">
                                        Data tambahan
                                    </p>
                                    <p class="mt-1 text-sm font-semibold text-slate-900">
                                        Informasi pendukung surat
                                    </p>
                                </div>
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-500">
                                    {{ detailRows.length }} data
                                </span>
                            </div>

                            <div class="divide-y divide-slate-200">
                                <div
                                    v-for="field in detailRows"
                                    :key="field.key"
                                    class="grid gap-2 px-4 py-3 text-sm md:grid-cols-[180px_minmax(0,1fr)] md:gap-4"
                                >
                                    <p class="text-slate-500">{{ field.label }}</p>
                                    <p class="min-w-0 break-words font-medium leading-6 text-slate-900">
                                        {{ field.value }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_1px_2px_rgba(15,23,42,0.04)]">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="grid size-10 place-items-center rounded-2xl bg-sky-50 text-sky-600 ring-1 ring-sky-100">
                            <ShieldCheck class="size-5" />
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">
                                Riwayat Persetujuan
                            </h2>
                            <p class="text-sm text-slate-500">
                                Alur proses surat dari validasi sampai selesai.
                            </p>
                        </div>
                    </div>

                    <div v-if="processTimeline.length > 0" class="space-y-3 sm:space-y-4">
                        <div
                            v-for="(entry, index) in processTimeline"
                            :key="entry.id"
                            class="grid grid-cols-[24px_minmax(0,1fr)] gap-3 sm:gap-4"
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
                                    <div class="min-w-0 flex-1">
                                        <p class="break-words text-sm font-semibold leading-6 text-slate-900">
                                            {{ entry.label }}
                                        </p>
                                        <p
                                            v-if="entry.role"
                                            class="mt-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400"
                                        >
                                            {{ entry.role }}
                                        </p>
                                        <p
                                            v-if="entry.actor"
                                            class="mt-1 text-xs font-medium text-slate-500"
                                        >
                                            {{ entry.actor }}
                                        </p>
                                    </div>
                                    <div class="flex shrink-0 flex-col items-end gap-2 text-right">
                                        <p class="text-xs font-medium text-slate-500">
                                            {{ formatDateTime(entry.timestamp) }}
                                        </p>
                                        <button
                                            v-if="entry.note"
                                            type="button"
                                            class="fast-btn fast-btn-danger shrink-0 px-2.5 py-1 text-xs"
                                            :aria-expanded="expandedTimelineNoteId === entry.id"
                                            :aria-controls="`timeline-note-${entry.id}`"
                                            @click="toggleTimelineNote(entry.id)"
                                        >
                                            <AlertCircle class="size-3.5" />
                                            Catatan
                                        </button>
                                    </div>
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

                <div class="self-start rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_1px_2px_rgba(15,23,42,0.04)]">
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

                    <div
                        class="rounded-2xl border px-4 py-4"
                        :class="
                                isFinished
                                    ? 'border-emerald-200 bg-emerald-50'
                                : 'border-amber-200 bg-amber-50'
                        "
                    >
                        <div class="flex items-start gap-3">
                            <QrCode
                                class="mt-0.5 size-5 shrink-0"
                                :class="
                                        isFinished
                                            ? 'text-blue-600'
                                            : 'text-amber-600'
                                "
                            />
                            <div>
                                <p
                                    class="text-sm font-semibold"
                                    :class="
                                            isFinished
                                                ? 'text-blue-800'
                                                : 'text-amber-800'
                                    "
                                >
                                    {{
                                        isFinished
                                            ? 'QR Code Aktif'
                                            : 'QR Code Belum Aktif'
                                    }}
                                </p>
                                <p
                                    class="mt-1 text-xs leading-5"
                                    :class="
                                            isFinished
                                                ? 'text-blue-700'
                                                : 'text-amber-700'
                                    "
                                >
                                    {{
                                        isFinished
                                            ? 'Surat sudah divalidasi. QR Code dapat dipindai untuk verifikasi.'
                                            : 'QR Code akan aktif setelah surat selesai divalidasi dan disetujui.'
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-if="can_edit && can('fast.admin.surat.update')" class="mt-4">
                        <Link
                            :href="`/admin/surat/${id}/edit?return_to=/admin/dashboard`"
                            class="fast-btn fast-btn-soft w-full px-4 py-2.5 text-sm font-semibold text-sky-700"
                        >
                            <FileEdit class="size-4" />
                            {{ status === 'pending' ? 'Lengkapi Data' : 'Edit & Teruskan' }}
                        </Link>
                    </div>

                    <div class="mt-4 space-y-3">
                        <button
                            v-if="can('fast.document.preview')"
                            type="button"
                            :disabled="!isFinished"
                            class="fast-btn fast-btn-outline w-full px-4 py-2.5 text-sm font-semibold transition"
                            :class="
                                isFinished
                                    ? ''
                                    : 'cursor-not-allowed border-dashed border-slate-200 bg-slate-50 text-slate-400 opacity-50 hover:bg-slate-50'
                            "
                            @click="openPreviewDocument"
                        >
                            <Eye class="size-4" />
                            Preview Dokumen
                        </button>

                        <button
                            v-if="isFinished && can('fast.document.download')"
                            type="button"
                            class="fast-btn fast-btn-primary w-full px-4 py-2.5 text-sm"
                            @click="openDownloadPdf"
                        >
                            <Download class="size-4" />
                            Unduh PDF
                        </button>

                        <template v-else-if="can('fast.document.download')" />

                        <div
                            v-else
                            class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500"
                        >
                            <Clock class="size-4 shrink-0" />
                            Anda tidak memiliki akses untuk mengunduh dokumen.
                        </div>
                    </div>

                    <div
                        v-if="lampiran.length > 0"
                        class="mt-4 border-t border-slate-100 pt-4"
                    >
                        <div class="mb-3 flex items-center gap-2">
                            <Paperclip class="size-4 text-slate-400" />
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-400">
                                Lampiran Pengajuan
                            </p>
                        </div>
                        <div class="space-y-2">
                            <div
                                v-for="file in lampiran"
                                :key="file.id"
                                class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3"
                            >
                                <div class="flex flex-col gap-2">
                                    <div class="min-w-0">
                                        <p class="break-words text-sm font-semibold leading-5 text-slate-900">
                                            {{ file.name }}
                                        </p>
                                    </div>
                                    <button
                                        v-if="file.url && can('fast.document.preview')"
                                        type="button"
                                        class="self-end inline-flex items-center text-sm font-medium text-blue-600 transition hover:text-blue-700"
                                        @click="openAttachmentPreview(file)"
                                    >
                                        Lihat lampiran
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </section>

        </div>
        <DocumentPreviewModal
            :open="viewerOpen"
            :mode="viewerType"
            :title="viewerTitle"
            :url="viewerUrl"
            :show-open-in-new-tab="true"
            :show-html-zoom-controls="true"
            :show-thumbnails="false"
            :initial-zoom="100"
            @close="closeViewer"
            @open-new-tab="openInNewTab"
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
                <DialogFooter class="border-t border-slate-100 px-6 py-4">
                    <Button variant="ghost" @click="closeAttachmentPreview">Tutup</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
