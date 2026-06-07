<script setup lang="ts">
import { ChevronRight } from "lucide-vue-next";

defineProps<{
	id: string;
	label: string;
	icon?: string;
	badge?: number;
	active?: boolean;
	hasSubmenu?: boolean;
}>();

const emit = defineEmits<(e: "navigate", id: string) => void>();
</script>

<template>
    <li>
        <button
            :id="`wos-nav-${id}`"
            :aria-current="active ? 'page' : undefined"
            :class="[
                'relative w-full flex items-center gap-2.5 px-3 py-[7px] rounded-md text-[13.5px] transition-colors duration-150 select-none group',
                active
                    ? 'bg-[#EFF6FF] text-[#2563EB] font-medium'
                    : 'text-[#374151] hover:bg-[#f3f4f6] hover:text-[#111827] font-normal',
            ]"
            @click="emit('navigate', id)"
        >
            <!-- Icon -->
            <svg
                v-if="icon"
                :class="[
                    'w-[18px] h-[18px] shrink-0 transition-colors',
                    active ? 'text-[#2563EB]' : 'text-[#6b7280] group-hover:text-[#374151]',
                ]"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" :d="icon" />
            </svg>

            <span class="flex-1 text-left truncate leading-none py-px">{{ label }}</span>

            <!-- Badge -->
            <span
                v-if="badge && badge > 0"
                class="flex-shrink-0 h-[18px] min-w-[18px] px-1.5 rounded-full text-[10.5px] font-semibold tabular-nums flex items-center justify-center bg-[#fef3c7] text-[#92400e]"
                :aria-label="`${badge} pending`"
            >
                {{ badge > 99 ? '99+' : badge }}
            </span>

            <!-- Submenu Chevron -->
            <ChevronRight 
                v-if="hasSubmenu"
                :class="[
                    'w-[14px] h-[14px] shrink-0 transition-colors opacity-60',
                    active ? 'text-[#2563EB]' : 'text-[#9ca3af] group-hover:text-[#6b7280]',
                ]"
            />
        </button>
    </li>
</template>
