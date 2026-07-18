<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import axios from "axios";
import { computed, reactive, ref, watch } from "vue";
import AppModal from "../../components/ui/AppModal.vue";
import { toast } from "../../composables/useWorkOs";

const props = defineProps<{
	radarConfig: Array<any>;
	radarBlockedItems?: Array<any>;
}>();

const protections = reactive<any[]>([]);
const managedLists = reactive<any[]>([]);
const isSaving = ref(false);

// Protection modal state
const showManageModal = ref(false);
const editingProtection = ref<any>(null);

const policyMeta: Record<
	string,
	{
		title: string;
		desc: string;
		hasDecision: boolean;
		blockSubtext: string;
		hasNotifyUser: boolean;
	}
> = {
	bot_detection: {
		title: "Edit bot detection policy",
		desc: "Automatically block or challenge authentication attempts when a bot or script is likely attempting to sign up or sign in.",
		hasDecision: false,
		blockSubtext: "",
		hasNotifyUser: false,
	},
	brute_force: {
		title: "Edit brute force attack policy",
		desc: "Block or challenge the user if the same IP/device has multiple sign-in attempts with a single email but different passwords.",
		hasDecision: true,
		blockSubtext: "Deny sign in",
		hasNotifyUser: false,
	},
	impossible_travel: {
		title: "Edit impossible travel policy",
		desc: "Block or challenge the user if they try to sign in from a different location that's impossible to travel to since their last sign-in.",
		hasDecision: true,
		blockSubtext: "Deny sign in",
		hasNotifyUser: true,
	},
	repeat_sign_up: {
		title: "Edit repeat sign up policy",
		desc: "Block or challenge the user if they are attempting to sign up multiple times.",
		hasDecision: true,
		blockSubtext: "Deny sign up",
		hasNotifyUser: false,
	},
	stale_account: {
		title: "Edit stale account policy",
		desc: "Notify when a user signs in to a dormant account.",
		hasDecision: false,
		blockSubtext: "",
		hasNotifyUser: true,
	},
	unrecognized_device: {
		title: "Edit unrecognized device policy",
		desc: "Block or challenge the user if they are signing in from a device that hasn't been used before.",
		hasDecision: true,
		blockSubtext: "Deny sign in",
		hasNotifyUser: true,
	},
	domain_protections: {
		title: "Edit domain protections policy",
		desc: "Detect and block authentication attempts from suspicious or temporary email domains.",
		hasDecision: true,
		blockSubtext: "Deny sign in",
		hasNotifyUser: false,
	},
	disposable_email_domains: {
		title: "Edit disposable email domains policy",
		desc: "WorkOS-identified disposable email domains that can be automatically blocked.",
		hasDecision: false,
		blockSubtext: "",
		hasNotifyUser: false,
	},
	sanctioned_countries: {
		title: "Edit sanctioned countries policy",
		desc: "Block or log authentication attempts originating from U.S. OFAC sanctioned countries.",
		hasDecision: false,
		blockSubtext: "",
		hasNotifyUser: false,
	},
};

const isPolicyEnabled = computed({
	get: () => editingProtection.value?.status === "Enabled",
	set: (val) => {
		if (editingProtection.value) {
			editingProtection.value.status = val ? "Enabled" : "Logging";
		}
	},
});

// Custom list modal state
const showCustomListModal = ref(false);
const activeCustomList = ref<any>(null);
const customListTab = ref<"Allow" | "Block">("Allow");

// Custom item form state
const customItemForm = reactive({
	value: "",
	reason: "",
});
const customItemErrors = ref<string>("");
const isSubmittingCustomItem = ref(false);

const defaultProtections = [
	{
		code: "bot_detection",
		name: "Bot detection",
		status: "Enabled",
		icon: "M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z",
		desc: "Detect bots and scripted attacks.",
		auto_block: false,
		notify_admin: false,
		sensitivity_level: 50,
	},
	{
		code: "brute_force",
		name: "Brute force attack",
		status: "Enabled",
		icon: "M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636",
		desc: "Detect multiple sign-in attempts with different passwords.",
		auto_block: false,
		notify_admin: false,
		sensitivity_level: 50,
	},
	{
		code: "impossible_travel",
		name: "Impossible travel",
		status: "Logging",
		icon: "M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
		desc: "Detect sign-ins from impossible travel locations.",
		auto_block: false,
		notify_admin: false,
		sensitivity_level: 50,
	},
	{
		code: "repeat_sign_up",
		name: "Repeat sign up",
		status: "Logging",
		icon: "M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z",
		desc: "Detect the same email used to sign up multiple times.",
		auto_block: false,
		notify_admin: false,
		sensitivity_level: 50,
	},
	{
		code: "stale_account",
		name: "Stale account",
		status: "Logging",
		icon: "M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z",
		desc: "Detect accounts inactive for a long time.",
		auto_block: false,
		notify_admin: false,
		sensitivity_level: 50,
	},
	{
		code: "unrecognized_device",
		name: "Unrecognized device",
		status: "Logging",
		icon: "M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z",
		desc: "Detect sign-ins from unrecognized devices.",
		auto_block: false,
		notify_admin: false,
		sensitivity_level: 50,
	},
	{
		code: "domain_protections",
		name: "Domain protections",
		status: "Disabled",
		icon: "M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9",
		desc: "Detect and block auth attempts from suspicious email domains.",
		auto_block: false,
		notify_admin: false,
		sensitivity_level: 50,
	},
];

