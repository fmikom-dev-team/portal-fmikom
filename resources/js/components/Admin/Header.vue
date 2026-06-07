<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
import { Bell, Command, Menu, Search } from "lucide-vue-next";
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import NotificationDropdown from "./NotificationDropdown.vue";
import UserDropdown from "./UserDropdown.vue";

defineProps<{
	collapsed: boolean;
}>();

const emit = defineEmits<{
	"toggle-mobile": [];
}>();

const page = usePage();

const user = computed(
	() => page.props.auth?.user || { name: "Admin", email: "" },
);

const searchQuery = ref("");
const searchFocused = ref(false);
const showNotifications = ref(false);
const showUserMenu = ref(false);
const avatarError = ref(false);

interface NotificationItem {
	id: string;
	type: string;
	title: string;
	message: string;
	avatar: string | null;
	href: string;
	unread: boolean;
	time: string;
	created_at: string;
	// biome-ignore lint/suspicious/noExplicitAny: complex structure
	extra?: any;
}

// Real Notifications State
const notificationsList = ref<NotificationItem[]>([]);
const notifCount = ref(0);

const fetchNotifications = async () => {
	try {
		const res = await fetch("/pagi/admin/api/notifications");
		if (res.ok) {
			const data = await res.json();
			notificationsList.value = data.notifications;
			notifCount.value = data.unreadCount;
		}
	} catch (e) {
		console.error("Failed to fetch admin notifications:", e);
	}
};

const markAllAsRead = async () => {
	try {
		const res = await fetch("/pagi/notifications/mark-all-read", {
			method: "POST",
			headers: {
				"X-CSRF-TOKEN":
					(
						document.querySelector(
							"meta[name=csrf-token]",
						) as HTMLMetaElement | null
					)?.content || "",
				Accept: "application/json",
			},
		});
		if (res.ok) {
			notificationsList.value.forEach((n) => {
				n.unread = false;
			});
			notifCount.value = 0;
		}
	} catch (e) {
		console.error("Failed to mark all as read:", e);
	}
};

const markAsRead = async (id: string) => {
	try {
		const res = await fetch(`/pagi/notifications/${id}/mark-read`, {
			method: "POST",
			headers: {
				"X-CSRF-TOKEN":
					(
						document.querySelector(
							"meta[name=csrf-token]",
						) as HTMLMetaElement | null
					)?.content || "",
				Accept: "application/json",
			},
		});
		if (res.ok) {
			const notif = notificationsList.value.find((n) => n.id === id);
			if (notif?.unread) {
				notif.unread = false;
				notifCount.value = Math.max(0, notifCount.value - 1);
			}
		}
	} catch (e) {
		console.error("Failed to mark notification as read:", e);
	}
};

const handleIncomingNotif = (payload: any) => {
	const data = payload.data ? payload.data : payload;
	const id = payload.id ?? String(Date.now());

	// Avoid duplicates
	const exists = notificationsList.value.some((n) => n.id === id);
	if (!exists) {
		notificationsList.value.unshift({
			id: id,
			type: data.type ?? "system",
			title: data.title ?? "Notifikasi Admin",
			message: data.message ?? "",
			avatar: data.avatar ?? null,
			href: data.href ?? "/pagi/admin",
			unread: true,
			time: "baru saja",
			created_at: new Date().toISOString(),
			extra: data,
		});
		notifCount.value++;
	}
};

// Close dropdowns on outside click
const handleOutsideClick = (e: MouseEvent) => {
	const target = e.target as HTMLElement;
	if (!target.closest("#notification-dropdown-wrapper"))
		showNotifications.value = false;
	if (!target.closest("#user-dropdown-wrapper")) showUserMenu.value = false;
};

onMounted(() => {
	document.addEventListener("click", handleOutsideClick);
	fetchNotifications();

	if (window.Echo) {
		// 1. Subscribe to shared admin reports channel
		window.Echo.private("pagi.admin.reports").listen(
			".PagiReportCreated",
			// biome-ignore lint/suspicious/noExplicitAny: echo listener payload
			(e: any) => {
				const exists = notificationsList.value.some(
					(n) =>
						n.id === String(e.id) || (n.extra && n.extra.report_id === e.id),
				);
				if (!exists) {
					notificationsList.value.unshift({
						id: String(e.id),
						type: "report",
						title: `Laporan Baru: ${e.reporter_name}`,
						message: `Melaporkan karya "${e.work_title}"`,
						avatar: null,
						href: "/pagi/admin/moderation",
						unread: true,
						time: "baru saja",
						created_at: new Date().toISOString(),
						extra: {
							report_id: e.id,
							work_id: e.work_id,
						},
					});
					notifCount.value++;
				}
			},
		);

		// 2. Subscribe to user-specific private channel for direct notifications
		const userId = page.props.auth?.user?.id;
		if (userId) {
			window.Echo.private(`App.Models.User.${userId}`)
				// biome-ignore lint/suspicious/noExplicitAny: echo listener payload
				.listen(".pagi.notification", (data: any) => {
					handleIncomingNotif(data);
				})
				// biome-ignore lint/suspicious/noExplicitAny: echo listener payload
				.notification((data: any) => {
					handleIncomingNotif(data);
				});
		}
	}
});

