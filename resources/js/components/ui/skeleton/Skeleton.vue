<script setup lang="ts">
import type { HTMLAttributes } from "vue";
import { cn } from "@/lib/utils";

interface SkeletonProps {
	class?: HTMLAttributes["class"];
	width?: string;
	height?: string;
}

const props = defineProps<SkeletonProps>();
</script>

<template>
  <div
    data-slot="skeleton"
    :class="cn('animate-pulse rounded-md bg-slate-200/70 dark:bg-zinc-800', props.class)"
    :style="{ width: props.width, height: props.height }"
  />
</template>
