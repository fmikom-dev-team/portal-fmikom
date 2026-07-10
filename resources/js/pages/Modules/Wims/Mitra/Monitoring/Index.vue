<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { formatIndonesianDateLabel } from '@/lib/date';
import WimsMitraLayout from '@/layouts/Modules/Wims/Mitra/Layout.vue';

defineOptions({
    layout: WimsMitraLayout,
});

type StudentItem = {
    registration_id: number;
    student_id?: number | null;
    photo_url?: string | null;
    name?: string | null;
    email?: string | null;
    nim?: string | null;
    phone?: string | null;
    company?: {
        id?: number | null;
        name?: string | null;
    } | null;
    mentor?: {
        id?: number | null;
        name?: string | null;
        role_context?: {
            slug?: string | null;
            label?: string | null;
        } | null;
    } | null;
    lecturer?: {
        id?: number | null;
        name?: string | null;
        role_context?: {
            slug?: string | null;
            label?: string | null;
        } | null;
    } | null;
    period_start?: string | null;
    period_end?: string | null;
    submitted_at?: string | null;
    status_pendaftaran?: string | null;
    dashboard_phase?: 'assigned' | 'upcoming' | 'active' | 'completed' | null;
    latest_logbook_date?: string | null;
};

const props = defineProps<{
    students: StudentItem[];
    initialStatus?: string;
}>();

const search = ref('');
const selectedStudent = ref<StudentItem | null>(null);
const selectedFilter = ref<'semua' | 'aktif' | 'selesai'>(
    props.initialStatus === 'aktif'
        ? 'aktif'
        : props.initialStatus === 'selesai'
          ? 'selesai'
          : 'semua',
);

const matchesSearch = (student: StudentItem) => {
    const keyword = search.value.trim().toLowerCase();

    if (keyword === '') {
        return true;
    }

    return [student.name, student.nim, student.company?.name]
        .filter(Boolean)
        .some((value) => String(value).toLowerCase().includes(keyword));
};

const filteredStudents = computed(() =>
    props.students.filter(
        (student) =>
            matchesSearch(student) &&
            (selectedFilter.value === 'semua' ||
                (selectedFilter.value === 'aktif' && student.dashboard_phase === 'active') ||
                (selectedFilter.value === 'selesai' && student.dashboard_phase === 'completed')),
    ),
);

const totalStudents = computed(() => props.students.length);
const activeStudentsCount = computed(
    () => props.students.filter((student) => student.dashboard_phase === 'active').length,
);
const completedStudentsCount = computed(
    () => props.students.filter((student) => student.dashboard_phase === 'completed').length,
);

const formatDateUi = (value?: string | null) => formatIndonesianDateLabel(value);

const phaseLabel = (phase?: StudentItem['dashboard_phase']) => {
    if (phase === 'completed') return 'Siap Dinilai';
    if (phase === 'active') return 'Aktif';
    return null;
};

const phaseClass = (phase?: StudentItem['dashboard_phase']) => {
    if (phase === 'completed') {
        return 'border-violet-200 bg-violet-50 text-violet-700';
    }

    return 'border-blue-200 bg-blue-50 text-blue-700';
};

const periodLabel = (student: StudentItem) => {
    if (student.period_start && student.period_end) {
        return `${formatDateUi(student.period_start)} - ${formatDateUi(student.period_end)}`;
    }

    return formatDateUi(student.period_start || student.period_end);
};

const openMonitoring = (student: StudentItem) => {
    if (!student.student_id) {
        return;
    }

    router.visit(`/wims/mitra/monitoring/${student.student_id}`);
};

const openStudentProfile = (student: StudentItem) => {
    selectedStudent.value = student;
};

const resetFilters = () => {
    search.value = '';
};

const pageSubtitle = computed(() => {
    if (selectedFilter.value === 'aktif') {
        return 'Mahasiswa aktif di mitra ini.';
    }

    if (selectedFilter.value === 'selesai') {
        return 'Mahasiswa siap dinilai.';
    }

    return 'Semua mahasiswa bimbingan.';
});

const resultLabel = computed(() => {
    const count = filteredStudents.value.length;

    if (selectedFilter.value === 'aktif') {
        return `${count} aktif.`;
    }

    if (selectedFilter.value === 'selesai') {
        return `${count} siap dinilai.`;
    }

    return `Total ${count}.`;
});

