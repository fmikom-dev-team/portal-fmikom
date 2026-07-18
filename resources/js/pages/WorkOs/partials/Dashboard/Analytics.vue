<script setup lang="ts">
import { computed } from "vue";
import VueApexCharts from "vue3-apexcharts";
import type { ApexOptions } from "apexcharts";
import { useAppearance } from "@/composables/useAppearance";
import DashboardCard from "../../components/ui/DashboardCard.vue";

const props = defineProps<{
    stats: Record<string, any>;
}>();

const { resolvedAppearance } = useAppearance();
const isDark = computed(() => resolvedAppearance.value === "dark");

// ── 1. Weekly Logins Chart ───────────────────────────────────────
const weeklyLoginsSeries = computed(() => {
    const dataList = props.stats?.weekly_logins ?? [];
    return [
        { name: "Login Sukses", data: dataList.map((d: any) => d.success ?? 0) },
        { name: "Login Gagal", data: dataList.map((d: any) => d.failed ?? 0) }
    ];
});

const weeklyLoginsOptions = computed((): ApexOptions => ({
    chart: {
        type: "line",
        height: 250,
        toolbar: { show: false },
        animations: { enabled: true, speed: 400 },
        fontFamily: "var(--wos-font), sans-serif",
    },
    colors: ["#2563EB", "#EF4444"],
    stroke: {
        curve: "smooth",
        width: 3,
    },
    markers: {
        size: 4,
        hover: { size: 6 }
    },
    fill: {
        type: "gradient",
        gradient: {
            shade: isDark.value ? "dark" : "light",
            type: "vertical",
            opacityFrom: [0.15, 0.05],
            opacityTo: [0.0, 0.0],
        }
    },
    dataLabels: { enabled: false },
    xaxis: {
        categories: (props.stats?.weekly_logins ?? []).map((d: any) => d.label ?? ""),
        labels: { style: { colors: isDark.value ? "#a1a1aa" : "#6b7280", fontSize: "11px" } },
        axisBorder: { show: false },
        axisTicks: { show: false },
    },
    yaxis: { 
        labels: { style: { colors: isDark.value ? "#a1a1aa" : "#6b7280", fontSize: "11px" } } 
    },
    grid: { 
        borderColor: isDark.value ? "#27272a" : "#f3f4f6", 
        strokeDashArray: 4 
    },
    legend: { 
        position: "top", 
        fontSize: "12px",
        labels: { colors: isDark.value ? "#e4e4e7" : "#374151" }
    },
    tooltip: { x: { show: true } },
}));

// ── 2. Hourly Logins Chart ───────────────────────────────────────
const hourlyLoginsSeries = computed(() => {
    const dataList = props.stats?.hourly_logins ?? [];
    return [
        { name: "Login Sukses", data: dataList.map((d: any) => d.success ?? 0) }
    ];
});

const hourlyLoginsOptions = computed((): ApexOptions => ({
    chart: {
        type: "bar",
        height: 250,
        toolbar: { show: false },
        animations: { enabled: true, speed: 400 },
        fontFamily: "var(--wos-font), sans-serif",
    },
    colors: ["#4F46E5"],
    plotOptions: {
        bar: {
            borderRadius: 4,
            columnWidth: "60%",
        }
    },
    dataLabels: { enabled: false },
    xaxis: {
        categories: (props.stats?.hourly_logins ?? []).map((d: any) => d.hour ?? ""),
        labels: { 
            style: { colors: isDark.value ? "#a1a1aa" : "#6b7280", fontSize: "10px" },
            hideOverlappingLabels: true
        },
        axisBorder: { show: false },
        axisTicks: { show: false },
    },
    yaxis: { 
        labels: { style: { colors: isDark.value ? "#a1a1aa" : "#6b7280", fontSize: "11px" } } 
    },
    grid: { 
        borderColor: isDark.value ? "#27272a" : "#f3f4f6", 
        strokeDashArray: 4 
    },
    tooltip: { x: { show: true } },
}));

// ── 3. Logins by Role Chart ──────────────────────────────────────
const roleLoginsSeries = computed(() => {
    const dataObj = props.stats?.role_logins ?? {};
    return Object.values(dataObj).map(Number);
});

