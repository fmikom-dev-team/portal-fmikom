<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    CircleCheckBig,
    Save,
    Search,
    Zap,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import WimsAdminLayout from '@/layouts/Modules/Wims/Admin/Layout.vue';
import wimsRoutes from '@/routes/wims';

defineOptions({
    layout: WimsAdminLayout,
});

type Filters = {
    status?: string;
    search?: string;
    period?: string;
    pendaftaran?: number | null;
};

type Summary = {
    all?: number;
    approved_or_active?: number;
    completed?: number;
    assigned_company?: number;
    assigned_dosen?: number;
    available_companies?: number;
    fully_assigned?: number;
};

type OptionItem = {
    id?: number;
    value?: string;
    label: string;
};

type PlacementItem = {
    id: number;
    company_id?: number | null;
    student?: {
        id?: number | null;
        name?: string | null;
        email?: string | null;
        identity?: string | null;
    } | null;
    company?: {
        id?: number | null;
        name?: string | null;
    } | null;
    dosen_pembimbing_id?: number | null;
    tanggal_mulai?: string | null;
    tanggal_selesai?: string | null;
    status?: string | null;
    can_assign?: boolean;
    can_activate?: boolean;
    can_complete?: boolean;
    can_complete_now?: boolean;
};

type PaginationLink = {
    url?: string | null;
    label: string;
    active: boolean;
};

type PlacementPagination = {
    data: PlacementItem[];
    total?: number;
    from?: number | null;
    to?: number | null;
    links: PaginationLink[];
};

const props = defineProps<{
    filters: Filters;
    summary: Summary;
    placements: PlacementPagination;
    batchActions: {
        eligible_on_current_page?: number;
        eligible_with_current_filters?: number;
    };
    options: {
        companies: OptionItem[];
        dosen: OptionItem[];
        periods: OptionItem[];
    };
}>();

const status = ref(props.filters.status || 'all');
const search = ref(props.filters.search || '');
const period = ref(props.filters.period || '');
const registrationFocus = ref<number | null>(props.filters.pendaftaran ?? null);
const processingId = ref<number | null>(null);
const bulkProcessing = ref<'selected' | 'filtered' | null>(null);
const selectedCompletionIds = ref<number[]>([]);
const assignmentForms = reactive<
    Record<number, { perusahaan_id: string; dosen_pembimbing_id: string }>
>({});
const savedAssignments = reactive<
    Record<number, { perusahaan_id: string; dosen_pembimbing_id: string }>
>({});
const editingRows = reactive<Record<number, boolean>>({});

const hydrateAssignmentForms = (items: PlacementItem[]) => {
    items.forEach((item) => {
        const next = {
            perusahaan_id: item.company_id ? String(item.company_id) : '',
            dosen_pembimbing_id: item.dosen_pembimbing_id
                ? String(item.dosen_pembimbing_id)
                : '',
        };

        assignmentForms[item.id] = { ...next };
        savedAssignments[item.id] = { ...next };
        editingRows[item.id] = !(
            next.perusahaan_id || next.dosen_pembimbing_id
        );
    });
};

hydrateAssignmentForms(props.placements.data);

watch(
    () => props.placements.data,
    (items) => {
        hydrateAssignmentForms(items);
        selectedCompletionIds.value = selectedCompletionIds.value.filter((id) =>
            items.some((item) => item.id === id && item.can_complete_now),
        );
    },
    { deep: true },
);

watch(
    () => props.filters,
    (filters) => {
        status.value = filters.status || 'all';
        search.value = filters.search || '';
        period.value = filters.period || '';
    },
    { deep: true },
);

const eligibleCompletionItems = computed(() =>
    props.placements.data.filter((item) => item.can_complete_now),
);

const allEligibleOnPageSelected = computed(() => {
    const eligibleIds = eligibleCompletionItems.value.map((item) => item.id);

    return eligibleIds.length > 0 && eligibleIds.every((id) => selectedCompletionIds.value.includes(id));
});

