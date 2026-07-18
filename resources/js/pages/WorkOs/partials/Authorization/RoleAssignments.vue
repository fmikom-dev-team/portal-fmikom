<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import {
	CheckSquare,
	Edit,
	ShieldAlert,
	Square,
	Trash2,
	UserPlus,
} from "lucide-vue-next";
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import AppModal from "../../components/ui/AppModal.vue";
import { formatDate, toast } from "../../composables/useWorkOs";

const props = defineProps<{
	users: any[];
	roles: any[];
	modules: any[];
	searchQuery?: string;
}>();

const search = ref("");
watch(() => props.searchQuery, (val) => {
	search.value = val || "";
});
const showAssignModal = ref(false);
const showEditModal = ref(false);
const showRemoveModal = ref(false);
const isSubmitting = ref(false);

const assignForm = ref({
	user_id: "",
	module_id: "",
	role_id: "",
});

const editingAssignment = ref<any>(null);
const editForm = ref({
	role_id: "",
	is_active: true,
});

const selectedToRemove = ref<any>(null);

// Flatten all assignments from all users
const allAssignments = computed(() => {
	if (!props.users) return [];
	const list: any[] = [];
	props.users.forEach((user) => {
		if (user.module_roles) {
			user.module_roles.forEach((mr: any) => {
				list.push({
					id: mr.id,
					user_id: user.id,
					user_name: user.name,
					user_email: user.email,
					user_foto: user.foto_path,
					module_id: mr.module_id,
					module_name: mr.module_name || "Unknown Module",
					module_code: mr.module_code || "UNKNOWN",
					role_id: mr.role_id,
					role_name: mr.role_name || "Member",
					role_slug: mr.role_slug || "member",
					is_active: mr.is_active,
				});
			});
		}
	});

	return list.filter((item) => {
		if (!search.value.trim()) return true;
		const q = search.value.toLowerCase();
		return (
			item.user_name.toLowerCase().includes(q) ||
			item.user_email.toLowerCase().includes(q) ||
			item.module_name.toLowerCase().includes(q) ||
			item.role_name.toLowerCase().includes(q)
		);
	});
});

// Dropdown state logic
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

function openAssignModal() {
	assignForm.value = {
		user_id: "",
		module_id: "",
		role_id: "",
	};
	showAssignModal.value = true;
}

function submitAssign() {
	if (
		!assignForm.value.user_id ||
		!assignForm.value.module_id ||
		!assignForm.value.role_id
	)
		return;
	isSubmitting.value = true;
	router.post(
		`/workos/users/${assignForm.value.user_id}/module-roles`,
		{
			module_id: assignForm.value.module_id,
			role_id: assignForm.value.role_id,
		},
		{
			onSuccess: () => {
				showAssignModal.value = false;
				toast("Role assigned successfully.", "success");
			},
			onError: (err: any) => {
				const errMsg =
					Object.values(err).flat().join(" ") || "Failed to assign role.";
				toast(errMsg, "error");
			},
			onFinish: () => {
				isSubmitting.value = false;
			},
		},
	);
}

function openEditModal(assignment: any) {
	editingAssignment.value = assignment;
	editForm.value = {
		role_id: assignment.role_id,
		is_active: assignment.is_active,
	};
	showEditModal.value = true;
	activeMenu.value = null;
}

function submitEdit() {
	if (!editingAssignment.value) return;
	isSubmitting.value = true;
	router.patch(
		`/workos/module-roles/${editingAssignment.value.id}`,
		{
			role_id: editForm.value.role_id,
			is_active: editForm.value.is_active,
		},
		{
			onSuccess: () => {
				showEditModal.value = false;
				toast("Role assignment updated.", "success");
			},
			onError: () => toast("Failed to update role assignment.", "error"),
			onFinish: () => {
				isSubmitting.value = false;
			},
		},
	);
}

function openRemoveModal(assignment: any) {
	selectedToRemove.value = assignment;
	showRemoveModal.value = true;
	activeMenu.value = null;
}

function submitRemove() {
	if (!selectedToRemove.value) return;
	isSubmitting.value = true;
	router.delete(`/workos/module-roles/${selectedToRemove.value.id}`, {
		onSuccess: () => {
			showRemoveModal.value = false;
			toast("Role assignment removed successfully.", "success");
		},
		onError: () => toast("Failed to remove role assignment.", "error"),
		onFinish: () => {
			isSubmitting.value = false;
		},
	});
}
</script>

