<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    Building2,
    BriefcaseBusiness,
    ClipboardCheck,
    ClipboardList,
    FileCheck2,
    FileText,
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
const siteSettings = computed(() => (page.props as any).siteSettings || {});
const brandLogo = computed<string | null>(() => {
    const logo = siteSettings.value?.brand_logo;

    return typeof logo === 'string' && logo.trim().length > 0 ? logo : null;
});

const currentPath = computed(() => {
    const [path] = page.url.split('?');

    return path || '/';
});

const user = computed(() => page.props.auth?.user);
const pageSummary = computed(() => (page.props as any).wims_sidebar_counts ?? (page.props as any).summary ?? {});
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

type NavigationItem = {
    label: string;
    href: string;
    icon: any;
    active: (path: string) => boolean;
    disabled?: boolean;
    badge?: (summary: Record<string, any>) => number;
};

const resolveNavigationBadgeCount = (item: NavigationItem) => {
    const value = item.badge?.(pageSummary.value) ?? 0;
    return Number(value) > 0 ? Number(value) : 0;
};

const isMenuOpen = ref(false);
let initialHtmlDarkClass = false;
let initialBodyDarkClass = false;

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

onMounted(() => {
    if (typeof document !== 'undefined') {
        initialHtmlDarkClass = document.documentElement.classList.contains('dark');
        initialBodyDarkClass = document.body.classList.contains('dark');
    }

    syncAdminDocumentTheme();
    cleanupToastHandlers = registerToastHandlers();
});

onBeforeUnmount(() => {
    cleanupToastHandlers?.();
    cleanupToastHandlers = null;
    cleanupAdminDocumentTheme();
});

watch(currentPath, () => {
    closeMenu();
    syncAdminDocumentTheme();
});

const registerToastHandlers = () => {
    const offException = router.on('exception', (event) => {
        const message = event.detail.exception?.message || 'Terjadi kendala pada server.';
        toast.error(message);
    });

    const onHttpError = (event: Event) => {
        const detail = (event as CustomEvent<{ message?: string }>).detail;
        const message = detail?.message || 'Terjadi kendala saat memproses permintaan.';
        toast.error(message);
    };

    window.addEventListener('pagi-http-error', onHttpError as EventListener);

    return () => {
        offException();
        window.removeEventListener('pagi-http-error', onHttpError as EventListener);
    };
};

let cleanupToastHandlers: (() => void) | null = null;
const logout = () => {
    closeMenu();
    router.post('/logout');
};

const navigationItems = [
    {
        label: 'Dashboard',
        href: '/wims/admin/dashboard',
        icon: LayoutDashboard,
        active: (path: string) => path === '/wims/admin/dashboard',
    },
    {
        label: 'Pendaftaran Magang',
        href: '/wims/admin/pendaftaran',
        icon: ClipboardCheck,
        active: (path: string) => path.startsWith('/wims/admin/pendaftaran'),
        badge: (summary: Record<string, any>) => summary.pending ?? summary.pending_registrations ?? 0,
    },
    {
        label: 'Penempatan & Pembimbing',
        href: '/wims/admin/penempatan',
        icon: BriefcaseBusiness,
        active: (path: string) => path.startsWith('/wims/admin/penempatan'),
        badge: (summary: Record<string, any>) => summary.needs_assignment ?? 0,
    },
    {
        label: 'Monitoring Mahasiswa',
        href: '/wims/admin/monitoring',
        icon: Users,
        active: (path: string) => path.startsWith('/wims/admin/monitoring'),
    },
    {
        label: 'Rekap Nilai',
        href: '/wims/admin/rekap-nilai',
        icon: NotebookPen,
        active: (path: string) => path.startsWith('/wims/admin/rekap-nilai'),
    },
    {
        label: 'Template Penilaian',
        href: '/wims/admin/penilaian-template',
        icon: ClipboardList,
        active: (path: string) => path.startsWith('/wims/admin/penilaian-template'),
    },
    {
        label: 'Template Proposal & Laporan',
        href: '/wims/admin/template-proposal-laporan',
        icon: FileText,
        active: (path: string) => path.startsWith('/wims/admin/template-proposal-laporan'),
    },
    {
        label: 'Perusahaan Mitra',
        href: '/wims/admin/perusahaan',
        icon: Building2,
        active: (path: string) => path.startsWith('/wims/admin/perusahaan'),
    },
    {
        label: 'Validasi Laporan',
        href: '#',
        icon: FileCheck2,
        active: () => false,
        disabled: true,
    },
];

