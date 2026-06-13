<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    BookCheck,
    Building2,
    CheckCheck,
    FileChartColumnIncreasing,
    Inbox,
    Search,
    UserRound,
    Users,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatIndonesianDateLabel } from '@/lib/date';
import WimsAdminLayout from '@/layouts/Wims/Admin/Layout.vue';

defineOptions({
    layout: WimsAdminLayout,
});

type Filters = {
    search?: string;
    filter?: 'all' | 'incomplete' | 'missing_dosen_score' | 'missing_mitra_score' | 'complete';
};

type Summary = {
    total_students?: number;
    incomplete?: number;
    missing_dosen_score?: number;
    missing_mitra_score?: number;
    complete?: number;
};

type AssessmentState = {
    status_key: 'not_assessed' | 'draft' | 'submitted';
    status_label: string;
    total_score?: number | null;
    download_url?: string | null;
    submitted_at?: string | null;
};

type RecapItem = {
    id: number;
    student: {
        name?: string | null;
        nim?: string | null;
        email?: string | null;
        program_studi?: string | null;
    };
    company_name?: string | null;
    lecturer_name?: string | null;
    period_label?: string | null;
    registration_status?: string | null;
    dosen_assessment: AssessmentState;
    mitra_assessment: AssessmentState;
};

type PaginationLink = {
    url?: string | null;
    label: string;
    active: boolean;
};

type RecapPagination = {
    data: RecapItem[];
    total?: number;
    from?: number | null;
    to?: number | null;
    links: PaginationLink[];
};

const props = defineProps<{
    filters: Filters;
    summary: Summary;
    registrations: RecapPagination;
}>();

const search = ref(props.filters.search || '');
const filter = ref(props.filters.filter || 'all');

watch(
    () => props.filters,
    (nextFilters) => {
        search.value = nextFilters.search || '';
        filter.value = nextFilters.filter || 'all';
    },
    { deep: true },
);

const statCards = computed(() => [
    {
        label: 'Total Mahasiswa',
        value: props.summary.total_students ?? 0,
        icon: Users,
        iconBox: 'text-zinc-700',
        valueClass: 'text-zinc-950',
        hint: 'Masa PKL selesai',
    },
    {
        label: 'Belum Lengkap',
        value: props.summary.incomplete ?? 0,
        icon: FileChartColumnIncreasing,
        iconBox: 'text-amber-600',
        valueClass: 'text-amber-700',
        hint: null,
    },
    {
        label: 'Nilai Dosen Belum Ada',
        value: props.summary.missing_dosen_score ?? 0,
        icon: BookCheck,
        iconBox: 'text-blue-600',
        valueClass: 'text-blue-600',
        hint: null,
    },
    {
        label: 'Nilai Mitra Belum Ada',
        value: props.summary.missing_mitra_score ?? 0,
        icon: Building2,
        iconBox: 'text-violet-600',
        valueClass: 'text-violet-700',
        hint: null,
    },
    {
        label: 'Lengkap',
        value: props.summary.complete ?? 0,
        icon: CheckCheck,
        iconBox: 'text-emerald-600',
        valueClass: 'text-emerald-700',
        hint: null,
    },
]);

