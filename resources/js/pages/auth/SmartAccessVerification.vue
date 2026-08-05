<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3";
import axios from "axios";
import { onMounted, onUnmounted, ref } from "vue";

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

const cardTitle = ref("Smart Access Control");
const cardDescription = ref(
	"Evaluate each login based on real-time signals like IP, device history, and context before allowing access intelligently.",
);

const isFinished = ref(false);
const isRedirecting = ref(false);
const redirectUrl = ref("");
const verificationError = ref("");

// Infinite Matrix Scrambler
const SCRAMBLED_STRINGS = [
	"6*7A0^!HIETD@6XS749%2$4L4RO$SH*8W#6OPLLF%WSKVI^PTT1PJUOS60EQL$*K53*Y#AK5GDM6XIWX79XR^DQOMEJF$F1ZNL*L0Z&#LJ4B$E97Q76VF0U#HY!37J5$GKCI0RMK$2P1F9JJYGVR@IAHYPZALXQMJ!519!GZTQSA$#BEXUYPSZ302Z*&DDWW!NI61S#!MAHJ0Y&3J8*EBIMM$#X%46NJ0*9P3L@UW5A8NCZX&98CQ75NL9XEH11NBB^E&LQ1YPZALMJ3DSUXBS9*DADQ7ND0SCI#HY!37J5$GKKAUGDYE@#8CBDUFA9#3EYGVR@IAHZCUKIYPZALX#3EYZX&98CQ75NL9XJ4B$E97Q76VF0LKU8S5KVSD9$#BEX2Z9HSABIU#CSDK@SN!",
	"Y4#!I*ZO1QCFU07QJFDVW#6$17$WW^#7MR5Q50I^2FFKJQW1&1%94ABU&$TX$RRTXT3P!4JPK3^A12&DQ15S08%Q^X*GUE761@6S5DA*HACX9@AS3B04YQ5*VD1*$XX9ECF4B9%O^^LGNDKT%FT2Y2SDC0M!GCNSPVWVNBAWEPT3Q2XK6M877&Q838ZWKGW8*SVG241H51EB2SU1QZL56OR44Q$95ZEDFOVS#AL@C%FEYKZEPI*F&EQUT^65O68J3Q9O^YACNTNVMAK4S#MRM!V@GOKPV0HO2IN$3501P^Y9K3UJ9%LFHMQTJK49A@&84HNFS9IYB@KMEBHIWPSD06$XL8@1A*5OMD!XW8#N7F&MM9R%6E&V&L$^J$8YMANP2TSIP3POYC!I!EER#JBFF",
	"4HM5$8&ZBKCL0G$2ZE7OAZHBUDZXDJW81WD7YDH7##HO7VM84J&@&PV^7YACYLRBWI2HDUW9@!I#H@3%HN%AD@!ED0FOPL#4N8X%LO31#T9N1!HWCAP9DY!KQ5AEMFLF6#DK#4AX70^HXSGH2Y1XJCALNF5XYZ0L28%THU@X&83MKC4R%LZ1J8B86NW1Z$Q8^6J6FP&%PXQ7#LUHV21UM^3K%LYDYO2KWZT!3&WB51UJXJ2Y8!$D7G54RUZEI78^G&1MD%8*5NGKU201%G@FY@CE8$4BG!YEBNCR0YLP@D!W@EU*3II2U8N^9*XZD^^R0BHBP7$7HV0P8F$!XVGWULL4YUDH#MQLQQE8A1&UW0HG42^SVEE3PP9XLURVKU8$OZ!0DV0X!@NHGNFG!I9KF",
	"IZE$@GCC&9OEB%@LLRX%IJ!VILBQ$%K#XALOTXTQD1%J82QSFUS512FRQHSO@#R#MK0C0@686S$XS1EPS0YLQ!%TL374LL#Y@DL4&1G85XA6S59K99DWZ8@LEVWAK94Y99VDSXS^V$71J092U2V#AB*@*45AZXIGVM^08V1&F1#!ST5PP7WBR*RE1SZ%UCJNMHP#^DJ0O1JAZIGPB7%V7DBQ^CKZ^6B^Q510BMK8Y3TA&@HZAHYCMG1J9Y1FOQ2TS3M$A@R%5^X$71W@N@%&W100&7768Q3!8V2F6K8#R^X!3VZ^GUHQ#3%BUSASCQL1#C4#AJ5RQJ1ITY%CZVD$$EZP!QRML2FOU%M9OH#17#I&H4SLS8U0E9%L^MDYEWYCUL*RXKYHKB$A7PZ10AB6^",
];

const scramblerText = ref(SCRAMBLED_STRINGS[0]);
let scramblerInterval: ReturnType<typeof setInterval> | null = null;
let scramblerIndex = 0;

const startScrambler = () => {
	scramblerInterval = setInterval(() => {
		scramblerIndex = (scramblerIndex + 1) % SCRAMBLED_STRINGS.length;
		scramblerText.value = SCRAMBLED_STRINGS[scramblerIndex];
	}, 500);
};

