<script setup lang="ts">
import PagiAdminLayout from "@/layouts/PagiAdminLayout.vue";
</script>

<template>
    <PagiAdminLayout title="Log Aktivitas">
        <div class="mb-6">
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Log Aktivitas</h1>
            <p class="mt-0.5 text-[13px] text-slate-400 dark:text-zinc-500">Pantau semua tindakan administratif di modul PAGI</p>
        </div>

        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 p-8 text-center">
            <div class="mx-auto h-12 w-12 rounded-full bg-slate-50 dark:bg-zinc-800 flex items-center justify-center mb-3">
                <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
            </div>
            <h3 class="text-[14px] font-bold text-slate-800 dark:text-zinc-100">Log kosong</h3>
            <p class="text-[13px] text-slate-500 dark:text-zinc-400 mt-1">Catatan aktivitas akan muncul di sini.</p>
        </div>
    </PagiAdminLayout>
</template>
