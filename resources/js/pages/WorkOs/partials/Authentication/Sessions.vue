<script setup lang="ts">
import axios from "axios";
import { AlertCircle, Check, Clock, Globe } from "lucide-vue-next";
import { onMounted, ref } from "vue";
import AppModal from "../../components/ui/AppModal.vue";

const showLifetimeModal = ref(false);
const showCorsModal = ref(false);

const lifetime = ref({
	maxLength: 365,
	maxUnit: "Days",
	tokenDuration: 5,
	tokenUnit: "Minutes",
	inactivityLength: 2,
	inactivityUnit: "Days",
});

const corsOrigins = ref<string[]>([]);
const newOrigin = ref("");

const toast = ref<{ type: "success" | "error"; message: string } | null>(null);
const showToast = (message: string, type: "success" | "error" = "success") => {
	toast.value = { type, message };
	setTimeout(() => {
		toast.value = null;
	}, 3000);
};

const isLoading = ref(false);

const loadConfig = async () => {
	try {
		isLoading.value = true;
		const res = await axios.get("/workos/auth-platform/sessions-config");
		if (res.data.lifetime) {
			lifetime.value = res.data.lifetime;
		}
		corsOrigins.value = res.data.corsOrigins || [];
	} catch (e) {
		console.error("Failed to load sessions config:", e);
		showToast("Failed to load settings.", "error");
	} finally {
		isLoading.value = false;
	}
};

onMounted(() => {
	loadConfig();
});

const addOrigin = () => {
	if (newOrigin.value && !corsOrigins.value.includes(newOrigin.value)) {
		corsOrigins.value.push(newOrigin.value);
		newOrigin.value = "";
	}
};

const removeOrigin = (origin: string) => {
	corsOrigins.value = corsOrigins.value.filter((o) => o !== origin);
};

const saveLifetime = async () => {
	try {
		await axios.patch("/workos/auth-platform/sessions-config", {
			lifetime: lifetime.value,
		});
		showLifetimeModal.value = false;
		showToast("Session lifetime settings saved.");
	} catch (e) {
		console.error(e);
		showToast("Failed to save session lifetime settings.", "error");
	}
};

const saveCors = async () => {
	try {
		await axios.patch("/workos/auth-platform/sessions-config", {
			corsOrigins: corsOrigins.value,
		});
		showCorsModal.value = false;
		showToast("CORS settings saved.");
	} catch (e) {
		console.error(e);
		showToast("Failed to save CORS settings.", "error");
	}
};
</script>

