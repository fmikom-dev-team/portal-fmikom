<script setup lang="ts">
import StudentBottomNav from '@/components/wims/Mahasiswa/StudentBottomNav.vue';
import StudentSidebar from '@/components/wims/Mahasiswa/StudentSidebar.vue';
import StudentTopbar from '@/components/wims/Mahasiswa/StudentTopbar.vue';
</script>

<template>
    <div class="h-screen overflow-hidden bg-wims-bg text-wims-text transition-colors duration-300">
        <div class="flex h-full min-h-0">
            <StudentSidebar />

            <div class="flex min-w-0 min-h-0 flex-1 flex-col overflow-hidden">
                <StudentTopbar />

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
    </div>
</template>
