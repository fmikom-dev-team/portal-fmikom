<script setup lang="ts">
import { Head, Link, usePage } from "@inertiajs/vue3";
import TraceAlumniLayout from "@/layouts/TraceAlumniLayout.vue";
import type { PageProps } from "@inertiajs/core";
import type { BreadcrumbItem } from "@/types";
import Banner from "./components/Banner.vue";
defineProps<{
    moduleName: string;
    roleName: string;
}>();
interface TracePageProps extends PageProps {
    auth: { user: any };
}

const page = usePage<TracePageProps>();
const name = page.props?.auth?.user?.name;
const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: "Dashboard",
        href: "#",
    },
];
</script>

<template>
    <TraceAlumniLayout
        title="Dashboard"
        :breadcrumbs="breadcrumbItems"
        :role-name="roleName"
        :module-name="moduleName"
    >
        <Banner role-name="alumni" :user-name="name" />
    </TraceAlumniLayout>
</template>
