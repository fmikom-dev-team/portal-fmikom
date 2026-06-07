export function formatTime(iso: string) {
	return new Date(iso).toLocaleTimeString("id-ID", {
		hour: "2-digit",
		minute: "2-digit",
	});
}

export function formatMessageDate(iso: string) {
	try {
		const date = new Date(iso);
		const now = new Date();

		const dateDay = new Date(
			date.getFullYear(),
			date.getMonth(),
			date.getDate(),
		);
		const nowDay = new Date(now.getFullYear(), now.getMonth(), now.getDate());

		const diffTime = nowDay.getTime() - dateDay.getTime();
		const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

		if (diffDays === 0) return "Hari Ini";
		if (diffDays === 1) return "Kemarin";
		if (diffDays > 1 && diffDays < 7) {
			const days = [
				"Minggu",
				"Senin",
				"Selasa",
				"Rabu",
				"Kamis",
				"Jumat",
				"Sabtu",
			];
			return days[date.getDay()];
		}

		const months = [
			"Jan",
			"Feb",
			"Mar",
			"Apr",
			"Mei",
			"Jun",
			"Jul",
			"Agu",
			"Sep",
			"Okt",
			"Nov",
			"Des",
		];
		const day = date.getDate();
		const month = months[date.getMonth()];
		const year = date.getFullYear();
		return `${day} ${month} ${year}`;
	} catch (e) {
		return "";
	}
}

export function formatConversationTime(timestamp: string | null) {
	if (!timestamp) return "";
	try {
		const date = new Date(timestamp);
		if (Number.isNaN(date.getTime())) return timestamp; // fallback

		const now = new Date();
		const diffMs = now.getTime() - date.getTime();
		const diffMins = Math.floor(diffMs / 60000);

		if (diffMins < 1) return "Baru saja";

		const dateDay = new Date(
			date.getFullYear(),
			date.getMonth(),
			date.getDate(),
		);
		const nowDay = new Date(now.getFullYear(), now.getMonth(), now.getDate());

		const diffTime = nowDay.getTime() - dateDay.getTime();
		const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

		if (diffDays === 0) {
			const hours = String(date.getHours()).padStart(2, "0");
			const mins = String(date.getMinutes()).padStart(2, "0");
			return `${hours}.${mins}`;
		}
		if (diffDays === 1) return "Kemarin";
		if (diffDays > 1 && diffDays < 7) {
			const days = [
				"Minggu",
				"Senin",
				"Selasa",
				"Rabu",
				"Kamis",
				"Jumat",
				"Sabtu",
			];
			return days[date.getDay()];
		}

		const day = String(date.getDate()).padStart(2, "0");
		const month = String(date.getMonth() + 1).padStart(2, "0");
		const year = String(date.getFullYear()).slice(-2);
		return `${day}/${month}/${year}`;
	} catch (e) {
		return timestamp;
	}
}

export function formatLastSeen(iso: string) {
	try {
		const date = new Date(iso);
		const now = new Date();
		const diffMs = now.getTime() - date.getTime();
		const diffMin = Math.floor(diffMs / 60000);
		if (diffMin < 1) return "baru saja";
		if (diffMin < 60) return `${diffMin} menit yang lalu`;
		const diffHrs = Math.floor(diffMin / 60);
		if (diffHrs < 24) return `${diffHrs} jam yang lalu`;
		const diffDays = Math.floor(diffHrs / 24);
		if (diffDays === 1) return "kemarin";
		return `${diffDays} hari yang lalu`;
	} catch (e) {
		return "";
	}
}

export function avatarUrl(path: string | null) {
	if (!path) return null;
	if (path.startsWith("http")) return path;
	return `/storage/${path}`;
}

export function isVerifiedUser(user: any): boolean {
	if (!user) return false;
	return true; // Semua pengguna portal mendapatkan lencana terverifikasi
}

export function isWithin20Minutes(createdAt: string): boolean {
	const diff = (Date.now() - new Date(createdAt).getTime()) / 60000;
	return diff <= 20;
}
