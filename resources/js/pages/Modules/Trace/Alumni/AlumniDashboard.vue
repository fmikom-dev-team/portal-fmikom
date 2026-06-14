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
} from "lucide-vue-next";
import { computed } from "vue";

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
        "text-green-600 bg-green-50 dark:bg-green-950/30 dark:text-green-400",
    wirausaha:
        "text-teal-600 bg-teal-50 dark:bg-teal-950/30 dark:text-teal-400",
    lanjut_studi:
        "text-blue-600 bg-blue-50 dark:bg-blue-950/30 dark:text-blue-400",
    mencari_kerja:
        "text-amber-600 bg-amber-50 dark:bg-amber-950/30 dark:text-amber-400",
};

const completenessColor = computed(() => {
    if (props.stats.completeness >= 80) return "bg-green-500";
    if (props.stats.completeness >= 50) return "bg-amber-500";
    return "bg-red-400";
});

const quickActions = [
    {
        label: "Lengkapi Profil",
        desc: "Isi data diri dan domisili Anda",
        href: "/trace/profile-alumni",
        icon: UserIcon,
        color: "text-green-600 bg-green-50 dark:bg-green-950/30",
    },
    {
        label: "Riwayat Karir",
        desc: "Kelola data pekerjaan Anda",
        href: "/trace/career",
        icon: BriefcaseIcon,
        color: "text-emerald-600 bg-emerald-50 dark:bg-emerald-950/30",
    },
    {
        label: "Isi Kuesioner",
        desc: "Bantu kami meningkatkan kualitas kampus",
        href: "/trace/kuesioner",
        icon: ClipboardListIcon,
        color: "text-teal-600 bg-teal-50 dark:bg-teal-950/30",
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
            <!-- Welcome Banner -->
            <div
                class="rounded-2xl bg-gradient-to-br from-green-600 to-emerald-700 p-6 text-white shadow-md shadow-green-500/20"
            >
                <p class="text-sm text-green-100/80 mb-1">
                    Selamat datang kembali
                </p>
                <h2 class="text-2xl font-bold mb-1">
                    Halo, {{ user?.name?.split(" ")[0] }}! 👋
                </h2>
                <p class="text-sm text-green-100/70">
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

            <!-- Alert: Profile belum lengkap -->
            <div
                v-if="stats.completeness < 80"
                class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/40 dark:bg-amber-950/20"
            >
                <AlertCircle class="h-5 w-5 text-amber-500 shrink-0 mt-0.5" />
                <div class="flex-1 min-w-0">
                    <p
                        class="text-sm font-semibold text-amber-800 dark:text-amber-300"
                    >
                        Profil Anda belum lengkap ({{ stats.completeness }}%)
                    </p>
                    <p
                        class="text-xs text-amber-600 dark:text-amber-400 mt-0.5"
                    >
                        Lengkapi profil untuk meningkatkan visibilitas Anda di
                        jaringan alumni.
                    </p>
                </div>
                <Link
                    href="/trace/profile-alumni"
                    class="shrink-0 text-xs font-semibold text-amber-700 dark:text-amber-400 hover:underline flex items-center gap-1"
                >
                    Lengkapi <ArrowRight class="h-3 w-3" />
                </Link>
            </div>

            <!-- Alert: Kuesioner belum diisi -->
            <div
                v-if="!hasFilledKuesioner"
                class="flex items-start gap-3 rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-900/40 dark:bg-blue-950/20"
            >
                <ClipboardListIcon
                    class="h-5 w-5 text-blue-500 shrink-0 mt-0.5"
                />
                <div class="flex-1 min-w-0">
                    <p
                        class="text-sm font-semibold text-blue-800 dark:text-blue-300"
                    >
                        Kuesioner tracer study belum diisi
                    </p>
                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-0.5">
                        Bantu kami meningkatkan kualitas pendidikan dengan
                        mengisi kuesioner.
                    </p>
                </div>
                <Link
                    href="/trace/kuesioner"
                    class="shrink-0 text-xs font-semibold text-blue-700 dark:text-blue-400 hover:underline flex items-center gap-1"
                >
                    Isi sekarang <ArrowRight class="h-3 w-3" />
                </Link>
            </div>

            <!-- Stats Cards -->
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
                        {{ stats.completeness }}%
                    </p>
                    <div
                        class="h-1.5 w-full rounded-full bg-slate-100 dark:bg-zinc-800"
                    >
                        <div
                            class="h-1.5 rounded-full transition-all duration-500"
                            :class="completenessColor"
                            :style="{ width: stats.completeness + '%' }"
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
                                    ? 'text-green-500'
                                    : 'text-slate-300 dark:text-zinc-600'
                            "
                        />
                        <span
                            class="text-sm font-semibold"
                            :class="
                                hasFilledKuesioner
                                    ? 'text-green-600 dark:text-green-400'
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
                        class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-4 hover:border-green-200 hover:shadow-sm transition-all dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-green-900/40 group"
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
                            class="h-4 w-4 text-slate-300 dark:text-zinc-600 ml-auto shrink-0 group-hover:text-green-500 transition-colors"
                        />
                    </Link>
                </div>
            </div>
        </div>
    </TraceAlumniLayout>
</template>
