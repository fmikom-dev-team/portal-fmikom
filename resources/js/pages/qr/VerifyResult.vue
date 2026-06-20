<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ShieldCheck, XCircle, Clock3, HelpCircle, FileText, Hash, Calendar, Award, ArrowLeft } from 'lucide-vue-next';

const props = defineProps<{
    found: boolean;
    valid: boolean;
    revoked: boolean;
    message: string;
    surat: {
        nomor_surat?: string | null;
        jenis_surat?: string | null;
        kategori?: string | null;
        status?: string | null;
        nama_pemohon?: string | null;
        nim?: string | null;
        program_studi?: string | null;
        keperluan?: string | null;
        tanggal_terbit?: string | null;
        disahkan_oleh?: string | null;
        nama_approver?: string | null;
        tanggal_persetujuan_final?: string | null;
    } | null;
}>();

const state = computed(() => {
    if (!props.found) return 'not_found';
    if (props.revoked) return 'revoked';
    if (props.valid) return 'valid';
    return 'pending';
});

const cfg = computed(() => ({
    valid:     { icon: ShieldCheck,  label: 'DOKUMEN VALID',           color: 'blue',   bg: 'bg-blue-600',   ring: 'ring-blue-200',   light: 'bg-blue-50',   text: 'text-blue-800',   border: 'border-blue-200' },
    revoked:   { icon: XCircle,      label: 'DOKUMEN DICABUT',         color: 'red',    bg: 'bg-red-600',    ring: 'ring-red-200',    light: 'bg-red-50',    text: 'text-red-800',    border: 'border-red-200' },
    pending:   { icon: Clock3,       label: 'MENUNGGU PERSETUJUAN',    color: 'amber',  bg: 'bg-amber-500',  ring: 'ring-amber-200',  light: 'bg-amber-50',  text: 'text-amber-800',  border: 'border-amber-200' },
    not_found: { icon: HelpCircle,   label: 'TIDAK DITEMUKAN',         color: 'slate',  bg: 'bg-slate-500',  ring: 'ring-slate-200',  light: 'bg-slate-50',  text: 'text-slate-700',  border: 'border-slate-200' },
}[state.value]));

function fmt(d?: string | null) {
    if (!d) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
    }).format(new Date(d));
}

function fmtFull(d?: string | null) {
    if (!d) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    }).format(new Date(d));
}
</script>

