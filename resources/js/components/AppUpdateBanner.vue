<script setup lang="ts">
import { useServiceWorker } from "@/composables/useServiceWorker";
import {
	ChevronDown,
	ChevronUp,
	Cpu,
	Layers,
	RefreshCw,
	ShieldCheck,
	X,
	Zap,
} from "lucide-vue-next";
import { computed, onMounted, onUnmounted, ref } from "vue";

// ── Baca metadata dari data attributes yang diinjeksi oleh app.ts ─────────────
const isAdmin = ref(false);
const displayVersion = ref("...");
const brandName = ref("Portal FMIKOM");
const brandLogo = ref("/asset/brand-logo.webp");
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

const getFormattedBuildDate = (ver: string) => {
	let dateObj = new Date();
	if (ver && /^\d{10,13}$/.test(ver)) {
		dateObj = new Date(Number.parseInt(ver, 10));
	}
	const months = [
		"Jan",
		"Feb",
		"Mar",
		"Apr",
		"Mei",
		"Jun",
		"Jul",
		"Agu",
		"Sep",
		"Okt",
		"Nov",
		"Des",
	];
	const day = String(dateObj.getDate()).padStart(2, "0");
	const month = months[dateObj.getMonth()];
	const year = dateObj.getFullYear();
	return `${day} ${month} ${year}`;
};

const formattedBuildDate = computed(() =>
	getFormattedBuildDate(displayVersion.value),
);

const dynamicFallbackItems = computed(() => [
	{ text: `🚀 Pembaruan Sistem Versi ${formattedBuildDate.value}` },
	{ text: "⚡ Peningkatan kestabilan performa & perbaikan antarmuka seluler" },
	{ text: "🔄 Sinkronisasi keamanan & penyesuaian aset Service Worker PWA" },
]);

function parseUpdateItems(raw?: string): Array<{ text: string }> {
	if (!raw) return [];
	try {
		const parsed = JSON.parse(raw);
		if (Array.isArray(parsed)) {
			return parsed
				.map((item: any) => {
					if (typeof item === "string") return { text: item };
					return { text: item.text || item.title || "" };
				})
				.filter((i) => Boolean(i.text));
		}
	} catch (_) {
		return raw
			.split("\n")
			.map((t) => t.trim())
			.filter(Boolean)
			.map((text) => ({ text }));
	}
	return [];
}