const applyFilters = () => {
    router.get(
        '/wims/admin/penempatan',
        {
            status: status.value,
            search: search.value || undefined,
            period: period.value || undefined,
            pendaftaran: registrationFocus.value || undefined,
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
};

const resetFilters = () => {
    status.value = 'all';
    search.value = '';
    period.value = '';
    registrationFocus.value = null;
    applyFilters();
};

const toggleSelectedCompletion = (id: number, checked: boolean) => {
    if (checked) {
        if (!selectedCompletionIds.value.includes(id)) {
            selectedCompletionIds.value = [...selectedCompletionIds.value, id];
        }

        return;
    }

    selectedCompletionIds.value = selectedCompletionIds.value.filter((value) => value !== id);
};

const toggleSelectAllEligibleOnPage = (checked: boolean) => {
    const eligibleIds = eligibleCompletionItems.value.map((item) => item.id);

    if (checked) {
        selectedCompletionIds.value = Array.from(new Set([...selectedCompletionIds.value, ...eligibleIds]));

        return;
    }

    selectedCompletionIds.value = selectedCompletionIds.value.filter((id) => !eligibleIds.includes(id));
};

const statusLabel = (value?: string | null) => {
    if (value === 'approved') return 'Disetujui';
    if (value === 'aktif') return 'Aktif';
    if (value === 'selesai') return 'Selesai';
    if (value === 'revisi') return 'Revisi';
    if (value === 'rejected') return 'Ditolak';
    return 'Menunggu';
};

const statusClass = (value?: string | null) => {
    if (value === 'approved')
        return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    if (value === 'aktif') return 'border-sky-200 bg-sky-50 text-sky-700';
    if (value === 'selesai')
        return 'border-violet-200 bg-violet-50 text-violet-700';
    if (value === 'revisi')
        return 'border-amber-200 bg-amber-50 text-amber-700';
    if (value === 'rejected') return 'border-rose-200 bg-rose-50 text-rose-700';
    return 'border-blue-200 bg-blue-50 text-[#2563EB]';
};

const hasSavedAssignment = (item: PlacementItem) => {
    const saved = savedAssignments[item.id];

    return Boolean(saved?.perusahaan_id && saved?.dosen_pembimbing_id);
};

const hasCompleteAssignment = (item: PlacementItem) => {
    const current = assignmentForms[item.id];

    return Boolean(current?.perusahaan_id && current?.dosen_pembimbing_id);
};

const isSavedReadonlyPlacement = (item: PlacementItem) => {
    return Boolean(
        item.can_assign &&
            item.status !== 'aktif' &&
            item.status !== 'selesai' &&
            hasSavedAssignment(item) &&
            !editingRows[item.id],
    );
};

const isDirty = (item: PlacementItem) => {
    const current = assignmentForms[item.id];
    const saved = savedAssignments[item.id];

    if (!current || !saved) {
        return false;
    }

    return (
        current.perusahaan_id !== saved.perusahaan_id ||
        current.dosen_pembimbing_id !== saved.dosen_pembimbing_id
    );
};

const rowStatusLabel = (item: PlacementItem) => {
    if (item.status === 'aktif') {
        return 'Aktif';
    }

    if (item.status === 'selesai') {
        return 'PKL telah diselesaikan';
    }

    if (!item.can_assign) {
        return 'Menunggu persetujuan';
    }

    if (editingRows[item.id]) {
        return isDirty(item) ? 'Perubahan belum disimpan' : 'Sedang diedit';
    }

    return hasSavedAssignment(item)
        ? 'Penempatan tersimpan'
        : 'Lengkapi perusahaan dan dosen';
};

const rowStatusClass = (item: PlacementItem) => {
    if (item.status === 'aktif') {
        return 'bg-sky-50 text-sky-700 ring-1 ring-sky-200';
    }

    if (item.status === 'selesai') {
        return 'bg-violet-50 text-violet-700 ring-1 ring-violet-200';
    }

    if (!item.can_assign) {
        return 'bg-amber-50 text-amber-700 ring-1 ring-amber-200';
    }

    if (editingRows[item.id]) {
        return isDirty(item)
            ? 'bg-blue-50 text-[#2563EB] ring-1 ring-blue-200'
            : 'bg-slate-100 text-slate-600 ring-1 ring-slate-200';
    }

    return hasSavedAssignment(item)
        ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'
        : 'bg-white text-slate-500 ring-1 ring-slate-200';
};

const startEdit = (item: PlacementItem) => {
    if (!item.can_assign) {
        return;
    }

    editingRows[item.id] = true;
};

const cancelEdit = (item: PlacementItem) => {
    assignmentForms[item.id] = { ...savedAssignments[item.id] };
    editingRows[item.id] = !hasSavedAssignment(item);
};

const savePlacement = (item: PlacementItem) => {
    if (!item.can_assign || !editingRows[item.id]) {
        return;
    }

    processingId.value = item.id;

    router.put(
        wimsRoutes.admin.placements.update(item.id).url,
        {
            perusahaan_id: assignmentForms[item.id]?.perusahaan_id || null,
            dosen_pembimbing_id:
                assignmentForms[item.id]?.dosen_pembimbing_id || null,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                savedAssignments[item.id] = { ...assignmentForms[item.id] };
                editingRows[item.id] = false;
            },
            onFinish: () => {
                processingId.value = null;
            },
        },
    );
};

const activatePlacement = (item: PlacementItem) => {
    processingId.value = item.id;

    router.post(
        wimsRoutes.admin.placements.activate(item.id).url,
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                processingId.value = null;
            },
        },
    );
};

