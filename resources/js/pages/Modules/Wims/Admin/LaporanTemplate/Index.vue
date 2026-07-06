<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Download, FileText, MoreVertical, Save, Trash2, Upload } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import WimsAdminLayout from '@/layouts/Modules/Wims/Admin/Layout.vue';

defineOptions({
    layout: WimsAdminLayout,
});

type TemplateCard = {
    id: number;
    template_type?: 'proposal' | 'final_report';
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
    proposal_template?: TemplateCard | null;
    final_report_template?: TemplateCard | null;
}>();

const proposalFileInput = ref<HTMLInputElement | null>(null);
const finalReportFileInput = ref<HTMLInputElement | null>(null);

const proposalForm = useForm({
    template_type: 'proposal' as const,
    title: props.proposal_template?.title ?? 'Template Proposal PKL',
    description: props.proposal_template?.description ?? '',
    file: null as File | null,
    is_active: props.proposal_template?.is_active ?? true,
});

const finalReportForm = useForm({
    template_type: 'final_report' as const,
    title: props.final_report_template?.title ?? 'Template Laporan Akhir',
    description: props.final_report_template?.description ?? '',
    file: null as File | null,
    is_active: props.final_report_template?.is_active ?? true,
});

const syncProposalForm = () => {
    proposalForm.title = props.proposal_template?.title ?? 'Template Proposal PKL';
    proposalForm.description = props.proposal_template?.description ?? '';
    proposalForm.file = null;
    proposalForm.is_active = props.proposal_template?.is_active ?? true;
    proposalForm.clearErrors();
};

const syncFinalReportForm = () => {
    finalReportForm.title = props.final_report_template?.title ?? 'Template Laporan Akhir';
    finalReportForm.description = props.final_report_template?.description ?? '';
    finalReportForm.file = null;
    finalReportForm.is_active = props.final_report_template?.is_active ?? true;
    finalReportForm.clearErrors();
};

watch(() => props.proposal_template, () => syncProposalForm(), { immediate: true, deep: true });
watch(() => props.final_report_template, () => syncFinalReportForm(), { immediate: true, deep: true });

const openFilePicker = (type: 'proposal' | 'final_report') => {
    if (type === 'proposal') {
        proposalFileInput.value?.click();
        return;
    }

    finalReportFileInput.value?.click();
};

const onFileChange = (event: Event, type: 'proposal' | 'final_report') => {
    const target = event.target as HTMLInputElement;

    if (type === 'proposal') {
        proposalForm.file = target.files?.[0] ?? null;
        return;
    }

    finalReportForm.file = target.files?.[0] ?? null;
};

const submit = (type: 'proposal' | 'final_report') => {
    const isProposal = type === 'proposal';
    const form = isProposal ? proposalForm : finalReportForm;
    const currentTemplate = isProposal ? props.proposal_template : props.final_report_template;
    const endpoint = currentTemplate
        ? `/wims/admin/template-proposal-laporan/${currentTemplate.id}`
        : '/wims/admin/template-proposal-laporan';

    const options = {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => (isProposal ? syncProposalForm() : syncFinalReportForm()),
    };

    if (currentTemplate) {
        form.put(endpoint, options);
        return;
    }

    form.post(endpoint, options);
};

const deleteTemplate = (type: 'proposal' | 'final_report') => {
    const currentTemplate = type === 'proposal' ? props.proposal_template : props.final_report_template;

    if (!currentTemplate) {
        return;
    }

    const label = type === 'proposal' ? 'proposal PKL' : 'laporan akhir PKL';

    if (!window.confirm(`Hapus template ${label} yang sedang aktif?`)) {
        return;
    }

    router.delete(`/wims/admin/template-proposal-laporan/${currentTemplate.id}`, {
        preserveScroll: true,
    });
};

const downloadTemplate = (type: 'proposal' | 'final_report') => {
    const currentTemplate = type === 'proposal' ? props.proposal_template : props.final_report_template;

    if (!currentTemplate?.download_url || typeof window === 'undefined') {
        return;
    }

    window.location.href = currentTemplate.download_url;
};
</script>

