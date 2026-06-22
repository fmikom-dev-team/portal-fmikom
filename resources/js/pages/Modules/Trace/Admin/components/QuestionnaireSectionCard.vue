<script setup lang="ts">
import {
    Trash2,
    PlusCircle,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import QuestionEditorCard from './questionnaire/QuestionEditorCard.vue';
import ChoiceOptionsEditor from './questionnaire/ChoiceOptionsEditor.vue';
import ScaleConfigEditor from './questionnaire/ScaleConfigEditor.vue';
import MatrixConfigEditor from './questionnaire/MatrixConfigEditor.vue';
import type { KuesionerSection } from '@/types/trace';

const section = defineModel<KuesionerSection>('section', { required: true });

const { sIndex, totalSections } = defineProps<{
    sIndex: number;
    totalSections: number;
}>();

const emit = defineEmits<{
    (e: 'remove'): void;
}>();

// ─── Question CRUD ───
const addQuestion = () => {
    section.value.pertanyaans.push({
        teks: 'Pertanyaan Baru',
        tipe: 'text',
        tipe_data: 'text',
        is_required: false,
        meta: {
            kategori: 'Umum',
            acuan: [],
            acuan_text: '',
            target_table: 'detail_jawabans',
            scale_min: 1,
            scale_max: 5,
            scale_label_min: '',
            scale_label_max: '',
            columns: ['1', '2', '3', '4', '5'],
        },
        opsi_jawabans: [],
        matrix_rows: [],
    });
};

const duplicateQuestion = (qIndex: number) => {
    const original = section.value.pertanyaans[qIndex];
    const clone = JSON.parse(JSON.stringify(original));
    delete clone.id;
    clone.opsi_jawabans = (clone.opsi_jawabans || []).map((o: Record<string, unknown>) => {
        const copy = { ...o };
        delete copy.id;

        return copy;
    });
    section.value.pertanyaans.splice(qIndex + 1, 0, clone);
};

const removeQuestion = (qIndex: number) => {
    section.value.pertanyaans.splice(qIndex, 1);
};

const addOption = (qIndex: number) => {
    const question = section.value.pertanyaans[qIndex];

    if (!question.opsi_jawabans) {
        question.opsi_jawabans = [];
    }

    question.opsi_jawabans.push({
        label: 'Opsi ' + (question.opsi_jawabans.length + 1),
        skor: 0,
    });
};

const removeOption = (qIndex: number, optionIndex: number) => {
    section.value.pertanyaans[qIndex].opsi_jawabans.splice(optionIndex, 1);
};

const addMatrixRow = (qIndex: number) => {
    const question = section.value.pertanyaans[qIndex];

    if (!question.matrix_rows) {
        question.matrix_rows = [];
    }

    question.matrix_rows.push('Baris Baru');
};

const removeMatrixRow = (qIndex: number, rIndex: number) => {
    section.value.pertanyaans[qIndex].matrix_rows.splice(rIndex, 1);
};

// ─── Matrix Columns ───
const addMatrixColumn = (qIndex: number) => {
    const question = section.value.pertanyaans[qIndex];
    if (!question.meta) question.meta = {};
    if (!question.meta.columns) question.meta.columns = [];
    question.meta.columns.push(String(question.meta.columns.length + 1));
};

const removeMatrixColumn = (qIndex: number, colIndex: number) => {
    section.value.pertanyaans[qIndex].meta.columns.splice(colIndex, 1);
};
</script>

<template>
    <Card
        class="group/section relative overflow-hidden rounded-2xl border-l-8 border-l-[#0C447C] shadow-sm"
    >
        <CardHeader class="pb-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Badge
                        class="h-7 rounded-lg bg-[#0C447C] px-2 text-[10px] font-bold hover:bg-[#0C447C]"
                    >
                        BAGIAN {{ sIndex + 1 }} DARI {{ totalSections }}
                    </Badge>
                </div>
                <Button
                    variant="ghost"
                    size="icon"
                    class="h-8 w-8 text-destructive opacity-0 transition-opacity group-hover/section:opacity-100"
                    @click="emit('remove')"
                >
                    <Trash2 class="h-4 w-4" />
                </Button>
            </div>

            <div class="mt-4 space-y-3">
                <Input
                    v-model="section.judul"
                    placeholder="Judul"
                    class="h-auto border-none bg-transparent p-0 text-2xl font-bold focus-visible:ring-0"
                />
                <Textarea
                    v-model="section.deskripsi"
                    placeholder="Deskripsi (opsional)"
                    class="min-h-15 resize-none border-none bg-transparent p-0 text-sm italic focus-visible:ring-0"
                />
            </div>
        </CardHeader>

        <Separator class="mx-6 w-auto" />

        <CardContent class="space-y-6 p-6">
            <div class="space-y-6">
                <QuestionEditorCard
                    v-for="(question, qIndex) in section.pertanyaans"
                    :key="qIndex"
                    v-model:question="section.pertanyaans[qIndex]"
                    :q-index="Number(qIndex)"
                    :s-index="sIndex"
                    @remove="removeQuestion(Number(qIndex))"
                    @duplicate="duplicateQuestion(Number(qIndex))"
                >
                    <ChoiceOptionsEditor
                        :options="question.opsi_jawabans || []"
                        :question-tipe="question.tipe"
                        @add="addOption(Number(qIndex))"
                        @remove="(oIdx: number) => removeOption(Number(qIndex), oIdx)"
                    />
                    <ScaleConfigEditor
                        :question="question"
                    />
                    <MatrixConfigEditor
                        :question="question"
                        @add-row="addMatrixRow(Number(qIndex))"
                        @remove-row="(rIdx: number) => removeMatrixRow(Number(qIndex), rIdx)"
                        @add-column="addMatrixColumn(Number(qIndex))"
                        @remove-column="(cIdx: number) => removeMatrixColumn(Number(qIndex), cIdx)"
                    />
                </QuestionEditorCard>

                <Button
                    variant="outline"
                    class="h-14 w-full gap-2 rounded-2xl border-dashed border-slate-300 bg-white/50 text-slate-500 transition-all hover:border-[#85B7EB] hover:bg-[#85B7EB]/10 hover:text-[#0C447C] dark:bg-slate-900/50"
                    @click="addQuestion"
                >
                    <PlusCircle class="h-5 w-5" />
                    <span class="font-bold"
                        >Tambah Pertanyaan ke Bagian Ini</span
                    >
                </Button>
            </div>
        </CardContent>
    </Card>
</template>
