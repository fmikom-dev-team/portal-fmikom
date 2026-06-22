<script setup lang="ts">
import { Link, router } from "@inertiajs/vue3";
import TraceAlumniLayout from "@/layouts/TraceAlumniLayout.vue";
import type { BreadcrumbItem } from "@/types";
import { TPageHeader, TEmptyState, TPagination } from '@/components/trace';
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
    Search,
    MapPin,
    Clock,
    Briefcase,
    Bookmark,
    Building2,
    DollarSign,
    ChevronDown,
    Filter,
    SlidersHorizontal,
    FileText,
    X,
} from "lucide-vue-next";
import { ref } from "vue";

interface Job {
    id: number;
    title: string;
    description: string;
    status: string;
    deadline: string;
    experience_level: string;
    location_type: string;
    tipe_kerja: string;
    salary_min: number | null;
    salary_max: number | null;
    is_salary_visible: boolean;
    applicants_count: number;
    category: { nama: string } | null;
    mitra: { nama_perusahaan: string; logo_path: string | null; logo_url: string | null };
}

interface PaginatedJobs {
    data: Job[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
    to: number;
}

const props = defineProps<{
    jobs: PaginatedJobs;
    categories: Array<{ id: number; nama: string }>;
    mitras: Array<{ id: number; nama_perusahaan: string }>;
    filters: {
        search: string | null;
        category: string | null;
        tipe_kerja: string | null;
        location_type: string | null;
        mitra_id: string | null;
        salary_min: string | null;
        salary_max: string | null;
    };
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: "Dashboard", href: "/trace" },
    { title: "Lowongan Kerja", href: "/trace/jobs" },
];

const selectClass = 'flex h-10 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 transition-all focus:outline-none focus:ring-2 focus:ring-[#0C447C]/50 focus:border-[#0C447C] dark:border-zinc-700 dark:bg-zinc-900 dark:text-slate-300 appearance-none';

const search = ref(props.filters.search ?? "");
const category = ref(props.filters.category ?? "");
const tipeKerja = ref(props.filters.tipe_kerja ?? "");
const locationType = ref(props.filters.location_type ?? "");
const mitraId = ref(props.filters.mitra_id ?? "");
const salaryMin = ref(props.filters.salary_min ?? "");
const salaryMax = ref(props.filters.salary_max ?? "");
const showAdvanced = ref(Boolean(props.filters.category || props.filters.tipe_kerja || props.filters.location_type || props.filters.mitra_id || props.filters.salary_min || props.filters.salary_max));



function applyFilters() {
    const params: Record<string, string> = {};
    if (search.value) params.search = search.value;
    if (category.value) params.category = category.value;
    if (tipeKerja.value) params.tipe_kerja = tipeKerja.value;
    if (locationType.value) params.location_type = locationType.value;
    if (mitraId.value) params.mitra_id = mitraId.value;
    if (salaryMin.value) params.salary_min = salaryMin.value;
    if (salaryMax.value) params.salary_max = salaryMax.value;

    router.get("/trace/jobs", params, {
        preserveState: true,
        preserveScroll: true,
    });
}

function clearFilters() {
    search.value = "";
    category.value = "";
    tipeKerja.value = "";
    locationType.value = "";
    mitraId.value = "";
    salaryMin.value = "";
    salaryMax.value = "";
    router.get("/trace/jobs", {}, { preserveState: true });
}



const hasActiveFilters = () =>
    search.value || category.value || tipeKerja.value || locationType.value || mitraId.value || salaryMin.value || salaryMax.value;

function formatSalary(value: number): string {
    if (value >= 1_000_000) return `${(value / 1_000_000).toFixed(value % 1_000_000 === 0 ? 0 : 1)} jt`;
    if (value >= 1_000) return `${(value / 1_000).toFixed(0)} rb`;
    return value.toLocaleString("id-ID");
}

function salaryDisplay(job: Job): string | null {
    if (!job.is_salary_visible) return null;
    if (job.salary_min && job.salary_max) return `Rp ${formatSalary(job.salary_min)} – ${formatSalary(job.salary_max)}`;
    if (job.salary_min) return `Dari Rp ${formatSalary(job.salary_min)}`;
    if (job.salary_max) return `Hingga Rp ${formatSalary(job.salary_max)}`;
    return null;
}

