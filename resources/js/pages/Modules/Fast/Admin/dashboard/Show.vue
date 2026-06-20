<script setup lang="ts">
import AdminLayout from '@/layouts/Modules/Fast/AdminLayout.vue';
import DocumentPreviewModal from '@/components/DocumentPreviewModal.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
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
    nomor_surat?: string | null;
    pemohon: { name: string; nim?: string | null };
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
    can_approve: boolean;
    can_edit: boolean;
    previewTemplateUrl: string | null;
    generatedDocumentUrl: string | null;
};

const props = defineProps<{ id: number } & Surat>();

const viewerOpen = ref(false);
const viewerUrl = ref<string | null>(null);
const viewerTitle = ref('');
const viewerType = ref<'html' | 'pdf'>('html');
const copiedNumber = ref(false);
const expandedTimelineNoteId = ref<number | null>(null);

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

const statusLabel: Record<string, string> = {
    pending: 'Menunggu Validasi',
    revision_requested: 'Menunggu Revisi Admin',
    validated_admin: 'Sudah Divalidasi Admin',
    approved_kaprodi: 'Disetujui Kaprodi',
    approved_dekan: 'Disetujui Dekan',
    finished: 'Selesai',
    rejected_admin: 'Ditolak Admin',
    rejected_approver: props.latest_rejection?.label ?? 'Ditolak Pimpinan',
};

const statusColor: Record<string, string> = {
    pending: 'bg-amber-50 text-amber-700 border-amber-200',
    revision_requested: 'bg-amber-50 text-amber-700 border-amber-200',
    validated_admin: 'bg-slate-100 text-slate-700 border-slate-200',
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

    return 'border-slate-200 bg-slate-50 text-slate-700';
}

function openPreviewDocument() {
    const sourceUrl = props.generatedDocumentUrl ?? props.previewTemplateUrl;
    if (!sourceUrl) return;

    const isPdfDocument = sourceUrl.endsWith('/pdf');
    viewerUrl.value = sourceUrl;
    viewerTitle.value = documentTitle.value;
    viewerType.value = isPdfDocument ? 'pdf' : 'html';
    viewerOpen.value = true;
}

function openDownloadPdf() {
    viewerUrl.value = `/admin/surat/${props.id}/pdf`;
    viewerTitle.value = documentTitle.value;
    viewerType.value = 'pdf';
    viewerOpen.value = true;
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
        title="Detail Surat"
        :subtitle="jenis_surat"
        active-menu="letters"
        :breadcrumbs="[
            { label: 'Arsip', href: '/admin/archive' },
            { label: 'Detail Surat' },
        ]"
    >
        <Head :title="`Detail Surat - ${jenis_surat}`" />

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

                    <div class="mt-2 divide-y divide-slate-100 rounded-2xl border border-slate-200 bg-white">
                        <div class="grid gap-2 px-4 py-3 text-sm md:grid-cols-[180px_minmax(0,1fr)] md:gap-4">
                            <p class="text-slate-500">Nama</p>
                            <p class="min-w-0 break-words font-medium leading-6 text-slate-900">
                                {{ pemohon?.name || '-' }}
                            </p>
                        </div>

                        <div class="grid gap-2 px-4 py-3 text-sm md:grid-cols-[180px_minmax(0,1fr)] md:gap-4">
                            <p class="text-slate-500">NIM / NIP</p>
                            <p class="min-w-0 break-words font-mono font-medium leading-6 text-slate-900">
                                {{ pemohon?.nim || '-' }}
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

                    <div v-if="can_edit" class="mt-4">
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
                            v-if="previewTemplateUrl || generatedDocumentUrl"
                            type="button"
                            class="fast-btn fast-btn-outline w-full px-4 py-2.5 text-sm font-semibold"
                            @click="openPreviewDocument"
                        >
                            <Eye class="size-4" />
                            Preview Dokumen
                        </button>

                        <button
                            v-if="isFinished"
                            type="button"
                            class="fast-btn fast-btn-primary w-full px-4 py-2.5 text-sm font-semibold"
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
                            Dokumen belum tersedia - surat belum selesai diproses.
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
                            Alur proses surat dari validasi sampai selesai.
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

                <div v-else class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-sm text-slate-500">
                    Riwayat belum tersedia.
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
