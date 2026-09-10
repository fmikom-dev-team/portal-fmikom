<script setup lang="ts">
// resources/js/pages/Modules/Fast/Admin/archive/Index.vue
import AdminLayout from '@/layouts/Modules/Fast/AdminLayout.vue';
import { useFastPermissions } from '@/composables/modules/fast/useFastPermissions';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    Search,
    Download,
    Eye,
    Archive,
    FileText,
    ChevronDown,
} from 'lucide-vue-next';

const { can } = useFastPermissions();
const page = usePage<{
    context?: { active_role?: string | null } | null;
}>();
const activeRoleSlug = computed(() =>
    String(page.props.context?.active_role ?? 'admin').toLowerCase(),
);
const adminBasePath = computed(() =>
    ['dekan', 'kaprodi'].includes(activeRoleSlug.value)
        ? `/${activeRoleSlug.value}/admin`
        : '/admin',
);
type SuratItem = {
    id: number;
    type: string;
    nomor_surat?: string | null;
    keperluan?: string | null;
    tanggal_selesai?: string | null;
    generated_file_path?: string | null;
    download_url?: string | null;
    letter_mode?: string | null;
    letter_mode_label?: string | null;
    is_institution?: boolean;
    subject?: { name?: string | null; nim?: string | null } | null;
    jenisSurat?: {
        nama?: string | null;
        category?: { nama?: string | null } | null;
    } | null;
    validator?: { name?: string | null } | null;
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
        `${adminBasePath.value}/archive`,
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
function archiveUrl() {
    const params = new URLSearchParams();

    if (search.value) params.set('search', search.value);
    if (dateFrom.value) params.set('date_from', dateFrom.value);
    if (dateTo.value) params.set('date_to', dateTo.value);
    if (categoryId.value) params.set('category_id', categoryId.value);

    const query = params.toString();
    return `${adminBasePath.value}/archive${query ? `?${query}` : ''}`;
}
function detailUrl(id: number) {
    return `${adminBasePath.value}/surat/${id}?return_to=${encodeURIComponent(archiveUrl())}`;
}
</script>
<template>
    <AdminLayout
        title="Arsip Surat"
        subtitle="Semua dokumen final dari pengajuan user dan surat yang dibuat admin"
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
                        yang dibuat admin atas nama subjek terkait.
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
                        placeholder="Cari nomor surat, jenis surat, kategori, pemohon, validator..."
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
                Surat pengajuan user maupun surat admin yang sudah selesai akan
                muncul di sini.
            </p>
        </div>
        <!-- Arsip list -->
        <div v-else class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-5 py-3.5">Nomor Surat</th>
                            <th class="px-5 py-3.5">Kategori</th>
                            <th class="px-5 py-3.5">Validator Surat</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="item in surats.data"
                            :key="item.id"
                            class="transition-colors hover:bg-slate-50/80"
                        >
                            <td class="px-5 py-4">
                                <p class="font-mono text-xs font-semibold text-slate-800">
                                    {{ item.nomor_surat ?? '-' }}
                                </p>
                                <p class="mt-1 text-[11px] text-slate-400">
                                    {{ item.jenisSurat?.nama ?? '-' }}
                                </p>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600">
                                {{ item.jenisSurat?.category?.nama ?? '-' }}
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600">
                                {{ item.validator?.name ?? '-' }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <Link
                                        v-if="can('fast.admin.archive.view')"
                                        :href="detailUrl(item.id)"
                                        class="fast-btn fast-btn-outline inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-slate-600"
                                        title="Lihat"
                                    >
                                        <Eye class="size-3.5" /> Lihat
                                    </Link>
                                    <a
                                        v-if="item.download_url && can('fast.document.download')"
                                        :href="item.download_url"
                                        target="_blank"
                                        class="fast-btn fast-btn-primary inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium"
                                        title="Unduh PDF"
                                    >
                                        <Download class="size-3.5" /> Unduh
                                    </a>
                                    <span
                                        v-else-if="can('fast.document.download')"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-3 py-2 text-xs font-medium text-slate-400"
                                        title="PDF belum tersedia"
                                    >
                                        <FileText class="size-3.5" /> Belum tersedia
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
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
