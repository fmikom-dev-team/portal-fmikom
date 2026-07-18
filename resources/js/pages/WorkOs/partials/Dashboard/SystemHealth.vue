<script setup lang="ts">
import { computed } from "vue";
import DashboardCard from "../../components/ui/DashboardCard.vue";

const props = defineProps<{
    stats: Record<string, any>;
}>();

// Services config for System Health
const services = computed(() => props.stats?.system_health?.services ?? []);
const avgUptime = computed(() => props.stats?.system_health?.avg_uptime ?? "0.00%");

// Security Center variables
const securityCenter = computed(() => {
    const sc = props.stats?.security_center ?? {
        failed_login_today: 0,
        blocked_ips: 0,
        mfa_adoption_rate: 0,
        password_reset_request: 0
    };

    // Determine colors/status
    // Green = aman, Yellow = perlu perhatian, Red = masalah
    const failedVal = Number(sc.failed_login_today);
    const failedStatus = failedVal > 50 ? "red" : (failedVal > 20 ? "yellow" : "green");

    const blockedVal = Number(sc.blocked_ips);
    const blockedStatus = blockedVal > 15 ? "red" : (blockedVal > 5 ? "yellow" : "green");

    const mfaVal = Number(sc.mfa_adoption_rate);
    const mfaStatus = mfaVal < 50 ? "red" : (mfaVal < 80 ? "yellow" : "green");

    const resetVal = Number(sc.password_reset_request);
    const resetStatus = resetVal > 30 ? "red" : (resetVal > 15 ? "yellow" : "green");

    return [
        {
            label: "Failed Login Today",
            value: failedVal,
            status: failedStatus,
            desc: failedStatus === "green" ? "Normal" : (failedStatus === "yellow" ? "Tinggi" : "Kritis"),
            unit: "Percobaan"
        },
        {
            label: "Blocked IP Addresses",
            value: blockedVal,
            status: blockedStatus,
            desc: blockedStatus === "green" ? "Aman" : (blockedStatus === "yellow" ? "Perlu diawasi" : "Serangan aktif"),
            unit: "IP Terblokir"
        },
        {
            label: "MFA Adoption Rate",
            value: `${mfaVal}%`,
            status: mfaStatus,
            desc: mfaStatus === "green" ? "Bagus" : (mfaStatus === "yellow" ? "Perlu ditingkatkan" : "Rendah"),
            unit: "Pengguna aktif"
        },
        {
            label: "Password Reset Request",
            value: resetVal,
            status: resetStatus,
            desc: resetStatus === "green" ? "Normal" : (resetStatus === "yellow" ? "Meningkat" : "Mencurigakan"),
            unit: "Permintaan"
        }
    ];
});
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- System Health Monitoring -->
        <DashboardCard title="System Health" subtitle="Status layanan dan uptime infrastruktur WorkOS">
            <template #actions>
                <span class="inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 px-2 py-0.5 rounded text-[11px] font-semibold ring-1 ring-emerald-600/10 dark:ring-emerald-400/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Average {{ avgUptime }}
                </span>
            </template>

            <div class="space-y-4">
                <div v-for="service in services" :key="service.name" class="flex items-start justify-between gap-3 p-3 rounded-lg border border-[#f3f4f6] dark:border-zinc-800 hover:bg-[#f9fafb] dark:bg-zinc-900 dark:hover:bg-zinc-800/40 transition-colors">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span :class="[
                                'w-2 h-2 rounded-full shrink-0',
                                service.status === 'online' ? 'bg-emerald-500' : (service.status === 'warning' ? 'bg-amber-500' : 'bg-red-500')
                            ]"></span>
                            <span class="text-[13px] font-semibold text-[#111827] dark:text-zinc-100">{{ service.name }}</span>
                        </div>
                        <p class="text-[11px] text-[#6b7280] dark:text-zinc-500 mt-0.5 leading-snug">{{ service.description }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-[12px] font-bold text-[#111827] dark:text-zinc-100">{{ service.uptime }}</span>
                        <div class="text-[10px] text-[#9ca3af] dark:text-zinc-600 mt-0.5">Uptime</div>
                    </div>
                </div>
            </div>
        </DashboardCard>

        <!-- Security Center -->
        <DashboardCard title="Security Center" subtitle="Pemantauan integritas keamanan dan otentikasi">
            <template #actions>
                <span class="text-[11px] text-[#9ca3af] dark:text-zinc-600">Real-time Protection</span>
            </template>

            <div class="grid grid-cols-2 gap-3">
                <div v-for="item in securityCenter" :key="item.label" class="border border-[#e5e7eb] dark:border-zinc-800 rounded-xl p-3.5 flex flex-col justify-between hover:bg-slate-50 dark:hover:bg-zinc-800/40 transition-colors bg-white dark:bg-zinc-900">
                    <div>
                        <span class="text-[11px] font-medium text-[#6b7280] dark:text-zinc-400 block truncate">{{ item.label }}</span>
                        <span class="text-[24px] font-extrabold text-[#111827] dark:text-zinc-50 block mt-1 tracking-tight">{{ item.value }}</span>
                    </div>
                    <div class="flex items-center justify-between mt-3 pt-2 border-t border-[#f3f4f6] dark:border-zinc-800">
                        <span class="text-[10px] text-[#9ca3af] dark:text-zinc-600">{{ item.unit }}</span>
                        <span :class="[
                            'px-1.5 py-0.5 rounded text-[10px] font-bold uppercase shrink-0',
                            item.status === 'green' ? 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400' : 
                            (item.status === 'yellow' ? 'bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400' : 'bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400')
                        ]">{{ item.desc }}</span>
                    </div>
                </div>
            </div>
        </DashboardCard>
    </div>
</template>