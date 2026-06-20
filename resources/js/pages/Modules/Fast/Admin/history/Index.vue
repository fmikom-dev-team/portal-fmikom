<script setup lang="ts">
// File: resources/js/pages/Modules/Fast/Admin/history/Index.vue
import AdminLayout from '@/layouts/Modules/Fast/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    Search,
    Eye,
    Download,
    CheckCircle2,
    XCircle,
    Clock3,
    FileText,
    Calendar,
    FileCheck,
    ChevronDown,
} from 'lucide-vue-next';
type SuratItem = {
    id: number;
    nomor_surat?: string | null;
    status: string;
    keperluan: string;
    tanggal_pengajuan?: string | null;
    tanggal_selesai?: string | null;
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
    filters: { search?: string; status?: string; category_id?: string };
    categories: Array<{ id: number; nama: string }>;
    notif_count_revision_admin?: number;
}>();
const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? 'pending');
const categoryId = ref(props.filters.category_id ?? '');
const isFilterActive = computed(
    () => search.value !== '' || categoryId.value !== '' || status.value !== 'pending',
);
const revisionNotifCount = computed(() => props.notif_count_revision_admin ?? 0);

const statusFilters = computed(() => [
    { key: 'pending', label: 'Pending', color: 'amber' as const },
    { key: 'revisi', label: 'Revisi', color: 'red' as const },
    { key: '', label: 'Semua', color: 'blue' as const },
]);

function applyFilter() {
    router.get(
        '/admin/history',
        {
            search: search.value || undefined,
            status: status.value,
            category_id: categoryId.value || undefined,
        },
        { preserveState: true, replace: true },
    );
}

