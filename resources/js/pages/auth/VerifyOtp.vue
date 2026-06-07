<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import { onMounted, onUnmounted, ref } from "vue";
import InputError from "@/components/InputError.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Spinner } from "@/components/ui/spinner";
import AuthLayout from "@/layouts/AuthLayout.vue";

const { email, expiresAt } = defineProps<{
	email: string;
	expiresAt: string | null;
	status: string | null;
}>();

const form = useForm({
	otp: "",
});

const resendForm = useForm({});

// Timer countdown
const timeLeft = ref(0);
let timerInterval: ReturnType<typeof setInterval> | null = null;

const startTimer = () => {
	if (!expiresAt) return;
	const updateTimer = () => {
		const diff = Math.floor(
			(new Date(expiresAt).getTime() - Date.now()) / 1000,
		);
		timeLeft.value = Math.max(0, diff);
	};
	updateTimer();
	timerInterval = setInterval(updateTimer, 1000);
};

const formatTime = (seconds: number) => {
	const m = Math.floor(seconds / 60);
	const s = seconds % 60;
	return `${m}:${s.toString().padStart(2, "0")}`;
};

onMounted(startTimer);
onUnmounted(() => {
	if (timerInterval) clearInterval(timerInterval);
});

// OTP Input handling — auto-move & paste
const otpInputs = ref<HTMLInputElement[]>([]);
const otpDigits = ref(["", "", "", "", "", ""]);

const handleOtpInput = (index: number, event: Event) => {
	const input = event.target as HTMLInputElement;
	const value = input.value.replace(/\D/g, "");
	otpDigits.value[index] = value.slice(-1);
	form.otp = otpDigits.value.join("");

	if (value && index < 5) {
		otpInputs.value[index + 1]?.focus();
	}
};

const handleOtpKeydown = (index: number, event: KeyboardEvent) => {
	if (event.key === "Backspace" && !otpDigits.value[index] && index > 0) {
		otpInputs.value[index - 1]?.focus();
	}
};

const handleOtpPaste = (event: ClipboardEvent) => {
	event.preventDefault();
	const pastedData =
		event.clipboardData?.getData("text").replace(/\D/g, "") ?? "";
	for (let i = 0; i < 6; i++) {
		otpDigits.value[i] = pastedData[i] ?? "";
	}
	form.otp = otpDigits.value.join("");
	if (pastedData.length > 0) {
		otpInputs.value[Math.min(pastedData.length - 1, 5)]?.focus();
	}
};

const submit = () => {
	form.post("/verify-otp");
};

const resendOtp = () => {
	resendForm.post("/resend-otp");
};
</script>

<template>
    <AuthLayout title="Verifikasi Email" description="Satu langkah lagi!">
        <Head title="Verifikasi OTP" />

        <!-- Icon -->
        <div class="flex justify-center mb-6">
            <div class="w-16 h-16 bg-gradient-to-br from-[#2563eb] to-[#7C6EF8] rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-200">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <!-- Deskripsi -->
        <div class="text-center mb-6">
            <h2 class="text-xl font-bold text-slate-800 mb-1">Cek Kotak Masuk Email Anda</h2>
            <p class="text-sm text-slate-500">
                Kami mengirimkan kode 6 digit ke
            </p>
            <p class="text-sm font-semibold text-[#2563eb] mt-0.5">{{ email }}</p>
        </div>

        <!-- Status Message -->
        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-700 bg-green-50 p-3 rounded-xl border border-green-200 flex items-center gap-2 justify-center">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div class="grid gap-6">
                <!-- OTP Input Boxes -->
                <div class="grid gap-2">
                    <Label class="font-semibold text-slate-800 text-center text-sm">Masukkan Kode OTP</Label>
                    <div class="flex justify-center gap-2.5" @paste="handleOtpPaste">
                        <input
                            v-for="(_, i) in otpDigits"
                            :key="i"
                            :ref="(el) => { if (el) otpInputs[i] = el as HTMLInputElement }"
                            type="text"
                            inputmode="numeric"
                            maxlength="1"
                            :value="otpDigits[i]"
                            @input="handleOtpInput(i, $event)"
                            @keydown="handleOtpKeydown(i, $event)"
                            class="w-12 h-14 text-center text-xl font-bold border-2 rounded-xl outline-none transition-all duration-200 bg-white
                                   border-slate-200 text-slate-800 placeholder-slate-200
                                   focus:border-[#2563eb] focus:bg-indigo-50/30 focus:ring-2 focus:ring-[#2563eb]/20"
                            :class="otpDigits[i] ? 'border-[#2563eb] bg-indigo-50/20' : ''"
                        />
                    </div>
                    <InputError :message="form.errors.otp" class="mt-1 text-center" />
                </div>

                <!-- Timer -->
                <div v-if="expiresAt" class="text-center">
                    <p v-if="timeLeft > 0" class="text-xs text-slate-400">
                        Kode berlaku selama
                        <span class="font-bold text-[#2563eb]">{{ formatTime(timeLeft) }}</span>
                    </p>
                    <p v-else class="text-xs text-red-500 font-medium">
                        Kode OTP sudah kadaluarsa. Kirim ulang di bawah.
                    </p>
                </div>

                <div class="flex flex-col gap-3">
                    <Button
                        type="submit"
                        class="w-full bg-[#2563eb] hover:bg-[#3B2DCB] text-white shadow-[0_6px_20px_rgba(82,68,228,0.4)] transition-all h-12 rounded-xl text-md font-medium"
                        :disabled="form.processing || form.otp.length < 6"
                    >
                        <Spinner v-if="form.processing" class="mr-2" />
                        Verifikasi Email Saya
                    </Button>

                    <Button
                        type="button"
                        variant="ghost"
                        class="w-full text-slate-500 hover:text-[#2563eb] h-10 transition-colors text-sm font-medium"
                        :disabled="resendForm.processing"
                        @click="resendOtp"
                    >
                        <Spinner v-if="resendForm.processing" class="mr-2 h-3 w-3" />
                        <svg v-else class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Kirim ulang kode OTP
                    </Button>
                </div>

                <!-- Tips -->
                <div class="bg-amber-50 border border-amber-100 rounded-xl p-3 text-xs text-amber-700">
                    <p class="font-semibold mb-1">💡 Tips:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-amber-600">
                        <li>Cek folder <span class="font-medium">Spam / Junk</span> jika tidak muncul</li>
                        <li>Kode berlaku selama <span class="font-medium">15 menit</span></li>
                        <li>Gunakan kode terbaru jika kirim ulang</li>
                    </ul>
                </div>
            </div>
        </form>
    </AuthLayout>
</template>
