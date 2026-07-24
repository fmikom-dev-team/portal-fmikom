<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import axios from "axios";
import { computed, reactive, ref, watch } from "vue";
import AppModal from "../../components/ui/AppModal.vue";
import { formatDate, toast } from "../../composables/useWorkOs";

const props = defineProps<{
	user?: any;
	roles?: any[];
	modules?: any[];
}>();

const emit = defineEmits<(e: "back") => void>();

const groupedModuleRoles = computed(() => {
	if (!props.user?.module_roles) return [];

	const groups = new Map();
	props.user.module_roles.forEach((mr: any) => {
		const code = mr.module_code || "UNKNOWN";
		if (!groups.has(code)) {
			groups.set(code, {
				module_name: mr.module_name,
				module_code: code,
				joined_at: mr.created_at || "May 17, 2026",
				roles: [],
			});
		}
		groups.get(code).roles.push({
			id: mr.id,
			role_name: mr.role_name,
			is_active: mr.is_active,
		});
	});

	return Array.from(groups.values());
});

const activeTab = ref("details");
const isDeleting = ref(false);

const modal = reactive({
	editDetails: false,
	deleteConfirm: false,
	assignModule: false,
	disconnectConfirm: false,
	sessionDetails: false,
	editMetadata: false,
	emailDetails: false,
});

const confirmModal = reactive({
	show: false,
	title: "",
	description: "",
	message: "",
	confirmText: "",
	confirmBgClass: "bg-[#dc2626] hover:bg-[#b91c1c]",
	onConfirm: async () => {},
	isLoading: false,
});

interface ConfirmOptions {
	title: string;
	description?: string;
	message: string;
	confirmText?: string;
	confirmBgClass?: string;
	onConfirm: () => void | Promise<void>;
}

function openConfirm({
	title,
	description,
	message,
	confirmText,
	confirmBgClass,
	onConfirm,
}: ConfirmOptions) {
	confirmModal.title = title;
	confirmModal.description = description || "";
	confirmModal.message = message;
	confirmModal.confirmText = confirmText || "Yes, proceed";
	confirmModal.confirmBgClass =
		confirmBgClass || "bg-[#dc2626] hover:bg-[#b91c1c]";
	confirmModal.onConfirm = async () => {
		confirmModal.isLoading = true;
		try {
			await onConfirm();
			confirmModal.show = false;
		} finally {
			confirmModal.isLoading = false;
		}
	};
	confirmModal.show = true;
}

const editForm = reactive({
	name: "",
	email: "",
	user_type: "",
	is_active: true,
	nomor_induk: "",
	location: "",
	tanggal_lahir: "",
});
const isEditing = ref(false);

const metadataForm = reactive({
	json: "{}",
});
const isEditingMetadata = ref(false);
const metadataError = ref("");

const assignForm = reactive({
	module_id: "",
	role_id: "",
});
const isAssigning = ref(false);
const isModuleSelectLocked = ref(false);

function openAssignModal() {
	assignForm.module_id = "";
	assignForm.role_id = "";
	isModuleSelectLocked.value = false;
	modal.assignModule = true;
}

function submitAssignModule() {
	if (
		isAssigning.value ||
		!props.user ||
		!assignForm.module_id ||
		!assignForm.role_id
	)
		return;
	isAssigning.value = true;

	router.post(
		`/workos/users/${props.user.id}/module-roles`,
		{ ...assignForm },
		{
			preserveScroll: true,
			onSuccess: () => {
				modal.assignModule = false;
				toast("Module role assigned successfully.", "success");
			},
			onError: () =>
				toast(
					"Failed to assign module role. User may already have this role.",
					"error",
				),
			onFinish: () => {
				isAssigning.value = false;
			},
		},
	);
}

function openEditModal() {
	editForm.name = props.user?.name || "";
	editForm.email = props.user?.email || "";
	editForm.user_type = props.user?.user_type || "mahasiswa";
	editForm.is_active = props.user?.is_active ?? true;
	editForm.nomor_induk = props.user?.nomor_induk || "";
	editForm.location = props.user?.location || "";
	editForm.tanggal_lahir = props.user?.tanggal_lahir || "";
	modal.editDetails = true;
}

function submitEditDetails() {
	if (isEditing.value || !props.user) return;
	isEditing.value = true;

	router.patch(
		`/workos/users/${props.user.id}`,
		{ ...editForm },
		{
			preserveScroll: true,
			onSuccess: () => {
				modal.editDetails = false;
				toast("User details updated successfully.", "success");
			},
			onError: () => toast("Failed to update user details.", "error"),
			onFinish: () => {
				isEditing.value = false;
			},
		},
	);
}

function openMetadataModal() {
	metadataForm.json = props.user?.metadata
		? JSON.stringify(props.user.metadata, null, 2)
		: "{\n  \n}";
	metadataError.value = "";
	modal.editMetadata = true;
}

function submitEditMetadata() {
	let parsed = {};
	try {
		parsed = JSON.parse(metadataForm.json);
	} catch (e) {
		metadataError.value = "Invalid JSON format. Please correct it.";
		return;
	}

	if (isEditingMetadata.value || !props.user) return;
	isEditingMetadata.value = true;
	metadataError.value = "";

	router.patch(
		`/workos/users/${props.user.id}`,
		{ metadata: parsed },
		{
			preserveScroll: true,
			onSuccess: () => {
				modal.editMetadata = false;
				toast("User metadata updated successfully.", "success");
			},
			onError: () => toast("Failed to update user metadata.", "error"),
			onFinish: () => {
				isEditingMetadata.value = false;
			},
		},
	);
}

const openDropdownCode = ref<string | null>(null);

function toggleDropdown(code: string) {
	if (openDropdownCode.value === code) {
		openDropdownCode.value = null;
	} else {
		openDropdownCode.value = code;
	}
}

// Window click listener to close dropdowns
if (typeof window !== "undefined") {
	window.addEventListener("click", () => {
		openDropdownCode.value = null;
	});
}

function removeModuleMembership(group: any) {
	openConfirm({
		title: "Remove module membership",
		message: `Are you sure you want to remove this user from ${group.module_code}? This will delete all role assignments for this module.`,
		confirmText: "Yes, remove",
		onConfirm: async () => {
			// Delete all user module roles sequentially
			for (const role of group.roles) {
				await new Promise<void>((resolve, reject) => {
					router.delete(`/workos/users/${role.id}`, {
						preserveScroll: true,
						onSuccess: () => resolve(),
						onError: () => reject(),
					});
				});
			}
			openDropdownCode.value = null;
			toast(`User removed from ${group.module_code} successfully.`, "success");
		},
	});
}

function openEditMembershipRoles(group: any) {
	const matchedModule = props.modules?.find(
		(m) => m.code === group.module_code,
	);
	assignForm.module_id = matchedModule ? matchedModule.id : "";
	assignForm.role_id = "";
	isModuleSelectLocked.value = true;
	modal.assignModule = true;
	openDropdownCode.value = null;
}

function goToOrganization() {
	router.visit("/workos/organizations");
}

function toggleModuleMembership(group: any) {
	const active = group.roles.some((r: any) => r.is_active);
	const actionText = active ? "deactivate" : "activate";
	const newStatus = !active;

	openConfirm({
		title: `${active ? "Deactivate" : "Activate"} module membership`,
		message: `Are you sure you want to ${actionText} this user's membership for ${group.module_code}?`,
		confirmText: active ? "Yes, deactivate" : "Yes, activate",
		onConfirm: async () => {
			for (const role of group.roles) {
				await new Promise<void>((resolve, reject) => {
					router.patch(
						`/workos/users/${role.id}`,
						{ is_active: newStatus },
						{
							preserveScroll: true,
							onSuccess: () => resolve(),
							onError: () => reject(),
						},
					);
				});
			}
			openDropdownCode.value = null;
			toast(
				`User membership ${active ? "deactivated" : "activated"} successfully.`,
				"success",
			);
		},
	});
}

const isEmailSuppressionVisible = ref(true);

function confirmDelete() {
	modal.deleteConfirm = true;
}

function approveDeletion() {
	confirmDelete();
}

function rejectDeletion() {
	if (!props.user) return;
	triggerConfirm({
		title: "Tolak Pengajuan Penghapusan",
		description: "Apakah Anda yakin ingin menolak pengajuan penghapusan akun ini?",
		message: `Tindakan ini akan membatalkan status permohonan hapus akun untuk ${props.user.email} dan mengembalikannya ke aktif.`,
		confirmText: "Ya, Tolak",
		confirmBgClass: "bg-red-600 hover:bg-red-700",
		onConfirm: () => {
			return new Promise<void>((resolve, reject) => {
				router.post(`/workos/users/${props.user?.id}/reject-deletion`, {}, {
					onSuccess: () => {
						toast("Pengajuan penghapusan ditolak.", "success");
						resolve();
					},
					onError: () => {
						toast("Gagal menolak pengajuan.", "error");
						reject();
					},
				});
			});
		}
	});
}

function deleteUser() {
	if (!props.user) return;

	isDeleting.value = true;
	router.delete(`/workos/users/${props.user.id}`, {
		onSuccess: () => {
			modal.deleteConfirm = false;
			toast("User deleted successfully.", "success");
			emit("back");
		},
		onError: () => toast("Failed to delete user.", "error"),
		onFinish: () => {
			isDeleting.value = false;
		},
	});
}

const credentialToDisconnect = ref<any>(null);
const isDisconnecting = ref(false);

function confirmDisconnect(cred: any) {
	credentialToDisconnect.value = cred;
	modal.disconnectConfirm = true;
}

function disconnectCredential() {
	if (!props.user || !credentialToDisconnect.value) return;

	isDisconnecting.value = true;
	router.delete(
		`/workos/users/${props.user.id}/oauth/${credentialToDisconnect.value.id}`,
		{
			preserveScroll: true,
			onSuccess: () => {
				modal.disconnectConfirm = false;
				toast("Account disconnected successfully.", "success");
				credentialToDisconnect.value = null;
			},
			onError: () => toast("Failed to disconnect account.", "error"),
			onFinish: () => {
				isDisconnecting.value = false;
			},
		},
	);
}

const sessions = ref<any[]>([]);
const isSessionsLoading = ref(false);

async function fetchSessions() {
	if (!props.user?.id) return;
	isSessionsLoading.value = true;
	try {
		const response = await axios.get(`/workos/users/${props.user.id}/sessions`);
		sessions.value = response.data.sessions || [];
	} catch (e) {
		console.error("Failed to fetch user sessions", e);
		toast("Failed to load active sessions", "error");
	} finally {
		isSessionsLoading.value = false;
	}
}

async function revokeSession(sessionId: string) {
	if (!props.user?.id) return;
	try {
		await axios.delete(`/workos/users/${props.user.id}/sessions/${sessionId}`);
		toast("Session revoked successfully.", "success");
		await fetchSessions();
	} catch (e) {
		console.error("Failed to revoke session", e);
		toast("Failed to revoke session", "error");
	}
}

function revokeAllSessions() {
	if (!props.user?.id) return;
	openConfirm({
		title: "Revoke all active sessions",
		description: "This will invalidate all active sessions for this user.",
		message:
			"Are you sure you want to revoke all active sessions for this user? They will be logged out of all devices.",
		confirmText: "Yes, revoke all sessions",
		confirmBgClass: "bg-red-600 hover:bg-red-700",
		onConfirm: async () => {
			try {
				await axios.delete(`/workos/users/${props.user.id}/sessions`);
				toast("All active sessions revoked.", "success");
				await fetchSessions();
			} catch (e) {
				console.error("Failed to revoke all sessions", e);
				toast("Failed to revoke all sessions", "error");
			}
		},
	});
}

