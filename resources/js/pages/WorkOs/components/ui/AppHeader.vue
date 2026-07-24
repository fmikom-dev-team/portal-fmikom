<script setup lang="ts">
import { Link, router, usePage } from "@inertiajs/vue3";
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import ShadcnSearch from "@/components/ui/ShadcnSearch.vue";

const props = defineProps<{
	activeLabel: string;
	notifications?: any[];
	unreadNotificationsCount?: number;
}>();

const emit = defineEmits<{
	(e: "toggleSidebar"): void;
	(e: "search", query: string): void;
	(e: "view-registration", requestId: string): void;
}>();

const page = usePage();
const authUser = computed(() => (page.props as any).auth?.user);
const userInitial = computed(() =>
	(authUser.value?.name ?? "A").charAt(0).toUpperCase(),
);
const userName = computed(() => authUser.value?.name ?? "Admin");
const userEmail = computed(() => authUser.value?.email ?? "");

import { useAppearance } from "@/composables/useAppearance";
import { ThemeTogglerButton } from "@/components/animate-ui/components/buttons/theme-toggler";

const { appearance, resolvedAppearance, updateAppearance } = useAppearance();

const activeTheme = computed({
	get: () => appearance.value === "system" ? resolvedAppearance.value : appearance.value,
	set: (val) => {
		updateAppearance(val);
	}
});

// ── Project & Environment constants ────────────────────────────────
const currentProject = "Web Dev";
const currentEnv = "Staging";
const currentEnvColor = "#f59e0b";

// User dropdown
const userOpen = ref(false);
const notifOpen = ref(false);

const notificationsList = computed(() => props.notifications || []);
const unreadCount = computed(() => props.unreadNotificationsCount ?? 0);

function markAllAsRead() {
	router.post("/workos/notifications/mark-all-read", {}, {
		preserveScroll: true,
	});
}

function clickNotification(n: any) {
	if (n.read_at === null) {
		router.post(`/workos/notifications/${n.id}/toggle-read`, {}, {
			preserveScroll: true,
		});
	}
	notifOpen.value = false;
	if (n.data?.extra?.type === 'registration_request') {
		emit('view-registration', n.data.extra.registration_request_id);
	}
}

// Close all on outside click
function closeAll(e: MouseEvent) {
	const target = e.target as HTMLElement;
	if (!target.closest("[data-dropdown]")) {
		userOpen.value = false;
		notifOpen.value = false;
	}
}

onMounted(() => {
	document.addEventListener("click", closeAll);
});

onUnmounted(() => {
	document.removeEventListener("click", closeAll);
});
</script>

