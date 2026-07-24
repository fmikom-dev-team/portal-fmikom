<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted, nextTick } from "vue";
import { Search, Loader2, ArrowRight, CornerDownLeft } from "lucide-vue-next";

const props = withDefaults(
    defineProps<{
        modelValue?: string;
        endpoint?: string;
        placeholder?: string;
        widthClass?: string;
        queryParams?: Record<string, any>;
        showDropdown?: boolean;
    }>(),
    {
        modelValue: "",
        endpoint: "",
        placeholder: "Cari sesuatu...",
        widthClass: "w-[200px] sm:w-[260px]",
        queryParams: () => ({}),
        showDropdown: true,
    }
);

const emit = defineEmits<{
    (e: "update:modelValue", value: string): void;
    (e: "select", item: any): void;
}>();

const searchQuery = ref(props.modelValue);
const searchResults = ref<any[]>([]);
const isSearching = ref(false);
const isOpen = ref(false);
const activeIndex = ref(-1);
let debounceTimeout: any = null;

const inputRef = ref<HTMLInputElement | null>(null);

// Sync prop changes to local ref
watch(() => props.modelValue, (newVal) => {
    searchQuery.value = newVal;
});

// Emit update:modelValue event when searchQuery changes
watch(searchQuery, (newVal) => {
    emit("update:modelValue", newVal);
});

// Watch search query changes to fetch results
watch(searchQuery, (newVal) => {
    clearTimeout(debounceTimeout);
    if (!props.endpoint || newVal.length < 2) {
        searchResults.value = [];
        return;
    }

    isSearching.value = true;
    activeIndex.value = -1;

    debounceTimeout = setTimeout(async () => {
        try {
            const urlObj = new URL(props.endpoint!, window.location.origin);
            urlObj.searchParams.append("q", newVal);
            
            for (const [key, val] of Object.entries(props.queryParams)) {
                if (val !== undefined && val !== null) {
                    urlObj.searchParams.append(key, String(val));
                }
            }

            const res = await fetch(urlObj.toString());
            const data = await res.json();
            searchResults.value = data.results || [];
        } catch (e) {
            console.error("Instant search fetch failed:", e);
        } finally {
            isSearching.value = false;
        }
    }, 300);
});

// Focus input on open
watch(isOpen, (newVal) => {
    if (newVal) {
        // Prevent body scrolling when dialog is open
        document.body.style.overflow = "hidden";
        nextTick(() => {
            inputRef.value?.focus();
        });
    } else {
        document.body.style.overflow = "";
        searchQuery.value = "";
        searchResults.value = [];
    }
});

// Open / Close actions
const openDialog = () => {
    isOpen.value = true;
};

const closeDialog = () => {
    isOpen.value = false;
};

// Select action
const handleSelect = (item: any) => {
    isOpen.value = false;
    emit("select", item);
    
    if (item.url) {
        window.location.href = item.url;
    }
};

// Key controls (Esc, Arrows, Enter)
const handleKeyDown = (e: KeyboardEvent) => {
    if (!isOpen.value) return;

    if (e.key === "Escape") {
        closeDialog();
        e.preventDefault();
    } else if (e.key === "ArrowDown") {
        if (searchResults.value.length > 0) {
            activeIndex.value = (activeIndex.value + 1) % searchResults.value.length;
            e.preventDefault();
        }
    } else if (e.key === "ArrowUp") {
        if (searchResults.value.length > 0) {
            activeIndex.value = (activeIndex.value - 1 + searchResults.value.length) % searchResults.value.length;
            e.preventDefault();
        }
    } else if (e.key === "Enter") {
        if (activeIndex.value >= 0 && activeIndex.value < searchResults.value.length) {
            handleSelect(searchResults.value[activeIndex.value]);
            e.preventDefault();
        }
    }
};

// Global shortcut listener (⌘K / Ctrl+K)
const handleGlobalShortcut = (e: KeyboardEvent) => {
    if ((e.metaKey || e.ctrlKey) && e.key === "k") {
        e.preventDefault();
        isOpen.value = !isOpen.value;
    }
};

