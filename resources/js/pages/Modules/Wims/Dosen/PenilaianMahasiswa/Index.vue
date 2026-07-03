<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    ArrowLeft,
    ClipboardCheck,
    FilePenLine,
    Search,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatIndonesianDateLabel } from '@/lib/date';
import WimsDosenLayout from '@/layouts/Modules/Wims/Dosen/Layout.vue';

defineOptions({
    layout: WimsDosenLayout,
});

type StudentItem = {
    id: number;
    student: {
        name?: string | null;
        nim?: string | null;
        email?: string | null;
    };
    company?: {
        id?: number | null;
        name?: string | null;
    } | null;
    period: {
        start?: string | null;
        end?: string | null;
        label?: string | null;
    };
    registration_status?: string | null;
    dashboard_phase?: 'assigned' | 'upcoming' | 'active' | 'completed' | null;
    assessment: {
        status_key: 'not_assessed' | 'draft' | 'submitted';
        status_label: string;
        total_score?: number | null;
        submitted_at?: string | null;
        template_name?: string | null;
    };
};

const props = defineProps<{
    summary: {
        total_students: number;
        not_assessed: number;
        draft: number;
        submitted: number;
    };
    students: StudentItem[];
}>();

const search = ref('');
const status = ref<'all' | 'not_assessed' | 'draft' | 'submitted'>('all');

const filteredStudents = computed(() => {
    const keyword = search.value.trim().toLowerCase();

    return props.students.filter((item) => {
        const matchesKeyword =
            keyword === '' ||
            [
                item.student.name,
                item.student.nim,
                item.student.email,
                item.company?.name,
                item.period.label,
            ]
                .filter(Boolean)
                .some((value) => String(value).toLowerCase().includes(keyword));

        if (!matchesKeyword) {
            return false;
        }

        if (status.value === 'all') {
            return true;
        }

        return item.assessment.status_key === status.value;
    });
});

const hasStudents = computed(() => props.students.length > 0);

const statusLabelClass = (statusKey: StudentItem['assessment']['status_key']) => {
    if (statusKey === 'submitted') {
        return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    }

    if (statusKey === 'draft') {
        return 'border-amber-200 bg-amber-50 text-amber-700';
    }

    return 'border-slate-200 bg-slate-50 text-slate-600';
};

const registrationStatusLabel = (value?: string | null) => {
    if (value === 'selesai') return 'Selesai';
    if (value === 'aktif') return 'Aktif';
    if (value === 'approved') return 'Disetujui';

    return 'Penugasan';
};

const registrationStatusClass = (value?: string | null) => {
    if (value === 'selesai') {
        return 'border-violet-200 bg-violet-50 text-violet-700';
    }

    if (value === 'aktif') {
        return 'border-sky-200 bg-sky-50 text-sky-700';
    }

    if (value === 'approved') {
        return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    }

    return 'border-slate-200 bg-slate-50 text-slate-600';
};

const assessmentStatusValue = (item: StudentItem) =>
    item.dashboard_phase === 'completed' ? 'selesai' : item.registration_status;

const actionLabel = (item: StudentItem) => {
    if (item.assessment.status_key === 'submitted') {
        return 'Lihat Nilai';
    }

    if (item.assessment.status_key === 'draft') {
        return 'Lanjutkan Draft';
    }

    return 'Isi Nilai';
};

const openAssessment = (id: number) => {
    router.visit(`/wims/dosen/penilaian-mahasiswa/${id}`);
};

const goBack = () => {
    router.visit('/wims/dosen/dashboard');
};

</script>

