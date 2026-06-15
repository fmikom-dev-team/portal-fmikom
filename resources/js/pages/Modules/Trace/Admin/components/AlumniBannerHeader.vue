<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { Search, XCircle } from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';


interface Props {
    totalAlumni?: string | number;
    syncActive?: boolean;
    filters?: {
        search?: string;
        status?: string;
        prodi?: string;
        angkatan?: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    totalAlumni: 0,
    syncActive: true,
    filters: () => ({
        search: '',
        status: 'Semua Status',
        prodi: 'Semua Prodi',
        angkatan: 'Semua Angkatan',
    }),
});

const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'Semua Status');
const prodiFilter = ref(props.filters.prodi || 'Semua Prodi');
const angkatanFilter = ref(props.filters.angkatan || 'Semua Angkatan');

const formattedTotalAlumni = computed(() => {
    return Number(props.totalAlumni).toLocaleString('id-ID');
});

const hasActiveFilters = computed(() => {
    return searchQuery.value ||
        (statusFilter.value && statusFilter.value !== 'Semua Status') ||
        (prodiFilter.value && prodiFilter.value !== 'Semua Prodi') ||
        (angkatanFilter.value && angkatanFilter.value !== 'Semua Angkatan');
});

const statuses = [
    'Semua Status',
    'Bekerja',
    'Belum Bekerja',
    'Wirausaha',
    'Lanjut Studi',
];

const prodis = ['Semua Prodi', 'Informatika', 'Sistem Informasi', 'Matematika'];

// Generate angkatan years from 2010 to current year
const currentYear = new Date().getFullYear();
const angkatanOptions = ['Semua Angkatan', ...Array.from({ length: currentYear - 2009 }, (_, i) => String(currentYear - i))];

const handleFilterChange = useDebounceFn(() => {
    router.get(
        '/admin/alumni',
        {
            search: searchQuery.value,
            status:
                statusFilter.value === 'Semua Status'
                    ? undefined
                    : statusFilter.value,
            prodi:
                prodiFilter.value === 'Semua Prodi'
                    ? undefined
                    : prodiFilter.value,
            angkatan:
                angkatanFilter.value === 'Semua Angkatan'
                    ? undefined
                    : angkatanFilter.value,
        },
        { preserveState: true, replace: true },
    );
}, 300);

watch([statusFilter, prodiFilter, angkatanFilter], () => {
    handleFilterChange();
});

function clearFilters() {
    searchQuery.value = '';
    statusFilter.value = 'Semua Status';
    prodiFilter.value = 'Semua Prodi';
    angkatanFilter.value = 'Semua Angkatan';
    handleFilterChange();
}
</script>

<template>
    <section
        class="relative flex flex-col gap-10 overflow-hidden rounded-3xl border border-border/40 bg-accent/40 p-8 shadow-sm transition-all md:p-10"
    >
        <div
            class="pointer-events-none absolute -top-24 -right-24 h-96 w-96 rounded-full bg-[#B6FF00]/10 blur-[100px]"
        ></div>
        <div
            class="pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-[#2563EB]/10 blur-[80px]"
        ></div>

        <div class="relative z-10 flex flex-col gap-1">
            <p
                class="text-xs font-bold tracking-[0.2em] text-muted-foreground/70 uppercase"
            >
                Total Alumni Terdaftar
            </p>
            <div class="flex items-baseline gap-4">
                <h2
                    class="text-5xl font-black tracking-tighter text-foreground md:text-7xl lg:text-8xl"
                >
                    {{ formattedTotalAlumni }}
                </h2>

                <div
                    v-if="syncActive"
                    class="flex items-center gap-2 rounded-full bg-green-500/10 px-3 py-1 text-[10px] font-bold tracking-wider text-green-600 uppercase dark:text-green-400"
                >
                    <span class="relative flex h-2 w-2">
                        <span
                            class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"
                        ></span>
                        <span
                            class="relative inline-flex h-2 w-2 rounded-full bg-green-500"
                        ></span>
                    </span>
                    Sinkronisasi real-time aktif
                </div>
            </div>
        </div>

        <div
            class="relative z-10 flex flex-col gap-4"
        >
            <div class="flex flex-col gap-3 md:flex-row md:items-center">
                <div class="relative w-full max-w-sm">
                    <Search
                        class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted-foreground/60"
                    />
                    <Input
                        v-model="searchQuery"
                        placeholder="Cari berdasarkan nama, NIM, atau instansi..."
                        class="h-11 rounded-2xl border-border/40 bg-background/50 pl-10 shadow-sm transition-all focus-visible:ring-primary/20"
                        @input="handleFilterChange"
                    />
                </div>

                <Button
                    v-if="hasActiveFilters"
                    variant="ghost"
                    size="sm"
                    class="h-11 rounded-2xl text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/20 font-semibold"
                    @click="clearFilters"
                >
                    <XCircle class="h-4 w-4 mr-1.5" />
                    Hapus Filter
                </Button>
            </div>

            <div class="flex flex-1 flex-wrap items-center gap-3">
                <!-- Status Filter -->
                <Select v-model="statusFilter">
                    <SelectTrigger
                        class="h-11 w-[160px] rounded-2xl border-border/40 bg-background/50 font-semibold shadow-sm focus:ring-primary/20"
                    >
                        <SelectValue placeholder="Pilih Status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="s in statuses" :key="s" :value="s">
                            {{ s }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <!-- Prodi Filter -->
                <Select v-model="prodiFilter">
                    <SelectTrigger
                        class="h-11 w-[180px] rounded-2xl border-border/40 bg-background/50 font-semibold shadow-sm focus:ring-primary/20"
                    >
                        <SelectValue placeholder="Pilih Prodi" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="p in prodis" :key="p" :value="p">
                            {{ p }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <!-- Angkatan Filter -->
                <Select v-model="angkatanFilter">
                    <SelectTrigger
                        class="h-11 w-[180px] rounded-2xl border-border/40 bg-background/50 font-semibold shadow-sm focus:ring-primary/20"
                    >
                        <SelectValue placeholder="Pilih Angkatan" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="a in angkatanOptions" :key="a" :value="a">
                            {{ a }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>
    </section>
</template>
