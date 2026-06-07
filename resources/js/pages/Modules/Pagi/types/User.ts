export interface PagiUser {
	id: number;
	name: string;
	email: string;
	pagi_username: string | null;
	role_title: string | null;
	pagi_role: string | null;
	user_type: string;
	bio: string | null;
	location: string | null;
	website: string | null;
	twitter: string | null;
	linkedin: string | null;
	github: string | null;
	instagram: string | null;
	foto_path: string | null;
	banner_path: string | null;
	tanggal_lahir: string | null;
	skills: string[] | Array<{ name: string; percentage: number }>;
	timezone: string | null;
	timezone_extended: string | null;
	languages: Array<{ language: string; proficiency: string }>;
	followers_count: number;
	following_count: number;
}
