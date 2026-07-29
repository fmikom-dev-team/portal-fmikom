<script setup lang="ts">
import { onMounted, onUnmounted, ref } from "vue";

interface ModerationItem {
	id: number;
	title: string;
	author: string;
	authorHandle: string;
	type: "Laporan" | "Karya Baru" | "Komentar";
	reportedBy?: string;
	time: string;
	status: "active" | "warning" | "hidden" | "removed" | "pending";
	thumbnail?: string;
}

defineProps<{
	items: ModerationItem[];
	loading?: boolean;
}>();

const brokenImages = ref<Record<number | string, boolean>>({});
const activeDropdownId = ref<number | null>(null);
const dropdownStyle = ref<{ top?: string; right?: string }>({});

// Lightbox state
const showLightbox = ref(false);
const lightboxUrl = ref("");
const lightboxTitle = ref("");

const emit = defineEmits<{
	review: [id: number];
	warn: [id: number];
	hide: [id: number];
	remove: [id: number];
}>();

const typeConfig = {
	Laporan: "bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400",
	"Karya Baru":
		"bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400",
	Komentar: "bg-slate-100 text-slate-600 dark:bg-zinc-700 dark:text-zinc-300",
};

const statusConfig = {
	active:
		"bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400",
	warning:
		"bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400",
	hidden: "bg-slate-100 text-slate-500 dark:bg-zinc-800 dark:text-zinc-400",
	removed: "bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400",
	pending: "bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400",
};

const statusLabel = {
	active: "Aktif",
	warning: "Peringatan",
	hidden: "Tersembunyi",
	removed: "Dihapus",
	pending: "Menunggu",
};

const toggleDropdown = (id: number, e: MouseEvent) => {
	e.stopPropagation();
	if (activeDropdownId.value === id) {
		activeDropdownId.value = null;
		return;
	}

	const target = e.currentTarget as HTMLElement;
	if (target) {
		const rect = target.getBoundingClientRect();
		dropdownStyle.value = {
			top: `${rect.bottom + 6}px`,
			right: `${window.innerWidth - rect.right}px`,
		};
	}

	activeDropdownId.value = id;
};

const openLightbox = (url: string, title: string) => {
	if (!url) return;
	lightboxUrl.value = url;
	lightboxTitle.value = title;
	showLightbox.value = true;
};

const closeLightbox = () => {
	showLightbox.value = false;
	lightboxUrl.value = "";
	lightboxTitle.value = "";
};

const handleDocumentClick = () => {
	activeDropdownId.value = null;
};

const handleKeyDown = (e: KeyboardEvent) => {
	if (e.key === "Escape") {
		closeLightbox();
		activeDropdownId.value = null;
	}
};

const handleScroll = () => {
	activeDropdownId.value = null;
};

onMounted(() => {
	document.addEventListener("click", handleDocumentClick);
	document.addEventListener("keydown", handleKeyDown);
	window.addEventListener("scroll", handleScroll, { passive: true });
});

onUnmounted(() => {
	document.removeEventListener("click", handleDocumentClick);
	document.removeEventListener("keydown", handleKeyDown);
	window.removeEventListener("scroll", handleScroll);
});
</script>

