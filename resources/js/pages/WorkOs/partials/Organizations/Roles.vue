<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { computed, onMounted, onUnmounted, reactive, ref, watch } from "vue";
import AppModal from "../../components/ui/AppModal.vue";
import { toast } from "../../composables/useWorkOs";

const props = defineProps<{
	roles?: any[];
	organization?: any;
}>();

const activeMenu = ref<number | null>(null);
function toggleMenu(id: number, e: Event) {
	e.stopPropagation();
	activeMenu.value = activeMenu.value === id ? null : id;
}
function closeMenus() {
	activeMenu.value = null;
}
onMounted(() => document.addEventListener("click", closeMenus));
onUnmounted(() => document.removeEventListener("click", closeMenus));

const modal = reactive({
	priority: false,
	add: false,
	delete: false,
});

const selectedRoleId = ref("");
const deletingRole = ref<any>(null);
const isSubmitting = ref(false);
const orgRoles = ref<any[]>([]);
const search = ref("");

function initRoles() {
	const rawRoles = [...(props.organization?.roles || [])];
	const priorities = props.organization?.settings_data?.role_priorities || [];
	if (priorities.length > 0) {
		rawRoles.sort((a, b) => {
			const idxA = priorities.indexOf(a.id);
			const idxB = priorities.indexOf(b.id);
			if (idxA === -1 && idxB === -1) return 0;
			if (idxA === -1) return 1;
			if (idxB === -1) return -1;
			return idxA - idxB;
		});
	}
	orgRoles.value = rawRoles;
}

watch(
	() => props.organization,
	() => {
		initRoles();
	},
	{ deep: true, immediate: true },
);

const filteredRoles = computed(() => {
	return orgRoles.value.filter(
		(r) =>
			!search.value ||
			(r.nama || r.name || "")
				.toLowerCase()
				.includes(search.value.toLowerCase()) ||
			(r.slug || "").toLowerCase().includes(search.value.toLowerCase()),
	);
});

const localRoles = ref<any[]>([]);
const draggedItem = ref<number | null>(null);

function openPriorityModal() {
	localRoles.value = [...orgRoles.value];
	modal.priority = true;
}
function closePriorityModal() {
	modal.priority = false;
}
function savePriority() {
	const roleIds = localRoles.value.map((r) => r.id);
	isSubmitting.value = true;
	router.post(
		`/workos/modules/${props.organization.id}/settings-data`,
		{ role_priorities: roleIds },
		{
			preserveScroll: true,
			onSuccess: () => {
				orgRoles.value = [...localRoles.value];
				modal.priority = false;
				toast("Role priorities updated.", "success");
			},
			onError: () => toast("Failed to update role priorities.", "error"),
			onFinish: () => {
				isSubmitting.value = false;
			},
		},
	);
}

// ── Admin Portal Role Assignment State ──
const roleMappings = computed(() => props.organization?.settings_data?.role_mappings || []);
const showRoleAssignmentModal = ref(false);
const tempMappings = ref<any[]>([]);

function openRoleAssignmentModal() {
	tempMappings.value = [...roleMappings.value].map((m) => ({ ...m }));
	showRoleAssignmentModal.value = true;
}

function addMappingRow() {
	tempMappings.value.push({ id: Date.now(), group: "", role: "Member" });
}

function removeMappingRow(index: number) {
	tempMappings.value.splice(index, 1);
}

function saveRoleAssignment() {
	const validMappings = tempMappings.value.filter((m) => m.group.trim() !== "");
	isSubmitting.value = true;
	router.post(
		`/workos/modules/${props.organization.id}/settings-data`,
		{ role_mappings: validMappings },
		{
			preserveScroll: true,
			onSuccess: () => {
				showRoleAssignmentModal.value = false;
				toast("Role assignment mappings updated.", "success");
			},
			onError: () => toast("Failed to update role assignment.", "error"),
			onFinish: () => {
				isSubmitting.value = false;
			},
		},
	);
}

