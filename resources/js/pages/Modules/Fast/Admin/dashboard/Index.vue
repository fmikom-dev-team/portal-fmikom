<script setup lang="ts">
// File: resources/js/pages/admin/dashboard/Index.vue
import AdminLayout from '@/layouts/Modules/Fast/AdminLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import {
    Eye,
    CheckCircle2,
    AlertCircle,
    XCircle,
    Clock3,
    FileText,
    AlertTriangle,
    X,
    RefreshCw,
    ArrowRight,
} from 'lucide-vue-next';

type SuratItem = {
    id: number;
    status: string;
    can_approve?: boolean;
    can_edit?: boolean;
    nomor_surat?: string | null;
    tanggal_pengajuan?: string | null;
    created_at?: string | null;
    pemohon?: { name?: string | null; nim?: string | null } | null;
    jenisSurat?: { id?: number | null; nama?: string | null } | null;
};

type Summary = {
    total: number;
    pending: number;
    validated: number;
    finished: number;
    rejected: number;
};
type PaginatedSurats = {
    data: SuratItem[];
    from?: number | null;
    to?: number | null;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

type AdminActivityItem = {
    id: number;
    nomor_surat?: string | null;
    keperluan?: string | null;
    tanggal_selesai?: string | null;
    pemohon?: { name?: string | null } | null;
    jenisSurat?: { nama?: string | null } | null;
};

type AdminActivitySummary = {
    recent: AdminActivityItem[];
};

type PageProps = {
    flash?: {
        success?: string;
        error?: string;
    };
};

const props = defineProps<{
    auth?: { user?: { name?: string | null } | null };
    surats: PaginatedSurats;
    summary: Summary;
    links: {
        submissionsIndex: string;
    };
}>();

const page = usePage<PageProps>();
const toastMessage = ref('');
const toastVariant = ref<'success' | 'error'>('success');

// -- Aksi validasi admin (centang / silang / komen) --------------------------
const approvingId = ref<number | null>(null);
function showToast(message: string, variant: 'success' | 'error' = 'success') {
    toastMessage.value = message;
    toastVariant.value = variant;
    window.setTimeout(() => {
        if (toastMessage.value === message) {
            toastMessage.value = '';
        }
    }, 2800);
}
watch(
    () => [page.props.flash?.success, page.props.flash?.error],
    ([success, error]) => {
        if (typeof success === 'string' && success.length > 0) {
            showToast(success, 'success');
            return;
        }
        if (typeof error === 'string' && error.length > 0) {
            showToast(error, 'error');
        }
    },
    { immediate: true },
);
function approveSurat(id: number) {
    if (approvingId.value) return;
    approvingId.value = id;
    router.post(
        `/admin/surat/${id}/approve`,
        {},
        {
            preserveScroll: true,
            onError: () => {
                showToast('Gagal memvalidasi pengajuan.', 'error');
            },
            onFinish: () => {
                approvingId.value = null;
            },
        },
    );
}

const rejectModalOpen = ref(false);
const rejectTargetId = ref<number | null>(null);
const rejectForm = useForm({ reason: '' });
function openRejectModal(id: number) {
    rejectTargetId.value = id;
    rejectForm.reset();
    rejectModalOpen.value = true;
}
function closeRejectModal() {
    rejectModalOpen.value = false;
    rejectTargetId.value = null;
}
function submitReject() {
    if (rejectTargetId.value === null) return;
    rejectForm.post(`/admin/surat/${rejectTargetId.value}/reject`, {
        preserveScroll: true,
        onError: () => {
            showToast('Gagal menolak pengajuan.', 'error');
        },
        onSuccess: () => closeRejectModal(),
    });
}

const quickSubmissions = computed(() => props.surats.data.slice(0, 4));
const statCards = computed(() => [
    {
        label: 'Total Pengajuan',
        value: props.summary.total,
        icon: FileText,
        border: 'border-blue-200',
        iconColor: 'text-blue-400',
    },
    {
        label: 'Pending',
        value: props.summary.pending,
        icon: Clock3,
        border: 'border-sky-200',
        iconColor: 'text-sky-400',
    },
    {
        label: 'Selesai',
        value: props.summary.finished,
        icon: CheckCircle2,
        border: 'border-green-200',
        iconColor: 'text-green-400',
    },
    {
        label: 'Ditolak',
        value: props.summary.rejected,
        icon: XCircle,
        border: 'border-red-200',
        iconColor: 'text-red-400',
    },
]);

function statusLabel(s: string) {
    const map: Record<string, string> = {
        pending: 'Pending',
        validated_admin: 'Diteruskan ke Approver',
        approved_kaprodi: 'Disetujui Kaprodi',
        approved_dekan: 'Disetujui Dekan',
        revision_requested: 'Revisi',
        finished: 'Selesai',
        rejected_admin: 'Ditolak Admin',
        rejected_approver: 'Ditolak Pimpinan',
        cancelled: 'Dibatalkan',
    };
    return map[s] ?? s;
}

function statusClass(s: string) {
    if (s === 'finished') return 'bg-emerald-50 text-emerald-700';
    if (s === 'rejected_admin' || s === 'rejected_approver')
        return 'bg-red-50 text-red-700';
    if (s === 'revision_requested') return 'bg-amber-50 text-amber-700';
    if (s.startsWith('approved')) return 'bg-emerald-50 text-emerald-700';
    if (s === 'validated_admin') return 'bg-slate-100 text-slate-700';
    if (s === 'cancelled') return 'bg-slate-100 text-slate-600';
    return 'bg-amber-50 text-amber-700';
}

function formatDate(d?: string | null) {
    if (!d) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(d));
}

