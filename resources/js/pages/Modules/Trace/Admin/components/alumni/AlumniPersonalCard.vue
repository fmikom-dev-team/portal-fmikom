<script setup lang="ts">
import {
    User,
    CreditCard,
    Hash,
    Mail,
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
            <User class="h-4 w-4 text-blue-500" />
            <h2
                class="text-sm font-black text-slate-800 dark:text-white"
            >
                Data Pribadi
            </h2>
        </div>
        <div class="flex-1 space-y-4 p-5">
            <div class="flex items-start gap-3">
                <div
                    class="mt-0.5 rounded-lg bg-blue-50 p-2 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                >
                    <User class="h-4 w-4" />
                </div>
                <div class="flex flex-col">
                    <span
                        class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >Jenis Kelamin</span
                    >
                    <span
                        class="text-sm font-semibold text-slate-700 dark:text-slate-300"
                        >{{
                            alumni.jenis_kelamin === 'L'
                                ? '♂ Laki-laki'
                                : alumni.jenis_kelamin === 'P'
                                  ? '♀ Perempuan'
                                  : '-'
                        }}</span
                    >
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div
                    class="mt-0.5 rounded-lg bg-indigo-50 p-2 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400"
                >
                    <CreditCard class="h-4 w-4" />
                </div>
                <div class="flex flex-col">
                    <span
                        class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >NIK</span
                    >
                    <span
                        class="font-mono text-sm font-bold tracking-wider text-slate-700 dark:text-slate-300"
                        >{{ alumni.nik_masked || '-' }}</span
                    >
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div
                    class="mt-0.5 rounded-lg bg-violet-50 p-2 text-violet-600 dark:bg-violet-950/50 dark:text-violet-400"
                >
                    <Hash class="h-4 w-4" />
                </div>
                <div class="flex flex-col">
                    <span
                        class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >NPWP</span
                    >
                    <span
                        class="font-mono text-sm font-bold tracking-wider text-slate-700 dark:text-slate-300"
                        >{{ alumni.npwp_masked || '-' }}</span
                    >
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div
                    class="mt-0.5 rounded-lg bg-slate-50 p-2 text-slate-600 dark:bg-slate-800 dark:text-slate-400"
                >
                    <Mail class="h-4 w-4" />
                </div>
                <div class="flex min-w-0 flex-col">
                    <span
                        class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >Email Akun</span
                    >
                    <span
                        class="truncate text-sm font-semibold text-slate-700 dark:text-slate-300"
                        :title="alumni.user?.email"
                        >{{ alumni.user?.email || '-' }}</span
                    >
                </div>
            </div>
        </div>
    </div>
</template>