const completePlacement = (item: PlacementItem) => {
    if (!item.can_complete_now) {
        return;
    }

    processingId.value = item.id;

    router.post(
        wimsRoutes.admin.placements.complete(item.id).url,
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                processingId.value = null;
            },
        },
    );
};

const completeSelectedPlacements = () => {
    if (!selectedCompletionIds.value.length) {
        return;
    }

    if (
        !window.confirm(
            `Tandai ${selectedCompletionIds.value.length} mahasiswa terpilih sebagai selesai PKL?`,
        )
    ) {
        return;
    }

    bulkProcessing.value = 'selected';

    router.post(
        '/wims/admin/penempatan/complete-selected',
        {
            ids: selectedCompletionIds.value,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                selectedCompletionIds.value = [];
            },
            onFinish: () => {
                bulkProcessing.value = null;
            },
        },
    );
};

const completeFilteredPlacements = () => {
    const totalEligible = props.batchActions.eligible_with_current_filters ?? 0;

    if (!totalEligible) {
        return;
    }

    if (
        !window.confirm(
            `Tandai ${totalEligible} mahasiswa dari hasil filter saat ini sebagai selesai PKL?`,
        )
    ) {
        return;
    }

    bulkProcessing.value = 'filtered';

    router.post(
        '/wims/admin/penempatan/complete-filtered',
        {
            status: status.value,
            search: search.value || undefined,
            period: period.value || undefined,
            pendaftaran: registrationFocus.value || undefined,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                selectedCompletionIds.value = [];
            },
            onFinish: () => {
                bulkProcessing.value = null;
            },
        },
    );
};

const resolveDosenLabel = (value?: string | null) => {
    if (!value) {
        return 'Belum dipilih';
    }

    return (
        props.options.dosen.find((option) => String(option.id) === value)?.label ||
        'Belum dipilih'
    );
};

const resolveCompanyLabel = (value?: string | null) => {
    if (!value) {
        return 'Belum dipilih';
    }

    return (
        props.options.companies.find((option) => String(option.id) === value)
            ?.label || 'Belum dipilih'
    );
};

const studentInitial = (name?: string | null) => {
    const words = name?.trim().split(/\s+/).filter(Boolean).slice(0, 2) ?? [];

    if (!words.length) {
        return 'M';
    }

    return words.map((word) => word.charAt(0).toUpperCase()).join('');
};
</script>