function clearInactiveSessions() {
	if (!props.user?.id) return;
	openConfirm({
		title: "Clear inactive sessions",
		description: "This will delete all expired and revoked session history.",
		message:
			"Are you sure you want to clear all inactive (expired/revoked) sessions? This action is permanent.",
		confirmText: "Yes, clear sessions",
		confirmBgClass: "bg-red-600 hover:bg-red-700",
		onConfirm: async () => {
			try {
				await axios.delete(`/workos/users/${props.user.id}/sessions/clear`);
				toast("Inactive sessions cleared.", "success");
				await fetchSessions();
			} catch (e) {
				console.error("Failed to clear inactive sessions", e);
				toast("Failed to clear inactive sessions", "error");
			}
		},
	});
}

const hasActiveSessions = computed(() => {
	return sessions.value.some(
		(session: any) => getSessionStatus(session) === "Active",
	);
});

const hasInactiveSessions = computed(() => {
	return sessions.value.some(
		(session: any) => getSessionStatus(session) !== "Active",
	);
});

const emails = ref<any[]>([]);
const isEmailsLoading = ref(false);
const selectedEmail = ref<any>(null);

async function fetchEmails() {
	if (!props.user?.id) return;
	isEmailsLoading.value = true;
	try {
		const response = await axios.get(`/workos/users/${props.user.id}/emails`);
		emails.value = response.data.emails || [];
	} catch (e) {
		console.error("Failed to fetch user emails", e);
		toast("Failed to load email history", "error");
	} finally {
		isEmailsLoading.value = false;
	}
}

function openEmailDetails(email: any) {
	selectedEmail.value = email;
	modal.emailDetails = true;
}

function clearEmailHistory() {
	if (!props.user?.id) return;
	openConfirm({
		title: "Clear email history",
		description: "This will permanently delete all email logs for this user.",
		message:
			"Are you sure you want to clear this user's email history? This action cannot be undone.",
		confirmText: "Yes, clear email history",
		confirmBgClass: "bg-[#dc2626] hover:bg-[#b91c1c]",
		onConfirm: async () => {
			try {
				await axios.delete(`/workos/users/${props.user.id}/emails/clear`);
				toast("Email history cleared successfully.", "success");
				await fetchEmails();
			} catch (e) {
				console.error("Failed to clear email history", e);
				toast("Failed to clear email history", "error");
			}
		},
	});
}

watch(
	[() => props.user?.id, activeTab],
	([newUserId, newTab]) => {
		if (newUserId && newTab === "sessions") {
			fetchSessions();
		}
		if (newUserId && newTab === "emails") {
			fetchEmails();
		}
	},
	{ immediate: true },
);

function parseUserAgent(ua: string): string {
	if (!ua) return "Unknown Device";

	let browser = "Browser";
	if (ua.includes("Chrome") && !ua.includes("Edg")) browser = "Chrome";
	else if (ua.includes("Safari") && !ua.includes("Chrome")) browser = "Safari";
	else if (ua.includes("Firefox")) browser = "Firefox";
	else if (ua.includes("Edg")) browser = "Edge";

	let os = "Device";
	if (ua.includes("Macintosh") || ua.includes("Mac OS X")) os = "macOS";
	else if (ua.includes("Windows")) os = "Windows";
	else if (ua.includes("Linux")) os = "Linux";
	else if (ua.includes("Android")) os = "Android";
	else if (ua.includes("iPhone") || ua.includes("iPad")) os = "iOS";

	return `${browser} on ${os}`;
}

const selectedSession = ref<any>(null);

function openSessionDetails(session: any) {
	selectedSession.value = session;
	modal.sessionDetails = true;
}

function handleRevokeFromModal(sessionId: string) {
	openConfirm({
		title: "Revoke session",
		description: "This will invalidate the user's login session.",
		message:
			"Are you sure you want to revoke this session? The user will be logged out of this device.",
		confirmText: "Yes, revoke session",
		confirmBgClass: "bg-red-600 hover:bg-red-700",
		onConfirm: async () => {
			await revokeSession(sessionId);
			modal.sessionDetails = false;
		},
	});
}

function getSessionStatus(session: any): "Active" | "Expired" | "Revoked" {
	if (session.is_revoked) return "Revoked";
	if (session.expires_at && new Date(session.expires_at) < new Date())
		return "Expired";
	return "Active";
}

function sessionStatusColorDot(session: any) {
	const status = getSessionStatus(session);
	if (status === "Active") return "bg-[#10B981]"; // emerald-500
	return "bg-[#DF5C5F]"; // pink/red
}

function sessionStatusTextColor(session: any) {
	const status = getSessionStatus(session);
	if (status === "Active") return "text-[#10B981]"; // emerald-500
	return "text-[#DF5C5F]"; // pink/red
}

function getSessionOrganization(session: any) {
	return session.organization || "Portal";
}

function getSessionAuthentication(session: any) {
	return session.authentication || "Password";
}

function formatSessionDate(dateStr: string | null | undefined): string {
	if (!dateStr) return "—";
	try {
		const d = new Date(dateStr);
		return (
			d.toLocaleDateString("en-US", {
				month: "short",
				day: "numeric",
				year: "numeric",
			}) +
			", " +
			d.toLocaleTimeString("en-US", {
				hour: "numeric",
				minute: "2-digit",
				hour12: true,
			})
		);
	} catch {
		return dateStr;
	}
}

function getSessionAgentDetails(ua: string) {
	if (!ua) {
		return {
			browser: "Browser",
			os: "Device",
			browserLabel: "Browser",
			osLabel: "Device",
		};
	}
	let browser = "Browser";
	let browserLabel = "Browser";
	if (ua.includes("Chrome") && !ua.includes("Edg")) {
		browser = "Chrome";
		browserLabel = "Chrome";
	} else if (ua.includes("Safari") && !ua.includes("Chrome")) {
		browser = "Safari";
		browserLabel = "Safari";
	} else if (ua.includes("Firefox")) {
		browser = "Firefox";
		browserLabel = "Firefox";
	} else if (ua.includes("Edg")) {
		browser = "Edge";
		browserLabel = "Edge";
	}

	let os = "Device";
	let osLabel = "Device";
	if (ua.includes("Macintosh") || ua.includes("Mac OS X")) {
		os = "macOS";
		osLabel = "macOS";
	} else if (ua.includes("Windows")) {
		os = "Windows";
		osLabel = "Windows";
	} else if (ua.includes("Linux")) {
		os = "Linux";
		osLabel = "Linux";
	} else if (ua.includes("Android")) {
		os = "Android";
		osLabel = "Android";
	} else if (ua.includes("iPhone") || ua.includes("iPad")) {
		os = "iOS";
		osLabel = "iOS";
	}

	return { browser, os, browserLabel, osLabel };
}

function getDeviceType(ua: string): "desktop" | "mobile" {
	if (!ua) return "desktop";
	const lowercaseUa = ua.toLowerCase();
	if (
		lowercaseUa.includes("mobi") ||
		lowercaseUa.includes("iphone") ||
		lowercaseUa.includes("ipod") ||
		lowercaseUa.includes("android") ||
		lowercaseUa.includes("ipad")
	) {
		return "mobile";
	}
	return "desktop";
}

const isMobileDevice = computed(() => {
	const ua = selectedSession.value?.user_agent;
	return getDeviceType(ua) === "mobile";
});

const tabs = [
	{ id: "details", label: "Details" },
	{ id: "sessions", label: "Sessions" },
	{ id: "emails", label: "Emails" },
];
</script>

