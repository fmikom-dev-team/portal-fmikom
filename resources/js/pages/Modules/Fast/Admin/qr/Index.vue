<script setup lang="ts">
// resources/js/pages/Modules/Fast/Admin/qr/Index.vue
import AdminLayout from '@/layouts/Modules/Fast/AdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    Search,
    QrCode,
    ShieldOff,
    Eye,
    XCircle,
    ChevronDown,
    Download,
} from 'lucide-vue-next';
type SuratItem = {
    id: number;
    status: string;
    nomor_surat?: string | null;
    qr_token?: string | null;
    qr_status?: string;
    qr_revoked_at?: string | null;
    created_at?: string | null;
    pemohon?: { name?: string | null } | null;
    jenisSurat?: { nama?: string | null } | null;
};
type Paginated = {
    data: SuratItem[];
    from?: number | null;
    to?: number | null;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};
const props = defineProps<{
    surats: Paginated;
    filters: { search?: string; status?: string };
}>();
const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const isFilterActive = computed(
    () => search.value !== '' || status.value !== '',
);
// Modal revoke
const showRevokeModal = ref(false);
const targetId = ref<number | null>(null);
const targetNama = ref('');

// Modal QR preview
const showQrModal = ref(false);
const selectedQrToken = ref<string | null>(null);
const selectedQrNomor = ref('');

const revokeForm = useForm({ alasan: '' });

function openQrModal(item: SuratItem) {
    selectedQrToken.value = item.qr_token ?? null;
    selectedQrNomor.value =
        item.nomor_surat ?? item.jenisSurat?.nama ?? 'Surat';
    showQrModal.value = true;
}

function openRevoke(item: SuratItem) {
    targetId.value = item.id;
    targetNama.value = item.jenisSurat?.nama ?? 'Surat';
    revokeForm.reset();
    showRevokeModal.value = true;
}

function submitRevoke() {
    if (!targetId.value) return;

    revokeForm.post(`/admin/qr/${targetId.value}/revoke`, {
        onSuccess: () => {
            showRevokeModal.value = false;
        },
    });
}

function applyFilter() {
    router.get(
        '/admin/qr',
        {
            search: search.value || undefined,
            status: status.value || undefined,
        },
        { preserveState: true, replace: true },
    );
}

function formatDate(d?: string | null) {
    if (!d) return '-';

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(d));
}

function qrStatusClass(s?: string) {
    if (s === 'revoked') return 'bg-red-100 text-red-700 border-red-200';
    if (s === 'expired') return 'bg-slate-100 text-slate-500 border-slate-200';
    return 'bg-blue-100 text-blue-700 border-blue-200';
}

