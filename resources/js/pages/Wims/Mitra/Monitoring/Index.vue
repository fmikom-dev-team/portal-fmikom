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
import { formatIndonesianDateLabel } from '@/lib/date';
import WimsMitraLayout from '@/layouts/Wims/Mitra/Layout.vue';

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
    company?: string | null;
    mentor_name?: string | null;
    dosen_name?: string | null;
    period_start?: string | null;
    period_end?: string | null;
    submitted_at?: string | null;
    status_pendaftaran?: string | null;
    dashboard_phase?: 'assigned' | 'upcoming' | 'active' | 'completed' | null;
    attendance_date?: string | null;
    latest_logbook_date?: string | null;
};

const props = defineProps<{
    students: StudentItem[];
    initialStatus?: string;
}>();

const search = ref('');
const selectedStudent = ref<StudentItem | null>(null);

const initialFilter = computed(() => {
    if (props.initialStatus === 'aktif') return 'aktif';
    if (props.initialStatus === 'selesai') return 'selesai';
    return 'semua';
});

const matchesSearch = (student: StudentItem) => {
    const keyword = search.value.trim().toLowerCase();

    if (keyword === '') {
        return true;
    }

    return [student.name, student.nim, student.company]
        .filter(Boolean)
        .some((value) => String(value).toLowerCase().includes(keyword));
};

const filteredStudents = computed(() =>
    props.students.filter(
        (student) =>
            matchesSearch(student) &&
            (initialFilter.value === 'semua' ||
                (initialFilter.value === 'aktif' && student.dashboard_phase === 'active') ||
                (initialFilter.value === 'selesai' && student.dashboard_phase === 'completed')),
    ),
);

const formatDateUi = (value?: string | null) => formatIndonesianDateLabel(value);