function clearRoleMappings() {
	if (confirm("Are you sure you want to clear all role mappings?")) {
		isSubmitting.value = true;
		router.post(
			`/workos/modules/${props.organization.id}/settings-data`,
			{ role_mappings: [] },
			{
				preserveScroll: true,
				onSuccess: () => {
					toast("Role assignment mappings cleared.", "success");
				},
				onError: () => toast("Failed to clear role assignment.", "error"),
				onFinish: () => {
					isSubmitting.value = false;
				},
			},
		);
	}
}
function onDragStart(index: number) {
	draggedItem.value = index;
}

function onDrop(index: number) {
	if (draggedItem.value === null) return;
	const item = localRoles.value.splice(draggedItem.value, 1)[0];
	localRoles.value.splice(index, 0, item);
	draggedItem.value = null;
}

function openAdd() {
	selectedRoleId.value = "";
	modal.add = true;
}

function submitAdd() {
	if (!selectedRoleId.value || !props.organization) return;
	isSubmitting.value = true;
	router.post(
		`/workos/modules/${props.organization.id}/roles`,
		{ role_id: selectedRoleId.value },
		{
			onSuccess: () => {
				modal.add = false;
				toast("Role added to organization.", "success");
			},
			onError: () => toast("Failed to add role.", "error"),
			onFinish: () => {
				isSubmitting.value = false;
			},
		},
	);
}

function openDelete(role: any) {
	deletingRole.value = role;
	modal.delete = true;
	activeMenu.value = null;
}

function confirmDelete() {
	if (!deletingRole.value || !props.organization) return;
	isSubmitting.value = true;
	router.delete(
		`/workos/modules/${props.organization.id}/roles/${deletingRole.value.id}`,
		{
			onSuccess: () => {
				modal.delete = false;
				toast("Role removed from organization.", "success");
			},
			onError: () => toast("Failed to remove role.", "error"),
			onFinish: () => {
				isSubmitting.value = false;
			},
		},
	);
}

const availableRoles = computed(() => {
	const existingIds = props.organization?.roles?.map((r: any) => r.id) || [];
	return (props.roles || []).filter((r: any) => !existingIds.includes(r.id));
});
</script>

