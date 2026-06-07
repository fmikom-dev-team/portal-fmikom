<script setup lang="ts">
import { ref } from "vue";
import EmptyState from "../../components/ui/EmptyState.vue";
import { formatRelativeTime, statusDot } from "../../composables/useWorkOs";

const props = defineProps<{
	stats: Record<string, number>;
}>();

const emit = defineEmits<{
	(e: "navigate", page: string): void;
	(e: "open-detail", user: any): void;
}>();

// ── Stat product cards — matches WorkOS reference exactly ──────────
const productCards = [
	{
		label: "AuthKit",
		sublabel: "Active users in last 7 days",
		key: "active_users",
		footerKey: "total_users",
		footerLabel: "total users",
		hasSparkline: true,
	},
	{
		label: "Single Sign-On",
		sublabel: "Active connections",
		key: "total_modules",
		footerKey: null,
		footerLabel: null,
		hasSparkline: false,
	},
	{
		label: "Directory Sync",
		sublabel: "Active directories",
		key: "total_roles",
		footerKey: null,
		footerLabel: null,
		hasSparkline: false,
	},
	{
		label: "Audit Logs",
		sublabel: "Events defined",
		key: "total_logs",
		footerKey: null,
		footerLabel: null,
		hasSparkline: false,
	},
];

// ── Quick start docs — coloured app icons ──────────────────────────
const quickstartDocs = [
	{
		label: "AuthKit",
		desc: "Full-fledged auth platform",
		bg: "#2563EB",
		letter: "A",
	},
	{
		label: "Single Sign-On",
		desc: "Integrate with any IdP",
		bg: "#111827",
		letter: "S",
	},
	{
		label: "Directory Sync",
		desc: "Manage user directories",
		bg: "#7c3aed",
		letter: "D",
	},
	{
		label: "Audit Logs",
		desc: "Ingest and export events",
		bg: "#ea580c",
		letter: "L",
	},
];

// ── Explore docs — flat layout, icon only ─────────────────────────
const exploreDocs = [
	{
		label: "Documentation",
		desc: "How to integrate all WorkOS products into your app",
		icon: "M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z",
	},
	{
		label: "API Reference",
		desc: "Every WorkOS method and endpoint documented",
		icon: "M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4",
	},
	{
		label: "Third-party Integrations",
		desc: "Guides on using WorkOS with third-party services",
		icon: "M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z",
	},
	{
		label: "SDKs",
		desc: "Official open-source client libraries for your dev platform",
		icon: "M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z",
	},
];

// ── Copy env vars ──────────────────────────────────────────────────
const copied = ref<string | null>(null);
const envVars = [
	{ key: "WORKOS_CLIENT_ID", val: "client_01KMQ8PK25P7DC3MYXF5CTD00N" },
	{
		key: "WORKOS_API_KEY",
		val: "sk_test_a2V5XzAxS81R0FBNMVpXUENKT1JWRVpRVE5HNUh...",
	},
];

function copyEnvVar(val: string, key: string) {
	navigator.clipboard?.writeText(val).catch(() => {});
	copied.value = key;
	setTimeout(() => {
		copied.value = null;
	}, 1800);
}
</script>

