<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { computed, onMounted, onUnmounted, reactive, ref, watch } from "vue";
import { toast, toSlug } from "../../composables/useWorkOs";

const props = defineProps<{
	roles: Array<any>;
	permissions: Array<any>;
	searchQuery?: string;
}>();

const roleSearch = ref("");
watch(() => props.searchQuery, (val) => {
	roleSearch.value = val || "";
});

const protectedSlugs = ["super-admin", "admin", "mahasiswa", "dosen", "alumni", "mitra", "staff", "prodi"];
function isSystemRole(slug: string) {
	return protectedSlugs.includes((slug || "").toLowerCase());
}

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
	editPriority: false,
});
const roleForm = reactive({ nama: "", slug: "", deskripsi: "" });
const editingRole = ref<any>(null);
const deletingRole = ref<any>(null);
const permRole = ref<any>(null);
const selectedPerms = ref<number[]>([]);
const loading = ref(false);
const priorityList = ref<any[]>([]);

const groupedPerms = computed(() => {
	const g: Record<string, any[]> = {};
	props.permissions.forEach((p) => {
		const grp = p.group || "general";
		if (!g[grp]) g[grp] = [];
		g[grp].push(p);
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
			preserveScroll: true,
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
			preserveScroll: true,
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
	if (isSystemRole(role.slug)) {
		toast("Role sistem inti terlindungi dari penghapusan.", "error");
		return;
	}
	deletingRole.value = role;
	modal.deleteRole = true;
	activeMenu.value = null;
}
function confirmDelete() {
	if (!deletingRole.value) return;
	router.delete(`/workos/roles/${deletingRole.value.id}`, {
		preserveScroll: true,
		onSuccess: () => {
			modal.deleteRole = false;
			toast("Role dihapus.", "error");
		},
		onError: (errs: any) => {
			toast(errs.error || "Gagal menghapus role.", "error");
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
			preserveScroll: true,
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

function openPriorityModal() {
	priorityList.value = [...props.roles].sort((a, b) => (a.priority || 0) - (b.priority || 0));
	modal.editPriority = true;
}
function movePriority(index: number, direction: "up" | "down") {
	const newIdx = direction === "up" ? index - 1 : index + 1;
	if (newIdx < 0 || newIdx >= priorityList.value.length) return;
	const temp = priorityList.value[index];
	priorityList.value[index] = priorityList.value[newIdx];
	priorityList.value[newIdx] = temp;
}
function submitPriorities() {
	loading.value = true;
	const payload = priorityList.value.map((r, i) => ({ id: r.id, priority: i + 1 }));
	router.patch(
		"/workos/roles/priorities",
		{ roles: payload },
		{
			preserveScroll: true,
			onSuccess: () => {
				modal.editPriority = false;
				toast("Prioritas role diperbarui.");
			},
			onFinish: () => {
				loading.value = false;
			},
		},
	);
}
</script>

<template>
    <div class="space-y-5">
        <div class="mb-5">
            <h1 class="text-[22px] font-semibold text-gray-900 dark:text-zinc-100 tracking-tight mb-1">Roles</h1>
            <p class="text-[13px] text-gray-500 dark:text-zinc-400">Define and manage roles that can be assigned to users.</p>
        </div>

        <!-- Controls -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-5">
            <div class="relative w-full sm:w-auto">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 dark:text-zinc-500 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.3-4.3M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
                <input v-model="roleSearch" type="search" placeholder="Search roles…" class="h-9 w-full sm:w-52 pl-9 pr-3 text-[13px] bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 placeholder:text-gray-400 dark:text-zinc-400 transition-all"/>
            </div>
            <div class="hidden sm:block flex-1"/>
            <div class="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto">
                <button class="h-9 px-4 rounded-lg text-[13px] font-medium border border-gray-200 dark:border-zinc-700 text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 dark:bg-zinc-900 transition-colors w-full sm:w-auto cursor-pointer" @click="openPriorityModal">Edit priority</button>
                <button class="h-9 px-4 rounded-lg text-[13px] font-medium bg-gray-900 dark:bg-zinc-100 hover:bg-black dark:hover:bg-white text-white dark:text-zinc-900 shadow-sm transition-colors flex items-center justify-center gap-1.5 dark:shadow-none w-full sm:w-auto cursor-pointer" @click="openCreate">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Create role
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl overflow-x-auto shadow-sm dark:shadow-none min-h-[300px]">
            <table class="w-full text-left whitespace-nowrap">
                <caption class="sr-only">Tabel Peran dan Izin Akses</caption>
                <thead>
                    <tr class="border-b border-gray-200 dark:border-zinc-700">
                        <th class="px-5 py-3 text-[11px] font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Name</th>
                        <th class="px-5 py-3 text-[11px] font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Slug</th>
                        <th class="px-5 py-3 text-[11px] font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Permissions</th>
                        <th class="px-5 py-3 text-[11px] font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Users</th>
                        <th class="px-5 py-3 w-12"/>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="filteredRoles.length === 0">
                        <td colspan="5" class="py-12 text-center text-[13px] text-gray-400 dark:text-zinc-500">No roles found.</td>
                    </tr>
                    <tr v-for="(role, idx) in filteredRoles" :key="role.id" class="border-b border-gray-100 dark:border-zinc-800 last:border-0 hover:bg-gray-50 dark:hover:bg-zinc-800 dark:bg-zinc-900/80 transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-gray-900 dark:text-zinc-100 text-[13px]">{{ role.nama }}</p>
                                <span v-if="isSystemRole(role.slug)" class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-400 border border-gray-200 dark:border-zinc-700 flex items-center gap-1 select-none" title="Protected System Role">
                                    <svg class="w-2.5 h-2.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    System
                                </span>
                            </div>
                            <p v-if="role.deskripsi" class="text-[12px] text-gray-400 dark:text-zinc-500 mt-0.5">{{ role.deskripsi }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex px-2 py-[3px] rounded text-[11px] font-mono font-medium bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-400 border border-gray-200 dark:border-zinc-700">{{ role.slug }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <button class="text-[13px] text-gray-600 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-zinc-100 hover:underline transition-colors cursor-pointer" @click="openPerms(role)">
                                <span v-if="!role.permissions_count" class="text-gray-400 dark:text-zinc-500">None</span>
                                <span v-else class="font-medium">{{ role.permissions_count }} permission{{ role.permissions_count !== 1 ? 's' : '' }}</span>
                            </button>
                        </td>
                        <td class="px-5 py-4 text-[13px] relative group/tooltip">
                            <template v-if="role.users_count > 0">
                                <span class="font-semibold text-gray-900 dark:text-zinc-100 cursor-pointer underline decoration-dashed decoration-gray-300 dark:decoration-zinc-700 underline-offset-4 select-none">
                                    {{ role.users_count }} user{{ role.users_count !== 1 ? 's' : '' }}
                                </span>
                                <!-- Smart Dynamic Tooltip for Users -->
                                <div
                                    :class="[
                                        'absolute left-0 hidden group-hover/tooltip:flex flex-col gap-1.5 p-2.5 bg-gray-900 dark:bg-zinc-800 text-white rounded-xl shadow-2xl text-[11px] min-w-[160px] max-w-[240px] z-50 border border-gray-700/80 dark:border-zinc-700 pointer-events-none transition-all',
                                        idx >= Math.max(1, filteredRoles.length - 2)
                                            ? 'bottom-full mb-1.5'
                                            : 'top-full mt-1.5'
                                    ]"
                                >
                                    <div v-if="idx < Math.max(1, filteredRoles.length - 2)" class="absolute -top-1 left-4 w-2 h-2 bg-gray-900 dark:bg-zinc-800 rotate-45 border-t border-l border-gray-700/80 dark:border-zinc-700"></div>
                                    <div v-else class="absolute -bottom-1 left-4 w-2 h-2 bg-gray-900 dark:bg-zinc-800 rotate-45 border-b border-r border-gray-700/80 dark:border-zinc-700"></div>

                                    <span class="font-bold text-[10px] uppercase tracking-wider text-gray-400 dark:text-zinc-400 border-b border-gray-800 dark:border-zinc-700/80 pb-1">Assigned Users</span>
                                    <div class="flex flex-col gap-1 max-h-32 overflow-y-auto pt-0.5">
                                        <div v-for="u in (role.users || [])" :key="u.id" class="flex items-center gap-1.5 text-[11px] text-gray-200">
                                            <div class="w-1.5 h-1.5 rounded-full bg-blue-400 shrink-0"></div>
                                            <span class="truncate">{{ u.name }}</span>
                                        </div>
                                        <div v-if="(role.users_count || 0) > (role.users || []).length" class="text-[10px] text-gray-400 pt-0.5">
                                            +{{ role.users_count - (role.users || []).length }} more users
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <span v-else class="text-gray-400 dark:text-zinc-500">None</span>
                        </td>
                        <td class="px-5 py-4 text-right relative">
                            <button class="w-7 h-7 rounded-md flex items-center justify-center text-gray-400 hover:text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-800 dark:bg-zinc-800 transition-colors ml-auto cursor-pointer" @click.stop="toggleMenu(role.id, $event)">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/></svg>
                            </button>
                            <div v-if="activeMenu === role.id" class="absolute right-4 top-full mt-1 w-44 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl shadow-lg z-50 py-1.5 dark:shadow-none text-left">
                                <button class="w-full flex items-center gap-2.5 px-3 py-2 text-[13px] text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 dark:bg-zinc-900 text-left transition-colors cursor-pointer" @click="openEdit(role)">
                                    <svg class="w-3.5 h-3.5 text-gray-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit role
                                </button>
                                <button class="w-full flex items-center gap-2.5 px-3 py-2 text-[13px] text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 dark:bg-zinc-900 text-left transition-colors cursor-pointer" @click="openPerms(role)">
                                    <svg class="w-3.5 h-3.5 text-gray-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    Permissions
                                </button>
                                <div class="my-1 border-t border-gray-100 dark:border-zinc-800"/>
                                <button
                                    :disabled="isSystemRole(role.slug)"
                                    :class="[
                                        'w-full flex items-center gap-2.5 px-3 py-2 text-[13px] text-left transition-colors',
                                        isSystemRole(role.slug)
                                            ? 'text-gray-300 dark:text-zinc-600 cursor-not-allowed opacity-60'
                                            : 'text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30 cursor-pointer'
                                    ]"
                                    @click="openDelete(role)"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Delete role
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ─── SHARED MODALS ─── -->
        <Teleport to="body">
            <!-- Create Role -->
            <div v-if="modal.createRole" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-[2px] p-4" @click.self="modal.createRole = false">
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-zinc-700 w-full max-w-md dark:shadow-none">
                    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-zinc-800">
                        <h2 class="text-[16px] font-semibold text-gray-900 dark:text-zinc-100">Create role</h2>
                        <button class="p-1.5 rounded-md text-gray-400 dark:text-zinc-500 hover:bg-gray-100 dark:hover:bg-zinc-800 dark:bg-zinc-800 transition-colors" @click="modal.createRole = false"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <div class="px-6 py-5 space-y-4">
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-700 dark:text-zinc-300 mb-1.5">Role name</label>
                            <input v-model="roleForm.nama" type="text" placeholder="e.g. Dosen Pembimbing" class="w-full h-9 px-3 text-[13px] border border-gray-300 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 placeholder:text-gray-300 transition-all bg-white dark:bg-zinc-900 text-gray-900 dark:text-zinc-100" @input="autoSlug"/>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-700 dark:text-zinc-300 mb-1.5">Slug</label>
                            <div class="flex h-9 border border-gray-300 dark:border-zinc-700 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-400 transition-all bg-white dark:bg-zinc-900">
                                <span class="px-3 text-[11px] font-mono text-gray-400 dark:text-zinc-500 bg-gray-50 dark:bg-zinc-900 flex items-center border-r border-gray-200 dark:border-zinc-700">slug:</span>
                                <input v-model="roleForm.slug" type="text" placeholder="dosen-pembimbing" class="flex-1 px-3 text-[12px] font-mono focus:outline-none placeholder:text-gray-300 bg-transparent text-gray-900 dark:text-zinc-100"/>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-700 dark:text-zinc-300 mb-1.5">Description <span class="font-normal text-gray-400 dark:text-zinc-500">(optional)</span></label>
                            <input v-model="roleForm.deskripsi" type="text" placeholder="Short description" class="w-full h-9 px-3 text-[13px] border border-gray-300 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 placeholder:text-gray-300 transition-all bg-white dark:bg-zinc-900 text-gray-900 dark:text-zinc-100"/>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 dark:border-zinc-800 flex justify-end gap-2">
                        <button class="h-9 px-4 rounded-lg text-[13px] border border-gray-200 dark:border-zinc-700 text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 dark:bg-zinc-900 transition-colors" @click="modal.createRole = false">Cancel</button>
                        <button :disabled="loading" class="h-9 px-5 rounded-lg text-[13px] font-semibold bg-gray-900 hover:bg-black text-white shadow-sm transition-colors disabled:opacity-40 dark:shadow-none cursor-pointer" @click="submitCreate">{{ loading ? 'Creating…' : 'Create role' }}</button>
                    </div>
                </div>
            </div>

            <!-- Edit Role -->
            <div v-if="modal.editRole" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-[2px] p-4" @click.self="modal.editRole = false">
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-zinc-700 w-full max-w-md dark:shadow-none">
                    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-zinc-800">
                        <h2 class="text-[16px] font-semibold text-gray-900 dark:text-zinc-100">Edit role</h2>
                        <button class="p-1.5 rounded-md text-gray-400 dark:text-zinc-500 hover:bg-gray-100 dark:hover:bg-zinc-800 dark:bg-zinc-800 transition-colors" @click="modal.editRole = false"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <div class="px-6 py-5 space-y-4">
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-700 dark:text-zinc-300 mb-1.5">Role name</label>
                            <input v-model="roleForm.nama" type="text" class="w-full h-9 px-3 text-[13px] border border-gray-300 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition-all bg-white dark:bg-zinc-900 text-gray-900 dark:text-zinc-100"/>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-700 dark:text-zinc-300 mb-1.5">Slug</label>
                            <div class="flex h-9 border border-gray-300 dark:border-zinc-700 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-400 transition-all bg-white dark:bg-zinc-900">
                                <span class="px-3 text-[11px] font-mono text-gray-400 dark:text-zinc-500 bg-gray-50 dark:bg-zinc-900 flex items-center border-r border-gray-200 dark:border-zinc-700">slug:</span>
                                <input v-model="roleForm.slug" type="text" class="flex-1 px-3 text-[12px] font-mono focus:outline-none bg-transparent text-gray-900 dark:text-zinc-100"/>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-700 dark:text-zinc-300 mb-1.5">Description</label>
                            <input v-model="roleForm.deskripsi" type="text" class="w-full h-9 px-3 text-[13px] border border-gray-300 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition-all bg-white dark:bg-zinc-900 text-gray-900 dark:text-zinc-100"/>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 dark:border-zinc-800 flex justify-end gap-2">
                        <button class="h-9 px-4 rounded-lg text-[13px] border border-gray-200 dark:border-zinc-700 text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 dark:bg-zinc-900 transition-colors" @click="modal.editRole = false">Cancel</button>
                        <button :disabled="loading" class="h-9 px-4 rounded-lg text-[13px] font-semibold bg-gray-900 hover:bg-black text-white transition-colors disabled:opacity-40 cursor-pointer" @click="submitEdit">{{ loading ? 'Saving…' : 'Save changes' }}</button>
                    </div>
                </div>
            </div>

            <!-- Delete Role -->
            <div v-if="modal.deleteRole" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-[2px] p-4">
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-zinc-700 w-full max-w-sm dark:shadow-none">
                    <div class="px-6 py-5">
                        <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-950/50 flex items-center justify-center mb-4">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <h2 class="text-[15px] font-semibold text-gray-900 dark:text-zinc-100 mb-1">Delete role</h2>
                        <p class="text-[13px] text-gray-500 dark:text-zinc-400">
                            Delete <strong class="text-gray-800 dark:text-zinc-200">{{ deletingRole?.nama }}</strong>?
                        </p>
                        <p v-if="deletingRole?.users_count > 0" class="text-[12px] text-amber-600 dark:text-amber-400 mt-2 p-2 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-lg">
                            ⚠️ Role ini memiliki {{ deletingRole.users_count }} pengguna terikat. Harap pindahkan pengguna sebelum menghapus role ini.
                        </p>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 dark:border-zinc-800 flex justify-end gap-2">
                        <button class="h-9 px-4 rounded-lg text-[13px] border border-gray-200 dark:border-zinc-700 text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 dark:bg-zinc-900 transition-colors" @click="modal.deleteRole = false">Cancel</button>
                        <button :disabled="deletingRole?.users_count > 0" class="h-9 px-4 rounded-lg text-[13px] font-semibold bg-red-600 hover:bg-red-700 text-white transition-colors disabled:opacity-40 cursor-pointer" @click="confirmDelete">Delete</button>
                    </div>
                </div>
            </div>

            <!-- Role Permissions Modal -->
            <div v-if="modal.rolePerms" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-[2px] p-4" @click.self="modal.rolePerms = false">
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-zinc-700 w-full max-w-lg max-h-[85vh] flex flex-col dark:shadow-none">
                    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-zinc-800 shrink-0">
                        <div>
                            <h2 class="text-[16px] font-semibold text-gray-900 dark:text-zinc-100">Permissions for {{ permRole?.nama }}</h2>
                            <p class="text-[12px] text-gray-400 dark:text-zinc-500 mt-0.5">Select permissions to grant to this role.</p>
                        </div>
                        <button class="p-1.5 rounded-md text-gray-400 dark:text-zinc-500 hover:bg-gray-100 dark:hover:bg-zinc-800 dark:bg-zinc-800 transition-colors" @click="modal.rolePerms = false"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <div class="px-6 py-5 overflow-y-auto space-y-6 flex-1">
                        <div v-for="(perms, groupName) in groupedPerms" :key="groupName" class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-zinc-500">{{ groupName }}</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <label v-for="p in perms" :key="p.id" class="flex items-start gap-2.5 p-2.5 rounded-lg border border-gray-200 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50 cursor-pointer transition-colors">
                                    <input type="checkbox" :checked="selectedPerms.includes(p.id)" class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500" @change="togglePerm(p.id)"/>
                                    <div>
                                        <p class="text-[12px] font-semibold text-gray-900 dark:text-zinc-100 leading-tight">{{ p.name }}</p>
                                        <p class="text-[10px] font-mono text-gray-400 dark:text-zinc-500 mt-0.5">{{ p.slug }}</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 dark:border-zinc-800 flex justify-end gap-2 shrink-0">
                        <button class="h-9 px-4 rounded-lg text-[13px] border border-gray-200 dark:border-zinc-700 text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 dark:bg-zinc-900 transition-colors" @click="modal.rolePerms = false">Cancel</button>
                        <button :disabled="loading" class="h-9 px-5 rounded-lg text-[13px] font-semibold bg-gray-900 hover:bg-black text-white shadow-sm transition-colors disabled:opacity-40 cursor-pointer dark:shadow-none" @click="submitPerms">{{ loading ? 'Saving…' : 'Save permissions' }}</button>
                    </div>
                </div>
            </div>

            <!-- Edit Priority Modal -->
            <div v-if="modal.editPriority" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-[2px] p-4" @click.self="modal.editPriority = false">
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-zinc-700 w-full max-w-md dark:shadow-none">
                    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-zinc-800">
                        <div>
                            <h2 class="text-[16px] font-semibold text-gray-900 dark:text-zinc-100">Edit Role Priority</h2>
                            <p class="text-[12px] text-gray-400 dark:text-zinc-500 mt-0.5">Role with higher priority takes precedence.</p>
                        </div>
                        <button class="p-1.5 rounded-md text-gray-400 dark:text-zinc-500 hover:bg-gray-100 dark:hover:bg-zinc-800 dark:bg-zinc-800 transition-colors" @click="modal.editPriority = false"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <div class="px-6 py-5 space-y-2 max-h-[60vh] overflow-y-auto">
                        <div v-for="(r, index) in priorityList" :key="r.id" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-zinc-800/60 border border-gray-200 dark:border-zinc-700/80 rounded-xl">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full bg-gray-200 dark:bg-zinc-700 text-gray-700 dark:text-zinc-300 text-[11px] font-bold flex items-center justify-center">{{ index + 1 }}</span>
                                <div>
                                    <p class="text-[13px] font-semibold text-gray-900 dark:text-zinc-100">{{ r.nama }}</p>
                                    <span class="text-[10px] font-mono text-gray-400 dark:text-zinc-500">{{ r.slug }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button :disabled="index === 0" class="w-7 h-7 rounded-lg border border-gray-200 dark:border-zinc-700 flex items-center justify-center text-gray-600 dark:text-zinc-400 hover:bg-white dark:hover:bg-zinc-700 disabled:opacity-30 cursor-pointer" @click="movePriority(index, 'up')">▲</button>
                                <button :disabled="index === priorityList.length - 1" class="w-7 h-7 rounded-lg border border-gray-200 dark:border-zinc-700 flex items-center justify-center text-gray-600 dark:text-zinc-400 hover:bg-white dark:hover:bg-zinc-700 disabled:opacity-30 cursor-pointer" @click="movePriority(index, 'down')">▼</button>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 dark:border-zinc-800 flex justify-end gap-2">
                        <button class="h-9 px-4 rounded-lg text-[13px] border border-gray-200 dark:border-zinc-700 text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 dark:bg-zinc-900 transition-colors" @click="modal.editPriority = false">Cancel</button>
                        <button :disabled="loading" class="h-9 px-5 rounded-lg text-[13px] font-semibold bg-gray-900 hover:bg-black text-white shadow-sm transition-colors disabled:opacity-40 cursor-pointer dark:shadow-none" @click="submitPriorities">{{ loading ? 'Saving…' : 'Save priority' }}</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
