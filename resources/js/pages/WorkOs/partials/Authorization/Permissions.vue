<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { computed, onMounted, onUnmounted, reactive, ref, watch } from "vue";
import { toast, toSlug } from "../../composables/useWorkOs";

const props = defineProps<{
	permissions: Array<any>;
	searchQuery?: string;
}>();

const permSearch = ref("");
const activeGroup = ref("all");

watch(() => props.searchQuery, (val) => {
	permSearch.value = val || "";
});

const standardGroupOptions = computed(() => {
	const predefined = ["auth", "fast", "pagi", "portal", "system", "trace", "wims", "general"];
	const existingFromProps = props.permissions
		.map((p) => (p.group || "").toLowerCase())
		.filter(Boolean);
	return Array.from(new Set([...predefined, ...existingFromProps])).sort();
});

const availableGroups = computed(() => {
	const counts: Record<string, number> = {};
	props.permissions.forEach((p) => {
		const g = (p.group || "general").toLowerCase();
		counts[g] = (counts[g] || 0) + 1;
	});

	const groups = Object.keys(counts).map((g) => ({
		key: g,
		label: g.toUpperCase(),
		count: counts[g],
	}));

	return [
		{ key: "all", label: "ALL", count: props.permissions.length },
		...groups,
	];
});

const filteredPerms = computed(() => {
	let result = props.permissions;

	if (activeGroup.value !== "all") {
		result = result.filter(
			(p) => (p.group || "general").toLowerCase() === activeGroup.value,
		);
	}

	if (permSearch.value.trim()) {
		const q = permSearch.value.toLowerCase();
		result = result.filter(
			(p) =>
				p.name?.toLowerCase().includes(q) ||
				p.slug?.toLowerCase().includes(q) ||
				p.group?.toLowerCase().includes(q) ||
				p.description?.toLowerCase().includes(q),
		);
	}

	return result;
});

