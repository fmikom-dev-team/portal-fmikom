<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    AlertTriangle,
    MapPin,
    Plus,
    Save,
    Search,
    Trash2,
    UserRound,
} from 'lucide-vue-next';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
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
    DialogDescription,
    DialogHeader,
    DialogScrollContent,
    DialogTitle,
} from '@/components/ui/dialog';
import CompanyLocationPicker from '@/components/wims/CompanyLocationPicker.vue';
import WimsAdminLayout from '@/layouts/Wims/Admin/Layout.vue';

defineOptions({
    layout: WimsAdminLayout,
});

type Filters = {
    search?: string;
};

type Summary = {
    total?: number;
    active?: number;
    configured_location?: number;
    configured_schedule?: number;
    mitra_accounts?: number;
};

type WorkingDayOption = {
    value: string;
    label: string;
};

type CompanyItem = {
    id: number;
    nama: string;
    alamat?: string | null;
    kota?: string | null;
    latitude?: number | null;
    longitude?: number | null;
    radius_valid_meter?: number | null;
    jam_masuk?: string | null;
    jam_pulang?: string | null;
    toleransi_terlambat_menit?: number | null;
    hari_kerja?: string[];
    bidang_industri?: string | null;
    kontak_person?: string | null;
    telepon?: string | null;
    email?: string | null;
    is_active?: boolean;
    account?: AccountItem | null;
};

type AccountItem = {
    id: number;
    user_id: number;
    name?: string | null;
    email?: string | null;
    phone?: string | null;
    jabatan?: string | null;
    is_active?: boolean;
};

const props = defineProps<{
    filters: Filters;
    summary: Summary;
    workingDayOptions: WorkingDayOption[];
    companies: CompanyItem[];
}>();

const page = usePage<{
    flash?: {
        success?: string;
        error?: string;
    };
    errors?: Record<string, string | undefined>;
}>();

const search = ref(props.filters.search || '');
const activeCompanyId = ref<number | null>(props.companies[0]?.id ?? null);
const processing = ref(false);
const accountProcessing = ref(false);
const editorOpen = ref(false);
const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

const emptyForm = () => ({
    nama: '',
    alamat: '',
    kota: '',
    latitude: null as number | null,
    longitude: null as number | null,
    radius_valid_meter: '',
    jam_masuk: '',
    jam_pulang: '',
    toleransi_terlambat_menit: '0',
    hari_kerja: [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
    ] as string[],
    bidang_industri: '',
    kontak_person: '',
    telepon: '',
    email: '',
    is_active: true,
});

const form = reactive(emptyForm());
const accountForm = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    no_telepon: '',
    jabatan: '',
    is_active: true,
});

const selectedCompany = computed(
    () =>
        props.companies.find((item) => item.id === activeCompanyId.value) ??
        null,
);
const isEditMode = computed(() => selectedCompany.value !== null);
const selectedCompanyAccount = computed(
    () => selectedCompany.value?.account ?? null,
);

const hydrateForm = (company: CompanyItem | null) => {
    const next = company
        ? {
              nama: company.nama ?? '',
              alamat: company.alamat ?? '',
              kota: company.kota ?? '',
              latitude: company.latitude ?? null,
              longitude: company.longitude ?? null,
              radius_valid_meter: company.radius_valid_meter?.toString() ?? '',
              jam_masuk: company.jam_masuk ?? '',
              jam_pulang: company.jam_pulang ?? '',
              toleransi_terlambat_menit: String(
                  company.toleransi_terlambat_menit ?? 0,
              ),
              hari_kerja: [
                  ...(company.hari_kerja ?? [
                      'monday',
                      'tuesday',
                      'wednesday',
                      'thursday',
                      'friday',
                  ]),
              ],
              bidang_industri: company.bidang_industri ?? '',
              kontak_person: company.kontak_person ?? '',
              telepon: company.telepon ?? '',
              email: company.email ?? '',
              is_active: company.is_active ?? true,
          }
        : emptyForm();

    Object.assign(form, next);
};

watch(
    selectedCompany,
    (company) => {
        hydrateForm(company);
    },
    { immediate: true },
);

