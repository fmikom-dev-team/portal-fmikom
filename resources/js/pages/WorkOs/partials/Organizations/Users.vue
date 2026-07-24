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
const selectedRole = ref("");
const selectedStatus = ref("");
const selectedAuth = ref("all");

const authDropdownOpen = ref(false);
const roleDropdownOpen = ref(false);
const membershipDropdownOpen = ref(false);

const GOOGLE_SVG = `<svg viewBox="0 0 24 24" class="w-3.5 h-3.5 inline-block" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>`;
const GITHUB_SVG = `<svg viewBox="0 0 24 24" class="w-3.5 h-3.5 inline-block" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>`;
const MICROSOFT_SVG = `<svg viewBox="0 0 24 24" class="w-3.5 h-3.5 inline-block" xmlns="http://www.w3.org/2000/svg"><path d="M11.4 24H0V12.6h11.4V24z" fill="#F25022"/><path d="M24 24H12.6V12.6H24V24z" fill="#00A4EF"/><path d="M11.4 11.4H0V0h11.4v11.4z" fill="#7FBA00"/><path d="M24 11.4H12.6V0H24v11.4z" fill="#FFB900"/></svg>`;
const APPLE_SVG = `<svg viewBox="0 0 24 24" class="w-3.5 h-3.5 inline-block" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12.152 6.896c-.948 0-2.415-1.078-3.96-1.04-2.04.027-3.91 1.183-4.961 3.014-2.117 3.675-.546 9.103 1.519 12.09 1.013 1.454 2.208 3.09 3.792 3.039 1.52-.065 2.09-.987 3.935-.987 1.831 0 2.35.987 3.96.948 1.637-.026 2.676-1.48 3.676-2.948 1.156-1.688 1.636-3.325 1.662-3.415-.039-.013-3.182-1.221-3.22-4.857-.026-3.04 2.48-4.494 2.597-4.559-1.429-2.09-3.623-2.324-4.39-2.376-2-.156-3.675 1.09-4.61 1.09zM15.53 3.83c.843-1.012 1.4-2.427 1.245-3.83-1.207.052-2.662.805-3.532 1.818-.78.896-1.454 2.338-1.273 3.714 1.338.104 2.715-.688 3.559-1.701z"/></svg>`;
const GENERIC_KEY_SVG = `<svg viewBox="0 0 24 24" class="w-3.5 h-3.5 inline-block text-gray-400 dark:text-zinc-500" fill="none" stroke="currentColor" stroke-width="2" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m-5-4a5 5 0 015 5c0 2.071-.84 3.946-2.197 5.303L10 18H8v-2l.707-.707A5.002 5.002 0 0112 5z"/></svg>`;

const filterAuthLabel = computed(() => {
	if (selectedAuth.value === "email") return "Email + Password";
	if (selectedAuth.value === "all") return "All";
	return selectedAuth.value.charAt(0).toUpperCase() + selectedAuth.value.slice(1);
});

const filterRoleLabel = computed(() => {
	if (!selectedRole.value) return "All";
	const matched = props.organization?.roles?.find((r: any) => r.id === Number(selectedRole.value));
	return matched ? (matched.nama || matched.name) : selectedRole.value;
});

