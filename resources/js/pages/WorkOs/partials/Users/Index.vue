<script setup lang="ts">
import { router, usePage } from "@inertiajs/vue3";
import { computed, onMounted, onUnmounted, reactive, ref } from "vue";
import {
	formatDate,
	generatePassword,
	statusBadgeClass,
	statusDot,
	statusLabel,
	toast,
	typeBadge,
	typeLabel,
} from "../../composables/useWorkOs";
import Progress from "../../ui/Progress.vue";

const props = defineProps<{
	users: Array<any>;
	roles?: Array<any>;
	pendingCount: number;
}>();

const emit = defineEmits<(e: "openDetail", user: any) => void>();

// ─── SEARCH / FILTER ─────────────────────────────────────────────────────────
const userSearch = ref("");
const filterStatus = ref("all");
const filterRole = ref("all");
const userTab = ref<"users" | "invitations">("users");
const statusDropdownOpen = ref(false);
const roleDropdownOpen = ref(false);

const availableRoles = computed(() => {
	const list = [
		{ label: "Mahasiswa", value: "mahasiswa" },
		{ label: "Alumni", value: "alumni" },
		{ label: "Mitra", value: "mitra" },
		{ label: "Dosen", value: "dosen" },
		{ label: "Staff", value: "staff" },
		{ label: "Super Admin", value: "super_admin" },
	];

	if (props.roles) {
		props.roles.forEach((r) => {
			if (r.slug && !list.some((item) => item.value === r.slug)) {
				list.push({ label: r.nama, value: r.slug });
			}
		});
	}

	props.users.forEach((u) => {
		if (u.role?.slug && !list.some((item) => item.value === u.role.slug)) {
			list.push({ label: u.role.nama, value: u.role.slug });
		}
		if (u.module_roles) {
			u.module_roles.forEach((mr: any) => {
				if (mr.role_slug && !list.some((item) => item.value === mr.role_slug)) {
					list.push({
						label: mr.role_name || mr.role_slug,
						value: mr.role_slug,
					});
				}
			});
		}
	});

	return list;
});

const filteredUsers = computed(() => {
	let r = props.users;

	if (filterStatus.value !== "all") {
		r = r.filter((u) => u.status_approval === filterStatus.value);
	}

	if (filterRole.value !== "all") {
		r = r.filter((u) => {
			if (u.user_type === filterRole.value) return true;
			if (u.role && u.role.slug === filterRole.value) return true;
			if (u.module_roles?.some((mr: any) => mr.role_slug === filterRole.value))
				return true;
			return false;
		});
	}

	if (userSearch.value.trim()) {
		const q = userSearch.value.toLowerCase();
		r = r.filter(
			(u) =>
				u.name?.toLowerCase().includes(q) || u.email?.toLowerCase().includes(q),
		);
	}
	return r;
});

function closeAllDropdowns(e: MouseEvent) {
	const target = e.target as HTMLElement;
	if (!target.closest("[data-dropdown]")) {
		statusDropdownOpen.value = false;
		roleDropdownOpen.value = false;
	}
}

onMounted(() => document.addEventListener("click", closeAllDropdowns));
onUnmounted(() => document.removeEventListener("click", closeAllDropdowns));

// ─── MODALS ───────────────────────────────────────────────────────────────────
const modal = reactive({ createUser: false, uploadUsers: false });

// ─── UPLOAD MASSAL ────────────────────────────────────────────────────────────
const page = usePage();
const uploadStep = ref(1);
const selectedType = ref<"mahasiswa" | "alumni" | "dosen" | "mitra">(
	"mahasiswa",
);
const uploadFile = ref<File | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const isDragOver = ref(false);

const isUploading = ref(false);
const uploadProgress = ref(0);
const maxFileSize = 1024 * 1024 * 1024; // 1 GB in bytes

const localErrors = ref<any[]>([]);
const localSuccess = ref<string | null>(null);
const generalError = ref<string | null>(null);

function openUploadModal() {
	modal.uploadUsers = true;
	uploadStep.value = 1;
	uploadFile.value = null;
	localErrors.value = [];
	localSuccess.value = null;
	generalError.value = null;
	isUploading.value = false;
	uploadProgress.value = 0;
	if (fileInput.value) fileInput.value.value = "";
}