const defaultManagedLists = [
	{
		code: "disposable_email_domains",
		name: "Disposable email domains",
		desc: "WorkOS-identified disposable email domains",
		status: "Logging",
		auto_block: false,
		notify_admin: false,
		icon: "M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206",
	},
	{
		code: "sanctioned_countries",
		name: "U.S. sanctioned countries",
		desc: "U.S. OFAC sanctioned countries",
		status: "Disabled",
		auto_block: false,
		notify_admin: false,
		icon: "M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
	},
];

const customListTypes = [
	{
		key: "Device",
		label: "Devices",
		desc: "Configure trusted or untrusted device fingerprints.",
	},
	{
		key: "Domain",
		label: "Domains",
		desc: "Configure domain allowlist or blocklist.",
	},
	{
		key: "IP",
		label: "IP addresses",
		desc: "Configure IP address allowlist or blocklist.",
	},
	{
		key: "Email",
		label: "Users",
		desc: "Configure user email allowlist or blocklist.",
	},
	{
		key: "UserAgent",
		label: "User agents",
		desc: "Configure browser user agent allowlist or blocklist.",
	},
];

function initProtections() {
	protections.splice(
		0,
		protections.length,
		...structuredClone(defaultProtections),
	);
	managedLists.splice(
		0,
		managedLists.length,
		...structuredClone(defaultManagedLists),
	);

	if (props.radarConfig?.length) {
		props.radarConfig.forEach((cfg: any) => {
			const foundP = protections.find((p) => p.name === cfg.name);
			if (foundP) {
				foundP.id = cfg.id;
				foundP.code = cfg.code;
				foundP.status = cfg.status;
				foundP.auto_block = !!cfg.auto_block;
				foundP.notify_admin = !!cfg.notify_admin;
				foundP.sensitivity_level = cfg.sensitivity_level || 50;
				foundP.threshold_config =
					typeof cfg.threshold_config === "object" &&
					cfg.threshold_config !== null
						? cfg.threshold_config
						: {};
			}

			const foundM = managedLists.find((m) => m.name === cfg.name);
			if (foundM) {
				foundM.id = cfg.id;
				foundM.code = cfg.code;
				foundM.status = cfg.status;
				foundM.auto_block = !!cfg.auto_block;
				foundM.notify_admin = !!cfg.notify_admin;
				foundM.threshold_config =
					typeof cfg.threshold_config === "object" &&
					cfg.threshold_config !== null
						? cfg.threshold_config
						: {};
			}
		});
	}
}

watch(
	() => props.radarConfig,
	() => {
		initProtections();
	},
	{ immediate: true, deep: true },
);

const initialProtectionState = ref<string>("");

function handleManage(p: any) {
	editingProtection.value = structuredClone(p);

	if (
		!editingProtection.value.threshold_config ||
		typeof editingProtection.value.threshold_config !== "object"
	) {
		editingProtection.value.threshold_config = {};
	}
	if (!editingProtection.value.threshold_config.decision) {
		editingProtection.value.threshold_config.decision = editingProtection.value
			.auto_block
			? "block"
			: "challenge";
	}
	if (editingProtection.value.threshold_config.notify_user === undefined) {
		editingProtection.value.threshold_config.notify_user = false;
	}
	initialProtectionState.value = JSON.stringify(editingProtection.value);
	showManageModal.value = true;
}

const hasChanges = computed(() => {
	if (!editingProtection.value) return false;
	return (
		JSON.stringify(editingProtection.value) !== initialProtectionState.value
	);
});

function toggleStatus(event: any) {
	if (!editingProtection.value) return;
	const isChecked = event.target.checked;
	if (isChecked) {
		editingProtection.value.status = "Enabled";
	} else {
		const code = editingProtection.value.code;
		if (code === "domain_protections" || code === "sanctioned_countries") {
			editingProtection.value.status = "Disabled";
		} else {
			editingProtection.value.status = "Logging";
		}
	}
}

