<script setup lang="ts">
import axios from "axios";
import {
	AlertCircle,
	Check,
	Key,
	Link,
	Loader2,
	Mail,
	Shield,
} from "lucide-vue-next";
import { computed, onMounted, ref } from "vue";
import AppModal from "../../components/ui/AppModal.vue";

const emit = defineEmits(["navigate"]);

// ─────────────────────────────────────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────────────────────────────────────
interface Methods {
	email_password: {
		enabled: boolean;
		min_length: number;
		complexity: number;
		require_uppercase: boolean;
		require_lowercase: boolean;
		require_number: boolean;
		require_special: boolean;
	};
	passkeys: { enabled: boolean };
	magic_links: { enabled: boolean };
	sso: { enabled: boolean };
	mfa: { required: boolean; totp_enabled: boolean; sms_enabled: boolean };
}

// ─────────────────────────────────────────────────────────────────────────────
// State
// ─────────────────────────────────────────────────────────────────────────────
const methods = ref<any | null>(null);
const password = ref<any>(null);
const session = ref<any>(null);
const saving = ref<string | null>(null); // key being saved
const toast = ref<{ type: "success" | "error"; message: string } | null>(null);
const activeModal = ref<string | null>(null);
const isLoading = ref(true);
const historyEnabled = ref(false);

// SSO local config state (for the modal)
const ssoConfig = ref({
	auto_provision: true,
	enforce_sso: false,
});

// ─────────────────────────────────────────────────────────────────────────────
// Load
// ─────────────────────────────────────────────────────────────────────────────
const fetchMethods = async () => {
	isLoading.value = true;
	try {
		const { data } = await axios.get("/workos/auth-platform/methods");
		methods.value = data.methods;
		password.value = data.password;
		session.value = data.session;
		historyEnabled.value = (data.password?.history_count ?? 0) > 0;
	} catch {
		showToast("error", "Failed to fetch configuration.");
	} finally {
		isLoading.value = false;
	}
};

onMounted(fetchMethods);

// ─────────────────────────────────────────────────────────────────────────────
// Save a single setting — debounced-style per key
// ─────────────────────────────────────────────────────────────────────────────
const saveSetting = async (key: string, value: any) => {
	saving.value = key;
	try {
		await axios.patch("/workos/auth-platform/methods", { key, value });
		showToast("success", "Setting saved.");
	} catch {
		showToast("error", "Failed to save setting.");
	} finally {
		saving.value = null;
	}
};

// ─────────────────────────────────────────────────────────────────────────────
// Save Email + Password settings modal
// ─────────────────────────────────────────────────────────────────────────────
const saveEmailPasswordSettings = async () => {
	saving.value = "email_password";
	try {
		const payload = {
			settings: {
				"email_password.enabled": methods.value.email_password.enabled,
				"email_password.min_length": methods.value.email_password.min_length,
				"email_password.complexity": methods.value.email_password.complexity,
				"email_password.require_uppercase":
					methods.value.email_password.require_uppercase,
				"email_password.require_lowercase":
					methods.value.email_password.require_lowercase,
				"email_password.require_number":
					methods.value.email_password.require_number,
				"email_password.require_special":
					methods.value.email_password.require_special,
				"password.reject_breached": password.value.reject_breached,
				"password.history_count": historyEnabled.value
					? password.value.history_count
					: 0,
			},
		};
		await axios.patch("/workos/auth-platform/methods", payload);
		showToast("success", "Email + Password policy updated successfully.");
		activeModal.value = null;
	} catch {
		showToast("error", "Failed to update Email + Password policy.");
	} finally {
		saving.value = null;
	}
};

// ─────────────────────────────────────────────────────────────────────────────
// Save SSO settings
// ─────────────────────────────────────────────────────────────────────────────
const saveSsoSettings = async () => {
	saving.value = "sso";
	try {
		await axios.patch("/workos/auth-platform/methods", {
			settings: {
				"sso.enabled": methods.value.sso.enabled,
				"sso.auto_provision": ssoConfig.value.auto_provision,
				"sso.enforce_sso": ssoConfig.value.enforce_sso,
			},
		});
		showToast("success", "SSO settings saved successfully.");
		activeModal.value = null;
	} catch {
		showToast("error", "Failed to update SSO settings.");
	} finally {
		saving.value = null;
	}
};