watch(
    () => props.companies,
    (companies) => {
        if (!companies.length) {
            activeCompanyId.value = null;
            hydrateForm(null);
            return;
        }

        if (
            !companies.some((company) => company.id === activeCompanyId.value)
        ) {
            activeCompanyId.value = companies[0].id;
        }
    },
    { deep: true },
);

const filteredCompanies = computed(() => {
    const keyword = search.value.trim().toLowerCase();

    if (!keyword) {
        return props.companies;
    }

    return props.companies.filter((company) =>
        [company.nama, company.kota, company.alamat, company.bidang_industri]
            .filter(Boolean)
            .some((value) => String(value).toLowerCase().includes(keyword)),
    );
});

const startCreate = () => {
    activeCompanyId.value = null;
    hydrateForm(null);
    editorOpen.value = true;
};

const selectCompany = (companyId: number) => {
    activeCompanyId.value = companyId;
    editorOpen.value = true;
};

const closeEditor = () => {
    editorOpen.value = false;
    deleteDialogOpen.value = false;
    activeCompanyId.value = null;
    hydrateForm(null);
};

const resetMentorForm = () => {
    accountForm.name = '';
    accountForm.email = '';
    accountForm.password = '';
    accountForm.password_confirmation = '';
    accountForm.no_telepon = '';
    accountForm.jabatan = '';
    accountForm.is_active = true;
};

const submit = () => {
    processing.value = true;

    const payload = {
        nama: form.nama,
        alamat: form.alamat || null,
        kota: form.kota || null,
        latitude: form.latitude,
        longitude: form.longitude,
        radius_valid_meter: form.radius_valid_meter || null,
        jam_masuk: form.jam_masuk || null,
        jam_pulang: form.jam_pulang || null,
        toleransi_terlambat_menit: form.toleransi_terlambat_menit || 0,
        hari_kerja: form.hari_kerja,
        bidang_industri: form.bidang_industri || null,
        kontak_person: form.kontak_person || null,
        telepon: form.telepon || null,
        email: form.email || null,
        is_active: form.is_active,
    };

    const options = {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            editorOpen.value = false;
            activeCompanyId.value = null;
            hydrateForm(null);
        },
        onFinish: () => {
            processing.value = false;
        },
    };

    if (isEditMode.value && selectedCompany.value) {
        router.put(
            `/wims/admin/perusahaan/${selectedCompany.value.id}`,
            payload,
            options,
        );
        return;
    }

    router.post('/wims/admin/perusahaan', payload, options);
};

const submitMitraAccount = () => {
    if (!selectedCompany.value) {
        return;
    }

    accountProcessing.value = true;

    router.post(
        `/wims/admin/perusahaan/${selectedCompany.value.id}/account`,
        {
            name: accountForm.name,
            email: accountForm.email,
            password: accountForm.password,
            password_confirmation: accountForm.password_confirmation,
            no_telepon: accountForm.no_telepon || null,
            jabatan: accountForm.jabatan || null,
            is_active: accountForm.is_active,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                resetMentorForm();
            },
            onFinish: () => {
                accountProcessing.value = false;
            },
        },
    );
};

const destroyCompany = () => {
    if (!selectedCompany.value) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(`/wims/admin/perusahaan/${selectedCompany.value.id}`, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            deleteDialogOpen.value = false;
            editorOpen.value = false;
            activeCompanyId.value = null;
            hydrateForm(null);
        },
        onFinish: () => {
            deleteProcessing.value = false;
        },
    });
};
</script>

