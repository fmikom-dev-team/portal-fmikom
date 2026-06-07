<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { computed, onMounted, onUnmounted, ref } from "vue";
import AppModal from "../../components/ui/AppModal.vue";
import { formatDate, toast } from "../../composables/useWorkOs";

const props = defineProps<{
	users?: any[];
	organization?: any;
	roles?: any[];
}>();

const showInviteModal = ref(false);
const assignForm = ref({
	user_id: "",
	role_id: "",
});
const isSubmitting = ref(false);

// Role IDs that are mapped to this organization
const orgRoleIds = computed<number[]>(() =>
	(props.organization?.roles || []).map((r: any) => r.id),
);

const search = ref("");

// Users who have at least one role in this org that matches the organization's mapped roles
const orgUsers = computed(() => {
	if (!props.users || !props.organization) return [];
	if (orgRoleIds.value.length === 0) return []; // No mapped roles → no users

	return props.users
		.map((u) => {
			// Only keep module_roles entries for THIS module whose role is mapped
			const orgRoles = (u.module_roles || []).filter(
				(mr: any) =>
					mr.module_id === props.organization.id &&
					orgRoleIds.value.includes(mr.role_id),
			);
			if (!orgRoles.length) return null;
			return {
				...u,
				org_roles: orgRoles,
				is_active: orgRoles.some((mr: any) => mr.is_active),
			};
		})
		.filter(Boolean)
		.filter(
			(u) =>
				!search.value ||
				(u.email || "").toLowerCase().includes(search.value.toLowerCase()) ||
				(u.name || "").toLowerCase().includes(search.value.toLowerCase()),
		);
});

const availableUsers = computed(() => {
	if (!props.users) return [];
	return props.users.filter(
		(u) =>
			!(u.module_roles || []).some(
				(mr: any) =>
					mr.module_id === props.organization?.id &&
					orgRoleIds.value.includes(mr.role_id),
			),
	);
});

function openInviteModal() {
	assignForm.value.user_id = "";
	assignForm.value.role_id = "";
	showInviteModal.value = true;
}

function submitInvite() {
	if (
		!assignForm.value.user_id ||
		!assignForm.value.role_id ||
		!props.organization
	)
		return;

	isSubmitting.value = true;
	router.post(
		`/workos/users/${assignForm.value.user_id}/module-roles`,
		{
			module_id: props.organization.id,
			role_id: assignForm.value.role_id,
		},
		{
			onSuccess: () => {
				showInviteModal.value = false;
				toast("User successfully invited to organization.", "success");
				// If they were invited from the manage roles modal, we can leave it open but reset role_id
				assignForm.value.role_id = "";
			},
			onError: () => toast("Failed to invite user.", "error"),
			onFinish: () => {
				isSubmitting.value = false;
			},
		},
	);
}

// Menu and Modal State
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

const showManageModal = ref(false);
const showRemoveModal = ref(false);
const selectedUser = ref<any>(null);
const selectedRoleToRemove = ref<any>(null);

function openManageRoles(user: any) {
	selectedUser.value = user;
	assignForm.value.user_id = user.id;
	assignForm.value.role_id = "";
	showManageModal.value = true;
	activeMenu.value = null;
}

function openRemoveRole(user: any, role: any) {
	selectedUser.value = user;
	selectedRoleToRemove.value = role;
	showRemoveModal.value = true;
}

function submitRemoveRole() {
	if (!selectedRoleToRemove.value) return;
	isSubmitting.value = true;
	router.delete(`/workos/module-roles/${selectedRoleToRemove.value.id}`, {
		onSuccess: () => {
			showRemoveModal.value = false;
			toast("Role removed successfully.", "success");
			if (selectedUser.value && selectedUser.value.org_roles.length <= 1) {
				// If they have no roles left, close the manage modal too
				showManageModal.value = false;
			}
		},
		onError: () => toast("Failed to remove role.", "error"),
		onFinish: () => {
			isSubmitting.value = false;
		},
	});
}
</script>

