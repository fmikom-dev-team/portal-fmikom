<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import AppToast from "../components/ui/AppToast.vue";

defineProps<{
	title?: string;
}>();
</script>

<template>
    <Head>
        <title>{{ title ? `${title} – WorkOS` : 'WorkOS' }}</title>
    </Head>
    
    <div 
        class="min-h-screen bg-white dark:bg-zinc-900"
        style="font-family:'Inter',ui-sans-serif,system-ui,sans-serif"
    >
        <slot />
        <AppToast />
    </div>
</template>
<style>
/* Premium card shadow — multi-layer elevation (no hard border) */
.wos-card,
[class*="ring-1"][class*="ring-gray-900"] {
    box-shadow:
        0 0 0 1px rgba(0, 0, 0, 0.04),
        0 1px 3px rgba(0, 0, 0, 0.04),
        0 4px 12px rgba(0, 0, 0, 0.04),
        0 16px 40px rgba(0, 0, 0, 0.05);
}
</style>
