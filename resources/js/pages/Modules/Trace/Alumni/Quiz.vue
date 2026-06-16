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
import { TPageHeader, TStatusBadge, TEmptyState } from '@/components/trace';

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
        <div class="mx-auto max-w-6xl space-y-6">
            <!-- Header Section -->
            <TPageHeader
                title="Kuesioner"
                description="Pusat pengisian kuesioner pelacakan alumni FMIKOM."
                :icon="ClipboardList"
            >
                <template #actions>
                    <div class="flex flex-col items-end">
                        <span
                            class="text-xs font-bold tracking-widest text-slate-400 uppercase"
                            >Total Kuesioner</span
                        >
                        <span
                            class="text-2xl font-black text-[#0C447C] dark:text-[#85B7EB]"
                            >{{ kuesioners.length }}</span
                        >
                    </div>
                </template>
            </TPageHeader>

            <!-- Welcome/Info Banner -->
            <div
                class="overflow-hidden rounded-2xl bg-gradient-to-br from-[#0C447C] to-[#0C447C]/80 p-8 text-white shadow-xl shadow-[#0C447C]/20"
            >
                <div class="flex flex-col gap-8 md:flex-row md:items-center">
                    <div class="flex-1">
                        <h2 class="text-2xl font-black">
                            Kontribusi Anda Sangat Berarti!
                        </h2>
                        <p class="mt-2 text-[#85B7EB]/90">
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
                    class="group relative overflow-hidden rounded-2xl border border-slate-100 bg-white p-6 transition-all hover:border-[#85B7EB]/40 hover:shadow-xl hover:shadow-[#0C447C]/5 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-[#85B7EB]/30"
                >
                    <!-- Status Tag -->
                    <div class="mb-4 flex items-center justify-between">
                        <TStatusBadge
                            v-if="kuesioner.responses_count > 0"
                            status="closed"
                            label="Sudah Diisi · Terkunci"
                            size="sm"
                        />
                        <TStatusBadge
                            v-else
                            status="pending"
                            label="Belum Diisi"
                            size="sm"
                        />
                        <span
                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >Tahun {{ kuesioner.tahun }}</span
                        >
                    </div>

                    <h3
                        class="mb-2 text-xl font-black text-slate-900 transition-colors group-hover:text-[#0C447C] dark:text-white dark:group-hover:text-[#85B7EB]"
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
                        class="mb-6 flex items-center gap-4 border-t border-slate-100 pt-6 dark:border-zinc-800"
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
                        class="flex w-full items-center justify-center gap-2 rounded-xl py-4 text-sm font-black transition-all"
                        :class="
                            kuesioner.responses_count > 0
                                ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/20 dark:text-emerald-400 dark:hover:bg-emerald-950/40'
                                : 'bg-[#0C447C]/5 text-[#0C447C] group-hover:bg-[#0C447C] group-hover:text-white dark:bg-[#85B7EB]/10 dark:text-[#85B7EB] dark:group-hover:bg-[#85B7EB] dark:group-hover:text-slate-900'
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
            <TEmptyState
                v-else
                :icon="ClipboardList"
                title="Belum Ada Kuesioner Aktif"
                description="Silakan cek kembali di lain waktu."
                class="min-h-80 rounded-2xl border border-dashed border-slate-200 dark:border-zinc-700"
            />
        </div>
    </TraceAlumniLayout>
</template>
