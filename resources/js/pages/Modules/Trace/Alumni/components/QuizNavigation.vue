<script setup lang="ts">
import { ArrowRight, ArrowLeft, Send } from 'lucide-vue-next';

defineProps<{
    isFirst: boolean;
    isLast: boolean;
    processing: boolean;
    hasResponded: boolean;
}>();

const emit = defineEmits<{
    (e: 'prev'): void;
    (e: 'next'): void;
    (e: 'submit'): void;
}>();
</script>

<template>
    <div
        class="mt-6 flex flex-col-reverse gap-4 rounded-2xl border border-slate-100 bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800 dark:bg-zinc-900"
    >
        <div>
            <button
                v-if="!isFirst"
                type="button"
                @click="emit('prev')"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-6 py-4 text-sm font-bold text-slate-700 transition-all hover:bg-slate-50 hover:text-slate-900 sm:w-auto dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white"
            >
                <ArrowLeft class="h-5 w-5" />
                KEMBALI
            </button>
            <span
                v-else
                class="hidden px-4 text-sm text-slate-500 sm:inline-block"
                >Langkah awal kuesioner</span
            >
        </div>

        <div>
            <button
                v-if="!isLast"
                type="button"
                @click="emit('next')"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#0C447C] px-8 py-4 text-sm font-black text-white shadow-lg shadow-[#0C447C]/30 transition-all hover:-translate-y-0.5 hover:bg-[#0C447C]/90 hover:shadow-xl hover:shadow-[#0C447C]/40 active:translate-y-0 sm:w-auto dark:bg-[#85B7EB] dark:text-slate-900 dark:shadow-[#85B7EB]/20 dark:hover:bg-[#85B7EB]/90"
            >
                SELANJUTNYA
                <ArrowRight class="h-5 w-5" />
            </button>

            <button
                v-else
                type="submit"
                :disabled="processing || hasResponded"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#0C447C] px-8 py-4 text-sm font-black text-white shadow-lg shadow-[#0C447C]/30 transition-all hover:-translate-y-0.5 hover:bg-[#0C447C]/90 hover:shadow-xl hover:shadow-[#0C447C]/40 active:translate-y-0 disabled:transform-none disabled:opacity-50 disabled:shadow-none sm:w-auto dark:bg-[#85B7EB] dark:text-slate-900 dark:shadow-[#85B7EB]/20 dark:hover:bg-[#85B7EB]/90"
            >
                <Send v-if="!processing" class="h-5 w-5" />
                <span
                    v-else
                    class="h-5 w-5 animate-spin rounded-full border-2 border-white/30 border-t-white"
                ></span>
                KIRIM JAWABAN KUESIONER
            </button>
        </div>
    </div>
</template>
