<script setup lang="ts">
defineProps<{
    currentIndex: number;
    totalSections: number;
    progressPercent: number;
}>();
</script>

<template>
    <div class="mb-8" v-if="totalSections > 1">
        <div
            class="mb-3 flex justify-between text-sm font-bold text-slate-500 dark:text-slate-400"
        >
            <span class="text-[#0C447C] dark:text-[#85B7EB]">
                Bagian {{ currentIndex + 1 }} dari
                {{ totalSections }}
            </span>
            <span>{{ progressPercent }}% Selesai</span>
        </div>
        <div
            class="h-3 w-full overflow-hidden rounded-full border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-800"
        >
            <div
                class="relative h-full bg-[#0C447C] dark:bg-[#85B7EB] transition-all duration-500 ease-out"
                :style="{ width: progressPercent + '%' }"
            >
                <div
                    class="absolute inset-0 bg-white/20"
                    style="
                        background-image: linear-gradient(
                            45deg,
                            rgba(255, 255, 255, 0.15) 25%,
                            transparent 25%,
                            transparent 50%,
                            rgba(255, 255, 255, 0.15) 50%,
                            rgba(255, 255, 255, 0.15) 75%,
                            transparent 75%,
                            transparent
                        );
                        background-size: 1rem 1rem;
                    "
                ></div>
            </div>
        </div>
    </div>
</template>
