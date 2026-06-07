<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import { computed, onMounted, onUnmounted, ref } from "vue";

defineProps<{
	activeLabel: string;
}>();

const emit = defineEmits<(e: "toggleSidebar") => void>();

const page = usePage();
const authUser = computed(() => (page.props as any).auth?.user);
const userInitial = computed(() =>
	(authUser.value?.name ?? "A").charAt(0).toUpperCase(),
);
const userName = computed(() => authUser.value?.name ?? "Admin");
const userEmail = computed(() => authUser.value?.email ?? "");

// ── Project dropdown ───────────────────────────────────────────────
const projectOpen = ref(false);
const currentProject = ref("Web Dev");
const projects = ["Web Dev", "Production", "Staging App", "FMIKOM Portal"];

function selectProject(p: string) {
	currentProject.value = p;
	projectOpen.value = false;
}

// ── Environment dropdown ───────────────────────────────────────────
const envOpen = ref(false);
const currentEnv = ref("Staging");
const environments = [
	{ label: "Staging", color: "#f59e0b" },
	{ label: "Production", color: "#10b981" },
	{ label: "Development", color: "#6b7280" },
];
const currentEnvColor = computed(
	() =>
		environments.find((e) => e.label === currentEnv.value)?.color ?? "#f59e0b",
);

function selectEnv(e: { label: string; color: string }) {
	currentEnv.value = e.label;
	envOpen.value = false;
}

// ── User dropdown ──────────────────────────────────────────────────
const userOpen = ref(false);

// ── Search ────────────────────────────────────────────────────────
const searchExpanded = ref(false);
const searchQuery = ref("");
const searchInputRef = ref<HTMLInputElement | null>(null);

function toggleSearch() {
	searchExpanded.value = !searchExpanded.value;
	if (searchExpanded.value) {
		setTimeout(() => searchInputRef.value?.focus(), 50);
	} else {
		searchQuery.value = "";
	}
}

// ── Close all on outside click ─────────────────────────────────────
function closeAll(e: MouseEvent) {
	const target = e.target as HTMLElement;
	if (!target.closest("[data-dropdown]")) {
		projectOpen.value = false;
		envOpen.value = false;
		userOpen.value = false;
		if (!target.closest("[data-search]")) {
			searchExpanded.value = false;
			searchQuery.value = "";
		}
	}
}

onMounted(() => document.addEventListener("click", closeAll));
onUnmounted(() => document.removeEventListener("click", closeAll));
</script>

