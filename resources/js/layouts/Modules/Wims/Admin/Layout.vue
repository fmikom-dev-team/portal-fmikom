<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    Building2,
    BriefcaseBusiness,
    ClipboardCheck,
    ClipboardList,
    FileCheck2,
    LayoutDashboard,
    LogOut,
    Menu,
    NotebookPen,
    ShieldCheck,
    X,
    Users,
} from 'lucide-vue-next';

type AuthUser = {
    name?: string | null;
    email?: string | null;
};

type FlashProps = {
    success?: string | null;
    error?: string | null;
};

const page = usePage<{
    auth?: {
        user?: AuthUser | null;
    };
    flash?: FlashProps;
}>();

const currentPath = computed(() => {
    const [path] = page.url.split('?');

    return path || '/';
});

const user = computed(() => page.props.auth?.user);
const flash = computed(() => page.props.flash ?? {});
const userInitials = computed(() => {
    const name = user.value?.name?.trim();

    if (!name) {
        return 'AW';
    }

    return name
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
});
const toast = ref<{
    message: string;
    type: 'success' | 'error';
} | null>(null);
const isMenuOpen = ref(false);
let initialHtmlDarkClass = false;
let initialBodyDarkClass = false;

let toastTimeout: ReturnType<typeof setTimeout> | null = null;

const syncAdminDocumentTheme = () => {
    if (typeof document === 'undefined') {
        return;
    }

    const html = document.documentElement;
    const body = document.body;

    html.classList.add('wims-role-page');
    body.classList.add('wims-role-page');
    body.classList.add('wims-role-body');
    html.classList.remove('dark');
    body.classList.remove('dark');
};

const cleanupAdminDocumentTheme = () => {
    if (typeof document === 'undefined') {
        return;
    }

    const html = document.documentElement;
    const body = document.body;

    html.classList.remove('wims-role-page');
    body.classList.remove('wims-role-page');
    body.classList.remove('wims-role-body');
    html.classList.toggle('dark', initialHtmlDarkClass);
    body.classList.toggle('dark', initialBodyDarkClass);
};

const closeMenu = () => {
    isMenuOpen.value = false;
};

const showToast = (message: string, type: 'success' | 'error') => {
    toast.value = { message, type };

    if (toastTimeout) {
        clearTimeout(toastTimeout);
    }

    toastTimeout = setTimeout(() => {
        toast.value = null;
        toastTimeout = null;
    }, 3200);
};

watch(
    flash,
    (value) => {
        if (value.error) {
            showToast(value.error, 'error');
            return;
        }

        if (value.success) {
            showToast(value.success, 'success');
        }
    },
    { immediate: true, deep: true },
);

onMounted(() => {
    if (typeof document !== 'undefined') {
        initialHtmlDarkClass = document.documentElement.classList.contains('dark');
        initialBodyDarkClass = document.body.classList.contains('dark');
    }

    syncAdminDocumentTheme();
});

onBeforeUnmount(() => {
    if (toastTimeout) {
        clearTimeout(toastTimeout);
    }

    cleanupAdminDocumentTheme();
});

watch(currentPath, () => {
    closeMenu();
    syncAdminDocumentTheme();
});

const logout = () => {
    closeMenu();
    router.post('/logout');
};

const navigationItems = [
    {
        label: 'Dashboard',
        href: '/wims/admin/dashboard',
        icon: LayoutDashboard,
        description: 'Ringkasan operasional',
        active: (path: string) => path === '/wims/admin/dashboard',
    },
    {
        label: 'Pendaftaran Magang',
        href: '/wims/admin/pendaftaran',
        icon: ClipboardCheck,
        description: 'Verifikasi pengajuan',
        active: (path: string) => path.startsWith('/wims/admin/pendaftaran'),
    },
    {
        label: 'Penempatan & Pembimbing',
        href: '/wims/admin/penempatan',
        icon: BriefcaseBusiness,
        description: 'Lokasi dan dosen',
        active: (path: string) => path.startsWith('/wims/admin/penempatan'),
    },
    {
        label: 'Monitoring Mahasiswa',
        href: '/wims/admin/monitoring',
        icon: Users,
        description: 'Presensi dan logbook',
        active: (path: string) => path.startsWith('/wims/admin/monitoring'),
    },
    {
        label: 'Rekap Nilai',
        href: '/wims/admin/rekap-nilai',
        icon: NotebookPen,
        description: 'Nilai dosen dan mitra',
        active: (path: string) => path.startsWith('/wims/admin/rekap-nilai'),
    },
    {
        label: 'Template Penilaian',
        href: '/wims/admin/penilaian-template',
        icon: ClipboardList,
        description: 'Komponen dan bobot',
        active: (path: string) => path.startsWith('/wims/admin/penilaian-template'),
    },
    {
        label: 'Perusahaan Mitra',
        href: '/wims/admin/perusahaan',
        icon: Building2,
        description: 'Lokasi, radius, dan jam kerja',
        active: (path: string) => path.startsWith('/wims/admin/perusahaan'),
    },
    {
        label: 'Validasi Laporan',
        href: '#',
        icon: FileCheck2,
        description: 'Laporan akhir magang',
        active: () => false,
        disabled: true,
    },
];

