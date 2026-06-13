<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { useVModel } from "@vueuse/core"
import { cn } from "@/lib/utils"

const props = defineProps<{
  defaultValue?: string | number
  modelValue?: string | number
  class?: HTMLAttributes["class"]
}>()

const emits = defineEmits<{
  (e: "update:modelValue", payload: string | number): void
}>()

const modelValue = useVModel(props, "modelValue", emits, {
  passive: true,
  defaultValue: props.defaultValue,
})
</script>

<template>
  <input
    v-model="modelValue"
    data-slot="input"
    :class="cn(
      'file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground border-[#C6CDD5] dark:border-slate-600 h-10 w-full min-w-0 rounded-lg border bg-white dark:bg-slate-800 px-3 py-2 text-sm text-[#161616] dark:text-white shadow-none transition-[color,box-shadow,border-color,background-color] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:bg-slate-100 dark:disabled:bg-slate-800 disabled:text-slate-500 disabled:opacity-100',
      'focus-visible:border-[#0F62FE] focus-visible:ring-2 focus-visible:ring-[#0F62FE]/15',
      'aria-invalid:border-[#DA1E28] aria-invalid:ring-2 aria-invalid:ring-[#DA1E28]/10',
      props.class,
    )"
  >
</template>
