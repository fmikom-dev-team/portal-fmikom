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

// Realtime Sequential Step Carousel States
const currentStepIndex = ref(0); // 0: Token, 1: Signals, 2: Session
const currentStepProgress = ref(0);

const steps = [
	{
		id: 1,
		title: "Token Signature & Cipher Key",
		detail: "Memverifikasi keabsahan tanda tangan digital...",
	},
	{
		id: 2,
		title: "Evaluating Security Signals",
		detail: "Mengevaluasi sinyal peramban & riwayat IP...",
	},
	{
		id: 3,
		title: "Authorizing User Session",
		detail: "Menyiapkan hak akses modul & sesi pengguna...",
	},
];

// Infinite Matrix Scrambler
const SCRAMBLED_STRINGS = [
	"6*7A0^!HIETD@6XS749%2$4L4RO$SH*8W#6OPLLF%WSKVI^PTT1PJUOS60EQL$*K53*Y#AK5GDM6XIWX79XR^DQOMEJF$F1ZNL*L0Z&#LJ4B$E97Q76VF0U#HY!37J5$GKCI0RMK$2P1F9JJYGVR@IAHYPZALXQMJ!519!GZTQSA$#BEXUYPSZ302Z*&DDWW!NI61S#!MAHJ0Y&3J8*EBIMM$#X%46NJ0*9P3L@UW5A8NCZX&98CQ75NL9XEH11NBB^E&LQ1YPZALMJ3DSUXBS9*DADQ7ND0SCI#HY!37J5$GKKAUGDYE@#8CBDUFA9#3EYGVR@IAHZCUKIYPZALX#3EYZX&98CQ75NL9XJ4B$E97Q76VF0LKU8S5KVSD9$#BEX2Z9HSABIU#CSDK@SN!",
	"Y4#!I*ZO1QCFU07QJFDVW#6$17$WW^#7MR5Q50I^2FFKJQW1&1%94ABU&$TX$RRTXT3P!4JPK3^A12&DQ15S08%Q^X*GUE761@6S5DA*HACX9@AS3B04YQ5*VD1*$XX9ECF4B9%O^^LGNDKT%FT2Y2SDC0M!GCNSPVWVNBAWEPT3Q2XK6M877&Q838ZWKGW8*SVG241H51EB2SU1QZL56OR44Q$95ZEDFOVS#AL@C%FEYKZEPI*F&EQUT^65O68J3Q9O^YACNTNVMAK4S#MRM!V@GOKPV0HO2IN$3501P^Y9K3UJ9%LFHMQTJK49A@&84HNFS9IYB@KMEBHIWPSD06$XL8@1A*5OMD!XW8#N7F&MM9R%6E&V&L$^J$8YMANP2TSIP3POYC!I!EER#JBFF",
	"4HM5$8&ZBKCL0G$2ZE7OAZHBUDZXDJW81WD7YDH7##HO7VM84J&@&PV^7YACYLRBWI2HDUW9@!I#H@3%HN%AD@!ED0FOPL#4N8X%LO31#T9N1!HWCAP9DY!KQ5AEMFLF6#DK#4AX70^HXSGH2Y1XJCALNF5XYZ0L28%THU@X&83MKC4R%LZ1J8B86NW1Z$Q8^6J6FP&%PXQ7#LUHV21UM^3K%LYDYO2KWZT!3&WB51UJXJ2Y8!$D7G54RUZEI78^G&1MD%8*5NGKU201%G@FY@CE8$4BG!YEBNCR0YLP@D!W@EU*3II2U8N^9*XZD^^R0BHBP7$7HV0P8F$!XVGWULL4YUDH#MQLQQE8A1&UW0HG42^SVEE3PP9XLURVKU8$OZ!0DV0X!@NHGNFG!I9KF",
	"IZE$@GCC&9OEB%@LLRX%IJ!VILBQ$%K#XALOTXTQD1%J82QSFUS512FRQHSO@#R#MK0C0@686S$XS1EPS0YLQ!%TL374LL#Y@DL4&1G85XA6S59K99DWZ8@LEVWAK94Y99VDSXS^V$71J092U2V#AB*@*45AZXIGVM^08V1&F1#!ST5PP7WBR*RE1SZ%UCJNMHP#^DJ0O1JAZIGPB7%V7DBQ^CKZ^6B^Q510BMK8Y3TA&@HZAHYCMG1J9Y1FOQ2TS3M$A@R%5^X$71W@N@%&W100&7768Q3!8V2F6K8#R^X!3VZ^GUHQ#3%BUSASCQL1#C4#AJ5RQJ1ITY%CZVD$$EZP!QRML2FOU%M9OH#17#I&H4SLS8U0E9%L^MDYEWYCUL*RXKYHKB$A7PZ10AB6^",
];

