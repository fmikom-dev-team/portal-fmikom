<script setup lang="ts">
import FastLayout from '@/layouts/Modules/Fast/FastLayout.vue';
import DocumentPreviewModal from '@/components/DocumentPreviewModal.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    ArrowLeft,
    CheckCircle2,
    Clock3,
    Copy,
    Download,
    Eye,
    FileText,
    Paperclip,
    ShieldAlert,
    X,
} from 'lucide-vue-next';

type Lampiran = {
    id: number;
    name: string;
    url?: string | null;
    type?: string | null;
};

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

type SuratDetail = {
    id: number;
    pemohon?: {
        name?: string | null;
        nim_nip?: string | null;
        nomor_induk?: string | null;
    } | null;
    nomor_surat?: string | null;
    nomor_surat_status?: string | null;
    nomor_surat_status_label?: string | null;
    reference?: string | null;
    jenis_surat: string;
    approval_role_slug?: string | null;
    keperluan: string;
    isi_surat?: Record<string, unknown> | unknown[];
    detail_data?: Record<string, unknown>;
    lampiran: Lampiran[];
    tanggal_pengajuan?: string | null;
    tanggal_kebutuhan?: string | null;
    tanggal_selesai?: string | null;
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
    previewTemplateUrl?: string | null;
    generatedDocumentUrl?: string | null;
    pdfUrl?: string | null;
    canDownloadPdf?: boolean;
};

const props = defineProps<{
    userType?: {
        value?: string | null;
        label?: string | null;
    };
    back_href?: string;
    back_label?: string;
    surat: SuratDetail;
}>();

const viewerOpen = ref(false);
const viewerUrl = ref<string | null>(null);
const viewerTitle = ref('');
const viewerType = ref<'html' | 'pdf'>('html');
const copiedNumber = ref(false);
const expandedTimelineNoteId = ref<number | null>(null);

const backHref = computed(() => props.back_href || '/mahasiswa/history');
const backLabel = computed(() => props.back_label || 'Riwayat Surat');
const surat = computed(() => props.surat);
const detailSource = computed<Record<string, unknown>>(() => {
    const candidate = surat.value.detail_data ?? surat.value.isi_surat ?? {};

    if (Array.isArray(candidate)) {
        return {};
    }

    if (candidate && typeof candidate === 'object') {
        return candidate as Record<string, unknown>;
    }

    return {};
});
const documentTitle = computed(() =>
    surat.value.nomor_surat
        ? `${surat.value.jenis_surat} - ${surat.value.nomor_surat}`
        : surat.value.jenis_surat,
);
const pemohonName = computed(() => surat.value.pemohon?.name || '-');
const pemohonNumber = computed(
    () => surat.value.pemohon?.nim_nip || surat.value.pemohon?.nomor_induk || '-',
);
const attachmentCount = computed(() => surat.value.lampiran?.length ?? 0);
const importantDetailEntries = computed(() => detailEntries.value.slice(0, 8));
const extraDetailEntries = computed(() =>
    detailEntries.value
        .filter(({ key }) => !isCoreDetailKey(key))
        .slice(0, 6),
);
const timeline = computed(() => {
    const approval = surat.value.approval_timeline ?? [];
    if (approval.length > 0) {
        return approval.map((entry) => ({
            id: entry.id,
            label: entry.label,
            note: isTimelineNoteVisible(entry.status, entry.action)
                ? (entry.note ?? entry.description ?? null)
                : null,
            timestamp: entry.acted_at ?? null,
            status: entry.status ?? null,
            action: entry.action ?? null,
            role: entry.role ?? null,
            actor: entry.actor ?? null,
        }));
    }

    return (surat.value.history_timeline ?? []).map((entry) => ({
        id: entry.id,
        label: entry.label,
        note: isTimelineNoteVisible(entry.status, entry.action)
            ? (entry.note ?? entry.description ?? null)
            : null,
        timestamp: entry.created_at ?? null,
        status: entry.status ?? null,
        action: entry.action ?? null,
        role: entry.role ?? null,
        actor: entry.actor ?? null,
    }));
});
const detailEntries = computed(() =>
    Object.entries(detailSource.value)
        .filter(([, value]) => value !== null && value !== undefined && value !== '')
        .filter(([key]) => !isTechnicalDetailKey(key))
        .map(([key, value]) => ({
            key,
            label: key
                .replace(/_/g, ' ')
                .replace(/\b\w/g, (char) => char.toUpperCase()),
            value: formatValue(value),
        })),
);
const nomorSuratStatusLabel = computed(() => surat.value.nomor_surat_status_label || null);
const nomorSuratStatusClass = computed(() => {
    if (surat.value.nomor_surat_status === 'void') {
        return 'border-red-200 bg-red-50 text-red-700';
    }

    if (surat.value.nomor_surat_status === 'issued') {
        return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    }

    if (surat.value.nomor_surat_status === 'reserved') {
        return 'border-amber-200 bg-amber-50 text-amber-700';
    }

    return 'border-slate-200 bg-slate-50 text-slate-600';
});

