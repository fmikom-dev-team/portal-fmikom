<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { computed, reactive, ref } from "vue";
import { toast, toSlug } from "../../composables/useWorkOs";

const props = defineProps<{ permissions: Array<any> }>();

const permSearch = ref("");
const filteredPerms = computed(() => {
	if (!permSearch.value.trim()) return props.permissions;
	const q = permSearch.value.toLowerCase();
	return props.permissions.filter(
		(p) =>
			p.name?.toLowerCase().includes(q) ||
			p.slug?.toLowerCase().includes(q) ||
			p.group?.toLowerCase().includes(q),
	);
});
const groupedPerms = computed(() => {
	const g: Record<string, any[]> = {};
	filteredPerms.value.forEach((p) => {
		if (!g[p.group]) g[p.group] = [];
		g[p.group].push(p);
	});
	return g;
});

const modal = reactive({
	createPerm: false,
	editPerm: false,
	deletePerm: false,
});
const permForm = reactive({
	name: "",
	slug: "",
	group: "general",
	description: "",
});
const editingPerm = ref<any>(null);
const deletingPerm = ref<any>(null);
const loading = ref(false);

function autoSlug() {
	permForm.slug = toSlug(permForm.name);
}

function openCreate() {
	Object.assign(permForm, {
		name: "",
		slug: "",
		group: "general",
		description: "",
	});
	modal.createPerm = true;
}
function submitCreate() {
	loading.value = true;
	router.post(
		"/workos/permissions",
		{ ...permForm },
		{
			onSuccess: () => {
				modal.createPerm = false;
				toast("Permission dibuat.");
			},
			onError: () => toast("Gagal membuat permission.", "error"),
			onFinish: () => {
				loading.value = false;
			},
		},
	);
}

function openEdit(p: any) {
	editingPerm.value = p;
	Object.assign(permForm, {
		name: p.name,
		slug: p.slug,
		group: p.group,
		description: p.description ?? "",
	});
	modal.editPerm = true;
}
function submitEdit() {
	if (!editingPerm.value) return;
	loading.value = true;
	router.patch(
		`/workos/permissions/${editingPerm.value.id}`,
		{ ...permForm },
		{
			onSuccess: () => {
				modal.editPerm = false;
				toast("Permission diperbarui.");
			},
			onFinish: () => {
				loading.value = false;
			},
		},
	);
}

function openDelete(p: any) {
	deletingPerm.value = p;
	modal.deletePerm = true;
}
function confirmDelete() {
	if (!deletingPerm.value) return;
	router.delete(`/workos/permissions/${deletingPerm.value.id}`, {
		onSuccess: () => {
			modal.deletePerm = false;
			toast("Permission dihapus.", "error");
		},
	});
}
</script>

