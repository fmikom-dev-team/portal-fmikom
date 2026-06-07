<script setup lang="ts">
import html2canvas from "html2canvas-pro";
import QRCode from "qrcode";
import { computed, onMounted, ref, watch } from "vue";

const props = defineProps<{
	profileUser: {
		id: number;
		name: string;
		email: string;
		tanggal_lahir?: string;
		pagi_username?: string;
		bio?: string;
		avatar?: string;
		works_count?: number;
		followers_count?: number;
		nim_nip?: string | null;
		program_studi?: string | null;
		fakultas?: string | null;
	};
}>();

const isFlipped = ref(false);
const isDarkTheme = ref(true);
const qrCodeDataUrl = ref("");
const isDownloading = ref(false);

// Refs for the actual card faces (not the flip wrapper)
const frontCardRef = ref<HTMLElement | null>(null);
const backCardRef = ref<HTMLElement | null>(null);

const cardCoverImage = computed(() => {
	if (
		props.profileUser.avatar &&
		!props.profileUser.avatar.includes("api.dicebear.com")
	) {
		return props.profileUser.avatar;
	}
	return "https://images.unsplash.com/photo-1506366197612-8acc64095e53?q=80&w=480&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D";
});

const profileUrl = computed(() => {
	const base = window.location.origin;
	if (props.profileUser.pagi_username) {
		return `${base}/pagi/${props.profileUser.pagi_username}`;
	}
	return `${base}/pagi/profile/${props.profileUser.id}`;
});

const generateQrCode = async () => {
	try {
		const darkColor = isDarkTheme.value ? "#3b82f6" : "#2563eb";
		const lightColor = isDarkTheme.value ? "#0f0f12" : "#ffffff";

		qrCodeDataUrl.value = await QRCode.toDataURL(profileUrl.value, {
			width: 600,
			margin: 2,
			errorCorrectionLevel: "H",
			color: { dark: darkColor, light: lightColor },
		});
	} catch (err) {
		console.error("Failed to generate QR Code", err);
	}
};

// Download card as high-quality PNG (300 DPI equivalent at 4x scale)
const downloadCard = async () => {
	const target = isFlipped.value ? backCardRef.value : frontCardRef.value;
	if (!target) return;

	isDownloading.value = true;
	try {
		const canvas = await html2canvas(target, {
			scale: 4, // 4x = ~300 DPI for printing
			useCORS: true,
			allowTaint: false,
			backgroundColor: null,
			logging: false,
			imageTimeout: 30000,
			onclone: (doc) => {
				// 1. Remove all Y-rotation / mirroring transforms from inner container and both faces in the clone
				const inner = doc.querySelector(".flip-card-inner") as HTMLElement;
				if (inner) {
					inner.style.transform = "none";
					inner.style.transition = "none";
				}
				const front = doc.querySelector(".flip-card-front") as HTMLElement;
				if (front) {
					front.style.transform = "none";
					front.style.position = "relative";
				}
				const back = doc.querySelector(".flip-card-back") as HTMLElement;
				if (back) {
					back.style.transform = "none";
					back.style.position = "relative";
				}

				// 2. Inject a high-quality blurred background image to simulate backdrop-blur
				// since html2canvas doesn't support backdrop-filter
				if (!isFlipped.value) {
					const cardInner = doc.querySelector(
						".flip-card-front .card__inner",
					) as HTMLElement;
					const coverImg = doc.querySelector(
						".flip-card-front .card__cover",
					) as HTMLImageElement;
					if (cardInner && coverImg) {
						const blurContainer = doc.createElement("div");
						blurContainer.style.position = "absolute";
						blurContainer.style.bottom = "0";
						blurContainer.style.left = "0";
						blurContainer.style.right = "0";
						blurContainer.style.height = "48%";
						blurContainer.style.overflow = "hidden";
						blurContainer.style.zIndex = "1";

						const blurImg = doc.createElement("img");
						blurImg.src = coverImg.src;
						blurImg.style.position = "absolute";
						blurImg.style.bottom = "0";
						blurImg.style.left = "0";
						blurImg.style.width = "100%";
						blurImg.style.height = "208%"; // Matches Card ratio perfectly from bottom
						blurImg.style.objectFit = "cover";
						blurImg.style.filter = "blur(20px)";
						blurImg.style.transform = "scale(1.15)"; // Prevents white edges

						blurContainer.appendChild(blurImg);
						coverImg.parentNode?.insertBefore(
							blurContainer,
							coverImg.nextSibling,
						);
					}
				}
			},
		});

		const link = document.createElement("a");
		const side = isFlipped.value ? "back" : "front";
		const theme = isDarkTheme.value ? "dark" : "light";
		link.download = `fmikom-id-${props.profileUser.pagi_username || props.profileUser.id}-${side}-${theme}.png`;
		link.href = canvas.toDataURL("image/png", 1.0);
		link.click();
	} catch (err) {
		console.error("Download failed", err);
	} finally {
		isDownloading.value = false;
	}
};

