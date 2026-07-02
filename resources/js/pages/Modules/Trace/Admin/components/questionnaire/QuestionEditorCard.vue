<script setup lang="ts">
import {
    Trash2,
    GripVertical,
    Copy,
    TrendingUp,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
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
import type { Pertanyaan } from '@/types/trace';
import { computed, ref } from 'vue';

const question = defineModel<Pertanyaan>('question', { required: true });

const props = defineProps<{
    qIndex: number;
    sIndex: number;
}>();

const emit = defineEmits<{
    (e: 'remove'): void;
    (e: 'duplicate'): void;
}>();

const TIPE_TO_TIPE_DATA: Record<string, string> = {
    text: 'text',
    number: 'numeric',
    radio: 'categorical',
    checkbox: 'categorical',
    dropdown: 'categorical',
    scale: 'scale',
    matrix: 'scale',
};

const CATEGORY_OPTIONS = [
    'Umum',
    'Status Pekerjaan',
    'Masa Tunggu & Gaji',
    'Kesesuaian Bidang',
    'Kompetensi Lulusan',
    'Kepuasan Pengguna',
    'Lanjut Studi',
    'Wirausaha',
    'Identitas Responden',
];

const ACUAN_OPTIONS = ['LAM', 'IKU', 'Kurikulum', 'Prodi'];

const INDICATOR_OPTIONS = [
    { value: 'status_karier', label: 'Status Karier' },
    { value: 'masa_tunggu', label: 'Masa Tunggu' },
    { value: 'pendapatan', label: 'Pendapatan / Gaji' },
    { value: 'kesesuaian_bidang', label: 'Kesesuaian Bidang' },
    { value: 'tingkat_tempat_kerja', label: 'Tingkat Tempat Kerja' },
    { value: 'kompetensi_lulusan', label: 'Kompetensi Lulusan' },
    { value: 'kepuasan_pengguna', label: 'Kepuasan Pengguna' },
    { value: 'lanjut_studi', label: 'Lanjut Studi' },
    { value: 'wirausaha', label: 'Wirausaha' },
];

const customCategory = ref('');
const customAcuan = ref('');
const customIndicator = ref('');

const ensureMeta = () => {
    if (!question.value.meta) {
        question.value.meta = {};
    }

    return question.value.meta as Record<string, any>;
};

const ensureMetaArray = (key: string) => {
    const meta = ensureMeta();
    if (!Array.isArray(meta[key])) {
        meta[key] = [];
    }

    return meta[key] as string[];
};

const currentCategoryOptions = computed(() => {
    const category = (question.value.meta as Record<string, any> | null)?.kategori;
    if (category && !CATEGORY_OPTIONS.includes(category)) {
        return [...CATEGORY_OPTIONS, category];
    }

    return CATEGORY_OPTIONS;
});

const currentAcuanOptions = computed(() => {
    const selected = ((question.value.meta as Record<string, any> | null)?.acuan || []) as string[];

    return [...new Set([...ACUAN_OPTIONS, ...selected])];
});

const currentIndicatorOptions = computed(() => {
    const selected = ((question.value.meta as Record<string, any> | null)?.indikator || []) as string[];
    const customSelected = selected
        .filter((value) => !INDICATOR_OPTIONS.some((option) => option.value === value))
        .map((value) => ({ value, label: value }));

    return [...INDICATOR_OPTIONS, ...customSelected];
});

const onTipeChange = (newTipe: string) => {
    question.value.tipe = newTipe;
    question.value.tipe_data = TIPE_TO_TIPE_DATA[newTipe] || 'text';
};

const toggleAcuan = (tag: string) => {
    const acuan = ensureMetaArray('acuan');
    const idx = acuan.indexOf(tag);

    if (idx > -1) {
        acuan.splice(idx, 1);
    } else {
        acuan.push(tag);
    }
};

const toggleIndicator = (indicator: string) => {
    const indicators = ensureMetaArray('indikator');
    const idx = indicators.indexOf(indicator);

    if (idx > -1) {
        indicators.splice(idx, 1);
    } else {
        indicators.push(indicator);
    }
};

const addCustomCategory = () => {
    const value = customCategory.value.trim();
    if (!value) return;

    ensureMeta().kategori = value;
    customCategory.value = '';
};

const addCustomAcuan = () => {
    const value = customAcuan.value.trim();
    if (!value) return;

    const acuan = ensureMetaArray('acuan');
    if (!acuan.includes(value)) {
        acuan.push(value);
    }

    customAcuan.value = '';
};

const addCustomIndicator = () => {
    const value = customIndicator.value.trim();
    if (!value) return;

    const normalized = value.toLowerCase().replace(/\s+/g, '_');
    const indicators = ensureMetaArray('indikator');
    if (!indicators.includes(normalized)) {
        indicators.push(normalized);
    }

    customIndicator.value = '';
};
</script>

<template>
    <div
        class="group/question rounded-2xl border border-slate-200 bg-slate-50/50 p-3 shadow-xs transition-all hover:border-[#85B7EB] sm:p-6 dark:border-slate-800 dark:bg-slate-900/30 dark:hover:border-[#0C447C]"
    >
        <div class="flex items-start gap-2 sm:gap-4">
            <div
                class="hidden cursor-grab pt-2 text-muted-foreground active:cursor-grabbing sm:block"
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
                    <Select :model-value="question.tipe" @update:model-value="(val: string) => onTipeChange(val)">
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

                <!-- Slot for type-specific editors (options, scale, matrix) -->
                <slot />

                <Separator class="opacity-50" />

                <!-- Advanced Settings -->
                <div
                    class="mt-4 rounded-2xl border border-[#85B7EB]/50 bg-[#0C447C]/5 p-3 sm:p-5 dark:border-[#0C447C]/30 dark:bg-[#0C447C]/10"
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
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3"
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
                                    <SelectItem
                                        v-for="category in currentCategoryOptions"
                                        :key="category"
                                        :value="category"
                                    >
                                        {{ category }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <div class="flex gap-2">
                                <Input
                                    v-model="customCategory"
                                    placeholder="Kategori custom"
                                    class="h-8 rounded-lg bg-white text-[11px] dark:bg-slate-900"
                                    @keydown.enter.prevent="addCustomCategory"
                                />
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    class="h-8 shrink-0 rounded-lg px-2 text-[10px] font-bold"
                                    @click="addCustomCategory"
                                >
                                    Tambah
                                </Button>
                            </div>
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
                                    v-for="tag in currentAcuanOptions"
                                    :key="tag"
                                    type="button"
                                    @click="
                                        toggleAcuan(tag)
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
                            <div class="flex gap-2">
                                <Input
                                    v-model="customAcuan"
                                    placeholder="Acuan custom"
                                    class="h-8 rounded-lg bg-white text-[11px] dark:bg-slate-900"
                                    @keydown.enter.prevent="addCustomAcuan"
                                />
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    class="h-8 shrink-0 rounded-lg px-2 text-[10px] font-bold"
                                    @click="addCustomAcuan"
                                >
                                    Tambah
                                </Button>
                            </div>
                        </div>

                        <!-- Indikator Laporan -->
                        <div class="space-y-2">
                            <div class="flex items-center gap-1.5">
                                <Label
                                    class="text-[10px] font-black tracking-tight text-slate-600 uppercase"
                                    >Indikator Laporan</Label
                                >
                                <div class="group relative">
                                    <div
                                        class="flex h-3 w-3 cursor-help items-center justify-center rounded-full border border-slate-400 text-[8px] text-slate-500"
                                    >
                                        ?
                                    </div>
                                    <div
                                        class="absolute bottom-full left-0 z-50 mb-2 hidden w-56 rounded-lg bg-slate-800 p-2 text-[9px] text-white shadow-xl group-hover:block"
                                    >
                                        Penanda spesifik untuk report
                                        otomatis, misal masa tunggu,
                                        pendapatan, atau kesesuaian bidang.
                                    </div>
                                </div>
                            </div>
                            <div class="flex max-h-24 flex-wrap gap-2 overflow-y-auto pt-1">
                                <button
                                    v-for="indicator in currentIndicatorOptions"
                                    :key="indicator.value"
                                    type="button"
                                    @click="toggleIndicator(indicator.value)"
                                    :class="[
                                        'rounded-md border px-2 py-1 text-[9px] font-black transition-all',
                                        question.meta?.indikator?.includes(
                                            indicator.value,
                                        )
                                            ? 'border-emerald-600 bg-emerald-600 text-white shadow-md'
                                            : 'border-slate-200 bg-white text-slate-500 hover:border-emerald-300',
                                    ]"
                                >
                                    {{ indicator.label }}
                                </button>
                            </div>
                            <div class="flex gap-2">
                                <Input
                                    v-model="customIndicator"
                                    placeholder="Indikator custom"
                                    class="h-8 rounded-lg bg-white text-[11px] dark:bg-slate-900"
                                    @keydown.enter.prevent="addCustomIndicator"
                                />
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    class="h-8 shrink-0 rounded-lg px-2 text-[10px] font-bold"
                                    @click="addCustomIndicator"
                                >
                                    Tambah
                                </Button>
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
                    class="mt-4 flex flex-col items-start gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between sm:pt-5 dark:border-slate-800"
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

                    <div class="flex items-center gap-1 self-end sm:self-auto">
                        <Button
                            variant="ghost"
                            size="sm"
                            class="h-9 gap-2 rounded-xl px-3 font-bold text-slate-500 hover:bg-[#85B7EB]/10 hover:text-[#0C447C]"
                            @click="emit('duplicate')"
                            title="Duplikat Pertanyaan"
                        >
                            <Copy class="h-4 w-4" />
                            Duplikat
                        </Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            class="h-9 gap-2 rounded-xl px-3 font-bold text-destructive/60 hover:bg-destructive/5 hover:text-destructive"
                            @click="emit('remove')"
                        >
                            <Trash2 class="h-4 w-4" />
                            Hapus
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