<template>
    <div style="font-family: var(--wos-font)">
        <!-- Toolbar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 w-full md:w-auto">
                <!-- Search -->
                <div class="relative w-full sm:w-auto">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#9ca3af] dark:text-zinc-650" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" />
                    </svg>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search roles..."
                        class="w-full sm:w-[240px] h-[34px] pl-8 pr-3 text-[13px] border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors placeholder:text-[#9ca3af] dark:placeholder:text-zinc-550 text-[#111827] dark:text-zinc-100 bg-white dark:bg-zinc-900"
                    />
                </div>
                <!-- Role type filter -->
                <button class="flex items-center justify-center gap-1.5 h-[34px] px-3 border border-[#d1d5db] dark:border-zinc-700 rounded-md text-[13px] text-[#4b5563] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 w-full sm:w-auto cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-[#9ca3af] dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Role type
                </button>
            </div>
            
            <div class="flex items-center gap-2 w-full md:w-auto">
                <button
                    class="h-[34px] px-3.5 border border-[#d1d5db] dark:border-zinc-700 rounded-md text-[13px] font-semibold text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 flex-1 md:flex-none justify-center items-center flex cursor-pointer"
                    @click="openPriorityModal"
                >
                    Edit priority
                </button>
                <button 
                    class="h-[34px] px-3.5 bg-[#2563eb] text-white rounded-md text-[13px] font-semibold hover:bg-[#1d4ed8] transition-colors flex-1 md:flex-none justify-center items-center flex cursor-pointer"
                    @click="openAdd"
                >
                    Add role
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-xl overflow-x-auto bg-white dark:bg-zinc-900 ring-1 ring-gray-900/[0.04] dark:ring-white/[0.06] border dark:border-zinc-800">
            <table class="w-full text-left whitespace-nowrap">
                <caption class="sr-only">Associated Roles</caption>
                <thead>
                    <tr class="bg-[#f9fafb] dark:bg-zinc-800/60 border-b border-[#e5e7eb] dark:border-zinc-800">
                        <th class="px-4 py-3 text-[12px] font-semibold text-[#111827] dark:text-zinc-300">Name</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-[#111827] dark:text-zinc-300 w-[30%]">Slug</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-[#111827] dark:text-zinc-300 w-[40%]">Permissions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e5e7eb] dark:divide-zinc-800">
                    <tr v-if="!filteredRoles.length">
                        <td colspan="4" class="px-4 py-8 text-center text-[13px] text-[#6b7280] dark:text-zinc-500">
                            No roles associated with this organization.
                        </td>
                    </tr>
                    <tr
                        v-for="(role, index) in filteredRoles"
                        :key="role.id"
                        class="hover:bg-[#f9fafb] dark:bg-zinc-900 dark:hover:bg-zinc-800/40 transition-colors group"
                    >
                        <td class="px-4 py-3">
                            <div class="flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-[#9ca3af] dark:text-zinc-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <div>
                                    <p class="text-[13px] font-semibold text-[#111827] dark:text-zinc-100">{{ role.nama || role.name }}</p>
                                    <p class="text-[12px] text-[#6b7280] dark:text-zinc-400">{{ role.deskripsi || role.description || 'Custom role' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11.5px] font-mono font-medium text-[#4b5563] dark:text-zinc-300 bg-[#f3f4f6] dark:bg-zinc-800 border dark:border-zinc-700">
                                {{ role.slug }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-between">
                                <span :class="role.permissions_count === 0 ? 'text-[13px] text-[#9ca3af] dark:text-zinc-500' : 'text-[13px] text-[#4b5563] dark:text-zinc-400 underline decoration-dashed underline-offset-4'">
                                    {{ role.permissions_count === 0 ? 'None' : `${role.permissions_count} permissions` }}
                                </span>
                                
                                <div class="flex items-center gap-3 relative">
                                    <span v-if="role.id === orgRoles[0]?.id" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-900/60 bg-blue-50 dark:bg-blue-950/40">
                                        Default
                                    </span>
                                    <button 
                                        class="p-1 rounded text-[#9ca3af] dark:text-zinc-600 hover:text-[#374151] dark:hover:text-zinc-300 hover:bg-[#e5e7eb] dark:hover:bg-zinc-800 opacity-0 group-hover:opacity-100 transition-all"
                                        @click.stop="toggleMenu(role.id, $event)"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                                        </svg>
                                    </button>
                                    
                                    <div v-if="activeMenu === role.id" class="absolute right-0 top-full mt-1 w-36 bg-white dark:bg-zinc-900 border border-[#e5e7eb] dark:border-zinc-800 rounded-lg shadow-lg z-50 py-1 dark:shadow-none">
                                        <button class="w-full flex items-center gap-2 px-3 py-1.5 text-[13px] text-[#ef4444] dark:text-red-400 hover:bg-[#fef2f2] dark:hover:bg-red-950/40 text-left transition-colors" @click="openDelete(role)">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Admin Portal -->
        <div class="mt-6 mb-8">
            <h2 class="text-[15px] font-semibold text-[#111827] dark:text-zinc-100 mb-3">Admin Portal</h2>
            <div class="rounded-xl bg-white dark:bg-zinc-900 p-5 ring-1 ring-gray-900/[0.04] dark:ring-white/[0.06] border dark:border-zinc-800">
                <div class="flex flex-col sm:flex-row items-start justify-between gap-4">
                    <div class="flex-1 w-full">
                        <h3 class="text-[14px] font-semibold text-[#111827] dark:text-zinc-100 mb-1">Role assignment in Admin Portal</h3>
                        <p class="text-[12px] text-[#6b7280] dark:text-zinc-400 mb-3">Allow IT contacts to map user roles based on their identity provider groups.</p>
                        
                        <!-- Role Mappings Summary -->
                        <div v-if="roleMappings.length" class="mb-4 bg-gray-50 dark:bg-zinc-800/40 border border-gray-100 dark:border-zinc-800 p-3 rounded-md text-xs space-y-1.5">
                            <span class="block text-gray-500 dark:text-zinc-400 font-semibold mb-1">Mapped Roles:</span>
                            <div v-for="mapping in roleMappings" :key="mapping.id" class="flex flex-wrap items-center gap-1.5 text-gray-700 dark:text-zinc-300">
                                <span class="px-1.5 py-0.5 font-mono bg-gray-200 dark:bg-zinc-800 rounded text-gray-800 dark:text-zinc-300 text-[10px]">{{ mapping.group }}</span>
                                <span>mapped to</span>
                                <strong class="text-blue-600 dark:text-blue-400">{{ mapping.role }}</strong>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 w-full">
                            <button 
                                @click="openRoleAssignmentModal"
                                class="h-[30px] px-3 border border-[#d1d5db] dark:border-zinc-700 rounded-md text-[12px] font-medium text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 whitespace-nowrap cursor-pointer flex items-center justify-center shrink-0 flex-1 sm:flex-none"
                            >
                                Configure role assignment
                            </button>
                            <button 
                                @click="clearRoleMappings"
                                class="h-[30px] px-3 text-[12px] font-medium text-[#6b7280] dark:text-zinc-550 hover:text-red-600 transition-colors bg-transparent border-0 cursor-pointer whitespace-nowrap flex items-center justify-center shrink-0 flex-1 sm:flex-none"
                            >
                                Clear mappings
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 text-[13px] font-medium text-[#10b981] shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Enabled
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Priority Modal -->
        <AppModal
            :show="modal.priority"
            title="Edit priority"
            @close="closePriorityModal"
        >
            <template #description>
                When an organization member has different roles provisioned from different groups, the role with a higher priority will be used. Drag roles to change the priority, from highest to lowest.
            </template>
            
            <div class="rounded-xl divide-y divide-gray-100 dark:divide-zinc-800 ring-1 ring-gray-900/[0.04] dark:ring-white/[0.06] border dark:border-zinc-800">
                <div
                    v-for="(role, index) in localRoles"
                    :key="role.id"
                    draggable="true"
                    @dragstart="onDragStart(index)"
                    @dragover.prevent
                    @dragenter.prevent
                    @drop="onDrop(index)"
                    :class="['flex items-center justify-between p-3 bg-white dark:bg-zinc-900 hover:bg-[#f9fafb] dark:hover:bg-zinc-800/40 cursor-move transition-colors', draggedItem === index ? 'opacity-50' : '']"
                >
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#d1d5db] dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16" />
                        </svg>
                        <svg class="w-4 h-4 text-[#9ca3af] dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span class="text-[13.5px] font-medium text-[#374151] dark:text-zinc-300">{{ role.nama || role.name }}</span>
                    </div>
                    <span v-if="index === 0" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-900/60 bg-blue-50 dark:bg-blue-950/40">
                        Default
                    </span>
                </div>
            </div>

            <template #footer>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] dark:text-zinc-300 border border-[#d1d5db] dark:border-zinc-700 hover:bg-[#f3f4f6] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm dark:shadow-none"
                    @click="closePriorityModal"
                >
                    Cancel
                </button>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-[#2563eb] hover:bg-[#1d4ed8] transition-colors shadow-sm dark:shadow-none"
                    @click="savePriority"
                >
                    Save changes
                </button>
            </template>
        </AppModal>

        <!-- Add Role Modal -->
        <AppModal
            :show="modal.add"
            title="Add role to organization"
            @close="modal.add = false"
        >
            <div class="space-y-4">
                <div>
                    <label for="select_role" class="block text-[13px] font-semibold text-[#374151] dark:text-zinc-200 mb-1.5">Select Role</label>
                    <select
                        id="select_role"
                        v-model="selectedRoleId"
                        class="w-full h-9 px-3 text-[13px] border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] transition-colors text-[#111827] dark:text-zinc-100 bg-white dark:bg-zinc-900"
                    >
                        <option value="" disabled>Select a role...</option>
                        <option v-for="role in availableRoles" :key="role.id" :value="role.id">
                            {{ role.nama }} ({{ role.slug }})
                        </option>
                    </select>
                </div>
            </div>

            <template #footer>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] dark:text-zinc-200 border border-[#d1d5db] dark:border-zinc-700 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 dark:bg-zinc-900 transition-colors shadow-sm dark:shadow-none"
                    @click="modal.add = false"
                >
                    Cancel
                </button>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-[#2563eb] hover:bg-[#1d4ed8] transition-colors shadow-sm relative disabled:opacity-50 dark:shadow-none"
                    :disabled="isSubmitting || !selectedRoleId"
                    @click="submitAdd"
                >
                    <span :class="{ 'opacity-0': isSubmitting }">Add role</span>
                </button>
            </template>
        </AppModal>

        <!-- Delete Role Modal -->
        <AppModal
            :show="modal.delete"
            title="Delete role"
            type="danger"
            @close="modal.delete = false"
        >
            <template #description>
                Are you sure you want to remove the role <strong class="text-[#111827] dark:text-zinc-100">{{ deletingRole?.nama }}</strong> from this organization? This will only remove their access to this organization, not delete the role entirely.
            </template>

            <template #footer>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] dark:text-zinc-200 border border-[#d1d5db] dark:border-zinc-700 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 dark:bg-zinc-900 transition-colors shadow-sm dark:shadow-none"
                    @click="modal.delete = false"
                >
                    Cancel
                </button>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-[#ef4444] hover:bg-[#dc2626] transition-colors shadow-sm disabled:opacity-50 dark:shadow-none"
                    :disabled="isSubmitting"
                    @click="confirmDelete"
                >
                    <span :class="{ 'opacity-0': isSubmitting }">Remove role</span>
                </button>
            </template>
        </AppModal>

        <!-- Configure Role Assignment Modal -->
        <AppModal
            :show="showRoleAssignmentModal"
            title="Configure Role Assignment"
            @close="showRoleAssignmentModal = false"
        >
            <template #description>
                Map identity provider group names to specific portal roles.
            </template>
            
            <div class="space-y-2 max-h-60 overflow-y-auto mb-4 p-2 bg-gray-50 dark:bg-zinc-800/40 border border-gray-200 dark:border-zinc-800 rounded-lg">
                <div v-if="!tempMappings.length" class="text-center py-8 text-xs text-gray-500 dark:text-zinc-550">No role mappings defined.</div>
                <div v-for="(mapping, idx) in tempMappings" :key="mapping.id" class="flex items-center gap-2">
                    <input 
                        v-model="mapping.group" 
                        type="text" 
                        placeholder="IdP Group Name (e.g. Admins)" 
                        class="flex-1 h-8 px-2.5 text-xs border border-gray-300 dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] text-gray-800 dark:text-zinc-100 bg-white dark:bg-zinc-900"
                    />
                    <span class="text-gray-400 dark:text-zinc-650">➔</span>
                    <select 
                        v-model="mapping.role"
                        class="w-36 h-8 px-1 text-xs border border-gray-300 dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] text-gray-800 dark:text-zinc-100 bg-white dark:bg-zinc-900"
                    >
                        <option v-for="r in orgRoles" :key="r.id" :value="r.nama || r.name">{{ r.nama || r.name }}</option>
                    </select>
                    <button @click="removeMappingRow(idx)" class="text-red-500 hover:text-red-700 p-1 hover:bg-red-50 dark:hover:bg-red-950/30 rounded bg-transparent border-0 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            <template #footer>
                <button 
                    @click="addMappingRow" 
                    class="h-8 px-3 border border-blue-200 dark:border-blue-900/40 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/30 rounded-md text-xs font-semibold transition-colors bg-white dark:bg-zinc-900 cursor-pointer mr-auto"
                >
                    Add mapping
                </button>
                <button class="h-[34px] px-4 border border-[#d1d5db] dark:border-zinc-700 rounded-md text-xs font-semibold text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900" @click="showRoleAssignmentModal = false">Cancel</button>
                <button class="h-[34px] px-4 bg-[#2563eb] text-white rounded-md text-xs font-semibold hover:bg-[#1d4ed8] transition-colors disabled:opacity-50" :disabled="isSubmitting" @click="saveRoleAssignment">Save changes</button>
            </template>
        </AppModal>
    </div>
</template>