<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import Pagination from '@/components/ui/Pagination.vue';
import TraceAdminLayout from '@/layouts/TraceAdminLayout.vue';
import type { AlumniPagination } from '@/types/alumni';
import AlumniBannerHeader from './components/AlumniBannerHeader.vue';
import AlumniTable from './components/AlumniTable.vue';
import { AlertTriangle, CheckCircle2, ExternalLink, FileText, GraduationCap, XCircle } from 'lucide-vue-next';
import { TPageHeader } from '@/components/Trace';
import { ref } from 'vue';

interface PendingRoleChangeRequest {
    user_id: number;
    name: string;
    email: string;
    nomor_induk?: string | null;
    program_studi?: string | null;
    submitted_at?: string | null;
    data: {
        tahun_lulus?: number | null;
        angkatan?: number | null;
        alamat_rumah?: string | null;
        no_telepon?: string | null;
    };
    proof?: {
        url?: string | null;
        original_name?: string | null;
        mime?: string | null;
        size?: number | null;
    };
}

interface Props {
    alumniList: AlumniPagination;
    totalAlumni: number;
    pendingRoleChangeRequests: PendingRoleChangeRequest[];
    filters: {
        search?: string;
        status?: string;
        prodi?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs = [
    {
        title: 'Alumni',
        href: '/trace/admin/alumni',
    },
];

const formatDate = (value?: string | null) => {
    if (!value) return '-';

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value));
};

