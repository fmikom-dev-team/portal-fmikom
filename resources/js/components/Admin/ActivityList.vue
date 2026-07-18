<script setup lang="ts">
import { ref } from "vue";

interface Activity {
	id: number | string;
	type: "report" | "warning" | "takedown" | "comment" | "publish";
	title: string;
	description: string;
	actor: string;
	time: string;
	avatar?: string;
}

defineProps<{
	activities: Activity[];
	loading?: boolean;
}>();

const brokenAvatars = ref<Record<string | number, boolean>>({});

const typeConfig = {
	report: {
		color:
			"bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400",
		dot: "bg-amber-400",
		label: "Laporan",
	},
	warning: {
		color:
			"bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400",
		dot: "bg-orange-400",
		label: "Peringatan",
	},
	takedown: {
		color: "bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400",
		dot: "bg-rose-400",
		label: "Takedown",
	},
	comment: {
		color: "bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400",
		dot: "bg-blue-400",
		label: "Komentar",
	},
	publish: {
		color:
			"bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400",
		dot: "bg-emerald-400",
		label: "Publish",
	},
};
</script>

<template>
    <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-zinc-800">
            <h3 class="text-[13px] font-bold text-slate-800 dark:text-zinc-100">Aktivitas Terbaru</h3>
            <button class="text-[12px] font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 transition-colors">
                Lihat Semua
            </button>
        </div>

        <!-- ── Skeleton shimmer ──────────────────────────────────────────── -->
        <div v-if="loading" class="divide-y divide-slate-100 dark:divide-zinc-800">
            <div v-for="i in 5" :key="i" class="flex items-center gap-3 px-5 py-4">
                <!-- Avatar skeleton -->
                <div class="relative shrink-0">
                    <div class="h-8 w-8 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
                    <div class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-white dark:border-zinc-900 bg-slate-200 dark:bg-zinc-700 animate-shimmer" />
                </div>
                <!-- Text lines -->
                <div class="flex-1 space-y-2 min-w-0">
                    <div class="h-3 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer"
                         :style="{ width: (55 + i * 7) + '%' }" />
                    <div class="h-2.5 w-2/5 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
                </div>
                <!-- Badge + time -->
                <div class="shrink-0 space-y-1.5 text-right">
                    <div class="h-4 w-14 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer ml-auto" />
                    <div class="h-2.5 w-10 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer ml-auto" />
                </div>
            </div>
        </div>

        <!-- ── Content ────────────────────────────────────────────────────── -->
        <div v-else class="divide-y divide-slate-50 dark:divide-zinc-800/50">
            <div
                v-for="activity in activities"
                :key="activity.id"
                class="flex items-start gap-3.5 px-5 py-3.5 hover:bg-slate-50 dark:hover:bg-zinc-800/40 transition-colors group"
            >
                <!-- Avatar with type dot -->
                <div class="relative shrink-0">
                    <img
                        v-if="activity.avatar && !brokenAvatars[activity.id]"
                        :src="activity.avatar"
                        :alt="activity.actor"
                        @error="brokenAvatars[activity.id] = true"
                        class="h-8 w-8 rounded-full object-cover"
                    />
                    <img
                        v-else-if="activity.actor"
                        :src="`https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(activity.actor)}&backgroundColor=3b82f6,6366f1,8b5cf6,ec4899,f43f5e&backgroundType=gradientLinear&bold=true`"
                        :alt="activity.actor"
                        class="h-8 w-8 rounded-full object-cover"
                    />
                    <div v-else class="h-8 w-8 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center text-[11px] font-black text-white">
                        {{ activity.actor?.charAt(0).toUpperCase() || 'A' }}
                    </div>
                    <div :class="['absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-white dark:border-zinc-900', typeConfig[activity.type].dot]" />
                </div>

                <!-- Text -->
                <div class="flex-1 min-w-0">
                    <p class="text-[12.5px] font-semibold text-slate-800 dark:text-zinc-100 truncate leading-tight">
                        {{ activity.title }}
                    </p>
                    <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-0.5 truncate">
                        {{ activity.description }}
                    </p>
                </div>

                <!-- Badge + time -->
                <div class="shrink-0 text-right space-y-1">
                    <span :class="['inline-flex items-center rounded-full px-1.5 py-0.5 text-[9px] font-black tracking-wide uppercase', typeConfig[activity.type].color]">
                        {{ typeConfig[activity.type].label }}
                    </span>
                    <p class="text-[10px] text-slate-400 dark:text-zinc-600">{{ activity.time }}</p>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="activities.length === 0" class="flex flex-col items-center justify-center py-12 text-center">
                <div class="h-12 w-12 rounded-2xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center mb-3">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <p class="text-[13px] font-semibold text-slate-500 dark:text-zinc-400">Belum ada aktivitas</p>
                <p class="text-[11px] text-slate-400 dark:text-zinc-600 mt-1">Aktivitas admin akan muncul di sini</p>
            </div>
        </div>
    </div>
</template>
