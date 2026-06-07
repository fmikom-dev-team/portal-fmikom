<script setup lang="ts">
import { Edit, FolderSquarePlay, Plus, Trash2, Users } from "lucide-vue-next";
import { computed, ref } from "vue";
import AppModal from "../../components/ui/AppModal.vue";
import { toast } from "../../composables/useWorkOs";

// Initial mock data for groups
const groups = ref([
	{
		id: 1,
		name: "Administrators",
		description:
			"Users with full access to manage all portal configurations and modules.",
		membersCount: 3,
		created_at: "May 10, 2026",
	},
	{
		id: 2,
		name: "Dosen Penguji",
		description:
			"Academic supervisors and examiners who can grade student internships.",
		membersCount: 12,
		created_at: "May 12, 2026",
	},
	{
		id: 3,
		name: "Mahasiswa Magang",
		description: "Students enrolled in active internships with logbook access.",
		membersCount: 120,
		created_at: "May 14, 2026",
	},
	{
		id: 4,
		name: "Mitra Perusahaan",
		description: "External corporate partners publishing job postings.",
		membersCount: 15,
		created_at: "May 15, 2026",
	},
]);

const search = ref("");
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showRemoveModal = ref(false);
const isSubmitting = ref(false);

const groupForm = ref({
	name: "",
	description: "",
	membersCount: 0,
});

const editingGroup = ref<any>(null);
const selectedToRemove = ref<any>(null);

const filteredGroups = computed(() => {
	return groups.value.filter((g) => {
		if (!search.value.trim()) return true;
		const q = search.value.toLowerCase();
		return (
			g.name.toLowerCase().includes(q) ||
			g.description.toLowerCase().includes(q)
		);
	});
});

function openCreateModal() {
	groupForm.value = {
		name: "",
		description: "",
		membersCount: 0,
	};
	showCreateModal.value = true;
}

function submitCreate() {
	if (!groupForm.value.name) return;
	isSubmitting.value = true;

	setTimeout(() => {
		groups.value.unshift({
			id: Date.now(),
			name: groupForm.value.name,
			description: groupForm.value.description,
			membersCount: 0,
			created_at: new Date().toLocaleDateString("en-US", {
				month: "short",
				day: "numeric",
				year: "numeric",
			}),
		});
		isSubmitting.value = false;
		showCreateModal.value = false;
		toast("Group created successfully.", "success");
	}, 400);
}

function openEditModal(group: any) {
	editingGroup.value = group;
	groupForm.value = {
		name: group.name,
		description: group.description,
		membersCount: group.membersCount,
	};
	showEditModal.value = true;
}

function submitEdit() {
	if (!editingGroup.value || !groupForm.value.name) return;
	isSubmitting.value = true;

	setTimeout(() => {
		const idx = groups.value.findIndex((g) => g.id === editingGroup.value.id);
		if (idx !== -1) {
			groups.value[idx] = {
				...groups.value[idx],
				name: groupForm.value.name,
				description: groupForm.value.description,
				membersCount: groupForm.value.membersCount,
			};
		}
		isSubmitting.value = false;
		showEditModal.value = false;
		toast("Group updated successfully.", "success");
	}, 400);
}

function openRemoveModal(group: any) {
	selectedToRemove.value = group;
	showRemoveModal.value = true;
}

function submitRemove() {
	if (!selectedToRemove.value) return;
	isSubmitting.value = true;

	setTimeout(() => {
		groups.value = groups.value.filter(
			(g) => g.id !== selectedToRemove.value.id,
		);
		isSubmitting.value = false;
		showRemoveModal.value = false;
		toast("Group deleted successfully.", "success");
	}, 400);
}
</script>

