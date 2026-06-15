<script setup lang="ts">
import { AlertCircle } from 'lucide-vue-next';
import {
  Card,
  CardContent,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import { KUESIONER_CATEGORIES, YEARS } from '@/utils/constants';

const form = defineModel<any>('form', { required: true });
</script>

<template>
  <Card class="border-t-8 border-t-blue-600 rounded-2xl shadow-sm border-x border-b">
    <CardContent class="pt-6 space-y-4">
      <div class="space-y-3">
        <div class="space-y-1">
          <Label class="text-[10px] font-black uppercase tracking-widest text-blue-600">Judul Kuesioner Utama</Label>
          <Input 
            v-model="form.judul" 
            placeholder="Masukkan Judul Kuesioner di sini..." 
            class="border-none bg-transparent px-0 text-3xl font-black focus-visible:ring-0 active:bg-slate-50/50"
            id="judul-input"
          />
        </div>
        <p v-if="form.errors.judul" class="text-xs font-bold text-destructive flex items-center gap-1">
          <AlertCircle class="h-3 w-3" />
          {{ form.errors.judul }}
        </p>
        
        <Input 
          v-model="form.subtitle" 
          placeholder="Sub-judul (contoh: Fakultas Ilmu Komputer)" 
          class="border-none bg-transparent px-0 text-lg font-medium text-muted-foreground focus-visible:ring-0"
        />
        <p v-if="form.errors.subtitle" class="text-xs font-medium text-destructive">{{ form.errors.subtitle }}</p>
      </div>
      
      <Separator />
      
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-2">
        <div class="space-y-2">
          <Label class="text-[10px] font-bold uppercase tracking-wider">Kategori</Label>
          <Select v-model="form.kategori">
            <SelectTrigger class="h-10 bg-slate-50 dark:bg-slate-900 border-none">
              <SelectValue placeholder="Pilih Kategori" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="cat in KUESIONER_CATEGORIES" :key="cat" :value="cat">{{ cat }}</SelectItem>
            </SelectContent>
          </Select>
          <p v-if="form.errors.kategori" class="text-xs text-destructive">{{ form.errors.kategori }}</p>
        </div>

        <div class="space-y-2">
          <Label class="text-[10px] font-bold uppercase tracking-wider">Tahun Tracer / Sasaran</Label>
          <Select v-model="form.tahun">
            <SelectTrigger class="h-10 bg-slate-50 dark:bg-slate-900 border-none">
              <SelectValue placeholder="Pilih Tahun" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="year in YEARS" :key="year" :value="year">{{ year }}</SelectItem>
            </SelectContent>
          </Select>
          <p v-if="form.errors.tahun" class="text-xs text-destructive">{{ form.errors.tahun }}</p>
        </div>

        <div class="space-y-2">
          <Label class="text-[10px] font-bold uppercase tracking-wider">Tanggal Dibuka</Label>
          <Input v-model="form.date_mulai" type="date" class="h-10 bg-slate-50 dark:bg-slate-900 border-none rounded-md" />
          <p v-if="form.errors.date_mulai" class="text-xs text-destructive flex items-center gap-1">
            <AlertCircle class="h-3 w-3" />
            {{ form.errors.date_mulai }}
          </p>
        </div>

        <div class="space-y-2">
          <Label class="text-[10px] font-bold uppercase tracking-wider">Tanggal Ditutup</Label>
          <Input v-model="form.date_selesai" type="date" class="h-10 bg-slate-50 dark:bg-slate-900 border-none rounded-md" />
          <p v-if="form.errors.date_selesai" class="text-xs text-destructive flex items-center gap-1">
            <AlertCircle class="h-3 w-3" />
            {{ form.errors.date_selesai }}
          </p>
        </div>
      </div>

      <div class="space-y-2 pt-2">
        <Label class="text-[10px] font-bold uppercase tracking-wider">Deskripsi</Label>
        <Textarea 
          v-model="form.deskripsi" 
          placeholder="Tambahkan deskripsi kuesioner Anda..." 
          class="resize-none border-dashed bg-slate-50/50 dark:bg-slate-900/50"
        />
        <p v-if="form.errors.deskripsi" class="text-xs text-destructive">{{ form.errors.deskripsi }}</p>
      </div>
    </CardContent>
  </Card>
</template>
