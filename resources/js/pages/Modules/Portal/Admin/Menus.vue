<script setup>
import { Head, router, useForm } from "@inertiajs/vue3";
import {
	ChevronDown,
	ChevronRight,
	Edit2,
	FileText,
	GripVertical,
	Link as LinkIcon,
	Plus,
	Save,
	Trash2,
	X,
} from "lucide-vue-next";
import { ref, watch } from "vue";
import draggable from "vuedraggable";
import PortalAdminLayout from "@/layouts/PortalAdminLayout.vue";
import DeleteConfirmModal from "@/components/DeleteConfirmModal.vue";

const props = defineProps({
	menus: Array,
	pages: Array,
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const currentEditId = ref(null);
const isSavingOrder = ref(false);

const form = useForm({
	title: "",
	url: "",
	portal_page_id: null,
	parent_id: null,
	order: 0,
});

// Local state copy for dragging
const localMenus = ref([]);
const expandedMenus = ref({});
const toggleMenu = (menuId) => {
	expandedMenus.value[menuId] = !expandedMenus.value[menuId];
};

watch(
	() => props.menus,
	(newVal) => {
		const cloned = JSON.parse(JSON.stringify(newVal || []));
		localMenus.value = cloned.map((menu) => {
			if (!menu.children) {
				menu.children = [];
			}
			return menu;
		});
	},
	{ immediate: true, deep: true },
);

const openModal = (menu = null, parentId = null) => {
	isEditing.value = !!menu;
	if (menu) {
		currentEditId.value = menu.id;
		form.title = menu.title;
		form.url = menu.url || "";
		form.portal_page_id = menu.portal_page_id || null;
		form.parent_id = menu.parent_id || null;
		form.order = menu.order || 0;
	} else {
		currentEditId.value = null;
		form.reset();
		form.parent_id = parentId;
	}
	isModalOpen.value = true;
};

const closeModal = () => {
	isModalOpen.value = false;
	form.reset();
	form.clearErrors();
};

const saveMenu = () => {
	if (isEditing.value) {
		form.put(`/portal-admin/menus/${currentEditId.value}`, {
			onSuccess: () => closeModal(),
		});
	} else {
		form.post("/portal-admin/menus", {
			onSuccess: () => closeModal(),
		});
	}
};

const isDeleteMenuModalOpen = ref(false);
const deleteMenuId = ref(null);

const deleteMenu = (id) => {
	deleteMenuId.value = id;
	isDeleteMenuModalOpen.value = true;
};

const handleDeleteMenu = () => {
	if (deleteMenuId.value !== null) {
		form.delete(`/portal-admin/menus/${deleteMenuId.value}`, {
			onSuccess: () => {
				isDeleteMenuModalOpen.value = false;
				deleteMenuId.value = null;
			},
		});
	}
};

// Auto-serialize and send new menu structure on drag end
const onDragEnd = () => {
	isSavingOrder.value = true;
	const serialized = [];

	localMenus.value.forEach((menu, index) => {
		// Main menus
		serialized.push({
			id: menu.id,
			parent_id: null,
			order: index,
		});

		// Children menus
		if (menu.children && menu.children.length > 0) {
			menu.children.forEach((child, childIndex) => {
				// Flatten grandchildren if any (unsupported 3-level nesting)
				if (child.children && child.children.length > 0) {
					child.children.forEach((grandChild) => {
						localMenus.value.push(grandChild);
					});
					child.children = [];
				}

				serialized.push({
					id: child.id,
					parent_id: menu.id,
					order: childIndex,
				});
			});
		}
	});

	router.post(
		"/portal-admin/menus/reorder",
		{ menus: serialized },
		{
			preserveScroll: true,
			onFinish: () => {
				isSavingOrder.value = false;
			},
		},
	);
};
</script>

<template>
    <PortalAdminLayout title="Menu Navigation Management">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Navigation Menus</h1>
                    <p class="text-sm text-slate-500 mt-1">Manage main navbar and dropdown items</p>
                </div>
                <button @click="openModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-bold shadow-sm hover:bg-blue-700 transition-colors">
                    <Plus class="w-4 h-4" /> Add Main Menu
                </button>
            </div>

            <!-- Menus List -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 relative">
                <!-- Saving Order Loader Overlay -->
                <div v-if="isSavingOrder" class="absolute inset-0 bg-white/60 dark:bg-slate-950/60 backdrop-blur-xs flex items-center justify-center z-50 rounded-2xl">
                    <div class="flex items-center gap-3 bg-white dark:bg-slate-800 px-5 py-3 rounded-2xl shadow-lg border border-slate-100 dark:border-slate-800">
                        <svg class="animate-spin h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-200">Updating menu order...</span>
                    </div>
                </div>

                <div v-if="localMenus.length === 0" class="text-center py-12 text-slate-500">
                    Belum ada menu navigasi.
                </div>
                
                <div v-else class="flex flex-col gap-4">
                    <draggable 
                        v-model="localMenus" 
                        group="menus" 
                        item-key="id" 
                        handle=".handle-main" 
                        @end="onDragEnd"
                        class="flex flex-col gap-4"
                        ghost-class="opacity-40"
                    >
                        <template #item="{ element: menu }">
                            <div class="flex flex-col gap-2 p-2 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                                <!-- Main Menu Item -->
                                <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800 hover:bg-slate-100/50 dark:hover:bg-slate-800/70 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <button 
                                            type="button" 
                                            @click="toggleMenu(menu.id)" 
                                            class="p-1 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg text-slate-400 hover:text-slate-650 transition-colors shrink-0"
                                        >
                                            <ChevronDown 
                                                class="w-4 h-4 transition-transform duration-200"
                                                :class="expandedMenus[menu.id] ? '' : '-rotate-90'"
                                            />
                                        </button>
                                        <GripVertical class="w-5 h-5 text-slate-400 cursor-move handle-main shrink-0" />
                                        <span class="font-bold text-slate-800 dark:text-white text-[15px] sm:text-lg cursor-pointer select-none truncate max-w-[120px] sm:max-w-xs" @click="toggleMenu(menu.id)">{{ menu.title }}</span>
                                        <span v-if="menu.page" class="text-[10px] bg-emerald-100 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 px-2 py-0.5 rounded-full flex items-center gap-1 shrink-0"><FileText class="w-3 h-3"/> {{ menu.page.title }}</span>
                                        <span v-else-if="menu.url" class="text-[10px] bg-blue-100 dark:bg-blue-950/50 text-blue-700 dark:text-blue-400 px-2 py-0.5 rounded-full flex items-center gap-1 shrink-0"><LinkIcon class="w-3 h-3"/> URL</span>
                                    </div>
                                    <div class="flex items-center gap-1 sm:gap-2 shrink-0">
                                        <button @click="openModal(null, menu.id)" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-slate-800 rounded-lg transition-colors" title="Add Submenu">
                                            <Plus class="w-4 h-4" />
                                        </button>
                                        <button @click="openModal(menu)" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-slate-800 rounded-lg transition-colors">
                                            <Edit2 class="w-4 h-4" />
                                        </button>
                                        <button @click="deleteMenu(menu.id)" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-lg transition-colors">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Sub Menus (Collapsible Dropdown) -->
                                <div v-show="expandedMenus[menu.id]" class="transition-all duration-300">
                                    <draggable 
                                        v-model="menu.children" 
                                        group="menus" 
                                        item-key="id" 
                                        handle=".handle-sub" 
                                        @end="onDragEnd"
                                        class="flex flex-col gap-2 pl-6 sm:pl-12 pr-2 sm:pr-4 py-2 min-h-[30px] rounded-xl border border-dashed border-slate-200/60 dark:border-slate-800 bg-white/30 dark:bg-slate-900/30"
                                        ghost-class="opacity-40"
                                    >
                                        <template #item="{ element: child }">
                                            <div class="flex items-center justify-between p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                                                    <GripVertical class="w-4 h-4 text-slate-400 cursor-move handle-sub shrink-0" />
                                                    <ChevronRight class="w-4 h-4 text-slate-300 shrink-0" />
                                                    <span class="font-medium text-slate-700 dark:text-slate-200 text-sm truncate max-w-[100px] sm:max-w-xs">{{ child.title }}</span>
                                                    <span v-if="child.page" class="text-[10px] bg-emerald-100 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 px-2 py-0.5 rounded-full flex items-center gap-1 shrink-0"><FileText class="w-3 h-3"/> {{ child.page.title }}</span>
                                                    <span v-else-if="child.url" class="text-[10px] bg-blue-100 dark:bg-blue-950/50 text-blue-700 dark:text-blue-400 px-2 py-0.5 rounded-full flex items-center gap-1 shrink-0"><LinkIcon class="w-3 h-3"/> {{ child.url }}</span>
                                                </div>
                                                <div class="flex items-center gap-1 sm:gap-2 shrink-0">
                                                    <button @click="openModal(child)" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-slate-800 rounded-lg transition-colors">
                                                        <Edit2 class="w-4 h-4" />
                                                    </button>
                                                    <button @click="deleteMenu(child.id)" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-lg transition-colors">
                                                        <Trash2 class="w-4 h-4" />
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                    </draggable>
                                </div>
                            </div>
                        </template>
                    </draggable>
                </div>
            </div>
        </div>

        <!-- Menu Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeModal"></div>
            <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
                <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-lg text-slate-900 dark:text-white">
                        {{ isEditing ? 'Edit Menu' : 'Add Menu Item' }}
                    </h3>
                    <button @click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                
                <form @submit.prevent="saveMenu" class="p-5 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Label Menu</label>
                        <input v-model="form.title" type="text" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 focus:ring-blue-600 focus:border-blue-600" required>
                        <span v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</span>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Link to Page (Optional)</label>
                        <select v-model="form.portal_page_id" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 focus:ring-blue-600 focus:border-blue-600">
                            <option :value="null">-- Tidak ditautkan ke Page --</option>
                            <option v-for="page in pages" :key="page.id" :value="page.id">{{ page.title }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Atau Custom URL (Optional)</label>
                        <input v-model="form.url" type="text" placeholder="https://... atau /berita" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 focus:ring-blue-600 focus:border-blue-600">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Urutan (Order)</label>
                        <input v-model="form.order" type="number" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 focus:ring-blue-600 focus:border-blue-600">
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3">
                        <button type="button" @click="closeModal" class="px-4 py-2 font-semibold text-slate-500 hover:bg-slate-50 rounded-xl transition-colors">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="flex items-center gap-2 px-6 py-2 bg-blue-600 text-white rounded-xl font-bold shadow-sm hover:bg-blue-700 disabled:opacity-50">
                            <Save class="w-4 h-4" /> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <DeleteConfirmModal
            :show="isDeleteMenuModalOpen"
            title="Hapus Menu"
            message="Apakah Anda yakin ingin menghapus menu ini beserta semua sub-menunya? Tindakan ini tidak dapat dibatalkan."
            @confirm="handleDeleteMenu"
            @cancel="isDeleteMenuModalOpen = false"
        />
    </PortalAdminLayout>
</template>
