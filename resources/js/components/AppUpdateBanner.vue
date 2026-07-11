<script setup lang="ts">
import { useServiceWorker } from '@/composables/useServiceWorker';
import { ref, onMounted, onUnmounted } from 'vue';

const { updateAvailable, applyUpdate, snoozeUpdate, isSnoozed } = useServiceWorker();
const visible = ref(false);

// Interval untuk check apakah snooze sudah habis
let intervalId: any = null;

function checkVisibility() {
    const testMode = typeof window !== 'undefined' && new URLSearchParams(window.location.search).has('test_update');
    if (testMode || (updateAvailable.value && !isSnoozed())) {
        visible.value = true;
    } else {
        visible.value = false;
    }
}

// Pantau perubahan state update dan snooze
onMounted(() => {
    checkVisibility();
    intervalId = setInterval(checkVisibility, 3000); // Check setiap 3 detik
});

onUnmounted(() => {
    if (intervalId) clearInterval(intervalId);
});

function handleLater() {
    snoozeUpdate();
    visible.value = false;
}
</script>

<template>
    <Transition name="slide-fade">
        <div v-if="visible" class="fixed bottom-6 right-6 z-[9999] max-w-sm sm:max-w-md w-full bg-white dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl shadow-lg p-5 flex flex-col gap-4 font-sans text-zinc-900 dark:text-zinc-100">
            <div class="flex items-start gap-4">
                <!-- Circular Icon with arrow rotate -->
                <div class="flex-shrink-0 w-11 h-11 rounded-full border border-zinc-200 dark:border-zinc-800 flex items-center justify-center text-zinc-500 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-800/50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin-slow">
                        <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67" />
                    </svg>
                </div>
                
                <!-- Text -->
                <div class="flex-1 min-w-0 pr-6 relative">
                    <h3 class="text-[15px] font-semibold tracking-tight text-zinc-900 dark:text-white mb-1">
                        Version update is now available!
                    </h3>
                    <p class="text-[13px] text-zinc-500 dark:text-zinc-400 leading-relaxed font-normal">
                        This update contains several bug fixes and performance improvements.
                    </p>
                    
                    <!-- Close button (x) -->
                    <button @click="handleLater" class="absolute top-0 right-0 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors p-1" aria-label="Dismiss">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex items-center gap-3 pl-[3.75rem]">
                <button @click="applyUpdate" class="px-5 py-2 text-[13px] font-medium bg-zinc-950 hover:bg-zinc-800 dark:bg-zinc-100 dark:hover:bg-zinc-200 text-white dark:text-zinc-900 rounded-lg transition-colors shadow-sm">
                    Install
                </button>
                <button @click="handleLater" class="px-3 py-2 text-[13px] font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200 transition-colors">
                    Later
                </button>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.animate-spin-slow {
    animation: spin 8s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.slide-fade-enter-active {
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.slide-fade-leave-active {
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.slide-fade-enter-from,
.slide-fade-leave-to {
    transform: translateY(20px) scale(0.97);
    opacity: 0;
}
</style>
