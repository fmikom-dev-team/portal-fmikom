<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { LogOut } from 'lucide-vue-next';

const props = withDefaults(defineProps<{
    label?: string;
    compact?: boolean;
}>(), {
    label: 'Keluar',
    compact: false,
});

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <button
        type="button"
        class="inline-flex items-center justify-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-100"
        :class="compact ? 'h-10 w-10 px-0' : 'h-10'"
        :title="label"
        @click="logout"
    >
        <LogOut class="size-4" />
        <span v-if="!compact">{{ label }}</span>
    </button>
</template>


