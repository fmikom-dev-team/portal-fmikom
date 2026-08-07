import { createInertiaApp, router } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import type { DefineComponent } from "vue";
import { createApp, h } from "vue";
declare const __APP_BUILD_TIME__: string;
import Echo from "laravel-echo";
import Pusher from "pusher-js";
import { Toaster } from 'vue-sonner';
import 'vue-sonner/style.css';
import "../css/app.css";
import axios from "axios";
import { initializeTheme } from "@/composables/useAppearance";
import { useLoadingState } from "@/composables/useLoadingState";
import { initFlashToast } from "@/composables/useFlashToast";
import { initServiceWorkerUpdater, triggerSWCheck } from "@/composables/useServiceWorker";

(globalThis as any).axios = axios;
(globalThis as any).axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
(globalThis as any).axios.defaults.xsrfCookieName = "XSRF-TOKEN";
(globalThis as any).axios.defaults.xsrfHeaderName = "X-XSRF-TOKEN";

// ── Handle Chunk/Dynamic Import Loading Failures ─────────────────────────
// Ini terjadi ketika build baru telah dirilis ke server, sehingga hash file statis
// berubah dan file lama dihapus. Jika user masih membuka halaman lama, navigasi
// berikutnya akan memicu 404 pada dynamic import chunk. Kita memaksa reload halaman
// untuk memuat versi/manifest terbaru.
// GUARD: Reload hanya terjadi SATU KALI per sesi menggunakan sessionStorage
// untuk mencegah infinite reload loop jika masalah berlanjut setelah reload.
const CHUNK_RELOAD_FLAG = 'fmikom_chunk_reload_attempted';

const handleChunkError = (error: any) => {
	if (
		error &&
		(error.name === "TypeError" || error.message?.includes("Failed to fetch")) &&
		typeof error.message === "string" &&
		(error.message.includes("dynamically imported module") || error.message.includes("chunk"))
	) {
		const alreadyReloaded = sessionStorage.getItem(CHUNK_RELOAD_FLAG);
		if (alreadyReloaded) {
			console.warn("Chunk load failed again after reload. Skipping further auto-reloads.", error);
			return;
		}
		console.warn("Dynamic import / chunk load failed. Clearing Service Worker and reloading page...", error);
		
		if (typeof navigator !== "undefined" && navigator.serviceWorker) {
			navigator.serviceWorker.getRegistrations().then((registrations) => {
				Promise.all(registrations.map(r => r.unregister())).finally(() => {
					sessionStorage.setItem(CHUNK_RELOAD_FLAG, '1');
					globalThis.location.reload();
				});
			}).catch(() => {
				sessionStorage.setItem(CHUNK_RELOAD_FLAG, '1');
				globalThis.location.reload();
			});
		} else {
			sessionStorage.setItem(CHUNK_RELOAD_FLAG, '1');
			globalThis.location.reload();
		}
	}
};

// Hapus flag setelah page load berhasil penuh (semua chunk dimuat tanpa error)
globalThis.addEventListener("load", () => {
	sessionStorage.removeItem(CHUNK_RELOAD_FLAG);
});

globalThis.addEventListener("unhandledrejection", (event) => {
	handleChunkError(event.reason);
});

globalThis.addEventListener("error", (event) => {
	handleChunkError(event.error);
});

let echoInitialized = false;

function initEcho(reverbProps?: { key?: string; host?: string; port?: string | number; scheme?: string }) {
	if (echoInitialized || (globalThis as any).Broadcaster) return;
	echoInitialized = true;

	try {
		if ((globalThis as any).Pusher) {
			delete (globalThis as any).Pusher;
		}

		const isHttps = globalThis.location.protocol === "https:";
		const isLocal = ["localhost", "127.0.0.1", "::1"].includes(
			globalThis.location.hostname,
		);
		const wsHost = reverbProps?.host || import.meta.env.VITE_REVERB_HOST || globalThis.location.hostname;
		const wsPort =
			reverbProps?.port || (isHttps && !isLocal ? undefined : import.meta.env.VITE_REVERB_PORT || 8080);
		const wssPort =
			reverbProps?.port || (isHttps && !isLocal ? undefined : import.meta.env.VITE_REVERB_PORT || 8080);
		const forceTLS = reverbProps?.scheme === "https" || isHttps || import.meta.env.VITE_REVERB_SCHEME === "https";
		const reverbAppKey = reverbProps?.key || import.meta.env.VITE_REVERB_APP_KEY;

		(globalThis as any).Broadcaster = new Echo({
			broadcaster: "reverb",
			key: reverbAppKey,
			wsHost,
			wsPort,
			wssPort,
			forceTLS,
			enabledTransports: ["ws", "wss"],
			authEndpoint: "/broadcasting/auth",
			Pusher,
		});

		// Fallback for components that still reference window.Echo
		(globalThis as any).Echo = (globalThis as any).Broadcaster;

		if ((globalThis as any).Pusher) {
			delete (globalThis as any).Pusher;
		}

		const echoConn = (globalThis as any).Broadcaster;
		if (echoConn?.connector?.pusher) {
			const pusherConn = echoConn.connector.pusher.connection;
			pusherConn.bind(
				"state_change",
				(states: { previous: string; current: string }) => {
					console.log(
						`[Echo Connection] State changed from "${states.previous}" to "${states.current}"`,
					);
				},
			);
			pusherConn.bind("error", (err: unknown) => {
				// Non-critical WebSocket debug log (UI uses REST API fallback seamlessly)
				if (import.meta.env.DEV) {
					console.debug("[Echo Connection] Transport info:", err);
				}
			});
		}
	} catch (error) {
		console.error("Failed to initialize Echo:", error);
		echoInitialized = false;
	}
}

