export interface PagiComment {
	id: string | number;
	user_id: number;
	name: string;
	avatar: string | null;
	content: string;
	created_at: string;
	likes: number;
	liked: boolean;
	replies?: PagiReply[];
}

export interface PagiReply {
	id: string | number;
	user_id: number;
	name: string;
	avatar: string | null;
	content: string;
	created_at: string;
	likes: number;
	liked: boolean;
}
