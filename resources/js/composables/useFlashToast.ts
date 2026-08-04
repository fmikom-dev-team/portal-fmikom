import { router } from "@inertiajs/vue3";
import { showToast } from "./useGlobalToast";

const consumedFlashes = new Set<string>();

/**
 * Initialize global flash toast handler using Inertia's router events.
 * Consumes flash messages immediately so they never duplicate or persist on page navigation.
 */
export function initFlashToast() {
	router.on("success", (event) => {
		const props = event.detail.page.props as any;
		const flash = props?.flash;
		if (!flash) return;

		const successKey = flash.success ? `success:${flash.success}` : null;
		const errorKey = flash.error ? `error:${flash.error}` : null;
		const warningKey = flash.warning ? `warning:${flash.warning}` : null;
		const infoKey = flash.info ? `info:${flash.info}` : null;

		if (flash.success && !consumedFlashes.has(successKey!)) {
			consumedFlashes.add(successKey!);
			showToast(flash.success, "success");
			delete flash.success;
		}

		if (flash.error && !consumedFlashes.has(errorKey!)) {
			consumedFlashes.add(errorKey!);
			showToast(flash.error, "error");
			delete flash.error;
		}

		if (flash.warning && !consumedFlashes.has(warningKey!)) {
			consumedFlashes.add(warningKey!);
			showToast(flash.warning, "warning");
			delete flash.warning;
		}

		if (flash.info && !consumedFlashes.has(infoKey!)) {
			consumedFlashes.add(infoKey!);
			showToast(flash.info, "info");
			delete flash.info;
		}

		if (consumedFlashes.size > 50) {
			const entries = Array.from(consumedFlashes);
			for (const key of entries.slice(0, 25)) {
				consumedFlashes.delete(key);
			}
		}
	});
}