function toggleTimelineNote(id: number) {
    expandedTimelineNoteId.value =
        expandedTimelineNoteId.value === id ? null : id;
}

function timelineStepState(index: number, timestamp?: string | null): 'done' | 'current' | 'pending' {
    const lastIndex = timeline.value.length - 1;

    if (index === lastIndex && surat.value.status !== 'finished') {
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

function isTimelineNoteVisible(status?: string | null, action?: string | null): boolean {
    const loweredStatus = (status ?? '').toLowerCase();
    const loweredAction = (action ?? '').toLowerCase();

    return (
        loweredStatus.includes('reject') ||
        loweredStatus.includes('revision') ||
        loweredAction.includes('reject') ||
        loweredAction.includes('revision') ||
        loweredAction === 'rejected' ||
        loweredAction === 'revised'
    );
}

const statusMap: Record<string, string> = {
    pending: 'Menunggu Validasi',
    validated_admin: 'Diteruskan ke Approver',
    revision_requested: 'Perlu Revisi',
    approved_kaprodi: 'Disetujui Kaprodi',
    approved_dekan: 'Disetujui Dekan',
    finished: 'Selesai',
    rejected_admin: 'Ditolak Admin',
    rejected_approver: 'Ditolak Pimpinan',
    cancelled: 'Dibatalkan',
};

const statusClassMap: Record<string, string> = {
    pending: 'bg-amber-50 text-amber-700 border-amber-200',
    validated_admin: 'bg-slate-100 text-slate-700 border-slate-200',
    revision_requested: 'bg-amber-50 text-amber-700 border-amber-200',
    approved_kaprodi: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    approved_dekan: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    finished: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    rejected_admin: 'bg-red-50 text-red-700 border-red-200',
    rejected_approver: 'bg-red-50 text-red-700 border-red-200',
    cancelled: 'bg-slate-100 text-slate-600 border-slate-200',
};

function formatValue(value: unknown): string {
    if (value === null || value === undefined || value === '') return '-';
    if (Array.isArray(value)) {
        const items = value.map((item) => formatValue(item)).filter((item) => item !== '-');
        return items.length > 0 ? items.join(', ') : '-';
    }
    if (typeof value === 'object') {
        const candidate = value as Record<string, unknown>;
        return String(candidate.name ?? candidate.label ?? candidate.value ?? '-') || '-';
    }
    if (typeof value === 'boolean') {
        return value ? 'Ya' : 'Tidak';
    }
    return String(value);
}

function isTechnicalDetailKey(key: string): boolean {
    const normalized = key.toLowerCase();
    const technical = new Set([
        'id',
        'surat_id',
        'jenis_surat_id',
        'pemohon_id',
        'type',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
        'generated_at',
        'generated_file_path',
        'generated_file_type',
        'rendered_snapshot',
        'template_version',
        'qr_token',
        'qr_validated_at',
        'validated_by_admin_id',
        'validated_by_admin_at',
        'approved_by_id',
        'approved_at',
        'file_path',
        'path',
        'url',
        'token',
        'slug',
        'nama_file',
        'nama_asli',
        'mime_type',
        'field_name',
        'field_value',
        'approval_role',
        'approval_role_id',
        'meta',
        'metadata',
        'catatan_revisi',
        'rejection_reason',
        'admin_note',
    ]);

    return (
        technical.has(normalized) ||
        normalized.startsWith('_') ||
        normalized === 'id' ||
        normalized.endsWith('_id') ||
        normalized.includes('created_at') ||
        normalized.includes('updated_at') ||
        normalized === 'status' ||
        normalized.includes('token') ||
        normalized.includes('path') ||
        normalized.includes('url') ||
        normalized.includes('file')
    );
}

function isCoreDetailKey(key: string): boolean {
    const normalized = key.toLowerCase();

    return [
        'nama',
        'name',
        'nama_lengkap',
        'nim',
        'nip',
        'nim_nip',
        'nomor_induk',
        'jenis_surat',
        'keperluan',
        'tanggal_pengajuan',
        'tanggal_kebutuhan',
    ].includes(normalized);
}

function formatDate(iso?: string | null): string {
    if (!iso) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    }).format(new Date(iso));
}

