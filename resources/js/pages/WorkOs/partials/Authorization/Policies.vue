<script setup lang="ts">
import {
	CheckSquare,
	Edit,
	Eye,
	Plus,
	Shield,
	ShieldAlert,
	ShieldCheck,
	Square,
	Trash2,
} from "lucide-vue-next";
import { computed, ref } from "vue";
import AppModal from "../../components/ui/AppModal.vue";
import { toast } from "../../composables/useWorkOs";

// Initial mock data for policies
const policies = ref([
	{
		id: 1,
		name: "Allow Dosen access to Magang Module",
		description:
			"Grants academic advisors access to manage internship logbooks and grades.",
		effect: "Allow",
		status: "Active",
		roles: "Dosen",
	},
	{
		id: 2,
		name: "Allow Mitra access to Lowongan Module",
		description:
			"Grants corporate partners permission to post and manage job opportunities.",
		effect: "Allow",
		status: "Active",
		roles: "Mitra",
	},
	{
		id: 3,
		name: "Deny suspended user sign-ins",
		description:
			"Blocks sign-in attempts for accounts flagged as inactive or suspended.",
		effect: "Deny",
		status: "Active",
		roles: "All Roles",
	},
	{
		id: 4,
		name: "Enforce MFA for Administrators",
		description:
			"Requires multi-factor authentication for any user with admin roles.",
		effect: "Allow",
		status: "Inactive",
		roles: "Admin, Super Admin",
	},
]);

const search = ref("");
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showRemoveModal = ref(false);
const isSubmitting = ref(false);

const policyForm = ref({
	name: "",
	description: "",
	effect: "Allow",
	status: "Active",
	roles: "All Roles",
});

const editingPolicy = ref<any>(null);
const selectedToRemove = ref<any>(null);

const filteredPolicies = computed(() => {
	return policies.value.filter((p) => {
		if (!search.value.trim()) return true;
		const q = search.value.toLowerCase();
		return (
			p.name.toLowerCase().includes(q) ||
			p.description.toLowerCase().includes(q) ||
			p.roles.toLowerCase().includes(q)
		);
	});
});

function openCreateModal() {
	policyForm.value = {
		name: "",
		description: "",
		effect: "Allow",
		status: "Active",
		roles: "All Roles",
	};
	showCreateModal.value = true;
}

function submitCreate() {
	if (!policyForm.value.name) return;
	isSubmitting.value = true;

	setTimeout(() => {
		policies.value.unshift({
			id: Date.now(),
			name: policyForm.value.name,
			description: policyForm.value.description,
			effect: policyForm.value.effect,
			status: policyForm.value.status,
			roles: policyForm.value.roles,
		});
		isSubmitting.value = false;
		showCreateModal.value = false;
		toast("Policy created successfully.", "success");
	}, 400);
}

function openEditModal(policy: any) {
	editingPolicy.value = policy;
	policyForm.value = {
		name: policy.name,
		description: policy.description,
		effect: policy.effect,
		status: policy.status,
		roles: policy.roles,
	};
	showEditModal.value = true;
}

function submitEdit() {
	if (!editingPolicy.value || !policyForm.value.name) return;
	isSubmitting.value = true;

	setTimeout(() => {
		const idx = policies.value.findIndex(
			(p) => p.id === editingPolicy.value.id,
		);
		if (idx !== -1) {
			policies.value[idx] = {
				...policies.value[idx],
				name: policyForm.value.name,
				description: policyForm.value.description,
				effect: policyForm.value.effect,
				status: policyForm.value.status,
				roles: policyForm.value.roles,
			};
		}
		isSubmitting.value = false;
		showEditModal.value = false;
		toast("Policy updated successfully.", "success");
	}, 400);
}

function openRemoveModal(policy: any) {
	selectedToRemove.value = policy;
	showRemoveModal.value = true;
}

function submitRemove() {
	if (!selectedToRemove.value) return;
	isSubmitting.value = true;

	setTimeout(() => {
		policies.value = policies.value.filter(
			(p) => p.id !== selectedToRemove.value.id,
		);
		isSubmitting.value = false;
		showRemoveModal.value = false;
		toast("Policy deleted successfully.", "success");
	}, 400);
}
</script>

