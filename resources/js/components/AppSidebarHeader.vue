<script setup lang="ts">
import { Link, router, usePage } from "@inertiajs/vue3";
import { ChevronDown, LogOut, Settings } from "lucide-vue-next";
import { computed, onMounted, onUnmounted, ref } from "vue";
import UserMenuContent from "@/components/UserMenuContent.vue";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { SidebarTrigger } from "@/components/ui/sidebar";
import { useInitials } from "@/composables/useInitials";
import { edit as editProfile } from "@/routes/profile";

const page = usePage();
const user = computed(
    () => page.props.auth?.user || { name: "User", email: "" },
);
const { getInitials } = useInitials();

// Loading state for profile skeleton
const isLoading = ref(true);

// Scroll-aware for desktop header (optional shadow)
const isScrolled = ref(false);
const handleScroll = () => {
    isScrolled.value = globalThis.window.scrollY > 8;
};
onMounted(() => {
    globalThis.window.addEventListener("scroll", handleScroll, {
        passive: true,
    });
    setTimeout(() => {
        isLoading.value = false;
    }, 800);
});
onUnmounted(() =>
    globalThis.window.removeEventListener("scroll", handleScroll),
);
</script>

<template>
    <header class="w-full shrink-0">
        <!-- MOBILE HEADER: Blue gradient (Livin'-style), fixed -->
        <div
            class="flex md:hidden w-full items-center justify-between px-4 py-3 h-[68px] shrink-0 fixed top-0 left-0 right-0 z-30 bg-linear-to-r from-[#1d4ed8] to-[#3B82F6]"
        >
            <!-- Left Side: Skeleton Profile Loader -->
            <div
                v-if="isLoading"
                class="flex items-center gap-3 flex-1 min-w-0 animate-pulse"
            >
                <div
                    class="h-[46px] w-[46px] rounded-full bg-white/20 ring-2 ring-white/10 shadow-sm shrink-0"
                ></div>
                <div class="flex flex-col gap-1.5 min-w-0">
                    <div class="h-4 w-24 bg-white/20 rounded"></div>
                    <div class="h-3 w-20 bg-white/10 rounded"></div>
                </div>
            </div>
            <!-- Left Side: Clickable Avatar + User Info (goes to profile settings) -->
            <Link
                v-else
                :href="editProfile().url"
                class="flex items-center gap-3 flex-1 min-w-0"
            >
                <Avatar
                    class="h-[46px] w-[46px] overflow-hidden rounded-full ring-2 ring-white/30 shadow-sm shrink-0 transition-transform active:scale-95"
                >
                    <AvatarImage
                        v-if="user.avatar"
                        :src="user.avatar"
                        :alt="user.name"
                        class="object-cover"
                    />
                    <AvatarFallback
                        class="rounded-full bg-white/20 text-white text-sm font-bold"
                    >
                        {{ getInitials(user.name) }}
                    </AvatarFallback>
                </Avatar>
                <div class="flex flex-col text-left leading-tight min-w-0">
                    <span
                        class="text-sm font-extrabold tracking-tight text-white truncate max-w-[160px]"
                        >{{ user.name }}</span
                    >
                    <span
                        class="text-[11px] font-medium text-blue-100 mt-0.5 truncate max-w-[160px]"
                    >
                        {{ user.role_title || "User / Mahasiswa" }}
                    </span>
                </div>
            </Link>

            <!-- Right Side: Settings + Logout -->
            <div class="flex items-center gap-3 text-white shrink-0">
                <!-- Settings Icon -->
                <Link
                    :href="editProfile().url"
                    class="hover:opacity-75 transition-opacity p-1"
                    title="Pengaturan"
                >
                    <Settings class="w-[22px] h-[22px] stroke-[1.75]" />
                </Link>

                <!-- Logout Icon -->
                <button
                    @click="router.post('/logout')"
                    class="hover:opacity-75 transition-opacity p-1"
                    title="Keluar"
                >
                    <LogOut class="w-[22px] h-[22px] stroke-[1.75]" />
                </button>
            </div>
        </div>

        <!-- DESKTOP HEADER: Standard compact header -->
        <div
            class="hidden md:flex h-14 w-full items-center justify-between px-6 bg-transparent border-b-0 mb-1"
        >
            <!-- Left side toggle -->
            <div class="flex items-center gap-4 flex-1">
                <SidebarTrigger class="-ml-2 hidden" />
            </div>

            <!-- Right side dropdown skeleton -->
            <div
                v-if="isLoading"
                class="flex items-center gap-3 py-1 pl-2 pr-3 rounded-xl border border-transparent animate-pulse shrink-0"
            >
                <div
                    class="h-[36px] w-[36px] rounded-full bg-slate-200 dark:bg-zinc-800 ring-2 ring-slate-100/50 dark:ring-zinc-900/50 shadow-sm shrink-0"
                ></div>
                <div class="flex flex-col gap-1 text-left">
                    <div
                        class="h-3.5 w-24 bg-slate-200 dark:bg-zinc-800 rounded"
                    ></div>
                    <div
                        class="h-2.5 w-14 bg-slate-100 dark:bg-zinc-800/80 rounded"
                    ></div>
                </div>
                <div
                    class="h-3.5 w-3.5 bg-slate-200 dark:bg-zinc-800 rounded"
                ></div>
            </div>
            <!-- Right side dropdown -->
            <div v-else class="flex items-center gap-2 shrink-0">
                <DropdownMenu>
                    <DropdownMenuTrigger
                        class="flex items-center gap-3 py-1 pl-2 pr-3 rounded-xl outline-none hover:bg-slate-50 dark:hover:bg-zinc-900/60 border border-transparent hover:border-slate-100 dark:hover:border-zinc-850 transition-all duration-300 group"
                    >
                        <Avatar
                            class="h-[36px] w-[36px] overflow-hidden rounded-full ring-2 ring-slate-200/80 dark:ring-zinc-800 shadow-sm transition-all duration-300 group-hover:ring-indigo-500/30 dark:group-hover:ring-zinc-700"
                        >
                            <AvatarImage
                                v-if="user.avatar"
                                :src="user.avatar"
                                :alt="user.name"
                                class="object-cover"
                            />
                            <AvatarFallback
                                class="rounded-full bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 text-[10px] font-bold"
                            >
                                {{ getInitials(user.name) }}
                            </AvatarFallback>
                        </Avatar>
                        <div class="flex flex-col text-left leading-none">
                            <span
                                class="text-[10px] sm:text-xs font-bold text-slate-800 dark:text-zinc-200 tracking-tight uppercase truncate max-w-[180px]"
                                >{{ user.name }}</span
                            >
                            <span
                                class="text-[9px] sm:text-[10px] font-semibold text-slate-400 dark:text-zinc-500 mt-0.5 tracking-wide uppercase truncate max-w-[180px]"
                            >
                                {{ user.role_title || "User" }}
                            </span>
                        </div>
                        <ChevronDown
                            class="h-3.5 w-3.5 text-slate-400 dark:text-zinc-500 transition-transform duration-200 group-hover:translate-y-px"
                        />
                    </DropdownMenuTrigger>
                    <DropdownMenuContent
                        class="w-56 rounded-xl border border-gray-100 p-2 shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
                        align="end"
                        :side-offset="8"
                    >
                        <UserMenuContent :user="user" />
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>
    </header>
</template>