function formatDateTime(iso?: string | null): string {
    if (!iso) return '-';
    return (
        new Intl.DateTimeFormat('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }).format(new Date(iso)) + ' WIB'
    );
}

function statusLabel(status: string) {
    return statusMap[status] ?? 'Diproses';
}

function statusClass(status: string) {
    return statusClassMap[status] ?? 'bg-slate-50 text-slate-700 border-slate-200';
}

function openPreview() {
    const source = surat.value.generatedDocumentUrl || surat.value.previewTemplateUrl;
    if (!source) return;

    viewerOpen.value = true;
    viewerType.value = source.endsWith('/pdf') ? 'pdf' : 'html';
    viewerUrl.value = source;
    viewerTitle.value = documentTitle.value;
}

function openPdf() {
    if (!surat.value.pdfUrl) return;

    viewerOpen.value = true;
    viewerType.value = 'pdf';
    viewerUrl.value = surat.value.pdfUrl;
    viewerTitle.value = documentTitle.value;
}

function closeViewer() {
    viewerOpen.value = false;
    window.setTimeout(() => {
        viewerUrl.value = null;
    }, 200);
}

async function copyNomor() {
    if (!surat.value.nomor_surat) return;

    try {
        await navigator.clipboard.writeText(surat.value.nomor_surat);
        copiedNumber.value = true;
        window.setTimeout(() => {
            copiedNumber.value = false;
        }, 1400);
    } catch {
        copiedNumber.value = false;
    }
}
</script>

<template>
    <FastLayout
        title="Detail Surat"
        :subtitle="surat.jenis_surat"
        active-menu="history"
        :breadcrumbs="[
            { label: 'Dashboard', href: backHref.replace('/history', '/dashboard') },
            { label: backLabel, href: backHref },
            { label: 'Detail Surat' },
        ]"
    >
        <Head :title="`Detail Surat - ${surat.jenis_surat}`" />

        <div class="mx-auto max-w-6xl space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <Link
                    :href="backHref"
                    class="fast-btn fast-btn-outline items-center gap-2 px-4 py-2 text-sm font-medium"
                >
                    <ArrowLeft class="size-4" />
                    Kembali
                </Link>

                <span
                    class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold"
                    :class="statusClass(surat.status)"
                >
                    {{ statusLabel(surat.status) }}
                </span>
            </div>

            <section class="grid gap-4 lg:grid-cols-[minmax(0,1.55fr)_minmax(320px,0.9fr)]">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_1px_2px_rgba(15,23,42,0.04)]">
                    <div class="mb-4 flex items-center gap-3">
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

                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                        <div class="grid gap-2 border-b border-slate-100 px-4 py-3 text-sm md:grid-cols-[180px_minmax(0,1fr)] md:gap-4">
                            <p class="text-slate-500">Nama</p>
                            <p class="min-w-0 break-words font-medium leading-6 text-slate-900">
                                {{ pemohonName }}
                            </p>
                        </div>

                        <div class="grid gap-2 border-b border-slate-100 px-4 py-3 text-sm md:grid-cols-[180px_minmax(0,1fr)] md:gap-4">
                            <p class="text-slate-500">NIM / NIP</p>
                            <p class="min-w-0 break-words font-mono font-medium leading-6 text-slate-900">
                                {{ pemohonNumber }}
                            </p>
                        </div>

                        <div class="grid gap-2 border-b border-slate-100 px-4 py-3 text-sm md:grid-cols-[180px_minmax(0,1fr)] md:gap-4">
                            <p class="text-slate-500">Jenis Surat</p>
                            <p class="min-w-0 break-words font-medium leading-6 text-slate-900">
                                {{ surat.jenis_surat || '-' }}
                            </p>
                        </div>

                        <div class="grid gap-2 border-b border-slate-100 px-4 py-3 text-sm md:grid-cols-[180px_minmax(0,1fr)] md:gap-4">
                            <p class="text-slate-500">Tanggal Pengajuan</p>
                            <p class="min-w-0 break-words font-medium leading-6 text-slate-900">
                                {{ formatDateTime(surat.tanggal_pengajuan) }}
                            </p>
                        </div>

                        <div class="grid gap-2 border-b border-slate-100 px-4 py-3 text-sm md:grid-cols-[180px_minmax(0,1fr)] md:gap-4">
                            <p class="text-slate-500">Nomor Surat</p>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <button
                                        v-if="surat.nomor_surat || surat.reference"
                                        type="button"
                                        class="inline-flex max-w-full items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-left font-medium text-slate-900 transition hover:border-slate-300 hover:bg-slate-100"
                                        @click="copyNomor"
                                    >
                                        <span class="min-w-0 break-words font-mono">
                                            {{ surat.nomor_surat || surat.reference || '-' }}
                                        </span>
                                        <Copy class="size-3.5 shrink-0 text-slate-400" />
                                    </button>
                                    <span
                                        v-if="nomorSuratStatusLabel"
                                        class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                                        :class="nomorSuratStatusClass"
                                    >
                                        {{ nomorSuratStatusLabel }}
                                    </span>
                                </div>
                                <p v-if="!surat.nomor_surat && !surat.reference" class="font-medium leading-6 text-slate-900">
                                    -
                                </p>
                                <p v-if="copiedNumber" class="mt-1 text-xs text-emerald-600">
                                    Nomor surat disalin.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-2 border-b border-slate-100 px-4 py-3 text-sm md:grid-cols-[180px_minmax(0,1fr)] md:gap-4">
                            <p class="text-slate-500">Keperluan</p>
                            <p class="min-w-0 break-words font-medium leading-6 text-slate-900">
                                {{ surat.keperluan || '-' }}
                            </p>
                        </div>

                        <div
                            v-if="extraDetailEntries.length"
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
                                    {{ extraDetailEntries.length }} data
                                </span>
                            </div>

                            <div class="divide-y divide-slate-200">
                                <div
                                    v-for="field in extraDetailEntries"
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

                <aside class="space-y-4">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_1px_2px_rgba(15,23,42,0.04)]">
                        <div class="flex items-center gap-3">
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
                            class="mt-4 rounded-2xl border px-4 py-4"
                            :class="
                                surat.status === 'finished'
                                    ? 'border-emerald-200 bg-emerald-50'
                                    : 'border-amber-200 bg-amber-50'
                            "
                        >
                            <div class="flex items-start gap-3">
                                <ShieldAlert
                                    class="mt-0.5 size-5 shrink-0"
                                    :class="
                                        surat.status === 'finished'
                                            ? 'text-emerald-600'
                                            : 'text-amber-600'
                                    "
                                />
                                <div>
                                    <p
                                        class="text-sm font-semibold"
                                        :class="
                                            surat.status === 'finished'
                                                ? 'text-emerald-800'
                                                : 'text-amber-800'
                                        "
                                    >
                                        {{
                                            surat.status === 'finished'
                                                ? 'Dokumen Siap'
                                                : 'Dokumen Belum Aktif'
                                        }}
                                    </p>
                                    <p
                                        class="mt-1 text-sm leading-6"
                                        :class="
                                            surat.status === 'finished'
                                                ? 'text-emerald-700'
                                                : 'text-amber-700'
                                        "
                                    >
                                        {{
                                            surat.status === 'finished'
                                                ? 'Surat sudah divalidasi. Dokumen PDF dapat dibuka dan diunduh.'
                                                : 'Dokumen akan aktif setelah surat selesai diproses.'
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 space-y-3">
                        <button
                            type="button"
                            class="fast-btn fast-btn-outline w-full px-4 py-2.5 text-sm"
                            @click="openPreview"
                        >
                            <Eye class="size-4" />
                            Preview Dokumen
                            </button>

                        <button
                            v-if="surat.canDownloadPdf"
                            type="button"
                            class="fast-btn fast-btn-primary w-full px-4 py-2.5 text-sm"
                            @click="openPdf"
                        >
                            <Download class="size-4" />
                            Download PDF
                            </button>

                            <div
                                v-else
                                class="flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"
                            >
                                <Clock3 class="size-4 shrink-0" />
                                Dokumen belum tersedia - surat belum selesai diproses.
                            </div>
                        </div>

                        <div v-if="surat.lampiran?.length" class="mt-4 border-t border-slate-100 pt-4">
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-400">
                                Lampiran
                            </p>
                            <div class="mt-3 space-y-2">
                                <div
                                    v-for="attachment in surat.lampiran"
                                    :key="attachment.id"
                                    class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3"
                                >
                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ attachment.name }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ attachment.type || 'Lampiran' }}
                                    </p>
                                    <a
                                        v-if="attachment.url"
                                        :href="attachment.url"
                                        target="_blank"
                                        rel="noopener"
                                        class="mt-3 inline-flex items-center gap-2 text-sm font-medium text-blue-600 transition hover:text-blue-700"
                                    >
                                        Lihat lampiran
                                        <ArrowLeft class="size-4 rotate-180" />
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-4 shadow-[0_1px_2px_rgba(15,23,42,0.04)] sm:p-6">
                <div class="flex items-center gap-3">
                    <div class="grid size-10 place-items-center rounded-2xl bg-sky-50 text-sky-600 ring-1 ring-sky-100">
                        <CheckCircle2 class="size-5" />
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">
                            Riwayat Persetujuan
                        </h2>
                        <p class="text-sm text-slate-500">
                            Alur proses surat dari pengajuan sampai selesai.
                        </p>
                    </div>
                </div>

                <div v-if="timeline.length" class="mt-4 space-y-3 sm:mt-6 sm:space-y-4">
                    <div
                        v-for="(step, index) in timeline"
                        :key="step.id"
                        class="grid grid-cols-[24px_minmax(0,1fr)] gap-3 md:grid-cols-[24px_minmax(0,1fr)_190px] md:items-start md:gap-4"
                    >
                        <div class="relative flex items-start justify-center">
                            <span
                                v-if="index !== timeline.length - 1"
                                class="absolute left-1/2 top-6 h-full w-px -translate-x-1/2 bg-blue-200"
                            />
                            <span
                                class="relative z-10 mt-0.5 grid size-6 place-items-center rounded-full"
                                :class="timelineDotClasses(timelineStepState(index, step.timestamp))"
                            >
                                <CheckCircle2
                                    v-if="timelineStepState(index, step.timestamp) === 'done'"
                                    class="size-3.5"
                                />
                                <Clock3
                                    v-else-if="timelineStepState(index, step.timestamp) === 'current'"
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
                            :class="timelineCardClasses(timelineStepState(index, step.timestamp))"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="break-words text-sm font-semibold text-slate-900">
                                        {{ step.label }}
                                    </p>
                                    <p
                                        v-if="step.role"
                                        class="mt-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400"
                                    >
                                        {{ step.role }}
                                    </p>
                                    <p
                                        v-if="step.actor"
                                        class="mt-1 text-xs font-medium text-slate-500"
                                    >
                                        {{ step.actor }}
                                    </p>
                                </div>
                                <button
                                v-if="step.note"
                                type="button"
                                class="fast-btn fast-btn-danger shrink-0 px-2.5 py-1 text-xs"
                                :aria-expanded="expandedTimelineNoteId === step.id"
                                :aria-controls="`timeline-note-${step.id}`"
                                @click="toggleTimelineNote(step.id)"
                            >
                                    <ShieldAlert class="size-3.5" />
                                    Catatan
                                </button>
                            </div>
                            <div
                                v-if="step.note && expandedTimelineNoteId === step.id"
                                :id="`timeline-note-${step.id}`"
                                class="mt-3 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm leading-6 text-red-700"
                            >
                                {{ step.note }}
                            </div>
                            <p class="mt-3 text-xs font-medium text-slate-500 md:hidden">
                                {{
                                    timelineStepState(index, step.timestamp) === 'pending'
                                        ? 'Menunggu'
                                        : formatDateTime(step.timestamp)
                                }}
                            </p>
                        </div>

                        <div class="hidden pt-1 text-xs text-slate-500 md:block md:text-right">
                            {{ formatDateTime(step.timestamp) }}
                            <div v-if="step.actor" class="mt-1 font-medium text-slate-400">
                                {{ step.actor }}
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="mt-6 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-sm text-slate-500">
                    Riwayat proses belum tersedia.
                </div>
            </section>

        </div>

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
    </FastLayout>
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
