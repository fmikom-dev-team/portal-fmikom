<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { Check, Loader2, Search, Shield } from "lucide-vue-next";
import { computed, ref, watch } from "vue";
import { toast } from "../../composables/useWorkOs";

const props = defineProps<{
	roles: any[];
	permissions: any[];
	searchQuery?: string;
}>();

const search = ref("");
watch(() => props.searchQuery, (val) => {
	search.value = val || "";
});
const savingRoleIds = ref<number[]>([]);

// Group permissions by their group attribute
const groupedPermissions = computed(() => {
	const groups: Record<string, any[]> = {};

	// Filter permissions based on search
	const filtered = props.permissions.filter((p) => {
		if (!search.value.trim()) return true;
		const q = search.value.toLowerCase();
		return (
			p.name.toLowerCase().includes(q) ||
			p.slug.toLowerCase().includes(q) ||
			p.group.toLowerCase().includes(q)
		);
	});

	filtered.forEach((p) => {
		const gName = p.group || "general";
		if (!groups[gName]) {
			groups[gName] = [];
		}
		groups[gName].push(p);
	});

	return groups;
});

// Check if a role has a specific permission
function hasPermission(role: any, permissionId: number): boolean {
	return (role.permissions || []).some((p: any) => p.id === permissionId);
}

// Toggle permission on a role and save immediately
function togglePermission(role: any, permissionId: number) {
	if (savingRoleIds.value.includes(role.id)) return;

	const currentIds = (role.permissions || []).map((p: any) => p.id);
	let newIds: number[];

	if (currentIds.includes(permissionId)) {
		newIds = currentIds.filter((id: number) => id !== permissionId);
	} else {
		newIds = [...currentIds, permissionId];
	}

	savingRoleIds.value.push(role.id);

	router.patch(
		`/workos/roles/${role.id}/permissions`,
		{
			permission_ids: newIds,
		},
		{
			preserveScroll: true,
			onSuccess: () => {
				toast(
					`Permissions for role '${role.nama}' updated successfully.`,
					"success",
				);
			},
			onError: () => {
				toast(`Failed to update permissions for role '${role.nama}'.`, "error");
			},
			onFinish: () => {
				savingRoleIds.value = savingRoleIds.value.filter(
					(id) => id !== role.id,
				);
			},
		},
	);
}
</script>

