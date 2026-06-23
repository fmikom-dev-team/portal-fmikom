<script setup lang="ts">
import { Briefcase, Building2, Award, Clock } from "lucide-vue-next";

interface Stats {
    totalCareers: number;
    totalCompanies: number;
    currentStatus: string;
    yearsOfExperience: string;
}

defineProps<{
    stats: Stats;
}>();

const statusLabels: Record<string, string> = {
    bekerja: "Bekerja",
    wirausaha: "Wirausaha",
    mencari_kerja: "Mencari Kerja",
    lanjut_studi: "Lanjut Studi",
};

const statusColors: Record<string, string> = {
    bekerja: "green",
    wirausaha: "blue",
    mencari_kerja: "orange",
    lanjut_studi: "purple",
};
</script>

<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Careers -->
        <div
            class="rounded-xl border border-sidebar-border/70 bg-white p-5 dark:border-sidebar-border dark:bg-slate-900"
        >
            <div class="flex items-center justify-between">
                <div>
                    <p
                        class="text-sm font-medium text-gray-600 dark:text-gray-400"
                    >
                        Total Riwayat
                    </p>
                    <p
                        class="mt-2 text-3xl font-bold text-gray-900 dark:text-white"
                    >
                        {{ stats.totalCareers }}
                    </p>
                </div>
                <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900/30">
                    <Briefcase
                        class="h-6 w-6 text-blue-600 dark:text-blue-400"
                    />
                </div>
            </div>
        </div>

        <!-- Total Companies -->
        <div
            class="rounded-xl border border-sidebar-border/70 bg-white p-5 dark:border-sidebar-border dark:bg-slate-900"
        >
            <div class="flex items-center justify-between">
                <div>
                    <p
                        class="text-sm font-medium text-gray-600 dark:text-gray-400"
                    >
                        Perusahaan
                    </p>
                    <p
                        class="mt-2 text-3xl font-bold text-gray-900 dark:text-white"
                    >
                        {{ stats.totalCompanies }}
                    </p>
                </div>
                <div
                    class="rounded-full bg-purple-100 p-3 dark:bg-purple-900/30"
                >
                    <Building2
                        class="h-6 w-6 text-purple-600 dark:text-purple-400"
                    />
                </div>
            </div>
        </div>

        <!-- Current Status -->
        <div
            class="rounded-xl border border-sidebar-border/70 bg-white p-5 dark:border-sidebar-border dark:bg-slate-900"
        >
            <div class="flex items-center justify-between">
                <div>
                    <p
                        class="text-sm font-medium text-gray-600 dark:text-gray-400"
                    >
                        Status Saat Ini
                    </p>
                    <p
                        class="mt-2 text-lg font-bold text-gray-900 dark:text-white"
                    >
                        {{
                            statusLabels[stats.currentStatus] || "Belum Diatur"
                        }}
                    </p>
                </div>
                <div
                    :class="`bg-${statusColors[stats.currentStatus] || 'gray'}-100 dark:bg-${statusColors[stats.currentStatus] || 'gray'}-900/30`"
                    class="rounded-full p-3"
                >
                    <Award
                        :class="`text-${statusColors[stats.currentStatus] || 'gray'}-600 dark:text-${statusColors[stats.currentStatus] || 'gray'}-400`"
                        class="h-6 w-6"
                    />
                </div>
            </div>
        </div>

        <!-- Years of Experience -->
        <div
            class="rounded-xl border border-sidebar-border/70 bg-white p-5 dark:border-sidebar-border dark:bg-slate-900"
        >
            <div class="flex items-center justify-between">
                <div>
                    <p
                        class="text-sm font-medium text-gray-600 dark:text-gray-400"
                    >
                        Pengalaman
                    </p>
                    <p
                        class="mt-2 text-lg font-bold text-gray-900 dark:text-white"
                    >
                        {{ stats.yearsOfExperience }}
                    </p>
                </div>
                <div class="rounded-full bg-green-100 p-3 dark:bg-green-900/30">
                    <Clock class="h-6 w-6 text-green-600 dark:text-green-400" />
                </div>
            </div>
        </div>
    </div>
</template>
