<script setup lang="ts">
import ChartCard from "@/components/Admin/ChartCard.vue";
import StatsCard from "@/components/Admin/StatsCard.vue";
import PagiAdminLayout from "@/layouts/PagiAdminLayout.vue";

const statsCards = [
	{
		title: "Total Kunjungan",
		value: 48200,
		change: "+22.4%",
		trend: "up" as const,
		iconColor: "bg-indigo-500",
		icon: `<svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>`,
	},
	{
		title: "Pengguna Unik",
		value: 3840,
		change: "+15.7%",
		trend: "up" as const,
		iconColor: "bg-emerald-500",
		icon: `<svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>`,
	},
	{
		title: "Rata-rata Waktu",
		value: "4m 32s",
		change: "+0.8%",
		trend: "up" as const,
		iconColor: "bg-blue-500",
		icon: `<svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`,
	},
	{
		title: "Bounce Rate",
		value: "28.4%",
		change: "-4.2%",
		trend: "up" as const,
		iconColor: "bg-rose-500",
		icon: `<svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181" /></svg>`,
	},
];
</script>

<template>
    <PagiAdminLayout title="Analitik">
        <div class="mb-6">
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Analitik</h1>
            <p class="mt-0.5 text-[13px] text-slate-400 dark:text-zinc-500">Pantau performa platform PAGI secara real-time</p>
        </div>

        <div class="mb-5 grid grid-cols-2 lg:grid-cols-4 gap-3">
            <StatsCard
                v-for="card in statsCards"
                :key="card.title"
                :title="card.title"
                :value="card.value"
                :change="card.change"
                :trend="card.trend"
                :icon="card.icon"
                :icon-color="card.iconColor"
            />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <ChartCard title="Traffic Harian" />
            <ChartCard title="Aktivitas Pengguna" />
        </div>
    </PagiAdminLayout>
</template>