<template>
    <div style="font-family: var(--wos-font)" class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-[20px] font-semibold text-gray-900 dark:text-zinc-100 tracking-tight">Access Control Matrix</h1>
                <p class="text-[13px] text-gray-500 dark:text-zinc-400 mt-0.5">Map permissions directly to roles using an interactive, real-time matrix.</p>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="flex items-center gap-2">
            <div class="relative flex-1 max-w-sm">
                <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-zinc-500" />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search permissions by name or group..."
                    class="w-full h-[34px] pl-8 pr-3 text-[13px] border border-gray-200 dark:border-zinc-700 rounded-md focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:ring-zinc-700 transition-colors placeholder:text-gray-400 text-gray-900 dark:text-zinc-100 bg-white dark:bg-zinc-900"
                />
            </div>
        </div>

        <!-- Matrix Table -->
        <div v-if="permissions.length && roles.length" class="rounded-xl border border-gray-200 dark:border-zinc-700 overflow-x-auto bg-white dark:bg-zinc-900 shadow-sm dark:shadow-none">
            <table class="w-full text-left whitespace-nowrap border-collapse">
                <caption class="sr-only">Matriks Kontrol Akses Peran dan Izin</caption>
                <thead>
                    <tr class="bg-[#f9fafb] dark:bg-zinc-900 border-b border-gray-200 dark:border-zinc-700">
                        <th class="px-4 py-3 text-[12px] font-semibold text-gray-800 dark:text-zinc-200 w-[320px] sticky left-0 bg-[#f9fafb] dark:bg-zinc-900 border-r border-gray-200 dark:border-zinc-700">
                            Permission / Action
                        </th>
                        <th 
                            v-for="role in roles" 
                            :key="role.id" 
                            class="px-4 py-3 text-[12px] font-semibold text-gray-800 dark:text-zinc-200 text-center min-w-[120px] border-r border-gray-100 dark:border-zinc-800 last:border-r-0"
                        >
                            <div class="flex items-center justify-center gap-1.5">
                                <span>{{ role.nama }}</span>
                                <Loader2 v-if="savingRoleIds.includes(role.id)" class="w-3 h-3 text-gray-500 dark:text-zinc-400 animate-spin" />
                            </div>
                            <span class="block mt-0.5 text-[10px] font-mono font-normal text-gray-400 dark:text-zinc-500 uppercase tracking-tight">{{ role.slug }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <template v-for="(perms, groupName) in groupedPermissions" :key="groupName">
                        <!-- Group Category Header Row -->
                        <tr class="bg-gray-50 dark:bg-zinc-900/70 border-b border-gray-200 dark:border-zinc-700">
                            <td 
                                :colspan="roles.length + 1" 
                                class="px-4 py-2 text-[11px] font-bold text-gray-500 dark:text-zinc-400 uppercase tracking-widest bg-gray-50 dark:bg-zinc-900/70 sticky left-0"
                            >
                                {{ groupName }}
                            </td>
                        </tr>

                        <!-- Permission Rows -->
                        <tr 
                            v-for="perm in perms" 
                            :key="perm.id" 
                            class="border-b border-gray-100 dark:border-zinc-800 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 dark:bg-zinc-900/40 last:border-b-0 transition-colors"
                        >
                            <!-- Permission info (sticky on scroll) -->
                            <td class="px-4 py-3 sticky left-0 bg-white dark:bg-zinc-900 border-r border-gray-200 dark:border-zinc-700 shadow-[2px_0_5px_rgba(0,0,0,0.02)] min-w-[320px] whitespace-normal">
                                <div class="pr-2">
                                    <p class="text-[13px] font-semibold text-gray-900 dark:text-zinc-100 leading-normal">{{ perm.name }}</p>
                                    <p class="font-mono text-[10.5px] text-gray-400 dark:text-zinc-500 mt-0.5">{{ perm.slug }}</p>
                                    <p v-if="perm.description" class="text-[11.5px] text-gray-400 dark:text-zinc-500 mt-1 leading-normal">{{ perm.description }}</p>
                                </div>
                            </td>

                            <!-- Role Checkbox Cell -->
                            <td 
                                v-for="role in roles" 
                                :key="role.id" 
                                class="p-3 text-center border-r border-gray-100 dark:border-zinc-800 last:border-r-0 align-middle"
                            >
                                <div class="flex items-center justify-center">
                                    <button
                                        @click="togglePermission(role, perm.id)"
                                        :disabled="savingRoleIds.includes(role.id)"
                                        :class="[
                                            'w-5 h-5 rounded border flex items-center justify-center transition-all focus:outline-none focus:ring-1 focus:ring-gray-900 dark:ring-zinc-700',
                                            hasPermission(role, perm.id)
                                                ? 'bg-gray-950 border-gray-950 text-white'
                                                : 'bg-white dark:bg-zinc-900 border-gray-300 dark:border-zinc-700 hover:border-gray-400 text-transparent',
                                            savingRoleIds.includes(role.id) ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                                        ]"
                                    >
                                        <Check class="w-3.5 h-3.5" stroke-width="3" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div v-else class="rounded-xl bg-[#f9fafb] dark:bg-zinc-900 border border-dashed border-gray-200 dark:border-zinc-700 p-12 flex flex-col items-center justify-center text-center gap-2">
            <Shield class="w-8 h-8 text-gray-300" />
            <p class="text-[13px] font-medium text-gray-800 dark:text-zinc-200">No results found</p>
            <p class="text-[11.5px] text-gray-400 dark:text-zinc-500">Try adjusting your search criteria or add new permissions/roles.</p>
        </div>
    </div>
</template>