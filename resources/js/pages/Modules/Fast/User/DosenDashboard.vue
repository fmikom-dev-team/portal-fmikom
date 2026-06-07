<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";

defineProps<{
	moduleName: string;
	roleName: string;
}>();
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: moduleName + ' Dashboard', href: '#' }]">
        <Head :title="moduleName + ' Dashboard'" />
        
        <div class="flex h-full flex-1 flex-col gap-4 p-4 lg:gap-6 lg:p-6">
            <div class="relative z-10 mb-8 max-w-3xl">
                <h1 class="text-3xl font-black tracking-tight text-[#0f172a] dark:text-white">
                    Dashboard {{ moduleName }} <br />
                    <span class="text-xl text-[#2563EB] mt-2 block">{{ roleName }} Access</span>
                </h1>
                <p class="mt-4 text-base font-medium text-gray-500">
                    Selamat datang di {{ moduleName }}! Halaman ini dirender menggunakan template khusus untuk role Anda.
                </p>
                <div class="mt-6">
                    <Link href="/dashboard" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gray-100 py-2.5 px-6 text-sm font-bold text-gray-700 transition hover:bg-gray-200 dark:bg-zinc-800 dark:text-gray-300 dark:hover:bg-zinc-700">
                        &larr; Kembali ke Pemilihan Modul
                    </Link>
                </div>
            </div>
            
            <div class="rounded-xl border bg-white p-8 dark:border-zinc-800 dark:bg-zinc-950">
                <p class="text-center text-gray-500 font-medium">✨ Konten spesifik untuk {{ roleName }} akan ditampilkan di sini.</p>
            </div>
        </div>
    </AppLayout>
</template>
