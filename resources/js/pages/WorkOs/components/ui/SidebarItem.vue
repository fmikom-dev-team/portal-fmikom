<script setup lang="ts">
import { ChevronRight } from "lucide-vue-next";

defineProps<{
	id: string;
	label: string;
	icon?: string;
	badge?: number;
	active?: boolean;
	hasSubmenu?: boolean;
	collapsed?: boolean;
}>();

const emit = defineEmits<(e: "navigate", id: string) => void>();
</script>

<template>
    <li class="relative">
        <button
            :id="`wos-nav-${id}`"
            :aria-current="active ? 'page' : undefined"
            :class="[
                'group relative flex items-center rounded-lg text-[13.5px] transition-all duration-200 select-none h-9',
                collapsed ? 'w-9 h-9 mx-auto justify-center px-0' : 'w-full pl-[11px] pr-2',
                active
                    ? 'bg-[#EFF6FF] dark:bg-blue-500/15 text-[#2563EB] dark:text-blue-400 font-medium'
                    : 'text-[#374151] dark:text-zinc-300 font-normal hover:bg-slate-100 dark:hover:bg-zinc-800',
            ]"
            @click="emit('navigate', id)"
        >
            <!-- Icon -->
            <svg
                v-if="icon"
                :class="[
                    'w-[18px] h-[18px] shrink-0 transition-colors',
                    active ? 'text-[#2563EB] dark:text-blue-400' : 'text-[#6b7280] dark:text-zinc-500 group-hover:text-[#374151] dark:group-hover:text-zinc-300',
                ]"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" :d="icon" />
            </svg>

            <!-- Label (only rendered when expanded) -->
            <span
                v-if="!collapsed"
                class="transition-all duration-300 ease-in-out text-left truncate leading-none py-px overflow-hidden max-w-[180px] ml-2.5"
            >{{ label }}</span>

            <!-- Badge -->
            <span
                v-if="badge && badge > 0"
                :class="[
                    'transition-all duration-300',
                    collapsed 
                        ? 'absolute -top-0.5 -right-0.5 w-3.5 h-3.5 rounded-full bg-[#f59e0b] text-white text-[8px] font-bold flex items-center justify-center border border-white dark:border-zinc-900 ml-0 z-20' 
                        : 'flex-shrink-0 h-[18px] min-w-[18px] px-1.5 rounded-full text-[10.5px] font-semibold tabular-nums flex items-center justify-center bg-[#fef3c7] text-[#92400e] ml-auto'
                ]"
                :aria-label="`${badge} pending`"
            >
                {{ collapsed ? (badge > 9 ? '9+' : badge) : (badge > 99 ? '99+' : badge) }}
            </span>

            <!-- Submenu Chevron (only rendered when expanded) -->
            <ChevronRight 
                v-if="hasSubmenu && !collapsed"
                :class="[
                    'w-[14px] h-[14px] shrink-0 transition-all duration-300 opacity-60 ml-1.5',
                    active ? 'text-[#2563EB]' : 'text-[#9ca3af] group-hover:text-[#6b7280] dark:text-zinc-400'
                ]"
            />

            <!-- Floating Tooltip when Collapsed -->
            <div 
                v-if="collapsed" 
                class="pointer-events-none fixed left-[84px] z-[99999] rounded-xl bg-slate-900 dark:bg-zinc-100 text-white dark:text-zinc-900 text-xs font-bold px-3 py-1.5 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-all duration-200 shadow-2xl flex items-center gap-1.5 transform translate-x-1 group-hover:translate-x-0"
            >
                <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-900 dark:bg-zinc-100 rotate-45"></div>
                <span class="relative z-10">{{ label }}</span>
                <span v-if="badge && badge > 0" class="px-1.5 py-0.5 rounded-full bg-[#f59e0b] text-white text-[9.5px] font-black ml-1">
                    {{ badge }}
                </span>
            </div>
        </button>
    </li>
</template>
