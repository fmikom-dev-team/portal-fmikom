<script setup lang="ts">
defineProps<{
	label?: string;
	collapsed?: boolean;
}>();
</script>

<template>
    <div class="transition-all duration-300">
        <p
            v-if="label && !collapsed"
            class="px-3 text-[11px] font-semibold text-[#9ca3af] dark:text-zinc-600 uppercase tracking-[0.08em] select-none transition-all duration-300 overflow-hidden h-6 pt-1.5 pb-1 opacity-100"
        >
            {{ label }}
        </p>
        <ul :class="[collapsed ? 'space-y-1.5' : 'space-y-0.5']" role="list">
            <slot />
        </ul>
    </div>
</template>
