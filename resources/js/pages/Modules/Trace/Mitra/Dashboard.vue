<script setup>
import { Link } from '@inertiajs/vue3';
import {
    Briefcase,
    Users,
    Clock,
    CheckCircle,
    Plus,
    ArrowRight,
    FileText,
    Eye,
    ExternalLink,
} from 'lucide-vue-next';
import TraceMitraLayout from '@/layouts/TraceMitraLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { TPageHeader, TStatCard, TStatusBadge, TEmptyState } from '@/components/trace';
import Button from '@/components/ui/button/Button.vue';

const props = defineProps({
    mitra: Object,
    stats: Object,
    recentApplicants: Array,
});

const statusConfig = {
    applied: { label: 'Melamar', class: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border-amber-200 dark:border-amber-800/40' },
    reviewed: { label: 'Ditinjau', class: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border-blue-200 dark:border-blue-800/40' },
    accepted: { label: 'Diterima', class: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/40' },
    rejected: { label: 'Ditolak', class: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 border-red-200 dark:border-red-800/40' },
};

const getStatusConfig = (status) => statusConfig[status] ?? { label: status, class: 'bg-slate-100 text-slate-600' };

const formatDate = (dateStr) => {
    try {
        const date = new Date(dateStr);
        const now = new Date();
        const diffMs = now.getTime() - date.getTime();
        const diffMins = Math.floor(diffMs / 60000);
        if (diffMins < 60) return `${diffMins} menit lalu`;
        const diffHrs = Math.floor(diffMins / 60);
        if (diffHrs < 24) return `${diffHrs} jam lalu`;
        const diffDays = Math.floor(diffHrs / 24);
        if (diffDays < 30) return `${diffDays} hari lalu`;
        return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    } catch {
        return dateStr;
    }
};
</script>

<template>
    <TraceMitraLayout title="Dashboard">
        <div class="flex h-full flex-1 flex-col gap-6 max-w-7xl mx-auto w-full pb-12">

            <!-- Header -->
            <TPageHeader title="Dashboard Mitra" :description="`Selamat datang, ${mitra?.nama_perusahaan ?? 'Mitra'}. Kelola lowongan kerja dan pantau pelamar dari satu tempat.`" :icon="Briefcase">
                <template #actions>
                    <Link href="/trace/open-job/jobs-listings/create">
                        <Button class="gap-2 rounded-xl bg-[#0C447C] px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-[#0C447C]/25 transition-all hover:shadow-xl hover:bg-[#0C447C]/90">
                            <Plus class="h-4 w-4" />
                            Buat Lowongan
                        </Button>
                    </Link>
                </template>
            </TPageHeader>

            <!-- Stat Cards -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <TStatCard label="Total Lowongan" :value="stats?.total_jobs ?? 0" :icon="Briefcase" color="primary" />
                <TStatCard label="Lowongan Aktif" :value="stats?.active_jobs ?? 0" :icon="CheckCircle" color="emerald" />
                <TStatCard label="Total Pelamar" :value="stats?.total_applicants ?? 0" :icon="Users" color="primary" />
                <TStatCard label="Pelamar Menunggu" :value="stats?.pending_applicants ?? 0" :icon="Clock" color="accent" />
            </div>

            <!-- Quick Links -->
            <div class="grid gap-4 sm:grid-cols-3">
                <Link
                    href="/trace/open-job/jobs-listings/create"
                    class="group relative overflow-hidden rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-xs transition-all duration-200 hover:shadow-md hover:border-[#85B7EB] dark:hover:border-[#0C447C]"
                >
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#0C447C] to-[#85B7EB] shadow-sm transition-transform duration-200 group-hover:scale-110">
                            <Plus class="h-5 w-5 text-white" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-white">Buat Lowongan Baru</h3>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500">Publikasi posisi untuk alumni</p>
                        </div>
                    </div>
                    <ArrowRight class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 dark:text-slate-700 transition-all duration-200 group-hover:text-[#0C447C] group-hover:translate-x-1" />
                </Link>

                <Link
                    href="/trace/open-job/jobs-listings"
                    class="group relative overflow-hidden rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-xs transition-all duration-200 hover:shadow-md hover:border-[#85B7EB] dark:hover:border-[#0C447C]"
                >
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#0C447C] to-[#85B7EB] shadow-sm transition-transform duration-200 group-hover:scale-110">
                            <Eye class="h-5 w-5 text-white" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-white">Lihat Pelamar</h3>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500">Kelola lamaran masuk</p>
                        </div>
                    </div>
                    <ArrowRight class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 dark:text-slate-700 transition-all duration-200 group-hover:text-[#0C447C] group-hover:translate-x-1" />
                </Link>

                <Link
                    href="/trace/open-job/profile"
                    class="group relative overflow-hidden rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-xs transition-all duration-200 hover:shadow-md hover:border-emerald-200 dark:hover:border-emerald-800/40"
                >
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 shadow-sm transition-transform duration-200 group-hover:scale-110">
                            <FileText class="h-5 w-5 text-white" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-white">Profil Perusahaan</h3>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500">Edit info perusahaan</p>
                        </div>
                    </div>
                    <ArrowRight class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 dark:text-slate-700 transition-all duration-200 group-hover:text-emerald-500 group-hover:translate-x-1" />
                </Link>
            </div>

            <!-- Recent Applicants -->
            <Card class="overflow-hidden rounded-2xl border-slate-100 dark:border-slate-800 shadow-xs">
                <CardHeader class="flex flex-row items-center justify-between border-b border-slate-50 dark:border-slate-800/60 px-6 py-4">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#0C447C]/10 dark:bg-[#0C447C]/20">
                            <Users class="h-4 w-4 text-[#0C447C] dark:text-[#85B7EB]" />
                        </div>
                        <CardTitle class="text-sm font-black uppercase tracking-widest text-slate-400">
                            Pelamar Terbaru
                        </CardTitle>
                    </div>
                    <Link
                        href="/trace/open-job/jobs-listings"
                        class="inline-flex items-center gap-1 text-[11px] font-black text-[#0C447C] hover:text-[#0C447C] dark:text-[#85B7EB] dark:hover:text-[#85B7EB] transition-colors"
                    >
                        Lihat Semua
                        <ArrowRight class="h-3 w-3" />
                    </Link>
                </CardHeader>
                <CardContent class="p-0">
                    <!-- Applicants List -->
                    <div v-if="recentApplicants && recentApplicants.length > 0" class="divide-y divide-slate-50 dark:divide-slate-800/60">
                        <div
                            v-for="applicant in recentApplicants"
                            :key="applicant.id"
                            class="flex items-center justify-between gap-4 px-6 py-4 transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/20"
                        >
                            <div class="flex items-center gap-4 min-w-0">
                                <!-- Avatar placeholder -->
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[#85B7EB]/30 to-[#0C447C]/10 dark:from-[#0C447C]/40 dark:to-[#85B7EB]/20">
                                    <span class="text-xs font-black text-[#0C447C] dark:text-[#85B7EB]">
                                        {{ applicant.alumni?.user?.name?.charAt(0)?.toUpperCase() ?? '?' }}
                                    </span>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-sm font-bold text-slate-800 dark:text-white truncate">
                                        {{ applicant.alumni?.user?.name ?? '-' }}
                                    </h4>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <Link
                                            v-if="applicant.job_listing"
                                            :href="`/trace/open-job/jobs-listings/${applicant.job_listing.id}`"
                                            class="text-xs font-medium text-[#0C447C] dark:text-[#85B7EB] hover:underline truncate"
                                        >
                                            {{ applicant.job_listing.title }}
                                        </Link>
                                        <span class="text-slate-300 dark:text-slate-700">·</span>
                                        <span class="text-[11px] text-slate-400 shrink-0">
                                            {{ formatDate(applicant.applied_at ?? applicant.created_at) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <TStatusBadge :status="applicant.status" :label="getStatusConfig(applicant.status).label" size="sm" class="shrink-0" />
                        </div>
                    </div>

                    <!-- Empty State -->
                    <TEmptyState v-else :icon="Users" title="Belum ada pelamar" description="Pelamar yang melamar ke lowongan Anda akan muncul di sini." actionLabel="Buat Lowongan Pertama" actionHref="/trace/open-job/jobs-listings/create" />
                </CardContent>
            </Card>

        </div>
    </TraceMitraLayout>
</template>
