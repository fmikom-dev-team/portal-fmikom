<script setup lang="ts">
import axios from "axios";
import { computed, onMounted, onUnmounted, reactive, ref } from "vue";
import AppModal from "../../components/ui/AppModal.vue";
import { toast } from "../../composables/useWorkOs";
import Configuration from "./Configuration.vue";

const props = defineProps<{
	radarConfig: Array<any>;
	radarStats?: any;
	radarDetections?: any[];
	radarBlockedItems?: any[];
}>();

const localDetections = ref<any[]>([]);

// Active filters state
const activeFilters = reactive({
	type: [] as string[],
	action: [] as string[],
	dateRange: "all", // '24h', '7d', '30d', 'all'
	user: "",
	ip: "",
	country: "",
});

const activeDropdown = ref<string | null>(null);
const selectedDetection = ref<any | null>(null);

function toggleDropdown(name: string) {
	if (activeDropdown.value === name) {
		activeDropdown.value = null;
	} else {
		activeDropdown.value = name;
	}
}

// Click outside handler
const handleOutsideClick = (e: MouseEvent) => {
	const target = e.target as HTMLElement;
	if (!target.closest(".filter-popover-container")) {
		activeDropdown.value = null;
	}
};

onMounted(() => {
	localDetections.value = [...(props.radarDetections || [])];

	// Real-time listener for Radar threats
	if (typeof globalThis.window !== "undefined" && (globalThis as any).Broadcaster) {
		(globalThis as any).Broadcaster.private("radar.alerts").listen(
			String.raw`.App\Events\Radar\ThreatDetected`,
			(e: any) => {
				// Enrich with ISO timestamp if not present
				if (!e.created_at_iso) {
					e.created_at_iso = new Date().toISOString();
				}
				// Add new detection to the top of the table seamlessly
				localDetections.value.unshift(e);

				// Optionally play sound or show notification for critical threats
				if (e.severity === "Critical" || e.severity === "High") {
					toast(`Threat Blocked: ${e.type} from ${e.ip}`, "error");
				}
			},
		);
	}

	globalThis.addEventListener("click", handleOutsideClick);
});

onUnmounted(() => {
	globalThis.removeEventListener("click", handleOutsideClick);
});

// Dynamic values from data
const uniqueTypes = computed(() => {
	const types = new Set(
		localDetections.value.map((d) => d.type).filter(Boolean),
	);
	return Array.from(types).sort() as string[];
});

const uniqueActions = computed(() => {
	const actions = new Set(
		localDetections.value.map((d) => d.action).filter(Boolean),
	);
	return Array.from(actions).sort() as string[];
});

const uniqueCountries = computed(() => {
	const countries = new Set(
		localDetections.value.map((d) => d.country).filter(Boolean),
	);
	return Array.from(countries).sort() as string[];
});

const dateRangeLabel = computed(() => {
	if (activeFilters.dateRange === "24h") return "24h";
	if (activeFilters.dateRange === "7d") return "7d";
	if (activeFilters.dateRange === "30d") return "30d";
	return "All";
});

const hasActiveFilters = computed(() => {
	return (
		activeFilters.type.length > 0 ||
		activeFilters.action.length > 0 ||
		activeFilters.dateRange !== "all" ||
		activeFilters.user.trim() !== "" ||
		activeFilters.ip.trim() !== "" ||
		activeFilters.country.trim() !== ""
	);
});

function resetFilters() {
	activeFilters.type = [];
	activeFilters.action = [];
	activeFilters.dateRange = "all";
	activeFilters.user = "";
	activeFilters.ip = "";
	activeFilters.country = "";
}

function getDetectionTime(d: any): number {
	try {
		// Prefer ISO timestamp for accuracy
		if (d.created_at_iso) {
			return new Date(d.created_at_iso).getTime();
		}
		return new Date(d.created_at).getTime() || Date.now();
	} catch {
		return Date.now();
	}
}

function matchesTypeFilter(d: any): boolean {
	if (!activeFilters.type.length) return true;
	return activeFilters.type.includes(d.type);
}

function matchesActionFilter(d: any): boolean {
	if (!activeFilters.action.length) return true;
	return activeFilters.action.includes(d.action);
}

function matchesDateFilter(d: any): boolean {
	if (activeFilters.dateRange === "all") return true;
	const detTime = getDetectionTime(d);
	const now = Date.now();
	if (activeFilters.dateRange === "24h") {
		return detTime >= now - 24 * 60 * 60 * 1000;
	}
	if (activeFilters.dateRange === "7d") {
		return detTime >= now - 7 * 24 * 60 * 60 * 1000;
	}
	if (activeFilters.dateRange === "30d") {
		return detTime >= now - 30 * 24 * 60 * 60 * 1000;
	}
	return true;
}

function matchesUserFilter(d: any): boolean {
	const search = activeFilters.user.trim();
	if (!search) return true;
	const email = (
		d.metadata?.email ||
		d.metadata?.user ||
		"anonymous"
	).toLowerCase();
	return email.includes(search.toLowerCase());
}

function matchesIpFilter(d: any): boolean {
	const search = activeFilters.ip.trim();
	if (!search) return true;
	return !!d.ip && d.ip.toLowerCase().includes(search.toLowerCase());
}

function matchesCountryFilter(d: any): boolean {
	const search = activeFilters.country.trim();
	if (!search) return true;
	return !!d.country && d.country.toLowerCase().includes(search.toLowerCase());
}