let scramblerInterval: ReturnType<typeof setInterval> | null = null;
let scramblerIndex = 0;
const scramblerText = ref(SCRAMBLED_STRINGS[0]);

// WebGL Cloudscape Shader Background Logic
const canvasRef = ref<HTMLCanvasElement | null>(null);
const hostRef = ref<HTMLDivElement | null>(null);
let animationFrameId = 0;
let resizeObserver: ResizeObserver | null = null;

const vertexShaderGLSL = `
attribute vec2 position;
void main() {
  gl_Position = vec4(position, 0.0, 1.0);
}
`;

const fragmentShaderGLSL = `
precision highp float;

uniform vec2 u_resolution;
uniform float u_time;
uniform vec3 u_colorBottom;
uniform vec3 u_colorMid;
uniform vec3 u_colorTop;
uniform float u_speed;

float hash(vec2 p) {
  return fract(sin(dot(p, vec2(127.1, 311.7))) * 43758.5453123);
}

float noise(vec2 p) {
  vec2 i = floor(p);
  vec2 f = fract(p);
  float a = hash(i);
  float b = hash(i + vec2(1.0, 0.0));
  float c = hash(i + vec2(0.0, 1.0));
  float d = hash(i + vec2(1.0, 1.0));
  vec2 u = f * f * (3.0 - 2.0 * f);
  return mix(a, b, u.x) + (c - a) * u.y * (1.0 - u.x) + (d - b) * u.x * u.y;
}

float fbm(vec2 p, float t) {
  float v = 0.0;
  float a = 0.5;
  float fi = 0.0;
  mat2 rot = mat2(0.86, 0.51, -0.51, 0.86);
  
  for (int i = 0; i < 6; i++) {
    vec2 morph = vec2(sin(t * 0.5 + fi), cos(t * 0.3 - fi)) * 0.05;
    v += a * noise(p + morph);
    p = rot * p * 2.0;
    a *= 0.5;
    fi += 1.0;
  }
  return v;
}

void main() {
  vec2 uv = gl_FragCoord.xy / u_resolution;
  float t = u_time * u_speed;
  vec2 aspect = vec2(u_resolution.x / max(u_resolution.y, 1.0), 1.0);
  vec2 p = (uv - 0.5) * aspect;

  vec2 wind = vec2(t * 0.1, t * 0.02);

  float pattern = fbm(p * 2.2 - wind, t);

  float bandLow = smoothstep(0.3, 0.65, pattern);
  float bandHigh = smoothstep(0.7, 0.95, pattern); 
  
  vec3 color = mix(u_colorBottom, u_colorMid, bandLow);
  color = mix(color, u_colorTop, bandHigh);

  gl_FragColor = vec4(color, 1.0);
}
`;

const hexToRgbNormalized = (hex: string): [number, number, number] => {
	const normalized = hex.replace("#", "");
	const r = Number.parseInt(normalized.slice(0, 2), 16) / 255;
	const g = Number.parseInt(normalized.slice(2, 4), 16) / 255;
	const b = Number.parseInt(normalized.slice(4, 6), 16) / 255;
	return [r, g, b];
};

