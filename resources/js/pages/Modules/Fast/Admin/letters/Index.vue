<script setup lang="ts">
// resources/js/pages/Modules/Fast/Admin/letters/Index.vue
import AdminLayout from '@/layouts/Modules/Fast/AdminLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import {
    Eye,
    CheckCircle2,
    AlertCircle,
    Search,
    XCircle,
    AlertTriangle,
    X,
    FileEdit,
    BarChart3,
    ChevronDown,
} from 'lucide-vue-next';
type SuratItem = {
    id: number;
    status: string;
    can_approve?: boolean;
    revision_label?: string | null;
    can_edit?: boolean;
    nomor_surat?: string | null;
    keperluan?: string | null;
    tanggal_pengajuan?: string | null;
    created_at?: string | null;
    tanggal_selesai?: string | null;
    pemohon?: { name?: string | null; nim?: string | null } | null;
    jenisSurat?: {
        id?: number | null;
        nama?: string | null;
        category?: { nama?: string | null } | null;
    } | null;
};
type Paginated = {
    data: SuratItem[];
    from?: number | null;
    to?: number | null;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};
type PageProps = {
    flash?: {
        success?: string;
        error?: string;
    };
};
type Summary = {
    total: number;
    pending: number;
    finished: number;
    rejected: number;
};
const props = defineProps<{
    surats: Paginated;
    summary: Summary;
    filters: { status?: string; search?: string; jenis_surat_id?: string };
    categories: Array<{ id: number; nama: string }>;
}>();
const page = usePage<PageProps>();
const summary = props.summary;
const defaultStatus = 'pending';
const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? defaultStatus);
const jenisSuratId = ref(props.filters.jenis_surat_id ?? '');
const isFilterActive = computed(
    () =>
        search.value !== '' ||
        status.value !== defaultStatus ||
        jenisSuratId.value !== '',
);
const toastMessage = ref('');
const toastVariant = ref<'success' | 'error'>('success');

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

const statusFilters = computed(() => [
    {
        key: 'pending',
        label: 'Pending',
        color: 'amber' as const,
    },
    {
        key: 'rejected_admin',
        label: 'Ditolak',
        color: 'red' as const,
    },
    { key: 'all', label: 'Semua', color: 'blue' as const },
]);

