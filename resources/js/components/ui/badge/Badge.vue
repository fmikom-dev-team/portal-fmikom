<script setup lang="ts">
import type { PrimitiveProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import type { BadgeVariants } from "."
import { reactiveOmit } from "@vueuse/core"
import { Primitive } from "reka-ui"
import { cn } from "@/lib/utils"
import { badgeVariants } from "."

const props = defineProps<PrimitiveProps & {
  variant?: BadgeVariants["variant"]
  class?: HTMLAttributes["class"]
}>()

const delegatedProps = reactiveOmit(props, "class")
</script>

<template>
  <Primitive
    data-slot="badge"
    :class="
      cn(
        badgeVariants({ variant }),
        'min-h-6 rounded-full border px-2.5 py-0.5 text-[12px] font-medium shadow-none',
        variant === 'default' && 'bg-[#0F62FE] text-white',
        variant === 'secondary' &&
          'border-[#D7DEE7] dark:border-slate-600 bg-[#F4F4F4] dark:bg-slate-700 text-[#525252] dark:text-slate-300',
        variant === 'outline' &&
          'border-[#D7DEE7] dark:border-slate-600 bg-white dark:bg-slate-800 text-[#525252] dark:text-slate-300 hover:bg-[#F8FAFC] dark:hover:bg-slate-700',
        variant === 'destructive' && 'bg-[#DA1E28] text-white',
        props.class,
      )
    "
    v-bind="delegatedProps"
  >
    <slot />
  </Primitive>
</template>
