<script setup>
import { ref, computed } from 'vue';
import { Card, CardContent } from '@/components/ui/card';
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

// ── Section wizard navigation ──
const currentSection = ref(0);
const totalSections = computed(() => props.sections?.length || 0);
const activeSection = computed(() => props.sections?.[currentSection.value]);
const progress = computed(() => {
  if (totalSections.value <= 1) return 100;
  return Math.round(((currentSection.value + 1) / totalSections.value) * 100);
});

const goNext = () => {
  if (currentSection.value < totalSections.value - 1) currentSection.value++;
};
const goPrev = () => {
  if (currentSection.value > 0) currentSection.value--;
};

const getQuestionTypeLabel = (tipe) => {
  const map = {
    text: 'Teks Singkat',
    textarea: 'Teks Panjang',
    number: 'Angka',
    radio: 'Pilihan Ganda',
    checkbox: 'Kotak Centang',
    dropdown: 'Dropdown',
    scale: 'Skala',
    matrix: 'Matriks',
    date: 'Tanggal',
  };
  return map[tipe] || tipe;
};
</script>

<template>
  <div class="flex flex-col max-h-[80vh] overflow-hidden">
    <!-- ══ HEADER ══ -->
    <div class="shrink-0 px-6 pt-6 pb-4 bg-gradient-to-br from-[#0C447C] via-[#0F5299] to-[#1565C0] rounded-t-2xl text-white relative overflow-hidden">
      <!-- Decorative dots -->
      <div class="absolute inset-0 opacity-[0.06]"
           style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 18px 18px;" />

      <div class="relative z-10 text-center space-y-1.5">
        <div class="inline-flex items-center gap-1.5 rounded-full bg-white/15 backdrop-blur-sm px-3 py-1 text-[10px] font-bold uppercase tracking-widest">
          <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
          Pratinjau
        </div>
        <h1 class="text-xl font-black leading-tight">{{ judul || 'Judul Kuesioner' }}</h1>
        <p class="text-white/70 text-sm font-medium">{{ subtitle || 'Silakan isi kuesioner ini dengan jujur' }}</p>
      </div>
    </div>

    <!-- ══ PROGRESS BAR ══ -->
    <div v-if="totalSections > 1" class="shrink-0 px-6 pt-4 pb-2 bg-white dark:bg-slate-950">
      <div class="flex items-center justify-between mb-2">
        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
          Bagian {{ currentSection + 1 }} dari {{ totalSections }}
        </span>
        <span class="text-[11px] font-extrabold text-[#0C447C] dark:text-blue-400">
          {{ progress }}%
        </span>
      </div>
      <div class="h-1.5 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
        <div class="h-full rounded-full bg-gradient-to-r from-[#0C447C] to-[#2196F3] transition-all duration-500 ease-out"
             :style="{ width: progress + '%' }" />
      </div>

      <!-- Section pills -->
      <div class="flex gap-1.5 mt-3">
        <button v-for="(sec, i) in sections" :key="i"
                @click="currentSection = i"
                class="h-1.5 flex-1 rounded-full transition-all duration-300"
                :class="i === currentSection
                  ? 'bg-[#0C447C] dark:bg-blue-500 scale-y-125'
                  : i < currentSection
                    ? 'bg-[#0C447C]/30 dark:bg-blue-500/30'
                    : 'bg-slate-200 dark:bg-slate-700'"
        />
      </div>
    </div>

    <!-- ══ SECTION CONTENT (scrollable) ══ -->
    <div class="flex-1 overflow-y-auto px-6 py-5 bg-white dark:bg-slate-950 space-y-5">
      <template v-if="activeSection">
        <!-- Section Header -->
        <div class="flex items-start gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
          <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#0C447C]/10 dark:bg-blue-500/15 text-[#0C447C] dark:text-blue-400 font-black text-sm shrink-0">
            {{ currentSection + 1 }}
          </div>
          <div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 leading-snug">
              {{ activeSection.judul || activeSection.title || 'Bagian ' + (currentSection + 1) }}
            </h2>
            <p v-if="activeSection.deskripsi || activeSection.description"
               class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
              {{ activeSection.deskripsi || activeSection.description }}
            </p>
          </div>
        </div>

        <!-- Questions -->
        <div v-for="(q, qIndex) in (activeSection.pertanyaans || activeSection.questions || [])"
             :key="qIndex"
             class="group rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 p-5 transition-all duration-200 hover:border-slate-200 dark:hover:border-slate-700 hover:shadow-sm"
        >
          <!-- Question label -->
          <div class="flex items-start justify-between gap-3 mb-3">
            <Label class="text-[15px] font-bold leading-relaxed text-slate-800 dark:text-slate-100 flex-1">
              <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-[#0C447C]/10 dark:bg-blue-500/15 text-[#0C447C] dark:text-blue-400 text-xs font-black mr-2 shrink-0 translate-y-[1px]">
                {{ qIndex + 1 }}
              </span>
              {{ q.teks }}
              <span v-if="q.is_required" class="text-red-500 ml-0.5">*</span>
            </Label>
            <span class="shrink-0 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800 rounded-md px-2 py-0.5">
              {{ getQuestionTypeLabel(q.tipe) }}
            </span>
          </div>

          <!-- ── Text Input ── -->
          <Input v-if="q.tipe === 'text'" placeholder="Jawaban teks singkat..." disabled
                 class="bg-white dark:bg-slate-900 !opacity-70" />

          <!-- ── Textarea ── -->
          <Textarea v-if="q.tipe === 'textarea'" placeholder="Jawaban teks panjang..." disabled rows="3"
                    class="bg-white dark:bg-slate-900 !opacity-70 resize-none" />

          <!-- ── Number ── -->
          <Input v-if="q.tipe === 'number'" type="number" placeholder="0" disabled
                 class="bg-white dark:bg-slate-900 !opacity-70 max-w-[200px]" />

          <!-- ── Date ── -->
          <Input v-if="q.tipe === 'date'" type="date" disabled
                 class="bg-white dark:bg-slate-900 !opacity-70 max-w-[220px]" />

          <!-- ── Radio ── -->
          <RadioGroup v-if="q.tipe === 'radio'" class="space-y-2 mt-1">
            <label v-for="opt in q.opsi_jawabans" :key="opt.label"
                   class="flex items-center gap-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3 cursor-default opacity-70">
              <div class="w-[18px] h-[18px] rounded-full border-2 border-slate-300 dark:border-slate-600 shrink-0" />
              <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ opt.label }}</span>
            </label>
          </RadioGroup>

          <!-- ── Checkbox ── -->
          <div v-if="q.tipe === 'checkbox'" class="space-y-2 mt-1">
            <label v-for="opt in q.opsi_jawabans" :key="opt.label"
                   class="flex items-center gap-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3 cursor-default opacity-70">
              <div class="w-[18px] h-[18px] rounded-[5px] border-2 border-slate-300 dark:border-slate-600 shrink-0" />
              <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ opt.label }}</span>
            </label>
          </div>

          <!-- ── Dropdown ── -->
          <div v-if="q.tipe === 'dropdown'"
               class="flex items-center justify-between rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3 opacity-70 max-w-sm">
            <span class="text-sm text-slate-400">Pilih jawaban...</span>
            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
          </div>

          <!-- ── Scale / Likert ── -->
          <div v-if="q.tipe === 'scale'" class="mt-1">
            <div class="flex justify-between px-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase mb-2">
              <span>{{ q.meta?.scale_label_min || 'Sangat Tidak Setuju' }}</span>
              <span>{{ q.meta?.scale_label_max || 'Sangat Setuju' }}</span>
            </div>
            <div class="flex items-center justify-center gap-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 p-4">
              <div v-for="n in ((q.meta?.scale_max || 5) - (q.meta?.scale_min || 1) + 1)" :key="n"
                   class="flex flex-col items-center gap-1.5">
                <div class="w-10 h-10 rounded-full border-2 border-slate-200 dark:border-slate-700 flex items-center justify-center text-sm font-bold text-slate-400 dark:text-slate-500 hover:border-[#0C447C] hover:text-[#0C447C] transition-colors cursor-default">
                  {{ (q.meta?.scale_min || 1) + n - 1 }}
                </div>
              </div>
            </div>
          </div>

          <!-- ── Matrix ── -->
          <div v-if="q.tipe === 'matrix'" class="mt-1 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
            <table class="w-full text-sm">
              <thead>
                <tr class="bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700">
                  <th class="p-3 text-left font-bold text-slate-600 dark:text-slate-400 text-xs uppercase tracking-wide min-w-[180px]">
                    Pernyataan
                  </th>
                  <th v-for="col in (q.meta?.columns?.length ? q.meta.columns : ['1','2','3','4','5'])"
                      :key="col"
                      class="p-3 text-center font-bold text-slate-600 dark:text-slate-400 text-xs uppercase tracking-wide">
                    {{ col }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row, rIdx) in (q.matrix_rows || q.meta?.rows || [])" :key="row"
                    class="border-b last:border-0 border-slate-100 dark:border-slate-800"
                    :class="rIdx % 2 === 0 ? 'bg-white dark:bg-slate-950' : 'bg-slate-50/50 dark:bg-slate-900/30'">
                  <td class="p-3 font-medium text-slate-700 dark:text-slate-300">{{ row }}</td>
                  <td v-for="col in (q.meta?.columns?.length ? q.meta.columns : ['1','2','3','4','5'])"
                      :key="col" class="p-3 text-center">
                    <div class="w-5 h-5 rounded-full border-2 border-slate-300 dark:border-slate-600 mx-auto" />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>
    </div>

    <!-- ══ FOOTER NAVIGATION ══ -->
    <div class="shrink-0 px-6 py-4 bg-white dark:bg-slate-950 border-t border-slate-100 dark:border-slate-800 rounded-b-2xl">
      <div class="flex items-center justify-between">
        <button v-if="totalSections > 1"
                @click="goPrev"
                :disabled="currentSection === 0"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold transition-all"
                :class="currentSection === 0
                  ? 'text-slate-300 dark:text-slate-600 cursor-not-allowed'
                  : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
          </svg>
          Sebelumnya
        </button>
        <span v-else />

        <div class="flex items-center gap-3">
          <span v-if="totalSections > 1" class="text-xs font-medium text-slate-400">
            {{ currentSection + 1 }} / {{ totalSections }}
          </span>
          <button v-if="totalSections > 1 && currentSection < totalSections - 1"
                  @click="goNext"
                  class="inline-flex items-center gap-2 rounded-xl bg-[#0C447C] hover:bg-[#0F5299] text-white px-5 py-2.5 text-sm font-bold transition-all shadow-sm">
            Selanjutnya
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </button>
          <div v-else-if="totalSections > 1"
               class="inline-flex items-center gap-2 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-5 py-2.5 text-sm font-bold">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            Bagian Terakhir
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
