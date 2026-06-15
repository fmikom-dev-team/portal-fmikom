<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import TraceAlumniLayout from '@/layouts/TraceAlumniLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    ArrowLeft,
    ArrowRight,
    CalendarDays,
    MapPin,
    Ticket,
    CalendarCheck,
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

const statusConfig: Record<string, { label: string; dotClass: string; textClass: string; bgClass: string }> = {
    registered: {
        label: 'Terdaftar',
        dotClass: 'bg-emerald-500',
        textClass: 'text-emerald-700 dark:text-emerald-400',
        bgClass: 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800',
    },
    attended: {
        label: 'Hadir',
        dotClass: 'bg-blue-500',
        textClass: 'text-blue-700 dark:text-blue-400',
        bgClass: 'bg-blue-50 dark:bg-blue-950/30 border-blue-200 dark:border-blue-800',
    },
    cancelled: {
        label: 'Dibatalkan',
        dotClass: 'bg-red-500',
        textClass: 'text-red-600 dark:text-red-400',
        bgClass: 'bg-red-50 dark:bg-red-950/30 border-red-200 dark:border-red-800',
    },
};

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
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <Button as-child variant="ghost" size="icon-sm" class="rounded-xl text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/30">
                        <Link href="/trace/events">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <div class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 mb-0.5">
                            <Ticket class="h-4 w-4" />
                            <span class="text-[10px] font-black uppercase tracking-widest">Event Saya</span>
                        </div>
                        <h1 class="text-2xl font-black tracking-tight text-slate-800 dark:text-white">Event Terdaftar</h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            Daftar event yang telah Anda daftarkan.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Registrations List -->
            <div v-if="registrations.data.length > 0" class="space-y-3">
                <Card
                    v-for="reg in registrations.data"
                    :key="reg.id"
                    class="group rounded-2xl border-slate-100 dark:border-slate-800 shadow-xs transition-all hover:shadow-md hover:border-emerald-200 dark:hover:border-emerald-800/50"
                >
                    <CardContent class="p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <!-- Left: Event Info -->
                            <div class="flex-1 min-w-0 space-y-2">
                                <div class="flex items-center gap-3">
                                    <h3 class="text-base font-bold text-slate-800 dark:text-white truncate group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">
                                        {{ reg.event.title }}
                                    </h3>
                                    <!-- Status Badge -->
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider shrink-0"
                                        :class="statusConfig[reg.status]?.bgClass ?? statusConfig.registered.bgClass"
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full" :class="statusConfig[reg.status]?.dotClass ?? statusConfig.registered.dotClass" />
                                        <span :class="statusConfig[reg.status]?.textClass ?? statusConfig.registered.textClass">
                                            {{ statusConfig[reg.status]?.label ?? reg.status }}
                                        </span>
                                    </span>
                                </div>
                                <div class="flex flex-wrap gap-4">
                                    <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                        <CalendarDays class="h-3.5 w-3.5 text-emerald-500 dark:text-emerald-400 shrink-0" />
                                        {{ formatDate(reg.event.event_date) }}
                                    </div>
                                    <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                        <MapPin class="h-3.5 w-3.5 text-emerald-500 dark:text-emerald-400 shrink-0" />
                                        <span class="truncate">{{ reg.event.location }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 dark:text-slate-500">
                                        Didaftarkan: {{ formatDateTime(reg.registered_at) }}
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Action -->
                            <div class="shrink-0">
                                <Button as-child size="sm" variant="outline" class="rounded-lg text-xs border-emerald-200 text-emerald-700 hover:bg-emerald-50 dark:border-emerald-800 dark:text-emerald-400 dark:hover:bg-emerald-950/30">
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
            <div v-else class="flex flex-col items-center justify-center py-16 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 mb-4">
                    <CalendarCheck class="h-8 w-8 text-emerald-300 dark:text-emerald-700" />
                </div>
                <p class="text-sm font-bold text-slate-500 dark:text-slate-400 mb-1">Belum ada event terdaftar</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mb-4">Jelajahi event yang tersedia dan daftar sekarang.</p>
                <Button as-child size="sm" class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl">
                    <Link href="/trace/events" class="inline-flex items-center gap-1.5">
                        Jelajahi Events
                        <ArrowRight class="h-3.5 w-3.5" />
                    </Link>
                </Button>
            </div>

            <!-- Pagination -->
            <div v-if="registrations.last_page > 1" class="flex items-center justify-between">
                <p class="text-xs text-slate-400 dark:text-slate-500">
                    Halaman {{ registrations.current_page }} dari {{ registrations.last_page }}
                    ({{ registrations.total }} registrasi)
                </p>
                <nav class="flex items-center gap-1">
                    <template v-for="(link, idx) in registrations.links" :key="idx">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2.5 text-xs font-bold transition-colors"
                            :class="link.active
                                ? 'bg-emerald-600 text-white shadow-sm'
                                : 'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800'"
                            v-html="link.label"
                            preserve-scroll
                        />
                        <span
                            v-else
                            class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2.5 text-xs text-slate-300 dark:text-slate-600"
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </div>
        </div>
    </TraceAlumniLayout>
</template>
