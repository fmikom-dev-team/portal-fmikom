<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Bell, BriefcaseBusiness, CheckCheck, CircleAlert, GraduationCap, LogOut, Menu, RefreshCw, UserRound, X } from 'lucide-vue-next';
import AppToast from '@/pages/WorkOs/components/ui/AppToast.vue';

type AuthUser = {
    name?: string | null;
    email?: string | null;
    avatar?: string | null;
    photo_url?: string | null;
};

type NavigationItem = {
    label: string;
    href: string;
    icon: unknown;
    matches?: string[];
    description?: string;
};

type HeaderNotificationItem = {
    id: string;
    title: string;
    description: string;
    actionLabel?: string | null;
    tone: 'amber' | 'blue' | 'rose' | 'slate' | 'emerald';
    targetType?: 'section' | 'assessment-index';
    target?: string;
};

const props = defineProps<{
    brand: string;
    subtitle: string;
    navigationItems: NavigationItem[];
    brandStyle?: 'wims' | 'company';
}>();

const page = usePage<{
    auth?: {
        user?: AuthUser | null;
    };
    summary?: Record<string, number | null | undefined>;
    students?: Array<Record<string, unknown>>;
    warnings?: Array<Record<string, unknown>>;
    reviewBoard?: Array<Record<string, unknown>>;
    pendingAbsenceRequests?: Array<Record<string, unknown>>;
}>();

const isMenuOpen = ref(false);
const isRefreshing = ref(false);
const isNotificationOpen = ref(false);
const isProfileMenuOpen = ref(false);
const desktopNotificationMenuRef = ref<HTMLElement | null>(null);
const mobileNotificationMenuRef = ref<HTMLElement | null>(null);
const profileMenuRef = ref<HTMLElement | null>(null);
let initialHtmlDarkClass = false;
let initialBodyDarkClass = false;

const currentPath = computed(() => {
    const [path] = page.url.split('?');

    return path || '/';
});

const user = computed(() => page.props.auth?.user);
const userAvatar = computed(() => {
    const avatar = user.value?.avatar ?? user.value?.photo_url ?? null;

    return typeof avatar === 'string' && avatar.trim().length > 0 ? avatar : null;
});
const userInitials = computed(() => {
    const name = user.value?.name?.trim();

    if (!name) {
        return 'WM';
    }

    return name
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
});

const isActive = (item: NavigationItem) => {
    if (currentPath.value === item.href) {
        return true;
    }

    return (item.matches ?? []).some((prefix) => currentPath.value.startsWith(prefix));
};

const closeMenu = () => {
    isMenuOpen.value = false;
};

const logout = () => {
    closeMenu();
    closeNotifications();
    closeProfileMenu();
    router.post('/logout');
};

const refreshData = () => {
    if (isRefreshing.value) {
        return;
    }

    isRefreshing.value = true;
    router.reload({
        onFinish: () => {
            setTimeout(() => {
                isRefreshing.value = false;
            }, 500);
        },
    });
};

const isCompanyBrand = computed(() => props.brandStyle === 'company');
const headerRoleLabel = computed(() =>
    isCompanyBrand.value ? 'Pembimbing Mitra' : 'Dosen Pembimbing',
);
const dashboardHref = computed(() =>
    isCompanyBrand.value ? '/wims/mitra/dashboard' : '/wims/dosen/dashboard',
);
const isDashboardPage = computed(() =>
    currentPath.value === '/wims/dosen/dashboard' || currentPath.value === '/wims/mitra/dashboard',
);

const summary = computed(() => page.props.summary ?? {});
const warnings = computed(() => Array.isArray(page.props.warnings) ? page.props.warnings : []);
const students = computed(() => Array.isArray(page.props.students) ? page.props.students : []);
const reviewBoard = computed(() => Array.isArray(page.props.reviewBoard) ? page.props.reviewBoard : []);
const pendingAbsenceRequests = computed(() =>
    Array.isArray(page.props.pendingAbsenceRequests) ? page.props.pendingAbsenceRequests : [],
);