<template>
    <div class="px-4 sm:px-8 pt-6 pb-12 w-full max-w-[1200px]" style="font-family: var(--wos-font)">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-[13px] mb-5">
            <a href="#" class="text-[#2563EB] hover:underline" @click.prevent="emit('back')">Users</a>
            <span class="text-[#d1d5db] dark:text-zinc-600">/</span>
            <span class="text-[#6b7280] dark:text-zinc-400">User details</span>
        </div>

        <!-- Deletion Request Banner -->
        <div v-if="user?.deletion_requested_at" class="mb-6 rounded-xl border border-amber-200 bg-amber-50/70 p-5 dark:border-amber-900/40 dark:bg-amber-950/10 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex gap-3">
                <div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0">
                    <svg class="w-5 h-5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-amber-900 dark:text-amber-200">Permintaan Penghapusan Akun</h4>
                    <p class="text-[13px] text-amber-700 dark:text-amber-300 mt-0.5 leading-relaxed">
                        Pengguna ini telah mengajukan penghapusan akun pada <span class="font-semibold">{{ formatSessionDate(user.deletion_requested_at) }}</span>. 
                        Silakan tinjau dan setujui atau tolak pengajuan ini.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button 
                    @click="rejectDeletion"
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-gray-700 dark:text-zinc-300 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors shadow-xs"
                >
                    Tolak Pengajuan
                </button>
                <button 
                    @click="approveDeletion"
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-red-650 hover:bg-red-700 transition-colors shadow-xs"
                >
                    Setujui Penghapusan
                </button>
            </div>
        </div>

        <!-- Header -->
        <div class="flex items-start gap-4 mb-6">
            <!-- Avatar -->
            <div class="w-[52px] h-[52px] rounded-full bg-[#f3f4f6] dark:bg-zinc-800 border border-[#e5e7eb] dark:border-zinc-700 flex items-center justify-center text-[18px] font-semibold text-[#111827] dark:text-zinc-100 shrink-0 overflow-hidden shadow-sm dark:shadow-none">
                <img v-if="user?.foto_path" :src="user.foto_path" :alt="user.name" class="w-full h-full object-cover" />
                <span v-else>{{ user?.name ? user.name.charAt(0).toUpperCase() : 'U' }}</span>
            </div>
            
            <div class="flex-1 mt-0.5">
                <h1 class="text-[20px] font-semibold text-[#111827] dark:text-zinc-100 tracking-tight mb-1.5">{{ user?.name || 'User Name' }}</h1>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-mono text-[#4b5563] dark:text-zinc-400 bg-[#f3f4f6] dark:bg-zinc-800">
                        {{ user?.secure_id || ('user_' + (user?.id?.toString().padStart(6, '0') || '000000')) }}
                    </span>
                    <span class="text-[13px] text-[#6b7280] dark:text-zinc-400">
                        {{ user?.email || 'user@example.com' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex items-end border-b border-[#e5e7eb] dark:border-zinc-800 mb-8 overflow-x-auto wos-scroll" role="tablist">
            <button
                v-for="tab in tabs"
                :key="tab.id"
                role="tab"
                :aria-selected="activeTab === tab.id"
                :class="[
                    'flex items-center gap-1.5 px-1 pb-3 mr-6 text-[13px] font-medium border-b-2 -mb-px transition-all duration-150 whitespace-nowrap shrink-0',
                    activeTab === tab.id
                        ? 'border-[#2563EB] text-[#111827] dark:text-zinc-100'
                        : 'border-transparent text-[#6b7280] dark:text-zinc-400 hover:text-[#374151] dark:hover:text-zinc-200 hover:border-[#d1d5db] dark:border-zinc-700 dark:hover:border-zinc-700',
                ]"
                @click="activeTab = tab.id"
            >
                {{ tab.label }}
            </button>
        </div>

        <!-- Tab Content -->
        <div class="w-full">
            <!-- DETAILS TAB -->
            <div v-if="activeTab === 'details'" class="space-y-6">
                <!-- User details card -->
                <div class="bg-white dark:bg-zinc-900 border border-[#e5e7eb] dark:border-zinc-800 rounded-xl overflow-hidden shadow-sm dark:shadow-none">
                    <div class="px-6 py-5 border-b border-[#f3f4f6] dark:border-zinc-800">
                        <h2 class="text-[14px] font-semibold text-[#111827] dark:text-zinc-100">User details</h2>
                    </div>
                    <div class="px-6 py-4 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-start">
                            <div class="w-full sm:w-[180px] shrink-0 text-[13px] text-[#6b7280] dark:text-zinc-400 mb-1 sm:mb-0">Full name</div>
                            <div class="text-[13px] text-[#111827] dark:text-zinc-200">{{ user?.name || '—' }}</div>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-start">
                            <div class="w-full sm:w-[180px] shrink-0 text-[13px] text-[#6b7280] dark:text-zinc-400 mb-1 sm:mb-0">Email address</div>
                            <div class="flex items-center gap-2">
                                <span class="text-[13px] text-[#111827] dark:text-zinc-200 break-all">{{ user?.email || '—' }}</span>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-[#dcfce7] text-[#166534] shrink-0">Verified</span>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-start">
                            <div class="w-full sm:w-[180px] shrink-0 text-[13px] text-[#6b7280] dark:text-zinc-400 mb-1 sm:mb-0">Status</div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full" :class="user?.status_approval === 'approved' ? 'bg-[#10b981]' : (user?.status_approval === 'pending' ? 'bg-[#f59e0b]' : 'bg-[#ef4444]')"></span>
                                <span class="text-[13px] font-medium" :class="user?.status_approval === 'approved' ? 'text-[#10b981]' : (user?.status_approval === 'pending' ? 'text-[#f59e0b]' : 'text-[#ef4444]')">
                                    {{ user?.status_approval ? user.status_approval.charAt(0).toUpperCase() + user.status_approval.slice(1) : 'Unknown' }}
                                </span>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-start">
                            <div class="w-full sm:w-[180px] shrink-0 text-[13px] text-[#6b7280] dark:text-zinc-400 mb-1 sm:mb-0">Created</div>
                            <div class="text-[13px] text-[#111827] dark:text-zinc-200">{{ user?.created_at ? formatDate(user.created_at) : 'Apr 20, 2026, 2:13 AM' }}</div>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-start">
                            <div class="w-full sm:w-[180px] shrink-0 text-[13px] text-[#6b7280] dark:text-zinc-400 mb-1 sm:mb-0">External ID</div>
                            <div class="text-[13px] text-[#111827] dark:text-zinc-200">{{ user?.nomor_induk || 'Not set' }}</div>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-start">
                            <div class="w-full sm:w-[180px] shrink-0 text-[13px] text-[#6b7280] dark:text-zinc-400 mb-1 sm:mb-0">Alamat</div>
                            <div class="text-[13px] text-[#111827] dark:text-zinc-200">{{ user?.location || 'Not set' }}</div>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-start">
                            <div class="w-full sm:w-[180px] shrink-0 text-[13px] text-[#6b7280] dark:text-zinc-400 mb-1 sm:mb-0">Tanggal Lahir</div>
                            <div class="text-[13px] text-[#111827] dark:text-zinc-200">{{ user?.tanggal_lahir || 'Not set' }}</div>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-start">
                            <div class="w-full sm:w-[180px] shrink-0 text-[13px] text-[#6b7280] dark:text-zinc-400 mb-1 sm:mb-0">User type</div>
                            <div class="text-[13px] text-[#111827] dark:text-zinc-200">{{ user?.user_type ? user.user_type.replace('_', ' ').replace(/\b\w/g, (l: string) => l.toUpperCase()) : '—' }}</div>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-start" v-if="user?.foto_path">
                            <div class="w-full sm:w-[180px] shrink-0 text-[13px] text-[#6b7280] dark:text-zinc-400 mb-1 sm:mb-0">Profile picture</div>
                            <div class="text-[13px] text-[#111827] dark:text-zinc-200 truncate"><a :href="user?.foto_path" target="_blank" class="text-[#2563EB] hover:underline break-all">{{ user?.foto_path }}</a></div>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-[#f3f4f6] dark:border-zinc-800 bg-white dark:bg-zinc-900">
                        <button
                            @click="openEditModal"
                            class="h-[34px] px-3.5 rounded-md border border-[#d1d5db] dark:border-zinc-700 text-[#374151] dark:text-zinc-300 text-[13px] font-semibold hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors shadow-sm bg-white dark:bg-zinc-900 cursor-pointer dark:shadow-none"
                        >
                            Edit details
                        </button>
                    </div>
                </div>

                <!-- Custom metadata -->
                <div class="bg-white dark:bg-zinc-900 border border-[#e5e7eb] dark:border-zinc-800 rounded-xl p-6 shadow-sm dark:shadow-none">
                    <h2 class="text-[14px] font-semibold text-[#111827] dark:text-zinc-100 mb-1.5">Custom metadata</h2>
                    <p class="text-[13px] text-[#6b7280] dark:text-zinc-400 mb-4 leading-relaxed">
                        Store additional information about this user as key-value pairs. 
                        <a href="#" class="text-[#2563EB] hover:underline inline-flex items-center gap-1">
                            Learn more
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </p>

                    <div v-if="user?.metadata && Object.keys(user.metadata).length" class="mb-4">
                        <pre class="bg-[#f9fafb] dark:bg-zinc-950 border border-[#e5e7eb] dark:border-zinc-800 rounded-lg p-3 text-[12px] font-mono text-[#374151] dark:text-zinc-300 overflow-x-auto max-h-60 leading-relaxed">{{ JSON.stringify(user.metadata, null, 2) }}</pre>
                    </div>
                    <div v-else class="text-[13px] text-[#6b7280] dark:text-zinc-400 bg-[#f9fafb] dark:bg-zinc-950 border border-dashed border-[#e5e7eb] dark:border-zinc-800 rounded-lg p-4 text-center mb-4">
                        No metadata stored for this user.
                    </div>

                    <button 
                        @click="openMetadataModal"
                        class="h-[34px] px-3.5 rounded-md border border-[#d1d5db] dark:border-zinc-700 text-[#374151] dark:text-zinc-300 text-[13px] font-semibold hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors shadow-sm bg-white dark:bg-zinc-900 cursor-pointer dark:shadow-none"
                    >
                        Edit metadata
                    </button>
                </div>

                <!-- Authentication methods -->
                <div>
                    <h2 class="text-[15px] font-semibold text-[#111827] dark:text-zinc-100 mb-3">Authentication methods</h2>
                    <div class="bg-white dark:bg-zinc-900 border border-[#e5e7eb] dark:border-zinc-800 rounded-xl shadow-sm divide-y divide-[#e5e7eb] dark:divide-zinc-800 dark:shadow-none">
                        <div class="flex items-center justify-between px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded border border-[#e5e7eb] dark:border-zinc-700 bg-[#f9fafb] dark:bg-zinc-800 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-[#6b7280] dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                </div>
                                <span class="text-[13px] font-semibold text-[#111827] dark:text-zinc-100">Email & Password</span>
                            </div>
                            <span class="text-[13px] text-[#6b7280] dark:text-zinc-400">Last sign in <span class="text-[#111827] dark:text-zinc-200">Unknown</span></span>
                        </div>
                        <div v-for="cred in user?.oauth_credentials || []" :key="cred.id" class="flex items-center justify-between px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded border border-[#e5e7eb] dark:border-zinc-700 bg-[#f9fafb] dark:bg-zinc-800 flex items-center justify-center shrink-0">
                                    <svg v-if="cred.provider_slug === 'google'" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12.24 10.285V14.4h6.887c-.648 2.41-2.519 4.114-5.136 4.114-3.555 0-6.445-2.89-6.445-6.445s2.89-6.445 6.445-6.445c1.558 0 2.975.565 4.075 1.5l3.056-3.056C19.26 2.33 15.983 1.2 12.24 1.2 6.132 1.2 1.2 6.132 1.2 12.24s4.932 11.04 11.04 11.04c6.382 0 11.04-4.49 11.04-11.04 0-.745-.065-1.464-.187-2.155H12.24z"/>
                                    </svg>
                                    <svg v-else class="w-4 h-4 text-[#6b7280] dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2C6.477 2 2 6.477 2 12c0 4.42 2.865 8.17 6.839 9.49.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.464-1.11-1.464-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.831.092-.646.35-1.086.636-1.336-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.579.688.481C19.137 20.167 22 16.418 22 12c0-5.523-4.477-10-10-10z"/>
                                    </svg>
                                </div>
                                <span class="text-[13px] font-semibold text-[#111827] dark:text-zinc-100">{{ cred.provider_name }} OAuth</span>
                            </div>
                            <span class="text-[13px] text-[#6b7280] dark:text-zinc-400">Linked email: <span class="text-[#111827] dark:text-zinc-200">{{ cred.email }}</span></span>
                        </div>
                    </div>
                </div>

                <!-- Organization memberships -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-[15px] font-semibold text-[#111827] dark:text-zinc-100">Organization memberships</h2>
                        <button @click="openAssignModal" class="text-[13px] font-semibold text-[#2563EB] hover:underline">
                            Assign module
                        </button>
                    </div>
                    <div class="bg-white dark:bg-zinc-900 border border-[#e5e7eb] dark:border-zinc-800 rounded-xl shadow-sm divide-y divide-[#e5e7eb] dark:divide-zinc-800 dark:shadow-none">
                        <div v-if="!groupedModuleRoles.length" class="px-5 py-4 text-[13px] text-[#6b7280] dark:text-zinc-400 dark:bg-zinc-900">
                            This user is not a member of any organization.
                        </div>
                        <div v-for="group in groupedModuleRoles" :key="group.module_code" class="flex items-center justify-between px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-[#f3f4f6] dark:bg-zinc-800 border border-[#e5e7eb] dark:border-zinc-700 flex items-center justify-center text-[15px] font-bold text-[#111827] dark:text-zinc-100 shrink-0 uppercase shadow-xs">
                                    {{ group.module_code ? group.module_code.substring(0, 2) : 'MO' }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[13.5px] font-semibold text-[#111827] dark:text-zinc-100 leading-tight">{{ group.module_code }}</span>
                                    
                                    <!-- Premium dotted underline with hover black tooltip -->
                                    <div class="relative group/tooltip inline-block mt-0.5">
                                        <span class="text-[11.5px] text-[#6b7280] dark:text-zinc-400 border-b border-dotted border-[#9ca3af] dark:border-zinc-600 cursor-help font-medium hover:text-[#374151] dark:hover:text-zinc-300 transition-colors">
                                            {{ group.roles.length }} {{ group.roles.length > 1 ? 'roles' : 'role' }}
                                        </span>
                                        <div class="absolute bottom-full left-0 mb-1.5 hidden group-hover/tooltip:block bg-[#1f2937] text-white text-[11px] font-semibold rounded-md px-2.5 py-1.5 shadow-xl z-30 whitespace-nowrap leading-none border border-gray-700/50 dark:shadow-none">
                                            <div class="flex items-center gap-1.5">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#10b981]"></span>
                                                {{ group.roles.map(r => r.role_name).join(', ') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3.5">
                                <span class="text-[12.5px] text-[#6b7280] dark:text-zinc-400">Joined <span class="font-medium text-[#4b5563] dark:text-zinc-300">{{ group.joined_at }}</span></span>
                                
                                <!-- Premium ellipses action dropdown -->
                                <div class="relative">
                                    <button 
                                        @click.stop="toggleDropdown(group.module_code)"
                                        class="p-1 rounded-md hover:bg-gray-100 dark:bg-zinc-800 dark:hover:bg-zinc-800 text-gray-400 hover:text-gray-600 dark:text-zinc-400 dark:hover:text-zinc-300 transition-all focus:outline-none border border-transparent active:scale-95 bg-transparent cursor-pointer"
                                    >
                                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                                        </svg>
                                    </button>
                                    <div 
                                        v-if="openDropdownCode === group.module_code" 
                                        class="absolute right-0 mt-1 w-48 bg-white dark:bg-zinc-900 border border-[#e5e7eb] dark:border-zinc-800 rounded-lg shadow-lg py-1.5 z-20 dark:shadow-none"
                                        @click.stop
                                    >
                                        <button 
                                            @click="openEditMembershipRoles(group)"
                                            class="w-full text-left px-4 py-2 text-xs font-semibold text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:bg-zinc-900 dark:hover:bg-zinc-800 transition-colors flex items-center gap-1.5 bg-transparent border-0 cursor-pointer"
                                        >
                                            Edit membership roles
                                        </button>
                                        <button 
                                            @click="goToOrganization"
                                            class="w-full text-left px-4 py-2 text-xs font-semibold text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:bg-zinc-900 dark:hover:bg-zinc-800 transition-colors flex items-center gap-1.5 bg-transparent border-0 cursor-pointer"
                                        >
                                            Go to organization
                                        </button>
                                        <div class="border-t border-[#e5e7eb] dark:border-zinc-800 my-1"></div>
                                        <button 
                                            @click="toggleModuleMembership(group)"
                                            :class="[
                                                'w-full text-left px-4 py-2 text-xs font-semibold transition-colors flex items-center gap-1.5',
                                                group.roles.some((r: any) => r.is_active) ? 'text-red-500 hover:bg-red-50 hover:text-red-600' : 'text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 hover:text-emerald-700'
                                            ]"
                                        >
                                            {{ group.roles.some((r: any) => r.is_active) ? 'Deactivate membership' : 'Activate membership' }}
                                        </button>
                                        <button 
                                            @click="removeModuleMembership(group)"
                                            class="w-full text-left px-4 py-2 text-xs font-semibold text-red-650 hover:bg-red-50 dark:hover:bg-red-950/20 hover:text-red-700 transition-colors flex items-center gap-1.5 bg-transparent border-0 cursor-pointer"
                                        >
                                            Remove membership
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Connected accounts -->
                <div>
                    <h2 class="text-[15px] font-semibold text-[#111827] dark:text-zinc-100 mb-3">Connected accounts</h2>
                    <div v-if="!user?.oauth_credentials || !user.oauth_credentials.length" class="bg-[#f9fafb] dark:bg-zinc-800/30 border border-[#e5e7eb] dark:border-zinc-800 rounded-xl px-5 py-4 shadow-sm text-[13px] text-[#6b7280] dark:text-zinc-400 dark:shadow-none">
                        This user has not connected any accounts.
                    </div>
                    <div v-else class="bg-white dark:bg-zinc-900 border border-[#e5e7eb] dark:border-zinc-800 rounded-xl shadow-sm divide-y divide-[#e5e7eb] dark:divide-zinc-800 dark:shadow-none">
                        <div v-for="cred in user.oauth_credentials" :key="cred.id" class="flex items-center justify-between px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded border border-[#e5e7eb] dark:border-zinc-700 bg-[#f9fafb] dark:bg-zinc-800 flex items-center justify-center shrink-0">
                                    <svg v-if="cred.provider_slug === 'google'" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12.24 10.285V14.4h6.887c-.648 2.41-2.519 4.114-5.136 4.114-3.555 0-6.445-2.89-6.445-6.445s2.89-6.445 6.445-6.445c1.558 0 2.975.565 4.075 1.5l3.056-3.056C19.26 2.33 15.983 1.2 12.24 1.2 6.132 1.2 1.2 6.132 1.2 12.24s4.932 11.04 11.04 11.04c6.382 0 11.04-4.49 11.04-11.04 0-.745-.065-1.464-.187-2.155H12.24z"/>
                                    </svg>
                                    <svg v-else class="w-4 h-4 text-[#6b7280] dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2C6.477 2 2 6.477 2 12c0 4.42 2.865 8.17 6.839 9.49.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.464-1.11-1.464-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.831.092-.646.35-1.086.636-1.336-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.579.688.481C19.137 20.167 22 16.418 22 12c0-5.523-4.477-10-10-10z"/>
                                    </svg>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[13px] font-semibold text-[#111827] dark:text-zinc-100">{{ cred.provider_name }}</span>
                                    <span class="text-[11.5px] text-[#6b7280] dark:text-zinc-400">{{ cred.email }} &bull; ID: {{ cred.external_id }}</span>
                                </div>
                            </div>
                            <button
                                @click="confirmDisconnect(cred)"
                                class="h-7 px-3.5 rounded border border-[#d1d5db] dark:border-zinc-700 text-[#dc2626] dark:text-red-400 hover:bg-[#fef2f2] dark:hover:bg-red-950/20 hover:border-[#fca5a5] dark:hover:border-red-900/30 text-[12px] font-semibold transition-colors bg-white dark:bg-zinc-900 shadow-sm cursor-pointer dark:shadow-none"
                            >
                                Disconnect
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Danger zone -->
                <div>
                    <h2 class="text-[15px] font-semibold text-[#dc2626] mb-3">Danger zone</h2>
                    <div class="bg-white dark:bg-zinc-900 border border-[#e5e7eb] dark:border-zinc-800 rounded-xl overflow-hidden shadow-sm dark:shadow-none">
                        <div class="px-6 py-5">
                            <h3 class="text-[14px] font-semibold text-[#111827] dark:text-zinc-100 mb-1">Delete user</h3>
                            <div class="text-[13px] text-[#6b7280] dark:text-zinc-400">
                                <p v-if="user?.user_type === 'super_admin'" class="text-blue-700 font-semibold flex items-start gap-2 mb-1 bg-blue-50 border border-blue-200 rounded-lg p-3 leading-normal">
                                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <span>Akun Super Admin dilindungi dan tidak dapat dihapus secara langsung. Silakan ubah tipe/role user ini terlebih dahulu ke tipe user lain jika ingin menghapusnya.</span>
                                </p>
                                <p v-else>Deleting this user is permanent and cannot be undone.</p>
                            </div>
                        </div>
                        <div class="px-6 py-4 border-t border-[#f3f4f6] dark:border-zinc-800 bg-[#f9fafb] dark:bg-zinc-800/20">
                            <button
                                @click="confirmDelete"
                                :disabled="user?.user_type === 'super_admin'"
                                class="h-[34px] px-4 rounded-md border border-[#fca5a5] dark:border-red-900/30 text-[#dc2626] dark:text-red-400 text-[13px] font-semibold hover:bg-[#fef2f2] dark:hover:bg-red-950/20 transition-colors bg-white dark:bg-zinc-900 flex items-center gap-2 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:border-gray-250 disabled:text-gray-400"
                            >
                                Delete user
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- EMAILS TAB -->
            <div v-if="activeTab === 'emails'" class="space-y-6">
                <!-- Info Box -->
                <div v-if="isEmailSuppressionVisible" class="bg-[#f9fafb] dark:bg-zinc-800/20 border border-[#e5e7eb] dark:border-zinc-800 rounded-lg p-4 flex items-start gap-3 shadow-sm relative dark:shadow-none">
                    <svg class="w-[18px] h-[18px] text-[#6b7280] dark:text-zinc-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-[13px] text-[#4b5563] dark:text-zinc-300 pr-6">
                        Email suppression management is not available in sandbox environments. Switch to a production environment to manage email suppressions.
                    </div>
                    <button class="absolute top-4 right-4 text-[#9ca3af] dark:text-zinc-500 hover:text-[#4b5563] dark:hover:text-zinc-300 bg-transparent border-0 cursor-pointer" @click="isEmailSuppressionVisible = false">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Action buttons placed neatly above the table card -->
                <div class="flex justify-end">
                    <button 
                        @click="clearEmailHistory"
                        :disabled="emails.length === 0 || isEmailsLoading"
                        class="h-[34px] px-3.5 rounded-md border border-[#d1d5db] dark:border-zinc-700 text-[#374151] dark:text-zinc-300 text-[13px] font-semibold hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors shadow-sm bg-white dark:bg-zinc-900 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer dark:shadow-none"
                    >
                        Clear email history
                    </button>
                </div>

                <!-- Email history -->
                <div class="bg-white dark:bg-zinc-900 border border-[#e5e7eb] dark:border-zinc-800 rounded-xl overflow-hidden shadow-sm dark:shadow-none">
                    <div class="px-6 py-5 border-b border-[#e5e7eb] dark:border-zinc-800">
                        <h2 class="text-[15px] font-semibold text-[#111827] dark:text-zinc-100 mb-1">Email history</h2>
                        <p class="text-[13px] text-[#6b7280] dark:text-zinc-400">A record of emails sent to this user's email address</p>
                    </div>
                    
                    <div v-if="isEmailsLoading" class="p-12 flex items-center justify-center">
                        <svg class="animate-spin h-6 w-6 text-[#2563eb]" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <div v-else-if="emails.length === 0" class="bg-white dark:bg-zinc-900 p-12 flex flex-col items-center justify-center text-center">
                        <div class="w-12 h-12 flex items-center justify-center mb-3">
                            <svg class="w-8 h-8 text-[#9ca3af] dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-[14px] font-semibold text-[#111827] dark:text-zinc-100">No email events found in the last 30 days</h3>
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-left text-[13px] border-collapse whitespace-nowrap">
                            <caption class="sr-only">Email History</caption>
                            <thead>
                                <tr class="bg-gray-50/75 dark:bg-zinc-800/20 border-b border-gray-200/80 dark:border-zinc-800">
                                    <th class="px-6 py-3.5 text-[11px] font-bold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3.5 text-[11px] font-bold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Subject</th>
                                    <th class="px-6 py-3.5 text-[11px] font-bold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Recipient</th>
                                    <th class="px-6 py-3.5 text-[11px] font-bold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Date Sent</th>
                                    <th class="px-6 py-3.5 text-[11px] font-bold text-gray-500 dark:text-zinc-400 uppercase tracking-wider text-right"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-150 dark:divide-zinc-800">
                                <tr 
                                    v-for="email in emails" 
                                    :key="email.id" 
                                    class="hover:bg-gray-50 dark:bg-zinc-900/50 dark:hover:bg-zinc-800/10 transition-colors cursor-pointer group"
                                    @click="openEmailDetails(email)"
                                >
                                    <!-- Status -->
                                    <td class="px-6 py-3.5 align-middle">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span class="font-medium text-emerald-600 dark:text-emerald-400">{{ email.status }}</span>
                                        </div>
                                    </td>
                                    <!-- Subject -->
                                    <td class="px-6 py-3.5 align-middle text-gray-900 dark:text-zinc-100 font-medium">
                                        {{ email.subject }}
                                    </td>
                                    <!-- Recipient -->
                                    <td class="px-6 py-3.5 align-middle text-gray-600 dark:text-zinc-300">
                                        {{ email.email }}
                                    </td>
                                    <!-- Date Sent -->
                                    <td class="px-6 py-3.5 align-middle text-gray-500 dark:text-zinc-400">
                                        {{ formatSessionDate(email.created_at) }}
                                    </td>
                                    <!-- Chevron -->
                                    <td class="px-6 py-3.5 align-middle text-right">
                                        <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-gray-600 dark:text-zinc-400 transition-colors inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- SESSIONS TAB -->
            <div v-if="activeTab === 'sessions'" class="space-y-6">
                <!-- Action buttons placed neatly above the table card -->
                <div class="flex justify-end gap-3">
                    <button 
                        @click="clearInactiveSessions"
                        :disabled="!hasInactiveSessions || isSessionsLoading"
                        class="h-[34px] px-3.5 rounded-md border border-[#d1d5db] dark:border-zinc-700 text-[#374151] dark:text-zinc-300 text-[13px] font-semibold hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors shadow-sm bg-white dark:bg-zinc-900 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer dark:shadow-none"
                    >
                        Clear inactive sessions
                    </button>
                    <button 
                        @click="revokeAllSessions"
                        :disabled="!hasActiveSessions || isSessionsLoading"
                        class="h-[34px] px-3.5 rounded-md border border-[#d1d5db] dark:border-zinc-700 text-[#374151] dark:text-zinc-300 text-[13px] font-semibold hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors shadow-sm bg-white dark:bg-zinc-900 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer dark:shadow-none"
                    >
                        Revoke all active sessions
                    </button>
                </div>

                <div class="bg-white dark:bg-zinc-900 border border-[#e5e7eb] dark:border-zinc-800 rounded-xl overflow-hidden shadow-(--wos-shadow-card)">
                    <div v-if="isSessionsLoading" class="p-12 flex items-center justify-center">
                        <svg class="animate-spin h-6 w-6 text-[#2563eb]" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <div v-else-if="sessions.length === 0" class="bg-white dark:bg-zinc-900 p-12 flex flex-col items-center justify-center text-center">
                        <div class="w-12 h-12 flex items-center justify-center mb-3">
                            <svg class="w-8 h-8 text-[#9ca3af] dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-[14px] font-semibold text-[#111827] dark:text-zinc-100">No active sessions</h3>
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-left text-[13px] border-collapse whitespace-nowrap">
                            <caption class="sr-only">User Sessions</caption>
                            <thead>
                                <tr class="bg-gray-50/75 dark:bg-zinc-800/20 border-b border-gray-200/80 dark:border-zinc-800">
                                    <th class="px-6 py-3.5 text-[11px] font-bold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3.5 text-[11px] font-bold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Issued</th>
                                    <th class="px-6 py-3.5 text-[11px] font-bold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">End date</th>
                                    <th class="px-6 py-3.5 text-[11px] font-bold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Organization</th>
                                    <th class="px-6 py-3.5 text-[11px] font-bold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Authentication</th>
                                    <th class="px-6 py-3.5 text-[11px] font-bold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Application</th>
                                    <th class="px-6 py-3.5 text-[11px] font-bold text-gray-500 dark:text-zinc-400 uppercase tracking-wider text-right"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-150 dark:divide-zinc-800">
                                <tr 
                                    v-for="session in sessions" 
                                    :key="session.id" 
                                    class="hover:bg-gray-50 dark:bg-zinc-900/50 dark:hover:bg-zinc-800/10 transition-colors cursor-pointer group"
                                    @click="openSessionDetails(session)"
                                >
                                    <!-- Status -->
                                    <td class="px-6 py-3.5 align-middle">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full" :class="sessionStatusColorDot(session)"></span>
                                            <span class="font-medium" :class="sessionStatusTextColor(session)">{{ getSessionStatus(session) }}</span>
                                        </div>
                                    </td>
                                    <!-- Issued -->
                                    <td class="px-6 py-3.5 align-middle text-gray-800 dark:text-zinc-200">
                                        {{ formatSessionDate(session.created_at) }}
                                    </td>
                                    <!-- End Date -->
                                    <td class="px-6 py-3.5 align-middle text-gray-800 dark:text-zinc-200">
                                        {{ formatSessionDate(session.expires_at) }}
                                    </td>
                                    <!-- Organization -->
                                    <td class="px-6 py-3.5 align-middle text-gray-800 dark:text-zinc-200 font-medium">
                                        {{ getSessionOrganization(session) }}
                                    </td>
                                    <!-- Authentication -->
                                    <td class="px-6 py-3.5 align-middle text-gray-800 dark:text-zinc-200">
                                        {{ getSessionAuthentication(session) }}
                                    </td>
                                    <!-- Application -->
                                    <td class="px-6 py-3.5 align-middle text-gray-700 dark:text-zinc-200 font-medium">
                                        {{ session.application || 'Portal FMIKOM' }}
                                    </td>
                                    <!-- Chevron -->
                                    <td class="px-6 py-3.5 align-middle text-right">
                                        <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-gray-600 dark:text-zinc-400 transition-colors inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- EVENTS AND API KEYS TABS REMOVED -->
        </div>

        <!-- EDIT DETAILS MODAL -->
        <AppModal :show="modal.editDetails" title="Edit user details" description="Update the user's basic information and access state." @close="modal.editDetails = false">
            <div class="space-y-4">
                <div>
                    <label for="edit_full_name" class="block text-[13px] font-semibold text-[#374151] dark:text-zinc-300 mb-1.5">Full name</label>
                    <input
                        id="edit_full_name"
                        v-model="editForm.name"
                        type="text"
                        class="w-full h-9 px-3 text-[13px] border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors text-[#111827] dark:text-zinc-100 bg-white dark:bg-zinc-950"
                    />
                </div>
                <div>
                    <label for="edit_email" class="block text-[13px] font-semibold text-[#374151] dark:text-zinc-300 mb-1.5">Email address</label>
                    <input
                        id="edit_email"
                        v-model="editForm.email"
                        type="email"
                        class="w-full h-9 px-3 text-[13px] border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors text-[#111827] dark:text-zinc-100 bg-white dark:bg-zinc-950"
                    />
                </div>
                <div>
                    <label for="edit_user_type" class="block text-[13px] font-semibold text-[#374151] dark:text-zinc-300 mb-1.5">User type</label>
                    <select
                        id="edit_user_type"
                        v-model="editForm.user_type"
                        class="w-full h-9 px-3 text-[13px] border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors text-[#111827] dark:text-zinc-100 bg-white dark:bg-zinc-950"
                    >
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="alumni">Alumni</option>
                        <option value="mitra">Mitra Perusahaan</option>
                        <option value="dosen">Dosen</option>
                        <option value="staff">Staff / Admin Struktural</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </div>
                <div>
                    <label for="edit_nomor_induk" class="block text-[13px] font-semibold text-[#374151] dark:text-zinc-300 mb-1.5">External ID</label>
                    <input
                        id="edit_nomor_induk"
                        v-model="editForm.nomor_induk"
                        type="text"
                        placeholder="e.g. NIP/NIM/NIK"
                        class="w-full h-9 px-3 text-[13px] border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors text-[#111827] dark:text-zinc-100 bg-white dark:bg-zinc-950"
                    />
                </div>
                <div>
                    <label for="edit_location" class="block text-[13px] font-semibold text-[#374151] dark:text-zinc-300 mb-1.5">Alamat</label>
                    <input
                        id="edit_location"
                        v-model="editForm.location"
                        type="text"
                        placeholder="e.g. Jl. Raya No. 123"
                        class="w-full h-9 px-3 text-[13px] border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors text-[#111827] dark:text-zinc-100 bg-white dark:bg-zinc-950"
                    />
                </div>
                <div>
                    <label for="edit_tanggal_lahir" class="block text-[13px] font-semibold text-[#374151] dark:text-zinc-300 mb-1.5">Tanggal Lahir</label>
                    <input
                        id="edit_tanggal_lahir"
                        v-model="editForm.tanggal_lahir"
                        type="date"
                        class="w-full h-9 px-3 text-[13px] border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors text-[#111827] dark:text-zinc-100 bg-white dark:bg-zinc-950"
                    />
                </div>
                <div>
                    <span class="block text-[13px] font-semibold text-[#374151] dark:text-zinc-300 mb-1.5">Active status</span>
                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            v-model="editForm.is_active"
                            id="is_active_check"
                            class="w-4 h-4 rounded border-[#d1d5db] dark:border-zinc-700 text-[#2563eb] focus:ring-[#2563eb] dark:bg-zinc-950"
                        />
                        <label for="is_active_check" class="text-[13px] text-[#4b5563] dark:text-zinc-300">Allow user to sign in</label>
                    </div>
                </div>
            </div>
            
            <template #footer>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] dark:text-zinc-300 border border-[#d1d5db] dark:border-zinc-700 hover:bg-[#f3f4f6] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm cursor-pointer dark:shadow-none"
                    @click="modal.editDetails = false"
                >
                    Cancel
                </button>
                <button
                    :disabled="isEditing || !editForm.name || !editForm.email"
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-[#2563eb] dark:bg-blue-600 hover:bg-[#1d4ed8] dark:hover:bg-blue-700 transition-colors shadow-sm disabled:opacity-50 flex items-center gap-2 border-0 cursor-pointer dark:shadow-none"
                    @click="submitEditDetails"
                >
                    <svg v-if="isEditing" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    {{ isEditing ? 'Saving...' : 'Save changes' }}
                </button>
            </template>
        </AppModal>

        <!-- EDIT METADATA MODAL -->
        <AppModal :show="modal.editMetadata" title="Edit custom metadata" description="Store additional information as key-value pairs in a JSON object." @close="modal.editMetadata = false">
            <div class="space-y-4">
                <div>
                    <label for="metadata_editor" class="block text-[13px] font-semibold text-[#374151] dark:text-zinc-300 mb-1.5">JSON Metadata</label>
                    <textarea
                        id="metadata_editor"
                        v-model="metadataForm.json"
                        rows="8"
                        class="w-full p-3 text-[12px] font-mono border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors text-[#111827] dark:text-zinc-100 bg-[#f9fafb] dark:bg-zinc-950"
                        placeholder='{ "key": "value" }'
                    ></textarea>
                    <p v-if="metadataError" class="text-[12px] text-[#ef4444] mt-1">{{ metadataError }}</p>
                </div>
            </div>
            
            <template #footer>
                <button
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] dark:text-zinc-300 border border-[#d1d5db] dark:border-zinc-700 hover:bg-[#f3f4f6] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm cursor-pointer dark:shadow-none"
                    @click="modal.editMetadata = false"
                >
                    Cancel
                </button>
                <button
                    :disabled="isEditingMetadata"
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-[#2563eb] dark:bg-blue-600 hover:bg-[#1d4ed8] dark:hover:bg-blue-700 transition-colors shadow-sm disabled:opacity-50 flex items-center gap-2 border-0 cursor-pointer dark:shadow-none"
                    @click="submitEditMetadata"
                >
                    <svg v-if="isEditingMetadata" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    {{ isEditingMetadata ? 'Saving...' : 'Save metadata' }}
                </button>
            </template>
        </AppModal>

        <!-- ASSIGN MODULE ROLE MODAL -->
        <AppModal :show="modal.assignModule" title="Assign module" description="Add this user to a module with a specific role." @close="modal.assignModule = false">
            <div class="space-y-4">
                <div>
                    <label for="assign_module_id" class="block text-[13px] font-semibold text-[#374151] dark:text-zinc-300 mb-1.5">Module</label>
                    <select
                        id="assign_module_id"
                        v-model="assignForm.module_id"
                        :disabled="isModuleSelectLocked"
                        class="w-full h-9 px-3 text-[13px] border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors text-[#111827] dark:text-zinc-100 bg-white dark:bg-zinc-950 disabled:bg-gray-50 dark:disabled:bg-zinc-900 disabled:text-gray-500 dark:disabled:text-zinc-500 disabled:cursor-not-allowed"
                    >
                        <option value="" disabled>Select a module</option>
                        <option v-for="m in modules || []" :key="m.id" :value="m.id">{{ m.name }} ({{ m.code }})</option>
                    </select>
                </div>
                <div>
                    <label for="assign_role_id" class="block text-[13px] font-semibold text-[#374151] dark:text-zinc-300 mb-1.5">Role</label>
                    <select
                        id="assign_role_id"
                        v-model="assignForm.role_id"
                        class="w-full h-9 px-3 text-[13px] border border-[#d1d5db] dark:border-zinc-700 rounded-md focus:outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] transition-colors text-[#111827] dark:text-zinc-100 bg-white dark:bg-zinc-950"
                    >
                        <option value="" disabled>Select a role</option>
                        <option v-for="r in roles || []" :key="r.id" :value="r.id">{{ r.nama }} ({{ r.slug }})</option>
                    </select>
                </div>
            </div>
            <template #footer>
                <button class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] dark:text-zinc-300 border border-[#d1d5db] dark:border-zinc-700 hover:bg-[#f3f4f6] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm cursor-pointer dark:shadow-none" @click="modal.assignModule = false">Cancel</button>
                <button
                    :disabled="isAssigning || !assignForm.module_id || !assignForm.role_id"
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-[#2563eb] dark:bg-blue-600 hover:bg-[#1d4ed8] dark:hover:bg-blue-700 transition-colors shadow-sm disabled:opacity-50 flex items-center gap-2 border-0 cursor-pointer dark:shadow-none"
                    @click="submitAssignModule"
                >
                    <svg v-if="isAssigning" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    {{ isAssigning ? 'Assigning...' : 'Assign module' }}
                </button>
            </template>
        </AppModal>

        <!-- DELETE CONFIRMATION MODAL -->
        <AppModal :show="modal.deleteConfirm" title="Delete user" description="This action is permanent and cannot be undone." @close="modal.deleteConfirm = false">
            <div class="py-2 text-[13.5px] text-[#4b5563] dark:text-zinc-300">
                Are you sure you want to permanently delete <strong class="font-semibold text-[#111827] dark:text-zinc-100">{{ user?.name }}</strong>? All associated data and access will be removed immediately.
            </div>
            <template #footer>
                <button class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] dark:text-zinc-300 border border-[#d1d5db] dark:border-zinc-700 hover:bg-[#f3f4f6] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm cursor-pointer dark:shadow-none" @click="modal.deleteConfirm = false">Cancel</button>
                <button
                    :disabled="isDeleting"
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-[#dc2626] hover:bg-[#b91c1c] transition-colors shadow-sm disabled:opacity-50 flex items-center gap-2 border-0 cursor-pointer dark:shadow-none"
                    @click="deleteUser"
                >
                    <svg v-if="isDeleting" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    {{ isDeleting ? 'Deleting...' : 'Yes, delete user' }}
                </button>
            </template>
        </AppModal>

        <!-- DISCONNECT OAUTH CONFIRMATION MODAL -->
        <AppModal :show="modal.disconnectConfirm" title="Disconnect account" description="This action will unlink the connected identity provider." @close="modal.disconnectConfirm = false">
            <div class="py-2 text-[13.5px] text-[#4b5563] dark:text-zinc-300">
                Are you sure you want to disconnect the <strong class="font-semibold text-[#111827] dark:text-zinc-100">{{ credentialToDisconnect?.provider_name }}</strong> account linked to <strong class="font-semibold text-[#111827] dark:text-zinc-100">{{ credentialToDisconnect?.email }}</strong>? This user will no longer be able to log in using this OAuth provider.
            </div>
            <template #footer>
                <button class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] dark:text-zinc-300 border border-[#d1d5db] dark:border-zinc-700 hover:bg-[#f3f4f6] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm cursor-pointer dark:shadow-none" @click="modal.disconnectConfirm = false">Cancel</button>
                <button
                    :disabled="isDisconnecting"
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white bg-[#dc2626] hover:bg-[#b91c1c] transition-colors shadow-sm disabled:opacity-50 flex items-center gap-2 border-0 cursor-pointer dark:shadow-none"
                    @click="disconnectCredential"
                >
                    <svg v-if="isDisconnecting" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    {{ isDisconnecting ? 'Disconnecting...' : 'Yes, disconnect' }}
                </button>
            </template>
        </AppModal>

        <!-- GENERIC CONFIRMATION MODAL -->
        <AppModal :show="confirmModal.show" :title="confirmModal.title" :description="confirmModal.description" zIndexClass="z-[70]" @close="confirmModal.show = false">
            <div class="py-2 text-[13.5px] text-[#4b5563] dark:text-zinc-300">
                {{ confirmModal.message }}
            </div>
            <template #footer>
                <button class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] dark:text-zinc-300 border border-[#d1d5db] dark:border-zinc-700 hover:bg-[#f3f4f6] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm cursor-pointer dark:shadow-none" @click="confirmModal.show = false">Cancel</button>
                <button
                    :disabled="confirmModal.isLoading"
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white transition-colors shadow-sm disabled:opacity-50 flex items-center gap-2 dark:shadow-none"
                    :class="confirmModal.confirmBgClass"
                    @click="confirmModal.onConfirm"
                >
                    <svg v-if="confirmModal.isLoading" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    {{ confirmModal.isLoading ? 'Processing...' : confirmModal.confirmText }}
                </button>
            </template>
        </AppModal>

        <!-- USER SESSION DETAILS MODAL (Custom styled to match WorkOS Native layout exactly) -->
        <Teleport to="body">
            <Transition
                enter-from-class="opacity-0"
                enter-active-class="transition-opacity duration-200"
                leave-to-class="opacity-0"
                leave-active-class="transition-opacity duration-150"
            >
                <div v-if="modal.sessionDetails" class="fixed inset-0 z-60 flex items-center justify-center bg-transparent border-0 w-screen h-screen max-w-none max-h-none p-0">
                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-black/40 backdrop-blur-[1px]" aria-hidden="true" @click="modal.sessionDetails = false" />

                    <!-- Modal Panel -->
                    <Transition
                        enter-from-class="opacity-0 scale-95 translate-y-4"
                        enter-active-class="transition-all duration-300 ease-out"
                        leave-to-class="opacity-0 scale-95 translate-y-4"
                        leave-active-class="transition-all duration-200 ease-in"
                    >
                        <div class="relative bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-gray-100 dark:border-zinc-800 w-full max-w-[570px] mx-4 overflow-hidden p-8 dark:shadow-none" style="font-family: var(--wos-font)">
                            <!-- Header -->
                            <div class="mb-6">
                                <h2 class="text-[16px] font-bold text-gray-900 dark:text-zinc-100">User session</h2>
                            </div>

                            <!-- Body Content -->
                            <div v-if="selectedSession" class="space-y-6">
                                <!-- Top Section: Left (Info) & Right (Device Mockup) -->
                                <div class="flex flex-col md:flex-row gap-8 justify-between items-start">
                                    <!-- Left: Session Info -->
                                    <div class="flex-1 min-w-0 space-y-4">
                                        <div class="flex items-start text-[13px]">
                                            <span class="w-[100px] shrink-0 text-gray-400 dark:text-zinc-500 font-normal">Status</span>
                                            <div class="flex items-center gap-1.5">
                                                <span class="w-1.5 h-1.5 rounded-full" :class="sessionStatusColorDot(selectedSession) === 'bg-[#10B981]' ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                                <span class="font-normal" :class="sessionStatusTextColor(selectedSession) === 'text-[#10B981]' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500'">
                                                    {{ getSessionStatus(selectedSession) }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="flex items-start text-[13px]">
                                            <span class="w-[100px] shrink-0 text-gray-400 dark:text-zinc-500 font-normal">Issued</span>
                                            <span class="text-gray-700 dark:text-zinc-300 font-normal">{{ formatSessionDate(selectedSession.created_at) }}</span>
                                        </div>

                                        <div class="flex items-start text-[13px]">
                                            <span class="w-[100px] shrink-0 text-gray-400 dark:text-zinc-500 font-normal">
                                                {{ getSessionStatus(selectedSession) === 'Active' ? 'Expires' : 'Expired' }}
                                            </span>
                                            <span class="text-gray-700 dark:text-zinc-300 font-normal">{{ formatSessionDate(selectedSession.expires_at) }}</span>
                                        </div>

                                        <div class="flex items-start text-[13px]">
                                            <span class="w-[100px] shrink-0 text-gray-400 dark:text-zinc-500 font-normal">IP address</span>
                                            <span class="text-gray-700 dark:text-zinc-300 font-normal select-all break-all">{{ selectedSession.ip_address }}</span>
                                        </div>

                                        <div class="flex items-start text-[13px]">
                                            <span class="w-[100px] shrink-0 text-gray-400 dark:text-zinc-500 font-normal">User agent</span>
                                            <span class="text-gray-500 dark:text-zinc-400 leading-relaxed select-all wrap-break-word text-[12px] max-w-[270px] font-normal">
                                                {{ selectedSession.user_agent }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Right: Device Graphic Mockup (No background container box, matches screenshot) -->
                                    <div class="shrink-0 flex flex-col items-center justify-center pt-2 w-[160px]">
                                        <!-- Laptop Device Mockup -->
                                        <div v-if="!isMobileDevice" class="relative w-[140px] h-[96px] mb-2 shrink-0">
                                            <!-- Laptop SVG Mockup -->
                                            <svg width="140" height="96" viewBox="0 0 140 96" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full overflow-visible">
                                                <defs>
                                                    <!-- Soft realistic radial drop shadow -->
                                                    <radialGradient id="laptop-shadow" cx="50%" cy="50%" r="50%">
                                                        <stop offset="0%" stop-color="#000000" stop-opacity="0.25" />
                                                        <stop offset="60%" stop-color="#000000" stop-opacity="0.08" />
                                                        <stop offset="100%" stop-color="#000000" stop-opacity="0" />
                                                    </radialGradient>
                                                    <!-- Screen glare gradient -->
                                                    <linearGradient id="screen-glare" x1="0%" y1="0%" x2="100%" y2="100%">
                                                        <stop offset="0%" stop-color="#ffffff" stop-opacity="0.06" />
                                                        <stop offset="35%" stop-color="#ffffff" stop-opacity="0.01" />
                                                        <stop offset="36%" stop-color="#ffffff" stop-opacity="0" />
                                                        <stop offset="100%" stop-color="#ffffff" stop-opacity="0" />
                                                    </linearGradient>
                                                </defs>
                                                <!-- Soft shadow under Macbook base (floating drop shadow) -->
                                                <ellipse cx="70" cy="85" rx="55" ry="3" fill="url(#laptop-shadow)" />
                                                
                                                <!-- Laptop Screen Lid/Frame Bezel (Solid dark frame) -->
                                                <rect x="15" y="8" width="110" height="70" rx="3.5" fill="#18181B" stroke="#09090B" stroke-width="0.5" />
                                                
                                                <!-- Screen Content Area (Inner display - Lighter dark gray for high contrast) -->
                                                <rect x="17.5" y="10.5" width="105" height="65" rx="1.5" fill="#3F3F46" />
                                                
                                                <!-- OS Logo inside screen -->
                                                <g transform="translate(63, 36) scale(0.6)">
                                                    <!-- Apple Logo (for macOS/iOS) -->
                                                    <path v-if="getSessionAgentDetails(selectedSession.user_agent).os === 'macOS' || getSessionAgentDetails(selectedSession.user_agent).os === 'iOS'" 
                                                          d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 4.17c.66-.81 1.11-1.93.99-3.06-1 .04-2.21.67-2.93 1.49-.62.69-1.16 1.84-1.01 2.96 1.12.09 2.27-.58 2.95-1.39z" 
                                                          fill="#F4F4F5" opacity="0.95" />
                                                    
                                                    <!-- Windows Logo -->
                                                    <path v-else-if="getSessionAgentDetails(selectedSession.user_agent).os === 'Windows'" 
                                                          d="M3 5.545l7.5-1.091v7.091h-7.5zm8.25-1.2l9.75-1.455v8.655h-9.75zm-8.25 8.905h7.5v7.091l-7.5-1.091zm8.25 0h9.75v8.655l-9.75-1.455z" 
                                                          fill="#F4F4F5" opacity="0.95" />
                                                    
                                                    <!-- Generic monitor/globe for Linux/Other -->
                                                    <path v-else 
                                                          d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" 
                                                          fill="#F4F4F5" opacity="0.95" />
                                                </g>
                                                
                                                <!-- Screen Glare Overlay effect -->
                                                <rect x="17.5" y="10.5" width="105" height="65" rx="1.5" fill="url(#screen-glare)" pointer-events="none" />
                                                
                                                <!-- Hinge Connection -->
                                                <rect x="45" y="76" width="50" height="2" fill="#111214" />
                                                
                                                <!-- Keyboard/Laptop Base (Space Gray metallic finish - Thin & Sleek) -->
                                                <rect x="2" y="78" width="136" height="3" rx="1.5" fill="#27272A" />
                                                <rect x="4" y="81" width="132" height="1.5" rx="0.75" fill="#09090B" />
                                                
                                                <!-- Display Hinge Cutout / Notch in center -->
                                                <rect x="62" y="78" width="16" height="1" rx="0.5" fill="#18181B" />
                                            </svg>
                                            <!-- Overlay Browser Badge (Aligned nicely overlapping screen corner) -->
                                            <div class="absolute bottom-[8px] right-[4px] z-10 w-7 h-7 flex items-center justify-center bg-white dark:bg-zinc-900 rounded-full p-[3px] shadow-[0_2px_5px_rgba(0,0,0,0.15)] border border-white">
                                                <component :is="selectedSession ? 'div' : 'div'" class="w-full h-full flex items-center justify-center">
                                                    <template v-if="getSessionAgentDetails(selectedSession.user_agent).browser === 'Chrome'">
                                                        <svg class="w-full h-full" viewBox="0 0 24 24" fill="none">
                                                            <path fill="#EA4335" d="M12 2C6.48 2 2 6.48 2 12c0 .97.14 1.9.4 2.78l4.4-7.62A5.98 5.98 0 0112 6c.9 0 1.76.2 2.53.56l3.58-6.2A9.95 9.95 0 0012 2z"/>
                                                            <path fill="#FBBC05" d="M21.6 9.22A9.96 9.96 0 0012 2c.9 0 1.76.2 2.53.56L11 8.5v7h7.24a5.98 5.98 0 01-1.74 3.78l5.1-8.82c0-.08.01-.16 0-.24z"/>
                                                            <path fill="#34A853" d="M12 22a9.96 9.96 0 009.6-12.78l-5.1 8.82c-.89.65-1.97 1.04-3.14 1.04-.6 0-1.18-.1-1.72-.28l-3.58 6.2C9.04 21.86 10.49 22 12 22z"/>
                                                            <path fill="#4285F4" d="M6.4 14.78a5.98 5.98 0 01-.4-2.78c0-1.63.65-3.1 1.7-4.18l-4.4-7.62A9.96 9.96 0 002 12c0 4.14 2.52 7.7 6.13 9.22l3.58-6.2c-2.32-.47-4.22-2.12-5.31-4.24z"/>
                                                            <circle cx="12" cy="12" r="3.5" fill="#FFFFFF"/>
                                                            <circle cx="12" cy="12" r="2.5" fill="#4285F4"/>
                                                        </svg>
                                                    </template>
                                                    <template v-else-if="getSessionAgentDetails(selectedSession.user_agent).browser === 'Safari'">
                                                        <svg class="w-full h-full" viewBox="0 0 24 24" fill="none">
                                                            <circle cx="12" cy="12" r="10" fill="#0EA5E9"/>
                                                            <circle cx="12" cy="12" r="8" stroke="white" stroke-width="0.75" stroke-dasharray="1 1"/>
                                                            <path d="M14.5 9.5l-5 5 1.5-4 5-5-1.5 4z" fill="#EF4444"/>
                                                            <path d="M9.5 14.5l5-5-1.5 4-5 5 1.5-4z" fill="#E2E8F0"/>
                                                            <circle cx="12" cy="12" r="1" fill="white"/>
                                                        </svg>
                                                    </template>
                                                    <template v-else-if="getSessionAgentDetails(selectedSession.user_agent).browser === 'Firefox'">
                                                        <svg class="w-full h-full" viewBox="0 0 24 24" fill="none">
                                                            <circle cx="12" cy="12" r="10" fill="#E0f2fe"/>
                                                            <path d="M12 2a10 10 0 00-6.9 17.2c.4-.4.9-.7 1.3-1 .2-.1.4-.4.4-.7v-1.4c0-.6.3-1.1.8-1.4l2.1-1.3c.3-.2.5-.5.5-.9v-.8c0-.4-.2-.8-.5-1L8.5 9.8c-.3-.2-.5-.6-.5-1V8c0-.6.4-1.1 1-1.2.6-.1 1.2.1 1.5.6l.8 1.2c.3.5.9.8 1.5.8h.4c.5 0 .9-.3 1.1-.7l1.1-2.2c.3-.6.9-1 1.6-1H16c1.1 0 2 .9 2 2v.8c0 .5-.2 1-.6 1.3l-2.4 1.8c-.4.3-.6.8-.6 1.3v1.6c0 .4.2.8.5 1.1l2.4 2.4c.4.4.6.9.6 1.5v.4A10 10 0 0012 2z" fill="#F97316"/>
                                                        </svg>
                                                    </template>
                                                    <template v-else-if="getSessionAgentDetails(selectedSession.user_agent).browser === 'Edge'">
                                                        <svg class="w-full h-full" viewBox="0 0 24 24" fill="none">
                                                            <circle cx="12" cy="12" r="10" fill="#f0fdf4"/>
                                                            <path d="M12 4c-3.8 0-7 2.8-7.7 6.5.6-.4 1.3-.5 2-.5 1.9 0 3.5 1.3 3.9 3.1.2.9.1 1.9-.3 2.7-.4.8-1.1 1.4-2 1.7c1.2.9 2.7 1.5 4.3 1.5 3.9 0 7-3.1 7-7s-3.1-7-7-7z" fill="#10B981"/>
                                                        </svg>
                                                    </template>
                                                    <template v-else>
                                                        <svg class="w-full h-full text-gray-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <circle cx="12" cy="12" r="10"/>
                                                            <line x1="2" y1="12" x2="22" y2="12"/>
                                                            <path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>
                                                        </svg>
                                                    </template>
                                                </component>
                                            </div>
                                        </div>

                                        <!-- Mobile Device Mockup -->
                                        <div v-else class="relative w-[64px] h-[96px] mb-2 shrink-0">
                                            <!-- Mobile SVG Mockup -->
                                            <svg width="64" height="96" viewBox="0 0 64 96" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full overflow-visible">
                                                <defs>
                                                    <!-- Soft realistic radial drop shadow -->
                                                    <radialGradient id="phone-shadow" cx="50%" cy="50%" r="50%">
                                                        <stop offset="0%" stop-color="#000000" stop-opacity="0.25" />
                                                        <stop offset="60%" stop-color="#000000" stop-opacity="0.08" />
                                                        <stop offset="100%" stop-color="#000000" stop-opacity="0" />
                                                    </radialGradient>
                                                    <!-- Phone screen glare gradient -->
                                                    <linearGradient id="phone-glare" x1="0%" y1="0%" x2="100%" y2="100%">
                                                        <stop offset="0%" stop-color="#ffffff" stop-opacity="0.06" />
                                                        <stop offset="40%" stop-color="#ffffff" stop-opacity="0.01" />
                                                        <stop offset="41%" stop-color="#ffffff" stop-opacity="0" />
                                                        <stop offset="100%" stop-color="#ffffff" stop-opacity="0" />
                                                    </linearGradient>
                                                </defs>
                                                <!-- Soft shadow under phone base -->
                                                <ellipse cx="32" cy="88" rx="22" ry="3" fill="url(#phone-shadow)" />
                                                
                                                <!-- Phone Body Outer Frame (Matte black edge) -->
                                                <rect x="14" y="6" width="36" height="76" rx="7" fill="#18181B" stroke="#09090B" stroke-width="0.5" />
                                                
                                                <!-- Screen Content Area (Inner display - Lighter dark gray for high contrast) -->
                                                <rect x="16.5" y="8.5" width="31" height="71" rx="5" fill="#3F3F46" />
                                                
                                                <!-- Screen Glare Overlay effect -->
                                                <rect x="16.5" y="8.5" width="31" height="71" rx="5" fill="url(#phone-glare)" pointer-events="none" />
                                                
                                                <!-- Dynamic Island / Notch -->
                                                <rect x="24" y="11.5" width="16" height="2.5" rx="1.25" fill="#000" />
                                                
                                                <!-- Apple/Android OS logo inside screen -->
                                                <g transform="translate(25, 38) scale(0.55)">
                                                    <!-- Apple Logo (for macOS/iOS) -->
                                                    <path v-if="getSessionAgentDetails(selectedSession.user_agent).os === 'macOS' || getSessionAgentDetails(selectedSession.user_agent).os === 'iOS'" 
                                                          d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 4.17c.66-.81 1.11-1.93.99-3.06-1 .04-2.21.67-2.93 1.49-.62.69-1.16 1.84-1.01 2.96 1.12.09 2.27-.58 2.95-1.39z" 
                                                          fill="#F4F4F5" opacity="0.95" />
                                                    
                                                    <!-- Android Logo -->
                                                    <path v-else-if="getSessionAgentDetails(selectedSession.user_agent).os === 'Android'" 
                                                          d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" 
                                                          fill="#F4F4F5" opacity="0.95" />
                                                    
                                                    <!-- Generic globe -->
                                                    <path v-else 
                                                          d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" 
                                                          fill="#F4F4F5" opacity="0.95" />
                                                </g>
                                                
                                                <!-- Screen Line/Home indicator -->
                                                <rect x="25" y="75" width="14" height="1" rx="0.5" fill="#111214" opacity="0.5" />
                                            </svg>
                                            <!-- Overlay Browser Badge (Aligned nicely overlapping screen corner) -->
                                            <div class="absolute bottom-[8px] right-[6px] z-10 w-7 h-7 flex items-center justify-center bg-white dark:bg-zinc-900 rounded-full p-[3px] shadow-[0_2px_5px_rgba(0,0,0,0.15)] border border-white">
                                                <component :is="selectedSession ? 'div' : 'div'" class="w-full h-full flex items-center justify-center">
                                                    <template v-if="getSessionAgentDetails(selectedSession.user_agent).browser === 'Chrome'">
                                                        <svg class="w-full h-full" viewBox="0 0 24 24" fill="none">
                                                            <path fill="#EA4335" d="M12 2C6.48 2 2 6.48 2 12c0 .97.14 1.9.4 2.78l4.4-7.62A5.98 5.98 0 0112 6c.9 0 1.76.2 2.53.56l3.58-6.2A9.95 9.95 0 0012 2z"/>
                                                            <path fill="#FBBC05" d="M21.6 9.22A9.96 9.96 0 0012 2c.9 0 1.76.2 2.53.56L11 8.5v7h7.24a5.98 5.98 0 01-1.74 3.78l5.1-8.82c0-.08.01-.16 0-.24z"/>
                                                            <path fill="#34A853" d="M12 22a9.96 9.96 0 009.6-12.78l-5.1 8.82c-.89.65-1.97 1.04-3.14 1.04-.6 0-1.18-.1-1.72-.28l-3.58 6.2C9.04 21.86 10.49 22 12 22z"/>
                                                            <path fill="#4285F4" d="M6.4 14.78a5.98 5.98 0 01-.4-2.78c0-1.63.65-3.1 1.7-4.18l-4.4-7.62A9.96 9.96 0 002 12c0 4.14 2.52 7.7 6.13 9.22l3.58-6.2c-2.32-.47-4.22-2.12-5.31-4.24z"/>
                                                            <circle cx="12" cy="12" r="3.5" fill="#FFFFFF"/>
                                                            <circle cx="12" cy="12" r="2.5" fill="#4285F4"/>
                                                        </svg>
                                                    </template>
                                                    <template v-else-if="getSessionAgentDetails(selectedSession.user_agent).browser === 'Safari'">
                                                        <svg class="w-full h-full" viewBox="0 0 24 24" fill="none">
                                                            <circle cx="12" cy="12" r="10" fill="#0EA5E9"/>
                                                            <circle cx="12" cy="12" r="8" stroke="white" stroke-width="0.75" stroke-dasharray="1 1"/>
                                                            <path d="M14.5 9.5l-5 5 1.5-4 5-5-1.5 4z" fill="#EF4444"/>
                                                            <path d="M9.5 14.5l5-5-1.5 4-5 5 1.5-4z" fill="#E2E8F0"/>
                                                            <circle cx="12" cy="12" r="1" fill="white"/>
                                                        </svg>
                                                    </template>
                                                    <template v-else-if="getSessionAgentDetails(selectedSession.user_agent).browser === 'Firefox'">
                                                        <svg class="w-full h-full" viewBox="0 0 24 24" fill="none">
                                                            <circle cx="12" cy="12" r="10" fill="#E0f2fe"/>
                                                            <path d="M12 2a10 10 0 00-6.9 17.2c.4-.4.9-.7 1.3-1 .2-.1.4-.4.4-.7v-1.4c0-.6.3-1.1.8-1.4l2.1-1.3c.3-.2.5-.5.5-.9v-.8c0-.4-.2-.8-.5-1L8.5 9.8c-.3-.2-.5-.6-.5-1V8c0-.6.4-1.1 1-1.2.6-.1 1.2.1 1.5.6l.8 1.2c.3.5.9.8 1.5.8h.4c.5 0 .9-.3 1.1-.7l1.1-2.2c.3-.6.9-1 1.6-1H16c1.1 0 2 .9 2 2v.8c0 .5-.2 1-.6 1.3l-2.4 1.8c-.4.3-.6.8-.6 1.3v1.6c0 .4.2.8.5 1.1l2.4 2.4c.4.4.6.9.6 1.5v.4A10 10 0 0012 2z" fill="#F97316"/>
                                                        </svg>
                                                    </template>
                                                    <template v-else-if="getSessionAgentDetails(selectedSession.user_agent).browser === 'Edge'">
                                                        <svg class="w-full h-full" viewBox="0 0 24 24" fill="none">
                                                            <circle cx="12" cy="12" r="10" fill="#f0fdf4"/>
                                                            <path d="M12 4c-3.8 0-7 2.8-7.7 6.5.6-.4 1.3-.5 2-.5 1.9 0 3.5 1.3 3.9 3.1.2.9.1 1.9-.3 2.7-.4.8-1.1 1.4-2 1.7c1.2.9 2.7 1.5 4.3 1.5 3.9 0 7-3.1 7-7s-3.1-7-7-7z" fill="#10B981"/>
                                                        </svg>
                                                    </template>
                                                    <template v-else>
                                                        <svg class="w-full h-full text-gray-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <circle cx="12" cy="12" r="10"/>
                                                            <line x1="2" y1="12" x2="22" y2="12"/>
                                                            <path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>
                                                        </svg>
                                                    </template>
                                                </component>
                                            </div>
                                        </div>

                                        <!-- Device Label -->
                                        <span class="text-[12px] font-medium text-gray-500 dark:text-zinc-400 text-center leading-tight">
                                            {{ getSessionAgentDetails(selectedSession.user_agent).browserLabel }} on {{ getSessionAgentDetails(selectedSession.user_agent).osLabel }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Divider (Separates top info from bottom details) -->
                                <div class="border-t border-gray-200 dark:border-zinc-700/60 my-6"></div>

                                <!-- Bottom Section: Authentication, Organization, Application -->
                                <div class="space-y-4">
                                    <div class="flex items-start text-[13px]">
                                        <span class="w-[100px] shrink-0 text-gray-400 dark:text-zinc-500 font-normal">Authentication</span>
                                        <span class="text-gray-700 dark:text-zinc-300 font-normal">{{ getSessionAuthentication(selectedSession) }}</span>
                                    </div>

                                    <div class="flex items-start text-[13px]">
                                        <span class="w-[100px] shrink-0 text-gray-400 dark:text-zinc-500 font-normal">Organization</span>
                                        <a href="#" class="text-[13px] text-[#2563EB] font-normal hover:underline">
                                            {{ getSessionOrganization(selectedSession) }}
                                        </a>
                                    </div>

                                    <div class="flex items-start text-[13px]">
                                        <span class="w-[100px] shrink-0 text-gray-400 dark:text-zinc-500 font-normal">Application</span>
                                        <span class="text-gray-700 dark:text-zinc-300 font-medium">{{ selectedSession?.application || 'Portal FMIKOM' }}</span>
                                    </div>
                                </div>

                                <!-- Action Buttons (Inside the padding, no separate gray footer) -->
                                <div class="flex items-center justify-between pt-4">
                                    <div>
                                        <!-- Revoke session button on the left of footer if active -->
                                        <button 
                                            v-if="getSessionStatus(selectedSession) === 'Active'"
                                            @click="handleRevokeFromModal(selectedSession.id)"
                                            class="h-[34px] px-3.5 rounded-md border border-rose-200 text-rose-600 text-[13px] font-semibold hover:bg-rose-50 transition-colors shadow-sm bg-white dark:bg-zinc-900 dark:shadow-none"
                                        >
                                            Revoke session
                                        </button>
                                    </div>
                                    <button
                                        class="h-[34px] px-5 rounded-md text-[13px] font-semibold text-gray-700 dark:text-zinc-300 border border-gray-300 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm cursor-pointer dark:shadow-none"
                                        @click="modal.sessionDetails = false"
                                    >
                                        Done
                                    </button>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

        <!-- USER EMAIL DETAILS MODAL (Custom styled to match WorkOS visual style) -->
        <Teleport to="body">
            <Transition
                enter-from-class="opacity-0"
                enter-active-class="transition-opacity duration-200"
                leave-to-class="opacity-0"
                leave-active-class="transition-opacity duration-150"
            >
                <div v-if="modal.emailDetails" class="fixed inset-0 z-60 flex items-center justify-center bg-transparent border-0 w-screen h-screen max-w-none max-h-none p-0">
                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-black/40 backdrop-blur-[1px]" aria-hidden="true" @click="modal.emailDetails = false" />

                    <!-- Modal Panel -->
                    <Transition
                        enter-from-class="opacity-0 scale-95 translate-y-4"
                        enter-active-class="transition-all duration-300 ease-out"
                        leave-to-class="opacity-0 scale-95 translate-y-4"
                        leave-active-class="transition-all duration-200 ease-in"
                    >
                        <div class="relative bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-gray-100 dark:border-zinc-800 w-full max-w-[620px] mx-4 overflow-hidden p-8 dark:shadow-none" style="font-family: var(--wos-font)">
                            <!-- Header -->
                            <div class="mb-6 flex justify-between items-start">
                                <div>
                                    <h2 class="text-[16px] font-bold text-gray-900 dark:text-zinc-100">Email log details</h2>
                                    <p class="text-[13px] text-gray-500 dark:text-zinc-400 mt-1">Sent on {{ formatSessionDate(selectedEmail?.created_at) }}</p>
                                </div>
                                <div class="flex items-center gap-1.5 bg-emerald-50 dark:bg-emerald-950/20 px-2.5 py-1 rounded-full border border-emerald-100 dark:border-emerald-900/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400 tracking-wide uppercase">{{ selectedEmail?.status }}</span>
                                </div>
                            </div>

                            <!-- Body Content -->
                            <div v-if="selectedEmail" class="space-y-5">
                                <!-- Recipient & Subject Info Grid -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-gray-50 dark:bg-zinc-950 rounded-xl p-4 border border-gray-150 dark:border-zinc-800 text-[13px]">
                                    <div>
                                        <div class="text-[11px] font-bold text-gray-450 dark:text-zinc-500 uppercase tracking-wider mb-1">To (Recipient)</div>
                                        <div class="text-gray-900 dark:text-zinc-200 font-semibold select-all">{{ selectedEmail.email }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[11px] font-bold text-gray-450 dark:text-zinc-500 uppercase tracking-wider mb-1">Subject</div>
                                        <div class="text-gray-900 dark:text-zinc-200 font-semibold">{{ selectedEmail.subject }}</div>
                                    </div>
                                </div>

                                <!-- Body -->
                                <div>
                                    <div class="text-[11px] font-bold text-gray-450 dark:text-zinc-500 uppercase tracking-wider mb-2">Message Body</div>
                                    <div class="whitespace-pre-wrap bg-[#f9fafb] dark:bg-zinc-950 border border-[#e5e7eb] dark:border-zinc-800 rounded-xl p-5 text-[13px] text-gray-700 dark:text-zinc-300 max-h-[300px] overflow-y-auto font-mono leading-relaxed select-text">
                                        {{ selectedEmail.body }}
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center justify-end pt-4 border-t border-gray-100 dark:border-zinc-800">
                                    <button
                                        class="h-[34px] px-5 rounded-md text-[13px] font-semibold text-gray-700 dark:text-zinc-300 border border-gray-300 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm cursor-pointer dark:shadow-none"
                                        @click="modal.emailDetails = false"
                                    >
                                        Done
                                    </button>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>