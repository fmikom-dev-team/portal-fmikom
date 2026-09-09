<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import StudentBottomNav from '@/components/Modules/Wims/Mahasiswa/StudentBottomNav.vue';
import StudentSidebar from '@/components/Modules/Wims/Mahasiswa/StudentSidebar.vue';
import StudentTopbar from '@/components/Modules/Wims/Mahasiswa/StudentTopbar.vue';
import { useWimsStudentAppearance } from '@/composables/useWimsStudentAppearance';
import AppToast from '@/pages/WorkOs/components/ui/AppToast.vue';

const { resolvedAppearance } = useWimsStudentAppearance();
const sidebarCollapsed = ref(false);
const studentThemeClass = computed(() =>
    resolvedAppearance.value === 'dark' ? 'dark wims-student-dark' : '',
);
let initialHtmlDarkClass = false;
let initialBodyDarkClass = false;

const syncStudentDocumentTheme = () => {
    if (typeof document === 'undefined') {
        return;
    }

    const html = document.documentElement;
    const body = document.body;
    const isDark = resolvedAppearance.value === 'dark';

    html.classList.add('wims-student-page');
    body.classList.add('wims-student-page');
    body.classList.add('wims-student-body');

    html.classList.toggle('dark', isDark);
    body.classList.toggle('dark', isDark);
};

const cleanupStudentDocumentTheme = () => {
    if (typeof document === 'undefined') {
        return;
    }

    const html = document.documentElement;
    const body = document.body;

    html.classList.remove('wims-student-page');
    body.classList.remove('wims-student-page');
    body.classList.remove('wims-student-body');
    html.classList.toggle('dark', initialHtmlDarkClass);
    body.classList.toggle('dark', initialBodyDarkClass);
};

onMounted(() => {
    if (typeof document !== 'undefined') {
        initialHtmlDarkClass = document.documentElement.classList.contains('dark');
        initialBodyDarkClass = document.body.classList.contains('dark');
    }
    syncStudentDocumentTheme();

    sidebarCollapsed.value = window.localStorage.getItem('wims-student-sidebar-collapsed') === 'true';
});

watch(resolvedAppearance, () => {
    syncStudentDocumentTheme();
});

watch(sidebarCollapsed, (collapsed) => {
    if (typeof window !== 'undefined') {
        window.localStorage.setItem('wims-student-sidebar-collapsed', String(collapsed));
    }
});

onBeforeUnmount(() => {
    cleanupStudentDocumentTheme();
});
</script>

<template>
    <div
        class="wims-shell wims-student-shell h-screen overflow-hidden bg-wims-bg text-wims-text transition-colors duration-300"
        :class="studentThemeClass"
    >
        <div class="flex h-full min-h-0">
            <StudentSidebar :collapsed="sidebarCollapsed" />

            <div class="flex min-w-0 min-h-0 flex-1 flex-col overflow-hidden">
                <StudentTopbar :sidebar-collapsed="sidebarCollapsed" @toggle-sidebar="sidebarCollapsed = !sidebarCollapsed" />

                <main class="min-w-0 min-h-0 flex-1 overflow-y-auto overflow-x-hidden">
                    <div
                        class="mx-auto w-full max-w-[1320px] px-4 py-4 pb-32 sm:px-6 sm:py-6 sm:pb-32 lg:px-8 lg:py-8 lg:pb-8 xl:px-10"
                    >
                        <slot />
                    </div>
                </main>
            </div>
        </div>

        <StudentBottomNav />
        <AppToast />
    </div>
</template>