const notificationItems = computed<HeaderNotificationItem[]>(() => {
    const items: HeaderNotificationItem[] = [];
    const firstWarning = warnings.value[0];

    if (isCompanyBrand.value) {
        const notPresentToday = Number(summary.value.not_present_today ?? 0);
        const notEvaluated = Number(summary.value.not_evaluated ?? 0);

        if (pendingAbsenceRequests.value.length > 0) {
            items.push({
                id: 'pending-absence',
                title: `${pendingAbsenceRequests.value.length} pengajuan izin menunggu`,
                description: 'Segera setujui atau tolak izin dan sakit yang masih menunggu keputusan.',
                actionLabel: 'Tinjau',
                tone: 'amber',
                targetType: 'section',
                target: 'pending-absence-section',
            });
        }

        if (warnings.value.length > 0) {
            const warningName = typeof firstWarning?.name === 'string' ? firstWarning.name : 'Mahasiswa';

            items.push({
                id: 'warnings',
                title: `${warnings.value.length} mahasiswa perlu tindak lanjut`,
                description: `${warningName} memerlukan perhatian untuk presensi atau logbook.`,
                actionLabel: 'Periksa',
                tone: 'rose',
                targetType: 'section',
                target: 'warning-section',
            });
        }

        if (notPresentToday > 0) {
            items.push({
                id: 'attendance',
                title: `${notPresentToday} mahasiswa belum presensi`,
                description: 'Cek board presensi hari ini untuk melihat siapa yang belum check-in.',
                actionLabel: 'Lihat Presensi',
                tone: 'blue',
                targetType: 'section',
                target: 'attendance-board-section',
            });
        }

        if (reviewBoard.value.length > 0) {
            items.push({
                id: 'review-board',
                title: `${reviewBoard.value.length} logbook menunggu review`,
                description: 'Prioritaskan review harian atau revisi logbook yang masih antre.',
                actionLabel: 'Review',
                tone: 'amber',
                targetType: 'section',
                target: 'review-board-section',
            });
        }

        if (notEvaluated > 0) {
            items.push({
                id: 'assessment',
                title: `${notEvaluated} mahasiswa selesai belum dinilai`,
                description: 'Lengkapi penilaian mitra agar proses akhir mahasiswa tidak tertunda.',
                actionLabel: 'Isi Nilai',
                tone: 'slate',
                targetType: 'assessment-index',
            });
        }
    } else {
        const alfaStudents = students.value.filter(
            (student) => student?.attendance_status === 'alfa' && student?.dashboard_phase === 'active',
        );
        const logbookQueue = students.value.filter((student) =>
            ['belum_isi', 'revisi', 'menunggu_review'].includes(String(student?.logbook_status ?? '')),
        );
        const notEvaluated = Number(summary.value.not_evaluated ?? 0);

        if (warnings.value.length > 0) {
            const warningName = typeof firstWarning?.name === 'string' ? firstWarning.name : 'Mahasiswa';

            items.push({
                id: 'warnings',
                title: `${warnings.value.length} mahasiswa perlu monitoring`,
                description: `${warningName} memerlukan perhatian untuk presensi atau logbook.`,
                actionLabel: 'Periksa',
                tone: 'rose',
                targetType: 'section',
                target: 'warning-section',
            });
        }

        if (alfaStudents.length > 0) {
            items.push({
                id: 'attendance-alfa',
                title: `${alfaStudents.length} mahasiswa alfa hari ini`,
                description: 'Cek board presensi hari ini untuk melihat mahasiswa yang belum hadir.',
                actionLabel: 'Lihat Presensi',
                tone: 'blue',
                targetType: 'section',
                target: 'attendance-board-section',
            });
        }

        if (logbookQueue.length > 0) {
            items.push({
                id: 'logbook-board',
                title: `${logbookQueue.length} logbook perlu tindak lanjut`,
                description: 'Pantau ringkasan logbook terbaru mahasiswa aktif dari dashboard.',
                actionLabel: 'Lihat Logbook',
                tone: 'amber',
                targetType: 'section',
                target: 'logbook-board-section',
            });
        }

        if (notEvaluated > 0) {
            items.push({
                id: 'assessment',
                title: `${notEvaluated} mahasiswa selesai belum dinilai`,
                description: 'Lengkapi penilaian dosen agar proses akhir mahasiswa tidak tertunda.',
                actionLabel: 'Isi Nilai',
                tone: 'slate',
                targetType: 'assessment-index',
            });
        }
    }

    return items;
});

const hasNotifications = computed(() => notificationItems.value.length > 0);
const visibleNotificationItems = computed(() => notificationItems.value.slice(0, 4));
const remainingNotificationCount = computed(() =>
    Math.max(notificationItems.value.length - visibleNotificationItems.value.length, 0),
);

const notificationToneClass = (tone: HeaderNotificationItem['tone']) => {
    if (tone === 'rose') {
        return 'border-rose-200 bg-rose-50 text-rose-700';
    }

    if (tone === 'blue') {
        return 'border-blue-200 bg-blue-50 text-blue-700';
    }

    if (tone === 'amber') {
        return 'border-amber-200 bg-amber-50 text-amber-700';
    }

    if (tone === 'emerald') {
        return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    }

    return 'border-slate-200 bg-slate-50 text-slate-700';
};

const getNotificationIconClass = (tone: HeaderNotificationItem['tone']) => {
    if (tone === 'rose') {
        return 'bg-rose-100 text-rose-600';
    }

    if (tone === 'blue') {
        return 'bg-blue-100 text-blue-600';
    }

    if (tone === 'amber') {
        return 'bg-amber-100 text-amber-600';
    }

    if (tone === 'emerald') {
        return 'bg-emerald-100 text-emerald-600';
    }

    return 'bg-slate-100 text-slate-600';
};

const toggleNotifications = () => {
    isNotificationOpen.value = !isNotificationOpen.value;
};

const closeNotifications = () => {
    isNotificationOpen.value = false;
};

const toggleProfileMenu = () => {
    isProfileMenuOpen.value = !isProfileMenuOpen.value;
};