// ─────────────────────────────────────────────────────────────────────────────
// Save Passkeys settings
// ─────────────────────────────────────────────────────────────────────────────
const savePasskeysSettings = async () => {
	saving.value = "passkeys";
	try {
		await axios.patch("/workos/auth-platform/methods", {
			key: "passkeys.enabled",
			value: methods.value.passkeys.enabled,
		});
		showToast("success", "Passkeys setting saved.");
		activeModal.value = null;
	} catch {
		showToast("error", "Failed to save Passkeys settings.");
	} finally {
		saving.value = null;
	}
};

// ─────────────────────────────────────────────────────────────────────────────
// Save Magic Links settings
// ─────────────────────────────────────────────────────────────────────────────
const saveMagicLinksSettings = async () => {
	saving.value = "magic_links";
	try {
		await axios.patch("/workos/auth-platform/methods", {
			key: "magic_links.enabled",
			value: methods.value.magic_links.enabled,
		});
		showToast("success", "Magic Auth setting saved.");
		activeModal.value = null;
	} catch {
		showToast("error", "Failed to save Magic Auth settings.");
	} finally {
		saving.value = null;
	}
};

// ─────────────────────────────────────────────────────────────────────────────
// Toast
// ─────────────────────────────────────────────────────────────────────────────
const showToast = (type: "success" | "error", message: string) => {
	toast.value = { type, message };
	setTimeout(() => {
		toast.value = null;
	}, 3000);
};

// Complexity maps 1-4 → WorkOS labels
const COMPLEXITY_LABELS = [
	"Very Weak",
	"Weak",
	"Safely unguessable",
	"Very Strong",
];
const COMPLEXITY_COLORS = [
	"text-red-500",
	"text-orange-500",
	"text-emerald-600",
	"text-green-700",
];
const COMPLEXITY_BG = [
	"bg-red-50 border-red-200",
	"bg-orange-50 border-orange-200",
	"bg-emerald-50 border-emerald-200",
	"bg-green-50 border-green-200",
];

const complexityLabel = computed(() => {
	const c = methods.value?.email_password?.complexity ?? 3;
	return COMPLEXITY_LABELS[c - 1] ?? "Safely unguessable";
});
const complexityColor = computed(() => {
	const c = methods.value?.email_password?.complexity ?? 3;
	return COMPLEXITY_COLORS[c - 1] ?? "text-emerald-600";
});
const complexityBg = computed(() => {
	const c = methods.value?.email_password?.complexity ?? 3;
	return COMPLEXITY_BG[c - 1] ?? "bg-emerald-50 border-emerald-200";
});
const complexityDesc = computed(() => {
	const c = methods.value?.email_password?.complexity ?? 3;
	return (
		[
			"Too easy to guess — avoid for production.",
			"Weak protection — consider stronger rules.",
			"Moderate protection from offline slow-hash scenario.",
			"Very strong — best for sensitive applications.",
		][c - 1] ?? "Moderate protection from offline slow-hash scenario."
	);
});

// Active password rules summary for the card
const passwordRulesSummary = computed(() => {
	if (!methods.value) return "None";
	const ep = methods.value.email_password;
	const rules = [];
	if (ep.require_uppercase) rules.push("Uppercase");
	if (ep.require_lowercase) rules.push("Lowercase");
	if (ep.require_number) rules.push("Number");
	if (ep.require_special) rules.push("Special char");
	return rules.length ? rules.join(", ") : "None";
});

const breachedPasswordSummary = computed(() => {
	if (!password.value) return "Don't reject breached passwords";
	return password.value.reject_breached
		? "Reject breached passwords"
		: "Don't reject breached passwords";
});

const historyCountSummary = computed(() => {
	if (!password.value) return "Don't reject previously used passwords";
	const n = password.value.history_count ?? 0;
	return n > 0
		? `Reject last ${n} previously used passwords`
		: "Don't reject previously used passwords";
});