<template>
    <div class="p-6 md:p-8">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-[22px] font-semibold text-gray-900 tracking-tight mb-1">Permissions</h1>
                <p class="text-[13px] text-gray-500">Define granular permissions that can be assigned to roles.</p>
            </div>
        </div>

        <!-- Controls -->
        <div class="flex items-center gap-2 mb-5">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.3-4.3M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
                <input v-model="permSearch" type="search" placeholder="Search permissions…" class="h-9 w-56 pl-9 pr-3 text-[13px] bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 placeholder:text-gray-400 transition-all"/>
            </div>
            <div class="flex-1"/>
            <button class="h-9 px-4 rounded-lg text-[13px] font-medium bg-gray-900 hover:bg-black text-white shadow-sm transition-colors flex items-center gap-1.5" @click="openCreate">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Create permission
            </button>
        </div>

        <!-- Empty state -->
        <div v-if="!permissions.length" class="bg-white border border-dashed border-gray-200 rounded-xl p-12 text-center">
            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <p class="text-[14px] font-medium text-gray-700 mb-1">No permissions yet</p>
            <p class="text-[12px] text-gray-400 mb-4">Create your first permission to control access granularly.</p>
            <button class="h-8 px-4 rounded-lg text-[12px] font-medium bg-gray-900 text-white hover:bg-black transition-colors" @click="openCreate">Create permission</button>
        </div>

        <!-- Grouped tables -->
        <div v-else class="space-y-6">
            <div v-for="(perms, group) in groupedPerms" :key="group">
                <div class="flex items-center gap-2 mb-2">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest capitalize">{{ group }}</p>
                    <span class="text-[10px] text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded tabular-nums">{{ perms.length }}</span>
                </div>
                <div class="bg-white border border-gray-200 rounded-xl overflow-x-auto shadow-sm">
                    <table class="w-full text-left whitespace-nowrap">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/80">
                                <th class="px-4 py-2.5 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-4 py-2.5 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Slug</th>
                                <th class="px-4 py-2.5 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Assigned to</th>
                                <th class="px-4 py-2.5 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-4 py-2.5 w-24 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="p in perms" :key="p.id" class="border-b border-gray-50 last:border-0 hover:bg-gray-50/60 group transition-colors">
                                <td class="px-4 py-3 text-[13px] font-medium text-gray-900">{{ p.name }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-mono text-[11px] bg-gray-100 px-2 py-0.5 rounded border border-gray-200 text-gray-600">{{ p.slug }}</span>
                                </td>
                                <td class="px-4 py-3 text-[13px]">
                                    <span v-if="p.roles_count > 0" class="font-medium text-gray-900">{{ p.roles_count }} role{{ p.roles_count !== 1 ? 's' : '' }}</span>
                                    <span v-else class="text-gray-300">None</span>
                                </td>
                                <td class="px-4 py-3 text-[12px] text-gray-400">{{ p.description || '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button class="h-6 px-2.5 rounded-md text-[11px] text-gray-600 border border-gray-200 hover:bg-gray-100 transition-colors" @click="openEdit(p)">Edit</button>
                                        <button class="h-6 px-2.5 rounded-md text-[11px] text-red-500 border border-red-200 hover:bg-red-50 transition-colors" @click="openDelete(p)">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- MODALS -->
        <Teleport to="body">
            <!-- Create -->
            <div v-if="modal.createPerm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-[2px] p-4" @click.self="modal.createPerm = false">
                <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-md">
                    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                        <div><h2 class="text-[16px] font-semibold text-gray-900">Create permission</h2><p class="text-[12px] text-gray-400 mt-0.5">Add a new granular permission.</p></div>
                        <button class="p-1.5 rounded-md text-gray-400 hover:bg-gray-100 transition-colors" @click="modal.createPerm = false"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <div class="px-6 py-5 space-y-4">
                        <div><label class="block text-[12px] font-semibold text-gray-700 mb-1.5">Permission name</label><input v-model="permForm.name" type="text" placeholder="e.g. Manage Users" class="w-full h-9 px-3 text-[13px] border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 placeholder:text-gray-300 transition-all" @input="autoSlug"/></div>
                        <div><label class="block text-[12px] font-semibold text-gray-700 mb-1.5">Slug</label>
                            <div class="flex h-9 border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-400 transition-all">
                                <span class="px-3 text-[11px] font-mono text-gray-400 bg-gray-50 flex items-center border-r border-gray-200">slug:</span>
                                <input v-model="permForm.slug" type="text" placeholder="manage-users" class="flex-1 px-3 text-[12px] font-mono focus:outline-none placeholder:text-gray-300"/>
                            </div>
                        </div>
                        <div><label class="block text-[12px] font-semibold text-gray-700 mb-1.5">Group</label><input v-model="permForm.group" type="text" placeholder="e.g. users, roles, modules" class="w-full h-9 px-3 text-[13px] border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 placeholder:text-gray-300 transition-all"/></div>
                        <div><label class="block text-[12px] font-semibold text-gray-700 mb-1.5">Description <span class="font-normal text-gray-400">(optional)</span></label><input v-model="permForm.description" type="text" placeholder="Short description" class="w-full h-9 px-3 text-[13px] border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 placeholder:text-gray-300 transition-all"/></div>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                        <button class="h-9 px-4 rounded-lg text-[13px] border border-gray-200 text-gray-700 hover:bg-gray-50 transition-colors" @click="modal.createPerm = false">Cancel</button>
                        <button :disabled="loading" class="h-9 px-5 rounded-lg text-[13px] font-semibold bg-gray-900 hover:bg-black text-white shadow-sm transition-colors disabled:opacity-40" @click="submitCreate">{{ loading ? 'Creating…' : 'Create permission' }}</button>
                    </div>
                </div>
            </div>

            <!-- Edit -->
            <div v-if="modal.editPerm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-[2px] p-4" @click.self="modal.editPerm = false">
                <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-md">
                    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                        <h2 class="text-[16px] font-semibold text-gray-900">Edit permission</h2>
                        <button class="p-1.5 rounded-md text-gray-400 hover:bg-gray-100 transition-colors" @click="modal.editPerm = false"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <div class="px-6 py-5 space-y-4">
                        <div><label class="block text-[12px] font-semibold text-gray-700 mb-1.5">Name</label><input v-model="permForm.name" type="text" class="w-full h-9 px-3 text-[13px] border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition-all"/></div>
                        <div><label class="block text-[12px] font-semibold text-gray-700 mb-1.5">Slug</label>
                            <div class="flex h-9 border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-400 transition-all">
                                <span class="px-3 text-[11px] font-mono text-gray-400 bg-gray-50 flex items-center border-r border-gray-200">slug:</span>
                                <input v-model="permForm.slug" type="text" class="flex-1 px-3 text-[12px] font-mono focus:outline-none"/>
                            </div>
                        </div>
                        <div><label class="block text-[12px] font-semibold text-gray-700 mb-1.5">Group</label><input v-model="permForm.group" type="text" class="w-full h-9 px-3 text-[13px] border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition-all"/></div>
                        <div><label class="block text-[12px] font-semibold text-gray-700 mb-1.5">Description</label><input v-model="permForm.description" type="text" class="w-full h-9 px-3 text-[13px] border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition-all"/></div>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                        <button class="h-9 px-4 rounded-lg text-[13px] border border-gray-200 text-gray-700 hover:bg-gray-50 transition-colors" @click="modal.editPerm = false">Cancel</button>
                        <button :disabled="loading" class="h-9 px-4 rounded-lg text-[13px] font-semibold bg-gray-900 hover:bg-black text-white transition-colors disabled:opacity-40" @click="submitEdit">{{ loading ? 'Saving…' : 'Save changes' }}</button>
                    </div>
                </div>
            </div>

            <!-- Delete -->
            <div v-if="modal.deletePerm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-[2px] p-4">
                <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-sm">
                    <div class="px-6 py-5">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center mb-4"><svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
                        <h2 class="text-[15px] font-semibold text-gray-900 mb-1">Delete permission</h2>
                        <p class="text-[13px] text-gray-500">Delete <strong class="text-gray-800">{{ deletingPerm?.name }}</strong>? It will be removed from all roles.</p>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                        <button class="h-9 px-4 rounded-lg text-[13px] border border-gray-200 text-gray-700 hover:bg-gray-50 transition-colors" @click="modal.deletePerm = false">Cancel</button>
                        <button class="h-9 px-4 rounded-lg text-[13px] font-semibold bg-red-600 hover:bg-red-700 text-white transition-colors" @click="confirmDelete">Delete</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
