<script setup lang="ts">
import { reactiveOmit } from "@vueuse/core";
import { AlertDialogDescription, useForwardProps } from "reka-ui";
import type { HTMLAttributes } from "vue";
import { cn } from "@/lib/utils";

const props = defineProps<{ class?: HTMLAttributes["class"] }>();

const delegatedProps = reactiveOmit(props, "class");

const forwardedProps = useForwardProps(delegatedProps);
</script>

<template>
  <AlertDialogDescription
    data-slot="alert-dialog-description"
    v-bind="forwardedProps"
    :class="cn('text-muted-foreground text-sm', props.class)"
  >
    <slot />
  </AlertDialogDescription>
</template>
