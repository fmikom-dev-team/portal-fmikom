<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import TraceAdminLayout from '@/layouts/TraceAdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { TPageHeader, TDataTable, TStatusBadge, TEmptyState } from '@/components/Trace';
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
    { title: 'Events', href: '/trace/admin/events' },
    { title: props.event.title, href: `/trace/admin/events/${props.event.id}` },
]);



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
    router.put(`/trace/admin/events/${props.event.id}/status`, { status: newStatus }, {
        preserveScroll: true,
        onError: () => toast.error('Gagal memperbarui status event.'),
    });
};

const loadingAttendance = ref<number | null>(null);

const toggleAttendance = (registrationId: number) => {
    loadingAttendance.value = registrationId;
    router.post(`/trace/admin/events/${props.event.id}/registrations/${registrationId}/attendance`, {}, {
        preserveScroll: true,
        onError: () => toast.error('Gagal memperbarui kehadiran.'),
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
                    <Button as-child variant="ghost" size="icon-sm" class="rounded-xl text-slate-400 hover:text-[#0C447C] hover:bg-[#85B7EB]/10 dark:hover:bg-[#85B7EB]/10">
                        <Link href="/trace/admin/events">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <TPageHeader
                        :title="event.title"
                        description="Informasi lengkap dan data peserta"
                        :icon="CalendarCheck"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <TStatusBadge :status="event.status" size="md" />
                    <Button
                        v-if="event.status === 'draft'"
                        size="sm"
                        class="bg-[#0C447C] hover:bg-[#0C447C]/90 text-white rounded-xl text-xs"
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
                    <div class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-zinc-900 shadow-xs overflow-hidden">
                        <!-- Poster -->
                        <div v-if="event.poster_path" class="w-full overflow-hidden bg-slate-100 dark:bg-slate-800">
                            <img :src="`/storage/${event.poster_path}`" :alt="event.title" class="w-full object-contain" />
                        </div>
                        <div class="p-6">
                            <h2 class="text-lg font-black text-slate-800 dark:text-white mb-4">Deskripsi</h2>
                            <div class="prose prose-sm prose-slate dark:prose-invert max-w-none text-sm text-slate-600 dark:text-slate-400 leading-relaxed whitespace-pre-line">
                                {{ event.description }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Side Info -->
                <div class="space-y-4">
                    <div class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-zinc-900 shadow-xs">
                        <div class="p-5 space-y-4">
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
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#0C447C]/10 dark:bg-[#0C447C]/20">
                                    <Clock class="h-4 w-4 text-[#0C447C] dark:text-[#85B7EB]" />
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
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#0C447C]/10 dark:bg-[#0C447C]/20">
                                    <Users class="h-4 w-4 text-[#0C447C] dark:text-[#85B7EB]" />
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
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#0C447C]/10 dark:bg-[#0C447C]/20">
                                    <CheckCircle2 class="h-4 w-4 text-[#0C447C] dark:text-[#85B7EB]" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Kehadiran</p>
                                    <p class="text-sm font-bold text-slate-800 dark:text-white">
                                        {{ attendanceCount }}
                                        <span class="text-slate-400 font-medium">/ {{ event.registrations_count }}</span>
                                        <span v-if="event.registrations_count > 0" class="text-xs text-[#0C447C] dark:text-[#85B7EB] font-semibold ml-1">
                                            ({{ Math.round((attendanceCount / event.registrations_count) * 100) }}%)
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Registrations Table -->
            <TDataTable
                :columns="[
                    { key: 'index', label: '#' },
                    { key: 'name', label: 'Nama' },
                    { key: 'email', label: 'Email' },
                    { key: 'registered_at', label: 'Tanggal Daftar' },
                    { key: 'status', label: 'Status' },
                    { key: 'attendance', label: 'Kehadiran' },
                ]"
                :data="registrations"
                row-key="id"
                compact
            >
                <template #cell-index="{ row }">
                    <span class="text-xs font-bold text-slate-400">{{ registrations.indexOf(row) + 1 }}</span>
                </template>
                <template #cell-name="{ row }">
                    <p class="text-sm font-bold text-slate-800 dark:text-white">{{ row.user.name }}</p>
                </template>
                <template #cell-email="{ row }">
                    <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <Mail class="h-3.5 w-3.5 text-slate-400" />
                        {{ row.user.email }}
                    </div>
                </template>
                <template #cell-registered_at="{ row }">
                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ formatDateTime(row.registered_at) }}</span>
                </template>
                <template #cell-status="{ row }">
                    <TStatusBadge :status="row.status" />
                </template>
                <template #cell-attendance="{ row }">
                    <button
                        @click="toggleAttendance(row.id)"
                        :disabled="loadingAttendance === row.id"
                        class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider transition-all duration-200 hover:shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="row.attended_at
                            ? 'bg-[#0C447C]/10 text-[#0C447C] border-[#85B7EB] hover:bg-[#85B7EB]/20 dark:bg-[#0C447C]/20 dark:text-[#85B7EB] dark:border-[#0C447C] dark:hover:bg-[#0C447C]/30'
                            : 'bg-slate-50 text-slate-500 border-slate-200 hover:bg-slate-100 dark:bg-slate-800/50 dark:text-slate-400 dark:border-slate-700 dark:hover:bg-slate-800'"
                    >
                        <CheckCircle2 v-if="row.attended_at" class="h-3.5 w-3.5" />
                        <XCircle v-else class="h-3.5 w-3.5" />
                        {{ row.attended_at ? 'Hadir' : 'Belum Hadir' }}
                    </button>
                </template>
                <template #empty>
                    <TEmptyState
                        :icon="Users"
                        title="Belum ada peserta terdaftar"
                        description="Peserta yang mendaftar untuk event ini akan muncul di sini."
                    />
                </template>
            </TDataTable>
        </div>
    </TraceAdminLayout>
</template>
