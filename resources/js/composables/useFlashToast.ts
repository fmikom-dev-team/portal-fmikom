import { router } from "@inertiajs/vue3";
import { showToast } from "./useGlobalToast";

/**
 * Initialize global flash toast handler using Inertia's router events.
 * Consumes flash messages immediately so they never duplicate or persist on page navigation.
 */
export function initFlashToast() {
	router.on("success", (event) => {
		const props = event.detail.page.props as any;
		const flash = props?.flash;
		if (!flash) return;

		if (flash.success) {
			const msg = flash.success;
			delete flash.success;
			showToast(msg, "success");
		}

		if (flash.error) {
			const msg = flash.error;
			delete flash.error;
			showToast(msg, "error");
		}

		if (flash.warning) {
			const msg = flash.warning;
			delete flash.warning;
			showToast(msg, "warning");
		}

		if (flash.info) {
			const msg = flash.info;
			delete flash.info;
			showToast(msg, "info");
		}
	});
}
