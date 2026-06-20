<script setup lang="ts">
import { computed } from 'vue';
import {
    ExternalLink,
    FileText,
    ShieldCheck,
} from 'lucide-vue-next';

type DetailLampiran = {
    id: number;
    name: string;
    url: string;
    type?: string | null;
};

type ApprovalNote = {
    role?: string | null;
    status?: string | null;
    label?: string | null;
    note?: string | null;
    acted_at?: string | null;
    actor?: string | null;
};

type DetailData = {
    status: string;
    jenis_surat?: string | null;
    nomor_surat?: string | null;
    keperluan?: string | null;
    tanggal_pengajuan?: string | null;
    pemohon?: { name?: string | null; nim?: string | null } | null;
    isi_surat?: Record<string, unknown>;
    lampiran?: DetailLampiran[];
    approval_notes?: ApprovalNote[];
    draft_preview_url?: string | null;
};

const props = defineProps<{
    detailData: DetailData;
    formatDate: (date?: string | null) => string;
    detailEntries: (payload?: Record<string, unknown>) => [string, unknown][];
    formatLabel: (key: string) => string;
    formatDetailValue: (value: unknown) => string;
    numberLabel?: string;
    onOpenDraftPreview?: () => void;
    onOpenAttachmentPreview?: (file: DetailLampiran) => void;
}>();

const extraEntries = computed(() =>
    props.detailEntries(props.detailData.isi_surat),
);

function statusPillClass(status: string) {
    const lowered = String(status ?? '').trim().toLowerCase();
    if (
        lowered === 'approved_kaprodi' ||
        lowered === 'approved_dekan' ||
        lowered === 'finished'
    ) {
        return 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200';
    }
    if (lowered === 'revision_requested') {
        return 'bg-amber-50 text-amber-700 ring-1 ring-amber-200';
    }
    if (lowered === 'rejected_approver' || lowered === 'rejected_final') {
        return 'bg-red-50 text-red-700 ring-1 ring-red-200';
    }
    return 'bg-slate-100 text-slate-700 ring-1 ring-slate-200';
}
</script>

