<script setup lang="ts">
// resources/js/components/Modules/Fast/Admin/LetterStepIndicator.vue
const props = defineProps<{
    currentStep: number;
}>();

const steps = [
    { number: 1, title: 'Pilih Jenis Surat' },
    { number: 2, title: 'Isi Form Dinamis' },
    { number: 3, title: 'Preview & Submit' },
];
</script>

<template>
    <div class="grid gap-3 md:grid-cols-3">
        <div
            v-for="step in steps"
            :key="step.number"
            class="rounded-3xl border p-4 transition"
            :class="
                step.number === props.currentStep
                    ? 'border-indigo-200 bg-indigo-50 text-indigo-700'
                    : step.number < props.currentStep
                      ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                      : 'border-slate-200 bg-white text-slate-500'
            "
        >
            <p class="text-xs font-semibold tracking-[0.22em] uppercase">
                Step {{ step.number }}
            </p>
            <p class="mt-2 text-sm font-semibold">
                {{ step.title }}
            </p>
        </div>
    </div>
</template>
