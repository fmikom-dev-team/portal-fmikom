<script setup lang="ts">
import { Building2 } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import type { CareerHistory } from '@/types/trace';

defineProps<{
    careers: CareerHistory[];
}>();
</script>

<template>
    <div
        class="flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
    >
        <div
            class="flex items-center gap-2 border-b border-slate-100 bg-slate-50/50 px-5 py-4 dark:border-slate-800 dark:bg-slate-900/50"
        >
            <Building2 class="h-4 w-4 text-blue-500" />
            <h2
                class="text-sm font-black text-slate-800 dark:text-white"
            >
                Riwayat Karir
            </h2>
            <Badge
                variant="secondary"
                class="ml-auto border-none bg-blue-50 px-2.5 py-0.5 text-[10px] font-bold text-blue-700 dark:bg-blue-900/20 dark:text-blue-400"
            >
                {{ careers.length }}
                entri
            </Badge>
        </div>

        <div class="flex-1 p-6">
            <div v-if="careers.length > 0" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div
                    v-for="career in careers"
                    :key="career.id"
                    class="relative flex flex-col justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50/40 p-4 transition-all duration-200 hover:border-blue-200 hover:bg-slate-50/80 dark:border-slate-800 dark:bg-slate-900/30 dark:hover:border-blue-900/50 dark:hover:bg-slate-900/60"
                    :class="{
                        'border-blue-500/30 bg-blue-50/10 shadow-sm shadow-blue-500/5 dark:border-blue-500/20 dark:bg-blue-950/10':
                            career.is_current,
                    }"
                >
                    <div class="space-y-1.5">
                        <div
                            class="flex items-start justify-between gap-2"
                        >
                            <span
                                class="text-xs leading-tight font-bold text-slate-800 dark:text-white"
                            >
                                {{
                                    (career.nama_perusahaan || career.employment?.nama_perusahaan) &&
                                    (career.nama_perusahaan || career.employment?.nama_perusahaan) !== '-'
                                        ? career.nama_perusahaan || career.employment?.nama_perusahaan
                                        : 'Instansi Tidak Disebutkan'
                                }}
                            </span>
                            <span
                                v-if="career.is_current"
                                class="flex shrink-0 items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-[9px] font-black tracking-wider text-blue-700 uppercase dark:bg-blue-900/30 dark:text-blue-300"
                            >
                                <span
                                    class="h-1.5 w-1.5 animate-pulse rounded-full bg-blue-600 dark:bg-blue-400"
                                ></span>
                                Saat ini
                            </span>
                        </div>
                        <div
                            class="text-[11px] font-medium text-slate-500 dark:text-slate-400"
                        >
                                {{
                                    (career.jabatan || career.employment?.jabatan) &&
                                    (career.jabatan || career.employment?.jabatan) !== '-'
                                    ? career.jabatan || career.employment?.jabatan
                                    : 'Staf / Pegawai'
                            }}
                        </div>
                        <div
                            v-if="
                                (career.sektor_industri || career.employment?.sektor_industri) &&
                                (career.sektor_industri || career.employment?.sektor_industri) !== '-'
                            "
                            class="text-[10px] text-slate-400"
                        >
                            Sektor: {{ career.sektor_industri || career.employment?.sektor_industri }}
                        </div>
                    </div>
                    <div
                        class="flex items-center justify-between border-t border-slate-100 pt-2 text-[9px] font-bold tracking-wider text-slate-400 uppercase dark:border-slate-800/80"
                    >
                        <span
                            >Status:
                            {{
                                career.status?.replace(
                                    /_/g,
                                    ' ',
                                )
                            }}</span
                        >
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-else
                class="flex flex-col items-center justify-center py-12 text-center text-slate-400"
            >
                <Building2
                    class="mb-3 h-10 w-10 text-slate-300 dark:text-slate-700"
                />
                <p class="text-sm font-bold">
                    Belum ada riwayat karir
                </p>
                <p class="mt-1 text-xs text-slate-400">
                    Data riwayat karir alumni belum diisi atau
                    kosong.
                </p>
            </div>
        </div>
    </div>
</template>
