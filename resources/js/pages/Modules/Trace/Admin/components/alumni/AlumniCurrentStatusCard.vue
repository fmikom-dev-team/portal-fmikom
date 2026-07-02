<script setup lang="ts">
import {
    Briefcase,
    Building2,
    DollarSign,
    GraduationCap,
    Info,
    MapPin,
} from 'lucide-vue-next';
import type { CareerHistory, ProfilAlumni } from '@/types/trace';

defineProps<{
    alumni: ProfilAlumni;
    currentCareer: CareerHistory | null;
    currentEducation: CareerHistory | null;
}>();

const formatCurrency = (val?: number | null) => {
    if (!val) {
        return '-';
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val);
};

const employmentValue = (
    career: CareerHistory | null,
    field: 'nama_perusahaan' | 'jabatan' | 'alamat_perusahaan' | 'sektor_industri',
) => {
    return career?.[field] || career?.employment?.[field] || '-';
};

const educationValue = (
    career: CareerHistory | null,
    field:
        | 'nama_universitas'
        | 'program_studi_lanjutan'
        | 'jenjang_pendidikan'
        | 'sumber_biaya'
        | 'alamat_universitas',
) => {
    return career?.[field] || career?.education?.[field] || '';
};
</script>

<template>
    <div
        class="flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm md:col-span-2 lg:col-span-1 dark:border-slate-800 dark:bg-slate-900"
    >
        <div
            class="flex items-center gap-2 border-b border-slate-100 bg-slate-50/50 px-5 py-4 dark:border-slate-800 dark:bg-slate-900/50"
        >
            <Briefcase class="h-4 w-4 text-violet-500" />
            <h2 class="text-sm font-black text-slate-800 dark:text-white">
                Status & Karir Saat Ini
            </h2>
        </div>

        <div class="flex flex-1 flex-col justify-between p-5">
            <div
                v-if="
                    currentCareer &&
                    ['bekerja', 'wirausaha'].includes(currentCareer.status)
                "
                class="space-y-4"
            >
                <div class="flex items-start gap-3">
                    <div
                        class="mt-0.5 rounded-lg bg-blue-50 p-2 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                    >
                        <Building2 class="h-4 w-4" />
                    </div>
                    <div class="flex flex-col">
                        <span
                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Perusahaan
                        </span>
                        <span class="text-sm font-bold text-slate-800 dark:text-white">
                            {{ employmentValue(currentCareer, 'nama_perusahaan') }}
                        </span>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div
                        class="mt-0.5 rounded-lg bg-indigo-50 p-2 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400"
                    >
                        <Briefcase class="h-4 w-4" />
                    </div>
                    <div class="flex flex-col">
                        <span
                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Jabatan
                        </span>
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                            {{ employmentValue(currentCareer, 'jabatan') }}
                        </span>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div
                        class="mt-0.5 rounded-lg bg-violet-50 p-2 text-violet-600 dark:bg-violet-950/50 dark:text-violet-400"
                    >
                        <DollarSign class="h-4 w-4" />
                    </div>
                    <div class="flex flex-col">
                        <span
                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Rentang Gaji
                        </span>
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300">
                            {{
                                currentCareer.gaji_min || currentCareer.employment?.gaji_min
                                    ? `${formatCurrency(currentCareer.gaji_min || currentCareer.employment?.gaji_min)} - ${formatCurrency(currentCareer.gaji_max || currentCareer.employment?.gaji_max)}`
                                    : '-'
                            }}
                        </span>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div
                        class="mt-0.5 rounded-lg bg-slate-50 p-2 text-slate-600 dark:bg-slate-800 dark:text-slate-400"
                    >
                        <MapPin class="h-4 w-4" />
                    </div>
                    <div class="flex flex-col">
                        <span
                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Lokasi
                        </span>
                        <span
                            class="text-sm leading-relaxed font-semibold text-slate-700 dark:text-slate-300"
                        >
                            {{ employmentValue(currentCareer, 'alamat_perusahaan') }}
                        </span>
                    </div>
                </div>
            </div>

            <div
                v-else-if="
                    (currentCareer && currentCareer.status === 'lanjut_studi') ||
                    currentEducation
                "
                class="space-y-4"
            >
                <div class="flex items-start gap-3">
                    <div
                        class="mt-0.5 rounded-lg bg-purple-50 p-2 text-purple-600 dark:bg-purple-950/50 dark:text-purple-400"
                    >
                        <Building2 class="h-4 w-4" />
                    </div>
                    <div class="flex flex-col">
                        <span
                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Perguruan Tinggi
                        </span>
                        <span class="text-sm font-bold text-slate-800 dark:text-white">
                            {{
                                educationValue(currentCareer, 'nama_universitas') ||
                                educationValue(currentEducation, 'nama_universitas') ||
                                '-'
                            }}
                        </span>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div
                        class="mt-0.5 rounded-lg bg-fuchsia-50 p-2 text-fuchsia-600 dark:bg-fuchsia-950/50 dark:text-fuchsia-400"
                    >
                        <GraduationCap class="h-4 w-4" />
                    </div>
                    <div class="flex flex-col">
                        <span
                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Program Studi & Jenjang
                        </span>
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                            {{
                                `${educationValue(currentCareer, 'jenjang_pendidikan') || educationValue(currentEducation, 'jenjang_pendidikan') || '-'} - ${educationValue(currentCareer, 'program_studi_lanjutan') || educationValue(currentEducation, 'program_studi_lanjutan') || '-'}`
                            }}
                        </span>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div
                        class="mt-0.5 rounded-lg bg-pink-50 p-2 text-pink-600 dark:bg-pink-950/50 dark:text-pink-400"
                    >
                        <DollarSign class="h-4 w-4" />
                    </div>
                    <div class="flex flex-col">
                        <span
                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Sumber Biaya
                        </span>
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                            {{
                                educationValue(currentCareer, 'sumber_biaya') ||
                                educationValue(currentEducation, 'sumber_biaya') ||
                                '-'
                            }}
                        </span>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div
                        class="mt-0.5 rounded-lg bg-slate-50 p-2 text-slate-600 dark:bg-slate-800 dark:text-slate-400"
                    >
                        <MapPin class="h-4 w-4" />
                    </div>
                    <div class="flex flex-col">
                        <span
                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Alamat Kampus
                        </span>
                        <span
                            class="text-sm leading-relaxed font-semibold text-slate-700 dark:text-slate-300"
                        >
                            {{
                                educationValue(currentCareer, 'alamat_universitas') ||
                                educationValue(currentEducation, 'alamat_universitas') ||
                                '-'
                            }}
                        </span>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="flex flex-1 flex-col items-center justify-center py-6 text-center"
            >
                <div
                    class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-amber-50 text-amber-600 dark:bg-amber-950/30 dark:text-amber-400"
                >
                    <Info class="h-6 w-6" />
                </div>
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300">
                    Sedang Mencari Kerja
                </h3>
                <p class="mt-1 max-w-[200px] text-xs text-slate-400 dark:text-slate-500">
                    Alumni ini belum memiliki karir atau pendidikan lanjut aktif saat ini.
                </p>
            </div>
        </div>
    </div>
</template>
