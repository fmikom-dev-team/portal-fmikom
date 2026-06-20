<script setup lang="ts">
import { computed, nextTick, ref, watch } from "vue";

const props = withDefaults(
	defineProps<{
		modelValue: string;
		suggestions: string[];
		placeholder?: string;
		inputClass?: string;
	}>(),
	{
		placeholder: "",
		inputClass:
			"w-full bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg p-2 text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-indigo-500",
	},
);

const emit = defineEmits<(e: "update:modelValue", value: string) => void>();

const isOpen = ref(false);
const activeIndex = ref(-1);
const listRef = ref<HTMLElement | null>(null);

const filteredSuggestions = computed(() => {
	const query = props.modelValue ? props.modelValue.trim().toLowerCase() : "";
	if (!query) {
		return props.suggestions.slice(0, 15);
	}
	return props.suggestions
		.filter((item) => item.toLowerCase().includes(query))
		.slice(0, 15);
});

watch(filteredSuggestions, () => {
	activeIndex.value = -1;
});

const selectSuggestion = (item: string) => {
	emit("update:modelValue", item);
	isOpen.value = false;
	activeIndex.value = -1;
};

const onArrowDown = () => {
	if (!isOpen.value) {
		isOpen.value = true;
		return;
	}
	if (filteredSuggestions.value.length === 0) return;
	activeIndex.value =
		(activeIndex.value + 1) % filteredSuggestions.value.length;
	scrollToActive();
};

const onArrowUp = () => {
	if (!isOpen.value) return;
	if (filteredSuggestions.value.length === 0) return;
	activeIndex.value =
		(activeIndex.value - 1 + filteredSuggestions.value.length) %
		filteredSuggestions.value.length;
	scrollToActive();
};

const onEnter = () => {
	if (
		isOpen.value &&
		activeIndex.value >= 0 &&
		activeIndex.value < filteredSuggestions.value.length
	) {
		selectSuggestion(filteredSuggestions.value[activeIndex.value]);
	} else {
		isOpen.value = false;
	}
};

const onInput = (e: Event) => {
	const val = (e.target as HTMLInputElement).value;
	emit("update:modelValue", val);
	isOpen.value = true;
};

const onBlur = () => {
	setTimeout(() => {
		isOpen.value = false;
	}, 150);
};

const onFocus = () => {
	isOpen.value = true;
};

const scrollToActive = () => {
	nextTick(() => {
		if (!listRef.value) return;
		const activeEl = listRef.value.querySelector(
			".autocomplete-item-active",
		) as HTMLElement;
		if (activeEl) {
			const listEl = listRef.value;
			const activeTop = activeEl.offsetTop;
			const activeBottom = activeTop + activeEl.clientHeight;
			const listScrollTop = listEl.scrollTop;
			const listHeight = listEl.clientHeight;

			if (activeTop < listScrollTop) {
				listEl.scrollTop = activeTop;
			} else if (activeBottom > listScrollTop + listHeight) {
				listEl.scrollTop = activeBottom - listHeight;
			}
		}
	});
};
</script>

<template>
    <div class="relative w-full">
        <input
            type="text"
            :value="modelValue"
            @input="onInput"
            @focus="onFocus"
            @blur="onBlur"
            @keydown.down.prevent="onArrowDown"
            @keydown.up.prevent="onArrowUp"
            @keydown.enter.prevent="onEnter"
            @keydown.esc="isOpen = false"
            :placeholder="placeholder"
            :class="inputClass"
        />
        
        <div 
            v-if="isOpen && filteredSuggestions.length > 0"
            ref="listRef"
            class="absolute z-50 w-full mt-1 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-lg shadow-xl max-h-60 overflow-y-auto scrollbar-thin divide-y divide-slate-100 dark:divide-zinc-850"
        >
            <div
                v-for="(item, index) in filteredSuggestions"
                :key="item"
                @mousedown.prevent="selectSuggestion(item)"
                :class="[
                    'px-3 py-2 text-[11px] font-medium transition-colors cursor-pointer select-none',
                    index === activeIndex 
                        ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 autocomplete-item-active' 
                        : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-850/50'
                ]"
            >
                {{ item }}
            </div>
        </div>
    </div>
</template>