window.addEventListener("pagehide", () => {
	if ((globalThis as any).Broadcaster) {
		(globalThis as any).Broadcaster.disconnect();
	}
});

window.addEventListener("pageshow", (event) => {
	if (event.persisted && (globalThis as any).Broadcaster) {
		(globalThis as any).Broadcaster.connect();
	}
});

const appName = import.meta.env.VITE_APP_NAME || "Portal";

createInertiaApp({
	title: (title) => {
		const brandName = ((router as any).page?.props?.siteSettings as any)?.brand_name || appName;
		return title ? `${title} - ${brandName}` : brandName;
	},
	resolve: (name) =>
		resolvePageComponent(
			`./pages/${name}.vue`,
			import.meta.glob<DefineComponent>("./pages/**/*.vue"),
		),
	setup({ el, App, props, plugin }) {
		createApp({ render: () => h(App, props) })
			.use(plugin)
			.mount(el);

		const toasterEl = document.createElement('div');
		toasterEl.id = 'toaster-root';
		document.body.appendChild(toasterEl);
		createApp(Toaster, {
			position: typeof window !== "undefined" && window.innerWidth < 640 ? "top-center" : "bottom-right",
			offset: 24,
			mobileOffset: 12,
			duration: 3500,
			richColors: true,
			theme: "light",
			closeButton: true,
			gap: 10,
			visibleToasts: 3,
			toastOptions: {
				class: "w-full max-w-full rounded-2xl border px-3.5 py-3 shadow-xl backdrop-blur-2xl transition-all select-none",
				descriptionClass: "text-[12px] leading-relaxed break-words whitespace-normal text-slate-500 dark:text-zinc-400",
				classes: {
					toast: "rounded-2xl border border-slate-200/80 dark:border-zinc-800 bg-white/95 dark:bg-zinc-900/95 text-slate-800 dark:text-zinc-100 ring-1 ring-black/5 shadow-2xl w-full max-w-full",
					title: "text-[12.5px] font-bold tracking-tight leading-snug break-words whitespace-normal",
					description: "text-[11.5px] leading-relaxed break-words whitespace-normal text-slate-500 dark:text-zinc-400",
					success: "border-emerald-400/95 bg-emerald-50 text-emerald-700 shadow-emerald-900/12",
					error: "border-rose-400/95 bg-rose-50 text-rose-700 shadow-rose-900/12",
					info: "border-sky-200/80 bg-sky-50/80 text-sky-900 shadow-sky-950/5",
					warning: "border-amber-200/80 bg-amber-50/80 text-amber-900 shadow-amber-950/5",
				},
			},
		}).mount(toasterEl);

		if ((props.initialPage.props as any).auth?.user) {
			initEcho((props.initialPage.props as any).reverb);
		}

		// Mount AppUpdateBanner sebagai app Vue mandiri di luar Inertia
		// sehingga muncul di SEMUA halaman, tanpa peduli layout yang dipakai.
		// is_pagi_admin berasal dari PHP middleware dan tersedia di semua halaman.
		import('@/components/AppUpdateBanner.vue').then(({ default: AppUpdateBanner }) => {
			const initialProps = (props.initialPage.props as any);
			const isAdmin = Boolean(initialProps.is_pagi_admin);
			const siteSettings = initialProps.siteSettings || {};

			const bannerEl = document.createElement('div');
			bannerEl.id = 'app-update-banner-root';
			bannerEl.dataset.isAdmin = isAdmin ? '1' : '0';
			bannerEl.dataset.appVersion = (typeof __APP_BUILD_TIME__ !== 'undefined')
				? __APP_BUILD_TIME__
				: String(Date.now());
			if (siteSettings.brand_name) {
				bannerEl.dataset.brandName = siteSettings.brand_name;
			}
			if (siteSettings.brand_logo) {
				bannerEl.dataset.brandLogo = siteSettings.brand_logo;
			}
			if (siteSettings.app_update_items) {
				bannerEl.dataset.updateItems = typeof siteSettings.app_update_items === 'string'
					? siteSettings.app_update_items
					: JSON.stringify(siteSettings.app_update_items);
			}
			document.body.appendChild(bannerEl);
			createApp(AppUpdateBanner).mount(bannerEl);
		});
	},
	progress: {
		color: "#4B5563",
	},
});

