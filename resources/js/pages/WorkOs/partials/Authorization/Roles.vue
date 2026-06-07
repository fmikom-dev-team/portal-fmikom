<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { computed, onMounted, onUnmounted, reactive, ref } from "vue";
import { toast, toSlug } from "../../composables/useWorkOs";

const props = defineProps<{ roles: Array<any>; permissions: Array<any> }>();

const roleSearch = ref("");
const filteredRoles = computed(() => {
	if (!roleSearch.value.trim()) return props.roles;
	const q = roleSearch.value.toLowerCase();
	return props.roles.filter(
		(r) =>
			r.nama?.toLowerCase().includes(q) || r.slug?.toLowerCase().includes(q),
	);
});

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
	createRole: false,
	editRole: false,
	deleteRole: false,
	rolePerms: false,
});
const roleForm = reactive({ nama: "", slug: "", deskripsi: "" });
const editingRole = ref<any>(null);
const deletingRole = ref<any>(null);
const permRole = ref<any>(null);
const selectedPerms = ref<number[]>([]);
const loading = ref(false);

const groupedPerms = computed(() => {
	const g: Record<string, any[]> = {};
	props.permissions.forEach((p) => {
		if (!g[p.group]) g[p.group] = [];
		g[p.group].push(p);
	});
	return g;
});

function autoSlug() {
	roleForm.slug = toSlug(roleForm.nama);
}

function openCreate() {
	Object.assign(roleForm, { nama: "", slug: "", deskripsi: "" });
	modal.createRole = true;
}
function submitCreate() {
	loading.value = true;
	router.post(
		"/workos/roles",
		{ ...roleForm },
		{
			onSuccess: () => {
				modal.createRole = false;
				toast("Role dibuat.");
			},
			onError: () => toast("Gagal membuat role.", "error"),
			onFinish: () => {
				loading.value = false;
			},
		},
	);
}

function openEdit(role: any) {
	editingRole.value = role;
	Object.assign(roleForm, {
		nama: role.nama,
		slug: role.slug,
		deskripsi: role.deskripsi ?? "",
	});
	modal.editRole = true;
	activeMenu.value = null;
}
function submitEdit() {
	if (!editingRole.value) return;
	loading.value = true;
	router.patch(
		`/workos/roles/${editingRole.value.id}`,
		{ ...roleForm },
		{
			onSuccess: () => {
				modal.editRole = false;
				toast("Role diperbarui.");
			},
			onFinish: () => {
				loading.value = false;
			},
		},
	);
}

function openDelete(role: any) {
	deletingRole.value = role;
	modal.deleteRole = true;
	activeMenu.value = null;
}
function confirmDelete() {
	if (!deletingRole.value) return;
	router.delete(`/workos/roles/${deletingRole.value.id}`, {
		onSuccess: () => {
			modal.deleteRole = false;
			toast("Role dihapus.", "error");
		},
	});
}

function openPerms(role: any) {
	permRole.value = role;
	selectedPerms.value = role.permissions
		? role.permissions.map((p: any) => p.id)
		: [];
	modal.rolePerms = true;
	activeMenu.value = null;
}
function togglePerm(id: number) {
	const i = selectedPerms.value.indexOf(id);
	if (i >= 0) selectedPerms.value.splice(i, 1);
	else selectedPerms.value.push(id);
}
function submitPerms() {
	if (!permRole.value) return;
	loading.value = true;
	router.patch(
		`/workos/roles/${permRole.value.id}/permissions`,
		{ permission_ids: selectedPerms.value },
		{
			onSuccess: () => {
				modal.rolePerms = false;
				toast("Permissions disimpan.");
			},
			onFinish: () => {
				loading.value = false;
			},
		},
	);
}
</script>

