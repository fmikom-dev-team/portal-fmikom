<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    AlertCircle,
    ArrowLeft,
    BriefcaseBusiness,
    CalendarDays,
    CheckCheck,
    ClipboardList,
    Download,
    Eye,
    FileText,
} from 'lucide-vue-next';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { formatIndonesianDateLabel } from '@/lib/date';
import WimsMitraLayout from '@/layouts/Modules/Wims/Mitra/Layout.vue';

defineOptions({
    layout: WimsMitraLayout,
});

type ComponentItem = {
    id: number;
    no: number;
    name: string;
    description?: string | null;
    weight_percentage: number;
    score?: number | null;
    weighted_score?: number | null;
    note?: string | null;
};

type PageProps = {
    flash?: {
        success?: string;
        error?: string;
    };
    errors?: Record<string, string | undefined>;
};

const props = defineProps<{
    student: {
        pendaftaran_id: number;
        mahasiswa_id?: number | null;
        name?: string | null;
        nim?: string | null;
        email?: string | null;
        company?: string | null;
        status_pendaftaran?: string | null;
        period_label?: string | null;
        final_report?: {
            name?: string | null;
            view_url?: string | null;
            download_url?: string | null;
            uploaded_at?: string | null;
        } | null;
    };
    template_year?: string | null;
    template?: {
        id: number;
        name: string;
        description?: string | null;
        period_label?: string | null;
        total_weight: number;
        components: ComponentItem[];
    } | null;
    submission?: {
        id: number;
        status: string;
        status_label: string;
        total_score?: number | null;
        notes?: string | null;
        submitted_at?: string | null;
    } | null;
}>();

const page = usePage<PageProps>();
const isReadonly = computed(() => props.submission?.status === 'submitted');
const flash = computed(() => page.props.flash ?? {});
const returnSource = computed(() => {
    if (typeof window === 'undefined') {
        return null;
    }

    return new URLSearchParams(window.location.search).get('from');
});
const backLabel = computed(() => 'Kembali ke Dashboard');

const form = useForm({
    scores: (props.template?.components ?? []).map((component) => ({
        component_id: component.id,
        score: component.score ?? null,
        note: component.note ?? '',
    })),
    notes: props.submission?.notes ?? '',
    action: 'draft',
});

const totalWeightedScore = computed(() =>
    form.scores.reduce((total, item) => {
        const component = props.template?.components.find((entry) => entry.id === item.component_id);
        const scoreValue = Number(item.score ?? 0);
        const weight = Number(component?.weight_percentage ?? 0);

        return total + (Number.isFinite(scoreValue) ? scoreValue * (weight / 100) : 0);
    }, 0),
);

const weightedScore = (component: ComponentItem) => {
    const item = form.scores.find((entry) => entry.component_id === component.id);
    const scoreValue = Number(item?.score ?? 0);

    if (!Number.isFinite(scoreValue)) {
        return 0;
    }

    return scoreValue * (component.weight_percentage / 100);
};

const submitForm = (action: 'draft' | 'submit') => {
    if (!props.template || isReadonly.value) {
        return;
    }

    form.action = action;
    form.post(`/wims/mitra/penilaian-mahasiswa/${props.student.pendaftaran_id}`, {
        preserveScroll: true,
    });
};

const goBack = () => {
    if (returnSource.value === 'monitoring' && props.student.mahasiswa_id) {
        router.visit(`/wims/mitra/monitoring/${props.student.mahasiswa_id}`);
        return;
    }

    if (returnSource.value === 'dashboard') {
        router.visit('/wims/mitra/dashboard');
        return;
    }

    router.visit('/wims/mitra/penilaian-mahasiswa');
};

const openMonitoring = () => {
    if (!props.student.mahasiswa_id) {
        return;
    }

    router.visit(`/wims/mitra/monitoring/${props.student.mahasiswa_id}?from=assessment`);
};

const statusClass = computed(() => {
    if (props.submission?.status === 'submitted') {
        return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    }

    if (props.submission?.status === 'draft') {
        return 'border-amber-200 bg-amber-50 text-amber-700';
    }

    return 'border-slate-200 bg-slate-50 text-slate-600';
});

const statusSurfaceClass = computed(() => {
    if (props.submission?.status === 'submitted') {
        return 'border-emerald-100 bg-emerald-50/70';
    }

    if (props.submission?.status === 'draft') {
        return 'border-amber-100 bg-amber-50/70';
    }

    return 'border-slate-200 bg-slate-50/80';
});

