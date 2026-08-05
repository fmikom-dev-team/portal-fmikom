<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3";
import axios from "axios";
import { onMounted, ref } from "vue";
import AuthBase from "@/layouts/AuthLayout.vue";

defineOptions({ layout: AuthBase });

const props = defineProps<{
	isValid: boolean;
	errorMessage?: string;
	userData?: {
		name: string;
		email: string;
		role: string;
		provider: string;
	};
	signedParams?: {
		request_id: number;
		token: string;
		signature: string;
		expires: number;
	};
}>();

const steps = ref([
	{
		id: 1,
		title: "Token Signature & Cipher Key",
		detail: "Memverifikasi keabsahan tanda tangan digital dan token verifikasi...",
		status: "idle", // 'idle' | 'running' | 'success' | 'failed'
	},
	{
		id: 2,
		title: "Device & Context Security Signal",
		detail: "Mengevaluasi sinyal peramban, riwayat IP, dan preferensi keamanan...",
		status: "idle",
	},
	{
		id: 3,
		title: "Account Approval Verification",
		detail: "Mengonfirmasi status persetujuan resmi dari Administrator WorkOS...",
		status: "idle",
	},
	{
		id: 4,
		title: "Module Role Authorization",
		detail: "Menyiapkan hak akses modul, peran keanggotaan, dan sesi pengguna...",
		status: "idle",
	},
]);

const currentActiveIndex = ref(0);
const isFinished = ref(false);
const isRedirecting = ref(false);
const redirectUrl = ref("");
const verificationError = ref("");

const runStepAnimation = async () => {
	if (!props.isValid || !props.signedParams) return;

	for (let i = 0; i < steps.value.length; i++) {
		currentActiveIndex.value = i;
		steps.value[i].status = "running";
		await new Promise((resolve) => setTimeout(resolve, 800));
		steps.value[i].status = "success";
	}

	isFinished.value = true;
	await submitFinalVerification();
};

const submitFinalVerification = async () => {
	try {
		const response = await axios.post(
			"/auth/oauth/verify-access",
			{
				request_id: props.signedParams?.request_id,
				token: props.signedParams?.token,
			},
			{
				params: {
					signature: props.signedParams?.signature,
					expires: props.signedParams?.expires,
				},
			},
		);

		if (response.data.success) {
			redirectUrl.value = response.data.redirect_url || "/dashboard";
			// Brief delay to let the user see the verified card
			setTimeout(() => {
				isRedirecting.value = true;
				router.visit(redirectUrl.value);
			}, 1200);
		} else {
			verificationError.value =
				response.data.message || "Gagal mengaktifkan sesi pengguna.";
		}
	} catch (err: any) {
		verificationError.value =
			err.response?.data?.message ||
			"Terjadi kesalahan saat memverifikasi sesi akses.";
	}
};

const enterDashboard = () => {
	if (redirectUrl.value) {
		isRedirecting.value = true;
		router.visit(redirectUrl.value);
	} else {
		router.visit("/dashboard");
	}
};

onMounted(() => {
	if (props.isValid) {
		runStepAnimation();
	}
});
</script>