<template>
    <header
        class="wos-header flex items-center justify-between bg-white border-b border-[#e5e7eb] px-3 shrink-0 z-30 relative"
        style="height: 52px; font-family: var(--wos-font)"
    >
        <!-- ── Left ── -->
        <div class="flex items-center gap-1">
            <!-- Mobile hamburger -->
            <button
                class="md:hidden p-1.5 rounded-md text-[#6b7280] hover:bg-[#f3f4f6] hover:text-[#111827] transition-colors"
                aria-label="Open sidebar"
                @click.stop="emit('toggleSidebar')"
            >
                <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <!-- Project dropdown -->
            <div class="relative hidden sm:block" data-dropdown>
                <button
                    class="flex items-center gap-1.5 h-8 px-2 rounded-md text-[13.5px] font-semibold text-[#111827] hover:bg-[#f3f4f6] transition-colors"
                    @click.stop="projectOpen = !projectOpen; envOpen = false; userOpen = false"
                >
                    {{ currentProject }}
                    <svg class="w-[15px] h-[15px] text-[#9ca3af]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <!-- Dropdown menu -->
                <div
                    v-show="projectOpen"
                    class="absolute top-full left-0 mt-1 w-44 bg-white border border-[#e5e7eb] rounded-lg shadow-lg py-1 z-50"
                >
                    <button
                        v-for="p in projects"
                        :key="p"
                        class="w-full flex items-center gap-2.5 px-3 py-2 text-[13px] text-[#374151] hover:bg-[#f9fafb] transition-colors text-left"
                        @click.stop="selectProject(p)"
                    >
                        <svg v-if="p === currentProject" class="w-3.5 h-3.5 shrink-0" style="color: #2563EB" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span v-else class="w-3.5 h-3.5 shrink-0" />
                        <span :class="p === currentProject ? 'font-semibold text-[#111827]' : ''">{{ p }}</span>
                    </button>
                </div>
            </div>

            <!-- Divider -->
            <div class="hidden sm:block w-px h-3.5 bg-[#e5e7eb] mx-0.5" aria-hidden="true" />

            <!-- Environment dropdown -->
            <div class="relative hidden sm:block" data-dropdown>
                <button
                    class="flex items-center gap-1.5 h-8 px-2 rounded-md text-[13px] text-[#374151] hover:bg-[#f3f4f6] transition-colors"
                    @click.stop="envOpen = !envOpen; projectOpen = false; userOpen = false"
                >
                    <span class="w-2 h-2 rounded-full shrink-0" :style="{ backgroundColor: currentEnvColor }" />
                    {{ currentEnv }}
                    <svg class="w-[15px] h-[15px] text-[#9ca3af]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <!-- Dropdown menu -->
                <div
                    v-show="envOpen"
                    class="absolute top-full left-0 mt-1 w-44 bg-white border border-[#e5e7eb] rounded-lg shadow-lg py-1 z-50"
                >
                    <button
                        v-for="env in environments"
                        :key="env.label"
                        class="w-full flex items-center gap-2.5 px-3 py-1.5 text-[12.5px] text-[#374151] hover:bg-[#f9fafb] transition-colors text-left"
                        @click.stop="selectEnv(env)"
                    >
                        <span class="w-2 h-2 rounded-full shrink-0" :style="{ backgroundColor: env.color }" />
                        <span :class="env.label === currentEnv ? 'font-semibold text-[#111827]' : ''">{{ env.label }}</span>
                        <svg v-if="env.label === currentEnv" class="w-3 h-3 ml-auto shrink-0" style="color: #2563EB" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- ── Right ── -->
        <div class="flex items-center gap-0.5">

            <!-- Search (expandable) -->
            <div class="relative hidden sm:flex items-center" data-search>
                <Transition
                    enter-active-class="transition-all duration-200"
                    enter-from-class="opacity-0 w-7"
                    leave-active-class="transition-all duration-150"
                    leave-to-class="opacity-0 w-7"
                >
                    <div v-if="searchExpanded" class="flex items-center gap-1.5 h-7 px-2.5 rounded-md bg-white border border-[#2563EB] w-52 ring-2 ring-[#2563EB]/10">
                        <svg class="w-3.5 h-3.5 text-[#9ca3af] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" />
                        </svg>
                        <input
                            ref="searchInputRef"
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search..."
                            class="flex-1 bg-transparent text-[12px] text-[#111827] placeholder-[#9ca3af] outline-none"
                            @keydown.escape="searchExpanded = false; searchQuery = ''"
                        />
                        <kbd class="flex items-center h-4 px-1 rounded text-[9px] font-medium text-[#9ca3af] bg-[#f3f4f6] border border-[#e5e7eb]">Esc</kbd>
                    </div>
                </Transition>

                <button
                    v-if="!searchExpanded"
                    class="flex items-center gap-2 h-7 px-2.5 rounded-md bg-white border border-[#e5e7eb] text-[#9ca3af] hover:border-[#d1d5db] hover:bg-[#f9fafb] transition-all"
                    aria-label="Search"
                    @click.stop="toggleSearch"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" />
                    </svg>
                    <span class="text-[12px]">Search</span>
                    <kbd class="hidden md:flex items-center h-4 px-1 rounded text-[9px] font-medium text-[#9ca3af] bg-[#f3f4f6] border border-[#e5e7eb] ml-1">⌘K</kbd>
                </button>
            </div>

            <!-- User avatar + dropdown -->
            <div class="relative" data-dropdown>
                <button
                    class="flex items-center gap-2 h-9 px-2 hover:bg-[#f9fafb] rounded-md transition-colors text-left"
                    aria-label="User menu"
                    @click.stop="userOpen = !userOpen; projectOpen = false; envOpen = false"
                >
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-[11px] font-bold shrink-0 overflow-hidden" style="background-color: #2563EB">
                        <img v-if="authUser?.avatar" :src="authUser.avatar" :alt="userName" class="w-full h-full object-cover" />
                        <span v-else>{{ userInitial }}</span>
                    </div>
                    <div class="hidden sm:flex flex-col select-none">
                        <span class="text-[13px] font-semibold text-[#111827] leading-tight">{{ userName }}</span>
                        <span class="text-[10px] text-[#6b7280] leading-none mt-0.5">Admin</span>
                    </div>
                    <svg class="w-3.5 h-3.5 text-[#9ca3af] hidden sm:block shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- User dropdown menu -->
                <div
                    v-show="userOpen"
                    class="absolute top-full right-0 mt-1 w-52 bg-white border border-[#e5e7eb] rounded-lg shadow-lg py-1 z-50"
                >
                    <!-- User info header -->
                    <div class="px-3 py-2 border-b border-[#f3f4f6]">
                        <p class="text-[12.5px] font-semibold text-[#111827] truncate">{{ userName }}</p>
                        <p class="text-[11px] text-[#9ca3af] truncate">{{ userEmail }}</p>
                    </div>

                    <div class="py-1">
                        <button class="w-full flex items-center gap-2.5 px-3 py-1.5 text-[12.5px] text-[#374151] hover:bg-[#f9fafb] transition-colors text-left">
                            <svg class="w-3.5 h-3.5 text-[#9ca3af]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profile
                        </button>
                        <button class="w-full flex items-center gap-2.5 px-3 py-1.5 text-[12.5px] text-[#374151] hover:bg-[#f9fafb] transition-colors text-left">
                            <svg class="w-3.5 h-3.5 text-[#9ca3af]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
