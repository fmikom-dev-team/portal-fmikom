import { router } from "@inertiajs/vue3";
import { toast } from "vue-sonner";

/**
 * Initialize global flash toast handler using Inertia's router events.
 * Call once in app.ts Ã¢â‚¬â€ no need to call in layouts or pages.
 *
 * Uses the success event payload so we read the freshly navigated page,
 * which is more reliable than reading router.page on finish.
 */
export function initFlashToast() {
	let lastToastTime = 0;
	let lastToastMessage = "";

	router.on("success", (event) => {
		const flash = (event.detail.page.props as any)?.flash;
		if (!flash) return;

		const now = Date.now();
		if (flash.success) {
			if (flash.success !== lastToastMessage || now - lastToastTime > 1500) {
				toast.success(flash.success);
				lastToastMessage = flash.success;
				lastToastTime = now;
			}
		}
		if (flash.error) {
			if (flash.error !== lastToastMessage || now - lastToastTime > 1500) {
				toast.error(flash.error);
				lastToastMessage = flash.error;
				lastToastTime = now;
			}
		}
		if (flash.warning) toast.warning(flash.warning);
		if (flash.info) toast.info(flash.info);
	});
}
