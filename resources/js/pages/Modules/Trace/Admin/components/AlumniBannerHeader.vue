<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { TFilterBar } from '@/components/Trace';


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

const formattedTotalAlumni = computed(() => {
    return Number(props.totalAlumni).toLocaleString('id-ID');
});

// Generate angkatan years from 2010 to current year
const currentYear = new Date().getFullYear();
const angkatanOptions = Array.from({ length: currentYear - 2009 }, (_, i) => String(currentYear - i));

const filterConfig = computed(() => [
    {
        key: 'status',
        label: 'Status',
        options: [
            { value: 'Bekerja', label: 'Bekerja' },
            { value: 'Belum Bekerja', label: 'Belum Bekerja' },
            { value: 'Wirausaha', label: 'Wirausaha' },
            { value: 'Lanjut Studi', label: 'Lanjut Studi' },
        ],
    },
    {
        key: 'prodi',
        label: 'Prodi',
        options: [
            { value: 'Informatika', label: 'Informatika' },
            { value: 'Sistem Informasi', label: 'Sistem Informasi' },
            { value: 'Matematika', label: 'Matematika' },
        ],
    },
    {
        key: 'angkatan',
        label: 'Angkatan',
        options: angkatanOptions.map(a => ({ value: a, label: a })),
    },
]);

const currentFilters = computed(() => {
    const f: Record<string, string> = {};
    if (props.filters.search) f.search = props.filters.search;
    if (props.filters.status && props.filters.status !== 'Semua Status') f.status = props.filters.status;
    if (props.filters.prodi && props.filters.prodi !== 'Semua Prodi') f.prodi = props.filters.prodi;
    if (props.filters.angkatan && props.filters.angkatan !== 'Semua Angkatan') f.angkatan = props.filters.angkatan;
    return f;
});

function onFilterChange(filters: Record<string, string>) {
    router.get('/trace/admin/alumni', {
        search: filters.search || undefined,
        status: filters.status || undefined,
        prodi: filters.prodi || undefined,
        angkatan: filters.angkatan || undefined,
    }, { preserveState: true, replace: true });
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

        <div class="relative z-10">
            <TFilterBar
                search-placeholder="Cari berdasarkan nama, NIM, atau instansi..."
                :filters="filterConfig"
                :model-value="currentFilters"
                class="border-none shadow-none bg-transparent !p-0 [&>div]:p-0"
                @filter-change="onFilterChange"
            />
        </div>
    </section>
</template>