<template>
    <div style="font-family: var(--wos-font)" class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-[20px] font-semibold text-gray-900 tracking-tight">Authorization Policies</h1>
                <p class="text-[13px] text-gray-500 mt-0.5">Configure access rules and security guardrails applied during evaluations.</p>
            </div>
            
            <button
                class="h-[34px] px-4 bg-gray-900 text-white rounded-md text-[13px] font-semibold hover:bg-black transition-colors shadow-sm flex items-center gap-1.5 self-start sm:self-auto"
                @click="openCreateModal"
            >
                <Plus class="w-4 h-4" />
                <span>Create policy</span>
            </button>
        </div>

        <!-- Toolbar -->
        <div class="flex items-center gap-2">
            <div class="relative flex-1 max-w-sm">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" />
                </svg>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search policies by name or role..."
                    class="w-full h-[34px] pl-8 pr-3 text-[13px] border border-gray-200 rounded-md focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 transition-colors placeholder:text-gray-400 text-gray-900 bg-white"
                />
            </div>
        </div>

        <!-- Table -->
        <div v-if="filteredPolicies.length" class="rounded-xl overflow-x-auto bg-white border border-gray-200 shadow-sm">
            <table class="w-full text-left whitespace-nowrap">
                <caption class="sr-only">Authorization Policies</caption>
                <thead>
                    <tr class="bg-[#f9fafb] border-b border-gray-200">
                        <th class="px-4 py-3 text-[12px] font-semibold text-gray-800">Policy Name</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-gray-800">Effect</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-gray-800">Roles Applied</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-gray-800">Status</th>
                        <th class="px-4 py-3 w-28 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <tr
                        v-for="policy in filteredPolicies"
                        :key="policy.id"
                        class="hover:bg-[#f9fafb]/50 transition-colors group"
                    >
                        <td class="px-4 py-3 align-middle max-w-sm">
                            <div>
                                <p class="text-[13.5px] font-semibold text-gray-900 leading-tight">{{ policy.name }}</p>
                                <p class="text-[11.5px] text-gray-400 mt-1 leading-normal whitespace-pre-wrap">{{ policy.description }}</p>
                            </div>
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <span :class="['text-[11px] font-semibold px-2 py-0.5 rounded-full inline-flex items-center gap-1.5',
                                policy.effect === 'Allow' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200']">
                                <ShieldCheck v-if="policy.effect === 'Allow'" class="w-3.5 h-3.5" />
                                <ShieldAlert v-else class="w-3.5 h-3.5" />
                                {{ policy.effect }}
                            </span>
                        </td>
                        <td class="px-4 py-3 align-middle text-gray-600 text-[12.5px]">
                            {{ policy.roles }}
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <span :class="['text-[11px] font-medium px-2 py-0.5 rounded-full inline-flex items-center gap-1',
                                policy.status === 'Active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-gray-100 text-gray-500 border border-gray-200']">
                                <span class="w-1.5 h-1.5 rounded-full" :class="policy.status === 'Active' ? 'bg-emerald-500' : 'bg-gray-400'"></span>
                                {{ policy.status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right align-middle">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button 
                                    @click="openEditModal(policy)"
                                    class="h-6 px-2.5 rounded-md text-[11px] text-gray-600 border border-gray-200 hover:bg-gray-100 transition-colors"
                                >
                                    Edit
                                </button>
                                <button 
                                    @click="openRemoveModal(policy)"
                                    class="h-6 px-2.5 rounded-md text-[11px] text-red-500 border border-red-200 hover:bg-red-50 transition-colors"
                                >
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-else class="rounded-xl bg-[#f9fafb] border border-dashed border-gray-200 p-12 flex flex-col items-center justify-center text-center gap-2">
            <Shield class="w-8 h-8 text-gray-300" />
            <p class="text-[13px] font-medium text-gray-800">No policies found</p>
            <p class="text-[11.5px] text-gray-400">Try adjusting your search criteria or create a new policy.</p>
        </div>

        <!-- Create Modal -->
        <AppModal
            :show="showCreateModal"
            title="Create authorization policy"
            @close="showCreateModal = false"
        >
            <template #description>
                Define a new rule that evaluates access rights dynamically.
            </template>
            
            <div class="space-y-4 py-2">
                <div>
                    <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Policy Name</label>
                    <input
                        v-model="policyForm.name"
                        type="text"
                        placeholder="e.g. Require MFA for admin operations"
                        class="w-full h-9 px-3 text-[13px] border border-gray-300 rounded-md focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 bg-white text-gray-900"
                    />
                </div>

                <div>
                    <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Description</label>
                    <textarea
                        v-model="policyForm.description"
                        placeholder="Explain when and why this policy applies..."
                        rows="3"
                        class="w-full px-3 py-2 text-[13px] border border-gray-300 rounded-md focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 bg-white text-gray-900 resize-none"
                    ></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Effect</label>
                        <select
                            v-model="policyForm.effect"
                            class="w-full h-9 px-3 text-[13px] border border-gray-300 rounded-md focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 bg-white text-gray-900"
                        >
                            <option value="Allow">Allow</option>
                            <option value="Deny">Deny</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Status</label>
                        <select
                            v-model="policyForm.status"
                            class="w-full h-9 px-3 text-[13px] border border-gray-300 rounded-md focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 bg-white text-gray-900"
                        >
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Applied Roles</label>
                    <input
                        v-model="policyForm.roles"
                        type="text"
                        placeholder="e.g. Admin, Super Admin or All Roles"
                        class="w-full h-9 px-3 text-[13px] border border-gray-300 rounded-md focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 bg-white text-gray-900"
                    />
                </div>
            </div>

            <template #footer>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-gray-700 border border-gray-300 hover:bg-gray-50 transition-colors bg-white"
                    @click="showCreateModal = false"
                >
                    Cancel
                </button>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-gray-900 hover:bg-black transition-colors disabled:opacity-50 flex items-center justify-center gap-1.5"
                    @click="submitCreate"
                    :disabled="isSubmitting || !policyForm.name"
                >
                    <svg v-if="isSubmitting" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Create policy</span>
                </button>
            </template>
        </AppModal>

        <!-- Edit Modal -->
        <AppModal
            :show="showEditModal"
            title="Edit authorization policy"
            @close="showEditModal = false"
        >
            <div class="space-y-4 py-2">
                <div>
                    <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Policy Name</label>
                    <input
                        v-model="policyForm.name"
                        type="text"
                        class="w-full h-9 px-3 text-[13px] border border-gray-300 rounded-md focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 bg-white text-gray-900"
                    />
                </div>

                <div>
                    <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Description</label>
                    <textarea
                        v-model="policyForm.description"
                        rows="3"
                        class="w-full px-3 py-2 text-[13px] border border-gray-300 rounded-md focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 bg-white text-gray-900 resize-none"
                    ></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Effect</label>
                        <select
                            v-model="policyForm.effect"
                            class="w-full h-9 px-3 text-[13px] border border-gray-300 rounded-md focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 bg-white text-gray-900"
                        >
                            <option value="Allow">Allow</option>
                            <option value="Deny">Deny</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Status</label>
                        <select
                            v-model="policyForm.status"
                            class="w-full h-9 px-3 text-[13px] border border-gray-300 rounded-md focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 bg-white text-gray-900"
                        >
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Applied Roles</label>
                    <input
                        v-model="policyForm.roles"
                        type="text"
                        class="w-full h-9 px-3 text-[13px] border border-gray-300 rounded-md focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 bg-white text-gray-900"
                    />
                </div>
            </div>

            <template #footer>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-gray-700 border border-gray-300 hover:bg-gray-50 transition-colors bg-white"
                    @click="showEditModal = false"
                >
                    Cancel
                </button>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-gray-900 hover:bg-black transition-colors disabled:opacity-50 flex items-center justify-center gap-1.5"
                    @click="submitEdit"
                    :disabled="isSubmitting || !policyForm.name"
                >
                    <svg v-if="isSubmitting" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Save changes</span>
                </button>
            </template>
        </AppModal>

        <!-- Remove Modal -->
        <AppModal
            :show="showRemoveModal"
            title="Delete policy"
            type="danger"
            @close="showRemoveModal = false"
        >
            <template #description>
                Are you sure you want to delete the policy <strong class="text-gray-900">{{ selectedToRemove?.name }}</strong>?
                <span class="block mt-1 text-red-600">This action is permanent and cannot be undone.</span>
            </template>

            <template #footer>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-gray-700 border border-gray-300 hover:bg-gray-50 transition-colors bg-white"
                    @click="showRemoveModal = false"
                >
                    Cancel
                </button>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-red-600 hover:bg-red-700 transition-colors disabled:opacity-50"
                    :disabled="isSubmitting"
                    @click="submitRemove"
                >
                    <span>Delete policy</span>
                </button>
            </template>
        </AppModal>
    </div>
</template>