const pageHeaderMeta = [
    {
        match: (path: string) => path === '/wims/admin/dashboard',
        title: 'Dashboard Admin',
    },
    {
        match: (path: string) => path.startsWith('/wims/admin/perusahaan'),
        title: 'Perusahaan Mitra',
    },
    {
        match: (path: string) => path.startsWith('/wims/admin/pendaftaran'),
        title: 'Pendaftaran Magang',
    },
    {
        match: (path: string) => path.startsWith('/wims/admin/penempatan'),
        title: 'Penempatan & Pembimbing',
    },
    {
        match: (path: string) => path.startsWith('/wims/admin/penilaian-template'),
        title: 'Template Penilaian',
    },
    {
        match: (path: string) => path.startsWith('/wims/admin/template-proposal-laporan'),
        title: 'Template Proposal & Laporan',
    },
    {
        match: (path: string) => path.startsWith('/wims/admin/rekap-nilai'),
        title: 'Rekap Nilai Mahasiswa',
    },
    {
        match: (path: string) => path.startsWith('/wims/admin/monitoring'),
        title: 'Monitoring Mahasiswa',
    },
];

const activePageHeader = computed(
    () =>
        pageHeaderMeta.find((item) => item.match(currentPath.value)) ?? {
            title: 'WIMS Admin',
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
                                class="relative flex size-10 items-center justify-center overflow-hidden rounded-xl border border-blue-200 bg-gradient-to-br from-blue-50 via-white to-sky-50 text-blue-700 shadow-[0_1px_6px_-2px_rgba(0,0,0,0.06)]"
                            >
                                <img
                                    v-if="brandLogo"
                                    :src="brandLogo"
                                    alt="Brand Logo"
                                    class="h-full w-full object-contain"
                                    loading="eager"
                                    decoding="async"
                                />
                                <ShieldCheck v-else class="size-5" />
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
                                    <div class="flex items-center gap-2">
                                        <p class="text-[13px] font-bold leading-none" :class="item.active(currentPath) ? 'text-blue-700' : ''">
                                            {{ item.label }}
                                        </p>
                                        <span
                                            v-if="resolveNavigationBadgeCount(item) > 0"
                                            class="inline-flex min-w-5 items-center justify-center rounded-full bg-blue-600 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white"
                                        >
                                            {{ resolveNavigationBadgeCount(item) }}
                                        </span>
                                    </div>
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
                <header class="sticky top-0 z-20 border-b border-wims-border bg-wims-topbar backdrop-blur-sm" style="padding-top: env(safe-area-inset-top);">
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
                                class="relative flex size-10 items-center justify-center overflow-hidden rounded-xl border border-blue-200 bg-gradient-to-br from-blue-50 via-white to-sky-50 text-blue-700 shadow-[0_1px_6px_-2px_rgba(0,0,0,0.06)]"
                            >
                                <img
                                    v-if="brandLogo"
                                    :src="brandLogo"
                                    alt="Brand Logo"
                                    class="h-full w-full object-contain"
                                    loading="eager"
                                    decoding="async"
                                />
                                <ShieldCheck v-else class="size-5" />
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
                                    <div class="flex items-center gap-2">
                                        <p class="text-[13px] font-semibold leading-none" :class="item.active(currentPath) ? 'text-blue-700' : ''">
                                            {{ item.label }}
                                        </p>
                                        <span
                                            v-if="resolveNavigationBadgeCount(item) > 0"
                                            class="inline-flex min-w-5 items-center justify-center rounded-full bg-blue-600 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white"
                                        >
                                            {{ resolveNavigationBadgeCount(item) }}
                                        </span>
                                    </div>
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






