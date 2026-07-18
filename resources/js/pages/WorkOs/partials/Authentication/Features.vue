<script setup lang="ts">
import axios from "axios";
import {
	AlertCircle,
	Check,
	Globe,
	Loader2,
	Mail,
	MessageSquare,
	Monitor,
	Shield,
	UserCog,
	UserPlus,
} from "lucide-vue-next";
import { computed, onMounted, ref } from "vue";
import AppModal from "../../components/ui/AppModal.vue";

// ─────────────────────────────────────────────────────────────────────────────
// State
// ─────────────────────────────────────────────────────────────────────────────
const isLoading = ref(true);
const saving = ref<string | null>(null);
const activeModal = ref<string | null>(null);
const toast = ref<{ type: "success" | "error"; message: string } | null>(null);

// Feature data from backend
const features = ref<any>(null);

// Local edit copies (for modals)
const hostedUiEdit = ref({ enabled: true, idp_sso: true });
const signUpEdit = ref({ enabled: true });
const invitationsEdit = ref({ enabled: true, expiry_days: 7 });
const mfaEdit = ref({ mode: "off" });
const localizationEdit = ref({ enabled: true, fallback_language: "en-US" });
const impersonationEdit = ref({ enabled: false });

// Localization language search
const langSearch = ref("");
const langDropdownOpen = ref(false);

const LANGUAGES = [
	{ code: "nl", label: "Dutch" },
	{ code: "en-US", label: "English (US)" },
	{ code: "fr", label: "French" },
	{ code: "de", label: "German" },
	{ code: "it", label: "Italian" },
	{ code: "ja", label: "Japanese" },
	{ code: "pt-BR", label: "Portuguese (Brazil)" },
	{ code: "es", label: "Spanish" },
	{ code: "sv", label: "Swedish" },
	{ code: "id", label: "Indonesian" },
];

const filteredLanguages = computed(() => {
	if (!langSearch.value) return LANGUAGES;
	return LANGUAGES.filter((l) =>
		l.label.toLowerCase().includes(langSearch.value.toLowerCase()),
	);
});

const selectedLangLabel = computed(() => {
	return (
		LANGUAGES.find((l) => l.code === localizationEdit.value.fallback_language)
			?.label ?? "English (US)"
	);
});

// ─────────────────────────────────────────────────────────────────────────────
// Load
// ─────────────────────────────────────────────────────────────────────────────
const fetchFeatures = async () => {
	isLoading.value = true;
	try {
		const { data } = await axios.get("/workos/auth-platform/features");
		features.value = data.features;
		// Sync local edit state
		hostedUiEdit.value = { ...data.features.hosted_ui };
		signUpEdit.value = { ...data.features.sign_up };
		invitationsEdit.value = { ...data.features.invitations };
		mfaEdit.value = { ...data.features.mfa };
		localizationEdit.value = { ...data.features.localization };
		impersonationEdit.value = { ...data.features.user_impersonation };
	} catch {
		showToast("error", "Failed to load features.");
	} finally {
		isLoading.value = false;
	}
};

onMounted(fetchFeatures);

// ─────────────────────────────────────────────────────────────────────────────
// Save helpers
// ─────────────────────────────────────────────────────────────────────────────
const save = async (key: string, settingsPayload: Record<string, any>) => {
	saving.value = key;
	try {
		await axios.patch("/workos/auth-platform/features", {
			settings: settingsPayload,
		});
		// Update the live features state
		await fetchFeatures();
		showToast("success", "Saved successfully.");
		activeModal.value = null;
	} catch {
		showToast("error", "Failed to save.");
	} finally {
		saving.value = null;
	}
};

const quickToggle = async (
	featureKey: string,
	settingKey: string,
	value: boolean,
) => {
	saving.value = featureKey;
	try {
		await axios.patch("/workos/auth-platform/features", {
			key: settingKey,
			value,
		});
		await fetchFeatures();
		showToast("success", value ? "Feature enabled." : "Feature disabled.");
	} catch {
		showToast("error", "Failed to update.");
	} finally {
		saving.value = null;
	}
};

const saveHostedUi = () =>
	save("hosted_ui", {
		"feature.hosted_ui.enabled": hostedUiEdit.value.enabled,
		"feature.hosted_ui.idp_sso": hostedUiEdit.value.idp_sso,
	});

