<script setup lang="ts">
withDefaults(
	defineProps<{
		type?: "line" | "card" | "avatar" | "table";
		rows?: number;
		cols?: number;
	}>(),
	{
		type: "line",
		rows: 3,
		cols: 3,
	},
);
</script>

<template>
    <!-- Avatar row -->
    <div v-if="type === 'avatar'" class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-full wos-shimmer shrink-0" />
        <div class="flex-1 space-y-2">
            <div class="h-3.5 wos-shimmer rounded-md w-1/3" />
            <div class="h-3 wos-shimmer rounded-md w-1/2" />
        </div>
    </div>

    <!-- Card -->
    <div v-else-if="type === 'card'" class="rounded-xl border border-[#e5e7eb] dark:border-zinc-800 overflow-hidden">
        <div class="h-36 wos-shimmer" />
        <div class="p-4 space-y-2.5">
            <div class="h-4 wos-shimmer rounded-md w-2/3" />
            <div class="h-3.5 wos-shimmer rounded-md w-full" />
            <div class="h-3.5 wos-shimmer rounded-md w-4/5" />
        </div>
    </div>

    <!-- Table rows -->
    <template v-else-if="type === 'table'">
        <tr v-for="r in rows" :key="`sk-row-${r}`">
            <td v-for="c in cols" :key="`sk-col-${c}`" class="px-4 py-3 border-b border-[#f9fafb]">
                <div :class="['h-4 wos-shimmer rounded-md', c === 1 ? 'w-28' : c === cols ? 'w-12' : 'w-20']" />
            </td>
        </tr>
    </template>

    <!-- Default: lines -->
    <div v-else class="space-y-2.5">
        <div
            v-for="i in rows"
            :key="`sk-line-${i}`"
            :class="['h-4 wos-shimmer rounded-md', i === rows ? 'w-2/3' : i % 2 === 0 ? 'w-4/5' : 'w-full']"
        />
    </div>
</template>
