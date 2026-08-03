import { router } from "@inertiajs/vue3";
import { showToast } from "./useGlobalToast";

/**
 * Initialize global flash toast handler using Inertia's router events.
 * Call once in app.ts — no need to call in layouts or pages.
 */
export function initFlashToast() {
	router.on("success", (event) => {
		const flash = (event.detail.page.props as any)?.flash;
		if (!flash) return;

		if (flash.success) showToast(flash.success, "success");
		if (flash.error) showToast(flash.error, "error");
		if (flash.warning) showToast(flash.warning, "warning");
		if (flash.info) showToast(flash.info, "info");
	});
}
