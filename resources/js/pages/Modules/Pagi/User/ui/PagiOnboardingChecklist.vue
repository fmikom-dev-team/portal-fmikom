<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
import {
	Check,
	CheckCircle2,
	ChevronLeft,
	ChevronRight,
	Circle,
	X,
} from "lucide-vue-next";
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";

export type Step = {
	id: string;
	title: string;
	description?: string;
	targetSelector: string;
};

const props = withDefaults(
	defineProps<{
		roleName?: string;
		userId?: string | number;
	}>(),
	{
		roleName: "mahasiswa",
		userId: "guest",
	},
);

const page = usePage();
const siteSettings = computed(() => (page.props as any).siteSettings || {});

// Route detection for Profile vs Main Pagi
const isProfilePage = computed(() => {
	const component = (page.component as string) || "";
	const url =
		page.url || (typeof window !== "undefined" ? window.location.pathname : "");
	if (component.includes("Profile") || url.startsWith("/pagi/profile"))
		return true;
	// Detect custom profile URLs like /pagi/suntree
	if (
		url.startsWith("/pagi/") &&
		![
			"/pagi",
			"/pagi/people",
			"/pagi/gallery",
			"/pagi/cv",
			"/pagi/messages",
			"/pagi/notifications",
			"/pagi/settings",
			"/pagi/editor",
		].includes(url)
	) {
		return true;
	}
	return false;
});

const STORAGE_KEY = computed(
	() =>
		`${isProfilePage.value ? "pagi_onboarding_profile_v1" : "pagi_onboarding_v1"}_${props.userId || "guest"}`,
);

const tourTitle = computed(() =>
	isProfilePage.value ? "Panduan Profil Mahasiswa" : "Panduan Fitur PAGI",
);

// Steps based on role and route
const steps = computed<Step[]>(() => {
	const role = (props.roleName || "mahasiswa").toLowerCase();

	// Dedicated Mahasiswa Profile Tour Steps
	if (isProfilePage.value && role === "mahasiswa") {
		return [
			{
				id: "profile-avatar",
				title: "Foto Profil & Avatar",
				description:
					"Klik foto profil kamu untuk mengganti foto diri atau avatar terbaru.",
				targetSelector: "[data-onboard='profile-avatar']",
			},
			{
				id: "profile-banner",
				title: "Banner & Featured Media",
				description:
					"Unggah banner latar profil untuk memajang karya atau desain unggulanmu.",
				targetSelector: "[data-onboard='profile-banner']",
			},
			{
				id: "profile-bio",
				title: "Bio & Data Diri",
				description:
					"Lengkapi nama, username, role title kreatif, dan bio singkat kamu.",
				targetSelector: "[data-onboard='profile-bio']",
			},
			{
				id: "profile-socials",
				title: "Tautan Sosial & Lokasi",
				description:
					"Hubungkan akun LinkedIn, GitHub, Website, dan lokasi keberadaanmu.",
				targetSelector: "[data-onboard='profile-socials']",
			},
			{
				id: "profile-tabs",
				title: "Tab Portofolio & Aktivitas",
				description:
					"Kelola Karya (Work), Riwayat Pendidikan, Galeri, dan Sertifikat.",
				targetSelector: "[data-onboard='profile-tabs']",
			},
		];
	}

	// Standard Pagi Module Tour Steps
	if (role === "mahasiswa") {
		return [
			{
				id: "feed",
				title: "Jelajahi Feed & Showcase",
				description:
					"Temukan karya kreatif terbaru dari mahasiswa & civitas di PAGI.",
				targetSelector: "[data-onboard='pagi-feed']",
			},
			{
				id: "gallery",
				title: "Galeri Inspirasi & Visual",
				description:
					"Telusuri galeri visual lengkap dari berbagai kategori karya.",
				targetSelector: "[data-onboard='pagi-gallery']",
			},
			{
				id: "create-work",
				title: "Publikasikan Karya Kamu",
				description:
					"Bagikan proyek, desain, atau karya terbarumu ke seluruh komunitas.",
				targetSelector: "[data-onboard='pagi-create-work']",
			},
			{
				id: "people",
				title: "Jejaring & Koneksi Kreator",
				description:
					"Temukan kreator lain, kolaborator, dan kembangkan jaringanmu.",
				targetSelector: "[data-onboard='pagi-people']",
			},
			{
				id: "cv",
				title: "Pembuat CV & Resume",
				description:
					"Buat dan ekspor CV profesional kamu secara langsung di PAGI.",
				targetSelector: "[data-onboard='pagi-cv']",
			},
			{
				id: "messages",
				title: "Pesan & Diskusi Direct",
				description:
					"Kirim pesan langsung ke sesama mahasiswa, alumni, atau dosen.",
				targetSelector: "[data-onboard='pagi-messages']",
			},
			{
				id: "notifications",
				title: "Panel Notifikasi Aktivitas",
				description:
					"Pantau apresiasi, komentar, dan pembaruan penting secara real-time.",
				targetSelector: "[data-onboard='pagi-notifications']",
			},
			{
				id: "profile",
				title: "Lengkapi Profil & Portfolio",
				description:
					"Atur avatar, bio, dan kembangkan personal branding kamu di PAGI.",
				targetSelector: "[data-onboard='pagi-profile']",
			},
		];
	}

	return [
		{
			id: "feed",
			title: "Jelajahi Feed Karya",
			description:
				"Lihat proyek-proyek kreatif dan karya inovatif dari civitas komputer.",
			targetSelector: "[data-onboard='pagi-feed']",
		},
		{
			id: "gallery",
			title: "Galeri Inspirasi Visual",
			description:
				"Jelajahi karya terbaik berdasarkan kategori dan tren terkini.",
			targetSelector: "[data-onboard='pagi-gallery']",
		},
		{
			id: "people",
			title: "Direktori Talenta",
			description: "Temukan talenta dan lulusan terbaik di platform PAGI.",
			targetSelector: "[data-onboard='pagi-people']",
		},
		{
			id: "messages",
			title: "Pesan & Diskusi",
			description: "Kirim pesan langsung ke talenta atau civitas akademik.",
			targetSelector: "[data-onboard='pagi-messages']",
		},
		{
			id: "notifications",
			title: "Notifikasi Sistem & Aktivitas",
			description: "Terima pemberitahuan langsung terkait pembaruan platform.",
			targetSelector: "[data-onboard='pagi-notifications']",
		},
		{
			id: "profile",
			title: "Pengaturan Profil",
			description: "Kelola profil dan preferensi akun Anda.",
			targetSelector: "[data-onboard='pagi-profile']",
		},
	];
});

