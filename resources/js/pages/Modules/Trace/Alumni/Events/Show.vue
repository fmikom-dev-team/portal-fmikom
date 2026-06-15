<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import TraceAlumniLayout from '@/layouts/TraceAlumniLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    ArrowLeft,
    CalendarDays,
    Clock,
    MapPin,
    Users,
    CheckCircle2,
    XCircle,
    Image,
    Loader2,
    AlertCircle,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

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
    registrations_count: number;
}

const props = defineProps<{
    event: EventDetail;
    isRegistered: boolean;
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Dashboard', href: '/trace' },
    { title: 'Events', href: '/trace/events' },
    { title: props.event.title, href: `/trace/events/${props.event.id}` },
]);

const isProcessing = ref(false);

const formatDateFull = (dateStr: string) => {
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

const isPastDeadline = computed(() => {
    try {
        return new Date(props.event.registration_deadline) < new Date();
    } catch {
        return false;
    }
});

const isFull = computed(() => {
    return props.event.max_participants !== null && props.event.registrations_count >= props.event.max_participants;
});

const canRegister = computed(() => {
    return !props.isRegistered && !isPastDeadline.value && !isFull.value;
});

const register = () => {
    isProcessing.value = true;
    router.post(`/trace/events/${props.event.id}/register`, {}, {
        preserveScroll: true,
        onFinish: () => { isProcessing.value = false; },
    });
};

const cancelRegistration = () => {
    if (!confirm('Apakah Anda yakin ingin membatalkan pendaftaran?')) return;
    isProcessing.value = true;
    router.post(`/trace/events/${props.event.id}/cancel`, {}, {
        preserveScroll: true,
        onFinish: () => { isProcessing.value = false; },
    });
};
</script>

<template>
    <TraceAlumniLayout title="Detail Event" role-name="Alumni" :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-4xl space-y-6">
            <!-- Back Button -->
            <div class="flex items-center gap-3">
                <Button as-child variant="ghost" size="icon-sm" class="rounded-xl text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/30">
                    <Link href="/trace/events">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">Kembali ke daftar event</span>
            </div>

            <!-- Poster -->
            <div class="rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-800">
                <img
                    v-if="event.poster_path"
                    :src="`/storage/${event.poster_path}`"
                    :alt="event.title"
                    class="w-full object-contain"
                />
                <div v-else class="flex aspect-[2/3] items-center justify-center">
                    <Image class="h-12 w-12 text-slate-300 dark:text-slate-600" />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-4">
                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-slate-800 dark:text-white mb-2">
                            {{ event.title }}
                        </h1>
                        <!-- Meta inline -->
                        <div class="flex flex-wrap gap-4 mb-4">
                            <div class="flex items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400">
                                <CalendarDays class="h-4 w-4 text-emerald-500" />
                                {{ formatDateFull(event.event_date) }}
                            </div>
                            <div class="flex items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400">
                                <Clock class="h-4 w-4 text-emerald-500" />
                                {{ event.event_time_start }} — {{ event.event_time_end }}
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <Card class="rounded-2xl border-slate-100 dark:border-slate-800 shadow-xs">
                        <CardContent class="p-5">
                            <h3 class="text-sm font-black uppercase tracking-wider text-slate-400 mb-3">Deskripsi</h3>
                            <div class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed whitespace-pre-line">
                                {{ event.description }}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-4">
                    <!-- Info Card -->
                    <Card class="rounded-2xl border-slate-100 dark:border-slate-800 shadow-xs">
                        <CardContent class="p-5 space-y-4">
                            <div class="flex items-start gap-3">
                                <MapPin class="h-4 w-4 text-emerald-500 shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Lokasi</p>
                                    <p class="text-sm font-bold text-slate-800 dark:text-white">{{ event.location }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <Users class="h-4 w-4 text-emerald-500 shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Peserta</p>
                                    <p class="text-sm font-bold text-slate-800 dark:text-white">
                                        {{ event.registrations_count }}
                                        <span v-if="event.max_participants" class="text-slate-400 font-medium">/ {{ event.max_participants }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <AlertCircle class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Batas Pendaftaran</p>
                                    <p class="text-sm font-bold" :class="isPastDeadline ? 'text-red-500' : 'text-slate-800 dark:text-white'">
                                        {{ formatDateFull(event.registration_deadline) }}
                                    </p>
                                    <p v-if="isPastDeadline" class="text-[10px] text-red-500 font-semibold mt-0.5">Pendaftaran telah ditutup</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Registration Action -->
                    <Card class="rounded-2xl border-slate-100 dark:border-slate-800 shadow-xs">
                        <CardContent class="p-5">
                            <!-- Already registered -->
                            <div v-if="isRegistered" class="space-y-3">
                                <div class="flex items-center gap-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 p-3">
                                    <CheckCircle2 class="h-5 w-5 text-emerald-600 dark:text-emerald-400 shrink-0" />
                                    <div>
                                        <p class="text-sm font-bold text-emerald-700 dark:text-emerald-400">Sudah Terdaftar</p>
                                        <p class="text-[10px] text-emerald-600/80 dark:text-emerald-400/70">Anda telah mendaftar event ini</p>
                                    </div>
                                </div>
                                <Button
                                    variant="outline"
                                    class="w-full rounded-xl border-red-200 text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-950/30"
                                    :disabled="isProcessing"
                                    @click="cancelRegistration"
                                >
                                    <Loader2 v-if="isProcessing" class="h-4 w-4 animate-spin" />
                                    <XCircle v-else class="h-4 w-4" />
                                    {{ isProcessing ? 'Membatalkan...' : 'Batalkan Pendaftaran' }}
                                </Button>
                            </div>

                            <!-- Can register -->
                            <div v-else-if="canRegister">
                                <Button
                                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-sm"
                                    :disabled="isProcessing"
                                    @click="register"
                                >
                                    <Loader2 v-if="isProcessing" class="h-4 w-4 animate-spin" />
                                    <CheckCircle2 v-else class="h-4 w-4" />
                                    {{ isProcessing ? 'Mendaftar...' : 'Daftar Sekarang' }}
                                </Button>
                            </div>

                            <!-- Cannot register -->
                            <div v-else class="text-center py-2">
                                <div class="flex items-center gap-2 rounded-xl bg-slate-100 dark:bg-slate-800 p-3 justify-center">
                                    <XCircle class="h-4 w-4 text-slate-400 shrink-0" />
                                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                        <template v-if="isPastDeadline">Pendaftaran telah ditutup</template>
                                        <template v-else-if="isFull">Kuota peserta sudah penuh</template>
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </TraceAlumniLayout>
</template>