// Filtered detections
const filteredDetections = computed(() => {
	return localDetections.value.filter(
		(d) =>
			matchesTypeFilter(d) &&
			matchesActionFilter(d) &&
			matchesDateFilter(d) &&
			matchesUserFilter(d) &&
			matchesIpFilter(d) &&
			matchesCountryFilter(d),
	);
});

const radarTab = ref("Overview");
const isResetting = ref(false);
const showResetConfirm = ref(false);

async function confirmReset() {
	showResetConfirm.value = false;
	isResetting.value = true;
	try {
		await axios.delete("/workos/radar/detections");
		localDetections.value = [];
		toast("All detections have been cleared.", "success");
	} catch {
		toast("Failed to reset detections.", "error");
	} finally {
		isResetting.value = false;
	}
}

// SVG Area Chart Logic
const chartContainer = ref<HTMLElement | null>(null);
const hoveredIdx = ref<number | null>(null);
const hoveredPoint = computed(() => {
	if (hoveredIdx.value === null) return null;
	return chartPoints.value[hoveredIdx.value] || null;
});

const tooltipX = ref(0);
const tooltipY = ref(0);

const chartData = computed(() => {
	const points: Array<{ label: string; count: number; timeMs: number }> = [];
	const now = new Date();

	// Generate 14 intervals of 12 hours (last 7 days)
	for (let i = 13; i >= 0; i--) {
		const time = new Date(now.getTime() - i * 12 * 60 * 60 * 1000);
		const month = time.toLocaleString("en-US", { month: "short" });
		const day = time.getDate();
		const hour = String(time.getHours()).padStart(2, "0");
		const label = `${month} ${day}, ${hour}:00`;
		points.push({ label, count: 0, timeMs: time.getTime() });
	}

	if (localDetections.value.length) {
		localDetections.value.forEach((d) => {
			try {
				// Use ISO timestamp if available, otherwise try parsing formatted date
				const detTime = d.created_at_iso
					? new Date(d.created_at_iso).getTime()
					: new Date(d.created_at).getTime();
				if (!Number.isNaN(detTime)) {
					let minDiff = Infinity;
					let closestIdx = 0;
					points.forEach((p, idx) => {
						const diff = Math.abs(p.timeMs - detTime);
						if (diff < minDiff) {
							minDiff = diff;
							closestIdx = idx;
						}
					});
					// Accept if within 13-hour window of an interval point
					if (minDiff < 13 * 60 * 60 * 1000) {
						points[closestIdx].count++;
					}
				}
			} catch (err) {
				console.error("Error parsing date in chart:", err);
			}
		});
	}

	return points;
});

const chartPoints = computed(() => {
	const data = chartData.value;
	if (!data.length) return [];
	const counts = data.map((d) => d.count);
	const maxCount = Math.max(...counts, 1);

	return data.map((d, idx) => {
		const x = idx * (800 / (data.length - 1));
		const y = 135 - (d.count / maxCount) * 95; // Y ranges from 40 to 135 inside viewBox
		return { x, y, label: d.label, count: d.count };
	});
});

const linePath = computed(() => {
	const pts = chartPoints.value;
	if (!pts.length) return "";
	return pts
		.map(
			(p, idx) =>
				`${idx === 0 ? "M" : "L"} ${p.x.toFixed(1)} ${p.y.toFixed(1)}`,
		)
		.join(" ");
});

const areaPath = computed(() => {
	const pts = chartPoints.value;
	if (!pts.length) return "";
	const start = `M 0 150`;
	const line = pts
		.map((p) => `L ${p.x.toFixed(1)} ${p.y.toFixed(1)}`)
		.join(" ");
	const end = `L 800 150 Z`;
	return `${start} ${line} ${end}`;
});

function handleMouseMove(e: MouseEvent) {
	if (!chartContainer.value || !chartPoints.value.length) return;
	const rect = chartContainer.value.getBoundingClientRect();
	const mouseX = e.clientX - rect.left;
	const pct = mouseX / rect.width;
	const xCoord = pct * 800;

	let closestIdx = 0;
	let minDiff = Infinity;
	chartPoints.value.forEach((p, idx) => {
		const diff = Math.abs(p.x - xCoord);
		if (diff < minDiff) {
			minDiff = diff;
			closestIdx = idx;
		}
	});

	hoveredIdx.value = closestIdx;

	const activePt = chartPoints.value[closestIdx];
	tooltipX.value = (activePt.x / 800) * rect.width;
	tooltipY.value = (activePt.y / 160) * rect.height;
}

function handleMouseLeave() {
	hoveredIdx.value = null;
}

// Identifiers Computations — all use localDetections for real-time accuracy
const topIps = computed(() => {
	if (!localDetections.value.length) return [];
	const counts: Record<string, number> = {};
	localDetections.value.forEach((d) => {
		if (d.ip) counts[d.ip] = (counts[d.ip] || 0) + 1;
	});
	return Object.entries(counts)
		.map(([ip, count]) => ({
			value: ip,
			count,
			percentage: Math.round((count / localDetections.value.length) * 100),
		}))
		.sort((a, b) => b.count - a.count)
		.slice(0, 4);
});