const filterMembershipLabel = computed(() => {
	if (!selectedStatus.value) return "All";
	if (selectedStatus.value === "active") return "Active";
	if (selectedStatus.value === "inactive") return "Inactive";
	return selectedStatus.value;
});

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
		.filter((u) => {
			const matchesSearch = !search.value ||
				(u.email || "").toLowerCase().includes(search.value.toLowerCase()) ||
				(u.name || "").toLowerCase().includes(search.value.toLowerCase());
			const matchesRole = !selectedRole.value ||
				u.org_roles.some((mr: any) => mr.role_id === Number(selectedRole.value));
			const matchesStatus = !selectedStatus.value ||
				(selectedStatus.value === "active" ? u.is_active : !u.is_active);
			const matchesAuth = selectedAuth.value === "all" ||
				(selectedAuth.value === "email"
					? (!u.oauth_credentials || u.oauth_credentials.length === 0)
					: (u.oauth_credentials || []).some((oc: any) => oc.provider_slug === selectedAuth.value)
				);
			return matchesSearch && matchesRole && matchesStatus && matchesAuth;
		});
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
function closeAllDropdowns(e: MouseEvent) {
	const target = e.target as HTMLElement;
	if (!target.closest("[data-dropdown]")) {
		authDropdownOpen.value = false;
		roleDropdownOpen.value = false;
		membershipDropdownOpen.value = false;
		activeMenu.value = null;
	}
}
onMounted(() => document.addEventListener("click", closeAllDropdowns));
onUnmounted(() => document.removeEventListener("click", closeAllDropdowns));

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
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
            <!-- Search & Filters -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Search -->
                <div class="relative w-full sm:w-auto">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Search"
                        class="w-full sm:w-[280px] h-[34px] pl-9 pr-3 text-[13px] border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors placeholder:text-[#9ca3af] dark:placeholder:text-zinc-600 text-[#111827] dark:text-zinc-100 shadow-sm bg-white dark:bg-zinc-900 dark:shadow-none"
                    />
                </div>

                <!-- Authentication filter -->
                <div class="relative inline-block text-left" data-dropdown>
                    <button 
                        @click.stop="authDropdownOpen = !authDropdownOpen; roleDropdownOpen = false; membershipDropdownOpen = false"
                        class="flex items-center gap-1.5 h-[34px] px-3 border border-[#d1d5db] dark:border-zinc-700 rounded-md text-[13px] text-[#4b5563] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800/40 transition-colors bg-white dark:bg-zinc-900 shadow-sm font-medium cursor-pointer dark:shadow-none"
                    >
                        <span v-html="GENERIC_KEY_SVG" />
                        <span>{{ selectedAuth === 'all' ? '+ Authentication' : 'Authentication: ' + filterAuthLabel }}</span>
                    </button>
                    <div 
                        v-show="authDropdownOpen" 
                        class="absolute left-0 mt-1 w-48 bg-white dark:bg-zinc-900 border border-[#e5e7eb] dark:border-zinc-800 rounded-lg shadow-lg dark:shadow-black/40 py-1 z-50 dark:shadow-none"
                    >
                        <button 
                            v-for="authOpt in ['all', 'email', 'google', 'github', 'microsoft', 'apple']" 
                            :key="authOpt"
                            @click="selectedAuth = authOpt; authDropdownOpen = false"
                            class="w-full flex items-center justify-between px-3.5 py-2 text-[12.5px] text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:bg-zinc-900 dark:hover:bg-zinc-800/40 text-left transition-colors font-medium cursor-pointer"
                        >
                            <span>{{ authOpt === 'all' ? 'All Authentications' : (authOpt === 'email' ? 'Email + Password' : authOpt.charAt(0).toUpperCase() + authOpt.slice(1)) }}</span>
                            <svg v-if="selectedAuth === authOpt" class="w-3 h-3 text-[#2563EB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Roles filter -->
                <div class="relative inline-block text-left" data-dropdown>
                    <button 
                        @click.stop="roleDropdownOpen = !roleDropdownOpen; authDropdownOpen = false; membershipDropdownOpen = false"
                        class="flex items-center gap-1.5 h-[34px] px-3 border border-[#d1d5db] dark:border-zinc-700 rounded-md text-[13px] text-[#4b5563] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800/40 transition-colors bg-white dark:bg-zinc-900 shadow-sm font-medium cursor-pointer dark:shadow-none"
                    >
                        <svg class="w-3.5 h-3.5 text-[#9ca3af] dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.333 0 4 .667 4 2v1H3v-1c0-1.333 2.667-2 4-2z" />
                        </svg>
                        <span>{{ !selectedRole ? 'Roles' : 'Role: ' + filterRoleLabel }}</span>
                    </button>
                    <div 
                        v-show="roleDropdownOpen" 
                        class="absolute left-0 mt-1 w-52 bg-white dark:bg-zinc-900 border border-[#e5e7eb] dark:border-zinc-800 rounded-lg shadow-lg dark:shadow-black/40 py-1 z-50 max-h-60 overflow-y-auto wos-scroll dark:shadow-none"
                    >
                        <button 
                            @click="selectedRole = ''; roleDropdownOpen = false"
                            class="w-full flex items-center justify-between px-3.5 py-2 text-[12.5px] text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:bg-zinc-900 dark:hover:bg-zinc-800/40 text-left transition-colors font-medium cursor-pointer"
                        >
                            <span>All Roles</span>
                            <svg v-if="!selectedRole" class="w-3 h-3 text-[#2563EB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                        <button 
                            v-for="role in organization?.roles || []" 
                            :key="role.id"
                            @click="selectedRole = String(role.id); roleDropdownOpen = false"
                            class="w-full flex items-center justify-between px-3.5 py-2 text-[12.5px] text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:bg-zinc-900 dark:hover:bg-zinc-800/40 text-left transition-colors font-medium cursor-pointer"
                        >
                            <span>{{ role.nama || role.name }}</span>
                            <svg v-if="selectedRole === String(role.id)" class="w-3 h-3 text-[#2563EB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Membership filter -->
                <div class="relative inline-block text-left" data-dropdown>
                    <button 
                        @click.stop="membershipDropdownOpen = !membershipDropdownOpen; authDropdownOpen = false; roleDropdownOpen = false"
                        class="flex items-center gap-1.5 h-[34px] px-3 border border-[#d1d5db] dark:border-zinc-700 rounded-md text-[13px] text-[#4b5563] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800/40 transition-colors bg-white dark:bg-zinc-900 shadow-sm font-medium cursor-pointer dark:shadow-none"
                    >
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>{{ !selectedStatus ? '+ Membership' : 'Membership: ' + filterMembershipLabel }}</span>
                    </button>
                    <div 
                        v-show="membershipDropdownOpen" 
                        class="absolute left-0 mt-1 w-48 bg-white dark:bg-zinc-900 border border-[#e5e7eb] dark:border-zinc-800 rounded-lg shadow-lg dark:shadow-black/40 py-1 z-50 dark:shadow-none"
                    >
                        <button 
                            @click="selectedStatus = ''; membershipDropdownOpen = false"
                            class="w-full flex items-center justify-between px-3.5 py-2 text-[12.5px] text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:bg-zinc-900 dark:hover:bg-zinc-800/40 text-left transition-colors font-medium cursor-pointer"
                        >
                            <span>All Memberships</span>
                            <svg v-if="!selectedStatus" class="w-3 h-3 text-[#2563EB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                        <button 
                            v-for="membOpt in ['active', 'inactive']" 
                            :key="membOpt"
                            @click="selectedStatus = membOpt; membershipDropdownOpen = false"
                            class="w-full flex items-center justify-between px-3.5 py-2 text-[12.5px] text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:bg-zinc-900 dark:hover:bg-zinc-800/40 text-left transition-colors font-medium cursor-pointer"
                        >
                            <span>{{ membOpt === 'active' ? 'Active' : 'Inactive' }}</span>
                            <svg v-if="selectedStatus === membOpt" class="w-3 h-3 text-[#2563EB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <button
                class="h-[34px] px-4 bg-[#2563eb] text-white rounded-md text-[13px] font-semibold hover:bg-[#1d4ed8] transition-colors shadow-sm cursor-pointer dark:shadow-none"
                @click="openInviteModal"
            >
                Invite user
            </button>
        </div>

        <!-- Empty State -->
        <div v-if="!orgUsers.length" class="rounded-xl bg-[#f9fafb] dark:bg-zinc-900 p-12 flex flex-col items-center justify-center text-center gap-2 ring-1 ring-gray-900/[0.04] dark:ring-white/[0.06] border dark:border-zinc-800">
            <template v-if="orgRoleIds.length === 0">
                <svg class="w-8 h-8 text-[#d1d5db] dark:text-zinc-600 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
                <p class="text-[13px] font-medium text-[#374151] dark:text-zinc-300">No roles assigned to this organization</p>
                <p class="text-[12px] text-[#6b7280] dark:text-zinc-500">Go to the <strong>Roles</strong> tab and add at least one role before inviting users.</p>
            </template>
            <template v-else>
                <p class="text-[13px] text-[#6b7280] dark:text-zinc-500">No users found in this organization.</p>
            </template>
        </div>

        <!-- Users Table -->
        <div v-else class="rounded-xl overflow-x-auto bg-white dark:bg-zinc-900 ring-1 ring-gray-900/[0.04] dark:ring-white/[0.06] border dark:border-zinc-800">
            <table class="w-full text-left whitespace-nowrap">
                <caption class="sr-only">Organization Users</caption>
                <thead>
                    <tr class="bg-[#f9fafb] dark:bg-zinc-800/60 border-b border-[#e5e7eb] dark:border-zinc-800">
                        <th class="px-4 py-3 text-[12px] font-semibold text-[#111827] dark:text-zinc-300">User</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-[#111827] dark:text-zinc-300">Authentication</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-[#111827] dark:text-zinc-300">Roles</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-[#111827] dark:text-zinc-300">Membership</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-[#111827] dark:text-zinc-300">Sign-in count</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-[#111827] dark:text-zinc-300">Last sign-in</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-[#111827] dark:text-zinc-300 flex items-center gap-1">
                            Created
                            <svg class="w-3.5 h-3.5 text-[#111827] dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                        </th>
                        <th class="px-4 py-3 w-12" />
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e5e7eb] dark:divide-zinc-800">
                    <tr
                        v-for="u in orgUsers"
                        :key="u.id"
                        class="hover:bg-[#f9fafb] dark:bg-zinc-900 dark:hover:bg-zinc-800/40 transition-colors group"
                    >
                        <td class="px-4 py-3 align-top">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-[#f3f4f6] dark:bg-zinc-800 border border-[#e5e7eb] dark:border-zinc-700 flex items-center justify-center text-[#111827] dark:text-zinc-200 text-[12px] font-bold shrink-0 overflow-hidden">
                                    <img v-if="u.foto_path" :src="u.foto_path" :alt="u.name" class="w-full h-full object-cover" />
                                    <span v-else>{{ u.name?.charAt(0)?.toUpperCase() }}</span>
                                </div>
                                <div>
                                    <p class="text-[13px] font-medium text-[#111827] dark:text-zinc-100 leading-tight">{{ u.email }}</p>
                                    <p class="text-[12px] text-[#6b7280] dark:text-zinc-400 leading-tight mt-0.5">{{ u.name }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 align-top">
                            <div class="flex items-center gap-1.5 text-[13px] text-gray-700 dark:text-zinc-300 font-medium">
                                <template v-if="u.oauth_credentials && u.oauth_credentials.length > 0">
                                    <span v-for="oc in u.oauth_credentials" :key="oc.id" class="inline-flex items-center gap-1">
                                        <span v-if="oc.provider_slug === 'google'" v-html="GOOGLE_SVG" />
                                        <span v-else-if="oc.provider_slug === 'github'" v-html="GITHUB_SVG" />
                                        <span v-else-if="oc.provider_slug === 'microsoft'" v-html="MICROSOFT_SVG" />
                                        <span v-else-if="oc.provider_slug === 'apple'" v-html="APPLE_SVG" />
                                        <span v-else v-html="GENERIC_KEY_SVG" />
                                        {{ oc.provider_name || (oc.provider_slug.charAt(0).toUpperCase() + oc.provider_slug.slice(1)) }}
                                    </span>
                                </template>
                                <template v-else>
                                    <svg class="w-4 h-4 text-gray-400 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <rect x="3" y="4" width="18" height="16" rx="2" />
                                        <circle cx="8" cy="10" r="2" />
                                        <line x1="13" y1="9" x2="18" y2="9" />
                                        <line x1="13" y1="13" x2="18" y2="13" />
                                        <line x1="7" y1="16" x2="17" y2="16" />
                                    </svg>
                                    Email + Password
                                </template>
                            </div>
                        </td>
                        <td class="px-4 py-3 align-top">
                            <div class="tooltip-container">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-[11.5px] font-semibold bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-900/60 hover:bg-blue-100 hover:text-blue-700 transition-colors cursor-help">
                                    {{ u.org_roles.length }}
                                </span>
                                <div class="tooltip-content shadow-lg dark:shadow-none">
                                    <div class="text-[#94a3b8] font-bold text-[9px] tracking-wider uppercase mb-1.5 border-b border-[#334155] dark:border-zinc-800 pb-1">Assigned Roles</div>
                                    <div class="space-y-1">
                                        <div v-for="r in u.org_roles" :key="r.id" class="flex items-center gap-1.5 text-white text-[12px] font-medium py-0.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-400 shrink-0"></span>
                                            <span class="whitespace-nowrap">{{ r.role_name || 'Member' }}</span>
                                        </div>
                                    </div>
                                    <div class="tooltip-arrow"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 align-top">
                            <span class="inline-flex items-center gap-1.5 text-[13px] font-medium leading-none">
                                <span class="w-1.5 h-1.5 rounded-full" :class="u.is_active ? 'bg-emerald-500' : 'bg-red-500'" />
                                <span :class="u.is_active ? 'text-emerald-600' : 'text-red-500'">
                                    {{ u.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </span>
                        </td>
                        <td class="px-4 py-3 align-top text-[13px] text-[#111827] dark:text-zinc-300">
                            {{ u.sign_in_count || 0 }}
                        </td>
                        <td class="px-4 py-3 align-top text-[13px] text-[#111827] dark:text-zinc-300">
                            <template v-if="u.last_sign_in_at">
                                <p class="leading-tight font-medium">{{ u.last_sign_in_at.split(', ')[0] + ', ' + u.last_sign_in_at.split(', ')[1] }}</p>
                                <p class="text-[12px] text-[#6b7280] dark:text-zinc-500 leading-tight mt-0.5">{{ u.last_sign_in_at.split(', ')[2] }}</p>
                            </template>
                            <span v-else class="text-gray-400 dark:text-zinc-500">—</span>
                        </td>
                        <td class="px-4 py-3 align-top text-[13px] text-[#111827] dark:text-zinc-300">
                            <template v-if="u.created_at">
                                <p class="leading-tight font-medium">{{ u.created_at.split(', ')[0] + ', ' + u.created_at.split(', ')[1] }}</p>
                                <p class="text-[12px] text-[#6b7280] dark:text-zinc-500 leading-tight mt-0.5">{{ u.created_at.split(', ')[2] }}</p>
                            </template>
                            <span v-else class="text-gray-400 dark:text-zinc-500">—</span>
                        </td>
                        <td class="px-4 py-3 text-right align-top" @click.stop>
                            <div class="relative inline-block text-left" data-dropdown>
                                <button class="p-1 rounded text-[#9ca3af] hover:text-[#374151] dark:hover:text-zinc-200 dark:text-zinc-200 hover:bg-[#e5e7eb] transition-all cursor-pointer" @click.stop="toggleMenu(u.id, $event)">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                                    </svg>
                                </button>
                                
                                <div v-if="activeMenu === u.id" class="absolute right-0 mt-1 w-40 bg-white dark:bg-zinc-900 border border-[#e5e7eb] dark:border-zinc-800 rounded-lg shadow-lg z-50 py-1 text-left dark:shadow-none">
                                    <button class="w-full flex items-center gap-2 px-3 py-1.5 text-[13px] text-[#374151] dark:text-zinc-200 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 dark:bg-zinc-900 text-left transition-colors cursor-pointer" @click="openManageRoles(u)">
                                        <svg class="w-3.5 h-3.5 text-[#9ca3af] dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
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
                    <label class="block text-[13px] font-semibold text-[#374151] dark:text-zinc-200 mb-1.5">User</label>
                    <div class="relative">
                        <select
                            v-model="assignForm.user_id"
                            class="w-full h-9 pl-3 pr-8 text-[13px] border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] appearance-none bg-white dark:bg-zinc-900 text-[#111827] dark:text-zinc-100"
                        >
                            <option value="" disabled selected>Select a user to invite...</option>
                            <option v-for="u in availableUsers" :key="u.id" :value="u.id">
                                {{ u.email }} ({{ u.name }})
                            </option>
                        </select>
                        <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#9ca3af] dark:text-zinc-500 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                    <p v-if="!availableUsers.length" class="mt-1.5 text-[11px] text-[#ef4444]">All users are already in this organization.</p>
                </div>
                
                <div>
                    <label class="block text-[13px] font-semibold text-[#374151] dark:text-zinc-200 mb-1.5">Organization</label>
                    <input
                        type="text"
                        :value="organization?.name || 'Organization'"
                        readonly
                        class="w-full h-9 px-3 text-[13px] border border-[#e5e7eb] dark:border-zinc-800 rounded-md bg-[#f9fafb] dark:bg-zinc-900 text-[#6b7280] dark:text-zinc-400 cursor-not-allowed"
                    />
                </div>

                <div>
                    <label class="block text-[13px] font-semibold text-[#374151] dark:text-zinc-200 mb-1.5">Role</label>
                    <div class="relative">
                        <select
                            v-model="assignForm.role_id"
                            class="w-full h-9 pl-3 pr-8 text-[13px] border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] appearance-none bg-white dark:bg-zinc-900 text-[#111827] dark:text-zinc-100"
                        >
                            <option value="" disabled selected>Select a role...</option>
                            <option v-for="r in organization?.roles || []" :key="r.id" :value="r.id">
                                {{ r.nama || r.name }}
                            </option>
                        </select>
                        <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#9ca3af] dark:text-zinc-500 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            <template #footer>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] dark:text-zinc-200 border border-[#d1d5db] dark:border-zinc-700 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 dark:bg-zinc-900 transition-colors shadow-sm dark:shadow-none"
                    @click="showInviteModal = false"
                >
                    Cancel
                </button>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white transition-colors shadow-sm relative overflow-hidden disabled:opacity-50 disabled:cursor-not-allowed dark:shadow-none"
                    :class="[isSubmitting ? 'bg-[#1d4ed8]' : 'bg-[#2563eb] hover:bg-[#1d4ed8]']"
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
                Manage organization roles for <strong class="text-[#111827] dark:text-zinc-100">{{ selectedUser?.name }}</strong>.
            </template>
            
            <div class="space-y-5">
                <!-- Current Roles -->
                <div>
                    <h3 class="text-[12px] font-semibold text-[#6b7280] dark:text-zinc-400 uppercase tracking-wider mb-2">Current Roles</h3>
                    <div class="rounded-xl divide-y divide-gray-100 dark:divide-zinc-800 ring-1 ring-gray-900 dark:ring-zinc-700/[0.04]">
                        <div v-for="role in selectedUser?.org_roles" :key="role.id" class="flex items-center justify-between p-3 bg-white dark:bg-zinc-900">
                            <div class="flex items-center gap-2.5">
                                <div class="w-6 h-6 rounded-md bg-[#f3f4f6] dark:bg-zinc-800 flex items-center justify-center border border-[#e5e7eb] dark:border-zinc-800">
                                    <svg class="w-3.5 h-3.5 text-[#6b7280] dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <span class="text-[13px] font-medium text-[#374151] dark:text-zinc-200">{{ role.role_name }}</span>
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
                    <h3 class="text-[12px] font-semibold text-[#6b7280] dark:text-zinc-400 uppercase tracking-wider mb-2">Add Role</h3>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <select
                                v-model="assignForm.role_id"
                                class="w-full h-[34px] pl-3 pr-8 text-[13px] border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] appearance-none bg-white dark:bg-zinc-900 text-[#111827] dark:text-zinc-100"
                            >
                                <option value="" disabled selected>Select a role to add...</option>
                                <option v-for="r in organization?.roles || []" :key="r.id" :value="r.id">
                                    {{ r.nama || r.name }}
                                </option>
                            </select>
                            <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#9ca3af] dark:text-zinc-500 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        <button
                            class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-[#2563eb] hover:bg-[#1d4ed8] transition-colors shadow-sm disabled:opacity-50 dark:shadow-none"
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
                        class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] dark:text-zinc-200 border border-[#d1d5db] dark:border-zinc-700 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 dark:bg-zinc-900 transition-colors shadow-sm dark:shadow-none"
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
                Are you sure you want to remove the <strong class="text-[#111827] dark:text-zinc-100">{{ selectedRoleToRemove?.role_name }}</strong> role from <strong class="text-[#111827] dark:text-zinc-100">{{ selectedUser?.name }}</strong>?
                <span v-if="selectedUser?.org_roles.length === 1" class="block mt-1 text-[#ef4444]">
                    This is their last role. They will be removed from the organization entirely.
                </span>
            </template>

            <template #footer>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] dark:text-zinc-200 border border-[#d1d5db] dark:border-zinc-700 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 dark:bg-zinc-900 transition-colors shadow-sm dark:shadow-none"
                    @click="showRemoveModal = false"
                >
                    Cancel
                </button>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-[#ef4444] hover:bg-[#dc2626] transition-colors shadow-sm disabled:opacity-50 dark:shadow-none"
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
    left: 100%;
    top: 50%;
    transform: translateY(-50%) translateX(4px);
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
    transform: translateY(-50%) translateX(10px);
}
.tooltip-arrow {
    position: absolute;
    top: 50%;
    right: 100%;
    transform: translateY(-50%);
    border-width: 6px;
    border-style: solid;
    border-color: transparent #0f172a transparent transparent;
}
</style>