const isOpen = ref(false);
const completedSteps = ref<Set<string>>(new Set());
const activeCoachmarkId = ref<string | null>(null);
const isDismissedForever = ref(false);

const checkStorageState = () => {
	if (typeof window === "undefined") return;
	try {
		const stored = localStorage.getItem(STORAGE_KEY.value);
		if (stored === "completed" || stored === "dismissed") {
			isDismissedForever.value = true;
			isOpen.value = false;
			activeCoachmarkId.value = null;
		} else {
			isDismissedForever.value = false;
			completedSteps.value = new Set();
			setTimeout(() => {
				if (!isDismissedForever.value) {
					isOpen.value = true;
				}
			}, 1200);
		}
	} catch (_) {}
};

watch(STORAGE_KEY, () => {
	checkStorageState();
});

const completedCount = computed(
	() => steps.value.filter((step) => completedSteps.value.has(step.id)).length,
);
const totalSteps = computed(() => steps.value.length);
const progressPercent = computed(() =>
	totalSteps.value > 0 ? (completedCount.value / totalSteps.value) * 100 : 0,
);
const allCompleted = computed(() => completedCount.value === totalSteps.value);

const activeStep = computed(() =>
	activeCoachmarkId.value
		? steps.value.find((s) => s.id === activeCoachmarkId.value) || null
		: null,
);
const activeStepIndex = computed(() =>
	activeStep.value ? steps.value.indexOf(activeStep.value) : -1,
);

// Target positioning state for spotlight overlay
const targetPos = ref<{
	top: number;
	left: number;
	width: number;
	height: number;
} | null>(null);

