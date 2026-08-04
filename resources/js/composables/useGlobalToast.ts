import { toast as sonnerToast } from "vue-sonner";

let lastToastMessage = "";
let lastToastTime = 0;
let lastToastType = "";

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

	// 1. Exact match within 3 seconds
	if (normalizedMsg === lastToastMessage && now - lastToastTime < 3000) {
		return;
	}

	// 2. Partial / Overlap match within 2.5 seconds (e.g. "Undangan berhasil dibatalkan" vs "Undangan berhasil dibatalkan dan dihapus")
	if (now - lastToastTime < 2500) {
		const isSubstring = normalizedMsg.includes(lastToastMessage) || lastToastMessage.includes(normalizedMsg);
		const sharedPrefix = normalizedMsg.slice(0, 15) === lastToastMessage.slice(0, 15);
		if (isSubstring || (sharedPrefix && sharedPrefix.length >= 10)) {
			return;
		}

		// 3. Rapid dual-fire prevention for same toast type within 600ms
		if (lastToastType === type && now - lastToastTime < 600) {
			return;
		}
	}

	lastToastMessage = normalizedMsg;
	lastToastTime = now;
	lastToastType = type;

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
