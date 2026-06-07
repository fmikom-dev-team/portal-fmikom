<script setup lang="ts">
import Placeholder from "../Placeholder.vue";

defineProps<{ title?: string }>();
</script>

<template>
    <Placeholder :title="title || 'radar - detectionrules'" />
</template>