const closeProfileMenu = () => {
    isProfileMenuOpen.value = false;
};

const syncRoleDocumentTheme = () => {
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

const cleanupRoleDocumentTheme = () => {
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

const scrollToSection = (target?: string) => {
    if (!target || typeof document === 'undefined') {
        return;
    }

    document.getElementById(target)?.scrollIntoView({
        behavior: 'smooth',
        block: 'start',
    });
};

const handleNotificationAction = (item: HeaderNotificationItem) => {
    closeNotifications();

    if (item.targetType === 'section') {
        if (!isDashboardPage.value) {
            router.visit(dashboardHref.value);
            return;
        }

        scrollToSection(item.target);
        return;
    }

    if (item.targetType === 'assessment-index') {
        router.visit(isCompanyBrand.value ? '/wims/mitra/penilaian-mahasiswa' : '/wims/dosen/penilaian-mahasiswa');
    }
};

const handleClickOutsideNotification = (event: MouseEvent) => {
    const target = event.target;

    if (!(target instanceof Node)) {
        return;
    }

    const clickedInsideDesktop = desktopNotificationMenuRef.value?.contains(target) ?? false;
    const clickedInsideMobile = mobileNotificationMenuRef.value?.contains(target) ?? false;

    if (!clickedInsideDesktop && !clickedInsideMobile) {
        closeNotifications();
    }
};

const handleClickOutsideProfileMenu = (event: MouseEvent) => {
    const target = event.target;

    if (!(target instanceof Node)) {
        return;
    }

    if (!profileMenuRef.value?.contains(target)) {
        closeProfileMenu();
    }
};

const notificationToneLabel = (tone: HeaderNotificationItem['tone']) => {
    if (tone === 'rose') {
        return 'Prioritas';
    }

    if (tone === 'amber') {
        return 'Tindak Lanjut';
    }

    if (tone === 'blue') {
        return 'Presensi';
    }

    if (tone === 'slate') {
        return 'Penilaian';
    }

    return 'Aman';
};

const pageMeta = computed(() => {
    const path = currentPath.value;

    if (path.includes('/dashboard')) {
        return {
            title: 'Dashboard',
            subtitle: isCompanyBrand.value
                ? 'Ringkasan operasional mahasiswa magang dan tindak lanjut harian.'
                : 'Ringkasan mahasiswa bimbingan, monitoring, dan penilaian.',
        };
    }

    if (path.includes('/monitoring')) {
        return {
            title: 'Monitoring Mahasiswa',
            subtitle: isCompanyBrand.value
                ? 'Pantau presensi, logbook, dan progres mahasiswa magang.'
                : 'Pantau presensi, logbook, laporan, dan progres mahasiswa bimbingan.',
        };
    }

    if (path.includes('/penilaian')) {
        return {
            title: 'Penilaian Mahasiswa',
            subtitle: isCompanyBrand.value
                ? 'Kelola penilaian mahasiswa berdasarkan template aktif.'
                : 'Kelola nilai dosen berdasarkan template penilaian aktif.',
        };
    }

    return {
        title: props.brand,
        subtitle: props.subtitle,
    };
});

onMounted(() => {
    if (typeof document !== 'undefined') {
        initialHtmlDarkClass = document.documentElement.classList.contains('dark');
        initialBodyDarkClass = document.body.classList.contains('dark');
    }

    syncRoleDocumentTheme();
    document.addEventListener('click', handleClickOutsideNotification);
    document.addEventListener('click', handleClickOutsideProfileMenu);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutsideNotification);
    document.removeEventListener('click', handleClickOutsideProfileMenu);
    cleanupRoleDocumentTheme();
});

watch(currentPath, () => {
    closeNotifications();
    closeProfileMenu();
    closeMenu();
    syncRoleDocumentTheme();
});
</script>

<template>
    <div class="wims-role-shell flex h-screen flex-col overflow-hidden bg-wims-bg lg:flex-row">
        <aside class="hidden lg:flex lg:w-[272px] lg:flex-shrink-0">
            <div class="sticky top-0 flex h-screen w-full flex-col border-r border-wims-border bg-wims-card transition-colors duration-300">
                <div class="relative flex h-full flex-col px-4 py-6">
                    <div class="flex items-center gap-3 px-2 pb-8">
                    <div
                        class="relative flex size-10 items-center justify-center rounded-xl border shadow-[0_1px_6px_-2px_rgba(0,0,0,0.06)]"
                        :class="
                            isCompanyBrand
                                ? 'border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-teal-50 text-emerald-700'
                                : 'border-blue-200 bg-gradient-to-br from-blue-50 via-white to-indigo-50 text-blue-700'
                        "
                    >
                        <BriefcaseBusiness v-if="isCompanyBrand" class="size-5" />
                        <GraduationCap v-else class="size-5" />
                    </div>
                    <div>
                        <p
                            class="text-[15px] font-black uppercase tracking-[0.2em]"
                            :class="isCompanyBrand ? 'text-emerald-700' : 'text-blue-600'"
                        >
                            {{ isCompanyBrand ? 'MITRA' : 'WIMS' }}
                        </p>
                        <p class="mt-0.5 text-[10px] font-medium tracking-wide text-slate-400">
                            {{ subtitle }}
                        </p>
                    </div>
                </div>
                    <p class="mb-2.5 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400/80">
                        Menu Utama
                    </p>

                    <nav class="space-y-1">
                        <Link
                            v-for="item in navigationItems"
                            :key="item.href"
                            :href="item.href"
                            class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 transition-all duration-200"
                            :class="
                                isActive(item)
                                    ? 'bg-blue-50/80 text-blue-700'
                                    : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700'
                            "
                        >
                            <div
                                v-if="isActive(item)"
                                class="absolute inset-y-2.5 left-0 w-[3px] rounded-full bg-blue-600"
                            />

                            <div
                                class="flex size-8 items-center justify-center rounded-lg transition-all duration-200"
                                :class="
                                    isActive(item)
                                        ? 'bg-blue-100/80 text-blue-600'
                                        : 'bg-slate-100/80 text-slate-400 group-hover:bg-slate-200/80 group-hover:text-slate-600'
                                "
                            >
                                <component :is="item.icon" class="size-4" />
                            </div>

                            <div class="min-w-0 flex-1">
                                <p
                                    class="text-[13px] font-semibold leading-none"
                                    :class="isActive(item) ? 'text-blue-700' : ''"
                                >
                                    {{ item.label }}
                                </p>
                                <p
                                    class="mt-1 text-[11px] leading-none"
                                    :class="isActive(item) ? 'text-blue-500/80' : 'text-slate-400'"
                                >
                                    {{ item.description || item.label }}
                                </p>
                            </div>
                        </Link>
                    </nav>

                    <div class="mt-auto" />

                    <div class="mb-4 h-px bg-wims-border/60" />

                    <div class="rounded-xl border border-wims-border/80 bg-slate-50/80 p-3 transition-colors duration-300">
                        <div class="flex items-center gap-3">
                            <div
                                class="relative flex size-9 flex-shrink-0 items-center justify-center rounded-lg text-sm font-bold text-white shadow-[0_2px_8px_-4px_rgba(59,130,246,0.25)]"
                                :class="
                                    isCompanyBrand
                                        ? 'bg-gradient-to-br from-emerald-500 to-teal-600 shadow-[0_2px_10px_-4px_rgba(16,185,129,0.35)]'
                                        : 'bg-gradient-to-br from-blue-500 to-blue-600'
                                "
                            >
                                <UserRound v-if="!user?.name" class="size-4" />
                                <span v-else>{{ userInitials }}</span>
                                <span
                                    class="absolute -bottom-0.5 -right-0.5 size-2.5 rounded-full border-2 border-wims-card"
                                    :class="isCompanyBrand ? 'bg-emerald-500' : 'bg-blue-500'"
                                />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-[13px] font-semibold text-wims-text">
                                    {{ user?.name || brand }}
                                </p>
                                <p class="text-[11px] text-slate-500">
                                    {{ user?.email || 'wims@local' }}
                                </p>
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

        <div class="flex min-w-0 min-h-0 flex-1 flex-col overflow-hidden">
            <header class="sticky top-0 z-30 hidden border-b border-wims-border bg-wims-topbar backdrop-blur-xl transition-colors duration-300 lg:block">
                <div class="mx-auto flex w-full max-w-[1320px] items-start justify-between gap-4 px-8 py-4 xl:px-10">
                    <div class="min-w-0 max-w-3xl">
                        <h1 class="text-base font-semibold text-wims-text">
                            {{ pageMeta.title }}
                        </h1>
                        <p class="mt-1.5 text-[11px] leading-5 text-slate-600">
                            {{ pageMeta.subtitle }}
                        </p>
                    </div>
                    <div ref="desktopNotificationMenuRef" class="hidden shrink-0 items-center gap-3 lg:flex">
                        <button
                            type="button"
                            class="flex size-11 items-center justify-center rounded-xl border border-wims-border/80 bg-wims-card text-slate-500 transition-all duration-200 hover:border-blue-300/60 hover:bg-blue-50/80 hover:text-blue-600 active:scale-95 disabled:opacity-50"
                            aria-label="Refresh data"
                            :disabled="isRefreshing"
                            @click="refreshData"
                        >
                            <RefreshCw class="size-4 transition-transform duration-500" :class="isRefreshing ? 'animate-spin' : ''" />
                        </button>

                        <div class="relative">
                            <button
                                type="button"
                                class="relative flex size-11 items-center justify-center rounded-xl border border-wims-border/80 bg-wims-card text-slate-500 transition-all duration-200 hover:border-blue-300/60 hover:bg-blue-50/80 hover:text-blue-600 active:scale-95"
                                aria-label="Notifikasi"
                                @click.stop="toggleNotifications"
                            >
                                <Bell class="size-4" />
                                <span
                                    v-if="hasNotifications"
                                    class="absolute right-2.5 top-2.5 size-2 rounded-full bg-rose-500 ring-2 ring-wims-topbar"
                                />
                            </button>

                            <Transition
                                enter-active-class="transition duration-200 ease-out"
                                enter-from-class="opacity-0 -translate-y-2 scale-95"
                                enter-to-class="opacity-100 translate-y-0 scale-100"
                                leave-active-class="transition duration-150 ease-in"
                                leave-from-class="opacity-100 translate-y-0 scale-100"
                                leave-to-class="opacity-0 -translate-y-2 scale-95"
                            >
                                <div
                                    v-if="isNotificationOpen"
                                    class="absolute right-0 top-12 z-50 isolate w-[380px] overflow-hidden rounded-[26px] border border-wims-border bg-wims-card shadow-[0_24px_56px_-24px_rgba(15,23,42,0.28)]"
                                >
                                    <div class="flex items-center justify-between border-b border-wims-border/60 bg-wims-card px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            <p class="text-[15px] font-bold text-wims-text">Notifikasi</p>
                                            <span v-if="hasNotifications" class="size-2 rounded-full bg-rose-500" />
                                        </div>
                                        <button
                                            type="button"
                                            class="flex size-7 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600"
                                            aria-label="Tutup notifikasi"
                                            @click="closeNotifications"
                                        >
                                            <X class="size-3.5" />
                                        </button>
                                    </div>

                                    <div class="bg-wims-card px-4 pb-3 pt-2">
                                        <p class="text-[12px] leading-6 text-slate-500">
                                            {{ hasNotifications ? 'Ringkasan prioritas yang perlu Anda tindak lanjuti.' : 'Belum ada notifikasi baru saat ini.' }}
                                        </p>
                                    </div>

                                    <div v-if="hasNotifications" class="max-h-[360px] overflow-y-auto overscroll-contain bg-wims-card px-2 pb-2 scrollbar-thin">
                                        <button
                                            v-for="item in visibleNotificationItems"
                                            :key="item.id"
                                            type="button"
                                            class="group mb-2 flex w-full gap-3 rounded-2xl border border-wims-border/70 bg-slate-50/75 px-3.5 py-3 text-left transition-all duration-200 hover:border-slate-300 hover:bg-slate-50"
                                            @click="handleNotificationAction(item)"
                                        >
                                            <div
                                                class="mt-0.5 flex size-10 flex-shrink-0 items-center justify-center rounded-full"
                                                :class="getNotificationIconClass(item.tone)"
                                            >
                                                <CircleAlert
                                                    v-if="item.tone === 'rose' || item.tone === 'amber'"
                                                    class="size-4"
                                                />
                                                <CheckCheck v-else-if="item.tone === 'emerald'" class="size-4" />
                                                <Bell v-else class="size-4" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-start justify-between gap-3">
                                                    <p class="text-[13px] font-bold leading-5 text-wims-text">
                                                        {{ item.title }}
                                                    </p>
                                                    <span
                                                        class="shrink-0 rounded-full border px-2.5 py-1 text-[10px] font-bold"
                                                        :class="notificationToneClass(item.tone)"
                                                    >
                                                        {{ notificationToneLabel(item.tone) }}
                                                    </span>
                                                </div>
                                                <p class="mt-1 text-[12px] leading-6 text-slate-600">
                                                    {{ item.description }}
                                                </p>
                                                <p
                                                    v-if="item.actionLabel"
                                                    class="mt-2 text-[11px] font-bold text-blue-600"
                                                >
                                                    {{ item.actionLabel }}
                                                </p>
                                            </div>
                                        </button>

                                        <div
                                            v-if="remainingNotificationCount > 0"
                                            class="px-3 pb-2 pt-1 text-[11px] font-medium text-slate-400"
                                        >
                                            {{ remainingNotificationCount }} notifikasi lain tetap tersedia di dashboard saat ini.
                                        </div>
                                    </div>

                                    <div v-else class="bg-wims-card px-4 py-8 text-center">
                                        <div class="mx-auto mb-3 flex size-14 items-center justify-center rounded-2xl bg-slate-100/80">
                                            <Bell class="size-6 text-slate-300" />
                                        </div>
                                        <p class="mb-0.5 text-[13px] font-semibold text-wims-text">Semua sudah dibaca</p>
                                        <p class="text-[11px] text-slate-400">
                                            Tidak ada notifikasi baru saat ini
                                        </p>
                                    </div>

                                    <div class="border-t border-wims-border/50 bg-wims-card p-2.5">
                                        <button
                                            v-if="isDashboardPage"
                                            type="button"
                                            class="flex w-full items-center justify-center gap-1.5 rounded-xl py-2 text-[12px] font-semibold text-blue-600 transition-colors hover:bg-blue-50/80"
                                            @click="closeNotifications"
                                        >
                                            Tutup panel
                                        </button>
                                        <Link
                                            v-else
                                            :href="dashboardHref"
                                            class="flex w-full items-center justify-center gap-1.5 rounded-xl py-2 text-[12px] font-semibold text-blue-600 transition-colors hover:bg-blue-50/80"
                                            @click="closeNotifications"
                                        >
                                            Buka dashboard untuk melihat ringkasan
                                        </Link>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <div class="flex min-w-[240px] items-center gap-3 rounded-2xl border border-wims-border/80 bg-wims-card px-3 py-2.5">
                            <div
                                class="relative flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-xl text-xs font-bold text-white"
                                :class="
                                    isCompanyBrand
                                        ? 'bg-gradient-to-br from-emerald-500 to-teal-600'
                                        : 'bg-gradient-to-br from-blue-500 to-blue-600'
                                "
                            >
                                <img
                                    v-if="userAvatar"
                                    :src="userAvatar"
                                    alt="Foto profil"
                                    class="size-full object-cover"
                                />
                                <UserRound v-else-if="!user?.name" class="size-4" />
                                <span v-else>{{ userInitials }}</span>
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-[13px] font-bold text-wims-text">
                                    {{ user?.name || brand }}
                                </p>
                                <p class="truncate text-[11px] text-slate-500">
                                    {{ headerRoleLabel }}
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="flex size-11 items-center justify-center rounded-xl border border-wims-border/80 bg-wims-card text-slate-500 transition-all duration-200 hover:border-rose-300/60 hover:bg-rose-50 hover:text-rose-500 active:scale-95"
                            aria-label="Logout"
                            @click="logout"
                        >
                            <LogOut class="size-4" />
                        </button>
                    </div>
                </div>
            </header>

            <header class="sticky top-0 z-40 border-b border-wims-border/80 bg-wims-topbar backdrop-blur-xl transition-colors duration-300 lg:hidden">
                <div class="mx-auto flex w-full max-w-[1320px] items-center justify-between gap-3 px-4 py-3 sm:px-6">
                    <div class="flex min-w-0 items-center gap-2">
                        <button
                            type="button"
                            class="inline-flex size-9 flex-shrink-0 items-center justify-center rounded-xl border border-wims-border/80 bg-wims-card text-slate-700 transition-all duration-200 hover:border-blue-300/60 hover:bg-blue-50/80 hover:text-blue-600"
                            aria-label="Buka menu navigasi"
                            @click="isMenuOpen = true"
                        >
                            <Menu class="size-4" />
                        </button>

                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-blue-500">
                                WIMS
                            </p>
                            <p class="mt-0.5 truncate text-[15px] font-bold text-wims-text">
                                {{ pageMeta.title }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="flex size-9 items-center justify-center rounded-xl border border-wims-border/80 bg-wims-card text-slate-500 transition-all duration-200 hover:border-blue-300/60 hover:bg-blue-50/80 hover:text-blue-600 active:scale-95 disabled:opacity-50"
                            aria-label="Refresh data"
                            :disabled="isRefreshing"
                            @click="refreshData"
                        >
                            <RefreshCw class="size-4 transition-transform duration-500" :class="isRefreshing ? 'animate-spin' : ''" />
                        </button>

                        <div ref="mobileNotificationMenuRef" class="relative">
                            <button
                                type="button"
                                class="relative flex size-9 items-center justify-center rounded-xl border border-wims-border/80 bg-wims-card text-slate-500 transition-all duration-200 hover:border-blue-300/60 hover:bg-blue-50/80 hover:text-blue-600 active:scale-95"
                                aria-label="Notifikasi"
                                @click.stop="toggleNotifications"
                            >
                                <Bell class="size-4" />
                                <span
                                    v-if="hasNotifications"
                                    class="absolute right-1.5 top-1.5 size-2 rounded-full bg-rose-500 ring-2 ring-wims-topbar"
                                />
                            </button>

                            <Transition
                                enter-active-class="transition duration-200 ease-out"
                                enter-from-class="opacity-0 -translate-y-2 scale-95"
                                enter-to-class="opacity-100 translate-y-0 scale-100"
                                leave-active-class="transition duration-150 ease-in"
                                leave-from-class="opacity-100 translate-y-0 scale-100"
                                leave-to-class="opacity-0 -translate-y-2 scale-95"
                            >
                                <div
                                    v-if="isNotificationOpen"
                                    class="fixed inset-x-4 top-[60px] z-50 isolate overflow-hidden rounded-[26px] border border-wims-border bg-wims-card shadow-[0_24px_56px_-24px_rgba(15,23,42,0.28)] sm:absolute sm:inset-x-auto sm:right-0 sm:top-12 sm:w-[360px]"
                                >
                                    <div class="flex items-center justify-between border-b border-wims-border/60 bg-wims-card px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            <p class="text-[15px] font-bold text-wims-text">Notifikasi</p>
                                            <span v-if="hasNotifications" class="size-2 rounded-full bg-rose-500" />
                                        </div>
                                        <button
                                            type="button"
                                            class="flex size-7 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600"
                                            aria-label="Tutup notifikasi"
                                            @click="closeNotifications"
                                        >
                                            <X class="size-3.5" />
                                        </button>
                                    </div>

                                    <div class="bg-wims-card px-4 pb-3 pt-2">
                                        <p class="text-[12px] leading-6 text-slate-500">
                                            {{ hasNotifications ? 'Ringkasan prioritas yang perlu Anda tindak lanjuti.' : 'Belum ada notifikasi baru saat ini.' }}
                                        </p>
                                    </div>

                                    <div v-if="hasNotifications" class="max-h-[min(68vh,360px)] overflow-y-auto overscroll-contain bg-wims-card px-2 pb-2 scrollbar-thin">
                                        <button
                                            v-for="item in visibleNotificationItems"
                                            :key="`mobile-${item.id}`"
                                            type="button"
                                            class="group mb-2 flex w-full gap-3 rounded-2xl border border-wims-border/70 bg-slate-50/75 px-3.5 py-3 text-left transition-all duration-200 hover:border-slate-300 hover:bg-slate-50"
                                            @click="handleNotificationAction(item)"
                                        >
                                            <div
                                                class="mt-0.5 flex size-10 flex-shrink-0 items-center justify-center rounded-full"
                                                :class="getNotificationIconClass(item.tone)"
                                            >
                                                <CircleAlert
                                                    v-if="item.tone === 'rose' || item.tone === 'amber'"
                                                    class="size-4"
                                                />
                                                <CheckCheck v-else-if="item.tone === 'emerald'" class="size-4" />
                                                <Bell v-else class="size-4" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-start justify-between gap-3">
                                                    <p class="text-[13px] font-bold leading-5 text-wims-text">
                                                        {{ item.title }}
                                                    </p>
                                                    <span
                                                        class="shrink-0 rounded-full border px-2.5 py-1 text-[10px] font-bold"
                                                        :class="notificationToneClass(item.tone)"
                                                    >
                                                        {{ notificationToneLabel(item.tone) }}
                                                    </span>
                                                </div>
                                                <p class="mt-1 text-[12px] leading-6 text-slate-600">
                                                    {{ item.description }}
                                                </p>
                                                <p
                                                    v-if="item.actionLabel"
                                                    class="mt-2 text-[11px] font-bold text-blue-600"
                                                >
                                                    {{ item.actionLabel }}
                                                </p>
                                            </div>
                                        </button>

                                        <div
                                            v-if="remainingNotificationCount > 0"
                                            class="px-3 pb-2 pt-1 text-[11px] font-medium text-slate-400"
                                        >
                                            {{ remainingNotificationCount }} notifikasi lain tetap tersedia di dashboard saat ini.
                                        </div>
                                    </div>

                                    <div v-else class="bg-wims-card px-4 py-8 text-center">
                                        <div class="mx-auto mb-3 flex size-14 items-center justify-center rounded-2xl bg-slate-100/80">
                                            <Bell class="size-6 text-slate-300" />
                                        </div>
                                        <p class="mb-0.5 text-[13px] font-semibold text-wims-text">Semua sudah dibaca</p>
                                        <p class="text-[11px] text-slate-400">
                                            Tidak ada notifikasi baru saat ini
                                        </p>
                                    </div>

                                    <div class="border-t border-wims-border/50 bg-wims-card p-2.5">
                                        <button
                                            v-if="isDashboardPage"
                                            type="button"
                                            class="flex w-full items-center justify-center gap-1.5 rounded-xl py-2 text-[12px] font-semibold text-blue-600 transition-colors hover:bg-blue-50/80"
                                            @click="closeNotifications"
                                        >
                                            Tutup panel
                                        </button>
                                        <Link
                                            v-else
                                            :href="dashboardHref"
                                            class="flex w-full items-center justify-center gap-1.5 rounded-xl py-2 text-[12px] font-semibold text-blue-600 transition-colors hover:bg-blue-50/80"
                                            @click="closeNotifications"
                                        >
                                            Buka dashboard untuk melihat ringkasan
                                        </Link>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <div ref="profileMenuRef" class="relative">
                            <button
                                type="button"
                                class="flex size-9 items-center justify-center overflow-hidden rounded-xl border border-blue-200/80 bg-gradient-to-br from-blue-500 to-blue-600 text-[11px] font-bold text-white shadow-[0_0_12px_rgba(59,130,246,0.2)] transition-all active:scale-95"
                                aria-label="Akun"
                                @click.stop="toggleProfileMenu"
                            >
                                <img
                                    v-if="userAvatar"
                                    :src="userAvatar"
                                    alt="Foto profil"
                                    class="h-full w-full object-cover"
                                />
                                <UserRound v-else-if="!user?.name" class="size-4" />
                                <span v-else>{{ userInitials }}</span>
                            </button>

                            <Transition
                                enter-active-class="transition duration-200 ease-out"
                                enter-from-class="opacity-0 -translate-y-2 scale-95"
                                enter-to-class="opacity-100 translate-y-0 scale-100"
                                leave-active-class="transition duration-150 ease-in"
                                leave-from-class="opacity-100 translate-y-0 scale-100"
                                leave-to-class="opacity-0 -translate-y-2 scale-95"
                            >
                                <div
                                    v-if="isProfileMenuOpen"
                                    class="absolute right-0 top-12 z-50 w-56 overflow-hidden rounded-2xl border border-wims-border/80 bg-wims-card/95 shadow-[0_8px_32px_-8px_rgba(0,0,0,0.12)] backdrop-blur-2xl"
                                >
                                    <div class="border-b border-wims-border/60 px-3.5 py-3">
                                        <p class="truncate text-[13px] font-bold text-wims-text">
                                            {{ user?.name || brand }}
                                        </p>
                                        <p class="mt-0.5 truncate text-[11px] text-slate-500">
                                            {{ headerRoleLabel }}
                                        </p>
                                    </div>
                                    <div class="p-1.5">
                                        <button
                                            type="button"
                                            class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium text-rose-600 transition-colors hover:bg-rose-50/80"
                                            @click="logout"
                                        >
                                            <div class="flex size-7 items-center justify-center rounded-lg bg-rose-50/80 text-rose-600">
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

            <main class="min-w-0 min-h-0 flex-1 overflow-y-auto overflow-x-hidden">
                <slot />
            </main>
        </div>

        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="-translate-x-4 opacity-0"
            enter-to-class="translate-x-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="translate-x-0 opacity-100"
            leave-to-class="-translate-x-4 opacity-0"
        >
            <div v-if="isMenuOpen" class="fixed inset-0 z-50 lg:hidden">
                <button
                    type="button"
                    class="absolute inset-0 bg-slate-950/35"
                    aria-label="Tutup menu navigasi"
                    @click="closeMenu"
                />

                <div class="relative flex h-full w-full max-w-[304px] flex-col border-r border-wims-border bg-wims-card shadow-[0_24px_48px_-28px_rgba(15,23,42,0.32)] sm:max-w-[320px]">
                    <div class="flex items-start justify-between gap-3 px-4 py-4 sm:px-5 sm:py-5">
                        <div class="flex min-w-0 items-center gap-2.5 sm:gap-3">
                            <div
                                class="relative flex size-9 shrink-0 items-center justify-center rounded-xl border shadow-[0_1px_6px_-2px_rgba(0,0,0,0.06)] sm:size-10"
                                :class="
                                    isCompanyBrand
                                        ? 'border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-teal-50 text-emerald-700'
                                        : 'border-blue-200 bg-gradient-to-br from-blue-50 via-white to-indigo-50 text-blue-700'
                                "
                            >
                                <BriefcaseBusiness v-if="isCompanyBrand" class="size-5" />
                                <GraduationCap v-else class="size-5" />
                            </div>
                            <div class="min-w-0">
                                <p
                                    class="truncate text-[15px] font-black uppercase tracking-[0.2em]"
                                    :class="isCompanyBrand ? 'text-emerald-700' : 'text-blue-600'"
                                >
                                    {{ isCompanyBrand ? 'MITRA' : 'WIMS' }}
                                </p>
                                <p class="mt-0.5 truncate text-[10px] font-medium tracking-wide text-slate-400">
                                    {{ subtitle }}
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

                    <div class="px-4">
                        <p class="mb-2.5 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400/80">
                            Menu Utama
                        </p>
                    </div>

                    <div class="px-5 pb-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-10 items-center justify-center rounded-lg text-sm font-bold text-white"
                                :class="
                                    isCompanyBrand
                                        ? 'bg-gradient-to-br from-emerald-500 to-teal-600'
                                        : 'bg-gradient-to-br from-blue-500 to-blue-600'
                                "
                            >
                                <UserRound v-if="!user?.name" class="size-4" />
                                <span v-else>{{ userInitials }}</span>
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-[13px] font-semibold text-slate-900">
                                    {{ user?.name || brand }}
                                </p>
                                <p class="truncate text-[11px] text-slate-500">
                                    {{ user?.email || 'wims@local' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <nav class="flex-1 space-y-1 px-4 py-1">
                        <Link
                            v-for="item in navigationItems"
                            :key="`mobile-${item.href}`"
                            :href="item.href"
                            class="group relative flex items-center gap-2.5 rounded-xl px-3 py-2 transition-all duration-200 sm:gap-3 sm:py-2.5"
                            :class="
                                isActive(item)
                                    ? 'bg-blue-50/80 text-blue-700'
                                    : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700'
                            "
                            @click="closeMenu"
                        >
                            <div
                                v-if="isActive(item)"
                                class="absolute inset-y-2.5 left-0 w-[3px] rounded-full bg-blue-600"
                            />
                            <div
                                class="flex size-7 items-center justify-center rounded-lg transition-all duration-200 sm:size-8"
                                :class="
                                    isActive(item)
                                        ? 'bg-blue-100/80 text-blue-600'
                                        : 'bg-slate-100/80 text-slate-400 group-hover:bg-slate-200/80 group-hover:text-slate-600'
                                "
                            >
                                <component :is="item.icon" class="size-4" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[13px] font-semibold leading-none" :class="isActive(item) ? 'text-blue-700' : ''">
                                    {{ item.label }}
                                </p>
                                <p class="mt-1 text-[11px] leading-none" :class="isActive(item) ? 'text-blue-500/80' : 'text-slate-400'">
                                    {{ item.description || item.label }}
                                </p>
                            </div>
                        </Link>
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

        <AppToast />
    </div>
</template>
