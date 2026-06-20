<script setup lang="ts">
// resources/js/pages/admin/qr/Index.vue
import AdminLayout from '@/layouts/Modules/Fast/AdminLayout.vue';
import DocumentPreviewModal from '@/components/DocumentPreviewModal.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Search, QrCode, ShieldOff, Eye, XCircle, X, ExternalLink, ZoomIn, ZoomOut, RotateCcw, FileText } from 'lucide-vue-next';

type SuratItem = {
    id: number; status: string;
    nomor_surat?: string | null; qr_token?: string | null;
    qr_status?: string; qr_revoked_at?: string | null;
    created_at?: string | null;
    pemohon?: { name?: string | null } | null;
    jenisSurat?: { nama?: string | null } | null;
};
type Paginated = {
    data: SuratItem[];
    from?: number | null; to?: number | null; total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

const props = defineProps<{
    surats: Paginated;
    filters: { search?: string; status?: string };
}>();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

// ── Modal revoke ──────────────────────────────────────────────────────────
const showRevokeModal = ref(false);
const targetId        = ref<number | null>(null);
const targetNama      = ref('');
const revokeForm = useForm({ alasan: '' });

function openRevoke(item: SuratItem) {
    targetId.value   = item.id;
    targetNama.value = item.jenisSurat?.nama ?? 'Surat';
    revokeForm.reset();
    showRevokeModal.value = true;
}
function submitRevoke() {
    if (!targetId.value) return;
    revokeForm.post(`/admin/qr/${targetId.value}/revoke`, {
        onSuccess: () => { showRevokeModal.value = false; },
    });
}

function applyFilter() {
    router.get('/admin/qr', {
        search: search.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true, replace: true });
}
function formatDate(d?: string | null) {
    if (!d) return '-';
    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(d));
}
function qrStatusClass(s?: string) {
    if (s === 'revoked') return 'bg-red-100 text-red-700';
    if (s === 'expired') return 'bg-slate-100 text-slate-500';
    return 'bg-emerald-100 text-emerald-700';
}
function qrStatusLabel(s?: string) {
    if (s === 'revoked') return 'Dicabut';
    if (s === 'expired') return 'Kedaluwarsa';
    return 'Aktif';
}

// ── Viewer state ──────────────────────────────────────────────────────────
// mode: 'html' = iframe (template/generated HTML), 'pdf' = PdfViewer binary
const viewerOpen   = ref(false);
const viewerUrl    = ref<string | null>(null);
const viewerTitle  = ref('');
const viewerMode   = ref<'html' | 'pdf'>('html');
const viewerNomor  = ref<string | null>(null);
const viewerStatus = ref('');

// HTML iframe zoom
const iframeZoom  = ref(100);
const iframeLoad  = ref(true);
const iframeError = ref(false);

function openViewer(item: SuratItem) {
    iframeZoom.value  = 100;
    iframeLoad.value  = true;
    iframeError.value = false;

    if (item.status === 'finished') {
        // Ada PDF binary → pakai PdfViewer
        viewerUrl.value   = `/documents/surat/${item.id}/pdf`;
        viewerMode.value  = 'pdf';
    } else {
        // Belum generate → pakai iframe HTML preview
        viewerUrl.value   = `/documents/surat/${item.id}/template-preview`;
        viewerMode.value  = 'html';
    }
    viewerTitle.value  = `${item.jenisSurat?.nama ?? 'Surat'} — ${item.nomor_surat ?? 'Draft'}`;
    viewerNomor.value  = item.nomor_surat ?? null;
    viewerStatus.value = item.status;
    viewerOpen.value   = true;
}

function closeViewer() {
    viewerOpen.value = false;
    setTimeout(() => { viewerUrl.value = null; }, 200);
}

function openInNewTab() {
    if (viewerUrl.value) window.open(viewerUrl.value, '_blank');
}

// QR belum aktif saat PDF tapi status bukan finished
const showQrWarning = computed(() =>
    viewerMode.value === 'pdf' && viewerStatus.value !== 'finished'
);
</script>