function formatDeadline(dateStr: string): string {
    const d = new Date(dateStr);
    return d.toLocaleDateString("id-ID", { day: "numeric", month: "short", year: "numeric" });
}

const experienceLabelMap: Record<string, string> = {
    entry: "Entry Level",
    junior: "Junior",
    mid: "Mid-Level",
    senior: "Senior",
    lead: "Lead",
};

const locationLabelMap: Record<string, string> = {
    onsite: "On-site",
    remote: "Remote",
    hybrid: "Hybrid",
};

const tipeKerjaLabelMap: Record<string, string> = {
    full_time: "Full-time",
    part_time: "Part-time",
    contract: "Kontrak",
    internship: "Magang",
    freelance: "Freelance",
};
</script>

<template>
    <TraceAlumniLayout
        title="Lowongan Kerja"
        :breadcrumbs="breadcrumbItems"
        role-name="Alumni"
    >
        <div class="mx-auto space-y-6">
            <!-- Page Header -->
            <TPageHeader title="Lowongan Kerja" description="Jelajahi lowongan kerja yang cocok untuk Anda." :icon="Briefcase">
                <template #actions>
                    <Link
                        href="/trace/jobs/companies"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-slate-400 dark:hover:bg-zinc-800"
                    >
                        <Building2 class="h-4 w-4" />
                        Perusahaan
                    </Link>
                    <Link
                        href="/trace/jobs/my-bookmarks"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-slate-400 dark:hover:bg-zinc-800"
                    >
                        <Bookmark class="h-4 w-4" />
                        Tersimpan
                    </Link>
                    <Link
                        href="/trace/jobs/my-applications"
                        class="inline-flex items-center gap-2 rounded-xl border border-[#0C447C]/20 bg-[#0C447C]/5 px-4 py-2 text-sm font-semibold text-[#0C447C] transition-colors hover:bg-[#0C447C]/10 dark:border-[#85B7EB]/30 dark:bg-[#85B7EB]/10 dark:text-[#85B7EB] dark:hover:bg-[#85B7EB]/20"
                    >
                        <FileText class="h-4 w-4" />
                        Lamaran Saya
                    </Link>
                </template>
            </TPageHeader>

            <!-- Search & Filters -->
            <div class="rounded-2xl border border-slate-200/60 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <!-- Row 1: Search -->
                <div class="p-4 pb-3">
                    <div class="relative">
                        <Search class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari lowongan, perusahaan, posisi..."
                            aria-label="Cari lowongan"
                            :class="selectClass"
                            class="h-11 pl-10 pr-[4.5rem]"
                            @keyup.enter="applyFilters"
                        />
                        <button
                            class="absolute right-1.5 top-1/2 -translate-y-1/2 inline-flex h-8 items-center gap-1.5 rounded-lg bg-[#0C447C] px-3.5 text-xs font-semibold text-white transition-colors hover:bg-[#0C447C]/90 dark:bg-[#85B7EB] dark:text-slate-900"
                            @click="applyFilters"
                            aria-label="Cari lowongan"
                        >
                            <Search class="h-3.5 w-3.5" />
                            Cari
                        </button>
                    </div>
                </div>

                <!-- Row 2: Filter bar -->
                <div class="flex flex-wrap items-center gap-2 border-t border-slate-50 px-4 py-3 dark:border-zinc-800/50">
                    <SlidersHorizontal class="h-4 w-4 shrink-0 text-slate-400" />
                    <span class="text-xs text-slate-500 dark:text-slate-400">Filter lowongan berdasarkan kriteria</span>

                    <div class="ml-auto flex items-center gap-2">
                        <button
                            v-if="hasActiveFilters()"
                            class="inline-flex h-9 items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-2.5 text-[11px] font-medium text-red-600 transition-colors hover:bg-red-100 dark:border-red-900 dark:bg-red-950/20 dark:text-red-400"
                            @click="clearFilters"
                            aria-label="Reset filter"
                        >
                            <X class="h-3 w-3" />
                            Reset
                        </button>

                        <button
                            class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-[11px] font-medium text-slate-600 transition-colors hover:bg-slate-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-slate-400"
                            @click="showAdvanced = !showAdvanced"
                            aria-label="Buka/tutup filter lanjutan"
                        >
                            <Filter class="h-3.5 w-3.5" />
                            Filter Lanjutan
                            <ChevronDown
                                class="h-3 w-3 transition-transform duration-200"
                                :class="showAdvanced ? 'rotate-180' : ''"
                            />
                        </button>
                    </div>
                </div>

                <!-- Advanced Filters (collapsible) -->
                <div
                    v-show="showAdvanced"
                    class="border-t border-slate-100 bg-slate-50/50 px-4 py-4 dark:border-zinc-800 dark:bg-zinc-800/20"
                >
                    <div class="space-y-4">
                        <!-- Row: Bidang, Jenis, Tempat Kerja -->
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <div>
                                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-400">Bidang</label>
                                <select v-model="category" :class="selectClass" aria-label="Filter bidang">
                                    <option value="">Semua Bidang</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="String(cat.id)">
                                        {{ cat.nama }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-400">Jenis</label>
                                <select v-model="tipeKerja" :class="selectClass" aria-label="Filter jenis pekerjaan">
                                    <option value="">Semua Jenis</option>
                                    <option value="full_time">Full-time</option>
                                    <option value="part_time">Part-time</option>
                                    <option value="magang">Magang</option>
                                    <option value="freelance">Freelance</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-400">Tempat Kerja</label>
                                <select v-model="locationType" :class="selectClass" aria-label="Filter tempat kerja">
                                    <option value="">Semua Tempat Kerja</option>
                                    <option value="onsite">On-site</option>
                                    <option value="remote">Remote</option>
                                    <option value="hybrid">Hybrid</option>
                                </select>
                            </div>
                        </div>

                        <!-- Row: Perusahaan + Gaji -->
                        <div class="grid grid-cols-1 gap-3 lg:grid-cols-3">
                            <div>
                                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-400">Perusahaan</label>
                                <select v-model="mitraId" :class="selectClass" aria-label="Filter perusahaan">
                                    <option value="">Semua Perusahaan</option>
                                    <option v-for="m in mitras" :key="m.id" :value="String(m.id)">
                                        {{ m.nama_perusahaan }}
                                    </option>
                                </select>
                            </div>
                            <div class="lg:col-span-2">
                                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-400">Rentang Gaji</label>
                                <div class="flex items-center gap-2">
                                    <div class="relative flex-1">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">Rp</span>
                                        <input v-model="salaryMin" type="number" placeholder="Minimum" :class="selectClass" class="pl-8" aria-label="Gaji minimum" />
                                    </div>
                                    <span class="text-sm text-slate-300">–</span>
                                    <div class="relative flex-1">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">Rp</span>
                                        <input v-model="salaryMax" type="number" placeholder="Maksimum" :class="selectClass" class="pl-8" aria-label="Gaji maksimum" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Terapkan -->
                        <div class="flex justify-end">
                            <button
                                class="inline-flex h-10 items-center gap-1.5 rounded-xl bg-[#0C447C] px-6 text-sm font-semibold text-white shadow-sm shadow-[#0C447C]/20 transition-colors hover:bg-[#0C447C]/90 dark:bg-[#85B7EB] dark:text-slate-900 dark:hover:bg-[#85B7EB]/90"
                                @click="applyFilters"
                                aria-label="Terapkan filter"
                            >
                                <Filter class="h-3.5 w-3.5" />
                                Terapkan Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Job Cards Grid -->
            <div
                v-if="jobs.data.length > 0"
                class="grid grid-cols-1 gap-4 lg:grid-cols-2"
            >
                <div
                    v-for="job in jobs.data"
                    :key="job.id"
                    class="group rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm transition-all duration-200 hover:border-[#85B7EB]/40 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-[#85B7EB]/30"
                >
                    <div class="flex gap-4">
                        <!-- Company Logo -->
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-slate-100 dark:bg-zinc-800">
                            <img
                                v-if="job.mitra?.logo_url"
                                :src="job.mitra?.logo_url"
                                :alt="job.mitra.nama_perusahaan"
                                class="h-10 w-10 rounded-lg object-contain"
                            />
                            <Building2 v-else class="h-5 w-5 text-slate-400 dark:text-zinc-500" />
                        </div>

                        <!-- Job Info -->
                        <div class="min-w-0 flex-1">
                            <h3 class="truncate text-[15px] font-bold text-slate-900 group-hover:text-[#0C447C] dark:text-white dark:group-hover:text-[#85B7EB]">
                                {{ job.title }}
                            </h3>
                            <p class="mt-0.5 truncate text-sm text-slate-500 dark:text-slate-400">
                                {{ job.mitra?.nama_perusahaan }}
                            </p>
                        </div>
                    </div>

                    <!-- Tags -->
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        <Badge
                            v-if="job.experience_level"
                            variant="secondary"
                            class="rounded-lg bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600 dark:bg-zinc-800 dark:text-zinc-400"
                        >
                            {{ experienceLabelMap[job.experience_level] ?? job.experience_level }}
                        </Badge>
                        <Badge
                            v-if="job.location_type"
                            variant="secondary"
                            class="rounded-lg bg-blue-50 px-2 py-0.5 text-[11px] font-medium text-blue-600 dark:bg-blue-950/30 dark:text-blue-400"
                        >
                            <MapPin class="mr-1 inline h-3 w-3" />
                            {{ locationLabelMap[job.location_type] ?? job.location_type }}
                        </Badge>
                        <Badge
                            v-if="job.tipe_kerja"
                            variant="secondary"
                            class="rounded-lg bg-[#0C447C]/5 px-2 py-0.5 text-[11px] font-medium text-[#0C447C] dark:bg-[#85B7EB]/10 dark:text-[#85B7EB]"
                        >
                            <Briefcase class="mr-1 inline h-3 w-3" />
                            {{ tipeKerjaLabelMap[job.tipe_kerja] ?? job.tipe_kerja }}
                        </Badge>
                        <Badge
                            v-if="job.category?.nama"
                            variant="secondary"
                            class="rounded-lg bg-[#0C447C]/10 px-2 py-0.5 text-[11px] font-medium text-[#0C447C] dark:bg-[#0C447C]/20 dark:text-[#85B7EB]"
                        >
                            {{ job.category.nama }}
                        </Badge>
                    </div>

                    <!-- Bottom Row: Salary, Deadline, CTA -->
                    <div class="mt-4 flex items-center justify-between border-t border-slate-50 pt-3 dark:border-zinc-800">
                        <div class="flex items-center gap-3 text-xs text-slate-400 dark:text-zinc-500">
                            <span v-if="salaryDisplay(job)" class="flex items-center gap-1 font-semibold text-[#0C447C] dark:text-[#85B7EB]">
                                <DollarSign class="h-3.5 w-3.5" />
                                {{ salaryDisplay(job) }}
                            </span>
                            <span v-if="job.deadline" class="flex items-center gap-1">
                                <Clock class="h-3.5 w-3.5" />
                                {{ formatDeadline(job.deadline) }}
                            </span>
                        </div>

                        <Link
                            :href="`/trace/jobs/${job.id}`"
                            class="inline-flex items-center gap-1 rounded-lg bg-[#0C447C]/5 px-3 py-1.5 text-xs font-semibold text-[#0C447C] transition-colors hover:bg-[#0C447C]/10 dark:bg-[#85B7EB]/10 dark:text-[#85B7EB] dark:hover:bg-[#85B7EB]/20"
                        >
                            Lihat Detail
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="space-y-4">
                <TEmptyState
                    :icon="Briefcase"
                    title="Tidak ada lowongan ditemukan"
                    description="Coba ubah filter pencarian atau cek kembali nanti"
                />
                <div v-if="hasActiveFilters()" class="flex justify-center">
                    <button
                        class="inline-flex items-center gap-1.5 rounded-xl bg-[#0C447C]/5 px-4 py-2 text-sm font-semibold text-[#0C447C] transition-colors hover:bg-[#0C447C]/10 dark:bg-[#85B7EB]/10 dark:text-[#85B7EB]"
                        @click="clearFilters"
                        aria-label="Hapus semua filter"
                    >
                        <X class="h-4 w-4" />
                        Hapus semua filter
                    </button>
                </div>
            </div>

            <!-- Pagination -->
            <TPagination v-if="jobs.data.length > 0" :links="jobs.links" />
        </div>
    </TraceAlumniLayout>
</template>
