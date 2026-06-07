<script setup lang="ts">
defineProps<{
	open: boolean;
}>();

const _emit = defineEmits<{
	"update:open": [value: boolean];
}>();
</script>

<template>
    <!-- Overlay -->
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="open"
            class="fixed inset-0 z-30 bg-zinc-900/40 backdrop-blur-sm lg:hidden"
            @click="emit('update:open', false)"
        />
    </Transition>
</template>
