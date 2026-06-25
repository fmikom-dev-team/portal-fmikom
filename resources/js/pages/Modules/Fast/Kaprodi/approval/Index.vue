<script setup lang="ts">
import Page from '@/pages/approval/Index.vue';
import { useAttrs } from 'vue';

defineOptions({ inheritAttrs: false });
const passThroughProps = useAttrs() as any;
</script>

<template>
    <Page v-bind="passThroughProps" />
</template>