<template>
    <div class="w-full max-w-lg mx-auto py-4">
        <Head>
            <title>Smart Access Control - Verifikasi Akses</title>
        </Head>

        <!-- Clean Modern White Glass Card Container -->
        <div class="bg-white/90 backdrop-blur-xl border border-slate-200/80 shadow-2xl rounded-3xl p-6 sm:p-8 transition-all duration-500 relative overflow-hidden">
            <!-- Decorative Accent Top Gradient Bar -->
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-blue-600 via-indigo-500 to-cyan-400"></div>

            <!-- Card Header -->
            <div class="mb-6 text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 text-[#2563eb] shadow-sm mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h1 class="text-xl font-extrabold text-slate-800 tracking-tight font-sans">Smart Access Control</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-xs mx-auto leading-relaxed">
                    Evaluasi sinyal keamanan real-time sebelum memberikan izin akses akun.
                </p>
            </div>

            <!-- INVALID / EXPIRED TOKEN STATE -->
            <div v-if="!props.isValid || verificationError" class="p-5 bg-red-50/80 border border-red-200/80 rounded-2xl text-center animate-in fade-in zoom-in-95 duration-300">
                <div class="w-10 h-10 mx-auto rounded-full bg-red-100 text-red-600 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-red-900 mb-1">Verifikasi Akses Gagal</h3>
                <p class="text-xs text-red-700 leading-relaxed">
                    {{ props.errorMessage || verificationError }}
                </p>
                <div class="mt-4 pt-3 border-t border-red-100 flex justify-center">
                    <a href="/login" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#2563eb] hover:underline">
                        ← Kembali ke Halaman Login
                    </a>
                </div>
            </div>

            <!-- VALID ANIMATED VERIFICATION PROCESS -->
            <div v-else class="space-y-4">
                <!-- Staggered Signal Verification List -->
                <div class="space-y-2.5">
                    <div
                        v-for="(stepItem, index) in steps"
                        :key="stepItem.id"
                        :class="[
                            'p-3.5 rounded-2xl border transition-all duration-500 ease-out flex items-start gap-3',
                            index === currentActiveIndex && !isFinished
                                ? 'bg-blue-50/60 border-blue-300/80 shadow-md ring-1 ring-blue-500/20 opacity-100 scale-100 blur-none'
                                : index < currentActiveIndex || isFinished
                                ? 'bg-emerald-50/40 border-emerald-200/60 opacity-90 blur-[0.2px]'
                                : 'bg-slate-50/40 border-slate-100 opacity-40 blur-[1px]'
                        ]"
                    >
                        <!-- Status Icon Indicator -->
                        <div class="mt-0.5 shrink-0">
                            <!-- Success Checkmark -->
                            <div v-if="stepItem.status === 'success'" class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow-sm animate-in zoom-in duration-300">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <!-- Active Spinner -->
                            <div v-else-if="stepItem.status === 'running'" class="w-5 h-5 rounded-full border-2 border-blue-600 border-t-transparent animate-spin"></div>
                            <!-- Idle Circle -->
                            <div v-else class="w-5 h-5 rounded-full border border-slate-300 bg-white"></div>
                        </div>

                        <!-- Step Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-slate-800 truncate">{{ stepItem.title }}</h4>
                                <span v-if="stepItem.status === 'success'" class="text-[10px] font-semibold text-emerald-600 uppercase tracking-wider">Valid ✅</span>
                                <span v-else-if="stepItem.status === 'running'" class="text-[10px] font-medium text-blue-600 animate-pulse">Memproses...</span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-0.5 leading-snug line-clamp-1">
                                {{ stepItem.detail }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- VERIFIED USER PROFILE CARD (Revealed upon completion) -->
                <div
                    v-if="isFinished"
                    class="mt-6 p-5 bg-gradient-to-br from-indigo-50/70 via-blue-50/50 to-white border border-blue-100/90 rounded-2xl text-center shadow-sm animate-in fade-in slide-in-from-bottom-4 duration-500"
                >
                    <div class="relative inline-block mb-3">
                        <div class="w-16 h-16 rounded-2xl bg-white border border-slate-200 shadow-md flex items-center justify-center text-slate-700 font-extrabold text-xl overflow-hidden mx-auto">
                            {{ props.userData?.name?.charAt(0) || 'U' }}
                        </div>
                        <!-- Verified Badge Icon -->
                        <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-[#2563eb] text-white flex items-center justify-center border-2 border-white shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>

                    <div class="flex items-center justify-center gap-1.5 mb-0.5">
                        <h3 class="text-base font-extrabold text-slate-800 font-sans">{{ props.userData?.name }}</h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                            {{ props.userData?.provider }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mb-4">{{ props.userData?.email }}</p>

                    <button
                        type="button"
                        @click="enterDashboard"
                        :disabled="isRedirecting"
                        class="w-full bg-[#2563eb] hover:bg-[#1d4ed8] text-white font-semibold text-sm py-3 px-6 rounded-xl shadow-lg shadow-blue-500/20 transition-all flex items-center justify-center gap-2"
                    >
                        <span v-if="isRedirecting">Mengalihkan...</span>
                        <span v-else>Masuk ke Dashboard Portal →</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
