<script setup lang="ts">
// resources/js/pages/Modules/Fast/Admin/archive/Index.vue
import AdminLayout from '@/layouts/Modules/Fast/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    Search,
    Download,
    Eye,
    Archive,
    FileText,
    ChevronDown,
} from 'lucide-vue-next';
type SuratItem = {
    id: number;
    type: string;
    nomor_surat?: string | null;
    keperluan?: string | null;
    tanggal_selesai?: string | null;
    generated_file_path?: string | null;
    download_url?: string | null;
    pemohon?: { name?: string | null; nim?: string | null } | null;
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
    filters: {
        search?: string;
        date_from?: string;
        date_to?: string;
        category_id?: string;
    };
    categories: Array<{ id: number; nama: string }>;
}>();
const search = ref(props.filters.search ?? '');
const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');
const categoryId = ref(props.filters.category_id ?? '');
const isFilterActive = computed(
    () =>
        search.value !== '' ||
        dateFrom.value !== '' ||
        dateTo.value !== '' ||
        categoryId.value !== '',
);
function applyFilter() {
    router.get(
        '/admin/archive',
        {
            search: search.value || undefined,
            date_from: dateFrom.value || undefined,
            date_to: dateTo.value || undefined,
            category_id: categoryId.value || undefined,
        },
        { preserveState: true, replace: true },
    );
}
function resetFilter() {
    search.value = '';
    dateFrom.value = '';
    dateTo.value = '';
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
function sourceLabel(type: string) {
    return type === 'surat_keluar' ? 'Surat Keluar Admin' : 'Pengajuan User';
}
</script>
<template>
    <AdminLayout
        title="Arsip Surat"
        subtitle="Semua dokumen final dari pengajuan user dan surat keluar admin"
        active-menu="archive"
        :breadcrumbs="[{ label: 'Arsip Surat' }]"
    >
        <Head title="Arsip Surat" />
        <!-- Hero -->
        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-6">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <h2 class="mt-1 text-xl font-bold text-slate-900">
                        Arsip Surat
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Kumpulan dokumen final dari pengajuan user dan surat
                        keluar admin.
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
                        placeholder="Cari nomor surat, nama..."
                        class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 pr-4 pl-10 text-sm text-slate-800 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100"
                        @keyup.enter="applyFilter"
                    />
                </div>
                <div class="relative w-full lg:w-56">
                    <select
                        v-model="categoryId"
                        class="h-11 w-full appearance-none rounded-2xl border border-slate-200 bg-slate-50 pr-8 pl-4 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100"
                    >
                        <option value="">Semua Kategori</option>
                        <option
                            v-for="c in categories"
                            :key="c.id"
                            :value="String(c.id)"
                        >
                            {{ c.nama }}
                        </option>
                    </select>
                    <ChevronDown
                        class="pointer-events-none absolute top-1/2 right-3.5 size-3.5 -translate-y-1/2 text-slate-400"
                    />
                </div>
                <div class="flex w-full flex-col gap-3 sm:flex-row lg:w-auto lg:items-center">
                    <div class="flex flex-1 items-center gap-2">
                        <input
                            v-model="dateFrom"
                            type="date"
                            class="h-11 w-full appearance-none rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100"
                        />
                        <span class="hidden text-xs text-slate-400 sm:block">s/d</span>
                    </div>
                    <input
                        v-model="dateTo"
                        type="date"
                        class="h-11 w-full appearance-none rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100"
                    />
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
                        v-if="isFilterActive"
                        type="button"
                        class="fast-btn fast-btn-soft h-11 w-full px-5 text-sm font-medium text-blue-700 sm:w-auto"
                        @click="resetFilter"
                    >
                        Reset Filter
                    </button>
                </div>
                <p class="text-xs text-slate-400 lg:ml-auto">
                    {{ surats.from ?? 0 }}-{{ surats.to ?? 0 }} dari
                    {{ surats.total }} surat
                </p>
            </div>
        </div>
        <!-- Empty state -->
        <div
            v-if="surats.data.length === 0"
            class="flex flex-col items-center gap-3 py-16 text-center"
        >
            <div
                class="grid size-16 place-items-center rounded-2xl border border-slate-100 bg-slate-50"
            >
                <Archive class="size-8 text-slate-200" />
            </div>
            <p class="text-sm font-medium text-slate-400">
                Belum ada dokumen final di arsip.
            </p>
            <p class="text-xs text-slate-300">
                Surat pengajuan user maupun surat keluar admin yang sudah
                selesai akan muncul di sini.
            </p>
        </div>
        <!-- Document card grid -->
        <div v-else class="grid gap-4 sm:grid-cols-2">
            <div
                v-for="item in surats.data"
                :key="item.id"
                class="group overflow-hidden rounded-2xl border border-slate-200 bg-white transition-all hover:shadow-lg"
                :class="
                    item.type === 'surat_keluar'
                        ? 'hover:border-indigo-300'
                        : 'hover:border-blue-300'
                "
            >
                <!-- Top colored bar -->
                <div
                    class="h-1.5"
                    :class="
                        item.type === 'surat_keluar'
                            ? 'bg-indigo-400'
                            : 'bg-blue-400'
                    "
                />
                <div class="p-5">
                    <!-- Source badge + date -->
                    <div class="mb-4 flex items-center justify-between">
                        <span
                            class="rounded-full border px-2.5 py-1 text-[10px] font-semibold"
                            :class="
                                item.type === 'surat_keluar'
                                    ? 'border-indigo-100 bg-indigo-50 text-indigo-600'
                                    : 'border-blue-100 bg-blue-50 text-blue-600'
                            "
                        >
                            {{ sourceLabel(item.type) }}
                        </span>
                        <span class="text-[10px] text-slate-400">{{
                            formatDate(item.tanggal_selesai)
                        }}</span>
                    </div>
                    <!-- Document icon + nomor -->
                    <div class="mb-4 flex items-start gap-3">
                        <div
                            class="grid size-10 shrink-0 place-items-center rounded-xl border"
                            :class="
                                item.type === 'surat_keluar'
                                    ? 'border-indigo-100 bg-indigo-50 text-indigo-600'
                                    : 'border-blue-100 bg-blue-50 text-blue-600'
                            "
                        >
                            <FileText class="size-5" />
                        </div>
                        <div class="min-w-0">
                            <p
                                class="truncate font-mono text-xs font-semibold text-slate-700"
                            >
                                {{ item.nomor_surat ?? '-' }}
                            </p>
                            <p class="mt-0.5 truncate text-xs text-slate-500">
                                {{ item.jenisSurat?.nama ?? '-' }}
                            </p>
                        </div>
                    </div>
                    <!-- Details -->
                    <div class="mb-4 space-y-1.5">
                        <div class="flex items-center gap-2">
                            <span class="w-14 text-[10px] text-slate-400"
                                >Pemohon</span
                            >
                            <span class="text-xs font-medium text-slate-700">{{
                                item.pemohon?.name ?? '-'
                            }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-14 text-[10px] text-slate-400"
                                >NIM</span
                            >
                            <span
                                class="font-mono text-[10px] text-slate-500"
                                >{{ item.pemohon?.nim ?? '-' }}</span
                            >
                        </div>
                        <div class="flex items-start gap-2">
                            <span
                                class="w-14 shrink-0 text-[10px] text-slate-400"
                                >Keperluan</span
                            >
                            <span class="line-clamp-2 text-xs text-slate-600">{{
                                item.keperluan ?? '-'
                            }}</span>
                        </div>
                    </div>
                    <!-- Actions -->
                    <div
                        class="flex items-center gap-2 border-t border-slate-100 pt-3"
                    >
                        <Link
                            :href="`/admin/surat/${item.id}`"
                            class="fast-btn fast-btn-outline flex flex-1 items-center justify-center gap-1.5 py-2 text-[10px] font-medium text-slate-600"
                            title="Lihat Detail"
                        >
                            <Eye class="size-3" /> Detail
                        </Link>
                        <a
                            v-if="item.download_url"
                            :href="item.download_url"
                            target="_blank"
                            class="fast-btn fast-btn-primary flex flex-1 items-center justify-center gap-1.5 py-2 text-[10px] font-medium"
                            title="Download PDF"
                        >
                            <Download class="size-3" /> Unduh PDF
                        </a>
                        <div
                            v-else
                            class="flex flex-1 cursor-not-allowed items-center justify-center gap-1.5 rounded-lg bg-slate-100 py-2 text-[10px] font-medium text-slate-400"
                            title="PDF belum tersedia"
                        >
                            <FileText class="size-3" /> PDF Belum Tersedia
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
    </AdminLayout>
</template>