function resetFilter() {
    search.value = '';
    status.value = 'pending';
    categoryId.value = '';
    applyFilter();
}
function formatDate(d?: string | null) {
    if (!d) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(d));
}
function statusLabel(s: string) {
    const map: Record<string, string> = {
        pending: 'Pending',
        validated_admin: 'Diteruskan ke Approver',
        approved_kaprodi: 'Disetujui Kaprodi',
        approved_dekan: 'Disetujui Dekan',
        revision_requested: 'Revisi',
        finished: 'Selesai',
        rejected: 'Revisi',
        rejected_admin: 'Ditolak',
        rejected_approver: 'Ditolak',
    };
    return map[s] ?? s;
}
function statusIcon(s: string) {
    if (s === 'finished') return FileCheck;
    if (
        s === 'revision_requested' ||
        s === 'rejected' ||
        s.startsWith('rejected_')
    )
        return XCircle;
    if (s.startsWith('approved')) return CheckCircle2;
    if (s === 'validated_admin') return FileCheck;
    return Clock3;
}
function statusColor(s: string) {
    if (s === 'finished')
        return {
            bg: 'bg-emerald-50',
            border: 'border-emerald-200',
            text: 'text-emerald-600',
            line: 'bg-emerald-300',
        };
    if (
        s === 'revision_requested' ||
        s === 'rejected' ||
        s.startsWith('rejected_')
    )
        return {
            bg: 'bg-red-50',
            border: 'border-red-200',
            text: 'text-red-600',
            line: 'bg-red-300',
        };
    if (s.startsWith('approved'))
        return {
            bg: 'bg-emerald-50',
            border: 'border-emerald-200',
            text: 'text-emerald-600',
            line: 'bg-emerald-300',
        };
    if (s === 'validated_admin')
        return {
            bg: 'bg-slate-100',
            border: 'border-slate-200',
            text: 'text-slate-600',
            line: 'bg-slate-300',
        };
    return {
        bg: 'bg-amber-50',
        border: 'border-amber-200',
        text: 'text-amber-600',
        line: 'bg-amber-300',
    };
}
function statusClass(s: string) {
    if (s === 'finished') return 'bg-emerald-50 text-emerald-700';
    if (
        s === 'revision_requested' ||
        s === 'rejected' ||
        s.startsWith('rejected_')
    )
        return 'bg-red-50 text-red-700';
    if (s.startsWith('approved')) return 'bg-emerald-50 text-emerald-700';
    if (s === 'validated_admin') return 'bg-slate-100 text-slate-700';
    return 'bg-amber-50 text-amber-700';
}
</script>
<template>
    <AdminLayout
        title="Riwayat Admin"
        subtitle="Surat keluar buatan admin yang masih dalam proses atau perlu tindak lanjut"
        active-menu="history"
        :breadcrumbs="[{ label: 'Riwayat Admin' }]"
    >
        <Head title="Riwayat Admin" />
        <div
            v-if="revisionNotifCount > 0"
            class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3"
        >
            <div class="flex items-start gap-3">
                <div
                    class="mt-0.5 grid size-8 shrink-0 place-items-center rounded-full bg-red-100 text-red-600"
                >
                    <XCircle class="size-4" />
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-red-700">
                        Ada {{ revisionNotifCount }} surat yang dikembalikan ke admin
                    </p>
                    <p class="mt-0.5 text-xs text-red-600">
                        Surat ini masih perlu revisi atau tindak lanjut sebelum final.
                    </p>
                </div>
            </div>
        </div>
        <!-- Filter bar -->
        <div class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <div class="relative flex-1">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                    />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nomor surat, pemohon, atau keperluan..."
                        class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 pr-4 pl-10 text-sm text-slate-800 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100"
                        @keyup.enter="applyFilter"
                    />
                </div>
                <div class="relative w-full lg:w-56">
                    <select
                        v-model="categoryId"
                        class="h-11 w-full appearance-none rounded-2xl border border-slate-200 bg-slate-50 pr-8 pl-4 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100"
                        @change="applyFilter"
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
                    <ChevronDown
                        class="pointer-events-none absolute top-1/2 right-3.5 size-3.5 -translate-y-1/2 text-slate-400"
                    />
                </div>
                <button
                    type="button"
                    class="fast-btn fast-btn-soft h-11 w-full px-5 text-sm font-medium text-blue-700 sm:w-auto"
                    @click="resetFilter"
                >
                    Reset Filter
                </button>
                <p class="text-xs text-slate-400 lg:ml-auto">
                    {{ surats.from ?? 0 }}-{{ surats.to ?? 0 }} dari
                    {{ surats.total }} surat
                </p>
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
        <!-- Timeline cards -->
        <div class="relative pl-8">
            <!-- Vertical line -->
            <div
                class="absolute top-3 bottom-3 left-[19px] w-px bg-slate-200"
            />
            <div
                v-if="surats.data.length === 0"
                class="flex flex-col items-center gap-2 py-12 text-center"
            >
                <Calendar class="size-8 text-slate-300" />
                <p class="text-sm text-slate-400">Belum ada riwayat surat.</p>
            </div>
            <div
                v-for="(item, idx) in surats.data"
                :key="item.id"
                class="relative mb-4"
            >
                <!-- Timeline dot -->
                <div
                    class="absolute top-0 -left-8 z-10 grid size-10 place-items-center rounded-full border-2 bg-white"
                    :class="statusColor(item.status).border"
                >
                    <component
                        :is="statusIcon(item.status)"
                        :class="['size-5', statusColor(item.status).text]"
                    />
                </div>
                <!-- Card -->
                <div
                    class="rounded-2xl border bg-white p-5 transition-all hover:shadow-md"
                    :class="[
                        item.status === 'finished'
                            ? 'hover:border-blue-300'
                            : item.status === 'rejected' ||
                                item.status.startsWith('rejected_')
                              ? 'hover:border-red-300'
                              : item.status.startsWith('approved')
                                ? 'hover:border-sky-300'
                                : 'hover:border-amber-300',
                        'border-slate-200',
                    ]"
                >
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                        <!-- Info -->
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-bold text-slate-900">
                                    {{ item.jenisSurat?.nama ?? '-' }}
                                </p>
                                <span
                                    class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                    :class="statusClass(item.status)"
                                >
                                    {{ statusLabel(item.status) }}
                                </span>
                            </div>
                            <p
                                v-if="item.nomor_surat"
                                class="mt-1 font-mono text-[10px] text-slate-400"
                            >
                                {{ item.nomor_surat }}
                            </p>
                            <p
                                class="mt-2 text-xs leading-relaxed text-slate-600"
                            >
                                {{ item.keperluan }}
                            </p>
                            <div
                                class="mt-3 flex items-center gap-3 text-[10px] text-slate-400"
                            >
                                <span class="flex items-center gap-1">
                                    <Calendar class="size-3" />
                                    {{ formatDate(item.tanggal_pengajuan) }}
                                </span>
                                <span
                                    v-if="item.pemohon?.name"
                                    class="flex items-center gap-1"
                                >
                                    <FileText class="size-3" />
                                    {{ item.pemohon.name }}
                                </span>
                            </div>
                        </div>
                        <!-- Actions -->
                        <div class="flex shrink-0 items-start gap-2">
                            <Link
                                :href="`/admin/surat/${item.id}`"
                                class="fast-btn fast-btn-outline flex items-center gap-1.5 px-3 py-1.5 text-[10px] font-medium text-slate-600"
                                title="Lihat detail"
                            >
                                <Eye class="size-3" /> Detail
                            </Link>
                            <a
                                v-if="item.status === 'finished'"
                                :href="`/admin/surat/${item.id}/pdf`"
                                target="_blank"
                                class="fast-btn fast-btn-primary flex items-center gap-1.5 px-3 py-1.5 text-[10px] font-medium"
                                title="Unduh PDF"
                            >
                                <Download class="size-3" /> PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Pagination -->
        <div
            v-if="surats.links.length > 3"
            class="mt-5 flex flex-wrap items-center gap-1.5 pl-8"
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
    </AdminLayout>
</template>