const roleLoginsOptions = computed((): ApexOptions => {
    const dataObj = props.stats?.role_logins ?? {};
    return {
        chart: {
            type: "donut",
            height: 230,
            fontFamily: "var(--wos-font), sans-serif",
        },
        labels: Object.keys(dataObj).map((k: string) => {
            const labelsMap: Record<string, string> = {
                mahasiswa: "Mahasiswa",
                dosen: "Dosen",
                staff: "Staff",
                super_admin: "Super Admin",
            };
            return labelsMap[k] ?? k;
        }),
        colors: ["#3B82F6", "#F59E0B", "#10B981", "#2563eb"],
        dataLabels: { enabled: false },
        legend: { 
            position: "bottom", 
            fontSize: "12px",
            labels: { colors: isDark.value ? "#e4e4e7" : "#374151" }
        },
        plotOptions: { 
            pie: { 
                donut: { 
                    size: "70%",
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: "Total Login",
                            formatter: (w) => {
                                return w.globals.seriesTotals.reduce((a: number, b: number) => a + b, 0).toLocaleString();
                            }
                        }
                    }
                } 
            } 
        },
        tooltip: { y: { formatter: (v: number) => `${v} Login` } },
    };
});

// ── 4. User Prodi Distribution Chart ─────────────────────────────
const prodiSeries = computed(() => {
    const dataObj = props.stats?.prodi_distribution ?? {};
    return Object.values(dataObj).map(Number);
});

const prodiOptions = computed((): ApexOptions => {
    const dataObj = props.stats?.prodi_distribution ?? {};
    return {
        chart: {
            type: "donut",
            height: 230,
            fontFamily: "var(--wos-font), sans-serif",
        },
        labels: Object.keys(dataObj),
        colors: ["#EC4899", "#8B5CF6", "#06B6D4", "#10B981"],
        dataLabels: { enabled: false },
        legend: { 
            position: "bottom", 
            fontSize: "11px",
            labels: { colors: isDark.value ? "#e4e4e7" : "#374151" }
        },
        plotOptions: { 
            pie: { 
                donut: { 
                    size: "70%",
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: "Total Mhs",
                            formatter: (w) => {
                                return w.globals.seriesTotals.reduce((a: number, b: number) => a + b, 0).toLocaleString();
                            }
                        }
                    }
                } 
            } 
        },
        tooltip: { y: { formatter: (v: number) => `${v} Mahasiswa` } },
    };
});
</script>

<template>
    <div class="space-y-6">
        <!-- Main Login Trends -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <DashboardCard title="Login 7 Hari Terakhir" subtitle="Statistik login mingguan (sukses vs gagal)">
                <div class="min-h-[250px] flex items-center justify-center">
                    <VueApexCharts 
                        type="line" 
                        :options="weeklyLoginsOptions" 
                        :series="weeklyLoginsSeries" 
                        class="w-full"
                        height="250"
                    />
                </div>
            </DashboardCard>

            <DashboardCard title="Aktivitas Login per Jam" subtitle="Aktivitas login sepanjang hari ini">
                <div class="min-h-[250px] flex items-center justify-center">
                    <VueApexCharts 
                        type="bar" 
                        :options="hourlyLoginsOptions" 
                        :series="hourlyLoginsSeries" 
                        class="w-full"
                        height="250"
                    />
                </div>
            </DashboardCard>
        </div>

        <!-- Distributions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <DashboardCard title="Login berdasarkan Role" subtitle="Proporsi aktivitas login per tipe pengguna">
                <div class="min-h-[250px] flex items-center justify-center">
                    <VueApexCharts 
                        v-if="roleLoginsSeries.length > 0 && roleLoginsSeries.some(v => v > 0)"
                        type="donut" 
                        :options="roleLoginsOptions" 
                        :series="roleLoginsSeries" 
                        class="w-full"
                        height="230"
                    />
                    <div v-else class="text-[13px] text-gray-400 dark:text-zinc-500 text-center py-12">
                        Belum ada aktivitas login yang tercatat.
                    </div>
                </div>
            </DashboardCard>

            <DashboardCard title="Distribusi Program Studi" subtitle="Distribusi mahasiswa aktif per program studi">
                <div class="min-h-[250px] flex items-center justify-center">
                    <VueApexCharts 
                        v-if="prodiSeries.length > 0 && prodiSeries.some(v => v > 0)"
                        type="donut" 
                        :options="prodiOptions" 
                        :series="prodiSeries" 
                        class="w-full"
                        height="230"
                    />
                    <div v-else class="text-[13px] text-gray-400 dark:text-zinc-500 text-center py-12">
                        Belum ada data program studi mahasiswa yang aktif.
                    </div>
                </div>
            </DashboardCard>
        </div>
    </div>
</template>