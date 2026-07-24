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
    <li> <!-- NOSONAR -->
        <button
            :id="`wos-nav-${id}`"
            :aria-current="active ? 'page' : undefined"
            :class="[
                'relative w-full flex items-center rounded-md text-[13.5px] transition-all duration-300 select-none group h-9 pl-[11px] pr-2',
                active
                    ? 'bg-[#EFF6FF] dark:bg-blue-500/15 text-[#2563EB] dark:text-blue-400 font-medium'
                    : 'text-[#374151] dark:text-zinc-300 font-normal',
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

            <span
                class="transition-all duration-300 ease-in-out text-left truncate leading-none py-px overflow-hidden"
                :class="collapsed ? 'opacity-0 max-w-0 ml-0 pointer-events-none' : 'opacity-100 max-w-[180px] ml-2.5'"
            >{{ label }}</span>

            <!-- Badge -->
            <span
                v-if="badge && badge > 0"
                :class="[
                    'transition-all duration-300',
                    collapsed 
                        ? 'absolute top-0.5 right-1 w-3.5 h-3.5 rounded-full bg-[#f59e0b] text-white text-[8px] font-bold flex items-center justify-center border border-white ml-0' 
                        : 'flex-shrink-0 h-[18px] min-w-[18px] px-1.5 rounded-full text-[10.5px] font-semibold tabular-nums flex items-center justify-center bg-[#fef3c7] text-[#92400e] ml-auto'
                ]"
                :aria-label="`${badge} pending`"
            >
                {{ collapsed ? (badge > 9 ? '9+' : badge) : (badge > 99 ? '99+' : badge) }}
            </span>

            <!-- Submenu Chevron -->
            <ChevronRight 
                v-if="hasSubmenu"
                :class="[
                    'w-[14px] h-[14px] shrink-0 transition-all duration-300',
                    active ? 'text-[#2563EB]' : 'text-[#9ca3af] group-hover:text-[#6b7280] dark:text-zinc-400',
                    collapsed ? 'opacity-0 w-0 scale-0 ml-0' : 'opacity-60 w-[14px] scale-100 ml-1.5'
                ]"
            />
        </button>
    </li>
</template>