function qrStatusLabel(s?: string) {
    if (s === 'revoked') return 'Dicabut';
    if (s === 'expired') return 'Kedaluwarsa';
    return 'Aktif';
}
</script>
<template>
    <AdminLayout
        title="Kelola QR Code"
        subtitle="Pantau dan cabut QR Code surat"
        active-menu="qr"
        :breadcrumbs="[{ label: 'Kelola QR Code' }]"
    >
        <Head title="Kelola QR Code" />
        <!-- Hero -->
        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-6">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <h2 class="mt-1 text-xl font-bold text-slate-900">
                        Verifikasi & Keamanan
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Pantau status QR Code surat keluar. Cabut QR jika surat
                        dibatalkan.
                    </p>
                </div>
                <div class="shrink-0 text-right">
                    <p class="text-2xl font-bold text-slate-900">
                        {{ surats.total }}
                    </p>
                    <p class="text-xs text-slate-400">Total Surat</p>
                </div>
            </div>
        </div>
        <!-- Filter -->
        <div class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <div class="relative flex-1">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                    />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nomor surat, pemohon, atau jenis surat..."
                        class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 pr-4 pl-10 text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100"
                        @keyup.enter="applyFilter"
                    />
                </div>
                <div class="relative w-full lg:w-56">
                    <select
                        v-model="status"
                        class="h-11 w-full appearance-none rounded-2xl border border-slate-200 bg-slate-50 pr-8 pl-4 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100"
                        @change="applyFilter"
                    >
                        <option value="">Semua Status QR</option>
                        <option value="active">Aktif</option>
                        <option value="revoked">Dicabut</option>
                    </select>
                    <ChevronDown
                        class="pointer-events-none absolute top-1/2 right-3.5 size-3.5 -translate-y-1/2 text-slate-400"
                    />
                </div>
                <button
                    type="button"
                    class="fast-btn fast-btn-soft h-11 w-full px-5 text-sm font-medium text-blue-700 sm:w-auto"
                    @click="
                        search = '';
                        status = '';
                        applyFilter();
                    "
                >
                    Reset Filter
                </button>
                <p class="text-xs text-slate-400 lg:ml-auto">
                    {{ surats.from ?? 0 }}-{{ surats.to ?? 0 }} dari
                    {{ surats.total }} surat
                </p>
            </div>
        </div>
        <!-- Card rows -->
        <div class="space-y-3">
            <!-- Empty state -->
            <div
                v-if="surats.data.length === 0"
                class="flex flex-col items-center justify-center gap-4 rounded-2xl border border-dashed border-slate-300 bg-white py-20 text-center"
            >
                <div
                    class="grid size-16 place-items-center rounded-2xl border-2 border-blue-200 bg-blue-50 text-blue-600"
                >
                    <QrCode class="size-8" stroke-width="2" />
                </div>
                <p class="text-sm text-slate-400">
                    Belum ada surat dengan QR Code
                </p>
            </div>
            <div
                v-for="item in surats.data"
                :key="item.id"
                class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 transition-all hover:border-blue-200 hover:shadow-md"
            >
                <!-- Left stripe -->
                <div
                    class="absolute top-0 bottom-0 left-0 w-1"
                    :class="
                        item.qr_status === 'revoked'
                            ? 'bg-red-400'
                            : item.qr_status === 'expired'
                              ? 'bg-slate-400'
                              : 'bg-blue-400'
                    "
                />
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="grid size-11 place-items-center rounded-2xl border-2"
                            :class="
                                item.qr_status === 'revoked'
                                    ? 'border-red-200 bg-red-50 text-red-600'
                                    : item.qr_status === 'expired'
                                      ? 'border-slate-200 bg-slate-100 text-slate-500'
                                      : 'border-blue-200 bg-blue-50 text-blue-600'
                            "
                        >
                            <QrCode class="size-5" stroke-width="2" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-900">
                                {{ item.jenisSurat?.nama ?? '-' }}
                            </p>
                            <p class="mt-0.5 font-mono text-xs text-slate-400">
                                {{ item.nomor_surat ?? '-' }}
                            </p>
                        </div>
                    </div>
                    <span
                        class="shrink-0 rounded-full border px-2.5 py-1 text-[10px] font-semibold"
                        :class="qrStatusClass(item.qr_status)"
                    >
                        {{ qrStatusLabel(item.qr_status) }}
                    </span>
                </div>
                <div
                    class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-400"
                >
                    <span class="flex items-center gap-1.5">
                        <span class="font-medium text-slate-500">Pemohon:</span>
                        {{ item.pemohon?.name ?? '-' }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="font-medium text-slate-500">Dibuat:</span>
                        {{ formatDate(item.created_at) }}
                    </span>
                    <span
                        v-if="item.qr_revoked_at"
                        class="flex items-center gap-1.5 text-red-400"
                    >
                        <span class="font-medium">Dicabut:</span>
                        {{ formatDate(item.qr_revoked_at) }}
                    </span>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <Link
                        :href="`/admin/surat/${item.id}`"
                        class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-50"
                        title="Lihat Surat"
                    >
                        <Eye class="size-3.5 text-slate-500" /> Lihat Surat
                    </Link>
                    <button
                        v-if="item.qr_token"
                        type="button"
                        class="flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 transition-colors hover:bg-blue-100"
                        title="Lihat QR"
                        @click="openQrModal(item)"
                    >
                        <QrCode class="size-3.5" /> Lihat QR
                    </button>
                    <button
                        v-if="item.qr_status !== 'revoked'"
                        type="button"
                        class="flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition-colors hover:bg-red-100"
                        title="Cabut QR"
                        @click="openRevoke(item)"
                    >
                        <ShieldOff class="size-3.5" /> Cabut QR
                    </button>
                    <div
                        v-else
                        class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-400"
                        title="QR sudah dicabut"
                    >
                        <XCircle class="size-3.5" /> Dicabut
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
                :href="link.url || '#'"
                class="rounded-lg px-3 py-1.5 text-xs font-medium transition-colors"
                :class="[
                    link.active
                        ? 'bg-blue-600 text-white'
                        : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50',
                    !link.url ? 'pointer-events-none opacity-40' : '',
                ]"
                v-html="link.label"
            />
        </div>
        <!-- Modal QR Preview -->
        <Transition name="fade">
            <div
                v-if="showQrModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
                @click.self="showQrModal = false"
            >
                <div
                    class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-xl"
                >
                    <h3 class="text-base font-bold text-slate-900">QR Code</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ selectedQrNomor }}
                    </p>
                    <div class="mt-5 flex justify-center">
                        <div
                            class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                        >
                            <img
                                v-if="selectedQrToken"
                                :src="`/qr-image/${selectedQrToken}`"
                                alt="QR Code"
                                class="size-56 object-contain"
                            />
                        </div>
                    </div>
                    <div class="mt-5 flex justify-center gap-2">
                        <a
                            v-if="selectedQrToken"
                            :href="`/qr-image/${selectedQrToken}`"
                            download="qr-code.svg"
                        class="fast-btn fast-btn-outline items-center gap-1.5 px-4 py-2 text-sm font-medium text-slate-700"
                        >
                            <Download class="size-4" /> Unduh SVG
                        </a>
                        <a
                            v-if="selectedQrToken"
                            :href="`/verifikasi-qr/${selectedQrToken}`"
                            target="_blank"
                        class="fast-btn fast-btn-primary items-center gap-1.5 px-4 py-2 text-sm font-semibold"
                        >
                            <Eye class="size-4" /> Verifikasi
                        </a>
                    </div>
                    <button
                        type="button"
                        class="mt-3 text-xs font-medium text-slate-400 transition-colors hover:text-slate-600"
                        @click="showQrModal = false"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </Transition>
        <!-- Modal Revoke -->
        <Transition name="fade">
            <div
                v-if="showRevokeModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
                @click.self="showRevokeModal = false"
            >
                <div
                    class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl"
                >
                    <h3 class="text-base font-bold text-slate-900">
                        Cabut QR Code
                    </h3>
                    <p class="mt-1 text-sm text-slate-500">{{ targetNama }}</p>
                    <div
                        class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700"
                    >
                        Setelah dicabut, QR Code tidak bisa diaktifkan kembali.
                        Scan akan menampilkan "Dokumen Tidak Valid".
                    </div>
                    <div class="mt-4">
                        <label class="block space-y-1.5">
                            <span class="text-xs font-medium text-slate-700"
                                >Alasan Pencabutan (opsional)</span
                            >
                            <input
                                v-model="revokeForm.alasan"
                                type="text"
                                class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-red-400"
                                placeholder="Contoh: Surat dibatalkan"
                            />
                        </label>
                    </div>
                    <div class="mt-5 flex justify-end gap-2">
                        <button
                            type="button"
                            class="fast-btn fast-btn-outline rounded-xl px-4 py-2 text-sm font-medium text-slate-600"
                            @click="showRevokeModal = false"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            class="fast-btn fast-btn-danger rounded-xl px-4 py-2 text-sm"
                            :disabled="revokeForm.processing"
                            @click="submitRevoke"
                        >
                            {{
                                revokeForm.processing
                                    ? 'Memproses...'
                                    : 'Ya, Cabut QR'
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>
<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