const emptyTitle = computed(() => {
    if (selectedFilter.value === 'aktif') return 'Belum ada data aktif.';
    if (selectedFilter.value === 'selesai') return 'Belum ada data siap dinilai.';
    return 'Belum ada data.';
});

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
            <header class="relative overflow-hidden rounded-2xl border border-wims-border/50 bg-wims-card/95 px-4 py-4 shadow-[0_1px_3px_rgba(0,0,0,0.04)] sm:px-6 sm:py-6">
                <button
                    type="button"
                    title="Kembali ke Dashboard"
                    aria-label="Kembali ke Dashboard"
                    class="absolute top-5 right-5 inline-flex size-9 items-center justify-center rounded-xl border border-wims-border bg-white/90 text-slate-500 transition duration-200 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-800 sm:top-6 sm:right-6 sm:size-10"
                    @click="router.visit('/wims/mitra/dashboard')"
                >
                    <ArrowLeft class="size-4" />
                </button>
                <div class="flex flex-col gap-2.5 sm:gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-3xl pr-10 sm:pr-12">
                        <h1 class="text-[18px] font-bold tracking-tight text-wims-text sm:text-[22px] lg:text-[28px]">
                            Mahasiswa Magang
                        </h1>
                        <p class="mt-1.5 text-[12px] leading-5 text-slate-600 sm:text-[13px]">
                            {{ pageSubtitle }}
                        </p>
                    </div>
                </div>
            </header>

            <Card class="rounded-2xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)]">
                <CardContent class="px-4 py-4 sm:px-5 sm:py-5">
                    <div class="space-y-3">
                        <div class="grid grid-cols-3 gap-2">
                            <div class="rounded-xl border border-slate-200/80 bg-slate-50/80 px-3 py-2.5">
                                <p class="text-[10px] font-medium uppercase tracking-[0.08em] text-slate-400">Total</p>
                                <p class="mt-1 text-[15px] font-bold text-wims-text">{{ totalStudents }}</p>
                            </div>
                            <div class="rounded-xl border border-blue-200/80 bg-blue-50/80 px-3 py-2.5">
                                <p class="text-[10px] font-medium uppercase tracking-[0.08em] text-blue-500">Aktif</p>
                                <p class="mt-1 text-[15px] font-bold text-blue-700">{{ activeStudentsCount }}</p>
                            </div>
                            <div class="rounded-xl border border-violet-200/80 bg-violet-50/80 px-3 py-2.5">
                                <p class="text-[10px] font-medium uppercase tracking-[0.08em] text-violet-500">Siap Dinilai</p>
                                <p class="mt-1 text-[15px] font-bold text-violet-700">{{ completedStudentsCount }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2 sm:gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div class="relative min-w-0 flex-1 lg:max-w-md">
                                <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
                                <Input
                                    v-model="search"
                                    type="text"
                                    placeholder="Cari mahasiswa, NIM, atau perusahaan"
                                    class="pl-9"
                                />
                            </div>
                            <div class="flex shrink-0 flex-col items-end gap-2 sm:flex-row sm:items-center sm:justify-between sm:text-sm lg:justify-end">
                                <Select v-model="selectedFilter">
                                    <SelectTrigger class="h-9 w-[132px] bg-white sm:w-[200px] dark:bg-slate-800">
                                        <SelectValue placeholder="Kategori" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="semua">Semua Kategori</SelectItem>
                                        <SelectItem value="aktif">Mahasiswa Aktif</SelectItem>
                                        <SelectItem value="selesai">Siap Dinilai</SelectItem>
                                    </SelectContent>
                                </Select>
                                <div v-if="search" class="flex items-center justify-end text-[13px] text-slate-600 sm:text-sm">
                                    <button
                                        type="button"
                                        class="text-[13px] font-bold text-[#0F62FE] hover:text-[#0050E6] sm:text-sm"
                                        @click="resetFilters"
                                    >
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <section class="space-y-4">
                <div v-if="filteredStudents.length" class="space-y-4">
                    <Card
                        v-for="student in filteredStudents"
                        :key="student.registration_id"
                        class="rounded-2xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)]"
                    >
                        <CardContent class="px-4 py-4 sm:px-5 sm:py-5">
                            <div class="md:hidden">
                                <div class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-3.5">
                                    <div class="flex items-start gap-3">
                                        <button
                                            type="button"
                                            class="flex size-11 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-wims-border bg-white text-sm font-bold text-slate-500 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                                            :title="`Lihat profil ${student.name || 'mahasiswa'}`"
                                            @click="openStudentProfile(student)"
                                        >
                                            <span>{{ studentInitial(student) }}</span>
                                        </button>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="min-w-0 text-[13px] font-bold text-wims-text">
                                                    {{ student.name || 'Mahasiswa' }}
                                                </p>
                                                <Badge
                                                    v-if="phaseLabel(student.dashboard_phase)"
                                                    variant="outline"
                                                    class="w-fit rounded-full px-2.5 py-1 text-[10px] font-bold"
                                                    :class="phaseClass(student.dashboard_phase)"
                                                >
                                                    {{ phaseLabel(student.dashboard_phase) }}
                                                </Badge>
                                            </div>
                                            <p class="mt-1 text-[11px] text-slate-500">
                                                {{ student.nim || '-' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-3 space-y-2.5 border-t border-slate-200 pt-3">
                                        <div>
                                            <p class="text-[10px] font-medium uppercase tracking-[0.08em] text-slate-400">Perusahaan</p>
                                            <p class="mt-1 text-[12px] font-bold leading-5 text-wims-text">
                                                {{ student.company?.name || 'Perusahaan belum tersedia' }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-medium uppercase tracking-[0.08em] text-slate-400">Periode Magang</p>
                                            <p class="mt-1 text-[12px] font-bold leading-5 text-wims-text">
                                                {{ periodLabel(student) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <Button
                                            type="button"
                                            class="h-9 w-full rounded-xl bg-[#0F62FE] px-3 text-[12px] font-bold text-white hover:bg-[#0050E6]"
                                            @click="openMonitoring(student)"
                                        >
                                            Monitoring
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <div class="relative hidden md:flex md:flex-col md:gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex min-w-0 flex-1 items-start gap-3">
                                    <button
                                        type="button"
                                        class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-wims-border bg-slate-100 text-xs font-bold text-slate-500 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 sm:size-12 sm:text-sm"
                                        :title="`Lihat profil ${student.name || 'mahasiswa'}`"
                                        @click="openStudentProfile(student)"
                                    >
                                        <span>{{ studentInitial(student) }}</span>
                                    </button>

                                    <div class="min-w-0 flex-1">
                                        <p class="text-[12px] font-bold text-wims-text">
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

                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between lg:justify-end">
                                    <Badge
                                        v-if="phaseLabel(student.dashboard_phase)"
                                        variant="outline"
                                        class="hidden w-fit rounded-full px-3 py-1 text-[11px] font-bold sm:inline-flex"
                                        :class="phaseClass(student.dashboard_phase)"
                                    >
                                        {{ phaseLabel(student.dashboard_phase) }}
                                    </Badge>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="h-9 rounded-lg border-wims-border bg-wims-card px-3.5 text-[12px] font-bold text-slate-700 hover:bg-slate-50"
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
                    <CardContent class="px-4 py-6 text-center">
                        <p class="text-[12px] font-bold text-wims-text">
                            {{ props.students.length ? 'Tidak ada mahasiswa yang cocok.' : emptyTitle }}
                        </p>
                        <p class="mt-1 text-[11px] leading-5 text-slate-500">
                            {{
                                props.students.length
                                    ? 'Coba ubah kata kunci pencarian atau filter monitoring yang dipakai.'
                                    : 'Data monitoring akan muncul setelah mahasiswa ditempatkan pada perusahaan mitra ini.'
                            }}
                        </p>
                    </CardContent>
                </Card>
            </section>
        </div>
    </div>

    <Dialog :open="!!selectedStudent" @update:open="(open) => { if (!open) selectedStudent = null; }">
        <DialogContent class="w-full max-w-[calc(100vw-2rem)] sm:max-w-[980px] max-h-[88vh] overflow-hidden rounded-2xl border border-wims-border bg-wims-card p-0 shadow-[0_24px_48px_-30px_rgba(15,23,42,0.28)]">
            <div v-if="selectedStudent" class="flex flex-col">
                <div class="min-h-0 overflow-y-auto overflow-x-hidden px-4 py-4 sm:px-6 sm:py-6">
                    <DialogHeader class="space-y-2 text-left">
                        <DialogTitle class="text-[14px] font-bold text-wims-text">
                            Profil Mahasiswa
                        </DialogTitle>
                        <DialogDescription class="text-[13px] leading-relaxed text-slate-600 sm:text-sm">
                            Ringkasan profil mahasiswa magang dalam daftar monitoring mitra.
                        </DialogDescription>
                    </DialogHeader>

                    <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-[300px_minmax(0,1fr)] lg:items-start">
                        <div class="h-fit min-w-0 self-start rounded-xl border border-wims-border bg-slate-50 p-4 sm:p-5">
                            <div class="flex flex-col items-center text-center">
                                <div class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-wims-border bg-white text-base font-bold text-slate-500 shadow-sm sm:size-20 sm:text-xl">
                                    <span>{{ studentInitial(selectedStudent) }}</span>
                                </div>
                                <p class="mt-3 min-w-0 break-words text-[12px] font-bold text-wims-text">
                                    {{ selectedStudent.name || 'Mahasiswa' }}
                                </p>
                                <p class="mt-1 text-[11px] text-slate-500 sm:text-sm">
                                    {{ selectedStudent.nim || '-' }}
                                </p>
                                <Badge
                                    v-if="phaseLabel(selectedStudent.dashboard_phase)"
                                    variant="outline"
                                    class="mt-2 w-fit rounded-full px-3 py-1 text-[11px] font-bold"
                                    :class="phaseClass(selectedStudent.dashboard_phase)"
                                >
                                    {{ phaseLabel(selectedStudent.dashboard_phase) }}
                                </Badge>
                            </div>

                            <div class="mt-4 space-y-3 border-t border-wims-border pt-4 text-sm">
                                <div class="min-w-0">
                                    <p class="text-[10px] font-medium uppercase tracking-[0.08em] text-slate-400">Email</p>
                                    <p class="mt-1 break-all font-medium text-wims-text">
                                        {{ selectedStudent.email || '-' }}
                                    </p>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[10px] font-medium uppercase tracking-[0.08em] text-slate-400">Telepon</p>
                                    <p class="mt-1 break-words font-medium text-wims-text">
                                        {{ selectedStudent.phone || '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="h-fit min-w-0 self-start rounded-xl border border-wims-border bg-wims-card p-4 sm:p-5">
                            <div>
                                <h3 class="text-[14px] font-bold text-wims-text">
                                    Ringkasan Magang
                                </h3>
                                <p class="mt-1 text-[11px] leading-5 text-slate-600 sm:text-sm">
                                    Data penempatan mahasiswa pada perusahaan mitra yang sedang dipantau.
                                </p>
                            </div>

                            <div class="mt-4 space-y-2.5">
                                <div class="min-w-0 rounded-xl border border-wims-border bg-slate-50 px-4 py-3">
                                    <p class="text-[10px] font-medium uppercase tracking-[0.08em] text-slate-400">Perusahaan</p>
                                    <p class="mt-1 break-words text-[12px] font-bold text-wims-text">
                                        {{ selectedStudent.company?.name || 'Perusahaan belum tersedia' }}
                                    </p>
                                </div>
                                <div class="min-w-0 rounded-xl border border-wims-border bg-slate-50 px-4 py-3">
                                    <p class="text-[10px] font-medium uppercase tracking-[0.08em] text-slate-400">Periode</p>
                                    <p class="mt-1 break-words text-[12px] font-bold text-wims-text">
                                        {{ periodLabel(selectedStudent) }}
                                    </p>
                                </div>
                                <div class="min-w-0 rounded-xl border border-wims-border bg-slate-50 px-4 py-3">
                                    <p class="text-[10px] font-medium uppercase tracking-[0.08em] text-slate-400">Pembimbing Lapangan</p>
                                    <p class="mt-1 break-words text-[12px] font-bold text-wims-text">
                                        {{ selectedStudent.mentor?.name || '-' }}
                                    </p>
                                </div>
                                <div class="min-w-0 rounded-xl border border-wims-border bg-slate-50 px-4 py-3">
                                    <p class="text-[10px] font-medium uppercase tracking-[0.08em] text-slate-400">Dosen Pembimbing</p>
                                    <p class="mt-1 break-words text-[12px] font-bold text-wims-text">
                                        {{ selectedStudent.lecturer?.name || '-' }}
                                    </p>
                                </div>
                                <div class="min-w-0 rounded-xl border border-wims-border bg-slate-50 px-4 py-3">
                                    <p class="text-[10px] font-medium uppercase tracking-[0.08em] text-slate-400">Terdaftar Pada</p>
                                    <p class="mt-1 break-words text-[12px] font-bold text-wims-text">
                                        {{ formatDateUi(selectedStudent.submitted_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-wims-border bg-wims-card px-5 py-2.5 sm:px-6">
                    <div class="flex justify-end">
                        <Button
                            type="button"
                            variant="outline"
                            class="h-9 rounded-lg border-wims-border bg-wims-card px-4 text-[12px] font-bold text-slate-700 hover:bg-slate-50"
                            @click="selectedStudent = null"
                        >
                            Tutup
                        </Button>
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>





