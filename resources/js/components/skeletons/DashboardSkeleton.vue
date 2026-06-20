<script setup lang="ts">
import { Skeleton } from "@/components/ui/skeleton";
</script>

<template>
  <div class="space-y-6 w-full">
    <!-- Welcome Header Skeleton -->
    <div class="space-y-2">
      <Skeleton class="h-8 w-64" />
      <Skeleton class="h-4 w-96" />
    </div>

    <!-- Stats Grid Skeleton -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <div v-for="i in 4" :key="i" class="border border-slate-150 dark:border-slate-800 p-6 rounded-[20px] bg-card space-y-3">
        <div class="flex items-center justify-between">
          <Skeleton class="h-4 w-20" />
          <Skeleton class="h-8 w-8 rounded-full" />
        </div>
        <Skeleton class="h-8 w-28" />
        <Skeleton class="h-3 w-40" />
      </div>
    </div>

    <!-- Chart & Activity Panels -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <!-- Main Visual Analytics Area -->
      <div class="lg:col-span-2 border border-slate-150 dark:border-slate-800 p-6 rounded-[20px] bg-card space-y-4">
        <div class="flex justify-between items-center">
          <Skeleton class="h-5 w-36" />
          <Skeleton class="h-8 w-24" />
        </div>
        <Skeleton class="h-64 w-full rounded-xl" />
      </div>

      <!-- Quick Activities Panel -->
      <div class="border border-slate-150 dark:border-slate-800 p-6 rounded-[20px] bg-card space-y-4">
        <Skeleton class="h-5 w-32" />
        <div class="space-y-4">
          <div v-for="i in 3" :key="i" class="flex items-center space-x-3">
            <Skeleton class="h-10 w-10 rounded-full" />
            <div class="space-y-2 w-full">
              <Skeleton class="h-4 w-3/4" />
              <Skeleton class="h-3 w-1/2" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