const applyFilters = () => {
    router.get(
        '/wims/admin/rekap-nilai',
        {
            search: search.value || undefined,
            filter: filter.value,
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
};

const resetFilters = () => {
    search.value = '';
    filter.value = 'all';
    applyFilters();
};

const assessmentStatusClass = (statusKey: AssessmentState['status_key']) => {
    if (statusKey === 'submitted') {
        return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    }

    if (statusKey === 'draft') {
        return 'border-amber-200 bg-amber-50 text-amber-700';
    }

    return 'border-slate-200 bg-slate-50 text-slate-600';
};

const registrationStatusLabel = (value?: string | null) => {
    if (value === 'aktif') return 'Aktif';
    if (value === 'selesai') return 'Selesai';
    if (value === 'approved') return 'Disetujui';
    if (value === 'pending') return 'Menunggu';
    if (value === 'revisi') return 'Revisi';
    if (value === 'rejected') return 'Ditolak';
    return 'Belum Diatur';
};

const registrationStatusClass = (value?: string | null) => {
    if (value === 'aktif') return 'border-sky-200 bg-sky-50 text-sky-700';
    if (value === 'selesai') return 'border-violet-200 bg-violet-50 text-violet-700';
    if (value === 'approved') return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    if (value === 'pending') return 'border-amber-200 bg-amber-50 text-amber-700';
    if (value === 'revisi') return 'border-blue-200 bg-blue-50 text-blue-700';
    if (value === 'rejected') return 'border-rose-200 bg-rose-50 text-rose-700';
    return 'border-slate-200 bg-slate-50 text-slate-600';
};

const formatScore = (value?: number | null) => {
    if (value === null || value === undefined) {
        return '-';
    }

    return value.toFixed(2);
};


const openDownload = (url?: string | null) => {
    if (!url || typeof window === 'undefined') {
        return;
    }

    window.location.href = url;
};
</script>

<template>
    <Head title="Rekap Nilai Mahasiswa" />

    <div class="mx-auto w-full max-w-[1320px] space-y-5 px-4 py-4 sm:px-6 sm:py-6 lg:space-y-6 lg:px-8 lg:py-8">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <Card
                v-for="card in statCards"
                :key="card.label"
                class="rounded-xl border border-zinc-200 bg-white py-0 shadow-none"
            >
                <CardContent class="px-5 py-4">
                    <div class="flex min-h-24 flex-col">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-h-10">
                                <p class="text-xs font-bold leading-5 text-slate-500">
                                    {{ card.label }}
                                </p>
                            </div>
                            <div class="flex size-9 shrink-0 items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50">
                                <component :is="card.icon" class="size-4" :class="card.iconBox" />
                            </div>
                        </div>
                        <div class="mt-auto">
                            <p class="pt-3 text-[22px] font-bold tracking-tight sm:text-[24px]" :class="card.valueClass">
                                {{ card.value }}
                            </p>
                            <p v-if="card.hint" class="mt-2 text-xs font-bold text-slate-500">
                                {{ card.hint }}
                            </p>
                            <div v-else class="mt-2 h-[18px]" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </section>

        <Card class="rounded-xl border border-zinc-200 bg-white py-0 shadow-none">
            <CardHeader class="border-b border-zinc-200 px-5 py-4">
                <div>
                    <div>
                        <CardTitle class="text-[15px] font-bold text-slate-950">
                            Daftar Rekap Nilai
                        </CardTitle>
                        <p class="mt-1 text-sm leading-6 text-slate-600">
                            Nilai dosen dan mitra ditampilkan terpisah tanpa nilai gabungan atau nilai akhir.
                        </p>
                    </div>
                </div>
            </CardHeader>

            <CardContent class="space-y-5 px-5 py-5">
                <form
                    class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_220px_auto_auto]"
                    @submit.prevent="applyFilters"
                >
                    <div class="relative">
                        <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-zinc-400" />
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Cari mahasiswa, NIM, perusahaan, atau dosen..."
                            class="h-10 rounded-lg border-zinc-200 bg-zinc-50 pl-10"
                        />
                    </div>

                    <select
                        v-model="filter"
                        class="h-10 rounded-lg border border-zinc-200 bg-zinc-50 px-3 text-sm text-zinc-900 outline-none transition focus:border-blue-600 focus:ring-2 focus:ring-blue-600/10"
                    >
                        <option value="all">Semua</option>
                        <option value="incomplete">Belum Lengkap</option>
                        <option value="missing_dosen_score">Nilai Dosen Belum Ada</option>
                        <option value="missing_mitra_score">Nilai Mitra Belum Ada</option>
                        <option value="complete">Lengkap</option>
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

                <div v-if="registrations.data.length" class="hidden overflow-x-auto lg:block">
                    <table class="min-w-full border-collapse">
                        <thead class="bg-zinc-50">
                            <tr class="border-y border-zinc-200">
                                <th class="px-4 py-3 text-left text-[11px] font-bold tracking-[0.16em] text-slate-500 uppercase">Mahasiswa</th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold tracking-[0.16em] text-slate-500 uppercase">Penempatan</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold tracking-[0.16em] text-slate-500 uppercase">Penilaian Dosen</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold tracking-[0.16em] text-slate-500 uppercase">Penilaian Mitra</th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold tracking-[0.16em] text-slate-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="item in registrations.data"
                                :key="item.id"
                                class="border-b border-zinc-100 align-top transition-colors hover:bg-zinc-50"
                            >
                                <td class="px-4 py-3.5">
                                    <div class="min-w-[250px]">
                                        <p class="text-sm font-bold text-zinc-950">
                                            {{ item.student.name || '-' }}
                                        </p>
                                        <p class="mt-0.5 text-xs text-zinc-500">
                                            {{ item.student.nim || '-' }} | {{ item.student.email || '-' }}
                                        </p>
                                        <p class="mt-1 text-xs text-zinc-500">
                                            {{ item.student.program_studi || 'Program studi belum tersedia' }}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-sm text-zinc-700">
                                    <div class="min-w-[240px] space-y-2 whitespace-normal break-words">
                                        <div>
                                            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Perusahaan</p>
                                            <p class="mt-1 text-sm font-bold text-zinc-950">
                                                {{ item.company_name || '-' }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Dosen Pembimbing</p>
                                            <p class="mt-1 text-sm text-zinc-700">
                                                {{ item.lecturer_name || '-' }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Periode</p>
                                            <p class="mt-1 text-sm text-zinc-700">
                                                {{ formatIndonesianDateLabel(item.period_label) }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <div class="mx-auto flex max-w-[170px] flex-col items-center gap-2">
                                        <p class="text-sm font-bold text-zinc-950">
                                            {{ formatScore(item.dosen_assessment.total_score) }}
                                        </p>
                                        <Badge
                                            variant="outline"
                                            class="w-fit rounded-full px-2.5 py-0.5 text-[10px] font-bold shadow-none"
                                            :class="assessmentStatusClass(item.dosen_assessment.status_key)"
                                        >
                                            {{ item.dosen_assessment.status_label }}
                                        </Badge>
                                        <p v-if="item.dosen_assessment.submitted_at" class="text-xs leading-5 text-zinc-500">
                                            Dikirim {{ formatIndonesianDateLabel(item.dosen_assessment.submitted_at) }}
                                        </p>
                                        <Button
                                            v-if="item.dosen_assessment.download_url"
                                            type="button"
                                            variant="outline"
                                            class="h-8 rounded-lg border-zinc-200 px-3 text-xs font-bold text-zinc-700"
                                            @click="openDownload(item.dosen_assessment.download_url)"
                                        >
                                            Unduh
                                        </Button>
                                        <div v-else class="h-[20px]" />
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <div class="mx-auto flex max-w-[170px] flex-col items-center gap-2">
                                        <p class="text-sm font-bold text-zinc-950">
                                            {{ formatScore(item.mitra_assessment.total_score) }}
                                        </p>
                                        <Badge
                                            variant="outline"
                                            class="w-fit rounded-full px-2.5 py-0.5 text-[10px] font-bold shadow-none"
                                            :class="assessmentStatusClass(item.mitra_assessment.status_key)"
                                        >
                                            {{ item.mitra_assessment.status_label }}
                                        </Badge>
                                        <p v-if="item.mitra_assessment.submitted_at" class="text-xs leading-5 text-zinc-500">
                                            Dikirim {{ formatIndonesianDateLabel(item.mitra_assessment.submitted_at) }}
                                        </p>
                                        <Button
                                            v-if="item.mitra_assessment.download_url"
                                            type="button"
                                            variant="outline"
                                            class="h-8 rounded-lg border-zinc-200 px-3 text-xs font-bold text-zinc-700"
                                            @click="openDownload(item.mitra_assessment.download_url)"
                                        >
                                            Unduh
                                        </Button>
                                        <div v-else class="h-[20px]" />
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <Badge
                                        variant="outline"
                                        class="rounded-full px-2.5 py-0.5 text-[10px] font-bold shadow-none"
                                        :class="registrationStatusClass(item.registration_status)"
                                    >
                                        {{ registrationStatusLabel(item.registration_status) }}
                                    </Badge>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="registrations.data.length" class="space-y-4 lg:hidden">
                    <div
                        v-for="item in registrations.data"
                        :key="`mobile-${item.id}`"
                        class="rounded-xl border border-zinc-200 bg-white px-4 py-4"
                    >
                        <div class="flex items-start gap-3">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-700">
                                <UserRound class="size-4" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-sm font-bold text-zinc-950">
                                        {{ item.student.name || '-' }}
                                    </p>
                                    <Badge
                                        variant="outline"
                                        class="rounded-full px-2.5 py-0.5 text-[10px] font-bold shadow-none"
                                        :class="registrationStatusClass(item.registration_status)"
                                    >
                                        {{ registrationStatusLabel(item.registration_status) }}
                                    </Badge>
                                </div>
                                <p class="mt-1 break-words text-xs leading-5 text-zinc-500">
                                    {{ item.student.nim || '-' }} | {{ item.student.email || '-' }}
                                </p>
                                <p class="mt-1 text-xs text-zinc-500">
                                    {{ item.student.program_studi || 'Program studi belum tersedia' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-lg border border-zinc-200 bg-zinc-50 px-3.5 py-3 sm:col-span-2">
                                <p class="text-[11px] font-bold tracking-[0.16em] text-slate-500 uppercase">Penempatan</p>
                                <p class="mt-1.5 text-sm font-bold text-zinc-950">{{ item.company_name || '-' }}</p>
                                <p class="mt-1 text-sm text-zinc-700">{{ item.lecturer_name || '-' }}</p>
                                <p class="mt-1 text-xs text-zinc-500">
                                    {{ formatIndonesianDateLabel(item.period_label) }}
                                </p>
                            </div>
                            <div class="rounded-lg border border-zinc-200 bg-zinc-50 px-3.5 py-3">
                                <p class="text-[11px] font-bold tracking-[0.16em] text-slate-500 uppercase">Nilai Dosen</p>
                                <p class="mt-1.5 text-sm font-bold text-zinc-950">
                                    {{ formatScore(item.dosen_assessment.total_score) }}
                                </p>
                                <div class="mt-2">
                                    <Badge
                                        variant="outline"
                                        class="rounded-full px-2.5 py-0.5 text-[10px] font-bold shadow-none"
                                        :class="assessmentStatusClass(item.dosen_assessment.status_key)"
                                    >
                                        {{ item.dosen_assessment.status_label }}
                                    </Badge>
                                </div>
                                <p v-if="item.dosen_assessment.submitted_at" class="mt-1 text-xs leading-5 text-zinc-500">
                                    Dikirim {{ formatIndonesianDateLabel(item.dosen_assessment.submitted_at) }}
                                </p>
                                <Button
                                    v-if="item.dosen_assessment.download_url"
                                    type="button"
                                    variant="outline"
                                    class="mt-2 h-8 rounded-lg border-zinc-200 px-3 text-xs font-bold text-zinc-700"
                                    @click="openDownload(item.dosen_assessment.download_url)"
                                >
                                    Unduh Nilai Dosen
                                </Button>
                            </div>
                            <div class="rounded-lg border border-zinc-200 bg-zinc-50 px-3.5 py-3">
                                <p class="text-[11px] font-bold tracking-[0.16em] text-slate-500 uppercase">Nilai Mitra</p>
                                <p class="mt-1.5 text-sm font-bold text-zinc-950">
                                    {{ formatScore(item.mitra_assessment.total_score) }}
                                </p>
                                <div class="mt-2">
                                    <Badge
                                        variant="outline"
                                        class="rounded-full px-2.5 py-0.5 text-[10px] font-bold shadow-none"
                                        :class="assessmentStatusClass(item.mitra_assessment.status_key)"
                                    >
                                        {{ item.mitra_assessment.status_label }}
                                    </Badge>
                                </div>
                                <p v-if="item.mitra_assessment.submitted_at" class="mt-1 text-xs leading-5 text-zinc-500">
                                    Dikirim {{ formatIndonesianDateLabel(item.mitra_assessment.submitted_at) }}
                                </p>
                                <Button
                                    v-if="item.mitra_assessment.download_url"
                                    type="button"
                                    variant="outline"
                                    class="mt-2 h-8 rounded-lg border-zinc-200 px-3 text-xs font-bold text-zinc-700"
                                    @click="openDownload(item.mitra_assessment.download_url)"
                                >
                                    Unduh Nilai Mitra
                                </Button>
                            </div>
                            <div class="rounded-lg border border-zinc-200 bg-zinc-50 px-3.5 py-3 sm:col-span-2">
                                <p class="text-[11px] font-bold tracking-[0.16em] text-slate-500 uppercase">Status PKL</p>
                                <div class="mt-1.5">
                                    <Badge
                                        variant="outline"
                                        class="rounded-full px-2.5 py-0.5 text-[10px] font-bold shadow-none"
                                        :class="registrationStatusClass(item.registration_status)"
                                    >
                                        {{ registrationStatusLabel(item.registration_status) }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="px-5 py-7 text-center">
                    <div class="mx-auto flex size-10 items-center justify-center rounded-full bg-zinc-100">
                        <Inbox class="size-5 text-zinc-400" />
                    </div>
                    <p class="mt-3 text-sm text-zinc-500">
                        Tidak ada data rekap yang cocok dengan filter saat ini.
                    </p>
                </div>

                <div class="flex flex-col gap-3 border-t border-zinc-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-zinc-500">
                        Menampilkan {{ registrations.from ?? 0 }}-{{ registrations.to ?? 0 }}
                        dari {{ registrations.total ?? 0 }} mahasiswa.
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
