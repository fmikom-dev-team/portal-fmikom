<script setup lang="ts">
import { useSkeleton } from "@/composables/useSkeleton";
import CardSkeleton from "./CardSkeleton.vue";

const { getResponsiveCount } = useSkeleton();
</script>

<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 w-full">
    <!-- Viewport count adapts dynamically to target device width -->
    <CardSkeleton v-for="i in getResponsiveCount(4, 1)" :key="i" />
  </div>
</template>