const pageHeaderMeta = [
    {
        match: (path: string) => path === '/wims/admin/dashboard',
        title: 'Dashboard Admin',
        description:
            'Ringkasan approval, penempatan, dan status dokumen untuk operasional PKL/magang.',
    },
    {
        match: (path: string) => path.startsWith('/wims/admin/perusahaan'),
        title: 'Perusahaan Mitra',
        description:
            'Kelola data perusahaan mitra, lokasi presensi, radius, jam kerja, dan akun pembimbing mitra.',
    },
    {
        match: (path: string) => path.startsWith('/wims/admin/pendaftaran'),
        title: 'Pendaftaran Magang',
        description:
            'Verifikasi pengajuan PKL/magang mahasiswa sebelum masuk tahap penempatan.',
    },
    {
        match: (path: string) => path.startsWith('/wims/admin/penempatan'),
        title: 'Penempatan & Pembimbing',
        description:
            'Atur perusahaan mitra dan dosen pembimbing untuk pengajuan yang sudah disetujui.',
    },
    {
        match: (path: string) => path.startsWith('/wims/admin/penilaian-template'),
        title: 'Template Penilaian',
        description:
            'Kelola komponen dan bobot penilaian yang digunakan oleh dosen dan mitra.',
    },
    {
        match: (path: string) => path.startsWith('/wims/admin/rekap-nilai'),
        title: 'Rekap Nilai Mahasiswa',
        description:
            'Pantau nilai dosen dan mitra secara terpisah untuk mahasiswa yang telah selesai masa PKL.',
    },
    {
        match: (path: string) => path.startsWith('/wims/admin/monitoring'),
        title: 'Monitoring Mahasiswa',
        description:
            'Pantau pelaksanaan PKL/magang, presensi, logbook, laporan akhir, dan nilai mahasiswa.',
    },
];

const activePageHeader = computed(
    () =>
        pageHeaderMeta.find((item) => item.match(currentPath.value)) ?? {
            title: 'WIMS Admin',
            description: 'Kelola operasional PKL dan magang dari satu panel.',
        },
);
</script>

