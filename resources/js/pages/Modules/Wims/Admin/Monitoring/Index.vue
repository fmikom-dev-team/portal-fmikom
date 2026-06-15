<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    BriefcaseBusiness,
    CheckCircle2,
    ClipboardList,
    FileCheck2,
    Search,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import WimsAdminLayout from '@/layouts/Modules/Wims/Admin/Layout.vue';

defineOptions({
    layout: WimsAdminLayout,
});

type Filters = {
    status?: string;
    search?: string;
    company_id?: number | null;
    dosen_id?: number | null;
};

type Summary = {
    all?: number;
    active?: number;
    completed?: number;
    reports_uploaded?: number;
    final_scores?: number;
};

type OptionItem = {
    id: number;
    label: string;
};

type MonitoringItem = {
    id: number;
    student_name?: string | null;
    student_email?: string | null;
    student_identity?: string | null;
    status?: string | null;
    company_name?: string | null;
    dosen_name?: string | null;
    period_label?: string | null;
    attendance?: {
        status?: string | null;
        label?: string | null;
        date?: string | null;
    };
    logbook?: {
        status?: string | null;
        label?: string | null;
        date?: string | null;
    };
    report?: {
        uploaded?: boolean;
        label?: string | null;
        uploaded_at?: string | null;
    };
    evaluation?: {
        nilai_akhir?: number | null;
        status_penilaian?: string | null;
        status_key?: string | null;
        status_label?: string | null;
        label?: string | null;
        dosen_score?: number | null;
        mitra_score?: number | null;
        is_complete?: boolean;
    };
};

type PaginationLink = {
    url?: string | null;
    label: string;
    active: boolean;
};

type RegistrationPagination = {
    data: MonitoringItem[];
    total?: number;
    from?: number | null;
    to?: number | null;
    links: PaginationLink[];
};

const props = defineProps<{
    filters: Filters;
    summary: Summary;
    registrations: RegistrationPagination;
    options: {
        companies: OptionItem[];
        dosen: OptionItem[];
    };
}>();

const status = ref(props.filters.status || 'all');
const search = ref(props.filters.search || '');
const companyId = ref(
    props.filters.company_id ? String(props.filters.company_id) : '',
);
const dosenId = ref(
    props.filters.dosen_id ? String(props.filters.dosen_id) : '',
);

watch(
    () => props.filters,
    (filters) => {
        status.value = filters.status || 'all';
        search.value = filters.search || '';
        companyId.value = filters.company_id ? String(filters.company_id) : '';
        dosenId.value = filters.dosen_id ? String(filters.dosen_id) : '';
    },
    { deep: true },
);

const statCards = computed(() => [
    {
        label: 'Total Pengajuan',
        value: props.summary.all ?? 0,
        accent: 'text-slate-950',
    },
    {
        label: 'Magang Aktif',
        value: props.summary.active ?? 0,
        accent: 'text-[#2563EB]',
    },
    {
        label: 'PKL Selesai',
        value: props.summary.completed ?? 0,
        accent: 'text-violet-700',
    },
    {
        label: 'Laporan Masuk',
        value: props.summary.reports_uploaded ?? 0,
        accent: 'text-emerald-700',
    },
    {
        label: 'Penilaian Dosen',
        value: props.summary.final_scores ?? 0,
        accent: 'text-amber-700',
    },
]);

