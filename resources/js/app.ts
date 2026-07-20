import { createInertiaApp, router } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import type { DefineComponent } from "vue";
import { createApp, h, Teleport } from "vue";
import Echo from "laravel-echo";
import Pusher from "pusher-js";
import { Toaster } from 'vue-sonner';
import 'vue-sonner/style.css';
import "../css/app.css";
import axios from "axios";
import { initializeTheme } from "@/composables/useAppearance";
import { useLoadingState } from "@/composables/useLoadingState";
import { initFlashToast } from "@/composables/useFlashToast";
import { initServiceWorkerUpdater } from "@/composables/useServiceWorker";

(globalThis as any).axios = axios;
(globalThis as any).axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
(globalThis as any).axios.defaults.xsrfCookieName = "fm_csrf";
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
				console.error("[Echo Connection] Error details:", err);
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
			position: 'bottom-right',
			offset: 28,
			mobileOffset: 16,
			duration: 3200,
			richColors: true,
			theme: 'light',
			closeButton: true,
			gap: 10,
			visibleToasts: 3,
			toastOptions: {
				class: 'w-[min(92vw,26rem)] rounded-3xl border px-4 py-3.5 shadow-[0_24px_60px_-24px_rgba(15,23,42,0.28)] backdrop-blur-2xl',
				descriptionClass: 'text-sm leading-5 text-slate-500',
				classes: {
					toast: 'rounded-3xl border border-slate-200/80 bg-white/80 text-slate-800 ring-1 ring-white/50',
					title: 'text-[13px] font-semibold tracking-tight',
					description: 'text-[12px] leading-5 text-slate-500',
					success: 'border-emerald-400/95 bg-emerald-50 text-emerald-700 shadow-emerald-900/12',
					error: 'border-rose-400/95 bg-rose-50 text-rose-700 shadow-rose-900/12',
					info: 'border-sky-200/80 bg-sky-50/80 text-sky-900 shadow-sky-950/5',
					warning: 'border-amber-200/80 bg-amber-50/80 text-amber-900 shadow-amber-950/5',
				},
			},
		}).mount(toasterEl);

		if ((props.initialPage.props as any).auth?.user) {
			initEcho((props.initialPage.props as any).reverb);
		}

		// Mount AppUpdateBanner sebagai app Vue mandiri di luar Inertia
		// sehingga muncul di SEMUA halaman, tanpa peduli layout yang dipakai
		import('@/components/AppUpdateBanner.vue').then(({ default: AppUpdateBanner }) => {
			const bannerEl = document.createElement('div');
			bannerEl.id = 'app-update-banner-root';
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
		globalThis.dispatchEvent(customEvent);
	}
});

initializeTheme();

// Inisialisasi Service Worker PWA
if (typeof navigator !== "undefined" && "serviceWorker" in navigator) {
	initServiceWorkerUpdater();
}

// Global flash ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ toast handler (fires once per Inertia navigation)
initFlashToast();