const { startLoading, stopLoading } = useLoadingState();
let loadingTimeout: ReturnType<typeof setTimeout>;

router.on("start", (event) => {
	const path = event.detail.visit.url.pathname;
	loadingTimeout = setTimeout(() => {
		startLoading(path);
	}, 120);
});

router.on("finish", () => {
	clearTimeout(loadingTimeout);
	stopLoading();
});

router.on("navigate", () => {
	document.body.style.overflow = "";
});

router.on("success", (event) => {
	const props = event.detail.page.props as any;

	// Re-apply theme state on page transitions to handle public vs private pages
	initializeTheme();

	if (props.auth?.user) {
		initEcho(props.reverb);
	}

	if (props.siteSettings) {
		const settings = props.siteSettings;

		if (settings.brand_favicon) {
			const favicons = document.querySelectorAll("link[rel*='icon']");
			favicons.forEach((el) => el.remove());

			const newFavicon = document.createElement("link");
			newFavicon.rel = "icon";
			newFavicon.href = settings.brand_favicon;
			document.head.appendChild(newFavicon);
		}

		if (settings.primary_color) {
			document.documentElement.style.setProperty("--primary", settings.primary_color);
			document.documentElement.style.setProperty("--wos-primary", settings.primary_color);
		}
	}

	if (props.is_pagi_admin !== undefined || props.siteSettings) {
		const bannerEl = document.getElementById('app-update-banner-root');
		if (bannerEl) {
			if (props.is_pagi_admin !== undefined) {
				bannerEl.dataset.isAdmin = props.is_pagi_admin ? '1' : '0';
			}
			if (props.siteSettings) {
				if (props.siteSettings.brand_name) bannerEl.dataset.brandName = props.siteSettings.brand_name;
				if (props.siteSettings.brand_logo) bannerEl.dataset.brandLogo = props.siteSettings.brand_logo;
				if (props.siteSettings.app_update_items) {
					bannerEl.dataset.updateItems = typeof props.siteSettings.app_update_items === 'string'
						? props.siteSettings.app_update_items
						: JSON.stringify(props.siteSettings.app_update_items);
				}
			}
		}
	}

	// Trigger SW check pada setiap navigasi halaman Inertia
	triggerSWCheck();
});

router.on("invalid", (event) => {
	const status = event.detail.response?.status;
	if (status === 401 || status === 419) {
		event.preventDefault();
		router.visit("/login", { replace: true });
	} else if (status === 413) {
		event.preventDefault();
		const customEvent = new CustomEvent("pagi-http-error", {
			detail: {
				status,
				message:
					"Gagal mengunggah! Ukuran berkas terlalu besar (melebihi batas server 100MB). Hubungi admin jika masalah berlanjut.",
			},
		});
		globalThis.dispatchEvent(customEvent);
	} else if (status === 422) {
		event.preventDefault();
		const responseData = event.detail.response?.data;
		let errMsg = "Data yang Anda masukkan tidak valid.";
		if (responseData?.errors) {
			const firstError = Object.values(responseData.errors)[0];
			if (Array.isArray(firstError)) {
				errMsg = firstError[0];
			} else if (typeof firstError === "string") {
				errMsg = firstError;
			}
		} else if (responseData?.message) {
			errMsg = responseData.message;
		}
		const customEvent = new CustomEvent("pagi-http-error", {
			detail: {
				status,
				message: errMsg,
			},
		});
	} else if (status === 502 || status === 503) {
		event.preventDefault();
		const visitUrl = event.detail.response?.config?.url || globalThis.location.pathname;
		const isAuthRoute = visitUrl.includes("/logout") || visitUrl.includes("/login");

		if (isAuthRoute) {
			globalThis.location.href = "/login";
			return;
		}

		// Perform a graceful silent retry for 502/503 network flaps or container restarts
		const retryKey = `fm_retry_${visitUrl}`;
		const currentRetries = Number.parseInt(sessionStorage.getItem(retryKey) || "0", 10);

		if (currentRetries < 2) {
			sessionStorage.setItem(retryKey, String(currentRetries + 1));
			setTimeout(() => {
				router.visit(visitUrl, { preserveScroll: true, preserveState: true });
			}, 1000);
		} else {
			sessionStorage.removeItem(retryKey);
			globalThis.location.href = "/login";
		}
	}
});

initializeTheme();

// Inisialisasi Service Worker PWA
if (typeof navigator !== "undefined" && "serviceWorker" in navigator) {
	initServiceWorkerUpdater();
}

// Global flash toast handler (fires once per Inertia navigation)
initFlashToast();
