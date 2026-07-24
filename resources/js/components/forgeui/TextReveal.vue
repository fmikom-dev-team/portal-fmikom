<script setup lang="ts">
import { computed, onMounted, ref } from "vue";

const props = withDefaults(
    defineProps<{
        text: string;
        staggerDelay?: number;
        className?: string;
    }>(),
    {
        staggerDelay: 0.05,
        className: "",
    },
);

// Split text into words, spaces, and newlines
const words = computed(() => {
    if (!props.text) return [];
    return props.text.split(/(\s+)/).filter((word) => word !== "");
});

const isMounted = ref(false);

onMounted(() => {
    // Trigger the staggered fade-in animations on mount
    setTimeout(() => {
        isMounted.value = true;
    }, 100);
});
</script>

<template>
    <div :class="['inline-block', className]">
        <template v-for="(word, index) in words" :key="index">
            <!-- Render line breaks if the segment contains a newline character -->
            <br v-if="word.includes('\n')" />

            <!-- Render spaces -->
            <span v-else-if="word === ' '" class="inline-block">&nbsp;</span>

            <!-- Render animated words -->
            <span
                v-else
                class="inline-block transform"
                :class="[
                    isMounted
                        ? 'opacity-100 translate-y-0'
                        : 'opacity-0 translate-y-6',
                ]"
                :style="{
                    transition: 'opacity 1.2s cubic-bezier(0.16, 1, 0.3, 1), transform 1.2s cubic-bezier(0.16, 1, 0.3, 1)',
                    transitionDelay: `${index * staggerDelay * 1000}ms`,
                    willChange: 'transform, opacity',
                }"
            >
                {{ word }}
            </span>
        </template>
    </div>
</template>