<template>
    <!-- ── Skeleton ─────────────────────────────────────────────────────── -->
    <template v-if="loading">
        <div class="divide-y divide-slate-100 dark:divide-zinc-800">
            <div v-for="i in 5" :key="i" class="flex items-center gap-4 px-5 py-4">
                <!-- Thumbnail skeleton -->
                <div class="h-10 w-10 rounded-xl bg-slate-100 dark:bg-zinc-800 animate-shimmer shrink-0" />
                <!-- Text skeleton -->
                <div class="flex-1 space-y-2 min-w-0">
                    <div class="h-3 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer"
                         :style="{ width: (50 + i * 8) + '%' }" />
                    <div class="h-2.5 w-1/3 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer" />
                </div>
                <!-- Badge skeleton -->
                <div class="h-5 w-16 rounded-full bg-slate-100 dark:bg-zinc-800 animate-shimmer shrink-0 hidden sm:block" />
                <!-- Button skeleton -->
                <div class="h-7 w-14 rounded-lg bg-slate-100 dark:bg-zinc-800 animate-shimmer shrink-0" />
            </div>
        </div>
    </template>

    <template v-else>
        <!-- Table with horizontal scroll on mobile -->
        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px]">
                <caption class="sr-only">Tabel Moderasi Konten</caption>
                <thead>
                    <tr class="border-b border-slate-100 dark:border-zinc-800">
                        <th class="px-5 py-3.5 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Konten</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Jenis</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500 hidden md:table-cell">Pelapor</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500 hidden lg:table-cell">Waktu</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500 hidden lg:table-cell">Status</th>
                        <th class="px-5 py-3.5 text-right text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-zinc-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-zinc-800/50">
                    <tr
                        v-for="item in items"
                        :key="item.id"
                        class="group hover:bg-slate-50 dark:hover:bg-zinc-800/30 transition-colors"
                    >
                        <!-- Content (thumbnail + title) -->
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <!-- Thumbnail (clickable for Lightbox) -->
                                <button
                                    type="button"
                                    @click="item.thumbnail && openLightbox(item.thumbnail, item.title)"
                                    :class="[
                                        'h-10 w-10 shrink-0 rounded-xl overflow-hidden bg-slate-100 dark:bg-zinc-800 border border-slate-100 dark:border-zinc-700 relative group/thumb focus:outline-none focus:ring-2 focus:ring-indigo-500 text-left',
                                        item.thumbnail && !brokenImages[item.id] ? 'cursor-pointer hover:border-indigo-500 transition-colors' : 'cursor-default'
                                    ]"
                                    :title="item.thumbnail ? 'Klik untuk memperbesar gambar' : item.title"
                                >
                                    <img
                                        v-if="item.thumbnail && !brokenImages[item.id]"
                                        :src="item.thumbnail"
                                        :alt="item.title"
                                        @error="brokenImages[item.id] = true"
                                        class="h-full w-full object-cover transition-transform group-hover/thumb:scale-110"
                                    />
                                    <div v-else class="h-full w-full flex items-center justify-center">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159" />
                                        </svg>
                                    </div>
                                    <div v-if="item.thumbnail && !brokenImages[item.id]" class="absolute inset-0 bg-black/20 opacity-0 group-hover/thumb:opacity-100 flex items-center justify-center transition-opacity">
                                        <svg class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6" />
                                        </svg>
                                    </div>
                                </button>
                                <!-- Title + handle -->
                                <div class="min-w-0">
                                    <p class="text-[12.5px] font-semibold text-slate-800 dark:text-zinc-100 truncate max-w-[160px] sm:max-w-[220px]">{{ item.title }}</p>
                                    <p class="text-[11px] text-slate-400 dark:text-zinc-500 truncate">oleh {{ item.authorHandle }}</p>
                                </div>
                            </div>
                        </td>

                        <!-- Type Badge -->
                        <td class="px-4 py-3.5">
                            <span :class="['inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-black whitespace-nowrap', typeConfig[item.type]]">
                                {{ item.type }}
                            </span>
                        </td>

                        <!-- Reporter -->
                        <td class="px-4 py-3.5 hidden md:table-cell">
                            <p class="text-[12px] font-medium text-slate-600 dark:text-zinc-400 truncate max-w-[120px]">{{ item.reportedBy || item.author }}</p>
                        </td>

                        <!-- Time -->
                        <td class="px-4 py-3.5 hidden lg:table-cell">
                            <p class="text-[11px] text-slate-400 dark:text-zinc-500 whitespace-nowrap">{{ item.time }}</p>
                        </td>

                        <!-- Status -->
                        <td class="px-4 py-3.5 hidden lg:table-cell">
                            <span :class="['inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold whitespace-nowrap', statusConfig[item.status]]">
                                {{ statusLabel[item.status] }}
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-1.5 relative">
                                <button
                                    @click="emit('review', item as any)"
                                    class="rounded-lg border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-900/20 px-2.5 py-1.5 text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-colors whitespace-nowrap"
                                >
                                    Tinjau
                                </button>
                                
                                <!-- Three-dots dropdown trigger -->
                                <button
                                    @click="toggleDropdown(item.id, $event)"
                                    class="h-7 w-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-zinc-800 hover:text-slate-600 dark:hover:text-zinc-200 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    title="Opsi lainnya"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm6.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm6.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                    </svg>
                                </button>

                                <!-- Teleported Popover Dropdown Menu (Escapes table overflow) -->
                                <Teleport to="body">
                                    <div
                                        v-if="activeDropdownId === item.id"
                                        style="position: fixed; z-index: 99999;"
                                        :style="dropdownStyle"
                                        class="w-44 rounded-xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-2xl py-1.5 animate-fade-in font-sans"
                                        @click.stop
                                    >
                                        <button
                                            @click="emit('review', item as any); activeDropdownId = null;"
                                            class="w-full px-3.5 py-1.5 text-left text-[11.5px] font-semibold text-slate-700 dark:text-zinc-200 hover:bg-indigo-50 dark:hover:bg-zinc-800 hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center gap-2 transition-colors"
                                        >
                                            <svg class="h-3.5 w-3.5 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            Tinjau Detail
                                        </button>
                                        <button
                                            v-if="item.thumbnail"
                                            @click="openLightbox(item.thumbnail, item.title); activeDropdownId = null;"
                                            class="w-full px-3.5 py-1.5 text-left text-[11.5px] font-semibold text-slate-700 dark:text-zinc-200 hover:bg-slate-50 dark:hover:bg-zinc-800 flex items-center gap-2 transition-colors"
                                        >
                                            <svg class="h-3.5 w-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159" />
                                            </svg>
                                            Lihat Gambar
                                        </button>
                                        <div class="my-1 border-t border-slate-100 dark:border-zinc-800" />
                                        <button
                                            @click="navigator.clipboard?.writeText(String(item.id)); activeDropdownId = null;"
                                            class="w-full px-3.5 py-1.5 text-left text-[11.5px] font-medium text-slate-500 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800 flex items-center gap-2 transition-colors"
                                        >
                                            <svg class="h-3.5 w-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 011.927-.184" />
                                            </svg>
                                            Salin ID Karya
                                        </button>
                                    </div>
                                </Teleport>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Empty state -->
        <div v-if="items.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
            <div class="h-14 w-14 rounded-2xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center mb-4">
                <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
            </div>
            <p class="text-[14px] font-bold text-slate-600 dark:text-zinc-300">Tidak ada konten menunggu tinjauan</p>
            <p class="text-[12px] text-slate-400 dark:text-zinc-500 mt-1">Semua konten telah ditinjau ✓</p>
        </div>

        <!-- Lightbox Image Modal -->
        <Teleport to="body">
            <div
                v-if="showLightbox"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md p-4 animate-fade-in"
                @click="closeLightbox"
            >
                <div class="relative max-w-4xl max-h-[90vh] flex flex-col items-center" @click.stop>
                    <!-- Close button -->
                    <button
                        @click="closeLightbox"
                        class="absolute -top-10 right-0 text-white/70 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-full transition-all"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <!-- Image -->
                    <img
                        :src="lightboxUrl"
                        :alt="lightboxTitle"
                        class="max-h-[80vh] max-w-full rounded-2xl shadow-2xl object-contain border border-white/10"
                    />
                    <p v-if="lightboxTitle" class="mt-3 text-center text-[13px] font-semibold text-white/90">
                        {{ lightboxTitle }}
                    </p>
                </div>
            </div>
        </Teleport>
    </template>
</template>