function handleFileChange(e: Event) {
	const files = (e.target as HTMLInputElement).files;
	if (files && files.length > 0) {
		const file = files[0];
		if (file.size > maxFileSize) {
			generalError.value = "Ukuran file maksimal adalah 1 GB.";
			toast("File terlalu besar. Maksimal 1 GB.", "error");
			uploadFile.value = null;
			return;
		}
		uploadFile.value = file;
		localErrors.value = [];
		generalError.value = null;
	}
}

function handleDrop(e: DragEvent) {
	isDragOver.value = false;
	const files = e.dataTransfer?.files;
	if (files && files.length > 0) {
		const file = files[0];
		if (file.size > maxFileSize) {
			generalError.value = "Ukuran file maksimal adalah 1 GB.";
			toast("File terlalu besar. Maksimal 1 GB.", "error");
			uploadFile.value = null;
			return;
		}
		uploadFile.value = file;
		localErrors.value = [];
		generalError.value = null;
	}
}

function selectUserType(type: "mahasiswa" | "alumni" | "dosen" | "mitra") {
	selectedType.value = type;
	uploadStep.value = 2;
}

function getTemplateUrl(format: "csv" | "xlsx") {
	return `/workos/users/template?type=${selectedType.value}&format=${format}`;
}

function submitUpload() {
	if (loading.value || !uploadFile.value) return;
	loading.value = true;
	isUploading.value = true;
	uploadProgress.value = 0;
	localErrors.value = [];
	localSuccess.value = null;
	generalError.value = null;

	const formData = new FormData();
	formData.append("file", uploadFile.value);
	formData.append("user_type", selectedType.value);

	router.post("/workos/users/upload", formData, {
		preserveScroll: true,
		onProgress: (event) => {
			if (event.percentage) {
				uploadProgress.value = event.percentage;
			}
		},
		onSuccess: () => {
			const flash = page.props.flash as any;
			if (flash?.success) {
				localSuccess.value = flash.success;
				toast(flash.success, "success");
				uploadFile.value = null;
				uploadStep.value = 1;
				modal.uploadUsers = false;
			}
		},
		onError: (errors) => {
			if (errors.file) {
				generalError.value = errors.file;
				toast(errors.file, "error");
			} else {
				toast("Gagal mengunggah file. Periksa kembali data Anda.", "error");
			}
		},
		onFinish: () => {
			loading.value = false;
			isUploading.value = false;
			const flash = page.props.flash as any;
			if (flash?.import_errors && flash.import_errors.length > 0) {
				localErrors.value = flash.import_errors;
				toast("Beberapa baris data gagal divalidasi.", "error");
			}
		},
	});
}

const createForm = reactive({
	first_name: "",
	last_name: "",
	email: "",
	password: "",
	user_type: "mahasiswa",
	nomor_induk: "",
});
const showPw = ref(false);
const loading = ref(false);

function genPw() {
	createForm.password = generatePassword();
}

function resetForm() {
	Object.assign(createForm, {
		first_name: "",
		last_name: "",
		email: "",
		password: "",
		user_type: "mahasiswa",
		nomor_induk: "",
	});
	showPw.value = false;
}

function submitCreateUser() {
	if (loading.value) return;
	loading.value = true;
	router.post(
		"/workos/users",
		{ ...createForm },
		{
			preserveScroll: true,
			onSuccess: () => {
				modal.createUser = false;
				resetForm();
				toast("User berhasil dibuat.", "success");
			},
			onError: () =>
				toast("Gagal membuat user. Periksa kembali data.", "error"),
			onFinish: () => {
				loading.value = false;
			},
		},
	);
}
</script>

