<script setup lang="ts">
import { FileText } from 'lucide-vue-next';

type FlowStep = {
    number: number;
    title: string;
    description: string;
    tone: 'blue' | 'amber' | 'emerald';
};

const steps: FlowStep[] = [
    {
        number: 1,
        title: 'Buka Formulir',
        description: 'Akses menu "Ajukan Surat"',
        tone: 'blue',
    },
    {
        number: 2,
        title: 'Pilih Jenis Surat',
        description: 'Sesuaikan dengan keperluan',
        tone: 'blue',
    },
    {
        number: 3,
        title: 'Lengkapi Data',
        description: 'Isi identitas & unggahan syarat',
        tone: 'blue',
    },
    {
        number: 4,
        title: 'Finalisasi',
        description: 'Preview dan klaim permohonan',
        tone: 'blue',
    },
    {
        number: 5,
        title: 'Validasi',
        description: 'Verifikasi Dekan/Kaprodi',
        tone: 'amber',
    },
    {
        number: 6,
        title: 'Cetak',
        description: 'Unduh & cetak mandiri',
        tone: 'emerald',
    },
];

const toneClasses: Record<
    FlowStep['tone'],
    { badge: string; line: string; card: string; icon: string }
> = {
    blue: {
        badge: 'border-blue-400 bg-blue-400 text-white shadow-[0_6px_14px_rgba(59,130,246,0.18)]',
        line: 'bg-blue-300',
        card: 'border-blue-100/80',
        icon: 'text-blue-600',
    },
    amber: {
        badge: 'border-amber-400 bg-amber-400 text-white shadow-[0_6px_14px_rgba(245,158,11,0.18)]',
        line: 'bg-amber-300',
        card: 'border-amber-100/80',
        icon: 'text-amber-600',
    },
    emerald: {
        badge: 'border-emerald-400 bg-emerald-400 text-white shadow-[0_6px_14px_rgba(16,185,129,0.18)]',
        line: 'bg-emerald-300',
        card: 'border-emerald-100/80',
        icon: 'text-emerald-600',
    },
};
</script>

<template>
    <section class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-3 flex items-center gap-2">
            <div class="grid size-9 shrink-0 place-items-center rounded-xl border border-blue-100 bg-blue-50 text-blue-600">
                <FileText class="size-4" />
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-slate-900">Alur Pembuatan Surat</p>
                <p class="text-xs leading-5 text-slate-500">
                    Ikuti langkah berikut agar pengajuan lebih cepat dan konsisten.
                </p>
            </div>
        </div>

        <div class="space-y-2">
                <div
                    v-for="step in steps"
                    :key="step.number"
                    class="flex items-stretch gap-2.5"
                >
                    <div class="relative flex shrink-0 items-start justify-center pt-0.5">
                        <span
                            v-if="step.number !== steps.length"
                            :class="toneClasses[step.tone].line"
                            class="absolute left-1/2 top-9 h-[calc(100%+0.45rem)] w-px -translate-x-1/2 rounded-full"
                        >
                        </span>
                        <span
                            class="relative z-10 grid size-9 place-items-center rounded-full border text-xs font-semibold shadow-[0_1px_2px_rgba(15,23,42,0.03)]"
                            :class="toneClasses[step.tone].badge"
                        >
                            {{ step.number }}
                        </span>
                    </div>

                    <div
                        class="min-w-0 flex-1 rounded-2xl border bg-white p-3"
                        :class="toneClasses[step.tone].card"
                    >
                        <p class="text-xs font-semibold text-slate-900">
                            {{ step.title }}
                        </p>
                        <p class="mt-0.5 text-[10px] leading-4 text-slate-400">
                            {{ step.description }}
                        </p>
                    </div>
                </div>
        </div>
    </section>
</template>
