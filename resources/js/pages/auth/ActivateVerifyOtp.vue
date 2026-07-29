<script setup lang="ts">
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { onMounted, onUnmounted, ref, watch } from "vue";
import InputError from "@/components/InputError.vue";
import { Button } from "@/components/ui/button";
import { Spinner } from "@/components/ui/spinner";
import AuthLayout from "@/layouts/AuthLayout.vue";

const props = defineProps<{
	email: string;
	expiresAt: string | null;
	status: string | null;
}>();

const page = usePage();

const form = useForm({
	otp: "",
});

const resendForm = useForm({});

const helpdeskForm = useForm({
	new_email: "",
});

// Counter percobaan gagal & resend untuk mengontrol kemunculan card bantuan
const failedOtpAttempts = ref(0);
const resendCount = ref(0);
const showHelpdeskModal = ref(false);

// Timer countdown untuk OTP expiration
const timeLeft = ref(0);
let timerInterval: ReturnType<typeof setInterval> | null = null;

// Cooldown berjenjang (Tiered Exponential Cooldown: 30s -> 60s -> 120s -> 300s)
const resendCooldown = ref(0);
let resendInterval: ReturnType<typeof setInterval> | null = null;

const getCooldownDuration = () => {
	const schedule = [30, 60, 120, 300];
	const index = Math.min(resendCount.value, schedule.length - 1);
	return schedule[index];
};

const startTimer = () => {
	if (!props.expiresAt) return;
	const updateTimer = () => {
		const diff = Math.floor(
			(new Date(props.expiresAt).getTime() - Date.now()) / 1000,
		);
		timeLeft.value = Math.max(0, diff);
	};
	updateTimer();
	timerInterval = setInterval(updateTimer, 1000);
};

const startResendCooldown = () => {
	const duration = getCooldownDuration();
	resendCooldown.value = duration;
	if (resendInterval) clearInterval(resendInterval);
	resendInterval = setInterval(() => {
		if (resendCooldown.value > 0) {
			resendCooldown.value--;
		} else {
			if (resendInterval) clearInterval(resendInterval);
		}
	}, 1000);
};

const formatTime = (seconds: number) => {
	const m = Math.floor(seconds / 60);
	const s = seconds % 60;
	return `${m}:${s.toString().padStart(2, "0")}`;
};

onMounted(() => {
	startTimer();
	startResendCooldown(); // Cooldown Tier 1 (30s) saat mendarat
});

