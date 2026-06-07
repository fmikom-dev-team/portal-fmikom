export interface AuthUser {
	id: number;
	name: string;
	foto_path: string | null;
	metadata?: any;
}

export interface Conversation {
	id: number;
	name: string;
	foto_path: string | null;
	last_message: string | null;
	last_message_at: string | null;
	last_message_sender_id?: number | null;
	last_message_read_at?: string | null;
	last_message_sending?: boolean;
	last_message_is_deleted?: boolean;
	last_message_is_deleted_for_me?: boolean;
	last_message_id?: number | null;
	unread_count: number;
	conversation_id: string;
	last_seen_at?: string | null;
	metadata?: any;
	is_blocked_by_me?: boolean;
	is_pinned?: boolean;
	is_archived?: boolean;
	is_manual_unread?: boolean;
	is_followed_placeholder?: boolean;
	formatted_time?: string;
}

export interface Message {
	id: number;
	sender_id: number;
	body: string;
	is_deleted?: boolean;
	is_deleted_for_me?: boolean;
	edited_at?: string | null;
	parent_id?: number | null;
	parent?: {
		id: number;
		body: string;
		sender: { id: number; name: string };
	} | null;
	read_at: string | null;
	created_at: string;
	sending?: boolean;
	reactions?: Record<string, number[]>;
	sender: { id: number; name: string; foto_path: string | null };
}

export interface Contact {
	id: number;
	name: string;
	foto_path: string | null;
}
