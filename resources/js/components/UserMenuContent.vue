<script setup lang="ts">
import { Link, router } from "@inertiajs/vue3";
import { LayoutGrid, LogOut, Settings, User as UserIcon } from "lucide-vue-next";
import { computed } from "vue";
import UserInfo from "@/components/UserInfo.vue";
import {
	DropdownMenuGroup,
	DropdownMenuItem,
	DropdownMenuLabel,
	DropdownMenuSeparator,
} from "@/components/ui/dropdown-menu";
import { logout } from "@/routes";
import type { User } from "@/types";

type Props = {
	user: User;
};

const handleLogout = () => {
	router.flushAll();
};

defineProps<Props>();

const currentPath = computed(() => {
	if (typeof window !== "undefined") {
		return window.location.pathname;
	}
	return "";
});

const isWorkOs = computed(() => currentPath.value.startsWith("/workos"));
const isPortalAdmin = computed(() => currentPath.value.startsWith("/portal-admin"));
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <!-- In WorkOS context -->
        <template v-if="isWorkOs">
            <DropdownMenuItem :as-child="true">
                <Link class="block w-full cursor-pointer flex items-center" href="/workos/settings" prefetch>
                    <Settings class="mr-2 h-4 w-4 text-slate-500" />
                    Settings
                </Link>
            </DropdownMenuItem>
            <DropdownMenuItem :as-child="true">
                <Link class="block w-full cursor-pointer flex items-center" href="/dashboard" prefetch>
                    <LayoutGrid class="mr-2 h-4 w-4 text-slate-500" />
                    Portal Modules
                </Link>
            </DropdownMenuItem>
        </template>

        <!-- In Portal Admin context -->
        <template v-else-if="isPortalAdmin">
            <DropdownMenuItem :as-child="true">
                <Link class="block w-full cursor-pointer flex items-center" href="/portal-admin/settings" prefetch>
                    <Settings class="mr-2 h-4 w-4 text-slate-500" />
                    Settings
                </Link>
            </DropdownMenuItem>
            <DropdownMenuItem :as-child="true">
                <Link class="block w-full cursor-pointer flex items-center" href="/dashboard" prefetch>
                    <LayoutGrid class="mr-2 h-4 w-4 text-slate-500" />
                    Portal Utama
                </Link>
            </DropdownMenuItem>
        </template>

        <!-- Default: Portal Modules / Dashboard context -->
        <template v-else>
            <DropdownMenuItem :as-child="true">
                <Link class="block w-full cursor-pointer flex items-center" href="/settings/profile" prefetch>
                    <UserIcon class="mr-2 h-4 w-4 text-slate-500" />
                    Profile
                </Link>
            </DropdownMenuItem>
            <DropdownMenuItem :as-child="true">
                <Link class="block w-full cursor-pointer flex items-center" href="/settings" prefetch>
                    <Settings class="mr-2 h-4 w-4 text-slate-500" />
                    Settings
                </Link>
            </DropdownMenuItem>
        </template>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <Link
            class="block w-full cursor-pointer flex items-center"
            :href="logout()"
            method="post"
            @click="handleLogout"
            as="button"
            data-test="logout-button"
        >
            <LogOut class="mr-2 h-4 w-4 text-rose-500" />
            Log out
        </Link>
    </DropdownMenuItem>
</template>