const formatFileSize = (bytes?: number | null) => {
    if (!bytes) return '-';

    if (bytes < 1024 * 1024) {
        return `${Math.round(bytes / 1024)} KB`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
};

const decisionDialog = ref<{
    open: boolean;
    action: 'approve' | 'reject' | null;
    request: PendingRoleChangeRequest | null;
    reason: string;
    error: string;
    processing: boolean;
}>({
    open: false,
    action: null,
    request: null,
    reason: '',
    error: '',
    processing: false,
});

const openDecisionDialog = (action: 'approve' | 'reject', request: PendingRoleChangeRequest) => {
    decisionDialog.value = {
        open: true,
        action,
        request,
        reason: '',
        error: '',
        processing: false,
    };
};

const closeDecisionDialog = () => {
    if (decisionDialog.value.processing) return;

    decisionDialog.value.open = false;
};

const submitRoleChangeDecision = () => {
    const request = decisionDialog.value.request;
    const action = decisionDialog.value.action;

    if (!request || !action) return;

    if (action === 'reject' && !decisionDialog.value.reason.trim()) {
        decisionDialog.value.error = 'Alasan penolakan wajib diisi.';
        return;
    }

    decisionDialog.value.processing = true;
    decisionDialog.value.error = '';

    const url = `/trace/admin/alumni/role-change/${request.user_id}/${action}`;
    const payload = action === 'reject' ? { reason: decisionDialog.value.reason.trim() } : {};

    router.post(url, payload, {
        preserveScroll: true,
        onSuccess: () => {
            decisionDialog.value.open = false;
        },
        onError: () => {
            decisionDialog.value.error = 'Gagal memproses pengajuan. Cek kembali data lalu coba lagi.';
        },
        onFinish: () => {
            decisionDialog.value.processing = false;
        },
    });
};
</script>

<template>
    <Head title="Alumni" />

    <TraceAdminLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 max-w-7xl mx-auto w-full"
        >
            <TPageHeader
                title="Data Alumni"
                description="Kelola data alumni dan pantau perkembangan karir mereka."
                :icon="GraduationCap"
            />

            <AlumniBannerHeader :total-alumni="totalAlumni" :filters="filters" />

            <div
                v-if="pendingRoleChangeRequests.length > 0"
                class="overflow-hidden rounded-3xl border border-amber-200 bg-amber-50/60 shadow-sm dark:border-amber-900/50 dark:bg-amber-950/20"
            >
                <div class="border-b border-amber-200/70 px-5 py-4 dark:border-amber-900/50">
                    <h2 class="text-sm font-black text-amber-900 dark:text-amber-200">
                        Pengajuan Perubahan Role Mahasiswa ke Alumni
                    </h2>
                    <p class="mt-1 text-xs font-medium text-amber-700 dark:text-amber-300">
                        Tinjau data pengajuan sebelum memberi akses TRACE sebagai alumni.
                    </p>
                </div>

                <div class="divide-y divide-amber-200/70 dark:divide-amber-900/50">
                    <div
                        v-for="request in pendingRoleChangeRequests"
                        :key="request.user_id"
                        class="grid gap-4 bg-white/70 p-5 dark:bg-slate-950/30 lg:grid-cols-[1.3fr_1fr_auto]"
                    >
                        <div>
                            <p class="text-sm font-black text-slate-900 dark:text-white">
                                {{ request.name }}
                            </p>
                            <p class="mt-1 text-xs font-medium text-slate-500">
                                {{ request.nomor_induk || '-' }} - {{ request.program_studi || '-' }}
                            </p>
                            <p class="mt-1 text-xs text-slate-400">{{ request.email }}</p>
                        </div>

                        <div class="grid gap-1 text-xs font-medium text-slate-600 dark:text-slate-300">
                            <span>Tahun lulus: <strong>{{ request.data.tahun_lulus || '-' }}</strong></span>
                            <span>Angkatan: <strong>{{ request.data.angkatan || '-' }}</strong></span>
                            <span>No. HP: <strong>{{ request.data.no_telepon || '-' }}</strong></span>
                            <span>Alamat: <strong>{{ request.data.alamat_rumah || '-' }}</strong></span>
                            <span>Diajukan: <strong>{{ formatDate(request.submitted_at) }}</strong></span>
                            <a
                                v-if="request.proof?.url"
                                :href="request.proof.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="mt-2 inline-flex w-fit items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-black text-blue-700 transition hover:bg-blue-100 dark:border-blue-900/60 dark:bg-blue-950/30 dark:text-blue-200"
                            >
                                <FileText class="h-4 w-4" />
                                Lihat Dokumen
                                <ExternalLink class="h-3.5 w-3.5" />
                            </a>
                            <span v-if="request.proof?.original_name" class="text-[11px] text-slate-400">
                                {{ request.proof.original_name }} ({{ formatFileSize(request.proof.size) }})
                            </span>
                        </div>

                        <div class="flex items-center gap-2 lg:justify-end">
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white transition hover:bg-emerald-700"
                                @click="openDecisionDialog('approve', request)"
                            >
                                <CheckCircle2 class="h-4 w-4" />
                                Approve
                            </button>
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-white px-4 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-50"
                                @click="openDecisionDialog('reject', request)"
                            >
                                <XCircle class="h-4 w-4" />
                                Reject
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col">
                <AlumniTable :alumni="alumniList.data" />

                <Pagination
                    :links="alumniList.links"
                    :total="totalAlumni"
                    :count="alumniList.data.length"
                    label="alumni"
                />
            </div>
        </div>

        <div
            v-if="decisionDialog.open && decisionDialog.request"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
            @click.self="closeDecisionDialog"
        >
            <div class="w-full max-w-xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-950">
                <div class="border-b border-slate-100 px-6 py-5 dark:border-slate-800">
                    <div class="flex items-start gap-3">
                        <div
                            class="rounded-2xl p-3"
                            :class="decisionDialog.action === 'approve' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/30' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/30'"
                        >
                            <CheckCircle2 v-if="decisionDialog.action === 'approve'" class="h-5 w-5" />
                            <AlertTriangle v-else class="h-5 w-5" />
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-slate-900 dark:text-white">
                                {{ decisionDialog.action === 'approve' ? 'Setujui pengajuan ini?' : 'Tolak pengajuan ini?' }}
                            </h2>
                            <p class="mt-1 text-sm font-medium text-slate-500">
                                Pastikan data dan dokumen sudah dicek sebelum keputusan disimpan.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 p-6">
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 text-sm dark:border-slate-800 dark:bg-slate-900/50">
                        <p class="font-black text-slate-900 dark:text-white">{{ decisionDialog.request.name }}</p>
                        <p class="mt-1 text-xs font-medium text-slate-500">
                            {{ decisionDialog.request.nomor_induk || '-' }} - {{ decisionDialog.request.program_studi || '-' }}
                        </p>
                        <div class="mt-3 grid gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300 sm:grid-cols-2">
                            <span>Tahun lulus: {{ decisionDialog.request.data.tahun_lulus || '-' }}</span>
                            <span>Angkatan: {{ decisionDialog.request.data.angkatan || '-' }}</span>
                            <span>No. HP: {{ decisionDialog.request.data.no_telepon || '-' }}</span>
                            <span>Diajukan: {{ formatDate(decisionDialog.request.submitted_at) }}</span>
                        </div>
                        <p class="mt-2 text-xs font-semibold text-slate-600 dark:text-slate-300">
                            Alamat: {{ decisionDialog.request.data.alamat_rumah || '-' }}
                        </p>
                    </div>

                    <a
                        v-if="decisionDialog.request.proof?.url"
                        :href="decisionDialog.request.proof.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-black text-blue-700 transition hover:bg-blue-100 dark:border-blue-900/60 dark:bg-blue-950/30 dark:text-blue-200"
                    >
                        <FileText class="h-4 w-4" />
                        Buka Bukti Kelulusan
                        <ExternalLink class="h-3.5 w-3.5" />
                    </a>

                    <div v-if="decisionDialog.action === 'reject'">
                        <label class="mb-1 block text-xs font-black uppercase tracking-wider text-slate-500">
                            Alasan Penolakan
                        </label>
                        <textarea
                            v-model="decisionDialog.reason"
                            rows="4"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                            placeholder="Contoh: dokumen belum sesuai atau data tahun lulus perlu diperbaiki."
                        />
                    </div>

                    <p v-if="decisionDialog.error" class="text-sm font-semibold text-rose-600">
                        {{ decisionDialog.error }}
                    </p>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-100 px-6 py-4 dark:border-slate-800">
                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-50 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-900"
                        :disabled="decisionDialog.processing"
                        @click="closeDecisionDialog"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        class="rounded-xl px-5 py-2.5 text-sm font-bold text-white transition disabled:opacity-50"
                        :class="decisionDialog.action === 'approve' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'"
                        :disabled="decisionDialog.processing"
                        @click="submitRoleChangeDecision"
                    >
                        {{ decisionDialog.processing ? 'Memproses...' : (decisionDialog.action === 'approve' ? 'Ya, Setujui' : 'Ya, Tolak') }}
                    </button>
                </div>
            </div>
        </div>
    </TraceAdminLayout>
</template>
