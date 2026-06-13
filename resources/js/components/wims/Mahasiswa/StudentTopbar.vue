<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    Bell, CheckCheck, CircleAlert, LogOut, Moon, RefreshCw, Sun, X,
} from 'lucide-vue-next';
import { useAppearance } from '@/composables/useAppearance';

const page = usePage();
const { appearance, resolvedAppearance, updateAppearance } = useAppearance();

const currentPath = computed(() => {
    const [path] = page.url.split('?');
    return path || '/';
});

const user = computed(() => page.props.auth?.user ?? null);
const showNotifications = ref(false);
const showProfileDropdown = ref(false);
const isRefreshing = ref(false);

const alerts = computed(() => {
    const raw = page.props.alerts;
    return Array.isArray(raw)
        ? raw.filter(
              (item): item is { id?: string | number | null; message?: string | null; type?: string | null; title?: string | null; created_at?: string | null } =>
                  item !== null && typeof item === 'object',
          )
        : [];
});
const hasAlerts = computed(() => alerts.value.length > 0);

const getAlertType = (alert: { type?: string | null; message?: string | null }) => {
    const t = alert.type?.toLowerCase() ?? '';
    const m = alert.message?.toLowerCase() ?? '';
    if (t === 'success' || m.includes('berhasil') || m.includes('sukses')) return 'success';
    if (t === 'warning' || m.includes('peringatan') || m.includes('batas')) return 'warning';
    return 'info';
};

const getAlertTitle = (alert: { title?: string | null; type?: string | null; message?: string | null }) => {
    if (alert.title) return alert.title;
    const type = getAlertType(alert);
    if (type === 'success') return 'Berhasil';
    if (type === 'warning') return 'Peringatan';
    return 'Pengingat Harian';
};

const getRelativeTime = (dateStr?: string | null) => {
    if (!dateStr) return 'Baru saja';
    const diff = Date.now() - new Date(dateStr).getTime();
    const mins = Math.floor(diff / 60000);
    if (mins < 1) return 'Baru saja';
    if (mins < 60) return `${mins} menit lalu`;
    const hrs = Math.floor(mins / 60);
    if (hrs < 24) return `${hrs} jam lalu`;
    return `${Math.floor(hrs / 24)} hari lalu`;
};

const markAllRead = () => {
    // Placeholder — emit or call API to mark all as read
};

const title = computed(() => {
    const path = currentPath.value;
    if (path === '/wims/dashboard') return 'Dashboard';
    if (path.startsWith('/wims/absensi')) return 'Presensi';
    if (path.startsWith('/wims/pendaftaran')) return 'Pendaftaran';
    if (path.startsWith('/wims/logbook')) return 'Logbook';
    if (path.startsWith('/wims/laporan')) return 'Laporan';
    if (path.startsWith('/wims/profil')) return 'Profil';
    return 'Student Portal';
});

const subtitle = computed(() => {
    const path = currentPath.value;
    if (path === '/wims/dashboard') return 'Ringkasan mahasiswa';
    if (path.startsWith('/wims/absensi')) return 'Presensi magang';
    if (path.startsWith('/wims/pendaftaran')) return 'Siklus pendaftaran';
    if (path.startsWith('/wims/logbook')) return 'Aktivitas harian';
    if (path.startsWith('/wims/laporan')) return 'Dokumen akhir';
    if (path.startsWith('/wims/profil')) return 'Data mahasiswa';
    return 'Portal mahasiswa FMIKOM';
});

const userInitial = computed(() =>
    String(user.value?.name ?? 'M').trim().charAt(0).toUpperCase(),
);
const userAvatar = computed<string | null>(() => {
    const avatar = user.value?.avatar ?? user.value?.photo_url ?? user.value?.foto_url ?? null;
    if (avatar && typeof avatar === 'string' && avatar.trim().length > 0) return avatar;
    return null;
});

const refreshData = () => {
    if (isRefreshing.value) return;
    isRefreshing.value = true;
    router.reload({
        onFinish: () => {
            setTimeout(() => {
                isRefreshing.value = false;
            }, 600);
        },
    });
};

