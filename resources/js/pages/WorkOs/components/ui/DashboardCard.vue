<script setup lang="ts">
defineProps<{
	title?: string;
	subtitle?: string;
	noPadding?: boolean;
	flat?: boolean;
}>();
</script>

<template>
    <div
        class="bg-white rounded-xl overflow-hidden"
        :class="flat ? 'border border-[#e5e7eb]' : 'border border-[#e5e7eb] hover:border-[#d1d5db] transition-colors'"
        style="font-family: var(--wos-font)"
    >
        <!-- Header -->
        <div v-if="title || $slots.header" class="flex items-center justify-between px-5 py-3.5 border-b border-[#f3f4f6]">
            <slot name="header">
                <div>
                    <h3 class="text-[13px] font-semibold text-[#111827]">{{ title }}</h3>
                    <p v-if="subtitle" class="text-[11.5px] text-[#6b7280] mt-0.5">{{ subtitle }}</p>
                </div>
            </slot>
            <div v-if="$slots.actions" class="flex items-center gap-2">
                <slot name="actions" />
            </div>
        </div>

        <!-- Body -->
        <div :class="noPadding ? '' : 'p-5'">
            <slot />
        </div>

        <!-- Footer -->
        <div v-if="$slots.footer" class="px-5 py-3 border-t border-[#f3f4f6] bg-[#f9fafb]/50">
            <slot name="footer" />
        </div>
    </div>
</template>
