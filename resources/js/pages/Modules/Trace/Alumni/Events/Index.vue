<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import TraceAlumniLayout from '@/layouts/TraceAlumniLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    CalendarDays,
    MapPin,
    Users,
    ArrowRight,
    CalendarCheck,
    Image,
    Ticket,
    Clock,
    CheckCircle2,
    Search,
} from 'lucide-vue-next';
import { ref } from 'vue';

interface Event {
    id: number;
    title: string;
    description: string;
    event_date: string;
    location: string;
    poster_path: string | null;
    status: 'published' | 'closed';
    registrations_count: number;
    max_participants: number | null;
}

interface PaginatedEvents {
    data: Event[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    events: PaginatedEvents;
    filters: { search: string | null; waktu: string | null };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/trace' },
    { title: 'Events', href: '/trace/events' },
];

const search = ref(props.filters.search ?? '');
const activeTab = ref(props.filters.waktu ?? '');

const tabs = [
    { value: '', label: 'Semua' },
    { value: 'upcoming', label: 'Akan Datang' },
    { value: 'ongoing', label: 'Berlangsung' },
    { value: 'past', label: 'Selesai' },
];

function applyFilters() {
    const params: Record<string, string> = {};
    if (search.value) params.search = search.value;
    if (activeTab.value) params.waktu = activeTab.value;
    router.get('/trace/events', params, { preserveState: true, replace: true });
}

function setTab(tab: string) {
    activeTab.value = tab;
    applyFilters();
}

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

const truncate = (text: string, len: number) => {
    if (!text) return '';
    return text.length > len ? text.substring(0, len) + '...' : text;
};

const isFull = (event: Event) => {
    return event.max_participants !== null && event.registrations_count >= event.max_participants;
};

const isPast = (event: Event) => {
    const eventDate = new Date(event.event_date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    eventDate.setHours(0, 0, 0, 0);
    return eventDate < today || event.status === 'closed';
};

const isToday = (event: Event) => {
    const eventDate = new Date(event.event_date);
    const today = new Date();
    return eventDate.toDateString() === today.toDateString() && event.status !== 'closed';
};
</script>

<template>
    <TraceAlumniLayout title="Events" role-name="Alumni" :breadcrumbs="breadcrumbs">
        <div class="mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 mb-1">
                        <CalendarCheck class="h-4 w-4" />
                        <span class="text-[10px] font-black uppercase tracking-widest">Event Alumni</span>
                    </div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-800 dark:text-white">Events</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        Temukan dan ikuti event menarik untuk alumni.
                    </p>
                </div>
                <Button as-child variant="outline" class="rounded-xl border-emerald-200 text-emerald-700 hover:bg-emerald-50 dark:border-emerald-800 dark:text-emerald-400 dark:hover:bg-emerald-950/30">
                    <Link href="/trace/events/my-events" class="inline-flex items-center gap-2">
                        <Ticket class="h-4 w-4" />
                        Event Saya
                    </Link>
                </Button>
            </div>

            <!-- Search & Filter Bar -->
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-3">
                <!-- Search -->
                <div class="relative">
                    <Search class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <Input
                        v-model="search"
                        placeholder="Cari event, lokasi..."
                        class="rounded-xl pl-10 pr-20 h-11"
                        @keyup.enter="applyFilters"
                    />
                    <button
                        class="absolute right-1.5 top-1/2 -translate-y-1/2 inline-flex h-8 items-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 text-xs font-semibold text-white transition-colors hover:bg-emerald-700"
                        @click="applyFilters"
                    >
                        <Search class="h-3.5 w-3.5" />
                        Cari
                    </button>
                </div>

                <!-- Filter tabs -->
                <div class="flex flex-wrap items-center gap-1.5">
                    <button
                        v-for="tab in tabs"
                        :key="tab.value"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold transition-colors"
                        :class="activeTab === tab.value
                            ? 'bg-emerald-600 text-white shadow-sm'
                            : 'bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700'"
                        @click="setTab(tab.value)"
                    >
                        {{ tab.label }}
                    </button>
                </div>
            </div>

            <!-- Event Cards Grid -->
            <div v-if="events.data.length > 0" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Card
                    v-for="event in events.data"
                    :key="event.id"
                    class="group rounded-2xl border-slate-100 dark:border-slate-800 shadow-xs overflow-hidden transition-all hover:shadow-md hover:border-emerald-200 dark:hover:border-emerald-800/50"
                >
                    <!-- Poster -->
                    <div class="relative aspect-[2/3] w-full overflow-hidden bg-slate-100 dark:bg-slate-800">
                        <img
                            v-if="event.poster_path"
                            :src="`/storage/${event.poster_path}`"
                            :alt="event.title"
                            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                            :class="{ 'opacity-60 grayscale-[30%]': isPast(event) }"
                        />
                        <div v-else class="flex h-full items-center justify-center">
                            <Image class="h-10 w-10 text-slate-300 dark:text-slate-600" />
                        </div>
                        <!-- Status badge -->
                        <div class="absolute top-3 left-3">
                            <span
                                v-if="isPast(event)"
                                class="inline-flex items-center gap-1 rounded-lg bg-slate-800/80 backdrop-blur px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-200"
                            >
                                <CheckCircle2 class="h-3 w-3" />
                                Selesai
                            </span>
                            <span
                                v-else-if="isToday(event)"
                                class="inline-flex items-center gap-1 rounded-lg bg-emerald-600/90 backdrop-blur px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white animate-pulse"
                            >
                                <Clock class="h-3 w-3" />
                                Berlangsung
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center gap-1 rounded-lg bg-sky-600/90 backdrop-blur px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white"
                            >
                                <CalendarDays class="h-3 w-3" />
                                Akan Datang
                            </span>
                        </div>
                    </div>

                    <CardContent class="p-5 space-y-3">
                        <!-- Title -->
                        <h3 class="text-base font-bold text-slate-800 dark:text-white line-clamp-2 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">
                            {{ event.title }}
                        </h3>

                        <!-- Description -->
                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2">
                            {{ truncate(event.description, 100) }}
                        </p>

                        <!-- Meta -->
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                <CalendarDays class="h-3.5 w-3.5 text-emerald-500 dark:text-emerald-400 shrink-0" />
                                {{ formatDate(event.event_date) }}
                            </div>
                            <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                <MapPin class="h-3.5 w-3.5 text-emerald-500 dark:text-emerald-400 shrink-0" />
                                <span class="truncate">{{ event.location }}</span>
                            </div>
                        </div>

                        <!-- Footer: Capacity + CTA -->
                        <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800">
                            <div class="flex items-center gap-1.5">
                                <Users class="h-3.5 w-3.5 text-slate-400" />
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-300">
                                    {{ event.registrations_count }}
                                </span>
                                <span v-if="event.max_participants" class="text-[10px] text-slate-400">
                                    / {{ event.max_participants }}
                                </span>
                                <span
                                    v-if="isFull(event)"
                                    class="text-[9px] font-bold uppercase tracking-wider text-red-500 ml-1"
                                >Penuh</span>
                            </div>
                            <Button as-child size="sm" class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs h-8">
                                <Link :href="`/trace/events/${event.id}`" class="inline-flex items-center gap-1">
                                    Lihat Detail
                                    <ArrowRight class="h-3 w-3" />
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <div v-else class="flex flex-col items-center justify-center py-16 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 mb-4">
                    <CalendarCheck class="h-8 w-8 text-emerald-300 dark:text-emerald-700" />
                </div>
                <p class="text-sm font-bold text-slate-500 dark:text-slate-400 mb-1">Belum ada event tersedia</p>
                <p class="text-xs text-slate-400 dark:text-slate-500">Event baru akan muncul di sini saat dipublikasikan.</p>
            </div>

            <!-- Pagination -->
            <div v-if="events.last_page > 1" class="flex items-center justify-between">
                <p class="text-xs text-slate-400 dark:text-slate-500">
                    Halaman {{ events.current_page }} dari {{ events.last_page }}
                </p>
                <nav class="flex items-center gap-1">
                    <template v-for="(link, idx) in events.links" :key="idx">
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
