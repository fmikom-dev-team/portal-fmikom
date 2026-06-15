<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import TraceAlumniLayout from "@/layouts/TraceAlumniLayout.vue";
import type { BreadcrumbItem } from "@/types";
import Pagination from "@/components/ui/Pagination.vue";
import { Badge } from "@/components/ui/badge";
import {
    Card,
    CardContent,
} from "@/components/ui/card";
import {
    FileText,
    ArrowLeft,
    Briefcase,
    Building2,
    ExternalLink,
    Clock,
    CheckCircle2,
    Eye,
    XCircle,
    Inbox,
} from "lucide-vue-next";

interface Application {
    id: number;
    status: string;
    cover_letter: string;
    created_at: string;
    reviewer_note: string | null;
    reviewed_at: string | null;
    job_listing: {
        id: number;
        title: string;
        mitra: {
            nama_perusahaan: string;
            logo_path: string | null;
            logo_url: string | null;
        } | null;
    } | null;
}

interface PaginatedApplications {
    data: Application[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
    to: number;
}

const props = defineProps<{
    applications: PaginatedApplications;
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: "Dashboard", href: "/trace" },
    { title: "Lowongan Kerja", href: "/trace/jobs" },
    { title: "Lamaran Saya", href: "/trace/jobs/my-applications" },
];

const statusConfig: Record<string, { label: string; class: string; icon: typeof Clock }> = {
    applied: {
        label: "Menunggu",
        class: "border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-400",
        icon: Clock,
    },
    reviewed: {
        label: "Ditinjau",
        class: "border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-800 dark:bg-blue-950/30 dark:text-blue-400",
        icon: Eye,
    },
    accepted: {
        label: "Diterima",
        class: "border-green-200 bg-green-50 text-green-700 dark:border-green-800 dark:bg-green-950/30 dark:text-green-400",
        icon: CheckCircle2,
    },
    rejected: {
        label: "Ditolak",
        class: "border-red-200 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-950/30 dark:text-red-400",
        icon: XCircle,
    },
};

function getStatusConfig(status: string) {
    return statusConfig[status] ?? statusConfig.applied;
}

function formatDate(dateStr: string): string {
    const d = new Date(dateStr);
    return d.toLocaleDateString("id-ID", {
        day: "numeric",
        month: "short",
        year: "numeric",
    });
}

function formatTime(dateStr: string): string {
    const d = new Date(dateStr);
    return d.toLocaleTimeString("id-ID", {
        hour: "2-digit",
        minute: "2-digit",
    });
}
</script>

