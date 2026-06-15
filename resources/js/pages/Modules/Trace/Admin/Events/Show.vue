<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import TraceAdminLayout from '@/layouts/TraceAdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    ArrowLeft,
    CalendarDays,
    Clock,
    MapPin,
    Users,
    UserCheck,
    Mail,
    CalendarCheck,
    AlertCircle,
    CheckCircle2,
    XCircle,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Registration {
    id: number;
    status: string;
    registered_at: string;
    attended_at: string | null;
    user: {
        name: string;
        email: string;
    };
}

interface EventDetail {
    id: number;
    title: string;
    description: string;
    location: string;
    event_date: string;
    event_time_start: string;
    event_time_end: string;
    registration_deadline: string;
    max_participants: number | null;
    poster_path: string | null;
    status: 'draft' | 'published' | 'closed';
    registrations_count: number;
}

const props = defineProps<{
    event: EventDetail;
    registrations: Registration[];
    attendanceCount: number;
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Dashboard', href: '/trace/admin' },
    { title: 'Events', href: '/admin/events' },
    { title: props.event.title, href: `/admin/events/${props.event.id}` },
]);

const statusConfig: Record<string, { label: string; classes: string }> = {
    draft: {
        label: 'Draft',
        classes: 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
    },
    published: {
        label: 'Published',
        classes: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/30 dark:text-emerald-400 dark:border-emerald-800',
    },
    closed: {
        label: 'Closed',
        classes: 'bg-red-50 text-red-600 border-red-200 dark:bg-red-950/30 dark:text-red-400 dark:border-red-800',
    },
};

const regStatusConfig: Record<string, { label: string; classes: string }> = {
    registered: {
        label: 'Terdaftar',
        classes: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/30 dark:text-emerald-400',
    },
    attended: {
        label: 'Hadir',
        classes: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/30 dark:text-blue-400',
    },
    cancelled: {
        label: 'Dibatalkan',
        classes: 'bg-red-50 text-red-600 border-red-200 dark:bg-red-950/30 dark:text-red-400',
    },
};