onBeforeUnmount(() => {
	document.removeEventListener("click", handleOutsideClick);
	const userId = page.props.auth?.user?.id;
	if (userId && window.Echo) {
		try {
			window.Echo.leave(`App.Models.User.${userId}`);
		} catch (_) {}
		try {
			window.Echo.leave("pagi.admin.reports");
		} catch (_) {}
	}
});
</script>

<template>
    <header class="sticky top-0 z-20 flex items-center justify-between h-[64px] px-4 sm:px-6 bg-white/90 dark:bg-zinc-950/90 backdrop-blur-md border-b border-slate-100 dark:border-zinc-800 shrink-0">

        <!-- Left: Mobile Toggle + Breadcrumb hint -->
        <div class="flex items-center gap-3">
            <button
                @click="emit('toggle-mobile')"
                class="lg:hidden flex items-center justify-center h-8 w-8 rounded-lg bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-300 hover:text-indigo-600 transition-colors"
                aria-label="Toggle sidebar"
            >
                <Menu class="h-4 w-4" />
            </button>

            <!-- Search -->
            <div
                class="relative hidden sm:flex items-center h-9 rounded-xl border transition-all duration-200"
                :class="searchFocused
                    ? 'w-[280px] border-indigo-400 bg-white dark:bg-zinc-900 shadow-sm shadow-indigo-100'
                    : 'w-[220px] border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-900'"
            >
                <Search class="ml-3 h-3.5 w-3.5 shrink-0 text-slate-400" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari mahasiswa, karya, laporan..."
                    class="flex-1 bg-transparent px-2.5 text-[13px] font-medium text-slate-700 dark:text-zinc-200 placeholder-slate-400 focus:outline-none"
                    @focus="searchFocused = true"
                    @blur="searchFocused = false"
                />
                <div class="mr-2.5 flex items-center gap-0.5 text-slate-300 dark:text-zinc-600">
                    <Command class="h-3 w-3" />
                    <span class="text-[10px] font-bold">K</span>
                </div>
            </div>
        </div>

        <!-- Right: Actions -->
        <div class="flex items-center gap-2">

            <!-- Notification Bell -->
            <div id="notification-dropdown-wrapper" class="relative">
                <button
                    @click.stop="showNotifications = !showNotifications; showUserMenu = false"
                    class="relative flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-300 hover:bg-slate-200 dark:hover:bg-zinc-700 hover:text-indigo-600 transition-all"
                    aria-label="Notifikasi"
                >
                    <Bell class="h-4 w-4" />
                    <span
                        v-if="notifCount > 0"
                        class="absolute right-1.5 top-1.5 flex h-[7px] w-[7px] items-center justify-center rounded-full bg-rose-500 border-2 border-white dark:border-zinc-950"
                    />
                </button>

                <Transition
                    enter-active-class="transition duration-150 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-1"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition duration-100 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-1"
                >
                    <NotificationDropdown
                        v-if="showNotifications"
                        :notifications="notificationsList"
                        :unread-count="notifCount"
                        @mark-read="markAsRead"
                        @mark-all-read="markAllAsRead"
                        @close="showNotifications = false"
                    />
                </Transition>
            </div>

            <!-- Divider -->
            <div class="h-6 w-px bg-slate-200 dark:bg-zinc-700 mx-1" />

            <!-- User Avatar + Dropdown -->
            <div id="user-dropdown-wrapper" class="relative">
                <button
                    @click.stop="showUserMenu = !showUserMenu; showNotifications = false"
                    class="flex items-center gap-2.5 rounded-xl px-2 py-1.5 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all group"
                    aria-label="User menu"
                >
                    <img
                        v-if="user.avatar && !avatarError"
                        :src="user.avatar"
                        :alt="user.name"
                        @error="avatarError = true"
                        class="h-7 w-7 rounded-full object-cover ring-2 ring-indigo-100 dark:ring-indigo-900"
                    />
                    <div v-else class="h-7 w-7 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-black ring-2 ring-indigo-100 dark:ring-indigo-900">
                        {{ user.name?.charAt(0) || 'A' }}
                    </div>
                    <div class="hidden md:block text-left">
                        <p class="text-[12px] font-bold text-slate-800 dark:text-zinc-100 leading-tight">{{ user.name }}</p>
                        <p class="text-[10px] text-slate-400 dark:text-zinc-500 leading-none mt-0.5">Super Admin</p>
                    </div>
                    <svg class="hidden md:block h-3 w-3 text-slate-400 group-hover:text-slate-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <Transition
                    enter-active-class="transition duration-150 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-1"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition duration-100 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-1"
                >
                    <UserDropdown v-if="showUserMenu" :user="user" @close="showUserMenu = false" />
                </Transition>
            </div>
        </div>
    </header>
</template>
