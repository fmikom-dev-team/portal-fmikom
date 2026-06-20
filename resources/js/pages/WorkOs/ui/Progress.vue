<script setup lang="ts">
import { computed } from "vue";

interface Props {
	variant?: "line" | "circle" | "radial";
	value?: number;
	className?: string;
	indicatorClassName?: string;
	// Circle & Radial props
	size?: number;
	strokeWidth?: number;
	trackClassName?: string;
	// Radial specific props
	startAngle?: number;
	endAngle?: number;
	showLabel?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
	variant: "line",
	value: 0,
	className: "",
	indicatorClassName: "",
	size: 48,
	strokeWidth: 4,
	trackClassName: "",
	startAngle: -90,
	endAngle: 90,
	showLabel: false,
});

// Circle calculations
const radius = computed(() => (props.size - props.strokeWidth) / 2);
const circumference = computed(() => radius.value * 2 * Math.PI);
const circleOffset = computed(
	() => circumference.value - (props.value / 100) * circumference.value,
);

// Radial calculations
const angleRange = computed(() => props.endAngle - props.startAngle);
const progressAngle = computed(() => (props.value / 100) * angleRange.value);
const toRadians = (degrees: number) => (degrees * Math.PI) / 180;

const startX = computed(
	() => props.size / 2 + radius.value * Math.cos(toRadians(props.startAngle)),
);
const startY = computed(
	() => props.size / 2 + radius.value * Math.sin(toRadians(props.startAngle)),
);
const endX = computed(
	() =>
		props.size / 2 +
		radius.value * Math.cos(toRadians(props.startAngle + progressAngle.value)),
);
const endY = computed(
	() =>
		props.size / 2 +
		radius.value * Math.sin(toRadians(props.startAngle + progressAngle.value)),
);

const largeArc = computed(() => (progressAngle.value > 180 ? 1 : 0));

const radialPathData = computed(() =>
	[
		"M",
		startX.value,
		startY.value,
		"A",
		radius.value,
		radius.value,
		0,
		largeArc.value,
		1,
		endX.value,
		endY.value,
	].join(" "),
);

const radialTrackPathData = computed(() =>
	[
		"M",
		props.size / 2 + radius.value * Math.cos(toRadians(props.startAngle)),
		props.size / 2 + radius.value * Math.sin(toRadians(props.startAngle)),
		"A",
		radius.value,
		radius.value,
		0,
		angleRange.value > 180 ? 1 : 0,
		1,
		props.size / 2 + radius.value * Math.cos(toRadians(props.endAngle)),
		props.size / 2 + radius.value * Math.sin(toRadians(props.endAngle)),
	].join(" "),
);
</script>

<template>
  <!-- Line Variant -->
  <div
    v-if="variant === 'line'"
    data-slot="progress"
    class="relative h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
    :class="className"
  >
    <div
      data-slot="progress-indicator"
      class="h-full w-full flex-1 bg-blue-600 dark:bg-blue-500 transition-all duration-300 ease-out"
      :class="indicatorClassName"
      :style="{ transform: `translateX(-${100 - (value || 0)}%)` }"
    ></div>
  </div>

  <!-- Circle Variant -->
  <div
    v-else-if="variant === 'circle'"
    data-slot="progress-circle"
    class="relative inline-flex items-center justify-center"
    :class="className"
    :style="{ width: `${size}px`, height: `${size}px` }"
  >
    <svg class="absolute inset-0 -rotate-90" :width="size" :height="size" :viewBox="`0 0 ${size} ${size}`">
      <circle
        data-slot="progress-circle-track"
        :cx="size / 2"
        :cy="size / 2"
        :r="radius"
        stroke="currentColor"
        :stroke-width="strokeWidth"
        fill="none"
        class="text-slate-100 dark:text-slate-800"
        :class="trackClassName"
      />
      <circle
        data-slot="progress-circle-indicator"
        :cx="size / 2"
        :cy="size / 2"
        :r="radius"
        stroke="currentColor"
        :stroke-width="strokeWidth"
        fill="none"
        :stroke-dasharray="circumference"
        :stroke-dashoffset="circleOffset"
        stroke-linecap="round"
        class="text-blue-600 dark:text-blue-500 transition-all duration-300 ease-in-out"
        :class="indicatorClassName"
      />
    </svg>
    <div
      data-slot="progress-circle-content"
      class="relative z-10 flex items-center justify-center text-xs font-bold"
    >
      <slot>{{ Math.round(value) }}%</slot>
    </div>
  </div>

  <!-- Radial Variant -->
  <div
    v-else-if="variant === 'radial'"
    data-slot="progress-radial"
    class="relative inline-flex items-center justify-center"
    :class="className"
    :style="{ width: `${size}px`, height: `${size}px` }"
  >
    <svg :width="size" :height="size" :viewBox="`0 0 ${size} ${size}`">
      <path
        :d="radialTrackPathData"
        stroke="currentColor"
        :stroke-width="strokeWidth"
        fill="none"
        stroke-linecap="round"
        class="text-slate-100 dark:text-slate-800"
        :class="trackClassName"
      />
      <path
        :d="radialPathData"
        stroke="currentColor"
        :stroke-width="strokeWidth"
        fill="none"
        stroke-linecap="round"
        class="text-blue-600 dark:text-blue-500 transition-all duration-300 ease-in-out"
        :class="indicatorClassName"
      />
    </svg>
    <div v-if="showLabel || $slots.default" class="absolute inset-0 flex items-center justify-center">
      <slot>
        <span class="text-sm font-bold">{{ Math.round(value) }}%</span>
      </slot>
    </div>
  </div>
</template>