const initCloudscapeWebGL = () => {
	const canvas = canvasRef.value;
	const host = hostRef.value;
	if (!canvas || !host) return;

	const gl = canvas.getContext("webgl", { antialias: true, alpha: true });
	if (!gl) return;

	const compileGLSLShader = (type: number, source: string) => {
		const shader = gl.createShader(type);
		if (!shader) return null;
		gl.shaderSource(shader, source);
		gl.compileShader(shader);
		if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
			gl.deleteShader(shader);
			return null;
		}
		return shader;
	};

	const vertexShader = compileGLSLShader(gl.VERTEX_SHADER, vertexShaderGLSL);
	const fragmentShader = compileGLSLShader(
		gl.FRAGMENT_SHADER,
		fragmentShaderGLSL,
	);
	if (!vertexShader || !fragmentShader) return;

	const glProgram = gl.createProgram();
	if (!glProgram) return;

	gl.attachShader(glProgram, vertexShader);
	gl.attachShader(glProgram, fragmentShader);
	gl.linkProgram(glProgram);

	if (!gl.getProgramParameter(glProgram, gl.LINK_STATUS)) {
		gl.deleteProgram(glProgram);
		return;
	}

	gl.useProgram(glProgram);

	const vertexPositionAttribLocation = gl.getAttribLocation(
		glProgram,
		"position",
	);
	const screenQuadVertexBuffer = gl.createBuffer();
	gl.bindBuffer(gl.ARRAY_BUFFER, screenQuadVertexBuffer);
	gl.bufferData(
		gl.ARRAY_BUFFER,
		new Float32Array([-1, -1, 1, -1, -1, 1, 1, 1]),
		gl.STATIC_DRAW,
	);
	gl.enableVertexAttribArray(vertexPositionAttribLocation);
	gl.vertexAttribPointer(
		vertexPositionAttribLocation,
		2,
		gl.FLOAT,
		false,
		0,
		0,
	);

	const resolutionUniformLocation = gl.getUniformLocation(
		glProgram,
		"u_resolution",
	);
	const timeUniformLocation = gl.getUniformLocation(glProgram, "u_time");
	const colorBottomUniformLocation = gl.getUniformLocation(
		glProgram,
		"u_colorBottom",
	);
	const colorMidUniformLocation = gl.getUniformLocation(
		glProgram,
		"u_colorMid",
	);
	const colorTopUniformLocation = gl.getUniformLocation(
		glProgram,
		"u_colorTop",
	);
	const speedUniformLocation = gl.getUniformLocation(glProgram, "u_speed");

	const resize = () => {
		const dpr = Math.min(window.devicePixelRatio || 1, 2);
		const { width, height } = host.getBoundingClientRect();
		canvas.width = Math.max(1, Math.floor(width * dpr));
		canvas.height = Math.max(1, Math.floor(height * dpr));
		gl.viewport(0, 0, canvas.width, canvas.height);
		if (resolutionUniformLocation) {
			gl.uniform2f(resolutionUniformLocation, canvas.width, canvas.height);
		}
	};

	resize();
	resizeObserver = new ResizeObserver(resize);
	resizeObserver.observe(host);

	const start = performance.now();
	const colorBottom = hexToRgbNormalized("#cbd5e1"); // Soft Slate Blue/Cyan
	const colorMid = hexToRgbNormalized("#f1f5f9"); // Soft Slate White
	const colorTop = hexToRgbNormalized("#ffffff"); // Pure White

	const render = (now: number) => {
		const elapsedSec = (now - start) / 1000;
		gl.clearColor(0, 0, 0, 0);
		gl.clear(gl.COLOR_BUFFER_BIT);

		if (timeUniformLocation) gl.uniform1f(timeUniformLocation, elapsedSec);
		if (colorBottomUniformLocation)
			gl.uniform3f(
				colorBottomUniformLocation,
				colorBottom[0],
				colorBottom[1],
				colorBottom[2],
			);
		if (colorMidUniformLocation)
			gl.uniform3f(
				colorMidUniformLocation,
				colorMid[0],
				colorMid[1],
				colorMid[2],
			);
		if (colorTopUniformLocation)
			gl.uniform3f(colorTopUniformLocation, colorTop[0], colorTop[1], colorTop[2]);
		if (speedUniformLocation) gl.uniform1f(speedUniformLocation, 0.5);

		gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);
		animationFrameId = requestAnimationFrame(render);
	};

	animationFrameId = requestAnimationFrame(render);
};

