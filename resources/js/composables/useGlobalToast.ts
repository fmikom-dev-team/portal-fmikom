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

	// Normalize message (strip trailing dots/whitespace and lowercase) for 100% accurate deduplication
	const normalizedMsg = message.trim().replace(/\.+$/, "").toLowerCase();

	if (normalizedMsg === lastToastMessage && now - lastToastTime < 2000) {
		return;
	}

	lastToastMessage = normalizedMsg;
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
