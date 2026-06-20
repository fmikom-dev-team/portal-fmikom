<script setup lang="ts">
defineProps<{
	label?: string;
	collapsed?: boolean;
}>();
</script>

<template>
    <div class="transition-all duration-300">
        <p
            v-if="label"
            class="px-3 text-[11px] font-semibold text-[#9ca3af] uppercase tracking-[0.08em] select-none transition-all duration-300 overflow-hidden"
            :class="collapsed ? 'opacity-0 h-0 pt-0 pb-0 my-0' : 'opacity-100 h-6 pt-1.5 pb-1'"
        >
            {{ label }}
        </p>
        <ul :class="[collapsed ? 'space-y-1' : 'space-y-0.5']" role="list">
            <slot />
        </ul>
    </div>
</template>
