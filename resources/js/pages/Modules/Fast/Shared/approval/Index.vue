<script setup lang="ts">
import AdminLayout from '@/layouts/Modules/Fast/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    BadgeCheck,
    Check,
    Clock3,
    X,
    XCircle,
    FileText,
} from 'lucide-vue-next';
type Summary = {
    waiting: number;
    approved: number;
    revision_requested: number;
    final_rejected?: number;
};
type DetailLampiran = {
    id: number;
    name: string;
    url: string;
    type?: string | null;
};
type SuratItem = {
    id: number;
    type?: string | null;
    status: string;
    tanggal_pengajuan?: string | null;
    created_at?: string | null;
    letter_mode?: string | null;
    letter_mode_label?: string | null;
    is_institution?: boolean;
    subject?: { name?: string | null; nim?: string | null } | null;
    jenisSurat?: { id?: number | null; nama?: string | null } | null;
};
type SuratDetail = {
    id: number;
    type?: string | null;
    status: string;
    jenis_surat?: string | null;
    nomor_surat?: string | null;
    keperluan?: string | null;
    tanggal_pengajuan?: string | null;
    subject?: { name?: string | null; nim?: string | null } | null;
    isi_surat?: Record<string, unknown>;
    lampiran?: DetailLampiran[];
    approval_notes?: {
        role?: string | null;
        status?: string | null;
        label?: string | null;
        note?: string | null;
        acted_at?: string | null;
        actor?: string | null;
    }[];
    draft_preview_url?: string | null;
};
type PaginationLink = { url: string | null; label: string; active: boolean };
type PaginatedSurats = {
    data: SuratItem[];
    links: PaginationLink[];
    from?: number | null;
    to?: number | null;
    total: number;
};
type FilterState = {
    status?: string;
};
type PageProps = {
    auth: { user?: { name?: string } };
    flash?: { success?: string };
};
const props = withDefaults(
    defineProps<{
        role?: { name?: string | null; slug?: string | null };
        surats?: PaginatedSurats;
        summary?: Summary;
        filters?: FilterState;
    }>(),
    {
        role: () => ({ name: 'Approval', slug: 'dekan' }),
        surats: () => ({ data: [], links: [], total: 0 }),
        summary: () => ({
            waiting: 0,
            approved: 0,
            revision_requested: 0,
            final_rejected: 0,
        }),
        filters: () => ({ status: 'validated_admin' }),
    },
);

const page = usePage<PageProps>();
const filters = reactive({
    status: props.filters.status ?? 'validated_admin',
});
const attachmentPreviewOpen = ref(false);
const activeAttachment = ref<DetailLampiran | null>(null);
const toastMessage = ref('');
const normalizedRole = computed(() =>
    String(props.role.slug ?? props.role.name ?? '')
        .toLowerCase()
        .includes('kaprodi')
        ? 'kaprodi'
        : 'dekan',
);
const basePath = computed(() => `/${normalizedRole.value}`);
const visibleSurats = computed(() => props.surats.data.slice(0, 7));
const quickSubmissions = computed(() => visibleSurats.value);
const summaryCards = computed(() => [
    {
        label: 'Divalidasi Admin',
        value: props.summary.waiting,
        status: 'validated_admin',
        icon: Clock3,
        border: 'border-amber-200',
        iconColor: 'text-amber-500',
        textColor: 'text-amber-700',
    },
    {
        label: 'Disetujui',
        value: props.summary.approved,
        status:
            normalizedRole.value === 'kaprodi'
                ? 'approved_kaprodi'
                : 'approved_dekan',
        icon: BadgeCheck,
        border: 'border-emerald-200',
        iconColor: 'text-emerald-500',
        textColor: 'text-emerald-700',
    },
    {
        label:
            normalizedRole.value === 'kaprodi'
                ? 'Revisi dari Kaprodi'
                : 'Revisi dari Dekan',
        value: props.summary.revision_requested,
        status: 'revision_requested',
        icon: XCircle,
        border: 'border-amber-200',
        iconColor: 'text-amber-500',
        textColor: 'text-amber-700',
    },
    {
        label: 'Ditolak Final',
        value: props.summary.final_rejected ?? 0,
        status: 'rejected_approver',
        icon: X,
        border: 'border-slate-200',
        iconColor: 'text-slate-500',
        textColor: 'text-slate-700',
    },
]);

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
const ns = (s?: string | null) =>
    String(s ?? '')
        .trim()
        .toLowerCase();