const getElementPosition = (selector: string) => {
	if (typeof window === "undefined") return null;
	const elements = Array.from(document.querySelectorAll(selector)) as HTMLElement[];
	if (elements.length === 0) return null;

	const el = elements.find((e) => {
		const rect = e.getBoundingClientRect();
		return rect.width > 0 && rect.height > 0 && getComputedStyle(e).display !== 'none' && getComputedStyle(e).visibility !== 'hidden';
	}) || elements[0];

	const rect = el.getBoundingClientRect();
	return {
		top: rect.top,
		left: rect.left,
		width: rect.width,
		height: rect.height,
	};
};

const updateTargetPosition = () => {
	if (!activeStep.value) {
		targetPos.value = null;
		return;
	}
	targetPos.value = getElementPosition(activeStep.value.targetSelector);
};

let resizeObserver: ResizeObserver | null = null;

const setupPositionListeners = () => {
	if (typeof window === "undefined") return;
	window.addEventListener("resize", updateTargetPosition);
	window.addEventListener("scroll", updateTargetPosition);

	if (activeStep.value) {
		const targetEl = document.querySelector(activeStep.value.targetSelector);
		if (targetEl && typeof ResizeObserver !== "undefined") {
			resizeObserver?.disconnect();
			resizeObserver = new ResizeObserver(updateTargetPosition);
			resizeObserver.observe(targetEl);
		}
	}
};

const cleanupPositionListeners = () => {
	if (typeof window === "undefined") return;
	window.removeEventListener("resize", updateTargetPosition);
	window.removeEventListener("scroll", updateTargetPosition);
	resizeObserver?.disconnect();
	resizeObserver = null;
};

const isMobile = ref(typeof window !== "undefined" ? window.innerWidth < 640 : false);

const updateIsMobile = () => {
	if (typeof window !== "undefined") {
		isMobile.value = window.innerWidth < 640;
	}
};

watch(activeCoachmarkId, (newVal) => {
	if (newVal && activeStep.value) {
		nextTick(() => {
			const targetEl = document.querySelector(
				activeStep.value?.targetSelector || "",
			);
			if (targetEl) {
				targetEl.scrollIntoView({
					behavior: "smooth",
					block: "center",
					inline: "center",
				});
			}
			setTimeout(() => {
				updateTargetPosition();
				setupPositionListeners();
			}, 350);
		});
	} else {
		cleanupPositionListeners();
		targetPos.value = null;
	}
});

const handleKeyDown = (e: KeyboardEvent) => {
	if (!activeStep.value) return;
	if (e.key === "Escape") {
		activeCoachmarkId.value = null;
	} else if (e.key === "ArrowRight") {
		nextCoachmark();
	} else if (e.key === "ArrowLeft") {
		prevCoachmark();
	} else if (e.key === "Enter") {
		e.preventDefault();
		markStepComplete(activeStep.value.id);
	}
};

const openTourManually = () => {
	isDismissedForever.value = false;
	isOpen.value = true;
	activeCoachmarkId.value = null;
	try {
		localStorage.removeItem(STORAGE_KEY.value);
	} catch (_) {}
};

onMounted(() => {
	if (typeof window === "undefined") return;
	updateIsMobile();
	window.addEventListener("resize", updateIsMobile);
	window.addEventListener("keydown", handleKeyDown);
	window.addEventListener("pagi:open_onboarding", openTourManually);
	checkStorageState();
});

onUnmounted(() => {
	if (typeof window === "undefined") return;
	window.removeEventListener("resize", updateIsMobile);
	window.removeEventListener("keydown", handleKeyDown);
	window.removeEventListener("pagi:open_onboarding", openTourManually);
	cleanupPositionListeners();
});

const toggleChecklist = () => {
	isOpen.value = !isOpen.value;
	if (!isOpen.value) {
		activeCoachmarkId.value = null;
	}
};

const startCoachmark = (stepId: string) => {
	if (completedSteps.value.has(stepId)) return;
	activeCoachmarkId.value = stepId;
};

const markStepComplete = (stepId: string) => {
	const newSet = new Set(completedSteps.value);
	newSet.add(stepId);
	completedSteps.value = newSet;

	// Advance to next incomplete step
	const currentIndex = steps.value.findIndex((s) => s.id === stepId);
	const nextIncomplete = steps.value
		.slice(currentIndex + 1)
		.find((s) => !newSet.has(s.id));

	if (nextIncomplete) {
		setTimeout(() => {
			activeCoachmarkId.value = nextIncomplete.id;
		}, 300);
	} else {
		activeCoachmarkId.value = null;
	}

	if (newSet.size === steps.value.length) {
		finishOnboarding();
	}
};