<template>
    <div class="p-6 md:p-8">
        <div class="mb-6">
            <h1 class="text-[22px] font-semibold text-gray-900 tracking-tight mb-1">Roles</h1>
            <p class="text-[13px] text-gray-500">Define and manage roles that can be assigned to users.</p>
        </div>

        <!-- Controls -->
        <div class="flex items-center gap-2 mb-5">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.3-4.3M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
                <input v-model="roleSearch" type="search" placeholder="Search roles…" class="h-9 w-52 pl-9 pr-3 text-[13px] bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 placeholder:text-gray-400 transition-all"/>
            </div>
            <div class="flex-1"/>
            <button class="h-9 px-4 rounded-lg text-[13px] border border-gray-200 text-gray-700 hover:bg-gray-50 transition-colors">Edit priority</button>
            <button class="h-9 px-4 rounded-lg text-[13px] font-medium bg-gray-900 hover:bg-black text-white shadow-sm transition-colors flex items-center gap-1.5" @click="openCreate">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Create role
            </button>
        </div>

        <!-- Table -->
        <div class="bg-white border border-gray-200 rounded-xl overflow-x-auto shadow-sm">
            <table class="w-full text-left whitespace-nowrap">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="px-5 py-3 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-5 py-3 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Slug</th>
                        <th class="px-5 py-3 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Permissions</th>
                        <th class="px-5 py-3 w-12"/>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="filteredRoles.length === 0">
                        <td colspan="4" class="py-12 text-center text-[13px] text-gray-400">No roles found.</td>
                    </tr>
                    <tr v-for="role in filteredRoles" :key="role.id" class="border-b border-gray-100 last:border-0 hover:bg-gray-50/80 transition-colors">
                        <td class="px-5 py-4">
                            <p class="font-semibold text-gray-900 text-[13px]">{{ role.nama }}</p>
                            <p v-if="role.deskripsi" class="text-[12px] text-gray-400 mt-0.5">{{ role.deskripsi }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex px-2 py-[3px] rounded text-[11px] font-mono font-medium bg-gray-100 text-gray-600 border border-gray-200">{{ role.slug }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <button class="text-[13px] text-gray-600 hover:text-gray-900 hover:underline transition-colors" @click="openPerms(role)">
                                <span v-if="!role.permissions_count" class="text-gray-400">None</span>
                                <span v-else>{{ role.permissions_count }} permission{{ role.permissions_count !== 1 ? 's' : '' }}</span>
                            </button>
                        </td>
                        <td class="px-5 py-4 text-right relative">
                            <button class="w-7 h-7 rounded-md flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors" @click.stop="toggleMenu(role.id, $event)">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/></svg>
                            </button>
                            <div v-if="activeMenu === role.id" class="absolute right-4 top-full mt-1 w-40 bg-white border border-gray-200 rounded-xl shadow-lg z-50 py-1.5">
                                <button class="w-full flex items-center gap-2.5 px-3 py-2 text-[13px] text-gray-700 hover:bg-gray-50 text-left transition-colors" @click="openEdit(role)">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit role
                                </button>
                                <button class="w-full flex items-center gap-2.5 px-3 py-2 text-[13px] text-gray-700 hover:bg-gray-50 text-left transition-colors" @click="openPerms(role)">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    Permissions
                                </button>
                                <div class="my-1 border-t border-gray-100"/>
                                <button class="w-full flex items-center gap-2.5 px-3 py-2 text-[13px] text-red-500 hover:bg-red-50 text-left transition-colors" @click="openDelete(role)">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Delete role
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ─── SHARED MODAL WRAPPER ─── -->
        <Teleport to="body">
            <!-- Create Role -->
            <div v-if="modal.createRole" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-[2px] p-4" @click.self="modal.createRole = false">
                <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-md">
                    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                        <h2 class="text-[16px] font-semibold text-gray-900">Create role</h2>
                        <button class="p-1.5 rounded-md text-gray-400 hover:bg-gray-100 transition-colors" @click="modal.createRole = false"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <div class="px-6 py-5 space-y-4">
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-700 mb-1.5">Role name</label>
                            <input v-model="roleForm.nama" type="text" placeholder="e.g. Dosen Pembimbing" class="w-full h-9 px-3 text-[13px] border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 placeholder:text-gray-300 transition-all" @input="autoSlug"/>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-700 mb-1.5">Slug</label>
                            <div class="flex h-9 border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-400 transition-all">
                                <span class="px-3 text-[11px] font-mono text-gray-400 bg-gray-50 flex items-center border-r border-gray-200">slug:</span>
                                <input v-model="roleForm.slug" type="text" placeholder="dosen-pembimbing" class="flex-1 px-3 text-[12px] font-mono focus:outline-none placeholder:text-gray-300"/>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-700 mb-1.5">Description <span class="font-normal text-gray-400">(optional)</span></label>
                            <input v-model="roleForm.deskripsi" type="text" placeholder="Short description" class="w-full h-9 px-3 text-[13px] border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 placeholder:text-gray-300 transition-all"/>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                        <button class="h-9 px-4 rounded-lg text-[13px] border border-gray-200 text-gray-700 hover:bg-gray-50 transition-colors" @click="modal.createRole = false">Cancel</button>
                        <button :disabled="loading" class="h-9 px-5 rounded-lg text-[13px] font-semibold bg-gray-900 hover:bg-black text-white shadow-sm transition-colors disabled:opacity-40" @click="submitCreate">{{ loading ? 'Creating…' : 'Create role' }}</button>
                    </div>
                </div>
            </div>

            <!-- Edit Role -->
            <div v-if="modal.editRole" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-[2px] p-4" @click.self="modal.editRole = false">
                <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-md">
                    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                        <h2 class="text-[16px] font-semibold text-gray-900">Edit role</h2>
                        <button class="p-1.5 rounded-md text-gray-400 hover:bg-gray-100 transition-colors" @click="modal.editRole = false"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <div class="px-6 py-5 space-y-4">
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-700 mb-1.5">Role name</label>
                            <input v-model="roleForm.nama" type="text" class="w-full h-9 px-3 text-[13px] border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition-all"/>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-700 mb-1.5">Slug</label>
                            <div class="flex h-9 border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-400 transition-all">
                                <span class="px-3 text-[11px] font-mono text-gray-400 bg-gray-50 flex items-center border-r border-gray-200">slug:</span>
                                <input v-model="roleForm.slug" type="text" class="flex-1 px-3 text-[12px] font-mono focus:outline-none"/>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-700 mb-1.5">Description</label>
                            <input v-model="roleForm.deskripsi" type="text" class="w-full h-9 px-3 text-[13px] border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition-all"/>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                        <button class="h-9 px-4 rounded-lg text-[13px] border border-gray-200 text-gray-700 hover:bg-gray-50 transition-colors" @click="modal.editRole = false">Cancel</button>
                        <button :disabled="loading" class="h-9 px-4 rounded-lg text-[13px] font-semibold bg-gray-900 hover:bg-black text-white shadow-sm transition-colors disabled:opacity-40" @click="submitEdit">{{ loading ? 'Saving…' : 'Save changes' }}</button>
                    </div>
                </div>
            </div>

            <!-- Delete Role -->
            <div v-if="modal.deleteRole" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-[2px] p-4">
                <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-sm">
                    <div class="px-6 py-5">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center mb-4">
                            <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <h2 class="text-[15px] font-semibold text-gray-900 mb-1">Delete role</h2>
                        <p class="text-[13px] text-gray-500">Delete <strong class="text-gray-800">{{ deletingRole?.nama }}</strong>? All assignments will be removed. This action cannot be undone.</p>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                        <button class="h-9 px-4 rounded-lg text-[13px] border border-gray-200 text-gray-700 hover:bg-gray-50 transition-colors" @click="modal.deleteRole = false">Cancel</button>
                        <button class="h-9 px-4 rounded-lg text-[13px] font-semibold bg-red-600 hover:bg-red-700 text-white transition-colors" @click="confirmDelete">Delete</button>
                    </div>
                </div>
            </div>

            <!-- Role Permissions -->
            <div v-if="modal.rolePerms" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-[2px] p-4" @click.self="modal.rolePerms = false">
                <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-lg">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h2 class="text-[16px] font-semibold text-gray-900">Manage Permissions</h2>
                        <p class="text-[12px] text-gray-500 mt-0.5">Role: <span class="font-mono font-semibold text-gray-700">{{ permRole?.slug }}</span></p>
                    </div>
                    <div class="px-6 py-4 max-h-72 overflow-y-auto">
                        <div v-if="!permissions.length" class="py-6 text-center text-[13px] text-gray-400">No permissions defined.</div>
                        <div v-for="(perms, group) in groupedPerms" :key="group" class="mb-4 last:mb-0">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">{{ group }}</p>
                            <div class="space-y-0.5">
                                <label v-for="p in perms" :key="p.id" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                    <div
                                        :class="['w-4 h-4 rounded border-2 flex items-center justify-center shrink-0 transition-all', selectedPerms.includes(p.id) ? 'bg-gray-900 border-gray-900' : 'bg-white border-gray-300']"
                                        @click="togglePerm(p.id)"
                                    >
                                        <svg v-if="selectedPerms.includes(p.id)" class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-[13px] font-medium text-gray-800">{{ p.name }}</p>
                                        <p class="text-[11px] font-mono text-gray-400">{{ p.slug }}</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-[12px] text-gray-400">{{ selectedPerms.length }} selected</span>
                        <div class="flex gap-2">
                            <button class="h-9 px-4 rounded-lg text-[13px] border border-gray-200 text-gray-700 hover:bg-gray-50 transition-colors" @click="modal.rolePerms = false">Cancel</button>
                            <button :disabled="loading" class="h-9 px-4 rounded-lg text-[13px] font-semibold bg-gray-900 hover:bg-black text-white shadow-sm transition-colors disabled:opacity-40" @click="submitPerms">{{ loading ? 'Saving…' : 'Save permissions' }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