const verifyUserAccess = async () => {
	if (!props.isValid || !props.signedParams) return;

	// Give time for face drawing animation
	await new Promise((resolve) => setTimeout(resolve, 2000));

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
			isFinished.value = true;
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
	isRedirecting.value = true;
	if (redirectUrl.value) {
		router.visit(redirectUrl.value);
	} else {
		router.visit("/dashboard");
	}
};

onMounted(() => {
	startScrambler();
	if (props.isValid) {
		verifyUserAccess();
	}
});

onUnmounted(() => {
	if (scramblerInterval) clearInterval(scramblerInterval);
});
</script>

<template>
    <!-- Clean Modern White Standalone Page Layout -->
    <div class="min-h-screen w-full bg-slate-100/90 dark:bg-neutral-950 flex flex-col items-center justify-center p-4 sm:p-6 font-sans">
        <Head>
            <title>Smart Access Control - Verifikasi Akses</title>
        </Head>

        <!-- INVALID / EXPIRED STATE -->
        <div v-if="!props.isValid || verificationError" class="w-full max-w-sm p-6 bg-white dark:bg-neutral-900 border border-red-200 dark:border-red-900/60 rounded-2xl shadow-xl text-center animate-in fade-in zoom-in-95 duration-300">
            <div class="w-12 h-12 mx-auto rounded-full bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400 flex items-center justify-center mb-3">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-base font-bold text-red-900 dark:text-red-300 mb-1">Verifikasi Akses Gagal</h3>
            <p class="text-xs text-red-700 dark:text-red-400 leading-relaxed mb-4">
                {{ props.errorMessage || verificationError }}
            </p>
            <a href="/login" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#2563eb] hover:underline">
                ← Kembali ke Halaman Login
            </a>
        </div>

        <!-- FORGEUI SECURITY CARD (SCALED UP 2X - RESPONSIVE CLEAN MODERN WHITE) -->
        <div v-else class="flex flex-col items-center gap-6 w-full max-w-[540px] sm:max-w-[620px] md:max-w-[680px] px-2 sm:px-0">
            <div class="relative overflow-hidden shadow-2xl shadow-black/5 flex h-[520px] sm:h-[560px] w-full items-center justify-center rounded-3xl bg-white border border-neutral-200/90 dark:bg-neutral-900 dark:border-neutral-800 transition-all duration-300">
                <!-- Infinite Scrambler Matrix Background -->
                <div class="absolute top-[14%] max-w-[480px] sm:max-w-[560px] px-4 pointer-events-none select-none">
                    <p class="font-mono text-xs sm:text-sm leading-5 break-words whitespace-normal text-neutral-400 opacity-35">
                        {{ scramblerText }}
                    </p>
                </div>

                <!-- Container Masks (Gradient Transitions) -->
                <div class="absolute top-0 left-0 h-full w-24 sm:w-32 bg-gradient-to-r from-white via-white/80 to-transparent dark:from-neutral-900" />
                <div class="absolute top-0 right-0 h-full w-24 sm:w-32 bg-gradient-to-l from-white via-white/80 to-transparent dark:from-neutral-900" />
                <div class="absolute top-0 left-0 h-44 w-full bg-gradient-to-b from-white via-white/95 to-transparent dark:from-neutral-900" />

                <!-- Card Header Text -->
                <div class="absolute top-6 left-0 w-full px-6 sm:px-8 z-10">
                    <h3 class="text-neutral-900 dark:text-white text-lg sm:text-2xl font-extrabold tracking-tight">{{ cardTitle }}</h3>
                    <p class="mt-1.5 text-xs sm:text-sm leading-relaxed text-neutral-500 dark:text-neutral-400 max-w-xl">
                        {{ cardDescription }}
                    </p>
                </div>

                <!-- FaceCard Animated Frame (Center Avatar Scaled Up) -->
                <div class="relative z-10 rounded-[6px] bg-neutral-200/70 dark:bg-neutral-950/60 p-1 shadow-md">
                    <div class="relative h-44 w-32 sm:h-48 sm:w-36 rounded-[4px] bg-gradient-to-br from-neutral-100 to-neutral-200 dark:from-neutral-800 dark:to-neutral-900 flex items-center justify-center overflow-hidden">
                        <svg
                            viewBox="0 0 80 96"
                            fill="none"
                            class="absolute inset-0 h-full w-full"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="1.5"
                        >
                            <path
                                d="M26.22 78.25c2.679-3.522 1.485-17.776 1.485-17.776-1.084-2.098-1.918-4.288-2.123-5.619-3.573 0-3.7-8.05-3.827-9.937-.102-1.509 1.403-1.383 2.169-1.132-.298-1.3-.92-5.408-1.021-11.446C22.775 24.794 30.94 17.75 40 17.75h.005c9.059 0 17.225 7.044 17.097 14.59-.102 6.038-.723 10.147-1.021 11.446.765-.251 2.271-.377 2.169 1.132-.128 1.887-.254 9.937-3.827 9.937-.205 1.331-1.039 3.521-2.123 5.619 0 0-1.194 14.254 1.485 17.776"
                                class="stroke-neutral-300 dark:stroke-neutral-800"
                            ></path>
                            <path
                                d="M27.705 60.474a26.884 26.884 0 0 0 1.577 2.682c1.786 2.642 5.36 6.792 10.718 6.792h.005c5.358 0 8.932-4.15 10.718-6.792a26.884 26.884 0 0 0 1.577-2.682"
                                class="stroke-neutral-300 dark:stroke-neutral-800"
                            />
                            <path
                                d="M26.22 78.25c2.679-3.522 1.485-17.776 1.485-17.776-1.084-2.098-1.918-4.288-2.123-5.619-3.573 0-3.7-8.05-3.827-9.937-.102-1.509 1.403-1.383 2.169-1.132-.298-1.3-.92-5.408-1.021-11.446C22.775 24.794 30.94 17.75 40 17.75h.005c9.059 0 17.225 7.044 17.097 14.59-.102 6.038-.723 10.147-1.021 11.446.765-.251 2.271-.377 2.169 1.132-.128 1.887-.254 9.937-3.827 9.937-.205 1.331-1.039 3.521-2.123 5.619 0 0-1.194 14.254 1.485 17.776"
                                class="animate-draw-outline stroke-[#06b6d4] [filter:drop-shadow(0_0_8px_#06b6d4)]"
                            ></path>
                            <path
                                d="M27.705 60.474a26.884 26.884 0 0 0 1.577 2.682c1.786 2.642 5.36 6.792 10.718 6.792h.005c5.358 0 8.932-4.15 10.718-6.792a26.884 26.884 0 0 0 1.577-2.682"
                                class="animate-draw stroke-[#06b6d4] [filter:drop-shadow(0_0_8px_#06b6d4)]"
                            />
                        </svg>
                    </div>
                </div>

                <!-- Curved Bottom Overlay Arch -->
                <div class="absolute bottom-0 h-1/2 w-[150%] rounded-t-[60%] bg-gradient-to-b from-neutral-100/95 to-white shadow-[0_0_900px_rgba(250,250,250,0.9)] dark:from-neutral-900 dark:to-neutral-950 pointer-events-none" />

                <!-- Bottom User Info & Cyan Animated Check Circle -->
                <div class="absolute top-[73%] flex h-20 w-full flex-col items-center justify-center gap-1.5 z-10">
                    <div class="flex items-center justify-center gap-2 text-sm sm:text-base font-bold text-neutral-800 dark:text-white">
                        <span>{{ props.userData?.name || 'User Verification' }}</span>
                        <!-- CheckCircle Badge Cyan -->
                        <div class="relative w-5 h-5 flex items-center justify-center">
                            <div class="w-5 h-5 rounded-full bg-[#06b6d4] flex items-center justify-center text-white text-xs font-bold [filter:drop-shadow(0_0_5px_#06b6d4)] animate-in zoom-in duration-300">
                                ✓
                            </div>
                        </div>
                    </div>
                    <div class="text-xs sm:text-sm text-neutral-500 font-normal">
                        {{ props.userData?.email || 'verifying@example.com' }}
                    </div>
                </div>
            </div>

            <!-- EXPLICIT ENTER DASHBOARD BUTTON (MATCHING EXPANDED WIDTH) -->
            <button
                v-if="isFinished"
                type="button"
                @click="enterDashboard"
                :disabled="isRedirecting"
                class="w-full bg-[#2563eb] hover:bg-[#1d4ed8] text-white font-bold text-base py-4 px-8 rounded-2xl shadow-xl shadow-blue-500/25 transition-all flex items-center justify-center gap-2.5 cursor-pointer animate-in fade-in slide-in-from-bottom-3 duration-300"
            >
                <span v-if="isRedirecting">Mengalihkan...</span>
                <span v-else>Masuk ke Dashboard Portal →</span>
            </button>
            <div v-else class="text-sm font-medium text-neutral-500 animate-pulse flex items-center gap-2 py-2">
                <div class="w-4 h-4 rounded-full border-2 border-blue-600 border-t-transparent animate-spin"></div>
                Evaluasi sinyal keamanan akun...
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-draw-outline {
  stroke-dasharray: 160;
  stroke-dashoffset: 160;
  animation: draw-outline 4s ease forwards;
}

@keyframes draw-outline {
  from {
    stroke-dasharray: 160;
    stroke-dashoffset: 160;
  }
  to {
    stroke-dasharray: 160;
    stroke-dashoffset: 0;
  }
}

.animate-draw {
  stroke-dasharray: 100;
  stroke-dashoffset: 100;
  animation: draw 7s ease 0.5s forwards;
}

@keyframes draw {
  from {
    stroke-dasharray: 100;
    stroke-dashoffset: 100;
  }
  to {
    stroke-dasharray: 100;
    stroke-dashoffset: 0;
  }
}
</style>

