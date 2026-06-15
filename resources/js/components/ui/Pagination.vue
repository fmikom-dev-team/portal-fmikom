<script setup lang="ts">
import { Link } from "@inertiajs/vue3";

defineProps<{
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    total?: number;
    count?: number;
    label?: string;
}>();
</script>

<template>
    <div
        class="flex flex-col items-center justify-between gap-4 pt-4 sm:flex-row"
    >
        <p
            v-if="total !== undefined"
            class="text-sm font-medium text-muted-foreground"
        >
            Menampilkan
            <span class="font-bold text-foreground">{{ count }}</span> dari
            {{ total }} {{ label || "data" }}
        </p>
        <div v-else></div>

        <nav class="flex items-center gap-1">
            <Link
                v-for="link in links"
                :key="link.label"
                :href="link.url || '#'"
                class="flex h-10 min-w-[40px] items-center justify-center rounded-xl px-2 text-sm font-bold transition-colors"
                :class="[
                    link.active
                        ? 'bg-blue-600 px-4 text-white shadow-lg shadow-blue-500/20'
                        : 'text-muted-foreground hover:bg-muted',
                    !link.url ? 'pointer-events-none opacity-40' : '',
                    link.label.includes('Previous') ||
                    link.label.includes('Next')
                        ? 'w-auto bg-muted/50 px-4'
                        : '',
                ]"
                v-html="link.label"
            />
        </nav>
    </div>
</template>