const readonlyAlertClass = computed(() => {
    if (props.submission?.status === 'submitted') {
        return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    }

    return 'border-amber-200 bg-amber-50 text-amber-700';
});

const readonlyAlertTitle = computed(() =>
    props.submission?.status === 'submitted' ? 'Nilai Sudah Dikirim' : 'Penilaian Masih Draft',
);

const readonlyAlertText = computed(() =>
    props.submission?.status === 'submitted'
        ? 'Nilai mitra sudah dikirim dan tidak dapat diubah.'
        : 'Penilaian masih tersimpan sebagai draft dan masih dapat diperbarui.',
);

const formatScore = (value?: number | null) => Number(value ?? 0).toFixed(2);

const componentScoreAt = (index: number) => form.scores[index]?.score;

</script>

<template>
    <Head title="Penilaian Mahasiswa" />

    <div class="min-h-screen bg-wims-bg">
        <div class="mx-auto flex w-full max-w-[1320px] flex-col gap-4 px-4 py-3 pb-7 lg:gap-5 sm:px-6 sm:py-6 sm:pb-10 lg:px-8 lg:py-8 xl:px-10">
            <section class="relative overflow-hidden rounded-2xl border border-wims-border/50 bg-wims-card/95 px-5 py-5 shadow-[0_1px_3px_rgba(0,0,0,0.04)] sm:px-6 sm:py-6">
                <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-start">
                    <div class="min-w-0">
                        <h1 class="text-[17px] font-bold tracking-tight text-wims-text sm:text-[20px]">
                            Form Penilaian Mahasiswa
                        </h1>
                        <div class="mt-4 max-w-3xl rounded-xl border border-wims-border bg-slate-50/80 px-4 py-4 sm:rounded-2xl">
                            <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-slate-400">
                                Mahasiswa
                            </p>
                            <p class="mt-2 break-words text-base font-bold text-wims-text">
                                {{ props.student.name || 'Mahasiswa' }}
                            </p>
                            <p class="mt-1 break-words text-xs text-slate-500">
                                {{ props.student.nim || '-' }} • {{ props.student.email || '-' }}
                            </p>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="min-w-0 rounded-xl border border-wims-border bg-white px-3.5 py-3">
                                    <div class="flex items-center gap-2 text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">
                                        <BriefcaseBusiness class="size-3.5" />
                                        Perusahaan
                                    </div>
                                    <p class="mt-1.5 break-words text-sm font-bold text-wims-text">
                                        {{ props.student.company || '-' }}
                                    </p>
                                </div>
                                <div class="min-w-0 rounded-xl border border-wims-border bg-white px-3.5 py-3">
                                    <div class="flex items-center gap-2 text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">
                                        <CalendarDays class="size-3.5" />
                                        Periode PKL
                                    </div>
                                    <p class="mt-1.5 break-words text-sm font-bold text-wims-text">
                                        {{ formatIndonesianDateLabel(props.student.period_label) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-start xl:justify-end">
                        <button
                            type="button"
                            class="inline-flex h-9 w-fit items-center gap-1.5 rounded-lg border border-wims-border bg-wims-card px-3 text-[11px] font-bold text-slate-700 transition duration-200 hover:border-slate-300 hover:bg-slate-50 sm:px-3.5 sm:text-xs"
                            @click="goBack"
                        >
                            <ArrowLeft class="size-3.5" />
                            {{ backLabel }}
                        </button>
                    </div>
                </div>
            </section>

            <Alert v-if="flash.success" class="border-emerald-200 bg-emerald-50 text-emerald-700">
                <AlertTitle>Berhasil</AlertTitle>
                <AlertDescription>{{ flash.success }}</AlertDescription>
            </Alert>

            <Alert v-if="flash.error" class="border-rose-200 bg-rose-50 text-rose-700">
                <AlertTitle>Penilaian Tidak Dapat Diubah</AlertTitle>
                <AlertDescription>{{ flash.error }}</AlertDescription>
            </Alert>

            <Card
                v-if="!props.template"
                class="rounded-2xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)]"
            >
                <CardContent class="flex flex-col items-center gap-4 px-6 py-12 text-center">
                    <div class="flex size-14 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                        <AlertCircle class="size-6" />
                    </div>
                    <div>
                        <p class="text-[15px] font-bold text-wims-text">
                            Template penilaian tahun {{ props.template_year || 'PKL ini' }} belum tersedia.
                        </p>
                        <p class="mt-2 text-[13px] leading-6 text-slate-600 sm:text-sm">
                            Admin perlu mengaktifkan template penilaian untuk tahun PKL mahasiswa ini.
                        </p>
                    </div>
                </CardContent>
            </Card>

            <template v-else>
                <Card class="rounded-2xl border border-wims-border bg-wims-card py-0 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.18)]">
                    <CardHeader class="px-5 pt-5 pb-4 sm:px-6 sm:pt-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0">
                                <CardTitle class="text-base font-bold text-wims-text">
                                    {{ props.template.period_label ? formatIndonesianDateLabel(props.template.period_label) : `Periode ${props.template_year || '-'}` }}
                                </CardTitle>
                                <CardDescription class="mt-1 text-[13px] leading-6 text-slate-600 sm:text-sm">
                                    {{ props.template.description || `Berlaku untuk mahasiswa PKL tahun ${props.template_year || '-'}.` }}
                                </CardDescription>
                            </div>
                            <Badge
                                variant="outline"
                                class="w-fit rounded-full px-3 py-1 text-[11px] font-bold"
                                :class="statusClass"
                            >
                                {{ props.submission?.status_label || 'Belum Dinilai' }}
                            </Badge>
                        </div>
                    </CardHeader>

                    <CardContent class="space-y-4 px-5 pb-5 sm:space-y-5 sm:px-6 sm:pb-6">
                        <div class="grid grid-cols-2 gap-2.5 sm:gap-3 xl:grid-cols-4">
                            <div class="rounded-xl border border-wims-border bg-slate-50/80 px-4 py-3">
                                <div class="flex items-center gap-2 text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">
                                    <ClipboardList class="size-3.5" />
                                    Template
                                </div>
                                <p class="mt-1.5 break-words text-sm font-bold text-wims-text">
                                    {{ props.template.name }}
                                </p>
                            </div>
                            <div class="rounded-xl border border-wims-border bg-slate-50/80 px-4 py-3">
                                <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">
                                    Tahun
                                </p>
                                <p class="mt-1.5 text-sm font-bold text-wims-text">
                                    {{ props.template_year || '-' }}
                                </p>
                            </div>
                            <div class="rounded-xl border border-wims-border bg-slate-50/80 px-4 py-3">
                                <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">
                                    Bobot Total
                                </p>
                                <p class="mt-1.5 text-sm font-bold text-wims-text">
                                    {{ props.template.total_weight }}%
                                </p>
                            </div>
                            <div class="rounded-xl border px-4 py-3" :class="statusSurfaceClass">
                                <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">
                                    Status Penilaian
                                </p>
                                <div class="mt-1.5">
                                    <Badge
                                        variant="outline"
                                        class="rounded-full px-3 py-1 text-[11px] font-bold"
                                        :class="statusClass"
                                    >
                                        {{ props.submission?.status_label || 'Belum Dinilai' }}
                                    </Badge>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-wims-border bg-slate-50/80 px-4 py-3">
                            <div class="flex items-center gap-2 text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">
                                <CalendarDays class="size-3.5" />
                                Masa Berlaku
                            </div>
                            <p class="mt-1.5 text-sm font-bold text-wims-text">
                                {{ formatIndonesianDateLabel(props.template.period_label) }}
                            </p>
                        </div>

                        <section class="rounded-2xl border border-wims-border bg-slate-50/80 px-4 py-4 sm:px-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 text-sm font-bold text-wims-text">
                                        <FileText class="size-4 text-slate-500" />
                                        Laporan Akhir Mahasiswa
                                    </div>
                                    <p class="mt-1 text-xs leading-5 text-slate-500">
                                        Tinjau dokumen laporan akhir sebelum memberi penilaian mitra.
                                    </p>
                                    <div
                                        class="mt-3 rounded-xl border px-3.5 py-3"
                                        :class="props.student.final_report ? 'border-emerald-200 bg-emerald-50/70' : 'border-slate-200 bg-white'"
                                    >
                                        <p class="text-sm font-bold text-wims-text">
                                            {{ props.student.final_report?.name || 'Belum diunggah' }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{
                                                props.student.final_report?.uploaded_at
                                                    ? `Diunggah pada ${formatIndonesianDateLabel(props.student.final_report.uploaded_at)}`
                                                    : 'Mahasiswa belum mengunggah laporan akhir.'
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-2 sm:flex-row">
                                    <a
                                        v-if="props.student.final_report?.view_url"
                                        :href="props.student.final_report.view_url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-wims-border bg-white px-4 text-sm font-bold text-slate-700 transition duration-200 hover:border-slate-300 hover:bg-slate-50"
                                    >
                                        <Eye class="size-4" />
                                        Lihat Dokumen
                                    </a>
                                    <a
                                        v-if="props.student.final_report?.download_url"
                                        :href="props.student.final_report.download_url"
                                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-[#0F62FE] px-4 text-sm font-bold text-white transition duration-200 hover:bg-[#0050E6]"
                                    >
                                        <Download class="size-4" />
                                        Unduh
                                    </a>
                                </div>
                            </div>
                        </section>

                        <div
                            v-if="isReadonly || props.submission?.status === 'draft'"
                            class="rounded-xl border px-4 py-3 text-sm"
                            :class="readonlyAlertClass"
                        >
                            <p class="font-bold">{{ readonlyAlertTitle }}</p>
                            <p class="mt-1 leading-6">{{ readonlyAlertText }}</p>
                        </div>

                        <section class="overflow-hidden rounded-2xl border border-wims-border">
                            <div class="border-b border-wims-border bg-slate-50/80 px-4 py-3 sm:px-5">
                                <p class="text-sm font-bold text-wims-text">Komponen Nilai</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    Nilai terbobot dihitung otomatis sesuai bobot setiap komponen.
                                </p>
                            </div>

                            <div class="hidden overflow-x-auto md:block">
                                <table class="min-w-full border-collapse">
                                    <thead class="bg-slate-50/70">
                                        <tr class="border-b border-wims-border">
                                            <th class="px-5 py-3 text-left text-[12px] font-bold uppercase tracking-[0.06em] text-slate-500">No</th>
                                            <th class="px-5 py-3 text-left text-[12px] font-bold uppercase tracking-[0.06em] text-slate-500">Komponen</th>
                                            <th class="px-5 py-3 text-left text-[12px] font-bold uppercase tracking-[0.06em] text-slate-500">Bobot</th>
                                            <th class="px-5 py-3 text-left text-[12px] font-bold uppercase tracking-[0.06em] text-slate-500">Nilai</th>
                                            <th class="px-5 py-3 text-right text-[12px] font-bold uppercase tracking-[0.06em] text-slate-500">Nilai Terbobot</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(component, index) in props.template.components"
                                            :key="component.id"
                                            class="border-b border-wims-border bg-white align-top last:border-b-0"
                                        >
                                            <td class="px-5 py-3.5 text-sm text-wims-text">{{ component.no }}</td>
                                            <td class="px-5 py-3.5">
                                                <p class="text-sm font-bold text-wims-text">{{ component.name }}</p>
                                                <p v-if="component.description" class="mt-1 break-words text-xs leading-5 text-slate-500">
                                                    {{ component.description }}
                                                </p>
                                            </td>
                                            <td class="px-5 py-3.5 text-sm font-medium text-wims-text">
                                                {{ component.weight_percentage }}%
                                            </td>
                                            <td class="px-5 py-3.5">
                                                <div v-if="isReadonly" class="text-sm font-bold text-wims-text">
                                                    {{ formatScore(componentScoreAt(index)) }}
                                                </div>
                                                <input
                                                    v-else
                                                    v-model.number="form.scores[index].score"
                                                    type="number"
                                                    min="0"
                                                    max="100"
                                                    step="0.01"
                                                    class="h-9 w-24 rounded-lg border border-wims-border bg-white px-3 text-[13px] text-wims-text outline-none transition duration-200 hover:border-slate-300 focus:border-[#0F62FE] focus:ring-2 focus:ring-[#0F62FE]/10 disabled:bg-slate-50 sm:h-10 sm:text-sm"
                                                    placeholder="0-100"
                                                    :disabled="form.processing"
                                                />
                                            </td>
                                            <td class="px-5 py-3.5 text-right text-sm font-bold text-emerald-700">
                                                {{ formatScore(weightedScore(component)) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="space-y-3 p-4 md:hidden">
                                <div
                                    v-for="(component, index) in props.template.components"
                                    :key="`mobile-${component.id}`"
                                    class="rounded-xl border border-wims-border bg-white px-4 py-3.5"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs text-slate-400">{{ component.no }}.</p>
                                            <p class="mt-1 text-sm font-bold text-wims-text">{{ component.name }}</p>
                                            <p v-if="component.description" class="mt-1 break-words text-xs leading-5 text-slate-500">
                                                {{ component.description }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Bobot</p>
                                            <p class="mt-1 text-sm font-bold text-wims-text">{{ component.weight_percentage }}%</p>
                                        </div>
                                    </div>
                                    <div class="mt-3 grid grid-cols-2 gap-3">
                                        <div>
                                            <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Nilai</p>
                                            <div v-if="isReadonly" class="mt-1 text-sm font-bold text-wims-text">
                                                {{ formatScore(componentScoreAt(index)) }}
                                            </div>
                                            <input
                                                v-else
                                                v-model.number="form.scores[index].score"
                                                type="number"
                                                min="0"
                                                max="100"
                                                step="0.01"
                                                class="mt-1 h-9 w-full rounded-lg border border-wims-border bg-white px-3 text-[13px] text-wims-text outline-none transition duration-200 hover:border-slate-300 focus:border-[#0F62FE] focus:ring-2 focus:ring-[#0F62FE]/10 disabled:bg-slate-50 sm:h-10 sm:text-sm"
                                                placeholder="0-100"
                                                :disabled="form.processing"
                                            />
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate-400">Nilai Terbobot</p>
                                            <p class="mt-1 text-sm font-bold text-emerald-700">
                                                {{ formatScore(weightedScore(component)) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-wims-border bg-slate-50/80 px-4 py-4">
                            <div class="flex items-center gap-2 text-sm font-bold text-wims-text">
                                <FileText class="size-4 text-slate-500" />
                                Catatan Umum
                            </div>
                            <textarea
                                v-if="!isReadonly"
                                v-model="form.notes"
                                rows="3"
                                class="mt-3 w-full rounded-xl border border-wims-border bg-white px-3 py-2.5 text-sm text-wims-text outline-none transition duration-200 hover:border-slate-300 focus:border-[#0F62FE] focus:ring-2 focus:ring-[#0F62FE]/10 disabled:bg-slate-50"
                                placeholder="Catatan umum untuk penilaian mitra."
                                :disabled="form.processing"
                            />
                            <div
                                v-else
                                class="mt-3 rounded-xl border border-wims-border bg-white px-3.5 py-3 text-sm leading-6"
                                :class="form.notes?.trim() ? 'text-wims-text' : 'text-slate-500'"
                            >
                                {{ form.notes?.trim() ? form.notes : 'Tidak ada catatan umum.' }}
                            </div>
                        </section>

                        <section class="rounded-2xl border border-wims-border bg-slate-50/80 px-4 py-4 sm:px-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div class="space-y-2">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-slate-400">
                                        Total Nilai Mitra
                                    </p>
                                    <div class="inline-flex w-fit items-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-base font-bold text-emerald-700 sm:text-lg">
                                        {{ formatScore(totalWeightedScore) }} / 100
                                    </div>
                                    <p v-if="props.submission?.submitted_at" class="text-xs text-slate-500">
                                        Dikirim pada {{ formatIndonesianDateLabel(props.submission?.submitted_at) }}
                                    </p>
                                </div>

                                <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:justify-end">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="h-9 rounded-lg border-wims-border bg-wims-card px-3.5 text-[13px] font-bold text-slate-700 transition duration-200 hover:bg-slate-50 sm:h-10 sm:px-4 sm:text-sm"
                                        @click="openMonitoring"
                                    >
                                        Lihat Monitoring
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="h-9 rounded-lg border-wims-border bg-wims-card px-3.5 text-[13px] font-bold text-slate-700 transition duration-200 hover:bg-slate-50 sm:h-10 sm:px-4 sm:text-sm"
                                        @click="goBack"
                                    >
                                        Kembali
                                    </Button>
                                    <Button
                                        v-if="!isReadonly"
                                        type="button"
                                        variant="outline"
                                        class="h-9 rounded-lg border-wims-border bg-white px-3.5 text-[13px] font-bold text-wims-text transition duration-200 hover:border-slate-300 hover:bg-slate-50 sm:h-10 sm:px-4 sm:text-sm"
                                        :disabled="form.processing"
                                        @click="submitForm('draft')"
                                    >
                                        Simpan Draft
                                    </Button>
                                    <Button
                                        v-if="!isReadonly"
                                        type="button"
                                        class="h-9 rounded-lg bg-[#0F62FE] px-3.5 text-[13px] font-bold text-white transition duration-200 hover:bg-[#0050E6] disabled:bg-slate-300 sm:h-10 sm:px-4 sm:text-sm"
                                        :disabled="form.processing"
                                        @click="submitForm('submit')"
                                    >
                                        Kirim Nilai
                                    </Button>
                                </div>
                            </div>
                        </section>
                    </CardContent>
                </Card>
            </template>
        </div>
    </div>
</template>


