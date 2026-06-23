<script setup lang="ts">
import { AlertCircle } from 'lucide-vue-next';
import { computed } from 'vue';
import type { Pertanyaan } from '@/types/trace';

type AnswerValue = string | number | number[] | Record<string, unknown>;

const props = defineProps<{
    pertanyaan: Pertanyaan;
    modelValue: AnswerValue;
    questionIndex: number;
    errors: Record<string, string | undefined>;
    readonly: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: AnswerValue): void;
}>();

const answer = computed({
    get: () => props.modelValue,
    set: (val: AnswerValue) => emit('update:modelValue', val),
});

const isEvenIndex = (index: number) => index % 2 === 0;

const fieldError = computed(() => props.errors[`answers.${props.pertanyaan.id}`]);
</script>

<template>
    <div
        class="space-y-4"
        :id="'question-' + pertanyaan.id"
    >
        <label
            class="block text-base font-bold text-slate-800 dark:text-slate-200"
        >
            {{ questionIndex + 1 }}.
            {{ pertanyaan.teks }}
            <span
                v-if="pertanyaan.is_required"
                class="ml-1 text-red-500"
                title="Wajib Diisi"
                >*</span
            >
        </label>

        <!-- Text Input -->
        <div
            v-if="pertanyaan.tipe === 'text'"
            class="relative"
        >
            <input
                v-model="answer"
                type="text"
                placeholder="Jawaban Anda"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm transition-all outline-none focus:bg-white focus:ring-2 focus:ring-green-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                :disabled="readonly"
            />
        </div>

        <!-- Textarea -->
        <div
            v-if="pertanyaan.tipe === 'textarea'"
            class="relative"
        >
            <textarea
                v-model="answer"
                rows="4"
                placeholder="Jawaban Anda"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm transition-all outline-none focus:bg-white focus:ring-2 focus:ring-green-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                :disabled="readonly"
            ></textarea>
        </div>

        <!-- Number Input -->
        <div
            v-if="pertanyaan.tipe === 'number'"
            class="relative"
        >
            <input
                v-model="answer"
                type="number"
                placeholder="0"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm transition-all outline-none focus:bg-white focus:ring-2 focus:ring-green-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                :disabled="readonly"
            />
        </div>

        <!-- Dropdown -->
        <div
            v-if="pertanyaan.tipe === 'dropdown'"
            class="relative"
        >
            <select
                v-model="answer"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm transition-all outline-none focus:bg-white focus:ring-2 focus:ring-green-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                :disabled="readonly"
            >
                <option value="" disabled>
                    Pilih Jawaban
                </option>
                <option
                    v-for="opsi in pertanyaan.opsi_jawabans"
                    :key="opsi.id"
                    :value="opsi.id"
                >
                    {{ opsi.label }}
                </option>
            </select>
        </div>

        <!-- Radio -->
        <div
            v-if="pertanyaan.tipe === 'radio'"
            class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1"
        >
            <label
                v-for="opsi in pertanyaan.opsi_jawabans"
                :key="opsi.id"
                class="relative flex cursor-pointer items-center gap-4 rounded-xl border border-slate-100 bg-slate-50/50 p-4 transition-all hover:border-green-200 hover:bg-green-50/50 dark:border-slate-800 dark:bg-slate-800/50 dark:hover:border-green-900/50"
                :class="{
                    'border-green-500 bg-green-50/50 dark:border-green-500/50 dark:bg-green-900/20':
                        answer === opsi.id,
                }"
            >
                <input
                    v-model="answer"
                    type="radio"
                    :name="'q_' + pertanyaan.id"
                    :value="opsi.id"
                    class="h-5 w-5 border-slate-300 text-green-600 focus:ring-green-500"
                    :disabled="readonly"
                />
                <span
                    class="text-sm font-medium text-slate-700 dark:text-slate-300"
                    >{{ opsi.label }}</span
                >
            </label>
        </div>

        <!-- Checkbox -->
        <div
            v-if="pertanyaan.tipe === 'checkbox'"
            class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1"
        >
            <label
                v-for="opsi in pertanyaan.opsi_jawabans"
                :key="opsi.id"
                class="relative flex cursor-pointer items-center gap-4 rounded-xl border border-slate-100 bg-slate-50/50 p-4 transition-all hover:border-green-200 hover:bg-green-50/50 dark:border-slate-800 dark:bg-slate-800/50 dark:hover:border-green-900/50"
                :class="{
                    'border-green-500 bg-green-50/50 dark:border-green-500/50 dark:bg-green-900/20':
                        answer?.includes(opsi.id),
                }"
            >
                <input
                    v-model="answer"
                    type="checkbox"
                    :value="opsi.id"
                    class="h-5 w-5 rounded border-slate-300 text-green-600 focus:ring-green-500"
                    :disabled="readonly"
                />
                <span
                    class="text-sm font-medium text-slate-700 dark:text-slate-300"
                    >{{ opsi.label }}</span
                >
            </label>
        </div>

        <!-- Scale -->
        <div
            v-if="pertanyaan.tipe === 'scale'"
            class="space-y-6 pt-4"
        >
            <div
                class="flex justify-between px-4 text-xs font-black tracking-widest text-slate-400 uppercase"
            >
                <span>{{ pertanyaan.meta?.scale_label_min || 'Sangat Tidak Setuju' }}</span>
                <span>{{ pertanyaan.meta?.scale_label_max || 'Sangat Setuju' }}</span>
            </div>
            <div
                class="flex items-center justify-between gap-2 px-2"
            >
                <label
                    v-for="n in ((pertanyaan.meta?.scale_max || 5) - (pertanyaan.meta?.scale_min || 1) + 1)"
                    :key="n"
                    class="group relative flex flex-1 cursor-pointer flex-col items-center gap-3"
                >
                    <div
                        class="absolute inset-0 rounded-xl bg-green-50 opacity-0 transition-opacity group-hover:opacity-100 dark:bg-green-900/20"
                        :class="{
                            'opacity-100':
                                answer === (pertanyaan.meta?.scale_min || 1) + n - 1,
                        }"
                        style="
                            z-index: 0;
                            transform: scale(
                                1.2
                            );
                        "
                    ></div>
                    <input
                        v-model="answer"
                        type="radio"
                        :name="'q_' + pertanyaan.id"
                        :value="(pertanyaan.meta?.scale_min || 1) + n - 1"
                        class="relative z-10 h-6 w-6 cursor-pointer border-slate-300 text-green-600 focus:ring-green-500"
                        :disabled="readonly"
                    />
                    <span
                        class="relative z-10 text-sm font-black text-slate-600 dark:text-slate-300"
                        :class="{
                            'text-green-600 dark:text-green-400':
                                answer === (pertanyaan.meta?.scale_min || 1) + n - 1,
                        }"
                        >{{ (pertanyaan.meta?.scale_min || 1) + n - 1 }}</span
                    >
                </label>
            </div>
        </div>

        <!-- Matrix -->
        <div
            v-if="pertanyaan.tipe === 'matrix'"
            class="overflow-x-auto rounded-2xl border border-slate-100 shadow-sm dark:border-slate-800"
        >
            <table class="w-full text-sm">
                <thead
                    class="border-b border-slate-100 bg-slate-50/80 dark:border-slate-700 dark:bg-slate-800/80"
                >
                    <tr>
                        <th
                            class="min-w-50 p-5 text-left font-black text-slate-900 dark:text-white"
                        >
                            Pernyataan
                        </th>
                        <th
                            v-for="(col, ci) in (pertanyaan.meta?.columns?.length ? pertanyaan.meta.columns : ['1','2','3','4','5'])"
                            :key="ci"
                            class="w-16 p-4 text-center font-black text-slate-900 dark:text-white text-xs"
                        >
                            {{ col }}
                        </th>
                    </tr>
                </thead>
                <tbody
                    class="divide-y divide-gray-50 dark:divide-slate-800/50"
                >
                    <tr
                        v-for="(
                            row, rIdx
                        ) in pertanyaan.meta
                            ?.rows"
                        :key="row"
                        class="transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/30"
                        :class="{
                            'bg-slate-50/30 dark:bg-slate-800/10':
                                isEvenIndex(
                                    rIdx,
                                ),
                        }"
                    >
                        <td
                            class="p-5 font-medium text-slate-700 dark:text-slate-300"
                        >
                            {{ row }}
                        </td>
                        <td
                            v-for="(col, ci) in (pertanyaan.meta?.columns?.length ? pertanyaan.meta.columns : ['1','2','3','4','5'])"
                            :key="ci"
                            class="p-4 text-center"
                        >
                            <label
                                class="flex w-full cursor-pointer justify-center p-2"
                            >
                                <input
                                    v-model="answer[row]"
                                    type="radio"
                                    :name="
                                        'q_' +
                                        pertanyaan.id +
                                        '_' +
                                        rIdx
                                    "
                                    :value="ci + 1"
                                    class="h-5 w-5 cursor-pointer border-slate-300 text-green-600 focus:ring-green-500"
                                    :disabled="readonly"
                                />
                            </label>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Field Error -->
        <div
            v-if="fieldError"
            class="mt-2 flex items-center gap-2 rounded-lg bg-red-50 p-3 text-sm font-bold text-red-600 dark:bg-red-900/20 dark:text-red-400"
        >
            <AlertCircle
                class="h-4 w-4 shrink-0"
            />
            <span
                >Pertanyaan ini wajib diisi
                dengan benar.</span
            >
        </div>
    </div>
</template>
