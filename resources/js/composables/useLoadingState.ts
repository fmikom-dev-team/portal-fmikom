import { readonly, ref } from "vue";

const isLoading = ref(false);
const loadingType = ref<
	| "Dashboard"
	| "Table"
	| "Form"
	| "Portfolio"
	| "CVBuilder"
	| "Chat"
	| "News"
	| "UserProfile"
	| null
>(null);

const pathMapping: Array<{ pattern: RegExp; type: typeof loadingType.value }> =
	[
		{ pattern: /^\/pagi\/gallery/i, type: "Portfolio" },
		{ pattern: /^\/pagi\/people/i, type: "Portfolio" },
		{ pattern: /^\/pagi$/i, type: "Portfolio" },
		{ pattern: /^\/pagi\/explore/i, type: "Portfolio" },
		{ pattern: /^\/pagi\/cv\/.*\/edit/i, type: "CVBuilder" },
		{ pattern: /^\/pagi\/messages/i, type: "Chat" },
		{ pattern: /^\/pagi\/profile/i, type: "UserProfile" },
		{ pattern: /^\/pagi\/admin\/dashboard/i, type: "Dashboard" },
		{ pattern: /^\/pagi\/admin\/analytics/i, type: "Dashboard" },
		{ pattern: /^\/pagi\/admin\/settings/i, type: "Form" },
		{ pattern: /^\/pagi\/admin/i, type: "Table" },
		{ pattern: /^\/workos\/users/i, type: "Table" },
		{ pattern: /^\/workos\/audit-logs/i, type: "Table" },
		{ pattern: /^\/workos\/roles/i, type: "Table" },
		{ pattern: /^\/portal-admin\/settings/i, type: "Form" },
		{ pattern: /^\/portal-admin\/appearance/i, type: "Form" },
		{
			pattern:
				/^\/portal-admin\/(posts|categories|media|pages|comments|academic-calendars|events|menus)/i,
			type: "Table",
		},
		{ pattern: /^\/portal-admin/i, type: "Dashboard" },
		{ pattern: /^\/dashboard/i, type: "Dashboard" },
		{ pattern: /^\/portal/i, type: "Dashboard" },
		{ pattern: /^\/settings/i, type: "Form" },
	];

export function useLoadingState() {
	function startLoading(path: string) {
		const match = pathMapping.find((m) => m.pattern.test(path));
		loadingType.value = match ? match.type : "Form";
		isLoading.value = true;
	}

	function stopLoading() {
		isLoading.value = false;
		loadingType.value = null;
	}

	return {
		isLoading: readonly(isLoading),
		loadingType: readonly(loadingType),
		startLoading,
		stopLoading,
	};
}
