<script setup>
import { computed } from 'vue';
// Pastikan kamu sudah install shadcn card (npx shadcn-ui@latest add card)
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

const props = defineProps({
  label: String,
  value: String,
  trend: String,
  subValue: String,
  subLabel: String,
  isTargetMeta: Boolean,
  color: { type: String, default: 'blue' }
});

// Warna teks untuk trend berdasarkan prop color
const trendColorClass = computed(() => {
  return {
    'text-blue-600': props.color === 'blue',
    'text-green-600': props.color === 'green',
    'text-gray-600': props.color === 'gray',
  };
});
</script>

<template>
  <Card class="overflow-hidden border-gray-100 shadow-sm">
    <CardHeader class="pb-2 p-6">
      <CardTitle class="text-xs font-bold text-gray-500 tracking-wider uppercase">
        {{ label }}
      </CardTitle>
    </CardHeader>
    
    <CardContent class="p-6 pt-0">
      <div class="flex items-end">
        <h2 class="text-4xl font-extrabold text-gray-900 tracking-tight">
          {{ value }}
        </h2>
        
        <span v-if="trend" class="ml-2 mb-1 text-sm font-bold" :class="trendColorClass">
          {{ trend }}
        </span>

        <div v-if="subValue" class="ml-4 mb-1 border-l pl-4 border-gray-100">
          <p class="text-sm font-bold text-gray-400 leading-none">
            {{ subValue }}
          </p>
          <p class="text-[10px] font-medium text-gray-400 uppercase mt-1">
            {{ subLabel }}
          </p>
        </div>
      </div>
    </CardContent>
  </Card>
</template>