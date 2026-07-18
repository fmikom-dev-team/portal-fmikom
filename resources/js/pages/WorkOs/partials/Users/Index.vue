<script setup lang="ts">
import { router, usePage } from "@inertiajs/vue3";
import { computed, onMounted, onUnmounted, reactive, ref, watch } from "vue";
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
import MotionTabs from "@/components/ui/tabs/MotionTabs.vue";

const props = defineProps<{
	users: Array<any> | Record<string, any>;
	roles?: Array<any>;
	pendingCount: number;
	searchQuery?: string;
}>();

const emit = defineEmits<(e: "openDetail", user: any) => void>();

// ─── SEARCH / FILTER ─────────────────────────────────────────────────────────
const usersArray = computed(() => {
	if (!props.users) return [];
	if (Array.isArray(props.users)) return props.users;
	if (props.users && Array.isArray(props.users.data)) return props.users.data;
	return [];
});

const userSearch = ref("");
const filterStatus = ref("all");

watch(() => props.searchQuery, (newVal) => {
	userSearch.value = newVal || "";
});

const filterRole = ref("all");
const filterAuth = ref("all");
const filterMembership = ref("all");

const subTab = ref<'all' | 'pending_approval' | 'admin_created' | 'active'>('all');

const pendingApprovalCount = computed(() => {
	return usersArray.value.filter((u: any) => {
		return u.registration_type === 'self_registered' && u.status_approval === 'pending';
	}).length;
});

const subTabs = computed(() => [
	{ id: 'all', label: 'Semua' },
	{ id: 'pending_approval', label: 'Butuh Persetujuan', badge: pendingApprovalCount.value },
	{ id: 'admin_created', label: 'Dibuat Admin' },
	{ id: 'active', label: 'Aktif' }
]);

const authDropdownOpen = ref(false);
const membershipDropdownOpen = ref(false);
const activeUserMenuId = ref<any>(null);

function toggleUserMenu(id: any, e: MouseEvent) {
	e.stopPropagation();
	activeUserMenuId.value = activeUserMenuId.value === id ? null : id;
}

const filterAuthLabel = computed(() => {
	if (filterAuth.value === "email") return "Email + Password";
	if (filterAuth.value === "all") return "All";
	return filterAuth.value.charAt(0).toUpperCase() + filterAuth.value.slice(1);
});

const filterRoleLabel = computed(() => {
	if (filterRole.value === "all") return "All";
	const matched = availableRoles.value.find((r) => r.value === filterRole.value);
	return matched ? matched.label : filterRole.value;
});

const filterMembershipLabel = computed(() => {
	if (filterMembership.value === "all") return "All";
	if (filterMembership.value === "active") return "Active";
	if (filterMembership.value === "pending") return "Pending";
	if (filterMembership.value === "rejected") return "Inactive";
	if (filterMembership.value === "deletion_requested") return "Pending Deletion";
	return filterMembership.value;
});

let searchTimeout: any = null;
function applyFilters() {
	router.visit("/workos/users", {
		method: "get",
		data: {
			search: userSearch.value || undefined,
			role: filterRole.value !== "all" ? filterRole.value : undefined,
			auth: filterAuth.value !== "all" ? filterAuth.value : undefined,
			membership: filterMembership.value !== "all" ? filterMembership.value : undefined,
			page: 1,
		},
		preserveState: true,
		preserveScroll: true,
		only: ["users"],
	});
}

watch(userSearch, () => {
	if (searchTimeout) clearTimeout(searchTimeout);
	searchTimeout = setTimeout(() => {
		applyFilters();
	}, 300);
});

