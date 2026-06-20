<script setup lang="ts">
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    BookOpenText,
    CalendarCheck,
    ClipboardList,
    FileText,
    GraduationCap,
    House,
    Landmark,
    LogOut,
    UserRound,
} from 'lucide-vue-next';
import wimsRoutes from '@/routes/wims';

const page = usePage();

const currentPath = computed(() => {
    const [path] = page.url.split('?');
    return path || '/';
});

const user = computed(() => page.props.auth?.user ?? null);
const userInitial = computed(() =>
    String(user.value?.name ?? 'M')
        .trim()
        .charAt(0)
        .toUpperCase(),
);

const items = [
    {
        label: 'Dashboard',
        description: 'Ringkasan aktivitas',
        href: '/wims/dashboard',
        match: (path: string) => path === '/wims/dashboard',
        icon: House,
    },
    {
        label: 'Presensi',
        description: 'Presensi harian',
        href: wimsRoutes.attendance().url,
        match: (path: string) => path.startsWith('/wims/absensi'),
        icon: CalendarCheck,
    },
    {
        label: 'Pendaftaran',
        description: 'Status dan periode',
        href: wimsRoutes.registration().url,
        match: (path: string) => path.startsWith('/wims/pendaftaran'),
        icon: BookOpenText,
    },
    {
        label: 'Logbook',
        description: 'Catatan kegiatan',
        href: wimsRoutes.logbook().url,
        match: (path: string) => path.startsWith('/wims/logbook'),
        icon: ClipboardList,
    },
    {
        label: 'Laporan',
        description: 'Dokumen akhir',
        href: wimsRoutes.laporan().url,
        match: (path: string) => path.startsWith('/wims/laporan'),
        icon: FileText,
    },
    {
        label: 'Profil',
        description: 'Data mahasiswa',
        href: '/wims/profil',
        match: (path: string) => path.startsWith('/wims/profil'),
        icon: UserRound,
    },
];

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <aside class="hidden lg:flex lg:w-[272px] lg:flex-shrink-0">
        <div
            class="sticky top-0 flex h-screen w-full flex-col border-r border-wims-border bg-wims-card transition-colors duration-300"
        >
            <div class="relative flex h-full flex-col px-4 py-6">
                <!-- Logo -->
                <div class="flex items-center gap-3 px-2 pb-8">
                    <div class="relative flex size-10 items-center justify-center rounded-xl border border-blue-200 bg-gradient-to-br from-blue-50 via-white to-indigo-50 text-blue-700 shadow-[0_1px_6px_-2px_rgba(0,0,0,0.06)] dark:border-blue-500/30 dark:from-blue-500/15 dark:via-slate-800 dark:to-indigo-500/10 dark:text-blue-300 dark:shadow-none">
                        <Landmark class="size-5" />
                        <GraduationCap class="absolute -right-1 -bottom-1 size-3.5 rounded-full bg-wims-card text-blue-500 dark:bg-slate-800 dark:text-blue-300" />
                    </div>
                    <div>
                        <h1 class="text-[15px] font-black uppercase tracking-[0.2em] text-blue-600 dark:text-blue-400">WIMS</h1>
                        <p class="text-[10px] font-medium tracking-wide text-slate-400 dark:text-slate-500">Student Portal</p>
                    </div>
                </div>

                <!-- Nav label -->
                <p class="mb-2.5 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400/80 dark:text-slate-500/80">
                    Menu Utama
                </p>

                <!-- Navigation -->
                <nav class="space-y-1">
                    <Link
                        v-for="item in items"
                        :key="item.label"
                        :href="item.href"
                        class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 transition-all duration-200"
                        :class="
                            item.match(currentPath)
                                ? 'bg-blue-50/80 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300'
                                : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/20 hover:text-slate-700 dark:hover:text-slate-200'
                        "
                    >
                        <!-- Active left border accent -->
                        <div
                            v-if="item.match(currentPath)"
                            class="absolute inset-y-2.5 left-0 w-[3px] rounded-full bg-blue-600 dark:bg-blue-400"
                        />

                        <!-- Icon container -->
                        <div
                            class="flex size-8 items-center justify-center rounded-lg transition-all duration-200"
                            :class="
                                item.match(currentPath)
                                    ? 'bg-blue-100/80 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400'
                                    : 'bg-slate-100/80 dark:bg-slate-700/40 text-slate-400 dark:text-slate-500 group-hover:bg-slate-200/80 dark:group-hover:bg-slate-700/60 group-hover:text-slate-600 dark:group-hover:text-slate-300'
                            "
                        >
                            <component :is="item.icon" class="size-4" />
                        </div>

                        <div class="min-w-0 flex-1">
                            <p
                                class="text-[13px] font-semibold leading-none"
                                :class="item.match(currentPath) ? 'text-blue-700 dark:text-blue-300' : ''"
                            >
                                {{ item.label }}
                            </p>
                            <p
                                class="mt-1 text-[11px] leading-none"
                                :class="item.match(currentPath) ? 'text-blue-500/80 dark:text-blue-400/80' : 'text-slate-400 dark:text-slate-500'"
                            >
                                {{ item.description }}
                            </p>
                        </div>
                    </Link>
                </nav>

                <!-- Spacer -->
                <div class="mt-auto" />

                <!-- Divider -->
                <div class="mb-4 h-px bg-wims-border/60" />

                <!-- User card -->
                <div class="rounded-xl border border-wims-border/80 bg-slate-50/80 dark:bg-slate-800/40 p-3 transition-colors duration-300">
                    <div class="flex items-center gap-3">
                        <div class="relative flex size-9 flex-shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 text-sm font-bold text-white shadow-[0_2px_8px_-4px_rgba(59,130,246,0.25)] dark:shadow-[0_2px_10px_-4px_rgba(59,130,246,0.3)]">
                            {{ userInitial }}
                            <span class="absolute -bottom-0.5 -right-0.5 size-2.5 rounded-full border-2 border-wims-card bg-emerald-500" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-[13px] font-semibold text-wims-text">
                                {{ user?.name ?? 'Mahasiswa' }}
                            </p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Mahasiswa PKL</p>
                        </div>
                        <!-- Logout -->
                        <button
                            type="button"
                            class="flex size-8 flex-shrink-0 items-center justify-center rounded-lg text-slate-400 dark:text-slate-500 transition-all duration-200 hover:bg-rose-50 dark:hover:bg-rose-500/10 hover:text-rose-500 dark:hover:text-rose-400"
                            title="Keluar"
                            @click="logout"
                        >
                            <LogOut class="size-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </aside>
</template>

