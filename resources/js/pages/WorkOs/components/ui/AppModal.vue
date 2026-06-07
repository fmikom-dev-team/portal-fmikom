<script setup lang="ts">
import { onMounted, onUnmounted } from "vue";

const props = withDefaults(
	defineProps<{
		show: boolean;
		title?: string;
		description?: string;
		size?:
			| "sm"
			| "md"
			| "lg"
			| "xl"
			| "2xl"
			| "3xl"
			| "4xl"
			| "5xl"
			| "6xl"
			| "7xl"
			| "full";
	}>(),
	{
		size: "md",
	},
);

const emit = defineEmits<(e: "close") => void>();

const sizes: Record<string, string> = {
	sm: "max-w-sm",
	md: "max-w-md",
	lg: "max-w-lg",
	xl: "max-w-xl",
	"2xl": "max-w-2xl",
	"3xl": "max-w-3xl",
	"4xl": "max-w-4xl",
	"5xl": "max-w-5xl",
	"6xl": "max-w-6xl",
	"7xl": "max-w-7xl",
	full: "max-w-[calc(100vw-2rem)] md:max-w-[calc(100vw-4rem)] mx-4 md:mx-8",
};

function handleKeydown(e: KeyboardEvent) {
	if (e.key === "Escape" && props.show) emit("close");
}

onMounted(() => document.addEventListener("keydown", handleKeydown));
onUnmounted(() => document.removeEventListener("keydown", handleKeydown));
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-from-class="opacity-0"
            enter-active-class="transition-opacity duration-200"
            leave-to-class="opacity-0"
            leave-active-class="transition-opacity duration-150"
        >
            <dialog
                v-if="show"
                open
                class="fixed inset-0 z-60 flex items-center justify-center bg-transparent border-0 w-screen h-screen max-w-none max-h-none p-0"
                :aria-label="title"
                @click.self="emit('close')"
            >
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/25 backdrop-blur-[2px]" aria-hidden="true" @click="emit('close')" />

                <!-- Modal panel -->
                <Transition
                    enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-active-class="transition-all duration-300 ease-out"
                    leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    leave-active-class="transition-all duration-200 ease-in"
                >
                    <div
                        v-if="show"
                        :class="['relative bg-white rounded-lg shadow-xl w-full overflow-hidden transition-all', sizes[size]]"
                        style="font-family: var(--wos-font)"
                    >
                        <!-- Header -->
                        <div v-if="title || $slots.header" class="flex items-start justify-between px-6 py-5 border-b border-[#f3f4f6]">
                            <slot name="header">
                                <div>
                                    <h2 class="text-[15px] font-semibold text-[#111827]">{{ title }}</h2>
                                    <p v-if="description" class="text-[12.5px] text-[#6b7280] mt-1">{{ description }}</p>
                                </div>
                            </slot>
                            <button
                                class="ml-4 p-1.5 rounded-md text-[#9ca3af] hover:text-[#374151] hover:bg-[#f3f4f6] transition-colors shrink-0"
                                aria-label="Close"
                                @click="emit('close')"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="px-6 py-5">
                            <slot />
                        </div>

                        <!-- Footer -->
                        <div v-if="$slots.footer" class="px-6 py-4 border-t border-[#f3f4f6] flex items-center justify-end gap-2 bg-[#f9fafb]">
                            <slot name="footer" />
                        </div>
                    </div>
                </Transition>
            </dialog>
        </Transition>
    </Teleport>
</template>
