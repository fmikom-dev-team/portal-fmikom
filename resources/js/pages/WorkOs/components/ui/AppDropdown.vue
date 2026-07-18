<script setup lang="ts">
import { onMounted, onUnmounted, ref } from "vue";

const props = withDefaults(
	defineProps<{
		align?: "left" | "right";
		minWidth?: string;
	}>(),
	{
		align: "right",
		minWidth: "min-w-[160px]",
	},
);

const open = ref(false);
const containerRef = ref<HTMLElement | null>(null);

function handleOutside(e: MouseEvent) {
	if (containerRef.value && !containerRef.value.contains(e.target as Node)) {
		open.value = false;
	}
}

onMounted(() => document.addEventListener("mousedown", handleOutside));
onUnmounted(() => document.removeEventListener("mousedown", handleOutside));
</script>

<template>
    <div ref="containerRef" class="relative inline-block" style="font-family: var(--wos-font)">
        <!-- Trigger -->
        <div @click="open = !open" @keydown.enter="open = !open" @keydown.space.prevent="open = !open">
            <slot name="trigger" />
        </div>

        <!-- Panel -->
        <Transition
            enter-from-class="opacity-0 scale-[0.97] translate-y-0.5"
            enter-active-class="transition-all duration-150 ease-out"
            leave-to-class="opacity-0 scale-[0.97]"
            leave-active-class="transition-all duration-100"
        >
            <div
                v-if="open"
                :class="[
                    'absolute z-[70] mt-1.5 py-1 bg-white dark:bg-zinc-900 rounded-xl border border-[#e5e7eb] dark:border-zinc-800 shadow-lg overflow-hidden',
                    minWidth,
                    align === 'right' ? 'right-0' : 'left-0',
                ]"
                role="menu"
                @click="open = false"
            >
                <slot />
            </div>
        </Transition>
    </div>
</template>
