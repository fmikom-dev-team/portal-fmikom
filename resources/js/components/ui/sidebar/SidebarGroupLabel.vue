<script setup lang="ts">
import type { PrimitiveProps } from "reka-ui";
import { Primitive } from "reka-ui";
import type { HTMLAttributes } from "vue";
import { cn } from "@/lib/utils";

const props = defineProps<
	PrimitiveProps & {
		class?: HTMLAttributes["class"];
	}
>();
</script>

<template>
  <Primitive
    data-slot="sidebar-group-label"
    data-sidebar="group-label"
    :as="as"
    :as-child="asChild"
    :class="cn(
      'text-sidebar-foreground/70 ring-sidebar-ring flex h-8 shrink-0 items-center rounded-md px-2 text-xs font-medium outline-hidden focus-visible:ring-2 [&>svg]:size-4 [&>svg]:shrink-0 transition-all duration-300 ease-in-out',
      'group-data-[collapsible=icon]:h-0 group-data-[collapsible=icon]:py-0 group-data-[collapsible=icon]:my-0 group-data-[collapsible=icon]:opacity-0 group-data-[collapsible=icon]:pointer-events-none',
      props.class)"
  >
    <slot />
  </Primitive>
</template>
