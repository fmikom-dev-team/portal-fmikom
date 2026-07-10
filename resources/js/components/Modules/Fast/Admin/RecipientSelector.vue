<script setup lang="ts">
import { computed, ref } from 'vue';
import { Plus, X } from 'lucide-vue-next';

const props = withDefaults(
    defineProps<{
        modelValue: string[];
        title?: string;
        description?: string;
        manualPlaceholder?: string;
    }>(),
    {
        title: 'Kepada Yth.',
        description: 'Tentukan penerima surat. Isi manual lebih dari satu tujuan.',
        manualPlaceholder: 'Ketik manual...',
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string[]];
}>();

const manual = ref('');

const selected = computed(() => props.modelValue ?? []);

function emitValue(value: string[]) {
    emit('update:modelValue', value);
}

function addRecipient(value: string) {
    const trimmed = value.trim();

    if (!trimmed || selected.value.includes(trimmed)) return;

    emitValue([...selected.value, trimmed]);
    manual.value = '';
}

function removeRecipient(index: number) {
    emitValue(selected.value.filter((_, currentIndex) => currentIndex !== index));
}
</script>

<template>
    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <h3 class="mb-1 text-sm font-semibold text-slate-800">
            {{ title }}
        </h3>
        <p class="mb-3 text-xs text-slate-400">
            {{ description }}
        </p>

        <div
            v-if="selected.length > 0"
            class="mb-3 flex flex-wrap gap-2"
        >
            <div
                v-for="(recipient, idx) in selected"
                :key="`${recipient}-${idx}`"
                class="flex items-center gap-1.5 rounded-full border border-blue-200 bg-blue-50 px-3 py-1"
            >
                <span class="text-xs font-medium text-blue-800">
                    {{ recipient }}
                </span>
                <button
                    type="button"
                    class="fast-btn fast-btn-ghost fast-btn-icon text-blue-500 hover:text-blue-700"
                    @click="removeRecipient(idx)"
                >
                    <X class="size-3" />
                </button>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <input
                v-model="manual"
                type="text"
                :placeholder="manualPlaceholder"
                class="h-9 min-w-[220px] flex-1 rounded-xl border border-slate-200 bg-white px-3 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                @keyup.enter.prevent="manual && addRecipient(manual)"
            />

            <button
                type="button"
                class="fast-btn fast-btn-outline flex h-9 items-center gap-1 px-3 text-xs font-medium text-slate-700"
                @click="manual && addRecipient(manual)"
            >
                <Plus class="size-3.5" />
                Tambah
            </button>
        </div>
    </div>
</template>
