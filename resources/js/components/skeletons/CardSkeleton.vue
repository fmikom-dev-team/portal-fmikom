<script setup lang="ts">
import { Skeleton } from "@/components/ui/skeleton";

interface CardSkeletonProps {
  type?: 'portfolio' | 'news' | 'simple';
}

withDefaults(defineProps<CardSkeletonProps>(), {
  type: 'portfolio',
});
</script>

<template>
  <div class="border border-slate-150 dark:border-slate-800 rounded-[20px] overflow-hidden bg-card p-4 space-y-4">
    <!-- Media Box -->
    <Skeleton v-if="type !== 'simple'" class="w-full aspect-[16/10] rounded-2xl" />
    
    <!-- Header Details -->
    <div class="flex items-center space-x-3">
      <Skeleton v-if="type === 'portfolio'" class="h-10 w-10 rounded-full shrink-0" />
      <div class="space-y-2 w-full">
        <Skeleton class="h-4 w-3/4" />
        <Skeleton class="h-3 w-1/2" />
      </div>
    </div>

    <!-- Tags & Info -->
    <div v-if="type === 'portfolio'" class="flex space-x-2 pt-2">
      <Skeleton class="h-5 w-12 rounded-full" />
      <Skeleton class="h-5 w-16 rounded-full" />
      <Skeleton class="h-5 w-14 rounded-full" />
    </div>
  </div>
</template>
