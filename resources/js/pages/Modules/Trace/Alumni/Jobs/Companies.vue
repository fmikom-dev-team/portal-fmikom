<script setup lang="ts">
import { Link, router } from "@inertiajs/vue3";
import TraceAlumniLayout from "@/layouts/TraceAlumniLayout.vue";
import type { BreadcrumbItem } from "@/types";
import { TPageHeader, TEmptyState, TPagination } from '@/components/Trace';
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import {
    Building2,
    Search,
    Briefcase,
    ArrowLeft,
    ArrowRight,
} from "lucide-vue-next";
import { ref, watch } from "vue";

interface Company {
    id: number;
    nama_perusahaan: string;
    logo_path: string | null;
    logo_url: string | null;
    deskripsi: string | null;
    job_listings_count: number;
}

interface PaginatedCompanies {
    data: Company[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
}

const props = defineProps<{
    companies: PaginatedCompanies;
    filters: { search: string | null };
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: "Dashboard", href: "/trace" },
    { title: "Lowongan Kerja", href: "/trace/jobs" },
    { title: "Daftar Perusahaan", href: "/trace/jobs/companies" },
];

const search = ref(props.filters.search ?? "");

function doSearch() {
    router.get(
        "/trace/jobs/companies",
        { search: search.value || undefined },
        { preserveState: true, replace: true },
    );
}
</script>

<template>
    <TraceAlumniLayout
        title="Daftar Perusahaan"
        :breadcrumbs="breadcrumbItems"
        role-name="Alumni"
    >
        <div class="mx-auto space-y-6">
            <!-- Page Header -->
            <TPageHeader title="Daftar Perusahaan" :description="`${companies.total} perusahaan dengan lowongan aktif`" :icon="Building2">
                <template #actions>
                    <Link
                        href="/trace/jobs"
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition-colors hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700"
                    >
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </template>
            </TPageHeader>

            <!-- Search -->
            <div class="flex items-center gap-2">
                <div class="relative flex-1 max-w-md">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <Input
                        v-model="search"
                        placeholder="Cari perusahaan..."
                        class="rounded-xl pl-10"
                        @keyup.enter="doSearch"
                    />
                </div>
                <Button class="rounded-xl bg-[#0C447C] hover:bg-[#0C447C]/90 text-white gap-1.5 dark:bg-[#85B7EB] dark:text-slate-900 dark:hover:bg-[#85B7EB]/90" @click="doSearch">
                    <Search class="h-3.5 w-3.5" /> Cari
                </Button>
            </div>

            <!-- Company Grid -->
            <div
                v-if="companies.data.length"
                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <Link
                    v-for="company in companies.data"
                    :key="company.id"
                    :href="`/trace/jobs?mitra_id=${company.id}`"
                    class="group rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm transition-all duration-200 hover:border-[#85B7EB]/40 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-[#85B7EB]/30"
                >
                    <div class="flex items-center gap-4">
                        <!-- Logo -->
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-slate-50 border border-slate-100 dark:bg-zinc-800 dark:border-zinc-700 overflow-hidden">
                            <img
                                v-if="company.logo_url"
                                :src="company.logo_url"
                                :alt="company.nama_perusahaan"
                                class="h-14 w-14 rounded-xl object-contain"
                            />
                            <Building2 v-else class="h-7 w-7 text-slate-300 dark:text-zinc-600" />
                        </div>

                        <!-- Info -->
                        <div class="min-w-0 flex-1">
                            <h3 class="truncate text-[15px] font-bold text-slate-900 group-hover:text-[#0C447C] dark:text-white dark:group-hover:text-[#85B7EB]">
                                {{ company.nama_perusahaan }}
                            </h3>
                            <p
                                v-if="company.deskripsi"
                                class="mt-0.5 line-clamp-1 text-xs text-slate-400 dark:text-zinc-500"
                            >
                                {{ company.deskripsi }}
                            </p>
                            <div class="mt-2 flex items-center gap-1.5">
                                <div class="flex items-center gap-1 rounded-lg px-2 py-1 text-[11px] font-bold"
                                    :class="company.job_listings_count > 0
                                        ? 'bg-[#0C447C]/5 text-[#0C447C] dark:bg-[#85B7EB]/10 dark:text-[#85B7EB]'
                                        : 'bg-slate-100 text-slate-400 dark:bg-zinc-800 dark:text-zinc-500'"
                                >
                                    <Briefcase class="h-3 w-3" />
                                    {{ company.job_listings_count }} Lowongan
                                </div>
                            </div>
                        </div>

                        <!-- Arrow -->
                        <ArrowRight class="h-4 w-4 shrink-0 text-slate-300 transition-transform group-hover:translate-x-0.5 group-hover:text-[#0C447C] dark:text-zinc-600 dark:group-hover:text-[#85B7EB]" />
                    </div>
                </Link>
            </div>

            <!-- Empty State -->
            <TEmptyState
                v-else
                :icon="Building2"
                title="Belum ada perusahaan"
                description="Belum ada perusahaan yang memposting lowongan. Cek kembali nanti."
            />

            <!-- Pagination -->
            <TPagination v-if="companies.data.length" :links="companies.links" />
        </div>
    </TraceAlumniLayout>
</template>