onMounted(() => {
    window.addEventListener("keydown", handleGlobalShortcut);
    window.addEventListener("keydown", handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener("keydown", handleGlobalShortcut);
    window.removeEventListener("keydown", handleKeyDown);
    document.body.style.overflow = "";
    clearTimeout(debounceTimeout);
});
</script>

<template>
    <div :class="widthClass">
        <!-- Reusable Search Trigger Button (Styled like a modern search input bar) -->
        <button 
            @click="openDialog" 
            type="button"
            class="flex items-center justify-between bg-slate-50 hover:bg-slate-100/70 dark:bg-slate-900/50 dark:hover:bg-slate-800/80 border border-slate-200 dark:border-slate-800 rounded-xl h-[38px] px-3 text-slate-400 dark:text-zinc-550 hover:text-slate-500 hover:border-slate-300 dark:hover:border-zinc-700 transition-all text-left text-xs font-medium cursor-pointer w-full select-none"
        >
            <div class="flex items-center gap-2">
                <Search class="w-4 h-4 text-slate-400 dark:text-zinc-500 shrink-0" />
                <span>{{ placeholder }}</span>
            </div>
            <!-- Shortcut Hint Badge (Hidden on mobile) -->
            <kbd class="hidden sm:inline-flex h-5 select-none items-center gap-1 rounded border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-1.5 font-mono text-[9px] font-bold text-slate-400 dark:text-zinc-500 shadow-2xs">
                <span class="text-xs">⌘</span>K
            </kbd>
        </button>

        <!-- Command Dialog Popup Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="isOpen" class="fixed inset-0 z-50 flex items-start justify-center pt-[12vh] p-4 bg-black/40 dark:bg-black/75">
                    <!-- Backdrop overlay (no blur) -->
                    <div class="absolute inset-0 cursor-default" @click="closeDialog"></div>

                    <!-- Command Dialog Box -->
                    <Transition
                        enter-active-class="transition duration-300 ease-out"
                        enter-from-class="transform scale-95 -translate-y-2"
                        enter-to-class="transform scale-100 translate-y-0"
                        leave-active-class="transition duration-200 ease-in"
                        leave-from-class="transform scale-100 translate-y-0"
                        leave-to-class="transform scale-95 -translate-y-2"
                    >
                        <div class="relative bg-white dark:bg-zinc-950 border border-slate-200/90 dark:border-zinc-800 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden flex flex-col max-h-[70vh]">
                            <!-- Header Search Input wrapper -->
                            <div class="p-4 pb-3 border-b border-slate-100 dark:border-zinc-900 shrink-0">
                                <div class="flex items-center bg-zinc-100 dark:bg-zinc-900 border border-transparent rounded-xl px-3 py-2 transition-all">
                                    <Search class="w-4 h-4 text-zinc-400 dark:text-zinc-550 shrink-0" />
                                    <input
                                        ref="inputRef"
                                        v-model="searchQuery"
                                        :placeholder="placeholder"
                                        class="flex-1 bg-transparent border-none focus:outline-none focus:ring-0 text-[13px] font-semibold text-zinc-800 dark:text-zinc-200 placeholder-zinc-450 pl-2.5 outline-none"
                                    />
                                    <button 
                                        @click="closeDialog" 
                                        type="button"
                                        class="text-[9px] font-bold text-zinc-450 dark:text-zinc-500 bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded px-1.5 py-0.5 hover:bg-slate-50 dark:hover:bg-zinc-900 transition-colors cursor-pointer"
                                    >
                                        ESC
                                    </button>
                                </div>
                            </div>

                            <!-- List Results Panel -->
                            <div class="overflow-y-auto flex-1 py-2">
                                <!-- Loading State -->
                                <div v-if="isSearching" class="px-4 py-8 text-xs text-slate-400 dark:text-zinc-500 flex flex-col items-center justify-center gap-2">
                                    <Loader2 class="w-5 h-5 animate-spin text-blue-500" />
                                    <span>Mencari...</span>
                                </div>

                                <!-- Prompt search -->
                                <div v-else-if="searchQuery.length < 2" class="px-4 py-10 text-xs text-slate-450 dark:text-zinc-500 text-center flex flex-col items-center gap-1.5 select-none">
                                    <Search class="w-6 h-6 text-slate-300 dark:text-zinc-700" />
                                    <span>Ketik kata pencarian Anda (minimal 2 karakter)</span>
                                </div>

                                <!-- Empty State -->
                                <div v-else-if="searchResults.length === 0" class="px-4 py-10 text-xs text-slate-450 dark:text-zinc-500 italic text-center select-none">
                                    Tidak ada hasil ditemukan untuk "{{ searchQuery }}"
                                </div>

                                <!-- Suggestions List -->
                                <div v-else class="space-y-1">
                                    <div class="px-4 py-1 text-[9px] font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-widest select-none">
                                        Hasil Pencarian
                                    </div>
                                    <div class="px-2">
                                        <button
                                            v-for="(item, index) in searchResults"
                                            :key="item.url + item.id"
                                            @click="handleSelect(item)"
                                            class="w-full flex items-center justify-between px-3 py-2 rounded-lg transition-all text-left text-xs"
                                            :class="[
                                                activeIndex === index 
                                                    ? 'bg-zinc-100 dark:bg-zinc-900 text-zinc-900 dark:text-white font-bold' 
                                                    : 'hover:bg-zinc-50/50 dark:hover:bg-zinc-900/30 text-zinc-750 dark:text-zinc-300 font-medium'
                                            ]"
                                        >
                                            <div class="flex-1 min-w-0 pr-3">
                                                <p class="truncate text-[13px]">{{ item.title }}</p>
                                                <p class="text-[10px] text-zinc-400 dark:text-zinc-500 mt-0.5 truncate font-normal" v-if="item.description">
                                                    {{ item.description }}
                                                </p>
                                            </div>
                                            <div class="flex items-center gap-2 shrink-0 select-none">
                                                <!-- Custom category labels -->
                                                <span 
                                                    v-if="item.type"
                                                    class="text-[9px] font-black px-2 py-0.5 rounded-md uppercase tracking-wider bg-white dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 border border-slate-200 dark:border-zinc-700 shadow-2xs"
                                                >
                                                    {{ item.type }}
                                                </span>
                                                <CornerDownLeft class="w-3.5 h-3.5 text-blue-500 opacity-0 transition-opacity shrink-0" :class="{ 'opacity-100': activeIndex === index }" />
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Keyboard Hint -->
                            <div class="hidden sm:flex items-center justify-between px-4 py-2.5 bg-zinc-50 dark:bg-zinc-900/40 border-t border-slate-100 dark:border-zinc-850 shrink-0 text-[10px] text-zinc-400 dark:text-zinc-550 select-none">
                                <div class="flex items-center gap-3">
                                    <span class="flex items-center gap-1">
                                        <kbd class="border border-slate-200 dark:border-zinc-750 bg-white dark:bg-zinc-950 px-1 rounded">↑↓</kbd> navigasi
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <kbd class="border border-slate-200 dark:border-zinc-750 bg-white dark:bg-zinc-950 px-1.5 rounded">↵</kbd> buka
                                    </span>
                                </div>
                                <div>
                                    <span>Tekan <kbd class="border border-slate-200 dark:border-zinc-750 bg-white dark:bg-zinc-950 px-1 rounded">ESC</kbd> untuk keluar</span>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

    </div>
</template>
