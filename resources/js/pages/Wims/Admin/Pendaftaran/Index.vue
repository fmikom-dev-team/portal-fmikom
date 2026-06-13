<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    BriefcaseBusiness,
    CheckCheck,
    RotateCcw,
    Search,
    SlidersHorizontal,
    XCircle,
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
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import WimsAdminLayout from '@/layouts/Wims/Admin/Layout.vue';
import wimsRoutes from '@/routes/wims';

defineOptions({
    layout: WimsAdminLayout,
});

type Filters = {
    status?: string;
    search?: string;
};

type Summary = {
    all?: number;
    pending?: number;
    approved?: number;
    revisi?: number;
    rejected?: number;
    aktif?: number;
};

type RegistrationItem = {
    id: number;
    student_name?: string | null;
    student_email?: string | null;
    student_identity?: string | null;
    proposal_company_name?: string | null;
    proposal_company_address?: string | null;
    final_company_name?: string | null;
    application_note?: string | null;
    revision_note?: string | null;
    tanggal_mulai?: string | null;
    tanggal_selesai?: string | null;
    status?: string | null;
    dosen_pembimbing_id?: number | null;
};

type PaginationLink = {
    url?: string | null;
    label: string;
    active: boolean;
};

type RegistrationPagination = {
    data: RegistrationItem[];
    current_page?: number;
    last_page?: number;
    per_page?: number;
    total?: number;
    from?: number | null;
    to?: number | null;
    links: PaginationLink[];
};

const props = defineProps<{
    filters: Filters;
    summary: Summary;
    registrations: RegistrationPagination;
}>();

const status = ref(props.filters.status || 'all');
const search = ref(props.filters.search || '');
const processingId = ref<number | null>(null);
const revisionDialogOpen = ref(false);
const revisionTarget = ref<RegistrationItem | null>(null);
const revisionNote = ref('');

watch(
    () => props.filters,
    (filters) => {
        status.value = filters.status || 'all';
        search.value = filters.search || '';
    },
    { deep: true },
);

const statusTabs = computed(() => [
    { label: 'Semua', value: 'all', count: props.summary.all ?? 0 },
    { label: 'Menunggu', value: 'pending', count: props.summary.pending ?? 0 },
    {
        label: 'Disetujui',
        value: 'approved',
        count: props.summary.approved ?? 0,
    },
    { label: 'Revisi', value: 'revisi', count: props.summary.revisi ?? 0 },
    {
        label: 'Ditolak',
        value: 'rejected',
        count: props.summary.rejected ?? 0,
    },
    { label: 'Aktif', value: 'aktif', count: props.summary.aktif ?? 0 },
]);

const applyFilters = () => {
    router.get(
        wimsRoutes.admin.registrations.index().url,
        {
            status: status.value,
            search: search.value || undefined,
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
};

const selectStatus = (value: string) => {
    status.value = value;
    applyFilters();
};

const resetFilters = () => {
    status.value = 'all';
    search.value = '';
    applyFilters();
};

const statusLabel = (value?: string | null) => {
    if (value === 'selesai') {
        return 'Selesai';
    }

    if (value === 'approved') {
        return 'Disetujui';
    }

    if (value === 'rejected') {
        return 'Ditolak';
    }

    if (value === 'revisi') {
        return 'Revisi';
    }

    if (value === 'aktif') {
        return 'Aktif';
    }

    return 'Menunggu';
};

const statusClass = (value?: string | null) => {
    if (value === 'selesai') {
        return 'border-violet-200 bg-violet-50 text-violet-700';
    }

    if (value === 'approved') {
        return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    }

    if (value === 'rejected') {
        return 'border-rose-200 bg-rose-50 text-rose-700';
    }

    if (value === 'revisi') {
        return 'border-amber-200 bg-amber-50 text-amber-700';
    }

    if (value === 'aktif') {
        return 'border-sky-200 bg-sky-50 text-sky-700';
    }

    return 'border-blue-200 bg-blue-50 text-[#2563EB]';
};

const canReview = (item: RegistrationItem) =>
    !inArrayStatus(item.status, ['aktif', 'selesai']);

const inArrayStatus = (value: string | null | undefined, items: string[]) =>
    value !== null && value !== undefined && items.includes(value);

const updateStatus = (
    item: RegistrationItem,
    nextStatus: 'approved' | 'rejected' | 'revisi',
) => {
    if (!canReview(item)) {
        return;
    }

    if (nextStatus === 'revisi') {
        revisionTarget.value = item;
        revisionNote.value = item.revision_note ?? '';
        revisionDialogOpen.value = true;

        return;
    }

    processingId.value = item.id;

    router.patch(
        wimsRoutes.admin.registrations.updateStatus(item.id).url,
        {
            status: nextStatus,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                processingId.value = null;
            },
        },
    );
};

const submitRevision = () => {
    if (!revisionTarget.value || !revisionNote.value.trim()) {
        return;
    }

    processingId.value = revisionTarget.value.id;

    router.patch(
        wimsRoutes.admin.registrations.updateStatus(revisionTarget.value.id)
            .url,
        {
            status: 'revisi',
            catatan_revisi_admin: revisionNote.value.trim(),
        },
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                processingId.value = null;
            },
            onSuccess: () => {
                revisionDialogOpen.value = false;
                revisionTarget.value = null;
                revisionNote.value = '';
            },
        },
    );
};

