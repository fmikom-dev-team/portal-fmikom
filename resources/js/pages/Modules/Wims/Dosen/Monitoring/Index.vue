<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { formatIndonesianDateLabel } from '@/lib/date';
import WimsDosenLayout from '@/layouts/Modules/Wims/Dosen/Layout.vue';

defineOptions({
    layout: WimsDosenLayout,
});

type StudentItem = {
    id: number;
    pendaftaran_id?: number | null;
    name?: string | null;
    nim?: string | null;
    company?: {
        id?: number | null;
        name?: string | null;
    } | null;
    period_start?: string | null;
    period_end?: string | null;
    status_pendaftaran?: string | null;
    dashboard_phase?: 'assigned' | 'upcoming' | 'active' | 'completed' | null;
};

type FilterKey = 'semua' | 'aktif' | 'selesai';

const props = defineProps<{
    students: StudentItem[];
    initialStatus?: string;
}>();

const normalizeFilter = (value?: string | null): FilterKey => {
    if (value === 'aktif') return 'aktif';
    if (value === 'selesai') return 'selesai';
    return 'semua';
};

const search = ref('');
const selectedFilter = ref<FilterKey>(normalizeFilter(props.initialStatus));

const formatDateUi = (value?: string | null) => formatIndonesianDateLabel(value);

const matchesSearch = (student: StudentItem) => {
    const keyword = search.value.trim().toLowerCase();

    if (keyword === '') {
        return true;
    }

    return [student.name, student.nim, student.company?.name]
        .filter(Boolean)
        .some((value) => String(value).toLowerCase().includes(keyword));
};

const matchesFilter = (student: StudentItem) => {
    if (selectedFilter.value === 'semua') {
        return true;
    }

    if (selectedFilter.value === 'aktif') {
        return student.dashboard_phase === 'active';
    }

    return student.dashboard_phase === 'completed';
};

const filteredStudents = computed(() =>
    props.students.filter((student) => matchesSearch(student) && matchesFilter(student)),
);

const pageSubtitle = computed(() => {
    if (selectedFilter.value === 'aktif') {
        return 'Daftar mahasiswa bimbingan yang sedang menjalani PKL pada periode aktif.';
    }

    if (selectedFilter.value === 'selesai') {
        return 'Daftar mahasiswa bimbingan yang sudah menyelesaikan periode PKL.';
    }

    return 'Kelola dan pantau seluruh mahasiswa bimbingan dosen.';
});

const resultLabel = computed(() => {
    const count = filteredStudents.value.length;

    if (selectedFilter.value === 'aktif') {
        return `Menampilkan ${count} mahasiswa aktif.`;
    }

    if (selectedFilter.value === 'selesai') {
        return `Menampilkan ${count} mahasiswa selesai.`;
    }

    return `Menampilkan ${count} mahasiswa.`;
});

const periodLabel = (student: StudentItem) => {
    if (student.period_start && student.period_end) {
        return `${formatDateUi(student.period_start)} - ${formatDateUi(student.period_end)}`;
    }

    return formatDateUi(student.period_start || student.period_end);
};

const phaseLabel = (student: StudentItem) => {
    if (student.dashboard_phase === 'completed' || student.status_pendaftaran === 'selesai') {
        return 'Selesai';
    }

    return 'Aktif';
};

const phaseClass = (student: StudentItem) => {
    if (student.dashboard_phase === 'completed' || student.status_pendaftaran === 'selesai') {
        return 'border-violet-200 bg-violet-50 text-violet-700';
    }

    return 'border-blue-200 bg-blue-50 text-blue-700';
};

const openMonitoring = (student: StudentItem) => {
    router.visit(`/wims/dosen/monitoring/${student.id}?pendaftaran=${student.pendaftaran_id}&mode=view`);
};

const resetSearch = () => {
    search.value = '';
};

const studentInitial = (student: StudentItem) => {
    const words = student.name
        ?.trim()
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2) ?? [];

    if (words.length === 0) {
        return 'M';
    }

    return words
        .map((word) => word.charAt(0).toUpperCase())
        .join('');
};
</script>

