<script setup lang="ts">
import { AlertDialogAction } from "reka-ui";
import type { HTMLAttributes } from "vue";
import { cn } from "@/lib/utils";
import { buttonVariants } from "@/components/ui/button";

const props = defineProps<{ class?: HTMLAttributes["class"] }>();
</script>

<template>
  <AlertDialogAction
    data-slot="alert-dialog-action"
    v-bind="props"
    :class="cn(buttonVariants(), props.class)"
  >
    <slot />
  </AlertDialogAction>
</template>
