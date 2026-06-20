<script setup lang="ts">
// resources/js/pages/Modules/Fast/Admin/letters/Edit.vue
import AdminLayout from '@/layouts/Modules/Fast/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { ChevronLeft, Save, Search, Plus, X, FileEdit } from 'lucide-vue-next';
type FieldOption = { label: string; value: string };
type FieldConfig = {
    name: string;
    label: string;
    type: string;
    required: boolean;
    placeholder?: string;
    help?: string;
    options?: FieldOption[];
    add_label?: string;
    item_label?: string;
    repeatable?: boolean;
    sumber_data?: 'data_pemohon' | 'data_kampus' | 'data_sistem';
    editable_role?: 'mahasiswa' | 'admin' | 'sistem';
    mode_form_pemohon?: 'editable' | 'readonly' | 'hidden';
};
type JenisSurat = {
    id: number;
    nama: string;
    slug?: string | null;
    deskripsi?: string | null;
    approval_role?: {
        id?: number | null;
        nama?: string | null;
        slug?: string | null;
    } | null;
    category?: { id?: number | null; nama?: string | null } | null;
    template?: {
        id?: number | null;
        name?: string | null;
        subject?: string | null;
    } | null;
    field_config: FieldConfig[];
};
type FormData = {
    jenis_surat_id: number;
    keperluan: string;
    perihal?: string;
    kepada_yth?: string[];
    lampiran_keterangan?: string;
    data: Record<string, string | boolean | string[]>;
};
const props = defineProps<{
    surat: { id: number; keperluan: string; status: string };
    returnTo?: string | null;
    jenisSurat: JenisSurat;
    formData: FormData;
}>();
// â”€â”€ Preset Kepada Yth â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const defaultPresets = [
    'Dekan FMIKOM',
    'Ketua Program Studi Informatika',
    'Ketua Program Studi Sistem Informasi',
    'Wakil Dekan Bidang Akademik',
];
const consumedFormKeys = ['kepada_yth', 'lampiran_keterangan', 'perihal'] as const;
// â”€â”€ Form â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const form = useForm({
    jenis_surat_id: props.formData.jenis_surat_id,
    keperluan: props.formData.keperluan ?? '',
    perihal:
        props.formData.perihal ??
        (props.formData.data.perihal as string | undefined) ??
        '',
    kepada_yth:
        props.formData.kepada_yth ??
        (props.formData.data.kepada_yth as string[] | undefined) ??
        ([] as string[]),
    lampiran_keterangan:
        props.formData.lampiran_keterangan ??
        (props.formData.data.lampiran_keterangan as string | undefined) ??
        '',
    form_data: { ...props.formData.data } as Record<
        string,
        string | boolean | string[]
    >,
    return_to: props.returnTo ?? `/admin/surat/${props.surat.id}`,
});
function stripConsumedFormKeys() {
    consumedFormKeys.forEach((key) => {
        if (form.form_data[key] !== undefined) delete form.form_data[key];
    });
}
stripConsumedFormKeys();
// â”€â”€ Kepada Yth â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const kepadaSearch = ref('');
const kepadaManual = ref('');
const showDropdown = ref(false);
let blurTimer: ReturnType<typeof setTimeout> | null = null;
const filteredPresets = computed(() =>
    defaultPresets.filter(
        (p) =>
            p.toLowerCase().includes(kepadaSearch.value.toLowerCase()) &&
            !form.kepada_yth.includes(p),
    ),
);
function onSearchFocus() {
    showDropdown.value = true;
}
function onSearchBlur() {
    blurTimer = setTimeout(() => {
        showDropdown.value = false;
    }, 200);
}
function cancelBlur() {
    if (blurTimer) {
        clearTimeout(blurTimer);
        blurTimer = null;
    }
}
function addKepada(value: string) {
    const v = value.trim();
    if (v && !form.kepada_yth.includes(v)) form.kepada_yth.push(v);
    kepadaSearch.value = '';
    kepadaManual.value = '';
    showDropdown.value = false;
}
function removeKepada(i: number) {
    form.kepada_yth.splice(i, 1);
}
// â”€â”€ Repeatable â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function addRepeat(name: string) {
    const cur = form.form_data[name];
    if (Array.isArray(cur)) cur.push('');
    else form.form_data[name] = [''];
}
function removeRepeat(name: string, i: number) {
    const cur = form.form_data[name];
    if (Array.isArray(cur)) cur.splice(i, 1);
}
// â”€â”€ Submit â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function submit() {
    // Merge special fields back into form_data before sending
    const payload = buildPayload();
    // Use Inertia form transformation to send as PATCH
    form.transform(() => payload).patch(`/admin/surat/${props.surat.id}`);
}

