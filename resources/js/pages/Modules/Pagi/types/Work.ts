export interface PagiWork {
	id: number;
	user_id: number;
	title: string;
	image: string;
	content: any;
	created_at: string;
	likes: number;
	liked: boolean;
	comments: any[];
	views: number;
	is_published: boolean;
	tools_used: string[] | null;
	description: string | null;
	category: string | null;
	tags: string[];
	resolved_collaborators: any[];
	user: {
		id: number;
		name: string;
		pagi_username: string | null;
		avatar: string | null;
		location: string | null;
	} | null;
}
