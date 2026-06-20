<script setup lang="ts">
import { Skeleton } from "@/components/ui/skeleton";
</script>

<template>
  <div class="flex h-[calc(100vh-10rem)] border border-slate-150 dark:border-slate-800 rounded-[24px] bg-card overflow-hidden w-full">
    <!-- Conversations List Sidebar -->
    <div class="w-80 border-r border-slate-150 dark:border-slate-800 flex flex-col p-4 space-y-4 shrink-0 hidden md:flex">
      <Skeleton class="h-10 w-full rounded-lg" />
      <div class="space-y-4 overflow-y-auto flex-1">
        <div v-for="i in 5" :key="i" class="flex items-center space-x-3">
          <Skeleton class="h-12 w-12 rounded-full shrink-0" />
          <div class="space-y-2 w-full">
            <Skeleton class="h-4 w-3/4" />
            <Skeleton class="h-3 w-1/2" />
          </div>
        </div>
      </div>
    </div>

    <!-- Active Chat Window -->
    <div class="flex-1 flex flex-col">
      <!-- Active Chat Header -->
      <div class="h-16 border-b border-slate-150 dark:border-slate-800 p-4 flex items-center space-x-3">
        <Skeleton class="h-10 w-10 rounded-full" />
        <div class="space-y-1">
          <Skeleton class="h-4 w-32" />
          <Skeleton class="h-3 w-16" />
        </div>
      </div>

      <!-- Messages Body -->
      <div class="flex-1 p-4 space-y-6 overflow-y-auto">
        <div v-for="i in 3" :key="i" class="flex flex-col space-y-2" :class="{ 'items-end': i === 2 }">
          <div class="flex items-end space-x-2" :class="{ 'flex-row-reverse space-x-reverse': i === 2 }">
            <Skeleton class="h-8 w-8 rounded-full shrink-0" />
            <Skeleton class="h-12 w-48 rounded-2xl" />
          </div>
          <Skeleton class="h-3 w-12" />
        </div>
      </div>

      <!-- Input Bar -->
      <div class="p-4 border-t border-slate-150 dark:border-slate-800 flex items-center space-x-2">
        <Skeleton class="h-10 w-10 rounded-full" />
        <Skeleton class="h-10 w-full rounded-lg" />
        <Skeleton class="h-10 w-10 rounded-full" />
      </div>
    </div>
  </div>
</template>
