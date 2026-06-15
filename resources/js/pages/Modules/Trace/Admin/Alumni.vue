<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Pagination from '@/components/ui/Pagination.vue';
import TraceAdminLayout from '@/layouts/TraceAdminLayout.vue';
import type { AlumniPagination } from '@/types/alumni';
import AlumniBannerHeader from './components/AlumniBannerHeader.vue';
import AlumniTable from './components/AlumniTable.vue';


interface Props {
    alumniList: AlumniPagination;
    totalAlumni: number;
    filters: {
        search?: string;
        status?: string;
        prodi?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs = [
    {
        title: 'Alumni',
        href: '/admin/alumni',
    },
];
</script>

<template>
    <Head title="Alumni" />

    <TraceAdminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 max-w-7xl mx-auto w-full"
        >
            <AlumniBannerHeader :total-alumni="totalAlumni" :filters="filters" />  

            <div class="flex flex-col">
                <AlumniTable :alumni="alumniList.data" />
                
                <Pagination 
                    :links="alumniList.links" 
                    :total="totalAlumni"
                    :count="alumniList.data.length"
                    label="alumni"
                />
            </div>
        </div>
    </TraceAdminLayout>
</template>
