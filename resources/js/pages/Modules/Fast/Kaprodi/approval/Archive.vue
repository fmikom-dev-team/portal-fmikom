<script setup lang="ts">
import Page from '@/pages/Modules/Fast/Shared/approval/Archive.vue';
import { useAttrs } from 'vue';

defineOptions({ inheritAttrs: false });
const passThroughProps = useAttrs() as any;
</script>

<template>
    <Page v-bind="passThroughProps" />
</template>

