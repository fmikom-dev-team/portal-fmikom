<script setup lang="ts">
import {
    Trash2,
    GripVertical,
    PlusCircle,
    TrendingUp,
    Copy,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
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
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';

const section = defineModel<any>('section', { required: true });

const { sIndex, totalSections } = defineProps<{
    sIndex: number;
    totalSections: number;
}>();

const emit = defineEmits<{
    (e: 'remove'): void;
}>();

// ─── Auto-set tipe_data when tipe changes ───
const TIPE_TO_TIPE_DATA: Record<string, string> = {
    text: 'text',
    number: 'numeric',
    radio: 'categorical',
    checkbox: 'categorical',
    dropdown: 'categorical',
    scale: 'scale',
    matrix: 'scale',
};

const onTipeChange = (qIndex: number, newTipe: string) => {
    const question = section.value.pertanyaans[qIndex];
    question.tipe = newTipe;
    question.tipe_data = TIPE_TO_TIPE_DATA[newTipe] || 'text';
};

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
    clone.opsi_jawabans = (clone.opsi_jawabans || []).map((o: any) => {
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
const initMatrixColumns = (qIndex: number) => {
    const question = section.value.pertanyaans[qIndex];
    if (!question.meta) question.meta = {};
    if (!question.meta.columns || question.meta.columns.length === 0) {
        question.meta.columns = ['1', '2', '3', '4', '5'];
    }
};

const addMatrixColumn = (qIndex: number) => {
    const question = section.value.pertanyaans[qIndex];
    if (!question.meta) question.meta = {};
    if (!question.meta.columns) question.meta.columns = [];
    question.meta.columns.push(String(question.meta.columns.length + 1));
};

const removeMatrixColumn = (qIndex: number, colIndex: number) => {
    section.value.pertanyaans[qIndex].meta.columns.splice(colIndex, 1);
};

const toggleAcuan = (question: any, tag: string) => {
    if (!question.meta) {
        question.meta = { acuan: [] };
    }

    if (!Array.isArray(question.meta.acuan)) {
        question.meta.acuan = [];
    }

    const idx = question.meta.acuan.indexOf(tag);

    if (idx > -1) {
        question.meta.acuan.splice(idx, 1);
    } else {
        question.meta.acuan.push(tag);
    }
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
                <div
                    v-for="(question, qIndex) in section.pertanyaans"
                    :key="qIndex"
                    class="group/question rounded-2xl border border-slate-200 bg-slate-50/50 p-6 shadow-xs transition-all hover:border-[#85B7EB] dark:border-slate-800 dark:bg-slate-900/30 dark:hover:border-[#0C447C]"
                >
                    <div class="flex items-start gap-4">
                        <div
                            class="cursor-grab pt-2 text-muted-foreground active:cursor-grabbing"
                        >
                            <GripVertical class="h-4 w-4" />
                        </div>

                        <div class="flex-1 space-y-4">
                            <!-- Question Text & Type -->
                            <div class="flex flex-col gap-4 md:flex-row">
                                <div class="flex-1 space-y-2">
                                    <Input
                                        v-model="question.teks"
                                        placeholder="Teks Pertanyaan"
                                        class="h-11 rounded-xl bg-white font-medium dark:bg-slate-900"
                                    />
                                </div>
                                <Select :model-value="question.tipe" @update:model-value="(val: string) => onTipeChange(Number(qIndex), val)">
                                    <SelectTrigger
                                        class="h-11 w-full rounded-xl bg-white md:w-55 dark:bg-slate-900"
                                    >
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="text"
                                            >Jawaban Singkat</SelectItem
                                        >
                                        <SelectItem value="number"
                                            >Angka / Numerik</SelectItem
                                        >
                                        <SelectItem value="radio"
                                            >Pilihan Ganda</SelectItem
                                        >
                                        <SelectItem value="checkbox"
                                            >Kotak Centang</SelectItem
                                        >
                                        <SelectItem value="dropdown"
                                            >Dropdown / Daftar</SelectItem
                                        >
                                        <SelectItem value="scale"
                                            >Skala Linear</SelectItem
                                        >
                                        <SelectItem value="matrix"
                                            >Matrix</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Options for radio/checkbox/dropdown -->
                            <div
                                v-if="
                                    ['radio', 'checkbox', 'dropdown'].includes(
                                        question.tipe,
                                    )
                                "
                                class="space-y-2 pl-4"
                            >
                                <div
                                    v-for="(
                                        option, oIndex
                                    ) in question.opsi_jawabans"
                                    :key="oIndex"
                                    class="flex items-center gap-3"
                                >
                                    <div
                                        class="h-4 w-4 rounded-full border border-slate-300 dark:border-slate-700"
                                        :class="
                                            question.tipe === 'checkbox'
                                                ? 'rounded-sm'
                                                : ''
                                        "
                                    ></div>
                                    <Input
                                        v-model="option.label"
                                        class="h-9 border-none bg-transparent px-0 font-medium focus-visible:ring-0"
                                    />
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-muted-foreground/30 opacity-0 transition-opacity group-hover/question:opacity-100 hover:text-destructive"
                                        @click="
                                            removeOption(
                                                Number(qIndex),
                                                Number(oIndex),
                                            )
                                        "
                                    >
                                        <Trash2 class="h-3 w-3" />
                                    </Button>
                                </div>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="h-8 gap-2 px-0 text-[11px] font-black tracking-widest text-[#0C447C] hover:text-[#0C447C]"
                                    @click="addOption(Number(qIndex))"
                                >
                                    <PlusCircle class="h-3.5 w-3.5" />
                                    TAMBAH OPSI
                                </Button>
                            </div>

                            <!-- Scale Linear config -->
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
                                <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3 dark:bg-slate-800/50">
                                    <span class="text-[9px] font-bold text-slate-400">{{ question.meta?.scale_label_min || 'Min' }}</span>
                                    <div class="flex gap-1.5">
                                        <div
                                            v-for="n in ((question.meta?.scale_max || 5) - (question.meta?.scale_min || 1) + 1)"
                                            :key="n"
                                            class="flex h-7 w-7 items-center justify-center rounded-full border border-slate-200 text-[10px] font-bold text-slate-500 dark:border-slate-700"
                                        >
                                            {{ (question.meta?.scale_min || 1) + n - 1 }}
                                        </div>
                                    </div>
                                    <span class="text-[9px] font-bold text-slate-400">{{ question.meta?.scale_label_max || 'Max' }}</span>
                                </div>
                            </div>
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
                                                @click="removeMatrixColumn(Number(qIndex), cIdx)"
                                            >
                                                <Trash2 class="h-3 w-3" />
                                            </Button>
                                        </div>
                                    </div>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="h-7 gap-1.5 px-0 text-[10px] font-black tracking-widest text-[#0C447C] hover:text-[#0C447C]"
                                        @click="addMatrixColumn(Number(qIndex))"
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
                                                @click="removeMatrixRow(Number(qIndex), Number(rIndex))"
                                            >
                                                <Trash2 class="h-3 w-3" />
                                            </Button>
                                        </div>
                                    </div>

                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="h-8 gap-2 px-0 text-[10px] font-black tracking-widest text-[#0C447C] hover:text-[#0C447C]"
                                        @click="addMatrixRow(Number(qIndex))"
                                    >
                                        <PlusCircle class="h-3.5 w-3.5" />
                                        TAMBAH BARIS MATRIX
                                    </Button>
                                </div>
                            </div>

                            <Separator class="opacity-50" />

                            <!-- Advanced Settings -->
                            <div
                                class="mt-4 rounded-2xl border border-[#85B7EB]/50 bg-[#0C447C]/5 p-5 dark:border-[#0C447C]/30 dark:bg-[#0C447C]/10"
                            >
                                <div class="mb-4 flex items-center gap-2">
                                    <TrendingUp class="h-4 w-4 text-[#0C447C]" />
                                    <p
                                        class="text-[11px] font-black tracking-[0.15em] text-[#0C447C] uppercase"
                                    >
                                        Konfigurasi Analisis & Akreditasi
                                    </p>
                                </div>

                                <div
                                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                                >
                                    <!-- Grup Kategori -->
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-1.5">
                                            <Label
                                                class="text-[10px] font-black tracking-tight text-slate-600 uppercase"
                                                >Grup Kategori</Label
                                            >
                                            <div class="group relative">
                                                <div
                                                    class="flex h-3 w-3 cursor-help items-center justify-center rounded-full border border-slate-400 text-[8px] text-slate-500"
                                                >
                                                    ?
                                                </div>
                                                <div
                                                    class="absolute bottom-full left-0 z-50 mb-2 hidden w-48 rounded-lg bg-slate-800 p-2 text-[9px] text-white shadow-xl group-hover:block"
                                                >
                                                    Mengelompokkan hasil di
                                                    dashboard agar rapi per
                                                    topik (misal: Relevansi).
                                                </div>
                                            </div>
                                        </div>
                                        <Select
                                            v-model="question.meta.kategori"
                                        >
                                            <SelectTrigger
                                                class="h-10 w-full rounded-xl border-slate-200 bg-white text-xs font-bold shadow-sm dark:bg-slate-900"
                                            >
                                                <SelectValue
                                                    placeholder="Pilih Kategori"
                                                />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="Umum"
                                                    >Umum</SelectItem
                                                >
                                                <SelectItem
                                                    value="Status Pekerjaan"
                                                    >Status
                                                    Pekerjaan</SelectItem
                                                >
                                                <SelectItem
                                                    value="Masa Tunggu & Gaji"
                                                    >Masa Tunggu &
                                                    Gaji</SelectItem
                                                >
                                                <SelectItem
                                                    value="Kesesuaian Bidang"
                                                    >Kesesuaian
                                                    Bidang</SelectItem
                                                >
                                                <SelectItem
                                                    value="Kompetensi Lulusan"
                                                    >Kompetensi
                                                    Lulusan</SelectItem
                                                >
                                                <SelectItem
                                                    value="Kepuasan Pengguna"
                                                    >Kepuasan
                                                    Pengguna</SelectItem
                                                >
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <!-- Acuan Laporan -->
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-1.5">
                                            <Label
                                                class="text-[10px] font-black tracking-tight text-slate-600 uppercase"
                                                >Acuan Laporan</Label
                                            >
                                            <div class="group relative">
                                                <div
                                                    class="flex h-3 w-3 cursor-help items-center justify-center rounded-full border border-slate-400 text-[8px] text-slate-500"
                                                >
                                                    ?
                                                </div>
                                                <div
                                                    class="absolute bottom-full left-0 z-50 mb-2 hidden w-48 rounded-lg bg-slate-800 p-2 text-[9px] text-white shadow-xl group-hover:block"
                                                >
                                                    Menandai pertanyaan ini
                                                    milik standar akreditasi
                                                    mana (misal: LAM, IKU).
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap gap-2 pt-1">
                                            <button
                                                v-for="tag in [
                                                    'LAM',
                                                    'IKU',
                                                    'Kurikulum',
                                                    'Prodi',
                                                ]"
                                                :key="tag"
                                                type="button"
                                                @click="
                                                    toggleAcuan(question, tag)
                                                "
                                                :class="[
                                                    'rounded-md border px-2 py-1 text-[9px] font-black transition-all',
                                                    question.meta?.acuan?.includes(
                                                        tag,
                                                    )
                                                        ? 'border-[#0C447C] bg-[#0C447C] text-white shadow-md'
                                                        : 'border-slate-200 bg-white text-slate-500 hover:border-[#85B7EB]',
                                                ]"
                                            >
                                                {{ tag }}
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Jenis Nilai -->
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-1.5">
                                            <Label
                                                class="text-[10px] font-black tracking-tight text-slate-600 uppercase"
                                                >Jenis Nilai</Label
                                            >
                                            <div class="group relative">
                                                <div
                                                    class="flex h-3 w-3 cursor-help items-center justify-center rounded-full border border-slate-400 text-[8px] text-slate-500"
                                                >
                                                    ?
                                                </div>
                                                <div
                                                    class="absolute bottom-full left-0 z-50 mb-2 hidden w-48 rounded-lg bg-slate-800 p-2 text-[9px] text-white shadow-xl group-hover:block"
                                                >
                                                    Otomatis ditentukan
                                                    berdasarkan tipe pertanyaan.
                                                    Bisa diubah manual jika
                                                    diperlukan.
                                                </div>
                                            </div>
                                        </div>
                                        <Select v-model="question.tipe_data">
                                            <SelectTrigger
                                                class="h-10 w-full rounded-xl border-slate-200 bg-white text-xs font-bold shadow-sm dark:bg-slate-900"
                                            >
                                                <SelectValue
                                                    placeholder="Tipe Data"
                                                />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="categorical"
                                                    >Kategoris
                                                    (Pilihan)</SelectItem
                                                >
                                                <SelectItem value="numeric"
                                                    >Numerik (Angka)</SelectItem
                                                >
                                                <SelectItem value="text"
                                                    >Teks Bebas</SelectItem
                                                >
                                                <SelectItem value="scale"
                                                    >Skala {{ question.meta?.scale_min || 1 }}-{{ question.tipe === 'matrix' ? (question.meta?.columns?.length || 5) : (question.meta?.scale_max || 5) }}
                                                    (Likert)</SelectItem
                                                >
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Actions -->
                            <div
                                class="mt-4 flex items-center justify-between border-t border-slate-100 pt-5 dark:border-slate-800"
                            >
                                <div class="flex items-center gap-5">
                                    <div class="flex items-center gap-3">
                                        <Switch
                                            :id="`req-${sIndex}-${qIndex}`"
                                            v-model="question.is_required"
                                            class="scale-90"
                                        />
                                        <Label
                                            :for="`req-${sIndex}-${qIndex}`"
                                            class="cursor-pointer text-[11px] font-black tracking-wider text-slate-500 uppercase"
                                        >
                                            Wajib Diisi
                                        </Label>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="h-9 gap-2 rounded-xl px-3 font-bold text-slate-500 hover:bg-[#85B7EB]/10 hover:text-[#0C447C]"
                                        @click="
                                            duplicateQuestion(Number(qIndex))
                                        "
                                        title="Duplikat Pertanyaan"
                                    >
                                        <Copy class="h-4 w-4" />
                                        Duplikat
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="h-9 gap-2 rounded-xl px-3 font-bold text-destructive/60 hover:bg-destructive/5 hover:text-destructive"
                                        @click="removeQuestion(Number(qIndex))"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                        Hapus
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
