<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import Button from '@/components/ui/button/Button.vue';
import type { CTAItem } from '@/types';

const props = defineProps<{
  linkCta: CTAItem[];
}>();
</script>

<template>
  <div class="relative z-10 flex flex-wrap items-center gap-4">
    <Button 
      v-for="cta in props.linkCta" 
      :key="cta.title" 
      :variant="cta.variant"
      as-child
      class="h-12 rounded-xl px-8 min-w-40 text-[15px] font-bold transition-all active:scale-95 border"
      :class="[
        cta.variant === 'default' 
          ? 'bg-[#2563EB] border-[#2563EB] text-white hover:bg-[#1d4ed8] shadow-md shadow-blue-500/20' 
          : 'bg-white border-border text-foreground hover:bg-gray-50 hover:shadow-sm'
      ]"
    >
      <Link :href="cta.href" class="flex items-center justify-center">
        <component 
          :is="cta.icon" 
          v-if="cta.icon" 
          class="mr-2.5 h-5 w-5 stroke-[2.5px]" 
        /> 
        {{ cta.title }}
      </Link>
    </Button>
  </div>
</template>

<style scoped>
button:focus-visible {
  outline: 2px solid #2563EB;
  outline-offset: 2px;
}
</style>