const topCountries = computed(() => {
	if (!localDetections.value.length) return [];
	const counts: Record<string, number> = {};
	localDetections.value.forEach((d) => {
		const c = d.country || "Unknown";
		counts[c] = (counts[c] || 0) + 1;
	});
	return Object.entries(counts)
		.map(([country, count]) => ({
			value: country,
			count,
			percentage: Math.round((count / localDetections.value.length) * 100),
		}))
		.sort((a, b) => b.count - a.count)
		.slice(0, 4);
});

const topDomains = computed(() => {
	if (!localDetections.value.length) return [];
	const counts: Record<string, number> = {};
	localDetections.value.forEach((d) => {
		let domain = "unknown.com";
		if (d.metadata && typeof d.metadata === "object") {
			const email = d.metadata.email || d.metadata.user;
			if (email?.includes("@")) {
				domain = email.split("@")[1];
			} else if (d.metadata.reason?.includes("domain")) {
				domain = "mailinator.com";
			}
		}
		counts[domain] = (counts[domain] || 0) + 1;
	});
	return Object.entries(counts)
		.map(([domain, count]) => ({
			value: domain,
			count,
			percentage: Math.round((count / localDetections.value.length) * 100),
		}))
		.sort((a, b) => b.count - a.count)
		.slice(0, 4);
});

const topUsers = computed(() => {
	if (!localDetections.value.length) return [];
	const counts: Record<string, number> = {};
	localDetections.value.forEach((d) => {
		let user = "anonymous";
		if (d.metadata && typeof d.metadata === "object") {
			user = d.metadata.user || d.metadata.email || "anonymous";
		}
		counts[user] = (counts[user] || 0) + 1;
	});
	return Object.entries(counts)
		.map(([user, count]) => ({
			value: user,
			count,
			percentage: Math.round((count / localDetections.value.length) * 100),
		}))
		.sort((a, b) => b.count - a.count)
		.slice(0, 4);
});

function statusColor(s: string) {
	if (s === "Enabled") return "text-emerald-600";
	if (s === "Logging") return "text-amber-600";
	return "text-gray-400";
}
function statusDot(s: string) {
	if (s === "Enabled") return "bg-emerald-500";
	if (s === "Logging") return "bg-amber-400";
	return "bg-gray-300";
}

// Stats computed from localDetections (always in sync with realtime data)
const overviewStats = computed(() => [
	{ label: "Total Detections", value: localDetections.value.length },
	{
		label: "Allowed",
		value: localDetections.value.filter((d) => d.action === "Allowed").length,
	},
	{
		label: "Challenged",
		value: localDetections.value.filter((d) => d.action === "Challenged")
			.length,
	},
	{
		label: "Blocked",
		value: localDetections.value.filter((d) => d.action === "Blocked").length,
	},
]);

const breakdowns = computed(() => {
	const typeMap: Record<string, { label: string; color: string }> = {
		"Bot detection": { label: "Bot detection", color: "bg-emerald-500" },
		"Brute force attack": { label: "Brute force attack", color: "bg-blue-500" },
		"Impossible travel": { label: "Impossible travel", color: "bg-violet-500" },
		"Repeat sign up": { label: "Repeat sign up", color: "bg-cyan-500" },
		"Stale account": { label: "Stale account", color: "bg-amber-500" },
		"Unrecognized device": {
			label: "Unrecognized device",
			color: "bg-rose-500",
		},
		"Restriction enforced": {
			label: "Restriction enforced",
			color: "bg-red-600",
		},
	};
	const counts: Record<string, number> = {};
	localDetections.value.forEach((d) => {
		if (d.type) counts[d.type] = (counts[d.type] || 0) + 1;
	});
	return Object.entries(typeMap).map(([key, meta]) => ({
		l: meta.label,
		c: meta.color,
		v: counts[key] || 0,
	}));
});

function severityColor(s: string) {
	if (s === "Critical") return "bg-red-100 text-red-850 dark:bg-red-950/40 dark:text-red-400";
	if (s === "High") return "bg-orange-100 text-orange-850 dark:bg-orange-950/40 dark:text-orange-400";
	if (s === "Medium") return "bg-yellow-100 text-yellow-850 dark:bg-yellow-950/40 dark:text-yellow-400";
	return "bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-400";
}
</script>