<template>
    <div style="font-family: var(--wos-font)" class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-[20px] font-semibold text-gray-900 dark:text-zinc-100 tracking-tight">Role Assignments</h1>
                <p class="text-[13px] text-gray-500 dark:text-zinc-400 mt-0.5">Assign users to roles within specific modules or organizations.</p>
            </div>
            
            <button
                class="h-[34px] px-4 bg-gray-900 text-white rounded-md text-[13px] font-semibold hover:bg-black transition-colors shadow-sm flex items-center gap-1.5 self-start sm:self-auto dark:shadow-none"
                @click="openAssignModal"
            >
                <UserPlus class="w-4 h-4" />
                <span>Assign role</span>
            </button>
        </div>

        <!-- Toolbar -->
        <div class="flex items-center gap-2">
            <div class="relative flex-1 max-w-sm">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" />
                </svg>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search assignments by user, module, or role..."
                    class="w-full h-[34px] pl-8 pr-3 text-[13px] border border-gray-200 dark:border-zinc-700 rounded-md focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:ring-zinc-700 transition-colors placeholder:text-gray-400 text-gray-900 dark:text-zinc-100 bg-white dark:bg-zinc-900"
                />
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!allAssignments.length" class="rounded-xl bg-[#f9fafb] dark:bg-zinc-900 border border-dashed border-gray-200 dark:border-zinc-700 p-12 flex flex-col items-center justify-center text-center gap-2">
            <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.052-.03c0-.223.012-.447.037-.666A11.944 11.944 0 0112 12.75c2.17 0 4.207.576 5.963 1.584M12 3v1.75m0 2.25v.008H12V7.008z"/>
            </svg>
            <p class="text-[13px] font-medium text-gray-800 dark:text-zinc-200">No role assignments found</p>
            <p class="text-[11.5px] text-gray-400 dark:text-zinc-500">Click <strong>Assign role</strong> to create the first connection.</p>
        </div>

        <!-- Assignments Table -->
        <div v-else class="rounded-xl overflow-x-auto bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 shadow-sm dark:shadow-none">
            <table class="w-full text-left whitespace-nowrap">
                <caption class="sr-only">Tabel Alokasi Peran Pengguna</caption>
                <thead>
                    <tr class="bg-[#f9fafb] dark:bg-zinc-900 border-b border-gray-200 dark:border-zinc-700">
                        <th class="px-4 py-3 text-[12px] font-semibold text-gray-800 dark:text-zinc-200">User</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-gray-800 dark:text-zinc-200">Module / Organization</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-gray-800 dark:text-zinc-200">Role</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-gray-800 dark:text-zinc-200">Status</th>
                        <th class="px-4 py-3 w-6" />
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-zinc-800 text-sm">
                    <tr
                        v-for="assignment in allAssignments"
                        :key="assignment.id"
                        class="hover:bg-[#f9fafb] dark:hover:bg-zinc-800 dark:bg-zinc-900/50 transition-colors group"
                    >
                        <!-- User Cell -->
                        <td class="px-4 py-3 align-middle">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-[#f3f4f6] dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 flex items-center justify-center text-gray-900 dark:text-zinc-100 text-[12px] font-bold shrink-0 overflow-hidden">
                                    <img v-if="assignment.user_foto" :src="assignment.user_foto" :alt="assignment.user_name" class="w-full h-full object-cover" />
                                    <span v-else>{{ assignment.user_name?.charAt(0)?.toUpperCase() }}</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[13px] font-medium text-gray-900 dark:text-zinc-100 truncate leading-tight">{{ assignment.user_name }}</p>
                                    <p class="text-[12px] text-gray-400 dark:text-zinc-500 truncate leading-tight mt-0.5">{{ assignment.user_email }}</p>
                                </div>
                            </div>
                        </td>
                        
                        <!-- Module Cell -->
                        <td class="px-4 py-3 align-middle">
                            <div>
                                <p class="text-[13px] font-medium text-gray-800 dark:text-zinc-200 leading-tight">{{ assignment.module_name }}</p>
                                <span class="inline-block mt-0.5 font-mono text-[10px] bg-gray-100 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 text-gray-500 dark:text-zinc-400 px-1 rounded">{{ assignment.module_code }}</span>
                            </div>
                        </td>

                        <!-- Role Cell -->
                        <td class="px-4 py-3 align-middle">
                            <div>
                                <span class="text-[13px] font-medium text-gray-800 dark:text-zinc-200 leading-tight">{{ assignment.role_name }}</span>
                            </div>
                        </td>

                        <!-- Status Cell -->
                        <td class="px-4 py-3 align-middle">
                            <span :class="['text-[11px] font-semibold px-2 py-0.5 rounded-full inline-flex items-center gap-1', 
                                assignment.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200']">
                                <span class="w-1.5 h-1.5 rounded-full" :class="assignment.is_active ? 'bg-emerald-500' : 'bg-red-500'"></span>
                                {{ assignment.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>

                        <!-- Actions Cell -->
                        <td class="px-4 py-3 text-right align-middle">
                            <div class="relative flex justify-end">
                                <button class="p-1 rounded text-gray-400 hover:text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-800 dark:bg-zinc-800 opacity-0 group-hover:opacity-100 transition-all" @click.stop="toggleMenu(assignment.id, $event)">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                                    </svg>
                                </button>
                                
                                <div v-if="activeMenu === assignment.id" class="absolute right-0 top-full mt-1 w-36 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-lg shadow-lg z-50 py-1 text-left dark:shadow-none">
                                    <button class="w-full flex items-center gap-2 px-3 py-1.5 text-[13px] text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 dark:bg-zinc-900 transition-colors" @click="openEditModal(assignment)">
                                        <Edit class="w-3.5 h-3.5 text-gray-400 dark:text-zinc-500" />
                                        <span>Edit assignment</span>
                                    </button>
                                    <button class="w-full flex items-center gap-2 px-3 py-1.5 text-[13px] text-red-600 hover:bg-red-50 transition-colors" @click="openRemoveModal(assignment)">
                                        <Trash2 class="w-3.5 h-3.5 text-red-500" />
                                        <span>Remove</span>
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Assign Role Modal -->
        <AppModal
            :show="showAssignModal"
            title="Assign user role"
            @close="showAssignModal = false"
        >
            <template #description>
                Assign a role to a user within a specific module or organization context.
            </template>
            
            <div class="space-y-4 py-2">
                <!-- User Selector -->
                <div>
                    <label class="block text-[13px] font-semibold text-gray-700 dark:text-zinc-300 mb-1.5">User</label>
                    <div class="relative">
                        <select
                            v-model="assignForm.user_id"
                            class="w-full h-9 pl-3 pr-8 text-[13px] border border-gray-300 dark:border-zinc-700 rounded-md focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:ring-zinc-700 bg-white dark:bg-zinc-900 text-gray-900 dark:text-zinc-100"
                        >
                            <option value="" disabled selected>Select a user...</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">
                                {{ u.name }} ({{ u.email }})
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Module Selector -->
                <div>
                    <label class="block text-[13px] font-semibold text-gray-700 dark:text-zinc-300 mb-1.5">Module / Organization</label>
                    <div class="relative">
                        <select
                            v-model="assignForm.module_id"
                            class="w-full h-9 pl-3 pr-8 text-[13px] border border-gray-300 dark:border-zinc-700 rounded-md focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:ring-zinc-700 bg-white dark:bg-zinc-900 text-gray-900 dark:text-zinc-100"
                        >
                            <option value="" disabled selected>Select a module...</option>
                            <option v-for="m in modules" :key="m.id" :value="m.id">
                                {{ m.name }} ({{ m.code }})
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Role Selector -->
                <div>
                    <label class="block text-[13px] font-semibold text-gray-700 dark:text-zinc-300 mb-1.5">Role</label>
                    <div class="relative">
                        <select
                            v-model="assignForm.role_id"
                            class="w-full h-9 pl-3 pr-8 text-[13px] border border-gray-300 dark:border-zinc-700 rounded-md focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:ring-zinc-700 bg-white dark:bg-zinc-900 text-gray-900 dark:text-zinc-100"
                        >
                            <option value="" disabled selected>Select a role...</option>
                            <option v-for="r in roles" :key="r.id" :value="r.id">
                                {{ r.nama }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <template #footer>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-gray-700 dark:text-zinc-300 border border-gray-300 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900"
                    @click="showAssignModal = false"
                >
                    Cancel
                </button>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white transition-colors bg-gray-900 hover:bg-black disabled:opacity-50 flex items-center justify-center gap-1.5"
                    @click="submitAssign"
                    :disabled="isSubmitting || !assignForm.user_id || !assignForm.module_id || !assignForm.role_id"
                >
                    <svg v-if="isSubmitting" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Assign role</span>
                </button>
            </template>
        </AppModal>

        <!-- Edit Assignment Modal -->
        <AppModal
            :show="showEditModal"
            title="Edit role assignment"
            @close="showEditModal = false"
        >
            <template #description>
                Modify role details and active status for <strong class="text-gray-900 dark:text-zinc-100">{{ editingAssignment?.user_name }}</strong> in module <strong class="text-gray-900 dark:text-zinc-100">{{ editingAssignment?.module_name }}</strong>.
            </template>
            
            <div class="space-y-4 py-2" v-if="editingAssignment">
                <!-- Role Selector -->
                <div>
                    <label class="block text-[13px] font-semibold text-gray-700 dark:text-zinc-300 mb-1.5">Role</label>
                    <div class="relative">
                        <select
                            v-model="editForm.role_id"
                            class="w-full h-9 pl-3 pr-8 text-[13px] border border-gray-300 dark:border-zinc-700 rounded-md focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:ring-zinc-700 bg-white dark:bg-zinc-900 text-gray-900 dark:text-zinc-100"
                        >
                            <option v-for="r in roles" :key="r.id" :value="r.id">
                                {{ r.nama }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Active Status Toggle -->
                <div class="flex items-center justify-between py-2.5 border-t border-gray-100 dark:border-zinc-800 mt-2">
                    <div>
                        <p class="text-[13px] font-semibold text-gray-800 dark:text-zinc-200">Assignment Status</p>
                        <p class="text-[11.5px] text-gray-400 dark:text-zinc-500">Temporarily disable or enable this role assignment.</p>
                    </div>
                    <button 
                        @click="editForm.is_active = !editForm.is_active"
                        class="p-1 rounded-md text-gray-500 dark:text-zinc-400 hover:bg-gray-50 dark:hover:bg-zinc-800 dark:bg-zinc-900 transition-colors"
                    >
                        <CheckSquare v-if="editForm.is_active" class="w-5 h-5 text-gray-900 dark:text-zinc-100" />
                        <Square v-else class="w-5 h-5 text-gray-300" />
                    </button>
                </div>
            </div>

            <template #footer>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-gray-700 dark:text-zinc-300 border border-gray-300 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900"
                    @click="showEditModal = false"
                >
                    Cancel
                </button>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-gray-900 hover:bg-black transition-colors disabled:opacity-50 flex items-center justify-center gap-1.5"
                    @click="submitEdit"
                    :disabled="isSubmitting || !editForm.role_id"
                >
                    <svg v-if="isSubmitting" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Save changes</span>
                </button>
            </template>
        </AppModal>

        <!-- Remove Assignment Modal -->
        <AppModal
            :show="showRemoveModal"
            title="Remove assignment"
            type="danger"
            @close="showRemoveModal = false"
        >
            <template #description>
                Are you sure you want to remove the <strong class="text-gray-900 dark:text-zinc-100">{{ selectedToRemove?.role_name }}</strong> assignment for <strong class="text-gray-900 dark:text-zinc-100">{{ selectedToRemove?.user_name }}</strong> in module <strong class="text-gray-900 dark:text-zinc-100">{{ selectedToRemove?.module_name }}</strong>?
                <span class="block mt-1 text-red-600">This action is permanent and cannot be undone.</span>
            </template>

            <template #footer>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-gray-700 dark:text-zinc-300 border border-gray-300 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900"
                    @click="showRemoveModal = false"
                >
                    Cancel
                </button>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-red-600 hover:bg-red-700 transition-colors disabled:opacity-50"
                    :disabled="isSubmitting"
                    @click="submitRemove"
                >
                    <span>Remove assignment</span>
                </button>
            </template>
        </AppModal>
    </div>
</template>