function subjectLabel(type?: string | null) {
    return type === 'surat_keluar' ? 'Atas Nama' : 'Pemohon';
}
function isInstitutionLetter(item: { is_institution?: boolean | null; letter_mode?: string | null }) {
    return Boolean(item.is_institution) || item.letter_mode === 'institution';
}
function subjectName(item: { type?: string | null; subject?: { name?: string | null } | null }) {
    return item.subject?.name ?? '-';
}
function subjectNim(item: { subject?: { nim?: string | null } | null }) {
    return item.subject?.nim ?? '-';
}
function rowCanBeProcessed(item: SuratItem) {
    return ns(item.status) === 'validated_admin';
}
function statusLabel(
    item: {
        status: string;
        is_institution?: boolean;
        letter_mode?: string | null;
    },
) {
    const s = ns(item.status);
    const isInstitution = isInstitutionLetter(item);
    if (s === 'validated_admin') return isInstitution ? 'Menunggu Persetujuan' : 'Divalidasi Admin';
    if (s === 'approved_kaprodi') return 'Disetujui Kaprodi';
    if (s === 'approved_dekan') return 'Disetujui Dekan';
    if (s === 'revision_requested')
        return normalizedRole.value === 'kaprodi'
            ? 'Dikembalikan Kaprodi'
            : 'Dikembalikan Dekan';
    if (s === 'rejected_approver')
        return normalizedRole.value === 'kaprodi'
            ? 'Ditolak Kaprodi'
            : 'Ditolak Dekan';
    return 'Diproses';
}
function statusBadgeClass(status: string) {
    const s = ns(status);
    if (s === 'validated_admin') return 'bg-amber-50 text-amber-700';
    if (s === 'approved_kaprodi' || s === 'approved_dekan')
        return 'bg-emerald-50 text-emerald-700';
    if (s === 'revision_requested') return 'bg-amber-50 text-amber-700';
    if (s === 'rejected_approver') return 'bg-red-50 text-red-700';
    return 'bg-amber-50 text-amber-700';
}
function formatDate(date?: string | null) {
    if (!date) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(date));
}
function applyFilters() {
    const params = filters.status ? { status: filters.status } : {};
    router.get(`${basePath.value}/dashboard`, params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}
function openAttachmentPreview(f: DetailLampiran) {
    if (isPdfAttachment(f) && f.url) {
        window.open(f.url, '_blank', 'noopener,noreferrer');
        return;
    }

    if (isWordAttachment(f) && f.url) {
        const link = document.createElement('a');
        link.href = f.url;
        link.download = f.name || 'lampiran.docx';
        link.rel = 'noopener';
        document.body.appendChild(link);
        link.click();
        link.remove();
        return;
    }

    activeAttachment.value = f;
    attachmentPreviewOpen.value = true;
}
function closeAttachmentPreview() {
    attachmentPreviewOpen.value = false;
    activeAttachment.value = null;
}
function isImageAttachment(f?: DetailLampiran | null) {
    if (!f) return false;
    return (
        (f.type ?? '').toLowerCase().startsWith('image/') ||
        ['.jpg', '.jpeg', '.png', '.gif', '.webp'].some((e) =>
            f.name.toLowerCase().endsWith(e),
        )
    );
}
function isPdfAttachment(f?: DetailLampiran | null) {
    if (!f) return false;
    return (
        (f.type ?? '').toLowerCase().includes('pdf') ||
        f.name.toLowerCase().endsWith('.pdf')
    );
}

function isWordAttachment(f?: DetailLampiran | null) {
    if (!f) return false;

    const type = (f.type ?? '').toLowerCase();
    const name = f.name.toLowerCase();

    return (
        type.includes('msword') ||
        type.includes('wordprocessingml.document') ||
        name.endsWith('.doc') ||
        name.endsWith('.docx')
    );
}
</script>
<template>
    <AdminLayout
        :title="`Dashboard ${role.name || 'Approval'}`"
        subtitle="Monitoring approval lanjutan setelah validasi admin"
        active-menu="approval.dashboard"
        :breadcrumbs="[{ label: 'Approval' }]"
    >
        <Head :title="`Dashboard ${role.name || 'Approval'}`" />
        <!-- Monitoring Hero -->
        <div class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div
                v-for="stat in summaryCards"
                :key="stat.label"
                class="rounded-2xl border bg-white p-4 text-left transition hover:shadow-sm"
                :class="stat.border"
            >
                <div class="flex items-center justify-between">
                    <p class="text-[11px] text-slate-500">
                        {{ stat.label }}
                    </p>
                    <component
                        :is="stat.icon"
                        class="size-4"
                        :class="stat.iconColor"
                    />
                </div>
                <p class="mt-2 text-2xl font-bold text-slate-900">
                    {{ String(stat.value).padStart(2, '0') }}
                </p>
                <p class="mt-1 text-[11px] text-slate-400">
                    {{ stat.status === 'validated_admin' ? 'Surat menunggu tindakan approver' : stat.status === 'approved_kaprodi' || stat.status === 'approved_dekan' ? 'Sudah disetujui dan masuk arsip' : stat.status === 'revision_requested' ? 'Perlu revisi dari admin' : 'Keputusan final telah dibuat' }}
                </p>
            </div>
        </div>
        <!-- Main grid: Tabel + Sidebar -->
        <div class="grid gap-6 xl:grid-cols-[1fr_300px]">
            <!-- Tabel -->
            <div
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white"
            >
                <!-- Header + Filter -->
                <div class="border-b border-slate-100 px-5 py-4">
                    <div class="mb-3 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900">
                                Daftar Surat Approval
                            </h2>
                            <p class="mt-0.5 text-xs text-slate-400">
                                {{ surats.from ?? 0 }}-{{ surats.to ?? 0 }} dari
                                {{ surats.total }} data
                            </p>
                        </div>
                    </div>
                </div>
                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50">
                            <tr
                                class="text-[10px] font-semibold tracking-widest text-slate-400 uppercase"
                            >
                                <th class="px-5 py-3">Subjek Surat</th>
                                <th class="px-5 py-3">Jenis Surat</th>
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-5 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="surats.data.length === 0">
                                <td colspan="4" class="px-5 py-12 text-center">
                                    <FileText
                                        class="mx-auto mb-2 size-10 text-slate-300"
                                    />
                                    <p class="text-sm text-slate-400">
                                        Belum ada data approval untuk filter
                                        yang dipilih.
                                    </p>
                                </td>
                            </tr>
                            <tr
                                v-for="item in visibleSurats"
                                :key="item.id"
                                class="border-t border-slate-100 text-sm transition-colors hover:bg-slate-50/50"
                            >
                                <td class="px-5 py-3.5">
                                    <p
                                        class="text-xs font-semibold text-slate-900"
                                    >
                                        {{
                                            isInstitutionLetter(item)
                                                ? 'Surat Institusi'
                                                : subjectName(item)
                                        }}
                                        </p>
                                    <p
                                        v-if="!isInstitutionLetter(item)"
                                        class="font-mono text-[10px] text-slate-400"
                                    >
                                        {{ subjectNim(item) }}
                                    </p>
                                </td>
                                <td
                                    class="max-w-[180px] truncate px-5 py-3.5 text-xs text-slate-600"
                                >
                                    {{ item.jenisSurat?.nama || '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-400">
                                    {{
                                        formatDate(
                                            item.tanggal_pengajuan ||
                                                item.created_at,
                                        )
                                    }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span
                                        class="rounded-full px-2 py-1 text-[10px] font-semibold"
                                        :class="statusBadgeClass(item.status)"
                                    >
                                        {{ statusLabel(item) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div
                    v-if="surats.links.length > 3"
                    class="flex flex-wrap items-center gap-1.5 border-t border-slate-100 px-5 py-3"
                >
                <Link
                    v-for="link in surats.links"
                    :key="`${link.label}-${link.url}`"
                    :href="link.url || ''"
                    class="fast-btn px-3 py-1.5 text-xs font-medium"
                    :class="[
                        link.active
                            ? 'fast-btn-primary'
                            : 'fast-btn-outline',
                        !link.url ? 'pointer-events-none opacity-40' : '',
                    ]"
                    v-html="link.label"
                />
                </div>
            </div>
            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Surat Terbaru -->
                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <h3
                        class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-900"
                    >
                        <Clock3 class="size-4 text-blue-500" /> Surat Terbaru
                    </h3>
                    <div class="space-y-3">
                        <template v-if="quickSubmissions.length">
                            <div
                                v-for="item in quickSubmissions"
                                :key="item.id"
                                class="rounded-xl border border-slate-100 bg-slate-50 p-3"
                            >
                                <div class="flex items-start gap-2">
                                    <div
                                        class="mt-1.5 size-1.5 rounded-full bg-blue-500"
                                    />
                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="truncate text-xs font-medium text-slate-700"
                                        >
                                            {{
                                                isInstitutionLetter(item)
                                                    ? 'Surat Institusi'
                                                    : subjectName(item)
                                            }}
                                        </p>
                                        <p class="text-[10px] text-slate-400">
                                            {{
                                                isInstitutionLetter(item)
                                                    ? (item.jenisSurat?.nama ?? '-')
                                                    : (item.jenisSurat?.nama ?? '-')
                                            }}
                                        </p>
                                        <div class="mt-1 flex items-center gap-2">
                                            <span
                                                class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                                :class="
                                                    statusBadgeClass(
                                                        item.status,
                                                    )
                                                "
                                            >
                                                {{ statusLabel(item) }}
                                            </span>
                                            <span class="text-[10px] text-slate-400">
                                                {{
                                                    formatDate(
                                                        item.tanggal_pengajuan ??
                                                            item.created_at,
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <p v-else class="text-xs text-slate-400">
                            Belum ada pengajuan
                        </p>
                    </div>
                    <div class="mt-3 border-t border-slate-100 pt-3">
                        <Link
                            :href="`${basePath}/antrian`"
                            class="text-xs font-medium text-blue-600 transition-colors hover:text-blue-700"
                        >
                            Lihat Antrian Approval
                        </Link>
                    </div>
                </div>

            </div>
        </div>
        <!-- Modal Lampiran -->
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
                        <DialogTitle
                            class="text-lg font-semibold text-slate-900"
                            >Preview Lampiran</DialogTitle
                        >
                        <DialogDescription class="text-sm text-slate-400">{{
                            activeAttachment?.name
                        }}</DialogDescription>
                    </DialogHeader>
                </div>
                <div class="min-h-0 flex-1 overflow-y-auto bg-slate-50 p-4">
                    <div
                        v-if="
                            activeAttachment &&
                            isImageAttachment(activeAttachment)
                        "
                        class="flex justify-center"
                    >
                        <img
                            :src="activeAttachment.url"
                            :alt="activeAttachment.name"
                            class="max-h-[65vh] rounded-xl border border-slate-200 object-contain shadow-sm"
                        />
                    </div>
                    <div
                        v-else-if="
                            activeAttachment &&
                            isPdfAttachment(activeAttachment)
                        "
                        class="overflow-hidden rounded-xl border border-slate-200 shadow-sm"
                    >
                        <iframe
                            :src="activeAttachment.url"
                            class="h-[65vh] w-full"
                            title="Preview PDF"
                        />
                    </div>
                    <div
                        v-else
                        class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-500"
                    >
                        Preview hanya tersedia untuk PDF dan gambar.
                    </div>
                </div>
                <div
                    class="flex justify-end border-t border-slate-100 px-6 py-4"
                >
                    <Button variant="ghost" @click="closeAttachmentPreview"
                        >Tutup</Button
                    >
                </div>
            </DialogContent>
        </Dialog>
        <!-- Toast -->
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
                    <BadgeCheck class="size-5 shrink-0 text-blue-500" />
                    <p class="text-sm font-medium">{{ toastMessage }}</p>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>