const applyFilters = () => {
    router.get(
        '/wims/admin/monitoring',
        {
            status: status.value,
            search: search.value || undefined,
            company_id: companyId.value || undefined,
            dosen_id: dosenId.value || undefined,
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
};

const resetFilters = () => {
    status.value = 'all';
    search.value = '';
    companyId.value = '';
    dosenId.value = '';
    applyFilters();
};

const registrationStatusClass = (value?: string | null) => {
    if (value === 'aktif') return 'border-sky-200 bg-sky-50 text-sky-700';
    if (value === 'selesai')
        return 'border-violet-200 bg-violet-50 text-violet-700';
    if (value === 'approved')
        return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    if (value === 'pending')
        return 'border-amber-200 bg-amber-50 text-amber-700';
    if (value === 'revisi') return 'border-blue-200 bg-blue-50 text-[#2563EB]';
    if (value === 'rejected') return 'border-rose-200 bg-rose-50 text-rose-700';
    return 'border-slate-200 bg-slate-50 text-slate-700';
};

const registrationStatusLabel = (value?: string | null) => {
    if (value === 'aktif') return 'Aktif';
    if (value === 'selesai') return 'Selesai';
    if (value === 'approved') return 'Disetujui';
    if (value === 'pending') return 'Menunggu';
    if (value === 'revisi') return 'Revisi';
    if (value === 'rejected') return 'Ditolak';
    return 'Menunggu';
};

const attendanceClass = (value?: string | null) => {
    if (value === 'hadir' || value === 'tepat_waktu') return 'text-emerald-700';
    if (value === 'terlambat') return 'text-amber-700';
    if (value === 'izin') return 'text-sky-700';
    if (value === 'sakit') return 'text-violet-700';
    if (value === 'alfa') return 'text-rose-700';
    return 'text-slate-500';
};

const logbookClass = (value?: string | null) => {
    if (value === 'approved' || value === 'disetujui') return 'text-emerald-700';
    if (value === 'revisi') return 'text-amber-700';
    if (value === 'rejected') return 'text-rose-700';
    return 'text-slate-500';
};

const attentionBadgeLabel = (item: MonitoringItem) => {
    if (
        item.status === 'selesai' &&
        item.evaluation?.is_complete !== true
    ) {
        return 'Menunggu Penilaian';
    }

    if (
        item.status === 'aktif' &&
        (item.attendance?.status === 'alfa' ||
            !item.logbook?.date ||
            !item.report?.uploaded)
    ) {
        return 'Perlu ditinjau';
    }

    return null;
};

const studentInitial = (name?: string | null) => {
    const words = name?.trim().split(/\s+/).filter(Boolean).slice(0, 2) ?? [];

    if (!words.length) {
        return 'M';
    }

    return words.map((word) => word.charAt(0).toUpperCase()).join('');
};
</script>

<template>
    <Head title="Monitoring Mahasiswa Admin" />

    <div
        class="mx-auto w-full max-w-[1320px] space-y-5 px-4 py-4 sm:px-6 sm:py-6 lg:space-y-6 lg:px-8 lg:py-8"
    >
        <section>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <div
                    v-for="item in statCards"
                    :key="item.label"
                    class="flex min-h-24 flex-col justify-between rounded-xl border border-zinc-200 bg-white px-5 py-4 shadow-none"
                >
                    <p class="text-xs font-bold text-slate-500">
                        {{ item.label }}
                    </p>
                    <p
                        class="mt-2 text-[22px] font-bold tracking-tight sm:text-[24px]"
                        :class="item.accent"
                    >
                        {{ item.value }}
                    </p>
                </div>
            </div>
        </section>

        <Card
            class="rounded-xl border border-zinc-200 bg-white py-0 shadow-none"
        >
            <CardHeader class="border-b border-zinc-200 px-5 py-4">
                <div>
                    <div>
                        <CardTitle class="text-[15px] font-bold text-slate-950">
                            Rekap Pelaksanaan
                        </CardTitle>
                        <CardDescription class="mt-1 text-sm leading-6 text-slate-600">
                            Gunakan filter untuk memantau mahasiswa berdasarkan
                            status magang, perusahaan, atau dosen pembimbing.
                        </CardDescription>
                    </div>
                </div>
            </CardHeader>

            <CardContent class="space-y-5 px-5 py-5">
                <form
                    class="grid gap-3 xl:grid-cols-[minmax(0,1fr)_170px_210px_210px_auto_auto]"
                    @submit.prevent="applyFilters"
                >
                    <div class="relative">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-zinc-400"
                        />
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Cari mahasiswa, perusahaan, atau dosen..."
                            class="h-10 rounded-lg border-zinc-200 bg-zinc-50 pl-10"
                        />
                    </div>

                    <select
                        v-model="status"
                        class="h-10 rounded-lg border border-zinc-200 bg-zinc-50 px-3 text-sm text-zinc-900 transition outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/10"
                    >
                        <option value="all">Semua Status</option>
                        <option value="pending">Menunggu</option>
                        <option value="approved">Disetujui</option>
                        <option value="aktif">Aktif</option>
                        <option value="selesai">Selesai</option>
                        <option value="revisi">Revisi</option>
                        <option value="rejected">Ditolak</option>
                    </select>

                    <select
                        v-model="companyId"
                        class="h-10 rounded-lg border border-zinc-200 bg-zinc-50 px-3 text-sm text-zinc-900 transition outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/10"
                    >
                        <option value="">Semua Perusahaan</option>
                        <option
                            v-for="company in options.companies"
                            :key="company.id"
                            :value="String(company.id)"
                        >
                            {{ company.label }}
                        </option>
                    </select>

                    <select
                        v-model="dosenId"
                        class="h-10 rounded-lg border border-zinc-200 bg-zinc-50 px-3 text-sm text-zinc-900 transition outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/10"
                    >
                        <option value="">Semua Dosen</option>
                        <option
                            v-for="dosen in options.dosen"
                            :key="dosen.id"
                            :value="String(dosen.id)"
                        >
                            {{ dosen.label }}
                        </option>
                    </select>

                    <Button
                        type="submit"
                        class="h-10 rounded-lg bg-blue-600 px-4 text-white hover:bg-blue-700"
                    >
                        Terapkan
                    </Button>

                    <Button
                        type="button"
                        variant="outline"
                        class="h-10 rounded-lg border-zinc-200 px-4 text-zinc-700"
                        @click="resetFilters"
                    >
                        Reset
                    </Button>
                </form>

                <div class="space-y-5">
                    <div
                        v-for="item in registrations.data"
                        :key="item.id"
                        class="rounded-xl border border-zinc-200 bg-white px-4 py-4 sm:px-5"
                    >
                        <div
                            class="flex flex-col gap-4"
                        >
                            <div class="min-w-0">
                                <div class="flex items-start gap-3">
                                    <div
                                        class="flex size-10 items-center justify-center rounded-lg bg-blue-50 text-[13px] font-bold text-blue-600"
                                    >
                                        {{ studentInitial(item.student_name) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div
                                            class="flex flex-wrap items-center gap-2"
                                        >
                                            <p
                                                class="truncate text-sm font-bold text-zinc-950"
                                            >
                                                {{ item.student_name || '-' }}
                                            </p>
                                            <Badge
                                                variant="outline"
                                                class="rounded-full px-2.5 py-0.5 text-[10px] font-bold shadow-none"
                                                :class="
                                                    registrationStatusClass(
                                                        item.status,
                                                    )
                                                "
                                            >
                                                {{ registrationStatusLabel(item.status) }}
                                            </Badge>
                                            <Badge
                                                v-if="attentionBadgeLabel(item)"
                                                variant="outline"
                                                class="rounded-full border-amber-200 bg-amber-50 px-2.5 py-0.5 text-[10px] font-bold text-amber-700 shadow-none"
                                            >
                                                {{ attentionBadgeLabel(item) }}
                                            </Badge>
                                        </div>
                                        <div
                                            class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-zinc-500"
                                        >
                                            <span class="truncate">{{
                                                item.student_email || '-'
                                            }}</span>
                                            <span class="text-zinc-300">|</span>
                                            <span>{{
                                                item.student_identity ||
                                                'Identitas belum tersedia'
                                            }}</span>
                                        </div>
                                        <div class="mt-3 flex flex-wrap gap-2.5">
                                            <span
                                                class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-bold text-slate-600"
                                            >
                                                {{
                                                    item.company_name ||
                                                    'Belum ada perusahaan final'
                                                }}
                                            </span>
                                            <span
                                                class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-bold text-slate-600"
                                            >
                                                {{
                                                    item.dosen_name ||
                                                    'Belum ada dosen pembimbing'
                                                }}
                                            </span>
                                            <span
                                                class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-bold text-slate-600"
                                            >
                                                {{ item.period_label || '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="mt-5 border-t border-zinc-200 pt-5"
                        >
                            <div
                                class="grid gap-0 overflow-hidden rounded-lg md:grid-cols-2 md:divide-x md:divide-zinc-200 xl:grid-cols-4 xl:divide-x"
                            >
                                <div
                                    class="border-b border-zinc-200 px-0 py-4 md:border-b-0 md:px-4 md:py-4 xl:px-5"
                                >
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="mt-0.5 flex size-8 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600"
                                        >
                                            <CheckCircle2 class="size-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                                                Presensi terakhir
                                            </p>
                                            <p
                                                class="mt-1 text-sm font-bold"
                                                :class="
                                                    attendanceClass(
                                                        item.attendance?.status,
                                                    )
                                                "
                                            >
                                                {{ item.attendance?.label || '-' }}
                                            </p>
                                            <p class="mt-1 text-xs text-slate-500">
                                                {{
                                                    item.attendance?.date ||
                                                    'Belum ada riwayat presensi.'
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="border-b border-zinc-200 px-0 py-4 md:border-b-0 md:px-4 md:py-4 xl:px-5"
                                >
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="mt-0.5 flex size-8 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600"
                                        >
                                            <ClipboardList class="size-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                                                Logbook terakhir
                                            </p>
                                            <p
                                                class="mt-1 text-sm font-bold"
                                                :class="
                                                    logbookClass(
                                                        item.logbook?.status,
                                                    )
                                                "
                                            >
                                                {{ item.logbook?.label || '-' }}
                                            </p>
                                            <p class="mt-1 text-xs text-slate-500">
                                                {{
                                                    item.logbook?.date ||
                                                    'Belum ada logbook yang masuk.'
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="border-b border-zinc-200 px-0 py-4 md:border-b-0 md:px-4 md:py-4 xl:px-5"
                                >
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="mt-0.5 flex size-8 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600"
                                        >
                                            <FileCheck2 class="size-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                                                Laporan akhir
                                            </p>
                                            <p
                                                class="mt-1 text-sm font-bold"
                                                :class="
                                                    item.report?.uploaded
                                                        ? 'text-emerald-700'
                                                        : 'text-zinc-600'
                                                "
                                            >
                                                {{ item.report?.label || '-' }}
                                            </p>
                                            <p class="mt-1 text-xs text-slate-500">
                                                {{
                                                    item.report?.uploaded_at ||
                                                    'Dokumen belum diunggah mahasiswa.'
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="px-0 py-4 md:px-4 md:py-4 xl:px-5">
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="mt-0.5 flex size-8 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600"
                                        >
                                            <CheckCircle2 class="size-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                                                Penilaian
                                            </p>
                                            <div class="mt-1 space-y-1">
                                                <p class="text-sm font-bold text-zinc-900">
                                                    Dosen:
                                                    <span class="font-bold">
                                                        {{ item.evaluation?.dosen_score ?? '-' }}
                                                    </span>
                                                </p>
                                                <p class="text-sm font-bold text-zinc-900">
                                                    Mitra:
                                                    <span class="font-bold">
                                                        {{ item.evaluation?.mitra_score ?? '-' }}
                                                    </span>
                                                </p>
                                            </div>
                                            <p class="mt-2 text-xs text-slate-500">
                                                {{
                                                    item.evaluation?.label ||
                                                    'Belum ada penilaian.'
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="!registrations.data.length"
                        class="rounded-xl border border-dashed border-zinc-200 bg-zinc-50 px-5 py-8 text-center"
                    >
                        <p class="text-sm font-bold text-slate-700">
                            Tidak ada data monitoring yang cocok dengan filter
                            saat ini.
                        </p>
                        <p class="mt-2 text-sm text-slate-500">
                            Coba ubah status, perusahaan, dosen, atau kata kunci
                            pencarian.
                        </p>
                    </div>
                </div>

                <div
                    class="flex flex-col gap-3 border-t border-zinc-200 pt-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <p class="text-sm text-zinc-500">
                        Menampilkan {{ registrations.from ?? 0 }}-{{
                            registrations.to ?? 0
                        }}
                        dari {{ registrations.total ?? 0 }} data pelaksanaan.
                    </p>

                    <div class="flex flex-wrap items-center gap-2">
                        <template
                            v-for="(link, index) in registrations.links"
                            :key="`${index}-${link.label}`"
                        >
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="inline-flex min-w-10 items-center justify-center rounded-lg border px-3 py-2 text-sm transition"
                                :class="
                                    link.active
                                        ? 'border-blue-600 bg-blue-600 text-white'
                                        : 'border-zinc-200 bg-white text-zinc-600 hover:border-zinc-300 hover:text-zinc-900'
                                "
                                preserve-scroll
                                preserve-state
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="inline-flex min-w-10 items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm text-zinc-400"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>