<template>
    <header
        class="wos-header flex items-center justify-between bg-white dark:bg-zinc-900 border-b border-[#e5e7eb] dark:border-zinc-800 px-3 shrink-0 z-30 relative"
        style="padding-top: env(safe-area-inset-top); height: calc(52px + env(safe-area-inset-top)); font-family: var(--wos-font)"
    >
        <!-- ── Left ── -->
        <div class="flex items-center gap-1">
            <!-- Mobile hamburger -->
            <button
                class="md:hidden p-1.5 rounded-md text-[#6b7280] hover:bg-[#f3f4f6] dark:hover:bg-zinc-800 dark:bg-zinc-800 hover:text-[#111827] dark:hover:text-zinc-100 dark:text-zinc-100 transition-colors"
                aria-label="Open sidebar"
                @click.stop="emit('toggleSidebar')"
            >
                <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <!-- Modern Breadcrumbs UI -->
            <nav class="hidden sm:flex items-center gap-2 text-[13px] font-medium text-[#4b5563] ml-1.5" aria-label="Breadcrumb">
                <!-- Root / App Name -->
                <span class="text-[#111827] dark:text-zinc-100 font-semibold">WorkOS</span>
                <!-- Separator & Active Page -->
                <template v-if="activeLabel">
                    <svg class="w-3.5 h-3.5 text-[#9ca3af] dark:text-zinc-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="font-semibold text-[#111827] dark:text-zinc-100 truncate max-w-[120px] sm:max-w-[200px]">{{ activeLabel }}</span>
                </template>
            </nav>
        </div>

        <!-- ── Right ── -->
        <div class="flex items-center gap-0.5">

            <!-- Reusable Shadcn Command Dialog Search (WorkOs scope) -->
            <ShadcnSearch 
                endpoint="/workos/instant-search"
                placeholder="Cari user, peran..."
                class="hidden sm:block mr-2"
            />

            <!-- Notification Bell -->
            <div class="mr-1 relative" data-dropdown>
                <button
                    class="p-2 rounded-md text-[#6b7280] dark:text-zinc-400 hover:bg-[#f3f4f6] dark:hover:bg-zinc-800 hover:text-[#111827] dark:hover:text-zinc-100 transition-colors relative flex items-center justify-center"
                    aria-label="Notifikasi"
                    @click.stop="notifOpen = !notifOpen"
                >
                    <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span
                        v-if="unreadCount > 0"
                        class="absolute top-1 right-1 w-3.5 h-3.5 bg-red-500 text-white text-[8px] font-bold rounded-full flex items-center justify-center animate-pulse"
                    >
                        {{ unreadCount }}
                    </span>
                </button>

                <!-- Notifications Dropdown Panel -->
                <div
                    v-show="notifOpen"
                    class="absolute top-full right-0 mt-1 w-80 bg-white dark:bg-zinc-900 border border-[#e5e7eb] dark:border-zinc-800 rounded-xl shadow-lg py-2 z-50 dark:shadow-none animate-in fade-in slide-in-from-top-2 duration-200"
                >
                    <!-- Header -->
                    <div class="px-4 py-2 border-b border-[#f3f4f6] dark:border-zinc-800 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-805 dark:text-white uppercase tracking-wider">Notifikasi</span>
                        <button
                            v-if="unreadCount > 0"
                            @click="markAllAsRead"
                            class="text-[10px] font-bold text-blue-600 dark:text-blue-400 hover:underline"
                        >
                            Tandai Semua Dibaca
                        </button>
                    </div>

                    <!-- List -->
                    <div class="max-h-64 overflow-y-auto divide-y divide-[#f3f4f6] dark:divide-zinc-800">
                        <div v-if="notificationsList.length === 0" class="px-4 py-6 text-center text-xs text-slate-400 dark:text-zinc-500">
                            Tidak ada notifikasi
                        </div>
                        <button
                            v-for="n in notificationsList"
                            :key="n.id"
                            @click="clickNotification(n)"
                            :class="[
                                'w-full text-left px-4 py-3 hover:bg-[#f9fafb] dark:hover:bg-zinc-800/50 transition-colors flex gap-2.5 items-start',
                                n.read_at === null ? 'bg-blue-50/20 dark:bg-blue-900/10' : ''
                            ]"
                        >
                            <!-- Dot indicators -->
                            <div class="w-1.5 h-1.5 rounded-full bg-blue-600 mt-1.5 shrink-0" :class="n.read_at === null ? 'opacity-100' : 'opacity-0'"></div>
                            
                            <div class="flex-1 min-w-0">
                                <p class="text-[12px] font-bold text-slate-800 dark:text-white leading-snug">{{ n.data?.title || 'Notifikasi' }}</p>
                                <p class="text-[11px] text-slate-500 dark:text-zinc-400 mt-0.5 leading-normal">{{ n.data?.description }}</p>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Theme Toggler -->
            <div class="mr-1 flex items-center shrink-0">
                <ThemeTogglerButton
                    v-model="activeTheme"
                    variant="ghost"
                    size="sm"
                    direction="ltr"
                    :modes="['light', 'dark']"
                />
            </div>

            <!-- User avatar + dropdown -->
            <div class="relative" data-dropdown>
                <button
                    class="flex items-center gap-2 h-9 px-2 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 dark:bg-zinc-900 rounded-md transition-colors text-left"
                    aria-label="User menu"
                    @click.stop="userOpen = !userOpen"
                >
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-[11px] font-bold shrink-0 overflow-hidden" style="background-color: #2563EB">
                        <img v-if="authUser?.avatar" :src="authUser.avatar" :alt="userName" class="w-full h-full object-cover" />
                        <span v-else>{{ userInitial }}</span>
                    </div>
                    <div class="hidden sm:flex flex-col select-none">
                        <span class="text-[13px] font-semibold text-[#111827] dark:text-zinc-100 leading-tight uppercase">{{ userName }}</span>
                        <span class="text-[10px] text-[#6b7280] dark:text-zinc-400 leading-none mt-0.5">Admin</span>
                    </div>
                    <svg class="w-3.5 h-3.5 text-[#9ca3af] dark:text-zinc-500 hidden sm:block shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- User dropdown menu -->
                <div
                    v-show="userOpen"
                    class="absolute top-full right-0 mt-1 w-52 bg-white dark:bg-zinc-900 border border-[#e5e7eb] dark:border-zinc-800 rounded-lg shadow-lg py-1 z-50 dark:shadow-none"
                >
                    <!-- User info header -->
                    <div class="px-3 py-2 border-b border-[#f3f4f6]">
                        <p class="text-[12.5px] font-semibold text-[#111827] dark:text-zinc-100 truncate uppercase">{{ userName }}</p>
                        <p class="text-[11px] text-[#9ca3af] dark:text-zinc-500 truncate">{{ userEmail }}</p>
                    </div>

                    <div class="py-1">
                        <button class="w-full flex items-center gap-2.5 px-3 py-1.5 text-[12.5px] text-[#374151] dark:text-zinc-200 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 dark:bg-zinc-900 transition-colors text-left">
                            <svg class="w-3.5 h-3.5 text-[#9ca3af] dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profile
                        </button>
                        <button class="w-full flex items-center gap-2.5 px-3 py-1.5 text-[12.5px] text-[#374151] dark:text-zinc-200 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 dark:bg-zinc-900 transition-colors text-left">
                            <svg class="w-3.5 h-3.5 text-[#9ca3af] dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Settings
                        </button>
                    </div>

                    <div class="border-t border-[#f3f4f6] py-1">
                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            class="w-full flex items-center gap-2.5 px-3 py-1.5 text-[12.5px] text-[#ef4444] hover:bg-[#fef2f2] transition-colors text-left"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Sign out
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>
