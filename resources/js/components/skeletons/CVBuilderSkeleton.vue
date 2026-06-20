<script setup lang="ts">
import { Skeleton } from "@/components/ui/skeleton";
</script>

<template>
  <div class="w-full flex flex-col md:flex-row gap-6">
    <!-- Builder Controls Sidebar -->
    <div class="w-full md:w-80 space-y-6 shrink-0 border border-slate-150 dark:border-slate-800 p-4 rounded-[20px] bg-card">
      <Skeleton class="h-10 w-full" />
      <div class="space-y-3">
        <Skeleton v-for="i in 5" :key="i" class="h-12 w-full" />
      </div>
    </div>

    <!-- CV Document Preview Sheet -->
    <div class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-8 shadow-sm aspect-[1/1.4] max-w-[700px] rounded-[24px] space-y-8 mx-auto md:mx-0">
      <div class="border-b border-slate-150 dark:border-slate-800 pb-6 space-y-3">
        <Skeleton class="h-8 w-2/5" />
        <Skeleton class="h-4 w-1/4" />
      </div>
      <div class="grid grid-cols-3 gap-6">
        <div class="col-span-1 space-y-4">
          <Skeleton class="h-5 w-12" />
          <Skeleton class="h-3 w-full" />
          <Skeleton class="h-3 w-5/6" />
        </div>
        <div class="col-span-2 space-y-6">
          <Skeleton class="h-5 w-24" />
          <Skeleton class="h-3 w-full" />
          <Skeleton class="h-3 w-3/4" />
        </div>
      </div>
    </div>
  </div>
</template>
