import { toast as sonnerToast } from "vue-sonner";

let lastToastMessage = "";
let lastToastTime = 0;
let lastToastType = "";

const defaultTitles: Record<string, string> = {
	success: "Berhasil Disimpan",
	error: "Pemberitahuan",
	warning: "Peringatan",
	info: "Informasi",
};

/**
 * Global Deduplicated Modern Toast Dispatcher
 * Prevents duplicate toasts when both client JS and Inertia flash props fire for the same action.
 */
export function showToast(
	message: string,
	type: "success" | "error" | "info" | "warning" = "success",
	duration = 3500,
	customTitle?: string,
) {
	if (!message) return;
	const now = Date.now();

	// Normalize message (strip trailing dots/whitespace and lowercase) for 100% accurate deduplication
	const normalizedMsg = message.trim().replace(/\.+$/, "").toLowerCase();

	// 1. Exact match within 8 seconds (prevents repeated toast during rapid tab switches / page reloads)
	if (normalizedMsg === lastToastMessage && now - lastToastTime < 8000) {
		return;
	}

	// 2. Partial / Overlap match within 5 seconds
	if (now - lastToastTime < 5000) {
		const isSubstring =
			normalizedMsg.includes(lastToastMessage) ||
			lastToastMessage.includes(normalizedMsg);
		const sharedPrefix =
			normalizedMsg.slice(0, 15) === lastToastMessage.slice(0, 15);
		if (isSubstring || (sharedPrefix && sharedPrefix.length >= 10)) {
			return;
		}

		// 3. Rapid dual-fire prevention for same toast type within 1200ms
		if (lastToastType === type && now - lastToastTime < 1200) {
			return;
		}
	}

	lastToastMessage = normalizedMsg;
	lastToastTime = now;
	lastToastType = type;

	const title = customTitle || defaultTitles[type] || "Pemberitahuan";

	if (type === "error") {
		sonnerToast.error(title, { description: message, duration });
	} else if (type === "warning") {
		sonnerToast.warning(title, { description: message, duration });
	} else if (type === "info") {
		sonnerToast.info(title, { description: message, duration });
	} else {
		sonnerToast.success(title, { description: message, duration });
	}
}

