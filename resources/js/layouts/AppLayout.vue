<script setup lang="ts">
import IdleTimeout from "@/components/IdleTimeout.vue";
import AppLayout from "@/layouts/app/AppSidebarLayout.vue";
import type { BreadcrumbItem } from "@/types";

const props = withDefaults(
	defineProps<{
		breadcrumbs?: BreadcrumbItem[];
	}>(),
	{
		breadcrumbs: () => [],
	},
);
</script>

<template>
    <AppLayout :breadcrumbs="props.breadcrumbs">
        <slot />
        <IdleTimeout />
    </AppLayout>
</template>

