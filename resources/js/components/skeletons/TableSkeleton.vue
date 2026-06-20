<script setup lang="ts">
import { Skeleton } from "@/components/ui/skeleton";

interface ColumnConfig {
  width?: string;
  hasAvatar?: boolean;
}

interface Props {
  rows?: number;
  columns?: ColumnConfig[];
}

withDefaults(defineProps<Props>(), {
  rows: 5,
  columns: () => [
    { width: 'w-1/4', hasAvatar: true },
    { width: 'w-1/6' },
    { width: 'w-1/6' },
    { width: 'w-1/12' }
  ]
});
</script>

<template>
  <div class="space-y-4 w-full">
    <!-- Header Controls -->
    <div class="flex justify-between items-center">
      <Skeleton class="h-10 w-48" />
      <Skeleton class="h-10 w-24" />
    </div>

    <!-- Table -->
    <div class="border border-slate-200 dark:border-slate-800 rounded-lg bg-card">
      <div v-for="r in rows" :key="r" class="flex items-center justify-between p-4 border-b border-slate-100 dark:border-slate-800 last:border-0">
        <div v-for="(col, cIdx) in columns" :key="cIdx" class="flex items-center space-x-3" :class="col.width">
          <Skeleton v-if="col.hasAvatar" class="h-8 w-8 rounded-full shrink-0" />
          <Skeleton class="h-4 w-full" />
        </div>
      </div>
    </div>
  </div>
</template>
