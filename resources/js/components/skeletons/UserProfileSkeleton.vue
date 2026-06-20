<script setup lang="ts">
import { Skeleton } from "@/components/ui/skeleton";
</script>

<template>
  <div class="space-y-6 w-full max-w-6xl mx-auto">
    <!-- Profile Header Skeleton -->
    <div class="relative border border-slate-150 dark:border-slate-800 rounded-[24px] bg-card overflow-hidden">
      <!-- Banner -->
      <Skeleton class="w-full h-48 sm:h-64" />
      <!-- Header Meta -->
      <div class="p-6 pt-16 sm:pt-6 flex flex-col sm:flex-row items-center sm:items-start justify-between gap-4">
        <!-- Avatar and Username info -->
        <div class="absolute -top-16 sm:static flex flex-col sm:flex-row items-center sm:items-end gap-4">
          <Skeleton class="h-28 w-28 sm:h-32 sm:w-32 rounded-full border-4 border-white dark:border-slate-900 shrink-0" />
          <div class="space-y-2 text-center sm:text-left">
            <Skeleton class="h-6 w-48 mx-auto sm:mx-0" />
            <Skeleton class="h-4 w-32 mx-auto sm:mx-0" />
            <Skeleton class="h-3 w-40 mx-auto sm:mx-0" />
          </div>
        </div>
        <Skeleton class="h-10 w-32 rounded-xl mt-4 sm:mt-0" />
      </div>
    </div>

    <!-- Tabs List Skeleton -->
    <div class="flex space-x-4 border-b border-slate-150 dark:border-slate-850 pb-2">
      <Skeleton v-for="i in 4" :key="i" class="h-8 w-20 rounded" />
    </div>

    <!-- Tab Content Area Skeleton -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="md:col-span-2 space-y-6">
        <div class="border border-slate-150 dark:border-slate-800 p-6 rounded-[20px] bg-card space-y-4">
          <Skeleton class="h-5 w-32" />
          <Skeleton class="h-4 w-full" />
          <Skeleton class="h-4 w-5/6" />
          <Skeleton class="h-4 w-3/4" />
        </div>
      </div>
      <div class="border border-slate-150 dark:border-slate-800 p-6 rounded-[20px] bg-card space-y-4">
        <Skeleton class="h-5 w-24" />
        <div class="flex flex-wrap gap-2">
          <Skeleton v-for="i in 5" :key="i" class="h-6 w-16 rounded-full" />
        </div>
      </div>
    </div>
  </div>
</template>
