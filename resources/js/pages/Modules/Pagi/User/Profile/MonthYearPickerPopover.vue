<script setup lang="ts">
import { Calendar, ChevronLeft, ChevronRight } from "lucide-vue-next";
import { computed, onMounted, onUnmounted, ref } from "vue";

const props = withDefaults(
	defineProps<{
		modelValue: string;
		label: string;
		placeholder?: string;
		required?: boolean;
		showClear?: boolean;
	}>(),
	{
		placeholder: "Select Date",
		required: false,
		showClear: false,
	},
);

const emit = defineEmits<(e: "update:modelValue", value: string) => void>();

const isOpen = ref(false);
const pickerYear = ref(new Date().getFullYear());
const containerRef = ref<HTMLElement | null>(null);

const MONTHS = [
	{ name: "January", val: "01" },
	{ name: "February", val: "02" },
	{ name: "March", val: "03" },
	{ name: "April", val: "04" },
	{ name: "May", val: "05" },
	{ name: "June", val: "06" },
	{ name: "July", val: "07" },
	{ name: "August", val: "08" },
	{ name: "September", val: "09" },
	{ name: "October", val: "10" },
	{ name: "November", val: "11" },
	{ name: "December", val: "12" },
];

const formatMonthYear = (dateStr: string) => {
	if (!dateStr) return "";
	if (!dateStr.includes("-")) return dateStr;
	const [year, month] = dateStr.split("-");
	const monthNames = [
		"January",
		"February",
		"March",
		"April",
		"May",
		"June",
		"July",
		"August",
		"September",
		"October",
		"November",
		"December",
	];
	const mIdx = Number.parseInt(month, 10) - 1;
	return `${monthNames[mIdx] || month} ${year}`;
};

const displayValue = computed(() => {
	return props.modelValue
		? formatMonthYear(props.modelValue)
		: props.placeholder;
});

const togglePicker = () => {
	if (!isOpen.value) {
		pickerYear.value = props.modelValue
			? Number.parseInt(props.modelValue.split("-")[0], 10)
			: new Date().getFullYear();
	}
	isOpen.value = !isOpen.value;
};

const prevYear = () => {
	pickerYear.value--;
};

const nextYear = () => {
	pickerYear.value++;
};

const selectMonth = (monthVal: string) => {
	const selected = `${pickerYear.value}-${monthVal}`;
	emit("update:modelValue", selected);
	isOpen.value = false;
};

const clearDate = () => {
	emit("update:modelValue", "");
	isOpen.value = false;
};

const clickOutsideHandler = (e: MouseEvent) => {
	if (
		isOpen.value &&
		containerRef.value &&
		!containerRef.value.contains(e.target as Node)
	) {
		isOpen.value = false;
	}
};

onMounted(() => {
	document.addEventListener("click", clickOutsideHandler);
});

onUnmounted(() => {
	document.removeEventListener("click", clickOutsideHandler);
});
</script>

<template>
	<div ref="containerRef" class="space-y-1.5 relative datepicker-container">
		<span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
			{{ label }} <span v-if="required" class="text-red-500">*</span>
		</span>
		
		<div 
			@click.stop="togglePicker"
			class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 flex items-center justify-between cursor-pointer shadow-2xs select-none"
		>
			<span class="text-xs font-semibold" :class="modelValue ? 'text-slate-800 dark:text-white' : 'text-slate-400'">
				{{ displayValue }}
			</span>
			<Calendar class="w-4 h-4 text-slate-400" />
		</div>

		<!-- Custom month-year calendar popup -->
		<Transition
			enter-active-class="transition ease-out duration-100"
			enter-from-class="opacity-0 scale-95"
			enter-to-class="opacity-100 scale-100"
			leave-active-class="transition ease-in duration-75"
			leave-from-class="opacity-100 scale-100"
			leave-to-class="opacity-0 scale-95"
		>
			<div 
				v-if="isOpen" 
				class="absolute top-16 left-0 bg-white dark:bg-slate-905 border border-slate-200/80 dark:border-slate-800 shadow-xl rounded-xl p-3 z-50 w-64 text-center select-none"
			>
				<div class="flex items-center justify-between mb-3 px-1">
					<button type="button" @click.stop="prevYear" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 border-none bg-transparent cursor-pointer flex items-center justify-center">
						<ChevronLeft class="w-4 h-4 text-slate-600 dark:text-slate-400" />
					</button>
					<span class="text-xs font-black text-slate-800 dark:text-white">{{ pickerYear }}</span>
					<button type="button" @click.stop="nextYear" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 border-none bg-transparent cursor-pointer flex items-center justify-center">
						<ChevronRight class="w-4 h-4 text-slate-600 dark:text-slate-400" />
					</button>
				</div>
				<div class="grid grid-cols-3 gap-1">
					<button 
						v-for="month in MONTHS"
						:key="month.val"
						type="button"
						@click="selectMonth(month.val)"
						class="py-1.5 text-[11px] font-semibold rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-950/40 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors border-none bg-transparent cursor-pointer text-slate-700 dark:text-slate-300"
					>
						{{ month.name.slice(0, 3) }}
					</button>
				</div>
				<div v-if="showClear" class="border-t border-slate-100 dark:border-slate-800 mt-2 pt-2">
					<button 
						type="button"
						@click="clearDate"
						class="text-[10px] font-bold text-red-550 hover:underline border-none bg-transparent cursor-pointer"
					>
						Clear Date
					</button>
				</div>
			</div>
		</Transition>
	</div>
</template>
