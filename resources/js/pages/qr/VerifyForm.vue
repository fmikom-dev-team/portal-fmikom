<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ShieldCheck, ArrowRight } from 'lucide-vue-next';

const token = ref('');

function submit(): void {
    const trimmedToken = token.value.trim();
    if (!trimmedToken) return;
    window.location.href = `/verifikasi-qr/${encodeURIComponent(trimmedToken)}`;
}
</script>

<template>
    <Head title="Verifikasi Dokumen" />

    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-50 via-white to-blue-50/40 px-4 py-10">
        <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-7 shadow-xl shadow-slate-200/40">
            <!-- Logo area -->
            <div class="mb-6 flex flex-col items-center gap-3 text-center">
                <div class="grid size-14 place-items-center rounded-2xl bg-primary shadow-md shadow-blue-200">
                    <ShieldCheck class="size-7 text-white" stroke-width="2.5" />
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-blue-600">Verifikasi QR</p>
                    <h1 class="mt-1 text-2xl font-bold text-slate-900">Cek Keaslian Dokumen</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Masukkan token dari QR code surat untuk melihat status validasinya.
                    </p>
                </div>
            </div>

            <form class="space-y-4" @submit.prevent="submit">
                <label class="block space-y-2">
                    <span class="text-sm font-medium text-slate-700">Token QR</span>
                    <input
                        v-model="token"
                        type="text"
                        inputmode="text"
                        autocomplete="off"
                        placeholder="Contoh: 550e8400-e29b-41d4-a716-446655440000"
                    class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition focus:border-primary focus:bg-white focus:ring-2 focus:ring-blue-100"
                    />
                </label>

                <button
                    type="submit"
                    class="fast-btn fast-btn-primary h-12 w-full px-4 text-sm"
                    :disabled="token.trim().length === 0"
                >
                    Verifikasi Dokumen
                    <ArrowRight class="size-4" />
                </button>
            </form>

            <p class="mt-5 text-center text-[10px] text-slate-400">
                FMIKOM - Universitas NU Al Ghazali Cilacap
            </p>
        </div>
    </div>
</template>
