<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import TraceAlumniLayout from '@/layouts/TraceAlumniLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { TPageHeader, TStatusBadge, TEmptyState, TPagination } from '@/components/trace';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    CalendarDays,
    MapPin,
    Users,
    ArrowRight,
    Image,
    Ticket,
    Clock,
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
            <TPageHeader title="Event Alumni" description="Temukan event seru dan kembangkan jaringan Anda." :icon="CalendarDays">
                <template #actions>
                    <Button as-child variant="outline" class="rounded-xl border-[#0C447C]/20 text-[#0C447C] hover:bg-[#0C447C]/5 dark:border-[#85B7EB]/30 dark:text-[#85B7EB] dark:hover:bg-[#85B7EB]/10">
                        <Link href="/trace/events/my-events" class="inline-flex items-center gap-2">
                            <Ticket class="h-4 w-4" />
                            Event Saya
                        </Link>
                    </Button>
                </template>
            </TPageHeader>

            <!-- Search & Filter Bar -->
            <div class="rounded-2xl border border-slate-200/60 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-3">
                <!-- Search -->
                <div class="relative">
                    <Input
                        v-model="search"
                        placeholder="Cari event, lokasi..."
                        class="rounded-xl pl-4 pr-20 h-11"
                        @keyup.enter="applyFilters"
                    />
                    <button
                        class="absolute right-1.5 top-1/2 -translate-y-1/2 inline-flex h-8 items-center gap-1.5 rounded-lg bg-[#0C447C] px-3.5 text-xs font-semibold text-white transition-colors hover:bg-[#0C447C]/90 dark:bg-[#85B7EB] dark:text-slate-900"
                        @click="applyFilters"
                    >
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
                            ? 'bg-[#0C447C] text-white shadow-sm dark:bg-[#85B7EB] dark:text-slate-900'
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
                    class="group rounded-2xl border border-slate-200/60 dark:border-zinc-800 shadow-xs overflow-hidden transition-all hover:shadow-md hover:border-[#85B7EB]/40 dark:hover:border-[#85B7EB]/30"
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
                            <TStatusBadge
                                v-if="isPast(event)"
                                status="closed"
                                label="Selesai"
                                variant="solid"
                            />
                            <TStatusBadge
                                v-else-if="isToday(event)"
                                status="active"
                                label="Berlangsung"
                                variant="solid"
                                class="animate-pulse"
                            />
                            <TStatusBadge
                                v-else
                                status="registered"
                                label="Akan Datang"
                                variant="solid"
                            />
                        </div>
                    </div>

                    <CardContent class="p-5 space-y-3">
                        <!-- Title -->
                        <h3 class="text-base font-bold text-slate-800 dark:text-white line-clamp-2 group-hover:text-[#0C447C] dark:group-hover:text-[#85B7EB] transition-colors">
                            {{ event.title }}
                        </h3>

                        <!-- Description -->
                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2">
                            {{ truncate(event.description, 100) }}
                        </p>

                        <!-- Meta -->
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                <CalendarDays class="h-3.5 w-3.5 text-[#0C447C] dark:text-[#85B7EB] shrink-0" />
                                {{ formatDate(event.event_date) }}
                            </div>
                            <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                <MapPin class="h-3.5 w-3.5 text-[#0C447C] dark:text-[#85B7EB] shrink-0" />
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
                            <Button as-child size="sm" class="bg-[#0C447C] hover:bg-[#0C447C]/90 text-white rounded-lg text-xs h-8 dark:bg-[#85B7EB] dark:text-slate-900 dark:hover:bg-[#85B7EB]/90">
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
            <TEmptyState
                v-else
                :icon="CalendarDays"
                title="Belum ada event tersedia"
                description="Event baru akan muncul di sini saat dipublikasikan."
            />

            <!-- Pagination -->
            <TPagination v-if="events.last_page > 1" :links="events.links" />
        </div>
    </TraceAlumniLayout>
</template>