<template>
    <div class="wims-shell h-screen overflow-hidden bg-wims-bg text-wims-text">
        <div class="flex h-full">
            <aside class="hidden xl:flex xl:w-[272px] xl:flex-shrink-0">
                <div class="sticky top-0 flex h-screen w-full flex-col border-r border-wims-border bg-wims-card transition-colors duration-300">
                    <div class="relative flex h-full flex-col px-4 py-6">
                        <div class="flex items-center gap-3 px-2 pb-8">
                        <div
                            class="relative flex size-10 items-center justify-center rounded-xl border border-blue-200 bg-gradient-to-br from-blue-50 via-white to-sky-50 text-blue-700 shadow-[0_1px_6px_-2px_rgba(0,0,0,0.06)]"
                        >
                            <ShieldCheck class="size-5" />
                        </div>
                        <div>
                            <p
                                class="text-[15px] font-black uppercase tracking-[0.2em] text-blue-600"
                            >
                                WIMS
                            </p>
                            <p class="mt-0.5 text-[10px] font-bold tracking-wide text-slate-400">
                                Admin Akademik
                            </p>
                        </div>
                    </div>
                    <div
                        class="mb-2.5 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400/80"
                    >
                        Menu Utama
                    </div>
                    <nav class="space-y-1">
                        <template
                            v-for="item in navigationItems"
                            :key="item.label"
                        >
                            <component
                                :is="Link"
                                v-if="!item.disabled"
                                :href="item.href"
                                class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 transition-all duration-200"
                                :class="
                                    item.active(currentPath)
                                        ? 'bg-blue-50/80 text-blue-700'
                                        : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700'
                                "
                            >
                                <div
                                    v-if="item.active(currentPath)"
                                    class="absolute inset-y-2.5 left-0 w-[3px] rounded-full bg-blue-600"
                                />
                                <div
                                    class="flex size-8 items-center justify-center rounded-lg transition-all duration-200"
                                    :class="
                                        item.active(currentPath)
                                            ? 'bg-blue-100/80 text-blue-600'
                                            : 'bg-slate-100/80 text-slate-400 group-hover:bg-slate-200/80 group-hover:text-slate-600'
                                    "
                                >
                                    <component
                                        :is="item.icon"
                                        class="size-4"
                                    />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[13px] font-bold leading-none" :class="item.active(currentPath) ? 'text-blue-700' : ''">
                                        {{ item.label }}
                                    </p>
                                    <p
                                        class="mt-1 truncate text-[11px] leading-none"
                                        :class="item.active(currentPath) ? 'text-blue-500/80' : 'text-slate-400'"
                                    >
                                        {{ item.description }}
                                    </p>
                                </div>
                            </component>
                        </template>
                    </nav>

                    <div class="mt-auto" />

                    <div class="mb-4 h-px bg-wims-border/60" />

                    <div class="rounded-xl border border-wims-border/80 bg-slate-50/80 px-3 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <div
                                    class="relative flex size-9 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 text-[11px] font-bold text-white shadow-[0_2px_8px_-4px_rgba(59,130,246,0.25)]"
                                >
                                    {{ userInitials }}
                                    <span class="absolute -bottom-0.5 -right-0.5 size-2.5 rounded-full border-2 border-wims-card bg-blue-500" />
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-[13px] font-bold text-wims-text">
                                        {{ user?.name || 'Admin WIMS' }}
                                    </p>
                                    <p class="mt-0.5 truncate text-[11px] text-slate-500">
                                        {{ user?.email || 'admin@wims.local' }}
                                    </p>
                                </div>
                            </div>
                            <button
                                type="button"
                                class="flex size-8 flex-shrink-0 items-center justify-center rounded-lg text-slate-400 transition-all duration-200 hover:bg-rose-50 hover:text-rose-500"
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

            <div class="flex min-h-screen min-w-0 flex-1 flex-col overflow-hidden">
                <header class="sticky top-0 z-20 border-b border-wims-border bg-wims-topbar backdrop-blur-sm">
                    <div
                        class="mx-auto hidden w-full max-w-[1320px] items-start px-4 py-4 sm:px-6 sm:py-5 lg:flex lg:px-8"
                    >
                        <div class="min-w-0 max-w-3xl">
                            <h1
                                class="text-[22px] font-bold tracking-tight text-slate-950 sm:text-[24px]"
                            >
                                {{ activePageHeader.title }}
                            </h1>
                            <p
                                class="mt-2 hidden max-w-2xl text-sm leading-6 text-slate-600 md:block"
                            >
                                {{ activePageHeader.description }}
                            </p>
                        </div>
                    </div>

                    <div class="mx-auto flex w-full max-w-[1320px] items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:hidden">
                        <div class="flex min-w-0 items-center gap-3">
                            <button
                                type="button"
                                class="inline-flex size-10 flex-shrink-0 items-center justify-center rounded-xl border border-wims-border/80 bg-wims-card text-slate-700 transition-all duration-200 hover:border-blue-300/60 hover:bg-blue-50/80 hover:text-blue-600"
                                aria-label="Buka menu navigasi"
                                @click="isMenuOpen = true"
                            >
                                <Menu class="size-4" />
                            </button>

                            <div class="min-w-0">
                                <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-blue-500">
                                    WIMS
                                </p>
                                <p class="mt-0.5 truncate text-[16px] font-bold text-slate-950">
                                    {{ activePageHeader.title }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex min-w-0 items-center gap-2 rounded-2xl border border-wims-border/80 bg-wims-card px-2.5 py-2"
                        >
                            <div
                                class="relative flex size-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-[11px] font-bold text-white"
                            >
                                {{ userInitials }}
                                <span class="absolute -bottom-0.5 -right-0.5 size-2.5 rounded-full border-2 border-wims-card bg-blue-500" />
                            </div>
                            <div class="min-w-0 hidden sm:block">
                                <p class="truncate text-[12px] font-bold text-slate-900">
                                    {{ user?.name || 'Admin WIMS' }}
                                </p>
                                <p class="truncate text-[10px] text-slate-500">
                                    Admin Akademik
                                </p>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="min-h-0 flex-1 overflow-y-auto">
                    <slot />
                </main>
            </div>
        </div>

        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="translate-y-2 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-2 opacity-0"
        >
            <div
                v-if="toast"
                class="fixed top-4 right-4 z-50 w-full max-w-sm rounded-xl border bg-white px-4 py-3 shadow-sm sm:top-6 sm:right-6"
                :class="
                    toast.type === 'error'
                        ? 'border-rose-200 text-rose-700'
                        : 'border-emerald-200 text-emerald-700'
                "
            >
                <p class="text-sm font-bold">
                    {{
                        toast.type === 'error' ? 'Terjadi kendala' : 'Berhasil'
                    }}
                </p>
                <p class="mt-1 text-sm leading-5">
                    {{ toast.message }}
                </p>
            </div>
        </transition>

        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="-translate-x-4 opacity-0"
            enter-to-class="translate-x-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="translate-x-0 opacity-100"
            leave-to-class="-translate-x-4 opacity-0"
        >
            <div v-if="isMenuOpen" class="fixed inset-0 z-50 xl:hidden">
                <button
                    type="button"
                    class="absolute inset-0 bg-slate-950/35"
                    aria-label="Tutup menu navigasi"
                    @click="closeMenu"
                />

                <div class="relative flex h-full w-full max-w-[304px] flex-col border-r border-wims-border bg-wims-card shadow-[0_24px_48px_-28px_rgba(15,23,42,0.32)] sm:max-w-[320px]">
                    <div class="flex items-start justify-between gap-3 px-4 py-4 sm:px-5 sm:py-5">
                        <div class="flex min-w-0 items-center gap-3">
                            <div
                                class="relative flex size-10 items-center justify-center rounded-xl border border-blue-200 bg-gradient-to-br from-blue-50 via-white to-sky-50 text-blue-700 shadow-[0_1px_6px_-2px_rgba(0,0,0,0.06)]"
                            >
                                <ShieldCheck class="size-5" />
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-[15px] font-black uppercase tracking-[0.2em] text-blue-600">
                                    WIMS
                                </p>
                                <p class="mt-0.5 truncate text-[10px] font-bold tracking-wide text-slate-400">
                                    Admin Akademik
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="inline-flex size-9 items-center justify-center rounded-xl border border-wims-border/80 bg-wims-card text-slate-700 transition-all duration-200 hover:border-blue-300/60 hover:bg-blue-50/80 hover:text-blue-600 sm:size-10"
                            aria-label="Tutup menu navigasi"
                            @click="closeMenu"
                        >
                            <X class="size-4" />
                        </button>
                    </div>

                    <div class="px-5 pb-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="relative flex size-10 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 text-sm font-bold text-white"
                            >
                                {{ userInitials }}
                                <span class="absolute -bottom-0.5 -right-0.5 size-2.5 rounded-full border-2 border-wims-card bg-blue-500" />
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-[13px] font-semibold text-slate-900">
                                    {{ user?.name || 'Admin WIMS' }}
                                </p>
                                <p class="truncate text-[11px] text-slate-500">
                                    {{ user?.email || 'admin@wims.local' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="px-4">
                        <p class="mb-2.5 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400/80">
                            Menu Utama
                        </p>
                    </div>

                    <nav class="flex-1 space-y-1 px-4 py-1">
                        <template
                            v-for="item in navigationItems"
                            :key="`mobile-drawer-${item.label}`"
                        >
                            <component
                                :is="Link"
                                v-if="!item.disabled"
                                :href="item.href"
                                class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 transition-all duration-200"
                                :class="
                                    item.active(currentPath)
                                        ? 'bg-blue-50/80 text-blue-700'
                                        : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700'
                                "
                                @click="closeMenu"
                            >
                                <div
                                    v-if="item.active(currentPath)"
                                    class="absolute inset-y-2.5 left-0 w-[3px] rounded-full bg-blue-600"
                                />
                                <div
                                    class="flex size-8 items-center justify-center rounded-lg transition-all duration-200"
                                    :class="
                                        item.active(currentPath)
                                            ? 'bg-blue-100/80 text-blue-600'
                                            : 'bg-slate-100/80 text-slate-400 group-hover:bg-slate-200/80 group-hover:text-slate-600'
                                    "
                                >
                                    <component :is="item.icon" class="size-4" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[13px] font-semibold leading-none" :class="item.active(currentPath) ? 'text-blue-700' : ''">
                                        {{ item.label }}
                                    </p>
                                    <p class="mt-1 text-[11px] leading-none" :class="item.active(currentPath) ? 'text-blue-500/80' : 'text-slate-400'">
                                        {{ item.description }}
                                    </p>
                                </div>
                            </component>
                        </template>
                    </nav>

                    <div class="mt-auto px-4 pb-4">
                        <div class="mb-4 h-px bg-wims-border/60" />
                        <div class="rounded-xl border border-wims-border/80 bg-slate-50/80 p-3">
                            <button
                                type="button"
                                class="flex w-full items-center justify-center gap-2 rounded-lg text-sm font-semibold text-rose-500 transition-all duration-200 hover:bg-rose-50"
                                @click="logout"
                            >
                                <LogOut class="size-3.5" />
                                <span>Logout</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>