const nextCoachmark = () => {
	if (activeStepIndex.value < 0) return;
	for (let i = activeStepIndex.value + 1; i < steps.value.length; i++) {
		if (!completedSteps.value.has(steps.value[i].id)) {
			activeCoachmarkId.value = steps.value[i].id;
			return;
		}
	}
};

const prevCoachmark = () => {
	if (activeStepIndex.value <= 0) return;
	for (let i = activeStepIndex.value - 1; i >= 0; i--) {
		if (!completedSteps.value.has(steps.value[i].id)) {
			activeCoachmarkId.value = steps.value[i].id;
			return;
		}
	}
};

const finishOnboarding = () => {
	try {
		localStorage.setItem(STORAGE_KEY.value, "completed");
	} catch (_) {}
	isDismissedForever.value = true;
	isOpen.value = false;
	activeCoachmarkId.value = null;
};

const dismissOnboarding = () => {
	try {
		localStorage.setItem(STORAGE_KEY.value, "dismissed");
	} catch (_) {}
	isDismissedForever.value = true;
	isOpen.value = false;
	activeCoachmarkId.value = null;
};

// Compute dynamic position & pointer arrow position for coachmark popover card
const popoverConfig = computed(() => {
	if (!targetPos.value) return { style: {}, arrowDir: "up", arrowLeft: 50 };

	const { top, left, width, height } = targetPos.value;
	const winWidth = typeof window !== "undefined" ? window.innerWidth : 360;
	const winHeight = typeof window !== "undefined" ? window.innerHeight : 640;

	const cardWidth = Math.min(290, winWidth - 24);
	const cardHeight = 165;
	const margin = 14;

	const elementCenterX = left + width / 2;

	let calcLeft = elementCenterX - cardWidth / 2;
	calcLeft = Math.max(12, Math.min(calcLeft, winWidth - cardWidth - 12));

	// Calculate arrow pointer X offset inside the card relative to target element center
	const arrowLeft = Math.max(20, Math.min(elementCenterX - calcLeft, cardWidth - 20));

	let calcTop = top + height + margin;
	let arrowDir: "up" | "down" = "up";

	// Place card ABOVE element ONLY if placing below would overflow screen bottom OR element is in lower 35% of viewport
	if (top > winHeight * 0.65 || calcTop + cardHeight > winHeight - 12) {
		calcTop = top - cardHeight - margin;
		arrowDir = "down";
	}

	// Clamp calcTop safely within viewport top/bottom margins
	calcTop = Math.max(12, Math.min(calcTop, winHeight - cardHeight - 12));

	return {
		style: {
			top: `${calcTop}px`,
			left: `${calcLeft}px`,
			width: `${cardWidth}px`,
		},
		arrowDir,
		arrowLeft,
	};
});
</script>