<template>
    <Head title="Verifikasi Dokumen - FMIKOM" />

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50/40 flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-md space-y-4">
            <!-- Header -->
            <div class="text-center space-y-2">
                <div class="mx-auto grid size-12 place-items-center rounded-2xl bg-blue-600 shadow-lg shadow-blue-200">
                    <FileText class="size-6 text-white" stroke-width="2" />
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-900">Verifikasi Dokumen</p>
                    <p class="text-[10px] text-slate-400">FMIKOM - Universitas NU Al Ghazali Cilacap</p>
                </div>
            </div>

            <!-- Status Card -->
            <div class="overflow-hidden rounded-3xl bg-white shadow-xl shadow-slate-200/40 ring-1"
                :class="cfg.ring">
                <!-- Status Banner -->
                <div class="flex items-center gap-4 px-6 py-5" :class="cfg.bg">
                    <div class="grid size-10 shrink-0 place-items-center rounded-xl bg-white/20 text-white">
                        <component :is="cfg.icon" class="size-5" stroke-width="2.5" />
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-white/70">Status Verifikasi</p>
                        <p class="text-base font-bold text-white leading-tight">{{ cfg.label }}</p>
                        <p class="text-xs text-white/80 mt-0.5">{{ message }}</p>
                    </div>
                </div>

                <!-- Document Details -->
                <div v-if="found && surat" class="px-6 py-5 space-y-4">
                    <!-- Letter Type -->
                    <div class="rounded-2xl border px-4 py-3" :class="cfg.border + ' ' + cfg.light">
                        <div class="flex items-center gap-2">
                            <FileText class="size-4 text-slate-400" />
                            <div>
                                <p class="text-xs font-semibold text-slate-700">{{ surat.jenis_surat ?? '-' }}</p>
                                <p v-if="surat.kategori" class="text-[10px] text-slate-400">{{ surat.kategori }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Document Identity -->
                    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                        <div class="px-4 py-2.5 border-b border-slate-100 bg-slate-50/50">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Detail Dokumen</p>
                        </div>
                        <div class="divide-y divide-slate-100">
                            <div class="flex items-center justify-between px-4 py-3">
                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <Hash class="size-3.5 text-slate-400" /> Nomor Surat
                                </div>
                                <p class="text-xs font-bold text-slate-800 font-mono">{{ surat.nomor_surat ?? '-' }}</p>
                            </div>
                            <div class="flex items-center justify-between px-4 py-3">
                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <Calendar class="size-3.5 text-slate-400" /> Tanggal Terbit
                                </div>
                                <p class="text-xs font-bold text-slate-800">{{ fmt(surat.tanggal_terbit) }}</p>
                            </div>
                            <div v-if="surat.program_studi" class="flex items-center justify-between px-4 py-3">
                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <Award class="size-3.5 text-slate-400" /> Program Studi
                                </div>
                                <p class="text-xs font-bold text-slate-800">{{ surat.program_studi }}</p>
                            </div>
                            <div class="flex items-center justify-between px-4 py-3">
                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <Award class="size-3.5 text-slate-400" /> Disahkan Oleh
                                </div>
                                <p class="text-xs font-bold text-slate-800">{{ surat.disahkan_oleh ?? '-' }}</p>
                            </div>
                            <div class="flex items-center justify-between px-4 py-3">
                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <Calendar class="size-3.5 text-slate-400" /> Persetujuan Final
                                </div>
                                <p class="text-xs font-bold text-slate-800">{{ fmt(surat.tanggal_persetujuan_final) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Status Message Box -->
                    <div v-if="state === 'valid'" class="flex items-center gap-3 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3">
                        <div class="grid size-7 shrink-0 place-items-center rounded-lg bg-blue-600 text-white">
                            <ShieldCheck class="size-4" stroke-width="2.5" />
                        </div>
                        <p class="text-xs font-semibold text-blue-800">Dokumen sah dan diterbitkan resmi oleh FMIKOM.</p>
                    </div>
                    <div v-if="state === 'revoked'" class="flex items-center gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3">
                        <div class="grid size-7 shrink-0 place-items-center rounded-lg bg-red-600 text-white">
                            <XCircle class="size-4" stroke-width="2.5" />
                        </div>
                        <p class="text-xs font-semibold text-red-800">Dokumen telah dicabut dan tidak berlaku lagi.</p>
                    </div>
                    <div v-if="state === 'pending'" class="flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3">
                        <div class="grid size-7 shrink-0 place-items-center rounded-lg bg-amber-500 text-white">
                            <Clock3 class="size-4" stroke-width="2.5" />
                        </div>
                        <p class="text-xs font-semibold text-amber-800">Dokumen masih dalam proses persetujuan.</p>
                    </div>
                </div>

                <!-- Not Found State -->
                <div v-if="!found" class="px-6 py-5">
                    <div class="space-y-2 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-4">
                        <p class="text-xs font-semibold text-slate-700">Kemungkinan penyebab:</p>
                        <div class="space-y-1">
                            <p class="text-[11px] text-slate-500 flex items-center gap-2">
                                <span class="size-1.5 rounded-full bg-slate-300" /> QR code rusak atau tidak terbaca sempurna
                            </p>
                            <p class="text-[11px] text-slate-500 flex items-center gap-2">
                                <span class="size-1.5 rounded-full bg-slate-300" /> Surat belum mendapat persetujuan resmi
                            </p>
                            <p class="text-[11px] text-slate-500 flex items-center gap-2">
                                <span class="size-1.5 rounded-full bg-slate-300" /> Surat tidak tercatat dalam sistem
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer Timestamp -->
                <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50/60 px-6 py-3">
                    <p class="text-[10px] text-slate-400">{{ fmtFull(new Date().toISOString()) }}</p>
                    <div class="flex items-center gap-1.5">
                        <div class="size-1.5 rounded-full animate-pulse"
                            :class="{
                                'bg-blue-400': cfg.color === 'blue',
                                'bg-red-400': cfg.color === 'red',
                                'bg-amber-400': cfg.color === 'amber',
                                'bg-slate-400': cfg.color === 'slate',
                            }" />
                        <p class="text-[10px] font-medium text-slate-400">Real-time</p>
                    </div>
                </div>
            </div>

            <!-- Back Link -->
            <div class="text-center">
                <a href="/verifikasi-qr"
                    class="inline-flex items-center gap-1.5 text-xs font-medium text-blue-600 transition-colors hover:text-blue-700 hover:underline">
                    <ArrowLeft class="size-3.5" /> Verifikasi token lain
                </a>
            </div>
        </div>
    </div>
</template>
