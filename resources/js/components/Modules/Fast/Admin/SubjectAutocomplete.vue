<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { LoaderCircle, Search, X } from 'lucide-vue-next';

type SubjectOption = {
    id: number;
    name: string;
    email?: string | null;
    nomor_induk?: string | null;
    program_studi?: string | null;
};

const props = defineProps<{
    modelValue?: number | string | null;
    initialOptions?: SubjectOption[];
    searchUrl: string;
    error?: string | null;
    placeholder?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: number | ''): void;
    (e: 'select', value: SubjectOption | null): void;
}>();

function formatLabel(subject: SubjectOption): string {
    const detail = [subject.nomor_induk, subject.program_studi]
        .filter(Boolean)
        .join(' | ');

    return detail ? `${subject.name} (${detail})` : subject.name;
}

const options = ref<SubjectOption[]>(props.initialOptions ?? []);
const selectedOption = ref<SubjectOption | null>(
    (props.initialOptions ?? []).find(
        (subject) => subject.id === Number(props.modelValue || 0),
    ) ?? null,
);
const searchText = ref(
    selectedOption.value ? formatLabel(selectedOption.value) : '',
);
const isOpen = ref(false);
const isLoading = ref(false);
const blurTimer = ref<ReturnType<typeof setTimeout> | null>(null);
let searchTimer: ReturnType<typeof setTimeout> | null = null;
let activeRequest: AbortController | null = null;

const hasQuery = computed(() => searchText.value.trim().length >= 2);
const showEmptyState = computed(
    () => !isLoading.value && hasQuery.value && options.value.length === 0,
);

watch(
    () => props.initialOptions,
    (nextOptions) => {
        if (!nextOptions?.length) {
            return;
        }

        options.value = nextOptions;

        if (!selectedOption.value && props.modelValue) {
            selectedOption.value =
                nextOptions.find(
                    (subject) => subject.id === Number(props.modelValue || 0),
                ) ?? null;

            if (selectedOption.value) {
                searchText.value = formatLabel(selectedOption.value);
            }
        }
    },
    { immediate: true },
);

watch(searchText, (value) => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(() => {
        void loadOptions(value.trim());
    }, 250);
});

async function loadOptions(query: string): Promise<void> {
    if (query !== '' && query.length < 2) {
        options.value = [];
        isLoading.value = false;

        return;
    }

    activeRequest?.abort();
    activeRequest = new AbortController();
    isLoading.value = true;

    try {
        const response = await fetch(
            `${props.searchUrl}?q=${encodeURIComponent(query)}`,
            {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: activeRequest.signal,
            },
        );

        if (!response.ok) {
            throw new Error('Failed to load subject options.');
        }

        const payload = (await response.json()) as { data?: SubjectOption[] };
        options.value = payload.data ?? [];
    } catch (error) {
        if ((error as Error).name !== 'AbortError') {
            options.value = [];
        }
    } finally {
        isLoading.value = false;
    }
}

function openDropdown(): void {
    cancelBlur();
    isOpen.value = true;

    if (options.value.length === 0) {
        void loadOptions(searchText.value.trim());
    }
}

function closeDropdown(): void {
    blurTimer.value = setTimeout(() => {
        isOpen.value = false;

        if (selectedOption.value) {
            searchText.value = formatLabel(selectedOption.value);
        }
    }, 150);
}

function cancelBlur(): void {
    if (blurTimer.value) {
        clearTimeout(blurTimer.value);
        blurTimer.value = null;
    }
}

function handleInput(event: Event): void {
    const target = event.target as HTMLInputElement;
    searchText.value = target.value;

    if (selectedOption.value) {
        selectedOption.value = null;
        emit('update:modelValue', '');
        emit('select', null);
    }
}

function selectSubject(subject: SubjectOption): void {
    selectedOption.value = subject;
    searchText.value = formatLabel(subject);
    options.value = [
        subject,
        ...options.value.filter((item) => item.id !== subject.id),
    ];
    isOpen.value = false;
    emit('update:modelValue', subject.id);
    emit('select', subject);
}

function clearSelection(): void {
    selectedOption.value = null;
    searchText.value = '';
    emit('update:modelValue', '');
    emit('select', null);
    void loadOptions('');
}
</script>

<template>
    <div class="relative">
        <Search
            class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
        />
        <input
            :value="searchText"
            type="text"
            :placeholder="placeholder ?? 'Cari nama, nomor induk, atau email...'"
            class="h-10 w-full rounded-xl border border-slate-200 bg-white pr-10 pl-10 text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
            :class="error ? 'border-red-300' : ''"
            autocomplete="off"
            @focus="openDropdown"
            @blur="closeDropdown"
            @input="handleInput"
        />
        <button
            v-if="selectedOption || searchText"
            type="button"
            class="absolute top-1/2 right-3 -translate-y-1/2 text-slate-400 hover:text-slate-600"
            @mousedown.prevent="clearSelection"
        >
            <X class="size-4" />
        </button>
        <LoaderCircle
            v-else-if="isLoading"
            class="absolute top-1/2 right-3 size-4 -translate-y-1/2 animate-spin text-slate-400"
        />

        <div
            v-if="isOpen"
            class="absolute z-20 mt-2 max-h-72 w-full overflow-auto rounded-xl border border-slate-200 bg-white p-2 shadow-xl"
            @mousedown.prevent="cancelBlur"
        >
            <div
                v-if="isLoading"
                class="px-3 py-2 text-xs text-slate-500"
            >
                Mencari pengguna...
            </div>
            <div
                v-else-if="showEmptyState"
                class="px-3 py-2 text-xs text-slate-500"
            >
                Tidak ada pengguna yang cocok.
            </div>
            <div
                v-else-if="!hasQuery && options.length === 0"
                class="px-3 py-2 text-xs text-slate-500"
            >
                Ketik minimal 2 karakter untuk mencari subjek surat.
            </div>
            <button
                v-for="subject in options"
                :key="subject.id"
                type="button"
                class="w-full rounded-lg px-3 py-2 text-left hover:bg-slate-50"
                @mousedown.prevent="selectSubject(subject)"
            >
                <p class="text-sm font-medium text-slate-800">
                    {{ subject.name }}
                </p>
                <p
                    v-if="subject.nomor_induk || subject.program_studi || subject.email"
                    class="mt-0.5 text-xs text-slate-500"
                >
                    {{
                        [subject.nomor_induk, subject.program_studi, subject.email]
                            .filter(Boolean)
                            .join(' | ')
                    }}
                </p>
            </button>
        </div>
    </div>
</template>