const startScrambler = () => {
	scramblerInterval = setInterval(() => {
		scramblerIndex = (scramblerIndex + 1) % SCRAMBLED_STRINGS.length;
		scramblerText.value = SCRAMBLED_STRINGS[scramblerIndex];
	}, 500);
};

const animateStepProgress = (durationMs: number): Promise<void> => {
	return new Promise((resolve) => {
		currentStepProgress.value = 0;
		const intervalMs = 20;
		const increment = 100 / (durationMs / intervalMs);

		const timer = setInterval(() => {
			currentStepProgress.value = Math.min(
				currentStepProgress.value + increment,
				100,
			);
			if (currentStepProgress.value >= 100) {
				clearInterval(timer);
				resolve();
			}
		}, intervalMs);
	});
};

const runSequentialVerification = async () => {
	if (!props.isValid || !props.signedParams) return;

	const apiPromise = axios.post(
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

	try {
		// Step 1: Token Signature & Cipher Key (2.4 seconds smooth pace)
		currentStepIndex.value = 0;
		await animateStepProgress(2400);
		await new Promise((resolve) => setTimeout(resolve, 400));

		// Step 2: Evaluating Security Signals (2.6 seconds smooth pace)
		currentStepIndex.value = 1;
		await animateStepProgress(2600);
		await new Promise((resolve) => setTimeout(resolve, 400));

		// Step 3: Authorizing User Session (2.2 seconds smooth pace)
		currentStepIndex.value = 2;
		await animateStepProgress(2200);

		const response = await apiPromise;

		if (response.data.success) {
			await new Promise((resolve) => setTimeout(resolve, 500));
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
	initCloudscapeWebGL();
	if (props.isValid) {
		runSequentialVerification();
	}
});

onUnmounted(() => {
	if (scramblerInterval) clearInterval(scramblerInterval);
	if (animationFrameId) cancelAnimationFrame(animationFrameId);
	if (resizeObserver) resizeObserver.disconnect();
});
</script>

<template>
    <!-- Clean Modern White Standalone Page Layout with WebGL Cloudscape Background -->
    <div ref="hostRef" class="relative min-h-screen w-full bg-slate-100 flex flex-col items-center justify-center p-4 sm:p-6 font-sans overflow-hidden">
        <!-- WebGL Canvas Shader Background -->
        <canvas ref="canvasRef" aria-hidden="true" class="pointer-events-none absolute inset-0 h-full w-full z-0 opacity-80" />

        <Head>
            <title>Smart Access Control - Verifikasi Akses</title>
        </Head>

        <!-- INVALID / EXPIRED STATE -->
        <div v-if="!props.isValid || verificationError" class="relative z-10 w-full max-w-sm p-6 bg-white/95 backdrop-blur-md dark:bg-neutral-900 border border-red-200 dark:border-red-900/60 rounded-2xl shadow-xl text-center animate-in fade-in zoom-in-95 duration-300">
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

        <!-- FORGEUI SECURITY CARD (PERFECT PORTRAIT CARD PROPORTIONS) -->
        <div v-else class="relative z-10 flex flex-col items-center gap-5 w-full max-w-[390px] sm:max-w-[420px] px-3 sm:px-0">
            <div class="relative overflow-hidden shadow-2xl shadow-black/5 flex h-[480px] sm:h-[510px] w-full items-center justify-center rounded-3xl bg-white/95 backdrop-blur-md border border-neutral-200/90 dark:bg-neutral-900 dark:border-neutral-800 transition-all duration-300">
                <!-- Infinite Scrambler Matrix Background -->
                <div class="absolute top-[12%] max-w-[340px] sm:max-w-[370px] px-3 pointer-events-none select-none">
                    <p class="font-mono text-[11px] sm:text-xs leading-4 break-words whitespace-normal text-neutral-400 opacity-35">
                        {{ scramblerText }}
                    </p>
                </div>

                <!-- Container Masks (Gradient Transitions) -->
                <div class="absolute top-0 left-0 h-full w-16 sm:w-20 bg-gradient-to-r from-white via-white/80 to-transparent dark:from-neutral-900" />
                <div class="absolute top-0 right-0 h-full w-16 sm:w-20 bg-gradient-to-l from-white via-white/80 to-transparent dark:from-neutral-900" />
                <div class="absolute top-0 left-0 h-32 w-full bg-gradient-to-b from-white via-white/95 to-transparent dark:from-neutral-900" />

                <!-- Card Header Text -->
                <div class="absolute top-5 left-0 w-full px-6 z-10">
                    <h3 class="text-neutral-900 dark:text-white text-lg sm:text-xl font-bold tracking-tight">{{ cardTitle }}</h3>
                    <p class="mt-1 text-xs leading-relaxed text-neutral-500 dark:text-neutral-400">
                        {{ cardDescription }}
                    </p>
                </div>

                <!-- FaceCard Animated Frame (Raised Higher Position - Zero Overlap) -->
                <div class="absolute top-[24%] sm:top-[26%] z-10 rounded-[5px] bg-neutral-200/70 dark:bg-neutral-950/60 p-1 shadow-md transition-all duration-300">
                    <div class="relative h-32 w-26 sm:h-36 sm:w-28 rounded-[3px] bg-gradient-to-br from-neutral-100 to-neutral-200 dark:from-neutral-800 dark:to-neutral-900 flex items-center justify-center overflow-hidden">
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

                <!-- FORGEUI 3-CARD STACK CAROUSEL (Top Stack, Middle Active Focus, Bottom Stack) -->
                <div v-if="!isFinished" class="absolute top-[60%] sm:top-[62%] w-[88%] max-w-[340px] h-[155px] flex flex-col items-center justify-center z-20 animate-in fade-in duration-300">
                    <!-- Top and Bottom Gradient Mask Fades -->
                    <div class="pointer-events-none absolute top-0 z-40 h-[30%] w-full bg-gradient-to-b from-white via-white/80 to-transparent dark:from-neutral-900" />
                    <div class="pointer-events-none absolute bottom-0 z-40 h-[30%] w-full bg-gradient-to-t from-white via-white/80 to-transparent dark:from-neutral-900" />

                    <div
                        v-for="(stepItem, index) in steps"
                        :key="stepItem.id"
                        :class="[
                            'absolute w-full flex flex-col justify-center gap-1.5 rounded-xl border p-2.5 transition-all duration-500 ease-out shadow-sm',
                            // ACTIVE MIDDLE FOCUS CARD
                            index === currentStepIndex
                                ? 'bg-gradient-to-br from-white to-neutral-50 border-emerald-400 dark:border-emerald-700 shadow-md ring-1 ring-emerald-500/20 scale-100 opacity-100 blur-none translate-y-0 z-30'
                            // TOP COMPLETED STACK CARD (Pushed Up & Blurred)
                                : index === currentStepIndex - 1
                                ? 'bg-gradient-to-br from-emerald-50/50 to-white border-emerald-200/90 dark:border-emerald-900/60 scale-[0.90] opacity-45 blur-[0.7px] -translate-y-4 z-10 pointer-events-none'
                            // BOTTOM WAITING STACK CARD (Pushed Down & Blurred)
                                : index === currentStepIndex + 1
                                ? 'bg-gradient-to-br from-white to-neutral-50/80 border-neutral-200/80 dark:border-neutral-800 scale-[0.90] opacity-45 blur-[0.7px] translate-y-4 z-10 pointer-events-none'
                            // OFFSCREEN HIDDEN CARD
                                : 'opacity-0 scale-[0.8] -translate-y-8 pointer-events-none hidden'
                        ]"
                    >
                        <div class="flex items-center gap-2 text-xs font-semibold text-neutral-800 dark:text-neutral-200">
                            <!-- Active Spinner -->
                            <div v-if="index === currentStepIndex" class="w-3.5 h-3.5 rounded-full border-2 border-emerald-500 border-t-transparent animate-spin shrink-0"></div>
                            <!-- Completed Checkmark -->
                            <div v-else-if="index < currentStepIndex" class="w-3.5 h-3.5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[9px] font-bold shrink-0">✓</div>
                            <!-- Pending Bullet -->
                            <div v-else class="w-3.5 h-3.5 rounded-full border border-neutral-300 dark:border-neutral-700 shrink-0"></div>
                            
                            <span class="truncate">{{ stepItem.title }}</span>
                        </div>

                        <!-- Animated Progress Bar -->
                        <div class="h-1.5 w-full bg-neutral-200/90 dark:bg-neutral-800 rounded-full overflow-hidden">
                            <div
                                class="h-full bg-emerald-500 transition-all duration-150 ease-out rounded-full"
                                :style="{ width: index < currentStepIndex ? '100%' : index === currentStepIndex ? `${currentStepProgress}%` : '0%' }"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- VERIFIED USER INFO & CYAN CHECK BADGE (Revealed upon completion) -->
                <div v-else class="absolute top-[70%] flex h-20 w-full flex-col items-center justify-center gap-1 z-20 animate-in fade-in slide-in-from-bottom-2 duration-500">
                    <div class="flex items-center justify-center gap-1.5 text-xs sm:text-sm font-bold text-neutral-800 dark:text-white">
                        <span>{{ props.userData?.name || 'User Verification' }}</span>
                        <!-- CheckCircle Badge Cyan -->
                        <div class="relative w-4.5 h-4.5 flex items-center justify-center">
                            <div class="w-4.5 h-4.5 rounded-full bg-[#06b6d4] flex items-center justify-center text-white text-[10px] font-bold [filter:drop-shadow(0_0_4px_#06b6d4)] animate-in zoom-in duration-300">
                                ✓
                            </div>
                        </div>
                    </div>
                    <div class="text-[11px] sm:text-xs text-neutral-500 font-normal">
                        {{ props.userData?.email || 'verifying@example.com' }}
                    </div>
                </div>
            </div>

            <!-- EXPLICIT ENTER DASHBOARD BUTTON (MATCHING PORTRAIT CARD WIDTH) -->
            <button
                v-if="isFinished"
                type="button"
                @click="enterDashboard"
                :disabled="isRedirecting"
                class="w-full bg-[#2563eb] hover:bg-[#1d4ed8] text-white font-bold text-sm py-3.5 px-6 rounded-xl shadow-lg shadow-blue-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer animate-in fade-in slide-in-from-bottom-2 duration-300"
            >
                <span v-if="isRedirecting">Mengalihkan...</span>
                <span v-else>Masuk ke Dashboard Portal →</span>
            </button>
            <div v-else class="text-xs font-medium text-neutral-500 animate-pulse flex items-center gap-2 py-1">
                <div class="w-3.5 h-3.5 rounded-full border-2 border-blue-600 border-t-transparent animate-spin"></div>
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

