<script setup lang="ts">
import { GraduationCap } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import type { CareerHistory } from '@/types/trace';

defineProps<{
    educationHistory: CareerHistory[];
}>();
</script>

<template>
    <div
        class="flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
    >
        <div
            class="flex items-center gap-2 border-b border-slate-100 bg-slate-50/50 px-5 py-4 dark:border-slate-800 dark:bg-slate-900/50"
        >
            <GraduationCap class="h-4 w-4 text-purple-500" />
            <h2
                class="text-sm font-black text-slate-800 dark:text-white"
            >
                Riwayat Pendidikan Lanjut
            </h2>
            <Badge
                variant="secondary"
                class="ml-auto border-none bg-purple-50 px-2.5 py-0.5 text-[10px] font-bold text-purple-700 dark:bg-purple-900/20 dark:text-purple-400"
            >
                {{ educationHistory.length }}
                entri
            </Badge>
        </div>

        <div class="flex-1 p-6">
            <div
                v-if="
                    educationHistory.length > 0
                "
                class="grid grid-cols-1 gap-4 sm:grid-cols-2"
            >
                <div
                    v-for="edu in educationHistory"
                    :key="edu.id"
                    class="relative flex flex-col justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50/40 p-4 transition-all duration-200 hover:border-purple-200 hover:bg-slate-50/80 dark:border-slate-800 dark:bg-slate-900/30 dark:hover:border-purple-900/50 dark:hover:bg-slate-900/60"
                    :class="{
                        'border-purple-500/30 bg-purple-50/10 shadow-sm shadow-purple-500/5 dark:border-purple-500/20 dark:bg-purple-950/10':
                            edu.is_current,
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
                                    edu.nama_universitas ||
                                    edu.education?.nama_universitas ||
                                    'Universitas tidak disebutkan'
                                }}
                            </span>
                            <span
                                v-if="edu.is_current"
                                class="flex shrink-0 items-center gap-1 rounded-full bg-purple-100 px-2 py-0.5 text-[9px] font-black tracking-wider text-purple-700 uppercase dark:bg-purple-900/30 dark:text-purple-300"
                            >
                                <span
                                    class="h-1.5 w-1.5 animate-pulse rounded-full bg-purple-600 dark:bg-purple-400"
                                ></span>
                                Saat ini
                            </span>
                        </div>
                        <div
                            class="text-[11px] font-medium text-slate-500 dark:text-slate-400"
                        >
                            {{
                                edu.program_studi_lanjutan ||
                                edu.education?.program_studi_lanjutan ||
                                '-'
                            }}
                            <span
                                v-if="
                                    edu.jenjang_pendidikan ||
                                    edu.education?.jenjang_pendidikan
                                "
                                class="text-slate-400"
                                >({{
                                    edu.jenjang_pendidikan ||
                                    edu.education?.jenjang_pendidikan
                                }})</span
                            >
                        </div>
                        <div
                            v-if="edu.sumber_biaya || edu.education?.sumber_biaya"
                            class="text-[10px] text-slate-400"
                        >
                            Biaya:
                            {{ edu.sumber_biaya || edu.education?.sumber_biaya }}
                        </div>
                    </div>
                    <div
                        class="flex items-center justify-between border-t border-slate-100 pt-2 text-[9px] font-bold tracking-wider text-slate-400 uppercase dark:border-slate-800/80"
                    >
                        <span
                            >Mulai:
                            {{
                                edu.tanggal_mulai
                                    ? new Date(
                                          edu.tanggal_mulai,
                                      ).getFullYear()
                                    : '-'
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
                <GraduationCap
                    class="mb-3 h-10 w-10 text-slate-300 dark:text-slate-700"
                />
                <p class="text-sm font-bold">
                    Belum ada riwayat pendidikan
                </p>
                <p class="mt-1 text-xs text-slate-400">
                    Data riwayat pendidikan tinggi lanjut belum
                    diisi atau kosong.
                </p>
            </div>
        </div>
    </div>
</template>
