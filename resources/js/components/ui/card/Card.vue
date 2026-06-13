<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <div
    data-slot="card"
    :class="
      cn(
        'bg-card text-card-foreground flex flex-col gap-6 rounded-xl border border-[#E0E0E0] py-6 shadow-[0_16px_32px_-28px_rgba(15,23,42,0.22)]',
        props.class,
      )
    "
  >
    <slot />
  </div>
</template>
