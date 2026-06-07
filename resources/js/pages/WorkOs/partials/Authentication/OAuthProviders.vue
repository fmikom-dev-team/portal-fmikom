<script setup lang="ts">
import axios from "axios";
import { AlertCircle, Check, Loader2, ShieldCheck } from "lucide-vue-next";
import { computed, onMounted, ref } from "vue";

// ─────────────────────────────────────────────────────────────────────────────
// Provider Icons (real SVG brand logos)
// ─────────────────────────────────────────────────────────────────────────────
const PROVIDER_META: Record<
	string,
	{ color: string; abbr: string; svg: string }
> = {
	google: {
		color: "#4285F4",
		abbr: "GO",
		svg: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>`,
	},
	microsoft: {
		color: "#0078D4",
		abbr: "MS",
		svg: `<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M11.4 24H0V12.6h11.4V24z" fill="#F25022"/><path d="M24 24H12.6V12.6H24V24z" fill="#00A4EF"/><path d="M11.4 11.4H0V0h11.4v11.4z" fill="#7FBA00"/><path d="M24 11.4H12.6V0H24v11.4z" fill="#FFB900"/></svg>`,
	},
	github: {
		color: "#24292F",
		abbr: "GH",
		svg: `<svg viewBox="0 0 24 24" fill="#24292F" xmlns="http://www.w3.org/2000/svg"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>`,
	},
	apple: {
		color: "#000000",
		abbr: "AP",
		svg: `<svg viewBox="0 0 24 24" fill="#000000" xmlns="http://www.w3.org/2000/svg"><path d="M12.152 6.896c-.948 0-2.415-1.078-3.96-1.04-2.04.027-3.91 1.183-4.961 3.014-2.117 3.675-.546 9.103 1.519 12.09 1.013 1.454 2.208 3.09 3.792 3.039 1.52-.065 2.09-.987 3.935-.987 1.831 0 2.35.987 3.96.948 1.637-.026 2.676-1.48 3.676-2.948 1.156-1.688 1.636-3.325 1.662-3.415-.039-.013-3.182-1.221-3.22-4.857-.026-3.04 2.48-4.494 2.597-4.559-1.429-2.09-3.623-2.324-4.39-2.376-2-.156-3.675 1.09-4.61 1.09zM15.53 3.83c.843-1.012 1.4-2.427 1.245-3.83-1.207.052-2.662.805-3.532 1.818-.78.896-1.454 2.338-1.273 3.714 1.338.104 2.715-.688 3.559-1.701z"/></svg>`,
	},
	gitlab: {
		color: "#FC6D26",
		abbr: "GL",
		svg: `<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M4.845.904a.96.96 0 00-.898.648L.05 13.144a1.434 1.434 0 00.52 1.603l11.43 8.303 11.43-8.303a1.434 1.434 0 00.52-1.603L19.955 1.552a.96.96 0 00-1.817 0l-2.853 8.765H8.715L5.862 1.552a.96.96 0 00-.92-.648h-.097z" fill="#FC6D26"/></svg>`,
	},
	linkedin: {
		color: "#0A66C2",
		abbr: "LI",
		svg: `<svg viewBox="0 0 24 24" fill="#0A66C2" xmlns="http://www.w3.org/2000/svg"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>`,
	},
	salesforce: {
		color: "#00A1E0",
		abbr: "SF",
		svg: `<svg viewBox="0 0 24 24" fill="#00A1E0" xmlns="http://www.w3.org/2000/svg"><path d="M9.975 9.93a4.02 4.02 0 011.424-1.071 4.083 4.083 0 011.74-.38c.87 0 1.66.222 2.34.647a4.435 4.435 0 011.624 1.763 5.32 5.32 0 011.908-.354c1.488 0 2.754.527 3.797 1.58C23.852 13.169 24 14.44 24 15.84a5.28 5.28 0 01-1.567 3.76A5.263 5.263 0 0118.67 21.1H5.56a4.625 4.625 0 01-3.383-1.404A4.619 4.619 0 010 16.305a4.617 4.617 0 011.197-3.09 4.607 4.607 0 012.945-1.508 4.11 4.11 0 01-.164-1.161 4.133 4.133 0 011.216-2.942 4.123 4.123 0 012.936-1.22c.734 0 1.42.187 2.022.534-.057.326-.1.662-.177.012z"/></svg>`,
	},
	slack: {
		color: "#4A154B",
		abbr: "SL",
		svg: `<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M5.042 15.165a2.528 2.528 0 01-2.52 2.523A2.528 2.528 0 010 15.165a2.527 2.527 0 012.522-2.52h2.52v2.52zm1.271 0a2.527 2.527 0 012.521-2.52 2.527 2.527 0 012.521 2.52v6.313A2.528 2.528 0 018.834 24a2.528 2.528 0 01-2.521-2.522v-6.313zM8.834 5.042a2.528 2.528 0 01-2.521-2.52A2.528 2.528 0 018.834 0a2.528 2.528 0 012.521 2.522v2.52H8.834zm0 1.271a2.528 2.528 0 012.521 2.521 2.528 2.528 0 01-2.521 2.521H2.522A2.528 2.528 0 010 8.834a2.528 2.528 0 012.522-2.521h6.312zm10.122 2.521a2.528 2.528 0 012.522-2.521A2.528 2.528 0 0124 8.834a2.528 2.528 0 01-2.522 2.521h-2.522V8.834zm-1.268 0a2.528 2.528 0 01-2.523 2.521 2.527 2.527 0 01-2.52-2.521V2.522A2.527 2.527 0 0115.165 0a2.528 2.528 0 012.523 2.522v6.312zm-2.523 10.122a2.528 2.528 0 012.523 2.522A2.528 2.528 0 0115.165 24a2.527 2.527 0 01-2.52-2.522v-2.522h2.52zm0-1.268a2.527 2.527 0 01-2.52-2.523 2.526 2.526 0 012.52-2.52h6.313A2.527 2.527 0 0124 15.165a2.528 2.528 0 01-2.522 2.523h-6.313z" fill="#4A154B"/></svg>`,
	},
};