<template>
    <Head title="Template Proposal & Laporan" />

    <div class="mx-auto w-full max-w-[1500px] space-y-5 px-4 py-4 sm:px-6 sm:py-6 lg:space-y-6 lg:px-8 lg:py-8">
        <div class="grid gap-5 lg:grid-cols-2">
            <Card class="rounded-xl border border-zinc-200 bg-white py-0 shadow-none">
                <CardHeader class="border-b border-zinc-200 px-5 py-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <CardTitle class="text-[15px] font-bold text-slate-950">Template Proposal PKL</CardTitle>
                            <CardDescription class="mt-1 text-sm leading-6 text-slate-600">
                                Upload template proposal yang akan ditampilkan di halaman pendaftaran mahasiswa.
                            </CardDescription>
                        </div>
                        <DropdownMenu v-if="proposal_template">
                            <DropdownMenuTrigger as-child>
                                <Button type="button" variant="outline" size="icon" class="size-8 rounded-lg border border-zinc-200 bg-white text-zinc-700 p-0">
                                    <MoreVertical class="size-3.5" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-44">
                                <DropdownMenuItem @click="downloadTemplate('proposal')">
                                    <Download class="size-4" />
                                    Download
                                </DropdownMenuItem>
                                <DropdownMenuItem class="text-rose-600 focus:text-rose-600" @click="deleteTemplate('proposal')">
                                    <Trash2 class="size-4" />
                                    Hapus
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </CardHeader>
                <CardContent class="space-y-5 px-5 py-5">
                    <div v-if="proposal_template" class="flex flex-col gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-zinc-950">{{ proposal_template.title || proposal_template.original_name || 'Template Proposal PKL' }}</p>
                            <p class="mt-1 text-sm text-zinc-600">{{ proposal_template.original_name || 'File belum diberi nama' }} - {{ proposal_template.updated_at || '-' }}</p>
                        </div>
                        <span class="inline-flex w-fit rounded-full px-2.5 py-1 text-[10px] font-bold ring-1" :class="proposal_template.is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-600 ring-slate-200'">
                            {{ proposal_template.is_active ? 'Aktif' : 'Nonaktif' }}
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
                                    <p class="text-sm font-semibold text-zinc-800"><span class="text-blue-600">Klik untuk pilih file</span> atau drop file ke sini</p>
                                    <p class="mt-1 text-xs text-zinc-500">PDF, DOC, DOCX maksimal 10 MB</p>
                                </div>
                                <input ref="proposalFileInput" type="file" accept=".pdf,.doc,.docx" class="sr-only" @change="(event) => onFileChange(event, 'proposal')" />
                                <Button type="button" variant="outline" class="h-9 rounded-xl border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-700" @click="openFilePicker('proposal')">
                                    <Upload class="size-4" /> {{ proposal_template ? 'Ganti File' : 'Pilih File' }}
                                </Button>
                            </div>
                        </div>
                        <p v-if="proposalForm.file" class="text-xs text-zinc-500">File terpilih: {{ proposalForm.file.name }}</p>
                        <InputError :message="proposalForm.errors.file" />
                    </div>
                    <div class="flex items-center gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3">
                        <input v-model="proposalForm.is_active" type="checkbox" class="size-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500" />
                        <div>
                            <p class="text-sm font-bold text-zinc-900">Jadikan template aktif</p>
                            <p class="text-xs text-zinc-500">Mahasiswa hanya melihat template yang aktif.</p>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <Button type="button" class="h-9 rounded-xl bg-gradient-to-r from-blue-600 to-blue-500 px-4 text-sm font-bold text-white shadow-lg shadow-blue-500/20" :disabled="proposalForm.processing" @click="submit('proposal')">
                            <Save class="size-4" /> {{ proposalForm.processing ? 'Menyimpan...' : (proposal_template ? 'Simpan Perubahan' : 'Upload Template') }}
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <Card class="rounded-xl border border-zinc-200 bg-white py-0 shadow-none">
                <CardHeader class="border-b border-zinc-200 px-5 py-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <CardTitle class="text-[15px] font-bold text-slate-950">Template Laporan Akhir PKL</CardTitle>
                            <CardDescription class="mt-1 text-sm leading-6 text-slate-600">
                                Upload template laporan akhir yang akan dipakai mahasiswa saat masa PKL selesai.
                            </CardDescription>
                        </div>
                        <DropdownMenu v-if="final_report_template">
                            <DropdownMenuTrigger as-child>
                                <Button type="button" variant="outline" size="icon" class="size-8 rounded-lg border border-zinc-200 bg-white text-zinc-700 p-0">
                                    <MoreVertical class="size-3.5" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-44">
                                <DropdownMenuItem @click="downloadTemplate('final_report')">
                                    <Download class="size-4" />
                                    Download
                                </DropdownMenuItem>
                                <DropdownMenuItem class="text-rose-600 focus:text-rose-600" @click="deleteTemplate('final_report')">
                                    <Trash2 class="size-4" />
                                    Hapus
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </CardHeader>
                <CardContent class="space-y-5 px-5 py-5">
                    <div v-if="final_report_template" class="flex flex-col gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-zinc-950">{{ final_report_template.title || final_report_template.original_name || 'Template Laporan Akhir PKL' }}</p>
                            <p class="mt-1 text-sm text-zinc-600">{{ final_report_template.original_name || 'File belum diberi nama' }} - {{ final_report_template.updated_at || '-' }}</p>
                        </div>
                        <span class="inline-flex w-fit rounded-full px-2.5 py-1 text-[10px] font-bold ring-1" :class="final_report_template.is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-600 ring-slate-200'">
                            {{ final_report_template.is_active ? 'Aktif' : 'Nonaktif' }}
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
                                    <p class="text-sm font-semibold text-zinc-800"><span class="text-blue-600">Klik untuk pilih file</span> atau drop file ke sini</p>
                                    <p class="mt-1 text-xs text-zinc-500">PDF, DOC, DOCX maksimal 10 MB</p>
                                </div>
                                <input ref="finalReportFileInput" type="file" accept=".pdf,.doc,.docx" class="sr-only" @change="(event) => onFileChange(event, 'final_report')" />
                                <Button type="button" variant="outline" class="h-9 rounded-xl border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-700" @click="openFilePicker('final_report')">
                                    <Upload class="size-4" /> {{ final_report_template ? 'Ganti File' : 'Pilih File' }}
                                </Button>
                            </div>
                        </div>
                        <p v-if="finalReportForm.file" class="text-xs text-zinc-500">File terpilih: {{ finalReportForm.file.name }}</p>
                        <InputError :message="finalReportForm.errors.file" />
                    </div>
                    <div class="flex items-center gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3">
                        <input v-model="finalReportForm.is_active" type="checkbox" class="size-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500" />
                        <div>
                            <p class="text-sm font-bold text-zinc-900">Jadikan template aktif</p>
                            <p class="text-xs text-zinc-500">Mahasiswa hanya melihat template yang aktif.</p>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <Button type="button" class="h-9 rounded-xl bg-gradient-to-r from-blue-600 to-blue-500 px-4 text-sm font-bold text-white shadow-lg shadow-blue-500/20" :disabled="finalReportForm.processing" @click="submit('final_report')">
                            <Save class="size-4" /> {{ finalReportForm.processing ? 'Menyimpan...' : (final_report_template ? 'Simpan Perubahan' : 'Upload Template') }}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