<template>
    <Head title="Perusahaan Mitra Admin" />

    <div
        class="mx-auto w-full max-w-[1320px] space-y-5 px-4 py-4 sm:px-6 sm:py-6 lg:space-y-6 lg:px-8 lg:py-8"
    >
        <section>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <div
                    class="flex min-h-24 flex-col justify-between rounded-xl border border-zinc-200 bg-white px-5 py-4 shadow-none"
                >
                    <p class="text-xs font-bold text-slate-500">Total</p>
                    <p class="mt-2 text-[22px] font-bold tracking-tight text-slate-950 sm:text-[24px]">
                        {{ summary.total ?? 0 }}
                    </p>
                </div>
                <div
                    class="flex min-h-24 flex-col justify-between rounded-xl border border-zinc-200 bg-white px-5 py-4 shadow-none"
                >
                    <p class="text-xs font-bold text-slate-500">Aktif</p>
                    <p class="mt-2 text-[22px] font-bold tracking-tight text-emerald-600 sm:text-[24px]">
                        {{ summary.active ?? 0 }}
                    </p>
                </div>
                <div
                    class="flex min-h-24 flex-col justify-between rounded-xl border border-zinc-200 bg-white px-5 py-4 shadow-none"
                >
                    <p class="text-xs font-bold text-slate-500">
                        Lokasi Siap
                    </p>
                    <p class="mt-2 text-[22px] font-bold tracking-tight text-blue-600 sm:text-[24px]">
                        {{ summary.configured_location ?? 0 }}
                    </p>
                </div>
                <div
                    class="flex min-h-24 flex-col justify-between rounded-xl border border-zinc-200 bg-white px-5 py-4 shadow-none"
                >
                    <p class="text-xs font-bold text-slate-500">
                        Jam Kerja Siap
                    </p>
                    <p class="mt-2 text-[22px] font-bold tracking-tight text-amber-600 sm:text-[24px]">
                        {{ summary.configured_schedule ?? 0 }}
                    </p>
                </div>
                <div
                    class="flex min-h-24 flex-col justify-between rounded-xl border border-zinc-200 bg-white px-5 py-4 shadow-none"
                >
                    <p class="text-xs font-bold text-slate-500">
                        Akun Mitra
                    </p>
                    <p class="mt-2 text-[22px] font-bold tracking-tight text-slate-950 sm:text-[24px]">
                        {{ summary.mitra_accounts ?? 0 }}
                    </p>
                </div>
            </div>
        </section>

        <Card class="rounded-xl border border-zinc-200 bg-white py-0 shadow-none">
            <CardHeader class="border-b border-zinc-200 px-5 py-4">
                <div
                    class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"
                >
                    <div>
                        <CardTitle class="text-[15px] font-bold text-slate-950">
                            Daftar Perusahaan
                        </CardTitle>
                        <CardDescription class="mt-1 text-sm leading-6 text-slate-600">
                            Tinjau perusahaan mitra yang sudah terdaftar, lalu
                            buka detailnya saat perlu diperbarui.
                        </CardDescription>
                    </div>

                    <Button
                        type="button"
                        class="h-9 rounded-lg bg-blue-600 px-4 text-sm font-bold text-white hover:bg-blue-700"
                        @click="startCreate"
                    >
                        <Plus class="size-4" />
                        Tambah Perusahaan
                    </Button>
                </div>
            </CardHeader>

            <CardContent class="space-y-5 px-5 py-5">
                <div class="relative">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                    />
                    <Input
                        v-model="search"
                        type="text"
                        placeholder="Cari perusahaan, kota, atau bidang..."
                        class="h-10 rounded-lg border-zinc-200 bg-zinc-50 pl-10 text-sm"
                    />
                </div>

                <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white">
                    <div
                        v-for="company in filteredCompanies"
                        :key="company.id"
                        class="border-b border-zinc-200 px-4 py-4 transition last:border-b-0 sm:px-5"
                        :class="
                            editorOpen && activeCompanyId === company.id
                                ? 'bg-blue-50/70'
                                : 'bg-white hover:bg-zinc-50'
                        "
                    >
                        <div
                            class="flex h-full flex-col gap-4"
                            :class="
                                editorOpen && activeCompanyId === company.id
                                    ? 'rounded-lg border border-blue-200 bg-blue-50/40 px-4 py-4'
                                    : 'pb-5'
                            "
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-bold text-slate-950"
                                    >
                                        {{ company.nama }}
                                    </p>
                                </div>

                                <span
                                    class="shrink-0 rounded-full px-2.5 py-0.5 text-[10px] font-bold ring-1"
                                    :class="
                                        company.is_active
                                            ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                                            : 'bg-slate-100 text-slate-600 ring-slate-200'
                                    "
                                >
                                    {{
                                        company.is_active
                                            ? 'Aktif'
                                            : 'Nonaktif'
                                    }}
                                </span>
                            </div>

                            <p class="text-xs text-slate-500">
                                {{ company.kota || 'Kota belum diisi' }}
                            </p>

                            <div class="flex items-end justify-between gap-4">
                                <p class="min-w-0 text-xs font-bold text-slate-600">
                                    {{
                                        company.bidang_industri ||
                                        'Bidang umum'
                                    }}
                                </p>
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="h-8 shrink-0 rounded-lg border-zinc-200 bg-white px-3 text-xs font-bold text-zinc-700 hover:border-zinc-300 hover:bg-zinc-50"
                                    @click="selectCompany(company.id)"
                                >
                                    Lihat Data
                                </Button>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="!filteredCompanies.length"
                        class="px-4 py-6 text-center text-sm text-slate-500"
                    >
                        Tidak ada perusahaan yang sesuai.
                    </div>
                </div>
            </CardContent>
        </Card>

        <Dialog
            :open="editorOpen"
            @update:open="
                (open) => {
                    if (!open) closeEditor();
                }
            "
        >
            <DialogScrollContent
                class="w-full max-w-4xl overflow-hidden border-zinc-200 bg-white p-0 shadow-xl"
            >
                <div
                    class="sticky top-0 z-30 border-b border-zinc-200 bg-white/95 px-5 py-4 backdrop-blur sm:px-6"
                >
                    <DialogHeader class="space-y-1.5 text-left">
                        <DialogTitle class="text-[15px] font-bold text-slate-950">
                            {{
                                isEditMode
                                    ? 'Edit Perusahaan Mitra'
                                    : 'Tambah Perusahaan Mitra'
                            }}
                        </DialogTitle>
                        <DialogDescription class="text-sm leading-6 text-slate-600">
                            {{
                                isEditMode
                                    ? 'Lengkapi data perusahaan, lokasi presensi, jam kerja, dan kontak mitra.'
                                    : 'Lengkapi data perusahaan, lokasi presensi, jam kerja, dan kontak mitra.'
                            }}
                        </DialogDescription>
                    </DialogHeader>
                </div>

                <div class="max-h-[calc(100vh-11rem)] space-y-5 overflow-y-auto px-5 py-5 sm:px-6">
                    <Alert
                        v-if="
                            page.props.errors?.location ||
                            page.props.errors?.schedule ||
                            page.props.flash?.error
                        "
                        variant="destructive"
                        class="border-rose-200 bg-rose-50 text-rose-700"
                        >
                            <AlertTitle>Data perusahaan belum valid</AlertTitle>
                            <AlertDescription>
                                {{
                                page.props.errors?.location ||
                                page.props.errors?.schedule ||
                                page.props.flash?.error
                            }}
                        </AlertDescription>
                    </Alert>

                    <section class="space-y-5 rounded-xl border border-zinc-200 bg-white p-4 sm:p-5">
                        <div class="space-y-1">
                            <h3 class="text-[15px] font-bold text-slate-950">
                                Data Perusahaan
                            </h3>
                            <p class="text-xs text-slate-500">
                                Lengkapi identitas dasar perusahaan mitra.
                            </p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="block space-y-2 md:col-span-2">
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                                    Nama Perusahaan
                                    <span class="text-rose-500">*</span>
                                </span>
                                <Input
                                    v-model="form.nama"
                                    type="text"
                                    required
                                    class="h-10 rounded-lg border-zinc-200 bg-white"
                                />
                            </label>

                            <label class="block space-y-2">
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                                    Kota
                                    <span class="text-rose-500">*</span>
                                </span>
                                <Input
                                    v-model="form.kota"
                                    type="text"
                                    class="h-10 rounded-lg border-zinc-200 bg-white"
                                />
                            </label>

                            <label class="block space-y-2">
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                                    Bidang Industri
                                    <span class="text-rose-500">*</span>
                                </span>
                                <Input
                                    v-model="form.bidang_industri"
                                    type="text"
                                    class="h-10 rounded-lg border-zinc-200 bg-white"
                                />
                            </label>

                            <label class="block space-y-2 md:col-span-2">
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                                    Alamat
                                    <span class="text-rose-500">*</span>
                                </span>
                                <textarea
                                    v-model="form.alamat"
                                    rows="3"
                                    class="min-h-24 w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm leading-6 text-zinc-900 transition outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/10"
                                />
                            </label>
                        </div>
                    </section>

                    <section class="space-y-5 rounded-xl border border-zinc-200 bg-white p-4 sm:p-5">
                        <div class="flex items-start gap-3">
                            <div
                                class="flex size-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600"
                            >
                                <MapPin class="size-4" />
                            </div>
                            <div class="space-y-1">
                                <h3 class="text-[15px] font-bold text-slate-950">
                                    Lokasi Presensi
                                </h3>
                                <p class="text-xs text-slate-500">
                                    Cari alamat perusahaan atau pilih titik lokasi pada peta.
                                </p>
                            </div>
                        </div>

                        <div class="company-location-shell rounded-xl border border-zinc-200 bg-zinc-50 p-3">
                            <CompanyLocationPicker
                                v-model:latitude="form.latitude"
                                v-model:longitude="form.longitude"
                                v-model:address="form.alamat"
                            />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            <label class="flex flex-col gap-2">
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                                    Radius Presensi (meter)
                                    <span class="text-rose-500">*</span>
                                </span>
                                <Input
                                    v-model="form.radius_valid_meter"
                                    type="number"
                                    min="10"
                                    class="h-10 rounded-lg border-zinc-200 bg-white"
                                />
                            </label>
                        </div>
                    </section>

                    <section class="space-y-5 rounded-xl border border-zinc-200 bg-white p-4 sm:p-5">
                        <div class="space-y-1">
                            <h3 class="text-[15px] font-bold text-slate-950">
                                Jam Kerja dan Hari Kerja
                            </h3>
                            <p class="text-xs text-slate-500">
                                Digunakan untuk validasi presensi dan monitoring mahasiswa.
                            </p>
                        </div>

                        <div class="grid items-start gap-4 md:grid-cols-2 xl:grid-cols-3">
                            <label class="flex flex-col gap-2">
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                                    Jam Masuk
                                    <span class="text-rose-500">*</span>
                                </span>
                                <Input
                                    v-model="form.jam_masuk"
                                    type="time"
                                    class="h-10 rounded-lg border-zinc-200 bg-white"
                                />
                            </label>
                            <label class="flex flex-col gap-2">
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                                    Jam Pulang
                                    <span class="text-rose-500">*</span>
                                </span>
                                <Input
                                    v-model="form.jam_pulang"
                                    type="time"
                                    class="h-10 rounded-lg border-zinc-200 bg-white"
                                />
                            </label>
                            <label class="flex flex-col gap-2">
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                                    Toleransi Terlambat (menit)
                                    <span class="text-rose-500">*</span>
                                </span>
                                <Input
                                    v-model="form.toleransi_terlambat_menit"
                                    type="number"
                                    min="0"
                                    class="h-10 rounded-lg border-zinc-200 bg-white"
                                />
                            </label>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <p class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                                    Hari Kerja Perusahaan
                                    <span class="text-rose-500">*</span>
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <label
                                    v-for="day in props.workingDayOptions"
                                    :key="day.value"
                                    class="flex items-center gap-2 rounded-full border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-700"
                                >
                                    <input
                                        v-model="form.hari_kerja"
                                        :value="day.value"
                                        type="checkbox"
                                        class="size-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-600"
                                    />
                                    <span>{{ day.label }}</span>
                                </label>
                            </div>
                        </div>

                        <p class="text-xs leading-5 text-slate-500">
                            Jam masuk, toleransi, dan hari kerja digunakan untuk menentukan status presensi dan monitoring mahasiswa.
                        </p>
                    </section>

                    <section class="space-y-5 rounded-xl border border-zinc-200 bg-white p-4 sm:p-5">
                        <div class="space-y-1">
                            <h3 class="text-[15px] font-bold text-slate-950">
                                Kontak dan Status
                            </h3>
                            <p class="text-xs text-slate-500">
                                Informasi kontak perusahaan dan status operasional mitra.
                            </p>
                        </div>

                        <div class="grid items-start gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <label class="flex flex-col gap-2">
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                                    Kontak Person
                                    <span class="text-rose-500">*</span>
                                </span>
                                <Input
                                    v-model="form.kontak_person"
                                    type="text"
                                    class="h-10 rounded-lg border-zinc-200 bg-white"
                                />
                            </label>
                            <label class="flex flex-col gap-2">
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                                    Telepon
                                    <span class="text-rose-500">*</span>
                                </span>
                                <Input
                                    v-model="form.telepon"
                                    type="text"
                                    class="h-10 rounded-lg border-zinc-200 bg-white"
                                />
                            </label>
                            <label class="flex flex-col gap-2">
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                                    Email
                                    <span class="text-rose-500">*</span>
                                </span>
                                <Input
                                    v-model="form.email"
                                    type="email"
                                    class="h-10 rounded-lg border-zinc-200 bg-white"
                                />
                            </label>
                            <label class="flex flex-col gap-2">
                                <span class="inline-flex min-h-5 items-end gap-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                                    Status
                                    <span class="text-rose-500">*</span>
                                </span>
                                <select
                                    v-model="form.is_active"
                                    required
                                    class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 transition outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/10"
                                >
                                    <option :value="true">Aktif</option>
                                    <option :value="false">Nonaktif</option>
                                </select>
                            </label>
                        </div>
                    </section>

                    <section
                        v-if="isEditMode"
                        class="space-y-5 rounded-xl border border-zinc-200 bg-white p-4 sm:p-5"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="flex size-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600"
                            >
                                <UserRound class="size-4" />
                            </div>
                            <div>
                                <p class="text-[15px] font-bold text-slate-950">
                                    Akun Mitra Perusahaan
                                </p>
                                <p class="text-xs text-slate-500">
                                    Satu akun mitra akan menjadi akun login dan
                                    pembimbing mitra untuk perusahaan ini.
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="selectedCompanyAccount"
                            class="space-y-3"
                        >
                            <div
                                class="rounded-xl border border-zinc-200 bg-white px-4 py-4"
                            >
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div class="min-w-0">
                                        <p
                                            class="truncate text-sm font-bold text-zinc-950"
                                        >
                                            {{ selectedCompanyAccount?.name || '-' }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ selectedCompanyAccount?.email || '-' }}
                                        </p>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <span
                                                class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-bold text-slate-600"
                                            >
                                                {{
                                                    selectedCompanyAccount?.jabatan ||
                                                    'Jabatan belum diisi'
                                                }}
                                            </span>
                                            <span
                                                class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-bold text-slate-600"
                                            >
                                                {{
                                                    selectedCompanyAccount?.phone ||
                                                    'Telepon belum diisi'
                                                }}
                                            </span>
                                            <span
                                                class="rounded-full px-3 py-1 text-xs font-bold ring-1"
                                                :class="
                                                    selectedCompanyAccount?.is_active
                                                        ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                                                        : 'bg-slate-100 text-slate-600 ring-slate-200'
                                                "
                                            >
                                                {{
                                                    selectedCompanyAccount?.is_active
                                                        ? 'Aktif'
                                                        : 'Nonaktif'
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else-if="isEditMode"
                            class="rounded-xl border border-dashed border-zinc-200 bg-zinc-50 px-4 py-4 text-center text-sm text-slate-500"
                        >
                            Belum ada akun mitra untuk perusahaan ini.
                        </div>

                        <form
                            v-if="isEditMode"
                            class="grid gap-4 border-t border-zinc-200 pt-5 md:grid-cols-2"
                            autocomplete="off"
                            @submit.prevent="submitMitraAccount"
                        >
                            <input
                                type="text"
                                name="fake_username"
                                autocomplete="username"
                                class="hidden"
                                tabindex="-1"
                            />
                            <input
                                type="password"
                                name="fake_password"
                                autocomplete="current-password"
                                class="hidden"
                                tabindex="-1"
                            />

                            <Alert
                                v-if="
                                    page.props.errors?.name ||
                                    page.props.errors?.email ||
                                    page.props.errors?.password ||
                                    page.props.errors?.no_telepon ||
                                    page.props.errors?.jabatan
                                "
                                variant="destructive"
                                class="border-rose-200 bg-rose-50 text-rose-700 md:col-span-2"
                            >
                                <AlertTitle
                                    >Data akun mitra belum valid</AlertTitle
                                >
                                <AlertDescription>
                                    {{
                                        page.props.errors?.name ||
                                        page.props.errors?.email ||
                                        page.props.errors?.password ||
                                        page.props.errors?.no_telepon ||
                                        page.props.errors?.jabatan
                                    }}
                                </AlertDescription>
                            </Alert>

                            <label class="block space-y-2">
                                <span
                                    class="inline-flex items-center gap-1 text-[11px] font-bold tracking-[0.16em] text-slate-500 uppercase"
                                >
                                    Nama Mitra
                                    <span class="text-rose-500">*</span>
                                </span>
                                <Input
                                    v-model="accountForm.name"
                                    name="account_name"
                                    autocomplete="off"
                                    type="text"
                                    required
                                    class="h-10 rounded-lg border-zinc-200 bg-white"
                                />
                            </label>
                            <label class="block space-y-2">
                                <span
                                    class="inline-flex items-center gap-1 text-[11px] font-bold tracking-[0.16em] text-slate-500 uppercase"
                                >
                                    Email Login
                                    <span class="text-rose-500">*</span>
                                </span>
                                <Input
                                    v-model="accountForm.email"
                                    name="account_email"
                                    autocomplete="username"
                                    type="email"
                                    required
                                    class="h-10 rounded-lg border-zinc-200 bg-white"
                                />
                            </label>
                            <label class="block space-y-2">
                                <span
                                    class="text-[11px] font-bold tracking-[0.16em] text-slate-500 uppercase"
                                    >Nomor Telepon</span
                                >
                                <Input
                                    v-model="accountForm.no_telepon"
                                    name="account_phone"
                                    autocomplete="tel"
                                    type="text"
                                    class="h-10 rounded-lg border-zinc-200 bg-white"
                                />
                            </label>
                            <label class="block space-y-2">
                                <span
                                    class="text-[11px] font-bold tracking-[0.16em] text-slate-500 uppercase"
                                    >Jabatan di Perusahaan</span
                                >
                                <Input
                                    v-model="accountForm.jabatan"
                                    name="account_position"
                                    autocomplete="organization-title"
                                    type="text"
                                    class="h-10 rounded-lg border-zinc-200 bg-white"
                                />
                                <p class="text-xs text-slate-500">
                                    Contoh: Supervisor, HRD, Kepala Divisi, atau
                                    PIC Mitra.
                                </p>
                            </label>
                            <label class="block space-y-2">
                                <span
                                    class="inline-flex items-center gap-1 text-[11px] font-bold tracking-[0.16em] text-slate-500 uppercase"
                                >
                                    Password Awal
                                    <span class="text-rose-500">*</span>
                                </span>
                                <Input
                                    v-model="accountForm.password"
                                    name="account_password"
                                    autocomplete="new-password"
                                    type="password"
                                    required
                                    class="h-10 rounded-lg border-zinc-200 bg-white"
                                />
                            </label>
                            <label class="block space-y-2">
                                <span
                                    class="inline-flex items-center gap-1 text-[11px] font-bold tracking-[0.16em] text-slate-500 uppercase"
                                >
                                    Konfirmasi Password
                                    <span class="text-rose-500">*</span>
                                </span>
                                <Input
                                    v-model="accountForm.password_confirmation"
                                    name="account_password_confirmation"
                                    autocomplete="new-password"
                                    type="password"
                                    required
                                    class="h-10 rounded-lg border-zinc-200 bg-white"
                                />
                            </label>
                            <label class="block space-y-2 md:col-span-2">
                                <span
                                    class="inline-flex items-center gap-1 text-[11px] font-bold tracking-[0.16em] text-slate-500 uppercase"
                                >
                                    Status Akun
                                    <span class="text-rose-500">*</span>
                                </span>
                                <select
                                    v-model="accountForm.is_active"
                                    required
                                    class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 transition outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/10"
                                >
                                    <option :value="true">Aktif</option>
                                    <option :value="false">Nonaktif</option>
                                </select>
                            </label>
                            <div class="flex justify-end md:col-span-2">
                                <Button
                                    type="submit"
                                    class="h-9 rounded-lg bg-blue-600 px-4 text-sm font-bold text-white hover:bg-blue-700"
                                    :disabled="accountProcessing"
                                >
                                    <Plus class="size-4" />
                                    Buat Akun Mitra
                                </Button>
                            </div>
                        </form>

                        <div
                            v-else
                            class="rounded-xl border border-dashed border-zinc-200 bg-white px-4 py-4 text-center text-sm text-slate-500"
                        >
                            Simpan data perusahaan terlebih dahulu untuk membuat akun mitra.
                        </div>
                    </section>

                    <div
                        class="sticky bottom-0 z-30 -mx-5 flex flex-col-reverse gap-3 border-t border-zinc-200 bg-white px-5 py-4 shadow-[0_-8px_18px_rgba(15,23,42,0.06)] sm:mx-0 sm:flex-row sm:items-center sm:justify-end sm:px-0"
                    >
                        <Button
                            type="button"
                            variant="outline"
                            class="h-9 rounded-lg border-zinc-200 px-4 text-sm font-bold text-zinc-700"
                            @click="closeEditor"
                        >
                            Batal
                        </Button>
                        <Button
                            type="button"
                            class="h-9 rounded-lg bg-blue-600 px-4 text-sm font-bold text-white hover:bg-blue-700"
                            :disabled="processing"
                            @click="submit"
                        >
                            <Save class="size-4" />
                            {{
                                isEditMode
                                    ? 'Simpan Perubahan'
                                    : 'Tambah Perusahaan'
                            }}
                        </Button>
                        <Button
                            v-if="isEditMode"
                            type="button"
                            variant="outline"
                            class="h-9 rounded-lg border-rose-200 px-4 text-sm font-bold text-rose-600 hover:bg-rose-50 hover:text-rose-700"
                            @click="deleteDialogOpen = true"
                        >
                            <Trash2 class="size-4" />
                            Hapus Perusahaan
                        </Button>
                    </div>
                </div>
            </DialogScrollContent>
        </Dialog>

        <Dialog v-model:open="deleteDialogOpen">
            <DialogScrollContent
                class="w-full max-w-md border-zinc-200 bg-white p-0 shadow-xl"
            >
                <div class="space-y-5 px-5 py-5 sm:px-6">
                    <DialogHeader class="items-center space-y-2 text-center">
                        <div
                            class="flex size-10 items-center justify-center rounded-full bg-rose-50 text-rose-600"
                        >
                            <AlertTriangle class="size-5" />
                        </div>
                        <DialogTitle class="text-[15px] font-bold text-slate-950">
                            Hapus Perusahaan Mitra
                        </DialogTitle>
                        <DialogDescription class="text-sm leading-6 text-slate-600">
                            Yakin ingin menghapus perusahaan mitra? semua data terkait perusahaan akan hilang, harap berhati-hati !
                        </DialogDescription>
                    </DialogHeader>

                    <div
                        v-if="selectedCompany"
                        class="rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3"
                    >
                        <p class="text-sm font-bold text-zinc-950">
                            {{ selectedCompany.nama }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ selectedCompany.kota || 'Kota belum diisi' }}
                        </p>
                    </div>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <Button
                            type="button"
                            variant="outline"
                            class="h-9 rounded-lg border-zinc-200 px-4 text-sm font-bold text-zinc-700"
                            :disabled="deleteProcessing"
                            @click="deleteDialogOpen = false"
                        >
                            Batal
                        </Button>
                        <Button
                            type="button"
                            class="h-9 rounded-lg bg-rose-600 px-4 text-sm font-bold text-white hover:bg-rose-700"
                            :disabled="deleteProcessing"
                            @click="destroyCompany"
                        >
                            <Trash2 class="size-4" />
                            Ya, Hapus
                        </Button>
                    </div>
                </div>
            </DialogScrollContent>
        </Dialog>
    </div>
</template>

<style scoped>
.company-location-shell :deep(.space-y-4 > .flex.flex-col.gap-3.sm\:flex-row > button) {
    border: 1px solid #e0e0e0;
    background: #ffffff;
    color: #525252;
}

.company-location-shell :deep(.space-y-4 > .flex.flex-col.gap-3.sm\:flex-row > button:hover) {
    background: #f8fafc;
}
</style>
