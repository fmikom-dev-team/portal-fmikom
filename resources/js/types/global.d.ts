import type { Auth } from "@/types/auth";

// Extend ImportMeta interface for Vite...
declare module "vite/client" {
	interface ImportMetaEnv {
		readonly VITE_APP_NAME: string;
		[key: string]: string | boolean | undefined;
	}

	interface ImportMeta {
		readonly env: ImportMetaEnv;
		readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
	}
}

declare module "@inertiajs/core" {
	export interface InertiaConfig {
		sharedPageProps: {
			name: string;
			auth: Auth;
			sidebarOpen: boolean;
			[key: string]: unknown;
		};
	}

	export interface PageProps {
		auth?: {
			user?: {
				id: number;
				name: string;
				email: string;
				avatar?: string | null;
				pagi_username?: string | null;
				foto_path?: string | null;
				location?: string | null;
				user_type?: string | null;
				following?: number[];
				metadata?: {
					following?: number[];
					pagi_work_theme?: string | null;
					pagi_work_palette_index?: number;
					[key: string]: any;
				};
			};
		};
		context?: {
			active_role?: string;
		};
		recent_notifications?: any[];
		[key: string]: any;
	}
}

declare module "vue" {
	interface ComponentCustomProperties {
		$inertia: typeof Router;
		$page: Page;
		$headManager: ReturnType<typeof createHeadManager>;
	}
}
