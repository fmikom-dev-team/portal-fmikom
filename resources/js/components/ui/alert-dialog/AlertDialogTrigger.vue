<script setup lang="ts">
import { AlertDialogTrigger } from "reka-ui";
import type { HTMLAttributes } from "vue";

const props = defineProps<{ asChild?: boolean; class?: HTMLAttributes["class"] }>();
</script>

<template>
  <AlertDialogTrigger
    data-slot="alert-dialog-trigger"
    v-bind="props"
  >
    <slot />
  </AlertDialogTrigger>
</template>
