<script setup lang="ts">
import { AlertDialogRoot, useForwardPropsEmits } from "reka-ui";

const props = defineProps<{
  open?: boolean;
  defaultOpen?: boolean;
}>();

const emits = defineEmits<{
  'update:open': [value: boolean];
}>();

const forwarded = useForwardPropsEmits(props, emits);
</script>

<template>
  <AlertDialogRoot
    v-slot="slotProps"
    data-slot="alert-dialog"
    v-bind="forwarded"
  >
    <slot v-bind="slotProps" />
  </AlertDialogRoot>
</template>
