<script setup lang="ts">
import axios from "axios";
import { CheckCircle2, Copy, ShieldCheck, Smartphone } from "lucide-vue-next";
import { ref } from "vue";

const isSetupOpen = ref(false);
const qrCodeSvg = ref("");
const secret = ref("");
const verificationCode = ref("");
const isVerified = ref(false);
const backupCodes = ref<string[]>([]);
const errorMsg = ref("");
const isSubmitting = ref(false);
const isActive = ref(false);
const isLoadingStatus = ref(true);

const fetchStatus = async () => {
	isLoadingStatus.value = true;
	try {
		const res = await axios.get("/workos/auth-platform/mfa/user-status");
		isActive.value = res.data.is_active;
	} catch (e) {
		console.error("Failed to fetch MFA status", e);
	} finally {
		isLoadingStatus.value = false;
	}
};

import { onMounted } from "vue";

onMounted(() => {
	fetchStatus();
});

const startMfaSetup = async () => {
	isSetupOpen.value = true;
	errorMsg.value = "";
	try {
		const res = await axios.post("/workos/auth-platform/mfa/setup");
		qrCodeSvg.value = res.data.qr_code_svg;
		secret.value = res.data.secret;
	} catch (e: any) {
		errorMsg.value = e.response?.data?.error || "Failed to start setup";
	}
};

const verifyMfa = async () => {
	if (!verificationCode.value || verificationCode.value.length < 6) return;

	isSubmitting.value = true;
	errorMsg.value = "";

	try {
		const res = await axios.post("/workos/auth-platform/mfa/verify", {
			code: verificationCode.value,
		});

		if (res.data.success) {
			isVerified.value = true;
			isActive.value = true;
			backupCodes.value = res.data.backup_codes;
		}
	} catch (e: any) {
		errorMsg.value = e.response?.data?.error || "Invalid verification code";
	} finally {
		isSubmitting.value = false;
	}
};

const copyCodes = () => {
	navigator.clipboard.writeText(backupCodes.value.join("\n"));
	alert("Codes copied to clipboard!");
};
</script>

<template>
    <div class="space-y-6 animate-fade-in max-w-[800px]">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-gray-200 dark:border-zinc-700 pb-5">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-zinc-100 tracking-tight">Multi-factor Authentication</h1>
                <p class="text-[14px] text-gray-500 dark:text-zinc-400 mt-1">Secure your accounts by requiring a second form of authentication.</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl shadow-sm overflow-hidden dark:shadow-none">
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                        <Smartphone class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-zinc-100 mb-1">Authenticator App (TOTP)</h3>
                        <p class="text-[13px] text-gray-500 dark:text-zinc-400 mb-4">Use an app like 1Password, Authy, or Google Authenticator to generate one-time codes.</p>
                        
                        <!-- Status Loading -->
                        <div v-if="isLoadingStatus" class="wos-shimmer h-8 w-32 rounded-md mb-4"></div>

                        <!-- Not Active & Not Setup -->
                        <button 
                            v-else-if="!isActive && !isSetupOpen"
                            @click="startMfaSetup" 
                            class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-[13px] font-medium text-white hover:bg-blue-700 transition-colors shadow-sm dark:shadow-none"
                        >
                            Set up Authenticator App
                        </button>

                        <!-- Already Active Indicator -->
                        <div v-else-if="isActive && !isSetupOpen" class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 mb-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <CheckCircle2 class="w-5 h-5 text-emerald-600" />
                                <div>
                                    <h4 class="text-[13px] font-semibold text-emerald-900">MFA is Active</h4>
                                    <p class="text-[12px] text-emerald-700">Your account is secured with TOTP authentication.</p>
                                </div>
                            </div>
                            <button 
                                @click="startMfaSetup" 
                                class="px-3 py-1.5 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-md text-[12px] font-medium text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors shadow-sm dark:shadow-none"
                            >
                                Buat Baru (Reset)
                            </button>
                        </div>

                        <!-- Setup Flow -->
                        <div v-if="isSetupOpen" class="mt-6 border border-gray-200 dark:border-zinc-700 rounded-lg p-5 bg-gray-50 dark:bg-zinc-900/50">
                            
                            <div v-if="!isVerified">
                                <h4 class="text-[14px] font-semibold text-gray-900 dark:text-zinc-100 mb-4">1. Scan this QR Code</h4>
                                <div class="bg-white dark:bg-zinc-900 p-3 rounded-lg border border-gray-200 dark:border-zinc-700 w-max mb-4 shadow-sm dark:shadow-none">
                                    <!-- Render the Base64 SVG -->
                                    <img v-if="qrCodeSvg" :src="`data:image/svg+xml;base64,${qrCodeSvg}`" alt="QR Code" class="w-40 h-40" />
                                    <div v-else class="w-40 h-40 bg-gray-100 dark:bg-zinc-800 animate-pulse flex items-center justify-center text-gray-400 dark:text-zinc-500 text-xs">Loading...</div>
                                </div>
                                <div class="text-[12px] text-gray-500 dark:text-zinc-400 mb-6 flex items-center gap-2">
                                    <span class="font-medium">Secret:</span> <code class="bg-gray-100 dark:bg-zinc-800 px-1.5 py-0.5 rounded text-gray-800 dark:text-zinc-200">{{ secret }}</code>
                                </div>

                                <h4 class="text-[14px] font-semibold text-gray-900 dark:text-zinc-100 mb-3">2. Enter the verification code</h4>
                                <div class="flex items-center gap-3">
                                    <input 
                                        v-model="verificationCode" 
                                        type="text" 
                                        placeholder="000 000" 
                                        maxlength="6"
                                        class="w-32 px-3 py-2 text-[14px] tracking-widest text-center border border-gray-300 dark:border-zinc-700 rounded-md shadow-sm focus:ring-1 focus:ring-[#2563eb] focus:border-[#2563eb dark:shadow-none]"
                                    >
                                    <button 
                                        @click="verifyMfa" 
                                        :disabled="isSubmitting"
                                        class="px-4 py-2 bg-gray-900 text-white rounded-md text-[13px] font-medium hover:bg-gray-800 transition-colors disabled:opacity-50"
                                    >
                                        {{ isSubmitting ? 'Verifying...' : 'Verify' }}
                                    </button>
                                </div>
                                <p v-if="errorMsg" class="text-red-500 text-[12px] mt-2">{{ errorMsg }}</p>
                            </div>

                            <!-- Success & Backup Codes -->
                            <div v-else class="animate-fade-in">
                                <div class="flex items-center gap-2 text-emerald-600 mb-4">
                                    <CheckCircle2 class="w-6 h-6" />
                                    <h4 class="text-[16px] font-semibold">MFA Successfully Activated!</h4>
                                </div>
                                
                                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-lg p-5">
                                    <div class="flex items-start justify-between mb-4">
                                        <div>
                                            <h5 class="text-[14px] font-semibold text-gray-900 dark:text-zinc-100">Backup Codes</h5>
                                            <p class="text-[13px] text-gray-500 dark:text-zinc-400">Save these codes in a safe place. You can use them to access your account if you lose your device.</p>
                                        </div>
                                        <button @click="copyCodes" class="text-gray-500 dark:text-zinc-400 hover:text-gray-700 p-1.5 bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200 rounded-md transition-colors" title="Copy all">
                                            <Copy class="w-4 h-4" />
                                        </button>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                        <div v-for="code in backupCodes" :key="code" class="bg-gray-50 dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 text-gray-700 dark:text-zinc-300 font-mono text-[13px] px-3 py-2 rounded text-center">
                                            {{ code }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</template>