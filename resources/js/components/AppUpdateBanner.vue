<script setup lang="ts">
import { useServiceWorker } from "@/composables/useServiceWorker";
import { X, RefreshCw, Zap, ChevronDown, ChevronUp, ShieldCheck, Cpu, Layers } from "lucide-vue-next";
import { onMounted, onUnmounted, ref } from "vue";

// ── Baca metadata dari data attributes yang diinjeksi oleh app.ts ─────────────
// Gunakan getElementById langsung — getCurrentInstance().$el belum ada saat setup
// karena DOM belum dirender (proxy.$el = null di fase setup).
const isAdmin = ref(false);
const displayVersion = ref('...');
const brandName = ref('Portal FMIKOM');
const brandLogo = ref('/asset/brand-logo.webp');
const updateItems = ref<Array<{ text: string; icon?: string }>>([]);

const {
	updateAvailable,
	isInstalling,
	installProgress,
	installStepText,
	startInteractiveUpdate,
	snoozeUpdate,
	isSnoozed,
} = useServiceWorker();

const visible = ref(false);
const showDetails = ref(false);
let intervalId: any = null;

function parseUpdateItems(raw?: string): Array<{ text: string }> {
	if (!raw) return [];
	try {
		const parsed = JSON.parse(raw);
		if (Array.isArray(parsed)) {
			return parsed.map((item: any) => {
				if (typeof item === 'string') return { text: item };
				return { text: item.text || item.title || '' };
			}).filter(i => Boolean(i.text));
		}
	} catch (_) {
		return raw.split('\n').map(t => t.trim()).filter(Boolean).map(text => ({ text }));
	}
	return [];
}

function checkVisibility() {
	const bannerRoot = document.getElementById('app-update-banner-root');
	if (bannerRoot?.dataset) {
		if (bannerRoot.dataset.isAdmin !== undefined) {
			isAdmin.value = bannerRoot.dataset.isAdmin === '1';
		}
		if (bannerRoot.dataset.appVersion) {
			displayVersion.value = bannerRoot.dataset.appVersion;
		}
		if (bannerRoot.dataset.brandName) {
			brandName.value = bannerRoot.dataset.brandName;
		}
		if (bannerRoot.dataset.brandLogo) {
			brandLogo.value = bannerRoot.dataset.brandLogo;
		}
		if (bannerRoot.dataset.updateItems) {
			const parsed = parseUpdateItems(bannerRoot.dataset.updateItems);
			if (parsed.length > 0) {
				updateItems.value = parsed;
			}
		}
	}

	if (updateItems.value.length === 0) {
		updateItems.value = [
			{ text: "Fitur Panduan & Onboarding Spotlight PAGI" },
			{ text: "Optimalisasi Kecepatan & Notifikasi System" },
			{ text: "Sinkronisasi Cache Service Worker PWA" }
		];
	}

	// Test mode HANYA untuk admin — publik tidak boleh tahu cara ini
	const testMode = isAdmin.value &&
		typeof window !== 'undefined' &&
		new URLSearchParams(window.location.search).has('test_update');

	if (testMode || (updateAvailable.value && !isSnoozed())) {
		visible.value = true;
	} else {
		visible.value = false;
	}
}

