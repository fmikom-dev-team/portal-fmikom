<script setup lang="ts">
import { Head, Link, usePage } from "@inertiajs/vue3";
import TraceAlumniLayout from "@/layouts/TraceAlumniLayout.vue";
import type { PageProps } from "@inertiajs/core";
import type { BreadcrumbItem } from "@/types";
import {
    UserIcon,
    BriefcaseIcon,
    ClipboardListIcon,
    CheckCircle2,
    AlertCircle,
    TrendingUp,
    ArrowRight,
    GraduationCap,
    Clock,
    CalendarCheck,
    FileText,
    Circle,
    LayoutDashboard,
} from "lucide-vue-next";
import { computed } from "vue";
import { TPageHeader, TStatCard } from '@/components/trace';

interface CompletenessItem {
    label: string;
    done: boolean;
}

interface ProfileCompleteness {
    items: CompletenessItem[];
    percentage: number;
}

interface Stats {
    hasProfile: boolean;
    completeness: number;
    currentStatus: string;
    totalCareers: number;
    yearsOfExperience: string;
}

const props = defineProps<{
    moduleName: string;
    roleName: string;
    stats: Stats;
    hasFilledKuesioner: boolean;
    profileCompleteness: ProfileCompleteness;
    appliedJobsCount: number;
    upcomingEventsCount: number;
    pendingKuesionersCount: number;
}>();

const page = usePage<PageProps & { auth: { user: any } }>();
const user = computed(() => page.props.auth?.user);

const breadcrumbItems: BreadcrumbItem[] = [
    { title: "Dashboard", href: "/trace" },
];

const statusLabel: Record<string, string> = {
    bekerja: "Bekerja",
    wirausaha: "Wirausaha",
    lanjut_studi: "Lanjut Studi",
    mencari_kerja: "Mencari Kerja",
};

const statusColor: Record<string, string> = {
    bekerja:
        "text-[#0C447C] bg-[#0C447C]/5 dark:bg-[#85B7EB]/10 dark:text-[#85B7EB]",
    wirausaha:
        "text-[#EF9F27] bg-[#EF9F27]/5 dark:bg-[#FAC775]/10 dark:text-[#FAC775]",
    lanjut_studi:
        "text-[#0C447C] bg-[#0C447C]/5 dark:bg-[#85B7EB]/10 dark:text-[#85B7EB]",
    mencari_kerja:
        "text-amber-600 bg-amber-50 dark:bg-amber-950/30 dark:text-amber-400",
};

const completenessColor = computed(() => {
    if (props.profileCompleteness.percentage >= 80) return "bg-[#0C447C] dark:bg-[#85B7EB]";
    if (props.profileCompleteness.percentage >= 50) return "bg-amber-500";
    return "bg-red-400";
});

const completenessGradient = computed(() => {
    if (props.profileCompleteness.percentage >= 80) return "from-[#0C447C] to-[#85B7EB]";
    if (props.profileCompleteness.percentage >= 50) return "from-amber-500 to-orange-500";
    return "from-red-400 to-rose-500";
});

const quickActions = [
    {
        label: "Lengkapi Profil",
        desc: "Isi data diri dan domisili Anda",
        href: "/trace/profile-alumni",
        icon: UserIcon,
        color: "text-[#0C447C] bg-[#0C447C]/5 dark:text-[#85B7EB] dark:bg-[#85B7EB]/10",
    },
    {
        label: "Riwayat Karir",
        desc: "Kelola data pekerjaan Anda",
        href: "/trace/career",
        icon: BriefcaseIcon,
        color: "text-[#0C447C] bg-[#0C447C]/5 dark:text-[#85B7EB] dark:bg-[#85B7EB]/10",
    },
    {
        label: "Isi Kuesioner",
        desc: "Bantu kami meningkatkan kualitas kampus",
        href: "/trace/kuesioner",
        icon: ClipboardListIcon,
        color: "text-[#EF9F27] bg-[#EF9F27]/5 dark:text-[#FAC775] dark:bg-[#FAC775]/10",
    },
];
</script>

