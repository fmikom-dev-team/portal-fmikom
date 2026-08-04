<script setup lang="ts">
import { Link, router } from "@inertiajs/vue3";
import { LayoutGrid, LogOut, Settings } from "lucide-vue-next";
import { computed } from "vue";
import UserInfo from "@/components/UserInfo.vue";
import {
	DropdownMenuGroup,
	DropdownMenuItem,
	DropdownMenuLabel,
	DropdownMenuSeparator,
} from "@/components/ui/dropdown-menu";
import { logout } from "@/routes";
import { edit } from "@/routes/profile";
import type { User } from "@/types";

type Props = {
	user: User;
};

const handleLogout = () => {
	router.flushAll();
};

defineProps<Props>();

const settingsUrl = computed(() => {
	if (typeof window !== "undefined" && window.location.pathname.startsWith("/workos")) {
		return "/workos/settings";
	}
	return edit();
});
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full cursor-pointer flex items-center" :href="settingsUrl" prefetch>
                <Settings class="mr-2 h-4 w-4 text-slate-500" />
                Settings
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full cursor-pointer flex items-center" href="/workos/organizations" prefetch>
                <LayoutGrid class="mr-2 h-4 w-4 text-slate-500" />
                Portal Modules
            </Link>
        </DropdownMenuItem>
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
