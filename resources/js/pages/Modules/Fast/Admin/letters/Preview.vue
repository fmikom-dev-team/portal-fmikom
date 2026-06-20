<script setup lang="ts">
// resources/js/pages/Modules/Fast/Admin/letters/Preview.vue
import AdminLayout from '@/layouts/Modules/Fast/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import LetterStepIndicator from '@/components/Modules/Fast/Admin/LetterStepIndicator.vue';
import {
    ChevronLeft,
    Send,
    Eye,
    Maximize2,
    Minimize2,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted } from 'vue';
// Buat blob URL dari HTML agar tidak perlu sandbox
const blobUrl = ref('');
onMounted(() => {
    const blob = new Blob([props.previewDocumentHtml], { type: 'text/html' });
    blobUrl.value = URL.createObjectURL(blob);
});
onUnmounted(() => {
    if (blobUrl.value) URL.revokeObjectURL(blobUrl.value);
});
const previewBlobUrl = computed(() => blobUrl.value);
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
    field_config: Array<{
        name: string;
        label: string;
        type: string;
        required: boolean;
    }>;
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
    jenisSurat: JenisSurat;
    formData: FormData;
    renderedHtml: string;
    previewDocumentHtml: string;
}>();
const form = useForm({
    jenis_surat_id: props.formData.jenis_surat_id,
    keperluan: props.formData.keperluan,
    perihal: props.formData.perihal ?? '',
    kepada_yth: props.formData.kepada_yth ?? [],
    lampiran_keterangan: props.formData.lampiran_keterangan ?? '',
    form_data: props.formData.data,
});
const fullPreview = ref(false);
function submit() {
    form.post('/admin/surat/store');
}
function goBack() {
    window.history.back();
}
const needsApproval = !!props.jenisSurat.approval_role?.id;
const steps = needsApproval
    ? [
          'Surat diajukan',
          'Validasi Admin',
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
            { label: 'Isi Form', href: `/admin/surat/form/${jenisSurat.id}` },
            { label: 'Preview' },
        ]"
    >
        <Head :title="`Preview - ${jenisSurat.nama}`" />
        <!-- Hero header -->
        <div
            class="mb-6 rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-white p-6"
        >
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <h2 class="mt-1 text-xl font-bold text-slate-900">
                        Preview & Submit
                    </h2>
                    <p class="mt-1 max-w-lg text-sm text-slate-500">
                        Review dokumen surat sebelum mengajukan. Pastikan semua
                        data sudah benar.
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
                            :src="previewBlobUrl"
                            title="Preview surat"
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
                                  ? 'Ajukan Surat'
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
                        <div>
                            <p class="text-slate-400">Jenis Surat</p>
                            <p class="mt-0.5 font-semibold text-slate-800">
                                {{ jenisSurat.nama }}
                            </p>
                        </div>
                        <div>
                            <p class="text-slate-400">Keperluan</p>
                            <p class="mt-0.5 text-slate-700">
                                {{ formData.keperluan }}
                            </p>
                        </div>
                        <div v-if="formData.perihal">
                            <p class="text-slate-400">Perihal</p>
                            <p class="mt-0.5 text-slate-700">
                                {{ formData.perihal }}
                            </p>
                        </div>
                        <div
                            v-if="
                                formData.kepada_yth &&
                                formData.kepada_yth.length > 0
                            "
                        >
                            <p class="text-slate-400">Kepada Yth.</p>
                            <div class="mt-1 space-y-0.5">
                                <p
                                    v-for="k in formData.kepada_yth"
                                    :key="k"
                                    class="text-slate-700"
                                >
                                </p>
                            </div>
                        </div>
                        <div v-if="formData.lampiran_keterangan">
                            <p class="text-slate-400">Lampiran</p>
                            <p class="mt-0.5 text-slate-700">
                                {{ formData.lampiran_keterangan }}
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
                        {{
                            needsApproval
                        }}
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