function saveManage() {
	if (editingProtection.value.threshold_config?.decision === "block") {
		editingProtection.value.auto_block = true;
	} else if (
		editingProtection.value.threshold_config?.decision === "challenge"
	) {
		editingProtection.value.auto_block = false;
	}

	const idxP = protections.findIndex(
		(p) => p.name === editingProtection.value.name,
	);
	if (idxP >= 0) {
		protections[idxP] = editingProtection.value;
	} else {
		const idxM = managedLists.findIndex(
			(m) => m.name === editingProtection.value.name,
		);
		if (idxM >= 0) {
			managedLists[idxM] = editingProtection.value;
		}
	}

	// Save immediately to DB
	isSaving.value = true;
	const allConfigs = [
		...protections.map((p) => ({
			id: p.id,
			name: p.name,
			status: p.status,
			auto_block: p.auto_block,
			notify_admin: p.notify_admin,
			sensitivity_level: p.sensitivity_level,
			threshold_config: p.threshold_config || {},
		})),
		...managedLists.map((m) => ({
			id: m.id,
			name: m.name,
			status: m.status,
			auto_block: m.auto_block,
			notify_admin: m.notify_admin,
			sensitivity_level: 50,
			threshold_config: m.threshold_config || {},
		})),
	];

	router.patch(
		"/workos/radar/config",
		{
			protections: allConfigs,
		},
		{
			preserveScroll: true,
			onSuccess: () => {
				toast("Radar configuration saved.", "success");
				showManageModal.value = false;
			},
			onError: () => toast("Failed to save configuration.", "error"),
			onFinish: () => {
				isSaving.value = false;
			},
		},
	);
}

function statusColor(s: string) {
	if (s === "Enabled") return "text-emerald-600";
	if (s === "Logging") return "text-amber-600";
	return "text-gray-400";
}
function statusDot(s: string) {
	if (s === "Enabled") return "bg-emerald-500";
	if (s === "Logging") return "bg-amber-400";
	return "bg-gray-300";
}

// Custom lists summary & operations
function getCustomListSummary(typeKey: string) {
	const items =
		props.radarBlockedItems?.filter((item) => item.type === typeKey) || [];
	const allowedCount = items.filter((item) => item.action === "Allow").length;
	const blockedCount = items.filter((item) => item.action === "Block").length;

	if (allowedCount === 0 && blockedCount === 0) {
		return `No items have been allowed or blocked.`;
	}

	const parts = [];
	if (allowedCount > 0) parts.push(`${allowedCount} allowed`);
	if (blockedCount > 0) parts.push(`${blockedCount} blocked`);

	const res = parts.join(", ");
	return res.charAt(0).toUpperCase() + res.slice(1);
}

const initialCustomListState = ref<string>("");

function openCustomListEdit(typeInfo: any) {
	activeCustomList.value = typeInfo;
	customListTab.value = "Allow";
	customItemForm.value = "";
	customItemForm.reason = "";
	customItemErrors.value = "";

	const itemsForThisType =
		props.radarBlockedItems?.filter((item) => item.type === typeInfo.key) || [];
	initialCustomListState.value = JSON.stringify(itemsForThisType);

	showCustomListModal.value = true;
}

const hasCustomListChanges = computed(() => {
	if (!activeCustomList.value) return false;
	const currentItems =
		props.radarBlockedItems?.filter(
			(item) => item.type === activeCustomList.value.key,
		) || [];
	return JSON.stringify(currentItems) !== initialCustomListState.value;
});

const customListModalTitle = computed(() => {
	if (!activeCustomList.value) return "";
	const label = activeCustomList.value.label.toLowerCase();
	return `Edit restricted ${label}`;
});

const customListModalDesc = computed(() => {
	if (!activeCustomList.value) return "";
	const label = activeCustomList.value.label.toLowerCase();
	return `Allow or block specific ${label} to bypass any detections.`;
});

const filteredBlockedItems = computed(() => {
	if (!activeCustomList.value || !props.radarBlockedItems) return [];
	return props.radarBlockedItems.filter(
		(item) =>
			item.type === activeCustomList.value.key &&
			item.action === customListTab.value,
	);
});

function validateCustomItem(type: string, value: string): string | null {
	const val = value.trim();
	if (!val) return "Value tidak boleh kosong.";

	if (type === "IP") {
		const ipv4Regex = /^(\d{1,3}\.){3}\d{1,3}$/;
		const ipv6Regex = /^([\da-fA-F]{1,4}:){7}[\da-fA-F]{1,4}$/;
		if (ipv4Regex.test(val)) {
			const parts = val.split(".").map(Number);
			if (parts.some((p) => p < 0 || p > 255 || Number.isNaN(p))) {
				return "Format IP Address tidak valid (contoh: 192.168.1.1).";
			}
		} else if (!ipv6Regex.test(val)) {
			return "Format IP Address tidak valid (contoh: 192.168.1.1).";
		}
	} else if (type === "Email") {
		const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		if (!emailRegex.test(val)) {
			return "Format Email tidak valid (contoh: user@domain.com).";
		}
	} else if (type === "Domain") {
		const domainRegex = /^([a-zA-Z0-9]+(-[a-zA-Z0-9]+)*\.)+[a-zA-Z]{2,}$/;
		if (!domainRegex.test(val)) {
			return "Format Domain tidak valid (contoh: domain.com).";
		}
	}

	return null;
}