watch([filterRole, filterAuth, filterMembership], () => {
	applyFilters();
});
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

	usersArray.value.forEach((u) => {
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
	let r = usersArray.value;

	if (subTab.value !== "all") {
		if (subTab.value === "pending_approval") {
			r = r.filter((u) => u.registration_type === 'self_registered' && u.status_approval === 'pending');
		} else if (subTab.value === "admin_created") {
			r = r.filter((u) => u.registration_type === 'admin_created');
		} else if (subTab.value === "active") {
			r = r.filter((u) => u.status_approval === 'approved' || u.status_approval === 'activated');
		}
	}

	if (filterStatus.value !== "all") {
		if (filterStatus.value === "deletion_requested") {
			r = r.filter((u) => u.deletion_requested_at !== null);
		} else {
			r = r.filter((u) => u.status_approval === filterStatus.value);
		}
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

	return r;
});

const currentPage = ref(1);
const perPage = 50;

watch([subTab, userSearch, filterStatus, filterRole, filterAuth], () => {
	currentPage.value = 1;
});

const paginatedUsers = computed(() => {
	const start = (currentPage.value - 1) * perPage;
	return filteredUsers.value.slice(start, start + perPage);
});

const totalPages = computed(() => Math.ceil(filteredUsers.value.length / perPage));

const paginationLinks = computed(() => {
	const links = [];
	const max = totalPages.value;
	const current = currentPage.value;

	// Previous
	links.push({
		label: "&laquo; Previous",
		active: false,
		disabled: current === 1,
		page: current - 1
	});

	// Page numbers
	for (let i = 1; i <= max; i++) {
		links.push({
			label: i.toString(),
			active: current === i,
			disabled: current === i,
			page: i
		});
	}

	// Next
	links.push({
		label: "Next &raquo;",
		active: false,
		disabled: current === max || max === 0,
		page: current + 1
	});

	return links;
});

function closeAllDropdowns(e: MouseEvent) {
	const target = e.target as HTMLElement;
	if (!target.closest("[data-dropdown]")) {
		statusDropdownOpen.value = false;
		roleDropdownOpen.value = false;
		authDropdownOpen.value = false;
		membershipDropdownOpen.value = false;
		activeUserMenuId.value = null;
	}
}

onMounted(() => {
	document.addEventListener("click", closeAllDropdowns);
	if (typeof window !== "undefined") {
		const urlParams = new URLSearchParams(window.location.search);
		const searchParam = urlParams.get("search");
		if (searchParam) {
			userSearch.value = searchParam;
		}
		const roleParam = urlParams.get("role");
		if (roleParam) {
			filterRole.value = roleParam;
		}
		const authParam = urlParams.get("auth");
		if (authParam) {
			filterAuth.value = authParam;
		}
		const membershipParam = urlParams.get("membership");
		if (membershipParam) {
			filterMembership.value = membershipParam;
		}
	}
});
onUnmounted(() => document.removeEventListener("click", closeAllDropdowns));

// ─── MODALS ───────────────────────────────────────────────────────────────────
const modal = reactive({ createUser: false, uploadUsers: false });

const toggleStatusModal = reactive({
	show: false,
	user: null as any,
	targetStatus: false,
	isLoading: false
});

function openToggleStatus(u: any, event: Event) {
	event.stopPropagation();
	toggleStatusModal.user = u;
	toggleStatusModal.targetStatus = !!u.is_active;
	toggleStatusModal.show = true;
	toggleStatusModal.isLoading = false;
}

function handleToggleStatusSubmit() {
	if (!toggleStatusModal.user) return;
	toggleStatusModal.isLoading = true;

	if (toggleStatusModal.user.status_approval === 'pending') {
		router.post(`/workos/users/${toggleStatusModal.user.id}/approve`, {}, {
			preserveState: true,
			preserveScroll: true,
			onSuccess: () => {
				toggleStatusModal.show = false;
				toast('Pendaftaran user berhasil disetujui.', 'success');
			},
			onError: (errors: any) => {
				toast(errors.error || 'Gagal menyetujui pendaftaran.', 'error');
			},
			onFinish: () => {
				toggleStatusModal.isLoading = false;
			}
		});
		return;
	}

	router.patch(`/workos/users/${toggleStatusModal.user.id}`, {
		is_active: toggleStatusModal.targetStatus
	}, {
		preserveState: true,
		preserveScroll: true,
		onSuccess: () => {
			toggleStatusModal.show = false;
			toast('Status akun user berhasil diperbarui.', 'success');
		},
		onError: (errors: any) => {
			toast(errors.error || 'Gagal memperbarui status user.', 'error');
		},
		onFinish: () => {
			toggleStatusModal.isLoading = false;
		}
	});
}

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

const GOOGLE_SVG = `<svg viewBox="0 0 24 24" class="w-3.5 h-3.5 inline-block" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>`;
const GITHUB_SVG = `<svg viewBox="0 0 24 24" class="w-3.5 h-3.5 inline-block" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>`;
const MICROSOFT_SVG = `<svg viewBox="0 0 24 24" class="w-3.5 h-3.5 inline-block" xmlns="http://www.w3.org/2000/svg"><path d="M11.4 24H0V12.6h11.4V24z" fill="#F25022"/><path d="M24 24H12.6V12.6H24V24z" fill="#00A4EF"/><path d="M11.4 11.4H0V0h11.4v11.4z" fill="#7FBA00"/><path d="M24 11.4H12.6V0H24v11.4z" fill="#FFB900"/></svg>`;
const APPLE_SVG = `<svg viewBox="0 0 24 24" class="w-3.5 h-3.5 inline-block" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12.152 6.896c-.948 0-2.415-1.078-3.96-1.04-2.04.027-3.91 1.183-4.961 3.014-2.117 3.675-.546 9.103 1.519 12.09 1.013 1.454 2.208 3.09 3.792 3.039 1.52-.065 2.09-.987 3.935-.987 1.831 0 2.35.987 3.96.948 1.637-.026 2.676-1.48 3.676-2.948 1.156-1.688 1.636-3.325 1.662-3.415-.039-.013-3.182-1.221-3.22-4.857-.026-3.04 2.48-4.494 2.597-4.559-1.429-2.09-3.623-2.324-4.39-2.376-2-.156-3.675 1.09-4.61 1.09zM15.53 3.83c.843-1.012 1.4-2.427 1.245-3.83-1.207.052-2.662.805-3.532 1.818-.78.896-1.454 2.338-1.273 3.714 1.338.104 2.715-.688 3.559-1.701z"/></svg>`;

import AppModal from "../../components/ui/AppModal.vue";

const confirmModal = reactive({
	show: false,
	title: "",
	description: "",
	message: "",
	confirmText: "Oke",
	confirmBgClass: "bg-[#2563EB] hover:bg-[#1d4ed8]",
	isLoading: false,
	onConfirm: () => {},
});

const rejectModal = reactive({
	show: false,
	user: null as any,
	reason: "",
	isLoading: false,
});

function handleUserAction(action: string, user: any) {
	activeUserMenuId.value = null;
	if (action === "view") {
		emit("openDetail", user);
	} else if (action === "approve") {
		confirmModal.title = "Setujui Pengguna";
		confirmModal.description = "Apakah Anda yakin ingin menyetujui akun ini?";
		confirmModal.message = `Menyetujui pendaftaran user ${user.email} akan langsung membuat tautan aktivasi unik dan mengirimkannya secara otomatis via email ke pengguna.`;
		confirmModal.confirmText = "Ya, Setujui";
		confirmModal.confirmBgClass = "bg-green-600 hover:bg-green-700";
		confirmModal.onConfirm = () => {
			confirmModal.isLoading = true;
			router.post(`/workos/users/${user.id}/approve`, {}, {
				preserveScroll: true,
				onSuccess: () => {
					confirmModal.show = false;
					confirmModal.isLoading = false;
					toast("User approved successfully.", "success");
				},
				onError: () => {
					confirmModal.isLoading = false;
				}
			});
		};
		confirmModal.show = true;
	} else if (action === "reject") {
		rejectModal.user = user;
		rejectModal.reason = "";
		rejectModal.show = true;
	} else if (action === "delete") {
		confirmModal.title = "Hapus Pengguna";
		confirmModal.description = "Apakah Anda yakin ingin menghapus akun ini?";
		confirmModal.message = `Tindakan ini permanen dan tidak dapat dibatalkan. Akun ${user.email} akan dihapus dari sistem.`;
		confirmModal.confirmText = "Ya, Hapus";
		confirmModal.confirmBgClass = "bg-red-600 hover:bg-red-700";
		confirmModal.onConfirm = () => {
			confirmModal.isLoading = true;
			router.delete(`/workos/users/${user.id}`, {
				preserveScroll: true,
				onSuccess: () => {
					confirmModal.show = false;
					confirmModal.isLoading = false;
					toast("User deleted successfully.", "success");
				},
				onError: () => {
					confirmModal.isLoading = false;
				}
			});
		};
		confirmModal.show = true;
	}
}

function executeRejectAction() {
	if (!rejectModal.user) return;
	rejectModal.isLoading = true;
	router.post(`/workos/users/${rejectModal.user.id}/reject`, {
		reason: rejectModal.reason
	}, {
		preserveScroll: true,
		onSuccess: () => {
			rejectModal.show = false;
			rejectModal.isLoading = false;
			toast("User rejected.", "success");
		},
		onError: () => {
			rejectModal.isLoading = false;
		}
	});
}
</script>

<template>
    <div class="w-full px-4 sm:px-8 pt-4 sm:pt-8 pb-12 min-w-0" style="font-family: var(--wos-font)">
        <!-- Page header -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-8">
            <div>
                <h1 class="text-[22px] font-semibold text-[#111827] dark:text-zinc-100 tracking-tight mb-1">Users</h1>
                <p class="text-[13px] text-[#6b7280] dark:text-zinc-400">Manage users and invitations authenticated or invited through AuthKit.</p>
            </div>
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 w-full sm:w-auto">
                <button
                    v-if="userTab === 'users'"
                    class="h-[34px] px-4 border border-[#d1d5db] dark:border-zinc-700 text-[#374151] dark:text-zinc-300 rounded-md text-[13px] font-semibold hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm flex items-center justify-center gap-1.5 cursor-pointer dark:shadow-none flex-1 sm:flex-none"
                    @click="openUploadModal"
                >
                    <svg class="w-4 h-4 text-[#4b5563] dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Upload users
                </button>
                <button
                    class="h-[34px] px-4 bg-[#2563eb] dark:bg-blue-600 text-white rounded-md text-[13px] font-semibold hover:bg-[#1d4ed8] dark:hover:bg-blue-700 transition-colors shadow-sm cursor-pointer border-0 dark:shadow-none flex-1 sm:flex-none text-center justify-center inline-flex items-center"
                    @click="modal.createUser = true"
                >
                    {{ userTab === 'users' ? 'Create user' : 'Invite user' }}
                </button>
            </div>
        </div>


        <!-- Tabs -->
        <MotionTabs
            v-model="userTab"
            variant="underline"
            :tabs="[
                { id: 'users', label: 'Users' },
                { id: 'invitations', label: 'Invitations' }
            ]"
            container-class="mb-5"
        />

        <!-- Sub-tabs for User Category (Self-Registration vs Admin-Driven) -->
        <MotionTabs
            v-if="userTab === 'users'"
            v-model="subTab"
            variant="pill"
            :tabs="subTabs"
            container-class="mb-5"
        />

        <!-- Toolbar -->
        <div v-if="userTab === 'users'" class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
            <div class="flex flex-wrap items-center gap-2">
                <!-- Search -->
                <div class="relative w-full sm:w-auto">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input
                        v-model="userSearch"
                        type="search"
                        placeholder="Search"
                        class="w-full sm:w-[280px] h-[34px] pl-9 pr-3 text-[13px] border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors placeholder:text-[#9ca3af] dark:placeholder:text-zinc-500 text-[#111827] dark:text-zinc-100 shadow-sm bg-white dark:bg-zinc-900 dark:shadow-none"
                    />
                </div>

                <!-- Authentication filter -->
                <div class="relative inline-block text-left" data-dropdown>
                    <button 
                        @click.stop="authDropdownOpen = !authDropdownOpen; roleDropdownOpen = false; membershipDropdownOpen = false"
                        class="flex items-center gap-1.5 h-[34px] px-3 border border-[#d1d5db] dark:border-zinc-700 rounded-md text-[13px] text-[#4b5563] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm font-medium cursor-pointer dark:shadow-none"
                    >
                        <span v-html="GENERIC_KEY_SVG" />
                        <span>{{ filterAuth === 'all' ? '+ Authentication' : 'Authentication: ' + filterAuthLabel }}</span>
                    </button>
                    <div 
                        v-show="authDropdownOpen" 
                        class="absolute left-0 mt-1 w-48 bg-white dark:bg-zinc-900 border border-[#e5e7eb] dark:border-zinc-800 rounded-lg shadow-lg py-1 z-50 dark:shadow-none"
                    >
                        <button 
                            v-for="authOpt in ['all', 'email', 'google', 'github', 'microsoft', 'apple']" 
                            :key="authOpt"
                            @click="filterAuth = authOpt; authDropdownOpen = false"
                            class="w-full flex items-center justify-between px-3.5 py-2 text-[12.5px] text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:bg-zinc-900 dark:hover:bg-zinc-800 text-left transition-colors font-medium cursor-pointer bg-transparent border-0"
                        >
                            <span>{{ authOpt === 'all' ? 'All Authentications' : (authOpt === 'email' ? 'Email + Password' : authOpt.charAt(0).toUpperCase() + authOpt.slice(1)) }}</span>
                            <svg v-if="filterAuth === authOpt" class="w-3 h-3 text-[#2563EB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Roles filter -->
                <div class="relative inline-block text-left" data-dropdown>
                    <button 
                        @click.stop="roleDropdownOpen = !roleDropdownOpen; authDropdownOpen = false; membershipDropdownOpen = false"
                        class="flex items-center gap-1.5 h-[34px] px-3 border border-[#d1d5db] dark:border-zinc-700 rounded-md text-[13px] text-[#4b5563] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm font-medium cursor-pointer dark:shadow-none"
                    >
                        <svg class="w-3.5 h-3.5 text-[#9ca3af] dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.333 0 4 .667 4 2v1H3v-1c0-1.333 2.667-2 4-2z" />
                        </svg>
                        <span>{{ filterRole === 'all' ? 'Roles' : 'Role: ' + filterRoleLabel }}</span>
                    </button>
                    <div 
                        v-show="roleDropdownOpen" 
                        class="absolute left-0 mt-1 w-52 bg-white dark:bg-zinc-900 border border-[#e5e7eb] dark:border-zinc-800 rounded-lg shadow-lg py-1 z-50 max-h-60 overflow-y-auto wos-scroll dark:shadow-none"
                    >
                        <button 
                            @click="filterRole = 'all'; roleDropdownOpen = false"
                            class="w-full flex items-center justify-between px-3.5 py-2 text-[12.5px] text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:bg-zinc-900 dark:hover:bg-zinc-800 text-left transition-colors font-medium cursor-pointer bg-transparent border-0"
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
                            class="w-full flex items-center justify-between px-3.5 py-2 text-[12.5px] text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:bg-zinc-900 dark:hover:bg-zinc-800 text-left transition-colors font-medium cursor-pointer bg-transparent border-0"
                        >
                            <span>{{ role.label }}</span>
                            <svg v-if="filterRole === role.value" class="w-3 h-3 text-[#2563EB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Membership filter -->
                <div class="relative inline-block text-left" data-dropdown>
                    <button 
                        @click.stop="membershipDropdownOpen = !membershipDropdownOpen; authDropdownOpen = false; roleDropdownOpen = false"
                        class="flex items-center gap-1.5 h-[34px] px-3 border border-[#d1d5db] dark:border-zinc-700 rounded-md text-[13px] text-[#4b5563] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm font-medium cursor-pointer dark:shadow-none"
                    >
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>{{ filterMembership === 'all' ? '+ Membership' : 'Membership: ' + filterMembershipLabel }}</span>
                    </button>
                    <div 
                        v-show="membershipDropdownOpen" 
                        class="absolute left-0 mt-1 w-48 bg-white dark:bg-zinc-900 border border-[#e5e7eb] dark:border-zinc-800 rounded-lg shadow-lg py-1 z-50 dark:shadow-none"
                    >
                        <button 
                            v-for="membOpt in ['all', 'active', 'pending', 'rejected', 'deletion_requested']" 
                            :key="membOpt"
                            @click="filterMembership = membOpt; membershipDropdownOpen = false"
                            class="w-full flex items-center justify-between px-3.5 py-2 text-[12.5px] text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:bg-zinc-900 dark:hover:bg-zinc-800 text-left transition-colors font-medium cursor-pointer bg-transparent border-0"
                        >
                            <span>{{ membOpt === 'all' ? 'All Memberships' : (membOpt === 'active' ? 'Active' : (membOpt === 'pending' ? 'Pending' : (membOpt === 'rejected' ? 'Inactive' : 'Pending Deletion'))) }}</span>
                            <svg v-if="filterMembership === membOpt" class="w-3 h-3 text-[#2563EB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Create / Add user button -->
            <div class="hidden md:flex items-center gap-2">
                <button
                    v-if="userTab === 'users'"
                    class="h-[34px] px-4 border border-[#d1d5db] dark:border-zinc-700 text-[#374151] dark:text-zinc-300 rounded-md text-[13px] font-semibold hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm flex items-center gap-1.5 cursor-pointer dark:shadow-none"
                    @click="openUploadModal"
                >
                    <svg class="w-4 h-4 text-[#4b5563] dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Upload users
                </button>
                <button
                    class="h-[34px] px-4 bg-[#2563eb] dark:bg-blue-600 text-white rounded-md text-[13px] font-semibold hover:bg-[#1d4ed8] dark:hover:bg-blue-700 transition-colors shadow-sm cursor-pointer border-0 dark:shadow-none"
                    @click="modal.createUser = true"
                >
                    {{ userTab === 'users' ? 'Create user' : 'Invite user' }}
                </button>
            </div>
        </div>
        
        <!-- Invitations Toolbar -->
        <div v-if="userTab === 'invitations'" class="flex flex-col sm:flex-row sm:items-center gap-2 mb-4">
            <!-- Search -->
            <div class="relative w-full sm:w-auto">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#9ca3af] dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" />
                </svg>
                <input
                    type="search"
                    placeholder="Search"
                    class="w-full sm:w-[280px] h-[34px] pl-8 pr-3 text-[13px] border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors placeholder:text-[#9ca3af] dark:placeholder:text-zinc-500 text-[#111827] dark:text-zinc-100 shadow-sm bg-white dark:bg-zinc-900 dark:shadow-none"
                />
            </div>
        </div>

        <!-- Main Tab Content Transition -->
        <Transition name="fade" mode="out-in">
            <!-- Invitations Empty State -->
            <div v-if="userTab === 'invitations'" key="invitations" class="border border-[#e5e7eb] dark:border-zinc-800 rounded-lg bg-white dark:bg-zinc-900 p-12 flex flex-col items-center justify-center text-center mt-4">
                <div class="w-12 h-12 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-[#9ca3af] dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <h3 class="text-[15px] font-semibold text-[#111827] dark:text-zinc-100 mb-3">No invitations have been created in this environment</h3>
                <button
                    class="h-[34px] px-4 border border-[#d1d5db] dark:border-zinc-700 rounded-md text-[13px] font-semibold text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm cursor-pointer dark:shadow-none"
                    @click="modal.createUser = true"
                >
                    Invite user
                </button>
            </div>


            <!-- Users Section (Table + Pagination) -->
            <div v-else-if="userTab === 'users'" :key="'users_' + userTab" class="space-y-4">
                <!-- Users Table -->
                <div class="border border-[#e5e7eb] dark:border-zinc-800 rounded-lg overflow-x-auto bg-white dark:bg-zinc-900 shadow-sm dark:shadow-none min-h-[400px]">
                    <table class="w-full text-left whitespace-nowrap">
                        <caption class="sr-only">Users</caption>
                        <thead>
                            <tr class="bg-[#f9fafb] dark:bg-zinc-800/40 border-b border-[#e5e7eb] dark:border-zinc-800">
                                <th class="px-4 py-3 text-[12px] font-semibold text-[#111827] dark:text-zinc-200">User</th>
                                <th class="px-4 py-3 text-[12px] font-semibold text-[#111827] dark:text-zinc-200">Authentication</th>
                                <th class="px-4 py-3 text-[12px] font-semibold text-[#111827] dark:text-zinc-200">Roles</th>
                                <th class="px-4 py-3 text-[12px] font-semibold text-[#111827] dark:text-zinc-200">Membership</th>
                                <th class="px-4 py-3 text-[12px] font-semibold text-[#111827] dark:text-zinc-200">Sign-in count</th>
                                <th class="px-4 py-3 text-[12px] font-semibold text-[#111827] dark:text-zinc-200">Last sign-in</th>
                                <th class="px-4 py-3 text-[12px] font-semibold text-[#111827] dark:text-zinc-100 flex items-center gap-1">
                                    Created
                                    <svg class="w-3.5 h-3.5 text-[#111827] dark:text-zinc-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                    </svg>
                                </th>
                                <th class="px-4 py-3 w-12" />
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e5e7eb] dark:divide-zinc-800">
                            <tr v-if="filteredUsers.length === 0" key="empty">
                                <td colspan="8" class="py-12 text-center text-[13px] text-[#6b7280] dark:text-zinc-400">
                                    No users found.
                                </td>
                            </tr>
                            <tr
                                v-for="u in paginatedUsers"
                                :key="u.id"
                                class="hover:bg-[#f9fafb] dark:bg-zinc-900 dark:hover:bg-zinc-800/20 transition-colors cursor-pointer group"
                                @click="emit('openDetail', u)"
                            >
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-[#f3f4f6] dark:bg-zinc-800 border border-[#e5e7eb] dark:border-zinc-700 flex items-center justify-center text-[#111827] dark:text-zinc-200 text-[12px] font-bold shrink-0 overflow-hidden">
                                            <img v-if="u.foto_path" :src="u.foto_path" :alt="u.name" class="w-full h-full object-cover" />
                                            <span v-else>{{ u.name?.charAt(0)?.toUpperCase() }}</span>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <p class="text-[13px] font-medium text-[#111827] dark:text-zinc-200 leading-tight">{{ u.email }}</p>
                                                <span 
                                                    v-if="u.registration_type === 'self_registered'"
                                                    class="px-1.5 py-0.5 text-[9px] font-bold rounded bg-green-50 text-green-700 dark:bg-green-950/30 dark:text-green-400 select-none shrink-0"
                                                >
                                                    Registrasi Mandiri
                                                </span>
                                                <span 
                                                    v-else
                                                    class="px-1.5 py-0.5 text-[9px] font-bold rounded bg-indigo-50 text-indigo-700 dark:bg-indigo-950/30 dark:text-indigo-400 select-none shrink-0"
                                                >
                                                    Dibuat Admin
                                                </span>
                                            </div>
                                            <p class="text-[12px] text-[#6b7280] dark:text-zinc-400 leading-tight mt-0.5">{{ u.name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5 text-[13px] text-gray-700 dark:text-zinc-300 font-medium">
                                        <template v-if="u.oauth_credentials && u.oauth_credentials.length > 0">
                                            <span v-for="oc in u.oauth_credentials" :key="oc.id" class="inline-flex items-center gap-1">
                                                <span v-if="oc.provider_slug === 'google'" v-html="GOOGLE_SVG" />
                                                <span v-else-if="oc.provider_slug === 'github'" v-html="GITHUB_SVG" />
                                                <span v-else-if="oc.provider_slug === 'microsoft'" v-html="MICROSOFT_SVG" />
                                                <span v-else-if="oc.provider_slug === 'apple'" v-html="APPLE_SVG" />
                                                <span v-else v-html="GENERIC_KEY_SVG" />
                                                {{ oc.provider_name || (oc.provider_slug.charAt(0).toUpperCase() + oc.provider_slug.slice(1)) }}
                                            </span>
                                        </template>
                                        <template v-else>
                                            <span v-html="GENERIC_KEY_SVG" />
                                            <span>Email + Password</span>
                                        </template>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-[13px] text-[#111827] dark:text-zinc-200">
                                    <span v-if="u.role" class="px-2 py-0.5 rounded text-[11.5px] font-medium bg-[#f3f4f6] dark:bg-zinc-800 text-gray-800 dark:text-zinc-350">
                                        {{ u.role.nama }}
                                    </span>
                                    <span v-else class="text-gray-400 dark:text-zinc-500">—</span>
                                </td>
                                <td class="px-4 py-3" @click.stop>
                                    <button 
                                        @click="openToggleStatus(u, $event)"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#f9fafb] hover:bg-[#f3f4f6] dark:bg-zinc-800 dark:hover:bg-zinc-700/80 border border-[#e5e7eb] dark:border-zinc-700 hover:border-gray-300 dark:hover:border-zinc-600 transition-all cursor-pointer shadow-sm active:scale-95 text-left"
                                    >
                                        <template v-if="u.is_active || u.status_approval === 'approved'">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse" />
                                            <span class="text-emerald-500">Active</span>
                                        </template>
                                        <template v-else-if="u.status_approval === 'pending'">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse" />
                                            <span class="text-amber-500">Pending</span>
                                        </template>
                                        <template v-else>
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse" />
                                            <span class="text-red-500">Inactive</span>
                                        </template>
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-[13px] text-[#111827] dark:text-zinc-200">
                                    {{ u.sign_in_count || 0 }}
                                </td>
                                <td class="px-4 py-3 text-[13px] text-[#111827] dark:text-zinc-200">
                                    <template v-if="u.last_sign_in_at">
                                        <p class="leading-tight font-medium">{{ u.last_sign_in_at.split(', ')[0] + ', ' + u.last_sign_in_at.split(', ')[1] }}</p>
                                        <p class="text-[12px] text-[#6b7280] dark:text-zinc-400 leading-tight mt-0.5">{{ u.last_sign_in_at.split(', ')[2] }}</p>
                                    </template>
                                    <span v-else class="text-gray-400 dark:text-zinc-500">—</span>
                                </td>
                                <td class="px-4 py-3 text-[13px] text-[#111827] dark:text-zinc-200">
                                    <template v-if="u.created_at">
                                        <p class="leading-tight font-medium">{{ u.created_at.split(', ')[0] + ', ' + u.created_at.split(', ')[1] }}</p>
                                        <p class="text-[12px] text-[#6b7280] dark:text-zinc-400 leading-tight mt-0.5">{{ u.created_at.split(', ')[2] }}</p>
                                    </template>
                                    <span v-else class="text-gray-400 dark:text-zinc-500">—</span>
                                </td>
                                <td class="px-4 py-3 text-right" @click.stop>
                                    <div class="relative inline-block text-left" data-dropdown>
                                        <button 
                                            @click="toggleUserMenu(u.id, $event)"
                                            class="p-1.5 rounded text-gray-400 hover:text-gray-600 dark:text-zinc-400 dark:hover:text-zinc-300 hover:bg-gray-100 dark:bg-zinc-800 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                                            </svg>
                                        </button>
                                        <div 
                                            v-show="activeUserMenuId === u.id" 
                                            class="absolute right-0 mt-1 w-40 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-lg z-50 py-1 dark:shadow-none"
                                        >
                                            <button 
                                                @click="handleUserAction('view', u)"
                                                class="w-full flex items-center gap-2 px-3.5 py-2 text-[12.5px] text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:bg-zinc-900 dark:hover:bg-zinc-800 text-left transition-colors font-medium cursor-pointer bg-transparent border-0"
                                            >
                                                <svg class="w-3.5 h-3.5 text-gray-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                View details
                                            </button>
                                            <template v-if="u.status_approval === 'pending' && u.registration_type === 'self_registered'">
                                                <button 
                                                    @click="handleUserAction('approve', u)"
                                                    class="w-full flex items-center gap-2 px-3.5 py-2 text-[12.5px] text-emerald-600 hover:bg-gray-50 dark:bg-zinc-900 dark:hover:bg-zinc-800 text-left transition-colors font-medium cursor-pointer bg-transparent border-0"
                                                >
                                                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Approve
                                                </button>
                                                <button 
                                                    @click="handleUserAction('reject', u)"
                                                    class="w-full flex items-center gap-2 px-3.5 py-2 text-[12.5px] text-red-600 hover:bg-gray-50 dark:bg-zinc-900 dark:hover:bg-zinc-800 text-left transition-colors font-medium cursor-pointer bg-transparent border-0"
                                                >
                                                    <svg class="w-3.5 h-3.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    Reject
                                                </button>
                                            </template>
                                            <button 
                                                @click="handleUserAction('delete', u)"
                                                class="w-full flex items-center gap-2 px-3.5 py-2 text-[12.5px] text-red-600 hover:bg-gray-50 dark:bg-zinc-900 dark:hover:bg-zinc-800 text-left transition-colors font-medium border-t border-gray-100 dark:border-zinc-800 cursor-pointer bg-transparent border-t-0"
                                            >
                                                <svg class="w-3.5 h-3.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Delete user
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
                <div v-if="filteredUsers.length > perPage" class="mt-4 flex items-center justify-between border-t border-[#e5e7eb] dark:border-zinc-800 pt-4 px-2">
                    <span class="text-[12.5px] text-[#4b5563] dark:text-zinc-400">
                        Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, filteredUsers.length) }} of {{ filteredUsers.length }} entries
                    </span>
                    <div class="flex items-center gap-1.5">
                        <button
                            v-for="link in paginationLinks"
                            :key="link.label"
                            :disabled="link.disabled"
                            @click="currentPage = link.page"
                            v-html="link.label"
                            :class="[
                                'h-[30px] px-3 rounded text-[12.5px] flex items-center justify-center transition-colors border cursor-pointer',
                                link.active 
                                    ? 'bg-[#2563eb] text-white border-[#2563eb] font-semibold' 
                                    : 'bg-white dark:bg-zinc-900 text-[#374151] dark:text-zinc-300 border-[#d1d5db] dark:border-zinc-700 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed bg-transparent'
                            ]"
                        />
                    </div>
                </div>
            </div>
        </Transition>

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
                        <div v-if="modal.createUser" class="bg-white dark:bg-zinc-900 rounded-xl shadow-2xl border border-[#e5e7eb] dark:border-zinc-800 w-full max-w-[480px] dark:shadow-none">
                            <!-- Modal Header -->
                            <div class="px-6 pt-6 pb-4">
                                <h2 class="text-[18px] font-semibold text-[#111827] dark:text-zinc-100 tracking-tight">{{ userTab === 'users' ? 'Create user' : 'Invite user' }}</h2>
                            </div>

                            <!-- Modal Body -->
                            <div class="px-6 pb-6 space-y-4">
                                <!-- Names -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[13px] font-semibold text-[#374151] dark:text-zinc-300 mb-1.5">First name</label>
                                        <input
                                            v-model="createForm.first_name"
                                            type="text"
                                            placeholder="Jane (optional)"
                                            class="w-full h-9 px-3 text-[13px] border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors placeholder:text-[#9ca3af] dark:placeholder:text-zinc-500 text-[#111827] dark:text-zinc-100 bg-white dark:bg-zinc-950"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[13px] font-semibold text-[#374151] dark:text-zinc-300 mb-1.5">Last name</label>
                                        <input
                                            v-model="createForm.last_name"
                                            type="text"
                                            placeholder="Doe (optional)"
                                            class="w-full h-9 px-3 text-[13px] border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors placeholder:text-[#9ca3af] dark:placeholder:text-zinc-500 text-[#111827] dark:text-zinc-100 bg-white dark:bg-zinc-950"
                                        />
                                    </div>
                                </div>
                                
                                <!-- Email -->
                                <div>
                                    <label class="block text-[13px] font-semibold text-[#374151] dark:text-zinc-300 mb-1.5">Email address</label>
                                    <input
                                        v-model="createForm.email"
                                        type="email"
                                        placeholder="jane.doe@example.com"
                                        class="w-full h-9 px-3 text-[13px] border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors placeholder:text-[#9ca3af] dark:placeholder:text-zinc-500 text-[#111827] dark:text-zinc-100 bg-white dark:bg-zinc-950"
                                    />
                                </div>

                                <!-- Password -->
                                <div v-if="userTab === 'users'">
                                    <label class="block text-[13px] font-semibold text-[#374151] dark:text-zinc-300 mb-1.5">Password</label>
                                    <div class="flex gap-2">
                                        <div class="relative flex-1">
                                            <input
                                                v-model="createForm.password"
                                                :type="showPw ? 'text' : 'password'"
                                                placeholder="At least 10 characters"
                                                class="w-full h-9 px-3 pr-9 text-[13px] border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors placeholder:text-[#9ca3af] text-[#111827] dark:text-zinc-100"
                                            />
                                            <button
                                                type="button"
                                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[#9ca3af] dark:text-zinc-500 hover:text-[#4b5563] dark:hover:text-zinc-300 transition-colors bg-transparent border-0 cursor-pointer"
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
                                            class="h-9 px-3 rounded-md text-[13px] font-semibold border border-[#d1d5db] dark:border-zinc-700 text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors whitespace-nowrap bg-white dark:bg-zinc-900 shadow-sm cursor-pointer dark:shadow-none"
                                            @click="genPw"
                                        >
                                            Generate
                                        </button>
                                    </div>
                                </div>

                                <!-- External ID -->
                                <div>
                                    <label class="block text-[13px] font-semibold text-[#374151] dark:text-zinc-300 mb-1.5">External ID</label>
                                    <input
                                        v-model="createForm.nomor_induk"
                                        type="text"
                                        placeholder="User ID"
                                        class="w-full h-9 px-3 text-[13px] border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors placeholder:text-[#9ca3af] dark:placeholder:text-zinc-500 text-[#111827] dark:text-zinc-100 bg-white dark:bg-zinc-950"
                                    />
                                    <p class="text-[12.5px] text-[#6b7280] dark:text-zinc-400 mt-1.5 leading-relaxed">
                                        Optional identifier that can be used to link this user to your system.
                                        <a href="#" class="text-[#2563EB] dark:text-blue-400 hover:underline inline-flex items-center gap-1">
                                            Learn more
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                        </a>
                                    </p>
                                </div>
                            </div>

                            <!-- Modal Footer -->
                            <div class="px-6 py-4 flex justify-end gap-2 border-t border-[#e5e7eb] dark:border-zinc-800 rounded-b-xl bg-[#f9fafb] dark:bg-zinc-800/20">
                                <button
                                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] dark:text-zinc-300 border border-[#d1d5db] dark:border-zinc-700 hover:bg-[#f3f4f6] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm cursor-pointer dark:shadow-none"
                                    @click="modal.createUser = false; resetForm()"
                                >
                                    Cancel
                                </button>
                                <button
                                    :disabled="loading || !createForm.email"
                                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-[#2563eb] dark:bg-blue-600 hover:bg-[#1d4ed8] dark:hover:bg-blue-700 transition-colors shadow-sm disabled:opacity-50 flex items-center gap-2 border-0 cursor-pointer dark:shadow-none"
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
                        <div v-if="modal.uploadUsers" class="bg-white dark:bg-zinc-900 rounded-xl shadow-2xl border border-[#e5e7eb] dark:border-zinc-800 w-full max-w-[560px] dark:shadow-none">
                            <!-- Modal Header -->
                            <div class="px-6 pt-6 pb-4 flex items-center justify-between border-b border-[#e5e7eb] dark:border-zinc-800">
                                <h2 class="text-[16px] font-semibold text-[#111827] dark:text-zinc-100 tracking-tight flex items-center gap-2">
                                    <span v-if="uploadStep === 1">Upload User Data</span>
                                    <span v-else class="flex items-center gap-1.5">
                                        <button :disabled="isUploading" @click="uploadStep = 1; localErrors = []" class="text-[#6b7280] dark:text-zinc-400 hover:text-[#111827] dark:hover:text-zinc-200 transition-colors cursor-pointer disabled:opacity-30 bg-transparent border-0">
                                            <svg class="w-4 h-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                            </svg>
                                        </button>
                                        Upload {{ typeLabel(selectedType) }}
                                    </span>
                                </h2>
                                <button :disabled="isUploading" @click="modal.uploadUsers = false" class="text-[#9ca3af] hover:text-[#4b5563] dark:text-zinc-400 transition-colors cursor-pointer disabled:opacity-30 bg-transparent border-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Modal Body -->
                            <!-- STEP 1: Pilih Tipe User -->
                            <div v-if="uploadStep === 1" class="p-6 space-y-4">
                                <p class="text-[13px] text-[#4b5563] dark:text-zinc-300 mb-4">Pilih jenis data pengguna yang ingin Anda unggah secara massal:</p>
                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Mahasiswa -->
                                    <button 
                                        @click="selectUserType('mahasiswa')"
                                        class="flex flex-col items-start text-left p-5 rounded-xl border border-[#e5e7eb] dark:border-zinc-800 hover:border-[#475569] dark:hover:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-all duration-200 group cursor-pointer shadow-sm bg-transparent dark:shadow-none"
                                    >
                                        <div class="w-9 h-9 rounded-lg bg-[#f8fafc] dark:bg-zinc-800 text-[#475569] dark:text-zinc-400 border border-[#e2e8f0] dark:border-zinc-700 flex items-center justify-center mb-3 group-hover:bg-[#f1f5f9] dark:group-hover:bg-zinc-800 group-hover:text-[#0f172a] dark:group-hover:text-zinc-200 group-hover:border-[#cbd5e1] dark:group-hover:border-zinc-600 transition-colors">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-[14px] font-semibold text-[#111827] dark:text-zinc-100">Mahasiswa</h3>
                                        <p class="text-[12px] text-[#6b7280] dark:text-zinc-400 mt-1">Impor mahasiswa aktif beserta prodi dan NIM.</p>
                                    </button>

                                    <!-- Alumni -->
                                    <button 
                                        @click="selectUserType('alumni')"
                                        class="flex flex-col items-start text-left p-5 rounded-xl border border-[#e5e7eb] dark:border-zinc-800 hover:border-[#475569] dark:hover:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-all duration-200 group cursor-pointer shadow-sm bg-transparent dark:shadow-none"
                                    >
                                        <div class="w-9 h-9 rounded-lg bg-[#f8fafc] dark:bg-zinc-800 text-[#475569] dark:text-zinc-400 border border-[#e2e8f0] dark:border-zinc-700 flex items-center justify-center mb-3 group-hover:bg-[#f1f5f9] dark:group-hover:bg-zinc-800 group-hover:text-[#0f172a] dark:group-hover:text-zinc-200 group-hover:border-[#cbd5e1] dark:group-hover:border-zinc-600 transition-colors">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-[14px] font-semibold text-[#111827] dark:text-zinc-100">Alumni</h3>
                                        <p class="text-[12px] text-[#6b7280] dark:text-zinc-400 mt-1">Impor data alumni lengkap dengan tahun kelulusan.</p>
                                    </button>

                                    <!-- Dosen -->
                                    <button 
                                        @click="selectUserType('dosen')"
                                        class="flex flex-col items-start text-left p-5 rounded-xl border border-[#e5e7eb] dark:border-zinc-800 hover:border-[#475569] dark:hover:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-all duration-200 group cursor-pointer shadow-sm bg-transparent dark:shadow-none"
                                    >
                                        <div class="w-9 h-9 rounded-lg bg-[#f8fafc] dark:bg-zinc-800 text-[#475569] dark:text-zinc-400 border border-[#e2e8f0] dark:border-zinc-700 flex items-center justify-center mb-3 group-hover:bg-[#f1f5f9] dark:group-hover:bg-zinc-800 group-hover:text-[#0f172a] dark:group-hover:text-zinc-200 group-hover:border-[#cbd5e1] dark:group-hover:border-zinc-600 transition-colors">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                        </div>
                                        <h3 class="text-[14px] font-semibold text-[#111827] dark:text-zinc-100">Dosen</h3>
                                        <p class="text-[12px] text-[#6b7280] dark:text-zinc-400 mt-1">Impor dosen / struktural menggunakan NIP / NIDN.</p>
                                    </button>

                                    <!-- Mitra -->
                                    <button 
                                        @click="selectUserType('mitra')"
                                        class="flex flex-col items-start text-left p-5 rounded-xl border border-[#e5e7eb] dark:border-zinc-800 hover:border-[#475569] dark:hover:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-all duration-200 group cursor-pointer shadow-sm bg-transparent dark:shadow-none"
                                    >
                                        <div class="w-9 h-9 rounded-lg bg-[#f8fafc] dark:bg-zinc-800 text-[#475569] dark:text-zinc-400 border border-[#e2e8f0] dark:border-zinc-700 flex items-center justify-center mb-3 group-hover:bg-[#f1f5f9] dark:group-hover:bg-zinc-800 group-hover:text-[#0f172a] dark:group-hover:text-zinc-200 group-hover:border-[#cbd5e1] dark:group-hover:border-zinc-600 transition-colors">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-[14px] font-semibold text-[#111827] dark:text-zinc-100">Mitra</h3>
                                        <p class="text-[12px] text-[#6b7280] dark:text-zinc-400 mt-1">Impor mitra eksternal lengkap dengan nama perusahaan.</p>
                                    </button>
                                </div>
                            </div>

                            <!-- STEP 2: Unduh Template & Unggah File -->
                            <div v-else class="p-6 space-y-5">
                                <!-- Real-time uploading state animation -->
                                <div v-if="isUploading" class="border border-[#e2e8f0] dark:border-zinc-800 rounded-xl p-6 bg-slate-50/50 dark:bg-zinc-800/20 space-y-4 shadow-sm flex flex-col justify-center dark:shadow-none">
                                    <div class="flex items-center justify-between text-[13px]">
                                        <span class="font-medium text-[#0f172a] dark:text-zinc-200 flex items-center gap-2">
                                            <svg class="w-4 h-4 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            Mengunggah berkas...
                                        </span>
                                        <span class="font-bold text-blue-600">{{ Math.round(uploadProgress) }}%</span>
                                    </div>
                                    <Progress :value="uploadProgress" variant="line" className="h-2 bg-slate-100 dark:bg-zinc-800" indicatorClassName="bg-blue-600 rounded-full" />
                                    <p class="text-[11.5px] text-[#6b7280] dark:text-zinc-400">Mohon jangan menutup atau memuat ulang halaman ini sampai proses pengunggahan selesai.</p>
                                </div>

                                <template v-else>
                                    <!-- Bagian Unduh Template -->
                                    <div class="bg-[#f9fafb] dark:bg-zinc-800/40 rounded-xl p-4 border border-[#e5e7eb] dark:border-zinc-800 flex items-center justify-between">
                                        <div>
                                            <h4 class="text-[13px] font-semibold text-[#111827] dark:text-zinc-200">Unduh Template Pengguna</h4>
                                            <p class="text-[11.5px] text-[#6b7280] dark:text-zinc-400 mt-0.5">Format template disesuaikan dengan tipe {{ typeLabel(selectedType) }}.</p>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <a 
                                                :href="getTemplateUrl('csv')" 
                                                download 
                                                class="h-[30px] px-2.5 bg-white dark:bg-zinc-900 border border-[#d1d5db] dark:border-zinc-700 text-[#374151] dark:text-zinc-300 rounded-md text-[12px] font-semibold hover:bg-[#f3f4f6] dark:hover:bg-zinc-800 transition-colors flex items-center gap-1 shadow-sm cursor-pointer dark:shadow-none"
                                            >
                                                .CSV
                                            </a>
                                            <a 
                                                :href="getTemplateUrl('xlsx')" 
                                                download 
                                                class="h-[30px] px-2.5 bg-emerald-600 text-white rounded-md text-[12px] font-semibold hover:bg-emerald-700 transition-colors flex items-center gap-1 shadow-sm cursor-pointer dark:shadow-none"
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
                                            isDragOver ? 'border-[#2563eb] bg-[#2563eb]/5 dark:bg-blue-650/5' : 'border-[#d1d5db] dark:border-zinc-700 hover:border-[#9ca3af] dark:hover:border-zinc-500 bg-white dark:bg-zinc-950'
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
                                        <div class="w-10 h-10 rounded-full bg-[#f3f4f6] dark:bg-zinc-800 flex items-center justify-center mb-3">
                                            <svg class="w-5 h-5 text-[#6b7280] dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                        </div>
                                        <p class="text-[13px] text-[#111827] dark:text-zinc-100 font-medium">
                                            {{ uploadFile ? uploadFile.name : 'Pilih atau seret file ke sini' }}
                                        </p>
                                        <p class="text-[11.5px] text-[#6b7280] dark:text-zinc-400 mt-1">
                                            {{ uploadFile ? `${(uploadFile.size / 1024 / 1024).toFixed(2)} MB` : 'Mendukung CSV dan Excel (.xlsx) hingga 1 GB' }}
                                        </p>
                                    </div>

                                    <!-- General Error Message -->
                                    <div v-if="generalError" class="p-3 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/30 text-red-700 dark:text-red-400 rounded-lg text-[12.5px] flex items-start gap-2">
                                        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <span>{{ generalError }}</span>
                                    </div>

                                    <!-- Row-level errors list -->
                                    <div v-if="localErrors.length > 0" class="border border-red-200 dark:border-zinc-800 rounded-lg max-h-48 overflow-y-auto wos-scroll bg-red-50/50 dark:bg-red-950/20">
                                        <div class="px-3.5 py-2.5 border-b border-red-200 dark:border-zinc-800 bg-red-50 dark:bg-red-950/20 flex items-center justify-between sticky top-0">
                                            <span class="text-[12px] font-semibold text-red-800">Terdapat {{ localErrors.length }} baris data yang gagal divalidasi:</span>
                                        </div>
                                        <div class="p-3 space-y-3.5">
                                            <div v-for="err in localErrors" :key="err.row" class="text-[12px] flex flex-col items-start">
                                                <span class="font-bold text-[#111827] dark:text-zinc-200">Baris {{ err.row }} ({{ err.email }}):</span>
                                                <ul class="list-disc pl-4 mt-0.5 space-y-0.5 text-[#e11d48] dark:text-red-400">
                                                    <li v-for="msg in err.errors" :key="msg">{{ msg }}</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Modal Footer -->
                            <div class="px-6 py-4 flex justify-end gap-2 border-t border-[#e5e7eb] dark:border-zinc-800 rounded-b-xl bg-[#f9fafb] dark:bg-zinc-800/40">
                                <button
                                    :disabled="isUploading"
                                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] dark:text-zinc-300 border border-[#d1d5db] dark:border-zinc-700 hover:bg-[#f3f4f6] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm cursor-pointer disabled:opacity-50 dark:shadow-none"
                                    @click="modal.uploadUsers = false; localErrors = []"
                                >
                                    Batal
                                </button>
                                <button
                                    v-if="uploadStep === 2"
                                    :disabled="loading || !uploadFile || isUploading"
                                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-[#2563eb] dark:bg-blue-600 hover:bg-[#1d4ed8] dark:hover:bg-blue-700 transition-colors shadow-sm disabled:opacity-50 flex items-center gap-2 cursor-pointer border-0 dark:shadow-none"
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

        <!-- Modern AppModal Confirm Dialog -->
        <AppModal :show="confirmModal.show" :title="confirmModal.title" :description="confirmModal.description" @close="confirmModal.show = false">
            <div class="text-[13px] text-[#4b5563] dark:text-zinc-400 font-medium">
                {{ confirmModal.message }}
            </div>
            <template #footer>
                <div class="flex justify-end gap-2">
                    <button class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] dark:text-zinc-350 border border-[#d1d5db] dark:border-zinc-700 hover:bg-[#f3f4f6] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm cursor-pointer dark:shadow-none" @click="confirmModal.show = false">Batal</button>
                    <button
                        :disabled="confirmModal.isLoading"
                        class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white transition-colors shadow-sm disabled:opacity-50 flex items-center gap-2 cursor-pointer border-0 dark:shadow-none"
                        :class="confirmModal.confirmBgClass"
                        @click="confirmModal.onConfirm"
                    >
                        <svg v-if="confirmModal.isLoading" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        {{ confirmModal.isLoading ? 'Memproses...' : confirmModal.confirmText }}
                    </button>
                </div>
            </template>
        </AppModal>

        <!-- Modern AppModal Reject Dialog with Input Reason -->
        <AppModal :show="rejectModal.show" title="Tolak Pendaftaran User" description="Masukkan alasan penolakan untuk dikirimkan melalui email ke pendaftar." @close="rejectModal.show = false">
            <div class="space-y-4">
                <div class="text-[13px] text-[#4b5563] dark:text-zinc-400 font-medium">
                    Apakah Anda yakin ingin menolak akun <span class="font-bold text-slate-805 dark:text-white">{{ rejectModal.user?.email }}</span>?
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">Alasan Penolakan (Opsional)</label>
                    <textarea
                        v-model="rejectModal.reason"
                        placeholder="Tuliskan catatan mengapa pendaftaran ini ditolak..."
                        class="w-full h-24 p-3 rounded-lg border border-[#e5e7eb] dark:border-zinc-800 bg-white dark:bg-zinc-950 text-slate-800 dark:text-zinc-200 focus:outline-none focus:border-red-500 text-xs resize-none"
                    ></textarea>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-2">
                    <button class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] dark:text-zinc-350 border border-[#d1d5db] dark:border-zinc-700 hover:bg-[#f3f4f6] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm cursor-pointer dark:shadow-none" @click="rejectModal.show = false">Batal</button>
                    <button
                        :disabled="rejectModal.isLoading"
                        class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-red-600 hover:bg-red-700 transition-colors shadow-sm disabled:opacity-50 flex items-center gap-2 cursor-pointer border-0 dark:shadow-none"
                        @click="executeRejectAction"
                    >
                        <svg v-if="rejectModal.isLoading" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        {{ rejectModal.isLoading ? 'Memproses...' : 'Tolak' }}
                    </button>
                </div>
            </template>
        </AppModal>

        <!-- Modern AppModal Toggle Status Dialog -->
        <AppModal :show="toggleStatusModal.show" title="Ubah Status Akun User" description="Konfirmasi status aktif atau nonaktif untuk akun user." @close="toggleStatusModal.show = false">
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-zinc-800/40 rounded-xl border border-slate-100 dark:border-zinc-800 gap-4">
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <div class="w-10 h-10 rounded-full bg-[#f3f4f6] dark:bg-zinc-800 border border-[#e5e7eb] dark:border-zinc-700 flex items-center justify-center text-[#111827] dark:text-zinc-200 text-sm font-bold shrink-0 overflow-hidden">
                            <img v-if="toggleStatusModal.user?.foto_path" :src="toggleStatusModal.user.foto_path" :alt="toggleStatusModal.user.name" class="w-full h-full object-cover" />
                            <span v-else>{{ toggleStatusModal.user?.name?.charAt(0)?.toUpperCase() }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[13px] font-semibold text-slate-800 dark:text-zinc-200 leading-tight truncate" :title="toggleStatusModal.user?.email">{{ toggleStatusModal.user?.email }}</p>
                            <p class="text-[11.5px] text-slate-500 dark:text-zinc-400 leading-tight mt-0.5 truncate">{{ toggleStatusModal.user?.name }}</p>
                        </div>
                    </div>
                    
                    <!-- Modern Toggle Switch (Simulated) -->
                    <button 
                        @click="toggleStatusModal.targetStatus = !toggleStatusModal.targetStatus"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-0 ml-4"
                        :class="toggleStatusModal.targetStatus ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-zinc-700'"
                    >
                        <span 
                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                            :class="toggleStatusModal.targetStatus ? 'translate-x-5' : 'translate-x-0'"
                        />
                    </button>
                </div>

                <div class="text-[13px] text-[#4b5563] dark:text-zinc-400 font-medium leading-relaxed">
                    <template v-if="toggleStatusModal.user?.status_approval === 'pending'">
                        Akun user ini sedang menunggu persetujuan (Pending). Menyetujui akun ini akan memicu pengiriman email aktivasi ke user. Apakah Anda yakin ingin menyetujui akun ini?
                    </template>
                    <template v-else-if="toggleStatusModal.targetStatus !== !!toggleStatusModal.user?.is_active">
                        <template v-if="toggleStatusModal.targetStatus">
                            Apakah Anda yakin ingin <span class="font-semibold text-emerald-600 dark:text-emerald-400">mengaktifkan kembali</span> akun user ini? Pengguna akan dapat masuk kembali ke sistem.
                        </template>
                        <template v-else>
                            Apakah Anda yakin ingin <span class="font-semibold text-red-600 dark:text-red-400">menonaktifkan</span> akun user ini? Pengguna tidak akan dapat mengakses sistem sampai diaktifkan kembali.
                        </template>
                    </template>
                    <template v-else>
                        <span v-if="toggleStatusModal.targetStatus">
                            Akun user ini saat ini <span class="font-semibold text-emerald-600 dark:text-emerald-400">Aktif</span>. Geser sakelar ke kiri jika Anda ingin menonaktifkan akun.
                        </span>
                        <span v-else>
                            Akun user ini saat ini <span class="font-semibold text-red-600 dark:text-red-400">Nonaktif</span>. Geser sakelar ke kanan jika Anda ingin mengaktifkan kembali akun.
                        </span>
                    </template>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-2">
                    <button class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] dark:text-zinc-350 border border-[#d1d5db] dark:border-zinc-700 hover:bg-[#f3f4f6] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm cursor-pointer dark:shadow-none" @click="toggleStatusModal.show = false">Batal</button>
                    <button
                        :disabled="toggleStatusModal.isLoading || (toggleStatusModal.user?.status_approval !== 'pending' && toggleStatusModal.targetStatus === !!toggleStatusModal.user?.is_active)"
                        class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 cursor-pointer border-0 dark:shadow-none"
                        :class="[
                            toggleStatusModal.user?.status_approval === 'pending'
                                ? 'bg-emerald-600 hover:bg-emerald-700'
                                : (toggleStatusModal.targetStatus === !!toggleStatusModal.user?.is_active
                                    ? 'bg-slate-400 dark:bg-zinc-700'
                                    : (toggleStatusModal.targetStatus ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-red-600 hover:bg-red-700'))
                        ]"
                        @click="handleToggleStatusSubmit"
                    >
                        <svg v-if="toggleStatusModal.isLoading" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        {{ toggleStatusModal.isLoading ? 'Memproses...' : (toggleStatusModal.user?.status_approval === 'pending' ? 'Setujui' : (toggleStatusModal.targetStatus === !!toggleStatusModal.user?.is_active ? 'Simpan' : (toggleStatusModal.targetStatus ? 'Aktifkan Akun' : 'Nonaktifkan Akun'))) }}
                    </button>
                </div>
            </template>
        </AppModal>
    </div>
</template>

<style scoped>
/* Fade transition for main tab contents */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(4px);
}

/* List transition for table rows */
.list-enter-active,
.list-leave-active {
  transition: all 0.3s ease;
}

.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateY(8px);
}

/* Smooth layout move when list items change */
.list-move {
  transition: transform 0.3s ease;
}
</style>
