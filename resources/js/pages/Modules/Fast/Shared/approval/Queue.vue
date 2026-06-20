<script setup lang="ts">
import AdminLayout from '@/layouts/Modules/Fast/AdminLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog';
import {
    BadgeCheck,
    Check,
    Clock3,
    Search,
    X,
    XCircle,
    FileText,
    AlertCircle,
    Eye,
    ExternalLink,
    GraduationCap,
} from 'lucide-vue-next';
type DetailLampiran = {
    id: number;
    name: string;
    url: string;
    type?: string | null;
};
type SuratItem = {
    id: number;
    status: string;
    tanggal_pengajuan?: string | null;
    created_at?: string | null;
    pemohon?: { name?: string | null; nim?: string | null } | null;
    jenisSurat?: { id?: number | null; nama?: string | null } | null;
};
type SuratDetail = {
    id: number;
    status: string;
    jenis_surat?: string | null;
    nomor_surat?: string | null;
    keperluan?: string | null;
    tanggal_pengajuan?: string | null;
    pemohon?: { name?: string | null; nim?: string | null } | null;
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
type PageProps = {
    flash?: { success?: string };
};
const props = defineProps<{
    role: { name?: string | null; slug?: string | null };
    surats: PaginatedSurats;
    filters: { search?: string; category_id?: string };
    categories: Array<{ id: number; nama: string }>;
}>();
const page = usePage<PageProps>();
const search = ref(props.filters.search ?? '');
const categoryId = ref(props.filters.category_id ?? '');
const toastMessage = ref('');
const selectedSurat = ref<SuratItem | null>(null);
const rejectModalOpen = ref(false);
const finalRejectModalOpen = ref(false);
const rejectForm = useForm({ reason: '' });
const finalRejectForm = useForm({ reason: '' });
const attachmentPreviewOpen = ref(false);
const activeAttachment = ref<DetailLampiran | null>(null);
const normalizedRole = computed(() =>
    String(props.role.slug ?? props.role.name ?? '')
        .toLowerCase()
        .includes('kaprodi')
        ? 'kaprodi'
        : 'dekan',
);
const basePath = computed(() => `/${normalizedRole.value}`);
const firstName = computed(
    () => String(props.role.name ?? 'Approver').split(' ')[0],
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
function applyFilter() {
    router.get(
        `${basePath.value}/antrian`,
        {
            search: search.value || undefined,
            category_id: categoryId.value || undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}
function resetFilter() {
    search.value = '';
    categoryId.value = '';
    applyFilter();
}
async function openDetail(id: number) {
    router.visit(`${basePath.value}/surat/${id}/detail?from=antrian`);
}
function openAttachmentPreview(f: DetailLampiran) {
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
function formatDate(date?: string | null) {
    if (!date) return '';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(date));
}
function initials(name?: string | null) {
    if (!name) return '?';
    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() ?? '')
        .join('');
}
function statusLabel(status: string) {
    const labels: Record<string, string> = {
        pending: 'Pending',
        validated_admin: 'Pending',
        revision_requested: 'Revisi',
        rejected_admin: 'Ditolak Admin',
        rejected_approver: 'Ditolak Pimpinan',
        approved_kaprodi: 'Disetujui Kaprodi',
        approved_dekan: 'Disetujui Dekan',
    };
    return labels[status] ?? status;
}
function statusClass(status: string) {
    if (status === 'pending') return 'bg-amber-50 text-amber-700';
    if (status === 'validated_admin') return 'bg-amber-50 text-amber-700';
    if (status === 'revision_requested') return 'bg-amber-50 text-amber-700';
    if (status === 'rejected_admin' || status === 'rejected_approver')
        return 'bg-red-50 text-red-700';
    if (status.startsWith('approved')) return 'bg-emerald-50 text-emerald-700';
    return 'bg-slate-100 text-slate-600';
}
function cardBorderClass(status: string) {
    if (status === 'pending') return 'hover:border-amber-300';
    if (status === 'validated_admin') return 'hover:border-amber-300';
    if (status === 'revision_requested') return 'hover:border-amber-300';
    if (status === 'rejected_admin' || status === 'rejected_approver')
        return 'hover:border-red-300';
    if (status.startsWith('approved')) return 'hover:border-emerald-300';
    return 'hover:border-slate-300';
}
function stripeClass(status: string) {
    if (status === 'pending') return 'bg-amber-400';
    if (status === 'validated_admin') return 'bg-amber-400';
    if (status === 'revision_requested') return 'bg-amber-400';
    if (status === 'rejected_admin' || status === 'rejected_approver')
        return 'bg-red-400';
    if (status.startsWith('approved')) return 'bg-emerald-400';
    return 'bg-slate-300';
}
function submitApprove(item: SuratItem) {
    router.post(
        `${basePath.value}/surat/${item.id}/approve`,
        {},
        { preserveScroll: true },
    );
}
function openRejectModal(item: SuratItem) {
    selectedSurat.value = item;
    rejectForm.reset();
    rejectModalOpen.value = true;
}
function closeRejectModal() {
    rejectModalOpen.value = false;
    selectedSurat.value = null;
    rejectForm.reset();
}
function submitReject() {
    if (!selectedSurat.value) return;
    rejectForm.post(
        `${basePath.value}/surat/${selectedSurat.value.id}/reject`,
        { preserveScroll: true, onSuccess: () => closeRejectModal() },
    );
}
function openFinalRejectModal(item: SuratItem) {
    selectedSurat.value = item;
    finalRejectForm.reset();
    finalRejectModalOpen.value = true;
}
function closeFinalRejectModal() {
    finalRejectModalOpen.value = false;
    selectedSurat.value = null;
    finalRejectForm.reset();
}
function submitFinalReject() {
    if (!selectedSurat.value) return;
    finalRejectForm.post(
        `${basePath.value}/surat/${selectedSurat.value.id}/final-reject`,
        { preserveScroll: true, onSuccess: () => closeFinalRejectModal() },
    );
}
</script>
<template>
    <AdminLayout
        :title="`Antrian Approval ${role.name || 'Approval'}`"
        subtitle="Daftar surat yang menunggu persetujuan Anda"
        active-menu="approval.antrian"
        :breadcrumbs="[
            { label: 'Dashboard', href: `${basePath}/dashboard` },
            { label: 'Antrian Approval' },
        ]"
        >
        <Head :title="`Antrian Approval ${role.name || 'Approval'}`" />
        <!-- Search & Filter -->
        <div class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <div class="relative flex-1">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                    />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nama atau NIM pemohon..."
                        class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 pr-4 pl-10 text-sm text-slate-800 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100"
                        @keyup.enter="applyFilter"
                    />
                </div>
                <div class="w-full lg:w-56">
                    <select
                        v-model="categoryId"
                        class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100"
                    >
                        <option value="">Semua Kategori</option>
                        <option
                            v-for="category in categories"
                            :key="category.id"
                            :value="String(category.id)"
                        >
                            {{ category.nama }}
                        </option>
                    </select>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row lg:flex-row">
                    <button
                        type="button"
                        class="fast-btn fast-btn-primary h-11 w-full px-5 text-sm sm:w-auto"
                        @click="applyFilter"
                    >
                        Terapkan
                    </button>
                    <button
                        v-if="search || categoryId"
                        type="button"
                        class="fast-btn fast-btn-outline h-11 w-full px-5 text-sm font-medium text-slate-500 sm:w-auto"
                        @click="resetFilter"
                    >
                        Reset
                    </button>
                </div>
            </div>
        </div>
        <!-- Card list -->
        <div class="space-y-3">
            <div
                v-if="surats.data.length === 0"
                class="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-slate-200 bg-white py-16 text-center"
            >
                <div
                    class="grid size-16 place-items-center rounded-2xl border border-slate-100 bg-slate-50"
                >
                    <FileText class="size-8 text-slate-200" />
                </div>
                <p class="text-sm font-medium text-slate-400">
                    Tidak ada surat dalam antrian approval.
                </p>
                <p class="text-xs text-slate-300">
                    Coba ubah filter atau kata kunci pencarian.
                </p>
            </div>
            <div
                v-for="item in surats.data"
                :key="item.id"
                class="group relative flex items-start gap-0 overflow-hidden rounded-2xl border border-slate-200 bg-white transition-all hover:shadow-md"
                :class="cardBorderClass(item.status)"
            >
                <div
                    class="w-1.5 shrink-0 self-stretch"
                    :class="stripeClass(item.status)"
                />
                <div class="flex-1 p-4 sm:p-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="flex min-w-0 flex-1 items-center gap-3">
                            <div
                                class="grid size-10 shrink-0 place-items-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-500"
                            >
                                {{ initials(item.pemohon?.name) }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p
                                        class="truncate text-sm font-semibold text-slate-900"
                                    >
                                        {{ item.pemohon?.name || '' }}
                                    </p>
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                        :class="statusClass(item.status)"
                                    >
                                        {{ statusLabel(item.status) }}
                                    </span>
                                </div>
                                <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <p class="text-xs text-slate-500">
                                        {{ item.jenisSurat?.nama || '' }}
                                    </p>
                                    <p class="font-mono text-[10px] text-slate-400">
                                        {{ item.pemohon?.nim || '' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="shrink-0 text-right sm:pt-0.5">
                            <p class="text-[10px] text-slate-400">
                                {{
                                    formatDate(
                                        item.tanggal_pengajuan ||
                                            item.created_at,
                                    )
                                }}
                            </p>
                        </div>
                        <div
                            class="flex shrink-0 flex-wrap items-center justify-end gap-2"
                        >
                            <button
                                type="button"
                                class="flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-1.5 text-[10px] font-medium text-slate-600 transition-colors hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600"
                                title="Lihat Detail"
                                @click="openDetail(item.id)"
                            >
                                <Eye class="size-3" /> Detail
                            </button>
                            <button
                                type="button"
                                class="fast-btn fast-btn-primary flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-medium"
                                title="Setujui"
                                @click="submitApprove(item)"
                            >
                                <Check class="size-3" /> Proses
                            </button>
                            <button
                                type="button"
                                class="fast-btn fast-btn-danger flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-medium"
                                title="Kembalikan untuk revisi"
                                @click="openRejectModal(item)"
                            >
                                <X class="size-3" /> Revisi
                            </button>
                            <button
                                type="button"
                                class="fast-btn fast-btn-danger flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-medium"
                                title="Tolak final"
                                @click="openFinalRejectModal(item)"
                            >
                                <XCircle class="size-3" /> Tolak
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div
                v-if="surats.links.length > 3"
                class="mt-5 flex flex-wrap items-center gap-1.5"
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
        <!-- Modal Reject -->
        <Dialog
            :open="rejectModalOpen"
            @update:open="(v) => (v ? null : closeRejectModal())"
        >
            <DialogContent
                class="max-w-md rounded-2xl border-0 bg-white p-0"
                :show-close-button="false"
            >
                <div class="p-6">
                    <DialogHeader class="mb-4 text-left">
                        <DialogTitle
                            class="text-lg font-semibold text-slate-900"
                            >Kembalikan untuk Revisi</DialogTitle
                        >
                        <DialogDescription class="text-sm text-slate-400"
                            >Isi catatan revisi untuk admin terkait surat
                            {{
                                selectedSurat?.pemohon?.name || 'pemohon'
                            }}.</DialogDescription
                        >
                    </DialogHeader>
                    <form @submit.prevent="submitReject" class="space-y-4">
                        <label class="block space-y-1.5">
                            <span class="text-xs font-medium text-slate-700"
                                >Catatan Revisi
                                <span class="text-red-500">*</span></span
                            >
                            <textarea
                                v-model="rejectForm.reason"
                                rows="4"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100"
                                placeholder="Jelaskan bagian draft yang perlu direvisi..."
                            ></textarea>
                            <p
                                v-if="rejectForm.errors.reason"
                                class="text-xs text-red-500"
                            >
                                {{ rejectForm.errors.reason }}
                            </p>
                        </label>
                        <DialogFooter class="gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                class="rounded-xl"
                                @click="closeRejectModal"
                                >Batal</Button
                            >
                            <Button
                                type="submit"
                                class="rounded-xl bg-red-600 text-white hover:bg-red-700"
                                :disabled="rejectForm.processing"
                                >Kembalikan ke Admin</Button
                            >
                        </DialogFooter>
                    </form>
                </div>
            </DialogContent>
        </Dialog>
        <!-- Modal Final Reject -->
        <Dialog
            :open="finalRejectModalOpen"
            @update:open="(v) => (v ? null : closeFinalRejectModal())"
        >
            <DialogContent
                class="max-w-md rounded-2xl border-0 bg-white p-0"
                :show-close-button="false"
            >
                <div class="p-6">
                    <DialogHeader class="mb-4 text-left">
                        <DialogTitle
                            class="text-lg font-semibold text-slate-900"
                            >Tolak Final</DialogTitle
                        >
                        <DialogDescription class="text-sm text-slate-400"
                            >Keputusan akhir. Pengajuan tidak kembali ke admin
                            dan alasan akan terlihat oleh
                            pemohon.</DialogDescription
                        >
                    </DialogHeader>
                    <form @submit.prevent="submitFinalReject" class="space-y-4">
                        <label class="block space-y-1.5">
                            <span class="text-xs font-medium text-slate-700"
                                >Alasan Penolakan
                                <span class="text-red-500">*</span></span
                            >
                            <textarea
                                v-model="finalRejectForm.reason"
                                rows="4"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100"
                                placeholder="Jelaskan alasan penolakan final..."
                            ></textarea>
                            <p
                                v-if="finalRejectForm.errors.reason"
                                class="text-xs text-red-500"
                            >
                                {{ finalRejectForm.errors.reason }}
                            </p>
                        </label>
                        <DialogFooter class="gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                class="rounded-xl"
                                @click="closeFinalRejectModal"
                                >Batal</Button
                            >
                            <Button
                                type="submit"
                                class="rounded-xl bg-slate-800 text-white hover:bg-slate-900"
                                :disabled="finalRejectForm.processing"
                                >Tolak Final</Button
                            >
                        </DialogFooter>
                    </form>
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