function addCustomItem() {
	const type = activeCustomList.value.key;
	const value = customItemForm.value.trim();
	const reason = customItemForm.reason.trim();
	const action = customListTab.value;

	customItemErrors.value = "";

	const errorMsg = validateCustomItem(type, value);
	if (errorMsg) {
		customItemErrors.value = errorMsg;
		return;
	}

	isSubmittingCustomItem.value = true;

	axios
		.post("/workos/radar/blocked-items", {
			type,
			value,
			action,
			reason,
		})
		.then((response) => {
			toast(response.data.message || "Item berhasil ditambahkan.", "success");
			customItemForm.value = "";
			customItemForm.reason = "";
			router.reload({
				only: ["radarBlockedItems"],
				preserveScroll: true,
			} as any);
		})
		.catch((error) => {
			const msg = error.response?.data?.message || "Gagal menambahkan item.";
			customItemErrors.value = msg;
			toast(msg, "error");
		})
		.finally(() => {
			isSubmittingCustomItem.value = false;
		});
}

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

function removeCustomItem(id: number) {
	openConfirm({
		title: "Hapus item",
		description: "Tindakan ini akan menghapus item dari daftar kustom.",
		message: "Apakah Anda yakin ingin menghapus item ini?",
		confirmText: "Hapus",
		onConfirm: async () => {
			try {
				const response = await axios.delete(
					`/workos/radar/blocked-items/${id}`,
				);
				toast(response.data.message || "Item berhasil dihapus.", "success");
				router.reload({
					only: ["radarBlockedItems"],
					preserveScroll: true,
				} as any);
			} catch (error: any) {
				const msg = error.response?.data?.message || "Gagal menghapus item.";
				toast(msg, "error");
			}
		},
	});
}

function disableAllProtection() {
	openConfirm({
		title: "Disable protection",
		description: "This will disable all active security protections in Radar.",
		message:
			"Are you sure you want to turn off all security protections? Your application will no longer be defended against automated threats.",
		confirmText: "Disable protection",
		confirmBgClass: "bg-[#dc2626] hover:bg-[#b91c1c]",
		onConfirm: async () => {
			isSaving.value = true;

			const disabledProtections = protections.map((p) => {
				p.status = p.code === "domain_protections" ? "Disabled" : "Logging";
				return p;
			});
			const disabledManagedLists = managedLists.map((m) => {
				m.status = m.code === "sanctioned_countries" ? "Disabled" : "Logging";
				return m;
			});

			const allConfigs = [
				...disabledProtections.map((p) => ({
					id: p.id,
					name: p.name,
					status: p.status,
					auto_block: p.auto_block,
					notify_admin: p.notify_admin,
					sensitivity_level: p.sensitivity_level,
					threshold_config: p.threshold_config || {},
				})),
				...disabledManagedLists.map((m) => ({
					id: m.id,
					name: m.name,
					status: m.status,
					auto_block: m.auto_block,
					notify_admin: m.notify_admin,
					sensitivity_level: 50,
					threshold_config: m.threshold_config || {},
				})),
			];

			router.patch(
				"/workos/radar/config",
				{
					protections: allConfigs,
				},
				{
					preserveScroll: true,
					onSuccess: () => {
						toast("All security protections have been disabled.", "success");
						initProtections();
					},
					onError: () => toast("Failed to disable protections.", "error"),
					onFinish: () => {
						isSaving.value = false;
					},
				},
			);
		},
	});
}
</script>

