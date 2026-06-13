<script setup lang="ts">
import type { PrimitiveProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import type { ButtonVariants } from "."
import { Primitive } from "reka-ui"
import { cn } from "@/lib/utils"
import { buttonVariants } from "."

interface Props extends PrimitiveProps {
  variant?: ButtonVariants["variant"]
  size?: ButtonVariants["size"]
  class?: HTMLAttributes["class"]
}

const props = withDefaults(defineProps<Props>(), {
  as: "button",
})
</script>

<template>
  <Primitive
    data-slot="button"
    :as="as"
    :as-child="asChild"
    :class="
      cn(
        buttonVariants({ variant, size }),
        'rounded-lg border border-transparent font-medium shadow-none',
        'focus-visible:ring-2 focus-visible:ring-[#0F62FE]/15 focus-visible:border-[#0F62FE]',
        'data-[slot=button]:disabled:shadow-none',
        variant === 'default' && 'bg-[#0F62FE] text-white hover:bg-[#0050E6]',
        variant === 'outline' &&
          'border-[#E0E0E0] dark:border-slate-600 bg-white dark:bg-slate-800 text-[#161616] dark:text-white hover:bg-[#F8FAFC] dark:hover:bg-slate-700 hover:text-[#161616] dark:hover:text-white',
        variant === 'secondary' &&
          'border-[#E0E0E0] dark:border-slate-600 bg-[#F4F4F4] dark:bg-slate-700 text-[#161616] dark:text-white hover:bg-[#EDEFF3] dark:hover:bg-slate-600',
        variant === 'ghost' &&
          'border-transparent bg-transparent text-[#525252] dark:text-slate-300 hover:bg-[#F4F4F4] dark:hover:bg-slate-700 hover:text-[#161616] dark:hover:text-white',
        variant === 'link' && 'text-[#0F62FE] hover:text-[#0050E6]',
        variant === 'destructive' && 'border-transparent',
        size === 'default' && 'h-10 px-4 py-2',
        size === 'sm' && 'h-9 rounded-lg px-3',
        size === 'lg' && 'h-11 rounded-lg px-5',
        size === 'icon' && 'size-10 rounded-lg',
        size === 'icon-sm' && 'size-9 rounded-lg',
        size === 'icon-lg' && 'size-11 rounded-lg',
        props.class,
      )
    "
  >
    <slot />
  </Primitive>
</template>