<template>
    <TraceAlumniLayout
        title="Dashboard"
        :breadcrumbs="breadcrumbItems"
        :role-name="roleName"
        :module-name="moduleName"
    >
        <div class="mx-auto space-y-6">
            <!-- Page Header -->
            <TPageHeader
                title="Dashboard"
                description="Selamat datang kembali — pantau perkembangan karir alumni FMIKOM"
                :icon="LayoutDashboard"
            />

            <!-- Welcome Banner -->
            <div
                class="rounded-2xl bg-gradient-to-br from-[#0C447C] to-[#0C447C]/80 p-6 text-white shadow-md shadow-[#0C447C]/20"
            >
                <p class="text-sm text-[#85B7EB]/80 mb-1">
                    Selamat datang kembali
                </p>
                <h2 class="text-2xl font-bold mb-1">
                    Halo, {{ user?.name?.split(" ")[0] }}! 👋
                </h2>
                <p class="text-sm text-[#85B7EB]/70">
                    Kembangkan jaringan profesional Anda dan pantau perkembangan
                    karir alumni FMIKOM.
                </p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <Link
                        href="/trace/career"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-white/15 hover:bg-white/25 px-4 py-2 text-sm font-semibold transition-colors backdrop-blur-sm"
                    >
                        <BriefcaseIcon class="h-4 w-4" />
                        Lihat Karir
                    </Link>
                    <Link
                        href="/trace/profile-alumni"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-white/15 hover:bg-white/25 px-4 py-2 text-sm font-semibold transition-colors backdrop-blur-sm"
                    >
                        <UserIcon class="h-4 w-4" />
                        Profil Saya
                    </Link>
                </div>
            </div>

            <!-- Profile Completeness Card -->
            <div
                class="rounded-2xl border border-slate-100 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900 shadow-sm"
            >
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br"
                            :class="completenessGradient"
                        >
                            <UserIcon class="h-5 w-5 text-white" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                Kelengkapan Profil
                            </h3>
                            <p class="text-xs text-slate-400 dark:text-slate-500">
                                {{ profileCompleteness.percentage }}% selesai
                            </p>
                        </div>
                    </div>
                    <span
                        class="text-2xl font-black tabular-nums"
                        :class="{
                            'text-[#0C447C] dark:text-[#85B7EB]': profileCompleteness.percentage >= 80,
                            'text-amber-600 dark:text-amber-400': profileCompleteness.percentage >= 50 && profileCompleteness.percentage < 80,
                            'text-red-500 dark:text-red-400': profileCompleteness.percentage < 50,
                        }"
                    >
                        {{ profileCompleteness.percentage }}%
                    </span>
                </div>

                <!-- Progress Bar -->
                <div class="h-2 w-full rounded-full bg-slate-100 dark:bg-zinc-800 mb-4 overflow-hidden">
                    <div
                        class="h-2 rounded-full bg-gradient-to-r transition-all duration-700 ease-out"
                        :class="completenessGradient"
                        :style="{ width: profileCompleteness.percentage + '%' }"
                    />
                </div>

                <!-- Checklist Items -->
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    <div
                        v-for="item in profileCompleteness.items"
                        :key="item.label"
                        class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm transition-colors"
                        :class="item.done
                            ? 'bg-[#0C447C]/5 dark:bg-[#85B7EB]/10'
                            : 'bg-slate-50 dark:bg-zinc-800/50'"
                    >
                        <CheckCircle2
                            v-if="item.done"
                            class="h-4 w-4 text-[#0C447C] dark:text-[#85B7EB] shrink-0"
                        />
                        <Circle
                            v-else
                            class="h-4 w-4 text-slate-300 dark:text-zinc-600 shrink-0"
                        />
                        <span
                            class="truncate text-xs font-medium"
                            :class="item.done
                                ? 'text-[#0C447C] dark:text-[#85B7EB]'
                                : 'text-slate-500 dark:text-slate-400'"
                        >
                            {{ item.label }}
                        </span>
                    </div>
                </div>

                <!-- CTA if not complete -->
                <div
                    v-if="profileCompleteness.percentage < 100"
                    class="mt-4 flex items-center justify-between rounded-xl bg-gradient-to-r from-[#0C447C]/5 to-[#85B7EB]/10 dark:from-[#0C447C]/20 dark:to-[#85B7EB]/10 px-4 py-3 border border-[#85B7EB]/20 dark:border-[#85B7EB]/15"
                >
                    <div class="flex items-center gap-2">
                        <AlertCircle class="h-4 w-4 text-[#0C447C] dark:text-[#85B7EB]" />
                        <span class="text-xs font-semibold text-[#0C447C] dark:text-[#85B7EB]">
                            Lengkapi profil Anda untuk meningkatkan visibilitas
                        </span>
                    </div>
                    <Link
                        href="/trace/profile-alumni"
                        class="inline-flex items-center gap-1 text-xs font-bold text-[#0C447C] dark:text-[#85B7EB] hover:underline"
                    >
                        Lengkapi <ArrowRight class="h-3 w-3" />
                    </Link>
                </div>
            </div>

            <!-- Quick Stats Row -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <TStatCard
                    label="Lamaran Kerja"
                    :value="appliedJobsCount"
                    :icon="BriefcaseIcon"
                    color="primary"
                    trend-label="pekerjaan dilamar"
                />
                <TStatCard
                    label="Event Mendatang"
                    :value="upcomingEventsCount"
                    :icon="CalendarCheck"
                    color="primary"
                    trend-label="event terdaftar"
                />
                <TStatCard
                    label="Kuesioner Pending"
                    :value="pendingKuesionersCount"
                    :icon="FileText"
                    color="accent"
                    trend-label="belum diisi"
                />
            </div>

            <!-- Alert: Kuesioner belum diisi -->
            <div
                v-if="!hasFilledKuesioner"
                class="flex items-start gap-3 rounded-xl border border-[#85B7EB]/30 bg-[#0C447C]/5 p-4 dark:border-[#85B7EB]/20 dark:bg-[#0C447C]/20"
            >
                <ClipboardListIcon
                    class="h-5 w-5 text-[#0C447C] dark:text-[#85B7EB] shrink-0 mt-0.5"
                />
                <div class="flex-1 min-w-0">
                    <p
                        class="text-sm font-semibold text-[#0C447C] dark:text-[#85B7EB]"
                    >
                        Kuesioner tracer study belum diisi
                    </p>
                    <p class="text-xs text-[#0C447C]/70 dark:text-[#85B7EB]/70 mt-0.5">
                        Bantu kami meningkatkan kualitas pendidikan dengan
                        mengisi kuesioner.
                    </p>
                </div>
                <Link
                    href="/trace/kuesioner"
                    class="shrink-0 text-xs font-semibold text-[#0C447C] dark:text-[#85B7EB] hover:underline flex items-center gap-1"
                >
                    Isi sekarang <ArrowRight class="h-3 w-3" />
                </Link>
            </div>

            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <!-- Kelengkapan Profil -->
                <div
                    class="rounded-2xl border border-slate-100 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <p class="text-xs text-slate-400 dark:text-slate-500 mb-1">
                        Kelengkapan Profil
                    </p>
                    <p
                        class="text-2xl font-black text-slate-900 dark:text-white mb-2"
                    >
                        {{ profileCompleteness.percentage }}%
                    </p>
                    <div
                        class="h-1.5 w-full rounded-full bg-slate-100 dark:bg-zinc-800"
                    >
                        <div
                            class="h-1.5 rounded-full transition-all duration-500"
                            :class="completenessColor"
                            :style="{ width: profileCompleteness.percentage + '%' }"
                        />
                    </div>
                </div>

                <!-- Status Karir -->
                <div
                    class="rounded-2xl border border-slate-100 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <p class="text-xs text-slate-400 dark:text-slate-500 mb-2">
                        Status Karir
                    </p>
                    <span
                        class="inline-block rounded-lg px-2.5 py-1 text-xs font-bold"
                        :class="
                            statusColor[stats.currentStatus] ??
                            statusColor.mencari_kerja
                        "
                    >
                        {{
                            statusLabel[stats.currentStatus] ??
                            "Tidak Diketahui"
                        }}
                    </span>
                </div>

                <!-- Total Pekerjaan -->
                <div
                    class="rounded-2xl border border-slate-100 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <p class="text-xs text-slate-400 dark:text-slate-500 mb-1">
                        Total Pekerjaan
                    </p>
                    <p
                        class="text-2xl font-black text-slate-900 dark:text-white"
                    >
                        {{ stats.totalCareers }}
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        riwayat tercatat
                    </p>
                </div>

                <!-- Kuesioner -->
                <div
                    class="rounded-2xl border border-slate-100 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <p class="text-xs text-slate-400 dark:text-slate-500 mb-2">
                        Kuesioner
                    </p>
                    <div class="flex items-center gap-1.5">
                        <CheckCircle2
                            class="h-5 w-5"
                            :class="
                                hasFilledKuesioner
                                    ? 'text-[#0C447C] dark:text-[#85B7EB]'
                                    : 'text-slate-300 dark:text-zinc-600'
                            "
                        />
                        <span
                            class="text-sm font-semibold"
                            :class="
                                hasFilledKuesioner
                                    ? 'text-[#0C447C] dark:text-[#85B7EB]'
                                    : 'text-slate-400'
                            "
                        >
                            {{
                                hasFilledKuesioner
                                    ? "Sudah diisi"
                                    : "Belum diisi"
                            }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div>
                <h3
                    class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3"
                >
                    Akses Cepat
                </h3>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <Link
                        v-for="action in quickActions"
                        :key="action.href"
                        :href="action.href"
                        class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-4 hover:border-[#85B7EB]/40 hover:shadow-sm transition-all dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-[#85B7EB]/30 group"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                            :class="action.color"
                        >
                            <component :is="action.icon" class="h-5 w-5" />
                        </div>
                        <div class="min-w-0">
                            <p
                                class="text-sm font-semibold text-slate-900 dark:text-white"
                            >
                                {{ action.label }}
                            </p>
                            <p class="text-xs text-slate-400 truncate">
                                {{ action.desc }}
                            </p>
                        </div>
                        <ArrowRight
                            class="h-4 w-4 text-slate-300 dark:text-zinc-600 ml-auto shrink-0 group-hover:text-[#0C447C] dark:group-hover:text-[#85B7EB] transition-colors"
                        />
                    </Link>
                </div>
            </div>
        </div>
    </TraceAlumniLayout>
</template>