<template>
    <Head title="Penilaian Mahasiswa" />

    <div class="min-h-screen bg-wims-bg">
        <div class="mx-auto flex w-full max-w-[1320px] flex-col gap-4 px-4 py-3 lg:gap-5 sm:px-6 sm:py-6 lg:px-8 lg:py-8 xl:px-10">
            <header class="relative overflow-hidden rounded-2xl border border-wims-border/50 bg-wims-card/95 px-5 py-5 shadow-[0_1px_3px_rgba(0,0,0,0.04)] sm:px-6 sm:py-6">
                <button
                    type="button"
                    title="Kembali ke Dashboard"
                    aria-label="Kembali ke Dashboard"
                    class="absolute top-5 right-5 inline-flex size-9 items-center justify-center rounded-xl border border-wims-border bg-white/90 text-slate-500 transition duration-200 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-800 sm:top-6 sm:right-6 sm:size-10"
                    @click="goBack"
                >
                    <ArrowLeft class="size-4" />
                </button>
                <div class="flex flex-col gap-2.5 sm:gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-3xl pr-10 sm:pr-12">
                        <h1 class="text-[20px] font-bold tracking-tight text-wims-text sm:text-[24px] lg:text-[30px]">
                            Ringkasan Penilaian Dosen
                        </h1>
                        <p class="mt-1.5 max-w-3xl text-[13px] leading-relaxed text-slate-600 sm:text-sm">
                            Kelola nilai dosen untuk mahasiswa bimbingan berdasarkan template penilaian.
                        </p>
                    </div>
                </div>
            </header>

            <Card class="rounded-2xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)] transition duration-200 hover:shadow-[0_22px_42px_-32px_rgba(15,23,42,0.2)]">
                <CardHeader class="px-5 pt-5 pb-4 sm:px-6 sm:pt-5">
                    <div class="flex flex-col gap-3 sm:gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <CardTitle class="text-[15px] font-bold text-wims-text">
                                Daftar Mahasiswa
                            </CardTitle>
                            <CardDescription class="mt-1 text-[13px] leading-relaxed text-slate-600 sm:text-sm">
                                Isi nilai baru, lanjutkan draft, atau lihat nilai yang sudah dikirim.
                            </CardDescription>
                        </div>
                        <div class="flex w-full flex-col gap-2.5 sm:gap-3 lg:w-auto lg:flex-row">
                            <div class="relative w-full lg:w-80">
                                <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Cari nama, NIM, atau perusahaan..."
                                    class="h-10 w-full rounded-lg border border-wims-border bg-white pr-3 pl-9 text-base text-wims-text outline-none transition duration-200 hover:border-slate-300 focus:border-[#0F62FE] focus:ring-2 focus:ring-[#0F62FE]/10 sm:text-sm"
                                />
                            </div>
                            <select
                                v-model="status"
                                class="h-10 rounded-lg border border-wims-border bg-white px-3 text-base text-wims-text outline-none transition duration-200 hover:border-slate-300 focus:border-[#0F62FE] focus:ring-2 focus:ring-[#0F62FE]/10 sm:text-sm"
                            >
                                <option value="all">Semua</option>
                                <option value="not_assessed">Belum Dinilai</option>
                                <option value="draft">Draft</option>
                                <option value="submitted">Sudah Dikirim</option>
                            </select>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="px-0 pb-0">
                    <div v-if="filteredStudents.length" class="hidden overflow-x-auto md:block">
                        <table class="min-w-full border-collapse">
                            <thead class="bg-slate-50/70">
                                <tr class="border-y border-wims-border">
                                    <th class="px-5 py-3 text-left text-sm font-bold uppercase tracking-[0.06em] text-slate-500">Mahasiswa</th>
                                    <th class="px-5 py-3 text-left text-sm font-bold uppercase tracking-[0.06em] text-slate-500">Perusahaan</th>
                                    <th class="px-5 py-3 text-left text-sm font-bold uppercase tracking-[0.06em] text-slate-500">Periode</th>
                                    <th class="px-5 py-3 text-left text-sm font-bold uppercase tracking-[0.06em] text-slate-500">Status Magang</th>
                                    <th class="px-5 py-3 text-left text-sm font-bold uppercase tracking-[0.06em] text-slate-500">Status Penilaian</th>
                                    <th class="px-5 py-3 text-right text-sm font-bold uppercase tracking-[0.06em] text-slate-500">Nilai Dosen</th>
                                    <th class="px-5 py-3 text-right text-sm font-bold uppercase tracking-[0.06em] text-slate-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="item in filteredStudents"
                                    :key="item.id"
                                    class="border-t border-wims-border bg-white align-top transition duration-200 hover:bg-blue-50/40"
                                >
                                    <td class="px-5 py-4">
                                        <p class="text-[13px] font-bold text-wims-text">{{ item.student.name || 'Mahasiswa' }}</p>
                                        <p class="mt-1 text-[11px] text-slate-500">
                                            {{ item.student.nim || '-' }} &bull; {{ item.student.email || '-' }}
                                        </p>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-wims-text">
                                        {{ item.company?.name || '-' }}
                                    </td>
                                    <td class="px-5 py-4 text-sm text-wims-text">
                                        {{ formatIndonesianDateLabel(item.period.label) }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <Badge
                                            variant="outline"
                                            class="rounded-full px-3 py-1 text-[11px] font-bold"
                                            :class="registrationStatusClass(assessmentStatusValue(item))"
                                        >
                                            {{ registrationStatusLabel(assessmentStatusValue(item)) }}
                                        </Badge>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-col gap-2">
                                            <Badge
                                                variant="outline"
                                                class="w-fit rounded-full px-3 py-1 text-[11px] font-bold"
                                                :class="statusLabelClass(item.assessment.status_key)"
                                            >
                                                {{ item.assessment.status_label }}
                                            </Badge>
                                            <p v-if="item.assessment.submitted_at" class="text-[11px] text-slate-500">
                                                Dikirim {{ formatIndonesianDateLabel(item.assessment.submitted_at) }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-right text-[13px] font-bold text-wims-text">
                                        {{ item.assessment.total_score !== null && item.assessment.total_score !== undefined ? item.assessment.total_score.toFixed(2) : '-' }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex justify-end">
                                            <Button
                                                type="button"
                                                class="h-9 rounded-lg bg-[#0F62FE] px-3.5 text-[13px] font-bold text-white shadow-sm transition duration-200 hover:bg-[#0050E6] hover:shadow-md"
                                                @click="openAssessment(item.id)"
                                            >
                                                <FilePenLine class="mr-2 size-4" />
                                                {{ actionLabel(item) }}
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="filteredStudents.length" class="overflow-hidden rounded-2xl border border-wims-border bg-white md:hidden">
                        <div
                            v-for="item in filteredStudents"
                            :key="`mobile-${item.id}`"
                            class="border-b border-wims-border/70 px-4 py-3 last:border-b-0"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-[12px] font-bold leading-5 text-wims-text">{{ item.student.name || 'Mahasiswa' }}</p>
                                    <p class="mt-0.5 break-words text-[11px] leading-5 text-slate-500">
                                        {{ item.student.nim || '-' }} &bull; {{ item.student.email || '-' }}
                                    </p>
                                </div>
                                <Badge
                                    variant="outline"
                                    class="rounded-full px-2.5 py-0.5 text-[11px] font-bold"
                                    :class="statusLabelClass(item.assessment.status_key)"
                                >
                                    {{ item.assessment.status_label }}
                                </Badge>
                            </div>

                            <div class="mt-2.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] text-slate-500">
                                <span class="font-medium text-wims-text">{{ item.company?.name || '-' }}</span>
                                <span class="text-slate-300">&bull;</span>
                                <span>{{ formatIndonesianDateLabel(item.period.label) }}</span>
                                <span class="text-slate-300">&bull;</span>
                                <span class="font-medium text-slate-700">{{ item.assessment.total_score !== null && item.assessment.total_score !== undefined ? item.assessment.total_score.toFixed(2) : '-' }}</span>
                            </div>

                            <div class="mt-2 flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <Badge
                                        variant="outline"
                                        class="rounded-full px-2.5 py-0.5 text-[11px] font-bold"
                                        :class="registrationStatusClass(assessmentStatusValue(item))"
                                    >
                                        {{ registrationStatusLabel(assessmentStatusValue(item)) }}
                                    </Badge>
                                </div>
                                <Button
                                    type="button"
                                    class="h-8 rounded-lg bg-[#0F62FE] px-3 text-[12px] font-bold text-white shadow-sm transition duration-200 hover:bg-[#0050E6]"
                                    @click="openAssessment(item.id)"
                                >
                                    <FilePenLine class="mr-1.5 size-4" />
                                    {{ actionLabel(item) }}
                                </Button>
                            </div>
                        </div>
                    </div>

                    <div v-else class="flex flex-col items-center justify-center gap-3 px-6 py-10 text-center">
                        <div class="flex size-12 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                            <ClipboardCheck class="size-5" />
                        </div>
                        <div v-if="hasStudents">
                            <p class="text-[13px] font-bold text-wims-text">Tidak ada mahasiswa yang sesuai dengan filter.</p>
                            <p class="mt-1 text-[11px] text-slate-500">
                                Coba ubah kata kunci atau filter status penilaian.
                            </p>
                        </div>
                        <div v-else>
                            <p class="text-[13px] font-bold text-wims-text">Belum ada mahasiswa yang siap dinilai.</p>
                            <p class="mt-1 text-[11px] text-slate-500">
                                Penilaian dosen dapat dilakukan setelah mahasiswa menyelesaikan PKL.
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
