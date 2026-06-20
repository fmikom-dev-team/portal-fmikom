<script setup lang="ts">
import { Skeleton } from "@/components/ui/skeleton";
</script>

<template>
  <div class="w-64 h-screen border-r border-slate-150 dark:border-slate-800 bg-sidebar p-4 flex flex-col space-y-6 shrink-0">
    <!-- Brand Info -->
    <div class="flex items-center space-x-3">
      <Skeleton class="h-9 w-9 rounded-lg shrink-0" />
      <div class="space-y-2 w-full">
        <Skeleton class="h-4 w-28" />
        <Skeleton class="h-3 w-16" />
      </div>
    </div>

    <!-- Nav Links List -->
    <div class="space-y-4 flex-1">
      <div v-for="i in 6" :key="i" class="flex items-center space-x-3 py-1">
        <Skeleton class="h-5 w-5 rounded-md shrink-0" />
        <Skeleton class="h-4 w-32" />
      </div>
    </div>

    <!-- User Section -->
    <div class="flex items-center space-x-3 border-t border-slate-150 dark:border-slate-800 pt-4">
      <Skeleton class="h-10 w-10 rounded-full shrink-0" />
      <div class="space-y-2 w-full">
        <Skeleton class="h-4 w-24" />
        <Skeleton class="h-3 w-32" />
      </div>
    </div>
  </div>
</template>