<template>
    <div class="w-full px-4 sm:px-8 pt-4 sm:pt-8 pb-12 min-w-0" style="font-family: var(--wos-font)">
        <!-- Page header -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-8">
            <div>
                <h1 class="text-[22px] font-semibold text-[#111827] tracking-tight mb-1">Users</h1>
                <p class="text-[13px] text-[#6b7280]">Manage users and invitations authenticated or invited through AuthKit.</p>
            </div>
            <div class="flex items-center gap-2">
                <button
                    v-if="userTab === 'users'"
                    class="h-[34px] px-4 border border-[#d1d5db] text-[#374151] rounded-md text-[13px] font-semibold hover:bg-[#f9fafb] transition-colors bg-white shadow-sm flex items-center gap-1.5 cursor-pointer"
                    @click="openUploadModal"
                >
                    <svg class="w-4 h-4 text-[#4b5563]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Upload users
                </button>
                <button
                    class="h-[34px] px-4 bg-[#6366f1] text-white rounded-md text-[13px] font-semibold hover:bg-[#4f46e5] transition-colors shadow-sm cursor-pointer"
                    @click="modal.createUser = true"
                >
                    {{ userTab === 'users' ? 'Create user' : 'Invite user' }}
                </button>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex items-end border-b border-[#e5e7eb] mb-5 overflow-x-auto wos-scroll" role="tablist">
            <button
                v-for="tab in [{ id: 'users', l: 'Users' }, { id: 'invitations', l: 'Invitations' }]"
                :key="tab.id"
                role="tab"
                :aria-selected="userTab === tab.id"
                :class="[
                    'flex items-center gap-1.5 px-1 pb-3 mr-6 text-[13px] font-medium border-b-2 -mb-px transition-all duration-150 whitespace-nowrap shrink-0',
                    userTab === tab.id
                        ? 'border-[#2563EB] text-[#111827]'
                        : 'border-transparent text-[#6b7280] hover:text-[#374151] hover:border-[#d1d5db]',
                ]"
                @click="userTab = tab.id as 'users' | 'invitations'"
            >
                {{ tab.l }}
            </button>
        </div>

        <!-- Toolbar -->
        <div v-if="userTab === 'users'" class="flex flex-col sm:flex-row sm:items-center gap-2 mb-4">
            <!-- Search -->
            <div class="relative w-full sm:w-auto">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#9ca3af]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" />
                </svg>
                <input
                    v-model="userSearch"
                    type="search"
                    placeholder="Search"
                    class="w-full sm:w-[280px] h-[34px] pl-8 pr-3 text-[13px] border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#a78bfa] focus:ring-1 focus:ring-[#a78bfa] transition-colors placeholder:text-[#9ca3af] text-[#111827] shadow-sm"
                />
            </div>

            <!-- Status filter -->
            <div class="relative inline-block text-left" data-dropdown>
                <button 
                    @click.stop="statusDropdownOpen = !statusDropdownOpen; roleDropdownOpen = false"
                    class="flex items-center gap-1.5 h-[34px] px-3 border border-[#d1d5db] rounded-md text-[13px] text-[#4b5563] hover:bg-[#f9fafb] transition-colors bg-white shadow-sm"
                >
                    <svg class="w-3.5 h-3.5 text-[#9ca3af]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Status: <span class="font-semibold text-[#111827]">{{ filterStatus === 'all' ? 'All' : (filterStatus.charAt(0).toUpperCase() + filterStatus.slice(1)) }}</span>
                </button>
                <div 
                    v-show="statusDropdownOpen" 
                    class="absolute left-0 mt-1 w-44 bg-white border border-[#e5e7eb] rounded-lg shadow-lg py-1 z-50"
                >
                    <button 
                        v-for="status in ['all', 'approved', 'pending', 'rejected']" 
                        :key="status"
                        @click="filterStatus = status; statusDropdownOpen = false"
                        class="w-full flex items-center justify-between px-3.5 py-2 text-[12.5px] text-[#374151] hover:bg-[#f9fafb] text-left transition-colors"
                    >
                        <span>{{ status === 'all' ? 'All Statuses' : (status.charAt(0).toUpperCase() + status.slice(1)) }}</span>
                        <svg v-if="filterStatus === status" class="w-3 h-3 text-[#2563EB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Role filter -->
            <div class="relative inline-block text-left" data-dropdown>
                <button 
                    @click.stop="roleDropdownOpen = !roleDropdownOpen; statusDropdownOpen = false"
                    class="flex items-center gap-1.5 h-[34px] px-3 border border-[#d1d5db] rounded-md text-[13px] text-[#4b5563] hover:bg-[#f9fafb] transition-colors bg-white shadow-sm"
                >
                    <svg class="w-3.5 h-3.5 text-[#9ca3af]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Role: <span class="font-semibold text-[#111827] truncate max-w-[120px] inline-block align-bottom leading-none">{{ filterRole === 'all' ? 'All' : (availableRoles.find(r => r.value === filterRole)?.label ?? filterRole) }}</span>
                </button>
                <div 
                    v-show="roleDropdownOpen" 
                    class="absolute left-0 mt-1 w-52 bg-white border border-[#e5e7eb] rounded-lg shadow-lg py-1 z-50 max-h-60 overflow-y-auto wos-scroll"
                >
                    <button 
                        @click="filterRole = 'all'; roleDropdownOpen = false"
                        class="w-full flex items-center justify-between px-3.5 py-2 text-[12.5px] text-[#374151] hover:bg-[#f9fafb] text-left transition-colors"
                    >
                        <span>All Roles</span>
                        <svg v-if="filterRole === 'all'" class="w-3 h-3 text-[#2563EB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>
                    <button 
                        v-for="role in availableRoles" 
                        :key="role.value"
                        @click="filterRole = role.value; roleDropdownOpen = false"
                        class="w-full flex items-center justify-between px-3.5 py-2 text-[12.5px] text-[#374151] hover:bg-[#f9fafb] text-left transition-colors"
                    >
                        <span>{{ role.label }}</span>
                        <svg v-if="filterRole === role.value" class="w-3 h-3 text-[#2563EB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Invitations Toolbar -->
        <div v-if="userTab === 'invitations'" class="flex flex-col sm:flex-row sm:items-center gap-2 mb-4">
            <!-- Search -->
            <div class="relative w-full sm:w-auto">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#9ca3af]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" />
                </svg>
                <input
                    type="search"
                    placeholder="Search"
                    class="w-full sm:w-[280px] h-[34px] pl-8 pr-3 text-[13px] border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#a78bfa] focus:ring-1 focus:ring-[#a78bfa] transition-colors placeholder:text-[#9ca3af] text-[#111827] shadow-sm"
                />
            </div>
        </div>

        <!-- Invitations Empty State -->
        <div v-if="userTab === 'invitations'" class="border border-[#e5e7eb] rounded-lg bg-white p-12 flex flex-col items-center justify-center text-center mt-4">
            <div class="w-12 h-12 flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-[#9ca3af]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <h3 class="text-[15px] font-semibold text-[#111827] mb-3">No invitations have been created in this environment</h3>
            <button
                class="h-[34px] px-4 border border-[#d1d5db] rounded-md text-[13px] font-semibold text-[#374151] hover:bg-[#f9fafb] transition-colors bg-white shadow-sm"
                @click="modal.createUser = true"
            >
                Invite user
            </button>
        </div>

        <!-- Users Table -->
        <div v-if="userTab === 'users'" class="border border-[#e5e7eb] rounded-lg overflow-x-auto bg-white shadow-sm">
            <table class="w-full text-left whitespace-nowrap">
                <thead>
                    <tr class="bg-[#f9fafb] border-b border-[#e5e7eb]">
                        <th class="px-4 py-3 text-[12px] font-semibold text-[#111827]">User</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-[#111827]">Organizations</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-[#111827]">Status</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-[#111827]">Sign-in count</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-[#111827]">Last sign-in</th>
                        <th class="px-4 py-3 text-[12px] font-semibold text-[#111827] flex items-center gap-1">
                            Created
                            <svg class="w-3.5 h-3.5 text-[#9ca3af]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                        </th>
                        <th class="px-4 py-3 w-6" />
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e5e7eb]">
                    <tr v-if="filteredUsers.length === 0">
                        <td colspan="7" class="py-12 text-center text-[13px] text-[#6b7280]">
                            No users found.
                        </td>
                    </tr>
                    <tr
                        v-for="u in filteredUsers"
                        :key="u.id"
                        class="hover:bg-[#f9fafb] transition-colors cursor-pointer group"
                        @click="emit('openDetail', u)"
                    >
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-[#f3f4f6] border border-[#e5e7eb] flex items-center justify-center text-[#111827] text-[12px] font-bold shrink-0 overflow-hidden">
                                    <img v-if="u.foto_path" :src="u.foto_path" :alt="u.name" class="w-full h-full object-cover" />
                                    <span v-else>{{ u.name?.charAt(0)?.toUpperCase() }}</span>
                                </div>
                                <div>
                                    <p class="text-[13px] font-medium text-[#111827] leading-tight">{{ u.email }}</p>
                                    <p class="text-[12px] text-[#6b7280] leading-tight mt-0.5">{{ u.name }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[13px] text-[#2563EB] hover:underline" v-if="u.module_roles?.length > 0">
                                {{ u.module_roles[0].module_name || 'Organization' }}
                                <span v-if="u.module_roles.length > 1" class="text-[#6b7280] no-underline hover:no-underline">(+{{ u.module_roles.length - 1 }})</span>
                            </span>
                            <span v-else class="text-[13px] text-[#9ca3af]">None</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full" :class="u.status_approval === 'approved' ? 'bg-[#10b981]' : (u.status_approval === 'pending' ? 'bg-[#f59e0b]' : 'bg-[#ef4444]')" />
                                <span class="text-[13px] font-medium" :class="u.status_approval === 'approved' ? 'text-[#10b981]' : (u.status_approval === 'pending' ? 'text-[#f59e0b]' : 'text-[#ef4444]')">
                                    {{ u.status_approval ? u.status_approval.charAt(0).toUpperCase() + u.status_approval.slice(1) : 'Unknown' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-[13px] text-[#111827]">
                            1
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-[13px] text-[#111827] leading-tight">{{ u.created_at ? u.created_at.split(', ')[0] + ', ' + u.created_at.split(', ')[1] : 'Unknown' }}</p>
                            <p class="text-[12px] text-[#6b7280] leading-tight mt-0.5">{{ u.created_at ? u.created_at.split(', ')[2] : '' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-[13px] text-[#111827] leading-tight">{{ u.created_at ? u.created_at.split(', ')[0].split(' ')[1] : '—' }}</p>
                            <p class="text-[12px] text-[#6b7280] leading-tight mt-0.5">{{ u.created_at ? u.created_at.split(', ')[2] : '' }}</p>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <svg class="w-4 h-4 text-[#d1d5db] group-hover:text-[#9ca3af] transition-colors ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ─── CREATE USER MODAL ─── -->
        <Teleport to="body">
            <Transition
                enter-from-class="opacity-0"
                enter-active-class="transition-opacity duration-200"
                leave-to-class="opacity-0"
                leave-active-class="transition-opacity duration-150"
            >
                <div v-if="modal.createUser" class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 backdrop-blur-sm p-4" @click.self="modal.createUser = false">
                    <Transition
                        enter-from-class="opacity-0 scale-95"
                        enter-active-class="transition-all duration-200 ease-out"
                        leave-to-class="opacity-0 scale-95"
                        leave-active-class="transition-all duration-150"
                    >
                        <div v-if="modal.createUser" class="bg-white rounded-xl shadow-2xl border border-[#e5e7eb] w-full max-w-[480px]">
                            <!-- Modal Header -->
                            <div class="px-6 pt-6 pb-4">
                                <h2 class="text-[18px] font-semibold text-[#111827] tracking-tight">{{ userTab === 'users' ? 'Create user' : 'Invite user' }}</h2>
                            </div>

                            <!-- Modal Body -->
                            <div class="px-6 pb-6 space-y-4">
                                <!-- Names -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[13px] font-semibold text-[#374151] mb-1.5">First name</label>
                                        <input
                                            v-model="createForm.first_name"
                                            type="text"
                                            placeholder="Jane (optional)"
                                            class="w-full h-9 px-3 text-[13px] border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#a78bfa] focus:ring-1 focus:ring-[#a78bfa] transition-colors placeholder:text-[#9ca3af] text-[#111827]"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[13px] font-semibold text-[#374151] mb-1.5">Last name</label>
                                        <input
                                            v-model="createForm.last_name"
                                            type="text"
                                            placeholder="Doe (optional)"
                                            class="w-full h-9 px-3 text-[13px] border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#a78bfa] focus:ring-1 focus:ring-[#a78bfa] transition-colors placeholder:text-[#9ca3af] text-[#111827]"
                                        />
                                    </div>
                                </div>
                                
                                <!-- Email -->
                                <div>
                                    <label class="block text-[13px] font-semibold text-[#374151] mb-1.5">Email address</label>
                                    <input
                                        v-model="createForm.email"
                                        type="email"
                                        placeholder="jane.doe@example.com"
                                        class="w-full h-9 px-3 text-[13px] border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#a78bfa] focus:ring-1 focus:ring-[#a78bfa] transition-colors placeholder:text-[#9ca3af] text-[#111827]"
                                    />
                                </div>

                                <!-- Password -->
                                <div v-if="userTab === 'users'">
                                    <label class="block text-[13px] font-semibold text-[#374151] mb-1.5">Password</label>
                                    <div class="flex gap-2">
                                        <div class="relative flex-1">
                                            <input
                                                v-model="createForm.password"
                                                :type="showPw ? 'text' : 'password'"
                                                placeholder="At least 10 characters"
                                                class="w-full h-9 px-3 pr-9 text-[13px] border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#a78bfa] focus:ring-1 focus:ring-[#a78bfa] transition-colors placeholder:text-[#9ca3af] text-[#111827]"
                                            />
                                            <button
                                                type="button"
                                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[#9ca3af] hover:text-[#4b5563] transition-colors"
                                                @click="showPw = !showPw"
                                            >
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path v-if="!showPw" stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    <path v-else stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                                </svg>
                                            </button>
                                        </div>
                                        <button
                                            type="button"
                                            class="h-9 px-3 rounded-md text-[13px] font-semibold border border-[#d1d5db] text-[#374151] hover:bg-[#f9fafb] transition-colors whitespace-nowrap bg-white shadow-sm"
                                            @click="genPw"
                                        >
                                            Generate
                                        </button>
                                    </div>
                                </div>

                                <!-- External ID -->
                                <div>
                                    <label class="block text-[13px] font-semibold text-[#374151] mb-1.5">External ID</label>
                                    <input
                                        v-model="createForm.nomor_induk"
                                        type="text"
                                        placeholder="User ID"
                                        class="w-full h-9 px-3 text-[13px] border border-[#d1d5db] rounded-md focus:outline-none focus:border-[#a78bfa] focus:ring-1 focus:ring-[#a78bfa] transition-colors placeholder:text-[#9ca3af] text-[#111827]"
                                    />
                                    <p class="text-[12.5px] text-[#6b7280] mt-1.5 leading-relaxed">
                                        Optional identifier that can be used to link this user to your system.
                                        <a href="#" class="text-[#2563EB] hover:underline inline-flex items-center gap-1">
                                            Learn more
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                        </a>
                                    </p>
                                </div>
                            </div>

                            <!-- Modal Footer -->
                            <div class="px-6 py-4 flex justify-end gap-2 border-t border-[#e5e7eb] rounded-b-xl">
                                <button
                                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] border border-[#d1d5db] hover:bg-[#f3f4f6] transition-colors bg-white shadow-sm"
                                    @click="modal.createUser = false; resetForm()"
                                >
                                    Cancel
                                </button>
                                <button
                                    :disabled="loading || !createForm.email"
                                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-[#6366f1] hover:bg-[#4f46e5] transition-colors shadow-sm disabled:opacity-50 flex items-center gap-2"
                                    @click="submitCreateUser"
                                >
                                    <svg v-if="loading" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    {{ loading ? 'Creating…' : 'Create user' }}
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

        <!-- ─── UPLOAD USERS MODAL ─── -->
        <Teleport to="body">
            <Transition
                enter-from-class="opacity-0"
                enter-active-class="transition-opacity duration-200"
                leave-to-class="opacity-0"
                leave-active-class="transition-opacity duration-150"
            >
                <div v-if="modal.uploadUsers" class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 backdrop-blur-sm p-4" @click.self="!isUploading && (modal.uploadUsers = false)">
                    <Transition
                        enter-from-class="opacity-0 scale-95"
                        enter-active-class="transition-all duration-200 ease-out"
                        leave-to-class="opacity-0 scale-95"
                        leave-active-class="transition-all duration-150"
                    >
                        <div v-if="modal.uploadUsers" class="bg-white rounded-xl shadow-2xl border border-[#e5e7eb] w-full max-w-[560px]">
                            <!-- Modal Header -->
                            <div class="px-6 pt-6 pb-4 flex items-center justify-between border-b border-[#e5e7eb]">
                                <h2 class="text-[16px] font-semibold text-[#111827] tracking-tight flex items-center gap-2">
                                    <span v-if="uploadStep === 1">Upload User Data</span>
                                    <span v-else class="flex items-center gap-1.5">
                                        <button :disabled="isUploading" @click="uploadStep = 1; localErrors = []" class="text-[#6b7280] hover:text-[#111827] transition-colors cursor-pointer disabled:opacity-30">
                                            <svg class="w-4 h-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                            </svg>
                                        </button>
                                        Upload {{ typeLabel(selectedType) }}
                                    </span>
                                </h2>
                                <button :disabled="isUploading" @click="modal.uploadUsers = false" class="text-[#9ca3af] hover:text-[#4b5563] transition-colors cursor-pointer disabled:opacity-30">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Modal Body -->
                            <!-- STEP 1: Pilih Tipe User -->
                            <div v-if="uploadStep === 1" class="p-6 space-y-4">
                                <p class="text-[13px] text-[#4b5563] mb-4">Pilih jenis data pengguna yang ingin Anda unggah secara massal:</p>
                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Mahasiswa -->
                                    <button 
                                        @click="selectUserType('mahasiswa')"
                                        class="flex flex-col items-start text-left p-5 rounded-xl border border-[#e5e7eb] hover:border-[#475569] hover:bg-slate-50 transition-all duration-200 group cursor-pointer shadow-sm"
                                    >
                                        <div class="w-9 h-9 rounded-lg bg-[#f8fafc] text-[#475569] border border-[#e2e8f0] flex items-center justify-center mb-3 group-hover:bg-[#f1f5f9] group-hover:text-[#0f172a] group-hover:border-[#cbd5e1] transition-colors">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-[14px] font-semibold text-[#111827]">Mahasiswa</h3>
                                        <p class="text-[12px] text-[#6b7280] mt-1">Impor mahasiswa aktif beserta prodi dan NIM.</p>
                                    </button>

                                    <!-- Alumni -->
                                    <button 
                                        @click="selectUserType('alumni')"
                                        class="flex flex-col items-start text-left p-5 rounded-xl border border-[#e5e7eb] hover:border-[#475569] hover:bg-slate-50 transition-all duration-200 group cursor-pointer shadow-sm"
                                    >
                                        <div class="w-9 h-9 rounded-lg bg-[#f8fafc] text-[#475569] border border-[#e2e8f0] flex items-center justify-center mb-3 group-hover:bg-[#f1f5f9] group-hover:text-[#0f172a] group-hover:border-[#cbd5e1] transition-colors">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-[14px] font-semibold text-[#111827]">Alumni</h3>
                                        <p class="text-[12px] text-[#6b7280] mt-1">Impor data alumni lengkap dengan tahun kelulusan.</p>
                                    </button>

                                    <!-- Dosen -->
                                    <button 
                                        @click="selectUserType('dosen')"
                                        class="flex flex-col items-start text-left p-5 rounded-xl border border-[#e5e7eb] hover:border-[#475569] hover:bg-slate-50 transition-all duration-200 group cursor-pointer shadow-sm"
                                    >
                                        <div class="w-9 h-9 rounded-lg bg-[#f8fafc] text-[#475569] border border-[#e2e8f0] flex items-center justify-center mb-3 group-hover:bg-[#f1f5f9] group-hover:text-[#0f172a] group-hover:border-[#cbd5e1] transition-colors">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                        </div>
                                        <h3 class="text-[14px] font-semibold text-[#111827]">Dosen</h3>
                                        <p class="text-[12px] text-[#6b7280] mt-1">Impor dosen / struktural menggunakan NIP / NIDN.</p>
                                    </button>

                                    <!-- Mitra -->
                                    <button 
                                        @click="selectUserType('mitra')"
                                        class="flex flex-col items-start text-left p-5 rounded-xl border border-[#e5e7eb] hover:border-[#475569] hover:bg-slate-50 transition-all duration-200 group cursor-pointer shadow-sm"
                                    >
                                        <div class="w-9 h-9 rounded-lg bg-[#f8fafc] text-[#475569] border border-[#e2e8f0] flex items-center justify-center mb-3 group-hover:bg-[#f1f5f9] group-hover:text-[#0f172a] group-hover:border-[#cbd5e1] transition-colors">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-[14px] font-semibold text-[#111827]">Mitra</h3>
                                        <p class="text-[12px] text-[#6b7280] mt-1">Impor mitra eksternal lengkap dengan nama perusahaan.</p>
                                    </button>
                                </div>
                            </div>

                            <!-- STEP 2: Unduh Template & Unggah File -->
                            <div v-else class="p-6 space-y-5">
                                <!-- Real-time uploading state animation -->
                                <div v-if="isUploading" class="border border-[#e2e8f0] rounded-xl p-6 bg-slate-50/50 space-y-4 shadow-sm flex flex-col justify-center">
                                    <div class="flex items-center justify-between text-[13px]">
                                        <span class="font-medium text-[#0f172a] flex items-center gap-2">
                                            <svg class="w-4 h-4 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            Mengunggah berkas...
                                        </span>
                                        <span class="font-bold text-indigo-600">{{ Math.round(uploadProgress) }}%</span>
                                    </div>
                                    <Progress :value="uploadProgress" variant="line" className="h-2 bg-slate-100" indicatorClassName="bg-indigo-600 rounded-full" />
                                    <p class="text-[11.5px] text-[#6b7280]">Mohon jangan menutup atau memuat ulang halaman ini sampai proses pengunggahan selesai.</p>
                                </div>

                                <template v-else>
                                    <!-- Bagian Unduh Template -->
                                    <div class="bg-[#f9fafb] rounded-xl p-4 border border-[#e5e7eb] flex items-center justify-between">
                                        <div>
                                            <h4 class="text-[13px] font-semibold text-[#111827]">Unduh Template Pengguna</h4>
                                            <p class="text-[11.5px] text-[#6b7280] mt-0.5">Format template disesuaikan dengan tipe {{ typeLabel(selectedType) }}.</p>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <a 
                                                :href="getTemplateUrl('csv')" 
                                                download 
                                                class="h-[30px] px-2.5 bg-white border border-[#d1d5db] text-[#374151] rounded-md text-[12px] font-semibold hover:bg-[#f3f4f6] transition-colors flex items-center gap-1 shadow-sm cursor-pointer"
                                            >
                                                .CSV
                                            </a>
                                            <a 
                                                :href="getTemplateUrl('xlsx')" 
                                                download 
                                                class="h-[30px] px-2.5 bg-emerald-600 text-white rounded-md text-[12px] font-semibold hover:bg-emerald-700 transition-colors flex items-center gap-1 shadow-sm cursor-pointer"
                                            >
                                                Excel (.xlsx)
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Drag & Drop Zone -->
                                    <div 
                                        @dragover.prevent="isDragOver = true"
                                        @dragleave="isDragOver = false"
                                        @drop.prevent="handleDrop"
                                        :class="[
                                            'border-2 border-dashed rounded-xl p-8 flex flex-col items-center justify-center text-center cursor-pointer transition-all duration-150',
                                            isDragOver ? 'border-[#6366f1] bg-[#6366f1]/5' : 'border-[#d1d5db] hover:border-[#9ca3af] bg-white'
                                        ]"
                                        @click="fileInput?.click()"
                                    >
                                        <input 
                                            ref="fileInput"
                                            type="file"
                                            accept=".csv,.xlsx"
                                            class="hidden"
                                            @change="handleFileChange"
                                        />
                                        <div class="w-10 h-10 rounded-full bg-[#f3f4f6] flex items-center justify-center mb-3">
                                            <svg class="w-5 h-5 text-[#6b7280]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                        </div>
                                        <p class="text-[13px] text-[#111827] font-medium">
                                            {{ uploadFile ? uploadFile.name : 'Pilih atau seret file ke sini' }}
                                        </p>
                                        <p class="text-[11.5px] text-[#6b7280] mt-1">
                                            {{ uploadFile ? `${(uploadFile.size / 1024 / 1024).toFixed(2)} MB` : 'Mendukung CSV dan Excel (.xlsx) hingga 1 GB' }}
                                        </p>
                                    </div>

                                    <!-- General Error Message -->
                                    <div v-if="generalError" class="p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-[12.5px] flex items-start gap-2">
                                        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <span>{{ generalError }}</span>
                                    </div>

                                    <!-- Row-level errors list -->
                                    <div v-if="localErrors.length > 0" class="border border-red-200 rounded-lg max-h-48 overflow-y-auto wos-scroll bg-red-50/50">
                                        <div class="px-3.5 py-2.5 border-b border-red-200 bg-red-50 flex items-center justify-between sticky top-0">
                                            <span class="text-[12px] font-semibold text-red-800">Terdapat {{ localErrors.length }} baris data yang gagal divalidasi:</span>
                                        </div>
                                        <div class="p-3 space-y-3.5">
                                            <div v-for="err in localErrors" :key="err.row" class="text-[12px] flex flex-col items-start">
                                                <span class="font-bold text-[#111827]">Baris {{ err.row }} ({{ err.email }}):</span>
                                                <ul class="list-disc pl-4 mt-0.5 space-y-0.5 text-[#e11d48]">
                                                    <li v-for="msg in err.errors" :key="msg">{{ msg }}</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Modal Footer -->
                            <div class="px-6 py-4 flex justify-end gap-2 border-t border-[#e5e7eb] rounded-b-xl bg-[#f9fafb]">
                                <button
                                    :disabled="isUploading"
                                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] border border-[#d1d5db] hover:bg-[#f3f4f6] transition-colors bg-white shadow-sm cursor-pointer disabled:opacity-50"
                                    @click="modal.uploadUsers = false; localErrors = []"
                                >
                                    Batal
                                </button>
                                <button
                                    v-if="uploadStep === 2"
                                    :disabled="loading || !uploadFile || isUploading"
                                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-[#6366f1] hover:bg-[#4f46e5] transition-colors shadow-sm disabled:opacity-50 flex items-center gap-2 cursor-pointer"
                                    @click="submitUpload"
                                >
                                    <svg v-if="loading && !isUploading" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span>{{ isUploading ? 'Mengunggah…' : (loading ? 'Memproses…' : 'Mulai Impor') }}</span>
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
