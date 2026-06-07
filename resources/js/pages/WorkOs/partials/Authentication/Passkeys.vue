<script setup lang="ts">
import axios from "axios";
import { AlertCircle, CheckCircle2, Fingerprint } from "lucide-vue-next";
import { ref } from "vue";

const isRegistering = ref(false);
const isVerifying = ref(false);
const errorMsg = ref("");
const successMsg = ref("");

const arrayBufferToBase64 = (buffer: ArrayBuffer) => {
	let binary = "";
	const bytes = new Uint8Array(buffer);
	for (let i = 0; i < bytes.byteLength; i++) {
		binary += String.fromCharCode(bytes[i]);
	}
	return window.btoa(binary);
};

const base64ToArrayBuffer = (base64: string) => {
	const binary_string = window.atob(base64);
	const len = binary_string.length;
	const bytes = new Uint8Array(len);
	for (let i = 0; i < len; i++) {
		bytes[i] = binary_string.charCodeAt(i);
	}
	return bytes.buffer;
};

const registerPasskey = async () => {
	isRegistering.value = true;
	errorMsg.value = "";
	successMsg.value = "";

	try {
		// 1. Get options from server
		const optRes = await axios.post("/workos/auth-platform/passkeys/options");
		const options = optRes.data;

		// Convert base64 challenge to ArrayBuffer
		options.challenge = base64ToArrayBuffer(options.challenge);
		options.user.id = base64ToArrayBuffer(options.user.id);

		// 2. Call WebAuthn API
		const credential: any = await navigator.credentials.create({
			publicKey: options,
		});

		// 3. Send response back to server
		const verifyRes = await axios.post(
			"/workos/auth-platform/passkeys/verify",
			{
				id: credential.id,
				rawId: arrayBufferToBase64(credential.rawId),
				type: credential.type,
				response: {
					attestationObject: arrayBufferToBase64(
						credential.response.attestationObject,
					),
					clientDataJSON: arrayBufferToBase64(
						credential.response.clientDataJSON,
					),
				},
			},
		);

		successMsg.value =
			"Passkey registered successfully! You can now use your biometric sensor to log in.";
	} catch (e: any) {
		errorMsg.value =
			e.response?.data?.error ||
			e.message ||
			"Passkey registration failed or was cancelled.";
	} finally {
		isRegistering.value = false;
	}
};
</script>

<template>
    <div class="space-y-6 animate-fade-in max-w-[800px]">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-gray-200 pb-5">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 tracking-tight">Passkeys (WebAuthn)</h1>
                <p class="text-[14px] text-gray-500 mt-1">Experience passwordless authentication using biometrics.</p>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 text-center">
            
            <div class="w-16 h-16 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center mx-auto mb-4 border border-purple-100">
                <Fingerprint class="w-8 h-8" />
            </div>
            
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Register a Passkey</h3>
            <p class="text-[14px] text-gray-500 mb-6 max-w-md mx-auto">
                Passkeys provide the highest level of security against phishing by storing cryptographic keys directly in your device's hardware (Face ID, Touch ID, or Windows Hello).
            </p>

            <div v-if="errorMsg" class="mb-4 bg-red-50 text-red-600 text-[13px] px-4 py-3 rounded-lg flex items-start gap-2 text-left max-w-md mx-auto border border-red-100">
                <AlertCircle class="w-4 h-4 shrink-0 mt-0.5" />
                <span>{{ errorMsg }}</span>
            </div>

            <div v-if="successMsg" class="mb-4 bg-emerald-50 text-emerald-600 text-[13px] px-4 py-3 rounded-lg flex items-start gap-2 text-left max-w-md mx-auto border border-emerald-100">
                <CheckCircle2 class="w-4 h-4 shrink-0 mt-0.5" />
                <span>{{ successMsg }}</span>
            </div>

            <button 
                @click="registerPasskey" 
                :disabled="isRegistering"
                class="px-5 py-2.5 bg-gray-900 border border-transparent rounded-lg text-[14px] font-medium text-white hover:bg-gray-800 transition-colors shadow-sm disabled:opacity-50 inline-flex items-center gap-2"
            >
                <span v-if="isRegistering" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                <Fingerprint v-else class="w-4 h-4" />
                {{ isRegistering ? 'Waiting for device...' : 'Create Passkey' }}
            </button>
        </div>
    </div>
</template>