<template>
    <div class="space-y-6 animate-fade-in max-w-[800px]">
        
        <!-- Toast -->
        <Transition enter-from-class="translate-y-2 opacity-0" enter-to-class="translate-y-0 opacity-100" leave-to-class="opacity-0" enter-active-class="transition duration-200" leave-active-class="transition duration-150">
            <div v-if="toast" class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border text-[13px] font-medium bg-emerald-50 border-emerald-200 text-emerald-800">
                <Check class="w-4 h-4" />
                {{ toast.message }}
            </div>
        </Transition>

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 tracking-tight">Sessions</h1>
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="space-y-4">
            <!-- Session lifetime skeleton -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 wos-shimmer rounded-xl shrink-0" />
                    <div class="flex-1 space-y-4">
                        <div class="space-y-2">
                            <div class="h-4 wos-shimmer rounded w-1/4" />
                            <div class="h-3.5 wos-shimmer rounded w-3/4" />
                        </div>
                        <div class="space-y-3 pt-2">
                            <div v-for="i in 3" :key="i" class="grid grid-cols-[180px_1fr] items-center">
                                <div class="h-3.5 wos-shimmer rounded w-28" />
                                <div class="h-3.5 wos-shimmer rounded w-16" />
                            </div>
                        </div>
                        <div class="h-8 wos-shimmer rounded w-16 pt-2" />
                    </div>
                </div>
            </div>

            <!-- CORS skeleton -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 wos-shimmer rounded-xl shrink-0" />
                    <div class="flex-1 space-y-4">
                        <div class="space-y-2">
                            <div class="h-4 wos-shimmer rounded w-1/3" />
                            <div class="h-3.5 wos-shimmer rounded w-2/3" />
                        </div>
                        <div class="grid grid-cols-[180px_1fr] items-center pt-2">
                            <div class="h-3.5 wos-shimmer rounded w-28" />
                            <div class="h-3.5 wos-shimmer rounded w-24" />
                        </div>
                        <div class="h-8 wos-shimmer rounded w-16 pt-2" />
                    </div>
                </div>
            </div>
        </div>

        <template v-else>
            <!-- ── Session lifetime ────────────────────────────────────────── -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center border border-gray-200 shrink-0">
                        <Clock class="w-6 h-6 text-gray-700" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-[14px] font-semibold text-gray-900">Session lifetime</h3>
                        <p class="text-[13px] text-gray-500 mt-0.5">Configure maximum session length, inactivity timeout, and access token validation time.</p>
                        
                        <div class="mt-5 space-y-3 text-[13px]">
                            <div class="grid grid-cols-[180px_1fr]">
                                <span class="text-gray-500">Maximum session length</span>
                                <span class="text-gray-900 font-medium">{{ lifetime.maxLength }} {{ lifetime.maxUnit.toLowerCase() }}</span>
                            </div>
                            <div class="grid grid-cols-[180px_1fr]">
                                <span class="text-gray-500">Access token duration</span>
                                <span class="text-gray-900 font-medium">{{ lifetime.tokenDuration }} {{ lifetime.tokenUnit.toLowerCase() }}</span>
                            </div>
                            <div class="grid grid-cols-[180px_1fr]">
                                <span class="text-gray-500">Inactivity timeout</span>
                                <span class="text-gray-900 font-medium">{{ lifetime.inactivityLength }} {{ lifetime.inactivityUnit.toLowerCase() }}</span>
                            </div>
                        </div>
                        
                        <button @click="showLifetimeModal = true" class="mt-5 px-3 py-1.5 bg-white border border-gray-200 rounded-md text-[13px] font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                            Manage
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── CORS ────────────────────────────────────────────────────── -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm mt-4">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center border border-gray-200 shrink-0">
                        <Globe class="w-6 h-6 text-gray-700" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-[14px] font-semibold text-gray-900">Cross-Origin Resource Sharing (CORS)</h3>
                        <p class="text-[13px] text-gray-500 mt-0.5">Define which web origins can securely interact with the WorkOS API.</p>
                        
                        <div class="mt-5 text-[13px] grid grid-cols-[180px_1fr]">
                            <span class="text-gray-500">Allowed web origins</span>
                            <span class="text-gray-900 font-medium">{{ corsOrigins.length ? corsOrigins.join(', ') : 'None' }}</span>
                        </div>
                        
                        <button @click="showCorsModal = true" class="mt-5 px-3 py-1.5 bg-white border border-gray-200 rounded-md text-[13px] font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                            Manage
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <!-- ========================================== MODALS ========================================== -->

        <!-- Session Lifetime Modal -->
        <AppModal
            :show="showLifetimeModal"
            title="Session lifetime"
            description="Configure maximum session length, inactivity timeout, and access token validation time."
            size="md"
            @close="showLifetimeModal = false"
        >
            <div class="space-y-4 text-[13px]">
                <div>
                    <label class="block font-medium text-gray-900 mb-1.5">Maximum session length</label>
                    <div class="flex gap-2">
                        <input type="number" v-model="lifetime.maxLength" class="w-24 px-3 py-1.5 border border-gray-300 rounded-md focus:ring-1 focus:ring-[#2563eb]" />
                        <select v-model="lifetime.maxUnit" class="w-32 px-3 py-1.5 border border-gray-300 rounded-md focus:ring-1 focus:ring-[#2563eb] bg-white">
                            <option>Days</option>
                            <option>Hours</option>
                            <option>Minutes</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-medium text-gray-900 mb-1.5">Access token duration</label>
                    <div class="flex gap-2">
                        <input type="number" v-model="lifetime.tokenDuration" class="w-24 px-3 py-1.5 border border-gray-300 rounded-md focus:ring-1 focus:ring-[#2563eb]" />
                        <select v-model="lifetime.tokenUnit" class="w-32 px-3 py-1.5 border border-gray-300 rounded-md focus:ring-1 focus:ring-[#2563eb] bg-white">
                            <option>Days</option>
                            <option>Hours</option>
                            <option>Minutes</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-medium text-gray-900 mb-1.5">Inactivity timeout <span class="text-gray-400 font-normal">(optional)</span></label>
                    <div class="flex gap-2">
                        <input type="number" v-model="lifetime.inactivityLength" class="w-24 px-3 py-1.5 border border-gray-300 rounded-md focus:ring-1 focus:ring-[#2563eb]" />
                        <select v-model="lifetime.inactivityUnit" class="w-32 px-3 py-1.5 border border-gray-300 rounded-md focus:ring-1 focus:ring-[#2563eb] bg-white">
                            <option>Days</option>
                            <option>Hours</option>
                            <option>Minutes</option>
                        </select>
                    </div>
                </div>
            </div>
            <template #footer>
                <button @click="showLifetimeModal = false" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-[13px] font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <button @click="saveLifetime" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-[13px] font-medium hover:bg-blue-700 transition-colors">Save</button>
            </template>
        </AppModal>

        <!-- CORS Modal -->
        <AppModal
            :show="showCorsModal"
            title="Cross-Origin Resource Sharing (CORS)"
            size="lg"
            @close="showCorsModal = false"
        >
            <div class="space-y-4 text-[13px]">
                <p class="text-gray-600 leading-relaxed">
                    Configuring CORS is required for client-side authentication APIs and the Widgets API, as they involve browser-based requests restricted by the same-origin policy. Unlisted origins will be rejected.
                </p>

                <div>
                    <label class="flex items-center gap-1 font-medium text-gray-900 mb-1.5">
                        Allowed web origins <AlertCircle class="w-3.5 h-3.5 text-gray-400" />
                    </label>
                    <div class="flex gap-2">
                        <input type="text" v-model="newOrigin" @keyup.enter="addOrigin" placeholder="https://example.com" class="flex-1 px-3 py-1.5 border border-gray-300 rounded-md focus:ring-1 focus:ring-[#2563eb]" />
                        <button @click="addOrigin" class="px-4 py-1.5 bg-gray-100 border border-gray-200 text-gray-700 rounded-md font-medium hover:bg-gray-200 transition-colors">+ Add</button>
                    </div>
                    <p class="text-gray-500 text-[12px] mt-2">
                        Staging supports the usage of wildcards <code>*</code>, <code>localhost</code> origins and <code>http</code> protocol.
                    </p>
                </div>

                <div class="mt-4 border border-gray-200 rounded-xl overflow-hidden bg-gray-50/50 min-h-[150px] flex flex-col">
                    <div v-if="corsOrigins.length === 0" class="flex-1 flex flex-col items-center justify-center text-gray-400">
                        <Globe class="w-8 h-8 mb-2 opacity-50" />
                        <span class="text-[13px]">No web origins have been added</span>
                    </div>
                    <ul v-else class="divide-y divide-gray-100 bg-white">
                        <li v-for="origin in corsOrigins" :key="origin" class="flex items-center justify-between px-4 py-3">
                            <span class="font-medium text-gray-900">{{ origin }}</span>
                            <button @click="removeOrigin(origin)" class="text-gray-400 hover:text-red-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            <template #footer>
                <button @click="showCorsModal = false" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-[13px] font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <button @click="saveCors" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-[13px] font-medium hover:bg-blue-700 transition-colors">Save changes</button>
            </template>
        </AppModal>
    </div>
</template>