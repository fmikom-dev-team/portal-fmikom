export type UseInitialsReturn = {
	getInitials: (fullName?: string) => string;
	getInitialsAvatar: (fullName?: string) => string;
};

export function getInitials(fullName?: string): string {
	if (!fullName) {
		return "";
	}

	const names = fullName.trim().split(" ");

	if (names.length === 0) {
		return "";
	}

	if (names.length === 1) {
		return names[0].charAt(0).toUpperCase();
	}

	return `${names[0].charAt(0)}${names[names.length - 1].charAt(0)}`.toUpperCase();
}

export function getInitialsAvatar(fullName?: string): string {
	const initials = getInitials(fullName) || "?";
	const name = fullName || "User";

	// Generate a beautiful, stable HSL background color based on name hash
	let hash = 0;
	for (let i = 0; i < name.length; i++) {
		hash = name.charCodeAt(i) + ((hash << 5) - hash);
	}
	const hue = Math.abs(hash) % 360;
	// Use 65% saturation and 48% lightness for a vibrant premium background
	const color = `hsl(${hue}, 65%, 48%)`;

	const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100"><rect width="100" height="100" fill="${color}"/><text x="50%" y="52%" font-size="38" font-family="'Outfit', 'Inter', system-ui, sans-serif" font-weight="700" fill="#ffffff" dominant-baseline="middle" text-anchor="middle">${initials}</text></svg>`;

	return `data:image/svg+xml;utf8,${encodeURIComponent(svg)}`;
}

export function useInitials(): UseInitialsReturn {
	return { getInitials, getInitialsAvatar };
}
