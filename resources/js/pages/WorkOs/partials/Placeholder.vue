<script setup lang="ts">
defineProps<{
	title?: string;
}>();
</script>

<template>
    <div class="flex flex-col items-center justify-center min-h-[60vh] p-8" style="font-family: var(--wos-font)">
        <div class="w-12 h-12 rounded-2xl bg-[#f3f4f6] border border-[#e5e7eb] flex items-center justify-center mb-5">
            <svg class="w-5 h-5 text-[#9ca3af]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
        </div>
        <h2 class="text-[16px] font-semibold text-[#111827] mb-1.5 capitalize">
            {{ title ? title.replace(/-/g, ' ').replace(/\./g, ' / ') : 'Coming Soon' }}
        </h2>
        <p class="text-[13px] text-[#6b7280] text-center max-w-xs leading-relaxed">
            This section is under development and will be available soon.
        </p>
        <div class="mt-6 flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-[#10b981]" aria-hidden="true" />
            <span class="text-[11.5px] text-[#6b7280] font-medium">In Progress</span>
        </div>
    </div>
</template>
