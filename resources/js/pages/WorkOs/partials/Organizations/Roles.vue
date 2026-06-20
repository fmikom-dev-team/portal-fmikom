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

const orgRoles = ref<any[]>([...(props.organization?.roles || [])]);
const search = ref("");

watch(
	() => props.organization?.roles,
	(newVal) => {
		orgRoles.value = [...(newVal || [])];
	},
	{ deep: true },
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
	orgRoles.value = [...localRoles.value];
	toast("Role priorities updated.", "success");
	modal.priority = false;
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
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <!-- Search -->
                <div class="relative">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#9ca3af]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" />
                    </svg>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search roles..."
                        class="w-[280px] h-[34px] pl-8 pr-3 text-[13px] border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors placeholder:text-[#9ca3af] text-[#111827]"
                    />
                </div>
                <!-- Role type filter -->
                <button class="flex items-center gap-1.5 h-[34px] px-3 border border-[#d1d5db] rounded-md text-[13px] text-[#4b5563] hover:bg-[#f9fafb] transition-colors">
                    <svg class="w-3.5 h-3.5 text-[#9ca3af]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Role type
                </button>
            </div>
            
            <div class="flex items-center gap-2">
                <button
                    class="h-[34px] px-3.5 border border-[#d1d5db] rounded-md text-[13px] font-semibold text-[#374151] hover:bg-[#f9fafb] transition-colors"
                    @click="openPriorityModal"
                >
                    Edit priority
                </button>
                <button 
                    class="h-[34px] px-3.5 bg-[#2563eb] text-white rounded-md text-[13px] font-semibold hover:bg-[#1d4ed8] transition-colors"
                    @click="openAdd"
                >
                    Add role
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-xl overflow-hidden bg-white ring-1 ring-gray-900/[0.04]">
            <table class="w-full text-left whitespace-nowrap">
                <caption class="sr-only">Associated Roles</caption>
                <thead>
                    <tr class="bg-[#f9fafb] border-b border-[#e5e7eb]">
                        <th class="px-4 py-3 text-[12px] font-semibold text-[#111827]">Name</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-[#111827] w-[30%]">Slug</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-[#111827] w-[40%]">Permissions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e5e7eb]">
                    <tr v-if="!filteredRoles.length">
                        <td colspan="4" class="px-4 py-8 text-center text-[13px] text-[#6b7280]">
                            No roles associated with this organization.
                        </td>
                    </tr>
                    <tr
                        v-for="(role, index) in filteredRoles"
                        :key="role.id"
                        class="hover:bg-[#f9fafb] transition-colors group"
                    >
                        <td class="px-4 py-3">
                            <div class="flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-[#9ca3af] mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <div>
                                    <p class="text-[13px] font-semibold text-[#111827]">{{ role.nama || role.name }}</p>
                                    <p class="text-[12px] text-[#6b7280]">{{ role.deskripsi || role.description || 'Custom role' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11.5px] font-mono font-medium text-[#4b5563] bg-[#f3f4f6]">
                                {{ role.slug }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-between">
                                <span :class="role.permissions_count === 0 ? 'text-[13px] text-[#9ca3af]' : 'text-[13px] text-[#4b5563] underline decoration-dashed underline-offset-4'">
                                    {{ role.permissions_count === 0 ? 'None' : `${role.permissions_count} permissions` }}
                                </span>
                                
                                <div class="flex items-center gap-3 relative">
                                    <span v-if="role.id === orgRoles[0]?.id" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold text-blue-700 border border-blue-200 bg-blue-50">
                                        Default
                                    </span>
                                    <button 
                                        class="p-1 rounded text-[#9ca3af] hover:text-[#374151] hover:bg-[#e5e7eb] opacity-0 group-hover:opacity-100 transition-all"
                                        @click.stop="toggleMenu(role.id, $event)"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                                        </svg>
                                    </button>
                                    
                                    <div v-if="activeMenu === role.id" class="absolute right-0 top-full mt-1 w-36 bg-white border border-[#e5e7eb] rounded-lg shadow-lg z-50 py-1">
                                        <button class="w-full flex items-center gap-2 px-3 py-1.5 text-[13px] text-[#ef4444] hover:bg-[#fef2f2] text-left transition-colors" @click="openDelete(role)">
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

        <!-- Edit Priority Modal -->
        <AppModal
            :show="modal.priority"
            title="Edit priority"
            @close="closePriorityModal"
        >
            <template #description>
                When an organization member has different roles provisioned from different groups, the role with a higher priority will be used. Drag roles to change the priority, from highest to lowest.
            </template>
            
            <div class="rounded-xl divide-y divide-gray-100 ring-1 ring-gray-900/[0.04]">
                <div
                    v-for="(role, index) in localRoles"
                    :key="role.id"
                    draggable="true"
                    @dragstart="onDragStart(index)"
                    @dragover.prevent
                    @dragenter.prevent
                    @drop="onDrop(index)"
                    :class="['flex items-center justify-between p-3 bg-white hover:bg-[#f9fafb] cursor-move transition-colors', draggedItem === index ? 'opacity-50' : '']"
                >
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#d1d5db]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16" />
                        </svg>
                        <svg class="w-4 h-4 text-[#9ca3af]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span class="text-[13.5px] font-medium text-[#374151]">{{ role.nama || role.name }}</span>
                    </div>
                    <span v-if="index === 0" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold text-blue-700 border border-blue-200 bg-blue-50">
                        Default
                    </span>
                </div>
            </div>

            <template #footer>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] border border-[#d1d5db] hover:bg-[#f3f4f6] transition-colors bg-white shadow-sm"
                    @click="closePriorityModal"
                >
                    Cancel
                </button>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-[#2563eb] hover:bg-[#1d4ed8] transition-colors shadow-sm"
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
                    <label for="select_role" class="block text-[13px] font-semibold text-[#374151] mb-1.5">Select Role</label>
                    <select
                        id="select_role"
                        v-model="selectedRoleId"
                        class="w-full h-9 px-3 text-[13px] border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] transition-colors text-[#111827] bg-white"
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
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] border border-[#d1d5db] hover:bg-[#f9fafb] transition-colors shadow-sm"
                    @click="modal.add = false"
                >
                    Cancel
                </button>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-[#2563eb] hover:bg-[#1d4ed8] transition-colors shadow-sm relative disabled:opacity-50"
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
                Are you sure you want to remove the role <strong class="text-[#111827]">{{ deletingRole?.nama }}</strong> from this organization? This will only remove their access to this organization, not delete the role entirely.
            </template>

            <template #footer>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] border border-[#d1d5db] hover:bg-[#f9fafb] transition-colors shadow-sm"
                    @click="modal.delete = false"
                >
                    Cancel
                </button>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-[#ef4444] hover:bg-[#dc2626] transition-colors shadow-sm disabled:opacity-50"
                    :disabled="isSubmitting"
                    @click="confirmDelete"
                >
                    <span :class="{ 'opacity-0': isSubmitting }">Remove role</span>
                </button>
            </template>
        </AppModal>
    </div>
</template>