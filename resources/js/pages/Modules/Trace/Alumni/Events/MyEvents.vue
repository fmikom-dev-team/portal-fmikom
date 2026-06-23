<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import TraceAlumniLayout from '@/layouts/TraceAlumniLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { TPageHeader, TStatusBadge, TEmptyState, TPagination } from '@/components/trace';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    ArrowLeft,
    ArrowRight,
    CalendarDays,
    MapPin,
    Ticket,
} from 'lucide-vue-next';

interface Registration {
    id: number;
    status: 'registered' | 'attended' | 'cancelled';
    registered_at: string;
    event: {
        id: number;
        title: string;
        event_date: string;
        location: string;
    };
}

interface PaginatedRegistrations {
    data: Registration[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    registrations: PaginatedRegistrations;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/trace' },
    { title: 'Events', href: '/trace/events' },
    { title: 'Event Saya', href: '/trace/events/my-events' },
];

const formatDate = (dateStr: string) => {
    try {
        return new Date(dateStr).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        });
    } catch {
        return dateStr;
    }
};

const formatDateTime = (dateStr: string) => {
    try {
        return new Date(dateStr).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch {
        return dateStr;
    }
};
</script>

<template>
    <TraceAlumniLayout title="Event Saya" role-name="Alumni" :breadcrumbs="breadcrumbs">
        <div class="mx-auto space-y-6">
            <!-- Header -->
            <TPageHeader title="Event Saya" description="Event yang sudah Anda ikuti dan yang akan datang." :icon="Ticket">
                <template #actions>
                    <Button as-child variant="ghost" size="icon-sm" class="rounded-xl text-slate-400 hover:text-[#0C447C] hover:bg-[#0C447C]/5 dark:hover:text-[#85B7EB] dark:hover:bg-[#85B7EB]/10">
                        <Link href="/trace/events">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                </template>
            </TPageHeader>

            <!-- Registrations List -->
            <div v-if="registrations.data.length > 0" class="space-y-3">
                <Card
                    v-for="reg in registrations.data"
                    :key="reg.id"
                    class="group rounded-2xl border border-slate-200/60 dark:border-zinc-800 shadow-xs transition-all hover:shadow-md hover:border-[#85B7EB]/40 dark:hover:border-[#85B7EB]/30"
                >
                    <CardContent class="p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <!-- Left: Event Info -->
                            <div class="flex-1 min-w-0 space-y-2">
                                <div class="flex items-center gap-3">
                                    <h3 class="text-base font-bold text-slate-800 dark:text-white truncate group-hover:text-[#0C447C] dark:group-hover:text-[#85B7EB] transition-colors">
                                        {{ reg.event.title }}
                                    </h3>
                                    <!-- Status Badge -->
                                    <TStatusBadge
                                        :status="reg.status === 'attended' ? 'hadir' : reg.status"
                                        :label="reg.status === 'attended' ? 'Hadir' : undefined"
                                        size="sm"
                                    />
                                </div>
                                <div class="flex flex-wrap gap-4">
                                    <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                        <CalendarDays class="h-3.5 w-3.5 text-[#0C447C] dark:text-[#85B7EB] shrink-0" />
                                        {{ formatDate(reg.event.event_date) }}
                                    </div>
                                    <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                        <MapPin class="h-3.5 w-3.5 text-[#0C447C] dark:text-[#85B7EB] shrink-0" />
                                        <span class="truncate">{{ reg.event.location }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 dark:text-slate-500">
                                        Didaftarkan: {{ formatDateTime(reg.registered_at) }}
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Action -->
                            <div class="shrink-0">
                                <Button as-child size="sm" variant="outline" class="rounded-lg text-xs border-[#0C447C]/20 text-[#0C447C] hover:bg-[#0C447C]/5 dark:border-[#85B7EB]/30 dark:text-[#85B7EB] dark:hover:bg-[#85B7EB]/10">
                                    <Link :href="`/trace/events/${reg.event.id}`" class="inline-flex items-center gap-1.5">
                                        Lihat Event
                                        <ArrowRight class="h-3 w-3" />
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <TEmptyState
                v-else
                :icon="CalendarDays"
                title="Belum ada event terdaftar"
                description="Jelajahi event yang tersedia dan daftar sekarang."
                action-label="Jelajahi Events"
                action-href="/trace/events"
            />

            <!-- Pagination -->
            <TPagination v-if="registrations.last_page > 1" :links="registrations.links" />
        </div>
    </TraceAlumniLayout>
</template>
