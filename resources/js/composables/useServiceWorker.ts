/**
 * useServiceWorker.ts
 *
 * Composable yang mengelola siklus hidup Service Worker PWA secara otomatis
 * dengan alur interaktif modern (User Confirmation + Animated Installing Modal).
 */

import { ref } from "vue";

// ── Module-level state: di-share antar semua instance composable ──────────────
const updateAvailable = ref(false);
const isInstalling = ref(false);
const installProgress = ref(0);
const installStepText = ref("");

let waitingWorker: ServiceWorker | null = null;
let globalRegistration: ServiceWorkerRegistration | null = null;

// Durasi snooze ketika user klik "Nanti" (5 menit)
const SNOOZE_KEY = "fmikom_update_snoozed_until";
const SNOOZE_DURATION_MS = 5 * 60 * 1000;

export function isSnoozed(): boolean {
	try {
		const val = localStorage.getItem(SNOOZE_KEY);
		if (!val) return false;
		return Date.now() < parseInt(val, 10);
	} catch {
		return false;
	}
}

export function snoozeUpdate(): void {
	try {
		localStorage.setItem(SNOOZE_KEY, String(Date.now() + SNOOZE_DURATION_MS));
	} catch {
		/* ignore */
	}
}

export function clearSnooze(): void {
	try {
		localStorage.removeItem(SNOOZE_KEY);
	} catch {
		/* ignore */
	}
}

// ── Singleton init: hanya dijalankan sekali di app.ts ────────────────────────
let initialized = false;

export function initServiceWorkerUpdater(): void {
	if (
		initialized ||
		typeof window === "undefined" ||
		!("serviceWorker" in navigator)
	)
		return;
	initialized = true;

	// Registrasi Service Worker PWA dengan updateViaCache: 'none'
	// agar browser selalu mengecek file sw-pwa.js terbaru di server (tanpa tertahan HTTP cache)
	navigator.serviceWorker
		.register("/sw-pwa.js", { scope: "/", updateViaCache: "none" })
		.then((reg) => {
			globalRegistration = reg;
			// Paksa cek update pertama kali saat registrasi selesai
			reg.update().catch(() => {});
		})
		.catch((err) => {
			console.warn("[PWA] Service Worker registration failed:", err);
		});

	// Refreshes page ONCE when new Service Worker takes control after user clicks Install
	let refreshing = false;
	navigator.serviceWorker.addEventListener("controllerchange", () => {
		if (refreshing) return;
		refreshing = true;
		clearSnooze();
		window.location.reload();
	});

	navigator.serviceWorker.ready.then((reg) => {
		globalRegistration = reg;

		// SW baru sudah menunggu dari sesi sebelumnya (tab dibuka setelah build)
		if (reg.waiting && navigator.serviceWorker.controller) {
			waitingWorker = reg.waiting;
			updateAvailable.value = true;
		}

		// Pantau update yang akan datang
		reg.addEventListener("updatefound", () => {
			const newWorker = reg.installing;
			if (!newWorker) return;

			newWorker.addEventListener("statechange", () => {
				if (
					newWorker.state === "installed" &&
					navigator.serviceWorker.controller
				) {
					waitingWorker = newWorker;
					updateAvailable.value = true;
				}
			});
		});
	});

	// Cek update saat user kembali ke tab
	document.addEventListener("visibilitychange", () => {
		if (document.visibilityState === "visible") {
			globalRegistration?.update().catch(() => {});
		}
	});

	// Cek update secara berkala setiap 30 detik
	setInterval(() => {
		globalRegistration?.update().catch(() => {});
	}, 30000);

	// Cek update saat online kembali
	window.addEventListener("online", () => {
		globalRegistration?.update().catch(() => {});
	});
}

/** Cek manual Service Worker update (misal dipicu saat navigasi halaman) */
export function triggerSWCheck(): void {
	if (globalRegistration) {
		globalRegistration.update().catch(() => {});
	}
}

// ── Composable untuk digunakan di komponen ───────────────────────────────────
export function useServiceWorker() {
	function applyUpdate(): void {
		clearSnooze();
		if (waitingWorker) {
			waitingWorker.postMessage({ type: "SKIP_WAITING" });
		} else {
			window.location.reload();
		}
	}

	function startInteractiveUpdate(): void {
		if (isInstalling.value) return;

		isInstalling.value = true;
		installProgress.value = 0;
		installStepText.value = "Menyiapkan aset terbaru...";

		// Step 1: 0% -> 40%
		setTimeout(() => {
			installProgress.value = 40;
			installStepText.value = "Memperbarui cache Service Worker...";
		}, 300);

		// Step 2: 40% -> 80%
		setTimeout(() => {
			installProgress.value = 85;
			installStepText.value = "Membersihkan berkas lama & menyegarkan...";
		}, 700);

		// Step 3: 85% -> 100% & trigger reload
		setTimeout(() => {
			installProgress.value = 100;
			installStepText.value = "Pembaruan selesai! Memuat ulang...";

			setTimeout(() => {
				applyUpdate();
			}, 300);
		}, 1100);
	}

	return {
		updateAvailable,
		isInstalling,
		installProgress,
		installStepText,
		applyUpdate,
		startInteractiveUpdate,
		isSnoozed,
		snoozeUpdate,
	};
}
