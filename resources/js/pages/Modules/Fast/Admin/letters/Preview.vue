<script setup lang="ts">
// resources/js/pages/Modules/Fast/Admin/letters/Preview.vue
import AdminLayout from '@/layouts/Modules/Fast/AdminLayout.vue';
import { useFastPermissions } from '@/composables/modules/fast/useFastPermissions';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import LetterStepIndicator from '@/components/Modules/Fast/Admin/LetterStepIndicator.vue';
import {
    ChevronLeft,
    Send,
    Eye,
    Maximize2,
    Minimize2,
} from 'lucide-vue-next';
type AttachmentColumn = { key: string; label: string; align: 'left' | 'center' | 'right'; bold: boolean };
type AttachmentRow = Record<string, string>;
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
    template?: { id?: number | null; name?: string | null } | null;
    requires_subject_user?: boolean;
    letter_mode?: 'personal' | 'institution';
    letter_mode_label?: string | null;
    field_config: Array<{
        name: string;
        label: string;
        type: string;
        required: boolean;
    }>;
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
type SubjectSummary = {
    id: number;
    name: string;
    email?: string | null;
    nomor_induk?: string | null;
    program_studi?: string | null;
};
const props = defineProps<{
    jenisSurat: JenisSurat;
    formData: FormData;
    subjectSummary?: SubjectSummary | null;
    renderedHtml: string;
    renderedAttachmentHtml?: string | null;
    previewDocumentUrl: string;
}>();
const form = useForm({
    jenis_surat_id: props.formData.jenis_surat_id,
    subject_name: props.formData.subject_name ?? '',
    keperluan: props.formData.keperluan,
    perihal: props.formData.perihal ?? '',
    kepada_yth: props.formData.kepada_yth ?? [],
    lampiran_keterangan: props.formData.lampiran_keterangan ?? '',
    lampiran_judul: props.formData.lampiran_judul ?? '',
    lampiran_orientation: props.formData.lampiran_orientation ?? 'portrait',
    lampiran_judul_align: props.formData.lampiran_judul_align ?? 'center',
    lampiran_judul_bold: props.formData.lampiran_judul_bold ?? '1',
    lampiran_label_no: props.formData.lampiran_label_no ?? 'No',
    lampiran_label_nama: props.formData.lampiran_label_nama ?? 'Nama Mahasiswa',
    lampiran_label_nim: props.formData.lampiran_label_nim ?? 'NIM',
    lampiran_label_prodi: props.formData.lampiran_label_prodi ?? 'Program Studi',
    lampiran_mode: props.formData.lampiran_mode ?? 'none',
    lampiran_mahasiswa: props.formData.lampiran_mahasiswa ?? [],
    lampiran_columns: props.formData.lampiran_columns ?? [],
    lampiran_rows: props.formData.lampiran_rows ?? [],
    form_data: props.formData.data,
});
const { can } = useFastPermissions();
const fullPreview = ref(false);
const previewDocumentUrl = computed(() => props.previewDocumentUrl);
const hasAttachmentPreview = computed(
    () => props.formData.lampiran_mode === 'student_list' && !!props.renderedAttachmentHtml,
);
const attachmentOrientationLabel = computed(() =>
    (props.formData.lampiran_orientation ?? 'portrait') === 'landscape' ? 'Landscape' : 'Potret',
);
const requiresSubjectUser = computed(() => !!props.jenisSurat.requires_subject_user);
const isInstitutionLetter = computed(
    () => !requiresSubjectUser.value || props.jenisSurat.letter_mode === 'institution',
);
const workflowStatusLabel = computed(() =>
    needsApproval
        ? `Menunggu approval${props.jenisSurat.approval_role?.nama ? ` ${props.jenisSurat.approval_role.nama}` : ''}`
        : 'Siap digenerate langsung',
);
const workflowStatusDescription = computed(() =>
    needsApproval
        ? 'Surat akan disimpan lalu diteruskan ke alur approval sebelum PDF final dan QR validasi dibuat.'
        : 'Surat langsung dibuat, PDF final digenerate, dan QR validasi disiapkan setelah submit.',
);
function submit() {
    form.post('/admin/surat/store');
}
function goBack() {
    router.visit(`/admin/surat/form/${props.jenisSurat.id}?resume_preview=1`);
}
const needsApproval = !!props.jenisSurat.approval_role?.id;
const steps = needsApproval
    ? [
          'Surat disimpan admin',
          'Pemeriksaan admin',
          `Approval ${props.jenisSurat.approval_role?.nama ?? ''}`,
          'Generate PDF & QR',
      ]
    : ['Surat dibuat', 'Generate PDF & QR', 'Selesai'];