// Apply Strong preset in modal
const applyStrongPreset = () => {
	if (!methods.value) return;
	methods.value.email_password.min_length = 12;
	methods.value.email_password.complexity = 3;
	methods.value.email_password.require_uppercase = true;
	methods.value.email_password.require_lowercase = true;
	methods.value.email_password.require_number = true;
	methods.value.email_password.require_special = false;
	password.value.reject_breached = true;
};

// Track whether user is on Strong or Custom preset
const isStrongPreset = computed(() => {
	if (!methods.value || !password.value) return false;
	const ep = methods.value.email_password;
	return (
		ep.min_length >= 12 &&
		ep.complexity >= 3 &&
		ep.require_uppercase &&
		ep.require_lowercase &&
		ep.require_number &&
		password.value.reject_breached
	);
});
</script>

<template>
    <div class="space-y-5 animate-fade-in max-w-[800px]">

        <!-- Toast -->
        <Transition enter-from-class="translate-y-2 opacity-0" enter-to-class="translate-y-0 opacity-100" leave-to-class="opacity-0" enter-active-class="transition duration-200" leave-active-class="transition duration-150">
            <div v-if="toast" :class="['fixed bottom-6 right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border text-[13px] font-medium', toast.type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800']">
                <Check v-if="toast.type === 'success'" class="w-4 h-4" />
                <AlertCircle v-else class="w-4 h-4" />
                {{ toast.message }}
            </div>
        </Transition>

        <!-- Header -->
        <div class="border-b border-gray-200 pb-5">
            <h1 class="text-2xl font-semibold text-gray-900 tracking-tight">Methods</h1>
            <p class="text-[14px] text-gray-500 mt-1">Configure which authentication methods are available to your users.</p>
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="space-y-4">
            <div v-for="i in 4" :key="i" class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-4">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3 w-3/4">
                        <div class="w-10 h-10 rounded-xl wos-shimmer shrink-0" />
                        <div class="space-y-2 flex-1">
                            <div class="h-4 wos-shimmer rounded w-1/4" />
                            <div class="h-3.5 wos-shimmer rounded w-full" />
                        </div>
                    </div>
                    <div class="h-4 wos-shimmer rounded w-12 shrink-0" />
                </div>
                <div class="divide-y divide-gray-100">
                    <div v-for="j in 2" :key="j" class="flex justify-between items-center py-2.5">
                        <div class="h-3.5 wos-shimmer rounded w-1/3" />
                        <div class="h-3.5 wos-shimmer rounded w-16" />
                    </div>
                </div>
                <div class="h-8 wos-shimmer rounded w-16" />
            </div>
        </div>

        <template v-else-if="methods">

            <!-- ── Single Sign-On ───────────────────────────────────────────── -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gray-900 rounded-xl flex items-center justify-center shrink-0">
                            <Shield class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <h3 class="text-[14px] font-semibold text-gray-900">Single Sign-On</h3>
                            <p class="text-[13px] text-gray-500">Allow users to sign in with SAML and OIDC identity providers like Entra ID, Okta, and Google Workspace.</p>
                        </div>
                    </div>
                </div>
                <!-- Info rows -->
                <div class="divide-y divide-gray-100 mb-3">
                    <div class="flex items-center justify-between py-2">
                        <span class="text-[13px] text-gray-500">Enterprise SSO in AuthKit</span>
                        <div :class="['flex items-center gap-1 text-[13px] font-medium', methods.sso.enabled ? 'text-emerald-600' : 'text-gray-400']">
                            <Check v-if="methods.sso.enabled" class="w-3.5 h-3.5" />
                            {{ methods.sso.enabled ? 'Enabled' : 'Disabled' }}
                        </div>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-[13px] text-gray-500">Sign-in consent page</span>
                        <div :class="['flex items-center gap-1 text-[13px] font-medium', ssoConfig.enforce_sso ? 'text-emerald-600' : 'text-gray-400']">
                            <Check v-if="ssoConfig.enforce_sso" class="w-3.5 h-3.5" />
                            {{ ssoConfig.enforce_sso ? 'Enabled' : 'Disabled' }}
                        </div>
                    </div>
                </div>
                <button @click="activeModal = 'sso'" class="px-3 py-1.5 bg-white border border-gray-200 rounded-md text-[13px] font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                    Manage
                </button>
            </div>

            <!-- ── Email + Password ────────────────────────────────────────── -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center border border-indigo-200 shrink-0">
                            <Mail class="w-5 h-5 text-indigo-600" />
                        </div>
                        <div>
                            <h3 class="text-[14px] font-semibold text-gray-900">Email + Password</h3>
                            <p class="text-[13px] text-gray-500">Allow users to sign in with email and password.</p>
                        </div>
                    </div>
                    <div :class="['flex items-center gap-1 text-[13px] font-medium shrink-0', methods.email_password.enabled ? 'text-emerald-600' : 'text-gray-400']">
                        <Check v-if="methods.email_password.enabled" class="w-3.5 h-3.5" />
                        {{ methods.email_password.enabled ? 'Enabled' : 'Disabled' }}
                    </div>
                </div>
                <!-- Info rows -->
                <div class="divide-y divide-gray-100 mb-3">
                    <div class="flex items-center justify-between py-2">
                        <span class="text-[13px] text-gray-500">Min. password length</span>
                        <span class="text-[13px] text-gray-700 font-medium">{{ methods.email_password.min_length }} characters</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-[13px] text-gray-500">Min. password complexity</span>
                        <span class="text-[13px] text-gray-700 font-medium flex items-center gap-2">
                            {{ methods.email_password.complexity }}
                            <span :class="['text-[11px] font-semibold px-1.5 py-0.5 rounded border', complexityColor, complexityBg]">
                                {{ complexityLabel }}
                            </span>
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-[13px] text-gray-500">Password rules</span>
                        <span class="text-[13px] text-gray-700">{{ passwordRulesSummary }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-[13px] text-gray-500">Compromised passwords</span>
                        <span class="text-[13px] text-gray-700">{{ breachedPasswordSummary }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-[13px] text-gray-500">Password history</span>
                        <span class="text-[13px] text-gray-700">{{ historyCountSummary }}</span>
                    </div>
                </div>
                <button @click="activeModal = 'email_password'" class="px-3 py-1.5 bg-white border border-gray-200 rounded-md text-[13px] font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                    Manage
                </button>
            </div>

            <!-- ── Passkeys ────────────────────────────────────────────────── -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center border border-purple-200 shrink-0">
                            <Key class="w-5 h-5 text-purple-600" />
                        </div>
                        <div>
                            <h3 class="text-[14px] font-semibold text-gray-900">Passkeys</h3>
                            <p class="text-[13px] text-gray-500">Allow users to sign in with a passkey. Passkeys are faster, easier, and more secure than passwords.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 mt-4 sm:mt-0">
                        <div v-if="methods.passkeys.enabled" class="flex items-center gap-1.5 text-emerald-600 text-[13px] font-medium">
                            <Check class="w-4 h-4" /> Enabled
                        </div>
                        <div v-else class="flex items-center gap-1.5 text-gray-500 text-[13px] font-medium">
                            <div class="w-3.5 h-3.5 border-2 border-gray-300 rounded-full"></div> Disabled
                        </div>
                    </div>
                </div>
                <button @click="activeModal = 'passkeys'" class="mt-4 px-3 py-1.5 bg-white border border-gray-200 rounded-md text-[13px] font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                    Manage
                </button>
            </div>

            <!-- ── Magic Links ─────────────────────────────────────────────── -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center border border-blue-200 shrink-0">
                            <Link class="w-5 h-5 text-blue-600" />
                        </div>
                        <div>
                            <h3 class="text-[14px] font-semibold text-gray-900">Magic Auth</h3>
                            <p class="text-[13px] text-gray-500">Allow users to sign in with a unique six-digit code sent to their email address.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 mt-4 sm:mt-0">
                        <div v-if="methods.magic_links.enabled" class="flex items-center gap-1.5 text-emerald-600 text-[13px] font-medium">
                            <Check class="w-4 h-4" /> Enabled
                        </div>
                        <div v-else class="flex items-center gap-1.5 text-gray-500 text-[13px] font-medium">
                            <div class="w-3.5 h-3.5 border-2 border-gray-300 rounded-full"></div> Disabled
                        </div>
                    </div>
                </div>
                <button @click="activeModal = 'magic_links'" class="mt-4 px-3 py-1.5 bg-white border border-gray-200 rounded-md text-[13px] font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                    Manage
                </button>
            </div>


        </template>

        <!-- SSO Manage Modal -->
        <AppModal
            :show="activeModal === 'sso'"
            title="Single Sign-On"
            description="Allow users to sign in with SAML and OIDC identity providers."
            size="md"
            @close="activeModal = null"
        >
            <div class="space-y-6 text-[13px]">
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <span class="font-medium text-gray-900 text-base">Enable</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="methods.sso.enabled" class="sr-only peer" />
                        <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border after:border-gray-300 after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>
                
                <div class="space-y-3">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" v-model="ssoConfig.auto_provision" class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                        <div>
                            <span class="block font-medium text-gray-900">Auto-provision users</span>
                            <span class="block text-gray-500 mt-0.5">Automatically create an account when a user signs in via SSO.</span>
                        </div>
                    </label>
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" v-model="ssoConfig.enforce_sso" class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                        <div>
                            <span class="block font-medium text-gray-900">Enforce SSO</span>
                            <span class="block text-gray-500 mt-0.5">Require all matching domains to authenticate via SSO.</span>
                        </div>
                    </label>
                </div>
            </div>
            <template #footer>
                <button @click="activeModal = null" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-[13px] font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <button @click="saveSsoSettings" :disabled="saving === 'sso'" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-[13px] font-medium hover:bg-indigo-700 flex items-center gap-2 disabled:opacity-60">
                    <Loader2 v-if="saving === 'sso'" class="w-4 h-4 animate-spin" />
                    Save changes
                </button>
            </template>
        </AppModal>

        <!-- Email + Password Modal -->
        <AppModal
            :show="activeModal === 'email_password'"
            title="Email + Password"
            description="Users can sign in with email and password."
            size="md"
            @close="activeModal = null"
        >
            <div class="space-y-5 text-[13px]">
                <!-- Enable toggle — large pill matching reference -->
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="methods.email_password.enabled" class="sr-only peer" />
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-5 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                    <span class="font-medium text-gray-900 text-[14px]">Enable</span>
                </div>

                <hr class="border-gray-200" />

                <!-- Password policy -->
                <div>
                    <h4 class="font-semibold text-gray-900 text-[14px] mb-1">Password policy</h4>
                    <p class="text-gray-500 text-[13px] mb-4">Rules that will be enforced for passwords on user sign up.</p>

                    <!-- Strong / Custom selector cards -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div @click="applyStrongPreset"
                            :class="['border rounded-lg p-3.5 cursor-pointer transition-all', isStrongPreset ? 'border-2 border-indigo-600 bg-white' : 'border-gray-200 hover:border-gray-300']">
                            <div :class="['font-semibold text-[13px]', isStrongPreset ? 'text-indigo-700' : 'text-gray-900']">Strong</div>
                            <div :class="['text-[12px] mt-0.5', isStrongPreset ? 'text-indigo-500' : 'text-gray-500']">Recommended for most apps</div>
                        </div>
                        <div @click="() => { methods.email_password.require_uppercase = false; methods.email_password.require_lowercase = false; methods.email_password.require_number = false; methods.email_password.require_special = false; password.reject_breached = false; }"
                            :class="['border rounded-lg p-3.5 cursor-pointer transition-all', !isStrongPreset ? 'border-2 border-indigo-600 bg-white' : 'border-gray-200 hover:border-gray-300']">
                            <div :class="['font-semibold text-[13px]', !isStrongPreset ? 'text-indigo-700' : 'text-gray-900']">Custom</div>
                            <div :class="['text-[12px] mt-0.5', !isStrongPreset ? 'text-indigo-500' : 'text-gray-500']">Full control over every rule</div>
                        </div>
                    </div>

                    <!-- STRONG: summary bullet list (exactly like reference) -->
                    <div v-if="isStrongPreset" class="border border-gray-200 rounded-lg divide-y divide-gray-100 overflow-hidden">
                        <div class="flex items-center gap-3 px-4 py-3">
                            <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span class="text-[13px] text-gray-700">Minimum password length of {{ methods.email_password.min_length }} characters</span>
                        </div>
                        <div class="flex items-center gap-3 px-4 py-3">
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <span class="text-[13px] text-gray-700">Reject passwords with low complexity (<a href="https://github.com/dropbox/zxcvbn" target="_blank" class="text-indigo-600 hover:underline">zxcvbn</a> score less than {{ methods.email_password.complexity }}).</span>
                        </div>
                        <div class="flex items-center gap-3 px-4 py-3">
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span class="text-[13px] text-gray-700">Reject breached passwords, sourced by <a href="https://haveibeenpwned.com" target="_blank" class="text-indigo-600 hover:underline">haveibeenpwned</a>.</span>
                        </div>
                    </div>

                    <!-- CUSTOM: full editable form -->
                    <div v-else class="space-y-4 border border-gray-200 rounded-lg p-4">
                        <div>
                            <label class="block font-medium text-gray-900 mb-2 text-[13px]">Minimum password length</label>
                            <div class="flex items-center gap-2">
                                <input type="number" v-model="methods.email_password.min_length" class="w-16 px-3 py-1.5 border border-gray-300 rounded-md text-[13px] focus:ring-1 focus:ring-indigo-500" min="6" max="128" />
                                <span class="text-gray-600">characters</span>
                            </div>
                        </div>

                        <div>
                            <label class="block font-medium text-gray-900 mb-2 text-[13px]">Minimum complexity <span class="font-normal text-gray-500">(zxcvbn)</span></label>
                            <div class="flex items-center gap-2 mb-1.5">
                                <button v-for="n in 4" :key="n" @click="methods.email_password.complexity = n"
                                    :class="['w-8 h-8 rounded border text-[13px] font-medium flex items-center justify-center transition-colors', methods.email_password.complexity === n ? 'border-indigo-600 text-indigo-600 bg-indigo-50' : 'border-gray-300 text-gray-700 hover:bg-gray-50']">
                                    {{ n }}
                                </button>
                                <span :class="['text-[11px] font-semibold px-1.5 py-0.5 rounded border ml-1', complexityColor, complexityBg]">{{ complexityLabel }}</span>
                            </div>
                        </div>

                        <div>
                            <label class="block font-medium text-gray-900 mb-2 text-[13px]">Password rules</label>
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-gray-600 cursor-pointer"><input type="checkbox" v-model="methods.email_password.require_uppercase" class="rounded border-gray-300 text-indigo-600" /> Uppercase letter</label>
                                <label class="flex items-center gap-2 text-gray-600 cursor-pointer"><input type="checkbox" v-model="methods.email_password.require_lowercase" class="rounded border-gray-300 text-indigo-600" /> Lowercase letter</label>
                                <label class="flex items-center gap-2 text-gray-600 cursor-pointer"><input type="checkbox" v-model="methods.email_password.require_number" class="rounded border-gray-300 text-indigo-600" /> Number</label>
                                <label class="flex items-center gap-2 text-gray-600 cursor-pointer"><input type="checkbox" v-model="methods.email_password.require_special" class="rounded border-gray-300 text-indigo-600" /> Special character</label>
                            </div>
                        </div>

                        <label class="flex items-start gap-2 cursor-pointer">
                            <input type="checkbox" v-model="password.reject_breached" class="mt-0.5 rounded border-gray-300 text-indigo-600" />
                            <span class="text-[13px] text-gray-700">Reject breached passwords (<a href="https://haveibeenpwned.com" target="_blank" class="text-indigo-600 hover:underline">haveibeenpwned</a>)</span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer flex-wrap">
                            <input type="checkbox" v-model="historyEnabled" class="rounded border-gray-300 text-indigo-600" />
                            <span class="text-[13px] text-gray-700">Reject last</span>
                            <input type="number" v-model="password.history_count" :disabled="!historyEnabled" class="w-12 px-2 py-1 border border-gray-300 rounded text-center text-[13px] disabled:opacity-50" min="1" max="20" />
                            <span class="text-[13px] text-gray-700">previously used passwords</span>
                        </label>
                    </div>
                </div>
            </div>
            <template #footer>
                <button @click="activeModal = null" class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-[13px] font-medium text-gray-700 hover:bg-gray-50 transition-colors">Cancel</button>
                <button @click="saveEmailPasswordSettings" :disabled="saving === 'email_password'" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-[13px] font-semibold hover:bg-indigo-700 flex items-center gap-2 disabled:opacity-60 transition-colors">
                    <Loader2 v-if="saving === 'email_password'" class="w-4 h-4 animate-spin" />
                    Save changes
                </button>
            </template>
        </AppModal>


        <!-- Passkeys Modal -->
        <AppModal
            :show="activeModal === 'passkeys'"
            title="Passkeys"
            description="Users can sign in with passkeys. Passkeys are faster, easier, and more secure than passwords."
            size="md"
            @close="activeModal = null"
        >
            <div class="space-y-6 text-[13px]">
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <span class="font-medium text-gray-900 text-[14px]">Enable</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="methods.passkeys.enabled" class="sr-only peer" />
                        <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border after:border-gray-300 after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>
                
                <div>
                    <h4 class="font-semibold text-gray-900 text-[14px] mb-3">Options</h4>
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" checked />
                        <div>
                            <span class="block font-medium text-gray-900">Prompt existing users to add a passkey</span>
                            <span class="block text-gray-500 mt-0.5 leading-relaxed">Ask users with a password to create a passkey on their next sign-in. The notice can be dismissed permanently or will show every <strong>2 weeks</strong>.</span>
                        </div>
                    </label>
                </div>
            </div>
            <template #footer>
                <button @click="activeModal = null" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-[13px] font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <button @click="savePasskeysSettings" :disabled="saving === 'passkeys'" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-[13px] font-medium hover:bg-indigo-700 flex items-center gap-2 disabled:opacity-60">
                    <Loader2 v-if="saving === 'passkeys'" class="w-4 h-4 animate-spin" />
                    Save changes
                </button>
            </template>
        </AppModal>

        <!-- Magic Links Modal -->
        <AppModal
            :show="activeModal === 'magic_links'"
            title="Magic Auth"
            description="Allow users to sign in with a unique six-digit code sent to their email address."
            size="md"
            @close="activeModal = null"
        >
            <div class="space-y-6 text-[13px]">
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <span class="font-medium text-gray-900 text-[14px]">Enable</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="methods.magic_links.enabled" class="sr-only peer" />
                        <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border after:border-gray-300 after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>

                <div>
                    <h4 class="font-semibold text-gray-900 text-[14px] mb-3">Code settings</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between py-2 border border-gray-100 rounded-lg px-3">
                            <div>
                                <span class="block font-medium text-gray-900">Six-digit code</span>
                                <span class="block text-gray-500 text-[12px]">Send a one-time code via email for passwordless login.</span>
                            </div>
                            <span class="text-[11px] font-semibold text-emerald-600 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">Active</span>
                        </div>
                    </div>
                </div>
            </div>
            <template #footer>
                <button @click="activeModal = null" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-[13px] font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <button @click="saveMagicLinksSettings" :disabled="saving === 'magic_links'" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-[13px] font-medium hover:bg-indigo-700 flex items-center gap-2 disabled:opacity-60">
                    <Loader2 v-if="saving === 'magic_links'" class="w-4 h-4 animate-spin" />
                    Save changes
                </button>
            </template>
        </AppModal>

        
    </div>
</template>