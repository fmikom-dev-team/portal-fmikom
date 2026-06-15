<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import {
    ClipboardList,
    ArrowRight,
    Calendar,
    Clock,
    FileText,
    Lock,
    Eye,
} from "lucide-vue-next";
import { show as alumniTracerShow } from "@/routes/module/trace/kuesioner";
import TraceAlumniLayout from "@/layouts/TraceAlumniLayout.vue";
import type { BreadcrumbItem } from "@/types";

defineProps<{
    kuesioners: any[];
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: "Dashboard", href: "/trace" },
    { title: "Kuesioner", href: "/trace/kuesioner" },
];

const formatDateRange = (
    mulai: string | null,
    selesai: string | null,
): string => {
    const fmt = (d: string) =>
        new Date(d).toLocaleDateString("id-ID", {
            day: "numeric",
            month: "short",
            year: "numeric",
        });

    if (mulai && selesai) return `${fmt(mulai)} – ${fmt(selesai)}`;
    if (selesai) return `Tutup ${fmt(selesai)}`;
    if (mulai) return `Buka ${fmt(mulai)}`;
    return "Selalu Aktif";
};
</script>

<template>
    <Head title="Kuesioner" />
    <TraceAlumniLayout role-name="Alumni" :breadcrumbs="breadcrumbItems">
        <div class="mx-auto max-w-6xl">
            <!-- Header Section -->
            <div
                class="mb-10 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h1
                        class="text-3xl font-black text-slate-900 dark:text-white"
                    >
                        Tracer Study
                    </h1>
                    <p class="mt-1 text-slate-500 dark:text-slate-400">
                        Pusat pengisian kuesioner pelacakan alumni FMIKOM.
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex flex-col items-end">
                        <span
                            class="text-xs font-bold tracking-widest text-slate-400 uppercase"
                            >Total Kuesioner</span
                        >
                        <span
                            class="text-2xl font-black text-green-600 dark:text-green-400"
                            >{{ kuesioners.length }}</span
                        >
                    </div>
                </div>
            </div>

            <!-- Welcome/Info Banner -->
            <div
                class="mb-10 overflow-hidden rounded-3xl bg-gradient-to-br from-green-500 to-emerald-700 p-8 text-white shadow-xl shadow-green-500/20"
            >
                <div class="flex flex-col gap-8 md:flex-row md:items-center">
                    <div class="flex-1">
                        <h2 class="text-2xl font-black">
                            Kontribusi Anda Sangat Berarti!
                        </h2>
                        <p class="mt-2 text-green-100 opacity-90">
                            Data yang Anda berikan akan membantu Fakultas dalam
                            meningkatkan kualitas kurikulum dan pelayanan bagi
                            mahasiswa di masa depan.
                        </p>
                    </div>
                    <div class="shrink-0">
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-md"
                        >
                            <ClipboardList class="h-8 w-8 text-white" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Questionnaire List -->
            <div
                v-if="kuesioners.length"
                class="grid gap-6 md:grid-cols-2 lg:grid-cols-3"
            >
                <div
                    v-for="kuesioner in kuesioners"
                    :key="kuesioner.id"
                    class="group relative overflow-hidden rounded-3xl border border-slate-100 bg-white p-6 transition-all hover:border-green-200 hover:shadow-xl hover:shadow-green-500/5 dark:border-slate-800 dark:bg-slate-900"
                >
                    <!-- Status Tag -->
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            v-if="kuesioner.responses_count > 0"
                            class="flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-black text-emerald-600 uppercase dark:bg-emerald-900/30 dark:text-emerald-400"
                        >
                            <Lock class="h-3 w-3" /> Sudah Diisi &middot;
                            Terkunci
                        </div>
                        <div
                            v-else
                            class="flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-[10px] font-black text-amber-600 uppercase dark:bg-amber-900/30 dark:text-amber-400"
                        >
                            <Clock class="h-3 w-3" /> Belum Diisi
                        </div>
                        <span
                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >Tahun {{ kuesioner.tahun }}</span
                        >
                    </div>

                    <h3
                        class="mb-2 text-xl font-black text-slate-900 transition-colors group-hover:text-green-600 dark:text-white"
                    >
                        {{ kuesioner.judul }}
                    </h3>
                    <p
                        class="mb-6 line-clamp-2 text-sm text-slate-500 dark:text-slate-400"
                    >
                        {{
                            kuesioner.subtitle ||
                            "Mohon kesediaannya untuk mengisi kuesioner tracer study ini."
                        }}
                    </p>

                    <div
                        class="mb-6 flex items-center gap-4 border-t border-slate-50 pt-6 dark:border-slate-800"
                    >
                        <div
                            class="flex items-center gap-1 text-xs font-bold text-slate-500 dark:text-slate-400"
                        >
                            <FileText class="h-3.5 w-3.5" />
                            {{ kuesioner.sections_count || 0 }} Bagian
                        </div>
                        <div
                            class="flex items-center gap-1 text-xs font-bold text-slate-500 dark:text-slate-400"
                        >
                            <Calendar class="h-3.5 w-3.5" />
                            {{
                                formatDateRange(
                                    kuesioner.date_mulai,
                                    kuesioner.date_selesai,
                                )
                            }}
                        </div>
                    </div>

                    <Link
                        :href="alumniTracerShow(kuesioner.id).url"
                        class="flex w-full items-center justify-center gap-2 rounded-2xl py-4 text-sm font-black transition-all"
                        :class="
                            kuesioner.responses_count > 0
                                ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/20 dark:text-emerald-400 dark:hover:bg-emerald-950/40'
                                : 'bg-slate-50 text-slate-900 group-hover:bg-green-600 group-hover:text-white dark:bg-slate-800 dark:text-white'
                        "
                    >
                        <Eye
                            v-if="kuesioner.responses_count > 0"
                            class="h-4 w-4"
                        />
                        {{
                            kuesioner.responses_count > 0
                                ? "Lihat Jawaban"
                                : "Isi Sekarang"
                        }}
                        <ArrowRight
                            v-if="kuesioner.responses_count === 0"
                            class="h-4 w-4"
                        />
                    </Link>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-else
                class="flex min-h-100 flex-col items-center justify-center rounded-3xl border-2 border-dashed border-slate-100 bg-slate-50/30 text-center dark:border-slate-800 dark:bg-slate-900/30"
            >
                <div
                    class="mb-4 rounded-full bg-white p-6 shadow-sm dark:bg-slate-900"
                >
                    <ClipboardList class="h-12 w-12 text-slate-300" />
                </div>
                <h3 class="text-xl font-black text-slate-900 dark:text-white">
                    Belum Ada Kuesioner Aktif
                </h3>
                <p class="mt-2 text-slate-500 dark:text-slate-400">
                    Silakan cek kembali di lain waktu.
                </p>
            </div>
        </div>
    </TraceAlumniLayout>
</template>