const formatDate = (dateStr: string) => {
    try {
        return new Date(dateStr).toLocaleDateString('id-ID', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
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

const toggleStatus = (newStatus: string) => {
    router.put(`/admin/events/${props.event.id}/status`, { status: newStatus }, {
        preserveScroll: true,
    });
};

const loadingAttendance = ref<number | null>(null);

const toggleAttendance = (registrationId: number) => {
    loadingAttendance.value = registrationId;
    router.post(`/admin/events/${props.event.id}/registrations/${registrationId}/attendance`, {}, {
        preserveScroll: true,
        onFinish: () => {
            loadingAttendance.value = null;
        },
    });
};
</script>

<template>
    <TraceAdminLayout title="Detail Event" :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-5xl space-y-6">
            <!-- Back + Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <Button as-child variant="ghost" size="icon-sm" class="rounded-xl text-slate-400 hover:text-sky-600 hover:bg-sky-50 dark:hover:bg-sky-950/30">
                        <Link href="/admin/events">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <div class="flex items-center gap-1.5 text-sky-600 dark:text-sky-400 mb-0.5">
                            <CalendarCheck class="h-4 w-4" />
                            <span class="text-[10px] font-black uppercase tracking-widest">Detail Event</span>
                        </div>
                        <h1 class="text-2xl font-black tracking-tight text-slate-800 dark:text-white">
                            {{ event.title }}
                        </h1>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span
                        class="inline-flex items-center rounded-lg border px-3 py-1 text-[11px] font-bold uppercase tracking-wider"
                        :class="statusConfig[event.status]?.classes ?? statusConfig.draft.classes"
                    >
                        {{ statusConfig[event.status]?.label ?? event.status }}
                    </span>
                    <Button
                        v-if="event.status === 'draft'"
                        size="sm"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs"
                        @click="toggleStatus('published')"
                    >
                        Publish
                    </Button>
                    <Button
                        v-if="event.status === 'published'"
                        size="sm"
                        variant="destructive"
                        class="rounded-xl text-xs"
                        @click="toggleStatus('closed')"
                    >
                        Tutup Event
                    </Button>
                </div>
            </div>

            <!-- Event Detail Card -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Main Info -->
                <div class="lg:col-span-2 space-y-6">
                    <Card class="rounded-2xl border-slate-100 dark:border-slate-800 shadow-xs overflow-hidden">
                        <!-- Poster -->
                        <div v-if="event.poster_path" class="w-full overflow-hidden bg-slate-100 dark:bg-slate-800">
                            <img :src="`/storage/${event.poster_path}`" :alt="event.title" class="w-full object-contain" />
                        </div>
                        <CardContent class="p-6">
                            <h2 class="text-lg font-black text-slate-800 dark:text-white mb-4">Deskripsi</h2>
                            <div class="prose prose-sm prose-slate dark:prose-invert max-w-none text-sm text-slate-600 dark:text-slate-400 leading-relaxed whitespace-pre-line">
                                {{ event.description }}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Side Info -->
                <div class="space-y-4">
                    <Card class="rounded-2xl border-slate-100 dark:border-slate-800 shadow-xs">
                        <CardContent class="p-5 space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sky-50 dark:bg-sky-950/30">
                                    <CalendarDays class="h-4 w-4 text-sky-600 dark:text-sky-400" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tanggal</p>
                                    <p class="text-sm font-bold text-slate-800 dark:text-white">{{ formatDate(event.event_date) }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-950/30">
                                    <Clock class="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Waktu</p>
                                    <p class="text-sm font-bold text-slate-800 dark:text-white">
                                        {{ event.event_time_start }} — {{ event.event_time_end }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-950/30">
                                    <MapPin class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Lokasi</p>
                                    <p class="text-sm font-bold text-slate-800 dark:text-white">{{ event.location }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-950/30">
                                    <AlertCircle class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Batas Pendaftaran</p>
                                    <p class="text-sm font-bold text-slate-800 dark:text-white">{{ formatDate(event.registration_deadline) }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-purple-50 dark:bg-purple-950/30">
                                    <Users class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Peserta</p>
                                    <p class="text-sm font-bold text-slate-800 dark:text-white">
                                        {{ event.registrations_count }}
                                        <span v-if="event.max_participants" class="text-slate-400 font-medium">/ {{ event.max_participants }}</span>
                                        <span v-else class="text-xs text-slate-400 font-medium ml-1">tanpa batas</span>
                                    </p>
                                </div>
                            </div>

                            <!-- Attendance Summary -->
                            <div class="flex items-start gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-teal-50 dark:bg-teal-950/30">
                                    <CheckCircle2 class="h-4 w-4 text-teal-600 dark:text-teal-400" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Kehadiran</p>
                                    <p class="text-sm font-bold text-slate-800 dark:text-white">
                                        {{ attendanceCount }}
                                        <span class="text-slate-400 font-medium">/ {{ event.registrations_count }}</span>
                                        <span v-if="event.registrations_count > 0" class="text-xs text-teal-600 dark:text-teal-400 font-semibold ml-1">
                                            ({{ Math.round((attendanceCount / event.registrations_count) * 100) }}%)
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Registrations Table -->
            <Card class="rounded-2xl border-slate-100 dark:border-slate-800 shadow-xs overflow-hidden">
                <CardHeader class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20 px-6 py-4">
                    <div class="flex items-center gap-2">
                        <UserCheck class="h-4 w-4 text-sky-600 dark:text-sky-400" />
                        <CardTitle class="text-sm font-black uppercase tracking-widest text-slate-400">
                            Daftar Peserta ({{ registrations.length }})
                        </CardTitle>
                    </div>
                </CardHeader>
                <CardContent class="p-0">
                    <div v-if="registrations.length > 0" class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-800">
                                    <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400">#</th>
                                    <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400">Nama</th>
                                    <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400">Email</th>
                                    <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400">Tanggal Daftar</th>
                                    <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400">Status</th>
                                    <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400">Kehadiran</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                                <tr
                                    v-for="(reg, idx) in registrations"
                                    :key="reg.id"
                                    class="transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/20"
                                >
                                    <td class="px-5 py-3 text-xs font-bold text-slate-400">{{ idx + 1 }}</td>
                                    <td class="px-5 py-3">
                                        <p class="text-sm font-bold text-slate-800 dark:text-white">{{ reg.user.name }}</p>
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                            <Mail class="h-3.5 w-3.5 text-slate-400" />
                                            {{ reg.user.email }}
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-xs text-slate-500 dark:text-slate-400">
                                        {{ formatDateTime(reg.registered_at) }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <span
                                            class="inline-flex items-center rounded-lg border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                                            :class="regStatusConfig[reg.status]?.classes ?? regStatusConfig.registered.classes"
                                        >
                                            {{ regStatusConfig[reg.status]?.label ?? reg.status }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <button
                                            @click="toggleAttendance(reg.id)"
                                            :disabled="loadingAttendance === reg.id"
                                            class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider transition-all duration-200 hover:shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                            :class="reg.attended_at
                                                ? 'bg-teal-50 text-teal-700 border-teal-200 hover:bg-teal-100 dark:bg-teal-950/30 dark:text-teal-400 dark:border-teal-800 dark:hover:bg-teal-950/50'
                                                : 'bg-slate-50 text-slate-500 border-slate-200 hover:bg-slate-100 dark:bg-slate-800/50 dark:text-slate-400 dark:border-slate-700 dark:hover:bg-slate-800'"
                                        >
                                            <CheckCircle2 v-if="reg.attended_at" class="h-3.5 w-3.5" />
                                            <XCircle v-else class="h-3.5 w-3.5" />
                                            {{ reg.attended_at ? 'Hadir' : 'Belum Hadir' }}
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="flex flex-col items-center justify-center py-12 text-center">
                        <Users class="h-10 w-10 text-slate-200 dark:text-slate-700 mb-3" />
                        <p class="text-sm font-semibold text-slate-400 dark:text-slate-500">Belum ada peserta terdaftar</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </TraceAdminLayout>
</template>