<template>
    <AdminLayout
        title="Kelola QR Code"
        subtitle="Pantau dan cabut QR Code surat"
        active-menu="qr"
        :breadcrumbs="[{ label: 'Kelola QR Code' }]"
    >
        <Head title="Kelola QR Code" />

        <!-- Tabel -->
        <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
            <!-- Filter -->
            <div class="flex flex-wrap gap-2 border-b border-slate-100 px-5 py-4">
                <div class="relative">
                    <Search class="pointer-events-none absolute left-3 top-1/2 size-3.5 -translate-y-1/2 text-slate-400" />
                    <input v-model="search" type="text" placeholder="Cari nomor surat, nama..."
                        class="h-9 w-52 rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-3 text-xs text-slate-700 placeholder-slate-400 outline-none focus:border-emerald-400"
                        @keyup.enter="applyFilter" />
                </div>
                <select v-model="status"
                    class="h-9 rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs text-slate-700 outline-none focus:border-emerald-400">
                    <option value="">Semua Status QR</option>
                    <option value="active">Aktif</option>
                    <option value="revoked">Dicabut</option>
                </select>
                <button type="button"
                    class="fast-btn fast-btn-primary h-9 px-4 text-xs"
                    @click="applyFilter">Terapkan</button>
                <button type="button"
                    class="fast-btn fast-btn-outline h-9 px-4 text-xs text-slate-500"
                    @click="search = ''; status = ''; applyFilter()">Reset</button>
                <p class="ml-auto flex items-center text-xs text-slate-400">
                    {{ surats.from ?? 0 }}–{{ surats.to ?? 0 }} dari {{ surats.total }} surat
                </p>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50">
                        <tr class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">
                            <th class="px-5 py-3">Surat</th>
                            <th class="px-5 py-3">Pemohon</th>
                            <th class="px-5 py-3">Nomor Surat</th>
                            <th class="px-5 py-3">Dibuat</th>
                            <th class="px-5 py-3">Status QR</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="surats.data.length === 0">
                            <td colspan="6" class="px-5 py-12 text-center text-sm text-slate-400">
                                Belum ada surat dengan QR Code.
                            </td>
                        </tr>
                        <tr v-for="item in surats.data" :key="item.id"
                            class="border-t border-slate-100 text-sm hover:bg-slate-50/50 transition-colors">
                            <td class="px-5 py-3.5">
                                <p class="text-xs font-medium text-slate-700 max-w-[160px] truncate">
                                    {{ item.jenisSurat?.nama ?? '-' }}
                                </p>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-600">{{ item.pemohon?.name ?? '-' }}</td>
                            <td class="px-5 py-3.5 font-mono text-xs text-slate-500">{{ item.nomor_surat ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-xs text-slate-400">{{ formatDate(item.created_at) }}</td>
                            <td class="px-5 py-3.5">
                                <div class="space-y-1">
                                    <span class="rounded-full px-2 py-1 text-[10px] font-semibold"
                                        :class="qrStatusClass(item.qr_status)">
                                        {{ qrStatusLabel(item.qr_status) }}
                                    </span>
                                    <p v-if="item.qr_revoked_at" class="text-[10px] text-red-400">
                                        Dicabut: {{ formatDate(item.qr_revoked_at) }}
                                    </p>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button"
                                        class="grid size-7 place-items-center rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition-colors"
                                        :title="item.status === 'finished' ? 'Lihat PDF' : 'Preview Template'"
                                        @click="openViewer(item)">
                                        <Eye class="size-3.5" />
                                    </button>
                                    <a v-if="item.qr_token"
                                        :href="`/verifikasi-qr/${item.qr_token}`"
                                        target="_blank"
                                        class="grid size-7 place-items-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors"
                                        title="Preview QR">
                                        <QrCode class="size-3.5" />
                                    </a>
                                    <button v-if="item.qr_status !== 'revoked'" type="button"
                                        class="grid size-7 place-items-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-colors"
                                        title="Cabut QR"
                                        @click="openRevoke(item)">
                                        <ShieldOff class="size-3.5" />
                                    </button>
                                    <div v-else
                                        class="grid size-7 place-items-center rounded-lg bg-slate-50 text-slate-300"
                                        title="QR sudah dicabut">
                                        <XCircle class="size-3.5" />
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="surats.links.length > 3"
                class="flex flex-wrap items-center gap-1.5 border-t border-slate-100 px-5 py-3">
                <Link v-for="link in surats.links" :key="link.label"
                    :href="link.url ?? '#'"
                    :prefetch="false"
                    class="rounded-lg px-3 py-1.5 text-xs font-medium transition-colors"
                    :class="[
                        link.active ? 'fast-btn-primary' : 'fast-btn-outline',
                        !link.url ? 'pointer-events-none opacity-40' : '',
                    ]"
                    v-html="link.label" />
            </div>
        </div>

        <!-- ── Modal Revoke ── -->
        <Transition name="fade">
            <div v-if="showRevokeModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
                @click.self="showRevokeModal = false">
                <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
                    <h3 class="text-base font-bold text-slate-900">Cabut QR Code</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ targetNama }}</p>
                    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700">
                        ⚠ Setelah dicabut, QR Code tidak bisa diaktifkan kembali.
                    </div>
                    <div class="mt-4">
                        <label class="block space-y-1.5">
                            <span class="text-xs font-medium text-slate-700">Alasan Pencabutan (opsional)</span>
                            <input v-model="revokeForm.alasan" type="text"
                                class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-red-400"
                                placeholder="Contoh: Surat dibatalkan" />
                        </label>
                    </div>
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button"
                            class="fast-btn fast-btn-outline rounded-xl px-4 py-2 text-sm font-medium text-slate-600"
                            @click="showRevokeModal = false">Batal</button>
                        <button type="button"
                            class="fast-btn fast-btn-danger rounded-xl px-4 py-2 text-sm"
                            :disabled="revokeForm.processing"
                            @click="submitRevoke">
                            {{ revokeForm.processing ? 'Memproses...' : 'Ya, Cabut QR' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <DocumentPreviewModal
            :open="viewerOpen"
            :mode="viewerMode"
            :title="viewerTitle"
            :subtitle="viewerNomor"
            :url="viewerUrl"
            :show-open-in-new-tab="true"
            :show-thumbnails="true"
            :initial-zoom="100"
            :show-html-zoom-controls="true"
            @close="closeViewer"
            @open-new-tab="openInNewTab"
        >
            <template #banner>
                <div
                    v-if="showQrWarning"
                    class="shrink-0 flex items-center gap-2 bg-amber-500/90 px-4 py-2 text-xs font-medium text-white"
                >
                    <svg
                        class="size-4 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    QR Code belum aktif — surat belum divalidasi.
                </div>
            </template>
        </DocumentPreviewModal>
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity .15s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