<template>
    <Head title="Penempatan & Pembimbing Admin" />

    <div
        class="mx-auto w-full max-w-[1320px] space-y-5 px-4 py-4 sm:px-6 sm:py-6 lg:space-y-6 lg:px-8 lg:py-8"
    >
        <section>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div
                    class="rounded-xl border border-zinc-200 bg-white px-5 py-4 shadow-none"
                >
                    <p class="text-xs font-bold text-slate-500">Siap Diproses</p>
                    <p class="mt-2 text-[22px] font-bold tracking-tight text-slate-950 sm:text-[24px]">
                        {{ summary.approved_or_active ?? 0 }}
                    </p>
                </div>
                <div
                    class="rounded-xl border border-zinc-200 bg-white px-5 py-4 shadow-none"
                >
                    <p class="text-xs font-bold text-slate-500">Perusahaan Terisi</p>
                    <p class="mt-2 text-[22px] font-bold tracking-tight text-blue-600 sm:text-[24px]">
                        {{ summary.assigned_company ?? 0 }}
                    </p>
                </div>
                <div
                    class="rounded-xl border border-zinc-200 bg-white px-5 py-4 shadow-none"
                >
                    <p class="text-xs font-bold text-slate-500">Dosen Terisi</p>
                    <p class="mt-2 text-[22px] font-bold tracking-tight text-emerald-600 sm:text-[24px]">
                        {{ summary.assigned_dosen ?? 0 }}
                    </p>
                </div>
                <div
                    class="rounded-xl border border-zinc-200 bg-white px-5 py-4 shadow-none"
                >
                    <p class="text-xs font-bold text-slate-500">Penempatan Lengkap</p>
                    <p class="mt-2 text-[22px] font-bold tracking-tight text-amber-600 sm:text-[24px]">
                        {{ summary.fully_assigned ?? 0 }}
                    </p>
                </div>
                <div
                    class="rounded-xl border border-zinc-200 bg-white px-5 py-4 shadow-none sm:col-span-2 xl:col-span-4"
                >
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold text-slate-500">Arsip PKL Selesai</p>
                            <p class="mt-2 text-[22px] font-bold tracking-tight text-violet-700 sm:text-[24px]">
                                {{ summary.completed ?? 0 }}
                            </p>
                        </div>
                        <p class="max-w-xl text-right text-xs leading-5 text-slate-500">
                            Data PKL yang sudah selesai tetap disimpan agar riwayat penempatan, logbook, absensi, dan penilaian dosen/mitra tetap bisa ditelusuri.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <Card class="rounded-xl border border-zinc-200 bg-white py-0 shadow-none">
            <CardHeader class="border-b border-zinc-200 px-5 py-4">
                <div>
                    <div>
                        <CardTitle class="text-[15px] font-bold text-slate-950"
                            >Daftar Penempatan</CardTitle
                        >
                        <CardDescription class="mt-1 text-sm leading-6 text-slate-600">
                            Kelola perusahaan, dosen pembimbing, status
                            penempatan, dan aktivasi mahasiswa.
                        </CardDescription>
                    </div>
                </div>
            </CardHeader>

            <CardContent class="space-y-5 px-5 py-5">
                <form
                    class="grid gap-4 md:grid-cols-2 xl:grid-cols-[minmax(0,1.3fr)_180px_220px_auto_auto]"
                    @submit.prevent="applyFilters"
                >
                    <div class="relative">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-zinc-400"
                        />
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Cari mahasiswa atau perusahaan..."
                            class="h-10 rounded-lg border-zinc-200 bg-zinc-50 pl-10"
                        />
                    </div>

                    <select
                        v-model="status"
                        class="h-10 rounded-lg border border-zinc-200 bg-zinc-50 px-3 text-sm text-zinc-900 transition outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/10"
                    >
                        <option value="all">Semua Status</option>
                        <option value="approved">Disetujui</option>
                        <option value="aktif">Aktif</option>
                        <option value="selesai">Selesai / Arsip</option>
                    </select>

                    <select
                        v-model="period"
                        class="h-10 rounded-lg border border-zinc-200 bg-zinc-50 px-3 text-sm text-zinc-900 transition outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/10"
                    >
                        <option value="">Semua Periode</option>
                        <option
                            v-for="option in props.options.periods"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>

                    <Button
                        type="submit"
                        class="h-10 rounded-xl bg-gradient-to-r from-blue-600 to-blue-500 px-4 text-sm font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:shadow-blue-500/30 active:scale-[0.98] dark:from-[#214FAF] dark:to-[#0F6FBE] dark:shadow-[0_14px_34px_-18px_rgba(8,15,30,0.84)] dark:hover:shadow-[0_18px_38px_-18px_rgba(8,15,30,0.92)]"
                    >
                        Terapkan
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        class="h-10 rounded-xl border border-wims-border/60 bg-wims-card px-4 text-sm font-semibold text-slate-700 dark:bg-slate-800/40 dark:text-slate-300 dark:hover:bg-slate-700/30"
                        @click="resetFilters"
                    >
                        Reset
                    </Button>
                </form>

                <div
                    class="flex items-center gap-2 rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-2.5 text-sm text-zinc-600"
                >
                    <span class="font-bold text-zinc-900">{{
                        summary.available_companies ?? 0
                    }}</span>
                    perusahaan aktif tersedia untuk penempatan saat ini.
                </div>

                <div class="rounded-xl border border-sky-200 bg-sky-50/80 px-4 py-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm font-bold text-sky-900">
                                    Selesai Massal PKL
                                </p>
                                <p class="mt-1 text-xs leading-5 text-sky-900/80">
                                    Filter dulu, lalu pilih yang siap selesai atau gunakan semua hasil filter.
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-2 text-xs font-medium text-sky-900/80">
                                <span class="rounded-full border border-sky-200 bg-white px-3 py-1.5">
                                    Di halaman ini: {{ props.batchActions.eligible_on_current_page ?? 0 }}
                                </span>
                                <span class="rounded-full border border-sky-200 bg-white px-3 py-1.5">
                                    Dalam filter: {{ props.batchActions.eligible_with_current_filters ?? 0 }}
                                </span>
                            </div>

                            <label class="inline-flex items-center gap-2 text-sm text-sky-900/90">
                                <input
                                    type="checkbox"
                                    class="size-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500"
                                    :checked="allEligibleOnPageSelected"
                                    :disabled="!eligibleCompletionItems.length"
                                    @change="toggleSelectAllEligibleOnPage(($event.target as HTMLInputElement).checked)"
                                />
                                Pilih semua di halaman ini
                            </label>
                        </div>

                        <div class="flex flex-col gap-2 sm:flex-row">
                            <Button
                                type="button"
                                variant="outline"
                                class="h-10 rounded-lg border-sky-200 bg-white px-4 text-sky-800 hover:bg-sky-100"
                                :disabled="!selectedCompletionIds.length || bulkProcessing !== null"
                                @click="completeSelectedPlacements"
                            >
                                Selesai Terpilih
                            </Button>
                            <Button
                                type="button"
                                class="h-10 rounded-lg bg-sky-700 px-4 text-white hover:bg-sky-800"
                                :disabled="!(props.batchActions.eligible_with_current_filters ?? 0) || bulkProcessing !== null"
                                @click="completeFilteredPlacements"
                            >
                                Selesai Semua Hasil Filter
                            </Button>
                        </div>
                    </div>
                </div>

                <div class="space-y-5">
                    <div
                        v-for="(item, index) in placements.data"
                        :key="item.id"
                        class="rounded-xl border px-4 py-4 shadow-none"
                        :class="
                            index % 2 === 0
                                ? 'border-zinc-200 bg-white'
                                : 'border-slate-200 bg-slate-50/55'
                        "
                    >
                        <div
                            class="grid gap-4 xl:items-start"
                            :class="
                                isSavedReadonlyPlacement(item)
                                    ? 'xl:grid-cols-[minmax(340px,420px)_minmax(0,1fr)]'
                                    : 'xl:grid-cols-[minmax(0,1fr)_minmax(0,280px)_minmax(0,240px)]'
                            "
                        >
                            <div
                                class="min-w-0"
                                :class="
                                    item.status === 'aktif' ||
                                    item.status === 'selesai' ||
                                    !item.can_assign
                                        ? 'xl:col-span-3'
                                        : ''
                                "
                            >
                                <div
                                    class="flex flex-col gap-5"
                                    :class="
                                        isSavedReadonlyPlacement(item)
                                            ? ''
                                            : 'xl:flex-row xl:items-start'
                                    "
                                >
                                    <label
                                        v-if="item.can_complete_now"
                                        class="mt-1 inline-flex shrink-0 items-center xl:pt-2"
                                    >
                                        <input
                                            type="checkbox"
                                            class="size-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500"
                                            :checked="selectedCompletionIds.includes(item.id)"
                                            @change="toggleSelectedCompletion(item.id, ($event.target as HTMLInputElement).checked)"
                                        />
                                    </label>
                                    <div class="flex min-w-0 flex-1 items-start gap-3 xl:min-w-[360px] xl:pr-6">
                                        <div
                                            class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-[13px] font-bold text-blue-600"
                                        >
                                            {{ studentInitial(item.student?.name) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-col gap-2">
                                                <div class="flex flex-wrap items-center gap-2">
                                                <p
                                                    class="truncate text-sm font-bold text-zinc-950"
                                                >
                                                    {{ item.student?.name || '-' }}
                                                </p>
                                                <Badge
                                                    variant="outline"
                                                    class="rounded-full px-2.5 py-0.5 text-[10px] font-bold shadow-none"
                                                    :class="
                                                        statusClass(item.status)
                                                    "
                                                >
                                                    {{ statusLabel(item.status) }}
                                                </Badge>
                                                <Badge
                                                    v-if="item.status === 'aktif'"
                                                    variant="outline"
                                                    class="rounded-full border-sky-200 bg-sky-50 px-2.5 py-0.5 text-[10px] font-bold text-sky-700 shadow-none"
                                                >
                                                    Terkunci
                                                </Badge>
                                                </div>
                                                <p
                                                    class="break-words text-xs text-slate-500"
                                                >
                                                    {{
                                                        item.student?.email || '-'
                                                    }}
                                                    <span class="mx-2 text-slate-300">•</span>
                                                    {{
                                                        item.student?.identity ||
                                                        'Identitas belum tersedia'
                                                    }}
                                                </p>
                                                <div class="space-y-1 text-xs text-zinc-900">
                                                    <p class="break-words">
                                                        <span class="uppercase tracking-[0.14em]">Mitra</span>
                                                        <span class="mx-1.5 text-slate-300">•</span>
                                                        {{ item.company?.name || 'Belum ada perusahaan' }}
                                                    </p>
                                                    <p class="break-words">
                                                        <span class="uppercase tracking-[0.14em]">Dosen Pembimbing</span>
                                                        <span class="mx-1.5 text-slate-300">•</span>
                                                        {{
                                                            resolveDosenLabel(
                                                                assignmentForms[item.id]?.dosen_pembimbing_id,
                                                            )
                                                        }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <template v-if="item.status === 'aktif' || item.status === 'selesai' || !item.can_assign">
                                <div class="xl:col-span-2" />
                            </template>
                            <template v-else>
                                <div :class="isSavedReadonlyPlacement(item) ? '' : 'xl:col-span-2'">
                                    <div class="rounded-xl bg-zinc-50/80 px-4 py-4">
                                        <template v-if="isSavedReadonlyPlacement(item)">
                                            <div class="flex flex-col gap-5 md:grid md:grid-cols-[minmax(0,1fr)_minmax(18rem,28rem)] md:items-start md:gap-6">
                                                <div class="space-y-2 md:pt-0.5">
                                                    <div>
                                                        <p class="text-sm font-bold leading-tight text-zinc-950">
                                                            Penempatan Tersimpan
                                                        </p>
                                                        <p class="mt-1 text-sm leading-tight text-slate-500">
                                                            Data penempatan sudah tersimpan.
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="flex flex-col gap-3 md:justify-self-end md:w-full">
                                                    <div class="ml-auto flex items-center gap-2 text-right">
                                                        <p class="text-sm font-bold leading-tight text-zinc-950">
                                                            Periode
                                                        </p>
                                                        <p class="text-sm font-bold leading-tight text-zinc-950">
                                                            {{ item.tanggal_mulai || '-' }} - {{ item.tanggal_selesai || '-' }}
                                                        </p>
                                                    </div>
                                                    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-end">
                                                        <Button
                                                            type="button"
                                                            variant="outline"
                                                            class="h-9 rounded-xl border border-wims-border/60 bg-wims-card px-4 text-sm font-semibold text-slate-700 dark:bg-slate-800/40 dark:text-slate-300 dark:hover:bg-slate-700/30"
                                                            :disabled="processingId === item.id"
                                                            @click="startEdit(item)"
                                                        >
                                                            Edit Penempatan
                                                        </Button>
                                                        <Button
                                                            v-if="item.can_activate"
                                                            type="button"
                                                            class="h-9 rounded-xl bg-emerald-600 px-4 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 transition-all hover:bg-emerald-700 hover:shadow-emerald-500/30 active:scale-[0.98]"
                                                            :disabled="processingId === item.id"
                                                            @click="activatePlacement(item)"
                                                        >
                                                            <Zap class="size-4" />
                                                            Aktifkan Magang
                                                        </Button>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                        <template v-else>
                                            <div class="mb-4">
                                                <p class="text-sm font-bold text-zinc-950">
                                                    Lengkapi Penempatan
                                                </p>
                                                <p class="mt-1 text-sm text-zinc-500">
                            Pilih perusahaan mitra dan dosen pembimbing sebelum mengaktifkan mahasiswa.
                                                </p>
                                            </div>

                                            <div class="grid gap-4 lg:grid-cols-2">
                                                <label class="flex flex-col gap-2">
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                                                        Pilih perusahaan
                                                        <span class="text-rose-500">*</span>
                                                    </span>
                                                    <select
                                                        v-model="
                                                            assignmentForms[item.id].perusahaan_id
                                                        "
                                                        required
                                                        :disabled="
                                                            !item.can_assign ||
                                                            processingId === item.id ||
                                                            !editingRows[item.id]
                                                        "
                                                        class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 transition outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/10 disabled:bg-zinc-100 disabled:text-zinc-500"
                                                    >
                                                        <option value="">Pilih perusahaan</option>
                                                        <option
                                                            v-for="company in options.companies"
                                                            :key="company.id"
                                                            :value="String(company.id)"
                                                        >
                                                            {{ company.label }}
                                                        </option>
                                                    </select>
                                                </label>

                                                <label class="flex flex-col gap-2">
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                                                        Pilih dosen pembimbing
                                                        <span class="text-rose-500">*</span>
                                                    </span>
                                                    <select
                                                        v-model="
                                                            assignmentForms[item.id]
                                                                .dosen_pembimbing_id
                                                        "
                                                        required
                                                        :disabled="
                                                            !item.can_assign ||
                                                            processingId === item.id ||
                                                            !editingRows[item.id]
                                                        "
                                                        class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 transition outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/10 disabled:bg-zinc-100 disabled:text-zinc-500"
                                                    >
                                                        <option value="">Pilih dosen</option>
                                                        <option
                                                            v-for="dosen in options.dosen"
                                                            :key="dosen.id"
                                                            :value="String(dosen.id)"
                                                        >
                                                            {{ dosen.label }}
                                                        </option>
                                                    </select>
                                                </label>
                                            </div>

                                            <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                                <p class="text-xs leading-5 text-slate-500">
                                                    Penempatan dapat disimpan setelah perusahaan dan dosen pembimbing dipilih.
                                                </p>
                                                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center">
                                                    <Button
                                                        type="button"
                                                        variant="outline"
                                                        class="h-9 rounded-xl border border-wims-border/60 bg-wims-card px-4 text-sm font-semibold text-slate-700 dark:bg-slate-800/40 dark:text-slate-300 dark:hover:bg-slate-700/30"
                                                        :disabled="processingId === item.id"
                                                        @click="cancelEdit(item)"
                                                    >
                                                        Batal
                                                    </Button>
                                                    <Button
                                                        type="button"
                                                        class="h-9 rounded-xl px-4 text-sm font-bold"
                                                        :class="
                                                            !item.can_assign ||
                                                            processingId === item.id ||
                                                            !isDirty(item) ||
                                                            !hasCompleteAssignment(item)
                                                                ? 'cursor-not-allowed bg-zinc-100 text-zinc-400 hover:bg-zinc-100'
                                                                : 'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg shadow-blue-500/20 transition-all hover:shadow-blue-500/30 active:scale-[0.98] dark:from-[#214FAF] dark:to-[#0F6FBE] dark:shadow-[0_14px_34px_-18px_rgba(8,15,30,0.84)] dark:hover:shadow-[0_18px_38px_-18px_rgba(8,15,30,0.92)]'
                                                        "
                                                        :disabled="
                                                            !item.can_assign ||
                                                            processingId === item.id ||
                                                            !isDirty(item) ||
                                                            !hasCompleteAssignment(item)
                                                        "
                                                        @click="savePlacement(item)"
                                                    >
                                                        <Save class="size-4" />
                                                        Simpan Penempatan
                                                    </Button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <div
                                v-if="!isSavedReadonlyPlacement(item)"
                                class="flex flex-col gap-2 xl:items-end"
                            >
                                <div
                                    v-if="
                                        item.status !== 'aktif' &&
                                        item.status !== 'selesai' &&
                                        !isSavedReadonlyPlacement(item) &&
                                        !(
                                            item.can_assign &&
                                            item.status !== 'selesai' &&
                                            editingRows[item.id]
                                        )
                                    "
                                    class="rounded-lg px-3 py-2 text-center text-xs font-bold xl:w-full"
                                    :class="rowStatusClass(item)"
                                >
                                    {{ rowStatusLabel(item) }}
                                </div>

                                <Button
                                    v-if="
                                        item.can_assign &&
                                        item.status !== 'aktif' &&
                                        item.status !== 'selesai' &&
                                        !isSavedReadonlyPlacement(item) &&
                                        !editingRows[item.id]
                                    "
                                    type="button"
                                    class="h-9 rounded-xl bg-gradient-to-r from-blue-600 to-blue-500 px-4 text-sm font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:shadow-blue-500/30 active:scale-[0.98] dark:from-[#214FAF] dark:to-[#0F6FBE] dark:shadow-[0_14px_34px_-18px_rgba(8,15,30,0.84)] dark:hover:shadow-[0_18px_38px_-18px_rgba(8,15,30,0.92)]"
                                    @click="startEdit(item)"
                                >
                                    Edit Penempatan
                                </Button>

                                <Button
                                    v-if="
                                        item.can_activate &&
                                        !editingRows[item.id]
                                    "
                                    type="button"
                                    class="h-9 w-full rounded-xl bg-emerald-600 px-4 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 transition-all hover:bg-emerald-700 hover:shadow-emerald-500/30 active:scale-[0.98]"
                                    :disabled="processingId === item.id"
                                    @click="activatePlacement(item)"
                                >
                                    <Zap class="size-4" />
                                    Aktifkan Magang
                                </Button>
                                <Button
                                    v-if="item.can_complete"
                                    type="button"
                                    variant="outline"
                                    class="hidden"
                                    :disabled="processingId === item.id || !item.can_complete_now"
                                    @click="completePlacement(item)"
                                >
                                    Selesaikan PKL
                                </Button>
                            </div>
                        </div>

                        <div
                            v-if="item.can_complete"
                            class="mt-5 flex flex-col gap-4 rounded-xl border border-sky-200/70 bg-sky-50/50 px-4 py-4 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-sky-800">
                                    Penempatan aktif
                                </p>
                                <p class="mt-1 text-xs leading-5 text-sky-800/80">
                                    Perusahaan, dosen pembimbing, dan periode sudah final.
                                </p>
                            </div>
                            <div class="flex shrink-0 flex-col items-start gap-1 sm:items-end">
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="h-9 rounded-lg border-rose-200 bg-white px-4 text-sm font-bold text-rose-700 hover:bg-rose-50"
                                    :disabled="processingId === item.id || !item.can_complete_now"
                                    @click="completePlacement(item)"
                                >
                                    Tandai PKL Selesai
                                </Button>
                                <p class="text-xs leading-5 text-zinc-500">
                                    {{
                                        item.can_complete_now
                                            ? 'Siap ditandai selesai karena periode PKL sudah berakhir.'
                                            : 'Tombol aktif setelah tanggal akhir PKL terlewati.'
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="!placements.data.length"
                        class="rounded-xl border border-dashed border-zinc-200 bg-zinc-50 px-5 py-6 text-center"
                    >
                        <p class="text-sm font-bold text-slate-700">
                            Tidak ada data penempatan yang cocok.
                        </p>
                        <p class="mt-2 text-sm text-zinc-500">
                            Ubah filter status atau kata kunci pencarian.
                        </p>
                    </div>
                </div>

                <div
                    class="flex flex-col gap-3 border-t border-zinc-200 pt-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <p class="text-sm text-zinc-500">
                        Menampilkan {{ placements.from ?? 0 }}-{{
                            placements.to ?? 0
                        }}
                        dari {{ placements.total ?? 0 }} data penempatan.
                    </p>

                    <div class="flex flex-wrap items-center gap-2">
                        <template
                            v-for="(link, index) in placements.links"
                            :key="`${index}-${link.label}`"
                        >
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="inline-flex min-w-10 items-center justify-center rounded-xl border px-3 py-2 text-sm transition"
                                :class="
                                    link.active
                                        ? 'border-blue-600 bg-blue-600 text-white'
                                        : 'border-zinc-200 bg-white text-zinc-600 hover:border-zinc-300 hover:text-zinc-900'
                                "
                                preserve-scroll
                                preserve-state
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="inline-flex min-w-10 items-center justify-center rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm text-zinc-400"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
