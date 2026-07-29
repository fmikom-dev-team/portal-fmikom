<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import {
	BookOpen,
	ChevronLeft,
	Globe,
	GraduationCap,
	LayoutGrid,
	Users,
	X,
} from "lucide-vue-next";
import { computed, ref } from "vue";
import AppLogo from "@/components/AppLogo.vue";
import { useCurrentUrl } from "@/composables/useCurrentUrl";
import { toUrl } from "@/lib/utils";
import { dashboard } from "@/routes";

const props = defineProps<{
	collapsed?: boolean;
	mobileOpen?: boolean;
}>();

const emit = defineEmits<{
	(e: "update:collapsed", value: boolean): void;
	(e: "update:mobileOpen", value: boolean): void;
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user || ({} as Record<string, any>));
const { isCurrentUrl } = useCurrentUrl();

// Hover highlighter style
const hoverStyle = ref({ top: "0px", height: "0px", opacity: 0 });

const handleMouseEnter = (e: MouseEvent) => {
	const el = e.currentTarget as HTMLElement;
	hoverStyle.value = {
		top: `${el.offsetTop}px`,
		height: `${el.offsetHeight}px`,
		opacity: 1,
	};
};

const handleMouseLeave = () => {
	hoverStyle.value.opacity = 0;
};
</script>

