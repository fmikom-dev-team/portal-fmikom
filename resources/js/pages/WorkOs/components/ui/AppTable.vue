<script setup lang="ts">
interface Column {
	key: string;
	label: string;
	width?: string;
	align?: "left" | "center" | "right";
}

defineProps<{
	columns: Column[];
	items?: any[];
	loading?: boolean;
	emptyTitle?: string;
	emptyDescription?: string;
}>();
</script>

<template>
    <div
        class="bg-white dark:bg-zinc-900 rounded-xl overflow-hidden ring-1 ring-gray-900/4 dark:ring-white/[0.06]"
        style="font-family: var(--wos-font); box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.04), 0 12px 32px rgba(0,0,0,0.05);"
    >
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <caption class="sr-only">Tabel Data WorkOS</caption>
                <thead>
                    <tr class="border-b border-[#f3f4f6] dark:border-zinc-800">
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            :class="[
                                'px-4 py-2.5 text-[11px] font-semibold text-[#6b7280] dark:text-zinc-500 uppercase tracking-wider bg-[#f9fafb] dark:bg-zinc-800/60 whitespace-nowrap',
                                col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : 'text-left',
                                col.width || '',
                            ]"
                        >
                            {{ col.label }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Loading skeleton -->
                    <template v-if="loading">
                        <tr v-for="i in 5" :key="`skeleton-${i}`">
                            <td v-for="col in columns" :key="col.key" class="px-4 py-3 border-b border-[#f9fafb] dark:border-zinc-800 last-of-type:border-0">
                                <div class="h-4 wos-shimmer rounded-md" :class="col.key === 'name' ? 'w-32' : 'w-20'" />
                            </td>
                        </tr>
                    </template>

                    <!-- Empty state -->
                    <template v-else-if="!items || items.length === 0">
                        <tr>
                            <td :colspan="columns.length" class="py-14 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-full bg-[#f3f4f6] dark:bg-zinc-800 flex items-center justify-center mb-3">
                                        <svg class="w-5 h-5 text-[#9ca3af] dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                    </div>
                                    <p class="text-[13px] font-medium text-[#374151] dark:text-zinc-300">{{ emptyTitle || 'No results' }}</p>
                                    <p v-if="emptyDescription" class="text-[12px] text-[#9ca3af] dark:text-zinc-600 mt-0.5">{{ emptyDescription }}</p>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <!-- Data rows via slot -->
                    <slot v-else :items="items" />
                </tbody>
            </table>
        </div>

        <!-- Footer slot (pagination, etc.) -->
        <div v-if="$slots.footer" class="px-4 py-3 border-t border-[#f3f4f6] dark:border-zinc-800 bg-[#f9fafb]/50 dark:bg-zinc-800/40">
            <slot name="footer" />
        </div>
    </div>
</template>