<template>
    <div style="font-family: var(--wos-font)" class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-[20px] font-semibold text-gray-900 tracking-tight">Authorization Groups</h1>
                <p class="text-[13px] text-gray-500 mt-0.5">Organize users into logical groups to apply permissions and policies collectively.</p>
            </div>
            
            <button
                class="h-[34px] px-4 bg-gray-900 text-white rounded-md text-[13px] font-semibold hover:bg-black transition-colors shadow-sm flex items-center gap-1.5 self-start sm:self-auto"
                @click="openCreateModal"
            >
                <Plus class="w-4 h-4" />
                <span>Create group</span>
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
                    placeholder="Search groups by name..."
                    class="w-full h-[34px] pl-8 pr-3 text-[13px] border border-gray-200 rounded-md focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 transition-colors placeholder:text-gray-400 text-gray-900 bg-white"
                />
            </div>
        </div>

        <!-- Table -->
        <div v-if="filteredGroups.length" class="rounded-xl overflow-x-auto bg-white border border-gray-200 shadow-sm">
            <table class="w-full text-left whitespace-nowrap">
                <thead>
                    <tr class="bg-[#f9fafb] border-b border-gray-200">
                        <th class="px-4 py-3 text-[12px] font-semibold text-gray-800">Group Name</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-gray-800">Description</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-gray-800">Members</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-gray-800">Created</th>
                        <th class="px-4 py-3 w-28 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <tr
                        v-for="group in filteredGroups"
                        :key="group.id"
                        class="hover:bg-[#f9fafb]/50 transition-colors group"
                    >
                        <td class="px-4 py-3 align-middle font-semibold text-gray-900">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-md bg-gray-50 border border-gray-100 flex items-center justify-center text-gray-500">
                                    <Users class="w-3.5 h-3.5" />
                                </div>
                                <span>{{ group.name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 align-middle text-gray-500 max-w-xs truncate" :title="group.description">
                            {{ group.description }}
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 border border-gray-200 text-gray-700 font-mono">
                                {{ group.membersCount }} user{{ group.membersCount !== 1 ? 's' : '' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 align-middle text-gray-500 text-[12.5px]">
                            {{ group.created_at }}
                        </td>
                        <td class="px-4 py-3 text-right align-middle">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button 
                                    @click="openEditModal(group)"
                                    class="h-6 px-2.5 rounded-md text-[11px] text-gray-600 border border-gray-200 hover:bg-gray-100 transition-colors"
                                >
                                    Edit
                                </button>
                                <button 
                                    @click="openRemoveModal(group)"
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
            <Users class="w-8 h-8 text-gray-300" />
            <p class="text-[13px] font-medium text-gray-800">No groups found</p>
            <p class="text-[11.5px] text-gray-400">Try adjusting your search criteria or create a new group.</p>
        </div>

        <!-- Create Modal -->
        <AppModal
            :show="showCreateModal"
            title="Create user group"
            @close="showCreateModal = false"
        >
            <template #description>
                Group users together to simplify access policy mapping.
            </template>
            
            <div class="space-y-4 py-2">
                <div>
                    <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Group Name</label>
                    <input
                        v-model="groupForm.name"
                        type="text"
                        placeholder="e.g. Internship Advisors"
                        class="w-full h-9 px-3 text-[13px] border border-gray-300 rounded-md focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 bg-white text-gray-900"
                    />
                </div>

                <div>
                    <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Description</label>
                    <textarea
                        v-model="groupForm.description"
                        placeholder="Provide details about who belongs to this group..."
                        rows="3"
                        class="w-full px-3 py-2 text-[13px] border border-gray-300 rounded-md focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 bg-white text-gray-900 resize-none"
                    ></textarea>
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
                    :disabled="isSubmitting || !groupForm.name"
                >
                    <svg v-if="isSubmitting" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Create group</span>
                </button>
            </template>
        </AppModal>

        <!-- Edit Modal -->
        <AppModal
            :show="showEditModal"
            title="Edit user group"
            @close="showEditModal = false"
        >
            <div class="space-y-4 py-2">
                <div>
                    <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Group Name</label>
                    <input
                        v-model="groupForm.name"
                        type="text"
                        class="w-full h-9 px-3 text-[13px] border border-gray-300 rounded-md focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 bg-white text-gray-900"
                    />
                </div>

                <div>
                    <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Description</label>
                    <textarea
                        v-model="groupForm.description"
                        rows="3"
                        class="w-full px-3 py-2 text-[13px] border border-gray-300 rounded-md focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 bg-white text-gray-900 resize-none"
                    ></textarea>
                </div>

                <div>
                    <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Members Count</label>
                    <input
                        v-model.number="groupForm.membersCount"
                        type="number"
                        min="0"
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
                    :disabled="isSubmitting || !groupForm.name"
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
            title="Delete user group"
            type="danger"
            @close="showRemoveModal = false"
        >
            <template #description>
                Are you sure you want to delete the group <strong class="text-gray-900">{{ selectedToRemove?.name }}</strong>?
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
                    <span>Delete group</span>
                </button>
            </template>
        </AppModal>
    </div>
</template>