function applyFilter() {
    router.get(
        '/admin/surat',
        {
            search: search.value || undefined,
            status: status.value || undefined,
            jenis_surat_id: jenisSuratId.value || undefined,
        },
        { preserveState: true, replace: true },
    );
}
function resetFilter() {
    search.value = '';
    status.value = defaultStatus;
    jenisSuratId.value = '';
    applyFilter();
}
const rejectModalOpen = ref(false);
const rejectTargetId = ref<number | null>(null);
const rejectForm = useForm({ reason: '' });
const approvingId = ref<number | null>(null);
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
function formatDate(d?: string | null) {
    if (!d) return '';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(d));
}
function statusLabel(item: SuratItem) {
    const map: Record<string, string> = {
        pending: 'Pending',
        validated_admin: 'Diteruskan ke Approver',
        approved_kaprodi: 'Disetujui Kaprodi',
        approved_dekan: 'Disetujui Dekan',
        revision_requested: 'Ditolak',
        finished: 'Selesai',
        rejected_admin: 'Ditolak',
        rejected_approver: 'Ditolak Pimpinan',
    };
    return map[item.status] ?? item.status;
}
function statusClass(s: string) {
    if (s === 'finished') return 'bg-emerald-50 text-emerald-700';
    if (s === 'revision_requested') return 'bg-amber-50 text-amber-700';
    if (s === 'rejected_admin' || s === 'rejected_approver')
        return 'bg-red-50 text-red-700';
    if (s.startsWith('approved')) return 'bg-emerald-50 text-emerald-700';
    if (s === 'validated_admin') return 'bg-slate-100 text-slate-700';
    return 'bg-amber-50 text-amber-700';
}
function initials(name?: string | null) {
    if (!name) return '?';
    return name
        .split(' ')
        .map((w) => w[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
}
</script>
<template>
    <AdminLayout
        title="Pengajuan Masuk"
        subtitle="Permohonan user yang menunggu proses admin"
        active-menu="letters.index"
        :breadcrumbs="[{ label: 'Pengajuan Masuk' }]"
    >
        <Head title="Pengajuan Masuk" />
        <!-- Top bar: search + tombol -->
        <div class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
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
                <div class="relative w-full lg:w-56">
                    <select
                        v-model="jenisSuratId"
                        class="h-11 w-full appearance-none rounded-2xl border border-slate-200 bg-slate-50 pr-8 pl-4 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100"
                        @change="applyFilter"
                    >
                        <option value="">Semua Kategori</option>
                        <option
                            v-for="j in categories"
                            :key="j.id"
                            :value="String(j.id)"
                        >
                            {{ j.nama }}
                        </option>
                    </select>
                    <ChevronDown
                        class="pointer-events-none absolute top-1/2 right-3.5 size-3.5 -translate-y-1/2 text-slate-400"
                    />
                </div>
                <button
                    type="button"
                    class="h-11 w-full rounded-2xl border border-blue-200 bg-blue-50 px-5 text-sm font-medium text-blue-700 transition-colors hover:border-blue-300 hover:bg-blue-100 hover:text-blue-800 sm:w-auto"
                    @click="resetFilter"
                >
                    Reset Filter
                </button>
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-2">
                <button
                    v-for="filter in statusFilters"
                    :key="filter.key || 'all'"
                    type="button"
                    class="rounded-full border px-3 py-1.5 text-xs font-medium transition-colors"
                    :class="
                        status === filter.key
                            ? filter.color === 'red'
                                ? 'border-red-500 bg-red-500 text-white shadow-sm'
                                : filter.color === 'amber'
                                  ? 'border-amber-500 bg-amber-500 text-white shadow-sm'
                                  : 'border-blue-500 bg-blue-500 text-white shadow-sm'
                            : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300'
                    "
                    @click="
                        status = filter.key;
                        applyFilter();
                    "
                >
                    {{ filter.label }}
                </button>
            </div>
        </div>
        <!-- Card list with colored left border stripe -->
        <div class="space-y-2">
            <div
                v-if="surats.data.length === 0"
                class="flex flex-col items-center gap-3 py-16 text-center"
            >
                <div
                    class="grid size-16 place-items-center rounded-2xl border border-slate-100 bg-slate-50"
                >
                    <BarChart3 class="size-8 text-slate-200" />
                </div>
                <p class="text-sm font-medium text-slate-400">
                    Tidak ada pengajuan
                </p>
                <p class="text-xs text-slate-300">
                    Coba ubah filter atau cari dengan kata kunci lain
                </p>
            </div>
            <div
                v-for="item in surats.data"
                :key="item.id"
                        class="group relative flex items-start gap-0 overflow-hidden rounded-xl border border-slate-200 bg-white transition-all hover:shadow-md"
                    :class="[
                    item.status === 'finished'
                        ? 'hover:border-blue-300'
                        : item.status.startsWith('approved')
                          ? 'hover:border-sky-300'
                          : item.status === 'rejected_admin' ||
                              item.status === 'rejected_approver'
                            ? 'hover:border-red-300'
                            : item.status === 'revision_requested'
                              ? 'hover:border-amber-300'
                              : 'hover:border-indigo-300',
                ]"
            >
                <!-- Colored left stripe -->
                <div
                    class="w-1.5 shrink-0 self-stretch"
                    :class="[
                        item.status === 'finished'
                            ? 'bg-blue-400'
                            : item.status.startsWith('approved')
                              ? 'bg-sky-400'
                              : item.status === 'rejected_admin' ||
                                  item.status === 'rejected_approver'
                                ? 'bg-red-400'
                                : item.status === 'revision_requested'
                                  ? 'bg-amber-400'
                                  : 'bg-indigo-400',
                    ]"
                />
                <div class="flex-1 p-4">
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center"
                    >
                        <!-- Main info: avatar + name + surat -->
                        <div class="flex min-w-0 flex-1 items-center gap-3">
                            <div
                                class="grid size-9 shrink-0 place-items-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-500"
                            >
                                {{ initials(item.pemohon?.name) }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p
                                        class="truncate text-sm font-semibold text-slate-900"
                                    >
                                        {{ item.pemohon?.name ?? '' }}
                                    </p>
                                    <span
                                        class="inline-block rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                        :class="statusClass(item.status)"
                                    >
                                        {{ statusLabel(item) }}
                                    </span>
                                </div>
                                <div class="mt-0.5 flex items-center gap-2">
                                    <p class="text-xs text-slate-500">
                                        {{ item.jenisSurat?.nama ?? '' }}
                                    </p>
                                    <p
                                        class="font-mono text-[10px] text-slate-400"
                                    >
                                        {{ item.nomor_surat ?? '' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <!-- Date -->
                        <div class="shrink-0 text-right sm:text-left">
                            <p class="text-[10px] text-slate-400">
                                {{
                                    formatDate(
                                        item.tanggal_pengajuan ??
                                            item.created_at,
                                    )
                                }}
                            </p>
                        </div>
                        <!-- Actions -->
                        <div class="flex shrink-0 items-center gap-2">
                            <Link
                                :href="`/admin/surat/${item.id}`"
                                class="fast-btn fast-btn-outline flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-medium text-slate-600"
                                title="Lihat Detail"
                            >
                                <Eye class="size-3" /> Detail
                            </Link>
                            <button
                                v-if="item.can_approve"
                                type="button"
                                :disabled="approvingId === item.id"
                                class="fast-btn fast-btn-primary flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-medium"
                                title="Validasi & Teruskan"
                                @click="approveSurat(item.id)"
                            >
                                <CheckCircle2 class="size-3" /> Proses
                            </button>
                            <Link
                                v-if="item.can_edit"
                                :href="`/admin/surat/${item.id}/edit?return_to=/admin/surat`"
                                class="fast-btn fast-btn-danger flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-medium"
                                :title="
                                    item.status === 'pending'
                                        ? 'Lengkapi Data & Validasi'
                                        : 'Edit & Teruskan'
                                "
                            >
                                <CheckCircle2 class="size-3" />
                                {{
                                    item.status === 'pending'
                                        ? 'Lengkapi'
                                        : 'Proses Ulang'
                                }}
                            </Link>
                            <button
                                v-if="item.status === 'pending'"
                                type="button"
                                class="fast-btn fast-btn-danger flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-medium"
                                title="Tolak"
                                @click="openRejectModal(item.id)"
                            >
                                <XCircle class="size-3" /> Tolak
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Pagination -->
        <div
            v-if="surats.links.length > 3"
            class="mt-5 flex flex-wrap items-center gap-1.5"
        >
                <Link
                    v-for="link in surats.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
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
                                Berikan komentar atau alasan penolakan untuk
                                pemohon.
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
