<script setup>
import { Link } from '@inertiajs/vue3';
import {
    Briefcase,
    Users,
    Clock,
    CheckCircle,
    TrendingUp,
    Plus,
    ArrowRight,
    FileText,
    Eye,
    ExternalLink,
} from 'lucide-vue-next';
import TraceMitraLayout from '@/layouts/TraceMitraLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import Badge from '@/components/ui/badge/Badge.vue';
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
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-col gap-1">
                    <div class="flex items-center gap-1.5 text-violet-600 dark:text-violet-400">
                        <TrendingUp class="h-4 w-4" />
                        <span class="text-xs font-black uppercase tracking-widest">Dashboard Mitra</span>
                    </div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-800 dark:text-white sm:text-3xl">
                        Selamat datang, {{ mitra?.nama_perusahaan ?? 'Mitra' }}
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Kelola lowongan kerja dan pantau pelamar dari satu tempat.
                    </p>
                </div>
                <Link href="/trace/open-job/jobs-listings/create">
                    <Button class="gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-purple-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-violet-500/25 transition-all hover:shadow-xl hover:shadow-violet-500/30 hover:from-violet-700 hover:to-purple-700">
                        <Plus class="h-4 w-4" />
                        Buat Lowongan
                    </Button>
                </Link>
            </div>

            <!-- Stat Cards -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Lowongan (with published/pending breakdown) -->
                <div class="relative overflow-hidden rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-xs transition-all duration-200 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Total Lowongan</span>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-sm">
                            <Briefcase class="h-4 w-4 text-white" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-3xl font-black text-slate-800 dark:text-white leading-none">
                            {{ stats?.total_jobs ?? 0 }}
                        </span>
                    </div>
                    <div class="mt-3 flex items-center gap-3 border-t border-slate-50 dark:border-slate-800/60 pt-3">
                        <div class="flex items-center gap-1.5">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">
                                {{ stats?.active_jobs ?? 0 }} Published
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">
                                {{ stats?.pending_jobs ?? 0 }} Pending
                            </span>
                        </div>
                    </div>
                    <div class="absolute -bottom-4 -right-4 h-20 w-20 rounded-full opacity-10 blur-2xl bg-gradient-to-br from-violet-500 to-purple-600"></div>
                </div>

                <!-- Lowongan Aktif -->
                <div class="relative overflow-hidden rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-xs transition-all duration-200 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Lowongan Aktif</span>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 shadow-sm">
                            <CheckCircle class="h-4 w-4 text-white" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-3xl font-black text-slate-800 dark:text-white leading-none">
                            {{ stats?.active_jobs ?? 0 }}
                        </span>
                    </div>
                    <div class="absolute -bottom-4 -right-4 h-20 w-20 rounded-full opacity-10 blur-2xl bg-gradient-to-br from-emerald-500 to-green-600"></div>
                </div>

                <!-- Total Pelamar -->
                <div class="relative overflow-hidden rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-xs transition-all duration-200 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Total Pelamar</span>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-sm">
                            <Users class="h-4 w-4 text-white" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-3xl font-black text-slate-800 dark:text-white leading-none">
                            {{ stats?.total_applicants ?? 0 }}
                        </span>
                    </div>
                    <div class="absolute -bottom-4 -right-4 h-20 w-20 rounded-full opacity-10 blur-2xl bg-gradient-to-br from-blue-500 to-indigo-600"></div>
                </div>

                <!-- Pelamar Menunggu -->
                <div class="relative overflow-hidden rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-xs transition-all duration-200 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Pelamar Menunggu</span>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-sm">
                            <Clock class="h-4 w-4 text-white" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-3xl font-black text-slate-800 dark:text-white leading-none">
                            {{ stats?.pending_applicants ?? 0 }}
                        </span>
                    </div>
                    <div class="absolute -bottom-4 -right-4 h-20 w-20 rounded-full opacity-10 blur-2xl bg-gradient-to-br from-amber-500 to-orange-600"></div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="grid gap-4 sm:grid-cols-3">
                <Link
                    href="/trace/open-job/jobs-listings/create"
                    class="group relative overflow-hidden rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-xs transition-all duration-200 hover:shadow-md hover:border-violet-200 dark:hover:border-violet-800/40"
                >
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-sm transition-transform duration-200 group-hover:scale-110">
                            <Plus class="h-5 w-5 text-white" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-white">Buat Lowongan Baru</h3>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500">Publikasi posisi untuk alumni</p>
                        </div>
                    </div>
                    <ArrowRight class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 dark:text-slate-700 transition-all duration-200 group-hover:text-violet-500 group-hover:translate-x-1" />
                </Link>

                <Link
                    href="/trace/open-job/jobs-listings"
                    class="group relative overflow-hidden rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-xs transition-all duration-200 hover:shadow-md hover:border-blue-200 dark:hover:border-blue-800/40"
                >
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-sm transition-transform duration-200 group-hover:scale-110">
                            <Eye class="h-5 w-5 text-white" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-white">Lihat Pelamar</h3>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500">Kelola lamaran masuk</p>
                        </div>
                    </div>
                    <ArrowRight class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 dark:text-slate-700 transition-all duration-200 group-hover:text-blue-500 group-hover:translate-x-1" />
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
                        <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-violet-50 dark:bg-violet-950/30">
                            <Users class="h-4 w-4 text-violet-600 dark:text-violet-400" />
                        </div>
                        <CardTitle class="text-sm font-black uppercase tracking-widest text-slate-400">
                            Pelamar Terbaru
                        </CardTitle>
                    </div>
                    <Link
                        href="/trace/open-job/jobs-listings"
                        class="inline-flex items-center gap-1 text-[11px] font-black text-violet-600 hover:text-violet-700 dark:text-violet-400 dark:hover:text-violet-300 transition-colors"
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
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-violet-100 to-purple-100 dark:from-violet-900/40 dark:to-purple-900/40">
                                    <span class="text-xs font-black text-violet-700 dark:text-violet-300">
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
                                            class="text-xs font-medium text-violet-600 dark:text-violet-400 hover:underline truncate"
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
                            <Badge
                                class="shrink-0 rounded-lg border px-2.5 py-1 text-[10px] font-bold"
                                :class="getStatusConfig(applicant.status).class"
                            >
                                {{ getStatusConfig(applicant.status).label }}
                            </Badge>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="flex flex-col items-center justify-center py-16 px-6 text-center">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 dark:bg-slate-800/50 mb-4">
                            <Users class="h-7 w-7 text-slate-300 dark:text-slate-600" />
                        </div>
                        <h4 class="text-sm font-bold text-slate-500 dark:text-slate-400">Belum ada pelamar</h4>
                        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500 max-w-xs">
                            Pelamar yang melamar ke lowongan Anda akan muncul di sini.
                        </p>
                        <Link href="/trace/open-job/jobs-listings/create" class="mt-4">
                            <Button variant="outline" class="gap-2 rounded-xl text-xs font-bold text-violet-600 border-violet-200 hover:bg-violet-50 dark:text-violet-400 dark:border-violet-800 dark:hover:bg-violet-950/30">
                                <Plus class="h-3.5 w-3.5" />
                                Buat Lowongan Pertama
                            </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>

        </div>
    </TraceMitraLayout>
</template>