<template>
    <div style="font-family: var(--wos-font)">
        <!-- Toolbar -->
        <div class="flex items-center justify-between mb-4">
            <!-- Search -->
            <div class="relative">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#9ca3af]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" />
                </svg>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search by email..."
                    class="w-[280px] h-[34px] pl-8 pr-3 text-[13px] border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#a78bfa] focus:ring-1 focus:ring-[#a78bfa] transition-colors placeholder:text-[#9ca3af] text-[#111827]"
                />
            </div>
            
            <button
                class="h-[34px] px-4 bg-[#6366f1] text-white rounded-md text-[13px] font-semibold hover:bg-[#4f46e5] transition-colors shadow-sm"
                @click="openInviteModal"
            >
                Invite user
            </button>
        </div>

        <!-- Empty State -->
        <div v-if="!orgUsers.length" class="rounded-xl bg-[#f9fafb] p-12 flex flex-col items-center justify-center text-center gap-2 ring-1 ring-gray-900/[0.04]">
            <template v-if="orgRoleIds.length === 0">
                <svg class="w-8 h-8 text-[#d1d5db] mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
                <p class="text-[13px] font-medium text-[#374151]">No roles assigned to this organization</p>
                <p class="text-[12px] text-[#6b7280]">Go to the <strong>Roles</strong> tab and add at least one role before inviting users.</p>
            </template>
            <template v-else>
                <p class="text-[13px] text-[#6b7280]">No users found in this organization.</p>
            </template>
        </div>

        <!-- Users Table -->
        <div v-else class="rounded-xl overflow-x-auto bg-white ring-1 ring-gray-900/[0.04]">
            <table class="w-full text-left whitespace-nowrap">
                <thead>
                    <tr class="bg-[#f9fafb] border-b border-[#e5e7eb]">
                        <th class="px-4 py-3 text-[12px] font-semibold text-[#111827]">User</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-[#111827]">Role</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-[#111827]">Status</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-[#111827]">Created</th>
                        <th class="px-4 py-3 w-6" />
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e5e7eb]">
                    <tr
                        v-for="u in orgUsers"
                        :key="u.id"
                        class="hover:bg-[#f9fafb] transition-colors group"
                    >
                        <td class="px-4 py-3 align-top">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-[#f3f4f6] border border-[#e5e7eb] flex items-center justify-center text-[#111827] text-[12px] font-bold shrink-0 overflow-hidden">
                                    <img v-if="u.foto_path" :src="u.foto_path" :alt="u.name" class="w-full h-full object-cover" />
                                    <span v-else>{{ u.name?.charAt(0)?.toUpperCase() }}</span>
                                </div>
                                <div>
                                    <p class="text-[13px] font-medium text-[#111827] leading-tight">{{ u.email }}</p>
                                    <p class="text-[12px] text-[#6b7280] leading-tight mt-0.5">{{ u.name }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 align-top">
                            <div class="tooltip-container">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-[11.5px] font-semibold bg-indigo-50 text-indigo-600 border border-indigo-200 hover:bg-indigo-100 hover:text-indigo-700 transition-colors cursor-help">
                                    {{ u.org_roles.length }}
                                </span>
                                <div class="tooltip-content shadow-lg">
                                    <div class="text-[#94a3b8] font-bold text-[9px] tracking-wider uppercase mb-1.5 border-b border-[#334155] pb-1">Assigned Roles</div>
                                    <div class="space-y-1">
                                        <div v-for="r in u.org_roles" :key="r.id" class="flex items-center gap-1.5 text-white text-[12px] font-medium py-0.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 shrink-0"></span>
                                            <span class="whitespace-nowrap">{{ r.role_name || 'Member' }}</span>
                                        </div>
                                    </div>
                                    <div class="tooltip-arrow"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 align-top">
                            <span :class="['text-[11px] font-medium px-2 py-0.5 rounded-full', u.is_active ? 'bg-[#dcfce7] text-[#166534]' : 'bg-[#fef2f2] text-[#ef4444]']">
                                {{ u.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 align-top">
                            <p class="text-[13px] text-[#111827] leading-tight">{{ u.created_at ? formatDate(u.created_at).split(',')[0] : 'Unknown' }}</p>
                            <p class="text-[12px] text-[#6b7280] leading-tight mt-0.5">{{ u.created_at ? formatDate(u.created_at).split(',')[1] : '' }}</p>
                        </td>
                        <td class="px-4 py-3 text-right align-top">
                            <div class="relative flex justify-end">
                                <button class="p-1 rounded text-[#9ca3af] hover:text-[#374151] hover:bg-[#e5e7eb] opacity-0 group-hover:opacity-100 transition-all" @click.stop="toggleMenu(u.id, $event)">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                                    </svg>
                                </button>
                                
                                <div v-if="activeMenu === u.id" class="absolute right-0 top-full mt-1 w-40 bg-white border border-[#e5e7eb] rounded-lg shadow-lg z-50 py-1">
                                    <button class="w-full flex items-center gap-2 px-3 py-1.5 text-[13px] text-[#374151] hover:bg-[#f9fafb] text-left transition-colors" @click="openManageRoles(u)">
                                        <svg class="w-3.5 h-3.5 text-[#9ca3af]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        Manage roles
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Invite User Modal -->
        <AppModal
            :show="showInviteModal"
            title="Invite user"
            @close="showInviteModal = false"
        >
            <template #description>
                Assign an existing user to this organization and grant them a specific role.
            </template>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-[13px] font-semibold text-[#374151] mb-1.5">User</label>
                    <div class="relative">
                        <select
                            v-model="assignForm.user_id"
                            class="w-full h-9 pl-3 pr-8 text-[13px] border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] appearance-none bg-white text-[#111827]"
                        >
                            <option value="" disabled selected>Select a user to invite...</option>
                            <option v-for="u in availableUsers" :key="u.id" :value="u.id">
                                {{ u.email }} ({{ u.name }})
                            </option>
                        </select>
                        <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#9ca3af] pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                    <p v-if="!availableUsers.length" class="mt-1.5 text-[11px] text-[#ef4444]">All users are already in this organization.</p>
                </div>
                
                <div>
                    <label class="block text-[13px] font-semibold text-[#374151] mb-1.5">Organization</label>
                    <input
                        type="text"
                        :value="organization?.name || 'Organization'"
                        readonly
                        class="w-full h-9 px-3 text-[13px] border border-[#e5e7eb] rounded-md bg-[#f9fafb] text-[#6b7280] cursor-not-allowed"
                    />
                </div>

                <div>
                    <label class="block text-[13px] font-semibold text-[#374151] mb-1.5">Role</label>
                    <div class="relative">
                        <select
                            v-model="assignForm.role_id"
                            class="w-full h-9 pl-3 pr-8 text-[13px] border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] appearance-none bg-white text-[#111827]"
                        >
                            <option value="" disabled selected>Select a role...</option>
                            <option v-for="r in organization?.roles || []" :key="r.id" :value="r.id">
                                {{ r.nama || r.name }}
                            </option>
                        </select>
                        <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#9ca3af] pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            <template #footer>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] border border-[#d1d5db] hover:bg-[#f9fafb] transition-colors shadow-sm"
                    @click="showInviteModal = false"
                >
                    Cancel
                </button>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white transition-colors shadow-sm relative overflow-hidden disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="[isSubmitting ? 'bg-[#4f46e5]' : 'bg-[#6366f1] hover:bg-[#4f46e5]']"
                    @click="submitInvite"
                    :disabled="isSubmitting || !assignForm.user_id || !assignForm.role_id"
                >
                    <span :class="{ 'opacity-0': isSubmitting }">Invite user</span>
                    <div v-if="isSubmitting" class="absolute inset-0 flex items-center justify-center">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </button>
            </template>
        </AppModal>

        <!-- Manage Roles Modal -->
        <AppModal
            :show="showManageModal"
            title="Manage roles"
            @close="showManageModal = false"
        >
            <template #description>
                Manage organization roles for <strong class="text-[#111827]">{{ selectedUser?.name }}</strong>.
            </template>
            
            <div class="space-y-5">
                <!-- Current Roles -->
                <div>
                    <h3 class="text-[12px] font-semibold text-[#6b7280] uppercase tracking-wider mb-2">Current Roles</h3>
                    <div class="rounded-xl divide-y divide-gray-100 ring-1 ring-gray-900/[0.04]">
                        <div v-for="role in selectedUser?.org_roles" :key="role.id" class="flex items-center justify-between p-3 bg-white">
                            <div class="flex items-center gap-2.5">
                                <div class="w-6 h-6 rounded-md bg-[#f3f4f6] flex items-center justify-center border border-[#e5e7eb]">
                                    <svg class="w-3.5 h-3.5 text-[#6b7280]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <span class="text-[13px] font-medium text-[#374151]">{{ role.role_name }}</span>
                            </div>
                            <button
                                class="text-[13px] font-medium text-[#ef4444] hover:text-[#dc2626] transition-colors"
                                @click="openRemoveRole(selectedUser, role)"
                            >
                                Remove
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Add Role -->
                <div>
                    <h3 class="text-[12px] font-semibold text-[#6b7280] uppercase tracking-wider mb-2">Add Role</h3>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <select
                                v-model="assignForm.role_id"
                                class="w-full h-[34px] pl-3 pr-8 text-[13px] border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] appearance-none bg-white text-[#111827]"
                            >
                                <option value="" disabled selected>Select a role to add...</option>
                                <option v-for="r in organization?.roles || []" :key="r.id" :value="r.id">
                                    {{ r.nama || r.name }}
                                </option>
                            </select>
                            <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#9ca3af] pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        <button
                            class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-[#6366f1] hover:bg-[#4f46e5] transition-colors shadow-sm disabled:opacity-50"
                            :disabled="isSubmitting || !assignForm.role_id"
                            @click="submitInvite"
                        >
                            Add role
                        </button>
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end">
                    <button
                        class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] border border-[#d1d5db] hover:bg-[#f9fafb] transition-colors shadow-sm"
                        @click="showManageModal = false"
                    >
                        Done
                    </button>
                </div>
            </template>
        </AppModal>

        <!-- Remove Role Modal -->
        <AppModal
            :show="showRemoveModal"
            title="Remove role"
            type="danger"
            @close="showRemoveModal = false"
        >
            <template #description>
                Are you sure you want to remove the <strong class="text-[#111827]">{{ selectedRoleToRemove?.role_name }}</strong> role from <strong class="text-[#111827]">{{ selectedUser?.name }}</strong>?
                <span v-if="selectedUser?.org_roles.length === 1" class="block mt-1 text-[#ef4444]">
                    This is their last role. They will be removed from the organization entirely.
                </span>
            </template>

            <template #footer>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] border border-[#d1d5db] hover:bg-[#f9fafb] transition-colors shadow-sm"
                    @click="showRemoveModal = false"
                >
                    Cancel
                </button>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-[#ef4444] hover:bg-[#dc2626] transition-colors shadow-sm disabled:opacity-50"
                    :disabled="isSubmitting"
                    @click="submitRemoveRole"
                >
                    <span :class="{ 'opacity-0': isSubmitting }">Remove role</span>
                </button>
            </template>
        </AppModal>
    </div>
</template>

<style scoped>
.tooltip-container {
    position: relative;
    display: inline-block;
}
.tooltip-content {
    position: absolute;
    bottom: 125%;
    left: 50%;
    transform: translateX(-50%) translateY(4px);
    background-color: #0f172a;
    border: 1px solid #1e293b;
    color: #ffffff;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 12px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
    min-width: 140px;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1), transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    z-index: 999;
}
.tooltip-container:hover .tooltip-content {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}
.tooltip-arrow {
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border-width: 6px;
    border-style: solid;
    border-color: #0f172a transparent transparent transparent;
}
</style>
