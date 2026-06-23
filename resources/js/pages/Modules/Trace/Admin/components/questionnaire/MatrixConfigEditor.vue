<script setup lang="ts">
import { Trash2, PlusCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Separator } from '@/components/ui/separator';
import type { Pertanyaan } from '@/types/trace';

const props = defineProps<{
    question: Pertanyaan;
}>();

const emit = defineEmits<{
    (e: 'addRow'): void;
    (e: 'removeRow', index: number): void;
    (e: 'addColumn'): void;
    (e: 'removeColumn', index: number): void;
}>();
</script>

<template>
    <div
        v-if="question.tipe === 'matrix'"
        class="space-y-4 pl-4"
    >
        <!-- Column Labels -->
        <div class="space-y-2">
            <p class="text-[10px] font-black tracking-[0.2em] text-[#0C447C] uppercase">
                Label Skala (Kolom)
            </p>
            <div class="space-y-1.5">
                <div
                    v-for="(col, cIdx) in (question.meta?.columns || [])"
                    :key="cIdx"
                    class="group/col flex items-center gap-2"
                >
                    <span class="text-[10px] font-bold text-slate-400 tabular-nums w-5 text-right">{{ cIdx + 1 }}.</span>
                    <Input
                        v-model="question.meta.columns[cIdx]"
                        placeholder="Label kolom"
                        class="h-8 text-xs border-slate-200 dark:border-slate-700"
                    />
                    <Button
                        v-if="(question.meta?.columns?.length || 0) > 2"
                        variant="ghost"
                        size="icon"
                        class="h-6 w-6 text-muted-foreground/30 opacity-0 group-hover/col:opacity-100 hover:text-destructive"
                        @click="emit('removeColumn', cIdx)"
                    >
                        <Trash2 class="h-3 w-3" />
                    </Button>
                </div>
            </div>
            <Button
                variant="ghost"
                size="sm"
                class="h-7 gap-1.5 px-0 text-[10px] font-black tracking-widest text-[#0C447C] hover:text-[#0C447C]"
                @click="emit('addColumn')"
            >
                <PlusCircle class="h-3 w-3" />
                TAMBAH KOLOM
            </Button>
        </div>

        <Separator class="opacity-30" />

        <!-- Matrix Rows -->
        <div class="space-y-2">
            <p class="text-[10px] font-black tracking-[0.2em] text-[#0C447C] uppercase">
                Sub-Pertanyaan (Baris Matrix)
            </p>
            <div class="space-y-2">
                <div
                    v-for="(row, rIndex) in question.matrix_rows"
                    :key="rIndex"
                    class="group/row flex items-center gap-2"
                >
                    <div class="h-6 w-1 rounded-full bg-[#85B7EB] dark:bg-[#0C447C]"></div>
                    <Input
                        v-model="question.matrix_rows[rIndex]"
                        placeholder="Contoh: Integritas & Kejujuran"
                        class="h-9 border-none bg-transparent px-0 text-sm focus-visible:ring-0"
                    />
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-7 w-7 text-muted-foreground/30 opacity-0 group-hover/row:opacity-100 hover:text-destructive"
                        @click="emit('removeRow', Number(rIndex))"
                    >
                        <Trash2 class="h-3 w-3" />
                    </Button>
                </div>
            </div>

            <Button
                variant="ghost"
                size="sm"
                class="h-8 gap-2 px-0 text-[10px] font-black tracking-widest text-[#0C447C] hover:text-[#0C447C]"
                @click="emit('addRow')"
            >
                <PlusCircle class="h-3.5 w-3.5" />
                TAMBAH BARIS MATRIX
            </Button>
        </div>
    </div>
</template>