<template>
    <Head title="Monitoring Mahasiswa" />

    <div class="min-h-screen bg-wims-bg">
        <div class="mx-auto w-full max-w-[1320px] space-y-4 px-4 py-3 lg:space-y-5 sm:px-6 sm:py-6 lg:px-8 lg:py-8 xl:px-10">
            <header class="relative overflow-hidden rounded-2xl border border-wims-border/50 bg-wims-card/95 px-5 py-5 shadow-[0_1px_3px_rgba(0,0,0,0.04)] sm:px-6 sm:py-6">
                <button
                    type="button"
                    title="Kembali ke Dashboard"
                    aria-label="Kembali ke Dashboard"
                    class="absolute top-5 right-5 inline-flex size-9 items-center justify-center rounded-xl border border-wims-border bg-white/90 text-slate-500 transition duration-200 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-800 sm:top-6 sm:right-6 sm:size-10"
                    @click="router.visit('/wims/dosen/dashboard')"
                >
                    <ArrowLeft class="size-4" />
                </button>
                <div class="flex flex-col gap-2.5 sm:gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-3xl pr-10 sm:pr-12">
                        <h1 class="text-[20px] font-bold tracking-tight text-wims-text sm:text-[24px] lg:text-[30px]">
                            Daftar Mahasiswa Bimbingan
                        </h1>
                        <p class="mt-1.5 text-[13px] leading-relaxed text-slate-600 sm:text-sm">
                            {{ pageSubtitle }}
                        </p>
                    </div>
                </div>
            </header>

            <Card class="rounded-2xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)]">
                <CardContent class="px-5 py-5 sm:px-6">
                    <div class="flex flex-col gap-2.5 sm:gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div class="relative w-full lg:max-w-md">
                            <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
                            <Input
                                v-model="search"
                                type="text"
                                placeholder="Cari nama, NIM, atau perusahaan"
                                class="pl-9"
                            />
                        </div>
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between sm:text-sm lg:justify-end">
                            <Select v-model="selectedFilter">
                                <SelectTrigger class="h-10 w-full min-w-[180px] bg-white sm:w-[200px]">
                                    <SelectValue placeholder="Pilih kategori" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="semua">Semua Kategori</SelectItem>
                                    <SelectItem value="aktif">Mahasiswa Aktif</SelectItem>
                                    <SelectItem value="selesai">Mahasiswa Selesai</SelectItem>
                                </SelectContent>
                            </Select>
                            <div class="flex flex-col gap-1.5 text-[13px] text-slate-600 sm:items-end sm:text-sm">
                                <p>{{ resultLabel }}</p>
                                <button
                                    v-if="search"
                                    type="button"
                                    class="text-left text-[13px] font-bold text-[#0F62FE] hover:text-[#0050E6] sm:text-right sm:text-sm"
                                    @click="resetSearch"
                                >
                                    Reset search
                                </button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <section class="space-y-4">
                <div v-if="filteredStudents.length" class="space-y-6">
                    <Card
                        v-for="student in filteredStudents"
                        :key="student.pendaftaran_id ?? student.id"
                        class="rounded-2xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)]"
                    >
                        <CardContent class="px-5 py-5 sm:px-6">
                            <div class="relative flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <Badge
                                    variant="outline"
                                    class="absolute top-0 right-0 rounded-full px-3 py-1 text-[11px] font-bold sm:hidden"
                                    :class="phaseClass(student)"
                                >
                                    {{ phaseLabel(student) }}
                                </Badge>
                                <div class="flex min-w-0 flex-1 items-start gap-3">
                                    <div class="flex size-12 shrink-0 items-center justify-center rounded-xl border border-wims-border bg-slate-100 text-sm font-bold text-slate-500">
                                        {{ studentInitial(student) }}
                                    </div>

                                    <div class="min-w-0 flex-1 pr-20 sm:pr-0">
                                        <p class="text-[13px] font-bold text-wims-text">
                                            {{ student.name || 'Mahasiswa' }}
                                        </p>
                                        <div class="mt-1 flex flex-col gap-1 text-[11px] text-slate-500 sm:flex-row sm:flex-wrap sm:items-center sm:gap-x-3 sm:gap-y-1">
                                            <span>{{ student.nim || '-' }}</span>
                                            <span class="hidden text-slate-300 sm:inline">&bull;</span>
                                            <span>{{ student.company?.name || 'Perusahaan belum tersedia' }}</span>
                                            <span class="hidden text-slate-300 sm:inline">&bull;</span>
                                            <span class="text-[11px] leading-5">Periode {{ periodLabel(student) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                    <Badge
                                        variant="outline"
                                        class="hidden w-fit rounded-full px-3 py-1 text-[11px] font-bold sm:inline-flex"
                                        :class="phaseClass(student)"
                                    >
                                        {{ phaseLabel(student) }}
                                    </Badge>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="h-10 rounded-lg border-wims-border bg-wims-card px-3.5 text-[13px] font-bold text-slate-700 hover:bg-slate-50"
                                        @click="openMonitoring(student)"
                                    >
                                        Lihat Monitoring
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card
                    v-else
                    class="rounded-xl border border-dashed border-wims-border bg-wims-card py-0 shadow-none"
                >
                    <CardContent class="px-5 py-8 text-center">
                        <p class="text-[13px] font-bold text-wims-text">
                            Tidak ada mahasiswa yang sesuai dengan pencarian.
                        </p>
                        <p class="mt-1 text-[11px] leading-5 text-slate-500">
                            Coba ubah kata kunci pencarian yang dipakai.
                        </p>
                    </CardContent>
                </Card>
            </section>
        </div>
    </div>
</template>