watch([isDarkTheme, profileUrl], () => {
	generateQrCode();
});

onMounted(generateQrCode);
</script>

<template>
	<div class="space-y-6 fmikom-id-workspace">
		<!-- Header with Theme Selector -->
		<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
			<div class="space-y-1 text-left">
				<h2 class="text-sm font-black text-[#14171f] tracking-tight">FMIKOM Digital Student ID</h2>
				<p class="text-[11px] text-[#677084] font-medium leading-relaxed">
					Klik pada kartu untuk membalikkan dan melihat QR Code profil Anda.
				</p>
			</div>

			<!-- Switch Theme Option -->
			<div class="flex items-center gap-1.5 self-start sm:self-auto bg-slate-100/80 p-1 rounded-full border border-slate-200/50">
				<button 
					@click="isDarkTheme = true" 
					class="px-4 py-1.5 text-[10px] font-bold rounded-full transition-all cursor-pointer border-none"
					:class="isDarkTheme ? 'bg-[#14171f] text-white shadow-xs' : 'bg-transparent text-[#677084] hover:text-[#14171f]'"
				>
					Gelap (Dark)
				</button>
				<button 
					@click="isDarkTheme = false" 
					class="px-4 py-1.5 text-[10px] font-bold rounded-full transition-all cursor-pointer border-none"
					:class="!isDarkTheme ? 'bg-white text-[#14171f] shadow-xs' : 'bg-transparent text-[#677084] hover:text-[#14171f]'"
				>
					Cerah (Light)
				</button>
			</div>
		</div>

		<!-- Card Display Canvas -->
		<div class="my-8 flex justify-center items-center select-none">
			<div class="perspective-container flex justify-center">
				<div 
					class="flip-card-inner cursor-pointer" 
					:class="{ 'is-flipped': isFlipped }"
					@click="isFlipped = !isFlipped"
				>
					<!-- ========== FRONT FACE ========== -->
					<div ref="frontCardRef" data-card-face class="flip-card-front card" :class="isDarkTheme ? 'theme-dark' : 'theme-light'">
						<div class="card__inner">
							<!-- Cover photo -->
							<img class="card__cover" :src="cardCoverImage" alt="ID Card Cover">

							<div class="card__body">
								<!-- Name -->
								<h2 class="card__header text-left">
									{{ props.profileUser.name }}
								</h2>

								<!-- Program Studi -->
								<p class="text-[10px] font-bold text-left mt-1 leading-normal prodi-text">
									{{ props.profileUser.program_studi || 'Teknik Informatika' }}
								</p>

								<!-- NIM -->
								<p class="text-[10px] font-semibold text-left leading-normal nim-text">
									NIM: {{ props.profileUser.nim_nip || '-' }}
								</p>

							<div class="flex items-end justify-between mt-3 w-full gap-2">
    <div class="flex-1">
        <p class="text-[9px] font-extrabold uppercase tracking-wide leading-none text-left">
            {{ props.profileUser.fakultas || 'Fakultas Matematika dan Ilmu Komputer' }}
        </p>
    </div>

    <img
        :src="isDarkTheme ? '/cek1.svg?v=2' : '/cek2.svg?v=2'"
        alt="FMIKOM Logo"
        class="h-4 shrink-0 select-none"
        style="width: 82px; object-fit: contain;"
        draggable="false"
    >