</script>
<template>
    <AdminLayout
        title="Preview Surat"
        :subtitle="`${jenisSurat.category?.nama ?? 'Surat'} - ${jenisSurat.nama}`"
        active-menu="letters.create"
        :breadcrumbs="[
            { label: 'Buat Surat Keluar', href: '/admin/surat/create' },
            { label: 'Isi Data Surat', href: `/admin/surat/form/${jenisSurat.id}` },
            { label: 'Preview' },
        ]"
    >
        <Head :title="`Preview - ${jenisSurat.nama}`" />
        <div
            class="mb-6 rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-white p-6"
        >
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div
                        v-if="isInstitutionLetter"
                        class="mb-3 inline-flex rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700"
                    >
                        {{ props.jenisSurat.letter_mode_label || 'Surat Institusi' }}
                    </div>
                    <h2 class="mt-1 text-xl font-bold text-slate-900">
                        Preview Surat Admin
                    </h2>
                    <p class="mt-1 max-w-lg text-sm text-slate-500">
                        {{
                            requiresSubjectUser
                                ? 'Tinjau dokumen yang akan dibuat admin atas nama subjek. Pastikan identitas subjek, isi surat, dan alur approval sudah sesuai sebelum disimpan.'
                                : 'Tinjau dokumen surat institusi sebelum disimpan. Pastikan tujuan surat, isi, dan alur approval sudah sesuai.'
                        }}
                    </p>
                </div>
                <div class="hidden sm:block">
                    <LetterStepIndicator :current-step="3" />
                </div>
            </div>
        </div>
        <div class="grid gap-5 xl:grid-cols-[1fr_280px]">
            <!-- Preview dokumen -->
            <div class="space-y-4">
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white"
                >
                    <!-- Toolbar -->
                    <div
                        class="flex items-center justify-between border-b border-slate-100 px-5 py-3"
                    >
                        <div class="flex items-center gap-2">
                            <Eye class="size-4 text-slate-400" />
                            <h3 class="text-sm font-semibold text-slate-800">
                                Preview Dokumen
                            </h3>
                        </div>
                        <button
                            type="button"
                            class="fast-btn fast-btn-outline flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs text-slate-600"
                            @click="fullPreview = !fullPreview"
                        >
                            <component
                                :is="fullPreview ? Minimize2 : Maximize2"
                                class="size-3.5"
                            />
                            {{ fullPreview ? 'Ringkas' : 'Penuh' }}
                        </button>
                    </div>
                    <!-- Iframe -->
                    <div
                        class="transition-all duration-200"
                        :class="fullPreview ? 'h-[800px]' : 'h-[520px]'"
                    >
                        <iframe
                            class="h-full w-full border-0"
                            :src="previewDocumentUrl"
                            title="Preview surat"
                        />
                    </div>
                </div>
                <div
                    v-if="hasAttachmentPreview"
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white"
                >
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">
                                Preview Lampiran
                            </h3>
                            <p class="text-xs text-slate-400">
                                Daftar mahasiswa akan digenerate bersama surat utama dalam bundle PDF final.
                            </p>
                        </div>
                        <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700">
                            {{ attachmentOrientationLabel }} • {{ formData.lampiran_rows?.length ?? 0 }} baris
                        </span>
                    </div>
                    <div :class="fullPreview ? 'h-[760px]' : ((formData.lampiran_orientation ?? 'portrait') === 'landscape' ? 'h-[520px]' : 'h-[420px]')">
                        <iframe
                            class="h-full w-full border-0"
                            :srcdoc="renderedAttachmentHtml ?? ''"
                            title="Preview lampiran surat"
                        />
                    </div>
                </div>
                <!-- Navigasi -->
                <div
                    class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-5 py-4"
                >
                    <button
                        type="button"
                        class="fast-btn fast-btn-outline flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-slate-600"
                        @click="goBack"
                    >
                        <ChevronLeft class="size-4" /> Kembali Edit
                    </button>
                    <button
                        v-if="can('fast.admin.surat.create')"
                        type="button"
                        class="fast-btn fast-btn-primary flex items-center gap-1.5 px-5 py-2 text-sm font-semibold"
                        :disabled="form.processing"
                        @click="submit"
                    >
                        <Send v-if="!form.processing" class="size-4" />
                        {{
                            form.processing
                                ? 'Menyimpan...'
                                : needsApproval
                                  ? 'Simpan & Kirim ke Approval'
                                  : 'Buat & Generate Surat'
                        }}
                    </button>
                </div>
            </div>
            <!-- Panel kanan -->
            <div class="space-y-4">
                <!-- Ringkasan -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 class="mb-3 text-sm font-semibold text-slate-800">
                        Ringkasan
                    </h3>
                    <div class="space-y-3 text-xs">
                        <div v-if="subjectSummary">
                            <p class="text-slate-400">
                                {{ requiresSubjectUser ? 'Atas Nama' : 'Subjek Opsional' }}
                            </p>
                            <p class="mt-0.5 font-semibold text-slate-800">
                                {{ subjectSummary.name }}
                            </p>
                            <p
                                v-if="
                                    subjectSummary.program_studi ||
                                    subjectSummary.nomor_induk
                                "
                                class="mt-0.5 text-slate-500"
                            >
                                {{
                                    [subjectSummary.program_studi, subjectSummary.nomor_induk]
                                        .filter(Boolean)
                                        .join(' / ')
                                }}
                            </p>
                        </div>
                        <div v-else-if="!requiresSubjectUser">
                            <p class="text-slate-400">Mode Surat</p>
                            <p class="mt-0.5 font-semibold text-slate-800">
                                Surat institusi
                            </p>
                        </div>
                        <div>
                            <p class="text-slate-400">Peran Admin</p>
                            <p class="mt-0.5 font-semibold text-slate-800">
                                Pembuat surat
                            </p>
                        </div>
                        <div>
                            <p class="text-slate-400">Jenis Surat</p>
                            <p class="mt-0.5 font-semibold text-slate-800">
                                {{ jenisSurat.nama }}
                            </p>
                        </div>
                        <div>
                            <p class="text-slate-400">
                                Tujuan / Keperluan Surat
                            </p>
                            <p class="mt-0.5 text-slate-700">
                                {{ formData.keperluan }}
                            </p>
                        </div>
                        <div v-if="formData.lampiran_judul">
                            <p class="text-slate-400">Judul Lampiran</p>
                            <p class="mt-0.5 text-slate-700">
                                {{ formData.lampiran_judul }}
                            </p>
                        </div>
                        <div v-if="hasAttachmentPreview">
                            <p class="text-slate-400">Dokumen Lampiran</p>
                            <p class="mt-0.5 text-slate-700">
                                Tabel lampiran ({{ formData.lampiran_rows?.length ?? 0 }} baris)
                            </p>
                        </div>
                    </div>
                </div>
                <!-- Alur -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 class="mb-3 text-sm font-semibold text-slate-800">
                        Alur Setelah Submit
                    </h3>
                    <div class="space-y-2">
                        <div
                            v-for="(step, i) in steps"
                            :key="step"
                            class="flex items-center gap-2"
                        >
                            <div
                                class="grid size-5 shrink-0 place-items-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-500"
                            >
                                {{ i + 1 }}
                            </div>
                            <span class="text-xs text-slate-600">{{
                                step
                            }}</span>
                        </div>
                    </div>
                    <div
                        class="mt-3 rounded-xl p-3 text-xs"
                        :class="
                            needsApproval
                                ? 'bg-amber-50 text-amber-700'
                                : 'bg-blue-50 text-blue-700'
                        "
                    >
                        <p class="font-semibold">
                            {{ workflowStatusLabel }}
                        </p>
                        <p class="mt-1 leading-5">
                            {{ workflowStatusDescription }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