onMounted(() => {
	// Baca data attributes SETELAH mount — DOM sudah siap
	checkVisibility();
	intervalId = setInterval(checkVisibility, 3000);
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
    <!-- Banner wrapper: fixed ke viewport top-right menggunakan inline style
         (tidak bisa pakai Tailwind fixed karena component ada di standalone Vue app) -->
    <div style="position: fixed; top: 72px; right: 24px; z-index: 999999; pointer-events: none;">

        <!-- Main Floating Card (Desktop) -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 scale-90 -translate-y-3"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-90 -translate-y-3"
        >
            <div
                v-if="visible && !isInstalling"
                style="pointer-events: all; width: 300px;"
                class="rounded-2xl border border-slate-200/90 dark:border-zinc-800 p-3 shadow-xl bg-white/95 dark:bg-zinc-900/95 backdrop-blur-xl text-slate-900 dark:text-white font-sans space-y-2.5"
            >
                <!-- Header Row: Logo + App Title + Badge + Close -->
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0 flex-1">
                        <!-- System Logo Box -->
                        <div class="relative shrink-0">
                            <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-zinc-800 border border-slate-200/80 dark:border-zinc-700 p-1 flex items-center justify-center overflow-hidden">
                                <!-- Logo diambil dari DOM img#app-logo jika ada, atau fallback SVG -->
                                <img
                                    :src="brandLogo"
                                    class="w-full h-full object-contain"
                                    alt="Logo"
                                    @error="($event.target as HTMLImageElement).style.display = 'none'"
                                />
                            </div>
                            <!-- Live Pulse Dot -->
                            <span class="absolute -top-0.5 -right-0.5 flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-indigo-600 border-2 border-white dark:border-zinc-900"></span>
                            </span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5">
                                <span class="text-[11px] font-black text-slate-900 dark:text-white tracking-tight truncate">
                                    {{ brandName }}
                                </span>
                                <!-- Versi realtime dari build timestamp (inject via vite define) -->
                                <span class="shrink-0 px-1.5 py-px rounded-md text-[9px] font-extrabold bg-indigo-100 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300">
                                    {{ displayVersion }}
                                </span>
                            </div>
                            <p class="text-[9.5px] font-semibold text-slate-400 dark:text-zinc-500 truncate">
                                Pembaruan Sistem Siap
                            </p>
                        </div>
                    </div>

                    <!-- Close Button -->
                    <button
                        @click="handleLater"
                        class="shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors"
                    >
                        <X class="w-3 h-3" />
                    </button>
                </div>

                <!-- Description -->
                <p class="text-[10.5px] text-slate-500 dark:text-zinc-400 leading-relaxed">
                    Versi terbaru tersedia dengan fitur &amp; perbaikan performa.
                </p>

                <!-- Expandable Detail Toggle -->
                <button
                    @click="showDetails = !showDetails"
                    class="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline"
                >
                    <span>{{ showDetails ? 'Sembunyikan Detail' : 'Lihat Detail Pembaruan' }}</span>
                    <ChevronUp v-if="showDetails" class="w-2.5 h-2.5" />
                    <ChevronDown v-else class="w-2.5 h-2.5" />
                </button>

                <!-- Release Notes Drawer -->
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 -translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-1"
                >
                    <div v-if="showDetails" class="p-2 rounded-xl bg-slate-50 dark:bg-zinc-800/60 border border-slate-200/60 dark:border-zinc-700/60 space-y-1.5">
                        <p class="text-[9px] font-extrabold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Rincian Pembaruan:</p>
                        <ul class="space-y-1">
                            <li v-for="(item, idx) in updateItems" :key="idx" class="flex items-center gap-1.5 text-[10px] text-slate-600 dark:text-zinc-300">
                                <ShieldCheck v-if="idx === 0" class="w-3 h-3 text-indigo-500 shrink-0" />
                                <Layers v-else-if="idx === 1" class="w-3 h-3 text-emerald-500 shrink-0" />
                                <Cpu v-else class="w-3 h-3 text-amber-500 shrink-0" />
                                <span>{{ item.text }}</span>
                            </li>
                        </ul>
                    </div>
                </Transition>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-1.5">
                    <button
                        @click="handleLater"
                        class="px-2.5 py-1 rounded-lg text-[10.5px] font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors"
                    >
                        Nanti
                    </button>
                    <button
                        @click="startInteractiveUpdate"
                        class="px-3 py-1 rounded-lg text-[10.5px] font-bold text-white bg-gradient-to-r from-indigo-600 to-emerald-500 shadow-sm hover:opacity-90 flex items-center gap-1 active:scale-95 transition-all"
                    >
                        <Zap class="w-2.5 h-2.5 fill-white" />
                        Perbarui
                    </button>
                </div>
            </div>
        </Transition>
    </div>

    <!-- Installing Progress Modal -->
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="isInstalling"
            style="position: fixed; inset: 0; z-index: 9999999; display: flex; align-items: center; justify-content: center; padding: 1rem;"
            class="bg-slate-900/70 dark:bg-black/85 backdrop-blur-md"
        >
            <div class="w-full max-w-xs bg-white dark:bg-zinc-900 rounded-3xl p-5 border border-slate-200/80 dark:border-zinc-800 shadow-2xl space-y-5 text-center relative overflow-hidden">
                <div class="absolute -top-16 -left-16 w-36 h-36 bg-indigo-500/20 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-16 -right-16 w-36 h-36 bg-emerald-500/20 rounded-full blur-3xl"></div>

                <div class="relative w-14 h-14 mx-auto">
                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-tr from-indigo-500 to-emerald-400 opacity-25 blur-md animate-pulse"></div>
                    <div class="w-full h-full rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800/80 flex items-center justify-center p-2 relative z-10">
                        <img
                            src="/asset/brand-logo.webp"
                            class="w-full h-full object-contain"
                            alt="Logo"
                            @error="($event.target as HTMLImageElement).style.display = 'none'; ($event.target as HTMLImageElement).nextElementSibling?.removeAttribute('hidden')"
                        />
                        <RefreshCw class="w-7 h-7 text-indigo-600 animate-spin hidden" />
                    </div>
                </div>

                <div class="space-y-1">
                    <h3 class="text-sm font-black text-slate-900 dark:text-white">Menerapkan Pembaruan</h3>
                    <p class="text-[11px] text-slate-500 dark:text-zinc-400 font-medium">{{ installStepText || "Mohon tunggu..." }}</p>
                </div>

                <div class="space-y-1.5">
                    <div class="w-full h-2 bg-slate-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                        <div
                            class="h-full bg-gradient-to-r from-indigo-600 to-emerald-400 rounded-full transition-all duration-300 ease-out"
                            :style="{ width: installProgress + '%' }"
                        ></div>
                    </div>
                    <div class="flex justify-between text-[9.5px] font-bold text-slate-400 dark:text-zinc-500">
                        <span>Portal FMIKOM</span>
                        <span>{{ installProgress }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>
