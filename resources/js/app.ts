import { createInertiaApp, router } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import type { DefineComponent } from "vue";
import { createApp, h } from "vue";
import "../css/app.css";
import "../css/workos.css";
import axios from "axios";
import { initializeTheme } from "@/composables/useAppearance";

// @ts-expect-error
window.axios = axios;
// @ts-expect-error
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// ── Laravel Echo (Reverb WebSocket) ─────────────────────────────────────────
import Echo from "laravel-echo";
import Pusher from "pusher-js";
// @ts-expect-error
window.Pusher = Pusher;

const isHttps = window.location.protocol === "https:";
const isLocal = ["localhost", "127.0.0.1", "::1"].includes(window.location.hostname);
const wsHost = import.meta.env.VITE_REVERB_HOST || window.location.hostname;
const wsPort = isHttps && !isLocal ? undefined : (import.meta.env.VITE_REVERB_PORT || 8080);
const wssPort = isHttps && !isLocal ? undefined : (import.meta.env.VITE_REVERB_PORT || 8080);
const forceTLS = isHttps || (import.meta.env.VITE_REVERB_SCHEME === "https");

// @ts-expect-error
window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost,
    wsPort,
    wssPort,
    forceTLS,
    enabledTransports: ["ws", "wss"],
    authEndpoint: "/broadcasting/auth",
});

// Connection diagnostics logging
if (window.Echo && window.Echo.connector && window.Echo.connector.pusher) {
    const pusherConn = window.Echo.connector.pusher.connection;
    pusherConn.bind("state_change", (states: { previous: string; current: string }) => {
        console.log(`[Echo Connection] State changed from "${states.previous}" to "${states.current}"`);
    });
    pusherConn.bind("error", (err: any) => {
        console.error("[Echo Connection] Error details:", err);
    });
}


const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createInertiaApp({
	title: (title) => (title ? `${title} - ${appName}` : appName),
	resolve: (name) =>
		resolvePageComponent(
			`./pages/${name}.vue`,
			import.meta.glob<DefineComponent>("./pages/**/*.vue"),
		),
	setup({ el, App, props, plugin }) {
		createApp({ render: () => h(App, props) })
			.use(plugin)
			.mount(el);
	},
	progress: {
		color: "#4B5563",
	},
});

// -----------------------------------------------------------------------
// Global Inertia Event Handlers
// -----------------------------------------------------------------------

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
				message: "Gagal mengunggah! Ukuran berkas terlalu besar (melebihi batas server 100MB). Hubungi admin jika masalah berlanjut.",
			}
		});
		window.dispatchEvent(customEvent);
	} else if (status === 422) {
		event.preventDefault();
		const responseData = event.detail.response?.data;
		let errMsg = "Data yang Anda masukkan tidak valid.";
		if (responseData && responseData.errors) {
			const firstError = Object.values(responseData.errors)[0];
			if (Array.isArray(firstError)) {
				errMsg = firstError[0];
			} else if (typeof firstError === 'string') {
				errMsg = firstError;
			}
		} else if (responseData && responseData.message) {
			errMsg = responseData.message;
		}
		const customEvent = new CustomEvent("pagi-http-error", {
			detail: {
				status,
				message: errMsg,
			}
		});
		window.dispatchEvent(customEvent);
	}
});

// This will set light / dark mode on page load...
initializeTheme();
