<script setup lang="ts">
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    BookOpenText,
    CalendarCheck,
    ClipboardList,
    FileText,
    House,
} from 'lucide-vue-next';
import wimsRoutes from '@/routes/wims';

const page = usePage();

const selectedPeriodId = computed(() => (page.props as any).selected_period_id ?? null);

const withSelectedPeriod = (href: string) => {
    if (!selectedPeriodId.value) {
        return href;
    }

    const url = new URL(href, window.location.origin);
    url.searchParams.set('pendaftaran', String(selectedPeriodId.value));
    return url.pathname + url.search + url.hash;
};
const currentPath = computed(() => {
    const [path] = page.url.split('?');
    return path || '/';
});

const items = [
    {
        label: 'Daftar',
        href: withSelectedPeriod(wimsRoutes.registration().url),
        match: (path: string) => path.startsWith('/wims/pendaftaran'),
        icon: BookOpenText,
        center: false,
    },
    {
        label: 'Presensi',
        href: withSelectedPeriod(wimsRoutes.attendance().url),
        match: (path: string) => path.startsWith('/wims/absensi'),
        icon: CalendarCheck,
        center: false,
    },
    {
        label: 'Home',
        href: withSelectedPeriod('/wims/dashboard'),
        match: (path: string) => path === '/wims/dashboard',
        icon: House,
        center: true,
    },
    {
        label: 'Logbook',
        href: withSelectedPeriod(wimsRoutes.logbook().url),
        match: (path: string) => path.startsWith('/wims/logbook'),
        icon: ClipboardList,
        center: false,
    },
    {
        label: 'Laporan',
        href: withSelectedPeriod(wimsRoutes.laporan().url),
        match: (path: string) => path.startsWith('/wims/laporan'),
        icon: FileText,
        center: false,
    },
];
</script>

<template>
    <nav class="fixed inset-x-0 bottom-0 z-40 lg:hidden">
        <!-- Safe area fill -->
        <div class="absolute inset-x-0 bottom-0 h-[env(safe-area-inset-bottom)] bg-wims-card" />

        <div class="relative mx-auto flex max-w-lg items-end justify-around px-4 pb-[max(env(safe-area-inset-bottom),10px)] pt-2.5">
            <!-- Glass pill background -->
            <div class="absolute inset-x-4 bottom-[max(env(safe-area-inset-bottom),10px)] top-2.5 rounded-[24px] border border-wims-border bg-wims-card/95 shadow-[0_-4px_24px_-8px_rgba(0,0,0,0.06)] dark:shadow-[0_-4px_32px_-8px_rgba(0,0,0,0.4)] backdrop-blur-xl transition-colors duration-300" />

            <Link
                v-for="item in items"
                :key="item.label"
                :href="item.href"
                class="relative z-10 flex flex-col items-center transition-all duration-300"
                :class="item.center ? '-mt-5' : 'mt-1'"
            >
                <!-- Center floating button -->
                <template v-if="item.center">
                    <div
                        class="flex size-12 items-center justify-center rounded-2xl transition-all duration-300 active:scale-95"
                        :class="
                            item.match(currentPath)
                                ? 'bg-gradient-to-br from-blue-500 to-blue-600 shadow-[0_6px_24px_-4px_rgba(59,130,246,0.55)] dark:shadow-[0_6px_24px_-4px_rgba(59,130,246,0.35)]'
                                : 'bg-gradient-to-br from-blue-600 to-blue-700 shadow-[0_4px_16px_-4px_rgba(59,130,246,0.35)] dark:shadow-[0_4px_16px_-4px_rgba(59,130,246,0.2)]'
                        "
                    >
                        <div
                            v-if="item.match(currentPath)"
                            class="absolute inset-0 rounded-2xl bg-blue-400/20 blur-lg"
                        />
                        <component :is="item.icon" class="relative size-5 text-white" />
                    </div>
                    <span
                        class="mt-1.5 text-[9px] font-bold tracking-wide transition-colors duration-200"
                        :class="item.match(currentPath) ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400 dark:text-slate-500'"
                    >
                        {{ item.label }}
                    </span>
                </template>

                <!-- Regular items -->
                <template v-else>
                    <div class="flex flex-col items-center gap-1 px-3.5 py-2">
                        <div
                            class="relative flex size-9 items-center justify-center rounded-xl transition-all duration-200"
                            :class="
                                item.match(currentPath)
                                    ? 'bg-blue-50 dark:bg-blue-500/12 text-blue-600 dark:text-blue-400'
                                    : 'text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300'
                            "
                        >
                            <!-- Active indicator dot -->
                            <span
                                v-if="item.match(currentPath)"
                                class="absolute -top-0.5 left-1/2 h-[3px] w-4 -translate-x-1/2 rounded-full bg-blue-500 dark:bg-blue-400"
                            />
                            <component
                                :is="item.icon"
                                class="size-[18px] transition-transform duration-200"
                                :class="item.match(currentPath) ? 'scale-105' : ''"
                            />
                        </div>
                        <span
                            class="text-[9px] font-semibold tracking-wide transition-colors duration-200"
                            :class="item.match(currentPath) ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400 dark:text-slate-500'"
                        >
                            {{ item.label }}
                        </span>
                    </div>
                </template>
            </Link>
        </div>
    </nav>
</template>