const phaseLabel = (phase?: StudentItem['dashboard_phase']) => {
    if (phase === 'completed') return 'Selesai';
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

const pageTitle = computed(() => {
    if (initialFilter.value === 'aktif') return 'Mahasiswa Aktif';
    if (initialFilter.value === 'selesai') return 'Mahasiswa Selesai';
    return 'Monitoring Mahasiswa';
});

const pageSubtitle = computed(() => {
    if (initialFilter.value === 'aktif') {
        return 'Daftar mahasiswa magang yang sedang berjalan di perusahaan mitra.';
    }

    if (initialFilter.value === 'selesai') {
        return 'Daftar mahasiswa yang sudah menyelesaikan periode magang.';
    }

    return 'Daftar lengkap mahasiswa magang pada perusahaan mitra.';
});

const resultLabel = computed(() => {
    const count = filteredStudents.value.length;

    if (initialFilter.value === 'aktif') {
        return `Menampilkan ${count} mahasiswa aktif.`;
    }

    if (initialFilter.value === 'selesai') {
        return `Menampilkan ${count} mahasiswa selesai.`;
    }

    return `Menampilkan ${count} mahasiswa.`;
});

const emptyTitle = computed(() => {
    if (initialFilter.value === 'aktif') return 'Belum ada mahasiswa aktif.';
    if (initialFilter.value === 'selesai') return 'Belum ada mahasiswa selesai.';
    return 'Belum ada mahasiswa bimbingan.';
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
            <header class="relative overflow-hidden rounded-2xl border border-wims-border/50 bg-wims-card/95 px-5 py-5 shadow-[0_1px_3px_rgba(0,0,0,0.04)] sm:px-6 sm:py-6">
                <div class="flex flex-col gap-2.5 sm:gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-3xl">
                        <h1 class="text-[17px] font-bold tracking-tight text-wims-text sm:text-[20px]">
                            Daftar Mahasiswa Magang
                        </h1>
                        <p class="mt-1.5 text-[12px] leading-5 text-slate-600 sm:text-sm sm:leading-6">
                            {{ pageSubtitle }}
                        </p>
                    </div>
                    <Button
                        type="button"
                        variant="outline"
                        class="inline-flex h-9 w-fit self-start items-center gap-1.5 rounded-lg border-wims-border bg-wims-card px-3 text-[11px] font-bold text-slate-700 transition duration-200 hover:border-slate-300 hover:bg-slate-50 sm:px-3.5 sm:text-xs lg:self-auto"
                        @click="router.visit('/wims/mitra/dashboard')"
                    >
                        <ArrowLeft class="size-3.5" />
                        Kembali ke Dashboard
                    </Button>
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
                        <div class="flex flex-col gap-1.5 text-[13px] text-slate-600 sm:flex-row sm:items-center sm:justify-between sm:text-sm lg:justify-end">
                            <p>{{ resultLabel }}</p>
                            <button
                                v-if="search"
                                type="button"
                                class="text-left text-[13px] font-bold text-[#0F62FE] hover:text-[#0050E6] sm:text-right sm:text-sm"
                                @click="resetFilters"
                            >
                                Reset search
                            </button>
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
                        <CardContent class="px-5 py-5 sm:px-6">
                            <div class="relative flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <Badge
                                    v-if="phaseLabel(student.dashboard_phase)"
                                    variant="outline"
                                    class="absolute top-0 right-0 rounded-full px-3 py-1 text-[11px] font-bold sm:hidden"
                                    :class="phaseClass(student.dashboard_phase)"
                                >
                                    {{ phaseLabel(student.dashboard_phase) }}
                                </Badge>
                                <div class="flex min-w-0 flex-1 items-start gap-3">
                                    <button
                                        type="button"
                                        class="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-wims-border bg-slate-100 text-sm font-bold text-slate-500 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                                        :title="`Lihat profil ${student.name || 'mahasiswa'}`"
                                        @click="openStudentProfile(student)"
                                    >
                                        <span>{{ studentInitial(student) }}</span>
                                    </button>

                                    <div class="min-w-0 flex-1 pr-20 sm:pr-0">
                                        <p class="text-sm font-bold text-wims-text">
                                            {{ student.name || 'Mahasiswa' }}
                                        </p>
                                        <div class="mt-1 flex flex-col gap-1 text-xs text-slate-500 sm:flex-row sm:flex-wrap sm:items-center sm:gap-x-3 sm:gap-y-1">
                                            <span>{{ student.nim || '-' }}</span>
                                            <span class="hidden text-slate-300 sm:inline">•</span>
                                            <span>{{ student.company || 'Perusahaan belum tersedia' }}</span>
                                            <span class="hidden text-slate-300 sm:inline">•</span>
                                            <span class="text-[11px] leading-4 sm:text-xs sm:leading-5">Periode {{ periodLabel(student) }}</span>
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
                                        class="h-10 rounded-lg border-wims-border bg-wims-card px-3.5 text-sm font-bold text-slate-700 hover:bg-slate-50"
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
                        <p class="text-sm font-bold text-wims-text">
                            {{ props.students.length ? 'Tidak ada mahasiswa yang cocok.' : emptyTitle }}
                        </p>
                        <p class="mt-1 text-sm leading-6 text-slate-500">
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
                <div class="min-h-0 overflow-y-auto overflow-x-hidden px-5 py-5 sm:px-6 sm:py-6">
                    <DialogHeader class="space-y-2 text-left">
                        <DialogTitle class="text-base font-bold text-wims-text">
                            Profil Mahasiswa
                        </DialogTitle>
                        <DialogDescription class="text-[13px] leading-6 text-slate-600 sm:text-sm">
                            Ringkasan profil mahasiswa magang dalam daftar monitoring mitra.
                        </DialogDescription>
                    </DialogHeader>

                    <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-[300px_minmax(0,1fr)] lg:items-start">
                        <div class="h-fit min-w-0 self-start rounded-xl border border-wims-border bg-slate-50 p-5">
                            <div class="flex flex-col items-center text-center">
                                <div class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-wims-border bg-white text-xl font-bold text-slate-500 shadow-sm">
                                    <span>{{ studentInitial(selectedStudent) }}</span>
                                </div>
                                <p class="mt-3 min-w-0 break-words text-base font-bold text-wims-text">
                                    {{ selectedStudent.name || 'Mahasiswa' }}
                                </p>
                                <p class="mt-1 text-sm text-slate-500">
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
                                    <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Email</p>
                                    <p class="mt-1 break-all font-medium text-wims-text">
                                        {{ selectedStudent.email || '-' }}
                                    </p>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Telepon</p>
                                    <p class="mt-1 break-words font-medium text-wims-text">
                                        {{ selectedStudent.phone || '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="h-fit min-w-0 self-start rounded-xl border border-wims-border bg-wims-card p-5">
                            <div>
                                <h3 class="text-base font-bold text-wims-text">
                                    Ringkasan Magang
                                </h3>
                                <p class="mt-1 text-[13px] leading-6 text-slate-600 sm:text-sm">
                                    Data penempatan mahasiswa pada perusahaan mitra yang sedang dipantau.
                                </p>
                            </div>

                            <div class="mt-4 space-y-2.5">
                                <div class="min-w-0 rounded-xl border border-wims-border bg-slate-50 px-4 py-3">
                                    <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Perusahaan</p>
                                    <p class="mt-1 break-words text-sm font-bold text-wims-text">
                                        {{ selectedStudent.company || 'Perusahaan belum tersedia' }}
                                    </p>
                                </div>
                                <div class="min-w-0 rounded-xl border border-wims-border bg-slate-50 px-4 py-3">
                                    <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Periode</p>
                                    <p class="mt-1 break-words text-sm font-bold text-wims-text">
                                        {{ periodLabel(selectedStudent) }}
                                    </p>
                                </div>
                                <div class="min-w-0 rounded-xl border border-wims-border bg-slate-50 px-4 py-3">
                                    <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Pembimbing Lapangan</p>
                                    <p class="mt-1 break-words text-sm font-bold text-wims-text">
                                        {{ selectedStudent.mentor_name || '-' }}
                                    </p>
                                </div>
                                <div class="min-w-0 rounded-xl border border-wims-border bg-slate-50 px-4 py-3">
                                    <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Dosen Pembimbing</p>
                                    <p class="mt-1 break-words text-sm font-bold text-wims-text">
                                        {{ selectedStudent.dosen_name || '-' }}
                                    </p>
                                </div>
                                <div class="min-w-0 rounded-xl border border-wims-border bg-slate-50 px-4 py-3">
                                    <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Terdaftar Pada</p>
                                    <p class="mt-1 break-words text-sm font-bold text-wims-text">
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
                            class="h-10 rounded-lg border-wims-border bg-wims-card px-4 text-sm font-bold text-slate-700 hover:bg-slate-50"
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
