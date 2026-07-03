<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Download, FileText, Save, Trash2, Upload } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import WimsAdminLayout from '@/layouts/Modules/Wims/Admin/Layout.vue';

defineOptions({
    layout: WimsAdminLayout,
});

type CurrentTemplate = {
    id: number;
    title?: string | null;
    description?: string | null;
    original_name?: string | null;
    mime_type?: string | null;
    file_size?: number | null;
    is_active?: boolean;
    updated_at?: string | null;
    download_url?: string | null;
};

const props = defineProps<{
    current_template?: CurrentTemplate | null;
}>();

const fileInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    title: props.current_template?.title ?? 'Template Laporan Akhir',
    description: props.current_template?.description ?? '',
    file: null as File | null,
    is_active: props.current_template?.is_active ?? true,
});

const hasCurrentTemplate = computed(() => Boolean(props.current_template));

const syncForm = () => {
    form.title = props.current_template?.title ?? 'Template Laporan Akhir';
    form.description = props.current_template?.description ?? '';
    form.file = null;
    form.is_active = props.current_template?.is_active ?? true;
    form.clearErrors();
};

watch(
    () => props.current_template,
    () => syncForm(),
    { immediate: true, deep: true },
);

const openFilePicker = () => {
    fileInput.value?.click();
};

const onFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    form.file = target.files?.[0] ?? null;
};

const submit = () => {
    const options = {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => syncForm(),
    };

    if (props.current_template) {
        form.put(`/wims/admin/template-laporan-akhir/${props.current_template.id}`, options);
        return;
    }

    form.post('/wims/admin/template-laporan-akhir', options);
};

const deleteTemplate = () => {
    if (!props.current_template) {
        return;
    }

    if (!window.confirm('Hapus template laporan akhir yang sedang aktif?')) {
        return;
    }

    router.delete(`/wims/admin/template-laporan-akhir/${props.current_template.id}`, {
        preserveScroll: true,
    });
};

const downloadTemplate = () => {
    if (!props.current_template?.download_url || typeof window === 'undefined') {
        return;
    }

    window.location.href = props.current_template.download_url;
};
</script>

<template>
    <Head title="Template Laporan Akhir" />

    <div class="mx-auto w-full max-w-[1320px] space-y-5 px-4 py-4 sm:px-6 sm:py-6 lg:space-y-6 lg:px-8 lg:py-8">
        <Card class="rounded-xl border border-zinc-200 bg-white py-0 shadow-none">
            <CardHeader class="border-b border-zinc-200 px-5 py-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <CardTitle class="text-[15px] font-bold text-slate-950">
                            Template Laporan Akhir
                        </CardTitle>
                        <CardDescription class="mt-1 text-sm leading-6 text-slate-600">
                            Upload file baru untuk mengganti template aktif. Mahasiswa akan selalu mengambil versi terbaru.
                        </CardDescription>
                    </div>

                    <div v-if="hasCurrentTemplate" class="flex flex-wrap gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            class="h-9 rounded-xl border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-700"
                            @click="downloadTemplate"
                        >
                            <Download class="size-4" />
                            Download
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            class="h-9 rounded-xl border border-rose-200 bg-white px-4 text-sm font-bold text-rose-600 hover:bg-rose-50 hover:text-rose-700"
                            @click="deleteTemplate"
                        >
                            <Trash2 class="size-4" />
                            Hapus
                        </Button>
                    </div>
                </div>
            </CardHeader>

            <CardContent class="space-y-5 px-5 py-5">
                <div
                    v-if="current_template"
                    class="flex flex-col gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-zinc-950">
                            {{ current_template.title || current_template.original_name || 'Template Laporan Akhir' }}
                        </p>
                        <p class="mt-1 text-sm text-zinc-600">
                            {{ current_template.original_name || 'File belum diberi nama' }}
                            <span class="mx-1">•</span>
                            {{ current_template.updated_at || '-' }}
                        </p>
                    </div>

                    <span
                        class="inline-flex w-fit rounded-full px-2.5 py-1 text-[10px] font-bold ring-1"
                        :class="current_template.is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-600 ring-slate-200'"
                    >
                        {{ current_template.is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-bold text-zinc-900">File template</label>
                    <div class="rounded-xl border border-dashed border-zinc-200 bg-zinc-50 px-4 py-4">
                        <div class="flex flex-col items-center gap-2 text-center">
                            <div class="flex size-11 items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200">
                                <FileText class="size-5 text-zinc-500" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-zinc-800">
                                    <span class="text-blue-600">Klik untuk pilih file</span> atau drop file ke sini
                                </p>
                                <p class="mt-1 text-xs text-zinc-500">PDF, DOC, DOCX maksimal 10 MB</p>
                            </div>
                            <input
                                ref="fileInput"
                                type="file"
                                accept=".pdf,.doc,.docx"
                                class="sr-only"
                                @change="onFileChange"
                            />
                            <Button
                                type="button"
                                variant="outline"
                                class="h-9 rounded-xl border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-700"
                                @click="openFilePicker"
                            >
                                <Upload class="size-4" />
                                {{ current_template ? 'Ganti File' : 'Pilih File' }}
                            </Button>
                        </div>
                    </div>
                    <p v-if="form.file" class="text-xs text-zinc-500">
                        File terpilih: {{ form.file.name }}
                    </p>
                    <InputError :message="form.errors.file" />
                </div>

                <div class="flex items-center gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3">
                    <input v-model="form.is_active" type="checkbox" class="size-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500" />
                    <div>
                        <p class="text-sm font-bold text-zinc-900">Jadikan template aktif</p>
                        <p class="text-xs text-zinc-500">Mahasiswa hanya melihat template yang aktif.</p>
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                    <Button
                        type="button"
                        class="h-9 rounded-xl bg-gradient-to-r from-blue-600 to-blue-500 px-4 text-sm font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:shadow-blue-500/30 active:scale-[0.98] dark:from-[#214FAF] dark:to-[#0F6FBE] dark:shadow-[0_14px_34px_-18px_rgba(8,15,30,0.84)] dark:hover:shadow-[0_18px_38px_-18px_rgba(8,15,30,0.92)]"
                        :disabled="form.processing"
                        @click="submit"
                    >
                        <Save class="size-4" />
                        {{ form.processing ? 'Menyimpan...' : (current_template ? 'Simpan Perubahan' : 'Upload Template') }}
                    </Button>
                </div>
            </CardContent>
        </Card>
    </div>
</template>