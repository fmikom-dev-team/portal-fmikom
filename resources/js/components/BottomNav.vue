<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    CalendarCheck,
    ClipboardList,
    FileText,
    House,
    NotebookTabs,
} from 'lucide-vue-next';
import wims from '@/routes/wims';

const page = usePage();

const currentPath = computed(() => {
    const [path] = page.url.split('?');

    return path || '/';
});

const items = [
    {
        label: 'Home',
        href: '/wims/dashboard',
        match: (path: string) => path === '/wims/dashboard',
        icon: House,
    },
    {
        label: 'Absensi',
        href: wims.attendance().url,
        match: (path: string) => path.startsWith('/wims/absensi'),
        icon: CalendarCheck,
    },
    {
        label: 'Daftar',
        href: wims.registration().url,
        match: (path: string) => path.startsWith('/wims/pendaftaran'),
        icon: NotebookTabs,
    },
    {
        label: 'Logbook',
        href: wims.logbook().url,
        match: (path: string) => path.startsWith('/wims/logbook'),
        icon: ClipboardList,
    },
    {
        label: 'Laporan',
        href: '/wims/laporan',
        match: (path: string) => path.startsWith('/wims/laporan'),
        icon: FileText,
    },
];
</script>

<template>
    <nav
        class="fixed inset-x-0 bottom-0 z-50 border-t border-slate-200 dark:border-slate-800 bg-white/95 dark:bg-slate-900/95 backdrop-blur"
    >
        <div class="mx-auto grid max-w-7xl grid-cols-5">
            <Link
                v-for="item in items"
                :key="item.label"
                :href="item.href"
                class="flex min-h-16 flex-col items-center justify-center gap-1 px-2 py-2 text-xs font-medium transition-colors"
                :class="
                    item.match(currentPath)
                        ? 'text-[#2563EB]'
                        : 'text-slate-500 hover:text-slate-700'
                "
            >
                <component
                    :is="item.icon"
                    class="size-5"
                    :class="item.match(currentPath) ? 'text-[#2563EB]' : ''"
                />
                <span>{{ item.label }}</span>
            </Link>
        </div>
    </nav>
</template>
