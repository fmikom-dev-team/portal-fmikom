/**
 * useServiceWorker.ts
 *
 * Composable yang mengelola siklus hidup Service Worker secara otomatis.
 *
 * Behavior "selalu muncul hingga diinstall":
 * - Update terdeteksi → updateAvailable = true (persistent sampai user install)
 * - User klik "Nanti" → banner sembunyi selama SNOOZE_DURATION (default: 5 menit)
 * - Setiap navigasi Inertia / kembali ke tab: cek kembali, tampilkan lagi jika snooze habis
 * - User klik "Install" → SW aktif → reload → updateAvailable = false
 */

import { onUnmounted, ref } from "vue";

// ── Module-level state: di-share antar semua instance composable ──────────────
const updateAvailable = ref(false);
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

	// Registrasi Service Worker PWA
	navigator.serviceWorker.register('/sw-pwa.js', { scope: '/' })
		.then((reg) => {
			console.log("[PWA] Service Worker registered successfully with scope:", reg.scope);
		})
		.catch((err) => {
			console.warn("[PWA] Service Worker registration failed:", err);
		});

	// Dengarkan pesan APP_UPDATED dari SW → reload seamless
	navigator.serviceWorker.addEventListener("message", (event: MessageEvent) => {
		if (event.data?.type === "APP_UPDATED") {
			clearSnooze();
			window.location.reload();
		}
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

	// Cek update saat online kembali
	window.addEventListener("online", () => {
		globalRegistration?.update().catch(() => {});
	});
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

	return {
		updateAvailable,
		applyUpdate,
		isSnoozed,
		snoozeUpdate,
	};
}
