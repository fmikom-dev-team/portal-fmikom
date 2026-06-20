import { createInertiaApp, router } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import type { DefineComponent } from "vue";
import { createApp, h } from "vue";
import "../css/app.css";
import axios from "axios";
import { initializeTheme } from "@/composables/useAppearance";
import { useLoadingState } from "@/composables/useLoadingState";

(globalThis as any).axios = axios;
(globalThis as any).axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
(globalThis as any).axios.defaults.xsrfCookieName = "fm_csrf";
(globalThis as any).axios.defaults.xsrfHeaderName = "X-XSRF-TOKEN";


// ── Laravel Echo (Reverb WebSocket) ─────────────────────────────────────────
let echoInitialized = false;

async function initEcho() {
	if (echoInitialized || (globalThis as any).Broadcaster) return;
	echoInitialized = true;

	try {
		const [{ default: Echo }, { default: Pusher }] = await Promise.all([
			import("laravel-echo"),
			import("pusher-js"),
		]);

		if ((globalThis as any).Pusher) {
			delete (globalThis as any).Pusher;
		}

		const isHttps = globalThis.location.protocol === "https:";
		const isLocal = ["localhost", "127.0.0.1", "::1"].includes(
			globalThis.location.hostname,
		);
		const wsHost = import.meta.env.VITE_REVERB_HOST || globalThis.location.hostname;
		const wsPort =
			isHttps && !isLocal ? undefined : import.meta.env.VITE_REVERB_PORT || 8080;
		const wssPort =
			isHttps && !isLocal ? undefined : import.meta.env.VITE_REVERB_PORT || 8080;
		const forceTLS = isHttps || import.meta.env.VITE_REVERB_SCHEME === "https";

		(globalThis as any).Broadcaster = new Echo({
			broadcaster: "reverb",
			key: import.meta.env.VITE_REVERB_APP_KEY,
			wsHost,
			wsPort,
			wssPort,
			forceTLS,
			enabledTransports: ["ws", "wss"],
			authEndpoint: "/broadcasting/auth",
			Pusher,
		});

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

// ── bfcache WebSocket Support ─────────────────────────────────────────
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
	http: {
		xsrfCookieName: "fm_csrf",
		xsrfHeaderName: "X-XSRF-TOKEN",
	},
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

		// Initialize Echo only if the user is authenticated on initial load
		if (props.initialPage.props.auth?.user) {
			initEcho();
		}
	},
	progress: {
		color: "#4B5563",
	},
});

// -----------------------------------------------------------------------
// Global Inertia Event Handlers
// -----------------------------------------------------------------------

// biome-ignore lint/correctness/useHookAtTopLevel: Vue composable, not a React hook
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

router.on("success", (event) => {
	const props = event.detail.page.props as any;

	// Initialize Echo dynamically if user logs in
	if (props.auth?.user) {
		initEcho();
	}

	if (props.siteSettings) {
		const settings = props.siteSettings;
		
		// 1. Dynamic Favicon Swapping
		if (settings.brand_favicon) {
			const favicons = document.querySelectorAll("link[rel*='icon']");
			favicons.forEach(el => el.remove());
			
			const newFavicon = document.createElement('link');
			newFavicon.rel = 'icon';
			newFavicon.href = settings.brand_favicon;
			document.head.appendChild(newFavicon);
		}

		// 2. Dynamic Primary Accent Color
		if (settings.primary_color) {
			document.documentElement.style.setProperty('--primary', settings.primary_color);
			document.documentElement.style.setProperty('--wos-primary', settings.primary_color);
		}
	}
});

/**
 * Handle response yang tidak valid dari server:
 * - 401 Unauthorized  → session habis atau belum login
 * - 419 Token Mismatch → CSRF token expired (session habis)
 * Keduanya redirect ke /login tanpa full page reload yang kasar.
 */
router.on("invalid", (event) => {
	const status = event.detail.response?.status;
	if (status === 401 || status === 419) {
		event.preventDefault();
		// Inertia visit ke login — lebih smooth daripada window.location
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

// This will set light / dark mode on page load...
initializeTheme();

// ── PWA Service Worker Registration ──────────────────────────────────────────
if ("serviceWorker" in navigator) {
	globalThis.addEventListener("load", () => {
		navigator.serviceWorker
			.register("/sw.js")
			.then((registration) => {
				console.log(
					"[PWA] Service Worker registered with scope:",
					registration.scope,
				);
			})
			.catch((error) => {
				console.error("[PWA] Service Worker registration failed:", error);
			});
	});
}
