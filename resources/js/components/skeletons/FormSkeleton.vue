<script setup lang="ts">
import { Skeleton } from "@/components/ui/skeleton";

interface FormSkeletonProps {
  fields?: number;
}

withDefaults(defineProps<FormSkeletonProps>(), {
  fields: 4,
});
</script>

<template>
  <div class="space-y-6 max-w-2xl">
    <div v-for="f in fields" :key="f" class="space-y-2">
      <Skeleton class="h-4 w-24" /> <!-- Label -->
      <Skeleton class="h-10 w-full rounded-md" /> <!-- Input Area -->
    </div>
    <div class="flex justify-end space-x-3 pt-4 border-t border-slate-150 dark:border-slate-800">
      <Skeleton class="h-10 w-24 rounded-md" /> <!-- Cancel Button -->
      <Skeleton class="h-10 w-32 rounded-md" /> <!-- Submit Button -->
    </div>
  </div>
</template>