function getGroupBadgeClass(groupName: string) {
	const g = (groupName || "").toLowerCase();
	if (g === "auth") {
		return "bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800/60";
	}
	if (g === "fast") {
		return "bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800/60";
	}
	if (g === "users") {
		return "bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60";
	}
	if (g === "roles") {
		return "bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60";
	}
	return "bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-400 border-gray-200 dark:border-zinc-700";
}

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
	createPerm: false,
	editPerm: false,
	deletePerm: false,
});
const permForm = reactive({
	name: "",
	slug: "",
	group: "auth",
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
		group: "auth",
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
			preserveScroll: true,
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
		group: (p.group || "general").toLowerCase(),
		description: p.description ?? "",
	});
	modal.editPerm = true;
	activeMenu.value = null;
}
function submitEdit() {
	if (!editingPerm.value) return;
	loading.value = true;
	router.patch(
		`/workos/permissions/${editingPerm.value.id}`,
		{ ...permForm },
		{
			preserveScroll: true,
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
	activeMenu.value = null;
}
function confirmDelete() {
	if (!deletingPerm.value) return;
	router.delete(`/workos/permissions/${deletingPerm.value.id}`, {
		preserveScroll: true,
		onSuccess: () => {
			modal.deletePerm = false;
			toast("Permission dihapus.", "error");
		},
	});
}
</script>

<template>
    <div class="space-y-5">
        <!-- Title Header -->
        <div class="mb-5">
            <h1 class="text-[22px] font-semibold text-gray-900 dark:text-zinc-100 tracking-tight mb-1">Permissions</h1>
            <p class="text-[13px] text-gray-500 dark:text-zinc-400">Define granular permissions that can be assigned to roles.</p>
        </div>

        <!-- Controls matching Roles.vue -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-4">
            <div class="relative w-full sm:w-auto">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 dark:text-zinc-500 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.3-4.3M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
                <input v-model="permSearch" type="search" placeholder="Search permissions…" class="h-9 w-full sm:w-56 pl-9 pr-3 text-[13px] bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 placeholder:text-gray-400 dark:text-zinc-400 transition-all"/>
            </div>
            <div class="hidden sm:block flex-1"/>
            <button class="h-9 px-4 rounded-lg text-[13px] font-medium bg-gray-900 dark:bg-zinc-100 hover:bg-black dark:hover:bg-white text-white dark:text-zinc-900 shadow-sm transition-colors flex items-center justify-center gap-1.5 dark:shadow-none w-full sm:w-auto cursor-pointer" @click="openCreate">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Create permission
            </button>
        </div>

        <!-- Group Filter Pills Bar -->
        <div v-if="availableGroups.length > 1" class="flex items-center gap-1.5 overflow-x-auto pb-1 mb-4 no-scrollbar">
            <button
                v-for="g in availableGroups"
                :key="g.key"
                type="button"
                :class="[
                    'px-3.5 py-1.5 rounded-lg text-[12px] font-medium transition-colors flex items-center gap-1.5 shrink-0 cursor-pointer border select-none outline-none',
                    activeGroup === g.key
                        ? 'bg-gray-900 border-gray-900 text-white dark:bg-zinc-100 dark:border-zinc-100 dark:text-zinc-900 font-semibold'
                        : 'bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-400 hover:bg-gray-50 dark:hover:bg-zinc-800'
                ]"
                @click="activeGroup = g.key"
            >
                <span>{{ g.label }}</span>
                <span
                    class="px-1.5 py-0.2 rounded-full text-[10px] tabular-nums font-semibold"
                    :class="activeGroup === g.key ? 'bg-white/20 text-white dark:bg-black/10 dark:text-zinc-900' : 'bg-gray-100 dark:bg-zinc-800 text-gray-500 dark:text-zinc-400'"
                >
                    {{ g.count }}
                </span>
            </button>
        </div>

        <!-- Single Table Card matching Roles.vue structure -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl overflow-x-auto shadow-sm dark:shadow-none min-h-[300px]">
            <table class="w-full text-left whitespace-nowrap">
                <caption class="sr-only">Tabel Izin Akses</caption>
                <thead>
                    <tr class="border-b border-gray-200 dark:border-zinc-700">
                        <th class="px-5 py-3 text-[11px] font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Name</th>
                        <th class="px-5 py-3 text-[11px] font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Slug</th>
                        <th class="px-5 py-3 text-[11px] font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Assigned to</th>
                        <th class="px-5 py-3 text-[11px] font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Description</th>
                        <th class="px-5 py-3 w-12 text-right text-[11px] font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider"/>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="filteredPerms.length === 0">
                        <td colspan="5" class="py-12 text-center text-[13px] text-gray-400 dark:text-zinc-500">No permissions found in this category.</td>
                    </tr>
                    <tr v-for="(p, idx) in filteredPerms" :key="p.id" class="border-b border-gray-100 dark:border-zinc-800 last:border-0 hover:bg-gray-50 dark:hover:bg-zinc-800 dark:bg-zinc-900/80 transition-colors">
                        <td class="px-5 py-4">
                            <p class="font-semibold text-gray-900 dark:text-zinc-100 text-[13px]">{{ p.name }}</p>
                            <span
                                v-if="p.group"
                                class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider border mt-1"
                                :class="getGroupBadgeClass(p.group)"
                            >
                                {{ p.group }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex px-2 py-[3px] rounded text-[11px] font-mono font-medium bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-400 border border-gray-200 dark:border-zinc-700">{{ p.slug }}</span>
                        </td>
                        <td class="px-5 py-4 text-[13px] relative group/tooltip">
                            <template v-if="p.roles_count > 0">
                                <span class="font-semibold text-gray-900 dark:text-zinc-100 cursor-pointer underline decoration-dashed decoration-gray-300 dark:decoration-zinc-700 underline-offset-4 select-none">
                                    {{ p.roles_count }} role{{ p.roles_count !== 1 ? 's' : '' }}
                                </span>
                                <!-- Smart Dynamic Tooltip (Opens DOWNWARD for top rows, UPWARD for bottom rows) -->
                                <div
                                    :class="[
                                        'absolute left-0 hidden group-hover/tooltip:flex flex-col gap-1.5 p-2.5 bg-gray-900 dark:bg-zinc-800 text-white rounded-xl shadow-2xl text-[11px] min-w-[150px] max-w-[240px] z-50 border border-gray-700/80 dark:border-zinc-700 pointer-events-none transition-all',
                                        idx >= Math.max(1, filteredPerms.length - 2)
                                            ? 'bottom-full mb-1.5'
                                            : 'top-full mt-1.5'
                                    ]"
                                >
                                    <!-- Caret for Downward Tooltip -->
                                    <div v-if="idx < Math.max(1, filteredPerms.length - 2)" class="absolute -top-1 left-4 w-2 h-2 bg-gray-900 dark:bg-zinc-800 rotate-45 border-t border-l border-gray-700/80 dark:border-zinc-700"></div>
                                    <!-- Caret for Upward Tooltip -->
                                    <div v-else class="absolute -bottom-1 left-4 w-2 h-2 bg-gray-900 dark:bg-zinc-800 rotate-45 border-b border-r border-gray-700/80 dark:border-zinc-700"></div>

                                    <span class="font-bold text-[10px] uppercase tracking-wider text-gray-400 dark:text-zinc-400 border-b border-gray-800 dark:border-zinc-700/80 pb-1">Assigned Roles</span>
                                    <div class="flex flex-wrap gap-1 max-h-32 overflow-y-auto pt-0.5">
                                        <span v-for="r in (p.roles || [])" :key="r.id" class="px-2 py-0.5 rounded text-[10px] font-medium bg-gray-800 dark:bg-zinc-700 text-gray-200 border border-gray-700/80">
                                            {{ r.nama }}
                                        </span>
                                    </div>
                                </div>
                            </template>
                            <span v-else class="text-gray-400 dark:text-zinc-500">None</span>
                        </td>
                        <td class="px-5 py-4 text-[12px] text-gray-500 dark:text-zinc-400 max-w-xs truncate">
                            {{ p.description || '—' }}
                        </td>
                        <td class="px-5 py-4 text-right relative">
                            <button class="w-7 h-7 rounded-md flex items-center justify-center text-gray-400 hover:text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-800 dark:bg-zinc-800 transition-colors ml-auto" @click.stop="toggleMenu(p.id, $event)">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/></svg>
                            </button>
                            <div v-if="activeMenu === p.id" class="absolute right-4 top-full mt-1 w-40 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl shadow-lg z-50 py-1.5 dark:shadow-none text-left">
                                <button class="w-full flex items-center gap-2.5 px-3 py-2 text-[13px] text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 dark:bg-zinc-900 text-left transition-colors" @click="openEdit(p)">
                                    <svg class="w-3.5 h-3.5 text-gray-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit permission
                                </button>
                                <div class="my-1 border-t border-gray-100 dark:border-zinc-800"/>
                                <button class="w-full flex items-center gap-2.5 px-3 py-2 text-[13px] text-red-500 hover:bg-red-50 text-left transition-colors" @click="openDelete(p)">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Delete permission
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- MODALS -->
        <Teleport to="body">
            <!-- Create -->
            <div v-if="modal.createPerm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-[2px] p-4" @click.self="modal.createPerm = false">
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-zinc-700 w-full max-w-md dark:shadow-none">
                    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-zinc-800">
                        <div><h2 class="text-[16px] font-semibold text-gray-900 dark:text-zinc-100">Create permission</h2><p class="text-[12px] text-gray-400 dark:text-zinc-500 mt-0.5">Add a new granular permission.</p></div>
                        <button class="p-1.5 rounded-md text-gray-400 dark:text-zinc-500 hover:bg-gray-100 dark:hover:bg-zinc-800 dark:bg-zinc-800 transition-colors" @click="modal.createPerm = false"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <div class="px-6 py-5 space-y-4">
                        <div><label class="block text-[12px] font-semibold text-gray-700 dark:text-zinc-300 mb-1.5">Permission name</label><input v-model="permForm.name" type="text" placeholder="e.g. Manage Users" class="w-full h-9 px-3 text-[13px] border border-gray-300 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 placeholder:text-gray-300 transition-all bg-white dark:bg-zinc-900 text-gray-900 dark:text-zinc-100" @input="autoSlug"/></div>
                        <div><label class="block text-[12px] font-semibold text-gray-700 dark:text-zinc-300 mb-1.5">Slug</label>
                            <div class="flex h-9 border border-gray-300 dark:border-zinc-700 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-400 transition-all bg-white dark:bg-zinc-900">
                                <span class="px-3 text-[11px] font-mono text-gray-400 dark:text-zinc-500 bg-gray-50 dark:bg-zinc-900 flex items-center border-r border-gray-200 dark:border-zinc-700">slug:</span>
                                <input v-model="permForm.slug" type="text" placeholder="manage-users" class="flex-1 px-3 text-[12px] font-mono focus:outline-none placeholder:text-gray-300 bg-transparent text-gray-900 dark:text-zinc-100"/>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-700 dark:text-zinc-300 mb-1.5">Group Category</label>
                            <select v-model="permForm.group" class="w-full h-9 px-3 text-[13px] bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 text-gray-900 dark:text-zinc-100 transition-all cursor-pointer">
                                <option v-for="opt in standardGroupOptions" :key="opt" :value="opt">
                                    {{ opt.toUpperCase() }}
                                </option>
                            </select>
                        </div>
                        <div><label class="block text-[12px] font-semibold text-gray-700 dark:text-zinc-300 mb-1.5">Description <span class="font-normal text-gray-400 dark:text-zinc-500">(optional)</span></label><input v-model="permForm.description" type="text" placeholder="Short description" class="w-full h-9 px-3 text-[13px] border border-gray-300 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 placeholder:text-gray-300 transition-all bg-white dark:bg-zinc-900 text-gray-900 dark:text-zinc-100"/></div>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 dark:border-zinc-800 flex justify-end gap-2">
                        <button class="h-9 px-4 rounded-lg text-[13px] border border-gray-200 dark:border-zinc-700 text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 dark:bg-zinc-900 transition-colors" @click="modal.createPerm = false">Cancel</button>
                        <button :disabled="loading" class="h-9 px-5 rounded-lg text-[13px] font-semibold bg-gray-900 hover:bg-black text-white shadow-sm transition-colors disabled:opacity-40 dark:shadow-none" @click="submitCreate">{{ loading ? 'Creating…' : 'Create permission' }}</button>
                    </div>
                </div>
            </div>

            <!-- Edit -->
            <div v-if="modal.editPerm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-[2px] p-4" @click.self="modal.editPerm = false">
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-zinc-700 w-full max-w-md dark:shadow-none">
                    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-zinc-800">
                        <h2 class="text-[16px] font-semibold text-gray-900 dark:text-zinc-100">Edit permission</h2>
                        <button class="p-1.5 rounded-md text-gray-400 dark:text-zinc-500 hover:bg-gray-100 dark:hover:bg-zinc-800 dark:bg-zinc-800 transition-colors" @click="modal.editPerm = false"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <div class="px-6 py-5 space-y-4">
                        <div><label class="block text-[12px] font-semibold text-gray-700 dark:text-zinc-300 mb-1.5">Name</label><input v-model="permForm.name" type="text" class="w-full h-9 px-3 text-[13px] border border-gray-300 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition-all bg-white dark:bg-zinc-900 text-gray-900 dark:text-zinc-100"/></div>
                        <div><label class="block text-[12px] font-semibold text-gray-700 dark:text-zinc-300 mb-1.5">Slug</label>
                            <div class="flex h-9 border border-gray-300 dark:border-zinc-700 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-400 transition-all bg-white dark:bg-zinc-900">
                                <span class="px-3 text-[11px] font-mono text-gray-400 dark:text-zinc-500 bg-gray-50 dark:bg-zinc-900 flex items-center border-r border-gray-200 dark:border-zinc-700">slug:</span>
                                <input v-model="permForm.slug" type="text" class="flex-1 px-3 text-[12px] font-mono focus:outline-none bg-transparent text-gray-900 dark:text-zinc-100"/>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-700 dark:text-zinc-300 mb-1.5">Group Category</label>
                            <select v-model="permForm.group" class="w-full h-9 px-3 text-[13px] bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 text-gray-900 dark:text-zinc-100 transition-all cursor-pointer">
                                <option v-for="opt in standardGroupOptions" :key="opt" :value="opt">
                                    {{ opt.toUpperCase() }}
                                </option>
                            </select>
                        </div>
                        <div><label class="block text-[12px] font-semibold text-gray-700 dark:text-zinc-300 mb-1.5">Description</label><input v-model="permForm.description" type="text" class="w-full h-9 px-3 text-[13px] border border-gray-300 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition-all bg-white dark:bg-zinc-900 text-gray-900 dark:text-zinc-100"/></div>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 dark:border-zinc-800 flex justify-end gap-2">
                        <button class="h-9 px-4 rounded-lg text-[13px] border border-gray-200 dark:border-zinc-700 text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 dark:bg-zinc-900 transition-colors" @click="modal.editPerm = false">Cancel</button>
                        <button :disabled="loading" class="h-9 px-4 rounded-lg text-[13px] font-semibold bg-gray-900 hover:bg-black text-white transition-colors disabled:opacity-40" @click="submitEdit">{{ loading ? 'Saving…' : 'Save changes' }}</button>
                    </div>
                </div>
            </div>

            <!-- Delete -->
            <div v-if="modal.deletePerm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-[2px] p-4">
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-zinc-700 w-full max-w-sm dark:shadow-none">
                    <div class="px-6 py-5">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center mb-4"><svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
                        <h2 class="text-[15px] font-semibold text-gray-900 dark:text-zinc-100 mb-1">Delete permission</h2>
                        <p class="text-[13px] text-gray-500 dark:text-zinc-400">Delete <strong class="text-gray-800 dark:text-zinc-200">{{ deletingPerm?.name }}</strong>? It will be removed from all roles.</p>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 dark:border-zinc-800 flex justify-end gap-2">
                        <button class="h-9 px-4 rounded-lg text-[13px] border border-gray-200 dark:border-zinc-700 text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 dark:bg-zinc-900 transition-colors" @click="modal.deletePerm = false">Cancel</button>
                        <button class="h-9 px-4 rounded-lg text-[13px] font-semibold bg-red-600 hover:bg-red-700 text-white transition-colors" @click="confirmDelete">Delete</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
