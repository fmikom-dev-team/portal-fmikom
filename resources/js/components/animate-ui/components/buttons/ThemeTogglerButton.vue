<script setup lang="ts">
import { computed, nextTick } from "vue";
import { Sun, Moon, Laptop } from "lucide-vue-next";

interface Props {
  modelValue?: string; // 'light' | 'dark' | 'system' | 'auto'
  modes?: ('light' | 'dark' | 'system' | 'auto')[];
  variant?: 'default' | 'accent' | 'destructive' | 'outline' | 'secondary' | 'ghost';
  size?: 'default' | 'sm' | 'lg';
  direction?: 'btt' | 'ttb' | 'ltr' | 'rtl';
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: "light",
  modes: () => ["light", "dark"],
  variant: "default",
  size: "default",
  direction: "ltr",
});

const emit = defineEmits(["update:modelValue", "change"]);

// Standardize modes and values
const activeMode = computed(() => {
  const val = props.modelValue;
  return val === "auto" ? "system" : val;
});

const availableModes = computed(() => {
  return props.modes.map(m => m === "auto" ? "system" : m);
});

// Map icon component
const activeIcon = computed(() => {
  if (activeMode.value === "dark") return Moon;
  if (activeMode.value === "system") return Laptop;
  return Sun;
});

// Cycling logic with View Transition animation
const cycleTheme = () => {
  const currentIdx = availableModes.value.indexOf(activeMode.value);
  if (currentIdx === -1) return;
  
  const nextIdx = (currentIdx + 1) % availableModes.value.length;
  const nextMode = props.modes[nextIdx];
  
  const isAppearanceTransition = (document as any).startViewTransition
    && !window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (!isAppearanceTransition) {
    emit("update:modelValue", nextMode);
    emit("change", nextMode);
    return;
  }

  (document as any).startViewTransition(async () => {
    emit("update:modelValue", nextMode);
    emit("change", nextMode);
    await nextTick();
  });
};

// Transition class naming
const transitionName = computed(() => `slide-${props.direction}`);
</script>

<template>
  <button
    @click="cycleTheme"
    type="button"
    :class="[
      'relative flex items-center justify-center rounded-xl transition-all duration-200 overflow-hidden cursor-pointer select-none focus:outline-none shrink-0',
      
      // Variants
      variant === 'outline' || variant === 'default' 
        ? 'border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/80 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' 
        : '',
      variant === 'ghost' 
        ? 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' 
        : '',
      variant === 'secondary' 
        ? 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200' 
        : '',
        
      // Sizes
      size === 'sm' ? 'h-8 w-8' : '',
      size === 'lg' ? 'h-10 w-10' : '',
      size === 'default' ? 'h-9 w-9' : '',
    ]"
    aria-label="Toggle Theme"
  >
    <div class="relative w-full h-full flex items-center justify-center">
      <transition :name="transitionName">
        <component 
          :is="activeIcon" 
          :key="activeMode" 
          class="w-[18px] h-[18px] shrink-0" 
        />
      </transition>
    </div>
  </button>
</template>

<style scoped>
/* Base transition settings */
.slide-ltr-enter-active, .slide-ltr-leave-active,
.slide-rtl-enter-active, .slide-rtl-leave-active,
.slide-ttb-enter-active, .slide-ttb-leave-active,
.slide-btt-enter-active, .slide-btt-leave-active {
  transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
  position: absolute;
}

/* LTR: old slides right, new enters from left */
.slide-ltr-enter-from {
  transform: translateX(-100%);
  opacity: 0;
}
.slide-ltr-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

/* RTL: old slides left, new enters from right */
.slide-rtl-enter-from {
  transform: translateX(100%);
  opacity: 0;
}
.slide-rtl-leave-to {
  transform: translateX(-100%);
  opacity: 0;
}

/* TTB: old slides down, new enters from top */
.slide-ttb-enter-from {
  transform: translateY(-100%);
  opacity: 0;
}
.slide-ttb-leave-to {
  transform: translateY(100%);
  opacity: 0;
}

/* BTT: old slides up, new enters from bottom */
.slide-btt-enter-from {
  transform: translateY(100%);
  opacity: 0;
}
.slide-btt-leave-to {
  transform: translateY(-100%);
  opacity: 0;
}
</style>
