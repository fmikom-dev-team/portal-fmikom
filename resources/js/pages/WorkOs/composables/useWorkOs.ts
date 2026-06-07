import { reactive } from "vue";

// ─── TOAST STATE ───────────────────────────────────────────────────────────────
export const toastState = reactive({
	show: false,
	msg: "",
	type: "success" as "success" | "error" | "info" | "warning",
});
let _toastTimer: ReturnType<typeof setTimeout> | null = null;

export function toast(
	msg: string,
	type: "success" | "error" | "info" | "warning" = "success",
	duration = 3500,
) {
	if (_toastTimer) clearTimeout(_toastTimer);
	Object.assign(toastState, { show: true, msg, type });
	_toastTimer = setTimeout(() => {
		toastState.show = false;
	}, duration);
}

// ─── USER TYPE BADGES ──────────────────────────────────────────────────────────
export const typeBadge: Record<string, string> = {
	mahasiswa: "bg-blue-50 text-blue-700 ring-blue-200",
	alumni: "bg-violet-50 text-violet-700 ring-violet-200",
	dosen: "bg-amber-50 text-amber-700 ring-amber-200",
	mitra: "bg-emerald-50 text-emerald-700 ring-emerald-200",
	staff: "bg-slate-100 text-slate-700 ring-slate-200",
};

export function typeLabel(type: string): string {
	const map: Record<string, string> = {
		mahasiswa: "Mahasiswa",
		alumni: "Alumni",
		dosen: "Dosen",
		mitra: "Mitra",
		staff: "Staff",
	};
	return map[type] ?? type;
}

// ─── STATUS UTILITIES ──────────────────────────────────────────────────────────
export function statusDot(u: any): string {
	if (u.status_approval === "approved" && u.is_active) return "bg-emerald-500";
	if (u.status_approval === "rejected") return "bg-red-400";
	if (u.status_approval === "pending") return "bg-amber-400";
	return "bg-slate-300";
}

export function statusLabel(u: any): string {
	if (u.status_approval === "approved" && u.is_active) return "Active";
	if (u.status_approval === "rejected") return "Rejected";
	if (u.status_approval === "pending") return "Pending";
	return "Inactive";
}

export function statusBadgeClass(u: any): string {
	if (u.status_approval === "approved" && u.is_active)
		return "bg-emerald-50 text-emerald-700 ring-emerald-200";
	if (u.status_approval === "rejected")
		return "bg-red-50 text-red-700 ring-red-200";
	if (u.status_approval === "pending")
		return "bg-amber-50 text-amber-700 ring-amber-200";
	return "bg-slate-100 text-slate-600 ring-slate-200";
}

// alias untuk backward compat
export const statusClass = statusBadgeClass;

// ─── FORMATTING UTILS ─────────────────────────────────────────────────────────
export function formatDate(dateStr: string | null | undefined): string {
	if (!dateStr) return "—";
	try {
		const d = new Date(dateStr);
		return d.toLocaleDateString("id-ID", {
			day: "2-digit",
			month: "short",
			year: "numeric",
		});
	} catch {
		return dateStr;
	}
}

export function formatRelativeTime(dateStr: string | null | undefined): string {
	if (!dateStr) return "—";
	const now = Date.now();
	const then = new Date(dateStr).getTime();
	const diff = now - then;
	const sec = Math.floor(diff / 1000);
	const min = Math.floor(sec / 60);
	const hr = Math.floor(min / 60);
	const day = Math.floor(hr / 24);
	if (day > 30) return formatDate(dateStr);
	if (day > 0) return `${day}d ago`;
	if (hr > 0) return `${hr}h ago`;
	if (min > 0) return `${min}m ago`;
	return "Just now";
}

export function initialsOf(name: string | null | undefined): string {
	if (!name) return "?";
	return name
		.split(" ")
		.slice(0, 2)
		.map((w) => w[0]?.toUpperCase() ?? "")
		.join("");
}

// ─── CLIPBOARD ────────────────────────────────────────────────────────────────
export async function copyToClipboard(text: string): Promise<void> {
	try {
		await navigator.clipboard.writeText(text);
		toast("Copied to clipboard", "success", 1800);
	} catch {
		toast("Failed to copy", "error");
	}
}

// ─── PASSWORD GENERATOR ───────────────────────────────────────────────────────
export function generatePassword(length = 16): string {
	const chars =
		"ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#$%^&*";
	return Array.from(
		{ length },
		() => chars[Math.floor(Math.random() * chars.length)],
	).join("");
}

// ─── SLUG GENERATOR ──────────────────────────────────────────────────────────
export function toSlug(str: string): string {
	return str
		.toLowerCase()
		.replace(/\s+/g, "-")
		.replace(/[^a-z0-9-]/g, "");
}
