<script setup lang="ts">
import { ref } from "vue";
import StatsCard from "../../components/ui/StatsCard.vue";
import DashboardCard from "../../components/ui/DashboardCard.vue";
import Analytics from "./Analytics.vue";
import SystemHealth from "./SystemHealth.vue";
import Activity from "./Activity.vue";

const props = defineProps<{
    stats: Record<string, any>;
}>();

const emit = defineEmits<{
    (e: "navigate", page: string): void;
    (e: "open-detail", user: any): void;
}>();

// Hero stats configuration
const heroStats = [
    {
        label: "Total User Aktif",
        key: "active_users",
        icon: "👥",
        sublabel: "Seluruh akun pengguna aktif di portal"
    },
    {
        label: "Login Hari Ini",
        key: "login_hari_ini",
        icon: "🔐",
        sublabel: "Aktivitas login hari ini"
    },
    {
        label: "Percobaan Login Gagal",
        key: "login_gagal_hari_ini",
        icon: "🚨",
        sublabel: "Aktivitas login mencurigakan"
    },
    {
        label: "Organisasi Terhubung",
        key: "organisasi_terhubung",
        icon: "🏢",
        sublabel: "Fakultas / Unit terhubung"
    }
];

// Quick Actions Configuration
const quickActions = [
    {
        label: "Tambah User",
        desc: "Buat akun pengguna baru",
        icon: `<svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
        </svg>`,
        target: "users"
    },
    {
        label: "Tambah Organisasi",
        desc: "Tambah fakultas / unit baru",
        icon: `<svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>`,
        target: "organizations"
    },
    {
        label: "Reset Password",
        desc: "Kelola kata sandi akun",
        icon: `<svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m-2 4a2 2 0 012 2m-8-3a3 3 0 11.586-1.586l7 7A2 2 0 0114 16H8a2 2 0 01-2-2v-3.586l.586-.586z" />
        </svg>`,
        target: "users"
    },
    {
        label: "Kirim Undangan",
        desc: "Undang anggota via email",
        icon: `<svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>`,
        target: "emails"
    },
    {
        label: "Export Report",
        desc: "Unduh laporan aktivitas log",
        icon: `<svg class="w-5 h-5 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>`,
        target: "audit-logs"
    },
    {
        label: "Kelola Role",
        desc: "Atur hak akses dan role",
        icon: `<svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>`,
        target: "authorization"
    }
];
</script>

<template>
    <div class="w-full px-8 pt-8 pb-12 space-y-6" style="font-family: var(--wos-font)">
        <!-- Page title -->
        <div>
            <h1 class="text-[22px] font-semibold text-[#111827] tracking-tight">Overview</h1>
            <p class="text-[13px] text-gray-500 mt-1">Status portal, aktivitas login harian, dan metrik keamanan sistem.</p>
        </div>

        <!-- ── 1. HERO STATISTICS ── -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div
                v-for="stat in heroStats"
                :key="stat.key"
                class="bg-white rounded-xl p-5 border border-[#e5e7eb] hover:border-blue-300 hover:shadow-sm transition-all"
            >
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-semibold text-[#6B7280] uppercase tracking-wide truncate max-w-[130px]">{{ stat.label }}</span>
                    <span class="text-base select-none shrink-0">{{ stat.icon }}</span>
                </div>
                <div class="text-[26px] font-extrabold text-[#111827] tracking-tight leading-none mt-1">
                    {{ stat.valueOverride || (stats[stat.key] !== undefined ? stats[stat.key].toLocaleString() : 0) }}
                </div>
                <p class="text-[11px] text-[#9CA3AF] mt-2 truncate">{{ stat.sublabel }}</p>
            </div>
        </div>

        <!-- ── 2. QUICK ACTIONS ── -->
        <DashboardCard title="Quick Actions" subtitle="Pintasan cepat untuk administrasi unit, role, dan akun pengguna">
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3.5">
                <button
                    v-for="action in quickActions"
                    :key="action.label"
                    @click="emit('navigate', action.target)"
                    class="flex flex-col items-start p-4 rounded-xl border border-[#e5e7eb] hover:border-blue-300 hover:bg-slate-50/50 transition-all text-left group cursor-pointer"
                >
                    <div class="w-8 h-8 rounded-lg bg-slate-50 border border-gray-100 flex items-center justify-center group-hover:scale-105 transition-transform" v-html="action.icon"></div>
                    <span class="text-[12.5px] font-bold text-[#111827] mt-3 group-hover:text-blue-600 transition-colors">{{ action.label }}</span>
                    <span class="text-[10px] text-gray-400 mt-1 leading-snug">{{ action.desc }}</span>
                </button>
            </div>
        </DashboardCard>

        <!-- ── 3. LOGIN ANALYTICS & DISTRIBUTIONS ── -->
        <Analytics :stats="stats" />

        <!-- ── 4. SYSTEM HEALTH & SECURITY CENTER ── -->
        <SystemHealth :stats="stats" />

        <!-- ── 5. RECENT ACTIVITIES & AUDIT SUMMARY ── -->
        <Activity :stats="stats" @navigate="page => emit('navigate', page)" />
    </div>
</template>