const saveSignUp = () =>
	save("sign_up", {
		"feature.sign_up.enabled": signUpEdit.value.enabled,
	});

const saveInvitations = () =>
	save("invitations", {
		"feature.invitations.enabled": invitationsEdit.value.enabled,
		"feature.invitations.expiry_days": invitationsEdit.value.expiry_days,
	});

const saveMfa = () =>
	save("mfa", {
		"feature.mfa.mode": mfaEdit.value.mode,
	});

const saveLocalization = () =>
	save("localization", {
		"feature.localization.enabled": localizationEdit.value.enabled,
		"feature.localization.fallback_language":
			localizationEdit.value.fallback_language,
	});

const saveImpersonation = () =>
	save("user_impersonation", {
		"feature.user_impersonation.enabled": impersonationEdit.value.enabled,
	});

// Open modal and sync local copy
const openModal = (key: string) => {
	if (!features.value) return;
	if (key === "hosted_ui") hostedUiEdit.value = { ...features.value.hosted_ui };
	if (key === "sign_up") signUpEdit.value = { ...features.value.sign_up };
	if (key === "invitations")
		invitationsEdit.value = { ...features.value.invitations };
	if (key === "mfa") mfaEdit.value = { ...features.value.mfa };
	if (key === "localization")
		localizationEdit.value = { ...features.value.localization };
	if (key === "user_impersonation")
		impersonationEdit.value = { ...features.value.user_impersonation };
	activeModal.value = key;
};

