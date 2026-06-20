<script setup lang="ts">
import { Skeleton } from "@/components/ui/skeleton";
</script>

<template>
  <div class="space-y-4 w-full max-w-3xl">
    <div v-for="i in 5" :key="i" class="border border-slate-150 dark:border-slate-800 p-4 rounded-[16px] bg-card flex items-center space-x-3">
      <Skeleton class="h-10 w-10 rounded-full shrink-0" />
      <div class="space-y-2 w-full">
        <Skeleton class="h-4 w-3/4" />
        <Skeleton class="h-3 w-1/4" />
      </div>
    </div>
  </div>
</template>