<template>
    <TraceAlumniLayout
        title="Lamaran Saya"
        :breadcrumbs="breadcrumbItems"
        role-name="Alumni"
    >
        <div class="mx-auto space-y-6">
            <!-- Page Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <Link
                            href="/trace/jobs"
                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-400 transition-colors hover:border-slate-300 hover:text-slate-600 dark:border-zinc-700 dark:hover:border-zinc-600 dark:hover:text-slate-300"
                        >
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                        <div>
                            <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                                Lamaran Saya
                            </h1>
                            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                                Pantau status lamaran pekerjaan Anda
                            </p>
                        </div>
                    </div>
                </div>
                <Link
                    href="/trace/jobs"
                    class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition-colors hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400 dark:hover:bg-emerald-950/50"
                >
                    <Briefcase class="h-4 w-4" />
                    Cari Lowongan
                </Link>
            </div>

            <!-- Applications List -->
            <div v-if="applications.data.length > 0" class="space-y-3">
                <Card
                    v-for="app in applications.data"
                    :key="app.id"
                    class="group rounded-2xl border-slate-100 shadow-sm transition-all duration-200 hover:border-emerald-200 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-emerald-800"
                >
                    <CardContent class="p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <!-- Job Info -->
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 dark:bg-zinc-800">
                                        <img
                                            v-if="app.job_listing?.mitra?.logo_url"
                                            :src="app.job_listing.mitra.logo_url"
                                            :alt="app.job_listing.mitra.nama_perusahaan"
                                            class="h-8 w-8 rounded-lg object-contain"
                                        />
                                        <Building2 v-else class="h-4.5 w-4.5 text-slate-400 dark:text-zinc-500" />
                                    </div>
                                    <div class="min-w-0">
                                        <Link
                                            v-if="app.job_listing"
                                            :href="`/trace/jobs/${app.job_listing.id}`"
                                            class="text-[15px] font-bold text-slate-900 transition-colors hover:text-emerald-700 dark:text-white dark:hover:text-emerald-400"
                                        >
                                            {{ app.job_listing.title }}
                                        </Link>
                                        <span v-else class="text-[15px] font-bold text-slate-400 italic">
                                            Lowongan telah dihapus
                                        </span>
                                        <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                                            {{ app.job_listing?.mitra?.nama_perusahaan ?? '-' }}
                                        </p>
                                        <div class="mt-2 flex items-center gap-3 text-xs text-slate-400 dark:text-zinc-500">
                                            <span class="flex items-center gap-1">
                                                <Clock class="h-3.5 w-3.5" />
                                                {{ formatDate(app.created_at) }} · {{ formatTime(app.created_at) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cover Letter Preview -->
                                <p
                                    v-if="app.cover_letter"
                                    class="mt-3 line-clamp-2 rounded-lg bg-slate-50 p-3 text-xs leading-relaxed text-slate-500 dark:bg-zinc-800/50 dark:text-slate-400"
                                >
                                    {{ app.cover_letter }}
                                </p>

                                <!-- Reviewer Note (for accepted/rejected) -->
                                <div
                                    v-if="(app.status === 'accepted' || app.status === 'rejected') && (app.reviewer_note || app.reviewed_at)"
                                    class="mt-3 rounded-xl border p-3.5"
                                    :class="app.status === 'accepted'
                                        ? 'border-green-100 bg-green-50/50 dark:border-green-900/30 dark:bg-green-950/10'
                                        : 'border-red-100 bg-red-50/50 dark:border-red-900/30 dark:bg-red-950/10'"
                                >
                                    <div class="flex items-center gap-1.5 mb-1.5">
                                        <component
                                            :is="app.status === 'accepted' ? CheckCircle2 : XCircle"
                                            class="h-3.5 w-3.5"
                                            :class="app.status === 'accepted' ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400'"
                                        />
                                        <span class="text-[11px] font-bold uppercase tracking-wider"
                                            :class="app.status === 'accepted' ? 'text-green-700 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                                        >
                                            {{ app.status === 'accepted' ? 'Lamaran Diterima' : 'Lamaran Ditolak' }}
                                        </span>
                                    </div>
                                    <p v-if="app.reviewed_at" class="text-[11px] text-slate-400 dark:text-slate-500 mb-2">
                                        {{ new Date(app.reviewed_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                                    </p>
                                    <p v-if="app.reviewer_note" class="text-sm leading-relaxed text-slate-600 dark:text-slate-400" style="white-space: pre-line;">
                                        {{ app.reviewer_note }}
                                    </p>
                                    <p v-else class="text-xs italic text-slate-400 dark:text-slate-500">
                                        Tidak ada catatan dari perusahaan.
                                    </p>
                                </div>
                            </div>

                            <!-- Status & Action -->
                            <div class="flex shrink-0 items-center gap-3 sm:flex-col sm:items-end">
                                <Badge
                                    variant="outline"
                                    class="flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-semibold"
                                    :class="getStatusConfig(app.status).class"
                                >
                                    <component
                                        :is="getStatusConfig(app.status).icon"
                                        class="h-3.5 w-3.5"
                                    />
                                    {{ getStatusConfig(app.status).label }}
                                </Badge>
                                <Link
                                    :href="`/trace/jobs/${app.job_listing.id}`"
                                    class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-200 dark:bg-zinc-800 dark:text-slate-400 dark:hover:bg-zinc-700"
                                >
                                    <ExternalLink class="h-3 w-3" />
                                    Detail
                                </Link>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <div
                v-else
                class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 py-16 dark:border-zinc-700 dark:bg-zinc-900/50"
            >
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-zinc-800">
                    <Inbox class="h-7 w-7 text-slate-400 dark:text-zinc-500" />
                </div>
                <h3 class="mt-4 text-base font-bold text-slate-700 dark:text-slate-300">
                    Belum ada lamaran
                </h3>
                <p class="mt-1 max-w-sm text-center text-sm text-slate-400 dark:text-slate-500">
                    Anda belum melamar pekerjaan apapun. Jelajahi lowongan yang tersedia dan mulai melamar.
                </p>
                <Link
                    href="/trace/jobs"
                    class="mt-5 inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-500/20 transition-colors hover:bg-emerald-700"
                >
                    <Briefcase class="h-4 w-4" />
                    Jelajahi Lowongan
                </Link>
            </div>

            <!-- Pagination -->
            <Pagination
                v-if="applications.data.length > 0"
                :links="applications.links"
                :total="applications.total"
                :count="applications.to"
                label="lamaran"
            />
        </div>
    </TraceAlumniLayout>
</template>
