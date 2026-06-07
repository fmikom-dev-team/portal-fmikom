<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import {
	SidebarGroup,
	SidebarGroupLabel,
	SidebarMenu,
	SidebarMenuButton,
	SidebarMenuItem,
} from "@/components/ui/sidebar";
import { useCurrentUrl } from "@/composables/useCurrentUrl";
import type { NavItem } from "@/types";

defineProps<{
	items: NavItem[];
}>();

const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <SidebarGroup class="px-3 py-6">
        <SidebarGroupLabel class="mb-4 px-2 text-xs font-bold uppercase tracking-wider text-gray-400">Menu Utama</SidebarGroupLabel>
        <SidebarMenu class="space-y-1.5">
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="isCurrentUrl(item.href)"
                    :tooltip="item.title"
                    class="relative h-12 w-full overflow-hidden transition-all duration-300"
                >
                    <Link :href="item.href" class="flex w-full items-center gap-4 px-3"
                          :class="isCurrentUrl(item.href) 
                            ? 'font-bold text-[#2563EB] bg-blue-50/50 dark:bg-[#2563EB]/10' 
                            : 'font-medium text-gray-500 hover:text-gray-900 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-zinc-800/50'">
                        
                        <!-- Left Active Indicator Pill -->
                        <div 
                            class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 rounded-r-full bg-[#2563EB] transition-all duration-300"
                            :class="isCurrentUrl(item.href) ? 'h-8 opacity-100' : 'h-0 opacity-0'"
                        ></div>

                        <component 
                            :is="item.icon" 
                            class="h-[22px] w-[22px] shrink-0 transition-colors"
                            :class="isCurrentUrl(item.href) ? 'text-[#2563EB]' : 'text-gray-400 group-hover:text-gray-600'" 
                        />
                        <span class="text-[15px] truncate">{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