// Enable-only shortcut (for disabled features)
const enableFeature = async (key: string) => {
	const keyMap: Record<string, string> = {
		hosted_ui: "feature.hosted_ui.enabled",
		sign_up: "feature.sign_up.enabled",
		invitations: "feature.invitations.enabled",
		mfa: "feature.mfa.mode", // special: sets mode to "optional"
		localization: "feature.localization.enabled",
		user_impersonation: "feature.user_impersonation.enabled",
	};
	if (key === "mfa") {
		await save("mfa", { "feature.mfa.mode": "optional" });
	} else {
		await quickToggle(key, keyMap[key], true);
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

// ─────────────────────────────────────────────────────────────────────────────
// Computed
// ─────────────────────────────────────────────────────────────────────────────
const mfaEnabled = computed(() => features.value?.mfa?.mode !== "off");
const mfaStatusLabel = computed(() => {
	const m = features.value?.mfa?.mode ?? "off";
	return m === "off" ? "Disabled" : m.charAt(0).toUpperCase() + m.slice(1);
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
        <div class="border-b border-gray-200 dark:border-zinc-700 pb-5">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-zinc-100 tracking-tight">Features</h1>
            <p class="text-[14px] text-gray-500 dark:text-zinc-400 mt-1">Manage global platform features.</p>
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="space-y-4">
            <div v-for="i in 6" :key="i" class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl shadow-sm p-5 space-y-3 dark:shadow-none">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl wos-shimmer shrink-0" />
                    <div class="flex-1 space-y-2">
                        <div class="flex justify-between items-start">
                            <div class="h-4 wos-shimmer rounded w-1/4" />
                            <div class="h-4 wos-shimmer rounded w-12" />
                        </div>
                        <div class="h-3.5 wos-shimmer rounded w-full" />
                        <div class="h-3.5 wos-shimmer rounded w-5/6" />
                    </div>
                </div>
                <div class="h-8 wos-shimmer rounded w-16" />
            </div>
        </div>

        <template v-else-if="features">

            <!-- ── Hosted UI ──────────────────────────────────────────────── -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl shadow-sm p-5 dark:shadow-none">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-slate-900 text-white rounded-xl flex items-center justify-center shrink-0">
                        <Monitor class="w-5 h-5" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-[14px] font-semibold text-gray-900 dark:text-zinc-100">Hosted UI</h3>
                                <p class="text-[13px] text-gray-500 dark:text-zinc-400 mt-0.5">Users sign in to your app using a customizable hosted authentication UI.</p>
                            </div>
                            <div :class="['flex items-center gap-1 text-[13px] font-medium shrink-0', features.hosted_ui.enabled ? 'text-emerald-600' : 'text-gray-400 dark:text-zinc-500']">
                                <Check v-if="features.hosted_ui.enabled" class="w-3.5 h-3.5" />
                                {{ features.hosted_ui.enabled ? 'Enabled' : 'Disabled' }}
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 mt-3">
                            <button @click="openModal('hosted_ui')" class="px-3 py-1.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-md text-[13px] font-medium text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors shadow-sm dark:shadow-none">Manage</button>
                            <div v-if="features.hosted_ui.authkit_url || features.hosted_ui.redirect_uri" class="flex items-center gap-1.5 text-[12px] text-gray-500 dark:text-zinc-400 border border-gray-200 dark:border-zinc-700 rounded-md px-2.5 py-1.5 bg-gray-50 dark:bg-zinc-900">
                                <span class="truncate max-w-[200px]">{{ features.hosted_ui.authkit_url || features.hosted_ui.redirect_uri }}</span>
                                <span class="text-[10px] font-semibold text-gray-600 dark:text-zinc-400 bg-gray-200 px-1.5 py-0.5 rounded shrink-0">Staging</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Sign-up ────────────────────────────────────────────────── -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl shadow-sm p-5 dark:shadow-none">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-blue-100 text-blue-500 rounded-xl flex items-center justify-center shrink-0 border border-blue-200">
                        <UserPlus class="w-5 h-5" />
                    </div>
                    <div class="flex-1">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-[14px] font-semibold text-gray-900 dark:text-zinc-100">Sign-up</h3>
                                <p class="text-[13px] text-gray-500 dark:text-zinc-400 mt-0.5">Allow users to sign up for your app and create their own user accounts.</p>
                            </div>
                            <div :class="['flex items-center gap-1 text-[13px] font-medium shrink-0', features.sign_up.enabled ? 'text-emerald-600' : 'text-gray-400 dark:text-zinc-500']">
                                <Check v-if="features.sign_up.enabled" class="w-3.5 h-3.5" />
                                {{ features.sign_up.enabled ? 'Enabled' : 'Disabled' }}
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-3">
                            <button v-if="features.sign_up.enabled" @click="openModal('sign_up')" class="px-3 py-1.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-md text-[13px] font-medium text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors shadow-sm dark:shadow-none">Manage</button>
                            <button v-else @click="enableFeature('sign_up')" :disabled="saving === 'sign_up'" class="px-3 py-1.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-md text-[13px] font-medium text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors shadow-sm flex items-center gap-1.5 dark:shadow-none">
                                <Loader2 v-if="saving === 'sign_up'" class="w-3.5 h-3.5 animate-spin" />
                                Enable
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Invitations ─────────────────────────────────────────────── -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl shadow-sm p-5 dark:shadow-none">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center shrink-0 border border-emerald-200">
                        <Mail class="w-5 h-5" />
                    </div>
                    <div class="flex-1">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-[14px] font-semibold text-gray-900 dark:text-zinc-100">Invitations</h3>
                                <p class="text-[13px] text-gray-500 dark:text-zinc-400 mt-0.5">Configure the expiration period for user invitations to join an organization.</p>
                            </div>
                        </div>
                        <!-- Detail row -->
                        <div class="flex items-center gap-6 mt-3 mb-3">
                            <div class="text-[13px] text-gray-500 dark:text-zinc-400">Default invitation expiry</div>
                            <div class="text-[13px] text-gray-900 dark:text-zinc-100 font-medium">{{ features.invitations.expiry_days }} days</div>
                        </div>
                        <button @click="openModal('invitations')" class="px-3 py-1.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-md text-[13px] font-medium text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors shadow-sm dark:shadow-none">Manage</button>
                    </div>
                </div>
            </div>

            <!-- ── Multi-factor auth ──────────────────────────────────────── -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl shadow-sm p-5 dark:shadow-none">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center shrink-0 border border-purple-200">
                        <MessageSquare class="w-5 h-5" />
                    </div>
                    <div class="flex-1">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-[14px] font-semibold text-gray-900 dark:text-zinc-100">Multi-factor auth</h3>
                                <p class="text-[13px] text-gray-500 dark:text-zinc-400 mt-0.5">Require non-SSO users to set up multi-factor authentication to sign in.</p>
                            </div>
                            <div :class="['flex items-center gap-1 text-[13px] font-medium shrink-0', mfaEnabled ? 'text-emerald-600' : 'text-gray-400 dark:text-zinc-500']">
                                <Check v-if="mfaEnabled" class="w-3.5 h-3.5" />
                                <span v-else class="w-3.5 h-3.5 border-2 border-gray-300 dark:border-zinc-700 rounded-full inline-block"></span>
                                {{ mfaStatusLabel }}
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-3">
                            <button v-if="mfaEnabled" @click="openModal('mfa')" class="px-3 py-1.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-md text-[13px] font-medium text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors shadow-sm dark:shadow-none">Manage</button>
                            <button v-else @click="openModal('mfa')" class="px-3 py-1.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-md text-[13px] font-medium text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors shadow-sm dark:shadow-none">Enable</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Localization ─────────────────────────────────────────────── -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl shadow-sm p-5 dark:shadow-none">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-sky-100 text-sky-600 rounded-xl flex items-center justify-center shrink-0 border border-sky-200">
                        <Globe class="w-5 h-5" />
                    </div>
                    <div class="flex-1">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-[14px] font-semibold text-gray-900 dark:text-zinc-100">Localization</h3>
                                <p class="text-[13px] text-gray-500 dark:text-zinc-400 mt-0.5">Automatically display AuthKit's hosted UI and authentication emails in the user's browser language.</p>
                            </div>
                            <div :class="['flex items-center gap-1 text-[13px] font-medium shrink-0', features.localization.enabled ? 'text-emerald-600' : 'text-gray-400 dark:text-zinc-500']">
                                <Check v-if="features.localization.enabled" class="w-3.5 h-3.5" />
                                {{ features.localization.enabled ? 'Enabled' : 'Disabled' }}
                            </div>
                        </div>
                        <div class="flex items-center gap-6 mt-3 mb-3" v-if="features.localization.enabled">
                            <div class="text-[13px] text-gray-500 dark:text-zinc-400">Fallback language</div>
                            <div class="text-[13px] text-gray-900 dark:text-zinc-100 font-medium">{{ selectedLangLabel }}</div>
                        </div>
                        <div class="flex items-center gap-2 mt-3">
                            <button v-if="features.localization.enabled" @click="openModal('localization')" class="px-3 py-1.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-md text-[13px] font-medium text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors shadow-sm dark:shadow-none">Manage</button>
                            <button v-else @click="enableFeature('localization')" :disabled="saving === 'localization'" class="px-3 py-1.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-md text-[13px] font-medium text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors shadow-sm flex items-center gap-1.5 dark:shadow-none">
                                <Loader2 v-if="saving === 'localization'" class="w-3.5 h-3.5 animate-spin" />
                                Enable
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── User Impersonation ──────────────────────────────────────── -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl shadow-sm p-5 dark:shadow-none">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-orange-100 text-orange-600 rounded-xl flex items-center justify-center shrink-0 border border-orange-200">
                        <UserCog class="w-5 h-5" />
                    </div>
                    <div class="flex-1">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-[14px] font-semibold text-gray-900 dark:text-zinc-100">User impersonation</h3>
                                <p class="text-[13px] text-gray-500 dark:text-zinc-400 mt-0.5">Allow team members in the dashboard to sign in as any user of your app.</p>
                            </div>
                            <div :class="['flex items-center gap-1 text-[13px] font-medium shrink-0', features.user_impersonation.enabled ? 'text-emerald-600' : 'text-gray-400 dark:text-zinc-500']">
                                <Check v-if="features.user_impersonation.enabled" class="w-3.5 h-3.5" />
                                <span v-else class="w-3.5 h-3.5 border-2 border-gray-300 dark:border-zinc-700 rounded-full inline-block"></span>
                                {{ features.user_impersonation.enabled ? 'Enabled' : 'Disabled' }}
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-3">
                            <button v-if="features.user_impersonation.enabled" @click="openModal('user_impersonation')" class="px-3 py-1.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-md text-[13px] font-medium text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors shadow-sm dark:shadow-none">Manage</button>
                            <button v-else @click="enableFeature('user_impersonation')" :disabled="saving === 'user_impersonation'" class="px-3 py-1.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-md text-[13px] font-medium text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors shadow-sm flex items-center gap-1.5 dark:shadow-none">
                                <Loader2 v-if="saving === 'user_impersonation'" class="w-3.5 h-3.5 animate-spin" />
                                Enable
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </template>

        <!-- ══════════════════════════════════════════════════════════════════ -->
        <!-- MODALS                                                            -->
        <!-- ══════════════════════════════════════════════════════════════════ -->

        <!-- Hosted UI Modal -->
        <AppModal :show="activeModal === 'hosted_ui'" title="AuthKit" size="md" @close="activeModal = null">
            <div class="space-y-5 text-[13px]">
                <!-- Enable toggle -->
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="hostedUiEdit.enabled" class="sr-only peer" />
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-5 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white dark:bg-zinc-900 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                    <span class="font-medium text-gray-900 dark:text-zinc-100 text-[14px]">Enable</span>
                </div>
                <p class="text-gray-600 dark:text-zinc-400 text-[13px]">Users will sign in and sign up using the <span class="text-blue-600 font-medium">AuthKit</span> hosted UI.</p>

                <hr class="border-gray-200 dark:border-zinc-700" />

                <!-- Identity provider SSO -->
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-zinc-100 text-[14px] mb-3">Identity provider-initiated SSO</h4>
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" v-model="hostedUiEdit.idp_sso" class="mt-1 rounded border-gray-300 dark:border-zinc-700 text-blue-600 focus:ring-[#2563eb]" />
                        <span class="text-gray-700 dark:text-zinc-300 leading-relaxed">
                            Use AuthKit to handle requests coming from IdPs, and complete authentication flows like email verification before going back to the default <span class="text-blue-600 font-medium">Redirect URI</span>.
                        </span>
                    </label>
                    <div class="mt-3 bg-gray-50 dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-lg p-3 text-gray-600 dark:text-zinc-400 text-[12.5px] flex gap-2">
                        <Shield class="w-4 h-4 mt-0.5 text-gray-400 dark:text-zinc-500 shrink-0" />
                        <p>Applications with an existing WorkOS SSO integration may want to keep this option disabled until ready to fully migrate to AuthKit.</p>
                    </div>
                </div>
            </div>
            <template #footer>
                <button @click="activeModal = null" class="px-4 py-2 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-lg text-[13px] font-medium text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800">Cancel</button>
                <button @click="saveHostedUi" :disabled="saving === 'hosted_ui'" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-[13px] font-semibold hover:bg-blue-700 flex items-center gap-2 disabled:opacity-60">
                    <Loader2 v-if="saving === 'hosted_ui'" class="w-4 h-4 animate-spin" />
                    Save changes
                </button>
            </template>
        </AppModal>

        <!-- Sign-up Modal -->
        <AppModal :show="activeModal === 'sign_up'" title="Sign-up" description="Allow users to sign up for your app and create their own user accounts." size="md" @close="activeModal = null">
            <div class="space-y-4 text-[13px]">
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="signUpEdit.enabled" class="sr-only peer" />
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-5 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white dark:bg-zinc-900 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                    <span class="font-medium text-gray-900 dark:text-zinc-100 text-[14px]">Enable</span>
                </div>
                <div v-if="!signUpEdit.enabled" class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-amber-800 text-[12.5px]">
                    Disabling sign-up will prevent new users from creating accounts. Existing users will not be affected.
                </div>
            </div>
            <template #footer>
                <button @click="activeModal = null" class="px-4 py-2 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-lg text-[13px] font-medium text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800">Cancel</button>
                <button @click="saveSignUp" :disabled="saving === 'sign_up'" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-[13px] font-semibold hover:bg-blue-700 flex items-center gap-2 disabled:opacity-60">
                    <Loader2 v-if="saving === 'sign_up'" class="w-4 h-4 animate-spin" />
                    Save changes
                </button>
            </template>
        </AppModal>

        <!-- Invitations Modal -->
        <AppModal :show="activeModal === 'invitations'" title="Invitations" description="Configure the expiration period for user invitations to join an organization (maximum of 30 days)." size="md" @close="activeModal = null">
            <div class="space-y-4 text-[13px]">
                <div>
                    <label class="block font-medium text-gray-900 dark:text-zinc-100 mb-2">Default invitation expiry</label>
                    <div class="flex items-center gap-2">
                        <input
                            type="number"
                            v-model="invitationsEdit.expiry_days"
                            min="1"
                            max="30"
                            class="w-20 px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-[13px] focus:ring-1 focus:ring-[#2563eb] focus:border-[#2563eb]"
                        />
                        <span class="text-gray-600 dark:text-zinc-400">days</span>
                    </div>
                    <p class="text-gray-500 dark:text-zinc-400 text-[12px] mt-1.5">Maximum is 30 days. After expiry, invitations must be resent.</p>
                </div>
            </div>
            <template #footer>
                <button @click="activeModal = null" class="px-4 py-2 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-lg text-[13px] font-medium text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800">Cancel</button>
                <button @click="saveInvitations" :disabled="saving === 'invitations'" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-[13px] font-semibold hover:bg-blue-700 flex items-center gap-2 disabled:opacity-60">
                    <Loader2 v-if="saving === 'invitations'" class="w-4 h-4 animate-spin" />
                    Save changes
                </button>
            </template>
        </AppModal>

        <!-- MFA Modal -->
        <AppModal :show="activeModal === 'mfa'" title="Multi-Factor Auth" size="md" @close="activeModal = null">
            <div class="space-y-5 text-[13px]">
                <p class="text-gray-600 dark:text-zinc-400 leading-relaxed">
                    Multi-factor authentication (MFA) requires the user to have an authenticator app that supports one-time passcodes. When a user is enrolled in MFA, they must successfully complete an MFA challenge during sign in. Does <strong>not</strong> apply to SSO users.
                </p>

                <div class="space-y-4">
                    <label class="flex items-start gap-3 cursor-pointer group">
                        <input type="radio" v-model="mfaEdit.mode" value="off" class="mt-1 border-gray-300 dark:border-zinc-700 text-blue-600 focus:ring-[#2563eb] shrink-0" />
                        <div>
                            <span class="block font-medium text-gray-900 dark:text-zinc-100">Off</span>
                            <span class="block text-gray-500 dark:text-zinc-400 mt-0.5">MFA is disabled for all users.</span>
                        </div>
                    </label>
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="radio" v-model="mfaEdit.mode" value="optional" class="mt-1 border-gray-300 dark:border-zinc-700 text-blue-600 focus:ring-[#2563eb] shrink-0" />
                        <div>
                            <span class="block font-medium text-gray-900 dark:text-zinc-100">Optional</span>
                            <span class="block text-gray-500 dark:text-zinc-400 mt-0.5">Users may optionally enroll in MFA and enforcement is configured by their organization membership.</span>
                        </div>
                    </label>
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="radio" v-model="mfaEdit.mode" value="required" class="mt-1 border-gray-300 dark:border-zinc-700 text-blue-600 focus:ring-[#2563eb] shrink-0" />
                        <div>
                            <span class="block font-medium text-gray-900 dark:text-zinc-100">Required</span>
                            <span class="block text-gray-500 dark:text-zinc-400 mt-0.5">All users must enroll in MFA and are challenged each time they sign in.</span>
                        </div>
                    </label>
                </div>
            </div>
            <template #footer>
                <button @click="activeModal = null" class="px-4 py-2 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-lg text-[13px] font-medium text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800">Cancel</button>
                <button @click="saveMfa" :disabled="saving === 'mfa'" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-[13px] font-semibold hover:bg-blue-700 flex items-center gap-2 disabled:opacity-60">
                    <Loader2 v-if="saving === 'mfa'" class="w-4 h-4 animate-spin" />
                    Save changes
                </button>
            </template>
        </AppModal>

        <!-- Localization Modal -->
        <AppModal :show="activeModal === 'localization'" title="Localization" size="md" @close="activeModal = null; langDropdownOpen = false">
            <div class="space-y-5 text-[13px]">
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="localizationEdit.enabled" class="sr-only peer" />
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-5 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white dark:bg-zinc-900 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                    <span class="font-medium text-gray-900 dark:text-zinc-100 text-[14px]">Enable</span>
                </div>
                <p class="text-gray-600 dark:text-zinc-400 text-[13px] -mt-2">Automatically display AuthKit's hosted UI and authentication emails sent by WorkOS in the user's browser language.</p>

                <hr class="border-gray-200 dark:border-zinc-700" />

                <!-- Fallback language searchable dropdown -->
                <div>
                    <label class="block font-medium text-gray-900 dark:text-zinc-100 mb-2">Fallback language</label>
                    <div class="relative">
                        <button
                            @click="langDropdownOpen = !langDropdownOpen"
                            class="w-full flex items-center justify-between px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-[13px] bg-white dark:bg-zinc-900 hover:border-gray-400 focus:outline-none focus:ring-1 focus:ring-[#2563eb]"
                        >
                            <span>{{ LANGUAGES.find(l => l.code === localizationEdit.fallback_language)?.label ?? 'English (US)' }}</span>
                            <svg class="w-4 h-4 text-gray-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div v-if="langDropdownOpen" class="absolute z-50 w-full mt-1 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-lg shadow-lg overflow-hidden dark:shadow-none">
                            <div class="p-2 border-b border-gray-100 dark:border-zinc-800">
                                <input v-model="langSearch" type="text" placeholder="Search languages" class="w-full px-3 py-1.5 text-[13px] border border-gray-200 dark:border-zinc-700 rounded-md focus:outline-none focus:ring-1 focus:ring-[#2563eb]" autofocus />
                            </div>
                            <div class="max-h-48 overflow-y-auto">
                                <div v-if="!langSearch && filteredLanguages.length > 0" class="px-3 py-1.5 text-[11px] font-semibold text-gray-400 dark:text-zinc-500 uppercase tracking-wide">Common languages</div>
                                <button
                                    v-for="lang in filteredLanguages"
                                    :key="lang.code"
                                    @click="localizationEdit.fallback_language = lang.code; langDropdownOpen = false; langSearch = ''"
                                    class="w-full flex items-center justify-between px-3 py-2 text-[13px] text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 dark:bg-zinc-900 transition-colors"
                                >
                                    <span>{{ lang.label }}</span>
                                    <Check v-if="lang.code === localizationEdit.fallback_language" class="w-4 h-4 text-blue-600" />
                                </button>
                                <div v-if="filteredLanguages.length === 0" class="px-3 py-3 text-[13px] text-gray-500 dark:text-zinc-400 text-center">No languages found</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <template #footer>
                <button @click="activeModal = null; langDropdownOpen = false" class="px-4 py-2 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-lg text-[13px] font-medium text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800">Cancel</button>
                <button @click="saveLocalization" :disabled="saving === 'localization'" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-[13px] font-semibold hover:bg-blue-700 flex items-center gap-2 disabled:opacity-60">
                    <Loader2 v-if="saving === 'localization'" class="w-4 h-4 animate-spin" />
                    Save changes
                </button>
            </template>
        </AppModal>

        <!-- User Impersonation Modal -->
        <AppModal :show="activeModal === 'user_impersonation'" title="User Impersonation" description="Allow team members in the dashboard to sign in as any user of your app." size="md" @close="activeModal = null">
            <div class="space-y-4 text-[13px]">
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="impersonationEdit.enabled" class="sr-only peer" />
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-5 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white dark:bg-zinc-900 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                    <span class="font-medium text-gray-900 dark:text-zinc-100 text-[14px]">Enable</span>
                </div>
                <div v-if="impersonationEdit.enabled" class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-amber-800 text-[12.5px] flex gap-2">
                    <AlertCircle class="w-4 h-4 shrink-0 mt-0.5" />
                    <p>User impersonation grants admin users full access to impersonate any user. Only enable if you trust all dashboard members.</p>
                </div>
            </div>
            <template #footer>
                <button @click="activeModal = null" class="px-4 py-2 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-lg text-[13px] font-medium text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800">Cancel</button>
                <button @click="saveImpersonation" :disabled="saving === 'user_impersonation'" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-[13px] font-semibold hover:bg-blue-700 flex items-center gap-2 disabled:opacity-60">
                    <Loader2 v-if="saving === 'user_impersonation'" class="w-4 h-4 animate-spin" />
                    Save changes
                </button>
            </template>
        </AppModal>

    </div>
</template>
