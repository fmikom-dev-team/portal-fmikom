<script setup lang="ts">
import AdminLayout from '@/layouts/Modules/Fast/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    Search,
    FileText,
    Download,
    FileCheck2,
    RotateCcw,
} from 'lucide-vue-next';
type SuratItem = {
    id: number;
    status: string;
    tanggal_selesai?: string | null;
    created_at?: string | null;
    pemohon?: { name?: string | null; nim?: string | null } | null;
    jenisSurat?: { id?: number | null; nama?: string | null } | null;
    nomor_surat?: string | null;
    download_url?: string | null;
};
type PaginationLink = { url: string | null; label: string; active: boolean };
type PaginatedSurats = {
    data: SuratItem[];
    links: PaginationLink[];
    from?: number | null;
    to?: number | null;
    total: number;
};
const props = defineProps<{
    role: { name?: string | null; slug?: string | null };
    surats: PaginatedSurats;
    filters: {
        search?: string;
        jenis_surat_id?: string;
        tanggal_mulai?: string;
        tanggal_akhir?: string;
    };
    jenisSuratOptions: Record<string, string>;
}>();
const search = ref(props.filters.search ?? '');
const jenisSuratId = ref(props.filters.jenis_surat_id ?? '');
const tanggalMulai = ref(props.filters.tanggal_mulai ?? '');
const tanggalAkhir = ref(props.filters.tanggal_akhir ?? '');
const normalizedRole = computed(() =>
    String(props.role.slug ?? props.role.name ?? '')
        .toLowerCase()
        .includes('kaprodi')
        ? 'kaprodi'
        : 'dekan',
);
const basePath = computed(() => `/${normalizedRole.value}`);
function applySearch() {
    const params: Record<string, string> = {};
    if (search.value) params.search = search.value;
    if (jenisSuratId.value) params.jenis_surat_id = jenisSuratId.value;
    if (tanggalMulai.value) params.tanggal_mulai = tanggalMulai.value;
    if (tanggalAkhir.value) params.tanggal_akhir = tanggalAkhir.value;
    router.get(`${basePath.value}/unduh`, params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}
function resetFilters() {
    search.value = '';
    jenisSuratId.value = '';
    tanggalMulai.value = '';
    tanggalAkhir.value = '';
    applySearch();
}
function formatDate(date?: string | null) {
    if (!date) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(date));
}
</script>
<template>
    <AdminLayout
        :title="`Unduh Surat ${role.name || 'Approval'}`"
        subtitle="Daftar surat final yang siap diunduh"
        active-menu="approval.unduh"
        :breadcrumbs="[
            { label: 'Dashboard', href: `${basePath}/dashboard` },
            { label: 'Unduh Surat' },
        ]"
    >
        <Head :title="`Unduh Surat ${role.name || 'Approval'}`" />
        <!-- Hero -->
        <div
            class="mb-6 rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-white p-6"
        >
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <h2 class="mt-1 text-xl font-bold text-slate-900">
                        Surat Final
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Surat yang telah selesai dan tersedia dalam format PDF.
                    </p>
                </div>
            </div>
        </div>
        <!-- Filters -->
        <div
            class="mb-4 flex flex-wrap items-center gap-2 rounded-xl border border-slate-200 bg-white p-3"
        >
            <div class="relative">
                <Search
                    class="pointer-events-none absolute top-1/2 left-3 size-3.5 -translate-y-1/2 text-slate-400"
                />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari nama atau NIM..."
                    class="h-9 w-52 rounded-xl border border-slate-200 bg-slate-50 pr-3 pl-9 text-xs text-slate-700 placeholder-slate-400 outline-none focus:border-blue-400"
                    @keyup.enter="applySearch"
                />
            </div>
            <select
                v-model="jenisSuratId"
                class="h-9 rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs text-slate-700 outline-none focus:border-blue-400"
            >
                <option value="">Semua Kategori</option>
                <option
                    v-for="(nama, id) in jenisSuratOptions"
                    :key="id"
                    :value="id"
                >
                    {{ nama }}
                </option>
            </select>
            <input
                v-model="tanggalMulai"
                type="date"
                class="h-9 rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs text-slate-700 outline-none focus:border-blue-400"
                placeholder="Tanggal Mulai"
            />
            <input
                v-model="tanggalAkhir"
                type="date"
                class="h-9 rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs text-slate-700 outline-none focus:border-blue-400"
                placeholder="Tanggal Akhir"
            />
            <button
                type="button"
                class="fast-btn fast-btn-primary h-9 px-4 text-xs"
                @click="applySearch"
            >
                Cari
            </button>
            <button
                v-if="search || jenisSuratId || tanggalMulai || tanggalAkhir"
                type="button"
                class="text-xs text-slate-400 hover:text-blue-600"
                @click="resetFilters"
            >
                <RotateCcw class="size-3.5" />
            </button>
        </div>
        <!-- Table -->
        <div
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50">
                        <tr
                            class="text-[10px] font-semibold tracking-widest text-slate-400 uppercase"
                        >
                            <th class="px-5 py-3">No</th>
                            <th class="px-5 py-3">Nomor Surat</th>
                            <th class="px-5 py-3">Pemohon</th>
                            <th class="px-5 py-3">Jenis Surat</th>
                            <th class="px-5 py-3">Tanggal Selesai</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="surats.data.length === 0">
                            <td colspan="6" class="px-5 py-12 text-center">
                                <FileText
                                    class="mx-auto mb-2 size-10 text-slate-300"
                                />
                                <p class="text-sm text-slate-400">
                                    Belum ada surat final yang tersedia untuk
                                    diunduh.
                                </p>
                            </td>
                        </tr>
                        <tr
                            v-for="(item, idx) in surats.data"
                            :key="item.id"
                            class="border-t border-slate-100 text-sm transition-colors hover:bg-slate-50/50"
                        >
                            <td class="px-5 py-3.5 text-xs text-slate-500">
                                {{ (surats.from ?? 0) + idx }}
                            </td>
                            <td
                                class="px-5 py-3.5 font-mono text-[11px] text-slate-600"
                            >
                                {{ item.nomor_surat || '-' }}
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="text-xs font-semibold text-slate-900">
                                    {{ item.pemohon?.name || '-' }}
                                </p>
                                <p class="font-mono text-[10px] text-slate-400">
                                    {{ item.pemohon?.nim || '-' }}
                                </p>
                            </td>
                            <td
                                class="max-w-[180px] truncate px-5 py-3.5 text-xs text-slate-600"
                            >
                                {{ item.jenisSurat?.nama || '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-400">
                                {{ formatDate(item.tanggal_selesai) }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div
                                    class="flex items-center justify-end gap-1.5"
                                >
                                    <a
                                        v-if="item.download_url"
                                        :href="item.download_url"
                                        target="_blank"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-600 transition-colors hover:bg-blue-100"
                                    >
                                        <Download class="size-3.5" /> Unduh PDF
                                    </a>
                                    <span v-else class="text-xs text-slate-400"
                                        >Belum tersedia</span
                                    >
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
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
    </AdminLayout>
</template>
