<script setup lang="ts">
import PagiAdminLayout from "@/layouts/PagiAdminLayout.vue";
</script>

<template>
    <PagiAdminLayout title="Galeri Karya">
        <div class="mb-6">
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Galeri Karya</h1>
            <p class="mt-0.5 text-[13px] text-slate-400 dark:text-zinc-500">Lihat semua karya dalam tampilan galeri</p>
        </div>

        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 p-8 text-center">
            <div class="mx-auto h-12 w-12 rounded-full bg-slate-50 dark:bg-zinc-800 flex items-center justify-center mb-3">
                <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <h3 class="text-[14px] font-bold text-slate-800 dark:text-zinc-100">Galeri kosong</h3>
            <p class="text-[13px] text-slate-500 dark:text-zinc-400 mt-1">Belum ada karya yang diunggah.</p>
        </div>
    </PagiAdminLayout>
</template>
