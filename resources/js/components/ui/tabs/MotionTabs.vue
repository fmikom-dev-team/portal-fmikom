<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, nextTick } from "vue";

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
		variant?: "pill" | "underline" | "segment";
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

const updatePill = () => {
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
	}
};

onMounted(() => {
	nextTick(updatePill);
	setTimeout(updatePill, 50);
	setTimeout(updatePill, 150);
	setTimeout(updatePill, 300); // layout settle helper
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
		nextTick(() => setTimeout(updatePill, 50));
	},
	{ deep: true },
);
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
        <!-- Sliding background pill/underline indicator -->
        <div 
            :class="[
                'absolute transition-all duration-350 cubic-bezier(0.16, 1, 0.3, 1) z-0',
                variant === 'pill' ? 'bg-white dark:bg-zinc-900 rounded-lg shadow-sm border border-slate-200/40 dark:border-zinc-800/50' : '',
                variant === 'segment' ? 'bg-white dark:bg-zinc-800 rounded-md shadow-sm' : '',
                variant === 'underline' ? 'bg-[#2563eb] dark:bg-blue-500' : '',
                pillStyle.width === '0px' ? 'opacity-0' : 'opacity-100',
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
                'relative z-10 text-left transition-colors border-0 cursor-pointer bg-transparent outline-none flex items-center gap-1.5 select-none transition-all duration-200 whitespace-nowrap',
                variant === 'underline' 
                    ? 'px-1 pb-3 text-[13px] font-medium -mb-px' 
                    : (variant === 'segment' 
                        ? 'px-3 py-1.5 rounded-md text-xs font-semibold' 
                        : 'px-3.5 py-1.5 rounded-lg text-xs font-semibold'),
                modelValue === tab.id
                    ? (activeClass || (variant === 'underline' ? 'text-[#2563eb] dark:text-blue-400 font-semibold' : 'text-[#111827] dark:text-zinc-100'))
                    : (inactiveClass || (variant === 'underline' ? 'text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-zinc-200' : 'text-slate-500 hover:text-slate-800 dark:text-zinc-450 dark:hover:text-zinc-300'))
            ]"
        >
            <component v-if="tab.icon" :is="tab.icon" class="w-4 h-4 shrink-0" />
            <span>{{ tab.label }}</span>
            <span 
                v-if="tab.badge !== undefined" 
                :class="[
                    'ml-1 text-[10px] px-1.5 py-0.5 rounded-full font-bold transition-all duration-200 shrink-0',
                    modelValue === tab.id 
                        ? 'bg-red-500 text-white' 
                        : 'bg-slate-200 dark:bg-zinc-700 text-slate-600 dark:text-zinc-350',
                    tab.badgeClass
                ]"
            >
                {{ tab.badge }}
            </span>
        </button>
    </div>
</template>