</div>
							</div>
						</div>
					</div>

					<!-- ========== BACK FACE ========== -->
					<div ref="backCardRef" data-card-face class="flip-card-back card" :class="isDarkTheme ? 'theme-dark' : 'theme-light'">
						<div class="back-panel p-6 flex flex-col justify-between h-full">
							<!-- Top Branding -->
							<div class="flex justify-between items-start w-full">
								<div class="text-left">
									<h3 class="text-sm font-black tracking-wider branding-title">FMIKOM PORTAL</h3>
									<span class="text-[9px] font-bold uppercase tracking-widest block branding-subtitle">Kartu Mahasiswa Digital</span>
								</div>
								<div class="w-8 h-8 rounded-full flex items-center justify-center border branding-avatar">
									<span class="text-xs font-black">FM</span>
								</div>
							</div>

							<!-- QR Code — centered, large, NO blur overlay -->
							<div class="flex-1 flex flex-col items-center justify-center gap-3 my-4">
								<div 
									class="qr-wrapper relative flex items-center justify-center"
									:class="isDarkTheme ? 'bg-[#0f0f12] border-zinc-800' : 'bg-white border-zinc-200'"
								>
									<!-- QR Code image — crisp, no blur -->
									<img 
										v-if="qrCodeDataUrl"
										:src="qrCodeDataUrl"
										alt="Profile QR Code"
										class="qr-image"
									>
									<div v-else class="qr-placeholder animate-pulse" :class="isDarkTheme ? 'bg-zinc-900' : 'bg-zinc-100'"></div>

									<!-- Center FM badge -->
									<div 
										v-if="qrCodeDataUrl"
										class="absolute w-9 h-9 rounded-xl shadow-md flex items-center justify-center border fm-badge pointer-events-none"
										:class="isDarkTheme ? 'bg-[#111215] border-blue-500/40 text-blue-400' : 'bg-white border-blue-200 text-[#2563eb]'"
									>
										<span class="text-[10px] font-black tracking-tight">FM</span>
									</div>
								</div>

								<!-- Scan label -->
								<div class="text-center space-y-0.5">
									<p class="text-[10px] font-black tracking-wide" :class="isDarkTheme ? 'text-white' : 'text-zinc-800'">
										@{{ props.profileUser.pagi_username || 'user' }}
									</p>
									<p class="text-[8px] font-semibold uppercase tracking-widest" :class="isDarkTheme ? 'text-zinc-500' : 'text-zinc-400'">
										Pindai untuk mengunjungi profil
									</p>
								</div>
							</div>

							<!-- Bottom footer with cek logo -->
							<div class="w-full flex justify-between items-center pt-3 border-t border-line">
								<span class="text-[7px] font-bold uppercase tracking-wider" :class="isDarkTheme ? 'text-zinc-600' : 'text-zinc-400'">
									ID: {{ props.profileUser.id }}
								</span>
								<!-- cek logo on back too -->
								<img 
									:src="isDarkTheme ? '/cek1.svg?v=2' : '/cek2.svg?v=2'"
									alt="FMIKOM"
									class="h-4 shrink-0 select-none opacity-70"
									style="width: 82px; object-fit: contain;"
									draggable="false"
								>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Download Button -->
		<div class="flex justify-center pt-2 pb-2">
			<button
				@click="downloadCard"
				:disabled="isDownloading"
				class="inline-flex items-center gap-2.5 px-6 py-3 rounded-2xl text-xs font-bold transition-all border-none cursor-pointer shadow-sm hover:shadow-md active:scale-98 disabled:opacity-60 disabled:cursor-not-allowed"
				:class="isDarkTheme 
					? 'bg-[#14171f] text-white hover:bg-[#222834]' 
					: 'bg-[#0f172a] text-white hover:bg-[#1e293b]'"
			>
				<!-- Download icon -->
				<svg v-if="!isDownloading" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
					<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
					<polyline points="7 10 12 15 17 10"/>
					<line x1="12" y1="15" x2="12" y2="3"/>
				</svg>
				<!-- Loading spinner -->
				<svg v-else class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
					<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
					<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
				</svg>
				<span>{{ isDownloading ? 'Menyiapkan...' : `Unduh Sisi ${isFlipped ? 'Belakang' : 'Depan'}` }}</span>
			</button>
		</div>
	</div>
</template>

<style scoped>
@import url('https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700,800|outfit:300,400,500,600,700,800');

.fmikom-id-workspace {
	font-family: 'Plus Jakarta Sans', 'Outfit', system-ui, -apple-system, sans-serif !important;
}

/* =========================================
   3D Flip Container
   ========================================= */
.perspective-container {
	perspective: 1200px;
}

.flip-card-inner {
	position: relative;
	width: 19.5rem;
	height: 30.5rem;
	max-width: 100%;
	text-align: center;
	transition: transform 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
	transform-style: preserve-3d;
}

@media (max-width: 440px) {
	.flip-card-inner {
		width: 17rem;
		height: 26.5rem;
	}
}

.flip-card-inner.is-flipped {
	transform: rotateY(180deg);
}

.flip-card-front,
.flip-card-back {
	position: absolute !important;
	width: 100%;
	height: 100%;
	-webkit-backface-visibility: hidden;
	backface-visibility: hidden;
	border-radius: 2rem;
	overflow: clip;
}

.flip-card-back {
	transform: rotateY(180deg);
}

/* =========================================
   Card base
   ========================================= */
.card {
	width: 100%;
	height: 100%;
	border-radius: 2rem;
	overflow: clip;
	padding: 0;
	border: 8px solid;
	position: relative;
	box-sizing: border-box;
	transition: background-color 0.3s, border-color 0.3s, box-shadow 0.3s;
}

/* =========================================
   Dark Theme
   ========================================= */
.card.theme-dark {
	color: #e5e5e5;
	background: #111215;
	border-color: #1c1c1e;
	box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.85), inset 0 1px 2px rgba(255,255,255,0.1);
}

