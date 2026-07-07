<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Pertanyaan } from '@/types/trace';

const props = defineProps<{
    question: Pertanyaan;
}>();
</script>

<template>
    <div
        v-if="question.tipe === 'scale'"
        class="space-y-3 pl-4"
    >
        <p class="text-[10px] font-black tracking-[0.2em] text-emerald-600 uppercase">
            Konfigurasi Skala
        </p>
        <div class="grid grid-cols-2 gap-3">
            <div class="space-y-1">
                <Label class="text-[10px] font-bold text-slate-500">Nilai Minimum</Label>
                <Input
                    v-model.number="question.meta.scale_min"
                    type="number"
                    :min="0"
                    placeholder="1"
                    class="h-8 text-xs"
                    @change="() => { if (!question.meta.scale_min && question.meta.scale_min !== 0) question.meta.scale_min = 1; }"
                />
            </div>
            <div class="space-y-1">
                <Label class="text-[10px] font-bold text-slate-500">Nilai Maksimum</Label>
                <Input
                    v-model.number="question.meta.scale_max"
                    type="number"
                    :min="(question.meta?.scale_min || 1) + 1"
                    placeholder="5"
                    class="h-8 text-xs"
                    @change="() => { if (!question.meta.scale_max) question.meta.scale_max = 5; }"
                />
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div class="space-y-1">
                <Label class="text-[10px] font-bold text-slate-500">Label Kiri (Min)</Label>
                <Input
                    v-model="question.meta.scale_label_min"
                    placeholder="Sangat Tidak Setuju"
                    class="h-8 text-xs"
                />
            </div>
            <div class="space-y-1">
                <Label class="text-[10px] font-bold text-slate-500">Label Kanan (Max)</Label>
                <Input
                    v-model="question.meta.scale_label_max"
                    placeholder="Sangat Setuju"
                    class="h-8 text-xs"
                />
            </div>
        </div>
        <!-- Preview -->
        <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl bg-slate-50 px-3 py-3 sm:px-4 dark:bg-slate-800/50">
            <span class="text-[9px] font-bold text-slate-400">{{ question.meta?.scale_label_min || 'Min' }}</span>
            <div class="flex flex-wrap justify-center gap-1.5">
                <div
                    v-for="n in ((question.meta?.scale_max || 5) - (question.meta?.scale_min || 1) + 1)"
                    :key="n"
                    class="flex h-6 w-6 items-center justify-center rounded-full border border-slate-200 text-[10px] font-bold text-slate-500 sm:h-7 sm:w-7 dark:border-slate-700"
                >
                    {{ (question.meta?.scale_min || 1) + n - 1 }}
                </div>
            </div>
            <span class="text-[9px] font-bold text-slate-400">{{ question.meta?.scale_label_max || 'Max' }}</span>
        </div>
    </div>
</template>