const studentInitial = (name?: string | null) => {
    const words = name?.trim().split(/\s+/).filter(Boolean).slice(0, 2) ?? [];

    if (!words.length) {
        return 'M';
    }

    return words.map((word) => word.charAt(0).toUpperCase()).join('');
};

const placementLink = (item: RegistrationItem) =>
    `${wimsRoutes.admin.placements.index().url}?pendaftaran=${item.id}`;
</script>

<template>
    <Head title="Pendaftaran Magang Admin" />

    <div
        class="mx-auto w-full max-w-[1320px] space-y-5 px-4 py-4 sm:px-6 sm:py-6 lg:space-y-6 lg:px-8 lg:py-8"
    >
        <section>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div
                    class="flex min-h-24 flex-col justify-between rounded-xl border border-zinc-200 bg-white px-5 py-4 shadow-none"
                >
                    <p class="text-xs font-bold text-slate-500">Total</p>
                    <p class="mt-2 text-[22px] font-bold tracking-tight text-slate-950 sm:text-[24px]">
                        {{ summary.all ?? 0 }}
                    </p>
                </div>
                <div
                    class="flex min-h-24 flex-col justify-between rounded-xl border border-zinc-200 bg-white px-5 py-4 shadow-none"
                >
                    <p class="text-xs font-bold text-slate-500">Menunggu</p>
                    <p class="mt-2 text-[22px] font-bold tracking-tight text-amber-600 sm:text-[24px]">
                        {{ summary.pending ?? 0 }}
                    </p>
                </div>
                <div
                    class="flex min-h-24 flex-col justify-between rounded-xl border border-zinc-200 bg-white px-5 py-4 shadow-none"
                >
                    <p class="text-xs font-bold text-slate-500">Disetujui</p>
                    <p class="mt-2 text-[22px] font-bold tracking-tight text-emerald-600 sm:text-[24px]">
                        {{ summary.approved ?? 0 }}
                    </p>
                </div>
                <div
                    class="flex min-h-24 flex-col justify-between rounded-xl border border-zinc-200 bg-white px-5 py-4 shadow-none"
                >
                    <p class="text-xs font-bold text-slate-500">Revisi</p>
                    <p class="mt-2 text-[22px] font-bold tracking-tight text-[#2563EB] sm:text-[24px]">
                        {{ summary.revisi ?? 0 }}
                    </p>
                </div>
            </div>
        </section>

        <Card class="rounded-xl border border-zinc-200 bg-white py-0 shadow-none">
            <CardHeader class="border-b border-zinc-200 px-5 py-4">
                <div class="space-y-5">
                    <div>
                        <CardTitle class="text-[15px] font-bold text-slate-950">
                            Filter Pengajuan
                        </CardTitle>
                        <CardDescription class="mt-1 text-sm leading-6 text-slate-600">
                            Cari mahasiswa atau perusahaan, lalu persempit
                            daftar berdasarkan status pengajuan.
                        </CardDescription>
                    </div>

                    <div
                        class="-mx-1 overflow-x-auto [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden lg:overflow-visible"
                    >
                        <div class="flex min-w-max flex-nowrap items-center gap-2 px-1 lg:min-w-0 lg:flex-wrap">
                            <button
                                v-for="tab in statusTabs"
                                :key="tab.value"
                                type="button"
                                class="inline-flex h-8 shrink-0 items-center gap-2 rounded-lg border px-3 text-sm font-bold whitespace-nowrap transition"
                                :class="
                                    status === tab.value
                                        ? 'border-blue-600 bg-blue-600 text-white'
                                        : 'border-zinc-200 bg-white text-zinc-600 hover:border-zinc-300 hover:text-zinc-900'
                                "
                                @click="selectStatus(tab.value)"
                            >
                                <span>{{ tab.label }}</span>
                                <span
                                    class="rounded-full px-2.5 py-0.5 text-[10px] font-bold"
                                    :class="
                                        status === tab.value
                                            ? 'bg-white/15 text-white'
                                            : 'bg-zinc-100 text-zinc-500'
                                    "
                                >
                                    {{ tab.count }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </CardHeader>

            <CardContent class="space-y-5 px-5 py-5">
                <form
                    class="grid gap-4 md:grid-cols-[minmax(0,1fr)_auto_auto]"
                    @submit.prevent="applyFilters"
                >
                    <div class="relative">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-zinc-400"
                        />
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Cari nama mahasiswa, email, NIM, atau perusahaan..."
                            class="h-10 rounded-lg border-zinc-200 bg-zinc-50 pl-10"
                        />
                    </div>

                    <Button
                        type="submit"
                        class="h-10 rounded-lg bg-blue-600 px-4 text-white hover:bg-blue-700"
                    >
                        <SlidersHorizontal class="size-4" />
                        Terapkan
                    </Button>

                    <Button
                        type="button"
                        variant="outline"
                        class="h-10 rounded-lg border-zinc-200 px-4 text-zinc-700"
                        @click="resetFilters"
                    >
                        Reset
                    </Button>
                </form>

                <div class="space-y-4">
                    <div
                        v-if="registrations.data.length"
                        class="space-y-4"
                    >
                        <div
                            v-for="item in registrations.data"
                            :key="item.id"
                            class="rounded-xl border border-zinc-200 bg-white px-4 py-4 shadow-none sm:px-5"
                        >
                            <div
                                class="flex flex-col gap-4 xl:grid xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.25fr)_minmax(280px,0.85fr)] xl:items-start"
                            >
                                <div class="min-w-0">
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="flex size-10 items-center justify-center rounded-lg bg-blue-50 text-[13px] font-bold text-blue-600"
                                        >
                                            {{ studentInitial(item.student_name) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p
                                                    class="truncate text-sm font-bold text-zinc-950"
                                                >
                                                    {{ item.student_name || '-' }}
                                                </p>
                                                <Badge
                                                    variant="outline"
                                                    class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase shadow-none"
                                                    :class="statusClass(item.status)"
                                                >
                                                    {{ statusLabel(item.status) }}
                                                </Badge>
                                            </div>
                                            <p class="mt-1 truncate text-sm text-zinc-500">
                                                {{ item.student_email || '-' }}
                                            </p>
                                            <p class="mt-1 text-xs text-zinc-400">
                                                {{
                                                    item.student_identity ||
                                                    'Identitas belum tersedia'
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="min-w-0 space-y-4 xl:pr-2">
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="mt-0.5 flex size-9 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600"
                                        >
                                            <BriefcaseBusiness class="size-4" />
                                        </div>
                                        <div class="min-w-0 flex-1 space-y-4">
                                            <div>
                                                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                                                    Perusahaan Usulan
                                                </p>
                                                <p
                                                    class="mt-1 text-sm font-bold text-zinc-900"
                                                >
                                                    {{
                                                        item.proposal_company_name ||
                                                        'Belum ada usulan perusahaan'
                                                    }}
                                                </p>
                                                <p
                                                    v-if="
                                                        item.proposal_company_address ||
                                                        !['aktif', 'selesai'].includes(
                                                            item.status || '',
                                                        )
                                                    "
                                                    class="mt-1 text-sm leading-6 text-slate-600"
                                                >
                                                    {{
                                                        item.proposal_company_address ||
                                                        'Kampus dapat menentukan perusahaan pada tahap penempatan.'
                                                    }}
                                                </p>
                                            </div>
                                            <div>
                                                <p
                                                    class="text-[11px] font-bold uppercase tracking-[0.16em]"
                                                    :class="
                                                        ['aktif', 'selesai'].includes(
                                                            item.status || '',
                                                        )
                                                            ? 'text-slate-600'
                                                            : 'text-slate-500'
                                                    "
                                                >
                                                    Penempatan Final
                                                </p>
                                                <p
                                                    class="mt-1 text-sm"
                                                    :class="
                                                        ['aktif', 'selesai'].includes(
                                                            item.status || '',
                                                        )
                                                            ? 'font-bold text-zinc-950'
                                                            : 'text-zinc-700'
                                                    "
                                                >
                                                    {{
                                                        item.final_company_name ||
                                                        'Belum ditetapkan kampus'
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="space-y-4 rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-4 xl:min-h-full"
                                >
                                    <div class="space-y-1">
                                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                                            Periode PKL/Magang
                                        </p>
                                        <p class="text-sm font-bold text-zinc-900">
                                            {{ item.tanggal_mulai || '-' }}
                                        </p>
                                        <p class="text-xs text-zinc-500">
                                            s/d {{ item.tanggal_selesai || '-' }}
                                        </p>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2">
                                        <template v-if="item.status === 'approved'">
                                            <span
                                                class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700"
                                            >
                                                Siap ditempatkan
                                            </span>
                                            <Link
                                                :href="placementLink(item)"
                                                class="inline-flex h-9 items-center gap-2 rounded-lg bg-blue-600 px-3 text-sm font-bold text-white transition hover:bg-blue-700"
                                            >
                                                <BriefcaseBusiness class="size-4" />
                                                Lanjut ke Penempatan
                                            </Link>
                                        </template>
                                        <template v-else-if="canReview(item)">
                                            <Button
                                                type="button"
                                                size="sm"
                                                class="h-9 rounded-lg bg-emerald-600 px-3 text-sm font-bold text-white hover:bg-emerald-700"
                                                :disabled="
                                                    processingId === item.id ||
                                                    item.status === 'approved'
                                                "
                                                @click="updateStatus(item, 'approved')"
                                            >
                                                <CheckCheck class="size-4" />
                                                Setujui
                                            </Button>
                                            <Button
                                                type="button"
                                                size="sm"
                                                class="h-9 rounded-lg border border-amber-200 bg-amber-50 px-3 text-sm font-bold text-amber-700 hover:bg-amber-100"
                                                :disabled="
                                                    processingId === item.id ||
                                                    item.status === 'revisi'
                                                "
                                                @click="updateStatus(item, 'revisi')"
                                            >
                                                <RotateCcw class="size-4" />
                                                Revisi
                                            </Button>
                                            <Button
                                                type="button"
                                                size="sm"
                                                class="h-9 rounded-lg border border-rose-200 bg-white px-3 text-sm font-bold text-rose-700 hover:bg-rose-50"
                                                :disabled="
                                                    processingId === item.id ||
                                                    item.status === 'rejected'
                                                "
                                                @click="updateStatus(item, 'rejected')"
                                            >
                                                <XCircle class="size-4" />
                                                Tolak
                                            </Button>
                                        </template>
                                        <div
                                            v-else
                                            class="flex flex-wrap items-center gap-2 text-xs"
                                        >
                                            <span
                                                class="rounded-full border border-zinc-200 bg-white px-2.5 py-0.5 text-[10px] font-bold text-slate-600"
                                            >
                                                {{
                                                    item.status === 'selesai'
                                                        ? 'Sudah selesai'
                                                        : 'Sudah aktif'
                                                }}
                                            </span>
                                            <Link
                                                :href="placementLink(item)"
                                                class="font-bold text-blue-600 hover:text-blue-700"
                                            >
                                                Kelola di Penempatan
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="item.application_note || item.revision_note"
                                class="mt-5 border-t border-zinc-200 pt-5"
                            >
                                <div class="grid gap-4 lg:grid-cols-2">
                                    <div
                                        v-if="item.application_note"
                                        class="rounded-lg bg-zinc-50 px-4 py-3"
                                    >
                                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                                            Catatan mahasiswa
                                        </p>
                                        <p class="mt-1 text-sm leading-6 text-slate-600">
                                            {{ item.application_note }}
                                        </p>
                                    </div>
                                    <div
                                        v-if="item.revision_note"
                                        class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3"
                                    >
                                        <p class="text-sm font-bold text-amber-800">
                                            Catatan revisi admin
                                        </p>
                                        <p class="mt-1 text-sm leading-6 text-amber-800">
                                            {{ item.revision_note }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="rounded-xl border border-dashed border-zinc-200 bg-zinc-50 px-5 py-8 text-center"
                    >
                        <p class="text-sm font-bold text-slate-700">
                            Tidak ada pengajuan yang cocok dengan filter saat
                            ini.
                        </p>
                        <p class="mt-2 text-sm text-slate-500">
                            Coba ubah status filter atau kata kunci pencarian.
                        </p>
                    </div>
                </div>

                <div
                    class="flex items-center justify-between gap-4 border-t border-zinc-200 pt-5"
                >
                    <div
                        class="flex flex-wrap items-center gap-2 text-sm text-zinc-500"
                    >
                        <span
                            >Menampilkan {{ registrations.from ?? 0 }}-{{
                                registrations.to ?? 0
                            }}
                            dari {{ registrations.total ?? 0 }} pengajuan.</span
                        >
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <template
                            v-for="(link, index) in registrations.links"
                            :key="`${index}-${link.label}`"
                        >
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="inline-flex min-w-10 items-center justify-center rounded-lg border px-3 py-2 text-sm transition"
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
                                class="inline-flex min-w-10 items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm text-zinc-400"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </CardContent>
        </Card>

        <Dialog v-model:open="revisionDialogOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader class="space-y-1.5 text-left">
                    <DialogTitle class="text-[15px] font-bold text-slate-950">Catatan Revisi Pendaftaran</DialogTitle>
                    <DialogDescription class="text-sm leading-6 text-slate-600">
                        Tulis catatan yang perlu diperbaiki mahasiswa sebelum
                        pengajuan dikirim ulang.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <div
                        class="rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-700"
                    >
                        <p class="font-bold text-zinc-900">
                            {{ revisionTarget?.student_name || '-' }}
                        </p>
                        <p class="mt-1 text-xs text-zinc-500">
                            {{
                                revisionTarget?.proposal_company_name ||
                                'Tanpa usulan perusahaan'
                            }}
                        </p>
                    </div>

                    <div class="space-y-2.5">
                        <label
                            for="revision-note"
                            class="inline-flex items-center gap-1 text-[11px] font-bold tracking-[0.16em] uppercase text-slate-500"
                        >
                            Alasan revisi
                            <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            id="revision-note"
                            v-model="revisionNote"
                            rows="5"
                            required
                            class="min-h-32 w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm leading-6 text-zinc-900 transition outline-none focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/15"
                            placeholder="Contoh: lengkapi nama perusahaan, perjelas alamat, atau sesuaikan periode pengajuan."
                        />
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        class="h-10 rounded-lg border-zinc-200 text-sm font-bold text-zinc-700"
                        @click="revisionDialogOpen = false"
                    >
                        Batal
                    </Button>
                    <Button
                        type="button"
                        class="h-10 rounded-lg border border-amber-200 bg-amber-50 text-sm font-bold text-amber-700 hover:bg-amber-100"
                        :disabled="
                            !revisionTarget ||
                            !revisionNote.trim() ||
                            processingId === revisionTarget?.id
                        "
                        @click="submitRevision"
                    >
                        Simpan Revisi
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