<template>
    <div class="space-y-5">
        <section
            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_1px_2px_rgba(15,23,42,0.04)]"
        >
            <div class="mb-4 flex items-center gap-3">
                <div
                    class="grid size-10 place-items-center rounded-2xl bg-sky-50 text-sky-600 ring-1 ring-sky-100"
                >
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

            <div
                class="divide-y divide-slate-100 rounded-2xl border border-slate-200 bg-white"
            >
                <div
                    class="grid gap-2 px-4 py-3 text-sm sm:grid-cols-[180px_minmax(0,1fr)] sm:gap-4"
                >
                    <p class="text-slate-500">Nama</p>
                    <p class="min-w-0 break-words font-medium leading-6 text-slate-900">
                        {{ detailData.pemohon?.name || '-' }}
                    </p>
                </div>
                <div
                    class="grid gap-2 px-4 py-3 text-sm sm:grid-cols-[180px_minmax(0,1fr)] sm:gap-4"
                >
                    <p class="text-slate-500">NIM / NIP</p>
                    <p class="min-w-0 break-words font-mono font-medium leading-6 text-slate-900">
                        {{ detailData.pemohon?.nim || '-' }}
                    </p>
                </div>
                <div
                    class="grid gap-2 px-4 py-3 text-sm sm:grid-cols-[180px_minmax(0,1fr)] sm:gap-4"
                >
                    <p class="text-slate-500">Jenis Surat</p>
                    <p class="min-w-0 break-words font-medium leading-6 text-slate-900">
                        {{ detailData.jenis_surat || '-' }}
                    </p>
                </div>
                <div
                    class="grid gap-2 px-4 py-3 text-sm sm:grid-cols-[180px_minmax(0,1fr)] sm:gap-4"
                >
                    <p class="text-slate-500">Tanggal</p>
                    <p class="min-w-0 break-words font-medium leading-6 text-slate-900">
                        {{ formatDate(detailData.tanggal_pengajuan) }}
                    </p>
                </div>
                <div
                    class="grid gap-2 px-4 py-3 text-sm sm:grid-cols-[180px_minmax(0,1fr)] sm:gap-4"
                >
                    <p class="text-slate-500">
                        {{ numberLabel || 'Nomor Surat' }}
                    </p>
                    <p class="min-w-0 break-words font-mono font-medium leading-6 text-slate-900">
                        {{ detailData.nomor_surat || '-' }}
                    </p>
                </div>
                <div
                    class="grid gap-2 px-4 py-3 text-sm sm:grid-cols-[180px_minmax(0,1fr)] sm:gap-4"
                >
                    <p class="text-slate-500">Keperluan</p>
                    <p class="min-w-0 break-words font-medium leading-6 text-slate-900">
                        {{ detailData.keperluan || '-' }}
                    </p>
                </div>
                <template v-if="extraEntries.length > 0">
                    <div
                        v-for="[key, value] in extraEntries"
                        :key="key"
                        class="grid gap-2 px-4 py-3 text-sm sm:grid-cols-[180px_minmax(0,1fr)] sm:gap-4"
                    >
                        <p class="text-slate-500">
                            {{ formatLabel(key) }}
                        </p>
                        <p class="min-w-0 break-words font-medium leading-6 text-slate-900">
                            {{ formatDetailValue(value) }}
                        </p>
                    </div>
                </template>
                <div
                    v-if="extraEntries.length === 0"
                    class="px-4 py-6 text-sm text-slate-500"
                >
                    Tidak ada data tambahan.
                </div>
            </div>
        </section>

        <section
            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_1px_2px_rgba(15,23,42,0.04)]"
        >
            <div class="mb-4 flex items-center gap-3">
                <div
                    class="grid size-10 place-items-center rounded-2xl bg-sky-50 text-sky-600 ring-1 ring-sky-100"
                >
                    <ShieldCheck class="size-5" />
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">
                        Dokumen
                    </h2>
                    <p class="text-sm text-slate-500">
                        Akses preview dan informasi dokumen surat.
                    </p>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                <p class="text-sm font-semibold text-slate-900">
                    Status Surat
                </p>
                <p class="mt-1 text-xs leading-5 text-slate-500">
                    Dokumen surat saat ini sedang
                    {{ detailData.draft_preview_url ? 'tersedia untuk ditinjau.' : 'diproses.' }}
                </p>
                <span
                    class="mt-3 inline-flex rounded-full px-2.5 py-1 text-[10px] font-semibold"
                    :class="statusPillClass(detailData.status)"
                >
                    {{ detailData.status }}
                </span>
            </div>

                <button
                    v-if="detailData.draft_preview_url && onOpenDraftPreview"
                    type="button"
                    class="fast-btn fast-btn-soft mt-4 w-full justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-sky-700"
                    @click="onOpenDraftPreview"
                >
                    <ExternalLink class="size-4" />
                    Preview Dokumen
                </button>

            <div
                v-if="detailData.lampiran && detailData.lampiran.length > 0"
                class="mt-5"
            >
                <h3 class="mb-3 text-sm font-semibold text-slate-900">
                    Lampiran
                </h3>
                <div class="space-y-2">
                    <button
                        v-for="file in detailData.lampiran"
                        :key="file.id"
                        type="button"
                        class="fast-btn fast-btn-outline flex w-full items-center justify-between gap-3 px-4 py-3 text-left"
                        @click="onOpenAttachmentPreview?.(file)"
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
                            Preview
                        </span>
                    </button>
                </div>
            </div>
        </section>

        <section
            v-if="detailData.approval_notes && detailData.approval_notes.length > 0"
            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_1px_2px_rgba(15,23,42,0.04)]"
        >
            <div class="mb-4 flex items-center gap-3">
                <div
                    class="grid size-10 place-items-center rounded-2xl bg-amber-50 text-amber-600 ring-1 ring-amber-100"
                >
                    <ShieldCheck class="size-5" />
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">
                        Catatan Revisi / Approval
                    </h2>
                    <p class="text-sm text-slate-500">
                        Catatan dari role terkait ditampilkan sesuai urutan aksi.
                    </p>
                </div>
            </div>

            <div class="space-y-3">
                <div
                    v-for="(note, idx) in detailData.approval_notes"
                    :key="idx"
                    class="rounded-2xl border border-amber-100 bg-amber-50 p-4"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold text-amber-700">
                                {{ note.label || 'Catatan' }}
                            </p>
                            <p class="mt-1 text-sm font-medium text-slate-800">
                                {{ note.note || '-' }}
                            </p>
                        </div>
                        <span
                            v-if="note.acted_at"
                            class="shrink-0 text-[10px] text-amber-700"
                        >
                            {{ formatDate(note.acted_at) }}
                        </span>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
