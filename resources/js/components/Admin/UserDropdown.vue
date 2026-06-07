<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { Bell, LogOut, Settings, Shield, User } from "lucide-vue-next";
import { ref } from "vue";

defineProps<{
	user: {
		name: string;
		email: string;
		avatar?: string;
	};
}>();

const avatarError = ref(false);

const emit = defineEmits<{ close: [] }>();

const menuItems = [
	{ label: "Profil Saya", icon: User, href: "/settings/profile" },
	{ label: "Pengaturan", icon: Settings, href: "/pagi/admin/settings" },
	{
		label: "Notifikasi",
		icon: Bell,
		href: "/pagi/admin/settings/notifications",
	},
	{ label: "Keamanan", icon: Shield, href: "/settings/password" },
];
</script>

<template>
    <div class="absolute right-0 top-full mt-2 w-[220px] rounded-2xl bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 shadow-xl shadow-slate-200/60 dark:shadow-zinc-900 z-50 overflow-hidden">

        <!-- User Info -->
        <div class="px-4 py-3.5 border-b border-slate-100 dark:border-zinc-800">
            <div class="flex items-center gap-2.5">
                <img
                    v-if="user.avatar && !avatarError"
                    :src="user.avatar"
                    :alt="user.name"
                    @error="avatarError = true"
                    class="h-9 w-9 rounded-full object-cover ring-2 ring-indigo-100 dark:ring-indigo-900 shrink-0"
                />
                <div v-else class="h-9 w-9 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center text-white text-sm font-black ring-2 ring-indigo-100 dark:ring-indigo-900 shrink-0">
                    {{ user.name?.charAt(0) }}
                </div>
                <div class="min-w-0">
                    <p class="text-[13px] font-bold text-slate-800 dark:text-zinc-100 truncate">{{ user.name }}</p>
                    <p class="text-[11px] text-slate-400 dark:text-zinc-500 truncate">{{ user.email }}</p>
                </div>
            </div>
            <div class="mt-2.5 flex items-center gap-1.5">
                <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 text-[10px] font-black text-indigo-700 dark:text-indigo-400">
                    <Shield class="h-2.5 w-2.5" /> Super Admin
                </span>
            </div>
        </div>

        <!-- Menu Items -->
        <div class="py-1.5">
            <Link
                v-for="item in menuItems"
                :key="item.href"
                :href="item.href"
                @click="emit('close')"
                class="flex items-center gap-3 px-4 py-2.5 text-[13px] font-medium text-slate-600 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800 hover:text-slate-900 dark:hover:text-zinc-100 transition-colors group"
            >
                <component :is="item.icon" class="h-3.5 w-3.5 text-slate-400 dark:text-zinc-500 group-hover:text-indigo-500 transition-colors shrink-0" />
                {{ item.label }}
            </Link>
        </div>

        <!-- Divider -->
        <div class="h-px bg-slate-100 dark:bg-zinc-800" />

        <!-- Logout -->
        <div class="py-1.5">
            <Link
                href="/logout"
                method="post"
                as="button"
                class="flex w-full items-center gap-3 px-4 py-2.5 text-[13px] font-medium text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 hover:text-rose-600 transition-colors group"
                @click="emit('close')"
            >
                <LogOut class="h-3.5 w-3.5 shrink-0" />
                Keluar
            </Link>
        </div>
    </div>
</template>