function checkVisibility() {
	const bannerRoot = document.getElementById("app-update-banner-root");
	if (bannerRoot?.dataset) {
		if (bannerRoot.dataset.isAdmin !== undefined) {
			isAdmin.value = bannerRoot.dataset.isAdmin === "1";
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
		updateItems.value = dynamicFallbackItems.value;
	}

	// Test mode HANYA untuk admin — publik tidak boleh tahu cara ini
	const testMode =
		isAdmin.value &&
		typeof window !== "undefined" &&
		new URLSearchParams(window.location.search).has("test_update");

	if (testMode || (updateAvailable.value && !isSnoozed())) {
		visible.value = true;
	} else {
		visible.value = false;
	}
}

onMounted(() => {
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
	<!-- Banner wrapper: fixed ke viewport dengan layout responsif -->
	<div class="fixed top-4 sm:top-16 left-3 right-3 sm:left-auto sm:right-6 z-[999999] pointer-events-none flex justify-center sm:block">

		<!-- Main Floating Card -->
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
				class="pointer-events-auto w-full sm:w-[320px] max-w-[calc(100vw-24px)] rounded-2xl border border-slate-200/90 dark:border-zinc-800 p-3.5 shadow-2xl bg-white/95 dark:bg-zinc-900/95 backdrop-blur-xl text-slate-900 dark:text-white font-sans space-y-3 ring-1 ring-black/5"
			>
				<!-- Header Row: Logo + App Title + Date Badge + Close -->
				<div class="flex items-center justify-between gap-2">
					<div class="flex items-center gap-2.5 min-w-0 flex-1">
						<!-- System Logo Box -->
						<div class="relative shrink-0">
							<div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-zinc-800 border border-slate-200/80 dark:border-zinc-700 p-1 flex items-center justify-center overflow-hidden">
								<img
									:src="brandLogo"
									class="w-full h-full object-contain"
									alt="Logo"
									@error="($event.target as HTMLImageElement).style.display = 'none'"
								/>
							</div>
							<!-- Live Pulse Dot -->
							<span class="absolute -top-0.5 -right-0.5 flex h-2.5 w-2.5">
								<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
								<span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500 border-2 border-white dark:border-zinc-900"></span>
							</span>
						</div>

						<div class="min-w-0 flex-1">
							<div class="flex items-center gap-1.5 flex-wrap">
								<span class="text-[11.5px] font-extrabold text-slate-900 dark:text-white tracking-tight truncate">
									{{ brandName }}
								</span>
								<!-- Versi Tanggal Realtime -->
								<span class="shrink-0 px-2 py-0.5 rounded-full text-[9px] font-black bg-indigo-50 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60">
									{{ formattedBuildDate }}
								</span>
							</div>
							<p class="text-[9.5px] font-semibold text-slate-400 dark:text-zinc-500 truncate mt-0.5">
								Pembaruan Sistem Siap
							</p>
						</div>
					</div>

					<!-- Close Button -->
					<button
						@click="handleLater"
						class="shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors border-none bg-transparent cursor-pointer"
					>
						<X class="w-3.5 h-3.5" />
					</button>
				</div>

				<!-- Description -->
				<p class="text-[11px] text-slate-500 dark:text-zinc-400 leading-relaxed font-medium">
					Versi terbaru telah siap dipasang dengan peningkatan performa &amp; responsivitas.
				</p>

				<!-- Expandable Detail Toggle -->
				<button
					@click="showDetails = !showDetails"
					class="inline-flex items-center gap-1 text-[10.5px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline border-none bg-transparent cursor-pointer p-0"
				>
					<span>{{ showDetails ? 'Sembunyikan Detail' : 'Lihat Detail Pembaruan' }}</span>
					<ChevronUp v-if="showDetails" class="w-3 h-3" />
					<ChevronDown v-else class="w-3 h-3" />
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
					<div v-if="showDetails" class="p-2.5 rounded-xl bg-slate-50 dark:bg-zinc-800/60 border border-slate-200/60 dark:border-zinc-700/60 space-y-1.5">
						<p class="text-[9px] font-extrabold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">Rincian Pembaruan:</p>
						<ul class="space-y-1.5">
							<li v-for="(item, idx) in updateItems" :key="idx" class="flex items-start gap-1.5 text-[10.5px] font-medium text-slate-700 dark:text-zinc-300 leading-snug break-words">
								<ShieldCheck v-if="idx === 0" class="w-3.5 h-3.5 text-indigo-500 shrink-0 mt-0.5" />
								<Layers v-else-if="idx === 1" class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" />
								<Cpu v-else class="w-3.5 h-3.5 text-amber-500 shrink-0 mt-0.5" />
								<span>{{ item.text }}</span>
							</li>
						</ul>
					</div>
				</Transition>

				<!-- Action Buttons -->
				<div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-100 dark:border-zinc-800/80">
					<button
						@click="handleLater"
						class="px-3 py-1.5 rounded-xl text-[11px] font-bold text-slate-500 hover:text-slate-700 dark:hover:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors border-none bg-transparent cursor-pointer"
					>
						Nanti
					</button>
					<button
						@click="startInteractiveUpdate"
						class="px-3.5 py-1.5 rounded-xl text-[11px] font-extrabold text-white bg-gradient-to-r from-indigo-600 to-emerald-500 shadow-md shadow-indigo-500/20 hover:opacity-90 flex items-center gap-1.5 active:scale-95 transition-all border-none cursor-pointer"
					>
						<Zap class="w-3 h-3 fill-white" />
						<span>Perbarui</span>
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
