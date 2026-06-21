<script setup lang="ts">
import Page from '@/pages/approval/Index.vue';

defineOptions({ inheritAttrs: false });
</script>

<template>
    <Page v-bind="$attrs" />
</template>