// ─────────────────────────────────────────────────────────────────────────────
// State
// ─────────────────────────────────────────────────────────────────────────────
const providers = ref<any[]>([]);
const isLoading = ref(true);
const activeProvider = ref<any>(null);
const isSaving = ref(false);
const toast = ref<{ type: "success" | "error"; message: string } | null>(null);

// Modal form state
const form = ref({
	is_enabled: false,
	use_demo: true,
	client_id: "",
	client_secret: "",
	showSecret: false,
});

// ─────────────────────────────────────────────────────────────────────────────
// Load
// ─────────────────────────────────────────────────────────────────────────────
const fetchProviders = async () => {
	isLoading.value = true;
	try {
		const res = await axios.get("/workos/auth-platform/providers");
		providers.value = res.data.providers;
	} catch {
		showToast("error", "Failed to load providers");
	} finally {
		isLoading.value = false;
	}
};

onMounted(fetchProviders);

// ─────────────────────────────────────────────────────────────────────────────
// Modal
// ─────────────────────────────────────────────────────────────────────────────
const openModal = (provider: any) => {
	activeProvider.value = provider;
	form.value = {
		is_enabled: provider.is_enabled,
		use_demo: provider.use_demo,
		client_id: provider.client_id || "",
		client_secret: "",
		showSecret: false,
	};
};

const closeModal = () => {
	activeProvider.value = null;
};

const saveProvider = async () => {
	if (!activeProvider.value) return;
	isSaving.value = true;

	const payload: any = {
		is_enabled: form.value.is_enabled,
		use_demo: form.value.use_demo,
		client_id: form.value.client_id || null,
	};

	// Only send client_secret if user typed something new
	if (form.value.client_secret) {
		payload.client_secret = form.value.client_secret;
	}

	try {
		const res = await axios.patch(
			`/workos/auth-platform/providers/${activeProvider.value.slug}`,
			payload,
		);

		// Update local list
		const idx = providers.value.findIndex(
			(p) => p.slug === activeProvider.value.slug,
		);
		if (idx !== -1) providers.value[idx] = res.data.provider;

		showToast("success", `${activeProvider.value.name} settings saved.`);
		closeModal();
	} catch (e: any) {
		showToast(
			"error",
			e.response?.data?.message || "Failed to save provider settings.",
		);
	} finally {
		isSaving.value = false;
	}
};

