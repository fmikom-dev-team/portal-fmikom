<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";

const props = withDefaults(
	defineProps<{
		modelValue: string;
		tabs: Array<{
			id: string;
			label: string;
			badge?: string | number;
			icon?: any;
			badgeClass?: string;
		}>;
		orientation?: "horizontal" | "vertical";
		variant?: "pill" | "underline" | "segment" | "sidebar";
		containerClass?: string;
		pillClass?: string;
		activeClass?: string;
		inactiveClass?: string;
	}>(),
	{
		orientation: "horizontal",
		variant: "pill",
		containerClass: "",
		pillClass: "",
		activeClass: "",
		inactiveClass: "",
	},
);

const emit = defineEmits<{
	(e: "update:modelValue", value: string): void;
	(e: "change", value: string): void;
}>();

const tabRefs = ref<HTMLElement[]>([]);
const pillStyle = ref({ top: "0px", height: "0px", left: "0px", width: "0px" });
const isReady = ref(false);

const updatePill = () => {
	if (props.variant === "sidebar") return;
	const activeIndex = props.tabs.findIndex((t) => t.id === props.modelValue);
	const activeEl = tabRefs.value[activeIndex];
	if (activeEl) {
		if (props.variant === "underline") {
			pillStyle.value = {
				top: `${activeEl.offsetTop + activeEl.offsetHeight - 2}px`,
				height: `2px`,
				left: `${activeEl.offsetLeft}px`,
				width: `${activeEl.offsetWidth}px`,
			};
		} else {
			pillStyle.value = {
				top: `${activeEl.offsetTop}px`,
				height: `${activeEl.offsetHeight}px`,
				left: `${activeEl.offsetLeft}px`,
				width: `${activeEl.offsetWidth}px`,
			};
		}
		isReady.value = true;
	}
};

onMounted(() => {
	nextTick(updatePill);
	window.addEventListener("resize", updatePill);
});

onUnmounted(() => {
	window.removeEventListener("resize", updatePill);
});

watch(
	() => props.modelValue,
	() => {
		nextTick(updatePill);
	},
);

watch(
	() => props.tabs,
	() => {
		nextTick(updatePill);
	},
	{ deep: true },
);

const defaultPillBg = computed(() => {
	if (props.pillClass.includes("bg-")) return "";
	if (props.variant === "pill" || props.variant === "sidebar") return "bg-white dark:bg-zinc-900 border border-slate-200/60 dark:border-zinc-800/80 shadow-sm";
	if (props.variant === "segment") return "bg-white dark:bg-zinc-800 shadow-sm";
	return "";
});
</script>

<template>
    <div 
        role="tablist"
        :class="[
            'relative flex transition-colors duration-150 max-w-full overflow-x-auto wos-scroll no-scrollbar select-none',
            orientation === 'vertical' ? 'flex-col space-y-0.5' : 'items-center',
            variant === 'pill' ? 'bg-slate-100/80 dark:bg-zinc-800/40 p-1 rounded-xl w-fit' : '',
            variant === 'segment' ? 'bg-slate-100/80 dark:bg-zinc-800/40 p-0.5 rounded-lg w-fit' : '',
            variant === 'underline' ? 'border-b border-slate-200 dark:border-zinc-800 gap-6 w-full' : '',
            containerClass
        ]"
    >
        <!-- Sliding background indicator for non-sidebar variants -->
        <div 
            v-if="variant !== 'sidebar'"
            :class="[
                'absolute z-0 rounded-lg',
                isReady ? 'transition-all duration-200 ease-out opacity-100' : 'opacity-0',
                defaultPillBg,
                variant === 'underline' ? 'bg-indigo-600 dark:bg-indigo-400' : '',
                pillClass
            ]"
            :style="pillStyle"
        />

        <!-- Tab Trigger Buttons -->
        <button
            v-for="(tab, index) in tabs" 
            :key="tab.id"
            :ref="el => { if (el) tabRefs[index] = el as any }"
            @click="emit('update:modelValue', tab.id); emit('change', tab.id)"
            type="button"
            role="tab"
            :aria-selected="modelValue === tab.id"
            :class="[
                'relative z-10 text-left transition-all duration-150 border-0 cursor-pointer outline-none flex items-center gap-1.5 select-none whitespace-nowrap',
                variant === 'sidebar'
                    ? 'px-3 py-2 rounded-xl text-xs font-semibold w-full'
                    : (variant === 'underline' 
                        ? 'px-1 pb-3 text-[13px] font-medium -mb-px' 
                        : (variant === 'segment' 
                            ? 'px-3 py-1.5 rounded-md text-xs font-semibold' 
                            : 'px-3.5 py-1.5 rounded-lg text-xs font-semibold')),
                variant === 'sidebar'
                    ? (modelValue === tab.id
                        ? (activeClass || 'bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 font-bold border border-gray-200/80 dark:border-zinc-700/60 shadow-xs')
                        : (inactiveClass || 'text-slate-500 hover:text-slate-900 dark:text-zinc-400 dark:hover:text-zinc-100 hover:bg-gray-100/60 dark:hover:bg-zinc-800/40 bg-transparent'))
                    : (modelValue === tab.id
                        ? (activeClass || (variant === 'underline' ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-slate-900 dark:text-zinc-100 font-bold'))
                        : (inactiveClass || (variant === 'underline' ? 'text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-zinc-200' : 'text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-zinc-200')))
            ]"
        >
            <component v-if="tab.icon" :is="tab.icon" class="w-4 h-4 shrink-0" />
            <span>{{ tab.label }}</span>
            <span 
                v-if="tab.badge !== undefined" 
                :class="[
                    'ml-1 text-[10px] px-1.5 py-0.5 rounded-full font-bold transition-all duration-200 shrink-0',
                    tab.badgeClass ? tab.badgeClass : (modelValue === tab.id 
                        ? 'bg-white/20 text-white' 
                        : 'bg-slate-200 dark:bg-zinc-700 text-slate-600 dark:text-zinc-300')
                ]"
            >
                {{ tab.badge }}
            </span>
        </button>
    </div>
</template>
