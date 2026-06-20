<script setup lang="ts">
import { Calendar } from "lucide-vue-next";
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";

const props = withDefaults(
	defineProps<{
		modelValue: string;
		placeholder?: string;
		inputClass?: string;
	}>(),
	{
		placeholder: "cth. Jan 2024",
		inputClass:
			"w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-indigo-500",
	},
);

const emit = defineEmits<(e: "update:modelValue", value: string) => void>();

const months = [
	"Jan",
	"Feb",
	"Mar",
	"Apr",
	"Mei",
	"Jun",
	"Jul",
	"Agu",
	"Sep",
	"Okt",
	"Nov",
	"Des",
];
const currentYear = new Date().getFullYear();
const years = Array.from({ length: 40 }, (_, i) => currentYear + 3 - i); // years list from +3 down to -36

const isOpen = ref(false);
const pickerRef = ref<HTMLElement | null>(null);

// Parse current modelValue to select matching month and year in picker UI
const parsedMonth = computed(() => {
	if (!props.modelValue) return -1;
	const parts = props.modelValue.split(" ");
	if (parts.length >= 1) {
		return months.indexOf(parts[0]);
	}
	return -1;
});

const parsedYear = computed(() => {
	if (!props.modelValue) return currentYear;
	const parts = props.modelValue.split(" ");
	if (parts.length >= 2) {
		const y = parseInt(parts[1], 10);
		if (!Number.isNaN(y)) return y;
	}
	return currentYear;
});

const activeMonth = ref(
	parsedMonth.value >= 0 ? parsedMonth.value : new Date().getMonth(),
);
const activeYear = ref(parsedYear.value);

watch(
	() => props.modelValue,
	() => {
		if (parsedMonth.value >= 0) activeMonth.value = parsedMonth.value;
		if (parsedYear.value) activeYear.value = parsedYear.value;
	},
);

const selectMonth = (mIndex: number) => {
	activeMonth.value = mIndex;
	updateValue();
};

const selectYear = (year: number) => {
	activeYear.value = year;
	updateValue();
};

const updateValue = () => {
	emit("update:modelValue", `${months[activeMonth.value]} ${activeYear.value}`);
};

const setPresent = () => {
	emit("update:modelValue", "Sekarang");
	isOpen.value = false;
};

const handleInput = (e: Event) => {
	emit("update:modelValue", (e.target as HTMLInputElement).value);
};

const handleClickOutside = (event: MouseEvent) => {
	if (pickerRef.value && !pickerRef.value.contains(event.target as Node)) {
		isOpen.value = false;
	}
};

onMounted(() => {
	document.addEventListener("mousedown", handleClickOutside);
});

onBeforeUnmount(() => {
	document.removeEventListener("mousedown", handleClickOutside);
});
</script>

<template>
    <div class="relative w-full" ref="pickerRef">
        <div class="relative">
            <input
                type="text"
                :value="modelValue"
                @input="handleInput"
                @focus="isOpen = true"
                :placeholder="placeholder"
                :class="inputClass"
            />
            <button
                type="button"
                @click="isOpen = !isOpen"
                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 bg-transparent border-none p-0.5 cursor-pointer flex items-center"
            >
                <Calendar class="w-3.5 h-3.5" />
            </button>
        </div>

        <div
            v-if="isOpen"
            class="absolute z-50 mt-1.5 w-64 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl shadow-xl p-3 space-y-3"
        >
            <!-- Header Toggle to 'Present' / 'Sekarang' -->
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-850 pb-2">
                <span class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Pilih Bulan & Tahun</span>
                <button
                    type="button"
                    @click="setPresent"
                    class="px-2 py-0.5 text-[10px] font-black rounded bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/30 hover:bg-indigo-100/50 cursor-pointer"
                >
                    Set "Sekarang"
                </button>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <!-- Month Selector -->
                <div class="space-y-1">
                    <div class="text-[9px] font-black text-slate-400 dark:text-zinc-500 uppercase">Bulan</div>
                    <div class="grid grid-cols-3 gap-1 max-h-36 overflow-y-auto pr-0.5 scrollbar-thin">
                        <button
                            v-for="(m, index) in months"
                            :key="m"
                            type="button"
                            @click="selectMonth(index)"
                            :class="[
                                'py-1 text-[10px] font-bold rounded text-center transition-all cursor-pointer border-none',
                                index === activeMonth && modelValue !== 'Sekarang'
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-slate-50 hover:bg-slate-100 dark:bg-zinc-950 dark:hover:bg-zinc-850 text-slate-700 dark:text-zinc-300'
                            ]"
                        >
                            {{ m }}
                        </button>
                    </div>
                </div>

                <!-- Year Selector -->
                <div class="space-y-1">
                    <div class="text-[9px] font-black text-slate-400 dark:text-zinc-500 uppercase">Tahun</div>
                    <div class="grid grid-cols-2 gap-1 h-36 overflow-y-auto pr-0.5 scrollbar-thin">
                        <button
                            v-for="y in years"
                            :key="y"
                            type="button"
                            @click="selectYear(y)"
                            :class="[
                                'py-1 text-[10px] font-bold rounded text-center transition-all cursor-pointer border-none',
                                y === activeYear && modelValue !== 'Sekarang'
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-slate-50 hover:bg-slate-100 dark:bg-zinc-950 dark:hover:bg-zinc-850 text-slate-700 dark:text-zinc-300'
                            ]"
                        >
                            {{ y }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