<template>
    <!-- Mobile Backdrop Overlay -->
    <Transition
        enter-active-class="transition-opacity duration-200 ease-out"
        enter-from-class="opacity-0"
        leave-active-class="transition-opacity duration-150 ease-in"
        leave-to-class="opacity-0"
    >
        <div
            v-if="mobileOpen"
            class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-xs lg:hidden"
            @click="emit('update:mobileOpen', false)"
        />
    </Transition>

    <!-- Sidebar Aside Container -->
    <aside
        :class="[
            'fixed inset-y-0 left-0 z-40 flex flex-col bg-white dark:bg-zinc-950 border-r border-slate-100 dark:border-zinc-800 transition-all duration-300',
            collapsed ? 'w-[72px] overflow-visible' : 'w-[260px] overflow-hidden',
            mobileOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full lg:translate-x-0'
        ]"
    >
        <!-- Header Row (Logo + Collapse Button) -->
        <div
            class="flex items-center h-[68px] shrink-0 border-b border-slate-100 dark:border-zinc-800 transition-all duration-300"
            :class="collapsed ? 'justify-center px-0' : 'justify-between px-4'"
        >
            <!-- Collapsed state: Click logo to expand -->
            <button
                v-if="collapsed"
                @click="emit('update:collapsed', false)"
                class="hidden lg:flex w-10 h-10 rounded-xl items-center justify-center hover:bg-slate-100 dark:hover:bg-zinc-900 transition-colors"
                title="Buka Sidebar"
            >
                <AppLogo />
            </button>

            <!-- Expanded state: App Logo + Collapse toggle / Mobile Close -->
            <template v-else>
                <Link :href="toUrl(dashboard())" class="flex items-center gap-2 overflow-hidden min-w-0">
                    <AppLogo />
                </Link>

                <!-- Desktop Collapse Button -->
                <button
                    @click="emit('update:collapsed', true)"
                    class="hidden lg:flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-[#2563eb] hover:bg-slate-100 dark:hover:bg-zinc-900 transition-colors shrink-0"
                    title="Tutup Sidebar"
                >
                    <ChevronLeft class="w-4 h-4" />
                </button>

                <!-- Mobile Close Button -->
                <button
                    @click="emit('update:mobileOpen', false)"
                    class="lg:hidden flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white shrink-0"
                >
                    <X class="w-4 h-4" />
                </button>
            </template>
        </div>

        <!-- Scrollable Navigation Content -->
        <div
            class="flex-1 overflow-y-auto py-4 space-y-4"
            :class="collapsed ? 'overflow-x-visible' : 'overflow-x-hidden'"
        >
            <!-- SECTION 1: GENERAL -->
            <div>
                <div
                    class="px-4 mb-2 overflow-hidden transition-all duration-200"
                    :class="collapsed ? 'h-0 opacity-0 mb-0' : 'h-auto opacity-100'"
                >
                    <span class="text-[10px] font-black text-slate-400 dark:text-zinc-500 tracking-widest uppercase">GENERAL</span>
                </div>
                <nav
                    :class="collapsed ? 'px-0 flex flex-col items-center gap-1.5' : 'px-3 flex flex-col gap-0.5 relative'"
                    @mouseleave="handleMouseLeave"
                >
                    <Link
                        :href="toUrl(dashboard())"
                        :class="[
                            'group relative flex items-center text-[13px] transition-all duration-150 h-10 z-10 rounded-xl',
                            isCurrentUrl(dashboard())
                                ? 'bg-slate-100 text-slate-900 dark:bg-zinc-800 dark:text-white font-bold'
                                : 'text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-white font-medium',
                            collapsed ? 'w-10 h-10 justify-center px-0' : 'w-full gap-3 px-4'
                        ]"
                        @mouseenter="handleMouseEnter"
                    >
                        <LayoutGrid class="w-[18px] h-[18px] shrink-0" />
                        <span v-if="!collapsed" class="truncate">Dashboard</span>
                        <!-- Collapsed Tooltip -->
                        <div
                            v-if="collapsed"
                            class="pointer-events-none fixed left-[84px] z-[99999] rounded-xl bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-xs font-bold px-3 py-1.5 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-all duration-200 shadow-2xl flex items-center gap-1.5 transform translate-x-1 group-hover:translate-x-0"
                        >
                            <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-900 dark:bg-slate-100 rotate-45"></div>
                            <span class="relative z-10">Dashboard</span>
                        </div>
                    </Link>
                </nav>
            </div>

            <!-- SECTION 2: MANAGEMENT (Admin / Super Admin) -->
            <div v-if="user.is_admin || user.is_super_admin">
                <div
                    class="px-4 mb-2 overflow-hidden transition-all duration-200"
                    :class="collapsed ? 'h-0 opacity-0 mb-0' : 'h-auto opacity-100'"
                >
                    <span class="text-[10px] font-black text-slate-400 dark:text-zinc-500 tracking-widest uppercase">MANAGEMENT</span>
                </div>
                <nav
                    :class="collapsed ? 'px-0 flex flex-col items-center gap-1.5' : 'px-3 flex flex-col gap-0.5 relative'"
                    @mouseleave="handleMouseLeave"
                >
                    <Link
                        href="/portal-admin"
                        :class="[
                            'group relative flex items-center text-[13px] transition-all duration-150 h-10 z-10 rounded-xl',
                            isCurrentUrl('/portal-admin')
                                ? 'bg-slate-100 text-slate-900 dark:bg-zinc-800 dark:text-white font-bold'
                                : 'text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-white font-medium',
                            collapsed ? 'w-10 h-10 justify-center px-0' : 'w-full gap-3 px-4'
                        ]"
                        @mouseenter="handleMouseEnter"
                    >
                        <Globe class="w-[18px] h-[18px] shrink-0" />
                        <span v-if="!collapsed" class="truncate">Portal Admin (Web)</span>
                        <div
                            v-if="collapsed"
                            class="pointer-events-none fixed left-[84px] z-[99999] rounded-xl bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-xs font-bold px-3 py-1.5 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-all duration-200 shadow-2xl flex items-center gap-1.5 transform translate-x-1 group-hover:translate-x-0"
                        >
                            <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-900 dark:bg-slate-100 rotate-45"></div>
                            <span class="relative z-10">Portal Admin (Web)</span>
                        </div>
                    </Link>

                    <Link
                        href="/workos"
                        :class="[
                            'group relative flex items-center text-[13px] transition-all duration-150 h-10 z-10 rounded-xl',
                            isCurrentUrl('/workos')
                                ? 'bg-slate-100 text-slate-900 dark:bg-zinc-800 dark:text-white font-bold'
                                : 'text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-white font-medium',
                            collapsed ? 'w-10 h-10 justify-center px-0' : 'w-full gap-3 px-4'
                        ]"
                        @mouseenter="handleMouseEnter"
                    >
                        <Users class="w-[18px] h-[18px] shrink-0" />
                        <span v-if="!collapsed" class="truncate">Manajemen Role User</span>
                        <div
                            v-if="collapsed"
                            class="pointer-events-none fixed left-[84px] z-[99999] rounded-xl bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-xs font-bold px-3 py-1.5 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-all duration-200 shadow-2xl flex items-center gap-1.5 transform translate-x-1 group-hover:translate-x-0"
                        >
                            <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-900 dark:bg-slate-100 rotate-45"></div>
                            <span class="relative z-10">Manajemen Role User</span>
                        </div>
                    </Link>
                </nav>
            </div>

            <!-- SECTION 3: LAYANAN UNUGHA -->
            <div>
                <div
                    class="px-4 mb-2 overflow-hidden transition-all duration-200"
                    :class="collapsed ? 'h-0 opacity-0 mb-0' : 'h-auto opacity-100'"
                >
                    <span class="text-[10px] font-black text-slate-400 dark:text-zinc-500 tracking-widest uppercase">LAYANAN UNUGHA</span>
                </div>
                <nav
                    :class="collapsed ? 'px-0 flex flex-col items-center gap-1.5' : 'px-3 flex flex-col gap-0.5 relative'"
                    @mouseleave="handleMouseLeave"
                >
                    <a
                        href="https://siakad.unugha.ac.id"
                        target="_blank"
                        rel="noopener noreferrer"
                        :class="[
                            'group relative flex items-center text-[13px] transition-all duration-150 h-10 z-10 rounded-xl text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-white font-medium',
                            collapsed ? 'w-10 h-10 justify-center px-0' : 'w-full gap-3 px-4'
                        ]"
                        @mouseenter="handleMouseEnter"
                    >
                        <GraduationCap class="w-[18px] h-[18px] shrink-0" />
                        <span v-if="!collapsed" class="truncate">Siakad UNUGHA</span>
                        <div
                            v-if="collapsed"
                            class="pointer-events-none fixed left-[84px] z-[99999] rounded-xl bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-xs font-bold px-3 py-1.5 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-all duration-200 shadow-2xl flex items-center gap-1.5 transform translate-x-1 group-hover:translate-x-0"
                        >
                            <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-900 dark:bg-slate-100 rotate-45"></div>
                            <span class="relative z-10">Siakad UNUGHA</span>
                        </div>
                    </a>

                    <a
                        href="https://bima.kemdikbud.go.id/"
                        target="_blank"
                        rel="noopener noreferrer"
                        :class="[
                            'group relative flex items-center text-[13px] transition-all duration-150 h-10 z-10 rounded-xl text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-white font-medium',
                            collapsed ? 'w-10 h-10 justify-center px-0' : 'w-full gap-3 px-4'
                        ]"
                        @mouseenter="handleMouseEnter"
                    >
                        <BookOpen class="w-[18px] h-[18px] shrink-0" />
                        <span v-if="!collapsed" class="truncate">SINTA BIMA</span>
                        <div
                            v-if="collapsed"
                            class="pointer-events-none fixed left-[84px] z-[99999] rounded-xl bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-xs font-bold px-3 py-1.5 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-all duration-200 shadow-2xl flex items-center gap-1.5 transform translate-x-1 group-hover:translate-x-0"
                        >
                            <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-900 dark:bg-slate-100 rotate-45"></div>
                            <span class="relative z-10">SINTA BIMA</span>
                        </div>
                    </a>

                    <a
                        href="https://unugha.ac.id"
                        target="_blank"
                        rel="noopener noreferrer"
                        :class="[
                            'group relative flex items-center text-[13px] transition-all duration-150 h-10 z-10 rounded-xl text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-white font-medium',
                            collapsed ? 'w-10 h-10 justify-center px-0' : 'w-full gap-3 px-4'
                        ]"
                        @mouseenter="handleMouseEnter"
                    >
                        <Globe class="w-[18px] h-[18px] shrink-0" />
                        <span v-if="!collapsed" class="truncate">Web Utama UNUGHA</span>
                        <div
                            v-if="collapsed"
                            class="pointer-events-none fixed left-[84px] z-[99999] rounded-xl bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-xs font-bold px-3 py-1.5 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-all duration-200 shadow-2xl flex items-center gap-1.5 transform translate-x-1 group-hover:translate-x-0"
                        >
                            <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-900 dark:bg-slate-100 rotate-45"></div>
                            <span class="relative z-10">Web Utama UNUGHA</span>
                        </div>
                    </a>
                </nav>
            </div>
        </div>

        <!-- Footer Row -->
        <div class="px-4 py-3 shrink-0 border-t border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-950">
            <p
                v-if="!collapsed"
                class="text-[10px] font-semibold text-slate-400 dark:text-zinc-500 text-center transition-all duration-200"
            >
                &copy; 2026 Portal FMIKOM
            </p>
        </div>
    </aside>
</template>