<template>
    <div class="p-6 md:p-8">
        <div class="mb-6">
            <h1 class="text-[22px] font-semibold text-gray-900 dark:text-zinc-100 tracking-tight mb-1">Radar</h1>
            <p class="text-[13px] text-gray-500 dark:text-zinc-400">Real-time threat detection and security monitoring.</p>
        </div>

        <!-- Tabs -->
        <div class="flex border-b border-gray-200 dark:border-zinc-800 mb-6">
            <button
                v-for="t in ['Overview', 'Detections', 'Configuration']"
                :key="t"
                :class="['px-4 py-2.5 text-[13px] font-medium border-b-2 -mb-px transition-colors bg-transparent cursor-pointer',
                    radarTab === t ? 'border-blue-600 text-blue-600 dark:text-blue-400 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-zinc-400 dark:hover:text-zinc-300']"
                @click="radarTab = t"
            >
                {{ t }}
            </button>
        </div>

        <!-- OVERVIEW TAB -->
        <div v-if="radarTab === 'Overview'">
            <!-- Stats card -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm mb-5 overflow-hidden dark:shadow-none">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-zinc-800">
                    <div class="flex items-center justify-between gap-4 flex-wrap">
                        <div class="flex items-start gap-8 flex-wrap">
                            <div v-for="stat in overviewStats" :key="stat.label">
                                <p class="text-[28px] font-bold text-gray-900 dark:text-zinc-100 tabular-nums">{{ stat.value }}</p>
                                <p class="text-[12px] text-gray-500 dark:text-zinc-400 mt-0.5">{{ stat.label }}</p>
                            </div>
                        </div>
                        <!-- Reset button -->
                        <button
                            @click="showResetConfirm = true"
                            :disabled="isResetting || localDetections.length === 0"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 dark:border-red-900/40 text-red-600 dark:text-red-400 text-[12px] font-medium hover:bg-red-50 dark:hover:bg-red-950/30 disabled:opacity-40 disabled:cursor-not-allowed transition-colors bg-transparent cursor-pointer"
                        >
                            <svg v-if="isResetting" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            {{ isResetting ? 'Clearing…' : 'Reset detections' }}
                        </button>
                    </div>
                </div>
                <!-- Interactive SVG Chart -->
                <div class="border-b border-gray-100 dark:border-zinc-800 bg-[#f9fafb]/50 dark:bg-zinc-800/20 px-6 py-5 relative">
                    <div class="h-44 w-full relative" ref="chartContainer" @mousemove="handleMouseMove" @mouseleave="handleMouseLeave">
                        <svg class="w-full h-full overflow-visible" viewBox="0 0 800 160" preserveAspectRatio="none">
                            <defs>
                                <linearGradient id="chartGradient" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#1d4ed8" stop-opacity="0.2" />
                                    <stop offset="100%" stop-color="#1d4ed8" stop-opacity="0.0" />
                                </linearGradient>
                            </defs>
                            
                            <!-- Grid lines -->
                            <line v-for="i in 4" :key="i" x1="0" :y1="(i * 32)" x2="800" :y2="(i * 32)" stroke="currentColor" class="text-gray-250 dark:text-zinc-800" stroke-width="0.8" stroke-dasharray="3 3" />
                            
                            <!-- Area under curve -->
                            <path :d="areaPath" fill="url(#chartGradient)" />
                            
                            <!-- Main line -->
                            <path :d="linePath" fill="none" stroke="#1d4ed8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            
                            <!-- Active Line Indicator -->
                            <line v-if="hoveredPoint" :x1="hoveredPoint.x" y1="10" :x2="hoveredPoint.x" y2="150" stroke="#1d4ed8" stroke-width="1" stroke-dasharray="3 3" />

                            <!-- Interactive Nodes -->
                            <circle v-for="(p, idx) in chartPoints" :key="idx" :cx="p.x" :cy="p.y" :r="hoveredIdx === idx ? 5 : 3" :fill="hoveredIdx === idx ? '#1d4ed8' : '#ffffff'" stroke="#1d4ed8" stroke-width="2" style="transition: all 0.15s ease;" />
                        </svg>

                        <!-- Tooltip Overlay -->
                        <div v-if="hoveredPoint" class="absolute bg-gray-900 dark:bg-zinc-950 text-white rounded-lg shadow-xl px-3 py-2 text-[11px] pointer-events-none transition-all duration-75 z-10 border dark:border-zinc-800 dark:shadow-none" :style="{ left: tooltipX + 'px', top: (tooltipY - 10) + 'px', transform: 'translate(-50%, -100%)' }">
                            <p class="font-medium text-gray-400 dark:text-zinc-500 mb-0.5">Time: {{ hoveredPoint.label }}</p>
                            <p class="font-semibold text-[13px] text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                                {{ hoveredPoint.count }} Detections
                            </p>
                        </div>
                    </div>
                    
                    <!-- X-Axis Labels -->
                    <div class="flex justify-between text-[10px] text-gray-400 dark:text-zinc-500 mt-2 font-medium px-1">
                        <span v-for="(p, idx) in chartData" :key="idx" v-show="idx % 2 === 0 || idx === chartData.length - 1">{{ p.label }}</span>
                    </div>
                </div>

                <!-- Breakdown -->
                <div class="px-6 py-4 grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-4">
                    <div v-for="b in breakdowns" :key="b.l" class="text-center">
                        <p class="text-[20px] font-bold text-gray-900 dark:text-zinc-100 tabular-nums">{{ b.v }}</p>
                        <p class="text-[10px] text-gray-500 dark:text-zinc-400 flex items-center justify-center gap-1 mt-0.5 leading-tight">
                            <span class="w-1.5 h-1.5 rounded-full shrink-0" :class="b.c"/>
                            {{ b.l }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Identifiers -->
            <p class="text-[14px] font-semibold text-gray-900 dark:text-zinc-100 mb-3">Identifiers</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- IP Addresses -->
                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm p-5 dark:shadow-none">
                    <p class="text-[13px] font-semibold text-gray-800 dark:text-zinc-200 mb-4">IP Addresses</p>
                    <div v-if="!topIps.length" class="py-10 text-center text-gray-400 dark:text-zinc-500">
                        <p class="text-[12px]">No IP addresses detected in the last 24 hours</p>
                    </div>
                    <div v-else class="space-y-3">
                        <div v-for="ip in topIps" :key="ip.value" class="space-y-1">
                            <div class="flex items-center justify-between text-[12px]">
                                <span class="font-mono text-gray-700 dark:text-zinc-300 font-medium">{{ ip.value }}</span>
                                <span class="text-gray-500 dark:text-zinc-400 font-semibold">{{ ip.count }} ({{ ip.percentage }}%)</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-zinc-800 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-blue-600 h-full rounded-full" :style="{ width: ip.percentage + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Countries -->
                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm p-5 dark:shadow-none">
                    <p class="text-[13px] font-semibold text-gray-800 dark:text-zinc-200 mb-4">Countries</p>
                    <div v-if="!topCountries.length" class="py-10 text-center text-gray-400 dark:text-zinc-500">
                        <p class="text-[12px]">No countries detected in the last 24 hours</p>
                    </div>
                    <div v-else class="space-y-3">
                        <div v-for="c in topCountries" :key="c.value" class="space-y-1">
                            <div class="flex items-center justify-between text-[12px]">
                                <span class="text-gray-700 dark:text-zinc-300 font-medium">{{ c.value }}</span>
                                <span class="text-gray-500 dark:text-zinc-400 font-semibold">{{ c.count }} ({{ c.percentage }}%)</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-zinc-800 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-emerald-600 h-full rounded-full" :style="{ width: c.percentage + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Domains -->
                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm p-5 dark:shadow-none">
                    <p class="text-[13px] font-semibold text-gray-800 dark:text-zinc-200 mb-4">Domains</p>
                    <div v-if="!topDomains.length" class="py-10 text-center text-gray-400 dark:text-zinc-500">
                        <p class="text-[12px]">No domains detected in the last 24 hours</p>
                    </div>
                    <div v-else class="space-y-3">
                        <div v-for="d in topDomains" :key="d.value" class="space-y-1">
                            <div class="flex items-center justify-between text-[12px]">
                                <span class="font-mono text-gray-700 dark:text-zinc-300 font-medium">{{ d.value }}</span>
                                <span class="text-gray-500 dark:text-zinc-400 font-semibold">{{ d.count }} ({{ d.percentage }}%)</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-zinc-800 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-violet-600 h-full rounded-full" :style="{ width: d.percentage + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users -->
                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm p-5 dark:shadow-none">
                    <p class="text-[13px] font-semibold text-gray-800 dark:text-zinc-200 mb-4">Users</p>
                    <div v-if="!topUsers.length" class="py-10 text-center text-gray-400 dark:text-zinc-500">
                        <p class="text-[12px]">No users detected in the last 24 hours</p>
                    </div>
                    <div v-else class="space-y-3">
                        <div v-for="u in topUsers" :key="u.value" class="space-y-1">
                            <div class="flex items-center justify-between text-[12px]">
                                <span class="text-gray-700 dark:text-zinc-300 font-medium truncate max-w-[200px]" :title="u.value">{{ u.value }}</span>
                                <span class="text-gray-500 dark:text-zinc-400 font-semibold shrink-0">{{ u.count }} ({{ u.percentage }}%)</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-zinc-800 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-rose-600 h-full rounded-full" :style="{ width: u.percentage + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DETECTIONS TAB -->
        <div v-else-if="radarTab === 'Detections'">
            <!-- Filters Row -->
            <div class="flex flex-wrap items-center gap-2 mb-4">
                <!-- + Type -->
                <div class="relative filter-popover-container">
                    <button @click.stop="toggleDropdown('type')" :class="['flex items-center gap-1.5 px-3 py-1.5 border rounded-lg bg-white dark:bg-zinc-900 text-[12px] font-medium transition-colors select-none focus:outline-none cursor-pointer', activeFilters.type.length ? 'border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-500' : 'border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800']">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Type{{ activeFilters.type.length ? `: ${activeFilters.type.length} selected` : '' }}</span>
                    </button>
                    <!-- Type Popover -->
                    <div v-if="activeDropdown === 'type'" class="absolute left-0 mt-2 w-56 rounded-lg bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 shadow-lg py-2 z-30 dark:shadow-none">
                        <div class="px-3 py-1 text-[11px] font-semibold text-gray-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Filter by Type</div>
                        <div class="max-h-48 overflow-y-auto px-1">
                            <label v-for="t in uniqueTypes" :key="t" class="flex items-center gap-2 px-3 py-1.5 hover:bg-gray-50 dark:bg-zinc-900 dark:hover:bg-zinc-800/60 rounded cursor-pointer text-[12px] text-gray-700 dark:text-zinc-300">
                                <input type="checkbox" :value="t" v-model="activeFilters.type" class="rounded border-gray-300 dark:border-zinc-700 text-blue-600 focus:ring-[#2563eb] w-3.5 h-3.5" />
                                <span class="truncate">{{ t }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- + Action -->
                <div class="relative filter-popover-container">
                    <button @click.stop="toggleDropdown('action')" :class="['flex items-center gap-1.5 px-3 py-1.5 border rounded-lg bg-white dark:bg-zinc-900 text-[12px] font-medium transition-colors select-none focus:outline-none cursor-pointer', activeFilters.action.length ? 'border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-500' : 'border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800']">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Action{{ activeFilters.action.length ? `: ${activeFilters.action.length} selected` : '' }}</span>
                    </button>
                    <!-- Action Popover -->
                    <div v-if="activeDropdown === 'action'" class="absolute left-0 mt-2 w-48 rounded-lg bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 shadow-lg py-2 z-30 dark:shadow-none">
                        <div class="px-3 py-1 text-[11px] font-semibold text-gray-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Filter by Action</div>
                        <div class="px-1">
                            <label v-for="a in uniqueActions" :key="a" class="flex items-center gap-2 px-3 py-1.5 hover:bg-gray-50 dark:bg-zinc-900 dark:hover:bg-zinc-800/60 rounded cursor-pointer text-[12px] text-gray-700 dark:text-zinc-300">
                                <input type="checkbox" :value="a" v-model="activeFilters.action" class="rounded border-gray-300 dark:border-zinc-700 text-blue-600 focus:ring-[#2563eb] w-3.5 h-3.5" />
                                <span>{{ a }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Date -->
                <div class="relative filter-popover-container">
                    <button @click.stop="toggleDropdown('date')" :class="['flex items-center gap-1.5 px-3 py-1.5 border rounded-lg bg-white dark:bg-zinc-900 text-[12px] font-medium transition-colors select-none focus:outline-none cursor-pointer', activeFilters.dateRange !== 'all' ? 'border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-500' : 'border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800']">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Date{{ activeFilters.dateRange !== 'all' ? `: ${dateRangeLabel}` : '' }}</span>
                    </button>
                    <!-- Date Popover -->
                    <div v-if="activeDropdown === 'date'" class="absolute left-0 mt-2 w-48 rounded-lg bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 shadow-lg py-2 z-30 dark:shadow-none">
                        <div class="px-3 py-1 text-[11px] font-semibold text-gray-400 dark:text-zinc-500 uppercase tracking-wider mb-1">Filter by Date</div>
                        <div class="px-1">
                            <button v-for="opt in [{val:'24h', label:'Last 24 hours'}, {val:'7d', label:'Last 7 days'}, {val:'30d', label:'Last 30 days'}, {val:'all', label:'All time'}]" :key="opt.val" @click="activeFilters.dateRange = opt.val; activeDropdown = null" :class="['w-full text-left px-3 py-1.5 text-[12px] rounded hover:bg-gray-50 dark:hover:bg-zinc-800 cursor-pointer border-0', activeFilters.dateRange === opt.val ? 'text-blue-600 dark:text-blue-450 font-medium bg-blue-50/50 dark:bg-blue-950/20' : 'text-gray-700 dark:text-zinc-300 bg-transparent']">
                                {{ opt.label }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- User -->
                <div class="relative filter-popover-container">
                    <button @click.stop="toggleDropdown('user')" :class="['flex items-center gap-1.5 px-3 py-1.5 border rounded-lg bg-white dark:bg-zinc-900 text-[12px] font-medium transition-colors select-none focus:outline-none cursor-pointer', activeFilters.user ? 'border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-500' : 'border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800']">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>User{{ activeFilters.user ? `: ${activeFilters.user}` : '' }}</span>
                    </button>
                    <!-- User Popover -->
                    <div v-if="activeDropdown === 'user'" class="absolute left-0 mt-2 w-56 rounded-lg bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 shadow-lg p-2.5 z-30 dark:shadow-none">
                        <div class="text-[11px] font-semibold text-gray-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                            <span>Filter by User/Email</span>
                            <button v-if="activeFilters.user" @click="activeFilters.user = ''" class="text-[10px] text-red-500 hover:text-red-700 bg-transparent border-0 cursor-pointer">Clear</button>
                        </div>
                        <input type="text" v-model="activeFilters.user" placeholder="Search user or email..." class="w-full px-2.5 py-1.5 text-[12px] border border-gray-250 dark:border-zinc-700 rounded focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] text-gray-800 dark:text-zinc-100 bg-white dark:bg-zinc-900" />
                    </div>
                </div>

                <!-- IP Address -->
                <div class="relative filter-popover-container">
                    <button @click.stop="toggleDropdown('ip')" :class="['flex items-center gap-1.5 px-3 py-1.5 border rounded-lg bg-white dark:bg-zinc-900 text-[12px] font-medium transition-colors select-none focus:outline-none cursor-pointer', activeFilters.ip ? 'border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-500' : 'border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800']">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        <span>IP Address{{ activeFilters.ip ? `: ${activeFilters.ip}` : '' }}</span>
                    </button>
                    <!-- IP Address Popover -->
                    <div v-if="activeDropdown === 'ip'" class="absolute left-0 mt-2 w-56 rounded-lg bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 shadow-lg p-2.5 z-30 dark:shadow-none">
                        <div class="text-[11px] font-semibold text-gray-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                            <span>Filter by IP Address</span>
                            <button v-if="activeFilters.ip" @click="activeFilters.ip = ''" class="text-[10px] text-red-500 hover:text-red-700 bg-transparent border-0 cursor-pointer">Clear</button>
                        </div>
                        <input type="text" v-model="activeFilters.ip" placeholder="Search IP address..." class="w-full px-2.5 py-1.5 text-[12px] border border-gray-250 dark:border-zinc-700 rounded focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] text-gray-800 dark:text-zinc-100 bg-white dark:bg-zinc-900" />
                    </div>
                </div>

                <!-- Country -->
                <div class="relative filter-popover-container">
                    <button @click.stop="toggleDropdown('country')" :class="['flex items-center gap-1.5 px-3 py-1.5 border rounded-lg bg-white dark:bg-zinc-900 text-[12px] font-medium transition-colors select-none focus:outline-none cursor-pointer', activeFilters.country ? 'border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-500' : 'border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800']">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Country{{ activeFilters.country ? `: ${activeFilters.country}` : '' }}</span>
                    </button>
                    <!-- Country Popover -->
                    <div v-if="activeDropdown === 'country'" class="absolute left-0 mt-2 w-56 rounded-lg bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 shadow-lg py-2 z-30 font-sans dark:shadow-none">
                        <div class="px-3 py-1 text-[11px] font-semibold text-gray-400 dark:text-zinc-500 uppercase tracking-wider mb-1 flex items-center justify-between">
                            <span>Filter by Country</span>
                            <button v-if="activeFilters.country" @click="activeFilters.country = ''" class="text-[10px] text-red-500 hover:text-red-700 bg-transparent border-0 cursor-pointer">Clear</button>
                        </div>
                        <div class="px-3 pb-2 border-b border-gray-100 dark:border-zinc-800">
                            <input type="text" v-model="activeFilters.country" placeholder="Type country code or name..." class="w-full px-2.5 py-1.5 text-[12px] border border-gray-250 dark:border-zinc-700 rounded focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] text-gray-800 dark:text-zinc-100 bg-white dark:bg-zinc-900" />
                        </div>
                        <div class="max-h-36 overflow-y-auto px-1 mt-1" v-if="uniqueCountries.length">
                            <button v-for="c in uniqueCountries" :key="c" @click="activeFilters.country = c; activeDropdown = null" :class="['w-full text-left px-3 py-1.5 text-[12px] rounded hover:bg-gray-50 dark:hover:bg-zinc-800/80 truncate border-0 cursor-pointer bg-transparent', activeFilters.country === c ? 'text-blue-600 dark:text-blue-450 font-medium bg-blue-50/50 dark:bg-blue-950/20' : 'text-gray-700 dark:text-zinc-300']">
                                {{ c }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Reset Filters button -->
                <button v-if="hasActiveFilters" @click="resetFilters" class="text-[11.5px] font-medium text-red-600 hover:text-red-800 transition-colors ml-2 bg-transparent border-0 cursor-pointer">
                    Clear filters
                </button>

                <!-- Spacer + Reset Detections button -->
                <div class="ml-auto flex items-center">
                    <button
                        @click="showResetConfirm = true"
                        :disabled="isResetting || localDetections.length === 0"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 dark:border-red-900/40 text-red-600 dark:text-red-400 text-[12px] font-medium hover:bg-red-50 dark:hover:bg-red-950/30 disabled:opacity-40 disabled:cursor-not-allowed transition-colors bg-transparent cursor-pointer"
                    >
                        <svg v-if="isResetting" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        {{ isResetting ? 'Clearing…' : 'Reset all detections' }}
                    </button>
                </div>
            </div>

            <!-- Table Container -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden dark:shadow-none">
                <div v-if="!filteredDetections?.length" class="py-20 flex flex-col items-center justify-center text-gray-400 dark:text-zinc-500 bg-white dark:bg-zinc-900">
                    <svg class="w-10 h-10 mb-3 text-gray-300 dark:text-zinc-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="9" stroke-dasharray="3 3" />
                        <circle cx="12" cy="12" r="5" />
                        <circle cx="12" cy="12" r="1" />
                    </svg>
                    <p class="text-[13px] font-medium text-gray-600 dark:text-zinc-300 mb-1">No detections</p>
                    <p class="text-[12px] text-gray-400 dark:text-zinc-500">No activity matches the selected filter options.</p>
                </div>
                
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left whitespace-nowrap">
                        <caption class="sr-only">Threat Detections</caption>
                        <thead>
                            <tr class="bg-gray-50/75 dark:bg-zinc-800/50 border-b border-gray-100 dark:border-zinc-800 text-[11px] font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">
                                <th class="px-6 py-3.5">Type</th>
                                <th class="px-6 py-3.5">Action</th>
                                <th class="px-6 py-3.5">Email</th>
                                <th class="px-6 py-3.5">IP Address</th>
                                <th class="px-6 py-3.5">Country</th>
                                <th class="px-6 py-3.5 flex items-center gap-1">
                                    <span>Timestamp</span>
                                    <svg class="w-3 h-3 text-gray-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                    </svg>
                                </th>
                                <th class="w-10 px-6 py-3.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                            <tr v-for="d in filteredDetections" :key="d.id" class="hover:bg-gray-50 dark:bg-zinc-900/50 dark:hover:bg-zinc-800/40 transition-colors">
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[13px] font-medium text-gray-900 dark:text-zinc-100">{{ d.type }}</span>
                                        <span :class="['px-1.5 py-0.5 rounded-[4px] text-[10px] font-semibold', severityColor(d.severity)]">
                                            {{ d.severity }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-3">
                                    <span :class="[
                                        'px-2 py-0.5 rounded text-[11px] font-medium border transition-colors',
                                        d.action === 'Blocked' ? 'bg-red-50 text-red-700 border-red-100 dark:bg-red-950/20 dark:text-red-400 dark:border-red-900/40' :
                                        d.action === 'Challenged' ? 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-950/20 dark:text-amber-400 dark:border-amber-900/40' :
                                        'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-900/40'
                                    ]">
                                        {{ d.action }}
                                    </span>
                                </td>
                                <td class="px-6 py-3">
                                    <span class="text-[13px] text-gray-600 dark:text-zinc-300 font-medium">{{ d.metadata?.email || d.metadata?.user || 'anonymous' }}</span>
                                </td>
                                <td class="px-6 py-3 font-mono text-[12.5px] text-gray-700 dark:text-zinc-300">
                                    {{ d.ip }}
                                </td>
                                <td class="px-6 py-3 text-[13px] text-gray-600 dark:text-zinc-300">
                                    {{ d.country || 'Unknown' }}
                                </td>
                                <td class="px-6 py-3">
                                    <p class="text-[12.5px] text-gray-700 dark:text-zinc-300 font-medium">{{ d.created_at }}</p>
                                    <p class="text-[10.5px] text-gray-400 dark:text-zinc-500 mt-0.5">{{ d.created_at_human }}</p>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <button @click.stop="selectedDetection = d" class="p-1.5 rounded-md hover:bg-gray-100 dark:bg-zinc-800 dark:hover:bg-zinc-800 text-gray-400 hover:text-gray-600 dark:text-zinc-400 dark:hover:text-zinc-300 transition-colors bg-transparent border-0 cursor-pointer" title="View details">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Detection Details Modal -->
        <AppModal
            :show="!!selectedDetection"
            title="Detection Details"
            description="Detailed metadata and information for this security threat detection."
            @close="selectedDetection = null"
        >
            <div class="space-y-4" v-if="selectedDetection">
                <!-- Header Status Badge -->
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-zinc-800">
                    <div>
                        <p class="text-[14px] font-bold text-gray-900 dark:text-zinc-100">{{ selectedDetection.type }}</p>
                        <p class="text-[11.5px] text-gray-400 dark:text-zinc-500 mt-0.5">{{ selectedDetection.created_at }} ({{ selectedDetection.created_at_human }})</p>
                    </div>
                    <span :class="['px-2.5 py-1 rounded text-[11px] font-semibold border',
                        selectedDetection.action === 'Blocked' ? 'bg-red-50 text-red-700 border-red-100 dark:bg-red-950/20 dark:text-red-400 dark:border-red-900/40' :
                        selectedDetection.action === 'Challenged' ? 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-950/20 dark:text-amber-400 dark:border-amber-900/40' :
                        'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-900/40'
                    ]">
                        {{ selectedDetection.action }}
                    </span>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-2 gap-4 text-[12px]">
                    <div>
                        <p class="text-gray-400 dark:text-zinc-500 font-medium">Risk Score</p>
                        <p :class="['text-[14px] font-bold mt-0.5', selectedDetection.risk_score > 80 ? 'text-red-600 dark:text-red-400' : selectedDetection.risk_score > 50 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400']">
                            {{ selectedDetection.risk_score }} / 100
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-400 dark:text-zinc-500 font-medium">Severity</p>
                        <span :class="['px-2 py-0.5 rounded text-[10px] font-semibold mt-1 inline-block', severityColor(selectedDetection.severity)]">
                            {{ selectedDetection.severity }}
                        </span>
                    </div>
                    <div>
                        <p class="text-gray-400 dark:text-zinc-500 font-medium">IP Address</p>
                        <p class="font-mono text-gray-900 dark:text-zinc-200 font-medium mt-0.5">{{ selectedDetection.ip }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 dark:text-zinc-500 font-medium">Location</p>
                        <p class="text-gray-900 dark:text-zinc-200 font-medium mt-0.5">
                            {{ selectedDetection.city ? selectedDetection.city + ', ' : '' }}{{ selectedDetection.country }}
                        </p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-gray-400 dark:text-zinc-500 font-medium font-sans">Device & OS</p>
                        <p class="text-gray-900 dark:text-zinc-200 font-medium mt-0.5">{{ selectedDetection.device }}</p>
                    </div>
                </div>

                <!-- Metadata Block -->
                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-zinc-800" v-if="selectedDetection.metadata">
                    <p class="text-[12px] text-gray-400 dark:text-zinc-500 font-medium mb-1.5">Metadata</p>
                    <pre class="bg-gray-50 dark:bg-zinc-800 border border-gray-100 dark:border-zinc-700 rounded-lg p-3 text-[11px] font-mono text-gray-700 dark:text-zinc-300 overflow-x-auto max-h-48 leading-relaxed">{{ JSON.stringify(selectedDetection.metadata, null, 2) }}</pre>
                </div>
            </div>
            
            <template #footer>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] dark:text-zinc-300 border border-[#d1d5db] dark:border-zinc-700 hover:bg-[#f3f4f6] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm cursor-pointer dark:shadow-none"
                    @click="selectedDetection = null"
                >
                    Close
                </button>
            </template>
        </AppModal>

        <!-- Reset Confirmation Modal -->
        <AppModal :show="showResetConfirm" title="Reset all detections" @close="showResetConfirm = false">
            <div class="flex flex-col items-center text-center py-2">
                <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-950/30 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <p class="text-[14px] text-gray-700 dark:text-zinc-300 leading-relaxed max-w-xs">
                    This will permanently delete <strong>all {{ localDetections.length }} detections</strong> from the log. This action cannot be undone.
                </p>
            </div>
            <template #footer>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] dark:text-zinc-300 border border-[#d1d5db] dark:border-zinc-700 hover:bg-[#f3f4f6] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm cursor-pointer dark:shadow-none"
                    @click="showResetConfirm = false"
                >
                    Cancel
                </button>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-red-600 hover:bg-red-700 transition-colors shadow-sm cursor-pointer dark:shadow-none"
                    @click="confirmReset"
                >
                    Yes, clear all
                </button>
            </template>
        </AppModal>
    </div>
</template>