<template>
	<!-- Floating Launcher Button (FAB) — Hides completely once completed / dismissed -->
	<div
		v-if="!isOpen && !activeCoachmarkId && !isDismissedForever"
		class="fixed bottom-5 right-5 z-40"
	>
		<button
			@click="toggleChecklist"
			class="relative group flex items-center gap-2.5 h-12 px-4 rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-semibold shadow-xl hover:scale-105 active:scale-95 transition-all duration-300 border border-slate-700/50 dark:border-slate-200/50 cursor-pointer"
			:aria-label="tourTitle"
		>
			<!-- App Logo Badge -->
			<div
				class="h-6 w-6 rounded-lg flex items-center justify-center overflow-hidden shrink-0 transition-transform duration-300 group-hover:scale-110"
				:class="siteSettings.brand_logo ? 'bg-transparent border-0 p-0 shadow-none' : 'bg-gradient-to-br from-indigo-600 to-purple-600 shadow-xs'"
			>
				<img
					v-if="siteSettings.brand_logo"
					:src="siteSettings.brand_logo"
					class="h-full w-full object-contain"
					alt="Logo App"
				/>
				<span v-else class="text-white text-[10px] font-black tracking-tight">P</span>
			</div>

			<span class="text-xs font-bold tracking-tight">{{ isProfilePage ? 'Panduan Profil' : 'Panduan PAGI' }}</span>
			<span
				v-if="completedCount < totalSteps"
				class="ml-0.5 px-1.5 py-0.5 rounded-full text-[10px] font-black bg-indigo-600 text-white"
			>
				{{ completedCount }}/{{ totalSteps }}
			</span>
			<span v-else class="ml-0.5 text-emerald-400 font-bold">✓</span>
		</button>
	</div>

	<!-- Interactive Checklist Modal Card -->
	<Transition
		enter-active-class="transition duration-300 ease-out"
		enter-from-class="opacity-0 translate-y-4 scale-95"
		enter-to-class="opacity-100 translate-y-0 scale-100"
		leave-active-class="transition duration-200 ease-in"
		leave-from-class="opacity-100 translate-y-0 scale-100"
		leave-to-class="opacity-0 translate-y-4 scale-95"
	>
		<div
			v-if="isOpen && !activeCoachmarkId && !isDismissedForever"
			class="fixed inset-x-4 bottom-20 sm:bottom-5 sm:right-5 sm:left-auto sm:inset-x-auto z-50 w-auto sm:w-80 max-h-[75vh] sm:max-h-[85vh] bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-3xl sm:rounded-2xl shadow-2xl overflow-hidden flex flex-col pointer-events-auto select-none"
		>
			<!-- Header with App Logo -->
			<div class="p-4 border-b border-slate-100 dark:border-zinc-800/80 bg-slate-50/50 dark:bg-zinc-900/50 flex items-center justify-between">
				<div class="flex items-center gap-2.5">
					<div
						class="h-7 w-7 rounded-lg overflow-hidden flex items-center justify-center shrink-0"
						:class="siteSettings.brand_logo ? 'bg-transparent border-0 p-0 shadow-none' : 'bg-gradient-to-br from-indigo-600 to-purple-600 shadow-xs'"
					>
						<img
							v-if="siteSettings.brand_logo"
							:src="siteSettings.brand_logo"
							class="h-full w-full object-contain"
							alt="Logo App"
						/>
						<span v-else class="text-white text-xs font-black tracking-tight">P</span>
					</div>
					<div>
						<h3 class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-100">
							{{ tourTitle }}
						</h3>
						<p class="text-[10px] text-slate-500 dark:text-slate-400">
							Role: <span class="capitalize font-semibold text-indigo-600 dark:text-indigo-400">{{ roleName }}</span>
						</p>
					</div>
				</div>
				<button
					@click="dismissOnboarding"
					class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg transition-colors cursor-pointer"
					aria-label="Tutup"
				>
					<X class="h-4 w-4" />
				</button>
			</div>

			<!-- Progress Bar -->
			<div class="px-4 pt-3 pb-2 space-y-1 bg-white dark:bg-zinc-900">
				<div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 dark:text-slate-400">
					<span>Kemajuan</span>
					<span class="text-indigo-600 dark:text-indigo-400 font-bold">{{ completedCount }}/{{ totalSteps }}</span>
				</div>
				<div class="w-full bg-slate-100 dark:bg-zinc-800 h-1.5 rounded-full overflow-hidden">
					<div
						class="h-full bg-gradient-to-r from-indigo-500 to-purple-600 transition-all duration-500 rounded-full"
						:style="{ width: `${progressPercent}%` }"
					></div>
				</div>
			</div>

			<!-- Checklist Items -->
			<div class="flex-1 overflow-y-auto p-4 space-y-2.5 max-h-72">
				<div
					v-for="step in steps"
					:key="step.id"
					@click="startCoachmark(step.id)"
					class="p-2.5 rounded-xl border transition-all duration-200 flex items-start gap-3 cursor-pointer group"
					:class="[
						completedSteps.has(step.id)
							? 'bg-emerald-50/50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-900/40 opacity-75'
							: 'bg-white dark:bg-zinc-900/90 border-slate-200 dark:border-zinc-800 hover:border-indigo-300 dark:hover:border-indigo-800 hover:shadow-xs'
					]"
				>
					<div class="mt-0.5 shrink-0">
						<div
							v-if="completedSteps.has(step.id)"
							class="h-4.5 w-4.5 rounded-full bg-emerald-500 text-white flex items-center justify-center"
						>
							<Check class="h-3 w-3" />
						</div>
						<Circle
							v-else
							class="h-4.5 w-4.5 text-slate-300 dark:text-zinc-600 group-hover:text-indigo-500 transition-colors"
						/>
					</div>

					<div class="flex-1 min-w-0">
						<h4
							class="text-xs font-bold text-slate-800 dark:text-slate-200 leading-snug group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors"
							:class="{ 'line-through text-slate-400 dark:text-slate-500': completedSteps.has(step.id) }"
						>
							{{ step.title }}
						</h4>
						<p
							v-if="step.description"
							class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed"
						>
							{{ step.description }}
						</p>
					</div>
				</div>
			</div>

			<!-- Footer Action -->
			<div class="p-3 border-t border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/50 flex items-center justify-between text-xs">
				<button
					@click="dismissOnboarding"
					class="text-[11px] font-medium text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors cursor-pointer"
				>
					Sembunyikan
				</button>

				<button
					v-if="allCompleted"
					@click="finishOnboarding"
					class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-1.5 shadow-sm transition-all cursor-pointer"
				>
					<CheckCircle2 class="h-3.5 w-3.5" />
					Selesai Tur
				</button>
				<button
					v-else
					@click="toggleChecklist"
					class="px-3 py-1.5 rounded-lg bg-slate-800 dark:bg-zinc-800 hover:bg-slate-700 text-white font-semibold text-[11px] transition-all cursor-pointer"
				>
					Nanti Saja
				</button>
			</div>
		</div>
	</Transition>

	<!-- Coachmark Spotlight Overlay -->
	<Teleport to="body">
		<Transition
			enter-active-class="transition duration-200 ease-out"
			enter-from-class="opacity-0"
			enter-to-class="opacity-100"
			leave-active-class="transition duration-150 ease-in"
			leave-from-class="opacity-100"
			leave-to-class="opacity-0"
		>
			<div
				v-if="activeStep"
				class="fixed inset-0 z-[100] pointer-events-none"
			>
				<!-- Radial Spotlight Mask (if target is found) -->
				<div
					v-if="targetPos"
					class="absolute inset-0 transition-all duration-300"
					:style="{
						background: `radial-gradient(circle at ${targetPos.left + targetPos.width / 2}px ${targetPos.top + targetPos.height / 2}px, transparent ${Math.max(targetPos.width, targetPos.height) / 2 + 8}px, rgba(0, 0, 0, 0.72) ${Math.max(targetPos.width, targetPos.height) / 2 + 9}px)`
					}"
				></div>

				<!-- Full Dim Overlay (if target selector not found) -->
				<div
					v-else
					class="absolute inset-0 bg-black/70 backdrop-blur-xs flex items-center justify-center pointer-events-auto"
				>
					<div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 p-6 rounded-2xl max-w-sm mx-4 shadow-2xl text-center">
						<h3 class="font-bold text-sm text-slate-800 dark:text-slate-100 mb-2">
							{{ activeStep.title }}
						</h3>
						<p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
							Elemen navigasi ini berada di halaman atau sudut lain. Klik "Tandai Selesai" untuk melanjutkan.
						</p>
						<div class="flex justify-center gap-2">
							<button
								@click="activeCoachmarkId = null"
								class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
							>
								Tutup
							</button>
							<button
								@click="markStepComplete(activeStep.id)"
								class="px-3 py-1.5 text-xs font-bold rounded-lg bg-indigo-600 text-white hover:bg-indigo-500 transition-colors cursor-pointer"
							>
								Tandai Selesai
							</button>
						</div>
					</div>
				</div>

				<!-- Spotlight Highlight Border -->
				<div
					v-if="targetPos"
					class="absolute border-2 border-indigo-500 rounded-xl transition-all duration-300 pointer-events-none z-[101] animate-pulse"
					:style="{
						top: `${targetPos.top - 5}px`,
						left: `${targetPos.left - 5}px`,
						width: `${targetPos.width + 10}px`,
						height: `${targetPos.height + 10}px`,
						boxShadow: '0 0 0 4px rgba(99, 102, 241, 0.35), 0 0 25px rgba(99, 102, 241, 0.6)'
					}"
				></div>

				<!-- Precise Icon-Anchored Coachmark Popover Card -->
				<div
					v-if="targetPos"
					class="fixed z-[102] bg-white/98 dark:bg-zinc-900/98 backdrop-blur-2xl border border-slate-200/90 dark:border-zinc-800 rounded-2xl p-4 shadow-[0_12px_40px_rgba(0,0,0,0.3)] pointer-events-auto transition-all duration-300 select-none"
					:style="popoverConfig.style"
				>
					<!-- Dynamic Directional Pointer Arrow -->
					<div
						v-if="popoverConfig.arrowDir === 'up'"
						class="absolute -top-2 w-3.5 h-3.5 bg-white dark:bg-zinc-900 border-t border-l border-slate-200/90 dark:border-zinc-800 rotate-45 z-10 transition-all duration-300"
						:style="{ left: `${popoverConfig.arrowLeft}px`, transform: 'translateX(-50%) rotate(45deg)' }"
					></div>
					<div
						v-else
						class="absolute -bottom-2 w-3.5 h-3.5 bg-white dark:bg-zinc-900 border-b border-r border-slate-200/90 dark:border-zinc-800 rotate-45 z-10 transition-all duration-300"
						:style="{ left: `${popoverConfig.arrowLeft}px`, transform: 'translateX(-50%) rotate(45deg)' }"
					></div>

					<!-- Header Stepper Info & Segmented Progress Bar -->
					<div class="space-y-1.5 mb-2.5">
						<div class="flex items-center justify-between gap-2">
							<span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/60">
								Langkah {{ activeStepIndex + 1 }} dari {{ totalSteps }}
							</span>

							<button
								@click="activeCoachmarkId = null"
								class="p-0.5 rounded-full text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
								aria-label="Tutup"
							>
								<X class="h-3.5 w-3.5" />
							</button>
						</div>

						<!-- Segmented Progress Indicators -->
						<div class="flex items-center gap-1 w-full pt-0.5">
							<div
								v-for="(st, idx) in steps"
								:key="st.id"
								class="h-1 flex-1 rounded-full transition-all duration-300"
								:class="[
									completedSteps.has(st.id)
										? 'bg-indigo-600 dark:bg-indigo-500'
										: idx === activeStepIndex
										? 'bg-indigo-400 dark:bg-indigo-400 animate-pulse'
										: 'bg-slate-100 dark:bg-zinc-800'
								]"
							></div>
						</div>
					</div>

					<h4 class="font-extrabold text-xs sm:text-sm text-slate-900 dark:text-slate-50 mb-1 leading-snug tracking-tight">
						{{ activeStep.title }}
					</h4>

					<p v-if="activeStep.description" class="text-[11px] sm:text-xs text-slate-600 dark:text-zinc-300 mb-3.5 leading-relaxed">
						{{ activeStep.description }}
					</p>

					<!-- Action Controls -->
					<div class="flex items-center justify-between gap-2 pt-2.5 border-t border-slate-100 dark:border-zinc-800/80">
						<div class="flex items-center gap-1">
							<button
								@click="prevCoachmark"
								:disabled="activeStepIndex <= 0"
								class="p-1 rounded-lg border border-slate-200 dark:border-zinc-700 text-slate-600 dark:text-slate-300 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
								title="Sebelumnya"
							>
								<ChevronLeft class="h-3.5 w-3.5" />
							</button>
							<button
								@click="nextCoachmark"
								:disabled="activeStepIndex >= totalSteps - 1"
								class="p-1 rounded-lg border border-slate-200 dark:border-zinc-700 text-slate-600 dark:text-slate-300 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
								title="Selanjutnya"
							>
								<ChevronRight class="h-3.5 w-3.5" />
							</button>
						</div>

						<button
							@click="markStepComplete(activeStep.id)"
							class="px-3 py-1 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:scale-95 text-white font-bold text-xs flex items-center gap-1 shadow-sm transition-all cursor-pointer"
						>
							<Check class="h-3.5 w-3.5" />
							<span>{{ activeStepIndex >= totalSteps - 1 ? 'Selesai' : 'Lanjut' }}</span>
						</button>
					</div>
				</div>
			</div>
		</Transition>
	</Teleport>
</template>
