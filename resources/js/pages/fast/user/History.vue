<script setup lang="ts">
import { useAttrs } from "vue";
import Page from "@/pages/Modules/Fast/Mahasiswa/History.vue";

defineOptions({ inheritAttrs: false });
const passThroughProps = useAttrs() as any;
</script>

<template>
    <Page v-bind="passThroughProps" />
</template>