// Quick toggle without opening modal
const quickToggle = async (provider: any) => {
	const newState = !provider.is_enabled;
	provider.is_enabled = newState; // Optimistic UI

	try {
		await axios.patch(`/workos/auth-platform/providers/${provider.slug}`, {
			is_enabled: newState,
		});
		showToast(
			"success",
			`${provider.name} ${newState ? "enabled" : "disabled"}.`,
		);
	} catch {
		provider.is_enabled = !newState; // Rollback
		showToast("error", "Failed to update provider status.");
	}
};

// ─────────────────────────────────────────────────────────────────────────────
// Toast
// ─────────────────────────────────────────────────────────────────────────────
const showToast = (type: "success" | "error", message: string) => {
	toast.value = { type, message };
	setTimeout(() => {
		toast.value = null;
	}, 3500);
};

// ─────────────────────────────────────────────────────────────────────────────
// Computed
// ─────────────────────────────────────────────────────────────────────────────
const enabledCount = computed(
	() => providers.value.filter((p) => p.is_enabled).length,
);
const isCustomCreds = computed(() => !form.value.use_demo);
</script>

<template>
    <div class="space-y-6 animate-fade-in max-w-[800px] relative">

        <!-- Toast -->
        <Transition enter-from-class="translate-y-2 opacity-0" enter-to-class="translate-y-0 opacity-100" leave-to-class="translate-y-2 opacity-0" enter-active-class="transition duration-200" leave-active-class="transition duration-150">
            <div v-if="toast" :class="['fixed bottom-6 right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border text-[13px] font-medium', toast.type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800']">
                <Check v-if="toast.type === 'success'" class="w-4 h-4" />
                <AlertCircle v-else class="w-4 h-4" />
                {{ toast.message }}
            </div>
        </Transition>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-gray-200 pb-5">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 tracking-tight">Providers</h1>
                <p class="text-[14px] text-gray-500 mt-1">Allow users to sign in with an account from one of these providers.</p>
            </div>
            <div class="text-[13px] text-gray-500 bg-gray-50 border border-gray-200 px-3 py-1.5 rounded-md">
                <span class="font-semibold text-gray-700">{{ enabledCount }}</span> enabled
            </div>
        </div>

        <!-- Provider List -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">

            <!-- Loading -->
            <div v-if="isLoading" class="p-4 space-y-4">
                <div v-for="i in 5" :key="i" class="flex items-center justify-between py-2 border-b border-gray-100 last:border-none">
                    <div class="flex items-center gap-4 w-1/2">
                        <div class="w-10 h-10 rounded-lg wos-shimmer" />
                        <div class="h-4 wos-shimmer rounded w-2/3" />
                    </div>
                    <div class="h-8 wos-shimmer rounded w-16" />
                </div>
            </div>

            <div v-else class="divide-y divide-gray-100">
                <div v-for="p in providers" :key="p.slug"
                    class="p-4 flex items-center justify-between hover:bg-gray-50/60 transition-colors group">

                    <!-- Provider Info -->
                    <div class="flex items-center gap-4">
                        <!-- Icon -->
                        <div class="w-10 h-10 rounded-lg border border-gray-200 bg-white shadow-sm flex items-center justify-center overflow-hidden p-1.5">
                            <span v-if="PROVIDER_META[p.slug]?.svg"
                                class="w-full h-full flex items-center justify-center"
                                v-html="PROVIDER_META[p.slug].svg" />
                            <span v-else
                                class="text-[11px] font-bold"
                                :style="{ color: '#6B7280' }">
                                {{ p.name.substring(0, 2).toUpperCase() }}
                            </span>
                        </div>

                        <!-- Name & Badges -->
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-medium text-[14px] text-gray-900">{{ p.name }}</span>
                            <span v-if="p.is_enabled && p.use_demo" class="bg-yellow-100 text-yellow-700 text-[11px] font-medium px-2 py-0.5 rounded-full border border-yellow-200">
                                Demo credentials
                            </span>
                            <span v-else-if="p.is_enabled && p.has_custom" class="bg-indigo-50 text-indigo-600 text-[11px] font-medium px-2 py-0.5 rounded-full border border-indigo-100">
                                Custom credentials
                            </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-3">
                        <div v-if="p.is_enabled" class="flex items-center gap-1.5 text-emerald-600 text-[13px] font-medium">
                            <ShieldCheck class="w-4 h-4" />
                            Enabled
                        </div>

                        <button v-if="p.is_enabled"
                            @click="openModal(p)"
                            class="px-3 py-1.5 bg-gray-100 border border-transparent rounded-md text-[13px] font-medium text-gray-700 hover:bg-gray-200 transition-colors">
                            Manage
                        </button>
                        <button v-else
                            @click="openModal(p)"
                            class="px-3 py-1.5 bg-white border border-gray-200 rounded-md text-[13px] font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                            Enable
                        </button>
                    </div>
                </div>

                <!-- Footer -->
                <div class="p-5 bg-gray-50/50 flex items-center justify-between">
                    <div>
                        <h4 class="text-[13px] font-medium text-gray-900">Need a different OAuth provider?</h4>
                        <p class="text-[12px] text-gray-500">Let us know. Customer requests help shape what we add next.</p>
                    </div>
                    <button class="px-3 py-1.5 bg-white border border-gray-200 rounded-md text-[13px] font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                        Contact support
                    </button>
                </div>
            </div>
        </div>

        <!-- ─── Manage Modal ─────────────────────────────────────────────── -->
        <Teleport to="body">
            <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" leave-active-class="transition duration-150" leave-to-class="opacity-0">
                <div v-if="activeProvider" class="fixed inset-0 z-50 flex items-center justify-center p-4">

                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-black/40 backdrop-blur-[1px]" @click="closeModal" />

                    <!-- Panel -->
                    <div class="relative z-10 bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">

                        <!-- Body -->
                        <div class="p-6 md:p-8 space-y-7 overflow-y-auto wos-scroll flex-1">
                            
                            <!-- Title -->
                            <h2 class="text-xl font-semibold text-gray-900">{{ activeProvider.name }} OAuth</h2>

                            <!-- Enable Toggle -->
                            <div class="flex items-center gap-3">
                                <label for="provider-enabled-toggle" class="relative inline-flex items-center cursor-pointer">
                                    <input id="provider-enabled-toggle" type="checkbox" v-model="form.is_enabled" class="sr-only peer" />
                                    <div class="w-[34px] h-[20px] bg-gray-200 peer-focus:ring-2 peer-focus:ring-[#5c6dff]/30 rounded-full peer peer-checked:after:translate-x-[14px] after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#5c6dff] peer-checked:after:border-white"></div>
                                    <span class="sr-only">Enable OAuth Provider</span>
                                </label>
                                <span class="text-[14px] font-medium text-gray-900">Enable</span>
                            </div>

                            <!-- Credential Mode Cards -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Demo -->
                                <div @click="form.use_demo = true"
                                    :class="['border rounded-lg p-5 cursor-pointer transition-all', form.use_demo ? 'border-[#5c6dff] ring-1 ring-[#5c6dff]' : 'border-gray-200 hover:border-gray-300']">
                                    <p class="text-[14px] font-semibold text-gray-900 mb-1">Demo credentials</p>
                                    <p class="text-[13px] text-gray-500">Test {{ activeProvider.name }} OAuth without setup</p>
                                </div>
                                <!-- Custom -->
                                <div @click="form.use_demo = false"
                                    :class="['border rounded-lg p-5 cursor-pointer transition-all', !form.use_demo ? 'border-[#5c6dff] ring-1 ring-[#5c6dff]' : 'border-gray-200 hover:border-gray-300']">
                                    <p class="text-[14px] font-semibold text-gray-900 mb-1">Your app's credentials</p>
                                    <p class="text-[13px] text-gray-500">Configure your own {{ activeProvider.name }} OAuth</p>
                                </div>
                            </div>

                            <!-- Custom credential fields -->
                            <div v-if="!form.use_demo" class="space-y-6">
                                <!-- Redirect URI -->
                                <div>
                                    <h3 class="text-[14px] font-semibold text-gray-900 mb-1">Redirect URI</h3>
                                    <p class="text-[14px] text-gray-700 break-all">{{ activeProvider.redirect_uri }}</p>
                                </div>

                                <!-- Client ID -->
                                <div>
                                    <div class="flex justify-between items-end mb-1.5">
                                        <label for="client-id-input" class="text-[14px] font-semibold text-gray-900">{{ activeProvider.name }} Client ID</label>
                                        <a href="#" class="text-[13px] text-[#5c6dff] hover:underline flex items-center gap-1">Learn more about {{ activeProvider.name }} OAuth <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg></a>
                                    </div>
                                    <input id="client-id-input" v-model="form.client_id" type="text"
                                        :placeholder="`Enter your ${activeProvider.name} Client ID`"
                                        class="w-full px-3 py-2 text-[14px] border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5c6dff] focus:border-[#5c6dff] outline-none transition" />
                                </div>

                                <!-- Client Secret -->
                                <div>
                                    <div class="flex justify-between items-end mb-1.5">
                                        <label for="client-secret-input" class="text-[14px] font-semibold text-gray-900">{{ activeProvider.name }} Client Secret</label>
                                        <a href="#" class="text-[13px] text-[#5c6dff] hover:underline flex items-center gap-1">{{ activeProvider.name }} Developer Console <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg></a>
                                    </div>
                                    <input id="client-secret-input" v-model="form.client_secret" type="password"
                                        :placeholder="`Enter your ${activeProvider.name} Client Secret`"
                                        class="w-full px-3 py-2 text-[14px] border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5c6dff] focus:border-[#5c6dff] outline-none transition" />
                                </div>

                                <!-- OAuth tokens -->
                                <div class="flex items-center gap-2 mt-4">
                                    <input type="checkbox" id="return-tokens" class="w-4 h-4 rounded border-gray-300 text-[#5c6dff] focus:ring-[#5c6dff]"/>
                                    <label for="return-tokens" class="text-[14px] font-medium text-gray-900 cursor-pointer">Return {{ activeProvider.name }} OAuth tokens</label>
                                    <div class="group relative flex items-center cursor-help">
                                        <AlertCircle class="w-4 h-4 text-gray-400" />
                                    </div>
                                </div>

                                <!-- Scopes -->
                                <div>
                                    <div class="flex justify-between items-end mb-1.5">
                                        <label for="scopes-select" class="text-[14px] font-semibold text-gray-900 flex items-center gap-1">Scopes <AlertCircle class="w-3.5 h-3.5 text-gray-400"/></label>
                                        <a href="#" class="text-[13px] text-[#5c6dff] hover:underline flex items-center gap-1">See {{ activeProvider.name }} docs for OAuth scopes <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg></a>
                                    </div>
                                    <div class="relative">
                                        <select id="scopes-select" class="w-full appearance-none px-3 py-2 text-[14px] border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5c6dff] focus:border-[#5c6dff] outline-none transition bg-white text-gray-900">
                                            <option>2 scopes selected</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-gray-500">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-2 mt-3">
                                        <span class="bg-gray-100 border border-gray-200 text-gray-700 px-2.5 py-1 rounded-md text-[12.5px] font-mono">openid</span>
                                        <span class="bg-gray-100 border border-gray-200 text-gray-700 px-2.5 py-1 rounded-md text-[12.5px] font-mono">profile</span>
                                        <span class="bg-gray-100 border border-gray-200 text-gray-700 px-2.5 py-1 rounded-md text-[12.5px] font-mono">email</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-6 py-4 bg-white flex justify-end gap-3 border-t border-gray-100 shrink-0">
                            <button @click="closeModal" class="px-4 py-2 border border-gray-200 bg-white text-gray-700 rounded-md text-[13.5px] font-medium hover:bg-gray-50 transition-colors shadow-sm">
                                Cancel
                            </button>
                            <button @click="saveProvider" :disabled="isSaving"
                                class="px-4 py-2 bg-[#5c6dff] border border-transparent text-white rounded-md text-[13.5px] font-medium hover:bg-[#4b59e6] transition-colors shadow-sm disabled:opacity-50 flex items-center gap-2">
                                <Loader2 v-if="isSaving" class="w-3.5 h-3.5 animate-spin" />
                                Save changes
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>