function historyIcon(action: string) {
    if (action.includes('approved') || action === 'validated') return '?';
    if (action === 'rejected') return '?';
    if (action === 'generated' || action === 'printed') return '?';
    if (action === 'created') return '+';
    return 'ï¿½';
}

function historyColor(action: string) {
    if (action.includes('approved') || action === 'validated')
        return 'text-blue-600 bg-blue-50';
    if (action === 'rejected') return 'text-red-600 bg-red-50';
    if (action === 'created') return 'text-indigo-600 bg-indigo-50';
    return 'text-slate-500 bg-slate-100';
}

function activityBadgeClass(action?: string | null) {
    const value = String(action ?? '').toLowerCase();
    if (value.includes('approved') || value.includes('validated')) {
        return 'bg-emerald-50 text-emerald-700';
    }
    if (value.includes('revised') || value.includes('revision')) {
        return 'bg-amber-50 text-amber-700';
    }
    if (value.includes('rejected')) {
        return 'bg-red-50 text-red-700';
    }
    return 'bg-slate-100 text-slate-600';
}

</script>

<template>
    <AdminLayout
        title="Dashboard"
        subtitle="Monitoring pengajuan masuk dan surat keluar"
        active-menu="dashboard"
    >
        <Head title="Dashboard Admin" />

        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="stat in statCards"
                :key="stat.label"
                class="rounded-xl border border-slate-200/80 bg-white p-3 shadow-[0_1px_2px_rgba(15,23,42,0.03)]"
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
                <p class="mt-1 text-2xl font-bold text-slate-900">
                    {{ String(stat.value).padStart(2, '0') }}
                </p>
            </div>
        </div>

        <!-- Main grid -->
        <div class="grid gap-6 xl:grid-cols-[1fr_300px]">
            <!-- Tabel surat -->
            <div
                class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-[0_1px_2px_rgba(15,23,42,0.03)]"
            >
                <!-- Toolbar -->
                <div
                    class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">
                            Daftar Pengajuan Masuk
                        </h2>
                        <p class="mt-0.5 text-xs text-slate-400">
                            {{ surats?.from ?? 0 }} - {{ surats?.to ?? 0 }} dari
                            {{ surats?.total ?? 0 }}
                        </p>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50">
                            <tr
                                class="text-[10px] font-semibold tracking-widest text-slate-400 uppercase"
                            >
                                <th class="px-5 py-3">Pemohon</th>
                                <th class="px-5 py-3">Jenis Surat</th>
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="surats?.data?.length === 0">
                                <td
                                    colspan="5"
                                    class="px-5 py-12 text-center text-sm text-slate-400"
                                >
                                    Belum ada pengajuan user yang menunggu
                                    proses.
                                </td>
                            </tr>
                            <tr
                                v-for="item in surats?.data ?? []"
                                :key="item.id"
                                class="border-t border-slate-100 text-sm transition-colors hover:bg-slate-50/50"
                            >
                                <td class="px-5 py-3.5">
                                    <p
                                        class="text-xs font-semibold text-slate-900"
                                    >
                                        {{ item.pemohon?.name ?? '-' }}
                                    </p>
                                    <p
                                        class="font-mono text-[10px] text-slate-400"
                                    >
                                        {{ item.pemohon?.nim ?? '-' }}
                                    </p>
                                </td>
                                <td
                                    class="max-w-[160px] truncate px-5 py-3.5 text-xs text-slate-600"
                                >
                                    {{ item.jenisSurat?.nama ?? '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-400">
                                    {{
                                        formatDate(
                                            item.tanggal_pengajuan ??
                                                item.created_at,
                                        )
                                    }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span
                                        class="rounded-full px-2 py-1 text-[10px] font-semibold"
                                        :class="statusClass(item.status)"
                                    >
                                        {{ statusLabel(item.status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div
                                        class="flex items-center justify-end gap-1.5"
                                    >
                                        <Link
                                            :href="`/admin/surat/${item.id}`"
                                            title="Lihat detail"
                                            class="grid size-7 place-items-center rounded-lg bg-slate-100 text-slate-500 transition-colors hover:bg-slate-200"
                                        >
                                            <Eye class="size-3.5" />
                                        </Link>
                                        <button
                                            v-if="item.can_approve"
                                            type="button"
                                            title="Validasi & teruskan"
                                            :disabled="approvingId === item.id"
                                            class="grid size-7 place-items-center rounded-lg bg-blue-50 text-blue-600 transition-colors hover:bg-blue-100 disabled:opacity-50"
                                            @click="approveSurat(item.id)"
                                        >
                                            <CheckCircle2 class="size-3.5" />
                                        </button>
                                        <Link
                                            v-else-if="item.can_edit"
                                            :href="`/admin/surat/${item.id}/edit?return_to=/admin/dashboard`"
                                            title="Lengkapi data"
                                            class="grid size-7 place-items-center rounded-lg bg-amber-50 text-amber-600 transition-colors hover:bg-amber-100"
                                        >
                                            <FileEdit class="size-3.5" />
                                        </Link>
                                        <button
                                            v-if="item.status === 'pending'"
                                            type="button"
                                            title="Tolak (beri komentar)"
                                            class="grid size-7 place-items-center rounded-lg bg-red-50 text-red-600 transition-colors hover:bg-red-100"
                                            @click="openRejectModal(item.id)"
                                        >
                                            <XCircle class="size-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

            <!-- Kanan: Ringkasan & Info -->
            <div class="space-y-4">
                <!-- Aktivitas Terbaru -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-[0_1px_2px_rgba(15,23,42,0.03)]">
                    <h3
                        class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-900"
                    >
                        <Clock3 class="size-4 text-blue-500" /> Ringkasan Cepat
                    </h3>
                    <div class="space-y-3">
                        <template v-if="quickSubmissions.length">
                            <div
                                v-for="item in quickSubmissions"
                                :key="item.id"
                                class="rounded-xl border border-slate-100 bg-slate-50/70 p-3"
                            >
                                <div class="flex items-start gap-2">
                                    <div
                                        class="mt-1.5 size-1.5 rounded-full bg-blue-500"
                                    />
                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="truncate text-xs font-medium text-slate-700"
                                        >
                                            {{ item.pemohon?.name ?? '-' }}
                                        </p>
                                        <p class="text-[10px] text-slate-400">
                                            {{ item.jenisSurat?.nama ?? '-' }}
                                        </p>
                                        <div class="mt-1 flex items-center gap-2">
                                            <span
                                                class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                                :class="
                                                    statusClass(item.status)
                                                "
                                            >
                                                {{ statusLabel(item.status) }}
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
                            :href="props.links.submissionsIndex"
                            class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 transition-colors hover:text-blue-700"
                        >
                            Lihat Selengkapnya
                            <ArrowRight class="size-3.5" />
                        </Link>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal Tolak Pengajuan (komentar) -->
        <Transition name="fade">
            <div
                v-if="rejectModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
                @click.self="closeRejectModal"
            >
                <div
                    class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl"
                >
                    <div class="mb-4 flex items-center gap-3">
                        <div
                            class="grid size-10 shrink-0 place-items-center rounded-xl bg-red-50"
                        >
                            <AlertTriangle class="size-5 text-red-500" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">
                                Tolak Pengajuan
                            </h3>
                            <p class="text-xs text-slate-400">
                                Berikan komentar/alasan penolakan untuk pemohon.
                            </p>
                        </div>
                        <button
                            type="button"
                            class="ml-auto rounded-lg p-1 text-slate-400 hover:bg-slate-100"
                            @click="closeRejectModal"
                        >
                            <X class="size-4" />
                        </button>
                    </div>
                    <textarea
                        v-model="rejectForm.reason"
                        rows="4"
                        placeholder="Jelaskan alasan penolakan..."
                        class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100"
                    />
                    <p
                        v-if="rejectForm.errors.reason"
                        class="mt-1 text-xs text-red-500"
                    >
                        {{ rejectForm.errors.reason }}
                    </p>
                    <div class="mt-4 flex justify-end gap-2">
                        <button
                            type="button"
                            class="fast-btn fast-btn-outline rounded-xl px-4 py-2 text-sm font-medium text-slate-600"
                            @click="closeRejectModal"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            class="fast-btn fast-btn-danger rounded-xl px-4 py-2 text-sm"
                            :disabled="
                                rejectForm.processing ||
                                !rejectForm.reason.trim()
                            "
                            @click="submitReject"
                        >
                            {{
                                rejectForm.processing
                                    ? 'Memproses...'
                                    : 'Tolak Pengajuan'
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

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
                class="fixed top-5 left-1/2 z-50 w-[calc(100%-2rem)] max-w-sm -translate-x-1/2 rounded-xl border px-4 py-3 shadow-lg"
                :class="
                    toastVariant === 'success'
                        ? 'border-blue-200 bg-blue-50 text-blue-800'
                        : 'border-red-200 bg-red-50 text-red-800'
                "
            >
                <div class="flex items-center gap-2.5">
                    <CheckCircle2
                        v-if="toastVariant === 'success'"
                        class="size-5 shrink-0 text-blue-500"
                    />
                    <AlertCircle
                        v-else
                        class="size-5 shrink-0 text-red-500"
                    />
                    <p class="text-sm font-medium">{{ toastMessage }}</p>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>

<!-- <style scoped> //error
.badge-green  { @apply bg-emerald-100 text-emerald-700; }
.badge-red    { @apply bg-red-100 text-red-700; }
.badge-amber  { @apply bg-amber-100 text-amber-700; }
.badge-blue   { @apply bg-blue-100 text-blue-700; }
.badge-indigo { @apply bg-indigo-100 text-indigo-700; }
</style> -->

<!-- <style scoped>  //opsi pertama
@reference "tailwindcss";

.badge-green  { @apply bg-emerald-100 text-emerald-700; }
.badge-red    { @apply bg-red-100 text-red-700; }
.badge-amber  { @apply bg-amber-100 text-amber-700; }
.badge-blue   { @apply bg-blue-100 text-blue-700; }
.badge-indigo { @apply bg-indigo-100 text-indigo-700; }
</style> -->
