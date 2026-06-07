export interface PagiNotificationItem {
	id: string;
	type: string;
	title: string;
	message: string;
	avatar: string | null;
	href: string;
	unread: boolean;
	time: string;
	created_at: string;
	sender_id: number | null;
	portfolio_id: number | null;
}

export interface PagiNotificationGroup {
	group: string;
	items: PagiNotificationItem[];
}
