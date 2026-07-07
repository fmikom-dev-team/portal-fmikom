<script setup lang="ts">
import AdminLayout from '@/layouts/Modules/Fast/AdminLayout.vue';
import { useFastPermissions } from '@/composables/modules/fast/useFastPermissions';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import LetterStepIndicator from '@/components/Modules/Fast/Admin/LetterStepIndicator.vue';
import RecipientSelector from '@/components/Modules/Fast/Admin/RecipientSelector.vue';
import { ChevronRight, ChevronLeft, Plus, X } from 'lucide-vue-next';
type AttachmentColumn = { key: string; label: string; align: 'left' | 'center' | 'right'; bold: boolean };
type AttachmentRow = Record<string, string>;
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
    requires_subject_user?: boolean;
    field_config: FieldConfig[];
};
type FormData = {
    jenis_surat_id: number;
    subject_name?: string | null;
    keperluan: string;
    perihal?: string;
    kepada_yth?: string[];
    lampiran_keterangan?: string;
    lampiran_judul?: string;
    lampiran_orientation?: string;
    lampiran_judul_align?: string;
    lampiran_judul_bold?: string;
    lampiran_label_no?: string;
    lampiran_label_nama?: string;
    lampiran_label_nim?: string;
    lampiran_label_prodi?: string;
    lampiran_mode?: string;
    lampiran_mahasiswa?: Array<{ nama: string; nim: string; prodi: string }>;
    lampiran_columns?: AttachmentColumn[];
    lampiran_rows?: AttachmentRow[];
    data: Record<string, unknown>;
};
const props = defineProps<{
    jenisSurat: JenisSurat;
    formData: FormData;
}>();
const form = useForm({
    jenis_surat_id: props.formData.jenis_surat_id,
    subject_name: props.formData.subject_name ?? '',
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
    lampiran_judul:
        props.formData.lampiran_judul ??
        (props.formData.data.lampiran_judul as string | undefined) ??
        '',
    lampiran_orientation:
        props.formData.lampiran_orientation ??
        (props.formData.data.lampiran_orientation as string | undefined) ??
        'portrait',
    lampiran_judul_align:
        props.formData.lampiran_judul_align ??
        (props.formData.data.lampiran_judul_align as string | undefined) ??
        'center',
    lampiran_judul_bold:
        props.formData.lampiran_judul_bold ??
        (props.formData.data.lampiran_judul_bold as string | undefined) ??
        '1',
    lampiran_label_no:
        props.formData.lampiran_label_no ??
        (props.formData.data.lampiran_label_no as string | undefined) ??
        'No',
    lampiran_label_nama:
        props.formData.lampiran_label_nama ??
        (props.formData.data.lampiran_label_nama as string | undefined) ??
        'Nama Mahasiswa',
    lampiran_label_nim:
        props.formData.lampiran_label_nim ??
        (props.formData.data.lampiran_label_nim as string | undefined) ??
        'NIM',
    lampiran_label_prodi:
        props.formData.lampiran_label_prodi ??
        (props.formData.data.lampiran_label_prodi as string | undefined) ??
        'Program Studi',
    lampiran_mode:
        props.formData.lampiran_mode ??
        (props.formData.data.lampiran_mode as string | undefined) ??
        'none',
    lampiran_mahasiswa:
        props.formData.lampiran_mahasiswa ??
        (props.formData.data.lampiran_mahasiswa as
            | Array<{ nama: string; nim: string; prodi: string }>
            | undefined) ??
        [],
    lampiran_columns:
        props.formData.lampiran_columns ??
        (props.formData.data.lampiran_columns as AttachmentColumn[] | undefined) ??
        [
            { key: 'col_1', label: 'No', align: 'center', bold: true },
            { key: 'col_2', label: 'Nama Mahasiswa', align: 'left', bold: true },
            { key: 'col_3', label: 'NIM', align: 'center', bold: true },
            { key: 'col_4', label: 'Program Studi', align: 'left', bold: true },
        ],
    lampiran_rows:
        props.formData.lampiran_rows ??
        (props.formData.data.lampiran_rows as AttachmentRow[] | undefined) ??
        [],
    form_data: { ...props.formData.data } as Record<
        string,
        unknown
    >,
});
const { can } = useFastPermissions();
function addRepeat(name: string) {
    const cur = form.form_data[name];
    if (Array.isArray(cur)) cur.push('');
    else form.form_data[name] = [''];
}
function removeRepeat(name: string, i: number) {
    const cur = form.form_data[name];
    if (Array.isArray(cur)) cur.splice(i, 1);
}
function addStudentAttachmentRow() {
    form.lampiran_mode = 'student_list';
    if (!form.lampiran_columns.length) {
        addAttachmentColumn();
    }
    const row: AttachmentRow = {};
    form.lampiran_columns.forEach((column) => {
        row[column.key] = '';
    });
    form.lampiran_rows.push(row);
}
function removeStudentAttachmentRow(index: number) {
    form.lampiran_rows.splice(index, 1);
    if (!form.lampiran_rows.length) {
        form.lampiran_mode = 'none';
    }
}
function addAttachmentColumn() {
    form.lampiran_mode = 'student_list';
    const nextKey = `col_${Date.now()}_${form.lampiran_columns.length + 1}`;
    form.lampiran_columns.push({
        key: nextKey,
        label: `Kolom ${form.lampiran_columns.length + 1}`,
        align: 'left',
        bold: true,
    });
    form.lampiran_rows.forEach((row) => {
        row[nextKey] = '';
    });
}
function removeAttachmentColumn(index: number) {
    const column = form.lampiran_columns[index];
    if (!column) return;
    form.lampiran_columns.splice(index, 1);
    form.lampiran_rows.forEach((row) => {
        delete row[column.key];
    });
}
function submit() {
    form.post('/admin/surat/preview');
}
function fieldError(name: string): string | undefined {
    return (
        (form.errors as any)[`form_data.${name}`] ??
        (form.errors as any)[`data.${name}`] ??
        (form.errors as any)[name]
    );
}
const hasRequiredFields = computed(
    () =>
        (props.jenisSurat.field_config ?? []).filter(
            (f) => f.required && f.type !== 'recipient',
        ).length,
);
const visibleFields = computed(() =>
    (props.jenisSurat.field_config ?? []).filter(
        (f): f is FieldConfig =>
            !!f && (f.mode_form_pemohon ?? 'editable') !== 'hidden',
    ),
);
const requiresSubjectUser = computed(() => !!props.jenisSurat.requires_subject_user);
</script>
<template>
    <AdminLayout
        title="Buat Surat"
        :subtitle="`${jenisSurat.category?.nama ?? 'Surat'} - ${jenisSurat.nama}`"
        active-menu="letters.create"
        :breadcrumbs="[
            { label: 'Buat Surat Keluar', href: '/admin/surat/create' },
            { label: 'Isi Data Surat' },
        ]"
    >
        <Head :title="`Form - ${jenisSurat.nama}`" />
        <div
            class="mb-6 rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-white p-6"
        >
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <h2 class="mt-1 text-xl font-bold text-slate-900">
                        {{
                            requiresSubjectUser
                                ? 'Buat Surat Atas Nama Subjek'
                                : 'Buat Surat Institusi'
                        }}
                    </h2>
                    <p class="mt-1 max-w-lg text-sm text-slate-500">
                        {{
                            requiresSubjectUser
                                ? 'Admin mengisi data surat untuk subjek yang dipilih. Pastikan identitas subjek, isi surat, dan field wajib sudah lengkap sebelum lanjut ke preview.'
                                : 'Admin mengisi data surat institusi atas nama fakultas atau kampus. Subjek pengguna tidak wajib dipilih kecuali memang surat perlu mewakili individu tertentu.'
                        }}
                    </p>
                </div>
                <div class="hidden sm:block">
                    <LetterStepIndicator :current-step="2" />
                </div>
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
                <!-- Subjek surat -->
                <div
                    class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5"
                >
                    <h3 class="text-sm font-semibold text-slate-800">
                        Subjek Surat
                    </h3>
                    <label class="block space-y-1.5">
                        <span class="text-xs font-medium text-slate-700"
                            >Atas Nama
                            <span v-if="requiresSubjectUser" class="text-red-500">*</span></span
                        >
                        <input
                            v-model="form.subject_name"
                            type="text"
                            class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                            :class="form.errors.subject_name ? 'border-red-300' : ''"
                            placeholder="Contoh: Ahmad Fauzi"
                        />
                        <p
                            v-if="form.errors.subject_name"
                            class="text-xs text-red-500"
                        >
                            {{ form.errors.subject_name }}
                        </p>
                        <p class="text-xs text-slate-400">
                            {{
                                requiresSubjectUser
                                    ? 'Isi nama subjek surat secara manual. Nilai ini akan dipakai sebagai identitas atas nama pada dokumen.'
                                    : 'Kosongkan jika surat diterbitkan sebagai surat institusi. Isi bila surat perlu mewakili individu tertentu.'
                            }}
                        </p>
                    </label>
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
                            >Tujuan / Keperluan Surat
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
                            placeholder="Contoh: Pengajuan cuti akademik"
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
                            >Lampiran</span
                        >
                        <input
                            v-model="form.lampiran_keterangan"
                            type="text"
                            class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                            placeholder="Contoh: 1 (satu) lembar"
                        />
                        <p class="text-xs text-slate-400">
                            Isi keterangan lampiran yang tertulis di surat utama agar ikut tersusun dalam bundle PDF final.
                        </p>
                    </label>
                </div>
                <RecipientSelector
                    v-model="form.kepada_yth"
                />
                <div
                    class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">
                                Lampiran Surat
                            </h3>
                            <p class="mt-1 text-xs text-slate-400">
                                Gunakan saat surat keluar membutuhkan daftar mahasiswa sebagai bagian dari bundle final.
                            </p>
                        </div>
                        <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                            <input
                                v-model="form.lampiran_mode"
                                type="checkbox"
                                true-value="student_list"
                                false-value="none"
                                class="rounded border-slate-300 text-blue-600"
                            />
                            Lampiran daftar mahasiswa
                        </label>
                    </div>
                    <div
                        v-if="form.lampiran_mode === 'student_list'"
                        class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50/70 p-4"
                    >
                        <label class="block space-y-1.5">
                            <span class="text-xs font-medium text-slate-700"
                                >Judul Konten Lampiran</span
                            >
                            <textarea
                                v-model="form.lampiran_judul"
                                rows="4"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400"
                                placeholder="Contoh: DAFTAR NAMA MAHASISWA PESERTA RESPONSI PROGRAM KERJA PRAKTEK KERJA LAPANGAN&#10;FAKULTAS MATEMATIKA DAN ILMU KOMPUTER&#10;UNIVERSITAS NAHDLATUL ULAMA AL GHAZALI [UNUGHA] CILACAP&#10;TAHUN 2025"
                            />
                            <p class="text-xs text-slate-500">
                                Judul ini tampil di tengah di atas tabel lampiran. Satu baris satu kalimat.
                            </p>
                        </label>
                        <div class="grid gap-3 md:grid-cols-[180px_180px_auto]">
                            <label class="block space-y-1.5">
                                <span class="text-xs font-medium text-slate-700">Orientasi Lampiran</span>
                                <select
                                    v-model="form.lampiran_orientation"
                                    class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none focus:border-blue-400"
                                >
                                    <option value="portrait">Potret</option>
                                    <option value="landscape">Landscape</option>
                                </select>
                            </label>
                            <label class="block space-y-1.5">
                                <span class="text-xs font-medium text-slate-700">Posisi Judul</span>
                                <select
                                    v-model="form.lampiran_judul_align"
                                    class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none focus:border-blue-400"
                                >
                                    <option value="left">Kiri</option>
                                    <option value="center">Tengah</option>
                                    <option value="right">Kanan</option>
                                </select>
                            </label>
                            <label class="inline-flex items-center gap-2 pt-7 text-sm font-medium text-slate-700">
                                <input
                                    v-model="form.lampiran_judul_bold"
                                    type="checkbox"
                                    true-value="1"
                                    false-value="0"
                                    class="rounded border-slate-300 text-blue-600"
                                />
                                Judul tebal
                            </label>
                        </div>
                        <p class="text-xs text-slate-500">
                            Gunakan `landscape` jika tabel lampiran punya banyak kolom atau isi kolom cukup lebar.
                        </p>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-xs font-medium text-slate-700">Kolom Tabel</p>
                                <button
                                    type="button"
                                    class="fast-btn fast-btn-outline flex items-center gap-1 px-3 py-1.5 text-xs text-blue-600"
                                    @click="addAttachmentColumn"
                                >
                                    <Plus class="size-3.5" />
                                    Tambah Kolom
                                </button>
                            </div>
                            <div
                                v-for="(column, index) in form.lampiran_columns"
                                :key="column.key"
                                class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-3 md:grid-cols-[1fr_140px_110px_44px]"
                            >
                                <label class="block space-y-1.5">
                                    <span class="text-xs font-medium text-slate-700">Label Kolom {{ index + 1 }}</span>
                                    <input
                                        v-model="column.label"
                                        type="text"
                                        class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none focus:border-blue-400"
                                        :placeholder="`Kolom ${index + 1}`"
                                    />
                                </label>
                                <label class="block space-y-1.5">
                                    <span class="text-xs font-medium text-slate-700">Alignment</span>
                                    <select
                                        v-model="column.align"
                                        class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none focus:border-blue-400"
                                    >
                                        <option value="left">Kiri</option>
                                        <option value="center">Tengah</option>
                                        <option value="right">Kanan</option>
                                    </select>
                                </label>
                                <label class="inline-flex items-center gap-2 pt-7 text-sm font-medium text-slate-700">
                                    <input
                                        v-model="column.bold"
                                        type="checkbox"
                                        class="rounded border-slate-300 text-blue-600"
                                    />
                                    Bold
                                </label>
                                <div class="pt-7 text-right">
                                    <button
                                        type="button"
                                        class="fast-btn fast-btn-ghost fast-btn-icon text-slate-400 hover:text-red-500"
                                        @click="removeAttachmentColumn(index)"
                                    >
                                        <X class="size-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <thead>
                                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        <th
                                            v-for="column in form.lampiran_columns"
                                            :key="column.key"
                                            class="px-3 py-2"
                                            :class="{
                                                'text-left': column.align === 'left',
                                                'text-center': column.align === 'center',
                                                'text-right': column.align === 'right',
                                                'font-bold': column.bold,
                                            }"
                                        >
                                            {{ column.label || 'Kolom' }}
                                        </th>
                                        <th class="w-12 px-3 py-2"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    <tr
                                        v-for="(row, index) in form.lampiran_rows"
                                        :key="index"
                                    >
                                        <td
                                            v-for="column in form.lampiran_columns"
                                            :key="column.key"
                                            class="px-3 py-2"
                                        >
                                            <input
                                                v-model="row[column.key]"
                                                type="text"
                                                class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none focus:border-blue-400"
                                                :placeholder="column.label || 'Isi nilai'"
                                            />
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <button
                                                type="button"
                                                class="fast-btn fast-btn-ghost fast-btn-icon text-slate-400 hover:text-red-500"
                                                @click="removeStudentAttachmentRow(index)"
                                            >
                                                <X class="size-4" />
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs text-slate-500">
                                Anda bisa menambah, menghapus, dan mengatur alignment tiap kolom tabel lampiran.
                            </p>
                            <button
                                type="button"
                                class="fast-btn fast-btn-outline flex items-center gap-1 px-3 py-1.5 text-xs text-blue-600"
                                @click="addStudentAttachmentRow"
                            >
                                <Plus class="size-3.5" />
                                Tambah Baris
                            </button>
                        </div>
                        <p v-if="form.errors.lampiran_rows" class="text-xs text-red-500">{{ form.errors.lampiran_rows }}</p>
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
                    <p class="text-xs text-slate-400">
                        Lengkapi field template yang akan muncul pada dokumen
                        surat untuk subjek terpilih.
                    </p>
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
                                        class="fast-btn fast-btn-ghost fast-btn-icon text-slate-400 hover:text-red-500"
                                        @click="removeRepeat(field.name, idx)"
                                    >
                                        <X class="size-4" />
                                    </button>
                                </div>
                                <button
                                    type="button"
                                    class="fast-btn fast-btn-outline flex items-center gap-1 px-3 py-1.5 text-xs text-blue-600"
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
                        href="/admin/surat/create"
                        class="fast-btn fast-btn-outline flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-slate-700"
                    >
                        <ChevronLeft class="size-4" /> Kembali Pilih Jenis
                    </a>
                    <button
                        v-if="can('fast.admin.surat.create')"
                        type="submit"
                        class="fast-btn fast-btn-primary flex items-center gap-1.5 px-5 py-2 text-sm font-semibold"
                        :disabled="form.processing"
                    >
                        {{
                            form.processing
                                ? 'Memproses...'
                                : 'Lanjut ke Preview'
                        }}
                        <ChevronRight v-if="!form.processing" class="size-4" />
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
                        <div class="flex justify-between gap-3">
                            <span class="text-slate-400">Atas Nama</span>
                            <span
                                class="max-w-[140px] text-right font-medium text-slate-700"
                                >{{
                                    form.subject_name || 'Belum diisi'
                                }}</span
                            >
                        </div>
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
                                        : 'bg-blue-50 text-blue-700'
                                "
                            >
                                {{
                                    jenisSurat.approval_role?.id
                                        ? jenisSurat.approval_role.nama
                                        : 'Langsung Selesai'
                                }}
                            </span>
                        </div>
                        <div class="flex justify-between gap-3">
                            <span class="text-slate-400">Peran Anda</span>
                            <span
                                class="max-w-[140px] text-right font-medium text-slate-700"
                            >
                                Admin pembuat surat
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
                            v-for="field in jenisSurat.field_config.filter(
                                (f) => f.required,
                            )"
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
                        <p
                            v-if="
                                jenisSurat.field_config.filter(
                                    (f) => f.required,
                                ).length === 0
                            "
                            class="text-xs text-slate-400"
                        >
                            Tidak ada field wajib
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>