const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value;
    showProfileDropdown.value = false;
};
const toggleProfileDropdown = () => {
    showProfileDropdown.value = !showProfileDropdown.value;
    showNotifications.value = false;
};
const toggleTheme = () => {
    if (appearance.value === 'light') updateAppearance('dark');
    else if (appearance.value === 'dark') updateAppearance('light');
    else updateAppearance(prefersDark() ? 'light' : 'dark');
};
const prefersDark = () => {
    if (typeof window === 'undefined') return false;
    return window.matchMedia('(prefers-color-scheme: dark)').matches;
};
const logout = () => {
    router.post('/logout');
};

watch(currentPath, () => {
    showNotifications.value = false;
    showProfileDropdown.value = false;
});
</script>

<template>
    <!-- ======== MOBILE TOPBAR ======== -->
    <header class="sticky top-0 z-30 border-b border-wims-border/80 bg-wims-topbar backdrop-blur-xl lg:hidden transition-colors duration-300">
        <div class="flex items-center justify-between gap-3 px-4 py-3">
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-blue-500 dark:text-blue-400">WIMS</p>
                <p class="mt-0.5 text-[15px] font-bold text-wims-text">{{ title }}</p>
            </div>

            <div class="flex items-center gap-2">
                <!-- Theme Toggle -->
                <button
                    type="button"
                    class="flex size-9 items-center justify-center rounded-xl border border-wims-border/80 bg-wims-card text-slate-500 dark:text-slate-400 transition-all duration-200 hover:border-blue-300/60 dark:hover:border-blue-500/30 hover:bg-blue-50/80 dark:hover:bg-blue-500/10 hover:text-blue-600 dark:hover:text-blue-400 active:scale-95"
                    aria-label="Toggle tema"
                    @click="toggleTheme"
                >
                    <Sun v-if="resolvedAppearance === 'dark'" class="size-4" />
                    <Moon v-else class="size-4" />
                </button>

                <!-- Refresh Data -->
                <button
                    type="button"
                    class="flex size-9 items-center justify-center rounded-xl border border-wims-border/80 bg-wims-card text-slate-500 dark:text-slate-400 transition-all duration-200 hover:border-blue-300/60 dark:hover:border-blue-500/30 hover:bg-blue-50/80 dark:hover:bg-blue-500/10 hover:text-blue-600 dark:hover:text-blue-400 active:scale-95 disabled:opacity-50"
                    aria-label="Refresh data"
                    :disabled="isRefreshing"
                    @click="refreshData"
                >
                    <RefreshCw class="size-4 transition-transform duration-500" :class="isRefreshing ? 'animate-spin' : ''" />
                </button>

                <!-- Notification Bell -->
                <div class="relative">
                    <button
                        type="button"
                        class="relative flex size-9 items-center justify-center rounded-xl border border-wims-border/80 bg-wims-card text-slate-500 dark:text-slate-400 transition-all duration-200 hover:border-blue-300/60 dark:hover:border-blue-500/30 hover:bg-blue-50/80 dark:hover:bg-blue-500/10 hover:text-blue-600 dark:hover:text-blue-400 active:scale-95"
                        aria-label="Notifikasi"
                        @click="toggleNotifications"
                    >
                        <Bell class="size-4" />
                        <span
                            v-if="hasAlerts"
                            class="absolute right-1.5 top-1.5 size-2 rounded-full bg-rose-500 ring-2 ring-wims-topbar shadow-[0_0_6px_rgba(239,68,68,0.6)]"
                        />
                    </button>

                    <!-- Notification panel mobile -->
                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 -translate-y-2 scale-95"
                        enter-to-class="opacity-100 translate-y-0 scale-100"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="opacity-100 translate-y-0 scale-100"
                        leave-to-class="opacity-0 -translate-y-2 scale-95"
                    >
                        <div
                            v-if="showNotifications"
                            class="fixed inset-x-4 top-[60px] z-50 overflow-hidden rounded-2xl border border-wims-border/80 bg-wims-card/95 shadow-[0_12px_40px_-12px_rgba(0,0,0,0.12)] dark:shadow-[0_12px_48px_-12px_rgba(0,0,0,0.5)] backdrop-blur-2xl sm:absolute sm:inset-x-auto sm:right-0 sm:top-12 sm:w-[360px]"
                        >
                            <!-- Header -->
                            <div class="flex items-center justify-between border-b border-wims-border/60 px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-bold text-wims-text">Notifikasi</p>
                                    <span v-if="hasAlerts" class="flex size-5 items-center justify-center rounded-full bg-blue-500 text-[10px] font-bold text-white">{{ alerts.length }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button
                                        v-if="hasAlerts"
                                        type="button"
                                        class="rounded-lg px-2 py-1 text-[10px] font-semibold text-blue-600 dark:text-blue-400 hover:bg-blue-50/80 dark:hover:bg-blue-500/10 transition-colors"
                                        @click="markAllRead"
                                    >
                                        Tandai dibaca
                                    </button>
                                    <button
                                        class="flex size-7 items-center justify-center rounded-lg text-slate-400 dark:text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700/40 hover:text-slate-600 dark:hover:text-slate-300 transition-colors"
                                        @click="showNotifications = false"
                                    >
                                        <X class="size-3.5" />
                                    </button>
                                </div>
                            </div>

                            <!-- Alert items -->
                            <div v-if="hasAlerts" class="max-h-[320px] overflow-y-auto overscroll-contain p-1.5 scrollbar-thin">
                                <div
                                    v-for="(alert, idx) in alerts"
                                    :key="String(alert.id ?? alert.message ?? idx)"
                                    class="group relative flex gap-3 rounded-xl px-3 py-3 transition-colors duration-200 hover:bg-slate-50/80 dark:hover:bg-slate-700/20"
                                >
                                    <!-- Unread dot -->
                                    <span class="absolute left-1 top-1/2 -translate-y-1/2 size-1.5 rounded-full bg-blue-500 dark:bg-blue-400" />

                                    <!-- Type icon -->
                                    <div
                                        class="flex size-9 flex-shrink-0 items-center justify-center rounded-full"
                                        :class="{
                                            'bg-blue-100 dark:bg-blue-500/15 text-blue-600 dark:text-blue-400': getAlertType(alert) === 'info',
                                            'bg-amber-100 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400': getAlertType(alert) === 'warning',
                                            'bg-emerald-100 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400': getAlertType(alert) === 'success',
                                        }"
                                    >
                                        <CircleAlert v-if="getAlertType(alert) === 'info'" class="size-4" />
                                        <CircleAlert v-else-if="getAlertType(alert) === 'warning'" class="size-4" />
                                        <CheckCheck v-else class="size-4" />
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="text-[13px] font-bold text-wims-text leading-tight">{{ getAlertTitle(alert) }}</p>
                                        <p class="mt-0.5 text-[12px] text-slate-600 dark:text-slate-400 leading-relaxed line-clamp-2">{{ alert.message ?? 'Pengingat baru tersedia.' }}</p>
                                        <p class="mt-1 text-[10px] text-slate-400 dark:text-slate-500">{{ getRelativeTime(alert.created_at) }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty state -->
                            <div v-else class="px-4 py-8 text-center">
                                <div class="mx-auto mb-3 flex size-14 items-center justify-center rounded-2xl bg-slate-100/80 dark:bg-slate-700/30">
                                    <Bell class="size-6 text-slate-300 dark:text-slate-600" />
                                </div>
                                <p class="text-[13px] font-semibold text-wims-text mb-0.5">Semua sudah dibaca</p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500">Tidak ada notifikasi baru saat ini</p>
                            </div>

                            <!-- Footer -->
                            <div class="border-t border-wims-border/40 p-2">
                                <Link
                                    href="/wims/dashboard"
                                    class="flex w-full items-center justify-center gap-1.5 rounded-xl py-2 text-[12px] font-semibold text-blue-600 dark:text-blue-400 hover:bg-blue-50/80 dark:hover:bg-blue-500/10 transition-colors"
                                    @click="showNotifications = false"
                                >
                                    Lihat semua notifikasi
                                </Link>
                            </div>
                        </div>
                    </Transition>
                </div>

                <!-- User avatar -->
                <div class="relative">
                    <button
                        type="button"
                        class="flex size-9 items-center justify-center overflow-hidden rounded-xl border border-blue-200/80 dark:border-blue-500/25 bg-gradient-to-br from-blue-500 to-blue-600 text-[11px] font-bold text-white shadow-[0_0_12px_rgba(59,130,246,0.25)] dark:shadow-[0_0_12px_rgba(59,130,246,0.15)] transition-all active:scale-95"
                        @click="toggleProfileDropdown"
                    >
                        <img v-if="userAvatar" :src="userAvatar" alt="Avatar" class="h-full w-full object-cover" />
                        <span v-else>{{ userInitial }}</span>
                    </button>

                    <!-- Profile dropdown mobile -->
                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 -translate-y-2 scale-95"
                        enter-to-class="opacity-100 translate-y-0 scale-100"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="opacity-100 translate-y-0 scale-100"
                        leave-to-class="opacity-0 -translate-y-2 scale-95"
                    >
                        <div
                            v-if="showProfileDropdown"
                            class="absolute right-0 top-12 z-50 w-48 overflow-hidden rounded-2xl border border-wims-border/80 bg-wims-card/95 shadow-[0_8px_32px_-8px_rgba(0,0,0,0.1)] dark:shadow-[0_8px_40px_-8px_rgba(0,0,0,0.45)] backdrop-blur-2xl"
                        >
                            <div class="p-1.5">
                                <Link
                                    href="/wims/profil"
                                    class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium text-wims-text hover:bg-slate-50/80 dark:hover:bg-slate-700/25 transition-colors"
                                    @click="showProfileDropdown = false"
                                >
                                    <div class="flex size-7 items-center justify-center rounded-lg bg-blue-50/80 dark:bg-blue-500/12 text-blue-600 dark:text-blue-400">
                                        <div class="size-3.5 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 text-[9px] font-bold text-white flex items-center justify-center">
                                            {{ userInitial }}
                                        </div>
                                    </div>
                                    <span>Profil</span>
                                </Link>
                                <button
                                    type="button"
                                    class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-50/80 dark:hover:bg-rose-500/10 transition-colors"
                                    @click="logout"
                                >
                                    <div class="flex size-7 items-center justify-center rounded-lg bg-rose-50/80 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400">
                                        <LogOut class="size-3.5" />
                                    </div>
                                    <span>Keluar</span>
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>
    </header>

    <!-- ======== DESKTOP TOPBAR ======== -->
    <header class="sticky top-0 z-30 hidden border-b border-wims-border/80 bg-wims-topbar backdrop-blur-xl lg:block transition-colors duration-300">
        <div class="mx-auto flex w-full max-w-[1320px] items-center gap-6 px-8 py-3 xl:px-10">
            <!-- Breadcrumb / Title -->
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-[0.14em] text-blue-500 dark:text-blue-400">WIMS</span>
                    <span class="text-slate-300 dark:text-slate-600">/</span>
                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">{{ subtitle }}</span>
                </div>
                <p class="mt-0.5 text-base font-bold text-wims-text">{{ title }}</p>
            </div>

            <div class="flex items-center gap-2.5">
                <!-- Theme Toggle -->
                <button
                    type="button"
                    class="flex size-9 items-center justify-center rounded-xl border border-wims-border/80 bg-wims-card text-slate-500 dark:text-slate-400 transition-all duration-200 hover:border-blue-300/60 dark:hover:border-blue-500/30 hover:bg-blue-50/80 dark:hover:bg-blue-500/10 hover:text-blue-600 dark:hover:text-blue-400 active:scale-95"
                    aria-label="Toggle tema"
                    @click="toggleTheme"
                >
                    <Sun v-if="resolvedAppearance === 'dark'" class="size-4" />
                    <Moon v-else class="size-4" />
                </button>

                <!-- Refresh Data -->
                <button
                    type="button"
                    class="flex size-9 items-center justify-center rounded-xl border border-wims-border/80 bg-wims-card text-slate-500 dark:text-slate-400 transition-all duration-200 hover:border-blue-300/60 dark:hover:border-blue-500/30 hover:bg-blue-50/80 dark:hover:bg-blue-500/10 hover:text-blue-600 dark:hover:text-blue-400 active:scale-95 disabled:opacity-50"
                    aria-label="Refresh data"
                    title="Refresh data"
                    :disabled="isRefreshing"
                    @click="refreshData"
                >
                    <RefreshCw class="size-4 transition-transform duration-500" :class="isRefreshing ? 'animate-spin' : ''" />
                </button>

                <!-- Notification Bell -->
                <div class="relative">
                    <button
                        type="button"
                        class="relative flex size-9 items-center justify-center rounded-xl border border-wims-border/80 bg-wims-card text-slate-500 dark:text-slate-400 transition-all duration-200 hover:border-blue-300/60 dark:hover:border-blue-500/30 hover:bg-blue-50/80 dark:hover:bg-blue-500/10 hover:text-blue-600 dark:hover:text-blue-400 active:scale-95"
                        @click="toggleNotifications"
                    >
                        <Bell class="size-4" />
                        <span
                            v-if="hasAlerts"
                            class="absolute right-1.5 top-1.5 size-2 rounded-full bg-rose-500 ring-2 ring-wims-topbar shadow-[0_0_6px_rgba(239,68,68,0.6)]"
                        />
                    </button>

                    <!-- Notification panel desktop -->
                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 -translate-y-2 scale-95"
                        enter-to-class="opacity-100 translate-y-0 scale-100"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="opacity-100 translate-y-0 scale-100"
                        leave-to-class="opacity-0 -translate-y-2 scale-95"
                    >
                        <div
                            v-if="showNotifications"
                            class="absolute right-0 top-12 z-50 w-[380px] overflow-hidden rounded-2xl border border-wims-border/80 bg-wims-card/95 shadow-[0_12px_40px_-12px_rgba(0,0,0,0.1)] dark:shadow-[0_12px_48px_-12px_rgba(0,0,0,0.5)] backdrop-blur-2xl"
                        >
                            <!-- Header -->
                            <div class="flex items-center justify-between border-b border-wims-border/60 px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-bold text-wims-text">Notifikasi</p>
                                    <span v-if="hasAlerts" class="flex size-5 items-center justify-center rounded-full bg-blue-500 text-[10px] font-bold text-white">{{ alerts.length }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button
                                        v-if="hasAlerts"
                                        type="button"
                                        class="rounded-lg px-2 py-1 text-[10px] font-semibold text-blue-600 dark:text-blue-400 hover:bg-blue-50/80 dark:hover:bg-blue-500/10 transition-colors"
                                        @click="markAllRead"
                                    >
                                        Tandai dibaca
                                    </button>
                                    <button
                                        class="flex size-7 items-center justify-center rounded-lg text-slate-400 dark:text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700/40 hover:text-slate-600 dark:hover:text-slate-300 transition-colors"
                                        @click="showNotifications = false"
                                    >
                                        <X class="size-3.5" />
                                    </button>
                                </div>
                            </div>

                            <!-- Alert items -->
                            <div v-if="hasAlerts" class="max-h-[320px] overflow-y-auto overscroll-contain p-1.5 scrollbar-thin">
                                <div
                                    v-for="(alert, idx) in alerts"
                                    :key="`desktop-${String(alert.id ?? alert.message ?? idx)}`"
                                    class="group relative flex gap-3 rounded-xl px-3 py-3 transition-colors duration-200 hover:bg-slate-50/80 dark:hover:bg-slate-700/20"
                                >
                                    <!-- Unread dot -->
                                    <span class="absolute left-1 top-1/2 -translate-y-1/2 size-1.5 rounded-full bg-blue-500 dark:bg-blue-400" />

                                    <!-- Type icon -->
                                    <div
                                        class="flex size-9 flex-shrink-0 items-center justify-center rounded-full"
                                        :class="{
                                            'bg-blue-100 dark:bg-blue-500/15 text-blue-600 dark:text-blue-400': getAlertType(alert) === 'info',
                                            'bg-amber-100 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400': getAlertType(alert) === 'warning',
                                            'bg-emerald-100 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400': getAlertType(alert) === 'success',
                                        }"
                                    >
                                        <CircleAlert v-if="getAlertType(alert) === 'info'" class="size-4" />
                                        <CircleAlert v-else-if="getAlertType(alert) === 'warning'" class="size-4" />
                                        <CheckCheck v-else class="size-4" />
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="text-[13px] font-bold text-wims-text leading-tight">{{ getAlertTitle(alert) }}</p>
                                        <p class="mt-0.5 text-[12px] text-slate-600 dark:text-slate-400 leading-relaxed line-clamp-2">{{ alert.message ?? 'Pengingat baru tersedia.' }}</p>
                                        <p class="mt-1 text-[10px] text-slate-400 dark:text-slate-500">{{ getRelativeTime(alert.created_at) }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty state -->
                            <div v-else class="px-4 py-8 text-center">
                                <div class="mx-auto mb-3 flex size-14 items-center justify-center rounded-2xl bg-slate-100/80 dark:bg-slate-700/30">
                                    <Bell class="size-6 text-slate-300 dark:text-slate-600" />
                                </div>
                                <p class="text-[13px] font-semibold text-wims-text mb-0.5">Semua sudah dibaca</p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500">Tidak ada notifikasi baru saat ini</p>
                            </div>

                            <!-- Footer -->
                            <div class="border-t border-wims-border/40 p-2">
                                <Link
                                    href="/wims/dashboard"
                                    class="flex w-full items-center justify-center gap-1.5 rounded-xl py-2 text-[12px] font-semibold text-blue-600 dark:text-blue-400 hover:bg-blue-50/80 dark:hover:bg-blue-500/10 transition-colors"
                                    @click="showNotifications = false"
                                >
                                    Lihat semua notifikasi
                                </Link>
                            </div>
                        </div>
                    </Transition>
                </div>

                <!-- Divider -->
                <div class="h-6 w-px bg-wims-border/60" />

                <!-- User profile chip -->
                <Link
                    href="/wims/profil"
                    class="flex items-center gap-2.5 rounded-xl border border-wims-border/80 bg-wims-card px-3 py-1.5 transition-all duration-200 hover:border-blue-300/60 dark:hover:border-blue-500/25 hover:bg-blue-50/60 dark:hover:bg-blue-500/[0.06]"
                >
                    <div class="flex size-8 flex-shrink-0 items-center justify-center overflow-hidden rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 text-xs font-bold text-white shadow-[0_0_10px_rgba(59,130,246,0.2)] dark:shadow-[0_0_10px_rgba(59,130,246,0.15)]">
                        <img v-if="userAvatar" :src="userAvatar" alt="Avatar" class="h-full w-full object-cover" />
                        <span v-else>{{ userInitial }}</span>
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-[13px] font-semibold text-wims-text">{{ user?.name ?? 'Mahasiswa' }}</p>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500">Mahasiswa PKL</p>
                    </div>
                </Link>

                <!-- Logout Button Desktop -->
                <button
                    type="button"
                    class="flex size-9 items-center justify-center rounded-xl border border-wims-border/80 bg-wims-card text-slate-400 dark:text-slate-500 transition-all duration-200 hover:border-rose-300/60 dark:hover:border-rose-500/30 hover:bg-rose-50/80 dark:hover:bg-rose-500/10 hover:text-rose-500 dark:hover:text-rose-400 active:scale-95"
                    title="Keluar"
                    @click="logout"
                >
                    <LogOut class="size-4" />
                </button>
            </div>
        </div>
    </header>
</template>