function buildPayload() {
    return {
        jenis_surat_id: form.jenis_surat_id,
        keperluan: form.keperluan,
        perihal: form.perihal,
        form_data: {
            ...form.form_data,
            kepada_yth: form.kepada_yth,
            lampiran_keterangan: form.lampiran_keterangan,
        },
        return_to: form.return_to,
    };
}
function fieldError(name: string): string | undefined {
    return (
        (form.errors as any)[`form_data.${name}`] ??
        (form.errors as any)[`data.${name}`] ??
        (form.errors as any)[name]
    );
}
const requiredFields = computed(() =>
    (props.jenisSurat.field_config ?? []).filter(
        (f): f is FieldConfig => !!f && f.required,
    ),
);
const visibleFields = computed(() =>
    (props.jenisSurat.field_config ?? []).filter(
        (f): f is FieldConfig =>
            !!f && (f.mode_form_pemohon ?? 'editable') !== 'hidden',
    ),
);
const isPendingEdit = computed(() => props.surat.status === 'pending');
const alertTitle = computed(() =>
    isPendingEdit.value
        ? 'Surat baru menunggu validasi admin'
        : 'Surat perlu diperbarui',
);
const alertDescription = computed(() =>
    isPendingEdit.value
        ? 'Admin dapat melengkapi data yang dibutuhkan sebelum validasi diteruskan.'
        : `Surat ini sebelumnya ditolak. Setelah diperbarui, akan diteruskan kembali ke ${props.jenisSurat.approval_role?.nama ?? 'approval'} untuk persetujuan.`,
);
const submitLabel = computed(() =>
    isPendingEdit.value ? 'Simpan & Validasi' : 'Simpan & Teruskan',
);
</script>
<template>
    <AdminLayout
        title="Edit Surat"
        :subtitle="`${jenisSurat.category?.nama ?? 'Surat'} - ${jenisSurat.nama}`"
        active-menu="letters"
        :breadcrumbs="[
            { label: 'Dashboard', href: '/admin/dashboard' },
            { label: 'Edit Surat' },
        ]"
    >
        <Head :title="`Edit - ${jenisSurat.nama}`" />
        <!-- Header alert -->
        <div
            class="mb-6 flex items-start gap-3 rounded-2xl px-5 py-4"
            :class="
                isPendingEdit
                    ? 'border border-blue-200 bg-blue-50'
                    : 'border border-amber-200 bg-amber-50'
            "
        >
            <FileEdit
                class="mt-0.5 size-4 shrink-0"
                :class="isPendingEdit ? 'text-blue-600' : 'text-amber-600'"
            />
            <div>
                <p
                    class="text-sm font-semibold"
                    :class="isPendingEdit ? 'text-blue-800' : 'text-amber-800'"
                >
                    {{ alertTitle }}
                </p>
                <p
                    class="text-xs"
                    :class="isPendingEdit ? 'text-blue-700' : 'text-amber-700'"
                >
                    {{ alertDescription }}
                </p>
            </div>
        </div>
        <form
            class="grid gap-5 xl:grid-cols-[1fr_280px]"
            @submit.prevent="submit"
        >
            <div class="space-y-4">
                <!-- Info surat -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="flex items-center gap-3">
                        <div class="shrink-0 rounded-xl bg-blue-50 p-2.5">
                            <div class="size-5 rounded-lg bg-blue-600" />
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">
                                {{ jenisSurat.category?.nama }}
                            </p>
                            <h2 class="text-base font-bold text-slate-900">
                                {{ jenisSurat.nama }}
                            </h2>
                            <p
                                v-if="jenisSurat.deskripsi"
                                class="mt-0.5 text-xs text-slate-500"
                            >
                                {{ jenisSurat.deskripsi }}
                            </p>
                        </div>
                    </div>
                </div>
                <!-- Informasi Umum -->
                <div
                    class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5"
                >
                    <h3 class="text-sm font-semibold text-slate-800">
                        Informasi Umum
                    </h3>
                    <label class="block space-y-1.5">
                        <span class="text-xs font-medium text-slate-700"
                            >Keperluan Surat
                            <span class="text-red-500">*</span></span
                        >
                        <input
                            v-model="form.keperluan"
                            type="text"
                            required
                            class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                            :class="
                                form.errors.keperluan ? 'border-red-300' : ''
                            "
                            placeholder="Contoh: Untuk keperluan akademik"
                        />
                        <p
                            v-if="form.errors.keperluan"
                            class="text-xs text-red-500"
                        >
                            {{ form.errors.keperluan }}
                        </p>
                    </label>
                    <label class="block space-y-1.5">
                        <span class="text-xs font-medium text-slate-700"
                            >Perihal</span
                        >
                        <input
                            v-model="form.perihal"
                            type="text"
                            class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                            placeholder="Contoh: Permohonan Dispensasi"
                        />
                    </label>
                    <label class="block space-y-1.5">
                        <span class="text-xs font-medium text-slate-700"
                            >Keterangan Lampiran</span
                        >
                        <input
                            v-model="form.lampiran_keterangan"
                            type="text"
                            class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                            placeholder="Contoh: 1 (satu) lembar"
                        />
                    </label>
                </div>
                <!-- Kepada Yth -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 class="mb-1 text-sm font-semibold text-slate-800">
                        Kepada Yth.
                    </h3>
                    <p class="mb-3 text-xs text-slate-400">
                        Pilih dari daftar atau ketik manual. Bisa lebih dari
                        satu.
                    </p>
                    <div
                        v-if="form.kepada_yth.length > 0"
                        class="mb-3 flex flex-wrap gap-2"
                    >
                        <div
                            v-for="(k, idx) in form.kepada_yth"
                            :key="idx"
                            class="flex items-center gap-1.5 rounded-full border border-blue-200 bg-blue-50 px-3 py-1"
                        >
                            <span
                                class="text-xs font-medium text-blue-800"
                                >{{ k }}</span
                            >
                            <button
                                type="button"
                                class="fast-btn fast-btn-ghost fast-btn-icon text-blue-500 hover:text-blue-700"
                                @click="removeKepada(idx)"
                            >
                                <X class="size-3" />
                            </button>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <div class="relative min-w-[160px] flex-1">
                            <Search
                                class="pointer-events-none absolute top-1/2 left-3 size-3.5 -translate-y-1/2 text-slate-400"
                            />
                            <input
                                v-model="kepadaSearch"
                                type="text"
                                placeholder="Cari preset..."
                                class="h-9 w-full rounded-xl border border-slate-200 bg-white pr-3 pl-9 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                @focus="onSearchFocus"
                                @blur="onSearchBlur"
                                @keyup.enter.prevent="
                                    kepadaSearch && addKepada(kepadaSearch)
                                "
                            />
                            <div
                                v-if="
                                    showDropdown && filteredPresets.length > 0
                                "
                                class="absolute top-full left-0 z-10 mt-1 w-64 rounded-xl border border-slate-200 bg-white shadow-lg"
                            >
                                <button
                                    v-for="p in filteredPresets"
                                    :key="p"
                                    type="button"
                                    class="fast-btn fast-btn-outline flex w-full items-center gap-2 px-3 py-2 text-xs text-slate-700"
                                    @mousedown.prevent="
                                        cancelBlur();
                                        addKepada(p);
                                    "
                                >
                                    <Plus
                                        class="size-3 shrink-0 text-blue-500"
                                    />
                                    {{ p }}
                                </button>
                            </div>
                        </div>
                        <input
                            v-model="kepadaManual"
                            type="text"
                            placeholder="Ketik manual..."
                            class="h-9 w-44 rounded-xl border border-slate-200 bg-white px-3 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                            @keyup.enter.prevent="
                                kepadaManual && addKepada(kepadaManual)
                            "
                        />
                        <button
                            type="button"
                            class="fast-btn fast-btn-outline flex h-9 items-center gap-1 px-3 text-xs font-medium text-slate-700"
                            @click="kepadaManual && addKepada(kepadaManual)"
                        >
                            <Plus class="size-3.5" /> Tambah
                        </button>
                    </div>
                </div>
                <!-- Field dinamis -->
                <div
                    v-if="visibleFields.length > 0"
                    class="rounded-2xl border border-slate-200 bg-white p-5"
                >
                    <h3 class="mb-4 text-sm font-semibold text-slate-800">
                        Data Surat
                    </h3>
                    <div class="space-y-4">
                        <div v-for="field in visibleFields" :key="field.name">
                            <!-- Textarea -->
                            <label
                                v-if="field.type === 'textarea'"
                                class="block space-y-1.5"
                            >
                                <span
                                    class="text-xs font-medium text-slate-700"
                                >
                                    {{ field.label
                                    }}<span
                                        v-if="field.required"
                                        class="ml-0.5 text-red-500"
                                        >*</span
                                    >
                                </span>
                                <span
                                    v-if="field.sumber_data === 'data_kampus'"
                                    class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-semibold text-amber-700"
                                >
                                    Data oleh kampus
                                </span>
                                <textarea
                                    v-model="
                                        form.form_data[field.name] as string
                                    "
                                    rows="4"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                    :class="
                                        fieldError(field.name)
                                            ? 'border-red-300'
                                            : ''
                                    "
                                    :placeholder="field.placeholder ?? ''"
                                />
                                <p
                                    v-if="field.help"
                                    class="text-xs text-slate-400"
                                >
                                    {{ field.help }}
                                </p>
                                <p
                                    v-if="fieldError(field.name)"
                                    class="text-xs text-red-500"
                                >
                                    {{ fieldError(field.name) }}
                                </p>
                            </label>
                            <!-- Select -->
                            <label
                                v-else-if="field.type === 'select'"
                                class="block space-y-1.5"
                            >
                                <span
                                    class="text-xs font-medium text-slate-700"
                                >
                                    {{ field.label
                                    }}<span
                                        v-if="field.required"
                                        class="ml-0.5 text-red-500"
                                        >*</span
                                    >
                                </span>
                                <span
                                    v-if="field.sumber_data === 'data_kampus'"
                                    class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-semibold text-amber-700"
                                >
                                    Data oleh kampus
                                </span>
                                <select
                                    v-model="
                                        form.form_data[field.name] as string
                                    "
                                    class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none focus:border-blue-400"
                                    :class="
                                        fieldError(field.name)
                                            ? 'border-red-300'
                                            : ''
                                    "
                                >
                                    <option value="">- Pilih -</option>
                                    <option
                                        v-for="opt in field.options"
                                        :key="opt.value"
                                        :value="opt.value"
                                    >
                                        {{ opt.label }}
                                    </option>
                                </select>
                                <p
                                    v-if="fieldError(field.name)"
                                    class="text-xs text-red-500"
                                >
                                    {{ fieldError(field.name) }}
                                </p>
                            </label>
                            <!-- Date -->
                            <label
                                v-else-if="field.type === 'date'"
                                class="block space-y-1.5"
                            >
                                <span
                                    class="text-xs font-medium text-slate-700"
                                >
                                    {{ field.label
                                    }}<span
                                        v-if="field.required"
                                        class="ml-0.5 text-red-500"
                                        >*</span
                                    >
                                </span>
                                <span
                                    v-if="field.sumber_data === 'data_kampus'"
                                    class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-semibold text-amber-700"
                                >
                                    Data oleh kampus
                                </span>
                                <input
                                    v-model="
                                        form.form_data[field.name] as string
                                    "
                                    type="date"
                                    class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none focus:border-blue-400"
                                    :class="
                                        fieldError(field.name)
                                            ? 'border-red-300'
                                            : ''
                                    "
                                />
                                <p
                                    v-if="fieldError(field.name)"
                                    class="text-xs text-red-500"
                                >
                                    {{ fieldError(field.name) }}
                                </p>
                            </label>
                            <!-- Number -->
                            <label
                                v-else-if="field.type === 'number'"
                                class="block space-y-1.5"
                            >
                                <span
                                    class="text-xs font-medium text-slate-700"
                                >
                                    {{ field.label
                                    }}<span
                                        v-if="field.required"
                                        class="ml-0.5 text-red-500"
                                        >*</span
                                    >
                                </span>
                                <span
                                    v-if="field.sumber_data === 'data_kampus'"
                                    class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-semibold text-amber-700"
                                >
                                    Data oleh kampus
                                </span>
                                <input
                                    v-model="
                                        form.form_data[field.name] as string
                                    "
                                    type="number"
                                    class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                    :class="
                                        fieldError(field.name)
                                            ? 'border-red-300'
                                            : ''
                                    "
                                    :placeholder="field.placeholder ?? ''"
                                />
                                <p
                                    v-if="fieldError(field.name)"
                                    class="text-xs text-red-500"
                                >
                                    {{ fieldError(field.name) }}
                                </p>
                            </label>
                            <!-- Checkbox -->
                            <label
                                v-else-if="field.type === 'checkbox'"
                                class="flex cursor-pointer items-center gap-2"
                            >
                                <input
                                    v-model="
                                        form.form_data[field.name] as boolean
                                    "
                                    type="checkbox"
                                    class="rounded border-slate-300 text-blue-600"
                                />
                                <span class="text-sm text-slate-700">{{
                                    field.label
                                }}</span>
                            </label>
                            <!-- Repeatable -->
                            <div
                                v-else-if="
                                    field.type === 'repeatable' ||
                                    field.repeatable
                                "
                                class="space-y-2"
                            >
                                <span
                                    class="text-xs font-medium text-slate-700"
                                >
                                    {{ field.label
                                    }}<span
                                        v-if="field.required"
                                        class="ml-0.5 text-red-500"
                                        >*</span
                                    >
                                </span>
                                <span
                                    v-if="field.sumber_data === 'data_kampus'"
                                    class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-semibold text-amber-700"
                                >
                                    Data oleh kampus
                                </span>
                                <div
                                    v-for="(_, idx) in form.form_data[
                                        field.name
                                    ] as string[]"
                                    :key="idx"
                                    class="flex gap-2"
                                >
                                    <input
                                        v-model="
                                            (
                                                form.form_data[
                                                    field.name
                                                ] as string[]
                                            )[idx]
                                        "
                                        type="text"
                                        class="h-9 flex-1 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                        :placeholder="`${field.item_label ?? 'Item'} ${idx + 1}`"
                                    />
                                    <button
                                        type="button"
                                        class="text-slate-400 hover:text-red-500"
                                        @click="removeRepeat(field.name, idx)"
                                    >
                                        <X class="size-4" />
                                    </button>
                                </div>
                                <button
                                    type="button"
                                    class="flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700"
                                    @click="addRepeat(field.name)"
                                >
                                    <Plus class="size-3.5" />
                                    {{ field.add_label ?? 'Tambah Item' }}
                                </button>
                            </div>
                            <!-- Text (default) -->
                            <label v-else class="block space-y-1.5">
                                <span
                                    class="text-xs font-medium text-slate-700"
                                >
                                    {{ field.label
                                    }}<span
                                        v-if="field.required"
                                        class="ml-0.5 text-red-500"
                                        >*</span
                                    >
                                </span>
                                <span
                                    v-if="field.sumber_data === 'data_kampus'"
                                    class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-semibold text-amber-700"
                                >
                                    Data oleh kampus
                                </span>
                                <input
                                    v-model="
                                        form.form_data[field.name] as string
                                    "
                                    type="text"
                                    class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                    :class="
                                        fieldError(field.name)
                                            ? 'border-red-300'
                                            : ''
                                    "
                                    :placeholder="field.placeholder ?? ''"
                                />
                                <p
                                    v-if="field.help"
                                    class="text-xs text-slate-400"
                                >
                                    {{ field.help }}
                                </p>
                                <p
                                    v-if="fieldError(field.name)"
                                    class="text-xs text-red-500"
                                >
                                    {{ fieldError(field.name) }}
                                </p>
                            </label>
                        </div>
                    </div>
                </div>
                <!-- Navigasi -->
                <div
                    class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-5 py-4"
                >
                    <a
                        :href="returnTo"
                        class="fast-btn fast-btn-outline items-center gap-1.5 px-4 py-2 text-sm font-medium text-slate-700"
                    >
                        <ChevronLeft class="size-4" /> Kembali
                    </a>
                    <button
                        type="submit"
                        class="fast-btn fast-btn-primary flex items-center gap-1.5 px-5 py-2 text-sm font-semibold"
                        :disabled="form.processing"
                    >
                        {{
                            form.processing
                                ? 'Menyimpan...'
                                : submitLabel
                        }}
                        <Save v-if="!form.processing" class="size-4" />
                    </button>
                </div>
            </div>
            <!-- Panel kanan -->
            <div class="space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 class="mb-3 text-sm font-semibold text-slate-800">
                        Info Surat
                    </h3>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Jenis</span>
                            <span
                                class="max-w-[140px] text-right font-medium text-slate-700"
                                >{{ jenisSurat.nama }}</span
                            >
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Approval</span>
                            <span
                                class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                :class="
                                    jenisSurat.approval_role?.id
                                        ? 'bg-amber-50 text-amber-700'
                                        : 'bg-slate-100 text-slate-700'
                                "
                            >
                                {{
                                    jenisSurat.approval_role?.id
                                        ? jenisSurat.approval_role.nama
                                        : 'Langsung Selesai'
                                }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 class="mb-3 text-sm font-semibold text-slate-800">
                        Field Wajib
                    </h3>
                    <div class="space-y-1.5">
                        <div
                            v-for="field in requiredFields"
                            :key="field.name"
                            class="flex items-center gap-2"
                        >
                            <span
                                class="size-1.5 shrink-0 rounded-full bg-red-400"
                            />
                            <span class="text-xs text-slate-600">{{
                                field.label
                            }}</span>
                        </div>
                        <p v-if="requiredFields.length === 0" class="text-xs text-slate-400">
                            Tidak ada field wajib
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>
