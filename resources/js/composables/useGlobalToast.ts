import { toast as sonnerToast } from "vue-sonner";

let lastToastMessage = "";
let lastToastTime = 0;

/**
 * Global Deduplicated Toast Dispatcher
 * Prevents duplicate toasts when both client JS and Inertia flash props fire for the same action.
 */
export function showToast(
	message: string,
	type: "success" | "error" | "info" | "warning" = "success",
	duration = 3500,
) {
	if (!message) return;
	const now = Date.now();

	// Deduplicate identical toast messages fired within 1500ms
	if (message === lastToastMessage && now - lastToastTime < 1500) {
		return;
	}

	lastToastMessage = message;
	lastToastTime = now;

	if (type === "error") {
		sonnerToast.error(message, { duration });
	} else if (type === "warning") {
		sonnerToast.warning(message, { duration });
	} else if (type === "info") {
		sonnerToast.info(message, { duration });
	} else {
		sonnerToast.success(message, { duration });
	}
}
