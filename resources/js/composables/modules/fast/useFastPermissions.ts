import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";

type FastPageProps = {
	fast_permissions?: string[];
};

export function useFastPermissions() {
	const page = usePage<FastPageProps>();

	const permissions = computed(() => page.props.fast_permissions ?? []);

	function can(permission: string) {
		return (
			permissions.value.includes("*") || permissions.value.includes(permission)
		);
	}

	function canAny(permissionList: string[]) {
		return permissionList.some((permission) => can(permission));
	}

	return {
		permissions,
		can,
		canAny,
	};
}
