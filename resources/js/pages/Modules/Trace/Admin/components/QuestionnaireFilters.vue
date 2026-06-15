<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { Search, SlidersHorizontal } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';

const props = defineProps<{
  filters: {
    search?: string;
    status?: string;
    year?: string;
  }
}>();

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || 'all');
const year = ref(props.filters.year || 'all');

const years = [
  { label: 'Tahun', value: 'all' },
  { label: '2026', value: '2026' },
  { label: '2025', value: '2025' },
  { label: '2024', value: '2024' },
];

const handleSearch = useDebounceFn(() => {
  router.get(
    '/admin/quesionnaires',
    { 
      search: search.value, 
      status: status.value === 'all' ? undefined : status.value,
      year: year.value === 'all' ? undefined : year.value
    },
    { preserveState: true, replace: true }
  );
}, 300);

watch([status, year], () => {
  handleSearch();
});
</script>

<template>
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div class="relative w-full max-w-xl">
      <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
      <Input
        v-model="search"
        placeholder="Cari kuesioner berdasarkan judul atau tahun..."
        class="h-12 pl-10 pr-4 bg-background/50 border-border/50 focus-visible:ring-primary/20"
        @input="handleSearch"
      />
    </div>

    <div class="flex items-center gap-3">
      <Select v-model="status">
        <SelectTrigger class="h-12 w-[140px] bg-background/50 border-border/50">
          <SelectValue placeholder="Semua Status" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="all">Semua Status</SelectItem>
          <SelectItem value="active">Aktif</SelectItem>
          <SelectItem value="closed">Ditutup</SelectItem>
        </SelectContent>
      </Select>

      <Select v-model="year">
        <SelectTrigger class="h-12 w-[120px] bg-background/50 border-border/50">
          <SelectValue placeholder="Tahun" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem v-for="y in years" :key="y.value" :value="y.value">
            {{ y.label }}
          </SelectItem>
        </SelectContent>
      </Select>

     
    </div>
  </div>
</template>
