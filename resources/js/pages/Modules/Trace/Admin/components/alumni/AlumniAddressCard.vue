<script setup lang="ts">
import {
    Home,
    Map,
    MapPin,
    Globe,
} from 'lucide-vue-next';
import type { ProfilAlumni } from '@/types/trace';

defineProps<{
    alumni: ProfilAlumni;
}>();
</script>

<template>
    <div
        class="flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
    >
        <div
            class="flex items-center gap-2 border-b border-slate-100 bg-slate-50/50 px-5 py-4 dark:border-slate-800 dark:bg-slate-900/50"
        >
            <Home class="h-4 w-4 text-emerald-500" />
            <h2
                class="text-sm font-black text-slate-800 dark:text-white"
            >
                Domisili / Alamat Rumah
            </h2>
        </div>
        <div class="flex-1 space-y-4 p-5">
            <div class="flex items-start gap-3">
                <div
                    class="mt-0.5 rounded-lg bg-emerald-50 p-2 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400"
                >
                    <Map class="h-4 w-4" />
                </div>
                <div class="flex flex-col">
                    <span
                        class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >Provinsi</span
                    >
                    <span
                        class="text-sm font-bold text-slate-700 dark:text-slate-300"
                        >{{ alumni.provinsi?.name || '-' }}</span
                    >
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div
                    class="mt-0.5 rounded-lg bg-teal-50 p-2 text-teal-600 dark:bg-teal-950/50 dark:text-teal-400"
                >
                    <MapPin class="h-4 w-4" />
                </div>
                <div class="flex flex-col">
                    <span
                        class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >Kota / Kabupaten</span
                    >
                    <span
                        class="text-sm font-semibold text-slate-700 dark:text-slate-300"
                        >{{ alumni.kota?.name || '-' }}</span
                    >
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div
                    class="mt-0.5 rounded-lg bg-slate-50 p-2 text-slate-600 dark:bg-slate-800 dark:text-slate-400"
                >
                    <Home class="h-4 w-4" />
                </div>
                <div class="flex flex-col">
                    <span
                        class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >Alamat Lengkap</span
                    >
                    <span
                        class="text-sm leading-relaxed font-semibold text-slate-700 dark:text-slate-300"
                        >{{ alumni.alamat_rumah || '-' }}</span
                    >
                </div>
            </div>

            <div
                v-if="alumni.latitude_rumah"
                class="flex items-start gap-3"
            >
                <div
                    class="mt-0.5 rounded-lg bg-emerald-50 p-2 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400"
                >
                    <Globe class="h-4 w-4" />
                </div>
                <div class="flex flex-col">
                    <span
                        class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >Koordinat</span
                    >
                    <span
                        class="mt-0.5 w-max rounded bg-emerald-500/10 px-2 py-0.5 font-mono text-xs font-bold text-emerald-600 dark:text-emerald-400"
                    >
                        {{
                            Number(
                                alumni.latitude_rumah,
                            ).toFixed(5)
                        }},
                        {{
                            Number(
                                alumni.longitude_rumah,
                            ).toFixed(5)
                        }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
