export type User = {
	id: number;
	name: string;
	email: string;
	avatar?: string;
	email_verified_at: string | null;
	created_at: string;
	updated_at: string;
	role_title?: string | null;
	bio?: string | null;
	location?: string | null;
	website?: string | null;
	twitter?: string | null;
	linkedin?: string | null;
	github?: string | null;
	foto_path?: string | null;
	[key: string]: unknown;
};

export type Auth = {
	user: User;
};

export type TwoFactorConfigContent = {
	title: string;
	description: string;
	buttonText: string;
};
