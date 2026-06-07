<script setup lang="ts">
defineProps<{
	label?: string;
}>();
</script>

<template>
    <div>
        <p
            v-if="label"
            class="px-3 pt-1.5 pb-1 text-[11px] font-semibold text-[#9ca3af] uppercase tracking-[0.08em] select-none"
        >
            {{ label }}
        </p>
        <ul class="space-y-0.5" role="list">
            <slot />
        </ul>
    </div>
</template>
