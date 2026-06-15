<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    ArrowLeft,
    CheckCheck,
    ClipboardCheck,
    FilePenLine,
    Search,
    Users,
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
    company?: string | null;
    period: {
        start?: string | null;
        end?: string | null;
        label?: string | null;
    };
    registration_status?: string | null;
    assessment: {
        status_key: 'not_assessed' | 'draft' | 'submitted';
        status: string;
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
                item.company,
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

const summaryCards = computed(() => [
    {
        label: 'Total Mahasiswa',
        value: props.summary.total_students,
        tone: 'text-slate-900',
        cardClass: 'border-slate-200 bg-slate-50/70',
        iconClass: 'border-slate-200 bg-white text-slate-600',
        icon: Users,
        description: 'Mahasiswa bimbingan yang sudah masuk tahap penilaian.',
    },
    {
        label: 'Belum Dinilai',
        value: props.summary.not_assessed,
        tone: 'text-slate-700',
        cardClass: 'border-amber-100/80 bg-amber-50/45',
        iconClass: 'border-amber-100 bg-white text-amber-600',
        icon: ClipboardCheck,
        description: 'Mahasiswa selesai yang belum memiliki nilai dosen.',
    },
    {
        label: 'Draft',
        value: props.summary.draft,
        tone: 'text-amber-700',
        cardClass: 'border-orange-100/80 bg-orange-50/45',
        iconClass: 'border-orange-100 bg-white text-orange-600',
        icon: FilePenLine,
        description: 'Penilaian dosen yang sudah disimpan tetapi belum dikirim.',
    },
    {
        label: 'Sudah Dikirim',
        value: props.summary.submitted,
        tone: 'text-emerald-700',
        cardClass: 'border-emerald-100/80 bg-emerald-50/45',
        iconClass: 'border-emerald-100 bg-white text-emerald-600',
        icon: CheckCheck,
        description: 'Nilai dosen yang sudah final dan tidak dapat diubah.',
    },
]);

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
                <div class="flex flex-col gap-2.5 sm:gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-3xl">
                        <h1 class="text-[17px] font-bold tracking-tight text-wims-text sm:text-[20px]">
                            Ringkasan Penilaian Dosen
                        </h1>
                        <p class="mt-1.5 max-w-3xl text-[12px] leading-5 text-slate-600 sm:text-sm sm:leading-6">
                            Kelola nilai dosen untuk mahasiswa bimbingan berdasarkan template penilaian.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-9 w-fit self-start items-center gap-1.5 rounded-lg border border-wims-border bg-wims-card px-3 text-[11px] font-bold text-slate-700 transition duration-200 hover:border-slate-300 hover:bg-slate-50 sm:px-3.5 sm:text-xs lg:self-auto"
                        @click="goBack"
                    >
                        <ArrowLeft class="size-3.5" />
                        Kembali ke Dashboard
                    </button>
                </div>
            </header>

            <section class="grid grid-cols-2 gap-3 lg:gap-4 xl:grid-cols-4">
                <Card
                    v-for="card in summaryCards"
                    :key="card.label"
                    class="rounded-2xl py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)] transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_22px_42px_-28px_rgba(15,23,42,0.24)]"
                    :class="card.cardClass"
                >
                    <CardContent class="px-4 py-4 sm:px-5 sm:py-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-500 sm:text-xs">{{ card.label }}</p>
                                <p class="mt-1.5 text-[24px] font-bold tracking-tight sm:text-3xl" :class="card.tone">
                                    {{ card.value }}
                                </p>
                            </div>
                            <div class="flex size-10 items-center justify-center rounded-xl border" :class="card.iconClass">
                                <component :is="card.icon" class="size-4" />
                            </div>
                        </div>
                        <p class="mt-2 text-[11px] leading-5 text-slate-500 sm:text-xs">
                            {{ card.description }}
                        </p>
                    </CardContent>
                </Card>
            </section>

            <Card class="rounded-2xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)] transition duration-200 hover:shadow-[0_22px_42px_-32px_rgba(15,23,42,0.2)]">
                <CardHeader class="px-5 pt-5 pb-4 sm:px-6 sm:pt-5">
                    <div class="flex flex-col gap-3 sm:gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <CardTitle class="text-base font-bold text-wims-text">
                                Daftar Mahasiswa
                            </CardTitle>
                            <CardDescription class="mt-1 text-sm text-slate-600">
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
                                    class="h-9 w-full rounded-lg border border-wims-border bg-white pr-3 pl-9 text-[13px] text-wims-text outline-none transition duration-200 hover:border-slate-300 focus:border-[#0F62FE] focus:ring-2 focus:ring-[#0F62FE]/10 sm:h-10 sm:text-sm"
                                />
                            </div>
                            <select
                                v-model="status"
                                class="h-9 rounded-lg border border-wims-border bg-white px-3 text-[13px] text-wims-text outline-none transition duration-200 hover:border-slate-300 focus:border-[#0F62FE] focus:ring-2 focus:ring-[#0F62FE]/10 sm:h-10 sm:text-sm"
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
                                    <th class="px-5 py-3 text-left text-[12px] font-bold uppercase tracking-[0.06em] text-slate-500">Mahasiswa</th>
                                    <th class="px-5 py-3 text-left text-[12px] font-bold uppercase tracking-[0.06em] text-slate-500">Perusahaan</th>
                                    <th class="px-5 py-3 text-left text-[12px] font-bold uppercase tracking-[0.06em] text-slate-500">Periode</th>
                                    <th class="px-5 py-3 text-left text-[12px] font-bold uppercase tracking-[0.06em] text-slate-500">Status Magang</th>
                                    <th class="px-5 py-3 text-left text-[12px] font-bold uppercase tracking-[0.06em] text-slate-500">Status Penilaian</th>
                                    <th class="px-5 py-3 text-right text-[12px] font-bold uppercase tracking-[0.06em] text-slate-500">Nilai Dosen</th>
                                    <th class="px-5 py-3 text-right text-[12px] font-bold uppercase tracking-[0.06em] text-slate-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="item in filteredStudents"
                                    :key="item.id"
                                    class="border-t border-wims-border bg-white align-top transition duration-200 hover:bg-blue-50/40"
                                >
                                    <td class="px-5 py-4">
                                        <p class="text-sm font-bold text-wims-text">{{ item.student.name || 'Mahasiswa' }}</p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ item.student.nim || '-' }} &bull; {{ item.student.email || '-' }}
                                        </p>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-wims-text">
                                        {{ item.company || '-' }}
                                    </td>
                                    <td class="px-5 py-4 text-sm text-wims-text">
                                        {{ formatIndonesianDateLabel(item.period.label) }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <Badge
                                            variant="outline"
                                            class="rounded-full px-3 py-1 text-[11px] font-bold"
                                            :class="registrationStatusClass(item.registration_status)"
                                        >
                                            {{ registrationStatusLabel(item.registration_status) }}
                                        </Badge>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-col gap-2">
                                            <Badge
                                                variant="outline"
                                                class="w-fit rounded-full px-3 py-1 text-[11px] font-bold"
                                                :class="statusLabelClass(item.assessment.status_key)"
                                            >
                                                {{ item.assessment.status }}
                                            </Badge>
                                            <p v-if="item.assessment.submitted_at" class="text-xs text-slate-500">
                                                Dikirim {{ formatIndonesianDateLabel(item.assessment.submitted_at) }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-right text-sm font-bold text-wims-text">
                                        {{ item.assessment.total_score !== null && item.assessment.total_score !== undefined ? item.assessment.total_score.toFixed(2) : '-' }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex justify-end">
                                            <Button
                                                type="button"
                                                class="h-9 rounded-lg bg-[#0F62FE] px-3.5 text-sm font-bold text-white shadow-sm transition duration-200 hover:bg-[#0050E6] hover:shadow-md"
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

                    <div v-if="filteredStudents.length" class="space-y-3 px-4 pb-4 md:hidden">
                        <div
                            v-for="item in filteredStudents"
                            :key="`mobile-${item.id}`"
                            class="rounded-2xl border border-wims-border bg-white px-4 py-4 shadow-[0_12px_28px_-28px_rgba(15,23,42,0.3)]"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-wims-text">{{ item.student.name || 'Mahasiswa' }}</p>
                                    <p class="mt-1 break-words text-xs leading-5 text-slate-500">
                                        {{ item.student.nim || '-' }} &bull; {{ item.student.email || '-' }}
                                    </p>
                                </div>
                                <Badge
                                    variant="outline"
                                    class="rounded-full px-3 py-1 text-[11px] font-bold"
                                    :class="statusLabelClass(item.assessment.status_key)"
                                >
                                    {{ item.assessment.status }}
                                </Badge>
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-xl border border-wims-border bg-slate-50/80 px-3.5 py-3">
                                    <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Perusahaan</p>
                                    <p class="mt-1.5 text-sm font-bold text-wims-text">{{ item.company || '-' }}</p>
                                </div>
                                <div class="rounded-xl border border-wims-border bg-slate-50/80 px-3.5 py-3">
                                    <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Periode</p>
                                    <p class="mt-1.5 text-sm font-bold text-wims-text">
                                        {{ formatIndonesianDateLabel(item.period.label) }}
                                    </p>
                                </div>
                                <div class="rounded-xl border border-wims-border bg-slate-50/80 px-3.5 py-3">
                                    <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Status Magang</p>
                                    <div class="mt-1.5">
                                        <Badge
                                            variant="outline"
                                            class="rounded-full px-3 py-1 text-[11px] font-bold"
                                            :class="registrationStatusClass(item.registration_status)"
                                        >
                                            {{ registrationStatusLabel(item.registration_status) }}
                                        </Badge>
                                    </div>
                                </div>
                                <div class="rounded-xl border border-wims-border bg-slate-50/80 px-3.5 py-3">
                                    <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Nilai Dosen</p>
                                    <p class="mt-1.5 text-sm font-bold text-wims-text">
                                        {{ item.assessment.total_score !== null && item.assessment.total_score !== undefined ? item.assessment.total_score.toFixed(2) : '-' }}
                                    </p>
                                    <p v-if="item.assessment.submitted_at" class="mt-1 text-xs text-slate-500">
                                        Dikirim {{ formatIndonesianDateLabel(item.assessment.submitted_at) }}
                                    </p>
                                </div>
                            </div>

                            <Button
                                type="button"
                                class="mt-4 h-10 w-full rounded-lg bg-[#0F62FE] px-4 text-sm font-bold text-white shadow-sm transition duration-200 hover:bg-[#0050E6]"
                                @click="openAssessment(item.id)"
                            >
                                <FilePenLine class="mr-2 size-4" />
                                {{ actionLabel(item) }}
                            </Button>
                        </div>
                    </div>

                    <div v-else class="flex flex-col items-center justify-center gap-3 px-6 py-10 text-center">
                        <div class="flex size-12 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                            <ClipboardCheck class="size-5" />
                        </div>
                        <div v-if="hasStudents">
                            <p class="text-sm font-bold text-wims-text">Tidak ada mahasiswa yang sesuai dengan filter.</p>
                            <p class="mt-1 text-sm text-slate-500">
                                Coba ubah kata kunci atau filter status penilaian.
                            </p>
                        </div>
                        <div v-else>
                            <p class="text-sm font-bold text-wims-text">Belum ada mahasiswa yang siap dinilai.</p>
                            <p class="mt-1 text-sm text-slate-500">
                                Penilaian dosen dapat dilakukan setelah mahasiswa menyelesaikan PKL.
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>


