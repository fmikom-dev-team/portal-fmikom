<script setup lang="ts">
import { Skeleton } from "@/components/ui/skeleton";
import CardSkeleton from "./CardSkeleton.vue";
</script>

<template>
  <div class="space-y-8 w-full">
    <!-- Featured News Skeleton -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 border border-slate-150 dark:border-slate-800 p-6 rounded-[24px] bg-card items-center">
      <Skeleton class="lg:col-span-2 aspect-[16/9] w-full rounded-2xl" />
      <div class="space-y-4">
        <Skeleton class="h-4 w-24" />
        <Skeleton class="h-8 w-full" />
        <Skeleton class="h-8 w-5/6" />
        <Skeleton class="h-4 w-full" />
        <Skeleton class="h-4 w-2/3" />
        <div class="flex items-center space-x-3 pt-4">
          <Skeleton class="h-10 w-10 rounded-full" />
          <Skeleton class="h-4 w-24" />
        </div>
      </div>
    </div>

    <!-- News Grid Skeleton -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <CardSkeleton v-for="i in 3" :key="i" type="news" />
    </div>
  </div>
</template>