.card.theme-dark .card__body {
	background: linear-gradient(
		to top,
		rgba(20, 32, 38, 0.97) 0%,
		rgba(26, 42, 50, 0.88) 40%,
		rgba(32, 53, 63, 0.55) 70%,
		rgba(32, 53, 63, 0) 100%
	);
}

.card.theme-dark .card__header .chips { color: #ffffff; }
.card.theme-dark .verified-icon        { color: #93c5fd; }
.card.theme-dark .prodi-text           { color: #93c5fd; }
.card.theme-dark .nim-text             { color: #94a3b8; }
.card.theme-dark .fakultas-text        { color: #cbd5e1; }
.card.theme-dark .back-panel           { background: #0f0f12; }
.card.theme-dark .branding-title       { color: #ffffff; }
.card.theme-dark .branding-subtitle    { color: #71717a; }
.card.theme-dark .branding-avatar      { background: rgba(37,99,235,0.1); border-color: rgba(37,99,235,0.25); color: #60a5fa; }
.card.theme-dark .border-line          { border-color: rgba(63,63,70,0.7); }

/* =========================================
   Light Theme
   ========================================= */
.card.theme-light {
	color: #1f2937;
	background: #ffffff;
	border-color: #e8ecf0;
	box-shadow: 0 30px 60px -15px rgba(15,23,42,0.10), inset 0 1px 2px rgba(255,255,255,0.9), 0 4px 16px rgba(15,23,42,0.04);
}

.card.theme-light .card__body {
	background: linear-gradient(
		to top,
		rgba(255, 255, 255, 0.99) 0%,
		rgba(255, 255, 255, 0.93) 40%,
		rgba(255, 255, 255, 0.60) 70%,
		rgba(255, 255, 255, 0) 100%
	);
}

.card.theme-light .card__header .chips { color: #0f172a; }
.card.theme-light .verified-icon        { color: #3b82f6; }
.card.theme-light .prodi-text           { color: #2563eb; }
.card.theme-light .nim-text             { color: #64748b; }
.card.theme-light .fakultas-text        { color: #334155; }
.card.theme-light .back-panel           { background: #ffffff; }
.card.theme-light .branding-title       { color: #0f172a; }
.card.theme-light .branding-subtitle    { color: #64748b; }
.card.theme-light .branding-avatar      { background: rgba(37,99,235,0.05); border-color: rgba(37,99,235,0.15); color: #2563eb; }
.card.theme-light .border-line          { border-color: #e2e8f0; }

/* =========================================
   Card inner — FRONT only gets blur overlay
   ========================================= */
.card__inner {
	grid-template-areas: "stack";
	position: relative;
	border-radius: 1.5rem;
	overflow: clip;
	display: grid;
	height: 100%;
}

/* Blur overlay ONLY for front face (covers bottom gradient area) */
.flip-card-front .card__inner::after {
	content: "";
	position: absolute;
	inset: 0;
	border-radius: 1.5rem;
	backdrop-filter: blur(24px);
	-webkit-backdrop-filter: blur(24px);
	mask: linear-gradient(to top, black 35%, transparent 75%);
	-webkit-mask: linear-gradient(to top, black 35%, transparent 75%);
	pointer-events: none;
}

/* Back face: NO blur overlay at all */
.flip-card-back .card__inner::after {
	display: none;
}

.card__cover {
	grid-area: stack;
	width: 100%;
	height: 100%;
	object-fit: cover;
}

.card__body {
	position: relative;
	z-index: 2;
	grid-area: stack;
	margin-top: auto;
	padding: 1.5rem 1.6rem 1.6rem;
	box-sizing: border-box;
	display: flex;
	flex-direction: column;
	justify-content: flex-end;
}

.card__header {
	font-size: 1.2rem;
	font-weight: 700;
	margin: 0;
	letter-spacing: -0.02em;
}

/* =========================================
   Back panel
   ========================================= */
.back-panel {
	border-radius: 1.5rem;
	border: none;
	height: 100%;
	box-sizing: border-box;
	transition: background-color 0.3s;
}

/* =========================================
   QR Code — crisp, no blur
   ========================================= */
.qr-wrapper {
	width: 11rem;
	height: 11rem;
	border-radius: 1.25rem;
	border-width: 1px;
	border-style: solid;
	padding: 0.5rem;
	/* Ensure QR is never blurred */
	isolation: isolate;
	image-rendering: pixelated;
}

.qr-image {
	width: 100%;
	height: 100%;
	object-fit: contain;
	display: block;
	/* Critical: no filter/blur allowed here */
	image-rendering: -webkit-optimize-contrast;
	image-rendering: crisp-edges;
}

.qr-placeholder {
	width: 100%;
	height: 100%;
	border-radius: 0.75rem;
}

.chips {
	font-weight: 500;
	display: inline-flex;
	align-items: center;
	gap: 0.35rem;
}
</style>
