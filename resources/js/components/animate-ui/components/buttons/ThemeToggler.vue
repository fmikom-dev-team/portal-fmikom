<script setup lang="ts">
const props = withDefaults(
    defineProps<{
        modelValue: boolean; // true = dark mode, false = light mode
        size?: number;
    }>(),
    {
        size: 18,
    },
);

const emit = defineEmits(["update:modelValue", "change"]);

const toggleTheme = () => {
    const newVal = !props.modelValue;
    emit("update:modelValue", newVal);
    emit("change", newVal);
};
</script>

<template>
    <button
        @click="toggleTheme"
        class="theme-toggle-btn relative flex items-center justify-center rounded-xl p-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 transition-colors focus:outline-none cursor-pointer"
        aria-label="Toggle dark mode"
        :class="{ 'theme-toggle-btn--dark': modelValue }"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            :width="size"
            :height="size"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="theme-toggle-svg"
        >
            <!-- Moon mask -->
            <mask id="moon-mask">
                <rect x="0" y="0" width="100%" height="100%" fill="white" />
                <circle
                    cx="24"
                    cy="0"
                    r="8"
                    fill="black"
                    class="mask-circle"
                />
            </mask>
            
            <!-- Sun/Moon Center Circle -->
            <circle
                cx="12"
                cy="12"
                r="5"
                fill="currentColor"
                mask="url(#moon-mask)"
                class="center-circle"
            />
            
            <!-- Sun Rays -->
            <g class="sun-rays" stroke="currentColor">
                <line x1="12" y1="1" x2="12" y2="3" />
                <line x1="12" y1="21" x2="12" y2="23" />
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
                <line x1="1" y1="12" x2="3" y2="12" />
                <line x1="21" y1="12" x2="23" y2="12" />
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
            </g>
        </svg>
    </button>
</template>

<style scoped>
.theme-toggle-svg {
    transform: rotate(0deg);
    transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    will-change: transform;
}

.center-circle {
    r: 5px;
    transition: r 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

.mask-circle {
    cx: 24px;
    cy: 0px;
    r: 8px;
    transition: cx 0.6s cubic-bezier(0.16, 1, 0.3, 1), cy 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

.sun-rays {
    opacity: 1;
    transform: scale(1);
    transform-origin: center;
    transition: opacity 0.4s ease-out, transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

/* Dark Mode States */
.theme-toggle-btn--dark .theme-toggle-svg {
    transform: rotate(40deg);
}

.theme-toggle-btn--dark .center-circle {
    r: 9px;
}

.theme-toggle-btn--dark .mask-circle {
    cx: 17px;
    cy: 7px;
}

.theme-toggle-btn--dark .sun-rays {
    opacity: 0;
    transform: scale(0.5);
}
</style>