<template>
    <div>
        <!-- Protections -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm mb-5 dark:shadow-none">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-zinc-800">
                <p class="text-[14px] font-semibold text-gray-900 dark:text-zinc-100">Protections</p>
                <p class="text-[12px] text-gray-500 dark:text-zinc-400 mt-0.5">Configure specific protections to automatically block or challenge behavior.</p>
            </div>
            <div class="divide-y divide-gray-50 dark:divide-zinc-800">
                <div v-for="p in protections" :key="p.name" class="flex flex-col sm:flex-row sm:items-center px-6 py-4 gap-3 sm:gap-4">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-zinc-800 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-gray-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="p.icon"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-semibold text-gray-800 dark:text-zinc-200">{{ p.name }}</p>
                            <p class="text-[11px] text-gray-400 dark:text-zinc-400 mt-0.5 leading-tight">{{ p.desc }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 sm:justify-end ml-11 sm:ml-0">
                        <span :class="['text-[11px] font-medium flex items-center gap-1.5 w-20', statusColor(p.status)]">
                            <span class="w-1.5 h-1.5 rounded-full" :class="statusDot(p.status)"/>
                            {{ p.status }}
                        </span>
                        <button
                            :class="['h-7 px-3 text-[12px] border rounded-md transition-colors w-[76px] font-medium shadow-sm cursor-pointer', p.status === 'Enabled' ? 'border-[#d1d5db] dark:border-zinc-700 text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 bg-white dark:bg-zinc-900' : 'border-[#d1d5db] dark:border-zinc-700 text-[#111827] dark:text-zinc-100 bg-[#f9fafb] dark:bg-zinc-800 hover:bg-[#f3f4f6] dark:hover:bg-zinc-700/60']"
                            @click="handleManage(p)"
                        >
                            {{ p.status === 'Enabled' ? 'Manage' : 'Enable' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Restrictions -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm mb-5 dark:shadow-none">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-zinc-800">
                <p class="text-[14px] font-semibold text-[#111827] dark:text-zinc-100">Restrictions</p>
            </div>
            <!-- Managed lists -->
            <div class="px-6 py-4">
                <p class="text-[12px] font-semibold text-gray-700 dark:text-zinc-300 mb-1">Managed lists</p>
                <p class="text-[11px] text-gray-400 dark:text-zinc-400 mb-3">Restrict lists of known identifiers.</p>
                <div v-for="r in managedLists" :key="r.name" class="flex items-center justify-between py-3 border-t border-gray-50 dark:border-zinc-800">
                    <div>
                        <p class="text-[13px] font-medium text-gray-800 dark:text-zinc-200">{{ r.name }}</p>
                        <p class="text-[11px] text-gray-400 dark:text-zinc-400">{{ r.desc }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span :class="['text-[11px] font-medium flex items-center gap-1.5 w-20', statusColor(r.status)]">
                            <span class="w-1.5 h-1.5 rounded-full" :class="statusDot(r.status)"/>
                            {{ r.status }}
                        </span>
                        <button 
                            :class="['h-7 px-3 text-[12px] border rounded-md transition-colors w-[76px] font-medium shadow-sm cursor-pointer', r.status === 'Enabled' ? 'border-[#d1d5db] dark:border-zinc-700 text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 bg-white dark:bg-zinc-900' : 'border-[#d1d5db] dark:border-zinc-700 text-[#111827] dark:text-zinc-100 bg-[#f9fafb] dark:bg-zinc-800 hover:bg-[#f3f4f6] dark:hover:bg-zinc-700/60']"
                            @click="handleManage(r)"
                        >
                            {{ r.status === 'Enabled' ? 'Manage' : 'Enable' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom lists -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm mb-5 dark:shadow-none">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-zinc-800">
                <p class="text-[14px] font-semibold text-[#111827] dark:text-zinc-100">Custom lists</p>
                <p class="text-[12px] text-gray-500 dark:text-zinc-400 mt-0.5">Allow or block specific identifiers.</p>
            </div>
            <div class="divide-y divide-gray-50 dark:divide-zinc-800">
                <div v-for="cl in customListTypes" :key="cl.key" class="flex flex-col sm:flex-row sm:items-center justify-between px-6 py-4 gap-2">
                    <div>
                        <p class="text-[13px] font-medium text-gray-800 dark:text-zinc-200">{{ cl.label }}</p>
                        <p class="text-[11px] text-gray-400 dark:text-zinc-400 mt-0.5">{{ getCustomListSummary(cl.key) }}</p>
                    </div>
                    <button 
                        @click="openCustomListEdit(cl)"
                        class="h-7 px-3 text-[12px] font-medium border border-[#d1d5db] dark:border-zinc-700 rounded-md text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 transition-colors shadow-sm self-start sm:self-auto w-16 bg-white dark:bg-zinc-900 cursor-pointer dark:shadow-none"
                    >
                        Edit
                    </button>
                </div>
            </div>
        </div>

        <!-- Manage Protection Modal -->
        <AppModal 
            :show="showManageModal" 
            @close="showManageModal = false"
        >
            <div class="space-y-4" v-if="editingProtection">
                <!-- Header Title & Subtitle inside default slot -->
                <div>
                    <h2 class="text-[16px] font-semibold text-[#111827] dark:text-zinc-100">{{ policyMeta[editingProtection.code]?.title || editingProtection.name }}</h2>
                    <p class="text-[12.5px] text-[#6b7280] dark:text-zinc-400 mt-1.5 leading-relaxed">{{ policyMeta[editingProtection.code]?.desc || editingProtection.desc }}</p>
                </div>

                <!-- Enable switch -->
                <div class="flex items-center gap-3 py-1">
                    <label for="policy-enabled-checkbox" class="relative inline-flex items-center cursor-pointer">
                        <input 
                            id="policy-enabled-checkbox"
                            type="checkbox" 
                            :checked="editingProtection.status === 'Enabled'" 
                            @change="toggleStatus" 
                            class="sr-only peer"
                        >
                        <div class="w-9 h-5 bg-gray-200 dark:bg-zinc-800 rounded-full peer peer-focus:ring-2 peer-focus:ring-[#2563eb]/20 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white dark:bg-zinc-900 after:border-gray-300 dark:border-zinc-700 dark:after:border-zinc-700 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                        <span class="sr-only">Enable protection policy</span>
                    </label>
                    <span class="text-sm font-semibold text-gray-800 dark:text-zinc-200">Enable</span>
                </div>

                <!-- Content below switch (with divider) -->
                <div class="border-t border-gray-100 dark:border-zinc-800 pt-4 space-y-4">
                    <!-- Decision Section (Block / Challenge) -->
                    <div v-if="policyMeta[editingProtection.code]?.hasDecision" class="space-y-2">
                        <span class="block text-[13px] font-semibold text-gray-900 dark:text-zinc-100">Decision</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Block Card -->
                            <div 
                                @click="editingProtection.status === 'Enabled' && (editingProtection.threshold_config.decision = 'block')"
                                :class="[
                                    'border rounded-xl p-3.5 flex flex-col justify-between h-20 transition-all', 
                                    editingProtection.status !== 'Enabled'
                                        ? 'border-gray-200 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-800/10 opacity-50 cursor-not-allowed select-none' 
                                        : (editingProtection.threshold_config?.decision === 'block'
                                            ? 'border-2 border-blue-600 dark:border-blue-500 bg-blue-50/10 dark:bg-blue-950/10 cursor-pointer' 
                                            : 'border border-gray-200 dark:border-zinc-800 hover:border-gray-300 dark:hover:border-zinc-700 bg-white dark:bg-zinc-900 cursor-pointer')
                                ]"
                            >
                                <span class="block text-[13px] font-semibold text-gray-900 dark:text-zinc-100">Block</span>
                                <span class="block text-[11px] text-gray-500 dark:text-zinc-400 mt-1">{{ policyMeta[editingProtection.code]?.blockSubtext }}</span>
                            </div>

                            <!-- Challenge Card -->
                            <div 
                                @click="editingProtection.status === 'Enabled' && (editingProtection.threshold_config.decision = 'challenge')"
                                :class="[
                                    'border rounded-xl p-3.5 flex flex-col justify-between h-20 transition-all', 
                                    editingProtection.status !== 'Enabled'
                                        ? 'border-gray-200 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-800/10 opacity-50 cursor-not-allowed select-none' 
                                        : (editingProtection.threshold_config?.decision === 'challenge'
                                            ? 'border-2 border-blue-600 dark:border-blue-500 bg-blue-50/10 dark:bg-blue-950/10 cursor-pointer' 
                                            : 'border border-gray-200 dark:border-zinc-800 hover:border-gray-300 dark:hover:border-zinc-700 bg-white dark:bg-zinc-900 cursor-pointer')
                                ]"
                            >
                                <span class="block text-[13px] font-semibold text-gray-900 dark:text-zinc-100">Challenge</span>
                                <span class="block text-[11px] text-gray-500 dark:text-zinc-400 mt-1">With one-time passcode</span>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications Section -->
                    <div class="space-y-3">
                        <span class="block text-[13px] font-semibold text-[#111827] dark:text-zinc-100">Notifications</span>
                        
                        <!-- Notify Admin -->
                        <label 
                            class="flex items-start gap-3" 
                            :class="[editingProtection.status !== 'Enabled' ? 'opacity-50 cursor-not-allowed select-none' : 'cursor-pointer']"
                        >
                            <input 
                                type="checkbox" 
                                v-model="editingProtection.notify_admin" 
                                :disabled="editingProtection.status !== 'Enabled'"
                                class="mt-1 h-4 w-4 rounded border-gray-300 dark:border-zinc-700 text-blue-600 focus:ring-[#2563eb] disabled:opacity-50 disabled:cursor-not-allowed" 
                            />
                            <span class="text-[12.5px] text-[#4b5563] dark:text-zinc-300 leading-normal">Notify admin by email every time this policy takes an action.</span>
                        </label>

                        <!-- Notify User (dynamic) -->
                        <label 
                            v-if="policyMeta[editingProtection.code]?.hasNotifyUser" 
                            class="flex items-start gap-3" 
                            :class="[editingProtection.status !== 'Enabled' ? 'opacity-50 cursor-not-allowed select-none' : 'cursor-pointer']"
                        >
                            <input 
                                type="checkbox" 
                                v-model="editingProtection.threshold_config.notify_user" 
                                :disabled="editingProtection.status !== 'Enabled'"
                                class="mt-1 h-4 w-4 rounded border-gray-300 dark:border-zinc-700 text-blue-600 focus:ring-[#2563eb] disabled:opacity-50 disabled:cursor-not-allowed" 
                            />
                            <span class="text-[12.5px] text-[#4b5563] dark:text-zinc-300 leading-normal">Notify user by email every time this policy takes an action.</span>
                        </label>
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2 mt-4">
                    <button 
                        @click="showManageModal = false" 
                        class="h-[34px] px-4 text-[13px] font-semibold text-[#374151] dark:text-zinc-300 border border-[#d1d5db] dark:border-zinc-700 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 bg-white dark:bg-zinc-900 rounded-md transition-colors shadow-sm cursor-pointer dark:shadow-none"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="saveManage" 
                        :disabled="!hasChanges || isSaving"
                        :class="[
                            'h-[34px] px-4 text-[13px] font-semibold rounded-md transition-colors relative flex items-center justify-center min-w-[100px]',
                            (hasChanges && !isSaving) 
                                ? 'text-white bg-[#111827] dark:bg-zinc-100 dark:text-zinc-900 hover:bg-black dark:hover:bg-white cursor-pointer shadow-sm' 
                                : 'text-[#9ca3af] bg-[#f3f4f6] dark:bg-zinc-800 dark:text-zinc-500 cursor-not-allowed'
                        ]"
                    >
                        <span :class="{ 'opacity-0': isSaving }">Save changes</span>
                        <div v-if="isSaving" class="absolute inset-0 flex items-center justify-center">
                            <svg class="animate-spin h-4 w-4 text-[#9ca3af] dark:text-zinc-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </button>
                </div>
            </template>
        </AppModal>

        <!-- Edit Custom List Modal -->
        <AppModal 
            :show="showCustomListModal" 
            @close="showCustomListModal = false"
        >
            <div class="space-y-4" v-if="activeCustomList">
                <!-- Header Title & Subtitle inside default slot -->
                <div>
                    <h2 class="text-[16px] font-semibold text-[#111827] dark:text-zinc-100">{{ customListModalTitle }}</h2>
                    <p class="text-[12.5px] text-[#6b7280] dark:text-zinc-400 mt-1.5 leading-relaxed">{{ customListModalDesc }}</p>
                </div>

                <!-- Tab Filter: Allowed vs Blocked -->
                <div class="bg-gray-100 dark:bg-zinc-800 p-1 rounded-lg flex">
                    <button
                        v-for="tab in ([{ key: 'Allow', label: 'Allowed' }, { key: 'Block', label: 'Blocked' }] as const)"
                        :key="tab.key"
                        :class="['flex-1 py-1.5 text-[13px] font-medium rounded-md transition-all duration-150 text-center border-0 cursor-pointer',
                            customListTab === tab.key 
                                ? 'bg-white dark:bg-zinc-900 text-gray-900 dark:text-zinc-100 shadow-sm' 
                                : 'text-gray-500 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-zinc-200 bg-transparent']"
                        @click="customListTab = tab.key"
                    >
                        {{ tab.label }}
                    </button>
                </div>

                <!-- Form Add New Item -->
                <div class="space-y-2">
                    <label for="custom-item-value-input" class="flex items-center gap-1 text-[13px] font-semibold text-[#111827] dark:text-zinc-100">
                        <span>{{ customListTab === 'Allow' ? 'Allowed' : 'Blocked' }} {{ activeCustomList.label.toLowerCase() }}</span>
                        <!-- Small SVG info icon (i) -->
                        <svg class="w-3.5 h-3.5 text-gray-400 dark:text-zinc-500 cursor-pointer" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </label>
                    
                    <div class="flex gap-2">
                        <input 
                            id="custom-item-value-input"
                            type="text" 
                            v-model="customItemForm.value" 
                            :placeholder="activeCustomList.key === 'Device' ? 'Enter device identifier' : activeCustomList.key === 'IP' ? 'Enter IP address' : activeCustomList.key === 'Domain' ? 'Enter domain' : activeCustomList.key === 'Email' ? 'Enter email address' : 'Enter user agent'"
                            class="flex-1 text-[13px] border border-gray-250 dark:border-zinc-700 rounded-lg py-2 px-3 focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:ring-zinc-700 bg-white dark:bg-zinc-900 text-gray-800 dark:text-zinc-100 placeholder-gray-405 dark:placeholder-zinc-600"
                            @keyup.enter="customItemForm.value && addCustomItem()"
                        />
                        <button 
                            @click="addCustomItem"
                            :disabled="isSubmittingCustomItem || !customItemForm.value"
                            class="h-[38px] px-4 text-[13px] font-semibold bg-white dark:bg-zinc-900 border border-[#d1d5db] dark:border-zinc-700 text-[#374151] dark:text-zinc-300 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 rounded-lg shadow-sm transition-colors disabled:opacity-50 flex items-center justify-center gap-1.5 shrink-0 cursor-pointer dark:shadow-none"
                        >
                            <svg v-if="isSubmittingCustomItem" class="animate-spin h-3.5 w-3.5 text-gray-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span v-else>+ Add</span>
                        </button>
                    </div>

                    <!-- Error Alert -->
                    <div v-if="customItemErrors" class="bg-red-50 dark:bg-red-950/20 border border-red-100 dark:border-red-900/40 rounded-lg p-2.5 flex items-start gap-2.5 mt-2">
                        <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p class="text-[12px] text-red-700 dark:text-red-400 font-medium leading-normal">{{ customItemErrors }}</p>
                    </div>
                </div>

                <!-- Items List Box -->
                <div class="border border-gray-200 dark:border-zinc-800 rounded-xl bg-white dark:bg-zinc-900 p-6 min-h-[300px] flex flex-col justify-start">
                    <div v-if="!filteredBlockedItems.length" class="flex flex-col items-center justify-center py-12 text-center my-auto">
                        <div class="w-10 h-10 rounded-full bg-gray-50 dark:bg-zinc-800 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-gray-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-11.314l.707.707m11.314 11.314l.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                            </svg>
                        </div>
                        <p class="text-[13px] text-gray-500 dark:text-zinc-400 font-medium">
                            No {{ activeCustomList.label.toLowerCase() }} have been {{ customListTab === 'Allow' ? 'allowed' : 'blocked' }}
                        </p>
                    </div>
                    
                    <div v-else class="divide-y divide-gray-100 dark:divide-zinc-800 max-h-[300px] overflow-y-auto w-full">
                        <div v-for="item in filteredBlockedItems" :key="item.id" class="flex items-center justify-between py-3 px-4 first:pt-0 last:pb-0 hover:bg-gray-50 dark:bg-zinc-900/50 dark:hover:bg-zinc-800/40 transition-colors">
                            <div class="min-w-0 flex-1 pr-4">
                                <p class="text-[13px] font-mono font-medium text-gray-900 dark:text-zinc-100 truncate">{{ item.value }}</p>
                            </div>
                            <button 
                                @click="removeCustomItem(item.id)"
                                class="p-1.5 rounded-lg text-gray-400 dark:text-zinc-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors shrink-0 bg-transparent border-0 cursor-pointer"
                                title="Delete from list"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-2 mt-4">
                    <button 
                        @click="showCustomListModal = false" 
                        class="h-[34px] px-4 text-[13px] font-semibold text-[#374151] dark:text-zinc-300 border border-[#d1d5db] dark:border-zinc-700 hover:bg-[#f9fafb] dark:hover:bg-zinc-800 bg-white dark:bg-zinc-900 rounded-md transition-colors shadow-sm cursor-pointer dark:shadow-none"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="showCustomListModal = false" 
                        :disabled="!hasCustomListChanges"
                        :class="[
                            'h-[34px] px-4 text-[13px] font-semibold rounded-md transition-colors shadow-sm cursor-pointer',
                            hasCustomListChanges 
                                ? 'text-white bg-[#111827] dark:bg-zinc-100 dark:text-zinc-900 hover:bg-black dark:hover:bg-white' 
                                : 'text-[#9ca3af] bg-[#f3f4f6] dark:bg-zinc-800 dark:text-zinc-500 cursor-not-allowed']"
                    >
                        Save changes
                    </button>
                </div>
            </template>
        </AppModal>

        <!-- Danger zone -->
        <div class="mt-8">
            <p class="text-[14px] font-semibold text-[#dc2626] dark:text-red-400 mb-3">Danger zone</p>
            <div class="bg-white dark:bg-zinc-900/40 border border-[#fca5a5] dark:border-red-900/40 rounded-xl shadow-sm p-6 dark:shadow-none">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <p class="text-[13px] font-semibold text-gray-900 dark:text-zinc-100">Disable protection</p>
                        <p class="text-[12px] text-[#6b7280] dark:text-zinc-400 mt-1 leading-relaxed">
                            Turning off protection will stop defending your app against AI bots, account abuse, credential theft, and more.
                        </p>
                    </div>
                    <button 
                        @click="disableAllProtection"
                        class="h-[34px] px-4 text-[13px] font-semibold text-[#dc2626] dark:text-red-400 border border-[#fca5a5] dark:border-red-900/40 hover:bg-red-50/30 dark:hover:bg-red-950/20 bg-white dark:bg-zinc-900/40 rounded-md transition-colors shadow-sm shrink-0 cursor-pointer dark:shadow-none"
                    >
                        Disable protection
                    </button>
                </div>
            </div>
        </div>

        <!-- GENERIC CONFIRMATION MODAL -->
        <AppModal :show="confirmModal.show" :title="confirmModal.title" :description="confirmModal.description" @close="confirmModal.show = false">
            <div class="py-2 text-[13.5px] text-[#4b5563] dark:text-zinc-300">
                {{ confirmModal.message }}
            </div>
            <template #footer>
                <button class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-[#374151] dark:text-zinc-300 border border-[#d1d5db] dark:border-zinc-700 hover:bg-[#f3f4f6] dark:hover:bg-zinc-800 transition-colors bg-white dark:bg-zinc-900 shadow-sm cursor-pointer dark:shadow-none" @click="confirmModal.show = false">Cancel</button>
                <button
                    :disabled="confirmModal.isLoading"
                    class="h-[34px] px-4 rounded-md text-[13px] font-semibold text-white transition-colors shadow-sm disabled:opacity-50 flex items-center gap-2 cursor-pointer border-0 dark:shadow-none"
                    :class="confirmModal.confirmBgClass"
                    @click="confirmModal.onConfirm"
                >
                    <svg v-if="confirmModal.isLoading" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    {{ confirmModal.isLoading ? 'Processing...' : confirmModal.confirmText }}
                </button>
            </template>
        </AppModal>
    </div>
</template>

