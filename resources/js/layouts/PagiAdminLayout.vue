<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import { ref } from "vue";
import Header from "@/components/Admin/Header.vue";
import MobileSidebar from "@/components/Admin/MobileSidebar.vue";
import Sidebar from "@/components/Admin/Sidebar.vue";

defineProps<{
	title?: string;
}>();

const collapsed = ref(false);
const mobileOpen = ref(false);
</script>

<template>
    <Head>
        <title>{{ title ? `${title} — Admin PAGI` : 'Admin PAGI' }}</title>
    </Head>

    <div class="min-h-screen bg-[#f8f9fc] dark:bg-zinc-950 font-sans selection:bg-indigo-500 selection:text-white">

        <!-- Mobile Overlay -->
        <MobileSidebar :open="mobileOpen" @update:open="mobileOpen = $event" />

        <!-- Sidebar -->
        <Sidebar
            :collapsed="collapsed"
            :mobile-open="mobileOpen"
            @update:collapsed="collapsed = $event"
            @update:mobile-open="mobileOpen = $event"
        />

        <!-- Main Content Area -->
        <div
            class="flex min-h-screen flex-col transition-all duration-300 ease-in-out"
            :class="collapsed ? 'lg:ml-[72px]' : 'lg:ml-[240px]'"
        >
            <!-- Sticky Header -->
            <Header
                :collapsed="collapsed"
                @toggle-mobile="mobileOpen = !mobileOpen"
            />

            <!-- Page Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 transition-colors duration-300">
                <slot />
            </main>

            <!-- Footer -->
            <footer class="px-6 py-4 border-t border-slate-100 dark:border-zinc-800">
                <p class="text-[11px] text-slate-400 dark:text-zinc-600 text-center">
                    KaryaKampus Admin — PAGI Module · {{ new Date().getFullYear() }}
                </p>
            </footer>
        </div>
    </div>
</template>

<style scoped>
::-webkit-scrollbar { width: 5px; height: 5px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }
.dark ::-webkit-scrollbar-thumb { background: #3f3f46; }
::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
.dark ::-webkit-scrollbar-thumb:hover { background: #52525b; }
</style>
