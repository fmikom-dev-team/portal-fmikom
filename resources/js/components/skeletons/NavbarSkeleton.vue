<script setup lang="ts">
import { Skeleton } from "@/components/ui/skeleton";
</script>

<template>
  <header class="flex h-16 items-center justify-between border-b border-slate-150 dark:border-slate-800 px-6 bg-card w-full">
    <!-- Breadcrumbs Skeleton -->
    <div class="flex items-center space-x-2">
      <Skeleton class="h-4 w-12" />
      <span class="text-slate-300">/</span>
      <Skeleton class="h-4 w-20" />
    </div>

    <!-- Actions & User Profile -->
    <div class="flex items-center space-x-4">
      <Skeleton class="h-8 w-8 rounded-full" />
      <Skeleton class="h-8 w-8 rounded-full" />
      <div class="flex items-center space-x-2 border-l border-slate-150 dark:border-slate-800 pl-4">
        <Skeleton class="h-8 w-8 rounded-full" />
        <div class="space-y-1 hidden md:block">
          <Skeleton class="h-3 w-16" />
          <Skeleton class="h-2 w-12" />
        </div>
      </div>
    </div>
  </header>
</template>
