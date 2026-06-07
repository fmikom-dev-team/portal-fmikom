<script setup lang="ts">
import PagiAdminLayout from "@/layouts/PagiAdminLayout.vue";
</script>

<template>
    <PagiAdminLayout title="Roles & Permissions">
        <div class="mb-6">
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Roles & Permissions</h1>
            <p class="mt-0.5 text-[13px] text-slate-400 dark:text-zinc-500">Kelola hak akses untuk modul PAGI</p>
        </div>

        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 p-8 text-center">
            <div class="mx-auto h-12 w-12 rounded-full bg-slate-50 dark:bg-zinc-800 flex items-center justify-center mb-3">
                <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <h3 class="text-[14px] font-bold text-slate-800 dark:text-zinc-100">Belum ada role tambahan</h3>
            <p class="text-[13px] text-slate-500 dark:text-zinc-400 mt-1">Daftar role dan permission akan muncul di sini.</p>
        </div>
    </PagiAdminLayout>
</template>
