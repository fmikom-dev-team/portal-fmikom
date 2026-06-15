<script setup>
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps({
  judul: String,
  subtitle: String,
  sections: Array
});
</script>

<template>
  <div class="space-y-10 py-6 max-h-[80vh] overflow-y-auto px-1">
    <div class="text-center space-y-2">
      <h1 class="text-3xl font-black text-slate-900 dark:text-white">{{ judul || 'Judul Kuesioner' }}</h1>
      <p class="text-slate-500 font-medium">{{ subtitle || 'Silakan isi kuesioner ini dengan jujur' }}</p>
    </div>

    <div v-for="(section, sIndex) in sections" :key="sIndex" class="space-y-6">
      <div class="border-l-4 border-blue-600 pl-4 py-1">
        <h2 class="text-xl font-bold text-slate-800 dark:text-slate-200">{{ section.judul }}</h2>
        <p v-if="section.deskripsi" class="text-sm text-slate-500 mt-1">{{ section.deskripsi }}</p>
      </div>

      <div class="space-y-4">
        <Card v-for="(q, qIndex) in section.pertanyaans" :key="qIndex" class="shadow-sm border-slate-200 dark:border-slate-800">
          <CardContent class="pt-6 space-y-4">
            <div class="space-y-2">
              <Label class="text-base font-bold leading-relaxed">
                {{ qIndex + 1 }}. {{ q.teks }}
                <span v-if="q.is_required" class="text-red-500 ml-1">*</span>
              </Label>
              
              <!-- Text Input -->
              <Input v-if="q.tipe === 'text'" :placeholder="'Jawaban Anda...'" disabled />
              
              <!-- Number Input -->
              <Input v-if="q.tipe === 'number'" type="number" :placeholder="'0'" disabled />

              <!-- Radio Group -->
              <RadioGroup v-if="q.tipe === 'radio'" class="space-y-2 pt-2">
                <div v-for="opt in q.opsi_jawabans" :key="opt.label" class="flex items-center space-x-3">
                  <RadioGroupItem :value="opt.label" disabled />
                  <Label class="font-medium opacity-80 cursor-default">{{ opt.label }}</Label>
                </div>
              </RadioGroup>

              <!-- Checkbox Group -->
              <div v-if="q.tipe === 'checkbox'" class="space-y-3 pt-2">
                <div v-for="opt in q.opsi_jawabans" :key="opt.label" class="flex items-center space-x-3">
                  <Checkbox :id="opt.label" disabled />
                  <Label :for="opt.label" class="font-medium opacity-80 cursor-default">{{ opt.label }}</Label>
                </div>
              </div>

              <!-- Dropdown -->
              <Select v-if="q.tipe === 'dropdown'" disabled>
                <SelectTrigger>
                  <SelectValue placeholder="Pilih jawaban..." />
                </SelectTrigger>
              </Select>

              <!-- Scale / Likert -->
              <div v-if="q.tipe === 'scale'" class="space-y-2">
                <div class="flex justify-between px-4 text-xs font-black tracking-widest text-slate-400 uppercase">
                    <span>{{ q.meta?.scale_label_min || 'Min' }}</span>
                    <span>{{ q.meta?.scale_label_max || 'Max' }}</span>
                </div>
                <div class="flex justify-between items-center max-w-md mx-auto py-4 px-4 bg-slate-50 dark:bg-slate-900 rounded-2xl">
                   <div v-for="n in ((q.meta?.scale_max || 5) - (q.meta?.scale_min || 1) + 1)" :key="n" class="flex flex-col items-center gap-3">
                      <div class="w-10 h-10 rounded-full border-2 border-slate-300 dark:border-slate-700 flex items-center justify-center text-sm font-bold text-slate-400">
                          {{ (q.meta?.scale_min || 1) + n - 1 }}
                      </div>
                   </div>
                </div>
              </div>

              <!-- Matrix Questions -->
              <div v-if="q.tipe === 'matrix'" class="overflow-x-auto border rounded-xl">
                <table class="w-full text-sm">
                  <thead class="bg-slate-50 dark:bg-slate-900 border-b">
                    <tr>
                      <th class="p-4 text-left font-bold min-w-[200px]">Pernyataan</th>
                      <th v-for="col in (q.meta?.columns?.length ? q.meta.columns : ['1','2','3','4','5'])" :key="col" class="p-4 text-center font-bold">{{ col }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="row in q.matrix_rows" :key="row" class="border-b last:border-0">
                      <td class="p-4 font-medium">{{ row }}</td>
                      <td v-for="col in (q.meta?.columns?.length ? q.meta.columns : ['1','2','3','4','5'])" :key="col" class="p-4 text-center">
                        <div class="w-5 h-5 rounded-full border-2 border-slate-300 dark:border-slate-700 mx-auto opacity-50"></div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <div class="pt-8 border-t border-dashed text-center">
        <p class="text-xs text-slate-400 font-medium italic">--- Akhir dari Kuesioner ---</p>
    </div>
  </div>
</template>
