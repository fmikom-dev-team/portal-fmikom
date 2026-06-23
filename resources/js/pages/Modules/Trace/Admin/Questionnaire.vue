<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import { Plus, FileText } from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { TPageHeader, TEmptyState } from "@/components/Trace";
import Pagination from "@/components/ui/Pagination.vue";
import TraceAdminLayout from "@/layouts/TraceAdminLayout.vue";
import { index, create } from "@/routes/module/trace/admin/questionnaires/index";
import type { BreadcrumbItem } from "@/types";
import type { Kuesioner, TraceFilters } from '@/types/trace';
import type { PaginationLinks } from '@/types/trace';
import QuestionnaireCard from "./components/QuestionnaireCard.vue";
import QuestionnaireFilters from "./components/QuestionnaireFilters.vue";

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: "Manajemen Kuesioner",
        href: index().url,
    },
];

const props = defineProps<{
    kuesioners: {
        data: Kuesioner[];
        links: PaginationLinks;
        total: number;
        current_page: number;
        last_page: number;
    };
    filters: TraceFilters;
    activeCount?: number;
}>();

const cta = [
    {
        title: "Buat Kuesioner",
        href: create().url,
        variant: "default" as const,
        icon: Plus,
    },
];
</script>

<template>
    <Head title="Manajemen Kuesioner" />

    <TraceAdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-8 p-6 lg:p-10 max-w-7xl mx-auto w-full">
            <TPageHeader
                title="Manajemen Kuesioner"
                description="Buat dan kelola kuesioner untuk melacak karir alumni."
                :icon="FileText"
            >
                <template #actions>
                    <div class="flex items-center gap-4">
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-black text-foreground">
                                <template v-if="activeCount === undefined">
                                    <span class="animate-pulse opacity-20"
                                        >--</span
                                    >
                                </template>
                                <template v-else>{{ activeCount }}</template>
                            </span>
                            <span
                                class="text-sm text-muted-foreground/60 font-medium"
                                >Aktif</span
                            >
                        </div>
                        <Link :href="create().url">
                            <Button
                                size="lg"
                                class="h-12 px-6 rounded-xl bg-[#0C447C] hover:bg-[#0C447C]/90 shadow-lg shadow-[#0C447C]/20 gap-2.5 font-bold transition-all hover:scale-[1.02] active:scale-[0.98] dark:bg-[#85B7EB] dark:text-slate-900 dark:hover:bg-[#85B7EB]/90 dark:shadow-[#85B7EB]/20"
                            >
                                <Plus class="h-4 w-4" />
                                Buat Kuesioner
                            </Button>
                        </Link>
                    </div>
                </template>
            </TPageHeader>

            <QuestionnaireFilters :filters="filters" />

            <div
                class="hidden md:grid grid-cols-[1fr_128px_144px_160px_110px] gap-6 px-5 text-[10px] font-bold uppercase tracking-[0.15em] text-muted-foreground/60"
            >
                <div>Judul Kuesioner</div>
                <div class="text-center">Status</div>
                <div class="text-center">Responden</div>
                <div class="text-right">Tanggal Dibuat</div>
                <div class="text-right">Aksi</div>
            </div>

            <div class="space-y-4">
                <template v-if="kuesioners.data.length > 0">
                    <QuestionnaireCard
                        v-for="item in kuesioners.data"
                        :key="item.id"
                        :kuesioner="item"
                    />
                </template>
                <TEmptyState
                    v-else
                    title="Kuesioner tidak ada."
                    description="Buat kuesioner baru untuk memulai"
                />
            </div>

            <Pagination
                :links="kuesioners.links"
                :total="kuesioners.total"
                :count="kuesioners.data.length"
                label="survei"
            />
        </div>
    </TraceAdminLayout>
</template>

<style scoped>
.space-y-4 > * {
    animation: slideIn 0.4s ease-out forwards;
    opacity: 0;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.space-y-4 > *:nth-child(1) {
    animation-delay: 0.05s;
}
.space-y-4 > *:nth-child(2) {
    animation-delay: 0.1s;
}
.space-y-4 > *:nth-child(3) {
    animation-delay: 0.15s;
}
.space-y-4 > *:nth-child(4) {
    animation-delay: 0.2s;
}
.space-y-4 > *:nth-child(5) {
    animation-delay: 0.25s;
}
</style>
