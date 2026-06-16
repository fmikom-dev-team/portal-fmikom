<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import TraceAlumniLayout from "@/layouts/TraceAlumniLayout.vue";
import type { BreadcrumbItem } from "@/types";
import { TPageHeader, TStatusBadge, TEmptyState, TPagination } from '@/components/trace';
import { Button } from "@/components/ui/button";
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

function mapStatus(status: string): string {
    if (status === 'applied') return 'pending';
    if (status === 'reviewed') return 'pending_review';
    return status;
}

function mapStatusLabel(status: string): string | undefined {
    if (status === 'applied') return 'Menunggu';
    if (status === 'reviewed') return 'Ditinjau';
    if (status === 'accepted') return 'Diterima';
    if (status === 'rejected') return 'Ditolak';
    return undefined;
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
            <TPageHeader title="Lamaran Saya" description="Pantau status lamaran pekerjaan Anda" :icon="FileText">
                <template #actions>
                    <Button as-child variant="ghost" size="icon-sm" class="rounded-xl text-slate-400 hover:text-[#0C447C] hover:bg-[#0C447C]/5 dark:hover:text-[#85B7EB] dark:hover:bg-[#85B7EB]/10">
                        <Link href="/trace/jobs">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <Link
                        href="/trace/jobs"
                        class="inline-flex items-center gap-2 rounded-xl border border-[#0C447C]/20 bg-[#0C447C]/5 px-4 py-2 text-sm font-semibold text-[#0C447C] transition-colors hover:bg-[#0C447C]/10 dark:border-[#85B7EB]/30 dark:bg-[#85B7EB]/10 dark:text-[#85B7EB] dark:hover:bg-[#85B7EB]/20"
                    >
                        <Briefcase class="h-4 w-4" />
                        Cari Lowongan
                    </Link>
                </template>
            </TPageHeader>

            <!-- Applications List -->
            <div v-if="applications.data.length > 0" class="space-y-3">
                <Card
                    v-for="app in applications.data"
                    :key="app.id"
                    class="group rounded-2xl border border-slate-200/60 shadow-sm transition-all duration-200 hover:border-[#85B7EB]/40 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-[#85B7EB]/30"
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
                                            class="text-[15px] font-bold text-slate-900 transition-colors hover:text-[#0C447C] dark:text-white dark:hover:text-[#85B7EB]"
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
                                <TStatusBadge
                                    :status="mapStatus(app.status)"
                                    :label="mapStatusLabel(app.status)"
                                    size="md"
                                />
                                <Link
                                    v-if="app.job_listing"
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
            <TEmptyState
                v-else
                :icon="Inbox"
                title="Belum ada lamaran"
                description="Anda belum melamar pekerjaan apapun. Jelajahi lowongan yang tersedia dan mulai melamar."
                action-label="Jelajahi Lowongan"
                action-href="/trace/jobs"
            />

            <!-- Pagination -->
            <TPagination v-if="applications.data.length > 0" :links="applications.links" />
        </div>
    </TraceAlumniLayout>
</template>
