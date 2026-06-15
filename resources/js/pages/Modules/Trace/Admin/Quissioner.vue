<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import HeroBanner from '@/components/ui/hero-banner/HeroBanner.vue';
import Pagination from '@/components/ui/Pagination.vue';
import TraceAdminLayout from '@/layouts/TraceAdminLayout.vue';
import { index, create } from '@/routes/quesionnaires';
import type { BreadcrumbItem } from '@/types';
import QuestionnaireCard from './components/QuestionnaireCard.vue';
import QuestionnaireFilters from './components/QuestionnaireFilters.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Manajemen Kuesioner',
        href: index().url,
    },
];

const props = defineProps<{ 
    kuesioners: {
        data: any[];
        links: any[];
        total: number;
        current_page: number;
        last_page: number;
    };
    filters: any;
    activeCount: number;
}>();

const cta = [
    {
        title: 'Buat Kuesioner',
        href: create().url,
        variant: 'default' as const,
        icon: Plus,
    },
];
</script>

<template>
    <Head title="Manajemen Kuesioner" />

    <TraceAdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-8 p-6 lg:p-10 max-w-7xl mx-auto w-full">
            
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div class="space-y-2">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600 dark:text-blue-400">
                        Ikhtisar Survei
                    </p>
                    <h1 class="text-5xl font-black tracking-tight text-foreground">
                        <template v-if="activeCount === undefined">
                             <span class="animate-pulse opacity-20">--</span>
                        </template>
                        <template v-else>
                            {{ activeCount }}
                        </template>
                        <span class="text-muted-foreground/40 font-medium ml-2">Aktif</span>
                    </h1>
                    <p class="max-w-md text-muted-foreground leading-relaxed">
                        Kelola dan pantau studi pelacakan institusional di seluruh departemen akademik dengan analitik presisi tinggi.
                    </p>
                </div>

                <Link :href="create().url">
                    <Button size="lg" class="h-14 px-8 rounded-2xl bg-blue-600 hover:bg-blue-700 shadow-xl shadow-blue-500/20 gap-3 text-base font-bold transition-all hover:scale-[1.02] active:scale-[0.98]">
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-white/20">
                            <Plus class="h-4 w-4" />
                        </div>
                        Buat Kuesioner
                    </Button>
                </Link>
            </div>

            <QuestionnaireFilters :filters="filters" />

            <div class="hidden md:grid grid-cols-[1fr_128px_144px_160px_110px] gap-6 px-5 text-[10px] font-bold uppercase tracking-[0.15em] text-muted-foreground/60">
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
                <div v-else class="flex flex-col items-center justify-center py-20 rounded-3xl border border-dashed border-border bg-muted/30">
                    <p class="text-muted-foreground font-medium">Kuesioner tidak ditemukan.</p>
                </div>
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

.space-y-4 > *:nth-child(1) { animation-delay: 0.05s; }
.space-y-4 > *:nth-child(2) { animation-delay: 0.1s; }
.space-y-4 > *:nth-child(3) { animation-delay: 0.15s; }
.space-y-4 > *:nth-child(4) { animation-delay: 0.2s; }
.space-y-4 > *:nth-child(5) { animation-delay: 0.25s; }
</style>
