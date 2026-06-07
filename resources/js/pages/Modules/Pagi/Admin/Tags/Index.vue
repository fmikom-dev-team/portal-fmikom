<script setup lang="ts">
import PagiAdminLayout from "@/layouts/PagiAdminLayout.vue";
</script>

<template>
    <PagiAdminLayout title="Tags & Kategori">
        <div class="mb-6">
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Tags & Kategori</h1>
            <p class="mt-0.5 text-[13px] text-slate-400 dark:text-zinc-500">Kelola tag dan kategori untuk karya</p>
        </div>

        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 p-8 text-center">
            <div class="mx-auto h-12 w-12 rounded-full bg-slate-50 dark:bg-zinc-800 flex items-center justify-center mb-3">
                <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
            </div>
            <h3 class="text-[14px] font-bold text-slate-800 dark:text-zinc-100">Belum ada tag</h3>
            <p class="text-[13px] text-slate-500 dark:text-zinc-400 mt-1">Daftar tag akan muncul di sini.</p>
        </div>
    </PagiAdminLayout>
</template>