onUnmounted(() => {
	if (timerInterval) clearInterval(timerInterval);
	if (resendInterval) clearInterval(resendInterval);
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
	form.post("/activate/verify-otp", {
		onError: () => {
			failedOtpAttempts.value++;
		},
	});
};

const resendOtp = () => {
	if (resendCooldown.value > 0) return;
	resendForm.post("/activate/resend-otp", {
		onSuccess: () => {
			resendCount.value++;
			startResendCooldown();
		},
	});
};

const submitHelpdesk = () => {
	helpdeskForm.post("/activate/helpdesk-request", {
		onSuccess: () => {
			showHelpdeskModal.value = false;
			// Jika backend mengembalikan URL WhatsApp, buka otomatis di tab baru
			const waUrl = (page.props as Record<string, unknown>).waUrl as string;
			if (waUrl) {
				window.open(waUrl, "_blank");
			}
		},
	});
};
</script>

<template>
    <AuthLayout title="Aktivasi Akun" description="Verifikasi kode OTP Anda">
        <Head>
            <title>Verifikasi OTP Aktivasi</title>
        </Head>

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
            <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-1">Cek Kotak Masuk Email Anda</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Kami telah mengirimkan kode OTP 6 digit ke email
            </p>
            <p class="text-sm font-semibold text-[#2563eb] dark:text-blue-400 mt-0.5">{{ email }}</p>
        </div>

        <!-- Status Message -->
        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-700 bg-green-50 dark:bg-green-950/30 dark:text-green-400 p-3 rounded-xl border border-green-200 dark:border-green-900 flex items-center gap-2 justify-center">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div class="grid gap-6">
                <!-- OTP Input Boxes -->
                <div class="grid gap-2">
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
                            class="w-12 h-14 text-center text-xl font-bold border-2 rounded-xl outline-none transition-all duration-200 bg-white dark:bg-slate-950
                                   border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white placeholder-slate-200
                                   focus:border-[#2563eb] focus:bg-indigo-50/30 focus:ring-2 focus:ring-[#2563eb]/20"
                            :class="otpDigits[i] ? 'border-[#2563eb] bg-indigo-50/20' : ''"
                        />
                    </div>
                    <InputError :message="form.errors.otp" class="mt-1 text-center" />
                </div>

                <!-- Timer Expiration -->
                <div v-if="expiresAt" class="text-center">
                    <p v-if="timeLeft > 0" class="text-xs text-slate-400 dark:text-slate-500">
                        Kode berlaku selama
                        <span class="font-bold text-[#2563eb] dark:text-blue-400">{{ formatTime(timeLeft) }}</span>
                    </p>
                    <p v-else class="text-xs text-red-500 font-medium">
                        Kode OTP sudah kedaluwarsa. Silakan kirim ulang di bawah.
                    </p>
                </div>

                <div class="flex flex-col gap-3">
                    <Button
                        type="submit"
                        class="w-full bg-[#2563eb] hover:bg-[#3B2DCB] text-white shadow-[0_6px_20px_rgba(82,68,228,0.4)] transition-all h-12 rounded-xl text-md font-medium"
                        :disabled="form.processing || form.otp.length < 6"
                    >
                        <Spinner v-if="form.processing" class="mr-2" />
                        Verifikasi OTP
                    </Button>

                    <Button
                        type="button"
                        variant="ghost"
                        class="w-full text-slate-500 hover:text-[#2563eb] dark:text-slate-400 dark:hover:text-blue-400 h-10 transition-colors text-sm font-medium"
                        :disabled="resendForm.processing || resendCooldown > 0"
                        @click="resendOtp"
                    >
                        <Spinner v-if="resendForm.processing" class="mr-2 h-3 w-3" />
                        <svg v-else class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span v-if="resendCooldown > 0">Kirim ulang kode OTP ({{ formatTime(resendCooldown) }})</span>
                        <span v-else>Kirim ulang kode OTP</span>
                    </Button>
                </div>

                <!-- CARD BANTUAN HELPDESK (Conditional Trigger: Tampil hanya jika gagal OTP >= 2x, Resend >= 2x, atau Expired) -->
                <div
                    v-if="failedOtpAttempts >= 2 || resendCount >= 2 || timeLeft === 0"
                    class="bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 rounded-2xl p-4 transition-all duration-300 animate-fadeIn"
                >
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 bg-blue-600 text-white rounded-xl flex items-center justify-center shrink-0 shadow-sm mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-bold text-slate-800 dark:text-white">Terkendala Dengan Email Terdaftar?</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Jika email di atas tidak aktif atau salah, Anda dapat mengajukan pembaruan email ke Admin FMIKOM.
                            </p>
                            <button
                                type="button"
                                @click="showHelpdeskModal = true"
                                class="mt-2.5 inline-flex items-center text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline gap-1"
                            >
                                💬 Pengajuan Ubah Email & Bantuan Admin →
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tips Default -->
                <div class="bg-amber-50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900 rounded-xl p-3 text-xs text-amber-700 dark:text-amber-300">
                    <div class="flex justify-between items-center mb-1">
                        <p class="font-semibold">💡 Tips:</p>
                        <button
                            type="button"
                            @click="showHelpdeskModal = true"
                            class="text-[11px] font-medium text-amber-700 hover:underline dark:text-amber-300"
                        >
                            Email tidak aktif?
                        </button>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-amber-600 dark:text-amber-400">
                        <li>Cek folder <span class="font-medium">Spam / Junk</span> jika tidak muncul</li>
                        <li>Kode berlaku selama <span class="font-medium">15 menit</span></li>
                        <li>Gunakan kode terbaru jika kirim ulang</li>
                    </ul>
                </div>
            </div>
        </form>

        <!-- MODAL HELPDESK UNTUK UBAH EMAIL & WA CS -->
        <div v-if="showHelpdeskModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fadeIn">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 dark:border-slate-800">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-lg flex items-center justify-center font-bold text-sm">
                            💬
                        </div>
                        <h3 class="text-base font-bold text-slate-800 dark:text-white">Bantuan Email & CS FMIKOM</h3>
                    </div>
                    <button @click="showHelpdeskModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        ✕
                    </button>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4 leading-relaxed">
                    Masukkan email aktif baru Anda. Permintaan akan langsung dikirimkan ke <strong>Notifikasi Dashboard Admin WorkOS</strong> dan menghubungkan Anda ke <strong>WhatsApp Helpdesk FMIKOM</strong> dengan pesan pre-formatted.
                </p>

                <form @submit.prevent="submitHelpdesk">
                    <div class="grid gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Email Aktif Baru yang Diajukan</label>
                            <input
                                v-model="helpdeskForm.new_email"
                                type="email"
                                required
                                placeholder="contoh: emailbaru@gmail.com"
                                class="w-full px-3.5 py-2.5 text-sm border rounded-xl outline-none transition-all dark:bg-slate-950 dark:border-slate-800 dark:text-white border-slate-200 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20"
                            />
                            <InputError :message="helpdeskForm.errors.new_email" class="mt-1" />
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-xl text-xs text-slate-600 dark:text-slate-400 space-y-1">
                            <p class="font-semibold text-slate-700 dark:text-slate-300">📌 Verifikasi Fisik Singkat:</p>
                            <p>Saat terhubung ke WhatsApp Admin, mohon lampirkan foto <strong>KTM (Kartu Tanda Mahasiswa) / KTP</strong> sebagai verifikasi identitas.</p>
                        </div>

                        <div class="flex gap-2 justify-end mt-2">
                            <Button
                                type="button"
                                variant="ghost"
                                @click="showHelpdeskModal = false"
                                class="text-xs text-slate-500 h-10"
                            >
                                Batal
                            </Button>
                            <Button
                                type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white text-xs h-10 rounded-xl px-4 font-semibold"
                                :disabled="helpdeskForm.processing"
                            >
                                <Spinner v-if="helpdeskForm.processing" class="mr-1.5 h-3 w-3" />
                                Kirim & Buka WA Admin
                            </Button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthLayout>
</template>