<template>
    <div class="w-full px-8 pt-8 pb-12" style="font-family: var(--wos-font)">

        <!-- ── Page title ── -->
        <h1 class="text-[22px] font-semibold text-[#111827] tracking-tight mb-6">Overview</h1>

        <!-- ════════════════════════════════════════════════════════
             STAT CARDS — 4 columns, matching WorkOS style exactly
        ════════════════════════════════════════════════════════ -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-9">
            <div
                v-for="card in productCards"
                :key="card.key"
                class="bg-white rounded-xl p-5 ring-1 ring-gray-900/[0.04] hover:ring-gray-900/[0.08] transition-all relative overflow-hidden"
            >
                <!-- Label -->
                <p class="text-[13px] font-semibold text-[#374151] mb-3">{{ card.label }}</p>

                <!-- Sparkline (AuthKit only — decorative area chart) -->
                <div v-if="card.hasSparkline" class="absolute top-4 right-4 opacity-20">
                    <svg width="64" height="36" viewBox="0 0 64 36" fill="none">
                        <path d="M0 28 C8 22 16 8 24 10 C32 12 40 26 48 20 C54 16 60 8 64 6" stroke="#6b7280" stroke-width="1.5" fill="none"/>
                        <path d="M0 28 C8 22 16 8 24 10 C32 12 40 26 48 20 C54 16 60 8 64 6 L64 36 L0 36 Z" fill="#6b7280"/>
                    </svg>
                </div>

                <!-- Big number -->
                <p class="text-[32px] font-bold text-[#111827] leading-none mb-1.5 tabular-nums">
                    {{ stats[card.key] ?? 0 }}
                </p>

                <!-- Sub label -->
                <p class="text-[12px] text-[#6b7280]">{{ card.sublabel }}</p>

                <!-- Footer (AuthKit only) -->
                <div v-if="card.footerKey" class="mt-4 pt-3 border-t border-[#f3f4f6]">
                    <p class="text-[12px] text-[#6b7280]">
                        {{ stats[card.footerKey] ?? 0 }} {{ card.footerLabel }}
                    </p>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════
             QUICK START — 2 columns
        ════════════════════════════════════════════════════════ -->
        <section class="mb-9">
            <h2 class="text-[15px] font-semibold text-[#111827] mb-4">Quick start</h2>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">

                <!-- Left: Copy environment variables -->
                <div class="bg-white border border-[#e5e7eb] rounded-xl p-5">
                    <div class="flex items-center gap-2 mb-2">
                        <!-- Gear icon -->
                        <svg class="w-3.5 h-3.5 text-[#9ca3af] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <h3 class="text-[13px] font-semibold text-[#111827]">Copy environment variables</h3>
                    </div>
                    <p class="text-[12px] text-[#6b7280] mb-4 leading-relaxed">
                        Configure and test your WorkOS integration by enabling products and completing setup here in staging.
                    </p>
                    <!-- Env var rows -->
                    <div class="space-y-1.5">
                        <div
                            v-for="env in envVars"
                            :key="env.key"
                            class="flex items-center gap-2 bg-[#f9fafb] border border-[#e5e7eb] rounded-lg px-3 py-2"
                        >
                            <span class="text-[10.5px] font-semibold text-[#374151] wos-code shrink-0 w-[130px]">{{ env.key }}</span>
                            <span class="flex-1 text-[10.5px] text-[#6b7280] wos-code truncate">{{ env.val }}</span>
                            <button
                                class="shrink-0 text-[#9ca3af] hover:text-[#374151] transition-colors p-0.5 rounded"
                                :aria-label="`Copy ${env.key}`"
                                @click="copyEnvVar(env.val, env.key)"
                            >
                                <svg v-if="copied === env.key" class="w-3.5 h-3.5 text-[#10b981]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right: Quick start docs -->
                <div class="bg-white border border-[#e5e7eb] rounded-xl p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-[13px] font-semibold text-[#111827]">Quick start docs</h3>
                        <a
                            href="#"
                            class="flex items-center gap-1 text-[12px] font-medium transition-colors"
                            style="color: #2563EB"
                        >
                            View all
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>
                    <!-- 2×2 grid of doc items -->
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            v-for="doc in quickstartDocs"
                            :key="doc.label"
                            class="flex items-center gap-2.5 p-2.5 rounded-lg hover:bg-[#f9fafb] transition-colors text-left"
                        >
                            <!-- Coloured app icon -->
                            <div
                                class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-[12px] font-bold shrink-0"
                                :style="{ backgroundColor: doc.bg }"
                            >
                                {{ doc.letter }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-[12px] font-semibold text-[#111827] leading-snug truncate">{{ doc.label }}</p>
                                <p class="text-[10.5px] text-[#9ca3af] truncate leading-snug">{{ doc.desc }}</p>
                            </div>
                        </button>
                    </div>
                </div>

            </div>
        </section>

        <!-- ════════════════════════════════════════════════════════
             EXPLORE THE PLATFORM — flat 4-column layout (no cards)
        ════════════════════════════════════════════════════════ -->
        <section>
            <h2 class="text-[15px] font-semibold text-[#111827] mb-5">Explore the platform</h2>

            <!-- Flat row, dividers between items on desktop -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-6">
                <div
                    v-for="item in exploreDocs"
                    :key="item.label"
                    class="flex flex-col gap-1.5 cursor-pointer group"
                >
                    <!-- Icon -->
                    <div class="flex items-center gap-2 mb-0.5">
                        <svg
                            class="w-4 h-4 text-[#6b7280] group-hover:text-[#2563EB] transition-colors shrink-0"
                            fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.75"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                        </svg>
                        <p class="text-[12.5px] font-semibold text-[#111827] group-hover:text-[#2563EB] transition-colors leading-snug">
                            {{ item.label }}
                        </p>
                    </div>
                    <p class="text-[11.5px] text-[#6b7280] leading-relaxed">{{ item.desc }}</p>
                </div>
            </div>
        </section>

    </div>
</template>
